--
-- PostgreSQL database dump
--

-- Dumped from database version 9.1.2
-- Dumped by pg_dump version 9.1.2
-- Started on 2012-01-20 11:29:16 YEKT

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

--
-- TOC entry 2706 (class 0 OID 0)
-- Dependencies: 333
-- Name: cs_property_type_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_property_type_id_seq', 8, true);


--
-- TOC entry 2703 (class 0 OID 19066)
-- Dependencies: 213
-- Data for Name: cs_property_type; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_property_type (id, name, description) FROM stdin;
2	Строка	Значение типа Строка
1	Текст	Значение типа Текст
3	Число	Значение типа Число
4	Дата	Значение типа Дата
5	Время	Значение типа Время
6	Дата и Время	Значение типа Дата и Время
7	Объект	Значение типа Объект
8	Логика	Значение типа Логика
\.


-- Completed on 2012-01-20 11:29:16 YEKT

--
-- PostgreSQL database dump complete
--

