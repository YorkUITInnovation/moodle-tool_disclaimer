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
 * AMD module: disclaimer_withdraw
 *
 * Provides a self-service modal allowing the current logged-in user to
 * withdraw their own disclaimer acknowledgements from the user menu.
 *
 * @module     tool_disclaimer/disclaimer_withdraw
 * @copyright  2026 York University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Ajax from 'core/ajax';
import Modal from 'core/modal';
import ModalSaveCancel from 'core/modal_save_cancel';
import ModalEvents from 'core/modal_events';
import Notification from 'core/notification';
import Templates from 'core/templates';
import {getString} from 'core/str';
let listenerAttached = false;
/**
 * Attach withdrawal trigger listeners once per page.
 */
export const init = () => {
    if (listenerAttached) {
        return;
    }
    listenerAttached = true;
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest(
            '[data-action="open-disclaimer-withdraw-modal"],' +
            'a[href*="disclaimerwithdraw=1"],' +
            'a[href*="disclaimerwithdraw%3D1"]'
        );
        if (!trigger) {
            return;
        }
        event.preventDefault();
        void openStatusModal();
    });
    // Support direct URL trigger: /user/preferences.php?disclaimerwithdraw=1
    const params = new URLSearchParams(window.location.search);
    if (params.get('disclaimerwithdraw') === '1') {
        void openStatusModal();
        params.delete('disclaimerwithdraw');
        const cleanurl = `${window.location.pathname}${params.toString() ? `?${params.toString()}` : ''}${window.location.hash}`;
        window.history.replaceState({}, document.title, cleanurl);
    }
};
/**
 * Load the current user's accepted disclaimers then open the selection modal.
 */
const openStatusModal = async() => {
    try {
        const status = await Ajax.call([{
            methodname: 'tool_disclaimer_get_withdraw_status',
            args: {},
        }])[0];
        await showModal(status);
    } catch (error) {
        Notification.exception(error);
    }
};
/**
 * Render the withdrawal selection modal.
 *
 * @param {Object} status  Result from tool_disclaimer_get_withdraw_status.
 */
const showModal = async(status) => {
    const context = {
        ...status,
        hasdisclaimers: status.disclaimers.length > 0,
    };
    const [body, title, withdrawlabel, cancellabel] = await Promise.all([
        Templates.render('tool_disclaimer/withdraw_modal', context),
        getString('withdraw_title', 'tool_disclaimer'),
        getString('withdraw_now', 'tool_disclaimer'),
        getString('cancel', 'tool_disclaimer'),
    ]);
    const footer =
        `<button class="btn btn-secondary" data-action="cancel">${cancellabel}</button>` +
        `<button class="btn btn-danger" id="tool-disclaimer-withdraw-submit">${withdrawlabel}</button>`;
    const modal = await Modal.create({
        title: title,
        body: body,
        footer: footer,
        removeOnClose: true,
    });
    // "Select all" toggle disables individual checkboxes.
    modal.getRoot().on('change', '#tool-disclaimer-withdraw-all', (event) => {
        const checked = !!event.currentTarget.checked;
        modal.getRoot()[0].querySelectorAll('.tool-disclaimer-withdraw-item').forEach((cb) => {
            cb.checked = false;
            cb.disabled = checked;
        });
    });
    modal.getRoot().on('click', '#tool-disclaimer-withdraw-submit', async() => {
        const root = modal.getRoot()[0];
        const withdrawall = !!root.querySelector('#tool-disclaimer-withdraw-all')?.checked;
        const selected = Array.from(root.querySelectorAll('.tool-disclaimer-withdraw-item:checked'))
            .map((cb) => parseInt(cb.value, 10))
            .filter((id) => id > 0);
        if (!withdrawall && selected.length === 0) {
            Notification.addNotification({
                type: 'warning',
                message: await getString('withdraw_select_one', 'tool_disclaimer'),
            });
            return;
        }
        await confirmWithdraw(modal, selected, withdrawall);
    });
    modal.getRoot().on('click', '[data-action="cancel"]', () => modal.hide());
    modal.getRoot().on(ModalEvents.hidden, () => modal.destroy());
    modal.show();
};
/**
 * Ask the user to confirm, then execute the withdrawal.
 *
 * @param {Object}   parentModal
 * @param {Number[]} disclaimerids
 * @param {Boolean}  withdrawall
 */
const confirmWithdraw = async(parentModal, disclaimerids, withdrawall) => {
    const [title, message, button] = await Promise.all([
        getString('withdraw_confirm_title', 'tool_disclaimer'),
        getString('withdraw_confirm_message', 'tool_disclaimer'),
        getString('withdraw_now', 'tool_disclaimer'),
    ]);
    const confirmModal = await ModalSaveCancel.create({title, body: message, removeOnClose: true});
    confirmModal.setSaveButtonText(button);
    confirmModal.getRoot().on(ModalEvents.save, async() => {
        try {
            const result = await Ajax.call([{
                methodname: 'tool_disclaimer_withdraw',
                args: {disclaimerids, withdrawall},
            }])[0];
            confirmModal.destroy();
            parentModal.destroy();
            Notification.addNotification({
                type: result.success ? 'success' : 'warning',
                message: result.message,
            });
            if (result.success && result.deletedcount > 0) {
                // Re-open to show updated disclaimer list.
                await openStatusModal();
            }
        } catch (error) {
            Notification.exception(error);
        }
    });
    confirmModal.getRoot().on(ModalEvents.hidden, () => confirmModal.destroy());
    confirmModal.show();
};
