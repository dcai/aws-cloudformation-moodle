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
