<?php
use spark_core\phpformbuilder\Form;
use spark_core\phpformbuilder\Validator\Validator;
//session_start();
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot.'/spark/core/includes/phpformbuilder/Form.php');
require_once($CFG->dirroot.'/spark/core/includes/phpformbuilder/Validator/Validator.php');
require_once($CFG->dirroot.'/spark/core/includes/phpformbuilder/Validator/Exception.php');
require_once($CFG->dirroot.'/spark/admission/lib.php');

$errorpage = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Application Form</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-T8Gy5hrqNKT+hzMclPo118YTQO6cYprQmhrYwIiQ/3axmI1hQomh7Ud2hPOy8SP1" crossorigin="anonymous">

    <style>
        .container {
            max-width: 600px;
        }
        .red {
            color:red;
            font-size: 10px;
        }
        .text-danger {
            color:red;
            font-size: 10px;
        }
    </style>
</head>

<body>
<div class="container">
    <p class="alert alert-warning">%errormessage%</p>
</div>
</body>
</html>
EOT;


$formid = 'application-form';

if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken($formid) === true) {
    try {
        spark_admission_age_gate();
    } catch(Exception $ex) {
        $message = $ex->getMessage();
        echo str_replace('%errormessage%', $message, $errorpage);
        die;
    }

    $validator = new Validator($_POST);

    if ($requireds = $DB->get_records('spark_admission_app_fields', array('required' => 1, 'disabled' => 0))) {
        foreach ($requireds as $required) {
            $validator
                ->required()
                ->validate($required->name);
        }
    }

    if (isset($_POST['primary_email'])) {
        $validator->email()->validate('primary_email');
    }
    if (isset($_POST['alternate_email'])) {
        $validator->email()->validate('alternate_email');
    }

    //$validator->hasPattern('/custom_regex/')->validate($field_name);
    //$validator->length(5)->validate('zip-code');

    // check for errors

    if ($validator->hasErrors()) {
        $_SESSION['errors'][$formid] = $validator->getAllErrors();
    } else {
        $formdata = (object)$_POST;
        $schoolcode = get_config('spark_core', 'schoolcode');
        $appliedyear = $formdata->school_year_applied_for;

        if (!isset($formdata->school_applied_for)) {
            $formdata->school_applied_for = $schoolcode;
        }
        if ($DB->record_exists('spark_admission_app', array(
            'student_first_name' => $formdata->student_first_name,
            'student_last_name' => $formdata->student_last_name,
            'school_applied_for' => $formdata->school_applied_for,
            'school_year_applied_for' => $formdata->school_year_applied_for
        ))) {
            echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Application Form</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-T8Gy5hrqNKT+hzMclPo118YTQO6cYprQmhrYwIiQ/3axmI1hQomh7Ud2hPOy8SP1" crossorigin="anonymous">

    <style>
        .container {
            max-width: 600px;
        }
        .red {
            color:red;
            font-size: 10px;
        }
        .text-danger {
            color:red;
            font-size: 10px;
        }
    </style>
</head>

<body>
    <p class="ewMessage">
    Thank you for your interest in '.$schoolcode.'.<br /> Records indicate this student has already applied for the '. $appliedyear .' school year.
    </p>
</body>
</html>';
            die;
        } else {
            // Add application.
            $formdata->timecreated = time();
            $formdata->token = sha1(str_shuffle('' . mt_rand() . time()));
            $applicationid = $DB->insert_record('spark_admission_app', $formdata);

            // Send email.
            $schoolname = get_config('spark_core', 'school_name');
            $noreplyuser  = core_user::get_noreply_user();
            $supportuser  = core_user::get_support_user();

            $noreplyuser->firstname = $schoolname;
            $noreplyuser->lastname = '';

            $recipients = array();
            $tempuser = clone $supportuser;
            $tempuser->email = $formdata->primary_email;
            $tempuser->firstname = $formdata->parent_name;
            $tempuser->lastname = '';
            $tempuser->mailformat = 1;
            $recipients[] = $tempuser;

            $sendconfirmationemail = get_config('spark_admission', 'sendverificationemail');

            $today = date('m/d/Y g:i A');

            if ($recipients && $sendconfirmationemail) {
                $messagesubject = get_config('spark_admission', 'verificationsubject');
                $emailbody = get_config('spark_admission', 'verificationbody');

                $validationturl = new moodle_url('/spark/admission/verify.php', array('t' => $formdata->token));

                // Subject
                $search = array(
                    'schoolname' => '[[schoolname]]'
                );
                $replace = array(
                    'schoolname' => $schoolname
                );
                $messagesubject = str_replace($search, $replace, $messagesubject);

                // Email body.
                $search = array(
                    'parentname' => '[[parentname]]',
                    'appliedyear' => '[[appliedyear]]',
                    'studentfirstname' => '[[studentfirstname]]',
                    'studentlastname' => '[[studentlastname]]',
                    'validationurl' => '[[validationurl]]',
                    'today' => '[[today]]'
                );
                $replace = array(
                    'parentname' => $formdata->parent_name,
                    'appliedyear' => $formdata->school_year_applied_for,
                    'studentfirstname' => $formdata->student_first_name,
                    'studentlastname' => $formdata->student_last_name,
                    'validationurl' => $validationturl->out(false),
                    'today' => $today
                );
                $messagehtml = str_replace($search, $replace, $emailbody);
                $messagetext = html_to_text($messagehtml);

                foreach ($recipients as $recipient) {
                    email_to_user($recipient, $noreplyuser, $messagesubject, $messagetext, $messagehtml);
                }
            }

            // Sent administrative notice.
            $administrators = array();
            $additionalrecipients  = get_config('spark_admission', 'additionalrecipients');
            $additionalrecipients = str_replace(' ', '', $additionalrecipients);
            $additionalrecipients = explode(',', $additionalrecipients);

            if ($additionalrecipients) {
                foreach ($additionalrecipients as $additionalrecipient) {
                    if (filter_var($additionalrecipient, FILTER_VALIDATE_EMAIL)) {
                        $tempuser = clone $supportuser;
                        $tempuser->email = $additionalrecipient;
                        $tempuser->mailformat = 1;
                        $administrators[] = $tempuser;
                    }
                }
            }

            $emailbodyadmin =
                '<p>Date: '.$today.'</p>'.
                '<p>Student First:   '.$formdata->student_first_name.'</p>'.
                '<p>Student Middle:   '.$formdata->student_middle_name.'</p>'.
                '<p>Student Lastname: '.$formdata->student_last_name.'</p>'.
                '<p>DOB: '.$formdata->student_birth_date_month.'/'.$formdata->student_birth_date_day.'/'.$formdata->student_birth_date_year.'</p>'.
                '<p>School: '.$formdata->school_applied_for.'</p>'.
                '<p>Applied Grade: '.$formdata->grade_applied_for.'</p>'.
                '<p>Applied Year: '.$formdata->school_year_applied_for.'</p>'.
                '<p>Parent/Guardian Name: '.$formdata->parent_name.'</p>'.
                '<p>Relation: '.$formdata->parent_relation.'</p>'.
                '<p>Address: '.$formdata->address_street.'</p>'.
                '<p>Zip: '.$formdata->address_zip.'</p>'.
                '<p>City: '.$formdata->address_city.'</p>'.
                '<p>State: '.$formdata->address_state.'</p>'.
                '<p>Primary email: '.$formdata->primary_email.'</p>'.
                '<p>Home phone: '.$formdata->home_phone.'</p>'.
                '<p>Cell phone: '.$formdata->mobile_phone.'</p>';

            if (isset($formdata->current_school)) {
                $emailbodyadmin .= html_writer::tag('p', 'Current school: '.$formdata->current_school);
            }
            if (isset($formdata->current_school_other)) {
                $emailbodyadmin .= html_writer::tag('p', 'Current school other: '.$formdata->current_school_other);
            }

            $messagetextadmin = html_to_text($emailbodyadmin);

            if ($administrators) {
                foreach ($administrators as $recipient) {
                    email_to_user($recipient, $noreplyuser, 'Application notification', $messagetextadmin, $emailbodyadmin);
                }
            }

            $successmessage = get_config('spark_admission', 'successmessage');

            echo '
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="utf-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <title>Application Form</title>
                    <!-- Bootstrap CSS -->
                    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
                    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-T8Gy5hrqNKT+hzMclPo118YTQO6cYprQmhrYwIiQ/3axmI1hQomh7Ud2hPOy8SP1" crossorigin="anonymous">

                    <style>
                         .container {
                            max-width: 600px;
                        }
                        .red {
                            color:red;
                            font-size: 10px;
                        }
                        .text-danger {
                            color:red;
                            font-size: 10px;
                        }
                    </style>
                </head>

                <body>
                    <p class="ewMessage">
                    '.$successmessage.'.
                    </p>
                </body>
                </html>';
        }
        Form::clear($formid);
        die;
    }
}

