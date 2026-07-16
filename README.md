# CapsulePHP

Framework PHP minimal, sans dépendance runtime. Architecture inspirée d'Astro : pages composées de sections, layouts automatiques, API déclarative, **dashboard développeur** intégré.

**Documentation complète :** [doc.md](doc.md)

## Prérequis

- PHP 8.2+
- SQLite 3 + PDO
- Composer (dev/tests)

## Démarrage rapide

```bash
make deps    # extensions PHP, SQLite, Composer (première fois)
make init    # data/, SQLite seed, bin/
make dev
```

- Site public : [http://localhost:8080](http://localhost:8080)
- **Dashboard développeur** : [http://localhost:8080/dev](http://localhost:8080/dev)
- Healthcheck API : [http://localhost:8080/api/health](http://localhost:8080/api/health)

Optionnel dans `.env` :

```env
DEV_PASSWORD=votre-mot-de-passe
```

Sans `DEV_PASSWORD` en mode dev, `/dev` est accessible sans login.

Optionnel pour l'espace client :

```env
CLIENT_PASSWORD=mot-de-passe-client
```

- **Espace client** : [http://localhost:8080/admin](http://localhost:8080/admin)
- Sans `CLIENT_PASSWORD` en mode dev, `/admin` est accessible sans login.

## Créer un site complet (parcours)

1. **Initialiser** — `make init` puis `make dev` ; ouvrir `/dev`.
2. **Pages** — `/dev/pages` : créer `contact`, ajouter sections (hero, features, cta), publier.
3. **Sections** — `/dev/pages/{slug}` : variantes, visibilité, réordonnancement ; aperçu live via iframe.
4. **Site** — `/dev/site` : nom, footer, navigation (pages + liens + boutons), CTA header, partials.
5. **Thème** — `/dev/theme` : couleurs et polices ; aperçu immédiat sur `/dev/preview/_`.
6. **Vérifier** — lien « Voir le site ↗ » ; `make test` pour la suite PHPUnit.

```bash
make test         # PHPUnit
bin/db reset      # Repartir du seed (nav/thème par défaut)
```

## Structure

```
public/              Document root (index.php + assets)
resources/
  sections/          Bibliothèque de blocs HTML (hero, cta…)
  layouts/           Layouts site public
  dev/               UI dashboard développeur (/dev)
app/Http/Dev/        Contrôleurs dashboard développeur
src/                 Framework Capsule
data/                SQLite (pages, thème)
migrations/          Schéma + seed site par défaut
```

## En bref

| Besoin | Action |
|--------|--------|
| Builder le site | `/dev` → pages, sections, thème |
| Contenu pages | SQLite (`pages.sections` JSON) |
| Nouvelle section (framework) | `resources/sections/{type}/{variant}.html` + `registry.yaml` |
| CSS section | `public/assets/css/sections/{type}/{variant}.css` |
| CSS layout | `public/assets/css/layouts/{layout}.css` |
| Thème global | `/dev/theme` ou `site_settings` en DB |
| Backup | `bin/site export > backup.json` |
| Restaurer | `bin/site import < backup.json` |
| Endpoint API | `config/routes.php` + `app/Http/` |
| Réinitialiser la DB | `bin/db reset` |

## Dashboards

| URL | Rôle |
|-----|------|
| `/dev` | Développeur : structure, sections, variantes, thème, permissions client |
| `/admin` | Client : identité du site + édition des champs autorisés (édition complète en étape 3) |

## Commandes utiles

```bash
make dev          # Serveur local
make test         # PHPUnit
bin/db init       # Schéma + site par défaut
bin/db reset      # Réinitialise SQLite
bin/site export   # Export JSON pages + thème
```

## Licence

Projet pédagogique / squelette. Adapter selon vos besoins.
