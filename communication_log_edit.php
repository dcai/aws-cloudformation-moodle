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
require_once('communication_log_form.php');

$id            = optional_param('id', 0, PARAM_INT);
$applicationid = required_param('applicationid', PARAM_INT);
$action        = optional_param('action', 'edit', PARAM_RAW);

require_login(null, false);

$contextsystem = context_system::instance();

// Permission.
if (!has_capability('spark/admission:manageapplication', $contextsystem) && !has_capability('spark/admission:processapplication', $contextsystem)) {
    print_error('permissionerror', 'spark_admission');
}

$PAGE->https_required();

$thispageurl = new moodle_url('/spark/admission/communication_log_edit.php', array('id' => $id, 'applicationid' => $applicationid, 'action' => $action));

$PAGE->set_url($thispageurl);
$PAGE->set_pagelayout('course');
$PAGE->set_context($contextsystem);
$PAGE->verify_https_required();

$name = get_string('addedit', 'spark_admission');
$title = get_string('addedit', 'spark_admission');
$heading = $SITE->fullname;

// Breadcrumb.
$PAGE->navbar->add(get_string('pluginname', 'spark_admission'));
$PAGE->navbar->add(get_string('applications', 'spark_admission'),
    new moodle_url('/spark/admission/application.php'));
$PAGE->navbar->add(get_string('communicationlog', 'spark_admission'),
    new moodle_url('/spark/admission/communication_log.php', array('id' => $applicationid)));
$PAGE->navbar->add($name);

$PAGE->set_title($title);
$PAGE->set_heading($heading);

// Data Clerk can view approved applications.
if (has_capability('spark/admission:processapplication', $contextsystem, null, false)) {
    $app = $DB->get_record('spark_admission_app', array('id' => $applicationid, 'approved' => 1, 'deleted' => 0), '*', MUST_EXIST);
} else {
    $app = $DB->get_record('spark_admission_app', array('id' => $applicationid, 'deleted' => 0), '*', MUST_EXIST);
}

if ($action == 'edit') {
    $toform = $DB->get_record('spark_admission_app_log', array('id' => $id, 'deleted' => 0), '*', MUST_EXIST);
    $mform = new communication_log_form(null, (array)$toform);
} else {
    $mform = new communication_log_form(null, array());
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/spark/admission/communication_log.php', array('id' => $applicationid)));
} else if ($fromform = $mform->get_data()) {
    if ($action == 'add') {
        $fromform->userid = $USER->id;
        $fromform->timecreated = time();
        $fromform->id = $DB->insert_record('spark_admission_app_log', $fromform);
        redirect(new moodle_url('/spark/admission/communication_log.php', array('id' => $applicationid)), get_string('successful', 'spark_admission'), 0);
    } else {
        $fromform->id = $id;
        $fromform->timemodified = time();
        $DB->update_record('spark_admission_app_log', $fromform);
        redirect(new moodle_url('/spark/admission/communication_log.php', array('id' => $applicationid)), get_string('successful', 'spark_admission'), 0);
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
    $toform->applicationid = $applicationid;
    $mform->set_data($toform);
}

$mform->display();

echo $OUTPUT->footer();