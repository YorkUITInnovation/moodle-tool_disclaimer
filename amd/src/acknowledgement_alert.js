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

/**
 * AMD module for the Acknowledgement disclaimer modal.
 *
 * Renders a single-button (OK) modal for disclaimers of type 'acknowledgement'.
 * Once the user clicks OK the response is saved via web service (response = 1, objectid = 0)
 * and the modal is destroyed. The modal will never appear again for this user.
 *
 * @module     tool_disclaimer/acknowledgement_alert
 * @copyright  2026 York University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Modal from 'core/modal';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';
import ajax from 'core/ajax';
import notification from 'core/notification';

/**
 * Initialise the acknowledgement modal.
 *
 * @param {Object} results
 * @param {number} results.disclaimerid
 * @param {number} results.userid
 */
export const init = async(results) => {

    // Fetch full disclaimer data (subject + message) from the web service.
    let params;
    try {
        params = await fetchData(results.disclaimerid, results.userid);
    } catch (e) {
        // fetchData already showed a notification — bail silently.
        return;
    }

    // objectid is always 0 for acknowledgement-type disclaimers (not course-scoped).
    params.objectid = 0;

    const modal = await Modal.create({
        title: params.subject,
        body: Templates.render('tool_disclaimer/disclaimer_modal', params),
        footer: Templates.render('tool_disclaimer/acknowledgement_buttons', {
            userid: params.userid,
            disclaimerid: params.id,
        }),
        large: true,
        backdrop: 'static',
        keyboard: false,
    });

    // Remove the header X button once the modal is fully in the DOM.
    // Using ModalEvents.shown ensures the button exists before we query for it.
    // getRoot()[0] scopes the selector to this modal only — other modals are unaffected.
    modal.getRoot().one(ModalEvents.shown, () => {
        modal.getRoot()[0].querySelectorAll('[data-action="hide"], .btn-close').forEach(el => el.remove());
    });

    modal.show();

    let isClosing = false;

    /**
     * Destroy the modal and clean up any leftover Bootstrap backdrop artefacts.
     */
    function hideModal() {
        if (isClosing) {
            return;
        }
        isClosing = true;
        document.body.removeEventListener('click', clickHandler);
        modal.destroy();
        // Bootstrap 5 cleanup.
        setTimeout(() => {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.paddingRight = '';
        }, 50);
    }

    /**
     * Click handler attached to document.body so it catches the footer button
     * regardless of where Bootstrap renders the modal portal.
     *
     * @param {MouseEvent} event
     */
    const clickHandler = (event) => {
        if (isClosing) {
            return;
        }
        if (event.target.closest('#btn-tool-disclaimer-ok')) {
            // response = 1 (accepted / acknowledged), objectid = 0 (system-wide).
            saveResponse(params.id, params.userid, 1, 0);
            hideModal();
        }
    };

    document.body.addEventListener('click', clickHandler);
};

/**
 * Persist the user's acknowledgement via the existing tool_disclaimer web service.
 *
 * @param {number} disclaimerid
 * @param {number} userid
 * @param {number} response  1 = acknowledged/accepted
 * @param {number} objectid  0 for system-scoped acknowledgements
 */
function saveResponse(disclaimerid, userid, response, objectid) {
    const call = ajax.call([{
        methodname: 'tool_disclaimer_response',
        args: {userid, disclaimerid, response, objectid},
    }]);
    call[0].fail(() => {
        notification.alert('Could not save acknowledgement');
    });
}

/**
 * Fetch the disclaimer record from the server.
 *
 * @param {number} disclaimerid
 * @param {number} userid
 * @returns {Promise<Object>}
 */
function fetchData(disclaimerid, userid) {
    return new Promise((resolve, reject) => {
        const call = ajax.call([{
            methodname: 'tool_disclaimer_get_disclaimer',
            args: {id: disclaimerid},
        }]);
        call[0].done((data) => {
            data.userid = userid;
            resolve(data);
        }).fail(() => {
            notification.alert('Could not load disclaimer');
            reject();
        });
    });
}
