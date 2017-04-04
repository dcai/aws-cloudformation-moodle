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
require_once($CFG->dirroot.'/lib/excellib.class.php');
require_once($CFG->dirroot.'/spark/admission/lib.php');
require_once($CFG->libdir.'/adminlib.php');

// Paging options.
$page      = optional_param('page', 0, PARAM_INT);
$perpage   = optional_param('perpage', 20, PARAM_INT);
$sort      = optional_param('sort', 'timecreated', PARAM_RAW);
$dir       = optional_param('dir', 'DESC', PARAM_ALPHA);
// Action.
$action    = optional_param('action', false, PARAM_ALPHA);
// Filters.
$filter_school_applied_for      = optional_param('school_applied_for', '', PARAM_TEXT);
$filter_school_year_applied_for = optional_param('school_year_applied_for', '', PARAM_TEXT);
$filter_student_gender          = optional_param('student_gender', '', PARAM_TEXT);
$filter_student_race            = optional_param('student_race', '', PARAM_TEXT);
$filter_grade_applied_for       = optional_param('grade_applied_for', '', PARAM_TEXT);
$filter_hear                    = optional_param('hear', '', PARAM_TEXT);
$filter_transportation          = optional_param('transportation', '', PARAM_TEXT);
$filter_approved               = optional_param('approved', 0, PARAM_INT);

$search                         = optional_param('search', '', PARAM_TEXT);

require_login(null, false);
$contextsystem = context_system::instance();

// Permission.
if (!has_capability('spark/admission:manageapplication', $contextsystem)
    && !has_capability('spark/admission:processapplication', $contextsystem)) {
    print_error('permissionerror', 'spark_admission');
}

$thispageurl = new moodle_url('/spark/admission/application.php');

$PAGE->set_url($thispageurl);
$PAGE->set_pagelayout('course');
$PAGE->set_context($contextsystem);
$PAGE->requires->css('/spark/admission/css/application.css');

$name = get_string('applications', 'spark_admission');
$title = get_string('applications', 'spark_admission');
$heading = $SITE->fullname;

// Breadcrumb.
//$PAGE->navbar->add(get_string('pluginname', 'spark_admission'));
//$PAGE->navbar->add($name);
admin_externalpage_setup('application', null, null);

$PAGE->set_title($title);
$PAGE->set_heading($heading);

$datacolumns = array(
    'id' => 'a.id',
    'student_first_name' => 'a.student_first_name',
    'student_middle_name' => 'a.student_middle_name',
    'student_last_name' => 'a.student_last_name',
    'student_gender' => 'a.student_gender',
    'student_race' => 'a.student_race',
    'idnumber' => 'a.idnumber',
    'transportation' => 'a.transportation',
    'school_applied_for' => 'a.school_applied_for',
    'school_year_applied_for' => 'a.school_year_applied_for',
    'grade_applied_for' => 'a.grade_applied_for',
    'student_birth_date_month' => 'a.student_birth_date_month',
    'student_birth_date_day' => 'a.student_birth_date_day',
    'student_birth_date_year' => 'a.student_birth_date_year',
    'parent_name' => 'a.parent_name',
    'parent_relation' => 'a.parent_relation',
    'address_street' => 'a.address_street',
    'address_city' => 'a.address_city',
    'address_state' => 'a.address_state',
    'address_zip' => 'a.address_zip',
    'mailing_address' => 'a.mailing_address',
    'primary_email' => 'a.primary_email',
    'alternate_email' => 'a.alternate_email',
    'home_phone' => 'a.home_phone',
    'work_phone' => 'a.work_phone',
    'mobile_phone' => 'a.mobile_phone',
    'current_school' => 'a.current_school',
    'current_school_other' => 'a.current_school_other',
    'sibling' => 'a.sibling',
    'sibling_name' => 'a.sibling_name',
    'sibling_grade' => 'a.sibling_grade',
    'placement' => 'a.placement',
    'placement_name' => 'a.placement_name',
    'placement_grade' => 'a.placement_grade',
    'esol' => 'a.esol',
    'ese' => 'a.ese',
    'ese_text' => 'a.ese_text',
    'gifted' => 'a.gifted',
    'gifted_text' => 'a.gifted_text',
    'expulsion' => 'a.expulsion',
    'skipped' => 'a.skipped',
    'skipped_text' => 'a.skipped_text',
    'retained' => 'a.retained',
    'retained_text' => 'a.retained_text',
    'vpk' => 'a.vpk',
    'vpk_text' => 'a.vpk_text',
    'hear' => 'a.hear',
    'hear_other' => 'a.hear_other',
    'approved' => 'a.approved',
    'confirmed' => 'a.confirmed',
    'withdraw_date' => 'a.withdraw_date',
    'withdraw_reason' => 'a.withdraw_reason',
    'enrollment_date' => 'a.enrollment_date',
    'note' => 'a.note',
    'token' => 'a.token',
    'timecreated' => 'a.timecreated',
    'timemodified' => 'a.timemodified',
);


