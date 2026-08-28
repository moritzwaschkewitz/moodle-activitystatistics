# Activity Statistics (tool_activitystatistics)

[![Moodle Plugin Directory](https://img.shields.io/badge/moodle-plugin-orange.svg)](https://moodle.org)
[![License: GNU GPL v3](https://img.shields.io/badge/license-GNU%20GPL%20v3-blue.svg)](http://www.gnu.org/licenses/gpl-3.0.html)

A custom Moodle admin tool designed to track, aggregate, and visualize system-wide activity metrics and historical progression over time through an intuitive dashboard.

---

## Features

* **Overview KPI Cards:** Quick metrics showing total activities, total module instances, and the timestamp of the last data update.
* **Activity Distribution Chart:** A comprehensive bar chart displaying the frequency of each activity type across the platform.
* **Historical Data & Trends:**
    * Track total activity growth over time with clean, day-normalized line charts.
    * Filter historical progression by custom date ranges or predefined periods (e.g., last 30, 90, or 180 days).
    * Filter multi-line charts dynamically by specific activity modules (e.g., Forum, Assignment, Quiz).
* **Automated Logging:** Includes a scheduled task to regularly snapshot and log activity metrics anonymously.

---

## Requirements

* Moodle 4.1 or higher (recommended: latest stable release)
* PHP 8.0 or higher

---

## Installation

1. Log in to your Moodle site as an administrator and go to **Site administration > Plugins > Install plugins**.
2. Upload the ZIP package of the plugin or place it manually on your server into your Moodle installation path under: `admin/tool/activitystatistics`
3. Log in as admin and complete the Moodle database upgrade prompt.
4. Navigate to **Site administration > Reports > Activity Statistics** (or the respective admin menu location) to view the dashboard.

---

## Scheduled Tasks

The plugin relies on a scheduled task (`\tool_activitystatistics\task\log_activities_count`) to capture historical snapshots.

* By default, this task is scheduled to run **daily at 11:30 PM** during off-peak hours.
* **Data Safeguard:** To prevent database bloat, the plugin enforces a strict "one snapshot per day" rule. Even if the task is triggered manually or configured to run hourly, it will safely skip execution if a snapshot for the current day already exists.
* You can adjust the execution time under **Site administration > Server > Scheduled tasks** to best fit your server's maintenance window.

---

## License

This plugin is open-source software licensed under the [GNU GPL v3 license](http://www.gnu.org/licenses/gpl-3.0.html).