FROM registry.gitlab.berlinonline.net/land-intranet/zmsbase:integrationtest

EXPOSE 80

RUN mkdir -p /var/www/zmsentities/public/_tests
WORKDIR /var/www/zmsentities

ARG GIT_USER
ARG GIT_PASS
ARG GIT_REF

COPY public/_tests/ /var/www/zmsentities/public/_tests/

RUN mkdir -p /var/www/html/terminvereinbarung/zmsentities
RUN ln -s /var/www/zmsentities/public/_tests /var/www/html/terminvereinbarung/zmsentities/_tests

CMD ["bash", "-c", "/usr/sbin/httpd -DFOREGROUND"]

