# Moodle Disclaimer Tool Plugin - Complete Documentation

## Overview

The **tool_disclaimer** is a Moodle admin tool plugin that enables administrators to create, manage, and display disclaimer messages (legal notices, consent forms, etc.) to users across the Moodle platform. Users must respond to these disclaimers before continuing, and their responses are logged for compliance and audit purposes.

**Version:** 2025061000  
**Requires:** Moodle 4.4+ (version 2022112814)  
**Author:** Patrick Thibaudeau  
**License:** GNU GPL v3 or later

---

## Key Features

1. **Multiple Disclaimer Management**: Create and manage multiple disclaimers for different contexts
2. **Context-Based Display**: Show disclaimers in different contexts (courses, early alerts)
3. **Role-Based Targeting**: Restrict disclaimers to specific user roles
4. **Publication Control**: Set active/inactive status and date ranges for publishing
5. **User Consent Tracking**: Log all user responses with attempt counts
6. **Flexible Redirect**: Redirect users to specific URLs if they decline the disclaimer
7. **Rich Text Support**: Support for formatted messages with embedded assets
8. **Search & Filter**: Filter disclaimers by name for easy management

---

## Database Schema

### Table: `tool_disclaimer`
Stores the disclaimer records with their content and configuration.

| Field | Type | Description |
|-------|------|-------------|
| id | int | Primary key |
| name | varchar(255) | Disclaimer name (unique identifier) |
| context | varchar(25) | Context type: 'course', 'early_alert' |
| frontpageonly | int(1) | Display only on front page (1=yes, 0=no) |
| subject | varchar(255) | Disclaimer subject/title |
| message | text | Disclaimer message content |
| messageformat | varchar(50) | Message format (HTML, plain text, etc.) |
| usepublisheddate | int(1) | Use date range for publishing (1=yes, 0=no) |
| published | int(1) | Active status (1=active, 0=inactive) |
| publishedstart | int | Unix timestamp for publish start date |
| publishedend | int | Unix timestamp for publish end date |
| redirectto | varchar(255) | URL to redirect if user declines (default: /my) |
| categories | text | JSON array of selected course categories |
| courses | text | JSON array of selected courses |
| usermodified | int | ID of last user to modify this record |
| timecreated | int | Unix timestamp of creation |
| timemodified | int | Unix timestamp of last modification |

### Table: `tool_disclaimer_role`
Stores roles that trigger the display of specific disclaimers.

| Field | Type | Description |
|-------|------|-------------|
| id | int | Primary key |
| disclaimerid | int | Foreign key to tool_disclaimer |
| role | varchar(255) | Role shortname (e.g., 'student', 'teacher') |
| usermodified | int | ID of last user to modify this record |
| timecreated | int | Unix timestamp of creation |
| timemodified | int | Unix timestamp of last modification |

### Table: `tool_disclaimer_log`
Tracks user responses to disclaimers for compliance auditing.

| Field | Type | Description |
|-------|------|-------------|
| id | int | Primary key |
| disclaimerid | int | Foreign key to tool_disclaimer |
| userid | int | ID of the user responding |
| objectid | int | Context object ID (e.g., course ID, 0 for system-wide) |
| response | int | User response: 1=accepted, 0 or 2=declined |
| attempt | int | Attempt counter (how many times user has responded) |
| usermodified | int | ID of last user to modify this record |
| timecreated | int | Unix timestamp of response |
| timemodified | int | Unix timestamp of last modification |

---

## Core Classes & Architecture

### Class Hierarchy

```
crud (Abstract Base Class)
  ├── disclaimer
  ├── disclaimer_log
  
disclaimers (Collection Class)
disclaimer_logs (Collection Class)

helper (Utility Class)
popup_notification (Template Renderer)

External Web Services:
  ├── tool_disclaimer_ws
  ├── roles_ws
  └── user_response_ws
```

### Core Classes

#### `crud` (classes/crud.php)
**Abstract base class for database operations.**

Methods:
- `get_record()` - Retrieves a record by ID
- `delete_record()` - Deletes a record
- `insert_record($data)` - Inserts a new record
- `update_record($data)` - Updates an existing record

#### `disclaimer` (classes/disclaimer.php)
**Represents a single disclaimer record with full CRUD operations.**

