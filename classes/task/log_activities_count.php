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

namespace tool_activitystatistics\task;

use core\task\scheduled_task;

class log_activities_count extends scheduled_task {

    public function get_name() {
        return get_string('logactivitiescount', 'tool_activitystatistics');
    }

    public function execute() {
        global $DB;

        $modules = $DB->get_records('modules');

        $timestamp = time();

        foreach ($modules as $mod) {
            $lookup = $DB->get_record('tool_activitystatistics_lookup', ['activityname' => $mod->name]);
            if (!$lookup) {
                $lookupid = $DB->insert_record('tool_activitystatistics_lookup', ['activityname' => $mod->name]);
            } else {
                $lookupid = $lookup->id;
            }

            $count = $DB->count_records('course_modules', [
                'module' => $mod->id,
                'deletioninprogress' => 0
            ]);
            mtrace("$mod->name: $count");

            $lastlog = $DB->get_record_sql(
                'SELECT count FROM {tool_activitystatistics_counts}
                 WHERE activityid = ? 
                 ORDER BY timestamp DESC 
                 LIMIT 1',
                [$lookupid]
            );

            if (!$lastlog || $lastlog->count != $count) {
                $DB->insert_record('tool_activitystatistics_counts', [
                    'activityid' => $lookupid,
                    'count' => $count,
                    'timestamp' => $timestamp
                ]);
                mtrace("Found new activity count for $mod->name: $lastlog->count-> $count");
            }
        }
    }
}
