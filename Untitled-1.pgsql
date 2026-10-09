-- ============================================================
-- SCRIPT COMPLETO DE CRIAÇÃO DO BANCO DE DADOS: A TOCA DE LEITURA
-- SGDB: PostgreSQL
-- ============================================================

-- ------------------------------------------------------------
-- 0. APAGAR TABELAS EXISTENTES (Para evitar conflitos ao recriar)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS mensagens_contato CASCADE;
DROP TABLE IF EXISTS resenhas_videos CASCADE;
DROP TABLE IF EXISTS postagens_feed CASCADE;
DROP TABLE IF EXISTS historico_leitura CASCADE;
DROP TABLE IF EXISTS livros CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;


-- ------------------------------------------------------------
-- 2. TABELA DE LIVROS (CATÁLOGO / LOJA)
-- Usada em: compras.php, procurar.php
-- ------------------------------------------------------------
CREATE TABLE livros (
    id SERIAL PRIMARY KEY,                          -- Identificador único do livro
    titulo VARCHAR(200) NOT NULL,                   -- Título do livro
    autor VARCHAR(100) NOT NULL,                    -- Nome do autor
    editora VARCHAR(100),                           -- Nome da editora
    genero VARCHAR(50) NOT NULL,                    -- Gênero (Romance, Ficção, Suspense, Terror, etc.)
    preco DECIMAL(10, 2) NOT NULL DEFAULT 0.00,     -- Preço de venda
    estrelas INT DEFAULT 5,                         -- Avaliação em estrelas (1 a 5)
    capa_url VARCHAR(500),                          -- Nome do arquivo da imagem (ex: dom_casmurro.jpg)
    total_leitores INT DEFAULT 0                    -- Quantidade de leitores para estatísticas
);

-- ------------------------------------------------------------
-- 3. TABELA DE HISTÓRICO DE LEITURA (PROGRESSO INDIVIDUAL)
-- Usada em: atualizar_status.php
-- ------------------------------------------------------------

CREATE TABLE historico_leitura (
    id SERIAL PRIMARY KEY,                          -- Identificador único do registro
    usuario_id INT REFERENCES usuarios(id) ON DELETE CASCADE, -- ID do leitor (se deletar o usuário, deleta o histórico)
    livro_nome VARCHAR(200) NOT NULL,               -- Nome do livro que o usuário está lendo
    genero VARCHAR(50),                             -- Gênero literário da leitura atual
    tempo_leitura VARCHAR(50),                      -- Duração estimada (ex: 15 dias)
    paginas_lidas INT DEFAULT 0,                    -- Páginas que o leitor já concluiu
    total_paginas INT DEFAULT 0,                    -- Total de páginas da obra
    progresso_pct INT DEFAULT 0,                    -- Porcentagem calculada (0 a 100%)
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Data da última atualização
);

-- ------------------------------------------------------------
-- 4. TABELA DE POSTAGENS NO FEED (COMUNIDADE E OPINIÕES)
-- Usada em: interagir.php
-- ------------------------------------------------------------
CREATE TABLE postagens_feed (
    id SERIAL PRIMARY KEY,                          -- Identificador da publicação
    usuario_id INT REFERENCES usuarios(id) ON DELETE CASCADE, -- ID do autor do post
    livro_nome VARCHAR(200) NOT NULL,               -- Nome do livro comentado
    comentario TEXT NOT NULL,                       -- Texto da opinião / resenha rápida
    progresso INT DEFAULT 0,                        -- Porcentagem de leitura no momento do post
    aba_categoria VARCHAR(50) DEFAULT 'Geral',      -- Categoria de exibição (Geral, Amigos, Minhas, Seguindo)
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP   -- Data/hora de envio da postagem
);

-- ------------------------------------------------------------
-- 5. TABELA DE RESENHAS EM VÍDEO
-- Usada em: resenhas.php
-- ------------------------------------------------------------
CREATE TABLE resenhas_videos (
    id SERIAL PRIMARY KEY,                          -- Identificador da resenha
    titulo_livro VARCHAR(200) NOT NULL,             -- Nome do livro abordado no vídeo
    url_video VARCHAR(255) NOT NULL,                -- Link/URL externa do vídeo (ex: YouTube)
    resumo TEXT NOT NULL                            -- Resumo explicativo da análise
);

-- ------------------------------------------------------------
-- 6. TABELA DE MENSAGENS DE CONTATO (SUPORTE / ATENDIMENTO)
-- Usada em: contato.php
-- ------------------------------------------------------------
CREATE TABLE mensagens_contato (
    id SERIAL PRIMARY KEY,                          -- Identificador da mensagem
    nome VARCHAR(100) NOT NULL,                     -- Nome do cliente/visitante
    email VARCHAR(150) NOT NULL,                    -- E-mail para retorno de contato
    mensagem TEXT NOT NULL,                         -- Texto com a dúvida, crítica ou sugestão
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP   -- Data e hora do envio
);

-- 

