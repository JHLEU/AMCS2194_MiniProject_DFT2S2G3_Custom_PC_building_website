# Specification: Module 8 — `live-chat-service` (Customer Service Live Chat)

## 1. Objective
Replace the speculative AI bot with a direct, real-time **Customer Service Live Chat** system connecting storefront visitors/customers with the ERP Customer Service & Support department:
1. **Storefront Floating Live Chat Widget**: An interactive widget floating in the bottom-right corner of the public storefront, enabling customers to message customer support and view agent responses.
2. **ERP Customer Service Chat Workbench (`/erp/chat`)**: A dedicated multi-conversation dashboard where staff and technicians can monitor incoming customer inquiries in real time, view conversation transcripts, and reply as official support agents.

---

## 2. Architecture & Database Design

Using the existing `chat_logs` table (`2026_11_06_000005_create_builds_and_chats_table.php`):
- `id`: Primary key
- `session_id`: Unique identifier per conversation (string 100, indexed)
- `customer_id`: Nullable foreign key → `users.id` (attached if user is logged in)
- `sender_type`: Enum (`Customer`, `Agent`, `Bot`)
- `message`: Text
- `timestamps()`: Created at, updated at

### 2.1 Model: `App\Models\ChatLog`
- Fillable: `['session_id', 'customer_id', 'sender_type', 'message']`
- Relationship: `belongsTo(User::class, 'customer_id')`
- Scopes:
  - `scopeForSession($query, string $sessionId)`
  - `scopeActiveSessions($query)`: Groups logs by `session_id` and orders by latest message.

---

## 3. Endpoints & Controllers

### 3.1 Storefront Customer Chat (`App\Http\Controllers\Api\LiveChatController`)
- `GET /api/chat/messages`: Returns JSON array of messages for the caller's session.
- `POST /api/chat/send`:
  - Validates `message` (min 1, max 1000 chars) and optional `session_id`.
  - Automatically associates `Auth::id()` if logged in.
  - Creates `ChatLog` with `sender_type = 'Customer'`.
  - Returns updated messages list and active `session_id`.

### 3.2 ERP Customer Service Desk (`App\Http\Controllers\Erp\LiveChatController`)
- `GET /erp/chat`: Triage workbench listing all customer chat sessions with recent message previews, timestamps, and customer names.
- `GET /erp/chat/{sessionId}`: Returns message history for a specific customer session.
- `POST /erp/chat/{sessionId}/reply`:
  - Validates `message`.
  - Creates `ChatLog` with `sender_type = 'Agent'`.
  - Returns latest conversation state.

---

## 4. UI Specifications

### 4.1 Storefront Widget (`resources/views/partials/chat-widget.blade.php`)
- Fixed bottom-right trigger badge: `💬 Live Support (Online)`.
- Click expands a chat box (360px wide, 480px tall).
- Header with online indicator and close button (✕).
- Chat message bubble list:
  - Customer messages: right-aligned, primary accent background.
  - Agent messages: left-aligned, dark card background with `👨‍💼 Support Specialist` badge.
- Sticky footer input with instant Send button and Enter key submission.
- Periodic polling (every 4 seconds when open) to fetch agent replies seamlessly.

### 4.2 ERP Customer Service Dashboard (`resources/views/erp/chat/index.blade.php`)
- Split-screen layout:
  - Left pane (340px): Active conversation sessions list with search, active indicators, and preview snippets.
  - Right pane (flex 1): Full conversation history with chronological chat bubbles, customer profile card (if registered), and quick reply input.

---

## 5. Security & RBAC Guardrails
- **Public Chat Safety**: HTML in messages is sanitized/escaped to prevent XSS. Rate limits apply to public message submission.
- **ERP Protection**: `/erp/chat/*` routes are strictly protected by `middleware(['auth', 'role:staff'])`. Unauthenticated visitors and customer role users are rejected (302/403).

---

## 6. Verification Criteria
1. Feature tests in `tests/Feature/LiveChatServiceTest.php`:
   - Customer can send message via widget API and receives session ID.
   - Staff/Admin can view chat queue in ERP workbench.
   - Staff can post agent reply to customer session.
   - Customer receives agent reply in subsequent message poll.
   - Unauthenticated visitors and customers cannot access ERP chat routes (403/302).
2. Full test suite (`php artisan test`) passes with 100% success rate.
