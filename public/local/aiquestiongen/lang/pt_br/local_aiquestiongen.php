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
 * Strings em português brasileiro.
 *
 * @package     local_aiquestiongen
 * @copyright   2025 TCC Project
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Gerador de Questões com IA';
$string['aiquestiongen:view'] = 'Visualizar Gerador de Questões com IA';
$string['aiquestiongen:generate'] = 'Gerar questões usando IA';
$string['aiquestiongen:manage'] = 'Gerenciar configurações do Gerador de Questões com IA';

// Interface principal
$string['uploadcurriculum'] = 'Enviar Plano de Ensino';
$string['uploadcurriculum_help'] = 'Envie seu plano de ensino em formato PDF para gerar automaticamente bancos de questões baseados nos tópicos do currículo.';
$string['selectfile'] = 'Selecionar arquivo PDF';
$string['processingfile'] = 'Processando arquivo...';
$string['analyzetopics'] = 'Analisar Tópicos';
$string['generatequestions'] = 'Gerar Questões';
$string['reviewquestions'] = 'Revisar Questões Geradas';
$string['savequestions'] = 'Salvar no Banco de Questões';

// Análise de tópicos
$string['topicsidentified'] = 'Tópicos Identificados';
$string['notopicsfound'] = 'Não foi possível identificar tópicos no documento enviado. Verifique o conteúdo do arquivo.';
$string['topic'] = 'Tópico';
$string['description'] = 'Descrição';
$string['questioncount'] = 'Número de Questões';
$string['questiontypes'] = 'Tipos de Questões';

// Tipos de questões
$string['multichoice'] = 'Múltipla Escolha';
$string['truefalse'] = 'Verdadeiro/Falso';
$string['shortanswer'] = 'Resposta Curta';
$string['essay'] = 'Dissertativa';

// Configurações
$string['settings'] = 'Configurações do Gerador de Questões com IA';
$string['apikey'] = 'Chave da API de IA';
$string['apikey_desc'] = 'Chave da API para o serviço de IA (OpenAI, Claude, etc.)';
$string['apiendpoint'] = 'Endpoint da API';
$string['apiendpoint_desc'] = 'URL do endpoint da API para o serviço de IA';
$string['defaultquestioncount'] = 'Questões padrão por tópico';
$string['defaultquestioncount_desc'] = 'Número padrão de questões a serem geradas para cada tópico identificado';
$string['questiontypes_desc'] = 'Selecione quais tipos de questões devem ser geradas';

// Mensagens
$string['success'] = 'Sucesso';
$string['error'] = 'Erro';
$string['fileprocessed'] = 'Arquivo processado com sucesso';
$string['questionsgenerated'] = '{$a} questões geradas com sucesso';
$string['questionssaved'] = 'Questões salvas no banco de questões com sucesso';
$string['invalidfile'] = 'Formato de arquivo inválido. Por favor, envie um arquivo PDF.';
$string['nofileselected'] = 'Por favor, selecione um arquivo para enviar.';
$string['apierror'] = 'Erro ao comunicar com o serviço de IA: {$a}';
$string['processingerror'] = 'Erro ao processar o documento: {$a}';

// Privacidade
$string['privacy:metadata'] = 'O plugin Gerador de Questões com IA não armazena dados pessoais.';

// Strings adicionais
$string['recent_jobs'] = 'Trabalhos Recentes';
$string['filename'] = 'Nome do Arquivo';
$string['status'] = 'Status';
$string['actions'] = 'Ações';
$string['view_topics'] = 'Ver Tópicos';
$string['processing'] = 'Processando';
$string['completed'] = 'Concluído';
$string['failed'] = 'Falhou';
$string['pending'] = 'Pendente';

// Tarefas
$string['task_process_pending_jobs'] = 'Processar trabalhos pendentes de geração de questões com IA';
$string['task_cleanup_old_jobs'] = 'Limpar trabalhos antigos de geração de questões com IA';