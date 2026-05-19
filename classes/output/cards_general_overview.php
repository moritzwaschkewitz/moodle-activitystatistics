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

class cards_general_overview implements renderable, templatable {

    private $stats;

    public function __construct($overview_stats) {
        $this->stats = $overview_stats;
    }

    public function export_for_template(renderer_base $output) {
        return [
            'total_activities' => $this->stats->total_activities,
            'total_count' => number_format($this->stats->total_count),
            'last_update' => userdate($this->stats->last_update)
        ];
    }
}