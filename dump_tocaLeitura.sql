-- ============================================================================
-- DUMP COMPLETO DO BANCO DE DADOS: A TOCA DE LEITURA
-- SGDB: PostgreSQL
-- ============================================================================

BEGIN;

-- ----------------------------------------------------------------------------
-- 1. LIMPEZA DE TABELAS EXISTENTES (DROP TABLES)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS itens_pedido CASCADE;
DROP TABLE IF EXISTS pedidos CASCADE;
DROP TABLE IF EXISTS mensagens_contato CASCADE;
DROP TABLE IF EXISTS logs_acesso CASCADE;
DROP TABLE IF EXISTS postagens_feed CASCADE;
DROP TABLE IF EXISTS historico_leitura CASCADE;
DROP TABLE IF EXISTS resenhas_videos CASCADE;
DROP TABLE IF EXISTS livros CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;

-- ----------------------------------------------------------------------------
-- 2. CRIAÇÃO DAS TABELAS (DDL)
-- ----------------------------------------------------------------------------

-- Tabela: USUARIOS
CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela: LIVROS
CREATE TABLE livros (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL UNIQUE,
    autor VARCHAR(100) NOT NULL,
    editora VARCHAR(100),
    genero VARCHAR(50) NOT NULL,
    preco DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    estrelas NUMERIC(2, 1) DEFAULT 5.0,
    capa_url TEXT,
    total_leitores INT DEFAULT 0
);

