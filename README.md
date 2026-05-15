# Ina Zaoui

Pour se connecter avec le compte de Ina, il faut utiliser les identifiants suivants:
- identifiant : `ina`
- mot de passe : `password`

__Installation :__

* ```shell composer install```

__Démarrage du projet :__

* ```shell docker compose up -d``` (Développement uniquement)
  * -> Configuré pour créer une base de donnée local pour le dev et les tests, ainsi que mailhog pour tester l'envoi des emails
* ```shell symfony console serve -d``` (Développement uniquement)
  * -> Démarrage du projet symfony en version 7.4
* ```shell symfony console doctrine:migrations:migrate```
  * -> Application des migrations à réaliser depuis la version 5.4 vers la version 7.4
    * Création des entités User
    * Mapping des Médias (requiert les médias dans le dossier public/uploads)
    * Initialisation des séquences Postgres SQL en fonction des données existantes
    * Création du compte administrateur via un UserProvider (⚠️ En production, il faut réinitialiser immédiatement le mot de passe de 'ina' (Utilisateur 1)
    * Création des comptes utilisateurs invités avec MOT DE PASSE non conforme à bcrypt (Réinitialisation obligatoire)
* Mise en ligne des images (Télécharger les images, avec leurs noms actuels dans le dossier public//uploads)


Draw | Brouillon
PRODUCTION : 

Installer le projet si ce n'est pas fait
Forcer le cache à se vider à la main : rm -rf var/cache/*
composer install --no-dev --optimize-autoloader
Fichier .env.local : APP_ENV=prod APP_DEBUG=0
Récupérer depuis le repository de benoitsouillat
* git remote add deploy https://github.com/benoitsouillat/inazaoui
* git fetch deploy
* git config pull.rebase false (Non nécessaire si c'est un git clone)
* git pull origin master --allow-unrelated-histories (Le flag n'est pas nécessaire si git clone)



