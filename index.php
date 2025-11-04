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

require_once(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('tool/activitystatistics:view', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/admin/tool/activitystatistics/index.php'));
$PAGE->set_title('Hello World - Title');
$PAGE->set_heading('Hello World - Heading');

echo $OUTPUT->header();

global $DB;


echo html_writer::tag('h2', 'Dashboard Overview');

// --- Get ALL current activity data ONCE --- //
/*
 * since DB only logs changes the last timestamp for each activity is different
 * -> Inner select finds last timestamp for each activity
 */
$latest_activity_data = $DB->get_records_sql("
    SELECT l.activityname, c.count
    FROM {tool_activitystatistics_lookup} l
    JOIN {tool_activitystatistics_counts} c ON l.id = c.activityid
    INNER JOIN (
        SELECT activityid, MAX(timestamp) AS last_ts
        FROM {tool_activitystatistics_counts}
        GROUP BY activityid
    ) lc ON c.activityid = lc.activityid AND c.timestamp = lc.last_ts
");


// --- General overview --- //
$number_of_activities = count($latest_activity_data);

$total_count = array_sum(array_column($latest_activity_data, 'count'));

$lasttimestamp = $DB->get_field_sql("
    SELECT MAX(timestamp) 
    FROM {tool_activitystatistics_counts}
");
echo html_writer::start_div('row mb-4');

$cards = [
    ['title' => 'Total Activities', 'value' => $number_of_activities],
    ['title' => 'Total Count', 'value' => number_format($total_count)],
    ['title' => 'Last Update', 'value' => userdate($lasttimestamp)]
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


// --- Top Activities Table --- //
$top_activities = $latest_activity_data;
usort($top_activities, function($a, $b) {
    return $b->count <=> $a->count;
});
$top_activities = array_slice($top_activities, 0, 5);

echo html_writer::tag('h3', 'Top 5 Activities');

$table = new html_table();
$table->head = ['Rank', 'Activity', 'Count'];
$table->data = [];
$rank = 1;

foreach ($top_activities as $record) {
    $table->data[] = [
        $rank++,
        format_string($record->activityname),
        format_string($record->count)
    ];
}

echo html_writer::table($table);

echo $OUTPUT->footer();