# Tasks: live-chat-service

## Task 1: ChatLog Model & Customer Storefront Chat API
**Description:**
- Implement `App\Models\ChatLog` with relationships, constants (`SENDER_CUSTOMER`, `SENDER_AGENT`, `SENDER_BOT`), and query scopes.
- Implement `App\Http\Controllers\Api\LiveChatController`:
  - `messages(Request $request)`: returns JSON of chat messages for active session.
  - `send(Request $request)`: stores customer message (`sender_type = 'Customer'`) and returns updated thread.
- Register routes in `routes/web.php` under `/api/chat`.

**Acceptance criteria:**
- [x] `ChatLog` model created with casting and customer relation.
- [x] `GET /api/chat/messages` returns messages for session.
- [x] `POST /api/chat/send` validates and saves customer messages.

**Verification:**
- [x] API endpoints verified via test suite assertions.

**Dependencies:** None

**Files likely touched:**
- `app/Models/ChatLog.php`
- `app/Http/Controllers/Api/LiveChatController.php`
- `routes/web.php`

**Estimated scope:** Medium (3 files)

---

## Task 2: Storefront Floating Live Chat Widget
**Description:**
- Create partial `resources/views/partials/chat-widget.blade.php`:
  - Fixed floating chat trigger button with online indicator.
  - Expandable chat dialog box with message history stream.
  - Differentiated message bubbles for `Customer` vs `Agent`.
  - Input field with send button and auto-scroll to bottom.
- Add client-side script `public/js/chat-widget.js`:
  - Handles widget open/close toggle.
  - Manages session ID in `localStorage`.
  - Sends messages via AJAX and polls `/api/chat/messages` every 4 seconds when open.
- Embed partial in `resources/views/layouts/app.blade.php`.

**Acceptance criteria:**
- [x] Chat bubble appears on all storefront pages.
- [x] Customer can type and send messages.
- [x] Agent replies appear dynamically in the widget.

**Verification:**
- [x] Browser rendering and UI interaction.

**Dependencies:** Task 1

**Files likely touched:**
- `resources/views/partials/chat-widget.blade.php`
- `public/js/chat-widget.js`
- `resources/views/layouts/app.blade.php`

**Estimated scope:** Medium (3 files)

---

## Task 3: ERP Customer Service Chat Workbench & Staff Reply
**Description:**
- Implement `App\Http\Controllers\Erp\LiveChatController`:
  - `index(Request $request)`: renders chat workbench with session list.
  - `history(string $sessionId)`: returns conversation history for session.
  - `reply(Request $request, string $sessionId)`: saves agent reply (`sender_type = 'Agent'`).
- Create view `resources/views/erp/chat/index.blade.php`:
  - Left pane: sessions queue with customer name/email, last message snippet, timestamp.
  - Right pane: full conversation transcript, customer info card, and agent reply box.
- Wire sidebar navigation in `resources/views/layouts/erp.blade.php` (`Customer Chat Logs` -> `route('erp.chat.index')`).
- Register ERP chat routes under `middleware(['auth', 'role:staff'])`.

**Acceptance criteria:**
- [x] `GET /erp/chat` renders staff conversation workbench.
- [x] Staff can select a session and view all messages.
- [x] `POST /erp/chat/{sessionId}/reply` sends agent message.
- [x] Sidebar link in ERP points to active route.

**Verification:**
- [x] ERP route list and feature test assertions.

**Dependencies:** Task 1, Task 2

**Files likely touched:**
- `app/Http/Controllers/Erp/LiveChatController.php`
- `resources/views/erp/chat/index.blade.php`
- `resources/views/layouts/erp.blade.php`
- `routes/web.php`

**Estimated scope:** Large (4 files)

---

## Task 4: Automated Feature Tests (`LiveChatServiceTest`)
**Description:**
- Create `tests/Feature/LiveChatServiceTest.php` covering:
  - Customer sending message via API and receiving session ID.
  - Customer fetching conversation history.
  - Staff viewing conversation list and transcripts in ERP.
  - Staff sending agent reply.
  - Customer receiving agent reply.
  - RBAC protection: guests and customers blocked from ERP chat (302/403).
- Run `php artisan test` across all test suites to confirm 100% pass rate.

**Acceptance criteria:**
- [x] All live chat feature tests pass.
- [x] Full test suite (`php artisan test`) passes with 0 regressions.

**Verification:**
- [x] `php artisan test` passes with 0 errors (69 tests, 493 assertions passing 100%).

**Dependencies:** Task 1, Task 2, Task 3

**Files likely touched:**
- `tests/Feature/LiveChatServiceTest.php`

**Estimated scope:** Medium (1 file)

---

## Checkpoint: Complete (`live-chat-service`)
- [x] All acceptance criteria in `SPEC-live-chat-service.md` met
- [x] `php artisan migrate:fresh --seed` and `php artisan test` pass 100%
- [x] Ready for human review
