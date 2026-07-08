-- =====================================================================
-- database.sql
-- Project: projectperiode4 — User Story 121 "Nieuwe Medewerker Toevoegen"
--
-- Doel: een administrator kan een nieuwe medewerker toevoegen aan het
-- systeem, zodat de personeelslijst up-to-date blijft en de nieuwe
-- medewerker geregistreerd staat binnen de applicatie.
--
-- Compatibel met MySQL 8.0+ / MariaDB 10.4+
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS personeelsbeheer;
CREATE DATABASE personeelsbeheer
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE personeelsbeheer;

-- ---------------------------------------------------------------------
-- Tabel: afdelingen
-- De afdeling waarbinnen een medewerker werkzaam is.
-- ---------------------------------------------------------------------
CREATE TABLE afdelingen (
    afdeling_id     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    naam            VARCHAR(100) NOT NULL,
    omschrijving    VARCHAR(255) NULL,
    PRIMARY KEY (afdeling_id),
    UNIQUE KEY uq_afdeling_naam (naam)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabel: functies
-- De functietitel / rol die een medewerker binnen het bedrijf vervult.
-- ---------------------------------------------------------------------
CREATE TABLE functies (
    functie_id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titel           VARCHAR(100) NOT NULL,
    PRIMARY KEY (functie_id),
    UNIQUE KEY uq_functie_titel (titel)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabel: gebruikers
-- De accounts die toegang hebben tot het systeem (o.a. de administrator
-- die medewerkers toevoegt, conform "Als administrator wil ik...").
-- ---------------------------------------------------------------------
CREATE TABLE gebruikers (
    gebruiker_id    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    gebruikersnaam  VARCHAR(50)  NOT NULL,
    wachtwoord_hash VARCHAR(255) NOT NULL,
    rol             ENUM('administrator', 'medewerker') NOT NULL DEFAULT 'medewerker',
    aangemaakt_op   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (gebruiker_id),
    UNIQUE KEY uq_gebruikersnaam (gebruikersnaam)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabel: medewerkers
-- De personeelslijst zelf — de kern van user story 121.
-- Verplichte velden komen overeen met de "Acceptance Criteria"
-- (Scenario 1: alle verplichte gegevens correct invullen en opslaan).
-- ---------------------------------------------------------------------
CREATE TABLE medewerkers (
    medewerker_id     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    personeelsnummer  VARCHAR(20)  NOT NULL,
    voornaam          VARCHAR(100) NOT NULL,
    achternaam        VARCHAR(100) NOT NULL,
    email             VARCHAR(150) NOT NULL,
    telefoonnummer    VARCHAR(20)  NULL,
    geboortedatum     DATE         NULL,
    datum_in_dienst   DATE         NOT NULL,
    afdeling_id       INT UNSIGNED NOT NULL,
    functie_id        INT UNSIGNED NOT NULL,
    status            ENUM('actief', 'inactief') NOT NULL DEFAULT 'actief',
    toegevoegd_door   INT UNSIGNED NULL,
    aangemaakt_op     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    bijgewerkt_op     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                       ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (medewerker_id),
    UNIQUE KEY uq_personeelsnummer (personeelsnummer),
    UNIQUE KEY uq_email (email),
    KEY idx_achternaam (achternaam),
    CONSTRAINT fk_medewerker_afdeling
        FOREIGN KEY (afdeling_id) REFERENCES afdelingen(afdeling_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_medewerker_functie
        FOREIGN KEY (functie_id) REFERENCES functies(functie_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_medewerker_toegevoegd_door
        FOREIGN KEY (toegevoegd_door) REFERENCES gebruikers(gebruiker_id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabel: audit_log
-- Houdt bij wie wanneer een medewerker heeft toegevoegd of gewijzigd.
-- Nuttig om "Updated by ... op ..." (zoals zichtbaar in Azure DevOps)
-- ook voor de personeelslijst te kunnen tonen.
-- ---------------------------------------------------------------------
CREATE TABLE audit_log (
    log_id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    medewerker_id   INT UNSIGNED NOT NULL,
    gebruiker_id    INT UNSIGNED NULL,
    actie           ENUM('toegevoegd', 'gewijzigd', 'gedeactiveerd') NOT NULL,
    actie_op        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    details         VARCHAR(255) NULL,
    PRIMARY KEY (log_id),
    CONSTRAINT fk_log_medewerker
        FOREIGN KEY (medewerker_id) REFERENCES medewerkers(medewerker_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_log_gebruiker
        FOREIGN KEY (gebruiker_id) REFERENCES gebruikers(gebruiker_id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- Voorbeelddata
-- =====================================================================

INSERT INTO afdelingen (naam, omschrijving) VALUES
    ('Artistiek',      'Regie, choreografie en artistieke leiding'),
    ('Techniek',       'Licht, geluid en decor'),
    ('Front of House', 'Kassa, garderobe en publieksbegeleiding'),
    ('Productie',      'Planning, marketing en algemeen beheer');

INSERT INTO functies (titel) VALUES
    ('Regisseur'),
    ('Acteur'),
    ('Lichttechnicus'),
    ('Geluidstechnicus'),
    ('Decorbouwer'),
    ('Kassamedewerker'),
    ('Producent'),
    ('Administrator');

INSERT INTO gebruikers (gebruikersnaam, wachtwoord_hash, rol) VALUES
    ('i.ghafoori', '$2y$10$replace_with_real_bcrypt_hash_admin', 'administrator'),
    ('s.devries',  '$2y$10$replace_with_real_bcrypt_hash_user',  'medewerker');

INSERT INTO medewerkers
    (personeelsnummer, voornaam, achternaam, email, telefoonnummer,
     geboortedatum, datum_in_dienst, afdeling_id, functie_id, status, toegevoegd_door)
VALUES
    ('PN-1001', 'Sanne',  'de Vries',  's.devries@theater.nl',  '0612345678',
     '1994-03-12', '2021-09-01', 1, 1, 'actief', 1),
    ('PN-1002', 'Daan',   'Bakker',    'd.bakker@theater.nl',   '0623456789',
     '1998-07-22', '2022-02-15', 2, 3, 'actief', 1),
    ('PN-1003', 'Layla',  'El Amrani', 'l.elamrani@theater.nl', '0634567890',
     '1996-11-05', '2023-05-10', 3, 6, 'actief', 1);

-- =====================================================================
-- Voorbeeld: query passend bij Scenario 1 van de Acceptance Criteria
-- ("Overzicht medewerkers" dashboard, met afdeling en functie zichtbaar)
-- =====================================================================
-- SELECT m.medewerker_id, m.personeelsnummer, m.voornaam, m.achternaam,
--        m.email, a.naam AS afdeling, f.titel AS functie, m.status,
--        m.datum_in_dienst
-- FROM medewerkers m
-- JOIN afdelingen a ON a.afdeling_id = m.afdeling_id
-- JOIN functies f   ON f.functie_id  = m.functie_id
-- ORDER BY m.achternaam, m.voornaam;