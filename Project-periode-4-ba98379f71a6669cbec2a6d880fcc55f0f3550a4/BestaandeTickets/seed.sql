-- ============================================================
--  Voorbeelddata — Aurora Theater
--  Gevuld wanneer de reserveringen-tabel leeg is, zodat je
--  direct tickets kunt wijzigen en annuleren.
-- ============================================================

INSERT INTO zalen (id, naam) VALUES
  (1, 'Grote Zaal'),
  (2, 'Theaterzaal 2'),
  (3, 'Blauwe Zaal');

INSERT INTO voorstellingen (id, titel, datum, aanvangstijd, zaal_id, prijs_per_stoel, beschikbare_stoelen) VALUES
  (1, 'The Phantom of the Opera', '2026-07-10', '20:00', 1, 24.50, 480),
  (2, 'Soldaat van Oranje',       '2026-07-15', '19:30', 2, 39.00, 250),
  (3, 'Het Zwanenmeer (Ballet)',  '2026-07-20', '14:00', 3, 32.00, 120);

INSERT INTO reserveringen (gebruiker_id, voorstelling_id, aantal_stoelen, totaalprijs, betaalmethode, status, aangemaakt_op) VALUES
  (2, 1, 2, 49.00,  'ideal',      'bevestigd',      '2026-06-01 10:00:00'),
  (2, 2, 3, 117.00, 'pin',        'in_behandeling', '2026-06-05 14:30:00'),
  (2, 3, 1, 32.00,  'creditcard', 'geannuleerd',    '2026-06-08 09:00:00');
