<?php
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

namespace tool_activitystatistics;

use stdClass;

class data_provider {
    /**
     * Fetches high-level activity statistics from the database.
     *
     * The returned object contains the following properties:
     * - int `$total_activities`: Total number of activity types.
     * - int `$total_count`: Total number of activity instances.
     * - int|false`$last_update`: Unix timestamp of the last update to the plugin's statistics table.
     *
     * Note: If the plugin's statistics table ({tool_activitystatistics_counts})
     * does not have any data yet (e.g., upon initial installation before any
     * data collection has occurred), the last_update value will evaluate to false.
     *
     * @global $DB
     * @return stdClass Object containing the overview statistics.
     * @throws \dml_exception If there is an error interacting with the database.
     */
    public static function get_overview_stats(): stdClass {
        global $DB;

        $overview_stats = new stdClass();

        $overview_stats->total_activities = $DB->count_records('modules');
        $overview_stats->total_count = $DB->get_field_sql('SELECT COUNT(id) FROM {course_modules}') ?: 0;
        $overview_stats->last_update = $DB->get_field_sql('SELECT MAX(timestamp) FROM {tool_activitystatistics_counts}');

        return $overview_stats;
    }
}