# tool_disclaimer — Plugin Documentation

## Overview

The **tool_disclaimer** is a Moodle admin tool plugin that enables administrators to create, manage, and display disclaimer messages (legal notices, consent forms, AI policy acknowledgements, etc.) to users across the Moodle platform. Users must respond to these disclaimers before continuing, and their responses are logged for compliance and audit purposes.

**Version:** 2025061000  
**Requires:** Moodle 5.1+ (version 2022112814)  
**Author:** Patrick Thibaudeau  
**Maintained by:** York University IT Innovation  
**License:** GNU GPL v3 or later

---

## Key Features

1. **Multiple Disclaimer Types**: `course`, `early_alert`, and `acknowledgement` contexts
2. **Role-Based Targeting**: Restrict disclaimers to specific user roles (course/early_alert types)
3. **System-Wide Acknowledgement**: One-click OK modal shown to **all authenticated users** on every page load until acknowledged (acknowledgement type)
4. **Cross-Tab Suppression Guard**: Uses browser `localStorage` pending/saved states to prevent duplicate acknowledgement prompts across tabs while a save is in-flight
5. **Publication Control**: Set active/inactive status and optional date ranges
6. **User Consent Tracking**: All responses are logged for compliance
7. **Flexible Redirect**: Redirect users to specific URLs when they decline (course/early_alert types)
8. **Rich Text Support**: Formatted messages with embedded assets
9. **Bootstrap 5 Compatible**: All modals and UI use Bootstrap 5 classes
10. **Moodle 5.1 Hook System**: Uses `core\hook\output\before_standard_head_html_generation` (replaces deprecated `before_standard_html_head`)
11. **User Response Management**: Admin UI to search, view, and reset individual user responses

---

## Disclaimer Context Types

### `course`
- Triggered when a user views a course (`core\event\course_viewed`)
- Supports role restrictions, front-page-only mode, and date ranges
- Modal has **Accept / Decline / Cancel** buttons
- Declined users are redirected to a configured URL

### `early_alert`
- Triggered when an early alert event fires
- Same modal pattern as `course`
- Separate event handler `tool_disclaimer_earlyalert_viewed()` in `eventslib.php`

### `acknowledgement` *(added for York University Moodle 5.1)*
- **Not** event-driven — fires via the Moodle 5.1 hook system on **every page load**
- Targets **all authenticated non-guest users** — no role table check
- Modal has a **single "OK" button** only — no X, no Cancel, no Decline
- Modal is `backdrop: static` and `keyboard: false` — cannot be dismissed
- Recorded in `tool_disclaimer_log` with `response = 1, objectid = 0`
- Never shown again to the same user after acknowledgement
- Implemented via:
  - `classes/hook/output/before_standard_head_html_generation.php`
  - `amd/src/acknowledgement_alert.js`
  - `templates/acknowledgement_buttons.mustache`

---

## Database Schema

### Table: `tool_disclaimer`

| Field | Type | Description |
|-------|------|-------------|
| id | int | Primary key |
| name | varchar(255) | Disclaimer name (unique identifier) |
| context | varchar(25) | Context type: `course`, `early_alert`, `acknowledgement` |
| frontpageonly | int(1) | Display only on front page (1=yes, 0=no) |
| subject | varchar(255) | Disclaimer subject/title |
| message | text | Disclaimer message content |
| messageformat | varchar(50) | Message format |
| usepublisheddate | int(1) | Use date range for publishing |
| published | int(1) | Active status (1=active, 0=inactive) |
| publishedstart | int | Unix timestamp for publish start |
| publishedend | int | Unix timestamp for publish end |
| redirectto | varchar(255) | URL to redirect if user declines |
| categories | text | JSON array of selected categories |
| courses | text | JSON array of selected courses |
| usermodified | int | Last modifier user ID |
| timecreated | int | Unix timestamp of creation |
| timemodified | int | Unix timestamp of last modification |

### Table: `tool_disclaimer_role`

| Field | Type | Description |
|-------|------|-------------|
| id | int | Primary key |
| disclaimerid | int | FK to tool_disclaimer |
| role | varchar(255) | Role shortname |
| usermodified | int | Last modifier |
| timecreated | int | Created timestamp |
| timemodified | int | Modified timestamp |

> **Note:** The `tool_disclaimer_role` table is **not consulted** for `acknowledgement` type disclaimers. The hook fires for all authenticated non-guest users unconditionally.

### Table: `tool_disclaimer_log`

| Field | Type | Description |
|-------|------|-------------|
| id | int | Primary key |
| disclaimerid | int | FK to tool_disclaimer |
| userid | int | User who responded |
| objectid | int | Context object ID (course ID, or `0` for system-wide/acknowledgement) |
| response | int | 1=accepted/acknowledged, 0=declined, 2=cancelled |
| attempt | int | Attempt counter |
| usermodified | int | Last modifier |
| timecreated | int | Response timestamp |
| timemodified | int | Modified timestamp |

