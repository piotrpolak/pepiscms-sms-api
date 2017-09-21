# PepisCMS SMS API demo

Please note that you will not be able to build this project without having access to a private git@bitbucket.org:ppolak/pepiscms.git repository!

# Prerequisites
 * Docker
 * Docker compose
 * Local composer (will be removed in future)

## Setup steps

* Download dependencies (currently locally):

    ```bash
    pushd app/ && composer install && \
    cp vendor/pepis/pepiscms/install.php ./install.php && \
    sed -i "/\$core_path =.*/c\$core_path = './vendor/pepis/pepiscms/';" ./install.php && \
    popd
    ```

* Stat the environment:

    ```bash
    docker-compose up
    ```

* Open the installation script in browser at [http://localhost/install.php](http://localhost/install.php)

* Clean up the environment:

    ```bash
    docker-compose down && docker-compose kill
    ```