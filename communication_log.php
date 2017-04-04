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

// Paging options.
$page      = optional_param('page', 0, PARAM_INT);
$perpage   = optional_param('perpage', 20, PARAM_INT);
$sort      = optional_param('sort', 'timecreated', PARAM_ALPHANUM);
$dir       = optional_param('dir', 'ASC', PARAM_ALPHA);
// Action.
$action    = optional_param('action', false, PARAM_ALPHA);
$search    = optional_param('search', '', PARAM_TEXT);
// Application
$id        = required_param('id', PARAM_INT);

require_login(null, false);
$contextsystem = context_system::instance();

// Permission.
if (!has_capability('spark/admission:manageapplication', $contextsystem) && !has_capability('spark/admission:processapplication', $contextsystem)) {
    print_error('permissionerror', 'spark_admission');
}
$thispageurl = new moodle_url('/spark/admission/communication_log.php', array('id' => $id));

$PAGE->set_url($thispageurl);
$PAGE->set_pagelayout('course');
$PAGE->set_context($contextsystem);
$PAGE->requires->css('/spark/admission/css/application.css');

$name = get_string('communicationlog', 'spark_admission');
$title = get_string('communicationlog', 'spark_admission');
$heading = $SITE->fullname;

// Breadcrumb.
$PAGE->navbar->add(get_string('pluginname', 'spark_admission'));
$PAGE->navbar->add(get_string('applications', 'spark_admission'), new moodle_url('/spark/admission/application.php'));
$PAGE->navbar->add($name);

$PAGE->set_title($title);
$PAGE->set_heading($heading);

$datacolumns = array(
    'id' => 'a.id',
    'applicationid' => 'a.applicationid',
    'logtype' => 'a.logtype',
    'note' => 'a.note',
    'userid' => 'a.userid',
    'deleted' => 'a.deleted',
    'enteredby' => "CONCAT(u.lastname, ', ', u.firstname)",
    'timecreated' => 'a.timecreated',
    'timemodified' => 'a.timemodified'
);

// Data Clerk can view approved applications.
if (has_capability('spark/admission:processapplication', $contextsystem, null, false)) {
    $app = $DB->get_record('spark_admission_app', array('id' => $id, 'approved' => 1, 'deleted' => 0), '*', MUST_EXIST);
} else {
    $app = $DB->get_record('spark_admission_app', array('id' => $id, 'deleted' => 0), '*', MUST_EXIST);
}
// Filter.
$where = " AND ".$datacolumns['applicationid']." = '$id'";
if ($search) {
    $where .= " AND ".$datacolumns['name']." LIKE '%$search%'";
}

// Sort.
$order = '';
if ($sort) {
    $order = " ORDER BY $datacolumns[$sort] $dir";
}

// Count records for paging.
$countsql = "SELECT COUNT(1) 
               FROM {spark_admission_app_log} a 
               JOIN {user} u 
                 ON a.userid = u.id 
              WHERE a.deleted = 0 
                    $where";
$totalcount = $DB->count_records_sql($countsql);

// Table columns.
$columns = array(
    'rowcount',
    'logtype',
    'note',
    'enteredby',
    'timecreated',
    'timemodified',
    'action'
);

$sql = "SELECT a.id,
               a.applicationid,
               a.logtype,
               a.note,
               a.userid,
               a.deleted,
               u.firstname,
               u.lastname,
               a.timecreated,
               a.timemodified,
               CONCAT(u.lastname, ', ', u.firstname) enteredby
          FROM {spark_admission_app_log} a
          JOIN {user} u 
            ON a.userid = u.id
         WHERE a.deleted = 0
               $where
               $order";

