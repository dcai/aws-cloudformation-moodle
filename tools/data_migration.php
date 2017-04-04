<?php
/*
 * This file is part of Spark LMS
 *
 * Copyright (C) 2010 onwards Spark Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Mustafa Bahcaci <mbahcaci@charterresources.us>
 * @package spark
 * @subpackage spark_assignment
 */

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
//require_once($CFG->libdir.'/adminlib.php');
//require_once($CFG->dirroot . '/spark/assignment/lib.php');

set_time_limit(0);

require_login();

if (!is_siteadmin()) {
    error('Permission');
}
$dbclass = get_class($DB);

$DB_APP = new mysqli_native_moodle_database(true);

$DB_APP->connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass, 'app_rcsax', '');
$totalnumber = $DB_APP->count_records_sql("SELECT COUNT(id) FROM app_application", null);
$counter = 0;
$applications = $DB_APP->get_recordset_sql("SELECT * FROM app_application ORDER BY id ASC", null);

echo "STARTED ".date("Y-m-d H:i:s").'<br>';
foreach ($applications as $application) {
    $counter++;
    $rec = new stdClass();
    $rec->student_first_name = $application->firstname;
    $rec->student_middle_name = $application->middlename;
    $rec->student_last_name = $application->lastname;
    $rec->student_gender = $application->sex;
    $rec->student_race = $application->race;
    if (!empty($application->applied_school)) {
        $rec->school_applied_for = $application->applied_school;
    }

    if ($application->applied_year) {
        if ($appliedyear = $DB_APP->get_record('app_applied_year', array('id' => $application->applied_year))) {
            $rec->school_year_applied_for = $appliedyear->applied_year;
        }
    }

    $rec->grade_applied_for = (($application->gradelevel-1) == 0) ? 'K' : ($application->gradelevel-1);
    $rec->student_birth_date_month = $application->dob_mm;
    $rec->student_birth_date_day = $application->dob_dd;
    $rec->student_birth_date_year = $application->dob_yyyy;
    $rec->parent_name = $application->parent_guardian;
    $rec->parent_relation = $application->relation_to_student;
    $rec->address_street = $application->address;
    $rec->address_city = $application->city;
    $rec->address_state = substr(strtoupper($application->state), 0, 2);
    $rec->address_zip = $application->zip;
    $rec->mailing_address = $application->mailing_address;
    $rec->primary_email = $application->primary_email;
    $rec->alternate_email = $application->alternate_email;
    $rec->home_phone = $application->home_phone;
    $rec->work_phone = $application->work_phone;
    $rec->mobile_phone = $application->mobile_phone;

    if ($application->current_school) {
        if ($currentschool = $DB_APP->get_record('app_schools', array('school_id' => $application->current_school))) {
            $rec->current_school = $currentschool->school_name;
        }
    }

    $rec->current_school_other = $application->other_school;
    if (isset($application->sibling)) $rec->sibling = ($application->sibling == 'Y') ? 1 : 0;
    if (isset($application->sibling_name)) $rec->sibling_name = $application->sibling_name;
    if (isset($application->sibling_grade)) $rec->sibling_grade = $application->sibling_grade;

    if (isset($application->placement)){
        if (strtoupper($application->placement) == 'Y') {
            $application->placement = 1;
        } else {
            $application->placement = 0;
        }
    }
    if (isset($application->placement)) $rec->placement = $application->placement;
    if (isset($application->placement_name)) $rec->placement_name = $application->placement_name;
    if (isset($application->placement_grade)) $rec->placement_grade = $application->placement_grade;
    $rec->esol = ($application->esol == 'Y') ? 1 : 0;
    $rec->ese = ($application->special_ed == 'Y') ? 1: 0;
    $rec->ese_text = $application->special_ed_text;
    $rec->gifted = ($application->tag == 'Y') ? 1 : 0;
    $rec->gifted_text = $application->tag_text;
    $rec->expulsion = ($application->expulsion == 'Y') ? 1 : 0;
    $rec->skipped = ($application->skipped_grade == 'Y') ? 1 : 0;
    $rec->skipped_text = $application->skipped_grade_text;
    $rec->retained = ($application->retained_grade == 'Y') ? 1 : 0;
    $rec->retained_text = $application->retained_grade_text;
    if (isset($application->vpk)) $rec->vpk = ($application->vpk == 'Y') ? 1 : 0;
    if (isset($application->vpk_text)) $rec->vpk_text = $application->vpk_text;

    if ($application->hear) {
        if ($hear = $DB_APP->get_record('app_hear', array('hear_id' => $application->hear))) {
            $rec->hear = $hear->hear;
        }
    }

    if (isset($application->hear_other)) $rec->hear_other = $application->hear_other;
    $rec->approved = ($application->approved == 'Y') ? 1 : 0;
    $rec->confirmed = ($application->confirmed == 'Y') ? 1 : 0;
    if (!empty($application->withdrawapplication)) {
        $rec->withdraw_date = strtotime($application->withdrawapplication);
    } else {
        $rec->withdraw_date = 0;
    }
    $rec->withdraw_reason = $application->withdrawreason;
    if (!empty($application->enrolling)) {
        $rec->enrollment_date = strtotime($application->enrolling);
    } else {
        $rec->enrollment_date = 0;
    }
    $rec->note = $application->notes;

    if ($application->transportation) {
        if ($transportation = $DB_APP->get_record('app_transportation', array('id' => $application->transportation))) {
            $rec->transportation = $transportation->transportation;
        }
    }

    $rec->idnumber = $application->student_id;

    $rec->deleted = 0;
    if (!empty($application->entry_date)) {
        $rec->timecreated = strtotime($application->entry_date .' '. $application->entry_time);
    } else {
        $rec->timecreated = 0;
    }

    $rec->timemodified = 0;

    $rec->id = $DB->insert_record('spark_admission_app', $rec);

    if ($callogs  = $DB_APP->get_records('app_call_log', array('application_id' => $application->id))) {
        foreach ($callogs as $callog) {
            $log = new stdClass();
            $log->applicationid = $rec->id;
            $log->logtype = 'call';
            $log->note = $callog->call_note;
            $log->userid = '';
            $log->deleted = 0;
            $log->timecreated = strtotime($callog->call_date .' '. $callog->call_time);
            $log->timemodified = 0;
            $DB->insert_record('spark_admission_app_log', $log);
        }
    }
    if ($emailogs  = $DB_APP->get_records('app_sent_email_log', array('application_id' => $application->id, 'is_server_email' => 0))) {
        foreach ($emailogs as $emaillog) {
            $log = new stdClass();
            $log->applicationid = $rec->id;
            $log->logtype = 'email';
            $log->note = $emaillog->note;
            $log->userid = '';
            $log->deleted = 0;
            $log->timecreated = strtotime($emaillog->sent_date .' '. $emaillog->sent_time);
            $log->timemodified = 0;
            $DB->insert_record('spark_admission_app_log', $log);
        }
    }
    if ($letterlogs  = $DB_APP->get_records('app_sent_letter_log', array('application_id' => $application->id))) {
        foreach ($letterlogs as $letterlog) {
            $log = new stdClass();
            $log->applicationid = $rec->id;
            $log->logtype = 'letter';
            $log->note = $letterlog->note;
            $log->userid = '';
            $log->deleted = 0;
            $log->timecreated = strtotime($letterlog->sent_date);
            $log->timemodified = 0;
            $DB->insert_record('spark_admission_app_log', $log);
        }
    }
    if (($counter % 100) == 0) {
        echo "<br>$counter of $totalnumber <br>.";
    } else {
        echo '.';
    }

}
$applications->close();
echo "<br>ENDED ".date("Y-m-d H:i:s").'<br>';