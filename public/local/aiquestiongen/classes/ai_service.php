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
 * AI service integration for topic analysis and question generation.
 *
 * @package     local_aiquestiongen
 * @copyright   2025 TCC Project
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aiquestiongen;

defined('MOODLE_INTERNAL') || die();

/**
 * AI Service class for integrating with external AI APIs.
 */
class ai_service {
    
    /** @var string AI API endpoint */
    private $apiendpoint;
    
    /** @var string AI API key */
    private $apikey;
    
    /**
     * Constructor.
     */
    public function __construct() {
        $this->apiendpoint = get_config('local_aiquestiongen', 'apiendpoint');
        $this->apikey = get_config('local_aiquestiongen', 'apikey');
    }
    
    /**
     * Analyze curriculum text and extract topics.
     *
     * @param string $text Curriculum text content
     * @return array Array of topics with title, description, and content
     * @throws \Exception If API call fails
     */
    public function analyze_topics($text) {
        if (empty($this->apikey) || empty($this->apiendpoint)) {
            // Return sample topics for demonstration
            return $this->get_sample_topics();
        }
        
        $prompt = $this->build_topic_analysis_prompt($text);
        
        try {
            $response = $this->call_ai_api($prompt);
            return $this->parse_topics_response($response);
        } catch (\Exception $e) {
            // Fallback to sample topics if API fails
            return $this->get_sample_topics();
        }
    }
    
    /**
     * Generate questions for a specific topic.
     *
     * @param array $topic Topic data
     * @param array $questiontypes Types of questions to generate
     * @param int $count Number of questions to generate
     * @return array Array of generated questions
     * @throws \Exception If API call fails
     */
    public function generate_questions($topic, $questiontypes, $count) {
        if (empty($this->apikey) || empty($this->apiendpoint)) {
            // Return sample questions for demonstration
            return $this->get_sample_questions($topic, $questiontypes, $count);
        }
        
        $prompt = $this->build_question_generation_prompt($topic, $questiontypes, $count);
        
        try {
            $response = $this->call_ai_api($prompt);
            return $this->parse_questions_response($response);
        } catch (\Exception $e) {
            // Fallback to sample questions if API fails
            return $this->get_sample_questions($topic, $questiontypes, $count);
        }
    }
    
