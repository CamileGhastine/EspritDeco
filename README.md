# Installation du projet Symfony (Docker) Esprit Déco

## Prérequis

* Docker
* Docker Compose

## Conteneurs utilisés

* `ecom_php`
* `ecom_nginx`
* `ecom_mysql`
* `ecom_phpmyadmin`
* `ecom_mailhog`

## Étapes d’installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/CamileGhastine/EspritDeco.git
cd EspritDeco
```

### 2. Démarrer les conteneurs

Vérifier que les ports ne sont pas déjà utilisés

```bash
docker-compose up -d --build
```

### 3. Installer les dépendances Symfony

```bash
docker exec -it ecom_php composer install
```

### 4. Configurer l’environnement

Créer le fichier `.env.local` :

```bash
cp .env .env.local
```

Vérifier la configuration de la base de données :

```
DATABASE_URL="mysql://user:pwd@ecom:3306/db_name"
```

Renseigner votre clef API Stripe
```
STRIPE_SECRET=sk_test_***
```

Adapter les autres variables d’environnement si nécessaire


### 5. Lancer les migrations

```bash
docker exec -it ecom_php php bin/console doctrine:migrations:migrate
```

### 6. Charger les fausses donées si besoin

```bash
docker exec -it ecom_php php bin/console doctrine:fixtures:load
```

## Accès aux services

* Application Symfony : http://localhost:8080
* PHPMyAdmin : http://localhost:8081
* MailHog : http://localhost:8025
Les emails envoyés par l’application sont visibles via MailHog

Pour se connecter avec le roles ROLE_ADMIN:
- admin@esprit-deco.fr
- admin
Pour se connecter avec le mode ROLE_USER :
- toto@gmail.com
- pass

## Commandes utiles

* Accéder au conteneur PHP :

```bash
docker exec -it ecom_php sh
```

* Voir les logs :

```bash
docker-compose logs -f
```

* Arrêter les conteneurs :

```bash
docker-compose down
```

## Carte de test pour Stripe

4242 4242 4242 4242
CVC :123
MM/AA : Mois/Année posterieur à la date du jour