/* ==================================================
    The Form
================================================== */
$formfields = spark_admission_get_formfields();

$form = new Form($formid, 'horizontal', 'novalidate,autocomplete=off', 'bs3');

// Plugins.
$form->addPlugin('icheck', 'input', 'default', array('%theme%' => 'square', '%color%' => 'blue'));
$form->addPlugin('inputmask', 'input');

$form->startFieldset('Student Information');

// First name.
$form->setCols(4, 3);
$form->groupInputs('student_first_name', 'student_middle_name', 'student_last_name');
$fieldname = 'student_first_name';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        if ($field->helptext) {
            $form->addHtml('<span class="help-block">' . $field->helptext . '</span>', $fieldname, 'after');
        }
        $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
    }
}

// Miidle name.
$form->setCols(0, 2);
$fieldname = 'student_middle_name';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        if ($field->helptext) {
            $form->addHtml('<span class="help-block">' . $field->helptext . '</span>', $fieldname, 'after');
        }
        $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
    }
}

// Last name.
$form->setCols(0, 3);
$fieldname = 'student_last_name';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        if ($field->helptext) {
            $form->addHtml('<span class="help-block">' . $field->helptext . '</span>', $fieldname, 'after');
        }
        $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
    }
}

// Gender.
$form->setCols(4, 8);
$fieldname = 'student_gender';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        $form->addRadio($fieldname, 'Male', 'M');
        $form->addRadio($fieldname, 'Female', 'F');
        $form->printRadioGroup($fieldname, $field->label, true, spark_admission_attributes($field));
    }
}

