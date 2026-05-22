# For documentation see https://github.com/sclorg/nginx-container
FROM registry.access.redhat.com/ubi9/nginx-124:9.8-1779304984@sha256:708952e886f448dfd0b4739fa80655cc15954326a117b855adfb670520f594d3

# Copy built web application
COPY dist .

# Copy custom nginx configurations
COPY docker/nginx/*.conf "${NGINX_DEFAULT_CONF_PATH}"

# Start the web server
CMD nginx -g "daemon off;"
