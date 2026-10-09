# Levantamento de Requisitos Funcionais (RF) 

Os Requisitos Funcionais descrevem os comportamentos, recursos e serviços que o sistema A Toca de Leitura executa para atender aos utilizadores e administradores.

## Módulo 1: Gestão de Acesso e Contas de Utilizador

RF01 - Cadastro de Utilizadores: O sistemadeve permitir que novos leitores se registrem fornecendo nome completo, e-mail único e palavra-passe.

RF02 - Autenticação (Login/Logout): O sistema deve validar as credenciais do utilizador (e-mail e palavra-passe) e gerir sessões seguras em PHP.

RF03 - Exclusão de Conta pelo Leitor: O sistema deve permitir que o  utilizador autenticado elimine a sua conta mediante a confirmação do nome e da palavra-passe.

RF04 - Controle de Permissões (RBAC): O sistema deve diferenciar leitores comuns de administradores através do sinalizador is_admin.

## Módulo 2: Estante Virtual e Acompanhamento de Leitura

RF05 - Adicionar Livros à Estante: O sistema deve permitir que o leitor registre um livro na sua estante, informando título, gênero, meta de tempo e quantidade total de páginas.

RF06 - Atualização e Cálculo de Progresso: O sistema deve permitir a atualização das páginas lidas e calcular automaticamente a porcentagem de conclusão (0% á 100%).

RF07 - Divulgação Automática no Feed: O sistema deve oferecer uma opção (checkbox) para publicar automaticamenteo progresso de leitura no feed comunitário.

RF08 - Remoção de Registros da Estante: O sistema deve permitir que o leitor remova livros do seu histórico de leitura individual. 

## Módulo 3: Catálogo, Pesquisa e Loja

RF09 - Listagem de Livros sem Duplicados: O sistema deve listar o catálogo comercial garantindo a exibição de apenas um registro por título.

RF10 - Filtragem de Livros sem Duplicados: O sistema deve permitir a filtragem dinâmica de livros por categorias (Romance, Ficção, Fantasia, Suspense, Terror, Mangás/HQs e Literatura Infantil).

RF11 - Pesquisa de Obras: O sistema deve permitir a busca textual por título, autor ou editora no catálogo e na secção "Procure".

RF12 - Ranking dos Mais Lidos: O sistema deve exibir os livros com maior número acumulado de leitores na ausência de filtros de pesquisa ativos.

## Módulo 4: Feed Social e Central de Resenhas

RF13 - Publicação de Opiniões no Feed: O sistema deve permitir que leitores autenticados publiquem comentários e avaliações categorizados por abas (Geral, Amigos, Minhas, Seguindo).

RF14 - Eliminação de Publicações Próprias: O sistema deve permitir que cada leitor elimine exclusivamente as suas próprias postagens do feed.

RF15 - Reprodução de Resenhas em Vídeo: O sistema deve converter hiperligações do Youtube e renderizar o leitor de vídeo (iframe) integrado na página de resenhas.

## Módulo 5: Suporte e Painel Administrativo

RF16 - Envio de Mensagens de Contato: O sistema deve disponibilizar um formulário aberto para envio de dúvidas, sugestões ou críticas à equipe do site.

RF17 - Painel de Administração: O sistema deve disponibilizar uma interface restrita (admin.php) para gestãoglobal da plataforma.

RF18 - Moderação de Conteúdos e Utilizadores: O sistema deve permitir ao administrador promover/rebaixar permissões de utilizadores, remover contas e apagar qualquer publicação do feed comunitário.


# Levantamento de Requisitos Não Funcionais (RNF)

Os Requisitos Não Funcionais definem as qualidades técnicas, restrições arquiteturais e padrões de desempenho da plataforma.

## Segurança

RNF01 - Encriptação de Palavras-Chave: As palavras-passe dos utilizadores devem ser tranformadas num hash criptográfico seguro utilizando o algoritmo Bcrypt (password_hash) antes do armazenamento.

RNF02 - Prevenção contra SQL Injection: Todas as interações com o banco de dados PostgreSQL devem utilizar instruções preparadas via PDO (PDO::prepare) com passagem parametrizada.

