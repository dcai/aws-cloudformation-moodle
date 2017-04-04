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


require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot.'/spark/admission/lib.php');
require_once('form_fields_form.php');

$id     = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', 'edit', PARAM_RAW);

require_login(null, false);

$contextsystem = context_system::instance();

// Permission.
require_capability('spark/admission:applicationsetting', $contextsystem);

$PAGE->https_required();

$thispageurl = new moodle_url('/spark/admission/form_fields_edit.php', array('id' => $id, 'action' => $action));

$PAGE->set_url($thispageurl);
$PAGE->set_pagelayout('course');
$PAGE->set_context($contextsystem);
$PAGE->verify_https_required();

$name = get_string('addedit', 'spark_admission');
$title = get_string('addedit', 'spark_admission');
$heading = $SITE->fullname;

// Breadcrumb.
$PAGE->navbar->add(get_string('pluginname', 'spark_admission'));
$PAGE->navbar->add(get_string('form_fields', 'spark_admission'), new moodle_url('/spark/admission/form_fields.php'));
$PAGE->navbar->add($name);

$PAGE->set_title($title);
$PAGE->set_heading($heading);

if ($action == 'edit') {
    $toform = $DB->get_record('spark_admission_app_fields', array('id' => $id), '*', MUST_EXIST);
    $mform = new application_form(null, (array)$toform);
} else {
    $mform = new application_form(null, array());
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/spark/admission/form_fields.php'));
} else if ($fromform = $mform->get_data()) {
    if ($action == 'add') {
        $fromform->timecreated = time();
        $fromform->id = $DB->insert_record('spark_admission_app_fields', $fromform);
        redirect(new moodle_url('/spark/admission/form_fields.php'), get_string('successful', 'spark_admission'), 0);
    } else {
        $fromform->id = $id;
        $fromform->timemodified = time();
        $DB->update_record('spark_admission_app_fields', $fromform);
        redirect(new moodle_url('/spark/admission/form_fields.php'), get_string('successful', 'spark_admission'), 0);
    }
    exit;
}

echo $OUTPUT->header();

if (($action == 'edit') && ($id)) {
    $toform->action = $action;
    $mform->set_data($toform);
} else {
    $toform = new stdClass();
    $toform->action = $action;
    $mform->set_data($toform);
}

$mform->display();

echo $OUTPUT->footer();