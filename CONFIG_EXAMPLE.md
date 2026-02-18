# Assistara Kontaktformular - Konfigurationsbeispiel

Dieses File dient als Vorlage. Die echte `config.php` enthält sensible Daten und wird NICHT ins Git committed.

---

## Schnellstart

1. **Kopieren Sie `api/config.php`**
2. **Füllen Sie folgende Werte aus:**

### SMTP Zugangsdaten (Netcup)
```php
define('SMTP_USERNAME', 'info@assistara.de');
define('SMTP_PASSWORD', 'HIER_IHR_SMTP_PASSWORT'); // ← WICHTIG!
```

**Wo finde ich das SMTP-Passwort?**
- Netcup CCP → E-Mail → E-Mail-Konten → info@assistara.de
- Entweder vorhanden oder neu setzen

---

### reCAPTCHA v3 Keys
```php
define('RECAPTCHA_SECRET_KEY', 'HIER_IHR_SECRET_KEY'); // ← WICHTIG!
```

**Wie bekomme ich reCAPTCHA Keys?**
1. Besuchen Sie: https://www.google.com/recaptcha/admin
2. Erstellen Sie eine neue Site (v3)
3. Domain: assistara.de
4. Kopieren Sie **Site Key** (für kontakt.html) und **Secret Key** (für config.php)

---

### Umgebung
```php
define('ENVIRONMENT', 'production'); // 'development' nur für Tests!
```

**Development:** Zeigt Fehler an, weniger strenge Validierung
**Production:** Keine Fehleranzeige, volle Sicherheit

---

## Netcup SMTP-Einstellungen

Falls Standard-Einstellungen nicht funktionieren:

### Alternativer Port (SSL statt TLS)
```php
define('SMTP_PORT', 465);
define('SMTP_SECURE', 'ssl');
```

### Standard (empfohlen)
```php
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
```

---

## Anpassungen

### Rate Limiting ändern
Standardmäßig: 3 Anfragen pro 10 Minuten pro IP

**Weniger streng:**
```php
define('MAX_SUBMISSIONS_PER_IP', 5);
define('RATE_LIMIT_WINDOW', 900); // 15 Minuten
```

**Strenger:**
```php
define('MAX_SUBMISSIONS_PER_IP', 2);
define('RATE_LIMIT_WINDOW', 300); // 5 Minuten
```

---

### reCAPTCHA Score anpassen
Standardmäßig: 0.5 (mittel streng)

**Weniger streng (mehr Anfragen durchlassen):**
```php
define('RECAPTCHA_MIN_SCORE', 0.3);
```

**Strenger (weniger Spam, evtl. false positives):**
```php
define('RECAPTCHA_MIN_SCORE', 0.7);
```

---

### Validierungs-Limits ändern
Falls längere Nachrichten erlaubt werden sollen:

```php
define('MAX_MESSAGE_LENGTH', 10000); // Standard: 5000
define('MAX_NAME_LENGTH', 150);      // Standard: 100
```

---

## Email-Adressen ändern

Falls Anfragen an verschiedene Adressen gehen sollen:

```php
// Admin-Email (empfängt Formulardaten)
define('ADMIN_EMAIL', 'support@assistara.de');

// Absender-Email (erscheint im "Von"-Feld)
define('FROM_EMAIL', 'noreply@assistara.de');
define('FROM_NAME', 'Assistara Kontaktformular');
```

---

## Alternative: PHP mail() statt PHPMailer

Falls PHPMailer Probleme macht, kann die native `mail()` Funktion verwendet werden.

**Siehe:** `PHPMAILER_INSTALLATION.md` → Option 3

---

## Sicherheitshinweise

⚠️ **NIEMALS committen:**
- `api/config.php` mit echten Passwörtern
- `api/logs/*.log`
- `.env` Files

✓ **Immer prüfen:**
- Ist `.gitignore` korrekt?
- Sind Logs schreibbar? (755)
- Ist SSL/HTTPS aktiv?

---

## Test-Checkliste

Nach Konfiguration testen:

1. [ ] Formular ausfüllen und absenden
2. [ ] Admin-Email bei info@assistara.de angekommen?
3. [ ] Bestätigungs-Email beim Absender angekommen?
4. [ ] HTML-Design der Emails korrekt?
5. [ ] Spam-Filter getestet (3+ schnelle Anfragen)?
6. [ ] reCAPTCHA funktioniert (keine Fehler in Browser-Console)?
7. [ ] Logs werden bei Fehlern geschrieben?

---

Bei Fragen siehe `DEPLOYMENT.md` oder Netcup Support-Dokumentation.
