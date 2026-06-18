-- 1. Maak de database aan als deze nog niet bestaat
CREATE DATABASE IF NOT EXISTS `project_periode_4` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Zorg ervoor dat we in de juiste database werken
USE `project_periode_4`;

-- --------------------------------------------------------

-- 2. Maak de tabel 'shows' aan voor de voorstellingen
CREATE TABLE IF NOT EXISTS `shows` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `show_date` DATE NOT NULL,          -- Slaat datums veilig op als YYYY-MM-DD
  `show_time` TIME NOT NULL,          -- Slaat tijd veilig op als HH:MM:SS
  `hall` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------

-- 3. Voeg de testdata (Happy Flow) direct toe aan de database
INSERT INTO `shows` (`id`, `title`, `show_date`, `show_time`, `hall`) VALUES
(1, 'The Phantom of the Opera', '2026-06-12', '20:00:00', 'Grote Zaal'),
(2, 'Het Zwanenmeer', '2026-06-15', '19:30:00', 'Rode Zaal'),
(3, 'Soldaat van Oranje', '2026-06-18', '14:00:00', 'Theaterzaal 1'),
(4, 'De Klucht van de Molenaar', '2026-06-22', '20:15:00', 'Kleine Zaal')
ON DUPLICATE KEY UPDATE `title`=`title`;