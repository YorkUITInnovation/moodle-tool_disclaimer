<?php
require_once('../../../config.php');

use tool_disclaimer\helper;
use tool_disclaimer\tables\disclaimer_table;
use tool_disclaimer\forms\disclaimer_filter_form;

global $CFG, $OUTPUT, $PAGE, $DB, $USER;
include_once($CFG->dirroot . '/admin/tool/disclaimer/classes/tables/disclaimer_table.php');
include_once($CFG->dirroot . '/admin/tool/disclaimer/classes/forms/disclaimer_filter_form.php');

require_login(1, false);

$context = context_system::instance();

// Capability to view/edit page
$hasCapability_view_edit = has_capability('tool/disclaimer:view', $PAGE->context, $USER->id);
if (!$hasCapability_view_edit) {
    redirect($CFG->wwwroot . '/my');
}

// Load AMD module
//$PAGE->requires->js_call_amd('local_organization/units', 'init');
// Load CSS file
$PAGE->requires->css('/admin/tool/disclaimer/css/general.css');

$term = optional_param('q', '', PARAM_TEXT);

$formdata = new stdClass();
$formdata->name = $term;

$mform = new disclaimer_filter_form(null, array('formdata' => $formdata));

if ($mform->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present
    redirect($CFG->wwwroot . '/admin/tool/disclaimer/index.php');
} else if ($data = $mform->get_data()) { // form is submitted with filter
    // Process validated data
    $term_filter = $data->q;
} else {
    // Display the form
//    $mform->display();
}

$table = new disclaimer_table('tool_disclaimer_table');
$params = array();
// Define the SQL query to fetch data
//retrieve campus id from form data when submit
$sql = ' id >= 1';
if (!empty($term_filter)) {
    $sql .= " AND LOWER(name) LIKE '%$term_filter%'";
}
// Define the SQL query to fetch data
$table->set_sql('*', '{tool_disclaimer}', $sql );

// Define the base URL for the table
$table->define_baseurl(new moodle_url('/admin/tool/disclaimer/index.php'));

helper::page(
    new moodle_url('/admin/tool/disclaimer/index.php'),
    get_string('disclaimers', 'tool_disclaimer'),
    get_string('disclaimers', 'tool_disclaimer')
);

$PAGE->set_header(get_string('disclaimers', 'tool_disclaimer'));

echo $OUTPUT->header();
// Set up the table
$mform->display();
$table->out(20, true);
echo $OUTPUT->footer();

