
--
-- TOC entry 1494 (class 1259 OID 19213)
-- Dependencies: 3
-- Name: files; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE files (
    id integer NOT NULL,
    "queueID" character varying(100) NOT NULL,
    created integer NOT NULL,
    data text NOT NULL
);

--
-- TOC entry 1493 (class 1259 OID 19211)
-- Dependencies: 1494 3
-- Name: files_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE files_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1772 (class 2604 OID 19216)
-- Dependencies: 1493 1494 1494
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE files ALTER COLUMN id SET DEFAULT nextval('files_id_seq'::regclass);


--
-- TOC entry 1774 (class 2606 OID 19221)
-- Dependencies: 1494 1494
-- Name: files_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY files
    ADD CONSTRAINT files_pkey PRIMARY KEY (id);


--
-- TOC entry 1775 (class 1259 OID 19222)
-- Dependencies: 1494
-- Name: queueID; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX "queueID" ON files USING btree ("queueID");
