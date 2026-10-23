

-- ATUALIZAÇÃO DA TABELA DO FEED: Adiciona o controle de páginas lidas

-- Adiciona as colunas de página atual e total de páginas nas postagens do feed para calcular a porcentagem de leitura
ALTER TABLE postagens_feed ADD COLUMN pagina_atual INT DEFAULT 0, ADD COLUMN paginas_total INT DEFAULT 0;



-- CORREÇÃO DE ACENTUAÇÃO / CODIFICAÇÃO DE TEXTO (ENCODING)

-- Corrige caracteres especiais e acentos quebrados nos títulos dos livros (converte de WIN1252 para UTF-8)
UPDATE livros 
SET titulo = convert_from(convert_to(titulo, 'WIN1252'), 'UTF8')
WHERE titulo LIKE '%Ã%';

-- Corrige a acentuação e caracteres especiais quebrados nos nomes dos autores
UPDATE livros 
SET autor = convert_from(convert_to(autor, 'WIN1252'), 'UTF8')
WHERE autor LIKE '%Ã%';

