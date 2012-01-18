--
-- PostgreSQL database dump
--

-- Started on 2008-01-16 16:22:54 YEKT

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- TOC entry 2099 (class 0 OID 0)
-- Dependencies: 1578
-- Name: cs_action_type_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_action_type_id_seq', 8, true);


--
-- TOC entry 2096 (class 0 OID 25070)
-- Dependencies: 1546
-- Data for Name: cs_action_type; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_action_type (id, name, description, in_use) FROM stdin;
1	Начало	Начало процесса	t
2	Действие	Простое действие	t
3	Переключение	Логический переключатель	t
6	Конец	Конец процесса	t
7	Самостоятельное	Самостоятельное действие	t
4	Разделение	Разделение на несколько действий	f
5	Объединение	Объединение нескольких процессов	f
8	Информация	Информационное действие	t
\.


-- Completed on 2008-01-16 16:22:55 YEKT

--
-- PostgreSQL database dump complete
--

