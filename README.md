# soat-fase5-report

Microsserviço responsável por gerar e entregar relatórios de análise em PDF. Recebe um `protocol_uuid`, consulta o serviço externo de trigger para obter os dados da análise e renderiza o relatório com DomPDF.

---

## Arquitetura

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
