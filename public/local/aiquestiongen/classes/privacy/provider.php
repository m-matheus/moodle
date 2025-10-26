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
 * Privacy provider implementation.
 *
 * @package     local_aiquestiongen
 * @copyright   2025 TCC Project
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aiquestiongen\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider for the AI Question Generator plugin.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Get the metadata for this plugin.
     *
     * @param collection $collection The collection to add metadata to.
     * @return collection The updated collection of metadata items.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_aiquestiongen_jobs',
            [
                'userid' => 'privacy:metadata:jobs:userid',
                'courseid' => 'privacy:metadata:jobs:courseid',
                'filename' => 'privacy:metadata:jobs:filename',
                'status' => 'privacy:metadata:jobs:status',
                'timecreated' => 'privacy:metadata:jobs:timecreated',
                'timemodified' => 'privacy:metadata:jobs:timemodified'
            ],
            'privacy:metadata:jobs'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        
        $sql = "SELECT DISTINCT ctx.id
                  FROM {context} ctx
                  JOIN {course} c ON c.id = ctx.instanceid AND ctx.contextlevel = :courselevel
                  JOIN {local_aiquestiongen_jobs} j ON j.courseid = c.id
                 WHERE j.userid = :userid";
        
        $params = [
            'courselevel' => CONTEXT_COURSE,
            'userid' => $userid
        ];
        
        $contextlist->add_from_sql($sql, $params);
        
        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        
        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }
        
        $sql = "SELECT j.userid
                  FROM {local_aiquestiongen_jobs} j
                 WHERE j.courseid = :courseid";
        
        $params = ['courseid' => $context->instanceid];
        
        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        
        if (empty($contextlist->count())) {
            return;
        }
        
        $user = $contextlist->get_user();
        
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_COURSE) {
                continue;
            }
            
            $jobs = $DB->get_records('local_aiquestiongen_jobs', [
                'userid' => $user->id,
                'courseid' => $context->instanceid
            ]);
            
            if (!empty($jobs)) {
                $data = [];
                foreach ($jobs as $job) {
                    $data[] = [
                        'filename' => $job->filename,
                        'status' => $job->status,
                        'timecreated' => transform::datetime($job->timecreated),
                        'timemodified' => transform::datetime($job->timemodified)
                    ];
                }
                
                writer::with_context($context)->export_data(
                    [get_string('pluginname', 'local_aiquestiongen')],
                    (object) $data
                );
            }
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        
        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }
        
        $DB->delete_records('local_aiquestiongen_jobs', ['courseid' => $context->instanceid]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        
        if (empty($contextlist->count())) {
            return;
        }
        
        $user = $contextlist->get_user();
        
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_COURSE) {
                continue;
            }
            
            $DB->delete_records('local_aiquestiongen_jobs', [
                'userid' => $user->id,
                'courseid' => $context->instanceid
            ]);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        
        $context = $userlist->get_context();
        
        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }
        
        $userids = $userlist->get_userids();
        
        if (!empty($userids)) {
            list($usersql, $userparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
            $userparams['courseid'] = $context->instanceid;
            
            $DB->delete_records_select(
                'local_aiquestiongen_jobs',
                "userid $usersql AND courseid = :courseid",
                $userparams
            );
        }
    }
}