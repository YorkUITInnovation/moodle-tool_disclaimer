import notification from 'core/notification';
import ModalFactory from 'core/modal_factory';
import Templates from 'core/templates';
import ajax from 'core/ajax';
import config from 'core/config';

export const init = async (results) => {

   var params = await fetchData(results.disclaimerid, results.userid);
   params.objectid = results.objectid;

    const modal = await ModalFactory.create({
        title: params.subject,
        body: Templates.render('tool_disclaimer/disclaimer_modal', params),
        footer: Templates.render('tool_disclaimer/modal_buttons', params),
    });
    // Prevent the modal from being dismissed when clicking outside
    modal.getRoot().modal({
        backdrop: 'static',
        keyboard: false
    });
    modal.show();

    /**
     * Hide the modal and remove the backdrop
     */
    function hideModal() {
        modal.destroy();
        // Remove the backdrop manually using plain JavaScript
        var backdrops = document.getElementsByClassName('modal-backdrop');
        while (backdrops[0]) {
            backdrops[0].parentNode.removeChild(backdrops[0]);
        }
    }

    // Wait 2 seconds to activate teh buttons
    await new Promise(resolve => setTimeout(resolve, 2000));

    // If the user clicks the button with attribute data-action="hide", the modal will be hidden
    await document.querySelector('[data-action="hide"]').addEventListener('click', () => {
        respond(params.id, params.userid, 0, params.objectid, params.redirectto);
        hideModal();
    });

    // When button with class btn-tool-disclaimer-cancel is clicked, the modal will be hidden
    await document.getElementById('btn-tool-disclaimer-cancel').addEventListener('click', () => {
        respond(params.id, params.userid, 0, params.objectid, params.redirectto);
        hideModal();
    });

    // When Button with id btn-tool-disclaimer-yes is clicked, the modal will be hidden
    await document.getElementById('btn-tool-disclaimer-yes').addEventListener('click', () => {
        respond(params.id, params.userid, 1, params.objectid, '');
        hideModal();
    });

    // When Button with id btn-tool-disclaimer-no is clicked, the modal will be hidden
    await document.getElementById('btn-tool-disclaimer-no').addEventListener('click', () => {
        respond(params.id, params.userid, 2, params.objectid, params.redirectto);
        hideModal();
    });

    // Set paramaters for template


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
 *
 * @param {int} disclaimerid
 * @param {int} userid
 * @returns {Promise<unknown>}
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