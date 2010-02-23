/*CREATE TABLE queues (
	id INTEGER PRIMARY KEY ,
	queueID CHAR(13) NOT NULL UNIQUE,
	lastAccessTime INTEGER(11) NOT NULL
);*/

CREATE TABLE files (
	id INTEGER PRIMARY KEY ,
	queueID CHAR(13) NOT NULL ,
	created INTEGER(11) NOT NULL ,
	data TEXT NOT NULL
);
/*
CREATE VIEW queues AS 
  SELECT queueID, created AS lastAccess
  FROM files
  GROUP BY queueID
  ORDER BY lastAccess DESC*/


/*INSERT INTO queues (queueID,lastAccessTime) VALUES ("test",1236);*/