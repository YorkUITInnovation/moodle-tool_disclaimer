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

    return true;
}