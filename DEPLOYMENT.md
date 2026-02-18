# Assistara Website - Deployment Anleitung für Netcup

Dieses Dokument beschreibt die Schritte zur Einrichtung des sicheren Kontaktformulars auf Netcup Webhosting.

---

## 📋 Voraussetzungen

- Netcup Webhosting-Paket (mit PHP 7.4+ und Email-Support)
- FTP/SFTP-Zugang zu Ihrem Netcup-Server
- Zugriff auf das Netcup Customer Control Panel (CCP)
- Email-Account: info@assistara.de (muss im CCP eingerichtet sein)
- Google reCAPTCHA v3 Account (kostenlos)

---

## 🚀 Schritt-für-Schritt Deployment

### 1. PHPMailer installieren

**Option A: Manueller Download (Empfohlen)**
1. Besuchen Sie: https://github.com/PHPMailer/PHPMailer/releases
2. Laden Sie die neueste Version herunter (z.B. PHPMailer-6.x.zip)
3. Extrahieren Sie die Dateien
4. Kopieren Sie folgende Dateien nach `api/vendor/PHPMailer/`:
   - PHPMailer.php
   - SMTP.php
   - Exception.php

**Option B: Via Composer (Falls verfügbar)**
```bash
cd api/
composer require phpmailer/phpmailer
```

**Option C: Native PHP mail() Funktion**
Siehe `PHPMAILER_INSTALLATION.md` für Anweisungen

---

### 2. reCAPTCHA v3 konfigurieren

1. **reCAPTCHA-Schlüssel erstellen:**
   - Besuchen Sie: https://www.google.com/recaptcha/admin
   - Klicken Sie auf "+" um eine neue Site zu erstellen
   - **Label:** Assistara Kontaktformular
   - **reCAPTCHA-Typ:** v3
   - **Domains:** assistara.de (und ggf. assistara.conflictstudios.de)
   - Akzeptieren Sie die Nutzungsbedingungen
   - Klicken Sie auf "Senden"

2. **Schlüssel notieren:**
   - **Site Key** (öffentlich)
   - **Secret Key** (vertraulich!)

3. **Site Key in kontakt.html einfügen:**
   
   Öffnen Sie `kontakt.html` und ersetzen Sie **BEIDE** Vorkommen von `YOUR_RECAPTCHA_SITE_KEY`:
   
   ```html
   <!-- Im <head> -->
   <script src="https://www.google.com/recaptcha/api.js?render=HIER_SITE_KEY_EINFÜGEN"></script>
   
   <!-- Im <script>-Bereich -->
   const RECAPTCHA_SITE_KEY = 'HIER_SITE_KEY_EINFÜGEN';
   ```

4. **Secret Key in config.php einfügen** (siehe Schritt 3)

---

### 3. Backend konfigurieren (WICHTIG!)

1. **Öffnen Sie `api/config.php`**

2. **SMTP-Zugangsdaten eintragen:**
   ```php
   define('SMTP_USERNAME', 'info@assistara.de');
   define('SMTP_PASSWORD', 'IHR_EMAIL_PASSWORT_HIER'); // WICHTIG!
   ```

   **SMTP-Passwort finden:**
   - Loggen Sie sich im Netcup CCP ein
   - Navigieren Sie zu: **E-Mail → E-Mail-Konten**
   - Wählen Sie info@assistara.de aus
   - Notieren Sie das SMTP-Passwort (oder setzen Sie ein neues)

3. **reCAPTCHA Secret Key eintragen:**
   ```php
   define('RECAPTCHA_SECRET_KEY', 'IHR_SECRET_KEY_HIER'); // Von Schritt 2
   ```

4. **Umgebung auf Production setzen:**
   ```php
   define('ENVIRONMENT', 'production'); // 'development' → 'production'
   ```

5. **Speichern Sie die Datei**

⚠️ **WICHTIG:** `config.php` NIE in Git committen! (ist bereits in .gitignore)

---

### 4. Dateien auf Netcup hochladen

1. **Verbinden Sie sich per FTP/SFTP** mit Ihrem Netcup-Server:
   - **Host:** Ihre Domain oder Server-IP
   - **Benutzername:** Ihr FTP-Username (im CCP unter "FTP")
   - **Passwort:** Ihr FTP-Passwort
   - **Port:** 21 (FTP) oder 22 (SFTP)

2. **Navigieren Sie zum Web-Root-Verzeichnis:**
   - Meist: `/httpdocs/` oder `/public_html/`

3. **Laden Sie alle Dateien hoch:**
   ```
   /api/
   /components/
   /css/
   /data/
   /js/
   /*.html
   /.htaccess
   /CNAME
   ```

4. **Verzeichnisberechtigungen prüfen:**
   - `api/logs/` → 755 (oder 775)
   - Alle PHP-Dateien → 644
   - `.htaccess` → 644

---

### 5. SSL/HTTPS aktivieren

