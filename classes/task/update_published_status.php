<?php
/**
 * *************************************************************************
 * *                           YULearn ELMS                               **
 * *************************************************************************
 * @package     local                                                     **
 * @subpackage  yulearn                                                   **
 * @name        YULearn ELMS                                              **
 * @copyright   UIT - Innovation lab & EAAS                               **
 * @link                                                                  **
 * @author      Patrick Thibaudeau                                        **
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later  **
 * *************************************************************************
 * ************************************************************************ */

namespace tool_disclaimer\task;

use local_yulearn\Team;
use local_yulearn\YorkHr;

class update_published_status extends \core\task\scheduled_task
{

    /**
     *
     * @global \moodle_database $DB
     */
    public function execute()
    {
        global $DB;

        // Get all disclaimers with usedaterange
        $disclaimers = $DB->get_records('tool_disclaimer', ['usepublisheddate' => 1]);
        // Loop through disclaimers
        foreach ($disclaimers as $disclaimer) {
            // if the current date is between publishedstart and publishedend, update the record to published
            if (time() >= $disclaimer->publishedstart && time() <= $disclaimer->publishedend) {
                // Check to see if another disclaimer is published for the same context and id is not equal to this disclaimer
                $sql = "SELECT * 
                        FROM {tool_disclaimer} 
                        WHERE context = ? 
                        AND published = 1
                        AND id != ?";
                if ($results = $DB->get_records_sql($sql, [$disclaimer->context, $disclaimer->id])) {
                    // Loop through the results and update them to unpublished
                    foreach ($results as $result) {
                        $result->published = 0;
                        $DB->update_record('tool_disclaimer', $result);
                    }
                }
                // Update the current disclaimer to published
                $disclaimer->published = 1;
                $DB->update_record('tool_disclaimer', $disclaimer);
            } else {
                // if the current date is not between publishedstart and publishedend, update the record to unpublished
                $disclaimer->published = 0;
                $DB->update_record('tool_disclaimer', $disclaimer);
            }
        }
    }

    public function get_name(): string
    {
        return get_string('update_published_status', 'tool_disclaimer');
    }

    public function get_run_if_component_disabled()
    {
        return true;
    }

}
