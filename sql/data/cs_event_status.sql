--
-- PostgreSQL database dump
--

-- Started on 2008-04-08 09:14:19 YEKST

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- TOC entry 2248 (class 0 OID 0)
-- Dependencies: 1802
-- Name: cs_event_status_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_event_status_id_seq', 6, true);


--
-- TOC entry 2245 (class 0 OID 125579)
-- Dependencies: 1803
-- Data for Name: cs_event_status; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_event_status (id, name, description) FROM stdin;
1	Создано	Только создано
2	В ожидании	В ожидании события
3	В процессе	В процессе выполнения
4	Завершено	Уже завершено
5	Отменено	Отменено пользователем
6	Удалено	Помечено на удаление
\.


-- Completed on 2008-04-08 09:14:19 YEKST

--
-- PostgreSQL database dump complete
--

