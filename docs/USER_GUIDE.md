# tool_disclaimer — User Guide

## Table of Contents
1. [Overview](#overview)
2. [For End Users](#for-end-users)
3. [For Administrators](#for-administrators)
4. [For Developers](#for-developers)

---

## Overview

The Moodle Disclaimer Tool allows administrators to create, manage, and display disclaimers to users based on context, user roles, and time periods. Three context types are supported:

| Type | When shown | Modal buttons |
|------|-----------|---------------|
| `course` | On course view event | Accept / Decline / Cancel |
| `early_alert` | On early alert event | Accept / Decline / Cancel |
| `acknowledgement` | Every page load (all authenticated users) | OK only |

The `acknowledgement` type is designed for sitewide policy notices (e.g. AI usage policy) that every authenticated user must acknowledge once. It cannot be dismissed without clicking OK.

---

## For End Users

### Disclaimer Popups

When accessing Moodle you may encounter a disclaimer modal. There are two modal styles:

#### Accept/Decline Modal (course or early_alert)
- Appears when you view a specific course or trigger an early alert
- Contains **Yes**, **No**, and **Cancel** buttons
- Clicking **No** or **Cancel** may redirect you to another page
- You will not be shown the same disclaimer again after accepting

#### Acknowledgement Modal (sitewide policy)
- Appears on any Moodle page until you acknowledge it
- Contains a single **OK** button — it cannot be dismissed by clicking outside or pressing Escape
- After clicking OK the modal will never appear again on any course or page
- This is used for sitewide policy notices such as the AI usage policy

### When Disclaimers Appear

- When logging in and navigating to any page (acknowledgement type)
- When accessing specific courses (course type)
- When triggering early alerts (early_alert type)
- Based on your user role (for course/early_alert types)
- During specific time periods configured by administrators

---

## For Administrators

### Accessing the Plugin

Navigate to **Site Administration → Courses → Disclaimers**

Required capabilities:
- `tool/disclaimer:view` — view the disclaimer list
- `tool/disclaimer:edit` — create, edit, and manage user responses
- `tool/disclaimer:delete` — delete disclaimers
- `tool/disclaimer:reports` — view reports

### Creating a Disclaimer

1. Go to **Site Administration → Courses → Disclaimers**
2. Click **New**
3. Fill in:
   - **Name** — internal identifier
   - **Context** — `course`, `early_alert`, or `acknowledgement`
   - **Subject** — modal title
   - **Message** — rich text content
4. Configure publishing:
   - **Published** — enable/disable immediately
   - **Use Date Range** — optionally set start/end dates
5. For `course`/`early_alert` types:
   - Assign **Roles** to restrict which users see it
   - Set **Front Page Only** if applicable
   - Set **Redirect URL** for declined responses
6. For `acknowledgement` type:
   - **Do not assign roles** — the hook targets all authenticated users regardless
   - The modal cannot be declined; only OK is available
7. Click **Save**

### Context Type Guidance

| Context | Role restrictions? | Can be declined? | Trigger |
|---------|--------------------|-----------------|---------|
| `course` | Yes | Yes (redirect) | Course viewed event |
| `early_alert` | Yes | Yes (redirect) | Early alert event |
| `acknowledgement` | No (ignored) | No | Every page load |

### Managing Disclaimers

- **Edit** — modify any field on a published disclaimer
- **Publish/Unpublish** — toggle the published field
- **Date Ranges** — schedule automatic activation/deactivation
- **Delete** — removes disclaimer, associated roles, and all user logs (irreversible)

> Only one published disclaimer per context type is typically active at a time.

### User Response Management

Navigate to **Site Administration → Courses → Disclaimers → User Responses**

Search and filter all user responses. Use the **Reset** button to delete a user's log entry so they will be prompted again on their next relevant trigger.

**Common use cases:**
- Student accidentally declined a course disclaimer → reset their response
- Tester needs to re-see the acknowledgement modal → reset their acknowledgement log entry
- Compliance audit → filter by context and response status

### Best Practices

- Write disclaimers in clear, plain language
- Set date ranges when running time-limited notices
- Use role restrictions to target only the relevant audience
- Test with a non-admin account before publishing widely
- For the `acknowledgement` type, ensure only one is published at a time

---

## For Developers

### Adding a New Context Type

1. Add the new context value to the dropdown in `classes/forms/edit_disclaimer_form.php`
2. Add a language string for it in `lang/en/tool_disclaimer.php`
3. Create an event handler in `eventslib.php` (if event-driven) **or** add logic to the hook callback `classes/hook/output/before_standard_head_html_generation.php` (if page-load-driven)
4. Register the event in `db/events.php` if needed

### Hook Registration (Moodle 5.1)

The `acknowledgement` type uses `db/hooks.php` (not the deprecated `before_standard_html_head`):

```php
$callbacks = [
    [
        'hook'     => \core\hook\output\before_standard_head_html_generation::class,
        'callback' => \tool_disclaimer\hook\output\before_standard_head_html_generation::class . '::callback',
        'priority' => 500,
    ],
];
```

### AMD Module Pattern

For a new modal type, follow `acknowledgement_alert.js`:
1. Import `core/modal`, `core/modal_events`, `core/templates`, `core/ajax`, `core/notification`
2. Fetch disclaimer via `tool_disclaimer_get_disclaimer`
3. Create modal with `Modal.create()` using Bootstrap 5 options
4. Listen for `ModalEvents.shown` to manipulate DOM after render
5. Save response via `tool_disclaimer_response` web service

### Building AMD Modules

```bash
# From the Moodle root (requires Node 22)
npx grunt amd --root=public/admin/tool/disclaimer
```

### Key Web Services

| Method | Purpose | Parameters |
|--------|---------|------------|
| `tool_disclaimer_response` | Log user response | `disclaimerid, userid, response, objectid, attempt` |
| `tool_disclaimer_get_disclaimer` | Fetch disclaimer data | `id` |
| `tool_disclaimer_get_role` | Fetch role restrictions | `term` |
| `tool_disclaimer_delete` | Delete disclaimer + cascade | `id` |

### Checking a User's Acknowledgement Programmatically

```php
global $DB, $USER;

$acknowledged = $DB->record_exists_select(
    'tool_disclaimer_log',
    'disclaimerid = :did AND userid = :uid AND response = :resp AND objectid = 0',
    ['did' => $disclaimerid, 'uid' => $USER->id, 'resp' => 1]
);
```

### Creating an Acknowledgement Disclaimer Programmatically

```php
use tool_disclaimer\disclaimer;

$data = new stdClass();
$data->name          = 'AI Usage Policy';
$data->context       = 'acknowledgement';
$data->subject       = 'AI Usage Policy Acknowledgement';
$data->message       = 'By clicking OK you agree to the AI usage policy...';
$data->messageformat = FORMAT_HTML;
$data->published     = 1;
$data->frontpageonly = 0;
$data->usepublisheddate = 0;
$data->redirectto    = '';
$data->usermodified  = $USER->id;
$data->timecreated   = time();
$data->timemodified  = time();

$disclaimer = new disclaimer();
$id = $disclaimer->insert_record($data);
// Do NOT insert into tool_disclaimer_role — the hook ignores it for acknowledgement type.
```

### Bootstrap 5 Notes

- All modals use `core/modal` (Bootstrap 5) — **not** `core/modal_factory` (deprecated since Moodle 4.3)
- Backdrop and keyboard options: `backdrop: 'static', keyboard: false` for acknowledgement
- Remove dismiss buttons using `ModalEvents.shown` listener, not inline HTML removal
- Use `data-action="cancel"` on footer buttons for Bootstrap 5 modal binding

### Security Checklist

- [ ] `require_login()` on all PHP pages
- [ ] `require_capability()` on admin pages
- [ ] `confirm_sesskey()` on destructive actions
- [ ] `PARAM_INT`/`PARAM_TEXT` on all inputs
- [ ] Parameterized SQL (`$DB->get_records_sql` with named placeholders)
- [ ] `format_string()` / `fullname()` for user-facing output
- [ ] Mustache auto-escaping for template variables

---

## Troubleshooting

| Problem | Check |
|---------|-------|
| Acknowledgement modal not appearing | Is disclaimer published? Is context exactly `acknowledgement`? Purge Moodle caches. Check browser console for AMD errors. |
| Course modal not appearing | Is disclaimer published? Does user's role match role restrictions? Check `tool_disclaimer_log` for existing accepted record. |
| Modal appears but OK does nothing | Check browser network tab for AJAX errors. Verify `tool_disclaimer_response` web service is enabled. |
| Modal reappears after acknowledging | Verify `tool_disclaimer_log` record was inserted with `response=1, objectid=0`. |
| Hook not firing | Verify `db/hooks.php` is present and Moodle caches are purged after adding it. |
