<?php
// Prevent direct access for security
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Signup Activity admin page template for Restrict Users Registration by EmailVerifierPro.app
 *
 * Renders the user's signup activity table and management UI in the WordPress admin.
 *
 * @author Tuhin Bhuiyan <https://tuhin.dev>
 * @package RestrictUsersRegistration
 */

// User's Signup Activity Page
// Table: restusre_ip_signups (id, email_used, ip_address, signup_time)

?>
<div class="container-fluid" style="padding: 2em 0;">
    <div class="card shadow-sm mx-auto" style="border-radius: 1rem; max-width: 800px; width: 100%;">
        <div class="card-body p-4 p-md-5">
            <h2 class="mb-4"><i class="bi bi-person-lines-fill"></i> <?php esc_html_e('User Signup Logs', 'restusre-restrict-users-registration'); ?></h2>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <input type="text" id="restusre-signup-search" class="form-control" placeholder="<?php esc_attr_e('Search email or IP...', 'restusre-restrict-users-registration'); ?>" style="max-width: 250px; display:inline-block;">
                </div>
                <div>
                    <label class="me-2">Show</label>
                    <select id="restusre-signup-page-size" class="form-select d-inline-block" style="width:auto;">
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="ms-2">per page</span>
                </div>
            </div>
            <table class="table table-bordered table-hover" id="restusre-signup-activity-table">
                <thead class="table-light">
                    <tr>
                        <th><?php esc_html_e('ID', 'restusre-restrict-users-registration'); ?></th>
                        <th><?php esc_html_e('Email', 'restusre-restrict-users-registration'); ?></th>
                        <th><?php esc_html_e('IP Address', 'restusre-restrict-users-registration'); ?></th>
                        <th><?php esc_html_e('Signup Time', 'restusre-restrict-users-registration'); ?></th>
                        <th><?php esc_html_e('Action', 'restusre-restrict-users-registration'); ?></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <nav>
                <ul class="pagination justify-content-center" id="restusre-signup-pagination"></ul>
            </nav>
        </div>
    </div>
    <div class="text-center mt-4" style="padding-top: 2.5rem;">
        <a href="https://emailverifierpro.app/#pricing" target="_blank" rel="noopener">
            <img src="<?php echo esc_url( RESTUSRE_PLUGIN_URL . 'admin/images/email_verifier_pro_promox600.jpg' ); ?>" width="auto" height="208" alt="<?php esc_attr_e('Get your own Email Verification Application for Personal or Use As SaaS. Limited Time Discount!','restusre-restrict-users-registration'); ?>" style="border-radius:12px;box-shadow:0 2px 8px #0001;">
        </a>
    </div>
</div>
<!-- End of Signup Activity Template -->
