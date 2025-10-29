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
 * Tests for backwards compatibility change in global clean_param() handling arrays.
 *
 * @package   core
 * @copyright 2025
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class clean_param_array_compatibility_test extends advanced_testcase {
    public function test_clean_param_allows_array_backcompat(): void {
        $this->resetAfterTest();

        $input = ['One ', ' Two', 'Three'];
        $cleaned = clean_param($input, PARAM_TEXT);

        $this->assertIsArray($cleaned);
        $this->assertCount(3, $cleaned);
        // Whitespace trimmed only by PARAM_TEXT rules (no implicit trimming); ensure elements are strings.
        foreach ($cleaned as $value) {
            $this->assertIsString($value);
        }
    }

    public function test_clean_param_array_does_not_recurse(): void {
        $this->resetAfterTest();
        $input = ['a', ['b']];
        // The inner array should trigger an exception when non-recursive.
        $this->expectException(coding_exception::class);
        clean_param($input, PARAM_TEXT);
    }

    public function test_clean_param_array_recursive_still_available(): void {
        $this->resetAfterTest();
        $input = ['a', ['b']];
        $cleaned = clean_param_array($input, PARAM_TEXT, true);
        $this->assertEquals(['a', ['b']], $cleaned);
    }
}
