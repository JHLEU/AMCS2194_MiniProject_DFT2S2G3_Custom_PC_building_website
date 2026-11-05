CREATE DATABASE DB;
USE DB;
-- ==========================================
-- 1. 用户与权限管理 (RBAC & Auth)
-- ==========================================
CREATE TABLE Roles (
    RoleID INT AUTO_INCREMENT PRIMARY KEY,
    RoleName VARCHAR(50) NOT NULL, -- Admin, Staff_Warehouse, Staff_Tech, Customer
    Permissions JSON -- 可选：存储具体的页面访问权限
);

CREATE TABLE Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    RoleID INT NOT NULL,
    Username VARCHAR(50) UNIQUE NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    Phone VARCHAR(20),
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (RoleID) REFERENCES Roles(RoleID)
);

-- ==========================================
-- 2. 供应商与商品目录 (Catalog & Suppliers)
-- ==========================================
CREATE TABLE Suppliers (
    SupplierID INT AUTO_INCREMENT PRIMARY KEY,
    CompanyName VARCHAR(100) NOT NULL,
    ContactName VARCHAR(50),
    ContactPhone VARCHAR(20),
    Email VARCHAR(100)
);

CREATE TABLE Categories (
    CategoryID INT AUTO_INCREMENT PRIMARY KEY,
    CategoryName VARCHAR(50) NOT NULL -- CPU, GPU, RAM, Motherboard, PSU等
);

CREATE TABLE Products (
    ProductID INT AUTO_INCREMENT PRIMARY KEY,
    CategoryID INT NOT NULL,
    SupplierID INT NOT NULL,
    Brand VARCHAR(50),
    ProductName VARCHAR(100) NOT NULL,
    -- 兼容性与计算字段 (供前端 Custom Page & CPU Calculate 使用)
    TDP_Wattage INT DEFAULT 0, 
    SocketType VARCHAR(50),    -- 例如: LGA1700, AM5
    RamType VARCHAR(20),       -- 例如: DDR4, DDR5
    -- 基础价格与库存预警
    BaseCostPrice DECIMAL(10, 2), 
    SellingPrice DECIMAL(10, 2) NOT NULL,
    WarrantyMonths INT DEFAULT 12,
    LowStockThreshold INT DEFAULT 5, -- 库存预警线
    FOREIGN KEY (CategoryID) REFERENCES Categories(CategoryID),
    FOREIGN KEY (SupplierID) REFERENCES Suppliers(SupplierID)
);

-- ==========================================
-- 3. 仓库与序列号追踪 (Inventory & SN - ERP核心)
-- ==========================================
-- 记录每一个具体的物理硬件，实现精确的成本和保固追踪
CREATE TABLE InventoryItems (
    ItemID INT AUTO_INCREMENT PRIMARY KEY,
    ProductID INT NOT NULL,
    SerialNumber VARCHAR(100) UNIQUE NOT NULL, -- 序列号 (SN)
    ActualCostPrice DECIMAL(10, 2) NOT NULL,   -- 这批货的实际进货成本 (用于计算净利润)
    Status ENUM('InStock', 'Reserved', 'Sold', 'RMA', 'Lost') DEFAULT 'InStock',
    ReceivedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    OrderID INT NULL, -- 售出后绑定订单
    FOREIGN KEY (ProductID) REFERENCES Products(ProductID)
);

-- ==========================================
-- 4. 订单与现金流 (Orders & Cash Flow)
-- ==========================================
CREATE TABLE Orders (
    OrderID INT AUTO_INCREMENT PRIMARY KEY,
    CustomerID INT NOT NULL,
    OrderNumber VARCHAR(50) UNIQUE NOT NULL,
    TotalSellingAmount DECIMAL(10, 2) NOT NULL, -- 收入
    TotalCostAmount DECIMAL(10, 2) NOT NULL,    -- 总成本 (发货时从 InventoryItems 统计得出)
    -- 净利润 = TotalSellingAmount - TotalCostAmount
    OrderStatus ENUM('Pending', 'Paid', 'Assembling', 'Testing', 'Shipped', 'Completed', 'Cancelled') DEFAULT 'Pending',
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CustomerID) REFERENCES Users(UserID)
);