RNF03 - Proteção contra Cross-Site Scripting(XSS): Todos os dados inseridos por utilizadores e exibidos no ecrã devem passar pelo tratamento htmlspecialchars().

RNF04 - Restrição de Acesso às Rotas Administrativas: Páginas privadas e administrativas devem validar a sessão no servidor e redirecionar tentativas não autorizadas.

## Desempenho e Arquitetura

RNF05 - Compatibilidade de Base de Dados: O sistema deve operar sobre o SGBD PostgreSQL (porta 5432) garantindo a integridade transacional.

RNF06 - Reposta e Renderização: O tempo de resposta das consultas de listagem do catálogo não deve exceder 2 segundos em condições normais de utilização.

RNF07 - Integridade Referencial CASCATA: As chaves estrangeiras entre a tabela usuarios e as tabelas dependentes devem utilizar a instrução ON DELETE CASCADE.

## Usabilidade  e Portabilidade

RNF08 - Design Fluido e Responsivo: As tabelas e formulários devem adaptar-se a diferentes resoluções de ecrã (desktops e dispositivos móveis).

RNF09 - Compatibilidade com Navegadores: O front-end (HTML5, CSS3) deve funcionar nos navegadores modernos (Chrome, Firefox, Edge, Safari, Brave).

# Regras de Negócio (RN)

As Regras de Negócio definem as políticas e restrições operacionais da aplicação A Toca de Leitura.

RN01 - Unicidade de Identificação do Leitor: Não é permitido registrar mais do que uma conta com o mesmo endereço de e-mail (email UNIQUE).

RN02 - Unicidade do Título do Livro: A tabela de acervo não pode conter obras com títulos idênticos (titulo UNIQUE).

RN03 - Limites do Cálculo de Progresso: A porcentagem de leitura é calculada pela fórmula progresso =páginas lidas/totaldepáginas100, ficando estritamente limitada ao intervalo de $0\%$ a $100\%$.

RN04 - Exclusão de Conta em Cascata: A remoção de um utilizador implica a eliminação automática de todo o seu histórico de leitura e postagens no feed comunitário.

RN05 - Nível de Permissão para Moderação: Leitores comuns só podem eliminar as suas próprias publicações; a eliminação de postagens de terceiros é exclusiva para contas com is_admin = TRUE.

RN06 - Acesso ao Painel de Controle: O acesso ao admin.php requer sessão ativa com a flag is_admin = TRUE. Caso contrário, o sistema deve redirecionar o utilizador para a página incial com mensagem de erro.

RN07 - Proteção da Conta Principal de Admin: O painel administrativo deve proibir que o administrador ativo remova o seu próprio privilégio de acesso ou elimine a sua própria conta através de interface de terceiros.

# Product Backlog

# 📋 Product Backlog - A Toca de Leitura

