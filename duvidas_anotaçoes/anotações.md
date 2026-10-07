Criando a tabela, pesquisei como eu faria pra salvar automaticamente a data e hora exata que o registro foi inserido na tabela do banco de dados. E apareceu o seguinte:

criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP

Esse trecho define uma coluna no banco de dados que salva automaticamente a data e a hora exatas em que um registro foi inserido na tabela.

Detalhamento de cada parte:
criado_em: É o nome da coluna (equivalente ao created_at em inglês).

TIMESTAMP: É o tipo de dado. Armazena data e hora juntas no formato AAAA-MM-DD HH:MM:SS (por exemplo, 2026-10-02 08:42:00).

DEFAULT CURRENT_TIMESTAMP: Define o valor padrão (default). Se você fizer um INSERT sem especificar nada para a coluna criado_em, o PostgreSQL vai pegar a data e hora atuais do servidor e preenchê-la automaticamente.