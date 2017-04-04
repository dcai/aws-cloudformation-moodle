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

class application_form extends moodleform {
    public function definition() {

        $mform = $this->_form;
        $customdata = $this->_customdata;

        $mform->addElement('header', '', get_string('form_field', 'spark_admission'), '');

        $mform->addElement('text', 'name', get_string('name', 'spark_admission'), array('size' => '60'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'label', get_string('label', 'spark_admission'), array('size' => '60'));
        $mform->setType('label', PARAM_TEXT);
        //$mform->addRule('label', null, 'required', null, 'client');

        $mform->addElement('text', 'placeholder', get_string('placeholder', 'spark_admission'), array('size' => '60'));
        $mform->setType('placeholder', PARAM_TEXT);
        //$mform->addRule('placeholder', null, 'required', null, 'client');

        $mform->addElement('text', 'defaultvalue', get_string('defaultvalue', 'spark_admission'), array('size' => '60'));
        $mform->setType('defaultvalue', PARAM_TEXT);
        //$mform->addRule('defaultvalue', null, 'required', null, 'client');

        $mform->addElement('text', 'mask', get_string('mask', 'spark_admission'), array('size' => '60'));
        $mform->setType('mask', PARAM_TEXT);
        //$mform->addRule('mask', null, 'required', null, 'client');

        $mform->addElement('textarea', 'options', get_string('options', 'spark_admission'), 'wrap="virtual" rows="5" cols="60"');
        $mform->setType('options', PARAM_NOTAGS);
        //$mform->addRule('options', null, 'required', null, 'client');

        $mform->addElement('textarea', 'helptext', get_string('helptext', 'spark_admission'), 'wrap="virtual" rows="5" cols="60"');
        $mform->setType('helptext', PARAM_NOTAGS);
        //$mform->addRule('helptext', null, 'required', null, 'client');

        if ($customdata['optional']) {
            $mform->addElement('selectyesno', 'disabled', get_string('disabled', 'spark_admission'));
            $mform->setType('disabled', PARAM_INT);
            $mform->addRule('disabled', null, 'required', null, 'client');
        }

        $mform->addElement('selectyesno', 'required', get_string('required', 'spark_admission'));
        $mform->setType('required', PARAM_INT);
        $mform->addRule('required', null, 'required', null, 'client');

        if (is_siteadmin() && false) {
            $mform->addElement('selectyesno', 'optional', get_string('optional', 'spark_admission'));
            $mform->setType('optional', PARAM_INT);
            $mform->addRule('optional', null, 'required', null, 'client');
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