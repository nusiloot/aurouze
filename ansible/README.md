Pour déployer aurouze via ansible :

 - ajouter un groupe giildaservers dans /etc/ansible/hosts et y mettre les machines (et leurs parametres)
 - copier install.yml.example en install.yml
 - adapter les champs vars
 - lancer ansible-playbook avec la commande suivante :

    ansible-playbook --ask-become-pass  install.yml -u your_user
