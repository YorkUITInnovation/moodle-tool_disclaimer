<?php
function tool_disclaimer_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array())
{
    global $DB;

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

//    require_login(1, true);

    $fileAreas = array(
        'assets',
    );

    if (!in_array($filearea, $fileAreas)) {
        return false;
    }


    $itemid = array_shift($args);
    $filename = array_pop($args);
    $path = !count($args) ? '/' : '/' . implode('/', $args) . '/';

    $fs = get_file_storage();

    $file = $fs->get_file($context->id, 'tool_disclaimer', $filearea, $itemid, $path, $filename);

    // If the file does not exist.
    if (!$file) {
        send_file_not_found();
    }

    send_stored_file($file, 86400, 0, $forcedownload); // Options.
}

/**
 * Display link in course navigation to disclaimer page
 */
function tool_disclaimer_extend_navigation_course(
    navigation_node $parentnode,
    stdClass        $course,
    context_course  $context
)
{
    global $USER, $DB;

    // Get disclaimer in table tool_disclaimer_log
    $disclaimer = $DB->get_record('tool_disclaimer_log', ['userid' => $USER->id, 'objectid' => $course->id], '*', IGNORE_MISSING);
    // Check if the user has the capability to view disclaimers.
    if ($disclaimer) {
        $parentnode->add(
            get_string('disclaimers', 'tool_disclaimer'),
            new moodle_url('/admin/tool/disclaimer/edit_my_disclaimer.php', ['id' => $disclaimer->id, 'courseid' => $course->id]),
            navigation_node::TYPE_CUSTOM,
            null,
            'tool_disclaimer'
        );
    }
}

/**
 * Display link in course navigation to disclaimer page
 */
function tool_disclaimer_extend_navigation_frontpage(
    navigation_node $parentnode,
    stdClass        $course,
    context_course  $context
)
{
    global $USER, $DB;

    // Get disclaimer in table tool_disclaimer_log
    $disclaimer = $DB->get_record('tool_disclaimer_log', ['userid' => $USER->id, 'objectid' => $course->id], '*', IGNORE_MISSING);
    // Check if the user has the capability to view disclaimers.
    if ($disclaimer) {
        $parentnode->add(
            get_string('disclaimers', 'tool_disclaimer'),
            new moodle_url('/admin/tool/disclaimer/edit_my_disclaimer.php', ['id' => $disclaimer->id, 'courseid' => $course->id]),
            navigation_node::TYPE_CUSTOM,
            null,
            'tool_disclaimer'
        );
    }
}