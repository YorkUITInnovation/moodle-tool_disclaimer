import notification from 'core/notification';
import ModalFactory from 'core/modal_factory';
import Templates from 'core/templates';
import ajax from 'core/ajax';
import config from 'core/config';

/**
 * Initializes the disclaimer modal, waits for buttons, and attaches event listeners.
 * @param {Object} results - The results object containing disclaimer and user info.
 */
export const init = async (results) => {

   var params = await fetchData(results.disclaimerid, results.userid);
   params.objectid = results.objectid;

    const modal = await ModalFactory.create({
        title: params.subject,
        body: Templates.render('tool_disclaimer/disclaimer_modal', params),
        footer: Templates.render('tool_disclaimer/modal_buttons', params),
        large: true,
    });
    // Prevent the modal from being dismissed when clicking outside
    modal.getRoot().modal({
        backdrop: 'static',
        keyboard: false
    });
    modal.show();

    let isModalClosing = false;
    // Define the event handler function so we can remove it later
    const buttonClickHandler = (event) => {
        // Only handle clicks if modal is still open
        if (isModalClosing) {
            return;
        }

        // Check if the clicked element or any ancestor matches our selectors
        if (event.target.closest('#btn-tool-disclaimer-cancel') ||
            event.target.closest('[data-action="hide"]')) {
            respond(params.id, params.userid, 0, params.objectid, params.redirectto);
            hideModal();
        } else if (event.target.closest('#btn-tool-disclaimer-yes')) {
            respond(params.id, params.userid, 1, params.objectid, '');
            hideModal();
        } else if (event.target.closest('#btn-tool-disclaimer-no')) {
            respond(params.id, params.userid, 2, params.objectid, params.redirectto);
            hideModal();
        }
    };

    /**
     * Hide and destroy the modal simply
     */
    function hideModal() {
        if (isModalClosing) {
            return;
        }
        isModalClosing = true;

        // Remove the event listener to prevent multiple calls
        document.body.removeEventListener('click', buttonClickHandler);

        // Simple approach - just destroy the modal
        modal.destroy();

        // Clean up any leftover backdrop elements
        setTimeout(() => {
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
            document.body.classList.remove('modal-open');
            document.body.style.paddingRight = '';
        }, 50);
    }

    // Add the event listener
    document.body.addEventListener('click', buttonClickHandler);

    // Set parameters for template


};

/**
 * Make ajax call to save the response of the user
 * @param {number} disclaimerid
 * @param {number} userid
 * @param {number} response
 * @param {number} objectid
 * @param {string} redirectto
 */
function respond(disclaimerid, userid, response, objectid, redirectto = '') {
    if (objectid === undefined) {
        objectid = 0;
    }

    const args = {
        userid: userid,
        disclaimerid: disclaimerid,
        response: response,
        objectid: objectid
    };

    var saveResponse = ajax.call([{
        methodname: 'tool_disclaimer_response',
        args: args
    }]);
    saveResponse[0].done(function () {
        if (redirectto) {
            window.location.href = config.wwwroot + redirectto;
        }
    }).fail(function () {
        notification.alert('Could not save response');
    });
}

/**
 * Fetches disclaimer data for a given disclaimer and user.
 * @param {number} disclaimerid - The disclaimer ID.
 * @param {number} userid - The user ID.
 * @returns {Promise<Object>} Resolves with the disclaimer data object.
 */
function fetchData (disclaimerid, userid) {
    return new Promise((resolve, reject) => {
        var getDisclaimer = ajax.call([{
            methodname: 'tool_disclaimer_get_disclaimer',
            args: {
                id: disclaimerid
            }
        }]);
        getDisclaimer[0].done(function (data) {
            data.userid = userid;
            resolve(data);
        }).fail(function () {
            notification.alert('Could not get disclaimer');
            reject();
        });
    });
}