<?php
defined('MOODLE_INTERNAL') || die;

$context = context_system::instance();

$ADMIN->add('courses', new admin_externalpage('tool_disclaimer', new lang_string('pluginname', 'tool_disclaimer'), "$CFG->wwwroot/admin/tool/disclaimer/index.php"));