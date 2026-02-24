<?php
// Prevent direct access for security
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Domain Blacklist admin page template for Restrict Users Registration by EmailVerifierPro.app
 *
 * Renders the domain blacklist management UI in the WordPress admin.
 *
 * @author Tuhin Bhuiyan <https://tuhin.dev>
 * @package RestrictUsersRegistration
 */

// Domain Blacklist Management Page
$domain_blacklist = RESTUSRE_DB::get_option('restusre_domain_blacklist', array());
$debug = !empty(RESTUSRE_DB::get_option('restusre_general', array())['debug_logging']);
?>
<div class="container-fluid" style="padding: 2em 0;">
    <div class="card shadow-sm mx-auto" style="border-radius: 1rem; max-width: 800px; width: 100%;">
        <div class="card-body p-4 p-md-5">
            <h2 class="mb-3"><i class="bi bi-envelope-x"></i> <?php esc_html_e( 'Domain Blacklist', 'restusre-restrict-users-registration' ); ?></h2>
            <p class="text-muted"><?php esc_html_e( 'Block specific domains from registering. Add below:', 'restusre-restrict-users-registration' ); ?></p>
            <form id="restusre-domain-blacklist-form" class="mb-3 d-flex flex-row gap-2" onsubmit="return false;" style="max-width: 500px;">
                <input type="text" class="form-control" id="restusre-blacklist-domain-input" placeholder="example.com" required />
                <button type="submit" class="btn btn-danger"><?php esc_html_e('Add to Blacklist', 'restusre-restrict-users-registration'); ?></button>
            </form>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <label for="restusre-domain-search" class="form-label mb-0 me-2">Search:</label>
                    <input type="text" id="restusre-domain-search" class="form-control d-inline-block" style="width:200px;" placeholder="<?php esc_attr_e('Search domain...','restusre-restrict-users-registration'); ?>">
                </div>
                <div>
                    <label for="restusre-domain-page-size" class="form-label mb-0 me-2">Show:</label>
                    <select id="restusre-domain-page-size" class="form-select d-inline-block" style="width:80px;">
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="ms-2"><?php esc_html_e('per page','restusre-restrict-users-registration'); ?></span>
                </div>
            </div>
            <table class="table table-bordered table-hover" id="restusre-domain-blacklist-table">
                <thead class="table-light">
                    <tr>
                        <th><?php esc_html_e('Domain', 'restusre-restrict-users-registration'); ?></th>
                        <th><?php esc_html_e('Status', 'restusre-restrict-users-registration'); ?></th>
                        <th><?php esc_html_e('Added', 'restusre-restrict-users-registration'); ?></th>
                        <th><?php esc_html_e('Action', 'restusre-restrict-users-registration'); ?></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <nav>
                <ul class="pagination justify-content-center" id="restusre-domain-pagination"></ul>
            </nav>
        </div>
    </div>
    <div class="text-center mt-4" style="padding-top: 2.5rem;">
        <a href="https://emailverifierpro.app/#pricing" target="_blank" rel="noopener">
            <img src="<?php echo esc_url( RESTUSRE_PLUGIN_URL . 'admin/images/email_verifier_pro_promox600.jpg' ); ?>" width="auto" height="208" alt="<?php esc_attr_e('Get your own Email Verification Application for Personal or Use As SaaS. Limited Time Discount!','restusre-restrict-users-registration'); ?>" style="border-radius:12px;box-shadow:0 2px 8px #0001;">
        </a>
    </div>
</div>
<!-- End of Domain Blacklist Template -->
