# Assistara Website - Sicheres Kontaktformular

Professionelles, sicheres Kontaktformular mit Email-Versand für die Assistara-Website, optimiert für Netcup Webhosting.

---

## ✨ Features

- ✅ **Sicherer Email-Versand** via PHPMailer mit SMTP-Authentifizierung
- ✅ **Spam-Schutz** mit Google reCAPTCHA v3 (unsichtbar)
- ✅ **Rate Limiting** gegen Missbrauch (3 Anfragen/10 Min pro IP)
- ✅ **CSRF-Schutz** gegen Cross-Site Request Forgery
- ✅ **Honeypot-Feld** als zusätzlicher Spam-Filter
- ✅ **Automatische Bestätigungs-Emails** an Absender
- ✅ **Professionelle HTML-Email-Templates** im Website-Design
- ✅ **Input-Validierung** (Client + Server-seitig)
- ✅ **XSS/Injection-Schutz** durch Sanitization
- ✅ **Error-Logging** für Fehleranalyse
- ✅ **HTTPS-Verschlüsselung** erzwungen
- ✅ **DSGVO-konform** mit Datenschutz-Checkbox
- ✅ **Loading-States** und User-Feedback
- ✅ **Moderne AJAX-Submission** ohne Page-Reload
- ✅ **Mobile-optimiert** und responsive

---

## 📁 Projektstruktur

```
AssistaraWebsite/
├── api/
│   ├── config.php              # Backend-Konfiguration (NICHT committen!)
│   ├── contact-handler.php     # Hauptlogik für Formularverarbeitung
│   ├── get-csrf-token.php      # CSRF Token Generator
│   ├── email-templates/
│   │   ├── admin-notification.html    # Email an Admin
│   │   └── user-confirmation.html     # Bestätigung an User
│   ├── logs/
│   │   └── .gitkeep
│   └── vendor/
│       └── PHPMailer/          # PHPMailer Library (manuell hochladen)
│           ├── PHPMailer.php
│           ├── SMTP.php
│           └── Exception.php
├── css/
│   └── styles.css              # Styles inkl. Loading/Status-Styles
├── js/
│   └── main.js                 # Haupt-JavaScript
├── components/
│   ├── navbar.html
│   └── footer.html
├── kontakt.html                # Kontaktformular (aktualisiert)
├── .htaccess                   # Apache-Konfiguration
├── .gitignore                  # Git Ignore-Regeln
├── DEPLOYMENT.md               # Deployment-Anleitung
├── CONFIG_EXAMPLE.md           # Konfigurations-Hilfe
└── README.md                   # Diese Datei
```

---

## 🚀 Quick Start

### 1. Voraussetzungen
- Netcup Webhosting (PHP 7.4+, SMTP)
- Email-Account: info@assistara.de
- Google reCAPTCHA v3 Account (kostenlos)

### 2. Installation

#### A) PHPMailer hochladen
Laden Sie PHPMailer in `api/vendor/PHPMailer/`:
- https://github.com/PHPMailer/PHPMailer/releases

Benötigte Dateien: `PHPMailer.php`, `SMTP.php`, `Exception.php`

#### B) reCAPTCHA konfigurieren
1. Erstellen Sie einen reCAPTCHA v3 Site auf: https://www.google.com/recaptcha/admin
   - Domain: `assistara.de`
2. Kopieren Sie **Site Key** und **Secret Key**
3. In `kontakt.html` (2 Stellen):
   ```html
   <script src="https://www.google.com/recaptcha/api.js?render=SITE_KEY_HIER"></script>
   ```
   ```javascript
   const RECAPTCHA_SITE_KEY = 'SITE_KEY_HIER';
   ```

#### C) Backend konfigurieren
Öffnen Sie `api/config.php` und tragen Sie ein:

