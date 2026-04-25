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
    'tool_disclaimer_get_withdraw_status' => array(
        'classname' => 'tool_disclaimer_withdraw_ws',
        'methodname' => 'get_status',
        'classpath' => 'admin/tool/disclaimer/classes/external/withdraw_ws.php',
        'description' => 'Get accepted disclaimers for the current user (self-service)',
        'type' => 'read',
        'capabilities' => '',
        'ajax' => true
    ),
    'tool_disclaimer_withdraw' => array(
        'classname' => 'tool_disclaimer_withdraw_ws',
        'methodname' => 'withdraw',
        'classpath' => 'admin/tool/disclaimer/classes/external/withdraw_ws.php',
        'description' => 'Withdraw selected or all disclaimer acknowledgements for the current user (self-service)',
        'type' => 'write',
        'capabilities' => '',
        'ajax' => true
    ),
    'tool_disclaimer_manage_get_withdraw_status' => array(
        'classname' => 'tool_disclaimer_withdraw_ws',
        'methodname' => 'manage_get_status',
        'classpath' => 'admin/tool/disclaimer/classes/external/withdraw_ws.php',
        'description' => 'Admin/manager: get accepted disclaimers for any user',
        'type' => 'read',
        'capabilities' => 'tool/disclaimer:withdrawmanage',
        'ajax' => true
    ),
    'tool_disclaimer_manage_withdraw' => array(
        'classname' => 'tool_disclaimer_withdraw_ws',
        'methodname' => 'manage_withdraw',
        'classpath' => 'admin/tool/disclaimer/classes/external/withdraw_ws.php',
        'description' => 'Admin/manager: withdraw selected or all disclaimers for any user',
        'type' => 'write',
        'capabilities' => 'tool/disclaimer:withdrawmanage',
        'ajax' => true
    ),

);