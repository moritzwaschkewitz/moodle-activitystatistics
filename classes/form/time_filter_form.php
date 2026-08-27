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

class time_filter_form extends \moodleform {
    protected function definition() {
        $mform = $this->_form;

        $periods = [
            'all' => get_string('alltime', 'tool_activitystatistics'),
            '30' => get_string('lastxdays', 'tool_activitystatistics', 30),
            '90' => get_string('lastxdays', 'tool_activitystatistics', 90),
            '180' => get_string('lastxdays', 'tool_activitystatistics', 180),
            'custom' => get_string('customrange', 'tool_activitystatistics'),
        ];
        $mform->addElement('select', 'period', get_string('period', 'tool_activitystatistics'), $periods);
        $mform->setDefault('period', 'all');

        $mform->addElement('date_selector', 'fromdate', get_string('fromdate', 'tool_activitystatistics'), ['optional' => true]);
        $mform->addElement('date_selector', 'todate', get_string('todate', 'tool_activitystatistics'), ['optional' => true]);

        $mform->hideif('fromdate', 'period', 'neq', 'custom');
        $mform->hideif('todate', 'period', 'neq', 'custom');

        // Pass through module filter state to not lose it when this form is submitted.
        $modules = $this->_customdata['modules'] ?? null;
        if (is_array($modules)) {
            $mform->addElement('hidden', 'filtersubmitted', 1);
            $mform->setType('filtersubmitted', PARAM_INT);
            foreach ($modules as $name => $value) {
                $mform->addElement('hidden', 'modules[' . $name . ']', $value);
                $mform->setType('modules[' . $name . ']', PARAM_BOOL);
            }
        }

        $this->add_action_buttons(false, get_string('filter', 'moodle'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ($data['period'] === 'custom') {
            if (empty($data['fromdate'])) {
                $errors['fromdate'] = get_string('required');
            }
            if (empty($data['todate'])) {
                $errors['todate'] = get_string('required');
            }
            if (!empty($data['fromdate']) && !empty($data['todate']) && $data['fromdate'] > $data['todate']) {
                $errors['fromdate'] = get_string('fromdatebeforetodate', 'tool_activitystatistics');
            }
        }
        return $errors;
    }
}