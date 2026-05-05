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
 * @param {string} results.savedKey localStorage key set after user acknowledges
 */
export const init = async(results) => {

    // Fetch full disclaimer data (subject + message) from the web service.
    let params;
    try {
        params = await fetchData(results.disclaimerid, results.userid);
    } catch (e) {
        // FetchData already showed a notification — bail silently.
        return;
    }

    // Objectid is always 0 for acknowledgement-type disclaimers (not course-scoped).
    params.objectid = 0;

    // Pass savedKey through so the click handler can set it on acknowledgement.
    params.savedKey = results.savedKey;

    const pendingttlms = 5 * 60 * 1000;

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
        removeOnClose: true,
    });

    // Once the modal is fully in the DOM:
    //  1. Remove the header X / close button.
    //  2. Set Bootstrap 5 static-backdrop and no-keyboard data attributes directly
    //     on the .modal element — Moodle's core/modal does not forward the
    //     backdrop/keyboard options to Bootstrap, so we must set them ourselves.
    modal.getRoot().one(ModalEvents.shown, () => {
        const root = modal.getRoot()[0];

        // Remove dismiss controls.
        root.querySelectorAll('[data-action="hide"], .btn-close').forEach(el => el.remove());

        // Force Bootstrap 5 static backdrop + no keyboard dismiss.
        const modalEl = root.querySelector('.modal');
        if (modalEl) {
            modalEl.setAttribute('data-bs-backdrop', 'static');
            modalEl.setAttribute('data-bs-keyboard', 'false');
        }
    });

    modal.show();

    let isClosing = false;

    // Guard: if the modal is hidden by any means other than the OK button
    // (e.g. Moodle's own backdrop click handler or Escape key), re-show it
    // immediately so the user cannot dismiss it without acknowledging.
    modal.getRoot().on(ModalEvents.hidden, () => {
        if (isClosing) {
            modal.destroy();
        } else {
            modal.show();
        }
    });

    /**
     * Hide the modal after acknowledgement has been saved.
     */
    function hideModal() {
        if (isClosing) {
            return;
        }
        isClosing = true;
        document.body.removeEventListener('click', clickHandler);
        modal.hide();
    }

     /**
      * Click handler attached to document.body so it catches the footer button
      * regardless of where Bootstrap renders the modal portal.
      *
      * @param {MouseEvent} event
      */
     const clickHandler = function(event) {
         if (isClosing) {
             return;
         }
         if (event.target.closest('#btn-tool-disclaimer-ok')) {
             event.preventDefault();

             const okbutton = event.target.closest('#btn-tool-disclaimer-ok');
             if (okbutton) {
                 okbutton.disabled = true;
             }

             if (typeof localStorage !== 'undefined' && params.savedKey) {
                 localStorage.setItem(params.savedKey, JSON.stringify({
                     status: 'pending',
                     expires: Date.now() + pendingttlms,
                 }));
             }

             // Response = 1 (accepted / acknowledged), objectid = 0 (system-wide).
             saveResponse(params.id, params.userid, 1, 0)
                 .then(() => {
                     if (typeof localStorage !== 'undefined' && params.savedKey) {
                         localStorage.setItem(params.savedKey, JSON.stringify({
                             status: 'saved',
                         }));
                     }

                     hideModal();
                 })
                 .catch(() => {
                     if (typeof localStorage !== 'undefined' && params.savedKey) {
                         localStorage.removeItem(params.savedKey);
                     }

                     if (okbutton) {
                         okbutton.disabled = false;
                     }
                 });
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
    return new Promise((resolve, reject) => {
        const call = ajax.call([{
            methodname: 'tool_disclaimer_response',
            args: {userid, disclaimerid, response, objectid},
        }]);

        call[0].done(resolve).fail(() => {
            notification.alert('Could not save acknowledgement');
            reject();
        });
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
