# Numberstore

Basisanwendung auf **Laravel 13** mit **Web-Anmeldung** (Laravel Breeze, Blade), **REST-API** unter `/api/v1`, **Laravel Sanctum** für API-Tokens und **Swagger UI** (OpenAPI) zur API-Dokumentation. Als Datenbank wird **MySQL** verwendet.

## Voraussetzungen

- PHP **8.3+** (siehe [Laravel 13 – Server Requirements](https://laravel.com/docs/13.x/deployment#server-requirements))
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) und npm (für Vite / Frontend-Assets). Mit **Node 22** mindestens **v22.21.1** verwenden (siehe [Fehlerbehebung](#fehler-shouldupgradecallback-bei-composer-run-dev)); alternativ **Node 20 LTS**. Im Projekt liegt eine [`.nvmrc`](.nvmrc) mit `22.21.1` für [nvm](https://github.com/nvm-sh/nvm): `nvm install && nvm use`.
- **MySQL** (lokal oder z. B. über [Laravel Herd](https://herd.laravel.com/))

## Installation

1. Repository klonen und ins Projektverzeichnis wechseln:

   ```bash
   git clone <repository-url> numberstore
   cd numberstore
   ```

2. PHP-Abhängigkeiten installieren:

   ```bash
   composer install
   ```

3. Umgebungsdatei anlegen und Anwendungsschlüssel generieren:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **MySQL-Datenbank** anlegen (Beispiel):

   ```bash
   mysql -u root -e "CREATE DATABASE IF NOT EXISTS numberstore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

5. In der `.env` die Datenbankverbindung eintragen (Standard aus `.env.example`):

   - `DB_CONNECTION=mysql`
   - `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

6. Migrationen ausführen:

   ```bash
   php artisan migrate
   ```

7. **Frontend-Assets** installieren und für die Produktion bauen:

   ```bash
   npm install
   npm run build
   ```

8. **OpenAPI-Datei** erzeugen (liegt unter `storage/api-docs/` und ist nicht versioniert):

   ```bash
   php artisan l5-swagger:generate
   ```

### Schnellstart (ein Befehl)

Nach `.env` und Datenbank:

```bash
composer run setup
```

(`setup` führt u. a. `composer install`, `migrate`, `npm install` und `npm run build` aus – siehe `composer.json`.)

## Lokale Entwicklung

- Alle Dienste parallel (PHP-Server, Queue, Logs, Vite):

  ```bash
  composer run dev
  ```

- Nur Vite (Hot Reload), wenn die App bereits über Herd oder `php artisan serve` erreichbar ist:

  ```bash
  npm run dev
  ```

### Laravel Herd

Liegt das Projekt unter `~/Herd/numberstore`, erzeugt Herd in der Regel automatisch eine lokale URL (z. B. `https://numberstore.test`). Trage die gleiche Basis-URL in `.env` bei `APP_URL` ein, damit generierte Links und ggf. Swagger korrekt auflösen.

Alternativ ohne Herd:

```bash
php artisan serve
```

### Fehler: `shouldUpgradeCallback` bei `composer run dev`

Wenn Vite mit **Herd-HTTPS** startet und direkt mit `TypeError: server.shouldUpgradeCallback is not a function` (Node `node:_http_server`) abbricht, liegt das an einem **Bug in Node.js v22.21.0** bei HTTPS-Servern und WebSocket-Upgrades (siehe [nodejs/node#60336](https://github.com/nodejs/node/issues/60336)).

**Lösung:** Node auf **v22.21.1 oder neuer** aktualisieren (z. B. `nvm install 22.21.1 && nvm use`, oder über den Node-Installer / Homebrew). **Workaround:** vorübergehend **Node 22.20.x** oder **Node 20 LTS** nutzen.

## Web-Oberfläche und Anmeldung

Nach dem Start erreichst du die Startseite; **Registrierung** und **Login** stellt **Laravel Breeze** bereit (Routen unter `/register`, `/login` usw.).

## REST-API (Version 1)

Alle API-Routen haben das Präfix `/api/v1`.

| Methode | Pfad | Auth | Beschreibung |
|--------|------|------|----------------|
| `GET` | `/api/v1/health` | nein | Erreichbarkeit / Basis-Info |
| `POST` | `/api/v1/auth/token` | nein | JSON-Body: `email`, `password` → liefert `token` (Sanctum) |
| `GET` | `/api/v1/user` | ja | `Authorization: Bearer <token>` |

Beispiel Token holen:

```bash
curl -s -X POST https://numberstore.test/api/v1/auth/token \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"du@example.com","password":"dein-passwort"}'
```

## Swagger / OpenAPI

- **Swagger UI:** `/api/documentation`
- Rohdaten der Spezifikation: Route `GET /docs` (JSON gemäß L5-Swagger-Konfiguration)

Nach Änderungen an den OpenAPI-**Attributen** in den Controllern (Namespace `OpenApi\Attributes`) die Spezifikation neu erzeugen:

```bash
php artisan l5-swagger:generate
```

In **Produktion** solltest du `L5_SWAGGER_GENERATE_ALWAYS` in der `.env` auf `false` setzen und die Dokumentation im Deployment mit `php artisan l5-swagger:generate` bauen (siehe Kommentar in `.env.example`).

## Tests

```bash
php artisan test
```

## Technologieüberblick

- Laravel **13.x**
- Laravel **Breeze** (Blade) – Authentifizierung im Browser
- Laravel **Sanctum** – API-Tokens
- **darkaonline/l5-swagger** – Swagger UI und Generierung aus PHP-Attributen
- **MySQL** – persistenter Datenspeicher

Neue Datenbank-Migrationen im Projekt bitte wie gewohnt mit `php artisan make:migration` anlegen.
