<?php

$string['acknowledgement'] = 'Acknowledgement (all authenticated users)';
$string['acknowledgement_context_help'] = 'An acknowledgement disclaimer is shown to every authenticated user on every page until they click OK. It is not course-scoped and requires no role or course selection.';
$string['cancel'] = 'Cancel';
$string['change_response'] = 'Change Response';
$string['context'] = 'Context';
$string['contextpath'] = 'Context path';
$string['could_not_delete_disclaimer'] = 'Error: Could not delete disclaimer';
$string['course'] = 'Course';
$string['delete'] = 'Delete';
$string['delete_disclaimer_help'] = 'Are you sure you want to delete this disclaimer? This will remove the disclaimer, roles and user responses. This action cannot be undone.';
$string['disclaimer_exists'] = 'A published disclaimer already exists for this context. You can only have one published disclaimer per context.';
$string['disclaimers'] = 'Disclaimers';
$string['early_alert'] = 'Early Alert';
$string['edit'] = 'Edit';
$string['edit_disclaimer'] = 'Edit Disclaimer';
$string['field_required'] = 'This field is required';
$string['filter'] = 'Filter';
$string['front_page_only'] = 'Front page only?';
$string['front_page_only_help'] = 'Should this disclaimer be shown on the front page only (/my or home)?';
$string['message'] = 'Message';
$string['name'] = 'name';
$string['new'] = 'New';
$string['no'] = 'No';
$string['not_your_disclaimer'] = 'You are trying to access a disclaimer that does not belong to you.';
$string['ok'] = 'I Acknowledge';
$string['options'] = 'Options';
$string['original_message'] = 'Original message';
$string['pluginname'] = 'Disclaimer';
$string['published'] = 'Published';
$string['publish_from'] = 'Publish from';
$string['publish_until'] = 'Publish until';
$string['reset'] = 'Reset';
$string['redirect_to_url'] = 'Redirect to URL';
$string['redirect_to_url_help'] = 'If cancelled or declined, the user will be redirected to this URL. Leave blank to remain on the page.';
$string['subject'] = 'Subject';
$string['system'] = 'System';
$string['update_published_status'] = 'Update published disclaimer status';
$string['use_published_date'] = 'Use date range to publish?';
$string['yes'] = 'Yes';

// User responses page
$string['accepted'] = 'Accepted';
$string['actions'] = 'Actions';
$string['all'] = 'All';
$string['attempt'] = 'Attempt';
$string['declined'] = 'Declined';
$string['disclaimer_name'] = 'Disclaimer Name';
$string['response_reset_success'] = 'User response has been successfully reset';
$string['response_status'] = 'Response Status';
$string['reset_response'] = 'Reset Response';
$string['reset_response_confirm'] = 'Are you sure you want to reset the disclaimer response for user {$a->username} ({$a->email}) for disclaimer "{$a->disclaimer}" in context "{$a->context}"? This will allow the user to see the disclaimer again.';
$string['timecreated'] = 'Response Date';
$string['userid'] = 'User ID';
$string['userid_help'] = 'Enter the numeric user ID to search for a specific user.';
$string['user_responses'] = 'User Responses';


// Access privileges
$string['disclaimer:delete'] = 'Delete disclaimer record';
$string['disclaimer:edit'] = 'Edit dsiclaimer record';
$string['disclaimer:reports'] = 'View disclaimer reports';
$string['disclaimer:view'] = 'View disclaimer records';

// Roles
$string['role_authenticated_user'] = 'Authenticated user';
$string['role_authenticated_user_home'] = 'Authenticated user on site home';
$string['role_course_creator'] = 'Course Creator';
$string['role_guest'] = 'Guest';
$string['role_manager'] = 'Manager';
$string['role_non-editing_teacher'] = 'Non-editing teacher';
$string['role_student'] = 'Student';
$string['role_teacher'] = 'Teacher';

/**
 * Privacy
 */
$string['privacy:metadata:tool_disclaimer'] = 'The Disclaimer tool stores information about disclaimers, including which user last modified each disclaimer.';
$string['privacy:metadata:tool_disclaimer:usermodified'] = 'The ID of the user who last modified the disclaimer.';
$string['privacy:metadata:tool_disclaimer_role'] = 'The Disclaimer tool stores role assignments for disclaimers, including which user last modified each assignment.';
$string['privacy:metadata:tool_disclaimer_role:usermodified'] = 'The ID of the user who last modified the role assignment.';
$string['privacy:metadata:tool_disclaimer_log'] = 'The Disclaimer tool stores user responses to disclaimers, including the user who responded and who last modified the log entry.';
$string['privacy:metadata:tool_disclaimer_log:userid'] = 'The ID of the user who responded to the disclaimer.';
$string['privacy:metadata:tool_disclaimer_log:usermodified'] = 'The ID of the user who last modified the log entry.';

// Privacy export paths
$string['privacy:disclaimers'] = 'Disclaimers';
$string['privacy:roles'] = 'Roles';
$string['privacy:userresponses'] = 'User Responses';
