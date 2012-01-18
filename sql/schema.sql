--
-- PostgreSQL database dump
--

-- Started on 2008-04-25 16:09:18 YEKST

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- TOC entry 3014 (class 1262 OID 81676)
-- Dependencies: 3013
-- Name: cannabis; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON DATABASE cannabis IS 'cannabis project';


--
-- TOC entry 3015 (class 0 OID 0)
-- Dependencies: 5
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA public IS 'Standard public schema';


--
-- TOC entry 616 (class 2612 OID 81679)
-- Name: plpgsql; Type: PROCEDURAL LANGUAGE; Schema: -; Owner: postgres
--

CREATE PROCEDURAL LANGUAGE plpgsql;


SET search_path = public, pg_catalog;

--
-- TOC entry 19 (class 1255 OID 81680)
-- Dependencies: 616 5
-- Name: activate_process_instance(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION activate_process_instance(instance_id bigint) RETURNS void
    AS $$DECLARE
	pid	int8;
BEGIN
	SELECT parent_id FROM cs_process_instance WHERE id = instance_id INTO pid;
	IF pid IS NULL THEN
--		IF have_active_actions(instance_id) THEN
			UPDATE cs_process_instance SET status_id = 1 WHERE id = instance_id;
--		ELSE
--			UPDATE cs_process_instance SET status_id = 1, started_at = now() WHERE id = instance_id;
--		END IF;
	ELSE
--		IF have_active_actions(instance_id) THEN
			UPDATE cs_process_instance SET status_id = 8 WHERE id = instance_id;
--		ELSE
--			UPDATE cs_process_instance SET status_id = 8, started_at = now() WHERE id = instance_id;
--		END IF;
	END IF;

	FOR pid IN select id from cs_process_instance WHERE parent_id = instance_id LOOP
		EXECUTE activate_process_instance(pid);
	END LOOP;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.activate_process_instance(instance_id bigint) OWNER TO postgres;

--
-- TOC entry 20 (class 1255 OID 81681)
-- Dependencies: 5
-- Name: can_create_process_instance(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION can_create_process_instance(a_process_id integer) RETURNS boolean
    AS $_$SELECT is_active FROM cs_process WHERE id = $1;$_$
    LANGUAGE sql;


ALTER FUNCTION public.can_create_process_instance(a_process_id integer) OWNER TO postgres;

--
-- TOC entry 21 (class 1255 OID 81682)
-- Dependencies: 616 5
-- Name: can_create_project_instance(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION can_create_project_instance(a_project_id integer) RETURNS boolean
    AS $$DECLARE
	is_perm		bool;
	is_act		bool;
	instances	int4;
BEGIN
	select is_active from cs_project where id = a_project_id into is_act;
	IF is_act THEN
		select is_permanent from cs_project where id = a_project_id into is_perm;
		select count(id) from cs_project_instance where project_id = a_project_id into instances;
		IF is_perm THEN
			IF instances >= 1 THEN
				RETURN false;
			ELSE
				RETURN true;
			END IF;
		ELSE
			RETURN true;
		END IF;
	ELSE
		RETURN false;
	END IF;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.can_create_project_instance(a_project_id integer) OWNER TO postgres;

--
-- TOC entry 22 (class 1255 OID 81683)
-- Dependencies: 616 5
-- Name: create_process_instance(integer, integer, bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION create_process_instance(a_process_id integer, a_initiator_id integer, a_parent_id bigint) RETURNS bigint
    AS $$DECLARE
	iid	int8;
	vid	int8;
	pid	int4;
	res	int8;
BEGIN
	IF can_create_process_instance(a_process_id) THEN
--		INSERT INTO cs_process_instance (parent_id, process_id, initiator_id, status_id, started_at) VALUES (a_parent_id, a_process_id, a_initiator_id, 1, now());
		INSERT INTO cs_process_instance (parent_id, process_id, initiator_id, status_id) VALUES (a_parent_id, a_process_id, a_initiator_id, 1);
		SELECT currval('cs_process_instance_id_seq'::regclass) INTO iid;
		FOR pid IN SELECT id FROM cs_process_property WHERE process_id = a_process_id ORDER BY id, name, type_id LOOP
			INSERT INTO cs_property_value (mime_type, value) VALUES (NULL, (SELECT default_value FROM cs_process_property WHERE id = pid));
			SELECT currval('cs_property_value_id_seq'::regclass) INTO vid;
			INSERT INTO cs_process_property_value (instance_id, property_id, value_id) VALUES (iid, pid, vid);
		END LOOP;

		FOR pid IN SELECT id FROM cs_process_action WHERE process_id = a_process_id AND npp = 0 ORDER BY npp, type_id, id, name LOOP
--			INSERT INTO cs_process_current_action (instance_id, action_id, initiator_id, performer_id, status_id, started_at, planed, fixed_planed) VALUES (iid, pid, a_initiator_id, a_initiator_id, 1, now(), (select planed from cs_process_action where id = pid), (select fixed_planed from cs_process_action where id = pid));
			INSERT INTO cs_process_current_action (instance_id, action_id, initiator_id, performer_id, status_id, planed, fixed_planed) VALUES (iid, pid, a_initiator_id, a_initiator_id, 1, (select planed from cs_process_action where id = pid), (select fixed_planed from cs_process_action where id = pid));
		END LOOP;

		FOR pid IN SELECT id FROM cs_process_action WHERE process_id = a_process_id AND npp > 0 ORDER BY npp, type_id, id, name LOOP
			INSERT INTO cs_process_current_action (instance_id, action_id, planed, fixed_planed) VALUES (iid, pid, (select planed from cs_process_action where id = pid), (select fixed_planed from cs_process_action where id = pid));
		END LOOP;

--		FOR pid IN SELECT id FROM processes_tree WHERE parent_id = a_process_id LOOP
--			res = create_process_instance(pid, a_initiator_id, iid);
--		END LOOP;
		RETURN iid;
	ELSE
		RETURN 0;
	END IF;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.create_process_instance(a_process_id integer, a_initiator_id integer, a_parent_id bigint) OWNER TO postgres;

--
-- TOC entry 23 (class 1255 OID 81684)
-- Dependencies: 616 5
-- Name: create_process_instance(bigint, integer, integer, bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION create_process_instance(a_project_instance_id bigint, a_process_id integer, a_initiator_id integer, a_parent_id bigint) RETURNS bigint
    AS $$DECLARE
	iid	int8;
	vid	int8;
	pid	int4;
	res	int8;
BEGIN
	IF can_create_process_instance(a_process_id) THEN
--		INSERT INTO cs_process_instance (parent_id, process_id, initiator_id, status_id, started_at) VALUES (a_parent_id, a_process_id, a_initiator_id, 1, now());
		INSERT INTO cs_process_instance (parent_id, process_id, initiator_id, status_id) VALUES (a_parent_id, a_process_id, a_initiator_id, 1);
		SELECT currval('cs_process_instance_id_seq'::regclass) INTO iid;
		FOR pid IN SELECT id FROM cs_process_property WHERE process_id = a_process_id ORDER BY id, name, type_id LOOP
			INSERT INTO cs_property_value (mime_type, value) VALUES (NULL, (SELECT default_value FROM cs_process_property WHERE id = pid));
			SELECT currval('cs_property_value_id_seq'::regclass) INTO vid;
			INSERT INTO cs_process_property_value (instance_id, property_id, value_id) VALUES (iid, pid, vid);
		END LOOP;

		FOR pid IN SELECT id FROM cs_process_action WHERE process_id = a_process_id AND npp = 0 ORDER BY npp, type_id, id, name LOOP
--			INSERT INTO cs_process_current_action (instance_id, action_id, initiator_id, performer_id, status_id, started_at, planed, fixed_planed) VALUES (iid, pid, a_initiator_id, a_initiator_id, 1, now(), (select planed from cs_process_action where id = pid), (select fixed_planed from cs_process_action where id = pid));
			INSERT INTO cs_process_current_action (instance_id, action_id, initiator_id, performer_id, status_id, planed, fixed_planed) VALUES (iid, pid, a_initiator_id, a_initiator_id, 1, (select planed from cs_process_action where id = pid), (select fixed_planed from cs_process_action where id = pid));
		END LOOP;

		FOR pid IN SELECT id FROM cs_process_action WHERE process_id = a_process_id AND npp > 0 ORDER BY npp, type_id, id, name LOOP
			INSERT INTO cs_process_current_action (instance_id, action_id, planed, fixed_planed) VALUES (iid, pid, (select planed from cs_process_action where id = pid), (select fixed_planed from cs_process_action where id = pid));
		END LOOP;

		INSERT INTO cs_project_process_instance (process_instance_id, project_instance_id) VALUES (iid, a_project_instance_id);
--		FOR pid IN SELECT id FROM processes_tree WHERE parent_id = a_process_id LOOP
--			res = create_process_instance(a_project_instance_id, pid, a_initiator_id, iid);
--		END LOOP;
		RETURN iid;
	ELSE
		RETURN 0;
	END IF;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.create_process_instance(a_project_instance_id bigint, a_process_id integer, a_initiator_id integer, a_parent_id bigint) OWNER TO postgres;

--
-- TOC entry 24 (class 1255 OID 81685)
-- Dependencies: 616 5
-- Name: create_project_instance(integer, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION create_project_instance(a_project_id integer, a_initiator_id integer) RETURNS bigint
    AS $$DECLARE
	iid		int8;
	pid		int4;
	vid		int8;
	res		int8;
	query		text;
BEGIN
	IF can_create_project_instance(a_project_id) THEN
		INSERT INTO cs_project_instance (project_id, initiator_id, status_id, started_at) VALUES (a_project_id, a_initiator_id, 1, now());
		SELECT currval('cs_project_instance_id_seq'::regclass) INTO iid;
--		query = 'select * from processes_tree where id in (select process_id from cs_project_process where project_id = ' || a_project_id || ') order by id, name';
--		FOR pid IN EXECUTE query LOOP
--			res = create_process_instance(iid, pid, a_initiator_id, NULL);
--		END LOOP;
		FOR pid IN SELECT id FROM cs_project_property WHERE project_id = a_project_id LOOP
			INSERT INTO cs_property_value (mime_type, value) VALUES (NULL, (SELECT default_value FROM cs_project_property WHERE id = pid));
			SELECT currval('cs_property_value_id_seq'::regclass) INTO vid;
			INSERT INTO cs_project_property_value (instance_id, property_id, value_id) VALUES (iid, pid, vid);
		END LOOP;
		RETURN iid;
	ELSE
		RETURN 0;
	END IF;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.create_project_instance(a_project_id integer, a_initiator_id integer) OWNER TO postgres;

--
-- TOC entry 25 (class 1255 OID 81686)
-- Dependencies: 616 5
-- Name: deactivate_process_instance(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION deactivate_process_instance(instance_id bigint) RETURNS void
    AS $$DECLARE
	pid	int8;
BEGIN
	UPDATE cs_process_instance SET status_id = NULL WHERE id = instance_id;
--	IF have_active_actions(instance_id) THEN
--		UPDATE cs_process_instance SET status_id = NULL, ended_at = NULL WHERE id = instance_id;
--	ELSE
--		UPDATE cs_process_instance SET status_id = NULL, started_at = NULL, ended_at = NULL WHERE id = instance_id;
--	END IF;
	FOR pid IN select id from cs_process_instance WHERE parent_id = instance_id LOOP
		EXECUTE deactivate_process_instance(pid);
	END LOOP;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.deactivate_process_instance(instance_id bigint) OWNER TO postgres;

--
-- TOC entry 26 (class 1255 OID 81687)
-- Dependencies: 616 5
-- Name: delete_process_instance(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION delete_process_instance(instance_id bigint) RETURNS void
    AS $$DECLARE
	pid	int8;
BEGIN
	SELECT parent_id FROM cs_process_instance WHERE id = instance_id INTO pid;
	IF pid IS NULL THEN
		UPDATE cs_process_instance SET status_id = 6, ended_at = now() WHERE id = instance_id;
	ELSE
		UPDATE cs_process_instance SET status_id = 13, ended_at = now() WHERE id = instance_id;
	END IF;

	FOR pid IN select id from cs_process_instance WHERE parent_id = instance_id LOOP
		EXECUTE delete_process_instance(pid);
	END LOOP;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.delete_process_instance(instance_id bigint) OWNER TO postgres;

--
-- TOC entry 27 (class 1255 OID 81688)
-- Dependencies: 616 5
-- Name: erase_process_instance(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION erase_process_instance(instance_id bigint) RETURNS void
    AS $$DECLARE
	pid	int8;
BEGIN
	DELETE FROM cs_process_instance WHERE id = instance_id;
	FOR pid IN select id from cs_process_instance WHERE parent_id = instance_id LOOP
		EXECUTE erase_process_instance(pid);
	END LOOP;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.erase_process_instance(instance_id bigint) OWNER TO postgres;

--
-- TOC entry 28 (class 1255 OID 81689)
-- Dependencies: 616 5
-- Name: error_process_instance(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION error_process_instance(instance_id bigint) RETURNS void
    AS $$DECLARE
	pid	int8;
BEGIN
	SELECT parent_id FROM cs_process_instance WHERE id = instance_id INTO pid;
	IF pid IS NULL THEN
		UPDATE cs_process_instance SET status_id = 5, ended_at = now() WHERE id = instance_id;
	ELSE
		UPDATE cs_process_instance SET status_id = 12, ended_at = now() WHERE id = instance_id;
	END IF;

	FOR pid IN select id from cs_process_instance WHERE parent_id = instance_id LOOP
		EXECUTE error_process_instance(pid);
	END LOOP;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.error_process_instance(instance_id bigint) OWNER TO postgres;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- TOC entry 1621 (class 1259 OID 81690)
-- Dependencies: 2257 5
-- Name: cs_account; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_account (
    id integer NOT NULL,
    parent_id integer,
    name character varying(150),
    description text,
    permission_id integer,
    passwd character varying(150),
    email character varying(150),
    icq character varying(150),
    jabber character varying(150),
    is_active boolean DEFAULT true,
    cell character varying(150),
    cellop_id integer,
    division_id integer
);


ALTER TABLE public.cs_account OWNER TO postgres;

--
-- TOC entry 29 (class 1255 OID 81696)
-- Dependencies: 258 616 5
-- Name: get_accounts_by_divisions_list(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_accounts_by_divisions_list(divisions_list text) RETURNS SETOF cs_account
    AS $$DECLARE
	account1	cs_account%rowtype;
	query		text;
BEGIN
	query = 'select distinct * from cs_account where id in (select account_id from cs_account_division where division_id in (select id from divisions_tree where id in (' || divisions_list || ') or parent_id in (' || divisions_list || '))) order by name';
	for account1 in execute query loop
		return next account1;
	end loop;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_accounts_by_divisions_list(divisions_list text) OWNER TO postgres;

--
-- TOC entry 30 (class 1255 OID 81697)
-- Dependencies: 258 616 5
-- Name: get_accounts_by_post(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_accounts_by_post(posts_list text) RETURNS SETOF cs_account
    AS $$DECLARE
	query	text;
	account	cs_account%rowtype;
BEGIN
	query = 'select * from cs_account where id in 
			(select account_id from cs_account_division where division_id in 
				(select division_id from cs_post_relation where post_id in (' || posts_list || '))
				and account_id in (select account_id from cs_account_post where post_id in (select relation_post_id from cs_post_relation where post_id in (' || posts_list || '))))
			and (is_active = true and passwd is not null and permission_id is not null) order by name';

	for account in execute query loop
		return next account;
	end loop;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_accounts_by_post(posts_list text) OWNER TO postgres;

--
-- TOC entry 31 (class 1255 OID 81698)
-- Dependencies: 258 616 5
-- Name: get_accounts_by_post_and_division(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_accounts_by_post_and_division(posts_list text, divisions_list text) RETURNS SETOF cs_account
    AS $$DECLARE
	query	text;
	account	cs_account%rowtype;
BEGIN
	query = 'select * from cs_account where id in 
			(select account_id from cs_account_division where division_id in 
				(select division_id from cs_post_relation where post_id in (' || posts_list || ') and division_id in (' || divisions_list || '))
				and account_id in (select account_id from cs_account_post where post_id in (select relation_post_id from cs_post_relation where post_id in (' || posts_list || ') and division_id in (' || divisions_list || '))))
			and (is_active = true and passwd is not null and permission_id is not null) order by name';

	for account in execute query loop
		return next account;
	end loop;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_accounts_by_post_and_division(posts_list text, divisions_list text) OWNER TO postgres;

--
-- TOC entry 32 (class 1255 OID 81699)
-- Dependencies: 616 5
-- Name: get_full_tree(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_full_tree(a_table text) RETURNS SETOF integer
    AS $$declare 
   id0		int4;
   id1		int4;
begin
  for id0 in execute 'select id from ' || a_table || ' where parent_id is null' loop
	for id1 in select * from get_tree(a_table, id0, 1) loop
		return next id1;
	end loop; 
  end loop;
end;
$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_full_tree(a_table text) OWNER TO postgres;

--
-- TOC entry 33 (class 1255 OID 81700)
-- Dependencies: 616 5
-- Name: get_level(text, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_level(a_table text, a_root integer) RETURNS smallint
    AS $$DECLARE
	pid	int4;
	level	int2;
BEGIN
	level = 0;
	EXECUTE 'select parent_id from ' || a_table || ' where id = ' || a_root into pid;
	IF NOT pid IS NULL THEN
		level = level + 1;
		WHILE NOT pid IS NULL LOOP
			EXECUTE 'select parent_id from ' || a_table || ' where id = ' || pid into pid;
			IF NOT pid IS NULL THEN
				level = level + 1;
			END IF;
		END LOOP;
	END IF;
	RETURN level;
END$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_level(a_table text, a_root integer) OWNER TO postgres;

--
-- TOC entry 1622 (class 1259 OID 81701)
-- Dependencies: 5
-- Name: cs_cellop; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_cellop (
    id integer NOT NULL,
    name character varying(150),
    description text,
    gate character varying(150)
);


ALTER TABLE public.cs_cellop OWNER TO postgres;

--
-- TOC entry 1623 (class 1259 OID 81706)
-- Dependencies: 5
-- Name: cs_division; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_division (
    id integer NOT NULL,
    parent_id integer,
    name character varying(150),
    description text,
    boss_id integer
);


ALTER TABLE public.cs_division OWNER TO postgres;

--
-- TOC entry 1624 (class 1259 OID 81711)
-- Dependencies: 5
-- Name: cs_permission; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_permission (
    id integer NOT NULL,
    name character varying(150),
    description text
);


ALTER TABLE public.cs_permission OWNER TO postgres;

--
-- TOC entry 1625 (class 1259 OID 81716)
-- Dependencies: 1945 5
-- Name: accounts_tree; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW accounts_tree AS
    SELECT child.id, child.parent_id, child.name, child.description, child.permission_id, child.passwd, child.email, child.icq, child.jabber, child.is_active, child.cell, child.division_id, cs_division.name AS divisionname, child.cellop_id, cs_cellop.name AS cellopname, cs_cellop.gate AS cellopgate, parent.name AS parentname, cs_permission.name AS permission, (SELECT get_level('cs_account'::text, child.id) AS get_level) AS "level" FROM (((((cs_account child JOIN get_full_tree('cs_account'::text) pid(pid) ON ((child.id = pid.pid))) LEFT JOIN cs_account parent ON ((child.parent_id = parent.id))) LEFT JOIN cs_permission ON ((child.permission_id = cs_permission.id))) LEFT JOIN cs_division ON ((child.division_id = cs_division.id))) LEFT JOIN cs_cellop ON ((child.cellop_id = cs_cellop.id)));


ALTER TABLE public.accounts_tree OWNER TO postgres;

--
-- TOC entry 34 (class 1255 OID 81720)
-- Dependencies: 314 616 5
-- Name: get_accounts_tree(integer, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_accounts_tree(from_parent_id integer, is_root boolean) RETURNS SETOF accounts_tree
    AS $$DECLARE
	account1	accounts_tree%rowtype;
	account2	accounts_tree%rowtype;
BEGIN
	if (is_root) then
		if (from_parent_id is null) then
			select * from accounts_tree where id is null into account1;
		else
			select * from accounts_tree where id = from_parent_id into account1;
		end if;
		return next account1;
	end if;
	for account1 in select * from accounts_tree where parent_id = from_parent_id loop
		return next account1;
		for account2 in select * from get_accounts_tree(account1.id, false) loop
			return next account2;
		end loop;
	end loop;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_accounts_tree(from_parent_id integer, is_root boolean) OWNER TO postgres;

--
-- TOC entry 35 (class 1255 OID 81721)
-- Dependencies: 616 5
-- Name: get_action_timeout(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_action_timeout(a_action_id bigint) RETURNS real
    AS $$DECLARE
	action		action_instances_list%rowtype;
	timeout		reltime;
	minus		float4;
	coef		float4;
BEGIN
	minus	= 0;
	SELECT * FROM action_instances_list WHERE id = a_action_id INTO action;
	timeout = (action.ended_at - action.started_at);
	IF ((timeout::integer >= -60) AND (timeout::integer <= 60)) OR (timeout::integer = 0)   THEN 
		minus	= 0;
	ELSE
		coef = get_constant('weight_multiplier')::float4;

		IF ((action.ended_at - action.started_at) > action.planed) THEN
			timeout = (action.ended_at - action.started_at) - action.planed;
			minus	= minus + (timeout::integer * coef::float4);
		ELSE
			timeout = action.planed - (action.ended_at - action.started_at);
			minus	= minus - (timeout::integer * coef::float4);
		END IF;
	END IF;
	RETURN minus::float4;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_action_timeout(a_action_id bigint) OWNER TO postgres;

--
-- TOC entry 36 (class 1255 OID 81722)
-- Dependencies: 616 5
-- Name: get_action_timeout(timestamp without time zone, timestamp without time zone, reltime); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_action_timeout(a_started_at timestamp without time zone, a_ended_at timestamp without time zone, a_planed reltime) RETURNS real
    AS $$DECLARE
	timeout		reltime;
	minus		float4;
	coef		float4;
BEGIN
	timeout = (a_ended_at - a_started_at);
	minus = 0;

	IF ((timeout::integer >= -60) AND (timeout::integer <= 60)) OR (timeout::integer = 0)   THEN 
		minus = 0;
	ELSE
		coef = get_constant('weight_multiplier')::float4;

		IF ((a_ended_at - a_started_at) > a_planed) THEN
			timeout = (a_ended_at - a_started_at) - a_planed;
			minus	= minus + (timeout::integer * coef::float4);
		ELSE
			timeout = a_planed - (a_ended_at - a_started_at);
			minus	= minus - (timeout::integer * coef::float4);
		END IF;
	END IF;

	RETURN minus::float4;
END$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_action_timeout(a_started_at timestamp without time zone, a_ended_at timestamp without time zone, a_planed reltime) OWNER TO postgres;

--
-- TOC entry 37 (class 1255 OID 81723)
-- Dependencies: 5
-- Name: get_constant(character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_constant(name character varying) RETURNS text
    AS $_$SELECT value FROM cs_constants WHERE name = $1;$_$
    LANGUAGE sql;


ALTER FUNCTION public.get_constant(name character varying) OWNER TO postgres;

--
-- TOC entry 38 (class 1255 OID 81724)
-- Dependencies: 258 616 5
-- Name: get_division_accounts(integer, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_division_accounts(from_division_id integer, is_root boolean) RETURNS SETOF cs_account
    AS $$DECLARE
	account1	cs_account%rowtype;
	division1	cs_account_division%rowtype;
BEGIN
	if (is_root) then
		if (from_division_id is null) then
			for division1 in (select * from cs_account_division where division_id in (select id from cs_division where parent_id is null)) loop
				select * from cs_account where id = division1.account_id and passwd is not null and permission_id is not null into account1;
				if found then
					return next account1;
				end if;
			end loop;
		else
			for division1 in (select * from cs_account_division where division_id in (select id from cs_division where id = from_division_id)) loop
				select * from cs_account where id = division1.account_id and passwd is not null and permission_id is not null into account1;
				if found then
					return next account1;
				end if;
			end loop;
		end if;
	end if;

	for division1 in (select * from cs_account_division where division_id in (select id from cs_division where parent_id = from_division_id)) loop
		for account1 in select * from get_division_accounts(division1.division_id, false) loop
			if found then
				return next account1;
			end if;
		end loop;
	end loop;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_division_accounts(from_division_id integer, is_root boolean) OWNER TO postgres;

--
-- TOC entry 39 (class 1255 OID 81725)
-- Dependencies: 616 5
-- Name: get_full_big_tree(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_full_big_tree(a_table text) RETURNS SETOF bigint
    AS $$declare 
   id0		int8;
   id1		int8;
begin
  for id0 in execute 'select id from ' || a_table || ' where parent_id is null' loop
	for id1 in select * from get_tree(a_table, id0, 1) loop
		return next id1;
	end loop; 
  end loop;
end;
$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_full_big_tree(a_table text) OWNER TO postgres;

--
-- TOC entry 40 (class 1255 OID 81726)
-- Dependencies: 616 5
-- Name: get_level(text, bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_level(a_table text, a_root bigint) RETURNS smallint
    AS $$DECLARE
	pid	int8;
	level	int2;
BEGIN
	level = 0;
	EXECUTE 'select parent_id from ' || a_table || ' where id = ' || a_root into pid;
	IF NOT pid IS NULL THEN
		level = level + 1;
		WHILE NOT pid IS NULL LOOP
			EXECUTE 'select parent_id from ' || a_table || ' where id = ' || pid into pid;
			IF NOT pid IS NULL THEN
				level = level + 1;
			END IF;
		END LOOP;
	END IF;
	RETURN level;
END$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_level(a_table text, a_root bigint) OWNER TO postgres;

--
-- TOC entry 41 (class 1255 OID 81727)
-- Dependencies: 616 5
-- Name: get_max_document_npp(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_max_document_npp(a_parent_id bigint) RETURNS integer
    AS $$DECLARE
	a_npp	int4;
BEGIN
	IF (a_parent_id IS NULL) THEN
		SELECT max(npp) FROM cs_public_document WHERE parent_id IS NULL INTO a_npp;
	ELSE
		SELECT max(npp) FROM cs_public_document WHERE parent_id = a_parent_id INTO a_npp;
	END IF;
	RETURN a_npp;
END;
$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_max_document_npp(a_parent_id bigint) OWNER TO postgres;

--
-- TOC entry 42 (class 1255 OID 81728)
-- Dependencies: 616 5
-- Name: get_npp(integer, smallint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_npp(a_root integer, a_level smallint) RETURNS smallint
    AS $$DECLARE
	id	int4;
	npp	int2;
BEGIN
	npp = a_level;
	FOR id IN SELECT from_action_id FROM cs_process_transition WHERE to_action_id = a_root LOOP
		npp = get_npp(id, (a_level+1)::int2);
	END LOOP;
	RETURN npp;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_npp(a_root integer, a_level smallint) OWNER TO postgres;

--
-- TOC entry 43 (class 1255 OID 81729)
-- Dependencies: 616 5
-- Name: get_process_instance_progress(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_process_instance_progress(a_process_instance_id bigint) RETURNS real
    AS $$DECLARE
	progress	float4;
BEGIN
	SELECT SUM(weight) FROM process_instance_actions_list WHERE instance_id = a_process_instance_id AND status_id = 4 INTO progress;
	IF (progress <> 0) THEN
		RETURN ROUND(progress::numeric, 2);
	ELSE
		RETURN 0;
	END IF;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_process_instance_progress(a_process_instance_id bigint) OWNER TO postgres;

--
-- TOC entry 44 (class 1255 OID 81730)
-- Dependencies: 616 5
-- Name: get_process_instance_realprogress(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_process_instance_realprogress(a_process_instance_id bigint) RETURNS real
    AS $$DECLARE
	progress	float4;
BEGIN
	SELECT SUM(weight-minuscoef) FROM process_instance_actions_list WHERE instance_id = a_process_instance_id AND status_id = 4 INTO progress;
	IF (progress <> 0) THEN
		RETURN ROUND(progress::numeric, 2);
	ELSE
		RETURN 0;
	END IF;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_process_instance_realprogress(a_process_instance_id bigint) OWNER TO postgres;

--
-- TOC entry 45 (class 1255 OID 81731)
-- Dependencies: 616 5
-- Name: get_project_instance_progress(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_project_instance_progress(a_project_instance_id bigint) RETURNS real
    AS $$DECLARE
	a_progress	float4;
BEGIN
	SELECT SUM(progress) FROM project_processes_instances_tree WHERE project_instance_id = a_project_instance_id INTO a_progress;
	IF (a_progress <> 0) THEN
		RETURN a_progress;
	ELSE
		RETURN 0;
	END IF;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_project_instance_progress(a_project_instance_id bigint) OWNER TO postgres;

--
-- TOC entry 46 (class 1255 OID 81732)
-- Dependencies: 616 5
-- Name: get_project_instance_realprogress(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_project_instance_realprogress(a_project_instance_id bigint) RETURNS real
    AS $$DECLARE
	a_progress	float4;
BEGIN
	SELECT SUM(realprogress) FROM project_processes_instances_tree WHERE project_instance_id = a_project_instance_id INTO a_progress;
	IF (a_progress <> 0) THEN
		RETURN a_progress;
	ELSE
		RETURN 0;
	END IF;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_project_instance_realprogress(a_project_instance_id bigint) OWNER TO postgres;

--
-- TOC entry 47 (class 1255 OID 81733)
-- Dependencies: 5
-- Name: get_property_directory_custom(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_property_directory_custom(directory_id integer) RETURNS boolean
    AS $_$SELECT custom FROM cs_directory WHERE id = $1;$_$
    LANGUAGE sql;


ALTER FUNCTION public.get_property_directory_custom(directory_id integer) OWNER TO postgres;

--
-- TOC entry 48 (class 1255 OID 81734)
-- Dependencies: 5
-- Name: get_property_directory_name(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_property_directory_name(directory_id integer) RETURNS text
    AS $_$SELECT name FROM cs_directory WHERE id = $1;$_$
    LANGUAGE sql;


ALTER FUNCTION public.get_property_directory_name(directory_id integer) OWNER TO postgres;

--
-- TOC entry 49 (class 1255 OID 81735)
-- Dependencies: 5
-- Name: get_property_directory_parameters(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_property_directory_parameters(directory_id integer) RETURNS text
    AS $_$SELECT parameters FROM cs_directory WHERE id = $1;$_$
    LANGUAGE sql;


ALTER FUNCTION public.get_property_directory_parameters(directory_id integer) OWNER TO postgres;

--
-- TOC entry 50 (class 1255 OID 81736)
-- Dependencies: 5
-- Name: get_property_directory_tablename(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_property_directory_tablename(directory_id integer) RETURNS text
    AS $_$SELECT tablename FROM cs_directory WHERE id = $1;$_$
    LANGUAGE sql;


ALTER FUNCTION public.get_property_directory_tablename(directory_id integer) OWNER TO postgres;

--
-- TOC entry 51 (class 1255 OID 81737)
-- Dependencies: 616 5
-- Name: get_real_performer(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_real_performer(performer_id integer) RETURNS integer
    AS $$DECLARE
	real_delegate_id	int4;
	real_performer_id	int4;
BEGIN
	real_performer_id = performer_id;
	FOR real_delegate_id IN SELECT delegate_id FROM cs_delegate WHERE account_id = performer_id AND started_at <= localtimestamp AND ended_at >= localtimestamp AND is_active = true LOOP
		real_performer_id = get_real_performer(real_delegate_id);
	END LOOP;
	RETURN real_performer_id;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_real_performer(performer_id integer) OWNER TO postgres;

--
-- TOC entry 62 (class 1255 OID 81738)
-- Dependencies: 616 5
-- Name: get_tree(text, integer, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_tree(a_table text, root integer, flag integer) RETURNS SETOF integer
    AS $$declare 
   id0		int4;
   id1		int4;
   id2		int4;
begin
  if flag <> 0 then
	id0 = root;
	return next id0;
  end if;
  for id1 in execute 'select * from ' || a_table || ' where parent_id = ' || root loop
	for id2 in select * from get_tree(a_table, id1, 1) loop
		return next id2;
	end loop; 
  end loop;
end;
$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_tree(a_table text, root integer, flag integer) OWNER TO postgres;

--
-- TOC entry 63 (class 1255 OID 81739)
-- Dependencies: 616 5
-- Name: get_tree(text, bigint, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION get_tree(a_table text, root bigint, flag integer) RETURNS SETOF bigint
    AS $$declare 
   id0		int8;
   id1		int8;
   id2		int8;
begin
  if flag <> 0 then
	id0 = root;
	return next id0;
  end if;
  for id1 in execute 'select * from ' || a_table || ' where parent_id = ' || root loop
	for id2 in select * from get_tree(a_table, id1, 1) loop
		return next id2;
	end loop; 
  end loop;
end;
$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_tree(a_table text, root bigint, flag integer) OWNER TO postgres;

--
-- TOC entry 52 (class 1255 OID 81740)
-- Dependencies: 616 5
-- Name: have_active_actions(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION have_active_actions(a_process_instance bigint) RETURNS boolean
    AS $$DECLARE
	actions	int4;
BEGIN
	SELECT count(ID) FROM cs_process_current_action WHERE status_id IS NOT NULL and started_at IS NOT NULL and instance_id = a_process_instance INTO actions;
	IF actions > 0 THEN
		RETURN true;
	ELSE
		RETURN false;
	END IF;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.have_active_actions(a_process_instance bigint) OWNER TO postgres;

--
-- TOC entry 53 (class 1255 OID 81741)
-- Dependencies: 616 5
-- Name: is_child_of(text, integer, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION is_child_of(a_table text, a_child_id integer, a_parent_id integer) RETURNS boolean
    AS $$DECLARE
	pid	int4;
BEGIN
	EXECUTE 'select parent_id from ' || a_table || ' where id = ' || a_child_id into pid;
	IF NOT pid IS NULL THEN
		IF pid = a_parent_id THEN
			RETURN true;
		END IF;
		WHILE NOT pid IS NULL LOOP
			EXECUTE 'select parent_id from ' || a_table || ' where id = ' || pid into pid;
			IF pid = a_parent_id THEN
				RETURN true;
			END IF;
		END LOOP;
	END IF;
	RETURN false;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.is_child_of(a_table text, a_child_id integer, a_parent_id integer) OWNER TO postgres;

--
-- TOC entry 54 (class 1255 OID 81742)
-- Dependencies: 616 5
-- Name: is_parent_of(text, integer, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION is_parent_of(a_table text, a_parent_id integer, a_child_id integer) RETURNS boolean
    AS $$DECLARE
	pid	int4;
BEGIN
	EXECUTE 'select parent_id from ' || a_table || ' where id = ' || a_child_id into pid;
	IF NOT pid IS NULL THEN
		IF pid = a_parent_id THEN
			RETURN true;
		END IF;
		WHILE NOT pid IS NULL LOOP
			EXECUTE 'select parent_id from ' || a_table || ' where id = ' || pid into pid;
			IF pid = a_parent_id THEN
				RETURN true;
			END IF;
		END LOOP;
	END IF;
	RETURN false;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.is_parent_of(a_table text, a_parent_id integer, a_child_id integer) OWNER TO postgres;

--
-- TOC entry 55 (class 1255 OID 81743)
-- Dependencies: 616 5
-- Name: make_chrono_snapshot(bigint, bigint, bigint, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION make_chrono_snapshot(a_process_id bigint, a_from_id bigint, a_to_id bigint, a_account_id integer) RETURNS boolean
    AS $$DECLARE
	sid		int4;
	cid		int8;
	pid		int8;
	vid		int8;
	bid		int8;
	a_action	cs_process_current_action%rowtype;
	a_property	cs_process_property_value%rowtype;
	a_value		cs_property_value%rowtype;
	a_blob		cs_blob%rowtype;
BEGIN
	SELECT status_id FROM cs_process_instance WHERE id = a_process_id INTO sid;
	IF (sid > 0) THEN
		INSERT INTO cs_chrono(chrono_at, account_id, from_action_id, to_action_id, instance_id, status_id) VALUES(now(), a_account_id, a_from_id, a_to_id, a_process_id, sid);

		SELECT currval('cs_chrono_id_seq'::regclass) INTO cid;
		IF (cid > 0) THEN
			FOR a_action IN SELECT * FROM cs_process_current_action WHERE instance_id = a_process_id LOOP
				INSERT INTO cs_chrono_action (chrono_id, process_instance_id, action_instance_id,
								action_id, status_id, initiator_id, performer_id,
								planed, fixed_planed, started_at, ended_at)
							VALUES (cid, a_process_id, a_action.id, a_action.action_id,
								a_action.status_id, a_action.initiator_id, a_action.performer_id,
								a_action.planed, a_action.fixed_planed,
								a_action.started_at, a_action.ended_at);
			END LOOP;
			FOR a_property IN SELECT * FROM cs_process_property_value WHERE instance_id = a_process_id LOOP
				INSERT INTO cs_chrono_property (chrono_id, process_instance_id,
								property_instance_id, property_id,
								property_value_id)
							VALUES (cid, a_process_id, a_property.id,
								a_property.property_id, a_property.value_id);

				SELECT currval('cs_chrono_property_id_seq'::regclass) INTO pid;

				SELECT * FROM cs_property_value WHERE id = a_property.value_id INTO a_value;

				INSERT INTO cs_chrono_value (mime_type, value) VALUES (a_value.mime_type, a_value.value);

				SELECT currval('cs_chrono_value_id_seq'::regclass) INTO vid;

				UPDATE cs_chrono_property SET value_id = vid WHERE id = pid;

				IF (a_value.mime_type IS NOT NULL) THEN
					SELECT * FROM cs_blob WHERE value_id = a_value.id INTO a_blob;

					IF (a_blob.id > 0) THEN
						INSERT INTO cs_chrono_blob (blob_id, blob_value_id, value_id, blob)
								VALUES (a_blob.id, a_blob.value_id, vid, a_blob.blob);
					END IF;
				END IF;
			END LOOP;
		END IF;
		RETURN true;
	ELSE
		RETURN false;
	END IF;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.make_chrono_snapshot(a_process_id bigint, a_from_id bigint, a_to_id bigint, a_account_id integer) OWNER TO postgres;

--
-- TOC entry 56 (class 1255 OID 81744)
-- Dependencies: 616 5
-- Name: rebuild_documents_tree(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION rebuild_documents_tree(a_parent_id bigint) RETURNS boolean
    AS $$DECLARE
	did		int8;	
	counter		int4;
	cur_npp		int4;
	res		boolean;
BEGIN
	counter = 0;

	IF (a_parent_id IS NULL) THEN
		FOR did IN SELECT id FROM public_documents_tree WHERE parent_id IS NULL ORDER BY npp LOOP
			SELECT npp FROM cs_public_document WHERE id = did INTO cur_npp;
			IF ((counter <> cur_npp) OR (cur_npp IS NULL)) THEN
				UPDATE cs_public_document SET npp = counter WHERE id = did;
			END IF;
			res = rebuild_documents_tree(did);
			counter = counter + 1;
		END LOOP;
	ELSE
		FOR did IN SELECT id FROM public_documents_tree WHERE parent_id = a_parent_id ORDER BY npp LOOP
			SELECT npp FROM cs_public_document WHERE id = did INTO cur_npp;
			IF ((counter <> cur_npp) OR (cur_npp IS NULL)) THEN
				UPDATE cs_public_document SET npp = counter WHERE id = did;
			END IF;
			res = rebuild_documents_tree(did);
			counter = counter + 1;
		END LOOP;
	END IF;
	RETURN true;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.rebuild_documents_tree(a_parent_id bigint) OWNER TO postgres;

--
-- TOC entry 57 (class 1255 OID 81745)
-- Dependencies: 616 5
-- Name: reinit_instances(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION reinit_instances() RETURNS void
    AS $$DECLARE
	id	int8;
BEGIN
	truncate cs_process_instance CASCADE;
	truncate cs_process_property_value CASCADE;
	truncate cs_process_current_action CASCADE;
	truncate cs_project_instance CASCADE;
	truncate cs_project_property_value CASCADE;
	truncate cs_project_process_instance CASCADE;
	truncate cs_property_value CASCADE;
	alter sequence cs_process_instance_id_seq restart with 1;
	alter sequence cs_process_current_action_id_seq restart with 1;
	alter sequence cs_project_instance_id_seq restart with 1;
	alter sequence cs_project_process_instance_id_seq restart with 1;
	alter sequence cs_project_property_value_id_seq restart with 1;
	alter sequence cs_process_property_value_id_seq restart with 1;
	alter sequence cs_property_value_id_seq restart with 1;

	select create_project_instance(1, 1) into id;
	select create_project_instance(3, 1) into id;
	select create_project_instance(4, 1) into id;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.reinit_instances() OWNER TO postgres;

--
-- TOC entry 58 (class 1255 OID 81746)
-- Dependencies: 616 5
-- Name: restart_from_action(bigint, bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION restart_from_action(a_process_id bigint, a_action_id bigint) RETURNS boolean
    AS $$DECLARE
	aid	int8;
	pid	int8;
	res	int8;
	fid	int8;
BEGIN
	FOR aid IN SELECT id FROM process_instance_actions_list WHERE instance_id = a_process_id AND npp >= (SELECT npp FROM process_instance_actions_list WHERE id = a_action_id) AND id <> a_action_id LOOP
		UPDATE cs_process_current_action SET status_id = NULL, initiator_id = NULL, performer_id = NULL, started_at = NULL, ended_at = NULL WHERE id = aid;
		DELETE FROM cs_account_today WHERE action_instance_id = aid AND process_instance_id = a_process_id;
		DELETE FROM cs_process_current_action_performer WHERE instance_action_id = aid;
		FOR pid IN SELECT process_id FROM cs_process_action_child WHERE action_id = aid LOOP
			SELECT erase_process_instance(pid);
		END LOOP;
	END LOOP;
	UPDATE cs_process_current_action SET status_id = 1, ended_at = NULL WHERE id = a_action_id;
	FOR pid IN SELECT process_id FROM cs_process_action_child WHERE action_id = a_action_id LOOP
		EXECUTE erase_process_instance(pid);
	END LOOP;

	RETURN true;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.restart_from_action(a_process_id bigint, a_action_id bigint) OWNER TO postgres;

--
-- TOC entry 59 (class 1255 OID 81747)
-- Dependencies: 616 5
-- Name: sort_process_actions(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION sort_process_actions(a_process_id integer) RETURNS boolean
    AS $$DECLARE
	a_id		int4;
	a_npp		int2;
	a_old_npp	int2;
BEGIN
	a_old_npp = 0;
	UPDATE cs_process_action SET npp = NULL WHERE process_id = a_process_id;
	FOR a_id IN SELECT id FROM cs_process_action WHERE process_id = a_process_id AND type_id < 7 ORDER BY type_id, id, name LOOP
		a_npp = get_npp(a_id, 0::int2);
		UPDATE cs_process_action SET npp = a_npp WHERE id = a_id;
		IF a_npp > a_old_npp THEN
			a_old_npp = a_npp;
		END IF;
	END LOOP;
	FOR a_id IN SELECT id FROM cs_process_action WHERE process_id = a_process_id AND type_id >= 7 ORDER BY type_id, id, name LOOP
		a_old_npp = a_old_npp + 1;
		UPDATE cs_process_action SET npp = a_old_npp WHERE id = a_id;
	END LOOP;
	RETURN true;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.sort_process_actions(a_process_id integer) OWNER TO postgres;

--
-- TOC entry 60 (class 1255 OID 81748)
-- Dependencies: 616 5
-- Name: swap_documents_npp(bigint, bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION swap_documents_npp(first_doc_id bigint, second_doc_id bigint) RETURNS boolean
    AS $$DECLARE
	first_npp	int4;
	second_npp	int4;
BEGIN
	SELECT npp FROM cs_public_document WHERE id = first_doc_id INTO first_npp;
	SELECT npp FROM cs_public_document WHERE id = second_doc_id INTO second_npp;
	
	UPDATE cs_public_document SET npp = second_npp WHERE id = first_doc_id;
	UPDATE cs_public_document SET npp = first_npp WHERE id = second_doc_id;

	RETURN true;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.swap_documents_npp(first_doc_id bigint, second_doc_id bigint) OWNER TO postgres;

--
-- TOC entry 61 (class 1255 OID 81749)
-- Dependencies: 616 5
-- Name: terminate_process_instance(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION terminate_process_instance(instance_id bigint) RETURNS void
    AS $$DECLARE
	pid	int8;
BEGIN
	SELECT parent_id FROM cs_process_instance WHERE id = instance_id INTO pid;
	IF pid IS NULL THEN
		UPDATE cs_process_instance SET status_id = 2, ended_at = now() WHERE id = instance_id;
	ELSE
		UPDATE cs_process_instance SET status_id = 9, ended_at = now() WHERE id = instance_id;
	END IF;

	FOR pid IN select id from cs_process_instance WHERE parent_id = instance_id LOOP
		EXECUTE terminate_process_instance(pid);
	END LOOP;
END;$$
    LANGUAGE plpgsql;


ALTER FUNCTION public.terminate_process_instance(instance_id bigint) OWNER TO postgres;

--
-- TOC entry 1626 (class 1259 OID 81750)
-- Dependencies: 5
-- Name: cs_account_post; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_account_post (
    id integer NOT NULL,
    account_id integer,
    post_id integer,
    division_id integer
);


ALTER TABLE public.cs_account_post OWNER TO postgres;

--
-- TOC entry 1627 (class 1259 OID 81752)
-- Dependencies: 5
-- Name: cs_post; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_post (
    id integer NOT NULL,
    name character varying(150),
    description text
);


ALTER TABLE public.cs_post OWNER TO postgres;

--
-- TOC entry 1628 (class 1259 OID 81757)
-- Dependencies: 1946 5
-- Name: divisions_tree; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW divisions_tree AS
    SELECT child.id, child.name, child.parent_id, parent.name AS parentname, child.description, child.boss_id, cs_account.name AS bossname, (SELECT get_level('cs_division'::text, child.id) AS get_level) AS "level" FROM (((cs_division child JOIN get_full_tree('cs_division'::text) pid(pid) ON ((child.id = pid.pid))) LEFT JOIN cs_account ON ((child.boss_id = cs_account.id))) LEFT JOIN cs_division parent ON ((child.parent_id = parent.id)));


ALTER TABLE public.divisions_tree OWNER TO postgres;

--
-- TOC entry 1629 (class 1259 OID 81761)
-- Dependencies: 1947 5
-- Name: account_posts_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW account_posts_list AS
    SELECT cs_account_post.id, cs_account_post.account_id, cs_account_post.post_id, cs_account_post.division_id, cs_account.name AS accountname, cs_account.description AS accountdescr, cs_post.name AS postname, cs_post.description AS postdescr, divisions_tree.name AS divisionname, divisions_tree.description AS divisiondescr, divisions_tree.parentname AS parentdivisionname FROM cs_account_post, cs_account, cs_post, divisions_tree WHERE (((cs_account_post.account_id = cs_account.id) AND (cs_account_post.post_id = cs_post.id)) AND (cs_account_post.division_id = divisions_tree.id));


ALTER TABLE public.account_posts_list OWNER TO postgres;

--
-- TOC entry 1630 (class 1259 OID 81764)
-- Dependencies: 2264 5
-- Name: cs_account_today; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_account_today (
    id bigint NOT NULL,
    account_id integer,
    process_instance_id bigint,
    action_instance_id bigint,
    status_id integer,
    started_at timestamp without time zone,
    ended_at timestamp without time zone,
    confirm boolean DEFAULT true
);


ALTER TABLE public.cs_account_today OWNER TO postgres;

--
-- TOC entry 1631 (class 1259 OID 81767)
-- Dependencies: 2266 5
-- Name: cs_action_type; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_action_type (
    id integer NOT NULL,
    name character varying(150),
    description text,
    in_use boolean DEFAULT true
);


ALTER TABLE public.cs_action_type OWNER TO postgres;

--
-- TOC entry 1632 (class 1259 OID 81773)
-- Dependencies: 2268 2269 2270 2271 2272 2273 5
-- Name: cs_process; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_process (
    id integer NOT NULL,
    parent_id integer,
    name character varying(150),
    description text,
    author_id integer,
    version real DEFAULT 1.0,
    created_at timestamp without time zone,
    activated_at timestamp without time zone,
    is_active boolean DEFAULT false,
    is_public boolean DEFAULT false,
    is_standalone boolean DEFAULT true,
    is_hidden boolean DEFAULT false,
    is_system boolean DEFAULT false
);


ALTER TABLE public.cs_process OWNER TO postgres;

--
-- TOC entry 1633 (class 1259 OID 81784)
-- Dependencies: 2275 2276 5
-- Name: cs_process_action; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_process_action (
    id integer NOT NULL,
    name character varying(150),
    description text,
    process_id integer,
    role_id integer,
    type_id integer,
    is_interactive boolean,
    weight real DEFAULT 0.0,
    planed reltime,
    fixed_planed boolean DEFAULT true,
    form text,
    code text,
    npp smallint,
    true_action_id integer,
    false_action_id integer,
    condition text
);


ALTER TABLE public.cs_process_action OWNER TO postgres;

--
-- TOC entry 1634 (class 1259 OID 81791)
-- Dependencies: 2278 5
-- Name: cs_process_current_action; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_process_current_action (
    id bigint NOT NULL,
    instance_id bigint,
    action_id integer,
    status_id integer,
    initiator_id integer,
    performer_id integer,
    planed reltime,
    fixed_planed boolean DEFAULT true,
    started_at timestamp without time zone,
    ended_at timestamp without time zone
);


ALTER TABLE public.cs_process_current_action OWNER TO postgres;

--
-- TOC entry 1635 (class 1259 OID 81794)
-- Dependencies: 5
-- Name: cs_process_instance; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_process_instance (
    id bigint NOT NULL,
    parent_id bigint,
    process_id integer,
    initiator_id integer,
    status_id integer,
    started_at timestamp without time zone,
    ended_at timestamp without time zone
);


ALTER TABLE public.cs_process_instance OWNER TO postgres;

--
-- TOC entry 1636 (class 1259 OID 81796)
-- Dependencies: 2281 2282 5
-- Name: cs_project; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_project (
    id integer NOT NULL,
    name character varying(150),
    description text,
    is_permanent boolean,
    author_id integer,
    version real DEFAULT 1.0,
    created_at timestamp without time zone,
    activated_at timestamp without time zone,
    is_active boolean,
    is_system boolean DEFAULT false
);


ALTER TABLE public.cs_project OWNER TO postgres;

--
-- TOC entry 1637 (class 1259 OID 81803)
-- Dependencies: 5
-- Name: cs_project_instance; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_project_instance (
    id bigint NOT NULL,
    project_id integer,
    initiator_id integer,
    status_id integer,
    started_at timestamp without time zone,
    ended_at timestamp without time zone
);


ALTER TABLE public.cs_project_instance OWNER TO postgres;

--
-- TOC entry 1638 (class 1259 OID 81805)
-- Dependencies: 5
-- Name: cs_project_process_instance; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_project_process_instance (
    id bigint NOT NULL,
    process_instance_id bigint,
    project_instance_id bigint
);


ALTER TABLE public.cs_project_process_instance OWNER TO postgres;

--
-- TOC entry 1639 (class 1259 OID 81807)
-- Dependencies: 5
-- Name: cs_status; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_status (
    id integer NOT NULL,
    name character varying(150),
    description text
);


ALTER TABLE public.cs_status OWNER TO postgres;

--
-- TOC entry 1640 (class 1259 OID 81812)
-- Dependencies: 1948 5
-- Name: process_instance_actions_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW process_instance_actions_list AS
    SELECT cs_process_current_action.id, cs_process_current_action.instance_id, cs_process_current_action.action_id, cs_process_current_action.status_id, cs_process_current_action.initiator_id, cs_process_current_action.performer_id, cs_process_current_action.planed, cs_process_current_action.fixed_planed, cs_process_current_action.started_at, cs_process_current_action.ended_at, cs_process_action.process_id, cs_process_action.name, cs_process_action.description, cs_process_action.type_id, cs_action_type.name AS typename, cs_process_action.is_interactive, cs_process_action.weight, cs_process_action.code, cs_process_action.form, cs_process_action.true_action_id, true_process_action.name AS trueactionname, cs_process_action.false_action_id, false_process_action.name AS falseactionname, cs_status.name AS statusname, cs_initiator.name AS initiatorname, cs_performer.name AS performername, cs_process_action.npp, cs_process_action.condition FROM (((((((cs_process_current_action LEFT JOIN cs_status ON ((cs_process_current_action.status_id = cs_status.id))) LEFT JOIN cs_account cs_initiator ON ((cs_process_current_action.initiator_id = cs_initiator.id))) LEFT JOIN cs_account cs_performer ON ((cs_process_current_action.performer_id = cs_performer.id))) LEFT JOIN cs_process_action ON ((cs_process_current_action.action_id = cs_process_action.id))) LEFT JOIN cs_process_action true_process_action ON ((cs_process_action.true_action_id = true_process_action.id))) LEFT JOIN cs_process_action false_process_action ON ((cs_process_action.false_action_id = false_process_action.id))) LEFT JOIN cs_action_type ON ((cs_process_action.type_id = cs_action_type.id))) ORDER BY cs_process_action.npp, cs_process_current_action.id, cs_process_current_action.instance_id, cs_process_current_action.action_id, cs_process_current_action.status_id, cs_process_current_action.initiator_id, cs_process_current_action.performer_id, cs_process_current_action.started_at, cs_process_current_action.ended_at, cs_process_action.name, cs_process_action.description, cs_process_action.type_id, cs_action_type.name, cs_process_action.is_interactive, cs_process_action.weight, cs_process_action.code, cs_process_action.form, cs_status.name, cs_initiator.name, cs_performer.name, cs_process_action.planed, cs_process_action.fixed_planed, cs_process_action.process_id;


ALTER TABLE public.process_instance_actions_list OWNER TO postgres;

--
-- TOC entry 1641 (class 1259 OID 81816)
-- Dependencies: 1949 5
-- Name: process_instances_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW process_instances_list AS
    SELECT child.id, child.parent_id, child.process_id, child.initiator_id, child.status_id, child.started_at, child.ended_at, process.name, process.description, process.version, cs_status.name AS statusname, cs_account.name AS initiatorname FROM ((((cs_process_instance child LEFT JOIN cs_process process ON ((child.process_id = process.id))) LEFT JOIN cs_process_instance parent ON ((child.parent_id = parent.id))) LEFT JOIN cs_account ON ((child.initiator_id = cs_account.id))) LEFT JOIN cs_status ON ((child.status_id = cs_status.id)));


ALTER TABLE public.process_instances_list OWNER TO postgres;

--
-- TOC entry 1642 (class 1259 OID 81820)
-- Dependencies: 1950 5
-- Name: project_processes_instances_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW project_processes_instances_list AS
    SELECT cs_project_process_instance.project_instance_id, cs_project_instance.project_id, cs_project.name AS projectname, child.id, child.parent_id, parent.name AS parentname, child.process_id, child.initiator_id, child.status_id, child.started_at, child.ended_at, child.name, child.description, child.statusname, child.initiatorname, child.version FROM cs_project_process_instance, cs_project_instance, cs_project, (process_instances_list child LEFT JOIN process_instances_list parent ON ((child.parent_id = parent.id))) WHERE (((cs_project_process_instance.process_instance_id = child.id) AND (cs_project_process_instance.project_instance_id = cs_project_instance.id)) AND (cs_project_instance.project_id = cs_project.id));


ALTER TABLE public.project_processes_instances_list OWNER TO postgres;

--
-- TOC entry 1643 (class 1259 OID 81823)
-- Dependencies: 1951 5
-- Name: account_today_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW account_today_list AS
    SELECT cs_account_today.id, cs_account_today.account_id, cs_account_today.process_instance_id, cs_account_today.action_instance_id, cs_account_today.status_id, cs_account_today.started_at, cs_account_today.ended_at, cs_account_today.confirm, cs_account.name AS accountname, cs_account.description AS accountdescr, project_processes_instances_list.process_id, project_processes_instances_list.project_id, project_processes_instances_list.project_instance_id, project_processes_instances_list.projectname, project_processes_instances_list.initiator_id, project_processes_instances_list.initiatorname, project_processes_instances_list.name AS processname, project_processes_instances_list.description AS processdescr, project_processes_instances_list.parent_id, project_processes_instances_list.parentname, process_instance_actions_list.name AS actionname, process_instance_actions_list.description AS actiondescr, cs_status.name AS statusname, cs_status.description AS statusdescr, process_instance_actions_list.npp FROM ((((cs_account_today LEFT JOIN cs_account ON ((cs_account.id = cs_account_today.account_id))) LEFT JOIN project_processes_instances_list ON ((project_processes_instances_list.id = cs_account_today.process_instance_id))) LEFT JOIN process_instance_actions_list ON ((process_instance_actions_list.id = cs_account_today.action_instance_id))) LEFT JOIN cs_status ON ((cs_status.id = cs_account_today.status_id)));


ALTER TABLE public.account_today_list OWNER TO postgres;

--
-- TOC entry 1644 (class 1259 OID 81827)
-- Dependencies: 1952 5
-- Name: accounts_without_groups_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW accounts_without_groups_list AS
    SELECT cs_account.id, cs_account.parent_id, cs_account.name, cs_account.description, cs_account.permission_id, cs_account.passwd, cs_account.email, cs_account.icq, cs_account.jabber, cs_account.is_active, cs_account.cell, cs_account.cellop_id, cs_account.division_id FROM cs_account WHERE ((cs_account.permission_id IS NOT NULL) AND (cs_account.passwd IS NOT NULL)) ORDER BY cs_account.name, cs_account.id;


ALTER TABLE public.accounts_without_groups_list OWNER TO postgres;

--
-- TOC entry 1645 (class 1259 OID 81830)
-- Dependencies: 1953 5
-- Name: action_instances_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW action_instances_list AS
    SELECT cs_process_current_action.id, cs_process_current_action.instance_id, cs_process_current_action.action_id, cs_process_current_action.started_at, cs_process_current_action.ended_at, cs_process_current_action.status_id, cs_process_action.weight, cs_process_action.planed, cs_process_action.npp FROM cs_process_current_action, cs_process_action WHERE (cs_process_current_action.action_id = cs_process_action.id);


ALTER TABLE public.action_instances_list OWNER TO postgres;

--
-- TOC entry 1646 (class 1259 OID 81833)
-- Dependencies: 1954 5
-- Name: processes_tree; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW processes_tree AS
    SELECT child.id, child.parent_id, child.name, child.description, child.author_id, child.version, child.created_at, child.activated_at, child.is_active, child.is_public, child.is_standalone, child.is_hidden, child.is_system, parent.name AS parentname, cs_account.name AS authorname, (SELECT get_level('cs_process'::text, child.id) AS get_level) AS "level" FROM (((cs_process child JOIN get_full_tree('cs_process'::text) pid(pid) ON ((child.id = pid.pid))) LEFT JOIN cs_process parent ON ((child.parent_id = parent.id))) LEFT JOIN cs_account ON ((child.author_id = cs_account.id)));


ALTER TABLE public.processes_tree OWNER TO postgres;

--
-- TOC entry 1647 (class 1259 OID 81837)
-- Dependencies: 1955 5
-- Name: active_processes_tree; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW active_processes_tree AS
    SELECT processes_tree.id, processes_tree.parent_id, processes_tree.name, processes_tree.description, processes_tree.author_id, processes_tree.version, processes_tree.created_at, processes_tree.activated_at, processes_tree.is_active, processes_tree.is_public, processes_tree.is_standalone, processes_tree.parentname, processes_tree.authorname, processes_tree."level" FROM processes_tree WHERE (processes_tree.is_active = true);


ALTER TABLE public.active_processes_tree OWNER TO postgres;

--
-- TOC entry 1648 (class 1259 OID 81840)
-- Dependencies: 2287 5
-- Name: cs_calendar; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_calendar (
    id integer NOT NULL,
    name character varying(150),
    description text,
    owner_id integer,
    is_public boolean,
    is_deleted boolean DEFAULT false
);


ALTER TABLE public.cs_calendar OWNER TO postgres;

--
-- TOC entry 1649 (class 1259 OID 81846)
-- Dependencies: 2289 2290 5
-- Name: cs_calendar_event; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_calendar_event (
    id bigint NOT NULL,
    calendar_id integer,
    status_id integer,
    author_id integer,
    started_at timestamp without time zone,
    ended_at timestamp without time zone,
    subject character varying(250),
    event text,
    is_periodic boolean,
    priority_id integer,
    created_at timestamp without time zone,
    is_deleted boolean DEFAULT false,
    is_erased boolean DEFAULT false
);


ALTER TABLE public.cs_calendar_event OWNER TO postgres;

--
-- TOC entry 1650 (class 1259 OID 81853)
-- Dependencies: 5
-- Name: cs_calendar_event_period_detail; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_calendar_event_period_detail (
    id bigint NOT NULL,
    event_id bigint,
    started_at timestamp without time zone,
    ended_at timestamp without time zone
);


ALTER TABLE public.cs_calendar_event_period_detail OWNER TO postgres;

--
-- TOC entry 1651 (class 1259 OID 81855)
-- Dependencies: 5
-- Name: cs_event_priority; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_event_priority (
    id integer NOT NULL,
    name character varying(150),
    description text
);


ALTER TABLE public.cs_event_priority OWNER TO postgres;

--
-- TOC entry 1652 (class 1259 OID 81860)
-- Dependencies: 5
-- Name: cs_event_status; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_event_status (
    id integer NOT NULL,
    name character varying(150),
    description text
);


ALTER TABLE public.cs_event_status OWNER TO postgres;

--
-- TOC entry 1653 (class 1259 OID 81865)
-- Dependencies: 1956 5
-- Name: calendar_event_period_details_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW calendar_event_period_details_list AS
    SELECT cs_calendar_event_period_detail.id, cs_calendar_event_period_detail.event_id, cs_calendar_event_period_detail.started_at, cs_calendar_event_period_detail.ended_at, cs_calendar_event.calendar_id, cs_calendar_event.status_id, cs_calendar_event.author_id, cs_calendar_event.started_at AS event_started_at, cs_calendar_event.ended_at AS event_ended_at, cs_calendar_event.subject AS eventsubject, cs_calendar_event.event, cs_calendar_event.is_periodic, cs_calendar_event.priority_id, cs_calendar_event.created_at, cs_calendar_event.is_deleted, cs_calendar_event.is_erased, cs_calendar.name AS calendarname, cs_calendar.description AS calendardescr, cs_calendar.is_public, cs_calendar.is_deleted AS calendar_is_deleted, cs_event_status.name AS statusname, cs_event_status.description AS statusdescr, cs_event_priority.name AS priorityname, cs_event_priority.description AS prioritydescr, cs_account.name AS authorname, cs_account.description AS authordescr FROM cs_calendar_event_period_detail, cs_calendar_event, cs_calendar, cs_event_status, cs_event_priority, cs_account WHERE (((((cs_calendar_event_period_detail.event_id = cs_calendar_event.id) AND (cs_calendar_event.calendar_id = cs_calendar.id)) AND (cs_calendar_event.status_id = cs_event_status.id)) AND (cs_calendar_event.priority_id = cs_event_priority.id)) AND (cs_calendar_event.author_id = cs_account.id));


ALTER TABLE public.calendar_event_period_details_list OWNER TO postgres;

--
-- TOC entry 1654 (class 1259 OID 81869)
-- Dependencies: 5
-- Name: cs_calendar_event_period; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_calendar_event_period (
    id bigint NOT NULL,
    event_id bigint,
    period_id integer,
    condition_id integer,
    value text
);


ALTER TABLE public.cs_calendar_event_period OWNER TO postgres;

--
-- TOC entry 1655 (class 1259 OID 81874)
-- Dependencies: 5
-- Name: cs_event_period; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_event_period (
    id integer NOT NULL,
    name character varying(150),
    description text
);


ALTER TABLE public.cs_event_period OWNER TO postgres;

--
-- TOC entry 1656 (class 1259 OID 81879)
-- Dependencies: 5
-- Name: cs_event_period_condition; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_event_period_condition (
    id integer NOT NULL,
    period_id integer,
    name character varying(150),
    description text
);


ALTER TABLE public.cs_event_period_condition OWNER TO postgres;

--
-- TOC entry 1657 (class 1259 OID 81884)
-- Dependencies: 1957 5
-- Name: calendar_event_periods_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW calendar_event_periods_list AS
    SELECT cs_calendar_event_period.id, cs_calendar_event_period.event_id, cs_calendar_event_period.period_id, cs_calendar_event_period.condition_id, cs_calendar_event_period.value, cs_calendar_event.calendar_id, cs_calendar_event.author_id, cs_calendar_event.status_id, cs_calendar_event.started_at, cs_calendar_event.ended_at, cs_calendar_event.subject, cs_calendar_event.event, cs_calendar_event.is_periodic, cs_calendar_event.priority_id, cs_calendar_event.created_at, cs_author.name AS authorname, cs_author.description AS authordescr, cs_event_status.name AS statusname, cs_event_status.description AS statusdescr, cs_event_priority.name AS priorityname, cs_event_priority.description AS prioritydescr, cs_event_period.name AS periodname, cs_event_period.description AS perioddescr, cs_event_period_condition.name AS conditionname, cs_event_period_condition.description AS conditiondescr FROM cs_calendar_event_period, cs_calendar_event, cs_account cs_author, cs_event_status, cs_event_priority, cs_event_period, cs_event_period_condition WHERE ((((((cs_calendar_event_period.event_id = cs_calendar_event.id) AND (cs_calendar_event.author_id = cs_author.id)) AND (cs_calendar_event.status_id = cs_event_status.id)) AND (cs_calendar_event.priority_id = cs_event_priority.id)) AND (cs_calendar_event_period.period_id = cs_event_period.id)) AND (cs_calendar_event_period.condition_id = cs_event_period_condition.id));


ALTER TABLE public.calendar_event_periods_list OWNER TO postgres;

--
-- TOC entry 1658 (class 1259 OID 81888)
-- Dependencies: 2298 2299 5
-- Name: cs_calendar_event_reciever; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_calendar_event_reciever (
    id bigint NOT NULL,
    event_id bigint,
    account_id integer,
    status_id integer,
    is_deleted boolean DEFAULT false,
    permission_id integer,
    is_erased boolean DEFAULT false
);


ALTER TABLE public.cs_calendar_event_reciever OWNER TO postgres;

--
-- TOC entry 1659 (class 1259 OID 81892)
-- Dependencies: 5
-- Name: cs_object_permission; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_object_permission (
    id integer NOT NULL,
    name character varying(150),
    description text
);


ALTER TABLE public.cs_object_permission OWNER TO postgres;

--
-- TOC entry 1660 (class 1259 OID 81897)
-- Dependencies: 1958 5
-- Name: calendar_event_recievers_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW calendar_event_recievers_list AS
    SELECT cs_calendar_event_reciever.id, cs_calendar_event_reciever.event_id, cs_calendar_event_reciever.account_id, cs_calendar_event_reciever.status_id, cs_calendar_event_reciever.is_deleted, cs_calendar_event_reciever.is_erased, cs_calendar_event_reciever.permission_id, cs_calendar_event.calendar_id, cs_calendar_event.author_id, cs_calendar_event.started_at, cs_calendar_event.ended_at, cs_calendar_event.subject, cs_calendar_event.event, cs_calendar_event.is_periodic, cs_calendar_event.priority_id, cs_calendar_event.created_at, cs_calendar_event.is_deleted AS event_is_deleted, cs_calendar_event.is_erased AS event_is_erased, cs_author.name AS authorname, cs_author.description AS authordescr, cs_reciever.name AS recievername, cs_reciever.description AS recieverdescr, cs_event_status.name AS statusname, cs_event_status.description AS statusdescr, cs_event_priority.name AS priorityname, cs_event_priority.description AS prioritydescr, cs_object_permission.name AS permissionname, cs_object_permission.description AS permissiondescr FROM cs_calendar_event_reciever, cs_calendar_event, cs_account cs_author, cs_account cs_reciever, cs_event_status, cs_event_priority, cs_object_permission WHERE ((((((cs_calendar_event_reciever.event_id = cs_calendar_event.id) AND (cs_calendar_event.author_id = cs_author.id)) AND (cs_calendar_event_reciever.account_id = cs_reciever.id)) AND (cs_calendar_event_reciever.status_id = cs_event_status.id)) AND (cs_calendar_event.priority_id = cs_event_priority.id)) AND (cs_calendar_event_reciever.permission_id = cs_object_permission.id));


ALTER TABLE public.calendar_event_recievers_list OWNER TO postgres;

--
-- TOC entry 1661 (class 1259 OID 81901)
-- Dependencies: 5
-- Name: cs_calendar_event_alarm; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_calendar_event_alarm (
    id bigint NOT NULL,
    event_id bigint,
    alarm_at timestamp without time zone,
    transport_id integer,
    subject character varying(250),
    message text
);


ALTER TABLE public.cs_calendar_event_alarm OWNER TO postgres;

--
-- TOC entry 1662 (class 1259 OID 81906)
-- Dependencies: 5
-- Name: cs_transport; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_transport (
    id integer NOT NULL,
    name character varying(150),
    description text,
    server_address character varying(50),
    server_login character varying(30),
    server_passwd character varying(30),
    server_port character varying(10),
    class_name character varying(30)
);


ALTER TABLE public.cs_transport OWNER TO postgres;

--
-- TOC entry 1663 (class 1259 OID 81911)
-- Dependencies: 1959 5
-- Name: calendar_events_alarms_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW calendar_events_alarms_list AS
    SELECT cs_calendar_event_alarm.id, cs_calendar_event_alarm.event_id, cs_calendar_event_alarm.alarm_at, cs_calendar_event_alarm.transport_id, cs_calendar_event_alarm.subject, cs_calendar_event_alarm.message, cs_calendar_event.calendar_id, cs_calendar_event.status_id, cs_calendar_event.author_id, cs_calendar_event.started_at, cs_calendar_event.ended_at, cs_calendar_event.subject AS eventsubject, cs_calendar_event.event, cs_calendar_event.is_periodic, cs_calendar_event.priority_id, cs_calendar_event.created_at, cs_calendar_event.is_deleted, cs_calendar_event.is_erased, cs_calendar.name AS calendarname, cs_calendar.description AS calendardescr, cs_calendar.is_public, cs_calendar.is_deleted AS calendar_is_deleted, cs_event_status.name AS statusname, cs_event_status.description AS statusdescr, cs_event_priority.name AS priorityname, cs_event_priority.description AS prioritydescr, cs_account.name AS authorname, cs_account.description AS authordescr, cs_transport.name AS transportname, cs_transport.description AS transportdescr, cs_transport.class_name AS transportclassname, cs_transport.server_address, cs_transport.server_login, cs_transport.server_passwd, cs_transport.server_port FROM cs_calendar_event_alarm, cs_calendar_event, cs_calendar, cs_event_status, cs_event_priority, cs_account, cs_transport WHERE ((((((cs_calendar_event_alarm.event_id = cs_calendar_event.id) AND (cs_calendar_event.calendar_id = cs_calendar.id)) AND (cs_calendar_event.status_id = cs_event_status.id)) AND (cs_calendar_event.priority_id = cs_event_priority.id)) AND (cs_calendar_event.author_id = cs_account.id)) AND (cs_calendar_event_alarm.transport_id = cs_transport.id));


ALTER TABLE public.calendar_events_alarms_list OWNER TO postgres;

--
-- TOC entry 1664 (class 1259 OID 81915)
-- Dependencies: 1960 5
-- Name: calendar_events_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW calendar_events_list AS
    SELECT cs_calendar_event.id, cs_calendar_event.calendar_id, cs_calendar_event.status_id, cs_calendar_event.author_id, cs_calendar_event.started_at, cs_calendar_event.ended_at, cs_calendar_event.subject, cs_calendar_event.event, cs_calendar_event.is_periodic, cs_calendar_event.priority_id, cs_calendar_event.created_at, cs_calendar_event.is_deleted, cs_calendar_event.is_erased, cs_calendar.name AS calendarname, cs_calendar.description AS calendardescr, cs_calendar.is_public, cs_calendar.is_deleted AS calendar_is_deleted, cs_event_status.name AS statusname, cs_event_status.description AS statusdescr, cs_event_priority.name AS priorityname, cs_event_priority.description AS prioritydescr, cs_account.name AS authorname, cs_account.description AS authordescr FROM cs_calendar_event, cs_calendar, cs_event_status, cs_event_priority, cs_account WHERE ((((cs_calendar_event.calendar_id = cs_calendar.id) AND (cs_calendar_event.status_id = cs_event_status.id)) AND (cs_calendar_event.priority_id = cs_event_priority.id)) AND (cs_calendar_event.author_id = cs_account.id));


ALTER TABLE public.calendar_events_list OWNER TO postgres;

--
-- TOC entry 1665 (class 1259 OID 81918)
-- Dependencies: 5
-- Name: cs_calendar_permission; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_calendar_permission (
    id integer NOT NULL,
    calendar_id integer,
    account_id integer,
    permission_id integer
);


ALTER TABLE public.cs_calendar_permission OWNER TO postgres;

--
-- TOC entry 1666 (class 1259 OID 81920)
-- Dependencies: 1961 5
-- Name: calendar_permissions_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW calendar_permissions_list AS
    SELECT cs_calendar_permission.id, cs_calendar_permission.calendar_id, cs_calendar_permission.account_id, cs_calendar_permission.permission_id, cs_calendar.name AS calendarname, cs_calendar.description AS calendardescr, cs_calendar.is_public, cs_account.name AS accountname, cs_account.description AS accountdescr, cs_object_permission.name AS permissionname, cs_object_permission.description AS permissiondescr FROM cs_calendar_permission, cs_calendar, cs_account, cs_object_permission WHERE (((cs_calendar_permission.calendar_id = cs_calendar.id) AND (cs_calendar_permission.account_id = cs_account.id)) AND (cs_calendar_permission.permission_id = cs_object_permission.id));


ALTER TABLE public.calendar_permissions_list OWNER TO postgres;

--
-- TOC entry 1870 (class 1259 OID 84333)
-- Dependencies: 2000 5
-- Name: calendars_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW calendars_list AS
    SELECT cs_calendar.id, cs_calendar.name, cs_calendar.description, cs_calendar.owner_id, cs_calendar.is_public, cs_calendar.is_deleted, cs_account.name AS ownername, cs_account.description AS ownerdescr FROM cs_calendar, cs_account WHERE (cs_calendar.owner_id = cs_account.id);


ALTER TABLE public.calendars_list OWNER TO postgres;

--
-- TOC entry 1667 (class 1259 OID 81926)
-- Dependencies: 5
-- Name: cs_chrono_action; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_chrono_action (
    id bigint NOT NULL,
    chrono_id bigint,
    process_instance_id bigint,
    action_instance_id bigint,
    action_id integer,
    status_id integer,
    initiator_id integer,
    performer_id integer,
    planed reltime,
    fixed_planed boolean,
    started_at timestamp without time zone,
    ended_at timestamp without time zone
);


ALTER TABLE public.cs_chrono_action OWNER TO postgres;

--
-- TOC entry 1668 (class 1259 OID 81928)
-- Dependencies: 1962 5
-- Name: chrono_process_instance_actions_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW chrono_process_instance_actions_list AS
    SELECT cs_chrono_action.id, cs_chrono_action.chrono_id, cs_chrono_action.process_instance_id, cs_chrono_action.action_instance_id, cs_chrono_action.action_id, cs_chrono_action.status_id, cs_chrono_action.initiator_id, cs_chrono_action.performer_id, cs_chrono_action.planed, cs_chrono_action.fixed_planed, cs_chrono_action.started_at, cs_chrono_action.ended_at, cs_process_action.process_id, cs_process_action.name, cs_process_action.description, cs_process_action.type_id, cs_action_type.name AS typename, cs_process_action.is_interactive, cs_process_action.weight, cs_process_action.code, cs_process_action.form, cs_process_action.true_action_id, true_process_action.name AS trueactionname, cs_process_action.false_action_id, false_process_action.name AS falseactionname, cs_status.name AS statusname, cs_initiator.name AS initiatorname, cs_performer.name AS performername, cs_process_action.npp FROM (((((((cs_chrono_action LEFT JOIN cs_status ON ((cs_chrono_action.status_id = cs_status.id))) LEFT JOIN cs_account cs_initiator ON ((cs_chrono_action.initiator_id = cs_initiator.id))) LEFT JOIN cs_account cs_performer ON ((cs_chrono_action.performer_id = cs_performer.id))) LEFT JOIN cs_process_action ON ((cs_chrono_action.action_id = cs_process_action.id))) LEFT JOIN cs_process_action true_process_action ON ((cs_process_action.true_action_id = true_process_action.id))) LEFT JOIN cs_process_action false_process_action ON ((cs_process_action.false_action_id = false_process_action.id))) LEFT JOIN cs_action_type ON ((cs_process_action.type_id = cs_action_type.id))) ORDER BY cs_process_action.npp, cs_chrono_action.id, cs_chrono_action.chrono_id, cs_chrono_action.process_instance_id, cs_chrono_action.action_instance_id;


ALTER TABLE public.chrono_process_instance_actions_list OWNER TO postgres;

--
-- TOC entry 1669 (class 1259 OID 81932)
-- Dependencies: 5
-- Name: cs_chrono_property; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_chrono_property (
    id bigint NOT NULL,
    process_instance_id bigint,
    property_instance_id bigint,
    property_id integer,
    property_value_id bigint,
    value_id bigint,
    chrono_id bigint
);


ALTER TABLE public.cs_chrono_property OWNER TO postgres;

--
-- TOC entry 1670 (class 1259 OID 81934)
-- Dependencies: 5
-- Name: cs_chrono_value; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_chrono_value (
    id bigint NOT NULL,
    mime_type character varying(150),
    value text
);


ALTER TABLE public.cs_chrono_value OWNER TO postgres;

--
-- TOC entry 1671 (class 1259 OID 81939)
-- Dependencies: 2308 2309 2310 2311 2312 5
-- Name: cs_process_property; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_process_property (
    id integer NOT NULL,
    name character varying(150),
    description text,
    process_id integer,
    sign_id integer DEFAULT 1,
    type_id integer DEFAULT 1,
    default_value text,
    is_list boolean DEFAULT false,
    is_name_as_value boolean DEFAULT false,
    directory_id integer,
    value_field text DEFAULT 'name'::text
);


ALTER TABLE public.cs_process_property OWNER TO postgres;

--
-- TOC entry 1672 (class 1259 OID 81949)
-- Dependencies: 5
-- Name: cs_property_type; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_property_type (
    id integer NOT NULL,
    name character varying(150),
    description text
);


ALTER TABLE public.cs_property_type OWNER TO postgres;

--
-- TOC entry 1673 (class 1259 OID 81954)
-- Dependencies: 5
-- Name: cs_sign; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_sign (
    id integer NOT NULL,
    name character varying(150),
    description text
);


ALTER TABLE public.cs_sign OWNER TO postgres;

--
-- TOC entry 1674 (class 1259 OID 81959)
-- Dependencies: 1963 5
-- Name: chrono_process_instance_properties_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW chrono_process_instance_properties_list AS
    SELECT cs_chrono_property.id, cs_chrono_property.chrono_id, cs_chrono_property.process_instance_id, cs_chrono_property.property_instance_id, cs_chrono_property.property_id, cs_chrono_property.property_value_id, cs_chrono_property.value_id, cs_process_property.name, cs_process_property.description, cs_process_property.sign_id, cs_sign.name AS signname, cs_process_property.type_id, cs_property_type.name AS typename, cs_process_property.is_list, cs_process_property.is_name_as_value, cs_process_property.value_field, cs_process_property.directory_id, (SELECT get_property_directory_name(cs_process_property.directory_id) AS get_property_directory_name) AS directoryname, (SELECT get_property_directory_tablename(cs_process_property.directory_id) AS get_property_directory_tablename) AS directorytablename, (SELECT get_property_directory_parameters(cs_process_property.directory_id) AS get_property_directory_parameters) AS directoryparameters, (SELECT get_property_directory_custom(cs_process_property.directory_id) AS get_property_directory_custom) AS directorycustom, cs_chrono_value.mime_type, cs_chrono_value.value FROM cs_chrono_property, cs_sign, cs_property_type, cs_chrono_value, cs_process_property WHERE ((((cs_process_property.id = cs_chrono_property.property_id) AND (cs_sign.id = cs_process_property.sign_id)) AND (cs_property_type.id = cs_process_property.type_id)) AND (cs_chrono_property.value_id = cs_chrono_value.id));


ALTER TABLE public.chrono_process_instance_properties_list OWNER TO postgres;

--
-- TOC entry 1675 (class 1259 OID 81963)
-- Dependencies: 5
-- Name: cs_chrono; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_chrono (
    id bigint NOT NULL,
    chrono_at timestamp without time zone,
    account_id integer,
    from_action_id bigint,
    to_action_id bigint,
    instance_id bigint,
    status_id integer
);


ALTER TABLE public.cs_chrono OWNER TO postgres;

--
-- TOC entry 1676 (class 1259 OID 81965)
-- Dependencies: 1964 5
-- Name: chrono_process_instances_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW chrono_process_instances_list AS
    SELECT cs_chrono.id, cs_chrono.chrono_at, cs_chrono.account_id, cs_chrono.from_action_id, cs_chrono.to_action_id, cs_chrono.instance_id, cs_account.name AS accountname, from_action.name AS fromactionname, to_action.name AS toactionname, cs_status.name AS statusname, process_instances_list.process_id, process_instances_list.name AS processname, process_instances_list.description AS processdescr FROM (((((cs_chrono LEFT JOIN cs_status ON ((cs_chrono.status_id = cs_status.id))) LEFT JOIN cs_account ON ((cs_chrono.account_id = cs_account.id))) LEFT JOIN process_instances_list ON ((cs_chrono.instance_id = process_instances_list.id))) LEFT JOIN process_instance_actions_list from_action ON ((cs_chrono.from_action_id = from_action.id))) LEFT JOIN process_instance_actions_list to_action ON ((cs_chrono.to_action_id = to_action.id)));


ALTER TABLE public.chrono_process_instances_list OWNER TO postgres;

--
-- TOC entry 1677 (class 1259 OID 81969)
-- Dependencies: 2317 2318 5
-- Name: cs_contact; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_contact (
    id integer NOT NULL,
    name character varying(150),
    description text,
    owner_id integer,
    is_public boolean DEFAULT false,
    is_deleted boolean DEFAULT false
);


ALTER TABLE public.cs_contact OWNER TO postgres;

--
-- TOC entry 1678 (class 1259 OID 81976)
-- Dependencies: 2320 5
-- Name: cs_contact_list; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_contact_list (
    id bigint NOT NULL,
    contact_id integer,
    account_id integer,
    is_deleted boolean DEFAULT false
);


ALTER TABLE public.cs_contact_list OWNER TO postgres;

--
-- TOC entry 1679 (class 1259 OID 81979)
-- Dependencies: 1965 5
-- Name: contact_accounts_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW contact_accounts_list AS
    SELECT cs_contact_list.id, cs_contact_list.contact_id, cs_contact_list.account_id, cs_contact_list.is_deleted, cs_contact.name AS contactname, cs_contact.description AS contactdescr, cs_contact.is_public, cs_contact.is_deleted AS contact_is_deleted, cs_account.name AS accountname, cs_account.description AS accountdescr FROM cs_contact_list, cs_contact, cs_account WHERE ((cs_contact_list.contact_id = cs_contact.id) AND (cs_contact_list.account_id = cs_account.id));


ALTER TABLE public.contact_accounts_list OWNER TO postgres;

--
-- TOC entry 1680 (class 1259 OID 81982)
-- Dependencies: 5
-- Name: cs_contact_permission; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_contact_permission (
    id integer NOT NULL,
    contact_id integer,
    account_id integer,
    permission_id integer
);


ALTER TABLE public.cs_contact_permission OWNER TO postgres;

--
-- TOC entry 1681 (class 1259 OID 81984)
-- Dependencies: 1966 5
-- Name: contact_permissions_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW contact_permissions_list AS
    SELECT cs_contact_permission.id, cs_contact_permission.contact_id, cs_contact_permission.account_id, cs_contact_permission.permission_id, cs_contact.name AS contactname, cs_contact.description AS contactdescr, cs_contact.is_public, cs_account.name AS accountname, cs_account.description AS accountdescr, cs_object_permission.name AS permissionname, cs_object_permission.description AS permissiondescr FROM cs_contact_permission, cs_contact, cs_account, cs_object_permission WHERE (((cs_contact_permission.contact_id = cs_contact.id) AND (cs_contact_permission.account_id = cs_account.id)) AND (cs_contact_permission.permission_id = cs_object_permission.id));


ALTER TABLE public.contact_permissions_list OWNER TO postgres;

--
-- TOC entry 1682 (class 1259 OID 81987)
-- Dependencies: 1967 5
-- Name: contacts_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW contacts_list AS
    SELECT cs_contact.id, cs_contact.name, cs_contact.description, cs_contact.owner_id, cs_contact.is_public, cs_contact.is_deleted, cs_account.name AS ownername, cs_account.description AS ownerdescr, (SELECT count(cs_contact_list.id) AS count FROM cs_contact_list WHERE (cs_contact_list.contact_id = cs_contact.id)) AS realcontactcount, (SELECT count(cs_contact_list.id) AS count FROM cs_contact_list WHERE ((cs_contact_list.contact_id = cs_contact.id) AND (cs_contact_list.is_deleted = false))) AS actualcontactcount FROM cs_contact, cs_account WHERE (cs_contact.owner_id = cs_account.id);


ALTER TABLE public.contacts_list OWNER TO postgres;

--
-- TOC entry 1683 (class 1259 OID 81990)
-- Dependencies: 5
-- Name: cs_account_division; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_account_division (
    id integer NOT NULL,
    account_id integer,
    division_id integer
);


ALTER TABLE public.cs_account_division OWNER TO postgres;

--
-- TOC entry 1684 (class 1259 OID 81992)
-- Dependencies: 1683 5
-- Name: cs_account_division_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_account_division_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_account_division_id_seq OWNER TO postgres;

--
-- TOC entry 3019 (class 0 OID 0)
-- Dependencies: 1684
-- Name: cs_account_division_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_account_division_id_seq OWNED BY cs_account_division.id;


--
-- TOC entry 1685 (class 1259 OID 81994)
-- Dependencies: 1621 5
-- Name: cs_account_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_account_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_account_id_seq OWNER TO postgres;

--
-- TOC entry 3020 (class 0 OID 0)
-- Dependencies: 1685
-- Name: cs_account_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_account_id_seq OWNED BY cs_account.id;


--
-- TOC entry 1686 (class 1259 OID 81996)
-- Dependencies: 1626 5
-- Name: cs_account_post_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_account_post_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_account_post_id_seq OWNER TO postgres;

--
-- TOC entry 3021 (class 0 OID 0)
-- Dependencies: 1686
-- Name: cs_account_post_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_account_post_id_seq OWNED BY cs_account_post.id;


--
-- TOC entry 1687 (class 1259 OID 81998)
-- Dependencies: 1630 5
-- Name: cs_account_today_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_account_today_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_account_today_id_seq OWNER TO postgres;

--
-- TOC entry 3022 (class 0 OID 0)
-- Dependencies: 1687
-- Name: cs_account_today_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_account_today_id_seq OWNED BY cs_account_today.id;


--
-- TOC entry 1688 (class 1259 OID 82000)
-- Dependencies: 1631 5
-- Name: cs_action_type_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_action_type_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_action_type_id_seq OWNER TO postgres;

--
-- TOC entry 3023 (class 0 OID 0)
-- Dependencies: 1688
-- Name: cs_action_type_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_action_type_id_seq OWNED BY cs_action_type.id;


--
-- TOC entry 1689 (class 1259 OID 82002)
-- Dependencies: 5
-- Name: cs_blob; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_blob (
    id bigint NOT NULL,
    value_id bigint,
    blob text
);


ALTER TABLE public.cs_blob OWNER TO postgres;

--
-- TOC entry 1690 (class 1259 OID 82007)
-- Dependencies: 1689 5
-- Name: cs_blob_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_blob_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_blob_id_seq OWNER TO postgres;

--
-- TOC entry 3024 (class 0 OID 0)
-- Dependencies: 1690
-- Name: cs_blob_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_blob_id_seq OWNED BY cs_blob.id;


--
-- TOC entry 1691 (class 1259 OID 82009)
-- Dependencies: 1661 5
-- Name: cs_calendar_event_alarm_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_calendar_event_alarm_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_calendar_event_alarm_id_seq OWNER TO postgres;

--
-- TOC entry 3025 (class 0 OID 0)
-- Dependencies: 1691
-- Name: cs_calendar_event_alarm_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_calendar_event_alarm_id_seq OWNED BY cs_calendar_event_alarm.id;


--
-- TOC entry 1692 (class 1259 OID 82011)
-- Dependencies: 5
-- Name: cs_calendar_event_blob; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_calendar_event_blob (
    id bigint NOT NULL,
    event_id bigint,
    name character varying(250),
    blob text
);


ALTER TABLE public.cs_calendar_event_blob OWNER TO postgres;

--
-- TOC entry 1693 (class 1259 OID 82016)
-- Dependencies: 1692 5
-- Name: cs_calendar_event_blob_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_calendar_event_blob_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_calendar_event_blob_id_seq OWNER TO postgres;

--
-- TOC entry 3026 (class 0 OID 0)
-- Dependencies: 1693
-- Name: cs_calendar_event_blob_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_calendar_event_blob_id_seq OWNED BY cs_calendar_event_blob.id;


--
-- TOC entry 1694 (class 1259 OID 82018)
-- Dependencies: 1649 5
-- Name: cs_calendar_event_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_calendar_event_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_calendar_event_id_seq OWNER TO postgres;

--
-- TOC entry 3027 (class 0 OID 0)
-- Dependencies: 1694
-- Name: cs_calendar_event_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_calendar_event_id_seq OWNED BY cs_calendar_event.id;


--
-- TOC entry 1695 (class 1259 OID 82020)
-- Dependencies: 1650 5
-- Name: cs_calendar_event_period_detail_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_calendar_event_period_detail_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_calendar_event_period_detail_id_seq OWNER TO postgres;

--
-- TOC entry 3028 (class 0 OID 0)
-- Dependencies: 1695
-- Name: cs_calendar_event_period_detail_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_calendar_event_period_detail_id_seq OWNED BY cs_calendar_event_period_detail.id;


--
-- TOC entry 1696 (class 1259 OID 82022)
-- Dependencies: 1654 5
-- Name: cs_calendar_event_period_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_calendar_event_period_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_calendar_event_period_id_seq OWNER TO postgres;

--
-- TOC entry 3029 (class 0 OID 0)
-- Dependencies: 1696
-- Name: cs_calendar_event_period_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_calendar_event_period_id_seq OWNED BY cs_calendar_event_period.id;


--
-- TOC entry 1697 (class 1259 OID 82024)
-- Dependencies: 1658 5
-- Name: cs_calendar_event_reciever_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_calendar_event_reciever_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_calendar_event_reciever_id_seq OWNER TO postgres;

--
-- TOC entry 3030 (class 0 OID 0)
-- Dependencies: 1697
-- Name: cs_calendar_event_reciever_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_calendar_event_reciever_id_seq OWNED BY cs_calendar_event_reciever.id;


--
-- TOC entry 1698 (class 1259 OID 82026)
-- Dependencies: 1648 5
-- Name: cs_calendar_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_calendar_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_calendar_id_seq OWNER TO postgres;

--
-- TOC entry 3031 (class 0 OID 0)
-- Dependencies: 1698
-- Name: cs_calendar_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_calendar_id_seq OWNED BY cs_calendar.id;


--
-- TOC entry 1699 (class 1259 OID 82028)
-- Dependencies: 1665 5
-- Name: cs_calendar_permission_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_calendar_permission_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_calendar_permission_id_seq OWNER TO postgres;

--
-- TOC entry 3032 (class 0 OID 0)
-- Dependencies: 1699
-- Name: cs_calendar_permission_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_calendar_permission_id_seq OWNED BY cs_calendar_permission.id;


--
-- TOC entry 1700 (class 1259 OID 82030)
-- Dependencies: 1622 5
-- Name: cs_cellop_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_cellop_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_cellop_id_seq OWNER TO postgres;

--
-- TOC entry 3033 (class 0 OID 0)
-- Dependencies: 1700
-- Name: cs_cellop_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_cellop_id_seq OWNED BY cs_cellop.id;


--
-- TOC entry 1701 (class 1259 OID 82032)
-- Dependencies: 1667 5
-- Name: cs_chrono_action_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_chrono_action_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_chrono_action_id_seq OWNER TO postgres;

--
-- TOC entry 3034 (class 0 OID 0)
-- Dependencies: 1701
-- Name: cs_chrono_action_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_chrono_action_id_seq OWNED BY cs_chrono_action.id;


--
-- TOC entry 1702 (class 1259 OID 82034)
-- Dependencies: 5
-- Name: cs_chrono_blob; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_chrono_blob (
    id bigint NOT NULL,
    blob_id bigint,
    blob_value_id bigint,
    value_id bigint,
    blob text
);


ALTER TABLE public.cs_chrono_blob OWNER TO postgres;

--
-- TOC entry 1703 (class 1259 OID 82039)
-- Dependencies: 1702 5
-- Name: cs_chrono_blob_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_chrono_blob_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_chrono_blob_id_seq OWNER TO postgres;

--
-- TOC entry 3035 (class 0 OID 0)
-- Dependencies: 1703
-- Name: cs_chrono_blob_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_chrono_blob_id_seq OWNED BY cs_chrono_blob.id;


--
-- TOC entry 1704 (class 1259 OID 82041)
-- Dependencies: 1675 5
-- Name: cs_chrono_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_chrono_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_chrono_id_seq OWNER TO postgres;

--
-- TOC entry 3036 (class 0 OID 0)
-- Dependencies: 1704
-- Name: cs_chrono_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_chrono_id_seq OWNED BY cs_chrono.id;


--
-- TOC entry 1705 (class 1259 OID 82043)
-- Dependencies: 1669 5
-- Name: cs_chrono_property_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_chrono_property_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_chrono_property_id_seq OWNER TO postgres;

--
-- TOC entry 3037 (class 0 OID 0)
-- Dependencies: 1705
-- Name: cs_chrono_property_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_chrono_property_id_seq OWNED BY cs_chrono_property.id;


--
-- TOC entry 1706 (class 1259 OID 82045)
-- Dependencies: 1670 5
-- Name: cs_chrono_value_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_chrono_value_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_chrono_value_id_seq OWNER TO postgres;

--
-- TOC entry 3038 (class 0 OID 0)
-- Dependencies: 1706
-- Name: cs_chrono_value_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_chrono_value_id_seq OWNED BY cs_chrono_value.id;


--
-- TOC entry 1707 (class 1259 OID 82047)
-- Dependencies: 2327 5
-- Name: cs_constants; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_constants (
    id integer NOT NULL,
    name character varying(150),
    description text,
    value text,
    fixed_name boolean DEFAULT true
);


ALTER TABLE public.cs_constants OWNER TO postgres;

--
-- TOC entry 1708 (class 1259 OID 82053)
-- Dependencies: 1707 5
-- Name: cs_constants_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_constants_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_constants_id_seq OWNER TO postgres;

--
-- TOC entry 3039 (class 0 OID 0)
-- Dependencies: 1708
-- Name: cs_constants_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_constants_id_seq OWNED BY cs_constants.id;


--
-- TOC entry 1709 (class 1259 OID 82055)
-- Dependencies: 1677 5
-- Name: cs_contact_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_contact_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_contact_id_seq OWNER TO postgres;

--
-- TOC entry 3040 (class 0 OID 0)
-- Dependencies: 1709
-- Name: cs_contact_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_contact_id_seq OWNED BY cs_contact.id;


--
-- TOC entry 1710 (class 1259 OID 82057)
-- Dependencies: 1678 5
-- Name: cs_contact_list_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_contact_list_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_contact_list_id_seq OWNER TO postgres;

--
-- TOC entry 3041 (class 0 OID 0)
-- Dependencies: 1710
-- Name: cs_contact_list_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_contact_list_id_seq OWNED BY cs_contact_list.id;


--
-- TOC entry 1711 (class 1259 OID 82059)
-- Dependencies: 1680 5
-- Name: cs_contact_permission_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_contact_permission_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_contact_permission_id_seq OWNER TO postgres;

--
-- TOC entry 3042 (class 0 OID 0)
-- Dependencies: 1711
-- Name: cs_contact_permission_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_contact_permission_id_seq OWNED BY cs_contact_permission.id;


--
-- TOC entry 1712 (class 1259 OID 82061)
-- Dependencies: 5
-- Name: cs_custom_setting; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_custom_setting (
    id integer NOT NULL,
    account_id integer,
    module_id integer,
    setup text
);


ALTER TABLE public.cs_custom_setting OWNER TO postgres;

--
-- TOC entry 1713 (class 1259 OID 82066)
-- Dependencies: 1712 5
-- Name: cs_custom_setting_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_custom_setting_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_custom_setting_id_seq OWNER TO postgres;

--
-- TOC entry 3043 (class 0 OID 0)
-- Dependencies: 1713
-- Name: cs_custom_setting_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_custom_setting_id_seq OWNED BY cs_custom_setting.id;


--
-- TOC entry 1714 (class 1259 OID 82068)
-- Dependencies: 2330 5
-- Name: cs_delegate; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_delegate (
    id bigint NOT NULL,
    account_id integer,
    delegate_id integer,
    started_at timestamp without time zone,
    ended_at timestamp without time zone,
    is_active boolean DEFAULT false
);


ALTER TABLE public.cs_delegate OWNER TO postgres;

--
-- TOC entry 1715 (class 1259 OID 82071)
-- Dependencies: 1714 5
-- Name: cs_delegate_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_delegate_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_delegate_id_seq OWNER TO postgres;

--
-- TOC entry 3044 (class 0 OID 0)
-- Dependencies: 1715
-- Name: cs_delegate_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_delegate_id_seq OWNED BY cs_delegate.id;


--
-- TOC entry 1716 (class 1259 OID 82073)
-- Dependencies: 2332 2333 5
-- Name: cs_directory; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_directory (
    id integer NOT NULL,
    name character varying(150),
    description text,
    tablename character varying(150),
    readonly boolean DEFAULT true,
    parameters character varying(150),
    custom boolean DEFAULT false
);


ALTER TABLE public.cs_directory OWNER TO postgres;

--
-- TOC entry 1717 (class 1259 OID 82080)
-- Dependencies: 5
-- Name: cs_directory_blob; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_directory_blob (
    id bigint NOT NULL,
    value_id bigint,
    blob text
);


ALTER TABLE public.cs_directory_blob OWNER TO postgres;

--
-- TOC entry 1718 (class 1259 OID 82085)
-- Dependencies: 1717 5
-- Name: cs_directory_blob_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_directory_blob_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_directory_blob_id_seq OWNER TO postgres;

--
-- TOC entry 3045 (class 0 OID 0)
-- Dependencies: 1718
-- Name: cs_directory_blob_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_directory_blob_id_seq OWNED BY cs_directory_blob.id;


--
-- TOC entry 1719 (class 1259 OID 82087)
-- Dependencies: 2336 2337 5
-- Name: cs_directory_field; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_directory_field (
    id integer NOT NULL,
    directory_id integer,
    name character varying(150),
    caption character varying(150),
    type_id integer DEFAULT 1,
    default_value text,
    autoinc boolean DEFAULT false
);


ALTER TABLE public.cs_directory_field OWNER TO postgres;

--
-- TOC entry 1720 (class 1259 OID 82094)
-- Dependencies: 1719 5
-- Name: cs_directory_field_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_directory_field_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_directory_field_id_seq OWNER TO postgres;

--
-- TOC entry 3046 (class 0 OID 0)
-- Dependencies: 1720
-- Name: cs_directory_field_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_directory_field_id_seq OWNED BY cs_directory_field.id;


--
-- TOC entry 1721 (class 1259 OID 82096)
-- Dependencies: 1716 5
-- Name: cs_directory_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_directory_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_directory_id_seq OWNER TO postgres;

--
-- TOC entry 3047 (class 0 OID 0)
-- Dependencies: 1721
-- Name: cs_directory_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_directory_id_seq OWNED BY cs_directory.id;


--
-- TOC entry 1722 (class 1259 OID 82098)
-- Dependencies: 5
-- Name: cs_directory_record; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_directory_record (
    id bigint NOT NULL,
    directory_id integer
);


ALTER TABLE public.cs_directory_record OWNER TO postgres;

--
-- TOC entry 1723 (class 1259 OID 82100)
-- Dependencies: 1722 5
-- Name: cs_directory_record_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_directory_record_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_directory_record_id_seq OWNER TO postgres;

--
-- TOC entry 3048 (class 0 OID 0)
-- Dependencies: 1723
-- Name: cs_directory_record_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_directory_record_id_seq OWNED BY cs_directory_record.id;


--
-- TOC entry 1724 (class 1259 OID 82102)
-- Dependencies: 5
-- Name: cs_directory_value; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_directory_value (
    id bigint NOT NULL,
    field_id integer,
    record_id bigint,
    mime_type character varying(150),
    value text
);


ALTER TABLE public.cs_directory_value OWNER TO postgres;

--
-- TOC entry 1725 (class 1259 OID 82107)
-- Dependencies: 1724 5
-- Name: cs_directory_value_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_directory_value_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_directory_value_id_seq OWNER TO postgres;

--
-- TOC entry 3049 (class 0 OID 0)
-- Dependencies: 1725
-- Name: cs_directory_value_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_directory_value_id_seq OWNED BY cs_directory_value.id;


--
-- TOC entry 1726 (class 1259 OID 82109)
-- Dependencies: 1623 5
-- Name: cs_division_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_division_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_division_id_seq OWNER TO postgres;

--
-- TOC entry 3050 (class 0 OID 0)
-- Dependencies: 1726
-- Name: cs_division_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_division_id_seq OWNED BY cs_division.id;


--
-- TOC entry 1727 (class 1259 OID 82111)
-- Dependencies: 5
-- Name: cs_event; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_event (
    id integer NOT NULL,
    name character varying(150),
    description text
);


ALTER TABLE public.cs_event OWNER TO postgres;

--
-- TOC entry 1728 (class 1259 OID 82116)
-- Dependencies: 1727 5
-- Name: cs_event_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_event_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_event_id_seq OWNER TO postgres;

--
-- TOC entry 3051 (class 0 OID 0)
-- Dependencies: 1728
-- Name: cs_event_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_event_id_seq OWNED BY cs_event.id;


--
-- TOC entry 1729 (class 1259 OID 82118)
-- Dependencies: 1656 5
-- Name: cs_event_period_condition_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_event_period_condition_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_event_period_condition_id_seq OWNER TO postgres;

--
-- TOC entry 3052 (class 0 OID 0)
-- Dependencies: 1729
-- Name: cs_event_period_condition_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_event_period_condition_id_seq OWNED BY cs_event_period_condition.id;


--
-- TOC entry 1730 (class 1259 OID 82120)
-- Dependencies: 1655 5
-- Name: cs_event_period_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_event_period_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_event_period_id_seq OWNER TO postgres;

--
-- TOC entry 3053 (class 0 OID 0)
-- Dependencies: 1730
-- Name: cs_event_period_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_event_period_id_seq OWNED BY cs_event_period.id;


--
-- TOC entry 1731 (class 1259 OID 82122)
-- Dependencies: 1651 5
-- Name: cs_event_priority_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_event_priority_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_event_priority_id_seq OWNER TO postgres;

--
-- TOC entry 3054 (class 0 OID 0)
-- Dependencies: 1731
-- Name: cs_event_priority_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_event_priority_id_seq OWNED BY cs_event_priority.id;


--
-- TOC entry 1732 (class 1259 OID 82124)
-- Dependencies: 1652 5
-- Name: cs_event_status_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_event_status_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_event_status_id_seq OWNER TO postgres;

--
-- TOC entry 3055 (class 0 OID 0)
-- Dependencies: 1732
-- Name: cs_event_status_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_event_status_id_seq OWNED BY cs_event_status.id;


--
-- TOC entry 1733 (class 1259 OID 82126)
-- Dependencies: 2342 2343 5
-- Name: cs_file; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_file (
    id bigint NOT NULL,
    parent_id bigint,
    name character varying(250),
    description text,
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    owner_id integer,
    updated_by integer,
    is_folder boolean DEFAULT false,
    is_deleted boolean DEFAULT false,
    mime character varying(150)
);


ALTER TABLE public.cs_file OWNER TO postgres;

--
-- TOC entry 1734 (class 1259 OID 82133)
-- Dependencies: 5
-- Name: cs_file_blob; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_file_blob (
    id bigint NOT NULL,
    file_id bigint,
    blob text
);


ALTER TABLE public.cs_file_blob OWNER TO postgres;

--
-- TOC entry 1735 (class 1259 OID 82138)
-- Dependencies: 1734 5
-- Name: cs_file_blob_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_file_blob_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_file_blob_id_seq OWNER TO postgres;

--
-- TOC entry 3056 (class 0 OID 0)
-- Dependencies: 1735
-- Name: cs_file_blob_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_file_blob_id_seq OWNED BY cs_file_blob.id;


--
-- TOC entry 1736 (class 1259 OID 82140)
-- Dependencies: 1733 5
-- Name: cs_file_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_file_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_file_id_seq OWNER TO postgres;

--
-- TOC entry 3057 (class 0 OID 0)
-- Dependencies: 1736
-- Name: cs_file_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_file_id_seq OWNED BY cs_file.id;


--
-- TOC entry 1737 (class 1259 OID 82142)
-- Dependencies: 5
-- Name: cs_file_permission; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_file_permission (
    id bigint NOT NULL,
    file_id bigint,
    account_id integer,
    permission_id integer
);


ALTER TABLE public.cs_file_permission OWNER TO postgres;

--
-- TOC entry 1738 (class 1259 OID 82144)
-- Dependencies: 1737 5
-- Name: cs_file_permission_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_file_permission_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_file_permission_id_seq OWNER TO postgres;

--
-- TOC entry 3058 (class 0 OID 0)
-- Dependencies: 1738
-- Name: cs_file_permission_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_file_permission_id_seq OWNED BY cs_file_permission.id;


--
-- TOC entry 1739 (class 1259 OID 82146)
-- Dependencies: 2347 5
-- Name: cs_message; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_message (
    id bigint NOT NULL,
    author_id integer,
    status_id integer,
    subject character varying(250),
    message text,
    deliver_report boolean,
    read_report boolean,
    is_deleted boolean,
    created_at timestamp without time zone,
    sended_at timestamp without time zone,
    is_erased boolean DEFAULT false
);


ALTER TABLE public.cs_message OWNER TO postgres;

--
-- TOC entry 1740 (class 1259 OID 82152)
-- Dependencies: 5
-- Name: cs_message_blob; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_message_blob (
    id bigint NOT NULL,
    message_id bigint,
    name character varying(250),
    blob text
);


ALTER TABLE public.cs_message_blob OWNER TO postgres;

--
-- TOC entry 1741 (class 1259 OID 82157)
-- Dependencies: 1740 5
-- Name: cs_message_blob_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_message_blob_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_message_blob_id_seq OWNER TO postgres;

--
-- TOC entry 3059 (class 0 OID 0)
-- Dependencies: 1741
-- Name: cs_message_blob_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_message_blob_id_seq OWNED BY cs_message_blob.id;


--
-- TOC entry 1742 (class 1259 OID 82159)
-- Dependencies: 1739 5
-- Name: cs_message_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_message_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_message_id_seq OWNER TO postgres;

--
-- TOC entry 3060 (class 0 OID 0)
-- Dependencies: 1742
-- Name: cs_message_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_message_id_seq OWNED BY cs_message.id;


--
-- TOC entry 1743 (class 1259 OID 82161)
-- Dependencies: 2350 2351 5
-- Name: cs_message_reciever; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_message_reciever (
    id bigint NOT NULL,
    message_id bigint,
    reciever_id integer,
    status_id integer,
    is_deleted boolean DEFAULT false,
    recieved_at timestamp without time zone,
    is_erased boolean DEFAULT false
);


ALTER TABLE public.cs_message_reciever OWNER TO postgres;

--
-- TOC entry 1744 (class 1259 OID 82165)
-- Dependencies: 1743 5
-- Name: cs_message_reciever_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_message_reciever_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_message_reciever_id_seq OWNER TO postgres;

--
-- TOC entry 3061 (class 0 OID 0)
-- Dependencies: 1744
-- Name: cs_message_reciever_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_message_reciever_id_seq OWNED BY cs_message_reciever.id;


--
-- TOC entry 1745 (class 1259 OID 82167)
-- Dependencies: 5
-- Name: cs_message_status; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_message_status (
    id integer NOT NULL,
    name character varying(150),
    description text
);


ALTER TABLE public.cs_message_status OWNER TO postgres;

--
-- TOC entry 1746 (class 1259 OID 82172)
-- Dependencies: 1745 5
-- Name: cs_message_status_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_message_status_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_message_status_id_seq OWNER TO postgres;

--
-- TOC entry 3062 (class 0 OID 0)
-- Dependencies: 1746
-- Name: cs_message_status_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_message_status_id_seq OWNED BY cs_message_status.id;


--
-- TOC entry 1747 (class 1259 OID 82174)
-- Dependencies: 2354 5
-- Name: cs_mime; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_mime (
    id integer NOT NULL,
    name character varying(100),
    ext character varying(100),
    is_active boolean DEFAULT true
);


ALTER TABLE public.cs_mime OWNER TO postgres;

--
-- TOC entry 1748 (class 1259 OID 82177)
-- Dependencies: 1747 5
-- Name: cs_mime_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_mime_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_mime_id_seq OWNER TO postgres;

--
-- TOC entry 3063 (class 0 OID 0)
-- Dependencies: 1748
-- Name: cs_mime_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_mime_id_seq OWNED BY cs_mime.id;


--
-- TOC entry 1749 (class 1259 OID 82179)
-- Dependencies: 2356 5
-- Name: cs_module; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_module (
    id integer NOT NULL,
    parent_id integer,
    name character varying(150),
    description text,
    caption character varying(150),
    is_hidden boolean DEFAULT false
);


ALTER TABLE public.cs_module OWNER TO postgres;

--
-- TOC entry 1750 (class 1259 OID 82185)
-- Dependencies: 1749 5
-- Name: cs_module_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_module_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_module_id_seq OWNER TO postgres;

--
-- TOC entry 3064 (class 0 OID 0)
-- Dependencies: 1750
-- Name: cs_module_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_module_id_seq OWNED BY cs_module.id;


--
-- TOC entry 1751 (class 1259 OID 82187)
-- Dependencies: 1659 5
-- Name: cs_object_permission_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_object_permission_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_object_permission_id_seq OWNER TO postgres;

--
-- TOC entry 3065 (class 0 OID 0)
-- Dependencies: 1751
-- Name: cs_object_permission_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_object_permission_id_seq OWNED BY cs_object_permission.id;


--
-- TOC entry 1752 (class 1259 OID 82189)
-- Dependencies: 1624 5
-- Name: cs_permission_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_permission_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_permission_id_seq OWNER TO postgres;

--
-- TOC entry 3066 (class 0 OID 0)
-- Dependencies: 1752
-- Name: cs_permission_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_permission_id_seq OWNED BY cs_permission.id;


--
-- TOC entry 1753 (class 1259 OID 82191)
-- Dependencies: 2358 2359 5
-- Name: cs_permission_list; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_permission_list (
    id integer NOT NULL,
    permission_id integer,
    module_id integer,
    can_read boolean,
    can_write boolean,
    can_delete boolean,
    can_admin boolean,
    can_review boolean DEFAULT false,
    can_observe boolean DEFAULT false
);


ALTER TABLE public.cs_permission_list OWNER TO postgres;

--
-- TOC entry 1754 (class 1259 OID 82195)
-- Dependencies: 1753 5
-- Name: cs_permission_list_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_permission_list_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_permission_list_id_seq OWNER TO postgres;

--
-- TOC entry 3067 (class 0 OID 0)
-- Dependencies: 1754
-- Name: cs_permission_list_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_permission_list_id_seq OWNED BY cs_permission_list.id;


--
-- TOC entry 1755 (class 1259 OID 82197)
-- Dependencies: 1627 5
-- Name: cs_post_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_post_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_post_id_seq OWNER TO postgres;

--
-- TOC entry 3068 (class 0 OID 0)
-- Dependencies: 1755
-- Name: cs_post_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_post_id_seq OWNED BY cs_post.id;


--
-- TOC entry 1756 (class 1259 OID 82199)
-- Dependencies: 5
-- Name: cs_post_relation; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_post_relation (
    id integer NOT NULL,
    post_id integer,
    relation_post_id integer,
    division_id integer
);


ALTER TABLE public.cs_post_relation OWNER TO postgres;

--
-- TOC entry 1757 (class 1259 OID 82201)
-- Dependencies: 1756 5
-- Name: cs_post_relation_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_post_relation_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_post_relation_id_seq OWNER TO postgres;

--
-- TOC entry 3069 (class 0 OID 0)
-- Dependencies: 1757
-- Name: cs_post_relation_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_post_relation_id_seq OWNED BY cs_post_relation.id;


--
-- TOC entry 1758 (class 1259 OID 82203)
-- Dependencies: 5
-- Name: cs_process_action_child; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_process_action_child (
    id bigint NOT NULL,
    action_id bigint,
    process_id bigint
);


ALTER TABLE public.cs_process_action_child OWNER TO postgres;

--
-- TOC entry 1759 (class 1259 OID 82205)
-- Dependencies: 1758 5
-- Name: cs_process_action_child_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_process_action_child_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_process_action_child_id_seq OWNER TO postgres;

--
-- TOC entry 3070 (class 0 OID 0)
-- Dependencies: 1759
-- Name: cs_process_action_child_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_process_action_child_id_seq OWNED BY cs_process_action_child.id;


--
-- TOC entry 1760 (class 1259 OID 82207)
-- Dependencies: 1633 5
-- Name: cs_process_action_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_process_action_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_process_action_id_seq OWNER TO postgres;

--
-- TOC entry 3071 (class 0 OID 0)
-- Dependencies: 1760
-- Name: cs_process_action_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_process_action_id_seq OWNED BY cs_process_action.id;


--
-- TOC entry 1761 (class 1259 OID 82209)
-- Dependencies: 2363 2364 2365 2366 2367 2368 2369 2370 2371 5
-- Name: cs_process_action_property; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_process_action_property (
    id bigint NOT NULL,
    action_id integer,
    property_id integer,
    npp smallint DEFAULT 0,
    is_required boolean DEFAULT false,
    is_readonly boolean DEFAULT false,
    is_hidden boolean DEFAULT false,
    is_nextuser boolean DEFAULT false,
    is_childprocess boolean DEFAULT false,
    is_combo boolean DEFAULT true,
    is_active boolean DEFAULT false,
    parameters character varying(150),
    is_multiple boolean DEFAULT false,
    condition text
);


ALTER TABLE public.cs_process_action_property OWNER TO postgres;

--
-- TOC entry 1762 (class 1259 OID 82223)
-- Dependencies: 1761 5
-- Name: cs_process_action_property_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_process_action_property_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_process_action_property_id_seq OWNER TO postgres;

--
-- TOC entry 3072 (class 0 OID 0)
-- Dependencies: 1762
-- Name: cs_process_action_property_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_process_action_property_id_seq OWNED BY cs_process_action_property.id;


--
-- TOC entry 1763 (class 1259 OID 82225)
-- Dependencies: 5
-- Name: cs_process_action_transport; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_process_action_transport (
    id integer NOT NULL,
    action_id integer,
    transport_id integer,
    subject_template text,
    text_template text,
    recipients_template text,
    event_id integer
);


ALTER TABLE public.cs_process_action_transport OWNER TO postgres;

--
-- TOC entry 1764 (class 1259 OID 82230)
-- Dependencies: 1763 5
-- Name: cs_process_action_transport_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_process_action_transport_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_process_action_transport_id_seq OWNER TO postgres;

--
-- TOC entry 3073 (class 0 OID 0)
-- Dependencies: 1764
-- Name: cs_process_action_transport_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_process_action_transport_id_seq OWNED BY cs_process_action_transport.id;


--
-- TOC entry 1765 (class 1259 OID 82232)
-- Dependencies: 1634 5
-- Name: cs_process_current_action_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_process_current_action_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_process_current_action_id_seq OWNER TO postgres;

--
-- TOC entry 3074 (class 0 OID 0)
-- Dependencies: 1765
-- Name: cs_process_current_action_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_process_current_action_id_seq OWNED BY cs_process_current_action.id;


--
-- TOC entry 1766 (class 1259 OID 82234)
-- Dependencies: 5
-- Name: cs_process_current_action_performer; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_process_current_action_performer (
    id bigint NOT NULL,
    instance_action_id bigint,
    status_id integer,
    initiator_id integer,
    performer_id integer,
    started_at timestamp without time zone,
    ended_at timestamp without time zone
);


ALTER TABLE public.cs_process_current_action_performer OWNER TO postgres;

--
-- TOC entry 1767 (class 1259 OID 82236)
-- Dependencies: 1766 5
-- Name: cs_process_current_action_performer_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_process_current_action_performer_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_process_current_action_performer_id_seq OWNER TO postgres;

--
-- TOC entry 3075 (class 0 OID 0)
-- Dependencies: 1767
-- Name: cs_process_current_action_performer_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_process_current_action_performer_id_seq OWNED BY cs_process_current_action_performer.id;


--
-- TOC entry 1768 (class 1259 OID 82238)
-- Dependencies: 1632 5
-- Name: cs_process_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_process_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_process_id_seq OWNER TO postgres;

--
-- TOC entry 3076 (class 0 OID 0)
-- Dependencies: 1768
-- Name: cs_process_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_process_id_seq OWNED BY cs_process.id;


--
-- TOC entry 1769 (class 1259 OID 82240)
-- Dependencies: 5
-- Name: cs_process_info_property; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_process_info_property (
    id integer NOT NULL,
    process_id integer,
    property_id integer
);


ALTER TABLE public.cs_process_info_property OWNER TO postgres;

--
-- TOC entry 1770 (class 1259 OID 82242)
-- Dependencies: 1769 5
-- Name: cs_process_info_property_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_process_info_property_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_process_info_property_id_seq OWNER TO postgres;

--
-- TOC entry 3077 (class 0 OID 0)
-- Dependencies: 1770
-- Name: cs_process_info_property_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_process_info_property_id_seq OWNED BY cs_process_info_property.id;


--
-- TOC entry 1771 (class 1259 OID 82244)
-- Dependencies: 1635 5
-- Name: cs_process_instance_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_process_instance_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_process_instance_id_seq OWNER TO postgres;

--
-- TOC entry 3078 (class 0 OID 0)
-- Dependencies: 1771
-- Name: cs_process_instance_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_process_instance_id_seq OWNED BY cs_process_instance.id;


--
-- TOC entry 1772 (class 1259 OID 82246)
-- Dependencies: 1671 5
-- Name: cs_process_property_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_process_property_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_process_property_id_seq OWNER TO postgres;

--
-- TOC entry 3079 (class 0 OID 0)
-- Dependencies: 1772
-- Name: cs_process_property_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_process_property_id_seq OWNED BY cs_process_property.id;


--
-- TOC entry 1773 (class 1259 OID 82248)
-- Dependencies: 5
-- Name: cs_process_property_value; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_process_property_value (
    id bigint NOT NULL,
    instance_id bigint,
    property_id integer,
    value_id bigint
);


ALTER TABLE public.cs_process_property_value OWNER TO postgres;

--
-- TOC entry 1774 (class 1259 OID 82250)
-- Dependencies: 1773 5
-- Name: cs_process_property_value_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_process_property_value_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_process_property_value_id_seq OWNER TO postgres;

--
-- TOC entry 3080 (class 0 OID 0)
-- Dependencies: 1774
-- Name: cs_process_property_value_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_process_property_value_id_seq OWNED BY cs_process_property_value.id;


--
-- TOC entry 1775 (class 1259 OID 82252)
-- Dependencies: 5
-- Name: cs_process_role; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_process_role (
    id integer NOT NULL,
    process_id integer,
    role_id integer,
    account_id integer
);


ALTER TABLE public.cs_process_role OWNER TO postgres;

--
-- TOC entry 1776 (class 1259 OID 82254)
-- Dependencies: 1775 5
-- Name: cs_process_role_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_process_role_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_process_role_id_seq OWNER TO postgres;

--
-- TOC entry 3081 (class 0 OID 0)
-- Dependencies: 1776
-- Name: cs_process_role_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_process_role_id_seq OWNED BY cs_process_role.id;


--
-- TOC entry 1777 (class 1259 OID 82256)
-- Dependencies: 5
-- Name: cs_process_transition; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_process_transition (
    id integer NOT NULL,
    process_id integer,
    from_action_id integer,
    to_action_id integer
);


ALTER TABLE public.cs_process_transition OWNER TO postgres;

--
-- TOC entry 1778 (class 1259 OID 82258)
-- Dependencies: 1777 5
-- Name: cs_process_transition_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_process_transition_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_process_transition_id_seq OWNER TO postgres;

--
-- TOC entry 3082 (class 0 OID 0)
-- Dependencies: 1778
-- Name: cs_process_transition_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_process_transition_id_seq OWNED BY cs_process_transition.id;


--
-- TOC entry 1779 (class 1259 OID 82260)
-- Dependencies: 5
-- Name: cs_process_transport; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_process_transport (
    id integer NOT NULL,
    process_id integer,
    transport_id integer,
    subject_template text,
    text_template text,
    recipients_template text,
    event_id integer
);


ALTER TABLE public.cs_process_transport OWNER TO postgres;

--
-- TOC entry 1780 (class 1259 OID 82265)
-- Dependencies: 1779 5
-- Name: cs_process_transport_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_process_transport_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_process_transport_id_seq OWNER TO postgres;

--
-- TOC entry 3083 (class 0 OID 0)
-- Dependencies: 1780
-- Name: cs_process_transport_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_process_transport_id_seq OWNED BY cs_process_transport.id;


--
-- TOC entry 1781 (class 1259 OID 82267)
-- Dependencies: 1636 5
-- Name: cs_project_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_project_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_project_id_seq OWNER TO postgres;

--
-- TOC entry 3084 (class 0 OID 0)
-- Dependencies: 1781
-- Name: cs_project_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_project_id_seq OWNED BY cs_project.id;


--
-- TOC entry 1782 (class 1259 OID 82269)
-- Dependencies: 1637 5
-- Name: cs_project_instance_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_project_instance_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_project_instance_id_seq OWNER TO postgres;

--
-- TOC entry 3085 (class 0 OID 0)
-- Dependencies: 1782
-- Name: cs_project_instance_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_project_instance_id_seq OWNED BY cs_project_instance.id;


--
-- TOC entry 1783 (class 1259 OID 82271)
-- Dependencies: 5
-- Name: cs_project_process; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_project_process (
    id integer NOT NULL,
    project_id integer,
    process_id integer
);


ALTER TABLE public.cs_project_process OWNER TO postgres;

--
-- TOC entry 1784 (class 1259 OID 82273)
-- Dependencies: 1783 5
-- Name: cs_project_process_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_project_process_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_project_process_id_seq OWNER TO postgres;

--
-- TOC entry 3086 (class 0 OID 0)
-- Dependencies: 1784
-- Name: cs_project_process_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_project_process_id_seq OWNED BY cs_project_process.id;


--
-- TOC entry 1785 (class 1259 OID 82275)
-- Dependencies: 1638 5
-- Name: cs_project_process_instance_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_project_process_instance_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_project_process_instance_id_seq OWNER TO postgres;

--
-- TOC entry 3087 (class 0 OID 0)
-- Dependencies: 1785
-- Name: cs_project_process_instance_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_project_process_instance_id_seq OWNED BY cs_project_process_instance.id;


--
-- TOC entry 1786 (class 1259 OID 82277)
-- Dependencies: 2381 2382 5
-- Name: cs_project_property; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_project_property (
    id integer NOT NULL,
    name character varying(150),
    description text,
    project_id integer,
    sign_id integer DEFAULT 1,
    type_id integer DEFAULT 1,
    default_value text
);


ALTER TABLE public.cs_project_property OWNER TO postgres;

--
-- TOC entry 1787 (class 1259 OID 82284)
-- Dependencies: 1786 5
-- Name: cs_project_property_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_project_property_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_project_property_id_seq OWNER TO postgres;

--
-- TOC entry 3088 (class 0 OID 0)
-- Dependencies: 1787
-- Name: cs_project_property_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_project_property_id_seq OWNED BY cs_project_property.id;


--
-- TOC entry 1788 (class 1259 OID 82286)
-- Dependencies: 5
-- Name: cs_project_property_value; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_project_property_value (
    id bigint NOT NULL,
    instance_id bigint,
    property_id integer,
    value_id bigint
);


ALTER TABLE public.cs_project_property_value OWNER TO postgres;

--
-- TOC entry 1789 (class 1259 OID 82288)
-- Dependencies: 1788 5
-- Name: cs_project_property_value_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_project_property_value_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_project_property_value_id_seq OWNER TO postgres;

--
-- TOC entry 3089 (class 0 OID 0)
-- Dependencies: 1789
-- Name: cs_project_property_value_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_project_property_value_id_seq OWNED BY cs_project_property_value.id;


--
-- TOC entry 1790 (class 1259 OID 82290)
-- Dependencies: 5
-- Name: cs_project_role; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_project_role (
    id integer NOT NULL,
    project_id integer,
    role_id integer,
    division_id integer
);


ALTER TABLE public.cs_project_role OWNER TO postgres;

--
-- TOC entry 1791 (class 1259 OID 82292)
-- Dependencies: 1790 5
-- Name: cs_project_role_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_project_role_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_project_role_id_seq OWNER TO postgres;

--
-- TOC entry 3090 (class 0 OID 0)
-- Dependencies: 1791
-- Name: cs_project_role_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_project_role_id_seq OWNED BY cs_project_role.id;


--
-- TOC entry 1792 (class 1259 OID 82294)
-- Dependencies: 1672 5
-- Name: cs_property_type_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_property_type_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_property_type_id_seq OWNER TO postgres;

--
-- TOC entry 3091 (class 0 OID 0)
-- Dependencies: 1792
-- Name: cs_property_type_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_property_type_id_seq OWNED BY cs_property_type.id;


--
-- TOC entry 1793 (class 1259 OID 82296)
-- Dependencies: 5
-- Name: cs_property_value; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_property_value (
    id bigint NOT NULL,
    mime_type character varying(150),
    value text
);


ALTER TABLE public.cs_property_value OWNER TO postgres;

--
-- TOC entry 1794 (class 1259 OID 82301)
-- Dependencies: 1793 5
-- Name: cs_property_value_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_property_value_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_property_value_id_seq OWNER TO postgres;

--
-- TOC entry 3092 (class 0 OID 0)
-- Dependencies: 1794
-- Name: cs_property_value_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_property_value_id_seq OWNED BY cs_property_value.id;


--
-- TOC entry 1795 (class 1259 OID 82303)
-- Dependencies: 5
-- Name: cs_public_blob; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_public_blob (
    id bigint NOT NULL,
    file_id bigint,
    blob text
);


ALTER TABLE public.cs_public_blob OWNER TO postgres;

--
-- TOC entry 1796 (class 1259 OID 82308)
-- Dependencies: 1795 5
-- Name: cs_public_blob_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_public_blob_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_public_blob_id_seq OWNER TO postgres;

--
-- TOC entry 3093 (class 0 OID 0)
-- Dependencies: 1796
-- Name: cs_public_blob_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_public_blob_id_seq OWNED BY cs_public_blob.id;


--
-- TOC entry 1797 (class 1259 OID 82310)
-- Dependencies: 2388 2389 2390 5
-- Name: cs_public_document; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_public_document (
    id bigint NOT NULL,
    parent_id bigint,
    topic_id integer,
    name character varying(250),
    description text,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    is_active boolean DEFAULT false,
    npp integer DEFAULT 0
);


ALTER TABLE public.cs_public_document OWNER TO postgres;

--
-- TOC entry 1798 (class 1259 OID 82318)
-- Dependencies: 1797 5
-- Name: cs_public_document_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_public_document_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_public_document_id_seq OWNER TO postgres;

--
-- TOC entry 3094 (class 0 OID 0)
-- Dependencies: 1798
-- Name: cs_public_document_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_public_document_id_seq OWNED BY cs_public_document.id;


--
-- TOC entry 1799 (class 1259 OID 82320)
-- Dependencies: 5
-- Name: cs_public_file; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_public_file (
    id bigint NOT NULL,
    name character varying(150),
    description text,
    filename character varying(250),
    mime_type character varying(100)
);


ALTER TABLE public.cs_public_file OWNER TO postgres;

--
-- TOC entry 1800 (class 1259 OID 82325)
-- Dependencies: 1799 5
-- Name: cs_public_file_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_public_file_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_public_file_id_seq OWNER TO postgres;

--
-- TOC entry 3095 (class 0 OID 0)
-- Dependencies: 1800
-- Name: cs_public_file_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_public_file_id_seq OWNED BY cs_public_file.id;


--
-- TOC entry 1801 (class 1259 OID 82327)
-- Dependencies: 2393 5
-- Name: cs_public_topic; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_public_topic (
    id integer NOT NULL,
    name character varying(150),
    description text,
    is_active boolean DEFAULT false
);


ALTER TABLE public.cs_public_topic OWNER TO postgres;

--
-- TOC entry 1802 (class 1259 OID 82333)
-- Dependencies: 1801 5
-- Name: cs_public_topic_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_public_topic_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_public_topic_id_seq OWNER TO postgres;

--
-- TOC entry 3096 (class 0 OID 0)
-- Dependencies: 1802
-- Name: cs_public_topic_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_public_topic_id_seq OWNED BY cs_public_topic.id;


--
-- TOC entry 1803 (class 1259 OID 82335)
-- Dependencies: 2395 5
-- Name: cs_responser; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_responser (
    id integer NOT NULL,
    name character varying(150),
    description text,
    account_id integer,
    is_active boolean DEFAULT true
);


ALTER TABLE public.cs_responser OWNER TO postgres;

--
-- TOC entry 1804 (class 1259 OID 82341)
-- Dependencies: 1803 5
-- Name: cs_responser_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_responser_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_responser_id_seq OWNER TO postgres;

--
-- TOC entry 3097 (class 0 OID 0)
-- Dependencies: 1804
-- Name: cs_responser_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_responser_id_seq OWNED BY cs_responser.id;


--
-- TOC entry 1805 (class 1259 OID 82343)
-- Dependencies: 5
-- Name: cs_role; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cs_role (
    id integer NOT NULL,
    name character varying(150),
    description text
);


ALTER TABLE public.cs_role OWNER TO postgres;

--
-- TOC entry 1806 (class 1259 OID 82348)
-- Dependencies: 1805 5
-- Name: cs_role_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_role_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_role_id_seq OWNER TO postgres;

--
-- TOC entry 3098 (class 0 OID 0)
-- Dependencies: 1806
-- Name: cs_role_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_role_id_seq OWNED BY cs_role.id;


--
-- TOC entry 1807 (class 1259 OID 82350)
-- Dependencies: 1673 5
-- Name: cs_sign_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_sign_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_sign_id_seq OWNER TO postgres;

--
-- TOC entry 3099 (class 0 OID 0)
-- Dependencies: 1807
-- Name: cs_sign_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_sign_id_seq OWNED BY cs_sign.id;


--
-- TOC entry 1808 (class 1259 OID 82352)
-- Dependencies: 1639 5
-- Name: cs_status_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_status_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_status_id_seq OWNER TO postgres;

--
-- TOC entry 3100 (class 0 OID 0)
-- Dependencies: 1808
-- Name: cs_status_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_status_id_seq OWNED BY cs_status.id;


--
-- TOC entry 1809 (class 1259 OID 82354)
-- Dependencies: 1662 5
-- Name: cs_transport_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cs_transport_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.cs_transport_id_seq OWNER TO postgres;

--
-- TOC entry 3101 (class 0 OID 0)
-- Dependencies: 1809
-- Name: cs_transport_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cs_transport_id_seq OWNED BY cs_transport.id;


--
-- TOC entry 1810 (class 1259 OID 82356)
-- Dependencies: 1968 5
-- Name: custom_settings; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW custom_settings AS
    SELECT cs_custom_setting.id, cs_custom_setting.account_id, cs_custom_setting.module_id, cs_custom_setting.setup, cs_account.name AS accountname, cs_account.description AS accountdescr, cs_module.name AS modulename, cs_module.description AS moduledescr FROM cs_custom_setting, cs_account, cs_module WHERE ((cs_custom_setting.account_id = cs_account.id) AND (cs_custom_setting.module_id = cs_module.id));


ALTER TABLE public.custom_settings OWNER TO postgres;

--
-- TOC entry 1811 (class 1259 OID 82359)
-- Dependencies: 1969 5
-- Name: delegates_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW delegates_list AS
    SELECT cs_delegate.id, cs_delegate.account_id, cs_delegate.delegate_id, cs_delegate.started_at, cs_delegate.ended_at, cs_delegate.is_active, cs_account.name AS accountname, delegate.name AS delegatename FROM cs_delegate, cs_account, cs_account delegate WHERE ((cs_delegate.account_id = cs_account.id) AND (cs_delegate.delegate_id = delegate.id));


ALTER TABLE public.delegates_list OWNER TO postgres;

--
-- TOC entry 1812 (class 1259 OID 82362)
-- Dependencies: 1970 5
-- Name: file_permissions_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW file_permissions_list AS
    SELECT cs_file_permission.id, cs_file_permission.file_id, cs_file_permission.account_id, cs_file_permission.permission_id, cs_file.name AS filename, cs_file.description AS filedescr, cs_file.mime AS filemime, cs_file.created_at, cs_file.updated_at, cs_file.owner_id, cs_file.updated_by, cs_file.is_folder, cs_file.is_deleted, cs_owner.name AS ownername, cs_owner.description AS ownerdescr, cs_updater.name AS updatername, cs_updater.description AS updaterdescr, cs_account.name AS accountname, cs_account.description AS accountdescr, cs_object_permission.name AS permissionname, cs_object_permission.description AS permissiondescr FROM cs_file_permission, cs_file, cs_account, cs_object_permission, cs_account cs_owner, cs_account cs_updater WHERE (((((cs_file_permission.file_id = cs_file.id) AND (cs_file_permission.account_id = cs_account.id)) AND (cs_file_permission.permission_id = cs_object_permission.id)) AND (cs_file.owner_id = cs_owner.id)) AND (cs_file.updated_by = cs_updater.id));


ALTER TABLE public.file_permissions_list OWNER TO postgres;

--
-- TOC entry 1813 (class 1259 OID 82365)
-- Dependencies: 1971 5
-- Name: files_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW files_list AS
    SELECT cs_file.id, cs_file.parent_id, cs_file.name, cs_file.description, cs_file.mime, cs_file.created_at, cs_file.updated_at, cs_file.owner_id, cs_file.updated_by, cs_file.is_folder, cs_file.is_deleted, cs_owner.name AS ownername, cs_owner.description AS ownerdescr, cs_updater.name AS updatername, cs_updater.description AS updaterdescr, cs_file_blob.id AS blob_id, cs_file_blob.blob FROM cs_file, cs_account cs_owner, cs_account cs_updater, cs_file_blob WHERE (((cs_file.owner_id = cs_owner.id) AND (cs_file.updated_by = cs_updater.id)) AND (cs_file.id = cs_file_blob.file_id));


ALTER TABLE public.files_list OWNER TO postgres;

--
-- TOC entry 1814 (class 1259 OID 82368)
-- Dependencies: 1972 5
-- Name: files_tree; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW files_tree AS
    SELECT child.id, child.parent_id, child.name, child.description, child.mime, child.created_at, child.updated_at, child.owner_id, child.updated_by, child.is_folder, child.is_deleted, parent.name AS parentname, parent.description AS parentdescr, parent.is_folder AS parent_is_folder, parent.is_deleted AS parent_is_deleted, cs_owner.name AS ownername, cs_owner.description AS ownerdescr, cs_updater.name AS updatername, cs_updater.description AS updaterdescr, cs_file_blob.id AS blob_id, cs_file_blob.blob, (SELECT get_level('cs_file'::text, child.id) AS get_level) AS "level" FROM (((((cs_file child JOIN get_full_big_tree('cs_file'::text) pid(pid) ON ((child.id = pid.pid))) LEFT JOIN cs_file parent ON ((child.parent_id = parent.id))) LEFT JOIN cs_account cs_owner ON ((child.owner_id = cs_owner.id))) LEFT JOIN cs_account cs_updater ON ((child.updated_by = cs_updater.id))) LEFT JOIN cs_file_blob ON ((child.id = cs_file_blob.file_id)));


ALTER TABLE public.files_tree OWNER TO postgres;

--
-- TOC entry 1815 (class 1259 OID 82372)
-- Dependencies: 1973 5
-- Name: message_recievers_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW message_recievers_list AS
    SELECT cs_message_reciever.id, cs_message_reciever.message_id, cs_message_reciever.reciever_id, cs_message_reciever.status_id, cs_message_reciever.is_deleted, cs_message_reciever.is_erased, cs_message_reciever.recieved_at, cs_message.author_id, cs_message.subject, cs_message.message, cs_message.deliver_report, cs_message.read_report, cs_message.is_deleted AS message_is_deleted, cs_message.is_erased AS message_is_erased, cs_message.created_at, cs_message.sended_at, cs_author.name AS authorname, cs_author.description AS authordescr, cs_message_status.name AS statusname, cs_message_status.description AS statusdescr, cs_reciever.name AS recievername, cs_reciever.description AS recieverdescr FROM cs_message_reciever, cs_message, cs_account cs_author, cs_account cs_reciever, cs_message_status WHERE ((((cs_message_reciever.message_id = cs_message.id) AND (cs_message.author_id = cs_author.id)) AND (cs_message_reciever.reciever_id = cs_reciever.id)) AND (cs_message_reciever.status_id = cs_message_status.id));


ALTER TABLE public.message_recievers_list OWNER TO postgres;

--
-- TOC entry 1816 (class 1259 OID 82375)
-- Dependencies: 1974 5
-- Name: messages_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW messages_list AS
    SELECT cs_message.id, cs_message.author_id, cs_message.status_id, cs_message.subject, cs_message.message, cs_message.deliver_report, cs_message.read_report, cs_message.is_deleted, cs_message.created_at, cs_message.sended_at, cs_message.is_erased, cs_account.name AS authorname, cs_account.description AS authordescr, cs_message_status.name AS statusname, cs_message_status.description AS statusdescr FROM cs_message, cs_account, cs_message_status WHERE ((cs_message.author_id = cs_account.id) AND (cs_message.status_id = cs_message_status.id));


ALTER TABLE public.messages_list OWNER TO postgres;

--
-- TOC entry 1817 (class 1259 OID 82378)
-- Dependencies: 1975 5
-- Name: modules_tree; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW modules_tree AS
    SELECT child.id, child.parent_id, child.name, child.description, child.caption, child.is_hidden, parent.name AS parentname, parent.caption AS parentcaption, (SELECT get_level('cs_module'::text, child.id) AS get_level) AS "level" FROM ((cs_module child JOIN get_full_tree('cs_module'::text) pid(pid) ON ((child.id = pid.pid))) LEFT JOIN cs_module parent ON ((child.parent_id = parent.id)));


ALTER TABLE public.modules_tree OWNER TO postgres;

--
-- TOC entry 1818 (class 1259 OID 82381)
-- Dependencies: 5
-- Name: pbb_bans; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pbb_bans (
    id integer NOT NULL,
    username character varying(200),
    ip character varying(255),
    email character varying(50),
    message character varying(255),
    expire integer
);


ALTER TABLE public.pbb_bans OWNER TO postgres;

--
-- TOC entry 1819 (class 1259 OID 82386)
-- Dependencies: 1818 5
-- Name: pbb_bans_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE pbb_bans_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.pbb_bans_id_seq OWNER TO postgres;

--
-- TOC entry 3102 (class 0 OID 0)
-- Dependencies: 1819
-- Name: pbb_bans_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE pbb_bans_id_seq OWNED BY pbb_bans.id;


--
-- TOC entry 1820 (class 1259 OID 82388)
-- Dependencies: 2399 2400 5
-- Name: pbb_categories; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pbb_categories (
    id integer NOT NULL,
    cat_name character varying(80) DEFAULT 'New Category'::character varying NOT NULL,
    disp_position integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.pbb_categories OWNER TO postgres;

--
-- TOC entry 1821 (class 1259 OID 82392)
-- Dependencies: 1820 5
-- Name: pbb_categories_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE pbb_categories_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.pbb_categories_id_seq OWNER TO postgres;

--
-- TOC entry 3103 (class 0 OID 0)
-- Dependencies: 1821
-- Name: pbb_categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE pbb_categories_id_seq OWNED BY pbb_categories.id;


--
-- TOC entry 1822 (class 1259 OID 82394)
-- Dependencies: 2402 2403 5
-- Name: pbb_censoring; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pbb_censoring (
    id integer NOT NULL,
    search_for character varying(60) DEFAULT ''::character varying NOT NULL,
    replace_with character varying(60) DEFAULT ''::character varying NOT NULL
);


ALTER TABLE public.pbb_censoring OWNER TO postgres;

--
-- TOC entry 1823 (class 1259 OID 82398)
-- Dependencies: 1822 5
-- Name: pbb_censoring_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE pbb_censoring_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.pbb_censoring_id_seq OWNER TO postgres;

--
-- TOC entry 3104 (class 0 OID 0)
-- Dependencies: 1823
-- Name: pbb_censoring_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE pbb_censoring_id_seq OWNED BY pbb_censoring.id;


--
-- TOC entry 1824 (class 1259 OID 82400)
-- Dependencies: 2405 5
-- Name: pbb_config; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pbb_config (
    conf_name character varying(255) DEFAULT ''::character varying NOT NULL,
    conf_value text
);


ALTER TABLE public.pbb_config OWNER TO postgres;

--
-- TOC entry 1825 (class 1259 OID 82406)
-- Dependencies: 2406 2407 2408 2409 2410 5
-- Name: pbb_forum_perms; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pbb_forum_perms (
    group_id integer DEFAULT 0 NOT NULL,
    forum_id integer DEFAULT 0 NOT NULL,
    read_forum smallint DEFAULT 1 NOT NULL,
    post_replies smallint DEFAULT 1 NOT NULL,
    post_topics smallint DEFAULT 1 NOT NULL
);


ALTER TABLE public.pbb_forum_perms OWNER TO postgres;

--
-- TOC entry 1826 (class 1259 OID 82413)
-- Dependencies: 2411 2412 2413 2414 2415 2416 5
-- Name: pbb_forums; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pbb_forums (
    id integer NOT NULL,
    forum_name character varying(80) DEFAULT 'New forum'::character varying NOT NULL,
    forum_desc text,
    redirect_url character varying(100),
    moderators text,
    num_topics integer DEFAULT 0 NOT NULL,
    num_posts integer DEFAULT 0 NOT NULL,
    last_post integer,
    last_post_id integer,
    last_poster character varying(200),
    sort_by smallint DEFAULT 0 NOT NULL,
    disp_position integer DEFAULT 0 NOT NULL,
    cat_id integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.pbb_forums OWNER TO postgres;

--
-- TOC entry 1827 (class 1259 OID 82424)
-- Dependencies: 1826 5
-- Name: pbb_forums_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE pbb_forums_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.pbb_forums_id_seq OWNER TO postgres;

--
-- TOC entry 3105 (class 0 OID 0)
-- Dependencies: 1827
-- Name: pbb_forums_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE pbb_forums_id_seq OWNED BY pbb_forums.id;


--
-- TOC entry 1828 (class 1259 OID 82426)
-- Dependencies: 2418 2419 2420 2421 2422 2423 2424 2425 2426 2427 2428 2429 2430 2431 5
-- Name: pbb_groups; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pbb_groups (
    g_id integer NOT NULL,
    g_title character varying(50) DEFAULT ''::character varying NOT NULL,
    g_user_title character varying(50),
    g_read_board smallint DEFAULT 1 NOT NULL,
    g_post_replies smallint DEFAULT 1 NOT NULL,
    g_post_topics smallint DEFAULT 1 NOT NULL,
    g_post_polls smallint DEFAULT 1 NOT NULL,
    g_edit_posts smallint DEFAULT 1 NOT NULL,
    g_delete_posts smallint DEFAULT 1 NOT NULL,
    g_delete_topics smallint DEFAULT 1 NOT NULL,
    g_set_title smallint DEFAULT 1 NOT NULL,
    g_search smallint DEFAULT 1 NOT NULL,
    g_search_users smallint DEFAULT 1 NOT NULL,
    g_edit_subjects_interval smallint DEFAULT 300 NOT NULL,
    g_post_flood smallint DEFAULT 30 NOT NULL,
    g_search_flood smallint DEFAULT 30 NOT NULL
);


ALTER TABLE public.pbb_groups OWNER TO postgres;

--
-- TOC entry 1829 (class 1259 OID 82442)
-- Dependencies: 1828 5
-- Name: pbb_groups_g_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE pbb_groups_g_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.pbb_groups_g_id_seq OWNER TO postgres;

--
-- TOC entry 3106 (class 0 OID 0)
-- Dependencies: 1829
-- Name: pbb_groups_g_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE pbb_groups_g_id_seq OWNED BY pbb_groups.g_id;


--
-- TOC entry 1830 (class 1259 OID 82444)
-- Dependencies: 2433 2434 2435 2436 5
-- Name: pbb_online; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pbb_online (
    user_id integer DEFAULT 1 NOT NULL,
    ident character varying(200) DEFAULT ''::character varying NOT NULL,
    logged integer DEFAULT 0 NOT NULL,
    idle smallint DEFAULT 0 NOT NULL
);


ALTER TABLE public.pbb_online OWNER TO postgres;

--
-- TOC entry 1831 (class 1259 OID 82450)
-- Dependencies: 2437 2438 2439 2440 2441 5
-- Name: pbb_posts; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pbb_posts (
    id integer NOT NULL,
    poster character varying(200) DEFAULT ''::character varying NOT NULL,
    poster_id integer DEFAULT 1 NOT NULL,
    poster_ip character varying(15),
    poster_email character varying(50),
    message text,
    hide_smilies smallint DEFAULT 0 NOT NULL,
    posted integer DEFAULT 0 NOT NULL,
    edited integer,
    edited_by character varying(200),
    topic_id integer DEFAULT 0 NOT NULL,
    file character varying(256)
);


ALTER TABLE public.pbb_posts OWNER TO postgres;

--
-- TOC entry 1832 (class 1259 OID 82460)
-- Dependencies: 1831 5
-- Name: pbb_posts_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE pbb_posts_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.pbb_posts_id_seq OWNER TO postgres;

--
-- TOC entry 3107 (class 0 OID 0)
-- Dependencies: 1832
-- Name: pbb_posts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE pbb_posts_id_seq OWNED BY pbb_posts.id;


--
-- TOC entry 1833 (class 1259 OID 82462)
-- Dependencies: 2443 2444 5
-- Name: pbb_ranks; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pbb_ranks (
    id integer NOT NULL,
    rank character varying(50) DEFAULT ''::character varying NOT NULL,
    min_posts integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.pbb_ranks OWNER TO postgres;

--
-- TOC entry 1834 (class 1259 OID 82466)
-- Dependencies: 1833 5
-- Name: pbb_ranks_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE pbb_ranks_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.pbb_ranks_id_seq OWNER TO postgres;

--
-- TOC entry 3108 (class 0 OID 0)
-- Dependencies: 1834
-- Name: pbb_ranks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE pbb_ranks_id_seq OWNED BY pbb_ranks.id;


--
-- TOC entry 1835 (class 1259 OID 82468)
-- Dependencies: 2446 2447 2448 2449 2450 5
-- Name: pbb_reports; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pbb_reports (
    id integer NOT NULL,
    post_id integer DEFAULT 0 NOT NULL,
    topic_id integer DEFAULT 0 NOT NULL,
    forum_id integer DEFAULT 0 NOT NULL,
    reported_by integer DEFAULT 0 NOT NULL,
    created integer DEFAULT 0 NOT NULL,
    message text,
    zapped integer,
    zapped_by integer
);


ALTER TABLE public.pbb_reports OWNER TO postgres;

--
-- TOC entry 1836 (class 1259 OID 82478)
-- Dependencies: 1835 5
-- Name: pbb_reports_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE pbb_reports_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.pbb_reports_id_seq OWNER TO postgres;

--
-- TOC entry 3109 (class 0 OID 0)
-- Dependencies: 1836
-- Name: pbb_reports_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE pbb_reports_id_seq OWNED BY pbb_reports.id;


--
-- TOC entry 1837 (class 1259 OID 82480)
-- Dependencies: 2452 2453 5
-- Name: pbb_search_cache; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pbb_search_cache (
    id integer DEFAULT 0 NOT NULL,
    ident character varying(200) DEFAULT ''::character varying NOT NULL,
    search_data text
);


ALTER TABLE public.pbb_search_cache OWNER TO postgres;

--
-- TOC entry 1838 (class 1259 OID 82487)
-- Dependencies: 2454 2455 2456 5
-- Name: pbb_search_matches; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pbb_search_matches (
    post_id integer DEFAULT 0 NOT NULL,
    word_id integer DEFAULT 0 NOT NULL,
    subject_match smallint DEFAULT 0 NOT NULL
);


ALTER TABLE public.pbb_search_matches OWNER TO postgres;

--
-- TOC entry 1839 (class 1259 OID 82492)
-- Dependencies: 2457 5
-- Name: pbb_search_words; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pbb_search_words (
    id integer NOT NULL,
    word character varying(20) DEFAULT ''::character varying NOT NULL
);


ALTER TABLE public.pbb_search_words OWNER TO postgres;

--
-- TOC entry 1840 (class 1259 OID 82495)
-- Dependencies: 1839 5
-- Name: pbb_search_words_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE pbb_search_words_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.pbb_search_words_id_seq OWNER TO postgres;

--
-- TOC entry 3110 (class 0 OID 0)
-- Dependencies: 1840
-- Name: pbb_search_words_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE pbb_search_words_id_seq OWNED BY pbb_search_words.id;


--
-- TOC entry 1841 (class 1259 OID 82497)
-- Dependencies: 2459 2460 5
-- Name: pbb_subscriptions; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pbb_subscriptions (
    user_id integer DEFAULT 0 NOT NULL,
    topic_id integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.pbb_subscriptions OWNER TO postgres;

--
-- TOC entry 1842 (class 1259 OID 82501)
-- Dependencies: 2461 2462 2463 2464 2465 2466 2467 2468 2469 2470 5
-- Name: pbb_topics; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pbb_topics (
    id integer NOT NULL,
    poster character varying(200) DEFAULT ''::character varying NOT NULL,
    subject character varying(255) DEFAULT ''::character varying NOT NULL,
    posted integer DEFAULT 0 NOT NULL,
    last_post integer DEFAULT 0 NOT NULL,
    last_post_id integer DEFAULT 0 NOT NULL,
    last_poster character varying(200),
    num_views integer DEFAULT 0 NOT NULL,
    num_replies integer DEFAULT 0 NOT NULL,
    closed smallint DEFAULT 0 NOT NULL,
    sticky smallint DEFAULT 0 NOT NULL,
    moved_to integer,
    forum_id integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.pbb_topics OWNER TO postgres;

--
-- TOC entry 1843 (class 1259 OID 82516)
-- Dependencies: 1842 5
-- Name: pbb_topics_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE pbb_topics_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.pbb_topics_id_seq OWNER TO postgres;

--
-- TOC entry 3111 (class 0 OID 0)
-- Dependencies: 1843
-- Name: pbb_topics_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE pbb_topics_id_seq OWNED BY pbb_topics.id;


--
-- TOC entry 1844 (class 1259 OID 82518)
-- Dependencies: 2472 2473 2474 2475 2476 2477 2478 2479 2480 2481 2482 2483 2484 2485 2486 2487 2488 2489 2490 2491 5
-- Name: pbb_users; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pbb_users (
    id integer NOT NULL,
    group_id integer DEFAULT 4 NOT NULL,
    username character varying(200) DEFAULT ''::character varying NOT NULL,
    "password" character varying(40) DEFAULT ''::character varying NOT NULL,
    email character varying(50) DEFAULT ''::character varying NOT NULL,
    title character varying(50),
    realname character varying(40),
    url character varying(100),
    jabber character varying(75),
    icq character varying(12),
    msn character varying(50),
    aim character varying(30),
    yahoo character varying(30),
    "location" character varying(30),
    use_avatar smallint DEFAULT 0 NOT NULL,
    signature text,
    disp_topics smallint,
    disp_posts smallint,
    email_setting smallint DEFAULT 1 NOT NULL,
    save_pass smallint DEFAULT 1 NOT NULL,
    notify_with_post smallint DEFAULT 0 NOT NULL,
    show_smilies smallint DEFAULT 1 NOT NULL,
    show_img smallint DEFAULT 1 NOT NULL,
    show_img_sig smallint DEFAULT 1 NOT NULL,
    show_avatars smallint DEFAULT 1 NOT NULL,
    show_sig smallint DEFAULT 1 NOT NULL,
    timezone real DEFAULT 0 NOT NULL,
    "language" character varying(25) DEFAULT 'English'::character varying NOT NULL,
    style character varying(25) DEFAULT 'Oxygen'::character varying NOT NULL,
    num_posts integer DEFAULT 0 NOT NULL,
    last_post integer,
    registered integer DEFAULT 0 NOT NULL,
    registration_ip character varying(15) DEFAULT '0.0.0.0'::character varying NOT NULL,
    last_visit integer DEFAULT 0 NOT NULL,
    admin_note character varying(30),
    activate_string character varying(50),
    activate_key character varying(8)
);


ALTER TABLE public.pbb_users OWNER TO postgres;

--
-- TOC entry 1845 (class 1259 OID 82543)
-- Dependencies: 1844 5
-- Name: pbb_users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE pbb_users_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.pbb_users_id_seq OWNER TO postgres;

--
-- TOC entry 3112 (class 0 OID 0)
-- Dependencies: 1845
-- Name: pbb_users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE pbb_users_id_seq OWNED BY pbb_users.id;


--
-- TOC entry 1846 (class 1259 OID 82545)
-- Dependencies: 1976 5
-- Name: permissions_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW permissions_list AS
    SELECT cs_permission_list.id, cs_permission_list.permission_id, cs_permission_list.module_id, cs_permission_list.can_read, cs_permission_list.can_write, cs_permission_list.can_delete, cs_permission_list.can_admin, cs_permission_list.can_review, cs_permission_list.can_observe, cs_module.name AS modulename, cs_module.description AS moduledescr, cs_module.is_hidden, cs_permission.name AS permname, cs_permission.description AS permdescr FROM cs_permission_list, cs_permission, cs_module WHERE ((cs_permission_list.permission_id = cs_permission.id) AND (cs_permission_list.module_id = cs_module.id));


ALTER TABLE public.permissions_list OWNER TO postgres;

--
-- TOC entry 1847 (class 1259 OID 82548)
-- Dependencies: 1977 5
-- Name: post_relations_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW post_relations_list AS
    SELECT cs_post_relation.id, cs_post_relation.post_id, cs_post_relation.relation_post_id, cs_post_relation.division_id, cs_division.name AS divisionname, cs_division.description AS divisiondescr, cs_post.name AS postname, cs_post.description AS postdescr, postrel.name AS relpostname, postrel.description AS relpostdescr FROM cs_post_relation, cs_division, cs_post, cs_post postrel WHERE (((cs_post_relation.division_id = cs_division.id) AND (cs_post_relation.post_id = cs_post.id)) AND (cs_post_relation.relation_post_id = postrel.id));


ALTER TABLE public.post_relations_list OWNER TO postgres;

--
-- TOC entry 1848 (class 1259 OID 82551)
-- Dependencies: 1978 5
-- Name: process_properties_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW process_properties_list AS
    SELECT cs_process_property.id, cs_process_property.name, cs_process_property.description, cs_process_property.process_id, cs_process_property.sign_id, cs_process_property.type_id, cs_process_property.default_value, cs_process_property.is_list, cs_process_property.is_name_as_value, cs_process_property.directory_id, cs_process_property.value_field, cs_sign.name AS signname, cs_property_type.name AS typename, (SELECT get_property_directory_name(cs_process_property.directory_id) AS get_property_directory_name) AS directoryname, (SELECT get_property_directory_tablename(cs_process_property.directory_id) AS get_property_directory_tablename) AS directorytablename, (SELECT get_property_directory_parameters(cs_process_property.directory_id) AS get_property_directory_parameters) AS directoryparameters, (SELECT get_property_directory_custom(cs_process_property.directory_id) AS get_property_directory_custom) AS directorycustom FROM cs_process_property, cs_sign, cs_property_type WHERE ((cs_sign.id = cs_process_property.sign_id) AND (cs_property_type.id = cs_process_property.type_id));


ALTER TABLE public.process_properties_list OWNER TO postgres;

--
-- TOC entry 1849 (class 1259 OID 82554)
-- Dependencies: 1979 5
-- Name: process_action_properties_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW process_action_properties_list AS
    SELECT cs_process_action_property.id, cs_process_action_property.action_id, cs_process_action_property.property_id, cs_process_action_property.npp, cs_process_action_property.is_required, cs_process_action_property.is_readonly, cs_process_action_property.is_nextuser, cs_process_action_property.is_childprocess, cs_process_action_property.is_active, cs_process_action_property.is_combo, cs_process_action_property.is_hidden, cs_process_action_property.is_multiple, cs_process_action_property.parameters, cs_process_action_property.condition, process_properties_list.name, process_properties_list.description, process_properties_list.process_id, process_properties_list.sign_id, process_properties_list.type_id, process_properties_list.default_value, process_properties_list.is_list, process_properties_list.is_name_as_value, process_properties_list.directory_id, process_properties_list.value_field, process_properties_list.signname, process_properties_list.typename, process_properties_list.directoryname, process_properties_list.directorytablename, process_properties_list.directoryparameters, process_properties_list.directorycustom FROM cs_process_action_property, process_properties_list WHERE (process_properties_list.id = cs_process_action_property.property_id);


ALTER TABLE public.process_action_properties_list OWNER TO postgres;

--
-- TOC entry 1850 (class 1259 OID 82557)
-- Dependencies: 1980 5
-- Name: process_action_transports_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW process_action_transports_list AS
    SELECT cs_process_action_transport.id, cs_process_action_transport.action_id, cs_process_action_transport.transport_id, cs_process_action_transport.subject_template, cs_process_action_transport.text_template, cs_process_action_transport.recipients_template, cs_process_action_transport.event_id, cs_event.name AS eventname, cs_transport.name, cs_transport.description, cs_transport.server_address, cs_transport.server_port, cs_transport.server_login, cs_transport.server_passwd, cs_transport.class_name FROM cs_process_action_transport, cs_transport, cs_event WHERE ((cs_process_action_transport.transport_id = cs_transport.id) AND (cs_process_action_transport.event_id = cs_event.id));


ALTER TABLE public.process_action_transports_list OWNER TO postgres;

--
-- TOC entry 1851 (class 1259 OID 82560)
-- Dependencies: 1981 5
-- Name: process_actions_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW process_actions_list AS
    SELECT DISTINCT cs_process_action.id, cs_process_action.name, cs_process_action.description, cs_process_action.process_id, cs_process_action.type_id, cs_process_action.is_interactive, cs_process_action.weight, cs_process_action.planed, cs_process_action.fixed_planed, cs_process_action.form, cs_process_action.code, cs_process_action.true_action_id, true_process_action.name AS trueactionname, cs_process_action.false_action_id, false_process_action.name AS falseactionname, cs_process_action.npp, cs_action_type.name AS typename FROM (((cs_process_action LEFT JOIN cs_action_type ON ((cs_process_action.type_id = cs_action_type.id))) LEFT JOIN cs_process_action true_process_action ON ((cs_process_action.true_action_id = true_process_action.id))) LEFT JOIN cs_process_action false_process_action ON ((cs_process_action.false_action_id = false_process_action.id))) ORDER BY cs_process_action.npp, cs_process_action.id, cs_process_action.name, cs_process_action.description, cs_process_action.process_id, cs_process_action.type_id, cs_process_action.is_interactive, cs_process_action.weight, cs_process_action.planed, cs_process_action.fixed_planed, cs_process_action.form, cs_process_action.code, cs_action_type.name, cs_process_action.true_action_id, true_process_action.name, cs_process_action.false_action_id, false_process_action.name;


ALTER TABLE public.process_actions_list OWNER TO postgres;

--
-- TOC entry 1852 (class 1259 OID 82564)
-- Dependencies: 1982 5
-- Name: process_info_properties_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW process_info_properties_list AS
    SELECT cs_process_info_property.id, cs_process_info_property.process_id, cs_process_info_property.property_id, cs_process_property.name, cs_process_property.description, cs_process_property.sign_id, cs_process_property.type_id, cs_process_property.default_value, cs_process_property.is_list, cs_process_property.is_name_as_value, cs_process_property.directory_id, cs_sign.name AS signname, cs_property_type.name AS typename, (SELECT get_property_directory_name(cs_process_property.directory_id) AS get_property_directory_name) AS directoryname, (SELECT get_property_directory_tablename(cs_process_property.directory_id) AS get_property_directory_tablename) AS directorytablename, (SELECT get_property_directory_parameters(cs_process_property.directory_id) AS get_property_directory_parameters) AS directoryparameters, (SELECT get_property_directory_custom(cs_process_property.directory_id) AS get_property_directory_custom) AS directorycustom FROM cs_process_info_property, cs_process_property, cs_sign, cs_property_type WHERE (((cs_process_info_property.property_id = cs_process_property.id) AND (cs_sign.id = cs_process_property.sign_id)) AND (cs_property_type.id = cs_process_property.type_id));


ALTER TABLE public.process_info_properties_list OWNER TO postgres;

--
-- TOC entry 1853 (class 1259 OID 82567)
-- Dependencies: 1983 5
-- Name: process_instance_actions_performers_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW process_instance_actions_performers_list AS
    SELECT cs_process_current_action_performer.id, cs_process_current_action_performer.instance_action_id, cs_process_current_action_performer.status_id, cs_process_current_action_performer.initiator_id, cs_process_current_action_performer.performer_id, cs_process_current_action_performer.started_at, cs_process_current_action_performer.ended_at, cs_process_current_action.instance_id, cs_process_current_action.action_id, cs_process_current_action.planed, cs_process_current_action.fixed_planed, cs_process_action.process_id, cs_process_action.name, cs_process_action.description, cs_process_action.type_id, cs_action_type.name AS typename, cs_process_action.is_interactive, cs_process_action.weight, cs_process_action.code, cs_process_action.form, cs_process_action.true_action_id, true_process_action.name AS trueactionname, cs_process_action.false_action_id, false_process_action.name AS falseactionname, cs_status.name AS statusname, cs_initiator.name AS initiatorname, cs_performer.name AS performername, cs_process_action.npp FROM ((((((((cs_process_current_action_performer LEFT JOIN cs_process_current_action ON ((cs_process_current_action_performer.instance_action_id = cs_process_current_action.id))) LEFT JOIN cs_status ON ((cs_process_current_action_performer.status_id = cs_status.id))) LEFT JOIN cs_account cs_initiator ON ((cs_process_current_action_performer.initiator_id = cs_initiator.id))) LEFT JOIN cs_account cs_performer ON ((cs_process_current_action_performer.performer_id = cs_performer.id))) LEFT JOIN cs_process_action ON ((cs_process_current_action.action_id = cs_process_action.id))) LEFT JOIN cs_process_action true_process_action ON ((cs_process_action.true_action_id = true_process_action.id))) LEFT JOIN cs_process_action false_process_action ON ((cs_process_action.false_action_id = false_process_action.id))) LEFT JOIN cs_action_type ON ((cs_process_action.type_id = cs_action_type.id)));


ALTER TABLE public.process_instance_actions_performers_list OWNER TO postgres;

--
-- TOC entry 1854 (class 1259 OID 82571)
-- Dependencies: 1984 5
-- Name: process_instance_properties_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW process_instance_properties_list AS
    SELECT cs_process_property_value.id, cs_process_property_value.instance_id, cs_process_property_value.property_id, cs_process_property_value.value_id, cs_process_property.name, cs_process_property.description, cs_process_property.sign_id, cs_sign.name AS signname, cs_process_property.type_id, cs_property_type.name AS typename, cs_process_property.is_list, cs_process_property.is_name_as_value, cs_process_property.value_field, cs_process_property.directory_id, (SELECT get_property_directory_name(cs_process_property.directory_id) AS get_property_directory_name) AS directoryname, (SELECT get_property_directory_tablename(cs_process_property.directory_id) AS get_property_directory_tablename) AS directorytablename, (SELECT get_property_directory_parameters(cs_process_property.directory_id) AS get_property_directory_parameters) AS directoryparameters, (SELECT get_property_directory_custom(cs_process_property.directory_id) AS get_property_directory_custom) AS directorycustom, cs_property_value.mime_type, cs_property_value.value FROM cs_process_property_value, cs_process_property, cs_sign, cs_property_type, cs_property_value WHERE ((((cs_process_property.id = cs_process_property_value.property_id) AND (cs_sign.id = cs_process_property.sign_id)) AND (cs_property_type.id = cs_process_property.type_id)) AND (cs_process_property_value.value_id = cs_property_value.id));


ALTER TABLE public.process_instance_properties_list OWNER TO postgres;

--
-- TOC entry 1855 (class 1259 OID 82575)
-- Dependencies: 1985 5
-- Name: process_instances_tree; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW process_instances_tree AS
    SELECT child.id, child.parent_id, child.process_id, child.initiator_id, child.status_id, child.started_at, child.ended_at, process.name, process.description, cs_status.name AS statusname, cs_account.name AS initiatorname, (SELECT get_level('cs_process_instance'::text, child.id) AS get_level) AS "level" FROM (((((cs_process_instance child JOIN get_full_big_tree('cs_process_instance'::text) pid(pid) ON ((child.id = pid.pid))) LEFT JOIN cs_process process ON ((child.process_id = process.id))) LEFT JOIN cs_process_instance parent ON ((child.parent_id = parent.id))) LEFT JOIN cs_account ON ((child.initiator_id = cs_account.id))) LEFT JOIN cs_status ON ((child.status_id = cs_status.id)));


ALTER TABLE public.process_instances_tree OWNER TO postgres;

--
-- TOC entry 1856 (class 1259 OID 82579)
-- Dependencies: 1986 5
-- Name: process_roles; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW process_roles AS
    SELECT cs_process_role.id, cs_process_role.process_id, cs_process_role.role_id, cs_process_role.account_id, cs_role.name AS rolename, cs_role.description AS roledescr, cs_account.name AS accountname, cs_account.description AS accountdescr FROM cs_process_role, cs_role, cs_account WHERE ((cs_process_role.role_id = cs_role.id) AND (cs_process_role.account_id = cs_account.id));


ALTER TABLE public.process_roles OWNER TO postgres;

--
-- TOC entry 1857 (class 1259 OID 82582)
-- Dependencies: 1987 5
-- Name: process_transitions; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW process_transitions AS
    SELECT cs_process_transition.id, cs_process_transition.process_id, cs_process_transition.from_action_id, cs_process_transition.to_action_id, cs_process.name AS processname, cs_from_action.name AS fromactionname, cs_from_action.description AS fromactiondescr, cs_to_action.name AS toactionname, cs_to_action.description AS toactiondescr, cs_from_action.npp AS fromnpp, cs_to_action.npp AS tonpp FROM cs_process_transition, cs_process, cs_process_action cs_from_action, cs_process_action cs_to_action WHERE (((cs_process_transition.process_id = cs_process.id) AND (cs_process_transition.from_action_id = cs_from_action.id)) AND (cs_process_transition.to_action_id = cs_to_action.id)) ORDER BY cs_from_action.npp, cs_to_action.npp;


ALTER TABLE public.process_transitions OWNER TO postgres;

--
-- TOC entry 1858 (class 1259 OID 82585)
-- Dependencies: 1988 5
-- Name: process_transports_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW process_transports_list AS
    SELECT cs_process_transport.id, cs_process_transport.process_id, cs_process_transport.transport_id, cs_process_transport.subject_template, cs_process_transport.text_template, cs_process_transport.recipients_template, cs_process_transport.event_id, cs_event.name AS eventname, cs_transport.name, cs_transport.description, cs_transport.server_address, cs_transport.server_port, cs_transport.server_login, cs_transport.server_passwd, cs_transport.class_name FROM cs_process_transport, cs_transport, cs_event WHERE ((cs_process_transport.transport_id = cs_transport.id) AND (cs_process_transport.event_id = cs_event.id));


ALTER TABLE public.process_transports_list OWNER TO postgres;

--
-- TOC entry 1859 (class 1259 OID 82588)
-- Dependencies: 1989 5
-- Name: processes_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW processes_list AS
    SELECT child.id, child.parent_id, child.name, child.description, child.author_id, child.version, child.created_at, child.activated_at, child.is_active, child.is_public, child.is_standalone, child.is_hidden, child.is_system, parent.name AS parentname, cs_account.name AS authorname FROM ((cs_process child LEFT JOIN cs_process parent ON ((child.parent_id = parent.id))) LEFT JOIN cs_account ON ((child.author_id = cs_account.id)));


ALTER TABLE public.processes_list OWNER TO postgres;

--
-- TOC entry 1860 (class 1259 OID 82592)
-- Dependencies: 1990 5
-- Name: project_active_processes_tree; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW project_active_processes_tree AS
    SELECT cs_project.id AS project_id, cs_project_process.id AS project_process_id, processes_tree.id, processes_tree.parent_id, processes_tree.name, processes_tree.description, processes_tree.author_id, processes_tree.version, processes_tree.created_at, processes_tree.activated_at, processes_tree.is_active, processes_tree.is_public, processes_tree.is_standalone, processes_tree.parentname, processes_tree.authorname, processes_tree."level" FROM cs_project, cs_project_process, processes_tree WHERE ((((cs_project_process.process_id = processes_tree.id) AND (cs_project_process.project_id = cs_project.id)) AND (processes_tree.is_active = true)) AND ((processes_tree.is_standalone = true) OR (processes_tree.is_public = true)));


ALTER TABLE public.project_active_processes_tree OWNER TO postgres;

--
-- TOC entry 1861 (class 1259 OID 82595)
-- Dependencies: 1991 5
-- Name: project_instance_properties_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW project_instance_properties_list AS
    SELECT cs_project_property_value.id, cs_project_property_value.instance_id, cs_project_property_value.property_id, cs_project_property_value.value_id, cs_project_property.name, cs_project_property.description, cs_project_property.sign_id, cs_sign.name AS signname, cs_project_property.type_id, cs_property_type.name AS typename, cs_property_value.mime_type, cs_property_value.value FROM cs_project_property_value, cs_project_property, cs_sign, cs_property_type, cs_property_value WHERE ((((cs_project_property.id = cs_project_property_value.property_id) AND (cs_sign.id = cs_project_property.sign_id)) AND (cs_property_type.id = cs_project_property.type_id)) AND (cs_project_property_value.value_id = cs_property_value.id));


ALTER TABLE public.project_instance_properties_list OWNER TO postgres;

--
-- TOC entry 1862 (class 1259 OID 82598)
-- Dependencies: 1992 5
-- Name: project_processes_instances_tree; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW project_processes_instances_tree AS
    SELECT cs_project_process_instance.project_instance_id, cs_project_instance.project_id, cs_project.name AS projectname, child.id, child.parent_id, parent.name AS parentname, child.process_id, child.initiator_id, child.status_id, child.started_at, child.ended_at, child.name, child.description, child.statusname, child.initiatorname, child."level" FROM cs_project_process_instance, cs_project_instance, cs_project, ((process_instances_tree child JOIN get_full_big_tree('cs_process_instance'::text) pid(pid) ON ((child.id = pid.pid))) LEFT JOIN process_instances_tree parent ON ((child.parent_id = parent.id))) WHERE (((cs_project_process_instance.process_instance_id = child.id) AND (cs_project_process_instance.project_instance_id = cs_project_instance.id)) AND (cs_project_instance.project_id = cs_project.id));


ALTER TABLE public.project_processes_instances_tree OWNER TO postgres;

--
-- TOC entry 1863 (class 1259 OID 82602)
-- Dependencies: 1993 5
-- Name: project_processes_instances_tree_old; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW project_processes_instances_tree_old AS
    SELECT cs_project_process_instance.project_instance_id, cs_project_instance.project_id, cs_project.name AS projectname, child.id, child.parent_id, child.initiator_id, child.status_id, child.started_at, child.ended_at, child.process_id, process.parent_id AS process_parent_id, process.name, process.parentname, process.description, process.is_active, process.author_id, process.authorname, process.created_at, process.activated_at, cs_status.name AS statusname, cs_account.name AS initiatorname, (SELECT get_level('cs_process_instance'::text, child.id) AS get_level) AS "level" FROM cs_project_process_instance, cs_project_instance, cs_project, (((((cs_process_instance child JOIN get_full_big_tree('cs_process_instance'::text) pid(pid) ON ((child.id = pid.pid))) LEFT JOIN processes_tree process ON ((child.process_id = process.id))) LEFT JOIN cs_process_instance parent ON ((child.parent_id = parent.id))) LEFT JOIN cs_account ON ((child.initiator_id = cs_account.id))) LEFT JOIN cs_status ON ((child.status_id = cs_status.id))) WHERE (((cs_project_process_instance.process_instance_id = child.id) AND (cs_project_process_instance.project_instance_id = cs_project_instance.id)) AND (cs_project_instance.project_id = cs_project.id));


ALTER TABLE public.project_processes_instances_tree_old OWNER TO postgres;

--
-- TOC entry 1864 (class 1259 OID 82606)
-- Dependencies: 1994 5
-- Name: project_processes_tree; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW project_processes_tree AS
    SELECT cs_project.id AS project_id, cs_project_process.id AS project_process_id, processes_tree.id, processes_tree.parent_id, processes_tree.name, processes_tree.description, processes_tree.author_id, processes_tree.version, processes_tree.created_at, processes_tree.activated_at, processes_tree.is_active, processes_tree.is_public, processes_tree.is_standalone, processes_tree.parentname, processes_tree.authorname, processes_tree."level" FROM cs_project, cs_project_process, processes_tree WHERE ((cs_project_process.process_id = processes_tree.id) AND (cs_project_process.project_id = cs_project.id));


ALTER TABLE public.project_processes_tree OWNER TO postgres;

--
-- TOC entry 1865 (class 1259 OID 82609)
-- Dependencies: 1995 5
-- Name: project_roles; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW project_roles AS
    SELECT cs_project_role.id, cs_project_role.project_id, cs_project_role.role_id, cs_project_role.division_id, cs_role.name AS rolename, cs_role.description AS roledescr, cs_division.name AS divisionname, cs_division.description AS divisiondescr FROM cs_project_role, cs_role, cs_division WHERE ((cs_project_role.role_id = cs_role.id) AND (cs_project_role.division_id = cs_division.id));


ALTER TABLE public.project_roles OWNER TO postgres;

--
-- TOC entry 1866 (class 1259 OID 82612)
-- Dependencies: 1996 5
-- Name: projects_instances; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW projects_instances AS
    SELECT cs_project_instance.id, cs_project_instance.project_id, cs_project_instance.initiator_id, cs_project_instance.status_id, cs_project_instance.started_at, cs_project_instance.ended_at, cs_status.name AS statusname, cs_project.name, cs_project.description, cs_project.is_active, cs_project.is_permanent, cs_project.is_system, cs_project.version, cs_account.name AS initiatorname FROM cs_project_instance, cs_status, cs_project, cs_account WHERE (((cs_project.id = cs_project_instance.project_id) AND (cs_account.id = cs_project_instance.initiator_id)) AND (cs_status.id = cs_project_instance.status_id));


ALTER TABLE public.projects_instances OWNER TO postgres;

--
-- TOC entry 1867 (class 1259 OID 82615)
-- Dependencies: 1997 5
-- Name: public_active_processes_tree; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public_active_processes_tree AS
    SELECT processes_tree.id, processes_tree.parent_id, processes_tree.name, processes_tree.description, processes_tree.author_id, processes_tree.version, processes_tree.created_at, processes_tree.activated_at, processes_tree.is_active, processes_tree.is_public, processes_tree.is_standalone, processes_tree.parentname, processes_tree.authorname, processes_tree."level" FROM processes_tree WHERE (((processes_tree.is_active = true) AND (processes_tree.is_standalone = true)) AND (processes_tree.is_public = true));


ALTER TABLE public.public_active_processes_tree OWNER TO postgres;

--
-- TOC entry 1868 (class 1259 OID 82618)
-- Dependencies: 1998 5
-- Name: public_documents_tree; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public_documents_tree AS
    SELECT child.id, child.parent_id, child.topic_id, child.name, child.description, child.created_at, child.updated_at, child.created_by, child.updated_by, child.is_active, child.npp, parent.name AS parentname, creator.name AS creatorname, updater.name AS updatorname, cs_public_topic.name AS topicname, (SELECT get_level('cs_public_document'::text, child.id) AS get_level) AS "level" FROM (((((cs_public_document child JOIN get_full_big_tree('cs_public_document'::text) pid(pid) ON ((child.id = pid.pid))) LEFT JOIN cs_public_document parent ON ((child.parent_id = parent.id))) LEFT JOIN cs_public_topic ON ((child.topic_id = cs_public_topic.id))) LEFT JOIN cs_account creator ON ((child.created_by = creator.id))) LEFT JOIN cs_account updater ON ((child.updated_by = updater.id)));


ALTER TABLE public.public_documents_tree OWNER TO postgres;

--
-- TOC entry 1869 (class 1259 OID 82622)
-- Dependencies: 1999 5
-- Name: responsers_list; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW responsers_list AS
    SELECT cs_responser.id, cs_responser.name, cs_responser.description, cs_responser.account_id, cs_responser.is_active, cs_account.name AS accountname, cs_account.description AS accountdescr FROM cs_responser, cs_account WHERE (cs_responser.account_id = cs_account.id) ORDER BY cs_responser.id, cs_responser.name;


ALTER TABLE public.responsers_list OWNER TO postgres;

--
-- TOC entry 2258 (class 2604 OID 82625)
-- Dependencies: 1685 1621
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_account ALTER COLUMN id SET DEFAULT nextval('cs_account_id_seq'::regclass);


--
-- TOC entry 2323 (class 2604 OID 82626)
-- Dependencies: 1684 1683
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_account_division ALTER COLUMN id SET DEFAULT nextval('cs_account_division_id_seq'::regclass);


--
-- TOC entry 2262 (class 2604 OID 82627)
-- Dependencies: 1686 1626
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_account_post ALTER COLUMN id SET DEFAULT nextval('cs_account_post_id_seq'::regclass);


--
-- TOC entry 2265 (class 2604 OID 82628)
-- Dependencies: 1687 1630
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_account_today ALTER COLUMN id SET DEFAULT nextval('cs_account_today_id_seq'::regclass);


--
-- TOC entry 2267 (class 2604 OID 82629)
-- Dependencies: 1688 1631
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_action_type ALTER COLUMN id SET DEFAULT nextval('cs_action_type_id_seq'::regclass);


--
-- TOC entry 2324 (class 2604 OID 82630)
-- Dependencies: 1690 1689
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_blob ALTER COLUMN id SET DEFAULT nextval('cs_blob_id_seq'::regclass);


--
-- TOC entry 2288 (class 2604 OID 82631)
-- Dependencies: 1698 1648
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_calendar ALTER COLUMN id SET DEFAULT nextval('cs_calendar_id_seq'::regclass);


--
-- TOC entry 2291 (class 2604 OID 82632)
-- Dependencies: 1694 1649
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_calendar_event ALTER COLUMN id SET DEFAULT nextval('cs_calendar_event_id_seq'::regclass);


--
-- TOC entry 2302 (class 2604 OID 82633)
-- Dependencies: 1691 1661
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_calendar_event_alarm ALTER COLUMN id SET DEFAULT nextval('cs_calendar_event_alarm_id_seq'::regclass);


--
-- TOC entry 2325 (class 2604 OID 82634)
-- Dependencies: 1693 1692
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_calendar_event_blob ALTER COLUMN id SET DEFAULT nextval('cs_calendar_event_blob_id_seq'::regclass);


--
-- TOC entry 2295 (class 2604 OID 82635)
-- Dependencies: 1696 1654
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_calendar_event_period ALTER COLUMN id SET DEFAULT nextval('cs_calendar_event_period_id_seq'::regclass);


--
-- TOC entry 2292 (class 2604 OID 82636)
-- Dependencies: 1695 1650
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_calendar_event_period_detail ALTER COLUMN id SET DEFAULT nextval('cs_calendar_event_period_detail_id_seq'::regclass);


--
-- TOC entry 2300 (class 2604 OID 82637)
-- Dependencies: 1697 1658
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_calendar_event_reciever ALTER COLUMN id SET DEFAULT nextval('cs_calendar_event_reciever_id_seq'::regclass);


--
-- TOC entry 2304 (class 2604 OID 82638)
-- Dependencies: 1699 1665
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_calendar_permission ALTER COLUMN id SET DEFAULT nextval('cs_calendar_permission_id_seq'::regclass);


--
-- TOC entry 2259 (class 2604 OID 82639)
-- Dependencies: 1700 1622
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_cellop ALTER COLUMN id SET DEFAULT nextval('cs_cellop_id_seq'::regclass);


--
-- TOC entry 2316 (class 2604 OID 82640)
-- Dependencies: 1704 1675
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_chrono ALTER COLUMN id SET DEFAULT nextval('cs_chrono_id_seq'::regclass);


--
-- TOC entry 2305 (class 2604 OID 82641)
-- Dependencies: 1701 1667
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_chrono_action ALTER COLUMN id SET DEFAULT nextval('cs_chrono_action_id_seq'::regclass);


--
-- TOC entry 2326 (class 2604 OID 82642)
-- Dependencies: 1703 1702
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_chrono_blob ALTER COLUMN id SET DEFAULT nextval('cs_chrono_blob_id_seq'::regclass);


--
-- TOC entry 2306 (class 2604 OID 82643)
-- Dependencies: 1705 1669
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_chrono_property ALTER COLUMN id SET DEFAULT nextval('cs_chrono_property_id_seq'::regclass);


--
-- TOC entry 2307 (class 2604 OID 82644)
-- Dependencies: 1706 1670
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_chrono_value ALTER COLUMN id SET DEFAULT nextval('cs_chrono_value_id_seq'::regclass);


--
-- TOC entry 2328 (class 2604 OID 82645)
-- Dependencies: 1708 1707
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_constants ALTER COLUMN id SET DEFAULT nextval('cs_constants_id_seq'::regclass);


--
-- TOC entry 2319 (class 2604 OID 82646)
-- Dependencies: 1709 1677
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_contact ALTER COLUMN id SET DEFAULT nextval('cs_contact_id_seq'::regclass);


--
-- TOC entry 2321 (class 2604 OID 82647)
-- Dependencies: 1710 1678
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_contact_list ALTER COLUMN id SET DEFAULT nextval('cs_contact_list_id_seq'::regclass);


--
-- TOC entry 2322 (class 2604 OID 82648)
-- Dependencies: 1711 1680
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_contact_permission ALTER COLUMN id SET DEFAULT nextval('cs_contact_permission_id_seq'::regclass);


--
-- TOC entry 2329 (class 2604 OID 82649)
-- Dependencies: 1713 1712
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_custom_setting ALTER COLUMN id SET DEFAULT nextval('cs_custom_setting_id_seq'::regclass);


--
-- TOC entry 2331 (class 2604 OID 82650)
-- Dependencies: 1715 1714
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_delegate ALTER COLUMN id SET DEFAULT nextval('cs_delegate_id_seq'::regclass);


--
-- TOC entry 2334 (class 2604 OID 82651)
-- Dependencies: 1721 1716
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_directory ALTER COLUMN id SET DEFAULT nextval('cs_directory_id_seq'::regclass);


--
-- TOC entry 2335 (class 2604 OID 82652)
-- Dependencies: 1718 1717
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_directory_blob ALTER COLUMN id SET DEFAULT nextval('cs_directory_blob_id_seq'::regclass);


--
-- TOC entry 2338 (class 2604 OID 82653)
-- Dependencies: 1720 1719
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_directory_field ALTER COLUMN id SET DEFAULT nextval('cs_directory_field_id_seq'::regclass);


--
-- TOC entry 2339 (class 2604 OID 82654)
-- Dependencies: 1723 1722
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_directory_record ALTER COLUMN id SET DEFAULT nextval('cs_directory_record_id_seq'::regclass);


--
-- TOC entry 2340 (class 2604 OID 82655)
-- Dependencies: 1725 1724
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_directory_value ALTER COLUMN id SET DEFAULT nextval('cs_directory_value_id_seq'::regclass);


--
-- TOC entry 2260 (class 2604 OID 82656)
-- Dependencies: 1726 1623
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_division ALTER COLUMN id SET DEFAULT nextval('cs_division_id_seq'::regclass);


--
-- TOC entry 2341 (class 2604 OID 82657)
-- Dependencies: 1728 1727
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_event ALTER COLUMN id SET DEFAULT nextval('cs_event_id_seq'::regclass);


--
-- TOC entry 2296 (class 2604 OID 82658)
-- Dependencies: 1730 1655
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_event_period ALTER COLUMN id SET DEFAULT nextval('cs_event_period_id_seq'::regclass);


--
-- TOC entry 2297 (class 2604 OID 82659)
-- Dependencies: 1729 1656
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_event_period_condition ALTER COLUMN id SET DEFAULT nextval('cs_event_period_condition_id_seq'::regclass);


--
-- TOC entry 2293 (class 2604 OID 82660)
-- Dependencies: 1731 1651
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_event_priority ALTER COLUMN id SET DEFAULT nextval('cs_event_priority_id_seq'::regclass);


--
-- TOC entry 2294 (class 2604 OID 82661)
-- Dependencies: 1732 1652
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_event_status ALTER COLUMN id SET DEFAULT nextval('cs_event_status_id_seq'::regclass);


--
-- TOC entry 2344 (class 2604 OID 82662)
-- Dependencies: 1736 1733
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_file ALTER COLUMN id SET DEFAULT nextval('cs_file_id_seq'::regclass);


--
-- TOC entry 2345 (class 2604 OID 82663)
-- Dependencies: 1735 1734
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_file_blob ALTER COLUMN id SET DEFAULT nextval('cs_file_blob_id_seq'::regclass);


--
-- TOC entry 2346 (class 2604 OID 82664)
-- Dependencies: 1738 1737
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_file_permission ALTER COLUMN id SET DEFAULT nextval('cs_file_permission_id_seq'::regclass);


--
-- TOC entry 2348 (class 2604 OID 82665)
-- Dependencies: 1742 1739
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_message ALTER COLUMN id SET DEFAULT nextval('cs_message_id_seq'::regclass);


--
-- TOC entry 2349 (class 2604 OID 82666)
-- Dependencies: 1741 1740
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_message_blob ALTER COLUMN id SET DEFAULT nextval('cs_message_blob_id_seq'::regclass);


--
-- TOC entry 2352 (class 2604 OID 82667)
-- Dependencies: 1744 1743
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_message_reciever ALTER COLUMN id SET DEFAULT nextval('cs_message_reciever_id_seq'::regclass);


--
-- TOC entry 2353 (class 2604 OID 82668)
-- Dependencies: 1746 1745
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_message_status ALTER COLUMN id SET DEFAULT nextval('cs_message_status_id_seq'::regclass);


--
-- TOC entry 2355 (class 2604 OID 82669)
-- Dependencies: 1748 1747
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_mime ALTER COLUMN id SET DEFAULT nextval('cs_mime_id_seq'::regclass);


--
-- TOC entry 2357 (class 2604 OID 82670)
-- Dependencies: 1750 1749
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_module ALTER COLUMN id SET DEFAULT nextval('cs_module_id_seq'::regclass);


--
-- TOC entry 2301 (class 2604 OID 82671)
-- Dependencies: 1751 1659
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_object_permission ALTER COLUMN id SET DEFAULT nextval('cs_object_permission_id_seq'::regclass);


--
-- TOC entry 2261 (class 2604 OID 82672)
-- Dependencies: 1752 1624
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_permission ALTER COLUMN id SET DEFAULT nextval('cs_permission_id_seq'::regclass);


--
-- TOC entry 2360 (class 2604 OID 82673)
-- Dependencies: 1754 1753
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_permission_list ALTER COLUMN id SET DEFAULT nextval('cs_permission_list_id_seq'::regclass);


--
-- TOC entry 2263 (class 2604 OID 82674)
-- Dependencies: 1755 1627
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_post ALTER COLUMN id SET DEFAULT nextval('cs_post_id_seq'::regclass);


--
-- TOC entry 2361 (class 2604 OID 82675)
-- Dependencies: 1757 1756
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_post_relation ALTER COLUMN id SET DEFAULT nextval('cs_post_relation_id_seq'::regclass);


--
-- TOC entry 2274 (class 2604 OID 82676)
-- Dependencies: 1768 1632
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_process ALTER COLUMN id SET DEFAULT nextval('cs_process_id_seq'::regclass);


--
-- TOC entry 2277 (class 2604 OID 82677)
-- Dependencies: 1760 1633
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_process_action ALTER COLUMN id SET DEFAULT nextval('cs_process_action_id_seq'::regclass);


--
-- TOC entry 2362 (class 2604 OID 82678)
-- Dependencies: 1759 1758
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_process_action_child ALTER COLUMN id SET DEFAULT nextval('cs_process_action_child_id_seq'::regclass);


--
-- TOC entry 2372 (class 2604 OID 82679)
-- Dependencies: 1762 1761
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_process_action_property ALTER COLUMN id SET DEFAULT nextval('cs_process_action_property_id_seq'::regclass);


--
-- TOC entry 2373 (class 2604 OID 82680)
-- Dependencies: 1764 1763
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_process_action_transport ALTER COLUMN id SET DEFAULT nextval('cs_process_action_transport_id_seq'::regclass);


--
-- TOC entry 2279 (class 2604 OID 82681)
-- Dependencies: 1765 1634
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_process_current_action ALTER COLUMN id SET DEFAULT nextval('cs_process_current_action_id_seq'::regclass);


--
-- TOC entry 2374 (class 2604 OID 82682)
-- Dependencies: 1767 1766
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_process_current_action_performer ALTER COLUMN id SET DEFAULT nextval('cs_process_current_action_performer_id_seq'::regclass);


--
-- TOC entry 2375 (class 2604 OID 82683)
-- Dependencies: 1770 1769
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_process_info_property ALTER COLUMN id SET DEFAULT nextval('cs_process_info_property_id_seq'::regclass);


--
-- TOC entry 2280 (class 2604 OID 82684)
-- Dependencies: 1771 1635
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_process_instance ALTER COLUMN id SET DEFAULT nextval('cs_process_instance_id_seq'::regclass);


--
-- TOC entry 2313 (class 2604 OID 82685)
-- Dependencies: 1772 1671
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_process_property ALTER COLUMN id SET DEFAULT nextval('cs_process_property_id_seq'::regclass);


--
-- TOC entry 2376 (class 2604 OID 82686)
-- Dependencies: 1774 1773
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_process_property_value ALTER COLUMN id SET DEFAULT nextval('cs_process_property_value_id_seq'::regclass);


--
-- TOC entry 2377 (class 2604 OID 82687)
-- Dependencies: 1776 1775
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_process_role ALTER COLUMN id SET DEFAULT nextval('cs_process_role_id_seq'::regclass);


--
-- TOC entry 2378 (class 2604 OID 82688)
-- Dependencies: 1778 1777
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_process_transition ALTER COLUMN id SET DEFAULT nextval('cs_process_transition_id_seq'::regclass);


--
-- TOC entry 2379 (class 2604 OID 82689)
-- Dependencies: 1780 1779
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_process_transport ALTER COLUMN id SET DEFAULT nextval('cs_process_transport_id_seq'::regclass);


--
-- TOC entry 2283 (class 2604 OID 82690)
-- Dependencies: 1781 1636
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_project ALTER COLUMN id SET DEFAULT nextval('cs_project_id_seq'::regclass);


--
-- TOC entry 2284 (class 2604 OID 82691)
-- Dependencies: 1782 1637
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_project_instance ALTER COLUMN id SET DEFAULT nextval('cs_project_instance_id_seq'::regclass);


--
-- TOC entry 2380 (class 2604 OID 82692)
-- Dependencies: 1784 1783
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_project_process ALTER COLUMN id SET DEFAULT nextval('cs_project_process_id_seq'::regclass);


--
-- TOC entry 2285 (class 2604 OID 82693)
-- Dependencies: 1785 1638
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_project_process_instance ALTER COLUMN id SET DEFAULT nextval('cs_project_process_instance_id_seq'::regclass);


--
-- TOC entry 2383 (class 2604 OID 82694)
-- Dependencies: 1787 1786
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_project_property ALTER COLUMN id SET DEFAULT nextval('cs_project_property_id_seq'::regclass);


--
-- TOC entry 2384 (class 2604 OID 82695)
-- Dependencies: 1789 1788
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_project_property_value ALTER COLUMN id SET DEFAULT nextval('cs_project_property_value_id_seq'::regclass);


--
-- TOC entry 2385 (class 2604 OID 82696)
-- Dependencies: 1791 1790
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_project_role ALTER COLUMN id SET DEFAULT nextval('cs_project_role_id_seq'::regclass);


--
-- TOC entry 2314 (class 2604 OID 82697)
-- Dependencies: 1792 1672
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_property_type ALTER COLUMN id SET DEFAULT nextval('cs_property_type_id_seq'::regclass);


--
-- TOC entry 2386 (class 2604 OID 82698)
-- Dependencies: 1794 1793
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_property_value ALTER COLUMN id SET DEFAULT nextval('cs_property_value_id_seq'::regclass);


--
-- TOC entry 2387 (class 2604 OID 82699)
-- Dependencies: 1796 1795
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_public_blob ALTER COLUMN id SET DEFAULT nextval('cs_public_blob_id_seq'::regclass);


--
-- TOC entry 2391 (class 2604 OID 82700)
-- Dependencies: 1798 1797
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_public_document ALTER COLUMN id SET DEFAULT nextval('cs_public_document_id_seq'::regclass);


--
-- TOC entry 2392 (class 2604 OID 82701)
-- Dependencies: 1800 1799
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_public_file ALTER COLUMN id SET DEFAULT nextval('cs_public_file_id_seq'::regclass);


--
-- TOC entry 2394 (class 2604 OID 82702)
-- Dependencies: 1802 1801
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_public_topic ALTER COLUMN id SET DEFAULT nextval('cs_public_topic_id_seq'::regclass);


--
-- TOC entry 2396 (class 2604 OID 82703)
-- Dependencies: 1804 1803
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_responser ALTER COLUMN id SET DEFAULT nextval('cs_responser_id_seq'::regclass);


--
-- TOC entry 2397 (class 2604 OID 82704)
-- Dependencies: 1806 1805
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_role ALTER COLUMN id SET DEFAULT nextval('cs_role_id_seq'::regclass);


--
-- TOC entry 2315 (class 2604 OID 82705)
-- Dependencies: 1807 1673
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_sign ALTER COLUMN id SET DEFAULT nextval('cs_sign_id_seq'::regclass);


--
-- TOC entry 2286 (class 2604 OID 82706)
-- Dependencies: 1808 1639
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_status ALTER COLUMN id SET DEFAULT nextval('cs_status_id_seq'::regclass);


--
-- TOC entry 2303 (class 2604 OID 82707)
-- Dependencies: 1809 1662
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE cs_transport ALTER COLUMN id SET DEFAULT nextval('cs_transport_id_seq'::regclass);


--
-- TOC entry 2398 (class 2604 OID 82708)
-- Dependencies: 1819 1818
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE pbb_bans ALTER COLUMN id SET DEFAULT nextval('pbb_bans_id_seq'::regclass);


--
-- TOC entry 2401 (class 2604 OID 82709)
-- Dependencies: 1821 1820
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE pbb_categories ALTER COLUMN id SET DEFAULT nextval('pbb_categories_id_seq'::regclass);


--
-- TOC entry 2404 (class 2604 OID 82710)
-- Dependencies: 1823 1822
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE pbb_censoring ALTER COLUMN id SET DEFAULT nextval('pbb_censoring_id_seq'::regclass);


--
-- TOC entry 2417 (class 2604 OID 82711)
-- Dependencies: 1827 1826
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE pbb_forums ALTER COLUMN id SET DEFAULT nextval('pbb_forums_id_seq'::regclass);


--
-- TOC entry 2432 (class 2604 OID 82712)
-- Dependencies: 1829 1828
-- Name: g_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE pbb_groups ALTER COLUMN g_id SET DEFAULT nextval('pbb_groups_g_id_seq'::regclass);


--
-- TOC entry 2442 (class 2604 OID 82713)
-- Dependencies: 1832 1831
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE pbb_posts ALTER COLUMN id SET DEFAULT nextval('pbb_posts_id_seq'::regclass);


--
-- TOC entry 2445 (class 2604 OID 82714)
-- Dependencies: 1834 1833
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE pbb_ranks ALTER COLUMN id SET DEFAULT nextval('pbb_ranks_id_seq'::regclass);


--
-- TOC entry 2451 (class 2604 OID 82715)
-- Dependencies: 1836 1835
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE pbb_reports ALTER COLUMN id SET DEFAULT nextval('pbb_reports_id_seq'::regclass);


--
-- TOC entry 2458 (class 2604 OID 82716)
-- Dependencies: 1840 1839
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE pbb_search_words ALTER COLUMN id SET DEFAULT nextval('pbb_search_words_id_seq'::regclass);


--
-- TOC entry 2471 (class 2604 OID 82717)
-- Dependencies: 1843 1842
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE pbb_topics ALTER COLUMN id SET DEFAULT nextval('pbb_topics_id_seq'::regclass);


--
-- TOC entry 2492 (class 2604 OID 82718)
-- Dependencies: 1845 1844
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE pbb_users ALTER COLUMN id SET DEFAULT nextval('pbb_users_id_seq'::regclass);


--
-- TOC entry 2649 (class 2606 OID 83184)
-- Dependencies: 1683 1683
-- Name: cs_account_divisions_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_account_division
    ADD CONSTRAINT cs_account_divisions_id PRIMARY KEY (id);


--
-- TOC entry 2505 (class 2606 OID 83186)
-- Dependencies: 1626 1626
-- Name: cs_account_post_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_account_post
    ADD CONSTRAINT cs_account_post_id PRIMARY KEY (id);


--
-- TOC entry 2512 (class 2606 OID 83188)
-- Dependencies: 1630 1630
-- Name: cs_account_today_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_account_today
    ADD CONSTRAINT cs_account_today_id PRIMARY KEY (id);


--
-- TOC entry 2494 (class 2606 OID 83190)
-- Dependencies: 1621 1621
-- Name: cs_accounts_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_account
    ADD CONSTRAINT cs_accounts_id PRIMARY KEY (id);


--
-- TOC entry 2518 (class 2606 OID 83192)
-- Dependencies: 1631 1631
-- Name: cs_action_types_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_action_type
    ADD CONSTRAINT cs_action_types_id PRIMARY KEY (id);


--
-- TOC entry 2653 (class 2606 OID 83194)
-- Dependencies: 1689 1689
-- Name: cs_blob_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_blob
    ADD CONSTRAINT cs_blob_id PRIMARY KEY (id);


--
-- TOC entry 2593 (class 2606 OID 83196)
-- Dependencies: 1661 1661
-- Name: cs_calendar_event_alarm_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_calendar_event_alarm
    ADD CONSTRAINT cs_calendar_event_alarm_id PRIMARY KEY (id);


--
-- TOC entry 2656 (class 2606 OID 83198)
-- Dependencies: 1692 1692
-- Name: cs_calendar_event_blob_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_calendar_event_blob
    ADD CONSTRAINT cs_calendar_event_blob_id PRIMARY KEY (id);


--
-- TOC entry 2562 (class 2606 OID 83200)
-- Dependencies: 1649 1649
-- Name: cs_calendar_event_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_calendar_event
    ADD CONSTRAINT cs_calendar_event_id PRIMARY KEY (id);


--
-- TOC entry 2568 (class 2606 OID 83202)
-- Dependencies: 1650 1650
-- Name: cs_calendar_event_period_detail_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_calendar_event_period_detail
    ADD CONSTRAINT cs_calendar_event_period_detail_id PRIMARY KEY (id);


--
-- TOC entry 2575 (class 2606 OID 83204)
-- Dependencies: 1654 1654
-- Name: cs_calendar_event_period_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_calendar_event_period
    ADD CONSTRAINT cs_calendar_event_period_id PRIMARY KEY (id);


--
-- TOC entry 2585 (class 2606 OID 83206)
-- Dependencies: 1658 1658
-- Name: cs_calendar_event_reciever_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_calendar_event_reciever
    ADD CONSTRAINT cs_calendar_event_reciever_id PRIMARY KEY (id);


--
-- TOC entry 2559 (class 2606 OID 83208)
-- Dependencies: 1648 1648
-- Name: cs_calendar_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_calendar
    ADD CONSTRAINT cs_calendar_id PRIMARY KEY (id);


--
-- TOC entry 2599 (class 2606 OID 83210)
-- Dependencies: 1665 1665
-- Name: cs_calendar_permission_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_calendar_permission
    ADD CONSTRAINT cs_calendar_permission_id PRIMARY KEY (id);


--
-- TOC entry 2498 (class 2606 OID 83212)
-- Dependencies: 1622 1622
-- Name: cs_cellop_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_cellop
    ADD CONSTRAINT cs_cellop_id PRIMARY KEY (id);


--
-- TOC entry 2604 (class 2606 OID 83214)
-- Dependencies: 1667 1667
-- Name: cs_chrono_action_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_chrono_action
    ADD CONSTRAINT cs_chrono_action_id PRIMARY KEY (id);


--
-- TOC entry 2659 (class 2606 OID 83216)
-- Dependencies: 1702 1702
-- Name: cs_chrono_blob_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_chrono_blob
    ADD CONSTRAINT cs_chrono_blob_id PRIMARY KEY (id);


--
-- TOC entry 2631 (class 2606 OID 83218)
-- Dependencies: 1675 1675
-- Name: cs_chrono_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_chrono
    ADD CONSTRAINT cs_chrono_id PRIMARY KEY (id);


--
-- TOC entry 2612 (class 2606 OID 83220)
-- Dependencies: 1669 1669
-- Name: cs_chrono_property_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_chrono_property
    ADD CONSTRAINT cs_chrono_property_id PRIMARY KEY (id);


--
-- TOC entry 2619 (class 2606 OID 83222)
-- Dependencies: 1670 1670
-- Name: cs_chrono_value_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_chrono_value
    ADD CONSTRAINT cs_chrono_value_id PRIMARY KEY (id);


--
-- TOC entry 2662 (class 2606 OID 83224)
-- Dependencies: 1707 1707
-- Name: cs_constants_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_constants
    ADD CONSTRAINT cs_constants_id PRIMARY KEY (id);


--
-- TOC entry 2637 (class 2606 OID 83226)
-- Dependencies: 1677 1677
-- Name: cs_contact_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_contact
    ADD CONSTRAINT cs_contact_id PRIMARY KEY (id);


--
-- TOC entry 2640 (class 2606 OID 83228)
-- Dependencies: 1678 1678
-- Name: cs_contact_list_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_contact_list
    ADD CONSTRAINT cs_contact_list_id PRIMARY KEY (id);


--
-- TOC entry 2644 (class 2606 OID 83230)
-- Dependencies: 1680 1680
-- Name: cs_contact_permission_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_contact_permission
    ADD CONSTRAINT cs_contact_permission_id PRIMARY KEY (id);


--
-- TOC entry 2664 (class 2606 OID 83232)
-- Dependencies: 1712 1712
-- Name: cs_custom_setting_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_custom_setting
    ADD CONSTRAINT cs_custom_setting_id PRIMARY KEY (id);


--
-- TOC entry 2668 (class 2606 OID 83234)
-- Dependencies: 1714 1714
-- Name: cs_delegate_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_delegate
    ADD CONSTRAINT cs_delegate_id PRIMARY KEY (id);


--
-- TOC entry 2674 (class 2606 OID 83236)
-- Dependencies: 1717 1717
-- Name: cs_directory_blob_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_directory_blob
    ADD CONSTRAINT cs_directory_blob_id PRIMARY KEY (id);


--
-- TOC entry 2677 (class 2606 OID 83238)
-- Dependencies: 1719 1719
-- Name: cs_directory_field_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_directory_field
    ADD CONSTRAINT cs_directory_field_id PRIMARY KEY (id);


--
-- TOC entry 2672 (class 2606 OID 83240)
-- Dependencies: 1716 1716
-- Name: cs_directory_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_directory
    ADD CONSTRAINT cs_directory_id PRIMARY KEY (id);


--
-- TOC entry 2681 (class 2606 OID 83242)
-- Dependencies: 1722 1722
-- Name: cs_directory_record_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_directory_record
    ADD CONSTRAINT cs_directory_record_id PRIMARY KEY (id);


--
-- TOC entry 2684 (class 2606 OID 83244)
-- Dependencies: 1724 1724
-- Name: cs_directory_value_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_directory_value
    ADD CONSTRAINT cs_directory_value_id PRIMARY KEY (id);


--
-- TOC entry 2500 (class 2606 OID 83246)
-- Dependencies: 1623 1623
-- Name: cs_divisions_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_division
    ADD CONSTRAINT cs_divisions_id PRIMARY KEY (id);


--
-- TOC entry 2688 (class 2606 OID 83248)
-- Dependencies: 1727 1727
-- Name: cs_event_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_event
    ADD CONSTRAINT cs_event_id PRIMARY KEY (id);


--
-- TOC entry 2582 (class 2606 OID 83250)
-- Dependencies: 1656 1656
-- Name: cs_event_period_condition_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_event_period_condition
    ADD CONSTRAINT cs_event_period_condition_id PRIMARY KEY (id);


--
-- TOC entry 2580 (class 2606 OID 83252)
-- Dependencies: 1655 1655
-- Name: cs_event_period_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_event_period
    ADD CONSTRAINT cs_event_period_id PRIMARY KEY (id);


--
-- TOC entry 2571 (class 2606 OID 83254)
-- Dependencies: 1651 1651
-- Name: cs_event_priority_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_event_priority
    ADD CONSTRAINT cs_event_priority_id PRIMARY KEY (id);


--
-- TOC entry 2573 (class 2606 OID 83256)
-- Dependencies: 1652 1652
-- Name: cs_event_status_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_event_status
    ADD CONSTRAINT cs_event_status_id PRIMARY KEY (id);


--
-- TOC entry 2695 (class 2606 OID 83258)
-- Dependencies: 1734 1734
-- Name: cs_file_blob_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_file_blob
    ADD CONSTRAINT cs_file_blob_id PRIMARY KEY (id);


--
-- TOC entry 2690 (class 2606 OID 83260)
-- Dependencies: 1733 1733
-- Name: cs_file_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_file
    ADD CONSTRAINT cs_file_id PRIMARY KEY (id);


--
-- TOC entry 2698 (class 2606 OID 83262)
-- Dependencies: 1737 1737
-- Name: cs_file_permission_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_file_permission
    ADD CONSTRAINT cs_file_permission_id PRIMARY KEY (id);


--
-- TOC entry 2707 (class 2606 OID 83264)
-- Dependencies: 1740 1740
-- Name: cs_message_blob_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_message_blob
    ADD CONSTRAINT cs_message_blob_id PRIMARY KEY (id);


--
-- TOC entry 2703 (class 2606 OID 83266)
-- Dependencies: 1739 1739
-- Name: cs_message_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_message
    ADD CONSTRAINT cs_message_id PRIMARY KEY (id);


--
-- TOC entry 2710 (class 2606 OID 83268)
-- Dependencies: 1743 1743
-- Name: cs_message_reciever_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_message_reciever
    ADD CONSTRAINT cs_message_reciever_id PRIMARY KEY (id);


--
-- TOC entry 2715 (class 2606 OID 83270)
-- Dependencies: 1745 1745
-- Name: cs_message_status_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_message_status
    ADD CONSTRAINT cs_message_status_id PRIMARY KEY (id);


--
-- TOC entry 2717 (class 2606 OID 83272)
-- Dependencies: 1747 1747
-- Name: cs_mime_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_mime
    ADD CONSTRAINT cs_mime_id PRIMARY KEY (id);


--
-- TOC entry 2719 (class 2606 OID 83274)
-- Dependencies: 1749 1749
-- Name: cs_modules_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_module
    ADD CONSTRAINT cs_modules_id PRIMARY KEY (id);


--
-- TOC entry 2591 (class 2606 OID 83276)
-- Dependencies: 1659 1659
-- Name: cs_object_permission_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_object_permission
    ADD CONSTRAINT cs_object_permission_id PRIMARY KEY (id);


--
-- TOC entry 2503 (class 2606 OID 83278)
-- Dependencies: 1624 1624
-- Name: cs_permissions_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_permission
    ADD CONSTRAINT cs_permissions_id PRIMARY KEY (id);


--
-- TOC entry 2721 (class 2606 OID 83280)
-- Dependencies: 1753 1753
-- Name: cs_permissions_list_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_permission_list
    ADD CONSTRAINT cs_permissions_list_pkey PRIMARY KEY (id);


--
-- TOC entry 2510 (class 2606 OID 83282)
-- Dependencies: 1627 1627
-- Name: cs_post_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_post
    ADD CONSTRAINT cs_post_id PRIMARY KEY (id);


--
-- TOC entry 2725 (class 2606 OID 83284)
-- Dependencies: 1756 1756
-- Name: cs_post_relation_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_post_relation
    ADD CONSTRAINT cs_post_relation_id PRIMARY KEY (id);


--
-- TOC entry 2730 (class 2606 OID 83286)
-- Dependencies: 1758 1758
-- Name: cs_process_action_child_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_process_action_child
    ADD CONSTRAINT cs_process_action_child_id PRIMARY KEY (id);


--
-- TOC entry 2734 (class 2606 OID 83288)
-- Dependencies: 1761 1761
-- Name: cs_process_action_property_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_process_action_property
    ADD CONSTRAINT cs_process_action_property_id PRIMARY KEY (id);


--
-- TOC entry 2738 (class 2606 OID 83290)
-- Dependencies: 1763 1763
-- Name: cs_process_action_transport_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_process_action_transport
    ADD CONSTRAINT cs_process_action_transport_id PRIMARY KEY (id);


--
-- TOC entry 2525 (class 2606 OID 83292)
-- Dependencies: 1633 1633
-- Name: cs_process_actions_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_process_action
    ADD CONSTRAINT cs_process_actions_id PRIMARY KEY (id);


--
-- TOC entry 2743 (class 2606 OID 83294)
-- Dependencies: 1766 1766
-- Name: cs_process_current_action_performer_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_process_current_action_performer
    ADD CONSTRAINT cs_process_current_action_performer_id PRIMARY KEY (id);


--
-- TOC entry 2532 (class 2606 OID 83296)
-- Dependencies: 1634 1634
-- Name: cs_process_current_actions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_process_current_action
    ADD CONSTRAINT cs_process_current_actions_pkey PRIMARY KEY (id);


--
-- TOC entry 2749 (class 2606 OID 83298)
-- Dependencies: 1769 1769
-- Name: cs_process_info_property_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_process_info_property
    ADD CONSTRAINT cs_process_info_property_id PRIMARY KEY (id);


--
-- TOC entry 2539 (class 2606 OID 83300)
-- Dependencies: 1635 1635
-- Name: cs_process_instances_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_process_instance
    ADD CONSTRAINT cs_process_instances_id PRIMARY KEY (id);


--
-- TOC entry 2621 (class 2606 OID 83302)
-- Dependencies: 1671 1671
-- Name: cs_process_properties_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_process_property
    ADD CONSTRAINT cs_process_properties_id PRIMARY KEY (id);


--
-- TOC entry 2758 (class 2606 OID 83304)
-- Dependencies: 1775 1775
-- Name: cs_process_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_process_role
    ADD CONSTRAINT cs_process_roles_pkey PRIMARY KEY (id);


--
-- TOC entry 2763 (class 2606 OID 83306)
-- Dependencies: 1777 1777
-- Name: cs_process_transitions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_process_transition
    ADD CONSTRAINT cs_process_transitions_pkey PRIMARY KEY (id);


--
-- TOC entry 2768 (class 2606 OID 83308)
-- Dependencies: 1779 1779
-- Name: cs_process_transport_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_process_transport
    ADD CONSTRAINT cs_process_transport_id PRIMARY KEY (id);


--
-- TOC entry 2753 (class 2606 OID 83310)
-- Dependencies: 1773 1773
-- Name: cs_process_values_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_process_property_value
    ADD CONSTRAINT cs_process_values_pkey PRIMARY KEY (id);


--
-- TOC entry 2520 (class 2606 OID 83312)
-- Dependencies: 1632 1632
-- Name: cs_processes_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_process
    ADD CONSTRAINT cs_processes_id PRIMARY KEY (id);


--
-- TOC entry 2548 (class 2606 OID 83314)
-- Dependencies: 1637 1637
-- Name: cs_project_instances_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_project_instance
    ADD CONSTRAINT cs_project_instances_id PRIMARY KEY (id);


--
-- TOC entry 2553 (class 2606 OID 83316)
-- Dependencies: 1638 1638
-- Name: cs_project_process_instance_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_project_process_instance
    ADD CONSTRAINT cs_project_process_instance_id PRIMARY KEY (id);


--
-- TOC entry 2773 (class 2606 OID 83318)
-- Dependencies: 1783 1783
-- Name: cs_project_processes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_project_process
    ADD CONSTRAINT cs_project_processes_pkey PRIMARY KEY (id);


--
-- TOC entry 2777 (class 2606 OID 83320)
-- Dependencies: 1786 1786
-- Name: cs_project_properties_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_project_property
    ADD CONSTRAINT cs_project_properties_id PRIMARY KEY (id);


--
-- TOC entry 2787 (class 2606 OID 83322)
-- Dependencies: 1790 1790
-- Name: cs_project_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_project_role
    ADD CONSTRAINT cs_project_roles_pkey PRIMARY KEY (id);


--
-- TOC entry 2782 (class 2606 OID 83324)
-- Dependencies: 1788 1788
-- Name: cs_project_values_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_project_property_value
    ADD CONSTRAINT cs_project_values_pkey PRIMARY KEY (id);


--
-- TOC entry 2545 (class 2606 OID 83326)
-- Dependencies: 1636 1636
-- Name: cs_projects_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_project
    ADD CONSTRAINT cs_projects_id PRIMARY KEY (id);


--
-- TOC entry 2627 (class 2606 OID 83328)
-- Dependencies: 1672 1672
-- Name: cs_property_types_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_property_type
    ADD CONSTRAINT cs_property_types_id PRIMARY KEY (id);


--
-- TOC entry 2792 (class 2606 OID 83330)
-- Dependencies: 1793 1793
-- Name: cs_property_values_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_property_value
    ADD CONSTRAINT cs_property_values_id PRIMARY KEY (id);


--
-- TOC entry 2794 (class 2606 OID 83332)
-- Dependencies: 1795 1795
-- Name: cs_public_blob_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_public_blob
    ADD CONSTRAINT cs_public_blob_id PRIMARY KEY (id);


--
-- TOC entry 2797 (class 2606 OID 83334)
-- Dependencies: 1797 1797
-- Name: cs_public_document_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_public_document
    ADD CONSTRAINT cs_public_document_id PRIMARY KEY (id);


--
-- TOC entry 2803 (class 2606 OID 83336)
-- Dependencies: 1799 1799
-- Name: cs_public_file_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_public_file
    ADD CONSTRAINT cs_public_file_id PRIMARY KEY (id);


--
-- TOC entry 2805 (class 2606 OID 83338)
-- Dependencies: 1801 1801
-- Name: cs_public_topic_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_public_topic
    ADD CONSTRAINT cs_public_topic_id PRIMARY KEY (id);


--
-- TOC entry 2807 (class 2606 OID 83340)
-- Dependencies: 1803 1803
-- Name: cs_responser_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_responser
    ADD CONSTRAINT cs_responser_id PRIMARY KEY (id);


--
-- TOC entry 2810 (class 2606 OID 83342)
-- Dependencies: 1805 1805
-- Name: cs_role_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_role
    ADD CONSTRAINT cs_role_id PRIMARY KEY (id);


--
-- TOC entry 2629 (class 2606 OID 83344)
-- Dependencies: 1673 1673
-- Name: cs_signs_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_sign
    ADD CONSTRAINT cs_signs_id PRIMARY KEY (id);


--
-- TOC entry 2557 (class 2606 OID 83346)
-- Dependencies: 1639 1639
-- Name: cs_statuses_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_status
    ADD CONSTRAINT cs_statuses_id PRIMARY KEY (id);


--
-- TOC entry 2597 (class 2606 OID 83348)
-- Dependencies: 1662 1662
-- Name: cs_transport_id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cs_transport
    ADD CONSTRAINT cs_transport_id PRIMARY KEY (id);


--
-- TOC entry 2812 (class 2606 OID 83350)
-- Dependencies: 1818 1818
-- Name: pbb_bans_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY pbb_bans
    ADD CONSTRAINT pbb_bans_pkey PRIMARY KEY (id);


--
-- TOC entry 2814 (class 2606 OID 83352)
-- Dependencies: 1820 1820
-- Name: pbb_categories_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY pbb_categories
    ADD CONSTRAINT pbb_categories_pkey PRIMARY KEY (id);


--
-- TOC entry 2816 (class 2606 OID 83354)
-- Dependencies: 1822 1822
-- Name: pbb_censoring_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY pbb_censoring
    ADD CONSTRAINT pbb_censoring_pkey PRIMARY KEY (id);


--
-- TOC entry 2818 (class 2606 OID 83356)
-- Dependencies: 1824 1824
-- Name: pbb_config_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY pbb_config
    ADD CONSTRAINT pbb_config_pkey PRIMARY KEY (conf_name);


--
-- TOC entry 2820 (class 2606 OID 83358)
-- Dependencies: 1825 1825 1825
-- Name: pbb_forum_perms_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY pbb_forum_perms
    ADD CONSTRAINT pbb_forum_perms_pkey PRIMARY KEY (group_id, forum_id);


--
-- TOC entry 2822 (class 2606 OID 83360)
-- Dependencies: 1826 1826
-- Name: pbb_forums_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY pbb_forums
    ADD CONSTRAINT pbb_forums_pkey PRIMARY KEY (id);


--
-- TOC entry 2824 (class 2606 OID 83362)
-- Dependencies: 1828 1828
-- Name: pbb_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY pbb_groups
    ADD CONSTRAINT pbb_groups_pkey PRIMARY KEY (g_id);


--
-- TOC entry 2828 (class 2606 OID 83364)
-- Dependencies: 1831 1831
-- Name: pbb_posts_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY pbb_posts
    ADD CONSTRAINT pbb_posts_pkey PRIMARY KEY (id);


--
-- TOC entry 2831 (class 2606 OID 83366)
-- Dependencies: 1833 1833
-- Name: pbb_ranks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY pbb_ranks
    ADD CONSTRAINT pbb_ranks_pkey PRIMARY KEY (id);


--
-- TOC entry 2833 (class 2606 OID 83368)
-- Dependencies: 1835 1835
-- Name: pbb_reports_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY pbb_reports
    ADD CONSTRAINT pbb_reports_pkey PRIMARY KEY (id);


--
-- TOC entry 2837 (class 2606 OID 83370)
-- Dependencies: 1837 1837
-- Name: pbb_search_cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY pbb_search_cache
    ADD CONSTRAINT pbb_search_cache_pkey PRIMARY KEY (id);


--
-- TOC entry 2842 (class 2606 OID 83372)
-- Dependencies: 1839 1839
-- Name: pbb_search_words_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY pbb_search_words
    ADD CONSTRAINT pbb_search_words_pkey PRIMARY KEY (word);


--
-- TOC entry 2844 (class 2606 OID 83374)
-- Dependencies: 1841 1841 1841
-- Name: pbb_subscriptions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY pbb_subscriptions
    ADD CONSTRAINT pbb_subscriptions_pkey PRIMARY KEY (user_id, topic_id);


--
-- TOC entry 2848 (class 2606 OID 83376)
-- Dependencies: 1842 1842
-- Name: pbb_topics_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY pbb_topics
    ADD CONSTRAINT pbb_topics_pkey PRIMARY KEY (id);


--
-- TOC entry 2850 (class 2606 OID 83378)
-- Dependencies: 1844 1844
-- Name: pbb_users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY pbb_users
    ADD CONSTRAINT pbb_users_pkey PRIMARY KEY (id);


--
-- TOC entry 2523 (class 1259 OID 83379)
-- Dependencies: 1633
-- Name: cs_process_action_npp; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX cs_process_action_npp ON cs_process_action USING btree (npp);


--
-- TOC entry 2650 (class 1259 OID 83380)
-- Dependencies: 1683
-- Name: fki_cs_account_divisions_account_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_account_divisions_account_id ON cs_account_division USING btree (account_id);


--
-- TOC entry 2651 (class 1259 OID 83381)
-- Dependencies: 1683
-- Name: fki_cs_account_divisions_division_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_account_divisions_division_id ON cs_account_division USING btree (division_id);


--
-- TOC entry 2506 (class 1259 OID 83382)
-- Dependencies: 1626
-- Name: fki_cs_account_post_account_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_account_post_account_id ON cs_account_post USING btree (account_id);


--
-- TOC entry 2507 (class 1259 OID 83383)
-- Dependencies: 1626
-- Name: fki_cs_account_post_division_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_account_post_division_id ON cs_account_post USING btree (division_id);


--
-- TOC entry 2508 (class 1259 OID 83384)
-- Dependencies: 1626
-- Name: fki_cs_account_post_post_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_account_post_post_id ON cs_account_post USING btree (post_id);


--
-- TOC entry 2513 (class 1259 OID 83385)
-- Dependencies: 1630
-- Name: fki_cs_account_today_account_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_account_today_account_id ON cs_account_today USING btree (account_id);


--
-- TOC entry 2514 (class 1259 OID 83386)
-- Dependencies: 1630
-- Name: fki_cs_account_today_action_instance_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_account_today_action_instance_id ON cs_account_today USING btree (action_instance_id);


--
-- TOC entry 2515 (class 1259 OID 83387)
-- Dependencies: 1630
-- Name: fki_cs_account_today_process_instance_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_account_today_process_instance_id ON cs_account_today USING btree (process_instance_id);


--
-- TOC entry 2516 (class 1259 OID 83388)
-- Dependencies: 1630
-- Name: fki_cs_account_today_status_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_account_today_status_id ON cs_account_today USING btree (status_id);


--
-- TOC entry 2495 (class 1259 OID 83389)
-- Dependencies: 1621
-- Name: fki_cs_accounts_parent_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_accounts_parent_id ON cs_account USING btree (parent_id);


--
-- TOC entry 2496 (class 1259 OID 83390)
-- Dependencies: 1621
-- Name: fki_cs_accounts_permission_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_accounts_permission_id ON cs_account USING btree (permission_id);


--
-- TOC entry 2654 (class 1259 OID 83391)
-- Dependencies: 1689
-- Name: fki_cs_blob_value_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_blob_value_id ON cs_blob USING btree (value_id);


--
-- TOC entry 2594 (class 1259 OID 83392)
-- Dependencies: 1661
-- Name: fki_cs_calendar_event_alarm_event_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_calendar_event_alarm_event_id ON cs_calendar_event_alarm USING btree (event_id);


--
-- TOC entry 2595 (class 1259 OID 83393)
-- Dependencies: 1661
-- Name: fki_cs_calendar_event_alarm_transport_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_calendar_event_alarm_transport_id ON cs_calendar_event_alarm USING btree (transport_id);


--
-- TOC entry 2563 (class 1259 OID 83394)
-- Dependencies: 1649
-- Name: fki_cs_calendar_event_author_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_calendar_event_author_id ON cs_calendar_event USING btree (author_id);


--
-- TOC entry 2657 (class 1259 OID 83395)
-- Dependencies: 1692
-- Name: fki_cs_calendar_event_blob_event_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_calendar_event_blob_event_id ON cs_calendar_event_blob USING btree (event_id);


--
-- TOC entry 2564 (class 1259 OID 83396)
-- Dependencies: 1649
-- Name: fki_cs_calendar_event_calendar_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_calendar_event_calendar_id ON cs_calendar_event USING btree (calendar_id);


--
-- TOC entry 2576 (class 1259 OID 83397)
-- Dependencies: 1654
-- Name: fki_cs_calendar_event_period_condition_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_calendar_event_period_condition_id ON cs_calendar_event_period USING btree (condition_id);


--
-- TOC entry 2569 (class 1259 OID 83398)
-- Dependencies: 1650
-- Name: fki_cs_calendar_event_period_detail_event_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_calendar_event_period_detail_event_id ON cs_calendar_event_period_detail USING btree (event_id);


--
-- TOC entry 2577 (class 1259 OID 83399)
-- Dependencies: 1654
-- Name: fki_cs_calendar_event_period_event_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_calendar_event_period_event_id ON cs_calendar_event_period USING btree (event_id);


--
-- TOC entry 2578 (class 1259 OID 83400)
-- Dependencies: 1654
-- Name: fki_cs_calendar_event_period_period_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_calendar_event_period_period_id ON cs_calendar_event_period USING btree (period_id);


--
-- TOC entry 2565 (class 1259 OID 83401)
-- Dependencies: 1649
-- Name: fki_cs_calendar_event_priority_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_calendar_event_priority_id ON cs_calendar_event USING btree (priority_id);


--
-- TOC entry 2586 (class 1259 OID 83402)
-- Dependencies: 1658
-- Name: fki_cs_calendar_event_reciever_account_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_calendar_event_reciever_account_id ON cs_calendar_event_reciever USING btree (account_id);


--
-- TOC entry 2587 (class 1259 OID 83403)
-- Dependencies: 1658
-- Name: fki_cs_calendar_event_reciever_event_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_calendar_event_reciever_event_id ON cs_calendar_event_reciever USING btree (event_id);


--
-- TOC entry 2588 (class 1259 OID 83404)
-- Dependencies: 1658
-- Name: fki_cs_calendar_event_reciever_permission_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_calendar_event_reciever_permission_id ON cs_calendar_event_reciever USING btree (permission_id);


--
-- TOC entry 2589 (class 1259 OID 83405)
-- Dependencies: 1658
-- Name: fki_cs_calendar_event_reciever_status_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_calendar_event_reciever_status_id ON cs_calendar_event_reciever USING btree (status_id);


--
-- TOC entry 2566 (class 1259 OID 83406)
-- Dependencies: 1649
-- Name: fki_cs_calendar_event_status_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_calendar_event_status_id ON cs_calendar_event USING btree (status_id);


--
-- TOC entry 2560 (class 1259 OID 83407)
-- Dependencies: 1648
-- Name: fki_cs_calendar_owner_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_calendar_owner_id ON cs_calendar USING btree (owner_id);


--
-- TOC entry 2600 (class 1259 OID 83408)
-- Dependencies: 1665
-- Name: fki_cs_calendar_permission_account_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_calendar_permission_account_id ON cs_calendar_permission USING btree (account_id);


--
-- TOC entry 2601 (class 1259 OID 83409)
-- Dependencies: 1665
-- Name: fki_cs_calendar_permission_calendar_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_calendar_permission_calendar_id ON cs_calendar_permission USING btree (calendar_id);


--
-- TOC entry 2602 (class 1259 OID 83410)
-- Dependencies: 1665
-- Name: fki_cs_calendar_permission_permission_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_calendar_permission_permission_id ON cs_calendar_permission USING btree (permission_id);


--
-- TOC entry 2632 (class 1259 OID 83411)
-- Dependencies: 1675
-- Name: fki_cs_chrono_account_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_chrono_account_id ON cs_chrono USING btree (account_id);


--
-- TOC entry 2605 (class 1259 OID 83412)
-- Dependencies: 1667
-- Name: fki_cs_chrono_action_action_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_chrono_action_action_id ON cs_chrono_action USING btree (action_id);


--
-- TOC entry 2606 (class 1259 OID 83413)
-- Dependencies: 1667
-- Name: fki_cs_chrono_action_action_instance_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_chrono_action_action_instance_id ON cs_chrono_action USING btree (action_instance_id);


--
-- TOC entry 2607 (class 1259 OID 83414)
-- Dependencies: 1667
-- Name: fki_cs_chrono_action_initiator_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_chrono_action_initiator_id ON cs_chrono_action USING btree (initiator_id);


--
-- TOC entry 2608 (class 1259 OID 83415)
-- Dependencies: 1667
-- Name: fki_cs_chrono_action_performer_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_chrono_action_performer_id ON cs_chrono_action USING btree (performer_id);


--
-- TOC entry 2609 (class 1259 OID 83416)
-- Dependencies: 1667
-- Name: fki_cs_chrono_action_process_instance_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_chrono_action_process_instance_id ON cs_chrono_action USING btree (process_instance_id);


--
-- TOC entry 2610 (class 1259 OID 83417)
-- Dependencies: 1667
-- Name: fki_cs_chrono_action_status_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_chrono_action_status_id ON cs_chrono_action USING btree (status_id);


--
-- TOC entry 2660 (class 1259 OID 83418)
-- Dependencies: 1702
-- Name: fki_cs_chrono_blob_value_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_chrono_blob_value_id ON cs_chrono_blob USING btree (value_id);


--
-- TOC entry 2633 (class 1259 OID 83419)
-- Dependencies: 1675
-- Name: fki_cs_chrono_from_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_chrono_from_id ON cs_chrono USING btree (from_action_id);


--
-- TOC entry 2634 (class 1259 OID 83420)
-- Dependencies: 1675
-- Name: fki_cs_chrono_instance_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_chrono_instance_id ON cs_chrono USING btree (instance_id);


--
-- TOC entry 2613 (class 1259 OID 83421)
-- Dependencies: 1669
-- Name: fki_cs_chrono_property_process_instance_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_chrono_property_process_instance_id ON cs_chrono_property USING btree (process_instance_id);


--
-- TOC entry 2614 (class 1259 OID 83422)
-- Dependencies: 1669
-- Name: fki_cs_chrono_property_property_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_chrono_property_property_id ON cs_chrono_property USING btree (property_id);


--
-- TOC entry 2615 (class 1259 OID 83423)
-- Dependencies: 1669
-- Name: fki_cs_chrono_property_property_instance_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_chrono_property_property_instance_id ON cs_chrono_property USING btree (property_instance_id);


--
-- TOC entry 2616 (class 1259 OID 83424)
-- Dependencies: 1669
-- Name: fki_cs_chrono_property_property_value_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_chrono_property_property_value_id ON cs_chrono_property USING btree (property_value_id);


--
-- TOC entry 2617 (class 1259 OID 83425)
-- Dependencies: 1669
-- Name: fki_cs_chrono_property_value_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_chrono_property_value_id ON cs_chrono_property USING btree (value_id);


--
-- TOC entry 2635 (class 1259 OID 83426)
-- Dependencies: 1675
-- Name: fki_cs_chrono_to_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_chrono_to_id ON cs_chrono USING btree (to_action_id);


--
-- TOC entry 2641 (class 1259 OID 83427)
-- Dependencies: 1678
-- Name: fki_cs_contact_list_account_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_contact_list_account_id ON cs_contact_list USING btree (account_id);


--
-- TOC entry 2642 (class 1259 OID 83428)
-- Dependencies: 1678
-- Name: fki_cs_contact_list_contact_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_contact_list_contact_id ON cs_contact_list USING btree (contact_id);


--
-- TOC entry 2638 (class 1259 OID 83429)
-- Dependencies: 1677
-- Name: fki_cs_contact_owner_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_contact_owner_id ON cs_contact USING btree (owner_id);


--
-- TOC entry 2645 (class 1259 OID 83430)
-- Dependencies: 1680
-- Name: fki_cs_contact_permission_account_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_contact_permission_account_id ON cs_contact_permission USING btree (account_id);


--
-- TOC entry 2646 (class 1259 OID 83431)
-- Dependencies: 1680
-- Name: fki_cs_contact_permission_contact_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_contact_permission_contact_id ON cs_contact_permission USING btree (contact_id);


--
-- TOC entry 2647 (class 1259 OID 83432)
-- Dependencies: 1680
-- Name: fki_cs_contact_permission_permission_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_contact_permission_permission_id ON cs_contact_permission USING btree (permission_id);


--
-- TOC entry 2533 (class 1259 OID 83433)
-- Dependencies: 1634
-- Name: fki_cs_current_actions_action_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_current_actions_action_id ON cs_process_current_action USING btree (action_id);


--
-- TOC entry 2534 (class 1259 OID 83434)
-- Dependencies: 1634
-- Name: fki_cs_current_actions_initiator_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_current_actions_initiator_id ON cs_process_current_action USING btree (initiator_id);


--
-- TOC entry 2535 (class 1259 OID 83435)
-- Dependencies: 1634
-- Name: fki_cs_current_actions_instance_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_current_actions_instance_id ON cs_process_current_action USING btree (instance_id);


--
-- TOC entry 2536 (class 1259 OID 83436)
-- Dependencies: 1634
-- Name: fki_cs_current_actions_performer_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_current_actions_performer_id ON cs_process_current_action USING btree (performer_id);


--
-- TOC entry 2537 (class 1259 OID 83437)
-- Dependencies: 1634
-- Name: fki_cs_current_actions_status_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_current_actions_status_id ON cs_process_current_action USING btree (status_id);


--
-- TOC entry 2665 (class 1259 OID 83438)
-- Dependencies: 1712
-- Name: fki_cs_custom_setting_account_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_custom_setting_account_id ON cs_custom_setting USING btree (account_id);


--
-- TOC entry 2666 (class 1259 OID 83439)
-- Dependencies: 1712
-- Name: fki_cs_custom_setting_module_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_custom_setting_module_id ON cs_custom_setting USING btree (module_id);


--
-- TOC entry 2669 (class 1259 OID 83440)
-- Dependencies: 1714
-- Name: fki_cs_delegate_account_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_delegate_account_id ON cs_delegate USING btree (account_id);


--
-- TOC entry 2670 (class 1259 OID 83441)
-- Dependencies: 1714
-- Name: fki_cs_delegate_delegate_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_delegate_delegate_id ON cs_delegate USING btree (delegate_id);


--
-- TOC entry 2675 (class 1259 OID 83442)
-- Dependencies: 1717
-- Name: fki_cs_directory_blob_value_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_directory_blob_value_id ON cs_directory_blob USING btree (value_id);


--
-- TOC entry 2678 (class 1259 OID 83443)
-- Dependencies: 1719
-- Name: fki_cs_directory_field_directory_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_directory_field_directory_id ON cs_directory_field USING btree (directory_id);


--
-- TOC entry 2679 (class 1259 OID 83444)
-- Dependencies: 1719
-- Name: fki_cs_directory_field_type_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_directory_field_type_id ON cs_directory_field USING btree (type_id);


--
-- TOC entry 2682 (class 1259 OID 83445)
-- Dependencies: 1722
-- Name: fki_cs_directory_record_directory_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_directory_record_directory_id ON cs_directory_record USING btree (directory_id);


--
-- TOC entry 2685 (class 1259 OID 83446)
-- Dependencies: 1724
-- Name: fki_cs_directory_value_field_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_directory_value_field_id ON cs_directory_value USING btree (field_id);


--
-- TOC entry 2686 (class 1259 OID 83447)
-- Dependencies: 1724
-- Name: fki_cs_directory_value_record_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_directory_value_record_id ON cs_directory_value USING btree (record_id);


--
-- TOC entry 2501 (class 1259 OID 83448)
-- Dependencies: 1623
-- Name: fki_cs_divisions_parent_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_divisions_parent_id ON cs_division USING btree (parent_id);


--
-- TOC entry 2583 (class 1259 OID 83449)
-- Dependencies: 1656
-- Name: fki_cs_event_period_condition_period_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_event_period_condition_period_id ON cs_event_period_condition USING btree (period_id);


--
-- TOC entry 2696 (class 1259 OID 83450)
-- Dependencies: 1734
-- Name: fki_cs_file_blob_file_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_file_blob_file_id ON cs_file_blob USING btree (file_id);


--
-- TOC entry 2691 (class 1259 OID 83451)
-- Dependencies: 1733
-- Name: fki_cs_file_owner_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_file_owner_id ON cs_file USING btree (owner_id);


--
-- TOC entry 2692 (class 1259 OID 83452)
-- Dependencies: 1733
-- Name: fki_cs_file_parent_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_file_parent_id ON cs_file USING btree (parent_id);


--
-- TOC entry 2699 (class 1259 OID 83453)
-- Dependencies: 1737
-- Name: fki_cs_file_permission_account_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_file_permission_account_id ON cs_file_permission USING btree (account_id);


--
-- TOC entry 2700 (class 1259 OID 83454)
-- Dependencies: 1737
-- Name: fki_cs_file_permission_file_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_file_permission_file_id ON cs_file_permission USING btree (file_id);


--
-- TOC entry 2701 (class 1259 OID 83455)
-- Dependencies: 1737
-- Name: fki_cs_file_permission_permission_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_file_permission_permission_id ON cs_file_permission USING btree (permission_id);


--
-- TOC entry 2693 (class 1259 OID 83456)
-- Dependencies: 1733
-- Name: fki_cs_file_updated_by; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_file_updated_by ON cs_file USING btree (updated_by);


--
-- TOC entry 2704 (class 1259 OID 83457)
-- Dependencies: 1739
-- Name: fki_cs_message_author_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_message_author_id ON cs_message USING btree (author_id);


--
-- TOC entry 2708 (class 1259 OID 83458)
-- Dependencies: 1740
-- Name: fki_cs_message_blob_message_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_message_blob_message_id ON cs_message_blob USING btree (message_id);


--
-- TOC entry 2711 (class 1259 OID 83459)
-- Dependencies: 1743
-- Name: fki_cs_message_reciever_message_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_message_reciever_message_id ON cs_message_reciever USING btree (message_id);


--
-- TOC entry 2712 (class 1259 OID 83460)
-- Dependencies: 1743
-- Name: fki_cs_message_reciever_reciever_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_message_reciever_reciever_id ON cs_message_reciever USING btree (reciever_id);


--
-- TOC entry 2713 (class 1259 OID 83461)
-- Dependencies: 1743
-- Name: fki_cs_message_reciever_status_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_message_reciever_status_id ON cs_message_reciever USING btree (status_id);


--
-- TOC entry 2705 (class 1259 OID 83462)
-- Dependencies: 1739
-- Name: fki_cs_message_status_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_message_status_id ON cs_message USING btree (status_id);


--
-- TOC entry 2722 (class 1259 OID 83463)
-- Dependencies: 1753
-- Name: fki_cs_permissions_list_module_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_permissions_list_module_id ON cs_permission_list USING btree (module_id);


--
-- TOC entry 2723 (class 1259 OID 83464)
-- Dependencies: 1753
-- Name: fki_cs_permissions_list_permission_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_permissions_list_permission_id ON cs_permission_list USING btree (permission_id);


--
-- TOC entry 2726 (class 1259 OID 83465)
-- Dependencies: 1756
-- Name: fki_cs_post_relation_division_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_post_relation_division_id ON cs_post_relation USING btree (division_id);


--
-- TOC entry 2727 (class 1259 OID 83466)
-- Dependencies: 1756
-- Name: fki_cs_post_relation_post_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_post_relation_post_id ON cs_post_relation USING btree (post_id);


--
-- TOC entry 2728 (class 1259 OID 83467)
-- Dependencies: 1756
-- Name: fki_cs_post_relation_relation_post_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_post_relation_relation_post_id ON cs_post_relation USING btree (relation_post_id);


--
-- TOC entry 2731 (class 1259 OID 83468)
-- Dependencies: 1758
-- Name: fki_cs_process_action_child_action_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_action_child_action_id ON cs_process_action_child USING btree (action_id);


--
-- TOC entry 2732 (class 1259 OID 83469)
-- Dependencies: 1758
-- Name: fki_cs_process_action_child_process_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_action_child_process_id ON cs_process_action_child USING btree (process_id);


--
-- TOC entry 2526 (class 1259 OID 83470)
-- Dependencies: 1633
-- Name: fki_cs_process_action_false_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_action_false_id ON cs_process_action USING btree (false_action_id);


--
-- TOC entry 2735 (class 1259 OID 83471)
-- Dependencies: 1761
-- Name: fki_cs_process_action_property_action_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_action_property_action_id ON cs_process_action_property USING btree (action_id);


--
-- TOC entry 2736 (class 1259 OID 83472)
-- Dependencies: 1761
-- Name: fki_cs_process_action_property_property_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_action_property_property_id ON cs_process_action_property USING btree (property_id);


--
-- TOC entry 2527 (class 1259 OID 83473)
-- Dependencies: 1633
-- Name: fki_cs_process_action_role_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_action_role_id ON cs_process_action USING btree (role_id);


--
-- TOC entry 2739 (class 1259 OID 83474)
-- Dependencies: 1763
-- Name: fki_cs_process_action_transport_action_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_action_transport_action_id ON cs_process_action_transport USING btree (action_id);


--
-- TOC entry 2740 (class 1259 OID 83475)
-- Dependencies: 1763
-- Name: fki_cs_process_action_transport_event_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_action_transport_event_id ON cs_process_action_transport USING btree (event_id);


--
-- TOC entry 2741 (class 1259 OID 83476)
-- Dependencies: 1763
-- Name: fki_cs_process_action_transport_transport_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_action_transport_transport_id ON cs_process_action_transport USING btree (transport_id);


--
-- TOC entry 2528 (class 1259 OID 83477)
-- Dependencies: 1633
-- Name: fki_cs_process_action_true_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_action_true_id ON cs_process_action USING btree (true_action_id);


--
-- TOC entry 2529 (class 1259 OID 83478)
-- Dependencies: 1633
-- Name: fki_cs_process_actions_process_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_actions_process_id ON cs_process_action USING btree (process_id);


--
-- TOC entry 2530 (class 1259 OID 83479)
-- Dependencies: 1633
-- Name: fki_cs_process_actions_type_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_actions_type_id ON cs_process_action USING btree (type_id);


--
-- TOC entry 2744 (class 1259 OID 83480)
-- Dependencies: 1766
-- Name: fki_cs_process_current_action_performer_action_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_current_action_performer_action_id ON cs_process_current_action_performer USING btree (instance_action_id);


--
-- TOC entry 2745 (class 1259 OID 83481)
-- Dependencies: 1766
-- Name: fki_cs_process_current_action_performer_initiator_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_current_action_performer_initiator_id ON cs_process_current_action_performer USING btree (initiator_id);


--
-- TOC entry 2746 (class 1259 OID 83482)
-- Dependencies: 1766
-- Name: fki_cs_process_current_action_performer_performer_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_current_action_performer_performer_id ON cs_process_current_action_performer USING btree (performer_id);


--
-- TOC entry 2747 (class 1259 OID 83483)
-- Dependencies: 1766
-- Name: fki_cs_process_current_action_performer_status_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_current_action_performer_status_id ON cs_process_current_action_performer USING btree (status_id);


--
-- TOC entry 2750 (class 1259 OID 83484)
-- Dependencies: 1769
-- Name: fki_cs_process_info_property_process_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_info_property_process_id ON cs_process_info_property USING btree (process_id);


--
-- TOC entry 2751 (class 1259 OID 83485)
-- Dependencies: 1769
-- Name: fki_cs_process_info_property_property_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_info_property_property_id ON cs_process_info_property USING btree (property_id);


--
-- TOC entry 2540 (class 1259 OID 83486)
-- Dependencies: 1635
-- Name: fki_cs_process_instances_initiator_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_instances_initiator_id ON cs_process_instance USING btree (initiator_id);


--
-- TOC entry 2541 (class 1259 OID 83487)
-- Dependencies: 1635
-- Name: fki_cs_process_instances_parent_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_instances_parent_id ON cs_process_instance USING btree (parent_id);


--
-- TOC entry 2542 (class 1259 OID 83488)
-- Dependencies: 1635
-- Name: fki_cs_process_instances_process_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_instances_process_id ON cs_process_instance USING btree (process_id);


--
-- TOC entry 2543 (class 1259 OID 83489)
-- Dependencies: 1635
-- Name: fki_cs_process_instances_status_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_instances_status_id ON cs_process_instance USING btree (status_id);


--
-- TOC entry 2622 (class 1259 OID 83490)
-- Dependencies: 1671
-- Name: fki_cs_process_properties_process_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_properties_process_id ON cs_process_property USING btree (process_id);


--
-- TOC entry 2623 (class 1259 OID 83491)
-- Dependencies: 1671
-- Name: fki_cs_process_properties_sign_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_properties_sign_id ON cs_process_property USING btree (sign_id);


--
-- TOC entry 2624 (class 1259 OID 83492)
-- Dependencies: 1671
-- Name: fki_cs_process_properties_type_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_properties_type_id ON cs_process_property USING btree (type_id);


--
-- TOC entry 2625 (class 1259 OID 83493)
-- Dependencies: 1671
-- Name: fki_cs_process_property_directory_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_property_directory_id ON cs_process_property USING btree (directory_id);


--
-- TOC entry 2754 (class 1259 OID 83494)
-- Dependencies: 1773
-- Name: fki_cs_process_property_value_instance_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_property_value_instance_id ON cs_process_property_value USING btree (instance_id);


--
-- TOC entry 2759 (class 1259 OID 83495)
-- Dependencies: 1775
-- Name: fki_cs_process_role_account_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_role_account_id ON cs_process_role USING btree (account_id);


--
-- TOC entry 2760 (class 1259 OID 83496)
-- Dependencies: 1775
-- Name: fki_cs_process_roles_process_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_roles_process_id ON cs_process_role USING btree (process_id);


--
-- TOC entry 2761 (class 1259 OID 83497)
-- Dependencies: 1775
-- Name: fki_cs_process_roles_role_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_roles_role_id ON cs_process_role USING btree (role_id);


--
-- TOC entry 2764 (class 1259 OID 83498)
-- Dependencies: 1777
-- Name: fki_cs_process_transitions_from_action_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_transitions_from_action_id ON cs_process_transition USING btree (from_action_id);


--
-- TOC entry 2765 (class 1259 OID 83499)
-- Dependencies: 1777
-- Name: fki_cs_process_transitions_process_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_transitions_process_id ON cs_process_transition USING btree (process_id);


--
-- TOC entry 2766 (class 1259 OID 83500)
-- Dependencies: 1777
-- Name: fki_cs_process_transitions_to_action_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_transitions_to_action_id ON cs_process_transition USING btree (to_action_id);


--
-- TOC entry 2769 (class 1259 OID 83501)
-- Dependencies: 1779
-- Name: fki_cs_process_transport_event_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_transport_event_id ON cs_process_transport USING btree (event_id);


--
-- TOC entry 2770 (class 1259 OID 83502)
-- Dependencies: 1779
-- Name: fki_cs_process_transport_process_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_transport_process_id ON cs_process_transport USING btree (process_id);


--
-- TOC entry 2771 (class 1259 OID 83503)
-- Dependencies: 1779
-- Name: fki_cs_process_transport_transport_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_transport_transport_id ON cs_process_transport USING btree (transport_id);


--
-- TOC entry 2755 (class 1259 OID 83504)
-- Dependencies: 1773
-- Name: fki_cs_process_values_property_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_values_property_id ON cs_process_property_value USING btree (property_id);


--
-- TOC entry 2756 (class 1259 OID 83505)
-- Dependencies: 1773
-- Name: fki_cs_process_values_value_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_process_values_value_id ON cs_process_property_value USING btree (value_id);


--
-- TOC entry 2521 (class 1259 OID 83506)
-- Dependencies: 1632
-- Name: fki_cs_processes_author_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_processes_author_id ON cs_process USING btree (author_id);


--
-- TOC entry 2522 (class 1259 OID 83507)
-- Dependencies: 1632
-- Name: fki_cs_processes_parent_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_processes_parent_id ON cs_process USING btree (parent_id);


--
-- TOC entry 2549 (class 1259 OID 83508)
-- Dependencies: 1637
-- Name: fki_cs_project_instances_initiator_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_project_instances_initiator_id ON cs_project_instance USING btree (initiator_id);


--
-- TOC entry 2550 (class 1259 OID 83509)
-- Dependencies: 1637
-- Name: fki_cs_project_instances_project_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_project_instances_project_id ON cs_project_instance USING btree (project_id);


--
-- TOC entry 2551 (class 1259 OID 83510)
-- Dependencies: 1637
-- Name: fki_cs_project_instances_status_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_project_instances_status_id ON cs_project_instance USING btree (status_id);


--
-- TOC entry 2554 (class 1259 OID 83511)
-- Dependencies: 1638
-- Name: fki_cs_project_process_instance_process_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_project_process_instance_process_id ON cs_project_process_instance USING btree (process_instance_id);


--
-- TOC entry 2555 (class 1259 OID 83512)
-- Dependencies: 1638
-- Name: fki_cs_project_process_instance_project_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_project_process_instance_project_id ON cs_project_process_instance USING btree (project_instance_id);


--
-- TOC entry 2774 (class 1259 OID 83513)
-- Dependencies: 1783
-- Name: fki_cs_project_processes_process_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_project_processes_process_id ON cs_project_process USING btree (process_id);


--
-- TOC entry 2775 (class 1259 OID 83514)
-- Dependencies: 1783
-- Name: fki_cs_project_processes_project_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_project_processes_project_id ON cs_project_process USING btree (project_id);


--
-- TOC entry 2778 (class 1259 OID 83515)
-- Dependencies: 1786
-- Name: fki_cs_project_properties_project_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_project_properties_project_id ON cs_project_property USING btree (project_id);


--
-- TOC entry 2779 (class 1259 OID 83516)
-- Dependencies: 1786
-- Name: fki_cs_project_properties_sign_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_project_properties_sign_id ON cs_project_property USING btree (sign_id);


--
-- TOC entry 2780 (class 1259 OID 83517)
-- Dependencies: 1786
-- Name: fki_cs_project_properties_type_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_project_properties_type_id ON cs_project_property USING btree (type_id);


--
-- TOC entry 2788 (class 1259 OID 83518)
-- Dependencies: 1790
-- Name: fki_cs_project_role_division_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_project_role_division_id ON cs_project_role USING btree (division_id);


--
-- TOC entry 2789 (class 1259 OID 83519)
-- Dependencies: 1790
-- Name: fki_cs_project_roles_project_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_project_roles_project_id ON cs_project_role USING btree (project_id);


--
-- TOC entry 2790 (class 1259 OID 83520)
-- Dependencies: 1790
-- Name: fki_cs_project_roles_role_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_project_roles_role_id ON cs_project_role USING btree (role_id);


--
-- TOC entry 2783 (class 1259 OID 83521)
-- Dependencies: 1788
-- Name: fki_cs_project_values_instance_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_project_values_instance_id ON cs_project_property_value USING btree (instance_id);


--
-- TOC entry 2784 (class 1259 OID 83522)
-- Dependencies: 1788
-- Name: fki_cs_project_values_property_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_project_values_property_id ON cs_project_property_value USING btree (property_id);


--
-- TOC entry 2785 (class 1259 OID 83523)
-- Dependencies: 1788
-- Name: fki_cs_project_values_value_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_project_values_value_id ON cs_project_property_value USING btree (value_id);


--
-- TOC entry 2546 (class 1259 OID 83524)
-- Dependencies: 1636
-- Name: fki_cs_projects_author_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_projects_author_id ON cs_project USING btree (author_id);


--
-- TOC entry 2795 (class 1259 OID 83525)
-- Dependencies: 1795
-- Name: fki_cs_public_blob_file_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_public_blob_file_id ON cs_public_blob USING btree (file_id);


--
-- TOC entry 2798 (class 1259 OID 83526)
-- Dependencies: 1797
-- Name: fki_cs_public_document_created_by; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_public_document_created_by ON cs_public_document USING btree (created_by);


--
-- TOC entry 2799 (class 1259 OID 83527)
-- Dependencies: 1797
-- Name: fki_cs_public_document_parent_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_public_document_parent_id ON cs_public_document USING btree (parent_id);


--
-- TOC entry 2800 (class 1259 OID 83528)
-- Dependencies: 1797
-- Name: fki_cs_public_document_topic_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_public_document_topic_id ON cs_public_document USING btree (topic_id);


--
-- TOC entry 2801 (class 1259 OID 83529)
-- Dependencies: 1797
-- Name: fki_cs_public_document_updated_by; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_public_document_updated_by ON cs_public_document USING btree (updated_by);


--
-- TOC entry 2808 (class 1259 OID 83530)
-- Dependencies: 1803
-- Name: fki_cs_responser_account_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_cs_responser_account_id ON cs_responser USING btree (account_id);


--
-- TOC entry 2825 (class 1259 OID 83531)
-- Dependencies: 1830
-- Name: pbb_online_user_id_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX pbb_online_user_id_idx ON pbb_online USING btree (user_id);


--
-- TOC entry 2826 (class 1259 OID 83532)
-- Dependencies: 1831 1831
-- Name: pbb_posts_multi_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX pbb_posts_multi_idx ON pbb_posts USING btree (poster_id, topic_id);


--
-- TOC entry 2829 (class 1259 OID 83533)
-- Dependencies: 1831
-- Name: pbb_posts_topic_id_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX pbb_posts_topic_id_idx ON pbb_posts USING btree (topic_id);


--
-- TOC entry 2834 (class 1259 OID 83534)
-- Dependencies: 1835
-- Name: pbb_reports_zapped_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX pbb_reports_zapped_idx ON pbb_reports USING btree (zapped);


--
-- TOC entry 2835 (class 1259 OID 83535)
-- Dependencies: 1837
-- Name: pbb_search_cache_ident_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX pbb_search_cache_ident_idx ON pbb_search_cache USING btree (ident);


--
-- TOC entry 2838 (class 1259 OID 83536)
-- Dependencies: 1838
-- Name: pbb_search_matches_post_id_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX pbb_search_matches_post_id_idx ON pbb_search_matches USING btree (post_id);


--
-- TOC entry 2839 (class 1259 OID 83537)
-- Dependencies: 1838
-- Name: pbb_search_matches_word_id_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX pbb_search_matches_word_id_idx ON pbb_search_matches USING btree (word_id);


--
-- TOC entry 2840 (class 1259 OID 83538)
-- Dependencies: 1839
-- Name: pbb_search_words_id_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX pbb_search_words_id_idx ON pbb_search_words USING btree (id);


--
-- TOC entry 2845 (class 1259 OID 83539)
-- Dependencies: 1842
-- Name: pbb_topics_forum_id_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX pbb_topics_forum_id_idx ON pbb_topics USING btree (forum_id);


--
-- TOC entry 2846 (class 1259 OID 83540)
-- Dependencies: 1842
-- Name: pbb_topics_moved_to_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX pbb_topics_moved_to_idx ON pbb_topics USING btree (moved_to);


--
-- TOC entry 2851 (class 1259 OID 83541)
-- Dependencies: 1844
-- Name: pbb_users_registered_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX pbb_users_registered_idx ON pbb_users USING btree (registered);


--
-- TOC entry 2852 (class 1259 OID 83542)
-- Dependencies: 1844
-- Name: pbb_users_username_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX pbb_users_username_idx ON pbb_users USING btree (username);


--
-- TOC entry 2853 (class 2606 OID 83543)
-- Dependencies: 2497 1622 1621
-- Name: cs_account_cellop_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_account
    ADD CONSTRAINT cs_account_cellop_id FOREIGN KEY (cellop_id) REFERENCES cs_cellop(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2854 (class 2606 OID 83548)
-- Dependencies: 2499 1623 1621
-- Name: cs_account_division_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_account
    ADD CONSTRAINT cs_account_division_id FOREIGN KEY (division_id) REFERENCES cs_division(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2934 (class 2606 OID 83553)
-- Dependencies: 2493 1621 1683
-- Name: cs_account_divisions_account_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_account_division
    ADD CONSTRAINT cs_account_divisions_account_id FOREIGN KEY (account_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2935 (class 2606 OID 83558)
-- Dependencies: 2499 1623 1683
-- Name: cs_account_divisions_division_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_account_division
    ADD CONSTRAINT cs_account_divisions_division_id FOREIGN KEY (division_id) REFERENCES cs_division(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2859 (class 2606 OID 83563)
-- Dependencies: 2493 1621 1626
-- Name: cs_account_post_account_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_account_post
    ADD CONSTRAINT cs_account_post_account_id FOREIGN KEY (account_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2860 (class 2606 OID 83568)
-- Dependencies: 2499 1623 1626
-- Name: cs_account_post_division_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_account_post
    ADD CONSTRAINT cs_account_post_division_id FOREIGN KEY (division_id) REFERENCES cs_division(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2861 (class 2606 OID 83573)
-- Dependencies: 2509 1627 1626
-- Name: cs_account_post_post_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_account_post
    ADD CONSTRAINT cs_account_post_post_id FOREIGN KEY (post_id) REFERENCES cs_post(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2862 (class 2606 OID 83578)
-- Dependencies: 2493 1621 1630
-- Name: cs_account_today_account_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_account_today
    ADD CONSTRAINT cs_account_today_account_id FOREIGN KEY (account_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2863 (class 2606 OID 83583)
-- Dependencies: 2531 1634 1630
-- Name: cs_account_today_action_instance_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_account_today
    ADD CONSTRAINT cs_account_today_action_instance_id FOREIGN KEY (action_instance_id) REFERENCES cs_process_current_action(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2864 (class 2606 OID 83588)
-- Dependencies: 2538 1635 1630
-- Name: cs_account_today_process_instance_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_account_today
    ADD CONSTRAINT cs_account_today_process_instance_id FOREIGN KEY (process_instance_id) REFERENCES cs_process_instance(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2865 (class 2606 OID 83593)
-- Dependencies: 2556 1639 1630
-- Name: cs_account_today_status_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_account_today
    ADD CONSTRAINT cs_account_today_status_id FOREIGN KEY (status_id) REFERENCES cs_status(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2855 (class 2606 OID 83598)
-- Dependencies: 2493 1621 1621
-- Name: cs_accounts_parent_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_account
    ADD CONSTRAINT cs_accounts_parent_id FOREIGN KEY (parent_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2856 (class 2606 OID 83603)
-- Dependencies: 2502 1624 1621
-- Name: cs_accounts_permission_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_account
    ADD CONSTRAINT cs_accounts_permission_id FOREIGN KEY (permission_id) REFERENCES cs_permission(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2936 (class 2606 OID 83608)
-- Dependencies: 2791 1793 1689
-- Name: cs_blob_value_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_blob
    ADD CONSTRAINT cs_blob_value_id FOREIGN KEY (value_id) REFERENCES cs_property_value(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2902 (class 2606 OID 83613)
-- Dependencies: 2561 1649 1661
-- Name: cs_calendar_event_alarm_event_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_calendar_event_alarm
    ADD CONSTRAINT cs_calendar_event_alarm_event_id FOREIGN KEY (event_id) REFERENCES cs_calendar_event(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2903 (class 2606 OID 83618)
-- Dependencies: 2596 1662 1661
-- Name: cs_calendar_event_alarm_transport_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_calendar_event_alarm
    ADD CONSTRAINT cs_calendar_event_alarm_transport_id FOREIGN KEY (transport_id) REFERENCES cs_transport(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2889 (class 2606 OID 83623)
-- Dependencies: 2493 1621 1649
-- Name: cs_calendar_event_author_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_calendar_event
    ADD CONSTRAINT cs_calendar_event_author_id FOREIGN KEY (author_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2937 (class 2606 OID 83628)
-- Dependencies: 2561 1649 1692
-- Name: cs_calendar_event_blob_event_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_calendar_event_blob
    ADD CONSTRAINT cs_calendar_event_blob_event_id FOREIGN KEY (event_id) REFERENCES cs_calendar_event(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2890 (class 2606 OID 83633)
-- Dependencies: 2558 1648 1649
-- Name: cs_calendar_event_calendar_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_calendar_event
    ADD CONSTRAINT cs_calendar_event_calendar_id FOREIGN KEY (calendar_id) REFERENCES cs_calendar(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2894 (class 2606 OID 83638)
-- Dependencies: 2581 1656 1654
-- Name: cs_calendar_event_period_condition_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_calendar_event_period
    ADD CONSTRAINT cs_calendar_event_period_condition_id FOREIGN KEY (condition_id) REFERENCES cs_event_period_condition(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2893 (class 2606 OID 83643)
-- Dependencies: 2561 1649 1650
-- Name: cs_calendar_event_period_detail_event_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_calendar_event_period_detail
    ADD CONSTRAINT cs_calendar_event_period_detail_event_id FOREIGN KEY (event_id) REFERENCES cs_calendar_event(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2895 (class 2606 OID 83648)
-- Dependencies: 2561 1649 1654
-- Name: cs_calendar_event_period_event_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_calendar_event_period
    ADD CONSTRAINT cs_calendar_event_period_event_id FOREIGN KEY (event_id) REFERENCES cs_calendar_event(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2896 (class 2606 OID 83653)
-- Dependencies: 2579 1655 1654
-- Name: cs_calendar_event_period_period_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_calendar_event_period
    ADD CONSTRAINT cs_calendar_event_period_period_id FOREIGN KEY (period_id) REFERENCES cs_event_period(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2891 (class 2606 OID 83658)
-- Dependencies: 2570 1651 1649
-- Name: cs_calendar_event_priority_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_calendar_event
    ADD CONSTRAINT cs_calendar_event_priority_id FOREIGN KEY (priority_id) REFERENCES cs_event_priority(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2898 (class 2606 OID 83663)
-- Dependencies: 2493 1621 1658
-- Name: cs_calendar_event_reciever_account_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_calendar_event_reciever
    ADD CONSTRAINT cs_calendar_event_reciever_account_id FOREIGN KEY (account_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2899 (class 2606 OID 83668)
-- Dependencies: 2561 1649 1658
-- Name: cs_calendar_event_reciever_event_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_calendar_event_reciever
    ADD CONSTRAINT cs_calendar_event_reciever_event_id FOREIGN KEY (event_id) REFERENCES cs_calendar_event(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2900 (class 2606 OID 83673)
-- Dependencies: 2590 1659 1658
-- Name: cs_calendar_event_reciever_permission_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_calendar_event_reciever
    ADD CONSTRAINT cs_calendar_event_reciever_permission_id FOREIGN KEY (permission_id) REFERENCES cs_object_permission(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2901 (class 2606 OID 83678)
-- Dependencies: 2572 1652 1658
-- Name: cs_calendar_event_reciever_status_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_calendar_event_reciever
    ADD CONSTRAINT cs_calendar_event_reciever_status_id FOREIGN KEY (status_id) REFERENCES cs_event_status(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2892 (class 2606 OID 83683)
-- Dependencies: 2572 1652 1649
-- Name: cs_calendar_event_status_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_calendar_event
    ADD CONSTRAINT cs_calendar_event_status_id FOREIGN KEY (status_id) REFERENCES cs_event_status(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2888 (class 2606 OID 83688)
-- Dependencies: 2493 1621 1648
-- Name: cs_calendar_owner_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_calendar
    ADD CONSTRAINT cs_calendar_owner_id FOREIGN KEY (owner_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2904 (class 2606 OID 83693)
-- Dependencies: 2493 1621 1665
-- Name: cs_calendar_permission_account_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_calendar_permission
    ADD CONSTRAINT cs_calendar_permission_account_id FOREIGN KEY (account_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2905 (class 2606 OID 83698)
-- Dependencies: 2558 1648 1665
-- Name: cs_calendar_permission_calendar_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_calendar_permission
    ADD CONSTRAINT cs_calendar_permission_calendar_id FOREIGN KEY (calendar_id) REFERENCES cs_calendar(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2906 (class 2606 OID 83703)
-- Dependencies: 2590 1659 1665
-- Name: cs_calendar_permission_permission_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_calendar_permission
    ADD CONSTRAINT cs_calendar_permission_permission_id FOREIGN KEY (permission_id) REFERENCES cs_object_permission(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2923 (class 2606 OID 83708)
-- Dependencies: 2493 1621 1675
-- Name: cs_chrono_account_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_chrono
    ADD CONSTRAINT cs_chrono_account_id FOREIGN KEY (account_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2907 (class 2606 OID 83713)
-- Dependencies: 2524 1633 1667
-- Name: cs_chrono_action_action_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_chrono_action
    ADD CONSTRAINT cs_chrono_action_action_id FOREIGN KEY (action_id) REFERENCES cs_process_action(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2908 (class 2606 OID 83718)
-- Dependencies: 2531 1634 1667
-- Name: cs_chrono_action_action_instance_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_chrono_action
    ADD CONSTRAINT cs_chrono_action_action_instance_id FOREIGN KEY (action_instance_id) REFERENCES cs_process_current_action(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2909 (class 2606 OID 83723)
-- Dependencies: 2493 1621 1667
-- Name: cs_chrono_action_initiator_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_chrono_action
    ADD CONSTRAINT cs_chrono_action_initiator_id FOREIGN KEY (initiator_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2910 (class 2606 OID 83728)
-- Dependencies: 2493 1621 1667
-- Name: cs_chrono_action_performer_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_chrono_action
    ADD CONSTRAINT cs_chrono_action_performer_id FOREIGN KEY (performer_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2911 (class 2606 OID 83733)
-- Dependencies: 2538 1635 1667
-- Name: cs_chrono_action_process_instance_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_chrono_action
    ADD CONSTRAINT cs_chrono_action_process_instance_id FOREIGN KEY (process_instance_id) REFERENCES cs_process_instance(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2912 (class 2606 OID 83738)
-- Dependencies: 2556 1639 1667
-- Name: cs_chrono_action_status_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_chrono_action
    ADD CONSTRAINT cs_chrono_action_status_id FOREIGN KEY (status_id) REFERENCES cs_status(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2938 (class 2606 OID 83743)
-- Dependencies: 2652 1689 1702
-- Name: cs_chrono_blob_blob_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_chrono_blob
    ADD CONSTRAINT cs_chrono_blob_blob_id FOREIGN KEY (blob_id) REFERENCES cs_blob(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2939 (class 2606 OID 83748)
-- Dependencies: 2618 1670 1702
-- Name: cs_chrono_blob_value_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_chrono_blob
    ADD CONSTRAINT cs_chrono_blob_value_id FOREIGN KEY (value_id) REFERENCES cs_chrono_value(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2924 (class 2606 OID 83753)
-- Dependencies: 2531 1634 1675
-- Name: cs_chrono_from_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_chrono
    ADD CONSTRAINT cs_chrono_from_id FOREIGN KEY (from_action_id) REFERENCES cs_process_current_action(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2925 (class 2606 OID 83758)
-- Dependencies: 2538 1635 1675
-- Name: cs_chrono_instance_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_chrono
    ADD CONSTRAINT cs_chrono_instance_id FOREIGN KEY (instance_id) REFERENCES cs_process_instance(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2913 (class 2606 OID 83763)
-- Dependencies: 2630 1675 1669
-- Name: cs_chrono_property_chrono_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_chrono_property
    ADD CONSTRAINT cs_chrono_property_chrono_id FOREIGN KEY (chrono_id) REFERENCES cs_chrono(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2914 (class 2606 OID 83768)
-- Dependencies: 2538 1635 1669
-- Name: cs_chrono_property_process_instance_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_chrono_property
    ADD CONSTRAINT cs_chrono_property_process_instance_id FOREIGN KEY (process_instance_id) REFERENCES cs_process_instance(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2915 (class 2606 OID 83773)
-- Dependencies: 2620 1671 1669
-- Name: cs_chrono_property_property_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_chrono_property
    ADD CONSTRAINT cs_chrono_property_property_id FOREIGN KEY (property_id) REFERENCES cs_process_property(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2916 (class 2606 OID 83778)
-- Dependencies: 2752 1773 1669
-- Name: cs_chrono_property_property_instance_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_chrono_property
    ADD CONSTRAINT cs_chrono_property_property_instance_id FOREIGN KEY (property_instance_id) REFERENCES cs_process_property_value(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2917 (class 2606 OID 83783)
-- Dependencies: 2791 1793 1669
-- Name: cs_chrono_property_property_value_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_chrono_property
    ADD CONSTRAINT cs_chrono_property_property_value_id FOREIGN KEY (property_value_id) REFERENCES cs_property_value(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2918 (class 2606 OID 83788)
-- Dependencies: 2618 1670 1669
-- Name: cs_chrono_property_value_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_chrono_property
    ADD CONSTRAINT cs_chrono_property_value_id FOREIGN KEY (value_id) REFERENCES cs_chrono_value(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2926 (class 2606 OID 83793)
-- Dependencies: 2556 1639 1675
-- Name: cs_chrono_status; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_chrono
    ADD CONSTRAINT cs_chrono_status FOREIGN KEY (status_id) REFERENCES cs_status(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2927 (class 2606 OID 83798)
-- Dependencies: 2531 1634 1675
-- Name: cs_chrono_to_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_chrono
    ADD CONSTRAINT cs_chrono_to_id FOREIGN KEY (to_action_id) REFERENCES cs_process_current_action(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2929 (class 2606 OID 83803)
-- Dependencies: 2493 1621 1678
-- Name: cs_contact_list_account_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_contact_list
    ADD CONSTRAINT cs_contact_list_account_id FOREIGN KEY (account_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2930 (class 2606 OID 83808)
-- Dependencies: 2636 1677 1678
-- Name: cs_contact_list_contact_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_contact_list
    ADD CONSTRAINT cs_contact_list_contact_id FOREIGN KEY (contact_id) REFERENCES cs_contact(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2928 (class 2606 OID 83813)
-- Dependencies: 2493 1621 1677
-- Name: cs_contact_owner_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_contact
    ADD CONSTRAINT cs_contact_owner_id FOREIGN KEY (owner_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2931 (class 2606 OID 83818)
-- Dependencies: 2493 1621 1680
-- Name: cs_contact_permission_account_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_contact_permission
    ADD CONSTRAINT cs_contact_permission_account_id FOREIGN KEY (account_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2932 (class 2606 OID 83823)
-- Dependencies: 2636 1677 1680
-- Name: cs_contact_permission_contact_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_contact_permission
    ADD CONSTRAINT cs_contact_permission_contact_id FOREIGN KEY (contact_id) REFERENCES cs_contact(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2933 (class 2606 OID 83828)
-- Dependencies: 2590 1659 1680
-- Name: cs_contact_permission_permission_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_contact_permission
    ADD CONSTRAINT cs_contact_permission_permission_id FOREIGN KEY (permission_id) REFERENCES cs_object_permission(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2873 (class 2606 OID 83833)
-- Dependencies: 2524 1633 1634
-- Name: cs_current_actions_action_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_current_action
    ADD CONSTRAINT cs_current_actions_action_id FOREIGN KEY (action_id) REFERENCES cs_process_action(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2874 (class 2606 OID 83838)
-- Dependencies: 2493 1621 1634
-- Name: cs_current_actions_initiator_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_current_action
    ADD CONSTRAINT cs_current_actions_initiator_id FOREIGN KEY (initiator_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2875 (class 2606 OID 83843)
-- Dependencies: 2493 1621 1634
-- Name: cs_current_actions_performer_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_current_action
    ADD CONSTRAINT cs_current_actions_performer_id FOREIGN KEY (performer_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2876 (class 2606 OID 83848)
-- Dependencies: 2556 1639 1634
-- Name: cs_current_actions_status_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_current_action
    ADD CONSTRAINT cs_current_actions_status_id FOREIGN KEY (status_id) REFERENCES cs_status(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2940 (class 2606 OID 83853)
-- Dependencies: 2493 1621 1712
-- Name: cs_custom_setting_account_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_custom_setting
    ADD CONSTRAINT cs_custom_setting_account_id FOREIGN KEY (account_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2941 (class 2606 OID 83858)
-- Dependencies: 2718 1749 1712
-- Name: cs_custom_setting_module_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_custom_setting
    ADD CONSTRAINT cs_custom_setting_module_id FOREIGN KEY (module_id) REFERENCES cs_module(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2942 (class 2606 OID 83863)
-- Dependencies: 2493 1621 1714
-- Name: cs_delegate_account_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_delegate
    ADD CONSTRAINT cs_delegate_account_id FOREIGN KEY (account_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2943 (class 2606 OID 83868)
-- Dependencies: 2493 1621 1714
-- Name: cs_delegate_delegate_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_delegate
    ADD CONSTRAINT cs_delegate_delegate_id FOREIGN KEY (delegate_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2944 (class 2606 OID 83873)
-- Dependencies: 2683 1724 1717
-- Name: cs_directory_blob_value_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_directory_blob
    ADD CONSTRAINT cs_directory_blob_value_id FOREIGN KEY (value_id) REFERENCES cs_directory_value(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2945 (class 2606 OID 83878)
-- Dependencies: 2671 1716 1719
-- Name: cs_directory_field_directory_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_directory_field
    ADD CONSTRAINT cs_directory_field_directory_id FOREIGN KEY (directory_id) REFERENCES cs_directory(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2946 (class 2606 OID 83883)
-- Dependencies: 2626 1672 1719
-- Name: cs_directory_field_type_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_directory_field
    ADD CONSTRAINT cs_directory_field_type_id FOREIGN KEY (type_id) REFERENCES cs_property_type(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2947 (class 2606 OID 83888)
-- Dependencies: 2671 1716 1722
-- Name: cs_directory_record_directory_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_directory_record
    ADD CONSTRAINT cs_directory_record_directory_id FOREIGN KEY (directory_id) REFERENCES cs_directory(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2948 (class 2606 OID 83893)
-- Dependencies: 2676 1719 1724
-- Name: cs_directory_value_field_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_directory_value
    ADD CONSTRAINT cs_directory_value_field_id FOREIGN KEY (field_id) REFERENCES cs_directory_field(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2949 (class 2606 OID 83898)
-- Dependencies: 2680 1722 1724
-- Name: cs_directory_value_record_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_directory_value
    ADD CONSTRAINT cs_directory_value_record_id FOREIGN KEY (record_id) REFERENCES cs_directory_record(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2857 (class 2606 OID 83903)
-- Dependencies: 2493 1621 1623
-- Name: cs_division_boss_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_division
    ADD CONSTRAINT cs_division_boss_id FOREIGN KEY (boss_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2858 (class 2606 OID 83908)
-- Dependencies: 2499 1623 1623
-- Name: cs_divisions_parent_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_division
    ADD CONSTRAINT cs_divisions_parent_id FOREIGN KEY (parent_id) REFERENCES cs_division(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2897 (class 2606 OID 83913)
-- Dependencies: 2579 1655 1656
-- Name: cs_event_period_condition_period_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_event_period_condition
    ADD CONSTRAINT cs_event_period_condition_period_id FOREIGN KEY (period_id) REFERENCES cs_event_period(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2953 (class 2606 OID 83918)
-- Dependencies: 2689 1733 1734
-- Name: cs_file_blob_file_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_file_blob
    ADD CONSTRAINT cs_file_blob_file_id FOREIGN KEY (file_id) REFERENCES cs_file(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2950 (class 2606 OID 83923)
-- Dependencies: 2493 1621 1733
-- Name: cs_file_owner_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_file
    ADD CONSTRAINT cs_file_owner_id FOREIGN KEY (owner_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2951 (class 2606 OID 83928)
-- Dependencies: 2689 1733 1733
-- Name: cs_file_parent_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_file
    ADD CONSTRAINT cs_file_parent_id FOREIGN KEY (parent_id) REFERENCES cs_file(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2954 (class 2606 OID 83933)
-- Dependencies: 2493 1621 1737
-- Name: cs_file_permission_account_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_file_permission
    ADD CONSTRAINT cs_file_permission_account_id FOREIGN KEY (account_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2955 (class 2606 OID 83938)
-- Dependencies: 2689 1733 1737
-- Name: cs_file_permission_file_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_file_permission
    ADD CONSTRAINT cs_file_permission_file_id FOREIGN KEY (file_id) REFERENCES cs_file(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2956 (class 2606 OID 83943)
-- Dependencies: 2590 1659 1737
-- Name: cs_file_permission_permission_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_file_permission
    ADD CONSTRAINT cs_file_permission_permission_id FOREIGN KEY (permission_id) REFERENCES cs_object_permission(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2952 (class 2606 OID 83948)
-- Dependencies: 2493 1621 1733
-- Name: cs_file_updated_by; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_file
    ADD CONSTRAINT cs_file_updated_by FOREIGN KEY (updated_by) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2957 (class 2606 OID 83953)
-- Dependencies: 2493 1621 1739
-- Name: cs_message_author_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_message
    ADD CONSTRAINT cs_message_author_id FOREIGN KEY (author_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2959 (class 2606 OID 83958)
-- Dependencies: 2702 1739 1740
-- Name: cs_message_blob_message_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_message_blob
    ADD CONSTRAINT cs_message_blob_message_id FOREIGN KEY (message_id) REFERENCES cs_message(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2960 (class 2606 OID 83963)
-- Dependencies: 2702 1739 1743
-- Name: cs_message_reciever_message_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_message_reciever
    ADD CONSTRAINT cs_message_reciever_message_id FOREIGN KEY (message_id) REFERENCES cs_message(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2961 (class 2606 OID 83968)
-- Dependencies: 2493 1621 1743
-- Name: cs_message_reciever_reciever_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_message_reciever
    ADD CONSTRAINT cs_message_reciever_reciever_id FOREIGN KEY (reciever_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2962 (class 2606 OID 83973)
-- Dependencies: 2714 1745 1743
-- Name: cs_message_reciever_status_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_message_reciever
    ADD CONSTRAINT cs_message_reciever_status_id FOREIGN KEY (status_id) REFERENCES cs_message_status(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2958 (class 2606 OID 83978)
-- Dependencies: 2714 1745 1739
-- Name: cs_message_status_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_message
    ADD CONSTRAINT cs_message_status_id FOREIGN KEY (status_id) REFERENCES cs_message_status(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2963 (class 2606 OID 83983)
-- Dependencies: 2718 1749 1749
-- Name: cs_module_parent_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_module
    ADD CONSTRAINT cs_module_parent_id FOREIGN KEY (parent_id) REFERENCES cs_module(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2964 (class 2606 OID 83988)
-- Dependencies: 2718 1749 1753
-- Name: cs_permissions_list_module_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_permission_list
    ADD CONSTRAINT cs_permissions_list_module_id FOREIGN KEY (module_id) REFERENCES cs_module(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2965 (class 2606 OID 83993)
-- Dependencies: 2502 1624 1753
-- Name: cs_permissions_list_permission_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_permission_list
    ADD CONSTRAINT cs_permissions_list_permission_id FOREIGN KEY (permission_id) REFERENCES cs_permission(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2966 (class 2606 OID 83998)
-- Dependencies: 2499 1623 1756
-- Name: cs_post_relation_division_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_post_relation
    ADD CONSTRAINT cs_post_relation_division_id FOREIGN KEY (division_id) REFERENCES cs_division(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2967 (class 2606 OID 84003)
-- Dependencies: 2509 1627 1756
-- Name: cs_post_relation_post_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_post_relation
    ADD CONSTRAINT cs_post_relation_post_id FOREIGN KEY (post_id) REFERENCES cs_post(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2968 (class 2606 OID 84008)
-- Dependencies: 2509 1627 1756
-- Name: cs_post_relation_relation_post_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_post_relation
    ADD CONSTRAINT cs_post_relation_relation_post_id FOREIGN KEY (relation_post_id) REFERENCES cs_post(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2969 (class 2606 OID 84013)
-- Dependencies: 2531 1634 1758
-- Name: cs_process_action_child_action_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_action_child
    ADD CONSTRAINT cs_process_action_child_action_id FOREIGN KEY (action_id) REFERENCES cs_process_current_action(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2970 (class 2606 OID 84018)
-- Dependencies: 2538 1635 1758
-- Name: cs_process_action_child_process_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_action_child
    ADD CONSTRAINT cs_process_action_child_process_id FOREIGN KEY (process_id) REFERENCES cs_process_instance(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2868 (class 2606 OID 84023)
-- Dependencies: 2524 1633 1633
-- Name: cs_process_action_false_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_action
    ADD CONSTRAINT cs_process_action_false_id FOREIGN KEY (false_action_id) REFERENCES cs_process_action(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2971 (class 2606 OID 84028)
-- Dependencies: 2524 1633 1761
-- Name: cs_process_action_property_action_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_action_property
    ADD CONSTRAINT cs_process_action_property_action_id FOREIGN KEY (action_id) REFERENCES cs_process_action(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2972 (class 2606 OID 84033)
-- Dependencies: 2620 1671 1761
-- Name: cs_process_action_property_property_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_action_property
    ADD CONSTRAINT cs_process_action_property_property_id FOREIGN KEY (property_id) REFERENCES cs_process_property(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2869 (class 2606 OID 84038)
-- Dependencies: 2757 1775 1633
-- Name: cs_process_action_role_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_action
    ADD CONSTRAINT cs_process_action_role_id FOREIGN KEY (role_id) REFERENCES cs_process_role(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2973 (class 2606 OID 84043)
-- Dependencies: 2524 1633 1763
-- Name: cs_process_action_transport_action_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_action_transport
    ADD CONSTRAINT cs_process_action_transport_action_id FOREIGN KEY (action_id) REFERENCES cs_process_action(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2974 (class 2606 OID 84048)
-- Dependencies: 2687 1727 1763
-- Name: cs_process_action_transport_event_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_action_transport
    ADD CONSTRAINT cs_process_action_transport_event_id FOREIGN KEY (event_id) REFERENCES cs_event(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2975 (class 2606 OID 84053)
-- Dependencies: 2596 1662 1763
-- Name: cs_process_action_transport_transport_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_action_transport
    ADD CONSTRAINT cs_process_action_transport_transport_id FOREIGN KEY (transport_id) REFERENCES cs_transport(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2870 (class 2606 OID 84058)
-- Dependencies: 2524 1633 1633
-- Name: cs_process_action_true_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_action
    ADD CONSTRAINT cs_process_action_true_id FOREIGN KEY (true_action_id) REFERENCES cs_process_action(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2871 (class 2606 OID 84063)
-- Dependencies: 2519 1632 1633
-- Name: cs_process_actions_process_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_action
    ADD CONSTRAINT cs_process_actions_process_id FOREIGN KEY (process_id) REFERENCES cs_process(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2872 (class 2606 OID 84068)
-- Dependencies: 2517 1631 1633
-- Name: cs_process_actions_type_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_action
    ADD CONSTRAINT cs_process_actions_type_id FOREIGN KEY (type_id) REFERENCES cs_action_type(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2877 (class 2606 OID 84073)
-- Dependencies: 2538 1635 1634
-- Name: cs_process_current_action_instance_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_current_action
    ADD CONSTRAINT cs_process_current_action_instance_id FOREIGN KEY (instance_id) REFERENCES cs_process_instance(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2976 (class 2606 OID 84078)
-- Dependencies: 2531 1634 1766
-- Name: cs_process_current_action_performer_action_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_current_action_performer
    ADD CONSTRAINT cs_process_current_action_performer_action_id FOREIGN KEY (instance_action_id) REFERENCES cs_process_current_action(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2977 (class 2606 OID 84083)
-- Dependencies: 2493 1621 1766
-- Name: cs_process_current_action_performer_initiator_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_current_action_performer
    ADD CONSTRAINT cs_process_current_action_performer_initiator_id FOREIGN KEY (initiator_id) REFERENCES cs_account(id) ON UPDATE CASCADE;


--
-- TOC entry 2978 (class 2606 OID 84088)
-- Dependencies: 2493 1621 1766
-- Name: cs_process_current_action_performer_performer_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_current_action_performer
    ADD CONSTRAINT cs_process_current_action_performer_performer_id FOREIGN KEY (performer_id) REFERENCES cs_account(id) ON UPDATE CASCADE;


--
-- TOC entry 2979 (class 2606 OID 84093)
-- Dependencies: 2556 1639 1766
-- Name: cs_process_current_action_performer_status_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_current_action_performer
    ADD CONSTRAINT cs_process_current_action_performer_status_id FOREIGN KEY (status_id) REFERENCES cs_status(id) ON UPDATE CASCADE;


--
-- TOC entry 2980 (class 2606 OID 84098)
-- Dependencies: 2519 1632 1769
-- Name: cs_process_info_property_process_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_info_property
    ADD CONSTRAINT cs_process_info_property_process_id FOREIGN KEY (process_id) REFERENCES cs_process(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2981 (class 2606 OID 84103)
-- Dependencies: 2620 1671 1769
-- Name: cs_process_info_property_property_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_info_property
    ADD CONSTRAINT cs_process_info_property_property_id FOREIGN KEY (property_id) REFERENCES cs_process_property(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2878 (class 2606 OID 84108)
-- Dependencies: 2493 1621 1635
-- Name: cs_process_instances_initiator_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_instance
    ADD CONSTRAINT cs_process_instances_initiator_id FOREIGN KEY (initiator_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2879 (class 2606 OID 84113)
-- Dependencies: 2538 1635 1635
-- Name: cs_process_instances_parent_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_instance
    ADD CONSTRAINT cs_process_instances_parent_id FOREIGN KEY (parent_id) REFERENCES cs_process_instance(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2880 (class 2606 OID 84118)
-- Dependencies: 2519 1632 1635
-- Name: cs_process_instances_process_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_instance
    ADD CONSTRAINT cs_process_instances_process_id FOREIGN KEY (process_id) REFERENCES cs_process(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2881 (class 2606 OID 84123)
-- Dependencies: 2556 1639 1635
-- Name: cs_process_instances_status_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_instance
    ADD CONSTRAINT cs_process_instances_status_id FOREIGN KEY (status_id) REFERENCES cs_status(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2919 (class 2606 OID 84128)
-- Dependencies: 2519 1632 1671
-- Name: cs_process_properties_process_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_property
    ADD CONSTRAINT cs_process_properties_process_id FOREIGN KEY (process_id) REFERENCES cs_process(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2920 (class 2606 OID 84133)
-- Dependencies: 2628 1673 1671
-- Name: cs_process_properties_sign_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_property
    ADD CONSTRAINT cs_process_properties_sign_id FOREIGN KEY (sign_id) REFERENCES cs_sign(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2921 (class 2606 OID 84138)
-- Dependencies: 2671 1716 1671
-- Name: cs_process_property_directory_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_property
    ADD CONSTRAINT cs_process_property_directory_id FOREIGN KEY (directory_id) REFERENCES cs_directory(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2922 (class 2606 OID 84143)
-- Dependencies: 2626 1672 1671
-- Name: cs_process_property_type_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_property
    ADD CONSTRAINT cs_process_property_type_id FOREIGN KEY (type_id) REFERENCES cs_property_type(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2982 (class 2606 OID 84148)
-- Dependencies: 2538 1635 1773
-- Name: cs_process_property_value_instance_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_property_value
    ADD CONSTRAINT cs_process_property_value_instance_id FOREIGN KEY (instance_id) REFERENCES cs_process_instance(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2985 (class 2606 OID 84153)
-- Dependencies: 2493 1621 1775
-- Name: cs_process_role_account_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_role
    ADD CONSTRAINT cs_process_role_account_id FOREIGN KEY (account_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2986 (class 2606 OID 84158)
-- Dependencies: 2809 1805 1775
-- Name: cs_process_role_role_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_role
    ADD CONSTRAINT cs_process_role_role_id FOREIGN KEY (role_id) REFERENCES cs_role(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2987 (class 2606 OID 84163)
-- Dependencies: 2519 1632 1775
-- Name: cs_process_roles_process_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_role
    ADD CONSTRAINT cs_process_roles_process_id FOREIGN KEY (process_id) REFERENCES cs_process(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2988 (class 2606 OID 84168)
-- Dependencies: 2524 1633 1777
-- Name: cs_process_transitions_from_action_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_transition
    ADD CONSTRAINT cs_process_transitions_from_action_id FOREIGN KEY (from_action_id) REFERENCES cs_process_action(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2989 (class 2606 OID 84173)
-- Dependencies: 2519 1632 1777
-- Name: cs_process_transitions_process_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_transition
    ADD CONSTRAINT cs_process_transitions_process_id FOREIGN KEY (process_id) REFERENCES cs_process(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2990 (class 2606 OID 84178)
-- Dependencies: 2524 1633 1777
-- Name: cs_process_transitions_to_action_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_transition
    ADD CONSTRAINT cs_process_transitions_to_action_id FOREIGN KEY (to_action_id) REFERENCES cs_process_action(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2991 (class 2606 OID 84183)
-- Dependencies: 2687 1727 1779
-- Name: cs_process_transport_event_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_transport
    ADD CONSTRAINT cs_process_transport_event_id FOREIGN KEY (event_id) REFERENCES cs_event(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2992 (class 2606 OID 84188)
-- Dependencies: 2519 1632 1779
-- Name: cs_process_transport_process_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_transport
    ADD CONSTRAINT cs_process_transport_process_id FOREIGN KEY (process_id) REFERENCES cs_process(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2993 (class 2606 OID 84193)
-- Dependencies: 2596 1662 1779
-- Name: cs_process_transport_transport_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_transport
    ADD CONSTRAINT cs_process_transport_transport_id FOREIGN KEY (transport_id) REFERENCES cs_transport(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2983 (class 2606 OID 84198)
-- Dependencies: 2620 1671 1773
-- Name: cs_process_values_property_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_property_value
    ADD CONSTRAINT cs_process_values_property_id FOREIGN KEY (property_id) REFERENCES cs_process_property(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2984 (class 2606 OID 84203)
-- Dependencies: 2791 1793 1773
-- Name: cs_process_values_value_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process_property_value
    ADD CONSTRAINT cs_process_values_value_id FOREIGN KEY (value_id) REFERENCES cs_property_value(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2866 (class 2606 OID 84208)
-- Dependencies: 2493 1621 1632
-- Name: cs_processes_author_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process
    ADD CONSTRAINT cs_processes_author_id FOREIGN KEY (author_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2867 (class 2606 OID 84213)
-- Dependencies: 2519 1632 1632
-- Name: cs_processes_parent_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_process
    ADD CONSTRAINT cs_processes_parent_id FOREIGN KEY (parent_id) REFERENCES cs_process(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2883 (class 2606 OID 84218)
-- Dependencies: 2493 1621 1637
-- Name: cs_project_instances_initiator_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_project_instance
    ADD CONSTRAINT cs_project_instances_initiator_id FOREIGN KEY (initiator_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2884 (class 2606 OID 84223)
-- Dependencies: 2544 1636 1637
-- Name: cs_project_instances_project_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_project_instance
    ADD CONSTRAINT cs_project_instances_project_id FOREIGN KEY (project_id) REFERENCES cs_project(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2885 (class 2606 OID 84228)
-- Dependencies: 2556 1639 1637
-- Name: cs_project_instances_status_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_project_instance
    ADD CONSTRAINT cs_project_instances_status_id FOREIGN KEY (status_id) REFERENCES cs_status(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2886 (class 2606 OID 84233)
-- Dependencies: 2538 1635 1638
-- Name: cs_project_process_instance_process_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_project_process_instance
    ADD CONSTRAINT cs_project_process_instance_process_id FOREIGN KEY (process_instance_id) REFERENCES cs_process_instance(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2887 (class 2606 OID 84238)
-- Dependencies: 2547 1637 1638
-- Name: cs_project_process_instance_project_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_project_process_instance
    ADD CONSTRAINT cs_project_process_instance_project_id FOREIGN KEY (project_instance_id) REFERENCES cs_project_instance(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2994 (class 2606 OID 84243)
-- Dependencies: 2519 1632 1783
-- Name: cs_project_processes_process_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_project_process
    ADD CONSTRAINT cs_project_processes_process_id FOREIGN KEY (process_id) REFERENCES cs_process(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2995 (class 2606 OID 84248)
-- Dependencies: 2544 1636 1783
-- Name: cs_project_processes_project_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_project_process
    ADD CONSTRAINT cs_project_processes_project_id FOREIGN KEY (project_id) REFERENCES cs_project(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2996 (class 2606 OID 84253)
-- Dependencies: 2544 1636 1786
-- Name: cs_project_properties_project_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_project_property
    ADD CONSTRAINT cs_project_properties_project_id FOREIGN KEY (project_id) REFERENCES cs_project(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2997 (class 2606 OID 84258)
-- Dependencies: 2628 1673 1786
-- Name: cs_project_properties_sign_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_project_property
    ADD CONSTRAINT cs_project_properties_sign_id FOREIGN KEY (sign_id) REFERENCES cs_sign(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2998 (class 2606 OID 84263)
-- Dependencies: 2626 1672 1786
-- Name: cs_project_properties_type_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_project_property
    ADD CONSTRAINT cs_project_properties_type_id FOREIGN KEY (type_id) REFERENCES cs_property_type(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3002 (class 2606 OID 84268)
-- Dependencies: 2499 1623 1790
-- Name: cs_project_role_division_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_project_role
    ADD CONSTRAINT cs_project_role_division_id FOREIGN KEY (division_id) REFERENCES cs_division(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3003 (class 2606 OID 84273)
-- Dependencies: 2809 1805 1790
-- Name: cs_project_role_role_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_project_role
    ADD CONSTRAINT cs_project_role_role_id FOREIGN KEY (role_id) REFERENCES cs_role(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3004 (class 2606 OID 84278)
-- Dependencies: 2544 1636 1790
-- Name: cs_project_roles_project_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_project_role
    ADD CONSTRAINT cs_project_roles_project_id FOREIGN KEY (project_id) REFERENCES cs_project(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2999 (class 2606 OID 84283)
-- Dependencies: 2547 1637 1788
-- Name: cs_project_values_instance_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_project_property_value
    ADD CONSTRAINT cs_project_values_instance_id FOREIGN KEY (instance_id) REFERENCES cs_project_instance(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3000 (class 2606 OID 84288)
-- Dependencies: 2620 1671 1788
-- Name: cs_project_values_property_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_project_property_value
    ADD CONSTRAINT cs_project_values_property_id FOREIGN KEY (property_id) REFERENCES cs_process_property(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3001 (class 2606 OID 84293)
-- Dependencies: 2791 1793 1788
-- Name: cs_project_values_value_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_project_property_value
    ADD CONSTRAINT cs_project_values_value_id FOREIGN KEY (value_id) REFERENCES cs_property_value(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2882 (class 2606 OID 84298)
-- Dependencies: 2493 1621 1636
-- Name: cs_projects_author_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_project
    ADD CONSTRAINT cs_projects_author_id FOREIGN KEY (author_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3005 (class 2606 OID 84303)
-- Dependencies: 2802 1799 1795
-- Name: cs_public_blob_file_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_public_blob
    ADD CONSTRAINT cs_public_blob_file_id FOREIGN KEY (file_id) REFERENCES cs_public_file(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3006 (class 2606 OID 84308)
-- Dependencies: 2493 1621 1797
-- Name: cs_public_document_created_by; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_public_document
    ADD CONSTRAINT cs_public_document_created_by FOREIGN KEY (created_by) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3007 (class 2606 OID 84313)
-- Dependencies: 2796 1797 1797
-- Name: cs_public_document_parent_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_public_document
    ADD CONSTRAINT cs_public_document_parent_id FOREIGN KEY (parent_id) REFERENCES cs_public_document(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3008 (class 2606 OID 84318)
-- Dependencies: 2804 1801 1797
-- Name: cs_public_document_topic_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_public_document
    ADD CONSTRAINT cs_public_document_topic_id FOREIGN KEY (topic_id) REFERENCES cs_public_topic(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3009 (class 2606 OID 84323)
-- Dependencies: 2493 1621 1797
-- Name: cs_public_document_updated_by; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_public_document
    ADD CONSTRAINT cs_public_document_updated_by FOREIGN KEY (updated_by) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3010 (class 2606 OID 84328)
-- Dependencies: 2493 1621 1803
-- Name: cs_responser_account_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cs_responser
    ADD CONSTRAINT cs_responser_account_id FOREIGN KEY (account_id) REFERENCES cs_account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3016 (class 0 OID 0)
-- Dependencies: 5
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- TOC entry 3017 (class 0 OID 0)
-- Dependencies: 62
-- Name: get_tree(text, integer, integer); Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON FUNCTION get_tree(a_table text, root integer, flag integer) FROM PUBLIC;
REVOKE ALL ON FUNCTION get_tree(a_table text, root integer, flag integer) FROM postgres;
GRANT ALL ON FUNCTION get_tree(a_table text, root integer, flag integer) TO postgres;
GRANT ALL ON FUNCTION get_tree(a_table text, root integer, flag integer) TO PUBLIC;


--
-- TOC entry 3018 (class 0 OID 0)
-- Dependencies: 63
-- Name: get_tree(text, bigint, integer); Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON FUNCTION get_tree(a_table text, root bigint, flag integer) FROM PUBLIC;
REVOKE ALL ON FUNCTION get_tree(a_table text, root bigint, flag integer) FROM postgres;
GRANT ALL ON FUNCTION get_tree(a_table text, root bigint, flag integer) TO postgres;
GRANT ALL ON FUNCTION get_tree(a_table text, root bigint, flag integer) TO PUBLIC;


-- Completed on 2008-04-25 16:09:21 YEKST

--
-- PostgreSQL database dump complete
--

