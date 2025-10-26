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

/**
 * Plugin functions and callbacks.
 *
 * @package     local_aiquestiongen
 * @copyright   2025 TCC Project
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extend course navigation to add AI Question Generator link.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course object
 * @param context $context The course context
 */
function local_aiquestiongen_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('local/aiquestiongen:view', $context)) {
        $url = new moodle_url('/local/aiquestiongen/index.php', ['courseid' => $course->id]);
        $navigation->add(
            get_string('pluginname', 'local_aiquestiongen'),
            $url,
            navigation_node::TYPE_CUSTOM,
            null,
            'aiquestiongen',
            new pix_icon('i/questions', get_string('pluginname', 'local_aiquestiongen'))
        );
    }
}

/**
 * Add link to course administration menu.
 *
 * @param settings_navigation $settingsnav The settings navigation object
 * @param context $context The context
 */
function local_aiquestiongen_extend_settings_navigation(settings_navigation $settingsnav, context $context) {
    if ($context->contextlevel == CONTEXT_COURSE && has_capability('local/aiquestiongen:view', $context)) {
        if ($coursenode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
            $url = new moodle_url('/local/aiquestiongen/index.php', ['courseid' => $context->instanceid]);
            $coursenode->add(
                get_string('pluginname', 'local_aiquestiongen'),
                $url,
                navigation_node::TYPE_CUSTOM,
                null,
                'aiquestiongen',
                new pix_icon('i/questions', get_string('pluginname', 'local_aiquestiongen'))
            );
        }
    }
}