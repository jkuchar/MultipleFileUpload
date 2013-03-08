--Table: files

--DROP TABLE files;

CREATE TABLE files (
  id       integer PRIMARY KEY,
  queueID  char(13) NOT NULL,
  created  integer NOT NULL,
  data     text,
  chunk    integer NOT NULL DEFAULT 1,
  chunks   integer NOT NULL DEFAULT 1,
  name     varchar(255) NOT NULL
);

CREATE UNIQUE INDEX files_Name
  ON files
  (name);
