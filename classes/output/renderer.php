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

use plugin_renderer_base;

class renderer extends plugin_renderer_base {

    /**
     * Renders the main index_page of the tool.
     *
     * Note on Moodle's rendering magic:
     * When invoking `$renderer->render($page_output)`, if `$page_output` is an
     * instance of the `index_page` class, Moodle's magic rendering system will
     * automatically look for and execute the matching `render_index_page` method
     * defined within this renderer.
     *
     * @param index_page $page The index page renderable object containing the data.
     * @return string The HTML output generated from the mustache template
     */
    protected function render_index_page(index_page $page) {
        return $this->render_from_template('tool_activitystatistics/index_page', $page->export_for_template($this));
    }

    /**
     * Renders the filter form using the standard moodleform display.
     */
    public function render_renderable_filter_form(\tool_activitystatistics\output\renderable_filter_form $f): string {
        $data = $f->export_for_template($this);
        //return $this->render_from_template('tool_activitystatistics/module_filter_form', $data);
        return $data->formhtml ?? $data['module_filter_form'];
    }
}