# CapsulePHP : contexte et fonctionnement

Ce document décrit le projet CapsulePHP (dépôt wf) en texte libre : à quoi il sert, comment il est pensé, comment une requête traverse le système, comment on construit un site, et comment on le livre. Il ne contient ni schémas ni exemples de code. Pour le démarrage technique, les commandes et la structure des fichiers, se reporter au README à la racine.

---

## 1. Qu’est-ce que CapsulePHP

CapsulePHP est à la fois un framework PHP minimal et un site builder orienté blocs. L’idée centrale est simple : un site vitrine ou une landing page n’est pas une application monolithique, c’est un assemblage ordonné de sections (hero, fonctionnalités, tarifs, témoignages, contact, etc.), enveloppées dans un layout commun (en-tête, navigation, pied de page) et stylées par un thème global.

Le produit s’adresse d’abord au développeur qui construit le site via un dashboard intégré sous le chemin /dev. Le visiteur final ne voit que le site public rendu. Un tableau de bord client sous /admin permet ensuite de modifier uniquement les champs de contenu et les médias autorisés par le développeur.

Le positionnement n’est pas celui d’un SaaS multi-locataires ni d’une SPA. CapsulePHP est un squelette à déployer par projet : un dépôt, une base SQLite, un panel développeur, un site public, éventuellement un export HTML statique pour un hébergeur de fichiers.

L’inspiration déclarée est proche d’Astro côté mental model : les pages sont des compositions de sections plutôt que des templates géants. En revanche le runtime de production reste du PHP sans dépendance Composer côté exécution, avec un moteur de vues léger de type Mustache.

---

## 2. Intention produit et public

Trois publics se distinguent clairement :

Le développeur utilise /dev pour structurer les pages, choisir les variantes de blocs, régler le chrome du site (identité, navigation, header, footer), définir le thème, gérer les médias, lancer l’export. C’est le cœur opérationnel du produit aujourd’hui.

Le visiteur consulte les pages publiées. Il reçoit du HTML rendu côté serveur (ou du HTML pré-généré après export), avec SEO, assets CSS et JS résolus selon les sections présentes, et le chrome du site.

Le client final utilise /admin pour retoucher uniquement le contenu autorisé (pages et médias selon la configuration définie dans /dev), sans toucher à la structure des blocs ni au thème.

Le cadre d’usage typique est un site marketing, une landing, un site vitrine multi-pages, éventuellement avec formulaires et médiathèque. Ce n’est pas un CMS généraliste de type WordPress, ni un framework applicatif complet avec ORM, files d’attente génériques et authentification utilisateurs métier.

---

## 3. Principes d’architecture

Le document root web est exclusivement le dossier public. Tout ce qui n’y est pas (sources framework, templates, configuration, données) ne doit pas être exposé directement par le serveur HTTP.

Une requête HTTP entre par le point d’entrée public, charge l’autoloader Capsule, démarre le conteneur d’injection de dépendances et le routeur, construit un objet requête, traverse une pile de middlewares (gestion d’erreurs, assets statiques, authentification du panel développeur, en-têtes de sécurité, chemin de base éventuel), puis atteint le routeur qui décide de la réponse.

Le framework lui-même vit dans src sous le namespace Capsule. La logique applicative du dashboard (contrôleurs) vit dans app sous le namespace App. Les templates HTML des sections, des layouts et de l’interface /dev vivent dans resources. Les assets CSS et JS servis au navigateur sont sous public/assets. Les données métier (pages, thème, site, médias, files d’import vidéo) sont dans SQLite.

Deux modes de livraison coexistent. En mode runtime PHP (local, Docker, Render), le panel /dev et le rendu dynamique des pages sont actifs. En mode export statique, chaque page publiée est figée en HTML dans un dossier dist, prêt pour un hébergeur de fichiers type Netlify ; le panel et l’API peuvent alors être proxifiés vers une instance PHP séparée si on souhaite encore éditer le site après déploiement.

Une règle de séparation importante : la logique métier du site construit ne doit pas s’installer dans le cœur framework, et le cœur framework ne doit pas dépendre de détails d’un site client particulier. Les types de sections s’enregistrent de façon déclarative ; on évite les branchements conditionnels géants sur le type de section dans le moteur de rendu.

---

