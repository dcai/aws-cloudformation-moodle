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
 * @subpackage spark_schedule
 */

function xmldb_spark_admission_install() {
    global $DB;

    $systemcontext = context_system::instance();

    $systemroles = array(
        'dataclerk',
    );
    foreach ($systemroles as $systemrole) {
        if ($role = $DB->get_record('role', array('shortname' => $systemrole))) {
            assign_capability('spark/admission:processapplication', CAP_ALLOW, $role->id, $systemcontext->id, true);
            assign_capability('spark/admission:deleteapplication', CAP_ALLOW, $role->id, $systemcontext->id, true);
        }
    }

    $systemroles = array(
        'principal',
        'exec'
    );
    foreach ($systemroles as $systemrole) {
        if ($role = $DB->get_record('role', array('shortname' => $systemrole))) {
            assign_capability('spark/admission:applicationsetting', CAP_ALLOW, $role->id, $systemcontext->id, true);
            assign_capability('spark/admission:manageapplication', CAP_ALLOW, $role->id, $systemcontext->id, true);
            assign_capability('spark/admission:deleteapplication', CAP_ALLOW, $role->id, $systemcontext->id, true);
        }
    }
    return true;


    $sql = "INSERT INTO `{spark_admission_app_fields}` (`id`, `name`, `label`, `placeholder`, `mask`, `helptext`, `defaultvalue`, `options`, `disabled`, `required`, `optional`, `timecreated`, `timemodified`) VALUES
(1, 'student_first_name', 'Name', '', '', 'First Name', '', '', 0, 1, 0, 0, 0),
(2, 'student_middle_name', '', '', '', 'Middle Name', '', '', 0, 0, 0, 0, 0),
(3, 'student_last_name', '', '', '', 'Last Name', '', '', 0, 1, 0, 0, 0),
(4, 'student_gender', 'Gender', '', '', '', '', '', 0, 1, 1, 0, 0),
(5, 'student_race', 'Race/Ethnicity', '', '', '', '', 'A|Asian\r\nB|African-American\r\nW|Caucasian\r\nH|Hispanic\r\nN|Native Indian/Alaskan\r\nM|Multi-Racial\r\nO|Other', 0, 1, 1, 0, 0),
(6, 'school_applied_for', 'School Applied For', '', '', '', '', 'RCSA|RCSA Elementary (K-5)\r\nRCSAMH|RCSA Middle-High (6-12)', 0, 1, 1, 0, 0),
(7, 'grade_applied_for', 'Grade Applied For', '', '', '', '', '0|Kindergarten\r\n1|1st Grade\r\n2|2nd Grade\r\n3|3rd Grade\r\n4|4th Grade\r\n5|5th Grade\r\n6|6th Grade\r\n7|7th Grade\r\n8|8th Grade\r\n9|9th Grade\r\n10|10th Grade\r\n11|11th Grade\r\n12|12th Grade', 0, 1, 1, 0, 0),
(8, 'student_birth_date_month', 'Date of birth', '', '', '', '', '', 0, 1, 0, 0, 0),
(9, 'student_birth_date_day', '', '', '', '', '', '', 0, 1, 0, 0, 0),
(10, 'student_birth_date_year', '', '', '', '', '', '1999|1999\r\n2000|2000\r\n2001|2001\r\n2002|2002\r\n2003|2003\r\n2004|2004\r\n2005|2005\r\n2006|2006\r\n2007|2007\r\n2008|2008\r\n2009|2009\r\n2010|2010\r\n2011|2011', 0, 1, 0, 0, 0),
(11, 'parent_name', 'Name', '', '', '', '', '', 0, 1, 0, 0, 0),
(12, 'parent_relation', 'Relationship to Applicant', '', '', '', '', '', 0, 1, 0, 0, 0),
(13, 'address_street', 'Resident Address', '', '', 'Street Address', '', '', 0, 1, 0, 0, 0),
(14, 'address_city', '', '', '', 'City', '', '', 0, 1, 0, 0, 0),
(15, 'address_state', '', '', '\'mask\': \'A\', \'repeat\': 2, \'greedy\' : false', 'State / Province', '', '', 0, 1, 0, 0, 0),
(16, 'address_zip', '', '', '\'mask\': \'9\', \'repeat\': 5, \'greedy\' : false', 'Zip Code', '', '', 0, 1, 0, 0, 0),
(17, 'mailing_address', 'Mailing address', '', '', 'if different than resident address', '', '', 0, 0, 1, 0, 0),
(18, 'primary_email', 'Primary Email', '', '', 'A valid email address is required to complete this application. You will receive instructions in the email provided to confirm your application. If not confirmed within 7 days, submitted applications will be discarded.', '', '', 0, 1, 1, 0, 0),
(19, 'alternate_email', 'Alternate Email', '', '', '', '', '', 0, 0, 1, 0, 0),
(20, 'home_phone', 'Home phone', '', '\'mask\': \'(999) 999-9999\'', '', '', '', 0, 1, 1, 0, 0),
(21, 'work_phone', 'Work phone', '', '\'mask\': \'(999) 999-9999\'', '', '', '', 0, 0, 1, 0, 0),
(22, 'mobile_phone', 'Mobile phone', '', '\'mask\': \'(999) 999-9999\'', '', '', '', 0, 0, 1, 0, 0),
(23, 'current_school', 'Current School', '', '', '', '', 'Private School|Private School\r\nPublic School|Public School\r\nCharter School|Charter School\r\nOther|Other', 0, 0, 1, 0, 0),
(24, 'current_school_other', '', 'Please Specify', '', '', '', '', 0, 0, 1, 0, 0),
(25, 'sibling', 'Do you currently have a student attending our school?', '', '', '', '', '', 0, 0, 1, 0, 0),
(26, 'sibling_name', '', 'if Yes, Sibling\'s full name', '', '', '', '', 0, 1, 1, 0, 0),
(27, 'sibling_grade', '', 'Grade', '', '', '', '', 0, 1, 1, 0, 0),
(28, 'placement', 'Are any of your other family members applying for placement at our school this year?', '', '', '', '', '', 0, 0, 1, 0, 0),
(29, 'placement_name', '', 'if Yes, Sibling\'s full name', '', '', '', '', 0, 0, 1, 0, 0),
(30, 'placement_grade', '', 'Grade', '', '', '', '', 0, 0, 1, 0, 0),
(31, 'esol', 'Is the applicant receiving ESOL services?', '', '', '', '', '', 0, 0, 1, 0, 0),
(32, 'ese', 'Is the applicant receiving Sp. Ed. services (ESE)?', '', '', '', '', '', 0, 0, 1, 0, 0),
(33, 'ese_text', '', 'If Yes, Please Specify', '', '', '', '', 0, 0, 1, 0, 0),
(34, 'gifted', 'Does the student have a Gifted Educational Plan?', '', '', '', '', '', 0, 0, 1, 0, 0),
(35, 'gifted_text', '', 'If Yes, Please Specify', '', '', '', '', 0, 0, 1, 0, 0),
(36, 'expulsion', 'Is the applicant currently under expulsion or has ever expelled before?', '', '', '', '', '', 0, 0, 1, 0, 0),
(37, 'skipped', 'Has applicant ever skipped grade?', '', '', '', '', '', 0, 0, 1, 0, 0),
(38, 'skipped_text', '', 'If Yes, Please Specify', '', '', '', '', 0, 0, 1, 0, 0),
(39, 'retained', 'Has applicant ever retained grade?', '', '', '', '', '', 0, 0, 1, 0, 0),
(40, 'retained_text', 'text', 'If Yes, Please Specify', '', '', '', '', 0, 0, 1, 0, 0),
(41, 'vpk', 'Did the student attend a VPK?', '', '', '', '', '', 0, 0, 1, 0, 0),
(42, 'vpk_text', '', 'If yes, What is the name of the VPK your child attended?', '', '', '', '', 0, 0, 1, 0, 0),
(43, 'hear', 'How did you hear about us?', '', '', '', '', 'Facebook|Facebook\r\nGoogle|Google\r\nOther|Other', 0, 0, 1, 0, 0),
(44, 'hear_other', '', 'If Other,  specify', '', '', '', '', 0, 0, 1, 0, 0),
(45, 'school_year_applied_for', 'School Year Applied For', '', '', '', '', '2014-2015|2014-2015|N\r\n2015-2016|2015-2016|Y\r\n2016-2017|2016-2017(Current Year)', 0, 1, 0, NULL, 0),
(46, 'transportation', 'Transportation', '', '', '', '', 'Self|Self\r\nBus|Bus\r\nOther|Other', 1, 0, 0, 0, 0)";

    //$DB->execute($sql, array('?','?','?','?','?','?','?','?','?','?','?'));

    return true;
}