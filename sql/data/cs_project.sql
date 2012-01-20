--
-- PostgreSQL database dump
--

-- Dumped from database version 9.1.2
-- Dumped by pg_dump version 9.1.2
-- Started on 2012-01-20 11:13:39 YEKT

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

--
-- TOC entry 2710 (class 0 OID 0)
-- Dependencies: 322
-- Name: cs_project_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_project_id_seq', 1, true);


--
-- TOC entry 2707 (class 0 OID 18874)
-- Dependencies: 176
-- Data for Name: cs_project; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_project (id, name, description, is_permanent, author_id, version, created_at, activated_at, is_active, is_system) FROM stdin;
1	Главное предприятие	Самое главное предприятие	t	1	1	2012-01-20 11:10:32	2012-01-20 11:12:33	t	f
\.


-- Completed on 2012-01-20 11:13:39 YEKT

--
-- PostgreSQL database dump complete
--

