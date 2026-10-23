--
-- PostgreSQL database dump
--

\restrict Me9w0JI656p1hK38afOKtxfSlWsShaUybsZ4ybBAR3g8AnbY44Sw7v2x7gpp2Po

-- Dumped from database version 18.6
-- Dumped by pg_dump version 18.6

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: historico_leitura; Type: TABLE; Schema: public; Owner: raposa
--

CREATE TABLE public.historico_leitura (
    id integer NOT NULL,
    usuario_id integer NOT NULL,
    livro_nome character varying(200) NOT NULL,
    genero character varying(50),
    tempo_leitura character varying(50),
    paginas_lidas integer DEFAULT 0,
    total_paginas integer DEFAULT 0,
    progresso_pct integer DEFAULT 0,
    atualizado_em timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.historico_leitura OWNER TO raposa;

--
-- Name: historico_leitura_id_seq; Type: SEQUENCE; Schema: public; Owner: raposa
--

CREATE SEQUENCE public.historico_leitura_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.historico_leitura_id_seq OWNER TO raposa;

--
-- Name: historico_leitura_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: raposa
--

ALTER SEQUENCE public.historico_leitura_id_seq OWNED BY public.historico_leitura.id;


--
-- Name: itens_pedido; Type: TABLE; Schema: public; Owner: raposa
--

CREATE TABLE public.itens_pedido (
    id integer NOT NULL,
    pedido_id integer NOT NULL,
    livro_id integer,
    quantidade integer NOT NULL,
    preco_unitario numeric(10,2) NOT NULL
);


ALTER TABLE public.itens_pedido OWNER TO raposa;

--
-- Name: itens_pedido_id_seq; Type: SEQUENCE; Schema: public; Owner: raposa
--

CREATE SEQUENCE public.itens_pedido_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.itens_pedido_id_seq OWNER TO raposa;

--
-- Name: itens_pedido_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: raposa
--

ALTER SEQUENCE public.itens_pedido_id_seq OWNED BY public.itens_pedido.id;


--
-- Name: livros; Type: TABLE; Schema: public; Owner: raposa
--

CREATE TABLE public.livros (
    id integer NOT NULL,
    titulo character varying(200) NOT NULL,
    autor character varying(100) NOT NULL,
    editora character varying(100),
    genero character varying(50) NOT NULL,
    preco numeric(10,2) DEFAULT 0.00 NOT NULL,
    estrelas numeric(2,1) DEFAULT 5.0,
    capa_url text,
    total_leitores integer DEFAULT 0
);


ALTER TABLE public.livros OWNER TO raposa;

--
-- Name: livros_id_seq; Type: SEQUENCE; Schema: public; Owner: raposa
--

CREATE SEQUENCE public.livros_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.livros_id_seq OWNER TO raposa;

--
-- Name: livros_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: raposa
--

ALTER SEQUENCE public.livros_id_seq OWNED BY public.livros.id;


--
-- Name: logs_acesso; Type: TABLE; Schema: public; Owner: raposa
--

CREATE TABLE public.logs_acesso (
    id integer NOT NULL,
    usuario_id integer,
    email_tentativa character varying(150) NOT NULL,
    ip_origem character varying(45),
    sucesso boolean DEFAULT true,
    data_acesso timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.logs_acesso OWNER TO raposa;

--
-- Name: logs_acesso_id_seq; Type: SEQUENCE; Schema: public; Owner: raposa
--

CREATE SEQUENCE public.logs_acesso_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.logs_acesso_id_seq OWNER TO raposa;

--
-- Name: logs_acesso_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: raposa
--

ALTER SEQUENCE public.logs_acesso_id_seq OWNED BY public.logs_acesso.id;


--
-- Name: mensagens_contato; Type: TABLE; Schema: public; Owner: raposa
--

CREATE TABLE public.mensagens_contato (
    id integer NOT NULL,
    usuario_id integer,
    nome character varying(100) NOT NULL,
    email character varying(150) NOT NULL,
    assunto character varying(255),
    mensagem text NOT NULL,
    resposta_admin text,
    data_resposta timestamp without time zone,
    status character varying(20) DEFAULT 'Pendente'::character varying,
    criado_em timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.mensagens_contato OWNER TO raposa;

--
-- Name: mensagens_contato_id_seq; Type: SEQUENCE; Schema: public; Owner: raposa
--

CREATE SEQUENCE public.mensagens_contato_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.mensagens_contato_id_seq OWNER TO raposa;

--
-- Name: mensagens_contato_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: raposa
--

ALTER SEQUENCE public.mensagens_contato_id_seq OWNED BY public.mensagens_contato.id;


--
-- Name: pedidos; Type: TABLE; Schema: public; Owner: raposa
--

CREATE TABLE public.pedidos (
    id integer NOT NULL,
    usuario_id integer NOT NULL,
    total numeric(10,2) NOT NULL,
    data_pedido timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    status character varying(50) DEFAULT 'ConcluÃ­do'::character varying
);


ALTER TABLE public.pedidos OWNER TO raposa;

--
-- Name: pedidos_id_seq; Type: SEQUENCE; Schema: public; Owner: raposa
--

CREATE SEQUENCE public.pedidos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pedidos_id_seq OWNER TO raposa;

--
-- Name: pedidos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: raposa
--

ALTER SEQUENCE public.pedidos_id_seq OWNED BY public.pedidos.id;


--
-- Name: postagens_feed; Type: TABLE; Schema: public; Owner: raposa
--

CREATE TABLE public.postagens_feed (
    id integer NOT NULL,
    usuario_id integer NOT NULL,
    livro_nome character varying(200) NOT NULL,
    comentario text NOT NULL,
    progresso integer DEFAULT 0,
    aba_categoria character varying(50) DEFAULT 'Geral'::character varying,
    criado_em timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.postagens_feed OWNER TO raposa;

--
-- Name: postagens_feed_id_seq; Type: SEQUENCE; Schema: public; Owner: raposa
--

CREATE SEQUENCE public.postagens_feed_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.postagens_feed_id_seq OWNER TO raposa;

--
-- Name: postagens_feed_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: raposa
--

ALTER SEQUENCE public.postagens_feed_id_seq OWNED BY public.postagens_feed.id;


--
-- Name: resenhas_videos; Type: TABLE; Schema: public; Owner: raposa
--

CREATE TABLE public.resenhas_videos (
    id integer NOT NULL,
    titulo_livro character varying(200) NOT NULL,
    url_video character varying(500) NOT NULL,
    resumo text
);


ALTER TABLE public.resenhas_videos OWNER TO raposa;

--
-- Name: resenhas_videos_id_seq; Type: SEQUENCE; Schema: public; Owner: raposa
--

CREATE SEQUENCE public.resenhas_videos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.resenhas_videos_id_seq OWNER TO raposa;

--
-- Name: resenhas_videos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: raposa
--

ALTER SEQUENCE public.resenhas_videos_id_seq OWNED BY public.resenhas_videos.id;


--
-- Name: usuarios; Type: TABLE; Schema: public; Owner: raposa
--

CREATE TABLE public.usuarios (
    id integer NOT NULL,
    nome character varying(100) NOT NULL,
    email character varying(150) NOT NULL,
    senha character varying(255) NOT NULL,
    is_admin boolean DEFAULT false,
    criado_em timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.usuarios OWNER TO raposa;

--
-- Name: usuarios_id_seq; Type: SEQUENCE; Schema: public; Owner: raposa
--

CREATE SEQUENCE public.usuarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuarios_id_seq OWNER TO raposa;

--
-- Name: usuarios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: raposa
--

ALTER SEQUENCE public.usuarios_id_seq OWNED BY public.usuarios.id;


--
-- Name: historico_leitura id; Type: DEFAULT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.historico_leitura ALTER COLUMN id SET DEFAULT nextval('public.historico_leitura_id_seq'::regclass);


--
-- Name: itens_pedido id; Type: DEFAULT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.itens_pedido ALTER COLUMN id SET DEFAULT nextval('public.itens_pedido_id_seq'::regclass);


--
-- Name: livros id; Type: DEFAULT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.livros ALTER COLUMN id SET DEFAULT nextval('public.livros_id_seq'::regclass);


--
-- Name: logs_acesso id; Type: DEFAULT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.logs_acesso ALTER COLUMN id SET DEFAULT nextval('public.logs_acesso_id_seq'::regclass);


--
-- Name: mensagens_contato id; Type: DEFAULT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.mensagens_contato ALTER COLUMN id SET DEFAULT nextval('public.mensagens_contato_id_seq'::regclass);


--
-- Name: pedidos id; Type: DEFAULT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.pedidos ALTER COLUMN id SET DEFAULT nextval('public.pedidos_id_seq'::regclass);


--
-- Name: postagens_feed id; Type: DEFAULT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.postagens_feed ALTER COLUMN id SET DEFAULT nextval('public.postagens_feed_id_seq'::regclass);


--
-- Name: resenhas_videos id; Type: DEFAULT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.resenhas_videos ALTER COLUMN id SET DEFAULT nextval('public.resenhas_videos_id_seq'::regclass);


--
-- Name: usuarios id; Type: DEFAULT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.usuarios ALTER COLUMN id SET DEFAULT nextval('public.usuarios_id_seq'::regclass);


--
-- Data for Name: historico_leitura; Type: TABLE DATA; Schema: public; Owner: raposa
--

COPY public.historico_leitura (id, usuario_id, livro_nome, genero, tempo_leitura, paginas_lidas, total_paginas, progresso_pct, atualizado_em) FROM stdin;
1	2	X-Men: Deus Ama, o Homem Mata - Volume 1	Mangás e HQs	3 dias	30	150	20	2026-10-21 11:26:18.993997
2	3	Estilhaça-me	Fantasia	7 dias	300	450	67	2026-10-21 11:30:17.912496
\.


--
-- Data for Name: itens_pedido; Type: TABLE DATA; Schema: public; Owner: raposa
--

COPY public.itens_pedido (id, pedido_id, livro_id, quantidade, preco_unitario) FROM stdin;
1	1	19	1	54.90
2	1	15	1	64.90
3	2	13	1	49.90
4	2	2	1	54.90
\.


--
-- Data for Name: livros; Type: TABLE DATA; Schema: public; Owner: raposa
--

COPY public.livros (id, titulo, autor, editora, genero, preco, estrelas, capa_url, total_leitores) FROM stdin;
2	Parte do Seu Mundo	Abby Jimenez	Arqueiro	Romance	54.90	4.7	https://imgs.search.brave.com/1MH-6EHn8_m7ZcaDsn8iFU7IH2Y0Xdf3J7EqDdwzPCI/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tLm1l/ZGlhLWFtYXpvbi5j/b20vaW1hZ2VzL0kv/NDFsQ2wxT1N6Y0wu/anBn	1850
3	Nem Te Conto	Emily Henry	Record	Romance	59.90	4.7	https://imgs.search.brave.com/iEsbhlnrzm7VpPfwruflFaP3yMN5O_3ZZqHuQ8yQP2w/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tLm1l/ZGlhLWFtYXpvbi5j/b20vaW1hZ2VzL0kv/NDFvWkVPc0FqVEwu/anBn	3100
4	Binding 13	Chloe Walsh	Bloom Brasil	Romance	69.90	4.7	https://imgs.search.brave.com/EYFO9bdte0fS09UyULu3nS25Q5-SN6t1l6fJv0lKMSs/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9odHRw/Mi5tbHN0YXRpYy5j/b20vRF9OUV9OUF85/MDc3ODMtTUxBODIx/NzM2MTg5NjJfMDIy/MDI1LU8ud2VicA	4100
5	Apostando no Amor	Lynn Painter	IntrÃ­nseca	Romance	49.90	4.8	https://imgs.search.brave.com/aCnB8e1DIMI7usc4bm--iZbOP84euPTyh1xJMrF3Kro/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tLm1l/ZGlhLWFtYXpvbi5j/b20vaW1hZ2VzL0kv/NDFndHNiTWx1Y0wu/anBn	2750
11	Corte de Espinhos e Rosas	Sarah J. Maas	Galera Record	Fantasia	59.90	4.8	https://imgs.search.brave.com/OgaikIQOmKap-UXmPvJq6MkzzzFT-iuiiNbJoLWQYSg/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9hY290/YXJicmFzaWwuY29t/LmJyL3dwLWNvbnRl/bnQvdXBsb2Fkcy8y/MDIxLzA3L2NvcnRl/LWRlLWVzcGluaG9z/LWUtcm9zYXMtY2Fw/YS02OTF4MTAyNC5q/cGc	4800
15	De Sangue e Cinzas	Jennifer L. Armentrout	Galera Record	Fantasia	64.90	4.8	https://imgs.search.brave.com/L9QiN-dM7zs1mqvExKN2xemSQEMl89W3rS41qF8-TGA/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9vc21l/bGhvcmVzbGl2cm9z/LmNvbS5ici93cC1j/b250ZW50L3VwbG9h/ZHMvMjAyMi8wMS9k/ZS1zYW5ndWUtZS1j/aW56YXMuanBn	3600
16	O Risco	S.T. Abby	Verus	Suspense	44.90	4.6	https://imgs.search.brave.com/qYLkPsR0_WpQTXPD2cFJFn5j0NsSFfHpfDqCQonqatk/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tLm1l/ZGlhLWFtYXpvbi5j/b20vaW1hZ2VzL0kv/NDFUMTJCTFJNQUwu/anBn	2900
17	Academia dos Casos Arquivados	Jennifer Lynn Barnes	Alt	Suspense	49.90	4.7	https://imgs.search.brave.com/lbFcSGoOwlKKjF-1GBfXKOQO-crqEq95k9jBNeS8DWw/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tLm1l/ZGlhLWFtYXpvbi5j/b20vaW1hZ2VzL0kv/NTFrVmU0c3Nydkwu/anBn	2100
18	A Empregada	Freida McFadden	Arqueiro	Suspense	42.90	4.7	https://imgs.search.brave.com/ebeSvN1GZ5lTHNMxXSFoMmbh9_j-xEiC9JkSuY98lT8/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9odHRw/Mi5tbHN0YXRpYy5j/b20vRF9RX05QXzJY/Xzg0NzU5OC1NTEI3/Nzk3NTQ0NjA5Nl8w/ODIwMjQtUC53ZWJw	4500
19	Jantar Secreto	Raphael Montes	Companhia das Letras	Suspense	54.90	4.6	https://imgs.search.brave.com/iVhh75RmQT-RoKt_AbcQVFd43TC6ZIKdcNfIbkEwsRc/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9odHRw/Mi5tbHN0YXRpYy5j/b20vRF9RX05QXzky/NzAyOC1NTEE4MjI5/ODA2NzM3NF8wMjIw/MjUtRi53ZWJw	3800
20	O Iluminado	Stephen King	Suma	Suspense	69.90	4.8	https://imgs.search.brave.com/V1BPGpdv_R4hbkFe3fbxpYzbXClAO1yriGDiSoljxmo/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9jZG4u/YXdzbGkuY29tLmJy/LzYwMHg0NTAvMjQ5/NS8yNDk1Nzg0L3By/b2R1dG8vMjcxNjYx/ODg4LzM1Mzc0Ni0x/LTV6Yzh6cmFhenYu/anBn	5100
21	IT: A Coisa	Stephen King	Suma	Terror	69.90	4.8	https://imgs.search.brave.com/A874EU0a7bIp9g-9QtBYn1g7ntS92-zo1yzmkGUp68Y/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWcu/b2x4LmNvbS5ici90/aHVtYnM3MDB4NTAw/Lzc2Lzc2NTY2NzUx/NDMzNjIzMi53ZWJw	5300
22	O Chamado de Cthulhu	H.P. Lovecraft	Hedra	Terror	39.90	4.7	https://imgs.search.brave.com/bsabIhdFwg7wfxAqbonKLBlQnHpLsdpBSDv45M-dW68/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWFn/ZXMtbmEuc3NsLWlt/YWdlcy1hbWF6b24u/Y29tL2ltYWdlcy9J/LzcxZXY0UEQwYlFM/LmpwZw	3400
23	Olhos Prateados	Scott Cawthon	IntrÃ­nseca	Terror	44.90	4.8	https://imgs.search.brave.com/bTQJ8cwexZcylxWv0KNZCrV_I7_aY3TnszANmnPjI7s/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9odHRw/Mi5tbHN0YXRpYy5j/b20vRF9RX05QXzJY/Xzc2OTIwNi1NTEI3/NTAwNDUyODc4MV8w/MzIwMjQtRS1vbGhv/cy1wcmF0ZWFkb3Mt/c2VyaWUtZml2ZS1u/aWdodHMtYXQtZnJl/ZGR5cy12b2wtMS1z/Y290dC1jYXd0aG9u/LWUta2lyYS1icmVl/ZC13cmlzbGV5LWxp/dnJvLWZpc2ljby53/ZWJw	2900
24	O Corvo	Edgar Allan Poe	DarkSide	Terror	34.90	4.6	https://imgs.search.brave.com/ClWJBvgwLuBRkoNLjOepC4YlqRdjt7DzOlFmijlp0qA/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tLm1l/ZGlhLWFtYXpvbi5j/b20vaW1hZ2VzL1Mv/YXBsdXMtbWVkaWEt/bGlicmFyeS1zZXJ2/aWNlLW1lZGlhL2I4/YmY2MTE4LTg3YzYt/NDAzNS1hODk0LTMy/OTcxMTIxZGU3My5f/X0NSMCwwLDMwMCwz/MDBfUFQwX1NYMzAw/X1YxX19fLnBuZw	4100
25	Jogos Mortais	Angela Marsons	Gutenberg	Terror	49.90	4.6	https://imgs.search.brave.com/G5zEvHgB42lP83lNkTFxFdWbqPQrMaKuQxJS4RXYiCw/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pLnpz/dC5jb20uYnIvdGh1/bWJzLzUxLzNhLzE2/Ly0xNDYxMzg2Mzk3/LmpwZw	2200
6	Harry Potter: A Pedra Filosofal	J.K. Rowling	Rocco	Ficção	44.90	4.9	https://imgs.search.brave.com/HPxalIyMin-mEPG9PbUG-67cz4xo3X9qcbGL5qJxzKA/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tLm1l/ZGlhLWFtYXpvbi5j/b20vaW1hZ2VzL0kv/MzFhTERkNzlSeEwu/anBn	5200
7	Maze Runner: Correr ou Morrer	James Dashner	V&R	Ficção	49.90	4.6	https://imgs.search.brave.com/drpKx0-3Td1gRX50HuHaAMWJY4GHLUJcLn9jtbpu-ls/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWFn/ZW5zLmVsaXZyb3Mu/aW5mby9KYW1lcy1E/YXNobmVyL0JhaXhh/ci1MaXZyby1Db3Jy/ZXItT3UtTW9ycmVy/LU1hemUtUnVubmVy/LVZvbC0xLUphbWVz/LURhc2huZXItRW0t/RXB1Yi1QZGYtTW9i/aS1PdS1MZXItT25s/aW5lX21lZGl1bS5q/cGc	2800
8	Duna	Frank Herbert	Aleph	Ficção	79.90	4.8	https://imgs.search.brave.com/b3agjkopmdSZBgHpAlfVl4c-ylE8ujSBquHF1SdrKRg/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9saXZy/YXJpYXB1YmxpY2Eu/Y29tLmJyL2NhcGEv/ZHVuYS1jcm9uaWNh/cy1kZS1kdW5hLWxp/dnJvLTEtZnJhbmst/aGVyYmVydC1wZGYt/QjAxNUVFNUpYNC53/ZWJw	3900
9	Percy Jackson e os Olimpianos: O Ladrão de Raios	Rick Riordan	IntrÃ­nseca	Ficção	49.90	4.8	https://imgs.search.brave.com/ONzy2bP92SH6iNwwqOErpcZUHWMvrQG166lKcI-WY-c/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9odHRw/Mi5tbHN0YXRpYy5j/b20vRF9OUV9OUF84/MTUxMDctTUxCNzQw/MDQ5NTA3ODhfMDEy/MDI0LVcud2VicA	4600
10	As Crà´nicas de Nárnia	C.S. Lewis	HarperCollins	Ficção	69.90	4.8	https://imgs.search.brave.com/w5yn26P00Lt6fjqPU4xp6t8V-FuuZ2_9hPZXczXihss/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tLm1l/ZGlhLWFtYXpvbi5j/b20vaW1hZ2VzL0kv/NTFTMkVRWHd3ekwu/anBn	4100
12	Era uma Vez um Coração Partido	Stephanie Garber	Gutenberg	Fantasia	54.90	4.8	https://imgs.search.brave.com/QW1xasUd-dCHwbLczZWbHOtY3WogdjhTQGBt-Zuizy4/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9ncnVw/b2F1dGVudGljYS5m/MWNkbi5jb20uYnIv/dmlldy8xNzYvMjQ0/L2ZhbHNlL3RydWUv/OTI0LmpwZw	3200
13	Estilhaça-me	Tahereh Mafi	Gutenberg	Fantasia	49.90	4.5	https://imgs.search.brave.com/iI2zLp891h0XB3-3lxSq43tEFi0pRYsCqlIeW-kbjko/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWFn/ZXMtbmEuc3NsLWlt/YWdlcy1hbWF6b24u/Y29tL2ltYWdlcy9J/LzcxM3Z3RjYxbW5M/LmpwZw	3900
14	Assistente do Vilão	Hannah Nicole Maehrer	Alt	Fantasia	59.90	4.8	https://imgs.search.brave.com/sU6ww6pYTX4CGLs2EnoCKhcjWS-1-pOND5pQCy3Qmxo/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9zdGF0/aWMuc2tlZWxvLmNv/bS9yZW1vdGUvMzIw/LzQ4MC8xMDAvaHR0/cHM6Ly9za29vYi5z/My5hbWF6b25hd3Mu/Y29tL2xpdnJvcy8x/MjI0MTU0OTAvQVNT/SVNURU5URV9ET19W/SUxBT18xNzA2MTI1/NTQ4MTIyNDE1NDkw/U0stVjExNzA2MTI1/NTUwQi5qcGc	2100
32	O Menino Maluquinho	Ziraldo	Melhoramentos	Literatura Infantil	32.90	4.8	https://imgs.search.brave.com/7VFz4A0GyhoLidD9eLf0dE5m-tbThCjP9fcJp0lBCDY/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9odHRw/Mi5tbHN0YXRpYy5j/b20vRF9RX05QXzJY/Xzg1ODA5NC1NTEI3/NTQxMjU4NzE3MV8w/MzIwMjQtRS53ZWJw	4900
34	A Bolsa Amarela	Lygia Bojunga	Casa Lygia Bojunga	Literatura Infantil	34.90	4.8	https://imgs.search.brave.com/p4yFPEUsuf9wLgWb6bBjeqSsI5b-H93ODeuL1EqyWS4/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9odHRw/Mi5tbHN0YXRpYy5j/b20vRF9RX05QXzJY/XzczMDgwNS1NTEI0/NjUwNjEyNTc4Nl8w/NjIwMjEtRS53ZWJw	3100
1	Táticas do Amor	Sarah Adams	IntrÃ­nseca	Romance	49.90	4.6	https://imgs.search.brave.com/iYJ2VMRB1ZtWRAy0BCkoqtPMHL_mB6ykEjii3b4f8dA/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tLm1l/ZGlhLWFtYXpvbi5j/b20vaW1hZ2VzL0kv/NDFlSjBsU2d0MUwu/anBn	2300
26	Jujutsu Kaisen - Volume 26	Gege Akutami	Panini	Mangás e HQs	39.90	5.0	https://imgs.search.brave.com/OfSEhI9vyS3VwjR3oKL9qMFoXs_j3l6CI5vP0DsNHpw/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pLmVi/YXlpbWcuY29tL2lt/YWdlcy9nL0gzMEFB/T1N3cjNwbUZBTE0v/cy1sNTAwLmpwZw	4800
27	Diário de uma Apotecária - Volume 01	Itsuki Nanao	Panini	Mangás e HQs	37.90	4.8	https://imgs.search.brave.com/nQsoNoHfN-zQdwig4lSPtUdYfj6vS-yLC0WzanMjVGY/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9sb2ph/dW5pY29yLmNvbS5i/ci93cC1jb250ZW50/L3VwbG9hZHMvMjAy/Ni8wMi91bmljb3It/cGFuaW5pLW1hbmdh/LWxpdnJvLWRpYXJp/b3MtZGUtdW1hLWFw/b3RlY2FyaWEta3Vz/dXJpeWEtbm8taGl0/b3JpZ290by0wMS1z/Y2FsZWQuanBn	3200
28	Overlord Vol. 01	Hugin Miyama	JBC	Mangás e HQs	34.90	4.9	https://imgs.search.brave.com/hS0Ou4V-AOk29ipNSFWWrPxyU7ee9fXjFTdXNV0KpOo/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pLmVi/YXlpbWcuY29tL2lt/YWdlcy9nL1VnTUFB/T1N3LTFSbDh6UWcv/cy1sNTAwLmpwZw	2500
29	X-Men: Deus Ama, o Homem Mata - Volume 1	Christopher Claremont	Panini	Mangás e HQs	69.90	4.8	https://imgs.search.brave.com/MpAnAHCK0EefNgqKWOaN0SLyLSfYUwBKkDZL8S8m7CE/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9saXZy/YXJpYWxveW9sYS5m/Yml0c3N0YXRpYy5u/ZXQvaW1nL3AveC1t/ZW4tZGV1cy1hbWEt/by1ob21lbS1tYXRh/LW1hcnZlbC1ncmFw/aGljLW5vdmVsLTI3/NzQ0My80NzIxODcu/anBnP3c9ODAwJmg9/ODAwJnY9bm8tY2hh/bmdlJnFzPWlnbm9y/ZQ	3900
30	Batman: A Corte das Corujas	Scott Snyder, Greg Capullo	Panini	Mangás e HQs	89.90	4.9	https://imgs.search.brave.com/VWPRG9269aFSriwSSrf4fr7TH1g8mNiCwbW25oeags8/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWFn/ZXMtbmEuc3NsLWlt/YWdlcy1hbWF6b24u/Y29tL2ltYWdlcy9J/LzcxWWdJcjhGNmdM/LmpwZw	5100
31	O Pequeno Prà­ncipe	Antoine de Saint-Exupéry	Agir	Literatura Infantil	29.90	4.8	https://imgs.search.brave.com/zv8kkhVfmI1rasLZgRILa0k7BJhL8APuK-H3Uc9yR5k/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tLm1l/ZGlhLWFtYXpvbi5j/b20vaW1hZ2VzL0kv/ODFEU2IzSjNaOUwu/anBn	5500
33	O Meu Pé de Laranja Lima	José Mauro de Vasconcelos	Melhoramentos	Literatura Infantil	39.90	4.8	https://imgs.search.brave.com/TPdc4BGXAzdJp2uzh6vhdiivllPjJfJmFav1ClIO994/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9jZG4u/ZG9vY2Euc3RvcmUv/ODEyNDMvcHJvZHVj/dHMvdTR3bW1zaHhs/ZnQxbWJ3amE1MHlo/ZHY0YXk5cXBya2J6/N3JvXzY0MHg2NDAr/ZmlsbF9mZmZmZmYu/anBnP3Y9MTY3MTI5/OTM0NA	4200
35	Reinações de Narizinho	Monteiro Lobato	Globo Livros	Literatura Infantil	44.90	4.6	https://imgs.search.brave.com/k4w0vBJ3ZQYN5N0mwrwV7t6_92NilvqDf71aQpoUxT8/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9odHRw/Mi5tbHN0YXRpYy5j/b20vRF9RX05QXzJY/XzkxNjg1OC1NTFU1/MDQ1NDU4Mjc0OV8w/NjIwMjItRS53ZWJw	3800
36	O Veneno da Montanha	Bianca Jung	\N	Fantasia	48.90	4.6	https://imgs.search.brave.com/-7yQ7f-UEMzF09Ok3lEwmScO_rYcOqkLMvMaSIcCu3c/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9jZG4u/YXdzbGkuY29tLmJy/LzYwMHgxMDAwLzI3/LzI3MTkyL3Byb2R1/dG8vMTYzMjI5Mzk0/LzEyMDcxMThlMjEu/anBn	0
\.


--
-- Data for Name: logs_acesso; Type: TABLE DATA; Schema: public; Owner: raposa
--

COPY public.logs_acesso (id, usuario_id, email_tentativa, ip_origem, sucesso, data_acesso) FROM stdin;
1	\N	admin@gmail.com	::1	f	2026-10-16 13:42:11.66414
2	\N	admin@gmail.com	::1	f	2026-10-16 13:42:24.109452
3	\N	admin@gmail.com	::1	f	2026-10-16 13:44:30.903938
4	\N	admin@gmail.com	::1	f	2026-10-16 13:44:39.278465
5	\N	admin@gmail.com	::1	f	2026-10-16 13:44:46.524996
6	\N	admin@gmail.com	::1	f	2026-10-16 13:47:22.737213
7	\N	admin@gmail.com	::1	f	2026-10-16 13:47:28.493859
8	\N	admin@gmail.com	::1	f	2026-10-16 13:47:49.95139
9	2	admin2@gmail.com	::1	t	2026-10-16 13:52:17.225784
10	2	admin2@gmail.com	::1	t	2026-10-21 08:55:26.863998
11	\N	beatriz@13	::1	f	2026-10-21 08:56:55.617039
12	2	admin2@gmail.com	::1	t	2026-10-21 08:57:02.516931
13	3	lu@gmail.com	::1	t	2026-10-21 11:27:16.119459
14	2	admin2@gmail.com	::1	t	2026-10-21 11:30:56.168096
\.


--
-- Data for Name: mensagens_contato; Type: TABLE DATA; Schema: public; Owner: raposa
--

COPY public.mensagens_contato (id, usuario_id, nome, email, assunto, mensagem, resposta_admin, data_resposta, status, criado_em) FROM stdin;
1	3	Lulu Manhani	lu@gmail.com	Elogio	O site ta muito legal!!!!	:):)	2026-10-21 11:39:00.449889	Respondida	2026-10-21 11:30:41.033613
\.


--
-- Data for Name: pedidos; Type: TABLE DATA; Schema: public; Owner: raposa
--

COPY public.pedidos (id, usuario_id, total, data_pedido, status) FROM stdin;
2	3	104.80	2026-10-21 11:30:46.381991	Enviado
1	2	119.80	2026-10-21 11:26:29.536801	Enviado
\.


--
-- Data for Name: postagens_feed; Type: TABLE DATA; Schema: public; Owner: raposa
--

COPY public.postagens_feed (id, usuario_id, livro_nome, comentario, progresso, aba_categoria, criado_em) FROM stdin;
1	2	Estilhaça-me	Muito bom esse livro!	60	Recomendação	2026-10-21 10:25:54.498738
2	2	duna	To gostando bastante, mas o livro é muito longo	19	Geral	2026-10-21 10:26:31.451441
3	2	X-Men: Deus Ama, o Homem Mata - Volume 1	Atualizei meu progresso de leitura! Já li 30 de 150 páginas (20%).	20	Geral	2026-10-21 10:44:19.181641
4	2	X-Men: Deus Ama, o Homem Mata - Volume 1	Atualizei meu progresso de leitura! Já li 30 de 150 páginas (20%).	20	Geral	2026-10-21 11:26:19.009111
5	3	Estilhaça-me	To gostando bastante! Bem diferente dos outros que eu ja li!	20	Recomendação	2026-10-21 11:28:25.197521
6	3	Estilhaça-me	Amando!	67	Geral	2026-10-21 11:29:38.766978
7	3	Estilhaça-me	Atualizei meu progresso de leitura! Já li 300 de 450 páginas (67%).	67	Geral	2026-10-21 11:30:17.918414
\.


--
-- Data for Name: resenhas_videos; Type: TABLE DATA; Schema: public; Owner: raposa
--

COPY public.resenhas_videos (id, titulo_livro, url_video, resumo) FROM stdin;
1	TÃ¡ticas do Amor	https://www.youtube.com/watch?v=zEOYc7lP8DA	Uma resenha apaixonante sobre a comÃ©dia romÃ¢ntica de Sarah Adams, destacando o desenvolvimento dos personagens e a leveza da histÃ³ria.
2	Parte do Seu Mundo	https://www.youtube.com/watch?v=FybviD4rTFY	AnÃ¡lise completa da obra de Abby Jimenez: uma histÃ³ria emocionante sobre diferenÃ§as sociais, recomeÃ§os e amor verdadeiro.
3	Nem Te Conto	https://www.youtube.com/watch?v=ld6bYPsqgM0	Confira os principais pontos positivos da escrita envolvente de Emily Henry nesta resenha cheia de diÃ¡logos afiados e humor.
4	Binding 13	https://www.youtube.com/watch?v=LtSrZoguL4U	AnÃ¡lise aprofundada sobre o romance de Chloe Walsh, abordando temas sensÃ­veis, superaÃ§Ã£o e o companheirismo dos protagonistas.
5	Apostando no Amor	https://www.youtube.com/watch?v=sEm7p9OApsM	Resenha bem-humorada sobre a obra de Lynn Painter, explorando a dinÃ¢mica do clichÃª de inimigos para amantes.
6	Harry Potter: A Pedra Filosofal	https://www.youtube.com/watch?v=EfdKgRE6JZM	Resenha nostÃ¡lgica sobre o inÃ­cio do mundo bruxo de J.K. Rowling, apresentando Hogwarts, amizade e magia inocente.
7	Maze Runner: Correr ou Morrer	https://www.youtube.com/watch?v=MK8c3WaeQYU	AnÃ¡lise do ritmo eletrizante da distopia de James Dashner: a Clareira, os Verdugos e o enigma do labirinto.
8	Duna	https://www.youtube.com/watch?v=Vy8hkKkTrLE	Resenha completa sobre a monumental Ã³pera espacial de Frank Herbert: ecologia, polÃ­tica, religiÃ£o e o destino de Paul Atreides.
9	Percy Jackson e os Olimpianos: O LadrÃ£o de Raios	https://www.youtube.com/watch?v=-nowadSIL98	AnÃ¡lise do clÃ¡ssico de Rick Riordan sobre a mitologia grega adaptada para o mundo moderno no Acampamento Meio-Sangue.
10	As CrÃ´nicas de NÃ¡rnia	https://www.youtube.com/watch?v=PMSRrB7H9lU	Resenha sobre a saga fantÃ¡stica de C.S. Lewis, cobrindo o guarda-roupa mÃ¡gico, Aslan e o combate entre o bem e o mal.
11	Corte de Espinhos e Rosas	https://www.youtube.com/watch?v=LHCLyNrKoXE	VÃ­deo anÃ¡lise do primeiro livro da famosa saga de Sarah J. Maas. Entenda a construÃ§Ã£o do universo de faerie e a jornada de Feyre.
12	Era uma Vez um CoraÃ§Ã£o Partido	https://www.youtube.com/watch?v=tUmwC3755XM	Resenha sobre o spin-off mÃ¡gico de Caraval por Stephanie Garber, destacando a estÃ©tica de conto de fadas e as maldiÃ§Ãµes do amor.
13	EstilhaÃ§a-me	https://www.youtube.com/watch?v=pz3Iia4pNtQ	AnÃ¡lise da distopia romÃ¢ntica de Tahereh Mafi: a poÃ©tica do texto, o toque mortal de Juliette e os dilemas do poder.
14	Assistente do VilÃ£o	https://www.youtube.com/watch?v=yXYPrZgLDS4	Resenha divertida sobre o sucesso de Hannah Nicole Maehrer, misturando comÃ©dia, fantasia e romance slow burn.
15	De Sangue e Cinzas	https://www.youtube.com/watch?v=HQTsF1qheCo	VÃ­deo opinativo sobre a saga de Jennifer L. Armentrout, focando nas reviravoltas marcantes, segredos do reino e quÃ­mica dos personagens.
16	O Risco	https://www.youtube.com/watch?v=FIW3-IG0558	Resenha chocante sobre a sÃ©rie Mindf*ck de S.T. Abby, mesclando vinganÃ§a brutal, suspense psicolÃ³gico e romance dark.
17	Academia dos Casos Arquivados	https://www.youtube.com/watch?v=36ov0Xqy5zo	AnÃ¡lise do mistÃ©rio de Jennifer Lynn Barnes sobre adolescentes ajudando o FBI a resolver crimes do passado.
18	A Empregada	https://www.youtube.com/watch?v=J9xgCTIat1o	Resenha sobre o bestseller de Freida McFadden: segredos de famÃ­lia, tensÃ£o domÃ©stica e reviravoltas imprevisÃ­veis.
19	Jantar Secreto	https://www.youtube.com/watch?v=0_uSN_-WGZo	AnÃ¡lise perturbadora do thriller nacional de Raphael Montes, abordando os limites Ã©ticos dos personagens.
20	O Iluminado	https://www.youtube.com/watch?v=JgfvH22MvF0	Resenha profunda da obra-prima do terror de Stephen King sobre o Hotel Overlook, isolamento e a loucura de Jack Torrance.
21	IT: A Coisa	https://www.youtube.com/watch?v=q55RnQTTvOM	Resenha detalhada do clÃ¡ssico de Stephen King. AnÃ¡lise sobre o medo na infÃ¢ncia, a amizade e a entidade em Derry.
22	O Chamado de Cthulhu	https://www.youtube.com/watch?v=ZWv3XhwYF24	VÃ­deo anÃ¡lise sobre a obra de H.P. Lovecraft e os fundamentos do horror cÃ³smico, loucura e a mitologia dos Antigos.
23	Olhos Prateados	https://www.youtube.com/watch?v=0ERJ9xiHXgA	Resenha sobre o livro oficial de Five Nights at Freddyâ€™s por Scott Cawthon, explorando o mistÃ©rio dos animatrÃ´nicos.
24	O Corvo	https://www.youtube.com/watch?v=ltwzkoucn60	AnÃ¡lise poÃ©tica e temÃ¡tica do poema sombrio de Edgar Allan Poe, abordando o luto, a melancolia e o desespero.
25	Jogos Mortais	https://www.youtube.com/watch?v=blqDIB172Wg	Resenha do thriller de Angela Marsons, com foco nas investigaÃ§Ãµes da detetive Kim Stone e na mente do assassino.
26	Jujutsu Kaisen - Volume 26	https://www.youtube.com/watch?v=W7pi30UekVE	AnÃ¡lise do clÃ­max das batalhas no volume 26 do mangÃ¡ de Gege Akutami, comentando a arte e o impacto no enredo.
27	DiÃ¡rio de uma ApotecÃ¡ria - Volume 01	https://www.youtube.com/watch?v=ldw5H6yCPxY	Resenha sobre as intrigas da corte imperial e o intelecto afiado da jovem Maomao nesta adaptaÃ§Ã£o em mangÃ¡.
28	Overlord Vol. 01	https://www.youtube.com/watch?v=T4MFz5LUPPc	VÃ­deo review do volume inicial do mangÃ¡/light novel, analisando a premissa isekai do soberano Ainz Ooal Gown.
29	X-Men: Deus Ama, o Homem Mata - Volume 1	https://www.youtube.com/watch?v=_v892Rg52EY	Resenha crÃ­tica da histÃ³rica graphic novel dos X-Men escrita por Chris Claremont, debatendo preconceito e heroÃ­smo.
30	Batman: A Corte das Corujas	https://www.youtube.com/watch?v=H3TpJ_neTnA	AnÃ¡lise de uma das melhores histÃ³rias do Batman escrita por Scott Snyder, explorando a sociedade secreta de Gotham.
31	O Pequeno PrÃ­ncipe	https://www.youtube.com/watch?v=4PQlBlpo5dI&t=336s	Resenha reflexiva sobre a obra imortal de Antoine de Saint-ExupÃ©ry, com foco nas liÃ§Ãµes sobre amor, amizade e humanidade.
32	O Menino Maluquinho	https://www.youtube.com/watch?v=8rDnr4QpPTw	VÃ­deo em homenagem Ã  obra de Ziraldo, ressaltando o valor da infÃ¢ncia livre, criatividade e afeto na literatura brasileira.
33	O Meu PÃ© de Laranja Lima	https://www.youtube.com/watch?v=Mh-MGmcI5nI	Resenha emocionante sobre a marcante histÃ³ria de ZezÃ© e Minguinho por JosÃ© Mauro de Vasconcelos.
34	A Bolsa Amarela	https://www.youtube.com/watch?v=TELa4VKxc5E	AnÃ¡lise da obra clÃ¡ssica infantil de Lygia Bojunga sobre imaginaÃ§Ã£o, vontade de crescer e aceitaÃ§Ã£o pessoal.
35	ReinaÃ§Ãµes de Narizinho	https://www.youtube.com/watch?v=4LzrSNi3JII	Resenha sobre o marco inicial do SÃ­tio do Pica-Pau Amarelo por Monteiro Lobato, destacando a fantasia e o folclore.
\.


--
-- Data for Name: usuarios; Type: TABLE DATA; Schema: public; Owner: raposa
--

COPY public.usuarios (id, nome, email, senha, is_admin, criado_em) FROM stdin;
1	Administrador	admin@gmail.com	$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1q9lA6n/K3d5D2oXJ1X2k8b3H1f7H4m	t	2026-10-16 08:59:09.477385
2	Admin	admin2@gmail.com	$2y$12$WVyURMc1/u6f2jPd4yTCze81VwChwGWWTCUFyaUxa4mU4u8Pwrr2G	t	2026-10-16 13:50:30.016836
3	Lulu Manhani	lu@gmail.com	$2y$12$2NrCy/jAFOxpSS5tl.1euOiN3t3RSmZ75ToHFldnVnsjunlarH/vq	f	2026-10-21 11:27:07.546591
\.


--
-- Name: historico_leitura_id_seq; Type: SEQUENCE SET; Schema: public; Owner: raposa
--

SELECT pg_catalog.setval('public.historico_leitura_id_seq', 2, true);


--
-- Name: itens_pedido_id_seq; Type: SEQUENCE SET; Schema: public; Owner: raposa
--

SELECT pg_catalog.setval('public.itens_pedido_id_seq', 4, true);


--
-- Name: livros_id_seq; Type: SEQUENCE SET; Schema: public; Owner: raposa
--

SELECT pg_catalog.setval('public.livros_id_seq', 36, true);


--
-- Name: logs_acesso_id_seq; Type: SEQUENCE SET; Schema: public; Owner: raposa
--

SELECT pg_catalog.setval('public.logs_acesso_id_seq', 14, true);


--
-- Name: mensagens_contato_id_seq; Type: SEQUENCE SET; Schema: public; Owner: raposa
--

SELECT pg_catalog.setval('public.mensagens_contato_id_seq', 1, true);


--
-- Name: pedidos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: raposa
--

SELECT pg_catalog.setval('public.pedidos_id_seq', 2, true);


--
-- Name: postagens_feed_id_seq; Type: SEQUENCE SET; Schema: public; Owner: raposa
--

SELECT pg_catalog.setval('public.postagens_feed_id_seq', 7, true);


--
-- Name: resenhas_videos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: raposa
--

SELECT pg_catalog.setval('public.resenhas_videos_id_seq', 35, true);


--
-- Name: usuarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: raposa
--

SELECT pg_catalog.setval('public.usuarios_id_seq', 3, true);


--
-- Name: historico_leitura historico_leitura_pkey; Type: CONSTRAINT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.historico_leitura
    ADD CONSTRAINT historico_leitura_pkey PRIMARY KEY (id);


--
-- Name: itens_pedido itens_pedido_pkey; Type: CONSTRAINT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.itens_pedido
    ADD CONSTRAINT itens_pedido_pkey PRIMARY KEY (id);


--
-- Name: livros livros_pkey; Type: CONSTRAINT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.livros
    ADD CONSTRAINT livros_pkey PRIMARY KEY (id);


--
-- Name: livros livros_titulo_key; Type: CONSTRAINT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.livros
    ADD CONSTRAINT livros_titulo_key UNIQUE (titulo);


--
-- Name: logs_acesso logs_acesso_pkey; Type: CONSTRAINT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.logs_acesso
    ADD CONSTRAINT logs_acesso_pkey PRIMARY KEY (id);


--
-- Name: mensagens_contato mensagens_contato_pkey; Type: CONSTRAINT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.mensagens_contato
    ADD CONSTRAINT mensagens_contato_pkey PRIMARY KEY (id);


--
-- Name: pedidos pedidos_pkey; Type: CONSTRAINT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.pedidos
    ADD CONSTRAINT pedidos_pkey PRIMARY KEY (id);


--
-- Name: postagens_feed postagens_feed_pkey; Type: CONSTRAINT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.postagens_feed
    ADD CONSTRAINT postagens_feed_pkey PRIMARY KEY (id);


--
-- Name: resenhas_videos resenhas_videos_pkey; Type: CONSTRAINT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.resenhas_videos
    ADD CONSTRAINT resenhas_videos_pkey PRIMARY KEY (id);


--
-- Name: usuarios usuarios_email_key; Type: CONSTRAINT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_email_key UNIQUE (email);


--
-- Name: usuarios usuarios_pkey; Type: CONSTRAINT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);


--
-- Name: historico_leitura historico_leitura_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.historico_leitura
    ADD CONSTRAINT historico_leitura_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;


--
-- Name: itens_pedido itens_pedido_livro_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.itens_pedido
    ADD CONSTRAINT itens_pedido_livro_id_fkey FOREIGN KEY (livro_id) REFERENCES public.livros(id) ON DELETE SET NULL;


--
-- Name: itens_pedido itens_pedido_pedido_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.itens_pedido
    ADD CONSTRAINT itens_pedido_pedido_id_fkey FOREIGN KEY (pedido_id) REFERENCES public.pedidos(id) ON DELETE CASCADE;


--
-- Name: logs_acesso logs_acesso_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.logs_acesso
    ADD CONSTRAINT logs_acesso_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;


--
-- Name: mensagens_contato mensagens_contato_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.mensagens_contato
    ADD CONSTRAINT mensagens_contato_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE SET NULL;


--
-- Name: pedidos pedidos_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.pedidos
    ADD CONSTRAINT pedidos_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;


--
-- Name: postagens_feed postagens_feed_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: raposa
--

ALTER TABLE ONLY public.postagens_feed
    ADD CONSTRAINT postagens_feed_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict Me9w0JI656p1hK38afOKtxfSlWsShaUybsZ4ybBAR3g8AnbY44Sw7v2x7gpp2Po

