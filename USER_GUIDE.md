# Moodle Disclaimer Tool - User Guide

## Table of Contents
1. [Overview](#overview)
2. [For End Users](#for-end-users)
3. [For Administrators](#for-administrators)
4. [For Developers](#for-developers)

## Overview

The Moodle Disclaimer Tool is a comprehensive plugin that allows administrators to create, manage, and display disclaimers to users based on various criteria such as user roles, contexts, and time periods. The plugin provides a modal-based disclaimer system that tracks user responses and ensures compliance.

**Key Features:**
- Context-based disclaimers (course, early alert, system-wide)
- Role-based targeting
- Time-based publishing with date ranges
- User response tracking and logging
- Modal popup interface
- Customizable redirect URLs
- Front-page only options

---

## For End Users

### What You'll Experience

#### Disclaimer Popups
When accessing Moodle, you may encounter disclaimer popups that require your acknowledgment. These disclaimers appear as modal windows that overlay the current page.

#### Disclaimer Interaction
1. **Reading the Disclaimer**: A modal window will appear with the disclaimer text
2. **Response Options**: You'll typically see buttons to Accept, Decline, or Cancel
3. **Required Action**: You must respond to proceed with your Moodle activities
4. **Redirect Behavior**: If you decline or cancel, you may be redirected to a specific URL or remain on the current page

#### When Disclaimers Appear
Disclaimers may appear:
- When logging into Moodle
- When accessing specific courses
- When navigating to the front page (/my or home)
- Based on your user role (student, teacher, etc.)
- During specific time periods set by administrators

#### Your Response History
- Your responses are logged and tracked
- You may have the option to change your response later
- Some disclaimers may require periodic re-acknowledgment

### Changing Your Disclaimer Response

If you need to modify a previous disclaimer response:
1. Navigate to your profile or the designated disclaimer management area
2. Look for "My Disclaimers" or similar option
3. Find the relevant disclaimer
4. Use the "Change Response" option if available

---

## For Administrators

### Accessing the Plugin

1. Navigate to **Site Administration** → **Courses** → **Disclaimers**
2. Ensure you have the appropriate capabilities:
   - `tool/disclaimer:view` - View disclaimer records
   - `tool/disclaimer:edit` - Edit disclaimer records
   - `tool/disclaimer:delete` - Delete disclaimer records
   - `tool/disclaimer:reports` - View disclaimer reports

### Creating a New Disclaimer

1. **Access the Disclaimers Page**: Go to Site Administration → Courses → Disclaimers
2. **Click "New"** to create a new disclaimer
3. **Fill in Required Fields**:
   - **Name**: Descriptive name for the disclaimer
   - **Context**: Choose from Course, Early Alert, or System
   - **Subject**: Title/header for the disclaimer
   - **Message**: The main disclaimer content (supports HTML formatting)

4. **Configure Display Options**:
   - **Front Page Only**: Check if disclaimer should only appear on the front page
   - **Context Path**: Specify the specific context path if needed
   - **Redirect URL**: Set where users go if they decline/cancel

5. **Set Publishing Options**:
   - **Published**: Enable/disable the disclaimer
   - **Use Date Range**: Optionally set start and end dates for publication
   - **Publish From/Until**: Specific dates when disclaimer is active

6. **Assign Roles**: Select which user roles should see this disclaimer

### Managing Existing Disclaimers

#### Viewing Disclaimers
- The main page shows a table of all disclaimers
- Use the search/filter options to find specific disclaimers
- View details including name, context, published status, and dates

#### Editing Disclaimers
1. Click the "Edit" button next to any disclaimer
2. Modify any fields as needed
3. Save changes

#### Publishing/Unpublishing
- Toggle the "Published" status
- Set date ranges for automatic publishing/unpublishing
- Note: Only one published disclaimer per context is allowed

#### Deleting Disclaimers
1. Click the "Delete" button
2. Confirm deletion (this removes the disclaimer, associated roles, and user responses)
3. **Warning**: This action cannot be undone

### Role Assignment

When creating or editing a disclaimer, you can assign it to specific roles:
- **Authenticated User**: All logged-in users
- **Authenticated User on Site Home**: Users on the home page only
- **Teacher**: Users with teacher roles within a course
- **Guest**: Guest users
- **Custom Roles**: Any other roles defined in your Moodle instance

### Monitoring and Reports

#### User Response Tracking
- The system automatically logs all user interactions
- Track who has accepted, declined, or not responded
- View timestamps for all responses

#### Reports (Coming Soon)
- Comprehensive reporting interface for disclaimer analytics
- User response summaries
- Compliance tracking

### Best Practices

1. **Clear Communication**: Write disclaimers in clear, understandable language
2. **Appropriate Timing**: Set reasonable date ranges for disclaimer publication
3. **Role Targeting**: Only show disclaimers to relevant user roles
4. **Regular Review**: Periodically review and update disclaimer content
5. **Testing**: Test disclaimers with different user roles before publishing

---

## For Developers

### Plugin Architecture

The Disclaimer Tool follows Moodle's standard plugin architecture with the following key components:

#### Directory Structure
```
admin/tool/disclaimer/
├── classes/
│   ├── disclaimer.php          # Main disclaimer class
│   ├── disclaimers.php         # Collection management
│   ├── disclaimer_log.php      # Individual log entry
│   ├── disclaimer_logs.php     # Log collection management
│   ├── helper.php              # Utility functions
│   ├── crud.php                # Base CRUD operations
│   ├── popup_notification.php  # Notification handling
│   ├── external/               # Web services
│   ├── forms/                  # Moodle forms
│   ├── tables/                 # Data tables
│   └── task/                   # Scheduled tasks
├── amd/                        # AMD JavaScript modules
├── templates/                  # Mustache templates
├── lang/                       # Language strings
├── db/                         # Database definitions
└── css/                        # Stylesheets
```

### Core Classes

#### `tool_disclaimer\disclaimer`
Main entity class extending the CRUD base class. Handles individual disclaimer records.

**Key Properties:**
- `id`, `name`, `context`, `contextpath`
- `frontpageonly`, `subject`, `message`
- `published`, `publishedstart`, `publishedend`
- `redirectto`, `usepublisheddate`

#### `tool_disclaimer\disclaimers`
Collection class for managing multiple disclaimer instances.

#### `tool_disclaimer\disclaimer_log`
Handles individual user response logging.

#### `tool_disclaimer\disclaimer_logs`
Manages collections of user response logs.

### Database Schema

#### Main Tables
- `tool_disclaimer_disclaimer` - Disclaimer definitions
- `tool_disclaimer_disclaimer_log` - User response logs
- `tool_disclaimer_disclaimer_roles` - Role assignments

### Web Services

The plugin provides web services for AJAX interactions:

#### `tool_disclaimer\external\disclaimer_ws`
- Disclaimer data management
- CRUD operations via web services

#### `tool_disclaimer\external\roles_ws`
- Role assignment management
- Role-based filtering

#### `tool_disclaimer\external\user_response_ws`
- User response handling
- Response logging and updates

### JavaScript Architecture

#### AMD Modules (`amd/src/`)

**`disclaimer_alert.js`**
- Handles disclaimer modal display
- Manages user interactions
- AJAX communication for responses

**`disclaimers.js`**
- Admin interface functionality
- Table management and filtering

**`roles.js`**
- Role selection and management
- Dynamic role assignment interface

### Form Classes

#### `edit_disclaimer_form`
Main form for creating/editing disclaimers with:
- Text inputs for name, subject
- HTML editor for message content
- Date selectors for publishing ranges
- Context and role selection

#### `disclaimer_filter_form`
Search and filtering interface for the admin table view.

### Templates

#### `disclaimer_modal.mustache`
Modal template for displaying disclaimers to users.

#### `disclaimer_table_action_buttons.mustache`
Action buttons for the admin table interface.

### Scheduled Tasks

#### `update_published_status`
Automatically updates disclaimer publication status based on date ranges.

### Events and Logging

The plugin integrates with Moodle's event system for:
- User response tracking
- Audit logging
- Performance monitoring

### Capabilities and Permissions

Defined in `db/access.php`:
- `tool/disclaimer:view` - View disclaimers
- `tool/disclaimer:edit` - Create/edit disclaimers
- `tool/disclaimer:delete` - Delete disclaimers
- `tool/disclaimer:reports` - Access reports

### Extending the Plugin

#### Adding New Contexts
1. Update the context options in `edit_disclaimer_form.php`
2. Add corresponding language strings
3. Implement context-specific logic in the helper class

#### Custom Response Types
1. Extend the database schema for new response types
2. Update the web services to handle new response options
3. Modify the modal template and JavaScript accordingly

#### Integration with Other Plugins
The plugin provides hooks and events that can be consumed by other plugins:
- Response events for external processing
- Context-aware disclaimer triggering
- Custom role integration

### Development Setup

1. **Install in Development Environment**:
   ```bash
   git clone <repository> admin/tool/disclaimer
   ```

2. **Enable Developer Mode** in Moodle for debugging

3. **Database Updates**: Run upgrade after schema changes

4. **JavaScript Development**:
   ```bash
   # Build AMD modules
   grunt amd
   ```

5. **Language String Management**: Update `lang/en/tool_disclaimer.php`

### Testing

#### Unit Tests
- Create PHPUnit tests for core classes
- Test CRUD operations and business logic

#### Behat Tests
- End-to-end testing for user interactions
- Admin interface testing

#### Manual Testing Checklist
- [ ] Disclaimer creation and editing
- [ ] Role assignment functionality
- [ ] Modal display and user interactions
- [ ] Date-based publishing
- [ ] User response logging
- [ ] Permission checking

### Performance Considerations

- **Database Queries**: Optimize table joins for large user bases
- **Caching**: Implement appropriate caching for frequently accessed disclaimers
- **JavaScript**: Minimize DOM manipulation in modal interactions
- **File Loading**: Lazy load CSS and JavaScript when needed

### Security Considerations

- **Input Validation**: All form inputs are properly validated
- **Capability Checks**: Proper permission checking throughout
- **XSS Prevention**: HTML content is properly escaped
- **CSRF Protection**: Forms include Moodle's CSRF tokens

### API Reference

#### Key Methods

**Disclaimer Class:**
```php
$disclaimer = new \tool_disclaimer\disclaimer();
$disclaimer->create($data);           // Create new disclaimer
$disclaimer->read($id);               // Load disclaimer by ID
$disclaimer->update($data);           // Update disclaimer
$disclaimer->delete($id);             // Delete disclaimer
```

**Helper Functions:**
```php
\tool_disclaimer\helper::get_active_disclaimers($context, $userid);
\tool_disclaimer\helper::log_user_response($disclaimerid, $userid, $response);
\tool_disclaimer\helper::check_user_permissions($userid, $capability);
```

This comprehensive guide should help both administrators effectively manage disclaimers and developers understand the plugin's architecture for customization and extension.
