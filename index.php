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

use core\chart_line;
use core\chart_pie;
use core\chart_series;

require_once(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('tool/activitystatistics:view', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/admin/tool/activitystatistics/index.php'));
$PAGE->set_title(get_string('index:title', 'tool_activitystatistics'));
$PAGE->set_heading(get_string('index:heading', 'tool_activitystatistics'));

echo $OUTPUT->header();

global $DB;

$modules = $DB->get_records('modules');
$all_counts_history = $DB->get_records('tool_activitystatistics_counts', [], 'timestamp ASC');

$overview_stats = data_provider::get_overview_stats();

if ($overview_stats->last_update === false) {
    echo $OUTPUT->notification(get_string('index:no_data_error', 'tool_activitystatistics'), 'warning');
    echo $OUTPUT->footer();
    exit;
}

// --- General overview --- //
echo html_writer::tag('h2', get_string('index:overview:heading', 'tool_activitystatistics'));

echo html_writer::start_div('row mb-4');
$cards = [
    ['title' => get_string('index:overview:total_activities', 'tool_activitystatistics'),
        'value' => $overview_stats->total_activities],
    ['title' => get_string('index:overview:total_count', 'tool_activitystatistics'),
        'value' => number_format($overview_stats->total_count)],
    ['title' => get_string('index:overview:last_update', 'tool_activitystatistics'),
        'value' => userdate($overview_stats->last_update)]
];

foreach ($cards as $card) {
    echo html_writer::start_div('col-md-4');
    echo html_writer::start_div('card text-center p-3 shadow-sm');
    echo html_writer::tag('h4', $card['title']);
    echo html_writer::tag('p', $card['value'], ['class' => 'h5']);
    echo html_writer::end_div();
    echo html_writer::end_div();
}
echo html_writer::end_div();

// --- Row 1, Column 1: Top Activities Table --- //
$current_activity_counts = data_provider::get_current_activity_counts();
$top_activities = array_slice($current_activity_counts, 0, 5);

echo html_writer::start_div('row mt-4'); // Row 1: Top5+Pie
echo html_writer::start_div('col-md-6'); // Row 1, Col 1: Top5
echo html_writer::tag('h3', get_string('index:top5:heading', 'tool_activitystatistics'));

$table = new html_table();
$table->head = [
    get_string('index:top5:rank', 'tool_activitystatistics'),
    get_string('index:top5:activity', 'tool_activitystatistics'),
    get_string('index:top5:count', 'tool_activitystatistics')];
$table->data = [];
$rank = 1;

foreach ($top_activities as $record) {
    $detail_url = new moodle_url('/admin/tool/activitystatistics/detail.php', ['id' => $record->id]);
    $activity_link = html_writer::link($detail_url, format_string($record->activityname));

    $table->data[] = [
        $rank++,
        $activity_link,
        format_string($record->count)
    ];
}
echo html_writer::table($table);
echo html_writer::end_div(); // End of Column 1: Top Activities Table (col-md-6)


// --- Row 1, Column 2: Pie Chart --- //
echo html_writer::start_div('col-md-6'); // Row 1, Col 2: Pie Chart
echo html_writer::tag('h3', get_string('index:activity_distribution:heading', 'tool_activitystatistics'));

$pie_labels = [];
$pie_data = [];

foreach ($current_activity_counts as $record) {
    $pie_labels[] = format_string($record->activityname);
    $pie_data[] = (int)$record->count;
}

$pie = new chart_pie();
$pie->set_labels($pie_labels);
$series = new chart_series(
    get_string('index:activity_distribution:chart_title', 'tool_activitystatistics'),
    $pie_data);
$pie->add_series($series);

echo $OUTPUT->render($pie);

echo html_writer::end_div(); // End of Column 2: Pie Chart (col-md-6)
echo html_writer::end_div(); // End of Row 1: Top5+Pie (row mt-4)


// --- Row 2: Line Chart(Total Count over Time) --- //
$history_data = data_provider::get_total_count_history();

echo html_writer::start_div('row mt-5');
echo html_writer::start_div('col-12');
echo html_writer::tag('h3', get_string('index:total_count:heading', 'tool_activitystatistics'));

$line_chart = new chart_line();
$line_chart->set_smooth(true);

$line_labels = [];
$line_data = [];

foreach ($history_data as $timestamp => $record) {
    $line_labels[] = userdate($timestamp, '%d.%m. %H:%M');
    $line_data[] = $record->total_sum;
}

$line_series = new chart_series(
    get_string('index:total_count:chart_title', 'tool_activitystatistics'),
    $line_data
);
$line_chart->add_series($line_series);
$line_chart->set_labels($line_labels);

echo $OUTPUT->render($line_chart);

// End of Row 2: Total Count over Time
echo html_writer::end_div(); // col-12
echo html_writer::end_div(); // row mt-5


echo $OUTPUT->footer();