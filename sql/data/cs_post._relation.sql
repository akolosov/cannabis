--
-- PostgreSQL database dump
--

-- Dumped from database version 9.1.2
-- Dumped by pg_dump version 9.1.2
-- Started on 2012-01-20 10:55:45 YEKT

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

--
-- TOC entry 2712 (class 0 OID 0)
-- Dependencies: 298
-- Name: cs_post_relation_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_post_relation_id_seq', 22, true);


--
-- TOC entry 2709 (class 0 OID 19350)
-- Dependencies: 297
-- Data for Name: cs_post_relation; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_post_relation (id, post_id, relation_post_id, division_id) FROM stdin;
3	2	1	1
5	1	2	1
8	2	1	2
9	2	2	2
10	1	2	2
11	1	1	2
12	1	3	2
19	3	1	1
20	3	1	2
21	3	3	1
22	3	3	2
\.


-- Completed on 2012-01-20 10:55:45 YEKT

--
-- PostgreSQL database dump complete
--

