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

namespace tool_activitystatistics\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form class handling global time frame selections (e.g., all time, last X days, or custom ranges).
 * Controls visibility of custom date pickers and submit actions dynamically based on the selected period.
 */
class time_filter_form extends \moodleform {
    protected function definition() {
        $mform = $this->_form;

        // Append custom classes to the form element:
        // - 'ignoredirty': Suppresses browser/Moodle warnings about unsaved changes during auto-submits.
        // - Flexbox utility classes ('d-flex', 'flex-wrap', etc.): Aligns elements horizontally.
        $this->_form->updateAttributes(['class' => 'mform d-flex flex-wrap gap-3 align-items-center mform-inline ignoredirty']);

        $periods = [
            'all' => get_string('filter:all_time', 'tool_activitystatistics'),
            '30' => get_string('filter:last_x_days', 'tool_activitystatistics', 30),
            '90' => get_string('filter:last_x_days', 'tool_activitystatistics', 90),
            '180' => get_string('filter:last_x_days', 'tool_activitystatistics', 180),
            'custom' => get_string('filter:custom_range', 'tool_activitystatistics'),
        ];

        // Period dropdown: Triggers an immediate form submission via inline JavaScript
        // for all predefined options, bypassing the need to click a submit button.
        $mform->addElement('select', 'period', get_string('filter:period', 'tool_activitystatistics'), $periods, ['onchange' => 'if(this.value !== "custom") { this.form.submit(); }']);

        // Dynamically retain the currently selected period from the request parameters
        // to prevent the dropdown from resetting to 'all' after a page reload.
        $currentperiod = optional_param('period', 'all', PARAM_ALPHANUM);
        $mform->setDefault('period', $currentperiod);

        // Date selectors for custom ranges (rendered without optional checkboxes).
        $mform->addElement('date_selector', 'fromdate', get_string('filter:from_date', 'tool_activitystatistics'));
        $mform->addElement('date_selector', 'todate', get_string('filter:to_date', 'tool_activitystatistics'));

        // Conditional visibility: Automatically hide custom date fields unless 'custom' range is selected.
        $mform->hideIf('fromdate', 'period', 'neq', 'custom');
        $mform->hideIf('todate', 'period', 'neq', 'custom');

        // Pass-through state: Retain the current module filter configuration
        // so it isn't lost when the time filter form is submitted.
        $modules = $this->_customdata['modules'] ?? null;
        if (is_array($modules)) {
            $mform->addElement('hidden', 'filtersubmitted', 1);
            $mform->setType('filtersubmitted', PARAM_INT);
            foreach ($modules as $name => $value) {
                $mform->addElement('hidden', 'modules[' . $name . ']', $value);
                $mform->setType('modules[' . $name . ']', PARAM_BOOL);
            }
        }

        // Manual submit button for custom ranges (since predefined periods auto-submit via JS).
        $mform->addElement('submit', 'submitfilter', get_string('filter', 'moodle'), ['class' => 'btn btn-primary']);

        // Hide the submit button unless a custom date range is explicitly chosen.
        $mform->hideIf('submitfilter', 'period', 'neq', 'custom');
    }

    /**
     * Custom form validation ensuring logical consistency for custom date ranges.
     *
     * @param array $data Submitted form data
     * @param array $files Uploaded files
     * @return array Array of error messages keyed by element name
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['period'] === 'custom') {
            if (empty($data['fromdate'])) {
                $errors['fromdate'] = get_string('required');
            }
            if (empty($data['todate'])) {
                $errors['todate'] = get_string('required');
            }
            // Ensure the start date does not lie after the end date.
            if (!empty($data['fromdate']) && !empty($data['todate']) && $data['fromdate'] > $data['todate']) {
                $errors['fromdate'] = get_string('filter:error:from_after_to', 'tool_activitystatistics');
            }
        }

        return $errors;
    }
}