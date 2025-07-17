<?php

require_once($CFG->libdir . "/externallib.php");
require_once("$CFG->dirroot/config.php");

class tool_disclaimer_user_response_ws extends external_api
{
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function response_parameters()
    {
        return new external_function_parameters(
            array(
                'userid' => new external_value(PARAM_INT, 'User id', VALUE_REQUIRED),
                'disclaimerid' => new external_value(PARAM_INT, 'Disclaimer record id', VALUE_REQUIRED),
                'response' => new external_value(PARAM_INT, 'Response: 0,1,2', VALUE_OPTIONAL, 0),
                'objectid' => new external_value(PARAM_INT, 'Object id is can be any objectid triggered by an event such as a courseid', VALUE_OPTIONAL, 0),
            )
        );
    }

    /**
     * @param $userid
     * @param $disclaimerid
     * @param $response
     * @param $objectid
     * @return true
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     */
    public static function response($userid, $disclaimerid, $response = 0, $objectid = 0)
    {
        global $DB;

        //Parameter validation
        $params = self::validate_parameters(self::response_parameters(), array(
                'userid' => $userid,
                'disclaimerid' => $disclaimerid,
                'response' => $response,
                'objectid' => $objectid
            )
        );

        // First look to see if record exists in teh local_tool_disclaimer_log table
        $sql = "SELECT * FROM {tool_disclaimer_log} WHERE disclaimerid = ? AND userid = ? AND objectid = ?";
        if ($record = $DB->get_record_sql($sql, array($disclaimerid, $userid, $objectid))) {
            // Update the record
            $record->response = $response;
            $record->timemodified = time();
            $record->usermodified = $userid;
            $record->attempt = $record->attempt + 1;
            $record->objectid = $objectid;
            $DB->update_record('tool_disclaimer_log', $record);
        } else {
            // Insert a new record
            $record = new stdClass();
            $record->disclaimerid = $disclaimerid;
            $record->userid = $userid;
            $record->response = $response;
            $record->objectid = $objectid;
            $record->timecreated = time();
            $record->timemodified = time();
            $record->attempt = 1;
            $DB->insert_record('tool_disclaimer_log', $record);
        }

        return true;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function response_returns()
    {
        return new external_value(PARAM_INT, 'Boolean');
    }
}