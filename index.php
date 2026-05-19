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

require_once(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('tool/activitystatistics:view', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/admin/tool/activitystatistics/index.php'));
$PAGE->set_title(get_string('index:title', 'tool_activitystatistics'));
$PAGE->set_heading(get_string('index:heading', 'tool_activitystatistics'));


$overview_stats = data_provider::get_overview_stats();
if ($overview_stats->last_update === false) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('index:no_data_error', 'tool_activitystatistics'), 'warning');
    echo $OUTPUT->footer();
    exit;
}

$page_output = new index_page(
    $overview_stats,
    data_provider::get_current_activity_counts(),
    data_provider::get_total_count_history()
);

echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('tool_activitystatistics');
echo $renderer->render($page_output);
echo $OUTPUT->footer();