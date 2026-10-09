```mermaid

erDiagram
    USUARIOS {
        int id PK "NOT NULL"
        string nome "NOT NULL"
        string email "NOT NULL UNIQUE"
        string senha "NOT NULL"
        boolean is_admin "DEFAULT false"
        timestamp criado_em "DEFAULT CURRENT_TIMESTAMP"
    }

    LIVROS {
        int id PK "NOT NULL"
        string titulo "NOT NULL UNIQUE"
        string autor "NOT NULL"
        string editora "NULL"
        string genero "NOT NULL"
        decimal preco "DEFAULT 0.00"
        int estrelas "DEFAULT 5"
        string capa_url "NULL"
        int total_leitores "DEFAULT 0"
    }

    HISTORICO_LEITURA {
        int id PK "NOT NULL"
        int usuario_id FK "NOT NULL"
        string livro_nome "NOT NULL"
        string genero "NULL"
        string tempo_leitura "NULL"
        int paginas_lidas "DEFAULT 0"
        int total_paginas "DEFAULT 0"
        int progresso_pct "DEFAULT 0"
        timestamp atualizado_em "DEFAULT CURRENT_TIMESTAMP"
    }

    POSTAGENS_FEED {
        int id PK "NOT NULL"
        int usuario_id FK "NOT NULL"
        string livro_nome "NOT NULL"
        string comentario "NOT NULL"
        int progresso "DEFAULT 0"
        string aba_categoria "DEFAULT Geral"
        timestamp criado_em "DEFAULT CURRENT_TIMESTAMP"
    }

    RESENHAS_VIDEOS {
        int id PK "NOT NULL"
        string titulo_livro "NOT NULL"
        string url_video "NOT NULL"
        string resumo "NULL"
    }

    MENSAGENS_CONTATO {
        int id PK "NOT NULL"
        string nome "NOT NULL"
        string email "NOT NULL"
        string mensagem "NOT NULL"
        timestamp criado_em "DEFAULT CURRENT_TIMESTAMP"
    }

    USUARIOS ||--o{ HISTORICO_LEITURA : regista
    USUARIOS ||--o{ POSTAGENS_FEED : publica
    LIVROS ||--o{ HISTORICO_LEITURA : contem
    LIVROS ||--o{ POSTAGENS_FEED : referencia
    LIVROS ||--o{ RESENHAS_VIDEOS : possui