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
 * Main page for AI Question Generator.
 *
 * @package     local_aiquestiongen
 * @copyright   2025 TCC Project
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/filelib.php');

$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', 'upload', PARAM_ALPHA);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($courseid);

require_login($course);
require_capability('local/aiquestiongen:view', $context);

$PAGE->set_url('/local/aiquestiongen/index.php', ['courseid' => $courseid]);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_title(get_string('pluginname', 'local_aiquestiongen'));
$PAGE->set_heading($course->fullname . ': ' . get_string('pluginname', 'local_aiquestiongen'));

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('pluginname', 'local_aiquestiongen'));

switch ($action) {
    case 'upload':
        show_upload_form($context, $courseid);
        break;
    case 'process':
        process_upload($context, $courseid);
        break;
    case 'topics':
        show_topics($courseid);
        break;
    case 'generate':
        generate_questions($courseid);
        break;
    case 'review':
        show_review($courseid);
        break;
    case 'save':
        save_questions($courseid);
        break;
    default:
        show_upload_form($context, $courseid);
}

echo $OUTPUT->footer();

/**
 * Show the file upload form.
 */
function show_upload_form($context, $courseid) {
    global $OUTPUT, $CFG;
    
    echo $OUTPUT->box_start('generalbox');
    echo html_writer::tag('p', get_string('uploadcurriculum_help', 'local_aiquestiongen'));
    
    echo '<form method="post" enctype="multipart/form-data" action="index.php">';
    echo '<input type="hidden" name="courseid" value="' . $courseid . '">';
    echo '<input type="hidden" name="action" value="process">';
    echo '<input type="hidden" name="sesskey" value="' . sesskey() . '">';
    
    echo '<div class="form-group">';
    echo '<label for="curriculum_file">' . get_string('selectfile', 'local_aiquestiongen') . '</label>';
    echo '<input type="file" id="curriculum_file" name="curriculum_file" accept=".pdf" required class="form-control">';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<input type="submit" value="' . get_string('analyzetopics', 'local_aiquestiongen') . '" class="btn btn-primary">';
    echo '</div>';
    
    echo '</form>';
    echo $OUTPUT->box_end();
    
    // Show recent jobs
    show_recent_jobs($courseid);
}

/**
 * Process the uploaded file.
 */
function process_upload($context, $courseid) {
    global $USER, $DB, $OUTPUT;
    
    require_sesskey();
    require_capability('local/aiquestiongen:generate', $context);
    
    if (!isset($_FILES['curriculum_file']) || $_FILES['curriculum_file']['error'] !== UPLOAD_ERR_OK) {
        echo $OUTPUT->notification(get_string('nofileselected', 'local_aiquestiongen'), 'error');
        show_upload_form($context, $courseid);
        return;
    }
    
    $file = $_FILES['curriculum_file'];
    
    // Validate file type
    if ($file['type'] !== 'application/pdf' && !str_ends_with(strtolower($file['name']), '.pdf')) {
        echo $OUTPUT->notification(get_string('invalidfile', 'local_aiquestiongen'), 'error');
        show_upload_form($context, $courseid);
        return;
    }
    
    try {
        // Create job record
        $job = new stdClass();
        $job->userid = $USER->id;
        $job->courseid = $courseid;
        $job->filename = $file['name'];
        $job->filecontentshash = sha1_file($file['tmp_name']);
        $job->status = 'processing';
        $job->timecreated = time();
        $job->timemodified = time();
        
        $jobid = $DB->insert_record('local_aiquestiongen_jobs', $job);
        
        // For now, simulate processing and redirect to topics view
        echo $OUTPUT->notification(get_string('fileprocessed', 'local_aiquestiongen'), 'success');
        
        // Simulate topic extraction (in a real implementation, this would call the AI service)
        create_sample_topics($jobid);
        
        echo '<div class="mt-3">';
        echo '<a href="index.php?courseid=' . $courseid . '&action=topics&jobid=' . $jobid . '" class="btn btn-primary">';
        echo get_string('topicsidentified', 'local_aiquestiongen');
        echo '</a>';
        echo '</div>';
        
    } catch (Exception $e) {
        echo $OUTPUT->notification(get_string('processingerror', 'local_aiquestiongen', $e->getMessage()), 'error');
        show_upload_form($context, $courseid);
    }
}

