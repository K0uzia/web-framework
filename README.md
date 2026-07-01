# CapsulePHP

Framework PHP minimal, sans dépendance runtime. Architecture inspirée d'Astro : pages fichier-based, layouts automatiques, API déclarative.

**Documentation complète :** [doc.md](doc.md)

## Prérequis

- PHP 8.2+
- SQLite 3 (optionnel, pour `bin/db`)
- Composer (dev/tests)

## Démarrage rapide

```bash
make init
make dev
```

Ouvrir [http://localhost:8080](http://localhost:8080)

Healthcheck API : [http://localhost:8080/api/health](http://localhost:8080/api/health)

## Structure

```
public/          Document root (index.php + assets)
resources/       Front source (pages, layouts, partials)
app/Http/        Contrôleurs API
src/             Framework Capsule
config/          Configuration et DI
```

## En bref

| Besoin | Action |
|--------|--------|
| Nouvelle page | `resources/pages/contact.yaml` + `contact.html` |
| CSS layout | `resources/styles/layouts/{layout}.css` |
| CSS page | `resources/styles/pages/{slug}/{slug}.css` |
| CSS section | `resources/styles/pages/{slug}/{section}.css` + `styles_sections` dans le YAML |
| CSS partial | `resources/styles/partials/{nom}.css` (auto si `{{> nom.html}}`) |
| Publier le CSS | `make styles` → copie vers `public/assets/css/` |
| Contenu éditable (dashboard) | `resources/pages/*.yaml` |
| Structure HTML | `resources/pages/*.html` |
| Nouveau layout | `resources/layouts/blog.html` |
| Partial | `resources/partials/header.html` |
| Endpoint API | `config/routes.php` + `app/Http/` |
| Config app | `config/app.php` |
| Base SQLite | `bin/db init` |

## Commandes utiles

```bash
make dev          # Serveur local
make test         # PHPUnit
make doc          # Ouvre doc.md
bin/db init       # Initialise SQLite
```

## Licence

Projet pédagogique / squelette. Adapter selon vos besoins.
