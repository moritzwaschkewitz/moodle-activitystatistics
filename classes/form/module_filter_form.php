<?php
// tool_activitystatistics/classes/form/module_filter_form.php

namespace tool_activitystatistics\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class module_filter_form extends \moodleform {

    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement('hidden', 'filtersubmitted', 1);
        $mform->setType('filtersubmitted', PARAM_INT);

        $selected = $this->_customdata['selected'] ?? null; // null => Default alle an

        $mform->addElement('header', 'filterhdr', get_string('index:module_filter', 'tool_activitystatistics'));

        // Alle installierten Aktivitätsmodule holen.
        // keys: forum, quiz, assign, ...
        $modplugins = \core_component::get_plugin_list('mod');

        $elements = [];
        $defaults = [];

        foreach ($modplugins as $modname => $unusedpath) {
            $label = get_string_manager()->string_exists('pluginname', "mod_$modname")
                ? get_string('pluginname', "mod_$modname")
                : $modname;

            // WICHTIG: Name ist modules[xyz], damit im Request ein modules-Array entsteht.
            $elname = "modules[$modname]";

            $elements[] = $mform->createElement(
                'advcheckbox',
                $elname,
                '',
                $label,
                ['group' => 1],
                [0, 1]
            );

            // Defaults für die Gruppe sammeln (Key ist nur $modname).
            if ($selected === null) {
                $defaults[$elname] = 1; // Standard: alle an
            } else {
                $defaults[$elname] = !empty($selected[$modname]) ? 1 : 0;
            }
        }

        $mform->addGroup($elements, 'modules', '', '', false);

        $mform->setDefaults($defaults);

        $buttonselements = [];
        $buttonselements[] = $mform->createElement('button', 'selectall',
            get_string('selectallmodules', 'tool_activitystatistics'),
            ['type' => 'button', 'data-action' => 'selectall']
        );
        $buttonselements[] = $mform->createElement('button', 'selectnone',
            get_string('selectnonemodules', 'tool_activitystatistics'),
            ['type' => 'button', 'data-action' => 'selectnone']
        );
        $buttonselements[] = $mform->createElement('submit', 'submitbutton',
            get_string('applyfilter', 'tool_activitystatistics'),
            ['class' => 'btn-primary']
        );

        // Eigene Group -> eigene Zeile im Formlayout.
        $mform->addGroup($buttonselements, 'selectbuttons', '', ' ', false);

        //$this->add_action_buttons(false, get_string('applyfilter', 'tool_activitystatistics'));
    }
}