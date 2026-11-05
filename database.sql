-- ==============================================================================
-- Custom PC Building & ERP Supply Chain Management System
-- Merged Database Schema (DB.sql + database.sql)
-- Target Database: db
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db`;

-- Temporarily disable foreign key checks during schema creation/import
SET FOREIGN_KEY_CHECKS = 0;

-- Clean drop of existing tables and views
DROP VIEW IF EXISTS `InventoryItems`;
DROP TABLE IF EXISTS `ChatLogs`, `SavedBuilds`, `TechSchedules`, `SupportTickets`, `Warranties`, `Payments`, `OrderItems`, `Orders`, `serial_numbers`, `restock_order_items`, `restock_orders`, `supplier_hardware_mapping`, `products`, `suppliers`, `Categories`, `Users`, `Roles`;

CREATE TABLE `Roles` (
    `RoleID` INT AUTO_INCREMENT PRIMARY KEY,
    `RoleName` VARCHAR(50) NOT NULL, -- Admin, Staff_Warehouse, Staff_Tech, Customer
    `Permissions` JSON NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `Users` (
    `UserID` INT AUTO_INCREMENT PRIMARY KEY,
    `RoleID` INT NOT NULL,
    `Username` VARCHAR(50) UNIQUE NOT NULL,
    `PasswordHash` VARCHAR(255) NOT NULL,
    `Email` VARCHAR(100) UNIQUE NOT NULL,
    `Phone` VARCHAR(20) NULL,
    `CreatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_users_role` FOREIGN KEY (`RoleID`) REFERENCES `Roles` (`RoleID`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------------------
-- 2. 分类与供应商 (Categories & Suppliers)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `Categories`;
CREATE TABLE `Categories` (
    `CategoryID` INT AUTO_INCREMENT PRIMARY KEY,
    `CategoryName` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_name` VARCHAR(255) NOT NULL,
    `contact_person` VARCHAR(150) NULL,
    `phone` VARCHAR(50) NULL,
    `email` VARCHAR(150) NOT NULL,
    `address` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------------------
-- 3. 硬件商品目录 (Hardware Catalog & Specifications)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) NOT NULL,           -- CPU, GPU, Motherboard, RAM, Storage, PSU, Case, Cooling
    `category_id` INT NULL,
    `supplier_id` INT NULL,
    `brand` VARCHAR(100) NULL,
    `specs` TEXT NULL,                           -- Detailed specifications & compatibility notes
    `tdp_wattage` INT DEFAULT 0,                 -- TDP for PSU calculation & custom builder
    `socket_type` VARCHAR(50) NULL,              -- e.g., LGA1700, AM5
    `ram_type` VARCHAR(20) NULL,                 -- e.g., DDR4, DDR5
    `cost_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `selling_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `warranty_months` INT DEFAULT 12,
    `low_stock_threshold` INT NOT NULL DEFAULT 5,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `Categories` (`CategoryID`) ON DELETE SET NULL,
    CONSTRAINT `fk_products_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
    INDEX `idx_products_category` (`category`),
    INDEX `idx_products_brand` (`brand`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------------------
-- 4. 供应商-硬件关联与采购重置 (Supplier Mapping & Restock Orders)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `supplier_hardware_mapping`;
CREATE TABLE `supplier_hardware_mapping` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `supplier_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `supplier_sku` VARCHAR(100) NULL,
    `agreed_unit_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `lead_time_days` INT NOT NULL DEFAULT 3,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_supplier_product` (`supplier_id`, `product_id`),
    CONSTRAINT `fk_shm_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_shm_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `restock_order_items`;
DROP TABLE IF EXISTS `restock_orders`;

CREATE TABLE `restock_orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_code` VARCHAR(50) NOT NULL UNIQUE,
    `supplier_id` INT NOT NULL,
    `status` ENUM('Pending', 'Approved', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    `total_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `order_date` DATE NOT NULL,
    `expected_date` DATE NULL,
    `completed_at` DATETIME NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_ro_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `restock_order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `restock_order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    `unit_cost` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `subtotal` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_roi_order` FOREIGN KEY (`restock_order_id`) REFERENCES `restock_orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_roi_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------------------
