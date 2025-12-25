<?php
return [
    // --- NAVIGATION ---
    'nav_dashboard' => 'Dashboard',
    'nav_admin' => 'Admin',
    'nav_logout' => 'Log out',
    'nav_login' => 'Log in',
    'nav_register' => 'Register',
    'nav_brand' => 'Laundry',
    'logged_in_as' => 'Logged in as',

    // --- DAYS OF WEEK ---
    'day_0' => 'Sun',
    'day_1' => 'Mon',
    'day_2' => 'Tue',
    'day_3' => 'Wed',
    'day_4' => 'Thu',
    'day_5' => 'Fri',
    'day_6' => 'Sat',

    // --- DASHBOARD ---
    'dash_title' => 'Bookings for',
    'status_maintenance' => 'MAINTENANCE',
    'status_free' => 'Free',
    'status_pending' => 'Pending...',
    'status_taken' => 'Taken',
    'status_mine' => 'Mine',

    // --- ADMIN PANEL ---
    'admin_title' => 'Admin Panel',
    'admin_subtitle' => 'Admin Area',
    'admin_sect_settings' => 'General Settings',
    'lbl_max_hours' => 'Max Weekly Hours per User',
    'note_max_hours' => 'Max bookable hours per week per user.',
    'lbl_registration_code' => 'Registration Secret Code',
    'note_registration_code' => 'The code students must enter to register.',
    'err_invalid_registration_code' => 'The Residence Code is incorrect. You can find it posted in the laundry room.',
    'btn_save' => 'Save Changes',
    'note_immediate_effect' => '*This change affects all new bookings immediately.',
    'delete_user' => 'Delete',
    'reset_user_pwd' => 'Reset Password',

    'admin_sect_machines' => 'Machines Status',
    'th_name' => 'Name',
    'th_type' => 'Type',
    'th_status' => 'Current Status',
    'th_action' => 'Action',
    'st_active' => 'Active',
    'st_maint' => 'Maintenance',
    'btn_set_maint' => 'Set Maintenance',
    'btn_set_active' => 'Reactivate',

    'admin_sect_users' => 'Users List',
    'th_username' => 'Username',
    'th_email' => 'Email',
    'th_apt' => 'Apartment',

    // Admin Messages (Backend)
    'msg_config_updated' => 'Weekly hours limit updated to: <strong>%d</strong>',
    'msg_invalid_num' => 'Please enter a valid number.',
    'msg_machine_updated' => 'Machine status updated to: <strong>%s</strong>',
    'msg_pass_reset' => 'Password reset. New password:',
    'msg_user_deleted' => 'User anonymized and future bookings deleted.',

    // Admin Modals
    'modal_reset_title' => 'Reset Password',
    'modal_reset_body' => 'Are you sure you want to reset the password for user <b>%s</b>?<br>The new password will be shown on screen.',
    'modal_delete_title' => 'Delete User',
    'modal_delete_body' => 'WARNING: You are about to anonymize user <b>%s</b>.<br>This action is irreversible.',
    'btn_reset_confirm' => 'Reset Password',
    'btn_delete_confirm' => 'Delete Permanently',

    // --- COMMON & AUTH ---
    'email_label' => 'Institutional Email',
    'password_label' => 'Password',
    'password_confirm_label' => 'Confirm Password',
    'apt_label' => 'Apartment',
    'username_label' => 'Username',
    'login_title' => 'Sign In',
    'btn_enter' => 'Enter',
    'link_no_account' => 'No account yet?',
    'link_create_account' => 'Create account',
    'error_creds' => 'Invalid credentials.',
    'register_title' => 'Create Account',
    'btn_register' => 'Sign Up',
    'residence_code_info' => 'The code is posted on the laundry notice board.',
    'link_have_account' => 'Already have an account?',
    'link_login_here' => 'Log in here',
    'lbl_username_gen' => 'Username (Generated)',
    'lbl_username_desc' => 'will be your display name',

    // --- ERRORS ---
    'err_apt_range' => 'Apartment number must be between 1 and 23.',
    'err_email_domain' => 'You must use an institutional email (@studio.unibo.it or @unibo.it)',
    'err_username_empty' => 'Username was not generated correctly.',
    'err_username_format' => 'Invalid username format (e.g. name12-89).',
    'err_pass_match' => 'Passwords do not match.',
    'err_pass_short' => 'Password must be at least 8 characters long.',
    'err_user_taken' => 'Email already registered or Username not available.',
    'err_db_generic' => 'Generic database error.',
    'err_db_conn' => 'Database connection error: ',
    'err_method' => 'Invalid method',
    'err_login_required' => 'Login required',
    'err_current_week_only' => 'You can only book for the current week!',
    'err_past_date' => 'You cannot book in the past!',
    'err_future_date' => 'You cannot book in the future!',
    'err_invalid_date' => 'Invalid date',
    'err_machine_maintenance' => 'This machine is currently under maintenance.',
    'err_limit_reached' => 'You reached the limit of %d weekly hours!',
    'err_slot_occupied' => 'Slot just taken by another user!',
    'err_tech_lock' => 'Technical Error (Lock)',
    'err_unauthorized' => 'Unauthorized',
    'err_booking_not_found' => 'Booking not found or not yours.',
    'err_missing_id' => 'Missing booking ID',
    'err_booking_expired' => 'Booking not found or expired.',
    'err_tech_confirm' => 'Technical error during confirmation.',
    'err_missing_params' => 'Missing parameters',

    // --- FOOTER & PRIVACY ---
    'footer_desc' => 'Washing machine and dryer booking system for Galvani student hall.',
    'footer_support' => 'Support',
    'footer_rules' => 'Rules',
    'footer_privacy' => 'Privacy Policy',
    'footer_report' => 'Report Issue',
    'footer_source_code' => 'Source Code',
    'footer_operational' => 'System Operational',
    'footer_server_time' => 'SRV',
    'footer_coded' => 'Coded with',
    'footer_by' => 'by',
    'btn_back_dashboard' => 'Back to Dashboard',

    'privacy_title' => 'Privacy & Data Management',

    // JS MODALS
    'modal_cancel_title' => 'Cancel Booking',
    'modal_cancel_msg' => 'Do you really want to cancel the booking for',
    'modal_confirm_title' => 'Confirm Booking',
    'modal_booking_msg' => 'You are booking for',
    'btn_confirm' => 'Confirm',
    'btn_close' => 'Close',
    'btn_cancel' => 'Cancel',
    'btn_delete' => 'Delete',
    'msg_info' => 'Info',
    'msg_error' => 'Error',
    'err_network' => 'Network Error',
    'err_server' => 'Server Error',
    'remaining_time' => 'Remaining Time',
    'msg_timeout' => 'Booking timed out! Please try again.',

    // --- PRIVACY PAGE ---
    'priv_title' => 'Privacy & Data Management',
    'priv_last_update' => 'Last update:',
    'priv_ref_gdpr' => 'Ref. GDPR (EU 2016/679)',

    // Section 1
    'priv_s1_title' => '1. What data is collected',
    'priv_s1_desc' => 'Only strictly necessary information is collected (Minimization Principle) to ensure service operation.',
    'priv_data_email' => 'Institutional Email',
    'priv_data_email_desc' => 'To uniquely identify you as an authorized student.',
    'priv_data_apt' => 'Apartment Number',
    'priv_data_apt_desc' => 'For urgent communications regarding machine usage.',
    'priv_data_pass' => 'Password',
    'priv_data_pass_desc' => 'Saved exclusively in hash format (irreversibly encrypted).',
    'priv_data_log' => 'Booking Log',
    'priv_data_log_desc' => 'Usage history for shift management.',

    // Section 2
    'priv_s2_title' => '2. Purpose of processing',
    'priv_s2_intro' => 'Personal data is used exclusively to:',
    'priv_use_1' => 'Manage secure access (Login).',
    'priv_use_2' => 'Organize the calendar and avoid conflicts between tenants.',
    'priv_use_3' => 'Ensure compliance with common rules.',
    'priv_note_vis_title' => 'Note on visibility:',
    'priv_note_vis_desc' => 'Data is not sold to third parties. However, the <em>Username</em> will be visible to other students on the booking calendar to allow internal organization (e.g., shift swaps).',

    // Section 3
    'priv_s3_title' => '3. Cancellation and Right to be Forgotten',
    'priv_s3_desc' => 'Account deletion can be requested at any time (Art. 17 GDPR). When an account is deleted, an <strong>Anonymization</strong> procedure is applied.',
    'priv_del_title' => 'Deleted Data',
    'priv_del_1' => 'Email and Password',
    'priv_del_2' => 'Association with apartment number',
    'priv_del_3' => 'All future bookings',
    'priv_anon_title' => 'Anonymized Data',
    'priv_anon_1' => 'Past booking history remains for statistics.',
    'priv_anon_2' => 'The author becomes generically <em>"Deleted User"</em>.',

    // Section 4
    'priv_s4_title' => '4. Your Rights',
    'priv_s4_desc' => 'In accordance with GDPR (Art. 15-22), you have the right to ask the manager for:',
    'priv_right_access' => 'Access to your data',
    'priv_right_rect' => 'Rectification of incorrect data',
    'priv_right_limit' => 'Limitation of processing',
    'priv_right_port' => 'Data portability',
    'priv_contact_text' => 'To exercise rights:',

    // Section 5
    'priv_s5_title' => '5. Cookie Policy',
    'priv_s5_desc' => 'Exclusively <strong>Technical Cookies</strong> (Session ID) are used to keep access active while browsing. No tracking, advertising profiling, or invasive third-party analytics are performed.',
    
    // Delay Reporting
    'lbl_delay' => 'Delay',
    'lbl_delay_min' => 'min',
    'modal_delay_title' => 'Report Delay',
    'modal_delay_desc' => 'Enter the minutes of delay accumulated by this machine to notify next users.',
    'btn_update_delay' => 'Update Delay',
    'delay_saved' => 'Delay updated!',

    // Laundry
    'Lavatrice 1' => 'Washing Machine 1',
    'Lavatrice 2' => 'Washing Machine 2',
    'Asciugatrice' => 'Dryer',
    'lavatrice' => 'Washing Machine',
    'asciugatrice' => 'Dryer',

    // Messages
    'no_other_registered_users' => 'No other registered users.',
    'no_other_users' => 'No other users.',
];
