# Ina Zaoui

## Prérequis

- PHP ^8.4
- Composer
- PostgreSQL 16
- Symfony CLI (`symfony`)
- Git
- Les fichiers médias de la version 5.4 (dossier `public/uploads`)

## Déploiement (PRODUCTION)

### 1. Cloner le dépôt

```bash
git clone https://github.com/benoitsouillat/inazaoui.git
cd inazaoui
```
### 2. Vider le cache

```bash
rm -rf var/cache/*
```

### 3. Configurer l'environnement

Créez un fichier `.env.local` à la racine (ou mettre à jour le fichier existant) :

```dotenv
APP_ENV=prod
APP_DEBUG=0
DATABASE_URL="postgresql://USER:PASSWORD@HOST:5432/DB_NAME?serverVersion=16&charset=utf8"
APP_SECRET=une_chaine_aleatoire_longue_et_secrete
```

### 4. Installer les dépendances

```bash
composer install --no-dev --optimize-autoloader
```

### 5. Appliquer les migrations

```bash
symfony console doctrine:migrations:migrate
```

Les migrations effectuent les opérations suivantes depuis la 5.4 vers la 7.4 :

- Création des entités `User`
- Mapping des médias (requiert les fichiers dans `public/uploads`)
- Initialisation des séquences PostgreSQL
- Création du compte administrateur (`ina`, utilisateur 1)
- Création des comptes utilisateurs invités

### 6. Déposer les fichiers médias

Placer les images de la version 5.4 dans `public/uploads/` en conservant leurs noms d'origine.

## Actions post-déploiement

Pour se connecter avec le compte de Ina, il faut utiliser les identifiants suivants:
- identifiant : `ina`
- mot de passe : `password`

> **Important :** Réinitialisez immédiatement les mots de passe après la première mise en ligne.
- Le mot de passe du compte `ina` (admin, utilisateur 1) doit être changé dès la connexion.
- Les comptes utilisateurs invités ont des mots de passe non conformes à bcrypt — la réinitialisation est **obligatoire** avant toute utilisation.

*****************
*****************

## Déploiement (DEVELOPPEMENT)

### 1. Cloner le dépôt

```bash
git clone https://github.com/benoitsouillat/inazaoui.git
cd inazaoui
```

### 2. Vider le cache

```bash
rm -rf var/cache/*
```

### 3. Configurer l'environnement

Créez un fichier `.env.local` à la racine :

```dotenv
APP_ENV=dev
APP_DEBUG=1
DATABASE_URL="postgresql://postgres:postgres@127.0.0.1:5432/ina_zaoui?serverVersion=16&charset=utf8"
APP_SECRET=une_chaine_aleatoire_secrete
```

### 4. Installer les dépendances

```bash
composer install
```

### 5. Démarrer les services Docker

```bash
docker compose up -d
```

Lance la base de données locale et Mailhog (test d'envoi d'emails).

### 6. Initialiser la base de données

Charge les tables et données issues de la version 5.4 :

```bash
symfony console initDevEnv
```

Puis appliquer les migrations vers la 7.4 :

```bash
symfony console doctrine:migrations:migrate
```

### 7. Démarrer le serveur

```bash
symfony server:start -d
```

## Identifiants de test

- Login : `ina`
- Mot de passe : `password`

## Commandes utiles

| Commande | Description |
|---|---|
| `symfony console initDevEnv` | Initialise la BDD dev avec les données 5.4 |
| `symfony console doctrine:migrations:migrate` | Applique les migrations |
| `composer test` | Lance les tests avec couverture HTML |
| `composer db-test` | Recrée la BDD de test et charge les fixtures |
| `composer phpstan` | Analyse statique du code |

## Tests

```bash
composer test
```

Les rapports de couverture sont générés dans `var/log/test/test-coverage/`.


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