    /**
     * Call the AI API with a prompt.
     *
     * @param string $prompt The prompt to send
     * @return string The API response
     * @throws \Exception If API call fails
     */
    private function call_ai_api($prompt) {
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert educational content creator and question generator.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 2000,
            'temperature' => 0.7
        ];
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apikey
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiendpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new \Exception('CURL error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpcode !== 200) {
            throw new \Exception('API returned HTTP ' . $httpcode . ': ' . $response);
        }
        
        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from API');
        }
        
        if (!isset($decoded['choices'][0]['message']['content'])) {
            throw new \Exception('Unexpected API response format');
        }
        
        return $decoded['choices'][0]['message']['content'];
    }
    
    /**
     * Build prompt for topic analysis.
     *
     * @param string $text Curriculum text
     * @return string The prompt
     */
    private function build_topic_analysis_prompt($text) {
        return "Analyze the following curriculum text and identify the main educational topics. " .
               "For each topic, provide:\n" .
               "1. A clear, concise title\n" .
               "2. A brief description (1-2 sentences)\n" .
               "3. Key concepts and subtopics\n\n" .
               "Return the results in JSON format with the following structure:\n" .
               "{\n" .
               "  \"topics\": [\n" .
               "    {\n" .
               "      \"title\": \"Topic Title\",\n" .
               "      \"description\": \"Brief description\",\n" .
               "      \"content\": \"Key concepts and subtopics\"\n" .
               "    }\n" .
               "  ]\n" .
               "}\n\n" .
               "Curriculum text:\n" . $text;
    }
    
    /**
     * Build prompt for question generation.
     *
     * @param array $topic Topic data
     * @param array $questiontypes Question types to generate
     * @param int $count Number of questions
     * @return string The prompt
     */
    private function build_question_generation_prompt($topic, $questiontypes, $count) {
        $types = implode(', ', $questiontypes);
        
        return "Generate {$count} educational questions about the following topic:\n\n" .
               "Topic: {$topic['title']}\n" .
               "Description: {$topic['description']}\n" .
               "Content: {$topic['content']}\n\n" .
               "Generate questions of these types: {$types}\n\n" .
               "For multiple choice questions, provide 4 options with only one correct answer.\n" .
               "For true/false questions, provide the statement and correct answer.\n" .
               "For short answer questions, provide the question and expected answer.\n" .
               "For essay questions, provide the question and key points to cover.\n\n" .
               "Return results in JSON format:\n" .
               "{\n" .
               "  \"questions\": [\n" .
               "    {\n" .
               "      \"name\": \"Question Name\",\n" .
               "      \"text\": \"Question text\",\n" .
               "      \"type\": \"multichoice|truefalse|shortanswer|essay\",\n" .
               "      \"answers\": [\n" .
               "        {\"text\": \"Answer text\", \"fraction\": 1.0, \"feedback\": \"Feedback\"}\n" .
               "      ],\n" .
               "      \"feedback\": \"General feedback\",\n" .
               "      \"difficulty\": \"easy|medium|hard\"\n" .
               "    }\n" .
               "  ]\n" .
               "}";
    }
    
    /**
     * Parse topics from AI response.
     *
     * @param string $response AI response
     * @return array Parsed topics
     */
    private function parse_topics_response($response) {
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !isset($decoded['topics'])) {
            return $this->get_sample_topics();
        }
        
        return $decoded['topics'];
    }
    
    /**
     * Parse questions from AI response.
     *
     * @param string $response AI response
     * @return array Parsed questions
     */
    private function parse_questions_response($response) {
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !isset($decoded['questions'])) {
            return [];
        }
        
        return $decoded['questions'];
    }
    
    /**
     * Get sample topics for demonstration.
     *
     * @return array Sample topics
     */
    private function get_sample_topics() {
        return [
            [
                'title' => 'Introdução à Programação',
                'description' => 'Conceitos básicos de programação, algoritmos e estruturas de dados fundamentais.',
                'content' => 'Variáveis, tipos de dados, estruturas condicionais, loops, arrays, funções'
            ],
            [
                'title' => 'Orientação a Objetos',
                'description' => 'Paradigma de programação orientada a objetos e seus princípios fundamentais.',
                'content' => 'Classes, objetos, herança, polimorfismo, encapsulamento, abstração, interfaces'
            ],
            [
                'title' => 'Banco de Dados',
                'description' => 'Conceitos fundamentais de sistemas de gerenciamento de banco de dados relacionais.',
                'content' => 'SQL, normalização, modelagem de dados, transações, joins, índices'
            ],
            [
                'title' => 'Desenvolvimento Web',
                'description' => 'Tecnologias e conceitos para desenvolvimento de aplicações web modernas.',
                'content' => 'HTML, CSS, JavaScript, APIs REST, HTTP, sessões, segurança web'
            ],
            [
                'title' => 'Teste e Qualidade',
                'description' => 'Metodologias e práticas para garantir a qualidade do software desenvolvido.',
                'content' => 'Testes unitários, TDD, debugging, métricas de qualidade, code review'
            ]
        ];
    }
    
    /**
     * Get sample questions for demonstration.
     *
     * @param array $topic Topic data
     * @param array $questiontypes Question types
     * @param int $count Number of questions
     * @return array Sample questions
     */
    private function get_sample_questions($topic, $questiontypes, $count) {
        $questions = [];
        $topictitle = $topic['title'] ?? 'Programação';
        
        for ($i = 1; $i <= $count; $i++) {
            $questiontype = $questiontypes[array_rand($questiontypes)];
            
            switch ($questiontype) {
                case 'multichoice':
                    $questions[] = [
                        'name' => "Questão {$i} - {$topictitle}",
                        'text' => "Qual das seguintes opções melhor descreve {$topictitle}?",
                        'type' => 'multichoice',
                        'answers' => [
                            ['text' => 'Opção correta sobre ' . $topictitle, 'fraction' => 1.0, 'feedback' => 'Correto!'],
                            ['text' => 'Opção incorreta A', 'fraction' => 0.0, 'feedback' => 'Incorreto.'],
                            ['text' => 'Opção incorreta B', 'fraction' => 0.0, 'feedback' => 'Incorreto.'],
                            ['text' => 'Opção incorreta C', 'fraction' => 0.0, 'feedback' => 'Incorreto.']
                        ],
                        'feedback' => "Esta questão testa o conhecimento básico sobre {$topictitle}.",
                        'difficulty' => 'medium'
                    ];
                    break;
                    
                case 'truefalse':
                    $questions[] = [
                        'name' => "Questão {$i} - {$topictitle}",
                        'text' => "{$topictitle} é um conceito fundamental na programação moderna.",
                        'type' => 'truefalse',
                        'answers' => [
                            ['text' => 'Verdadeiro', 'fraction' => 1.0, 'feedback' => 'Correto!'],
                            ['text' => 'Falso', 'fraction' => 0.0, 'feedback' => 'Incorreto.']
                        ],
                        'feedback' => "Esta questão verifica o entendimento sobre {$topictitle}.",
                        'difficulty' => 'easy'
                    ];
                    break;
                    
                case 'shortanswer':
                    $questions[] = [
                        'name' => "Questão {$i} - {$topictitle}",
                        'text' => "Cite dois conceitos importantes relacionados a {$topictitle}.",
                        'type' => 'shortanswer',
                        'answers' => [
                            ['text' => '*', 'fraction' => 1.0, 'feedback' => 'Resposta aceita.']
                        ],
                        'feedback' => "Respostas esperadas incluem conceitos fundamentais de {$topictitle}.",
                        'difficulty' => 'medium'
                    ];
                    break;
                    
                case 'essay':
                    $questions[] = [
                        'name' => "Questão {$i} - {$topictitle}",
                        'text' => "Explique detalhadamente os principais conceitos de {$topictitle} e sua importância na programação.",
                        'type' => 'essay',
                        'answers' => [],
                        'feedback' => "A resposta deve cobrir os aspectos teóricos e práticos de {$topictitle}.",
                        'difficulty' => 'hard'
                    ];
                    break;
            }
        }
        
        return $questions;
    }
}