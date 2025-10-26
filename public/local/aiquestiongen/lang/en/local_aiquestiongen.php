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
 * English language strings.
 *
 * @package     local_aiquestiongen
 * @copyright   2025 TCC Project
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'AI Question Generator';
$string['aiquestiongen:view'] = 'View AI Question Generator';
$string['aiquestiongen:generate'] = 'Generate questions using AI';
$string['aiquestiongen:manage'] = 'Manage AI Question Generator settings';

// Main interface
$string['uploadcurriculum'] = 'Upload Curriculum Plan';
$string['uploadcurriculum_help'] = 'Upload your teaching plan in PDF format to automatically generate question banks based on the curriculum topics.';
$string['selectfile'] = 'Select PDF file';
$string['processingfile'] = 'Processing file...';
$string['analyzetopics'] = 'Analyze Topics';
$string['generatequestions'] = 'Generate Questions';
$string['reviewquestions'] = 'Review Generated Questions';
$string['savequestions'] = 'Save to Question Bank';

// Topics analysis
$string['topicsidentified'] = 'Topics Identified';
$string['notopicsfound'] = 'No topics could be identified in the uploaded document. Please check the file content.';
$string['topic'] = 'Topic';
$string['description'] = 'Description';
$string['questioncount'] = 'Number of Questions';
$string['questiontypes'] = 'Question Types';

// Question types
$string['multichoice'] = 'Multiple Choice';
$string['truefalse'] = 'True/False';
$string['shortanswer'] = 'Short Answer';
$string['essay'] = 'Essay';

// Settings
$string['settings'] = 'AI Question Generator Settings';
$string['apikey'] = 'AI API Key';
$string['apikey_desc'] = 'API key for the AI service (OpenAI, Claude, etc.)';
$string['apiendpoint'] = 'API Endpoint';
$string['apiendpoint_desc'] = 'API endpoint URL for the AI service';
$string['defaultquestioncount'] = 'Default questions per topic';
$string['defaultquestioncount_desc'] = 'Default number of questions to generate for each identified topic';
$string['questiontypes_desc'] = 'Select which question types should be generated';

// Messages
$string['success'] = 'Success';
$string['error'] = 'Error';
$string['fileprocessed'] = 'File processed successfully';
$string['questionsgenerated'] = '{$a} questions generated successfully';
$string['questionssaved'] = 'Questions saved to question bank successfully';
$string['invalidfile'] = 'Invalid file format. Please upload a PDF file.';
$string['nofileselected'] = 'Please select a file to upload.';
$string['apierror'] = 'Error communicating with AI service: {$a}';
$string['processingerror'] = 'Error processing the document: {$a}';

// Privacy
$string['privacy:metadata'] = 'The AI Question Generator plugin does not store any personal data.';

// Additional strings
$string['recent_jobs'] = 'Recent Jobs';
$string['filename'] = 'Filename';
$string['status'] = 'Status';
$string['actions'] = 'Actions';
$string['view_topics'] = 'View Topics';
$string['processing'] = 'Processing';
$string['completed'] = 'Completed';
$string['failed'] = 'Failed';
$string['pending'] = 'Pending';

// Tasks
$string['task_process_pending_jobs'] = 'Process pending AI question generation jobs';
$string['task_cleanup_old_jobs'] = 'Clean up old AI question generation jobs';