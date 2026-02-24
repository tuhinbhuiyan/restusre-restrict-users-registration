<?php
// Prevent direct access for security
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Settings page template for Restrict Users Registration by EmailVerifierPro.app
 *
 * Renders the main plugin settings UI in the WordPress admin.
 *
 * @author Tuhin Bhuiyan <https://tuhin.dev>
 * @package RestrictUsersRegistration
 */

$general = RESTUSRE_DB::get_option('restusre_general', array(
    'enabled' => 0,
    'prevent_duplicate_ip' => 0,
    'delete_on_deactivate' => 0
));
$api     = RESTUSRE_DB::get_option('restusre_api', array( 'api_domain' => '', 'username' => '', 'api_key' => '' ));
?>
<div class="container-fluid" style="padding: 2em 0;">
    <div class="card shadow-sm mx-auto" style="border-radius: 1rem; max-width: 800px; width: 100%;">
        <div class="card-body p-2 p-md-3">
            <ul class="nav nav-tabs mb-4 d-flex justify-content-between" id="restusre-settings-tabs" role="tablist" style="width:100%;">
                <div class="d-flex">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="restusre-general-tab" data-bs-toggle="tab" data-bs-target="#restusre-general" type="button" role="tab"><?php esc_html_e('General', 'restusre-restrict-users-registration'); ?></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="restusre-api-tab" data-bs-toggle="tab" data-bs-target="#restusre-api-settings" type="button" role="tab"><?php esc_html_e('API Settings', 'restusre-restrict-users-registration'); ?></button>
                    </li>
                </div>
                <div class="d-flex ms-auto">
                    <li class="nav-item" role="presentation" style="background:rgb(45,183,66);border-radius:0.5em;margin-left:0.5em;">
                        <button class="nav-link" id="restusre-support-tab" type="button" role="tab" tabindex="-1" onclick="window.open('https://api.whatsapp.com/send?phone=15184002802','_blank'); return false;" style="cursor:pointer;color:#fff;display:flex;align-items:center;background:transparent;border:none;padding:0.35em 0.85em;min-height:2.2em;min-width:2.2em;">
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:1.7em;height:1.7em;border-radius:50%;background:rgba(255,255,255,0.18);margin-right:0.4em;">
                                <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0NzguMTY1IDQ3OC4xNjUiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDQ3OC4xNjUgNDc4LjE2NSIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgd2lkdGg9IjUxMiIgaGVpZ2h0PSI1MTIiPjxwYXRoIGQ9Ik00NzguMTY1IDIzMi45NDZjMCAxMjguNTY3LTEwNS4wNTcgMjMyLjk2Ni0yMzQuNjc5IDIzMi45NjYtNDEuMTAyIDAtNzkuODE0LTEwLjU5OS0xMTMuNDQ1LTI4Ljk2OUwwIDQ3OC4xNjVsNDIuNDM3LTEyNS4wNGMtMjEuNDM4LTM1LjA2NS0zMy43Ny03Ni4yMDctMzMuNzctMTIwLjE1OUM4LjY2NyAxMDQuMzQgMTEzLjc2MyAwIDI0My40ODUgMGMxMjkuNjIzIDAgMjM0LjY4IDEwNC4zNCAyMzQuNjggMjMyLjk0NnpNMjQzLjQ4NSAzNy4wOThjLTEwOC44MDIgMC0xOTcuNDIyIDg3LjgwMy0xOTcuNDIyIDE5NS44NjggMCA0Mi45MTUgMTMuOTg2IDgyLjYwMyAzNy41NzYgMTE0Ljg3OWwtMjQuNTg2IDcyLjU0MiA3NS44NDktMjMuOTY4YzMxLjEyMSAyMC40ODEgNjguNDU3IDMyLjI5NiAxMDguNTgzIDMyLjI5NiAxMDguNzIzIDAgMTk3LjMyMy04Ny44NDMgMTk3LjMyMy0xOTUuOTA4IDAtMTA3Ljg4Ni04OC42LTE5NS43MDktMTk3LjMyMy0xOTUuNzA5ek0zNjEuOTMxIDI4Ni42MmMtMS4zOTUtMi4zMzEtNS4yMi0zLjc0Ni0xMC44OTgtNi44MTQtNS45MTctMi44NDktMzQuMDg5LTE2LjQ5Ny0zOS41MDgtMTguMzctNS4xNi0xLjkxMy04Ljk4Ni0yLjg0OS0xMi44MTEgMi44MjktNC4wMDUgNS42MzgtMTQuOTAzIDE4LjYyOS0xOC4yMyAyMi4zNTQtMy41NDYgMy43ODUtNi44NTQgNC4yNjQtMTIuNTUyIDEuNDM1LTUuNjE4LTIuODA5LTI0LjI2Ny04Ljg2Ni00Ni4yMDMtMjguMzkxLTE3LjA1NS0xNS4wNDItMjguNjctMzMuNzExLTMxLjk5Ny0zOS41MDgtMy40MjctNS43NTgtLjM5OC04LjgyNiAyLjQ3MS0xMS42MzUgMi42OS0yLjU5IDUuNzc4LTYuNzM0IDguNjI3LTEwLjA0MSAyLjk2OS0zLjI4NyAzLjkwNS01LjYzOCA1Ljc5OC05LjQyNCAxLjkxMy0zLjkwNS45MzYtNy4xOTItLjQ3OC0xMC4xNDEtMS40MTUtMi44NDktMTMuMDEtMzAuODgxLTE3Ljc1Mi00Mi4zMzctNC44NDEtMTEuNDE2LTkuNTQzLTkuNTIzLTEyLjg3MS05LjUyMy0zLjQ2NyAwLTcuMjEyLS40NzgtMTEuMTE3LS40NzgtMy43ODUgMC0xMC4wNDEgMS4zOTUtMTUuMzgxIDcuMTkyLTUuMiA1LjY1OC0yMC4xMjMgMTkuNDY1LTIwLjEyMyA0Ny41OTcgMCAyOC4wNTIgMjAuNjAxIDU1LjMwOCAyMy41NSA1OS4wNTMgMi44NjkgMy43ODUgMzkuNzQ3IDYzLjE5NyA5OC4zMDMgODYuMDcgNTguNDc2IDIyLjg3MiA1OC40NzYgMTUuMzIxIDY5LjExNSAxNC4zNjUgMTAuMzgtLjk1NiAzNC4wNjktMTMuODY3IDM4LjgxMS0yNy4wOTYgNC42Ni0xMy40NSA0LjY2LTI0Ljc2NiAzLjI0Ni0yNy4xMzd6IiBmaWxsPSIjRkZGIi8+PC9zdmc+" alt="WhatsApp" style="height:1.1em;width:1.1em;vertical-align:middle;" />
                            </span>
                            <span style="color:#fff;font-weight:600;letter-spacing:0.01em;font-size:0.98em;line-height:1;"><?php esc_html_e('Support', 'restusre-restrict-users-registration'); ?></span>
                        </button>
                    </li>
                </div>
            </ul>
            <form id="restusre-settings-form" method="post">
                <div class="tab-content" id="restusre-settings-content">
                    <div class="tab-pane fade show active" id="restusre-general" role="tabpanel">
                        <h4 class="mb-4"><i class="bi bi-gear"></i> <?php esc_html_e( 'General Settings', 'restusre-restrict-users-registration' ); ?></h4>
                        <div class="form-check form-switch mb-3">
                            <input type="checkbox" class="form-check-input" id="restusre-enabled" name="general[enabled]" value="1" <?php checked( !empty($general['enabled']), 1 ); ?>>
                            <label class="form-check-label" for="restusre-enabled"><?php esc_html_e( 'Enable Plugin Email Validation', 'restusre-restrict-users-registration' ); ?></label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input type="checkbox" class="form-check-input" id="restusre-prevent-dup-ip" name="general[prevent_duplicate_ip]" value="1" <?php checked( !empty($general['prevent_duplicate_ip']), 1 ); ?>>
                            <label class="form-check-label" for="restusre-prevent-dup-ip"><?php esc_html_e( 'Prevent Duplicate IP Sign-ups', 'restusre-restrict-users-registration' ); ?></label>
                        </div>
                        <div class="mb-3">
                            <label for="restusre-invalid-retry-limit" class="form-label">
                                <?php esc_html_e('Invalid Email Retry Limit', 'restusre-restrict-users-registration'); ?>
                                <span class="text-muted" style="font-weight:normal; font-size:90%;">(<?php esc_html_e('How many times an email can return invalid before being blacklisted', 'restusre-restrict-users-registration'); ?>)</span>
                            </label>
                            <input type="number" min="1" max="10" class="form-control" id="restusre-invalid-retry-limit" name="general[invalid_retry_limit]" value="<?php echo isset($general['invalid_retry_limit']) ? esc_attr($general['invalid_retry_limit']) : 3; ?>" style="max-width:120px;">
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input type="checkbox" class="form-check-input" id="restusre-debug-logging" name="general[debug_logging]" value="1" <?php checked( !empty($general['debug_logging']), 1 ); ?>>
                            <label class="form-check-label" for="restusre-debug-logging"><?php esc_html_e( 'Enable Debug Logging (for troubleshooting, do not use in production)', 'restusre-restrict-users-registration' ); ?></label>
                        </div>
                         <div class="form-check form-switch mb-3">
                            <input type="checkbox" class="form-check-input" id="restusre-delete-on-deactivate" name="general[delete_on_deactivate]" value="1" <?php checked( !empty($general['delete_on_deactivate']), 1 ); ?>>
                            <label class="form-check-label" for="restusre-delete-on-deactivate"><?php esc_html_e( 'Delete all plugin data when the plugin is deactivated', 'restusre-restrict-users-registration' ); ?></label>
                        </div>
                        <div id="restusre-delete-confirmation" class="alert alert-danger mt-3" style="display:<?php echo !empty($general['delete_on_deactivate']) ? 'block' : 'none'; ?>;">
                            <strong><?php esc_html_e( 'Warning:', 'restusre-restrict-users-registration' ); ?></strong> <?php esc_html_e( 'All plugin data will be permanently deleted upon deactivation!', 'restusre-restrict-users-registration' ); ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="restusre-api-settings" role="tabpanel">
                        <h4 class="mb-4"><i class="bi bi-key"></i> <?php esc_html_e( 'API Settings', 'restusre-restrict-users-registration' ); ?></h4>
                        <div class="mb-3">
                            <label class="form-label"><?php esc_html_e( 'API Domain URL:', 'restusre-restrict-users-registration' ); ?></label>
                            <input type="text" class="form-control" name="api[api_domain]" value="<?php echo esc_attr( $api['api_domain'] ); ?>" placeholder="https://yourdomain/">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php esc_html_e( 'Username:', 'restusre-restrict-users-registration' ); ?></label>
                            <input type="text" class="form-control" name="api[username]" value="<?php echo esc_attr( $api['username'] ); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php esc_html_e( 'API Secret Key:', 'restusre-restrict-users-registration' ); ?></label>
                            <input type="text" class="form-control" name="api[api_key]" value="<?php echo esc_attr( $api['api_key'] ); ?>">
                        </div>
                    </div>
                </div>
                <input type="hidden" name="action" value="restusre_save_settings">
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary btn-md px-4"><i class="bi bi-save"></i> <?php esc_html_e( 'Save', 'restusre-restrict-users-registration' ); ?></button>
                </div>
            </form>
            <div id="restusre-settings-notice" style="display:none;"></div>
        </div>
    </div>
    <div class="text-center mt-4" style="padding-top: 2.5rem;">
        <a href="https://emailverifierpro.app/#pricing" target="_blank" rel="noopener">
            <img src="<?php echo esc_url( RESTUSRE_PLUGIN_URL . 'admin/images/email_verifier_pro_promox600.jpg' ); ?>" width="auto" height="208" alt="<?php esc_attr_e('Get your own Email Verification Application for Personal or Use As SaaS. Limited Time Discount!','restusre-restrict-users-registration'); ?>" style="border-radius:12px;box-shadow:0 2px 8px #0001;">
        </a>
    </div>
</div>
<!-- End of Settings Template -->