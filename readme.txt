=== Restrict Users Registration by EmailVerifierPro.app ===
Contributors: tuhinbhuiyan
Tags: email blacklist, domain blacklist, registration, email verification, spam prevention
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easily control who can register. Block bad emails/domains, prevent duplicate IPs, and real-time email validation during signup.

== Description ==

**Restrict Users Registration by EmailVerifierPro.app** is a powerful plugin to help you control who can register on your WordPress site. Block disposable, blacklisted, or suspicious emails and domains, prevent duplicate IP signups, and connect to Third Party API for real-time email validation.

**Features:**

* Email Blacklist: Block specific email addresses from registering.
* Domain Blacklist: Block entire email domains (e.g., @tempmail.com).
* API Integration: Connect to your own EmailVerifierPro.app / VerifyEmail.app instance for advanced email validation.
* Prevent Duplicate IP Signups: Block multiple registrations from the same IP.
* Invalid Email Retry Limit: Automatically blacklist emails after repeated invalid attempts.
* Debug Logging: Enable for troubleshooting (not recommended in production).
* Delete All Data on Deactivation: Optionally remove all plugin data when deactivating.
* Admin Activity Log: View recent signup attempts and actions.
* AJAX-powered admin interface for fast, modern management.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/restusre-restrict-users-registration/` directory, or install through the WordPress plugins screen.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to **Dashboard > Restrict Users Registration** to configure options, add blacklists, and connect your EmailVerifierPro.app / VerifyEmail.app API.

== Frequently Asked Questions ==

= What is the difference between EmailVerifierPro.app software and the VerifyEmail.app service? =
EmailVerifierPro.app is self-hosted software that you can run on your own server, providing email validation features similar to ZeroBounce. VerifyEmail.app, on the other hand, is a cloud-based, pay-as-you-go email verification service that also offers features comparable to ZeroBounce. Both systems' APIs are supported and can be used with this plugin.

= Do I need an EmailVerifierPro.app software or VerifyEmail.app account? =
No, but connecting your own instance enables advanced email validation. The plugin works with basic blacklist features out of the box.

= Can I block disposable or temporary emails? =
Yes! Add known disposable domains to the domain blacklist, or use EmailVerifierPro.app / VerifyEmail.app for automatic detection.

= Will this block spam bots? =
It helps reduce spam by blocking known bad emails/domains and duplicate IPs, but for best results use alongside a full security plugin.

= What happens if I enable 'Delete all plugin data on deactivation'? =
All plugin settings, blacklists, and logs will be permanently deleted when you deactivate the plugin.

== Support ==
For support or questions, contact:
- info@emailverifierpro.app

== Screenshots ==
1. Email and domain blacklist management in the admin panel
2. Settings page with API integration
3. Signup activity log

== Changelog ==
= 1.0.1 =
* update of system

= 1.0.0 =
* Initial release

== Upgrade Notice ==
= 1.0.0 =
First public release.

== Credits ==
Developed by Tuhin Bhuiyan (https://tuhin.dev)

== License ==
This plugin is free software, released under the GPLv2 or later.