// Race/Ethnicity.
$form->setCols(4, 4);
$fieldname = 'student_race';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        $options = spark_admission_validate_options($field->options);
        $form->addOption($fieldname, '', 'Please select ...', '', 'selected');
        foreach ($options as $option) {
            $form->addOption($fieldname, $option[0], $option[1]);
        }
        $form->addSelect($fieldname, $field->label, spark_admission_attributes($field));
    }
}

// School Applied For.
$form->setCols(4, 5);
$fieldname = 'school_applied_for';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        $options = spark_admission_validate_options($field->options);
        $form->addOption($fieldname, '', 'Please select ...', '', 'selected');
        foreach ($options as $option) {
            $form->addOption($fieldname, $option[0], $option[1]);
        }
        $form->addSelect($fieldname, $field->label, spark_admission_attributes($field));
    }
}

// School Year Applied For.
$form->setCols(4, 5);
$fieldname = 'school_year_applied_for';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        $options = spark_admission_validate_options($field->options);
        $form->addOption($fieldname, '', 'Please select ...', '', 'selected');
        foreach ($options as $option) {
            $form->addOption($fieldname, $option[0], $option[1]);
        }
        $form->addSelect($fieldname, $field->label, spark_admission_attributes($field));
    }
}

// Grade Applied For.
$form->setCols(4, 4);
$fieldname = 'grade_applied_for';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        $options = spark_admission_validate_options($field->options);
        $form->addOption($fieldname, '', 'Please select ...', '', 'selected');
        foreach ($options as $option) {
            $form->addOption($fieldname, $option[0], $option[1]);
        }
        $form->addSelect($fieldname, $field->label, spark_admission_attributes($field));

        $form->startDependantFields($fieldname, '0 K');

        // VPK.
        $fieldname = 'vpk';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $form->addRadio($fieldname, 'Yes', '1');
                $form->addRadio($fieldname, 'No', '0');
                $form->printRadioGroup($fieldname, $field->label, true, spark_admission_attributes($field));

                $form->startDependantFields($fieldname, '1');
                $fieldname = 'vpk_text';
                if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
                    if (!$field->disabled) {
                        $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
                    }
                }
                $form->endDependantFields();
            }
        }

        $form->endDependantFields();
    }
}