## 4. Cheminement d’une requête

Lorsqu’un navigateur frappe une URL, le serveur envoie la requête vers public/index.php. Le bootstrap charge la configuration, le conteneur, le routeur et la pile middleware.

La pile traite d’abord les erreurs de façon centralisée, puis les assets statiques si la requête porte sur un fichier déjà présent, puis l’accès au zone /dev (session et mot de passe développeur si configuré), puis les en-têtes de sécurité (CSP et apparentés), puis un éventuel préfixe de chemin de base (déploiement sous sous-dossier).

Le routeur fusionne deux sources de routes. Les routes API et dashboard déclarées dans la configuration ont la priorité. Les pages publiées issues de SQLite viennent ensuite : chaque page publiée devient une route publique dont le slug est l’URL (slug vide pour la page d’accueil).

Si la cible est une page publique, le moteur de pages lit la page en base, récupère le thème et le chrome du site (nom, navigation, variantes d’en-tête et de pied de page, CTA), demande au moteur de sections de rendre chaque section visible dans l’ordre, résout les feuilles de style et scripts nécessaires à cette combinaison, calcule les métadonnées SEO, puis injecte le tout dans un layout. La réponse HTML part vers le client.

Si la cible est une action /dev, un contrôleur applicatif lit ou écrit en base via des dépôts (repositories), puis renvoie soit une page complète du dashboard, soit un fragment HTML pour une mise à jour partielle (pattern proche de HTMX : le dashboard peut rafraîchir une zone sans recharger toute la page).

Si la cible est une API minimale (par exemple le healthcheck), la réponse est JSON ou un statut simple, sans layout de site.

---

## 5. Modèle mental des pages et des sections

Une page a un slug (chemin d’URL), un titre, une description, un layout, un indicateur de publication, des métadonnées SEO, et une liste ordonnée de sections. Chaque section a un type (hero, pricing, faq…), une variante visuelle (plusieurs mises en page pour un même type), un contenu éditable (textes, liens, listes, références médias), et un indicateur de visibilité.

Le catalogue des types et variantes n’est pas inventé à la volée dans le panel : il est déclaré dans un registre (fichier de catalogue des sections). Ce registre décrit quels champs sont éditables, quelles variantes existent, et comment le dashboard doit présenter l’éditeur. Les templates HTML correspondants vivent dans resources/sections, un dossier par type, un fichier par variante. Les styles associés sont des CSS autonomes par variante, servis depuis public/assets.

Quand une page est rendue, chaque section passe par un pipeline : le contenu stocké en JSON est enrichi éventuellement par un handler PHP (valeurs dérivées, médias résolus, défauts), puis un renderer de variante produit le HTML à partir du template, et le résolveur de styles et de scripts ajoute les assets manquants une seule fois par page.

Le chrome du site (identité, navigation, header, footer) est transversal. Il n’appartient pas à une page particulière. La navigation peut être construite automatiquement à partir des pages publiées, ou définie manuellement (liens, boutons, CTA). Des variantes de header et de footer permettent de changer l’apparence du chrome sans réécrire chaque page.

Le thème global (couleurs, polices, espacements) est stocké en base et injecté sous forme de variables CSS après les feuilles de style. Les CSS de base évitent de figer les couleurs et polices pour laisser le thème piloter l’apparence. Des bindings relient les tokens du thème aux sélecteurs du site.

---

## 6. Données persistées

Le stockage principal est SQLite. Les pages y sont des lignes avec le JSON des sections et des métas. Les réglages de site et de thème sont des clés JSON dans une table de paramètres. Une table médias décrit la bibliothèque d’images et de vidéos (URL, type MIME, taille, etc.). Une table d’imports vidéo suit l’état asynchrone des téléchargements et conversions (file d’attente, téléchargement, conversion, prêt, échec, éventuellement en attente d’approbation).

Les fichiers binaires (uploads site, médias, polices, imports) vivent sous public/uploads, donc accessibles en URL une fois acceptés. Sur certains chemins réseau (montage type /mnt), la base SQLite peut être redirigée vers un répertoire temporaire local pour éviter les problèmes de verrouillage NFS ; le reste du projet reste à son emplacement.

