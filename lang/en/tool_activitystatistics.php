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

defined('MOODLE_INTERNAL') || die();

// --- General Plugin Strings ---
$string['pluginname'] = 'Activity Statistics';
$string['privacy:metadata'] = 'This plugin does not store any personal data. All data is logged and stored anonymously.';

// --- Scheduled Tasks ---
$string['task:log_activities_count'] = 'Log activity counts';

// --- Dashboard Index Page ---
$string['index:title'] = 'Activity Statistics';
$string['index:heading'] = 'Activity Statistics';
$string['index:no_data_error'] = 'No activity statistics found. Has the scheduled task run at least once?';
$string['index:unknown_activity_error'] = 'Unknown activity';

// Dashboard Overview Section
$string['index:overview:heading'] = 'Dashboard Overview';
$string['index:overview:total_activities'] = 'Total Activities';
$string['index:overview:total_count'] = 'Total Count';
$string['index:overview:last_update'] = 'Last Update';

// Top 5 Activities Section
$string['index:top5:heading'] = 'Top 5 Activities';
$string['index:top5:rank'] = 'Rank';
$string['index:top5:activity'] = 'Activity';
$string['index:top5:count'] = 'Count';

// Activity Distribution Chart Section
$string['index:activity_distribution:heading'] = 'Activity Distribution';
$string['index:activity_distribution:chart_title'] = 'Activity Count';

// Historical Data Section & Charts
$string['index:history:heading'] = 'Historical Data';
$string['index:total_count:heading'] = 'Total Count Over Time';
$string['index:total_count:chart_title'] = 'Total Activity Count';
$string['index:multi_line_count:heading'] = 'Activity Count by Module';

// --- Module Filter Elements ---
$string['filter:select_all'] = 'Select all';
$string['filter:select_none'] = 'Select none';
$string['filter:apply'] = 'Apply filter';

// --- Time Filter Controls ---
$string['filter:period'] = 'Period';
$string['filter:all_time'] = 'All time';
$string['filter:last_x_days'] = 'Last {$a} days';
$string['filter:custom_range'] = 'Custom range';
$string['filter:from_date'] = 'From';
$string['filter:to_date'] = 'To';
$string['filter:error:from_after_to'] = 'The "From" date must be earlier than the "To" date.';