if ($action == 'excel') {
    set_time_limit(300);
    raise_memory_limit(MEMORY_EXTRA);

    $table = new stdClass();
    $table->head = $columns;

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
    array_shift($columns);
    // Delete last action column.
    array_pop($columns);

    $headers = $columns;

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
        foreach ($columns as $column) {
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
        if (($column == 'rowcount') || ($column == 'action')) {
            $$column = $string[$column];
        } else {
            $sorturl = $thispageurl;
            $sorturl->param('perpage', $perpage);
            $sorturl->param('sort', $column);
            $sorturl->param('dir', $columndir);
            $sorturl->param('search', $search);

            $$column = html_writer::link($sorturl->out(false), $string[$column]).$columnicon;
        }
    }

    $table = new html_table();

    $table->head = array();
    $table->wrap = array();
    foreach ($columns as $column) {
        $table->head[$column] = $$column;
        $table->wrap[$column] = '';
    }

    // Override cell wrap.
    $table->wrap['action'] = 'nowrap';

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
                case 'timecreated':
                case 'timemodified':
                    $$varname = '-';
                    if ($tablerow->$column > 0) {
                        $$varname = new html_table_cell(date("m/d/Y g:i A", $tablerow->$column));
                    }
                    break;
                case 'logtype':
                    $$varname = new html_table_cell(get_string($tablerow->$column, 'spark_admission'));
                    break;
                case 'action':
                    // Edit.
                    if (has_capability('spark/admission:manageapplication', $contextsystem) || has_capability('spark/admission:processapplication', $contextsystem)) {
                        $actionurl = new moodle_url('/spark/admission/communication_log_edit.php', array('id' => $tablerow->id, 'applicationid' => $id));
                        $actioniconurl = $OUTPUT->pix_url('cog', 'spark_admission');
                        $actionicontext = 'Edit';
                        $actionicon = html_writer::img($actioniconurl, $actionicontext, array('width' => '16', 'height' => '16'));
                        $actionlinks .= html_writer::link($actionurl->out(false), $actionicon, array(
                                'class' => 'actionlink',
                                'title' => get_string('edit', 'spark_admission'))).' ';
                    }
                    // Delete.
                    if (has_capability('spark/admission:manageapplication', $contextsystem) || has_capability('spark/admission:processapplication', $contextsystem)) {
                        $actionurl = new moodle_url('/spark/admission/communication_log_delete.php', array('id' => $tablerow->id, 'applicationid' => $id));
                        $actioniconurl = $OUTPUT->pix_url('delete', 'spark_admission');
                        $actionicontext = 'Delete';
                        $actionicon = html_writer::img($actioniconurl, $actionicontext, array('width' => '16', 'height' => '16'));
                        $actionlinks .= html_writer::link($actionurl->out(false), $actionicon, array(
                                'class' => 'actionlink',
                                'title' => get_string('delete', 'spark_admission'))).' ';
                    }

                    $$varname = new html_table_cell($actionlinks);
                    break;
                default:
                    $$varname = new html_table_cell($tablerow->$column);
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
    $searchformurl = new moodle_url('/spark/admission/communication_log.php');

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
        html_writer::empty_tag('input', array(
            'type' => 'text',
            'name' => 'search',
            'value' => $search,
            'class' => 'search-textbox',
        )).
        html_writer::empty_tag('input', array(
            'type' => 'submit',
            'value' => 'Search',
            'class' => 'search-submit-btn',
        )),
        array(
            'action' => $searchformurl->out(),
            'method' => 'post',
            'autocomplete' => 'off'
        )
    );
    echo html_writer::div($searchform, 'search-form-wrapper', array('id' => 'search-form'));

    $pagingurl = new moodle_url('/spark/admission/communication_log.php?',
        array(
            'perpage' => $perpage,
            'sort' => $sort,
            'dir' => $dir,
            'search' => $search
        )
    );
    // Preview application
    echo html_writer::start_tag('div', array('class' => 'app-preview-wrapper'));
    echo html_writer::start_tag('table');
    foreach ($app as $key => $value) {
        if ($field = $DB->get_record('spark_admission_app_fields', array('name' => $key, 'disabled' => 0))) {
            echo html_writer::start_tag('tr');
            echo html_writer::tag('td', html_writer::tag('strong', get_string($key, 'spark_admission')));
            echo html_writer::tag('td', $value);
            echo html_writer::end_tag('tr');
        }
    }
    echo html_writer::end_tag('table');
    echo html_writer::end_tag('div');

    $pagingbar = new paging_bar($totalcount, $page, $perpage, $pagingurl, 'page');

    echo $OUTPUT->render($pagingbar);
    echo html_writer::div(
        html_writer::table($table),
        'data-table-wrapper'
    );
    echo $OUTPUT->render($pagingbar);

    // Add record form.
    if (has_capability('spark/admission:manageapplication', $contextsystem) || has_capability('spark/admission:processapplication', $contextsystem)) {
        $formurl = new moodle_url('/spark/admission/communication_log_edit.php', array('applicationid' => $id, 'action' => 'add'));
        $submitbutton  = html_writer::tag('button', get_string('add', 'spark_admission'), array(
            'class' => 'spark-add-record-btn',
            'type' => 'submit',
            'value' => 'submit',
        ));
        $form = html_writer::tag('form', $submitbutton, array(
            'action' => $formurl->out(false),
            'method' => 'post',
            'autocomplete' => 'off'
        ));
        echo html_writer::div($form, 'add-record-btn-wrapper', array('id' => 'add-record-btn'));
    }

    // Export link.
    $exporexcelurl = $thispageurl;
    $exporexcelurl->remove_all_params();
    $exporexcelurl->param('action', 'excel');
    $exportexceliconurl = $OUTPUT->pix_url('page_white_excel', 'spark_admission');
    $exportexcelicon = html_writer::img($exportexceliconurl, '', array('width' => '16', 'height' => '16'));
    $exportexceliconlink = html_writer::link($exporexcelurl, $exportexcelicon);
    $exportexcellink = html_writer::link($exporexcelurl, 'XLS');

    $exportexturl = $thispageurl;
    $exportexturl->remove_all_params();
    $exportexturl->param('action', 'csv');
    $exporttexticonurl = $OUTPUT->pix_url('page_white_text', 'spark_admission');
    $exporttexticon = html_writer::img($exporttexticonurl, '', array('width' => '16', 'height' => '16'));
    $exporttexticonlink = html_writer::link($exportexturl, $exporttexticon);
    $exporttextlink = html_writer::link($exportexturl, 'CSV');

    echo html_writer::div(
        get_string('export', 'spark_admission').' :&nbsp;&nbsp;'.
        $exportexceliconlink.'&nbsp;'.$exportexcellink.'&nbsp;|&nbsp; '
        .$exporttexticonlink.'&nbsp;'.$exporttextlink, 'export-link-wrapper',
        array(
            'id' => 'export-link',
            'style' => 'text-align:center;'
        )
    );

    echo html_writer::end_div(); // Main wrapper.
    echo $OUTPUT->footer();
}