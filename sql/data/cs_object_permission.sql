--
-- PostgreSQL database dump
--

-- Started on 2008-03-27 09:07:04 YEKT

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- TOC entry 2247 (class 0 OID 0)
-- Dependencies: 1801
-- Name: cs_object_permission_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_object_permission_id_seq', 5, true);


--
-- TOC entry 2244 (class 0 OID 125559)
-- Dependencies: 1802
-- Data for Name: cs_object_permission; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_object_permission (id, name, description) FROM stdin;
1	Нет доступа	Доступ к объекту запрещен
2	Только чтение	Разрешено только чтение объекта
3	Чтение и Запись	Разрешено чтение и изменение свойств объекта
4	Чтение, Запись и Удаление	Разрешено чтение, изнменение свойств объекта и пометка на удаление
5	Полный доступ	Разрешены любые доступные действия над объектом
\.


-- Completed on 2008-03-27 09:07:04 YEKT

--
-- PostgreSQL database dump complete
--

