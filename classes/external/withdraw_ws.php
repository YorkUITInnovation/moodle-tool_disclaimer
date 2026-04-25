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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

/**
 * Web services for disclaimer withdrawal.
 *
 * Provides two distinct service families:
 *  - get_status / withdraw          : self-service only (current user).
 *  - manage_get_status / manage_withdraw : requires tool/disclaimer:withdrawmanage capability.
 *
 * @package    tool_disclaimer
 * @copyright  York University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_disclaimer_withdraw_ws extends external_api {

    // ---------------------------------------------------------------------------
    // Helper: fetch accepted disclaimers for a given user.
    // ---------------------------------------------------------------------------

    /**
     * Return an array of accepted disclaimer records for $userid.
     *
     * @param int $userid
     * @return array
     */
    private static function fetch_accepted_disclaimers(int $userid): array {
        global $DB;
        $sql = "SELECT DISTINCT d.id, d.name, d.context
                  FROM {tool_disclaimer_log} dl
                  JOIN {tool_disclaimer} d ON d.id = dl.disclaimerid
                 WHERE dl.userid = :userid
                   AND dl.response = :response
              ORDER BY d.name ASC";
        $records = $DB->get_records_sql($sql, ['userid' => $userid, 'response' => 1]);
        $disclaimers = [];
        foreach ($records as $record) {
            $disclaimers[] = [
                'id'      => (int)$record->id,
                'name'    => format_string($record->name),
                'context' => (string)$record->context,
            ];
        }
        return $disclaimers;
    }

    /**
     * Delete accepted disclaimer log rows for $userid.
     * Pass $ids = [] with $all = true to remove all, or a non-empty $ids array
     * for a targeted removal.
     *
     * @param int   $userid
     * @param int[] $ids
     * @param bool  $all
     * @return int Number of rows deleted.
     */
    private static function do_withdraw(int $userid, array $ids, bool $all): int {
        global $DB;
        $deletedcount = 0;
        if ($all) {
            $sql = 'userid = :userid AND response = :response';
            $sqlparams = ['userid' => $userid, 'response' => 1];
            $deletedcount = $DB->count_records_select('tool_disclaimer_log', $sql, $sqlparams);
            if ($deletedcount > 0) {
                $DB->delete_records_select('tool_disclaimer_log', $sql, $sqlparams);
            }
        } else {
            $cleanids = array_values(array_unique(array_map('intval', $ids)));
            if (empty($cleanids)) {
                throw new invalid_parameter_exception('At least one disclaimer must be selected.');
            }
            list($insql, $inparams) = $DB->get_in_or_equal($cleanids, SQL_PARAMS_NAMED, 'did');
            $sql = "userid = :userid AND response = :response AND disclaimerid {$insql}";
            $sqlparams = ['userid' => $userid, 'response' => 1] + $inparams;
            $deletedcount = $DB->count_records_select('tool_disclaimer_log', $sql, $sqlparams);
            if ($deletedcount > 0) {
                $DB->delete_records_select('tool_disclaimer_log', $sql, $sqlparams);
            }
        }
        return $deletedcount;
    }

    // ---------------------------------------------------------------------------
    // SELF-SERVICE: get_status (current user only)
    // ---------------------------------------------------------------------------

    /**
     * Parameters for get_status.
     *
     * @return external_function_parameters
     */
    public static function get_status_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * Returns accepted disclaimers for the current user.
     *
     * @return array
     */
    public static function get_status() {
        global $USER;
        $context = context_system::instance();
        self::validate_context($context);
        require_login();
        return [
            'userid'      => (int)$USER->id,
            'disclaimers' => self::fetch_accepted_disclaimers((int)$USER->id),
        ];
    }

    /**
     * Return structure for get_status.
     *
     * @return external_single_structure
     */
    public static function get_status_returns() {
        return new external_single_structure([
            'userid'      => new external_value(PARAM_INT, 'Current user ID'),
            'disclaimers' => new external_multiple_structure(
                new external_single_structure([
                    'id'      => new external_value(PARAM_INT,  'Disclaimer ID'),
                    'name'    => new external_value(PARAM_TEXT, 'Disclaimer name'),
                    'context' => new external_value(PARAM_TEXT, 'Disclaimer context'),
                ])
            ),
        ]);
    }

    // ---------------------------------------------------------------------------
    // SELF-SERVICE: withdraw (current user only)
    // ---------------------------------------------------------------------------

    /**
     * Parameters for withdraw.
     *
     * @return external_function_parameters
     */
    public static function withdraw_parameters() {
        return new external_function_parameters([
            'disclaimerids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Disclaimer ID'),
                'Selected disclaimer IDs',
                VALUE_DEFAULT,
                []
            ),
            'withdrawall'   => new external_value(PARAM_BOOL, 'Withdraw from all disclaimers', VALUE_DEFAULT, false),
        ]);
    }

    /**
     * Withdraw the current user from selected/all accepted disclaimers.
     *
     * @param array $disclaimerids
     * @param bool  $withdrawall
     * @return array
     */
    public static function withdraw($disclaimerids = [], $withdrawall = false) {
        global $USER;
        $params = self::validate_parameters(self::withdraw_parameters(), [
            'disclaimerids' => $disclaimerids,
            'withdrawall'   => $withdrawall,
        ]);
        $context = context_system::instance();
        self::validate_context($context);
        require_login();
        $deletedcount = self::do_withdraw((int)$USER->id, $params['disclaimerids'], (bool)$params['withdrawall']);
        return [
            'success'      => true,
            'deletedcount' => $deletedcount,
            'message'      => get_string('withdraw_success', 'tool_disclaimer', $deletedcount),
        ];
    }

    /**
     * Return structure for withdraw.
     *
     * @return external_single_structure
     */
    public static function withdraw_returns() {
        return new external_single_structure([
            'success'      => new external_value(PARAM_BOOL, 'Whether the operation succeeded'),
            'deletedcount' => new external_value(PARAM_INT,  'Number of log rows removed'),
            'message'      => new external_value(PARAM_TEXT, 'Result message'),
        ]);
    }

    // ---------------------------------------------------------------------------
    // ADMIN/MANAGER: manage_get_status (any target user, requires withdrawmanage)
    // ---------------------------------------------------------------------------

    /**
     * Parameters for manage_get_status.
     *
     * @return external_function_parameters
     */
    public static function manage_get_status_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'Target user ID', VALUE_REQUIRED),
        ]);
    }

    /**
     * Returns accepted disclaimers for any target user (admin/manager only).
     *
     * @param int $userid
     * @return array
     */
    public static function manage_get_status($userid) {
        $params = self::validate_parameters(self::manage_get_status_parameters(), ['userid' => $userid]);
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('tool/disclaimer:withdrawmanage', $context);
        $targetuserid = (int)$params['userid'];
        // Verify user exists.
        if (!is_numeric($targetuserid) || $targetuserid < 1) {
            throw new invalid_parameter_exception('Invalid user ID.');
        }
        $user = core_user::get_user($targetuserid, 'id, firstname, lastname, email', MUST_EXIST);
        return [
            'userid'      => (int)$user->id,
            'fullname'    => fullname($user),
            'email'       => $user->email,
            'disclaimers' => self::fetch_accepted_disclaimers((int)$user->id),
        ];
    }

    /**
     * Return structure for manage_get_status.
     *
     * @return external_single_structure
     */
    public static function manage_get_status_returns() {
        return new external_single_structure([
            'userid'      => new external_value(PARAM_INT,  'Target user ID'),
            'fullname'    => new external_value(PARAM_TEXT, 'Full name of the target user'),
            'email'       => new external_value(PARAM_TEXT, 'Email of the target user'),
            'disclaimers' => new external_multiple_structure(
                new external_single_structure([
                    'id'      => new external_value(PARAM_INT,  'Disclaimer ID'),
                    'name'    => new external_value(PARAM_TEXT, 'Disclaimer name'),
                    'context' => new external_value(PARAM_TEXT, 'Disclaimer context'),
                ])
            ),
        ]);
    }

    // ---------------------------------------------------------------------------
    // ADMIN/MANAGER: manage_withdraw (any target user, requires withdrawmanage)
    // ---------------------------------------------------------------------------

    /**
     * Parameters for manage_withdraw.
     *
     * @return external_function_parameters
     */
    public static function manage_withdraw_parameters() {
        return new external_function_parameters([
            'userid'        => new external_value(PARAM_INT,  'Target user ID',               VALUE_REQUIRED),
            'disclaimerids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Disclaimer ID'),
                'Selected disclaimer IDs',
                VALUE_DEFAULT,
                []
            ),
            'withdrawall'   => new external_value(PARAM_BOOL, 'Withdraw from all disclaimers', VALUE_DEFAULT, false),
        ]);
    }

    /**
     * Withdraw disclaimers for any target user (admin/manager only).
     *
     * @param int   $userid
     * @param array $disclaimerids
     * @param bool  $withdrawall
     * @return array
     */
    public static function manage_withdraw($userid, $disclaimerids = [], $withdrawall = false) {
        $params = self::validate_parameters(self::manage_withdraw_parameters(), [
            'userid'        => $userid,
            'disclaimerids' => $disclaimerids,
            'withdrawall'   => $withdrawall,
        ]);
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('tool/disclaimer:withdrawmanage', $context);
        $targetuserid = (int)$params['userid'];
        $deletedcount = self::do_withdraw($targetuserid, $params['disclaimerids'], (bool)$params['withdrawall']);
        return [
            'success'      => true,
            'deletedcount' => $deletedcount,
            'message'      => get_string('withdraw_success', 'tool_disclaimer', $deletedcount),
        ];
    }

    /**
     * Return structure for manage_withdraw.
     *
     * @return external_single_structure
     */
    public static function manage_withdraw_returns() {
        return new external_single_structure([
            'success'      => new external_value(PARAM_BOOL, 'Whether the operation succeeded'),
            'deletedcount' => new external_value(PARAM_INT,  'Number of log rows removed'),
            'message'      => new external_value(PARAM_TEXT, 'Result message'),
        ]);
    }
}