Key Properties:
- `id`, `name`, `subject`, `message`
- `context`, `contextpath`
- `frontpageonly`
- `published`, `publishedstart`, `publishedend`, `usepublisheddate`
- `categories`, `courses`, `redirectto`
- `usermodified`, `timecreated`, `timemodified`

Key Methods:
- `__construct($id = 0)` - Constructor (loads record if ID provided)
- `get_roles()` - Returns array of roles that trigger this disclaimer
- `get_frontpageonly()` - Returns frontpageonly flag value
- Inherited CRUD methods from parent class

#### `disclaimers` (classes/disclaimers.php)
**Collection class for retrieving all disclaimers.**

Methods:
- `__construct()` - Loads all disclaimer records
- `get_records()` - Returns all disclaimer records
- `get_select_array()` - Returns array suitable for form select elements

#### `disclaimer_log` (classes/disclaimer_log.php)
**Represents a user's response to a disclaimer.**

Key Properties:
- `disclaimerid` - Which disclaimer
- `userid` - Which user
- `objectid` - Context object (course ID, etc.)
- `response` - User's response (1=yes, 0/2=no)
- `attempt` - Response attempt counter

#### `helper` (classes/helper.php)
**Utility class for page setup, file management, and formatting.**

Key Methods:
- `page($url, $pagetitle, $pageheading, $context, $pagelayout)` - Sets up Moodle page
- `getFileManagerOptions($context, $maxfiles)` - Returns file manager options
- `getEditorOptions($context)` - Returns rich text editor options
- `getTextFieldOptions($context)` - Returns text field options
- `loadJQueryJS()` - Loads jQuery and DataTables libraries

#### External Web Services

**tool_disclaimer_ws** (classes/external/disclaimer_ws.php)
- `delete($id)` - Deletes disclaimer and associated records
- `get_disclaimer($id)` - Retrieves disclaimer data

**roles_ws** (classes/external/roles_ws.php)
- `get_role($disclaimerid)` - Retrieves roles for a disclaimer

**user_response_ws** (classes/external/user_response_ws.php)
- `response($disclaimerid, $userid, $response, $objectid, $attempt)` - Logs user response

---

## User Interface

### Admin Pages

#### Disclaimer Management (index.php)
**URL:** `/admin/tool/disclaimer/index.php`

**Features:**
- Displays table of all disclaimers
- Search/filter disclaimers by name
- Action buttons for edit, delete per disclaimer
- Link to create new disclaimer
- Paginated display (20 per page)

**Capabilities Required:** `tool/disclaimer:view`

#### Edit/Create Disclaimer (edit_disclaimer.php)
**URL:** `/admin/tool/disclaimer/edit_disclaimer.php[?id=<ID>]`

**Features:**
- Form for creating new or editing existing disclaimers
- Fields:
  - Name (required)
  - Context (course, early_alert)
  - Front page only toggle
  - Subject line
  - Rich text message editor
  - Publication status
  - Date range for publishing
  - Redirect URL for declined responses
  - Category/course selectors
  - Role selectors
- Save/Cancel buttons

**Capabilities Required:** `tool/disclaimer:edit`

#### User Responses Management (user_responses.php)
**URL:** `/admin/tool/disclaimer/user_responses.php`

**Features:**
- Displays table of all user responses to disclaimers
- Search/filter users by:
  - User ID
  - First name
  - Last name
  - Disclaimer context (course, early_alert)
  - Response status (accepted, declined)
- View user response details including:
  - User information (ID, name, email)
  - Disclaimer name and context
  - Response status (accepted/declined)
  - Attempt number
  - Response date/time
- Reset individual user responses
- Paginated display (20 per page)
- Sortable columns

**Capabilities Required:** `tool/disclaimer:edit`

**Use Case:**  
This page allows administrators to manage user responses to disclaimers. It's particularly useful when a user mistakenly declines a disclaimer and needs to see it again. By resetting a user's response, the disclaimer will be shown to them again on their next relevant action (e.g., viewing a course or triggering an early alert).

#### Reset User Response (reset_response.php)
**URL:** `/admin/tool/disclaimer/reset_response.php?id=<LOG_ID>`

