-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Jan 21, 2024 at 11:49 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crud`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(255) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `Contact` varchar(13) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `Name`, `email`, `Contact`, `Password`) VALUES
(2, 'Admin100', 'admin100@gmail.com', '09774637466', 'admin100password');

-- --------------------------------------------------------

--
-- Table structure for table `inquiry`
--

CREATE TABLE `inquiry` (
  `id` int(255) NOT NULL,
  `FNAME` varchar(50) NOT NULL,
  `LNAME` varchar(50) NOT NULL,
  `PNUM` varchar(13) NOT NULL,
  `EMAIL` varchar(255) NOT NULL,
  `INQUIRY` varchar(255) NOT NULL,
  `Date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inquiry`
--

INSERT INTO `inquiry` (`id`, `FNAME`, `LNAME`, `PNUM`, `EMAIL`, `INQUIRY`, `Date`) VALUES
(1, 'George Daniel', 'Sobretodo', '09774637455', 'email.gmail.com', 'Baka naman, sa Sabado na defense mo.', '0000-00-00'),
(2, 'George Daniel', 'Sobretodo', '09774637455', 'email.gmail.com', 'Ako ulit, gumagana na ba yung automatic Date?', '2023-11-22');

-- --------------------------------------------------------

--
-- Table structure for table `paystat`
--

CREATE TABLE `paystat` (
  `name` varchar(50) NOT NULL,
  `contact` varchar(50) NOT NULL,
  `t_id` int(11) NOT NULL,
  `pay_stat` varchar(20) NOT NULL,
  `amount_due` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paystat`
--

INSERT INTO `paystat` (`name`, `contact`, `t_id`, `pay_stat`, `amount_due`) VALUES
('George Daniel D. Sobretodo', '09774637455', 1, 'paid', 500);

-- --------------------------------------------------------

--
-- Table structure for table `pending`
--

CREATE TABLE `pending` (
  `id` int(11) NOT NULL,
  `services` varchar(100) NOT NULL,
  `cusname` varchar(100) NOT NULL,
  `cusemail` varchar(100) NOT NULL,
  `cusnum` varchar(13) NOT NULL,
  `cusdate` date NOT NULL,
  `cusadd` varchar(200) NOT NULL,
  `cuscity` varchar(100) NOT NULL,
  `cusprov` varchar(100) NOT NULL,
  `Inquiry` varchar(255) NOT NULL,
  `Date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pending`
--

INSERT INTO `pending` (`id`, `services`, `cusname`, `cusemail`, `cusnum`, `cusdate`, `cusadd`, `cuscity`, `cusprov`, `Inquiry`, `Date`) VALUES
(4, 'Disinfection_Services ', 'Bareng, Ferdinand Marcos', 'ferdinand.bareng@gmail.com', '09121212568', '2023-12-07', 'Bll Lot Street, Village', 'Bacoor', 'Cavite', 'Do not enter bedroom.', '2023-11-25'),
(5, 'Disinfection_Services ', 'Sobretodo, George Daniel D.', 'geordansobretodo@gmail.com', '09774637455', '2023-12-29', 'BLk Lot Street, Village', 'City', 'Province', '...', '2023-11-28');

-- --------------------------------------------------------

--
-- Table structure for table `pending_scheduled`
--

CREATE TABLE `pending_scheduled` (
  `id` int(255) NOT NULL,
  `services` varchar(255) NOT NULL,
  `cusname` varchar(255) NOT NULL,
  `cusemail` varchar(255) NOT NULL,
  `cusnum` varchar(13) NOT NULL,
  `cusdate` date NOT NULL,
  `cusadd` varchar(255) NOT NULL,
  `cuscity` varchar(50) NOT NULL,
  `cusprov` varchar(50) NOT NULL,
  `Inquiry` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pending_scheduled`
--

INSERT INTO `pending_scheduled` (`id`, `services`, `cusname`, `cusemail`, `cusnum`, `cusdate`, `cusadd`, `cuscity`, `cusprov`, `Inquiry`) VALUES
(1, 'House_Premium_Cleaning', 'George', 'email@gmail.com', '09774637455', '0000-00-00', 'B10 L36 Street, Village', 'Bacoor', 'Cavite', 'Gusto ko yung kumikintab pagkatapos.'),
(2, 'Mattress_Deep_Cleaning', 'Full Name', 'customer@gmail.com', '+639774637455', '0000-00-00', 'Blk Lot Street, Village', 'This City', 'In this Province', 'Ulit, na-eerror yung mga nauna e.'),
(4, 'Disinfection_Services ', 'Name', 'email@gmail.com', '+639774637455', '0000-00-00', 'Bahay namin', 'dito sa Bacoor', 'Cavite', 'Bakit ayaw gumana ng Date.'),
(5, 'Mattress_Deep_Cleaning', 'Tao Ako', 'itoemailko@gmail.com', '+639977463745', '2023-11-28', 'Bahay at Lupa', 'Lungsod', 'Probinsya', 'Gumagana na ba yung Date?'),
(6, 'Office_Premium_Cleaning', 'Marlon', 'emailadd@gmail.com', '09909090909', '2023-11-30', 'blobk Lot Street, village', 'City of Bacoor', 'Cavite', 'Palinis po, yung maayos.'),
(7, 'Disinfection_Services', 'Ferdi', 'barengferdi@gmail.com', '09774637455', '2023-11-25', 'Blk', 'Bacoor', 'Cavite', 'Pawisikan');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inquiry`
--
ALTER TABLE `inquiry`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `paystat`
--
ALTER TABLE `paystat`
  ADD PRIMARY KEY (`t_id`);

--
-- Indexes for table `pending`
--
ALTER TABLE `pending`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pending_scheduled`
--
ALTER TABLE `pending_scheduled`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inquiry`
--
ALTER TABLE `inquiry`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `paystat`
--
ALTER TABLE `paystat`
  MODIFY `t_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pending`
--
ALTER TABLE `pending`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pending_scheduled`
--
ALTER TABLE `pending_scheduled`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
