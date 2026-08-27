<?php
// tool_activitystatistics/classes/output/renderable_filter_form.php

namespace tool_activitystatistics\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;

class renderable_filter_form implements renderable, templatable {

    /** @var \tool_activitystatistics\form\module_filter_form */
    private $form;

    /** @var string eindeutige Container-ID für JS */
    private $containerid;

    /**
     * @param string|\moodle_url $actionurl
     * @param array|null $selectedmodules
     */
    public function __construct($actionurl, ?array $selectedmodules = null) {
        global $PAGE;

        $this->containerid = 'tool_activitystatistics_filter_' . uniqid();

        $customdata = [
            'selected' => $selectedmodules, // null => default alle an
        ];

        $this->form = new \tool_activitystatistics\form\module_filter_form($actionurl, $customdata);

        // AMD JS anbinden (Container-ID übergeben).
        $PAGE->requires->js_call_amd('tool_activitystatistics/module_selectall', 'init', [
            $this->containerid,
        ]);
    }

    public function export_for_template(renderer_base $output): array {
        return [
            'containerid' => $this->containerid,
            'module_filter_form' => $this->form->render(),
        ];
    }
}