---

## Architecture

### Directory Structure

```
admin/tool/disclaimer/
├── docs/                              ← Documentation (this folder)
│   ├── PLUGIN_DOCUMENTATION.md
│   ├── USER_GUIDE.md
│   ├── USER_RESPONSES_FEATURE.md
│   └── ARCHITECTURE.txt
├── classes/
│   ├── crud.php                       ← Abstract CRUD base
│   ├── disclaimer.php                 ← Single disclaimer model
│   ├── disclaimers.php                ← Collection of disclaimers
│   ├── disclaimer_log.php             ← Single log record
│   ├── disclaimer_logs.php            ← Log collection
│   ├── helper.php                     ← Utility/page setup
│   ├── popup_notification.php         ← Template renderer
│   ├── external/
│   │   ├── disclaimer_ws.php          ← delete, get_disclaimer
│   │   ├── roles_ws.php               ← get_role
│   │   └── user_response_ws.php       ← response logging
│   ├── forms/
│   │   ├── edit_disclaimer_form.php
│   │   ├── disclaimer_filter_form.php
│   │   └── user_response_filter_form.php
│   ├── tables/
│   │   ├── disclaimer_table.php
│   │   └── user_response_table.php
│   ├── hook/output/
│   │   └── before_standard_head_html_generation.php  ← acknowledgement hook
│   └── task/
│       └── update_published_status.php
├── amd/src/
│   ├── disclaimer_alert.js            ← Accept/Decline modal (course/early_alert)
│   ├── acknowledgement_alert.js       ← OK-only modal (acknowledgement)
│   ├── disclaimers.js                 ← Admin table interactions
│   ├── roles.js                       ← Role selector
│   └── user_responses.js             ← User responses table
├── templates/
│   ├── disclaimer_modal.mustache
│   ├── modal_buttons.mustache
│   ├── acknowledgement_buttons.mustache  ← OK button for acknowledgement modal
│   └── user_response_action_buttons.mustache
├── eventslib.php                      ← course_viewed + earlyalert handlers
├── db/
│   ├── install.xml
│   ├── access.php
│   ├── services.php
│   ├── events.php
│   └── hooks.php                      ← Moodle 5.1 hook registration
├── settings.php
├── lib.php
├── version.php
└── css/general.css
```

---

## Moodle 5.1 Hook Registration

The `acknowledgement` type uses the **Moodle 5.1 hook API** (not the deprecated `before_standard_html_head` callback):

**`db/hooks.php`:**
```php
$callbacks = [
    [
        'hook'     => \core\hook\output\before_standard_head_html_generation::class,
        'callback' => \tool_disclaimer\hook\output\before_standard_head_html_generation::class . '::callback',
        'priority' => 500,
    ],
];
```

The hook callback at `classes/hook/output/before_standard_head_html_generation.php`:
1. Returns immediately for guests, CLI, and AJAX requests
2. Queries `tool_disclaimer` for `context = 'acknowledgement'` AND `published = 1`
3. For each unacknowledged disclaimer (checked via `tool_disclaimer_log`), evaluates `localStorage` suppression state
4. Injects the `acknowledgement_alert` AMD call only when suppression state is not `saved` and not valid `pending`
5. Invalid/stale suppression payloads are cleared before deciding to show
4. Only one modal per page load (breaks after first unacknowledged)

---

## Display Flow by Context Type

### course / early_alert
```
User views course / triggers early alert event
    ↓
eventslib.php handler fires
    ↓
Is site admin? → YES → Skip
    ↓ NO
Query tool_disclaimer WHERE context='course|early_alert' AND published=1
    ↓
Check tool_disclaimer_role restrictions
    ├─ Roles set → user must have matching role
    └─ No roles → applies to all
    ↓
Check tool_disclaimer_log for prior response
    ├─ response=1 → Skip (already accepted)
    └─ No record or response≠1 → Show modal
    ↓
$PAGE->requires->js_call_amd('tool_disclaimer/disclaimer_alert', 'init', params)
    ↓
Bootstrap modal: Accept / Decline / Cancel
    ↓
AJAX: tool_disclaimer_response web service
    ↓
Insert into tool_disclaimer_log
    ↓
Accept → stay on page | Decline/Cancel → redirect
```

