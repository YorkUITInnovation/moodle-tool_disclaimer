<?php
namespace tool_disclaimer\forms;

use moodleform;

require_once("$CFG->libdir/formslib.php");

/**
 * Filter form for the user responses page.
 *
 * @package    tool_disclaimer
 * @copyright  2026 ED&IT York University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_response_filter_form extends moodleform {

    public function definition() {
        global $USER;
        $formdata = $this->_customdata['formdata'];
        $mform = $this->_form;

        // Add header.
        $mform->addElement('header', 'user_responses_filter', get_string('user_responses', 'tool_disclaimer'));

        // User ID field.
        $mform->addElement('text', 'userid', get_string('userid', 'tool_disclaimer'));
        $mform->setType('userid', PARAM_INT);

        // First name field.
        $mform->addElement('text', 'firstname', get_string('firstname'));
        $mform->setType('firstname', PARAM_TEXT);

        // Last name field.
        $mform->addElement('text', 'lastname', get_string('lastname'));
        $mform->setType('lastname', PARAM_TEXT);

        // Context dropdown (exclude acknowledgement — those are handled separately).
        $contexts = [
            '' => get_string('all', 'tool_disclaimer'),
            'course' => get_string('course', 'tool_disclaimer'),
            'early_alert' => get_string('early_alert', 'tool_disclaimer'),
            'acknowledgement' => get_string('acknowledgement', 'tool_disclaimer'),
        ];
        $mform->addElement('select', 'context', get_string('context', 'tool_disclaimer'), $contexts);

        // Response status dropdown.
        $responses = [
            '-1' => get_string('all', 'tool_disclaimer'),
            '1'  => get_string('accepted', 'tool_disclaimer'),
            '0'  => get_string('declined', 'tool_disclaimer'),
        ];
        $mform->addElement('select', 'response', get_string('response_status', 'tool_disclaimer'), $responses);

        // Button group.
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('filter', 'tool_disclaimer'));
        $buttonarray[] = $mform->createElement('cancel', 'resetbutton', get_string('reset', 'tool_disclaimer'));
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

        $this->set_data($formdata);
    }
}