// Date of birth
$form->setCols(4, 3);
$form->groupInputs('student_birth_date_month', 'student_birth_date_day', 'student_birth_date_year');
$fieldname = 'student_birth_date_month';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        $form->addOption($fieldname, '', 'Month', '', 'selected');
        $form->addOption($fieldname, '1', '01 - January');
        $form->addOption($fieldname, '2', '02 - February');
        $form->addOption($fieldname, '3', '03 - March');
        $form->addOption($fieldname, '4', '04 - April');
        $form->addOption($fieldname, '5', '05 - May');
        $form->addOption($fieldname, '6', '06 - June');
        $form->addOption($fieldname, '7', '07 - July');
        $form->addOption($fieldname, '8', '08 - August');
        $form->addOption($fieldname, '9', '09 - September');
        $form->addOption($fieldname, '10', '10 - October');
        $form->addOption($fieldname, '11', '11 - November');
        $form->addOption($fieldname, '12', '12 - December');
        $form->addSelect($fieldname, $field->label, spark_admission_attributes($field));
    }
}

// Day
$form->setCols(0, 2);
$fieldname = 'student_birth_date_day';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        $form->addOption($fieldname, '', 'Day', '', 'selected');
        for ($i = 1; $i <= 31; $i++) {
            $form->addOption($fieldname, $i, $i);
        }
        $form->addSelect($fieldname, $field->label, spark_admission_attributes($field));
    }
}
// Year
$form->setCols(0, 3);
$fieldname = 'student_birth_date_year';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        $options = spark_admission_validate_options($field->options);
        $form->addOption($fieldname, '', 'Year ...', '', 'selected');
        foreach ($options as $option) {
            $form->addOption($fieldname, $option[0], $option[1]);
        }
        $form->addSelect($fieldname, $field->label, spark_admission_attributes($field));
    }
}

$form->endFieldset();
$form->addHtml('<p>&nbsp;</p>');


// Parent/Guardian Information
$form->startFieldset('Parent/Guardian Information');

// Parent name
$form->setCols(4, 8);
$fieldname = 'parent_name';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        if ($field->helptext) {
            $form->addHtml('<span class="help-block">' . $field->helptext . '</span>', $fieldname, 'after');
        }
        $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
    }
}

// Parent relation
$form->setCols(4, 8);
$fieldname = 'parent_relation';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        if ($field->helptext) {
            $form->addHtml('<span class="help-block">' . $field->helptext . '</span>', $fieldname, 'after');
        }
        $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
    }
}

// Resident Address
$form->setCols(4, 8);
$fieldname = 'address_street';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        if ($field->helptext) {
            $form->addHtml('<span class="help-block">' . $field->helptext . '</span>', $fieldname, 'after');
        }
        $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
    }
}

// City.
$form->groupInputs('address_city', 'address_state', 'address_zip');
$form->setCols(4, 3);
$fieldname = 'address_city';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        if ($field->helptext) {
            $form->addHtml('<span class="help-block">' . $field->helptext . '</span>', $fieldname, 'after');
        }
        $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
    }
}
// State.
$form->setCols(0, 2);
$fieldname = 'address_state';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        if ($field->helptext) {
            $form->addHtml('<span class="help-block">' . $field->helptext . '</span>', $fieldname, 'after');
        }
        $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
    }
}
// Zip.
$form->setCols(0, 3);
$fieldname = 'address_zip';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        if ($field->helptext) {
            $form->addHtml('<span class="help-block">' . $field->helptext . '</span>', $fieldname, 'after');
        }
        $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
    }
}

// Mailing address.
$form->setCols(4, 8);
$fieldname = 'mailing_address';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        if ($field->helptext) {
            $form->addHtml('<span class="help-block red">' . $field->helptext . '</span>', $fieldname, 'after');
        }
        $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
    }
}

// Primary Email.
$form->setCols(4, 8);
$fieldname = 'primary_email';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        if ($field->helptext) {
            $form->addHtml('<span class="help-block red">' . $field->helptext . '</span>', $fieldname, 'after');
        }
        $form->addIcon($fieldname, '<span class="glyphicon glyphicon-envelope"></span>', 'before');
        $form->addInput('email', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
    }
}

// Alternate Email.
$form->setCols(4, 8);
$fieldname = 'alternate_email';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        if ($field->helptext) {
            $form->addHtml('<span class="help-block">' . $field->helptext . '</span>', $fieldname, 'after');
        }
        $form->addIcon($fieldname, '<span class="glyphicon glyphicon-envelope"></span>', 'before');
        $form->addInput('email', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
    }
}

