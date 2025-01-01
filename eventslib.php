<?php

use tool_disclaimer\disclaimer;

function tool_disclaimer_course_viewed($event)
{
    global $DB, $PAGE, $USER;
    $data = (object)$event->get_data();

    $courseid = $data->courseid;

    $course_context = context_course::instance($courseid);
    // Get user roles in course
    $roles = get_user_roles($course_context, $USER->id);

    $sql = "SELECT * FROM {tool_disclaimer} WHERE context = 'course'";
    $sql .= " AND ((published = 1) OR (NOW() BETWEEN publishedstart AND publishedend))";
    // Get disclaimer
    if ($early_alert_disclaimer = $DB->get_record_sql($sql)) {
        $DISCLAIMER = new disclaimer($early_alert_disclaimer->id);
        $disclaimer_roles = $DISCLAIMER->get_roles();
        // are there any roles to check against?
        if (!empty($disclaimer_roles)) {
            // Check to see if the user has a role that requires the disclaimer
            foreach ($roles as $role) {
                if (in_array($role->shortname, $disclaimer_roles)) {
                    $user_response_sql = "SELECT * FROM {tool_disclaimer_log} WHERE disclaimerid = ? AND userid = ? AND objectid = ? AND response IS NOT NULL";
                    if (!$user_responded = $DB->get_record_sql($user_response_sql, array($early_alert_disclaimer->id, $USER->id, $courseid))) {
                        $early_alert_disclaimer->userid = $USER->id;
                        $early_alert_disclaimer->objectid = (int)$courseid;
                        $PAGE->requires->js_call_amd('tool_disclaimer/disclaimer_alert', 'init', array($early_alert_disclaimer));
                    }
                }
            }
        } else {
            // Is this a site-wide disclaimer?
            if ($DISCLAIMER->get_excludesite() == 1 && $courseid != 1) {
                $user_response_sql = "SELECT * FROM {tool_disclaimer_log} WHERE disclaimerid = ? AND userid = ? AND objectid = ? AND response IS NOT NULL";
                if (!$user_responded = $DB->get_record_sql($user_response_sql, array($early_alert_disclaimer->id, $USER->id, $courseid))) {
                    $early_alert_disclaimer->userid = $USER->id;
                    $early_alert_disclaimer->objectid = (int)$courseid;
                    $PAGE->requires->js_call_amd('tool_disclaimer/disclaimer_alert', 'init', array($early_alert_disclaimer));
                }
            } else if ($courseid == 1) {
                $user_response_sql = "SELECT * FROM {tool_disclaimer_log} WHERE disclaimerid = ? AND userid = ? AND objectid = ? AND response IS NOT NULL";
                if (!$user_responded = $DB->get_record_sql($user_response_sql, array($early_alert_disclaimer->id, $USER->id, $courseid))) {
                    $early_alert_disclaimer->userid = $USER->id;
                    $early_alert_disclaimer->objectid = (int)$courseid;
                    $PAGE->requires->js_call_amd('tool_disclaimer/disclaimer_alert', 'init', array($early_alert_disclaimer));
                }
            }
        }

    }
}

function tool_disclaimer_earlyalert_viewed($event)
{
    global $DB, $USER, $PAGE;
    $data = (object)$event->get_data();

    $sql = "SELECT * FROM {tool_disclaimer} WHERE context = 'early_alert'";
    $sql .= " AND ((published = 1) OR (NOW() BETWEEN publishedstart AND publishedend))";

    if ($early_alert_disclaimer = $DB->get_record_sql($sql)) {
        $user_response_sql = "SELECT * FROM {tool_disclaimer_log} WHERE disclaimerid = ? AND userid = ? AND response IS NOT NULL";
        if (!$user_responded = $DB->get_record_sql($user_response_sql, array($early_alert_disclaimer->id, $USER->id))) {
            $early_alert_disclaimer->userid = $USER->id;
            $early_alert_disclaimer->objectid = (int)0;
            $PAGE->requires->js_call_amd('tool_disclaimer/disclaimer_alert', 'init', array($early_alert_disclaimer));
        }
    }

}