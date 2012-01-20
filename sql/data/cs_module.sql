--
-- PostgreSQL database dump
--

-- Started on 2008-01-16 16:21:07 YEKT

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- TOC entry 2100 (class 0 OID 0)
-- Dependencies: 1608
-- Name: cs_module_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cs_module_id_seq', 35, true);


--
-- TOC entry 2097 (class 0 OID 25285)
-- Dependencies: 1607
-- Data for Name: cs_module; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cs_module (id, parent_id, name, description, caption, is_hidden) FROM stdin;
2	1	divisions	Редактор подразделений	Подразделения	f
3	1	users	Редактор пользователей и групп	Пользователи	f
4	1	permissions	Редактор прав доступа	Права	f
5	1	modules	Редактор доступных модулей	Модули	f
6	1	roles	Редактор ролей	Роли	f
11	4	modules	Редактор прав доступа к модулям	Права на модули	f
12	\N	runtime	Модули выполнения	Выполнение	f
7	\N	builder	Модули моделирования	Моделирование	f
1	\N	admin	Модули администрирования	Администрирование	f
28	12	inboxes	Входящие документы пользователя - требующие его непосредственного участия	Входящие	f
17	1	constants	Редактор констант и системных переменных	Константы	f
8	7	processes	Редактор процессов, действий, свойств и пр.	Редактор процессов	f
9	7	projects	Редактор проектов, процессов, свойств и пр.	Редактор проектов	f
18	1	transports	Редактор транспортов сообщений и уведомлений	Транспорты	f
14	12	controllers	Контролер выполнения процессов - текущие процессы пользователя, текущие действия пользователя	Контроллер	f
29	12	outboxes	Исходящие или уже отправленные документы пользователя	Исходящие	f
19	1	directories	Редактор справочников	Справочники	f
20	1	posts	Редактор должностей	Должности	f
21	1	relations	Редактор отношений между должностями	Отношения	f
22	1	responsers	Редактор ответственных за определённые виды деятельности	Ответственные	f
23	3	posts	Редактор должностей пользователей	Должности	f
24	1	mimetypes	Редактор разрешенных для загрузки типов файлов	Типы файлов	f
25	12	templates	Шаблоны для создания новых документов.	Шаблоны	f
30	12	archives	Архив закрытых документов пользователя	Архив	f
31	12	zombies	Зависшие документы - с датой окончания, но без статуса окончания	Зависшие	f
32	12	reporters	Отчеты о выполнении документов	Отчеты	f
26	12	managers	Менеджер документов - управление текущими и завершенными документами	Менеджер	f
15	12	today	Текущие документы пользователя	Текущие	f
13	12	delegations	Делигирование полномочий пользователя на определённый срок другому пользователю	Делигирование	f
27	12	drafts	Черновики созданных, но не отправленных документов	Черновики	f
33	7	topics	Редактор статических разделов	Разделы	f
34	\N	groupware	Модули органайзера	Органайзер	f
35	34	calendars	Модуль календаря	Календарь	f
36	34	contacts	Модуль адресной книги	Контакты	f
37	34	files	Модуль обмена файлами	Файлы	f
38	34	messages	Модуль обмена сообщениями	Сообщения	f
39	\N	public	Общие разделы сайта	Общие	t
40	39	documents	Статические документы сайта	Документы	t
\.


-- Completed on 2008-01-16 16:21:07 YEKT

--
-- PostgreSQL database dump complete
--

