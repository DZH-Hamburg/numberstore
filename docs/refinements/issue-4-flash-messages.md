---

# Refinement-Vorschlag: Flash Messages (#4)

---

## Mein Verständnis des Issues

Flash-Nachrichten (Laravel `session('status')` nach Redirects) sollen nicht mehr als grüne Kästen im Seiteninhalt erscheinen, sondern als **Overlay unten rechts** (Toast/Layover). Sie sollen nach **ca. 5 Sekunden** ausblenden (ausfaden). Das betrifft die authentifizierte App-Oberfläche und sehr wahrscheinlich auch **Guest-Auth-Seiten**, die heute `x-auth-session-status` nutzen.

## Einbezogene Diskussion (Kommentare)

- Trigger-Kommentar `@cursoragent starte refinement` — keine inhaltliche Scope-Änderung.
- Ein früherer Bot-Kommentar erwähnte einen fehlgeschlagenen API-Post; **dieser Refinement-Vorschlag** ist die aktualisierte, vollständige Version für die Umsetzung.

---

## Erkenntnisse aus dem Code-Scan

### Betroffene Bereiche

- **Dateien/Ordner**:
  - Layouts: `resources/views/layouts/app.blade.php`, `resources/views/layouts/guest.blade.php` — hier zentral einbindbar (aktuell **kein** globaler Flash-Container).
  - Inline-Blöcke mit `session('status')`: `resources/views/dashboard.blade.php`, `resources/views/groups/index.blade.php`, `resources/views/groups/show.blade.php`, `resources/views/admin/users/index.blade.php`.
  - Spezialfälle (Schlüssel statt freiem Text): `resources/views/profile/partials/update-profile-information-form.blade.php` (`verification-link-sent`, `profile-updated` inkl. Alpine 2s-Hide), `resources/views/profile/partials/update-password-form.blade.php` (`password-updated`), `resources/views/auth/verify-email.blade.php` (`verification-link-sent`).
  - Komponente: `resources/views/components/auth-session-status.blade.php` — genutzt in `auth/login.blade.php`, `auth/forgot-password.blade.php` (grüner Inline-Text).
- **Datenbank**: keine.
- **API / Sanctum / Swagger**: nicht betroffen (reines Blade/UI).
- **Frontend**: Tailwind, **Alpine.js** ist bereits aktiv (`resources/js/app.js`); für Ein-/Ausblend-Animation und Timer gut geeignet.

### Nicht auf den ersten Blick sichtbar

- **Semantik von `session('status')`**: Mal **lesbarer Text** (z. B. nach Gruppen-Aktionen), mal **interne Schlüssel** (`profile-updated`, `password-updated`, `verification-link-sent`). Die Umsetzung braucht eine **klare Strategie**: entweder zentrale Zuordnung Schlüssel → `__()`-Text im Toast, oder nur freien Text anzeigen und Schlüssel-Fälle weiter in Partials behandeln (würde Doppel-Logik bedeuten).
- **Profil-Partial** blendet „Saved.“ aktuell nach **2 s** per Alpine aus — Issue verlangt **5 s** global; das sollte **vereinheitlicht** werden (Toast statt Inline).
- **Barrierefreiheit**: Overlay mit `role="status"` oder `role="alert"` (je nach Dringlichkeit), Fokus nicht stehlen, ggf. `aria-live`.
- **Tests**: In `tests/Feature/` keine gezielten Assertions auf Flash-HTML gefunden; neue oder angepasste Tests wären eher **Smoke** (Redirect + Session-Key) oder Browser-Tests, falls ihr die später einführt.

### Offene Fragen zum Issue

1. Sollen **alle** `session('status')`-Fälle (inkl. Login/Forgot-Password auf dem **Guest-Layout**) dieselbe Toast-Optik unten rechts bekommen, oder nur eingeloggte Bereiche?
2. Soll der Toast bei **Fehlermeldungen** (`session('error')` o. ä., falls genutzt) dasselbe Verhalten haben? (Im aktuellen Scan lag der Schwerpunkt auf `status`.)
3. **Stapelung**: Mehrere Flashs in einer Response — kommt das vor? Falls ja: nacheinander oder gestapelt?

---

