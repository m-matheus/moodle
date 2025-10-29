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
 * Question bank integration for saving generated questions.
 *
 * @package     local_aiquestiongen
 * @copyright   2025 TCC Project
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aiquestiongen;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/multichoice/questiontype.php');
require_once($CFG->dirroot . '/question/type/truefalse/questiontype.php');
require_once($CFG->dirroot . '/question/type/shortanswer/questiontype.php');
require_once($CFG->dirroot . '/question/type/essay/questiontype.php');

/**
 * Question bank integration class.
 */
class question_bank_integration {
    
    /**
     * Create or get a question category for AI generated questions.
     *
     * @param int $contextid Context ID (course context)
     * @param string $categoryname Category name
     * @return int Category ID
     */
    public static function get_or_create_category($contextid, $categoryname = 'IA - Questões Geradas') {
        global $DB;
        
        // Check if category already exists
        $category = $DB->get_record('question_categories', [
            'contextid' => $contextid,
            'name' => $categoryname
        ]);
        
        if ($category) {
            return $category->id;
        }
        
        // Get or create the top category for this context
        $topcategory = $DB->get_record('question_categories', [
            'contextid' => $contextid,
            'parent' => 0
        ]);
        
        if (!$topcategory) {
            // Create top category if it doesn't exist
            $topcategory = new \stdClass();
            $topcategory->name = 'Top';
            $topcategory->contextid = $contextid;
            $topcategory->info = '';
            $topcategory->infoformat = FORMAT_HTML;
            $topcategory->stamp = make_unique_id_code();
            $topcategory->parent = 0;
            $topcategory->sortorder = 0;
            
            $topcategory->id = $DB->insert_record('question_categories', $topcategory);
        }
        
        // Create new category as child of top category
        $newcategory = new \stdClass();
        $newcategory->name = $categoryname;
        $newcategory->contextid = $contextid;
        $newcategory->info = 'Categoria para questões geradas automaticamente pela IA';
        $newcategory->infoformat = FORMAT_HTML;
        $newcategory->stamp = make_unique_id_code();
        $newcategory->parent = $topcategory->id;
        $newcategory->sortorder = 999;
        
        return $DB->insert_record('question_categories', $newcategory);
    }
    
