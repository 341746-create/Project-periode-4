-- ============================================================
--  Aurora Theater — Database Initialisatie
--  MySQL 8.0+
-- ============================================================

CREATE DATABASE IF NOT EXISTS aurora_theater
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE aurora_theater;

-- ============================================================
--  TABEL: gebruikers
-- ============================================================
CREATE TABLE IF NOT EXISTS gebruikers (
    id              INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    naam            VARCHAR(100)    NOT NULL,
    email           VARCHAR(150)    NOT NULL UNIQUE,
    wachtwoord_hash VARCHAR(255)    NOT NULL,
    rol             ENUM('klant','medewerker','admin') NOT NULL DEFAULT 'klant',
    aangemaakt_op   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    bijgewerkt_op   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
--  TABEL: zalen  (theaters / halls)
-- ============================================================
CREATE TABLE IF NOT EXISTS zalen (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    naam        VARCHAR(100)    NOT NULL,
    capaciteit  SMALLINT        NOT NULL,
    beschrijving TEXT
) ENGINE=InnoDB;

-- ============================================================
--  TABEL: voorstellingen  (shows)
-- ============================================================
CREATE TABLE IF NOT EXISTS voorstellingen (
    id                  INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    titel               VARCHAR(200)    NOT NULL,
    beschrijving        TEXT,
    categorie           ENUM('drama','musical','klassiek','comedy','dans','opera') NOT NULL DEFAULT 'drama',
    datum               DATE            NOT NULL,
    aanvangstijd        TIME            NOT NULL,
    duur_minuten        SMALLINT        NOT NULL DEFAULT 120,
    zaal_id             INT UNSIGNED    NOT NULL,
    prijs_per_stoel     DECIMAL(6,2)    NOT NULL,
    afbeelding_url      VARCHAR(500),
    beschikbare_stoelen SMALLINT        NOT NULL,
    status              ENUM('gepland','actief','uitverkocht','geannuleerd') NOT NULL DEFAULT 'gepland',
    aangemaakt_op       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_voorstelling_zaal
        FOREIGN KEY (zaal_id) REFERENCES zalen(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
--  TABEL: stoelen  (individual seats per show)
-- ============================================================
CREATE TABLE IF NOT EXISTS stoelen (
    id              INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    voorstelling_id INT UNSIGNED    NOT NULL,
    rij             CHAR(2)         NOT NULL,   -- bijv. 'A', 'B', 'AA'
    nummer          TINYINT UNSIGNED NOT NULL,
    categorie       ENUM('standaard','premium','vip') NOT NULL DEFAULT 'standaard',
    status          ENUM('vrij','gereserveerd','bezet') NOT NULL DEFAULT 'vrij',

    UNIQUE KEY uq_stoel (voorstelling_id, rij, nummer),
    CONSTRAINT fk_stoel_voorstelling
        FOREIGN KEY (voorstelling_id) REFERENCES voorstellingen(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
--  TABEL: reserveringen  (bookings)
-- ============================================================
CREATE TABLE IF NOT EXISTS reserveringen (
    id                  INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    gebruiker_id        INT UNSIGNED    NOT NULL,
    voorstelling_id     INT UNSIGNED    NOT NULL,
    aantal_stoelen      TINYINT UNSIGNED NOT NULL DEFAULT 1,
    totaalprijs         DECIMAL(8,2)    NOT NULL,
    status              ENUM('in_behandeling','bevestigd','geannuleerd','voltooid') NOT NULL DEFAULT 'in_behandeling',
    betaalmethode       ENUM('ideal','creditcard','pin','contant') DEFAULT NULL,
    betaling_voltooid   BOOLEAN         NOT NULL DEFAULT FALSE,
    aangemaakt_op       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    bijgewerkt_op       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_reservering_gebruiker
        FOREIGN KEY (gebruiker_id) REFERENCES gebruikers(id) ON DELETE RESTRICT,
    CONSTRAINT fk_reservering_voorstelling
        FOREIGN KEY (voorstelling_id) REFERENCES voorstellingen(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
--  TABEL: reservering_stoelen  (koppeling reservering ↔ stoel)
-- ============================================================
CREATE TABLE IF NOT EXISTS reservering_stoelen (
    reservering_id  INT UNSIGNED    NOT NULL,
    stoel_id        INT UNSIGNED    NOT NULL,

    PRIMARY KEY (reservering_id, stoel_id),
    CONSTRAINT fk_rs_reservering
        FOREIGN KEY (reservering_id) REFERENCES reserveringen(id) ON DELETE CASCADE,
    CONSTRAINT fk_rs_stoel
        FOREIGN KEY (stoel_id) REFERENCES stoelen(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
--  INDEXEN  (performance)
-- ============================================================
CREATE INDEX idx_voorst_datum    ON voorstellingen(datum);
CREATE INDEX idx_voorst_status   ON voorstellingen(status);
CREATE INDEX idx_reserv_gebruiker ON reserveringen(gebruiker_id);
CREATE INDEX idx_reserv_status   ON reserveringen(status);

-- ============================================================
--  DEMO-DATA: Zalen
-- ============================================================
INSERT INTO zalen (naam, capaciteit, beschrijving) VALUES
    ('Grote Zaal',   500, 'Hoofdzaal met podium en balkon'),
    ('Kleine Zaal',  150, 'Intieme zaal voor kleinschalige producties'),
    ('Main Stage',   350, 'Modern podium met geavanceerde lichtinstallatie');

-- ============================================================
--  DEMO-DATA: Admin gebruiker
--  Wachtwoord: Admin@2026  (bcrypt hash)
-- ============================================================
INSERT INTO gebruikers (naam, email, wachtwoord_hash, rol) VALUES
    ('Admin Aurora', 'admin@aurora-theater.nl',
     '$2y$12$eUxJtdQ1P6BnDkS.0B4mWOzR6AeP1G9Lm8T3zNkVq5yYXwHiCbD2a', 'admin'),
    ('Demo Klant',   'demo@aurora-theater.nl',
     '$2y$12$eUxJtdQ1P6BnDkS.0B4mWOzR6AeP1G9Lm8T3zNkVq5yYXwHiCbD2a', 'klant');

-- ============================================================
--  DEMO-DATA: Voorstellingen
-- ============================================================
INSERT INTO voorstellingen
    (titel, beschrijving, categorie, datum, aanvangstijd, duur_minuten, zaal_id, prijs_per_stoel, afbeelding_url, beschikbare_stoelen, status)
VALUES
    ('Modern Drama',
     'Een emotioneel hedendaags toneelstuk over verlies, hoop en menselijke verbinding.',
     'drama', '2026-07-10', '20:00', 110, 1,
     24.50,
     'https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?auto=format&fit=crop&w=800&q=80',
     480, 'actief'),

    ('Broadway Musical Night',
     'Spectaculaire live zang- en dansproductie met nummers uit de grootste Broadway-hits.',
     'musical', '2026-07-15', '19:30', 150, 3,
     39.00,
     'https://images.unsplash.com/photo-1507924538820-ede94a04019d?auto=format&fit=crop&w=800&q=80',
     330, 'actief'),

    ('Klassiek Shakespeare',
     'Tijdloze teksten van William Shakespeare, prachtig vertolkt door een getalenteerd ensemble.',
     'klassiek', '2026-07-22', '20:00', 135, 2,
     19.50,
     'https://images.unsplash.com/photo-1518972559570-7cc1309f3229?auto=format&fit=crop&w=800&q=80',
     140, 'actief');

-- ============================================================
--  KLAAR
-- ============================================================
SELECT 'Aurora Theater database succesvol aangemaakt!' AS status;
