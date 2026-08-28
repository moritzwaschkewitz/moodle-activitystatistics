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

namespace tool_activitystatistics;

/**
 * Helper class for cleanly constructing dynamic SQL WHERE clauses and safely managing parameters.
 */
class sql_criteria {
    /** @var string[] Array of individual SQL condition strings. */
    private array $conditions = [];
    /** @var array Key-value store of associated query parameters. */
    private array $params = [];

    /**
     * Appends a new SQL condition along with its corresponding parameters.
     *
     * @param string $condition SQL conditional fragment (e.g., "timestamp >= :from").
     * @param array $params Associative array of parameters matching the condition.
     */
    public function add(string $condition, array $params = []): void {
        $this->conditions[] = $condition;
        $this->params = array_merge($this->params, $params);
    }

    /**
     * Compiles and returns the final WHERE clause string.
     *
     * @param string $prefix Custom prefix (defaults to 'WHERE ').
     * @return string Compiled SQL clause or an empty string if no conditions exist.
     */
    public function get_where(string $prefix = 'WHERE '): string {
        if (empty($this->conditions)) {
            return '';
        }
        return $prefix . implode(' AND ', $this->conditions);
    }

    /**
     * Returns all accumulated SQL parameters.
     *
     * @return array Associative array of parameters ready for database execution.
     */
    public function get_params(): array {
        return $this->params;
    }
}