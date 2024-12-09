# SingleSoftware Swagger Generator

**Uma biblioteca para gerar documentação Swagger automaticamente para APIs Laravel, com base em FormRequests.**

---

## Instalação

Instale a biblioteca usando o Composer:

```bash
composer require single_software/singles_swagger
```

---

## Funcionalidades

### Rotas de Documentação

Após instalar a biblioteca, você poderá acessar a documentação Swagger gerada automaticamente através das seguintes rotas:

- `/api-doc/{routeName}`  
  Exemplo: `/api-doc/api.php`

  Gera a documentação Swagger para um arquivo de rotas específico.

- `/api-doc`  
  Lista todas as rotas disponíveis para documentação.  
  Exemplo de saída:
  ```json
  [
      "api.php",
      "web.php",
      "api.json"
  ]
  ```

- `/api-doc/api.json`  
  Disponibiliza a documentação Swagger no formato JSON para integração com ferramentas externas.

---

### Comando Artisan

A biblioteca fornece um comando Artisan para gerar a documentação Swagger manualmente:

```bash
php artisan generate:swagger {routeFile} {--prefix=} {--tenants=}
```

#### Parâmetros do Comando:

- **`{routeFile}`**  
  O nome do arquivo de rotas para o qual a documentação será gerada.  
  Exemplo: `api.php`

- **`{--prefix=}`**  
  Um prefixo para as rotas que deve ser considerado na documentação.  
  Exemplo: `--prefix=api/v1`

- **`{--tenants=}`**  
  Indica que as rotas pertencem a um sistema com múltiplos tenants (multitenancy).  
  Exemplo: `--tenants=true`

---

## Exemplos de Uso

### 1. Gerar Documentação para um Arquivo de Rotas Específico
Acesse a rota:
```
http://sua-aplicacao.test/api-doc/api.php
```

Ou execute o comando Artisan:
```bash
php artisan generate:swagger api.php
```

### 2. Listar Todas as Rotas Disponíveis
Acesse:
```
http://sua-aplicacao.test/api-doc
```

### 3. Exportar Documentação como JSON
Acesse:
```
http://sua-aplicacao.test/api-doc/api.json
```

---

## Benefícios

- **Automação Completa**: Geração automática de documentação com base em validações definidas em `FormRequest`.
- **Flexibilidade**: Suporte a sistemas com múltiplos tenants e rotas com prefixos personalizados.
- **Integração Simples**: Use o formato JSON para integrar a documentação com ferramentas externas, como Swagger UI.

---

## Requisitos

- Laravel 8 ou superior.
- PHP 7.4 ou superior.

---

## Sobre

Esta biblioteca foi desenvolvida pela **SingleSoftware** para facilitar a criação de documentações Swagger em projetos Laravel.