```php
// SMTP (Netcup)
define('SMTP_USERNAME', 'info@assistara.de');
define('SMTP_PASSWORD', 'IHR_SMTP_PASSWORT');  // ← WICHTIG!

// reCAPTCHA
define('RECAPTCHA_SECRET_KEY', 'IHR_SECRET_KEY'); // ← WICHTIG!

// Umgebung
define('ENVIRONMENT', 'production');
```

**SMTP-Passwort finden:** Netcup CCP → E-Mail → E-Mail-Konten

#### D) Dateien hochladen
Per FTP/SFTP alle Dateien nach `/httpdocs/` hochladen.

#### E) SSL aktivieren
Netcup CCP → Domains → SSL-Zertifikate → Let's Encrypt erstellen

#### F) Testen
1. Öffnen: https://assistara.de/kontakt.html
2. Formular ausfüllen und absenden
3. Prüfen: Email bei info@assistara.de + Bestätigung beim Absender

---

## 📖 Dokumentation

- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Vollständige Deployment-Anleitung
- **[CONFIG_EXAMPLE.md](CONFIG_EXAMPLE.md)** - Konfigurations-Hilfe
- **[api/PHPMAILER_INSTALLATION.md](api/PHPMAILER_INSTALLATION.md)** - PHPMailer Setup

---

## 🔧 Konfiguration

### SMTP-Einstellungen (Netcup)
```php
define('SMTP_HOST', 'smtp.netcup.net');
define('SMTP_PORT', 587);           // oder 465 für SSL
define('SMTP_SECURE', 'tls');       // oder 'ssl'
```

### Rate Limiting anpassen
```php
define('MAX_SUBMISSIONS_PER_IP', 3);     // Anfragen pro IP
define('RATE_LIMIT_WINDOW', 600);        // Zeitfenster in Sekunden
```

### reCAPTCHA Score
```php
define('RECAPTCHA_MIN_SCORE', 0.5);  // 0.0-1.0 (höher = strenger)
```

---

## 🛡️ Sicherheitsfeatures

| Feature | Implementierung | Datei |
|---------|----------------|-------|
| **HTTPS** | .htaccess Redirect + HSTS Header | `.htaccess` |
| **CSRF** | Session-basierte Token-Validierung | `contact-handler.php` |
| **XSS** | `htmlspecialchars()` Sanitization | `contact-handler.php` |
| **Rate Limiting** | Session-Counter pro IP | `contact-handler.php` |
| **Spam** | reCAPTCHA v3 + Honeypot | `kontakt.html` + Backend |
| **Input Validation** | Server-seitig mit Limits | `config.php` |
| **SQL Injection** | Keine DB, aber prepared statements ready | - |
| **Header Security** | X-Frame-Options, CSP, etc. | `.htaccess` |
| **File Protection** | .htaccess Deny für config.php | `.htaccess` |

---

## 📧 Email-Templates

Beide Templates nutzen das Website-Design (Quicksand Font, Blau/Orange):

