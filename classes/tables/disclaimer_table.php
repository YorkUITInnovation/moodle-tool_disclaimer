<?php

namespace tool_disclaimer\tables;

use tool_disclaimer\helper;

require_once('../../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . "/externallib.php");

class disclaimer_table extends \table_sql
{
    protected $show_edit_buttons = false;
    protected $show_delete_buttons = false;

    /**
     * unit_table constructor.
     * @param $uniqueid
     */
    public function __construct($uniqueid)
    {
        GLOBAL $USER;
        parent::__construct($uniqueid);

        // Define the columns to be displayed
        $columns = array('name', 'context', 'actions');
        $this->define_columns($columns);

        // Define the headers for the columns
        $headers = array(
            get_string('name', 'tool_disclaimer'),
            get_string('context', 'tool_disclaimer'),
            '',
        );
        //Capabilities
        $system_context = \context_system::instance();
        if (has_capability('tool/disclaimer:edit', $system_context, $USER->id)) {
            $this->show_edit_buttons = true;
        }
        if (has_capability('tool/disclaimer:delete', $system_context, $USER->id)) {
            $this->show_delete_buttons = true;
        }

        $this->define_headers($headers);
    }

    /**
     * Function to define the actions column
     *
     * @param $values
     * @return string
     */
    public function col_actions($values)
    {
        global $OUTPUT, $DB, $CFG, $USER;

        // Get number of departments in the unit
//        $disclaimer_count = $DB->count_records('tool_disclaimer', []);

        $actions = [
            'edit_url' => $CFG->wwwroot . '/admin/tool/disclaimer/edit_disclaimer.php?id=' . $values->id,
            'showEditButtons' => $this->show_edit_buttons,
            'showDelButtons' => $this->show_delete_buttons,
            'id' => $values->id,
        ];
        return $OUTPUT->render_from_template('tool_disclaimer/disclaimer_table_action_buttons', $actions);
    }
}
