--
-- PostgreSQL database dump
--

-- Started on 2008-01-16 16:19:18 YEKT

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- TOC entry 2105 (class 0 OID 0)
-- Dependencies: 1575
-- Name: cs_account_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_account_id_seq', 1, true);


--
-- TOC entry 2102 (class 0 OID 24996)
-- Dependencies: 1536
-- Data for Name: cs_account; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_account (id, parent_id, name, description, permission_id, passwd, email, icq, jabber, is_active, cell, cellop_id, division_id) FROM stdin;
0	\N	Все пользователи	Все пользователи системы	\N	\N	\N	\N	\N	t	\N	\N	\N
-1	0	Система	Система документооборота	1	\N	\N	\N	\N	t	\N	\N	\N
1	34	Администратор	Администратор всей системы	1	040f9f5e60e3ec813eefdbf9697ab6cb	admin@uk-most.ru	\N	\N	t	\N	\N	\N
\.


-- Completed on 2008-01-16 16:19:18 YEKT

--
-- PostgreSQL database dump complete
--

