<?php

require_once($CFG->libdir . "/externallib.php");
require_once("$CFG->dirroot/config.php");

class tool_disclaimer_ws extends external_api
{
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function delete_parameters()
    {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'User id', VALUE_REQUIRED)
            )
        );
    }

    /**
     * @param $id
     * @return true
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     */
    public static function delete($id)
    {
        global $DB;

        //Parameter validation
        $params = self::validate_parameters(self::delete_parameters(), array(
                'id' => $id
            )
        );

        // Delete all roles associated with the disclaimer
        $DB->delete_records('tool_disclaimer_role', ['disclaimerid' => $id]);
        // Delete all user records associated with the disclaimer
        $DB->delete_records('tool_disclaimer_log', ['disclaimerid' => $id]);
        // Delete the record itself
        $DB->delete_records('tool_disclaimer', ['id' => $id]);

        return true;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function delete_returns()
    {
        return new external_value(PARAM_INT, 'Boolean');
    }
}