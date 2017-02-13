
-- START :: PostgreSQL Functions and Tables for SmartFramework #####

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;
SET default_tablespace = '';
SET default_with_oids = false;

-- BEGIN Transaction #####

BEGIN;

-- General Functions #####

CREATE FUNCTION smart_strip_tags(text) RETURNS text
	LANGUAGE sql IMMUTABLE STRICT
	AS $_$ -- strip html tags (v.150215)
SELECT regexp_replace(
	regexp_replace($1, E'(?x)<[^>]*?(\s alt \s* = \s* ([\'"]) ([^>]*?) \2) [^>]*? >', E'\3'),
	E'(?x)(< [^>]*? >)', '', 'g'
);
$_$;

CREATE FUNCTION smart_deaccent_string(text) RETURNS text
	LANGUAGE sql IMMUTABLE STRICT
	AS $_$ -- deaccent strings c.176x2 (v.150107)
SELECT translate(
	$1,
	'áâãäåāăąÁÂÃÄÅĀĂĄćĉčçĆĈČÇďĎèéêëēĕėěęÈÉÊËĒĔĖĚĘĝģĜĢĥħĤĦìíîïĩīĭȉȋįÌÍÎÏĨĪĬȈȊĮĳĵĲĴķĶĺļľłĹĻĽŁñńņňÑŃŅŇóôõöōŏőøœÒÓÔÕÖŌŎŐØŒŕŗřŔŖŘșşšśŝšßȘŞŠŚŜŠțţťȚŢŤùúûüũūŭůűųÙÚÛÜŨŪŬŮŰŲŵŴẏỳŷÿýẎỲŶŸÝźżžŹŻŽ',
	'aaaaaaaaAAAAAAAAccccCCCCdDeeeeeeeeeEEEEEEEEEggGGhhHHiiiiiiiiiiIIIIIIIIIIjjJJkKllllLLLLnnnnNNNNoooooooooOOOOOOOOOOrrrRRRsssssssSSSSSStttTTTuuuuuuuuuuUUUUUUUUUUwWyyyyyYYYYYzzzZZZ'
);
$_$;

CREATE FUNCTION smart_datediff(date_start date, date_end date) RETURNS bigint
	LANGUAGE sql IMMUTABLE STRICT
	AS $_$ -- return date diff in days (v.150107)
SELECT EXTRACT('epoch' FROM ($2::timestamp - $1::timestamp)::interval)::bigint / (3600 * 24)::int
$_$;

CREATE FUNCTION smart_period_diff(date_start date, date_end date) RETURNS bigint
	LANGUAGE sql IMMUTABLE STRICT
	AS $_$ -- return period diff in months (v.150107)
SELECT
((12)::int * EXTRACT('years' FROM age($2::timestamp, $1::timestamp)::interval))::bigint
+
((1)::int * EXTRACT('months' FROM age($2::timestamp, $1::timestamp)::interval))::bigint
$_$;

-- JsonB Array Functions #####

CREATE OR REPLACE FUNCTION smart_jsonb_arr_delete(data jsonb, rval text)
RETURNS jsonb
IMMUTABLE
LANGUAGE sql
AS $$ -- delete by value from jsonb array [] (v.170207)
	SELECT json_agg(value)::jsonb
	FROM (
		SELECT value FROM jsonb_array_elements_text($1) WHERE value != $2
	) t;
$$;

CREATE OR REPLACE FUNCTION smart_jsonb_arr_append(data jsonb, aval jsonb)
RETURNS jsonb
IMMUTABLE
LANGUAGE sql
AS $$ -- appends a jsonb array [] with another json array [] (v.170207)
	SELECT json_agg(value)::jsonb
	FROM (
		SELECT * FROM jsonb_array_elements_text($1)
		UNION
		SELECT * FROM jsonb_array_elements_text($2)
	) t;
$$;

-- JsonB Object Functions #####

CREATE OR REPLACE FUNCTION smart_jsonb_obj_delete(data jsonb, rkey text)
RETURNS jsonb
IMMUTABLE
LANGUAGE sql
AS $$ -- delete by key from jsonb object {} (v.170207)
	SELECT json_object_agg(key, value)::jsonb
	FROM (
		SELECT * FROM jsonb_each($1)
		WHERE key != $2
	) t;
$$;

CREATE OR REPLACE FUNCTION smart_jsonb_obj_append(data jsonb, aobj jsonb)
RETURNS jsonb
IMMUTABLE
LANGUAGE sql
AS $$ -- appends a jsonb object {} with another json object {} (v.170207)
	SELECT json_object_agg(key, value)::jsonb
	FROM (
		SELECT * FROM jsonb_each($1)
		UNION ALL
		SELECT * FROM jsonb_each($2)
	) t;
$$;

-- Table _info #####

CREATE TABLE _info (
	variable character varying(100) NOT NULL,
	value character varying(16384) DEFAULT ''::character varying NOT NULL,
	comments text DEFAULT ''::text NOT NULL,
	CONSTRAINT _info__check__variable CHECK ((char_length((variable)::text) >= 1))
);
ALTER TABLE ONLY _info ADD CONSTRAINT _info__variable PRIMARY KEY (variable);
COMMENT ON TABLE _info IS 'Smart.Framework MetaInfo v.2015.03.25';
COMMENT ON COLUMN _info.variable IS 'The Variable';
COMMENT ON COLUMN _info.value IS 'The Value';
COMMENT ON COLUMN _info.comments IS 'The Comments';
INSERT INTO _info VALUES ('version', 'smart.framework', 'Software version to Validate DB');
INSERT INTO _info VALUES ('id', 'app.default', 'The Unique ID of application.
Example:
''some.id''
This will avoid to accidental connect to other database.');
INSERT INTO _info VALUES ('history', '', 'Record Upgrades History');

-- COMMIT Transaction #####

COMMIT;

-- END #####
