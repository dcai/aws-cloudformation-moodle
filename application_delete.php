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

require_once('../../config.php');

$id = optional_param('id', 0, PARAM_INT);
$process = optional_param('process', 0, PARAM_INT);

require_login(null, false);

$contextsystem = context_system::instance();

require_capability('spark/admission:deleteapplication', $contextsystem);

$PAGE->set_url('/spark/admission/application_delete.php', array('id' => $id));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('course');
$title = get_string('delete', 'spark_admission');
$heading = $SITE->fullname;
$PAGE->set_title($heading);
$PAGE->set_heading($heading);

$PAGE->navbar->add(get_string('pluginname', 'spark_admission'));
$PAGE->navbar->add(
    get_string('applications', 'spark_admission'),
    new moodle_url('/spark/admission/application.php')
);
$PAGE->navbar->add($title);

$application = $DB->get_record('spark_admission_app',
    array('id' => $id, 'deleted' => 0), '*', MUST_EXIST
);
if ($process) {
    require_sesskey();
    $rec = new stdClass();
    $rec->id = $application->id;
    $rec->deleted = 1;
    $rec->timemodified = time();
    $DB->update_record('spark_admission_app', $rec);

    redirect(new moodle_url('/spark/admission/application.php'),
        get_string('successful', 'spark_admission'), 1
    );
    die;
} else {
    echo $OUTPUT->header();
    echo html_writer::tag('h1', $title, array('class' => 'page-title'));
    echo $OUTPUT->confirm('<div><strong>'.
        get_string('application', 'spark_admission').': </strong>'.$application->student_first_name.' '.$application->student_last_name.
        '<br><br>'.
        '</div>'.
        get_string('deleteconfirmmsg', 'spark_admission').'<br><br>',
        new moodle_url('/spark/admission/application_delete.php',
            array('id' => $id, 'process' => 1)), '/spark/discipline/violation.php');
    echo $OUTPUT->footer();
}