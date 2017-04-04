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

function xmldb_spark_admission_upgrade($version) {
    global $CFG, $DB;

    if ($version < 2016101200) {
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
    }

    return true;
}