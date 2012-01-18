--
-- PostgreSQL database dump
--

-- Started on 2008-03-25 10:25:45 YEKT

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- TOC entry 2254 (class 0 OID 0)
-- Dependencies: 1714
-- Name: cs_transport_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_transport_id_seq', 5, true);


--
-- TOC entry 2251 (class 0 OID 104636)
-- Dependencies: 1713
-- Data for Name: cs_transport; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_transport (id, name, description, server_address, server_login, server_passwd, server_port, class_name) FROM stdin;
2	Транспорт ICQ	Транспорт по протоколу ICQ	login.icq.com			5190	TransportICQ
3	Транспорт Jabber	Транспорт по протоколу Jabber	jabber.ru			5222	TransportJabber
5	Транспорт PHP5::Mail	Транспорт по протоколу PHP5::Mail					TransportMail
4	Транспорт SMS	Транспорт по протоколу SMS					TransportSMS
1	Tранспорт SMTP	Транспорт по протоколу SMTP	shadow	hunter	bumpy	25	TransportSMTPMail
\.


-- Completed on 2008-03-25 10:25:45 YEKT

--
-- PostgreSQL database dump complete
--

