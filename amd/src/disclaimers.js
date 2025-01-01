import notification from 'core/notification';
import ajax from 'core/ajax';
import {get_string as getString} from 'core/str';

export const init = () => {
    deleteDisclaimer();
};

/**
 * Delete Unit
 */
function deleteDisclaimer() {
    // Pop-up notification when .btn-local-organization-delete-campus is clicked
    document.querySelectorAll('.btn-tool-disclaimer-delete-disclaimer').forEach(button => {
        button.addEventListener('click', function () {
            // Get the data id attribute value
            var id = this.getAttribute('data-id');
            var row = this.closest('tr');
            var delete_string = getString('delete', 'tool_disclaimer');
            var delete_body = getString('delete_disclaimer_help', 'tool_disclaimer');
            var cancel = getString('cancel', 'tool_disclaimer');
            var could_not_delete_unit = getString('could_not_delete_disclaimer', 'tool_disclaimer');
            // Notification
            notification.confirm(delete_string, delete_body, delete_string, cancel, function () {
                // Delete the record
                var deleteCampus = ajax.call([{
                    methodname: 'tool_disclaimer_delete',
                    args: {
                        id: id
                    }
                }]);
                deleteCampus[0].done(function () {
                    row.remove();
                }).fail(function () {
                   notification.alert(could_not_delete_unit);
                });
            });

        });
    });

}