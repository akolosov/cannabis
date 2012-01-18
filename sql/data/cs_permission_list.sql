--
-- PostgreSQL database dump
--

-- Started on 2008-01-16 16:20:43 YEKT

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- TOC entry 2104 (class 0 OID 0)
-- Dependencies: 1611
-- Name: cs_permission_list_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_permission_list_id_seq', 82, true);


--
-- TOC entry 2101 (class 0 OID 25295)
-- Dependencies: 1610
-- Data for Name: cs_permission_list; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_permission_list (id, permission_id, module_id, can_read, can_write, can_delete, can_admin, can_review, can_observe) FROM stdin;
71	1	33	t	t	t	t	t	t
72	1	35	t	t	t	t	t	t
73	4	13	t	t	t	f	f	f
74	4	25	t	t	t	f	f	f
75	4	27	t	t	t	f	f	f
76	4	28	t	t	t	f	f	f
77	4	29	t	t	t	f	f	f
78	4	30	t	t	t	f	f	f
79	4	32	t	t	t	f	f	f
80	4	15	t	t	t	f	f	f
81	4	35	t	t	t	t	f	f
82	2	35	t	f	f	f	f	f
47	2	25	t	t	t	f	f	f
53	3	25	t	t	t	f	f	f
59	1	25	t	t	t	t	t	t
44	1	24	t	t	t	t	t	t
39	1	22	t	t	t	t	t	t
38	1	21	t	t	t	t	t	t
37	1	20	t	t	t	t	t	t
36	1	19	t	t	t	t	t	t
32	1	18	t	t	t	t	t	t
31	1	17	t	t	t	t	t	t
7	1	9	t	t	t	t	t	t
6	1	8	t	t	t	t	t	t
5	1	6	t	t	t	t	t	t
4	1	5	t	t	t	t	t	t
3	1	4	t	t	t	t	t	t
2	1	3	t	t	t	t	t	t
1	1	2	t	t	t	t	t	t
48	2	27	t	t	t	f	f	f
54	3	27	t	t	t	f	f	f
60	1	27	t	t	t	t	t	t
49	2	28	t	t	t	f	f	f
55	3	28	t	t	t	f	f	f
61	1	28	t	t	t	t	t	t
50	2	29	t	t	t	f	f	f
56	3	29	t	t	t	f	f	f
62	1	29	t	t	t	t	t	t
51	2	30	t	t	t	f	f	f
57	3	30	t	t	t	f	f	f
63	1	30	t	t	t	t	t	t
67	1	31	t	t	t	t	t	t
66	1	32	t	t	t	t	t	t
65	3	32	t	t	t	f	t	f
64	2	32	t	t	t	f	f	f
18	3	26	t	t	t	f	t	f
8	1	26	t	t	t	t	t	t
68	2	15	t	t	t	f	f	f
69	1	15	t	t	t	t	t	t
70	3	15	t	t	t	f	f	f
46	2	13	t	t	t	f	f	f
52	3	13	t	t	t	f	f	f
58	1	13	t	t	t	t	t	t
\.


-- Completed on 2008-01-16 16:20:43 YEKT

--
-- PostgreSQL database dump complete
--

