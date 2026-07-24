-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sty 08, 2026 at 08:15 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `trening_app`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `plan_treningowy`
--

CREATE TABLE `plan_treningowy` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dzien_tygodnia` enum('Poniedziałek','Wtorek','Środa','Czwartek','Piątek','Sobota','Niedziela') NOT NULL,
  `cwiczenie_id` int(11) NOT NULL,
  `powtorzenia` int(11) NOT NULL,
  `serie` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plan_treningowy`
--

INSERT INTO `plan_treningowy` (`id`, `user_id`, `dzien_tygodnia`, `cwiczenie_id`, `powtorzenia`, `serie`) VALUES
(1, 1, 'Poniedziałek', 1, 10, 3),
(2, 1, 'Poniedziałek', 14, 3, 10),
(3, 1, 'Środa', 14, 3, 10),
(4, 1, 'Sobota', 85, 4, 10),
(5, 2, 'Poniedziałek', 1, 10, 4),
(6, 2, 'Poniedziałek', 3, 12, 3),
(7, 2, 'Wtorek', 5, 10, 4),
(8, 2, 'Wtorek', 14, 20, 3),
(9, 2, 'Środa', 85, 30, 3),
(10, 2, 'Piątek', 7, 10, 3),
(11, 2, 'Sobota', 8, 12, 3),
(12, 2, 'Sobota', 82, 10, 3),
(13, 3, 'Poniedziałek', 1, 10, 4),
(14, 3, 'Poniedziałek', 3, 12, 3),
(15, 3, 'Poniedziałek', 14, 20, 3),
(16, 3, 'Wtorek', 6, 10, 4),
(17, 3, 'Czwartek', 10, 12, 3),
(18, 3, 'Piątek', 7, 10, 4),
(19, 3, 'Piątek', 16, 12, 3),
(20, 3, 'Sobota', 18, 30, 1),
(21, 3, 'Poniedziałek', 88, 40, 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `rodzaje_cwiczen`
--

CREATE TABLE `rodzaje_cwiczen` (
  `id` int(11) NOT NULL,
  `nazwa` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rodzaje_cwiczen`
--

INSERT INTO `rodzaje_cwiczen` (`id`, `nazwa`) VALUES
(87, ''),
(85, 'Bieg w miejscu (High Knees)'),
(88, 'Bierznia'),
(14, 'Brzuszki'),
(82, 'Burpees'),
(86, 'Mountain Climbers'),
(18, 'Orbitrek'),
(13, 'Plank (Deska)'),
(4, 'Podciąganie na drążku'),
(80, 'Pompki diamentowe'),
(83, 'Pompki z nogami na podwyższeniu'),
(17, 'Prostowanie ramion na wyciągu (triceps)'),
(81, 'Przysiady bułgarskie'),
(19, 'Rowerek stacjonarny'),
(3, 'Rozpiętki'),
(6, 'Ściąganie drążka wyciągu górnego'),
(20, 'Skakanka'),
(11, 'Uginanie nóg na maszynie'),
(16, 'Uginanie ramion ze sztangą (biceps)'),
(5, 'Wiosłowanie sztangą'),
(12, 'Wspięcia na palce'),
(2, 'Wyciskanie hantli na skosie'),
(1, 'Wyciskanie sztangi na ławce'),
(7, 'Wyciskanie żołnierskie (barki)'),
(9, 'Wykroki z hantlami'),
(10, 'Wypychanie nóg na suwnicy'),
(84, 'Wznosy bioder (Glute bridge)'),
(8, 'Wznosy hantli bokiem'),
(15, 'Wznosy nóg w zwisie');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `uzytkownicy`
--

CREATE TABLE `uzytkownicy` (
  `id` int(11) NOT NULL,
  `login` varchar(50) NOT NULL,
  `haslo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uzytkownicy`
--

INSERT INTO `uzytkownicy` (`id`, `login`, `haslo`) VALUES
(1, 'admin', '$2y$10$xg6FrRcgcxJNbuXGjf0B1uOpLaqfq/tILQlxlKb4T6hh3o5/RbrL.'),
(2, '123', '$2y$10$kD0lT0g5Al2w38GgN5Nsyuue3Hk8tR2tNjKMZQ3P2llY5QLN5TEhy'),
(3, '1234', '$2y$10$cRqMLKWCd5zaVD9rpSjgO.thJnjR3kvwyjQxuht7GBYu/IGSqJ9Ly');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `plan_treningowy`
--
ALTER TABLE `plan_treningowy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `cwiczenie_id` (`cwiczenie_id`);

--
-- Indeksy dla tabeli `rodzaje_cwiczen`
--
ALTER TABLE `rodzaje_cwiczen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nazwa` (`nazwa`);

--
-- Indeksy dla tabeli `uzytkownicy`
--
ALTER TABLE `uzytkownicy`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `plan_treningowy`
--
ALTER TABLE `plan_treningowy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `rodzaje_cwiczen`
--
ALTER TABLE `rodzaje_cwiczen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `uzytkownicy`
--
ALTER TABLE `uzytkownicy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `plan_treningowy`
--
ALTER TABLE `plan_treningowy`
  ADD CONSTRAINT `plan_treningowy_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `uzytkownicy` (`id`),
  ADD CONSTRAINT `plan_treningowy_ibfk_2` FOREIGN KEY (`cwiczenie_id`) REFERENCES `rodzaje_cwiczen` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
