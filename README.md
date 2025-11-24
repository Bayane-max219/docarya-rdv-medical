# Docarya – Plateforme de prise de rendez-vous médicaux

Docarya est une plateforme web permettant aux **patients** de prendre facilement
des rendez-vous auprès de **professionnels de santé**, et aux **administrateurs**
de valider les comptes et de superviser l’activité.

---

## ✨ Fonctionnalités principales

- **Côté patient**
  - Création de compte et connexion
  - Recherche de professionnels de santé (nom, spécialité, localisation…)
  - Consultation de la fiche d’un professionnel (photo, spécialité, avis, horaires)
  - Visualisation d’un **calendrier de disponibilités** sur 30 jours
  - Prise de rendez-vous sur des créneaux de 30 minutes
  - Gestion de *Mes rendez-vous* (liste, annulation)

- **Côté professionnel de santé**
  - Création de compte (en attente de validation par un administrateur)
  - Configuration des **horaires de travail** (jours / heures)
  - Gestion des **indisponibilités** (congés, pauses, etc.)
  - Consultation de la liste de ses rendez-vous
  - Confirmation / annulation des rendez-vous des patients

- **Côté administrateur**
  - Validation des nouveaux comptes professionnels de santé
  - Gestion des utilisateurs (patients, professionnels, administrateurs)

- **Notifications e‑mail**
  - Envoi d’un e‑mail à l’administrateur lorsqu’un professionnel s’inscrit
  - Envoi d’e‑mails d’information / confirmation (configurable via `MAILER_DSN`)

---

## 🧱 Stack technique

- **Backend** : PHP 8, Symfony
- **ORM** : Doctrine (MySQL / MariaDB)
- **Base de données** : `docarya1`
- **Frontend** : Twig, HTML/CSS, JavaScript
- **Outils & librairies**
  - Symfony CLI (recommandé pour lancer le serveur)
  - Composer
  - Symfony Mailer
  - Système de rôles Symfony (`ROLE_PATIENT`, `ROLE_PROFESSIONNEL_DE_SANTE`, `ROLE_ADMINISTRATEUR`)

---

## 📸 Captures d’écran

> Les fichiers d’images doivent être placés dans un dossier `screenshoots/`
> à la racine du projet (même niveau que `composer.json`).
> GitHub les affichera automatiquement si les chemins correspondent.

Liste complète des captures (toutes dans `screenshoots/`) :

### 01 – Page d’accueil (publique)

![Page d’accueil](screenshoots/01-Accueil.png)

### 02 – Page d’inscription

![Page d’inscription](screenshoots/02-Inscription.png)

### 03 – Formulaire d’inscription patient

![Formulaire patient](screenshoots/03-Formulaire_Patient.png)

### 04 – Formulaire d’inscription professionnel de santé

![Formulaire professionnel](screenshoots/04-Formulaire_Dr.png)

### 05 – Page de connexion

![Page de connexion](screenshoots/05-Connexion.png)

### 06 – E-mail de confirmation / notification

![E-mail de confirmation](screenshoots/06-email.png)

### 07 – Tableau de bord / accueil patient

![Accueil patient](screenshoots/07-Accuiel_Patient.png)

### 08 – Calendrier de prise de rendez-vous

![Calendrier de RDV](screenshoots/08-Calendrier.png)

### 09 – Profil d’un professionnel de santé

![Profil professionnel](screenshoots/09-Profil_Dr.png)

### 10 – Détail d’une consultation

![Consultation](screenshoots/10-Consultation.png)

### 11 – Liste des rendez-vous

![Liste des rendez-vous](screenshoots/11-RDV.png)

### 12 – Gestion des indisponibilités (agenda)

![Indisponibilités](screenshoots/12-Indisponibilite.png)

---

## 🚀 Installation et lancement en local

### 1. Prérequis

- PHP 8.x
- Composer
- MySQL / MariaDB
- (Optionnel) Symfony CLI : https://symfony.com/download

### 2. Cloner le projet

```bash
git clone https://github.com/Bayane-max219/docarya-rdv-medical.git
cd docarya-rdv-medical
```

### 3. Installer les dépendances PHP

```bash
composer install
```

### 4. Configuration des variables d’environnement

Le fichier `.env` contient uniquement des valeurs **exemple**.

En local, créer un fichier `.env.local` (non versionné) en copiant le contenu de
`.env` puis en adaptant les valeurs sensibles, par exemple :

```bash
# Exemple (à adapter)
DATABASE_URL="mysql://root:@127.0.0.1:3306/docarya1?serverVersion=mariadb-10.4.10"

MAILER_DSN="smtp://VOTRE_EMAIL:VOTRE_MOTDEPASSE_APPLI@smtp.gmail.com:587"
```

> ⚠️ `.env.local` est déjà ignoré par Git (`.gitignore`),
> vos identifiants ne seront pas publiés sur GitHub.

### 5. Base de données

Deux options :

- **Option A – Importer le dump SQL**  
  Importer le fichier `docarya1.sql` dans MySQL (via PhpMyAdmin ou la ligne de commande).

- **Option B – Migrations (si configurées)**  
  Adapter `DATABASE_URL` puis exécuter :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 6. Lancer le serveur de développement

Avec Symfony CLI :

```bash
symfony serve
```

ou avec le serveur PHP intégré :

```bash
php -S 127.0.0.1:8000 -t public
```

L’application sera accessible sur : http://127.0.0.1:8000

---

## 👥 Rôles utilisateurs

- `ROLE_PATIENT` : prise et gestion de rendez-vous
- `ROLE_PROFESSIONNEL_DE_SANTE` : gestion des créneaux et des RDV
- `ROLE_ADMINISTRATEUR` : validation des comptes et gestion globale

---

## 🎯 Objectif du projet

Ce projet a été réalisé comme **plateforme de gestion de rendez-vous médicaux**
et sert aussi de **projet portfolio** pour démontrer :

- la maîtrise de Symfony et Doctrine ;
- la conception d’un modèle métier (patients, professionnels, rendez-vous, agendas) ;
- la gestion de la sécurité et des rôles utilisateurs ;
- l’implémentation d’un calendrier dynamique de disponibilités.
