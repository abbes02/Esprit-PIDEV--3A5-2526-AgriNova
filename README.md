# AgrinovaWebapp - Documentation Complète

## 📋 Table des Matières

1. [Introduction](#introduction)
2. [Architecture du Projet](#architecture-du-projet)
3. [Commandes Console Symfony](#commandes-console-symfony)
4. [Les Entités (Entities)](#les-entités-entities)
   - [Utilisateur](#1-utilisateur)
   - [Materiel](#2-materiel)
   - [Location](#3-location)
   - [Panne](#4-panne)
5. [Contrôle de Saisie (Validation)](#contrôle-de-saisie-validation)
6. [Les Formulaires (Forms)](#les-formulaires-forms)
7. [Les Contrôleurs (Controllers)](#les-contrôleurs-controllers)
8. [Les Services](#les-services)
9. [Les Repositories](#les-repositories)
10. [Relations entre Entités](#relations-entre-entités)
11. [Guide d'Installation](#guide-dinstallation)

---

## Introduction

**AgrinovaWebapp** est une application web Symfony dédiée à la gestion de location de matériel agricole. Elle permet aux utilisateurs de :

- Publier du matériel agricole à louer (Marketplace)
- Effectuer des réservations de matériel
- Signaler des pannes sur le matériel
- Gérer le cycle de vie des locations

L'application suit l'architecture **MVC (Model-View-Controller)** de Symfony et implémente les bonnes pratiques de validation de formulaires comme présenté dans le workshop "Les Formulaires & Contrôle de Saisie".

---

## Architecture du Projet

```
src/
├── Controller/         # Contrôleurs (gestion des requêtes HTTP)
│   ├── DashboardController.php
│   ├── LocationController.php
│   ├── MaterielController.php
│   ├── PanneController.php
│   └── UserSwitchController.php
├── Entity/             # Entités Doctrine (modèles de données)
│   ├── Location.php
│   ├── Materiel.php
│   ├── Panne.php
│   └── Utilisateur.php
├── Form/               # Types de formulaires
│   ├── LocationType.php
│   ├── MaterielType.php
│   └── PanneType.php
├── Repository/         # Repositories (accès aux données)
│   ├── LocationRepository.php
│   ├── MaterielRepository.php
│   ├── PanneRepository.php
│   └── UtilisateurRepository.php
├── Service/            # Services métier
│   ├── LocationStatusManager.php
│   ├── MaterielStateManager.php
│   └── UserContext.php
└── Kernel.php
```

---

## Commandes Console Symfony

### 🚀 Commandes de Base

```bash
# Afficher toutes les commandes disponibles
php bin/console list

# Obtenir de l'aide sur une commande spécifique
php bin/console help <commande>

# Vérifier la version de Symfony
php bin/console --version
```

### 📦 Installation et Configuration

```bash
# Installer les dépendances PHP
composer install

# Installer les dépendances JavaScript (si applicable)
npm install

# Vérifier les prérequis Symfony
php bin/console check:requirements

# Vider le cache
php bin/console cache:clear

# Vider le cache en production
php bin/console cache:clear --env=prod
```

### 🗄️ Commandes Base de Données (Doctrine)

```bash
# Créer la base de données
php bin/console doctrine:database:create

# Supprimer la base de données
php bin/console doctrine:database:drop --force

# Afficher le schéma SQL sans l'exécuter
php bin/console doctrine:schema:update --dump-sql

# Mettre à jour le schéma (ATTENTION: à éviter en production)
php bin/console doctrine:schema:update --force

# Valider le mapping des entités
php bin/console doctrine:schema:validate
```

### 🔄 Commandes Migrations

```bash
# Générer une nouvelle migration
php bin/console make:migration

# Voir le statut des migrations
php bin/console doctrine:migrations:status

# Exécuter les migrations en attente
php bin/console doctrine:migrations:migrate

# Exécuter une migration spécifique
php bin/console doctrine:migrations:migrate 'DoctrineMigrations\Version20240101120000'

# Annuler la dernière migration
php bin/console doctrine:migrations:migrate prev

# Afficher le SQL d'une migration sans l'exécuter
php bin/console doctrine:migrations:migrate --dry-run
```

### 🏗️ Commandes Make (Génération de Code)

```bash
# Créer une nouvelle entité
php bin/console make:entity

# Créer une nouvelle entité avec des champs interactifs
php bin/console make:entity Materiel

# Modifier une entité existante (ajouter des champs)
php bin/console make:entity Materiel  # Répondre "yes" pour mettre à jour

# Créer un contrôleur CRUD complet
php bin/console make:crud Materiel

# Créer un contrôleur simple
php bin/console make:controller MaterielController

# Créer un formulaire
php bin/console make:form MaterielType

# Créer un formulaire basé sur une entité
php bin/console make:form MaterielType Materiel

# Créer un repository
php bin/console make:repository MaterielRepository

# Créer un service
php bin/console make:service MaterielStateManager

# Créer un validateur personnalisé
php bin/console make:validator NomValide
```

### 🔍 Commandes de Debug

```bash
# Lister toutes les routes
php bin/console debug:router

# Afficher les détails d'une route spécifique
php bin/console debug:router app_materiel_index

# Rechercher une route par pattern
php bin/console debug:router --show-controllers | grep materiel

# Lister tous les services du conteneur
php bin/console debug:container

# Rechercher un service spécifique
php bin/console debug:container MaterielRepository

# Afficher les paramètres de configuration
php bin/console debug:config doctrine

# Déboguer les formulaires
php bin/console debug:form

# Déboguer les événements
php bin/console debug:event-dispatcher

# Afficher les variables d'environnement
php bin/console debug:dotenv

# Vérifier la configuration Twig
php bin/console debug:twig
```

### ✅ Commandes de Validation

```bash
# Valider le mapping Doctrine
php bin/console doctrine:mapping:info

# Valider le schéma de base de données
php bin/console doctrine:schema:validate

# Vérifier la configuration du projet
php bin/console lint:yaml config/
php bin/console lint:twig templates/
php bin/console lint:container
```

### 🌐 Serveur de Développement

```bash
# Démarrer le serveur Symfony (recommandé)
symfony server:start

# Démarrer en arrière-plan
symfony server:start -d

# Arrêter le serveur
symfony server:stop

# Alternative: serveur PHP intégré
php -S localhost:8000 -t public/

# Serveur avec Symfony CLI sur un port spécifique
symfony server:start --port=8080
```

### 📊 Commandes Fixtures (Données de Test)

```bash
# Charger les fixtures
php bin/console doctrine:fixtures:load

# Charger sans confirmation
php bin/console doctrine:fixtures:load --no-interaction

# Ajouter des fixtures sans purger
php bin/console doctrine:fixtures:load --append

# Charger un groupe spécifique de fixtures
php bin/console doctrine:fixtures:load --group=dev
```

### 🧪 Commandes de Test

```bash
# Exécuter tous les tests
php bin/phpunit

# Exécuter les tests d'un dossier spécifique
php bin/phpunit tests/Entity/

# Exécuter un fichier de test spécifique
php bin/phpunit tests/Entity/MaterielTest.php

# Exécuter avec couverture de code
php bin/phpunit --coverage-html coverage/
```

### 📝 Commandes Spécifiques au Projet

```bash
# Exemple: Commande personnalisée pour rafraîchir les états des matériels
# php bin/console app:materiel:refresh-states

# Exemple: Commande pour nettoyer les locations expirées
# php bin/console app:location:cleanup
```

### 🔧 Workflow Complet de Développement

```bash
# 1. Cloner et installer
git clone <repository>
cd AgrinovaWebapp
composer install

# 2. Configurer la base de données (.env.local)
# DATABASE_URL="mysql://user:password@127.0.0.1:3306/agrinova"

# 3. Créer la base et exécuter les migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# 4. (Optionnel) Charger les données de test
php bin/console doctrine:fixtures:load

# 5. Démarrer le serveur
symfony server:start

# 6. Accéder à l'application
# http://localhost:8000
```

### 🔄 Workflow de Modification d'Entité

```bash
# 1. Modifier l'entité (ajouter un champ)
php bin/console make:entity Materiel
# > Ajouter le champ "marque" de type string

# 2. Générer la migration
php bin/console make:migration

# 3. Vérifier le SQL généré
php bin/console doctrine:migrations:migrate --dry-run

# 4. Exécuter la migration
php bin/console doctrine:migrations:migrate

# 5. Vider le cache
php bin/console cache:clear
```

### 📋 Résumé des Commandes Essentielles

| Commande | Description |
|----------|-------------|
| `php bin/console cache:clear` | Vider le cache |
| `php bin/console doctrine:database:create` | Créer la BDD |
| `php bin/console make:migration` | Créer une migration |
| `php bin/console doctrine:migrations:migrate` | Exécuter les migrations |
| `php bin/console make:entity` | Créer/modifier une entité |
| `php bin/console make:crud` | Générer un CRUD complet |
| `php bin/console make:form` | Créer un formulaire |
| `php bin/console debug:router` | Lister les routes |
| `php bin/console doctrine:schema:validate` | Valider le schéma |
| `symfony server:start` | Démarrer le serveur |

---

## Les Entités (Entities)

### 1. Utilisateur

**Fichier:** `src/Entity/Utilisateur.php`

**Description:** Représente un utilisateur de l'application (agriculteur, propriétaire ou administrateur).

#### Attributs

| Attribut | Type | Nullable | Description |
|----------|------|----------|-------------|
| `id` | integer | Non | Identifiant unique (auto-généré) |
| `nom` | string(255) | Non | Nom de famille |
| `prenom` | string(255) | Non | Prénom |
| `email` | string(255) | Non | Email (unique) |
| `role` | string(50) | Non | Rôle de l'utilisateur |

#### Rôles Disponibles

```php
public const ROLE_AGRICULTEUR = 'agriculteur';
public const ROLE_PROPRIETAIRE = 'proprietaire';
public const ROLE_ADMIN = 'admin';
```

#### Contraintes de Validation (Contrôle de Saisie)

```php
// Nom
#[Assert\NotBlank(message: 'Le nom est obligatoire.')]
#[Assert\Length(
    min: 2,
    max: 255,
    minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.',
    maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'
)]
#[Assert\Regex(
    pattern: '/^[a-zA-ZÀ-ÿ\s\-\']+$/',
    message: 'Le nom ne doit contenir que des lettres, espaces, tirets et apostrophes.'
)]

// Prénom
#[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
#[Assert\Length(min: 2, max: 255)]
#[Assert\Regex(pattern: '/^[a-zA-ZÀ-ÿ\s\-\']+$/')]

// Email
#[Assert\NotBlank(message: 'L\'email est obligatoire.')]
#[Assert\Email(message: 'L\'email "{{ value }}" n\'est pas un email valide.')]
#[Assert\Length(max: 255)]

// Rôle
#[Assert\NotBlank(message: 'Le rôle est obligatoire.')]
#[Assert\Choice(choices: self::ROLE_VALUES)]
```

#### Relations

- **OneToMany** vers `Materiel` (propriétaire de matériels)
- **OneToMany** vers `Location` (locations effectuées)

---

### 2. Materiel

**Fichier:** `src/Entity/Materiel.php`

**Description:** Représente un équipement agricole pouvant être mis en location.

#### Attributs

| Attribut | Type | Nullable | Description |
|----------|------|----------|-------------|
| `id` | integer | Non | Identifiant unique |
| `nom` | string(255) | Non | Nom du matériel |
| `type` | string(100) | Non | Type d'équipement |
| `description` | text | Oui | Description détaillée |
| `prixLocation` | float | Non | Prix de location par jour (TND) |
| `etat` | string(50) | Oui | État actuel du matériel |
| `imageUrl` | string(255) | Oui | URL de l'image |
| `dateAjout` | datetime | Oui | Date d'ajout |

#### Types de Matériel Disponibles

```php
public const TYPE_TRACTOR = 'tractor';      // Tracteur
public const TYPE_HARVESTER = 'harvester';  // Moissonneuse
public const TYPE_SEEDER = 'seeder';        // Semoir
public const TYPE_SPRAYER = 'sprayer';      // Pulvérisateur
public const TYPE_PLOW = 'plow';            // Charrue
public const TYPE_TRAILER = 'trailer';      // Remorque
public const TYPE_PUMP = 'pump';            // Pompe
public const TYPE_IRRIGATION = 'irrigation'; // Irrigation
public const TYPE_TOOL = 'tool';            // Outillage
public const TYPE_OTHER = 'other';          // Autre
```

#### États Possibles

```php
public const ETAT_DISPONIBLE = 'Disponible';  // Disponible à la location
public const ETAT_LOUE = 'Loue';              // Actuellement loué
public const ETAT_EN_PANNE = 'En Panne';      // En panne, non disponible
```

#### Contraintes de Validation (Contrôle de Saisie)

```php
// Nom
#[Assert\NotBlank(message: 'Le nom du matériel est obligatoire.')]
#[Assert\Length(
    min: 2,
    max: 255,
    minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.',
    maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'
)]

// Type
#[Assert\NotBlank(message: 'Le type de matériel est obligatoire.')]
#[Assert\Choice(
    choices: self::TYPE_VALUES,
    message: 'Le type sélectionné n\'est pas valide.'
)]

// Description
#[Assert\Length(
    max: 2000,
    maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères.'
)]

// Prix de location
#[Assert\NotBlank(message: 'Le prix de location est obligatoire.')]
#[Assert\Positive(message: 'Le prix de location doit être un nombre positif.')]
#[Assert\LessThanOrEqual(
    value: 100000,
    message: 'Le prix de location ne peut pas dépasser {{ compared_value }} TND.'
)]

// État
#[Assert\Choice(
    choices: self::ETAT_VALUES,
    message: 'L\'état sélectionné n\'est pas valide.'
)]
```

#### Relations

- **ManyToOne** vers `Utilisateur` (propriétaire)
- **OneToMany** vers `Location` (locations associées)
- **OneToMany** vers `Panne` (pannes signalées)

---

### 3. Location

**Fichier:** `src/Entity/Location.php`

**Description:** Représente une réservation/location de matériel agricole.

#### Attributs

| Attribut | Type | Nullable | Description |
|----------|------|----------|-------------|
| `id` | integer | Non | Identifiant unique |
| `dateDebut` | date | Non | Date de début de location |
| `dateFin` | date | Non | Date de fin de location |
| `montantTotal` | float | Non | Montant total de la location (TND) |
| `statut` | string(50) | Oui | Statut de la location |

#### Statuts Possibles

```php
public const STATUT_EN_COURS = 'En cours';   // Location active
public const STATUT_TERMINEE = 'Terminee';   // Location terminée
public const STATUT_ANNULEE = 'Annulee';     // Location annulée
```

#### Contraintes de Validation (Contrôle de Saisie)

```php
// Date de début
#[Assert\NotBlank(message: 'La date de début est obligatoire.')]
#[Assert\Type(\DateTimeInterface::class, message: 'La date de début n\'est pas valide.')]

// Date de fin
#[Assert\NotBlank(message: 'La date de fin est obligatoire.')]
#[Assert\Type(\DateTimeInterface::class, message: 'La date de fin n\'est pas valide.')]
#[Assert\GreaterThanOrEqual(
    propertyPath: 'dateDebut',
    message: 'La date de fin doit être supérieure ou égale à la date de début.'
)]

// Montant total
#[Assert\NotBlank(message: 'Le montant total est obligatoire.')]
#[Assert\Positive(message: 'Le montant total doit être un nombre positif.')]

// Statut
#[Assert\Choice(
    choices: self::STATUT_VALUES,
    message: 'Le statut sélectionné n\'est pas valide.'
)]

// Matériel
#[Assert\NotNull(message: 'Le matériel est obligatoire.')]

// Utilisateur
#[Assert\NotNull(message: 'L\'utilisateur est obligatoire.')]
```

#### Validation Callback (Règles Métier)

```php
#[Assert\Callback]
public function validateDateRange(ExecutionContextInterface $context): void
{
    $today = new \DateTimeImmutable('today');

    // Nouvelle location: date de début doit être aujourd'hui ou dans le futur
    if ($this->id === null && $this->dateDebut && $this->dateDebut < $today) {
        $context->buildViolation('La date de debut doit etre aujourd\'hui ou dans le futur.')
            ->atPath('dateDebut')
            ->addViolation();
    }

    // Date de fin doit être >= date de début
    if ($this->dateDebut && $this->dateFin && $this->dateFin < $this->dateDebut) {
        $context->buildViolation('La date de fin doit etre superieure ou egale a la date de debut.')
            ->atPath('dateFin')
            ->addViolation();
    }

    // Nouvelle location: le matériel doit être disponible
    if ($this->id === null && $this->materiel && $this->materiel->getEtat() !== Materiel::ETAT_DISPONIBLE) {
        $context->buildViolation('Le materiel selectionne n\'est pas disponible actuellement.')
            ->atPath('materiel')
            ->addViolation();
    }

    // Le montant doit être >= au prix de location
    if ($this->materiel && $this->montantTotal !== null) {
        $minimumAmount = (float) ($this->materiel->getPrixLocation() ?? 0.0);
        if ($minimumAmount > 0 && $this->montantTotal < $minimumAmount) {
            $context->buildViolation('Le montant total doit etre superieur ou egal au prix de location du materiel.')
                ->atPath('montantTotal')
                ->addViolation();
        }
    }
}
```

#### Relations

- **ManyToOne** vers `Materiel` (matériel loué)
- **ManyToOne** vers `Utilisateur` (locataire)

---

### 4. Panne

**Fichier:** `src/Entity/Panne.php`

**Description:** Représente un signalement de panne sur un matériel.

#### Attributs

| Attribut | Type | Nullable | Description |
|----------|------|----------|-------------|
| `id` | integer | Non | Identifiant unique |
| `descriptionPanne` | text | Non | Description du problème |
| `datePanne` | date | Non | Date du signalement |
| `dateReparation` | date | Oui | Date de réparation |
| `coutReparation` | float | Oui | Coût de réparation (TND) |
| `imageUrl` | string(512) | Oui | Photo de la panne |
| `diagnosticSolution` | text | Oui | Diagnostic et solution |
| `severity` | string(20) | Oui | Niveau de gravité |
| `panneType` | string(30) | Oui | Type de panne |
| `priority` | integer | Oui | Priorité (1-5) |
| `estimatedCost` | float | Oui | Coût estimé |
| `reportedByName` | string(255) | Oui | Nom du déclarant |

#### Niveaux de Gravité

```php
public const SEVERITY_MINOR = 'minor';      // Mineur
public const SEVERITY_MAJOR = 'major';      // Majeur
public const SEVERITY_CRITICAL = 'critical'; // Critique
```

#### Types de Panne

```php
public const TYPE_MECHANICAL = 'mechanical';   // Mécanique
public const TYPE_ELECTRICAL = 'electrical';   // Électrique
public const TYPE_HYDRAULIC = 'hydraulic';     // Hydraulique
public const TYPE_STRUCTURAL = 'structural';   // Structure
public const TYPE_ENGINE = 'engine';           // Moteur
public const TYPE_TIRE = 'tire';               // Pneu/Roue
public const TYPE_OTHER = 'other';             // Autre
```

#### Contraintes de Validation (Contrôle de Saisie)

```php
// Description de la panne
#[Assert\NotBlank(message: 'La description de la panne est obligatoire.')]
#[Assert\Length(
    min: 10,
    max: 2000,
    minMessage: 'La description doit contenir au moins {{ limit }} caractères.',
    maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères.'
)]

// Date de panne
#[Assert\NotBlank(message: 'La date de panne est obligatoire.')]
#[Assert\Type(\DateTimeInterface::class)]
#[Assert\LessThanOrEqual(
    value: 'today',
    message: 'La date de panne ne peut pas être dans le futur.'
)]

// Date de réparation
#[Assert\GreaterThanOrEqual(
    propertyPath: 'datePanne',
    message: 'La date de réparation doit être supérieure ou égale à la date de panne.'
)]

// Coût de réparation
#[Assert\PositiveOrZero(message: 'Le coût de réparation doit être un nombre positif ou zéro.')]
#[Assert\LessThanOrEqual(value: 1000000)]

// Gravité
#[Assert\Choice(choices: self::SEVERITY_VALUES)]

// Type de panne
#[Assert\Choice(choices: self::TYPE_VALUES)]

// Priorité
#[Assert\Range(min: 1, max: 5)]

// Matériel
#[Assert\NotNull(message: 'Le matériel concerné est obligatoire.')]
```

#### Validation Callback (Règles Métier)

```php
#[Assert\Callback]
public function validateRepairDate(ExecutionContextInterface $context): void
{
    $today = new \DateTimeImmutable('today');

    // Date de panne ne peut pas être dans le futur
    if ($this->datePanne && $this->datePanne > $today) {
        $context->buildViolation('La date de panne ne peut pas etre dans le futur.')
            ->atPath('datePanne')
            ->addViolation();
    }

    // Date de réparation doit être >= date de panne
    if ($this->datePanne && $this->dateReparation && $this->dateReparation < $this->datePanne) {
        $context->buildViolation('La date de reparation doit etre superieure ou egale a la date de panne.')
            ->atPath('dateReparation')
            ->addViolation();
    }
}
```

#### Relations

- **ManyToOne** vers `Materiel` (matériel concerné)

---

## Contrôle de Saisie (Validation)

### Principe du Workshop

Le contrôle de saisie dans Symfony se fait à plusieurs niveaux :

1. **Validation côté entité** : Utilisation des contraintes `Assert` de Symfony Validator
2. **Validation côté formulaire** : Contraintes dans les FormTypes
3. **Validation callback** : Règles métier complexes via `#[Assert\Callback]`
4. **Validation côté contrôleur** : Règles métier additionnelles

### Types de Contraintes Utilisées

| Contrainte | Description | Exemple |
|------------|-------------|---------|
| `NotBlank` | Champ obligatoire | `#[Assert\NotBlank(message: 'Le nom est obligatoire.')]` |
| `Length` | Longueur min/max | `#[Assert\Length(min: 2, max: 255)]` |
| `Email` | Format email valide | `#[Assert\Email(message: 'Email invalide.')]` |
| `Regex` | Expression régulière | `#[Assert\Regex(pattern: '/^[a-zA-Z]+$/')]` |
| `Choice` | Valeur parmi une liste | `#[Assert\Choice(choices: ['A', 'B'])]` |
| `Positive` | Nombre positif | `#[Assert\Positive]` |
| `PositiveOrZero` | Nombre >= 0 | `#[Assert\PositiveOrZero]` |
| `Range` | Valeur dans une plage | `#[Assert\Range(min: 1, max: 5)]` |
| `Type` | Type de données | `#[Assert\Type(\DateTimeInterface::class)]` |
| `GreaterThanOrEqual` | Comparaison | `#[Assert\GreaterThanOrEqual(propertyPath: 'dateDebut')]` |
| `LessThanOrEqual` | Comparaison | `#[Assert\LessThanOrEqual(value: 'today')]` |
| `NotNull` | Non null | `#[Assert\NotNull]` |
| `Callback` | Validation personnalisée | `#[Assert\Callback]` |
| `UniqueEntity` | Unicité en BDD | `#[UniqueEntity(fields: ['email'])]` |

### Exemple Complet de Validation

```php
<?php
namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class MonEntite
{
    // Champ obligatoire avec longueur contrôlée
    #[Assert\NotBlank(message: 'Ce champ est obligatoire.')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Minimum {{ limit }} caractères.',
        maxMessage: 'Maximum {{ limit }} caractères.'
    )]
    private ?string $nom = null;

    // Champ numérique positif
    #[Assert\NotBlank]
    #[Assert\Positive(message: 'Doit être positif.')]
    #[Assert\LessThanOrEqual(value: 10000)]
    private ?float $prix = null;

    // Date avec contrainte
    #[Assert\NotBlank]
    #[Assert\LessThanOrEqual(value: 'today', message: 'Date future interdite.')]
    private ?\DateTimeInterface $date = null;

    // Validation callback pour règles complexes
    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        // Logique de validation personnalisée
        if ($this->prix > 1000 && empty($this->justification)) {
            $context->buildViolation('Justification requise pour prix > 1000.')
                ->atPath('justification')
                ->addViolation();
        }
    }
}
```

---

## Les Formulaires (Forms)

### MaterielType

**Fichier:** `src/Form/MaterielType.php`

```php
<?php
namespace App\Form;

use App\Entity\Materiel;

final class MaterielType
{
    public static function build($builder): void
    {
        $typeChoices = array_flip(Materiel::TYPE_LABELS);

        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Ex: John Deere 5100M'],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type d\'équipement',
                'choices' => $typeChoices,
                'placeholder' => 'Sélectionner un type',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('prixLocation', MoneyType::class, [
                'label' => 'Prix location (par jour)',
                'currency' => 'TND',
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image du matériel',
                'mapped' => false,
                'required' => false,
                'attr' => ['accept' => 'image/*'],
            ]);
    }
}
```

### LocationType

**Fichier:** `src/Form/LocationType.php`

```php
<?php
namespace App\Form;

use App\Entity\Materiel;

final class LocationType
{
    public static function build($builder, array $options = []): void
    {
        $materielChoices = $options['materiel_choices'] ?? [];
        $lockMateriel = (bool) ($options['lock_materiel'] ?? false);

        $builder
            ->add('materiel', EntityType::class, [
                'class' => Materiel::class,
                'choices' => $materielChoices,
                'label' => 'Matériel à louer',
                'placeholder' => 'Sélectionner un matériel disponible',
                'disabled' => $lockMateriel,
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début',
                'attr' => ['min' => (new \DateTime())->format('Y-m-d')],
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin',
            ])
            ->add('montantTotal', HiddenType::class, [
                'required' => false,
            ]);
    }
}
```

### PanneType

**Fichier:** `src/Form/PanneType.php`

```php
<?php
namespace App\Form;

use App\Entity\Materiel;
use App\Entity\Panne;

final class PanneType
{
    public static function build($builder, array $options = []): void
    {
        $materielChoices = $options['materiel_choices'] ?? [];
        $lockMateriel = (bool) ($options['lock_materiel'] ?? false);

        $severityChoices = array_flip(Panne::SEVERITY_LABELS);
        $typeChoices = array_flip(Panne::TYPE_LABELS);

        $builder
            ->add('materiel', EntityType::class, [
                'class' => Materiel::class,
                'choices' => $materielChoices,
                'label' => 'Matériel concerné',
                'placeholder' => 'Sélectionner un matériel',
                'disabled' => $lockMateriel,
            ])
            ->add('severity', ChoiceType::class, [
                'label' => 'Niveau de gravité',
                'choices' => $severityChoices,
                'expanded' => true,  // Radio buttons
            ])
            ->add('panneType', ChoiceType::class, [
                'label' => 'Type de panne',
                'choices' => $typeChoices,
                'placeholder' => 'Sélectionner le type',
            ])
            ->add('descriptionPanne', TextareaType::class, [
                'label' => 'Description du problème',
                'attr' => ['rows' => 5],
            ]);
    }
}
```

---

## Les Contrôleurs (Controllers)

### MaterielController

**Fichier:** `src/Controller/MaterielController.php`

**Routes:**
- `GET /materiel/` - Liste des matériels (index)
- `GET|POST /materiel/new` - Création d'un matériel
- `GET /materiel/{id}` - Affichage d'un matériel
- `GET|POST /materiel/{id}/edit` - Modification d'un matériel
- `POST /materiel/{id}` - Suppression d'un matériel

**Fonctionnalités:**
- Gestion CRUD complète
- Upload d'images
- Vérification de propriété (seul le propriétaire peut modifier/supprimer)
- Mise à jour automatique de l'état via `MaterielStateManager`

### LocationController

**Fichier:** `src/Controller/LocationController.php`

**Routes:**
- `GET /location/` - Liste des locations de l'utilisateur
- `GET|POST /location/new` - Création d'une location
- `GET /location/{id}` - Affichage d'une location
- `GET|POST /location/{id}/edit` - Modification d'une location
- `POST /location/{id}/cancel` - Annulation d'une location
- `POST /location/{id}` - Suppression d'une location
- `GET /location/api/booked-periods/{materielId}` - API des périodes réservées

**Règles Métier (validées dans le contrôleur):**
```php
private function applyLocationBusinessRules(...): void
{
    // Un utilisateur ne peut pas louer son propre matériel
    if ($materiel->getProprietaire()?->getId() === $currentUser->getId()) {
        $form->get('materiel')->addError(
            new FormError('Vous ne pouvez pas louer votre propre materiel.')
        );
    }

    // Vérification de disponibilité
    if ($lockMateriel && $selectedMateriel->getEtat() !== Materiel::ETAT_DISPONIBLE) {
        $form->get('materiel')->addError(
            new FormError('Ce materiel n\'est plus disponible.')
        );
    }

    // Vérification des chevauchements de dates
    if ($locationRepository->hasOverlappingLocationForMateriel(...)) {
        $form->get('materiel')->addError(
            new FormError('Ce materiel est deja reserve sur la periode choisie.')
        );
    }
}
```

### PanneController

**Fichier:** `src/Controller/PanneController.php`

**Routes:**
- `GET /panne/` - Liste des pannes accessibles
- `GET|POST /panne/new` - Signalement d'une panne
- `GET /panne/{id}` - Affichage d'une panne
- `POST /panne/{id}` - Suppression d'une panne

**Règles Métier:**
- Un utilisateur peut signaler une panne uniquement sur :
  - Ses propres matériels
  - Les matériels qu'il loue actuellement

---

## Les Services

### MaterielStateManager

**Fichier:** `src/Service/MaterielStateManager.php`

**Rôle:** Gère l'état automatique du matériel en fonction des pannes et locations.

```php
public function refreshForMateriel(Materiel $materiel, bool $flush = true): void
{
    $newEtat = Materiel::ETAT_DISPONIBLE;

    // Priorité 1: Si panne non résolue → En Panne
    if ($this->panneRepository->hasUnresolvedForMateriel($materielId)) {
        $newEtat = Materiel::ETAT_EN_PANNE;
    }
    // Priorité 2: Si location en cours → Loué
    elseif ($this->locationRepository->hasCurrentLocationForMateriel($materielId)) {
        $newEtat = Materiel::ETAT_LOUE;
    }

    $materiel->setEtat($newEtat);
}
```

### LocationStatusManager

**Fichier:** `src/Service/LocationStatusManager.php`

**Rôle:** Gère le statut automatique des locations.

```php
public function applyAutomaticStatus(Location $location): bool
{
    // Location annulée = pas de changement
    if ($location->getStatut() === Location::STATUT_ANNULEE) {
        return false;
    }

    $today = new \DateTimeImmutable('today');
    
    // Si date fin passée → Terminée, sinon → En cours
    $newStatus = $dateFin < $today
        ? Location::STATUT_TERMINEE
        : Location::STATUT_EN_COURS;

    $location->setStatut($newStatus);
    return true;
}

public function cancel(Location $location): bool
{
    $location->setStatut(Location::STATUT_ANNULEE);
    return true;
}
```

### UserContext

**Fichier:** `src/Service/UserContext.php`

**Rôle:** Gère le contexte utilisateur courant (simulation multi-utilisateurs).

---

## Les Repositories

### MaterielRepository

**Méthodes principales:**
- `findOwnedByUser(int $userId)` - Matériels possédés par un utilisateur
- `findMarketplaceItems()` - Tous les matériels du marketplace
- `findAvailableForLocation(?int $includeMaterielId)` - Matériels disponibles à la location
- `findReportableForUser(int $userId)` - Matériels sur lesquels l'utilisateur peut signaler une panne
- `isOwnedByUser(int $materielId, int $userId)` - Vérifie la propriété
- `hasLinkedRows(int $materielId)` - Vérifie les dépendances avant suppression

### LocationRepository

**Méthodes principales:**
- `findByUtilisateurId(int $userId)` - Locations d'un utilisateur
- `hasCurrentLocationForMateriel(int $materielId)` - Location active sur un matériel
- `hasOverlappingLocationForMateriel(...)` - Vérifie les chevauchements de dates
- `countActiveByUtilisateurId(int $userId)` - Compte les locations actives
- `getBookedPeriodsForMateriel(int $materielId)` - Périodes réservées (pour le calendrier)

### PanneRepository

**Méthodes principales:**
- `findByOwnerId(int $ownerId)` - Pannes des matériels d'un propriétaire
- `findAccessibleByUserId(int $userId)` - Pannes accessibles (propriétaire ou locataire)
- `hasUnresolvedForMateriel(int $materielId)` - Panne non résolue
- `countUnresolvedByOwnerId(int $ownerId)` - Compte des pannes non résolues

---

## Relations entre Entités

```
┌─────────────────┐
│   Utilisateur   │
└────────┬────────┘
         │
         │ 1:N (proprietaire)
         ▼
┌─────────────────┐       1:N       ┌─────────────────┐
│    Materiel     │◄───────────────►│      Panne      │
└────────┬────────┘                 └─────────────────┘
         │
         │ 1:N (materiel)
         ▼
┌─────────────────┐
│    Location     │
└────────┬────────┘
         │
         │ N:1 (utilisateur)
         ▼
┌─────────────────┐
│   Utilisateur   │
└─────────────────┘
```

### Diagramme des Relations

| Entité Source | Relation | Entité Cible | Description |
|---------------|----------|--------------|-------------|
| Utilisateur | OneToMany | Materiel | Un utilisateur possède plusieurs matériels |
| Utilisateur | OneToMany | Location | Un utilisateur effectue plusieurs locations |
| Materiel | ManyToOne | Utilisateur | Un matériel appartient à un propriétaire |
| Materiel | OneToMany | Location | Un matériel peut avoir plusieurs locations |
| Materiel | OneToMany | Panne | Un matériel peut avoir plusieurs pannes |
| Location | ManyToOne | Materiel | Une location concerne un matériel |
| Location | ManyToOne | Utilisateur | Une location est effectuée par un utilisateur |
| Panne | ManyToOne | Materiel | Une panne concerne un matériel |

---

## Guide d'Installation

### Prérequis

- PHP 8.2+
- Composer
- MySQL/MariaDB ou PostgreSQL
- Symfony CLI (optionnel)

### Installation

```bash
# 1. Cloner le projet
git clone <repository-url>
cd AgrinovaWebapp

# 2. Installer les dépendances
composer install

# 3. Configurer la base de données (.env.local)
# DATABASE_URL="mysql://user:password@127.0.0.1:3306/agrinova?serverVersion=8.0"

# 4. Créer la base de données
php bin/console doctrine:database:create

# 5. Exécuter les migrations
php bin/console doctrine:migrations:migrate

# 6. (Optionnel) Charger les fixtures
php bin/console doctrine:fixtures:load

# 7. Lancer le serveur
symfony server:start
# ou
php -S localhost:8000 -t public/
```

### Structure des URLs

| URL | Description |
|-----|-------------|
| `/` | Dashboard |
| `/materiel` | Marketplace des matériels |
| `/materiel/new` | Ajouter un matériel |
| `/location` | Mes locations |
| `/location/new` | Nouvelle location |
| `/panne` | Gestion des pannes |
| `/panne/new` | Signaler une panne |

---

## Conclusion

Cette application Symfony implémente les bonnes pratiques de développement :

1. **Validation robuste** avec les contraintes Symfony Validator
2. **Séparation des responsabilités** (MVC + Services)
3. **Règles métier centralisées** dans les services et callbacks
4. **Relations Doctrine** bien définies avec cascade appropriée
5. **Formulaires réutilisables** avec options de configuration
6. **Gestion automatique des états** via les services dédiés

Pour toute question ou amélioration, consultez la documentation officielle Symfony :
- [Validation](https://symfony.com/doc/current/validation.html)
- [Forms](https://symfony.com/doc/current/forms.html)
- [Doctrine ORM](https://symfony.com/doc/current/doctrine.html)
