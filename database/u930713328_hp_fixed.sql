-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 09, 2026 at 09:47 PM
-- Server version: 11.8.9-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u930713328_hp`
--

-- --------------------------------------------------------

--
-- Table structure for table `albums`
--

CREATE TABLE `albums` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `event_type` enum('General','Meetings','Ceremony','Game') NOT NULL DEFAULT 'Game',
  `day` int(11) NOT NULL DEFAULT 1,
  `game_id` int(11) DEFAULT NULL,
  `match_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cover_photo_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text NOT NULL,
  `ip_address` varchar(50) DEFAULT '127.0.0.1',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'SYSTEM_DEPLOYMENT_PREPARED', 'Cleared test athletes and media repository; prepared database for live server deployment.', '127.0.0.1', '2026-10-09 01:44:22'),
(2, 1, 'ATHLETE_ROSTER_IMPORTED', 'Imported official athlete roster from EZ.xlsx: 370 athletes registered across 10 HPCL units.', '127.0.0.1', '2026-10-09 03:48:39'),
(3, 1, 'DRAWS_PUBLISHED', 'Published draws for Game #1 to public dashboard (FR-10)', '152.58.30.219', '2026-10-09 05:41:43'),
(4, 1, 'DRAWS_PUBLISHED', 'Published draws for Game #778 to public dashboard (FR-10)', '152.58.30.219', '2026-10-09 05:41:51'),
(5, 1, 'DRAWS_PUBLISHED', 'Published draws for Game #779 to public dashboard (FR-10)', '152.58.30.219', '2026-10-09 05:41:55'),
(6, 1, 'DRAWS_PUBLISHED', 'Published draws for Game #780 to public dashboard (FR-10)', '152.58.30.219', '2026-10-09 05:41:59'),
(7, 1, 'DRAWS_PUBLISHED', 'Published draws for Game #781 to public dashboard (FR-10)', '152.58.30.219', '2026-10-09 05:42:03'),
(8, 1, 'DRAWS_PUBLISHED', 'Published draws for Game #2 to public dashboard (FR-10)', '152.58.30.219', '2026-10-09 05:42:07'),
(9, 1, 'DRAWS_PUBLISHED', 'Published draws for Game #782 to public dashboard (FR-10)', '152.58.30.219', '2026-10-09 05:42:21'),
(10, 3, 'FINAL_SCORE_SYNC', 'Match #592 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 856. Score Summary: MR Table Tennis won (3-0, 0-3, 0-3, 3-0, 3-0)', '152.58.16.122', '2026-10-09 06:45:34'),
(11, 3, 'FINAL_SCORE_SYNC', 'Match #715 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 837. Score Summary: WZ Badminton won (15-0, 0-20, 15-0)', '106.192.209.245', '2026-10-09 06:48:24'),
(12, 3, 'LIVE_SCORE_UPDATE', 'Match #594 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 793. Score Summary: 3-0, 3-0, 3-0 (Live - Srv: VR)', '152.58.16.185', '2026-10-09 06:49:28'),
(13, 3, 'FINAL_SCORE_SYNC', 'Match #745 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 846. Score Summary: NZ Badminton won (15-3, 13-15, 8-15)', '106.192.209.245', '2026-10-09 06:56:45'),
(14, 3, 'LIVE_SCORE_UPDATE', 'Match #590 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 892. Score Summary: 2-3, 3-0, 0-3, 3-0, 3-1 (Live - Srv: HB)', '152.58.16.76', '2026-10-09 06:58:57'),
(15, 3, 'LIVE_SCORE_UPDATE', 'Match #589 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 838. Score Summary: 3-2, 3-0, 3-1 (Live - Srv: WZ)', '152.58.16.76', '2026-10-09 07:00:33'),
(16, 3, 'FINAL_SCORE_SYNC', 'Match #730 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 873. Score Summary: SCZ Badminton won (15-12, 15-11)', '106.192.209.245', '2026-10-09 07:01:18'),
(17, 3, 'FINAL_SCORE_SYNC', 'Match #481 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 894. Score Summary: HB Carrom won (Board 1: 1 - 2)', '152.58.31.100', '2026-10-09 07:03:51'),
(18, 3, 'FINAL_SCORE_SYNC', 'Match #716 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 819. Score Summary: PH Badminton won (10-15, 11-15)', '106.192.209.245', '2026-10-09 07:05:44'),
(19, 3, 'LIVE_SCORE_UPDATE', 'Match #631 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 820. Score Summary: 2-0, 2-0 (Live - Srv: PH)', '152.58.16.76', '2026-10-09 07:08:10'),
(20, 3, 'FINAL_SCORE_SYNC', 'Match #756 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 792. Score Summary: VR Badminton won (15-11, 17-15)', '106.192.209.245', '2026-10-09 07:08:21'),
(21, 3, 'FINAL_SCORE_SYNC', 'Match #744 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 819. Score Summary: PH Badminton won (15-7, 16-15)', '106.192.209.245', '2026-10-09 07:10:38'),
(22, 3, 'FINAL_SCORE_SYNC', 'Match #714 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 855. Score Summary: MR Badminton won (15-3, 15-5)', '106.192.209.245', '2026-10-09 07:12:22'),
(23, 3, 'FINAL_SCORE_SYNC', 'Match #488 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 840. Score Summary: WZ Carrom won (Board 1: 1 - 2)', '152.58.31.100', '2026-10-09 07:14:15'),
(24, 3, 'FINAL_SCORE_SYNC', 'Match #486 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 822. Score Summary: PH Carrom won (Board 1: 0 - 3)', '152.58.31.100', '2026-10-09 07:15:07'),
(25, 3, 'FINAL_SCORE_SYNC', 'Match #731 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 828. Score Summary: SZ Badminton won (15-5, 15-9)', '106.192.223.245', '2026-10-09 07:19:09'),
(26, 1, 'CREATE_MATCH', 'Manually created Match #772 (Round 2) for Game #779', '152.58.30.229', '2026-10-09 07:26:47'),
(27, 1, 'CREATE_MATCH', 'Manually created Match #773 (Round 2) for Game #779', '152.58.30.229', '2026-10-09 07:29:30'),
(28, 3, 'FINAL_SCORE_SYNC', 'Match #773 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 894. Score Summary: HB Carrom won (Board 1: 2 - 1)', '152.58.31.202', '2026-10-09 07:31:07'),
(29, 3, 'LIVE_SCORE_UPDATE', 'Match #591 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 892. Score Summary: 1-3, 0-3, 1-3 (Live - Srv: EZ)', '106.192.121.111', '2026-10-09 07:35:49'),
(30, 3, 'FINAL_SCORE_SYNC', 'Match #729 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 792. Score Summary: VR Badminton won (15-4, 8-15, 15-5)', '106.192.220.245', '2026-10-09 07:39:31'),
(31, 3, 'FINAL_SCORE_SYNC', 'Match #417 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 857. Score Summary: MR Bridge won (Session 1 T1: IMPs 0-0 | VPs 19.74-0.26)', '106.192.211.226', '2026-10-09 07:40:01'),
(32, 3, 'FINAL_SCORE_SYNC', 'Match #757 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 855. Score Summary: MR Badminton won (15-1, 15-1)', '106.192.220.245', '2026-10-09 07:41:06'),
(33, 3, 'LIVE_SCORE_UPDATE', 'Match #420 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 821. Score Summary: Session 1 T1: IMPs 0-0 | VPs 19.74-7.23 (Live)', '106.192.211.226', '2026-10-09 07:46:32'),
(34, 3, 'LIVE_SCORE_UPDATE', 'Match #419 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 794. Score Summary: Session 1 T1: IMPs 0-0 | VPs 7.58-10.00 (Live)', '106.192.211.226', '2026-10-09 07:48:00'),
(35, 3, 'LIVE_SCORE_UPDATE', 'Match #418 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 839. Score Summary: Session 1 T1: IMPs 0-0 | VPs 0.26-10.00 (Live)', '106.192.211.226', '2026-10-09 07:48:48'),
(36, 3, 'FINAL_SCORE_SYNC', 'Match #746 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 819. Score Summary: PH Badminton won (In Progress)', '106.192.220.245', '2026-10-09 07:49:42'),
(37, 3, 'FINAL_SCORE_SYNC', 'Match #747 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 864. Score Summary: NWZ Badminton won (15-6, 15-12)', '106.192.214.245', '2026-10-09 08:07:57'),
(38, 3, 'FINAL_SCORE_SYNC', 'Match #483 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 849. Score Summary: NZ Carrom won (Board 1: 0 - 3)', '152.58.31.45', '2026-10-09 08:20:37'),
(39, 3, 'FINAL_SCORE_SYNC', 'Match #484 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 831. Score Summary: SZ Carrom won (Board 1: 2 - 1)', '152.58.31.45', '2026-10-09 08:21:06'),
(40, 3, 'FINAL_SCORE_SYNC', 'Match #762 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 873. Score Summary: SCZ Badminton won (11-15, 11-15)', '106.192.218.245', '2026-10-09 08:30:21'),
(41, 3, 'FINAL_SCORE_SYNC', 'Match #718 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 801. Score Summary: NCZ Badminton won (15-7, 16-6)', '106.192.218.245', '2026-10-09 08:32:10'),
(42, 1, 'CLEAR_MATCHES', 'Admin cleared all drafted matches for Game #780', '152.58.33.170', '2026-10-09 08:56:36'),
(43, 1, 'CREATE_MATCH', 'Manually created Match #774 (Pool A - Round 1) for Game #780', '152.58.33.170', '2026-10-09 08:57:15'),
(44, 1, 'CREATE_MATCH', 'Manually created Match #775 (Pool A - Round 1) for Game #780', '152.58.33.170', '2026-10-09 08:57:44'),
(45, 1, 'CREATE_MATCH', 'Manually created Match #776 (Pool A - Round 1) for Game #780', '152.58.33.170', '2026-10-09 08:58:07'),
(46, 1, 'CREATE_MATCH', 'Manually created Match #777 (Pool A - Round 1) for Game #780', '152.58.33.170', '2026-10-09 08:58:31'),
(47, 1, 'CREATE_MATCH', 'Manually created Match #778 (Pool A - Round 1) for Game #780', '152.58.33.170', '2026-10-09 08:58:53'),
(48, 1, 'CREATE_MATCH', 'Manually created Match #779 (Pool A - Round 1) for Game #780', '152.58.33.170', '2026-10-09 08:59:18'),
(49, 1, 'CREATE_MATCH', 'Manually created Match #780 (Pool A - Round 2) for Game #780', '152.58.33.170', '2026-10-09 08:59:43'),
(50, 1, 'CREATE_MATCH', 'Manually created Match #781 (Pool A - Round 2) for Game #780', '152.58.33.170', '2026-10-09 08:59:57'),
(51, 1, 'CREATE_MATCH', 'Manually created Match #782 (Pool A - Round 2) for Game #780', '152.58.33.170', '2026-10-09 09:00:17'),
(52, 1, 'CREATE_MATCH', 'Manually created Match #783 (Pool A - Round 2) for Game #780', '152.58.33.170', '2026-10-09 09:00:40'),
(53, 1, 'CREATE_MATCH', 'Manually created Match #784 (Pool A - Round 2) for Game #780', '152.58.33.170', '2026-10-09 09:01:01'),
(54, 1, 'CREATE_MATCH', 'Manually created Match #785 (Pool A - Round 2) for Game #780', '152.58.33.170', '2026-10-09 09:01:23'),
(55, 3, 'FINAL_SCORE_SYNC', 'Match #717 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 855. Score Summary: MR Badminton won (16-14, 15-12)', '106.192.217.245', '2026-10-09 09:02:21'),
(56, 3, 'FINAL_SCORE_SYNC', 'Match #719 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 837. Score Summary: WZ Badminton won (15-9, 15-7)', '106.192.217.245', '2026-10-09 09:06:23'),
(57, 3, 'FINAL_SCORE_SYNC', 'Match #733 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 810. Score Summary: MF Badminton won (15-11, 15-10)', '106.192.217.245', '2026-10-09 09:08:06'),
(58, 3, 'FINAL_SCORE_SYNC', 'Match #761 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 828. Score Summary: SZ Badminton won (8-15, 15-9, 15-6)', '106.192.222.245', '2026-10-09 09:25:31'),
(59, 3, 'LIVE_SCORE_UPDATE', 'Match #774 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 859. Score Summary: Board 1: 1 - 0 (MR Chess Won)', '106.216.251.31', '2026-10-09 09:32:58'),
(60, 3, 'LIVE_SCORE_UPDATE', 'Match #775 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 796. Score Summary: Board 1: 0 - 1 (VR Chess Won)', '106.216.251.31', '2026-10-09 09:33:39'),
(61, 3, 'LIVE_SCORE_UPDATE', 'Match #776 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 877. Score Summary: Board 1: 1 - 0 (SCZ Chess Won)', '106.216.251.31', '2026-10-09 09:34:01'),
(62, 3, 'LIVE_SCORE_UPDATE', 'Match #777 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 886. Score Summary: Board 1: 1 - 0 (EZ Chess Won)', '106.216.251.31', '2026-10-09 09:34:19'),
(63, 3, 'LIVE_SCORE_UPDATE', 'Match #778 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 841. Score Summary: Board 1: 0 - 1 (WZ Chess Won)', '106.216.251.31', '2026-10-09 09:34:43'),
(64, 3, 'LIVE_SCORE_UPDATE', 'Match #779 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 895. Score Summary: Board 1: 1 - 0 (HB Chess Won)', '106.216.251.31', '2026-10-09 09:36:00'),
(65, 3, 'LIVE_SCORE_UPDATE', 'Match #780 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: Pending. Score Summary: Board 1: ½ - ½ (Draw)', '106.221.209.173', '2026-10-09 09:38:24'),
(66, 3, 'LIVE_SCORE_UPDATE', 'Match #781 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 796. Score Summary: Board 1: 1 - 0 (VR Chess Won)', '106.221.209.173', '2026-10-09 09:38:35'),
(67, 3, 'LIVE_SCORE_UPDATE', 'Match #782 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 877. Score Summary: Board 1: 0 - 1 (SCZ Chess Won)', '106.221.209.173', '2026-10-09 09:38:50'),
(68, 3, 'LIVE_SCORE_UPDATE', 'Match #783 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 850. Score Summary: Board 1: 0 - 1 (NZ Chess Won)', '106.221.209.173', '2026-10-09 09:39:07'),
(69, 3, 'LIVE_SCORE_UPDATE', 'Match #784 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 823. Score Summary: Board 1: 1 - 0 (PH Chess Won)', '106.221.209.173', '2026-10-09 09:39:20'),
(70, 3, 'FINAL_SCORE_SYNC', 'Match #732 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 792. Score Summary: VR Badminton won (15-12, 6-15, 15-11)', '106.192.222.245', '2026-10-09 09:39:50'),
(71, 3, 'LIVE_SCORE_UPDATE', 'Match #785 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 814. Score Summary: Board 1: 1 - 0 (MF Chess Won)', '106.221.209.173', '2026-10-09 09:40:17'),
(72, 3, 'FINAL_SCORE_SYNC', 'Match #487 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 858. Score Summary: MR Carrom won (Board 1: 1 - 2)', '152.58.31.173', '2026-10-09 09:50:01'),
(73, 3, 'FINAL_SCORE_SYNC', 'Match #489 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 813. Score Summary: MF Carrom won (Board 1: 1 - 2)', '152.58.31.173', '2026-10-09 09:53:05'),
(74, 3, 'FINAL_SCORE_SYNC', 'Match #482 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 795. Score Summary: VR Carrom won (Board 1: 2 - 1)', '152.58.31.173', '2026-10-09 09:53:35'),
(75, 3, 'FINAL_SCORE_SYNC', 'Match #485 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 876. Score Summary: SCZ Carrom won (Board 1: 1 - 2)', '152.58.31.173', '2026-10-09 09:56:50'),
(76, 3, 'FINAL_SCORE_SYNC', 'Match #478 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 822. Score Summary: PH Carrom won (Board 1: 3 - 0)', '152.58.31.173', '2026-10-09 09:58:50'),
(77, 3, 'FINAL_SCORE_SYNC', 'Match #754 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 855. Score Summary: MR Badminton won (9-15, 11-15)', '106.192.213.245', '2026-10-09 10:21:20'),
(78, 3, 'FINAL_SCORE_SYNC', 'Match #749 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 819. Score Summary: PH Badminton won (15-5, 15-10)', '106.192.213.245', '2026-10-09 10:29:07'),
(79, 1, 'CREATE_MATCH', 'Manually created Match #786 (Pool A - Round 3) for Game #780', '152.58.30.205', '2026-10-09 10:43:06'),
(80, 1, 'CREATE_MATCH', 'Manually created Match #787 (Pool A - Round 3) for Game #780', '152.58.30.205', '2026-10-09 10:43:22'),
(81, 1, 'CREATE_MATCH', 'Manually created Match #788 (Pool A - Round 3) for Game #780', '152.58.30.205', '2026-10-09 10:43:37'),
(82, 1, 'CREATE_MATCH', 'Manually created Match #789 (Pool A - Round 3) for Game #780', '152.58.30.205', '2026-10-09 10:43:54'),
(83, 1, 'CREATE_MATCH', 'Manually created Match #790 (Pool A - Round 3) for Game #780', '152.58.30.205', '2026-10-09 10:44:09'),
(84, 1, 'CREATE_MATCH', 'Manually created Match #791 (Pool A - Round 3) for Game #780', '152.58.30.205', '2026-10-09 10:44:29'),
(85, 1, 'CREATE_MATCH', 'Manually created Match #792 (Pool A - Round 4) for Game #780', '152.58.30.205', '2026-10-09 10:45:00'),
(86, 1, 'CREATE_MATCH', 'Manually created Match #793 (Pool A - Round 4) for Game #780', '152.58.30.205', '2026-10-09 10:45:19'),
(87, 1, 'CREATE_MATCH', 'Manually created Match #794 (Pool A - Round 4) for Game #780', '152.58.30.205', '2026-10-09 10:45:36'),
(88, 1, 'CREATE_MATCH', 'Manually created Match #795 (Pool A - Round 4) for Game #780', '152.58.30.205', '2026-10-09 10:45:48'),
(89, 1, 'CREATE_MATCH', 'Manually created Match #796 (Pool A - Round 4) for Game #780', '152.58.30.205', '2026-10-09 10:46:02'),
(90, 1, 'CREATE_MATCH', 'Manually created Match #797 (Pool A - Round 4) for Game #780', '152.58.30.205', '2026-10-09 10:46:20'),
(91, 3, 'FINAL_SCORE_SYNC', 'Match #725 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 801. Score Summary: NCZ Badminton won (12-15, 7-15)', '106.192.220.245', '2026-10-09 10:50:21'),
(92, 3, 'FINAL_SCORE_SYNC', 'Match #734 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 873. Score Summary: SCZ Badminton won (15-17, 15-7, 15-8)', '106.192.222.245', '2026-10-09 11:02:30'),
(93, 3, 'FINAL_SCORE_SYNC', 'Match #758 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 873. Score Summary: SCZ Badminton won (15-5, 15-3)', '106.192.221.245', '2026-10-09 11:08:54'),
(94, 3, 'LIVE_SCORE_UPDATE', 'Match #593 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 811. Score Summary: 3-0, 3-1, 0-3, 3-0 (Live - Srv: MF)', '223.228.40.160', '2026-10-09 11:12:06'),
(95, 3, 'LIVE_SCORE_UPDATE', 'Match #595 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 820. Score Summary: 1-3, 3-0, 3-0, 3-1 (Live - Srv: PH)', '106.195.2.221', '2026-10-09 11:14:55'),
(96, 3, 'FINAL_SCORE_SYNC', 'Match #720 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 855. Score Summary: MR Badminton won (15-11, 15-10)', '106.192.221.245', '2026-10-09 11:18:53'),
(97, 3, 'LIVE_SCORE_UPDATE', 'Match #596 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 838. Score Summary: 3-0, 3-0, 3-0 (Live - Srv: WZ)', '106.195.2.221', '2026-10-09 11:21:25'),
(98, 3, 'LIVE_SCORE_UPDATE', 'Match #598 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 874. Score Summary: 0-2, 2-0, 1-2 (Live - Srv: SCZ)', '106.195.2.221', '2026-10-09 11:23:55'),
(99, 3, 'LIVE_SCORE_UPDATE', 'Match #597 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 865. Score Summary: 3-0, 0-3, 0-3, 0-3 (Live - Srv: EZ)', '106.195.2.221', '2026-10-09 11:26:40'),
(100, 3, 'LIVE_SCORE_UPDATE', 'Match #607 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 838. Score Summary: 2-0, 1-2, 2-1 (Live - Srv: WZ)', '106.195.2.221', '2026-10-09 11:30:27'),
(101, 3, 'LIVE_SCORE_UPDATE', 'Match #602 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 892. Score Summary: 0-3, 0-3, 3-1, 0-3 (Live - Srv: NCZ)', '106.195.2.221', '2026-10-09 11:32:53'),
(102, 3, 'LIVE_SCORE_UPDATE', 'Match #608 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 793. Score Summary: 3-1, 3-1, 1-3, 3-0 (Live - Srv: VR)', '106.195.2.221', '2026-10-09 11:34:46'),
(103, 3, 'LIVE_SCORE_UPDATE', 'Match #619 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 892. Score Summary: 2-0, 3-1 (Live - Srv: HB)', '106.195.2.221', '2026-10-09 11:37:17'),
(104, 3, 'FINAL_SCORE_SYNC', 'Match #735 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 792. Score Summary: VR Badminton won (15-4, 15-12)', '106.192.214.178', '2026-10-09 11:37:31'),
(105, 3, 'FINAL_SCORE_SYNC', 'Match #589 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 838. Score Summary: WZ Table Tennis won (3-2, 3-0, 3-1)', '106.192.213.178', '2026-10-09 11:40:01'),
(106, 3, 'LIVE_SCORE_UPDATE', 'Match #629 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 856. Score Summary: 0-3, 3-0, 0-3, 3-0, 3-1 (Live - Srv: MR)', '106.195.2.221', '2026-10-09 11:41:38'),
(107, 3, 'LIVE_SCORE_UPDATE', 'Match #641 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 820. Score Summary: 0-2, 2-0, 2-0 (Live - Srv: PH)', '106.195.2.221', '2026-10-09 11:44:03'),
(108, 3, 'LIVE_SCORE_UPDATE', 'Match #609 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 883. Score Summary: 3-0, 3-0, 3-0 (Live - Srv: EZ)', '106.195.2.221', '2026-10-09 11:45:33'),
(109, 3, 'LIVE_SCORE_UPDATE', 'Match #616 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 811. Score Summary: 3-0, 3-0, 3-0 (Live - Srv: MF)', '106.195.2.221', '2026-10-09 11:46:38'),
(110, 3, 'LIVE_SCORE_UPDATE', 'Match #632 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 892. Score Summary: 1-2, 2-0, 2-0 (Live - Srv: HB)', '106.195.2.221', '2026-10-09 11:47:35'),
(111, 3, 'FINAL_SCORE_SYNC', 'Match #648 updated via Volunteer Scoring PWA. Status: completed. Winner ID: Pending. Score Summary: 50m Freestyle (Men) (50m Freestyle (Men) - Heat 1 (8 Swimmers)): 1st PATHAK DIVYANG (00.29.46)', '106.192.223.46', '2026-10-09 11:49:09'),
(112, 3, 'LIVE_SCORE_UPDATE', 'Match #588 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 847. Score Summary: 3-0, 3-0, 3-0 (Live - Srv: NZ)', '106.195.2.221', '2026-10-09 11:49:34'),
(113, 3, 'LIVE_SCORE_UPDATE', 'Match #604 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 892. Score Summary: 2-1, 2-0 (Live - Srv: HB)', '106.195.2.221', '2026-10-09 11:50:59'),
(114, 3, 'LIVE_SCORE_UPDATE', 'Match #637 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 811. Score Summary: 0-2, 2-1, 0-2 (Live - Srv: HB)', '106.195.2.221', '2026-10-09 11:52:22'),
(115, 3, 'LIVE_SCORE_UPDATE', 'Match #639 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 820. Score Summary: 2-0, 2-0 (Live - Srv: PH)', '106.195.2.221', '2026-10-09 11:57:37'),
(116, 3, 'LIVE_SCORE_UPDATE', 'Match #625 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 811. Score Summary: 3-0, 3-0, 0-3, 3-0 (Live - Srv: MF)', '106.195.2.221', '2026-10-09 11:58:31'),
(117, 3, 'FINAL_SCORE_SYNC', 'Match #649 updated via Volunteer Scoring PWA. Status: completed. Winner ID: Pending. Score Summary: 50m Freestyle (Men) (50m Freestyle (Men) - Heat 2 (8 Swimmers)): 1st PUNIT DESWAL (00.40.54)', '106.192.223.46', '2026-10-09 11:59:38'),
(118, 3, 'LIVE_SCORE_UPDATE', 'Match #603 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 838. Score Summary: 2-0, 2-0 (Live - Srv: WZ)', '106.195.2.221', '2026-10-09 12:00:21'),
(119, 3, 'LIVE_SCORE_UPDATE', 'Match #623 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 820. Score Summary: 3-0, 3-0, 3-0 (Live - Srv: PH)', '106.195.2.221', '2026-10-09 12:02:35'),
(120, 3, 'LIVE_SCORE_UPDATE', 'Match #599 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 811. Score Summary: 2-1, 2-0 (Live - Srv: MF)', '106.195.2.221', '2026-10-09 12:04:00'),
(121, 3, 'FINAL_SCORE_SYNC', 'Match #650 updated via Volunteer Scoring PWA. Status: completed. Winner ID: Pending. Score Summary: 50m Freestyle (Women) (50m Freestyle (Women) - Direct Final): 1st DIANA ROBIN (01.09.86)', '106.192.223.46', '2026-10-09 12:04:29'),
(122, 3, 'FINAL_SCORE_SYNC', 'Match #653 updated via Volunteer Scoring PWA. Status: completed. Winner ID: Pending. Score Summary: 50m Breaststroke (Women) (50m Breaststroke (Women) - Direct Final): 1st RAWAT NISHA (00.56.32)', '106.192.223.46', '2026-10-09 12:07:30'),
(123, 3, 'LIVE_SCORE_UPDATE', 'Match #630 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 793. Score Summary: 3-0, 3-0, 3-0 (Live - Srv: VR)', '106.195.2.221', '2026-10-09 12:15:35'),
(124, 3, 'FINAL_SCORE_SYNC', 'Match #588 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 847. Score Summary: NZ Table Tennis won (3-0, 3-0, 3-0)', '106.195.2.221', '2026-10-09 12:18:09'),
(125, 3, 'FINAL_SCORE_SYNC', 'Match #590 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 892. Score Summary: HB Table Tennis won (2-3, 3-0, 0-3, 3-0, 3-1)', '106.195.2.221', '2026-10-09 12:18:21'),
(126, 3, 'FINAL_SCORE_SYNC', 'Match #591 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 892. Score Summary: HB Table Tennis won (1-3, 0-3, 1-3)', '106.195.2.221', '2026-10-09 12:18:28'),
(127, 3, 'FINAL_SCORE_SYNC', 'Match #593 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 811. Score Summary: MF Table Tennis won (3-0, 3-1, 0-3, 3-0)', '106.195.2.221', '2026-10-09 12:18:35'),
(128, 3, 'FINAL_SCORE_SYNC', 'Match #594 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 793. Score Summary: VR Table Tennis won (3-0, 3-0, 3-0)', '106.195.2.221', '2026-10-09 12:18:40'),
(129, 3, 'FINAL_SCORE_SYNC', 'Match #595 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 820. Score Summary: PH Table Tennis won (1-3, 3-0, 3-0, 3-1)', '106.195.2.221', '2026-10-09 12:18:48'),
(130, 3, 'FINAL_SCORE_SYNC', 'Match #596 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 838. Score Summary: WZ Table Tennis won (3-0, 3-0, 3-0)', '106.195.2.221', '2026-10-09 12:18:52'),
(131, 3, 'FINAL_SCORE_SYNC', 'Match #597 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 865. Score Summary: NWZ Table Tennis won (3-0, 0-3, 0-3, 0-3)', '106.195.2.221', '2026-10-09 12:18:58'),
(132, 3, 'FINAL_SCORE_SYNC', 'Match #598 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 874. Score Summary: SCZ Table Tennis won (0-2, 2-0, 1-2)', '106.195.2.221', '2026-10-09 12:19:03'),
(133, 3, 'FINAL_SCORE_SYNC', 'Match #602 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 892. Score Summary: HB Table Tennis won (0-3, 0-3, 3-1, 0-3)', '106.195.2.221', '2026-10-09 12:19:07'),
(134, 3, 'FINAL_SCORE_SYNC', 'Match #603 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 838. Score Summary: WZ Table Tennis won (2-0, 2-0)', '106.195.2.221', '2026-10-09 12:19:11'),
(135, 3, 'FINAL_SCORE_SYNC', 'Match #604 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 892. Score Summary: HB Table Tennis won (2-1, 2-0)', '106.195.2.221', '2026-10-09 12:19:15'),
(136, 3, 'FINAL_SCORE_SYNC', 'Match #607 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 838. Score Summary: WZ Table Tennis won (2-0, 1-2, 2-1)', '106.195.2.221', '2026-10-09 12:19:21'),
(137, 3, 'FINAL_SCORE_SYNC', 'Match #599 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 811. Score Summary: MF Table Tennis won (2-1, 2-0)', '106.195.2.221', '2026-10-09 12:19:27'),
(138, 3, 'FINAL_SCORE_SYNC', 'Match #608 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 793. Score Summary: VR Table Tennis won (3-1, 3-1, 1-3, 3-0)', '106.195.2.221', '2026-10-09 12:19:32'),
(139, 3, 'FINAL_SCORE_SYNC', 'Match #609 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 883. Score Summary: EZ Table Tennis won (3-0, 3-0, 3-0)', '106.195.2.221', '2026-10-09 12:19:45'),
(140, 3, 'FINAL_SCORE_SYNC', 'Match #616 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 811. Score Summary: MF Table Tennis won (3-0, 3-0, 3-0)', '106.195.2.221', '2026-10-09 12:19:48'),
(141, 3, 'FINAL_SCORE_SYNC', 'Match #619 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 892. Score Summary: HB Table Tennis won (2-0, 3-1)', '106.195.2.221', '2026-10-09 12:19:51'),
(142, 3, 'FINAL_SCORE_SYNC', 'Match #623 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 820. Score Summary: PH Table Tennis won (3-0, 3-0, 3-0)', '106.195.2.221', '2026-10-09 12:19:53'),
(143, 3, 'FINAL_SCORE_SYNC', 'Match #625 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 811. Score Summary: MF Table Tennis won (3-0, 3-0, 0-3, 3-0)', '106.195.2.221', '2026-10-09 12:19:56'),
(144, 3, 'FINAL_SCORE_SYNC', 'Match #629 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 856. Score Summary: MR Table Tennis won (0-3, 3-0, 0-3, 3-0, 3-1)', '106.195.2.221', '2026-10-09 12:19:59'),
(145, 3, 'FINAL_SCORE_SYNC', 'Match #630 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 793. Score Summary: VR Table Tennis won (3-0, 3-0, 3-0)', '106.195.2.221', '2026-10-09 12:20:01'),
(146, 3, 'FINAL_SCORE_SYNC', 'Match #631 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 820. Score Summary: PH Table Tennis won (2-0, 2-0)', '106.195.2.221', '2026-10-09 12:20:03'),
(147, 3, 'FINAL_SCORE_SYNC', 'Match #632 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 892. Score Summary: HB Table Tennis won (1-2, 2-0, 2-0)', '106.195.2.221', '2026-10-09 12:20:06'),
(148, 3, 'FINAL_SCORE_SYNC', 'Match #637 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 811. Score Summary: MF Table Tennis won (0-2, 2-1, 0-2)', '106.195.2.221', '2026-10-09 12:20:09'),
(149, 3, 'FINAL_SCORE_SYNC', 'Match #639 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 820. Score Summary: PH Table Tennis won (2-0, 2-0)', '106.195.2.221', '2026-10-09 12:20:12'),
(150, 3, 'FINAL_SCORE_SYNC', 'Match #641 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 820. Score Summary: PH Table Tennis won (0-2, 2-0, 2-0)', '106.195.2.221', '2026-10-09 12:20:15'),
(151, 3, 'FINAL_SCORE_SYNC', 'Match #721 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 882. Score Summary: EZ Badminton won (15-10, 15-10)', '106.192.213.178', '2026-10-09 12:20:27'),
(152, 3, 'FINAL_SCORE_SYNC', 'Match #750 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 846. Score Summary: NZ Badminton won (17-15, 10-15, 14-16)', '106.192.213.178', '2026-10-09 12:23:03'),
(153, 3, 'FINAL_SCORE_SYNC', 'Match #760 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 855. Score Summary: MR Badminton won (15-12, 15-13)', '106.192.223.178', '2026-10-09 12:33:42'),
(154, 3, 'LIVE_SCORE_UPDATE', 'Match #636 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 820. Score Summary: 2-0, 2-0 (Live - Srv: PH)', '106.195.4.229', '2026-10-09 12:43:54'),
(155, 3, 'FINAL_SCORE_SYNC', 'Match #636 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 820. Score Summary: PH Table Tennis won (2-0, 2-0)', '106.195.4.229', '2026-10-09 12:44:03'),
(156, 3, 'LIVE_SCORE_UPDATE', 'Match #459 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: Pending. Score Summary: 6-3 [Pts: 0-0*]', '223.228.34.201', '2026-10-09 12:44:22'),
(157, 3, 'FINAL_SCORE_SYNC', 'Match #601 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 874. Score Summary: SCZ Table Tennis won (3-0, 3-0, 3-0)', '106.195.4.229', '2026-10-09 12:48:19'),
(158, 3, 'FINAL_SCORE_SYNC', 'Match #722 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 837. Score Summary: WZ Badminton won (17-19, 15-11, 6-15)', '106.192.208.178', '2026-10-09 12:57:14'),
(159, 3, 'FINAL_SCORE_SYNC', 'Match #736 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 864. Score Summary: NWZ Badminton won (15-12, 15-13)', '106.192.208.178', '2026-10-09 12:59:35'),
(160, 3, 'FINAL_SCORE_SYNC', 'Match #612 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 820. Score Summary: PH Table Tennis won (3-0, 0-2, 3-0)', '106.195.4.229', '2026-10-09 13:26:55'),
(161, 3, 'FINAL_SCORE_SYNC', 'Match #737 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 810. Score Summary: MF Badminton won (17-15, 15-9)', '106.192.209.178', '2026-10-09 13:27:44'),
(162, 3, 'FINAL_SCORE_SYNC', 'Match #755 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 873. Score Summary: SCZ Badminton won (4-15, 7-15)', '106.192.209.178', '2026-10-09 13:29:55'),
(163, 3, 'FINAL_SCORE_SYNC', 'Match #723 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 855. Score Summary: MR Badminton won (15-7, 15-4)', '106.192.209.178', '2026-10-09 13:34:01'),
(164, 3, 'FINAL_SCORE_SYNC', 'Match #627 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 820. Score Summary: PH Table Tennis won (3-0, 3-0, 3-0)', '106.195.4.229', '2026-10-09 13:55:03'),
(165, 3, 'FINAL_SCORE_SYNC', 'Match #728 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 819. Score Summary: PH Badminton won (9-15, 15-0, 15-13)', '106.192.213.178', '2026-10-09 14:05:11'),
(166, 3, 'FINAL_SCORE_SYNC', 'Match #752 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 819. Score Summary: PH Badminton won (15-5, 15-9)', '106.192.215.178', '2026-10-09 14:10:18'),
(167, 3, 'FINAL_SCORE_SYNC', 'Match #743 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 864. Score Summary: NWZ Badminton won (4-15, 11-15)', '106.192.215.178', '2026-10-09 14:13:48'),
(168, 3, 'FINAL_SCORE_SYNC', 'Match #738 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 792. Score Summary: VR Badminton won (15-6, 15-12)', '106.192.216.178', '2026-10-09 14:21:33'),
(169, 3, 'FINAL_SCORE_SYNC', 'Match #727 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 801. Score Summary: NCZ Badminton won (15-10, 15-5)', '106.192.216.178', '2026-10-09 14:35:11'),
(170, 3, 'FINAL_SCORE_SYNC', 'Match #724 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 837. Score Summary: WZ Badminton won (8-15, 15-17)', '106.192.216.178', '2026-10-09 14:37:05'),
(171, 3, 'FINAL_SCORE_SYNC', 'Match #611 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 793. Score Summary: VR Table Tennis won (2-3, 3-0, 1-3, 3-2, 3-1)', '106.195.10.157', '2026-10-09 15:01:41'),
(172, 3, 'FINAL_SCORE_SYNC', 'Match #633 updated via Volunteer Scoring PWA. Status: completed. Winner ID: 856. Score Summary: MR Table Tennis won (0-3, 3-0, 3-1, 1-3, 2-3)', '106.195.10.157', '2026-10-09 15:08:54'),
(173, 3, 'LIVE_SCORE_UPDATE', 'Match #786 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 796. Score Summary: Board 1: 0 - 1 (VR Chess Won)', '27.59.111.14', '2026-10-09 17:54:43'),
(174, 3, 'LIVE_SCORE_UPDATE', 'Match #787 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: Pending. Score Summary: Board 1: ½ - ½ (Draw)', '27.59.111.14', '2026-10-09 17:55:13'),
(175, 3, 'LIVE_SCORE_UPDATE', 'Match #788 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 850. Score Summary: Board 1: 1 - 0 (NZ Chess Won)', '27.59.111.14', '2026-10-09 17:55:38'),
(176, 3, 'LIVE_SCORE_UPDATE', 'Match #789 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: Pending. Score Summary: Board 1: ½ - ½ (Draw)', '27.59.111.14', '2026-10-09 17:55:57'),
(177, 3, 'LIVE_SCORE_UPDATE', 'Match #790 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 832. Score Summary: Board 1: 1 - 0 (SZ Chess Won)', '27.59.111.14', '2026-10-09 17:56:12'),
(178, 3, 'LIVE_SCORE_UPDATE', 'Match #791 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 805. Score Summary: Board 1: 0 - 1 (NCZ Chess Won)', '27.59.111.14', '2026-10-09 17:56:32'),
(179, 3, 'LIVE_SCORE_UPDATE', 'Match #792 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 796. Score Summary: Board 1: 1 - 0 (VR Chess Won)', '27.59.111.14', '2026-10-09 17:57:15'),
(180, 3, 'LIVE_SCORE_UPDATE', 'Match #793 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 850. Score Summary: Board 1: 1 - 0 (NZ Chess Won)', '27.59.111.14', '2026-10-09 17:57:30'),
(181, 3, 'LIVE_SCORE_UPDATE', 'Match #794 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 895. Score Summary: Board 1: 0 - 1 (HB Chess Won)', '27.59.111.14', '2026-10-09 17:57:48'),
(182, 3, 'LIVE_SCORE_UPDATE', 'Match #795 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: Pending. Score Summary: Board 1: ½ - ½ (Draw)', '27.59.111.14', '2026-10-09 17:58:03'),
(183, 3, 'LIVE_SCORE_UPDATE', 'Match #796 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 886. Score Summary: Board 1: 0 - 1 (EZ Chess Won)', '27.59.111.14', '2026-10-09 17:58:18'),
(184, 3, 'LIVE_SCORE_UPDATE', 'Match #797 updated via Volunteer Scoring PWA. Status: in_progress. Winner ID: 814. Score Summary: Board 1: 1 - 0 (MF Chess Won)', '27.59.111.14', '2026-10-09 17:58:35');

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `location` varchar(150) NOT NULL,
  `court_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`id`, `name`, `type`, `location`, `court_number`) VALUES
(1, 'Badminton Court 1', 'court', 'Indoor Sports Complex', 'Court 1'),
(778, 'Table Tennis', 'table', 'Table Tennis', ''),
(779, 'Carrom', 'board', 'Carrom', ''),
(780, 'Chess', 'board', 'Chess', ''),
(781, 'Bridge', 'table', 'Bridge', ''),
(782, 'Lawn Tennis', 'table', 'Lawn Tennis', ''),
(783, 'Swimming', 'pool', 'Pool Deck', ''),
(784, 'Main Secretariat', 'lane', 'Main Secretariat', ''),
(785, 'Badminton Court 2', 'court', 'Indoor Sports Complex', 'Court 2'),
(786, 'Table Tennis Table 2', 'table', 'Indoor Sports Complex', 'Table 2'),
(789, 'Badminton Court 3', 'court', 'Indoor Sports Complex', 'Court 3'),
(790, 'Badminton Court 4', 'court', 'Indoor Sports Complex', 'Court 4');

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'Team Event',
  `format` varchar(50) NOT NULL,
  `score_format_id` int(11) DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'fas fa-trophy',
  `rules_summary` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `men_min` int(11) NOT NULL DEFAULT 0,
  `men_max` int(11) NOT NULL DEFAULT 0,
  `women_min` int(11) NOT NULL DEFAULT 0,
  `women_max` int(11) NOT NULL DEFAULT 0,
  `total_max` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`id`, `name`, `slug`, `category`, `format`, `score_format_id`, `icon`, `rules_summary`, `created_at`, `men_min`, `men_max`, `women_min`, `women_max`, `total_max`) VALUES
(1, 'Badminton', 'badminton', 'Team Event', 'pools_knockout', NULL, 'fas fa-feather-alt', 'Team Event: Best of 3 games to 21 points. Top 2 teams qualify for Semi-Finals.', '2026-09-24 21:09:31', 4, 5, 2, 3, 8),
(2, 'Table Tennis', 'table-tennis', 'Team Event', 'pools_knockout', 1, 'fas fa-table-tennis', 'Men\'s Team: Best of 5 games (First 2 Singles, 3rd Doubles, then Reverse Singles). Women\'s Team: Best of 3 games (First 2 Singles, 3rd Doubles if required). Top 2 teams from each pool qualify for Semi-Finals.', '2026-09-24 21:09:31', 4, 5, 2, 3, 8),
(778, 'Bridge', 'bridge', 'Team Event', 'pools_knockout', NULL, 'fas fa-clone', NULL, '2026-09-28 11:35:34', 4, 4, 0, 0, 4),
(779, 'Carrom', 'carrom', 'Team Event', 'pools_knockout', NULL, 'fas fa-bullseye', NULL, '2026-09-28 11:35:34', 4, 4, 0, 4, 8),
(780, 'Chess', 'chess', 'Team Event', 'swiss_league', NULL, 'fas fa-chess', NULL, '2026-09-28 11:35:34', 4, 4, 0, 0, 4),
(781, 'Swimming', 'swimming', 'Team Event', 'timed_heats', NULL, 'fas fa-swimmer', NULL, '2026-09-28 11:35:34', 2, 3, 2, 3, 6),
(782, 'Tennis', 'tennis', 'Team Event', 'pools_knockout', NULL, 'fas fa-baseball-ball', NULL, '2026-09-28 11:35:34', 2, 3, 1, 2, 5),
(783, 'Badminton - Open Category', 'badminton-open', 'Open Category', 'knockout', NULL, 'fas fa-feather-alt', NULL, '2026-09-28 11:35:34', 0, 1, 0, 1, 2),
(784, 'Table Tennis - Open Category', 'table-tennis-open', 'Open Category', 'knockout', NULL, 'fas fa-table-tennis', 'Men\'s Above 30 / Open Category: Best of 5 games. Knockout format.', '2026-09-28 11:35:34', 0, 1, 0, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `master_events`
--

CREATE TABLE `master_events` (
  `id` int(11) NOT NULL,
  `event_name` varchar(150) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(150) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_events`
