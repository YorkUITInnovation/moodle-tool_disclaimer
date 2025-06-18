<?php
require_once('../../../config.php');

use tool_disclaimer\helper;

global $CFG, $OUTPUT, $PAGE, $DB, $USER;
include_once($CFG->dirroot . '/admin/tool/disclaimer/classes/forms/edit_my_disclaimer_form.php');

require_login(1, false);

$context = context_system::instance();

$id = optional_param('id', 0, PARAM_INT); // Disclaimer log id
$courseid = optional_param('courseid', 0, PARAM_INT);

if (!$id) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
}

$formdata = $DB->get_record('tool_disclaimer_log', ['id' => $id]);
if ($formdata->userid != $USER->id) {
    // User is trying to access a disclaimer log that does not belong to them.
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]),
    get_string('not_your_disclaimer', 'tool_disclaimer')
    );
}
$formdata->courseid = $courseid;
$disclaimer = $DB->get_record('tool_disclaimer', ['id' => $formdata->disclaimerid]);

$mform = new edit_my_disclaimer_form(null, array('formdata' => $formdata));

helper::page(
    new moodle_url('/admin/tool/disclaimer/edit_disclaimer.php?id=' . $id),
    get_string('edit_disclaimer', 'tool_disclaimer'),
    $disclaimer->name
);

echo $OUTPUT->header();

echo $OUTPUT->render_from_template(
    'tool_disclaimer/my_disclaimer_info',
    [
        'message' => $disclaimer->message,
    ]
);

if ($mform->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
} else if ($data = $mform->get_data()) { // form is submitted with filter
    if ($data->id) {
        $data->usermodified = $USER->id;
        $data->timemodified = time();
        $DB->update_record('tool_disclaimer_log', $data);
    }

    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
} else {
    // Display the form
    $mform->display();
}

echo $OUTPUT->footer();

