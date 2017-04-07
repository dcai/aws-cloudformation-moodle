<?php
/**
 * Created by PhpStorm.
 * User: MOODLER
 * Date: 8/31/2016
 * Time: 4:28 PM
 */

function spark_admission_attributes($field) {
    $attributes = array();
    if ($field->placeholder) {
        $attributes[] = 'placeholder='.spark_admission_attributes_escape($field->placeholder);
    }
    if ($field->mask) {

        $attributes[] = 'data-inputmask='.spark_admission_attributes_escape($field->mask);
    }
    if ($field->required) {
        $attributes[] = 'required';
    }
    return implode(',', $attributes);
}
function spark_admission_validate_options($data, $showall=false) {
    $options = array();

    $data = str_replace("\r", '', $data);

    if (($items = explode("\n", $data)) === false) {
        return options;
    } else {
        foreach ($items as $item) {
            $arr = explode("|", $item);
            if (count($arr) == 2) {
                $options[] = $arr;
            } else if (count($arr) == 3) {
                if ((strtoupper($arr[2]) == 'Y') || $showall)
                    $options[] = $arr;
            }
        }
    }
    return $options;
}
function spark_admission_attributes_escape($data) {
    return str_replace(',', '\,', $data);
}

function spark_admission_get_options($fieldname, $required=false) {
    global $DB;

    $data = array();

    if ($field = $DB->get_record('spark_admission_app_fields', array('name' => $fieldname))) {
        if (!$field->disabled || $required) {
            if ($options = spark_admission_validate_options($field->options, true)) {
                $data[''] = get_string('selectoption', 'spark_admission');
                foreach ($options as $option) {
                    $data[$option[0]] = $option[1];
                }
            }
        }
    }

    return $data;
}

function spark_admission_truncate($string, $length, $dots = "...") {
    return (strlen($string) > $length) ? substr($string, 0, $length - strlen($dots)) . $dots : $string;
}


function spark_admission_get_formfields() {
    global $DB;
    $sql = "SELECT a.name,
        a.label,
        a.placeholder,
        a.mask,
        a.helptext,
        a.defaultvalue,
        a.options,
        a.disabled,
        a.required,
        a.optional,
        a.timecreated,
        a.timemodified
        FROM {spark_admission_app_fields} a";

    return $DB->get_records_sql($sql);
}

function spark_admission_check_age($dob, $condate, $calculatemethod = 2){
    $birthdate = new DateTime(date("Y-m-d",  strtotime(implode('-', array_reverse(explode('/', $dob))))));
    $now= new DateTime(date("Y-m-d",  strtotime(implode('-', array_reverse(explode('/', $condate))))));
    $datediff = $birthdate->diff($now);

    if ($calculatemethod === 2) {
        if ($datediff->invert == 1) {
            // dob is after cutoff
            return false;
        }
        return true;

    }
    if ($calculatemethod === 1) {
        if ($datediff->invert == 1) {
            // dob is after cutoff
            return false;
        }
        if ($datediff->invert == 0 && $datediff->d == 0) {
            // dob is on cutoff day
            return false;
        }
        return true;
    }
    throw new Exception('something bad happended');
}

function spark_admission_validate_options_keyvalue($data, $showall=false) {
    $options = array();

    $data = str_replace("\r", '', $data);

    if (($items = explode("\n", $data)) === false) {
        return options;
    } else {
        foreach ($items as $item) {
            $arr = explode("|", $item);
            if (count($arr) == 2) {
                $options[$arr[0]] = $arr[1];
            }
        }
    }
    return $options;
}

function spark_admission_age_gate() {
    $formfields = spark_admission_get_formfields();
    $grades = spark_admission_validate_options_keyvalue($formfields['grade_applied_for']->options);
    $gradecode = $_POST['grade_applied_for'];

    $sparkadmissionconfig = get_config('spark_admission');
    $agecalculationmethod = (int)$sparkadmissionconfig->agecalculationmethod;
    $schoolyearappliedfor = $_POST['school_year_applied_for'];

    $agegap = 0;
    if ($gradecode === 'PK') {
        // PreK
        if ((int)$sparkadmissionconfig->restrictionprek !== 1) {
            // disabled, passing
            return;
        }
        $agegap = $sparkadmissionconfig->prekentranceage;
    } elseif ($gradecode === 'K') {
        // kendergerden
        if ((int)$sparkadmissionconfig->restrictionkindergarten !== 1) {
            // disabled, passing
            return;
        }
        $agegap = $sparkadmissionconfig->kindergartenentranceage;
    }

    $method = get_string('agecalculationmethodbefore', 'spark_admission');
    if ($agecalculationmethod == 2) {
        $method = get_string('agecalculationmethodonorbefore', 'spark_admission');
    }

    $cutoffday = $sparkadmissionconfig->cutoffdateday;
    $cutoffmonth = $sparkadmissionconfig->cutoffdatemonth;

    if (empty($cutoffday) || empty($cutoffmonth)) {
        // cutoff date wasn't set, pass
        return;
    }

    $currentyear = date('y');
    $cutoffdate = "$cutoffday/$cutoffmonth/$currentyear";

    $gradename = '';
    if (!empty($gradecode)) {
        $gradename = $grades[$gradecode];
    }
    $errormessage = "Student must be $agegap years old $method $cutoffdate to start $gradename in $schoolyearappliedfor school year. If you think you are getting this message in an error, please contact the school admission office.";

    $dob_month = $_POST['student_birth_date_month'];
    $dob_day = $_POST['student_birth_date_day'];
    $dob_year = $_POST['student_birth_date_year'];
    $dob = "$dob_day/$dob_month/$dob_year";
    $qulifiedyear = $dob_year + (int)$agegap;
    $qulifieddate = "$dob_day/$dob_month/$qulifiedyear";


    $result = spark_admission_check_age($qulifieddate, $cutoffdate, $agecalculationmethod);
    if ($result === false) {
        throw new Exception($errormessage);
    }
}
