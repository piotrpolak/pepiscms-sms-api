FROM tutum/apache-php

ENV ALLOW_OVERRIDE="true"

ADD ./install.sh /install.sh

RUN  apt-get -yq update && \
     apt-get -yqq install openssh-client && \
     mkdir -p /root/.ssh/ && touch /root/.ssh/known_hosts && \
     ssh-keyscan -H github.com >> /root/.ssh/known_hosts && \
     ssh-keyscan -H bitbucket.org >> /root/.ssh/known_hosts && \
     apt-get update && apt-get install -yq git && rm -rf /var/lib/apt/lists/* && \
     /usr/local/bin/composer self-update  && \
     rm -rf /app