-- 5. 序列号与物理库存追踪 (Serial Numbers & Batch Inventory)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `serial_numbers`;
CREATE TABLE `serial_numbers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sn_code` VARCHAR(100) NOT NULL UNIQUE,
    `product_id` INT NOT NULL,
    `status` ENUM('In-Stock', 'Reserved', 'Sold', 'RMA', 'Lost') NOT NULL DEFAULT 'In-Stock',
    `batch_cost` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `restock_order_id` INT NULL,
    `order_id` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_sn_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sn_restock` FOREIGN KEY (`restock_order_id`) REFERENCES `restock_orders` (`id`) ON DELETE SET NULL,
    INDEX `idx_sn_status` (`status`),
    INDEX `idx_sn_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------------------
-- 6. 订单与现金流 (Customer Orders, Order Items & Payments)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `Payments`;
DROP TABLE IF EXISTS `OrderItems`;
DROP TABLE IF EXISTS `Orders`;

CREATE TABLE `Orders` (
    `OrderID` INT AUTO_INCREMENT PRIMARY KEY,
    `CustomerID` INT NOT NULL,
    `OrderNumber` VARCHAR(50) UNIQUE NOT NULL,
    `TotalSellingAmount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `TotalCostAmount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `OrderStatus` ENUM('Pending', 'Paid', 'Assembling', 'Testing', 'Shipped', 'Completed', 'Cancelled') DEFAULT 'Pending',
    `CreatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_orders_customer` FOREIGN KEY (`CustomerID`) REFERENCES `Users` (`UserID`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `OrderItems` (
    `OrderItemID` INT AUTO_INCREMENT PRIMARY KEY,
    `OrderID` INT NOT NULL,
    `ProductID` INT NOT NULL,
    `Quantity` INT NOT NULL,
    `UnitPrice` DECIMAL(10, 2) NOT NULL,
    CONSTRAINT `fk_orderitems_order` FOREIGN KEY (`OrderID`) REFERENCES `Orders` (`OrderID`) ON DELETE CASCADE,
    CONSTRAINT `fk_orderitems_product` FOREIGN KEY (`ProductID`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `Payments` (
    `PaymentID` INT AUTO_INCREMENT PRIMARY KEY,
    `OrderID` INT NOT NULL,
    `PaymentMethod` VARCHAR(50) NULL,
    `TransactionID` VARCHAR(100) NULL,
    `Amount` DECIMAL(10, 2) NOT NULL,
    `PaymentStatus` ENUM('Pending', 'Success', 'Failed') DEFAULT 'Pending',
    `PaymentDate` DATETIME NULL,
    CONSTRAINT `fk_payments_order` FOREIGN KEY (`OrderID`) REFERENCES `Orders` (`OrderID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------------------
-- 7. 售后、保固与技术排期 (Warranties, Support Tickets & Tech Schedules)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `TechSchedules`;
DROP TABLE IF EXISTS `SupportTickets`;
DROP TABLE IF EXISTS `Warranties`;

CREATE TABLE `Warranties` (
    `WarrantyID` INT AUTO_INCREMENT PRIMARY KEY,
    `ItemID` INT NOT NULL, -- References serial_numbers(id)
    `OrderID` INT NOT NULL,
    `StartDate` DATE NOT NULL,
    `EndDate` DATE NOT NULL,
    `Status` ENUM('Active', 'Expired', 'Void') DEFAULT 'Active',
    CONSTRAINT `fk_warranties_item` FOREIGN KEY (`ItemID`) REFERENCES `serial_numbers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_warranties_order` FOREIGN KEY (`OrderID`) REFERENCES `Orders` (`OrderID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `SupportTickets` (
    `TicketID` INT AUTO_INCREMENT PRIMARY KEY,
    `CustomerID` INT NOT NULL,
    `WarrantyID` INT NULL,
    `IssueDescription` TEXT NOT NULL,
    `Status` ENUM('Open', 'Diagnosing', 'WaitingForParts', 'Resolved', 'Closed') DEFAULT 'Open',
    `CreatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_tickets_customer` FOREIGN KEY (`CustomerID`) REFERENCES `Users` (`UserID`) ON DELETE CASCADE,
    CONSTRAINT `fk_tickets_warranty` FOREIGN KEY (`WarrantyID`) REFERENCES `Warranties` (`WarrantyID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `TechSchedules` (
    `ScheduleID` INT AUTO_INCREMENT PRIMARY KEY,
    `TicketID` INT NOT NULL,
    `TechnicianID` INT NOT NULL,
    `ScheduledDateTime` DATETIME NOT NULL,
    `EstimatedDurationHours` INT DEFAULT 1,
    `Notes` TEXT NULL,
    CONSTRAINT `fk_schedules_ticket` FOREIGN KEY (`TicketID`) REFERENCES `SupportTickets` (`TicketID`) ON DELETE CASCADE,
    CONSTRAINT `fk_schedules_tech` FOREIGN KEY (`TechnicianID`) REFERENCES `Users` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------------------
-- 8. 前端互动与配置存储 (Saved Builds & Chat Logs)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `SavedBuilds`;
CREATE TABLE `SavedBuilds` (
    `BuildID` INT AUTO_INCREMENT PRIMARY KEY,
    `CustomerID` INT NOT NULL,
    `BuildName` VARCHAR(100) NULL,
    `ComponentsJSON` JSON NOT NULL,
    `TotalWattage` INT DEFAULT 0,
    `EstimatedPrice` DECIMAL(10, 2) DEFAULT 0.00,
    `CreatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_builds_customer` FOREIGN KEY (`CustomerID`) REFERENCES `Users` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `ChatLogs`;
CREATE TABLE `ChatLogs` (
    `ChatID` INT AUTO_INCREMENT PRIMARY KEY,
    `UserID` INT NULL,
    `SessionToken` VARCHAR(255) NULL,
    `Sender` ENUM('User', 'AI', 'HumanAgent') NOT NULL,
    `Message` TEXT NOT NULL,
    `Timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_chatlogs_user` FOREIGN KEY (`UserID`) REFERENCES `Users` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------------------
-- 9. 兼容性视图 (Compatibility View for Legacy InventoryItems)
-- ------------------------------------------------------------------------------
CREATE OR REPLACE VIEW `InventoryItems` AS
SELECT 
    `id` AS `ItemID`,
    `product_id` AS `ProductID`,
    `sn_code` AS `SerialNumber`,
    `batch_cost` AS `ActualCostPrice`,
    `status` AS `Status`,
    `created_at` AS `ReceivedDate`,
    `order_id` AS `OrderID`
FROM `serial_numbers`;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- SEED SAMPLE DATA
-- ==============================================================================

-- 1. Roles
INSERT INTO `Roles` (`RoleID`, `RoleName`, `Permissions`) VALUES
(1, 'Admin', '{"all": true}'),
(2, 'Staff_Warehouse', '{"inventory": true, "restock": true, "suppliers": true}'),
(3, 'Staff_Tech', '{"tickets": true, "schedules": true}'),
(4, 'Customer', '{"build": true, "orders": true}');

-- 2. Users (Passwords hashed for 'password123')
INSERT INTO `Users` (`UserID`, `RoleID`, `Username`, `PasswordHash`, `Email`, `Phone`) VALUES
(1, 1, 'admin', '$2y$10$wE8wB1Q4j1QfLskv7gW6j.0wz0wK9tD6lE1w9B8.H4U1K5x2b7u2q', 'admin@juspc.com', '+60 12-111 2222'),
(2, 2, 'warehouse_john', '$2y$10$wE8wB1Q4j1QfLskv7gW6j.0wz0wK9tD6lE1w9B8.H4U1K5x2b7u2q', 'john.wh@juspc.com', '+60 16-333 4444'),
(3, 3, 'tech_sam', '$2y$10$wE8wB1Q4j1QfLskv7gW6j.0wz0wK9tD6lE1w9B8.H4U1K5x2b7u2q', 'sam.tech@juspc.com', '+60 17-555 6666'),
(4, 4, 'alex_gamer', '$2y$10$wE8wB1Q4j1QfLskv7gW6j.0wz0wK9tD6lE1w9B8.H4U1K5x2b7u2q', 'alex@gmail.com', '+60 19-777 8888');

-- 3. Categories
INSERT INTO `Categories` (`CategoryID`, `CategoryName`) VALUES
(1, 'CPU'),
(2, 'GPU'),
(3, 'Motherboard'),
(4, 'RAM'),
(5, 'Storage'),
(6, 'PSU'),
(7, 'Case'),
(8, 'Cooling');

-- 4. Suppliers
INSERT INTO `suppliers` (`id`, `company_name`, `contact_person`, `phone`, `email`, `address`) VALUES
(1, 'TechData Distribution Asia', 'Marcus Vance', '+60 12-345 6789', 'marcus.v@techdata.my', 'Lot 45, Jalan Subang Utama, 47500 Subang Jaya, Selangor'),
(2, 'Synnex Metrodata Components', 'Sarah Tan', '+60 16-987 6543', 'orders@synnex-metro.com', 'Suite 12-B, Wisma Damansara, 50490 Kuala Lumpur'),
(3, 'ASUS Direct Enterprise APAC', 'Kevin Lee', '+60 11-2345 8899', 'apac_sales@asus-direct.com', 'Tech Park North, 11900 Bayan Lepas, Penang');

-- 5. Products Catalog
INSERT INTO `products` (`id`, `name`, `category`, `category_id`, `supplier_id`, `brand`, `specs`, `tdp_wattage`, `socket_type`, `ram_type`, `cost_price`, `selling_price`, `warranty_months`, `low_stock_threshold`) VALUES
(1, 'AMD Ryzen 7 7800X3D', 'CPU', 1, 1, 'AMD', 'Socket: AM5, Cores: 8, Threads: 16, TDP: 120W, PCIe 5.0, 3D V-Cache', 120, 'AM5', 'DDR5', 320.00, 399.00, 36, 5),
(2, 'Intel Core i7-14700K', 'CPU', 1, 1, 'Intel', 'Socket: LGA1700, Cores: 20 (8P+12E), TDP: 125W/253W Boost', 253, 'LGA1700', 'DDR5', 360.00, 429.00, 36, 4),
(3, 'NVIDIA GeForce RTX 4070 Super 12GB', 'GPU', 2, 1, 'NVIDIA', 'Length: 242mm, Slots: 2, Power: 220W, 1x 16-pin 12VHPWR', 220, NULL, NULL, 510.00, 599.00, 36, 3),
(4, 'ASUS ROG STRIX B650-A GAMING WIFI', 'Motherboard', 3, 3, 'ASUS', 'Form Factor: ATX, Socket: AM5, RAM: 4x DDR5, M.2 Slots: 3', 50, 'AM5', 'DDR5', 190.00, 239.00, 36, 4),
(5, 'Corsair Vengeance RGB 32GB (2x16GB)', 'RAM', 4, 2, 'Corsair', 'Type: DDR5, Speed: 6000MHz, Latency: CL30, Voltage: 1.35V', 20, NULL, 'DDR5', 95.00, 129.00, 60, 6),
(6, 'Samsung 990 PRO 2TB NVMe SSD', 'Storage', 5, 2, 'Samsung', 'Form: M.2 2280, Interface: PCIe 4.0 x4, Read: 7450 MB/s, Write: 6900 MB/s', 10, NULL, NULL, 140.00, 179.00, 60, 5),
(7, 'Corsair RM850x 850W Fully Modular', 'PSU', 6, 2, 'Corsair', 'Wattage: 850W, Efficiency: 80+ Gold, Form Factor: ATX', 850, NULL, NULL, 110.00, 149.00, 120, 4);

-- 6. Supplier-Hardware Mapping
INSERT INTO `supplier_hardware_mapping` (`supplier_id`, `product_id`, `supplier_sku`, `agreed_unit_price`, `lead_time_days`) VALUES
(1, 1, 'TD-CPU-AM5-7800X3D', 315.00, 2),
(1, 2, 'TD-CPU-1700-14700K', 355.00, 2),
(1, 3, 'TD-GPU-4070S-12G', 505.00, 4),
(2, 5, 'SYN-RAM-COR-DDR5-32G', 92.00, 1),
(2, 6, 'SYN-SSD-SAM-990P-2T', 135.00, 2),
(2, 7, 'SYN-PSU-RM850X', 108.00, 3),
(3, 4, 'ASUS-MB-B650A-STRIX', 185.00, 3);

-- 7. Restock Orders
INSERT INTO `restock_orders` (`id`, `order_code`, `supplier_id`, `status`, `total_amount`, `order_date`, `completed_at`, `notes`) VALUES
(1, 'RO-20261101-001', 1, 'Completed', 2680.00, '2026-11-01', '2026-11-03 14:30:00', 'Initial batch restocking for Ryzen 7800X3D and RTX 4070S'),
(2, 'RO-20261104-002', 2, 'Approved', 1144.00, '2026-11-04', NULL, 'Expedited order for DDR5 RAM and Samsung SSD units'),
(3, 'RO-20261105-003', 3, 'Pending', 740.00, '2026-11-05', NULL, 'Stocking ASUS ROG B650 motherboards for weekend PC builds');

-- 8. Restock Order Items
INSERT INTO `restock_order_items` (`restock_order_id`, `product_id`, `quantity`, `unit_cost`, `subtotal`) VALUES
(1, 1, 4, 315.00, 1260.00),
(1, 3, 2, 505.00, 1010.00),
(1, 2, 1, 410.00, 410.00),
(2, 5, 6, 92.00, 552.00),
(2, 6, 4, 135.00, 540.00),
(2, 7, 2, 26.00, 52.00),
(3, 4, 4, 185.00, 740.00);

-- 9. Serial Numbers
INSERT INTO `serial_numbers` (`sn_code`, `product_id`, `status`, `batch_cost`, `restock_order_id`) VALUES
('SN-7800X3D-001', 1, 'In-Stock', 320.00, 1),
('SN-7800X3D-002', 1, 'In-Stock', 320.00, 1),
('SN-7800X3D-003', 1, 'In-Stock', 320.00, 1),
('SN-7800X3D-004', 1, 'Sold', 320.00, 1),
('SN-7800X3D-005', 1, 'RMA', 320.00, 1),
('SN-14700K-001', 2, 'In-Stock', 360.00, 1),
('SN-14700K-002', 2, 'In-Stock', 360.00, 1),
('SN-4070S-001', 3, 'In-Stock', 510.00, 1),
('SN-4070S-002', 3, 'In-Stock', 510.00, 1),
('SN-B650A-001', 4, 'In-Stock', 190.00, 3),
('SN-B650A-002', 4, 'In-Stock', 190.00, 3),
('SN-RAM32G-001', 5, 'In-Stock', 95.00, 2),
('SN-RAM32G-002', 5, 'In-Stock', 95.00, 2),
('SN-RAM32G-003', 5, 'In-Stock', 95.00, 2),
('SN-RAM32G-004', 5, 'In-Stock', 95.00, 2),
('SN-990P-001', 6, 'In-Stock', 140.00, 2),
('SN-990P-002', 6, 'In-Stock', 140.00, 2),
('SN-990P-003', 6, 'In-Stock', 140.00, 2),
('SN-RM850X-001', 7, 'In-Stock', 110.00, 2);
