## 2. Como Executar o Projeto Localmente

Para rodar o projeto Toca de Leitura na sua máquina, é necessário configurar um ambiente local com PHP e o banco de dados PostgreSQL.

### 2.1. Pré-Requisitos
- PHP (v8.2 ou superior) habilitado com as extensões `pdo_pgsql` e `mbstring`.
- PostgreSQL instalado e em execução na porta 5432.
- Git instalado na máquina.

---

### 2.2. Instalação

#### Passo 1: Clonar o Repositório
Abra o seu terminal (ou Git Bash) e execute os comandos:

```bash
# Clonar o repositório do projeto
git clone https://github.com/beatrizbergamin003-afk/projetointegrador_tocaleitura.git

# Acessar a pasta do projeto
cd projetointegrador_tocaleitura

Passo 2: Configurar o Banco de Dados
O projeto conta com o arquivo dump_tocaLeitura.sql localizado na raiz do repositório, contendo a estrutura de tabelas e registros do projeto.

Siga os passos abaixo para recriar a base no PostgreSQL:

Abra o Terminal ou PowerShell.

Conecte-se ao PostgreSQL com o usuário administrador (postgres):

Bash
psql -U postgres
Se já existir o banco de dados de testes anteriores, remova-o e crie a base toca_leitura:

SQL
DROP DATABASE IF EXISTS toca_leitura;
CREATE DATABASE toca_leitura;
Crie o usuário raposa responsável pela aplicação e defina-o como proprietário do banco:

SQL
CREATE USER raposa WITH PASSWORD 'raposa123';
ALTER DATABASE toca_leitura OWNER TO raposa;
\q
Importe as tabelas e dados para o banco toca_leitura (garanta que o terminal esteja na raiz do projeto onde o arquivo .sql se encontra):

Bash
psql -U raposa -h localhost -d toca_leitura -f dump_tocaLeitura.sql
Passo 3: Configurar a Conexão no PHP
Abra o arquivo config/db.php no seu editor de código (ex: VS Code) e verifique se as configurações correspondem às credenciais abaixo:

PHP
$host     = "localhost";
$port     = "5432";
$dbname   = "toca_leitura";
$user     = "raposa";
$password = "raposa123";
Passo 4: Acessar a Aplicação Localmente
Com a raiz do projeto aberta no terminal (projetointegrador_tocaleitura), inicie o servidor embutido do PHP:

Bash
php -S localhost:8000
Abra o seu navegador de preferência e acesse a URL:http://localhost:8000 ou http://localhost:8000/paginas_site/index.php