**Features:**
- Confirmation page for resetting a user's disclaimer response
- Displays user details and disclaimer information
- Requires confirmation before deleting the response log
- Redirects back to user responses page after reset
- Shows success notification

**Capabilities Required:** `tool/disclaimer:edit`

---

## Frontend Behavior

### Disclaimer Display Flow

1. **Event Trigger**: When a user views a course or triggers an early alert event
2. **Event Handler** (eventslib.php):
   - Check if user is site admin (exempted)
   - Retrieve applicable disclaimers
   - Check user's roles
   - Query disclaimer logs for prior responses
   - If no prior acceptance, load JavaScript modal
3. **Modal Display** (disclaimer_alert.js):
   - Create Bootstrap modal with disclaimer subject and message
   - Display acceptance/decline buttons
   - Prevent modal dismissal outside buttons
   - Wait 2 seconds before activating buttons
4. **User Response**:
   - **Accept (Yes)**: Log response, continue on page
   - **Decline (No)**: Log response, redirect to specified URL
   - **Cancel**: Log response, redirect to specified URL
5. **Response Logging**: Store response in `tool_disclaimer_log` table with timestamp

### Modal Templates

**disclaimer_modal.mustache**: Renders the disclaimer message/content

**modal_buttons.mustache**: Renders Yes/No/Cancel buttons

**disclaimer_alert.js**: Controls modal behavior via AMD module

---

## Event Handling

### Supported Events (eventslib.php)

#### Course Viewed Event
**Event:** `core\event\course_viewed`

**Handler:** `tool_disclaimer_course_viewed()`

Logic:
1. Bypass for site admins
2. Get user's roles in course
3. Find published course disclaimers
4. Check role restrictions (if set)
5. Check publication date range
6. Check prior user responses
7. If new or declined previously, show modal

#### Early Alert Event
**Handler:** `tool_disclaimer_early_alert()` (in eventslib.php)

Similar logic for early alert context disclaimers

---

## Capabilities & Permissions

Four capabilities are defined (db/access.php):

| Capability | Description | Default Role |
|------------|-------------|--------------|
| `tool/disclaimer:view` | View disclaimer records | Manager |
| `tool/disclaimer:edit` | Edit disclaimer records | Manager |
| `tool/disclaimer:delete` | Delete disclaimer records | Manager |
| `tool/disclaimer:reports` | View disclaimer reports | Manager |

---

## Admin Settings

The plugin is registered in Moodle's admin tree (settings.php):
- **Location:** Site Administration > Courses > Disclaimers
- **Subpages:**
  - **Disclaimers** - Main disclaimer management page  
    URL: `/admin/tool/disclaimer/index.php`  
    Capability: `tool/disclaimer:view`
  - **User Responses** - User response management page  
    URL: `/admin/tool/disclaimer/user_responses.php`  
    Capability: `tool/disclaimer:edit`

---

## Web Services

Accessible via Moodle's AJAX interface (db/services.php):

### tool_disclaimer_response
**Purpose:** Log user response to disclaimer
**Type:** write
**Function:** `tool_disclaimer_user_response_ws::response()`
**Parameters:**
- disclaimerid (int)
- userid (int)
- response (int: 1=yes, 0=no, 2=cancel)
- objectid (int)
- attempt (int)

### tool_disclaimer_get_disclaimer
**Purpose:** Fetch disclaimer details
**Type:** read
**Function:** `tool_disclaimer_ws::get_disclaimer()`
**Parameters:**
- id (int): Disclaimer ID

### tool_disclaimer_get_role
**Purpose:** Retrieve roles for a disclaimer
**Type:** read
**Function:** `tool_disclaimer_roles_ws::get_role()`
**Parameters:**
- disclaimerid (int)

### tool_disclaimer_delete
**Purpose:** Delete disclaimer and associated data
**Type:** write
**Function:** `tool_disclaimer_ws::delete()`
**Parameters:**
- id (int): Disclaimer ID
**Deletes:**
- Disclaimer record
- Associated roles
- Associated user logs

---

## File Management

### Assets Storage
**Component:** tool_disclaimer  
**File Area:** assets  
**Item ID:** Disclaimer ID

Files uploaded as part of disclaimer messages are stored in Moodle's file storage system.

