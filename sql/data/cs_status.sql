--
-- PostgreSQL database dump
--

-- Started on 2008-01-16 16:19:58 YEKT

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- TOC entry 2098 (class 0 OID 0)
-- Dependencies: 1665
-- Name: cs_status_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_status_id_seq', 14, true);


--
-- TOC entry 2095 (class 0 OID 25110)
-- Dependencies: 1554
-- Data for Name: cs_status; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_status (id, name, description) FROM stdin;
13	ДП_Удалён	Дочерний процесс удлён
12	ДП_Ошибка	Произошла ошибка в дочернем процессе
11	ДП_Завершен	Дочерний процесс завершен
10	ДП_Ожидает	Дочерний процесс ожидает некого события
5	Ошибка	Произошла ошибка
9	ДП_Прерван	Дочериний процесс прерван
14	ДП_Пропущен	Дочерний процесс пропущен
7	Пропущен	Процесс или действие пропушено
4	Завершен	Успешно завершено
2	Прерван	Прервано пользователем
6	Удалён	Процесс удалён
3	Ожидает	Ожидает некоего события
1	Выполняется	В процессе выполнения
8	ДП_Выполняется	Дочерние процесс выполняется
\.


-- Completed on 2008-01-16 16:19:58 YEKT

--
-- PostgreSQL database dump complete
--

