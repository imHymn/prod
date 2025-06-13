-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 11, 2025 at 08:39 AM
-- Server version: 8.0.42-0ubuntu0.22.04.1
-- PHP Version: 8.1.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `robertsprod`
--

-- --------------------------------------------------------

--
-- Table structure for table `assembly_list_new`
--

CREATE TABLE `assembly_list_new` (
  `id` int NOT NULL,
  `itemID` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reference_no` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `model` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `material_no` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `material_description` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `shift` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lot_no` int DEFAULT NULL,
  `done_quantity` int DEFAULT NULL,
  `pending_quantity` int DEFAULT NULL,
  `total_quantity` int DEFAULT NULL,
  `section` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `person_incharge` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `time_in` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `time_out` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_needed` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assembly_list_new`
--

INSERT INTO `assembly_list_new` (`id`, `itemID`, `reference_no`, `model`, `material_no`, `material_description`, `shift`, `lot_no`, `done_quantity`, `pending_quantity`, `total_quantity`, `section`, `status`, `person_incharge`, `time_in`, `time_out`, `date_needed`, `created_at`) VALUES
(1, '1', '20250611-0001', 'L300', '80024330', '09-MIT-L3-MB282966X10L-CMBR RR PANEL ASY', '1st Shift', 1, 20, 10, 30, 'qc', 'done', 'ASSEMBLY1', '2025-06-11 7:00:00', '2025-06-11 7:22:00', '2025-06-11', '2025-06-11 13:51:50'),
(2, '27', '20250611-0001', 'L300', '80024330', '09-MIT-L3-MB282966X10L-CMBR RR PANEL ASY', '1st Shift', 1, 10, 0, 30, 'qc', 'done', 'ASSEMBLY1', '2025-06-11 7:50:00', '2025-06-11 8:00:00', '2025-06-11', '2025-06-11 13:54:14'),
(3, '2', '20250611-0002', 'L300', '80035725', '09-MIT-MB507047L1 HOUSING HEADLAMP LH', '1st Shift', 1, 30, -20, 30, 'qc', 'done', 'ASSEMBLY2', '2025-06-11 7:00:00', '2025-06-11 7:33:00', '2025-06-11', '2025-06-11 14:01:08'),
(4, '3', '20250611-0003', 'L300', '80035726', '09-MIT-5215A320Y1 HOUSING HEADLAMP RH', '1st Shift', 1, 20, -10, 30, 'qc', 'done', 'ASSEMBLY1', '2025-06-11 8:05:00', '2025-06-11 8:27:00', '2025-06-11', '2025-06-11 14:03:27'),
(5, '28', '20250611-0003', 'L300', '80035726', '09-MIT-5215A320Y1 HOUSING HEADLAMP RH', '1st Shift', 1, 10, 0, 30, 'qc', 'done', 'ASSEMBLY3', '2025-06-11 7:00:00', '2025-06-11 7:12:00', '2025-06-11', '2025-06-11 14:07:31'),
(6, '4', '20250611-0004', 'L300', '80120170', '09-MIT-MT1-MB280917L-REINF, LWR (LHD)', '1st Shift', 1, 25, -15, 30, 'qc', 'done', 'ASSEMBLY1', '2025-06-12 7:00:00', '2025-06-12 7:28:30', '2025-06-11', '2025-06-12 14:11:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assembly_list_new`
--
ALTER TABLE `assembly_list_new`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assembly_list_new`
--
ALTER TABLE `assembly_list_new`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
