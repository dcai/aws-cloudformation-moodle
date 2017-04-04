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
$applicationid = optional_param('applicationid', 0, PARAM_INT);
$process = optional_param('process', 0, PARAM_INT);

require_login(null, false);

$contextsystem = context_system::instance();

require_capability('spark/admission:deleteapplication', $contextsystem);

$PAGE->set_url('/spark/admission/communication_log_delete.php', array('id' => $id, 'applicationid' => $applicationid));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('course');
$title = get_string('delete', 'spark_admission');
$heading = $SITE->fullname;
$PAGE->set_title($heading);
$PAGE->set_heading($heading);

$PAGE->navbar->add(
    get_string('pluginname', 'spark_admission'),
    new moodle_url('/spark/admission/application.php')
);
$PAGE->navbar->add(
    get_string('communicationlog', 'spark_admission'),
    new moodle_url('/spark/admission/communication_log.php', array('id' => $applicationid))
);
$PAGE->navbar->add($title);

if (has_capability('spark/admission:processapplication', $contextsystem, null, false)) {
    $app = $DB->get_record('spark_admission_app', array('id' => $applicationid, 'approved' => 1, 'deleted' => 0), '*', MUST_EXIST);
} else {
    $app = $DB->get_record('spark_admission_app', array('id' => $applicationid, 'deleted' => 0), '*', MUST_EXIST);
}
$applicationlog = $DB->get_record('spark_admission_app_log',
    array('id' => $id, 'applicationid' => $applicationid, 'deleted' => 0), '*', MUST_EXIST
);
if ($process) {
    require_sesskey();
    $rec = new stdClass();
    $rec->id = $applicationlog->id;
    $rec->deleted = 1;
    $rec->timemodified = time();
    $DB->update_record('spark_admission_app_log', $rec);

    redirect(new moodle_url('/spark/admission/communication_log.php', array('id' =>$applicationid)),
        get_string('successful', 'spark_admission'), 1
    );
    die;
} else {
    echo $OUTPUT->header();
    echo html_writer::tag('h1', $title, array('class' => 'page-title'));
    echo $OUTPUT->confirm('<div><strong>'.
        get_string('logtype', 'spark_admission').': </strong>'.get_string($applicationlog->logtype, 'spark_admission').
        '<br><br><strong>'.
        get_string('note', 'spark_admission').': </strong><br>'.$applicationlog->note.
        '<br><br>'.
        '</div>'.
        get_string('deleteconfirmmsg', 'spark_admission').'<br><br>',
        new moodle_url('/spark/admission/communication_log_delete.php',
            array('id' => $id, 'applicationid' => $applicationid, 'process' => 1)
        ),
        new moodle_url('/spark/admission/communication_log.php',
            array('id' => $applicationid)
        )
    );
    echo $OUTPUT->footer();
}