// Home phone.
$form->setCols(4, 8);
$fieldname = 'home_phone';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        if ($field->helptext) {
            $form->addHtml('<span class="help-block">' . $field->helptext . '</span>', $fieldname, 'after');
        }
        $form->addIcon($fieldname, '<span class="glyphicon glyphicon-earphone"></span>', 'before');
        $form->addInput('tel', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
    }
}

// Work phone.
$form->setCols(4, 8);
$fieldname = 'work_phone';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        if ($field->helptext) {
            $form->addHtml('<span class="help-block">' . $field->helptext . '</span>', $fieldname, 'after');
        }
        $form->addIcon($fieldname, '<span class="glyphicon glyphicon-earphone"></span>', 'before');
        $form->addInput('tel', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
    }
}

// Mobile phone.
$form->setCols(4, 8);
$fieldname = 'mobile_phone';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        if ($field->helptext) {
            $form->addHtml('<span class="help-block">' . $field->helptext . '</span>', $fieldname, 'after');
        }
        $form->addIcon($fieldname, '<span class="glyphicon glyphicon-earphone"></span>', 'before');
        $form->addInput('tel', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
    }
}

$form->endFieldset();
$form->addHtml('<p>&nbsp;</p>');



// Other information.
$form->startFieldset('Other information');

// Sibling.
$form->setCols(4, 8);
$fieldname = 'sibling';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        $form->addRadio($fieldname, 'Yes', '1');
        $form->addRadio($fieldname, 'No', '0');
        $form->printRadioGroup($fieldname, $field->label, true, spark_admission_attributes($field));

        $form->startDependantFields($fieldname, '1');
        $form->groupInputs('sibling_name', 'sibling_grade');
        $form->setCols(4, 5);
        $fieldname = 'sibling_name';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
            }
        }
        $form->setCols(0, 3);
        $fieldname = 'sibling_grade';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
            }
        }
        $form->endDependantFields();
    }
}

// Placement.
$form->setCols(4, 8);
$fieldname = 'placement';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        $form->addRadio($fieldname, 'Yes', '1');
        $form->addRadio($fieldname, 'No', '0');
        $form->printRadioGroup($fieldname, $field->label, true, spark_admission_attributes($field));

        $form->startDependantFields($fieldname, '1');
        $form->groupInputs('placement_name', 'placement_grade');
        $form->setCols(4, 5);
        $fieldname = 'placement_name';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
            }
        }
        $form->setCols(0, 3);
        $fieldname = 'placement_grade';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
            }
        }
        $form->endDependantFields();
    }
}

// ESOL.
$form->setCols(4, 8);
$fieldname = 'esol';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        $form->addRadio($fieldname, 'Yes', '1');
        $form->addRadio($fieldname, 'No', '0');
        $form->printRadioGroup($fieldname, $field->label, true, spark_admission_attributes($field));
    }
}

// ESE.
$fieldname = 'ese';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        $form->addRadio($fieldname, 'Yes', '1');
        $form->addRadio($fieldname, 'No', '0');
        $form->printRadioGroup($fieldname, $field->label, true, spark_admission_attributes($field));

        $form->startDependantFields($fieldname, '1');
        $fieldname = 'ese_text';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
            }
        }
        $form->endDependantFields();
    }
}

// Gifted.
$fieldname = 'gifted';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        $form->addRadio($fieldname, 'Yes', '1');
        $form->addRadio($fieldname, 'No', '0');
        $form->printRadioGroup($fieldname, $field->label, true, spark_admission_attributes($field));

        $form->startDependantFields($fieldname, '1');
        $fieldname = 'gifted_text';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
            }
        }
        $form->endDependantFields();
    }
}

// Expulsion.
$fieldname = 'expulsion';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        $form->addRadio($fieldname, 'Yes', '1');
        $form->addRadio($fieldname, 'No', '0');
        $form->printRadioGroup($fieldname, $field->label, true, spark_admission_attributes($field));
    }
}

// Skipped.
$fieldname = 'skipped';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        $form->addRadio($fieldname, 'Yes', '1');
        $form->addRadio($fieldname, 'No', '0');
        $form->printRadioGroup($fieldname, $field->label, true, spark_admission_attributes($field));

        $form->startDependantFields($fieldname, '1');
        $fieldname = 'skipped_text';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
            }
        }
        $form->endDependantFields();
    }
}

