# Coachini - Système d'authentification

Ce projet utilise Symfony 6 pour gérer l'authentification des utilisateurs.

## Fonctionnalités

- Inscription des utilisateurs
- Connexion sécurisée
- Gestion du profil utilisateur
- Modification des informations personnelles
- Changement de mot de passe
- Messages flash pour les notifications

## Installation

1. Cloner le projet :
```bash
git clone [URL_DU_PROJET]
```

2. Installer les dépendances :
```bash
composer install
```

3. Configurer la base de données dans le fichier `.env` :
```env
DATABASE_URL="mysql://user:password@127.0.0.1:3306/coachini?serverVersion=8.0.32&charset=utf8mb4"
```

4. Créer la base de données :
```bash
php bin/console doctrine:database:create
```

5. Exécuter les migrations :
```bash
php bin/console doctrine:migrations:migrate
```

6. Lancer le serveur :
```bash
symfony server:start
```

## Utilisation

### Inscription
1. Accéder à `/register`
2. Remplir le formulaire d'inscription
3. Valider l'inscription

### Connexion
1. Accéder à `/login`
2. Entrer les identifiants
3. Se connecter

### Gestion du profil
1. Se connecter
2. Accéder à `/profile`
3. Modifier les informations ou le mot de passe

## Sécurité

- Protection CSRF activée
- Hachage des mots de passe
- Validation des données
- Protection des routes sensibles

## Contribution

1. Fork le projet
2. Créer une branche pour votre fonctionnalité
3. Commiter vos changements
4. Pousser vers la branche
5. Créer une Pull Request

## Licence

Ce projet est sous licence MIT. 