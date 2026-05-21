# soat-fase5-report

Microsserviço responsável por gerar e entregar relatórios de análise em PDF. Recebe um `protocol_uuid`, consulta o serviço externo de trigger para obter os dados da análise e renderiza o relatório com DomPDF.


## Alunos

| Aluno | RM | Discord | LinkedIn |
|---|---|---|---|
| Felipe | 365154 | felipeoli7eira | [@felipeoli7eira](https://www.linkedin.com/in/felipeoli7eira) |
| Nicolas | 365746 | nic_hcm | [@Nicolas Martins](https://www.linkedin.com/in/nicolas-hcm) |
| William | 365973 | wllsistemas | [@William Francisco Leite](https://www.linkedin.com/in/williamfranciscoleite) |
---

## Descrição do Problema

O sistema tem como objetivo automatizar a análise de diagramas de arquitetura de software por meio de Inteligência Artificial. Equipes de engenharia submetem diagramas (JPG, JPEG, PNG ou PDF) e recebem, de forma assíncrona, um relatório com **componentes identificados**, **riscos** e **recomendações** de melhoria — eliminando a necessidade de revisão manual.

---

## Arquitetura Proposta

O sistema é composto por cinco microsserviços interligados:

| Serviço | Papel |
|---|---|
| **BFF** | Ponto de entrada unificado; orquestra chamadas aos serviços internos |
| **upload-service** | Recebe diagramas, armazena no Amazon S3, publica na fila `protocols` |
| **trigger-service** | Consome a fila, aciona a IA e persiste resultados no PostgreSQL |
| **report-service** *(este serviço)* | Consulta resultados no trigger-service e gera relatório em PDF |
| **RabbitMQ** | Broker de mensagens para comunicação assíncrona |

> Diagrama interativo: [FIAP - HACKATON FASE 5](https://www.tldraw.com/f/CPYtIC_xwtcfbSCH4vgkT?d=v567.4.3513.1667.page)

### Arquitetura Interna

O projeto segue **Clean Architecture** com separação clara entre as camadas:

```
app/
├── Domain/          # Entidades, enums e contratos (interfaces)
├── Application/     # Use Cases com Input/Output DTOs
├── Infrastructure/  # Implementações concretas (HTTP gateway, renderer PDF)
└── Http/            # Controllers e rotas
```

A única dependência externa é o **Trigger Service**, acessado via HTTP. Em testes, essa dependência é substituída por um mock.

---

## Fluxo da Solução

**Envio para análise:**
1. Usuário envia diagrama via `POST /api/upload` no BFF
2. BFF repassa ao **upload-service**, que armazena no Amazon S3
3. **upload-service** publica `{ protocol_uuid, file_url, ... }` na fila `protocols` do RabbitMQ
4. **trigger-service** consome a mensagem e aciona o **Analysis Service (IA)**
5. IA processa e publica o resultado na fila `analysis_response`
6. **trigger-service** persiste o resultado no PostgreSQL (`SUCESSO` ou `ERRO`)

**Consulta do resultado:**
1. Usuário consulta status via `GET /api/status/{uuid}` no BFF → **report-service** → `GET /status/{uuid}` no trigger-service
2. Usuário solicita relatório via `GET /api/report/{uuid}` no BFF → **report-service** → `GET /data/{uuid}` no trigger-service → gera PDF

---

## Segurança

### Requisitos básicos adotados

- **Credenciais via variáveis de ambiente**: URL e timeout do trigger-service são injetados via `.env`, sem valores hardcoded
- **Rede Docker isolada** (`soat-net`): este serviço não é acessível externamente — somente o BFF se comunica com ele

### Validação e tratamento de entradas não confiáveis

- `protocol_uuid` validado como UUID válido antes de qualquer chamada ao trigger-service
- Parâmetro `?format` tratado com fallback seguro para `pdf` quando o valor informado é inválido
- Respostas do trigger-service são validadas antes de renderizar o PDF — campos ausentes ou malformados não causam erro não tratado

### Uso controlado do modelo de IA

- Este serviço não se comunica com a IA diretamente — consome apenas dados **já processados e persistidos** pelo trigger-service
- O schema dos dados recebidos (`components`, `risks`, `recommendations`) é esperado e validado antes da renderização do PDF
- O conteúdo do relatório reflete exclusivamente o que a IA retornou; nenhuma inferência adicional é feita aqui

### Tratamento de falhas e comportamentos inesperados da IA

- Quando a IA retornou erro (`status: ERRO`), o trigger-service expõe esse status e este serviço o repassa ao cliente sem tentar gerar um relatório inválido
- Timeout configurável (`TRIGGER_SERVICE_TIMEOUT`) evita que indisponibilidade do trigger-service bloqueie indefinidamente a geração do PDF
- Erros `502` são retornados ao cliente quando o trigger-service está indisponível, sem expor detalhes internos

### Comunicação entre serviços

- **Rede Docker interna** (`soat-net`): comunicação com o trigger-service trafega apenas na rede interna
- Timeout HTTP configurável para chamadas ao trigger-service
- Dependência única e bem definida (apenas o trigger-service), reduzindo a superfície de ataque

### Riscos e limitações conhecidos

| Risco | Impacto | Mitigação atual |
|---|---|---|
| Indisponibilidade do trigger-service bloqueia geração do PDF | Cliente recebe erro enquanto o serviço upstream estiver fora | Timeout configurável (`TRIGGER_SERVICE_TIMEOUT`); retorna `502` com mensagem clara |
| Dados da análise com schema inesperado podem causar falha na renderização | PDF gerado incompleto ou com erro | Schema de entrada validado antes de renderizar; fallback tratado pela camada de Application |

---

## Pré-requisitos

- Docker e Docker Compose

---

## Rodando o projeto

```bash
docker compose up -d
```

A aplicação fica disponível em `http://localhost:8000`.

---

## API

| Método | Rota                        | Descrição                          |
|--------|-----------------------------|------------------------------------|
| GET    | `/api/ping`                 | Health check                       |
| GET    | `/api/status/{protocol_uuid}` | Consulta o status da análise     |
| GET    | `/api/report/{protocol_uuid}` | Gera e retorna o relatório em PDF |

### GET `/api/ping`

```json
{ "err": false, "msg": "pong", "service": "soat-report" }
```

### GET `/api/status/{protocol_uuid}`

**Sucesso (200):**
```json
{ "err": false, "protocolUuid": "uuid", "status": "completed" }
```

Status possíveis: `RECEBIDO` | `EM_PROCESSAMENTO` | `SUCESSO` | `ERRO`

**Erro (502):** gateway indisponível.
```json
{ "err": true, "msg": "..." }
```

### GET `/api/report/{protocol_uuid}`

Retorna o relatório como arquivo PDF (`application/pdf`) com o header:
```
Content-Disposition: attachment; filename="report-{uuid}.pdf"
```

Aceita o query param `?format=pdf` (padrão). Formatos inválidos fazem fallback para PDF.

**Erro (502):** gateway indisponível.

---

## Testes

### Pré-requisitos

- Docker e Docker Compose
- Container `soat-report` em execução (`docker compose up -d`)

### 1. Instalar dependências (incluindo dev)

```bash
docker exec soat-report composer install
```

> Necessário apenas na primeira vez ou após alterações no `composer.json`.

### 2. Executar os testes

```bash
docker exec soat-report vendor/bin/phpunit
```

### 3. Executar com relatório de cobertura HTML

```bash
docker exec soat-report vendor/bin/phpunit --coverage-html var/coverage/html
```

O relatório estará disponível em `application/var/coverage/html/index.html`.

### Estrutura dos testes

| Suite   | Local                              | O que testa                                  |
|---------|------------------------------------|----------------------------------------------|
| Unit    | `tests/Unit/Domain/`               | Entidades e enums do domínio                 |
| Feature | `tests/Feature/`                   | Endpoints HTTP da controller (integração)    |

### Cobertura atual

| Métrica  | Resultado |
|----------|-----------|
| Classes  | 80%       |
| Métodos  | 86.67%    |
| Linhas   | 91.38%    |

---

## CI/CD

O pipeline de testes roda automaticamente em pull requests para `main` via GitHub Actions (`.github/workflows/tests.yml`).

O build falha se a cobertura de linhas cair abaixo de **85%**.

---

## Variáveis de ambiente relevantes

| Variável                        | Padrão                    | Descrição                        |
|---------------------------------|---------------------------|----------------------------------|
| `TRIGGER_SERVICE_BASE_URL`      | `http://trigger-service`  | URL base do serviço de trigger   |
| `TRIGGER_SERVICE_TIMEOUT`       | `10`                      | Timeout em segundos              |
| `APP_NAME`                      | —                         | Nome exibido no `/api/ping`      |
