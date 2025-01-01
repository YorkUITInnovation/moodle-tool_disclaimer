<?php
$observers = [
    [
        'eventname' => '\core\event\course_viewed',
        'callback' => 'tool_disclaimer_course_viewed',
        'includefile' => '/admin/tool/disclaimer/eventslib.php'
    ],
    [
        'eventname' => '\local_earlyalert\event\earlyalert_viewed',
        'callback' => 'tool_disclaimer_earlyalert_viewed',
        'includefile' => '/admin/tool/disclaimer/eventslib.php'
    ]
];