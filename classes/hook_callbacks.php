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
 * Hook callbacks for tool_disclaimer.
 *
 * @package    tool_disclaimer
 * @copyright  York University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_disclaimer;

/**
 * Hook callbacks used by the withdrawal UI.
 */
class hook_callbacks {

    /**
     * Add a disclaimer withdrawal link to the top-right user menu.
     *
     * @param \core_user\hook\extend_user_menu $hook
     */
    public static function extend_user_menu(\core_user\hook\extend_user_menu $hook): void {
        if (!isloggedin() || isguestuser()) {
            return;
        }

        $item = new \stdClass();
        $item->itemtype = 'link';
        $item->url = new \moodle_url('/user/preferences.php', ['disclaimerwithdraw' => 1]);
        $item->title = get_string('withdraw_disclaimer', 'tool_disclaimer');
        $item->titleidentifier = 'withdraw_disclaimer,tool_disclaimer';
        $hook->add_navitem($item);
    }

    /**
     * Ensure the withdrawal modal bootstrap is available for logged-in users.
     *
     * @param \core\hook\output\before_standard_top_of_body_html_generation $hook
     */
    public static function before_standard_top_of_body_html_generation(
        \core\hook\output\before_standard_top_of_body_html_generation $hook
    ): void {
        global $PAGE;

        if (!isloggedin() || isguestuser()) {
            return;
        }

        if (defined('CLI_SCRIPT') && CLI_SCRIPT) {
            return;
        }

        if (defined('AJAX_SCRIPT') && AJAX_SCRIPT) {
            return;
        }

        $PAGE->requires->js_call_amd('tool_disclaimer/disclaimer_withdraw', 'init');
    }
}

