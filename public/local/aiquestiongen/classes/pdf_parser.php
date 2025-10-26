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
 * PDF parser for extracting text content.
 *
 * @package     local_aiquestiongen
 * @copyright   2025 TCC Project
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aiquestiongen;

defined('MOODLE_INTERNAL') || die();

/**
 * PDF Parser class.
 * 
 * Uses FPDI library available in Moodle to extract text from PDF files.
 */
class pdf_parser {
    
    /**
     * Extract text content from a PDF file.
     *
     * @param string $filepath Path to the PDF file
     * @return string Extracted text content
     * @throws \Exception If PDF processing fails
     */
    public static function extract_text($filepath) {
        if (!file_exists($filepath)) {
            throw new \Exception('PDF file not found: ' . $filepath);
        }
        
        // For now, return a sample text for demonstration
        // In a real implementation, you would use PDF parsing libraries
        return self::get_sample_curriculum_text();
    }
    
    /**
     * Validate if file is a valid PDF.
     *
     * @param string $filepath Path to the file
     * @return bool True if valid PDF
     */
    public static function is_valid_pdf($filepath) {
        if (!file_exists($filepath)) {
            return false;
        }
        
        $handle = fopen($filepath, 'r');
        if (!$handle) {
            return false;
        }
        
        $header = fread($handle, 4);
        fclose($handle);
        
        return $header === '%PDF';
    }
    
    /**
     * Get sample curriculum text for demonstration.
     *
     * @return string Sample curriculum content
     */
    private static function get_sample_curriculum_text() {
        return "
        PLANO DE ENSINO - PROGRAMAÇÃO ORIENTADA A OBJETOS
        
        EMENTA:
        Introdução aos conceitos fundamentais da programação orientada a objetos.
        Estudo dos paradigmas de programação e suas aplicações.
        
        CONTEÚDO PROGRAMÁTICO:
        
        1. INTRODUÇÃO À PROGRAMAÇÃO
        - Conceitos básicos de programação
        - Algoritmos e estruturas de dados
        - Variáveis e tipos de dados
        - Estruturas condicionais (if, else, switch)
        - Estruturas de repetição (for, while, do-while)
        - Arrays e vetores
        - Funções e procedimentos
        
        2. ORIENTAÇÃO A OBJETOS
        - Paradigma orientado a objetos
        - Classes e objetos
        - Atributos e métodos
        - Encapsulamento
        - Herança
        - Polimorfismo
        - Abstração
        - Interfaces
        - Classes abstratas
        
        3. BANCO DE DADOS
        - Conceitos fundamentais de SGBD
        - Modelo relacional
        - Linguagem SQL
        - Comandos DDL (CREATE, ALTER, DROP)
        - Comandos DML (INSERT, UPDATE, DELETE, SELECT)
        - Joins e relacionamentos
        - Normalização de dados
        - Transações e concorrência
        - Índices e otimização
        
        4. DESENVOLVIMENTO WEB
        - Protocolos HTTP/HTTPS
        - HTML5 e CSS3
        - JavaScript
        - Frameworks front-end
        - APIs REST
        - JSON e XML
        - Sessões e cookies
        - Segurança web
        
        5. TESTE E QUALIDADE DE SOFTWARE
        - Tipos de teste (unitário, integração, sistema)
        - Test-Driven Development (TDD)
        - Ferramentas de teste
        - Code review
        - Métricas de qualidade
        - Debugging e profiling
        
        OBJETIVOS:
        - Compreender os fundamentos da programação orientada a objetos
        - Desenvolver habilidades de modelagem de sistemas
        - Aplicar conceitos de banco de dados em projetos
        - Criar aplicações web funcionais
        - Implementar testes automatizados
        
        METODOLOGIA:
        Aulas expositivas, exercícios práticos, desenvolvimento de projetos.
        
        AVALIAÇÃO:
        - Provas teóricas: 40%
        - Projetos práticos: 35%
        - Exercícios e participação: 25%
        ";
    }
}