### acknowledgement
```
Any authenticated page load
    ↓
Moodle 5.1 hook: before_standard_head_html_generation::callback()
    ↓
Guest or CLI/AJAX? → YES → Skip
    ↓ NO
Query tool_disclaimer WHERE context='acknowledgement' AND published=1
    ↓
Check tool_disclaimer_log: response=1 AND objectid=0 for this user
    ├─ Already acknowledged → Skip
    └─ Not acknowledged → continue
    ↓
Check localStorage key tool_disclaimer_saved_<disclaimerid>_<userid>
    ├─ {status: "saved"} → Skip
    ├─ {status: "pending", expires > now} → Skip
    └─ Missing / stale / invalid → continue
    ↓
$PAGE->requires->js_amd_inline(...) → require('tool_disclaimer/acknowledgement_alert').init(params)
    ↓
Static Bootstrap modal (no X, no dismiss, no keyboard): OK button only
    ↓
User clicks OK
    ↓
Set localStorage state to {status:"pending", expires: now+TTL}
    ↓
AJAX: tool_disclaimer_response (response=1, objectid=0)
    ↓
On success: set localStorage state to {status:"saved"}
On failure: remove localStorage key
    ↓
Insert into tool_disclaimer_log
    ↓
Modal destroyed — never shown again to this user
```

---

## JavaScript Modules

### `disclaimer_alert.js`
For `course` and `early_alert` types.
- Fetches disclaimer via `tool_disclaimer_get_disclaimer`
- Creates Bootstrap 5 modal with Accept/No/Cancel buttons
- Buttons activate after 2-second delay
- Sends response via `tool_disclaimer_response`
- Redirects on decline/cancel if `redirectto` is configured

### `acknowledgement_alert.js`
For `acknowledgement` type.
- Fetches disclaimer via `tool_disclaimer_get_disclaimer`
- Creates a `backdrop: 'static', keyboard: false` modal
- Removes all `.btn-close` / `[data-action="hide"]` elements after `ModalEvents.shown`
- Footer rendered from `acknowledgement_buttons.mustache` (single OK button)
- On OK: sets localStorage to `pending`, then calls `tool_disclaimer_response` with `response=1, objectid=0`
- On save success: updates localStorage to `saved`; on failure: removes key
- Uses `ModalEvents.hidden` guard to re-open unless acknowledgement save completed

---

## Web Services

| Service | Type | Description |
|---------|------|-------------|
| `tool_disclaimer_response` | write | Log user response (all types) |
| `tool_disclaimer_get_disclaimer` | read | Fetch disclaimer details |
| `tool_disclaimer_get_role` | read | Fetch roles for a disclaimer |
| `tool_disclaimer_delete` | write | Delete disclaimer + cascade |

---

## Capabilities

| Capability | Description | Default |
|-----------|-------------|---------|
| `tool/disclaimer:view` | View disclaimer list | Manager |
| `tool/disclaimer:edit` | Create/edit disclaimers + manage user responses | Manager |
| `tool/disclaimer:delete` | Delete disclaimers | Manager |
| `tool/disclaimer:reports` | View reports | Manager |

All capabilities require `CONTEXT_SYSTEM`.

---

## Admin Settings Location

**Site Administration → Courses → Disclaimers**
- **Disclaimers** — main management (`tool/disclaimer:view`)
- **User Responses** — response management (`tool/disclaimer:edit`)

---

## User Response Management

Administrators can search, view, and reset individual user disclaimer responses:

- **URL:** `/admin/tool/disclaimer/user_responses.php`
- **Search by:** User ID, First Name, Last Name
- **Filter by:** Context (`course`, `early_alert`, `acknowledgement`), Response status
- **Reset:** Deletes the log entry so the user will be prompted again on next trigger

> Resetting an `acknowledgement` response means the user will see the OK modal again on their next page load.

---

## Security

1. **Capability checks** on all admin pages (`require_capability`)
2. **CSRF protection** via `confirm_sesskey()` on reset actions
3. **Parameterized SQL** throughout — no raw concatenation
4. **Context validation** — system context only for all admin operations
5. **XSS prevention** — `format_string()`, `fullname()`, Mustache auto-escaping
6. **Site admin bypass** — site admins skip `course` and `early_alert` modals (acknowledgement modal skips guests only — admins still see it as it is sitewide policy)

---

## Performance

- Course/early_alert: Only queries on event trigger, not every page load
- Acknowledgement: One lightweight query per page load per user; breaks on first match
- Indexes on `tool_disclaimer_log.disclaimerid` for fast lookups
- Pagination (20/page default) on user response admin table

---

## Version History

| Date | Change |
|------|--------|
| 2025-01-04 | Initial release — course and early_alert contexts |
| 2026-02-12 | Added user responses admin UI and reset feature |
| 2026-04 | Added `acknowledgement` context type; migrated hook to Moodle 5.1 `before_standard_head_html_generation`; Bootstrap 5 modal compatibility; `acknowledgement_alert.js` AMD module |

---

## Support

For issues contact the York University IT Innovation team or the plugin maintainer.
