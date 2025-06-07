FROM dunglas/frankenphp:php8.4

RUN apt-get update \
  && apt-get install -y \
    git \
    unzip \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* \
  && install-php-extensions \
    xdebug

COPY --from=composer /usr/bin/composer /usr/bin/composer

ARG UID=1000
ARG GID=1000
ARG USERNAME=user
ARG GROUPNAME=user

RUN groupadd -g $GID $GROUPNAME \
  && useradd -m -s /bin/bash -u $UID -g $GID $USERNAME \
  && setcap -r /usr/local/bin/frankenphp \
  && chown -R ${USERNAME}:${USERNAME} /data/caddy && chown -R ${USERNAME}:${USERNAME} /config/caddy

USER $USERNAME
