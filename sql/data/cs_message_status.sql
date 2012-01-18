--
-- PostgreSQL database dump
--

-- Started on 2008-04-08 09:14:37 YEKST

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- TOC entry 2248 (class 0 OID 0)
-- Dependencies: 1800
-- Name: cs_message_status_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_message_status_id_seq', 7, true);


--
-- TOC entry 2245 (class 0 OID 125569)
-- Dependencies: 1801
-- Data for Name: cs_message_status; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_message_status (id, name, description) FROM stdin;
1	Создано	Сообщение создано
2	Отправлено	Сообщение отправлено
3	Принято	Сообщение принято
4	Прочтено	Сообщение прочтено
5	Отправлено еще раз	Сообщение отправлено еще раз
6	Принято еще раз	Сообщение принято еще раз
7	Удалено	Сообщение помечено на удаление
\.


-- Completed on 2008-04-08 09:14:37 YEKST

--
-- PostgreSQL database dump complete
--

