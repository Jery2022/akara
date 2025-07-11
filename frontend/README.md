# Présentation de l'application de gestion de PME, dénommée AKARA

## 1. Introduction

La gestion quotidienne d’une PME, quelle que soit sa taille, est souvent complexe et chronophage. Les dirigeants et responsables doivent jongler avec de multiples tâches : suivi des stocks, gestion des employés, relation clients et fournisseurs, facturation, et analyse financière. Cette multiplicité d’outils et de processus peut entraîner des erreurs, des pertes de temps, et une visibilité limitée sur la santé globale de l’entreprise.
Notre application de gestion de PME a pour objectif principal de centraliser et simplifier ces opérations en proposant une solution intégrée, accessible et intuitive. Elle permet d’automatiser les tâches répétitives, d’assurer la cohérence des données, et de fournir des tableaux de bord clairs pour une prise de décision rapide et éclairée.
Pour les PME du secteur du BTP (Bâtiment et Travaux Publics), ces enjeux sont encore plus critiques. Ce secteur se caractérise par une forte mobilité des équipes, une gestion complexe des chantiers, des fournisseurs multiples, et des contraintes réglementaires spécifiques. Notre application répond à ces besoins en offrant des modules adaptés, tels que la gestion des contrats, le suivi des entrepôts et des matériaux, ainsi que la gestion des factures et quittances liées aux projets.
En connectant un frontend moderne et ergonomique avec un backend robuste via une API sécurisée, l’application garantit une expérience fluide et réactive, tout en assurant la sécurité et la fiabilité des données. Ainsi, elle libère les dirigeants et leurs équipes des contraintes administratives pour qu’ils puissent se concentrer sur leur cœur de métier et la croissance de leur entreprise.

**Résumé exécutif :**
"AKARA est une application complète de gestion destinée aux petites et moyennes entreprises, avec un focus particulier sur les besoins du secteur du BTP. Elle centralise la gestion des stocks, des employés, des clients, des fournisseurs, des contrats, des factures, et bien plus, dans une interface moderne et intuitive. Grâce à une architecture frontend React et un backend API sécurisé, AKARA offre une expérience fluide et évolutive."

## 2. Fonctionnalités principales

Les fonctionnalités clés de votre application sont :

- **Gestion des stocks** : Suivi des niveaux de stock, alertes de réapprovisionnement, et gestion des commandes.
- **Gestion des stocks et entrepôts** : Suivi précis des articles, mouvements, et alertes de réapprovisionnement.
- **Gestion des employés** : Suivi des informations, planning, et accès sécurisé.
- **Gestion des fournisseurs et clients** : Base de données complète avec historique des transactions.
- **Gestion des contrats et factures** : Création, modification, et suivi des documents contractuels et financiers.
- **Tableaux de bord analytiques** : Visualisation des indicateurs clés pour piloter l’activité.
- **Mode sombre** : Interface adaptée pour un confort visuel optimal.
- **Système de notifications et toasts** : Alertes en temps réel pour les erreurs, succès, ou informations importantes.

## 3. Architecture de l'application

"L'application AKARA est construite sur une architecture client-serveur. Le frontend, développé en React, communique avec le backend via une API RESTful. Le backend, construit avec PHP natif, gère la logique métier et l'accès à la base de données. Cette séparation permet une évolutivité et une maintenance facilitées."

**Points clés :**

- **Frontend** : Application React avec gestion d’état via hooks, styles avec Tailwind CSS, et composants modulaires.
- **Backend** : Serveur PHP exposant une API REST sécurisée, gestion des données via deux bases de données (MySQL et MongoDB pour la communication).
- **Communication** : Le frontend interagit avec le backend via des appels API fetch sécurisés avec gestion des sessions sécurisées par Token JWT et protection contre les CRSF.
- **Authentification** : Système d’authentification avec gestion des sessions et protection des routes.
- **Déploiement** : Application déployée sur un serveur cloud avec HTTPS et gestion des CORS.

## 4. Technologies utilisées

Pour le développement de l'application, les technologies et outils suivants ont été utilisés à savoir :

- **Frontend** : React, Tailwind CSS
- **Backend** : PHP 8.2
- **Base de données** : MySQL et MongoDB
- **Outils de développement** : Git, Docker, Postman

## 5. Installation et configuration

Les instructions pour installer et configurer l'application sont :

**Prérequis :**
Node.js (version recommandée)
npm ou yarn
Bases de données configurée (MySQL et MongoDB)
Accès au serveur backend

**Etapes :**

1. Clonez le dépôt : `git clone <URL-du-dépôt>`
2. Accédez au répertoire du projet : `cd nom-du-projet`
3. Installez les dépendances : `npm install`
4. Démarrez le serveur backend : `npm run start` (dans le dossier backend)
5. Démarrez le frontend : `npm start` (dans le dossier frontend)
6. Ouvrez votre navigateur à l'adresse [http://localhost:3000](http://localhost:3000).

## 6. Utilisation de l'application