--

INSERT INTO `master_events` (`id`, `event_name`, `event_type`, `date`, `start_time`, `end_time`, `location`, `description`) VALUES
(1, 'Team Managers Meeting (TMM)', 'meeting', '2026-03-01', '09:00:00', '10:00:00', 'Conference Hall A', 'Fixture draw confirmation & briefing'),
(778, 'Reporting & Warm-up', 'protocol', '2026-10-09', '09:00:00', '09:30:00', 'Pool Area', 'All Swimmers: Official reporting, lane verification, and warm-up session.'),
(779, 'Intermission / Buffer', 'buffer', '2026-10-09', '11:45:00', '12:15:00', 'Pool Area', 'Pool Open for Recovery: Mid-day break and warm-down ahead of Men\'s Finals.'),
(780, 'LUNCH BREAK', 'break', '2026-10-09', '13:00:00', '14:00:00', 'Dining Hall', 'All Participants: Tournament Lunch Break for athletes, coaches, and officials.'),
(781, 'Medal Ceremony & Presentation', 'ceremony', '2026-10-09', '14:00:00', '14:30:00', 'Podium', 'All Winners: Swimming Medal Ceremony & Presentation (Gold, Silver, Bronze).');

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `round` varchar(100) NOT NULL,
  `pool_name` varchar(10) DEFAULT NULL,
  `team1_id` int(11) DEFAULT NULL,
  `team2_id` int(11) DEFAULT NULL,
  `winner_id` int(11) DEFAULT NULL,
  `facility_id` int(11) DEFAULT NULL,
  `score_format_id` int(11) DEFAULT NULL,
  `match_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('scheduled','in_progress','completed') DEFAULT 'scheduled',
  `scores_json` text DEFAULT NULL,
  `volunteer_id` int(11) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 1,
  `lock_device_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`id`, `game_id`, `round`, `pool_name`, `team1_id`, `team2_id`, `winner_id`, `facility_id`, `score_format_id`, `match_date`, `start_time`, `end_time`, `status`, `scores_json`, `volunteer_id`, `is_published`, `lock_device_id`, `created_at`) VALUES
