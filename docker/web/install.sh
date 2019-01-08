#!/bin/bash

composer install --prefer-dist --ignore-platform-reqs && \
    cp vendor/piotrpolak/pepiscms/install.php ./install.php && \
    sed -i "/\$vendor_path =.*/c\$vendor_path = './vendor/';" ./install.php && \
    /run.sh