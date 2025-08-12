# For documentation see https://github.com/sclorg/nginx-container
FROM registry.access.redhat.com/ubi9/nginx-124:9.6-1754406834@sha256:5a3130f72a6b50d77cb2642028ebac8acf420647887ddee7ccd1eedc2b02d69b

# Copy built web application
COPY --chown=default:root dist .
RUN chmod -R g+w .

# Copy custom nginx configurations
COPY docker/nginx/*.conf "${NGINX_DEFAULT_CONF_PATH}"

# Start the web server
CMD nginx -g "daemon off;"
