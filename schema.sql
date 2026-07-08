-- ============================================================
--  Aurora Theater — databaseschema
--  Bevat o.a. de tabellen voor reserveringen en het
--  wijzigingen_log dat het overzicht van aanpassingen voedt.
-- ============================================================

CREATE DATABASE IF NOT EXISTS aurora_theater
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE aurora_theater;

CREATE TABLE IF NOT EXISTS zalen (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    naam    VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS voorstellingen (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    titel               VARCHAR(150) NOT NULL,
    datum               DATE NOT NULL,
    aanvangstijd        TIME NOT NULL,
    zaal_id             INT NOT NULL,
    prijs_per_stoel     DECIMAL(8,2) NOT NULL DEFAULT 0,
    beschikbare_stoelen INT NOT NULL DEFAULT 0,
    FOREIGN KEY (zaal_id) REFERENCES zalen(id)
);

CREATE TABLE IF NOT EXISTS stoelen (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    voorstelling_id INT NOT NULL,
    rij         VARCHAR(5) NOT NULL,
    nummer      INT NOT NULL,
    status      ENUM('vrij','bezet') NOT NULL DEFAULT 'vrij',
    FOREIGN KEY (voorstelling_id) REFERENCES voorstellingen(id)
);

CREATE TABLE IF NOT EXISTS reserveringen (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    gebruiker_id    INT NOT NULL,
    voorstelling_id INT NOT NULL,
    aantal_stoelen  INT NOT NULL DEFAULT 1,
    totaalprijs     DECIMAL(10,2) NOT NULL DEFAULT 0,
    betaalmethode   ENUM('ideal','creditcard','pin','contant') NOT NULL DEFAULT 'ideal',
    status          ENUM('bevestigd','in_behandeling','geannuleerd','voltooid') NOT NULL DEFAULT 'in_behandeling',
    aangemaakt_op   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    bijgewerkt_op   DATETIME NULL,
    FOREIGN KEY (voorstelling_id) REFERENCES voorstellingen(id)
);

CREATE TABLE IF NOT EXISTS reservering_stoelen (
    reservering_id INT NOT NULL,
    stoel_id       INT NOT NULL,
    PRIMARY KEY (reservering_id, stoel_id),
    FOREIGN KEY (reservering_id) REFERENCES reserveringen(id),
    FOREIGN KEY (stoel_id) REFERENCES stoelen(id)
);

CREATE TABLE IF NOT EXISTS wijzigingen_log (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    reservering_id INT NOT NULL,
    type           ENUM('aangemaakt','bewerkt','geannuleerd') NOT NULL,
    detail         TEXT NOT NULL,
    gewijzigd_op   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservering_id) REFERENCES reserveringen(id)
);
