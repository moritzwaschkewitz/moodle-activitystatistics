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

use core\chart_series;
use core\chart_line;

class line_chart_total_count {
    private array $history_data;

    public function __construct(array $history_data) {
        $this->history_data = $history_data;
    }

    public function get_chart() {
        $line = new chart_line();
        $line->set_smooth(true);

        $line_labels = [];
        $line_data = [];

        foreach ($this->history_data as $timestamp => $record) {
            $line_labels[] = userdate($timestamp, '%d.%m. %H:%M');
            $line_data[] = $record->total_sum;
        }

        $line_series = new chart_series(
            get_string('index:total_count:chart_title', 'tool_activitystatistics'),
            $line_data
        );

        $line->add_series($line_series);
        $line->set_labels($line_labels);

        return $line;
    }
}