"Après avoir démarré l'application, vous serez accueilli par le tableau de bord. À partir de là, vous pouvez naviguer vers les différentes sections comme la gestion des stocks, des employés, etc. Chaque section est conçue pour être intuitive, avec des instructions claires pour chaque action."

## 7. API Documentation

Les points de terminaison disponibles, les méthodes HTTP, et les exemples de requêtes/réponses sont :

- **GET /api/stocks** : Récupère la liste des articles en stock.
- **POST /api/employees** : Ajoute un nouvel employé.
- **PUT /api/customers/:id** : Met à jour les informations d'un client.

---

### 7.1. GET — Récupérer des données

**Usage** : Obtenir une ressource ou une liste de ressources.

**Exemples** : Récupérer la liste des employés

---

GET /api/employees HTTP/1.1
Host: api.akara-app.com
Authorization: Bearer <token>
Accept: application/json

**Réponse (200 OK)** :

```json
[
  {
    "id": "123",
    "name": "Jean Dupont",
    "role": "Chef de chantier",
    "email": "jean.dupont@example.com"
  },
  {
    "id": "124",
    "name": "Marie Curie",
    "role": "Comptable",
    "email": "marie.curie@example.com"
  }
]
```

---

### 7.2. POST — Créer une nouvelle ressource

**Usage** : Ajouter un nouvel élément (ex : un client, un stock).

**Exemple** : Ajouter un nouveau client

POST /api/clients HTTP/1.1
Host: api.akara-app.com
Content-Type: application/json
Authorization: Bearer <token>

```json
{
  "name": "Entreprise BTP SAS",
  "contact": "contact@btpsas.com",
  "phone": "0123456789",
  "address": "12 rue des Travaux, Paris"
}
```

**Réponse (201 Created)** :

```json
{
  "id": "789",
  "name": "Entreprise BTP SAS",
  "contact": "contact@btpsas.com",
  "phone": "0123456789",
  "address": "12 rue des Travaux, Paris",
  "createdAt": "2025-07-10T10:00:00Z"
}
```

---

### 7.3. PUT — Mettre à jour une ressource existante

**Usage** : Modifier complètement une ressource identifiée.

**Exemple** : Mettre à jour les informations d’un employé

PUT /api/employees/123 HTTP/1.1
Host: api.akara-app.com
Content-Type: application/json
Authorization: Bearer <token>

```json
{
  "name": "Jean Dupont",
  "role": "Chef de projet",
  "email": "jean.dupont@example.com"
}
```

**Réponse (200 OK)** :

```json
{
  "id": "123",
  "name": "Jean Dupont",
  "role": "Chef de projet",
  "email": "jean.dupont@example.com",
  "updatedAt": "2025-07-10T11:00:00Z"
}
```

---

### 7.4. PATCH — Mise à jour partielle d’une ressource

**Usage** : Modifier partiellement une ressource (ex : changer uniquement le rôle).

**Exemple** : Modifier le rôle d’un employé

PATCH /api/employees/123 HTTP/1.1
Host: api.akara-app.com
Content-Type: application/json
Authorization: Bearer <token>

```json
{
  "role": "Directeur technique"
}
```

**Réponse (200 OK)** :

```json
{
  "id": "123",
  "name": "Jean Dupont",
  "role": "Directeur technique",
  "email": "jean.dupont@example.com",
  "updatedAt": "2025-07-10T11:30:00Z"
}
```

---

### 7.5. DELETE — Supprimer une ressource

**Usage** : Supprimer un élément identifié.

**Exemple** : Supprimer un client

DELETE /api/clients/789 HTTP/1.1
Host: api.akara-app.com
Authorization: Bearer <token>

**Réponse (204 No Content)** : Pas de corps, indique que la suppression a réussi.

---

### 7.6. POST — Authentification (exemple spécifique)

**Exemple** : Connexion utilisateur

POST /api/login HTTP/1.1
Host: api.akara-app.com
Content-Type: application/json

```json
{
  "email": "user@example.com",
  "password": "motdepasse123"
}
```

**Réponse (200 OK)** :

```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": "u123",
    "name": "Utilisateur Exemple",
    "role": "Administrateur"
  }
}
```

---

## Remarques générales

- Les réponses incluent généralement un code HTTP indiquant le succès ou l’erreur (200, 201, 204, 400, 401, 404, 500…).
- Le format JSON est privilégié pour les échanges.

### 8. Conclusion

"Notre application de gestion de PME batptisée AKARA est un outil puissant pour optimiser vos opérations commerciales. Avec une interface conviviale et des fonctionnalités robustes, elle vous aide à gérer efficacement votre entreprise. Nous vous invitons à l'essayer et à découvrir comment elle peut transformer votre gestion quotidienne."

---

### Autres éléments de documentation nécessaires

1. **Guide de contribution** : Permettre à d'autres développeurs de contribuer au projet AKARA.

2. **FAQ** : Réponses aux questions fréquentes que posent les utilisateurs sur l'application.

3. **Changelog** : Journal des modifications pour suivre les mises à jour et les nouvelles fonctionnalités.

4. **Contact** : Informations de contact pour le support technique ou les questions.

5. **Licences** : Licence MIT version 1.0.0.
