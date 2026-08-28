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

/**
 * Service class responsible for processing, padding, and normalizing raw statistical records
 * into clean, chronological datasets ready for chart consumption.
 */
class chart_service {

    /**
     * Prepares total history records by normalizing all timestamps to midnight (day-based)
     * and injecting fallback start points if custom time filters are applied.
     *
     * @param int|null $from Lower timestamp boundary.
     * @param int|null $to Upper timestamp boundary.
     * @return array Processed records indexed by normalized midnight timestamps.
     */
    public static function get_padded_total_history(?int $from, ?int $to): array {
        $records = data_provider::get_total_count_history($from, $to);
        $endtime = $to ?? time();
        $padded_records = [];
        $latest_state_val = 0;

        // 1. Establish start point (rounded to midnight) using database fallbacks if available.
        if ($from !== null) {
            $from_midnight = usergetmidnight($from);
            $fallback = data_provider::get_fallback_total_count($from);
            if ($fallback) {
                $latest_state_val = $fallback->total_sum;
            }
            $padded_records[$from_midnight] = (object)['timestamp' => $from_midnight, 'total_sum' => $latest_state_val];
        }

        // 2. Insert actual records, normalizing their timestamps to midnight.
        // Later entries on the same day will naturally overwrite earlier ones.
        foreach ($records as $rec) {
            $midnight = usergetmidnight($rec->timestamp);
            $rec->timestamp = $midnight;
            $padded_records[$midnight] = $rec;
            $latest_state_val = $rec->total_sum;
        }

        // 3. Establish end point (rounded to midnight) to complete the timeline range.
        $end_midnight = usergetmidnight($endtime);
        $padded_records[$end_midnight] = (object)['timestamp' => $end_midnight, 'total_sum' => $latest_state_val];

        // 4. Ensure chronological sorting by timestamp keys.
        ksort($padded_records);

        return $padded_records;
    }

    /**
     * Prepares module-specific history records, ensuring data continuity,
     * daily normalization, and unique tracking keys per module.
     *
     * @param string[]|null $modnames List of module names to process.
     * @param int|null $from Lower timestamp boundary.
     * @param int|null $to Upper timestamp boundary.
     * @return array Processed and re-indexed records ready for multi-line charting.
     */
    public static function get_padded_module_history(?array $modnames, ?int $from, ?int $to): array {
        $records = data_provider::get_activity_counts_history_by_module($modnames, $from, $to);
        $endtime = $to ?? time();

        $module_states = [];
        $padded_records = []; // Temporarily keyed by 'modulename_timestamp'

        $from_midnight = $from ? usergetmidnight($from) : null;
        $end_midnight = usergetmidnight($endtime);

        // 1. Load historical state fallbacks prior to the requested start window.
        $fallbacks = data_provider::get_fallback_module_counts($modnames, $from ?? 0);
        foreach ($fallbacks as $fallback) {
            $module_states[$fallback->activityname] = $fallback;
        }

        // 2. Set initial baseline points for each module at the start boundary.
        if ($from_midnight !== null) {
            foreach ($module_states as $modname => $state) {
                $start_point = clone $state;
                $start_point->timestamp = $from_midnight;
                $padded_records[$modname . '_' . $from_midnight] = $start_point;
            }
        }

        // 3. Process actual records, normalizing timestamps to midnight.
        foreach ($records as $rec) {
            $midnight = usergetmidnight($rec->timestamp);
            $rec->timestamp = $midnight;
            $padded_records[$rec->activityname . '_' . $midnight] = $rec;
            $module_states[$rec->activityname] = clone $rec;
        }

        // 4. Anchor end points for all active modules up to the end boundary.
        foreach ($module_states as $modname => $state) {
            $end_point = clone $state;
            $end_point->timestamp = $end_midnight;
            $padded_records[$modname . '_' . $end_midnight] = $end_point;
        }

        // 5. Sort all entries strictly by timestamp.
        uasort($padded_records, function($a, $b) {
            return $a->timestamp <=> $b->timestamp;
        });

        // 6. Assign sequential dummy IDs (required by most charting libraries) and clean array keys.
        $final_records = [];
        $i = 1;
        foreach ($padded_records as $rec) {
            $rec->id = $i++;
            $final_records[$rec->id] = $rec;
        }

        return $final_records;
    }
}