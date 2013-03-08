--Table: public.files

--DROP TABLE public.files;

CREATE TABLE public.files (
  id         serial NOT NULL PRIMARY KEY,
  "queueID"  varchar(100) NOT NULL,
  created    integer NOT NULL,
  data       text,
  chunk      integer,
  chunks     integer NOT NULL DEFAULT 1,
  "name"     varchar(255) NOT NULL
) WITH (
    OIDS = FALSE
);