**File Retrieval:** Via `tool_disclaimer_pluginfile()` (lib.php)
- Validates context level (system only)
- Validates file area
- Serves stored files with 24-hour cache

---

## JavaScript Modules (AMD)

### disclaimer_alert.js
**Purpose:** Display and manage disclaimer modal

**init(results)**
- results: Object containing disclaimerid, userid, objectid, redirectto
- Fetches disclaimer data
- Creates Bootstrap modal
- Attaches button event listeners
- Handles user response via AJAX

**Helper Functions:**
- `fetchData()` - AJAX call to retrieve disclaimer
- `respond()` - AJAX call to log user response
- `hideModal()` - Clean up and remove modal DOM

### disclaimers.js
**Purpose:** Manage disclaimer table interactions

**init()**
- Initializes disclaimer management table
- Attaches event handlers to action buttons
- Handles delete confirmations

### roles.js
**Purpose:** Manage role selection

**init()**
- Initializes role selector interface
- Handles multi-select role assignments

### user_responses.js
**Purpose:** Manage user responses table interactions

**init()**
- Initializes user responses management table
- Placeholder for interactive functionality on user responses page

---

## Styled Components

### CSS (css/general.css)
- Modal styling
- Table styling
- Button styling
- Form styling

---

## Language Strings (lang/en/tool_disclaimer.php)

Key language strings available for multi-language support:

- **UI Elements:** cancel, delete, edit, yes, no, options, filter, reset, new, actions, all
- **Disclaimers:** disclaimers, edit_disclaimer, name, subject, message, disclaimer_name
- **Settings:** context, published, front_page_only, use_published_date
- **Dates:** publish_from, publish_until, timecreated
- **Actions:** redirect_to_url, reset_response
- **User Responses:** user_responses, userid, accepted, declined, response_status, attempt, response_reset_success, reset_response_confirm
- **Roles:** All Moodle role names (student, teacher, manager, etc.)
- **Help Text:** Various help strings with _help suffix
- **Capabilities:** Localized capability descriptions

---

## Installation & Setup

### Installation Steps
1. Extract plugin to `/admin/tool/disclaimer/`
2. Visit Site Administration > Notifications
3. Moodle auto-detects and installs the plugin
4. Database tables are created automatically

### Initial Configuration
1. Navigate to Site Administration > Courses > Disclaimers
2. Click "New" to create first disclaimer
3. Fill in required fields
4. Select target roles and contexts
5. Publish the disclaimer
6. Users will see the disclaimer on next course view

---

## Data Flow Diagram

```
User Views Course
    ↓
course_viewed event triggered
    ↓
eventslib::tool_disclaimer_course_viewed()
    ↓
Query disclaimer records where context='course' AND published=1
    ↓
Check user's roles in course
    ↓
Does disclaimer have role restrictions?
    ├─ YES: Is user's role in restriction list?
    │        ├─ NO: Skip disclaimer
    │        └─ YES: Continue
    └─ NO: Continue
    ↓
Check user_response_log for prior response
    ├─ User accepted: Skip
    └─ No response or declined: Continue
    ↓
Load disclaimer_alert.js AMD module
    ↓
Display Bootstrap modal
    ↓
User interacts with modal
    ├─ Clicks Yes: response=1, continue
    ├─ Clicks No: response=0/2, redirect
    └─ Clicks Cancel: response=0/2, redirect
    ↓
Log response to tool_disclaimer_log table
```

---

## Common Use Cases

### Use Case 1: Course Entry Disclaimer
**Scenario:** All students must accept terms before accessing any course

**Setup:**
1. Create disclaimer with context='course'
2. Set frontpageonly=0
3. Select 'student' role
4. Select all courses (or specific ones)
5. Publish it

**Result:** Students see modal on first course access

### Use Case 2: Site-Wide Policy
**Scenario:** All users accept system policy when logging in

**Setup:**
1. Create disclaimer with context='course'
2. Set frontpageonly=1
3. Set published=1
4. Leave roles empty (applies to all)
5. Publish it

**Result:** Users see modal on front page

### Use Case 3: Time-Limited Notice
**Scenario:** Display message only during specific date range

**Setup:**
1. Create disclaimer
2. Set usepublisheddate=1
3. Set publishedstart and publishedend dates
4. Publish it