### Admin-Benachrichtigung (`admin-notification.html`)
- Empfänger: info@assistara.de
- Inhalt: Vollständige Formulardaten
- Design: Professionell, übersichtlich
- Farbe: Primär-Blau (#2b549e)

### User-Bestätigung (`user-confirmation.html`)
- Empfänger: Formular-Absender
- Inhalt: Bestätigung + Zusammenfassung
- Design: Freundlich, einladend
- Farbe: Sekundär-Orange (#f0832a)

---

## 🐛 Fehlerbehebung

### Emails kommen nicht an
1. Prüfen: `api/logs/contact-errors.log`
2. SMTP-Zugangsdaten korrekt?
3. Port 587 blockiert? → Versuche Port 465
4. Spam-Ordner prüfen

### reCAPTCHA Fehler
1. Site Key + Secret Key korrekt?
2. Domain in reCAPTCHA Admin registriert?
3. Browser-Console auf Fehler prüfen

### Rate Limiting zu streng
Erhöhen Sie `MAX_SUBMISSIONS_PER_IP` in `config.php`

### Session-Fehler
Prüfen Sie PHP Session-Support:
```php
<?php
session_start();
echo "OK";
?>
```

---

## 📊 Formular-Felder

| Feld | Typ | Pflicht | Validierung |
|------|-----|---------|-------------|
| **Kontaktart** | Select | Ja | Vorgegebene Optionen |
| **Name** | Text | Ja | Max 100 Zeichen |
| **E-Mail** | Email | Ja | FILTER_VALIDATE_EMAIL |
| **Telefon** | Tel | Nein | Max 30 Zeichen |
| **Nachricht** | Textarea | Ja | Max 5000 Zeichen |
| **Datenschutz** | Checkbox | Ja | Muss aktiviert sein |
| **Website** | Hidden | - | Honeypot (sollte leer bleiben) |

---

## 🎨 Design-Integration

Das Kontaktformular nutzt das bestehende Assistara-Design:

**Farben:**
- Primär: `#2b549e` (Blau)
- Sekundär: `#f0832a` (Orange)
- Akzent: `#e2be2b` (Gelb)

**Typografie:**
- Font: Quicksand (400, 500, 600, 700)
- Moderne, freundliche Ästhetik

**Komponenten:**
- Modal-Overlays für Status-Meldungen
- Loading-Spinner beim Submit
- Animierte Erfolgs-/Fehler-Nachrichten
- Responsive Design (Mobile-First)

---

## 🔐 Datenschutz (DSGVO)

- ✅ Keine Datenbank-Speicherung (nur Email-Versand)
- ✅ Datenschutz-Checkbox erforderlich
- ✅ Link zur Datenschutzerklärung
- ✅ SSL/HTTPS-Verschlüsselung
- ✅ Minimale Datenspeicherung (nur Logs bei Fehlern)
- ✅ IP-Speicherung nur temporär für Rate-Limiting (Session)

---

## 🚨 Wichtige Hinweise

⚠️ **VOR dem Deployment:**
1. `api/config.php` mit echten Zugangsdaten füllen
2. reCAPTCHA Keys in `kontakt.html` + `config.php` eintragen
3. `ENVIRONMENT` auf `'production'` setzen
4. PHPMailer hochladen
5. SSL-Zertifikat aktivieren

⚠️ **Sicherheit:**
- **NIEMALS** `config.php` mit Passwörtern committen!
- `.gitignore` schützt sensible Dateien
- Logs regelmäßig prüfen und rotieren

---

## 📞 Support & Kontakt

**Netcup Support:**
- Wiki: https://www.netcup-wiki.de/
- Forum: https://forum.netcup.de/

**PHPMailer Dokumentation:**
- GitHub: https://github.com/PHPMailer/PHPMailer
- Docs: https://github.com/PHPMailer/PHPMailer/wiki

**Google reCAPTCHA:**
- Admin: https://www.google.com/recaptcha/admin
- Docs: https://developers.google.com/recaptcha/docs/v3

---

## 📝 Lizenz & Credits

**Entwickelt für:** Assistara - Dein Plus an Unterstützung

**Technologien:**
- PHP 7.4+
- PHPMailer 6.x
- Google reCAPTCHA v3
- Vanilla JavaScript (ES6+)
- CSS3 (Custom Properties, Flexbox, Grid)

---

## ✅ Checkliste

- [ ] PHPMailer installiert
- [ ] reCAPTCHA Site + Secret Keys konfiguriert
- [ ] SMTP-Zugangsdaten in config.php
- [ ] Umgebung auf 'production' gesetzt
- [ ] Dateien auf Server hochgeladen
- [ ] SSL/HTTPS aktiviert
- [ ] Formular getestet (Email-Versand)
- [ ] Spam-Schutz getestet
- [ ] Mobile-Ansicht geprüft
- [ ] config.php NICHT committed

---

**Status:** ✅ Produktionsbereit

**Version:** 1.0

**Letzte Aktualisierung:** Februar 2026
