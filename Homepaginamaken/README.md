# 🎭 Aurora Theater — Project Periode 4

Een volledig PHP/MySQL webapplicatie voor theaterbeheer: voorstellingen bekijken, tickets reserveren, scannen en beheren.

---

## 📁 Projectstructuur

```
Project-periode-4-Dev/
├── index.php                          ← Homepagina (PHP + DB)
├── style.css                          ← Hoofdstijlen
├── script.js                          ← Hoofdscript
│
├── config/
│   └── database.php                   ← PDO database verbinding (singleton)
│
├── database/
│   ├── init.sql                       ← Database tabellen + demo-data
│   └── setup.php                      ← CLI setup script
│
├── Homepaginamaken/
│   └── index.php                      ← Developer overzicht pagina
│
├── NieuweTicketToevoegen/
│   ├── index.php                      ← Multi-stap reserveringsformulier
│   ├── style.css
│   ├── script.js
│   └── api/
│       └── create_ticket.php          ← POST: maak reservering aan
│
├── ticket overzicht/
│   ├── index.php                      ← Overzicht van reserveringen
│   ├── style.css
│   ├── script.js
│   └── api/
│       ├── get_tickets.php            ← GET: haal tickets op
│       ├── update_ticket.php          ← POST: bewerk reservering
│       └── cancel_ticket.php          ← POST: annuleer reservering
│
└── ticket scannen/
    ├── index.php                      ← Ticket scanner UI
    ├── style.css
    ├── script.js
    └── api/
        └── scan_ticket.php            ← POST: valideer ticketcode
```

---

## 🚀 Installatie

### 1. Vereisten
- PHP 8.1+
- MySQL 8.0+
- Een lokale server (XAMPP, Laragon, MAMP, of PHP built-in server)

### 2. Database aanmaken

```bash
# Met root gebruiker (voer in vanuit de projectmap):
php database/setup.php --user=root --pass=jouw_wachtwoord
```

Of importeer handmatig in phpMyAdmin:
```
database/init.sql
```

### 3. Database configuratie aanpassen

Bewerk `config/database.php` als je andere inloggegevens gebruikt:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'aurora_theater');
define('DB_USER', 'aurora_user');      // of 'root'
define('DB_PASS', 'AuroraPass2026!'); // jouw wachtwoord
```

### 4. Project starten

```bash
# Met PHP built-in server (vanuit projectmap):
php -S localhost:8080

# Bezoek dan:
# http://localhost:8080/index.php
```

---

## 🎟️ Ticketcode Formaat

Tickets krijgen automatisch een code bij reservering:

```
AUR-{reservering_id}-{gebruiker_id}
```

Voorbeeld: `AUR-5-2`

Gebruik deze code in de **Ticket Scanner** om in te checken.

**Demo-codes** (werken ook zonder database):
- `THR12345`
- `THR67890`
- `VIP2025`
- `SHOW001`

---

## 🗄️ Database Tabellen

| Tabel | Beschrijving |
|---|---|
| `gebruikers` | Klanten, medewerkers en admins |
| `zalen` | Theaterzalen (Grote Zaal, Kleine Zaal, Main Stage) |
| `voorstellingen` | Drama, Musical, Klassiek, Comedy, Dans, Opera |
| `stoelen` | Individuele stoelen per voorstelling |
| `reserveringen` | Boekingen per gebruiker |
| `reservering_stoelen` | Koppeltabel reservering ↔ stoel |

---

## 📡 API Endpoints

| Methode | URL | Beschrijving |
|---|---|---|
| `GET` | `/ticket overzicht/api/get_tickets.php?gebruiker_id=2` | Haal tickets op |
| `POST` | `/ticket overzicht/api/update_ticket.php` | Bewerk reservering |
| `POST` | `/ticket overzicht/api/cancel_ticket.php` | Annuleer reservering |
| `POST` | `/ticket scannen/api/scan_ticket.php` | Valideer ticketcode |
| `POST` | `/NieuweTicketToevoegen/api/create_ticket.php` | Nieuwe reservering |

---

## 🔐 Demo Inloggegevens

| Rol | E-mail | Wachtwoord |
|---|---|---|
| Admin | admin@aurora-theater.nl | Admin@2026 |
| Klant | demo@aurora-theater.nl | Admin@2026 |

---

## 📝 Commit Geschiedenis

1. `init: project structuur en database setup`
2. `feat: homepagina omgezet naar PHP`
3. `feat: ticket overzicht omgezet naar PHP`
4. `feat: ticket overzicht API endpoints (get, update, cancel)`
5. `feat: ticket scannen omgezet naar PHP`
6. `feat: ticket scannen API endpoint`
7. `feat: nieuw ticket toevoegen pagina`
8. `feat: nieuw ticket toevoegen API`
9. `feat: homepaginamaken sectie toegevoegd`
10. `docs: README bijgewerkt met setup instructies`

---

## 👨‍💻 Technologie

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 8.1+ (PDO)
- **Database**: MySQL 8.0+ (InnoDB)
- **Stijlen**: Custom CSS, Google Fonts (Inter), Font Awesome 6

&copy; 2026 Aurora Theater
