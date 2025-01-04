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

    /**
     * Get disclaimer parameters
     * @return external_function_parameters
     */
    public static function get_disclaimer_parameters()
    {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'Disclaimer id', VALUE_REQUIRED)
            )
        );
    }

    /**
     * Get disclaimer
     * @param $id
     * @return false|mixed|stdClass
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function get_disclaimer($id)
    {
        global $CFG;
        include_once($CFG->libdir . '/filelib.php');

        //Parameter validation
        $params = self::validate_parameters(self::get_disclaimer_parameters(), array(
                'id' => $id
            )
        );

        $context = \context_system::instance();

        $DISCLAIMER = new \tool_disclaimer\disclaimer($id);

        $disclaimer_record = $DISCLAIMER->get_record();
        $disclaimer = [];

        $disclaimer_roles = $DISCLAIMER->get_roles();
        $roles = [];
        if (!empty($disclaimer_roles)) {
            $i = 0;
            foreach ($disclaimer_roles as $role) {
                $roles[$i]['role'] = $role;
                $i++;
            }
        }

        $disclaimer['id'] = $disclaimer_record->id;
        $disclaimer['name'] = $disclaimer_record->name;
        $disclaimer['subject'] = $disclaimer_record->subject;
        $disclaimer['message'] = \file_rewrite_pluginfile_urls($disclaimer_record->message, 'pluginfile.php', $context->id, 'tool_disclaimer', 'assets', $disclaimer_record->id);
        $disclaimer['redirectto'] = $disclaimer_record->redirectto;
        $disclaimer['roles'] = $roles;
file_put_contents('/var/www/moodledata/temp/debug_disclaimer.txt', print_r($disclaimer, true));
        return $disclaimer;
    }

    /**
     * @return external_single_structure
     */
    public static function get_disclaimer_returns() {

          return  new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Disclaimer id', VALUE_OPTIONAL),
                    'name' => new external_value(PARAM_TEXT, 'name', VALUE_REQUIRED),
                    'subject' => new external_value(PARAM_TEXT, 'name', VALUE_REQUIRED),
                    'message' => new external_value(PARAM_RAW, 'name', VALUE_REQUIRED),
                    'redirectto' => new external_value(PARAM_TEXT, 'name', VALUE_OPTIONAL, ''),
                    'roles' => new external_multiple_structure(
                        new external_single_structure(
                            array (
                                'role' => new external_value(PARAM_TEXT, 'role', VALUE_OPTIONAL, ''),
                            )
                        )
                    )
                )
            );
    }

}