/**
 * Show identified topics.
 */
function show_topics($courseid) {
    global $DB, $OUTPUT;
    
    $jobid = required_param('jobid', PARAM_INT);
    $job = $DB->get_record('local_aiquestiongen_jobs', ['id' => $jobid, 'courseid' => $courseid], '*', MUST_EXIST);
    
    $topics = $DB->get_records('local_aiquestiongen_topics', ['jobid' => $jobid], 'sortorder');
    
    if (empty($topics)) {
        echo $OUTPUT->notification(get_string('notopicsfound', 'local_aiquestiongen'), 'warning');
        return;
    }
    
    echo $OUTPUT->heading(get_string('topicsidentified', 'local_aiquestiongen'), 3);
    
    echo '<form method="post" action="index.php">';
    echo '<input type="hidden" name="courseid" value="' . $courseid . '">';
    echo '<input type="hidden" name="action" value="generate">';
    echo '<input type="hidden" name="jobid" value="' . $jobid . '">';
    echo '<input type="hidden" name="sesskey" value="' . sesskey() . '">';
    
    echo '<table class="table table-striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>' . get_string('topic', 'local_aiquestiongen') . '</th>';
    echo '<th>' . get_string('description', 'local_aiquestiongen') . '</th>';
    echo '<th>' . get_string('questioncount', 'local_aiquestiongen') . '</th>';
    echo '<th>' . get_string('questiontypes', 'local_aiquestiongen') . '</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($topics as $topic) {
        echo '<tr>';
        echo '<td><strong>' . s($topic->title) . '</strong></td>';
        echo '<td>' . s($topic->description) . '</td>';
        echo '<td>';
        echo '<input type="number" name="questioncount[' . $topic->id . ']" value="' . $topic->questioncount . '" min="1" max="20" class="form-control" style="width: 80px;">';
        echo '</td>';
        echo '<td>';
        $types = explode(',', $topic->questiontypes);
        $availableTypes = ['multichoice', 'truefalse', 'shortanswer', 'essay'];
        foreach ($availableTypes as $type) {
            $checked = in_array($type, $types) ? 'checked' : '';
            echo '<label class="checkbox-inline">';
            echo '<input type="checkbox" name="questiontypes[' . $topic->id . '][]" value="' . $type . '" ' . $checked . '>';
            echo get_string($type, 'local_aiquestiongen');
            echo '</label><br>';
        }
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    
    echo '<div class="mt-3">';
    echo '<input type="submit" value="' . get_string('generatequestions', 'local_aiquestiongen') . '" class="btn btn-primary">';
    echo '</div>';
    echo '</form>';
}

/**
 * Show recent jobs for the course.
 */
