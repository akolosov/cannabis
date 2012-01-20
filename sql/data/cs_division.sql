--
-- PostgreSQL database dump
--

-- Dumped from database version 9.1.2
-- Dumped by pg_dump version 9.1.2
-- Started on 2012-01-20 10:29:27 YEKT

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

--
-- TOC entry 2709 (class 0 OID 0)
-- Dependencies: 267
-- Name: cs_division_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_division_id_seq', 1, true);


--
-- TOC entry 2706 (class 0 OID 18771)
-- Dependencies: 163
-- Data for Name: cs_division; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_division (id, parent_id, name, description, boss_id) FROM stdin;
1	\N	Главное предприятие	Самое главное предприятие	1
\.


-- Completed on 2012-01-20 10:29:28 YEKT

--
-- PostgreSQL database dump complete
--

