
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

-- Functions #####

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
    AS $_$-- return date diff in days (v.150107)
SELECT EXTRACT('epoch' FROM ($2::timestamp - $1::timestamp)::interval)::bigint / (3600 * 24)::int
$_$;

CREATE FUNCTION smart_period_diff(date_start date, date_end date) RETURNS bigint
    LANGUAGE sql IMMUTABLE STRICT
    AS $_$-- return period diff in months (v.150107)
SELECT
((12)::int * EXTRACT('years' FROM age($2::timestamp, $1::timestamp)::interval))::bigint
+
((1)::int * EXTRACT('months' FROM age($2::timestamp, $1::timestamp)::interval))::bigint
$_$;

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