// Filter.
$where = '';
// Data Clerk can view approved applications.
if (has_capability('spark/admission:processapplication', $contextsystem, null, false)) {
    $where .= " AND ".$datacolumns['approved']." = 1";
}
if ($search) {
    $where .= " AND (".$datacolumns['student_first_name']." LIKE '%$search%'".
        " OR ".$datacolumns['student_last_name']." LIKE '%$search%'".
        " OR ".$datacolumns['parent_name']." LIKE '%$search%'".
        " OR ".$datacolumns['address_street']." LIKE '%$search%'".
        " OR ".$datacolumns['address_city']." LIKE '%$search%'".
        " OR ".$datacolumns['address_zip']." LIKE '%$search%'".
        " OR ".$datacolumns['mailing_address']." LIKE '%$search%'".
        " OR ".$datacolumns['primary_email']." LIKE '%$search%'".
        " OR ".$datacolumns['alternate_email']." LIKE '%$search%'".
        " OR ".$datacolumns['sibling_name']." LIKE '%$search%'".
        " OR ".$datacolumns['current_school']." LIKE '%$search%'".
        " OR ".$datacolumns['current_school_other']." LIKE '%$search%'".
        ")";
}
if ($filter_school_applied_for) {
    $where .= " AND ".$datacolumns['school_applied_for']." = '$filter_school_applied_for'";
}
if ($filter_school_year_applied_for) {
    $where .= " AND ".$datacolumns['school_year_applied_for']." = '$filter_school_year_applied_for'";
}
if ($filter_student_gender) {
    $where .= " AND ".$datacolumns['student_gender']." = '$filter_student_gender'";
}
if ($filter_student_race) {
    $where .= " AND ".$datacolumns['student_race']." = '$filter_student_race'";
}
if ($filter_grade_applied_for) {
    $where .= " AND ".$datacolumns['grade_applied_for']." = '$filter_grade_applied_for'";
}
if ($filter_hear) {
    $where .= " AND ".$datacolumns['hear']." = '$filter_hear'";
}
if ($filter_transportation) {
    $where .= " AND ".$datacolumns['transportation']." = '$filter_transportation'";
}
if ($filter_approved) {
    $where .= " AND ".$datacolumns['approved']." = $filter_approved";
}

// Sort.
$order = '';
if ($sort) {
    $order = " ORDER BY $datacolumns[$sort] $dir";
}

// Count records for paging.
$countsql = "SELECT COUNT(1) FROM {spark_admission_app} a WHERE a.deleted = 0 $where";
$totalcount = $DB->count_records_sql($countsql);

