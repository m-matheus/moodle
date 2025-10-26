# AI Question Generator for Moodle

Este plugin do Moodle permite aos professores fazer upload de planos de ensino em formato PDF e gerar automaticamente bancos de questões usando Inteligência Artificial.

## Características

- ✅ Upload de arquivos PDF com planos de ensino
- ✅ Análise automática de tópicos usando IA
- ✅ Geração de diferentes tipos de questões:
  - Múltipla escolha
  - Verdadeiro/Falso
  - Resposta curta
  - Dissertativa
- ✅ Interface de revisão para edição das questões geradas
- ✅ Integração completa com o banco de questões do Moodle
- ✅ Suporte à privacidade e GDPR
- ✅ Interface multilíngue (Português e Inglês)

## Instalação

1. Faça o download ou clone este repositório
2. Copie a pasta `aiquestiongen` para `moodle/local/`
3. Acesse a área de administração do Moodle
4. Clique em "Notificações" para instalar o plugin
5. Configure as chaves da API de IA em "Administração do site > Plugins > Plugins locais > AI Question Generator"

## Configuração

### 1. API de IA

Acesse **Administração do site > Plugins > Plugins locais > AI Question Generator** e configure:

- **Chave da API**: Sua chave da API do serviço de IA (OpenAI, Claude, etc.)
- **Endpoint da API**: URL do endpoint da API (padrão: https://api.openai.com/v1/chat/completions)
- **Questões padrão por tópico**: Número padrão de questões a serem geradas por tópico (padrão: 5)
- **Tipos de questões**: Selecione quais tipos de questões devem ser geradas

### 2. Permissões

O plugin define três capacidades:

- `local/aiquestiongen:view` - Visualizar o plugin (professores, editores, gestores)
- `local/aiquestiongen:generate` - Gerar questões (professores, editores, gestores)
- `local/aiquestiongen:manage` - Gerenciar configurações (apenas gestores)

## Como Usar

### 1. Acesso ao Plugin

Em qualquer curso, você encontrará o link "Gerador de Questões com IA" no menu de navegação do curso ou na administração do curso.

### 2. Upload do Plano de Ensino

1. Clique em "Selecionar arquivo PDF"
2. Escolha seu plano de ensino em formato PDF
3. Clique em "Analisar Tópicos"

### 3. Revisão de Tópicos

Após o processamento, você verá:
- Lista de tópicos identificados automaticamente
- Opção para ajustar o número de questões por tópico
- Seleção dos tipos de questões a serem geradas

### 4. Geração de Questões

1. Configure os tópicos conforme desejado
2. Clique em "Gerar Questões"
3. Aguarde o processamento

### 5. Revisão das Questões

Na tela de revisão você pode:
- Visualizar todas as questões geradas
- Selecionar quais questões salvar
- Ver respostas e feedback de cada questão

### 6. Salvamento no Banco de Questões

1. Selecione as questões desejadas
2. Clique em "Salvar no Banco de Questões"
3. As questões serão salvas na categoria "IA - Questões Geradas"

## Estrutura do Banco de Dados

O plugin cria três tabelas:

### `local_aiquestiongen_jobs`
Rastreia trabalhos de processamento de PDF e geração de questões.

### `local_aiquestiongen_topics`
Armazena tópicos identificados nos documentos curriculares.

### `local_aiquestiongen_questions`
Armazena questões geradas antes de serem salvas no banco de questões.

## Arquitetura do Plugin

### Classes Principais

- **`pdf_parser`**: Extração de texto de arquivos PDF
- **`ai_service`**: Integração com APIs de IA
- **`question_bank_integration`**: Integração com o sistema de questões do Moodle
- **`privacy\provider`**: Implementação da API de privacidade

### Fluxo de Trabalho

1. **Upload**: Professor faz upload do PDF
2. **Extração**: Texto é extraído do PDF
3. **Análise**: IA analisa o texto e identifica tópicos
4. **Configuração**: Professor configura quantas questões gerar por tópico
5. **Geração**: IA gera questões baseadas nos tópicos
6. **Revisão**: Professor revisa e seleciona questões
7. **Salvamento**: Questões são salvas no banco de questões do Moodle

## Desenvolvimento

### Requisitos

- Moodle 4.1+
- PHP 7.4+
- Extensão cURL para integração com APIs de IA

### Estrutura de Arquivos

```
local/aiquestiongen/
├── version.php              # Metadados do plugin
├── settings.php             # Página de configurações
├── lib.php                  # Funções e callbacks
├── index.php                # Página principal
├── lang/                    # Arquivos de idioma
│   ├── en/
│   └── pt_br/
├── classes/                 # Classes do plugin
│   ├── pdf_parser.php
│   ├── ai_service.php
│   ├── question_bank_integration.php
│   └── privacy/provider.php
└── db/                      # Definições do banco de dados
    ├── access.php           # Capacidades
    └── install.xml          # Schema do banco
```

### Extensões Futuras

- Suporte a mais formatos de arquivo (Word, HTML)
- Integração com mais provedores de IA
- Análise de imagens em PDFs
- Geração de questões com imagens
- Exportação para diferentes formatos
- Dashboard de estatísticas

## Licença

Este plugin é licenciado sob a GNU General Public License v3.0, a mesma licença do Moodle.

## Suporte

Para suporte e relatórios de bugs, por favor use as issues do repositório.

## Contribuições

Contribuições são bem-vindas! Por favor:

1. Faça um fork do repositório
2. Crie uma branch para sua feature
3. Faça commit de suas alterações
4. Envie um pull request

## Autor

Desenvolvido como parte de um projeto de TCC (Trabalho de Conclusão de Curso).

---

**Nota**: Este plugin está em desenvolvimento ativo. Algumas funcionalidades podem estar em fase de teste. Use em ambiente de produção com cautela.