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

/*
 * Capability definitions for the Activity Statistics plugin.
 *
 * Notes on capability design:
 * - riskbitmask: Intentionally omitted.
 *   We do not use RISK_PERSONAL because this plugin only exposes globally aggregated,
 *   anonymized module counts (e.g., "Total Forums: 50"). It does not reveal any
 *   individual user activity, tracking data, or privacy-sensitive information.
 * - captype: Set to 'read' because this capability only grants view access to the
 *   dashboard, without allowing any data modification.
 * - contextlevel: Set to CONTEXT_SYSTEM since these are site-wide statistics,
 *   not tied to a specific course or category.
 */
$capabilities = [
    'tool/activitystatistics:view' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW
        ],
    ],
];