// Table columns.
$columns = array(
    'rowcount',
    'action',
    'student_first_name',
    'student_middle_name',
    'student_last_name',
    'student_gender',
    'student_race',
    'school_applied_for',
    'school_year_applied_for',
    'grade_applied_for',
    'student_birth_date_month',
    'student_birth_date_day',
    'student_birth_date_year',
    'parent_name',
    'parent_relation',
    'address_street',
    'address_city',
    'address_state',
    'address_zip',
    'mailing_address',
    'primary_email',
    'alternate_email',
    'home_phone',
    'work_phone',
    'mobile_phone',
    'current_school',
    'current_school_other',
    'sibling',
    'sibling_name',
    'sibling_grade',
    'placement',
    'placement_name',
    'placement_grade',
    'esol',
    'ese',
    'ese_text',
    'gifted',
    'gifted_text',
    'expulsion',
    'skipped',
    'skipped_text',
    'retained',
    'retained_text',
    'vpk',
    'vpk_text',
    'hear',
    'hear_other',
    'approved',
    'confirmed',
    'idnumber',
    'transportation',
    'withdraw_date',
    'withdraw_reason',
    'enrollment_date',
    'note',
    'timecreated',
    'action2',
);
$columns_export = array(
    'rowcount',
    'id',
    'student_first_name',
    'student_middle_name',
    'student_last_name',
    'student_gender',
    'student_race',
    'school_applied_for',
    'school_year_applied_for',
    'grade_applied_for',
    'student_birth_date_month',
    'student_birth_date_day',
    'student_birth_date_year',
    'parent_name',
    'parent_relation',
    'address_street',
    'address_city',
    'address_state',
    'address_zip',
    'mailing_address',
    'primary_email',
    'alternate_email',
    'home_phone',
    'work_phone',
    'mobile_phone',
    'current_school',
    'current_school_other',
    'sibling',
    'sibling_name',
    'sibling_grade',
    'placement',
    'placement_name',
    'placement_grade',
    'esol',
    'ese',
    'ese_text',
    'gifted',
    'gifted_text',
    'expulsion',
    'skipped',
    'skipped_text',
    'retained',
    'retained_text',
    'vpk',
    'vpk_text',
    'hear',
    'hear_other',
    'approved',
    'confirmed',
    'idnumber',
    'transportation',
    'withdraw_date',
    'withdraw_reason',
    'enrollment_date',
    'note',
    'timecreated',
    'action'
);

$formfields = $DB->get_records('spark_admission_app_fields');

foreach ($formfields as $formfield) {
    if ($formfield->disabled) {
        foreach ($columns as $index => $column) {
            if ($formfield->name == $column) {
                unset($columns[$index]);
            }
        }
        foreach ($columns_export as $index => $column) {
            if ($formfield->name == $column) {
                unset($columns_export[$index]);
            }
        }
    }
}

$sql = "SELECT a.*               
          FROM {spark_admission_app} a 
         WHERE a.deleted = 0
               $where
               $order";

