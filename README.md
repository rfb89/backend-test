# Back End Test Project <img src="https://coderockr.com/assets/images/coderockr.svg" align="right" height="50px" />

Sistema de cadastro de Usuário e Investimento via API.

## Tecnologias utilizadas

-   PHP 8.1.2
-   Laravel 9.11
-   Mariadb DB 15.1

## Instalação

1. Após clonar o projeto via Git, instale as dependências via composer:

```
composer install
```

2. Criar um banco de dados.

3. Copiar o arquivo `.env.example` para `.env` e definir as credências de acesso ao banco de dados:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

4. Executar comando de configuração da chave do Laravel:

```
php artisan key:generate
```

5. Executar comando de importação das tabela:

```
php artisan migrate
```

6. **Opcional:** Executar comando para criar dados fakes nas tabelas de Usuários e Investimentos:

```
php artisan db:seed
```

## Documentação API

**Usuários**

-   Cadastra um novo usuário: `POST api/user`
-   Excluir um usuário: `DELETE api/user/{id}`
-   Lista os dados de um usuário: `GET api/user/{id}`
-   Edita os dados de um usuário: `PUT api/user/{id}`
-   Lista todos os usuários: `GET api/users`

**Investimentos**

-   Cria um novo investimento para um usuário: `POST api/investment/create`
    -   Dados necessários:
    ```
    user_id: Id do Usuário
    amount: Valor de investimento
    date: Data inicial do investimento
    ```
-   Busca os dados de um investimento: `GET api/investment/{id}`
-   Finaliza o investimento: `PUT api/investment/{id}/withdrawal`
    -   Dado opcional:
    ```
    date: Data final do investimento
    ```
-   Busca todos os investimentos de um usuário: `GET api/investments/{user_id}?page={page}`
