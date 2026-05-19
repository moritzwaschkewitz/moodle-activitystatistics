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

use renderable;
use templatable;
use renderer_base;
use html_writer;

class index_page implements renderable, templatable {

    private cards_general_overview $overview_cards;
    private table_top5_activities $top_table;
    private pie_chart_activities_count $pie_chart;
    private line_chart_total_count $line_chart;

    public function __construct($overview_stats, $activity_counts, $history_data) {
        $this->overview_cards = new cards_general_overview($overview_stats);
        $this->top_table = new table_top5_activities(array_slice($activity_counts, 0, 5));
        $this->pie_chart = new pie_chart_activities_count($activity_counts);
        $this->line_chart = new line_chart_total_count($history_data);
    }

    public function export_for_template(renderer_base $output) {
        return [
            'overview_cards' => $this->overview_cards->export_for_template($output),
            'top_table' => html_writer::table($this->top_table->get_table()),
            'pie_chart' => $output->render($this->pie_chart->get_chart()),
            'line_chart' => $output->render($this->line_chart->get_chart()),
        ];
    }
}