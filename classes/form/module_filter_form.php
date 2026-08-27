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
 * Form class for filtering activity statistics by specific module types (e.g., forum, assign, quiz).
 * Dynamically discovers all installed mod plugins to build a multi-checkbox group.
 */
class module_filter_form extends \moodleform {

    public function definition(): void {
        global $PAGE;
        $mform = $this->_form;

        // Generate a unique DOM container ID to scope JavaScript operations safely
        // and prevent conflicts if multiple forms were ever present on the same page.
        $containerid = 'tool_activitystatistics_filter_' . uniqid();
        $this->_form->_attributes['id'] = $containerid;

        // Bind the AMD module responsible for "Select All" / "Deselect All" button interactions.
        $PAGE->requires->js_call_amd('tool_activitystatistics/module_selectall', 'init', [$containerid]);

        // Hidden flag used by the controller to detect whether this specific form has been submitted.
        $mform->addElement('hidden', 'filtersubmitted', 1);
        $mform->setType('filtersubmitted', PARAM_INT);

        // Retrieve previously selected modules from custom data passed during form instantiation.
        // A value of null indicates the default state where all modules are enabled.
        $selected = $this->_customdata['selected'] ?? null;

        // Automatically fetch all installed Moodle activity plugins (mod_*) from the file system.
        $modplugins = \core_component::get_plugin_list('mod');

        $elements = [];
        $defaults = [];

        foreach ($modplugins as $modname => $unusedpath) {
            // Fallback to the plugin folder name if a localized 'pluginname' string is missing.
            $label = get_string_manager()->string_exists('pluginname', "mod_$modname")
                ? get_string('pluginname', "mod_$modname")
                : $modname;

            // CRITICAL: The naming convention 'modules[modname]' forces PHP to parse
            // these checkboxes into a clean associative array upon form submission.
            $elname = "modules[$modname]";

            $elements[] = $mform->createElement(
                'advcheckbox',
                $elname,
                '',
                $label,
                ['group' => 1],
                [0, 1]
            );

            // Determine default states: either everything checked (if no prior selection exists)
            // or respect the preserved state from previous requests.
            if ($selected === null) {
                $defaults[$elname] = 1;
            } else {
                $defaults[$elname] = !empty($selected[$modname]) ? 1 : 0;
            }
        }

        // Group all dynamically generated checkboxes together under the name 'modules'.
        $mform->addGroup($elements, 'modules', '', '', false);
        $mform->setDefaults($defaults);

        // Build custom action buttons for batch-selection and submission.
        // Regular HTML buttons are used for 'selectall'/'selectnone' to prevent accidental form submission via JS.
        $buttonselements = [];
        $buttonselements[] = $mform->createElement('button', 'selectall',
            get_string('selectallmodules', 'tool_activitystatistics'),
            ['type' => 'button', 'data-action' => 'selectall', 'class' => 'btn btn-sm btn-outline-secondary']
        );
        $buttonselements[] = $mform->createElement('button', 'selectnone',
            get_string('selectnonemodules', 'tool_activitystatistics'),
            ['type' => 'button', 'data-action' => 'selectnone', 'class' => 'btn btn-sm btn-outline-secondary']
        );
        $buttonselements[] = $mform->createElement('submit', 'submitbutton',
            get_string('applyfilter', 'tool_activitystatistics'),
            ['class' => 'btn btn-sm btn-primary']
        );

        // Group the buttons into a single row, omitting default Moodle form spacing.
        $mform->addGroup($buttonselements, 'selectbuttons', '', ' ', false);
    }
}