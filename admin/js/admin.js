/*
 * Admin JS for Restrict Users Registration by EmailVerifierPro.app
 * Handles all admin UI logic, AJAX, and dynamic table rendering for plugin admin pages.
 *
 * @author Tuhin Bhuiyan <https://tuhin.dev>
 * @package RestrictUsersRegistration
 */

jQuery(document).ready(function($) {
    // Bootstrap tab persistence
    $('#restusre-settings-tabs button[data-bs-toggle="tab"]').on('click', function(e){
        e.preventDefault();
        var target = $(this).attr('data-bs-target');
        // Remove active from all tabs and panes
        $('#restusre-settings-tabs button').removeClass('active');
        $('.tab-pane').removeClass('show active');
        // Add active to clicked tab and corresponding pane
        $(this).addClass('active');
        $(target).addClass('show active');
        // Persist tab
        window.localStorage.setItem('restusre_active_tab', target);
    });
    // On page load, restore last active tab
    var lastTab = window.localStorage.getItem('restusre_active_tab');
    if(lastTab && $(lastTab).length){
        $('#restusre-settings-tabs button[data-bs-target="'+lastTab+'"]').addClass('active');
        $('.tab-pane').removeClass('show active');
        $(lastTab).addClass('show active');
    } else {
        // Default to first tab
        $('#restusre-settings-tabs button:first').addClass('active');
        $('.tab-pane:first').addClass('show active');
    }

    // SETTINGS FORM SUBMISSION (AJAX ONLY, PREVENT DEFAULT)
    $('#restusre-settings-form').off('submit').on('submit', function(e) {
        e.preventDefault(); // Always prevent default form submission immediately
        var general = {
            enabled: $('input[name="general[enabled]"]').is(':checked') ? 1 : 0,
            prevent_duplicate_ip: $('input[name="general[prevent_duplicate_ip]"]').is(':checked') ? 1 : 0,
            delete_on_deactivate: $('input[name="general[delete_on_deactivate]"]').is(':checked') ? 1 : 0,
            invalid_retry_limit: parseInt($('#restusre-invalid-retry-limit').val(), 10) || 3,
            debug_logging: $('#restusre-debug-logging').is(':checked') ? 1 : 0
        };
        var data = {
            action: 'restusre_save_settings',
            nonce: RESTUSRE_AJAX.nonce,
            general: general,
            api: {
                api_domain: $('input[name="api[api_domain]"]').val(),
                username: $('input[name="api[username]"]').val(),
                api_key: $('input[name="api[api_key]"]').val()
            },
            domains: $('#restusre-domains').length ? $('#restusre-domains').val().split('\n').map(function(item) {
                return item.trim();
            }).filter(function(item) {
                return item.length > 0;
            }) : []
        };
        if (general.debug_logging) {
            console.log('RESTUSRE DEBUG: Data sent to server:', data); // Debug log
        }
        // Show saving notice
        var $notice = $('#restusre-settings-notice');
        $notice.stop(true, true).hide().removeClass('alert-success alert-danger').addClass('alert alert-info').text('Saving settings...').fadeIn();
        $.post(RESTUSRE_AJAX.ajax_url, data, function(response) {
            if (response.success) {
                $notice.removeClass('alert-info alert-danger').addClass('alert-success').text(response.data).fadeIn().delay(2500).fadeOut();
            } else {
                $notice.removeClass('alert-info alert-success').addClass('alert-danger').text('Error: ' + response.data).fadeIn().delay(3500).fadeOut();
            }
        }).fail(function() {
            $notice.removeClass('alert-info alert-success').addClass('alert-danger').text('AJAX error. Please try again.').fadeIn().delay(3500).fadeOut();
        });
        return false; // Extra safety: never allow default form submission
    });

    // SETTINGS: Delete on Deactivate confirmation logic
    $('#restusre-delete-on-deactivate').on('change', function() {
        if ($(this).is(':checked')) {
            if (!confirm('Are you sure you want to delete all plugin data upon deactivation? This action cannot be undone!')) {
                $(this).prop('checked', false);
                $('#restusre-delete-confirmation').hide();
            } else {
                $('#restusre-delete-confirmation').show();
            }
        } else {
            $('#restusre-delete-confirmation').hide();
        }
    });

    // EMAIL BLACKLIST TAB LOGIC
    function renderEmailBlacklistTable(emails) {
        var $tbody = $('#restusre-email-blacklist-table tbody');
        $tbody.empty();
        if (!emails || emails.length === 0) {
            $tbody.append('<tr><td colspan="4" class="text-center text-muted">No blacklisted emails.</td></tr>');
            return;
        }
        emails.forEach(function(item) {
            var email = item.email || item;
            var status = item.status || 'active';
            var added = item.added || '';
            $tbody.append('<tr>' +
                '<td>' + email + '</td>' +
                '<td>' + status + '</td>' +
                '<td>' + (added ? added : '-') + '</td>' +
                '<td><button class="btn btn-sm btn-outline-danger restusre-remove-email" data-email="' + email + '">Remove</button></td>' +
            '</tr>');
        });
    }
    function fetchEmailBlacklist() {
        $.post(RESTUSRE_AJAX.ajax_url, {
            action: 'restusre_email_blacklist_list',
            nonce: RESTUSRE_AJAX.nonce
        }, function(response) {
            if (response.success) {
                renderEmailBlacklistTable(response.data);
            } else {
                renderEmailBlacklistTable([]);
            }
        });
    }
    // Load on tab open
    $('#restusre-email-blacklist-tab').on('click', function() {
        fetchEmailBlacklist();
    });
    // Also load on page load if tab is active
    if ($('#restusre-email-blacklist').hasClass('show')) {
        fetchEmailBlacklist();
    }
    // Add email
    $('#restusre-email-blacklist-form').off('submit').on('submit', function(e) {
        e.preventDefault();
        var email = $('#restusre-blacklist-email-input').val().trim().toLowerCase();
        if (!email) {
            console.log('RESTUSRE DEBUG: No email entered');
            return;
        }
        console.log('RESTUSRE DEBUG: Submitting email to blacklist:', email);
        $.post(RESTUSRE_AJAX.ajax_url, {
            action: 'restusre_email_blacklist_add',
            nonce: RESTUSRE_AJAX.nonce,
            email: email
        }, function(response) {
            console.log('RESTUSRE DEBUG: AJAX response:', response);
            if (response.success) {
                $('#restusre-blacklist-email-input').val('');
                fetchEmailBlacklist();
            } else {
                alert(response.data || 'Failed to add email.');
            }
        }).fail(function(xhr, status, error) {
            console.log('RESTUSRE DEBUG: AJAX error:', status, error, xhr.responseText);
            alert('AJAX error. Please try again.');
        });
    });
    // Remove email
    $('#restusre-email-blacklist-table').off('click', '.restusre-remove-email').on('click', '.restusre-remove-email', function(e) {
        e.preventDefault();
        var email = $(this).data('email');
        email = email.trim().toLowerCase();
        if (!window.restusreRemoveConfirming) {
            window.restusreRemoveConfirming = true;
            if (!confirm('Remove ' + email + ' from blacklist?')) {
                window.restusreRemoveConfirming = false;
                return;
            }
        }
        $.post(RESTUSRE_AJAX.ajax_url, {
            action: 'restusre_email_blacklist_remove',
            nonce: RESTUSRE_AJAX.nonce,
            email: email
        }, function(response) {
            window.restusreRemoveConfirming = false;
            if (response.success) {
                fetchEmailBlacklist();
            } else {
                alert(response.data || 'Failed to remove email.');
            }
        });
    });

    // DOMAIN BLACKLIST PAGE LOGIC
    function renderDomainBlacklistTable(domains) {
        var $tbody = $('#restusre-domain-blacklist-table tbody');
        $tbody.empty();
        if (!domains || domains.length === 0) {
            $tbody.append('<tr><td colspan="4" class="text-center text-muted">No blacklisted domains.</td></tr>');
            return;
        }
        domains.forEach(function(item) {
            var domain = item.domain || item;
            var status = item.status || 'active';
            var added = item.added || '';
            $tbody.append('<tr>' +
                '<td>' + domain + '</td>' +
                '<td>' + status + '</td>' +
                '<td>' + (added ? added : '-') + '</td>' +
                '<td><button class="btn btn-sm btn-outline-danger restusre-remove-domain" data-domain="' + domain + '">Remove</button></td>' +
            '</tr>');
        });
    }
    function fetchDomainBlacklist(callback) {
        $.post(RESTUSRE_AJAX.ajax_url, {
            action: 'restusre_domain_blacklist_list',
            nonce: RESTUSRE_AJAX.nonce
        }, function(response) {
            if (response.success) {
                if (callback) callback(response.data);
            } else {
                if (callback) callback([]);
            }
        });
    }
    // Paging, search, and page size for domain blacklist
    var domainBlacklistData = [], domainPage = 1, domainPageSize = 20, domainSearch = '';
    function updateDomainBlacklistTable() {
        var filtered = domainBlacklistData.filter(function(item) {
            return !domainSearch || (item.domain && item.domain.toLowerCase().includes(domainSearch));
        });
        var total = filtered.length;
        var start = (domainPage - 1) * domainPageSize;
        var end = start + domainPageSize;
        var pageData = filtered.slice(start, end);
        renderDomainBlacklistTable(pageData);
        // Pagination
        var $pagination = $('#restusre-domain-pagination');
        $pagination.empty();
        var totalPages = Math.ceil(total / domainPageSize);
        if (totalPages <= 1) return;
        for (var i = 1; i <= totalPages; i++) {
            $pagination.append('<li class="page-item' + (i === domainPage ? ' active' : '') + '"><a class="page-link" href="#">' + i + '</a></li>');
        }
    }
    // Event: open page
    if ($('#restusre-domain-blacklist-table').length) {
        fetchDomainBlacklist(function(data) {
            domainBlacklistData = data;
            updateDomainBlacklistTable();
        });
    }
    // Add domain
    $('#restusre-domain-blacklist-form').on('submit', function(e) {
        e.preventDefault();
        var domain = $('#restusre-blacklist-domain-input').val().trim().toLowerCase();
        if (!domain) return;
        $.post(RESTUSRE_AJAX.ajax_url, {
            action: 'restusre_domain_blacklist_add',
            nonce: RESTUSRE_AJAX.nonce,
            domain: domain
        }, function(response) {
            if (response.success) {
                $('#restusre-blacklist-domain-input').val('');
                fetchDomainBlacklist(function(data) {
                    domainBlacklistData = data;
                    updateDomainBlacklistTable();
                });
            } else {
                alert(response.data || 'Failed to add domain.');
            }
        });
    });
    // Remove domain
    $('#restusre-domain-blacklist-table').off('click', '.restusre-remove-domain').on('click', '.restusre-remove-domain', function(e) {
        e.preventDefault();
        var domain = $(this).data('domain');
        domain = domain.trim().toLowerCase();
        if (!window.restusreRemoveDomainConfirming) {
            window.restusreRemoveDomainConfirming = true;
            if (!confirm('Remove ' + domain + ' from blacklist?')) {
                window.restusreRemoveDomainConfirming = false;
                return;
            }
        }
        $.post(RESTUSRE_AJAX.ajax_url, {
            action: 'restusre_domain_blacklist_remove',
            nonce: RESTUSRE_AJAX.nonce,
            domain: domain
        }, function(response) {
            window.restusreRemoveDomainConfirming = false;
            if (response.success) {
                fetchDomainBlacklist(function(data) {
                    domainBlacklistData = data;
                    updateDomainBlacklistTable();
                });
            } else {
                alert(response.data || 'Failed to remove domain.');
            }
        });
    });
    // Paging
    $('#restusre-domain-pagination').on('click', '.page-link', function(e) {
        e.preventDefault();
        var page = parseInt($(this).text(), 10);
        if (!isNaN(page)) {
            domainPage = page;
            updateDomainBlacklistTable();
        }
    });
    // Page size
    $('#restusre-domain-page-size').on('change', function() {
        domainPageSize = parseInt($(this).val(), 10) || 20;
        domainPage = 1;
        updateDomainBlacklistTable();
    });
    // Search
    $('#restusre-domain-search').on('input', function() {
        domainSearch = $(this).val().trim().toLowerCase();
        domainPage = 1;
        updateDomainBlacklistTable();
    });

    // User Signup Activity JS
    (function($){
        let allSignups = [];
        let filteredSignups = [];
        let currentPage = 1;
        let pageSize = 20;
        let searchTerm = '';
        function renderSignupTable(rows) {
            var $tbody = $('#restusre-signup-activity-table tbody');
            $tbody.empty();
            if (!rows || rows.length === 0) {
                $tbody.append('<tr><td colspan="5" class="text-center text-muted">No signup activity found.</td></tr>');
                return;
            }
            rows.forEach(function(item) {
                $tbody.append('<tr>' +
                    '<td>' + item.id + '</td>' +
                    '<td>' + item.email_used + '</td>' +
                    '<td>' + item.ip_address + '</td>' +
                    '<td>' + item.signup_time + '</td>' +
                    '<td><button class="btn btn-sm btn-outline-danger restusre-remove-signup" data-id="' + item.id + '"><i class="bi bi-trash"></i> Remove</button></td>' +
                '</tr>');
            });
        }
        function renderSignupPagination(total, page, limit) {
            var $pagination = $('#restusre-signup-pagination');
            $pagination.empty();
            var totalPages = Math.ceil(total / limit);
            if (totalPages <= 1) return;
            for (let i = 1; i <= totalPages; i++) {
                $pagination.append('<li class="page-item' + (i === page ? ' active' : '') + '"><a class="page-link" href="#">' + i + '</a></li>');
            }
        }
        function updateSignupTable() {
            filteredSignups = allSignups.filter(function(item) {
                return !searchTerm || (item.email_used && item.email_used.toLowerCase().includes(searchTerm)) || (item.ip_address && item.ip_address.toLowerCase().includes(searchTerm));
            });
            var total = filteredSignups.length;
            var start = (currentPage - 1) * pageSize;
            var end = start + pageSize;
            var pageRows = filteredSignups.slice(start, end);
            renderSignupTable(pageRows);
            renderSignupPagination(total, currentPage, pageSize);
        }
        function fetchSignupActivity() {
            $.post(RESTUSRE_AJAX.ajax_url, {
                action: 'restusre_signup_activity_list',
                nonce: RESTUSRE_AJAX.nonce
            }, function(response) {
                if (response.success) {
                    allSignups = response.data || [];
                    currentPage = 1;
                    updateSignupTable();
                } else {
                    allSignups = [];
                    updateSignupTable();
                }
            });
        }
        // Pagination click
        $('#restusre-signup-pagination').on('click', 'a', function(e) {
            e.preventDefault();
            var page = parseInt($(this).text());
            if (!isNaN(page)) {
                currentPage = page;
                updateSignupTable();
            }
        });
        // Page size change
        $('#restusre-signup-page-size').on('change', function() {
            pageSize = parseInt($(this).val());
            currentPage = 1;
            updateSignupTable();
        });
        // Search filter
        $('#restusre-signup-search').on('input', function() {
            searchTerm = $(this).val().trim().toLowerCase();
            currentPage = 1;
            updateSignupTable();
        });
        // Remove signup
        $('#restusre-signup-activity-table').on('click', '.restusre-remove-signup', function() {
            var id = $(this).data('id');
            if (!confirm('Remove this signup record?')) return;
            $.post(RESTUSRE_AJAX.ajax_url, {
                action: 'restusre_signup_activity_remove',
                nonce: RESTUSRE_AJAX.nonce,
                id: id
            }, function(response) {
                if (response.success) {
                    fetchSignupActivity();
                } else {
                    alert(response.data || 'Failed to remove record.');
                }
            });
        });
        // Initial load
        if ($('#restusre-signup-activity-table').length) {
            fetchSignupActivity();
        }
    })(jQuery);

    // EMAIL BLACKLIST PAGE STATE
    // Removed the legacy email blacklist page state and search/limit logic

    // On page load, always fetch and show email blacklist if table exists
    if ($('#restusre-email-blacklist-table').length) {
        fetchEmailBlacklist();
    }
    // EMAIL BLACKLIST SEARCH FILTER (AJAX-based)
    $('#restusre-blacklist-search').on('input', function() {
        var searchTerm = $(this).val().trim().toLowerCase();
        $.post(RESTUSRE_AJAX.ajax_url, {
            action: 'restusre_email_blacklist_list',
            nonce: RESTUSRE_AJAX.nonce
        }, function(response) {
            if (response.success) {
                var filtered = response.data.filter(function(item) {
                    return !searchTerm || (item.email && item.email.toLowerCase().includes(searchTerm));
                });
                renderEmailBlacklistTable(filtered);
            } else {
                renderEmailBlacklistTable([]);
            }
        });
    });
});