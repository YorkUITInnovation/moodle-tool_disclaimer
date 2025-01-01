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
 * Potential user selector module.
 *
 * @module     local_organization/users
 * @class      users
 **/

define(['jquery', 'core/ajax', 'core/templates', 'core/str'], function($, Ajax, Templates, Str) {

    return /** @alias module:tool_dislaimer/roles */ {

        transport: function(selector, query, success, failure) { //Fetches results via ajax call
            let promise;
            let perpage = 50;

            promise = Ajax.call([{
                methodname: 'tool_disclaimer_get_role',
                args: {
                    term: query,
                }
            }]);

            promise[0].then(function(results) {
                if (results.length <= perpage) {
                    //console.log(results);
                    //Callback function returns an array to processResults containing the results obtained from the Ajax call
                    success(results);
                    return;
                }
                else {
                    return Str.get_string('toomanyresults', 'local_organization', '>' + perpage).then(function(toomanyresults) {
                        success(toomanyresults);
                        return;
                    });
                }


            }).fail(failure);
        },

        processResults: function(selector, results) { //Fetches results from transport and returns to form menu
            let records = [];
            if ($.isArray(results)) {
                $.each(results, function(index, record) {
                    records.push({
                        //the value of the item selected and that is passed into the form?
                        value: record.value,
                        //The text that displays inside the selection menu
                        label: record.label
                    });
                });
                return records;

            } else {
                return results;
            }
        }


    };

});