## Vorgeschlagene Akzeptanzkriterien

> Vorschläge zur Diskussion im Refinement.

- [ ] Nach erfolgreichen Redirects mit `session('status')` erscheint **eine** lesbare Meldung als **fixiertes Overlay unten rechts** (Viewport), konsistent mit bestehenden Opta-/Tailwind-Farben.
- [ ] Die Meldung **blendet nach ca. 5 Sekunden** aus (sichtbarer Fade-out) und verschwindet danach aus dem DOM bzw. ist nicht mehr sichtbar.
- [ ] Die bisherigen **grünen Inline-Kästen** in Dashboard, Gruppen-Listen/Detail, Admin-User-Index sind **entfernt**, ohne die Flash-Inhalte zu verlieren.
- [ ] Die Fälle **`profile-updated`**, **`password-updated`**, **`verification-link-sent`** zeigen die **übersetzten** Nutzertexte im Toast (wie bisher in den Partials/Verify-Email), nicht die Roh-Schlüssel.
- [ ] **Guest-Auth-Seiten** mit `x-auth-session-status` verhalten sich analog (Toast im Guest-Layout), sofern im Scope gewünscht.
- [ ] Keine Regression bei normalen Seitenaufrufen **ohne** Flash (kein leerer Toast-Container mit Layout-Sprung).

---

## Vorgeschlagener Kontext / Hintergrundinformation

Laravel Breeze setzt Flash typischerweise über `redirect()->with('status', …)`. Eine **zentrale Darstellung in den Layouts** vermeidet Copy-Paste und hält das Verhalten (Position, Dauer, Animation) einheitlich. Alpine ist bereits gebündelt — kein neues Framework nötig.

---

## Vorgeschlagene Technische Details

- **Neue Blade-Komponente** z. B. `x-flash-toast` oder Erweiterung einer bestehenden Komponente: liest `session('status')`, mappt bekannte Schlüssel zu `__()`-Strings, sonst `e($status)` für Freitext.
- **Einbindung** am Ende von `<body>` in `app` und `guest` Layout mit `fixed bottom-… right-… z-…`, Schatten/Rundung passend zu `dashboard`-Banner-Stil.
- **Alpine**: `x-data` mit `show`, `x-init` → `setTimeout` 5000 ms, danach `show = false` mit `x-transition:leave` (opacity), optional `@transitionend` zum Entfernen.
- **Aufräumen**: Inline-`@if (session('status'))`-Blöcke und redundante Alpine-2s-Logik in Profil-Partials entfernen; `auth-session-status` auf Login/Forgot-Password **ersetzen** oder zur leeren Shell machen, je nach gewählter Architektur.
- **Qualität**: `./vendor/bin/pint --test`, bei View-Tests `APP_ENV=testing php artisan test`; bei Bedarf `npm run build` vor manuellen Checks mit Vite.

---

## Aufwandsschätzung

| Bereich | Punkte |
|--------|--------|
| Implementierung | 3 |
| Testing | 1 |
| Code-Review | 1 |
| **Gesamt (Fibonacci)** | **5** |

**Begründung:** Mittlerer Umfang: eine zentrale Komponente + Layout-Hooks + Entfernen mehrerer Duplikate + sauberes Mapping der drei Breeze-Status-Schlüssel und Guest-Seiten. Automatisierte UI-Tests für Fade-Timing sind im Projekt nicht offensichtlich angelegt; manueller Check und ggf. einfache Feature-Tests auf Session reichen für das Risikoniveau. Kein API/Migrations-Risiko.

---

## Risiken und Hinweise

- **Doppelanzeige**, falls ein Toast im Layout **und** alte Inline-Blöcke parallel existieren — beim Umbau konsequent alle Stellen migrieren.
- **Z-Index-Konflikte** mit bestehenden Modals (z. B. Gruppen-Modals mit `z-50`): Toast-`z-index` bewusst setzen (über oder unter Modals je nach UX-Wunsch).
- **Lange Texte**: max-width und optional Zeilenumbruch, damit das Overlay nicht den halben Viewport blockiert.

---

*Automatisch erstellter Refinement-Vorschlag. Bitte im Team bestätigen oder anpassen.*

---
