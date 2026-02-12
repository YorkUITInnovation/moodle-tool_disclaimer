<?php
require_once('../../../config.php');


use tool_disclaimer\helper;
use tool_disclaimer\disclaimer;

global $CFG, $OUTPUT, $PAGE, $DB, $USER;
include_once($CFG->dirroot . '/admin/tool/disclaimer/classes/forms/edit_disclaimer_form.php');

require_login(1, false);

$context = context_system::instance();

// Capability to view/edit page
$hasCapability_view_edit = has_capability('tool/disclaimer:edit', $PAGE->context, $USER->id);
if (!$hasCapability_view_edit) {
    redirect($CFG->wwwroot . '/my');
}

$id = optional_param('id', 0, PARAM_INT);

if ($id) {
    $formdata = $DB->get_record('tool_disclaimer', ['id' => $id]);
    $roles = $DB->get_records('tool_disclaimer_role', ['disclaimerid' => $id]);
    // Create an array with role key and name being the field named role
    $formdata->roles = [];
    foreach ($roles as $role) {
        // Find the role in the roles table
        $moodle_role = $DB->get_record('role', ['shortname' => $role->role]);
        if (empty($moodle_role->name)) {
            $role_name = $role->role;
        } else {
            $role_name = $moodle_role->name;
        }

        $formdata->roles[$role->role] = $role_name;
    }

} else {
    $formdata = new stdClass();
    $formdata->id = null;
    $formdata->frontpageonly = 0;
    $formdata->context = 'course';
    $formdata->contextpath = '/course/view.php';
    $formdata->message = '';

}

$formdata = file_prepare_standard_editor(
// The existing data.
    $formdata,

    // The field name in the database.
    'message',

    // The options.
    \tool_disclaimer\helper::getTextFieldOptions($context),

    // The combination of contextid, component, filearea, and itemid.
    $context,
    'tool_disclaimer',
    'assets',
    $formdata->id
);

$mform = new \tool_disclaimer\forms\edit_disclaimer_form(null, array('formdata' => $formdata));

if ($mform->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present
    redirect($CFG->wwwroot . '/admin/tool/disclaimer/index.php');
} else if ($data = $mform->get_data()) { // form is submitted with filter
    if ($data->id) {
        $DISCLAIMER = new disclaimer($data->id);
        $DISCLAIMER->update_record($data);
    } else {
        $DISCLAIMER = new disclaimer();
        $data->id = $DISCLAIMER->insert_record($data);
    }

    //save editor text
    $draftid = file_get_submitted_draft_itemid('message_editor');
    $messageText = file_save_draft_area_files($draftid, $context->id, 'tool_disclaimer', 'assets', $data->id, \tool_disclaimer\helper::getTextFieldOptions($context), $data->message_editor['text']);
    $data->message = $messageText;

    $DISCLAIMER->update_record($data);

    unset($DISCLAIMER);

    redirect($CFG->wwwroot . '/admin/tool/disclaimer/index.php');
} else {
    // Display the form
//    $mform->display();
}


helper::page(
    new moodle_url('/admin/tool/disclaimer/edit_disclaimer.php?id=' . $id),
    get_string('edit_disclaimer', 'tool_disclaimer'),
    get_string('edit_disclaimer', 'tool_disclaimer')
);

echo $OUTPUT->header();
// Set up the table
$mform->display();

echo $OUTPUT->footer();