1. Loggen Sie sich im Netcup CCP ein
2. Navigieren Sie zu: **Domains → SSL-Zertifikate**
3. Wählen Sie Ihre Domain (assistara.de)
4. Klicken Sie auf "Let's Encrypt Zertifikat erstellen"
5. Aktivieren Sie "Automatische Erneuerung"
6. Warten Sie ca. 5-10 Minuten bis das Zertifikat aktiv ist

Das `.htaccess`-File erzwingt automatisch HTTPS-Weiterleitung.

---

### 6. Email-Konto testen

1. **SMTP-Verbindung testen:**
   - Im Netcup CCP: **E-Mail → E-Mail-Konten**
   - Wählen Sie info@assistara.de
   - **SMTP-Server:** smtp.netcup.net
   - **Port:** 587 (TLS) oder 465 (SSL)
   - **Authentifizierung:** Ja

2. **Test-Email senden:**
   - Verwenden Sie ein Email-Client (Thunderbird, Outlook)
   - Konfigurieren Sie das Konto mit den SMTP-Daten
   - Senden Sie eine Test-Email

---

### 7. Kontaktformular testen

1. **Öffnen Sie:** https://assistara.de/kontakt.html

2. **Funktionstest:**
   - Füllen Sie alle Felder aus
   - Klicken Sie auf "Nachricht senden"
   - Warten Sie auf Erfolgsmeldung

3. **Email-Empfang prüfen:**
   - Prüfen Sie info@assistara.de Posteingang
   - Admin-Benachrichtigung sollte ankommen
   - Bestätigungs-Email sollte an Absender gehen

4. **Spam-Test:**
   - Versuchen Sie 3-4 Anfragen schnell hintereinander
   - Ab der 4. sollte Rate-Limiting greifen

---

## 🔧 Fehlerbehebung

### Emails kommen nicht an

1. **Logs prüfen:**
   ```
   api/logs/contact-errors.log
   ```

2. **Häufige Probleme:**
   - **SMTP-Passwort falsch:** Prüfen Sie config.php
   - **Port blockiert:** Versuchen Sie Port 465 statt 587
   - **Email im Spam:** Prüfen Sie Spam-Ordner
   - **SPF/DKIM fehlt:** Netcup konfiguriert dies meist automatisch

3. **PHPMailer Fehler:**
   - Prüfen Sie ob alle 3 Dateien hochgeladen sind
   - Pfade in contact-handler.php korrekt?
   - Falls nicht: Nutzen Sie mail() Funktion (siehe PHPMAILER_INSTALLATION.md)

### reCAPTCHA Fehler

1. **"Sicherheitsprüfung fehlgeschlagen"**
   - Site Key korrekt in kontakt.html?
   - Secret Key korrekt in config.php?
   - Domain in reCAPTCHA Admin registriert?

2. **Score zu niedrig:**
   - Senken Sie `RECAPTCHA_MIN_SCORE` in config.php auf 0.3

### Rate Limiting zu streng

1. Öffnen Sie `api/config.php`
2. Erhöhen Sie:
   ```php
   define('MAX_SUBMISSIONS_PER_IP', 5); // statt 3
   define('RATE_LIMIT_WINDOW', 900); // 15 Minuten statt 10
   ```

### Session-Fehler

1. Prüfen Sie PHP Session-Support:
   - Erstellen Sie `test-session.php`:
   ```php
   <?php
   session_start();
   echo "Session funktioniert!";
   phpinfo();
   ?>
   ```
   - Aufrufen: https://assistara.de/test-session.php
   - Löschen Sie die Datei nach dem Test!

---

## 🔒 Sicherheits-Checkliste

- [ ] `api/config.php` mit echten Zugangsdaten konfiguriert
- [ ] `api/config.php` NICHT in Git committed
- [ ] reCAPTCHA v3 Site + Secret Key konfiguriert
- [ ] SSL/HTTPS aktiviert und erzwungen
- [ ] SMTP-Authentifizierung funktioniert
- [ ] Rate Limiting getestet
- [ ] Email-Versand getestet (Admin + User)
- [ ] Logs-Verzeichnis schreibbar (755)
- [ ] PHPMailer installiert
- [ ] `.htaccess` hochgeladen und aktiv
- [ ] Fehlerbehandlung getestet

---

## 📞 Support

**Bei Problemen:**
1. Prüfen Sie `api/logs/contact-errors.log`
2. Testen Sie PHP-Version: `<?php echo phpversion(); ?>`
3. Netcup Support: https://www.netcup-wiki.de/
4. PHPMailer Dokumentation: https://github.com/PHPMailer/PHPMailer

---

## 🎉 Fertig!

Ihr sicheres Kontaktformular ist jetzt live! 

**Features:**
✓ Sichere Email-Versendung via SMTP
✓ reCAPTCHA v3 Spam-Schutz
✓ Rate Limiting gegen Missbrauch
✓ CSRF-Schutz
✓ Automatische Bestätigungs-Emails
✓ Professionelle HTML-Email-Templates
✓ Error Logging
✓ HTTPS-Verschlüsselung
