--
-- PostgreSQL database dump
--

-- Dumped from database version 9.1.2
-- Dumped by pg_dump version 9.1.2
-- Started on 2012-01-20 10:56:55 YEKT

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

--
-- TOC entry 2713 (class 0 OID 0)
-- Dependencies: 226
-- Name: cs_account_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_account_id_seq', 5, true);


--
-- TOC entry 2710 (class 0 OID 18753)
-- Dependencies: 161
-- Data for Name: cs_account; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_account (id, parent_id, name, description, permission_id, passwd, email, icq, jabber, is_active, cell, cellop_id, division_id) FROM stdin;
0	\N	Все пользователи	Все пользователи системы	\N	\N	\N	\N	\N	t	\N	\N	\N
-1	0	Система	Система документооборота	1	\N	\N	\N	\N	t	\N	\N	\N
2	0	Пользователи	Пользователи ситемы	\N	\N				t		\N	1
1	-1	Администратор	Администратор всей системы	1	040f9f5e60e3ec813eefdbf9697ab6cb	admin@test.ru	\N	\N	t	\N	\N	1
4	2	Пользователь №1	Простой пользователь системы №1	2	202cb962ac59075b964b07152d234b70	user@test.ru			t		\N	2
3	2	Начальник	Самый главный	3	202cb962ac59075b964b07152d234b70	manager@test.ru			t		\N	2
5	2	Директор	Царь и Бог	4	202cb962ac59075b964b07152d234b70	director@test.ru			t		\N	1
\.


-- Completed on 2012-01-20 10:56:55 YEKT

--
-- PostgreSQL database dump complete
--

