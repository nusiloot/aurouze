# Initialisation du projet

## Symfony 2.8 (LTS)

### Installation de composer

> curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

### Récupération du projet

Se placer dans le dossier versionné du projet.

> git pull
> cd project/
> php composer update

Si le fichier composer n'est pas trouvé :

> php /usr/local/bin/composer update

Appliquez les droits sur les dossiers *cache* et *log* :

> rm -rf app/cache/* 
> rm -rf app/logs/*
> HTTPDUSER=`ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
> sudo chmod +a "$HTTPDUSER allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs
> sudo chmod +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs

### Gestion du vhost

Créez le vhost apache2

> sudo vim /etc/apache2/site-available/*project_name*.conf

	<VirtualHost *:80>
		ServerName uri_local_project_name
		DocumentRoot "/path_to_your_project/project_name/project/web"
		DirectoryIndex index.php
	
		<Directory "/path_to_your_project/project_name/project/web">
			AllowOverride All
			Require all granted
		</Directory>
	</VirtualHost>

> sudo a2ensite *project_name*.conf

Ajouter le ServerName dans le fichier *hosts*

> sudo vim /etc/hosts

Redemarrez apache2 :

> sudo service apache2 restart

### Configuration pour Symfony

Vérifiez la configuration en visitant la page :

http://uri_local_project_name/config.php
