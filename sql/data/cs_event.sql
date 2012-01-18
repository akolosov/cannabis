--
-- PostgreSQL database dump
--

-- Started on 2008-01-16 16:21:39 YEKT

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- TOC entry 2098 (class 0 OID 0)
-- Dependencies: 1604
-- Name: cs_event_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_event_id_seq', 16, true);


--
-- TOC entry 2095 (class 0 OID 25273)
-- Dependencies: 1603
-- Data for Name: cs_event; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_event (id, name, description) FROM stdin;
1	BeforeProcessStart	До старта процесса
2	AfterProcessEnd	После окончания процесса
7	AfterProcessStart	После старта процесса
8	BeforeProcessEnd	До окончания процесса
3	BeforeAnyActionStart	До старта любого действия
4	AfterAnyActionEnd	После окончания любого действия 
5	AfterAnyActionStart	После старта любого действия
6	BeforeAnyActionEnd	До окончания любого действия
9	BeforeIntActionStart	До начала интерактивного действия
10	AfterIntActionStart	После начала интерактивного действия
11	BeforeIntActionEnd	До окончания интерактивного действия
12	AfterIntActionEnd	После окончания интерактивного действия
13	BeforeNotActionStart	До начала неинтерактивного действия
14	AfterNotActionStart	После начала неинтерактивного действия
15	BeforeNotActionEnd	До окончания неинтерактивного действия
16	AfterNotActionEnd	После окончания неинтерактивного действия
\.


-- Completed on 2008-01-16 16:21:39 YEKT

--
-- PostgreSQL database dump complete
--