Des commandes CLI permettent d’exporterer ou d’importer un site (backup JSON) et de réinitialiser la base au seed par défaut (pages d’accueil et about, thème et identité de départ).

---

## 7. Parcours développeur : construire un site

Le cheminement habituel est le suivant.

On initialise le projet (dépendances, dossier de données, schéma SQLite, seed, synchronisation des styles). On lance le serveur de développement. On ouvre /dev. Si un mot de passe développeur est défini dans l’environnement, on s’authentifie ; sinon, en mode développement, l’accès peut être libre selon la configuration.

Dans le panneau des pages, on crée les pages nécessaires (accueil, contact, à propos, etc.), on duplique ou renomme au besoin, on définit laquelle est l’accueil, on publie quand le contenu est prêt. Sur une page donnée, on ajoute des sections depuis la bibliothèque, on choisit la variante, on remplit les champs, on réordonne, on masque temporairement une section sans la supprimer. L’aperçu live passe par une URL de preview dédiée (iframe), isolée côté sécurité (CSP) des autres origines.

On règle ensuite le chrome : nom du site, navigation, CTA d’en-tête, variantes header et footer, éventuels éléments de login affichés dans le chrome. On règle le thème : couleurs, polices (y compris upload), espacement. L’aperçu du thème peut se faire sur une preview générique.

On alimente la médiathèque pour les images et vidéos référencées par les champs des sections. Pour les vidéos provenant de YouTube ou d’un upload à convertir, on passe par la file d’imports : une entrée est créée, un worker externe (processus PHP dédié, éventuellement service systemd ou conteneur) télécharge et convertit (outils yt-dlp et ffmpeg), puis le fichier prêt peut être streamé et/ou approuvé avant usage.

On vérifie le site public via le lien de consultation. On exécute la suite de tests si on a modifié le framework ou des sections. On exporte en statique si la livraison client est un site HTML, ou on déploie le runtime PHP si le panel doit rester actif.

---

## 8. Parcours visiteur

Le visiteur ne voit que les pages marquées publiées. Pour chaque URL, le serveur (ou le fichier HTML exporté) présente le layout, le chrome, les sections visibles dans l’ordre, les styles et scripts nécessaires, et les métadonnées SEO (titre, description, canonical, Open Graph, éventuellement JSON-LD si le contenu le justifie).

Les pages non publiées n’apparaissent pas comme routes publiques. Les assets absents ou mal référencés se traduisent par des pages incomplètes visuellement ; d’où l’importance du résolveur d’assets et de la synchro des styles au moment de l’init et de l’ajout de sections.

---

## 9. Bibliothèque de blocs et atelier de design

La bibliothèque de sections couvre un large éventail de blocs marketing : hero, fonctionnalités, intégrations, tarifs, grille tarifaire, contact, témoignages, galerie, téléchargement, équipe, projets, changelog, process, industrie, listes, timeline, logos, services, comparaison, appels à l’action, awards, communauté, statistiques, carrières, FAQ, code, conformité, études de cas, waitlist, login, signup, expérience, démo, blog, et d’autres selon l’évolution du registre.

Chaque nouveau bloc suit un workflow de projet : d’abord un design source (souvent dans l’atelier React/Vite/shadcn nommé « block ui », qui n’est pas le runtime de production), puis conversion en template HTML de section, CSS autonome, enregistrement dans le catalogue, handler et renderer PHP si besoin, valeurs par défaut, éventuel JS branché via le handler, tests, et vérification que l’export statique inclut bien les assets.

Les icônes du site et du panel s’appuient sur Font Awesome hébergé localement dans le projet, pas via CDN. Les images de démonstration ne doivent pas dépendre d’URLs externes fragiles.

---

## 10. Dashboard développeur : comportement

L’interface /dev est une UI HTML servie par le runtime PHP, avec du JavaScript dédié sous public/assets. Elle s’appuie sur des partials et des réponses partielles pour mettre à jour des zones (liste de pages, éditeur de section, aperçu) sans rechargement complet systématique.

Les previews du site dans le dashboard passent par des chemins /dev/preview/…, distincts du site public, afin de contrôler la CSP et d’éviter de mélanger contexte éditeur et contexte public.

Les écrans principaux couvrent : liste et édition des pages, édition des sections d’une page, chrome et identité du site, thème, médiathèque, imports vidéo, export. L’ensemble est protégé par le middleware d’authentification développeur lorsque DEV_PASSWORD est défini.

