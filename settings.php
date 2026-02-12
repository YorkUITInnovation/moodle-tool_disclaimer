<?php
defined('MOODLE_INTERNAL') || die;

$context = context_system::instance();

// Create a category for disclaimer management
$ADMIN->add('courses', new admin_category('tool_disclaimer_category', new lang_string('disclaimers', 'tool_disclaimer')));

// Add main disclaimers management page
$ADMIN->add('tool_disclaimer_category', new admin_externalpage(
    'tool_disclaimer_manage',
    new lang_string('disclaimers', 'tool_disclaimer'),
    "$CFG->wwwroot/admin/tool/disclaimer/index.php",
    'tool/disclaimer:view'
));

// Add user responses page
$ADMIN->add('tool_disclaimer_category', new admin_externalpage(
    'tool_disclaimer_responses',
    new lang_string('user_responses', 'tool_disclaimer'),
    "$CFG->wwwroot/admin/tool/disclaimer/user_responses.php",
    'tool/disclaimer:edit'
));