-- Tabela: HISTORICO_LEITURA
CREATE TABLE historico_leitura (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    livro_nome VARCHAR(200) NOT NULL,
    genero VARCHAR(50),
    tempo_leitura VARCHAR(50),
    paginas_lidas INT DEFAULT 0,
    total_paginas INT DEFAULT 0,
    progresso_pct INT DEFAULT 0,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela: POSTAGENS_FEED
CREATE TABLE postagens_feed (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    livro_nome VARCHAR(200) NOT NULL,
    comentario TEXT NOT NULL,
    progresso INT DEFAULT 0,
    aba_categoria VARCHAR(50) DEFAULT 'Geral',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela: RESENHAS_VIDEOS
CREATE TABLE resenhas_videos (
    id SERIAL PRIMARY KEY,
    titulo_livro VARCHAR(200) NOT NULL,
    url_video VARCHAR(500) NOT NULL,
    resumo TEXT
);

-- Tabela: MENSAGENS_CONTATO
CREATE TABLE mensagens_contato (
    id SERIAL PRIMARY KEY,
    usuario_id INT REFERENCES usuarios(id) ON DELETE SET NULL,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    assunto VARCHAR(255),
    mensagem TEXT NOT NULL,
    resposta_admin TEXT,
    data_resposta TIMESTAMP,
    status VARCHAR(20) DEFAULT 'Pendente',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela: LOGS_ACESSO
CREATE TABLE logs_acesso (
    id SERIAL PRIMARY KEY,
    usuario_id INT REFERENCES usuarios(id) ON DELETE CASCADE,
    email_tentativa VARCHAR(150) NOT NULL,
    ip_origem VARCHAR(45),
    sucesso BOOLEAN DEFAULT TRUE,
    data_acesso TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela: PEDIDOS
CREATE TABLE pedidos (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    total DECIMAL(10, 2) NOT NULL,
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'Concluído'
);

-- Tabela: ITENS_PEDIDO
CREATE TABLE itens_pedido (
    id SERIAL PRIMARY KEY,
    pedido_id INT NOT NULL REFERENCES pedidos(id) ON DELETE CASCADE,
    livro_id INT REFERENCES livros(id) ON DELETE SET NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10, 2) NOT NULL
);

-- ----------------------------------------------------------------------------
-- 3. INSERÇÃO DE DADOS INICIAIS (DML)
-- ----------------------------------------------------------------------------

-- Cadastro do Usuário Administrador
INSERT INTO usuarios (nome, email, senha, is_admin) VALUES
('Administrador', 'admin@gmail.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1q9lA6n/K3d5D2oXJ1X2k8b3H1f7H4m', TRUE);

-- Inserção do Catálogo de Livros (com avaliações e capas padronizadas)
INSERT INTO livros (titulo, autor, editora, genero, preco, estrelas, capa_url, total_leitores) VALUES
-- Romance
('Táticas do Amor', 'Sarah Adams', 'Intrínseca', 'Romance', 49.90, 4.6, 'https://imgs.search.brave.com/iYJ2VMRB1ZtWRAy0BCkoqtPMHL_mB6ykEjii3b4f8dA/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tLm1l/ZGlhLWFtYXpvbi5j/b20vaW1hZ2VzL0kv/NDFlSjBsU2d0MUwu/anBn', 2300),
('Parte do Seu Mundo', 'Abby Jimenez', 'Arqueiro', 'Romance', 54.90, 4.7, 'https://imgs.search.brave.com/1MH-6EHn8_m7ZcaDsn8iFU7IH2Y0Xdf3J7EqDdwzPCI/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tLm1l/ZGlhLWFtYXpvbi5j/b20vaW1hZ2VzL0kv/NDFsQ2wxT1N6Y0wu/anBn', 1850),
('Nem Te Conto', 'Emily Henry', 'Record', 'Romance', 59.90, 4.7, 'https://imgs.search.brave.com/iEsbhlnrzm7VpPfwruflFaP3yMN5O_3ZZqHuQ8yQP2w/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tLm1l/ZGlhLWFtYXpvbi5j/b20vaW1hZ2VzL0kv/NDFvWkVPc0FqVEwu/anBn', 3100),
('Binding 13', 'Chloe Walsh', 'Bloom Brasil', 'Romance', 69.90, 4.7, 'https://imgs.search.brave.com/EYFO9bdte0fS09UyULu3nS25Q5-SN6t1l6fJv0lKMSs/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9odHRw/Mi5tbHN0YXRpYy5j/b20vRF9OUV9OUF85/MDc3ODMtTUxBODIx/NzM2MTg5NjJfMDIy/MDI1LU8ud2VicA', 4100),
('Apostando no Amor', 'Lynn Painter', 'Intrínseca', 'Romance', 49.90, 4.8, 'https://imgs.search.brave.com/aCnB8e1DIMI7usc4bm--iZbOP84euPTyh1xJMrF3Kro/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tLm1l/ZGlhLWFtYXpvbi5j/b20vaW1hZ2VzL0kv/NDFndHNiTWx1Y0wu/anBn', 2750),

-- Ficção
('Harry Potter: A Pedra Filosofal', 'J.K. Rowling', 'Rocco', 'Ficção', 44.90, 4.9, 'https://imgs.search.brave.com/HPxalIyMin-mEPG9PbUG-67cz4xo3X9qcbGL5qJxzKA/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tLm1l/ZGlhLWFtYXpvbi5j/b20vaW1hZ2VzL0kv/MzFhTERkNzlSeEwu/anBn', 5200),
('Maze Runner: Correr ou Morrer', 'James Dashner', 'V&R', 'Ficção', 49.90, 4.6, 'https://imgs.search.brave.com/drpKx0-3Td1gRX50HuHaAMWJY4GHLUJcLn9jtbpu-ls/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWFn/ZW5zLmVsaXZyb3Mu/aW5mby9KYW1lcy1E/YXNobmVyL0JhaXhh/ci1MaXZyby1Db3Jy/ZXItT3UtTW9ycmVy/LU1hemUtUnVubmVy/LVZvbC0xLUphbWVz/LURhc2huZXItRW0t/RXB1Yi1QZGYtTW9i/aS1PdS1MZXItT25s/aW5lX21lZGl1bS5q/cGc', 2800),
('Duna', 'Frank Herbert', 'Aleph', 'Ficção', 79.90, 4.8, 'https://imgs.search.brave.com/b3agjkopmdSZBgHpAlfVl4c-ylE8ujSBquHF1SdrKRg/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9saXZy/YXJpYXB1YmxpY2Eu/Y29tLmJyL2NhcGEv/ZHVuYS1jcm9uaWNh/cy1kZS1kdW5hLWxp/dnJvLTEtZnJhbmst/aGVyYmVydC1wZGYt/QjAxNUVFNUpYNC53/ZWJw', 3900),
('Percy Jackson e os Olimpianos: O Ladrão de Raios', 'Rick Riordan', 'Intrínseca', 'Ficção', 49.90, 4.8, 'https://imgs.search.brave.com/ONzy2bP92SH6iNwwqOErpcZUHWMvrQG166lKcI-WY-c/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9odHRw/Mi5tbHN0YXRpYy5j/b20vRF9OUV9OUF84/MTUxMDctTUxCNzQw/MDQ5NTA3ODhfMDEy/MDI0LVcud2VicA', 4600),
('As Crônicas de Nárnia', 'C.S. Lewis', 'HarperCollins', 'Ficção', 69.90, 4.8, 'https://imgs.search.brave.com/w5yn26P00Lt6fjqPU4xp6t8V-FuuZ2_9hPZXczXihss/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tLm1l/ZGlhLWFtYXpvbi5j/b20vaW1hZ2VzL0kv/NTFTMkVRWHd3ekwu/anBn', 4100),

-- Fantasia
('Corte de Espinhos e Rosas', 'Sarah J. Maas', 'Galera Record', 'Fantasia', 59.90, 4.8, 'https://imgs.search.brave.com/OgaikIQOmKap-UXmPvJq6MkzzzFT-iuiiNbJoLWQYSg/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9hY290/YXJicmFzaWwuY29t/LmJyL3dwLWNvbnRl/bnQvdXBsb2Fkcy8y/MDIxLzA3L2NvcnRl/LWRlLWVzcGluaG9z/LWUtcm9zYXMtY2Fw/YS02OTF4MTAyNC5q/cGc', 4800),
('Era uma Vez um Coração Partido', 'Stephanie Garber', 'Gutenberg', 'Fantasia', 54.90, 4.8, 'https://imgs.search.brave.com/QW1xasUd-dCHwbLczZWbHOtY3WogdjhTQGBt-Zuizy4/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9ncnVw/b2F1dGVudGljYS5m/MWNkbi5jb20uYnIv/dmlldy8xNzYvMjQ0/L2ZhbHNlL3RydWUv/OTI0LmpwZw', 3200),
('Estilhaça-me', 'Tahereh Mafi', 'Gutenberg', 'Fantasia', 49.90, 4.5, 'https://imgs.search.brave.com/iI2zLp891h0XB3-3lxSq43tEFi0pRYsCqlIeW-kbjko/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWFn/ZXMtbmEuc3NsLWlt/YWdlcy1hbWF6b24u/Y29tL2ltYWdlcy9J/LzcxM3Z3RjYxbW5M/LmpwZw', 3900),
('Assistente do Vilão', 'Hannah Nicole Maehrer', 'Alt', 'Fantasia', 59.90, 4.8, 'https://imgs.search.brave.com/sU6ww6pYTX4CGLs2EnoCKhcjWS-1-pOND5pQCy3Qmxo/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9zdGF0/aWMuc2tlZWxvLmNv/bS9yZW1vdGUvMzIw/LzQ4MC8xMDAvaHR0/cHM6Ly9za29vYi5z/My5hbWF6b25hd3Mu/Y29tL2xpdnJvcy8x/MjI0MTU0OTAvQVNT/SVNURU5URV9ET19W/SUxBT18xNzA2MTI1/NTQ4MTIyNDE1NDkw/U0stVjExNzA2MTI1/NTUwQi5qcGc', 2100),
('De Sangue e Cinzas', 'Jennifer L. Armentrout', 'Galera Record', 'Fantasia', 64.90, 4.8, 'https://imgs.search.brave.com/L9QiN-dM7zs1mqvExKN2xemSQEMl89W3rS41qF8-TGA/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9vc21l/bGhvcmVzbGl2cm9z/LmNvbS5ici93cC1j/b250ZW50L3VwbG9h/ZHMvMjAyMi8wMS9k/ZS1zYW5ndWUtZS1j/aW56YXMuanBn', 3600),

-- Suspense
('O Risco', 'S.T. Abby', 'Verus', 'Suspense', 44.90, 4.6, 'https://imgs.search.brave.com/qYLkPsR0_WpQTXPD2cFJFn5j0NsSFfHpfDqCQonqatk/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tLm1l/ZGlhLWFtYXpvbi5j/b20vaW1hZ2VzL0kv/NDFUMTJCTFJNQUwu/anBn', 2900),
('Academia dos Casos Arquivados', 'Jennifer Lynn Barnes', 'Alt', 'Suspense', 49.90, 4.7, 'https://imgs.search.brave.com/lbFcSGoOwlKKjF-1GBfXKOQO-crqEq95k9jBNeS8DWw/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tLm1l/ZGlhLWFtYXpvbi5j/b20vaW1hZ2VzL0kv/NTFrVmU0c3Nydkwu/anBn', 2100),
('A Empregada', 'Freida McFadden', 'Arqueiro', 'Suspense', 42.90, 4.7, 'https://imgs.search.brave.com/ebeSvN1GZ5lTHNMxXSFoMmbh9_j-xEiC9JkSuY98lT8/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9odHRw/Mi5tbHN0YXRpYy5j/b20vRF9RX05QXzJY/Xzg0NzU5OC1NTEI3/Nzk3NTQ0NjA5Nl8w/ODIwMjQtUC53ZWJw', 4500),
('Jantar Secreto', 'Raphael Montes', 'Companhia das Letras', 'Suspense', 54.90, 4.6, 'https://imgs.search.brave.com/iVhh75RmQT-RoKt_AbcQVFd43TC6ZIKdcNfIbkEwsRc/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9odHRw/Mi5tbHN0YXRpYy5j/b20vRF9RX05QXzky/NzAyOC1NTEE4MjI5/ODA2NzM3NF8wMjIw/MjUtRi53ZWJw', 3800),
('O Iluminado', 'Stephen King', 'Suma', 'Suspense', 69.90, 4.8, 'https://imgs.search.brave.com/V1BPGpdv_R4hbkFe3fbxpYzbXClAO1yriGDiSoljxmo/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9jZG4u/YXdzbGkuY29tLmJy/LzYwMHg0NTAvMjQ5/NS8yNDk1Nzg0L3By/b2R1dG8vMjcxNjYx/ODg4LzM1Mzc0Ni0x/LTV6Yzh6cmFhenYu/anBn', 5100),

-- Terror
('IT: A Coisa', 'Stephen King', 'Suma', 'Terror', 69.90, 4.8, 'https://imgs.search.brave.com/A874EU0a7bIp9g-9QtBYn1g7ntS92-zo1yzmkGUp68Y/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWcu/b2x4LmNvbS5ici90/aHVtYnM3MDB4NTAw/Lzc2Lzc2NTY2NzUx/NDMzNjIzMi53ZWJw', 5300),
('O Chamado de Cthulhu', 'H.P. Lovecraft', 'Hedra', 'Terror', 39.90, 4.7, 'https://imgs.search.brave.com/bsabIhdFwg7wfxAqbonKLBlQnHpLsdpBSDv45M-dW68/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWFn/ZXMtbmEuc3NsLWlt/YWdlcy1hbWF6b24u/Y29tL2ltYWdlcy9J/LzcxZXY0UEQwYlFM/LmpwZw', 3400),
('Olhos Prateados', 'Scott Cawthon', 'Intrínseca', 'Terror', 44.90, 4.8, 'https://imgs.search.brave.com/bTQJ8cwexZcylxWv0KNZCrV_I7_aY3TnszANmnPjI7s/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9odHRw/Mi5tbHN0YXRpYy5j/b20vRF9RX05QXzJY/Xzc2OTIwNi1NTEI3/NTAwNDUyODc4MV8w/MzIwMjQtRS1vbGhv/cy1wcmF0ZWFkb3Mt/c2VyaWUtZml2ZS1u/aWdodHMtYXQtZnJl/ZGR5cy12b2wtMS1z/Y290dC1jYXd0aG9u/LWUta2lyYS1icmVl/ZC13cmlzbGV5LWxp/dnJvLWZpc2ljby53/ZWJw', 2900),
('O Corvo', 'Edgar Allan Poe', 'DarkSide', 'Terror', 34.90, 4.6, 'https://imgs.search.brave.com/ClWJBvgwLuBRkoNLjOepC4YlqRdjt7DzOlFmijlp0qA/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tLm1l/ZGlhLWFtYXpvbi5j/b20vaW1hZ2VzL1Mv/YXBsdXMtbWVkaWEt/bGlicmFyeS1zZXJ2/aWNlLW1lZGlhL2I4/YmY2MTE4LTg3YzYt/NDAzNS1hODk0LTMy/OTcxMTIxZGU3My5f/X0NSMCwwLDMwMCwz/MDBfUFQwX1NYMzAw/X1YxX19fLnBuZw', 4100),
('Jogos Mortais', 'Angela Marsons', 'Gutenberg', 'Terror', 49.90, 4.6, 'https://imgs.search.brave.com/G5zEvHgB42lP83lNkTFxFdWbqPQrMaKuQxJS4RXYiCw/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pLnpz/dC5jb20uYnIvdGh1/bWJzLzUxLzNhLzE2/Ly0xNDYxMzg2Mzk3/LmpwZw', 2200),

-- Mangás e HQs
('Jujutsu Kaisen - Volume 26', 'Gege Akutami', 'Panini', 'Mangás e HQs', 39.90, 5.0, 'https://imgs.search.brave.com/OfSEhI9vyS3VwjR3oKL9qMFoXs_j3l6CI5vP0DsNHpw/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pLmVi/YXlpbWcuY29tL2lt/YWdlcy9nL0gzMEFB/T1N3cjNwbUZBTE0v/cy1sNTAwLmpwZw', 4800),
('Diário de uma Apotecária - Volume 01', 'Itsuki Nanao', 'Panini', 'Mangás e HQs', 37.90, 4.8, 'https://imgs.search.brave.com/nQsoNoHfN-zQdwig4lSPtUdYfj6vS-yLC0WzanMjVGY/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9sb2ph/dW5pY29yLmNvbS5i/ci93cC1jb250ZW50/L3VwbG9hZHMvMjAy/Ni8wMi91bmljb3It/cGFuaW5pLW1hbmdh/LWxpdnJvLWRpYXJp/b3MtZGUtdW1hLWFw/b3RlY2FyaWEta3Vz/dXJpeWEtbm8taGl0/b3JpZ290by0wMS1z/Y2FsZWQuanBn', 3200),
('Overlord Vol. 01', 'Hugin Miyama', 'JBC', 'Mangás e HQs', 34.90, 4.9, 'https://imgs.search.brave.com/hS0Ou4V-AOk29ipNSFWWrPxyU7ee9fXjFTdXNV0KpOo/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pLmVi/YXlpbWcuY29tL2lt/YWdlcy9nL1VnTUFB/T1N3LTFSbDh6UWcv/cy1sNTAwLmpwZw', 2500),
('X-Men: Deus Ama, o Homem Mata - Volume 1', 'Christopher Claremont', 'Panini', 'Mangás e HQs', 69.90, 4.8, 'https://imgs.search.brave.com/MpAnAHCK0EefNgqKWOaN0SLyLSfYUwBKkDZL8S8m7CE/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9saXZy/YXJpYWxveW9sYS5m/Yml0c3N0YXRpYy5u/ZXQvaW1nL3AveC1t/ZW4tZGV1cy1hbWEt/by1ob21lbS1tYXRh/LW1hcnZlbC1ncmFw/aGljLW5vdmVsLTI3/NzQ0My80NzIxODcu/anBnP3c9ODAwJmg9/ODAwJnY9bm8tY2hh/bmdlJnFzPWlnbm9y/ZQ', 3900),
('Batman: A Corte das Corujas', 'Scott Snyder, Greg Capullo', 'Panini', 'Mangás e HQs', 89.90, 4.9, 'https://imgs.search.brave.com/VWPRG9269aFSriwSSrf4fr7TH1g8mNiCwbW25oeags8/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWFn/ZXMtbmEuc3NsLWlt/YWdlcy1hbWF6b24u/Y29tL2ltYWdlcy9J/LzcxWWdJcjhGNmdM/LmpwZw', 5100),

-- Literatura Infantil
('O Pequeno Príncipe', 'Antoine de Saint-Exupéry', 'Agir', 'Literatura Infantil', 29.90, 4.8, 'https://imgs.search.brave.com/zv8kkhVfmI1rasLZgRILa0k7BJhL8APuK-H3Uc9yR5k/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tLm1l/ZGlhLWFtYXpvbi5j/b20vaW1hZ2VzL0kv/ODFEU2IzSjNaOUwu/anBn', 5500),
('O Menino Maluquinho', 'Ziraldo', 'Melhoramentos', 'Literatura Infantil', 32.90, 4.8, 'https://imgs.search.brave.com/7VFz4A0GyhoLidD9eLf0dE5m-tbThCjP9fcJp0lBCDY/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9odHRw/Mi5tbHN0YXRpYy5j/b20vRF9RX05QXzJY/Xzg1ODA5NC1NTEI3/NTQxMjU4NzE3MV8w/MzIwMjQtRS53ZWJw', 4900),
('O Meu Pé de Laranja Lima', 'José Mauro de Vasconcelos', 'Melhoramentos', 'Literatura Infantil', 39.90, 4.8, 'https://imgs.search.brave.com/TPdc4BGXAzdJp2uzh6vhdiivllPjJfJmFav1ClIO994/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9jZG4u/ZG9vY2Euc3RvcmUv/ODEyNDMvcHJvZHVj/dHMvdTR3bW1zaHhs/ZnQxbWJ3amE1MHlo/ZHY0YXk5cXBya2J6/N3JvXzY0MHg2NDAr/ZmlsbF9mZmZmZmYu/anBnP3Y9MTY3MTI5/OTM0NA', 4200),
('A Bolsa Amarela', 'Lygia Bojunga', 'Casa Lygia Bojunga', 'Literatura Infantil', 34.90, 4.8, 'https://imgs.search.brave.com/p4yFPEUsuf9wLgWb6bBjeqSsI5b-H93ODeuL1EqyWS4/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9odHRw/Mi5tbHN0YXRpYy5j/b20vRF9RX05QXzJY/XzczMDgwNS1NTEI0/NjUwNjEyNTc4Nl8w/NjIwMjEtRS53ZWJw', 3100),
('Reinações de Narizinho', 'Monteiro Lobato', 'Globo Livros', 'Literatura Infantil', 44.90, 4.6, 'https://imgs.search.brave.com/k4w0vBJ3ZQYN5N0mwrwV7t6_92NilvqDf71aQpoUxT8/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9odHRw/Mi5tbHN0YXRpYy5j/b20vRF9RX05QXzJY/XzkxNjg1OC1NTFU1/MDQ1NDU4Mjc0OV8w/NjIwMjItRS53ZWJw', 3800);

-- Inserção das Resenhas em Vídeo
INSERT INTO resenhas_videos (titulo_livro, url_video, resumo) VALUES
-- Romance
('Táticas do Amor', 'https://www.youtube.com/watch?v=zEOYc7lP8DA', 'Uma resenha apaixonante sobre a comédia romântica de Sarah Adams, destacando o desenvolvimento dos personagens e a leveza da história.'),
('Parte do Seu Mundo', 'https://www.youtube.com/watch?v=FybviD4rTFY', 'Análise completa da obra de Abby Jimenez: uma história emocionante sobre diferenças sociais, recomeços e amor verdadeiro.'),
('Nem Te Conto', 'https://www.youtube.com/watch?v=ld6bYPsqgM0', 'Confira os principais pontos positivos da escrita envolvente de Emily Henry nesta resenha cheia de diálogos afiados e humor.'),
('Binding 13', 'https://www.youtube.com/watch?v=LtSrZoguL4U', 'Análise aprofundada sobre o romance de Chloe Walsh, abordando temas sensíveis, superação e o companheirismo dos protagonistas.'),
('Apostando no Amor', 'https://www.youtube.com/watch?v=sEm7p9OApsM', 'Resenha bem-humorada sobre a obra de Lynn Painter, explorando a dinâmica do clichê de inimigos para amantes.'),

-- Ficção
('Harry Potter: A Pedra Filosofal', 'https://www.youtube.com/watch?v=EfdKgRE6JZM', 'Resenha nostálgica sobre o início do mundo bruxo de J.K. Rowling, apresentando Hogwarts, amizade e magia inocente.'),
('Maze Runner: Correr ou Morrer', 'https://www.youtube.com/watch?v=MK8c3WaeQYU', 'Análise do ritmo eletrizante da distopia de James Dashner: a Clareira, os Verdugos e o enigma do labirinto.'),
('Duna', 'https://www.youtube.com/watch?v=Vy8hkKkTrLE', 'Resenha completa sobre a monumental ópera espacial de Frank Herbert: ecologia, política, religião e o destino de Paul Atreides.'),
('Percy Jackson e os Olimpianos: O Ladrão de Raios', 'https://www.youtube.com/watch?v=-nowadSIL98', 'Análise do clássico de Rick Riordan sobre a mitologia grega adaptada para o mundo moderno no Acampamento Meio-Sangue.'),
('As Crônicas de Nárnia', 'https://www.youtube.com/watch?v=PMSRrB7H9lU', 'Resenha sobre a saga fantástica de C.S. Lewis, cobrindo o guarda-roupa mágico, Aslan e o combate entre o bem e o mal.'),

-- Fantasia
('Corte de Espinhos e Rosas', 'https://www.youtube.com/watch?v=LHCLyNrKoXE', 'Vídeo análise do primeiro livro da famosa saga de Sarah J. Maas. Entenda a construção do universo de faerie e a jornada de Feyre.'),
('Era uma Vez um Coração Partido', 'https://www.youtube.com/watch?v=tUmwC3755XM', 'Resenha sobre o spin-off mágico de Caraval por Stephanie Garber, destacando a estética de conto de fadas e as maldições do amor.'),
('Estilhaça-me', 'https://www.youtube.com/watch?v=pz3Iia4pNtQ', 'Análise da distopia romântica de Tahereh Mafi: a poética do texto, o toque mortal de Juliette e os dilemas do poder.'),
('Assistente do Vilão', 'https://www.youtube.com/watch?v=yXYPrZgLDS4', 'Resenha divertida sobre o sucesso de Hannah Nicole Maehrer, misturando comédia, fantasia e romance slow burn.'),
('De Sangue e Cinzas', 'https://www.youtube.com/watch?v=HQTsF1qheCo', 'Vídeo opinativo sobre a saga de Jennifer L. Armentrout, focando nas reviravoltas marcantes, segredos do reino e química dos personagens.'),

-- Suspense
('O Risco', 'https://www.youtube.com/watch?v=FIW3-IG0558', 'Resenha chocante sobre a série Mindf*ck de S.T. Abby, mesclando vingança brutal, suspense psicológico e romance dark.'),
('Academia dos Casos Arquivados', 'https://www.youtube.com/watch?v=36ov0Xqy5zo', 'Análise do mistério de Jennifer Lynn Barnes sobre adolescentes ajudando o FBI a resolver crimes do passado.'),
('A Empregada', 'https://www.youtube.com/watch?v=J9xgCTIat1o', 'Resenha sobre o bestseller de Freida McFadden: segredos de família, tensão doméstica e reviravoltas imprevisíveis.'),
('Jantar Secreto', 'https://www.youtube.com/watch?v=0_uSN_-WGZo', 'Análise perturbadora do thriller nacional de Raphael Montes, abordando os limites éticos dos personagens.'),
('O Iluminado', 'https://www.youtube.com/watch?v=JgfvH22MvF0', 'Resenha profunda da obra-prima do terror de Stephen King sobre o Hotel Overlook, isolamento e a loucura de Jack Torrance.'),

-- Terror
('IT: A Coisa', 'https://www.youtube.com/watch?v=q55RnQTTvOM', 'Resenha detalhada do clássico de Stephen King. Análise sobre o medo na infância, a amizade e a entidade em Derry.'),
('O Chamado de Cthulhu', 'https://www.youtube.com/watch?v=ZWv3XhwYF24', 'Vídeo análise sobre a obra de H.P. Lovecraft e os fundamentos do horror cósmico, loucura e a mitologia dos Antigos.'),
('Olhos Prateados', 'https://www.youtube.com/watch?v=0ERJ9xiHXgA', 'Resenha sobre o livro oficial de Five Nights at Freddy’s por Scott Cawthon, explorando o mistério dos animatrônicos.'),
('O Corvo', 'https://www.youtube.com/watch?v=ltwzkoucn60', 'Análise poética e temática do poema sombrio de Edgar Allan Poe, abordando o luto, a melancolia e o desespero.'),
('Jogos Mortais', 'https://www.youtube.com/watch?v=blqDIB172Wg', 'Resenha do thriller de Angela Marsons, com foco nas investigações da detetive Kim Stone e na mente do assassino.'),

-- Mangás e HQs
('Jujutsu Kaisen - Volume 26', 'https://www.youtube.com/watch?v=W7pi30UekVE', 'Análise do clímax das batalhas no volume 26 do mangá de Gege Akutami, comentando a arte e o impacto no enredo.'),
('Diário de uma Apotecária - Volume 01', 'https://www.youtube.com/watch?v=ldw5H6yCPxY', 'Resenha sobre as intrigas da corte imperial e o intelecto afiado da jovem Maomao nesta adaptação em mangá.'),
('Overlord Vol. 01', 'https://www.youtube.com/watch?v=T4MFz5LUPPc', 'Vídeo review do volume inicial do mangá/light novel, analisando a premissa isekai do soberano Ainz Ooal Gown.'),
('X-Men: Deus Ama, o Homem Mata - Volume 1', 'https://www.youtube.com/watch?v=_v892Rg52EY', 'Resenha crítica da histórica graphic novel dos X-Men escrita por Chris Claremont, debatendo preconceito e heroísmo.'),
('Batman: A Corte das Corujas', 'https://www.youtube.com/watch?v=H3TpJ_neTnA', 'Análise de uma das melhores histórias do Batman escrita por Scott Snyder, explorando a sociedade secreta de Gotham.'),

-- Literatura Infantil
('O Pequeno Príncipe', 'https://www.youtube.com/watch?v=4PQlBlpo5dI&t=336s', 'Resenha reflexiva sobre a obra imortal de Antoine de Saint-Exupéry, com foco nas lições sobre amor, amizade e humanidade.'),
('O Menino Maluquinho', 'https://www.youtube.com/watch?v=8rDnr4QpPTw', 'Vídeo em homenagem à obra de Ziraldo, ressaltando o valor da infância livre, criatividade e afeto na literatura brasileira.'),
('O Meu Pé de Laranja Lima', 'https://www.youtube.com/watch?v=Mh-MGmcI5nI', 'Resenha emocionante sobre a marcante história de Zezé e Minguinho por José Mauro de Vasconcelos.'),
('A Bolsa Amarela', 'https://www.youtube.com/watch?v=TELa4VKxc5E', 'Análise da obra clássica infantil de Lygia Bojunga sobre imaginação, vontade de crescer e aceitação pessoal.'),
('Reinações de Narizinho', 'https://www.youtube.com/watch?v=4LzrSNi3JII', 'Resenha sobre o marco inicial do Sítio do Pica-Pau Amarelo por Monteiro Lobato, destacando a fantasia e o folclore.');

COMMIT;