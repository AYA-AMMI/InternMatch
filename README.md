#  InternMatch : Smart Internship Matcher

> Une plateforme intelligente de mise en relation étudiants-entreprises pour les stages.


##  Table des Matières

- [À propos](#-à-propos)
- [Fonctionnalités principales](#-fonctionnalités-principales)
- [Technologies utilisées](#-technologies-utilisées)
- [Algorithme de Matching](#-algorithme-de-matching)
- [Installation](#-installation)
- [Utilisation](#-utilisation)
- [Captures d'écran](#-captures-décran)
- [Architecture](#-architecture)
- [Roadmap](#-roadmap)
- [Auteur](#-auteur)
- [License](#-license)

---

##  À propos

**Smart Internship Matcher** résout un problème majeur dans le recrutement de stagiaires :

- **Pour les étudiants** : Trop de temps perdu à chercher des stages non adaptés à leur profil
- **Pour les entreprises** : Réception de candidatures non qualifiées

###  La Solution

Une plateforme web qui utilise un **algorithme de compatibilité intelligent** pour matcher automatiquement les étudiants avec les offres de stage les plus pertinentes, calculant un score de compatibilité basé sur :
- Les compétences techniques
- La localisation géographique
- La durée souhaitée
- Le secteur d'activité

###  Objectifs

 Réduire de 70% le temps de recherche de stage  
 Augmenter de 50% la qualité des candidatures  
 Offrir une expérience utilisateur moderne et intuitive  
 Fournir des statistiques visuelles en temps réel  

---

##  Fonctionnalités principales

###  Espace Étudiant

- **Dashboard personnalisé** avec statistiques de candidatures
- **Système de matching intelligent** : Top 5 des offres compatibles avec score de compatibilité (0-100%)
- **Recherche avancée** avec filtres dynamiques (Ajax) :
  - Secteur d'activité
  - Localisation
  - Durée du stage
  - Fourchette salariale
- **Gestion des candidatures** : Suivi du statut (En attente/Accepté/Rejeté)
- **Profil personnalisable** : Compétences (tags), CV, préférences

###  Espace Entreprise

- **Dashboard analytique** avec graphiques interactifs (Chart.js)
- **CRUD complet des offres de stage** :
  - Création/modification/suppression
  - Gestion du statut (Active/Fermée)
- **Réception et gestion des candidatures** :
  - Visualisation du score de compatibilité
  - Acceptation/rejet avec un clic
  - Consultation des profils étudiants
- **Statistiques** : Évolution des candidatures par mois

###  Espace Administrateur

- **Dashboard global** avec métriques clés :
  - Total étudiants/entreprises/stages/candidatures
- **Gestion des utilisateurs** :
  - Modération des comptes étudiants
  - Vérification des entreprises
  - Suppression/édition
- **Statistiques avancées** :
  - Graphique circulaire : Répartition des candidatures par statut
  - Graphique en barres : Offres par secteur
  - Graphique linéaire : Évolution des inscriptions
- **Supervision des offres** : Modération et désactivation possible

---

##  Technologies utilisées

### Backend
- **Symfony 7.3** - Framework PHP moderne
- **Doctrine ORM** - Gestion de base de données
- **Symfony Security** - Authentification et autorisation
- **Twig** - Moteur de templates

### Frontend
- **Bootstrap 5.3** - Framework CSS responsive
- **Chart.js 4.x** - Visualisations graphiques
- **JavaScript ES6+** - Interactivité et Ajax
- **Custom CSS** - Design personnalisé

### Base de données
- **MySQL** - SGBD relationnel
- **Doctrine Migrations** - Versioning de schéma

### Outils de développement
- **Composer** - Gestionnaire de dépendances PHP
- **Symfony CLI** - Outils de développement
- **Git** - Gestion de version

---

##  Algorithme de Matching

L'algorithme calcule un **score de compatibilité de 0 à 100%** basé sur 4 critères pondérés :

###  Compétences techniques (50%)
```
Score = (Compétences en commun / Compétences requises) × 50
```
**Exemple :**
- Stage requiert : `[PHP, Symfony, MySQL, Docker]`
- Étudiant possède : `[PHP, Symfony, JavaScript, React]`
- Commun : 2 (PHP, Symfony)
- **Score = (2/4) × 50 = 25 points**

###  Localisation (25%)
- Même ville : **25 points**
- Même région : **15 points**
- Différent : **0 points**

###  Durée du stage (15%)
- Durée attendue = durée offerte : **15 points**
- Différence de ±1 mois : **10 points**
- Différence de ±2 mois : **5 points**
- Différence >2 mois : **0 points**

###  Secteur d'activité (10%)
- Secteur ciblé par l'étudiant : **10 points**
- Autre secteur : **5 points** (ouverture)

### Interprétation des scores

| Score | Badge | Couleur | Signification |
|-------|-------|---------|---------------|
| 90-100% | Excellent Match | 🟢 Vert | Profil idéal |
| 75-89% | Good Match | 🔵 Bleu | Très bon profil |
| 60-74% | Fair Match | 🟠 Orange | Profil acceptable |
| <60% | Low Match | ⚪ Gris | Profil peu adapté |

---

##  Installation

### Prérequis

- PHP 8.2 ou supérieur
- Composer 2.x
- MySQL 8.0+ / MariaDB 10.5+
- Symfony CLI (optionnel mais recommandé)

### Étapes d'installation

####  Cloner le repository

```
git clone https://github.com/votre-username/InternMatch.git
cd InternMatch
```

####  Installer les dépendances

```
composer install
```

####  Configurer la base de données

 `.env` :

```
DATABASE_URL="mysql://root:password@127.0.0.1:3306/internship_matcher?serverVersion=8.0"
APP_ENV=dev
```

####  Créer la base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

####  Charger les données de test (optionnel)

```
php bin/console doctrine:fixtures:load
```

Cela créera :
- 20 étudiants
- 10 entreprises
- 30 offres de stage
- 50 candidatures

####  Lancer le serveur de développement

```
symfony serve
# Ou avec PHP natif :
# php -S localhost:8000 -t public
```

Accéder à : **http://localhost:8000**

---

##  Utilisation

### Comptes de test

Après avoir chargé les fixtures, utilisez ces identifiants :

| Rôle | Email | Mot de passe |
|------|-------|--------------|
|  Étudiant | student@test.com | password |
|  Entreprise | company@test.com | password |
|  Admin | admin@test.com | password |

### Parcours utilisateur typique

#### Pour un étudiant :
1. Inscription avec compétences et préférences
2. Consultation du dashboard → Top 5 matches
3. Navigation dans toutes les offres avec filtres
4. Candidature aux offres pertinentes
5. Suivi des candidatures (statut en temps réel)

#### Pour une entreprise :
1. Inscription avec informations de l'entreprise
2. Création d'offres de stage détaillées
3. Réception des candidatures avec scores
4. Consultation des profils étudiants
5. Acceptation/rejet des candidatures

#### Pour un administrateur :
1. Vue d'ensemble avec statistiques globales
2. Gestion des utilisateurs (validation, suppression)
3. Modération des offres
4. Analyse des tendances via graphiques

---

##  Captures d'écran


### Dashboard Étudiant
![Dashboard Étudiant](docs/screenshots/student-dashboard.png)

### Système de Matching
![Matching System](docs/screenshots/matching-scores.png)

### Dashboard Entreprise
![Dashboard Entreprise](docs/screenshots/company-dashboard.png)

### Statistiques Admin
![Admin Stats](docs/screenshots/admin-statistics_1.png)
![Admin Stats](docs/screenshots/admin-statistics_2.png)

---

##  Architecture

### Structure du projet

```
smart-internship-matcher/
├── assets/              # Frontend assets (CSS, JS)
├── config/              # Configuration Symfony
├── migrations/          # Migrations Doctrine
├── public/              # Point d'entrée web
├── src/
│   ├── Controller/      # Contrôleurs (Student, Company, Admin)
│   ├── Entity/          # Entités Doctrine (User, Internship, Application)
│   ├── Repository/      # Repositories personnalisés
│   ├── Service/         # Services métier (MatchingService, StatsService)
│   ├── Form/            # Formulaires Symfony
│   └── Security/        # Configuration sécurité
├── templates/           # Vues Twig
├── tests/               # Tests unitaires et fonctionnels
├── var/                 # Cache et logs
└── vendor/              # Dépendances Composer
```

### Modèle de données

```
┌─────────────┐
│    User     │ (Inheritance: Single Table)
├─────────────┤
│ - id        │
│ - email     │◄─────────┐
│ - password  │          │
│ - roles     │          │
│ - type      │          │
└─────────────┘          │
      △                  │
      │                  │
┌─────┴──────┬──────────┴─────┐
│            │                 │
│  Student   │   Company       │
│            │                 │
│            │                 │
│            │    ┌──────────────┐
│            │    │  Internship  │
│            │    ├──────────────┤
│            │    │ - title      │
│            │    │ - skills     │
│            │    │ - location   │
│            │    └──────────────┘
│            │           │
│            │           │
│    ┌───────┴───────────┴────┐
│    │    Application         │
│    ├────────────────────────┤
│    │ - student_id           │
│    │ - internship_id        │
│    │ - matchScore (computed)│
│    │ - status               │
│    └────────────────────────┘
```

### Services principaux

- **MatchingService** : Calcul des scores de compatibilité
- **StatisticsService** : Génération des données pour graphiques
- **ApplicationManager** : Gestion du cycle de vie des candidatures

---

##  Roadmap

### Version 1.0 (Actuelle) 
- [x] Authentification multi-rôles
- [x] CRUD complet des offres
- [x] Algorithme de matching
- [x] Dashboards avec statistiques
- [x] Filtres Ajax dynamiques

### Version 1.1 (En cours) 
- [ ] Système de notifications en temps réel
- [ ] Email notifications (Symfony Mailer)
- [ ] Export CSV des statistiques
- [ ] Upload de CV (PDF)

### Version 2.0 (Futur) 
- [ ] API REST pour mobile
- [ ] Chat entre étudiants et entreprises
- [ ] Système de recommandations ML
- [ ] Analyse de CV automatique (NLP)
- [ ] Intégration LinkedIn OAuth

---

## 👩‍💻 Auteur

**AMMI Aya**  
Étudiante en 4ème année Génie Informatique  
École Nationale des Sciences Appliquées d'Oujda (ENSAO)

ammiaya1502@gmail.com
www.linkedin.com/in/ammi-aya
https://github.com/AYA-AMMI
---


##  Remerciements

- **Symfony Community** - Pour la documentation exceptionnelle
- **Chart.js** - Pour les visualisations de données
- **Bootstrap** - Pour le framework CSS

---

##  Contact

Pour toute question ou suggestion :

-  Email : ammiaya1502@gmail.com
-  LinkedIn : www.linkedin.com/in/ammi-aya
-  Issues : https://github.com/AYA-AMMI

---