| ID | Épico | História de Usuário (User Story) | Critérios de Aceitação | Prioridade (MoSCoW) | Estimativa (Points) |
| :--- | :--- | :--- | :--- | :---: | :---: |
| **US01** | Autenticação | Como leitor, quero me cadastrar e fazer login para acessar minha área restrita e salvar leituras. | Validar e-mail único, criptografar senha com Bcrypt (`password_hash`) e salvar dados essenciais na sessão PHP (`$_SESSION`). | **Must Have** | 3 |
| **US02** | Autenticação | Como leitor, quero deletar minha conta para remover meus dados permanentemente do sistema. | Solicitar confirmação de nome e senha, apagar usuário no banco com acionamento de `ON DELETE CASCADE` e encerrar a sessão. | **Should Have** | 2 |
| **US03** | Estante Virtual | Como leitor, quero adicionar livros à "Minha Estante" definindo meta de tempo e total de páginas. | Validar total de páginas > 0, associar ao `usuario_id` logado e permitir escolher gêneros do catálogo. | **Must Have** | 5 |
| **US04** | Estante Virtual | Como leitor, quero atualizar as páginas lidas e visualizar o progresso calculado em porcentagem. | Calcular progresso `(paginas_lidas / total_paginas) * 100` (max 100%), renderizar tag `<progress>` e permitir envio automático ao Feed. | **Must Have** | 3 |
| **US05** | Catálogo / Loja | Como leitor, quero filtrar por gênero e buscar livros por texto sem visualizar itens duplicados. | Aplicar `DISTINCT ON (titulo)` ou chave `UNIQUE`, além de buscas insensíveis a maiúsculas/acentos usando `ILIKE`. | **Must Have** | 5 |
| **US06** | Catálogo / Loja | Como leitor, quero ver o ranking dos livros mais lidos e simular a adição de itens ao carrinho. | Exibir top 5 ordenado por `total_leitores` no estado padrão do `procurar.php` e acionar alerta interativo ao clicar em "Comprar". | **Should Have** | 3 |
| **US07** | Feed Social | Como leitor, quero publicar opiniões e atualizações de leitura no feed divididos em abas. | Salvar ID do autor, nome do livro, comentário, % lida e categoria (*Geral*, *Amigos*, *Minhas*, *Seguindo*). | **Must Have** | 5 |
| **US08** | Feed Social | Como leitor, quero excluir minhas próprias publicações enviadas ao feed social. | Exibir botão "Excluir Publicação" apenas quando `postagens_feed.usuario_id == $_SESSION['usuario_id']`. | **Should Have** | 2 |
| **US09** | Resenhas | Como leitor, quero assistir a resenhas em vídeo pelo YouTube e pesquisar análises do acervo. | Extrair ID do link do YouTube (padrão e `youtu.be`), gerar tag `<iframe>` e disponibilizar busca unificada com o feed. | **Should Have** | 5 |
| **US10** | Suporte | Como visitante/leitor, quero enviar mensagens pelo formulário de contato. | Gravar nome, e-mail e texto na tabela `mensagens_contato` com timestamp do envio e exibir confirmação. | **Must Have** | 2 |
| **US11** | Painel Admin | Como administrador, quero acessar um painel restrito (`admin.php`) para gerenciar a plataforma. | Validar regra `verificar_admin()` com `is_admin = TRUE`, bloqueando não autorizados com redirecionamento e mensagem de erro. | **Must Have** | 3 |
| **US12** | Painel Admin | Como administrador, quero gerenciar permissões de usuários e moderar postagens inadequadas. | Permitir promover/rebaixar privilégios de admin, excluir qualquer usuário ou post do feed, impedindo autoexclusão da conta ativa. | **Must Have** | 5 |

# Detalhamento das Históriase Critérios de Aceitação 

## US01 - Registrar e Autenticar Leitor

. Descrição: Como leitor, quero criar uma conta com nome, e-mail e palavra-passe e efetuar login para aceder à minha área privada.

 ### . Critérios de Aceitação:

 - O sistema deve rejeitar e-mails já existentes na base de dados (RN01).

  - A palavra-passe deve ser armazenada com o algoritmo Bcrypt.

  - Em caso de credenciais incorretas no login.php, o sistema deve exibir uma mensagem de erro sem expor detalhes da falha.

  - Em caso de sucesso, o sistema deve guardar na sessão: usuario_id, usuario_nome e is_admin.

  ### US02 - Exclusão Definitiva de Conta

  - Descrição: Como leitor autenticado, quero apagar a minha conta para remover permanentemente os meus dados da plataforma.

  - Critérios de Aceitação: 

  - O formulário em deletar_usuario.php deve exigir a confirmação do nome do utilizador e da palavra-passe.

  - Após a confirmação, o sistema deve apegar o registro do utilizador e acionar o fecho de sessão (session_destroy()).

  - O banco de dados deve remover automaticamente todo o histórico e postagens vinculadas via ON DELETE CASCADE.

  ### US03 - Gestão de Livros na Estante

  . Descrição: como leitor, quero adicionar obras à minha estante indicando título, gênero e total de páginas.

  . Critérios de Aceitação:

  - O sistema deve permitir selecionar o gênero numa lista suspensa predefinida.

  - O campo de total de páginas deve ser um valor inteiro superior a zero.

  - O registro deve ser associado ao ID do utilizador autenticado.

  --

 ### US04 - Acompanhamento de Progresso de Leitura
   . Descrição: Como leitor, quero atualizar a quantidade de páginas lidas para ver a percentagem de progresso calculada.
   . Critérios de Aceitação:
   - O sistema deve calcular a percentagem através da fórmula de progresso e limitar o resultado ao valor máximo de $100\%$.
   - O ecrã deve renderizar visualmente a barra de progresso com a tag HTML <progress>.
   - Caso a opção "Publicar no Feed" esteja ativa, o sistema deve criar simultaneamente um registo na tabela postagens_feed.

