<?php

namespace tool_disclaimer\forms;
use moodleform;

require_once("$CFG->libdir/formslib.php");

class disclaimer_filter_form extends moodleform
{
    public function definition()
    {
        GLOBAL $USER;
        $formdata = $this->_customdata['formdata'];
        $mform = $this->_form;


        $context = \context_system::instance();

        // Conditionally show button groups based on capability .. there might be a better way to do this with just an element but I'm not sure yet
        $system_context = \context_system::instance();
        // Add header disclaimers
        $mform->addElement(
            'header',
            'disclaimers',
            get_string('disclaimers', 'tool_disclaimer')
        );
        if (has_capability('tool/disclaimer:edit', $system_context, $USER->id)) {
            // Group the text input and submit button
            $mform->addGroup(array(
                $mform->createElement(
                    'text',
                    'q',
                    get_string('name', 'tool_disclaimer')
                ),
                $mform->createElement(
                    'submit',
                    'submitbutton',
                    get_string('filter', 'tool_disclaimer'),
                    array('onclick' => 'window.location.href = \'edit_disclaimer.php\';')
                ),
                $mform->createElement(
                    'cancel',
                    'resetbutton',
                    get_string('reset', 'tool_disclaimer')
                ),
                $mform->createElement(
                    'button',
                    'adddisclaimer',
                    get_string('new', 'tool_disclaimer'),
                    array('onclick' => 'window.location.href = \'edit_disclaimer.php\';')
                )
            ), 'filtergroup', '', array(''), false);
        }
        else {
            $mform->addGroup(array(
                $mform->createElement(
                    'text',
                    'q',
                    get_string('name', 'tool_disclaimer')
                ),
                $mform->createElement(
                    'submit',
                    'submitbutton',
                    get_string('filter', 'tool_disclaimer'),
                    array('onclick' => 'window.location.href = \'edit_unit.php?campus_id=' . $formdata->campus_id . '\';')
                ),
                $mform->createElement(
                    'cancel',
                    'resetbutton',
                    get_string('reset', 'tool_disclaimer')
                )
            ), 'filtergroup', '', array(''), false);
        }
        $mform->setType('q', PARAM_NOTAGS);

        $this->set_data($formdata);
    }
}