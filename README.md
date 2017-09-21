# PepisCMS SMS API demo

Please note that you will not be able to build this project without having access to a private
[git@bitbucket.org:ppolak/pepiscms.git](ssh://git@bitbucket.org:ppolak/pepiscms.git) repository!

# Prerequisites
 * Docker
 * Docker compose
 * Access to [git@bitbucket.org:ppolak/pepiscms.git](ssh://git@bitbucket.org:ppolak/pepiscms.git)
 * SSH keys generated and linked with a BitBucket account

## Setup steps

* Stat the environment:

    ```bash
    docker-compose up
    ```

* Open the installation script in browser at [http://localhost/install.php](http://localhost/install.php)

* Clean up the environment:

    ```bash
    docker-compose down && docker-compose kill
    ```
    
## Additional commands


* Delete all docker containers
    ```bash
    docker rm $(docker ps -a -q)
    ```

* Delete all docker images
    ```bash
    docker rmi $(docker images -q)
    ```
    
* Reinitialize everything
    ```bash
    docker-compose down; docker-compose kill; \
    docker rmi pepiscmssmsapi_web; \
    sudo rm -rf mysql/ app/ && git checkout app/
    docker-compose up
    ```

