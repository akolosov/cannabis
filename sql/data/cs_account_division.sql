--
-- PostgreSQL database dump
--

-- Dumped from database version 9.1.2
-- Dumped by pg_dump version 9.1.2
-- Started on 2012-01-20 10:56:37 YEKT

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

--
-- TOC entry 2710 (class 0 OID 0)
-- Dependencies: 225
-- Name: cs_account_division_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_account_division_id_seq', 9, true);


--
-- TOC entry 2707 (class 0 OID 19118)
-- Dependencies: 224
-- Data for Name: cs_account_division; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_account_division (id, account_id, division_id) FROM stdin;
1	1	1
2	2	1
5	4	1
6	4	2
7	3	1
8	3	2
9	5	1
\.


-- Completed on 2012-01-20 10:56:38 YEKT

--
-- PostgreSQL database dump complete
--

