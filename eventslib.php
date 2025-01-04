<?php

/**
 * @package   tool_disclaimer
 * @copyright 2025 Patrick Thibaudeau
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use tool_disclaimer\disclaimer;

/**
 * Display disclaimer for course
 * @param $event
 */
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
                        $params = new stdClass();
                        $params->userid = $USER->id;
                        $params->objectid = (int)$courseid;
                        $params->disclaimerid = (int)$early_alert_disclaimer->id;

                        $PAGE->requires->js_call_amd('tool_disclaimer/disclaimer_alert', 'init', array($params));
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

/**
 * Display disclaimer for early alert
 * @param $event
 */
function tool_disclaimer_earlyalert_viewed($event)
{
    global $CFG, $DB, $USER, $PAGE;
    $data = (object)$event->get_data();

    $sql = "SELECT * FROM {tool_disclaimer} WHERE context = 'early_alert'";
    $sql .= " AND ((published = 1) OR (NOW() BETWEEN publishedstart AND publishedend))";

    if ($early_alert_disclaimer = $DB->get_record_sql($sql)) {
        // Check if user has already responded to disclaimer
        $user_response_sql = "SELECT * FROM {tool_disclaimer_log} WHERE disclaimerid = ? AND userid = ?";
        $user_responded = $DB->get_record_sql($user_response_sql, array($early_alert_disclaimer->id, $USER->id));
        // Check if the user has already responded true to the disclaimer
        if ($user_responded->response == 1) {
            return;
        }
        // If no record exists, insert a new record
        if ($user_responded == false) {
            // Insert new record
            $user_responded = new stdClass();
            $user_responded->disclaimerid = $early_alert_disclaimer->id;
            $user_responded->userid = $USER->id;
            $user_responded->response = 0;
            $user_responded->objectid = 0;
            $user_responded->timemodified = time();
            $user_responded->timecreated = time();
            $user_responded->usermodified = $USER->id;
            $user_responded->attempt = 0;
            $DB->insert_record('tool_disclaimer_log', $user_responded);
        }
        // If user has not responded to disclaimer, display disclaimer
        if ($user_responded->response == 0) {
            $params = new stdClass();
            $params->userid = (int)$USER->id;
            $params->objectid = 0;
            $params->disclaimerid = (int)$early_alert_disclaimer->id;
            $PAGE->requires->js_call_amd('tool_disclaimer/disclaimer_alert', 'init', array($params));
        } else {
            // If user responded no and a redirect url exists, redirect the user.
            if ($user_responded->response == 2) {
                if ($early_alert_disclaimer->redirectto) {
                    redirect($CFG->wwwroot . $early_alert_disclaimer->redirectto);
                }
            }
        }
    }
}