---

## 11. SEO, accessibilité, performance et agents

Le projet intègre des règles de qualité web persistantes (Cursor) : un h1 par page, titres et descriptions uniques, canonical correct, Open Graph aligné, HTML sémantique natif, noms accessibles sur les contrôles, pas de tiret cadratin dans les textes UI, polices et assets maîtrisés, pas d’injection tardive qui décale le layout, Font Awesome local uniquement.

Pour les agents IA qui naviguent le site, on vise un DOM compréhensible, des formulaires importants exposables de façon déclarative quand c’est pertinent, et un fichier llms.txt à jour si des parcours utiles aux agents existent.

Ces règles s’appliquent autant au site public rendu qu’aux templates de sections et, autant que possible, à l’UI /dev.

---

## 12. Export et déploiement

L’export statique rejoue le rendu de chaque page publiée et copie les assets nécessaires vers dist. Le résultat peut être publié sur Netlify (build via script dédié, dossier de publication dist). Dans ce mode, le site public est un ensemble de fichiers HTML ; le panel /dev n’est plus dans le même processus sauf si un proxy pointe vers une application PHP distante (variable d’environnement de type URL de l’app PHP).

Le déploiement runtime PHP (Docker, Render) conserve le panel et le rendu dynamique. Un disque persistant peut conserver la base et les uploads. Un healthcheck HTTP permet de surveiller l’instance. Un worker vidéo peut tourner à côté (compose dédié ou service systemd) pour traiter la file d’imports.

Le document root doit toujours être public, que ce soit en Apache, Nginx/PHP-FPM ou dans l’image Docker.

---

## 13. Stack technique (vue d’ensemble)

Runtime de production : PHP 8.2 ou supérieur, sans dépendance Composer au runtime. Base : SQLite 3 via PDO. Templates : moteur de vues maison avec échappement, HTML brut contrôlé, et partials. Dashboard : HTML et JavaScript maison, interactions partielles. Qualité en développement : PHPUnit, PHPStan, PHP-CS-Fixer, PHPCS via Composer. Atelier de design des blocs : React, TypeScript, Vite, shadcn, hors chemin critique de production. Vidéo : yt-dlp, ffmpeg, worker PHP. Déploiement : serveur PHP classique, Docker, Netlify pour le statique, Render pour le PHP.

---

## 14. Flux de données résumé en prose

L’éditeur dans /dev envoie des modifications aux contrôleurs. Les contrôleurs persistent via les repositories PDO. Les pages et réglages sont lus ensuite par le moteur de rendu public : page hydratée, sections enrichies par les handlers, HTML des templates, CSS et JS résolus, layout appliqué, HTML émis. L’export rejoue ce chemin pour chaque page publiée et matérialise le résultat en fichiers. Les imports vidéo passent par une table de file, un processus worker externe, des fichiers dans les uploads, puis des références dans la médiathèque utilisables par les champs des sections.

---

## 15. Ce qui n’est pas (encore) le projet

Ce n’est pas un CMS multi-auteurs avec workflows éditoriaux avancés. Ce n’est pas une application métier générique. Ce n’est pas une SPA. Le dashboard client /admin existe pour l’édition de contenu bornée (permissions définies dans /dev), pas pour restructurer le site. Une partie de la documentation historique (notamment certains passages de doc.md) peut encore parler d’un modèle où les pages vivaient en fichiers YAML sous resources/pages ; le modèle réel actuel est SQLite, avec édition via /dev et /admin. En cas de doute, le README et le comportement du code priment sur les passages obsolètes.

---

## 16. Où regarder ensuite

Pour démarrer et opérer au quotidien : README à la racine. Pour le détail technique long : doc.md (en gardant à l’esprit les éventuels passages obsolètes sur le stockage des pages). Pour les imports vidéo : documentation dédiée sous doc/. Pour le design source des blocs : README du dossier block ui. Pour les conventions de contribution (sections, dashboard, PHP) : règles Cursor sous .cursor/rules.

Ce fichier CONTEXTE.md a pour but de rester copiable et lisible comme récit : contexte, cheminement, responsabilités, sans dépendre de schémas ni d’extraits de code.
)
