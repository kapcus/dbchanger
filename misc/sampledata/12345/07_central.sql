GRANT SELECT, INSERT, UPDATE, DELETE ON TESTTABLE to <region> with grant option;
GRANT SELECT, INSERT, UPDATE, DELETE ON TESTTABLE TO <user>;
GRANT ALL ON TESTTABLE TO <systemuser>;

GRANT SELECT ON TESTTABLE_SEQ TO <region>;
GRANT SELECT ON TESTTABLE_SEQ TO <user>;
GRANT ALL ON TESTTABLE_SEQ TO <systemuser>;