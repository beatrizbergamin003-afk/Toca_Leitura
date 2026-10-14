DELETE FROM livros a
USING livros b
WHERE a.id > b.id
  AND LOWER(TRIM(a.titulo)) = LOWER(TRIM(b.titulo));

  ALTER TABLE livros ADD CONSTRAINT unique_livro_titulo UNIQUE (titulo);

  -- Insere as resenhas em vídeo de todos os livros do catálogo
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
('Reinações de Narizinho', 'hhttps://www.youtube.com/watch?v=4LzrSNi3JII', 'Resenha sobre o marco inicial do Sítio do Pica-Pau Amarelo por Monteiro Lobato, destacando a fantasia e o folclore.');


CREATE TABLE resenhas_videos (
    id SERIAL PRIMARY KEY,
    titulo_livro VARCHAR(200) NOT NULL,
    url_video VARCHAR(500) NOT NULL,
    resumo TEXT
);