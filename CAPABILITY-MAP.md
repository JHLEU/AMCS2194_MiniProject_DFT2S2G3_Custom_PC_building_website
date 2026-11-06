# Capability Map: Custom PC & ERP System

| Module id | Responsibility | Depends on | Key Deliverables |
|---|---|---|---|
| `core-infra-auth` | Laravel 11 setup, legacy migration, DB schema & migrations, multi-role auth & RBAC | — | Clean Laravel 11 structure (old code moved to `legacy/`), migrations for all core tables, role middleware (`Admin`, `Staff_Warehouse`, `Staff_Tech`, `Customer`), authentication views |
| `inventory-supply` | Product catalog, supplier mapping, restock orders, serial numbers | `core-infra-auth` | Products CRUD, categories, suppliers, supplier mapping, restock PO flow, serial numbers lifecycle (`In-Stock`, `Reserved`, `Sold`, `RMA`, `Lost`), low-stock alert logic |
| `compatibility-engine` | Hardware compatibility & power estimation service | `inventory-supply` | Socket compatibility (e.g., LGA1700, AM5), RAM type (DDR4 vs DDR5), TDP wattage calculation and PSU headroom checks |
| `order-checkout` | Order processing, payment mock/sandbox, status lifecycle | `inventory-supply`, `core-infra-auth` | Customer checkout, Order model, Order status transitions (`Pending` → `Paid` → `Assembling` → `Testing` → `Shipped` → `Completed`), Payment gateway mock/demo |
| `finance-cashflow` | Revenue, batch cost tracking, net profit, financial reports | `order-checkout`, `inventory-supply` | Profit & Loss calculations, Revenue/Cost aggregation, Daily/Monthly financial summary views |
| `warranty-techsupport` | Warranty period CRUD, RMA tickets, technician scheduling | `order-checkout`, `inventory-supply` | Automatic warranty registration upon order sale, Customer support tickets, Technician schedule timeline |
| `frontend-storefront` | Customer portal (JusPC layout: Home, PC Builder, Reviews, CPU Calc, Contact) | `compatibility-engine`, `order-checkout` | JusPC-inspired UI using Blade + Plain CSS + Plain JS: Home, PC Builder interactive UI, Google reviews carousel, CPU bottleneck calculator, Order tracking lookup, Contact WhatsApp/Call |
| `ai-chat-service` | Customer service widget (AI PC consultant + backend chat log) | `frontend-storefront`, `warranty-techsupport` | Chat widget UI, placeholder RAG/LLM service contract, message logging, escalation to support ticket |

**Approved Build Order:**
`core-infra-auth` → `inventory-supply` → `compatibility-engine` → `order-checkout` → `finance-cashflow` → `warranty-techsupport` → `frontend-storefront` → `ai-chat-service`
