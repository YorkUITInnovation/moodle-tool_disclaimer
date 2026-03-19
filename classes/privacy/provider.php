<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace tool_disclaimer\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy Subsystem implementation for tool_disclaimer.
 *
 * @package    tool_disclaimer
 * @copyright  2026 Carlos Arce <carlosarcelopera@catalyst-ca.net>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {

        $collection->add_database_table('tool_disclaimer', [
            'usermodified' => 'privacy:metadata:tool_disclaimer:usermodified',
        ], 'privacy:metadata:tool_disclaimer');

        $collection->add_database_table('tool_disclaimer_role', [
            'usermodified' => 'privacy:metadata:tool_disclaimer_role:usermodified',
        ], 'privacy:metadata:tool_disclaimer_role');

        $collection->add_database_table('tool_disclaimer_log', [
            'userid' => 'privacy:metadata:tool_disclaimer_log:userid',
            'usermodified' => 'privacy:metadata:tool_disclaimer_log:usermodified',
        ], 'privacy:metadata:tool_disclaimer_log');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid($userid): contextlist {
        global $DB;
        $contextlist = new contextlist();

        // Check if user appears in any table, if so add system context.
        if ($DB->record_exists('tool_disclaimer', ['usermodified' => $userid]) ||
            $DB->record_exists('tool_disclaimer_role', ['usermodified' => $userid]) ||
            $DB->record_exists_select('tool_disclaimer_log',
                'userid = :u1 OR usermodified = :u2',
                ['u1' => $userid, 'u2' => $userid]
            )) {
            $contextlist->add_system_context();
        }

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }

        // Find all users who have any data in disclaimer tables.
        $sql = "SELECT usermodified AS userid FROM {tool_disclaimer}
                UNION
                SELECT usermodified FROM {tool_disclaimer_role}
                UNION
                SELECT userid FROM {tool_disclaimer_log}
                UNION
                SELECT usermodified FROM {tool_disclaimer_log}";

        $userlist->add_from_sql('userid', $sql, []);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel !== CONTEXT_SYSTEM) {
                continue;
            }

            // Export disclaimer configurations modified by user.
            $sql = "SELECT td.* FROM {tool_disclaimer} td WHERE td.usermodified = :userid";
            $disclaimers = $DB->get_records_sql($sql, ['userid' => $userid]);

            if (!empty($disclaimers)) {
                $disclaimerdata = [];
                foreach ($disclaimers as $disclaimer) {
                    $disclaimerdata[] = (object) [
                        'id' => $disclaimer->id,
                        'name' => $disclaimer->name,
                        'context' => $disclaimer->context,
                        'subject' => $disclaimer->subject,
                        'published' => \core_privacy\local\request\transform::yesno($disclaimer->published),
                        'frontpageonly' => \core_privacy\local\request\transform::yesno($disclaimer->frontpageonly),
                        'timecreated' => \core_privacy\local\request\transform::datetime($disclaimer->timecreated),
                        'timemodified' => \core_privacy\local\request\transform::datetime($disclaimer->timemodified),
                    ];
                }
                writer::with_context($context)->export_data(
                    [get_string('pluginname', 'tool_disclaimer'), get_string('privacy:disclaimers', 'tool_disclaimer')],
                    (object) ['disclaimers' => $disclaimerdata]
                );
            }

            // Export disclaimer roles modified by user.
            $sql = "SELECT tdr.* FROM {tool_disclaimer_role} tdr WHERE tdr.usermodified = :userid";
            $roles = $DB->get_records_sql($sql, ['userid' => $userid]);

            if (!empty($roles)) {
                $roledata = [];
                foreach ($roles as $role) {
                    $roledata[] = (object) [
                        'id' => $role->id,
                        'disclaimerid' => $role->disclaimerid,
                        'role' => $role->role,
                        'timecreated' => \core_privacy\local\request\transform::datetime($role->timecreated),
                        'timemodified' => \core_privacy\local\request\transform::datetime($role->timemodified),
                    ];
                }
                writer::with_context($context)->export_data(
                    [get_string('pluginname', 'tool_disclaimer'), get_string('privacy:roles', 'tool_disclaimer')],
                    (object) ['roles' => $roledata]
                );
            }

            // Export disclaimer logs where user is the subject (userid).
            $sql = "SELECT tdl.* FROM {tool_disclaimer_log} tdl WHERE tdl.userid = :userid";
            $logs = $DB->get_records_sql($sql, ['userid' => $userid]);

            if (!empty($logs)) {
                $logdata = [];
                foreach ($logs as $log) {
                    $logdata[] = (object) [
                        'id' => $log->id,
                        'disclaimerid' => $log->disclaimerid,
                        'objectid' => $log->objectid,
                        'response' => \core_privacy\local\request\transform::yesno($log->response),
                        'attempt' => $log->attempt,
                        'usermodified' => $log->usermodified,
                        'timecreated' => \core_privacy\local\request\transform::datetime($log->timecreated),
                        'timemodified' => \core_privacy\local\request\transform::datetime($log->timemodified),
                    ];
                }
                writer::with_context($context)->export_data(
                    [get_string('pluginname', 'tool_disclaimer'), get_string('privacy:userresponses', 'tool_disclaimer')],
                    (object) ['responses' => $logdata]
                );
            }
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }

        // Anonymize usermodified field for all disclaimers.
        $DB->set_field('tool_disclaimer', 'usermodified', 0);

        // Anonymize usermodified field for all disclaimer roles.
        $DB->set_field('tool_disclaimer_role', 'usermodified', 0);

        // Anonymize user data in disclaimer logs (keep logs for audit trail).
        $DB->set_field('tool_disclaimer_log', 'userid', 0);
        $DB->set_field('tool_disclaimer_log', 'usermodified', 0);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }

        $userids = $userlist->get_userids();
        [$usersql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

        // Anonymize usermodified field for affected disclaimers.
        $sql = "usermodified $usersql";
        $DB->set_field_select('tool_disclaimer', 'usermodified', 0, $sql, $params);

        // Anonymize usermodified field for affected disclaimer roles.
        $sql = "usermodified $usersql";
        $DB->set_field_select('tool_disclaimer_role', 'usermodified', 0, $sql, $params);

        // Anonymize user data in disclaimer logs with separate WHERE clauses for precision.
        $sql = "userid $usersql";
        $DB->set_field_select('tool_disclaimer_log', 'userid', 0, $sql, $params);

        $sql = "usermodified $usersql";
        $DB->set_field_select('tool_disclaimer_log', 'usermodified', 0, $sql, $params);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel !== CONTEXT_SYSTEM) {
                continue;
            }

            // Anonymize usermodified field for affected disclaimers.
            $DB->set_field_select(
                'tool_disclaimer',
                'usermodified',
                0,
                'usermodified = :userid',
                ['userid' => $userid]
            );

            // Anonymize usermodified field for affected disclaimer roles.
            $DB->set_field_select(
                'tool_disclaimer_role',
                'usermodified',
                0,
                'usermodified = :userid',
                ['userid' => $userid]
            );

            // Anonymize user data in disclaimer logs for this user.
            $DB->set_field_select(
                'tool_disclaimer_log',
                'userid',
                0,
                'userid = :userid',
                ['userid' => $userid]
            );
            $DB->set_field_select(
                'tool_disclaimer_log',
                'usermodified',
                0,
                'usermodified = :userid',
                ['userid' => $userid]
            );
        }
    }
}
