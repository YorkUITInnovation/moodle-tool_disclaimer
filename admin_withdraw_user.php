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
 * Admin page: withdraw disclaimer responses on behalf of a user.
 *
 * Accessible from Site Administration -> Users -> Accounts.
 *
 * @package    tool_disclaimer
 * @copyright  York University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../../config.php');
require_login();
$context = context_system::instance();
$PAGE->set_context($context);
require_capability('tool/disclaimer:withdrawmanage', $context);

$selecteduserid = optional_param('userid', 0, PARAM_INT);

$mform = new \tool_disclaimer\forms\admin_withdraw_user_form(null, ['selecteduserid' => $selecteduserid]);
if ($data = $mform->get_data()) {
	redirect(new moodle_url('/admin/tool/disclaimer/admin_withdraw_user.php', [
		'userid' => (int)$data->userid,
	]));
}
$PAGE->set_url(new moodle_url('/admin/tool/disclaimer/admin_withdraw_user.php'));
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('admin_withdraw_title', 'tool_disclaimer'));
$PAGE->set_heading(get_string('admin_withdraw_title', 'tool_disclaimer'));
$PAGE->requires->js_call_amd('tool_disclaimer/admin_withdraw_user', 'init', [[
	'userid' => $selecteduserid,
]]);

ob_start();
$mform->display();
$userformhtml = ob_get_clean();
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('tool_disclaimer/admin_withdraw_user', [
	'userform' => $userformhtml,
	'selecteduserid' => $selecteduserid,
]);
echo $OUTPUT->footer();
