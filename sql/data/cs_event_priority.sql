--
-- PostgreSQL database dump
--

-- Started on 2008-03-27 10:05:15 YEKT

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- TOC entry 2247 (class 0 OID 0)
-- Dependencies: 1717
-- Name: cs_event_priority_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_event_priority_id_seq', 6, true);


--
-- TOC entry 2244 (class 0 OID 61029)
-- Dependencies: 1644
-- Data for Name: cs_event_priority; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_event_priority (id, name, description) FROM stdin;
1	Низкий	Низкий приоритет
2	Обычный	Обычный  приоритет
3	Средний	Средний приоритет
4	Высокий	Высокий  приоритет
5	Очень Высокий	Очень Высокий  приоритет
6	Наивысший	Наивысший  приоритет
\.


-- Completed on 2008-03-27 10:05:15 YEKT

--
-- PostgreSQL database dump complete
--

