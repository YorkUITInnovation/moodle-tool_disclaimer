<?php

namespace tool_disclaimer\forms;

use moodleform;

require_once("$CFG->libdir/formslib.php");

class edit_disclaimer_form extends moodleform
{
    public function definition()
    {
        GLOBAL $USER;
        $formdata = $this->_customdata['formdata'];
        $mform = $this->_form;

        $context = \context_system::instance();

        // Add id element
        $mform->addElement(
            'hidden',
            'id'
        );
        $mform->setType(
            'id',
            PARAM_INT
        );

        // Add name element
        $mform->addElement(
            'text',
            'name',
            get_string('name', 'tool_disclaimer')
        );
        $mform->setType(
            'name',
            PARAM_TEXT
        );

        $mform->addRule(
            'name',
            get_string('field_required', 'tool_disclaimer'),
            'required'
        );

        $context_options = [
            'course' => get_string('course', 'tool_disclaimer'),
            'early_alert' => get_string('early_alert', 'tool_disclaimer'),
        ];
        // Add context select element
        $mform->addElement(
            'select',
            'context',
            get_string('context', 'tool_disclaimer'),
            $context_options
        );
        $mform->setType(
            'context',
            PARAM_TEXT
        );

        // Add subject element
        $mform->addElement(
            'text',
            'subject',
            get_string('subject', 'tool_disclaimer')
        );
        $mform->setType(
            'subject',
            PARAM_TEXT
        );

        $mform->addRule(
            'subject',
            get_string('field_required', 'tool_disclaimer'),
            'required'
        );

        // Add message element
        $mform->addElement(
            'editor',
            'message_editor',
            get_string('message', 'tool_disclaimer'),
            $formdata->id,
            \tool_disclaimer\helper::getTextFieldOptions($context)
        );
        $mform->setType(
            'message_editor',
            PARAM_RAW
        );

        $mform->addRule(
            'message_editor',
            get_string('field_required', 'tool_disclaimer'),
            'required'
        );

        // Add header for options
        $mform->addElement(
            'header',
            'options',
            get_string('options', 'tool_disclaimer')
        );

        // Add selectyesno element excludesite
        $mform->addElement(
            'selectyesno',
            'excludesite',
            get_string('exclude_site', 'tool_disclaimer')
        );
        $mform->setType(
            'excludesite',
            PARAM_INT
        );

        // Add element redirectto
        $mform->addElement(
            'text',
            'redirectto',
            get_string('redirect_to_url', 'tool_disclaimer')
        );
        $mform->setType(
            'redirectto',
            PARAM_TEXT
        );

        // Add help button
        $mform->addHelpButton(
            'redirectto',
            'redirect_to_url',
            'tool_disclaimer'
        );

        // Get role data
        $role_options = ['multiple' => true, 'ajax' => 'tool_disclaimer/roles',   'noselectionstring' => get_string('role')];
        $roles = [];
        // Add autocomplete element for user using AMD
        $mform->addElement(
            'autocomplete',
            'roles',
            get_string('role'),
            $roles,
            $role_options
        );

        // Hide if context is early_alert
        $mform->hideIf(
            'roles',
            'context',
            'eq',
            'early_alert'
        );


        // Add element published
        $mform->addElement(
            'selectyesno',
            'published',
            get_string('published', 'tool_disclaimer')
        );
        $mform->setType(
            'published',
            PARAM_INT
        );


        $mform->setType(
            'publishedstart',
            PARAM_INT
        );
        // Ony visible if published is set to no
        $mform->hideIf(
            'publishedstart',
            'published',
            'eq',
            1
        );

        // Add element datetime publishedend
        $mform->addElement(
            'date_time_selector',
            'publishedend',
            get_string('publish_until', 'tool_disclaimer')
        );
        $mform->setType(
            'publishedend',
            PARAM_INT
        );
        // Ony visible if published is set to no
        $mform->hideIf(
            'publishedend',
            'published',
            'eq',
            1
        );

        // Add action buttons
        $this->add_action_buttons();

        $this->set_data($formdata);
    }
}