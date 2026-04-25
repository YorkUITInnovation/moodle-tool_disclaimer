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
 * AMD module: admin_withdraw_user
 *
 * Powers the Site Administration -> Users -> Accounts -> "Withdraw Disclaimer"
 * page. Allows admins/managers to look up any user and remove their accepted
 * disclaimer responses.
 *
 * @module     tool_disclaimer/admin_withdraw_user
 * @copyright  2026 York University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Ajax from 'core/ajax';
import ModalSaveCancel from 'core/modal_save_cancel';
import ModalEvents from 'core/modal_events';
import Notification from 'core/notification';
import {getString} from 'core/str';

let currentUser = null;

/**
 * Initialise event bindings on the admin page.
 *
 * @param {{userid?: Number}} params
 */
export const init = (params = {}) => {
    const withdrawBtn = document.getElementById('tool-disclaimer-admin-withdraw-btn');
    if (!withdrawBtn) {
        return;
    }

    withdrawBtn.addEventListener('click', () => void handleWithdraw());

    document.getElementById('tool-disclaimer-admin-disclaimer-list')?.addEventListener('change', (e) => {
        if (e.target.id === 'tool-disclaimer-admin-all') {
            const checked = e.target.checked;
            document.querySelectorAll('.tool-disclaimer-admin-item').forEach((cb) => {
                cb.checked = false;
                cb.disabled = checked;
            });
        }
        toggleWithdrawButton();
    });

    const userid = parseInt(params.userid, 10);
    if (userid > 0) {
        void loadUser(userid);
    }
};

/**
 * Fetch selected user disclaimers and render panel.
 *
 * @param {Number} userid
 */
const loadUser = async(userid) => {
    try {
        const data = await Ajax.call([{
            methodname: 'tool_disclaimer_manage_get_withdraw_status',
            args: {userid},
        }])[0];
        currentUser = data;
        renderUserPanel(data);
    } catch (error) {
        Notification.exception(error);
    }
};
/**
 * Populate the user info banner and disclaimer list.
 *
 * @param {Object} data  Response from manage_get_status.
 */
const renderUserPanel = async(data) => {
    const panel = document.getElementById('tool-disclaimer-admin-user-panel');
    const infoEl = document.getElementById('tool-disclaimer-admin-user-info');
    const listEl = document.getElementById('tool-disclaimer-admin-disclaimer-list');
    const withdrawBtn = document.getElementById('tool-disclaimer-admin-withdraw-btn');
    // Show user info.
    infoEl.textContent = `${data.fullname} (${data.email}) — ID: ${data.userid}`;
    if (data.disclaimers.length === 0) {
        const noneStr = await getString('admin_withdraw_none', 'tool_disclaimer');
        listEl.innerHTML = `<p class="text-muted mb-0">${noneStr}</p>`;
        withdrawBtn.classList.add('d-none');
    } else {
        const selectAllLabel = await getString('withdraw_select_all_disclaimers', 'tool_disclaimer');
        let html = `<div class="form-check mb-2">
            <input id="tool-disclaimer-admin-all" class="form-check-input" type="checkbox">
            <label for="tool-disclaimer-admin-all" class="form-check-label fw-semibold">${selectAllLabel}</label>
        </div>
        <div class="border rounded p-2" style="max-height:300px;overflow-y:auto;">`;
        data.disclaimers.forEach((d) => {
            html += `<div class="form-check">
                <input id="tool-disclaimer-admin-item-${d.id}"
                       class="form-check-input tool-disclaimer-admin-item"
                       type="checkbox" value="${d.id}">
                <label for="tool-disclaimer-admin-item-${d.id}" class="form-check-label">
                    ${d.name} <span class="text-muted">(${d.context})</span>
                </label>
            </div>`;
        });
        html += '</div>';
        listEl.innerHTML = html;
        // Wire "select all" because it was just inserted into the DOM.
        document.getElementById('tool-disclaimer-admin-all')?.addEventListener('change', (e) => {
            const checked = e.target.checked;
            document.querySelectorAll('.tool-disclaimer-admin-item').forEach((cb) => {
                cb.checked = false;
                cb.disabled = checked;
            });
            toggleWithdrawButton();
        });
        document.querySelectorAll('.tool-disclaimer-admin-item').forEach((cb) => {
            cb.addEventListener('change', toggleWithdrawButton);
        });
        withdrawBtn.classList.add('d-none');
    }
    panel.classList.remove('d-none');
};
/**
 * Show/hide the Withdraw button depending on whether anything is checked.
 */
const toggleWithdrawButton = () => {
    const withdrawBtn = document.getElementById('tool-disclaimer-admin-withdraw-btn');
    const allChecked = !!document.getElementById('tool-disclaimer-admin-all')?.checked;
    const anyItemChecked = document.querySelectorAll('.tool-disclaimer-admin-item:checked').length > 0;
    if (allChecked || anyItemChecked) {
        withdrawBtn.classList.remove('d-none');
    } else {
        withdrawBtn.classList.add('d-none');
    }
};
/**
 * Confirm and execute the withdrawal.
 */
const handleWithdraw = async() => {
    if (!currentUser) {
        return;
    }
    const withdrawall = !!document.getElementById('tool-disclaimer-admin-all')?.checked;
    const selected = Array.from(document.querySelectorAll('.tool-disclaimer-admin-item:checked'))
        .map((cb) => parseInt(cb.value, 10))
        .filter((id) => id > 0);
    if (!withdrawall && selected.length === 0) {
        Notification.addNotification({
            type: 'warning',
            message: await getString('withdraw_select_one', 'tool_disclaimer'),
        });
        return;
    }
    const [title, message, button] = await Promise.all([
        getString('withdraw_confirm_title', 'tool_disclaimer'),
        getString('admin_withdraw_confirm_message', 'tool_disclaimer', currentUser.fullname),
        getString('withdraw_now', 'tool_disclaimer'),
    ]);
    const confirmModal = await ModalSaveCancel.create({title, body: message, removeOnClose: true});
    confirmModal.setSaveButtonText(button);
    confirmModal.getRoot().on(ModalEvents.save, async() => {
        try {
            const result = await Ajax.call([{
                methodname: 'tool_disclaimer_manage_withdraw',
                args: {
                    userid: currentUser.userid,
                    disclaimerids: selected,
                    withdrawall: withdrawall,
                },
            }])[0];
            confirmModal.destroy();
            Notification.addNotification({
                type: result.success ? 'success' : 'warning',
                message: result.message,
            });
            if (result.success) {
                // Reload disclaimer list for the same user.
                const refreshed = await Ajax.call([{
                    methodname: 'tool_disclaimer_manage_get_withdraw_status',
                    args: {userid: currentUser.userid},
                }])[0];
                currentUser = refreshed;
                renderUserPanel(refreshed);
            }
        } catch (error) {
            Notification.exception(error);
        }
    });
    confirmModal.getRoot().on(ModalEvents.hidden, () => confirmModal.destroy());
    confirmModal.show();
};
