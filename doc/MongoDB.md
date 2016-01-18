# MongoDB

## Guide d'installation sous Debian

### Référence

[Install MongoDB on Debian] (https://docs.mongodb.org/master/tutorial/install-mongodb-on-debian)

### Import de la clé public

Afin d'authentifier le paquet, il faut importer la clé public de MongoDB :

> sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv EA312927

### Créer le fichier list

les paquets sont uniquement disponible pour la version 7 (Wheezy) de Debian.

> echo "deb http://repo.mongodb.org/apt/debian wheezy/mongodb-org/3.2 main" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.2.list

### Installer MongoDB

Mettre à jour sa base de paquets :

> sudo apt-get update

Installer la dernière version stable du paquet :

> sudo apt-get install -y mongodb-org

### Configuration par défaut

Les données sont stockées dans */var/lib/mongodb*

Les logs sont stockées dans *var/log/mongodb*

Cette configuration est modifiable en éditant le fichier :

> sudo vim /etc/mongod.conf

### Service MongoDB

Le service MongoDB se nomme **mongod**

On le gère via la commande :

> sudo service mongod (start|stop|restart)

Pour se connecter et interroger le service :

> mongo

## Mongo Express - Interface d'administration

### Guide d'installation

Dépendances :

> sudo aptitude install npm nodejs 
> sudo ln -s /usr/bin/nodejs /usr/bin/node


Installation globale :

> sudo npm install -g mongo-express

### Configuration

> sudo cp /usr/local/lib/node_modules/mongo-express/config.default.js /usr/local/lib/node_modules/mongo-express/config.js

Editez le fichier config.js, pour configurer l'accès.

### Interface

> mongo-express -u superuser -p password -d database_name

et rendez-vous sur la page : http://localhost:8081/


## Premier pas avec MongoDB

### Référence

### Introduction

MongoDB est une base documentaire composée de document au format JSON (clé - valeur). Ces documents sont stockés au format **BSON**, qui est la représentation binaire du JSON.
[Les types de données supportés par **BSON**] (https://docs.mongodb.org/master/reference/bson-types/)

### Documentation

https://docs.mongodb.org/master/