CREATE TABLE OrderItems (
    OrderItemID INT AUTO_INCREMENT PRIMARY KEY,
    OrderID INT NOT NULL,
    ProductID INT NOT NULL,
    Quantity INT NOT NULL,
    UnitPrice DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (OrderID) REFERENCES Orders(OrderID),
    FOREIGN KEY (ProductID) REFERENCES Products(ProductID)
);

CREATE TABLE Payments (
    PaymentID INT AUTO_INCREMENT PRIMARY KEY,
    OrderID INT NOT NULL,
    PaymentMethod VARCHAR(50), -- Stripe, FPX, Credit Card
    TransactionID VARCHAR(100),
    Amount DECIMAL(10, 2) NOT NULL,
    PaymentStatus ENUM('Pending', 'Success', 'Failed') DEFAULT 'Pending',
    PaymentDate DATETIME,
    FOREIGN KEY (OrderID) REFERENCES Orders(OrderID)
);

-- ==========================================
-- 5. 售后、保固与技术排期 (After-Sales & Tech Support)
-- ==========================================
CREATE TABLE Warranties (
    WarrantyID INT AUTO_INCREMENT PRIMARY KEY,
    ItemID INT NOT NULL, -- 绑定具体序列号 (SN)
    OrderID INT NOT NULL,
    StartDate DATE NOT NULL,
    EndDate DATE NOT NULL,
    Status ENUM('Active', 'Expired', 'Void') DEFAULT 'Active',
    FOREIGN KEY (ItemID) REFERENCES InventoryItems(ItemID),
    FOREIGN KEY (OrderID) REFERENCES Orders(OrderID)
);

CREATE TABLE SupportTickets (
    TicketID INT AUTO_INCREMENT PRIMARY KEY,
    CustomerID INT NOT NULL,
    WarrantyID INT NULL, -- 可选：是否关联特定保固
    IssueDescription TEXT NOT NULL,
    Status ENUM('Open', 'Diagnosing', 'WaitingForParts', 'Resolved', 'Closed') DEFAULT 'Open',
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CustomerID) REFERENCES Users(UserID),
    FOREIGN KEY (WarrantyID) REFERENCES Warranties(WarrantyID)
);

CREATE TABLE TechSchedules (
    ScheduleID INT AUTO_INCREMENT PRIMARY KEY,
    TicketID INT NOT NULL,
    TechnicianID INT NOT NULL, -- 负责的维修人员 (Users表)
    ScheduledDateTime DATETIME NOT NULL,
    EstimatedDurationHours INT DEFAULT 1,
    Notes TEXT,
    FOREIGN KEY (TicketID) REFERENCES SupportTickets(TicketID),
    FOREIGN KEY (TechnicianID) REFERENCES Users(UserID)
);

-- ==========================================
-- 6. 前端互动与保存配置 (Frontend Interactions)
-- ==========================================
CREATE TABLE SavedBuilds (
    BuildID INT AUTO_INCREMENT PRIMARY KEY,
    CustomerID INT NOT NULL,
    BuildName VARCHAR(100),
    ComponentsJSON JSON NOT NULL, -- 存储所选的 CPU, GPU 等配置信息
    TotalWattage INT,
    EstimatedPrice DECIMAL(10, 2),
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CustomerID) REFERENCES Users(UserID)
);

CREATE TABLE ChatLogs (
    ChatID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NULL, -- NULL 表示访客
    SessionToken VARCHAR(255),
    Sender ENUM('User', 'AI', 'HumanAgent') NOT NULL,
    Message TEXT NOT NULL,
    Timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);