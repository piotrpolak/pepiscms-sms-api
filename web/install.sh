#!/bin/bash

while [ ! -f /root/.ssh/id_rsa ] ;
do
    echo "Waiting for id_rsa before initializing composer git..."
    sleep 1
done

composer install && \
cp vendor/pepis/pepiscms/install.php ./install.php && \
sed -i "/\$core_path =.*/c\$core_path = './vendor/pepis/pepiscms/';" ./install.php

/run.sh