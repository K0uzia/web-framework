# Import vidéo (YouTube et fichiers locaux)

Pipeline asynchrone intégré à Capsule : file SQLite, worker CLI, yt-dlp, ffmpeg, lecture HTML5 locale avec support Range.

## Architecture

| Composant | Rôle |
|-----------|------|
| `VideoImportService` | Validation, mise en file, statut |
| `VideoImportRepository` | Table `video_imports` |
| `bin/video-import-worker.php` | Worker consommant la file |
| `YtDlpDownloader` | Téléchargement YouTube (sans shell) |
| `FfmpegConverter` | MP4 H.264/AAC navigateur |
| `/dev/video-imports` | Interface admin |
| `GET /dev/api/videos/{id}/status` | JSON statut |
| `GET /dev/api/videos/{id}/stream` | Streaming avec `Range` |

## Installation des outils (Linux)

```bash
bash scripts/install-video-tools.sh
```

Dépendances : ffmpeg et yt-dlp.

Installation recommandée :

```bash
bash scripts/install-video-tools.sh
```

Le script installe ffmpeg via apt, puis yt-dlp via apt, pipx ou un venv local (`tools/video-tools-venv`). Sur Ubuntu récent, pip système est bloqué (PEP 668) : le script n'utilise pas `sudo pip`.

Si yt-dlp est dans le venv local :

```bash
source config/video-tools.env
php bin/video-import-worker.php
```

Exemple manuel yt-dlp :

```bash
yt-dlp --no-playlist --write-info-json --write-thumbnail --retries 3 \
  -o '/var/www/uploads/%(id)s.%(ext)s' 'https://www.youtube.com/watch?v=VIDEO_ID'
```

Exemple conversion ffmpeg :

```bash
ffmpeg -i input.webm -c:v libx264 -preset fast -crf 23 -c:a aac -b:a 128k -movflags +faststart output.mp4
```

## Variables d'environnement

| Variable | Défaut | Description |
|----------|--------|-------------|
| `VIDEO_IMPORT_ROOT` | `public/uploads/media/imports` | Dossier de stockage |
| `VIDEO_IMPORT_MAX_BYTES` | `524288000` (500 Mo) | Taille max par fichier |
| `VIDEO_IMPORT_MAX_QUEUE` | `5` | Jobs actifs max par utilisateur |
| `VIDEO_IMPORT_REQUIRE_APPROVAL` | `false` | Approbation admin avant téléchargement |
| `VIDEO_IMPORT_DISK_QUOTA_BYTES` | `5368709120` (5 Go) | Quota total imports prêts |
| `VIDEO_IMPORT_YT_DLP_BIN` | `yt-dlp` | Binaire yt-dlp |
| `VIDEO_IMPORT_FFMPEG_BIN` | `ffmpeg` | Binaire ffmpeg |

## Lancer le worker

### Développement

```bash
php bin/video-import-worker.php
```

Un seul passage :

```bash
php bin/video-import-worker.php --once
```

### Production (systemd)

```bash
sudo cp deploy/capsule-video-worker.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now capsule-video-worker
```

### Docker Compose (optionnel)

```bash
docker compose -f docker-compose.video-worker.yml up -d
```

## API (protégée par auth `/dev`)

### Créer un import YouTube

```http
POST /dev/video-imports
Content-Type: application/x-www-form-urlencoded

mode=youtube&url=https://www.youtube.com/watch?v=XXXX&rights_accepted=1&label=Hero
```

Réponse JSON (header `Accept: application/json`) :

```json
{ "id": "vid-…", "status": "queued", "message": "En file d'attente." }
```

### Statut

```http
GET /dev/api/videos/{id}/status
```

```json
{
  "status": "downloading",
  "progress": 55,
  "message": "Téléchargement en cours…",
  "title": "",
  "public_video_url": "",
  "public_thumb_url": "",
  "media_id": ""
}
```

Statuts : `queued`, `pending_approval`, `downloading`, `converting`, `ready`, `failed`.

### Lecture

```http
GET /dev/api/videos/{id}/stream
Accept-Ranges: bytes
```

Une fois `ready`, la vidéo est aussi en bibliothèque (`media`) à `/uploads/media/imports/{id}/video.mp4`.

## Schéma SQL

Voir `migrations/sqlite_init.sql` (table `video_imports`).

## Guide sécurité (résumé)

1. **Auth** : routes sous `/dev`, cookie `capsule_dev` ou mot de passe `DEV_PASSWORD`.
2. **Injection** : `ProcessRunner` n'utilise jamais `shell_exec` ; arguments passés en tableau à `proc_open`.
3. **URLs** : whitelist d'hôtes YouTube via `YouTubeUrlValidator`.
4. **Uploads** : MIME autorisés, taille max, `is_uploaded_file()`.
5. **Quotas** : file par utilisateur, quota disque global configurable.
6. **Stockage** : un dossier par job (`imports/{id}/`), hors web root possible via symlink.
7. **Worker** : exécuter sous utilisateur dédié non-root (voir unit systemd).
8. **Logs** : sorties stderr yt-dlp/ffmpeg tronquées en base (`message`).
9. **Approbation** : `VIDEO_IMPORT_REQUIRE_APPROVAL=1` pour valider manuellement.
10. **Droits** : case à cocher obligatoire côté UI avant tout import.

## Note légale (à afficher à l'utilisateur)

> Le téléchargement ou la copie d'une vidéo protégée nécessite l'autorisation du titulaire des droits. Cet outil est destiné aux contenus dont vous êtes titulaire ou pour lesquels vous disposez d'une licence explicite. L'éditeur décline toute responsabilité en cas d'usage non conforme au droit d'auteur applicable.

## Tests

```bash
./vendor/bin/phpunit tests/YouTubeUrlValidatorTest.php tests/VideoImportRepositoryTest.php tests/VideoImportServiceTest.php
```

## Phase 2 (hors MVP)

- Transcodage HLS adaptatif
- Interface admin quotas avancés
- Video.js embarqué localement
- Nettoyage automatique par rétention
