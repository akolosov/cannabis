--
-- PostgreSQL database dump
--

-- Dumped from database version 9.1.2
-- Dumped by pg_dump version 9.1.2
-- Started on 2012-01-20 11:13:58 YEKT

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

--
-- TOC entry 2712 (class 0 OID 0)
-- Dependencies: 323
-- Name: cs_project_instance_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_project_instance_id_seq', 1, true);


--
-- TOC entry 2709 (class 0 OID 18882)
-- Dependencies: 177
-- Data for Name: cs_project_instance; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_project_instance (id, project_id, initiator_id, status_id, started_at, ended_at) FROM stdin;
1	1	1	1	2012-01-20 11:12:37.523875	\N
\.


-- Completed on 2012-01-20 11:13:58 YEKT

--
-- PostgreSQL database dump complete
--

