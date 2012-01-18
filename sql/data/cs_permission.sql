--
-- PostgreSQL database dump
--

-- Started on 2008-01-16 16:20:24 YEKT

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- TOC entry 2098 (class 0 OID 0)
-- Dependencies: 1609
-- Name: cs_permission_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_permission_id_seq', 4, true);


--
-- TOC entry 2095 (class 0 OID 25017)
-- Dependencies: 1539
-- Data for Name: cs_permission; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_permission (id, name, description) FROM stdin;
1	Полные права	Создание, изменение, удаление и администрирование во всех модулях
3	Средние права	Создание, изменение, удаление в модулях Моделирования и администрирование модулях Выполнения
2	Простые права	Чтение и запись в модулях Выполнения
4	Особые права	Простые права + Заполнение книги менеджера
\.


-- Completed on 2008-01-16 16:20:24 YEKT

--
-- PostgreSQL database dump complete
--

