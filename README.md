# BestaandeTickets — Ticketbeheer Aurora Theater

Met deze module kunnen gebruikers hun **bestaande reserveringen wijzigen en annuleren**.

## Functionaliteit

- **Wijzigen**: pas het aantal stoelen en de betaalmethode aan van een lopende reservering.
  Prijs en beschikbare stoelen worden automatisch bijgewerkt en gecontroleerd.
- **Annuleren**: zet een reservering op `geannuleerd`, geeft de stoelen vrij en
  vraagt optioneel naar een reden.
- **Wijzigingsoverzicht**: alle aanpassingen worden gelogd in `wijzigingen_log`
  en getoond in een overzichtstabel.

## Bestanden

- `index.php` — overzicht van tickets + bewerk-/annuleer-modals
- `script.js` — frontend logica (fetch naar de API's, changelog)
- `style.css` — opmaak
- `api/update_ticket.php` — werkt een reservering bij
- `api/cancel_ticket.php` — annuleert een reservering
- `config/database.php` — databaseverbinding
- `config/helpers.php` — `logWijziging()` helper
- `schema.sql` — aanmaak van de benodigde tabellen

## Installatie

1. Importeer het schema: `mysql < schema.sql`
2. Pas indien nodig de inloggegevens aan in `config/database.php`
3. Plaats de map in de documentroot van je webserver (bijv. `htdocs/BestaandeTickets`)
4. Open `index.php` in de browser

Werkt de database niet? De interface toont dan demo-data en simuleert
wijzigingen in de browser (demo-modus).
