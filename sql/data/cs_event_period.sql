--
-- PostgreSQL database dump
--

-- Started on 2008-03-27 10:25:04 YEKT

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- TOC entry 2247 (class 0 OID 0)
-- Dependencies: 1825
-- Name: cs_event_period_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_event_period_id_seq', 4, true);


--
-- TOC entry 2244 (class 0 OID 125930)
-- Dependencies: 1826
-- Data for Name: cs_event_period; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_event_period (id, name, description) FROM stdin;
1	Ежедневно	Ежедневное событие
2	Еженедельно	Ежеденельное событие
3	Ежемесячно	Ежемесячное событие
4	Ежегодно	Ежегодное событие
\.


-- Completed on 2008-03-27 10:25:04 YEKT

--
-- PostgreSQL database dump complete
--

