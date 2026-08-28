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

use tool_activitystatistics\data_provider;
use tool_activitystatistics\chart_service;
use tool_activitystatistics\output\index_page;
use tool_activitystatistics\form\time_filter_form;

require_once(__DIR__ . '/../../../config.php');

// Security checks: Ensure the user is logged in and possesses the required capability
// to view the activity statistics dashboard at the system context level.
require_login();
$context = context_system::instance();
require_capability('tool/activitystatistics:view', $context);

// Page setup: Define URL, context, and title/heading strings.
$PAGE->set_context($context);
$actionurl = new moodle_url('/admin/tool/activitystatistics/index.php');
$PAGE->set_url($actionurl);
$PAGE->set_title(get_string('index:title', 'tool_activitystatistics'));
$PAGE->set_heading(get_string('index:heading', 'tool_activitystatistics'));

// Fetch high-level overview statistics.
$overview_stats = data_provider::get_overview_stats();

// Early exit: If the statistics table has not been populated yet, display a warning notification
// and halt execution to prevent rendering broken charts.
if ($overview_stats->last_update === false) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('index:no_data_error', 'tool_activitystatistics'), 'warning');
    echo $OUTPUT->footer();
    exit;
}

// --- Filter processing ---
// Determine which module filters are active based on form submissions or pass-through parameters.
$filtersubmitted = optional_param('filtersubmitted', 0, PARAM_INT);
if ($filtersubmitted) {
    $selectedmodules = optional_param_array('modules', [], PARAM_BOOL);
} else {
    // If not submitted directly via the module form, check if module parameters
    // were passed along (e.g., preserved during a time filter form submission).
    $selectedmodules = null;
    $modulesparam = optional_param_array('modules', null, PARAM_BOOL);
    if ($modulesparam !== null) {
        $selectedmodules = $modulesparam;
    }
}

// Initialize the time filter form, passing down the active module states as custom data.
$timefilterform = new time_filter_form($actionurl, ['modules' => $selectedmodules]);

// 1. Set default timeframe state: all time (unbounded).
$fromtimestamp = null;
$totimestamp = null;

// 2. Evaluate time filter form submission and map selected periods to concrete Unix timestamps.
if ($timefilterdata = $timefilterform->get_data()) {
    $now = time();
    switch ($timefilterdata->period) {
        case '30':
            $fromtimestamp = strtotime('-30 days', $now);
            $totimestamp = $now;
            break;
        case '90':
            $fromtimestamp = strtotime('-90 days', $now);
            $totimestamp = $now;
            break;
        case '180':
            $fromtimestamp = strtotime('-180 days', $now);
            $totimestamp = $now;
            break;
        case 'custom':
            $fromtimestamp = $timefilterdata->fromdate;
            $totimestamp = $timefilterdata->todate;
            break;
        case 'all':
            // Defaults remain active (null), covering the entire timeline.
            break;
    }
}

// Extract only the internal names of modules that have been explicitly checked/enabled.
$enabledmodnames = null;
if (is_array($selectedmodules)) {
    $enabledmodnames = array_keys(array_filter($selectedmodules));
}

// Fetch padded and normalized historical chart datasets via the chart service,
// ensuring data continuity and clean timelines regardless of the chosen filter range.
$total_history = chart_service::get_padded_total_history($fromtimestamp, $totimestamp);
$module_history = chart_service::get_padded_module_history($enabledmodnames, $fromtimestamp, $totimestamp);

// Instantiate the renderable page object, injecting all processed data, forms, and contexts.
$page_output = new index_page(
    $overview_stats,
    data_provider::get_current_activity_counts(),
    $total_history,
    $actionurl,
    $timefilterform,
    $selectedmodules,
    $module_history
);

// Output rendering sequence.
echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('tool_activitystatistics');
echo $renderer->render($page_output);
echo $OUTPUT->footer();