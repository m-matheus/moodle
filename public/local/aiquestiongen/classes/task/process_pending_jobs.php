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
 * Scheduled task to process pending PDF analysis and question generation jobs.
 *
 * @package     local_aiquestiongen
 * @copyright   2025 TCC Project
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aiquestiongen\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Task to process pending jobs.
 */
class process_pending_jobs extends \core\task\scheduled_task {
    
    /**
     * Get the name of this task.
     * 
     * @return string Task name
     */
    public function get_name() {
        return get_string('task_process_pending_jobs', 'local_aiquestiongen');
    }
    
    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;
        
        // Get pending jobs (limit to 5 to avoid overloading)
        $jobs = $DB->get_records('local_aiquestiongen_jobs', 
            ['status' => 'pending'], 
            'timecreated ASC',
            '*',
            0, 5
        );
        
        if (empty($jobs)) {
            return;
        }
        
        foreach ($jobs as $job) {
            $this->process_job($job);
        }
    }
    
    /**
     * Process a single job.
     * 
     * @param \stdClass $job Job record
     */
    private function process_job($job) {
        global $DB;
        
        try {
            // Mark job as processing
            $job->status = 'processing';
            $job->timemodified = time();
            $DB->update_record('local_aiquestiongen_jobs', $job);
            
            // In a real implementation, you would:
            // 1. Extract text from the PDF file
            // 2. Call AI service to analyze topics
            // 3. Create topic records
            // 4. Mark job as completed
            
            // For now, we'll just mark as completed
            $job->status = 'completed';
            $job->timemodified = time();
            $DB->update_record('local_aiquestiongen_jobs', $job);
            
            mtrace("Processed job {$job->id} for user {$job->userid}");
            
        } catch (\Exception $e) {
            // Mark job as failed
            $job->status = 'failed';
            $job->error_message = $e->getMessage();
            $job->timemodified = time();
            $DB->update_record('local_aiquestiongen_jobs', $job);
            
            mtrace("Failed to process job {$job->id}: " . $e->getMessage());
        }
    }
}