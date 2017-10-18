FROM mysql:5.6

ENV MYSQL_ROOT_PASSWORD='zms'

COPY testdataset.sql.gz /docker-entrypoint-initdb.d/

