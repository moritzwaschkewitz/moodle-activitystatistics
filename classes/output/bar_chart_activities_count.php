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
use core\chart_bar;

class bar_chart_activities_count {

    private array $activity_counts;

    public function __construct(array $activity_counts) {
        $this->activity_counts = $activity_counts;
    }

    public function get_chart(): chart_bar {
        $bar = new chart_bar();

        $bar_labels = [];
        $bar_data = [];

        foreach ($this->activity_counts as $record) {
            $bar_labels[] = format_string($record->activityname);
            $bar_data[] = (int)$record->count;
        }

        $bar->set_labels($bar_labels);
        $series = new chart_series(
            get_string('index:activity_distribution:chart_title', 'tool_activitystatistics'),
            $bar_data);
        $bar->add_series($series);

        return $bar;
    }
}