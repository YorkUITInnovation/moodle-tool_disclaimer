<?php
require_once("$CFG->libdir/formslib.php");
class edit_my_disclaimer_form extends moodleform {

    /**
     * Define the form.
     */
    public function definition() {

        $formdata = $this->_customdata['formdata'];
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        // Add courseid
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        // Add a select field with Yes and No options.
        $mform->addElement('select', 'response', get_string('change_response', 'tool_disclaimer'), [
            1 => get_string('yes'),
            2 => get_string('no')
        ]);

        // Add a submit button.
        $this->add_action_buttons();

        $this->set_data($formdata);
    }
}