### US05 - Consulta do Catálogo sem Duplicados
    . Descrição: Como leitor, quero navegar pelo catálogo comercial filtrando por género ou pesquisando por texto.
    . Critérios de Aceitação:
    - A consulta SQL em compras.php deve utilizar a instrução DISTINCT ON (titulo) ou a restrição UNIQUE para garantir que nenhuma obra apareça duplicada.
    - Os filtros por género e as buscas por título devem utilizar a cláusula ILIKE para ignorar diferenças entre maiúsculas, minúsculas e acentuação.

### US06 - Ranking de Mais Lidos e Compra Simulada
     . Descrição: Como leitor, quero verificar quais são os livros mais populares e simular a adição de um exemplar ao carrinho.
     . Critérios de Aceitação:
    - Na página procurar.php, caso não haja termos de pesquisa digitados, o sistema deve exibir os 5 livros com o maior valor na coluna total_leitores.
    - O botão "Comprar" deve apresentar um alerta interativo na interface ao ser acionado.

### US07 - Publicação e Navegação no Feed Social
     . Descrição: Como leitor autenticado, quero partilhar a minha opinião sobre um livro e navegar pelas abas do feed comunitário.
     . Critérios de Aceitação:
     - O leitor pode filtrar as publicações pelas abas Geral, Amigos, Minhas e Seguindo.
     - O formulário deve registar o ID do utilizador, nome do livro, comentário, percentagem de progresso e a categoria selecionada.

### US08 - Moderação do Próprio Conteúdo no Feed
     . Descrição: Como leitor, quero poder eliminar publicações que eu tenha criado anteriormente no feed.
     . Critérios de Aceitação:
     - A hiperligação "Excluir Publicação" só deve ser exibida nos cartões onde postagens_feed.usuario_id == $_SESSION['usuario_id'].
     - Ao acionar a remoção, o sistema deve apagar o registo correspondente e recarregar a aba ativa do feed.

### US09 - Central de Resenhas em Vídeo
     . Descrição: Como leitor, quero visualizar análises em vídeo integradas das obras disponíveis na plataforma.
     . Critérios de Aceitação:
     - O sistema deve processar a hiperligação informada na coluna url_video e extrair o código do YouTube para gerar a tag <iframe>.
     - Deve ser exibido um resumo explicativo textual ao lado do leitor de vídeo.
     - A caixa de pesquisa da página deve filtrar simultaneamente as resenhas em vídeo e as opiniões de texto da comunidade.

### US10 - Atendimento e Suporte ao Cliente
     . Descrição: Como utilizador ou visitante, quero enviar mensagens de suporte à equipa da Toca de Leitura.
     . Critérios de Aceitação:
     - O formulário em contato.php deve validar o preenchimento obrigatório dos campos nome, e-mail e mensagem.
     - Os dados recebidos devem ser gravados na tabela mensagens_contato com o respetivo carimbo de data/hora (criado_em).
### US11 - Painel Administrativo Restrito
    . Descrição: Como administrador, quero aceder a uma área exclusiva para visualizar as métricas e os utilizadores do sistema.
    . Critérios de Aceitação:
    - A função verificar_admin() em db.php deve validar se o utilizador autenticado possui is_admin = TRUE.
    - Utilizadores não autorizados devem ser redirecionados para a página inicial com o parâmetro ?erro=sem_permissao.
    - A opção de menu "Painel Admin" só deve ser visível no cabeçalho (header.php) para administradores autenticados.
     
### US12 - Gestão Global de Utilizadores e Conteúdos
    . Descrição: Como administrador, quero promover/rebaixar contas de utilizadores e moderar conteúdos inadequados na comunidade.
    . Critérios de Aceitação:
    - O painel deve permitir alternar a flag is_admin de qualquer utilizador registado.
    - O administrador pode remover qualquer postagem do feed comunitário ou conta de utilizador sem restrições de autoria.
    - O sistema deve proibir que o administrador ativo despromova ou elimine a sua própria conta enquanto estiver com a sessão ligada.



