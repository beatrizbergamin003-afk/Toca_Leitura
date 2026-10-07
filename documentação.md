# Documentação inicial de Especificação do Sistema

# Toca de Leitura 

## Escopo e Contexto do Projeto 

### 1.1 Contexto e Justificativa (O Problema)
No ecossistema atual da leitura digital e física, os leitores enfrentam uma jornada fragmentada. Para vivenciar sua paixão pelos livros, um usuário frequentemente precisa alternar entre múltiplos canais desintegrados:

. Utiliza um site e-commerce tradicional para comprar livros;

. Recorre a aplicativos externos para marcar o progresso de páginas lidas;

. Acessa redes sociais genéricas para discutir opiniões com amigos;

. Busca plataformas externas de vídeo (como Youtube ou TikTok) para assistir a resenhas e descobrir novos lançamentos. 

Essa pulverização gera perda de engajamento, difilculta o acompanhamento de metas de leitura e priva o leitor de uma comunidade centralizada e focada exclusivamente no universo literário.

### 1.2 A Solução Proposta e Proposta de Valor
A Toca de Leitura surge como uma plataforma web unificada ("tudo-em-um") projetada para integrar a jornada do leitor do início ao fim. O sistema conecta o desejo de descoberta e aquisição de obras ao acompanhamento do hábito de leitura e à troca social de experiências.

#### Principais Pilares da Solução:

. Centralização Literária: Catálogo de livros com filtro por gêneros, sistema de busca e seção dedicada aos títulos em alta ("Mais lidos").

. Acompanhamento de Leitura: Ferramenta visual para atualizar o progresso de páginas e tempo de leitura, gerando barras indicadoras de porcentagem (0% a 100%).

. Engajamento Comunitário: Feed social dividido por abas (Geral, Amigos, Minhas, Seguindo) para publicar avaliações, tirar dúvidas e opinar sobre obras.

. Curadoria em Vídeo: Aba de resenhas audiovisuais com resumos descritivos para ajudar o leitor na escolha da sua próxima aventura.

. Autonomia e Suporte: Painel para gerenciamento da conta (incluindo exclusão segura com dupla confirmação) e canal direto de atendimento com a equipe.

### 1.3 Público-Alvo
A plataforma foi desenhada para atender a diferentes perfis da comunidade leitora:

. Leitores Assíduos e Estudantes: Pessoas que buscam organizar suas leituras atuais, contabilizar páginas e acompanhar seu ritmo diário.

. Comunidade e Clubes do Livro: Grupos de amigos e leitores que desejam compartilhar opiniões, comentar sobre capítulos e acompanhar o progresso dos seus pares.

. Consumidores de Conteúdo Literário (BookLovers): Usuários que buscam recomendações rápidas, listas de livros mais lidos e resenhas em vídeo antes de adquirir uma obra.

### 1.4 Objetivos do Projeto

#### 1.4.1 Objetivo Geral
Desenvolver uma aplicação web responsiva, amigável e intuitiva em PHP e PostgreSQL, oferecendo uma experiência completa de catálogo, acompanhamento de hábitos de leitura, feed social e suporte ao usuário.

#### 1.4.2 Objetivos Específicos

1. Facilitar a Descoberta e Compra: Disponibilizar um catálogo organizado com filtros por gêneros literários, preços, avaliações em estrelas e destaques de livros populares.

2. Incentivar a Leitura Contínua: Permitir que o usuário atualize seu status de leitura (páginas lidas, tempo gasto e gênero) e visualize seu progresso em tempo real.

3. Promover a Interação Social: Oferecer um mural dinâmico de opiniões com abas de navegação para que os leitores troquem impressões sobre os livros.

4. Garantir a Segurança dos Dados: Implementar controle rigoroso de sessões, criptografia de senhas e confirmação de segurança para operações críticas (como exclusão de conta).

5. Oferecer Suporte Direto: Disponibilizar um formulário acessível de comunicação para dúvidas e sugestões.

### 1.5 Delimitação do Escopo
Para assegurar o alinhamento das entregas do projeto, o escopo foi claramente definido entre as funcionalidades que pertencem ao sistema e aquelas que não serão desenvolvidas nesta versão inicial:

#### 1.5.1 O que o sistema FAZ (Dentro do Escopo)

. Apresentação do Site: Tela incial (Início) Explicando a proposta da Toca de Leitura.

. Acesso de Usuários: Telas de cadastro, login (com opção visual de login pelo Google) e encerramento de sessão (logout).

. Loja e Catálogo: Exibição de livros com preço, notas (1 a 5 estrelas), pesquisa por nome e filtro por gêneros literários.

. Feed Comunitário: Espaço para publicar opiniões e ver postagens de outros leitores organizadas por abas(Geral, Amigos, Minhas, Seguindo).

. Busca e Tendências: Tela para procurar obras e visualizar a lista de livros "Mais lidos".

. Central de Resenhas: Aba para assistir a vídeos de análises literárias acompanhados de resumos em texto.

. Controle de Leitura: Painel para o leitor atualizar o livro atual, tempo de leitura e total de páginas lidas, com barra de progresso (0% a 100%).

. Atendimento ao Leitor: Formulário na página Contate-nos para envio de mensagens e duvidas.

. Gestão de Perfil: Opçãopara o usuário apagar sua conta mediante confirmação de nome e senha.



