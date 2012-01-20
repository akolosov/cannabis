--
-- PostgreSQL database dump
--

-- Dumped from database version 9.1.2
-- Dumped by pg_dump version 9.1.2
-- Started on 2012-01-20 10:44:31 YEKT

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

--
-- TOC entry 2712 (class 0 OID 0)
-- Dependencies: 227
-- Name: cs_account_post_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_account_post_id_seq', 2, true);


--
-- TOC entry 2709 (class 0 OID 18818)
-- Dependencies: 166
-- Data for Name: cs_account_post; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_account_post (id, account_id, post_id, division_id) FROM stdin;
1	3	1	1
2	4	2	1
\.


-- Completed on 2012-01-20 10:44:31 YEKT

--
-- PostgreSQL database dump complete
--

