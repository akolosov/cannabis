--
-- PostgreSQL database dump
--

-- Dumped from database version 9.1.2
-- Dumped by pg_dump version 9.1.2
-- Started on 2012-01-20 10:56:03 YEKT

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

--
-- TOC entry 2706 (class 0 OID 0)
-- Dependencies: 296
-- Name: cs_post_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_post_id_seq', 3, true);


--
-- TOC entry 2703 (class 0 OID 18821)
-- Dependencies: 167
-- Data for Name: cs_post; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_post (id, name, description) FROM stdin;
2	Дурак	Вообще не главный
1	Начальник	Самый главный в отделе
3	Директор	Самый главный в конторе
\.


-- Completed on 2012-01-20 10:56:04 YEKT

--
-- PostgreSQL database dump complete
--

