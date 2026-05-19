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

    /**
     * Fetches the most recent activity statistics snapshot for all tracked modules.
     *
     * This method retrieves the latest recorded count for each activity type by
     * filtering for the maximum timestamp per activity, ordered by volume descending.
     *
     * Each object in the returned array contains:
     * - int `$id`: The ID of the activity module.
     * - string `$activityname`: The internal name of the module (e.g., 'forum', 'assign').
     * - int `$count`: The recorded instance count at that snapshot.
     * - int `$timestamp`: Unix timestamp of the snapshot.
     *
     * @global $DB
     * @return stdClass[] Array of objects representing the latest snapshot per activity, indexed by activity ID.
     * @throws \dml_exception If there is an error interacting with the database.
     */
    public static function get_current_activity_counts(): array
    {
        global $DB;

        $sql = "SELECT c.activityid as id, m.name as activityname, c.count, c.timestamp
                FROM {tool_activitystatistics_counts} c
                JOIN {modules} m on m.id = c.activityid
                JOIN (
                    SELECT activityid, MAX(timestamp) as max_ts
                    FROM {tool_activitystatistics_counts}
                    GROUP BY activityid
                ) latest ON c.activityid = latest.activityid AND c.timestamp = latest.max_ts
                ORDER BY c.count DESC";

        return $DB->get_records_sql($sql);
    }

    /**
     * Retrieves the historical progression of total activity counts over time.
     *
     * Aggregates the sum of all activity instances across the system for each unique
     * timestamp entry, sorted chronologically.
     *
     * Each object in the returned array contains:
     * - int `$timestamp`: Unix timestamp of the historical record (also serves as the array key).
     * - int `$total_sum`: The total combined count of all activities at that specific time.
     *
     * @global $DB
     * @return stdClass[] Array of objects containing historical totals, indexed by their timestamp.
     * @throws \dml_exception If there is an error interacting with the database.
     */
    public static function get_total_count_history() {
        global $DB;

        // Die DB summiert alle Counts (über alle Aktivitäten hinweg) pro Zeitstempel
        $sql = "SELECT timestamp, SUM(count) as total_sum
                  FROM {tool_activitystatistics_counts}
              GROUP BY timestamp
              ORDER BY timestamp ASC";

        // get_records_sql gibt ein Array zurück, bei dem die erste Spalte (timestamp) der Array-Key ist.
        return $DB->get_records_sql($sql);
    }
}