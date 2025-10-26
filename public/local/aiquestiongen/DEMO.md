# Demonstração do Plugin AI Question Generator

## Como Testar o Plugin

### 1. Instalação e Configuração

1. **Acesse as configurações do plugin**:
   - Vá para Administração do site > Plugins > Plugins locais > AI Question Generator

2. **Configure a API de IA**:
   - Se você tiver uma chave da OpenAI, insira-a no campo "Chave da API"
   - Deixe o endpoint padrão ou ajuste conforme necessário
   - Se não tiver uma chave da API, o plugin funcionará com dados de demonstração

### 2. Testando com Dados de Demonstração

O plugin foi desenvolvido para funcionar mesmo sem configuração de API, usando dados de amostra:

1. **Acesse um curso qualquer**
2. **Procure por "Gerador de Questões com IA"** no menu de navegação do curso
3. **Faça upload de qualquer arquivo PDF** (pode ser qualquer PDF, o conteúdo não importa para a demonstração)
4. **Siga o fluxo**:
   - Upload do arquivo
   - Visualização dos tópicos identificados
   - Configuração do número de questões
   - Geração das questões
   - Revisão e seleção
   - Salvamento no banco de questões

### 3. Dados de Demonstração

Quando o plugin não consegue conectar com a API de IA, ele utiliza dados simulados:

**Tópicos Identificados**:
- Introdução à Programação
- Orientação a Objetos  
- Banco de Dados
- Desenvolvimento Web
- Teste e Qualidade

**Tipos de Questões Geradas**:
- Múltipla escolha (com 4 alternativas)
- Verdadeiro/Falso
- Resposta curta
- Dissertativa

### 4. Verificando as Questões no Banco

Após salvar as questões:

1. **Acesse o banco de questões do curso**
2. **Procure pela categoria "IA - Questões Geradas"**
3. **Visualize as questões criadas**
4. **Use-as em quizzes normalmente**

### 5. Recursos Implementados

✅ **Interface completa de upload**
✅ **Análise de tópicos** (simulada ou via IA)
✅ **Geração de múltiplos tipos de questões**
✅ **Interface de revisão** com seleção de questões
✅ **Integração com banco de questões do Moodle**
✅ **Multilíngue** (Português e Inglês)
✅ **Sistema de permissões**
✅ **Privacidade GDPR**
✅ **Tarefas agendadas** para processamento em background
✅ **Limpeza automática** de dados antigos

### 6. Estrutura Técnica

**Arquitetura modular**:
- `pdf_parser.php` - Extração de texto de PDFs
- `ai_service.php` - Integração com APIs de IA
- `question_bank_integration.php` - Salvamento no banco de questões
- `privacy/provider.php` - Conformidade GDPR

**Banco de dados**:
- 3 tabelas para gerenciar o fluxo completo
- Relacionamentos adequados com tabelas do Moodle
- Campos para rastreamento e auditoria

**Integração**:
- Links no menu de navegação do curso
- Integração com sistema de permissões
- Uso das APIs padrão do Moodle

### 7. Cenário de Uso Real

Em um ambiente de produção com API configurada:

1. **Professor faz upload do plano de ensino em PDF**
2. **IA analisa o documento e extrai os tópicos principais**
3. **Sistema gera questões variadas baseadas em cada tópico**
4. **Professor revisa, edita se necessário, e seleciona as questões**
5. **Questões são salvas no banco e podem ser usadas em quizzes**

### 8. Benefícios para o TCC

Este plugin demonstra:

- **Aplicação prática de IA na educação**
- **Integração profunda com o Moodle**
- **Código bem estruturado e documentado**
- **Seguimento das melhores práticas do Moodle**
- **Interface de usuário intuitiva**
- **Considerações de privacidade e segurança**
- **Escalabilidade e manutenibilidade**

### 9. Screenshots e Documentação

O plugin inclui:
- README.md completo
- Documentação da API
- Comentários extensivos no código
- Strings de idioma bem organizadas
- Estrutura de arquivos padrão do Moodle

### 10. Extensões Futuras

O plugin foi desenvolvido de forma extensível para permitir:
- Suporte a mais formatos de arquivo
- Integração com outros provedores de IA
- Análise de imagens em documentos
- Dashboard de estatísticas
- Exportação para diferentes formatos

---

## Conclusão

Este plugin representa uma implementação completa e profissional de uma ferramenta educacional usando IA, seguindo todos os padrões e melhores práticas do Moodle. É um exemplo excelente de como a tecnologia pode ser aplicada para melhorar a experiência educacional.