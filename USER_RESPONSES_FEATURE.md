# User Responses Management Feature

## Overview

A new administrative interface has been added to the Disclaimer Tool plugin that allows administrators to search for and manage user responses to disclaimers. This feature is essential for resetting user responses when users mistakenly decline disclaimers.

## Files Created

### 1. Main Page: user_responses.php
- **Location:** `/admin/tool/disclaimer/user_responses.php`
- **Purpose:** Main page for searching and viewing user disclaimer responses
- **Capability Required:** `tool/disclaimer:edit`
- **Features:**
  - Search by User ID, First Name, Last Name
  - Filter by Disclaimer Context (course, early_alert)
  - Filter by Response Status (accepted, declined)
  - Paginated table display (20 per page)
  - Sortable columns
  - Reset action for each response

### 2. Reset Page: reset_response.php
- **Location:** `/admin/tool/disclaimer/reset_response.php`
- **Purpose:** Confirmation page for resetting a user's disclaimer response
- **Capability Required:** `tool/disclaimer:edit`

### 3. Filter Form: user_response_filter_form.php
- **Location:** `/admin/tool/disclaimer/classes/forms/user_response_filter_form.php`
- **Purpose:** Moodle form for filtering user responses

### 4. Table Class: user_response_table.php
- **Location:** `/admin/tool/disclaimer/classes/tables/user_response_table.php`
- **Purpose:** Table_sql implementation for displaying user responses

### 5. Mustache Template: user_response_action_buttons.mustache
- **Location:** `/admin/tool/disclaimer/templates/user_response_action_buttons.mustache`
- **Purpose:** Renders the reset action button for each user response

### 6. JavaScript Module: user_responses.js
- **Location:** `/admin/tool/disclaimer/amd/src/user_responses.js`
- **Purpose:** AMD module for user responses page interactivity

### 7. Language Strings
- **Location:** `/admin/tool/disclaimer/lang/en/tool_disclaimer.php`
- **New Strings Added:** accepted, actions, all, attempt, declined, disclaimer_name, response_reset_success, response_status, reset_response, reset_response_confirm, timecreated, userid, user_responses

### 8. Admin Navigation
- **Location:** `/admin/tool/disclaimer/settings.php`
- **Changes:** Created admin category with two pages: Disclaimers and User Responses

## Navigation Path

**Site Administration > Courses > Disclaimers > User Responses**

## Access Control

- Only users with the `tool/disclaimer:edit` capability can access this feature
- Typically limited to Site Administrators and Managers
- Teachers and Students cannot access this page

## Use Cases

### Use Case 1: User Mistakenly Declined Course Disclaimer
**Scenario:** A student accidentally clicked "No" on a course disclaimer and can no longer access their courses.

**Solution:**
1. Administrator navigates to User Responses page
2. Searches by student's name or user ID
3. Filters by context = "Course" and response = "Declined"
4. Clicks "Reset" button for the student's response
5. Confirms the reset action
6. Student will see the disclaimer again on their next course access

### Use Case 2: Resetting Early Alert Disclaimers
**Scenario:** A teacher declined an early alert disclaimer during testing and needs to see it again.

**Solution:**
1. Administrator filters by context = "Early Alert" 
2. Searches for the teacher's name
3. Resets the response
4. Teacher will see the disclaimer on their next early alert trigger

## Database Operations

### Reset Operation
- Deletes the record from `tool_disclaimer_log`
- Next time the user triggers the relevant event, the disclaimer will show again
- The attempt counter will reset

## Security Considerations

1. **Capability Check:** All pages verify `tool/disclaimer:edit` capability
2. **Session Key:** Reset operation requires valid session key (CSRF protection)
3. **Confirmation:** Two-step process prevents accidental deletions
4. **SQL Injection Protection:** Uses Moodle's parameterized queries
5. **Context Validation:** System context only

## Created: February 12, 2026
