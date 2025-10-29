<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * CLI helper to set draft questions to ready within a category (or all categories).
 *
 * Usage examples:
 *   php admin/cli/set_questions_ready.php --categoryid=5
 *   php admin/cli/set_questions_ready.php --all
 *   php admin/cli/set_questions_ready.php --dry-run --categoryid=12
 *
 * @package   core_question
 * @copyright 2025
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/clilib.php');

[$options, $unrecognized] = cli_get_params([
    'help' => false,
    'categoryid' => null,
    'all' => false,
    'dry-run' => false,
], [
    'h' => 'help',
]);

if ($unrecognized) {
    cli_error(get_string('cliunknowoption', 'admin', implode("\n  ", $unrecognized)));
}

if ($options['help']) {
    echo "Bulk set Draft questions to Ready.\n\n";
    echo "Options:\n";
    echo "  --categoryid=N    Limit to a single question category id\n";
    echo "  --all             Process all categories\n";
    echo "  --dry-run         Show what would change without persisting\n";
    echo "  -h, --help        This help\n\n";
    exit(0);
}

if (!$options['all'] && empty($options['categoryid'])) {
    cli_error("Specify --categoryid or --all");
}

$dryrun = !empty($options['dry-run']);

// Fetch categories.
$categoryids = [];
if ($options['all']) {
    $categoryids = $DB->get_fieldset_select('question_categories', 'id', '1=1');
} else {
    $categoryids = [(int)$options['categoryid']];
}

$moved = 0; $skipped = 0;
foreach ($categoryids as $catid) {
    mtrace("Processing category ID {$catid}");
    $questions = $DB->get_records('question', ['category' => $catid]);
    foreach ($questions as $q) {
        if ($q->status !== 'draft') {
            $skipped++; continue;
        }
        mtrace(" - Draft question #{$q->id} '{$q->name}' => Ready" . ($dryrun ? ' (dry-run)' : ''));
        if (!$dryrun) {
            $q->status = 'ready';
            $DB->update_record('question', $q);
        }
        $moved++;
    }
}

mtrace("Summary: changed {$moved} draft question(s); skipped {$skipped} already ready/other.");
if ($dryrun) {
    mtrace("Dry-run only. No changes saved.");
}
