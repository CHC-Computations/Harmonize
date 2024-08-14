--
-- PostgreSQL database dump
--

-- Dumped from database version 15.6 (Debian 15.6-0+deb12u1)
-- Dumped by pg_dump version 15.6 (Debian 15.6-0+deb12u1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: public; Type: SCHEMA; Schema: -; Owner: pg_database_owner
--

CREATE SCHEMA public;


ALTER SCHEMA public OWNER TO pg_database_owner;

--
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: pg_database_owner
--

COMMENT ON SCHEMA public IS 'standard public schema';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: cms_posts; Type: TABLE; Schema: public; Owner: libri
--

CREATE TABLE public.cms_posts (
    id integer NOT NULL,
    parent_id integer,
    p_order integer,
    lang character(2),
    url name,
    title name,
    content text,
    script text,
    date_c timestamp without time zone,
    date_m timestamp without time zone,
    author name,
    operator name
);


ALTER TABLE public.cms_posts OWNER TO libri;

--
-- Name: cms_posts_id_seq; Type: SEQUENCE; Schema: public; Owner: libri
--

CREATE SEQUENCE public.cms_posts_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.cms_posts_id_seq OWNER TO libri;

--
-- Name: cms_posts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: libri
--

ALTER SEQUENCE public.cms_posts_id_seq OWNED BY public.cms_posts.id;


--
-- Name: dic_rec_types; Type: TABLE; Schema: public; Owner: libri
--

CREATE TABLE public.dic_rec_types (
    id integer NOT NULL,
    rec_type_name name
);


ALTER TABLE public.dic_rec_types OWNER TO libri;

--
-- Name: dic_rec_types_id_seq; Type: SEQUENCE; Schema: public; Owner: libri
--

CREATE SEQUENCE public.dic_rec_types_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dic_rec_types_id_seq OWNER TO libri;

--
-- Name: dic_rec_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: libri
--

ALTER SEQUENCE public.dic_rec_types_id_seq OWNED BY public.dic_rec_types.id;


--
-- Name: dic_users_powers; Type: TABLE; Schema: public; Owner: libri
--

CREATE TABLE public.dic_users_powers (
    id integer NOT NULL,
    power name
);


ALTER TABLE public.dic_users_powers OWNER TO libri;

--
-- Name: dic_users_powers_id_seq; Type: SEQUENCE; Schema: public; Owner: libri
--

CREATE SEQUENCE public.dic_users_powers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dic_users_powers_id_seq OWNER TO libri;

--
-- Name: dic_users_powers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: libri
--

ALTER SEQUENCE public.dic_users_powers_id_seq OWNED BY public.dic_users_powers.id;


--
-- Name: dic_values_types; Type: TABLE; Schema: public; Owner: libri
--

CREATE TABLE public.dic_values_types (
    id integer NOT NULL,
    value_type name
);


ALTER TABLE public.dic_values_types OWNER TO libri;

--
-- Name: dic_values_types_id_seq; Type: SEQUENCE; Schema: public; Owner: libri
--

CREATE SEQUENCE public.dic_values_types_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dic_values_types_id_seq OWNER TO libri;

--
-- Name: dic_values_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: libri
--

ALTER SEQUENCE public.dic_values_types_id_seq OWNED BY public.dic_values_types.id;


--
-- Name: facets_queries; Type: TABLE; Schema: public; Owner: libri
--

CREATE TABLE public.facets_queries (
    code name NOT NULL,
    query text NOT NULL,
    "time" timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE public.facets_queries OWNER TO libri;

--
-- Name: matching_fields; Type: TABLE; Schema: public; Owner: libri
--

CREATE TABLE public.matching_fields (
    id integer NOT NULL,
    fieldname name
);


ALTER TABLE public.matching_fields OWNER TO libri;

--
-- Name: matching_fields_id_seq; Type: SEQUENCE; Schema: public; Owner: libri
--

CREATE SEQUENCE public.matching_fields_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.matching_fields_id_seq OWNER TO libri;

--
-- Name: matching_fields_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: libri
--

ALTER SEQUENCE public.matching_fields_id_seq OWNED BY public.matching_fields.id;


--
-- Name: matching_manual; Type: TABLE; Schema: public; Owner: libri
--

CREATE TABLE public.matching_manual (
    id integer NOT NULL,
    field integer,
    value text,
    target name,
    data timestamp without time zone,
    operator name,
    valuetype integer,
    rectype integer
);


ALTER TABLE public.matching_manual OWNER TO libri;

--
-- Name: matching_manual_id_seq; Type: SEQUENCE; Schema: public; Owner: libri
--

CREATE SEQUENCE public.matching_manual_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.matching_manual_id_seq OWNER TO libri;

--
-- Name: matching_manual_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: libri
--

ALTER SEQUENCE public.matching_manual_id_seq OWNED BY public.matching_manual.id;


--
-- Name: matching_results; Type: TABLE; Schema: public; Owner: libri
--

CREATE TABLE public.matching_results (
    id integer NOT NULL,
    string_id integer,
    rectype_id integer,
    eid_type name,
    eid name,
    match_type name,
    match_source name,
    match_level real,
    match_result name
);


ALTER TABLE public.matching_results OWNER TO libri;

--
-- Name: matching_results_id_seq; Type: SEQUENCE; Schema: public; Owner: libri
--

CREATE SEQUENCE public.matching_results_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.matching_results_id_seq OWNER TO libri;

--
-- Name: matching_results_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: libri
--

ALTER SEQUENCE public.matching_results_id_seq OWNED BY public.matching_results.id;


--
-- Name: matching_strings; Type: TABLE; Schema: public; Owner: libri
--

CREATE TABLE public.matching_strings (
    id integer NOT NULL,
    string character varying(380),
    clearstring character varying(350)
);


ALTER TABLE public.matching_strings OWNER TO libri;

--
-- Name: matching_strings_id_seq; Type: SEQUENCE; Schema: public; Owner: libri
--

CREATE SEQUENCE public.matching_strings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.matching_strings_id_seq OWNER TO libri;

--
-- Name: matching_strings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: libri
--

ALTER SEQUENCE public.matching_strings_id_seq OWNED BY public.matching_strings.id;


--
-- Name: matching_with_records; Type: TABLE; Schema: public; Owner: libri
--

CREATE TABLE public.matching_with_records (
    id_match integer NOT NULL,
    id_biblio name NOT NULL
);


ALTER TABLE public.matching_with_records OWNER TO libri;

--
-- Name: matching_with_records_id_match_seq; Type: SEQUENCE; Schema: public; Owner: libri
--

CREATE SEQUENCE public.matching_with_records_id_match_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.matching_with_records_id_match_seq OWNER TO libri;

--
-- Name: matching_with_records_id_match_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: libri
--

ALTER SEQUENCE public.matching_with_records_id_match_seq OWNED BY public.matching_with_records.id_match;


--
-- Name: persons; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.persons (
    viaf_id character varying(30) NOT NULL,
    wikiq integer,
    name name NOT NULL,
    name_sort name,
    year_born integer,
    year_death integer,
    place_born bigint,
    place_death bigint,
    rec_total integer,
    as_author integer,
    as_author2 integer,
    as_topic integer,
    solr_str text,
    name_search text,
    c_date timestamp without time zone,
    m_date timestamp without time zone
);


ALTER TABLE public.persons OWNER TO postgres;

--
-- Name: places_countries; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.places_countries (
    wikiq bigint NOT NULL,
    name name,
    color name
);


ALTER TABLE public.places_countries OWNER TO postgres;

--
-- Name: searchstrings; Type: TABLE; Schema: public; Owner: libri
--

CREATE TABLE public.searchstrings (
    core name NOT NULL,
    code name NOT NULL,
    string text,
    counter integer,
    lastuse timestamp without time zone
);


ALTER TABLE public.searchstrings OWNER TO libri;

--
-- Name: translate; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.translate (
    code name NOT NULL,
    lang character(2) NOT NULL,
    string text,
    importance integer
);


ALTER TABLE public.translate OWNER TO postgres;

--
-- Name: users; Type: TABLE; Schema: public; Owner: libri
--

CREATE TABLE public.users (
    id_user integer NOT NULL,
    username name,
    password name,
    email name,
    cdate timestamp without time zone,
    vcode name,
    status integer
);


ALTER TABLE public.users OWNER TO libri;

--
-- Name: users_id_user_seq; Type: SEQUENCE; Schema: public; Owner: libri
--

CREATE SEQUENCE public.users_id_user_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_id_user_seq OWNER TO libri;

--
-- Name: users_id_user_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: libri
--

ALTER SEQUENCE public.users_id_user_seq OWNED BY public.users.id_user;


--
-- Name: users_logged; Type: TABLE; Schema: public; Owner: libri
--

CREATE TABLE public.users_logged (
    id integer NOT NULL,
    id_user integer,
    data_in timestamp without time zone,
    user_agent text,
    cmskey name,
    account_type name,
    user_data text
);


ALTER TABLE public.users_logged OWNER TO libri;

--
-- Name: users_logged_id_seq; Type: SEQUENCE; Schema: public; Owner: libri
--

CREATE SEQUENCE public.users_logged_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_logged_id_seq OWNER TO libri;

--
-- Name: users_logged_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: libri
--

ALTER SEQUENCE public.users_logged_id_seq OWNED BY public.users_logged.id;


--
-- Name: users_params; Type: TABLE; Schema: public; Owner: libri
--

CREATE TABLE public.users_params (
    session_id name NOT NULL,
    name name NOT NULL,
    value text,
    ctime timestamp without time zone DEFAULT now()
);


ALTER TABLE public.users_params OWNER TO libri;

--
-- Name: users_powers; Type: TABLE; Schema: public; Owner: libri
--

CREATE TABLE public.users_powers (
    id integer NOT NULL,
    email name,
    power_id integer,
    data_in timestamp without time zone,
    data_out timestamp without time zone
);


ALTER TABLE public.users_powers OWNER TO libri;

--
-- Name: users_powers_id_seq; Type: SEQUENCE; Schema: public; Owner: libri
--

CREATE SEQUENCE public.users_powers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_powers_id_seq OWNER TO libri;

--
-- Name: users_powers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: libri
--

ALTER SEQUENCE public.users_powers_id_seq OWNED BY public.users_powers.id;


--
-- Name: wiki_labels; Type: TABLE; Schema: public; Owner: libri
--

CREATE TABLE public.wiki_labels (
    wikiq bigint NOT NULL,
    lang character(2) NOT NULL,
    value name,
    rec_type character(1)
);


ALTER TABLE public.wiki_labels OWNER TO libri;

--
-- Name: wiki_media_urls; Type: TABLE; Schema: public; Owner: libri
--

CREATE TABLE public.wiki_media_urls (
    file_name text NOT NULL,
    url text,
    "time" timestamp without time zone,
    width integer,
    height integer
);


ALTER TABLE public.wiki_media_urls OWNER TO libri;

--
-- Name: cms_posts id; Type: DEFAULT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.cms_posts ALTER COLUMN id SET DEFAULT nextval('public.cms_posts_id_seq'::regclass);


--
-- Name: dic_rec_types id; Type: DEFAULT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.dic_rec_types ALTER COLUMN id SET DEFAULT nextval('public.dic_rec_types_id_seq'::regclass);


--
-- Name: dic_users_powers id; Type: DEFAULT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.dic_users_powers ALTER COLUMN id SET DEFAULT nextval('public.dic_users_powers_id_seq'::regclass);


--
-- Name: dic_values_types id; Type: DEFAULT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.dic_values_types ALTER COLUMN id SET DEFAULT nextval('public.dic_values_types_id_seq'::regclass);


--
-- Name: matching_fields id; Type: DEFAULT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.matching_fields ALTER COLUMN id SET DEFAULT nextval('public.matching_fields_id_seq'::regclass);


--
-- Name: matching_manual id; Type: DEFAULT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.matching_manual ALTER COLUMN id SET DEFAULT nextval('public.matching_manual_id_seq'::regclass);


--
-- Name: matching_results id; Type: DEFAULT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.matching_results ALTER COLUMN id SET DEFAULT nextval('public.matching_results_id_seq'::regclass);


--
-- Name: matching_strings id; Type: DEFAULT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.matching_strings ALTER COLUMN id SET DEFAULT nextval('public.matching_strings_id_seq'::regclass);


--
-- Name: matching_with_records id_match; Type: DEFAULT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.matching_with_records ALTER COLUMN id_match SET DEFAULT nextval('public.matching_with_records_id_match_seq'::regclass);


--
-- Name: users_powers id; Type: DEFAULT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.users_powers ALTER COLUMN id SET DEFAULT nextval('public.users_powers_id_seq'::regclass);


--
-- Name: cms_posts cms_posts_pkey; Type: CONSTRAINT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.cms_posts
    ADD CONSTRAINT cms_posts_pkey PRIMARY KEY (id);


--
-- Name: dic_rec_types dic_rec_types_pkey; Type: CONSTRAINT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.dic_rec_types
    ADD CONSTRAINT dic_rec_types_pkey PRIMARY KEY (id);


--
-- Name: dic_users_powers dic_users_powers_pkey; Type: CONSTRAINT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.dic_users_powers
    ADD CONSTRAINT dic_users_powers_pkey PRIMARY KEY (id);


--
-- Name: dic_values_types dic_values_types_pkey; Type: CONSTRAINT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.dic_values_types
    ADD CONSTRAINT dic_values_types_pkey PRIMARY KEY (id);


--
-- Name: facets_queries facets_queries_pkey; Type: CONSTRAINT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.facets_queries
    ADD CONSTRAINT facets_queries_pkey PRIMARY KEY (code);


--
-- Name: facets_queries facets_queries_query_key; Type: CONSTRAINT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.facets_queries
    ADD CONSTRAINT facets_queries_query_key UNIQUE (query);


--
-- Name: matching_fields matching_fields_pkey; Type: CONSTRAINT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.matching_fields
    ADD CONSTRAINT matching_fields_pkey PRIMARY KEY (id);


--
-- Name: matching_manual matching_manual_pkey; Type: CONSTRAINT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.matching_manual
    ADD CONSTRAINT matching_manual_pkey PRIMARY KEY (id);


--
-- Name: matching_results matching_results_pkey; Type: CONSTRAINT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.matching_results
    ADD CONSTRAINT matching_results_pkey PRIMARY KEY (id);


--
-- Name: matching_strings matching_strings_pkey; Type: CONSTRAINT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.matching_strings
    ADD CONSTRAINT matching_strings_pkey PRIMARY KEY (id);


--
-- Name: matching_with_records matching_with_records_pkey; Type: CONSTRAINT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.matching_with_records
    ADD CONSTRAINT matching_with_records_pkey PRIMARY KEY (id_match, id_biblio);


--
-- Name: users_powers users_powers_pkey; Type: CONSTRAINT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.users_powers
    ADD CONSTRAINT users_powers_pkey PRIMARY KEY (id);


--
-- Name: clearstringidx; Type: INDEX; Schema: public; Owner: libri
--

CREATE INDEX clearstringidx ON public.matching_strings USING btree (clearstring);


--
-- Name: string_idx; Type: INDEX; Schema: public; Owner: libri
--

CREATE INDEX string_idx ON public.matching_strings USING btree (string);


--
-- Name: matching_manual matching_manual_field_fkey; Type: FK CONSTRAINT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.matching_manual
    ADD CONSTRAINT matching_manual_field_fkey FOREIGN KEY (field) REFERENCES public.matching_fields(id);


--
-- Name: matching_manual matching_manual_rectype_fkey; Type: FK CONSTRAINT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.matching_manual
    ADD CONSTRAINT matching_manual_rectype_fkey FOREIGN KEY (rectype) REFERENCES public.dic_rec_types(id);


--
-- Name: matching_manual matching_manual_valuetype_fkey; Type: FK CONSTRAINT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.matching_manual
    ADD CONSTRAINT matching_manual_valuetype_fkey FOREIGN KEY (valuetype) REFERENCES public.dic_values_types(id);


--
-- Name: users_powers users_powers_power_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: libri
--

ALTER TABLE ONLY public.users_powers
    ADD CONSTRAINT users_powers_power_id_fkey FOREIGN KEY (power_id) REFERENCES public.dic_users_powers(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- PostgreSQL database dump complete
--

