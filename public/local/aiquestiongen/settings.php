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
 * Plugin settings.
 *
 * @package     local_aiquestiongen
 * @copyright   2025 TCC Project
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_aiquestiongen', 
        get_string('settings', 'local_aiquestiongen'));
    
    $ADMIN->add('localplugins', $settings);

    // AI API Configuration
    $settings->add(new admin_setting_heading(
        'local_aiquestiongen/apiconfig',
        get_string('settings', 'local_aiquestiongen'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'local_aiquestiongen/apikey',
        get_string('apikey', 'local_aiquestiongen'),
        get_string('apikey_desc', 'local_aiquestiongen'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_aiquestiongen/apiendpoint',
        get_string('apiendpoint', 'local_aiquestiongen'),
        get_string('apiendpoint_desc', 'local_aiquestiongen'),
        'https://api.openai.com/v1/chat/completions',
        PARAM_URL
    ));

    // Question Generation Settings
    $settings->add(new admin_setting_configtext(
        'local_aiquestiongen/defaultquestioncount',
        get_string('defaultquestioncount', 'local_aiquestiongen'),
        get_string('defaultquestioncount_desc', 'local_aiquestiongen'),
        5,
        PARAM_INT
    ));

    $questiontypes = [
        'multichoice' => get_string('multichoice', 'local_aiquestiongen'),
        'truefalse' => get_string('truefalse', 'local_aiquestiongen'),
        'shortanswer' => get_string('shortanswer', 'local_aiquestiongen'),
        'essay' => get_string('essay', 'local_aiquestiongen'),
    ];

    $settings->add(new admin_setting_configmulticheckbox(
        'local_aiquestiongen/questiontypes',
        get_string('questiontypes', 'local_aiquestiongen'),
        get_string('questiontypes_desc', 'local_aiquestiongen'),
        ['multichoice' => 1, 'truefalse' => 1],
        $questiontypes
    ));
}