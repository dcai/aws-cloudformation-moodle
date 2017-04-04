<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * SPARK
 *
 * @package    spark_admission
 * @copyright  Mustafa Bahcaci <mbahcaci@charterresource.us>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class student_application_form extends moodleform {
    public function definition() {
        global $DB;

        $mform = $this->_form;
        $customdata = $this->_customdata;
        if (!empty($customdata['dataclerk'])) {
            $dataclerk = true;
        } else {
            $dataclerk = false;
        }
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

        $formfields = $DB->get_records_sql($sql);

        $mform->addElement('header', '', get_string('administration', 'spark_admission'), '');

        if (!$dataclerk) {
            $mform->addElement('selectyesno', 'approved', get_string('approved', 'spark_admission'));
            $mform->setType('approved', PARAM_INT);
        }

        $mform->addElement('text', 'idnumber', get_string('idnumber', 'spark_admission'), array('size' => '60'));
        $mform->setType('idnumber', PARAM_TEXT);
        $mform->addHelpButton('idnumber', 'idnumber', 'spark_admission');

        $mform->addElement('select', 'transportation', get_string('transportation', 'spark_admission'),
            spark_admission_get_options('transportation', true), null
        );


        $mform->addElement('date_selector', 'withdraw_date', get_string('withdraw_date', 'spark_admission'), array('optional' => true));

        $mform->addElement('textarea', 'withdraw_reason', get_string('withdraw_reason', 'spark_admission'), 'wrap="virtual" rows="4" cols="60"');
        $mform->setType('withdraw_reason', PARAM_NOTAGS);

        $mform->addElement('date_selector', 'enrollment_date', get_string('enrollment_date', 'spark_admission'), array('optional' => true));

        $mform->addElement('textarea', 'note', get_string('note', 'spark_admission'), 'wrap="virtual" rows="4" cols="60"');
        $mform->setType('note', PARAM_NOTAGS);
        //$mform->addRule('note', null, 'required', null, 'client');





        $mform->addElement('header', '', get_string('studentinfo', 'spark_admission'), '');

        $fieldname = 'student_first_name';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'student_middle_name';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'student_last_name';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'student_gender';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('select', $fieldname, get_string($fieldname, 'spark_admission'),
                    array(
                        'M' => 'Male',
                        'F' => 'Female'
                    ), null
                );
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        // Race.
        $fieldname = 'student_race';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $selectoptions = spark_admission_get_options($fieldname, false);
                $mform->addElement('select', $fieldname, get_string($fieldname, 'spark_admission'), $selectoptions, null);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        // School.
        $fieldname = 'school_applied_for';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $selectoptions = spark_admission_get_options($fieldname, false);
                $mform->addElement('select', $fieldname, get_string($fieldname, 'spark_admission'), $selectoptions, null);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        // Applied year.
        $fieldname = 'school_year_applied_for';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $selectoptions = spark_admission_get_options($fieldname, false);
                $mform->addElement('select', $fieldname, get_string($fieldname, 'spark_admission'), $selectoptions, null);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        // Grade.
        $fieldname = 'grade_applied_for';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $selectoptions = spark_admission_get_options($fieldname, false);
                $mform->addElement('select', $fieldname, get_string($fieldname, 'spark_admission'), $selectoptions, null);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }


        $birthday = array();
        // Month.
        $fieldname = 'student_birth_date_month';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                //$options = spark_admission_validate_options($field->options);
                $selectoptions = array();
                $selectoptions[] = 'Month';
                $selectoptions[] = '01 - January';
                $selectoptions[] = '02 - February';
                $selectoptions[] = '03 - March';
                $selectoptions[] = '04 - April';
                $selectoptions[] = '05 - May';
                $selectoptions[] = '06 - June';
                $selectoptions[] = '07 - July';
                $selectoptions[] = '08 - August';
                $selectoptions[] = '09 - September';
                $selectoptions[] = '10 - October';
                $selectoptions[] = '11 - November';
                $selectoptions[] = '12 - December';
                $birthday[] =& $mform->createElement('select', $fieldname, get_string($fieldname, 'spark_admission'), $selectoptions, null);
            }
        }

        // Day.
        $fieldname = 'student_birth_date_day';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $selectoptions[] = 'Day';
                for ($i = 1; $i <= 31; $i++) {
                    $selectoptions[$i] = $i;
                }
                $birthday[] =& $mform->createElement('select', $fieldname, get_string($fieldname, 'spark_admission'), $selectoptions, null);
            }
        }

        // Year.
        $fieldname = 'student_birth_date_year';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $selectoptions = spark_admission_get_options($fieldname, false);
                $birthday[] =& $mform->createElement('select', $fieldname, get_string($fieldname, 'spark_admission'), $selectoptions, null);
            }
        }

        $mform->addGroup($birthday, 'birthday', get_string('birthdate', 'spark_admission'), array(' '), false);




        $mform->addElement('header', '', get_string('parentinfo', 'spark_admission'), '');

        $fieldname = 'parent_name';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'parent_relation';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'address_street';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'address_city';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'address_state';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '2'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'address_zip';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '10'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'mailing_address';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'primary_email';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $mform->addElement('selectyesno', 'confirmed', get_string('confirmed', 'spark_admission'));
        $mform->setType('confirmed', PARAM_INT);

        $fieldname = 'primary_email';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'alternate_email';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'home_phone';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'work_phone';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'mobile_phone';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }




        $mform->addElement('header', '', get_string('otherinfo', 'spark_admission'), '');

        $fieldname = 'current_school';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $options = spark_admission_validate_options($field->options);
                $selectoptions = array();
                $selectoptions[] = 'Please select ...';
                foreach ($options as $option) {
                    $selectoptions[$option[0]] = $option[1];
                }
                $mform->addElement('select', $fieldname, get_string($fieldname, 'spark_admission'), $selectoptions, null);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'current_school_other';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
                $mform->disabledIf($fieldname, 'current_school', 'neq', 'Other');
            }
        }

        $fieldname = 'sibling';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('selectyesno', $fieldname, get_string($fieldname, 'spark_admission'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'sibling_name';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    //$mform->addRule($fieldname, null, 'required', null, 'client');
                }
                $mform->disabledIf($fieldname, 'sibling', 'eq', 0);
            }
        }

        $fieldname = 'sibling_grade';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    //$mform->addRule($fieldname, null, 'required', null, 'client');
                }
                $mform->disabledIf($fieldname, 'sibling', 'eq', 0);
            }
        }

        $fieldname = 'placement';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('selectyesno', $fieldname, get_string($fieldname, 'spark_admission'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'placement_name';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
                $mform->disabledIf($fieldname, 'placement', 'eq', 0);
            }
        }

        $fieldname = 'placement_grade';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
                $mform->disabledIf($fieldname, 'placement', 'eq', 0);
            }
        }

        $fieldname = 'esol';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('selectyesno', $fieldname, get_string($fieldname, 'spark_admission'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }
        $fieldname = 'ese';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('selectyesno', $fieldname, get_string($fieldname, 'spark_admission'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'ese_text';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
                $mform->disabledIf($fieldname, 'ese', 'eq', 0);
            }
        }

        $fieldname = 'gifted';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('selectyesno', $fieldname, get_string($fieldname, 'spark_admission'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'gifted_text';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
                $mform->disabledIf($fieldname, 'gifted', 'eq', 0);
            }
        }

        $fieldname = 'expulsion';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('selectyesno', $fieldname, get_string($fieldname, 'spark_admission'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'skipped';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('selectyesno', $fieldname, get_string($fieldname, 'spark_admission'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'skipped_text';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
                $mform->disabledIf($fieldname, 'skipped', 'eq', 0);
            }
        }

        $fieldname = 'retained';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('selectyesno', $fieldname, get_string($fieldname, 'spark_admission'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'retained_text';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
                $mform->disabledIf($fieldname, 'retained', 'eq', 0);
            }
        }

        $fieldname = 'vpk';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('selectyesno', $fieldname, get_string($fieldname, 'spark_admission'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
                $mform->disabledIf($fieldname, 'grade_applied_for', 'neq', '0');
            }
        }

        $fieldname = 'vpk_text';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
                $mform->disabledIf($fieldname, 'grade_applied_for', 'neq', '0');
                $mform->disabledIf($fieldname, 'vpk', 'eq', 0);
            }
        }

        $fieldname = 'hear';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $selectoptions = spark_admission_get_options($fieldname, false);
                $mform->addElement('select', $fieldname, get_string($fieldname, 'spark_admission'), $selectoptions, null);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
            }
        }

        $fieldname = 'hear_other';
        if ($field = (isset($formfields[$fieldname]) ? $formfields[$fieldname] : null)) {
            if (!$field->disabled) {
                $mform->addElement('text', $fieldname, get_string($fieldname, 'spark_admission'), array('size' => '60'));
                $mform->setType($fieldname, PARAM_TEXT);
                if ($field->required) {
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                }
                $mform->disabledIf($fieldname, 'hear', 'neq', 'Other');
            }
        }







        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_RAW);

        $this->add_action_buttons(true, get_string('submit', 'spark_admission'));
    }

    public function validation($data, $files) {
        $errors = array();
        return $errors;
    }
}