<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed'                                              => 'These credentials do not match our records.',
    'password'                                            => 'The provided password is incorrect.',
    'throttle'                                            => 'Too many login attempts. Please try again in :seconds seconds.',
    'network_issue'                                       => 'Network Issue.',

    // New Added.
    'you_are_not_logged_in'                               => 'You are not logged in.',
    'invalid_email'                                       => 'Invalid email.',
    'login_successfully'                                  => 'Login successfully.',
    'recovery_password_link_sent_successfully'            => 'Recovery password link sent successfully.',
    'unauthorized_login'                                  => 'Unauthorized login.',
    'user_is_not_active_contact_admin'                    => 'User is not active. Contact admin for activate user.',
    'your_session_has_been_expired_kindly_login_again'    => 'Your session has been expired kindly login again.',
    'logout_successful'                                   => 'Logout successful.',
    'failed_to_logout'                                    => 'Failed to logout.',
    'invalid_mobile'                                      => 'Invalid mobile number.',
    'otp_entered_incorrect'                               => 'The OTP entered is incorrect.',
    'user_is_already_verified_please_login'               => 'User is already verified please login.',
    'password_changed_successfully_please_login'          => 'Password changed successfully. Please login.',
    'invalid_otp'                                         => 'Invalid Otp.',
    'sorry_invalid_email_and_or_password_combination'     => 'Sorry invalid email and or password combination.',
    'your_account_is_in_active_please_contact_admin'      => 'Your account is In-Active. Please contact admin.',
    'current_password_incorrect'                          => 'The current password is incorrect.',
    'you_do_not_have_the_permission_to_use_this_resource' => 'You do not have the permission to use this resource.',
    'reset_password_link_sent_to_your_email_id'           => 'Forgot password email sent to your email id.',
    'otp_verified_successfully'                           => 'OTP verified successfully. You can now reset your password.',
    'invalid_url'           => 'Invalid URL or unable to reach to the required resource.',
    'invalid_user'           => 'Invalid user reference.',

    //
    'failed_to_module_name'                               => 'Failed to :moduleName.',
    'failed_to_save_module_name'                          => 'Failed to save :moduleName.',
    'failed_to_update_module_name'                        => 'Failed to update :moduleName.',
    'failed_to_retrieve_module_name'                      => 'Failed to retrieve :moduleName.',
    'failed_to_delete_module_name'                        => 'Failed to delete the :moduleName',
    'module_name_saved_successfully'                      => ':moduleName saved successfully.',
    'module_name_retrieved_successfully'                  => ':moduleName retrieved successfully.',
    'module_name_updated_successfully'                    => ':moduleName updated successfully.',
    'module_name_deleted_successfully'                    => ':moduleName deleted successfully.',
    'module_name_not_found'                               => ':moduleName not found.',
    'module_status_changed_successfully'                  => ':moduleName status changed successfully.',
    'failed_to_change_status'                             => 'Failed to change the :moduleName status.',
    'module_name_changed_successfully'                    => ':moduleName changed successfully.',
    'failed_to_change_module_name'                        => 'Failed to change :moduleName.',
    'module_name_duplicated_successfully'                 => ':moduleName duplicated successfully.',
    'failed_to_duplicate_module_name'                     => 'Failed to duplicate the :moduleName.',
    'module_name_cloned_successfully'                     => ':moduleName cloned successfully.',
    'failed_to_clone_module_name'                         => 'Failed to clone the :moduleName.',
    'already_in_use'                                      => 'Sorry you cannot delete this record as it is already in use.',
    'already_in_use_change_status'                        => 'Sorry you cannot change status this record as it is already in use.',

    //
    'synced_successfully'                                 => 'Synced successfully.',
    'failed_to_sync'                                      => 'Failed to sync.',
    'node_data_value_updated_successfully'                => 'Successfully updated the value.',
    'failed_to_update_the_node_data'                      => 'Failed to update the value.',
    'only_numbers_are_not_allowed'                        => 'Only numbers are not allowed.',
    'file_uploaded_successfully'                          => 'File uploaded successfully.',
    'failed_to_upload_file'                               => 'Failed to upload file.',
    'file_deleted_successfully'                           => 'File deleted successfully.',
    'failed_to_delete_file'                               => 'Failed to delete file.',
    'registration_successful_please_verify_email'         => 'Registration successful. Please check your email to verify your account.',
    'email_verified_successfully'                         => 'Email verified successfully. You can now login to your account.',
    'invalid_token'                                       => 'Invalid verification token.',
    'token_expired'                                       => 'Verification token has expired. Please register again.',
    'please_verify_your_email_before_login'               => 'Please verify your email address before logging in.',
    'account_inactive_or_blocked'                         => 'Your account is inactive or has been blocked. Please contact the administrator.',
    'contacts_imported_successfully'                      => 'Contacts imported successfully.',
    'atleast_one_field_required'                          => 'Please Select atleast one Filter.',
    'email_exist'                                         => "The entered email doesn't exist.",
    'contacts_import_queued'                              => 'Your CSV import has been queued for processing. You will receive an email with the results once the import is complete.',
    'import_status_retrieved'                             => 'Import status retrieved successfully.',
    'card_deleted_successfully'                           => 'Card deleted successfully',
    'card_deleted_failed'                                 => 'Failed to delete card',
    'batch_data_max_limit'                                => 'The number of records in the CSV file exceeds the allowed import limit of '.config('global.MAX_CONTACT_DATA_LIMIT')
];
