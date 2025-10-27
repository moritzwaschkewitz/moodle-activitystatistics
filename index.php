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

$sql = "
    SELECT l.activityname,
           c.count,
           c.timestamp
      FROM {tool_activitystatistics_lookup} l
      JOIN {tool_activitystatistics_counts} c
        ON l.id = c.activityid
     WHERE c.timestamp = (
         SELECT MAX(c2.timestamp)
           FROM {tool_activitystatistics_counts} c2
          WHERE c2.activityid = l.id
     )
  ORDER BY l.activityname ASC
";

$records = $DB->get_records_sql($sql);

$table = new html_table();
$table->head = ['Activity', 'Count', 'Last registered change'];
$table->data = [];

foreach ($records as $record) {
    $table->data[] = [
        format_string($record->activityname),
        format_string($record->count),
        userdate($record->timestamp)
    ];
}

echo html_writer::tag('h2', 'Activity Statistics');
echo html_writer::table($table);

echo $OUTPUT->footer();