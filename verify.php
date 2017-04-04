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

$token = optional_param('t', '', PARAM_RAW);
if ($token) {
    if ($application = $DB->get_record('spark_admission_app', array('token' => $token))) {
        // Success.
        $rec = new stdClass();
        $rec->id = $application->id;
        $rec->confirmed = 1;
        $DB->update_record('spark_admission_app', $rec);

        echo 'Your email address has been confirmed.';
    } else {
        // Invalid token.
        echo 'Invalid token!';
    }
} else {
    // Invalid parameter.
    echo 'Invalid parameter!';
}
