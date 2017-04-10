CREATE TABLE commits (
    id varchar(50) NOT NULL,
    author varchar(100),
    message varchar(200),
    review_id varchar(23),
    CONSTRAINT commits_pk PRIMARY KEY (id, review_id)
)
/
