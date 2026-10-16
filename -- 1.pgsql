-- 1. Altera a coluna para aceitar notas com casas decimais (ex: 4.8, 4.5)
ALTER TABLE livros ALTER COLUMN estrelas TYPE NUMERIC(2,1);

-- 2. Atualiza as avaliações de cada livro no banco de dados:

-- Romance
UPDATE livros SET estrelas = 4.6 WHERE titulo = 'Táticas do Amor';
UPDATE livros SET estrelas = 4.7 WHERE titulo = 'Parte do Seu Mundo';
UPDATE livros SET estrelas = 4.7 WHERE titulo = 'Nem Te Conto';
UPDATE livros SET estrelas = 4.7 WHERE titulo = 'Binding 13';
UPDATE livros SET estrelas = 4.8 WHERE titulo = 'Apostando no Amor';

-- Ficção
UPDATE livros SET estrelas = 4.9 WHERE titulo = 'Harry Potter: A Pedra Filosofal';
UPDATE livros SET estrelas = 4.6 WHERE titulo = 'Maze Runner: Correr ou Morrer';
UPDATE livros SET estrelas = 4.8 WHERE titulo = 'Duna';
UPDATE livros SET estrelas = 4.8 WHERE titulo = 'Percy Jackson e os Olimpianos: O Ladrão de Raios';
UPDATE livros SET estrelas = 4.8 WHERE titulo = 'As Crônicas de Nárnia';

-- Fantasia
UPDATE livros SET estrelas = 4.8 WHERE titulo = 'Corte de Espinhos e Rosas';
UPDATE livros SET estrelas = 4.8 WHERE titulo = 'Era uma Vez um Coração Partido';
UPDATE livros SET estrelas = 4.5 WHERE titulo = 'Estilhaça-me';
UPDATE livros SET estrelas = 4.8 WHERE titulo = 'Assistente do Vilão';
UPDATE livros SET estrelas = 4.8 WHERE titulo = 'De Sangue e Cinzas';

-- Suspense
UPDATE livros SET estrelas = 4.6 WHERE titulo = 'O Risco';
UPDATE livros SET estrelas = 4.7 WHERE titulo = 'Academia dos Casos Arquivados';
UPDATE livros SET estrelas = 4.7 WHERE titulo = 'A Empregada';
UPDATE livros SET estrelas = 4.6 WHERE titulo = 'Jantar Secreto';
UPDATE livros SET estrelas = 4.8 WHERE titulo = 'O Iluminado';

-- Terror
UPDATE livros SET estrelas = 4.8 WHERE titulo = 'IT: A Coisa';
UPDATE livros SET estrelas = 4.7 WHERE titulo = 'O Chamado de Cthulhu';
UPDATE livros SET estrelas = 4.8 WHERE titulo = 'Olhos Prateados';
UPDATE livros SET estrelas = 4.6 WHERE titulo = 'O Corvo';
UPDATE livros SET estrelas = 4.6 WHERE titulo = 'Jogos Mortais';

-- Mangás e HQs
UPDATE livros SET estrelas = 5.0 WHERE titulo = 'Jujutsu Kaisen - Volume 26';
UPDATE livros SET estrelas = 4.8 WHERE titulo = 'Diário de uma Apotecária - Volume 01';
UPDATE livros SET estrelas = 4.9 WHERE titulo = 'Overlord Vol. 01';
UPDATE livros SET estrelas = 4.8 WHERE titulo = 'X-Men: Deus Ama, o Homem Mata - Volume 1';
UPDATE livros SET estrelas = 4.9 WHERE titulo = 'Batman: A Corte das Corujas';

-- Literatura Infantil
UPDATE livros SET estrelas = 4.8 WHERE titulo = 'O Pequeno Príncipe';
UPDATE livros SET estrelas = 4.8 WHERE titulo = 'O Menino Maluquinho';
UPDATE livros SET estrelas = 4.8 WHERE titulo = 'O Meu Pé de Laranja Lima';
UPDATE livros SET estrelas = 4.8 WHERE titulo = 'A Bolsa Amarela';
UPDATE livros SET estrelas = 4.6 WHERE titulo = 'Reinações de Narizinho';

-- Tabela para registrar cada tentativa de login no sistema aba_categoria
CREATE TABLE IF NOT EXISTS logs_acesso (
    id SERIAL PRIMARY KEY,
    usuario_id INT REFERENCES usuarios(id) ON DELETE CASCADE,
    email_tentativa VARCHAR(150) NOT NULL,
    ip_origem VARCHAR(50),
    sucesso BOOLEAN DEFAULT TRUE,
    data_acesso TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

UPDATE usuarios SET is_admin = TRUE WHERE email = 'admin2@gmail.com';

CREATE TABLE IF NOT EXISTS logs_acesso (
    id SERIAL PRIMARY KEY,
    usuario_id INT REFERENCES usuarios(id) ON DELETE CASCADE,
    email_tentativa VARCHAR(150) NOT NULL,
    ip_origem VARCHAR(45),
    sucesso BOOLEAN DEFAULT TRUE,
    data_acesso TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

SELECT id, nome, email, is_admin FROM usuarios;

-- Tabela principal de pedidos
CREATE TABLE pedidos (
    id SERIAL PRIMARY KEY,
    usuario_id INT REFERENCES usuarios(id) ON DELETE CASCADE,
    total DECIMAL(10, 2) NOT NULL,
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'Concluído'
);

-- Tabela com os itens comprados em cada pedido
CREATE TABLE itens_pedido (
    id SERIAL PRIMARY KEY,
    pedido_id INT REFERENCES pedidos(id) ON DELETE CASCADE,
    livro_id INT REFERENCES livros(id) ON DELETE SET NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10, 2) NOT NULL
);

CREATE TABLE IF NOT EXISTS pedidos (
    id SERIAL PRIMARY KEY,
    usuario_id INT REFERENCES usuarios(id) ON DELETE CASCADE,
    total DECIMAL(10, 2) NOT NULL,
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'Concluído'
);

CREATE TABLE IF NOT EXISTS itens_pedido (
    id SERIAL PRIMARY KEY,
    pedido_id INT REFERENCES pedidos(id) ON DELETE CASCADE,
    livro_id INT REFERENCES livros(id) ON DELETE SET NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10, 2) NOT NULL
);

ALTER TABLE mensagens_contato 
ADD COLUMN IF NOT EXISTS usuario_id INT REFERENCES usuarios(id) ON DELETE SET NULL,
ADD COLUMN IF NOT EXISTS resposta_admin TEXT,
ADD COLUMN IF NOT EXISTS data_resposta TIMESTAMP,
ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'Pendente';

ALTER TABLE mensagens_contato 
ADD COLUMN IF NOT EXISTS data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE mensagens_contato 
ADD COLUMN IF NOT EXISTS usuario_id INT REFERENCES usuarios(id) ON DELETE SET NULL,
ADD COLUMN IF NOT EXISTS assunto VARCHAR(255),
ADD COLUMN IF NOT EXISTS resposta_admin TEXT,
ADD COLUMN IF NOT EXISTS data_resposta TIMESTAMP,
ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'Pendente',
ADD COLUMN IF NOT EXISTS data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

UPDATE mensagens_contato m
SET usuario_id = u.id
FROM usuarios u
WHERE LOWER(m.email) = LOWER(u.email) 
  AND m.usuario_id IS NULL;