**Result:** Disclaimer only shows within date range

### Use Case 4: Role-Specific Consent
**Scenario:** Only teachers need to accept additional responsibilities

**Setup:**
1. Create disclaimer with context='course'
2. Select 'teacher' and 'editingteacher' roles
3. Publish it

**Result:** Only users with those roles see the disclaimer

---

## API Reference

### Creating a New Disclaimer Programmatically

```php
use tool_disclaimer\disclaimer;

$data = new stdClass();
$data->name = 'My Disclaimer';
$data->subject = 'Important Notice';
$data->message = 'Please read carefully...';
$data->context = 'course';
$data->published = 1;
$data->frontpageonly = 0;
$data->redirectto = '/my';
$data->usermodified = $USER->id;

$disclaimer = new disclaimer();
$id = $disclaimer->insert_record($data);
```

### Retrieving a Disclaimer

```php
use tool_disclaimer\disclaimer;

$disclaimer = new disclaimer($id);
$roles = $disclaimer->get_roles();
$is_frontpage_only = $disclaimer->get_frontpageonly();
```

### Getting All Disclaimers

```php
use tool_disclaimer\disclaimers;

$all_disclaimers = new disclaimers();
$records = $all_disclaimers->get_records();
$select_array = $all_disclaimers->get_select_array();
```

### Logging a User Response

```php
use tool_disclaimer\disclaimer_log;

$log = new disclaimer_log();
$data = new stdClass();
$data->disclaimerid = $disclaimer_id;
$data->userid = $user_id;
$data->objectid = $course_id;
$data->response = 1; // 1=accepted, 0 or 2=declined
$data->attempt = 1;
$data->usermodified = $USER->id;

$log->insert_record($data);
```

### Checking User Response

```php
global $DB;

$response = $DB->get_record(
    'tool_disclaimer_log',
    [
        'disclaimerid' => $disclaimer_id,
        'userid' => $user_id,
        'objectid' => $object_id
    ]
);

if ($response && $response->response == 1) {
    // User has accepted
}
```

---

## Troubleshooting

### Modal Not Appearing
- Check if disclaimer is published
- Verify current date is within publishedstart/publishedend if using date ranges
- Confirm user's role is in role restrictions (if set)
- Check browser console for JavaScript errors
- Verify `tool_disclaimer/disclaimer_alert` AMD module is loaded

### User Response Not Logged
- Check database permissions
- Verify `tool_disclaimer_log` table exists
- Check web service permissions in services.php
- Verify AJAX calls complete (browser dev tools Network tab)

### Redirect Not Working
- Verify redirect URL is properly formatted
- Check for leading slash in URL
- Verify URL is accessible to user

### Multiple Disclaimers Not Working
- Only one published disclaimer per context is typically active
- Check `published` field - only one should be 1 per context
- Use date ranges to schedule multiple disclaimers

---

## Performance Considerations

- **Indexes:** `tool_disclaimer_log.disclaimerid` is indexed for fast lookups
- **Caching:** File assets cached for 24 hours
- **Modal Loads:** Only loads on event trigger, not on every page load
- **Database Queries:** Minimal queries - one per event trigger to check disclaimers

---

## Security Notes

1. **Capability Checks:** All CRUD operations check user capabilities
2. **Context Validation:** File serving validates system context
3. **CSRF Protection:** Moodle forms include CSRF tokens
4. **SQL Injection:** Uses Moodle's parameterized queries
5. **XSS Protection:** Messages are sanitized and escaped
6. **Admin-Only:** Disclaimer management is admin tool (system context only)

---

## Future Enhancements

Potential areas for expansion:
- Multiple disclaimers per context with scheduling
- Email notifications for administrator when users decline
- Reporting dashboard with response statistics
- Bulk operations (delete all logs, etc.)
- Export user responses to CSV
- Disclaimer versioning/audit trail
- Template library for common disclaimers
- A/B testing of disclaimer variations

---

## Support & Maintenance

**Version:** 2025061000  
**Last Updated:** January 4, 2025  
**Moodle Maturity:** Stable  
**Moodle Requirements:** 4.4+

For issues and contributions, contact the plugin maintainer or the Moodle community.
