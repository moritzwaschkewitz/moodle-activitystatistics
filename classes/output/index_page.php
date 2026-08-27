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
    private line_chart_total_count $line_chart;
    private bar_chart_activities_count $bar_chart;
    private renderable_filter_form $module_filter_form;
    private multi_line_chart_activity_counts $multi_line_chart;
    private \moodleform $time_filter_form;

    /**
     * @param array $overview_stats
     * @param array $activity_counts
     * @param array $history_data
     * @param string|\moodle_url $filteractionurl  URL, wohin das Filterformular submitten soll
     * @param array|null $selectedmodules          z.B. ['forum' => 1, 'quiz' => 0, ...] oder null (=> Default)
     */
    public function __construct(
        $overview_stats,
        $activity_counts,
        $history_data,
        $filteractionurl,
        ?array $selectedmodules = null,
        array $activityhistory = [],
        \moodleform $time_filter_form
    ) {
        $this->overview_cards = new cards_general_overview($overview_stats);
        $this->bar_chart = new bar_chart_activities_count($activity_counts);
        $this->line_chart = new line_chart_total_count($history_data);
        $this->multi_line_chart = new multi_line_chart_activity_counts($activityhistory);

        $this->module_filter_form = new renderable_filter_form($filteractionurl, $selectedmodules);
        $this->time_filter_form = $time_filter_form;
    }

    public function export_for_template(renderer_base $output) {
        return [
            'overview_cards' => $this->overview_cards->export_for_template($output),
            'bar_chart' => $output->render($this->bar_chart->get_chart()),
            'line_chart' => $output->render($this->line_chart->get_chart()),
            'module_filter_form' => $output->render($this->module_filter_form),
            'multi_line_chart' => $output->render($this->multi_line_chart->get_chart()),
            'time_filter_form' => $this->time_filter_form->render(),
        ];
    }
}