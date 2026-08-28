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
use tool_activitystatistics\form\module_filter_form;

/**
 * Main renderable class for the Activity Statistics dashboard.
 * Implements Moodle's renderable and templatable interfaces to bridge raw data
 * and chart objects with Mustache templates.
 */
class index_page implements renderable, templatable {

    /** @var cards_general_overview KPI overview cards component. */
    private cards_general_overview $overview_cards;

    /** @var line_chart_total_count Total count trend line chart component. */
    private line_chart_total_count $line_chart;

    /** @var bar_chart_activities_count Activity distribution bar chart component. */
    private bar_chart_activities_count $bar_chart;

    /** @var module_filter_form Form instance for filtering specific modules. */
    private module_filter_form $module_filter_form;

    /** @var multi_line_chart_activity_counts Multi-line chart component for module breakdowns. */
    private multi_line_chart_activity_counts $multi_line_chart;

    /** @var \moodleform Global time filter form instance. */
    private \moodleform $time_filter_form;

    /**
     * Constructor: Initializes all sub-components, charts, and forms required for the dashboard.
     *
     * @param mixed $overview_stats High-level statistics data.
     * @param array $activity_counts Current snapshot of activity instances.
     * @param array $history_data Processed historical total counts.
     * @param string|\moodle_url $filteractionurl Target URL for the module filter form submissions.
     * @param \moodleform $time_filter_form Initialized time filter form object.
     * @param array|null $selectedmodules Associative array of active modules or null for defaults.
     * @param array $activityhistory Processed historical module counts.
     */
    public function __construct(
        $overview_stats,
        $activity_counts,
        $history_data,
        $filteractionurl,
        \moodleform $time_filter_form,
        ?array $selectedmodules = null,
        array $activityhistory = []
    ) {
        $this->overview_cards = new cards_general_overview($overview_stats);
        $this->bar_chart = new bar_chart_activities_count($activity_counts);
        $this->line_chart = new line_chart_total_count($history_data);
        $this->multi_line_chart = new multi_line_chart_activity_counts($activityhistory);

        // Instantiate the module filter form, passing down the target action URL and previously selected states.
        $this->module_filter_form = new module_filter_form($filteractionurl, $selectedmodules);
        $this->time_filter_form = $time_filter_form;
    }

    /**
     * Exports all component data and pre-rendered chart HTML into a structured array
     * ready to be consumed by the Mustache template engine.
     *
     * @param renderer_base $output Moodle renderer instance.
     * @return array Associative array of template variables.
     */
    public function export_for_template(renderer_base $output) {
        return [
            'overview_cards' => $this->overview_cards->export_for_template($output),
            'bar_chart' => $output->render($this->bar_chart->get_chart()),
            'line_chart' => $output->render($this->line_chart->get_chart()),
            'module_filter_form' => $this->module_filter_form->render(),
            'multi_line_chart' => $output->render($this->multi_line_chart->get_chart()),
            'time_filter_form' => $this->time_filter_form->render(),
        ];
    }
}