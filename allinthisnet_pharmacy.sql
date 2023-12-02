-- phpMyAdmin SQL Dump
-- version 4.9.11
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 14, 2023 at 03:10 AM
-- Server version: 10.3.36-MariaDB-cll-lve
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `allinthisnet_pharmacy`
--

-- --------------------------------------------------------

--
-- Table structure for table `sma_accounts`
--

CREATE TABLE `sma_accounts` (
  `id` int(11) NOT NULL,
  `label` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fy_start` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `fy_end` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_locked` int(10) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sma_accounts`
--

INSERT INTO `sma_accounts` (`id`, `label`, `name`, `fy_start`, `fy_end`, `account_locked`) VALUES
(1, 'Avenzur', 'Avenzur', '2023-01-01', '2023-12-31', 0);

-- --------------------------------------------------------

--
-- Table structure for table `sma_accounts_groups`
--

CREATE TABLE `sma_accounts_groups` (
  `id` bigint(18) NOT NULL,
  `parent_id` bigint(18) DEFAULT 0,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `affects_gross` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `sma_accounts_groups`
--

INSERT INTO `sma_accounts_groups` (`id`, `parent_id`, `name`, `code`, `affects_gross`) VALUES
(1, NULL, 'Assets', '01', 0),
(2, NULL, 'Liabilities and Owners Equity', '02', 0),
(3, NULL, 'Incomes', '03', 0),
(4, NULL, 'Expenses', '04', 0),
(5, 1, 'Current Assets', '01-01', 0),
(6, 1, 'Long term assets', '01-02', 1),
(7, 2, 'Current Liabilities', '02-01', 0),
(8, 2, 'Long-term liabilities', '02-02', 1),
(9, 2, 'Equity', '02-03', 1),
(10, 2, 'Cost of Sales', '02-04', 1),
(11, 3, 'Incomes', '03-01', 1),
(12, 4, 'Expense', '04-01', 1),
(13, 5, 'Cash and cash equivalents', '01-01-01', 1),
(14, 5, 'Short term marketable securities', '01-01-02', 1),
(15, 5, 'Accounts receivable', '01-01-03', 1),
(16, 5, 'Inventory', '01-01-04', 1),
(17, 5, 'Other current assets', '01-01-05', 1),
(18, 6, 'Long term marketable securities', '01-02-01', 1),
(19, 6, 'Property, plant and equipment', '01-02-02', 1),
(20, 6, 'Goodwill', '01-02-03', 1),
(21, 6, 'Intellectual property', '01-02-04', 1),
(22, 6, 'Other long term assets', '01-02-05', 1),
(23, 7, 'Notes payable', '02-01-01', 1),
(24, 7, 'Accounts payable', '02-01-02', 1),
(25, 7, 'Other current liabilities', '02-01-03', 1),
(26, 8, 'Mortgages', '02-02-01', 1),
(27, 8, 'Bonds', '02-02-02', 1),
(28, 9, 'Capital', '02-03-01', 1),
(29, 9, 'Retained earnings', '02-03-02', 1),
(30, 10, 'Cost of sales', '02-04-01', 1),
(31, 11, 'Revenue', '03-01-01', 1),
(32, 11, 'Other Income', '03-01-02', 1),
(33, 12, 'Research and development', '04-01-01', 1),
(34, 12, 'Sales and marketing', '04-01-02', 1),
(35, 12, 'General and administrative', '04-01-03', 1),
(36, 12, 'Depreciation', '04-01-04', 1),
(37, 12, 'Finance Costs', '04-01-05', 1),
(38, 12, 'Income Tax Expense', '04-01-06', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sma_accounts_ledgers`
--

CREATE TABLE `sma_accounts_ledgers` (
  `id` bigint(18) NOT NULL,
  `group_id` bigint(18) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `op_balance` decimal(25,2) NOT NULL DEFAULT 0.00,
  `op_balance_dc` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(2) NOT NULL DEFAULT 0,
  `reconciliation` int(1) NOT NULL DEFAULT 0,
  `notes` varchar(500) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `sma_accounts_ledgers`
--

INSERT INTO `sma_accounts_ledgers` (`id`, `group_id`, `name`, `code`, `op_balance`, `op_balance_dc`, `type`, `reconciliation`, `notes`) VALUES
(1, 13, 'Bank Checking Account', '01-01-01-0001', '0.00', 'D', 0, 0, 'Bank Checking Account'),
(2, 13, 'Bank Savings Account', '01-01-01-0002', '0.00', 'D', 0, 1, 'Bank Savings Account'),
(3, 13, 'Online Savings Account', '01-01-01-0003', '0.00', 'D', 0, 1, 'Online Savings Account'),
(4, 13, 'Petty Cash Account', '01-01-01-0004', '0.00', 'D', 0, 0, 'Petty Cash Account'),
(5, 13, 'Paypal Account', '01-01-01-0005', '0.00', 'D', 0, 0, 'Paypal Account'),
(6, 14, 'Short Term Marketable Securities', '01-01-02-0001', '0.00', 'D', 0, 0, 'Short Term Marketable Securities'),
(7, 15, 'Accounts Receivable', '01-01-03-0001', '0.00', 'D', 0, 0, 'Accounts receivable'),
(8, 15, 'Allowance for doubtful debts account', '01-01-03-0002', '0.00', 'C', 0, 0, 'Allowance for doubtful debts account'),
(9, 16, 'Raw Materials', '01-01-04-0001', '0.00', 'D', 0, 0, 'Raw Materials'),
(10, 16, 'Work in progress', '01-01-04-0002', '0.00', 'D', 0, 0, 'Work in progress'),
(11, 16, 'Finished goods', '01-01-04-0003', '0.00', 'D', 0, 0, 'Finished goods'),
(12, 17, 'Other receivables', '01-01-05-0001', '0.00', 'D', 0, 0, 'Other receivables'),
(13, 17, 'Prepayments', '01-01-05-0002', '0.00', 'D', 0, 0, 'Prepayments'),
(14, 18, 'Long term marketable securities', '01-02-01-0001', '0.00', 'D', 0, 0, 'Long term marketable securities'),
(15, 19, 'Property', '01-02-02-0001', '0.00', 'D', 0, 0, 'Property'),
(16, 19, 'Property Depreciation', '01-02-02-0002', '0.00', 'C', 0, 0, 'Property Depreciation'),
(17, 19, 'Plant', '01-02-02-0003', '0.00', 'D', 0, 0, 'Plant'),
(18, 19, 'Plant Depreciation', '01-02-02-0004', '0.00', 'C', 0, 0, 'Plant Depreciation'),
(19, 19, 'Equipment', '01-02-02-0005', '0.00', 'D', 0, 0, 'Equipment'),
(20, 19, 'Equipment Depreciation', '01-02-02-0006', '0.00', 'C', 0, 0, 'Equipment Depreciation'),
(21, 20, 'Goodwill', '01-02-03-0001', '0.00', 'D', 0, 0, 'Goodwill'),
(22, 21, 'Intellectual Property', '01-02-04-0001', '0.00', 'D', 0, 0, 'Intellectual Property'),
(23, 21, 'Intellectual Property Amortization', '01-02-04-0002', '0.00', 'C', 0, 0, 'Intellectual Property Amortization'),
(24, 22, 'Other Assets', '01-02-05-0001', '0.00', 'D', 0, 0, 'Other Assets'),
(25, 23, 'Notes payable', '02-01-01-0001', '0.00', 'C', 0, 0, 'Notes payable'),
(26, 24, 'Accounts payable', '02-01-02-0001', '0.00', 'C', 0, 0, 'Accounts payable'),
(27, 25, 'Payroll payable', '02-01-03-0001', '0.00', 'C', 0, 0, 'Payroll payable'),
(28, 25, 'Interest payable', '02-01-03-0002', '0.00', 'C', 0, 0, 'Interest payable'),
(29, 25, 'Accrued expenses', '02-01-03-0003', '0.00', 'C', 0, 0, 'Accrued expenses'),
(30, 25, 'Unearned revenue', '02-01-03-0004', '0.00', 'C', 0, 0, 'Unearned revenue'),
(31, 25, 'Sales Tax payable', '02-01-03-0005', '0.00', 'C', 0, 0, 'Sales Tax payable'),
(32, 25, 'Purchase Tax payable', '02-01-03-0006', '0.00', 'C', 0, 0, 'Purchase Tax payable'),
(33, 25, 'Payroll tax payable', '02-01-03-0007', '0.00', 'C', 0, 0, 'Payroll tax payable'),
(34, 25, 'Income tax payable', '02-01-03-0008', '0.00', 'C', 0, 0, 'Income tax payable'),
(35, 26, 'Mortgage loan', '02-02-01-0001', '0.00', 'C', 0, 0, 'Mortgage loan'),
(36, 27, 'Bonds payable', '02-02-02-0001', '0.00', 'C', 0, 0, 'Bonds payable'),
(37, 28, 'Common stock', '02-03-01-0001', '0.00', 'C', 0, 0, 'Common stock'),
(38, 29, 'Retained earnings', '02-03-02-0001', '0.00', 'C', 0, 0, 'Retained earnings'),
(39, 31, 'Sales', '03-01-01-0001', '0.00', 'C', 0, 0, 'Sales'),
(40, 31, 'Discounts allowed', '03-01-01-0002', '0.00', 'D', 0, 0, 'Discounts allowed'),
(41, 30, 'Materials purchased', '02-04-01-0001', '0.00', 'D', 0, 0, 'Materials purchased'),
(42, 30, 'Packaging', '02-04-01-0002', '0.00', 'D', 0, 0, 'Packaging'),
(43, 30, 'Discounts taken', '02-04-01-0003', '0.00', 'D', 0, 0, 'Discounts taken'),
(44, 30, 'Carriage', '02-04-01-0004', '0.00', 'C', 0, 1, 'Carriage'),
(45, 30, 'Import duty', '02-04-01-0005', '0.00', 'C', 0, 1, 'Import duty'),
(46, 30, 'Transport insurance', '02-04-01-0006', '0.00', 'D', 0, 0, 'Transport insurance'),
(47, 30, 'Opening inventory', '02-04-01-0007', '0.00', 'D', 0, 0, 'Opening inventory'),
(48, 30, 'Closing inventory', '02-04-01-0008', '0.00', 'C', 0, 0, 'Closing inventory'),
(49, 30, 'Productive Labour', '02-04-01-0009', '0.00', 'D', 0, 0, 'Productive Labour'),
(50, 33, 'Research and development', '04-01-01-0001', '0.00', 'D', 0, 0, 'Research and development'),
(51, 34, 'Sales commissions', '04-01-02-0001', '0.00', 'D', 0, 0, 'Sales commissions'),
(52, 34, 'Sales promotion', '04-01-02-0002', '0.00', 'D', 0, 0, 'Sales promotion'),
(53, 34, 'Advertising', '04-01-02-0003', '0.00', 'D', 0, 0, 'Advertising'),
(54, 34, 'Gifts & samples', '04-01-02-0004', '0.00', 'D', 0, 0, 'Gifts & samples'),
(55, 34, 'Marketing expenses', '04-01-02-0005', '0.00', 'D', 0, 0, 'Marketing expenses'),
(56, 35, 'Payroll', '04-01-03-0001', '0.00', 'D', 0, 0, 'Payroll'),
(57, 35, 'Payroll expenses', '04-01-03-0002', '0.00', 'D', 0, 0, 'Payroll expenses'),
(58, 35, 'Payroll benefits', '04-01-03-0003', '0.00', 'D', 0, 0, 'Payroll benefits'),
(59, 35, 'Payroll taxes', '04-01-03-0004', '0.00', 'D', 0, 0, 'Payroll taxes'),
(60, 35, 'Pensions', '04-01-03-0005', '0.00', 'D', 0, 0, 'Pensions'),
(61, 35, 'Recruitment expenses', '04-01-03-0006', '0.00', 'D', 0, 0, 'Recruitment expenses'),
(62, 35, 'Rent', '04-01-03-0007', '0.00', 'D', 0, 0, 'Rent'),
(63, 35, 'Water', '04-01-03-0008', '0.00', 'D', 0, 0, 'Water'),
(64, 35, 'Property taxes', '04-01-03-0009', '0.00', 'D', 0, 0, 'Property taxes'),
(65, 35, 'Premises insurance', '04-01-03-0010', '0.00', 'D', 0, 0, 'Premises insurance'),
(66, 35, 'Electricity', '04-01-03-0011', '0.00', 'D', 0, 0, 'Electricity'),
(67, 35, 'Gas', '04-01-03-0012', '0.00', 'D', 0, 0, 'Gas'),
(68, 35, 'Oil', '04-01-03-0013', '0.00', 'D', 0, 0, 'Oil'),
(69, 35, 'Other heating costs', '04-01-03-0014', '0.00', 'D', 0, 0, 'Other heating costs'),
(70, 35, 'Motor fuel', '04-01-03-0015', '0.00', 'D', 0, 0, 'Motor fuel'),
(71, 35, 'Motor repairs', '04-01-03-0016', '0.00', 'D', 0, 0, 'Motor repairs'),
(72, 35, 'Licenses', '04-01-03-0017', '0.00', 'D', 0, 0, 'Licenses'),
(73, 35, 'Vehicle insurance', '04-01-03-0018', '0.00', 'D', 0, 0, 'Vehicle insurance'),
(74, 35, 'Miscellaneous motor', '04-01-03-0019', '0.00', 'D', 0, 0, 'Miscellaneous motor'),
(75, 35, 'Travelling', '04-01-03-0020', '0.00', 'D', 0, 0, 'Travelling'),
(76, 35, 'Car hire', '04-01-03-0021', '0.00', 'D', 0, 0, 'Car hire'),
(77, 35, 'Hotels', '04-01-03-0022', '0.00', 'D', 0, 0, 'Hotels'),
(78, 35, 'Entertainment', '04-01-03-0023', '0.00', 'D', 0, 0, 'Entertainment'),
(79, 35, 'Subsistence', '04-01-03-0024', '0.00', 'D', 0, 0, 'Subsistence'),
(80, 35, 'Printing', '04-01-03-0025', '0.00', 'D', 0, 0, 'Printing'),
(81, 35, 'Postage & carriage', '04-01-03-0026', '0.00', 'D', 0, 0, 'Postage & carriage'),
(82, 35, 'Telephone', '04-01-03-0027', '0.00', 'D', 0, 0, 'Telephone'),
(83, 35, 'Office stationery', '04-01-03-0028', '0.00', 'D', 0, 0, 'Office stationery'),
(84, 35, 'Books', '04-01-03-0029', '0.00', 'D', 0, 0, 'Books'),
(85, 35, 'Legal fees', '04-01-03-0030', '0.00', 'D', 0, 0, 'Legal fees'),
(86, 35, 'Audit & accountancy fees', '04-01-03-0031', '0.00', 'D', 0, 0, 'Audit & accountancy fees'),
(87, 35, 'Consultancy fees', '04-01-03-0032', '0.00', 'D', 0, 0, 'Consultancy fees'),
(88, 35, 'Professional fees', '04-01-03-0033', '0.00', 'D', 0, 0, 'Professional fees'),
(89, 35, 'Equipment hire', '04-01-03-0034', '0.00', 'D', 0, 0, 'Equipment hire'),
(90, 35, 'Equipment maintenance', '04-01-03-0035', '0.00', 'D', 0, 0, 'Equipment maintenance'),
(91, 35, 'Repairs & renewals', '04-01-03-0036', '0.00', 'D', 0, 0, 'Repairs & renewals'),
(92, 35, 'Cleaning', '04-01-03-0037', '0.00', 'D', 0, 0, 'Cleaning'),
(93, 35, 'Laundry', '04-01-03-0038', '0.00', 'D', 0, 0, 'Laundry'),
(94, 35, 'Premises expenses', '04-01-03-0039', '0.00', 'D', 0, 0, 'Premises expenses'),
(95, 35, 'Bad debt expense', '04-01-03-0040', '0.00', 'D', 0, 0, 'Bad debt expense'),
(96, 35, 'Donations', '04-01-03-0041', '0.00', 'D', 0, 0, 'Donations'),
(97, 35, 'Subscriptions', '04-01-03-0042', '0.00', 'D', 0, 0, 'Subscriptions'),
(98, 35, 'Clothing costs', '04-01-03-0043', '0.00', 'D', 0, 0, 'Clothing costs'),
(99, 35, 'Training costs', '04-01-03-0044', '0.00', 'D', 0, 0, 'Training costs'),
(100, 35, 'Insurance', '04-01-03-0045', '0.00', 'D', 0, 0, 'Insurance'),
(101, 35, 'Refreshments', '04-01-03-0046', '0.00', 'D', 0, 0, 'Refreshments'),
(102, 35, 'Suspense account', '04-01-03-0047', '0.00', 'D', 0, 0, 'Suspense account'),
(103, 35, 'Mispostings account', '04-01-03-0048', '0.00', 'D', 0, 0, 'Mispostings account'),
(104, 36, 'Property depreciation', '04-01-04-0001', '0.00', 'D', 0, 0, 'Property depreciation'),
(105, 36, 'Plant depreciation', '04-01-04-0002', '0.00', 'D', 0, 0, 'Plant depreciation'),
(106, 36, 'Equipment depreciation', '04-01-04-0003', '0.00', 'D', 0, 0, 'Equipment depreciation'),
(107, 36, 'Intellectal property amortization', '04-01-04-0004', '0.00', 'D', 0, 1, 'Intellectal property amortization'),
(108, 37, 'Interest expense', '04-01-05-0001', '0.00', 'D', 0, 0, 'Interest expense'),
(109, 37, 'Bank fees', '04-01-05-0002', '0.00', 'D', 0, 0, 'Bank fees'),
(110, 37, 'Currency charges', '04-01-05-0003', '0.00', 'D', 0, 0, 'Currency charges'),
(111, 32, 'Gain on sale of assets', '03-01-02-0001', '0.00', 'C', 0, 1, 'Gain on sale of assets'),
(112, 32, 'Interest income', '03-01-02-0002', '0.00', 'C', 0, 0, 'Interest income'),
(113, 32, 'Insurance claims', '03-01-02-0003', '0.00', 'C', 0, 0, 'Insurance claims'),
(114, 32, 'Rent income', '03-01-02-0004', '0.00', 'C', 0, 1, 'Rent income'),
(115, 38, 'Income tax expense', '04-01-06-0001', '0.00', 'D', 0, 0, 'Income tax expense');

-- --------------------------------------------------------

--
-- Table structure for table `sma_accounts_settings`
--

CREATE TABLE `sma_accounts_settings` (
  `id` int(1) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `fy_start` date NOT NULL,
  `fy_end` date NOT NULL,
  `currency_symbol` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `currency_format` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `decimal_places` int(2) NOT NULL DEFAULT 2,
  `date_format` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `timezone` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `manage_inventory` int(1) NOT NULL DEFAULT 0,
  `account_locked` int(1) NOT NULL DEFAULT 0,
  `email_use_default` int(1) NOT NULL DEFAULT 0,
  `email_protocol` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `email_host` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email_port` int(5) NOT NULL,
  `email_tls` int(1) NOT NULL DEFAULT 0,
  `email_username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email_password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email_from` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `print_paper_height` decimal(10,3) NOT NULL DEFAULT 0.000,
  `print_paper_width` decimal(10,3) NOT NULL DEFAULT 0.000,
  `print_margin_top` decimal(10,3) NOT NULL DEFAULT 0.000,
  `print_margin_bottom` decimal(10,3) NOT NULL DEFAULT 0.000,
  `print_margin_left` decimal(10,3) NOT NULL DEFAULT 0.000,
  `print_margin_right` decimal(10,3) NOT NULL DEFAULT 0.000,
  `print_orientation` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `print_page_format` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `database_version` int(10) DEFAULT NULL,
  `settings` blob DEFAULT NULL,
  `logo` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `sma_accounts_settings`
--

INSERT INTO `sma_accounts_settings` (`id`, `name`, `address`, `email`, `fy_start`, `fy_end`, `currency_symbol`, `currency_format`, `decimal_places`, `date_format`, `timezone`, `manage_inventory`, `account_locked`, `email_use_default`, `email_protocol`, `email_host`, `email_port`, `email_tls`, `email_username`, `email_password`, `email_from`, `print_paper_height`, `print_paper_width`, `print_margin_top`, `print_margin_bottom`, `print_margin_left`, `print_margin_right`, `print_orientation`, `print_page_format`, `database_version`, `settings`, `logo`) VALUES
(1, 'Avenzur', 'Riyadh, Saudi Arabia', 'admin@avenzur.com', '2023-01-01', '2023-12-31', 'SAR ', '##,###.##', 2, 'd-M-Y|dd-M-yy', 'UTC', 0, 0, 1, 'smtp', '', 0, 0, '', '', '', '0.000', '0.000', '0.000', '0.000', '0.000', '0.000', 'P', 'H', NULL, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `sma_addresses`
--

CREATE TABLE `sma_addresses` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `line1` varchar(50) NOT NULL,
  `line2` varchar(50) DEFAULT NULL,
  `city` varchar(25) NOT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `state` varchar(25) NOT NULL,
  `country` varchar(50) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_addresses`
--

INSERT INTO `sma_addresses` (`id`, `company_id`, `line1`, `line2`, `city`, `postal_code`, `state`, `country`, `phone`, `updated_at`) VALUES
(1, 7, 's', 's', 's', 's', 's', 's', 's', '2022-11-30 10:27:20'),
(2, 8, 'test address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 09:37:32'),
(3, 8, 'test address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 09:38:47'),
(4, 8, 'test address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 09:39:53'),
(5, 8, 'test Address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 10:18:46'),
(6, 8, 'test address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 10:28:26'),
(7, 8, 'test address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 10:29:31'),
(8, 8, 'test address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 11:31:06'),
(9, 8, 'Test Address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 12:33:39'),
(10, 8, 'test address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 12:37:20'),
(11, 8, 'test address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 20:25:18'),
(12, 8, 'test address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 20:35:55'),
(13, 8, 'test address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 20:40:09'),
(14, 8, 'test address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 20:42:30'),
(15, 8, 'test address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 20:43:54'),
(16, 8, 'test address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 20:48:19'),
(17, 8, 'test address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 20:52:27'),
(18, 8, 'test address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 21:01:03'),
(19, 8, 'test address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 21:06:59'),
(20, 8, 'test address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 21:11:03'),
(21, 8, 'Test Address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 21:12:09'),
(22, 8, 'test address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-08 21:31:03'),
(23, 8, 'test address', '', 'Rawalpindi', '46000', 'Punjab', 'Pakistan', '+923465320003', '2022-12-09 06:29:14'),
(24, 9, 'rwp', 'rwp', 'rawalpindi', '47000', 'punjab', 'pakistan', '03340542941', '2022-12-09 07:10:13'),
(25, 10, 'Rawalpindi', 'Test', 'Rawalpindi Punjab', '46000', 'Punjab', 'Pakistan', '+923325290945', '2022-12-10 12:12:40'),
(26, 11, 'sdfs', 's', 'riy', '', '', 'sfs', '05555', '2022-12-11 11:46:51'),
(27, 7, 'rwp', 'rwp', 'rwp', '47000', 'rwp', 'pakistan', '0123456789', '2022-12-12 09:41:15'),
(28, 13, 'rwp', 'rwp', 'rwp', '47000', 'rwp', 'pk', '123456789', '2022-12-12 09:45:36'),
(29, 11, 'gs', 'dsg', 'riy', '13524', 'saudi', 'Saudi Arabia', '05670245', '2022-12-12 12:13:46'),
(30, 7, 'rwp', 'rwp', 'rwp', '47000', 'wrp', 'pakistan', '12211212', '2022-12-13 05:25:30'),
(31, 14, 'rwp', 'rp', 'ei', '47000', 'rwp', 'pakistan', '98129872981', '2022-12-13 05:41:56'),
(32, 14, 'RWO', 'RWP', 'RWP', '4700', 'IUI', 'IUIO', '1234567', '2022-12-13 06:03:43'),
(33, 20, 'rwp', 'rwp', 'rwp', '47000', 'rwp', 'rwp', '1212112', '2022-12-13 07:57:57'),
(34, 21, 'rwp', 'rwp', 'rwp', '47000', 'rwprwp', 'rwp', '121275', '2022-12-13 08:03:32'),
(35, 21, 'kjkj', 'kjj', 'kj', 'l', 'j', 'j', '12142172', '2022-12-13 08:04:25'),
(36, 14, 'rwp', 'rwp', 'rwp', '46000', 'rwp', 'rwp', '12121212', '2022-12-15 07:13:54'),
(37, 22, '212', 'rwp', 'rwo', '4600', 'jkj', 'pk', '121212122', '2022-12-15 07:17:25'),
(38, 23, 'test', 'test', 'test', 'test', 'tetette', 'test', '12121212', '2022-12-15 10:35:21'),
(39, 24, 'test1', '', 'amman', '1117', '', 'Jordan', '9627555555555', '2022-12-15 10:38:57'),
(40, 25, 'test1', '', 'amman', '1117', '', 'Jordan', '962755555555555', '2022-12-15 10:41:51'),
(41, 25, 'test1', '', 'amman', '1117', '', 'Jordan', '799999999999', '2022-12-15 10:43:57'),
(42, 25, 'test1', '', 'amman', '1117', '', 'Jordan', '+962795555555', '2022-12-15 11:57:32'),
(43, 25, 'test1', '', 'amman', '1117', '', 'Jordan', '+962795555555', '2022-12-15 11:59:48'),
(44, 11, 'af', '', 'riy', '13524', 'saudi', 'Saudi Arabia', '0567024510', '2022-12-15 12:50:22'),
(45, 26, 'rwp', 'rwp', 'rwp', '120000', 'rwp', 'SA', '1212121212', '2022-12-16 10:05:26'),
(46, 26, 'rwp', 'rwp', 'rwp', '120000', 'rwp', 'SA', '1212121212', '2022-12-16 10:06:23'),
(47, 26, 'rwp', 'rwp', 'rwp', '120000', 'rwp', 'SA', '1212121212', '2022-12-16 10:06:43'),
(48, 26, 'rwp', 'rwp', 'rwp', '120000', 'rwp', 'SA', '1212121212', '2022-12-16 10:07:26'),
(49, 27, 'kk', 'kk', 'k', 'k', 'k', 'k', '11212122', '2022-12-16 10:12:09'),
(50, 27, 'kk', 'kk', 'k', 'k', 'k', 'k', '11212122', '2022-12-16 10:12:22'),
(51, 28, 'rwp', 'rwp', 'rwp', '121212', 'rwp', 'SA', '121212', '2022-12-16 10:14:11'),
(52, 28, 'rwp', 'rwp', 'rwp', '121212', 'rwp', 'SA', '121212', '2022-12-16 10:14:18'),
(53, 29, 'rrr', 'rrrrr', 'rrr', '12121212', 'rrr', 'SA', '2222', '2022-12-16 10:21:42'),
(54, 8, 'test', '', 'Rawalpindi', '11663', 'Riyadh', 'SA', '966123456744', '2022-12-16 13:54:17'),
(55, 8, 'test', '', 'Rawalpindi', '11663', 'Riyadh', 'SA', '966123456744', '2022-12-16 13:54:36'),
(56, 8, 'test', '', 'Rawalpindi', '11663', 'Riyadh', 'SA', '9661234567', '2022-12-16 14:01:23'),
(57, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '9661234567', '2022-12-16 14:06:42'),
(58, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '966001234567', '2022-12-16 14:11:12'),
(59, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '966001234567', '2022-12-16 14:12:31'),
(60, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '9661234567', '2022-12-16 14:17:55'),
(61, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '9661234567', '2022-12-16 14:20:12'),
(62, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '9661234567', '2022-12-16 14:21:08'),
(63, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '9661234567', '2022-12-16 14:21:50'),
(64, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '9661234567', '2022-12-16 14:22:25'),
(65, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '9661234567', '2022-12-16 14:23:20'),
(66, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '9661234567', '2022-12-16 14:30:05'),
(67, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '9661234567', '2022-12-16 14:35:45'),
(68, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '9661234567', '2022-12-16 14:36:45'),
(69, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '9661234567', '2022-12-16 14:41:11'),
(70, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '9661234567', '2022-12-16 14:43:53'),
(71, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '9661234567', '2022-12-16 14:45:01'),
(72, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '9661234567', '2022-12-16 14:48:59'),
(73, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '9661234567', '2022-12-16 14:50:07'),
(74, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '9661234567', '2022-12-16 14:52:37'),
(75, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '9661234567', '2022-12-16 14:54:04'),
(76, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '9661234567', '2022-12-16 14:56:06'),
(77, 30, 'test', '', 'Riyadh', '11663', '', 'SA', '9661234567', '2022-12-16 14:56:58'),
(78, 30, 'test address', '', 'Riyadh', '11663', '', 'SA', '9661234567', '2022-12-16 15:05:27'),
(79, 35, 'Riyadh', '', 'Riyadh', '11351', '', 'SA', '+971585280538', '2022-12-19 09:01:25'),
(80, 36, 'yyuhjv', '', 'riyadh', '', '', 'SA', '56576576', '2022-12-19 13:25:56'),
(81, 11, 'sdfs', '', 'dw', '', 'dfd', 'SA', '0567074903', '2022-12-20 12:25:13'),
(82, 44, 'H#41, St# 2', '', 'Riyadh', '11663', 'Riyadh', 'Saudi Arabia', '9661234567', '2022-12-21 11:27:09'),
(83, 8, 'test address', '', 'Rawalpindi', '46000', 'Punjab', 'SA', '+923465320003', '2022-12-21 11:27:37'),
(84, 8, 'test address', '', 'Riyadh', '11663', 'Riyadh', 'SA', '+923465320003', '2022-12-21 11:32:38'),
(85, 0, 'City Riyadh', 'Saudi Arabia ', 'Riyadh', '11663', '', 'SA', '+966 12345678', '2022-12-30 12:21:37'),
(86, 0, 'City Riyadh', 'Saudi Arabia ', 'Riyadh', '11663', '', 'SA', '+966 12345678', '2022-12-30 12:28:00'),
(87, 0, 'test address', '', 'Riyadh', '11663', '', 'SA', '+9661234567', '2022-12-30 12:31:58'),
(88, 0, 'test address', '', 'Riyadh', '11663', '', 'SA', '+9661234567', '2022-12-30 12:34:37'),
(89, 0, 'test address', '', 'Riyadh', '11663', '', 'SA', '+9661234567', '2022-12-30 12:42:11'),
(90, 0, 'test address', '', 'Riyadh', '11663', '', 'SA', '+9661234567', '2022-12-30 12:43:35'),
(91, 0, 'test address', '', 'Riyadh', '11663', '', 'SA', '+9661234567', '2022-12-30 12:44:28'),
(92, 0, 'test address', '', 'Riyadh', '11663', '', 'SA', '+9661234567', '2022-12-30 12:45:20'),
(93, 0, 'test address', '', 'Riyadh', '11663', '', 'SA', '+9661234567', '2022-12-30 12:46:33'),
(94, 0, 'test address', '', 'Riyadh', '11663', '', 'SA', '+9661234567', '2022-12-30 12:52:36'),
(95, 0, 'test address', '', 'Riyadh', '11663', '', 'SA', '+96612345677', '2022-12-30 12:55:59'),
(96, 0, 'test address', '', 'Riyadh', '11663', '', 'SA', '+96612345677', '2022-12-30 12:57:35'),
(97, 0, 'test address', '', 'Riyadh', '11663', '', 'SA', '+9661234567', '2022-12-30 13:01:55'),
(98, 525, 'test address', '', 'Riyadh', '11663', '', 'SA', '+9662345645', '2022-12-30 13:04:42'),
(99, 525, 'test address', '', 'Riyadh', '11663', '', 'SA', '+9662345645', '2022-12-30 13:08:18'),
(100, 525, 'test address', '', 'Riyadh', '11663', '', 'SA', '+9661234567', '2022-12-30 13:09:22'),
(101, 525, 'test address', '', 'Riyadh', '11663', '', 'SA', '+96612345672', '2022-12-30 13:14:15'),
(102, 526, 'Riyadh', '', 'Riyadh', '11436', 'Saudi', 'SA', '+966 567074903', '2022-12-30 13:41:38'),
(103, 525, 'Test address', '', 'Riyadh', '11663', '', 'SA', '+9661234567', '2022-12-30 13:57:01'),
(104, 525, 'Test address', '', 'Riyadh', '11663', '', 'SA', '+96612345678', '2022-12-30 14:07:16'),
(105, 525, 'test address', '', 'Riyadh', '11663', '', 'SA', '+96612345678', '2022-12-30 14:10:59'),
(106, 526, 'af', '', 'riy', '13524', 'saudi', 'SA', '+966 567074903', '2023-01-01 06:10:19'),
(107, 526, 'af', '', 'riy', '13524', '', 'SA', '+966 567074903', '2023-01-01 06:20:30'),
(108, 526, 'af', '', 'riy', '13524', '', 'SA', '+966 567074903', '2023-01-01 07:26:28'),
(109, 526, 'aada', 's', 'riyadh', 'ds', 'saudi', 'SA', '+966 567074903', '2023-01-03 12:16:39'),
(110, 526, 'sdfs', '', 'riyadh', 'ds', 'saudi', 'SA', '+966 567074903', '2023-01-04 06:20:15'),
(111, 525, 'Test address', '', 'Riyadh', '11663', '', 'SA', '96612345677', '2023-01-09 10:26:41'),
(112, 525, 'test address', '', 'Riyadh', '11663', '', 'SA', '+9661234567', '2023-01-11 21:03:56'),
(113, 529, 'Solimaiyah', '', 'Riyadh', '12242', 'Riyadh', 'SA', '+966 568241418', '2023-01-12 08:57:41'),
(114, 529, 'Riyadh', '', 'Riyadh', '12242', '', 'SA', '+966590503643', '2023-01-12 09:13:03'),
(115, 525, 'test address', '', 'Riyadh', '11663', '', 'SA', '+966 1234568998', '2023-01-18 14:58:12'),
(116, 525, 'test address', '', 'Riyadh', '11663', '', 'SA', '+966123456789', '2023-01-18 15:19:30'),
(117, 531, 'testiu', 'tetst', 'ttuit', 'iut', 'uit', 'iu', 'tiu', '2023-01-24 10:46:09'),
(118, 531, '123', 'hwp', 'jk', 'kjk', 'j', 'jk', 'k', '2023-01-24 10:47:01');

-- --------------------------------------------------------

--
-- Table structure for table `sma_adjustments`
--

CREATE TABLE `sma_adjustments` (
  `id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reference_no` varchar(55) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `note` text DEFAULT NULL,
  `attachment` varchar(55) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `count_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_adjustments`
--

INSERT INTO `sma_adjustments` (`id`, `date`, `reference_no`, `warehouse_id`, `note`, `attachment`, `created_by`, `updated_by`, `updated_at`, `count_id`) VALUES
(1, '2022-10-03 17:05:00', 'Mujtaba2', 1, '', NULL, 1, NULL, NULL, NULL),
(2, '2022-11-16 14:43:00', '565656', 1, '', NULL, 1, NULL, NULL, NULL),
(3, '2022-11-16 23:45:00', '123', 1, '', NULL, 1, NULL, NULL, NULL),
(4, '2022-11-16 23:52:00', 'Test', 1, '', NULL, 1, NULL, NULL, NULL),
(5, '2022-12-18 00:15:00', '2022/12/0001', 3, '', NULL, 1, NULL, NULL, NULL),
(6, '2022-12-22 23:56:00', '62190434', 4, '', NULL, 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sma_adjustment_items`
--

CREATE TABLE `sma_adjustment_items` (
  `id` int(11) NOT NULL,
  `adjustment_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `option_id` int(11) DEFAULT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `serial_no` varchar(255) DEFAULT NULL,
  `type` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_adjustment_items`
--

INSERT INTO `sma_adjustment_items` (`id`, `adjustment_id`, `product_id`, `option_id`, `quantity`, `warehouse_id`, `serial_no`, `type`) VALUES
(1, 1, 1, NULL, '3.0000', 1, '', 'subtraction'),
(2, 2, 3, 3, '8.0000', 1, '67687', 'addition'),
(3, 3, 1, NULL, '10.0000', 1, '', 'addition'),
(5, 4, 1, NULL, '50.0000', 1, '', 'addition'),
(6, 5, 26, 23, '20.0000', 3, '4564654', 'subtraction'),
(7, 6, 26, 22, '90.0000', 4, '', 'addition');

-- --------------------------------------------------------

--
-- Table structure for table `sma_api_keys`
--

CREATE TABLE `sma_api_keys` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reference` varchar(40) NOT NULL,
  `key` varchar(40) NOT NULL,
  `level` int(2) NOT NULL,
  `ignore_limits` tinyint(1) NOT NULL DEFAULT 0,
  `is_private_key` tinyint(1) NOT NULL DEFAULT 0,
  `ip_addresses` text DEFAULT NULL,
  `date_created` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sma_api_limits`
--

CREATE TABLE `sma_api_limits` (
  `id` int(11) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `count` int(10) NOT NULL,
  `hour_started` int(11) NOT NULL,
  `api_key` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sma_api_logs`
--

CREATE TABLE `sma_api_logs` (
  `id` int(11) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `method` varchar(6) NOT NULL,
  `params` text DEFAULT NULL,
  `api_key` varchar(40) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `time` int(11) NOT NULL,
  `rtime` float DEFAULT NULL,
  `authorized` varchar(1) NOT NULL,
  `response_code` smallint(3) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sma_aramex`
--

CREATE TABLE `sma_aramex` (
  `id` int(11) NOT NULL,
  `line1` varchar(25) NOT NULL DEFAULT 'testaccount',
  `line2` varchar(25) NOT NULL DEFAULT 'mbtest',
  `city` varchar(25) NOT NULL DEFAULT 'USD',
  `postal_code` varchar(25) NOT NULL DEFAULT '0.0000',
  `country_code` varchar(25) NOT NULL DEFAULT '0.0000',
  `person_name` varchar(25) NOT NULL DEFAULT '0.0000',
  `company_name` varchar(25) NOT NULL DEFAULT '0.0000',
  `landline_number` varchar(25) NOT NULL DEFAULT '0.0000',
  `cell_number` varchar(25) NOT NULL DEFAULT '0.0000',
  `Email` varchar(25) NOT NULL DEFAULT '0.0000',
  `account_entity` varchar(50) NOT NULL DEFAULT '0.0000',
  `account_number` varchar(50) NOT NULL DEFAULT '0.0000',
  `account_pin` varchar(50) NOT NULL DEFAULT '0.0000',
  `user_name` varchar(25) NOT NULL DEFAULT '0.0000',
  `password` varchar(25) NOT NULL DEFAULT '0.0000',
  `version` varchar(25) NOT NULL DEFAULT '0.0000',
  `shippment_url` varchar(150) NOT NULL DEFAULT '0.0000',
  `pickup_url` varchar(150) NOT NULL DEFAULT '0.0000',
  `test_shippment_url` varchar(150) NOT NULL DEFAULT '0.0000',
  `test_pickup_url` varchar(150) NOT NULL DEFAULT '0.0000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_aramex`
--

INSERT INTO `sma_aramex` (`id`, `line1`, `line2`, `city`, `postal_code`, `country_code`, `person_name`, `company_name`, `landline_number`, `cell_number`, `Email`, `account_entity`, `account_number`, `account_pin`, `user_name`, `password`, `version`, `shippment_url`, `pickup_url`, `test_shippment_url`, `test_pickup_url`) VALUES
(1, 'Al kharaj', 'Al kharaj', 'Riyadh', '11663', 'SA', 'Amr', 'Pharma drug store', '966568241418', '966568241418\r\n', 'ama@pharma.com.sa', 'RUH', '71449672', '107806', 'testingapi@aramex.com', 'R123456789$r', '1.0', 'https://ws.dev.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc?wsdl%27', 'https://ws.dev.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc?wsdl%27', 'https://ws.dev.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc?wsdl%27', 'https://ws.dev.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc?wsdl%27');

-- --------------------------------------------------------

--
-- Table structure for table `sma_aramex_shippment`
--

CREATE TABLE `sma_aramex_shippment` (
  `id` int(11) NOT NULL,
  `salesid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipmentid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `labelurl` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sma_aramex_shippment`
--

INSERT INTO `sma_aramex_shippment` (`id`, `salesid`, `reference`, `shipmentid`, `labelurl`, `date`, `note`) VALUES
(15, '101', '1672404959397', '30496440693', 'http://www.sandbox.aramex.com/content/rpt_cache/450dacb101dd489dbed48f2e4d60d29c.pdf', '2022-12-30 15:56:04', 'successful'),
(16, '102', '1672405057635', '30496440704', 'http://www.sandbox.aramex.com/content/rpt_cache/e4d738c3d3d041ec8fc0b89b7d091dc1.pdf', '2022-12-30 15:57:42', 'successful'),
(17, '103', '1672405317905', '30496440715', 'http://www.sandbox.aramex.com/content/rpt_cache/796394c74b034e9ab87cdfeba88deb64.pdf', '2022-12-30 16:02:02', 'successful'),
(18, '104', '1672405482890', '30496440726', 'http://www.sandbox.aramex.com/content/rpt_cache/56eb4aee8c5f4e1fb7c3683ab4e55033.pdf', '2022-12-30 16:04:48', 'successful'),
(19, '105', '1672405699051', '30496440730', 'http://www.sandbox.aramex.com/content/rpt_cache/e5bed69eb8b9409b8cd365856ff9ba04.pdf', '2022-12-30 16:08:24', 'successful'),
(20, '106', '1672405762860', '30496440741', 'http://www.sandbox.aramex.com/content/rpt_cache/711d8afb48d74560865650e4d700cb66.pdf', '2022-12-30 16:09:27', 'successful'),
(21, '107', '1672406056548', '30496440752', 'http://www.sandbox.aramex.com/content/rpt_cache/fa8b7fc060e84624ac1d46fb013fc44d.pdf', '2022-12-30 16:14:21', 'successful'),
(22, '108', '1672407698938', '30496440763', 'http://www.sandbox.aramex.com/content/rpt_cache/fccab7d4c978431eb4df14a52ab72ebd.pdf', '2022-12-30 16:41:44', 'successful'),
(23, '109', '1672408621989', '30496440774', 'http://www.sandbox.aramex.com/content/rpt_cache/bacac9ce11d5455c9caf7f4fcd618584.pdf', '2022-12-30 16:57:07', 'successful'),
(24, '110', '1672409237787', '30496440785', 'http://www.sandbox.aramex.com/content/rpt_cache/ea0d5889e1af4d19b5dc13f9d871d57b.pdf', '2022-12-30 17:07:23', 'successful'),
(25, '111', '1672409460223', '30496440796', 'http://www.sandbox.aramex.com/content/rpt_cache/f4711dd3392346f3a71fd4dd2432fb82.pdf', '2022-12-30 17:11:05', 'successful'),
(26, '112', '1672553425494', '30496440800', 'http://www.sandbox.aramex.com/content/rpt_cache/2b61ccec8ee6409baaf87cfa6fc17192.pdf', '2023-01-01 09:10:53', 'successful');

-- --------------------------------------------------------

--
-- Table structure for table `sma_attachments`
--

CREATE TABLE `sma_attachments` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `subject_type` varchar(55) NOT NULL,
  `file_name` varchar(100) NOT NULL,
  `orig_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_attachments`
--

INSERT INTO `sma_attachments` (`id`, `subject_id`, `subject_type`, `file_name`, `orig_name`) VALUES
(1, 14, 'purchase', 'e445ff0308c7163c3be2b5139832b4a8.pdf', 'SULFAD_01223001.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `sma_blog`
--

CREATE TABLE `sma_blog` (
  `id` int(10) NOT NULL,
  `name` varchar(15) NOT NULL,
  `title` varchar(60) NOT NULL,
  `description` varchar(180) NOT NULL,
  `slug` varchar(55) DEFAULT NULL,
  `body` text NOT NULL,
  `image` varchar(55) NOT NULL,
  `category` varchar(60) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_blog`
--

INSERT INTO `sma_blog` (`id`, `name`, `title`, `description`, `slug`, `body`, `image`, `category`, `updated_at`) VALUES
(19, 'What We Need to', 'What We Need to Know Pharmacy', 'What We Need to Know Pharmacy', 'need-to-know', 'Lorem ipsum is placeholder text commonly used in the graphic, print, and publishing industries for previewing layouts and visual mockups.\r\n\r\nLOREM IPSU', '50e3f061ae4adb747a8b37920d4a6a72.jpg', 'pharmacy', '2022-12-05 12:05:07'),
(20, 'What We Pharmac', 'What We Pharmacy to Know Pharmacy', 'What We Need to Know Pharmacy', 'pharmacy-detail', 'Lorem ipsum is placeholder text commonly used in the graphic, print, and publishing industries for previewing layouts and visual mockups.\r\n\r\nLOREM IPSU\r\nLorem ipsum is placeholder text commonly used in the graphic, print, and publishing industries for previewing layouts and visual mockups.\r\n\r\nLOREM IPSU\r\nLorem ipsum is placeholder text commonly used in the graphic, print, and publishing industries for previewing layouts and visual mockups.\r\n\r\nLOREM IPSU', '50e3f061ae4adb747a8b37920d4a6a72.jpg', 'pharmacy', '2022-12-05 12:05:13'),
(21, 'Pharmaceutical ', 'Pharmaceutical &amp; Biotechnology', 'Pharmaceutical &amp; Biotechnology', 'pharmaceutical-biotechnology', '<p>Pfizer is undoubtedly a leading innovator and employer in the Pharmaceutical and Biotechnology space. According to our inside research, the Pfizer website visitors have a keen interest in the latest news and regulatory developments. It certainly doesn\\\'t come as a surprise since all eyes are set on the performance of the Pfizer-BioNTech Covid19 vaccine. In addition to this, Pfizer also maintains a <a href=\\\"\\\\\\\">science blog</a> for sharing the latest know-how in the biopharmaceutical industry test.</p>', 'e29f8ad9c3a5acc0a09bc888d2f54820.jpg', 'pharamcy', '2022-12-19 16:25:02');

-- --------------------------------------------------------

--
-- Table structure for table `sma_blog_categories`
--

CREATE TABLE `sma_blog_categories` (
  `id` int(11) NOT NULL,
  `code` varchar(55) NOT NULL,
  `name` varchar(55) NOT NULL,
  `image` varchar(55) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `slug` varchar(55) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_blog_categories`
--

INSERT INTO `sma_blog_categories` (`id`, `code`, `name`, `image`, `parent_id`, `slug`, `description`) VALUES
(15, '12334', 'pharamcy', '69c1e2121ea7294c26c92e719e2042ea.png', 0, 'cax12', 'eeewewew'),
(16, '123', 'men', '85cd4a19b2f02e29e30ef55dcf8d871a.png', 15, 'men', '23'),
(17, '123', 'Women', '85cd4a19b2f02e29e30ef55dcf8d871a.png', 0, 'women', '23'),
(18, '12334', 'Children', '69c1e2121ea7294c26c92e719e2042ea.png', 0, 'children', 'eeewewew'),
(19, '12334', 'Children', '69c1e2121ea7294c26c92e719e2042ea.png', 0, 'children', 'eeewewew');

-- --------------------------------------------------------

--
-- Table structure for table `sma_brands`
--

CREATE TABLE `sma_brands` (
  `id` int(11) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `image` varchar(50) DEFAULT NULL,
  `slug` varchar(55) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_brands`
--

INSERT INTO `sma_brands` (`id`, `code`, `name`, `image`, `slug`, `description`) VALUES
(2, '002', 'Honst', NULL, 'honst', 'Honst'),
(3, 'ppi11', 'PHARMA', NULL, 'pharma', 'MFR');

-- --------------------------------------------------------

--
-- Table structure for table `sma_calendar`
--

CREATE TABLE `sma_calendar` (
  `id` int(11) NOT NULL,
  `title` varchar(55) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `start` datetime NOT NULL,
  `end` datetime DEFAULT NULL,
  `color` varchar(7) NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sma_captcha`
--

CREATE TABLE `sma_captcha` (
  `captcha_id` bigint(13) UNSIGNED NOT NULL,
  `captcha_time` int(10) UNSIGNED NOT NULL,
  `ip_address` varchar(16) CHARACTER SET latin1 NOT NULL DEFAULT '0',
  `word` varchar(20) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sma_cart`
--

CREATE TABLE `sma_cart` (
  `id` varchar(40) NOT NULL,
  `time` varchar(30) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `data` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_cart`
--

INSERT INTO `sma_cart` (`id`, `time`, `user_id`, `data`) VALUES
('15baea7009acc6f3d5513f9618476d12', '1670658036', NULL, '{\"cart_total\":113,\"total_item_tax\":0,\"total_items\":1,\"total_unique_items\":1,\"a36748431a6be989951e42439ab17d03\":{\"id\":\"383258270bce30602b4fb6ae6961ac7c\",\"product_id\":\"9\",\"qty\":1,\"name\":\"Flex\",\"slug\":\"flex\",\"code\":\"8057506630066\",\"price\":113,\"tax\":\"0.00\",\"image\":\"a3ddd2b15c1e63e37a363658aa87370c.jpg\",\"option\":false,\"options\":null,\"rowid\":\"a36748431a6be989951e42439ab17d03\",\"row_tax\":\"0.0000\",\"subtotal\":\"113.0000\"}}'),
('1694ae72f64b5b70f6545c4884b6c8e4', '1669809462', NULL, '{\"cart_total\":83,\"total_item_tax\":0,\"total_items\":1,\"total_unique_items\":1,\"95f8aaed86cc99df99598ee6c5e4df12\":{\"id\":\"470e1485cc208b2d5fdd50895bffa200\",\"product_id\":\"8\",\"qty\":1,\"name\":\"Grow Hair Nails\",\"slug\":\"grow-hair-nails\",\"code\":\"8057506630011\",\"price\":83,\"tax\":\"0.00\",\"image\":\"cb582f21a6054d970f8c8b6b939e80b6.jpg\",\"option\":false,\"options\":null,\"rowid\":\"95f8aaed86cc99df99598ee6c5e4df12\",\"row_tax\":\"0.0000\",\"subtotal\":\"83.0000\"}}'),
('1c1b487f9671e70471bc4fee9639d02e', '1671711930', NULL, '{\"cart_total\":100,\"total_item_tax\":0,\"total_items\":1,\"total_unique_items\":1,\"0cf624ddb5bfc87ded494c32f4249c87\":{\"id\":\"eccbc87e4b5ce2fe28308fd9f2a7baf3\",\"product_id\":\"3\",\"qty\":1,\"name\":\"Test Product 11\",\"slug\":\"test-product-11\",\"code\":\"TPR-11\",\"price\":100,\"tax\":\"0\",\"image\":\"no_image.png\",\"option\":\"3\",\"options\":[{\"id\":\"3\",\"name\":\"Black\",\"price\":null,\"total_quantity\":\"18.0000\",\"quantity\":\"5.0000\"}],\"rowid\":\"0cf624ddb5bfc87ded494c32f4249c87\",\"row_tax\":\"0.0000\",\"subtotal\":\"100.0000\"}}'),
('32e5a9b3454215929abaccd342d407f1', '1671781135', NULL, '{\"cart_total\":460,\"total_item_tax\":0,\"total_items\":1,\"total_unique_items\":1,\"7fc21e4748152389f7d6fab971eeddd0\":{\"id\":\"4e732ced3463d06de0ca9a15b6153677\",\"product_id\":\"26\",\"qty\":1,\"name\":\"amr\",\"slug\":\"amr\",\"code\":\"3w3w3\",\"price\":460,\"tax\":\"0\",\"image\":\"no_image.png\",\"option\":\"22\",\"options\":[{\"id\":\"22\",\"name\":\"30 TABs\",\"price\":\"230.0000\",\"total_quantity\":\"183.0000\",\"quantity\":\"79.0000\"},{\"id\":\"23\",\"name\":\"10mg\",\"price\":\"110.0000\",\"total_quantity\":\"100.0000\",\"quantity\":\"50.0000\"}],\"rowid\":\"7fc21e4748152389f7d6fab971eeddd0\",\"row_tax\":\"0.0000\",\"subtotal\":\"460.0000\"}}'),
('35699396865e91ec25292dd822caa20f', '1670569919', NULL, '{\"cart_total\":200,\"total_item_tax\":0,\"total_items\":5,\"total_unique_items\":5,\"f2e603ce81fee7fa17492097c38a6a1b\":{\"id\":\"82731168274e6f15f9777aafe4a1681f\",\"product_id\":\"1\",\"qty\":1,\"name\":\"Aloe Vera Juice [Pineapple Flavour] &ndash; Natural Hydrator, Better Liver Function &amp; Nutritious Booster\",\"slug\":\"aloe-vera-juice\",\"code\":\"1234567879\",\"price\":40,\"tax\":\"0.00\",\"image\":\"6cf2aead509142f3a4fc1130922d15c0.png\",\"option\":false,\"options\":null,\"rowid\":\"f2e603ce81fee7fa17492097c38a6a1b\",\"row_tax\":\"0.0000\",\"subtotal\":\"40.0000\"},\"edaece48b5596ab6502dbc92584f13fe\":{\"id\":\"26e27c1aeece5fd374d0152b82e01a5f\",\"product_id\":\"1\",\"qty\":1,\"name\":\"Aloe Vera Juice [Pineapple Flavour] &ndash; Natural Hydrator, Better Liver Function &amp; Nutritious Booster\",\"slug\":\"aloe-vera-juice\",\"code\":\"1234567879\",\"price\":40,\"tax\":\"0.00\",\"image\":\"6cf2aead509142f3a4fc1130922d15c0.png\",\"option\":false,\"options\":null,\"rowid\":\"edaece48b5596ab6502dbc92584f13fe\",\"row_tax\":\"0.0000\",\"subtotal\":\"40.0000\"},\"eecad7f877d8cc9f7d0e5bd6244481ca\":{\"id\":\"7bbb7770c312eaaf7b7e829ae0546057\",\"product_id\":\"1\",\"qty\":1,\"name\":\"Aloe Vera Juice [Pineapple Flavour] &ndash; Natural Hydrator, Better Liver Function &amp; Nutritious Booster\",\"slug\":\"aloe-vera-juice\",\"code\":\"1234567879\",\"price\":40,\"tax\":\"0.00\",\"image\":\"6cf2aead509142f3a4fc1130922d15c0.png\",\"option\":false,\"options\":null,\"rowid\":\"eecad7f877d8cc9f7d0e5bd6244481ca\",\"row_tax\":\"0.0000\",\"subtotal\":\"40.0000\"},\"07d65a83b49c010662b32595000fe1d2\":{\"id\":\"48fa1f44eeb0611dd3804ddfdcf840e9\",\"product_id\":\"1\",\"qty\":1,\"name\":\"Aloe Vera Juice [Pineapple Flavour] &ndash; Natural Hydrator, Better Liver Function &amp; Nutritious Booster\",\"slug\":\"aloe-vera-juice\",\"code\":\"1234567879\",\"price\":40,\"tax\":\"0.00\",\"image\":\"6cf2aead509142f3a4fc1130922d15c0.png\",\"option\":false,\"options\":null,\"rowid\":\"07d65a83b49c010662b32595000fe1d2\",\"row_tax\":\"0.0000\",\"subtotal\":\"40.0000\"},\"a932bed5e49cfa8686b87798a8e635fc\":{\"id\":\"bcb2098e8b0f900cbc57172ceeae863d\",\"product_id\":\"1\",\"qty\":1,\"name\":\"Aloe Vera Juice [Pineapple Flavour] &ndash; Natural Hydrator, Better Liver Function &amp; Nutritious Booster\",\"slug\":\"aloe-vera-juice\",\"code\":\"1234567879\",\"price\":40,\"tax\":\"0.00\",\"image\":\"6cf2aead509142f3a4fc1130922d15c0.png\",\"option\":false,\"options\":null,\"rowid\":\"a932bed5e49cfa8686b87798a8e635fc\",\"row_tax\":\"0.0000\",\"subtotal\":\"40.0000\"}}'),
('3b116890d80cff800db7f97376538b17', '1674557181', 36, '{\"cart_total\":230,\"total_item_tax\":0,\"total_items\":1,\"total_unique_items\":1,\"e06cdec7553322fddbf86e84687f25f2\":{\"id\":\"17e62166fc8586dfa4d1bc0e1742c08b\",\"product_id\":\"43\",\"qty\":1,\"name\":\"SULFAD 1GM\",\"slug\":\"sulfad-1gm\",\"code\":\"PDS004\",\"price\":230,\"tax\":\"0\",\"image\":\"dd22fc4600e730f8e5cffb3985990f3c.jpg\",\"option\":\"41\",\"options\":[{\"id\":\"41\",\"name\":\"EXPIRY DATE\",\"price\":\"0.0000\",\"total_quantity\":\"10113.0000\",\"quantity\":\"10113.0000\"}],\"rowid\":\"e06cdec7553322fddbf86e84687f25f2\",\"row_tax\":\"0.0000\",\"subtotal\":\"230.0000\"}}'),
('45347f82f8ae4211e6c064d96deca9ad', '1670321870', NULL, '{\"cart_total\":45,\"total_item_tax\":0,\"total_items\":1,\"total_unique_items\":1,\"a78ec44e8ee8b75f233714ae8ed882c3\":{\"id\":\"4682f29414b9bd53e56cf37c8568f045\",\"product_id\":\"22\",\"qty\":1,\"name\":\"Black Seed\",\"slug\":\"black-seed\",\"code\":\"8057506630110\",\"price\":45,\"tax\":\"0.00\",\"image\":\"3ca10cac16cfa9cc3e38aa579f960403.jpg\",\"option\":false,\"options\":null,\"rowid\":\"a78ec44e8ee8b75f233714ae8ed882c3\",\"row_tax\":\"0.0000\",\"subtotal\":\"45.0000\"}}'),
('4d508cdd8fb045ca5aec2d5873704663', '1671716796', NULL, '{\"cart_total\":460,\"total_item_tax\":0,\"total_items\":1,\"total_unique_items\":1,\"f3ac5b0eb6bf00c08b85dab1f94d4b67\":{\"id\":\"4e732ced3463d06de0ca9a15b6153677\",\"product_id\":\"26\",\"qty\":1,\"name\":\"amr\",\"slug\":\"amr\",\"code\":\"3w3w3\",\"price\":460,\"tax\":\"0\",\"image\":\"no_image.png\",\"option\":\"22\",\"options\":[{\"id\":\"22\",\"name\":\"30 TABs\",\"price\":\"230.0000\",\"total_quantity\":\"93.0000\",\"quantity\":\"79.0000\"},{\"id\":\"23\",\"name\":\"10mg\",\"price\":\"110.0000\",\"total_quantity\":\"100.0000\",\"quantity\":\"50.0000\"}],\"rowid\":\"f3ac5b0eb6bf00c08b85dab1f94d4b67\",\"row_tax\":\"0.0000\",\"subtotal\":\"460.0000\"}}'),
('66a1d37410701760c0949a7c925c373b', '1669795815', NULL, '{\"cart_total\":120,\"total_item_tax\":0,\"total_items\":2,\"total_unique_items\":2,\"de8cca3197504a1f8fbe031b9ae14082\":{\"id\":\"4db5775a601a6e62a35522bb9c12473e\",\"product_id\":\"13\",\"qty\":1,\"name\":\" Free Oxidant\",\"slug\":\"free-oxidant\",\"code\":\"8057506630233\",\"price\":60,\"tax\":\"0.00\",\"image\":\"f26755a51ebe90a908f845d46b6c978e.jpg\",\"option\":false,\"options\":null,\"rowid\":\"de8cca3197504a1f8fbe031b9ae14082\",\"row_tax\":\"0.0000\",\"subtotal\":\"60.0000\"},\"0fd86ef1a866aa83165b2c7dfa5d0579\":{\"id\":\"8bbebe02fa3061814975e89754fee1df\",\"product_id\":\"13\",\"qty\":1,\"name\":\" Free Oxidant\",\"slug\":\"free-oxidant\",\"code\":\"8057506630233\",\"price\":60,\"tax\":\"0.00\",\"image\":\"f26755a51ebe90a908f845d46b6c978e.jpg\",\"option\":false,\"options\":null,\"rowid\":\"0fd86ef1a866aa83165b2c7dfa5d0579\",\"row_tax\":\"0.0000\",\"subtotal\":\"60.0000\"}}'),
('7f57b93b200429daf4534c33448582b7', '1670409479', NULL, '{\"cart_total\":210,\"total_item_tax\":0,\"total_items\":2,\"total_unique_items\":2,\"edd7332ae80db0365d03ab8aa2cc09ea\":{\"id\":\"3d8cf689810a14ea4e2a83262a95cb32\",\"product_id\":\"11\",\"qty\":1,\"name\":\"L-Carnitine\",\"slug\":\"l-carnitine\",\"code\":\"8057506630158\",\"price\":105,\"tax\":\"0.00\",\"image\":\"e69124b504f0e0858dfe5e7813c9cafe.jpg\",\"option\":false,\"options\":null,\"rowid\":\"edd7332ae80db0365d03ab8aa2cc09ea\",\"row_tax\":\"0.0000\",\"subtotal\":\"105.0000\"},\"372336599964de589dffafdadfb9030c\":{\"id\":\"4a7facc4462614626ae24487183441b3\",\"product_id\":\"11\",\"qty\":1,\"name\":\"L-Carnitine\",\"slug\":\"l-carnitine\",\"code\":\"8057506630158\",\"price\":105,\"tax\":\"0.00\",\"image\":\"e69124b504f0e0858dfe5e7813c9cafe.jpg\",\"option\":false,\"options\":null,\"rowid\":\"372336599964de589dffafdadfb9030c\",\"row_tax\":\"0.0000\",\"subtotal\":\"105.0000\"}}'),
('82c0f6102390ea7617952b21cb6a224b', '1671177858', NULL, '{\"cart_total\":83,\"total_item_tax\":0,\"total_items\":1,\"total_unique_items\":1,\"815e6212def15fe76ed27cec7a393d59\":{\"id\":\"c9f0f895fb98ab9159f51fd0297e236d\",\"product_id\":\"8\",\"qty\":1,\"name\":\"Grow Hair Nails\",\"slug\":\"grow-hair-nails\",\"code\":\"8057506630011\",\"price\":83,\"tax\":\"0\",\"image\":\"cb582f21a6054d970f8c8b6b939e80b6.jpg\",\"option\":false,\"options\":null,\"rowid\":\"815e6212def15fe76ed27cec7a393d59\",\"row_tax\":\"0.0000\",\"subtotal\":\"83.0000\"}}'),
('8c3284389c6b3b20f74b7dbf2cb8ddfc', '1670833516', NULL, '{\"cart_total\":40,\"total_item_tax\":0,\"total_items\":1,\"total_unique_items\":1,\"28c8edde3d61a0411511d3b1866f0636\":{\"id\":\"c4ca4238a0b923820dcc509a6f75849b\",\"product_id\":\"1\",\"qty\":1,\"name\":\"Aloe Vera Juice [Pineapple Flavour] &ndash; Natural Hydrator, Better Liver Function &amp; Nutritious Booster\",\"slug\":\"aloe-vera-juice\",\"code\":\"1234567879\",\"price\":40,\"tax\":\"0\",\"image\":\"6cf2aead509142f3a4fc1130922d15c0.png\",\"option\":false,\"options\":null,\"rowid\":\"28c8edde3d61a0411511d3b1866f0636\",\"row_tax\":\"0.0000\",\"subtotal\":\"40.0000\"}}'),
('8cd0c3c9a52206cd050efcbbc8811c61', '1671191253', 10, '{\"cart_total\":217,\"total_item_tax\":0,\"total_items\":3,\"total_unique_items\":3,\"815e6212def15fe76ed27cec7a393d59\":{\"id\":\"c9f0f895fb98ab9159f51fd0297e236d\",\"product_id\":\"8\",\"qty\":1,\"name\":\"Grow Hair Nails\",\"slug\":\"grow-hair-nails\",\"code\":\"8057506630011\",\"price\":83,\"tax\":\"0\",\"image\":\"cb582f21a6054d970f8c8b6b939e80b6.jpg\",\"option\":false,\"options\":null,\"rowid\":\"815e6212def15fe76ed27cec7a393d59\",\"row_tax\":\"0.0000\",\"subtotal\":\"83.0000\"},\"28c8edde3d61a0411511d3b1866f0636\":{\"id\":\"c4ca4238a0b923820dcc509a6f75849b\",\"product_id\":\"1\",\"qty\":1,\"name\":\"Aloe Vera Juice [Pineapple Flavour] &ndash; Natural Hydrator, Better Liver Function &amp; Nutritious Booster\",\"slug\":\"aloe-vera-juice\",\"code\":\"1234567879\",\"price\":40,\"tax\":\"0\",\"image\":\"6cf2aead509142f3a4fc1130922d15c0.png\",\"option\":false,\"options\":null,\"rowid\":\"28c8edde3d61a0411511d3b1866f0636\",\"row_tax\":\"0.0000\",\"subtotal\":\"40.0000\"},\"cd7fd1517e323f26c6f1b0b6b96e3b3d\":{\"id\":\"8f14e45fceea167a5a36dedd4bea2543\",\"product_id\":\"7\",\"qty\":1,\"name\":\"Anti Aging\",\"slug\":\"anti-aging\",\"code\":\"8057506630271\",\"price\":94,\"tax\":\"0\",\"image\":\"64c6205e417559cb48c8962602c0dcc3.jpg\",\"option\":false,\"options\":null,\"rowid\":\"cd7fd1517e323f26c6f1b0b6b96e3b3d\",\"row_tax\":\"0.0000\",\"subtotal\":\"94.0000\"}}'),
('ab3ab6dfad8a175963e663d97ebb3f08', '1670235103', 1, '{\"cart_total\":177,\"total_item_tax\":0,\"total_items\":2,\"total_unique_items\":2,\"ada85595081b37df78c37455a61ca30e\":{\"id\":\"3cb581313f7ccc5f657383d02173fe72\",\"product_id\":\"9\",\"qty\":1,\"name\":\"Flex\",\"slug\":\"flex\",\"code\":\"8057506630066\",\"price\":113,\"tax\":\"0.00\",\"image\":\"a3ddd2b15c1e63e37a363658aa87370c.jpg\",\"option\":false,\"options\":null,\"rowid\":\"ada85595081b37df78c37455a61ca30e\",\"row_tax\":\"0.0000\",\"subtotal\":\"113.0000\"},\"8a1742940025013ffab608cc112501e8\":{\"id\":\"1e837a17bf3c547fd91ca38f2411e105\",\"product_id\":\"16\",\"qty\":1,\"name\":\"Healthy Vasco\",\"slug\":\"healthy-vasco\",\"code\":\"8057506630394\",\"price\":64,\"tax\":\"0.00\",\"image\":\"7fcf1d4b5bf09bac094371eafbbe5813.jpg\",\"option\":false,\"options\":null,\"rowid\":\"8a1742940025013ffab608cc112501e8\",\"row_tax\":\"0.0000\",\"subtotal\":\"64.0000\"}}'),
('b0d37becaf605a0304db5d4ad91bb48e', '1669803066', NULL, '{\"cart_total\":233,\"total_item_tax\":0,\"total_items\":3,\"total_unique_items\":3,\"2cc92ebd2571d5fffdc037626660b03b\":{\"id\":\"1bf401c640d6bdf534dbc9bdf6b4e206\",\"product_id\":\"11\",\"qty\":1,\"name\":\"L-Carnitine\",\"slug\":\"l-carnitine\",\"code\":\"8057506630158\",\"price\":105,\"tax\":\"0.00\",\"image\":\"e69124b504f0e0858dfe5e7813c9cafe.jpg\",\"option\":false,\"options\":null,\"rowid\":\"2cc92ebd2571d5fffdc037626660b03b\",\"row_tax\":\"0.0000\",\"subtotal\":\"105.0000\"},\"00ad4df1aa8bd512621d05efe1b34418\":{\"id\":\"bae10f7158be6633651c20ca0008e335\",\"product_id\":\"25\",\"qty\":1,\"name\":\"Detoxy\",\"slug\":\"detoxy\",\"code\":\"8057506630240\",\"price\":64,\"tax\":\"0.00\",\"image\":\"64e2b9ee0558b9094d40b43e8a759f25.jpg\",\"option\":false,\"options\":null,\"rowid\":\"00ad4df1aa8bd512621d05efe1b34418\",\"row_tax\":\"0.0000\",\"subtotal\":\"64.0000\"},\"3db80ec82ae64c47edad24ef28b617df\":{\"id\":\"332af3270436e47dcae3be40348eb818\",\"product_id\":\"25\",\"qty\":1,\"name\":\"Detoxy\",\"slug\":\"detoxy\",\"code\":\"8057506630240\",\"price\":64,\"tax\":\"0.00\",\"image\":\"64e2b9ee0558b9094d40b43e8a759f25.jpg\",\"option\":false,\"options\":null,\"rowid\":\"3db80ec82ae64c47edad24ef28b617df\",\"row_tax\":\"0.0000\",\"subtotal\":\"64.0000\"}}'),
('b2022d1f328eb8c56eb8cfefb6f51918', '1669802933', NULL, '{\"cart_total\":60,\"total_item_tax\":0,\"total_items\":1,\"total_unique_items\":1,\"b77b98ac9ff6d259fe4df12aab3c8ea5\":{\"id\":\"ba16986c92a3833a1ba97cf78c57f851\",\"product_id\":\"13\",\"qty\":1,\"name\":\" Free Oxidant\",\"slug\":\"free-oxidant\",\"code\":\"8057506630233\",\"price\":60,\"tax\":\"0.00\",\"image\":\"f26755a51ebe90a908f845d46b6c978e.jpg\",\"option\":false,\"options\":null,\"rowid\":\"b77b98ac9ff6d259fe4df12aab3c8ea5\",\"row_tax\":\"0.0000\",\"subtotal\":\"60.0000\"}}'),
('b66dfe338894f1587407ba7991a23977', '1670243484', NULL, '{\"cart_total\":185,\"total_item_tax\":0,\"total_items\":3,\"total_unique_items\":3,\"803d6ae7b928f483d7d35c821b2a0633\":{\"id\":\"2aea6fbf6947f82206c66a533c9623cd\",\"product_id\":\"11\",\"qty\":1,\"name\":\"L-Carnitine\",\"slug\":\"l-carnitine\",\"code\":\"8057506630158\",\"price\":105,\"tax\":\"0.00\",\"image\":\"e69124b504f0e0858dfe5e7813c9cafe.jpg\",\"option\":false,\"options\":null,\"rowid\":\"803d6ae7b928f483d7d35c821b2a0633\",\"row_tax\":\"0.0000\",\"subtotal\":\"105.0000\"},\"665ed6851f0f51916b2006730366a367\":{\"id\":\"5cf040c6538cd966d338101fa858aeec\",\"product_id\":\"2\",\"qty\":1,\"name\":\"Anti &ndash; Acne Serum &ndash; Brightens Skin, Fades Acne, Lighten Acne Scars &amp; Control Excess Oil Production\",\"slug\":\"anti-acne-serum-brightens-skin-fades-acne-lighten-acne-\",\"code\":\"554212121\",\"price\":20,\"tax\":\"0.00\",\"image\":\"d318b69c761b2d1d61fdf1c02d79e969.jpg\",\"option\":false,\"options\":null,\"rowid\":\"665ed6851f0f51916b2006730366a367\",\"row_tax\":\"0.0000\",\"subtotal\":\"20.0000\"},\"44a36645f999f0ca3f43a08fa0e873cd\":{\"id\":\"7d97e54076e512999504f5d48c04a092\",\"product_id\":\"13\",\"qty\":1,\"name\":\" Free Oxidant\",\"slug\":\"free-oxidant\",\"code\":\"8057506630233\",\"price\":60,\"tax\":\"0.00\",\"image\":\"f26755a51ebe90a908f845d46b6c978e.jpg\",\"option\":false,\"options\":null,\"rowid\":\"44a36645f999f0ca3f43a08fa0e873cd\",\"row_tax\":\"0.0000\",\"subtotal\":\"60.0000\"}}'),
('bd634a30d3f9e52aac125354148f6994', '1669795926', NULL, '{\"cart_total\":60,\"total_item_tax\":0,\"total_items\":1,\"total_unique_items\":1,\"bdc9a6788f911c759557e4f8221eea75\":{\"id\":\"ce8a43ce70687bcdabdec004a9883b97\",\"product_id\":\"13\",\"qty\":1,\"name\":\" Free Oxidant\",\"slug\":\"free-oxidant\",\"code\":\"8057506630233\",\"price\":60,\"tax\":\"0.00\",\"image\":\"f26755a51ebe90a908f845d46b6c978e.jpg\",\"option\":false,\"options\":null,\"rowid\":\"bdc9a6788f911c759557e4f8221eea75\",\"row_tax\":\"0.0000\",\"subtotal\":\"60.0000\"}}'),
('bf523ab5fdda536c4d7cce80106989f1', '1669209616', NULL, '{\"cart_total\":45,\"total_item_tax\":0,\"total_items\":1,\"total_unique_items\":1,\"8dd2a7ba71a9b19ade9b218a04d615b1\":{\"id\":\"777f7e369bb4899c6be2a0933bdc055c\",\"product_id\":\"22\",\"qty\":1,\"name\":\"Black Seed\",\"slug\":\"black-seed\",\"code\":\"8057506630110\",\"price\":45,\"tax\":\"0.00\",\"image\":\"3ca10cac16cfa9cc3e38aa579f960403.jpg\",\"option\":false,\"options\":null,\"rowid\":\"8dd2a7ba71a9b19ade9b218a04d615b1\",\"row_tax\":\"0.0000\",\"subtotal\":\"45.0000\"}}'),
('c67b822e48f0f4e1840902f016fb94fb', '1669810480', NULL, '{\"cart_total\":299,\"total_item_tax\":0,\"total_items\":4,\"total_unique_items\":3,\"2cbac3b02477f561921c14a7875f9c8d\":{\"id\":\"8651f0b5f0a8a62e38715df8ca064966\",\"product_id\":\"18\",\"qty\":1,\"name\":\"Men&rsquo;s Fertility\",\"slug\":\"mens-fertility\",\"code\":\"8057506630042\",\"price\":113,\"tax\":\"0.00\",\"image\":\"3cbe39646029aed6fac02fe19ece9b36.jpg\",\"option\":false,\"options\":null,\"rowid\":\"2cbac3b02477f561921c14a7875f9c8d\",\"row_tax\":\"0.0000\",\"subtotal\":\"113.0000\"},\"accc5250790a46df83f845474c862f2a\":{\"id\":\"f6732c8320a49f01ec5fcc7526852f4a\",\"product_id\":\"8\",\"qty\":2,\"name\":\"Grow Hair Nails\",\"slug\":\"grow-hair-nails\",\"code\":\"8057506630011\",\"price\":83,\"tax\":\"0.00\",\"image\":\"cb582f21a6054d970f8c8b6b939e80b6.jpg\",\"option\":false,\"options\":null,\"rowid\":\"accc5250790a46df83f845474c862f2a\",\"row_tax\":\"0.0000\",\"subtotal\":\"166.0000\"},\"dbbc9190090b63dd44c642a71ff9aedb\":{\"id\":\"ff9ef038d0434f8275892a23bdc8f344\",\"product_id\":\"2\",\"qty\":1,\"name\":\"Anti &ndash; Acne Serum &ndash; Brightens Skin, Fades Acne, Lighten Acne Scars &amp; Control Excess Oil Production\",\"slug\":\"anti-acne-serum-brightens-skin-fades-acne-lighten-acne-\",\"code\":\"554212121\",\"price\":20,\"tax\":\"0.00\",\"image\":\"d318b69c761b2d1d61fdf1c02d79e969.jpg\",\"option\":false,\"options\":null,\"rowid\":\"dbbc9190090b63dd44c642a71ff9aedb\",\"row_tax\":\"0.0000\",\"subtotal\":\"20.0000\"}}'),
('c70f9b1382298d0e1b034d01693743f6', '1674115535', NULL, '{\"cart_total\":230,\"total_item_tax\":0,\"total_items\":1,\"total_unique_items\":1,\"e06cdec7553322fddbf86e84687f25f2\":{\"id\":\"17e62166fc8586dfa4d1bc0e1742c08b\",\"product_id\":\"43\",\"qty\":1,\"name\":\"SULFAD 1GM\",\"slug\":\"sulfad-1gm\",\"code\":\"PDS004\",\"price\":230,\"tax\":\"0\",\"image\":\"dd22fc4600e730f8e5cffb3985990f3c.jpg\",\"option\":\"41\",\"options\":[{\"id\":\"41\",\"name\":\"EXPIRY DATE\",\"price\":\"0.0000\",\"total_quantity\":\"10113.0000\",\"quantity\":\"10113.0000\"}],\"rowid\":\"e06cdec7553322fddbf86e84687f25f2\",\"row_tax\":\"0.0000\",\"subtotal\":\"230.0000\"}}'),
('cb1790e85bab2ce2e2efe4c7658ee952', '1669894040', NULL, '{\"cart_total\":143,\"total_item_tax\":0,\"total_items\":2,\"total_unique_items\":2,\"a68f3e36818e22ef2fc9df2c8eae55ae\":{\"id\":\"8563218d9d28a09c05cf7f841352925f\",\"product_id\":\"13\",\"qty\":1,\"name\":\" Free Oxidant\",\"slug\":\"free-oxidant\",\"code\":\"8057506630233\",\"price\":60,\"tax\":\"0.00\",\"image\":\"f26755a51ebe90a908f845d46b6c978e.jpg\",\"option\":false,\"options\":null,\"rowid\":\"a68f3e36818e22ef2fc9df2c8eae55ae\",\"row_tax\":\"0.0000\",\"subtotal\":\"60.0000\"},\"40e33bd2c0af38bfc698413a8f130b13\":{\"id\":\"84854fa7b6b64754234d0a8a6ed1e28c\",\"product_id\":\"8\",\"qty\":1,\"name\":\"Grow Hair Nails\",\"slug\":\"grow-hair-nails\",\"code\":\"8057506630011\",\"price\":83,\"tax\":\"0.00\",\"image\":\"cb582f21a6054d970f8c8b6b939e80b6.jpg\",\"option\":false,\"options\":null,\"rowid\":\"40e33bd2c0af38bfc698413a8f130b13\",\"row_tax\":\"0.0000\",\"subtotal\":\"83.0000\"}}'),
('d2dd23136d5318799d6032041447dd69', '1672474435', 1, '{\"cart_total\":660,\"total_item_tax\":0,\"total_items\":3,\"total_unique_items\":2,\"caea1ee052b10daef730d1ef80be9239\":{\"id\":\"33e75ff09dd601bbe69f351039152189\",\"product_id\":\"28\",\"qty\":1,\"name\":\"sulfad2\",\"slug\":\"sulfad2\",\"code\":\"5765765\",\"price\":200,\"tax\":\"0\",\"image\":\"no_image.png\",\"option\":\"19\",\"options\":[{\"id\":\"19\",\"name\":\"30 TABs\",\"price\":\"0.0000\",\"total_quantity\":\"98.0000\",\"quantity\":\"98.0000\"},{\"id\":\"21\",\"name\":\"10mg\",\"price\":\"0.0000\",\"total_quantity\":\"260.0000\",\"quantity\":\"250.0000\"}],\"rowid\":\"caea1ee052b10daef730d1ef80be9239\",\"row_tax\":\"0.0000\",\"subtotal\":\"200.0000\"},\"02171e9baf5b164dc210fbe6f8499a56\":{\"id\":\"17e62166fc8586dfa4d1bc0e1742c08b\",\"product_id\":\"43\",\"qty\":2,\"name\":\"SULFAD 1GM\",\"slug\":\"sulfad-1gm\",\"code\":\"PDS004\",\"price\":230,\"tax\":\"0\",\"image\":\"dd22fc4600e730f8e5cffb3985990f3c.jpg\",\"option\":\"41\",\"options\":[{\"id\":\"41\",\"name\":\"EXPIRY DATE\",\"price\":\"0.0000\",\"total_quantity\":\"14236.0000\",\"quantity\":\"7118.0000\"}],\"rowid\":\"02171e9baf5b164dc210fbe6f8499a56\",\"row_tax\":\"0.0000\",\"subtotal\":\"460.0000\"}}'),
('d4885254a3f6b7231ae4fde2c48a4274', '1670505742', NULL, '{\"cart_total\":113,\"total_item_tax\":0,\"total_items\":1,\"total_unique_items\":1,\"d4e9a9b3d840af92b1112db33f1217ae\":{\"id\":\"fe443f225ffd837d990520a7e47ac68d\",\"product_id\":\"9\",\"qty\":1,\"name\":\"Flex\",\"slug\":\"flex\",\"code\":\"8057506630066\",\"price\":113,\"tax\":\"0.00\",\"image\":\"a3ddd2b15c1e63e37a363658aa87370c.jpg\",\"option\":false,\"options\":null,\"rowid\":\"d4e9a9b3d840af92b1112db33f1217ae\",\"row_tax\":\"0.0000\",\"subtotal\":\"113.0000\"}}'),
('e1761a574087943d478e2c730c468227', '1669870619', NULL, '{\"cart_total\":128,\"total_item_tax\":0,\"total_items\":2,\"total_unique_items\":2,\"7f2234e7f716be9a58dd7a9c9b712bc3\":{\"id\":\"33f23d867087b43819c244fb4b661e76\",\"product_id\":\"21\",\"qty\":1,\"name\":\"Ashwagandha\",\"slug\":\"ashwagandha\",\"code\":\"8057506630295\",\"price\":45,\"tax\":\"0.00\",\"image\":\"e3735f5c1e66a4e92c56624f748e3dbe.jpg\",\"option\":false,\"options\":null,\"rowid\":\"7f2234e7f716be9a58dd7a9c9b712bc3\",\"row_tax\":\"0.0000\",\"subtotal\":\"45.0000\"},\"dcad85245099833e4b57b4ea55e76064\":{\"id\":\"fb42e77517a66001bf1db9bc44fdf3ac\",\"product_id\":\"8\",\"qty\":1,\"name\":\"Grow Hair Nails\",\"slug\":\"grow-hair-nails\",\"code\":\"8057506630011\",\"price\":83,\"tax\":\"0.00\",\"image\":\"cb582f21a6054d970f8c8b6b939e80b6.jpg\",\"option\":false,\"options\":null,\"rowid\":\"dcad85245099833e4b57b4ea55e76064\",\"row_tax\":\"0.0000\",\"subtotal\":\"83.0000\"}}'),
('e78c59c5d31eda2173e241629c3008d0', '1670658011', NULL, '{\"cart_total\":159,\"total_item_tax\":0,\"total_items\":4,\"total_unique_items\":4,\"0cde83d647413daea8bb30cf90de286f\":{\"id\":\"c517310a3d37946164022ac1397da53d\",\"product_id\":\"2\",\"qty\":1,\"name\":\"Anti &ndash; Acne Serum &ndash; Brightens Skin, Fades Acne, Lighten Acne Scars &amp; Control Excess Oil Production\",\"slug\":\"anti-acne-serum-brightens-skin-fades-acne-lighten-acne-\",\"code\":\"554212121\",\"price\":20,\"tax\":\"0.00\",\"image\":\"d318b69c761b2d1d61fdf1c02d79e969.jpg\",\"option\":false,\"options\":null,\"rowid\":\"0cde83d647413daea8bb30cf90de286f\",\"row_tax\":\"0.0000\",\"subtotal\":\"20.0000\"},\"09bf27b25796817f13a09c6a8f90ff39\":{\"id\":\"0e5edbb96ff77169195df21e791b0364\",\"product_id\":\"15\",\"qty\":1,\"name\":\"CoEnzyme\",\"slug\":\"coenzyme\",\"code\":\"8057506630097\",\"price\":44,\"tax\":\"0.00\",\"image\":\"86d46a11b747ea32404eeec740215cd7.jpg\",\"option\":false,\"options\":null,\"rowid\":\"09bf27b25796817f13a09c6a8f90ff39\",\"row_tax\":\"0.0000\",\"subtotal\":\"44.0000\"},\"980ee3749c78882cf99ecf8bf118822a\":{\"id\":\"55775f0dcbf30c435cc76ac8141e8368\",\"product_id\":\"17\",\"qty\":1,\"name\":\"Maximum Power\",\"slug\":\"maximum-power\",\"code\":\"8057506630301\",\"price\":75,\"tax\":\"0.00\",\"image\":\"b8c322a673c85dc96c2a67305a36341d.jpg\",\"option\":false,\"options\":null,\"rowid\":\"980ee3749c78882cf99ecf8bf118822a\",\"row_tax\":\"0.0000\",\"subtotal\":\"75.0000\"},\"3af4f56907c0ef849dce5875ddae312c\":{\"id\":\"46b1877b32961365e89e052f14d42995\",\"product_id\":\"2\",\"qty\":1,\"name\":\"Anti &ndash; Acne Serum &ndash; Brightens Skin, Fades Acne, Lighten Acne Scars &amp; Control Excess Oil Production\",\"slug\":\"anti-acne-serum-brightens-skin-fades-acne-lighten-acne-\",\"code\":\"554212121\",\"price\":20,\"tax\":\"0.00\",\"image\":\"d318b69c761b2d1d61fdf1c02d79e969.jpg\",\"option\":false,\"options\":null,\"rowid\":\"3af4f56907c0ef849dce5875ddae312c\",\"row_tax\":\"0.0000\",\"subtotal\":\"20.0000\"}}'),
('f360a9e09e0e39c78a1a11ae6c163da5', '1671100600', NULL, '{\"cart_total\":60,\"total_item_tax\":0,\"total_items\":1,\"total_unique_items\":1,\"6f181f206b8555c5dc619bc206ab35ad\":{\"id\":\"37693cfc748049e45d87b8c7d8b9aacd\",\"product_id\":\"23\",\"qty\":1,\"name\":\"Beauty Combo\",\"slug\":\"93555055\",\"code\":\"93555055\",\"price\":60,\"tax\":\"0\",\"image\":\"no_image.png\",\"option\":false,\"options\":null,\"rowid\":\"6f181f206b8555c5dc619bc206ab35ad\",\"row_tax\":\"0.0000\",\"subtotal\":\"60.0000\"}}'),
('f76c9b6c59734034ccc7bcc8a7545b73', '1669895637', NULL, '{\"cart_total\":75,\"total_item_tax\":0,\"total_items\":1,\"total_unique_items\":1,\"0edb04e39ed02cae806ab5741588b949\":{\"id\":\"5f5c425d1517b629f299608e27162cbc\",\"product_id\":\"17\",\"qty\":1,\"name\":\"Maximum Power\",\"slug\":\"maximum-power\",\"code\":\"8057506630301\",\"price\":75,\"tax\":\"0.00\",\"image\":\"b8c322a673c85dc96c2a67305a36341d.jpg\",\"option\":false,\"options\":null,\"rowid\":\"0edb04e39ed02cae806ab5741588b949\",\"row_tax\":\"0.0000\",\"subtotal\":\"75.0000\"}}'),
('fbadc41983e7947e2bc64f24da28758b', '1671604424', NULL, '{\"cart_total\":460,\"total_item_tax\":0,\"total_items\":2,\"total_unique_items\":1,\"09b12ab84ec0bbdfc4d9a46ecc6eef41\":{\"id\":\"e369853df766fa44e1ed0ff613f563bd\",\"product_id\":\"34\",\"qty\":2,\"name\":\"SULFAD3\",\"slug\":\"64969409\",\"code\":\"64969409\",\"price\":230,\"tax\":\"0\",\"image\":\"no_image.png\",\"option\":\"37\",\"options\":[{\"id\":\"37\",\"name\":\"30 TABs\",\"price\":\"0.0000\",\"total_quantity\":\"500.0000\",\"quantity\":\"100.0000\"},{\"id\":\"38\",\"name\":\"15mg\",\"price\":\"110.0000\",\"total_quantity\":\"500.0000\",\"quantity\":\"100.0000\"}],\"rowid\":\"09b12ab84ec0bbdfc4d9a46ecc6eef41\",\"row_tax\":\"0.0000\",\"subtotal\":\"460.0000\"}}');

-- --------------------------------------------------------

--
-- Table structure for table `sma_categories`
--

CREATE TABLE `sma_categories` (
  `id` int(11) NOT NULL,
  `code` varchar(55) NOT NULL,
  `name` varchar(55) NOT NULL,
  `image` varchar(55) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `slug` varchar(55) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_categories`
--

INSERT INTO `sma_categories` (`id`, `code`, `name`, `image`, `parent_id`, `slug`, `description`) VALUES
(14, 'HERB', 'HERBAL', NULL, 0, 'herbal', 'Herbal'),
(15, 'PHARMA', 'PHARMACEUTICAL', NULL, 0, 'pharmaceutical', 'PHARMACEUTICAL'),
(16, 'MED', 'MEDICAL', NULL, 0, 'medical', 'MEDICAL SUPPLIES');

-- --------------------------------------------------------

--
-- Table structure for table `sma_combo_items`
--

CREATE TABLE `sma_combo_items` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `item_code` varchar(20) NOT NULL,
  `quantity` decimal(12,4) NOT NULL,
  `unit_price` decimal(25,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_combo_items`
--

INSERT INTO `sma_combo_items` (`id`, `product_id`, `item_code`, `quantity`, `unit_price`) VALUES
(3, 23, '554212121', '1.0000', '20.0000'),
(4, 23, '1234567879', '1.0000', '40.0000');

-- --------------------------------------------------------

--
-- Table structure for table `sma_companies`
--

CREATE TABLE `sma_companies` (
  `id` int(11) NOT NULL,
  `group_id` int(10) UNSIGNED DEFAULT NULL,
  `group_name` varchar(20) NOT NULL,
  `customer_group_id` int(11) DEFAULT NULL,
  `customer_group_name` varchar(100) DEFAULT NULL,
  `name` varchar(55) NOT NULL,
  `company` varchar(255) NOT NULL,
  `vat_no` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(55) DEFAULT NULL,
  `state` varchar(55) DEFAULT NULL,
  `postal_code` varchar(8) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `cf1` varchar(100) DEFAULT NULL,
  `cf2` varchar(100) DEFAULT NULL,
  `cf3` varchar(100) DEFAULT NULL,
  `cf4` varchar(100) DEFAULT NULL,
  `cf5` varchar(100) DEFAULT NULL,
  `cf6` varchar(100) DEFAULT NULL,
  `invoice_footer` text DEFAULT NULL,
  `payment_term` int(11) DEFAULT 0,
  `logo` varchar(255) DEFAULT 'logo.png',
  `award_points` int(11) DEFAULT 0,
  `deposit_amount` decimal(25,4) DEFAULT NULL,
  `price_group_id` int(11) DEFAULT NULL,
  `price_group_name` varchar(50) DEFAULT NULL,
  `gst_no` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_companies`
--

INSERT INTO `sma_companies` (`id`, `group_id`, `group_name`, `customer_group_id`, `customer_group_name`, `name`, `company`, `vat_no`, `address`, `city`, `state`, `postal_code`, `country`, `phone`, `email`, `cf1`, `cf2`, `cf3`, `cf4`, `cf5`, `cf6`, `invoice_footer`, `payment_term`, `logo`, `award_points`, `deposit_amount`, `price_group_id`, `price_group_name`, `gst_no`) VALUES
(57, 4, 'supplier', NULL, NULL, 'PPI', 'PHARMA PHARMACEUTICAL INDUSTRIES', '302003214700003', 'second industrial zone- Alkharj road', 'RIYADH', '', '', 'SAUDI ARABIA', '+966566076544', 'info@pharma.com.sa', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, NULL, NULL, ''),
(291, 3, 'customer', 1, 'General', 'Aali Albahar Medical Store', 'Aali Albahar Medical Store', '310103000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(292, 3, 'customer', 1, 'General', 'Abdul Karim Al Hakamy Pharmacy', 'Abdul Karim Al Hakamy Pharmacy', '300986000000000', '??????? ????????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(293, 3, 'customer', 1, 'General', 'Abdul Latif Jameel Hospital', 'Abdul Latif Jameel Hospital', '0', '????? ? ??? 23341', '', '', '23341', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(294, 3, 'customer', 1, 'General', 'Abha Hospital', 'Abha Hospital', '310531000000000', '??????? ????????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(295, 3, 'customer', 1, 'General', 'Adam Medical Co.', 'Adam Medical Co.', '310094000000000', '?????? 12811 2071???? ??? 2239', '', '', '12811', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(296, 3, 'customer', 1, 'General', 'Adel Pharmacies', 'Adel Pharmacies', '300419000000000', 'Qassim  ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(297, 3, 'customer', 1, 'General', 'Advanced Medical University Pathways co', 'Advanced Medical University Pathways co', '311004000000000', 'Eastern Region??????? ???????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(298, 3, 'customer', 1, 'General', 'Al-Hamaied Store.', 'Al-Hamaied Store.', '300264000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(299, 3, 'customer', 1, 'General', 'Al-Salam Medical Comflex', 'Al-Salam Medical Comflex', '310877000000000', 'Jeddah ???', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(300, 3, 'customer', 1, 'General', 'Al-Salhi pharmacy', 'Al-Salhi pharmacy', '300518000000000', '???????  Madinah', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(301, 3, 'customer', 1, 'General', 'Al Abeer international Medical Co', 'Al Abeer international Medical Co', '300209000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(302, 3, 'customer', 1, 'General', 'Al Amal Polyclinic', 'Al Amal Polyclinic', '0', 'Yanbu - ???? ?????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(303, 3, 'customer', 1, 'General', 'Al Atfeen Alhadisha Pharmacy', 'Al Atfeen Alhadisha Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(304, 3, 'customer', 1, 'General', 'Al Badr  Pharmacy Co', 'Al Badr  Pharmacy Co', '300225000000000', 'Makkah  ??? ???????', '', '', '51870', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(305, 3, 'customer', 1, 'General', 'Al Dawaa Medical Co Ltd', 'Al Dawaa Medical Co Ltd', '300545000000000', 'Al Khobar 31952 ?????', '', '', '31952', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(306, 3, 'customer', 1, 'General', 'Al Emeis Pharmacies', 'Al Emeis Pharmacies', '310471000000000', '???? ????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(307, 3, 'customer', 1, 'General', 'Al Faraby Medical Center Company', 'Al Faraby Medical Center Company', '300566000000000', '??????  Dammam', '', '', '32257', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(308, 3, 'customer', 1, 'General', 'Al Ghadeer Pharmacy', 'Al Ghadeer Pharmacy', '301371000000000', 'Dammam ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(309, 3, 'customer', 1, 'General', 'Al Hammadi Hospital', 'Al Hammadi Hospital', '0', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(310, 3, 'customer', 1, 'General', 'Al Hamra Hospital Pharmacy Drug Adv Co.', 'Al Hamra Hospital Pharmacy Drug Adv Co.', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(311, 3, 'customer', 1, 'General', 'Al Hayat Alwatany Co', 'Al Hayat Alwatany Co', '300050000000000', 'Riyadh 11521 ??????', '', '', '11521', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(312, 3, 'customer', 1, 'General', 'Al Hayat National Hospital Jizan', 'Al Hayat National Hospital Jizan', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(313, 3, 'customer', 1, 'General', 'Al Jazea Medical Co', 'Al Jazea Medical Co', '300057000000000', '?? ????? ???? ??? ?????? ????', '', '', '14266', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(314, 3, 'customer', 1, 'General', 'Al Jazeera Pharmacy', 'Al Jazeera Pharmacy', '310225000000000', 'Khobar ????? ?????', '', '', '31952', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(315, 3, 'customer', 1, 'General', 'Al Jedaany Hospital', 'Al Jedaany Hospital', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(316, 3, 'customer', 1, 'General', 'Al karama Pharmacy', 'Al karama Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(317, 3, 'customer', 1, 'General', 'Al Kordy pharmacy', 'Al Kordy pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(318, 3, 'customer', 1, 'General', 'Al Marja Medical Pharmacy', 'Al Marja Medical Pharmacy', '301284000000000', 'Jeddah ?? ??????? , ????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(319, 3, 'customer', 1, 'General', 'Al Mawared Medical Company', 'Al Mawared Medical Company', '310174000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(320, 3, 'customer', 1, 'General', 'Al Mohanna Pharmacies Group', 'Al Mohanna Pharmacies Group', '300848000000000', '??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(321, 3, 'customer', 1, 'General', 'Al Mubarak Pharmacy', 'Al Mubarak Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(322, 3, 'customer', 1, 'General', 'Al Nahdi Medical Company', 'Al Nahdi Medical Company', '300172000000000', '???? ???????, ??? 23715- 3985.', '', '', '23715', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(323, 3, 'customer', 1, 'General', 'Al Namouzagia Al Tebiah Pharmacy', 'Al Namouzagia Al Tebiah Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(324, 3, 'customer', 1, 'General', 'Al Nasr Modern Trading Company', 'Al Nasr Modern Trading Company', '311204000000000', '???  Makkah', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(325, 3, 'customer', 1, 'General', 'Al Noor Saudi Arabia', 'Al Noor Saudi Arabia', '123456789', 'Makkah ???', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(326, 3, 'customer', 1, 'General', 'Al Nour Pharmacy', 'Al Nour Pharmacy', '310868000000000', 'Dammam 31412 ??????', '', '', '31412', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(327, 3, 'customer', 1, 'General', 'Al Safa Almasiya Pharmacy', 'Al Safa Almasiya Pharmacy', '301304000000000', 'Hail ???? ?? ???????', '', '', '81415', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(328, 3, 'customer', 1, 'General', 'Al Salman Pharmacy', 'Al Salman Pharmacy', '300565000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(329, 3, 'customer', 1, 'General', 'Al Seha Alkhassa Pharmacy', 'Al Seha Alkhassa Pharmacy', '310415000000000', '????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(330, 3, 'customer', 1, 'General', 'Al Shablan Medical Center', 'Al Shablan Medical Center', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(331, 3, 'customer', 1, 'General', 'Al Sobhiaa Trading Est', 'Al Sobhiaa Trading Est', '300014000000000', 'Riyadh', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(332, 3, 'customer', 1, 'General', 'Al Takhsusi Pharmacy', 'Al Takhsusi Pharmacy', '300039000000000', '?????? Riyadh', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(333, 3, 'customer', 1, 'General', 'Al Tameez Pharmacy Taif Pharmaceuticals', 'Al Tameez Pharmacy Taif Pharmaceuticals', '300593000000000', 'Taif ??????', '', '', '39253', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(334, 3, 'customer', 1, 'General', 'Al Tawafeq Pharmacy Group', 'Al Tawafeq Pharmacy Group', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(335, 3, 'customer', 1, 'General', 'Al Wasfah Pharmacies', 'Al Wasfah Pharmacies', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(336, 3, 'customer', 1, 'General', 'Al Zahraa Hospital Dammam', 'Al Zahraa Hospital Dammam', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(337, 3, 'customer', 1, 'General', 'Al Zuha Pharmacy', 'Al Zuha Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(338, 3, 'customer', 1, 'General', 'Aldawaa al-naqi Pharmcy', 'Aldawaa al-naqi Pharmcy', '0', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(339, 3, 'customer', 1, 'General', 'Aleulyan Pharmacy', 'Aleulyan Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(340, 3, 'customer', 1, 'General', 'Alhabib Clinic', 'Alhabib Clinic', '310045000000000', 'Qassim   ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(341, 3, 'customer', 1, 'General', 'Ali Bin Ali Hospital', 'Ali Bin Ali Hospital', '310425000000000', '?????? 14515 ?? ????????', '', '', '14515', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(342, 3, 'customer', 1, 'General', 'Alif Thimar Medical Surgical Co', 'Alif Thimar Medical Surgical Co', '100618000000000', 'Business Bay, Dubai', '', '', 'P.O. Box', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(343, 3, 'customer', 1, 'General', 'Allewa\"A Pharmacy', 'Allewa\"A Pharmacy', '311045000000000', 'HAYIL ????', '', '', '1133', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(344, 3, 'customer', 1, 'General', 'Almostaqbal Specialized Medical', 'Almostaqbal Specialized Medical', '310135000000000', 'Jeddah    ???', '', '', 'P.O.BOX4', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(345, 3, 'customer', 1, 'General', 'Alnnasir Pharmacy', 'Alnnasir Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(346, 3, 'customer', 1, 'General', 'Alomna Pharmacy', 'Alomna Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(347, 3, 'customer', 1, 'General', 'Alrahmah Medical Clinic', 'Alrahmah Medical Clinic', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(348, 3, 'customer', 1, 'General', 'Alseha W Dawaa Medical', 'Alseha W Dawaa Medical', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(349, 3, 'customer', 1, 'General', 'Alshafi Medical', 'Alshafi Medical', '302008000000000', 'Riyadh ??????', '', '', '11323', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(350, 3, 'customer', 1, 'General', 'Altaeawun Warehouse', 'Altaeawun Warehouse', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(351, 3, 'customer', 1, 'General', 'Alwefaq Medical Pharmacy', 'Alwefaq Medical Pharmacy', '300026000000000', 'Riyadh 11438 ?????? ?? ??????', '', '', '11438', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(352, 3, 'customer', 1, 'General', 'Anas Pharmacy', 'Anas Pharmacy', '300538000000000', '???? ????? 61411', '', '', '61411', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(353, 3, 'customer', 1, 'General', 'Anaya Alsamo Pharmacy', 'Anaya Alsamo Pharmacy', '300017000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(354, 3, 'customer', 1, 'General', 'Andalusia Hospital', 'Andalusia Hospital', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(355, 3, 'customer', 1, 'General', 'Arkan Warehouse Medical', 'Arkan Warehouse Medical', '301116000000000', 'Riyadh', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(356, 3, 'customer', 1, 'General', 'Arrawdha General Hospital', 'Arrawdha General Hospital', '300596000000000', 'Dammam 31488 ??????', '', '', '31488', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(357, 3, 'customer', 1, 'General', 'Asas Alseha', 'Asas Alseha', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(358, 3, 'customer', 1, 'General', 'Asharq Alawsat Pharmacies Co', 'Asharq Alawsat Pharmacies Co', '300095000000000', '???? 2480- ?????? 12214- 2480', '', '', '12214', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(359, 3, 'customer', 1, 'General', 'Assaher Pharmacy', 'Assaher Pharmacy', '32003000000000', 'Riyadh  ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(360, 3, 'customer', 1, 'General', 'Aster Sanad Hospital', 'Aster Sanad Hospital', '300777000000000', 'Riyadh 13216 ??????', '', '', '13216', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(361, 3, 'customer', 1, 'General', 'Asya Pharmacy Hatim Trading', 'Asya Pharmacy Hatim Trading', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(362, 3, 'customer', 1, 'General', 'Azza pharmacy', 'Azza pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(363, 3, 'customer', 1, 'General', 'Balsam Medical Pharmacy', 'Balsam Medical Pharmacy', '300446000000000', 'Al Qatif 31452 ??????', '', '', '31452', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(364, 3, 'customer', 1, 'General', 'Balsam Tabuk Pharmacy', 'Balsam Tabuk Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(365, 3, 'customer', 1, 'General', 'Basel Store', 'Basel Store', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(366, 3, 'customer', 1, 'General', 'Basma Al Alam Pharmacy', 'Basma Al Alam Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(367, 3, 'customer', 1, 'General', 'Bet Albatarjee', 'Bet Albatarjee', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(368, 3, 'customer', 1, 'General', 'Blaad El-Afia Pharmacy', 'Blaad El-Afia Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(369, 3, 'customer', 1, 'General', 'Borg Alnokhba Pharmacy (Dawaa Algharbia)', 'Borg Alnokhba Pharmacy (Dawaa Algharbia)', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(370, 3, 'customer', 1, 'General', 'Buqshan Hospital', 'Buqshan Hospital', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(371, 3, 'customer', 1, 'General', 'Care Pharmaceutical', 'Care Pharmaceutical', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(372, 3, 'customer', 1, 'General', 'Cash', 'Cash', '0', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(373, 3, 'customer', 1, 'General', 'Charisma Co.', 'Charisma Co.', '310135000000000', 'Riyadh ??????', '', '', '11323', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(374, 3, 'customer', 1, 'General', 'Community Health Medical Co.', 'Community Health Medical Co.', '310387000000000', '????  Yanbu', '', '', 'P.O.BOX ', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(375, 3, 'customer', 1, 'General', 'Dalil Al Dawa Pharmacy', 'Dalil Al Dawa Pharmacy', '123456789', '??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(376, 3, 'customer', 1, 'General', 'Dallah Hospital', 'Dallah Hospital', '300049000000000', 'Riyadh 12381 ??????', '', '', '12381', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(377, 3, 'customer', 1, 'General', 'Dallah Nemar', 'Dallah Nemar', '300049000000000', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(378, 3, 'customer', 1, 'General', 'Dar Alasnad Medical', 'Dar Alasnad Medical', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(379, 3, 'customer', 1, 'General', 'Dawa Afifa Pharmacy', 'Dawa Afifa Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(380, 3, 'customer', 1, 'General', 'Dawa Al Salamah Pharmacy', 'Dawa Al Salamah Pharmacy', '300931000000000', 'Najran', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(381, 3, 'customer', 1, 'General', 'Dawa Al Tamaiz Trading Co', 'Dawa Al Tamaiz Trading Co', '0', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(382, 3, 'customer', 1, 'General', 'Dawaa Al Asr Altibiih', 'Dawaa Al Asr Altibiih', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(383, 3, 'customer', 1, 'General', 'Dawaa Al Safwa Alola', 'Dawaa Al Safwa Alola', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(384, 3, 'customer', 1, 'General', 'Dawaa Alaasemah', 'Dawaa Alaasemah', '123456798', 'Riyadh', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(385, 3, 'customer', 1, 'General', 'Deryaq Drug Store', 'Deryaq Drug Store', '300608000000000', 'Khamish Musait', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(386, 3, 'customer', 1, 'General', 'Dr Abdulrahman Al Mishari Hospital', 'Dr Abdulrahman Al Mishari Hospital', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(387, 3, 'customer', 1, 'General', 'Dr Mahasen Medical Complex', 'Dr Mahasen Medical Complex', '300428000000000', 'Qassim ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(388, 3, 'customer', 1, 'General', 'Dr Mahmoud Farag', 'Dr Mahmoud Farag', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(389, 3, 'customer', 1, 'General', 'Eilaj almujamaeih pharmacy', 'Eilaj almujamaeih pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(390, 3, 'customer', 1, 'General', 'Elite Medical Center', 'Elite Medical Center', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(391, 3, 'customer', 1, 'General', 'Enayt Alhowra Medical Complex', 'Enayt Alhowra Medical Complex', '0', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(392, 3, 'customer', 1, 'General', 'Exceer Pharmacies', 'Exceer Pharmacies', '0', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(393, 3, 'customer', 1, 'General', 'Factory Pharmacy', 'Factory Pharmacy', '300031000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(394, 3, 'customer', 1, 'General', 'Fahad Al-Awaji Pharmacy', 'Fahad Al-Awaji Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(395, 3, 'customer', 1, 'General', 'Fahd Aleawaja Pharmacy', 'Fahd Aleawaja Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(396, 3, 'customer', 1, 'General', 'First Clinic', 'First Clinic', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(397, 3, 'customer', 1, 'General', 'Future Medical Company Ltd', 'Future Medical Company Ltd', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(398, 3, 'customer', 1, 'General', 'Ghaya Pharmacy', 'Ghaya Pharmacy', '0', 'Riyadh - ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(399, 3, 'customer', 1, 'General', 'Ghodaf Pharmacies', 'Ghodaf Pharmacies', '0', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(400, 3, 'customer', 1, 'General', 'Greens Corners Medical .CO', 'Greens Corners Medical .CO', '311187000000000', 'Riyadh  ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(401, 3, 'customer', 1, 'General', 'Hai Algama Hospital', 'Hai Algama Hospital', '300241000000000', 'Riyadh ??????', '', '', '40301668', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(402, 3, 'customer', 1, 'General', 'Health and Care Company', 'Health and Care Company', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(403, 3, 'customer', 1, 'General', 'Health Gate Pharmacy', 'Health Gate Pharmacy', '301202000000000', 'Khamis Mushait', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(404, 3, 'customer', 1, 'General', 'Health House Pharmacy', 'Health House Pharmacy', '300256000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(405, 3, 'customer', 1, 'General', 'HERAA MEDICAL', 'HERAA MEDICAL', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(406, 3, 'customer', 1, 'General', 'International Medical Center', 'International Medical Center', '300189000000000', '??? Jeddah', '', '', '21451', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(407, 3, 'customer', 1, 'General', 'Islam Ali Virus zero', 'Islam Ali Virus zero', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(408, 3, 'customer', 1, 'General', 'Jeddah Medical Medicine Company', 'Jeddah Medical Medicine Company', '310370000000000', 'Jeddah ???', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(409, 3, 'customer', 1, 'General', 'KasAlraeayuh Altibiuh', 'KasAlraeayuh Altibiuh', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(410, 3, 'customer', 1, 'General', 'Kinda Medical Company', 'Kinda Medical Company', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(411, 3, 'customer', 1, 'General', 'king Abdul Aziz University Hospital', 'king Abdul Aziz University Hospital', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(412, 3, 'customer', 1, 'General', 'King Khalid University Hosp.', 'King Khalid University Hosp.', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(413, 3, 'customer', 1, 'General', 'King Saud Medical City', 'King Saud Medical City', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(414, 3, 'customer', 1, 'General', 'Kingdom Hospital', 'Kingdom Hospital', '300048000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(415, 3, 'customer', 1, 'General', 'Lemon Pharmacies', 'Lemon Pharmacies', '310139000000000', 'Riyadh', '', '', '2569 ON ', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(416, 3, 'customer', 1, 'General', 'Lialaa Alsahih for Medicines', 'Lialaa Alsahih for Medicines', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(417, 3, 'customer', 1, 'General', 'Life Lines Medical Co Ltd', 'Life Lines Medical Co Ltd', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(418, 3, 'customer', 1, 'General', 'Lights Pharmacy', 'Lights Pharmacy', '123456798', 'Riyadh', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(419, 3, 'customer', 1, 'General', 'Lualiwuh Alfstat Pharmacy', 'Lualiwuh Alfstat Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(420, 3, 'customer', 1, 'General', 'Luluah Al Zuha (1) Pharmacy', 'Luluah Al Zuha (1) Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(421, 3, 'customer', 1, 'General', 'Makkah Medical Center', 'Makkah Medical Center', '0', '??? ???????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(422, 3, 'customer', 1, 'General', 'Malak Pharmacy', 'Malak Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(423, 3, 'customer', 1, 'General', 'Manba Al Shifa Warehosue', 'Manba Al Shifa Warehosue', '0', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(424, 3, 'customer', 1, 'General', 'Mansour Rabia Medical Company', 'Mansour Rabia Medical Company', '300785000000000', 'Riyadh ??????', '', '', '11332', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(425, 3, 'customer', 1, 'General', 'Mansour Rabie Medical Company', 'Mansour Rabie Medical Company', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(426, 3, 'customer', 1, 'General', 'Markaz Al Warood Al Tibbi', 'Markaz Al Warood Al Tibbi', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(427, 3, 'customer', 1, 'General', 'Masarat Al Gamaa Medical Co', 'Masarat Al Gamaa Medical Co', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(428, 3, 'customer', 1, 'General', 'Mazallat Al Dawa Pharmacy', 'Mazallat Al Dawa Pharmacy', '310057000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(429, 3, 'customer', 1, 'General', 'Mazen Pharmacy', 'Mazen Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(430, 3, 'customer', 1, 'General', 'Medical Division for trading Est.', 'Medical Division for trading Est.', '300030000000000', 'Riyadh ??????', '', '', '11351', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(431, 3, 'customer', 1, 'General', 'Medical store', 'Medical store', '300202000000000', 'Makkah ??? ???????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(432, 3, 'customer', 1, 'General', 'Mesk Health Pharmacy', 'Mesk Health Pharmacy', '311154000000000', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(433, 3, 'customer', 1, 'General', 'Ministry of Health', 'Ministry of Health', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(434, 3, 'customer', 1, 'General', 'Ministry of National Guard', 'Ministry of National Guard', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(435, 3, 'customer', 1, 'General', 'Moaaz Medical Pharmacy', 'Moaaz Medical Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(436, 3, 'customer', 1, 'General', 'Mohamed Abdullah Alkhamis Pharmacy', 'Mohamed Abdullah Alkhamis Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(437, 3, 'customer', 1, 'General', 'More Care Medical Est.', 'More Care Medical Est.', '310088000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(438, 3, 'customer', 1, 'General', 'Mouwasat Medical Services Co.', 'Mouwasat Medical Services Co.', '300507000000000', 'PO Box 282, Dammam 31411', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(439, 3, 'customer', 1, 'General', 'Musharraf Al Omari Trading', 'Musharraf Al Omari Trading', '300832000000000', 'Asir ????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(440, 3, 'customer', 1, 'General', 'My Family  Pharmacy', 'My Family  Pharmacy', '300106000000000', 'JEDDAH ???', '', '', '21411', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(441, 3, 'customer', 1, 'General', 'Najam Al Jazirah', 'Najam Al Jazirah', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(442, 3, 'customer', 1, 'General', 'Najd Consulting Hospital', 'Najd Consulting Hospital', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(443, 3, 'customer', 1, 'General', 'Naqaa United Pharmaceutical Co', 'Naqaa United Pharmaceutical Co', '310702000000000', 'Riyadh 7896 ??????', '', '', '12711', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(444, 3, 'customer', 1, 'General', 'National Guard', 'National Guard', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(445, 3, 'customer', 1, 'General', 'National Medical Care', 'National Medical Care', '300055000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(446, 3, 'customer', 1, 'General', 'Nawfiz najid Pharmacy', 'Nawfiz najid Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(447, 3, 'customer', 1, 'General', 'New Jeddah Clinic Hospital', 'New Jeddah Clinic Hospital', '301206000000000', 'Jeddah ???', '', '', '21472', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(448, 3, 'customer', 1, 'General', 'Noura Pharmacy', 'Noura Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(449, 3, 'customer', 1, 'General', 'Nukhab Al Amakin AlTibiya Pharmacy', 'Nukhab Al Amakin AlTibiya Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(450, 3, 'customer', 1, 'General', 'Nukhab Al Amakin AlTibiya Pharmacy Co', 'Nukhab Al Amakin AlTibiya Pharmacy Co', '310908000000000', 'Makkah ???', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(451, 3, 'customer', 1, 'General', 'NUPCO', 'NUPCO', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(452, 3, 'customer', 1, 'General', 'NUPCO MARKET PLACE', 'NUPCO MARKET PLACE', '300063000000000', 'Al Wurud District Riyadh 11323', '', '', '11323', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(453, 3, 'customer', 1, 'General', 'Olaya Medical Center Pharmacy', 'Olaya Medical Center Pharmacy', '300054000000000', 'Riyadh', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(454, 3, 'customer', 1, 'General', 'Orange Pharmacy', 'Orange Pharmacy', '300026000000000', 'Riyadh 11381 ??????', '', '', '11381', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(455, 3, 'customer', 1, 'General', 'Pathways University Medical Company', 'Pathways University Medical Company', '310087000000000', 'Eastern Region ??????? ???????', '', '', '31951', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(456, 3, 'customer', 1, 'General', 'Pharma Home', 'Pharma Home', '0', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(457, 3, 'customer', 1, 'General', 'Pharma Industrial Factory', 'Pharma Industrial Factory', '302003000000000', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(458, 3, 'customer', 1, 'General', 'Pharmaceutical House Company', 'Pharmaceutical House Company', '300051000000000', '?????? Riyadh', '', '', '12794', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(459, 3, 'customer', 1, 'General', 'Pharmacy Origins Treatmen', 'Pharmacy Origins Treatmen', '300879000000000', 'NA', '', '', '62622', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(460, 3, 'customer', 1, 'General', 'Prince Sultan Medical Militrary City', 'Prince Sultan Medical Militrary City', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(461, 3, 'customer', 1, 'General', 'Qaleat Aldawa Pharmacy', 'Qaleat Aldawa Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(462, 3, 'customer', 1, 'General', 'Qamar Al Aroba Pharmacies', 'Qamar Al Aroba Pharmacies', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(463, 3, 'customer', 1, 'General', 'Ramaz Alamanh', 'Ramaz Alamanh', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(464, 3, 'customer', 1, 'General', 'Ramy Pharmacy', 'Ramy Pharmacy', '0', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(465, 3, 'customer', 1, 'General', 'Rashed Al Hassani Medical Group', 'Rashed Al Hassani Medical Group', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(466, 3, 'customer', 1, 'General', 'Rashid Medical Complex', 'Rashid Medical Complex', '300972000000000', '?????? ????? Qassim', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(467, 3, 'customer', 1, 'General', 'Rawabi Alamal Company', 'Rawabi Alamal Company', '310581000000000', 'Jeddah ???', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(468, 3, 'customer', 1, 'General', 'Rawabi Alamal Subagent', 'Rawabi Alamal Subagent', '310581000000000', 'Jeddah ???.', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(469, 3, 'customer', 1, 'General', 'Rawasi Taibah Trading Co.', 'Rawasi Taibah Trading Co.', '310804000000000', 'Makkah ???', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(470, 3, 'customer', 1, 'General', 'Rehab Health Pharmacy', 'Rehab Health Pharmacy', '0', '??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(471, 3, 'customer', 1, 'General', 'Retaj Al Seha Co.', 'Retaj Al Seha Co.', '300213000000000', '?????? Riyadh', '', '', '11662', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(472, 3, 'customer', 1, 'General', 'Riyadh Care Hospital', 'Riyadh Care Hospital', '300055000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(473, 3, 'customer', 1, 'General', 'Riyadh National Hospital', 'Riyadh National Hospital', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(474, 3, 'customer', 1, 'General', 'Riyadh Pharmacies', 'Riyadh Pharmacies', '300035000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(475, 3, 'customer', 1, 'General', 'Rokn Al-Hakim', 'Rokn Al-Hakim', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(476, 3, 'customer', 1, 'General', 'Rokn Al Sahafa Pharmacy Co.', 'Rokn Al Sahafa Pharmacy Co.', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(477, 3, 'customer', 1, 'General', 'Rowaa Medical Pharmacy', 'Rowaa Medical Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(478, 3, 'customer', 1, 'General', 'Rraghad  Pharmacy', 'Rraghad  Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(479, 3, 'customer', 1, 'General', 'Sada Medical Care Company', 'Sada Medical Care Company', '310221000000000', 'Riyadh  ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(480, 3, 'customer', 1, 'General', 'Safa Alorobah Warehouse', 'Safa Alorobah Warehouse', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(481, 3, 'customer', 1, 'General', 'Salaamatak Pharmacy', 'Salaamatak Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(482, 3, 'customer', 1, 'General', 'Salamat Buraydah Polyclinic', 'Salamat Buraydah Polyclinic', '300487000000000', 'Buraidah ?????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(483, 3, 'customer', 1, 'General', 'Samir Abbas Medical Center', 'Samir Abbas Medical Center', '310095000000000', 'Jeddah  ???', '', '', '23411', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(484, 3, 'customer', 1, 'General', 'Sanabel Al Dawa Medical Co', 'Sanabel Al Dawa Medical Co', '301229000000000', 'Eastern Region ??????? ???????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(485, 3, 'customer', 1, 'General', 'Saudi German Hosp', 'Saudi German Hosp', '300098000000000', '?? ???????  ??? Jeddah 23521', '', '', '23521', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(486, 3, 'customer', 1, 'General', 'Saudi German Hosp Aseer', 'Saudi German Hosp Aseer', '300098000000000', 'Aseer ????, Khamis Mushait', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(487, 3, 'customer', 1, 'General', 'Saudi German Hosp Dammam', 'Saudi German Hosp Dammam', '310135000000000', 'Dammam  ??????', '', '', 'P.O.BOX4', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(488, 3, 'customer', 1, 'General', 'Saudi German Hosp Madinah', 'Saudi German Hosp Madinah', '0', 'Madina ??????? ???????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(489, 3, 'customer', 1, 'General', 'Saudi German Hosp Riyadh', 'Saudi German Hosp Riyadh', '300098000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(490, 3, 'customer', 1, 'General', 'Shams Al-Ruqaiya Pharmacy', 'Shams Al-Ruqaiya Pharmacy', '300099000000000', 'Makkah ???', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(491, 3, 'customer', 1, 'General', 'Shifa Al Seha', 'Shifa Al Seha', '300597000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(492, 3, 'customer', 1, 'General', 'Shifa Al Tayseer Pharmacy', 'Shifa Al Tayseer Pharmacy', '310145000000000', 'Riyadh', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(493, 3, 'customer', 1, 'General', 'Shifa Tayba Pharmacy', 'Shifa Tayba Pharmacy', '0', 'MADina  ???????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(494, 3, 'customer', 1, 'General', 'Smart Apple Pharmaceutical Company', 'Smart Apple Pharmaceutical Company', '311382000000000', '?????? Riyadh', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(495, 3, 'customer', 1, 'General', 'Smart Stores Company for Drug Limited', 'Smart Stores Company for Drug Limited', '301232000000000', '???? ??? 466 4031 - 12284 ????', '', '', '12284', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(496, 3, 'customer', 1, 'General', 'Smou United Medical Company', 'Smou United Medical Company', '311199000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(497, 3, 'customer', 1, 'General', 'Sulaymaniyah Pharmacy', 'Sulaymaniyah Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(498, 3, 'customer', 1, 'General', 'Suliman Al Fakeeh Hosp.', 'Suliman Al Fakeeh Hosp.', '300817000000000', '??? Jeddah', '', '', '21461', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(499, 3, 'customer', 1, 'General', 'Tabay Pharmacy Group', 'Tabay Pharmacy Group', '300421000000000', '?????? ?????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(500, 3, 'customer', 1, 'General', 'TADAWI', 'TADAWI', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(501, 3, 'customer', 1, 'General', 'TADAWI Pharmacy', 'TADAWI Pharmacy', '301288000000000', 'Jeddah  ???', '', '', '??????  ', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(502, 3, 'customer', 1, 'General', 'Tadawina Pharmacy', 'Tadawina Pharmacy', '310121000000000', '?????? Al-Qassim', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(503, 3, 'customer', 1, 'General', 'Taj Al Dawa For Trading', 'Taj Al Dawa For Trading', '300017000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(504, 3, 'customer', 1, 'General', 'Teef Al Mansiyah', 'Teef Al Mansiyah', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(505, 3, 'customer', 1, 'General', 'Thanaya Aldwaa', 'Thanaya Aldwaa', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(506, 3, 'customer', 1, 'General', 'Top Medical Excellence Est', 'Top Medical Excellence Est', '310088000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(507, 3, 'customer', 1, 'General', 'Ultra Medical Hub', 'Ultra Medical Hub', '0', '???  Dubai', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(508, 3, 'customer', 1, 'General', 'Ultra Smooth Trading Est.', 'Ultra Smooth Trading Est.', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(509, 3, 'customer', 1, 'General', 'United Pharmaceutical', 'United Pharmaceutical', '300212000000000', 'Jeddah     ???', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', '');
INSERT INTO `sma_companies` (`id`, `group_id`, `group_name`, `customer_group_id`, `customer_group_name`, `name`, `company`, `vat_no`, `address`, `city`, `state`, `postal_code`, `country`, `phone`, `email`, `cf1`, `cf2`, `cf3`, `cf4`, `cf5`, `cf6`, `invoice_footer`, `payment_term`, `logo`, `award_points`, `deposit_amount`, `price_group_id`, `price_group_name`, `gst_no`) VALUES
(510, 3, 'customer', 1, 'General', 'Walid Muhammad Ghazzawi Medical Pharmacy', 'Walid Muhammad Ghazzawi Medical Pharmacy', '310371000000000', 'Makkah ??? ???????', '', '', '24232', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(511, 3, 'customer', 1, 'General', 'Waqiya W Ilaj', 'Waqiya W Ilaj', '310791000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(512, 3, 'customer', 1, 'General', 'Wardah Aldawaa Pharmcy', 'Wardah Aldawaa Pharmcy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(513, 3, 'customer', 1, 'General', 'Wassafat  Alalshafa Pharmacy', 'Wassafat  Alalshafa Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(514, 3, 'customer', 1, 'General', 'Wassafat Al Seha Pharmacy', 'Wassafat Al Seha Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(515, 3, 'customer', 1, 'General', 'Week Aldiwa Pharmacy', 'Week Aldiwa Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(516, 3, 'customer', 1, 'General', 'Week Pharmacy', 'Week Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(517, 3, 'customer', 1, 'General', 'Yanbu National Pharmacy', 'Yanbu National Pharmacy', '311092000000000', '???? ????????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(518, 3, 'customer', 1, 'General', 'Zad AlDawaa Pharmacy', 'Zad AlDawaa Pharmacy', '310231000000000', 'Makkah   ???', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(519, 3, 'customer', 1, 'General', 'Zad Alteb Co.', 'Zad Alteb Co.', '311224000000000', '???? ????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(520, 3, 'customer', 1, 'General', 'Zahrat Al Amal Pharmacy', 'Zahrat Al Amal Pharmacy', '', 'NA', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(521, 3, 'customer', 1, 'General', 'Zahrat Al Rowdah Pharmacies', 'Zahrat Al Rowdah Pharmacies', '0', '?????? 11352 ?????', '', '', '11352', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(522, 3, 'customer', 1, 'General', 'Zahrat Lemar Medical.Co', 'Zahrat Lemar Medical.Co', '300807000000000', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(523, 3, 'customer', 1, 'General', 'Zahret Al Amged Warehouse', 'Zahret Al Amged Warehouse', '0', 'Riyadh ??????', '', '', '', '', 'NA', 'test@gmail.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(524, NULL, 'biller', NULL, NULL, 'PHARMA DRUG STORE CO.', 'PHARMA DRUG STORE CO.', '310134982500003', 'OLAYA', 'RIYADH', '', '11351', '', '+966568241418', 'eid@pharma.com.sa', '', '', '', '', '', '', '', 0, 'avenzur-logov2-02.png', 0, NULL, NULL, NULL, ''),
(525, 3, 'customer', 1, 'General', 'Anus Ahmad', 'Pharma Drug Store', NULL, 'test address<br>', 'Riyadh', '', '11663', 'SA', '+9662345645', 'anusahmad2014@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'logo.png', 0, NULL, 1, 'Default', NULL),
(526, 3, 'customer', 1, 'General', 'Iqbal', 'Pharma Drug Store', NULL, 'Riyadh<br>', 'Riyadh', 'Saudi', '11436', 'SA', '+966 567074903', 'Haseen_333@yahoo.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'logo.png', 0, NULL, 1, 'Default', NULL),
(527, 3, 'customer', 1, 'General', 'CUSTOMER', 'WALK-IN CUSTOMER', '', 'Riyadh', 'Riyadh', '', '1163', 'Saudia Arabia', '966568241418', 'customer@pharmacyherbel.com', '', '', '', '', '', '', NULL, 0, 'logo.png', 0, NULL, 1, 'Default', ''),
(528, 3, 'customer', 1, 'General', 'Mohamed Ahmed', '', NULL, NULL, NULL, NULL, NULL, 'SA', '+966 544385270', 'eng.sheshtawy@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'logo.png', 0, NULL, 1, 'Default', NULL),
(529, 3, 'customer', 1, 'General', 'Amr', 'Pharma Drug Store', NULL, 'Solimaiyah<br>', 'Riyadh', 'Riyadh', '12242', 'SA', '+966 568241418', 'ama@pharma.com.sa', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'logo.png', 0, NULL, 1, 'Default', NULL),
(530, 3, 'customer', 1, 'General', 'WFUGfijSK UXlWjrGt', '', NULL, NULL, NULL, NULL, NULL, 'SA', '8734179520', 'goldurokud@outlook.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'logo.png', 0, NULL, 1, 'Default', NULL),
(531, 3, 'customer', 1, 'General', 'test user', '', NULL, NULL, NULL, NULL, NULL, 'AE', '9711212121221', 'testing@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'logo.png', 0, NULL, 1, 'Default', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sma_costing`
--

CREATE TABLE `sma_costing` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `sale_item_id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `purchase_item_id` int(11) DEFAULT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `purchase_net_unit_cost` decimal(25,4) DEFAULT NULL,
  `purchase_unit_cost` decimal(25,4) DEFAULT NULL,
  `sale_net_unit_price` decimal(25,4) NOT NULL,
  `sale_unit_price` decimal(25,4) NOT NULL,
  `quantity_balance` decimal(15,4) DEFAULT NULL,
  `inventory` tinyint(1) DEFAULT 0,
  `overselling` tinyint(1) DEFAULT 0,
  `option_id` int(11) DEFAULT NULL,
  `purchase_id` int(11) DEFAULT NULL,
  `transfer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_costing`
--

INSERT INTO `sma_costing` (`id`, `date`, `product_id`, `sale_item_id`, `sale_id`, `purchase_item_id`, `quantity`, `purchase_net_unit_cost`, `purchase_unit_cost`, `sale_net_unit_price`, `sale_unit_price`, `quantity_balance`, `inventory`, `overselling`, `option_id`, `purchase_id`, `transfer_id`) VALUES
(43, '2023-01-01', 43, 111, 114, 65, '1.0000', '115.0000', '115.0000', '230.0000', '230.0000', '6314.0000', 1, 0, 41, NULL, NULL),
(44, '2023-01-04', 43, 113, 116, 65, '1.0000', '115.0000', '115.0000', '230.0000', '230.0000', '6313.0000', 1, 0, 41, NULL, NULL),
(45, '2023-01-09', 43, 114, 117, 69, '1.0000', '115.0000', '115.0000', '230.0000', '230.0000', '2999.0000', 1, 0, 41, 14, NULL),
(46, '2023-01-11', 43, 115, 118, 69, '1.0000', '115.0000', '115.0000', '230.0000', '230.0000', '2998.0000', 1, 0, 41, 14, NULL),
(47, '2023-01-18', 43, 120, 123, 69, '1.0000', '115.0000', '115.0000', '230.0000', '230.0000', '2997.0000', 1, 0, 41, 14, NULL),
(48, '2023-02-07', 43, 121, 124, 71, '1.0000', '115.0000', '115.0000', '230.0000', '230.0000', '9.0000', 1, 0, 41, NULL, 5),
(49, '2023-02-08', 38, 122, 125, 73, '1.0000', '20.0000', '20.0000', '40.0000', '40.0000', '99.0000', 1, 0, NULL, 16, NULL),
(50, '2023-02-08', 43, 123, 125, 71, '1.0000', '115.0000', '115.0000', '230.0000', '230.0000', '8.0000', 1, 0, 41, NULL, 5),
(51, '2023-02-14', 43, 124, 126, 71, '6.0000', '115.0000', '115.0000', '230.0000', '230.0000', '2.0000', 1, 0, 41, NULL, 5),
(52, '2023-02-14', 43, 125, 127, 71, '2.0000', '115.0000', '115.0000', '230.0000', '230.0000', '0.0000', 1, 0, 41, NULL, 5);

-- --------------------------------------------------------

--
-- Table structure for table `sma_countries`
--

CREATE TABLE `sma_countries` (
  `id` int(10) NOT NULL,
  `name` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sma_countries`
--

INSERT INTO `sma_countries` (`id`, `name`, `code`) VALUES
(6, 'UAE', 'AE'),
(8, 'Saudi Arabia', 'SA');

-- --------------------------------------------------------

--
-- Table structure for table `sma_currencies`
--

CREATE TABLE `sma_currencies` (
  `id` int(11) NOT NULL,
  `code` varchar(5) NOT NULL,
  `name` varchar(55) NOT NULL,
  `rate` decimal(12,4) NOT NULL,
  `auto_update` tinyint(1) NOT NULL DEFAULT 0,
  `symbol` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_currencies`
--

INSERT INTO `sma_currencies` (`id`, `code`, `name`, `rate`, `auto_update`, `symbol`) VALUES
(1, 'USD', 'US Dollar', '3.7500', 0, '$'),
(2, 'EUR', 'EURO', '0.7340', 0, NULL),
(3, 'SAR', 'Riyal', '1.0000', 0, 'SAR');

-- --------------------------------------------------------

--
-- Table structure for table `sma_customer_groups`
--

CREATE TABLE `sma_customer_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `percent` int(11) NOT NULL,
  `discount` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_customer_groups`
--

INSERT INTO `sma_customer_groups` (`id`, `name`, `percent`, `discount`) VALUES
(1, 'General', 0, NULL),
(2, 'Reseller', -5, NULL),
(3, 'Distributor', -15, NULL),
(4, 'New Customer (+10)', 10, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sma_date_format`
--

CREATE TABLE `sma_date_format` (
  `id` int(11) NOT NULL,
  `js` varchar(20) NOT NULL,
  `php` varchar(20) NOT NULL,
  `sql` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_date_format`
--

INSERT INTO `sma_date_format` (`id`, `js`, `php`, `sql`) VALUES
(1, 'mm-dd-yyyy', 'm-d-Y', '%m-%d-%Y'),
(2, 'mm/dd/yyyy', 'm/d/Y', '%m/%d/%Y'),
(3, 'mm.dd.yyyy', 'm.d.Y', '%m.%d.%Y'),
(4, 'dd-mm-yyyy', 'd-m-Y', '%d-%m-%Y'),
(5, 'dd/mm/yyyy', 'd/m/Y', '%d/%m/%Y'),
(6, 'dd.mm.yyyy', 'd.m.Y', '%d.%m.%Y');

-- --------------------------------------------------------

--
-- Table structure for table `sma_deliveries`
--

CREATE TABLE `sma_deliveries` (
  `id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `sale_id` int(11) NOT NULL,
  `do_reference_no` varchar(50) NOT NULL,
  `sale_reference_no` varchar(50) NOT NULL,
  `customer` varchar(55) NOT NULL,
  `address` varchar(1000) NOT NULL,
  `note` varchar(1000) DEFAULT NULL,
  `status` varchar(15) DEFAULT NULL,
  `attachment` varchar(50) DEFAULT NULL,
  `delivered_by` varchar(50) DEFAULT NULL,
  `received_by` varchar(50) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sma_deposits`
--

CREATE TABLE `sma_deposits` (
  `id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `company_id` int(11) NOT NULL,
  `amount` decimal(25,4) NOT NULL,
  `paid_by` varchar(50) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sma_directpay`
--

CREATE TABLE `sma_directpay` (
  `id` int(11) NOT NULL,
  `merchant_id` varchar(50) NOT NULL,
  `authentication_token` varchar(150) NOT NULL,
  `payment_link` varchar(150) NOT NULL,
  `refund_link` varchar(150) NOT NULL,
  `test_payment_link` varchar(150) NOT NULL,
  `test_refund_link` varchar(150) NOT NULL DEFAULT '0.0000',
  `activation` int(10) NOT NULL,
  `version` varchar(20) NOT NULL,
  `currencyISOCode` int(20) NOT NULL,
  `payment_message_id` int(20) NOT NULL,
  `refund_message_id` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_directpay`
--

INSERT INTO `sma_directpay` (`id`, `merchant_id`, `authentication_token`, `payment_link`, `refund_link`, `test_payment_link`, `test_refund_link`, `activation`, `version`, `currencyISOCode`, `payment_message_id`, `refund_message_id`) VALUES
(1, 'DP00000017', 'MGQ5YjY4NWRhYjA5ZmQyYjBmZjAzYzE3', 'https://paytest.directpay.sa/SmartRoutePaymentWeb/SRPayMsgHandler', 'https://paytest.directpay.sa/SmartRoutePaymentWeb/SRMsgHandler', 'https://paytest.directpay.sa/SmartRoutePaymentWeb/SRPayMsgHandler', 'https://paytest.directpay.sa/SmartRoutePaymentWeb/SRMsgHandler', 1, '1.0', 682, 1, 4);

-- --------------------------------------------------------

--
-- Table structure for table `sma_expenses`
--

CREATE TABLE `sma_expenses` (
  `id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `reference` varchar(50) NOT NULL,
  `amount` decimal(25,4) NOT NULL,
  `note` varchar(1000) DEFAULT NULL,
  `created_by` varchar(55) NOT NULL,
  `attachment` varchar(55) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_expenses`
--

INSERT INTO `sma_expenses` (`id`, `date`, `reference`, `amount`, `note`, `created_by`, `attachment`, `category_id`, `warehouse_id`) VALUES
(1, '2022-12-18 01:11:00', '2022/12/0001', '200.0000', '', '1', '0', 1, 4);

-- --------------------------------------------------------

--
-- Table structure for table `sma_expense_categories`
--

CREATE TABLE `sma_expense_categories` (
  `id` int(11) NOT NULL,
  `code` varchar(55) NOT NULL,
  `name` varchar(55) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_expense_categories`
--

INSERT INTO `sma_expense_categories` (`id`, `code`, `name`) VALUES
(1, 'sh01', 'shipping'),
(2, 'prin', 'printing');

-- --------------------------------------------------------

--
-- Table structure for table `sma_gift_cards`
--

CREATE TABLE `sma_gift_cards` (
  `id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `card_no` varchar(20) NOT NULL,
  `value` decimal(25,4) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer` varchar(255) DEFAULT NULL,
  `balance` decimal(25,4) NOT NULL,
  `expiry` date DEFAULT NULL,
  `created_by` varchar(55) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_gift_cards`
--

INSERT INTO `sma_gift_cards` (`id`, `date`, `card_no`, `value`, `customer_id`, `customer`, `balance`, `expiry`, `created_by`) VALUES
(1, '2022-11-16 06:58:20', '12', '100.0000', 6, 'testcomp', '100.0000', '2024-11-18', '1');

-- --------------------------------------------------------

--
-- Table structure for table `sma_gift_card_topups`
--

CREATE TABLE `sma_gift_card_topups` (
  `id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `card_id` int(11) NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sma_groups`
--

CREATE TABLE `sma_groups` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `name` varchar(20) NOT NULL,
  `description` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_groups`
--

INSERT INTO `sma_groups` (`id`, `name`, `description`) VALUES
(1, 'owner', 'Owner'),
(2, 'admin', 'Administrator'),
(3, 'customer', 'Customer'),
(4, 'supplier', 'Supplier'),
(6, 'pharmacist', 'Pharmacists');

-- --------------------------------------------------------

--
-- Table structure for table `sma_login_attempts`
--

CREATE TABLE `sma_login_attempts` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `ip_address` varbinary(16) NOT NULL,
  `login` varchar(100) NOT NULL,
  `time` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sma_logs`
--

CREATE TABLE `sma_logs` (
  `id` int(11) NOT NULL,
  `detail` varchar(190) NOT NULL,
  `model` longtext DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_logs`
--

INSERT INTO `sma_logs` (`id`, `detail`, `model`, `date`) VALUES
(1, 'Supplier is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"2\",\"group_id\":\"4\",\"group_name\":\"supplier\",\"customer_group_id\":null,\"customer_group_name\":null,\"name\":\"Test Supplier\",\"company\":\"Supplier Company Name\",\"vat_no\":null,\"address\":\"Supplier Address\",\"city\":\"Petaling Jaya\",\"state\":\"Selangor\",\"postal_code\":\"46050\",\"country\":\"Malaysia\",\"phone\":\"0123456789\",\"email\":\"supplier@tecdiary.com\",\"cf1\":\"-\",\"cf2\":\"-\",\"cf3\":\"-\",\"cf4\":\"-\",\"cf5\":\"-\",\"cf6\":\"-\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":null,\"price_group_name\":null,\"gst_no\":null}}', '2022-10-02 20:18:36'),
(2, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"12\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Test Customer\",\"company\":\"Increate Tech\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":null,\"phone\":\"051123456\",\"email\":\"anus.increatetech@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-13 06:35:26'),
(3, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"36\",\"date\":\"2022-12-13 08:25:30\",\"reference_no\":\"SALE2022\\/12\\/0032\",\"customer_id\":\"7\",\"customer\":\"mubasher\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":null,\"staff_note\":null,\"total\":\"113.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"113.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"3\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"113.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"30\",\"reserve_id\":null,\"hash\":\"1e484b9e50720f9535220220c2b1c492981d43a6175c48041c539e44bd303983\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"47\",\"sale_id\":\"36\",\"product_id\":\"9\",\"product_code\":\"8057506630066\",\"product_name\":\"Flex\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"113.0000\",\"unit_price\":\"113.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"113.0000\",\"serial_no\":null,\"real_unit_price\":\"113.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":\"a3ddd2b15c1e63e37a363658aa87370c.jpg\",\"details\":\"<h2>Description<\\/h2><p>Flex\\u00ae improves joint mobility and function. Also, its unique ingredients help alleviating the pain and inflammation.<\\/p><h4>How to Use:<\\/h4><p>1 mini bottle per day.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 Bottle<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>MSM Methylsulfonylmethane<\\/td><td>500 mg<\\/td><\\/tr><tr><td>Hydrolyzed collagen type 2<\\/td><td>10 mg<\\/td><\\/tr><tr><td>Chondroitin sulphate<\\/td><td>20 mg<\\/td><\\/tr><tr><td>Capsaicin<\\/td><td>3 mg<\\/td><\\/tr><tr><td>Vitamin C<\\/td><td>100 mg<\\/td><\\/tr><tr><td>Acido Ialuronico<\\/td><td>200 mg<\\/td><\\/tr><tr><td>Device Clow<\\/td><td>300 mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"variant\":null,\"hsn_code\":null,\"second_name\":\"Promotes Better Joints\",\"base_unit_id\":\"3\",\"base_unit_code\":\"cap\"}]}', '2022-12-13 06:38:55'),
(4, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"7\",\"date\":\"2022-11-30 13:27:20\",\"reference_no\":\"SALE2022\\/11\\/0003\",\"customer_id\":\"7\",\"customer\":\"mubasher\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"243.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"243.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"3\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"1\",\"reserve_id\":null,\"hash\":\"f2c55d02cc319770db97b19f7ef8af4b9447c712e9060cc129d98dd35bb9b732\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"cod\"},\"items\":null}', '2022-12-13 06:39:20'),
(5, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":false,\"items\":null}', '2022-12-13 06:40:02'),
(6, 'Customer is being deleted by anus.increatetech@gmail.com (User Id: 4)', '{\"model\":{\"id\":\"15\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"ANUS\",\"company\":\"INCREATE\",\"vat_no\":\"12345\",\"address\":\"rwp\",\"city\":\"rwp\",\"state\":\"\",\"postal_code\":\"47000\",\"country\":\"PK\",\"phone\":\"123456789\",\"email\":\"anus.increattech@gmail.com\",\"cf1\":\"test\",\"cf2\":\"test\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"123\"}}', '2022-12-13 06:54:14'),
(7, 'Customer is being deleted by anus.increatetech@gmail.com (User Id: 4)', '{\"model\":false}', '2022-12-13 06:54:20'),
(8, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"16\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"4\",\"customer_group_name\":\"New Customer (+10)\",\"name\":\"Test Increate\",\"company\":\"Increate Tech\",\"vat_no\":\"\",\"address\":\"rwp\",\"city\":\"rwp\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"12121221\",\"email\":\"Increatetech@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-13 07:26:30'),
(9, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"17\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"test user\",\"company\":\"testinguser\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":null,\"phone\":\"123456789\",\"email\":\"test@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-13 07:32:24'),
(10, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"19\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"test test\",\"company\":\"test\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":null,\"phone\":\"12751287\",\"email\":\"shahmubasher53@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-13 07:54:45'),
(11, 'Return is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"1\",\"date\":\"2022-11-16 18:55:00\",\"reference_no\":\"1234\",\"customer_id\":\"1\",\"customer\":\"Walk-in Customer\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":\"\",\"total\":\"60.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":\"\",\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"grand_total\":\"60.0000\",\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"paid\":\"0.0000\",\"surcharge\":\"0.0000\",\"attachment\":null,\"hash\":\"34839c634884bdb694251181db209d0b9cc0879bfe283be0bd98178759cf7754\",\"cgst\":null,\"sgst\":null,\"igst\":null,\"shipping\":\"0.0000\"},\"items\":[{\"id\":\"1\",\"return_id\":\"1\",\"product_id\":\"23\",\"product_code\":\"93555055\",\"product_name\":\"Beauty Combo\",\"product_type\":\"combo\",\"option_id\":null,\"net_unit_price\":\"60.0000\",\"unit_price\":\"60.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"subtotal\":\"60.0000\",\"serial_no\":\"\",\"real_unit_price\":\"60.0000\",\"product_unit_id\":null,\"product_unit_code\":null,\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":\"no_image.png\",\"details\":\"<p>Test<\\/p>\",\"variant\":null,\"hsn_code\":null,\"second_name\":\"Test\"}]}', '2022-12-17 17:03:18'),
(12, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"37\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"test test\",\"company\":\"\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":\"AE\",\"phone\":\"12222222\",\"email\":\"testttttttttt@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-21 08:27:06'),
(13, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"38\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"test usersss\",\"company\":\"\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":\"AE\",\"phone\":\"12121212\",\"email\":\"testuser@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-21 10:35:24'),
(14, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"39\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"atiq ch\",\"company\":\"\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":\"AE\",\"phone\":\"121212121\",\"email\":\"atiq@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-21 10:35:26'),
(15, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"40\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"atirq atiej\",\"company\":\"\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":\"AE\",\"phone\":\"2938923890\",\"email\":\"abc@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-21 10:35:29'),
(16, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"41\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"atiq c\",\"company\":\"\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":\"AE\",\"phone\":\"122212121\",\"email\":\"atiq@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-21 10:35:38'),
(17, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"42\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"atiq 123\",\"company\":\"\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":\"AE\",\"phone\":\"12212\",\"email\":\"atiqq@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-21 10:35:43'),
(18, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"35\",\"code\":\"32234\",\"name\":\"test\",\"unit\":\"4\",\"cost\":\"94.0000\",\"price\":\"2200.0000\",\"alert_quantity\":\"4.0000\",\"image\":\"no_image.png\",\"category_id\":\"8\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"22\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"0.0000\",\"tax_rate\":\"2\",\"track_quantity\":\"1\",\"details\":\"\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"\",\"tax_method\":\"0\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"4\",\"purchase_unit\":\"4\",\"brand\":\"1\",\"slug\":\"test\",\"featured\":null,\"weight\":\"0.2000\",\"hsn_code\":null,\"views\":\"0\",\"hide\":\"0\",\"second_name\":\"Longevity Support Skin, Body & Cardiovascular\",\"hide_pos\":\"0\"}}', '2022-12-22 07:24:03'),
(19, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"36\",\"code\":\"123412\",\"name\":\"test\",\"unit\":\"4\",\"cost\":\"1200.0000\",\"price\":\"1250.0000\",\"alert_quantity\":\"0.0000\",\"image\":\"no_image.png\",\"category_id\":\"4\",\"subcategory_id\":null,\"cf1\":\"UAE\",\"cf2\":\"yes\",\"cf3\":\"test\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"6400.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":\"1\",\"promo_price\":\"1100.0000\",\"start_date\":\"2022-12-23\",\"end_date\":\"2022-12-24\",\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"2\",\"purchase_unit\":\"2\",\"brand\":\"3\",\"slug\":\"test\",\"featured\":null,\"weight\":\"0.0000\",\"hsn_code\":null,\"views\":\"9\",\"hide\":\"0\",\"second_name\":\"test\",\"hide_pos\":\"0\"}}', '2022-12-26 10:13:23'),
(20, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"1\",\"code\":\"1234567879\",\"name\":\"Aloe Vera Juice [Pineapple Flavour] \\u2013 Natural Hydrator, Better Liver Function & Nutritious Booster\",\"unit\":\"1\",\"cost\":\"20.0000\",\"price\":\"40.0000\",\"alert_quantity\":\"4.0000\",\"image\":\"6cf2aead509142f3a4fc1130922d15c0.png\",\"category_id\":\"1\",\"subcategory_id\":null,\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"203.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<p>Aloe vera juice is a gooey, thick liquid made from the flesh of the aloe vera plant leaf.\\u00a0<\\/p>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<p>Aloe vera juice is a gooey, thick liquid made from the flesh of the aloe vera plant leaf. It\\u2019s commonly known to treat sunburns. But drinking this healthy elixir in juice form provides you with a number of other health benefits.<\\/p><p>Aloe vera juice is made by crushing or grinding the entire leaf of the aloe vera plant, followed by various steps to purify and filter the liquid. With a mild, tolerable flavour, the juice mixes easily into smoothies and shakes.<\\/p>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"4\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"1\",\"purchase_unit\":\"1\",\"brand\":\"1\",\"slug\":\"aloe-vera-juice\",\"featured\":null,\"weight\":\"0.0200\",\"hsn_code\":null,\"views\":\"11\",\"hide\":\"0\",\"second_name\":\"\",\"hide_pos\":\"0\"}}', '2022-12-26 10:13:28'),
(21, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"21\",\"code\":\"8057506630295\",\"name\":\"Ashwagandha\",\"unit\":\"3\",\"cost\":\"45.0000\",\"price\":\"45.0000\",\"alert_quantity\":\"0.0000\",\"image\":\"e3735f5c1e66a4e92c56624f748e3dbe.jpg\",\"category_id\":\"3\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"0.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<h2>Description<\\/h2><p>Help relieve occasional stress, improves quality sleep, supports healthy cortisol balance, and promotes general well-being.<\\/p><h4>How to Use:<\\/h4><p>Take 2 capsules daily.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Ashwagandha (7% in witalonidi)<\\/td><td>500mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<h2>Description<\\/h2><p>Help relieve occasional stress, improves quality sleep, supports healthy cortisol balance, and promotes general well-being.<\\/p><h4>How to Use:<\\/h4><p>Take 2 capsules daily.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Ashwagandha (7% in witalonidi)<\\/td><td>500mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"3\",\"purchase_unit\":\"3\",\"brand\":\"2\",\"slug\":\"ashwagandha\",\"featured\":null,\"weight\":\"0.2000\",\"hsn_code\":null,\"views\":\"12\",\"hide\":\"0\",\"second_name\":\"Helps Relief Stress, Anxiety & Tension.\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:00'),
(22, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"26\",\"code\":\"3w3w3\",\"name\":\"amr\",\"unit\":\"4\",\"cost\":\"12800.0000\",\"price\":\"230.0000\",\"alert_quantity\":\"20.0000\",\"image\":\"no_image.png\",\"category_id\":\"1\",\"subcategory_id\":\"13\",\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"373.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"2\",\"purchase_unit\":\"2\",\"brand\":\"3\",\"slug\":\"amr\",\"featured\":null,\"weight\":\"0.0000\",\"hsn_code\":null,\"views\":\"0\",\"hide\":\"0\",\"second_name\":\"\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:07'),
(23, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"30\",\"code\":\"40363761\",\"name\":\"test\",\"unit\":\"3\",\"cost\":\"100.0000\",\"price\":\"150.0000\",\"alert_quantity\":\"5.0000\",\"image\":\"7f24794a16cb73d48667cb792edb3201.jpg\",\"category_id\":\"7\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"abc\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"0.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"3\",\"purchase_unit\":\"3\",\"brand\":\"2\",\"slug\":\"40363761\",\"featured\":null,\"weight\":\"1.0000\",\"hsn_code\":null,\"views\":\"0\",\"hide\":\"0\",\"second_name\":\"test ingredient\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:09'),
(24, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"29\",\"code\":\"45545\",\"name\":\"sulfad100\",\"unit\":\"2\",\"cost\":\"200.0000\",\"price\":\"400.0000\",\"alert_quantity\":\"0.0000\",\"image\":\"no_image.png\",\"category_id\":\"1\",\"subcategory_id\":\"13\",\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"1039.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"2\",\"purchase_unit\":\"2\",\"brand\":\"0\",\"slug\":\"sulfad100\",\"featured\":null,\"weight\":\"0.0000\",\"hsn_code\":null,\"views\":\"0\",\"hide\":\"0\",\"second_name\":\"\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:12'),
(25, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"32\",\"code\":\"50053148\",\"name\":\"test 10\",\"unit\":\"3\",\"cost\":\"10.0000\",\"price\":\"20.0000\",\"alert_quantity\":\"5.0000\",\"image\":\"90238c22696b0358cf7b93d95df04dc1.jpg\",\"category_id\":\"12\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"abc\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"0.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"3\",\"purchase_unit\":\"3\",\"brand\":\"2\",\"slug\":\"50053148\",\"featured\":null,\"weight\":\"1.0000\",\"hsn_code\":null,\"views\":\"0\",\"hide\":\"0\",\"second_name\":\"test ingredient\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:17'),
(26, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"2\",\"code\":\"554212121\",\"name\":\"Anti \\u2013 Acne Serum \\u2013 Brightens Skin, Fades Acne, Lighten Acne Scars & Control Excess Oil Production\",\"unit\":\"1\",\"cost\":\"10.0000\",\"price\":\"20.0000\",\"alert_quantity\":\"4.0000\",\"image\":\"d318b69c761b2d1d61fdf1c02d79e969.jpg\",\"category_id\":\"1\",\"subcategory_id\":null,\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"54.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<p>ChiltanPure Anti-Acne Serum is a lightweight, gentle formula that kills the germs that irritate and works brilliantly for acne-prone skin.\\u00a0<\\/p>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<p>ChiltanPure Anti-Acne Serum is a lightweight, gentle formula that kills the germs that irritate and works brilliantly for acne-prone skin. It removes excess oil from the skin and stimulates the growth of new skin cells and the elimination of old ones. This anti-acne serum treats blemishes while also preventing clogged pores and future breakouts. The organic blend aids in removing dead skin cells, and excess oil from the skin results in clear, radiant skin.<\\/p>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"5\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"1\",\"purchase_unit\":\"1\",\"brand\":\"1\",\"slug\":\"anti-acne-serum-brightens-skin-fades-acne-lighten-acne-\",\"featured\":\"1\",\"weight\":\"0.0200\",\"hsn_code\":null,\"views\":\"23\",\"hide\":\"0\",\"second_name\":\"\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:25'),
(27, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"28\",\"code\":\"5765765\",\"name\":\"sulfad2\",\"unit\":\"4\",\"cost\":\"120.0000\",\"price\":\"230.0000\",\"alert_quantity\":\"5.0000\",\"image\":\"no_image.png\",\"category_id\":\"1\",\"subcategory_id\":\"13\",\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"408.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<p>test<\\/p>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<p>test<\\/p>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"31\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":\"1\",\"promo_price\":\"200.0000\",\"start_date\":\"2022-12-17\",\"end_date\":\"2022-12-31\",\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"4\",\"purchase_unit\":\"4\",\"brand\":\"3\",\"slug\":\"sulfad2\",\"featured\":\"1\",\"weight\":\"0.0000\",\"hsn_code\":null,\"views\":\"2\",\"hide\":\"0\",\"second_name\":\"\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:29'),
(28, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"34\",\"code\":\"64969409\",\"name\":\"SULFAD3\",\"unit\":\"4\",\"cost\":\"200.0000\",\"price\":\"230.0000\",\"alert_quantity\":\"0.0000\",\"image\":\"no_image.png\",\"category_id\":\"1\",\"subcategory_id\":\"13\",\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"600.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"4\",\"purchase_unit\":\"4\",\"brand\":\"0\",\"slug\":\"64969409\",\"featured\":\"1\",\"weight\":\"0.0000\",\"hsn_code\":null,\"views\":\"0\",\"hide\":\"0\",\"second_name\":\"\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:32'),
(29, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"31\",\"code\":\"69868596\",\"name\":\"test2\",\"unit\":\"1\",\"cost\":\"10.0000\",\"price\":\"15.0000\",\"alert_quantity\":\"0.0000\",\"image\":\"3a97ddf95fb53148f5ac9d8ad58cfa88.png\",\"category_id\":\"7\",\"subcategory_id\":null,\"cf1\":\"uae\",\"cf2\":\"no\",\"cf3\":\"abc\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"0.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"0\",\"details\":\"\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"1\",\"purchase_unit\":\"1\",\"brand\":\"3\",\"slug\":\"69868596\",\"featured\":null,\"weight\":\"1.0000\",\"hsn_code\":null,\"views\":\"0\",\"hide\":\"0\",\"second_name\":\"test ingredient\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:35'),
(30, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"8\",\"code\":\"8057506630011\",\"name\":\"Grow Hair Nails\",\"unit\":\"3\",\"cost\":\"83.0000\",\"price\":\"83.0000\",\"alert_quantity\":\"0.0000\",\"image\":\"cb582f21a6054d970f8c8b6b939e80b6.jpg\",\"category_id\":\"2\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"8.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<h2>Description<\\/h2><p>Indications: The active ingredients of the product are an effective remedy to protect the skin and its hangers (hair and nails).<\\/p><h4>How to Use:<\\/h4><p>It is recommended to take 2 capsules once a day with a glass of water.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Apple Annurca extract<\\/td><td>208mg<\\/td><\\/tr><tr><td>Collagen Peptiplus\\u00ae type Derma<\\/td><td>300mg<\\/td><\\/tr><tr><td>L-Cysteine<\\/td><td>100mg<\\/td><\\/tr><tr><td>L-Methionine<\\/td><td>110mg<\\/td><\\/tr><tr><td>Biotin<\\/td><td>450mcg<\\/td><\\/tr><tr><td>Zinc Gluconate<\\/td><td>12mg (Zinc)<\\/td><\\/tr><tr><td>Vitamin E<\\/td><td>12mg<\\/td><\\/tr><tr><td>Coenzyme Q10<\\/td><td>30mg<\\/td><\\/tr><tr><td>Selenium L-Methionine<\\/td><td>300mcg<\\/td><\\/tr><tr><td>Keratin<\\/td><td>20mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<h2>Description<\\/h2><p>Indications: The active ingredients of the product are an effective remedy to protect the skin and its hangers (hair and nails).<\\/p><h4>How to Use:<\\/h4><p>It is recommended to take 2 capsules once a day with a glass of water.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Apple Annurca extract<\\/td><td>208mg<\\/td><\\/tr><tr><td>Collagen Peptiplus\\u00ae type Derma<\\/td><td>300mg<\\/td><\\/tr><tr><td>L-Cysteine<\\/td><td>100mg<\\/td><\\/tr><tr><td>L-Methionine<\\/td><td>110mg<\\/td><\\/tr><tr><td>Biotin<\\/td><td>450mcg<\\/td><\\/tr><tr><td>Zinc Gluconate<\\/td><td>12mg (Zinc)<\\/td><\\/tr><tr><td>Vitamin E<\\/td><td>12mg<\\/td><\\/tr><tr><td>Coenzyme Q10<\\/td><td>30mg<\\/td><\\/tr><tr><td>Selenium L-Methionine<\\/td><td>300mcg<\\/td><\\/tr><tr><td>Keratin<\\/td><td>20mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"3\",\"purchase_unit\":\"3\",\"brand\":\"2\",\"slug\":\"grow-hair-nails\",\"featured\":\"1\",\"weight\":\"0.2000\",\"hsn_code\":null,\"views\":\"8\",\"hide\":\"0\",\"second_name\":\"Advanced Formula for Strong Hair &amp; Nails\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:37'),
(31, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"18\",\"code\":\"8057506630042\",\"name\":\"Men\\u2019s Fertility\",\"unit\":\"3\",\"cost\":\"113.0000\",\"price\":\"113.0000\",\"alert_quantity\":\"4.0000\",\"image\":\"3cbe39646029aed6fac02fe19ece9b36.jpg\",\"category_id\":\"8\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"6.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<h2>Description<\\/h2><p>Men\\u2019s Fertility\\u00ae unique formula provides all the essential nutrients needed to maintain healthy sperm count and motility. It protects the sperms from damaging oxidants and boosts the sperms\\u2019 vitality for maximum fertility.<\\/p><h4>How to Use:<\\/h4><p>Take 3 Capsules per day.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>L Carnitine l-tartrate<\\/td><td>333mg<\\/td><\\/tr><tr><td>L- Cysteine<\\/td><td>66mg<\\/td><\\/tr><tr><td>Maca<\\/td><td>66mg<\\/td><\\/tr><tr><td>Arginine<\\/td><td>66mg<\\/td><\\/tr><tr><td>Ginseng (Ginseng C.A. Mey)<\\/td><td>100mg<\\/td><\\/tr><tr><td>Co Q10<\\/td><td>33mg<\\/td><\\/tr><tr><td>Zinc<\\/td><td>5mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<h2>Description<\\/h2><p>Men\\u2019s Fertility\\u00ae unique formula provides all the essential nutrients needed to maintain healthy sperm count and motility. It protects the sperms from damaging oxidants and boosts the sperms\\u2019 vitality for maximum fertility.<\\/p><h4>How to Use:<\\/h4><p>Take 3 Capsules per day.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>L Carnitine l-tartrate<\\/td><td>333mg<\\/td><\\/tr><tr><td>L- Cysteine<\\/td><td>66mg<\\/td><\\/tr><tr><td>Maca<\\/td><td>66mg<\\/td><\\/tr><tr><td>Arginine<\\/td><td>66mg<\\/td><\\/tr><tr><td>Ginseng (Ginseng C.A. Mey)<\\/td><td>100mg<\\/td><\\/tr><tr><td>Co Q10<\\/td><td>33mg<\\/td><\\/tr><tr><td>Zinc<\\/td><td>5mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"3\",\"purchase_unit\":\"3\",\"brand\":\"2\",\"slug\":\"mens-fertility\",\"featured\":\"1\",\"weight\":\"0.2000\",\"hsn_code\":null,\"views\":\"26\",\"hide\":\"0\",\"second_name\":\"Promotes Healthier Sperms\\u2019 Count &amp; Motility\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:40'),
(32, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"9\",\"code\":\"8057506630066\",\"name\":\"Flex\",\"unit\":\"3\",\"cost\":\"113.0000\",\"price\":\"113.0000\",\"alert_quantity\":\"4.0000\",\"image\":\"a3ddd2b15c1e63e37a363658aa87370c.jpg\",\"category_id\":\"4\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"3.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<h2>Description<\\/h2><p>Flex\\u00ae improves joint mobility and function. Also, its unique ingredients help alleviating the pain and inflammation.<\\/p><h4>How to Use:<\\/h4><p>1 mini bottle per day.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 Bottle<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>MSM Methylsulfonylmethane<\\/td><td>500 mg<\\/td><\\/tr><tr><td>Hydrolyzed collagen type 2<\\/td><td>10 mg<\\/td><\\/tr><tr><td>Chondroitin sulphate<\\/td><td>20 mg<\\/td><\\/tr><tr><td>Capsaicin<\\/td><td>3 mg<\\/td><\\/tr><tr><td>Vitamin C<\\/td><td>100 mg<\\/td><\\/tr><tr><td>Acido Ialuronico<\\/td><td>200 mg<\\/td><\\/tr><tr><td>Device Clow<\\/td><td>300 mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<h2>Description<\\/h2><p>Flex\\u00ae improves joint mobility and function. Also, its unique ingredients help alleviating the pain and inflammation.<\\/p><h4>How to Use:<\\/h4><p>1 mini bottle per day.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 Bottle<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>MSM Methylsulfonylmethane<\\/td><td>500 mg<\\/td><\\/tr><tr><td>Hydrolyzed collagen type 2<\\/td><td>10 mg<\\/td><\\/tr><tr><td>Chondroitin sulphate<\\/td><td>20 mg<\\/td><\\/tr><tr><td>Capsaicin<\\/td><td>3 mg<\\/td><\\/tr><tr><td>Vitamin C<\\/td><td>100 mg<\\/td><\\/tr><tr><td>Acido Ialuronico<\\/td><td>200 mg<\\/td><\\/tr><tr><td>Device Clow<\\/td><td>300 mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"3\",\"purchase_unit\":\"3\",\"brand\":\"2\",\"slug\":\"flex\",\"featured\":\"1\",\"weight\":\"0.2000\",\"hsn_code\":null,\"views\":\"41\",\"hide\":\"0\",\"second_name\":\"Promotes Better Joints\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:43'),
(33, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"15\",\"code\":\"8057506630097\",\"name\":\"CoEnzyme\",\"unit\":\"3\",\"cost\":\"44.0000\",\"price\":\"44.0000\",\"alert_quantity\":\"4.0000\",\"image\":\"86d46a11b747ea32404eeec740215cd7.jpg\",\"category_id\":\"7\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"6.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<h2>Description<\\/h2><p>CoEnzyme Q10\\u00ae has a powerful antioxidant properties. Also, It reduces LDL Cholesterol and prevents heart diseases and cellular aging.<\\/p><h4>How to Use:<\\/h4><p>Take 1 capsule everyday.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p>\\r\\n<table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Co-Enzyme Q10<\\/td><td>500mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<h2>Description<\\/h2><p>CoEnzyme Q10\\u00ae has a powerful antioxidant properties. Also, It reduces LDL Cholesterol and prevents heart diseases and cellular aging.<\\/p><h4>How to Use:<\\/h4><p>Take 1 capsule everyday.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p>\\r\\n<table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Co-Enzyme Q10<\\/td><td>500mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"3\",\"purchase_unit\":\"3\",\"brand\":\"2\",\"slug\":\"coenzyme\",\"featured\":\"1\",\"weight\":\"0.2000\",\"hsn_code\":null,\"views\":\"8\",\"hide\":\"0\",\"second_name\":\"Support Cellular Energy Production &amp; Healthy Heart Function\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:46'),
(34, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"22\",\"code\":\"8057506630110\",\"name\":\"Black Seed\",\"unit\":\"3\",\"cost\":\"45.0000\",\"price\":\"45.0000\",\"alert_quantity\":\"0.0000\",\"image\":\"3ca10cac16cfa9cc3e38aa579f960403.jpg\",\"category_id\":\"3\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"8.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<h2>Description<\\/h2><p>Black Seed\\u00ae has a unique formula which contains Black Seed which is a spice with remarkable antioxidant properties and rich of fibers and antioxidants.<\\/p><p>Black Seed\\u00ae also contains Vitamin E, Folic acid, Vitamin D & Biotin for maximum health benefits for the body and well-being.<\\/p><h4>How to Use:<\\/h4><p>Take 1 capsule everyday.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p>\\r\\n<table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Black seed<\\/td><td>400mg<\\/td><\\/tr><tr><td>Vitamin E<\\/td><td>25mg<\\/td><\\/tr><tr><td>Folic Acid<\\/td><td>200mcg<\\/td><\\/tr><tr><td>Vitamin D<\\/td><td>15mcg<\\/td><\\/tr><tr><td>Biotin<\\/td><td>500mcg<\\/td><\\/tr><\\/tbody><\\/table>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<h2>Description<\\/h2><p>Black Seed\\u00ae has a unique formula which contains Black Seed which is a spice with remarkable antioxidant properties and rich of fibers and antioxidants.<\\/p><p>Black Seed\\u00ae also contains Vitamin E, Folic acid, Vitamin D & Biotin for maximum health benefits for the body and well-being.<\\/p><h4>How to Use:<\\/h4><p>Take 1 capsule everyday.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p>\\r\\n<table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Black seed<\\/td><td>400mg<\\/td><\\/tr><tr><td>Vitamin E<\\/td><td>25mg<\\/td><\\/tr><tr><td>Folic Acid<\\/td><td>200mcg<\\/td><\\/tr><tr><td>Vitamin D<\\/td><td>15mcg<\\/td><\\/tr><tr><td>Biotin<\\/td><td>500mcg<\\/td><\\/tr><\\/tbody><\\/table>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"3\",\"purchase_unit\":\"3\",\"brand\":\"2\",\"slug\":\"black-seed\",\"featured\":\"1\",\"weight\":\"0.2000\",\"hsn_code\":null,\"views\":\"5\",\"hide\":\"0\",\"second_name\":\"Fortified with Vitamin D, Folic Acid, Vitamin E &amp; Biotin\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:55'),
(35, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"11\",\"code\":\"8057506630158\",\"name\":\"L-Carnitine\",\"unit\":\"1\",\"cost\":\"105.0000\",\"price\":\"105.0000\",\"alert_quantity\":\"0.0000\",\"image\":\"e69124b504f0e0858dfe5e7813c9cafe.jpg\",\"category_id\":\"5\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"0.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<h2>Description<\\/h2><p>L-Carnitine is a non-essential amino acid that helps to maintain overall good health by facilitating the transfer of fatty acid groups into the mitochondrial membrane for cellular energy production.<\\/p><h4>How to Use:<\\/h4><p>1 mini bottle per day.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 Bottl<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>L-Carnitine Tartrate<\\/td><td>1000 mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<h2>Description<\\/h2><p>L-Carnitine is a non-essential amino acid that helps to maintain overall good health by facilitating the transfer of fatty acid groups into the mitochondrial membrane for cellular energy production.<\\/p><h4>How to Use:<\\/h4><p>1 mini bottle per day.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 Bottl<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>L-Carnitine Tartrate<\\/td><td>1000 mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"1\",\"purchase_unit\":\"1\",\"brand\":\"2\",\"slug\":\"l-carnitine\",\"featured\":\"1\",\"weight\":\"0.2000\",\"hsn_code\":null,\"views\":\"4\",\"hide\":\"0\",\"second_name\":\"Boosts Cellular Energy &amp;amp; Improves Performance\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:55');
INSERT INTO `sma_logs` (`id`, `detail`, `model`, `date`) VALUES
(36, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"14\",\"code\":\"8057506630196\",\"name\":\"Immunity Defense\",\"unit\":\"3\",\"cost\":\"94.0000\",\"price\":\"94.0000\",\"alert_quantity\":\"4.0000\",\"image\":\"f00a60a034b0c667e98a62b5b187c775.jpg\",\"category_id\":\"6\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"17.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<h2>Description<\\/h2><p>Immunity Defense\\u00ae is a unique formula to support and strengthens the immune system.<\\/p><p>Quercetin which has a remarkable antioxidant function as it reduces lipid peroxidation and works in synergistic biological action with Vitamin D3 & Vitamin C for maximum immune system functions.<\\/p><h4>How to Use:<\\/h4><p>Take 1 capsule everyday.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Quercetin<\\/td><td>500mg<\\/td><\\/tr><tr><td>Vitamin C<\\/td><td>250mg<\\/td><\\/tr><tr><td>Vitamin D3<\\/td><td>50mcg<\\/td><\\/tr><\\/tbody><\\/table>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<h2>Description<\\/h2><p>Immunity Defense\\u00ae is a unique formula to support and strengthens the immune system.<\\/p><p>Quercetin which has a remarkable antioxidant function as it reduces lipid peroxidation and works in synergistic biological action with Vitamin D3 & Vitamin C for maximum immune system functions.<\\/p><h4>How to Use:<\\/h4><p>Take 1 capsule everyday.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Quercetin<\\/td><td>500mg<\\/td><\\/tr><tr><td>Vitamin C<\\/td><td>250mg<\\/td><\\/tr><tr><td>Vitamin D3<\\/td><td>50mcg<\\/td><\\/tr><\\/tbody><\\/table>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"3\",\"purchase_unit\":\"3\",\"brand\":\"2\",\"slug\":\"immunity-defense\",\"featured\":\"1\",\"weight\":\"0.0000\",\"hsn_code\":null,\"views\":\"3\",\"hide\":\"0\",\"second_name\":\"Maximum Immunity\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:55'),
(37, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"20\",\"code\":\"8057506630226\",\"name\":\"UTI Free\",\"unit\":\"3\",\"cost\":\"68.0000\",\"price\":\"68.0000\",\"alert_quantity\":\"4.0000\",\"image\":\"06d6d1d3da379e6a36db2f0b626633be.jpg\",\"category_id\":\"9\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"0.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<h2>Description<\\/h2><p>UTI Free\\u00ae is a cranberry-based formula that is useful in the drainage of body fluids. It also promotes an antibacterial action, especially at the level of the urinary system. It has the ability to make the surface of the mucous membranes anti-adhesive, both in the bladder and in the intestine. This helps to limit the colonization of pathogenic infectious agents, including E. coli.<\\/p><h4>How to Use:<\\/h4><p>Take two capsules a day, with plenty of water, preferably before meals.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Cranberry Powder<\\/td><td>250mg<\\/td><\\/tr><tr><td>D \\u2013 Mannose<\\/td><td>500mg<\\/td><\\/tr><tr><td>Lactobacillus Rhamnosus<\\/td><td>40mg equal to 6 billion CFU.<\\/td><\\/tr><\\/tbody><\\/table>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<h2>Description<\\/h2><p>UTI Free\\u00ae is a cranberry-based formula that is useful in the drainage of body fluids. It also promotes an antibacterial action, especially at the level of the urinary system. It has the ability to make the surface of the mucous membranes anti-adhesive, both in the bladder and in the intestine. This helps to limit the colonization of pathogenic infectious agents, including E. coli.<\\/p><h4>How to Use:<\\/h4><p>Take two capsules a day, with plenty of water, preferably before meals.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Cranberry Powder<\\/td><td>250mg<\\/td><\\/tr><tr><td>D \\u2013 Mannose<\\/td><td>500mg<\\/td><\\/tr><tr><td>Lactobacillus Rhamnosus<\\/td><td>40mg equal to 6 billion CFU.<\\/td><\\/tr><\\/tbody><\\/table>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"3\",\"purchase_unit\":\"3\",\"brand\":\"2\",\"slug\":\"uti-free\",\"featured\":\"1\",\"weight\":\"0.2000\",\"hsn_code\":null,\"views\":\"11\",\"hide\":\"0\",\"second_name\":\"Support Healthy Urinary Tract System\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:55'),
(38, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"13\",\"code\":\"8057506630233\",\"name\":\" Free Oxidant\",\"unit\":\"3\",\"cost\":\"60.0000\",\"price\":\"60.0000\",\"alert_quantity\":\"4.0000\",\"image\":\"f26755a51ebe90a908f845d46b6c978e.jpg\",\"category_id\":\"6\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"0.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<h2>Description<\\/h2><p>Free Oxidant\\u00ae contains three ingredients that works in synergy to provide maximum antioxidant benefits. Selenium is considered one of the most powerful antioxidant agents. It also participates in the regulation of thyroid function. Quercetin has a remarkable antioxidant function as it reduces lipid peroxidation and works in synergy with Vitamin C.<\\/p><h4>How to Use:<\\/h4><p>Take two capsules a day, with plenty of water, preferably in the morning or in the evening.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Selenium<\\/td><td>50 mg<\\/td><\\/tr><tr><td>Quercetin<\\/td><td>200 mg<\\/td><\\/tr><tr><td>Vitamin C<\\/td><td>250 mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<h2>Description<\\/h2><p>Free Oxidant\\u00ae contains three ingredients that works in synergy to provide maximum antioxidant benefits. Selenium is considered one of the most powerful antioxidant agents. It also participates in the regulation of thyroid function. Quercetin has a remarkable antioxidant function as it reduces lipid peroxidation and works in synergy with Vitamin C.<\\/p><h4>How to Use:<\\/h4><p>Take two capsules a day, with plenty of water, preferably in the morning or in the evening.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Selenium<\\/td><td>50 mg<\\/td><\\/tr><tr><td>Quercetin<\\/td><td>200 mg<\\/td><\\/tr><tr><td>Vitamin C<\\/td><td>250 mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"3\",\"purchase_unit\":\"3\",\"brand\":\"2\",\"slug\":\"free-oxidant\",\"featured\":null,\"weight\":\"0.2000\",\"hsn_code\":null,\"views\":\"10\",\"hide\":\"0\",\"second_name\":\"Powerful Antioxidants for Maximum Immunity\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:55'),
(39, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"25\",\"code\":\"8057506630240\",\"name\":\"Detoxy\",\"unit\":\"3\",\"cost\":\"64.0000\",\"price\":\"64.0000\",\"alert_quantity\":\"4.0000\",\"image\":\"64e2b9ee0558b9094d40b43e8a759f25.jpg\",\"category_id\":\"11\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"0.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<h2>Description<\\/h2><p>Glutathione is composed of 3 amino acids: glutamic acid, cysteine and glycine. It has a detoxifying function at the level of the liver and is useful for avoiding the side effects of chemotherapy at the level of the kidneys and nervous system. Selenium fights oxidative stress at the liver level.<\\/p><h4>How to Use:<\\/h4><p>Two capsules daily preferably during meals.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>L-Glutathione<\\/td><td>500mg<\\/td><\\/tr><tr><td>L-Cystine<\\/td><td>100mg<\\/td><\\/tr><tr><td>Selenium<\\/td><td>50mcg<\\/td><\\/tr><\\/tbody><\\/table>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<h2>Description<\\/h2><p>Glutathione is composed of 3 amino acids: glutamic acid, cysteine and glycine. It has a detoxifying function at the level of the liver and is useful for avoiding the side effects of chemotherapy at the level of the kidneys and nervous system. Selenium fights oxidative stress at the liver level.<\\/p><h4>How to Use:<\\/h4><p>Two capsules daily preferably during meals.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>L-Glutathione<\\/td><td>500mg<\\/td><\\/tr><tr><td>L-Cystine<\\/td><td>100mg<\\/td><\\/tr><tr><td>Selenium<\\/td><td>50mcg<\\/td><\\/tr><\\/tbody><\\/table>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"3\",\"purchase_unit\":\"3\",\"brand\":\"2\",\"slug\":\"detoxy\",\"featured\":null,\"weight\":\"0.2000\",\"hsn_code\":null,\"views\":\"0\",\"hide\":\"0\",\"second_name\":\"Advanced Formula for Body Cleansing & Detoxification\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:55'),
(40, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"7\",\"code\":\"8057506630271\",\"name\":\"Anti Aging\",\"unit\":\"3\",\"cost\":\"94.0000\",\"price\":\"94.0000\",\"alert_quantity\":\"4.0000\",\"image\":\"64c6205e417559cb48c8962602c0dcc3.jpg\",\"category_id\":\"2\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"9.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<h2>Description<\\/h2><p>Honst Anti Aging\\u00ae is filled with potent antioxidant for maximum health benefits to fight aging signs. It fights free radicals at the cellular level for maximum benefits for overall beauty, total body & cardiovascular health.<\\/p><h4>How to Use:<\\/h4><p>Two capsules daily preferably during meals.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Vitamin E<\\/td><td>60mg<\\/td><\\/tr><tr><td>Vitamin D3<\\/td><td>50mcg<\\/td><\\/tr><tr><td>Vitamin K<\\/td><td>200mcg<\\/td><\\/tr><tr><td>Resveratrol<\\/td><td>50mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<h2>Description<\\/h2><p>Honst Anti Aging\\u00ae is filled with potent antioxidant for maximum health benefits to fight aging signs. It fights free radicals at the cellular level for maximum benefits for overall beauty, total body & cardiovascular health.<\\/p><h4>How to Use:<\\/h4><p>Two capsules daily preferably during meals.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Vitamin E<\\/td><td>60mg<\\/td><\\/tr><tr><td>Vitamin D3<\\/td><td>50mcg<\\/td><\\/tr><tr><td>Vitamin K<\\/td><td>200mcg<\\/td><\\/tr><tr><td>Resveratrol<\\/td><td>50mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"3\",\"purchase_unit\":\"3\",\"brand\":\"2\",\"slug\":\"anti-aging\",\"featured\":null,\"weight\":\"0.2000\",\"hsn_code\":null,\"views\":\"40\",\"hide\":\"0\",\"second_name\":\"Longevity Support Skin, Body &amp; Cardiovascular\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:55'),
(41, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"19\",\"code\":\"8057506630288\",\"name\":\"Evening Primrose\",\"unit\":\"3\",\"cost\":\"68.0000\",\"price\":\"68.0000\",\"alert_quantity\":\"4.0000\",\"image\":\"1c7b22099fd5136e606efc12201b80e6.jpg\",\"category_id\":\"9\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"0.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<h2>Description<\\/h2><p>Evening Primrose\\u00ae is a precursor of hormone-like prostaglandins that have remarkable biological and health properties. It supports female hormones balance especially during periods and menapause. Also, it helps maintaining strong cardiovascular and immune system.<\\/p><h4>How to Use:<\\/h4><p>Take 1 capsule daily.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Primrose Oil (Evening Primrose Oil)<\\/td><td>1300mg<\\/td><\\/tr><tr><td>(Ac. Cis-Linoleic)<\\/td><td>900mg<\\/td><\\/tr><tr><td>(Ac, Gamma-Linoleic)<\\/td><td>130mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<h2>Description<\\/h2><p>Evening Primrose\\u00ae is a precursor of hormone-like prostaglandins that have remarkable biological and health properties. It supports female hormones balance especially during periods and menapause. Also, it helps maintaining strong cardiovascular and immune system.<\\/p><h4>How to Use:<\\/h4><p>Take 1 capsule daily.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Primrose Oil (Evening Primrose Oil)<\\/td><td>1300mg<\\/td><\\/tr><tr><td>(Ac. Cis-Linoleic)<\\/td><td>900mg<\\/td><\\/tr><tr><td>(Ac, Gamma-Linoleic)<\\/td><td>130mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"3\",\"purchase_unit\":\"3\",\"brand\":\"2\",\"slug\":\"evening-primrose\",\"featured\":null,\"weight\":\"0.2000\",\"hsn_code\":null,\"views\":\"0\",\"hide\":\"0\",\"second_name\":\"Promotes Healthy Female Hormones Balanc\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:55'),
(42, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"17\",\"code\":\"8057506630301\",\"name\":\"Maximum Power\",\"unit\":\"3\",\"cost\":\"75.0000\",\"price\":\"75.0000\",\"alert_quantity\":\"4.0000\",\"image\":\"b8c322a673c85dc96c2a67305a36341d.jpg\",\"category_id\":\"8\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"5.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<h2>Description<\\/h2><p>Indications: Naturally helps boosting energy levels and maintaining healthy cardiovascular & immune system.<\\/p><h4>How to Use:<\\/h4><p>Take 1 vial per day.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 VIAL<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Ashwagandha<\\/td><td>500mg<\\/td><\\/tr><tr><td>L-Arginine<\\/td><td>1000mg<\\/td><\\/tr><tr><td>Shilajit<\\/td><td>250mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<h2>Description<\\/h2><p>Indications: Naturally helps boosting energy levels and maintaining healthy cardiovascular & immune system.<\\/p><h4>How to Use:<\\/h4><p>Take 1 vial per day.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 VIAL<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Ashwagandha<\\/td><td>500mg<\\/td><\\/tr><tr><td>L-Arginine<\\/td><td>1000mg<\\/td><\\/tr><tr><td>Shilajit<\\/td><td>250mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"3\",\"purchase_unit\":\"3\",\"brand\":\"2\",\"slug\":\"maximum-power\",\"featured\":\"1\",\"weight\":\"0.2000\",\"hsn_code\":null,\"views\":\"17\",\"hide\":\"0\",\"second_name\":\"Lorem Epsum\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:55'),
(43, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"12\",\"code\":\"8057506630318\",\"name\":\"Maximum Energy\",\"unit\":\"1\",\"cost\":\"75.0000\",\"price\":\"75.0000\",\"alert_quantity\":\"0.0000\",\"image\":\"5262f67bc11e794b03fdb5d8d7ed180f.jpg\",\"category_id\":\"5\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"10.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<h2>Description<\\/h2><p>Indications: Naturally helps boosting energy levels and maintaining healthy cardiovascular & immune system.<\\/p><h4>How to Use:<\\/h4><p>Take 1 vial per day.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 VIAL<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Ashwagandha<\\/td><td>500mg<\\/td><\\/tr><tr><td>L-Arginine<\\/td><td>1000mg<\\/td><\\/tr><tr><td>Shilajit<\\/td><td>250mg<\\/td><\\/tr><tr><td>L-Acetyl Carnitine<\\/td><td>1000mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<h2>Description<\\/h2><p>Indications: Naturally helps boosting energy levels and maintaining healthy cardiovascular & immune system.<\\/p><h4>How to Use:<\\/h4><p>Take 1 vial per day.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 VIAL<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Ashwagandha<\\/td><td>500mg<\\/td><\\/tr><tr><td>L-Arginine<\\/td><td>1000mg<\\/td><\\/tr><tr><td>Shilajit<\\/td><td>250mg<\\/td><\\/tr><tr><td>L-Acetyl Carnitine<\\/td><td>1000mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"1\",\"purchase_unit\":\"1\",\"brand\":\"2\",\"slug\":\"maximum-energy\",\"featured\":null,\"weight\":\"0.2000\",\"hsn_code\":null,\"views\":\"0\",\"hide\":\"0\",\"second_name\":\"Lorem epsum\",\"hide_pos\":\"0\"}}', '2022-12-26 10:14:55'),
(44, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"10\",\"code\":\"8057506630332\",\"name\":\"Healthy Joints\",\"unit\":\"3\",\"cost\":\"120.0000\",\"price\":\"120.0000\",\"alert_quantity\":\"0.0000\",\"image\":\"6b29e727b87909895f076577b491467d.jpg\",\"category_id\":\"4\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"10.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<h2>Description<\\/h2><p>Healthy Joints\\u00ae is a unique formula that contains Glucosamine, Chondroitin, Collagen, Vitamin C & Vitamin D3. This formula contribute to the synthesis and maintenance of the constituent elements of joint cartilage. Therefore they help to maintain the well-being of the joints by supporting the physiological functioning of the joint cartilages. Collagen provides the elements useful for the synthesis of cartilage.<\\/p><h4>How to Use:<\\/h4><p>1 mini bottle per day.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 Bottle<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Type II Collagen<\\/td><td>5000mg<\\/td><\\/tr><tr><td>Glucosamine Sulphate<\\/td><td>500mg<\\/td><\\/tr><tr><td>Conditrin Sulphate<\\/td><td>300mg<\\/td><\\/tr><tr><td>Vitamin D (D3 800UI)<\\/td><td>10mcg<\\/td><\\/tr><tr><td>Vitamin C<\\/td><td>200mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<h2>Description<\\/h2><p>Healthy Joints\\u00ae is a unique formula that contains Glucosamine, Chondroitin, Collagen, Vitamin C & Vitamin D3. This formula contribute to the synthesis and maintenance of the constituent elements of joint cartilage. Therefore they help to maintain the well-being of the joints by supporting the physiological functioning of the joint cartilages. Collagen provides the elements useful for the synthesis of cartilage.<\\/p><h4>How to Use:<\\/h4><p>1 mini bottle per day.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 Bottle<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Type II Collagen<\\/td><td>5000mg<\\/td><\\/tr><tr><td>Glucosamine Sulphate<\\/td><td>500mg<\\/td><\\/tr><tr><td>Conditrin Sulphate<\\/td><td>300mg<\\/td><\\/tr><tr><td>Vitamin D (D3 800UI)<\\/td><td>10mcg<\\/td><\\/tr><tr><td>Vitamin C<\\/td><td>200mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"3\",\"purchase_unit\":\"3\",\"brand\":\"2\",\"slug\":\"healthy-joints\",\"featured\":null,\"weight\":\"0.2000\",\"hsn_code\":null,\"views\":\"1\",\"hide\":\"0\",\"second_name\":\"Glucosamine, Chondrotitin, Collagen, Vitamin C & D3\",\"hide_pos\":\"0\"}}', '2022-12-26 10:15:12'),
(45, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"16\",\"code\":\"8057506630394\",\"name\":\"Healthy Vasco\",\"unit\":\"3\",\"cost\":\"64.0000\",\"price\":\"64.0000\",\"alert_quantity\":\"0.0000\",\"image\":\"7fcf1d4b5bf09bac094371eafbbe5813.jpg\",\"category_id\":\"7\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"10.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<h2>Description<\\/h2><p>Healthy Vasco\\u00ae is a unique formula of Garlic, Lycopene & Resveratrol. It provides antioxidant properties against the aggressiveness of free radicals. Promotes vascular protection, helps regulate blood sugar r and boosts immune system.<\\/p><h4>How to Use:<\\/h4><p>Take 1 capsule everyday.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Licopene<\\/td><td>100mg<\\/td><\\/tr><tr><td>Resveratrolo<\\/td><td>40mg<\\/td><\\/tr><tr><td>Garlic<\\/td><td>500mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<h2>Description<\\/h2><p>Healthy Vasco\\u00ae is a unique formula of Garlic, Lycopene & Resveratrol. It provides antioxidant properties against the aggressiveness of free radicals. Promotes vascular protection, helps regulate blood sugar r and boosts immune system.<\\/p><h4>How to Use:<\\/h4><p>Take 1 capsule everyday.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Licopene<\\/td><td>100mg<\\/td><\\/tr><tr><td>Resveratrolo<\\/td><td>40mg<\\/td><\\/tr><tr><td>Garlic<\\/td><td>500mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"3\",\"purchase_unit\":\"3\",\"brand\":\"2\",\"slug\":\"healthy-vasco\",\"featured\":null,\"weight\":\"0.2000\",\"hsn_code\":null,\"views\":\"0\",\"hide\":\"0\",\"second_name\":\"Supports Healthy Cardiovascular Function\",\"hide_pos\":\"0\"}}', '2022-12-26 10:15:12'),
(46, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"24\",\"code\":\"8057506630431\",\"name\":\"Clean Liver\",\"unit\":\"4\",\"cost\":\"72.0000\",\"price\":\"72.0000\",\"alert_quantity\":\"4.0000\",\"image\":\"ab2585a400e3844b2cee7df07f1c9687.jpg\",\"category_id\":\"11\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"0.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<h2>Description<\\/h2><p>Clean Liver\\u00ae contains Turmeric (the active principle of Curcumin) which is a powerful anti-inflammatory and antioxidant. Along with Milk thistle, Bitter Fillanthus & Chicory for maximum liver benefits.<\\/p><h4>How to Use:<\\/h4><p>Take 2 capsules everyday.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Turmeric D.E. Tit. 95%<br>(of which curcuminoids)<\\/td><td>400mg<br>380mg<\\/td><\\/tr><tr><td>Black Pepper (Bioperin) D.E. Titrated 95%<br>(of which Paperin)<\\/td><td>20mg<br>19mg<\\/td><\\/tr><tr><td>Milk Thistle (Silybum Marianum Gaertn.) Fruits D.E. Tit. 80% Silymarin<\\/td><\\/tr><tr><td>Bitter Fillanthus Extract (Phyllanthus Niruri L.) Top D.E. Tit. 3% Tannins<\\/td><td>200mg<\\/td><\\/tr><tr><td>Chicory (Cichorium Intybus)<\\/td><td>200mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<h2>Description<\\/h2><p>Clean Liver\\u00ae contains Turmeric (the active principle of Curcumin) which is a powerful anti-inflammatory and antioxidant. Along with Milk thistle, Bitter Fillanthus & Chicory for maximum liver benefits.<\\/p><h4>How to Use:<\\/h4><p>Take 2 capsules everyday.<\\/p><h4>Supplement Facts:<\\/h4><p>AMOUNT PER 1 CAPSULE<\\/p><table><thead><tr><th>Ingredients<\\/th><th>Amount per serving size<\\/th><\\/tr><\\/thead><tbody><tr><td>Turmeric D.E. Tit. 95%<br>(of which curcuminoids)<\\/td><td>400mg<br>380mg<\\/td><\\/tr><tr><td>Black Pepper (Bioperin) D.E. Titrated 95%<br>(of which Paperin)<\\/td><td>20mg<br>19mg<\\/td><\\/tr><tr><td>Milk Thistle (Silybum Marianum Gaertn.) Fruits D.E. Tit. 80% Silymarin<\\/td><\\/tr><tr><td>Bitter Fillanthus Extract (Phyllanthus Niruri L.) Top D.E. Tit. 3% Tannins<\\/td><td>200mg<\\/td><\\/tr><tr><td>Chicory (Cichorium Intybus)<\\/td><td>200mg<\\/td><\\/tr><\\/tbody><\\/table>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"2\",\"purchase_unit\":\"2\",\"brand\":\"2\",\"slug\":\"clean-liver\",\"featured\":null,\"weight\":\"0.2000\",\"hsn_code\":null,\"views\":\"1\",\"hide\":\"0\",\"second_name\":\"Natural Support for Healthy Liver Function\",\"hide_pos\":\"0\"}}', '2022-12-26 10:15:12'),
(47, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"33\",\"code\":\"84813010\",\"name\":\"Product test 1\",\"unit\":\"3\",\"cost\":\"10.0000\",\"price\":\"15.0000\",\"alert_quantity\":\"5.0000\",\"image\":\"451c8d346c4a329df8bbb78bb57fa646.jpg\",\"category_id\":\"12\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"abc\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"35.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"3\",\"purchase_unit\":\"3\",\"brand\":\"2\",\"slug\":\"product-test-1\",\"featured\":null,\"weight\":\"0.5000\",\"hsn_code\":null,\"views\":\"0\",\"hide\":\"0\",\"second_name\":\"test ingredient\",\"hide_pos\":\"0\"}}', '2022-12-26 10:15:12'),
(48, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"23\",\"code\":\"93555055\",\"name\":\"Beauty Combo\",\"unit\":null,\"cost\":null,\"price\":\"60.0000\",\"alert_quantity\":\"0.0000\",\"image\":\"no_image.png\",\"category_id\":\"2\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"1\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"0.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"0\",\"details\":\"<p>Test<\\/p>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<p>Test<\\/p>\",\"tax_method\":\"1\",\"type\":\"combo\",\"supplier1\":\"0\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"0\",\"purchase_unit\":\"0\",\"brand\":\"2\",\"slug\":\"93555055\",\"featured\":null,\"weight\":\"0.5000\",\"hsn_code\":null,\"views\":\"0\",\"hide\":\"0\",\"second_name\":\"Test\",\"hide_pos\":\"0\"}}', '2022-12-26 10:15:12'),
(49, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"27\",\"code\":\"LANDPH001\",\"name\":\"SULFAD 1GM\",\"unit\":\"2\",\"cost\":\"100.0000\",\"price\":\"230.0000\",\"alert_quantity\":\"50.0000\",\"image\":\"no_image.png\",\"category_id\":\"1\",\"subcategory_id\":\"13\",\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"0.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"<p>FATTY LIVER FOR INVOICE<\\/p>\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"<p>FATTY LIVER<\\/p>\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"31\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":\"1\",\"promo_price\":\"200.0000\",\"start_date\":\"2022-12-16\",\"end_date\":\"2022-12-31\",\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"2\",\"purchase_unit\":\"2\",\"brand\":\"3\",\"slug\":\"sulfad-1gm\",\"featured\":\"1\",\"weight\":\"0.0000\",\"hsn_code\":null,\"views\":\"5\",\"hide\":\"0\",\"second_name\":\"\",\"hide_pos\":\"0\"}}', '2022-12-26 10:15:12'),
(50, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"3\",\"code\":\"TPR-11\",\"name\":\"Test Product 11\",\"unit\":\"2\",\"cost\":\"90.0000\",\"price\":\"100.0000\",\"alert_quantity\":\"20.0000\",\"image\":\"no_image.png\",\"category_id\":\"1\",\"subcategory_id\":null,\"cf1\":\"CF1\",\"cf2\":\"CF2\",\"cf3\":\"CF3\",\"cf4\":\"CF4\",\"cf5\":\"CF5\",\"cf6\":\"CF6\",\"quantity\":\"18.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":null,\"product_details\":\"\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":null,\"supplier1price\":\"90.0000\",\"supplier2\":null,\"supplier2price\":\"0.0000\",\"supplier3\":null,\"supplier3price\":\"0.0000\",\"supplier4\":null,\"supplier4price\":\"0.0000\",\"supplier5\":null,\"supplier5price\":\"0.0000\",\"promotion\":\"0\",\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"NG01\",\"supplier2_part_no\":\"\",\"supplier3_part_no\":\"\",\"supplier4_part_no\":\"\",\"supplier5_part_no\":\"\",\"sale_unit\":\"2\",\"purchase_unit\":\"2\",\"brand\":null,\"slug\":\"test-product-11\",\"featured\":null,\"weight\":\"0.2000\",\"hsn_code\":\"0\",\"views\":\"0\",\"hide\":\"0\",\"second_name\":\"Pro 11\",\"hide_pos\":\"0\"}}', '2022-12-26 10:15:12'),
(51, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"4\",\"code\":\"TPR-12\",\"name\":\"Test Product 12\",\"unit\":\"2\",\"cost\":\"90.0000\",\"price\":\"100.0000\",\"alert_quantity\":\"20.0000\",\"image\":\"no_image.png\",\"category_id\":\"1\",\"subcategory_id\":null,\"cf1\":\"CF1\",\"cf2\":\"CF2\",\"cf3\":\"CF3\",\"cf4\":\"CF4\",\"cf5\":\"CF5\",\"cf6\":\"CF6\",\"quantity\":\"0.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":null,\"product_details\":\"\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":null,\"supplier1price\":\"90.0000\",\"supplier2\":null,\"supplier2price\":\"0.0000\",\"supplier3\":null,\"supplier3price\":\"0.0000\",\"supplier4\":null,\"supplier4price\":\"0.0000\",\"supplier5\":null,\"supplier5price\":\"0.0000\",\"promotion\":\"0\",\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"NG01\",\"supplier2_part_no\":\"\",\"supplier3_part_no\":\"\",\"supplier4_part_no\":\"\",\"supplier5_part_no\":\"\",\"sale_unit\":\"2\",\"purchase_unit\":\"2\",\"brand\":null,\"slug\":\"test-product-12\",\"featured\":null,\"weight\":\"0.2000\",\"hsn_code\":\"0\",\"views\":\"0\",\"hide\":\"0\",\"second_name\":\"Pro 11\",\"hide_pos\":\"0\"}}', '2022-12-26 10:15:12'),
(52, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"5\",\"code\":\"TPR-13\",\"name\":\"Test Product 13\",\"unit\":\"2\",\"cost\":\"90.0000\",\"price\":\"100.0000\",\"alert_quantity\":\"20.0000\",\"image\":\"no_image.png\",\"category_id\":\"1\",\"subcategory_id\":null,\"cf1\":\"CF1\",\"cf2\":\"CF2\",\"cf3\":\"CF3\",\"cf4\":\"CF4\",\"cf5\":\"CF5\",\"cf6\":\"CF6\",\"quantity\":\"0.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":null,\"product_details\":\"\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":null,\"supplier1price\":\"90.0000\",\"supplier2\":null,\"supplier2price\":\"0.0000\",\"supplier3\":null,\"supplier3price\":\"0.0000\",\"supplier4\":null,\"supplier4price\":\"0.0000\",\"supplier5\":null,\"supplier5price\":\"0.0000\",\"promotion\":\"0\",\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"NG01\",\"supplier2_part_no\":\"\",\"supplier3_part_no\":\"\",\"supplier4_part_no\":\"\",\"supplier5_part_no\":\"\",\"sale_unit\":\"2\",\"purchase_unit\":\"2\",\"brand\":null,\"slug\":\"test-product-13\",\"featured\":null,\"weight\":\"0.2000\",\"hsn_code\":\"0\",\"views\":\"0\",\"hide\":\"0\",\"second_name\":\"Pro 11\",\"hide_pos\":\"0\"}}', '2022-12-26 10:15:12'),
(53, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"6\",\"code\":\"TPR-14\",\"name\":\"Test Product 14\",\"unit\":\"2\",\"cost\":\"90.0000\",\"price\":\"100.0000\",\"alert_quantity\":\"20.0000\",\"image\":\"no_image.png\",\"category_id\":\"1\",\"subcategory_id\":null,\"cf1\":\"CF1\",\"cf2\":\"CF2\",\"cf3\":\"CF3\",\"cf4\":\"CF4\",\"cf5\":\"CF5\",\"cf6\":\"CF6\",\"quantity\":\"0.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":null,\"product_details\":\"\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":null,\"supplier1price\":\"90.0000\",\"supplier2\":null,\"supplier2price\":\"0.0000\",\"supplier3\":null,\"supplier3price\":\"0.0000\",\"supplier4\":null,\"supplier4price\":\"0.0000\",\"supplier5\":null,\"supplier5price\":\"0.0000\",\"promotion\":\"0\",\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"NG01\",\"supplier2_part_no\":\"\",\"supplier3_part_no\":\"\",\"supplier4_part_no\":\"\",\"supplier5_part_no\":\"\",\"sale_unit\":\"2\",\"purchase_unit\":\"2\",\"brand\":null,\"slug\":\"test-product-14\",\"featured\":null,\"weight\":\"0.2000\",\"hsn_code\":\"0\",\"views\":\"0\",\"hide\":\"0\",\"second_name\":\"Pro 11\",\"hide_pos\":\"0\"}}', '2022-12-26 10:15:12'),
(54, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"90\",\"date\":\"2022-12-21 14:32:38\",\"reference_no\":\"SALE2022\\/12\\/0073\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"3\",\"note\":\"\",\"staff_note\":null,\"total\":\"230.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"230.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"21\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"84\",\"reserve_id\":null,\"hash\":\"d3e523db654f2def2ce576c25c850b678340524e4cf58918d1a6079e13c34c46\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:33'),
(55, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"89\",\"date\":\"2022-12-21 14:27:37\",\"reference_no\":\"SALE2022\\/12\\/0072\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"3\",\"note\":\"\",\"staff_note\":null,\"total\":\"200.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"200.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"21\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"83\",\"reserve_id\":null,\"hash\":\"66e50490d4c7cd5f4d977ab6387d7074b8c1db1d8d2394bd01eb121c922c332c\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:33'),
(56, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"88\",\"date\":\"2022-12-20 15:25:13\",\"reference_no\":\"SALE2022\\/12\\/0071\",\"customer_id\":\"11\",\"customer\":\"dg\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"3\",\"note\":null,\"staff_note\":null,\"total\":\"460.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"460.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"460.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"81\",\"reserve_id\":null,\"hash\":\"7903b36e712263e6ff4b2c7f40fdd116a7c53a5c2dc0c04b794e29a4a00359ce\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"95\",\"sale_id\":\"88\",\"product_id\":\"26\",\"product_code\":\"3w3w3\",\"product_name\":\"amr\",\"product_type\":\"standard\",\"option_id\":\"22\",\"net_unit_price\":\"460.0000\",\"unit_price\":\"460.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"3\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"460.0000\",\"serial_no\":null,\"real_unit_price\":\"460.0000\",\"sale_item_id\":null,\"product_unit_id\":\"4\",\"product_unit_code\":\"Car01\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:33'),
(57, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"87\",\"date\":\"2022-12-19 16:25:56\",\"reference_no\":\"SALE2022\\/12\\/0070\",\"customer_id\":\"36\",\"customer\":\"tryt\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"3\",\"note\":null,\"staff_note\":null,\"total\":\"200.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"200.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"200.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"80\",\"reserve_id\":null,\"hash\":\"6a25dc5443f5c1f9d66d2d4cbafcd30bf9f52fd55f60f57ede985408da1d57c9\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"94\",\"sale_id\":\"87\",\"product_id\":\"28\",\"product_code\":\"5765765\",\"product_name\":\"sulfad2\",\"product_type\":\"standard\",\"option_id\":\"19\",\"net_unit_price\":\"200.0000\",\"unit_price\":\"200.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"3\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"200.0000\",\"serial_no\":null,\"real_unit_price\":\"200.0000\",\"sale_item_id\":null,\"product_unit_id\":\"4\",\"product_unit_code\":\"Car01\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:33');
INSERT INTO `sma_logs` (`id`, `detail`, `model`, `date`) VALUES
(58, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"86\",\"date\":\"2022-12-19 12:01:25\",\"reference_no\":\"SALE2022\\/12\\/0069\",\"customer_id\":\"35\",\"customer\":\"Ultra3\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"3\",\"note\":null,\"staff_note\":null,\"total\":\"830.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"830.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"2\",\"pos\":\"0\",\"paid\":\"830.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"79\",\"reserve_id\":null,\"hash\":\"9319d2e9cd1595f5bcb1ee2c1a29c5f9e6eb07d569a0bd364bdc1fc4cf77d582\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"92\",\"sale_id\":\"86\",\"product_id\":\"28\",\"product_code\":\"5765765\",\"product_name\":\"sulfad2\",\"product_type\":\"standard\",\"option_id\":\"19\",\"net_unit_price\":\"430.0000\",\"unit_price\":\"430.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"3\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"430.0000\",\"serial_no\":null,\"real_unit_price\":\"430.0000\",\"sale_item_id\":null,\"product_unit_id\":\"2\",\"product_unit_code\":\"pc\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null},{\"id\":\"93\",\"sale_id\":\"86\",\"product_id\":\"29\",\"product_code\":\"45545\",\"product_name\":\"sulfad100\",\"product_type\":\"standard\",\"option_id\":\"26\",\"net_unit_price\":\"400.0000\",\"unit_price\":\"400.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"3\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"400.0000\",\"serial_no\":null,\"real_unit_price\":\"400.0000\",\"sale_item_id\":null,\"product_unit_id\":\"2\",\"product_unit_code\":\"pc\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:33'),
(59, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"85\",\"date\":\"2022-12-17 19:45:00\",\"reference_no\":\"gf776\",\"customer_id\":\"32\",\"customer\":\"alnahdi\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"3\",\"note\":\"\",\"staff_note\":\"\",\"total\":\"16100.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":\"50%\",\"total_discount\":\"8050.0000\",\"order_discount\":\"8050.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"8050.0000\",\"sale_status\":\"completed\",\"payment_status\":\"due\",\"payment_term\":\"0\",\"due_date\":null,\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"70\",\"pos\":\"0\",\"paid\":\"6050.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":\"0\",\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"0\",\"address_id\":null,\"reserve_id\":null,\"hash\":\"967fa7f8208737ecaa1ec0babfc359dbcce7c6676d37254d759fec6764a36895\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":null},\"items\":[{\"id\":\"91\",\"sale_id\":\"85\",\"product_id\":\"28\",\"product_code\":\"5765765\",\"product_name\":\"sulfad2\",\"product_type\":\"standard\",\"option_id\":\"21\",\"net_unit_price\":\"230.0000\",\"unit_price\":\"230.0000\",\"quantity\":\"70.0000\",\"warehouse_id\":\"3\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"subtotal\":\"16100.0000\",\"serial_no\":\"\",\"real_unit_price\":\"120.0000\",\"sale_item_id\":null,\"product_unit_id\":\"2\",\"product_unit_code\":\"pc\",\"unit_quantity\":\"70.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:33'),
(60, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"84\",\"date\":\"2022-12-16 18:05:27\",\"reference_no\":\"SALE2022\\/12\\/0068\",\"customer_id\":\"30\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":null,\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"45.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"78\",\"reserve_id\":null,\"hash\":\"2332ef06ee011f8fe17f64b67e5f5c3afb06f193ddde3e7e8f9f839fe4f62b01\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"90\",\"sale_id\":\"84\",\"product_id\":\"22\",\"product_code\":\"8057506630110\",\"product_name\":\"Black Seed\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"45.0000\",\"unit_price\":\"45.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"45.0000\",\"serial_no\":null,\"real_unit_price\":\"45.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:33'),
(61, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"83\",\"date\":\"2022-12-16 17:56:58\",\"reference_no\":\"SALE2022\\/12\\/0067\",\"customer_id\":\"30\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"77\",\"reserve_id\":null,\"hash\":\"d249c1696827e74ac499930b29d3a225cfe5c74c0e9b8e70ba29bc5b7450c93e\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:33'),
(62, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"82\",\"date\":\"2022-12-16 17:56:06\",\"reference_no\":\"SALE2022\\/12\\/0066\",\"customer_id\":\"30\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"76\",\"reserve_id\":null,\"hash\":\"3320c0608f0ad5e4fd7e38aeb037b7172d05668813f01b3e71033aebb2bc64a1\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:33'),
(63, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"81\",\"date\":\"2022-12-16 17:54:04\",\"reference_no\":\"SALE2022\\/12\\/0065\",\"customer_id\":\"30\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"75\",\"reserve_id\":null,\"hash\":\"6abc8bdca5c3259128c594a0d3f0c149b350a285e3d763e1baa7d5f0827977cc\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:33'),
(64, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"80\",\"date\":\"2022-12-16 17:52:37\",\"reference_no\":\"SALE2022\\/12\\/0064\",\"customer_id\":\"30\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"74\",\"reserve_id\":null,\"hash\":\"5507f942019549ff728656e05000a35f0fc4e365a21bc797e4df5ea67ca34c0a\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:33'),
(65, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"79\",\"date\":\"2022-12-16 17:50:07\",\"reference_no\":\"SALE2022\\/12\\/0063\",\"customer_id\":\"30\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"73\",\"reserve_id\":null,\"hash\":\"a0b532dce806d9f7ac1103fb2032ad9cb804625a6d5f2c26b7a97425869a441e\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:33'),
(66, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"78\",\"date\":\"2022-12-16 17:48:59\",\"reference_no\":\"SALE2022\\/12\\/0062\",\"customer_id\":\"30\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"72\",\"reserve_id\":null,\"hash\":\"48471b1aab1be433e26700540ef70fe34ddd4c7a549028ff2e9b7402aed27ade\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:33'),
(67, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"77\",\"date\":\"2022-12-16 17:45:01\",\"reference_no\":\"SALE2022\\/12\\/0061\",\"customer_id\":\"30\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"71\",\"reserve_id\":null,\"hash\":\"06003b94c5777a13c2af0743d023a6673a75cbff8c5f0cb6b077e59903ec5f6e\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:33'),
(68, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"76\",\"date\":\"2022-12-16 17:43:53\",\"reference_no\":\"SALE2022\\/12\\/0060\",\"customer_id\":\"30\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"70\",\"reserve_id\":null,\"hash\":\"346f8949acfb726def2d6a8809261443861eab9ea048e8976e14d556a20d6d7c\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:33'),
(69, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"75\",\"date\":\"2022-12-16 17:41:11\",\"reference_no\":\"SALE2022\\/12\\/0059\",\"customer_id\":\"30\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"69\",\"reserve_id\":null,\"hash\":\"5c4c10d1407b07dedd7a0f8ad1dac653578c946aec9f7b896bbd9fb4254b7b60\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:33'),
(70, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"74\",\"date\":\"2022-12-16 17:36:45\",\"reference_no\":\"SALE2022\\/12\\/0058\",\"customer_id\":\"30\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"68\",\"reserve_id\":null,\"hash\":\"247f0528b9fb3e353ce163c9b6f7b8b0eca2766ff3816c4e399e6ee1218575d3\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(71, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"71\",\"date\":\"2022-12-16 17:23:20\",\"reference_no\":\"SALE2022\\/12\\/0057\",\"customer_id\":\"30\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"65\",\"reserve_id\":null,\"hash\":\"c488d0ca1f996c680e4bd6857ba93874770671c2cc3bdeb17be1dbf9fcb672f2\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(72, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"70\",\"date\":\"2022-12-16 17:22:25\",\"reference_no\":\"SALE2022\\/12\\/0056\",\"customer_id\":\"30\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"64\",\"reserve_id\":null,\"hash\":\"c3c90c33bdd0ae82e7c3063fc5d2984efd0ba779490ecc74c1cf876bd2058083\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(73, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"69\",\"date\":\"2022-12-16 17:21:50\",\"reference_no\":\"SALE2022\\/12\\/0055\",\"customer_id\":\"30\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"63\",\"reserve_id\":null,\"hash\":\"c51247b0c6d07afded7abc6a198f9745076a2ecd1434ae2ebf59bd169764f813\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(74, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"68\",\"date\":\"2022-12-16 17:21:08\",\"reference_no\":\"SALE2022\\/12\\/0054\",\"customer_id\":\"30\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"62\",\"reserve_id\":null,\"hash\":\"9acc68bf70f01129f3799517b2bd460b9a22d1d8139d0c8a7f7075dee5d3f0a6\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(75, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"67\",\"date\":\"2022-12-16 17:20:12\",\"reference_no\":\"SALE2022\\/12\\/0053\",\"customer_id\":\"30\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"61\",\"reserve_id\":null,\"hash\":\"da14a30851e94dd2853770715f9adfa774ea30897a7e83697b21c3658c77053d\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(76, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"66\",\"date\":\"2022-12-16 17:17:55\",\"reference_no\":\"SALE2022\\/12\\/0052\",\"customer_id\":\"30\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"60\",\"reserve_id\":null,\"hash\":\"46e6b92cfa111bd180ae802f4041f3b9d1c86efc9c9e8298027ad9a68ba7df63\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(77, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"65\",\"date\":\"2022-12-16 17:12:31\",\"reference_no\":\"SALE2022\\/12\\/0051\",\"customer_id\":\"30\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":null,\"staff_note\":null,\"total\":\"95.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"95.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"2\",\"pos\":\"0\",\"paid\":\"95.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"59\",\"reserve_id\":null,\"hash\":\"d2600555cbe90a76408e290e084a7eec50666424ebd6d194781b1e1b08be47b9\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"72\",\"sale_id\":\"65\",\"product_id\":\"2\",\"product_code\":\"554212121\",\"product_name\":\"Anti \\u2013 Acne Serum \\u2013 Brightens Skin, Fades Acne, Lighten Acne Scars & Control Excess Oil Production\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"20.0000\",\"unit_price\":\"20.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"20.0000\",\"serial_no\":null,\"real_unit_price\":\"20.0000\",\"sale_item_id\":null,\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null},{\"id\":\"73\",\"sale_id\":\"65\",\"product_id\":\"17\",\"product_code\":\"8057506630301\",\"product_name\":\"Maximum Power\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"75.0000\",\"unit_price\":\"75.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"75.0000\",\"serial_no\":null,\"real_unit_price\":\"75.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:34'),
(78, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"64\",\"date\":\"2022-12-16 17:11:12\",\"reference_no\":\"SALE2022\\/12\\/0050\",\"customer_id\":\"30\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"95.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"95.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"2\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"58\",\"reserve_id\":null,\"hash\":\"8be6295f796985744c102c30e8b904f3f1e75c1491d28644135971c812d9a623\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(79, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"63\",\"date\":\"2022-12-16 17:06:42\",\"reference_no\":\"SALE2022\\/12\\/0049\",\"customer_id\":\"30\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"196.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"196.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"2\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"57\",\"reserve_id\":null,\"hash\":\"2cda1d47ca4e4f0070e0cdda6c04abdcdec4718d9e036bb75322944138ab0fab\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(80, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"62\",\"date\":\"2022-12-16 17:01:23\",\"reference_no\":\"SALE2022\\/12\\/0048\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":null,\"staff_note\":null,\"total\":\"133.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"133.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"2\",\"pos\":\"0\",\"paid\":\"133.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"56\",\"reserve_id\":null,\"hash\":\"7f18f5197df41bcac6660efe157bbe91ae6d0cbf961bec9658137facf53cafd9\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"66\",\"sale_id\":\"62\",\"product_id\":\"18\",\"product_code\":\"8057506630042\",\"product_name\":\"Men\\u2019s Fertility\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"113.0000\",\"unit_price\":\"113.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"113.0000\",\"serial_no\":null,\"real_unit_price\":\"113.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null},{\"id\":\"67\",\"sale_id\":\"62\",\"product_id\":\"2\",\"product_code\":\"554212121\",\"product_name\":\"Anti \\u2013 Acne Serum \\u2013 Brightens Skin, Fades Acne, Lighten Acne Scars & Control Excess Oil Production\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"20.0000\",\"unit_price\":\"20.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"20.0000\",\"serial_no\":null,\"real_unit_price\":\"20.0000\",\"sale_item_id\":null,\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:34'),
(81, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"59\",\"date\":\"2022-12-16 13:21:42\",\"reference_no\":\"SALE2022\\/12\\/0047\",\"customer_id\":\"29\",\"customer\":\"test\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":null,\"staff_note\":null,\"total\":\"138.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"138.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"2\",\"pos\":\"0\",\"paid\":\"138.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"53\",\"reserve_id\":null,\"hash\":\"ca41492ec4e335b60c76cf52abaaa175bb0de83a8d0d680b5a3133d7834e7588\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"64\",\"sale_id\":\"59\",\"product_id\":\"15\",\"product_code\":\"8057506630097\",\"product_name\":\"CoEnzyme\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"44.0000\",\"unit_price\":\"44.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"44.0000\",\"serial_no\":null,\"real_unit_price\":\"44.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null},{\"id\":\"65\",\"sale_id\":\"59\",\"product_id\":\"7\",\"product_code\":\"8057506630271\",\"product_name\":\"Anti Aging\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"94.0000\",\"unit_price\":\"94.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"94.0000\",\"serial_no\":null,\"real_unit_price\":\"94.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:34'),
(82, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"50\",\"date\":\"2022-12-15 15:50:22\",\"reference_no\":\"SALE2022\\/12\\/0046\",\"customer_id\":\"11\",\"customer\":\"dg\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":null,\"staff_note\":null,\"total\":\"138.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"138.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"2\",\"pos\":\"0\",\"paid\":\"138.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"44\",\"reserve_id\":null,\"hash\":\"6f6fe02cf69362338f58eb4247ce3da6534265093454e8c83f3848ee53e0a0ea\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"62\",\"sale_id\":\"50\",\"product_id\":\"14\",\"product_code\":\"8057506630196\",\"product_name\":\"Immunity Defense\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"94.0000\",\"unit_price\":\"94.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"94.0000\",\"serial_no\":null,\"real_unit_price\":\"94.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null},{\"id\":\"63\",\"sale_id\":\"50\",\"product_id\":\"15\",\"product_code\":\"8057506630097\",\"product_name\":\"CoEnzyme\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"44.0000\",\"unit_price\":\"44.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"44.0000\",\"serial_no\":null,\"real_unit_price\":\"44.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:34'),
(83, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"49\",\"date\":\"2022-12-15 14:59:48\",\"reference_no\":\"SALE2022\\/12\\/0045\",\"customer_id\":\"25\",\"customer\":\"test\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"113.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"113.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"43\",\"reserve_id\":null,\"hash\":\"becbc258442335037f9e24d6c9ffc97313061efc217cce529c7eba3e31f96667\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(84, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"48\",\"date\":\"2022-12-15 14:57:32\",\"reference_no\":\"SALE2022\\/12\\/0044\",\"customer_id\":\"25\",\"customer\":\"test\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"42\",\"reserve_id\":null,\"hash\":\"6003565951682a559cfd0adb7e537fd69bbba5f44edf52960bbf14248df894d0\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(85, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"47\",\"date\":\"2022-12-15 13:43:57\",\"reference_no\":\"SALE2022\\/12\\/0043\",\"customer_id\":\"25\",\"customer\":\"test\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"113.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"113.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"41\",\"reserve_id\":null,\"hash\":\"fa6e38d640b9dec42dd2d86b037f60400fd2d91d7cb8d40f182bc2542f30ecf1\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(86, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"46\",\"date\":\"2022-12-15 13:41:51\",\"reference_no\":\"SALE2022\\/12\\/0042\",\"customer_id\":\"25\",\"customer\":\"test\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":null,\"staff_note\":null,\"total\":\"113.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"113.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"113.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"40\",\"reserve_id\":null,\"hash\":\"2bf6e5ad528a4411f27deac5a2e399907543096d9545c81ab310cdd19ed81f20\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"58\",\"sale_id\":\"46\",\"product_id\":\"18\",\"product_code\":\"8057506630042\",\"product_name\":\"Men\\u2019s Fertility\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"113.0000\",\"unit_price\":\"113.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"113.0000\",\"serial_no\":null,\"real_unit_price\":\"113.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:34');
INSERT INTO `sma_logs` (`id`, `detail`, `model`, `date`) VALUES
(87, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"45\",\"date\":\"2022-12-15 13:38:57\",\"reference_no\":\"SALE2022\\/12\\/0041\",\"customer_id\":\"24\",\"customer\":\"test\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":null,\"staff_note\":null,\"total\":\"83.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"83.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"83.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"39\",\"reserve_id\":null,\"hash\":\"2dcaf5f338a55ee2e7683e4d60919ec2b5126c2ea9383b89407a5ce1fafc19d5\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"57\",\"sale_id\":\"45\",\"product_id\":\"8\",\"product_code\":\"8057506630011\",\"product_name\":\"Grow Hair Nails\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"83.0000\",\"unit_price\":\"83.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"83.0000\",\"serial_no\":null,\"real_unit_price\":\"83.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:34'),
(88, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"44\",\"date\":\"2022-12-15 13:35:21\",\"reference_no\":\"SALE2022\\/12\\/0040\",\"customer_id\":\"23\",\"customer\":\"test\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"40.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"40.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"38\",\"reserve_id\":null,\"hash\":\"ff37082b8389f6c5da3be880e7fb6b2864456cba8184d1207f79f87026670edf\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(89, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"43\",\"date\":\"2022-12-15 10:17:25\",\"reference_no\":\"SALE2022\\/12\\/0039\",\"customer_id\":\"21\",\"customer\":\"test\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":null,\"staff_note\":null,\"total\":\"40.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"40.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"10\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"40.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"37\",\"reserve_id\":null,\"hash\":\"4a1a32b9ac3fb8c7a7228e76d64d1d2179c60a2b5331f3dfe41d36fd23ca352c\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"55\",\"sale_id\":\"43\",\"product_id\":\"1\",\"product_code\":\"1234567879\",\"product_name\":\"Aloe Vera Juice [Pineapple Flavour] \\u2013 Natural Hydrator, Better Liver Function & Nutritious Booster\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"40.0000\",\"unit_price\":\"40.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"40.0000\",\"serial_no\":null,\"real_unit_price\":\"40.0000\",\"sale_item_id\":null,\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:34'),
(90, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"42\",\"date\":\"2022-12-15 10:13:54\",\"reference_no\":\"SALE2022\\/12\\/0038\",\"customer_id\":\"21\",\"customer\":\"test\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":null,\"staff_note\":null,\"total\":\"20.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"20.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"20.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"36\",\"reserve_id\":null,\"hash\":\"1ae3ae82ed4ee069df97e1ac95929e46f5132ad0b30f58944bbe4d13c5ef8926\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"54\",\"sale_id\":\"42\",\"product_id\":\"2\",\"product_code\":\"554212121\",\"product_name\":\"Anti \\u2013 Acne Serum \\u2013 Brightens Skin, Fades Acne, Lighten Acne Scars & Control Excess Oil Production\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"20.0000\",\"unit_price\":\"20.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"20.0000\",\"serial_no\":null,\"real_unit_price\":\"20.0000\",\"sale_item_id\":null,\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:34'),
(91, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"41\",\"date\":\"2022-12-13 11:04:25\",\"reference_no\":\"SALE2022\\/12\\/0037\",\"customer_id\":\"21\",\"customer\":\"test\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":null,\"staff_note\":null,\"total\":\"40.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"40.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"10\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"40.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"35\",\"reserve_id\":null,\"hash\":\"43354298abbad034208edf2bf7e2d8421621a9fec119d3df6ff7fd5140fd72f7\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"53\",\"sale_id\":\"41\",\"product_id\":\"1\",\"product_code\":\"1234567879\",\"product_name\":\"Aloe Vera Juice [Pineapple Flavour] \\u2013 Natural Hydrator, Better Liver Function & Nutritious Booster\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"40.0000\",\"unit_price\":\"40.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"40.0000\",\"serial_no\":null,\"real_unit_price\":\"40.0000\",\"sale_item_id\":null,\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:34'),
(92, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"40\",\"date\":\"2022-12-13 11:03:32\",\"reference_no\":\"SALE2022\\/12\\/0036\",\"customer_id\":\"21\",\"customer\":\"test\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"75.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"75.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"10\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"34\",\"reserve_id\":null,\"hash\":\"a5a9d9563d90c5e45e181cddff857b0241ff9fbeceaffdbe979ad8c91da0c232\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"cod\"},\"items\":null}', '2022-12-26 10:15:34'),
(93, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"39\",\"date\":\"2022-12-13 10:57:57\",\"reference_no\":\"SALE2022\\/12\\/0035\",\"customer_id\":\"20\",\"customer\":\"increate\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"60.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"60.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"9\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"33\",\"reserve_id\":null,\"hash\":\"8948a81de332c42df778c8bac9db8273ede7375f0a3632c548d3b6edc606fe6e\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(94, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"38\",\"date\":\"2022-12-13 09:03:43\",\"reference_no\":\"SALE2022\\/12\\/0034\",\"customer_id\":\"14\",\"customer\":\"test\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"153.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"153.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"3\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"3\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"32\",\"reserve_id\":null,\"hash\":\"c54b310e3541ba5d6bbac9de013b426103e34aadf1b2b8f3ee5c79485af040a0\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(95, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"37\",\"date\":\"2022-12-13 08:41:56\",\"reference_no\":\"SALE2022\\/12\\/0033\",\"customer_id\":\"14\",\"customer\":\"test\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"120.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"120.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"3\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"3\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"31\",\"reserve_id\":null,\"hash\":\"9d8f44036451cec4e2a99b5c2e400e7cc0926c0cf0f7d1aa10ea9a80359bfc4f\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"cod\"},\"items\":null}', '2022-12-26 10:15:34'),
(96, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"35\",\"date\":\"2022-12-12 15:13:46\",\"reference_no\":\"SALE2022\\/12\\/0031\",\"customer_id\":\"11\",\"customer\":\"dg\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":null,\"staff_note\":null,\"total\":\"263.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"263.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"3\",\"pos\":\"0\",\"paid\":\"263.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"29\",\"reserve_id\":null,\"hash\":\"36199a2109410117954edc6dce525f83338aee368d7760c015ea40cff232b2bc\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"44\",\"sale_id\":\"35\",\"product_id\":\"14\",\"product_code\":\"8057506630196\",\"product_name\":\"Immunity Defense\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"94.0000\",\"unit_price\":\"94.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"94.0000\",\"serial_no\":null,\"real_unit_price\":\"94.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null},{\"id\":\"45\",\"sale_id\":\"35\",\"product_id\":\"14\",\"product_code\":\"8057506630196\",\"product_name\":\"Immunity Defense\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"94.0000\",\"unit_price\":\"94.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"94.0000\",\"serial_no\":null,\"real_unit_price\":\"94.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null},{\"id\":\"46\",\"sale_id\":\"35\",\"product_id\":\"17\",\"product_code\":\"8057506630301\",\"product_name\":\"Maximum Power\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"75.0000\",\"unit_price\":\"75.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"75.0000\",\"serial_no\":null,\"real_unit_price\":\"75.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:34'),
(97, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"34\",\"date\":\"2022-12-12 12:45:36\",\"reference_no\":\"SALE2022\\/12\\/0030\",\"customer_id\":\"13\",\"customer\":\"increate\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":null,\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"3\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"45.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"28\",\"reserve_id\":null,\"hash\":\"90c921f99fe90d468f12712da97d2033552a3740e903617f4cee518a57e03368\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"43\",\"sale_id\":\"34\",\"product_id\":\"22\",\"product_code\":\"8057506630110\",\"product_name\":\"Black Seed\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"45.0000\",\"unit_price\":\"45.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"45.0000\",\"serial_no\":null,\"real_unit_price\":\"45.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:34'),
(98, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"33\",\"date\":\"2022-12-12 12:41:14\",\"reference_no\":\"SALE2022\\/12\\/0029\",\"customer_id\":\"7\",\"customer\":\"mubasher\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":null,\"staff_note\":null,\"total\":\"1007.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"1007.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"3\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"10\",\"pos\":\"0\",\"paid\":\"1007.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"27\",\"reserve_id\":null,\"hash\":\"23b924034f55b344a377ad713d8fdd0cb126d2ba8b1863cea17b572973b93020\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"36\",\"sale_id\":\"33\",\"product_id\":\"8\",\"product_code\":\"8057506630011\",\"product_name\":\"Grow Hair Nails\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"83.0000\",\"unit_price\":\"83.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"83.0000\",\"serial_no\":null,\"real_unit_price\":\"83.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null},{\"id\":\"37\",\"sale_id\":\"33\",\"product_id\":\"9\",\"product_code\":\"8057506630066\",\"product_name\":\"Flex\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"113.0000\",\"unit_price\":\"113.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"113.0000\",\"serial_no\":null,\"real_unit_price\":\"113.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null},{\"id\":\"38\",\"sale_id\":\"33\",\"product_id\":\"9\",\"product_code\":\"8057506630066\",\"product_name\":\"Flex\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"113.0000\",\"unit_price\":\"113.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"113.0000\",\"serial_no\":null,\"real_unit_price\":\"113.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null},{\"id\":\"39\",\"sale_id\":\"33\",\"product_id\":\"2\",\"product_code\":\"554212121\",\"product_name\":\"Anti \\u2013 Acne Serum \\u2013 Brightens Skin, Fades Acne, Lighten Acne Scars & Control Excess Oil Production\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"20.0000\",\"unit_price\":\"20.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"20.0000\",\"serial_no\":null,\"real_unit_price\":\"20.0000\",\"sale_item_id\":null,\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null},{\"id\":\"40\",\"sale_id\":\"33\",\"product_id\":\"9\",\"product_code\":\"8057506630066\",\"product_name\":\"Flex\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"113.0000\",\"unit_price\":\"113.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"113.0000\",\"serial_no\":null,\"real_unit_price\":\"113.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null},{\"id\":\"41\",\"sale_id\":\"33\",\"product_id\":\"9\",\"product_code\":\"8057506630066\",\"product_name\":\"Flex\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"113.0000\",\"unit_price\":\"113.0000\",\"quantity\":\"4.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"452.0000\",\"serial_no\":null,\"real_unit_price\":\"113.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"4.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null},{\"id\":\"42\",\"sale_id\":\"33\",\"product_id\":\"18\",\"product_code\":\"8057506630042\",\"product_name\":\"Men\\u2019s Fertility\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"113.0000\",\"unit_price\":\"113.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"113.0000\",\"serial_no\":null,\"real_unit_price\":\"113.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:34'),
(99, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"32\",\"date\":\"2022-12-11 14:46:51\",\"reference_no\":\"SALE2022\\/12\\/0028\",\"customer_id\":\"11\",\"customer\":\"dg\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":null,\"staff_note\":null,\"total\":\"88.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"88.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"2\",\"pos\":\"0\",\"paid\":\"88.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"26\",\"reserve_id\":null,\"hash\":\"250d1293e6d9c4d5a1c6def18eb22d597eaecb2d0e90263d6e78e5c53fb71f2d\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"35\",\"sale_id\":\"32\",\"product_id\":\"15\",\"product_code\":\"8057506630097\",\"product_name\":\"CoEnzyme\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"44.0000\",\"unit_price\":\"44.0000\",\"quantity\":\"2.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"88.0000\",\"serial_no\":null,\"real_unit_price\":\"44.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"2.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:34'),
(100, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"31\",\"date\":\"2022-12-10 15:12:40\",\"reference_no\":\"SALE2022\\/12\\/0027\",\"customer_id\":\"10\",\"customer\":\"Test\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":null,\"staff_note\":null,\"total\":\"225.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"225.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"3\",\"pos\":\"0\",\"paid\":\"225.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"25\",\"reserve_id\":null,\"hash\":\"0cbf67e47e28a47c3d5e07b75baee46ea04328dec04e7bb226efbc56dab1c65a\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"34\",\"sale_id\":\"31\",\"product_id\":\"17\",\"product_code\":\"8057506630301\",\"product_name\":\"Maximum Power\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"75.0000\",\"unit_price\":\"75.0000\",\"quantity\":\"3.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"225.0000\",\"serial_no\":null,\"real_unit_price\":\"75.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"3.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:34'),
(101, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"30\",\"date\":\"2022-12-09 10:10:13\",\"reference_no\":\"SALE2022\\/12\\/0026\",\"customer_id\":\"9\",\"customer\":\"test\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":null,\"staff_note\":null,\"total\":\"20.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"20.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"20.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"24\",\"reserve_id\":null,\"hash\":\"64bab32e791caf1e3bdd56263371eb9033ea4ebb0a95cd27a89fded30cddc062\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"33\",\"sale_id\":\"30\",\"product_id\":\"2\",\"product_code\":\"554212121\",\"product_name\":\"Anti \\u2013 Acne Serum \\u2013 Brightens Skin, Fades Acne, Lighten Acne Scars & Control Excess Oil Production\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"20.0000\",\"unit_price\":\"20.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"20.0000\",\"serial_no\":null,\"real_unit_price\":\"20.0000\",\"sale_item_id\":null,\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:34'),
(102, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"29\",\"date\":\"2022-12-09 09:29:14\",\"reference_no\":\"SALE2022\\/12\\/0025\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":null,\"staff_note\":null,\"total\":\"113.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"113.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"11300.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"23\",\"reserve_id\":null,\"hash\":\"fcc4f4493da5b93c1558dd1294382224963af77f4e3dd8b636b92e36a1921fb6\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":[{\"id\":\"32\",\"sale_id\":\"29\",\"product_id\":\"18\",\"product_code\":\"8057506630042\",\"product_name\":\"Men\\u2019s Fertility\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"113.0000\",\"unit_price\":\"113.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":\"0.0000\",\"subtotal\":\"113.0000\",\"serial_no\":null,\"real_unit_price\":\"113.0000\",\"sale_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:34'),
(103, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"28\",\"date\":\"2022-12-09 00:31:03\",\"reference_no\":\"SALE2022\\/12\\/0024\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"105.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"105.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"22\",\"reserve_id\":null,\"hash\":\"0dbb24afed4260f71baf46a9a825214ec3e01cd88cee3183a707081f9cf5db33\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(104, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"27\",\"date\":\"2022-12-09 00:12:09\",\"reference_no\":\"SALE2022\\/12\\/0023\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"44.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"44.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"21\",\"reserve_id\":null,\"hash\":\"66ec02094c8276608449e468494f09e974b9d56f8e588745f9dc36f591dd7ea9\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(105, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"26\",\"date\":\"2022-12-09 00:11:03\",\"reference_no\":\"SALE2022\\/12\\/0022\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"68.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"68.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"20\",\"reserve_id\":null,\"hash\":\"4a39e91122e00e6442f90dc8c9cfef46a5abdd30cea61e6bfe783831c228fb1a\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(106, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"25\",\"date\":\"2022-12-09 00:06:59\",\"reference_no\":\"SALE2022\\/12\\/0021\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"40.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"40.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"19\",\"reserve_id\":null,\"hash\":\"8abdbc0500cc05cf395eac261bcde5ce0246c145731f3b80962f4564081d8016\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(107, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"24\",\"date\":\"2022-12-09 00:01:03\",\"reference_no\":\"SALE2022\\/12\\/0020\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"18\",\"reserve_id\":null,\"hash\":\"85bd712b006c3ba677745c8f4330adb958ecdbc4dfb076345e28ec029ba50618\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(108, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"23\",\"date\":\"2022-12-08 23:52:27\",\"reference_no\":\"SALE2022\\/12\\/0019\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"40.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"40.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"17\",\"reserve_id\":null,\"hash\":\"0f3180acb52950d9e05c0f9029f6647625faf241a3d44b14f72364382f382bf4\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(109, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"22\",\"date\":\"2022-12-08 23:48:19\",\"reference_no\":\"SALE2022\\/12\\/0018\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"40.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"40.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"16\",\"reserve_id\":null,\"hash\":\"89ab7ba600080c4726abcae1ea3fcbbc73bca3692bad57bf11571e9db3a31dd3\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(110, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"21\",\"date\":\"2022-12-08 23:43:54\",\"reference_no\":\"SALE2022\\/12\\/0017\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"40.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"40.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"15\",\"reserve_id\":null,\"hash\":\"daebe72789703bc78231d409febe06ae27a20179201a3ad3ea4075c1f2712204\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(111, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"20\",\"date\":\"2022-12-08 23:42:30\",\"reference_no\":\"SALE2022\\/12\\/0016\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"14\",\"reserve_id\":null,\"hash\":\"2a63101c1d487852369a587d4216544715013de59faa82dc37419e39cf73e32b\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34');
INSERT INTO `sma_logs` (`id`, `detail`, `model`, `date`) VALUES
(112, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"19\",\"date\":\"2022-12-08 23:40:09\",\"reference_no\":\"SALE2022\\/12\\/0015\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"13\",\"reserve_id\":null,\"hash\":\"8f69b53ae3f4574ed444a6c912dbe17f505348c638c00313b0678622aec77d6a\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(113, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"18\",\"date\":\"2022-12-08 23:35:55\",\"reference_no\":\"SALE2022\\/12\\/0014\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"60.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"60.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"12\",\"reserve_id\":null,\"hash\":\"b1b29946476a6d14a8aadefe64787ac66ab3ce12b6aee8bf957d390cb15de28f\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(114, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"17\",\"date\":\"2022-12-08 23:25:18\",\"reference_no\":\"SALE2022\\/12\\/0013\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"68.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"68.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"11\",\"reserve_id\":null,\"hash\":\"ad99d4e4947fecc11a4aca36b162f51e6014e10e903198470bf0a5b9c369a1ac\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(115, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"16\",\"date\":\"2022-12-08 15:37:20\",\"reference_no\":\"SALE2022\\/12\\/0012\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"105.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"105.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"10\",\"reserve_id\":null,\"hash\":\"9297b81f0613609790ca29ac085d351a0bde8406b0b2b7c3caeeec9f5603f688\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(116, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"15\",\"date\":\"2022-12-08 15:33:39\",\"reference_no\":\"SALE2022\\/12\\/0011\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"105.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"105.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"9\",\"reserve_id\":null,\"hash\":\"3b12404cb28dd1734ed90eaea8c007d3db6dfebae4d08379b2d5854f4aa184dc\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(117, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"14\",\"date\":\"2022-12-08 14:31:06\",\"reference_no\":\"SALE2022\\/12\\/0010\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"113.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"113.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"8\",\"reserve_id\":null,\"hash\":\"f9960ccdd4ce97a8300422d651d467befb9d4ba6d99f48edf58e42af5c7756dc\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(118, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"13\",\"date\":\"2022-12-08 13:29:31\",\"reference_no\":\"SALE2022\\/12\\/0009\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"44.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"44.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"7\",\"reserve_id\":null,\"hash\":\"2842274edefe309f22423a7b2c365f693d15da401a80e9bbc9ede8343c601e35\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(119, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"12\",\"date\":\"2022-12-08 13:28:26\",\"reference_no\":\"SALE2022\\/12\\/0008\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"45.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"45.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"6\",\"reserve_id\":null,\"hash\":\"28ef44445df720af9fc8d823165d36de85957170c2e95ada292aa9d6f04e0c54\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(120, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"11\",\"date\":\"2022-12-08 13:18:46\",\"reference_no\":\"SALE2022\\/12\\/0007\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"40.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"40.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"5\",\"reserve_id\":null,\"hash\":\"aad166ad50e32a439c8ace669b977004f8faef6826d55f9c53a2613b1192c517\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(121, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"10\",\"date\":\"2022-12-08 12:39:53\",\"reference_no\":\"SALE2022\\/12\\/0006\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"40.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"40.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"4\",\"reserve_id\":null,\"hash\":\"1ea4cb246fa7ce9812642f71536461b086631c5f1f6b892881ff3523417fa924\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(122, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"9\",\"date\":\"2022-12-08 12:38:47\",\"reference_no\":\"SALE2022\\/12\\/0005\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"40.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"40.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"3\",\"reserve_id\":null,\"hash\":\"69059dce4a05daaab3b6b429222d0d628e196dddb488972c0340f04fbbbaace5\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(123, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"8\",\"date\":\"2022-12-08 12:37:32\",\"reference_no\":\"SALE2022\\/12\\/0004\",\"customer_id\":\"8\",\"customer\":\"Axtronica\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"113.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"113.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"2\",\"reserve_id\":null,\"hash\":\"f23e23036b04932c2d631bb35ec8001e0d502a2691aa58c1afa8cf2f51a822e7\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-26 10:15:34'),
(124, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"6\",\"date\":\"2022-11-16 19:08:00\",\"reference_no\":\"SALE2022\\/11\\/0002\",\"customer_id\":\"1\",\"customer\":\"Walk-in Customer\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":\"\",\"total\":\"60.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":\"\",\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"60.0000\",\"sale_status\":\"completed\",\"payment_status\":\"pending\",\"payment_term\":\"0\",\"due_date\":null,\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"2\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":\"0\",\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"0\",\"address_id\":null,\"reserve_id\":null,\"hash\":\"3fc521d9336605a248d7c2869bafaee82d0722286161d6b19363f4189fe6a11e\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":null},\"items\":[{\"id\":\"6\",\"sale_id\":\"6\",\"product_id\":\"2\",\"product_code\":\"554212121\",\"product_name\":\"Anti \\u2013 Acne Serum \\u2013 Brightens Skin, Fades Acne, Lighten Acne Scars & Control Excess Oil Production\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"20.0000\",\"unit_price\":\"20.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"subtotal\":\"20.0000\",\"serial_no\":\"\",\"real_unit_price\":\"20.0000\",\"sale_item_id\":null,\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null},{\"id\":\"7\",\"sale_id\":\"6\",\"product_id\":\"1\",\"product_code\":\"1234567879\",\"product_name\":\"Aloe Vera Juice [Pineapple Flavour] \\u2013 Natural Hydrator, Better Liver Function & Nutritious Booster\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"40.0000\",\"unit_price\":\"40.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"subtotal\":\"40.0000\",\"serial_no\":\"\",\"real_unit_price\":\"40.0000\",\"sale_item_id\":null,\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"1.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:34'),
(125, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"4\",\"date\":\"2022-11-16 10:01:00\",\"reference_no\":\"testreference\",\"customer_id\":\"6\",\"customer\":\"testcomp\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"-425.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"-425.0000\",\"sale_status\":\"returned\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":null,\"pos\":\"0\",\"paid\":\"-425.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":\"0\",\"return_sale_ref\":\"SR2022\\/11\\/0001\",\"sale_id\":\"2\",\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"0\",\"address_id\":null,\"reserve_id\":null,\"hash\":null,\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":null},\"items\":null}', '2022-12-26 10:15:34'),
(126, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"3\",\"date\":\"2022-11-16 09:59:00\",\"reference_no\":\"SALE2022\\/10\\/0001\",\"customer_id\":\"1\",\"customer\":\"Walk-in Customer\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":null,\"total\":\"-120.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"-120.0000\",\"sale_status\":\"returned\",\"payment_status\":\"paid\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":null,\"pos\":\"0\",\"paid\":\"-120.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":\"0\",\"return_sale_ref\":\"SR2022\\/11\\/0001\",\"sale_id\":\"1\",\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"0\",\"address_id\":null,\"reserve_id\":null,\"hash\":null,\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":null},\"items\":null}', '2022-12-26 10:15:34'),
(127, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"2\",\"date\":\"2022-11-16 09:50:00\",\"reference_no\":\"testreference\",\"customer_id\":\"6\",\"customer\":\"testcomp\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":\"\",\"total\":\"425.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":\"\",\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"425.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":\"0\",\"due_date\":null,\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"5\",\"pos\":\"0\",\"paid\":\"425.0000\",\"return_id\":\"4\",\"surcharge\":\"0.0000\",\"attachment\":\"0\",\"return_sale_ref\":\"SR2022\\/11\\/0001\",\"sale_id\":null,\"return_sale_total\":\"-425.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"0\",\"address_id\":null,\"reserve_id\":null,\"hash\":\"c95c1192f387be78cdc1a93491d38df5988760be6a954ddf666677f0f5334ad6\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":null},\"items\":[{\"id\":\"2\",\"sale_id\":\"2\",\"product_id\":\"3\",\"product_code\":\"TPR-11\",\"product_name\":\"Test Product 11\",\"product_type\":\"standard\",\"option_id\":\"3\",\"net_unit_price\":\"85.0000\",\"unit_price\":\"85.0000\",\"quantity\":\"5.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"subtotal\":\"425.0000\",\"serial_no\":\"\",\"real_unit_price\":\"85.0000\",\"sale_item_id\":null,\"product_unit_id\":\"2\",\"product_unit_code\":\"pc\",\"unit_quantity\":\"5.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:34'),
(128, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"1\",\"date\":\"2022-10-03 02:06:00\",\"reference_no\":\"SALE2022\\/10\\/0001\",\"customer_id\":\"1\",\"customer\":\"Walk-in Customer\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":\"\",\"total\":\"120.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":\"\",\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"120.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":\"0\",\"due_date\":null,\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"3\",\"pos\":\"0\",\"paid\":\"120.0000\",\"return_id\":\"3\",\"surcharge\":\"0.0000\",\"attachment\":\"0\",\"return_sale_ref\":\"SR2022\\/11\\/0001\",\"sale_id\":null,\"return_sale_total\":\"-120.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"0\",\"address_id\":null,\"reserve_id\":null,\"hash\":\"891b48ebecd36a9938595cf2e9957fd437a06d250ec20f9ca597e2d237faf4fe\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":null},\"items\":[{\"id\":\"1\",\"sale_id\":\"1\",\"product_id\":\"1\",\"product_code\":\"1234567879\",\"product_name\":\"Aloe Vera Juice [Pineapple Flavour] \\u2013 Natural Hydrator, Better Liver Function & Nutritious Booster\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"40.0000\",\"unit_price\":\"40.0000\",\"quantity\":\"3.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"subtotal\":\"120.0000\",\"serial_no\":\"\",\"real_unit_price\":\"40.0000\",\"sale_item_id\":null,\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"3.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:15:34'),
(129, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"5\",\"date\":\"2022-11-16 16:37:47\",\"reference_no\":\"SALE\\/POS2022\\/11\\/0001\",\"customer_id\":\"1\",\"customer\":\"Walk-in Customer\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"staff_note\":\"\",\"total\":\"60.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":\"\",\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"60.0000\",\"sale_status\":\"completed\",\"payment_status\":\"paid\",\"payment_term\":\"0\",\"due_date\":null,\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"1\",\"paid\":\"60.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":\"0.0000\",\"suspend_note\":null,\"api\":\"0\",\"shop\":\"0\",\"address_id\":null,\"reserve_id\":null,\"hash\":\"4f7a699fbefa94092aac7dc0424bd2ec25aa2f236846d9961b375ccefaeec6cd\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":null},\"items\":[{\"id\":\"5\",\"sale_id\":\"5\",\"product_id\":\"23\",\"product_code\":\"93555055\",\"product_name\":\"Beauty Combo\",\"product_type\":\"combo\",\"option_id\":null,\"net_unit_price\":\"60.0000\",\"unit_price\":\"60.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"subtotal\":\"60.0000\",\"serial_no\":\"\",\"real_unit_price\":\"60.0000\",\"sale_item_id\":null,\"product_unit_id\":null,\"product_unit_code\":null,\"unit_quantity\":\"1.0000\",\"comment\":\"\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null,\"base_unit_id\":null,\"base_unit_code\":null}]}', '2022-12-26 10:16:05'),
(130, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"43\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"syed mmubasher\",\"company\":\"\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":\"AE\",\"phone\":\"12128127876\",\"email\":\"syed@mail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:16:38'),
(131, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"44\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Anus Ahmad\",\"company\":\"\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":\"SA\",\"phone\":\"+923465320003\",\"email\":\"anusahmad2014@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:16:38'),
(132, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"45\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Anus Ahmad\",\"company\":\"\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":\"AE\",\"phone\":\"9661234567\",\"email\":\"anusahmad@hotmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:16:38'),
(133, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"46\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Anus Ahmad\",\"company\":\"\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":\"SA\",\"phone\":\"9661234657\",\"email\":\"anusahmad@hotmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:16:38'),
(134, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"47\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Anus Ahmad\",\"company\":\"\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":\"SA\",\"phone\":\"9661234567\",\"email\":\"anusahmad@hotmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:16:38'),
(135, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"48\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Anus Ahmad\",\"company\":\"\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":\"SA\",\"phone\":\"9661234567\",\"email\":\"anusahmad@hotmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:16:38'),
(136, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"49\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Anus Ahmad\",\"company\":\"\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":\"SA\",\"phone\":\"9661234567\",\"email\":\"anusahmad@hotmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:16:38'),
(137, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"50\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Anus Ahmad\",\"company\":\"\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":\"SA\",\"phone\":\"9661234567\",\"email\":\"anusahmad@hotmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:16:38'),
(138, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"51\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Syed  Mubasher\",\"company\":\"\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":\"AE\",\"phone\":\"12241242\",\"email\":\"syedmubasher433@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:16:38'),
(139, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"53\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"atiq etst\",\"company\":\"\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":\"SA\",\"phone\":\"12312341\",\"email\":\"atiq@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:16:59'),
(140, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"55\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"shoib shoib\",\"company\":\"\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":\"AE\",\"phone\":\"+44 123456\",\"email\":\"shoib@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:16:59'),
(141, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"56\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"tetsingngn uijoi\",\"company\":\"\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":\"SA\",\"phone\":\"12121212122\",\"email\":\"testing@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:16:59'),
(142, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"27\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"test\",\"company\":\"fff\",\"vat_no\":null,\"address\":\"kk<br>kk\",\"city\":\"k\",\"state\":\"k\",\"postal_code\":\"k\",\"country\":\"k\",\"phone\":\"11212122\",\"email\":\"fff!@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:17:22'),
(143, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"13\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"test\",\"company\":\"increate\",\"vat_no\":null,\"address\":\"rwp<br>rwp\",\"city\":\"rwp\",\"state\":\"rwp\",\"postal_code\":\"47000\",\"country\":\"pk\",\"phone\":\"123456789\",\"email\":\"test@increate.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:17:22'),
(144, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"20\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"shah g\",\"company\":\"increate\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":null,\"phone\":\"1241241278\",\"email\":\"shahmubasher53@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:17:22'),
(145, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"9\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Mubasher\",\"company\":\"test\",\"vat_no\":null,\"address\":\"rwp<br>rwp\",\"city\":\"rawalpindi\",\"state\":\"punjab\",\"postal_code\":\"47000\",\"country\":\"pakistan\",\"phone\":\"03340542941\",\"email\":\"mubasher.increate@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:19:04'),
(146, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"7\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"syed\",\"company\":\"mubasher\",\"vat_no\":null,\"address\":\"rwp<br>rwp\",\"city\":\"rwp\",\"state\":\"\",\"postal_code\":\"47000\",\"country\":\"Pakistan\",\"phone\":\"1234567890\",\"email\":\"syedmubasher433@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:19:05'),
(147, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"18\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Test Test\",\"company\":\"test\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":null,\"phone\":\"1212121212\",\"email\":\"test@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:19:20'),
(148, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"21\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"test test\",\"company\":\"test\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":null,\"phone\":\"121211212\",\"email\":\"test@checkdev.xyz\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:19:33'),
(149, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"14\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"test\",\"company\":\"test\",\"vat_no\":null,\"address\":\"rwp<br>rp\",\"city\":\"ei\",\"state\":\"rwp\",\"postal_code\":\"47000\",\"country\":\"pakistan\",\"phone\":\"98129872981\",\"email\":\"test@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:20:06'),
(150, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"22\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"test\",\"company\":\"test\",\"vat_no\":null,\"address\":\"212<br>rwp\",\"city\":\"rwo\",\"state\":\"jkj\",\"postal_code\":\"4600\",\"country\":\"pk\",\"phone\":\"121212122\",\"email\":\"test2@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:20:44'),
(151, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"23\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"test\",\"company\":\"test\",\"vat_no\":null,\"address\":\"test<br>test\",\"city\":\"test\",\"state\":\"tetette\",\"postal_code\":\"test\",\"country\":\"test\",\"phone\":\"1212211212\",\"email\":\"test@gmail.commmmmm\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:20:44'),
(152, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"24\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"ahmad\",\"company\":\"test\",\"vat_no\":null,\"address\":\"test<br>\",\"city\":\"amman\",\"state\":\"\",\"postal_code\":\"1117\",\"country\":\"Jordan\",\"phone\":\"9555555555555\",\"email\":\"ahmad.ab@stspayone.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:20:44'),
(153, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"25\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"ahmad\",\"company\":\"test\",\"vat_no\":null,\"address\":\"test<br>\",\"city\":\"amman\",\"state\":\"\",\"postal_code\":\"1117\",\"country\":\"Jordan\",\"phone\":\"9627555555555\",\"email\":\"test@test.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:20:44'),
(154, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"29\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"test\",\"company\":\"test\",\"vat_no\":null,\"address\":\"rrr<br>rrrrr\",\"city\":\"rrr\",\"state\":\"rrr\",\"postal_code\":\"12121212\",\"country\":\"SA\",\"phone\":\"2222\",\"email\":\"tesstt@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:20:44'),
(155, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"6\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"3\",\"customer_group_name\":\"Distributor\",\"name\":\"test\",\"company\":\"testcomp\",\"vat_no\":\"\",\"address\":\"jkhjkh\",\"city\":\"kjhj\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"65465657\",\"email\":\"testtest@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-26 10:20:44'),
(156, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"28\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"test\",\"company\":\"testinggg\",\"vat_no\":null,\"address\":\"rwp<br>rwp\",\"city\":\"rwp\",\"state\":\"rwp\",\"postal_code\":\"121212\",\"country\":\"SA\",\"phone\":\"121212\",\"email\":\"testing@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-26 10:20:44'),
(157, 'Return is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"2\",\"date\":\"2022-12-18 05:55:00\",\"reference_no\":\"tytutyu\",\"customer_id\":\"32\",\"customer\":\"alnahdi\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"3\",\"note\":\"\",\"staff_note\":\"\",\"total\":\"5025.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":\"50%\",\"total_discount\":\"2512.5000\",\"order_discount\":\"2512.5000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"grand_total\":\"2512.0000\",\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"25\",\"paid\":\"0.0000\",\"surcharge\":\"0.0000\",\"attachment\":null,\"hash\":\"5d6c11ce63c1bfa51277c9f264c2666d403564f2cdd4dce2f3515c13ecc90492\",\"cgst\":null,\"sgst\":null,\"igst\":null,\"shipping\":\"0.0000\"},\"items\":[{\"id\":\"2\",\"return_id\":\"2\",\"product_id\":\"3\",\"product_code\":\"TPR-11\",\"product_name\":\"Test Product 11\",\"product_type\":\"standard\",\"option_id\":\"3\",\"net_unit_price\":\"85.0000\",\"unit_price\":\"85.0000\",\"quantity\":\"5.0000\",\"warehouse_id\":\"3\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"subtotal\":\"425.0000\",\"serial_no\":\"\",\"real_unit_price\":\"85.0000\",\"product_unit_id\":\"2\",\"product_unit_code\":\"pc\",\"unit_quantity\":\"5.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null},{\"id\":\"3\",\"return_id\":\"2\",\"product_id\":\"28\",\"product_code\":\"5765765\",\"product_name\":\"sulfad2\",\"product_type\":\"standard\",\"option_id\":\"21\",\"net_unit_price\":\"230.0000\",\"unit_price\":\"230.0000\",\"quantity\":\"20.0000\",\"warehouse_id\":\"3\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"subtotal\":\"4600.0000\",\"serial_no\":\"\",\"real_unit_price\":\"120.0000\",\"product_unit_id\":\"2\",\"product_unit_code\":\"pc\",\"unit_quantity\":\"20.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null}]}', '2022-12-26 10:21:52');
INSERT INTO `sma_logs` (`id`, `detail`, `model`, `date`) VALUES
(158, 'Return is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"3\",\"date\":\"2022-12-17 17:30:00\",\"reference_no\":\"tytutyuffgg\",\"customer_id\":\"32\",\"customer\":\"alnahdi\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"warehouse_id\":\"4\",\"note\":\"\",\"staff_note\":\"\",\"total\":\"2725.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":\"50%\",\"total_discount\":\"1362.5000\",\"order_discount\":\"1362.5000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"grand_total\":\"1362.0000\",\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"15\",\"paid\":\"0.0000\",\"surcharge\":\"0.0000\",\"attachment\":null,\"hash\":\"1f96203404dca6774f972c96615fbe7dd1e850d7c4a9193d76d15d701cfaa625\",\"cgst\":null,\"sgst\":null,\"igst\":null,\"shipping\":\"0.0000\"},\"items\":[{\"id\":\"4\",\"return_id\":\"3\",\"product_id\":\"3\",\"product_code\":\"TPR-11\",\"product_name\":\"Test Product 11\",\"product_type\":\"standard\",\"option_id\":\"3\",\"net_unit_price\":\"85.0000\",\"unit_price\":\"85.0000\",\"quantity\":\"5.0000\",\"warehouse_id\":\"4\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"subtotal\":\"425.0000\",\"serial_no\":\"\",\"real_unit_price\":\"85.0000\",\"product_unit_id\":\"2\",\"product_unit_code\":\"pc\",\"unit_quantity\":\"5.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null},{\"id\":\"5\",\"return_id\":\"3\",\"product_id\":\"28\",\"product_code\":\"5765765\",\"product_name\":\"sulfad2\",\"product_type\":\"standard\",\"option_id\":\"21\",\"net_unit_price\":\"230.0000\",\"unit_price\":\"230.0000\",\"quantity\":\"10.0000\",\"warehouse_id\":\"4\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"subtotal\":\"2300.0000\",\"serial_no\":\"\",\"real_unit_price\":\"120.0000\",\"product_unit_id\":\"2\",\"product_unit_code\":\"pc\",\"unit_quantity\":\"10.0000\",\"comment\":null,\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null}]}', '2022-12-26 10:21:54'),
(159, 'Purchase is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"10\",\"reference_no\":\"123\",\"date\":\"2022-12-23 15:16:00\",\"supplier_id\":\"4\",\"supplier\":\"Test Supplier Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"total\":\"7680000.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":\"\",\"order_discount\":\"0.0000\",\"total_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"7680000.0000\",\"paid\":\"7680000.0000\",\"status\":\"received\",\"payment_status\":\"paid\",\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"attachment\":\"0\",\"payment_term\":\"0\",\"due_date\":null,\"return_id\":null,\"surcharge\":\"0.0000\",\"return_purchase_ref\":null,\"purchase_id\":null,\"return_purchase_total\":\"0.0000\",\"cgst\":null,\"sgst\":null,\"igst\":null},\"items\":[{\"id\":\"54\",\"purchase_id\":\"10\",\"transfer_id\":null,\"product_id\":\"36\",\"product_code\":\"123412\",\"product_name\":\"test\",\"option_id\":null,\"net_unit_cost\":\"76800.0000\",\"quantity\":\"6400.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"7680000.0000\",\"quantity_balance\":\"6400.0000\",\"date\":\"2022-12-23\",\"status\":\"received\",\"unit_cost\":\"76800.0000\",\"real_unit_cost\":\"76800.0000\",\"quantity_received\":\"6400.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"2\",\"product_unit_code\":\"Box01\",\"unit_quantity\":\"100.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"1200.0000\"}]}', '2022-12-26 10:22:59'),
(160, 'Purchase is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"8\",\"reference_no\":\"PO2022\\/12\\/0005\",\"date\":\"2022-12-17 20:46:00\",\"supplier_id\":\"31\",\"supplier\":\"LAND PHARMA\",\"warehouse_id\":\"4\",\"note\":\"\",\"total\":\"4000.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":\"\",\"order_discount\":\"0.0000\",\"total_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"4000.0000\",\"paid\":\"0.0000\",\"status\":\"received\",\"payment_status\":\"pending\",\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"attachment\":\"0\",\"payment_term\":\"0\",\"due_date\":null,\"return_id\":null,\"surcharge\":\"0.0000\",\"return_purchase_ref\":null,\"purchase_id\":null,\"return_purchase_total\":\"0.0000\",\"cgst\":null,\"sgst\":null,\"igst\":null},\"items\":[{\"id\":\"39\",\"purchase_id\":\"8\",\"transfer_id\":null,\"product_id\":\"29\",\"product_code\":\"45545\",\"product_name\":\"sulfad100\",\"option_id\":\"26\",\"net_unit_cost\":\"200.0000\",\"quantity\":\"20.0000\",\"warehouse_id\":\"4\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"4000.0000\",\"quantity_balance\":\"20.0000\",\"date\":\"2022-12-17\",\"status\":\"received\",\"unit_cost\":\"200.0000\",\"real_unit_cost\":\"200.0000\",\"quantity_received\":\"20.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"2\",\"product_unit_code\":\"pc\",\"unit_quantity\":\"20.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"200.0000\"}]}', '2022-12-26 10:22:59'),
(161, 'Purchase is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"7\",\"reference_no\":\"PO2022\\/12\\/0004\",\"date\":\"2022-12-17 20:44:00\",\"supplier_id\":\"31\",\"supplier\":\"LAND PHARMA\",\"warehouse_id\":\"3\",\"note\":\"\",\"total\":\"20000.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":\"\",\"order_discount\":\"0.0000\",\"total_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"20000.0000\",\"paid\":\"0.0000\",\"status\":\"received\",\"payment_status\":\"pending\",\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"attachment\":\"0\",\"payment_term\":\"0\",\"due_date\":null,\"return_id\":null,\"surcharge\":\"0.0000\",\"return_purchase_ref\":null,\"purchase_id\":null,\"return_purchase_total\":\"0.0000\",\"cgst\":null,\"sgst\":null,\"igst\":null},\"items\":[{\"id\":\"38\",\"purchase_id\":\"7\",\"transfer_id\":null,\"product_id\":\"29\",\"product_code\":\"45545\",\"product_name\":\"sulfad100\",\"option_id\":\"26\",\"net_unit_cost\":\"200.0000\",\"quantity\":\"100.0000\",\"warehouse_id\":\"3\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"20000.0000\",\"quantity_balance\":\"99.0000\",\"date\":\"2022-12-17\",\"status\":\"received\",\"unit_cost\":\"200.0000\",\"real_unit_cost\":\"200.0000\",\"quantity_received\":\"100.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"2\",\"product_unit_code\":\"pc\",\"unit_quantity\":\"100.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"200.0000\"}]}', '2022-12-26 10:22:59'),
(162, 'Purchase is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"6\",\"reference_no\":\"r65546\",\"date\":\"2022-12-17 19:20:00\",\"supplier_id\":\"31\",\"supplier\":\"LAND PHARMA\",\"warehouse_id\":\"4\",\"note\":\"&lt;p&gt;test&lt;&sol;p&gt;\",\"total\":\"24000.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":\"50%\",\"order_discount\":\"12000.0000\",\"total_discount\":\"12000.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"2000.0000\",\"grand_total\":\"14000.0000\",\"paid\":\"1000.0000\",\"status\":\"received\",\"payment_status\":\"partial\",\"created_by\":\"1\",\"updated_by\":\"1\",\"updated_at\":\"2022-12-17 19:32:59\",\"attachment\":\"0\",\"payment_term\":\"90\",\"due_date\":\"2023-03-17\",\"return_id\":null,\"surcharge\":\"0.0000\",\"return_purchase_ref\":null,\"purchase_id\":null,\"return_purchase_total\":\"0.0000\",\"cgst\":null,\"sgst\":null,\"igst\":null},\"items\":[{\"id\":\"33\",\"purchase_id\":\"6\",\"transfer_id\":null,\"product_id\":\"28\",\"product_code\":\"5765765\",\"product_name\":\"sulfad2\",\"option_id\":\"21\",\"net_unit_cost\":\"120.0000\",\"quantity\":\"200.0000\",\"warehouse_id\":\"4\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"24000.0000\",\"quantity_balance\":\"0.0000\",\"date\":\"2022-12-17\",\"status\":\"received\",\"unit_cost\":\"120.0000\",\"real_unit_cost\":\"120.0000\",\"quantity_received\":\"200.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"2\",\"product_unit_code\":\"pc\",\"unit_quantity\":\"200.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"120.0000\"}]}', '2022-12-26 10:22:59'),
(163, 'Purchase is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"5\",\"reference_no\":\"12345\",\"date\":\"2022-11-22 15:30:00\",\"supplier_id\":\"4\",\"supplier\":\"Test Supplier Company\",\"warehouse_id\":\"1\",\"note\":\"&lt;p&gt;Received first order&period;&lt;&sol;p&gt;\",\"total\":\"10240.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":\"\",\"order_discount\":\"0.0000\",\"total_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"10240.0000\",\"paid\":\"0.0000\",\"status\":\"received\",\"payment_status\":\"pending\",\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"attachment\":\"0\",\"payment_term\":\"0\",\"due_date\":null,\"return_id\":null,\"surcharge\":\"0.0000\",\"return_purchase_ref\":null,\"purchase_id\":null,\"return_purchase_total\":\"0.0000\",\"cgst\":null,\"sgst\":null,\"igst\":null},\"items\":[{\"id\":\"10\",\"purchase_id\":\"5\",\"transfer_id\":null,\"product_id\":\"8\",\"product_code\":\"8057506630011\",\"product_name\":\"Grow Hair Nails\",\"option_id\":null,\"net_unit_cost\":\"83.0000\",\"quantity\":\"10.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"830.0000\",\"quantity_balance\":\"8.0000\",\"date\":\"2022-11-22\",\"status\":\"received\",\"unit_cost\":\"83.0000\",\"real_unit_cost\":\"83.0000\",\"quantity_received\":\"10.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"10.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"83.0000\"},{\"id\":\"11\",\"purchase_id\":\"5\",\"transfer_id\":null,\"product_id\":\"22\",\"product_code\":\"8057506630110\",\"product_name\":\"Black Seed\",\"option_id\":null,\"net_unit_cost\":\"45.0000\",\"quantity\":\"10.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"450.0000\",\"quantity_balance\":\"8.0000\",\"date\":\"2022-11-22\",\"status\":\"received\",\"unit_cost\":\"45.0000\",\"real_unit_cost\":\"45.0000\",\"quantity_received\":\"10.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"10.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"45.0000\"},{\"id\":\"12\",\"purchase_id\":\"5\",\"transfer_id\":null,\"product_id\":\"10\",\"product_code\":\"8057506630332\",\"product_name\":\"Healthy Joints\",\"option_id\":null,\"net_unit_cost\":\"120.0000\",\"quantity\":\"10.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"1200.0000\",\"quantity_balance\":\"10.0000\",\"date\":\"2022-11-22\",\"status\":\"received\",\"unit_cost\":\"120.0000\",\"real_unit_cost\":\"120.0000\",\"quantity_received\":\"10.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"10.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"120.0000\"},{\"id\":\"13\",\"purchase_id\":\"5\",\"transfer_id\":null,\"product_id\":\"16\",\"product_code\":\"8057506630394\",\"product_name\":\"Healthy Vasco\",\"option_id\":null,\"net_unit_cost\":\"64.0000\",\"quantity\":\"10.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"640.0000\",\"quantity_balance\":\"10.0000\",\"date\":\"2022-11-22\",\"status\":\"received\",\"unit_cost\":\"64.0000\",\"real_unit_cost\":\"64.0000\",\"quantity_received\":\"10.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"10.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"64.0000\"},{\"id\":\"14\",\"purchase_id\":\"5\",\"transfer_id\":null,\"product_id\":\"14\",\"product_code\":\"8057506630196\",\"product_name\":\"Immunity Defense\",\"option_id\":null,\"net_unit_cost\":\"94.0000\",\"quantity\":\"10.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"940.0000\",\"quantity_balance\":\"7.0000\",\"date\":\"2022-11-22\",\"status\":\"received\",\"unit_cost\":\"94.0000\",\"real_unit_cost\":\"94.0000\",\"quantity_received\":\"10.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"10.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"94.0000\"},{\"id\":\"15\",\"purchase_id\":\"5\",\"transfer_id\":null,\"product_id\":\"12\",\"product_code\":\"8057506630318\",\"product_name\":\"Maximum Energy\",\"option_id\":null,\"net_unit_cost\":\"75.0000\",\"quantity\":\"10.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"750.0000\",\"quantity_balance\":\"10.0000\",\"date\":\"2022-11-22\",\"status\":\"received\",\"unit_cost\":\"75.0000\",\"real_unit_cost\":\"75.0000\",\"quantity_received\":\"10.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"10.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"75.0000\"},{\"id\":\"16\",\"purchase_id\":\"5\",\"transfer_id\":null,\"product_id\":\"17\",\"product_code\":\"8057506630301\",\"product_name\":\"Maximum Power\",\"option_id\":null,\"net_unit_cost\":\"75.0000\",\"quantity\":\"10.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"750.0000\",\"quantity_balance\":\"5.0000\",\"date\":\"2022-11-22\",\"status\":\"received\",\"unit_cost\":\"75.0000\",\"real_unit_cost\":\"75.0000\",\"quantity_received\":\"10.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"10.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"75.0000\"},{\"id\":\"17\",\"purchase_id\":\"5\",\"transfer_id\":null,\"product_id\":\"14\",\"product_code\":\"8057506630196\",\"product_name\":\"Immunity Defense\",\"option_id\":null,\"net_unit_cost\":\"94.0000\",\"quantity\":\"10.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"940.0000\",\"quantity_balance\":\"10.0000\",\"date\":\"2022-11-22\",\"status\":\"received\",\"unit_cost\":\"94.0000\",\"real_unit_cost\":\"94.0000\",\"quantity_received\":\"10.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"10.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"94.0000\"},{\"id\":\"18\",\"purchase_id\":\"5\",\"transfer_id\":null,\"product_id\":\"9\",\"product_code\":\"8057506630066\",\"product_name\":\"Flex\",\"option_id\":null,\"net_unit_cost\":\"113.0000\",\"quantity\":\"10.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"1130.0000\",\"quantity_balance\":\"3.0000\",\"date\":\"2022-11-22\",\"status\":\"received\",\"unit_cost\":\"113.0000\",\"real_unit_cost\":\"113.0000\",\"quantity_received\":\"10.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"10.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"113.0000\"},{\"id\":\"19\",\"purchase_id\":\"5\",\"transfer_id\":null,\"product_id\":\"15\",\"product_code\":\"8057506630097\",\"product_name\":\"CoEnzyme\",\"option_id\":null,\"net_unit_cost\":\"44.0000\",\"quantity\":\"10.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"440.0000\",\"quantity_balance\":\"6.0000\",\"date\":\"2022-11-22\",\"status\":\"received\",\"unit_cost\":\"44.0000\",\"real_unit_cost\":\"44.0000\",\"quantity_received\":\"10.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"10.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"44.0000\"},{\"id\":\"20\",\"purchase_id\":\"5\",\"transfer_id\":null,\"product_id\":\"18\",\"product_code\":\"8057506630042\",\"product_name\":\"Men\\u2019s Fertility\",\"option_id\":null,\"net_unit_cost\":\"113.0000\",\"quantity\":\"10.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"1130.0000\",\"quantity_balance\":\"6.0000\",\"date\":\"2022-11-22\",\"status\":\"received\",\"unit_cost\":\"113.0000\",\"real_unit_cost\":\"113.0000\",\"quantity_received\":\"10.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"10.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"113.0000\"},{\"id\":\"21\",\"purchase_id\":\"5\",\"transfer_id\":null,\"product_id\":\"2\",\"product_code\":\"554212121\",\"product_name\":\"Anti \\u2013 Acne Serum \\u2013 Brightens Skin, Fades Acne, Lighten Acne Scars & Control Excess Oil Production\",\"option_id\":null,\"net_unit_cost\":\"10.0000\",\"quantity\":\"10.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"100.0000\",\"quantity_balance\":\"5.0000\",\"date\":\"2022-11-22\",\"status\":\"received\",\"unit_cost\":\"10.0000\",\"real_unit_cost\":\"10.0000\",\"quantity_received\":\"10.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"10.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"10.0000\"},{\"id\":\"22\",\"purchase_id\":\"5\",\"transfer_id\":null,\"product_id\":\"7\",\"product_code\":\"8057506630271\",\"product_name\":\"Anti Aging\",\"option_id\":null,\"net_unit_cost\":\"94.0000\",\"quantity\":\"10.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"940.0000\",\"quantity_balance\":\"9.0000\",\"date\":\"2022-11-22\",\"status\":\"received\",\"unit_cost\":\"94.0000\",\"real_unit_cost\":\"94.0000\",\"quantity_received\":\"10.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"10.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"94.0000\"}]}', '2022-12-26 10:22:59'),
(164, 'Purchase is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"4\",\"reference_no\":\"PO2022\\/11\\/0003\",\"date\":\"2022-11-16 19:06:00\",\"supplier_id\":\"5\",\"supplier\":\"Test Supplier Company 2\",\"warehouse_id\":\"1\",\"note\":\"\",\"total\":\"10.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":\"\",\"order_discount\":\"0.0000\",\"total_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"10.0000\",\"paid\":\"0.0000\",\"status\":\"received\",\"payment_status\":\"pending\",\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"attachment\":\"0\",\"payment_term\":\"0\",\"due_date\":null,\"return_id\":null,\"surcharge\":\"0.0000\",\"return_purchase_ref\":null,\"purchase_id\":null,\"return_purchase_total\":\"0.0000\",\"cgst\":null,\"sgst\":null,\"igst\":null},\"items\":[{\"id\":\"8\",\"purchase_id\":\"4\",\"transfer_id\":null,\"product_id\":\"2\",\"product_code\":\"554212121\",\"product_name\":\"Anti \\u2013 Acne Serum \\u2013 Brightens Skin, Fades Acne, Lighten Acne Scars & Control Excess Oil Production\",\"option_id\":null,\"net_unit_cost\":\"10.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"10.0000\",\"quantity_balance\":\"1.0000\",\"date\":\"2022-11-16\",\"status\":\"received\",\"unit_cost\":\"10.0000\",\"real_unit_cost\":\"10.0000\",\"quantity_received\":\"1.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"1.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"10.0000\"}]}', '2022-12-26 10:22:59'),
(165, 'Purchase is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"3\",\"reference_no\":\"1111\",\"date\":\"2022-11-16 18:26:00\",\"supplier_id\":\"4\",\"supplier\":\"Test Supplier Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"total\":\"2000.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":\"\",\"order_discount\":\"0.0000\",\"total_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"2000.0000\",\"paid\":\"0.0000\",\"status\":\"received\",\"payment_status\":\"pending\",\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"attachment\":\"0\",\"payment_term\":\"0\",\"due_date\":null,\"return_id\":null,\"surcharge\":\"0.0000\",\"return_purchase_ref\":null,\"purchase_id\":null,\"return_purchase_total\":\"0.0000\",\"cgst\":null,\"sgst\":null,\"igst\":null},\"items\":[{\"id\":\"6\",\"purchase_id\":\"3\",\"transfer_id\":null,\"product_id\":\"1\",\"product_code\":\"1234567879\",\"product_name\":\"Aloe Vera Juice [Pineapple Flavour] \\u2013 Natural Hydrator, Better Liver Function & Nutritious Booster\",\"option_id\":null,\"net_unit_cost\":\"20.0000\",\"quantity\":\"100.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"2000.0000\",\"quantity_balance\":\"143.0000\",\"date\":\"2022-11-16\",\"status\":\"received\",\"unit_cost\":\"20.0000\",\"real_unit_cost\":\"20.0000\",\"quantity_received\":\"100.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"100.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"20.0000\"}]}', '2022-12-26 10:22:59'),
(166, 'Purchase is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"2\",\"reference_no\":\"PO2022\\/10\\/0002\",\"date\":\"2022-10-03 02:04:00\",\"supplier_id\":\"5\",\"supplier\":\"Test Supplier Company 2\",\"warehouse_id\":\"1\",\"note\":\"\",\"total\":\"500.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":\"\",\"order_discount\":\"0.0000\",\"total_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"500.0000\",\"paid\":\"500.0000\",\"status\":\"received\",\"payment_status\":\"paid\",\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"attachment\":\"0\",\"payment_term\":\"0\",\"due_date\":null,\"return_id\":null,\"surcharge\":\"0.0000\",\"return_purchase_ref\":null,\"purchase_id\":null,\"return_purchase_total\":\"0.0000\",\"cgst\":null,\"sgst\":null,\"igst\":null},\"items\":[{\"id\":\"2\",\"purchase_id\":\"2\",\"transfer_id\":null,\"product_id\":\"2\",\"product_code\":\"554212121\",\"product_name\":\"Anti \\u2013 Acne Serum \\u2013 Brightens Skin, Fades Acne, Lighten Acne Scars & Control Excess Oil Production\",\"option_id\":null,\"net_unit_cost\":\"10.0000\",\"quantity\":\"50.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"500.0000\",\"quantity_balance\":\"48.0000\",\"date\":\"2022-10-03\",\"status\":\"received\",\"unit_cost\":\"10.0000\",\"real_unit_cost\":\"10.0000\",\"quantity_received\":\"50.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"50.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"10.0000\"}]}', '2022-12-26 10:22:59'),
(167, 'Purchase is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"1\",\"reference_no\":\"PO2022\\/10\\/0001\",\"date\":\"2022-10-03 02:03:00\",\"supplier_id\":\"4\",\"supplier\":\"Test Supplier Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"total\":\"1000.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":\"\",\"order_discount\":\"0.0000\",\"total_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"1000.0000\",\"paid\":\"0.0000\",\"status\":\"received\",\"payment_status\":\"pending\",\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"attachment\":\"0\",\"payment_term\":\"0\",\"due_date\":null,\"return_id\":null,\"surcharge\":\"0.0000\",\"return_purchase_ref\":null,\"purchase_id\":null,\"return_purchase_total\":\"0.0000\",\"cgst\":null,\"sgst\":null,\"igst\":null},\"items\":[{\"id\":\"1\",\"purchase_id\":\"1\",\"transfer_id\":null,\"product_id\":\"1\",\"product_code\":\"1234567879\",\"product_name\":\"Aloe Vera Juice [Pineapple Flavour] \\u2013 Natural Hydrator, Better Liver Function & Nutritious Booster\",\"option_id\":null,\"net_unit_cost\":\"20.0000\",\"quantity\":\"50.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":null,\"subtotal\":\"1000.0000\",\"quantity_balance\":\"0.0000\",\"date\":\"2022-10-03\",\"status\":\"received\",\"unit_cost\":\"20.0000\",\"real_unit_cost\":\"20.0000\",\"quantity_received\":\"50.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"50.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"20.0000\"}]}', '2022-12-26 10:22:59'),
(168, 'Transfer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"4\",\"transfer_no\":\"TR2022\\/12\\/0002\",\"date\":\"2022-12-17 19:34:00\",\"from_warehouse_id\":\"4\",\"from_warehouse_code\":\"RUH002\",\"from_warehouse_name\":\"RIYADH WH\",\"to_warehouse_id\":\"3\",\"to_warehouse_code\":\"DXB001\",\"to_warehouse_name\":\"DUBAI WH\",\"note\":\"\",\"total\":\"36000.0000\",\"total_tax\":\"0.0000\",\"grand_total\":\"36000.0000\",\"created_by\":\"1\",\"status\":\"completed\",\"shipping\":\"0.0000\",\"attachment\":\"0\",\"cgst\":null,\"sgst\":null,\"igst\":null},\"items\":[{\"id\":\"34\",\"purchase_id\":null,\"transfer_id\":\"4\",\"product_id\":\"28\",\"product_code\":\"5765765\",\"product_name\":\"sulfad2\",\"option_id\":\"21\",\"net_unit_cost\":\"120.0000\",\"quantity\":\"300.0000\",\"warehouse_id\":\"3\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":null,\"expiry\":\"0000-00-00\",\"subtotal\":\"36000.0000\",\"quantity_balance\":\"230.0000\",\"date\":\"2022-12-17\",\"status\":\"received\",\"unit_cost\":\"120.0000\",\"real_unit_cost\":\"120.0000\",\"quantity_received\":null,\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"2\",\"product_unit_code\":\"pc\",\"unit_quantity\":\"300.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":null,\"variant\":null,\"unit\":null,\"hsn_code\":null,\"second_name\":null}]}', '2022-12-26 10:30:30'),
(169, 'Transfer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"3\",\"transfer_no\":\"123\",\"date\":\"2022-11-16 19:09:00\",\"from_warehouse_id\":\"1\",\"from_warehouse_code\":\"WHI\",\"from_warehouse_name\":\"Warehouse 1\",\"to_warehouse_id\":\"2\",\"to_warehouse_code\":\"WHII\",\"to_warehouse_name\":\"Warehouse 2\",\"note\":\"\",\"total\":\"1000.0000\",\"total_tax\":\"0.0000\",\"grand_total\":\"1000.0000\",\"created_by\":\"1\",\"status\":\"completed\",\"shipping\":\"0.0000\",\"attachment\":\"0\",\"cgst\":null,\"sgst\":null,\"igst\":null},\"items\":[{\"id\":\"9\",\"purchase_id\":null,\"transfer_id\":\"3\",\"product_id\":\"1\",\"product_code\":\"1234567879\",\"product_name\":\"Aloe Vera Juice [Pineapple Flavour] \\u2013 Natural Hydrator, Better Liver Function & Nutritious Booster\",\"option_id\":null,\"net_unit_cost\":\"20.0000\",\"quantity\":\"50.0000\",\"warehouse_id\":\"2\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":null,\"expiry\":null,\"subtotal\":\"1000.0000\",\"quantity_balance\":\"50.0000\",\"date\":\"2022-11-16\",\"status\":\"received\",\"unit_cost\":\"20.0000\",\"real_unit_cost\":\"20.0000\",\"quantity_received\":null,\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"50.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":null,\"variant\":null,\"unit\":null,\"hsn_code\":null,\"second_name\":null}]}', '2022-12-26 10:30:30'),
(170, 'Transfer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"2\",\"transfer_no\":\"test mujtaba\",\"date\":\"2022-10-03 13:14:00\",\"from_warehouse_id\":\"1\",\"from_warehouse_code\":\"WHI\",\"from_warehouse_name\":\"Warehouse 1\",\"to_warehouse_id\":\"2\",\"to_warehouse_code\":\"WHII\",\"to_warehouse_name\":\"Warehouse 2\",\"note\":\"\",\"total\":\"30.0000\",\"total_tax\":\"0.0000\",\"grand_total\":\"30.0000\",\"created_by\":\"1\",\"status\":\"pending\",\"shipping\":\"0.0000\",\"attachment\":\"0\",\"cgst\":null,\"sgst\":null,\"igst\":null},\"items\":[{\"id\":\"1\",\"transfer_id\":\"2\",\"product_id\":\"1\",\"product_code\":\"1234567879\",\"product_name\":\"Aloe Vera Juice [Pineapple Flavour] \\u2013 Natural Hydrator, Better Liver Function & Nutritious Booster\",\"option_id\":null,\"expiry\":null,\"quantity\":\"1.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"item_tax\":\"0.0000\",\"net_unit_cost\":\"20.0000\",\"subtotal\":\"20.0000\",\"quantity_balance\":\"1.0000\",\"unit_cost\":\"20.0000\",\"real_unit_cost\":\"20.0000\",\"date\":\"2022-10-03\",\"warehouse_id\":\"2\",\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"1.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"variant\":null,\"unit\":null,\"hsn_code\":null,\"second_name\":null},{\"id\":\"2\",\"transfer_id\":\"2\",\"product_id\":\"2\",\"product_code\":\"554212121\",\"product_name\":\"Anti \\u2013 Acne Serum \\u2013 Brightens Skin, Fades Acne, Lighten Acne Scars & Control Excess Oil Production\",\"option_id\":null,\"expiry\":null,\"quantity\":\"1.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"item_tax\":\"0.0000\",\"net_unit_cost\":\"10.0000\",\"subtotal\":\"10.0000\",\"quantity_balance\":\"1.0000\",\"unit_cost\":\"10.0000\",\"real_unit_cost\":\"10.0000\",\"date\":\"2022-10-03\",\"warehouse_id\":\"2\",\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"1.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"variant\":null,\"unit\":null,\"hsn_code\":null,\"second_name\":null}]}', '2022-12-26 10:30:30'),
(171, 'Transfer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"1\",\"transfer_no\":\"TR2022\\/10\\/0001\",\"date\":\"2022-10-03 02:05:00\",\"from_warehouse_id\":\"1\",\"from_warehouse_code\":\"WHI\",\"from_warehouse_name\":\"Warehouse 1\",\"to_warehouse_id\":\"2\",\"to_warehouse_code\":\"WHII\",\"to_warehouse_name\":\"Warehouse 2\",\"note\":\"\",\"total\":\"200.0000\",\"total_tax\":\"0.0000\",\"grand_total\":\"200.0000\",\"created_by\":\"1\",\"status\":\"completed\",\"shipping\":\"0.0000\",\"attachment\":\"0\",\"cgst\":null,\"sgst\":null,\"igst\":null},\"items\":[{\"id\":\"3\",\"purchase_id\":null,\"transfer_id\":\"1\",\"product_id\":\"1\",\"product_code\":\"1234567879\",\"product_name\":\"Aloe Vera Juice [Pineapple Flavour] \\u2013 Natural Hydrator, Better Liver Function & Nutritious Booster\",\"option_id\":null,\"net_unit_cost\":\"20.0000\",\"quantity\":\"10.0000\",\"warehouse_id\":\"2\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":null,\"item_discount\":null,\"expiry\":null,\"subtotal\":\"200.0000\",\"quantity_balance\":\"10.0000\",\"date\":\"2022-10-03\",\"status\":\"received\",\"unit_cost\":\"20.0000\",\"real_unit_cost\":\"20.0000\",\"quantity_received\":null,\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"10.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":null,\"variant\":null,\"unit\":null,\"hsn_code\":null,\"second_name\":null}]}', '2022-12-26 10:30:30'),
(172, 'Purchase is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"9\",\"reference_no\":\"3w3w3-22\",\"date\":\"2022-12-19 17:34:00\",\"supplier_id\":\"4\",\"supplier\":\"Test Supplier Company\",\"warehouse_id\":\"1\",\"note\":\"\",\"total\":\"179200.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":\"\",\"order_discount\":\"0.0000\",\"total_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"179200.0000\",\"paid\":\"0.0000\",\"status\":\"received\",\"payment_status\":\"pending\",\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"attachment\":\"0\",\"payment_term\":\"0\",\"due_date\":null,\"return_id\":null,\"surcharge\":\"0.0000\",\"return_purchase_ref\":null,\"purchase_id\":null,\"return_purchase_total\":\"0.0000\",\"cgst\":null,\"sgst\":null,\"igst\":null},\"items\":[{\"id\":\"52\",\"purchase_id\":\"9\",\"transfer_id\":null,\"product_id\":\"26\",\"product_code\":\"3w3w3\",\"product_name\":\"amr\",\"option_id\":\"22\",\"net_unit_cost\":\"12800.0000\",\"quantity\":\"14.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"expiry\":\"2022-12-31\",\"subtotal\":\"179200.0000\",\"quantity_balance\":\"14.0000\",\"date\":\"2022-12-19\",\"status\":\"received\",\"unit_cost\":\"12800.0000\",\"real_unit_cost\":\"12800.0000\",\"quantity_received\":\"14.0000\",\"supplier_part_no\":null,\"purchase_item_id\":null,\"product_unit_id\":\"2\",\"product_unit_code\":\"Box01\",\"unit_quantity\":\"14.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"base_unit_cost\":\"12800.0000\"}]}', '2022-12-26 10:30:56'),
(173, 'Quotation is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"2\",\"date\":\"2022-11-16 19:04:00\",\"reference_no\":\"QUOTE2022\\/11\\/0002\",\"customer_id\":\"1\",\"customer\":\"Walk-in Customer\",\"warehouse_id\":\"1\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"note\":\"\",\"internal_note\":null,\"total\":\"154.0000\",\"product_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"order_discount_id\":\"\",\"total_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"154.0000\",\"status\":\"completed\",\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"attachment\":null,\"supplier_id\":\"0\",\"supplier\":null,\"hash\":\"9e846b4bb914cb8f889cfb4c0a06ab802241f47024d2e52f78768d0a8b8643d0\",\"cgst\":null,\"sgst\":null,\"igst\":null},\"items\":[{\"id\":\"2\",\"quote_id\":\"2\",\"product_id\":\"1\",\"product_code\":\"1234567879\",\"product_name\":\"Aloe Vera Juice [Pineapple Flavour] \\u2013 Natural Hydrator, Better Liver Function & Nutritious Booster\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"40.0000\",\"unit_price\":\"40.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"subtotal\":\"40.0000\",\"serial_no\":null,\"real_unit_price\":\"40.0000\",\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"1.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"unit\":null,\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null},{\"id\":\"3\",\"quote_id\":\"2\",\"product_id\":\"2\",\"product_code\":\"554212121\",\"product_name\":\"Anti \\u2013 Acne Serum \\u2013 Brightens Skin, Fades Acne, Lighten Acne Scars & Control Excess Oil Production\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"20.0000\",\"unit_price\":\"20.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"subtotal\":\"20.0000\",\"serial_no\":null,\"real_unit_price\":\"20.0000\",\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"1.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"unit\":null,\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null},{\"id\":\"4\",\"quote_id\":\"2\",\"product_id\":\"7\",\"product_code\":\"8057506630271\",\"product_name\":\"Anti Aging\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"94.0000\",\"unit_price\":\"94.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"subtotal\":\"94.0000\",\"serial_no\":null,\"real_unit_price\":\"94.0000\",\"product_unit_id\":\"3\",\"product_unit_code\":\"cap\",\"unit_quantity\":\"1.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"unit\":null,\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null}]}', '2022-12-26 10:31:10'),
(174, 'Quotation is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"1\",\"date\":\"2022-10-03 11:59:00\",\"reference_no\":\"QUOTE2022\\/10\\/0001\",\"customer_id\":\"1\",\"customer\":\"Walk-in Customer\",\"warehouse_id\":\"1\",\"biller_id\":\"3\",\"biller\":\"Test Biller Company\",\"note\":\"\",\"internal_note\":null,\"total\":\"40.0000\",\"product_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"order_discount_id\":\"\",\"total_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"40.0000\",\"status\":\"pending\",\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"attachment\":null,\"supplier_id\":\"0\",\"supplier\":null,\"hash\":\"34277d09e5164d3cf078e28671849a0228143332be6a70d73f40711254392062\",\"cgst\":null,\"sgst\":null,\"igst\":null},\"items\":[{\"id\":\"1\",\"quote_id\":\"1\",\"product_id\":\"1\",\"product_code\":\"1234567879\",\"product_name\":\"Aloe Vera Juice [Pineapple Flavour] \\u2013 Natural Hydrator, Better Liver Function & Nutritious Booster\",\"product_type\":\"standard\",\"option_id\":null,\"net_unit_price\":\"40.0000\",\"unit_price\":\"40.0000\",\"quantity\":\"1.0000\",\"warehouse_id\":\"1\",\"item_tax\":\"0.0000\",\"tax_rate_id\":\"1\",\"tax\":\"0\",\"discount\":\"0\",\"item_discount\":\"0.0000\",\"subtotal\":\"40.0000\",\"serial_no\":null,\"real_unit_price\":\"40.0000\",\"product_unit_id\":\"1\",\"product_unit_code\":\"test\",\"unit_quantity\":\"1.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"tax_code\":\"NT\",\"tax_name\":\"No Tax\",\"tax_rate\":\"0.0000\",\"unit\":null,\"image\":null,\"details\":null,\"variant\":null,\"hsn_code\":null,\"second_name\":null}]}', '2022-12-26 10:31:10'),
(175, 'Delivery is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"2\",\"date\":\"2022-12-19 12:26:00\",\"sale_id\":\"86\",\"do_reference_no\":\"DO2022\\/12\\/0002\",\"sale_reference_no\":\"SALE2022\\/12\\/0069\",\"customer\":\"Mohamed\",\"address\":\"<p>Riyadh<br><br>Riyadh Riyadh<br>11351 SA<br>Tel: +971585280538<\\/p>\",\"note\":\"\",\"status\":\"delivering\",\"attachment\":null,\"delivered_by\":\"aramex\",\"received_by\":\"\",\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null}}', '2022-12-26 10:37:17'),
(176, 'Delivery is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"1\",\"date\":\"2022-12-17 19:54:00\",\"sale_id\":\"85\",\"do_reference_no\":\"DO2022\\/12\\/0001\",\"sale_reference_no\":\"gf776\",\"customer\":\"alnahdi\",\"address\":\"<p>ghghg riyadh   <br>Tel: 6556 Email: testtest4545@gmail.com<\\/p>\",\"note\":\"\",\"status\":\"delivered\",\"attachment\":null,\"delivered_by\":\"aramex\",\"received_by\":\"mohamed\",\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null}}', '2022-12-26 10:37:17'),
(177, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"11\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"dg\",\"company\":\"\",\"vat_no\":null,\"address\":\"sdfs<br>s\",\"city\":\"riyadh\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"saudi\",\"phone\":\"05555\",\"email\":\"haseen_333@yahoo.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-27 07:56:17'),
(178, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"52\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"uieydk2121212 kjoidje2121212\",\"company\":\"\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":\"AE\",\"phone\":\"ioj1212121212\",\"email\":\"ojp@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-27 07:56:17'),
(179, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"54\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Mujtaba Sarwar\",\"company\":\"\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":\"AE\",\"phone\":\"+923325290945\",\"email\":\"sarwarmujtaba@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-27 07:56:17'),
(180, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"32\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"2\",\"customer_group_name\":\"Reseller\",\"name\":\"alnahdi\",\"company\":\"alnahdi\",\"vat_no\":\"\",\"address\":\"ghghg\",\"city\":\"riyadh\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"6556\",\"email\":\"testtest4545@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-27 07:56:17'),
(181, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"8\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"4\",\"customer_group_name\":\"New Customer (+10)\",\"name\":\"Anus Ahmad\",\"company\":\"Axtronica\",\"vat_no\":\"\",\"address\":\"test address\",\"city\":\"Rawalpindi\",\"state\":\"Punjab\",\"postal_code\":\"46000\",\"country\":\"Pakistan\",\"phone\":\"+923465320003\",\"email\":\"anusahmad2014@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-27 07:56:17'),
(182, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"30\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Anus Ahmad\",\"company\":\"Axtronica\",\"vat_no\":null,\"address\":\"test<br>\",\"city\":\"Riyadh\",\"state\":\"\",\"postal_code\":\"11663\",\"country\":\"SA\",\"phone\":\"9661234567\",\"email\":\"anus.increatetech@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-27 07:56:17'),
(183, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"26\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"test User\",\"company\":\"Crown\",\"vat_no\":null,\"address\":\"rwp<br>rwp\",\"city\":\"rwp\",\"state\":\"rwp\",\"postal_code\":\"120000\",\"country\":\"SA\",\"phone\":\"1212121212\",\"email\":\"crown@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-27 07:56:17'),
(184, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"10\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Mujtaba Sarwar\",\"company\":\"inCreate Technologies\",\"vat_no\":null,\"address\":\"Rawalpindi<br>Test\",\"city\":\"Rawalpindi Punjab\",\"state\":\"Punjab\",\"postal_code\":\"46000\",\"country\":\"Pakistan\",\"phone\":\"+923325290945\",\"email\":\"sarwarmujtaba@gmail.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-27 07:56:17'),
(185, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"36\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"mohamed aly\",\"company\":\"tryt\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":null,\"phone\":\"034545436545\",\"email\":\"testesttttt@gamil.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-27 07:56:17');
INSERT INTO `sma_logs` (`id`, `detail`, `model`, `date`) VALUES
(186, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"33\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Mohamed Elsheshtawy\",\"company\":\"Ultra\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":null,\"phone\":\"+971585280538\",\"email\":\"m.aly@ultramedhub.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-27 07:56:17'),
(187, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"34\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Mohamed Elsheshtawy2\",\"company\":\"ultra2\",\"vat_no\":null,\"address\":null,\"city\":null,\"state\":null,\"postal_code\":null,\"country\":null,\"phone\":\"+971585280538\",\"email\":\"m.ali@nouvahub.com\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-27 07:56:17'),
(188, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"35\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Mohamed\",\"company\":\"Ultra3\",\"vat_no\":null,\"address\":\"Riyadh<br>\",\"city\":\"Riyadh\",\"state\":\"\",\"postal_code\":\"11351\",\"country\":\"SA\",\"phone\":\"+971585280538\",\"email\":\"msh@pharma.com.sa\",\"cf1\":null,\"cf2\":null,\"cf3\":null,\"cf4\":null,\"cf5\":null,\"cf6\":null,\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":null}}', '2022-12-27 07:56:17'),
(189, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"1\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Walk-in Customer\",\"company\":\"Walk-in Customer\",\"vat_no\":\"\",\"address\":\"Customer Address\",\"city\":\"Dammam\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"Saudia Arabia\",\"phone\":\"0123456789\",\"email\":\"customer@pharmacyherbel.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":null,\"price_group_name\":null,\"gst_no\":\"\"}}', '2022-12-27 07:56:17'),
(190, 'Supplier is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"5\",\"group_id\":\"4\",\"group_name\":\"supplier\",\"customer_group_id\":null,\"customer_group_name\":null,\"name\":\"Test Supplier 2\",\"company\":\"Test Supplier Company 2\",\"vat_no\":\"\",\"address\":\"2nd floor, test address\",\"city\":\"Jaddah\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"Saudia Arabia\",\"phone\":\"051321212\",\"email\":\"testsupplier2@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":null,\"price_group_name\":null,\"gst_no\":\"\"}}', '2022-12-27 11:44:21'),
(191, 'Supplier is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"4\",\"group_id\":\"4\",\"group_name\":\"supplier\",\"customer_group_id\":null,\"customer_group_name\":null,\"name\":\"Test Supplier 1\",\"company\":\"Test Supplier Company\",\"vat_no\":\"\",\"address\":\"first floor, test address\",\"city\":\"Riyadh\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"Saudi Arabia\",\"phone\":\"05112345657\",\"email\":\"testsupplier1@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":null,\"price_group_name\":null,\"gst_no\":\"\"}}', '2022-12-27 11:44:25'),
(192, 'Supplier is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"31\",\"group_id\":\"4\",\"group_name\":\"supplier\",\"customer_group_id\":null,\"customer_group_name\":null,\"name\":\"LAND PHARMA\",\"company\":\"LAND PHARMA\",\"vat_no\":\"\",\"address\":\"ITALY\",\"city\":\"ROME\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"00966566076544\",\"email\":\"AMREID857@GMAIL.COM\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":null,\"price_group_name\":null,\"gst_no\":\"\"}}', '2022-12-27 11:44:28'),
(193, 'Biller is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"3\",\"group_id\":null,\"group_name\":\"biller\",\"customer_group_id\":null,\"customer_group_name\":null,\"name\":\"Test Name\",\"company\":\"Test Biller Company\",\"vat_no\":\"5555\",\"address\":\"Biller adddress\",\"city\":\"Dammam\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"Saudia Arabia\",\"phone\":\"012345678\",\"email\":\"test@biller.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":\" Thank you for shopping with us. Please come again\",\"payment_term\":\"0\",\"logo\":\"logo2.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":null,\"price_group_name\":null,\"gst_no\":\"\"}}', '2022-12-27 11:44:42'),
(194, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"40\",\"code\":\"PDS004\",\"name\":\"SULFAD 1GM\",\"unit\":\"6\",\"cost\":\"115.0000\",\"price\":\"230.0000\",\"alert_quantity\":\"3000.0000\",\"image\":\"no_image.png\",\"category_id\":\"14\",\"subcategory_id\":null,\"cf1\":\"both\",\"cf2\":\"no\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"14236.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"57\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"6\",\"purchase_unit\":\"6\",\"brand\":\"3\",\"slug\":\"sulfad-1gm\",\"featured\":null,\"weight\":\"0.0000\",\"hsn_code\":null,\"views\":\"0\",\"hide\":\"0\",\"second_name\":\"ARTICHOKE,TURMERIC,LIQUORICE,MILK THISTLE\",\"hide_pos\":\"0\"}}', '2022-12-28 08:29:34'),
(195, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"58\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Aali Albahar Medical Store\",\"company\":\"Aali Albahar Medical Store\",\"vat_no\":\"3.10103E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:13:56'),
(196, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"59\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Abdul Karim Al Hakamy Pharmacy\",\"company\":\"Abdul Karim Al Hakamy Pharmacy\",\"vat_no\":\"3.00986E+14\",\"address\":\"??????? ????????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:13:56'),
(197, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"60\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Abdul Latif Jameel Hospital\",\"company\":\"Abdul Latif Jameel Hospital\",\"vat_no\":\"0\",\"address\":\"????? ? ??? 23341\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"23341\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:13:56'),
(198, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"61\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Abha Hospital\",\"company\":\"Abha Hospital\",\"vat_no\":\"3.10531E+14\",\"address\":\"??????? ????????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:13:56'),
(199, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"62\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Adam Medical Co.\",\"company\":\"Adam Medical Co.\",\"vat_no\":\"3.10094E+14\",\"address\":\"?????? 12811 2071???? ??? 2239\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"12811\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:13:56'),
(200, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"63\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Adel Pharmacies\",\"company\":\"Adel Pharmacies\",\"vat_no\":\"3.00419E+14\",\"address\":\"Qassim  ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:13:56'),
(201, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"64\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Advanced Medical University Pathways co\",\"company\":\"Advanced Medical University Pathways co\",\"vat_no\":\"3.11004E+14\",\"address\":\"Eastern Region??????? ???????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:13:56'),
(202, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"68\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Abeer international Medical Co\",\"company\":\"Al Abeer international Medical Co\",\"vat_no\":\"3.00209E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:13:56'),
(203, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"69\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Amal Polyclinic\",\"company\":\"Al Amal Polyclinic\",\"vat_no\":\"0\",\"address\":\"Yanbu - ???? ?????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:13:56'),
(204, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"70\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Atfeen Alhadisha Pharmacy\",\"company\":\"Al Atfeen Alhadisha Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:13:56'),
(205, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"71\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Badr  Pharmacy Co\",\"company\":\"Al Badr  Pharmacy Co\",\"vat_no\":\"3.00225E+14\",\"address\":\"Makkah  ??? ???????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"51870\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:05'),
(206, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"72\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Dawaa Medical Co Ltd\",\"company\":\"Al Dawaa Medical Co Ltd\",\"vat_no\":\"3.00545E+14\",\"address\":\"Al Khobar 31952 ?????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"31952\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:05'),
(207, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"73\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Emeis Pharmacies\",\"company\":\"Al Emeis Pharmacies\",\"vat_no\":\"3.10471E+14\",\"address\":\"???? ????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:05'),
(208, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"74\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Faraby Medical Center Company\",\"company\":\"Al Faraby Medical Center Company\",\"vat_no\":\"3.00566E+14\",\"address\":\"??????  Dammam\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"32257\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:05'),
(209, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"75\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Ghadeer Pharmacy\",\"company\":\"Al Ghadeer Pharmacy\",\"vat_no\":\"3.01371E+14\",\"address\":\"Dammam ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:05'),
(210, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"76\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Hammadi Hospital\",\"company\":\"Al Hammadi Hospital\",\"vat_no\":\"0\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:05'),
(211, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"77\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Hamra Hospital Pharmacy Drug Adv Co.\",\"company\":\"Al Hamra Hospital Pharmacy Drug Adv Co.\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:05'),
(212, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"78\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Hayat Alwatany Co\",\"company\":\"Al Hayat Alwatany Co\",\"vat_no\":\"3.0005E+14\",\"address\":\"Riyadh 11521 ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"11521\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:05'),
(213, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"79\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Hayat National Hospital Jizan\",\"company\":\"Al Hayat National Hospital Jizan\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:05'),
(214, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"80\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Jazea Medical Co\",\"company\":\"Al Jazea Medical Co\",\"vat_no\":\"3.00057E+14\",\"address\":\"?? ????? ???? ??? ?????? ????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"14266\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:05'),
(215, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"81\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Jazeera Pharmacy\",\"company\":\"Al Jazeera Pharmacy\",\"vat_no\":\"3.10225E+14\",\"address\":\"Khobar ????? ?????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"31952\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(216, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"82\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Jedaany Hospital\",\"company\":\"Al Jedaany Hospital\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(217, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"83\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al karama Pharmacy\",\"company\":\"Al karama Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(218, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"84\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Kordy pharmacy\",\"company\":\"Al Kordy pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(219, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"85\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Marja Medical Pharmacy\",\"company\":\"Al Marja Medical Pharmacy\",\"vat_no\":\"3.01284E+14\",\"address\":\"Jeddah ?? ??????? , ????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(220, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"86\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Mawared Medical Company\",\"company\":\"Al Mawared Medical Company\",\"vat_no\":\"3.10174E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(221, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"87\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Mohanna Pharmacies Group\",\"company\":\"Al Mohanna Pharmacies Group\",\"vat_no\":\"3.00848E+14\",\"address\":\"??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(222, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"88\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Mubarak Pharmacy\",\"company\":\"Al Mubarak Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(223, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"89\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Nahdi Medical Company\",\"company\":\"Al Nahdi Medical Company\",\"vat_no\":\"3.00172E+14\",\"address\":\"???? ???????, ??? 23715- 3985.\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"23715\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(224, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"90\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Namouzagia Al Tebiah Pharmacy\",\"company\":\"Al Namouzagia Al Tebiah Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(225, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"91\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Nasr Modern Trading Company\",\"company\":\"Al Nasr Modern Trading Company\",\"vat_no\":\"3.11204E+14\",\"address\":\"???  Makkah\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(226, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"92\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Noor Saudi Arabia\",\"company\":\"Al Noor Saudi Arabia\",\"vat_no\":\"123456789\",\"address\":\"Makkah ???\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(227, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"93\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Nour Pharmacy\",\"company\":\"Al Nour Pharmacy\",\"vat_no\":\"3.10868E+14\",\"address\":\"Dammam 31412 ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"31412\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(228, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"94\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Safa Almasiya Pharmacy\",\"company\":\"Al Safa Almasiya Pharmacy\",\"vat_no\":\"3.01304E+14\",\"address\":\"Hail ???? ?? ???????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"81415\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(229, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"95\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Salman Pharmacy\",\"company\":\"Al Salman Pharmacy\",\"vat_no\":\"3.00565E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(230, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"96\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Seha Alkhassa Pharmacy\",\"company\":\"Al Seha Alkhassa Pharmacy\",\"vat_no\":\"3.10415E+14\",\"address\":\"????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(231, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"97\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Shablan Medical Center\",\"company\":\"Al Shablan Medical Center\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(232, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"98\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Sobhiaa Trading Est\",\"company\":\"Al Sobhiaa Trading Est\",\"vat_no\":\"3.00014E+14\",\"address\":\"Riyadh\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(233, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"99\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Takhsusi Pharmacy\",\"company\":\"Al Takhsusi Pharmacy\",\"vat_no\":\"3.00039E+14\",\"address\":\"?????? Riyadh\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(234, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"100\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Tameez Pharmacy Taif Pharmaceuticals\",\"company\":\"Al Tameez Pharmacy Taif Pharmaceuticals\",\"vat_no\":\"3.00593E+14\",\"address\":\"Taif ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"39253\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(235, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"101\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Tawafeq Pharmacy Group\",\"company\":\"Al Tawafeq Pharmacy Group\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(236, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"102\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Wasfah Pharmacies\",\"company\":\"Al Wasfah Pharmacies\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(237, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"103\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Zahraa Hospital Dammam\",\"company\":\"Al Zahraa Hospital Dammam\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(238, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"104\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al Zuha Pharmacy\",\"company\":\"Al Zuha Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(239, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"65\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al-Hamaied Store.\",\"company\":\"Al-Hamaied Store.\",\"vat_no\":\"3.00264E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(240, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"66\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al-Salam Medical Comflex\",\"company\":\"Al-Salam Medical Comflex\",\"vat_no\":\"3.10877E+14\",\"address\":\"Jeddah ???\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(241, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"67\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Al-Salhi pharmacy\",\"company\":\"Al-Salhi pharmacy\",\"vat_no\":\"3.00518E+14\",\"address\":\"???????  Madinah\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(242, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"105\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Aldawaa al-naqi Pharmcy\",\"company\":\"Aldawaa al-naqi Pharmcy\",\"vat_no\":\"0\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(243, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"106\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Aleulyan Pharmacy\",\"company\":\"Aleulyan Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(244, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"107\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Alhabib Clinic\",\"company\":\"Alhabib Clinic\",\"vat_no\":\"3.10045E+14\",\"address\":\"Qassim   ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(245, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"108\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Ali Bin Ali Hospital\",\"company\":\"Ali Bin Ali Hospital\",\"vat_no\":\"3.10425E+14\",\"address\":\"?????? 14515 ?? ????????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"14515\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(246, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"109\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Alif Thimar Medical Surgical Co\",\"company\":\"Alif Thimar Medical Surgical Co\",\"vat_no\":\"1.00618E+14\",\"address\":\"Business Bay, Dubai\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"P.O. Box\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(247, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"110\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Allewa\\\"A Pharmacy\",\"company\":\"Allewa\\\"A Pharmacy\",\"vat_no\":\"3.11045E+14\",\"address\":\"HAYIL ????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"1133\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(248, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"111\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Almostaqbal Specialized Medical\",\"company\":\"Almostaqbal Specialized Medical\",\"vat_no\":\"3.10135E+14\",\"address\":\"Jeddah    ???\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"P.O.BOX4\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(249, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"112\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Alnnasir Pharmacy\",\"company\":\"Alnnasir Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(250, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"113\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Alomna Pharmacy\",\"company\":\"Alomna Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(251, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"114\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Alrahmah Medical Clinic\",\"company\":\"Alrahmah Medical Clinic\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(252, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"115\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Alseha W Dawaa Medical\",\"company\":\"Alseha W Dawaa Medical\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20');
INSERT INTO `sma_logs` (`id`, `detail`, `model`, `date`) VALUES
(253, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"116\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Alshafi Medical\",\"company\":\"Alshafi Medical\",\"vat_no\":\"3.02008E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"11323\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(254, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"117\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Altaeawun Warehouse\",\"company\":\"Altaeawun Warehouse\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(255, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"118\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Alwefaq Medical Pharmacy\",\"company\":\"Alwefaq Medical Pharmacy\",\"vat_no\":\"3.00026E+14\",\"address\":\"Riyadh 11438 ?????? ?? ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"11438\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(256, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"119\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Anas Pharmacy\",\"company\":\"Anas Pharmacy\",\"vat_no\":\"3.00538E+14\",\"address\":\"???? ????? 61411\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"61411\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(257, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"120\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Anaya Alsamo Pharmacy\",\"company\":\"Anaya Alsamo Pharmacy\",\"vat_no\":\"3.00017E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(258, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"121\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Andalusia Hospital\",\"company\":\"Andalusia Hospital\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(259, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"122\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Arkan Warehouse Medical\",\"company\":\"Arkan Warehouse Medical\",\"vat_no\":\"3.01116E+14\",\"address\":\"Riyadh\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(260, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"123\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Arrawdha General Hospital\",\"company\":\"Arrawdha General Hospital\",\"vat_no\":\"3.00596E+14\",\"address\":\"Dammam 31488 ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"31488\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(261, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"124\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Asas Alseha\",\"company\":\"Asas Alseha\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(262, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"125\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Asharq Alawsat Pharmacies Co\",\"company\":\"Asharq Alawsat Pharmacies Co\",\"vat_no\":\"3.00095E+14\",\"address\":\"???? 2480- ?????? 12214- 2480\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"12214\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(263, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"126\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Assaher Pharmacy\",\"company\":\"Assaher Pharmacy\",\"vat_no\":\"3.2003E+13\",\"address\":\"Riyadh  ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(264, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"127\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Aster Sanad Hospital\",\"company\":\"Aster Sanad Hospital\",\"vat_no\":\"3.00777E+14\",\"address\":\"Riyadh 13216 ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"13216\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(265, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"128\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Asya Pharmacy Hatim Trading\",\"company\":\"Asya Pharmacy Hatim Trading\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(266, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"129\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Azza pharmacy\",\"company\":\"Azza pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(267, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"130\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Balsam Medical Pharmacy\",\"company\":\"Balsam Medical Pharmacy\",\"vat_no\":\"3.00446E+14\",\"address\":\"Al Qatif 31452 ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"31452\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(268, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"131\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Balsam Tabuk Pharmacy\",\"company\":\"Balsam Tabuk Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(269, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"132\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Basel Store\",\"company\":\"Basel Store\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(270, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"133\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Basma Al Alam Pharmacy\",\"company\":\"Basma Al Alam Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(271, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"134\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Bet Albatarjee\",\"company\":\"Bet Albatarjee\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(272, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"135\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Blaad El-Afia Pharmacy\",\"company\":\"Blaad El-Afia Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(273, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"136\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Borg Alnokhba Pharmacy (Dawaa Algharbia)\",\"company\":\"Borg Alnokhba Pharmacy (Dawaa Algharbia)\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(274, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"137\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Buqshan Hospital\",\"company\":\"Buqshan Hospital\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(275, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"138\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Care Pharmaceutical\",\"company\":\"Care Pharmaceutical\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(276, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"139\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Cash\",\"company\":\"Cash\",\"vat_no\":\"0\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(277, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"140\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Charisma Co.\",\"company\":\"Charisma Co.\",\"vat_no\":\"3.10135E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"11323\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(278, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"141\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Community Health Medical Co.\",\"company\":\"Community Health Medical Co.\",\"vat_no\":\"3.10387E+14\",\"address\":\"????  Yanbu\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"P.O.BOX \",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(279, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"142\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Dalil Al Dawa Pharmacy\",\"company\":\"Dalil Al Dawa Pharmacy\",\"vat_no\":\"123456789\",\"address\":\"??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(280, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"143\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Dallah Hospital\",\"company\":\"Dallah Hospital\",\"vat_no\":\"3.00049E+14\",\"address\":\"Riyadh 12381 ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"12381\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(281, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"144\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Dallah Nemar\",\"company\":\"Dallah Nemar\",\"vat_no\":\"3.00049E+14\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(282, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"145\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Dar Alasnad Medical\",\"company\":\"Dar Alasnad Medical\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(283, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"146\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Dawa Afifa Pharmacy\",\"company\":\"Dawa Afifa Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(284, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"147\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Dawa Al Salamah Pharmacy\",\"company\":\"Dawa Al Salamah Pharmacy\",\"vat_no\":\"3.00931E+14\",\"address\":\"Najran\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(285, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"148\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Dawa Al Tamaiz Trading Co\",\"company\":\"Dawa Al Tamaiz Trading Co\",\"vat_no\":\"0\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(286, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"149\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Dawaa Al Asr Altibiih\",\"company\":\"Dawaa Al Asr Altibiih\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(287, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"150\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Dawaa Al Safwa Alola\",\"company\":\"Dawaa Al Safwa Alola\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(288, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"151\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Dawaa Alaasemah\",\"company\":\"Dawaa Alaasemah\",\"vat_no\":\"123456798\",\"address\":\"Riyadh\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(289, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"152\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Deryaq Drug Store\",\"company\":\"Deryaq Drug Store\",\"vat_no\":\"3.00608E+14\",\"address\":\"Khamish Musait\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(290, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"153\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Dr Abdulrahman Al Mishari Hospital\",\"company\":\"Dr Abdulrahman Al Mishari Hospital\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(291, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"154\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Dr Mahasen Medical Complex\",\"company\":\"Dr Mahasen Medical Complex\",\"vat_no\":\"3.00428E+14\",\"address\":\"Qassim ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(292, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"155\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Dr Mahmoud Farag\",\"company\":\"Dr Mahmoud Farag\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(293, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"156\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Eilaj almujamaeih pharmacy\",\"company\":\"Eilaj almujamaeih pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(294, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"157\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Elite Medical Center\",\"company\":\"Elite Medical Center\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(295, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"158\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Enayt Alhowra Medical Complex\",\"company\":\"Enayt Alhowra Medical Complex\",\"vat_no\":\"0\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(296, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"159\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Exceer Pharmacies\",\"company\":\"Exceer Pharmacies\",\"vat_no\":\"0\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(297, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"160\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Factory Pharmacy\",\"company\":\"Factory Pharmacy\",\"vat_no\":\"3.00031E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(298, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"161\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Fahad Al-Awaji Pharmacy\",\"company\":\"Fahad Al-Awaji Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(299, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"162\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Fahd Aleawaja Pharmacy\",\"company\":\"Fahd Aleawaja Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(300, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"163\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"First Clinic\",\"company\":\"First Clinic\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(301, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"164\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Future Medical Company Ltd\",\"company\":\"Future Medical Company Ltd\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(302, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"165\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Ghaya Pharmacy\",\"company\":\"Ghaya Pharmacy\",\"vat_no\":\"0\",\"address\":\"Riyadh - ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(303, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"166\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Ghodaf Pharmacies\",\"company\":\"Ghodaf Pharmacies\",\"vat_no\":\"0\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(304, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"167\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Greens Corners Medical .CO\",\"company\":\"Greens Corners Medical .CO\",\"vat_no\":\"3.11187E+14\",\"address\":\"Riyadh  ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(305, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"168\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Hai Algama Hospital\",\"company\":\"Hai Algama Hospital\",\"vat_no\":\"3.00241E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"40301668\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(306, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"169\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Health and Care Company\",\"company\":\"Health and Care Company\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(307, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"170\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Health Gate Pharmacy\",\"company\":\"Health Gate Pharmacy\",\"vat_no\":\"3.01202E+14\",\"address\":\"Khamis Mushait\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(308, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"171\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Health House Pharmacy\",\"company\":\"Health House Pharmacy\",\"vat_no\":\"3.00256E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(309, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"172\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"HERAA MEDICAL\",\"company\":\"HERAA MEDICAL\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(310, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"173\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"International Medical Center\",\"company\":\"International Medical Center\",\"vat_no\":\"3.00189E+14\",\"address\":\"??? Jeddah\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"21451\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(311, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"174\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Islam Ali Virus zero\",\"company\":\"Islam Ali Virus zero\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(312, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"175\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Jeddah Medical Medicine Company\",\"company\":\"Jeddah Medical Medicine Company\",\"vat_no\":\"3.1037E+14\",\"address\":\"Jeddah ???\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(313, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"176\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"KasAlraeayuh Altibiuh\",\"company\":\"KasAlraeayuh Altibiuh\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(314, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"177\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Kinda Medical Company\",\"company\":\"Kinda Medical Company\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(315, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"178\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"king Abdul Aziz University Hospital\",\"company\":\"king Abdul Aziz University Hospital\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(316, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"179\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"King Khalid University Hosp.\",\"company\":\"King Khalid University Hosp.\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:20'),
(317, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"180\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"King Saud Medical City\",\"company\":\"King Saud Medical City\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(318, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"181\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Kingdom Hospital\",\"company\":\"Kingdom Hospital\",\"vat_no\":\"3.00048E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(319, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"182\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Lemon Pharmacies\",\"company\":\"Lemon Pharmacies\",\"vat_no\":\"3.10139E+14\",\"address\":\"Riyadh\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"2569 ON \",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(320, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"183\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Lialaa Alsahih for Medicines\",\"company\":\"Lialaa Alsahih for Medicines\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(321, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"184\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Life Lines Medical Co Ltd\",\"company\":\"Life Lines Medical Co Ltd\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21');
INSERT INTO `sma_logs` (`id`, `detail`, `model`, `date`) VALUES
(322, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"185\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Lights Pharmacy\",\"company\":\"Lights Pharmacy\",\"vat_no\":\"123456798\",\"address\":\"Riyadh\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(323, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"186\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Lualiwuh Alfstat Pharmacy\",\"company\":\"Lualiwuh Alfstat Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(324, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"187\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Luluah Al Zuha (1) Pharmacy\",\"company\":\"Luluah Al Zuha (1) Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(325, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"188\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Makkah Medical Center\",\"company\":\"Makkah Medical Center\",\"vat_no\":\"0\",\"address\":\"??? ???????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(326, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"189\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Malak Pharmacy\",\"company\":\"Malak Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(327, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"190\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Manba Al Shifa Warehosue\",\"company\":\"Manba Al Shifa Warehosue\",\"vat_no\":\"0\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(328, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"191\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Mansour Rabia Medical Company\",\"company\":\"Mansour Rabia Medical Company\",\"vat_no\":\"3.00785E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"11332\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(329, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"192\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Mansour Rabie Medical Company\",\"company\":\"Mansour Rabie Medical Company\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(330, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"193\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Markaz Al Warood Al Tibbi\",\"company\":\"Markaz Al Warood Al Tibbi\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(331, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"194\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Masarat Al Gamaa Medical Co\",\"company\":\"Masarat Al Gamaa Medical Co\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(332, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"195\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Mazallat Al Dawa Pharmacy\",\"company\":\"Mazallat Al Dawa Pharmacy\",\"vat_no\":\"3.10057E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(333, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"196\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Mazen Pharmacy\",\"company\":\"Mazen Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(334, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"197\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Medical Division for trading Est.\",\"company\":\"Medical Division for trading Est.\",\"vat_no\":\"3.0003E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"11351\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(335, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"198\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Medical store\",\"company\":\"Medical store\",\"vat_no\":\"3.00202E+14\",\"address\":\"Makkah ??? ???????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(336, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"199\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Mesk Health Pharmacy\",\"company\":\"Mesk Health Pharmacy\",\"vat_no\":\"3.11154E+14\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(337, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"200\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Ministry of Health\",\"company\":\"Ministry of Health\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(338, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"201\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Ministry of National Guard\",\"company\":\"Ministry of National Guard\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(339, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"202\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Moaaz Medical Pharmacy\",\"company\":\"Moaaz Medical Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(340, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"203\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Mohamed Abdullah Alkhamis Pharmacy\",\"company\":\"Mohamed Abdullah Alkhamis Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(341, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"204\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"More Care Medical Est.\",\"company\":\"More Care Medical Est.\",\"vat_no\":\"3.10088E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(342, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"205\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Mouwasat Medical Services Co.\",\"company\":\"Mouwasat Medical Services Co.\",\"vat_no\":\"3.00507E+14\",\"address\":\"PO Box 282, Dammam 31411\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(343, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"206\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Musharraf Al Omari Trading\",\"company\":\"Musharraf Al Omari Trading\",\"vat_no\":\"3.00832E+14\",\"address\":\"Asir ????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(344, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"207\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"My Family  Pharmacy\",\"company\":\"My Family  Pharmacy\",\"vat_no\":\"3.00106E+14\",\"address\":\"JEDDAH ???\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"21411\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(345, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"208\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Najam Al Jazirah\",\"company\":\"Najam Al Jazirah\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(346, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"209\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Najd Consulting Hospital\",\"company\":\"Najd Consulting Hospital\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(347, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"210\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Naqaa United Pharmaceutical Co\",\"company\":\"Naqaa United Pharmaceutical Co\",\"vat_no\":\"3.10702E+14\",\"address\":\"Riyadh 7896 ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"12711\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(348, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"211\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"National Guard\",\"company\":\"National Guard\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(349, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"212\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"National Medical Care\",\"company\":\"National Medical Care\",\"vat_no\":\"3.00055E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(350, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"213\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Nawfiz najid Pharmacy\",\"company\":\"Nawfiz najid Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(351, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"214\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"New Jeddah Clinic Hospital\",\"company\":\"New Jeddah Clinic Hospital\",\"vat_no\":\"3.01206E+14\",\"address\":\"Jeddah ???\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"21472\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(352, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"215\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Noura Pharmacy\",\"company\":\"Noura Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(353, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"216\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Nukhab Al Amakin AlTibiya Pharmacy\",\"company\":\"Nukhab Al Amakin AlTibiya Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(354, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"217\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Nukhab Al Amakin AlTibiya Pharmacy Co\",\"company\":\"Nukhab Al Amakin AlTibiya Pharmacy Co\",\"vat_no\":\"3.10908E+14\",\"address\":\"Makkah ???\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(355, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"218\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"NUPCO\",\"company\":\"NUPCO\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(356, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"219\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"NUPCO MARKET PLACE\",\"company\":\"NUPCO MARKET PLACE\",\"vat_no\":\"3.00063E+14\",\"address\":\"Al Wurud District Riyadh 11323\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"11323\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(357, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"220\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Olaya Medical Center Pharmacy\",\"company\":\"Olaya Medical Center Pharmacy\",\"vat_no\":\"3.00054E+14\",\"address\":\"Riyadh\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(358, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"221\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Orange Pharmacy\",\"company\":\"Orange Pharmacy\",\"vat_no\":\"3.00026E+14\",\"address\":\"Riyadh 11381 ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"11381\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(359, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"222\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Pathways University Medical Company\",\"company\":\"Pathways University Medical Company\",\"vat_no\":\"3.10087E+14\",\"address\":\"Eastern Region ??????? ???????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"31951\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(360, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"223\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Pharma Home\",\"company\":\"Pharma Home\",\"vat_no\":\"0\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(361, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"224\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Pharma Industrial Factory\",\"company\":\"Pharma Industrial Factory\",\"vat_no\":\"3.02003E+14\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(362, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"225\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Pharmaceutical House Company\",\"company\":\"Pharmaceutical House Company\",\"vat_no\":\"3.00051E+14\",\"address\":\"?????? Riyadh\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"12794\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(363, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"226\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Pharmacy Origins Treatmen\",\"company\":\"Pharmacy Origins Treatmen\",\"vat_no\":\"3.00879E+14\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"62622\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(364, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"227\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Prince Sultan Medical Militrary City\",\"company\":\"Prince Sultan Medical Militrary City\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(365, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"228\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Qaleat Aldawa Pharmacy\",\"company\":\"Qaleat Aldawa Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(366, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"229\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Qamar Al Aroba Pharmacies\",\"company\":\"Qamar Al Aroba Pharmacies\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(367, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"230\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Ramaz Alamanh\",\"company\":\"Ramaz Alamanh\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(368, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"231\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Ramy Pharmacy\",\"company\":\"Ramy Pharmacy\",\"vat_no\":\"0\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(369, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"232\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Rashed Al Hassani Medical Group\",\"company\":\"Rashed Al Hassani Medical Group\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(370, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"233\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Rashid Medical Complex\",\"company\":\"Rashid Medical Complex\",\"vat_no\":\"3.00972E+14\",\"address\":\"?????? ????? Qassim\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(371, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"234\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Rawabi Alamal Company\",\"company\":\"Rawabi Alamal Company\",\"vat_no\":\"3.10581E+14\",\"address\":\"Jeddah ???\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(372, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"235\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Rawabi Alamal Subagent\",\"company\":\"Rawabi Alamal Subagent\",\"vat_no\":\"3.10581E+14\",\"address\":\"Jeddah ???.\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(373, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"236\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Rawasi Taibah Trading Co.\",\"company\":\"Rawasi Taibah Trading Co.\",\"vat_no\":\"3.10804E+14\",\"address\":\"Makkah ???\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(374, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"237\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Rehab Health Pharmacy\",\"company\":\"Rehab Health Pharmacy\",\"vat_no\":\"0\",\"address\":\"??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(375, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"238\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Retaj Al Seha Co.\",\"company\":\"Retaj Al Seha Co.\",\"vat_no\":\"3.00213E+14\",\"address\":\"?????? Riyadh\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"11662\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(376, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"239\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Riyadh Care Hospital\",\"company\":\"Riyadh Care Hospital\",\"vat_no\":\"3.00055E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(377, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"240\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Riyadh National Hospital\",\"company\":\"Riyadh National Hospital\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(378, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"241\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Riyadh Pharmacies\",\"company\":\"Riyadh Pharmacies\",\"vat_no\":\"3.00035E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(379, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"243\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Rokn Al Sahafa Pharmacy Co.\",\"company\":\"Rokn Al Sahafa Pharmacy Co.\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(380, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"242\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Rokn Al-Hakim\",\"company\":\"Rokn Al-Hakim\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(381, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"244\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Rowaa Medical Pharmacy\",\"company\":\"Rowaa Medical Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(382, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"245\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Rraghad  Pharmacy\",\"company\":\"Rraghad  Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(383, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"246\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Sada Medical Care Company\",\"company\":\"Sada Medical Care Company\",\"vat_no\":\"3.10221E+14\",\"address\":\"Riyadh  ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(384, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"247\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Safa Alorobah Warehouse\",\"company\":\"Safa Alorobah Warehouse\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(385, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"248\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Salaamatak Pharmacy\",\"company\":\"Salaamatak Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(386, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"249\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Salamat Buraydah Polyclinic\",\"company\":\"Salamat Buraydah Polyclinic\",\"vat_no\":\"3.00487E+14\",\"address\":\"Buraidah ?????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(387, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"250\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Samir Abbas Medical Center\",\"company\":\"Samir Abbas Medical Center\",\"vat_no\":\"3.10095E+14\",\"address\":\"Jeddah  ???\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"23411\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(388, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"251\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Sanabel Al Dawa Medical Co\",\"company\":\"Sanabel Al Dawa Medical Co\",\"vat_no\":\"3.01229E+14\",\"address\":\"Eastern Region ??????? ???????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(389, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"252\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Saudi German Hosp\",\"company\":\"Saudi German Hosp\",\"vat_no\":\"3.00098E+14\",\"address\":\"?? ???????  ??? Jeddah 23521\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"23521\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21');
INSERT INTO `sma_logs` (`id`, `detail`, `model`, `date`) VALUES
(390, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"253\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Saudi German Hosp Aseer\",\"company\":\"Saudi German Hosp Aseer\",\"vat_no\":\"3.00098E+14\",\"address\":\"Aseer ????, Khamis Mushait\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(391, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"254\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Saudi German Hosp Dammam\",\"company\":\"Saudi German Hosp Dammam\",\"vat_no\":\"3.10135E+14\",\"address\":\"Dammam  ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"P.O.BOX4\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(392, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"255\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Saudi German Hosp Madinah\",\"company\":\"Saudi German Hosp Madinah\",\"vat_no\":\"0\",\"address\":\"Madina ??????? ???????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(393, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"256\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Saudi German Hosp Riyadh\",\"company\":\"Saudi German Hosp Riyadh\",\"vat_no\":\"3.00098E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(394, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"257\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Shams Al-Ruqaiya Pharmacy\",\"company\":\"Shams Al-Ruqaiya Pharmacy\",\"vat_no\":\"3.00099E+14\",\"address\":\"Makkah ???\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(395, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"258\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Shifa Al Seha\",\"company\":\"Shifa Al Seha\",\"vat_no\":\"3.00597E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(396, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"259\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Shifa Al Tayseer Pharmacy\",\"company\":\"Shifa Al Tayseer Pharmacy\",\"vat_no\":\"3.10145E+14\",\"address\":\"Riyadh\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(397, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"260\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Shifa Tayba Pharmacy\",\"company\":\"Shifa Tayba Pharmacy\",\"vat_no\":\"0\",\"address\":\"MADina  ???????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(398, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"261\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Smart Apple Pharmaceutical Company\",\"company\":\"Smart Apple Pharmaceutical Company\",\"vat_no\":\"3.11382E+14\",\"address\":\"?????? Riyadh\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(399, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"262\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Smart Stores Company for Drug Limited\",\"company\":\"Smart Stores Company for Drug Limited\",\"vat_no\":\"3.01232E+14\",\"address\":\"???? ??? 466 4031 - 12284 ????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"12284\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(400, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"263\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Smou United Medical Company\",\"company\":\"Smou United Medical Company\",\"vat_no\":\"3.11199E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(401, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"264\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Sulaymaniyah Pharmacy\",\"company\":\"Sulaymaniyah Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(402, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"265\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Suliman Al Fakeeh Hosp.\",\"company\":\"Suliman Al Fakeeh Hosp.\",\"vat_no\":\"3.00817E+14\",\"address\":\"??? Jeddah\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"21461\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(403, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"266\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Tabay Pharmacy Group\",\"company\":\"Tabay Pharmacy Group\",\"vat_no\":\"3.00421E+14\",\"address\":\"?????? ?????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(404, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"267\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"TADAWI\",\"company\":\"TADAWI\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(405, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"268\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"TADAWI Pharmacy\",\"company\":\"TADAWI Pharmacy\",\"vat_no\":\"3.01288E+14\",\"address\":\"Jeddah  ???\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"??????  \",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(406, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"269\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Tadawina Pharmacy\",\"company\":\"Tadawina Pharmacy\",\"vat_no\":\"3.10121E+14\",\"address\":\"?????? Al-Qassim\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(407, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"270\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Taj Al Dawa For Trading\",\"company\":\"Taj Al Dawa For Trading\",\"vat_no\":\"3.00017E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(408, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"271\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Teef Al Mansiyah\",\"company\":\"Teef Al Mansiyah\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(409, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"272\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Thanaya Aldwaa\",\"company\":\"Thanaya Aldwaa\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(410, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"273\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Top Medical Excellence Est\",\"company\":\"Top Medical Excellence Est\",\"vat_no\":\"3.10088E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(411, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"274\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Ultra Medical Hub\",\"company\":\"Ultra Medical Hub\",\"vat_no\":\"0\",\"address\":\"???  Dubai\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(412, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"275\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Ultra Smooth Trading Est.\",\"company\":\"Ultra Smooth Trading Est.\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(413, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"276\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"United Pharmaceutical\",\"company\":\"United Pharmaceutical\",\"vat_no\":\"3.00212E+14\",\"address\":\"Jeddah     ???\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(414, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"277\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Walid Muhammad Ghazzawi Medical Pharmacy\",\"company\":\"Walid Muhammad Ghazzawi Medical Pharmacy\",\"vat_no\":\"3.10371E+14\",\"address\":\"Makkah ??? ???????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"24232\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(415, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"278\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Waqiya W Ilaj\",\"company\":\"Waqiya W Ilaj\",\"vat_no\":\"3.10791E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(416, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"279\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Wardah Aldawaa Pharmcy\",\"company\":\"Wardah Aldawaa Pharmcy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(417, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"280\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Wassafat  Alalshafa Pharmacy\",\"company\":\"Wassafat  Alalshafa Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(418, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"281\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Wassafat Al Seha Pharmacy\",\"company\":\"Wassafat Al Seha Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(419, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"282\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Week Aldiwa Pharmacy\",\"company\":\"Week Aldiwa Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(420, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"283\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Week Pharmacy\",\"company\":\"Week Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(421, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"284\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Yanbu National Pharmacy\",\"company\":\"Yanbu National Pharmacy\",\"vat_no\":\"3.11092E+14\",\"address\":\"???? ????????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(422, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"285\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Zad AlDawaa Pharmacy\",\"company\":\"Zad AlDawaa Pharmacy\",\"vat_no\":\"3.10231E+14\",\"address\":\"Makkah   ???\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(423, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"286\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Zad Alteb Co.\",\"company\":\"Zad Alteb Co.\",\"vat_no\":\"3.11224E+14\",\"address\":\"???? ????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(424, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"287\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Zahrat Al Amal Pharmacy\",\"company\":\"Zahrat Al Amal Pharmacy\",\"vat_no\":\"\",\"address\":\"NA\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(425, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"288\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Zahrat Al Rowdah Pharmacies\",\"company\":\"Zahrat Al Rowdah Pharmacies\",\"vat_no\":\"0\",\"address\":\"?????? 11352 ?????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"11352\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(426, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"289\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Zahrat Lemar Medical.Co\",\"company\":\"Zahrat Lemar Medical.Co\",\"vat_no\":\"3.00807E+14\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(427, 'Customer is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"290\",\"group_id\":\"3\",\"group_name\":\"customer\",\"customer_group_id\":\"1\",\"customer_group_name\":\"General\",\"name\":\"Zahret Al Amged Warehouse\",\"company\":\"Zahret Al Amged Warehouse\",\"vat_no\":\"0\",\"address\":\"Riyadh ??????\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"\",\"phone\":\"NA\",\"email\":\"test@gmail.com\",\"cf1\":\"\",\"cf2\":\"\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"invoice_footer\":null,\"payment_term\":\"0\",\"logo\":\"logo.png\",\"award_points\":\"0\",\"deposit_amount\":null,\"price_group_id\":\"1\",\"price_group_name\":\"Default\",\"gst_no\":\"\"}}', '2022-12-28 09:14:21'),
(428, 'Product is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"44\",\"code\":\"test441\",\"name\":\"Test\",\"unit\":\"6\",\"cost\":\"1456.0000\",\"price\":\"2912.0000\",\"alert_quantity\":\"20.0000\",\"image\":\"no_image.png\",\"category_id\":\"15\",\"subcategory_id\":null,\"cf1\":\"6\",\"cf2\":\"yes\",\"cf3\":\"\",\"cf4\":\"\",\"cf5\":\"\",\"cf6\":\"\",\"quantity\":\"0.0000\",\"tax_rate\":\"1\",\"track_quantity\":\"1\",\"details\":\"\",\"warehouse\":null,\"barcode_symbology\":\"code128\",\"file\":\"\",\"product_details\":\"\",\"tax_method\":\"1\",\"type\":\"standard\",\"supplier1\":\"57\",\"supplier1price\":null,\"supplier2\":null,\"supplier2price\":null,\"supplier3\":null,\"supplier3price\":null,\"supplier4\":null,\"supplier4price\":null,\"supplier5\":null,\"supplier5price\":null,\"promotion\":null,\"promo_price\":null,\"start_date\":null,\"end_date\":null,\"supplier1_part_no\":\"\",\"supplier2_part_no\":null,\"supplier3_part_no\":null,\"supplier4_part_no\":null,\"supplier5_part_no\":null,\"sale_unit\":\"0\",\"purchase_unit\":\"0\",\"brand\":\"3\",\"slug\":\"test\",\"featured\":\"1\",\"weight\":\"0.0000\",\"hsn_code\":null,\"views\":\"1\",\"hide\":\"0\",\"second_name\":\"\",\"hide_pos\":\"0\"}}', '2022-12-30 12:32:46'),
(429, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"93\",\"date\":\"2022-12-30 15:31:58\",\"reference_no\":\"SALE2022\\/12\\/0076\",\"customer_id\":\"0\",\"customer\":\"Anus Ahmad\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"230.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"230.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"87\",\"reserve_id\":null,\"hash\":\"d0f56453809564d59f85eb1f4c101b549a3454ee0cc1090ff6668a98ec3702fe\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-30 12:33:52'),
(430, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"92\",\"date\":\"2022-12-30 15:28:00\",\"reference_no\":\"SALE2022\\/12\\/0075\",\"customer_id\":\"0\",\"customer\":\"Mujtaba Sarwar\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"0.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"0.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"0\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"86\",\"reserve_id\":null,\"hash\":\"aee11374563f12873bcb86f2fa3d0baadf47f13f36652ada0d87dd8ff3be3059\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-30 12:33:52'),
(431, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"91\",\"date\":\"2022-12-30 15:21:37\",\"reference_no\":\"SALE2022\\/12\\/0074\",\"customer_id\":\"0\",\"customer\":\"Mujtaba Sarwar\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"230.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"230.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"85\",\"reserve_id\":null,\"hash\":\"0562d55cad41305b574f53c11aabe2ea7cd4dfae3027a5c73c9e4371db4d89b6\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-30 12:33:52'),
(432, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"100\",\"date\":\"2022-12-30 15:52:36\",\"reference_no\":\"SALE2022\\/12\\/0083\",\"customer_id\":\"0\",\"customer\":\"Anus Ahmad\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"0.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"0.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"0\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"94\",\"reserve_id\":null,\"hash\":\"d8d3a60be72f90d1d480df06c721f5e2485a34a8b39a440bec6fc14087485aa5\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-30 12:53:14'),
(433, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"99\",\"date\":\"2022-12-30 15:46:33\",\"reference_no\":\"SALE2022\\/12\\/0082\",\"customer_id\":\"0\",\"customer\":\"Anus Ahmad\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"0.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"0.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"0\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"93\",\"reserve_id\":null,\"hash\":\"030170c6837c67753139fd03e5ccdfe5ff25a4382645e586be0a5ecd34facf1e\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-30 12:53:14'),
(434, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"98\",\"date\":\"2022-12-30 15:45:20\",\"reference_no\":\"SALE2022\\/12\\/0081\",\"customer_id\":\"0\",\"customer\":\"Anus Ahmad\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"0.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"0.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"0\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"92\",\"reserve_id\":null,\"hash\":\"a921638df8a8c23ec3a68df5aa1d52dea297b2ab49a756de48338f2cad5eed0a\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-30 12:53:14'),
(435, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"97\",\"date\":\"2022-12-30 15:44:28\",\"reference_no\":\"SALE2022\\/12\\/0080\",\"customer_id\":\"0\",\"customer\":\"Anus Ahmad\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"0.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"0.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"0\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"91\",\"reserve_id\":null,\"hash\":\"ba351d53bd7157d4c84c34cd9905501e96ee517e422639991be032fb9d076c05\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-30 12:53:14'),
(436, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"96\",\"date\":\"2022-12-30 15:43:35\",\"reference_no\":\"SALE2022\\/12\\/0079\",\"customer_id\":\"0\",\"customer\":\"Anus Ahmad\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"0.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"0.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"0\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"90\",\"reserve_id\":null,\"hash\":\"0782027f186bc41dcd1660d076003c3eff7d884504befc9401042432bc61323e\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-30 12:53:14'),
(437, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"95\",\"date\":\"2022-12-30 15:42:11\",\"reference_no\":\"SALE2022\\/12\\/0078\",\"customer_id\":\"0\",\"customer\":\"Anus Ahmad\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"0.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"0.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"0\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"89\",\"reserve_id\":null,\"hash\":\"48d0c9a20cb8fc36b7f34dacd1aa9a5d6046ff5993d62650ae4ceb0b5c939059\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-30 12:53:14'),
(438, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"94\",\"date\":\"2022-12-30 15:34:37\",\"reference_no\":\"SALE2022\\/12\\/0077\",\"customer_id\":\"0\",\"customer\":\"Anus Ahmad\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"0.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"0.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":\"1\",\"updated_by\":null,\"updated_at\":null,\"total_items\":\"0\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"88\",\"reserve_id\":null,\"hash\":\"a70a8e8ec6b869d679bde453834d0e4661a30c880a3625a01792b26cfebf8a33\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-30 12:53:14'),
(439, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"102\",\"date\":\"2022-12-30 15:57:35\",\"reference_no\":\"SALE2022\\/12\\/0085\",\"customer_id\":\"0\",\"customer\":\"Anus Ahmad\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"0.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"0.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"0\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"96\",\"reserve_id\":null,\"hash\":\"867d5652c83aede6f7f21829e3e9ac77d086c2ca299d8e2bd0ba9de96eaa3bd1\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-30 13:00:48'),
(440, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"101\",\"date\":\"2022-12-30 15:55:59\",\"reference_no\":\"SALE2022\\/12\\/0084\",\"customer_id\":\"0\",\"customer\":\"Anus Ahmad\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"230.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"230.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"95\",\"reserve_id\":null,\"hash\":\"f3e97e38d3ec2f1790ed04f4504ead2f1802f2b53feb5d1a04c63e96080a0e84\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-30 13:00:48'),
(441, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"105\",\"date\":\"2022-12-30 16:08:18\",\"reference_no\":\"SALE2022\\/12\\/0088\",\"customer_id\":\"525\",\"customer\":\"Pharma Drug Store\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"0.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"0.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"0\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"99\",\"reserve_id\":null,\"hash\":\"d79bf919411649fa9e88c6199a0fe5b50216158058b5bfed3435a2cac0feeec0\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-30 13:08:39'),
(442, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"104\",\"date\":\"2022-12-30 16:04:42\",\"reference_no\":\"SALE2022\\/12\\/0087\",\"customer_id\":\"525\",\"customer\":\"Pharma Drug Store\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"230.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"230.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"98\",\"reserve_id\":null,\"hash\":\"4b3e735a1cb76ffdc476b7fb94f2e0042ce1b40616e3eeb93fea5325cc8addf5\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-30 13:08:39'),
(443, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"103\",\"date\":\"2022-12-30 16:01:55\",\"reference_no\":\"SALE2022\\/12\\/0086\",\"customer_id\":\"0\",\"customer\":\"Anus Ahmad\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"230.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"230.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"97\",\"reserve_id\":null,\"hash\":\"aed1489ef208ede2063c01428a6d5b5bb0b5f6a6fbda5a4ef895ffdc01e2fa0b\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-30 13:08:39'),
(444, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"108\",\"date\":\"2022-12-30 16:41:38\",\"reference_no\":\"SALE2022\\/12\\/0091\",\"customer_id\":\"526\",\"customer\":\"Pharma Drug Store\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"230.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"230.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"102\",\"reserve_id\":null,\"hash\":\"22882b4f80809a41f6cc40f7c0cb92882f5ac9704c94f062ce92e14fae836d3e\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-30 13:56:04');
INSERT INTO `sma_logs` (`id`, `detail`, `model`, `date`) VALUES
(445, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"107\",\"date\":\"2022-12-30 16:14:15\",\"reference_no\":\"SALE2022\\/12\\/0090\",\"customer_id\":\"525\",\"customer\":\"Pharma Drug Store\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"230.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"230.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"101\",\"reserve_id\":null,\"hash\":\"7ca0db2335b4bcfba0783d4488efd1a8c1a8c8d4ed15598875c3d06f794cb7d6\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-30 13:56:04'),
(446, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"106\",\"date\":\"2022-12-30 16:09:22\",\"reference_no\":\"SALE2022\\/12\\/0089\",\"customer_id\":\"525\",\"customer\":\"Pharma Drug Store\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"230.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"230.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"100\",\"reserve_id\":null,\"hash\":\"b67ff330d6574a581b79eeacf20a02970a9bad51ebce990610d804fc8f28021e\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-30 13:56:04'),
(447, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"109\",\"date\":\"2022-12-30 16:57:01\",\"reference_no\":\"SALE2022\\/12\\/0092\",\"customer_id\":\"525\",\"customer\":\"Pharma Drug Store\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"230.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"230.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":\"http:\\/\\/www.sandbox.aramex.com\\/content\\/rpt_cache\\/bacac9c\",\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"103\",\"reserve_id\":null,\"hash\":\"6ae4a4100835ecad6a3d13aba80b82554309b711dfc209f2e56edd7f7b93a3ef\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2022-12-30 14:06:33'),
(448, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"110\",\"date\":\"2022-12-30 17:07:16\",\"reference_no\":\"SALE2022\\/12\\/0093\",\"customer_id\":\"525\",\"customer\":\"Pharma Drug Store\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"230.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"230.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":\"http:\\/\\/www.sandbox.aramex.com\\/content\\/rpt_cache\\/ea0d5889e1af4d19b5dc13f9d871d57b.pdf\",\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"104\",\"reserve_id\":null,\"hash\":\"3f5f8b609d8eddc412c2a57e47d5f0f0d3d09058e3534b8580d9eddf6ef70812\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2023-01-09 09:59:31'),
(449, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":false,\"items\":null}', '2023-01-09 09:59:39'),
(450, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":false,\"items\":null}', '2023-01-09 09:59:53'),
(451, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":false,\"items\":null}', '2023-01-09 10:00:00'),
(452, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":false,\"items\":null}', '2023-01-09 10:00:18'),
(453, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"111\",\"date\":\"2022-12-30 17:10:59\",\"reference_no\":\"SALE2022\\/12\\/0094\",\"customer_id\":\"525\",\"customer\":\"Pharma Drug Store\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"230.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"0.0000\",\"grand_total\":\"230.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":\"http:\\/\\/www.sandbox.aramex.com\\/content\\/rpt_cache\\/f4711dd3392346f3a71fd4dd2432fb82.pdf\",\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"105\",\"reserve_id\":null,\"hash\":\"0b0906ff89208ae9fdaef33007659380202c0ed4f9a93c71490cd25357f52237\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2023-01-09 10:00:45'),
(454, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":false,\"items\":null}', '2023-01-09 10:01:05'),
(455, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"115\",\"date\":\"2023-01-03 15:16:39\",\"reference_no\":\"SALE2023\\/01\\/0098\",\"customer_id\":\"526\",\"customer\":\"Pharma Drug Store\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"230.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"24.0000\",\"grand_total\":\"254.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"109\",\"reserve_id\":null,\"hash\":\"ce9c3f690f4b8b701a680ded7843b610fdb47f44fd21c3ddcfd517aefaebb9ec\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2023-01-09 10:06:51'),
(456, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":false,\"items\":null}', '2023-01-09 10:07:19'),
(457, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"113\",\"date\":\"2023-01-01 09:20:30\",\"reference_no\":\"SALE2023\\/01\\/0096\",\"customer_id\":\"526\",\"customer\":\"Pharma Drug Store\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"230.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"24.0000\",\"grand_total\":\"254.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":null,\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"107\",\"reserve_id\":null,\"hash\":\"372a155a290b641e82d83a25555f7ec262a37d097079124c15e474694d62c9bd\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2023-01-09 10:14:25'),
(458, 'Sale is being deleted by owner (User Id: 1)', '{\"model\":{\"id\":\"112\",\"date\":\"2023-01-01 09:10:19\",\"reference_no\":\"SALE2023\\/01\\/0095\",\"customer_id\":\"526\",\"customer\":\"Pharma Drug Store\",\"biller_id\":\"524\",\"biller\":\"PHARMA DRUG STORE CO.\",\"warehouse_id\":\"9\",\"note\":\"\",\"staff_note\":null,\"total\":\"230.0000\",\"product_discount\":\"0.0000\",\"order_discount_id\":null,\"total_discount\":\"0.0000\",\"order_discount\":\"0.0000\",\"product_tax\":\"0.0000\",\"order_tax_id\":\"1\",\"order_tax\":\"0.0000\",\"total_tax\":\"0.0000\",\"shipping\":\"24.0000\",\"grand_total\":\"254.0000\",\"sale_status\":\"pending\",\"payment_status\":\"pending\",\"payment_term\":null,\"due_date\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":null,\"total_items\":\"1\",\"pos\":\"0\",\"paid\":\"0.0000\",\"return_id\":null,\"surcharge\":\"0.0000\",\"attachment\":\"http:\\/\\/www.sandbox.aramex.com\\/content\\/rpt_cache\\/2b61ccec8ee6409baaf87cfa6fc17192.pdf\",\"return_sale_ref\":null,\"sale_id\":null,\"return_sale_total\":\"0.0000\",\"rounding\":null,\"suspend_note\":null,\"api\":\"0\",\"shop\":\"1\",\"address_id\":\"106\",\"reserve_id\":null,\"hash\":\"58bc5e84dbfa38ea608ec8495f0da1fe5ce63ea952694514d074d17fd0119874\",\"manual_payment\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"payment_method\":\"directpay\"},\"items\":null}', '2023-01-09 10:22:27'),
(459, 'Transfer is being deleted by pharmacist (User Id: 37)', '{\"model\":{\"id\":\"9\",\"transfer_no\":\"TR2023\\/02\\/0006\",\"date\":\"2023-02-08 14:34:04\",\"from_warehouse_id\":\"11\",\"from_warehouse_code\":\"DXB001\",\"from_warehouse_name\":\"DUBAI WH\",\"to_warehouse_id\":\"12\",\"to_warehouse_code\":\"RUH001\",\"to_warehouse_name\":\"PHARMA DRUG STORE\",\"note\":\"\",\"total\":\"115.0000\",\"total_tax\":\"0.0000\",\"grand_total\":\"115.0000\",\"created_by\":\"38\",\"status\":\"pending\",\"shipping\":\"0.0000\",\"attachment\":\"0\",\"cgst\":null,\"sgst\":null,\"igst\":null},\"items\":[{\"id\":\"6\",\"transfer_id\":\"9\",\"product_id\":\"43\",\"product_code\":\"PDS004\",\"product_name\":\"SULFAD 1GM\",\"option_id\":\"41\",\"expiry\":null,\"quantity\":\"1.0000\",\"tax_rate_id\":null,\"tax\":\"\",\"item_tax\":\"0.0000\",\"net_unit_cost\":\"115.0000\",\"subtotal\":\"115.0000\",\"quantity_balance\":\"1.0000\",\"unit_cost\":\"115.0000\",\"real_unit_cost\":\"115.0000\",\"date\":\"2023-02-08\",\"warehouse_id\":\"12\",\"product_unit_id\":\"6\",\"product_unit_code\":\"PACK\",\"unit_quantity\":\"1.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"variant\":\"EXPIRY DATE\",\"unit\":\"6\",\"hsn_code\":null,\"second_name\":\"ARTICHOKE,TURMERIC,LIQUORICE,MILK THISTLE\"}]}', '2023-02-21 19:23:20'),
(460, 'Transfer is being deleted by pharmacist (User Id: 37)', '{\"model\":{\"id\":\"8\",\"transfer_no\":\"TR2023\\/02\\/0005\",\"date\":\"2023-02-07 13:53:13\",\"from_warehouse_id\":\"11\",\"from_warehouse_code\":\"DXB001\",\"from_warehouse_name\":\"DUBAI WH\",\"to_warehouse_id\":\"12\",\"to_warehouse_code\":\"RUH001\",\"to_warehouse_name\":\"PHARMA DRUG STORE\",\"note\":\"\",\"total\":\"230.0000\",\"total_tax\":\"0.0000\",\"grand_total\":\"230.0000\",\"created_by\":\"38\",\"status\":\"pending\",\"shipping\":\"0.0000\",\"attachment\":\"0\",\"cgst\":null,\"sgst\":null,\"igst\":null},\"items\":[{\"id\":\"5\",\"transfer_id\":\"8\",\"product_id\":\"43\",\"product_code\":\"PDS004\",\"product_name\":\"SULFAD 1GM\",\"option_id\":\"41\",\"expiry\":null,\"quantity\":\"2.0000\",\"tax_rate_id\":null,\"tax\":\"\",\"item_tax\":\"0.0000\",\"net_unit_cost\":\"115.0000\",\"subtotal\":\"230.0000\",\"quantity_balance\":\"2.0000\",\"unit_cost\":\"115.0000\",\"real_unit_cost\":\"115.0000\",\"date\":\"2023-02-07\",\"warehouse_id\":\"12\",\"product_unit_id\":\"6\",\"product_unit_code\":\"PACK\",\"unit_quantity\":\"2.0000\",\"gst\":null,\"cgst\":null,\"sgst\":null,\"igst\":null,\"variant\":\"EXPIRY DATE\",\"unit\":\"6\",\"hsn_code\":null,\"second_name\":\"ARTICHOKE,TURMERIC,LIQUORICE,MILK THISTLE\"}]}', '2023-02-21 19:29:37');

-- --------------------------------------------------------

--
-- Table structure for table `sma_migrations`
--

CREATE TABLE `sma_migrations` (
  `version` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_migrations`
--

INSERT INTO `sma_migrations` (`version`) VALUES
(315);

-- --------------------------------------------------------

--
-- Table structure for table `sma_notifications`
--

CREATE TABLE `sma_notifications` (
  `id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `from_date` datetime DEFAULT NULL,
  `till_date` datetime DEFAULT NULL,
  `scope` tinyint(1) NOT NULL DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_notifications`
--

INSERT INTO `sma_notifications` (`id`, `comment`, `date`, `from_date`, `till_date`, `scope`) VALUES
(1, '<p>Thank you for the system</p>', '2014-08-15 10:00:57', '2015-01-01 00:00:00', '2017-01-01 00:00:00', 3),
(2, '<p>Test</p>', '2022-10-26 06:55:21', '2022-10-26 11:55:00', '2022-10-29 15:55:00', 3);

-- --------------------------------------------------------

--
-- Table structure for table `sma_order_ref`
--

CREATE TABLE `sma_order_ref` (
  `ref_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `so` int(11) NOT NULL DEFAULT 1,
  `qu` int(11) NOT NULL DEFAULT 1,
  `po` int(11) NOT NULL DEFAULT 1,
  `to` int(11) NOT NULL DEFAULT 1,
  `pos` int(11) NOT NULL DEFAULT 1,
  `do` int(11) NOT NULL DEFAULT 1,
  `pay` int(11) NOT NULL DEFAULT 1,
  `re` int(11) NOT NULL DEFAULT 1,
  `rep` int(11) NOT NULL DEFAULT 1,
  `ex` int(11) NOT NULL DEFAULT 1,
  `ppay` int(11) NOT NULL DEFAULT 1,
  `qa` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_order_ref`
--

INSERT INTO `sma_order_ref` (`ref_id`, `date`, `so`, `qu`, `po`, `to`, `pos`, `do`, `pay`, `re`, `rep`, `ex`, `ppay`, `qa`) VALUES
(1, '2015-03-01', 106, 3, 6, 7, 7, 3, 41, 1, 1, 2, 8, 2);

-- --------------------------------------------------------

--
-- Table structure for table `sma_pages`
--

CREATE TABLE `sma_pages` (
  `id` int(11) NOT NULL,
  `name` varchar(15) NOT NULL,
  `title` varchar(60) NOT NULL,
  `description` varchar(180) NOT NULL,
  `slug` varchar(55) DEFAULT NULL,
  `body` text NOT NULL,
  `active` tinyint(1) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `order_no` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_pages`
--

INSERT INTO `sma_pages` (`id`, `name`, `title`, `description`, `slug`, `body`, `active`, `updated_at`, `order_no`) VALUES
(1, 'About us', 'About Us', 'About Us', 'about-us', '<blockquote>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</blockquote>', 1, '2022-12-19 16:02:09', 2),
(3, 'Privacy Policy', 'Privacy Policy', 'Privacy policy', 'privacy-policy', '<main>\r\n<section>\r\n<p>Effective Date: 15 April 2022 Avenzur and its subsidiaries respect your concerns about privacy. This Privacy Policy describes the types of personal information we collect about in</p><p>Click on one of the links below to jump to the listed section:</p><ul>\r\n<li>\r\n<h5>Information We Obtain</h5></li><li>\r\n<h5>How We Use The Information We Obtain</h5></li><li>\r\n<h5>Automated Collection Of Data</h5></li><li>\r\n<h5>Information Sharing</h5></li><li>\r\n<h5>Data Transfers</h5></li><li>\r\n<h5>Other Online Services And Third-Party Features</h5></li><li>\r\n<h5>Retention Of Personal Information</h5></li><li>\r\n<h5>How We Protect Personal Information</h5></li><li>\r\n<h5>Children’s Personal Information</h5></li><li>\r\n<h5>Updates To Our Privacy Policy</h5></li></ul><h4><strong>Information We Obtain</strong></h4><p>In connection with your use of the Services, you may provide personal information to us in various ways. The types of personal information we obtain include:</p><ul>\r\n<li>contact information (such as name, email address, shipping address and instructions, postal code, telephone number, and mobile number);</li><li>login credentials to create an account on the Services (such as email address and password);</li><li>information contained on your social media public profile, to the extent you decide to create an account on the Services using your social media account;</li><li>user generated content related to product reviews, comments, questions and answers;</li><li>payment information, such as name, billing address, account number, and payment card details (including card number, expiration date and security code) for payments processed by us. To the extent any payments are processed by any other third parties such as PayPal or Apple Pay, then the privacy policies of those parties shall govern such information;</li><li>order history, including information about products purchased or viewed on the Services;</li><li>details you provide through contests, sweepstakes and surveys;</li><li>social media information, such as social media handles, content and other data shared with us through third-party features that you use on our Services (such as apps, tools, payment services, widgets and plugins offered by social media services like Facebook, Google , Instagram, LinkedIn, Pinterest, Twitter and YouTube) or posted on social media pages (such as our social media pages or other pages accessible to us);</li><li>other personal information contained in content you submit on the Services, such as through our “Contact Us” feature or other customer support tools; and</li><li>Country/region and language preference based on mobile device settings and/or IP;</li><li>IP address, device, operating system, and browser information that we detect.</li></ul><p>You are not required to provide this information but, if you choose not to do so, we may not be able to offer you certain Services and related features.</p><h4><strong>How We Use The Information We Obtain</strong></h4><p>We will use the information we obtain through the Services as needed to fulfill our contractual obligation to provide you with the products and services you request and to deliver products ordered (including, but not limited to, transportation and customs clearance through related third party service providers).</p><p>We also will use the information we obtain through the Services if we have a legitimate interest to do so, including to support the following functions and activities:</p><ul>\r\n<li>establishing and managing your account;</li><li>communicating with you about your account or transactions and sending you information about features and enhancements;</li><li>processing claims in connection with our products and services, and keeping you informed about the status of your order;</li><li>posting your product reviews;</li><li>improving and customizing your experience with the Services, including providing recommendations based on your preferences;</li><li>identifying and authenticating you so you may use the Services;</li><li>marketing our products to you and providing you with promotions, including special deals, coupons, discounts and chances to win contests;</li><li>communicating with you about, and administering your participation in, contests, sweepstakes or surveys;</li><li>responding to your requests and inquiries and providing customer support, such as through our chatbot or other customer support tools;</li><li>operating, evaluating and improving our business (including developing new services; enhancing and improving our Services; managing our communications; analyzing our user base and Services; performing data analytics and market research; and performing accounting, auditing and other internal functions);</li><li>protecting against, identifying and preventing fraud and other criminal activity, claims and other liabilities;</li><li>complying with and enforcing applicable legal requirements, relevant industry standards and our policies, including this Privacy Policy and Avenzur’s Terms and Conditions,</li><li>communicating with you about changes to our policies.</li></ul><p>In addition, we will use your contact information to send you Health Newsletters, emails, SMS, push notifications about our products, services, sales and special offers if you sign up to receive them and have not opted out. We also may use your email address to display ads for our products, services, sales and special offers through Facebook’s and Google’s sites or networks. For example, if you are on Facebook, you may see our ads on the social media platform, or if you sign in to your Google account, you may see our ads as you use the Google search engine, Instagram, Facebook, Facebook Audience Network, YouTube, Gmail, and the Google Display Network because these third parties will match your email address with you.</p><p>We may combine information we obtain about you through our websites with the information obtained through our apps for the purposes described above. We also may use the information we obtain in other ways for which we provide specific notice at the time of collection or otherwise with your consent.</p><h4><strong>Automated Collection Of Data</strong></h4><p>When you use our Services or open our emails, we may obtain certain information by automated means, such as browser cookies, Flash cookies, local storage, web beacons and pixels, JavaScript, device identifiers, server logs and other technologies. The information we obtain in this manner may include your device IP address, domain name, identifiers associated with your devices, device and operating system type and characteristics, web browser characteristics, language preferences, clickstream data, your interactions with our Services (such as the web pages you visit, links you click and features you use), the pages that led or referred you to our Services, dates and times of access to our Services, and other information about your use of our Services. We also may receive your device’s geolocation and other information related to your location through GPS, Bluetooth, WiFi signals and other technologies for certain purposes listed above, such as to provide you with our Services. Your device may provide you with a notification when the Services attempt to collect your precise geolocation.</p><p>A “cookie” is a text file that websites send to a visitor’s computer or other Internet-connected device to uniquely identify the visitor’s browser or to store information or settings in the browser. Cookies may be set either directly by the website a user is visiting (“first-party cookies”), or by a domain other than the website the user is visiting (“third-party cookies”). A “Flash cookie,” also known as a local shared object, functions like a web cookie to personalize a user’s experience on sites that use Adobe Flash Player. A “web beacon,” also known as an Internet tag, pixel tag or clear GIF, links web pages to web servers and their cookies and may be used to transmit information collected through cookies back to a web server. We and our third-party service providers may use beacons in emails to help us track response rates, identify when our emails are accessed or forwarded, and for other purposes listed above.</p><p>The following types of cookies and similar technologies are used on the Services:</p><h4><strong><em>Necessary Cookies</em></strong></h4><p>We use necessary cookies to help enable the Services to function, including to (1) identify you once you have logged in to your account, (2) keep track of preferences you specify while you use the Services, and (3) manage the security of the Services.</p><h4><strong><em>Functionality Cookies</em></strong></h4><p>Our Services also use cookies to personalize the content of the Services based on your use of the Services, and support certain third-party services to provide enhanced functionality, such as to enable videos posted on the Services from YouTube. These features use third-party cookies (e.g., You Tube cookies) that are placed directly on your device by the third-party services. The privacy practices of these third parties, including details on the information they may collect about you, are subject to the privacy notices of these parties, which we strongly suggest you review.</p><h4><strong><em>Analytics Cookies</em></strong></h4><p>Through our Services, we may obtain personal information about your online activities over time and across third-party apps, websites, devices and other online services. On our Services, we use third-party online analytics services, such as those of Google Analytics. The service providers that administer these analytics services use automated technologies to collect data (such as email address, IP addresses, cookies and other device identifiers) to evaluate, for example, use of our Services and to diagnose technical issues. To learn more about Google Analytics, please visit <a href=\\\"\\\\\\\">http://www.google.com/analytics/learn/privacy</a>, and to opt out of being tracked by Google Analytics across all websites, please visit: <a href=\\\"\\\\\\\">http://tools.google.com/dlpage/gaoptout</a>. In addition, our Services use FullStory analytics cookies and similar technologies (local storage) to collect technical information from your device (such as IP address, device and operating system type, browser and approximate geographic location based on your device IP address), as well as information about your interactions with our Services (such as clickstream, browsing and keystroke data, and the referring URL and session duration). This information allows us to recreate your sessions so we can get a better understanding of how you use our Services, troubleshoot issues, and administer and improve our Services.</p><h4><strong><em>Personalized Advertising Cookies</em></strong></h4><p>Through our Services, both we and certain third parties may collect information about your online activities to provide you with advertising about products and services tailored to your individual interests. You may see our ads on other websites or mobile apps because we participate in advertising networks. Ad networks allow us to target our messaging to users considering demographic data, users’ inferred interests and browsing context. These networks track users’ online activities over time by collecting information through automated means, including through the use of browser cookies, web beacons, device identifiers, server logs, web beacons and other similar technologies. The networks use this information to show ads that may be tailored to individuals’ interests, to track users’ browsers or devices across multiple websites and apps, and to build a profile of users’ online browsing and app usage activities. The information our ad networks may collect includes data about users’ visits to websites and apps that participate in the relevant ad networks, such as the pages or ads viewed and the actions taken on the websites or apps. This data collection takes place both on our Services and on third-party websites and apps that participate in the ad networks. This process also helps us track the effectiveness of our marketing efforts.</p><h4><strong><em>How to Change Your Cookie Settings</em></strong></h4><p>To the extent required by applicable law, we will obtain your consent before placing non-essential cookies or similar technologies on your device, and keep your choice for a period of six (6) months. If you are located in the European Economic Area (“EEA”) or the United Kingdom (“UK”) you can change your cookie preferences at any time by clicking on the “Cookie Preferences” icon at the bottom of each page of our Services.</p><p>You also can stop certain types of cookies from being downloaded on your device by selecting the appropriate settings on your web browser. Most web browsers will tell you how to stop accepting new browser cookies, how to be notified when you receive a new browser cookie and how to disable existing cookies. The following external links will explain how to manage cookies for the most common browsers:</p><ul>\r\n<li>• <a href=\\\"\\\\\\\">Google Chrome</a></li><li>• <a href=\\\"\\\\\\\">Microsoft Edge</a></li><li>• <a href=\\\"\\\\\\\">Mozilla Firefox</a></li><li>• <a href=\\\"\\\\\\\">Microsoft Internet Explorer</a></li><li>• <a href=\\\"\\\\\\\">Opera</a></li><li>• <a href=\\\"\\\\\\\">Apple Safari</a></li></ul><p>To find out how to manage cookies for other browsers, please click “help” on your browser’s menu or visit <a href=\\\"http://www.allaboutcookies.org.\\\">www.allaboutcookies.org.</a> [Flash cookies typically cannot be controlled, deleted or disabled through your browser settings and instead must be managed through your Adobe Flash Player settings. To manage Flash cookies, which we may use on our website from time to time, you can go to the Adobe Flash Player Support page available here. In addition, your mobile device settings may allow you to prohibit mobile app platforms (such as Apple and Google) from sharing certain information obtained by automated means with app developers and operators such as us. Our Services are not designed to respond to “do not track” signals received from browsers. Please note that without cookies or other automated tools we use to collect this type of data, you may not be able to use all the features of our Services.</p><h4><strong><em>Chatbot Logs</em></strong></h4><p>Our Services also use a chatbot to provide automated customer assistance. A chatbot is a computer program that communicates with you, using text on a digital message interface and artificial intelligence. Put simply, if you ask a question through our chatbot, the chatbot will reply to you in human-ish behavior. Our chatbot is supported by Ada Support, a third-party chatbot service provider located in Canada, who performs services on our behalf (“Ada”). Ada uses an automated decision making process, when deciding on the correct answer to serve based on your question, and will receive message logs and usernames when you interact with the chatbot. Message logs contain information such as details of your account with us, including your username, e-mail address, phone number and address, as well as any other content you choose to submit when you make a customer support inquiry through the chatbot. Ada will retain the content of those messages, together with responses to those messages and any outcome from those messages. This information will be retained for twelve (12) months and will be used only to provide customer support and improve the quality of the chatbot services. If you are located in the EEA, UK or Switzerland, the above information will be transferred to Ada in Canada – a country which has been recognized by the European Commission, UK and Swiss Administration as providing an adequate level of data protection.</p><h4><strong>Information Sharing</strong></h4><p>We do not disclose personal information we obtain about you, except as described in this Privacy Policy. We will share your personal information with our (1) subsidiaries and affiliates, and (2) third-party service providers who perform services on our behalf (such as website hosting, payment processing and authorization, order fulfillment, transportation, customs clearance, marketing, data analytics, customer support and fraud prevention) for the purposes described in this Privacy Policy. We do not authorize our service providers to use or disclose the information except as necessary to perform services on our behalf or comply with legal requirements.</p><p>We also may disclose information about you: (1) if we are required to do so by law or legal process (such as a court order or subpoena); (2) in response to requests by government agencies, such as law enforcement authorities; (3) to establish, exercise or defend our legal rights; (4) when we believe disclosure is necessary or appropriate to prevent physical or other harm or financial loss; (5) in connection with an investigation of suspected or actual illegal activity; (6) in the event we sell or transfer all or a portion of our business or assets (including in the event of a reorganization, dissolution or liquidation); or (7) otherwise with your consent.</p><h4><strong>Data Transfers</strong></h4><p>We may transfer the personal information we collect about you to recipients in countries other than the country in which the information originally was collected. Those countries may not have the same data protection laws as the country in which you initially provided the information. When we transfer your information to recipients in other countries (such as the U.S.), we will protect that information as described in this Privacy Policy and will comply with applicable legal requirements providing adequate protection for the transfer of personal information to recipients in countries other than the one in which you provided the information, including by selecting service providers that are located in a country recognized by the European Commission as providing an adequate level of data protection or by implementing appropriate safeguards based on the European Commission’s Standard Contractual Clauses, where applicable. Subject to applicable law, you may obtain a copy of these safeguards by contacting us as on info@avenzur.com</p><h4><strong>Other Online Services And Third-Party Features</strong></h4><p>Our Services may provide links to other online services and websites for your convenience and information, and may include third-party features such as apps, tools, widgets and plug-ins (e.g., Facebook, Google , Instagram, LinkedIn, Pinterest, Twitter, YouTube, and Shopify). These services, websites, and third-party features may operate independently from us. The privacy practices of these third parties, including details on the information they may collect about you, are subject to the privacy statements of these parties, which we strongly suggest you review. To the extent any linked online services or third-party features are not owned or controlled by us, Avenzur is not responsible for these third parties’ information practices.</p><h4><strong>Retention Of Personal Information</strong></h4><p>To the extent required by applicable law, we keep the personal information you provide for the duration of our relationship, plus a reasonable period to comply with the applicable statute of limitations or if otherwise required under applicable law.</p><h4><strong>How We Protect Personal Information</strong></h4><p>We maintain administrative, technical and physical safeguards designed to protect personal information we obtain through the Services against accidental, unlawful or unauthorized destruction, loss, alteration, access, disclosure or use.</p><h4><strong>Children’s Personal Information</strong></h4><p>The Services are designed for a general audience and are not directed to children under the age of 16. Avenzur does not knowingly collect or solicit personal information from children under the age of 16 through the Services. If we learn that we have collected personal information from a child under the age of 16, we will promptly delete that information from our records.</p><h4><strong>Updates To Our Privacy Policy</strong></h4><p>This Privacy Policy may be updated periodically and without prior notice to you to reflect changes in our personal information practices. We will indicate at the top of the Privacy Policy when it was most recently updated.</p></section></main><footer></footer>', 1, '2022-11-29 14:08:20', 2);
INSERT INTO `sma_pages` (`id`, `name`, `title`, `description`, `slug`, `body`, `active`, `updated_at`, `order_no`) VALUES
(4, 'Term&Condition', 'OVERVIEW', 'This website is operated by Avenzur. Throughout the site, the terms “we”, “us” and “our” refer to Avenzur offers this website, including all information, tools and services availab', 'Terms-Conditions', '<p><p><p>By visiting our site and/ or purchasing something from us, you engage in our “Service” and agree to be bound by the following terms and conditions (“Terms of Service”, “Terms”), including those additional terms and conditions and policies referenced herein and/or available by hyperlink. These Terms of Service apply to all users of the site, including without limitation users who are browsers, vendors, customers, merchants, and/ or contributors of content.</p><p>Please read these Terms of Service carefully before accessing or using our website. By accessing or using any part of the site, you agree to be bound by these Terms of Service. If you do not agree to all the terms and conditions of this agreement, then you may not access the website or use any services. If these Terms of Service are considered an offer, acceptance is expressly limited to these Terms of Service.</p><p>Any new features or tools which are added to the current store shall also be subject to the Terms of Service. You can review the most current version of the Terms of Service at any time on this page. We reserve the right to update, change or replace any part of these Terms of Service by posting updates and/or changes to our website. It is your responsibility to check this page periodically for changes. Your continued use of or access to the website following the posting of any changes constitutes acceptance of those changes.<br>Our store is hosted on Shopify Inc. They provide us with the online e-commerce platform that allows us to sell our products and services to you.</p><h4><strong>SECTION 1 – ONLINE STORE TERMS</strong></h4><p>By agreeing to these Terms of Service, you represent that you are at least the age of majority in your state or province of residence, or that you are the age of majority in your state or province of residence and you have given us your consent to allow any of your minor dependents to use this site.</p><p>You may not use our products for any illegal or unauthorized purpose nor may you, in the use of the Service, violate any laws in your jurisdiction (including but not limited to copyright laws).</p><p>You must not transmit any worms or viruses or any code of a destructive nature.</p><p>A breach or violation of any of the Terms will result in an immediate termination of your Services.</p><h4><strong>SECTION 2 – GENERAL CONDITIONS</strong></h4><p>We reserve the right to refuse service to anyone for any reason at any time.</p><p>You understand that your content (not including credit card information), may be transferred unencrypted and involve (a) transmissions over various networks; and (b) changes to conform and adapt to technical requirements of connecting networks or devices. Credit card information is always encrypted during transfer over networks.</p><p>You agree not to reproduce, duplicate, copy, sell, resell or exploit any portion of the Service, use of the Service, or access to the Service or any contact on the website through which the service is provided, without express written permission by us.</p><p>The headings used in this agreement are included for convenience only and will not limit or otherwise affect these Terms.</p><h4><strong>SECTION 3 – PRODUCT PURCHASES</strong></h4><p>To the extent you make purchases on the Site, you agree that all purchases of products are made pursuant to the respective INCOTERMS designated upon order placement. Title for any products purchased by you will transfer upon our delivery to the carrier.</p><p>It is your responsibility to ascertain and comply with all applicable local, state, federal, and international laws regarding the receipt, possession, use, and sale of any item purchased from this Site.</p><p>When ordering from Avenzur (www.avenzur.com) you are responsible for assuring the product can be lawfully imported into your country. Customers are the importers of record and must comply with all laws and regulations of the destination country.</p><p>Avenzur reserves the right to prohibit purchases of any merchandise to resellers. Resellers are defined as a company or an individual that purchases goods with the intention of selling them rather than using them. Avenzur does does not support sales tax exemption requests for businesses or resellers.</p><h4><strong>SECTION 4 – USAGE AND TERMINATION</strong></h4><p>By using our Site you represent and agree that you are at least eighteen (18) years of age or older and are fully able and competent to agree to the terms in this Agreement or any Program terms and conditions. If you are under the age of 18, you are not permitted to use this Site or participate in any Program.</p><h5><strong>a. Account Setup & Use</strong></h5><p>You are required to establish an account on the Site in order to use certain features, such as making a purchase. You agree to provide accurate, true, complete and current information about yourself as prompted by the Site and to promptly update such information to maintain accurate, true, complete and current information. If you provide any inaccurate, false, incomplete or outdated information or we in our sole discretion suspect that such information is inaccurate, false, incomplete or outdated, we reserve the right to suspend or terminate your account and prohibit any and all current or future use of the Site or any portion thereof by you. During the registration process you will create a username and password. You are responsible for the confidentiality of your account and password and are fully responsible for all activities that occur under your account or password. You agree to immediately notify us of any unauthorized use of your account or password or any other security breach and to ensure that you exit from your account at the end of each session. You agree to be responsible for all charges resulting from the use of your account on the Site including charges resulting from unauthorized use of your account. We are not liable for any loss or damage resulting from your failure to comply with this section.</p><h5><strong>b. Use of the site</strong></h5><p>You agree to use the Site and engage in the Programs for lawful purposes and that you are responsible for your use of and communications on the Site. You agree not to post on or transmit through the Site any unlawful, infringing, defamatory, obscene, indecent, threatening, offensive or otherwise objectionable material of any kind including any material that encourages illegal conduct or conduct that would encourage civil liability, infringe on other’s intellectual property rights or otherwise violates any applicable local, state, national or international law. You agree not to use the Site in a manner that would interfere with normal operation or infringe on any others use of the Site.<br>You agree not to access the Site by any means other than the interface we provide. Displaying or running the Site or any information or material displayed on the Site in frames or through similar means on another website without our prior authorization is prohibited. Any permitted links to the Site must comply with all applicable laws, rules and regulations.<br>We make no representation that materials contained on the Site or that products described or offered on the Site are appropriate or available for use in jurisdictions outside Italy, or that this Agreement complies with the laws of any other country. Users of the Site outside Italy do so at their own initiative and risk and are responsible for complying with all applicable laws and regulations, and undertake to, at the time of at the time of purchase of a product, to ascertain whether the importation of the respective product is allowed by the laws of their country and by the applicable governmental agencies. You agree not to access the Site from any location or territory where its contents are illegal and that you and not us, are responsible for compliance with all applicable laws and regulations.<br>You may not use the Site or Avenzur’s services if you are the subject of U.S. and/or European sanctions or of sanctions consistent with U.S. and/or European law imposed by the government of the country where you are accessing the Site or using Avenzur’s services. You are responsible for compliance with all U.S. and/or European or other export and re-export restrictions that may apply to goods.</p><h5><strong>c. Termination or Suspension of the Agreement</strong></h5><p>This Agreement is effective until terminated by either us or you. We, in our sole discretion, may suspend or terminate this Agreement at any time without notice and deny you access to the Site or any portion of it. You may terminate this Agreement at any time by contacting our customer service and discontinuing all use of the Site. Upon termination by us or you, you must destroy all materials obtained from the Site including all copies of such materials whether made under the terms of use contained in this Agreement or otherwise. We reserve the right to modify or discontinue, temporarily or permanently, the Site or any portion of it with notice to you.</p><h5><strong>d. Account Termination</strong></h5><p>We reserve the right to terminate any account if your order is deemed fraudulent or credit card charges are disputed. You agree that we may terminate or suspend your access to all or part of the Site, with or without notice, for any conduct that we, in our sole discretion, believe is in violation of any part of this Agreement, laws or regulations or is harmful to another user or us or our affiliates.</p><h4><strong>SECTION 5 – ACCURACY, COMPLETENESS AND TIMELINESS OF INFORMATION</strong></h4><p>We are not responsible if information made available on this site is not accurate, complete or current. The material on this site is provided for general information only and should not be relied upon or used as the sole basis for making decisions without consulting primary, more accurate, more complete or more timely sources of information. Any reliance on the material on this site is at your own risk.</p><p>This site may contain certain historical information. Historical information, necessarily, is not current and is provided for your reference only. We reserve the right to modify the contents of this site at any time, but we have no obligation to update any information on our site. You agree that it is your responsibility to monitor changes to our site.</p><h4><strong>SECTION 6 – NOT HEALTHCARE ADVICE</strong></h4><p>The products and claims made about specific products on or through the Site have not been evaluated by the United States Food and Drug Administration and/or the European Medicines Agency (EMA) and are not approved to diagnose, treat, cure or prevent disease.</p><p>The Site is not intended to provide diagnosis, treatment or medical advice. Products, services, information and other content provided on the Site, including information that may be provided on the Site directly or by linking to third-party websites are provided for informational purposes only. Please consult with a physician or other healthcare professional regarding any medical or health related diagnosis or treatment options.</p><p>Information provided on the Site and linked websites, including information relating to medical and health conditions, treatments and products may be provided in summary form. Information on the Site including any product label or packaging should not be considered as a substitute for advice from a healthcare professional. The Site does not recommend self-management of health issues. Information on the Site is not comprehensive and does not cover all diseases, ailments, physical conditions or their treatment. Contact your healthcare professional promptly should you have any health related questions. Never disregard or delay medical advice based upon information you may have read on the Site.</p><p>Links to or access from any third party websites or resources is not an endorsement of any information, product or service. We are not responsible for the content or performance of any third party websites. Use of any third party websites is at your own risk.</p><p>You should not use the information or services on the Site to diagnose or treat any health issues or for prescription of any medication or other treatment. You should always consult with your healthcare professional and read information provided by the product manufacturer and any product label or packaging, prior to using any medication, nutritional, herbal or homeopathic product or before beginning any exercise or diet program or starting any treatment for a health issue. Individuals are different and may react differently to different products. You should consult your physician about interactions between medications you are taking and nutritional supplements. Comments made in any forums on the Site by employees or Site users are strictly their own personal views made in their own personal capacity and are not claims made by us or do they represent our positions or views. Product ratings by any current or previous employees or Site users are strictly their own personal views made in their own personal capacity and are not intended as a substitute for appropriate medical care or advice from a healthcare professional. We are not liable for any information provided on the Site with regard to recommendations regarding supplements for any health purposes.</p><p>Always check the product label or packaging prior to using any product. If there are discrepancies, customers should follow the information provided on the product label or packaging. You should contact the manufacturer directly for clarification as to product labeling and packaging details and recommended use.</p><h4><strong>SECTION 7 – MODIFICATIONS TO THE SERVICE AND PRICES</strong></h4><p>Prices for our products are subject to change without notice.</p><p>We reserve the right at any time to modify or discontinue the Service (or any part or content thereof) without notice at any time.</p><p>We shall not be liable to you or to any third-party for any modification, price change, suspension or discontinuance of the Service.<br>SECTION 5 – PRODUCTS OR SERVICES (if applicable)</p><p>Certain products or services may be available exclusively online through the website. These products or services may have limited quantities and are subject to return or exchange only according to our Return Policy.</p><p>We have made every effort to display as accurately as possible the colors and images of our products that appear at the store. We cannot guarantee that your computer monitor’s display of any color will be accurate.<br>We reserve the right, but are not obligated, to limit the sales of our products or Services to any person, geographic region or jurisdiction. We may exercise this right on a case-by-case basis. We reserve the right to limit the quantities of any products or services that we offer. All descriptions of products or product pricing are subject to change at anytime without notice, at the sole discretion of us. We reserve the right to discontinue any product at any time. Any offer for any product or service made on this site is void where prohibited.</p><p>We do not warrant that the quality of any products, services, information, or other material purchased or obtained by you will meet your expectations, or that any errors in the Service will be corrected.</p><h4><strong>SECTION 8 – ACCURACY OF BILLING AND ACCOUNT INFORMATION</strong></h4><p>We reserve the right to refuse any order you place with us. We may, in our sole discretion, limit or cancel quantities purchased per person, per household or per order. These restrictions may include orders placed by or under the same customer account, the same credit card, and/or orders that use the same billing and/or shipping address. In the event that we make a change to or cancel an order, we may attempt to notify you by contacting the e-mail and/or billing address/phone number provided at the time the order was made. We reserve the right to limit or prohibit orders that, in our sole judgment, appear to be placed by dealers, resellers or distributors.<br>You agree to provide current, complete and accurate purchase and account information for all purchases made at our store. You agree to promptly update your account and other information, including your email address and credit card numbers and expiration dates, so that we can complete your transactions and contact you as needed.</p><p>For more detail, please review our Returns Policy.</p><h4><strong>SECTION 9 – OPTIONAL TOOLS</strong></h4><p>We may provide you with access to third-party tools over which we neither monitor nor have any control nor input.</p><p>You acknowledge and agree that we provide access to such tools ”as is” and “as available” without any warranties, representations or conditions of any kind and without any endorsement. We shall have no liability whatsoever arising from or relating to your use of optional third-party tools.</p><p>Any use by you of optional tools offered through the site is entirely at your own risk and discretion and you should ensure that you are familiar with and approve of the terms on which tools are provided by the relevant third-party provider(s).</p><p>We may also, in the future, offer new services and/or features through the website (including, the release of new tools and resources). Such new features and/or services shall also be subject to these Terms of Service.</p><h4><strong>SECTION 10 – THIRD-PARTY LINKS</strong></h4><p>Certain content, products and services available via our Service may include materials from third-parties.</p><p>Third-party links on this site may direct you to third-party websites that are not affiliated with us. We are not responsible for examining or evaluating the content or accuracy and we do not warrant and will not have any liability or responsibility for any third-party materials or websites, or for any other materials, products, or services of third-parties.</p><p>We are not liable for any harm or damages related to the purchase or use of goods, services, resources, content, or any other transactions made in connection with any third-party websites. Please review carefully the third-party’s policies and practices and make sure you understand them before you engage in any transaction. Complaints, claims, concerns, or questions regarding third-party products should be directed to the third-party.</p><h4><strong>SECTION 11 – USER COMMENTS, FEEDBACK AND OTHER SUBMISSIONS</strong></h4><p>If, at our request, you send certain specific submissions (for example contest entries) or without a request from us you send creative ideas, suggestions, proposals, plans, or other materials, whether online, by email, by postal mail, or otherwise (collectively, ‘comments’), you agree that we may, at any time, without restriction, edit, copy, publish, distribute, translate and otherwise use in any medium any comments that you forward to us. We are and shall be under no obligation (1) to maintain any comments in confidence; (2) to pay compensation for any comments; or (3) to respond to any comments.<br>We may, but have no obligation to, monitor, edit or remove content that we determine in our sole discretion are unlawful, offensive, threatening, libelous, defamatory, pornographic, obscene or otherwise objectionable or violates any party’s intellectual property or these Terms of Service.<br>You agree that your comments will not violate any right of any third-party, including copyright, trademark, privacy, personality or other personal or proprietary right. You further agree that your comments will not contain libelous or otherwise unlawful, abusive or obscene material, or contain any computer virus or other malware that could in any way affect the operation of the Service or any related website. You may not use a false e-mail address, pretend to be someone other than yourself, or otherwise mislead us or third-parties as to the origin of any comments. You are solely responsible for any comments you make and their accuracy. We take no responsibility and assume no liability for any comments posted by you or any third-party.</p><h4><strong>SECTION 12 – PERSONAL INFORMATION</strong></h4><p>Your submission of personal information through the store is governed by our Privacy Policy. To view our Privacy Policy.</p><h4><strong>SECTION 13 – ERRORS, INACCURACIES AND OMISSIONS</strong></h4><p>Occasionally there may be information on our site or in the Service that contains typographical errors, inaccuracies or omissions that may relate to product descriptions, pricing, promotions, offers, product shipping charges, transit times and availability. We reserve the right to correct any errors, inaccuracies or omissions, and to change or update information or cancel orders if any information in the Service or on any related website is inaccurate at any time without prior notice (including after you have submitted your order).</p><p>We undertake no obligation to update, amend or clarify information in the Service or on any related website, including without limitation, pricing information, except as required by law. No specified update or refresh date applied in the Service or on any related website, should be taken to indicate that all information in the Service or on any related website has been modified or updated.</p><h4><strong>SECTION 14 – PROHIBITED USES</strong></h4><p>In addition to other prohibitions as set forth in the Terms of Service, you are prohibited from using the site or its content:<br>(a) for any unlawful purpose; (b) to solicit others to perform or participate in any unlawful acts; (c) to violate any international, federal, provincial or state regulations, rules, laws, or local ordinances; (d) to infringe upon or violate our intellectual property rights or the intellectual property rights of others; (e) to harass, abuse, insult, harm, defame, slander, disparage, intimidate, or discriminate based on gender, sexual orientation, religion, ethnicity, race, age, national origin, or disability; (f) to submit false or misleading information;<br>(g) to upload or transmit viruses or any other type of malicious code that will or may be used in any way that will affect the functionality or operation of the Service or of any related website, other websites, or the Internet; (h) to collect or track the personal information of others; (i) to spam, phish, pharm, pretext, spider, crawl, or scrape; (j) for any obscene or immoral purpose; or (k) to interfere with or circumvent the security features of the Service or any related website, other websites, or the Internet. We reserve the right to terminate your use of the Service or any related website for violating any of the prohibited uses.</p><h4><strong>SECTION 15 – DISCLAIMER OF WARRANTIES; LIMITATION OF LIABILITY</strong></h4><p>We do not guarantee, represent or warrant that your use of our service will be uninterrupted, timely, secure or error-free.</p><p>We do not warrant that the results that may be obtained from the use of the service will be accurate or reliable.</p><p>You agree that from time to time we may remove the service for indefinite periods of time or cancel the service at any time, without notice to you.<br>You expressly agree that your use of, or inability to use, the service is at your sole risk. The service and all products and services delivered to you through the service are (except as expressly stated by us) provided ‘as is’ and ‘as available’ for your use, without any representation, warranties or conditions of any kind, either express or implied, including all implied warranties or conditions of merchantability, merchantable quality, fitness for a particular purpose, durability, title, and non-infringement.<br>In no case shall Avenzur, our directors, officers, employees, affiliates, agents, contractors, interns, suppliers, service providers or licensors be liable for any injury, loss, claim, or any direct, indirect, incidental, punitive, special, or consequential damages of any kind, including, without limitation lost profits, lost revenue, lost savings, loss of data, replacement costs, or any similar damages, whether based in contract, tort (including negligence), strict liability or otherwise, arising from your use of any of the service or any products procured using the service, or for any other claim related in any way to your use of the service or any product, including, but not limited to, any errors or omissions in any content, or any loss or damage of any kind incurred as a result of the use of the service or any content (or product) posted, transmitted, or otherwise made available via the service, even if advised of their possibility.<br>Because some states or jurisdictions do not allow the exclusion or the limitation of liability for consequential or incidental damages, in such states or jurisdictions, our liability shall be limited to the maximum extent permitted by law.</p><h4><strong>SECTION 16 – INDEMNIFICATION</strong></h4><p>You agree to indemnify, defend and hold harmless Avenzur and our parent, subsidiaries, affiliates, partners, officers, directors, agents, contractors, licensors, service providers, subcontractors, suppliers, interns and employees, harmless from any claim or demand, including reasonable attorneys’ fees, made by any third-party due to or arising out of your breach of these Terms of Service or the documents they incorporate by reference, or your violation of any law or the rights of a third-party.</p><h4><strong>SECTION 17 – SEVERABILITY</strong></h4><p>In the event that any provision of these Terms of Service is determined to be unlawful, void or unenforceable, such provision shall nonetheless be enforceable to the fullest extent permitted by applicable law, and the unenforceable portion shall be deemed to be severed from these Terms of Service, such determination shall not affect the validity and enforceability of any other remaining provisions.</p><h4><strong>SECTION 18 – TERMINATION</strong></h4><p>The obligations and liabilities of the parties incurred prior to the termination date shall survive the termination of this agreement for all purposes.</p><p>These Terms of Service are effective unless and until terminated by either you or us. You may terminate these Terms of Service at any time by notifying us that you no longer wish to use our Services, or when you cease using our site.<br>If in our sole judgment you fail, or we suspect that you have failed, to comply with any term or provision of these Terms of Service, we also may terminate this agreement at any time without notice and you will remain liable for all amounts due up to and including the date of termination; and/or accordingly may deny you access to our Services (or any part thereof).</p><h4><strong>SECTION 19 – ENTIRE AGREEMENT</strong></h4><p>The failure of us to exercise or enforce any right or provision of these Terms of Service shall not constitute a waiver of such right or provision.</p><p>These Terms of Service and any policies or operating rules posted by us on this site or in respect to The Service constitutes the entire agreement and understanding between you and us and govern your use of the Service, superseding any prior or contemporaneous agreements, communications and proposals, whether oral or written, between you and us (including, but not limited to, any prior versions of the Terms of Service).<br>Any ambiguities in the interpretation of these Terms of Service shall not be construed against the drafting party.</p><h4><strong>SECTION 20 – GOVERNING LAW</strong></h4><p>These Terms of Service and any separate agreements whereby we provide you Services shall be governed by and construed in accordance with the laws of Italy.</p><h4><strong>SECTION 21 – AGREEMENT CHANGES</strong></h4><p>You can review the most current version of the Terms of Service at any time at this page.</p><p>We may, in our sole discretion, change these Terms without notice to you. If any change to these terms is found invalid, void, or for any reason unenforceable, that change is severable and does not affect the validity and enforceability of any remaining changes or conditions. YOUR CONTINUED PARTICIPATION AFTER WE CHANGE THESE TERMS CONSTITUTES YOUR ACCEPTANCE OF THE CHANGES. IF YOU DO NOT AGREE TO ANY CHANGES, YOU MUST CANCEL YOUR SUBSCRIPTIONS.</p><h4><strong>SECTION 20 – CONTACT INFORMATION</strong></h4><p>Questions about the Terms and Conditions should be sent to us at info@abmsrl.net</p></p></p>', 1, '2022-11-29 13:34:12', 3),
(5, 'Not healthcare ', 'Not Healthcare Advice', 'Not Healthcare Advice', 'not-healthcare-advice', '<p>The products and claims made about specific products on or through this Site have not been evaluated by the United States Food and Drug Administration (FDA) and are not approved to</p><p>This Site is not intended to provide diagnosis, treatment or medical advice. Products, services, information and other content provided on this Site, including information that may be provided on this Site directly or by linking to third-party websites are provided for informational purposes only. Please consult with a physician or other healthcare professional regarding any medical or health related diagnosis or treatment options.</p><p>Information provided on this Site and linked websites, including information relating to medical and health conditions, treatments and products may be provided in summary form. Information on this Site including any product label or packaging should not be considered as a substitute for advice from a healthcare professional. This Site does not recommend self-management of health issues. Information on this Site is not comprehensive and does not cover all diseases, ailments, physical conditions or their treatment. Contact your healthcare professional promptly should you have any health related questions. Never disregard or delay medical advice based upon information you may have read on this Site.</p><p>You should not use the information or services on this Site to diagnose or treat any health issues or for prescription of any medication or other treatment. You should always consult with your healthcare professional and read information provided by the product manufacture and any product label or packaging, prior to using any medication, nutritional, herbal or homeopathic product or before beginning any exercise or diet program or starting any treatment for a health issue. Individuals are different and may react differently to different products. You should consult your physician about interactions between medications you are taking and nutritional supplements. Comments made in any forums on this Site by employees or Site users are strictly their own personal views made in their own personal capacity and are not claims made by us or do they represent the position or view of iHerb. Product ratings by any current or previous employees or Site users are strictly their own personal views made in their own personal capacity and are not intended as a substitute for appropriate medical care or advice from a healthcare professional.</p><p>Always check the product label or packaging prior to using any product. If there are discrepancies, customers should follow the information provided on the product label or packaging. You should contact the manufacturer directly for clarification as to product labeling and packaging details and recommended use.</p><p>Avenzur is not liable for any information provided on this Site with regard to recommendations regarding supplements for any health purposes. The products or claims made about specific nutrients or products have not been evaluated by the Food and Drug Administration (FDA) and/or the European Medicine Agency (EMA). Dietary products are not intended to treat, prevent or cure disease. Consult with a healthcare professional before starting any diet, supplement or exercise program. iHerb makes no guarantee or warranty with respect to any products or services sold.</p><p>Avenzur is not responsible for any damages for information or services provided even if Avenzur has been advised of the possibility of damages.</p><p>These statements apply to <a href=\\\"http://www.Avenzur.com\\\">www.Avenzur.com</a> and all websites hosted or co-hosted by Avenzur</p>', 1, '2022-11-29 14:09:33', 4),
(8, 'Blog', 'blog', 'blog', 'blog', '<p>blog</p>', 1, '2022-12-05 11:31:15', 4),
(9, 'Contact Us', 'Contact Us', 'Contact Us', 'contact-us', '<p>Contact Us</p>', 1, '2022-12-10 17:33:21', 5);

-- --------------------------------------------------------

--
-- Table structure for table `sma_payments`
--

CREATE TABLE `sma_payments` (
  `id` int(11) NOT NULL,
  `date` timestamp NULL DEFAULT current_timestamp(),
  `sale_id` int(11) DEFAULT NULL,
  `return_id` int(11) DEFAULT NULL,
  `purchase_id` int(11) DEFAULT NULL,
  `reference_no` varchar(50) NOT NULL,
  `transaction_id` varchar(50) DEFAULT NULL,
  `paid_by` varchar(20) NOT NULL,
  `cheque_no` varchar(20) DEFAULT NULL,
  `cc_no` varchar(20) DEFAULT NULL,
  `cc_holder` varchar(25) DEFAULT NULL,
  `cc_month` varchar(2) DEFAULT NULL,
  `cc_year` varchar(4) DEFAULT NULL,
  `cc_type` varchar(20) DEFAULT NULL,
  `amount` decimal(25,4) NOT NULL,
  `currency` varchar(3) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `attachment` varchar(55) DEFAULT NULL,
  `type` varchar(20) NOT NULL,
  `note` varchar(1000) DEFAULT NULL,
  `pos_paid` decimal(25,4) DEFAULT 0.0000,
  `pos_balance` decimal(25,4) DEFAULT 0.0000,
  `approval_code` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_payments`
--

INSERT INTO `sma_payments` (`id`, `date`, `sale_id`, `return_id`, `purchase_id`, `reference_no`, `transaction_id`, `paid_by`, `cheque_no`, `cc_no`, `cc_holder`, `cc_month`, `cc_year`, `cc_type`, `amount`, `currency`, `created_by`, `attachment`, `type`, `note`, `pos_paid`, `pos_balance`, `approval_code`) VALUES
(35, '2022-12-30 22:14:00', NULL, NULL, 12, 'POP2022/12/0004', NULL, 'cash', '', '', '', '', '', 'Visa', '728625.0000', NULL, 1, NULL, 'sent', '', '0.0000', '0.0000', NULL),
(36, '2022-12-30 22:14:00', NULL, NULL, 11, 'POP2022/12/0005', NULL, 'cash', '', '', '', '', '', 'Visa', '869409.0000', NULL, 1, NULL, 'sent', '', '0.0000', '0.0000', NULL),
(37, '2023-01-01 15:27:23', 114, NULL, NULL, 'IPAY2023/01/0032', '1672557991851114', 'DirectPay', NULL, NULL, NULL, NULL, NULL, NULL, '254.0000', NULL, 0, NULL, 'received', '682 25400 had been paid for the Sale Reference No SALE2023/01/0097', '0.0000', '0.0000', NULL),
(38, '2023-01-04 14:21:20', 116, NULL, NULL, 'IPAY2023/01/0033', '1672813219613116', 'DirectPay', NULL, NULL, NULL, NULL, NULL, NULL, '254.0000', NULL, 0, NULL, 'received', '682 25400 had been paid for the Sale Reference No SALE2023/01/0099', '0.0000', '0.0000', NULL),
(39, '2023-01-09 18:27:39', 117, NULL, NULL, 'IPAY2023/01/0034', '1673260008430117', 'DirectPay', NULL, NULL, NULL, NULL, NULL, NULL, '254.0000', NULL, 0, NULL, 'received', '682 25400 had been paid for the Sale Reference No SALE2023/01/0100', '0.0000', '0.0000', NULL),
(40, '2023-01-11 14:55:26', 118, NULL, NULL, 'IPAY2023/01/0035', NULL, 'cash', '', '', '', '', '', '', '230.0000', NULL, 1, NULL, 'received', '', '230.0000', '0.0000', NULL),
(41, '2023-01-18 23:21:10', 123, NULL, NULL, 'IPAY2023/01/0036', '1674055172599123', 'DirectPay', NULL, NULL, NULL, NULL, NULL, NULL, '254.0000', NULL, 0, NULL, 'received', '682 25400 had been paid for the Sale Reference No SALE2023/01/0105', '0.0000', '0.0000', NULL),
(42, '2023-02-06 18:27:00', NULL, NULL, 15, 'POP2023/02/0006', NULL, 'cash', '', '', '', '', '', 'Visa', '11500.0000', NULL, 1, NULL, 'sent', '', '0.0000', '0.0000', NULL),
(43, '2023-02-07 18:37:29', 124, NULL, NULL, 'IPAY2023/02/0037', NULL, 'cash', '', '', '', '', '', '', '230.0000', NULL, 1, NULL, 'received', '', '230.0000', '0.0000', NULL),
(44, '2023-02-08 19:52:00', NULL, NULL, 16, 'POP2023/02/0007', NULL, 'cash', '', '', '', '', '', 'Visa', '3500.0000', NULL, 1, NULL, 'sent', '', '0.0000', '0.0000', NULL),
(45, '2023-02-08 19:48:38', 125, NULL, NULL, 'IPAY2023/02/0038', NULL, 'cash', '', '', '', '', '', '', '270.0000', NULL, 38, NULL, 'received', '', '270.0000', '0.0000', NULL),
(46, '2023-02-14 17:36:33', 126, NULL, NULL, 'IPAY2023/02/0039', NULL, 'cash', '', '', '', '', '', '', '1380.0000', NULL, 38, NULL, 'received', '', '1380.0000', '0.0000', NULL),
(47, '2023-02-14 20:00:47', 127, NULL, NULL, 'IPAY2023/02/0040', NULL, 'CC', '', '', '', '', '', '', '460.0000', NULL, 38, NULL, 'received', '', '460.0000', '0.0000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sma_paypal`
--

CREATE TABLE `sma_paypal` (
  `id` int(11) NOT NULL,
  `active` tinyint(4) NOT NULL,
  `account_email` varchar(255) NOT NULL,
  `paypal_currency` varchar(3) NOT NULL DEFAULT 'USD',
  `fixed_charges` decimal(25,4) NOT NULL DEFAULT 2.0000,
  `extra_charges_my` decimal(25,4) NOT NULL DEFAULT 3.9000,
  `extra_charges_other` decimal(25,4) NOT NULL DEFAULT 4.4000
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_paypal`
--

INSERT INTO `sma_paypal` (`id`, `active`, `account_email`, `paypal_currency`, `fixed_charges`, `extra_charges_my`, `extra_charges_other`) VALUES
(1, 0, 'mypaypal@paypal.com', 'USD', '0.0000', '0.0000', '0.0000');

-- --------------------------------------------------------

--
-- Table structure for table `sma_permissions`
--

CREATE TABLE `sma_permissions` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `products-index` tinyint(1) DEFAULT 0,
  `products-add` tinyint(1) DEFAULT 0,
  `products-edit` tinyint(1) DEFAULT 0,
  `products-delete` tinyint(1) DEFAULT 0,
  `products-cost` tinyint(1) DEFAULT 0,
  `products-price` tinyint(1) DEFAULT 0,
  `quotes-index` tinyint(1) DEFAULT 0,
  `quotes-add` tinyint(1) DEFAULT 0,
  `quotes-edit` tinyint(1) DEFAULT 0,
  `quotes-pdf` tinyint(1) DEFAULT 0,
  `quotes-email` tinyint(1) DEFAULT 0,
  `quotes-delete` tinyint(1) DEFAULT 0,
  `sales-index` tinyint(1) DEFAULT 0,
  `sales-add` tinyint(1) DEFAULT 0,
  `sales-edit` tinyint(1) DEFAULT 0,
  `sales-pdf` tinyint(1) DEFAULT 0,
  `sales-email` tinyint(1) DEFAULT 0,
  `sales-delete` tinyint(1) DEFAULT 0,
  `purchases-index` tinyint(1) DEFAULT 0,
  `purchases-add` tinyint(1) DEFAULT 0,
  `purchases-edit` tinyint(1) DEFAULT 0,
  `purchases-pdf` tinyint(1) DEFAULT 0,
  `purchases-email` tinyint(1) DEFAULT 0,
  `purchases-delete` tinyint(1) DEFAULT 0,
  `transfers-index` tinyint(1) DEFAULT 0,
  `transfers-add` tinyint(1) DEFAULT 0,
  `transfers-edit` tinyint(1) DEFAULT 0,
  `transfers-pdf` tinyint(1) DEFAULT 0,
  `transfers-email` tinyint(1) DEFAULT 0,
  `transfers-delete` tinyint(1) DEFAULT 0,
  `customers-index` tinyint(1) DEFAULT 0,
  `customers-add` tinyint(1) DEFAULT 0,
  `customers-edit` tinyint(1) DEFAULT 0,
  `customers-delete` tinyint(1) DEFAULT 0,
  `suppliers-index` tinyint(1) DEFAULT 0,
  `suppliers-add` tinyint(1) DEFAULT 0,
  `suppliers-edit` tinyint(1) DEFAULT 0,
  `suppliers-delete` tinyint(1) DEFAULT 0,
  `sales-deliveries` tinyint(1) DEFAULT 0,
  `sales-add_delivery` tinyint(1) DEFAULT 0,
  `sales-edit_delivery` tinyint(1) DEFAULT 0,
  `sales-delete_delivery` tinyint(1) DEFAULT 0,
  `sales-email_delivery` tinyint(1) DEFAULT 0,
  `sales-pdf_delivery` tinyint(1) DEFAULT 0,
  `sales-gift_cards` tinyint(1) DEFAULT 0,
  `sales-add_gift_card` tinyint(1) DEFAULT 0,
  `sales-edit_gift_card` tinyint(1) DEFAULT 0,
  `sales-delete_gift_card` tinyint(1) DEFAULT 0,
  `pos-index` tinyint(1) DEFAULT 0,
  `sales-return_sales` tinyint(1) DEFAULT 0,
  `reports-index` tinyint(1) DEFAULT 0,
  `reports-warehouse_stock` tinyint(1) DEFAULT 0,
  `reports-quantity_alerts` tinyint(1) DEFAULT 0,
  `reports-expiry_alerts` tinyint(1) DEFAULT 0,
  `reports-products` tinyint(1) DEFAULT 0,
  `reports-daily_sales` tinyint(1) DEFAULT 0,
  `reports-monthly_sales` tinyint(1) DEFAULT 0,
  `reports-sales` tinyint(1) DEFAULT 0,
  `reports-payments` tinyint(1) DEFAULT 0,
  `reports-purchases` tinyint(1) DEFAULT 0,
  `reports-profit_loss` tinyint(1) DEFAULT 0,
  `reports-customers` tinyint(1) DEFAULT 0,
  `reports-suppliers` tinyint(1) DEFAULT 0,
  `reports-staff` tinyint(1) DEFAULT 0,
  `reports-register` tinyint(1) DEFAULT 0,
  `sales-payments` tinyint(1) DEFAULT 0,
  `purchases-payments` tinyint(1) DEFAULT 0,
  `purchases-expenses` tinyint(1) DEFAULT 0,
  `products-adjustments` tinyint(1) NOT NULL DEFAULT 0,
  `bulk_actions` tinyint(1) NOT NULL DEFAULT 0,
  `customers-deposits` tinyint(1) NOT NULL DEFAULT 0,
  `customers-delete_deposit` tinyint(1) NOT NULL DEFAULT 0,
  `products-barcode` tinyint(1) NOT NULL DEFAULT 0,
  `purchases-return_purchases` tinyint(1) NOT NULL DEFAULT 0,
  `reports-expenses` tinyint(1) NOT NULL DEFAULT 0,
  `reports-daily_purchases` tinyint(1) DEFAULT 0,
  `reports-monthly_purchases` tinyint(1) DEFAULT 0,
  `products-stock_count` tinyint(1) DEFAULT 0,
  `edit_price` tinyint(1) DEFAULT 0,
  `returns-index` tinyint(1) DEFAULT 0,
  `returns-add` tinyint(1) DEFAULT 0,
  `returns-edit` tinyint(1) DEFAULT 0,
  `returns-delete` tinyint(1) DEFAULT 0,
  `returns-email` tinyint(1) DEFAULT 0,
  `returns-pdf` tinyint(1) DEFAULT 0,
  `reports-tax` tinyint(1) DEFAULT 0,
  `stock_request_view` tinyint(1) DEFAULT 0,
  `stock_request_approval` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_permissions`
--

INSERT INTO `sma_permissions` (`id`, `group_id`, `products-index`, `products-add`, `products-edit`, `products-delete`, `products-cost`, `products-price`, `quotes-index`, `quotes-add`, `quotes-edit`, `quotes-pdf`, `quotes-email`, `quotes-delete`, `sales-index`, `sales-add`, `sales-edit`, `sales-pdf`, `sales-email`, `sales-delete`, `purchases-index`, `purchases-add`, `purchases-edit`, `purchases-pdf`, `purchases-email`, `purchases-delete`, `transfers-index`, `transfers-add`, `transfers-edit`, `transfers-pdf`, `transfers-email`, `transfers-delete`, `customers-index`, `customers-add`, `customers-edit`, `customers-delete`, `suppliers-index`, `suppliers-add`, `suppliers-edit`, `suppliers-delete`, `sales-deliveries`, `sales-add_delivery`, `sales-edit_delivery`, `sales-delete_delivery`, `sales-email_delivery`, `sales-pdf_delivery`, `sales-gift_cards`, `sales-add_gift_card`, `sales-edit_gift_card`, `sales-delete_gift_card`, `pos-index`, `sales-return_sales`, `reports-index`, `reports-warehouse_stock`, `reports-quantity_alerts`, `reports-expiry_alerts`, `reports-products`, `reports-daily_sales`, `reports-monthly_sales`, `reports-sales`, `reports-payments`, `reports-purchases`, `reports-profit_loss`, `reports-customers`, `reports-suppliers`, `reports-staff`, `reports-register`, `sales-payments`, `purchases-payments`, `purchases-expenses`, `products-adjustments`, `bulk_actions`, `customers-deposits`, `customers-delete_deposit`, `products-barcode`, `purchases-return_purchases`, `reports-expenses`, `reports-daily_purchases`, `reports-monthly_purchases`, `products-stock_count`, `edit_price`, `returns-index`, `returns-add`, `returns-edit`, `returns-delete`, `returns-email`, `returns-pdf`, `reports-tax`, `stock_request_view`, `stock_request_approval`) VALUES
(1, 5, 1, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 0, 1, 1, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 1, 1, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1),
(2, 6, 1, 1, 1, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 1, 1, 0, 0, 1, 1, 1, 1, 1, 1, 1, NULL, 0, NULL, NULL, NULL, 0, 1, NULL, NULL, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, NULL, 1, 1, 1, 1, 1, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sma_pos_register`
--

CREATE TABLE `sma_pos_register` (
  `id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL,
  `cash_in_hand` decimal(25,4) NOT NULL,
  `status` varchar(10) NOT NULL,
  `total_cash` decimal(25,4) DEFAULT NULL,
  `total_cheques` int(11) DEFAULT NULL,
  `total_cc_slips` int(11) DEFAULT NULL,
  `total_cash_submitted` decimal(25,4) DEFAULT NULL,
  `total_cheques_submitted` int(11) DEFAULT NULL,
  `total_cc_slips_submitted` int(11) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `transfer_opened_bills` varchar(50) DEFAULT NULL,
  `closed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_pos_register`
--

INSERT INTO `sma_pos_register` (`id`, `date`, `user_id`, `cash_in_hand`, `status`, `total_cash`, `total_cheques`, `total_cc_slips`, `total_cash_submitted`, `total_cheques_submitted`, `total_cc_slips_submitted`, `note`, `closed_at`, `transfer_opened_bills`, `closed_by`) VALUES
(1, '2022-09-23 20:08:12', 1, '0.0000', 'open', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, '2023-02-06 16:28:12', 37, '0.0000', 'open', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, '2023-02-06 17:14:56', 38, '0.0000', 'open', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sma_pos_settings`
--

CREATE TABLE `sma_pos_settings` (
  `pos_id` int(1) NOT NULL,
  `cat_limit` int(11) NOT NULL,
  `pro_limit` int(11) NOT NULL,
  `default_category` int(11) NOT NULL,
  `default_customer` int(11) NOT NULL,
  `default_biller` int(11) NOT NULL,
  `display_time` varchar(3) NOT NULL DEFAULT 'yes',
  `cf_title1` varchar(255) DEFAULT NULL,
  `cf_title2` varchar(255) DEFAULT NULL,
  `cf_value1` varchar(255) DEFAULT NULL,
  `cf_value2` varchar(255) DEFAULT NULL,
  `receipt_printer` varchar(55) DEFAULT NULL,
  `cash_drawer_codes` varchar(55) DEFAULT NULL,
  `focus_add_item` varchar(55) DEFAULT NULL,
  `add_manual_product` varchar(55) DEFAULT NULL,
  `customer_selection` varchar(55) DEFAULT NULL,
  `add_customer` varchar(55) DEFAULT NULL,
  `toggle_category_slider` varchar(55) DEFAULT NULL,
  `toggle_subcategory_slider` varchar(55) DEFAULT NULL,
  `cancel_sale` varchar(55) DEFAULT NULL,
  `suspend_sale` varchar(55) DEFAULT NULL,
  `print_items_list` varchar(55) DEFAULT NULL,
  `finalize_sale` varchar(55) DEFAULT NULL,
  `today_sale` varchar(55) DEFAULT NULL,
  `open_hold_bills` varchar(55) DEFAULT NULL,
  `close_register` varchar(55) DEFAULT NULL,
  `keyboard` tinyint(1) NOT NULL,
  `pos_printers` varchar(255) DEFAULT NULL,
  `java_applet` tinyint(1) NOT NULL,
  `product_button_color` varchar(20) NOT NULL DEFAULT 'default',
  `tooltips` tinyint(1) DEFAULT 1,
  `paypal_pro` tinyint(1) DEFAULT 0,
  `stripe` tinyint(1) DEFAULT 0,
  `rounding` tinyint(1) DEFAULT 0,
  `char_per_line` tinyint(4) DEFAULT 42,
  `pin_code` varchar(20) DEFAULT NULL,
  `purchase_code` varchar(100) DEFAULT 'purchase_code',
  `envato_username` varchar(50) DEFAULT 'envato_username',
  `version` varchar(10) DEFAULT '3.4.53',
  `after_sale_page` tinyint(1) DEFAULT 0,
  `item_order` tinyint(1) DEFAULT 0,
  `authorize` tinyint(1) DEFAULT 0,
  `toggle_brands_slider` varchar(55) DEFAULT NULL,
  `remote_printing` tinyint(1) DEFAULT 1,
  `printer` int(11) DEFAULT NULL,
  `order_printers` varchar(55) DEFAULT NULL,
  `auto_print` tinyint(1) DEFAULT 0,
  `customer_details` tinyint(1) DEFAULT NULL,
  `local_printers` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_pos_settings`
--

INSERT INTO `sma_pos_settings` (`pos_id`, `cat_limit`, `pro_limit`, `default_category`, `default_customer`, `default_biller`, `display_time`, `cf_title1`, `cf_title2`, `cf_value1`, `cf_value2`, `receipt_printer`, `cash_drawer_codes`, `focus_add_item`, `add_manual_product`, `customer_selection`, `add_customer`, `toggle_category_slider`, `toggle_subcategory_slider`, `cancel_sale`, `suspend_sale`, `print_items_list`, `finalize_sale`, `today_sale`, `open_hold_bills`, `close_register`, `keyboard`, `pos_printers`, `java_applet`, `product_button_color`, `tooltips`, `paypal_pro`, `stripe`, `rounding`, `char_per_line`, `pin_code`, `purchase_code`, `envato_username`, `version`, `after_sale_page`, `item_order`, `authorize`, `toggle_brands_slider`, `remote_printing`, `printer`, `order_printers`, `auto_print`, `customer_details`, `local_printers`) VALUES
(1, 22, 20, 14, 527, 524, '1', 'GST Reg', 'VAT Reg', '123456789', '987654321', NULL, 'x1C', 'Ctrl+F3', 'Ctrl+Shift+M', 'Ctrl+Shift+C', 'Ctrl+Shift+A', 'Ctrl+F11', 'Ctrl+F12', 'F4', 'F7', 'F9', 'F8', 'Ctrl+F1', 'Ctrl+F2', 'Ctrl+F10', 1, NULL, 0, 'default', 1, 0, 0, 0, 42, NULL, 'purchase_code', 'envato_username', '3.4.53', 0, 0, 0, '', 1, NULL, 'null', 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sma_price_groups`
--

CREATE TABLE `sma_price_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_price_groups`
--

INSERT INTO `sma_price_groups` (`id`, `name`) VALUES
(1, 'Default');

-- --------------------------------------------------------

--
-- Table structure for table `sma_printers`
--

CREATE TABLE `sma_printers` (
  `id` int(11) NOT NULL,
  `title` varchar(55) NOT NULL,
  `type` varchar(25) NOT NULL,
  `profile` varchar(25) NOT NULL,
  `char_per_line` tinyint(3) UNSIGNED DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `ip_address` varbinary(45) DEFAULT NULL,
  `port` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sma_products`
--

CREATE TABLE `sma_products` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `unit` int(11) DEFAULT NULL,
  `cost` decimal(25,4) DEFAULT NULL,
  `price` decimal(25,4) NOT NULL,
  `alert_quantity` decimal(15,4) DEFAULT 20.0000,
  `image` varchar(255) DEFAULT 'no_image.png',
  `category_id` int(11) NOT NULL,
  `subcategory_id` int(11) DEFAULT NULL,
  `cf1` varchar(255) DEFAULT NULL,
  `cf2` varchar(255) DEFAULT NULL,
  `cf3` varchar(255) DEFAULT NULL,
  `cf4` varchar(255) DEFAULT NULL,
  `cf5` varchar(255) DEFAULT NULL,
  `cf6` varchar(255) DEFAULT NULL,
  `quantity` decimal(15,4) DEFAULT 0.0000,
  `tax_rate` int(11) DEFAULT NULL,
  `track_quantity` tinyint(1) DEFAULT 1,
  `details` varchar(1000) DEFAULT NULL,
  `warehouse` int(11) DEFAULT NULL,
  `barcode_symbology` varchar(55) NOT NULL DEFAULT 'code128',
  `file` varchar(100) DEFAULT NULL,
  `product_details` text DEFAULT NULL,
  `tax_method` tinyint(1) DEFAULT 0,
  `type` varchar(55) NOT NULL DEFAULT 'standard',
  `supplier1` int(11) DEFAULT NULL,
  `supplier1price` decimal(25,4) DEFAULT NULL,
  `supplier2` int(11) DEFAULT NULL,
  `supplier2price` decimal(25,4) DEFAULT NULL,
  `supplier3` int(11) DEFAULT NULL,
  `supplier3price` decimal(25,4) DEFAULT NULL,
  `supplier4` int(11) DEFAULT NULL,
  `supplier4price` decimal(25,4) DEFAULT NULL,
  `supplier5` int(11) DEFAULT NULL,
  `supplier5price` decimal(25,4) DEFAULT NULL,
  `promotion` tinyint(1) DEFAULT 0,
  `promo_price` decimal(25,4) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `supplier1_part_no` varchar(50) DEFAULT NULL,
  `supplier2_part_no` varchar(50) DEFAULT NULL,
  `supplier3_part_no` varchar(50) DEFAULT NULL,
  `supplier4_part_no` varchar(50) DEFAULT NULL,
  `supplier5_part_no` varchar(50) DEFAULT NULL,
  `sale_unit` int(11) DEFAULT NULL,
  `purchase_unit` int(11) DEFAULT NULL,
  `brand` int(11) DEFAULT NULL,
  `slug` varchar(55) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT NULL,
  `weight` decimal(10,4) DEFAULT NULL,
  `hsn_code` int(11) DEFAULT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  `hide` tinyint(1) NOT NULL DEFAULT 0,
  `second_name` varchar(255) DEFAULT NULL,
  `hide_pos` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_products`
--

INSERT INTO `sma_products` (`id`, `code`, `name`, `unit`, `cost`, `price`, `alert_quantity`, `image`, `category_id`, `subcategory_id`, `cf1`, `cf2`, `cf3`, `cf4`, `cf5`, `cf6`, `quantity`, `tax_rate`, `track_quantity`, `details`, `warehouse`, `barcode_symbology`, `file`, `product_details`, `tax_method`, `type`, `supplier1`, `supplier1price`, `supplier2`, `supplier2price`, `supplier3`, `supplier3price`, `supplier4`, `supplier4price`, `supplier5`, `supplier5price`, `promotion`, `promo_price`, `start_date`, `end_date`, `supplier1_part_no`, `supplier2_part_no`, `supplier3_part_no`, `supplier4_part_no`, `supplier5_part_no`, `sale_unit`, `purchase_unit`, `brand`, `slug`, `featured`, `weight`, `hsn_code`, `views`, `hide`, `second_name`, `hide_pos`) VALUES
(37, 'PDS001', 'KEROZ 0.5MG', 6, '1613.0000', '3225.0000', '10.0000', 'no_image.png', 15, NULL, '0', 'yes', '', '', '', '', '49.0000', 1, 1, '', NULL, 'code128', '', '', 1, 'standard', 57, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, 6, 6, 3, 'keroz-05mg', NULL, '0.0000', NULL, 0, 1, 'FINGOLIMOD', 0),
(38, 'PDS002', 'PLERUS PLUS', 6, '20.0000', '40.0000', '20000.0000', 'no_image.png', 14, NULL, '0', 'no', '', '', '', '', '99.0000', 1, 1, '', NULL, 'code128', '', '', 1, 'standard', 57, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, 6, 6, 3, 'plerus-plus', NULL, '0.0000', NULL, 0, 1, 'PELARGONIUM SIDOIDES', 0),
(39, 'PDS003', 'PLERUS', 6, '15.0000', '31.0000', '20000.0000', 'no_image.png', 14, NULL, '0', 'no', '', '', '', '', '100.0000', 1, 1, '', NULL, 'code128', '', '', 1, 'standard', 57, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, 6, 6, 3, 'plerus', NULL, '0.0000', NULL, 0, 1, 'PELARGONIUM SIDOIDES', 0),
(41, 'PDS005', 'TREDIA 120MG', 6, '182.0000', '364.0000', '10.0000', 'no_image.png', 15, NULL, '0', 'no', '', '', '', '', '23.0000', 1, 1, '', NULL, 'code128', '', '', 1, 'standard', 57, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, 6, 6, 3, 'tredia-120mg', NULL, '0.0000', NULL, 0, 1, 'DIMETHYL FUMARATE', 0),
(42, 'PDS006', 'TREDIA 240MG', 6, '1456.0000', '2912.0000', '20.0000', 'no_image.png', 15, NULL, '0', 'yes', '', '', '', '', '31.0000', 1, 1, '', NULL, 'code128', '', '', 1, 'standard', 57, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, 6, 6, 3, 'tredia-240mg', NULL, '0.0000', NULL, 0, 1, 'DIMETHYL FUMARATE', 0),
(43, 'PDS004', 'SULFAD 1GM', 6, '115.0000', '230.0000', '3.0000', 'dd22fc4600e730f8e5cffb3985990f3c.jpg', 14, NULL, '0', 'no', '', '', '', '', '85.0000', 1, 1, '', NULL, 'code128', '', '', 1, 'standard', 57, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, 6, 6, 3, 'sulfad-1gm', 1, '0.0000', NULL, 0, 0, 'ARTICHOKE,TURMERIC,LIQUORICE,MILK THISTLE', 0),
(45, '19752342', 'SULFAD 1GM', 6, '115.0000', '230.0000', '3.0000', '027aa43a3be05f6d5667d7058b35861a.jpg', 14, NULL, '6,8', 'no', 'abc', '', '', '', '0.0000', 1, 1, '', NULL, 'code128', '', '', 1, 'standard', 57, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, 0, 0, 3, '19752342', NULL, '0.5200', NULL, 0, 0, '', 0),
(46, 'IPR-1', 'Import Product 1', 6, '100.0000', '150.0000', '20.0000', 'no_image.png', 14, NULL, '0', 'no', 'Shelf - 1', 'CF4', 'CF5', 'CF6', '0.0000', 1, 1, '', NULL, 'code128', NULL, '', 1, 'standard', NULL, '0.0000', NULL, '0.0000', NULL, '0.0000', NULL, '0.0000', NULL, '0.0000', 0, NULL, NULL, NULL, '', '', '', '', '', 6, 6, 3, 'import-product-1', NULL, NULL, 0, 0, 0, 'Imp -1', 0),
(47, 'IPR-2', 'Import Product 2', 6, '80.0000', '100.0000', '20.0000', 'no_image.png', 14, NULL, '0', 'no', 'Shelf - 2', 'CF4', 'CF5', 'CF6', '0.0000', 1, 1, '', NULL, 'code128', NULL, '', 1, 'standard', NULL, '0.0000', NULL, '0.0000', NULL, '0.0000', NULL, '0.0000', NULL, '0.0000', 0, NULL, NULL, NULL, '', '', '', '', '', 6, 6, 3, 'import-product-2', NULL, NULL, 0, 0, 0, 'Imp -2', 0),
(48, 'IPR-3', 'Import Product 3', 6, '120.0000', '200.0000', '20.0000', 'no_image.png', 14, NULL, '0', 'no', 'Shelf - 3', 'CF4', 'CF5', 'CF6', '0.0000', 1, 1, '', NULL, 'code128', NULL, '', 1, 'standard', NULL, '0.0000', NULL, '0.0000', NULL, '0.0000', NULL, '0.0000', NULL, '0.0000', 0, NULL, NULL, NULL, '', '', '', '', '', 6, 6, 3, 'import-product-3', NULL, NULL, 0, 0, 0, 'Imp -3', 0);

-- --------------------------------------------------------

--
-- Table structure for table `sma_product_photos`
--

CREATE TABLE `sma_product_photos` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `photo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sma_product_prices`
--

CREATE TABLE `sma_product_prices` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `price_group_id` int(11) NOT NULL,
  `price` decimal(25,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sma_product_variants`
--

CREATE TABLE `sma_product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(55) NOT NULL,
  `cost` decimal(25,4) DEFAULT NULL,
  `price` decimal(25,4) DEFAULT NULL,
  `quantity` decimal(15,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_product_variants`
--

INSERT INTO `sma_product_variants` (`id`, `product_id`, `name`, `cost`, `price`, `quantity`) VALUES
(41, 43, 'EXPIRY DATE', NULL, '0.0000', '85.0000');

-- --------------------------------------------------------

--
-- Table structure for table `sma_promos`
--

CREATE TABLE `sma_promos` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `product2buy` int(11) NOT NULL,
  `product2get` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_promos`
--

INSERT INTO `sma_promos` (`id`, `name`, `product2buy`, `product2get`, `start_date`, `end_date`, `description`) VALUES
(1, 'Normal', 1, 3, '2022-11-17', '2022-11-25', '');

-- --------------------------------------------------------

--
-- Table structure for table `sma_purchases`
--

CREATE TABLE `sma_purchases` (
  `id` int(11) NOT NULL,
  `reference_no` varchar(55) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `supplier_id` int(11) NOT NULL,
  `supplier` varchar(55) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `note` varchar(1000) NOT NULL,
  `total` decimal(25,4) DEFAULT NULL,
  `product_discount` decimal(25,4) DEFAULT NULL,
  `order_discount_id` varchar(20) DEFAULT NULL,
  `order_discount` decimal(25,4) DEFAULT NULL,
  `total_discount` decimal(25,4) DEFAULT NULL,
  `product_tax` decimal(25,4) DEFAULT NULL,
  `order_tax_id` int(11) DEFAULT NULL,
  `order_tax` decimal(25,4) DEFAULT NULL,
  `total_tax` decimal(25,4) DEFAULT 0.0000,
  `shipping` decimal(25,4) DEFAULT 0.0000,
  `grand_total` decimal(25,4) NOT NULL,
  `paid` decimal(25,4) NOT NULL DEFAULT 0.0000,
  `status` varchar(55) DEFAULT '',
  `payment_status` varchar(20) DEFAULT 'pending',
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `attachment` varchar(55) DEFAULT NULL,
  `payment_term` tinyint(4) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `return_id` int(11) DEFAULT NULL,
  `surcharge` decimal(25,4) NOT NULL DEFAULT 0.0000,
  `return_purchase_ref` varchar(55) DEFAULT NULL,
  `purchase_id` int(11) DEFAULT NULL,
  `return_purchase_total` decimal(25,4) NOT NULL DEFAULT 0.0000,
  `cgst` decimal(25,4) DEFAULT NULL,
  `sgst` decimal(25,4) DEFAULT NULL,
  `igst` decimal(25,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_purchases`
--

INSERT INTO `sma_purchases` (`id`, `reference_no`, `date`, `supplier_id`, `supplier`, `warehouse_id`, `note`, `total`, `product_discount`, `order_discount_id`, `order_discount`, `total_discount`, `product_tax`, `order_tax_id`, `order_tax`, `total_tax`, `shipping`, `grand_total`, `paid`, `status`, `payment_status`, `created_by`, `updated_by`, `updated_at`, `attachment`, `payment_term`, `due_date`, `return_id`, `surcharge`, `return_purchase_ref`, `purchase_id`, `return_purchase_total`, `cgst`, `sgst`, `igst`) VALUES
(11, 'PPI0012022', '2022-12-28 16:04:00', 57, 'PHARMA PHARMACEUTICAL INDUSTRIES', 9, '', '869409.0000', '0.0000', '0', '0.0000', '0.0000', '0.0000', 1, '0.0000', '0.0000', '0.0000', '869409.0000', '869409.0000', 'received', 'paid', 1, NULL, NULL, '0', 127, '1970-01-01', NULL, '0.0000', NULL, NULL, '0.0000', NULL, NULL, NULL),
(12, 'PPI0022022', '2022-12-28 16:07:00', 57, 'PHARMA PHARMACEUTICAL INDUSTRIES', 9, '', '728625.0000', '0.0000', '0', '0.0000', '0.0000', '0.0000', 1, '0.0000', '0.0000', '0.0000', '728625.0000', '728625.0000', 'received', 'paid', 1, NULL, NULL, '0', 127, '2023-06-26', NULL, '0.0000', NULL, NULL, '0.0000', NULL, NULL, NULL),
(13, 'PPI10123001', '2023-01-08 16:59:00', 57, 'PHARMA PHARMACEUTICAL INDUSTRIES', 9, '', '150000.0000', '0.0000', '84.81%', '127215.0000', '127215.0000', '0.0000', 1, '0.0000', '0.0000', '0.0000', '22785.0000', '0.0000', 'received', 'pending', 1, 1, '2023-01-08 16:59:55', '0', 127, '2023-05-15', NULL, '0.0000', NULL, NULL, '0.0000', NULL, NULL, NULL),
(14, 'PPI01223001', '2023-01-09 17:43:00', 57, 'PHARMA PHARMACEUTICAL INDUSTRIES', 9, '', '345000.0000', '0.0000', '65.2%', '224940.0000', '224940.0000', '0.0000', 1, '0.0000', '0.0000', '0.0000', '120060.0000', '0.0000', 'received', 'pending', 1, NULL, NULL, '1', 127, '2023-07-08', NULL, '0.0000', NULL, NULL, '0.0000', NULL, NULL, NULL),
(15, '100000', '2023-02-06 18:26:00', 57, 'PHARMA PHARMACEUTICAL INDUSTRIES', 11, '', '11500.0000', '0.0000', '', '0.0000', '0.0000', '0.0000', 1, '0.0000', '0.0000', '0.0000', '11500.0000', '11500.0000', 'received', 'paid', 1, NULL, NULL, '0', 0, NULL, NULL, '0.0000', NULL, NULL, '0.0000', NULL, NULL, NULL),
(16, '123456789', '2023-02-08 19:51:00', 57, 'PHARMA PHARMACEUTICAL INDUSTRIES', 12, '', '3500.0000', '0.0000', '', '0.0000', '0.0000', '0.0000', 1, '0.0000', '0.0000', '0.0000', '3500.0000', '3500.0000', 'received', 'paid', 1, NULL, NULL, '0', 0, NULL, NULL, '0.0000', NULL, NULL, '0.0000', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sma_purchase_items`
--

CREATE TABLE `sma_purchase_items` (
  `id` int(11) NOT NULL,
  `purchase_id` int(11) DEFAULT NULL,
  `transfer_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `option_id` int(11) DEFAULT NULL,
  `net_unit_cost` decimal(25,4) NOT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `item_tax` decimal(25,4) DEFAULT NULL,
  `tax_rate_id` int(11) DEFAULT NULL,
  `tax` varchar(20) DEFAULT NULL,
  `discount` varchar(20) DEFAULT NULL,
  `item_discount` decimal(25,4) DEFAULT NULL,
  `expiry` date DEFAULT NULL,
  `subtotal` decimal(25,4) NOT NULL,
  `quantity_balance` decimal(15,4) DEFAULT 0.0000,
  `date` date NOT NULL,
  `status` varchar(50) NOT NULL,
  `unit_cost` decimal(25,4) DEFAULT NULL,
  `real_unit_cost` decimal(25,4) DEFAULT NULL,
  `quantity_received` decimal(15,4) DEFAULT NULL,
  `supplier_part_no` varchar(50) DEFAULT NULL,
  `purchase_item_id` int(11) DEFAULT NULL,
  `product_unit_id` int(11) DEFAULT NULL,
  `product_unit_code` varchar(10) DEFAULT NULL,
  `unit_quantity` decimal(15,4) NOT NULL,
  `gst` varchar(20) DEFAULT NULL,
  `cgst` decimal(25,4) DEFAULT NULL,
  `sgst` decimal(25,4) DEFAULT NULL,
  `igst` decimal(25,4) DEFAULT NULL,
  `base_unit_cost` decimal(25,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_purchase_items`
--

INSERT INTO `sma_purchase_items` (`id`, `purchase_id`, `transfer_id`, `product_id`, `product_code`, `product_name`, `option_id`, `net_unit_cost`, `quantity`, `warehouse_id`, `item_tax`, `tax_rate_id`, `tax`, `discount`, `item_discount`, `expiry`, `subtotal`, `quantity_balance`, `date`, `status`, `unit_cost`, `real_unit_cost`, `quantity_received`, `supplier_part_no`, `purchase_item_id`, `product_unit_id`, `product_unit_code`, `unit_quantity`, `gst`, `cgst`, `sgst`, `igst`, `base_unit_cost`) VALUES
(4, NULL, NULL, 3, 'TPR-11', 'Test Product 11', 3, '90.0000', '8.0000', 1, '0.0000', 1, '0', NULL, NULL, NULL, '720.0000', '0.0000', '2022-11-16', 'received', '90.0000', '90.0000', '8.0000', NULL, NULL, 2, 'pc', '8.0000', NULL, NULL, NULL, NULL, NULL),
(5, NULL, NULL, 1, '1234567879', 'Aloe Vera Juice [Pineapple Flavour] – Natural Hydrator, Better Liver Function & Nutritious Booster', NULL, '20.0000', '3.0000', 1, '0.0000', 1, '0', NULL, NULL, NULL, '60.0000', '0.0000', '2022-11-16', 'received', '20.0000', '20.0000', '3.0000', NULL, NULL, 1, 'test', '3.0000', NULL, NULL, NULL, NULL, NULL),
(7, NULL, NULL, 2, '554212121', 'Anti – Acne Serum – Brightens Skin, Fades Acne, Lighten Acne Scars & Control Excess Oil Production', NULL, '10.0000', '1.0000', 0, '0.0000', 1, '0', NULL, NULL, NULL, '10.0000', '0.0000', '2022-11-16', 'received', '10.0000', '10.0000', '1.0000', NULL, NULL, 1, 'test', '1.0000', NULL, NULL, NULL, NULL, NULL),
(23, NULL, NULL, 28, '5765765', 'sulfad2', 19, '120.0000', '100.0000', 3, '0.0000', 1, '0.0000', NULL, NULL, NULL, '12000.0000', '0.0000', '2022-12-17', 'received', '120.0000', NULL, '100.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(24, NULL, NULL, 28, '5765765', 'sulfad2', 23, '120.0000', '50.0000', 4, '0.0000', 1, '0.0000', NULL, NULL, NULL, '6000.0000', '0.0000', '2022-12-17', 'received', '120.0000', NULL, '50.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(25, NULL, NULL, 28, '5765765', 'sulfad2', 21, '120.0000', '100.0000', 4, '0.0000', 1, '0.0000', NULL, NULL, NULL, '12000.0000', '0.0000', '2022-12-17', 'received', '120.0000', NULL, '100.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(26, NULL, NULL, 26, '3w3w3', 'amr', 22, '200.0000', '80.0000', 3, '0.0000', 1, '0.0000', NULL, NULL, NULL, '16000.0000', '0.0000', '2022-12-17', 'received', '200.0000', NULL, '80.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(27, NULL, NULL, 26, '3w3w3', 'amr', 23, '200.0000', '70.0000', 3, '0.0000', 1, '0.0000', NULL, NULL, NULL, '14000.0000', '0.0000', '2022-12-17', 'received', '200.0000', NULL, '70.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(28, NULL, NULL, 26, '3w3w3', 'amr', 27, '200.0000', '90.0000', 4, '0.0000', 1, '0.0000', NULL, NULL, NULL, '18000.0000', '0.0000', '2022-12-17', 'received', '200.0000', NULL, '90.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(29, NULL, NULL, 26, '3w3w3', 'amr', 28, '200.0000', '50.0000', 4, '0.0000', 1, '0.0000', NULL, NULL, NULL, '10000.0000', '0.0000', '2022-12-17', 'received', '200.0000', NULL, '50.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(35, NULL, NULL, 3, 'TPR-11', 'Test Product 11', 3, '90.0000', '5.0000', 3, '0.0000', 1, '0', NULL, NULL, NULL, '450.0000', '0.0000', '2022-12-17', 'received', '90.0000', '90.0000', '5.0000', NULL, NULL, 2, 'pc', '5.0000', NULL, NULL, NULL, NULL, NULL),
(36, NULL, NULL, 28, '5765765', 'sulfad2', 21, '120.0000', '20.0000', 3, '0.0000', 1, '0', NULL, NULL, NULL, '2400.0000', '0.0000', '2022-12-17', 'received', '120.0000', '120.0000', '20.0000', NULL, NULL, 2, 'pc', '20.0000', NULL, NULL, NULL, NULL, NULL),
(37, NULL, NULL, 3, 'TPR-11', 'Test Product 11', 3, '90.0000', '5.0000', 4, '0.0000', 1, '0', NULL, NULL, NULL, '450.0000', '0.0000', '2022-12-17', 'received', '90.0000', '90.0000', '5.0000', NULL, NULL, 2, 'pc', '5.0000', NULL, NULL, NULL, NULL, NULL),
(40, NULL, NULL, 29, '45545', 'sulfad100', 27, '200.0000', '10.0000', 3, '0.0000', 1, '0.0000', NULL, NULL, NULL, '2000.0000', '0.0000', '2022-12-17', 'received', '200.0000', NULL, '10.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(41, NULL, NULL, 29, '45545', 'sulfad100', 40, '200.0000', '10.0000', 4, '0.0000', 1, '0.0000', NULL, NULL, NULL, '2000.0000', '0.0000', '2022-12-17', 'received', '200.0000', NULL, '10.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(42, NULL, NULL, 29, '45545', 'sulfad100', 34, '200.0000', '500.0000', 4, '0.0000', 1, '0.0000', NULL, NULL, NULL, '100000.0000', '0.0000', '2022-12-19', 'received', '200.0000', NULL, '500.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(43, NULL, NULL, 29, '45545', 'sulfad100', 42, '200.0000', '400.0000', 3, '0.0000', 1, '0.0000', NULL, NULL, NULL, '80000.0000', '0.0000', '2022-12-19', 'received', '200.0000', NULL, '400.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(44, NULL, NULL, 33, '84813010', 'Product test 1', 36, '10.0000', '10.0000', 1, '0.0000', 1, '0.0000', NULL, NULL, NULL, '100.0000', '0.0000', '2022-12-19', 'received', '10.0000', NULL, '10.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(45, NULL, NULL, 33, '84813010', 'Product test 1', 36, '10.0000', '10.0000', 2, '0.0000', 1, '0.0000', NULL, NULL, NULL, '100.0000', '0.0000', '2022-12-19', 'received', '10.0000', NULL, '10.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(46, NULL, NULL, 33, '84813010', 'Product test 1', 36, '10.0000', '5.0000', 3, '0.0000', 1, '0.0000', NULL, NULL, NULL, '50.0000', '0.0000', '2022-12-19', 'received', '10.0000', NULL, '5.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(47, NULL, NULL, 33, '84813010', 'Product test 1', 36, '10.0000', '10.0000', 4, '0.0000', 1, '0.0000', NULL, NULL, NULL, '100.0000', '0.0000', '2022-12-19', 'received', '10.0000', NULL, '10.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(48, NULL, NULL, 34, '64969409', 'SULFAD3', 37, '200.0000', '100.0000', 3, '0.0000', 1, '0.0000', NULL, NULL, NULL, '20000.0000', '0.0000', '2022-12-19', 'received', '200.0000', NULL, '100.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(49, NULL, NULL, 34, '64969409', 'SULFAD3', 37, '200.0000', '200.0000', 4, '0.0000', 1, '0.0000', NULL, NULL, NULL, '40000.0000', '0.0000', '2022-12-19', 'received', '200.0000', NULL, '200.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(50, NULL, NULL, 34, '64969409', 'SULFAD3', 38, '200.0000', '100.0000', 3, '0.0000', 1, '0.0000', NULL, NULL, NULL, '20000.0000', '0.0000', '2022-12-19', 'received', '200.0000', NULL, '100.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(51, NULL, NULL, 34, '64969409', 'SULFAD3', 38, '200.0000', '200.0000', 4, '0.0000', 1, '0.0000', NULL, NULL, NULL, '40000.0000', '0.0000', '2022-12-19', 'received', '200.0000', NULL, '200.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(53, NULL, NULL, 26, '3w3w3', 'amr', 22, '12800.0000', '90.0000', 4, '0.0000', 1, '0', NULL, NULL, NULL, '1152000.0000', '0.0000', '2022-12-22', 'received', '12800.0000', '12800.0000', '90.0000', NULL, NULL, 4, 'Car01', '90.0000', NULL, NULL, NULL, NULL, NULL),
(55, 11, NULL, 37, 'PDS001', 'KEROZ 0.5MG', NULL, '1613.0000', '49.0000', 9, '0.0000', 1, '0', '0', '0.0000', '2024-01-31', '79037.0000', '0.0000', '2022-12-28', 'received', '1613.0000', '1613.0000', '49.0000', NULL, NULL, 6, 'PACK', '49.0000', NULL, NULL, NULL, NULL, '1613.0000'),
(56, 11, NULL, 38, 'PDS002', 'PLERUS PLUS', NULL, '20.0000', '32373.0000', 9, '0.0000', 1, '0', '0', '0.0000', '2024-06-30', '647460.0000', '0.0000', '2022-12-28', 'received', '20.0000', '20.0000', '32373.0000', NULL, NULL, 6, 'PACK', '32373.0000', NULL, NULL, NULL, NULL, '20.0000'),
(57, 11, NULL, 39, 'PDS003', 'PLERUS', NULL, '15.0000', '83.0000', 9, '0.0000', 1, '0', '0', '0.0000', '2024-06-30', '1245.0000', '0.0000', '2022-12-28', 'received', '15.0000', '15.0000', '83.0000', NULL, NULL, 6, 'PACK', '83.0000', NULL, NULL, NULL, NULL, '15.0000'),
(58, 11, NULL, 40, 'PDS004', 'SULFAD 1GM', NULL, '115.0000', '803.0000', 9, '0.0000', 1, '0', '0', '0.0000', '2023-02-28', '92345.0000', '0.0000', '2022-12-28', 'received', '115.0000', '115.0000', '803.0000', NULL, NULL, 6, 'PACK', '803.0000', NULL, NULL, NULL, NULL, '115.0000'),
(59, 11, NULL, 41, 'PDS005', 'TREDIA 120MG', NULL, '182.0000', '23.0000', 9, '0.0000', 1, '0', '0', '0.0000', '2023-08-31', '4186.0000', '0.0000', '2022-12-28', 'received', '182.0000', '182.0000', '23.0000', NULL, NULL, 6, 'PACK', '23.0000', NULL, NULL, NULL, NULL, '182.0000'),
(60, 11, NULL, 42, 'PDS006', 'TREDIA 240MG', NULL, '1456.0000', '31.0000', 9, '0.0000', 1, '0', '0', '0.0000', '2023-08-31', '45136.0000', '0.0000', '2022-12-28', 'received', '1456.0000', '1456.0000', '31.0000', NULL, NULL, 6, 'PACK', '31.0000', NULL, NULL, NULL, NULL, '1456.0000'),
(61, 12, NULL, 38, 'PDS002', 'PLERUS PLUS', NULL, '20.0000', '120.0000', 9, '0.0000', 1, '0', '0', '0.0000', '2023-02-28', '2400.0000', '0.0000', '2022-12-28', 'received', '20.0000', '20.0000', '120.0000', NULL, NULL, 6, 'PACK', '120.0000', NULL, NULL, NULL, NULL, '20.0000'),
(62, 12, NULL, 40, 'PDS004', 'SULFAD 1GM', NULL, '115.0000', '6315.0000', 9, '0.0000', 1, '0', '0', '0.0000', '2023-06-30', '726225.0000', '0.0000', '2022-12-28', 'received', '115.0000', '115.0000', '6315.0000', NULL, NULL, 6, 'PACK', '6315.0000', NULL, NULL, NULL, NULL, '115.0000'),
(63, NULL, NULL, 40, 'PDS004', 'SULFAD 1GM', 39, '115.0000', '6315.0000', 9, '0.0000', 1, '0.0000', NULL, NULL, NULL, '726225.0000', '0.0000', '2022-12-28', 'received', '115.0000', NULL, '6315.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(64, NULL, NULL, 40, 'PDS004', 'SULFAD 1GM', 63, '115.0000', '803.0000', 9, '0.0000', 1, '0.0000', NULL, NULL, NULL, '92345.0000', '0.0000', '2022-12-28', 'received', '115.0000', NULL, '803.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(65, NULL, NULL, 43, 'PDS004', 'SULFAD 1GM', 41, '115.0000', '6315.0000', 9, '0.0000', 1, '0.0000', NULL, NULL, NULL, '726225.0000', '0.0000', '2022-12-28', 'received', '115.0000', NULL, '6315.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(66, NULL, NULL, 43, 'PDS004', 'SULFAD 1GM', 41, '115.0000', '803.0000', 9, '0.0000', 1, '0.0000', NULL, NULL, NULL, '92345.0000', '0.0000', '2022-12-28', 'received', '115.0000', NULL, '803.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL),
(68, 13, NULL, 39, 'PDS003', 'PLERUS', NULL, '15.0000', '10000.0000', 9, '0.0000', 1, '0', '0', '0.0000', '2024-08-31', '150000.0000', '0.0000', '2023-01-08', 'received', '15.0000', '15.0000', '10000.0000', NULL, NULL, 6, 'PACK', '10000.0000', NULL, NULL, NULL, NULL, '15.0000'),
(69, 14, NULL, 43, 'PDS004', 'SULFAD 1GM', 41, '115.0000', '3000.0000', 9, '0.0000', 1, '0', '0', '0.0000', '2024-01-31', '345000.0000', '0.0000', '2023-01-09', 'received', '115.0000', '115.0000', '3000.0000', NULL, NULL, 6, 'PACK', '3000.0000', NULL, NULL, NULL, NULL, '115.0000'),
(70, 15, NULL, 43, 'PDS004', 'SULFAD 1GM', 41, '115.0000', '100.0000', 11, '0.0000', 1, '0', '0', '0.0000', '2023-07-13', '11500.0000', '84.0000', '2023-02-06', 'received', '115.0000', '115.0000', '100.0000', NULL, NULL, 6, 'PACK', '100.0000', NULL, NULL, NULL, NULL, '115.0000'),
(71, NULL, 5, 43, 'PDS004', 'SULFAD 1GM', 41, '115.0000', '10.0000', 12, '0.0000', 1, '0', NULL, NULL, '2025-07-10', '1150.0000', '0.0000', '2023-02-06', 'received', '115.0000', '115.0000', NULL, NULL, NULL, 6, 'PACK', '10.0000', NULL, NULL, NULL, NULL, NULL),
(72, 16, NULL, 39, 'PDS003', 'PLERUS', NULL, '15.0000', '100.0000', 12, '0.0000', 1, '0', '0', '0.0000', NULL, '1500.0000', '100.0000', '2023-02-08', 'received', '15.0000', '15.0000', '100.0000', NULL, NULL, 6, 'PACK', '100.0000', NULL, NULL, NULL, NULL, '15.0000'),
(73, 16, NULL, 38, 'PDS002', 'PLERUS PLUS', NULL, '20.0000', '100.0000', 12, '0.0000', 1, '0', '0', '0.0000', NULL, '2000.0000', '99.0000', '2023-02-08', 'received', '20.0000', '20.0000', '100.0000', NULL, NULL, 6, 'PACK', '100.0000', NULL, NULL, NULL, NULL, '20.0000'),
(74, NULL, NULL, 43, 'PDS004', 'SULFAD 1GM', 41, '115.0000', '1.0000', 11, '0.0000', 1, '0', NULL, NULL, NULL, '115.0000', '1.0000', '2023-02-22', 'received', '115.0000', '115.0000', '1.0000', NULL, NULL, 6, 'PACK', '1.0000', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sma_quotes`
--

CREATE TABLE `sma_quotes` (
  `id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `reference_no` varchar(55) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `customer` varchar(55) NOT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `biller_id` int(11) NOT NULL,
  `biller` varchar(55) NOT NULL,
  `note` varchar(1000) DEFAULT NULL,
  `internal_note` varchar(1000) DEFAULT NULL,
  `total` decimal(25,4) NOT NULL,
  `product_discount` decimal(25,4) DEFAULT 0.0000,
  `order_discount` decimal(25,4) DEFAULT NULL,
  `order_discount_id` varchar(20) DEFAULT NULL,
  `total_discount` decimal(25,4) DEFAULT 0.0000,
  `product_tax` decimal(25,4) DEFAULT 0.0000,
  `order_tax_id` int(11) DEFAULT NULL,
  `order_tax` decimal(25,4) DEFAULT NULL,
  `total_tax` decimal(25,4) DEFAULT NULL,
  `shipping` decimal(25,4) DEFAULT 0.0000,
  `grand_total` decimal(25,4) NOT NULL,
  `status` varchar(20) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `attachment` varchar(55) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `supplier` varchar(55) DEFAULT NULL,
  `hash` varchar(255) DEFAULT NULL,
  `cgst` decimal(25,4) DEFAULT NULL,
  `sgst` decimal(25,4) DEFAULT NULL,
  `igst` decimal(25,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sma_quote_items`
--

CREATE TABLE `sma_quote_items` (
  `id` int(11) NOT NULL,
  `quote_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_code` varchar(55) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_type` varchar(20) DEFAULT NULL,
  `option_id` int(11) DEFAULT NULL,
  `net_unit_price` decimal(25,4) NOT NULL,
  `unit_price` decimal(25,4) DEFAULT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `item_tax` decimal(25,4) DEFAULT NULL,
  `tax_rate_id` int(11) DEFAULT NULL,
  `tax` varchar(55) DEFAULT NULL,
  `discount` varchar(55) DEFAULT NULL,
  `item_discount` decimal(25,4) DEFAULT NULL,
  `subtotal` decimal(25,4) NOT NULL,
  `serial_no` varchar(255) DEFAULT NULL,
  `real_unit_price` decimal(25,4) DEFAULT NULL,
  `product_unit_id` int(11) DEFAULT NULL,
  `product_unit_code` varchar(10) DEFAULT NULL,
  `unit_quantity` decimal(15,4) NOT NULL,
  `gst` varchar(20) DEFAULT NULL,
  `cgst` decimal(25,4) DEFAULT NULL,
  `sgst` decimal(25,4) DEFAULT NULL,
  `igst` decimal(25,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sma_refund`
--

CREATE TABLE `sma_refund` (
  `id` int(11) NOT NULL,
  `order_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `req_dates` date NOT NULL,
  `reason_refund` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `refund_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in-progress',
  `responseStatusCode` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responseStatusDescription` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responseTransactionId` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responseOriginalTransactionID` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responseMerchantId` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responseMessageId` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responseAmount` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responseCurrencyISOCode` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responseSecureHash` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refund_datetime` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sma_refund`
--

INSERT INTO `sma_refund` (`id`, `order_id`, `user_id`, `req_dates`, `reason_refund`, `notes`, `refund_status`, `responseStatusCode`, `responseStatusDescription`, `responseTransactionId`, `responseOriginalTransactionID`, `responseMerchantId`, `responseMessageId`, `responseAmount`, `responseCurrencyISOCode`, `responseSecureHash`, `refund_datetime`) VALUES
(18, '41', '10', '2022-12-16', 'Product doesn\\\'t work', 'test', 'failed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, '45', '10', '2022-12-16', 'Wrong Product Delivery', 'test', 'success', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, '44', '10', '2022-12-16', 'Product doesn\\\'t work', 'test', 'cancel', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sma_returns`
--

CREATE TABLE `sma_returns` (
  `id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `reference_no` varchar(55) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `customer` varchar(55) NOT NULL,
  `biller_id` int(11) NOT NULL,
  `biller` varchar(55) NOT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `note` varchar(1000) DEFAULT NULL,
  `staff_note` varchar(1000) DEFAULT NULL,
  `total` decimal(25,4) NOT NULL,
  `product_discount` decimal(25,4) DEFAULT 0.0000,
  `order_discount_id` varchar(20) DEFAULT NULL,
  `total_discount` decimal(25,4) DEFAULT 0.0000,
  `order_discount` decimal(25,4) DEFAULT 0.0000,
  `product_tax` decimal(25,4) DEFAULT 0.0000,
  `order_tax_id` int(11) DEFAULT NULL,
  `order_tax` decimal(25,4) DEFAULT 0.0000,
  `total_tax` decimal(25,4) DEFAULT 0.0000,
  `grand_total` decimal(25,4) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `total_items` smallint(6) DEFAULT NULL,
  `paid` decimal(25,4) DEFAULT 0.0000,
  `surcharge` decimal(25,4) NOT NULL DEFAULT 0.0000,
  `attachment` varchar(55) DEFAULT NULL,
  `hash` varchar(255) DEFAULT NULL,
  `cgst` decimal(25,4) DEFAULT NULL,
  `sgst` decimal(25,4) DEFAULT NULL,
  `igst` decimal(25,4) DEFAULT NULL,
  `shipping` decimal(25,4) DEFAULT 0.0000
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sma_return_items`
--

CREATE TABLE `sma_return_items` (
  `id` int(11) NOT NULL,
  `return_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `product_code` varchar(55) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_type` varchar(20) DEFAULT NULL,
  `option_id` int(11) DEFAULT NULL,
  `net_unit_price` decimal(25,4) NOT NULL,
  `unit_price` decimal(25,4) DEFAULT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `item_tax` decimal(25,4) DEFAULT NULL,
  `tax_rate_id` int(11) DEFAULT NULL,
  `tax` varchar(55) DEFAULT NULL,
  `discount` varchar(55) DEFAULT NULL,
  `item_discount` decimal(25,4) DEFAULT NULL,
  `subtotal` decimal(25,4) NOT NULL,
  `serial_no` varchar(255) DEFAULT NULL,
  `real_unit_price` decimal(25,4) DEFAULT NULL,
  `product_unit_id` int(11) DEFAULT NULL,
  `product_unit_code` varchar(10) DEFAULT NULL,
  `unit_quantity` decimal(15,4) NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `gst` varchar(20) DEFAULT NULL,
  `cgst` decimal(25,4) DEFAULT NULL,
  `sgst` decimal(25,4) DEFAULT NULL,
  `igst` decimal(25,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sma_sales`
--

CREATE TABLE `sma_sales` (
  `id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `reference_no` varchar(55) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `customer` varchar(55) NOT NULL,
  `biller_id` int(11) NOT NULL,
  `biller` varchar(55) NOT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `note` varchar(1000) DEFAULT NULL,
  `staff_note` varchar(1000) DEFAULT NULL,
  `total` decimal(25,4) NOT NULL,
  `product_discount` decimal(25,4) DEFAULT 0.0000,
  `order_discount_id` varchar(20) DEFAULT NULL,
  `total_discount` decimal(25,4) DEFAULT 0.0000,
  `order_discount` decimal(25,4) DEFAULT 0.0000,
  `product_tax` decimal(25,4) DEFAULT 0.0000,
  `order_tax_id` int(11) DEFAULT NULL,
  `order_tax` decimal(25,4) DEFAULT 0.0000,
  `total_tax` decimal(25,4) DEFAULT 0.0000,
  `shipping` decimal(25,4) DEFAULT 0.0000,
  `grand_total` decimal(25,4) NOT NULL,
  `sale_status` varchar(20) DEFAULT NULL,
  `payment_status` varchar(20) DEFAULT NULL,
  `payment_term` tinyint(4) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `total_items` smallint(6) DEFAULT NULL,
  `pos` tinyint(1) NOT NULL DEFAULT 0,
  `paid` decimal(25,4) DEFAULT 0.0000,
  `return_id` int(11) DEFAULT NULL,
  `surcharge` decimal(25,4) NOT NULL DEFAULT 0.0000,
  `attachment` varchar(500) DEFAULT NULL,
  `return_sale_ref` varchar(55) DEFAULT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `return_sale_total` decimal(25,4) NOT NULL DEFAULT 0.0000,
  `rounding` decimal(10,4) DEFAULT NULL,
  `suspend_note` varchar(255) DEFAULT NULL,
  `api` tinyint(1) DEFAULT 0,
  `shop` tinyint(1) DEFAULT 0,
  `address_id` int(11) DEFAULT NULL,
  `reserve_id` int(11) DEFAULT NULL,
  `hash` varchar(255) DEFAULT NULL,
  `manual_payment` varchar(55) DEFAULT NULL,
  `cgst` decimal(25,4) DEFAULT NULL,
  `sgst` decimal(25,4) DEFAULT NULL,
  `igst` decimal(25,4) DEFAULT NULL,
  `payment_method` varchar(55) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_sales`
--

INSERT INTO `sma_sales` (`id`, `date`, `reference_no`, `customer_id`, `customer`, `biller_id`, `biller`, `warehouse_id`, `note`, `staff_note`, `total`, `product_discount`, `order_discount_id`, `total_discount`, `order_discount`, `product_tax`, `order_tax_id`, `order_tax`, `total_tax`, `shipping`, `grand_total`, `sale_status`, `payment_status`, `payment_term`, `due_date`, `created_by`, `updated_by`, `updated_at`, `total_items`, `pos`, `paid`, `return_id`, `surcharge`, `attachment`, `return_sale_ref`, `sale_id`, `return_sale_total`, `rounding`, `suspend_note`, `api`, `shop`, `address_id`, `reserve_id`, `hash`, `manual_payment`, `cgst`, `sgst`, `igst`, `payment_method`) VALUES
(114, '2023-01-01 15:26:28', 'SALE2023/01/0097', 526, 'Pharma Drug Store', 524, 'PHARMA DRUG STORE CO.', 9, NULL, NULL, '230.0000', '0.0000', NULL, '0.0000', '0.0000', '0.0000', 1, '0.0000', '0.0000', '24.0000', '254.0000', 'completed', 'paid', NULL, NULL, NULL, NULL, NULL, 1, 0, '254.0000', NULL, '0.0000', NULL, NULL, NULL, '0.0000', NULL, NULL, 0, 1, 108, NULL, '3adc9fac7ae367b24ed369c686737cbd32ca856d8f72f0872479c15447aa751e', NULL, NULL, NULL, NULL, 'directpay'),
(116, '2023-01-04 14:20:15', 'SALE2023/01/0099', 526, 'Pharma Drug Store', 524, 'PHARMA DRUG STORE CO.', 9, NULL, NULL, '230.0000', '0.0000', NULL, '0.0000', '0.0000', '0.0000', 1, '0.0000', '0.0000', '24.0000', '254.0000', 'completed', 'paid', NULL, NULL, NULL, NULL, NULL, 1, 0, '254.0000', NULL, '0.0000', NULL, NULL, NULL, '0.0000', NULL, NULL, 0, 1, 110, NULL, '1170899425c75d9000f3d7f22d54c97fb9dfc99a21600e2354b9eec31ea77954', NULL, NULL, NULL, NULL, 'directpay'),
(117, '2023-01-09 18:26:41', 'SALE2023/01/0100', 525, 'Pharma Drug Store', 524, 'PHARMA DRUG STORE CO.', 9, NULL, NULL, '230.0000', '0.0000', NULL, '0.0000', '0.0000', '0.0000', 1, '0.0000', '0.0000', '24.0000', '254.0000', 'completed', 'paid', NULL, NULL, NULL, NULL, NULL, 1, 0, '254.0000', NULL, '0.0000', NULL, NULL, NULL, '0.0000', NULL, NULL, 0, 1, 111, NULL, 'b0db544a5989617929a38ab948f3448b237a10d50d351888283dac8c0679fae7', NULL, NULL, NULL, NULL, 'directpay'),
(118, '2023-01-11 14:55:26', 'SALE/POS2023/01/0002', 527, 'WALK-IN CUSTOMER', 524, 'PHARMA DRUG STORE CO.', 9, '', '', '230.0000', '0.0000', '', '0.0000', '0.0000', '0.0000', 1, '0.0000', '0.0000', '0.0000', '230.0000', 'completed', 'paid', 0, NULL, 1, NULL, NULL, 1, 1, '230.0000', NULL, '0.0000', NULL, NULL, NULL, '0.0000', '0.0000', NULL, 0, 0, NULL, NULL, 'de192baed57e5b4ff9be5628a1895ed8a28f1ea7d3ab1c3d2748f89452bc4ee0', NULL, NULL, NULL, NULL, NULL),
(119, '2023-01-12 05:03:56', 'SALE2023/01/0101', 525, 'Pharma Drug Store', 524, 'PHARMA DRUG STORE CO.', 9, '', NULL, '230.0000', '0.0000', NULL, '0.0000', '0.0000', '0.0000', 1, '0.0000', '0.0000', '24.0000', '254.0000', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, 1, 0, '0.0000', NULL, '0.0000', NULL, NULL, NULL, '0.0000', NULL, NULL, 0, 1, 112, NULL, 'c9e7aac09bd254bfeac7f3b7c1d4407c5eb69f0f19c1cab2f3d80eea8fc709f0', NULL, NULL, NULL, NULL, 'directpay'),
(120, '2023-01-12 16:57:41', 'SALE2023/01/0102', 529, 'Pharma Drug Store', 524, 'PHARMA DRUG STORE CO.', 9, '', NULL, '230.0000', '0.0000', NULL, '0.0000', '0.0000', '0.0000', 1, '0.0000', '0.0000', '24.0000', '254.0000', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, 1, 0, '0.0000', NULL, '0.0000', NULL, NULL, NULL, '0.0000', NULL, NULL, 0, 1, 113, NULL, '92e7c81883fddd2a3bc8f8bd2b67c8f8df506ea24b995be68215f7a32c8907a5', NULL, NULL, NULL, NULL, 'directpay'),
(121, '2023-01-12 17:13:03', 'SALE2023/01/0103', 529, 'Pharma Drug Store', 524, 'PHARMA DRUG STORE CO.', 9, '', NULL, '230.0000', '0.0000', NULL, '0.0000', '0.0000', '0.0000', 1, '0.0000', '0.0000', '24.0000', '254.0000', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, 1, 0, '0.0000', NULL, '0.0000', NULL, NULL, NULL, '0.0000', NULL, NULL, 0, 1, 114, NULL, '04d6c20d7d8b1c7cf48dbefe2207e7142f614d5f570e8cc19518e428924b9db1', NULL, NULL, NULL, NULL, 'directpay'),
(122, '2023-01-18 22:58:12', 'SALE2023/01/0104', 525, 'Pharma Drug Store', 524, 'PHARMA DRUG STORE CO.', 9, '', NULL, '230.0000', '0.0000', NULL, '0.0000', '0.0000', '0.0000', 1, '0.0000', '0.0000', '24.0000', '254.0000', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, 1, 0, '0.0000', NULL, '0.0000', NULL, NULL, NULL, '0.0000', NULL, NULL, 0, 1, 115, NULL, '75e89a33dd267545e64aed3789b0f781a84f8b4d7077cd2f85a77a11123a5682', NULL, NULL, NULL, NULL, 'directpay'),
(123, '2023-01-18 23:19:30', 'SALE2023/01/0105', 525, 'Pharma Drug Store', 524, 'PHARMA DRUG STORE CO.', 9, NULL, NULL, '230.0000', '0.0000', NULL, '0.0000', '0.0000', '0.0000', 1, '0.0000', '0.0000', '24.0000', '254.0000', 'completed', 'paid', NULL, NULL, NULL, NULL, NULL, 1, 0, '254.0000', NULL, '0.0000', NULL, NULL, NULL, '0.0000', NULL, NULL, 0, 1, 116, NULL, 'f77165b79148e9f0f96f6ee096d3ce32f138c18ee1e311922ee698217bc94f32', NULL, NULL, NULL, NULL, 'directpay'),
(124, '2023-02-07 18:37:29', 'SALE/POS2023/02/0003', 527, 'WALK-IN CUSTOMER', 524, 'PHARMA DRUG STORE CO.', 12, '', '', '230.0000', '0.0000', '', '0.0000', '0.0000', '0.0000', 1, '0.0000', '0.0000', '0.0000', '230.0000', 'completed', 'paid', 0, NULL, 1, NULL, NULL, 1, 1, '230.0000', NULL, '0.0000', NULL, NULL, NULL, '0.0000', '0.0000', NULL, 0, 0, NULL, NULL, 'b7a9679003a20efbfa28c23c0414d26f51b6f7eac153b562b919b5e57767feb6', NULL, NULL, NULL, NULL, NULL),
(125, '2023-02-08 19:48:38', 'SALE/POS2023/02/0004', 527, 'WALK-IN CUSTOMER', 524, 'PHARMA DRUG STORE CO.', 12, '', '', '270.0000', '0.0000', '', '0.0000', '0.0000', '0.0000', 1, '0.0000', '0.0000', '0.0000', '270.0000', 'completed', 'paid', 0, NULL, 38, NULL, NULL, 2, 1, '270.0000', NULL, '0.0000', NULL, NULL, NULL, '0.0000', '0.0000', NULL, 0, 0, NULL, NULL, 'd0016850283b49fb45a476e1309fa0c7fe0cfb982b8d99a5b13a635718a160ff', NULL, NULL, NULL, NULL, NULL),
(126, '2023-02-14 17:36:33', 'SALE/POS2023/02/0005', 527, 'WALK-IN CUSTOMER', 524, 'PHARMA DRUG STORE CO.', 12, '', '', '1380.0000', '0.0000', '', '0.0000', '0.0000', '0.0000', 1, '0.0000', '0.0000', '0.0000', '1380.0000', 'completed', 'paid', 0, NULL, 38, NULL, NULL, 6, 1, '1380.0000', NULL, '0.0000', NULL, NULL, NULL, '0.0000', '0.0000', NULL, 0, 0, NULL, NULL, '907fb2a383bcb973b2abf4174e2d43b6546dbbafcc4a2ce7170122a40ccd83ba', NULL, NULL, NULL, NULL, NULL),
(127, '2023-02-14 20:00:47', 'SALE/POS2023/02/0006', 527, 'WALK-IN CUSTOMER', 524, 'PHARMA DRUG STORE CO.', 12, '', '', '460.0000', '0.0000', '', '0.0000', '0.0000', '0.0000', 1, '0.0000', '0.0000', '0.0000', '460.0000', 'completed', 'paid', 0, NULL, 38, NULL, NULL, 2, 1, '460.0000', NULL, '0.0000', NULL, NULL, NULL, '0.0000', '0.0000', NULL, 0, 0, NULL, NULL, '84ca723efbaaf5704df92b3e5154c0c480929f4ad8b5cc97c27c42243fa94082', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sma_sale_items`
--

CREATE TABLE `sma_sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `product_code` varchar(55) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_type` varchar(20) DEFAULT NULL,
  `option_id` int(11) DEFAULT NULL,
  `net_unit_price` decimal(25,4) NOT NULL,
  `unit_price` decimal(25,4) DEFAULT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `item_tax` decimal(25,4) DEFAULT NULL,
  `tax_rate_id` int(11) DEFAULT NULL,
  `tax` varchar(55) DEFAULT NULL,
  `discount` varchar(55) DEFAULT NULL,
  `item_discount` decimal(25,4) DEFAULT NULL,
  `subtotal` decimal(25,4) NOT NULL,
  `serial_no` varchar(255) DEFAULT NULL,
  `real_unit_price` decimal(25,4) DEFAULT NULL,
  `sale_item_id` int(11) DEFAULT NULL,
  `product_unit_id` int(11) DEFAULT NULL,
  `product_unit_code` varchar(10) DEFAULT NULL,
  `unit_quantity` decimal(15,4) NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `gst` varchar(20) DEFAULT NULL,
  `cgst` decimal(25,4) DEFAULT NULL,
  `sgst` decimal(25,4) DEFAULT NULL,
  `igst` decimal(25,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_sale_items`
--

INSERT INTO `sma_sale_items` (`id`, `sale_id`, `product_id`, `product_code`, `product_name`, `product_type`, `option_id`, `net_unit_price`, `unit_price`, `quantity`, `warehouse_id`, `item_tax`, `tax_rate_id`, `tax`, `discount`, `item_discount`, `subtotal`, `serial_no`, `real_unit_price`, `sale_item_id`, `product_unit_id`, `product_unit_code`, `unit_quantity`, `comment`, `gst`, `cgst`, `sgst`, `igst`) VALUES
(111, 114, 43, 'PDS004', 'SULFAD 1GM', 'standard', 41, '230.0000', '230.0000', '1.0000', 9, '0.0000', 1, '0', NULL, '0.0000', '230.0000', NULL, '230.0000', NULL, 6, 'PACK', '1.0000', NULL, NULL, NULL, NULL, NULL),
(113, 116, 43, 'PDS004', 'SULFAD 1GM', 'standard', 41, '230.0000', '230.0000', '1.0000', 9, '0.0000', 1, '0', NULL, '0.0000', '230.0000', NULL, '230.0000', NULL, 6, 'PACK', '1.0000', NULL, NULL, NULL, NULL, NULL),
(114, 117, 43, 'PDS004', 'SULFAD 1GM', 'standard', 41, '230.0000', '230.0000', '1.0000', 9, '0.0000', 1, '0', NULL, '0.0000', '230.0000', NULL, '230.0000', NULL, 6, 'PACK', '1.0000', NULL, NULL, NULL, NULL, NULL),
(115, 118, 43, 'PDS004', 'SULFAD 1GM', 'standard', 41, '230.0000', '230.0000', '1.0000', 9, '0.0000', 1, '0', '0', '0.0000', '230.0000', '', '230.0000', NULL, 6, 'PACK', '1.0000', '', NULL, NULL, NULL, NULL),
(116, 119, 43, 'PDS004', 'SULFAD 1GM', 'standard', 41, '230.0000', '230.0000', '1.0000', 9, '0.0000', 1, '0', NULL, '0.0000', '230.0000', NULL, '230.0000', NULL, 6, 'PACK', '1.0000', NULL, NULL, NULL, NULL, NULL),
(117, 120, 43, 'PDS004', 'SULFAD 1GM', 'standard', 41, '230.0000', '230.0000', '1.0000', 9, '0.0000', 1, '0', NULL, '0.0000', '230.0000', NULL, '230.0000', NULL, 6, 'PACK', '1.0000', NULL, NULL, NULL, NULL, NULL),
(118, 121, 43, 'PDS004', 'SULFAD 1GM', 'standard', 41, '230.0000', '230.0000', '1.0000', 9, '0.0000', 1, '0', NULL, '0.0000', '230.0000', NULL, '230.0000', NULL, 6, 'PACK', '1.0000', NULL, NULL, NULL, NULL, NULL),
(119, 122, 43, 'PDS004', 'SULFAD 1GM', 'standard', 41, '230.0000', '230.0000', '1.0000', 9, '0.0000', 1, '0', NULL, '0.0000', '230.0000', NULL, '230.0000', NULL, 6, 'PACK', '1.0000', NULL, NULL, NULL, NULL, NULL),
(120, 123, 43, 'PDS004', 'SULFAD 1GM', 'standard', 41, '230.0000', '230.0000', '1.0000', 9, '0.0000', 1, '0', NULL, '0.0000', '230.0000', NULL, '230.0000', NULL, 6, 'PACK', '1.0000', NULL, NULL, NULL, NULL, NULL),
(121, 124, 43, 'PDS004', 'SULFAD 1GM', 'standard', 41, '230.0000', '230.0000', '1.0000', 12, '0.0000', 1, '0', '0', '0.0000', '230.0000', '', '230.0000', NULL, 6, 'PACK', '1.0000', '', NULL, NULL, NULL, NULL),
(122, 125, 38, 'PDS002', 'PLERUS PLUS', 'standard', NULL, '40.0000', '40.0000', '1.0000', 12, '0.0000', 1, '0', '0', '0.0000', '40.0000', '', '40.0000', NULL, 6, 'PACK', '1.0000', '', NULL, NULL, NULL, NULL),
(123, 125, 43, 'PDS004', 'SULFAD 1GM', 'standard', 41, '230.0000', '230.0000', '1.0000', 12, '0.0000', 1, '0', '0', '0.0000', '230.0000', '', '230.0000', NULL, 6, 'PACK', '1.0000', '', NULL, NULL, NULL, NULL),
(124, 126, 43, 'PDS004', 'SULFAD 1GM', 'standard', 41, '230.0000', '230.0000', '6.0000', 12, '0.0000', 1, '0', '0', '0.0000', '1380.0000', '', '230.0000', NULL, 6, 'PACK', '6.0000', '', NULL, NULL, NULL, NULL),
(125, 127, 43, 'PDS004', 'SULFAD 1GM', 'standard', 41, '230.0000', '230.0000', '2.0000', 12, '0.0000', 1, '0', '0', '0.0000', '460.0000', '', '230.0000', NULL, 6, 'PACK', '2.0000', '', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sma_sessions`
--

CREATE TABLE `sma_sessions` (
  `id` varchar(40) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `data` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_sessions`
--

INSERT INTO `sma_sessions` (`id`, `ip_address`, `timestamp`, `data`) VALUES
('008860cac0643db6452d98d6b1a8d2f1dde6e355', '101.50.127.6', 1677134292, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373133343239323b7265717565737465645f706167657c733a353a2261646d696e223b6964656e746974797c733a31343a2274657374706861726d6163697374223b757365726e616d657c733a31343a2274657374706861726d6163697374223b656d61696c7c733a33313a2274657374706861726d616369737440706861726d6168657262656c2e636f6d223b757365725f69647c733a323a223338223b6f6c645f6c6173745f6c6f67696e7c733a31303a2231363737303634393435223b6c6173745f69707c733a31333a223131362e37312e3138302e3733223b6176617461727c4e3b67656e6465727c733a343a226d616c65223b67726f75705f69647c733a313a2236223b77617265686f7573655f69647c733a323a223132223b766965775f72696768747c733a313a2230223b656469745f72696768747c733a313a2231223b616c6c6f775f646973636f756e747c733a313a2231223b62696c6c65725f69647c733a333a22353234223b636f6d70616e795f69647c4e3b73686f775f636f73747c733a313a2231223b73686f775f70726963657c733a313a2231223b68696464656e327c693a313b68696464656e317c693a313b72656d6f76655f746f6c737c733a313a2231223b),
('02e872e9bba415c6356c63dccc0347eba297e6e3', '101.50.127.6', 1677135863, 0x7265717565737465645f706167657c733a353a2261646d696e223b5f5f63695f6c6173745f726567656e65726174657c693a313637373133353836333b6964656e746974797c733a32343a226f776e657240706861726d61637968657262656c2e636f6d223b757365726e616d657c733a353a226f776e6572223b656d61696c7c733a32343a226f776e657240706861726d61637968657262656c2e636f6d223b757365725f69647c733a313a2231223b6f6c645f6c6173745f6c6f67696e7c733a31303a2231363737303637383939223b6c6173745f69707c733a31323a223130312e35302e3132372e36223b6176617461727c4e3b67656e6465727c733a343a226d616c65223b67726f75705f69647c733a313a2231223b77617265686f7573655f69647c4e3b766965775f72696768747c733a313a2230223b656469745f72696768747c733a313a2230223b616c6c6f775f646973636f756e747c733a313a2230223b62696c6c65725f69647c4e3b636f6d70616e795f69647c4e3b73686f775f636f73747c733a313a2230223b73686f775f70726963657c733a313a2230223b),
('0706e8166f5750ce8d39b63c247e360c0874e746', '185.191.171.45', 1678573022, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637383537333032323b),
('082ef74e8bfbf5cede62eb9d7220a167868fd3ac', '101.50.127.6', 1677221721, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373232313732313b),
('084b56a3666393837e0ebfbeeb8d6a694271c120', '185.191.171.21', 1677186495, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373138363439353b),
('08d66108736262a6646586bbb7aaf965661436b1', '45.179.246.20', 1678089839, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637383038393539333b7265717565737465645f706167657c733a32343a2263617465676f72792f6d65646963616c2f6d65646963616c223b),
('0a9448a353fbf4802799d1ae8d8773adeaaa9b67', '101.50.127.6', 1677136971, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373133363937313b7265717565737465645f706167657c733a33373a2261646d696e2f73746f636b5f726571756573742f696e636f6d696e675f7265717565737473223b),
('1f2d7ae4b3bd04169db5bbcbe7e080823bd35c83', '185.191.171.5', 1677173812, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373137333831323b),
('24ce114821f2b3dfa6f83ea97ccc04e1c31a1698', '185.191.171.42', 1677278784, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373237383738343b),
('2f54a5546a04a6d7a57a53eddac55c9d000b9764', '185.191.171.3', 1677199986, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373139393938363b7265717565737465645f706167657c733a32333a2263617465676f72792f706861726d61636575746963616c223b),
('312049c45347b38fc4b1dbfd795a4c38dd6c6888', '185.191.171.7', 1677268190, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373236383139303b7265717565737465645f706167657c733a32373a2263617465676f72792f68657262616c2f68657262616c2d73757070223b),
('34fbb0cfdb55bb3eb7d54d2431c03a843f8f9b65', '101.50.127.6', 1677135051, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373133353035313b7265717565737465645f706167657c733a353a2261646d696e223b6964656e746974797c733a31343a2274657374706861726d6163697374223b757365726e616d657c733a31343a2274657374706861726d6163697374223b656d61696c7c733a33313a2274657374706861726d616369737440706861726d6168657262656c2e636f6d223b757365725f69647c733a323a223338223b6f6c645f6c6173745f6c6f67696e7c733a31303a2231363737303634393435223b6c6173745f69707c733a31333a223131362e37312e3138302e3733223b6176617461727c4e3b67656e6465727c733a343a226d616c65223b67726f75705f69647c733a313a2236223b77617265686f7573655f69647c733a323a223132223b766965775f72696768747c733a313a2230223b656469745f72696768747c733a313a2231223b616c6c6f775f646973636f756e747c733a313a2231223b62696c6c65725f69647c733a333a22353234223b636f6d70616e795f69647c4e3b73686f775f636f73747c733a313a2231223b73686f775f70726963657c733a313a2231223b68696464656e327c693a313b68696464656e317c693a313b72656d6f76655f746f6c737c733a313a2231223b),
('3f02bd241595178d52ff950fa9cfe107ff7edae9', '93.158.161.76', 1677983824, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373938333832343b),
('471a707c829eef7664fa2fbf75d3135e972f740d', '101.50.127.6', 1677136971, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373133363937313b7265717565737465645f706167657c733a33373a2261646d696e2f73746f636b5f726571756573742f696e636f6d696e675f7265717565737473223b),
('5dea08af6004a6cea2d2f3ae7d948ef0ea5e9130', '101.50.127.6', 1677133607, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373133333630363b7265717565737465645f706167657c733a353a2261646d696e223b),
('5eaa07398c499e3c311328ad2aa466b86bbf6939', '203.175.73.52', 1677845942, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373834353934313b),
('69b72932e112f002f79f48a794f4e221aafb95fd', '206.84.153.123', 1677744165, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373734343136343b),
('6b943c57090dd56f28163de18f9c5b80fc2534a1', '185.191.171.15', 1677145509, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373134353530393b7265717565737465645f706167657c733a33383a2263617465676f72792f706861726d61636575746963616c2f706861726d61636575746963616c223b),
('7f739454b96c6a0a85ca3dda6a8539346125b889', '203.175.73.52', 1677269653, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373236393635333b),
('7fc71be321f2025fbf21f8f18e3bb2328bc32d54', '185.191.171.38', 1677198761, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373139383736313b),
('817c91bb4510e9faa8bb499178cf6ebfac8f92b1', '185.191.171.23', 1678272191, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637383237323139303b),
('85054f6f037d9d4b5cb1eee99f37afd035db5b43', '17.241.75.73', 1678135803, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637383133333930363b7265717565737465645f706167657c733a31333a2273686f702f70726f6475637473223b),
('8885066758c76d04905f1a06dc8d2803dec619df', '51.39.43.126', 1677686402, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373638363430303b7265717565737465645f706167657c733a31333a2273686f702f70726f6475637473223b),
('8f8c6a7d1aa9f3bdcd8bccaa9e4620d3b0a5c1f3', '101.50.127.6', 1677134697, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373133343639373b7265717565737465645f706167657c733a353a2261646d696e223b6964656e746974797c733a31343a2274657374706861726d6163697374223b757365726e616d657c733a31343a2274657374706861726d6163697374223b656d61696c7c733a33313a2274657374706861726d616369737440706861726d6168657262656c2e636f6d223b757365725f69647c733a323a223338223b6f6c645f6c6173745f6c6f67696e7c733a31303a2231363737303634393435223b6c6173745f69707c733a31333a223131362e37312e3138302e3733223b6176617461727c4e3b67656e6465727c733a343a226d616c65223b67726f75705f69647c733a313a2236223b77617265686f7573655f69647c733a323a223132223b766965775f72696768747c733a313a2230223b656469745f72696768747c733a313a2231223b616c6c6f775f646973636f756e747c733a313a2231223b62696c6c65725f69647c733a333a22353234223b636f6d70616e795f69647c4e3b73686f775f636f73747c733a313a2231223b73686f775f70726963657c733a313a2231223b68696464656e327c693a313b68696464656e317c693a313b72656d6f76655f746f6c737c733a313a2231223b),
('9225f09c7b89d5abcc7965a60da4f4abc4284d40', '185.191.171.18', 1677199792, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373139393739323b7265717565737465645f706167657c733a31353a2263617465676f72792f68657262616c223b),
('a40b7c05c29429337e3e034cbc2c0d462b1f0fd4', '185.191.171.40', 1677261883, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373236313838333b),
('aa983f071a2641f7518d0c1bfad6fc17d1683438', '93.158.161.76', 1678176000, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637383137363030303b7265717565737465645f706167657c733a32333a2263617465676f72792f706861726d61636575746963616c223b),
('b19c38cc74bdd590b47f6930b5866915bdee970e', '203.175.73.52', 1677313617, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373331333631373b),
('b41ce67c3a925c484d3ffe03b87017a2cc4a3c33', '185.191.171.8', 1678155860, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637383135353836303b),
('c0292068ba22833884bcc97c03c102b1bcc46069', '93.158.161.76', 1678430100, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637383433303130303b7265717565737465645f706167657c733a31353a2263617465676f72792f68657262616c223b),
('c338ee6bc1512005a7531deb0eee08e4238babbe', '185.191.171.42', 1677191800, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373139313830303b7265717565737465645f706167657c733a31333a2273686f702f70726f6475637473223b),
('d675e09576ceae0a3972b075d53c654c9cf10367', '101.50.127.6', 1677136139, 0x7265717565737465645f706167657c733a353a2261646d696e223b5f5f63695f6c6173745f726567656e65726174657c693a313637373133353836333b6964656e746974797c733a32343a226f776e657240706861726d61637968657262656c2e636f6d223b757365726e616d657c733a353a226f776e6572223b656d61696c7c733a32343a226f776e657240706861726d61637968657262656c2e636f6d223b757365725f69647c733a313a2231223b6f6c645f6c6173745f6c6f67696e7c733a31303a2231363737303637383939223b6c6173745f69707c733a31323a223130312e35302e3132372e36223b6176617461727c4e3b67656e6465727c733a343a226d616c65223b67726f75705f69647c733a313a2231223b77617265686f7573655f69647c4e3b766965775f72696768747c733a313a2230223b656469745f72696768747c733a313a2230223b616c6c6f775f646973636f756e747c733a313a2230223b62696c6c65725f69647c4e3b636f6d70616e795f69647c4e3b73686f775f636f73747c733a313a2230223b73686f775f70726963657c733a313a2230223b),
('d6dd525d5e8c29b2d6472c190e5ab8404c1fd482', '185.191.171.45', 1677271646, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373237313634363b7265717565737465645f706167657c733a31363a2263617465676f72792f6d65646963616c223b),
('e4def91f7d75c6361be09e3368ac6339ada33d3f', '185.191.171.41', 1677269159, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373236393135393b7265717565737465645f706167657c733a32343a2263617465676f72792f6d65646963616c2f6d65646963616c223b),
('e860476fa0d6a4a54981b4a9694bc350e016f78c', '101.50.127.6', 1677133968, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373133333936383b7265717565737465645f706167657c733a353a2261646d696e223b6964656e746974797c733a31343a2274657374706861726d6163697374223b757365726e616d657c733a31343a2274657374706861726d6163697374223b656d61696c7c733a33313a2274657374706861726d616369737440706861726d6168657262656c2e636f6d223b757365725f69647c733a323a223338223b6f6c645f6c6173745f6c6f67696e7c733a31303a2231363737303634393435223b6c6173745f69707c733a31333a223131362e37312e3138302e3733223b6176617461727c4e3b67656e6465727c733a343a226d616c65223b67726f75705f69647c733a313a2236223b77617265686f7573655f69647c733a323a223132223b766965775f72696768747c733a313a2230223b656469745f72696768747c733a313a2231223b616c6c6f775f646973636f756e747c733a313a2231223b62696c6c65725f69647c733a333a22353234223b636f6d70616e795f69647c4e3b73686f775f636f73747c733a313a2231223b73686f775f70726963657c733a313a2231223b68696464656e327c693a313b68696464656e317c693a313b72656d6f76655f746f6c737c733a313a2231223b),
('e919e88feeb722bf0cf0afc25d6267e136542c76', '185.191.171.4', 1677175439, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637373137353433393b7265717565737465645f706167657c733a32323a2263617465676f72792f68657262616c2f68657262616c223b),
('ee44f521a08d38aac5e3fa7dea647d5f57ac45ae', '203.175.73.52', 1678187268, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637383138373236383b),
('f2220e5aee37a1ec7963719d23ef3fd1aee5e3ba', '93.158.161.76', 1678138623, 0x5f5f63695f6c6173745f726567656e65726174657c693a313637383133383632333b);

-- --------------------------------------------------------

--
-- Table structure for table `sma_settings`
--

CREATE TABLE `sma_settings` (
  `setting_id` int(1) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `logo2` varchar(255) NOT NULL,
  `site_name` varchar(55) NOT NULL,
  `language` varchar(20) NOT NULL,
  `default_warehouse` int(2) NOT NULL,
  `accounting_method` tinyint(4) NOT NULL DEFAULT 0,
  `default_currency` varchar(3) NOT NULL,
  `default_tax_rate` int(2) NOT NULL,
  `rows_per_page` int(2) NOT NULL,
  `version` varchar(10) NOT NULL DEFAULT '1.0',
  `default_tax_rate2` int(11) NOT NULL DEFAULT 0,
  `dateformat` int(11) NOT NULL,
  `sales_prefix` varchar(20) DEFAULT NULL,
  `quote_prefix` varchar(20) DEFAULT NULL,
  `purchase_prefix` varchar(20) DEFAULT NULL,
  `transfer_prefix` varchar(20) DEFAULT NULL,
  `delivery_prefix` varchar(20) DEFAULT NULL,
  `payment_prefix` varchar(20) DEFAULT NULL,
  `return_prefix` varchar(20) DEFAULT NULL,
  `returnp_prefix` varchar(20) DEFAULT NULL,
  `expense_prefix` varchar(20) DEFAULT NULL,
  `item_addition` tinyint(1) NOT NULL DEFAULT 0,
  `theme` varchar(20) NOT NULL,
  `product_serial` tinyint(4) NOT NULL,
  `default_discount` int(11) NOT NULL,
  `product_discount` tinyint(1) NOT NULL DEFAULT 0,
  `discount_method` tinyint(4) NOT NULL,
  `tax1` tinyint(4) NOT NULL,
  `tax2` tinyint(4) NOT NULL,
  `overselling` tinyint(1) NOT NULL DEFAULT 0,
  `restrict_user` tinyint(4) NOT NULL DEFAULT 0,
  `restrict_calendar` tinyint(4) NOT NULL DEFAULT 0,
  `timezone` varchar(100) DEFAULT NULL,
  `iwidth` int(11) NOT NULL DEFAULT 0,
  `iheight` int(11) NOT NULL,
  `twidth` int(11) NOT NULL,
  `theight` int(11) NOT NULL,
  `watermark` tinyint(1) DEFAULT NULL,
  `reg_ver` tinyint(1) DEFAULT NULL,
  `allow_reg` tinyint(1) DEFAULT NULL,
  `reg_notification` tinyint(1) DEFAULT NULL,
  `auto_reg` tinyint(1) DEFAULT NULL,
  `protocol` varchar(20) NOT NULL DEFAULT 'mail',
  `mailpath` varchar(55) DEFAULT '/usr/sbin/sendmail',
  `smtp_host` varchar(100) DEFAULT NULL,
  `smtp_user` varchar(100) DEFAULT NULL,
  `smtp_pass` varchar(255) DEFAULT NULL,
  `smtp_port` varchar(10) DEFAULT '25',
  `smtp_crypto` varchar(10) DEFAULT NULL,
  `corn` datetime DEFAULT NULL,
  `customer_group` int(11) NOT NULL,
  `default_email` varchar(100) NOT NULL,
  `mmode` tinyint(1) NOT NULL,
  `bc_fix` tinyint(4) NOT NULL DEFAULT 0,
  `auto_detect_barcode` tinyint(1) NOT NULL DEFAULT 0,
  `captcha` tinyint(1) NOT NULL DEFAULT 1,
  `reference_format` tinyint(1) NOT NULL DEFAULT 1,
  `racks` tinyint(1) DEFAULT 0,
  `attributes` tinyint(1) NOT NULL DEFAULT 0,
  `product_expiry` tinyint(1) NOT NULL DEFAULT 0,
  `decimals` tinyint(2) NOT NULL DEFAULT 2,
  `qty_decimals` tinyint(2) NOT NULL DEFAULT 2,
  `decimals_sep` varchar(2) NOT NULL DEFAULT '.',
  `thousands_sep` varchar(2) NOT NULL DEFAULT ',',
  `invoice_view` tinyint(1) DEFAULT 0,
  `default_biller` int(11) DEFAULT NULL,
  `envato_username` varchar(50) DEFAULT NULL,
  `purchase_code` varchar(100) DEFAULT NULL,
  `rtl` tinyint(1) DEFAULT 0,
  `each_spent` decimal(15,4) DEFAULT NULL,
  `ca_point` tinyint(4) DEFAULT NULL,
  `each_sale` decimal(15,4) DEFAULT NULL,
  `sa_point` tinyint(4) DEFAULT NULL,
  `update` tinyint(1) DEFAULT 0,
  `sac` tinyint(1) DEFAULT 0,
  `display_all_products` tinyint(1) DEFAULT 0,
  `display_symbol` tinyint(1) DEFAULT NULL,
  `symbol` varchar(50) DEFAULT NULL,
  `remove_expired` tinyint(1) DEFAULT 0,
  `barcode_separator` varchar(2) NOT NULL DEFAULT '-',
  `set_focus` tinyint(1) NOT NULL DEFAULT 0,
  `price_group` int(11) DEFAULT NULL,
  `barcode_img` tinyint(1) NOT NULL DEFAULT 1,
  `ppayment_prefix` varchar(20) DEFAULT 'POP',
  `disable_editing` smallint(6) DEFAULT 90,
  `qa_prefix` varchar(55) DEFAULT NULL,
  `update_cost` tinyint(1) DEFAULT NULL,
  `apis` tinyint(1) NOT NULL DEFAULT 0,
  `state` varchar(100) DEFAULT NULL,
  `pdf_lib` varchar(20) DEFAULT 'dompdf',
  `use_code_for_slug` tinyint(1) DEFAULT NULL,
  `ws_barcode_type` varchar(10) DEFAULT 'weight',
  `ws_barcode_chars` tinyint(4) DEFAULT NULL,
  `flag_chars` tinyint(4) DEFAULT NULL,
  `item_code_start` tinyint(4) DEFAULT NULL,
  `item_code_chars` tinyint(4) DEFAULT NULL,
  `price_start` tinyint(4) DEFAULT NULL,
  `price_chars` tinyint(4) DEFAULT NULL,
  `price_divide_by` int(11) DEFAULT NULL,
  `weight_start` tinyint(4) DEFAULT NULL,
  `weight_chars` tinyint(4) DEFAULT NULL,
  `weight_divide_by` int(11) DEFAULT NULL,
  `ksa_qrcode` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_settings`
--

INSERT INTO `sma_settings` (`setting_id`, `logo`, `logo2`, `site_name`, `language`, `default_warehouse`, `accounting_method`, `default_currency`, `default_tax_rate`, `rows_per_page`, `version`, `default_tax_rate2`, `dateformat`, `sales_prefix`, `quote_prefix`, `purchase_prefix`, `transfer_prefix`, `delivery_prefix`, `payment_prefix`, `return_prefix`, `returnp_prefix`, `expense_prefix`, `item_addition`, `theme`, `product_serial`, `default_discount`, `product_discount`, `discount_method`, `tax1`, `tax2`, `overselling`, `restrict_user`, `restrict_calendar`, `timezone`, `iwidth`, `iheight`, `twidth`, `theight`, `watermark`, `reg_ver`, `allow_reg`, `reg_notification`, `auto_reg`, `protocol`, `mailpath`, `smtp_host`, `smtp_user`, `smtp_pass`, `smtp_port`, `smtp_crypto`, `corn`, `customer_group`, `default_email`, `mmode`, `bc_fix`, `auto_detect_barcode`, `captcha`, `reference_format`, `racks`, `attributes`, `product_expiry`, `decimals`, `qty_decimals`, `decimals_sep`, `thousands_sep`, `invoice_view`, `default_biller`, `envato_username`, `purchase_code`, `rtl`, `each_spent`, `ca_point`, `each_sale`, `sa_point`, `update`, `sac`, `display_all_products`, `display_symbol`, `symbol`, `remove_expired`, `barcode_separator`, `set_focus`, `price_group`, `barcode_img`, `ppayment_prefix`, `disable_editing`, `qa_prefix`, `update_cost`, `apis`, `state`, `pdf_lib`, `use_code_for_slug`, `ws_barcode_type`, `ws_barcode_chars`, `flag_chars`, `item_code_start`, `item_code_chars`, `price_start`, `price_chars`, `price_divide_by`, `weight_start`, `weight_chars`, `weight_divide_by`, `ksa_qrcode`) VALUES
(1, 'avenzur-logov2-021.png', 'avenzur-logov2-022.png', 'Pharmacy Herbel', 'english', 9, 2, 'SAR', 1, 10, '3.4.53', 1, 5, 'SALE', 'QUOTE', 'PO', 'TR', 'DO', 'IPAY', 'SR', 'PR', '', 1, 'blue', 1, 1, 1, 1, 1, 1, 0, 1, 0, 'Asia/Riyadh', 800, 800, 150, 150, 0, 0, 0, 0, NULL, 'smtp', 'app/libraries/sma.php', 'mail.checkdev.xyz', 'info@checkdev.xyz', '&P6b@UU&nyIo', '465', 'ssl', NULL, 1, 'info@checkdev.xyz', 0, 4, 1, 0, 2, 1, 1, 1, 0, 2, '.', ',', 0, 524, 'mujtaba1991', '62bfcc8d-d89a-4013-b1ed-b88ca8fd1446', 0, NULL, NULL, NULL, NULL, 0, 0, 1, 1, '', 0, '-', 1, 1, 0, 'POP', 90, '', 1, 0, 'AN', 'dompdf', 0, 'price', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sma_shop_settings`
--

CREATE TABLE `sma_shop_settings` (
  `shop_id` int(11) NOT NULL,
  `shop_name` varchar(55) NOT NULL,
  `description` varchar(160) NOT NULL,
  `warehouse` int(11) NOT NULL,
  `biller` int(11) NOT NULL,
  `about_link` varchar(55) NOT NULL,
  `terms_link` varchar(55) NOT NULL,
  `privacy_link` varchar(55) NOT NULL,
  `contact_link` varchar(55) NOT NULL,
  `payment_text` varchar(100) NOT NULL,
  `follow_text` varchar(100) NOT NULL,
  `facebook` varchar(55) NOT NULL,
  `twitter` varchar(55) DEFAULT NULL,
  `google_plus` varchar(55) DEFAULT NULL,
  `instagram` varchar(55) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(55) DEFAULT NULL,
  `cookie_message` varchar(180) DEFAULT NULL,
  `cookie_link` varchar(55) DEFAULT NULL,
  `slider` text DEFAULT NULL,
  `shipping` int(11) DEFAULT NULL,
  `purchase_code` varchar(100) DEFAULT 'purchase_code',
  `envato_username` varchar(50) DEFAULT 'envato_username',
  `version` varchar(10) DEFAULT '3.4.53',
  `logo` varchar(55) DEFAULT NULL,
  `bank_details` varchar(255) DEFAULT NULL,
  `products_page` tinyint(1) DEFAULT NULL,
  `hide0` tinyint(1) DEFAULT 0,
  `products_description` varchar(255) DEFAULT NULL,
  `private` tinyint(1) DEFAULT 0,
  `hide_price` tinyint(1) DEFAULT 0,
  `stripe` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_shop_settings`
--

INSERT INTO `sma_shop_settings` (`shop_id`, `shop_name`, `description`, `warehouse`, `biller`, `about_link`, `terms_link`, `privacy_link`, `contact_link`, `payment_text`, `follow_text`, `facebook`, `twitter`, `google_plus`, `instagram`, `phone`, `email`, `cookie_message`, `cookie_link`, `slider`, `shipping`, `purchase_code`, `envato_username`, `version`, `logo`, `bank_details`, `products_page`, `hide0`, `products_description`, `private`, `hide_price`, `stripe`) VALUES
(1, 'AVENZUR', 'Pharmacy Herbal', 9, 524, 'about-us', '', 'privacy-policy', 'contact-us', 'We accept PayPal or you can pay with your credit/debit cards.', 'Please click the link below to follow us on social media.', 'http://facebook.com/#', 'http://twitter.com/#', '', '', '000 0000 000', 'info@pharmacyherbal.com', 'We use cookies to improve your experience on our website. By browsing this website, you agree to our use of cookies.', '', '[{\"image\":\"s1.jpg\",\"link\":\"https:\\/\\/checkdev.xyz\\/pharmacy\\/shop\\/products\",\"caption\":\"amr\"},{\"image\":\"7696b09378fc2ad70a83886bbf59fe3c.png\",\"link\":\"https:\\/\\/checkdev.xyz\\/pharmacy\\/category\\/herbal\\/herbal-supp?page=1\",\"caption\":\"\"},{\"image\":\"73f9491b4dc128e1176f87c5084374e8.jpg\",\"link\":\"https:\\/\\/checkdev.xyz\\/pharmacy\\/shop\\/products\",\"caption\":\"\"},{\"link\":\"\",\"caption\":\"\"},{\"link\":\"\",\"caption\":\"\"}]', 24, '', 'envato_username', '3.4.53', 'avenzur-logov2-024.png', '', 0, 0, 'This is products page description and is locked on demo.', 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `sma_skrill`
--

CREATE TABLE `sma_skrill` (
  `id` int(11) NOT NULL,
  `active` tinyint(4) NOT NULL,
  `account_email` varchar(255) NOT NULL DEFAULT 'testaccount2@moneybookers.com',
  `secret_word` varchar(20) NOT NULL DEFAULT 'mbtest',
  `skrill_currency` varchar(3) NOT NULL DEFAULT 'USD',
  `fixed_charges` decimal(25,4) NOT NULL DEFAULT 0.0000,
  `extra_charges_my` decimal(25,4) NOT NULL DEFAULT 0.0000,
  `extra_charges_other` decimal(25,4) NOT NULL DEFAULT 0.0000
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_skrill`
--

INSERT INTO `sma_skrill` (`id`, `active`, `account_email`, `secret_word`, `skrill_currency`, `fixed_charges`, `extra_charges_my`, `extra_charges_other`) VALUES
(1, 0, 'testaccount2@moneybookers.com', 'mbtest', 'USD', '0.0000', '0.0000', '0.0000');

-- --------------------------------------------------------

--
-- Table structure for table `sma_sms_settings`
--

CREATE TABLE `sma_sms_settings` (
  `id` int(11) NOT NULL,
  `auto_send` tinyint(1) DEFAULT NULL,
  `config` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_sms_settings`
--

INSERT INTO `sma_sms_settings` (`id`, `auto_send`, `config`) VALUES
(1, NULL, '{\"gateway\":\"Log\",\"Log\":{}');

-- --------------------------------------------------------

--
-- Table structure for table `sma_stock_counts`
--

CREATE TABLE `sma_stock_counts` (
  `id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reference_no` varchar(55) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `type` varchar(10) NOT NULL,
  `initial_file` varchar(50) NOT NULL,
  `final_file` varchar(50) DEFAULT NULL,
  `brands` varchar(50) DEFAULT NULL,
  `brand_names` varchar(100) DEFAULT NULL,
  `categories` varchar(50) DEFAULT NULL,
  `category_names` varchar(100) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `products` int(11) DEFAULT NULL,
  `rows` int(11) DEFAULT NULL,
  `differences` int(11) DEFAULT NULL,
  `matches` int(11) DEFAULT NULL,
  `missing` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `finalized` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_stock_counts`
--

INSERT INTO `sma_stock_counts` (`id`, `date`, `reference_no`, `warehouse_id`, `type`, `initial_file`, `final_file`, `brands`, `brand_names`, `categories`, `category_names`, `note`, `products`, `rows`, `differences`, `matches`, `missing`, `created_by`, `updated_by`, `updated_at`, `finalized`) VALUES
(1, '2022-10-03 15:06:00', '', 1, 'partial', 'b1e71a196ff6bdc58a772e6d774205f8.csv', NULL, '', '', '', '', NULL, 2, 2, NULL, NULL, NULL, 1, NULL, NULL, NULL),
(2, '2022-11-16 21:48:00', '', 1, 'full', 'c0781c7b47246d7154c18f38df189504.csv', NULL, '', '', '', '', NULL, 24, 24, NULL, NULL, NULL, 1, NULL, NULL, NULL),
(3, '2022-11-16 21:59:00', '', 1, 'full', '198704ba3150f4a28bc027dbeabffc2e.csv', NULL, '', '', '', '', NULL, 24, 24, NULL, NULL, NULL, 1, NULL, NULL, NULL),
(4, '2022-11-26 19:00:00', '', 1, 'full', 'a5254bb029a9a26c6ffcb81259699a86.csv', NULL, '', '', '', '', NULL, 24, 24, NULL, NULL, NULL, 1, NULL, NULL, NULL),
(5, '2022-12-18 00:18:00', '', 3, 'full', 'cec40a3d211128eadaac5f59596a250d.csv', NULL, '', '', '', '', NULL, 3, 5, NULL, NULL, NULL, 1, NULL, NULL, NULL),
(6, '2022-12-18 01:18:00', '', 3, 'full', 'ffc4ac37d1c533d13f01adfcf3f0265d.csv', NULL, '', '', '', '', NULL, 6, 9, NULL, NULL, NULL, 1, NULL, NULL, NULL),
(7, '2022-12-19 20:11:00', '', 4, 'full', 'e0636c5ad1e45427ebd039087aa683c6.csv', NULL, '', '', '', '', NULL, 11, 15, NULL, NULL, NULL, 1, NULL, NULL, NULL),
(8, '2022-12-23 17:58:00', '111111', 1, 'partial', '3dd236ec8f3c5dccbefbbd339257dfa6.csv', NULL, '2', 'Honst', '9', 'For Women', NULL, 2, 2, NULL, NULL, NULL, 1, NULL, NULL, NULL),
(9, '2022-12-28 16:16:00', '', 9, 'full', 'cc2e41ffad29d597f6662ec4bcf683f0.csv', NULL, '', '', '', '', NULL, 6, 6, NULL, NULL, NULL, 1, NULL, NULL, NULL),
(10, '2023-02-07 21:19:00', '', 11, 'full', '00ed28bdca599fd6b7acfea50499558a.csv', NULL, '', '', '', '', NULL, 1, 1, NULL, NULL, NULL, 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sma_stock_count_items`
--

CREATE TABLE `sma_stock_count_items` (
  `id` int(11) NOT NULL,
  `stock_count_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_code` varchar(50) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `product_variant` varchar(55) DEFAULT NULL,
  `product_variant_id` int(11) DEFAULT NULL,
  `expected` decimal(15,4) NOT NULL,
  `counted` decimal(15,4) NOT NULL,
  `cost` decimal(25,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sma_suspended_bills`
--

CREATE TABLE `sma_suspended_bills` (
  `id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `customer_id` int(11) NOT NULL,
  `customer` varchar(55) DEFAULT NULL,
  `count` int(11) NOT NULL,
  `order_discount_id` varchar(20) DEFAULT NULL,
  `order_tax_id` int(11) DEFAULT NULL,
  `total` decimal(25,4) NOT NULL,
  `biller_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `suspend_note` varchar(255) DEFAULT NULL,
  `shipping` decimal(15,4) DEFAULT 0.0000,
  `cgst` decimal(25,4) DEFAULT NULL,
  `sgst` decimal(25,4) DEFAULT NULL,
  `igst` decimal(25,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sma_suspended_items`
--

CREATE TABLE `sma_suspended_items` (
  `id` int(11) NOT NULL,
  `suspend_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `product_code` varchar(55) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `net_unit_price` decimal(25,4) NOT NULL,
  `unit_price` decimal(25,4) NOT NULL,
  `quantity` decimal(15,4) DEFAULT 0.0000,
  `warehouse_id` int(11) DEFAULT NULL,
  `item_tax` decimal(25,4) DEFAULT NULL,
  `tax_rate_id` int(11) DEFAULT NULL,
  `tax` varchar(55) DEFAULT NULL,
  `discount` varchar(55) DEFAULT NULL,
  `item_discount` decimal(25,4) DEFAULT NULL,
  `subtotal` decimal(25,4) NOT NULL,
  `serial_no` varchar(255) DEFAULT NULL,
  `option_id` int(11) DEFAULT NULL,
  `product_type` varchar(20) DEFAULT NULL,
  `real_unit_price` decimal(25,4) DEFAULT NULL,
  `product_unit_id` int(11) DEFAULT NULL,
  `product_unit_code` varchar(10) DEFAULT NULL,
  `unit_quantity` decimal(15,4) NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `gst` varchar(20) DEFAULT NULL,
  `cgst` decimal(25,4) DEFAULT NULL,
  `sgst` decimal(25,4) DEFAULT NULL,
  `igst` decimal(25,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sma_tax_rates`
--

CREATE TABLE `sma_tax_rates` (
  `id` int(11) NOT NULL,
  `name` varchar(55) NOT NULL,
  `code` varchar(10) DEFAULT NULL,
  `rate` decimal(12,4) NOT NULL,
  `type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_tax_rates`
--

INSERT INTO `sma_tax_rates` (`id`, `name`, `code`, `rate`, `type`) VALUES
(1, 'No Tax', 'NT', '0.0000', '2'),
(2, 'VAT @10%', 'VAT10', '10.0000', '1'),
(3, 'GST @6%', 'GST', '6.0000', '1'),
(4, 'VAT @20%', 'VT20', '20.0000', '1');

-- --------------------------------------------------------

--
-- Table structure for table `sma_transfers`
--

CREATE TABLE `sma_transfers` (
  `id` int(11) NOT NULL,
  `transfer_no` varchar(55) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `from_warehouse_id` int(11) NOT NULL,
  `from_warehouse_code` varchar(55) NOT NULL,
  `from_warehouse_name` varchar(55) NOT NULL,
  `to_warehouse_id` int(11) NOT NULL,
  `to_warehouse_code` varchar(55) NOT NULL,
  `to_warehouse_name` varchar(55) NOT NULL,
  `note` varchar(1000) DEFAULT NULL,
  `total` decimal(25,4) DEFAULT NULL,
  `total_tax` decimal(25,4) DEFAULT NULL,
  `grand_total` decimal(25,4) DEFAULT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `status` varchar(55) NOT NULL DEFAULT 'pending',
  `shipping` decimal(25,4) NOT NULL DEFAULT 0.0000,
  `attachment` varchar(55) DEFAULT NULL,
  `cgst` decimal(25,4) DEFAULT NULL,
  `sgst` decimal(25,4) DEFAULT NULL,
  `igst` decimal(25,4) DEFAULT NULL,
  `approval` tinyint(1) DEFAULT 0,
  `type` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_transfers`
--

INSERT INTO `sma_transfers` (`id`, `transfer_no`, `date`, `from_warehouse_id`, `from_warehouse_code`, `from_warehouse_name`, `to_warehouse_id`, `to_warehouse_code`, `to_warehouse_name`, `note`, `total`, `total_tax`, `grand_total`, `created_by`, `status`, `shipping`, `attachment`, `cgst`, `sgst`, `igst`, `approval`, `type`) VALUES
(5, '100001', '2023-02-06 16:42:44', 11, 'DXB001', 'DUBAI WH', 12, 'RUH001', 'PHARMA DRUG STORE', '', '1150.0000', '0.0000', '1150.0000', '37', 'completed', '0.0000', '0', NULL, NULL, NULL, 1, 'stock'),
(6, 'TR2023/02/0003', '2023-02-06 21:41:15', 11, 'DXB001', 'DUBAI WH', 12, 'RUH001', 'PHARMA DRUG STORE', '', '1150.0000', '0.0000', '1150.0000', '38', 'pending', '0.0000', '0', NULL, NULL, NULL, 0, 'stock'),
(7, 'TR2023/02/0004', '2023-02-07 18:49:35', 11, 'DXB001', 'DUBAI WH', 12, 'RUH001', 'PHARMA DRUG STORE', '', '575.0000', '0.0000', '575.0000', '38', 'sent', '0.0000', '0', NULL, NULL, NULL, 0, 'stock'),
(10, '123456', '2023-02-22 16:39:09', 11, 'DXB001', 'DUBAI WH', 12, 'RUH001', 'PHARMA DRUG STORE', '', '115.0000', '0.0000', '115.0000', '38', 'pending', '0.0000', '0', NULL, NULL, NULL, 0, 'stock'),
(11, '123', '2023-02-22 18:19:52', 11, 'DXB001', 'DUBAI WH', 12, 'RUH001', 'PHARMA DRUG STORE', '', '230.0000', '0.0000', '230.0000', '37', 'pending', '0.0000', '0', NULL, NULL, NULL, 0, 'transfer'),
(12, '56789', '2023-02-22 19:24:58', 11, 'DXB001', 'DUBAI WH', 12, 'RUH001', 'PHARMA DRUG STORE', '', '115.0000', '0.0000', '115.0000', '38', 'pending', '0.0000', '0', NULL, NULL, NULL, 0, 'transfer');

-- --------------------------------------------------------

--
-- Table structure for table `sma_transfer_items`
--

CREATE TABLE `sma_transfer_items` (
  `id` int(11) NOT NULL,
  `transfer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_code` varchar(55) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `option_id` int(11) DEFAULT NULL,
  `expiry` date DEFAULT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `tax_rate_id` int(11) DEFAULT NULL,
  `tax` varchar(55) DEFAULT NULL,
  `item_tax` decimal(25,4) DEFAULT NULL,
  `net_unit_cost` decimal(25,4) DEFAULT NULL,
  `subtotal` decimal(25,4) DEFAULT NULL,
  `quantity_balance` decimal(15,4) NOT NULL,
  `unit_cost` decimal(25,4) DEFAULT NULL,
  `real_unit_cost` decimal(25,4) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `product_unit_id` int(11) DEFAULT NULL,
  `product_unit_code` varchar(10) DEFAULT NULL,
  `unit_quantity` decimal(15,4) NOT NULL,
  `gst` varchar(20) DEFAULT NULL,
  `cgst` decimal(25,4) DEFAULT NULL,
  `sgst` decimal(25,4) DEFAULT NULL,
  `igst` decimal(25,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_transfer_items`
--

INSERT INTO `sma_transfer_items` (`id`, `transfer_id`, `product_id`, `product_code`, `product_name`, `option_id`, `expiry`, `quantity`, `tax_rate_id`, `tax`, `item_tax`, `net_unit_cost`, `subtotal`, `quantity_balance`, `unit_cost`, `real_unit_cost`, `date`, `warehouse_id`, `product_unit_id`, `product_unit_code`, `unit_quantity`, `gst`, `cgst`, `sgst`, `igst`) VALUES
(3, 6, 43, 'PDS004', 'SULFAD 1GM', 41, '0000-00-00', '10.0000', 1, '0', '0.0000', '115.0000', '1150.0000', '10.0000', '115.0000', '115.0000', '2023-02-06', 12, 6, 'PACK', '10.0000', NULL, NULL, NULL, NULL),
(7, 7, 43, 'PDS004', 'SULFAD 1GM', 41, NULL, '5.0000', NULL, '', '0.0000', '115.0000', '575.0000', '5.0000', '115.0000', '115.0000', '2023-02-07', 12, 6, 'PACK', '5.0000', NULL, NULL, NULL, NULL),
(9, 11, 43, 'PDS004', 'SULFAD 1GM', 41, '0000-00-00', '2.0000', 1, '0', '0.0000', '115.0000', '230.0000', '2.0000', '115.0000', '115.0000', '2023-02-22', 12, 6, 'PACK', '2.0000', NULL, NULL, NULL, NULL),
(10, 12, 43, 'PDS004', 'SULFAD 1GM', 41, NULL, '1.0000', NULL, '', '0.0000', '115.0000', '115.0000', '1.0000', '115.0000', '115.0000', '2023-02-22', 12, 6, 'PACK', '1.0000', NULL, NULL, NULL, NULL),
(12, 10, 43, 'PDS004', 'SULFAD 1GM', 41, NULL, '1.0000', NULL, '', '0.0000', '115.0000', '115.0000', '1.0000', '115.0000', '115.0000', '2023-02-22', 12, 6, 'PACK', '1.0000', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sma_units`
--

CREATE TABLE `sma_units` (
  `id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(55) NOT NULL,
  `base_unit` int(11) DEFAULT NULL,
  `operator` varchar(1) DEFAULT NULL,
  `unit_value` varchar(55) DEFAULT NULL,
  `operation_value` varchar(55) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_units`
--

INSERT INTO `sma_units` (`id`, `code`, `name`, `base_unit`, `operator`, `unit_value`, `operation_value`) VALUES
(6, 'PACK', 'PACK', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sma_users`
--

CREATE TABLE `sma_users` (
  `id` int(11) UNSIGNED NOT NULL,
  `last_ip_address` varbinary(45) DEFAULT NULL,
  `ip_address` varbinary(45) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(40) NOT NULL,
  `salt` varchar(40) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `activation_code` varchar(40) DEFAULT NULL,
  `forgotten_password_code` varchar(40) DEFAULT NULL,
  `forgotten_password_time` int(11) UNSIGNED DEFAULT NULL,
  `remember_code` varchar(40) DEFAULT NULL,
  `created_on` int(11) UNSIGNED NOT NULL,
  `last_login` int(11) UNSIGNED DEFAULT NULL,
  `active` tinyint(1) UNSIGNED DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(55) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `group_id` int(10) UNSIGNED NOT NULL,
  `warehouse_id` int(10) UNSIGNED DEFAULT NULL,
  `biller_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `show_cost` tinyint(1) DEFAULT 0,
  `show_price` tinyint(1) DEFAULT 0,
  `award_points` int(11) DEFAULT 0,
  `view_right` tinyint(1) NOT NULL DEFAULT 0,
  `edit_right` tinyint(1) NOT NULL DEFAULT 0,
  `allow_discount` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_users`
--

INSERT INTO `sma_users` (`id`, `last_ip_address`, `ip_address`, `username`, `password`, `salt`, `email`, `activation_code`, `forgotten_password_code`, `forgotten_password_time`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `country`, `phone`, `avatar`, `gender`, `group_id`, `warehouse_id`, `biller_id`, `company_id`, `show_cost`, `show_price`, `award_points`, `view_right`, `edit_right`, `allow_discount`) VALUES
(1, 0x3130312e35302e3132372e36, 0x0000, 'owner', '2c8ab736b2ccab4f50e72d5fd7d21020cbb77ae7', NULL, 'owner@pharmacyherbel.com', NULL, NULL, NULL, '6e5e2f4c47ba10736e92891840965955f42f6f45', 1351661704, 1677135390, 1, 'Owner', 'Owner', 'Pharmacy Herbel', '012345678', NULL, 'male', 1, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0),
(34, NULL, 0x39342e3230362e31372e3639, 'sheshtawy', 'cf300d11c749c108b50911e7ff55a3812628ff44', NULL, 'eng.sheshtawy@gmail.com', NULL, NULL, NULL, NULL, 1673329106, 1673329106, 1, 'Mohamed', 'Ahmed', 'SA', '+966 544385270', NULL, 'male', 3, NULL, NULL, 528, 0, 0, 0, 0, 0, 0),
(35, NULL, 0x382e3230382e39302e3535, 'gndwfiojgvasth', '5caa7118896407b85c0cb942e9f5f62d09d2a845', NULL, 'goldurokud@outlook.com', NULL, NULL, NULL, NULL, 1673539473, 1673539473, 1, 'WFUGfijSK', 'UXlWjrGt', 'SA', '8734179520', NULL, 'male', 3, NULL, NULL, 530, 0, 0, 0, 0, 0, 0),
(36, 0x3230362e38342e3135332e34, 0x3230362e38342e3135332e34, 'testing', 'ebb58d698d43733b4c39f6c7a18ec00f82b13798', NULL, 'testing@gmail.com', NULL, NULL, NULL, NULL, 1674557124, 1674557151, 1, 'test', 'user', 'AE', '9711212121221', NULL, 'male', 3, NULL, NULL, 531, 0, 0, 0, 0, 0, 0),
(37, 0x3131362e37312e3138302e3733, 0x3230332e3137352e36362e3934, 'pharmacist', '71e33ae44f74fa7199d3567a40b7673eb8ee8a4f', NULL, 'pharmacist@avenzur.com', NULL, NULL, NULL, NULL, 1675671939, 1677065137, 1, 'Test', 'Pharmacist', NULL, '+966123456789', NULL, 'male', 6, 11, 524, NULL, 1, 1, 0, 0, 1, 1),
(38, 0x3130312e35302e3132372e36, 0x3230332e3137352e36362e3934, 'testpharmacist', 'e893180bcc6875ea420d2d15c451a9cce5cf99b5', NULL, 'testpharmacist@pharmaherbel.com', NULL, NULL, NULL, NULL, 1675672857, 1677133617, 1, 'Test', 'Pahramacist2', NULL, '+966789456123', NULL, 'male', 6, 12, 524, NULL, 1, 1, 0, 0, 1, 1),
(39, 0x3230332e3137352e36362e3934, 0x3230332e3137352e36362e3934, 'dubaiadmin', 'eed223f26c81e8d23d6e0201bb667e988a005894', NULL, 'dubaiadmin@avenzur.com', NULL, NULL, NULL, NULL, 1675674338, 1675674358, 1, 'Admin', 'Dubai', NULL, '+9661234568987', NULL, 'male', 2, 0, 0, NULL, 0, 0, 0, 1, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `sma_user_logins`
--

CREATE TABLE `sma_user_logins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `ip_address` varbinary(16) NOT NULL,
  `login` varchar(100) NOT NULL,
  `time` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_user_logins`
--

INSERT INTO `sma_user_logins` (`id`, `user_id`, `company_id`, `ip_address`, `login`, `time`) VALUES
(1, 1, NULL, 0x33382e372e3138342e3939, 'owner@tecdiary.com', '2022-09-23 18:39:50'),
(2, 1, NULL, 0x33382e372e3138342e3939, 'owner@tecdiary.com', '2022-09-23 18:40:25'),
(3, 1, NULL, 0x33382e372e3138342e3939, 'owner@tecdiary.com', '2022-09-23 19:34:41'),
(4, 1, NULL, 0x33382e372e3138342e3939, 'owner@pharmacyherbel.com', '2022-09-23 19:40:09'),
(5, 1, NULL, 0x33382e372e3138342e3939, 'owner@pharmacyherbel.com', '2022-09-23 19:41:18'),
(6, 1, NULL, 0x33382e372e3138342e3939, 'owner@pharmacyherbel.com', '2022-09-23 19:41:31'),
(7, 1, NULL, 0x33382e372e3138342e3939, 'owner@pharmacyherbel.com', '2022-09-23 20:05:41'),
(8, 1, NULL, 0x3230352e3136342e3133322e3630, 'owner@pharmacyherbel.com', '2022-09-26 12:57:50'),
(9, 1, NULL, 0x3230352e3136342e3133322e3630, 'owner@pharmacyherbel.com', '2022-09-26 13:33:16'),
(10, 1, NULL, 0x3230352e3136342e3133322e3630, 'owner@pharmacyherbel.com', '2022-09-30 17:15:36'),
(11, 1, NULL, 0x33392e34302e3131382e3235, 'owner@pharmacyherbel.com', '2022-10-02 16:14:41'),
(12, 1, NULL, 0x3131352e3138362e3139302e3537, 'owner@pharmacyherbel.com', '2022-10-02 21:12:16'),
(13, 1, NULL, 0x3131392e3136302e36342e3139, 'owner@pharmacyherbel.com', '2022-10-02 23:56:05'),
(14, 1, NULL, 0x3131352e3138362e3139302e3537, 'owner@pharmacyherbel.com', '2022-10-03 02:47:24'),
(15, 1, NULL, 0x3131352e3138362e3139302e3537, 'owner@pharmacyherbel.com', '2022-10-03 04:41:26'),
(16, 1, NULL, 0x3131392e3136302e36372e323031, 'owner@pharmacyherbel.com', '2022-10-03 13:44:53'),
(17, 1, NULL, 0x3230362e38342e3135362e313530, 'owner@pharmacyherbel.com', '2022-10-24 18:16:17'),
(18, 1, NULL, 0x3230362e38342e3135362e313530, 'owner@pharmacyherbel.com', '2022-10-24 19:10:55'),
(19, 1, NULL, 0x3230362e38342e3135362e313530, 'owner@pharmacyherbel.com', '2022-10-25 17:43:27'),
(20, 1, NULL, 0x3230362e38342e3135362e313530, 'owner@pharmacyherbel.com', '2022-10-25 17:53:00'),
(21, 1, NULL, 0x3230362e38342e3135362e313530, 'owner@pharmacyherbel.com', '2022-10-25 21:46:15'),
(22, 1, NULL, 0x3230362e38342e3135362e313530, 'owner@pharmacyherbel.com', '2022-10-26 13:52:39'),
(23, 1, NULL, 0x3130312e35302e38332e3232, 'owner@pharmacyherbel.com', '2022-11-03 17:05:24'),
(24, 1, NULL, 0x39312e37332e35362e3630, 'owner@pharmacyherbel.com', '2022-11-08 14:58:38'),
(25, 1, NULL, 0x322e35312e3132372e3931, 'owner@pharmacyherbel.com', '2022-11-16 14:05:59'),
(26, 1, NULL, 0x3230362e38342e3135322e313236, 'owner@pharmacyherbel.com', '2022-11-16 15:47:07'),
(27, 1, NULL, 0x3230362e38342e3135322e313236, 'owner@pharmacyherbel.com', '2022-11-16 16:03:28'),
(28, 1, NULL, 0x3230362e38342e3135322e313236, 'owner@pharmacyherbel.com', '2022-11-16 20:56:25'),
(29, 1, NULL, 0x3230362e38342e3135322e313236, 'owner@pharmacyherbel.com', '2022-11-16 20:56:52'),
(30, 1, NULL, 0x3230332e3137352e36362e323534, 'owner@pharmacyherbel.com', '2022-11-21 20:16:15'),
(31, 1, NULL, 0x3230332e3137352e36362e323534, 'owner@pharmacyherbel.com', '2022-11-21 20:37:08'),
(32, 1, NULL, 0x3230332e3137352e36362e323534, 'owner@pharmacyherbel.com', '2022-11-21 21:25:57'),
(33, 1, NULL, 0x3230332e3137352e36362e323534, 'owner@pharmacyherbel.com', '2022-11-21 21:43:58'),
(34, 1, NULL, 0x3230332e3137352e36362e323534, 'owner@pharmacyherbel.com', '2022-11-21 22:00:38'),
(35, 1, NULL, 0x3135342e3139322e31372e3536, 'owner@pharmacyherbel.com', '2022-11-22 04:12:40'),
(36, 1, NULL, 0x3230332e3137352e36362e323534, 'owner@pharmacyherbel.com', '2022-11-22 13:43:56'),
(37, 1, NULL, 0x3230332e3137352e36362e323534, 'owner@pharmacyherbel.com', '2022-11-22 13:47:14'),
(38, 1, NULL, 0x3230332e3137352e36362e3435, 'owner@pharmacyherbel.com', '2022-11-23 21:23:04'),
(39, 1, NULL, 0x3230332e3137352e36362e3435, 'owner@pharmacyherbel.com', '2022-11-26 14:54:43'),
(40, 1, NULL, 0x39342e34392e32372e313635, 'owner@pharmacyherbel.com', '2022-11-26 18:59:15'),
(41, 1, NULL, 0x3230362e38342e3133382e32, 'owner@pharmacyherbel.com', '2022-11-28 13:58:31'),
(42, 1, NULL, 0x3230362e38342e3133382e32, 'owner@pharmacyherbel.com', '2022-11-28 14:15:04'),
(43, 1, NULL, 0x3230362e38342e3133382e32, 'owner@pharmacyherbel.com', '2022-11-28 14:37:46'),
(44, 1, NULL, 0x3230362e38342e3135362e30, 'owner@pharmacyherbel.com', '2022-11-29 13:26:43'),
(45, 1, NULL, 0x3230362e38342e3135362e30, 'owner@pharmacyherbel.com', '2022-11-29 19:18:30'),
(46, 1, NULL, 0x3230362e38342e3135362e30, 'owner@pharmacyherbel.com', '2022-11-30 15:10:21'),
(47, 1, NULL, 0x3230362e38342e3135362e30, 'owner@pharmacyherbel.com', '2022-11-30 15:11:39'),
(48, 1, NULL, 0x3230362e38342e3135362e30, 'owner@pharmacyherbel.com', '2022-11-30 15:14:08'),
(49, 1, NULL, 0x3230362e38342e3135362e30, 'owner@pharmacyherbel.com', '2022-11-30 16:11:24'),
(50, 1, NULL, 0x3230362e38342e3135362e30, 'owner@pharmacyherbel.com', '2022-11-30 18:15:24'),
(51, 1, NULL, 0x3230362e38342e3135362e30, 'owner@pharmacyherbel.com', '2022-11-30 20:16:01'),
(52, 1, NULL, 0x3230362e38342e3135362e30, 'owner@pharmacyherbel.com', '2022-11-30 20:16:39'),
(53, 1, NULL, 0x3230362e38342e3135362e30, 'owner@pharmacyherbel.com', '2022-12-01 13:21:15'),
(54, 1, NULL, 0x3230362e38342e3135352e37, 'owner@pharmacyherbel.com', '2022-12-01 14:59:12'),
(55, 1, NULL, 0x3230362e38342e3135352e37, 'owner@pharmacyherbel.com', '2022-12-01 15:00:08'),
(56, 1, NULL, 0x3230362e38342e3135352e37, 'owner@pharmacyherbel.com', '2022-12-02 12:59:44'),
(57, 1, NULL, 0x3230362e38342e3135352e37, 'owner@pharmacyherbel.com', '2022-12-02 15:44:27'),
(58, 1, NULL, 0x3230362e38342e3135322e313534, 'owner@pharmacyherbel.com', '2022-12-05 13:02:53'),
(59, 1, NULL, 0x3131312e3131392e3137372e3139, 'owner@pharmacyherbel.com', '2022-12-05 17:49:09'),
(60, 1, NULL, 0x3131392e3136302e36342e313238, 'owner@pharmacyherbel.com', '2022-12-05 18:00:31'),
(61, 1, NULL, 0x3135342e3139322e33302e3234, 'owner@pharmacyherbel.com', '2022-12-05 18:23:13'),
(62, 1, NULL, 0x3135342e3139322e33302e3234, 'owner@pharmacyherbel.com', '2022-12-06 13:02:35'),
(63, 1, NULL, 0x3135342e3139322e33302e3234, 'owner@pharmacyherbel.com', '2022-12-06 15:19:31'),
(64, 1, NULL, 0x3135342e3139322e33302e3234, 'owner@pharmacyherbel.com', '2022-12-06 15:39:28'),
(65, 1, NULL, 0x3135342e3139322e33302e3234, 'owner@pharmacyherbel.com', '2022-12-06 15:41:13'),
(66, 1, NULL, 0x3230362e38342e3135362e3831, 'owner@pharmacyherbel.com', '2022-12-07 19:27:00'),
(67, 1, NULL, 0x3230362e38342e3135362e3831, 'owner@pharmacyherbel.com', '2022-12-07 21:16:07'),
(68, 1, NULL, 0x3230362e38342e3135362e3831, 'owner@pharmacyherbel.com', '2022-12-08 13:58:48'),
(69, 1, NULL, 0x3230362e38342e3135362e3831, 'owner@pharmacyherbel.com', '2022-12-08 14:14:40'),
(70, 1, NULL, 0x3230362e38342e3135362e3831, 'owner@pharmacyherbel.com', '2022-12-09 13:08:36'),
(71, 1, NULL, 0x3135342e3139322e33302e3936, 'owner@pharmacyherbel.com', '2022-12-09 14:32:15'),
(72, 1, NULL, 0x3230362e38342e3135342e313238, 'owner@pharmacyherbel.com', '2022-12-10 16:31:51'),
(73, 1, NULL, 0x3230362e38342e3135342e313238, 'owner@pharmacyherbel.com', '2022-12-10 19:39:18'),
(74, 1, NULL, 0x3230362e38342e3135342e313238, 'owner@pharmacyherbel.com', '2022-12-10 19:59:17'),
(75, 1, NULL, 0x3230362e38342e3135342e313238, 'owner@pharmacyherbel.com', '2022-12-10 20:14:31'),
(76, 1, NULL, 0x3230362e38342e3135342e313437, 'owner@pharmacyherbel.com', '2022-12-12 16:20:18'),
(77, 1, NULL, 0x3230362e38342e3135342e313437, 'owner@pharmacyherbel.com', '2022-12-12 16:41:04'),
(78, 1, NULL, 0x3230362e38342e3135342e313437, 'owner@pharmacyherbel.com', '2022-12-12 16:41:45'),
(79, 1, NULL, 0x3230362e38342e3135342e313437, 'owner@pharmacyherbel.com', '2022-12-12 16:42:49'),
(80, 1, NULL, 0x3230362e38342e3135342e313437, 'owner@pharmacyherbel.com', '2022-12-12 16:47:09'),
(81, 3, NULL, 0x3230362e38342e3135342e313437, 'anus.increatetech@gmail.com', '2022-12-12 17:32:49'),
(82, 3, NULL, 0x3230362e38342e3135342e313437, 'anus.increatetech@gmail.com', '2022-12-12 17:37:16'),
(83, 1, NULL, 0x3230362e38342e3135342e313437, 'owner@pharmacyherbel.com', '2022-12-12 17:43:34'),
(84, 3, NULL, 0x3230362e38342e3135342e313437, 'anus.increatetech@gmail.com', '2022-12-12 17:44:08'),
(85, 3, NULL, 0x3230362e38342e3135342e313437, 'anus.increatetech@gmail.com', '2022-12-12 17:48:05'),
(86, 1, NULL, 0x3230362e38342e3135342e313437, 'owner@pharmacyherbel.com', '2022-12-12 17:48:26'),
(87, 1, NULL, 0x3230362e38342e3135342e313437, 'owner@pharmacyherbel.com', '2022-12-13 13:16:25'),
(88, 3, NULL, 0x3230362e38342e3135342e313437, 'anus.increatetech@gmail.com', '2022-12-13 13:24:32'),
(89, 3, NULL, 0x3230362e38342e3135342e313437, 'anus.increatetech@gmail.com', '2022-12-13 13:28:10'),
(90, 3, NULL, 0x3230362e38342e3135342e313437, 'anus.increatetech@gmail.com', '2022-12-13 14:04:33'),
(91, 1, NULL, 0x3230362e38342e3135342e313437, 'owner@pharmacyherbel.com', '2022-12-13 14:15:59'),
(92, 1, NULL, 0x3230362e38342e3135342e313437, 'owner@pharmacyherbel.com', '2022-12-13 14:33:12'),
(93, 1, NULL, 0x3230362e38342e3135342e313437, 'owner@pharmacyherbel.com', '2022-12-13 14:41:20'),
(94, 1, NULL, 0x3230362e38342e3135342e313437, 'owner@pharmacyherbel.com', '2022-12-13 14:47:40'),
(95, 1, NULL, 0x3230362e38342e3135342e313437, 'owner@pharmacyherbel.com', '2022-12-13 14:51:59'),
(96, 4, NULL, 0x3230362e38342e3135342e313437, 'anus.increatetech@gmail.com', '2022-12-13 14:53:58'),
(97, 4, NULL, 0x3230362e38342e3135342e313437, 'anus.increatetech@gmail.com', '2022-12-13 15:20:41'),
(98, 9, NULL, 0x3230362e38342e3135342e313437, 'shahmubasher53@gmail.com', '2022-12-13 15:57:06'),
(99, 10, NULL, 0x3230362e38342e3135342e313437, 'test@checkdev.xyz', '2022-12-13 16:02:44'),
(100, 10, NULL, 0x3230362e38342e3135342e313437, 'test@checkdev.xyz', '2022-12-13 16:05:56'),
(101, 10, NULL, 0x3230362e38342e3135342e313437, 'test@checkdev.xyz', '2022-12-13 19:28:39'),
(102, 1, NULL, 0x3230362e38342e3135342e313437, 'owner@pharmacyherbel.com', '2022-12-13 20:16:44'),
(103, 1, NULL, 0x3230362e38342e3135342e313437, 'owner@pharmacyherbel.com', '2022-12-14 12:43:12'),
(104, 10, NULL, 0x3230362e38342e3135342e313437, 'test@checkdev.xyz', '2022-12-14 12:43:44'),
(105, 10, NULL, 0x3135342e3139322e31372e3536, 'test@checkdev.xyz', '2022-12-14 15:06:27'),
(106, 10, NULL, 0x3135342e3139322e31372e3536, 'test@checkdev.xyz', '2022-12-14 16:04:56'),
(107, 10, NULL, 0x3135342e3139322e31372e3536, 'test@checkdev.xyz', '2022-12-14 16:17:34'),
(108, 1, NULL, 0x3135342e3139322e31372e3536, 'owner@pharmacyherbel.com', '2022-12-14 17:25:40'),
(109, 1, NULL, 0x3230362e38342e3135342e313837, 'owner@pharmacyherbel.com', '2022-12-14 17:37:52'),
(110, 10, NULL, 0x3230362e38342e3135342e313837, 'test@checkdev.xyz', '2022-12-14 18:39:14'),
(111, 10, NULL, 0x3230362e38342e3135342e313837, 'test@checkdev.xyz', '2022-12-15 12:42:48'),
(112, 1, NULL, 0x3230362e38342e3135342e313837, 'owner@pharmacyherbel.com', '2022-12-15 12:44:22'),
(113, 10, NULL, 0x3230362e38342e3135332e313137, 'test@checkdev.xyz', '2022-12-15 15:15:35'),
(114, 10, NULL, 0x3230362e38342e3135332e313137, 'test@checkdev.xyz', '2022-12-15 15:18:39'),
(115, 1, NULL, 0x3230362e38342e3135332e313137, 'owner@pharmacyherbel.com', '2022-12-15 15:41:23'),
(116, 10, NULL, 0x3230362e38342e3135332e313137, 'test@checkdev.xyz', '2022-12-15 18:36:55'),
(117, 10, NULL, 0x3230362e38342e3135332e313137, 'test@checkdev.xyz', '2022-12-16 12:42:18'),
(118, 1, NULL, 0x3230362e38342e3135332e313137, 'owner@pharmacyherbel.com', '2022-12-16 12:43:56'),
(119, 10, NULL, 0x3230362e38342e3135332e313137, 'test@checkdev.xyz', '2022-12-16 12:56:40'),
(120, 1, NULL, 0x3230362e38342e3135332e313137, 'owner@pharmacyherbel.com', '2022-12-16 14:46:08'),
(121, 1, NULL, 0x3230362e38342e3135332e313137, 'owner@pharmacyherbel.com', '2022-12-16 18:29:26'),
(122, 1, NULL, 0x3230362e38342e3135332e313137, 'owner@pharmacyherbel.com', '2022-12-16 18:34:02'),
(123, 10, NULL, 0x3230362e38342e3135332e313137, 'test@checkdev.xyz', '2022-12-16 19:47:19'),
(124, 1, NULL, 0x3230362e38342e3135332e313137, 'owner@pharmacyherbel.com', '2022-12-16 19:49:42'),
(125, 1, NULL, 0x35302e36302e3135322e323334, 'owner@pharmacyherbel.com', '2022-12-17 21:31:53'),
(126, 1, NULL, 0x3135342e3139322e31372e3536, 'owner@pharmacyherbel.com', '2022-12-18 04:10:51'),
(127, 1, NULL, 0x3138382e34392e37372e3931, 'owner@pharmacyherbel.com', '2022-12-19 15:40:09'),
(128, 1, NULL, 0x322e35312e3132372e3931, 'owner@pharmacyherbel.com', '2022-12-19 16:33:33'),
(129, 1, NULL, 0x322e35312e3132372e3931, 'owner@pharmacyherbel.com', '2022-12-19 16:39:17'),
(130, 1, NULL, 0x322e35312e3132372e3931, 'owner@pharmacyherbel.com', '2022-12-19 16:50:18'),
(131, 1, NULL, 0x322e35312e3132372e3931, 'owner@pharmacyherbel.com', '2022-12-19 16:53:04'),
(132, 1, NULL, 0x322e35312e3132372e3931, 'owner@pharmacyherbel.com', '2022-12-19 16:54:50'),
(133, 1, NULL, 0x322e35312e3132372e3931, 'owner@pharmacyherbel.com', '2022-12-19 17:06:58'),
(134, 1, NULL, 0x3230362e38342e3135352e313033, 'owner@pharmacyherbel.com', '2022-12-19 18:34:48'),
(135, 1, NULL, 0x3230362e38342e3135352e313033, 'owner@pharmacyherbel.com', '2022-12-19 18:40:11'),
(136, 1, NULL, 0x3230362e38342e3135352e313033, 'owner@pharmacyherbel.com', '2022-12-19 18:58:15'),
(137, 1, NULL, 0x3231372e3136352e36302e3134, 'owner@pharmacyherbel.com', '2022-12-19 19:19:08'),
(138, 1, NULL, 0x3231372e3136352e36302e3134, 'owner@pharmacyherbel.com', '2022-12-19 21:06:20'),
(139, 1, NULL, 0x3231372e3136352e36302e3134, 'owner@pharmacyherbel.com', '2022-12-19 21:18:21'),
(140, 1, NULL, 0x3231372e3136352e36302e3134, 'owner@pharmacyherbel.com', '2022-12-19 21:27:44'),
(141, 1, NULL, 0x3230362e38342e3135322e3730, 'owner@pharmacyherbel.com', '2022-12-20 14:50:40'),
(142, 1, NULL, 0x3230362e38342e3135322e3730, 'owner@pharmacyherbel.com', '2022-12-20 19:11:49'),
(143, 1, NULL, 0x3230362e38342e3135322e3730, 'owner@pharmacyherbel.com', '2022-12-21 13:06:27'),
(144, 21, NULL, 0x3135342e3139322e31372e3536, 'anusahmad', '2022-12-21 19:17:22'),
(145, 21, NULL, 0x3135342e3139322e31372e3536, 'anusahmad', '2022-12-21 19:25:04'),
(146, 21, NULL, 0x3135342e3139322e31372e3536, 'anusahmad', '2022-12-21 19:28:53'),
(147, 1, NULL, 0x3135342e3139322e31372e3536, 'owner@pharmacyherbel.com', '2022-12-21 19:50:35'),
(148, 1, NULL, 0x3230362e38342e3135322e3730, 'owner@pharmacyherbel.com', '2022-12-21 19:50:37'),
(149, 1, NULL, 0x3230362e38342e3135322e3730, 'owner@pharmacyherbel.com', '2022-12-22 12:51:33'),
(150, 28, NULL, 0x3230362e38342e3135322e3730, 'syedmubasher433@gmail.com', '2022-12-22 13:50:42'),
(151, 28, NULL, 0x3230362e38342e3135322e3730, 'syedmubasher433@gmail.com', '2022-12-22 13:56:06'),
(152, 1, NULL, 0x33392e34302e31382e313632, 'owner@pharmacyherbel.com', '2022-12-22 18:13:18'),
(153, 1, NULL, 0x3230362e38342e3135322e3730, 'owner@pharmacyherbel.com', '2022-12-22 18:20:08'),
(154, 10, NULL, 0x3230362e38342e3135322e3730, 'test@checkdev.xyz', '2022-12-22 20:24:50'),
(155, 1, NULL, 0x3230362e38342e3135322e3730, 'owner@pharmacyherbel.com', '2022-12-22 20:45:59'),
(156, 31, NULL, 0x3230362e38342e3135322e3730, 'sarwarmujtaba@gmail.com', '2022-12-22 21:39:51'),
(157, 1, NULL, 0x3230362e38342e3135322e3730, 'owner@pharmacyherbel.com', '2022-12-22 21:41:15'),
(158, 1, NULL, 0x3230362e38342e3135322e3730, 'owner@pharmacyherbel.com', '2022-12-22 22:12:19'),
(159, 1, NULL, 0x3230362e38342e3135322e3730, 'owner@pharmacyherbel.com', '2022-12-23 13:59:41'),
(160, 1, NULL, 0x3230362e38342e3135322e3730, 'owner@pharmacyherbel.com', '2022-12-23 14:33:12'),
(161, 1, NULL, 0x3230362e38342e3135322e3730, 'owner@pharmacyherbel.com', '2022-12-23 14:36:15'),
(162, 1, NULL, 0x3230362e38342e3135322e3730, 'owner@pharmacyherbel.com', '2022-12-23 19:11:53'),
(163, 1, NULL, 0x3230362e38342e3135362e323333, 'owner@pharmacyherbel.com', '2022-12-26 18:11:58'),
(164, 1, NULL, 0x322e38382e3130302e313537, 'owner@pharmacyherbel.com', '2022-12-27 14:56:53'),
(165, 1, NULL, 0x3230362e38342e3135322e313037, 'owner@pharmacyherbel.com', '2022-12-27 15:54:59'),
(166, 1, NULL, 0x3230362e38342e3135322e313037, 'owner@pharmacyherbel.com', '2022-12-27 17:23:40'),
(167, 1, NULL, 0x322e38382e3130302e313537, 'owner@pharmacyherbel.com', '2022-12-27 17:46:58'),
(168, 1, NULL, 0x3230362e38342e3135322e313037, 'owner@pharmacyherbel.com', '2022-12-27 20:24:35'),
(169, 1, NULL, 0x322e38382e3130302e313537, 'owner@pharmacyherbel.com', '2022-12-28 14:17:57'),
(170, 1, NULL, 0x3230362e38342e3135322e313037, 'owner@pharmacyherbel.com', '2022-12-28 16:20:14'),
(171, 1, NULL, 0x3230362e38342e3135322e313037, 'owner@pharmacyherbel.com', '2022-12-28 18:17:12'),
(172, 1, NULL, 0x322e38382e3130302e313537, 'owner@pharmacyherbel.com', '2022-12-28 19:46:20'),
(173, 1, NULL, 0x3230362e38342e3135322e313037, 'owner@pharmacyherbel.com', '2022-12-29 20:15:47'),
(174, 1, NULL, 0x3230362e38342e3135322e313037, 'owner@pharmacyherbel.com', '2022-12-29 20:35:37'),
(175, 1, NULL, 0x3230362e38342e3135322e313037, 'owner@pharmacyherbel.com', '2022-12-29 21:33:06'),
(176, 1, NULL, 0x3230362e38342e3135322e313037, 'owner@pharmacyherbel.com', '2022-12-30 18:46:08'),
(177, 1, NULL, 0x3230362e38342e3135322e313037, 'owner@pharmacyherbel.com', '2022-12-30 19:17:17'),
(178, 1, NULL, 0x3230362e38342e3135322e313037, 'owner@pharmacyherbel.com', '2022-12-30 19:32:35'),
(179, 1, NULL, 0x3230362e38342e3135322e313037, 'owner@pharmacyherbel.com', '2022-12-30 20:09:50'),
(180, 1, NULL, 0x3230362e38342e3135322e313037, 'owner@pharmacyherbel.com', '2022-12-30 20:12:50'),
(181, 1, NULL, 0x3230362e38342e3135322e313037, 'owner@pharmacyherbel.com', '2022-12-30 20:16:30'),
(182, 1, NULL, 0x33372e35362e39382e3339, 'owner@pharmacyherbel.com', '2022-12-30 20:32:13'),
(183, 1, NULL, 0x3230362e38342e3135322e313037, 'owner@pharmacyherbel.com', '2022-12-30 21:00:30'),
(184, 1, NULL, 0x3230362e38342e3135322e313037, 'owner@pharmacyherbel.com', '2022-12-30 22:02:47'),
(185, 1, NULL, 0x3230362e38342e3135322e313037, 'owner@pharmacyherbel.com', '2022-12-30 22:12:36'),
(186, 1, NULL, 0x3230362e38342e3135322e313037, 'owner@pharmacyherbel.com', '2022-12-31 16:07:06'),
(187, 1, NULL, 0x3230362e38342e3135322e313037, 'owner@pharmacyherbel.com', '2022-12-31 16:08:52'),
(188, 1, NULL, 0x3137362e34342e3132322e313533, 'owner@pharmacyherbel.com', '2023-01-08 15:04:10'),
(189, 1, NULL, 0x3137362e34342e3132322e313533, 'owner@pharmacyherbel.com', '2023-01-08 17:26:26'),
(190, 1, NULL, 0x33372e3131312e3132382e313731, 'owner@pharmacyherbel.com', '2023-01-09 17:16:13'),
(191, 1, NULL, 0x3230362e38342e3135322e313539, 'owner@pharmacyherbel.com', '2023-01-09 17:26:07'),
(192, 1, NULL, 0x3137362e34342e3132322e313533, 'owner@pharmacyherbel.com', '2023-01-09 17:39:27'),
(193, 1, NULL, 0x3230362e38342e3135322e313539, 'owner@pharmacyherbel.com', '2023-01-09 19:29:40'),
(194, 1, NULL, 0x3230362e38342e3135322e313539, 'owner@pharmacyherbel.com', '2023-01-09 19:30:32'),
(195, 1, NULL, 0x3230362e38342e3135322e313539, 'owner@pharmacyherbel.com', '2023-01-09 19:56:46'),
(196, 1, NULL, 0x3230362e38342e3135322e313539, 'owner@pharmacyherbel.com', '2023-01-09 20:05:44'),
(197, 1, NULL, 0x322e35312e3132372e3931, 'owner@pharmacyherbel.com', '2023-01-11 14:52:51'),
(198, 1, NULL, 0x3138382e34392e3132322e3736, 'owner@pharmacyherbel.com', '2023-01-12 19:00:34'),
(199, 1, NULL, 0x3130332e3136392e36342e313730, 'owner@pharmacyherbel.com', '2023-01-15 15:16:54'),
(200, 1, NULL, 0x322e35312e3132372e3931, 'owner@pharmacyherbel.com', '2023-01-15 15:30:02'),
(201, 1, NULL, 0x3230362e38342e3135352e323236, 'owner@pharmacyherbel.com', '2023-01-16 18:14:55'),
(202, 1, NULL, 0x3230362e38342e3135352e323236, 'owner@pharmacyherbel.com', '2023-01-17 13:25:18'),
(203, 1, NULL, 0x3230362e38342e3135352e323236, 'owner@pharmacyherbel.com', '2023-01-18 18:14:32'),
(204, 1, NULL, 0x3230332e3137352e37332e3532, 'owner@pharmacyherbel.com', '2023-01-18 22:59:20'),
(205, 1, NULL, 0x3230362e38342e3135352e323236, 'owner@pharmacyherbel.com', '2023-01-20 18:38:24'),
(206, 1, NULL, 0x3230362e38342e3135332e34, 'owner@pharmacyherbel.com', '2023-01-24 18:40:54'),
(207, 1, NULL, 0x3230362e38342e3135332e34, 'owner@pharmacyherbel.com', '2023-01-24 18:43:35'),
(208, 36, NULL, 0x3230362e38342e3135332e34, 'testing@gmail.com', '2023-01-24 18:45:51'),
(209, 1, NULL, 0x3230362e38342e3135352e3130, 'owner@pharmacyherbel.com', '2023-02-01 14:59:55'),
(210, 1, NULL, 0x3230332e3137352e36362e3934, 'owner@pharmacyherbel.com', '2023-02-06 15:17:56'),
(211, 37, NULL, 0x3230332e3137352e36362e3934, 'pharmacist', '2023-02-06 16:27:55'),
(212, 1, NULL, 0x3230332e3137352e36362e3934, 'owner@pharmacyherbel.com', '2023-02-06 16:38:48'),
(213, 38, NULL, 0x3230332e3137352e36362e3934, 'testpharmacist', '2023-02-06 16:41:22'),
(214, 37, NULL, 0x3230332e3137352e36362e3934, 'pharmacist', '2023-02-06 16:42:10'),
(215, 1, NULL, 0x3230332e3137352e36362e3934, 'owner@pharmacyherbel.com', '2023-02-06 16:48:58'),
(216, 38, NULL, 0x3230332e3137352e36362e3934, 'testpharmacist', '2023-02-06 16:59:49'),
(217, 39, NULL, 0x3230332e3137352e36362e3934, 'dubaiadmin', '2023-02-06 17:05:58'),
(218, 1, NULL, 0x3230332e3137352e36362e3934, 'owner@pharmacyherbel.com', '2023-02-06 17:35:37'),
(219, 1, NULL, 0x3230332e3137352e36362e3934, 'owner@pharmacyherbel.com', '2023-02-06 20:06:38'),
(220, 1, NULL, 0x3230332e3137352e36362e3934, 'owner@pharmacyherbel.com', '2023-02-06 20:18:38'),
(221, 1, NULL, 0x3230332e3137352e36362e3934, 'owner@pharmacyherbel.com', '2023-02-07 13:45:05'),
(222, 1, NULL, 0x3230332e3137352e36362e3934, 'owner@pharmacyherbel.com', '2023-02-07 14:59:02'),
(223, 38, NULL, 0x3230332e3137352e36362e3934, 'testpharmacist', '2023-02-07 18:49:12'),
(224, 37, NULL, 0x3230332e3137352e36362e3934, 'pharmacist', '2023-02-07 18:58:00'),
(225, 38, NULL, 0x3230332e3137352e36362e3934, 'testpharmacist', '2023-02-07 19:58:38'),
(226, 38, NULL, 0x3230362e38342e3133382e3433, 'testpharmacist', '2023-02-08 17:38:59'),
(227, 1, NULL, 0x3230362e38342e3133382e3433, 'owner@pharmacyherbel.com', '2023-02-08 17:50:41'),
(228, 1, NULL, 0x3230362e38342e3135332e323039, 'owner@pharmacyherbel.com', '2023-02-11 15:11:06'),
(229, 1, NULL, 0x3230362e38342e3135332e323039, 'owner@pharmacyherbel.com', '2023-02-11 15:36:20'),
(230, 1, NULL, 0x3230362e38342e3135362e313038, 'owner@pharmacyherbel.com', '2023-02-13 19:56:04'),
(231, 38, NULL, 0x3230332e3137352e37332e3532, 'testpharmacist', '2023-02-14 16:35:14'),
(232, 38, NULL, 0x3230332e3137352e37332e3532, 'testpharmacist', '2023-02-15 16:22:09'),
(233, 1, NULL, 0x3230332e3137352e37332e3532, 'owner@pharmacyherbel.com', '2023-02-21 15:36:31'),
(234, 38, NULL, 0x3230332e3137352e37332e3532, 'testpharmacist', '2023-02-21 16:11:47'),
(235, 37, NULL, 0x3230332e3137352e37332e3532, 'pharmacist', '2023-02-21 16:12:50'),
(236, 37, NULL, 0x33392e34302e33312e3932, 'pharmacist', '2023-02-21 19:51:33'),
(237, 1, NULL, 0x33392e34302e33312e3932, 'owner@pharmacyherbel.com', '2023-02-21 20:06:25'),
(238, 1, NULL, 0x33392e34302e33312e3932, 'owner@pharmacyherbel.com', '2023-02-21 21:10:21'),
(239, 1, NULL, 0x33392e34302e33312e3932, 'owner@pharmacyherbel.com', '2023-02-21 22:02:58'),
(240, 37, NULL, 0x33392e34302e33312e3932, 'pharmacist', '2023-02-21 22:06:02'),
(241, 37, NULL, 0x3230332e3137352e37332e3532, 'pharmacist', '2023-02-22 03:07:58'),
(242, 1, NULL, 0x3230332e3137352e37332e3532, 'owner@pharmacyherbel.com', '2023-02-22 03:08:30'),
(243, 37, NULL, 0x3230332e3137352e37332e3532, 'pharmacist', '2023-02-22 03:09:05'),
(244, 1, NULL, 0x3230332e3137352e37332e3532, 'owner@pharmacyherbel.com', '2023-02-22 04:04:50'),
(245, 37, NULL, 0x3131362e37312e3138362e3737, 'pharmacist', '2023-02-22 16:29:06'),
(246, 38, NULL, 0x3131362e37312e3138302e3733, 'testpharmacist', '2023-02-22 16:33:49'),
(247, 37, NULL, 0x3131362e37312e3138302e3733, 'pharmacist', '2023-02-22 18:18:19'),
(248, 1, NULL, 0x3131362e37312e3138302e3733, 'owner@pharmacyherbel.com', '2023-02-22 19:19:43'),
(249, 38, NULL, 0x3131362e37312e3138302e3733, 'testpharmacist', '2023-02-22 19:22:25'),
(250, 37, NULL, 0x3131362e37312e3138302e3733, 'pharmacist', '2023-02-22 19:25:37'),
(251, 1, NULL, 0x3130312e35302e3132372e36, 'owner@pharmacyherbel.com', '2023-02-22 20:11:39'),
(252, 38, NULL, 0x3130312e35302e3132372e36, 'testpharmacist', '2023-02-23 14:26:57'),
(253, 1, NULL, 0x3130312e35302e3132372e36, 'owner@pharmacyherbel.com', '2023-02-23 14:56:30');

-- --------------------------------------------------------

--
-- Table structure for table `sma_variants`
--

CREATE TABLE `sma_variants` (
  `id` int(11) NOT NULL,
  `name` varchar(55) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_variants`
--

INSERT INTO `sma_variants` (`id`, `name`) VALUES
(10, 'STRENGHT'),
(11, 'PACK SIZE'),
(12, 'EXPIRY DATE'),
(13, 'BATCH NUMBER');

-- --------------------------------------------------------

--
-- Table structure for table `sma_warehouses`
--

CREATE TABLE `sma_warehouses` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `map` varchar(255) DEFAULT NULL,
  `phone` varchar(55) DEFAULT NULL,
  `email` varchar(55) DEFAULT NULL,
  `price_group_id` int(11) DEFAULT NULL,
  `warehouse_type` varchar(25) DEFAULT NULL,
  `country` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_warehouses`
--

INSERT INTO `sma_warehouses` (`id`, `code`, `name`, `address`, `map`, `phone`, `email`, `price_group_id`, `warehouse_type`, `country`) VALUES
(11, 'DXB001', 'DUBAI WH', '<p>DUBAI FREE ZONE</p>', NULL, '00971585280538', 'm.aly@ultramedhub.com', 0, 'warehouse', 6),
(12, 'RUH001', 'PHARMA DRUG STORE', '<p>RIYADH, NAMOUZAGIAH WAREHOUSE NO. 1, ALHAIR</p>', NULL, '+966568241418', 'eid@pharma.com.sa', 0, 'warehouse', 8),
(13, 'Test001', 'PAKWARE', '<p>Test Pakistan Warehouse</p>', NULL, '051123456', 'pakwarehouse@gmail.com', 0, 'warehouse', 8);

-- --------------------------------------------------------

--
-- Table structure for table `sma_warehouses_country`
--

CREATE TABLE `sma_warehouses_country` (
  `id` int(10) NOT NULL,
  `warehouses_id` int(10) NOT NULL,
  `country_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sma_warehouses_country`
--

INSERT INTO `sma_warehouses_country` (`id`, `warehouses_id`, `country_id`) VALUES
(34, 11, 6),
(35, 12, 8);

-- --------------------------------------------------------

--
-- Table structure for table `sma_warehouses_products`
--

CREATE TABLE `sma_warehouses_products` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `rack` varchar(55) DEFAULT NULL,
  `avg_cost` decimal(25,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_warehouses_products`
--

INSERT INTO `sma_warehouses_products` (`id`, `product_id`, `warehouse_id`, `quantity`, `rack`, `avg_cost`) VALUES
(143, 43, 11, '85.0000', NULL, '115.0000'),
(144, 43, 12, '0.0000', NULL, '115.0000'),
(145, 39, 12, '100.0000', NULL, '15.0000'),
(146, 38, 12, '99.0000', NULL, '20.0000'),
(147, 45, 11, '0.0000', NULL, '115.0000'),
(148, 45, 12, '0.0000', NULL, '115.0000'),
(149, 43, 13, '0.0000', NULL, '115.0000'),
(150, 46, 11, '0.0000', NULL, '0.0000'),
(151, 46, 12, '0.0000', NULL, '0.0000'),
(152, 46, 13, '0.0000', NULL, '0.0000'),
(153, 47, 11, '0.0000', NULL, '0.0000'),
(154, 47, 12, '0.0000', NULL, '0.0000'),
(155, 47, 13, '0.0000', NULL, '0.0000'),
(156, 48, 11, '0.0000', NULL, '0.0000'),
(157, 48, 12, '0.0000', NULL, '0.0000'),
(158, 48, 13, '0.0000', NULL, '0.0000');

-- --------------------------------------------------------

--
-- Table structure for table `sma_warehouses_products_variants`
--

CREATE TABLE `sma_warehouses_products_variants` (
  `id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `rack` varchar(55) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sma_warehouses_products_variants`
--

INSERT INTO `sma_warehouses_products_variants` (`id`, `option_id`, `product_id`, `warehouse_id`, `quantity`, `rack`) VALUES
(65, 41, 43, 11, '85.0000', NULL),
(66, 41, 43, 12, '0.0000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sma_wishlist`
--

CREATE TABLE `sma_wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sma_accounts`
--
ALTER TABLE `sma_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_accounts_groups`
--
ALTER TABLE `sma_accounts_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_id` (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `id` (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `sma_accounts_ledgers`
--
ALTER TABLE `sma_accounts_ledgers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_id` (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `id` (`id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `sma_accounts_settings`
--
ALTER TABLE `sma_accounts_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_id` (`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `sma_addresses`
--
ALTER TABLE `sma_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `sma_adjustments`
--
ALTER TABLE `sma_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `sma_adjustment_items`
--
ALTER TABLE `sma_adjustment_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `adjustment_id` (`adjustment_id`);

--
-- Indexes for table `sma_api_keys`
--
ALTER TABLE `sma_api_keys`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_api_limits`
--
ALTER TABLE `sma_api_limits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_api_logs`
--
ALTER TABLE `sma_api_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_aramex`
--
ALTER TABLE `sma_aramex`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_aramex_shippment`
--
ALTER TABLE `sma_aramex_shippment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_attachments`
--
ALTER TABLE `sma_attachments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_blog`
--
ALTER TABLE `sma_blog`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_blog_categories`
--
ALTER TABLE `sma_blog_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `sma_brands`
--
ALTER TABLE `sma_brands`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `sma_calendar`
--
ALTER TABLE `sma_calendar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_captcha`
--
ALTER TABLE `sma_captcha`
  ADD PRIMARY KEY (`captcha_id`),
  ADD KEY `word` (`word`);

--
-- Indexes for table `sma_cart`
--
ALTER TABLE `sma_cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_categories`
--
ALTER TABLE `sma_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `sma_combo_items`
--
ALTER TABLE `sma_combo_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_companies`
--
ALTER TABLE `sma_companies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `group_id_2` (`group_id`);

--
-- Indexes for table `sma_costing`
--
ALTER TABLE `sma_costing`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_countries`
--
ALTER TABLE `sma_countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_currencies`
--
ALTER TABLE `sma_currencies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_customer_groups`
--
ALTER TABLE `sma_customer_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_date_format`
--
ALTER TABLE `sma_date_format`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_deliveries`
--
ALTER TABLE `sma_deliveries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_deposits`
--
ALTER TABLE `sma_deposits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_directpay`
--
ALTER TABLE `sma_directpay`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_expenses`
--
ALTER TABLE `sma_expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_expense_categories`
--
ALTER TABLE `sma_expense_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `sma_gift_cards`
--
ALTER TABLE `sma_gift_cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `card_no` (`card_no`);

--
-- Indexes for table `sma_gift_card_topups`
--
ALTER TABLE `sma_gift_card_topups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `card_id` (`card_id`);

--
-- Indexes for table `sma_groups`
--
ALTER TABLE `sma_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_login_attempts`
--
ALTER TABLE `sma_login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_logs`
--
ALTER TABLE `sma_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_notifications`
--
ALTER TABLE `sma_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_order_ref`
--
ALTER TABLE `sma_order_ref`
  ADD PRIMARY KEY (`ref_id`);

--
-- Indexes for table `sma_pages`
--
ALTER TABLE `sma_pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_payments`
--
ALTER TABLE `sma_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_paypal`
--
ALTER TABLE `sma_paypal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_permissions`
--
ALTER TABLE `sma_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_pos_register`
--
ALTER TABLE `sma_pos_register`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_pos_settings`
--
ALTER TABLE `sma_pos_settings`
  ADD PRIMARY KEY (`pos_id`);

--
-- Indexes for table `sma_price_groups`
--
ALTER TABLE `sma_price_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `sma_printers`
--
ALTER TABLE `sma_printers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_products`
--
ALTER TABLE `sma_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `id` (`id`),
  ADD KEY `id_2` (`id`),
  ADD KEY `category_id_2` (`category_id`),
  ADD KEY `unit` (`unit`),
  ADD KEY `brand` (`brand`);

--
-- Indexes for table `sma_product_photos`
--
ALTER TABLE `sma_product_photos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_product_prices`
--
ALTER TABLE `sma_product_prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `price_group_id` (`price_group_id`);

--
-- Indexes for table `sma_product_variants`
--
ALTER TABLE `sma_product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_id_name` (`product_id`,`name`);

--
-- Indexes for table `sma_promos`
--
ALTER TABLE `sma_promos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_purchases`
--
ALTER TABLE `sma_purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `sma_purchase_items`
--
ALTER TABLE `sma_purchase_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_id` (`purchase_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sma_quotes`
--
ALTER TABLE `sma_quotes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `sma_quote_items`
--
ALTER TABLE `sma_quote_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quote_id` (`quote_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sma_refund`
--
ALTER TABLE `sma_refund`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_returns`
--
ALTER TABLE `sma_returns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `sma_return_items`
--
ALTER TABLE `sma_return_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `return_id` (`return_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `product_id_2` (`product_id`,`return_id`),
  ADD KEY `return_id_2` (`return_id`,`product_id`);

--
-- Indexes for table `sma_sales`
--
ALTER TABLE `sma_sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `sma_sale_items`
--
ALTER TABLE `sma_sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `product_id_2` (`product_id`,`sale_id`),
  ADD KEY `sale_id_2` (`sale_id`,`product_id`);

--
-- Indexes for table `sma_sessions`
--
ALTER TABLE `sma_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ci_sessions_timestamp` (`timestamp`);

--
-- Indexes for table `sma_settings`
--
ALTER TABLE `sma_settings`
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `sma_shop_settings`
--
ALTER TABLE `sma_shop_settings`
  ADD PRIMARY KEY (`shop_id`);

--
-- Indexes for table `sma_skrill`
--
ALTER TABLE `sma_skrill`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_sms_settings`
--
ALTER TABLE `sma_sms_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_stock_counts`
--
ALTER TABLE `sma_stock_counts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `sma_stock_count_items`
--
ALTER TABLE `sma_stock_count_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_count_id` (`stock_count_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sma_suspended_bills`
--
ALTER TABLE `sma_suspended_bills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_suspended_items`
--
ALTER TABLE `sma_suspended_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_tax_rates`
--
ALTER TABLE `sma_tax_rates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_transfers`
--
ALTER TABLE `sma_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `sma_transfer_items`
--
ALTER TABLE `sma_transfer_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transfer_id` (`transfer_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sma_units`
--
ALTER TABLE `sma_units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `base_unit` (`base_unit`);

--
-- Indexes for table `sma_users`
--
ALTER TABLE `sma_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`,`warehouse_id`,`biller_id`),
  ADD KEY `group_id_2` (`group_id`,`company_id`);

--
-- Indexes for table `sma_user_logins`
--
ALTER TABLE `sma_user_logins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_variants`
--
ALTER TABLE `sma_variants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_warehouses`
--
ALTER TABLE `sma_warehouses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `sma_warehouses_country`
--
ALTER TABLE `sma_warehouses_country`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sma_warehouses_products`
--
ALTER TABLE `sma_warehouses_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `sma_warehouses_products_variants`
--
ALTER TABLE `sma_warehouses_products_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `option_id` (`option_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `sma_wishlist`
--
ALTER TABLE `sma_wishlist`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sma_accounts`
--
ALTER TABLE `sma_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sma_accounts_groups`
--
ALTER TABLE `sma_accounts_groups`
  MODIFY `id` bigint(18) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `sma_accounts_ledgers`
--
ALTER TABLE `sma_accounts_ledgers`
  MODIFY `id` bigint(18) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `sma_addresses`
--
ALTER TABLE `sma_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT for table `sma_adjustments`
--
ALTER TABLE `sma_adjustments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sma_adjustment_items`
--
ALTER TABLE `sma_adjustment_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `sma_api_keys`
--
ALTER TABLE `sma_api_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sma_api_limits`
--
ALTER TABLE `sma_api_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sma_api_logs`
--
ALTER TABLE `sma_api_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sma_aramex_shippment`
--
ALTER TABLE `sma_aramex_shippment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `sma_attachments`
--
ALTER TABLE `sma_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sma_blog`
--
ALTER TABLE `sma_blog`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `sma_blog_categories`
--
ALTER TABLE `sma_blog_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `sma_brands`
--
ALTER TABLE `sma_brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sma_calendar`
--
ALTER TABLE `sma_calendar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sma_captcha`
--
ALTER TABLE `sma_captcha`
  MODIFY `captcha_id` bigint(13) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sma_categories`
--
ALTER TABLE `sma_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `sma_combo_items`
--
ALTER TABLE `sma_combo_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sma_companies`
--
ALTER TABLE `sma_companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=532;

--
-- AUTO_INCREMENT for table `sma_costing`
--
ALTER TABLE `sma_costing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `sma_countries`
--
ALTER TABLE `sma_countries`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sma_currencies`
--
ALTER TABLE `sma_currencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sma_customer_groups`
--
ALTER TABLE `sma_customer_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sma_date_format`
--
ALTER TABLE `sma_date_format`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sma_deliveries`
--
ALTER TABLE `sma_deliveries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sma_deposits`
--
ALTER TABLE `sma_deposits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sma_expenses`
--
ALTER TABLE `sma_expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sma_expense_categories`
--
ALTER TABLE `sma_expense_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sma_gift_cards`
--
ALTER TABLE `sma_gift_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sma_gift_card_topups`
--
ALTER TABLE `sma_gift_card_topups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sma_groups`
--
ALTER TABLE `sma_groups`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sma_login_attempts`
--
ALTER TABLE `sma_login_attempts`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `sma_logs`
--
ALTER TABLE `sma_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=461;

--
-- AUTO_INCREMENT for table `sma_notifications`
--
ALTER TABLE `sma_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sma_order_ref`
--
ALTER TABLE `sma_order_ref`
  MODIFY `ref_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sma_pages`
--
ALTER TABLE `sma_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sma_payments`
--
ALTER TABLE `sma_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `sma_permissions`
--
ALTER TABLE `sma_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sma_pos_register`
--
ALTER TABLE `sma_pos_register`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sma_price_groups`
--
ALTER TABLE `sma_price_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sma_printers`
--
ALTER TABLE `sma_printers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sma_products`
--
ALTER TABLE `sma_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `sma_product_photos`
--
ALTER TABLE `sma_product_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `sma_product_prices`
--
ALTER TABLE `sma_product_prices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sma_product_variants`
--
ALTER TABLE `sma_product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `sma_promos`
--
ALTER TABLE `sma_promos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sma_purchases`
--
ALTER TABLE `sma_purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `sma_purchase_items`
--
ALTER TABLE `sma_purchase_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `sma_quotes`
--
ALTER TABLE `sma_quotes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sma_quote_items`
--
ALTER TABLE `sma_quote_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sma_refund`
--
ALTER TABLE `sma_refund`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `sma_returns`
--
ALTER TABLE `sma_returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sma_return_items`
--
ALTER TABLE `sma_return_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sma_sales`
--
ALTER TABLE `sma_sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT for table `sma_sale_items`
--
ALTER TABLE `sma_sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `sma_sms_settings`
--
ALTER TABLE `sma_sms_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sma_stock_counts`
--
ALTER TABLE `sma_stock_counts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sma_stock_count_items`
--
ALTER TABLE `sma_stock_count_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sma_suspended_bills`
--
ALTER TABLE `sma_suspended_bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sma_suspended_items`
--
ALTER TABLE `sma_suspended_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sma_tax_rates`
--
ALTER TABLE `sma_tax_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sma_transfers`
--
ALTER TABLE `sma_transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sma_transfer_items`
--
ALTER TABLE `sma_transfer_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sma_units`
--
ALTER TABLE `sma_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sma_users`
--
ALTER TABLE `sma_users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `sma_user_logins`
--
ALTER TABLE `sma_user_logins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=254;

--
-- AUTO_INCREMENT for table `sma_variants`
--
ALTER TABLE `sma_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `sma_warehouses`
--
ALTER TABLE `sma_warehouses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `sma_warehouses_country`
--
ALTER TABLE `sma_warehouses_country`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `sma_warehouses_products`
--
ALTER TABLE `sma_warehouses_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=159;

--
-- AUTO_INCREMENT for table `sma_warehouses_products_variants`
--
ALTER TABLE `sma_warehouses_products_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `sma_wishlist`
--
ALTER TABLE `sma_wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sma_accounts_groups`
--
ALTER TABLE `sma_accounts_groups`
  ADD CONSTRAINT `sma_accounts_groups_fk_check_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `sma_accounts_groups` (`id`);

--
-- Constraints for table `sma_accounts_ledgers`
--
ALTER TABLE `sma_accounts_ledgers`
  ADD CONSTRAINT `sma_accounts_ledgers_fk_check_group_id` FOREIGN KEY (`group_id`) REFERENCES `sma_accounts_groups` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
