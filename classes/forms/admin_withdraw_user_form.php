<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace tool_disclaimer\forms;

use core_user;
use moodleform;

require_once($CFG->libdir . '/formslib.php');

/**
 * Admin user lookup form for disclaimer withdrawal.
 *
 * @package    tool_disclaimer
 */
class admin_withdraw_user_form extends moodleform {

    /**
     * Define form elements.
     */
    public function definition() {
        $mform = $this->_form;
        $selecteduserid = (int)($this->_customdata['selecteduserid'] ?? 0);
        $context = \context_system::instance();

        $useroptions = [
            'multiple' => false,
            'ajax' => 'core_user/form_user_selector',
            'valuehtmlcallback' => function($userid) use ($context): string {
                global $OUTPUT;

                $fields = \core_user\fields::for_name()->with_identity($context, false);
                $record = core_user::get_user($userid, 'id' . $fields->get_sql()->selects, MUST_EXIST);

                $user = (object)[
                    'id' => $record->id,
                    'fullname' => fullname($record, has_capability('moodle/site:viewfullnames', $context)),
                    'extrafields' => [],
                ];

                foreach ($fields->get_required_fields([\core_user\fields::PURPOSE_IDENTITY]) as $extrafield) {
                    $user->extrafields[] = (object)[
                        'name' => $extrafield,
                        'value' => s($record->$extrafield),
                    ];
                }

                return $OUTPUT->render_from_template('core_user/form_user_selector_suggestion', $user);
            },
        ];

        $mform->addElement('autocomplete', 'userid', get_string('admin_withdraw_userid', 'tool_disclaimer'), [], $useroptions);
        $mform->setType('userid', PARAM_INT);
        $mform->addRule('userid', get_string('required'), 'required', null, 'server');

        $buttons = [];
        $buttons[] = $mform->createElement('submit', 'submitbutton', get_string('admin_withdraw_load_user', 'tool_disclaimer'));
        $mform->addGroup($buttons, 'actions', '', ' ', false);

        if ($selecteduserid > 0) {
            $this->set_data((object)['userid' => $selecteduserid]);
        }
    }
}

