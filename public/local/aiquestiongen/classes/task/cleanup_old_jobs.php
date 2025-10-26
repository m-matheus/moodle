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
 * Scheduled task to clean up old jobs and associated data.
 *
 * @package     local_aiquestiongen
 * @copyright   2025 TCC Project
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aiquestiongen\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Task to clean up old jobs.
 */
class cleanup_old_jobs extends \core\task\scheduled_task {
    
    /**
     * Get the name of this task.
     * 
     * @return string Task name
     */
    public function get_name() {
        return get_string('task_cleanup_old_jobs', 'local_aiquestiongen');
    }
    
    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;
        
        // Delete jobs older than 30 days
        $cutoffdate = time() - (30 * 24 * 60 * 60);
        
        $oldjobs = $DB->get_records_select('local_aiquestiongen_jobs', 
            'timecreated < :cutoff', 
            ['cutoff' => $cutoffdate]
        );
        
        if (empty($oldjobs)) {
            mtrace('No old jobs to clean up.');
            return;
        }
        
        $transaction = $DB->start_delegated_transaction();
        
        try {
            foreach ($oldjobs as $job) {
                // Delete associated questions first
                $topics = $DB->get_records('local_aiquestiongen_topics', ['jobid' => $job->id]);
                foreach ($topics as $topic) {
                    // Only delete questions that haven't been saved to question bank
                    $DB->delete_records_select('local_aiquestiongen_questions', 
                        'topicid = :topicid AND status != :saved', 
                        ['topicid' => $topic->id, 'saved' => 'saved']
                    );
                }
                
                // Delete topics
                $DB->delete_records('local_aiquestiongen_topics', ['jobid' => $job->id]);
                
                // Delete job
                $DB->delete_records('local_aiquestiongen_jobs', ['id' => $job->id]);
            }
            
            $transaction->allow_commit();
            
            mtrace('Cleaned up ' . count($oldjobs) . ' old jobs.');
            
        } catch (\Exception $e) {
            $transaction->rollback($e);
            mtrace('Error cleaning up old jobs: ' . $e->getMessage());
        }
    }
}