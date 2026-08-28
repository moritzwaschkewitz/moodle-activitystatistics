<?php
namespace tool_activitystatistics\output;

defined('MOODLE_INTERNAL') || die();

use core\chart_line;
use core\chart_series;

class multi_line_chart_activity_counts {
    /** @var \stdClass[] */
    private array $historyrows;

    /**
     * @param \stdClass[] $historyrows Rows containing timestamp, activityname, count.
     */
    public function __construct(array $historyrows) {
        $this->historyrows = $historyrows;
    }

    public function get_chart(): chart_line {
        $line = new chart_line();
        $line->set_smooth(true);

        // 1) Collect all timestamps and values per activity.
        $timestamps = [];
        $valuesbyactivity = []; // [activityname][timestamp] = count

        foreach ($this->historyrows as $row) {
            $ts = (int)$row->timestamp;
            $activityname = (string)$row->activityname;

            $timestamps[$ts] = true;
            $valuesbyactivity[$activityname][$ts] = (int)$row->count;
        }

        $timestamps = array_keys($timestamps);
        sort($timestamps);

        // 2) Labels.
        $labels = array_map(function(int $ts): string {
            return userdate($ts, '%d.%m.%y');
        }, $timestamps);

        $line->set_labels($labels);

        // 3) One series per activity, aligned to the same timestamp axis.
        foreach ($valuesbyactivity as $activityname => $valuesbyts) {
            $seriesdata = [];
            foreach ($timestamps as $ts) {
                // Use null for missing points -> gaps in chart (works well for sparse data).
                $seriesdata[] = $valuesbyts[$ts] ?? null;
            }

            // Nice label: pluginname if available, otherwise fallback to module name.
            $label = get_string_manager()->string_exists('pluginname', "mod_$activityname")
                ? get_string('pluginname', "mod_$activityname")
                : $activityname;

            $line->add_series(new chart_series($label, $seriesdata));
        }

        return $line;
    }
}