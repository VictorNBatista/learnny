# API do Projeto Learnny

Esta API faz parte do projeto **Learrny**, uma plataforma de conexão entre alunos e professores particulares. A API oferece funcionalidades de **CRUD de usuários**, autenticação utilizando **Laravel Passport**, e gerenciamento de sessões com **tokens OAuth**.

## Tecnologias Utilizadas

- **PHP 8.x**: Linguagem principal do projeto.
- **Laravel 10.x**: Framework PHP utilizado para a construção da API.
- **Laravel Passport**: Gerenciamento de autenticação via OAuth 2.0.
- **MySQL**: Banco de dados relacional utilizado para armazenar informações dos usuários.
- **HeidiSQL**: Ferramenta de gerenciamento de banco de dados utilizada no ambiente de desenvolvimento.
- **Sodium**: Extensão de criptografia para PHP.

## Funcionalidades

- **CRUD de Usuários**
  - Criar, listar, atualizar e deletar usuários.
- **Autenticação**
  - Login e logout utilizando tokens OAuth 2.0 via Laravel Passport.
- **Listagem de Usuários**
  - Retorna uma lista de usuários cadastrados.

## Instalação e Configuração

### 1. Clonar o Repositório

Clone o repositório para seu ambiente local:

```bash
git clone https://github.com/VictorNBatista/learnny.git
```
### 2. Instalar Dependências

Execute o comando abaixo para instalar as dependências do projeto via Composer:

```bash
composer install
```

### 3. Configurar o Arquivo .env

Crie uma cópia do arquivo `.env.example` e renomeie para `.env`. Após isso, configure os seguintes parâmetros:

```bash
APP_NAME=Learrny
APP_ENV=local
APP_KEY=base64:gerado-pelo-comando-php-artisan-key-generate
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE="Nome Banco"
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

PASSPORT_PERSONAL_ACCESS_CLIENT_ID=1
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=token_secret
```

### 4. Gerar a Chave da Aplicação

Para gerar a chave do Laravel, execute o seguinte comando:

```bash
php artisan key:generate
```

### 5. Migrar as Tabelas do Banco de Dados

Rode as migrações para criar as tabelas necessárias no banco de dados:

```bash
php artisan migrate
```

### 6. Configurar o Passport

Execute os comandos abaixo para configurar o Laravel Passport:

#### 1. Gerar as suas chaves criptografia.
```bash
php artisan passport:keys
```

#### 2. Gerar o seu personal access.
```bash
php artisan passport:client --personal
```

Isso gerará os clients necessários para o OAuth.

### 7. Servir a Aplicação

Inicie o servidor local utilizando o comando abaixo:

```bash
php artisan serve
```

A API estará disponível em http://localhost:8000.

## Rotas da API

### Cadastro de Usuário

`POST /cadastrar`

Exemplo de body JSON:

```json
{
  "name": "User",
  "email": "user@email.com",
  "contact": "123456789",
  "password": "User@123",
  "password_confirmation": "User@123"
}
```

### Login

`POST /login`

Exemplo de body JSON:

```json
{
  "email": "user@email.com",
  "password": "User@123"
}
```

### Logout

`POST /logout `

O token deve ser enviado no cabeçalho `Authorization`:

```css
{
  Authorization: Bearer {seu_token}
}
```

### Listagem de Usuários

`GET /users `

Retorna a lista de todos os usuários cadastrados.

### Testes

Para rodar os testes, execute o seguinte comando:

```bash
php artisan test
```

---

# Aplicativo Web Learnny

Para acessar a aplicação, certifique-se do projeto estar dentro do diretório `C:\laragon\www` e que esteja com o laragon rodando o MySQL e Apache, juntamente com o BackEnd.

No navegador acesse a URL abaixo:
```URL
http://learnny.test/Learnny-front/index.html
```

Após estas etapas o sistema estará pronto para rodar.

### Contribuições

Sinta-se à vontade para contribuir com o projeto através de pull requests. Sugestões, melhorias e correções são sempre bem-vindas!

### Licença

Este projeto é licenciado sob a licença MIT.
