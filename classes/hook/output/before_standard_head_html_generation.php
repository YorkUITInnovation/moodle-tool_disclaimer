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

namespace tool_disclaimer\hook\output;

/**
 * Hook callback for core\hook\output\before_standard_head_html_generation.
 *
 * Injects the acknowledgement modal AMD call for published 'acknowledgement'-type
 * disclaimers not yet accepted by the current user.
 *
 * @package    tool_disclaimer
 * @copyright  York University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class before_standard_head_html_generation {

    /**
     * Callback invoked by the Moodle 5 hook system.
     *
     * @param \core\hook\output\before_standard_head_html_generation $hook
     */
    public static function callback(\core\hook\output\before_standard_head_html_generation $hook): void {
        global $DB, $PAGE, $USER;

        if (!isloggedin() || isguestuser()) {
            return;
        }

        if (defined('CLI_SCRIPT') && CLI_SCRIPT) {
            return;
        }
        if (defined('AJAX_SCRIPT') && AJAX_SCRIPT) {
            return;
        }

        $disclaimers = $DB->get_records_select(
            'tool_disclaimer',
            "context = :context AND published = :published",
            ['context' => 'acknowledgement', 'published' => 1],
            'timecreated ASC'
        );

        if (empty($disclaimers)) {
            return;
        }

        foreach ($disclaimers as $disclaimer) {
            $acknowledged = $DB->record_exists_select(
                'tool_disclaimer_log',
                'disclaimerid = :did AND userid = :uid AND response = :resp AND objectid = 0',
                ['did' => $disclaimer->id, 'uid' => $USER->id, 'resp' => 1]
            );

            if (!$acknowledged) {
                $PAGE->requires->js_call_amd('tool_disclaimer/acknowledgement_alert', 'init', [[
                    'disclaimerid' => (int)$disclaimer->id,
                    'userid'       => (int)$USER->id,
                ]]);
                break; // One modal per page load.
            }
        }
    }
}
