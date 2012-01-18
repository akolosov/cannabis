--
-- PostgreSQL database dump
--

-- Started on 2008-01-16 16:19:45 YEKT

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- TOC entry 2098 (class 0 OID 0)
-- Dependencies: 1664
-- Name: cs_sign_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_sign_id_seq', 3, true);


--
-- TOC entry 2095 (class 0 OID 25171)
-- Dependencies: 1569
-- Data for Name: cs_sign; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_sign (id, name, description) FROM stdin;
3	Скрытый	Скрытое значение свойство
2	Только чтение	Разрешено только чтение значение свойства
1	Чтение и Запись	Разрешено чтение и запись значение свойства
\.


-- Completed on 2008-01-16 16:19:45 YEKT

--
-- PostgreSQL database dump complete
--

