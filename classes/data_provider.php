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
     * - int `$total_activities`: Total number of available activity types.
     * - int `$total_count`: Total number of course module instances across the site.
     * - int|false `$last_update`: Unix timestamp of the last update recorded in the statistics table.
     *
     * Note: If the statistics table ({tool_activitystatistics_counts}) contains no records yet,
     * `$last_update` evaluates to false.
     *
     * @global \moodle_database $DB
     * @return stdClass Object containing overview metrics.
     * @throws \dml_exception If a database communication error occurs.
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
     * Retrieves the latest recorded count for each activity type by filtering
     * for the maximum timestamp per activity, ordered descending by volume.
     *
     * @global \moodle_database $DB
     * @return stdClass[] Array of latest snapshot objects, indexed by activity ID.
     * @throws \dml_exception If a database communication error occurs.
     */
    public static function get_current_activity_counts(): array {
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
     * Retrieves the historical progression of total activity counts over time,
     * optionally constrained by a time range.
     *
     * @param int|null $from Optional lower timestamp bound.
     * @param int|null $to Optional upper timestamp bound.
     * @global \moodle_database $DB
     * @return stdClass[] Array of historical records indexed by timestamp.
     * @throws \dml_exception If a database communication error occurs.
     */
    public static function get_total_count_history(?int $from = null, ?int $to = null): array {
        global $DB;
        $criteria = new sql_criteria();

        if ($from) { $criteria->add("timestamp >= :from", ['from' => $from]); }
        if ($to) { $criteria->add("timestamp <= :to", ['to' => $to]); }

        $sql = "SELECT timestamp, SUM(count) as total_sum 
                  FROM {tool_activitystatistics_counts} 
                  " . $criteria->get_where() . "
              GROUP BY timestamp 
              ORDER BY timestamp ASC";

        return $DB->get_records_sql($sql, $criteria->get_params());
    }

    /**
     * Finds the latest total count record strictly before a given timestamp.
     * Used as a fallback data point when a custom time filter starts at a point
     * where no direct snapshot exists, ensuring charts start accurately.
     *
     * @param int $from Upper timestamp limit (exclusive).
     * @return stdClass|null The matching record object, or null if none found.
     * @throws \dml_exception If a database communication error occurs.
     */
    public static function get_fallback_total_count(int $from): ?stdClass {
        global $DB;

        $latest = $DB->get_field_sql(
            'SELECT MAX(timestamp) FROM {tool_activitystatistics_counts} WHERE timestamp < :from',
            ['from' => $from]
        );

        if (!$latest) {
            return null;
        }

        $sql = "SELECT timestamp, SUM(count) as total_sum 
                  FROM {tool_activitystatistics_counts} 
                 WHERE timestamp = :ts 
              GROUP BY timestamp";

        $record = $DB->get_record_sql($sql, ['ts' => $latest]);
        return $record ?: null;
    }

    /**
     * Retrieves the historical progression of activity counts broken down by module over time.
     *
     * @param string[]|null $modnames Optional array of module internal names (e.g., 'forum'). Null = all.
     * @param int|null $from Optional lower timestamp bound.
     * @param int|null $to Optional upper timestamp bound.
     * @return \stdClass[] List of historical records.
     * @throws \dml_exception If a database communication error occurs.
     */
    public static function get_activity_counts_history_by_module(
        ?array $modnames = null,
        ?int $from = null,
        ?int $to = null
    ): array {
        global $DB;
        $criteria = new sql_criteria();

        if (!empty($modnames)) {
            list($insql, $inparams) = $DB->get_in_or_equal($modnames, SQL_PARAMS_NAMED, 'mn');
            $criteria->add("m.name $insql", $inparams);
        }
        if ($from) { $criteria->add("c.timestamp >= :from", ['from' => $from]); }
        if ($to) { $criteria->add("c.timestamp <= :to", ['to' => $to]); }

        $sql = "SELECT c.id, c.timestamp, m.name AS activityname, c.count
                  FROM {tool_activitystatistics_counts} c
                  JOIN {modules} m ON m.id = c.activityid
                  " . $criteria->get_where() . "
              ORDER BY c.timestamp ASC, m.name ASC";

        return $DB->get_records_sql($sql, $criteria->get_params());
    }

    /**
     * Finds the latest module-specific count records strictly before a given timestamp.
     * Acts as a fallback mechanism for the multi-line chart when filtering by custom time ranges.
     *
     * @param string[]|null $modnames Optional list of module names.
     * @param int $from Upper timestamp limit (exclusive).
     * @return array Array of fallback records indexed by record ID.
     * @throws \dml_exception If a database communication error occurs.
     */
    public static function get_fallback_module_counts(?array $modnames, int $from): array {
        global $DB;
        $criteria = new sql_criteria();

        $criteria->add("c.timestamp < :mainfrom", ['mainfrom' => $from]);

        if (!empty($modnames)) {
            list($insql, $inparams) = $DB->get_in_or_equal($modnames, SQL_PARAMS_NAMED, 'mn');
            $criteria->add("m.name $insql", $inparams);
        }

        $params = $criteria->get_params();
        $params['subfrom'] = $from;

        $sql = "SELECT c.id, c.timestamp, m.name AS activityname, c.count
                  FROM {tool_activitystatistics_counts} c
                  JOIN {modules} m ON m.id = c.activityid
                  JOIN (
                      SELECT activityid, MAX(timestamp) as max_ts
                      FROM {tool_activitystatistics_counts}
                      WHERE timestamp < :subfrom 
                      GROUP BY activityid
                  ) latest ON c.activityid = latest.activityid AND c.timestamp = latest.max_ts
                  " . $criteria->get_where('AND ') . "
              ORDER BY m.name ASC";

        return $DB->get_records_sql($sql, $params);
    }
}