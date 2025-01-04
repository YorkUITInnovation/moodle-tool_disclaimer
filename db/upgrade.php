<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Execute local_earlyalert upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_tool_disclaimer_upgrade($oldversion)
{
    global $DB;

    $dbman = $DB->get_manager();
    if ($oldversion < 2025010400) {

        // Rename field frontpageonly on table tool_disclaimer to NEWNAMEGOESHERE.
        $table = new xmldb_table('tool_disclaimer');
        $field = new xmldb_field('excludesite', XMLDB_TYPE_INTEGER, '1', null, null, null, '1', 'context');

        // Launch rename field frontpageonly.
        $dbman->rename_field($table, $field, 'frontpageonly');

        // Disclaimer savepoint reached.
        upgrade_plugin_savepoint(true, 2025010400, 'tool', 'disclaimer');
    }

    if ($oldversion < 2025010400.01) {

        // Define field usepublisheddate to be added to tool_disclaimer.
        $table = new xmldb_table('tool_disclaimer');
        $field = new xmldb_field('usepublisheddate', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'messageformat');

        // Conditionally launch add field usepublisheddate.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Disclaimer savepoint reached.
        upgrade_plugin_savepoint(true, 2025010400.01, 'tool', 'disclaimer');
    }

    return true;
}