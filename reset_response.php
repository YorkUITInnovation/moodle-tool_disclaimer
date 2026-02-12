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
 * Reset user response page for disclaimer plugin.
 *
 * This page allows administrators to reset a user's response to a disclaimer.
 *
 * @package    tool_disclaimer
 * @copyright  2026 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

require_login();

$context = context_system::instance();
$PAGE->set_context($context);

// Capability check - only admins who can edit disclaimers.
require_capability('tool/disclaimer:edit', $context);

$id = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);

// Verify the log entry exists.
$log = $DB->get_record('tool_disclaimer_log', ['id' => $id], '*', MUST_EXIST);

// Set up page.
$PAGE->set_url(new moodle_url('/admin/tool/disclaimer/reset_response.php', ['id' => $id]));
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('reset_response', 'tool_disclaimer'));
$PAGE->set_heading(get_string('reset_response', 'tool_disclaimer'));

$returnurl = new moodle_url('/admin/tool/disclaimer/user_responses.php');

// Handle confirmation.
if ($confirm && confirm_sesskey()) {
    // Delete the response log.
    $DB->delete_records('tool_disclaimer_log', ['id' => $id]);

    redirect(
        $returnurl,
        get_string('response_reset_success', 'tool_disclaimer'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Output page.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('reset_response', 'tool_disclaimer'));

// Get user and disclaimer details.
$user = $DB->get_record('user', ['id' => $log->userid], 'id, firstname, lastname, email', MUST_EXIST);
$disclaimer = $DB->get_record('tool_disclaimer', ['id' => $log->disclaimerid], 'name, context', MUST_EXIST);

// Build confirmation message.
$messagedata = new stdClass();
$messagedata->username = fullname($user);
$messagedata->email = $user->email;
$messagedata->disclaimer = format_string($disclaimer->name);
$messagedata->context = format_string($disclaimer->context);

$message = get_string('reset_response_confirm', 'tool_disclaimer', $messagedata);

// Display confirmation dialog.
$continueurl = new moodle_url('/admin/tool/disclaimer/reset_response.php', [
    'id' => $id,
    'confirm' => 1,
    'sesskey' => sesskey()
]);

echo $OUTPUT->confirm($message, $continueurl, $returnurl);

echo $OUTPUT->footer();
