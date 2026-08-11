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

namespace tool_activitystatistics\output;

defined('MOODLE_INTERNAL') || die();

use html_table;
use html_writer;
use moodle_url;

/**
 * Class table_top5_activities
 * @deprecated
 */
class table_top5_activities {

    private array $top_activities;

    public function __construct(array $top_activities) {
        $this->top_activities = $top_activities;
    }

    public function get_table(): html_table {
        $table = new html_table();
        $table->head = [
            get_string('index:top5:rank', 'tool_activitystatistics'),
            get_string('index:top5:activity', 'tool_activitystatistics'),
            get_string('index:top5:count', 'tool_activitystatistics')
        ];

        $table->data = [];
        $rank = 1;

        foreach ($this->top_activities as $record) {
            $detail_url = new moodle_url('/admin/tool/activitystatistics/detail.php', ['id' => $record->id]);
            $activity_link = html_writer::link($detail_url, format_string($record->activityname));

            $table->data[] = [
                $rank++,
                $activity_link,
                format_string($record->count)
            ];
        }

        return $table;
    }
}
