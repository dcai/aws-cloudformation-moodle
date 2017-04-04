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

class communication_log_form extends moodleform {
    public function definition() {
        global $DB;

        $mform = $this->_form;
        $customdata = $this->_customdata;

        $mform->addElement('header', '', get_string('communicationlog', 'spark_admission'), '');

        $mform->addElement('select', 'logtype', get_string('logtype', 'spark_admission'),
            array(
                'call' => get_string('call', 'spark_admission'),
                'email' => get_string('email', 'spark_admission'),
                'letter' => get_string('letter', 'spark_admission'),
            ),
            null
        );
        $mform->addRule('logtype', null, 'required', null, 'client');

        $mform->addElement('textarea', 'note', get_string('note', 'spark_admission'), 'wrap="virtual" rows="4" cols="60"');
        $mform->setType('note', PARAM_NOTAGS);
        $mform->addRule('note', null, 'required', null, 'client');


        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'applicationid');
        $mform->setType('applicationid', PARAM_INT);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_RAW);

        $this->add_action_buttons(true, get_string('submit', 'spark_admission'));
    }

    public function validation($data, $files) {
        $errors = array();
        return $errors;
    }
}