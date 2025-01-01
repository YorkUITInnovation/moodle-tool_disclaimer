import notification from 'core/notification';
import ModalFactory from 'core/modal_factory';
import Templates from 'core/templates';
import ajax from 'core/ajax';
import config from 'core/config';

export const init = async (data) => {
    // Set paramaters for template
    let params = {
        subject: data.subject,
        message: data.message,
        disclaimerid: data.id,
        userid: data.userid,
    };
    const modal = await ModalFactory.create({
        title: data.subject,
        body: Templates.render('tool_disclaimer/modal_buttons', params),
        footer: 'Nothing',
    });
    modal.show();

    // If the user clicks the button with attribute data-action="hide", the modal will be hidden
    document.querySelector('[data-action="hide"]').addEventListener('click', () => {
        respond(data.id, data.userid, null);
        // if dataredirecto is set, redirect to the page
        if (data.redirectto) {
            window.location.href = config.wwwroot + data.redirectto;
        }
    });

    // When button with class btn-tool-disclaimer-cancel is clicked, the modal will be hidden
    document.getElementById('btn-tool-disclaimer-cancel').addEventListener('click', () => {
        respond(data.id, data.userid, null);
        modal.hide();
        if (data.redirectto) {
            window.location.href = config.wwwroot + data.redirectto;
        }
    });

    // When Button with id btn-tool-disclaimer-yes is clicked, the modal will be hidden
    document.getElementById('btn-tool-disclaimer-yes').addEventListener('click', () => {
        respond(data.id, data.userid, 1);
        modal.hide();
    });

    // When Button with id btn-tool-disclaimer-no is clicked, the modal will be hidden
    document.getElementById('btn-tool-disclaimer-no').addEventListener('click', () => {
        respond(data.id, data.userid, 0);
        modal.hide();
        if (data.redirectto) {
            window.location.href = config.wwwroot + data.redirectto;
        }
    });

};

/**
 * Make ajax call to save the response of the user
 * @param {number} disclaimerid
 * @param {number} userid int
 * @param {string} response
 */
function respond(disclaimerid, userid, response) {
    var saveResponse = ajax.call([{
        methodname: 'tool_disclaimer_response',
        args: {
            userid: userid,
            disclaimerid: disclaimerid,
            response: response
        }
    }]);
    saveResponse[0].done(function () {
        // do nothing
    }).fail(function () {
        notification.alert('Could not save response');
    });
}