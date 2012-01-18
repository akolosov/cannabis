--
-- PostgreSQL database dump
--

-- Started on 2008-01-16 16:22:03 YEKT

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- TOC entry 2098 (class 0 OID 0)
-- Dependencies: 1581
-- Name: cs_cellop_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_cellop_id_seq', 4, true);


--
-- TOC entry 2095 (class 0 OID 25007)
-- Dependencies: 1537
-- Data for Name: cs_cellop; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_cellop (id, name, description, gate) FROM stdin;
1	МТС	МТС	sms.ural.mts.ru
3	МегаФон	МегаФон	sms.ugsm.ru
2	Билайн	Билайн	sms.beemail.ru
4	Utel	не работает	\N
\.


-- Completed on 2008-01-16 16:22:03 YEKT

--
-- PostgreSQL database dump complete
--