(459, 782, 'Group A - RR 1', 'A', 798, 834, NULL, 782, NULL, '2026-10-09', '10:00:00', '11:00:00', 'in_progress', '{\"type\":\"tennis\",\"sets\":[{\"t1\":\"6\",\"t2\":\"3\"}],\"s1_a\":\"6\",\"s1_b\":\"3\",\"s2_a\":\"\",\"s2_b\":\"\",\"s3_a\":\"\",\"s3_b\":\"\",\"pts_a\":\"0\",\"pts_b\":\"0\",\"live_points\":{\"t1\":\"0\",\"t2\":\"0\"},\"server\":\"t1\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"6-3 [Pts: 0-0*]\"}', NULL, 1, NULL, '2026-10-08 22:24:21'),
(460, 782, 'Group B - RR 1', 'B', 870, 852, NULL, 782, NULL, '2026-10-09', '10:00:00', '11:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:24:21'),
(461, 782, 'Group C - RR 1', 'C', 879, 816, NULL, 782, NULL, '2026-10-09', '10:00:00', '11:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:24:21'),
(462, 782, 'Group D - RR 1', 'D', 825, 807, NULL, 782, NULL, '2026-10-09', '10:00:00', '11:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:24:21'),
(463, 782, 'Group A - RR 2', 'A', 834, 861, NULL, 782, NULL, '2026-10-09', '11:00:00', '12:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:24:21'),
(464, 782, 'Group B - RR 2', 'B', 852, 843, NULL, 782, NULL, '2026-10-09', '11:00:00', '12:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:24:21'),
(465, 782, 'Group C - RR 2', 'C', 816, 897, NULL, 782, NULL, '2026-10-09', '11:00:00', '12:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:24:21'),
(466, 782, 'Group D - RR 2', 'D', 807, 888, NULL, 782, NULL, '2026-10-09', '11:00:00', '12:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:24:21'),
(467, 782, 'Group A - RR 3', 'A', 861, 798, NULL, 782, NULL, '2026-10-09', '12:00:00', '13:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:24:21'),
(468, 782, 'Group B - RR 3', 'B', 843, 870, NULL, 782, NULL, '2026-10-09', '12:00:00', '13:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:24:21'),
(469, 782, 'Group C - RR 3', 'C', 897, 879, NULL, 782, NULL, '2026-10-09', '12:00:00', '13:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:24:21'),
(470, 782, 'Group D - RR 3', 'D', 888, 825, NULL, 782, NULL, '2026-10-09', '12:00:00', '13:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:24:21'),
(471, 782, 'Quarter-Final 1 (Q1)', 'Knockout', 798, 807, NULL, 782, NULL, '2026-10-09', '14:00:00', '15:30:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:24:21'),
(472, 782, 'Quarter-Final 2 (Q2)', 'Knockout', 861, 825, NULL, 782, NULL, '2026-10-09', '14:00:00', '15:30:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:24:21'),
(473, 782, 'Quarter-Final 3 (Q3)', 'Knockout', 870, 816, NULL, 782, NULL, '2026-10-09', '15:30:00', '17:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:24:21'),
(474, 782, 'Quarter-Final 4 (Q4)', 'Knockout', 852, 879, NULL, 782, NULL, '2026-10-09', '15:30:00', '17:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:24:21'),
(475, 782, 'Semi-Final 1 (SF1)', 'Knockout', 798, 852, NULL, 782, NULL, '2026-10-10', '10:00:00', '11:30:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:24:21'),
(476, 782, 'Semi-Final 2 (SF2)', 'Knockout', 861, 870, NULL, 782, NULL, '2026-10-10', '10:00:00', '11:30:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:24:21'),
(477, 782, 'Championship Final', 'Knockout', 798, 861, NULL, 782, NULL, '2026-10-10', '11:30:00', '13:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:24:21'),
(478, 779, 'Men\'s Group A - RR 1', 'A', 822, 795, 822, 779, NULL, '2026-10-09', '10:00:00', '11:30:00', 'completed', '{\"type\":\"carrom\",\"board_no\":\"1\",\"points_a\":3,\"points_b\":0,\"status\":\"completed\",\"winner\":\"PH Carrom\",\"summary\":\"PH Carrom won (Board 1: 3 - 0)\"}', NULL, 1, NULL, '2026-10-08 22:28:02'),
(479, 779, 'Men\'s Group B - RR 1', 'B', 858, 804, NULL, 779, NULL, '2026-10-09', '10:00:00', '11:30:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:28:02'),
(480, 779, 'Men\'s Group C - RR 1', 'C', 840, 831, NULL, 779, NULL, '2026-10-09', '10:00:00', '11:30:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:28:02'),
(481, 779, 'Men\'s Group D - RR 1', 'D', 813, 894, 894, 779, NULL, '2026-10-09', '10:00:00', '11:30:00', 'completed', '{\"type\":\"carrom\",\"board_no\":\"1\",\"points_a\":1,\"points_b\":2,\"status\":\"completed\",\"winner\":\"HB Carrom\",\"summary\":\"HB Carrom won (Board 1: 1 - 2)\"}', NULL, 1, NULL, '2026-10-08 22:28:02'),
(482, 779, 'Men\'s Group A - RR 2', 'A', 795, 885, 795, 779, NULL, '2026-10-09', '11:30:00', '13:00:00', 'completed', '{\"type\":\"carrom\",\"board_no\":\"1\",\"points_a\":2,\"points_b\":1,\"status\":\"completed\",\"winner\":\"VR Carrom\",\"summary\":\"VR Carrom won (Board 1: 2 - 1)\"}', NULL, 1, NULL, '2026-10-08 22:28:02'),
(483, 779, 'Men\'s Group B - RR 2', 'B', 804, 849, 849, 779, NULL, '2026-10-09', '11:30:00', '13:00:00', 'completed', '{\"type\":\"carrom\",\"board_no\":\"1\",\"points_a\":0,\"points_b\":3,\"status\":\"completed\",\"winner\":\"NZ Carrom\",\"summary\":\"NZ Carrom won (Board 1: 0 - 3)\"}', NULL, 1, NULL, '2026-10-08 22:28:02'),
(484, 779, 'Men\'s Group C - RR 2', 'C', 831, 867, 831, 779, NULL, '2026-10-09', '11:30:00', '13:00:00', 'completed', '{\"type\":\"carrom\",\"board_no\":\"1\",\"points_a\":2,\"points_b\":1,\"status\":\"completed\",\"winner\":\"SZ Carrom\",\"summary\":\"SZ Carrom won (Board 1: 2 - 1)\"}', NULL, 1, NULL, '2026-10-08 22:28:02'),
(485, 779, 'Men\'s Group D - RR 2', 'D', 894, 876, 876, 779, NULL, '2026-10-09', '11:30:00', '13:00:00', 'completed', '{\"type\":\"carrom\",\"board_no\":\"1\",\"points_a\":1,\"points_b\":2,\"status\":\"completed\",\"winner\":\"SCZ Carrom\",\"summary\":\"SCZ Carrom won (Board 1: 1 - 2)\"}', NULL, 1, NULL, '2026-10-08 22:28:02'),
(486, 779, 'Men\'s Group A - RR 3', 'A', 885, 822, 822, 779, NULL, '2026-10-09', '14:00:00', '15:30:00', 'completed', '{\"type\":\"carrom\",\"board_no\":\"1\",\"points_a\":0,\"points_b\":3,\"status\":\"completed\",\"winner\":\"PH Carrom\",\"summary\":\"PH Carrom won (Board 1: 0 - 3)\"}', NULL, 1, NULL, '2026-10-08 22:28:02'),
(487, 779, 'Men\'s Group B - RR 3', 'B', 849, 858, 858, 779, NULL, '2026-10-09', '14:00:00', '15:30:00', 'completed', '{\"type\":\"carrom\",\"board_no\":\"1\",\"points_a\":1,\"points_b\":2,\"status\":\"completed\",\"winner\":\"MR Carrom\",\"summary\":\"MR Carrom won (Board 1: 1 - 2)\"}', NULL, 1, NULL, '2026-10-08 22:28:02'),
(488, 779, 'Men\'s Group C - RR 3', 'C', 867, 840, 840, 779, NULL, '2026-10-09', '14:00:00', '15:30:00', 'completed', '{\"type\":\"carrom\",\"board_no\":\"1\",\"points_a\":1,\"points_b\":2,\"status\":\"completed\",\"winner\":\"WZ Carrom\",\"summary\":\"WZ Carrom won (Board 1: 1 - 2)\"}', NULL, 1, NULL, '2026-10-08 22:28:02'),
(489, 779, 'Men\'s Group D - RR 3', 'D', 876, 813, 813, 779, NULL, '2026-10-09', '14:00:00', '15:30:00', 'completed', '{\"type\":\"carrom\",\"board_no\":\"1\",\"points_a\":1,\"points_b\":2,\"status\":\"completed\",\"winner\":\"MF Carrom\",\"summary\":\"MF Carrom won (Board 1: 1 - 2)\"}', NULL, 1, NULL, '2026-10-08 22:28:02'),
(490, 779, 'Men\'s QF 1', 'Knockout', 822, 894, NULL, 779, NULL, '2026-10-09', '15:30:00', '17:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:28:02'),
(491, 779, 'Men\'s QF 2', 'Knockout', 795, 813, NULL, 779, NULL, '2026-10-09', '15:30:00', '17:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:28:02'),
(492, 779, 'Men\'s QF 3', 'Knockout', 858, 831, NULL, 779, NULL, '2026-10-09', '15:30:00', '17:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:28:02'),
(493, 779, 'Men\'s QF 4', 'Knockout', 804, 840, NULL, 779, NULL, '2026-10-09', '15:30:00', '17:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:28:02'),
(494, 779, 'Men\'s SF 1', 'Knockout', 822, 840, NULL, 779, NULL, '2026-10-10', '10:00:00', '11:30:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:28:02'),
(495, 779, 'Men\'s SF 2', 'Knockout', 813, 858, NULL, 779, NULL, '2026-10-10', '10:00:00', '11:30:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:28:02'),
(496, 779, 'Men\'s Final', 'Knockout', 822, 813, NULL, 779, NULL, '2026-10-10', '11:30:00', '13:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:28:02'),
(588, 2, 'Men\'s Gr A (5 v 6)', 'A', 847, 829, 847, 778, 1, '2026-10-10', '10:00:00', '11:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":3,\"g1_b\":null,\"g2_a\":3,\"g2_b\":null,\"g3_a\":3,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"NZ Table Tennis\",\"summary\":\"NZ Table Tennis won (3-0, 3-0, 3-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(589, 2, 'Men\'s Gr A (3 v 5)', 'A', 838, 847, 838, 778, 1, '2026-10-10', '10:00:00', '11:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":3,\"g1_b\":2,\"g2_a\":3,\"g2_b\":0,\"g3_a\":3,\"g3_b\":1,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"WZ Table Tennis\",\"summary\":\"WZ Table Tennis won (3-2, 3-0, 3-1)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(590, 2, 'Men\'s Gr B (5 v 6)', 'B', 892, 865, 892, 778, 1, '2026-10-10', '10:00:00', '11:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":2,\"g1_b\":3,\"g2_a\":3,\"g2_b\":0,\"g3_a\":0,\"g3_b\":3,\"g4_a\":3,\"g4_b\":0,\"g5_a\":3,\"g5_b\":1,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"HB Table Tennis\",\"summary\":\"HB Table Tennis won (2-3, 3-0, 0-3, 3-0, 3-1)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(591, 2, 'Men\'s Gr B (3 v 5)', 'B', 883, 892, 892, 778, 1, '2026-10-10', '10:00:00', '11:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":1,\"g1_b\":3,\"g2_a\":0,\"g2_b\":3,\"g3_a\":1,\"g3_b\":3,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"HB Table Tennis\",\"summary\":\"HB Table Tennis won (1-3, 0-3, 1-3)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(592, 2, 'Men\'s Gr A (2 v 4)', 'A', 856, 874, 856, 778, 1, '2026-10-10', '11:30:00', '12:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":3,\"g1_b\":0,\"g2_a\":0,\"g2_b\":3,\"g3_a\":0,\"g3_b\":3,\"g4_a\":3,\"g4_b\":0,\"g5_a\":3,\"g5_b\":0,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"MR Table Tennis\",\"summary\":\"MR Table Tennis won (3-0, 0-3, 0-3, 3-0, 3-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(593, 2, 'Men\'s Gr A (1 v 5)', 'A', 811, 847, 811, 778, 1, '2026-10-10', '11:30:00', '12:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":3,\"g1_b\":0,\"g2_a\":3,\"g2_b\":1,\"g3_a\":0,\"g3_b\":3,\"g4_a\":3,\"g4_b\":0,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"MF Table Tennis\",\"summary\":\"MF Table Tennis won (3-0, 3-1, 0-3, 3-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(594, 2, 'Men\'s Gr B (2 v 4)', 'B', 793, 802, 793, 778, 1, '2026-10-10', '11:30:00', '12:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":3,\"g1_b\":0,\"g2_a\":3,\"g2_b\":0,\"g3_a\":3,\"g3_b\":0,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"VR Table Tennis\",\"summary\":\"VR Table Tennis won (3-0, 3-0, 3-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(595, 2, 'Men\'s Gr B (1 v 5)', 'B', 820, 892, 820, 778, 1, '2026-10-10', '11:30:00', '12:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":1,\"g1_b\":3,\"g2_a\":3,\"g2_b\":0,\"g3_a\":3,\"g3_b\":0,\"g4_a\":3,\"g4_b\":1,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"PH Table Tennis\",\"summary\":\"PH Table Tennis won (1-3, 3-0, 3-0, 3-1)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(596, 2, 'Men\'s Gr A (3 v 6)', 'A', 838, 829, 838, 778, 1, '2026-10-10', '12:30:00', '13:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":3,\"g1_b\":0,\"g2_a\":3,\"g2_b\":0,\"g3_a\":3,\"g3_b\":0,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"WZ Table Tennis\",\"summary\":\"WZ Table Tennis won (3-0, 3-0, 3-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(597, 2, 'Men\'s Gr B (3 v 6)', 'B', 883, 865, 865, 778, 1, '2026-10-10', '12:30:00', '13:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":3,\"g1_b\":0,\"g2_a\":0,\"g2_b\":3,\"g3_a\":0,\"g3_b\":3,\"g4_a\":0,\"g4_b\":3,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"NWZ Table Tennis\",\"summary\":\"NWZ Table Tennis won (3-0, 0-3, 0-3, 0-3)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(598, 2, 'Women\'s League (5 v 6)', 'Women', 793, 874, 874, 778, 2, '2026-10-10', '12:30:00', '13:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":0,\"g1_b\":2,\"g2_a\":2,\"g2_b\":0,\"g3_a\":1,\"g3_b\":2,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team2\",\"status\":\"completed\",\"winner_name\":\"SCZ Table Tennis\",\"summary\":\"SCZ Table Tennis won (0-2, 2-0, 1-2)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(599, 2, 'Women\'s League (3 v 4)', 'Women', 811, 838, 811, 778, 2, '2026-10-10', '12:30:00', '13:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":2,\"g1_b\":1,\"g2_a\":2,\"g2_b\":null,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"MF Table Tennis\",\"summary\":\"MF Table Tennis won (2-1, 2-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(600, 2, 'Men\'s Above 30 - Match 1', 'Open', 811, 820, NULL, 778, 1, '2026-10-10', '12:30:00', '13:30:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(601, 2, 'Men\'s Gr A (4 v 5)', 'A', 874, 847, 874, 778, 1, '2026-10-10', '13:30:00', '14:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":3,\"g1_b\":null,\"g2_a\":3,\"g2_b\":null,\"g3_a\":3,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"SCZ Table Tennis\",\"summary\":\"SCZ Table Tennis won (3-0, 3-0, 3-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(602, 2, 'Men\'s Gr B (4 v 5)', 'B', 802, 892, 892, 778, 1, '2026-10-10', '13:30:00', '14:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":0,\"g1_b\":3,\"g2_a\":0,\"g2_b\":3,\"g3_a\":3,\"g3_b\":1,\"g4_a\":0,\"g4_b\":3,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"HB Table Tennis\",\"summary\":\"HB Table Tennis won (0-3, 0-3, 3-1, 0-3)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(603, 2, 'Women\'s League (4 v 5)', 'Women', 838, 793, 838, 778, 2, '2026-10-10', '13:30:00', '14:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":2,\"g1_b\":null,\"g2_a\":2,\"g2_b\":null,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"WZ Table Tennis\",\"summary\":\"WZ Table Tennis won (2-0, 2-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(604, 2, 'Women\'s League (2 v 6)', 'Women', 892, 874, 892, 778, 2, '2026-10-10', '13:30:00', '14:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":2,\"g1_b\":1,\"g2_a\":2,\"g2_b\":0,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"HB Table Tennis\",\"summary\":\"HB Table Tennis won (2-1, 2-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(605, 2, 'Men\'s Above 30 - Match 2', 'Open', 856, 793, NULL, 778, 1, '2026-10-10', '13:30:00', '14:30:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(606, 2, 'Men\'s Gr A (2 v 6)', 'A', 856, 829, NULL, 778, 1, '2026-10-10', '14:30:00', '15:30:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(607, 2, 'Men\'s Gr A (3 v 4)', 'A', 838, 874, 838, 778, 1, '2026-10-10', '14:30:00', '15:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":2,\"g1_b\":0,\"g2_a\":1,\"g2_b\":2,\"g3_a\":2,\"g3_b\":1,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"WZ Table Tennis\",\"summary\":\"WZ Table Tennis won (2-0, 1-2, 2-1)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(608, 2, 'Men\'s Gr B (2 v 6)', 'B', 793, 865, 793, 778, 1, '2026-10-10', '14:30:00', '15:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":3,\"g1_b\":1,\"g2_a\":3,\"g2_b\":1,\"g3_a\":1,\"g3_b\":3,\"g4_a\":3,\"g4_b\":0,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"VR Table Tennis\",\"summary\":\"VR Table Tennis won (3-1, 3-1, 1-3, 3-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(609, 2, 'Men\'s Gr B (3 v 4)', 'B', 883, 802, 883, 778, 1, '2026-10-10', '14:30:00', '15:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":3,\"g1_b\":0,\"g2_a\":3,\"g2_b\":0,\"g3_a\":3,\"g3_b\":0,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"EZ Table Tennis\",\"summary\":\"EZ Table Tennis won (3-0, 3-0, 3-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(610, 2, 'Men\'s Gr A (2 v 5)', 'A', 856, 847, NULL, 778, 1, '2026-10-10', '15:30:00', '16:30:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(611, 2, 'Men\'s Gr B (2 v 5)', 'B', 793, 892, 793, 778, 1, '2026-10-10', '15:30:00', '16:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":2,\"g1_b\":3,\"g2_a\":3,\"g2_b\":null,\"g3_a\":1,\"g3_b\":3,\"g4_a\":3,\"g4_b\":2,\"g5_a\":3,\"g5_b\":1,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"VR Table Tennis\",\"summary\":\"VR Table Tennis won (2-3, 3-0, 1-3, 3-2, 3-1)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(612, 2, 'Women\'s League (1 v 6)', 'Women', 820, 874, 820, 778, 2, '2026-10-10', '15:30:00', '16:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":3,\"g1_b\":null,\"g2_a\":null,\"g2_b\":2,\"g3_a\":3,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"PH Table Tennis\",\"summary\":\"PH Table Tennis won (3-0, 0-2, 3-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(613, 2, 'Women\'s League (3 v 5)', 'Women', 811, 793, NULL, 778, 2, '2026-10-10', '15:30:00', '16:30:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(614, 2, 'Men\'s Above 30 - Match 3', 'Open', 838, 883, NULL, 778, 1, '2026-10-10', '15:30:00', '16:30:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(615, 2, 'Men\'s Above 30 - Match 4', 'Open', 874, 802, NULL, 778, 1, '2026-10-10', '16:30:00', '17:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(616, 2, 'Men\'s Gr A (1 v 6)', 'A', 811, 829, 811, 778, 1, '2026-10-10', '17:00:00', '18:00:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":3,\"g1_b\":null,\"g2_a\":3,\"g2_b\":null,\"g3_a\":3,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"MF Table Tennis\",\"summary\":\"MF Table Tennis won (3-0, 3-0, 3-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(617, 2, 'Men\'s Gr B (1 v 6)', 'B', 820, 865, NULL, 778, 1, '2026-10-10', '17:00:00', '18:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(618, 2, 'Women\'s League (3 v 6)', 'Women', 811, 874, NULL, 778, 2, '2026-10-10', '17:00:00', '18:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(619, 2, 'Women\'s League (2 v 5)', 'Women', 892, 793, 892, 778, 2, '2026-10-10', '17:00:00', '18:00:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":2,\"g1_b\":0,\"g2_a\":3,\"g2_b\":1,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"HB Table Tennis\",\"summary\":\"HB Table Tennis won (2-0, 3-1)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(620, 2, 'Men\'s Gr A (4 v 6)', 'A', 874, 829, NULL, 778, 1, '2026-10-10', '18:00:00', '19:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(621, 2, 'Men\'s Gr A (1 v 3)', 'A', 811, 838, NULL, 778, 1, '2026-10-10', '18:00:00', '19:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(622, 2, 'Men\'s Gr B (4 v 5)', 'B', 802, 892, NULL, 778, 1, '2026-10-10', '18:00:00', '19:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(623, 2, 'Men\'s Gr B (1 v 3)', 'B', 820, 883, 820, 778, 1, '2026-10-10', '18:00:00', '19:00:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":3,\"g1_b\":null,\"g2_a\":3,\"g2_b\":null,\"g3_a\":3,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"PH Table Tennis\",\"summary\":\"PH Table Tennis won (3-0, 3-0, 3-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(624, 2, 'Men\'s Gr A (3 v 6)', 'A', 838, 829, NULL, 778, 1, '2026-10-10', '19:00:00', '20:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(625, 2, 'Men\'s Gr A (1 v 4)', 'A', 811, 874, 811, 778, 1, '2026-10-10', '19:00:00', '20:00:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":3,\"g1_b\":null,\"g2_a\":3,\"g2_b\":null,\"g3_a\":null,\"g3_b\":3,\"g4_a\":3,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"MF Table Tennis\",\"summary\":\"MF Table Tennis won (3-0, 3-0, 0-3, 3-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(626, 2, 'Men\'s Gr B (3 v 6)', 'B', 883, 865, NULL, 778, 1, '2026-10-10', '19:00:00', '20:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(627, 2, 'Men\'s Gr B (1 v 4)', 'B', 820, 802, 820, 778, 1, '2026-10-10', '19:00:00', '20:00:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":3,\"g1_b\":null,\"g2_a\":3,\"g2_b\":null,\"g3_a\":3,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"PH Table Tennis\",\"summary\":\"PH Table Tennis won (3-0, 3-0, 3-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(628, 2, 'Men\'s Above 30 - Match 5', 'Open', 847, 892, NULL, 778, 1, '2026-10-10', '20:00:00', '21:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(629, 2, 'Men\'s Gr A (2 v 3)', 'A', 856, 838, 856, 778, 1, '2026-10-11', '09:30:00', '10:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":0,\"g1_b\":3,\"g2_a\":3,\"g2_b\":0,\"g3_a\":0,\"g3_b\":3,\"g4_a\":3,\"g4_b\":0,\"g5_a\":3,\"g5_b\":1,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"MR Table Tennis\",\"summary\":\"MR Table Tennis won (0-3, 3-0, 0-3, 3-0, 3-1)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(630, 2, 'Men\'s Gr B (2 v 3)', 'B', 793, 883, 793, 778, 1, '2026-10-11', '09:30:00', '10:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":3,\"g1_b\":null,\"g2_a\":3,\"g2_b\":null,\"g3_a\":3,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"VR Table Tennis\",\"summary\":\"VR Table Tennis won (3-0, 3-0, 3-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(631, 2, 'Women\'s League (1 v 5)', 'Women', 820, 793, 820, 778, 2, '2026-10-11', '09:30:00', '10:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":2,\"g1_b\":0,\"g2_a\":2,\"g2_b\":0,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"PH Table Tennis\",\"summary\":\"PH Table Tennis won (2-0, 2-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(632, 2, 'Women\'s League (2 v 4)', 'Women', 892, 838, 892, 778, 2, '2026-10-11', '09:30:00', '10:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":1,\"g1_b\":2,\"g2_a\":2,\"g2_b\":null,\"g3_a\":2,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"HB Table Tennis\",\"summary\":\"HB Table Tennis won (1-2, 2-0, 2-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(633, 2, 'Men\'s Above 30 - Semifinal 1', 'Open', 811, 856, 856, 778, 1, '2026-10-11', '09:30:00', '10:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":1,\"g1_a\":null,\"g1_b\":3,\"g2_a\":3,\"g2_b\":null,\"g3_a\":3,\"g3_b\":1,\"g4_a\":1,\"g4_b\":3,\"g5_a\":2,\"g5_b\":3,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"MR Table Tennis\",\"summary\":\"MR Table Tennis won (0-3, 3-0, 3-1, 1-3, 2-3)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(634, 2, 'Men\'s Gr A (1 v 2)', 'A', 811, 856, NULL, 778, 1, '2026-10-11', '10:30:00', '11:30:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(635, 2, 'Men\'s Gr B (1 v 2)', 'B', 820, 793, NULL, 778, 1, '2026-10-11', '10:30:00', '11:30:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(636, 2, 'Women\'s League (1 v 4)', 'Women', 820, 838, 820, 778, 2, '2026-10-11', '10:30:00', '11:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":2,\"g1_b\":null,\"g2_a\":2,\"g2_b\":null,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"PH Table Tennis\",\"summary\":\"PH Table Tennis won (2-0, 2-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(637, 2, 'Women\'s League (2 v 3)', 'Women', 892, 811, 811, 778, 2, '2026-10-11', '10:30:00', '11:30:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":null,\"g1_b\":2,\"g2_a\":2,\"g2_b\":1,\"g3_a\":null,\"g3_b\":2,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"MF Table Tennis\",\"summary\":\"MF Table Tennis won (0-2, 2-1, 0-2)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(638, 2, 'Men\'s Above 30 - Semifinal 2', 'Open', 820, 793, NULL, 778, 1, '2026-10-11', '10:30:00', '11:30:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(639, 2, 'Women\'s League (1 v 3)', 'Women', 820, 811, 820, 778, 2, '2026-10-11', '11:30:00', '13:00:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":2,\"g1_b\":null,\"g2_a\":2,\"g2_b\":null,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"PH Table Tennis\",\"summary\":\"PH Table Tennis won (2-0, 2-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(640, 2, 'Men\'s Above 30 - Final', 'Open', 811, 820, NULL, 778, 1, '2026-10-11', '11:30:00', '13:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(641, 2, 'Women\'s League (1 v 2)', 'Women', 820, 892, 820, 778, 2, '2026-10-11', '13:00:00', '15:00:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":0,\"g1_b\":2,\"g2_a\":2,\"g2_b\":0,\"g3_a\":2,\"g3_b\":0,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"PH Table Tennis\",\"summary\":\"PH Table Tennis won (0-2, 2-0, 2-0)\"}', NULL, 1, NULL, '2026-10-08 22:45:33'),
(642, 2, 'Men\'s Semi-Final 1', 'Knockout', 811, 793, NULL, 778, 1, '2026-10-11', '13:00:00', '15:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(643, 2, 'Men\'s Semi-Final 2', 'Knockout', 820, 856, NULL, 778, 1, '2026-10-11', '13:00:00', '15:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(644, 2, 'Men\'s Championship Final', 'Knockout', 811, 820, NULL, 778, 1, '2026-10-11', '15:00:00', '17:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-08 22:45:33'),
(648, 781, '50m Freestyle (Men) - Heat 1 (8 Swimmers)', 'Heats', NULL, NULL, NULL, 783, NULL, '2026-10-09', '09:30:00', '09:50:00', 'completed', '{\"type\":\"swimming\",\"event_name\":\"50m Freestyle (Men)\",\"category\":\"Team Event\",\"heat\":\"50m Freestyle (Men) - Heat 1 (8 Swimmers)\",\"heat_no\":\"50m Freestyle (Men) - Heat 1 (8 Swimmers)\",\"lanes\":{\"1\":{\"lane\":1,\"swimmer\":\"PATHAK DIVYANG\",\"zone\":\"MR\",\"time\":\"00.29.46\",\"pos\":\"1st\",\"status\":\"normal\"},\"2\":{\"lane\":2,\"swimmer\":\"HEMANTH KUMAR\",\"zone\":\"VR\",\"time\":\"00.33.34\",\"pos\":\"2nd\",\"status\":\"normal\"},\"3\":{\"lane\":3,\"swimmer\":\"S KHARE PRASANNA\",\"zone\":\"VR\",\"time\":\"00.39.26\",\"pos\":\"3rd\",\"status\":\"normal\"},\"4\":{\"lane\":4,\"swimmer\":\"ADSULE VISHWAS APPASAHEB\",\"zone\":\"MR\",\"time\":\"00.46.28\",\"pos\":\"4th\",\"status\":\"normal\"},\"5\":{\"lane\":5,\"swimmer\":\"KAUSHIK\",\"zone\":\"EZ\",\"time\":\"00.46.63\",\"pos\":\"5th\",\"status\":\"normal\"},\"6\":{\"lane\":6,\"swimmer\":\"PRIYABRATA PRADHAN\",\"zone\":\"PH\",\"time\":\"00.48.09\",\"pos\":\"6th\",\"status\":\"normal\"},\"7\":{\"lane\":7,\"swimmer\":\"ROUT NIHAR PRADHAN\",\"zone\":\"EZ\",\"time\":\"00.52.21\",\"pos\":\"7th\",\"status\":\"normal\"},\"8\":{\"lane\":8,\"swimmer\":\"SHUBHAM\",\"zone\":\"NC\",\"time\":\"00.55.46\",\"pos\":\"8th\",\"status\":\"normal\"}},\"status\":\"completed\",\"winner_name\":\"PATHAK DIVYANG\",\"summary\":\"50m Freestyle (Men) (50m Freestyle (Men) - Heat 1 (8 Swimmers)): 1st PATHAK DIVYANG (00.29.46)\"}', NULL, 1, NULL, '2026-10-09 04:20:18'),
(649, 781, '50m Freestyle (Men) - Heat 2 (8 Swimmers)', 'Heats', NULL, NULL, NULL, 783, NULL, '2026-10-09', '09:50:00', '10:10:00', 'completed', '{\"type\":\"swimming\",\"event_name\":\"50m Freestyle (Men)\",\"category\":\"Team Event\",\"heat\":\"50m Freestyle (Men) - Heat 2 (8 Swimmers)\",\"heat_no\":\"50m Freestyle (Men) - Heat 2 (8 Swimmers)\",\"lanes\":{\"1\":{\"lane\":1,\"swimmer\":\"PUNIT DESWAL\",\"zone\":\"VR\",\"time\":\"00.40.54\",\"pos\":\"1st\",\"status\":\"normal\"},\"2\":{\"lane\":2,\"swimmer\":\"THAKUR DEEPAK VASANT\",\"zone\":\"MR\",\"time\":\"00.45.69\",\"pos\":\"2nd\",\"status\":\"normal\"},\"3\":{\"lane\":3,\"swimmer\":\"DINESH KUMAR\",\"zone\":\"PH\",\"time\":\"00.49.97\",\"pos\":\"3rd\",\"status\":\"normal\"},\"4\":{\"lane\":4,\"swimmer\":\"TASHI\",\"zone\":\"EZ\",\"time\":\"00.51.97\",\"pos\":\"4th\",\"status\":\"normal\"},\"5\":{\"lane\":5,\"swimmer\":\"SURAJ CHOTULAL SHAHU\",\"zone\":\"MP\",\"time\":\"00.52.38\",\"pos\":\"5th\",\"status\":\"normal\"},\"6\":{\"lane\":6,\"swimmer\":\"BATNA RAJESH\",\"zone\":\"SZ\",\"time\":\"00.56.19\",\"pos\":\"6th\",\"status\":\"normal\"},\"7\":{\"lane\":7,\"swimmer\":\"MAHENDRA\",\"zone\":\"NW\",\"time\":\"00.59.75\",\"pos\":\"7th\",\"status\":\"normal\"},\"8\":{\"lane\":8,\"swimmer\":\"GIRIRAJ PATEL\",\"zone\":\"HB\",\"time\":\"01.01.72\",\"pos\":\"8th\",\"status\":\"normal\"}},\"status\":\"completed\",\"winner_name\":\"PUNIT DESWAL\",\"summary\":\"50m Freestyle (Men) (50m Freestyle (Men) - Heat 2 (8 Swimmers)): 1st PUNIT DESWAL (00.40.54)\"}', NULL, 1, NULL, '2026-10-09 04:20:18'),
(650, 781, '50m Freestyle (Women) - Direct Final', 'Final', NULL, NULL, NULL, 783, NULL, '2026-10-09', '10:15:00', '10:35:00', 'completed', '{\"type\":\"swimming\",\"event_name\":\"50m Freestyle (Women)\",\"category\":\"Team Event\",\"heat\":\"50m Freestyle (Women) - Direct Final\",\"heat_no\":\"50m Freestyle (Women) - Direct Final\",\"lanes\":{\"1\":{\"lane\":1,\"swimmer\":\"DIANA ROBIN\",\"zone\":\"PH\",\"time\":\"01.09.86\",\"pos\":\"1st\",\"status\":\"normal\"},\"2\":{\"lane\":2,\"swimmer\":\"AISHWARYA DEVANAND LAKHE\",\"zone\":\"MR\",\"time\":\"01.09.86\",\"pos\":\"2nd\",\"status\":\"normal\"},\"3\":{\"lane\":3,\"swimmer\":\"RITU KUMARI\",\"zone\":\"PH\",\"time\":\"01.25.57\",\"pos\":\"3rd\",\"status\":\"normal\"},\"4\":{\"lane\":4,\"swimmer\":\"PRIYANKA\",\"zone\":\"SC\",\"time\":\"01.55.00\",\"pos\":\"4th\",\"status\":\"normal\"}},\"status\":\"completed\",\"winner_name\":\"DIANA ROBIN\",\"summary\":\"50m Freestyle (Women) (50m Freestyle (Women) - Direct Final): 1st DIANA ROBIN (01.09.86)\"}', NULL, 1, NULL, '2026-10-09 04:20:18'),
(651, 781, '50m Breaststroke (Men) - Heat 1 (5 Swimmers)', 'Heats', NULL, NULL, NULL, 783, NULL, '2026-10-09', '10:45:00', '11:05:00', 'scheduled', '{\"type\":\"swimming\",\"event_name\":\"50m Breaststroke (Men)\",\"heat_no\":\"50m Breaststroke (Men) - Heat 1 (5 Swimmers)\",\"stage\":\"Heats\",\"participants\":\"Swimmers #1 to #5\",\"summary\":\"Scheduled: Swimmers #1 to #5 \\u2022 Heats\",\"lanes\":[{\"lane\":1,\"swimmer\":\"BHOWMICK KOUSHIK\",\"zone\":\"EZ\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":2,\"swimmer\":\"BHUTIA TASHI WANGYAL\",\"zone\":\"EZ\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":3,\"swimmer\":\"ROUT NIHAR RANJAN\",\"zone\":\"EZ\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":4,\"swimmer\":\"PATEL GIRIRAJ KISHORE\",\"zone\":\"HB\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":5,\"swimmer\":\"PRABHAKARARAO KOPPISETTY\",\"zone\":\"HB\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"}]}', NULL, 1, NULL, '2026-10-09 04:20:18'),
(652, 781, '50m Breaststroke (Men) - Heat 2 (5 Swimmers)', 'Heats', NULL, NULL, NULL, 783, NULL, '2026-10-09', '11:05:00', '11:25:00', 'scheduled', '{\"type\":\"swimming\",\"event_name\":\"50m Breaststroke (Men)\",\"heat_no\":\"50m Breaststroke (Men) - Heat 2 (5 Swimmers)\",\"stage\":\"Heats\",\"participants\":\"Swimmers #6 to #10\",\"summary\":\"Scheduled: Swimmers #6 to #10 \\u2022 Heats\",\"lanes\":[{\"lane\":1,\"swimmer\":\"SUNEET BATTY\",\"zone\":\"HB\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":2,\"swimmer\":\"HITESH PANIHAR\",\"zone\":\"MF\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":3,\"swimmer\":\"SURAJ CHOTULAL SHAHU\",\"zone\":\"MF\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":4,\"swimmer\":\"ADSULE VISHWAS APPASAHEB\",\"zone\":\"MR\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":5,\"swimmer\":\"PATHAK DIVYANG\",\"zone\":\"MR\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"}]}', NULL, 1, NULL, '2026-10-09 04:20:18'),
(653, 781, '50m Breaststroke (Women) - Direct Final', 'Final', NULL, NULL, NULL, 783, NULL, '2026-10-09', '11:30:00', '11:45:00', 'completed', '{\"type\":\"swimming\",\"event_name\":\"50m Breaststroke (Women)\",\"category\":\"Team Event\",\"heat\":\"50m Breaststroke (Women) - Direct Final\",\"heat_no\":\"50m Breaststroke (Women) - Direct Final\",\"lanes\":{\"1\":{\"lane\":1,\"swimmer\":\"RAWAT NISHA\",\"zone\":\"PH\",\"time\":\"00.56.32\",\"pos\":\"1st\",\"status\":\"normal\"},\"2\":{\"lane\":2,\"swimmer\":\"TISHA MEENA\",\"zone\":\"MR\",\"time\":\"01.35.07\",\"pos\":\"2nd\",\"status\":\"normal\"}},\"status\":\"completed\",\"winner_name\":\"RAWAT NISHA\",\"summary\":\"50m Breaststroke (Women) (50m Breaststroke (Women) - Direct Final): 1st RAWAT NISHA (00.56.32)\"}', NULL, 1, NULL, '2026-10-09 04:20:18'),
(654, 781, '50m Freestyle (Men) - FINAL', 'Final', NULL, NULL, NULL, 783, NULL, '2026-10-09', '12:15:00', '12:35:00', 'scheduled', '{\"type\":\"swimming\",\"event_name\":\"50m Freestyle (Men)\",\"heat_no\":\"50m Freestyle (Men) - FINAL\",\"stage\":\"Final\",\"participants\":\"Top Qualifiers\",\"summary\":\"Scheduled: Top Qualifiers \\u2022 Final\",\"lanes\":[]}', NULL, 1, NULL, '2026-10-09 04:20:18'),
(655, 781, '50m Breaststroke (Men) - FINAL', 'Final', NULL, NULL, NULL, 783, NULL, '2026-10-09', '12:40:00', '13:00:00', 'scheduled', '{\"type\":\"swimming\",\"event_name\":\"50m Breaststroke (Men)\",\"heat_no\":\"50m Breaststroke (Men) - FINAL\",\"stage\":\"Final\",\"participants\":\"Top Qualifiers\",\"summary\":\"Scheduled: Top Qualifiers \\u2022 Final\",\"lanes\":[]}', NULL, 1, NULL, '2026-10-09 04:20:18'),
(714, 1, 'Men\'s Pool A - Match 1 (A1 vs A6)', 'A', 855, 801, 855, 1, 3, '2026-10-09', '09:00:00', '09:40:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":3,\"g2_a\":15,\"g2_b\":5,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"MR Badminton\",\"summary\":\"MR Badminton won (15-3, 15-5)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(715, 1, 'Men\'s Pool A - Match 2 (A2 vs A5)', 'A', 837, 882, 837, 785, 3, '2026-10-09', '09:00:00', '09:40:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":null,\"g2_a\":null,\"g2_b\":20,\"g3_a\":15,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"WZ Badminton\",\"summary\":\"WZ Badminton won (15-0, 0-20, 15-0)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(716, 1, 'Men\'s Pool A - Match 3 (A3 vs A4)', 'A', 846, 819, 819, 1, 3, '2026-10-09', '10:20:00', '11:00:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":10,\"g1_b\":15,\"g2_a\":11,\"g2_b\":15,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"PH Badminton\",\"summary\":\"PH Badminton won (10-15, 11-15)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(717, 1, 'Men\'s Pool A - Match 4 (A1 vs A5)', 'A', 855, 882, 855, 785, 3, '2026-10-09', '10:20:00', '11:00:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":16,\"g1_b\":14,\"g2_a\":15,\"g2_b\":12,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"MR Badminton\",\"summary\":\"MR Badminton won (16-14, 15-12)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(718, 1, 'Men\'s Pool A - Match 5 (A6 vs A4)', 'A', 801, 819, 801, 1, 3, '2026-10-09', '11:40:00', '12:20:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":7,\"g2_a\":16,\"g2_b\":6,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"NCZ Badminton\",\"summary\":\"NCZ Badminton won (15-7, 16-6)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(719, 1, 'Men\'s Pool A - Match 6 (A2 vs A3)', 'A', 837, 846, 837, 785, 3, '2026-10-09', '11:40:00', '12:20:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":9,\"g2_a\":15,\"g2_b\":7,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"WZ Badminton\",\"summary\":\"WZ Badminton won (15-9, 15-7)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(720, 1, 'Men\'s Pool A - Match 7 (A1 vs A4)', 'A', 855, 819, 855, 1, 3, '2026-10-09', '14:00:00', '14:40:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":11,\"g2_a\":15,\"g2_b\":10,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"MR Badminton\",\"summary\":\"MR Badminton won (15-11, 15-10)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(721, 1, 'Men\'s Pool A - Match 8 (A5 vs A3)', 'A', 882, 846, 882, 785, 3, '2026-10-09', '14:00:00', '14:40:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":10,\"g2_a\":15,\"g2_b\":10,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"EZ Badminton\",\"summary\":\"EZ Badminton won (15-10, 15-10)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(722, 1, 'Men\'s Pool A - Match 9 (A6 vs A2)', 'A', 801, 837, 837, 1, 3, '2026-10-09', '15:20:00', '16:00:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":17,\"g1_b\":19,\"g2_a\":15,\"g2_b\":11,\"g3_a\":6,\"g3_b\":15,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"WZ Badminton\",\"summary\":\"WZ Badminton won (17-19, 15-11, 6-15)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(723, 1, 'Men\'s Pool A - Match 10 (A1 vs A3)', 'A', 855, 846, 855, 785, 3, '2026-10-09', '15:20:00', '16:00:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":7,\"g2_a\":15,\"g2_b\":4,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"MR Badminton\",\"summary\":\"MR Badminton won (15-7, 15-4)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(724, 1, 'Men\'s Pool A - Match 11 (A4 vs A2)', 'A', 819, 837, 837, 1, 3, '2026-10-09', '16:40:00', '17:20:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":8,\"g1_b\":15,\"g2_a\":15,\"g2_b\":17,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"WZ Badminton\",\"summary\":\"WZ Badminton won (8-15, 15-17)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(725, 1, 'Men\'s Pool A - Match 12 (A5 vs A6)', 'A', 882, 801, 801, 785, 3, '2026-10-09', '16:40:00', '17:20:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":12,\"g1_b\":15,\"g2_a\":7,\"g2_b\":15,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"NCZ Badminton\",\"summary\":\"NCZ Badminton won (12-15, 7-15)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(726, 1, 'Men\'s Pool A - Match 13 (A1 vs A2)', 'A', 855, 837, NULL, 1, 3, '2026-10-09', '17:20:00', '18:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-09 05:41:29'),
(727, 1, 'Men\'s Pool A - Match 14 (A3 vs A6)', 'A', 846, 801, 801, 785, 3, '2026-10-09', '17:20:00', '18:00:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":10,\"g2_a\":15,\"g2_b\":5,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"NCZ Badminton\",\"summary\":\"NCZ Badminton won (15-10, 15-5)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(728, 1, 'Men\'s Pool A - Match 15 (A4 vs A5)', 'A', 819, 882, 819, 1, 3, '2026-10-09', '18:00:00', '18:40:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":9,\"g1_b\":15,\"g2_a\":15,\"g2_b\":null,\"g3_a\":15,\"g3_b\":13,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"PH Badminton\",\"summary\":\"PH Badminton won (9-15, 15-0, 15-13)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(729, 1, 'Men\'s Pool B - Match 1 (B1 vs B6)', 'B', 792, 810, 792, 789, 3, '2026-10-09', '09:00:00', '09:40:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":4,\"g2_a\":8,\"g2_b\":15,\"g3_a\":15,\"g3_b\":5,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"VR Badminton\",\"summary\":\"VR Badminton won (15-4, 8-15, 15-5)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(730, 1, 'Men\'s Pool B - Match 2 (B2 vs B5)', 'B', 873, 864, 873, 790, 3, '2026-10-09', '09:00:00', '09:40:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":12,\"g2_a\":15,\"g2_b\":11,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"SCZ Badminton\",\"summary\":\"SCZ Badminton won (15-12, 15-11)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(731, 1, 'Men\'s Pool B - Match 3 (B3 vs B4)', 'B', 828, 891, 828, 789, 3, '2026-10-09', '10:20:00', '11:00:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":5,\"g2_a\":15,\"g2_b\":9,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"SZ Badminton\",\"summary\":\"SZ Badminton won (15-5, 15-9)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(732, 1, 'Men\'s Pool B - Match 4 (B1 vs B5)', 'B', 792, 864, 792, 790, 3, '2026-10-09', '10:20:00', '11:00:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":12,\"g2_a\":6,\"g2_b\":15,\"g3_a\":15,\"g3_b\":11,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"VR Badminton\",\"summary\":\"VR Badminton won (15-12, 6-15, 15-11)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(733, 1, 'Men\'s Pool B - Match 5 (B6 vs B4)', 'B', 810, 891, 810, 789, 3, '2026-10-09', '11:40:00', '12:20:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":11,\"g2_a\":15,\"g2_b\":10,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"MF Badminton\",\"summary\":\"MF Badminton won (15-11, 15-10)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(734, 1, 'Men\'s Pool B - Match 6 (B2 vs B3)', 'B', 873, 828, 873, 790, 3, '2026-10-09', '11:40:00', '12:20:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":17,\"g2_a\":15,\"g2_b\":7,\"g3_a\":15,\"g3_b\":8,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"SCZ Badminton\",\"summary\":\"SCZ Badminton won (15-17, 15-7, 15-8)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(735, 1, 'Men\'s Pool B - Match 7 (B1 vs B4)', 'B', 792, 891, 792, 789, 3, '2026-10-09', '14:00:00', '14:40:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":4,\"g2_a\":15,\"g2_b\":12,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"VR Badminton\",\"summary\":\"VR Badminton won (15-4, 15-12)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(736, 1, 'Men\'s Pool B - Match 8 (B5 vs B3)', 'B', 864, 828, 864, 790, 3, '2026-10-09', '14:00:00', '14:40:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":12,\"g2_a\":15,\"g2_b\":13,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"NWZ Badminton\",\"summary\":\"NWZ Badminton won (15-12, 15-13)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(737, 1, 'Men\'s Pool B - Match 9 (B6 vs B2)', 'B', 810, 873, 810, 789, 3, '2026-10-09', '15:20:00', '16:00:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":17,\"g1_b\":15,\"g2_a\":15,\"g2_b\":9,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"MF Badminton\",\"summary\":\"MF Badminton won (17-15, 15-9)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(738, 1, 'Men\'s Pool B - Match 10 (B1 vs B3)', 'B', 792, 828, 792, 790, 3, '2026-10-09', '15:20:00', '16:00:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":6,\"g2_a\":15,\"g2_b\":12,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"VR Badminton\",\"summary\":\"VR Badminton won (15-6, 15-12)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(739, 1, 'Men\'s Pool B - Match 11 (B4 vs B2)', 'B', 891, 873, NULL, 789, 3, '2026-10-09', '16:40:00', '17:20:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-09 05:41:29'),
(740, 1, 'Men\'s Pool B - Match 12 (B5 vs B6)', 'B', 864, 810, NULL, 790, 3, '2026-10-09', '16:40:00', '17:20:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-09 05:41:29'),
(741, 1, 'Men\'s Pool B - Match 13 (B1 vs B2)', 'B', 792, 873, NULL, 789, 3, '2026-10-09', '17:20:00', '18:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-09 05:41:29'),
(742, 1, 'Men\'s Pool B - Match 14 (B3 vs B6)', 'B', 828, 810, NULL, 790, 3, '2026-10-09', '17:20:00', '18:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-09 05:41:29'),
(743, 1, 'Men\'s Pool B - Match 15 (B4 vs B5)', 'B', 891, 864, 864, 789, 3, '2026-10-09', '18:00:00', '18:40:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":4,\"g1_b\":15,\"g2_a\":11,\"g2_b\":15,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"NWZ Badminton\",\"summary\":\"NWZ Badminton won (4-15, 11-15)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(744, 1, 'Women\'s Pool A - Match 1 (A1 vs A2)', 'A', 819, 864, 819, 1, 3, '2026-10-09', '09:40:00', '10:20:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":7,\"g2_a\":16,\"g2_b\":15,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"PH Badminton\",\"summary\":\"PH Badminton won (15-7, 16-15)\"}', NULL, 1, NULL, '2026-10-09 05:41:29');
INSERT INTO `matches` (`id`, `game_id`, `round`, `pool_name`, `team1_id`, `team2_id`, `winner_id`, `facility_id`, `score_format_id`, `match_date`, `start_time`, `end_time`, `status`, `scores_json`, `volunteer_id`, `is_published`, `lock_device_id`, `created_at`) VALUES
(745, 1, 'Women\'s Pool A - Match 2 (A3 vs A4)', 'A', 810, 846, 846, 785, 3, '2026-10-09', '09:40:00', '10:20:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":3,\"g2_a\":13,\"g2_b\":15,\"g3_a\":8,\"g3_b\":15,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"NZ Badminton\",\"summary\":\"NZ Badminton won (15-3, 13-15, 8-15)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(746, 1, 'Women\'s Pool A - Match 3 (A1 vs A5)', 'A', 819, 882, 819, 1, 3, '2026-10-09', '11:00:00', '11:40:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":null,\"g1_b\":null,\"g2_a\":null,\"g2_b\":null,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"PH Badminton\",\"summary\":\"PH Badminton won (In Progress)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(747, 1, 'Women\'s Pool A - Match 4 (A2 vs A3)', 'A', 864, 810, 864, 785, 3, '2026-10-09', '11:00:00', '11:40:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":6,\"g2_a\":15,\"g2_b\":12,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"NWZ Badminton\",\"summary\":\"NWZ Badminton won (15-6, 15-12)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(748, 1, 'Women\'s Pool A - Match 5 (A4 vs A5)', 'A', 846, 882, NULL, 1, 3, '2026-10-09', '12:20:00', '13:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-09 05:41:29'),
(749, 1, 'Women\'s Pool A - Match 6 (A1 vs A3)', 'A', 819, 810, 819, 785, 3, '2026-10-09', '12:20:00', '13:00:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":5,\"g2_a\":15,\"g2_b\":10,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"PH Badminton\",\"summary\":\"PH Badminton won (15-5, 15-10)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(750, 1, 'Women\'s Pool A - Match 7 (A2 vs A4)', 'A', 864, 846, 846, 1, 3, '2026-10-09', '14:40:00', '15:20:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":17,\"g1_b\":15,\"g2_a\":10,\"g2_b\":15,\"g3_a\":14,\"g3_b\":16,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"NZ Badminton\",\"summary\":\"NZ Badminton won (17-15, 10-15, 14-16)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(751, 1, 'Women\'s Pool A - Match 8 (A3 vs A5)', 'A', 810, 882, NULL, 785, 3, '2026-10-09', '14:40:00', '15:20:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-09 05:41:29'),
(752, 1, 'Women\'s Pool A - Match 9 (A1 vs A4)', 'A', 819, 846, 819, 1, 3, '2026-10-09', '16:00:00', '16:40:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":5,\"g2_a\":15,\"g2_b\":9,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"PH Badminton\",\"summary\":\"PH Badminton won (15-5, 15-9)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(753, 1, 'Women\'s Pool A - Match 10 (A2 vs A5)', 'A', 864, 882, NULL, 785, 3, '2026-10-09', '16:00:00', '16:40:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-09 05:41:29'),
(754, 1, 'Women\'s Pool B - Match 1 (B1 vs B2)', 'B', 792, 855, 855, 789, 3, '2026-10-09', '09:40:00', '10:20:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":9,\"g1_b\":15,\"g2_a\":11,\"g2_b\":15,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"MR Badminton\",\"summary\":\"MR Badminton won (9-15, 11-15)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(755, 1, 'Women\'s Pool B - Match 2 (B3 vs B4)', 'B', 828, 873, 873, 790, 3, '2026-10-09', '09:40:00', '10:20:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":4,\"g1_b\":15,\"g2_a\":7,\"g2_b\":15,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"SCZ Badminton\",\"summary\":\"SCZ Badminton won (4-15, 7-15)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(756, 1, 'Women\'s Pool B - Match 3 (B1 vs B5)', 'B', 792, 801, 792, 789, 3, '2026-10-09', '11:00:00', '11:40:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":11,\"g2_a\":17,\"g2_b\":15,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"VR Badminton\",\"summary\":\"VR Badminton won (15-11, 17-15)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(757, 1, 'Women\'s Pool B - Match 4 (B2 vs B3)', 'B', 855, 828, 855, 790, 3, '2026-10-09', '11:00:00', '11:40:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":1,\"g2_a\":15,\"g2_b\":1,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"MR Badminton\",\"summary\":\"MR Badminton won (15-1, 15-1)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(758, 1, 'Women\'s Pool B - Match 5 (B4 vs B5)', 'B', 873, 801, 873, 789, 3, '2026-10-09', '12:20:00', '13:00:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":5,\"g2_a\":15,\"g2_b\":3,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"SCZ Badminton\",\"summary\":\"SCZ Badminton won (15-5, 15-3)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(759, 1, 'Women\'s Pool B - Match 6 (B1 vs B3)', 'B', 792, 828, NULL, 790, 3, '2026-10-09', '12:20:00', '13:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-09 05:41:29'),
(760, 1, 'Women\'s Pool B - Match 7 (B2 vs B4)', 'B', 855, 873, 855, 789, 3, '2026-10-09', '14:40:00', '15:20:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":15,\"g1_b\":12,\"g2_a\":15,\"g2_b\":13,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"MR Badminton\",\"summary\":\"MR Badminton won (15-12, 15-13)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(761, 1, 'Women\'s Pool B - Match 8 (B3 vs B5)', 'B', 828, 801, 828, 790, 3, '2026-10-09', '14:40:00', '15:20:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":8,\"g1_b\":15,\"g2_a\":15,\"g2_b\":9,\"g3_a\":15,\"g3_b\":6,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"SZ Badminton\",\"summary\":\"SZ Badminton won (8-15, 15-9, 15-6)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(762, 1, 'Women\'s Pool B - Match 9 (B1 vs B4)', 'B', 792, 873, 873, 789, 3, '2026-10-09', '16:00:00', '16:40:00', 'completed', '{\"type\":\"badminton_table_tennis\",\"is_best_of_5\":0,\"g1_a\":11,\"g1_b\":15,\"g2_a\":11,\"g2_b\":15,\"g3_a\":null,\"g3_b\":null,\"g4_a\":null,\"g4_b\":null,\"g5_a\":null,\"g5_b\":null,\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"SCZ Badminton\",\"summary\":\"SCZ Badminton won (11-15, 11-15)\"}', NULL, 1, NULL, '2026-10-09 05:41:29'),
(763, 1, 'Women\'s Pool B - Match 10 (B2 vs B5)', 'B', 855, 801, NULL, 790, 3, '2026-10-09', '16:00:00', '16:40:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-09 05:41:29'),
(764, 1, 'Women\'s Semifinal 1 (Winner A vs Runner-up B)', 'Knockout', 819, 855, NULL, 1, 3, '2026-10-10', '10:00:00', '10:50:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-09 05:41:29'),
(765, 1, 'Women\'s Semifinal 2 (Winner B vs Runner-up A)', 'Knockout', 792, 864, NULL, 785, 3, '2026-10-10', '10:00:00', '10:50:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-09 05:41:29'),
(766, 1, 'Women\'s 3rd Place Playoff (Bronze Medal)', 'Knockout', 855, 864, NULL, 1, 3, '2026-10-10', '14:00:00', '14:50:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-09 05:41:29'),
(767, 1, 'Women\'s Championship Final (Gold & Silver)', 'Knockout', 819, 792, NULL, 785, 3, '2026-10-10', '14:00:00', '15:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-09 05:41:29'),
(768, 1, 'Men\'s Semifinal 1 (Winner A vs Runner-up B)', 'Knockout', 855, 873, NULL, 1, 3, '2026-10-10', '11:00:00', '12:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-09 05:41:29'),
(769, 1, 'Men\'s Semifinal 2 (Winner B vs Runner-up A)', 'Knockout', 792, 837, NULL, 785, 3, '2026-10-10', '11:00:00', '12:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-09 05:41:29'),
(770, 1, 'Men\'s 3rd Place Playoff (Bronze Medal)', 'Knockout', 873, 837, NULL, 1, 3, '2026-10-10', '15:30:00', '16:45:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-09 05:41:29'),
(771, 1, 'Men\'s Championship Final (Gold & Silver)', 'Knockout', 855, 792, NULL, 785, 3, '2026-10-10', '15:30:00', '17:00:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-09 05:41:29'),
(772, 779, 'Round 2', 'B', 840, 813, NULL, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'scheduled', NULL, NULL, 1, NULL, '2026-10-09 07:26:47'),
(773, 779, 'Round 2', NULL, 894, 813, 894, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'completed', '{\"type\":\"carrom\",\"board_no\":\"1\",\"points_a\":2,\"points_b\":1,\"status\":\"completed\",\"winner\":\"HB Carrom\",\"summary\":\"HB Carrom won (Board 1: 2 - 1)\"}', NULL, 1, NULL, '2026-10-09 07:29:30'),
(774, 780, 'Pool A - Round 1', NULL, 859, 850, 859, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"MR Chess\",\"zone\":\"MR\"},\"black\":{\"player\":\"NZ Chess\",\"zone\":\"NZ\"},\"result\":\"1-0\",\"points_a\":1,\"points_b\":0,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: 1 - 0 (MR Chess Won)\"}', NULL, 1, NULL, '2026-10-09 08:57:15'),
(775, 780, 'Pool A - Round 1', NULL, 832, 796, 796, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"SZ Chess\",\"zone\":\"SZ\"},\"black\":{\"player\":\"VR Chess\",\"zone\":\"VR\"},\"result\":\"0-1\",\"points_a\":0,\"points_b\":1,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: 0 - 1 (VR Chess Won)\"}', NULL, 1, NULL, '2026-10-09 08:57:44'),
(776, 780, 'Pool A - Round 1', NULL, 877, 823, 877, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"SCZ Chess\",\"zone\":\"SCZ\"},\"black\":{\"player\":\"PH Chess\",\"zone\":\"PH\"},\"result\":\"1-0\",\"points_a\":1,\"points_b\":0,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: 1 - 0 (SCZ Chess Won)\"}', NULL, 1, NULL, '2026-10-09 08:58:07'),
(777, 780, 'Pool A - Round 1', NULL, 886, 805, 886, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"EZ Chess\",\"zone\":\"EZ\"},\"black\":{\"player\":\"NCZ Chess\",\"zone\":\"NCZ\"},\"result\":\"1-0\",\"points_a\":1,\"points_b\":0,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: 1 - 0 (EZ Chess Won)\"}', NULL, 1, NULL, '2026-10-09 08:58:31'),
(778, 780, 'Pool A - Round 1', NULL, 868, 841, 841, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"NWZ Chess\",\"zone\":\"NWZ\"},\"black\":{\"player\":\"WZ Chess\",\"zone\":\"WZ\"},\"result\":\"0-1\",\"points_a\":0,\"points_b\":1,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: 0 - 1 (WZ Chess Won)\"}', NULL, 1, NULL, '2026-10-09 08:58:53'),
(779, 780, 'Pool A - Round 1', NULL, 895, 814, 895, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"HB Chess\",\"zone\":\"HB\"},\"black\":{\"player\":\"MF Chess\",\"zone\":\"MF\"},\"result\":\"1-0\",\"points_a\":1,\"points_b\":0,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: 1 - 0 (HB Chess Won)\"}', NULL, 1, NULL, '2026-10-09 08:59:18'),
(780, 780, 'Pool A - Round 2', NULL, 886, 859, NULL, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"EZ Chess\",\"zone\":\"EZ\"},\"black\":{\"player\":\"MR Chess\",\"zone\":\"MR\"},\"result\":\"1\\/2-1\\/2\",\"points_a\":0.5,\"points_b\":0.5,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: \\u00bd - \\u00bd (Draw)\"}', NULL, 1, NULL, '2026-10-09 08:59:43'),
(781, 780, 'Pool A - Round 2', NULL, 796, 895, 796, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"VR Chess\",\"zone\":\"VR\"},\"black\":{\"player\":\"HB Chess\",\"zone\":\"HB\"},\"result\":\"1-0\",\"points_a\":1,\"points_b\":0,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: 1 - 0 (VR Chess Won)\"}', NULL, 1, NULL, '2026-10-09 08:59:57'),
(782, 780, 'Pool A - Round 2', NULL, 841, 877, 877, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"WZ Chess\",\"zone\":\"WZ\"},\"black\":{\"player\":\"SCZ Chess\",\"zone\":\"SCZ\"},\"result\":\"0-1\",\"points_a\":0,\"points_b\":1,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: 0 - 1 (SCZ Chess Won)\"}', NULL, 1, NULL, '2026-10-09 09:00:17'),
(783, 780, 'Pool A - Round 2', NULL, 805, 850, 850, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"NCZ Chess\",\"zone\":\"NCZ\"},\"black\":{\"player\":\"NZ Chess\",\"zone\":\"NZ\"},\"result\":\"0-1\",\"points_a\":0,\"points_b\":1,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: 0 - 1 (NZ Chess Won)\"}', NULL, 1, NULL, '2026-10-09 09:00:40'),
(784, 780, 'Pool A - Round 2', NULL, 823, 868, 823, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"PH Chess\",\"zone\":\"PH\"},\"black\":{\"player\":\"NWZ Chess\",\"zone\":\"NWZ\"},\"result\":\"1-0\",\"points_a\":1,\"points_b\":0,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: 1 - 0 (PH Chess Won)\"}', NULL, 1, NULL, '2026-10-09 09:01:01'),
(785, 780, 'Pool A - Round 2', NULL, 814, 832, 814, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"MF Chess\",\"zone\":\"MF\"},\"black\":{\"player\":\"SZ Chess\",\"zone\":\"SZ\"},\"result\":\"1-0\",\"points_a\":1,\"points_b\":0,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: 1 - 0 (MF Chess Won)\"}', NULL, 1, NULL, '2026-10-09 09:01:23'),
(786, 780, 'Pool A - Round 3', NULL, 877, 796, 796, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"SCZ Chess\",\"zone\":\"SCZ\"},\"black\":{\"player\":\"VR Chess\",\"zone\":\"VR\"},\"result\":\"0-1\",\"points_a\":0,\"points_b\":1,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: 0 - 1 (VR Chess Won)\"}', NULL, 1, NULL, '2026-10-09 10:43:06'),
(787, 780, 'Pool A - Round 3', NULL, 859, 814, NULL, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"MR Chess\",\"zone\":\"MR\"},\"black\":{\"player\":\"MF Chess\",\"zone\":\"MF\"},\"result\":\"1\\/2-1\\/2\",\"points_a\":0.5,\"points_b\":0.5,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: \\u00bd - \\u00bd (Draw)\"}', NULL, 1, NULL, '2026-10-09 10:43:22'),
(788, 780, 'Pool A - Round 3', NULL, 850, 886, 850, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"NZ Chess\",\"zone\":\"NZ\"},\"black\":{\"player\":\"EZ Chess\",\"zone\":\"EZ\"},\"result\":\"1-0\",\"points_a\":1,\"points_b\":0,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: 1 - 0 (NZ Chess Won)\"}', NULL, 1, NULL, '2026-10-09 10:43:37'),
(789, 780, 'Pool A - Round 3', NULL, 895, 823, NULL, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"HB Chess\",\"zone\":\"HB\"},\"black\":{\"player\":\"PH Chess\",\"zone\":\"PH\"},\"result\":\"1\\/2-1\\/2\",\"points_a\":0.5,\"points_b\":0.5,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: \\u00bd - \\u00bd (Draw)\"}', NULL, 1, NULL, '2026-10-09 10:43:54'),
(790, 780, 'Pool A - Round 3', NULL, 832, 841, 832, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"SZ Chess\",\"zone\":\"SZ\"},\"black\":{\"player\":\"WZ Chess\",\"zone\":\"WZ\"},\"result\":\"1-0\",\"points_a\":1,\"points_b\":0,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: 1 - 0 (SZ Chess Won)\"}', NULL, 1, NULL, '2026-10-09 10:44:09'),
(791, 780, 'Pool A - Round 3', NULL, 868, 805, 805, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"NWZ Chess\",\"zone\":\"NWZ\"},\"black\":{\"player\":\"NCZ Chess\",\"zone\":\"NCZ\"},\"result\":\"0-1\",\"points_a\":0,\"points_b\":1,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: 0 - 1 (NCZ Chess Won)\"}', NULL, 1, NULL, '2026-10-09 10:44:29'),
(792, 780, 'Pool A - Round 4', NULL, 796, 859, 796, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"VR Chess\",\"zone\":\"VR\"},\"black\":{\"player\":\"MR Chess\",\"zone\":\"MR\"},\"result\":\"1-0\",\"points_a\":1,\"points_b\":0,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: 1 - 0 (VR Chess Won)\"}', NULL, 1, NULL, '2026-10-09 10:45:00'),
(793, 780, 'Pool A - Round 4', NULL, 850, 877, 850, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"NZ Chess\",\"zone\":\"NZ\"},\"black\":{\"player\":\"SCZ Chess\",\"zone\":\"SCZ\"},\"result\":\"1-0\",\"points_a\":1,\"points_b\":0,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: 1 - 0 (NZ Chess Won)\"}', NULL, 1, NULL, '2026-10-09 10:45:19'),
(794, 780, 'Pool A - Round 4', NULL, 805, 895, 895, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"NCZ Chess\",\"zone\":\"NCZ\"},\"black\":{\"player\":\"HB Chess\",\"zone\":\"HB\"},\"result\":\"0-1\",\"points_a\":0,\"points_b\":1,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: 0 - 1 (HB Chess Won)\"}', NULL, 1, NULL, '2026-10-09 10:45:36'),
(795, 780, 'Pool A - Round 4', NULL, 823, 832, NULL, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"PH Chess\",\"zone\":\"PH\"},\"black\":{\"player\":\"SZ Chess\",\"zone\":\"SZ\"},\"result\":\"1\\/2-1\\/2\",\"points_a\":0.5,\"points_b\":0.5,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: \\u00bd - \\u00bd (Draw)\"}', NULL, 1, NULL, '2026-10-09 10:45:48'),
(796, 780, 'Pool A - Round 4', NULL, 841, 886, 886, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"WZ Chess\",\"zone\":\"WZ\"},\"black\":{\"player\":\"EZ Chess\",\"zone\":\"EZ\"},\"result\":\"0-1\",\"points_a\":0,\"points_b\":1,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: 0 - 1 (EZ Chess Won)\"}', NULL, 1, NULL, '2026-10-09 10:46:02'),
(797, 780, 'Pool A - Round 4', NULL, 814, 868, 814, NULL, NULL, '2026-10-08', '09:00:00', '10:15:00', 'in_progress', '{\"type\":\"chess\",\"board_no\":\"1\",\"white\":{\"player\":\"MF Chess\",\"zone\":\"MF\"},\"black\":{\"player\":\"NWZ Chess\",\"zone\":\"NWZ\"},\"result\":\"1-0\",\"points_a\":1,\"points_b\":0,\"live_note\":\"\",\"status\":\"in_progress\",\"winner\":null,\"summary\":\"Board 1: 1 - 0 (MF Chess Won)\"}', NULL, 1, NULL, '2026-10-09 10:46:20'),
(823, 778, 'Round 1 (R-I)', 'Table 1', 812, 857, 857, NULL, NULL, '2026-10-09', '10:00:00', '11:30:00', 'completed', '{\"type\":\"bridge\",\"round_no\":1,\"round_label\":\"R-I\",\"table_no\":1,\"team1_num\":1,\"team2_num\":2,\"vps_a\":0.26,\"vps_b\":19.74,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-I Table 1: 0.26 - 19.74 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(824, 778, 'Round 1 (R-I)', 'Table 2', 803, 893, 803, NULL, NULL, '2026-10-09', '10:00:00', '11:30:00', 'completed', '{\"type\":\"bridge\",\"round_no\":1,\"round_label\":\"R-I\",\"table_no\":2,\"team1_num\":3,\"team2_num\":4,\"vps_a\":12.77,\"vps_b\":7.23,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-I Table 2: 12.77 - 7.23 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(825, 778, 'Round 1 (R-I)', 'Table 3', 794, 839, NULL, NULL, NULL, '2026-10-09', '10:00:00', '11:30:00', 'completed', '{\"type\":\"bridge\",\"round_no\":1,\"round_label\":\"R-I\",\"table_no\":3,\"team1_num\":5,\"team2_num\":6,\"vps_a\":10.00,\"vps_b\":10.00,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-I Table 3: 10 - 10 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(826, 778, 'Round 1 (R-I)', 'Table 4', 848, 875, 875, NULL, NULL, '2026-10-09', '10:00:00', '11:30:00', 'completed', '{\"type\":\"bridge\",\"round_no\":1,\"round_label\":\"R-I\",\"table_no\":4,\"team1_num\":7,\"team2_num\":8,\"vps_a\":7.58,\"vps_b\":12.42,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-I Table 4: 7.58 - 12.42 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(827, 778, 'Round 1 (R-I)', 'Table 5', 821, 866, 821, NULL, NULL, '2026-10-09', '10:00:00', '11:30:00', 'completed', '{\"type\":\"bridge\",\"round_no\":1,\"round_label\":\"R-I\",\"table_no\":5,\"team1_num\":9,\"team2_num\":10,\"vps_a\":19.74,\"vps_b\":0.26,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-I Table 5: 19.74 - 0.26 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(828, 778, 'Round 2 (R-II)', 'Table 1', 812, 866, 812, NULL, NULL, '2026-10-09', '11:45:00', '13:15:00', 'completed', '{\"type\":\"bridge\",\"round_no\":2,\"round_label\":\"R-II\",\"table_no\":1,\"team1_num\":1,\"team2_num\":10,\"vps_a\":20.00,\"vps_b\":0.00,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-II Table 1: 20 - 0 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(829, 778, 'Round 2 (R-II)', 'Table 2', 857, 821, 857, NULL, NULL, '2026-10-09', '11:45:00', '13:15:00', 'completed', '{\"type\":\"bridge\",\"round_no\":2,\"round_label\":\"R-II\",\"table_no\":2,\"team1_num\":2,\"team2_num\":9,\"vps_a\":11.27,\"vps_b\":8.73,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-II Table 2: 11.27 - 8.73 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(830, 778, 'Round 2 (R-II)', 'Table 3', 803, 875, 875, NULL, NULL, '2026-10-09', '11:45:00', '13:15:00', 'completed', '{\"type\":\"bridge\",\"round_no\":2,\"round_label\":\"R-II\",\"table_no\":3,\"team1_num\":3,\"team2_num\":8,\"vps_a\":7.58,\"vps_b\":12.42,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-II Table 3: 7.58 - 12.42 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(831, 778, 'Round 2 (R-II)', 'Table 4', 893, 794, 794, NULL, NULL, '2026-10-09', '11:45:00', '13:15:00', 'completed', '{\"type\":\"bridge\",\"round_no\":2,\"round_label\":\"R-II\",\"table_no\":4,\"team1_num\":4,\"team2_num\":5,\"vps_a\":4.00,\"vps_b\":16.00,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-II Table 4: 4 - 16 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(832, 778, 'Round 2 (R-II)', 'Table 5', 839, 848, 839, NULL, NULL, '2026-10-09', '11:45:00', '13:15:00', 'completed', '{\"type\":\"bridge\",\"round_no\":2,\"round_label\":\"R-II\",\"table_no\":5,\"team1_num\":6,\"team2_num\":7,\"vps_a\":20.00,\"vps_b\":0.00,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-II Table 5: 20 - 0 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(833, 778, 'Round 3 (R-III)', 'Table 1', 857, 839, 857, NULL, NULL, '2026-10-09', '14:30:00', '16:00:00', 'completed', '{\"type\":\"bridge\",\"round_no\":3,\"round_label\":\"R-III\",\"table_no\":1,\"team1_num\":2,\"team2_num\":6,\"vps_a\":20.00,\"vps_b\":0.00,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-III Table 1: 20 - 0 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(834, 778, 'Round 3 (R-III)', 'Table 2', 794, 821, 794, NULL, NULL, '2026-10-09', '14:30:00', '16:00:00', 'completed', '{\"type\":\"bridge\",\"round_no\":3,\"round_label\":\"R-III\",\"table_no\":2,\"team1_num\":5,\"team2_num\":9,\"vps_a\":20.00,\"vps_b\":0.00,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-III Table 2: 20 - 0 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(835, 778, 'Round 3 (R-III)', 'Table 3', 812, 875, 812, NULL, NULL, '2026-10-09', '14:30:00', '16:00:00', 'completed', '{\"type\":\"bridge\",\"round_no\":3,\"round_label\":\"R-III\",\"table_no\":3,\"team1_num\":1,\"team2_num\":8,\"vps_a\":16.90,\"vps_b\":3.10,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-III Table 3: 16.90 - 3.10 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(836, 778, 'Round 3 (R-III)', 'Table 4', 848, 803, 848, NULL, NULL, '2026-10-09', '14:30:00', '16:00:00', 'completed', '{\"type\":\"bridge\",\"round_no\":3,\"round_label\":\"R-III\",\"table_no\":4,\"team1_num\":7,\"team2_num\":3,\"vps_a\":20.00,\"vps_b\":0.00,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-III Table 4: 20 - 0 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(837, 778, 'Round 3 (R-III)', 'Table 5', 893, 866, 893, NULL, NULL, '2026-10-09', '14:30:00', '16:00:00', 'completed', '{\"type\":\"bridge\",\"round_no\":3,\"round_label\":\"R-III\",\"table_no\":5,\"team1_num\":4,\"team2_num\":10,\"vps_a\":18.37,\"vps_b\":1.63,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-III Table 5: 18.37 - 1.63 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(838, 778, 'Round 4 (R-IV)', 'Table 1', 857, 794, 857, NULL, NULL, '2026-10-09', '16:15:00', '17:45:00', 'completed', '{\"type\":\"bridge\",\"round_no\":4,\"round_label\":\"R-IV\",\"table_no\":1,\"team1_num\":2,\"team2_num\":5,\"vps_a\":18.97,\"vps_b\":1.03,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-IV Table 1: 18.97 - 1.03 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(839, 778, 'Round 4 (R-IV)', 'Table 2', 812, 839, 812, NULL, NULL, '2026-10-09', '16:15:00', '17:45:00', 'completed', '{\"type\":\"bridge\",\"round_no\":4,\"round_label\":\"R-IV\",\"table_no\":2,\"team1_num\":1,\"team2_num\":6,\"vps_a\":10.44,\"vps_b\":9.56,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-IV Table 2: 10.44 - 9.56 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(840, 778, 'Round 4 (R-IV)', 'Table 3', 893, 848, 893, NULL, NULL, '2026-10-09', '16:15:00', '17:45:00', 'completed', '{\"type\":\"bridge\",\"round_no\":4,\"round_label\":\"R-IV\",\"table_no\":3,\"team1_num\":4,\"team2_num\":7,\"vps_a\":10.44,\"vps_b\":9.56,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-IV Table 3: 10.44 - 9.56 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(841, 778, 'Round 4 (R-IV)', 'Table 4', 821, 803, 821, NULL, NULL, '2026-10-09', '16:15:00', '17:45:00', 'completed', '{\"type\":\"bridge\",\"round_no\":4,\"round_label\":\"R-IV\",\"table_no\":4,\"team1_num\":9,\"team2_num\":3,\"vps_a\":12.05,\"vps_b\":7.95,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-IV Table 4: 12.05 - 7.95 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(842, 778, 'Round 4 (R-IV)', 'Table 5', 866, 875, 866, NULL, NULL, '2026-10-09', '16:15:00', '17:45:00', 'completed', '{\"type\":\"bridge\",\"round_no\":4,\"round_label\":\"R-IV\",\"table_no\":5,\"team1_num\":10,\"team2_num\":8,\"vps_a\":10.86,\"vps_b\":9.14,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-IV Table 5: 10.86 - 9.14 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(843, 778, 'Round 5 (R-V)', 'Table 1', 857, 848, 857, NULL, NULL, '2026-10-09', '18:00:00', '19:30:00', 'completed', '{\"type\":\"bridge\",\"round_no\":5,\"round_label\":\"R-V\",\"table_no\":1,\"team1_num\":2,\"team2_num\":7,\"vps_a\":20.00,\"vps_b\":0.00,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-V Table 1: 20 - 0 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(844, 778, 'Round 5 (R-V)', 'Table 2', 794, 812, 794, NULL, NULL, '2026-10-09', '18:00:00', '19:30:00', 'completed', '{\"type\":\"bridge\",\"round_no\":5,\"round_label\":\"R-V\",\"table_no\":2,\"team1_num\":5,\"team2_num\":1,\"vps_a\":15.23,\"vps_b\":4.77,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-V Table 2: 15.23 - 4.77 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(845, 778, 'Round 5 (R-V)', 'Table 3', 875, 839, 875, NULL, NULL, '2026-10-09', '18:00:00', '19:30:00', 'completed', '{\"type\":\"bridge\",\"round_no\":5,\"round_label\":\"R-V\",\"table_no\":3,\"team1_num\":8,\"team2_num\":6,\"vps_a\":16.90,\"vps_b\":3.10,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-V Table 3: 16.90 - 3.10 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(846, 778, 'Round 5 (R-V)', 'Table 4', 821, 893, 821, NULL, NULL, '2026-10-09', '18:00:00', '19:30:00', 'completed', '{\"type\":\"bridge\",\"round_no\":5,\"round_label\":\"R-V\",\"table_no\":4,\"team1_num\":9,\"team2_num\":4,\"vps_a\":11.27,\"vps_b\":8.73,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-V Table 4: 11.27 - 8.73 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11'),
(847, 778, 'Round 5 (R-V)', 'Table 5', 803, 866, 803, NULL, NULL, '2026-10-09', '18:00:00', '19:30:00', 'completed', '{\"type\":\"bridge\",\"round_no\":5,\"round_label\":\"R-V\",\"table_no\":5,\"team1_num\":3,\"team2_num\":10,\"vps_a\":18.83,\"vps_b\":1.17,\"imps_a\":0,\"imps_b\":0,\"status\":\"completed\",\"summary\":\"R-V Table 5: 18.83 - 1.17 VPs\"}', NULL, 1, NULL, '2026-10-09 21:11:11');

-- --------------------------------------------------------

--
-- Table structure for table `photos`
--

CREATE TABLE `photos` (
  `id` int(11) NOT NULL,
  `album_id` int(11) DEFAULT NULL,
  `event_type` enum('General','Meetings','Ceremony','Game') NOT NULL DEFAULT 'Game',
  `title` varchar(255) DEFAULT NULL,
  `day` int(11) NOT NULL,
  `game_id` int(11) DEFAULT NULL,
  `time_slot` varchar(50) DEFAULT NULL,
  `match_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `compressed_path` varchar(255) DEFAULT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `designation` varchar(100) DEFAULT 'Executive',
  `gender` enum('Men','Women') NOT NULL DEFAULT 'Men',
  `photo_path` varchar(255) DEFAULT NULL,
  `is_u30` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `players`
--

INSERT INTO `players` (`id`, `team_id`, `unit_id`, `name`, `designation`, `gender`, `photo_path`, `is_u30`) VALUES
(1, 883, 788, 'MAHAPATRA PROMOD KUMAR', 'Player', 'Men', NULL, 0),
(2, 883, 788, 'KUMAR ANAND', 'Player', 'Men', NULL, 0),
(3, 883, 788, 'RAJAUR ANIL KUMAR', 'Player', 'Men', NULL, 0),
(4, 883, 788, 'SALIL DAS', 'Player', 'Men', NULL, 0),
(5, 883, 788, 'CHAKRABORTY SHOVAN', 'Player', 'Men', NULL, 0),
(6, 890, 788, 'RAHUL KARMAKAR', 'Player', 'Men', NULL, 0),
(7, 888, 788, 'BORO MANAS', 'Player', 'Men', NULL, 0),
(8, 888, 788, 'MISRI SAINYUM', 'Player', 'Men', NULL, 0),
(9, 888, 788, 'SINGH ARUN', 'Player', 'Men', NULL, 0),
(10, 885, 788, 'PATTANAIK RABI RANJAN', 'Player', 'Men', NULL, 0),
(11, 885, 788, 'MEENA SUNIL KUMAR', 'Player', 'Men', NULL, 0),
(12, 885, 788, 'NEWAR GYAN', 'Player', 'Men', NULL, 0),
(13, 885, 788, 'NAREN DAS', 'Player', 'Men', NULL, 0),
(14, 885, 788, 'KEDIA ANKITA KUMARI', 'Player', 'Women', NULL, 0),
(15, 885, 788, 'PRITIKA SONI', 'Player', 'Women', NULL, 0),
(16, 882, 788, 'KAMAN MRINMOY', 'Player', 'Men', NULL, 0),
(17, 882, 788, 'SIJOU SWRJEE', 'Player', 'Men', NULL, 0),
(18, 882, 788, 'KOTHA RAJEEV RATHAN KUMAR', 'Player', 'Men', NULL, 0),
(19, 882, 788, 'Pujyam Mishra', 'Player', 'Men', NULL, 0),
(20, 882, 788, 'ANURAG KUMAR', 'Player', 'Men', NULL, 0),
(21, 889, 788, 'RAVADA BALA PAVAN KALYAN', 'Player', 'Men', NULL, 0),
(22, 882, 788, 'PRARTHANA MISRI', 'Player', 'Women', NULL, 0),
(23, 882, 788, 'PRIYANKA SINGH', 'Player', 'Women', NULL, 0),
(24, 882, 788, 'KUMARI SWATI', 'Player', 'Women', NULL, 0),
(25, 887, 788, 'BHUTIA TASHI WANGYAL', 'Player', 'Men', NULL, 0),
(26, 887, 788, 'ROUT NIHAR RANJAN', 'Player', 'Men', NULL, 0),
(27, 887, 788, 'BHOWMICK KOUSHIK', 'Player', 'Men', NULL, 0),
(28, 886, 788, 'KOMA YATENDRA KUMAR', 'Player', 'Men', NULL, 0),
(29, 886, 788, 'ACHARYA DEEPAM', 'Player', 'Men', NULL, 0),
(30, 886, 788, 'ADRISH KAR', 'Player', 'Men', NULL, 0),
(31, 886, 788, 'DAS BISWANATH', 'Player', 'Men', NULL, 0),
(32, 882, 788, 'DASAR NARZARI', 'Team Manager', 'Men', NULL, 0),
(33, 893, 789, 'DEWANGAN BHUPENDRA', 'Captain', 'Men', NULL, 0),
(34, 893, 789, 'EESHAAN MITTAL', 'Player', 'Men', NULL, 0),
(35, 893, 789, 'PRIYANK PRAKASH', 'Player', 'Men', NULL, 0),
(36, 893, 789, 'RAY NEELAM SIDDHARTHA', 'Player', 'Women', NULL, 0),
(37, 891, 789, 'SACHIN YADAV', 'Captain', 'Men', NULL, 0),
(38, 891, 789, 'DIWAKAR KUMAR', 'Player', 'Men', NULL, 0),
(39, 891, 789, 'SURENDER SINGH KUMAR', 'Player', 'Men', NULL, 0),
(40, 891, 789, 'BANOTH SHIVAKUMAR', 'Player', 'Men', NULL, 0),
(41, 891, 789, 'ALAGU PRASANTH GNANASEKARAN', 'Player', 'Men', NULL, 0),
(42, 891, 789, 'JITENDRA SUMAN', 'Open Event', 'Men', NULL, 0),
(43, 895, 789, 'KAMBOJ SAKET', 'Captain', 'Men', NULL, 0),
(44, 895, 789, 'ASHUTOSH KUMAR', 'Player', 'Men', NULL, 0),
(45, 895, 789, 'MISHRA CHANDRA PRAKASH', 'Player', 'Men', NULL, 0),
(46, 895, 789, 'GEHANI JAIKUMAR VASUDEV', 'Player', 'Men', NULL, 0),
(47, 895, 789, 'S.Balachandar', 'Senior Leadership', 'Men', NULL, 0),
(48, 894, 789, 'TAMORE HARESHWAR', 'Captain', 'Men', NULL, 0),
(49, 894, 789, 'PATANKAR SATISH GANPAT', 'Player', 'Men', NULL, 0),
(50, 894, 789, 'MANGADE UMESH BABU', 'Player', 'Men', NULL, 0),
(51, 894, 789, 'VIVEK BHASKAR THAKARE', 'Player', 'Men', NULL, 0),
(52, 894, 789, 'NAGARE KALPANA', 'Captain', 'Women', NULL, 0),
(53, 894, 789, 'TEJASWINI KISHOR KENI', 'Player', 'Women', NULL, 0),
(54, 894, 789, 'CHOUDHARY SHUBHADA', 'Player', 'Women', NULL, 0),
(55, 894, 789, 'VINAYA UNDALE', 'Player', 'Women', NULL, 0),
(56, 897, 789, 'SARANGI PITABAS', 'Senior Leadership', 'Men', NULL, 0),
(57, 897, 789, 'ARUN JANARDHANAN', 'Player', 'Men', NULL, 0),
(58, 897, 789, 'THAKUR ASHISH SINGH', 'Captain', 'Men', NULL, 0),
(59, 897, 789, 'ANURAG SINGH', 'Player', 'Men', NULL, 0),
(60, 892, 789, 'N.THIRUNAVUKKARASU', 'Captain', 'Men', NULL, 0),
(61, 892, 789, 'CHAWLA GURDEEP SINGH', 'Player', 'Men', NULL, 0),
(62, 892, 789, 'ANIRBAN BISWAS', 'Player', 'Men', NULL, 0),
(63, 892, 789, 'AKEN ANILKUMAR RAMDAS', 'Player', 'Men', NULL, 0),
(64, 892, 789, 'ASHUTOSH SANDIP JADHAV', 'Player', 'Men', NULL, 0),
(65, 892, 789, 'B SRINIVASA GOPALA KRISHNA', 'Senior Leadership', 'Men', NULL, 0),
(66, 892, 789, 'DEBASHISH BASAK', 'Senior Leadership', 'Men', NULL, 0),
(67, 892, 789, 'BASAK MANDIRA', 'Captain', 'Women', NULL, 0),
(68, 892, 789, 'NIKAM VINITA VINAYAK', 'Player', 'Women', NULL, 0),
(69, 892, 789, 'SHREYA GUPTA', 'Player', 'Women', NULL, 0),
(70, 896, 789, 'PATEL GIRIRAJ KISHORE', 'Captain', 'Men', NULL, 0),
(71, 896, 789, 'PRABHAKARARAO KOPPISETTY', 'Player', 'Men', NULL, 0),
(72, 896, 789, 'SUNEET BATTY', 'Player', 'Men', NULL, 0),
(73, 891, 789, 'VIJAYANAND M RANE', 'Team Manager', 'Men', NULL, 0),
(74, 810, 780, 'BHIL MUKESH KUMAR', 'Player', 'Men', NULL, 0),
(75, 810, 780, 'SAHA RAHUL', 'Player', 'Men', NULL, 0),
(76, 810, 780, 'M KURIAKOSE JACOB', 'Player', 'Men', NULL, 0),
(77, 810, 780, 'YADAV MUKESH', 'Player', 'Men', NULL, 0),
(78, 810, 780, 'D GOPINATH', 'Player', 'Men', NULL, 0),
(79, 810, 780, 'SRINIVAS REDDY ALETI', 'Player', 'Men', NULL, 0),
(80, 810, 780, 'CH SRINIVAS', 'Player', 'Men', NULL, 0),
(81, 810, 780, 'BAFILA RAJINI DEEPAK', 'Player', 'Women', NULL, 0),
(82, 810, 780, 'BARA ARPITA KANAK', 'Player', 'Women', NULL, 0),
(83, 810, 780, 'BHAVIKA VERMA', 'Player', 'Women', NULL, 0),
(84, 817, 780, 'YASH SINGANIA', 'Player', 'Men', NULL, 0),
(85, 817, 780, 'POORNIMA', 'Player', 'Women', NULL, 0),
(86, 812, 780, 'RAMAN S S', 'Player', 'Men', NULL, 0),
(87, 812, 780, 'DEBJYOTI CHATTERJEE', 'Player', 'Men', NULL, 0),
(88, 812, 780, 'SAAYALI RAJENDRA LIMJE', 'Player', 'Women', NULL, 0),
(89, 812, 780, 'SHIKHARE ASHA SHARAD', 'Player', 'Women', NULL, 0),
(90, 813, 780, 'DASHARATH VAIKOLI', 'Player', 'Men', NULL, 0),
(91, 813, 780, 'MUKUNDE PRAKASH S', 'Player', 'Men', NULL, 0),
(92, 813, 780, 'MAHADIK SANJAY YASHWANT', 'Player', 'Men', NULL, 0),
(93, 813, 780, 'RAUL TUSHAR R', 'Player', 'Men', NULL, 0),
(94, 813, 780, 'GANU NEHA DINESH', 'Player', 'Women', NULL, 0),
(95, 813, 780, 'DHURI RASIKA SUSHIL', 'Player', 'Women', NULL, 0),
(96, 813, 780, 'TAPKE VANDANA AMRUT', 'Player', 'Women', NULL, 0),
(97, 813, 780, 'SAWANT SHOBHA RAJAN', 'Player', 'Women', NULL, 0),
(98, 814, 780, 'AGRAWAL ANSHUL MADHUSUDAN', 'Player', 'Men', NULL, 0),
(99, 814, 780, 'R. NARAYANAN', 'Player', 'Men', NULL, 0),
(100, 814, 780, 'GUPTA ASHISH', 'Player', 'Men', NULL, 0),
(101, 814, 780, 'ALAPATI CHAITANYA', 'Player', 'Men', NULL, 0),
(102, 816, 780, 'BANSAL PRABHJOT VISHAV VAS', 'Player', 'Men', NULL, 0),
(103, 816, 780, 'VANKUDOTH VIJAYKUMAR', 'Player', 'Men', NULL, 0),
(104, 811, 780, 'KOSTA ARUN', 'Player', 'Men', NULL, 0),
(105, 811, 780, 'PARMAR GAJANAN', 'Player', 'Men', NULL, 0),
(106, 811, 780, 'PAREKH PANKAJ PRAVINCHANDRA', 'Player', 'Men', NULL, 0),
(107, 811, 780, 'RAO SRINIVASA MARNI', 'Player', 'Men', NULL, 0),
(108, 811, 780, 'SINGH SHASHI KANT', 'Player', 'Men', NULL, 0),
(109, 811, 780, 'JASRAI MADHU', 'Player', 'Women', NULL, 0),
(110, 811, 780, 'TARUNA HEMANT KUMAR RATHOD', 'Player', 'Women', NULL, 0),
(111, 811, 780, 'BANDEKAR PRAGATI PRASAD', 'Player', 'Women', NULL, 0),
(112, 818, 780, 'DHAKER ROHIT', 'Player', 'Men', NULL, 0),
(113, 818, 780, 'Shilpa J Ramya', 'Player', 'Women', NULL, 0),
(114, 815, 780, 'HITESH PANIHAR', 'Player', 'Men', NULL, 0),
(115, 815, 780, 'SURAJ CHOTULAL SHAHU', 'Player', 'Men', NULL, 0),
(116, 815, 780, 'PANDEY KANIKA', 'Player', 'Women', NULL, 0),
(117, 815, 780, 'KAMAT NAMRATA', 'Player', 'Women', NULL, 0),
(118, 815, 780, 'NIDHI AGARWAL', 'Player', 'Women', NULL, 0),
(119, 810, 780, 'Tanaji Adate', 'Team Manager', 'Men', NULL, 0),
(120, 846, 784, 'GYAMBA SONAM', 'Player', 'Women', NULL, 0),
(121, 846, 784, 'KIRTDEEP KAUR', 'Player', 'Women', NULL, 0),
(122, 846, 784, 'CHAUHAN PRADEEP', 'Player', 'Men', NULL, 0),
(123, 846, 784, 'AKKAL VINOD KUMAR', 'Player', 'Men', NULL, 0),
(124, 846, 784, 'SHAIK ALTHAF', 'Player', 'Men', NULL, 0),
(125, 846, 784, 'DASH KAMAL KRUSHNA', 'Player', 'Men', NULL, 0),
(126, 846, 784, 'BOGALAGANI GANESH', 'Player', 'Men', NULL, 0),
(127, 848, 784, 'SHARMA NEERAJ', 'Player', 'Men', NULL, 0),
(128, 848, 784, 'SUNIL KUMAR', 'Player', 'Men', NULL, 0),
(129, 848, 784, 'GOYAL ROHIT', 'Player', 'Men', NULL, 0),
(130, 848, 784, 'TARACHAND', 'Player', 'Men', NULL, 0),
(131, 849, 784, 'SHORAJ SINGH', 'Player', 'Men', NULL, 0),
(132, 849, 784, 'RAMESH CHAND', 'Player', 'Men', NULL, 0),
(133, 849, 784, 'KHURANA DEEPAK', 'Player', 'Men', NULL, 0),
(134, 849, 784, 'P.V.K.N. APPA RAO', 'Player', 'Men', NULL, 0),
(135, 850, 784, 'PANDEY SANJAY KUMAR', 'Player', 'Men', NULL, 0),
(136, 850, 784, 'SACHIN CHAKRAVORTY', 'Player', 'Men', NULL, 0),
(137, 850, 784, 'VINAY KUMAR', 'Player', 'Men', NULL, 0),
(138, 850, 784, 'Kumar Varun', 'Player', 'Men', NULL, 0),
(139, 852, 784, 'NAIN SHRAVAN KUMAR', 'Player', 'Men', NULL, 0),
(140, 852, 784, 'PANGTEY DINESH', 'Player', 'Men', NULL, 0),
(141, 852, 784, 'SINGH SHAKTI', 'Player', 'Men', NULL, 0),
(142, 854, 784, 'RISHABH JAIN', 'Player', 'Men', NULL, 0),
(143, 851, 784, 'SAH BAIRISTER', 'Player', 'Men', NULL, 0),
(144, 851, 784, 'SHAH SHAHID LATEEF', 'Player', 'Men', NULL, 0),
(145, 847, 784, 'RAUT AJAY GAJANAN', 'Player', 'Men', NULL, 0),
(146, 847, 784, 'KUMAR NAVEEN', 'Player', 'Men', NULL, 0),
(147, 847, 784, 'NEHRA ABHISHEK', 'Player', 'Men', NULL, 0),
(148, 847, 784, 'BANSAL MANI', 'Player', 'Men', NULL, 0),
(149, 847, 784, 'HARSHIL ABHIJEET', 'Player', 'Men', NULL, 0),
(150, 864, 786, 'RAMESH PARMAR', 'Team Manager', 'Men', NULL, 0),
(151, 870, 786, 'RAJENDRAN V', 'Player', 'Men', NULL, 0),
(152, 870, 786, 'B SATHEESH KUMAR', 'Player', 'Men', NULL, 0),
(153, 864, 786, 'MEENA CHETAN PRAKASH', 'Player', 'Men', NULL, 0),
(154, 864, 786, 'MOHIT RAMEJA', 'Player', 'Men', NULL, 0),
(155, 864, 786, 'KHUSHAL MEENA', 'Player', 'Men', NULL, 0),
(156, 864, 786, 'PAWAR PRASAD MANOHAR', 'Captain', 'Men', NULL, 0),
(157, 864, 786, 'MEENA HEMENDRA KUMAR', 'Player', 'Men', NULL, 0),
(158, 864, 786, 'YADAV SWATI', 'Player', 'Women', NULL, 0),
(159, 864, 786, 'ANITA SHARMA', 'Player', 'Women', NULL, 0),
(160, 864, 786, 'BHUVANESHWARI', 'Player', 'Women', NULL, 0),
(161, 866, 786, 'MADHUKAR MANOJ KUMAR', 'Player', 'Men', NULL, 0),
(162, 866, 786, 'Sanjay Kumar Gupta', 'Player', 'Men', NULL, 0),
(163, 866, 786, 'NILESH MOHAN JAGTAP', 'Player', 'Men', NULL, 0),
(164, 866, 786, 'KAPIL DEV', 'Player', 'Men', NULL, 0),
(165, 867, 786, 'MEHTA DEVENDRAKUMAR P', 'Captain', 'Men', NULL, 0),
(166, 867, 786, 'MEHTA VIMAL AMRUTLAL', 'Player', 'Men', NULL, 0),
(167, 867, 786, 'MOHITE DHARMESH RAMRAO', 'Player', 'Men', NULL, 0),
(168, 867, 786, 'PATEL SANJAYKUMAR GOPALBHAI', 'Player', 'Men', NULL, 0),
(169, 867, 786, 'NIRMALA MARSHAL GONSALVES', 'Player', 'Women', NULL, 0),
(170, 867, 786, 'SHIVA NIGAM', 'Captain', 'Women', NULL, 0),
(171, 868, 786, 'BHATT SAURABH DIPAKKUMAR', 'Player', 'Men', NULL, 0),
(172, 868, 786, 'SINGH BHUPINDER', 'Player', 'Men', NULL, 0),
(173, 868, 786, 'BODDETI TULASI RAM', 'Captain', 'Men', NULL, 0),
(174, 868, 786, 'GIRI ANJANI KUMAR', 'Player', 'Men', NULL, 0),
(175, 870, 786, 'BISHNOI KRISHAN KUMAR', 'Player', 'Men', NULL, 0),
(176, 870, 786, 'KHANDELWAL VAIBHAV', 'Player', 'Men', NULL, 0),
(177, 870, 786, 'RAJ KUMAR', 'Captain', 'Men', NULL, 0),
(178, 865, 786, 'KUMAR ASHOK', 'Player', 'Men', NULL, 0),
(179, 865, 786, 'NIRANJAN ABHISHEK SINGH', 'Captain', 'Men', NULL, 0),
(180, 865, 786, 'DINKAR VAIBHAV', 'Player', 'Men', NULL, 0),
(181, 865, 786, 'PARPIYANI PIYUSH VINODBHAI', 'Player', 'Men', NULL, 0),
(182, 865, 786, 'AGARWAL MANISH', 'Player', 'Men', NULL, 0),
(183, 872, 786, 'CHOUHAN NAVEEN', 'Player', 'Men', NULL, 0),
(184, 871, 786, 'RAJ SUNNY', 'Player', 'Men', NULL, 0),
(185, 869, 786, 'CHANDNANI MAHESH K', 'Player', 'Men', NULL, 0),
(186, 869, 786, 'KUSHAGRA VASHISHTH', 'Player', 'Men', NULL, 0),
(187, 869, 786, 'MEENA MAHENDRA PRASAD', 'Captain', 'Men', NULL, 0),
(188, 855, 785, 'MD RAHISH ALAM', 'Player', 'Men', NULL, 0),
(189, 855, 785, 'VIVEK SINGH', 'Player', 'Men', NULL, 0),
(190, 858, 785, 'PADTE DINESH RAMCHANDRA', 'Player', 'Men', NULL, 0),
(191, 860, 785, 'THAKUR DEEPAK VASANT', 'Player', 'Men', NULL, 0),
(192, 862, 785, 'ROJIN ROBINSON', 'Player', 'Men', NULL, 0),
(193, 863, 785, 'VINAY GAUTAM', 'Player', 'Men', NULL, 0),
(194, 857, 785, 'MENDONSA MICHEAL PASCAL', 'Player', 'Men', NULL, 0),
(195, 857, 785, 'SHETTIGAR ASHOK PADMANABHA', 'Player', 'Men', NULL, 0),
(196, 857, 785, 'MUNDA ARUN SINGH', 'Player', 'Men', NULL, 0),
(197, 858, 785, 'ABHYANKAR ATUL YESHWANT', 'Player', 'Men', NULL, 0),
(198, 858, 785, 'DALVI JITENDRA LAXMAN', 'Player', 'Men', NULL, 0),
(199, 858, 785, 'PRAJAPATI KIRITKUMAR NAROTAM', 'Player', 'Men', NULL, 0),
(200, 859, 785, 'KHABIA RAMESHLAL ZUMBARLAL', 'Player', 'Men', NULL, 0),
(201, 859, 785, 'VISHNU', 'Player', 'Men', NULL, 0),
(202, 859, 785, 'SWARAJ VIBHOOSHAN PAI', 'Player', 'Men', NULL, 0),
(203, 856, 785, 'PANGE MILIND MOHAN', 'Player', 'Men', NULL, 0),
(204, 855, 785, 'DANGI MAHESH', 'Player', 'Men', NULL, 0),
(205, 861, 785, 'PRANAY RAHUL SHARMA', 'Player', 'Men', NULL, 0),
(206, 859, 785, 'NAYAK SHUBHAM', 'Player', 'Men', NULL, 0),
(207, 860, 785, 'ADSULE VISHWAS APPASAHEB', 'Player', 'Men', NULL, 0),
(208, 860, 785, 'PATHAK DIVYANG', 'Player', 'Men', NULL, 0),
(209, 856, 785, 'KUMAR MANISH', 'Player', 'Men', NULL, 0),
(210, 855, 785, 'DEEKSHA SRIVAS', 'Player', 'Women', NULL, 0),
(211, 855, 785, 'KOCHE SANSKRUTI', 'Player', 'Women', NULL, 0),
(212, 855, 785, 'PRIYA CHAUHAN', 'Player', 'Women', NULL, 0),
(213, 855, 785, 'DIVYA', 'Player', 'Women', NULL, 0),
(214, 860, 785, 'AISHWARYA DEVANAND LAKHE', 'Player', 'Women', NULL, 0),
(215, 860, 785, 'TISHA MEENA', 'Player', 'Women', NULL, 0),
(216, 856, 785, 'KUMAR SUJIT', 'Player', 'Men', NULL, 0),
(217, 855, 785, 'MORATHOTI GOPI KRISHNA', 'Player', 'Men', NULL, 0),
(218, 855, 785, 'WALA  SUKETU DHIRAJ', 'Player', 'Men', NULL, 0),
(219, 857, 785, 'SUNKARA RAMA CHANDRA RAO', 'Player', 'Men', NULL, 0),
(220, 856, 785, 'JAISWAL ABHISHEK', 'Player', 'Men', NULL, 0),
(221, 856, 785, 'BHARDWAJ PRIYANK', 'Player', 'Men', NULL, 0),
(222, 861, 785, 'VERMA DEEPAK KUMAR', 'Player', 'Men', NULL, 0),
(223, 861, 785, 'DAS ANKIT', 'Player', 'Men', NULL, 0),
(224, 861, 785, 'RAI NEERAJ KISHORE', 'Player', 'Men', NULL, 0),
(225, 861, 785, 'K. Thirumurugan', 'Player', 'Men', NULL, 0),
(226, 860, 785, 'Sunil Singh Yadav', 'Player', 'Men', NULL, 0),
(227, 873, 787, 'CHIRUMAMILLA G V S R K PRASAD', 'Player', 'Men', NULL, 0),
(228, 873, 787, 'KONDAGORRI KRANTI KUMAR', 'Player', 'Men', NULL, 0),
(229, 873, 787, 'ABHINAV VUDDAGIRI', 'Player', 'Men', NULL, 0),
(230, 873, 787, 'MOOKERJEE INDRAJIT', 'Player', 'Men', NULL, 0),
(231, 873, 787, 'BANDARU CHAITANYA VARAHA SAI RAM', 'Player', 'Men', NULL, 0),
(232, 873, 787, 'PARAVASTU VINUTHA', 'Player', 'Women', NULL, 0),
(233, 873, 787, 'KIRAN KUMARI', 'Player', 'Women', NULL, 0),
(234, 875, 787, 'AJAY A', 'Player', 'Men', NULL, 0),
(235, 875, 787, 'PAIDIPAMULA VEERENDRA BABU', 'Player', 'Men', NULL, 0),
(236, 875, 787, 'GOTE VINOD BHAURAOJI', 'Player', 'Men', NULL, 0),
(237, 875, 787, 'DAS AMITAVA', 'Player', 'Men', NULL, 0),
(238, 876, 787, 'P V K BHASKAR', 'Player', 'Men', NULL, 0),
(239, 876, 787, 'VENUGOPAL P', 'Player', 'Men', NULL, 0),
(240, 876, 787, 'LIONEL KENNETH JOHN', 'Player', 'Men', NULL, 0),
(241, 876, 787, 'SANTHOSH KUMAR S SHETTY', 'Player', 'Men', NULL, 0),
(242, 876, 787, 'V HAMSAVENI', 'Player', 'Women', NULL, 0),
(243, 876, 787, 'GUPTA SAUMYA', 'Player', 'Women', NULL, 0),
(244, 876, 787, 'PUVVALA SAI SUDHAMAYEE', 'Player', 'Women', NULL, 0),
(245, 876, 787, 'VIDYA VIJAY SHINDE', 'Player', 'Women', NULL, 0),
(246, 877, 787, 'SRIMANTHULA VENKATA SAI DHEERENDRA', 'Player', 'Men', NULL, 0),
(247, 877, 787, 'SNEHA BISHT', 'Player', 'Women', NULL, 0),
(248, 877, 787, 'GOLAP DAS', 'Player', 'Men', NULL, 0),
(249, 877, 787, 'GUPTA RAHUL', 'Player', 'Men', NULL, 0),
(250, 879, 787, 'BHUPATI MURALI KRISHNA', 'Player', 'Men', NULL, 0),
(251, 879, 787, 'SINGH NITIN CHANDRA', 'Player', 'Men', NULL, 0),
(252, 879, 787, 'SINGH SUKHWINDER', 'Player', 'Men', NULL, 0),
(253, 879, 787, 'SHALU PANDEY', 'Player', 'Women', NULL, 0),
(254, 880, 787, 'NIMISHAKAVI VENKATA RAMA CHITRA', 'Player', 'Women', NULL, 0),
(255, 880, 787, 'ABHISHEK SAGAR', 'Player', 'Men', NULL, 0),
(256, 881, 787, 'POLISETTY SAI MOHAN', 'Player', 'Men', NULL, 0),
(257, 881, 787, 'PERABATHULA SATYA MANIKYAM', 'Player', 'Women', NULL, 0),
(258, 878, 787, 'BATNA RAJASEKHAR', 'Player', 'Men', NULL, 0),
(259, 878, 787, 'MADEM LAXMI NAGA SRINIVAS', 'Player', 'Men', NULL, 0),
(260, 878, 787, 'ANAND RUPAK', 'Player', 'Men', NULL, 0),
(261, 878, 787, 'PRIYANKA', 'Player', 'Women', NULL, 0),
(262, 874, 787, 'KUMAR NAGMANI', 'Player', 'Men', NULL, 0),
(263, 874, 787, 'DHULKHED PRASAD SHRIPATI', 'Player', 'Men', NULL, 0),
(264, 874, 787, 'ASTHANA VATSAL', 'Player', 'Men', NULL, 0),
(265, 874, 787, 'GULATI ANKUR', 'Player', 'Men', NULL, 0),
(266, 874, 787, 'SWAMY MAHADEV H S', 'Player', 'Men', NULL, 0),
(267, 873, 787, 'ANIL KUMAR PALAKALURI', 'Team Manager', 'Men', NULL, 0),
(268, 828, 782, 'GOKULNATH M', 'Player', 'Men', NULL, 0),
(269, 828, 782, 'PRASHANTH PITTA', 'Player', 'Men', NULL, 0),
(270, 828, 782, 'D G KIRAN', 'Player', 'Men', NULL, 0),
(271, 828, 782, 'P S KATHIRVEL', 'Player', 'Men', NULL, 0),
(272, 828, 782, 'KISHAN KUMAR CHALLA', 'Player', 'Men', NULL, 0),
(273, 835, 782, 'SHRIDHAR DWIVEDI', 'Player', 'Men', NULL, 0),
(274, 834, 782, 'R.SELLA PRABU', 'Player', 'Men', NULL, 0),
(275, 834, 782, 'G GOUTHAM', 'Player', 'Men', NULL, 0),
(276, 834, 782, 'KIRAN KUMAR VARANASI', 'Player', 'Men', NULL, 0),
(277, 832, 782, 'C SARAVANA PERUMAL', 'Player', 'Men', NULL, 0),
(278, 832, 782, 'H EASWARA IYER', 'Player', 'Men', NULL, 0),
(279, 832, 782, 'GIRDONIA SACHIN KUMAR', 'Player', 'Men', NULL, 0),
(280, 832, 782, 'SANAL KUMAR MA', 'Player', 'Men', NULL, 0),
(281, 831, 782, 'K WILLIAMS', 'Player', 'Men', NULL, 0),
(282, 831, 782, 'G SEKARBABU', 'Player', 'Men', NULL, 0),
(283, 831, 782, 'SHARMA SHARAD', 'Player', 'Men', NULL, 0),
(284, 831, 782, 'PONNAPATI RATHNAKAR BABU', 'Player', 'Men', NULL, 0),
(285, 829, 782, 'R SUBHASH CHANDRA', 'Player', 'Men', NULL, 0),
(286, 829, 782, 'B PRABHU', 'Player', 'Men', NULL, 0),
(287, 829, 782, 'BSV HARSHAVARDHANA HEMANTH', 'Player', 'Men', NULL, 0),
(288, 829, 782, 'S SHANKAR', 'Player', 'Men', NULL, 0),
(289, 829, 782, 'D ANILKUMAR', 'Player', 'Men', NULL, 0),
(290, 828, 782, 'ANISHA THOTTEMPUDI', 'Player', 'Women', NULL, 0),
(291, 828, 782, 'P NEENA', 'Player', 'Women', NULL, 0),
(292, 835, 782, 'K R RANJANI', 'Player', 'Women', NULL, 0),
(293, 833, 782, 'ADHARI SOMAIAH', 'Player', 'Men', NULL, 0),
(294, 833, 782, 'BIJEESH PULIYASSERY', 'Player', 'Men', NULL, 0),
(295, 828, 782, 'U SAMBASIVAM', 'Team Manager', 'Men', NULL, 0),
(296, 792, 778, 'V B ANEESH', 'Player', 'Men', NULL, 0),
(297, 792, 778, 'K S DEEPAK', 'Player', 'Men', NULL, 0),
(298, 792, 778, 'SAI NISCHAL DEV', 'Player', 'Men', NULL, 0),
(299, 792, 778, 'SVS DURGA PRASAD', 'Player', 'Men', NULL, 0),
(300, 792, 778, 'K.SUNEEL', 'Player', 'Men', NULL, 0),
(301, 792, 778, 'TEJASWINI DEVI BHIMIREDDY', 'Player', 'Women', NULL, 0),
(302, 792, 778, 'P MEERA', 'Player', 'Women', NULL, 0),
(303, 794, 778, 'K N SATYANARAYANA', 'Player', 'Men', NULL, 0),
(304, 794, 778, 'CH NAGA CHAITANYA', 'Player', 'Men', NULL, 0),
(305, 794, 778, 'PUTREVU RAMAKRISHNA MURTY', 'Player', 'Men', NULL, 0),
(306, 794, 778, 'Ch SOMA SEKHARA BABU', 'Player', 'Men', NULL, 0),
(307, 795, 778, 'M LINGA SWAMY', 'Player', 'Men', NULL, 0),
(308, 795, 778, 'BORA GANAPATHI RAO', 'Player', 'Men', NULL, 0),
(309, 795, 778, 'G APPALA RAJU', 'Player', 'Men', NULL, 0),
(310, 795, 778, 'I RAMA RAO', 'Player', 'Men', NULL, 0),
(311, 795, 778, 'RETHUVARNA N K', 'Player', 'Women', NULL, 0),
(312, 795, 778, 'ANUSREE T P', 'Player', 'Women', NULL, 0),
(313, 796, 778, 'BEESETTI VENKATA SAI AKHIL', 'Player', 'Men', NULL, 0),
(314, 796, 778, 'KONERU CHARAN KUMAR', 'Player', 'Men', NULL, 0),
(315, 796, 778, 'M MURUGHESH', 'Player', 'Men', NULL, 0),
(316, 796, 778, 'M SANTHOSH KRISHNA', 'Player', 'Men', NULL, 0),
(317, 797, 778, 'PUNIT DESWAL', 'Player', 'Men', NULL, 0),
(318, 797, 778, 'HEMANTH KUMAR', 'Player', 'Men', NULL, 0),
(319, 797, 778, 'S KHARE PRASANNA', 'Player', 'Men', NULL, 0),
(320, 793, 778, 'ANUP TOPPO', 'Player', 'Men', NULL, 0),
(321, 793, 778, 'EESWAR CHAITANYA BATTULA', 'Player', 'Men', NULL, 0),
(322, 793, 778, 'SATISH KUMAR KARRI', 'Player', 'Men', NULL, 0),
(323, 793, 778, 'MOHAN KUMAR PACHILA', 'Player', 'Men', NULL, 0),
(324, 793, 778, 'M RAFI ALAM', 'Player', 'Men', NULL, 0),
(325, 793, 778, 'D BALA TRIPURA SUNDARI DEVI', 'Player', 'Women', NULL, 0),
(326, 793, 778, 'NAVODITA KANDARI', 'Player', 'Women', NULL, 0),
(327, 798, 778, 'PONNAGANTI RAVI', 'Player', 'Men', NULL, 0),
(328, 798, 778, 'K RISHIKESWAR', 'Player', 'Men', NULL, 0),
(329, 798, 778, 'SANJEEV RAJAK', 'Player', 'Men', NULL, 0),
(330, 798, 778, 'KIRAN KUMAR GANTA', 'Player', 'Men', NULL, 0),
(331, 799, 778, 'GANDU AKHIL', 'Player', 'Men', NULL, 0),
(332, 799, 778, 'MUNMUN KUMARI', 'Player', 'Women', NULL, 0),
(333, 800, 778, 'SALAPU VARDHAN', 'Player', 'Men', NULL, 0),
(334, 792, 778, 'P VENKATAPATHI RAJU', 'Team Manager', 'Men', NULL, 0),
(335, 837, 783, 'SRIVASTAVA SHIKHAR', 'Player', 'Men', NULL, 0),
(336, 837, 783, 'GUPTA SAMIR', 'Player', 'Men', NULL, 0),
(337, 837, 783, 'PAL GAURAV', 'Player', 'Men', NULL, 0),
(338, 837, 783, 'DHANISHTH RAMESH PAWAR', 'Player', 'Men', NULL, 0),
(339, 837, 783, 'CHAITANYA NALLA', 'Player', 'Men', NULL, 0),
(340, 844, 783, 'SEJAL SINGH', 'Player', 'Women', NULL, 0),
(341, 844, 783, 'KUMAR MANGLAM', 'Player', 'Men', NULL, 0),
(342, 838, 783, 'SAHU MAHENDRA KUMAR', 'Player', 'Men', NULL, 0),
(343, 838, 783, 'MEENA HARMUKH', 'Player', 'Men', NULL, 0),
(344, 838, 783, 'SAXENA UMESH CHANDRA', 'Player', 'Men', NULL, 0),
(345, 838, 783, 'RAO SHAILENDRA', 'Player', 'Men', NULL, 0),
(346, 838, 783, 'SHINDE PRATIDNYA RAVINDRA', 'Player', 'Women', NULL, 0),
(347, 838, 783, 'Mehak', 'Player', 'Women', NULL, 0),
(348, 845, 783, 'NIBIR BORA', 'Player', 'Men', NULL, 0),
(349, 840, 783, 'MORAVAKAR MANOHAR ANANT', 'Player', 'Men', NULL, 0),
(350, 840, 783, 'VICKY AGRAWAL', 'Player', 'Men', NULL, 0),
(351, 840, 783, 'RANE MANISH PANDURANG', 'Player', 'Men', NULL, 0),
(352, 840, 783, 'UDAWANT ASHISH MUKUNDRAO', 'Player', 'Men', NULL, 0),
(353, 840, 783, 'SUVARNA SHOBHA SHAILESH', 'Player', 'Women', NULL, 0),
(354, 840, 783, 'PRERNA BHARTI', 'Player', 'Women', NULL, 0),
(355, 840, 783, 'TWINA NADKARNI', 'Player', 'Women', NULL, 0),
(356, 843, 783, 'RAJPAL SUNNY', 'Player', 'Men', NULL, 0),
(357, 843, 783, 'MANAV PURI', 'Player', 'Men', NULL, 0),
(358, 843, 783, 'BHIMANENI SRIKANTH', 'Player', 'Men', NULL, 0),
(359, 842, 783, 'RAJ PUNEET', 'Player', 'Men', NULL, 0),
(360, 842, 783, 'DIXIT RAHUL', 'Player', 'Men', NULL, 0),
(361, 842, 783, 'NIGAM AMEET', 'Player', 'Men', NULL, 0),
(362, 841, 783, 'ANKIT', 'Player', 'Men', NULL, 0),
(363, 841, 783, 'REHAN FAHMID', 'Player', 'Men', NULL, 0),
(364, 841, 783, 'SONAWANE GULAB BALKRUSHNA', 'Player', 'Men', NULL, 0),
(365, 841, 783, 'CHIMMALAGI MARULASWAMI', 'Player', 'Men', NULL, 0),
(366, 839, 783, 'NARAYANE KISHORKUMAR SUDAM', 'Player', 'Men', NULL, 0),
(367, 839, 783, 'BHARUKA VINEET', 'Player', 'Men', NULL, 0),
(368, 839, 783, 'MAZGAONKAR PRADNYAN MAHADEV', 'Player', 'Men', NULL, 0),
(369, 839, 783, 'KUDTARKAR DINESH S', 'Player', 'Men', NULL, 0),
(370, 837, 783, 'NITIN T JADHAV', 'Team Manager', 'Men', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `points_scheme`
--

CREATE TABLE `points_scheme` (
  `id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `label` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `points_scheme`
--

INSERT INTO `points_scheme` (`id`, `position`, `points`, `label`) VALUES
(1, 1, 5, 'Winner'),
(2, 2, 3, 'Runner-up'),
(3, 3, 1, 'Second Runner-up');

-- --------------------------------------------------------

--
-- Table structure for table `score_formats`
--

CREATE TABLE `score_formats` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) NOT NULL,
  `game_id` int(11) DEFAULT NULL,
  `type` enum('sets','periods','points','custom') NOT NULL DEFAULT 'sets',
  `columns_json` text NOT NULL,
  `total_columns` int(11) NOT NULL DEFAULT 3,
  `win_rule` varchar(50) NOT NULL DEFAULT 'most_games',
  `target_wins` int(11) NOT NULL DEFAULT 2,
  `points_to_win` int(11) NOT NULL DEFAULT 21,
  `has_serving` tinyint(1) NOT NULL DEFAULT 1,
  `badge_text` varchar(50) NOT NULL DEFAULT 'Best of 3',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `score_formats`
--

INSERT INTO `score_formats` (`id`, `name`, `code`, `game_id`, `type`, `columns_json`, `total_columns`, `win_rule`, `target_wins`, `points_to_win`, `has_serving`, `badge_text`, `description`, `created_at`) VALUES
(1, 'Table Tennis Men (Best of 5)', 'tt_mens_bo5', 2, 'sets', '[{\"key\":\"g1\",\"label\":\"Game 1\",\"short\":\"G1\",\"max\":40},{\"key\":\"g2\",\"label\":\"Game 2\",\"short\":\"G2\",\"max\":40},{\"key\":\"g3\",\"label\":\"Game 3\",\"short\":\"G3\",\"max\":40},{\"key\":\"g4\",\"label\":\"Game 4\",\"short\":\"G4\",\"max\":40},{\"key\":\"g5\",\"label\":\"Game 5\",\"short\":\"G5\",\"max\":40}]', 5, 'most_games', 3, 11, 1, 'Best of 5', 'Official 5-game format for Men\'s team event. First team to win 3 games wins.', '2026-10-08 23:34:58'),
(2, 'Table Tennis Women (Best of 3)', 'tt_womens_bo3', 2, 'sets', '[{\"key\":\"g1\",\"label\":\"Game 1\",\"short\":\"G1\",\"max\":40},{\"key\":\"g2\",\"label\":\"Game 2\",\"short\":\"G2\",\"max\":40},{\"key\":\"g3\",\"label\":\"Game 3\",\"short\":\"G3\",\"max\":40}]', 3, 'most_games', 2, 11, 1, 'Best of 3', 'Official 3-game format for Women\'s team event. First team to win 2 games wins.', '2026-10-08 23:34:58'),
(3, 'Badminton (Best of 3 to 21 Pts)', 'badminton_bo3', 1, 'sets', '[{\"key\":\"g1\",\"label\":\"Game 1\",\"short\":\"G1\",\"max\":40},{\"key\":\"g2\",\"label\":\"Game 2\",\"short\":\"G2\",\"max\":40},{\"key\":\"g3\",\"label\":\"Game 3\",\"short\":\"G3\",\"max\":40}]', 3, 'most_games', 2, 21, 1, 'Best of 3', 'Standard 3-game format to 21 points with deuce up to 30.', '2026-10-08 23:34:58'),
(4, 'Lawn Tennis (Best of 3 Sets)', 'tennis_bo3', 782, 'sets', '[{\"key\":\"s1\",\"label\":\"Set 1\",\"short\":\"S1\",\"max\":7},{\"key\":\"s2\",\"label\":\"Set 2\",\"short\":\"S2\",\"max\":7},{\"key\":\"s3\",\"label\":\"Set 3\",\"short\":\"S3\",\"max\":7}]', 3, 'most_games', 2, 6, 1, 'Best of 3 Sets', '3 sets with advantage game scoring and tiebreaks.', '2026-10-08 23:34:58'),
(5, 'Custom 4 Quarters / Periods', 'periods_4q', NULL, 'periods', '[{\"key\":\"q1\",\"label\":\"Quarter 1\",\"short\":\"Q1\",\"max\":99},{\"key\":\"q2\",\"label\":\"Quarter 2\",\"short\":\"Q2\",\"max\":99},{\"key\":\"q3\",\"label\":\"Quarter 3\",\"short\":\"Q3\",\"max\":99},{\"key\":\"q4\",\"label\":\"Quarter 4\",\"short\":\"Q4\",\"max\":99}]', 4, 'total_score', 0, 0, 0, '4 Quarters', 'Cumulative 4-quarter / period scoring with total score summation.', '2026-10-08 23:34:58');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_key` varchar(64) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('leaderboard_mode', 'auto', '2026-10-09 01:31:41');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `seed` int(11) DEFAULT NULL,
  `pool` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `unit_id`, `game_id`, `name`, `seed`, `pool`, `created_at`) VALUES
(792, 778, 1, 'VR Badminton', 1, 'B', '2026-09-30 09:19:08'),
(793, 778, 2, 'VR Table Tennis', NULL, 'B', '2026-09-30 09:19:08'),
(794, 778, 778, 'VR Bridge', NULL, 'B', '2026-09-30 09:19:08'),
(795, 778, 779, 'VR Carrom', NULL, 'A', '2026-09-30 09:19:08'),
(796, 778, 780, 'VR Chess', NULL, NULL, '2026-09-30 09:19:08'),
(797, 778, 781, 'VR Swimming', NULL, 'B', '2026-09-30 09:19:08'),
(798, 778, 782, 'VR Tennis', NULL, 'A', '2026-09-30 09:19:08'),
(799, 778, 783, 'VR Badminton - Open Category', NULL, 'B', '2026-09-30 09:19:08'),
(800, 778, 784, 'VR Table Tennis - Open Category', NULL, 'B', '2026-09-30 09:19:08'),
(801, 779, 1, 'NCZ Badminton', 6, 'A', '2026-09-30 09:19:30'),
(802, 779, 2, 'NCZ Table Tennis', NULL, 'B', '2026-09-30 09:19:30'),
(803, 779, 778, 'NCZ Bridge', NULL, 'B', '2026-09-30 09:19:30'),
(804, 779, 779, 'NCZ Carrom', NULL, 'B', '2026-09-30 09:19:30'),
(805, 779, 780, 'NCZ Chess', NULL, NULL, '2026-09-30 09:19:30'),
(806, 779, 781, 'NCZ Swimming', NULL, 'B', '2026-09-30 09:19:30'),
(807, 779, 782, 'NCZ Tennis', NULL, 'D', '2026-09-30 09:19:30'),
(808, 779, 783, 'NCZ Badminton - Open Category', NULL, 'B', '2026-09-30 09:19:30'),
(809, 779, 784, 'NCZ Table Tennis - Open Category', NULL, 'B', '2026-09-30 09:19:30'),
(810, 780, 1, 'MF Badminton', 6, 'B', '2026-09-30 09:19:47'),
(811, 780, 2, 'MF Table Tennis', NULL, 'A', '2026-09-30 09:19:47'),
(812, 780, 778, 'MF Bridge', NULL, 'A', '2026-09-30 09:19:47'),
(813, 780, 779, 'MF Carrom', NULL, 'D', '2026-09-30 09:19:47'),
(814, 780, 780, 'MF Chess', NULL, NULL, '2026-09-30 09:19:47'),
(815, 780, 781, 'MF Swimming', NULL, 'B', '2026-09-30 09:19:47'),
(816, 780, 782, 'MF Tennis', NULL, 'C', '2026-09-30 09:19:47'),
(817, 780, 783, 'MF Badminton - Open Category', NULL, 'B', '2026-09-30 09:19:47'),
(818, 780, 784, 'MF Table Tennis - Open Category', NULL, 'B', '2026-09-30 09:19:47'),
(819, 781, 1, 'PH Badminton', 4, 'A', '2026-09-30 09:20:02'),
(820, 781, 2, 'PH Table Tennis', NULL, 'B', '2026-09-30 09:20:02'),
(821, 781, 778, 'PH Bridge', NULL, 'B', '2026-09-30 09:20:02'),
(822, 781, 779, 'PH Carrom', NULL, 'A', '2026-09-30 09:20:02'),
(823, 781, 780, 'PH Chess', NULL, NULL, '2026-09-30 09:20:02'),
(824, 781, 781, 'PH Swimming', NULL, 'B', '2026-09-30 09:20:02'),
(825, 781, 782, 'PH Tennis', NULL, 'D', '2026-09-30 09:20:02'),
(826, 781, 783, 'PH Badminton - Open Category', NULL, 'A', '2026-09-30 09:20:02'),
(827, 781, 784, 'PH Table Tennis - Open Category', NULL, 'A', '2026-09-30 09:20:02'),
(828, 782, 1, 'SZ Badminton', 3, 'B', '2026-09-30 09:20:19'),
(829, 782, 2, 'SZ Table Tennis', NULL, 'A', '2026-09-30 09:20:19'),
(830, 782, 778, 'SZ Bridge', NULL, NULL, '2026-09-30 09:20:19'),
(831, 782, 779, 'SZ Carrom', NULL, 'C', '2026-09-30 09:20:19'),
(832, 782, 780, 'SZ Chess', NULL, NULL, '2026-09-30 09:20:19'),
(833, 782, 781, 'SZ Swimming', NULL, 'B', '2026-09-30 09:20:19'),
(834, 782, 782, 'SZ Tennis', NULL, 'A', '2026-09-30 09:20:19'),
(835, 782, 783, 'SZ Badminton - Open Category', NULL, 'B', '2026-09-30 09:20:19'),
(836, 782, 784, 'SZ Table Tennis - Open Category', NULL, 'B', '2026-09-30 09:20:19'),
(837, 783, 1, 'WZ Badminton', 2, 'A', '2026-09-30 09:20:41'),
(838, 783, 2, 'WZ Table Tennis', NULL, 'A', '2026-09-30 09:20:41'),
(839, 783, 778, 'WZ Bridge', NULL, 'A', '2026-09-30 09:20:41'),
(840, 783, 779, 'WZ Carrom', NULL, 'C', '2026-09-30 09:20:41'),
(841, 783, 780, 'WZ Chess', NULL, NULL, '2026-09-30 09:20:41'),
(842, 783, 781, 'WZ Swimming', NULL, 'B', '2026-09-30 09:20:41'),
(843, 783, 782, 'WZ Tennis', NULL, 'B', '2026-09-30 09:20:41'),
(844, 783, 783, 'WZ Badminton - Open Category', NULL, 'B', '2026-09-30 09:20:41'),
(845, 783, 784, 'WZ Table Tennis - Open Category', NULL, 'B', '2026-09-30 09:20:41'),
(846, 784, 1, 'NZ Badminton', 3, 'A', '2026-09-30 09:20:56'),
(847, 784, 2, 'NZ Table Tennis', NULL, 'A', '2026-09-30 09:20:56'),
(848, 784, 778, 'NZ Bridge', NULL, 'B', '2026-09-30 09:20:56'),
(849, 784, 779, 'NZ Carrom', NULL, 'B', '2026-09-30 09:20:56'),
(850, 784, 780, 'NZ Chess', NULL, NULL, '2026-09-30 09:20:56'),
(851, 784, 781, 'NZ Swimming', NULL, 'B', '2026-09-30 09:20:56'),
(852, 784, 782, 'NZ Tennis', NULL, 'B', '2026-09-30 09:20:56'),
(853, 784, 783, 'NZ Badminton - Open Category', NULL, 'A', '2026-09-30 09:20:56'),
(854, 784, 784, 'NZ Table Tennis - Open Category', NULL, 'A', '2026-09-30 09:20:56'),
(855, 785, 1, 'MR Badminton', 1, 'A', '2026-09-30 09:21:13'),
(856, 785, 2, 'MR Table Tennis', NULL, 'A', '2026-09-30 09:21:13'),
(857, 785, 778, 'MR Bridge', NULL, 'A', '2026-09-30 09:21:13'),
(858, 785, 779, 'MR Carrom', NULL, 'B', '2026-09-30 09:21:13'),
(859, 785, 780, 'MR Chess', NULL, NULL, '2026-09-30 09:21:13'),
(860, 785, 781, 'MR Swimming', NULL, 'B', '2026-09-30 09:21:13'),
(861, 785, 782, 'MR Tennis', NULL, 'A', '2026-09-30 09:21:13'),
(862, 785, 783, 'MR Badminton - Open Category', NULL, 'B', '2026-09-30 09:21:13'),
(863, 785, 784, 'MR Table Tennis - Open Category', NULL, 'B', '2026-09-30 09:21:13'),
(864, 786, 1, 'NWZ Badminton', 5, 'B', '2026-09-30 09:21:33'),
(865, 786, 2, 'NWZ Table Tennis', NULL, 'B', '2026-09-30 09:21:33'),
(866, 786, 778, 'NWZ Bridge', NULL, 'A', '2026-09-30 09:21:33'),
(867, 786, 779, 'NWZ Carrom', NULL, 'C', '2026-09-30 09:21:33'),
(868, 786, 780, 'NWZ Chess', NULL, NULL, '2026-09-30 09:21:33'),
(869, 786, 781, 'NWZ Swimming', NULL, 'B', '2026-09-30 09:21:33'),
(870, 786, 782, 'NWZ Tennis', NULL, 'B', '2026-09-30 09:21:33'),
(871, 786, 783, 'NWZ Badminton - Open Category', NULL, 'A', '2026-09-30 09:21:33'),
(872, 786, 784, 'NWZ Table Tennis - Open Category', NULL, 'A', '2026-09-30 09:21:33'),
(873, 787, 1, 'SCZ Badminton', 2, 'B', '2026-09-30 09:22:00'),
(874, 787, 2, 'SCZ Table Tennis', NULL, 'A', '2026-09-30 09:22:00'),
(875, 787, 778, 'SCZ Bridge', NULL, 'A', '2026-09-30 09:22:00'),
(876, 787, 779, 'SCZ Carrom', NULL, 'D', '2026-09-30 09:22:00'),
(877, 787, 780, 'SCZ Chess', NULL, NULL, '2026-09-30 09:22:00'),
(878, 787, 781, 'SCZ Swimming', NULL, 'B', '2026-09-30 09:22:00'),
(879, 787, 782, 'SCZ Tennis', NULL, 'C', '2026-09-30 09:22:00'),
(880, 787, 783, 'SCZ Badminton - Open Category', NULL, 'A', '2026-09-30 09:22:00'),
(881, 787, 784, 'SCZ Table Tennis - Open Category', NULL, 'A', '2026-09-30 09:22:00'),
(882, 788, 1, 'EZ Badminton', 5, 'A', '2026-09-30 09:22:19'),
(883, 788, 2, 'EZ Table Tennis', NULL, 'B', '2026-09-30 09:22:19'),
(884, 788, 778, 'EZ Bridge', NULL, NULL, '2026-09-30 09:22:19'),
(885, 788, 779, 'EZ Carrom', NULL, 'A', '2026-09-30 09:22:19'),
(886, 788, 780, 'EZ Chess', NULL, NULL, '2026-09-30 09:22:19'),
(887, 788, 781, 'EZ Swimming', NULL, 'B', '2026-09-30 09:22:19'),
(888, 788, 782, 'EZ Tennis', NULL, 'D', '2026-09-30 09:22:19'),
(889, 788, 783, 'EZ Badminton - Open Category', NULL, 'A', '2026-09-30 09:22:19'),
(890, 788, 784, 'EZ Table Tennis - Open Category', NULL, 'A', '2026-09-30 09:22:19'),
(891, 789, 1, 'HB Badminton', 4, 'B', '2026-09-30 09:22:36'),
(892, 789, 2, 'HB Table Tennis', NULL, 'B', '2026-09-30 09:22:36'),
(893, 789, 778, 'HB Bridge', NULL, 'B', '2026-09-30 09:22:36'),
(894, 789, 779, 'HB Carrom', NULL, 'D', '2026-09-30 09:22:36'),
(895, 789, 780, 'HB Chess', NULL, NULL, '2026-09-30 09:22:36'),
(896, 789, 781, 'HB Swimming', NULL, 'B', '2026-09-30 09:22:36'),
(897, 789, 782, 'HB Tennis', NULL, 'C', '2026-09-30 09:22:36'),
(898, 789, 783, 'HB Badminton - Open Category', NULL, 'A', '2026-09-30 09:22:36'),
(899, 789, 784, 'HB Table Tennis - Open Category', NULL, 'A', '2026-09-30 09:22:36');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `short_code` varchar(10) NOT NULL,
  `color_code` varchar(20) DEFAULT '#0d6efd',
  `logo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `manual_rank` int(11) DEFAULT NULL,
  `manual_points` int(11) NOT NULL DEFAULT 0,
  `manual_gold` int(11) NOT NULL DEFAULT 0,
  `manual_silver` int(11) NOT NULL DEFAULT 0,
  `manual_bronze` int(11) NOT NULL DEFAULT 0,
  `manual_notes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `name`, `short_code`, `color_code`, `logo_path`, `created_at`, `manual_rank`, `manual_points`, `manual_gold`, `manual_silver`, `manual_bronze`, `manual_notes`) VALUES
(778, 'Vizag refinery', 'VR', '#0d6efd', NULL, '2026-09-30 09:19:08', NULL, 0, 0, 0, 0, NULL),
(779, 'North Central Zone', 'NCZ', '#fd0d0d', NULL, '2026-09-30 09:19:30', NULL, 0, 0, 0, 0, NULL),
(780, 'Marathon', 'MF', '#fd0ddd', NULL, '2026-09-30 09:19:47', NULL, 0, 0, 0, 0, NULL),
(781, 'Petroleum House', 'PH', '#910dfd', NULL, '2026-09-30 09:20:02', NULL, 0, 0, 0, 0, NULL),
(782, 'South Zone', 'SZ', '#610dfd', NULL, '2026-09-30 09:20:19', NULL, 0, 0, 0, 0, NULL),
(783, 'West Zone', 'WZ', '#0db5fd', NULL, '2026-09-30 09:20:41', NULL, 0, 0, 0, 0, NULL),
(784, 'North Zone', 'NZ', '#0df9fd', NULL, '2026-09-30 09:20:56', NULL, 0, 0, 0, 0, NULL),
(785, 'Mumbai Refinery', 'MR', '#0dfdcd', NULL, '2026-09-30 09:21:13', NULL, 0, 0, 0, 0, NULL),
(786, 'North West Zone', 'NWZ', '#0dfda1', NULL, '2026-09-30 09:21:33', NULL, 0, 0, 0, 0, NULL),
(787, 'South Central Zone', 'SCZ', '#0dfd3d', NULL, '2026-09-30 09:22:00', NULL, 0, 0, 0, 0, NULL),
(788, 'East Zone', 'EZ', '#d5fd0d', NULL, '2026-09-30 09:22:19', NULL, 0, 0, 0, 0, NULL),
(789, 'Hindustan Bhawan', 'HB', '#fd8d0d', NULL, '2026-09-30 09:22:36', NULL, 0, 0, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','nodal','volunteer','photographer') NOT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `unit_id`, `created_at`) VALUES
(1, 'admin', '$2y$10$0FY9HA3ApE/3VO9AdkAWj.o97kBfa1L8G6whfqHau7jyCZP5OH4gq', 'HPCL Admin', 'admin', NULL, '2026-09-24 21:09:26'),
(2, 'volunteer1', '$2y$10$iCyZwtherSTzWhcvuXZQLOopdpsxEMn6xSUsZ/90XNdRjVIqVbY1S', 'Scoring Volunteer 1', 'volunteer', NULL, '2026-09-24 21:09:26'),
(3, 'nodal1', '$2y$10$elCCBbqD1C.xyAESg5K7rOAw0s.9pM7mBXtB6Ch5OqMgC6XQRWXJK', 'Mumbai Refinery Team Manager', 'nodal', 785, '2026-09-24 21:09:26'),
(4, 'photo1', '$2y$10$VMxmrkwT8cgApge2za6ZOOF3LUA6EiGJUbNzTyeNG0tOUeCIOqn6W', 'Media Photographer 1', 'photographer', NULL, '2026-09-24 21:09:26'),
(778, 'nodal_mr', '$2y$10$RWA6KglUcxJzgxfFnvJIP.bWBym1Epg5fS5DqTeZfFo6Pav2kp0Ta', 'MR Team Manager', 'nodal', 785, '2026-09-28 11:24:50'),
(779, 'nodal_vr', '$2y$10$EiJLoO2Mox.glYza/v5M4OcNzqo7aC42ghfN8WimlzatmkLXeEaKO', 'VR Team Manager', 'nodal', 778, '2026-09-28 11:24:50'),
(780, 'vol_badminton1', '$2y$10$igJJaYAnSNQXHrdqoVysr.dR5G/3AlM.9eNUyPnxuV.WeKW7Fassi', 'Badminton Official Scorer 1', 'volunteer', NULL, '2026-10-09 04:38:20'),
(781, 'vol_badminton2', '$2y$10$4qLGuINGUXRcQfju70h/1ep.JwmYIF7cUFObEev0dkU4lr.OI0qGO', 'Badminton Official Scorer 2', 'volunteer', NULL, '2026-10-09 04:38:20'),
(782, 'vol_tabletennis1', '$2y$10$AjmyYcEAA7FxMMIXxtLfbeOMbtgmzYaFfNnfXabg3pOVOpE/ky8N6', 'Table Tennis Official Scorer 1', 'volunteer', NULL, '2026-10-09 04:38:20'),
(783, 'vol_tabletennis2', '$2y$10$QCS1B7d0imEzcpeBdgpNQ.JrvV5n4PS8xblKAO2anCCSjgnt2KtsC', 'Table Tennis Official Scorer 2', 'volunteer', NULL, '2026-10-09 04:38:20'),
(784, 'vol_bridge1', '$2y$10$QKlzSXOPuwFJFMBKttfzw.i1LQ6xDA1fLE84e1oEsUzldu0N3O3M2', 'Bridge Official Scorer 1', 'volunteer', NULL, '2026-10-09 04:38:20'),
(785, 'vol_bridge2', '$2y$10$64RzTdiXf/0O5CP4Dql.zeHKhVcyJgxD9JwfjI8LFc0AbLkxO.7wy', 'Bridge Official Scorer 2', 'volunteer', NULL, '2026-10-09 04:38:21'),
(786, 'vol_carrom1', '$2y$10$mPVurUBoHzeGJAeLC8Nr8efS4VdL4wi40smBVSXR3kWQ.1nPgYQ/K', 'Carrom Official Scorer 1', 'volunteer', NULL, '2026-10-09 04:38:21'),
(787, 'vol_carrom2', '$2y$10$57Usdf2MpCXiuKH.lQ1bnuS5/wJa6yo/vIulI0s0i2uoYQr6IGEPu', 'Carrom Official Scorer 2', 'volunteer', NULL, '2026-10-09 04:38:21'),
(788, 'vol_chess1', '$2y$10$q7ToMHRfykS0SCTxKl8kiuFHFKgbmIfrGVXeQp.hWMxSyv.xyCz6.', 'Chess Official Scorer 1', 'volunteer', NULL, '2026-10-09 04:38:21'),
(789, 'vol_chess2', '$2y$10$pSOHLhgHHlprADFT1sWAJ.YEnwah.azICqmyi8eqlEoiQvdVsddnO', 'Chess Official Scorer 2', 'volunteer', NULL, '2026-10-09 04:38:21'),
(790, 'vol_swimming1', '$2y$10$kK00OfIUMfoJL5Az1xsjM.e2O3LJvF6ecyPv8auECplcygPKTvdoy', 'Swimming Official Scorer 1', 'volunteer', NULL, '2026-10-09 04:38:21'),
(791, 'vol_swimming2', '$2y$10$8OZjOLCJxGZsIfxsBDxIx.S7Yu1/9aVFf/ku55790ol.NvCezswfO', 'Swimming Official Scorer 2', 'volunteer', NULL, '2026-10-09 04:38:21'),
(792, 'vol_tennis1', '$2y$10$ElVukRExmac9NniCIsizjOc35GsjS0HQyvxgsZCq9RVdgBosn0rOm', 'Tennis Official Scorer 1', 'volunteer', NULL, '2026-10-09 04:38:21'),
(793, 'vol_tennis2', '$2y$10$5jqzlZhSkTkdRB5sVZemO.C2omYTstyj6EVUm1XEqfSzQL/B0YvLG', 'Tennis Official Scorer 2', 'volunteer', NULL, '2026-10-09 04:38:21'),
(794, 'vol_badminton_open1', '$2y$10$/AzoPgNkHOTeAK/iH4kKT.UIRqeeX9XA/HIc6ZF4u5WxYqMCF90aO', 'Badminton Open Scorer 1', 'volunteer', NULL, '2026-10-09 04:38:21'),
(795, 'vol_badminton_open2', '$2y$10$Sxn0S.hRp81J0rrhUsEFMuZj2jYLN6s8czJdozgulJ1QJT0EDVmNu', 'Badminton Open Scorer 2', 'volunteer', NULL, '2026-10-09 04:38:21'),
(796, 'vol_tt_open1', '$2y$10$XFTjr0RviHBEq1C/3jYB1u1xExBi.Sr.Vhg2zwbc9l6vr5901CXZW', 'Table Tennis Open Scorer 1', 'volunteer', NULL, '2026-10-09 04:38:21'),
(797, 'vol_tt_open2', '$2y$10$S8/8gLJqdbqD.321fYJEhuSUe58ytbUVJz/0E.NqWDSL72iiuV5W6', 'Table Tennis Open Scorer 2', 'volunteer', NULL, '2026-10-09 04:38:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `albums`
--
ALTER TABLE `albums`
  ADD PRIMARY KEY (`id`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `match_id` (`match_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `master_events`
--
ALTER TABLE `master_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `team1_id` (`team1_id`),
  ADD KEY `team2_id` (`team2_id`),
  ADD KEY `winner_id` (`winner_id`),
  ADD KEY `facility_id` (`facility_id`),
  ADD KEY `volunteer_id` (`volunteer_id`);

--
-- Indexes for table `photos`
--
ALTER TABLE `photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `match_id` (`match_id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `fk_photos_album` (`album_id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `points_scheme`
--
ALTER TABLE `points_scheme`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `position` (`position`);

--
-- Indexes for table `score_formats`
--
ALTER TABLE `score_formats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `short_code` (`short_code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `unit_id` (`unit_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `albums`
--
ALTER TABLE `albums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=185;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=791;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=785;

--
-- AUTO_INCREMENT for table `master_events`
--
ALTER TABLE `master_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=782;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=848;

--
-- AUTO_INCREMENT for table `photos`
--
ALTER TABLE `photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=371;

--
-- AUTO_INCREMENT for table `points_scheme`
--
ALTER TABLE `points_scheme`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `score_formats`
--
ALTER TABLE `score_formats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=900;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=790;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=798;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `albums`
--
ALTER TABLE `albums`
  ADD CONSTRAINT `albums_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `albums_ibfk_2` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `albums_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`team1_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`team2_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `matches_ibfk_4` FOREIGN KEY (`winner_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `matches_ibfk_5` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `matches_ibfk_6` FOREIGN KEY (`volunteer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `photos`
--
ALTER TABLE `photos`
  ADD CONSTRAINT `fk_photos_album` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `photos_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `photos_ibfk_2` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `photos_ibfk_3` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `players`
--
ALTER TABLE `players`
  ADD CONSTRAINT `players_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `players_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teams_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