function show_recent_jobs($courseid) {
    global $DB, $OUTPUT, $USER;
    
    $jobs = $DB->get_records('local_aiquestiongen_jobs', 
        ['courseid' => $courseid, 'userid' => $USER->id], 
        'timecreated DESC',
        '*',
        0, 5
    );
    
    if (!empty($jobs)) {
        echo $OUTPUT->heading(get_string('recent_jobs', 'local_aiquestiongen'), 4);
        echo '<div class="table-responsive">';
        echo '<table class="table table-sm">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . get_string('filename', 'local_aiquestiongen') . '</th>';
        echo '<th>' . get_string('status', 'local_aiquestiongen') . '</th>';
        echo '<th>Data</th>';
        echo '<th>' . get_string('actions', 'local_aiquestiongen') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($jobs as $job) {
            echo '<tr>';
            echo '<td>' . s($job->filename) . '</td>';
            echo '<td><span class="badge badge-' . get_status_class($job->status) . '">' . get_string($job->status, 'local_aiquestiongen') . '</span></td>';
            echo '<td>' . userdate($job->timecreated) . '</td>';
            echo '<td>';
            if ($job->status === 'completed') {
                echo '<a href="index.php?courseid=' . $courseid . '&action=topics&jobid=' . $job->id . '" class="btn btn-sm btn-outline-primary">' . get_string('view_topics', 'local_aiquestiongen') . '</a>';
            }
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }
}

/**
 * Create sample topics for demonstration.
 */
function create_sample_topics($jobid) {
    global $DB;
    
    $sampleTopics = [
        [
            'title' => 'Introdução à Programação',
            'description' => 'Conceitos básicos de programação, algoritmos e estruturas de dados.',
            'content' => 'Variáveis, tipos de dados, estruturas condicionais e loops.',
        ],
        [
            'title' => 'Orientação a Objetos',
            'description' => 'Paradigma de programação orientada a objetos.',
            'content' => 'Classes, objetos, herança, polimorfismo e encapsulamento.',
        ],
        [
            'title' => 'Banco de Dados',
            'description' => 'Conceitos fundamentais de sistemas de gerenciamento de banco de dados.',
            'content' => 'SQL, normalização, modelagem de dados e transações.',
        ],
    ];
    
    $sortorder = 1;
    foreach ($sampleTopics as $topicData) {
        $topic = new stdClass();
        $topic->jobid = $jobid;
        $topic->title = $topicData['title'];
        $topic->description = $topicData['description'];
        $topic->content = $topicData['content'];
        $topic->sortorder = $sortorder++;
        $topic->questioncount = 5;
        $topic->questiontypes = 'multichoice,truefalse';
        $topic->timecreated = time();
        
        $DB->insert_record('local_aiquestiongen_topics', $topic);
    }
}

/**
 * Get CSS class for job status.
 */
function get_status_class($status) {
    switch ($status) {
        case 'completed':
            return 'success';
        case 'processing':
            return 'warning';
        case 'failed':
            return 'danger';
        default:
            return 'secondary';
    }
}

/**
 * Generate questions using AI.
 */
function generate_questions($courseid) {
    global $DB, $OUTPUT, $USER;
    
    require_sesskey();
    $jobid = required_param('jobid', PARAM_INT);
    $context = context_course::instance($courseid);
    require_capability('local/aiquestiongen:generate', $context);
    
    $job = $DB->get_record('local_aiquestiongen_jobs', ['id' => $jobid, 'courseid' => $courseid], '*', MUST_EXIST);
    
    // Get submitted topic configurations
    $questioncounts = optional_param_array('questioncount', [], PARAM_INT);
    $questiontypes = optional_param_array('questiontypes', [], PARAM_RAW);
    
    try {
        $aiservice = new \local_aiquestiongen\ai_service();
        $generatedcount = 0;
        
        // Get topics for this job
        $topics = $DB->get_records('local_aiquestiongen_topics', ['jobid' => $jobid], 'sortorder');
        
        foreach ($topics as $topic) {
            $count = isset($questioncounts[$topic->id]) ? $questioncounts[$topic->id] : $topic->questioncount;
            $types = isset($questiontypes[$topic->id]) ? $questiontypes[$topic->id] : explode(',', $topic->questiontypes);
            
            // Update topic configuration
            $topic->questioncount = $count;
            $topic->questiontypes = implode(',', $types);
            $DB->update_record('local_aiquestiongen_topics', $topic);
            
            // Generate questions using AI
            $topicdata = [
                'title' => $topic->title,
                'description' => $topic->description,
                'content' => $topic->content
            ];
            
            $questions = $aiservice->generate_questions($topicdata, $types, $count);
            
            // Save generated questions to our temporary table
            foreach ($questions as $questiondata) {
                $question = new stdClass();
                $question->topicid = $topic->id;
                $question->questionname = $questiondata['name'];
                $question->questiontext = $questiondata['text'];
                $question->questiontype = $questiondata['type'];
                $question->answers = json_encode($questiondata['answers']);
                $question->feedback = $questiondata['feedback'] ?? '';
                $question->difficulty = $questiondata['difficulty'] ?? 'medium';
                $question->status = 'generated';
                $question->timecreated = time();
                $question->timemodified = time();
                
                $DB->insert_record('local_aiquestiongen_questions', $question);
                $generatedcount++;
            }
        }
        
        // Update job status
        $job->status = 'completed';
        $job->timemodified = time();
        $DB->update_record('local_aiquestiongen_jobs', $job);
        
        echo $OUTPUT->notification(get_string('questionsgenerated', 'local_aiquestiongen', $generatedcount), 'success');
        
        echo '<div class="mt-3">';
        echo '<a href="index.php?courseid=' . $courseid . '&action=review&jobid=' . $jobid . '" class="btn btn-primary">';
        echo get_string('reviewquestions', 'local_aiquestiongen');
        echo '</a>';
        echo '</div>';
        
    } catch (Exception $e) {
        echo $OUTPUT->notification(get_string('processingerror', 'local_aiquestiongen', $e->getMessage()), 'error');
        show_topics($courseid);
    }
}

/**
 * Show review page for generated questions.
 */
function show_review($courseid) {
    global $DB, $OUTPUT;
    
    $jobid = required_param('jobid', PARAM_INT);
    $job = $DB->get_record('local_aiquestiongen_jobs', ['id' => $jobid, 'courseid' => $courseid], '*', MUST_EXIST);
    
    // Get all questions for this job (through topics)
    $sql = "SELECT q.*, t.title as topictitle
            FROM {local_aiquestiongen_questions} q
            JOIN {local_aiquestiongen_topics} t ON t.id = q.topicid
            WHERE t.jobid = :jobid
            ORDER BY t.sortorder, q.questionname";
    
    $questions = $DB->get_records_sql($sql, ['jobid' => $jobid]);
    
    if (empty($questions)) {
        echo $OUTPUT->notification('Nenhuma questão foi gerada ainda.', 'warning');
        return;
    }
    
    echo $OUTPUT->heading(get_string('reviewquestions', 'local_aiquestiongen'), 3);
    
    echo '<form method="post" action="index.php">';
    echo '<input type="hidden" name="courseid" value="' . $courseid . '">';
    echo '<input type="hidden" name="action" value="save">';
    echo '<input type="hidden" name="jobid" value="' . $jobid . '">';
    echo '<input type="hidden" name="sesskey" value="' . sesskey() . '">';
    
    echo '<div class="mb-3">';
    echo '<button type="button" id="select-all" class="btn btn-sm btn-outline-primary">Selecionar Todas</button>';
    echo '<button type="button" id="deselect-all" class="btn btn-sm btn-outline-secondary">Desmarcar Todas</button>';
    echo '</div>';
    
    $currenttopic = '';
    foreach ($questions as $question) {
        if ($currenttopic !== $question->topictitle) {
            if ($currenttopic !== '') {
                echo '</div>'; // Close previous topic
            }
            echo '<div class="card mb-3">';
            echo '<div class="card-header"><h5>' . s($question->topictitle) . '</h5></div>';
            echo '<div class="card-body">';
            $currenttopic = $question->topictitle;
        }
        
        echo '<div class="question-item border p-3 mb-2">';
        echo '<div class="form-check">';
        echo '<input class="form-check-input question-checkbox" type="checkbox" name="selected_questions[]" value="' . $question->id . '" checked>';
        echo '<label class="form-check-label">';
        echo '<strong>' . s($question->questionname) . '</strong>';
        echo '</label>';
        echo '</div>';
        
        echo '<div class="mt-2">';
        echo '<p><strong>Tipo:</strong> ' . get_string($question->questiontype, 'local_aiquestiongen') . '</p>';
        echo '<p><strong>Dificuldade:</strong> ' . ucfirst($question->difficulty) . '</p>';
        echo '<p><strong>Pergunta:</strong></p>';
        echo '<div class="question-text">' . format_text($question->questiontext, FORMAT_HTML) . '</div>';
        
        if (!empty($question->answers)) {
            $answers = json_decode($question->answers, true);
            if ($answers) {
                echo '<p><strong>Respostas:</strong></p>';
                echo '<ul>';
                foreach ($answers as $answer) {
                    $correct = isset($answer['fraction']) && $answer['fraction'] > 0 ? ' ✓' : '';
                    echo '<li>' . s($answer['text']) . $correct . '</li>';
                }
                echo '</ul>';
            }
        }
        
        if (!empty($question->feedback)) {
            echo '<p><strong>Feedback:</strong></p>';
            echo '<div class="feedback">' . format_text($question->feedback, FORMAT_HTML) . '</div>';
        }
        echo '</div>';
        echo '</div>';
    }
    
    if ($currenttopic !== '') {
        echo '</div>'; // Close last topic
        echo '</div>'; // Close last card
    }
    
    echo '<div class="mt-3">';
    echo '<input type="submit" value="' . get_string('savequestions', 'local_aiquestiongen') . '" class="btn btn-success">';
    echo ' <a href="index.php?courseid=' . $courseid . '" class="btn btn-secondary">Cancelar</a>';
    echo '</div>';
    echo '</form>';
    
    // Add JavaScript for select all/deselect all
    echo '<script>
    document.getElementById("select-all").addEventListener("click", function() {
        document.querySelectorAll(".question-checkbox").forEach(function(checkbox) {
            checkbox.checked = true;
        });
    });
    
    document.getElementById("deselect-all").addEventListener("click", function() {
        document.querySelectorAll(".question-checkbox").forEach(function(checkbox) {
            checkbox.checked = false;
        });
    });
    </script>';
}

/**
 * Save selected questions to the question bank.
 */
function save_questions($courseid) {
    global $DB, $OUTPUT, $USER;
    
    require_sesskey();
    $jobid = required_param('jobid', PARAM_INT);
    $selectedquestions = optional_param_array('selected_questions', [], PARAM_INT);
    
    $context = context_course::instance($courseid);
    require_capability('local/aiquestiongen:generate', $context);
    
    if (empty($selectedquestions)) {
        echo $OUTPUT->notification('Nenhuma questão foi selecionada.', 'warning');
        show_review($courseid);
        return;
    }
    
    try {
        // Get or create question category
        $categoryid = \local_aiquestiongen\question_bank_integration::get_or_create_category(
            $context->id, 
            'IA - Questões Geradas'
        );
        
        $savedcount = 0;
        
        foreach ($selectedquestions as $questionid) {
            $question = $DB->get_record('local_aiquestiongen_questions', ['id' => $questionid]);
            if (!$question) {
                continue;
            }
            
            // Prepare question data for saving
            $questiondata = [
                'name' => $question->questionname,
                'text' => $question->questiontext,
                'type' => $question->questiontype,
                'feedback' => $question->feedback,
                'answers' => json_decode($question->answers, true) ?: []
            ];
            
            // Save to question bank
            $bankentryid = \local_aiquestiongen\question_bank_integration::save_question(
                $questiondata, 
                $categoryid, 
                $USER->id
            );
            
            // Update our question record
            $question->status = 'saved';
            $question->questionbankentryid = $bankentryid;
            $question->timemodified = time();
            $DB->update_record('local_aiquestiongen_questions', $question);
            
            $savedcount++;
        }
        
        echo $OUTPUT->notification(get_string('questionssaved', 'local_aiquestiongen'), 'success');
        echo $OUTPUT->notification("$savedcount questões foram salvas no banco de questões.", 'info');
        
        // Show link to question bank
        $questionbankurl = new moodle_url('/question/edit.php', ['courseid' => $courseid, 'cat' => $categoryid . ',' . $context->id]);
        echo '<div class="mt-3">';
        echo '<a href="' . $questionbankurl . '" class="btn btn-primary">Ver no Banco de Questões</a>';
        echo ' <a href="index.php?courseid=' . $courseid . '" class="btn btn-secondary">Voltar ao Início</a>';
        echo '</div>';
        
    } catch (Exception $e) {
        echo $OUTPUT->notification('Erro ao salvar questões: ' . $e->getMessage(), 'error');
        show_review($courseid);
    }
}