if ($action == '_excel_') {
    set_time_limit(300);
    raise_memory_limit(MEMORY_EXTRA);

    $table = new stdClass();
    $table->head = $columns_export;

    // Delete first rowcount column.
    $itemid = array_shift($table->head);
    // Delete last action column.
    array_pop($table->head);

    $counter = 0;

    $table->data = array();

    $rs = $DB->get_recordset_sql($sql);
    foreach ($rs as $tablerow) {
        $row = array();
        foreach ($table->head as $column) {
            if (($column == 'timecreated')
                || ($column == 'timemodified')
                || ($column == 'withdraw_date')
                || ($column == 'enrollment_date')
            ) {
                if ($tablerow->$column > 0) {
                    $row[] = date("m/d/Y g:i A", $tablerow->$column);
                } else {
                    $row[] = '-';
                }
            } else {
                $row[] = $tablerow->$column;
            }
        }
        $table->data[] = $row;
    }
    $rs->close();

    $matrix = array();
    $filename = basename($thispageurl->out(), '.php').'_'.(time()).'.xls';

    if (!empty($table->head)) {
        $countcols = count($table->head);
        $keys = array_keys($table->head);
        $lastkey = end($keys);
        foreach ($table->head as $key => $heading) {
            $matrix[0][$key] = str_replace("\n",
                ' ',
                htmlspecialchars_decode(strip_tags(nl2br(get_string($heading, 'spark_admission'))))
            );
        }
    }

    if (!empty($table->data)) {
        foreach ($table->data as $rkey => $row) {
            foreach ($row as $key => $item) {
                $matrix[$rkey + 1][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($item))));
            }
        }
    }
    ob_clean();
    $downloadfilename = clean_filename($filename);
    // Creating a workbook.
    $workbook = new MoodleExcelWorkbook("-");
    // Sending HTTP headers.
    $workbook->send($downloadfilename);
    // Adding the worksheet.
    $myxls = $workbook->add_worksheet($filename);
    foreach ($matrix as $ri => $col) {
        foreach ($col as $ci => $cv) {
            $myxls->write_string($ri, $ci, $cv);
        }
    }
    $workbook->close();
    exit;
} else if ($action == 'csv') {
    set_time_limit(300);
    raise_memory_limit(MEMORY_EXTRA);
    $table = new stdClass();

    // Delete firs rowcount column.
    array_shift($columns_export);
    // Delete last action column.
    array_pop($columns_export);

    $headers = $columns_export;

    foreach ($headers as $ckey => $column) {
        $headers[$ckey] = get_string($column, 'spark_admission');
    }

    ob_clean();
    // Output headers so that the file is downloaded rather than displayed.
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=data.csv');

    // Create a file pointer connected to the output stream.
    $outputcsv = fopen('php://output', 'w');

    // Output the column headings.
    fputcsv($outputcsv, $headers);

    $tablerows = $DB->get_records_sql($sql);

    $counter = 0;

    $rs = $DB->get_recordset_sql($sql);
    foreach ($rs as $tablerow) {
        $row = array();
        foreach ($columns_export as $column) {
            if (($column == 'timecreated')
                || ($column == 'timemodified')
                || ($column == 'withdraw_date')
                || ($column == 'enrollment_date')
            ) {
                if ($tablerow->$column > 0) {
                    $row[] = date("m/d/Y g:i A", $tablerow->$column);
                } else {
                    $row[] = '-';
                }
            } else {
                $row[] = $tablerow->$column;
            }
        }
        fputcsv($outputcsv, $row);
    }
    $rs->close();
    exit;
} else {

    foreach ($columns as $column) {
        $string[$column] = get_string($column, 'spark_admission');
        if ($sort != $column) {
            $columnicon = "";
            if ($column == "name") {
                $columndir = "ASC";
            } else {
                $columndir = "ASC";
            }
        } else {
            $columndir = $dir == "ASC" ? "DESC" : "ASC";
            if ($column == "minpoint") {
                $columnicon = ($dir == "ASC") ? "sort_asc" : "sort_desc";
            } else {
                $columnicon = ($dir == "ASC") ? "sort_asc" : "sort_desc";
            }
            $columnicon = "<img class='iconsort' src=\"" . $OUTPUT->pix_url('t/' . $columnicon) . "\" alt=\"\" />";

        }
        if (($column == 'rowcount') || ($column == 'action') || ($column == 'action2')) {
            $$column = $string[$column];
        } else {
            $sorturl = $thispageurl;
            $sorturl->param('perpage', $perpage);
            $sorturl->param('sort', $column);
            $sorturl->param('dir', $columndir);
            $sorturl->param('search', $search);
            $sorturl->param('school_applied_for', $filter_school_applied_for);
            $sorturl->param('school_year_applied_for', $filter_school_year_applied_for);
            $sorturl->param('student_gender', $filter_student_gender);
            $sorturl->param('student_race', $filter_student_race);
            $sorturl->param('grade_applied_for', $filter_grade_applied_for);
            $sorturl->param('hear', $filter_hear);
            $sorturl->param('transportation', $filter_transportation);
            $sorturl->param('approved', $filter_approved);

            $$column = html_writer::link($sorturl->out(false), $string[$column]).$columnicon;
        }
    }

    $table = new html_table();

    $table->head = array();
    $table->wrap = array();
    foreach ($columns as $column) {
        $table->head[$column] = $$column;
        $table->wrap[$column] = 'nowrap';
    }

    // Override cell wrap.
    $table->wrap['action'] = 'nowrap';
    $table->wrap['action2'] = 'nowrap';

    $tablerows = $DB->get_records_sql($sql, null, $page * $perpage, $perpage);

    $counter = ($page * $perpage);

    foreach ($tablerows as $tablerow) {
        $row = new html_table_row();
        $actionlinks = '';
        foreach ($columns as $column) {
            $varname = 'cell'.$column;

            switch ($column) {
                case 'rowcount':
                    $$varname = ++$counter;
                    break;
                case 'withdraw_date':
                case 'enrollment_date':
                case 'timecreated':
                case 'timemodified':
                    $$varname = '-';
                    if ($tablerow->$column > 0) {
                        $$varname = new html_table_cell(date("m/d/Y g:i A", $tablerow->$column));
                    }
                    break;
                case 'sibling':
                case 'placement':
                case 'esol':
                case 'ese':
                case 'gifted':
                case 'expulsion':
                case 'skipped':
                case 'retained':
                case 'vpk':
                case 'approved':
                case 'confirmed':
                    if ($tablerow->$column > 0) {
                        $$varname = new html_table_cell(get_string('yes', 'spark_admission'));
                    } else {
                        $$varname = new html_table_cell('-');
                    }
                    break;
                case 'current_school_other':
                case 'ese_text':
                case 'gifted_text':
                case 'skipped_text':
                case 'retained_text':
                case 'vpk_text':
                case 'hear_other':
                    if ($tablerow->$column) {
                        $$varname = new html_table_cell(spark_admission_truncate($tablerow->$column, 20));
                    } else {
                        $$varname = '-';
                    }
                    break;
                case 'action':                    
                case 'action2':
                    $actionlinks = '';
                    // Communication log.
                    if (has_capability('spark/admission:manageapplication', $contextsystem) || has_capability('spark/admission:processapplication', $contextsystem)) {
                        $actionurl = new moodle_url('/spark/admission/communication_log.php', array('id' => $tablerow->id ));
                        $actioniconurl = $OUTPUT->pix_url('email_transfer', 'spark_admission');
                        $actionicontext = 'Edit';
                        $actionicon = html_writer::img($actioniconurl, $actionicontext, array('width' => '16', 'height' => '16'));
                        $actionlinks .= html_writer::link($actionurl->out(), $actionicon, array(
                                'class' => 'actionlink',
                                'title' => get_string('communicationlog', 'spark_admission'))).' ';
                    }
                    // Edit.
                    if (has_capability('spark/admission:manageapplication', $contextsystem) || has_capability('spark/admission:processapplication', $contextsystem)) {
                        $actionurl = new moodle_url('/spark/admission/application_edit.php', array('id' => $tablerow->id ));
                        $actioniconurl = $OUTPUT->pix_url('cog', 'spark_admission');
                        $actionicontext = 'Edit';
                        $actionicon = html_writer::img($actioniconurl, $actionicontext, array('width' => '16', 'height' => '16'));
                        $actionlinks .= html_writer::link($actionurl->out(), $actionicon, array(
                                'class' => 'actionlink',
                                'title' => get_string('edit', 'spark_admission'))).' ';
                    }
                    // Delete.
                    if (has_capability('spark/admission:deleteapplication', $contextsystem)) {
                        $actionurl = new moodle_url('/spark/admission/application_delete.php', array('id' => $tablerow->id ));
                        $actioniconurl = $OUTPUT->pix_url('delete', 'spark_admission');
                        $actionicontext = 'Delete';
                        $actionicon = html_writer::img($actioniconurl, $actionicontext, array('width' => '16', 'height' => '16'));
                        $actionlinks .= html_writer::link($actionurl->out(), $actionicon, array(
                                'class' => 'actionlink',
                                'title' => get_string('delete', 'spark_admission'))).' ';
                    }

                    $$varname = new html_table_cell($actionlinks);
                    break;
                default:
                    if ($tablerow->$column) {
                        $$varname = new html_table_cell($tablerow->$column);
                    } else {
                        $$varname = '-';
                    }
            }
        }

        $row->cells = array();
        foreach ($columns as $column) {
            $varname = 'cell' . $column;
            $row->cells[$column] = $$varname;
        }
        $table->data[] = $row;

    }

    echo $OUTPUT->header();
    echo html_writer::start_div('page-content-wrapper', array('id' => 'page-content'));
    echo html_writer::tag('h1', $title, array('class' => 'page-title'));

    // The view options.
    $searchformurl = new moodle_url('/spark/admission/application.php');

    $genders = array(
        'M' => 'Male',
        'F' => 'Female'
    );

    if (has_capability('spark/admission:processapplication', $contextsystem, null, false)) {
        $filter_approved_form = '';
    } else {
        $filter_approved_form = html_writer::div(html_writer::label(get_string('approved', 'spark_admission'), 'approved'), 'filter-form-label').
            html_writer::div(html_writer::select(
                array('1' => 'Yes', '0' => 'No'), 'approved', $filter_approved, get_string('all', 'spark_admission')
            ), 'filter-form-input');
    }



    // School applied for.
    if (spark_admission_get_options('school_applied_for', true)) {
        $schoolappliedfor = html_writer::div(html_writer::label(get_string('school_applied_for', 'spark_admission'), 'school_applied_for'), 'filter-form-label') .
        html_writer::div(html_writer::select(
            spark_admission_get_options('school_applied_for', true), 'school_applied_for', $filter_school_applied_for, get_string('all', 'spark_admission')
        ), 'filter-form-input');
    } else {
        $schoolappliedfor = '';
    }

    $searchform = html_writer::tag('form',
        html_writer::empty_tag('input', array(
            'type' => 'hidden',
            'name' => 'sesskey',
            'value' => sesskey(),
        )).
        html_writer::empty_tag('input', array(
            'type' => 'hidden',
            'name' => 'perpage',
            'value' => $perpage,
        )).
        html_writer::empty_tag('input', array(
            'type' => 'hidden',
            'name' => 'sort',
            'value' => $sort,
        )).
        html_writer::empty_tag('input', array(
            'type' => 'hidden',
            'name' => 'dir',
            'value' => $dir,
        )).
        // School applied for.
        $schoolappliedfor.
        // School year applied for.
        html_writer::div(html_writer::label(get_string('school_year_applied_for', 'spark_admission'), 'school_year_applied_for'), 'filter-form-label').
        html_writer::div(html_writer::select(
            spark_admission_get_options('school_year_applied_for', true), 'school_year_applied_for', $filter_school_year_applied_for, get_string('all', 'spark_admission')
        ), 'filter-form-input').

        // Gender.
        html_writer::div(html_writer::label(get_string('student_gender', 'spark_admission'), 'student_gender'), 'filter-form-label').
        html_writer::div(html_writer::select(
            $genders, 'student_gender', $filter_student_gender, get_string('all', 'spark_admission')
        ), 'filter-form-input').

        // Race.
        html_writer::div(html_writer::label(get_string('student_race', 'spark_admission'), 'student_race'), 'filter-form-label').
        html_writer::div(html_writer::select(
            spark_admission_get_options('student_race', true), 'student_race', $filter_student_race, get_string('all', 'spark_admission')
        ), 'filter-form-input').

        // Grade.
        html_writer::div(html_writer::label(get_string('grade_applied_for', 'spark_admission'), 'grade_applied_for'), 'filter-form-label').
        html_writer::div(html_writer::select(
            spark_admission_get_options('grade_applied_for', true), 'grade_applied_for', $filter_grade_applied_for, get_string('all', 'spark_admission')
        ), 'filter-form-input').

        // Hear.
        html_writer::div(html_writer::label(get_string('hear', 'spark_admission'), 'hear'), 'filter-form-label').
        html_writer::div(html_writer::select(
            spark_admission_get_options('hear', true), 'hear', $filter_hear, get_string('all', 'spark_admission')
        ), 'filter-form-input').

        // Transportation.
        html_writer::div(html_writer::label(get_string('transportation', 'spark_admission'), 'transportation'), 'filter-form-label').
        html_writer::div(html_writer::select(
            spark_admission_get_options('transportation', true), 'transportation', $filter_transportation, get_string('all', 'spark_admission')
        ), 'filter-form-input').

        // approved.
        $filter_approved_form.

        // Search,
        html_writer::div(html_writer::label('Search', 'search'), 'filter-form-label').
        html_writer::div(
            html_writer::empty_tag('input',
                array('type' => 'text', 'name' => 'search', 'value' => $search, 'class' => 'search-textbox')
            ),
            'filter-form-input'
        ).
        html_writer::empty_tag('input', array(
            'type' => 'submit',
            'value' => 'Search',
            'class' => 'search-submit-btn',
        )).
        html_writer::link(new moodle_url('/spark/admission/application.php'),
            get_string('reset', 'spark_discipline'), array('class' => 'filter-reset')
        ),
        array(
            'action' => $searchformurl->out(),
            'method' => 'post',
            'autocomplete' => 'off'
        )
    );
    $collapsed = true;
    if ($search
        || $filter_school_applied_for
        || $filter_school_year_applied_for
        || $filter_student_gender
        || $filter_student_race
        || $filter_grade_applied_for
        || $filter_hear
        || $filter_transportation
        || $filter_approved
    ) {
        $collapsed = false;
    }
    print_collapsible_region(
        html_writer::div($searchform, 'search-form-wrapper-advanced', array('id' => 'search-form')),
        'search-form-wrapper-collapsible',
        'id-search-form-wrapper-collapsible',
        get_string('search', 'spark_discipline'),
        '',
        $collapsed
    );

    $pagingurl = new moodle_url('/spark/admission/application.php?',
        array(
            'perpage' => $perpage,
            'sort' => $sort,
            'dir' => $dir,
            'search' => $search,
            'school_applied_for' => $filter_school_applied_for,
            'school_year_applied_for' => $filter_school_year_applied_for,
            'student_gender' => $filter_student_gender,
            'student_race' => $filter_student_race,
            'grade_applied_for' => $filter_grade_applied_for,
            'hear' => $filter_hear,
            'transportation' => $filter_transportation,
            'approved' => $filter_approved,
        )
    );

    $pagingbar = new paging_bar($totalcount, $page, $perpage, $pagingurl, 'page');

    echo $OUTPUT->render($pagingbar);
    echo html_writer::div(
        html_writer::table($table),
        'data-table-wrapper'
    );
    echo $OUTPUT->render($pagingbar);

    // Add record form.
    if (is_siteadmin() || has_capability('spark/discipline:manageviolation', $contextsystem)) {
        $formurl = new moodle_url('/spark/admission/application_edit.php', array('action' => 'add'));
        $submitbutton  = html_writer::tag('button', get_string('add', 'spark_admission'), array(
            'class' => 'spark-add-record-btn',
            'type' => 'submit',
            'value' => 'submit',
        ));
        $form = html_writer::tag('form', $submitbutton, array(
            'action' => $formurl->out(),
            'method' => 'post',
            'autocomplete' => 'off'
        ));
        echo html_writer::div($form, 'add-record-btn-wrapper', array('id' => 'add-record-btn'));
    }

    // Export link.
    $exporexcelurl = $thispageurl;
    $exporexcelurl->remove_all_params();
    $exporexcelurl->param('action', 'excel');
    $exporexcelurl->param('search', $search);
    $exporexcelurl->param('school_applied_for', $filter_school_applied_for);
    $exporexcelurl->param('school_year_applied_for', $filter_school_year_applied_for);
    $exporexcelurl->param('student_gender', $filter_student_gender);
    $exporexcelurl->param('student_race', $filter_student_race);
    $exporexcelurl->param('grade_applied_for', $filter_grade_applied_for);
    $exporexcelurl->param('hear', $filter_hear);
    $exporexcelurl->param('transportation', $filter_transportation);
    $exporexcelurl->param('approved', $filter_approved);


    $exportexceliconurl = $OUTPUT->pix_url('page_white_excel', 'spark_admission');
    $exportexcelicon = html_writer::img($exportexceliconurl, '', array('width' => '16', 'height' => '16'));
    $exportexceliconlink = html_writer::link($exporexcelurl, $exportexcelicon);
    $exportexcellink = html_writer::link($exporexcelurl, 'XLS');

    $exportexturl = $thispageurl;
    $exportexturl->remove_all_params();
    $exportexturl->param('action', 'csv');
    $exportexturl->param('search', $search);
    $exportexturl->param('school_applied_for', $filter_school_applied_for);
    $exportexturl->param('school_year_applied_for', $filter_school_year_applied_for);
    $exportexturl->param('student_gender', $filter_student_gender);
    $exportexturl->param('student_race', $filter_student_race);
    $exportexturl->param('grade_applied_for', $filter_grade_applied_for);
    $exportexturl->param('hear', $filter_hear);
    $exportexturl->param('transportation', $filter_transportation);
    $exportexturl->param('approved', $filter_approved);

    $exporttexticonurl = $OUTPUT->pix_url('page_white_text', 'spark_admission');
    $exporttexticon = html_writer::img($exporttexticonurl, '', array('width' => '16', 'height' => '16'));
    $exporttexticonlink = html_writer::link($exportexturl, $exporttexticon);
    $exporttextlink = html_writer::link($exportexturl, 'CSV');

    echo html_writer::div(
        get_string('export', 'spark_admission').' :&nbsp;&nbsp;'.
        //$exportexceliconlink.'&nbsp;'.$exportexcellink.'&nbsp;|&nbsp; '.
        $exporttexticonlink.'&nbsp;'.$exporttextlink, 'export-link-wrapper',
        array(
            'id' => 'export-link',
            'style' => 'text-align:center;'
        )
    );

    echo html_writer::end_div(); // Main wrapper.
    echo $OUTPUT->footer();
}