// Retained.
$fieldname = 'retained';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        $form->addRadio($fieldname, 'Yes', '1');
        $form->addRadio($fieldname, 'No', '0');
        $form->printRadioGroup($fieldname, $field->label, true, spark_admission_attributes($field));

        $form->startDependantFields($fieldname, '1');
        $fieldname = 'retained_text';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
            }
        }
        $form->endDependantFields();
    }
}

// Current school.
$fieldname = 'current_school';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        if ($field->helptext) {
            $form->addHtml('<span class="help-block">' . $field->helptext . '</span>', $fieldname, 'after');
        }
        $options = spark_admission_validate_options($field->options);
        $form->addOption($fieldname, '', 'Please select ...', '', 'selected');
        foreach ($options as $option) {
            $form->addOption($fieldname, $option[0], $option[1]);
        }
        $form->addSelect($fieldname, $field->label, spark_admission_attributes($field));

        $form->startDependantFields($fieldname, 'Other Other-Private Other-Public Other-Charter Other-Duval Other-StJohns Other-Clay Other-Florida Other-Outside');

        $fieldname = 'current_school_other';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                if ($field->helptext) {
                    $form->addHtml('<span class="help-block">' . $field->helptext . '</span>', $fieldname, 'after');
                }
                $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
            }
        }

        $form->endDependantFields();
    }
}

// Current school.
$fieldname = 'hear';
if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
    if (!$field->disabled) {
        $options = spark_admission_validate_options($field->options);
        $form->addOption($fieldname, '', 'Please select ...', '', 'selected');
        foreach ($options as $option) {
            $form->addOption($fieldname, $option[0], $option[1]);
        }
        $form->addSelect($fieldname, $field->label, spark_admission_attributes($field));

        $form->startDependantFields($fieldname, 'Other');
        $fieldname = 'hear_other';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $form->addInput('text', $fieldname, $field->defaultvalue, $field->label, spark_admission_attributes($field));
            }
        }
        $form->endDependantFields();
    }
}

$form->endFieldset();
$form->addHtml('<p>&nbsp;</p>');

$terms = get_config('spark_admission', 'termsandconditions');

$form->addHtml($terms);
$form->setCols(0, 12);
$form->addCheckbox('agreecheck', 'I agree to the above terms', 'agree', '');
$form->printCheckboxGroup('agreecheck', '', true, '');

$form->setCols(4, 8);
$form->addBtn('submit', 'submit-btn', 1, 'Submit', 'class=btn btn-primary application-submit,disabled=disabled');
$form->addHtml('<p>&nbsp;</p>');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Application Form</title>
    <!-- Bootstrap CSS -->
    <link href="<?php echo $CFG->wwwroot; ?>/spark/admission/css/applicationform.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,600,600i,700,700i" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,300i,400,400i,700,700i" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-T8Gy5hrqNKT+hzMclPo118YTQO6cYprQmhrYwIiQ/3axmI1hQomh7Ud2hPOy8SP1" crossorigin="anonymous">
    <?php $form->printIncludes('css'); ?>
    <style>

    </style>
</head>
<body>
<?php
if ($title = get_config('spark_admission', 'title')) {
    echo '<h1 class="text-center">'.$title.'</h1>';
}

if ($description = get_config('spark_admission', 'description')) {
    echo '<div class="container">
             <div class="row">
                <div class="col-sm-10 col-sm-offset-1 col-md-12 col-md-offset-0">'.$description.'</div>
                </div>
            </div>';
}
?>
<div class="container">
    <div class="row">
        <div class="col-sm-10 col-sm-offset-1 col-md-12 col-md-offset-0">
            <?php
            if (isset($sent_message)) {
                echo $sent_message;
            }
            $form->render();
            ?>
        </div>
    </div>
</div>
<?php
if ($footer = get_config('spark_admission', 'footer')) {
    echo '<div class="container footer"><div class="row"><div class="col-sm-10 col-sm-offset-1 col-md-12 col-md-offset-0">';
    echo $footer;
    echo '</div></div></div>';
}
?>
<script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<script src="<?php echo $CFG->wwwroot; ?>/spark/admission/js/index.js"></script>
<?php
$form->printIncludes('js');
$form->printJsCode();
?>
</body>
</html>
