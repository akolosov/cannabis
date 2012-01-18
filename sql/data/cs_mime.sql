--
-- PostgreSQL database dump
--

-- Started on 2008-01-16 16:21:20 YEKT

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- TOC entry 2099 (class 0 OID 0)
-- Dependencies: 1606
-- Name: cs_mime_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_mime_id_seq', 17, true);


--
-- TOC entry 2096 (class 0 OID 25280)
-- Dependencies: 1605
-- Data for Name: cs_mime; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_mime (id, name, ext, is_active) FROM stdin;
1	application/vnd.ms-excel	xls|xlt	t
2	application/msword	doc|dot	t
3	application/vnd.oasis.opendocument.text	odt	t
4	application/vnd.oasis.opendocument.spreadsheet	ods	t
5	application/vnd.ms-powerpoint	ppt|pps	t
6	application/vnd.oasis.opendocument.presentation	odp	t
7	image/gif	gif	t
8	image/jpeg	jpeg|jpg	t
9	image/x-coreldraw	cdr	t
10	application/pdf	pdf	t
11	application/rar	rar	t
12	application/zip	zip	t
13	application/x-gtar	gtar|tgz	t
14	application/x-bzip2	bz2	t
15	application/x-gzip	gz|gzip	t
16	image/png	png	t
17	image/tiff	tiff|tif	t
\.


-- Completed on 2008-01-16 16:21:20 YEKT

--
-- PostgreSQL database dump complete
--

