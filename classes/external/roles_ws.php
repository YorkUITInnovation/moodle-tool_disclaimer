<?php

require_once($CFG->libdir . "/externallib.php");
require_once("$CFG->dirroot/config.php");

class tool_disclaimer_roles_ws extends external_api
{
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_role_parameters()
    {
        return new external_function_parameters(
            array(
                'term' => new external_value(PARAM_TEXT, 'Role name', VALUE_OPTIONAL, ''),
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
    public static function get_role($term = '')
    {
        global $DB;

        //Parameter validation
        $params = self::validate_parameters(self::get_role_parameters(), array(
                'term' => $term
            )
        );

        $sql = "SELECT * FROM {role} WHERE shortname LIKE ?";
        $roles = $DB->get_records_sql($sql, array('%' . $term . '%'));

        $results = [];
        $i = 0;
        foreach ($roles as $role) {
            if (empty($role->name)) {
                switch ($role->shortname) {
                    case 'editingteacher':
                        $role->name = get_string('role_teacher', 'tool_disclaimer');
                        break;
                    case 'student':
                        $role->name = get_string('role_student', 'tool_disclaimer');
                        break;
                    case 'manager':
                        $role->name = get_string('role_manager', 'tool_disclaimer');
                        break;
                    case 'coursecreator':
                        $role->name = get_string('role_course_creator', 'tool_disclaimer');
                        break;
                    case 'teacher':
                        $role->name = get_string('role_non-editing_teacher', 'tool_disclaimer');
                        break;
                    case 'guest':
                        $role->name = get_string('role_guest', 'tool_disclaimer');
                        break;
                    case 'user':
                        $role->name = get_string('role_authenticated_user', 'tool_disclaimer');
                        break;
                    case 'frontpage':
                        $role->name = get_string('role_authenticated_user_home', 'tool_disclaimer');
                        break;
                }

            }
            $results[$i]['value'] = $role->shortname;
            $results[$i]['label'] = $role->name;
            $i++;
        }

        return $results;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_role_returns()
    {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'value' => new external_value(PARAM_TEXT, 'Role shortname', false),
                    'label' => new external_value(PARAM_TEXT, 'Role name', true)
                )
            )
        );
    }
}