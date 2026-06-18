-- 1. Maak de database aan als deze nog niet bestaat
CREATE DATABASE IF NOT EXISTS `project_periode_4` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Zorg ervoor dat we in de juiste database werken
USE `project_periode_4`;

-- --------------------------------------------------------

-- 2. Maak de tabel 'employees' aan op basis van jouw HTML-formulier
CREATE TABLE IF NOT EXISTS `employees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL, -- Ruimte voor veilig gehashte wachtwoorden
  `role` VARCHAR(20) DEFAULT 'Medewerker',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------

-- 3. Voeg de bestaande testdata toe uit jouw HTML-tabel
INSERT INTO `employees` (`id`, `first_name`, `last_name`, `email`, `password`, `role`) VALUES
(1, 'Imraan', 'Ghafoori', 'i.ghafoori@auroratheater.nl', '$2y$10$EIfb0Jxw17X3A6/X4G16O.LkmP.6G9iW2fUXlH/Y1A2B3C4D5E6F', 'Medewerker'),
(2, 'Anouk', 'de Jong', 'a.dejong@auroratheater.nl', '$2y$10$EIfb0Jxw17X3A6/X4G16O.LkmP.6G9iW2fUXlH/Y1A2B3C4D5E6F', 'Medewerker')
ON DUPLICATE KEY UPDATE `email`=`email`;