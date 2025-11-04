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

use core\chart_pie;
use core\chart_series;

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
$activity_snapshot = $DB->get_records_sql("
    SELECT l.id, l.activityname, c.count, c.timestamp
    FROM {tool_activitystatistics_lookup} l
    JOIN {tool_activitystatistics_counts} c ON l.id = c.activityid
    INNER JOIN (
        SELECT activityid, MAX(timestamp) AS last_ts
        FROM {tool_activitystatistics_counts}
        GROUP BY activityid
    ) lc ON c.activityid = lc.activityid AND c.timestamp = lc.last_ts
");

if (empty($activity_snapshot)) {
    echo html_writer::tag('p', 'No activity statistics found. Has the scheduled task run at least once?');
} else {
    // --- General overview --- //
    $number_of_activities = count($activity_snapshot);
    $total_count = array_sum(array_column($activity_snapshot, 'count'));
    $lasttimestamp = max(array_column($activity_snapshot, 'timestamp'));

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


    // --- Sort data once for both Table and Chart --- //
    usort($activity_snapshot, function($a, $b) {
        return $b->count <=> $a->count;
    });
    $top_activities = array_slice($activity_snapshot, 0, 5);

    echo html_writer::start_div('row mt-4');

    // --- Column 1: Top Activities Table --- //
    echo html_writer::start_div('col-md-6');
    echo html_writer::tag('h3', 'Top 5 Activities');

    $table = new html_table();
    $table->head = ['Rank', 'Activity', 'Count'];
    $table->data = [];
    $rank = 1;

    foreach ($top_activities as $record) {
        $detail_url = new moodle_url('/admin/tool/activitystatistics/detail.php', ['id' => $record->id]);
        $activity_link = html_writer::link($detail_url, format_string($record->activityname));

        $table->data[] = [
            $rank++,
            $activity_link, // Link statt nur Text
            format_string($record->count)
        ];
    }
    echo html_writer::table($table);
    echo html_writer::end_div(); // End of Column 1: Top Activities Table (col-md-6)


    // --- Column 2: Pie Chart --- //
    echo html_writer::start_div('col-md-6');
    echo html_writer::tag('h3', 'Activity Distribution');

    $pie_labels = [];
    $pie_data = [];

    foreach ($activity_snapshot as $record) {
        $pie_labels[] = format_string($record->activityname);
        $pie_data[] = (int)$record->count;
    }

    $pie = new chart_pie();
    $pie->set_labels($pie_labels);
    $series = new chart_series('Activity Count', $pie_data);
    $pie->add_series($series);

    echo $OUTPUT->render($pie);

    echo html_writer::end_div(); // End of Column 2: Pie Chart (col-md-6)
    echo html_writer::end_div(); // End of row mt-4
}

echo $OUTPUT->footer();