# Guide de contribution — Ina Zaoui

## Sommaire

1. [Prérequis](#prérequis)
2. [Conventions de nommage](#conventions-de-nommage)
3. [Procédure de contribution](#procédure-de-contribution)
4. [Politique de validation](#politique-de-validation)
5. [Bonnes pratiques](#bonnes-pratiques)

---

## Prérequis

Avant de contribuer, assurez-vous d'avoir correctement installé le projet en suivant le [README](README.md).

---

## Conventions de nommage

### Branches

Le nom d'une branche doit suivre le format suivant :

```
<type>/<description-courte>
```

| Type | Usage |
|---|---|
| `feature/` | Nouvelle fonctionnalité |
| `fix/` | Correction d'un bug |
| `refactor/` | Refactorisation sans changement de comportement |
| `test/` | Ajout ou correction de tests |
| `docs/` | Documentation uniquement |
| `ci/` | Intégration continue / pipeline |

**Exemples :**

```
feature/gestion-invites
fix/validation-upload-media
docs/contributing
```

- Utiliser le kebab-case (minuscules, tirets)
- Être concis mais explicite
- Éviter les noms génériques (`fix/bug`, `feature/update`)

### Commits

Les messages de commit suivent la convention [Conventional Commits](https://www.conventionalcommits.org/fr/) :

```
<type>(<scope>): <description courte>
```

| Type | Usage |
|---|---|
| `feat` | Nouvelle fonctionnalité |
| `fix` | Correction de bug |
| `refactor` | Refactorisation |
| `test` | Ajout / modification de tests |
| `docs` | Documentation |
| `ci` | Pipeline CI/CD |
| `chore` | Tâche technique sans impact fonctionnel |

**Exemples :**

```
feat(media): ajouter la validation du type et du poids du fichier
fix(auth): corriger le chargement des utilisateurs depuis la BDD
test(guest): ajouter les tests fonctionnels de la page invités
docs(readme): mettre à jour les instructions d'installation
```

- Le scope (entre parenthèses) est facultatif mais recommandé
- La description est en minuscules, sans point final
- En français

---

## Procédure de contribution

### 1. Créer une branche

Toujours partir de `master` à jour :

```bash
git checkout master
git pull origin master
git checkout -b feature/ma-fonctionnalite
```

### 2. Développer

- Un commit = une modification cohérente (ne pas mélanger plusieurs sujets)
- Committer régulièrement plutôt qu'en un seul gros commit final
- Vérifier que PHPStan ne remonte pas d'erreur avant de pousser :

```bash
composer phpstan
```

### 3. Écrire ou mettre à jour les tests

Toute nouvelle fonctionnalité ou correction de bug doit être accompagnée de tests.  
Le taux de couverture global doit rester **supérieur à 70 %**.

```bash
composer db-test   # recrée la BDD de test et charge les fixtures
composer test      # lance les tests avec rapport de couverture
```

Les rapports de couverture sont générés dans `var/log/test/test-coverage/`.

### 4. Pousser et ouvrir une Pull Request

```bash
git push origin feature/ma-fonctionnalite
```

Ouvrir une Pull Request sur GitHub vers `master` en remplissant :

- **Titre** : clair et concis (suit le format Conventional Commits)
- **Description** : ce que fait la PR, pourquoi, et comment la tester
- **Lien** vers l'issue associée si applicable

### 5. Soumettre un problème ou une idée

Pour signaler un bug ou proposer une fonctionnalité, ouvrir une **Issue** GitHub en précisant :

- **Bug** : étapes pour reproduire, comportement attendu vs observé, environnement (navigateur, OS)
- **Fonctionnalité** : contexte, besoin utilisateur, proposition de solution

---

## Politique de validation

Une Pull Request est fusionnable si :

- La pipeline CI passe (tests + PHPStan)
- Le taux de couverture de code reste au-dessus de 70 %
- Le code respecte les conventions décrites dans ce document
- La PR a été relue et approuvée

Aucun merge direct sur `master` sans passer par une Pull Request.

---

## Bonnes pratiques

### Analyse statique

PHPStan est configuré au niveau 5. Aucune erreur ne doit subsister avant de proposer une PR :

```bash
composer phpstan
```

### Tests

- Utiliser **PHPUnit** pour les tests unitaires et fonctionnels
- Les fixtures (données de test) sont dans `src/DataFixtures/`
- Préférer des tests qui vérifient le comportement métier plutôt que l'implémentation interne

### Sécurité

- Ne jamais committer de fichier `.env.local` ou contenant des secrets
- Valider toutes les entrées utilisateur côté serveur (pas uniquement côté client)
- Les fichiers uploadés doivent être validés en type (image uniquement) et en poids (≤ 2 Mo)

### Base de données

- Toute modification de schéma passe par une migration Doctrine :

```bash
symfony console doctrine:migrations:diff
symfony console doctrine:migrations:migrate
```

- Ne jamais modifier directement la base de données en production sans migration

### Environnement

- Utiliser Docker pour la base de données locale (`docker compose up -d`)
- Ne pas modifier les fichiers `.env` versionnés — utiliser `.env.local` pour les surcharges locales
