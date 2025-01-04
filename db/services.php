<?php

$functions = array(
    'tool_disclaimer_response' => array(
        'classname' => 'tool_disclaimer_user_response_ws',
        'methodname' => 'response',
        'classpath' => 'admin/tool/disclaimer/classes/external/user_response_ws.php',
        'description' => 'Saves the users response to a disclaimer',
        'type' => 'write',
        'capabilities' => '',
        'ajax' => true
    ),
    'tool_disclaimer_get_role' => array(
        'classname' => 'tool_disclaimer_roles_ws',
        'methodname' => 'get_role',
        'classpath' => 'admin/tool/disclaimer/classes/external/roles_ws.php',
        'description' => 'Get roles',
        'type' => 'read',
        'capabilities' => '',
        'ajax' => true
    ),
    'tool_disclaimer_delete' => array(
        'classname' => 'tool_disclaimer_ws',
        'methodname' => 'delete',
        'classpath' => 'admin/tool/disclaimer/classes/external/disclaimer_ws.php',
        'description' => 'Deletes disclaimer, disclaimer roles and logs',
        'type' => 'write',
        'capabilities' => '',
        'ajax' => true
    ),
    'tool_disclaimer_get_disclaimer' => array(
        'classname' => 'tool_disclaimer_ws',
        'methodname' => 'get_disclaimer',
        'classpath' => 'admin/tool/disclaimer/classes/external/disclaimer_ws.php',
        'description' => 'Get disclaimer data',
        'type' => 'read',
        'capabilities' => '',
        'ajax' => true
    ),

);