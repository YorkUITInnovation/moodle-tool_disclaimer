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

    if (is_siteadmin($USER)) {
        // If the user is a site admin, do not display the disclaimer.
        return;
    }

    $data = (object)$event->get_data();

    $courseid = $data->courseid;

    $course_context = context_course::instance($courseid);
    // Get user roles in course
    $roles = get_user_roles($course_context, $USER->id);

    $sql = "SELECT * FROM {tool_disclaimer} WHERE context = 'course'";
    $sql .= " AND ((published = 1) OR (NOW() BETWEEN publishedstart AND publishedend))";
    // Get disclaimer
    if ($course_disclaimer = $DB->get_record_sql($sql)) {
        $DISCLAIMER = new disclaimer($course_disclaimer->id);

        $user_response_sql = "SELECT * FROM {tool_disclaimer_log} WHERE disclaimerid = ? AND userid = ? AND objectid = ? AND response IS NOT NULL";

        $disclaimer_roles = $DISCLAIMER->get_roles();
        // are there any roles to check against?
        if (!empty($disclaimer_roles)) {
            // Check to see if the user has a role that requires the disclaimer
            foreach ($roles as $role) {
                if (in_array($role->shortname, $disclaimer_roles)) {
                    $user_responded = $DB->get_record_sql($user_response_sql, array($course_disclaimer->id, $USER->id, $courseid));
                    if (!$user_responded || $user_responded->response == 0) {
                        $params = new stdClass();
                        $params->userid = $USER->id;
                        $params->objectid = (int)$courseid;
                        $params->disclaimerid = (int)$course_disclaimer->id;

                        $PAGE->requires->js_call_amd('tool_disclaimer/disclaimer_alert', 'init', array($params));
                    }
                }
            }
        } else {
            // Is this a site-wide disclaimer?
            if ($DISCLAIMER->get_frontpageonly() == 1 && $courseid != 1) {
                $user_responded = $DB->get_record_sql($user_response_sql, array($course_disclaimer->id, $USER->id, $courseid));
                if (!$user_responded || $user_responded->response == 0) {
                    $course_disclaimer->userid = $USER->id;
                    $course_disclaimer->objectid = (int)$courseid;
                    $PAGE->requires->js_call_amd('tool_disclaimer/disclaimer_alert', 'init', array($course_disclaimer));
                }
            } else if ($courseid == 1) {
                $user_responded = $DB->get_record_sql($user_response_sql, array($course_disclaimer->id, $USER->id, $courseid));
                if (!$user_responded || $user_responded->response == 0) {
                    $course_disclaimer->userid = $USER->id;
                    $course_disclaimer->objectid = (int)$courseid;
                    $PAGE->requires->js_call_amd('tool_disclaimer/disclaimer_alert', 'init', array($course_disclaimer));
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

    if (is_siteadmin($USER)) {
        // If the user is a site admin, do not display the disclaimer.
        return;
    }

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