    /**
     * Save a question to the Moodle question bank.
     *
     * @param array $questiondata Question data from AI
     * @param int $categoryid Question category ID
     * @param int $userid User ID who owns the question
     * @return int Question bank entry ID
     * @throws \Exception If question creation fails
     */
    public static function save_question($questiondata, $categoryid, $userid) {
        global $DB;
        
        $transaction = $DB->start_delegated_transaction();
        
        try {
            // Create question bank entry
            $bankentry = new \stdClass();
            $bankentry->questioncategoryid = $categoryid;
            $bankentry->idnumber = null;
            $bankentry->ownerid = $userid;
            
            $bankentryid = $DB->insert_record('question_bank_entries', $bankentry);
            
            // Create question record
            $question = new \stdClass();
            $question->parent = 0;
            $question->name = $questiondata['name'];
            $question->questiontext = $questiondata['text'];
            $question->questiontextformat = FORMAT_HTML;
            $question->generalfeedback = $questiondata['feedback'] ?? '';
            $question->generalfeedbackformat = FORMAT_HTML;
            $question->defaultmark = 1;
            $question->penalty = 0.3333333;
            $question->qtype = $questiondata['type'];
            $question->length = 1;
            $question->stamp = make_unique_id_code();
            $question->timecreated = time();
            $question->timemodified = time();
            $question->createdby = $userid;
            $question->modifiedby = $userid;
            
            $questionid = $DB->insert_record('question', $question);
            
            // Create question version
            $version = new \stdClass();
            $version->questionbankentryid = $bankentryid;
            $version->version = 1;
            $version->questionid = $questionid;
            $version->status = 'ready';
            
            $DB->insert_record('question_versions', $version);
            
            // Create question-specific data based on type
            switch ($questiondata['type']) {
                case 'multichoice':
                    self::create_multichoice_question($questionid, $questiondata);
                    break;
                case 'truefalse':
                    self::create_truefalse_question($questionid, $questiondata);
                    break;
                case 'shortanswer':
                    self::create_shortanswer_question($questionid, $questiondata);
                    break;
                case 'essay':
                    // Essay questions don't need additional data
                    break;
            }
            
            $transaction->allow_commit();
            
            return $bankentryid;
            
        } catch (\Exception $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }
    
    /**
     * Create multiple choice question specific data.
     *
     * @param int $questionid Question ID
     * @param array $questiondata Question data
     */
    private static function create_multichoice_question($questionid, $questiondata) {
        global $DB;
        
        // Create multichoice options record
        $options = new \stdClass();
        $options->questionid = $questionid;
        $options->single = 1; // Single answer
        $options->shuffleanswers = 1;
        $options->correctfeedback = 'Sua resposta está correta.';
        $options->correctfeedbackformat = FORMAT_HTML;
        $options->partiallycorrectfeedback = 'Sua resposta está parcialmente correta.';
        $options->partiallycorrectfeedbackformat = FORMAT_HTML;
        $options->incorrectfeedback = 'Sua resposta está incorreta.';
        $options->incorrectfeedbackformat = FORMAT_HTML;
        $options->answernumbering = 'abc';
        $options->shownumcorrect = 0;
        
        $DB->insert_record('qtype_multichoice_options', $options);
        
        // Create answer records
        if (isset($questiondata['answers'])) {
            foreach ($questiondata['answers'] as $answerdata) {
                $answer = new \stdClass();
                $answer->question = $questionid;
                $answer->answer = $answerdata['text'];
                $answer->answerformat = FORMAT_HTML;
                $answer->fraction = $answerdata['fraction'];
                $answer->feedback = $answerdata['feedback'] ?? '';
                $answer->feedbackformat = FORMAT_HTML;
                
                $DB->insert_record('question_answers', $answer);
            }
        }
    }
    
    /**
     * Create true/false question specific data.
     *
     * @param int $questionid Question ID
     * @param array $questiondata Question data
     */
    private static function create_truefalse_question($questionid, $questiondata) {
        global $DB;
        
        // Create true/false options record
        $options = new \stdClass();
        $options->question = $questionid;
        $options->trueanswer = null;
        $options->falseanswer = null;
        
        // Find correct answer
        $trueanswer = 1; // Default to true
        if (isset($questiondata['answers'])) {
            foreach ($questiondata['answers'] as $answerdata) {
                if ($answerdata['fraction'] > 0 && strtolower($answerdata['text']) === 'falso') {
                    $trueanswer = 0;
                    break;
                }
            }
        }
        
        // Create answer records
        if (isset($questiondata['answers'])) {
            foreach ($questiondata['answers'] as $answerdata) {
                $answer = new \stdClass();
                $answer->question = $questionid;
                $answer->answer = $answerdata['text'];
                $answer->answerformat = FORMAT_HTML;
                $answer->fraction = $answerdata['fraction'];
                $answer->feedback = $answerdata['feedback'] ?? '';
                $answer->feedbackformat = FORMAT_HTML;
                
                $answerid = $DB->insert_record('question_answers', $answer);
                
                // Set the appropriate answer ID in options
                if (strtolower($answerdata['text']) === 'verdadeiro' || strtolower($answerdata['text']) === 'true') {
                    $options->trueanswer = $answerid;
                } else {
                    $options->falseanswer = $answerid;
                }
            }
        }
        
        $DB->insert_record('question_truefalse', $options);
    }
    
    /**
     * Create short answer question specific data.
     *
     * @param int $questionid Question ID
     * @param array $questiondata Question data
     */
    private static function create_shortanswer_question($questionid, $questiondata) {
        global $DB;
        
        // Create shortanswer options record
        $options = new \stdClass();
        $options->questionid = $questionid;
        $options->usecase = 0; // Case insensitive
        
        $DB->insert_record('qtype_shortanswer_options', $options);
        
        // Create answer records
        if (isset($questiondata['answers'])) {
            foreach ($questiondata['answers'] as $answerdata) {
                $answer = new \stdClass();
                $answer->question = $questionid;
                $answer->answer = $answerdata['text'];
                $answer->answerformat = FORMAT_MOODLE;
                $answer->fraction = $answerdata['fraction'];
                $answer->feedback = $answerdata['feedback'] ?? '';
                $answer->feedbackformat = FORMAT_HTML;
                
                $DB->insert_record('question_answers', $answer);
            }
        }
    }
    
    /**
     * Get questions from a specific category.
     *
     * @param int $categoryid Category ID
     * @return array Array of questions
     */
    public static function get_questions_by_category($categoryid) {
        global $DB;
        
        $sql = "SELECT q.*, qbe.id as bankentryid, qv.version
                FROM {question} q
                JOIN {question_versions} qv ON qv.questionid = q.id
                JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                WHERE qbe.questioncategoryid = :categoryid
                AND qv.status = 'ready'
                ORDER BY q.name";
        
        return $DB->get_records_sql($sql, ['categoryid' => $categoryid]);
    }
    
    /**
     * Delete a question from the question bank.
     *
     * @param int $bankentryid Question bank entry ID
     * @return bool Success
     */
    public static function delete_question($bankentryid) {
        global $DB;
        
        $transaction = $DB->start_delegated_transaction();
        
        try {
            // Get question versions
            $versions = $DB->get_records('question_versions', ['questionbankentryid' => $bankentryid]);
            
            foreach ($versions as $version) {
                // Delete question-specific data and answers
                $question = $DB->get_record('question', ['id' => $version->questionid]);
                if ($question) {
                    // Delete answers
                    $DB->delete_records('question_answers', ['question' => $question->id]);
                    
                    // Delete type-specific records
                    switch ($question->qtype) {
                        case 'multichoice':
                            $DB->delete_records('qtype_multichoice_options', ['questionid' => $question->id]);
                            break;
                        case 'truefalse':
                            $DB->delete_records('question_truefalse', ['question' => $question->id]);
                            break;
                        case 'shortanswer':
                            $DB->delete_records('qtype_shortanswer_options', ['questionid' => $question->id]);
                            break;
                    }
                    
                    // Delete question
                    $DB->delete_records('question', ['id' => $question->id]);
                }
            }
            
            // Delete versions
            $DB->delete_records('question_versions', ['questionbankentryid' => $bankentryid]);
            
            // Delete bank entry
            $DB->delete_records('question_bank_entries', ['id' => $bankentryid]);
            
            $transaction->allow_commit();
            
            return true;
            
        } catch (\Exception $e) {
            $transaction->rollback($e);
            return false;
        }
    }
}