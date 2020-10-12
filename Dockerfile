FROM webdevops/php-nginx:latest

COPY --chown=application src /app 
