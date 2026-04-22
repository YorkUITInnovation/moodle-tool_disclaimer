# tool_disclaimer — User Responses Feature

A dedicated administrative interface allows administrators to search for and manage user responses to all disclaimer types. This feature is essential for resetting responses when users mistakenly decline a disclaimer, or for compliance auditing.

---

## Files

| File | Purpose |
|------|---------|
| `user_responses.php` | Main page — search and view user responses |
| `reset_response.php` | Confirmation page for resetting a response |
| `classes/forms/user_response_filter_form.php` | Moodle form for filters |
| `classes/tables/user_response_table.php` | `table_sql` implementation |
| `templates/user_response_action_buttons.mustache` | Reset button template |
| `amd/src/user_responses.js` | AMD module for page interactivity |

---

## Navigation Path

**Site Administration → Courses → Disclaimers → User Responses**

URL: `/admin/tool/disclaimer/user_responses.php`

---

## Access Control

Requires the `tool/disclaimer:edit` capability (system context). Limited to Site Administrators and Managers by default. Teachers and Students cannot access this page.

---

## Search & Filter Options

| Field | Type | Description |
|-------|------|-------------|
| User ID | Integer (exact) | Match by Moodle user ID |
| First Name | Text (partial) | Case-insensitive LIKE search |
| Last Name | Text (partial) | Case-insensitive LIKE search |
| Context | Dropdown | `all`, `course`, `early_alert`, `acknowledgement` |
| Response Status | Dropdown | `all`, `accepted`, `declined` |

---

## Table Columns

- User ID
- First Name
- Last Name
- Email
- Disclaimer Name
- Context (course / early_alert / acknowledgement)
- Response Status (green badge = accepted, red badge = declined)
- Attempt Count
- Response Date (formatted)
- Actions (Reset button)

Table is sortable and paginated at 20 records per page.

---

## Reset Response Flow

1. Admin clicks **Reset** on a user response row
2. Browser navigates to `reset_response.php?id={logid}`
3. Confirmation page shows user name, email, disclaimer name and context
4. Admin clicks **Continue**
5. Server validates: `confirm_sesskey()` + `require_capability()` + record exists
6. Record deleted from `tool_disclaimer_log`
7. Redirect to `user_responses.php` with success notification
8. User will see the disclaimer again on their next relevant trigger

> **Acknowledgement reset:** Deleting the log entry means the user will see the OK-only acknowledgement modal on their very next page load.

---

## Database Query

```sql
SELECT dl.id, dl.userid, u.firstname, u.lastname, u.email,
       d.name AS disclaimername, d.context,
       dl.response, dl.attempt, dl.timecreated
FROM {tool_disclaimer_log} dl
JOIN {user} u ON u.id = dl.userid
JOIN {tool_disclaimer} d ON d.id = dl.disclaimerid
WHERE [filter conditions]
ORDER BY dl.timecreated DESC
```

---

## Security

1. `require_login()` on all pages — no guest access
2. `require_capability('tool/disclaimer:edit', $context)` — system context
3. `PARAM_INT`, `PARAM_TEXT`, `PARAM_ALPHA` type enforcement on all inputs
4. `$DB->sql_like_escape()` for LIKE queries
5. `confirm_sesskey()` on reset action (CSRF protection)
6. Two-step confirmation before deletion
7. `format_string()` / `fullname()` / Mustache auto-escaping for XSS prevention

---

## Use Cases

### User mistakenly declined a course disclaimer
1. Admin searches by student name
2. Filters by context = `course` and status = `declined`
3. Clicks Reset → confirms
4. Student sees disclaimer again on next course access

### Reset AI policy acknowledgement for testing
1. Admin filters by context = `acknowledgement`
2. Locates the user record
3. Clicks Reset
4. User sees the OK-only acknowledgement modal on their next page load

### Compliance audit
1. Admin filters by context = `course` AND status = `declined`
2. Reviews which users declined and when
3. Takes appropriate action

---

## Performance

- Single JOIN query across 3 tables with parameterized WHERE
- Server-side pagination (20 per page default)
- Server-side column sorting using indexed fields
- No N+1 queries

---

## Created

February 12, 2026 — Updated April 2026 to include `acknowledgement` context type.
