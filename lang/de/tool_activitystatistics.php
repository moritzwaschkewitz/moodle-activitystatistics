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

defined('MOODLE_INTERNAL') || die();

// --- General Plugin Strings ---
$string['pluginname'] = 'Aktivitätsstatistiken';
$string['privacy:metadata'] = 'Dieses Plugin speichert keine personenbezogenen Daten. Alle Daten werden protokolliert und anonym gespeichert.';

// --- Scheduled Tasks ---
$string['task:log_activities_count'] = 'Aktivitätsanzahl protokollieren';

// --- Dashboard Index Page ---
$string['index:title'] = 'Aktivitätsstatistiken';
$string['index:heading'] = 'Aktivitätsstatistiken';
$string['index:no_data_error'] = 'Keine Aktivitätsstatistiken gefunden. Wurde der geplante Task bereits mindestens einmal ausgeführt?';
$string['index:unknown_activity_error'] = 'Unbekannte Aktivität';

// Dashboard Overview Section
$string['index:overview:heading'] = 'Dashboard-Übersicht';
$string['index:overview:total_activities'] = 'Aktivitäten gesamt';
$string['index:overview:total_count'] = 'Gesamtanzahl';
$string['index:overview:last_update'] = 'Letzte Aktualisierung';

// Top 5 Activities Section
$string['index:top5:heading'] = 'Top 5 Aktivitäten';
$string['index:top5:rank'] = 'Rang';
$string['index:top5:activity'] = 'Aktivität';
$string['index:top5:count'] = 'Anzahl';

// Activity Distribution Chart Section
$string['index:activity_distribution:heading'] = 'Aktivitätsverteilung';
$string['index:activity_distribution:chart_title'] = 'Aktivitätsanzahl';

// Historical Data Section & Charts
$string['index:history:heading'] = 'Historische Daten';
$string['index:total_count:heading'] = 'Gesamtanzahl im Zeitverlauf';
$string['index:total_count:chart_title'] = 'Aktivitäts-Gesamtanzahl';
$string['index:multi_line_count:heading'] = 'Aktivitätsanzahl nach Modul';

// --- Module Filter Elements ---
$string['filter:select_all'] = 'Alle auswählen';
$string['filter:select_none'] = 'Keine auswählen';
$string['filter:apply'] = 'Filter anwenden';

// --- Time Filter Controls ---
$string['filter:period'] = 'Zeitraum';
$string['filter:all_time'] = 'Gesamte Zeit';
$string['filter:last_x_days'] = 'Letzte {$a} Tage';
$string['filter:custom_range'] = 'Benutzerdefinierter Zeitraum';
$string['filter:from_date'] = 'Von';
$string['filter:to_date'] = 'Bis';
$string['filter:error:from_after_to'] = 'Das "Von"-Datum muss vor dem "Bis"-Datum liegen.';