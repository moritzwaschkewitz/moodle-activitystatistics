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
use tool_activitystatistics\output\index_page;
use tool_activitystatistics\form\time_filter_form;

require_once(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('tool/activitystatistics:view', $context);

$PAGE->set_context($context);
$actionurl = new moodle_url('/admin/tool/activitystatistics/index.php');
$PAGE->set_url($actionurl);
$PAGE->set_title(get_string('index:title', 'tool_activitystatistics'));
$PAGE->set_heading(get_string('index:heading', 'tool_activitystatistics'));




$overview_stats = data_provider::get_overview_stats();
if ($overview_stats->last_update === false) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('index:no_data_error', 'tool_activitystatistics'), 'warning');
    echo $OUTPUT->footer();
    exit;
}

// --- Filter processing ---
// Module filter state.
$filtersubmitted = optional_param('filtersubmitted', 0, PARAM_INT);
if ($filtersubmitted) {
    $selectedmodules = optional_param_array('modules', [], PARAM_BOOL);
} else {
    // Not submitted via module form. Could be first load or time filter submission.
    // If time filter was submitted, it will have passed 'modules' params.
    $selectedmodules = null;
    $modulesparam = optional_param_array('modules', null, PARAM_BOOL);
    if ($modulesparam !== null) {
        $selectedmodules = $modulesparam;
    }
}

// Time filter state.
$timefilterform = new time_filter_form($actionurl, ['modules' => $selectedmodules]);

// 1. Set default state first: all time.
$fromtimestamp = null;
$totimestamp = null;

// 2. Check for form submission and override defaults if valid data is received.
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
            // Defaults are already set, do nothing.
            break;
    }
}

$enabledmodnames = null;
if (is_array($selectedmodules)) {
    $enabledmodnames = array_keys(array_filter($selectedmodules));
}

$page_output = new index_page(
    $overview_stats,
    data_provider::get_current_activity_counts(),
    data_provider::get_total_count_history($fromtimestamp, $totimestamp),
    $actionurl,
    $timefilterform,
    $selectedmodules,
    data_provider::get_activity_counts_history_by_module($enabledmodnames, $fromtimestamp, $totimestamp)
);

echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('tool_activitystatistics');
echo $renderer->render($page_output);
echo $OUTPUT->footer();