/**
 * TMV Main JavaScript - Trademark Verification System
 * Handles form submissions, verification, and UI interactions
 */
(function($) {
    'use strict';

    // Application Form Handler
    $(document).on('submit', '#tmv-application-form', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $btn = $form.find('.tmv-btn-primary');
        var $btnText = $btn.find('.tmv-btn-text');
        var $btnLoader = $btn.find('.tmv-btn-loader');
        var $response = $('#tmv-form-response');
        
        // Disable button
        $btn.prop('disabled', true);
        $btnText.hide();
        $btnLoader.show();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: tmvAjax.url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $response.removeClass('tmv-error').addClass('tmv-success')
                        .html('<strong>&#10003; Success!</strong><br>' + response.data.message)
                        .fadeIn();
                    $form[0].reset();
                } else {
                    $response.removeClass('tmv-success').addClass('tmv-error')
                        .html('<strong>&#10007; Error:</strong> ' + response.data.message)
                        .fadeIn();
                }
            },
            error: function() {
                $response.removeClass('tmv-success').addClass('tmv-error')
                    .html('<strong>&#10007; Error:</strong> Server error. Please try again.')
                    .fadeIn();
            },
            complete: function() {
                $btn.prop('disabled', false);
                $btnText.show();
                $btnLoader.hide();
            }
        });
    });

    // Logo Preview
    $(document).on('change', '#tmv-logo-upload', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#tmv-logo-preview').html('<img src="' + e.target.result + '" alt="Logo Preview" />');
            };
            reader.readAsDataURL(file);
        }
    });

    // Verification Form Handler
    $(document).on('submit', '#tmv-verify-form', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var searchCode = $form.find('#tmv-search-input').val().trim();
        var $loading = $('#tmv-verify-loading');
        var $result = $('#tmv-verify-result');
        var $card = $('.tmv-verify-card').first();
        
        if (!searchCode) return;
        
        // Show loading
        $card.fadeOut(200);
        $loading.fadeIn(300);
        $result.hide();
        
        $.ajax({
            url: tmvAjax.url,
            type: 'POST',
            data: {
                action: 'tmv_verify_trademark',
                tmv_nonce: $form.find('[name="tmv_nonce"]').val(),
                tmv_search_code: searchCode
            },
            success: function(response) {
                setTimeout(function() {
                    $loading.fadeOut(200, function() {
                        if (response.success) {
                            renderVerifyResult(response.data);
                        } else {
                            renderVerifyError(response.data.message);
                        }
                        $result.fadeIn(400).addClass('tmv-fade-in');
                    });
                }, 1500); // Simulate secure retrieval delay
            },
            error: function() {
                setTimeout(function() {
                    $loading.fadeOut(200, function() {
                        renderVerifyError('Connection error. Please try again.');
                        $result.fadeIn(400);
                    });
                }, 1000);
            }
        });
    });

    function renderVerifyResult(data) {
        var html = '<div class="tmv-result-card tmv-flip-card tmv-holographic">';
        html += '<div class="tmv-result-header">';
        html += '<h3>&#10003; Trademark Verified</h3>';
        html += '<p>This trademark is officially registered and active</p>';
        html += '</div>';
        html += '<div class="tmv-result-body">';
        
        // Logo Highlight Section
        if (data.logo_url) {
            html += '<div class="tmv-logo-highlight">';
            html += '<img src="' + data.logo_url + '" alt="Brand Logo" />';
            html += '<div class="tmv-brand-name">' + escapeHtml(data.logo_text || data.owner) + '</div>';
            html += '<div class="tmv-owner-name">' + escapeHtml(data.owner) + '</div>';
            html += '</div>';
        } else if (data.logo_text) {
            html += '<div class="tmv-logo-highlight">';
            html += '<div class="tmv-brand-name">' + escapeHtml(data.logo_text) + '</div>';
            html += '<div class="tmv-owner-name">' + escapeHtml(data.owner) + '</div>';
            html += '</div>';
        }
        
        // Certificate Image
        if (data.certificate_jpg) {
            html += '<div class="tmv-cert-image">';
            html += '<img src="' + data.certificate_jpg + '" alt="Certificate" />';
            html += '</div>';
        }
        
        // Details Grid
        html += '<div class="tmv-details-grid">';
        html += detailItem('TM Number', data.tm_number);
        html += detailItem('Certificate', data.cert_title);
        html += detailItem('Owner', data.owner);
        html += detailItem('Class', data.class);
        html += detailItem('Registration Date', data.reg_date);
        html += detailItem('Sealing Date', data.sealing_date);
        html += detailItem('Approved Date', data.approved_date);
        html += detailItem('Expiry Date', data.expiry_date);
        html += detailItem('Service', data.service_desc);
        html += detailItem('Signatory', data.signatory);
        html += detailItem('Designation', data.designation);
        html += detailItem('Status', '<span class="tmv-status-3d verified">&#10003; Verified</span>');
        html += '</div>';
        
        // QR Code Section
        html += '<div class="tmv-qr-section" style="text-align:center;margin-top:20px;padding:15px;border-top:1px solid #eee;">';
        html += '<p style="font-size:12px;color:#666;margin-bottom:8px;">Scan to Verify</p>';
        html += '<div id="tmv-result-qr" style="display:inline-block;"></div>';
        html += '</div>';
        
        html += '</div></div>';
        
        // Search Again button
        html += '<div style="text-align:center;margin-top:24px;">';
        html += '<button onclick="location.reload()" class="tmv-btn tmv-btn-primary tmv-3d-btn" style="padding:12px 32px;font-size:14px;">Search Again</button>';
        html += '</div>';
        
        $('#tmv-verify-result').html(html);
        
        // Generate QR code after DOM is updated
        if (typeof QRCode !== 'undefined' && data.verify_code) {
            var verifyUrl = tmvAjax.verify_url + '?code=' + data.verify_code;
            new QRCode(document.getElementById('tmv-result-qr'), {
                text: verifyUrl,
                width: 150,
                height: 150
            });
        } else if (data.verify_code) {
            var verifyUrl = tmvAjax.verify_url + '?code=' + data.verify_code;
            var qrContainer = document.getElementById('tmv-result-qr');
            if (qrContainer) {
                qrContainer.innerHTML = '<a href="' + verifyUrl + '" target="_blank" style="word-break:break-all;font-size:12px;color:#1a5c3a;">' + verifyUrl + '</a>';
            }
        }
    }

    function renderVerifyError(message) {
        var html = '<div class="tmv-result-error tmv-fade-in">';
        html += '<div class="tmv-error-icon">&#9888;</div>';
        html += '<h3>Not Found</h3>';
        html += '<p>' + escapeHtml(message) + '</p>';
        html += '<div style="margin-top:20px;">';
        html += '<button onclick="location.reload()" class="tmv-btn tmv-btn-primary tmv-3d-btn" style="padding:12px 32px;font-size:14px;">Try Again</button>';
        html += '</div>';
        html += '</div>';
        
        $('#tmv-verify-result').html(html);
    }

    function detailItem(label, value) {
        if (!value) return '';
        return '<div class="tmv-detail-item"><label>' + label + '</label><span>' + value + '</span></div>';
    }

    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    // 3D Card Tilt Effect
    $(document).on('mousemove', '.tmv-3d-card', function(e) {
        if (window.innerWidth < 768) return;
        
        var $card = $(this);
        var rect = this.getBoundingClientRect();
        var x = e.clientX - rect.left;
        var y = e.clientY - rect.top;
        var centerX = rect.width / 2;
        var centerY = rect.height / 2;
        var rotateX = (y - centerY) / 30;
        var rotateY = (centerX - x) / 30;
        
        $card.css('transform', 'perspective(1000px) rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg)');
    });

    $(document).on('mouseleave', '.tmv-3d-card', function() {
        $(this).css('transform', 'perspective(1000px) rotateX(0deg) rotateY(0deg)');
    });

    // ===== AUTH FORM HANDLERS =====

    // Login Form Handler
    $(document).on('submit', '#tmv-login-form', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $form.find('.tmv-btn-primary');
        var $btnText = $btn.find('.tmv-btn-text');
        var $btnLoader = $btn.find('.tmv-btn-loader');
        var $response = $('#tmv-login-response');

        $btn.prop('disabled', true);
        $btnText.hide();
        $btnLoader.show();
        $response.hide();

        $.ajax({
            url: tmvAjax.url,
            type: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    $response.removeClass('tmv-error').addClass('tmv-success')
                        .html('<strong>&#10003;</strong> ' + response.data.message)
                        .fadeIn();
                    if (response.data.redirect) {
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 1000);
                    }
                } else {
                    $response.removeClass('tmv-success').addClass('tmv-error')
                        .html('<strong>&#10007;</strong> ' + response.data.message)
                        .fadeIn();
                }
            },
            error: function() {
                $response.removeClass('tmv-success').addClass('tmv-error')
                    .html('<strong>&#10007;</strong> Server error. Please try again.')
                    .fadeIn();
            },
            complete: function() {
                $btn.prop('disabled', false);
                $btnText.show();
                $btnLoader.hide();
            }
        });
    });

    // Register Form Handler
    $(document).on('submit', '#tmv-register-form', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $form.find('.tmv-btn-primary');
        var $btnText = $btn.find('.tmv-btn-text');
        var $btnLoader = $btn.find('.tmv-btn-loader');
        var $response = $('#tmv-register-response');

        // Client-side password validation
        var password = $form.find('[name="tmv_password"]').val();
        var confirm = $form.find('[name="tmv_password_confirm"]').val();

        if (password !== confirm) {
            $response.removeClass('tmv-success').addClass('tmv-error')
                .html('<strong>&#10007;</strong> Passwords do not match.')
                .fadeIn();
            return;
        }

        if (password.length < 6) {
            $response.removeClass('tmv-success').addClass('tmv-error')
                .html('<strong>&#10007;</strong> Password must be at least 6 characters.')
                .fadeIn();
            return;
        }

        $btn.prop('disabled', true);
        $btnText.hide();
        $btnLoader.show();
        $response.hide();

        $.ajax({
            url: tmvAjax.url,
            type: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    $response.removeClass('tmv-error').addClass('tmv-success')
                        .html('<strong>&#10003;</strong> ' + response.data.message)
                        .fadeIn();
                    $form[0].reset();
                    if (response.data.redirect) {
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 2000);
                    }
                } else {
                    $response.removeClass('tmv-success').addClass('tmv-error')
                        .html('<strong>&#10007;</strong> ' + response.data.message)
                        .fadeIn();
                }
            },
            error: function() {
                $response.removeClass('tmv-success').addClass('tmv-error')
                    .html('<strong>&#10007;</strong> Server error. Please try again.')
                    .fadeIn();
            },
            complete: function() {
                $btn.prop('disabled', false);
                $btnText.show();
                $btnLoader.hide();
            }
        });
    });

    // Password Reset Form Handler
    $(document).on('submit', '#tmv-reset-form', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $form.find('.tmv-btn-primary');
        var $btnText = $btn.find('.tmv-btn-text');
        var $btnLoader = $btn.find('.tmv-btn-loader');
        var $response = $('#tmv-reset-response');

        $btn.prop('disabled', true);
        $btnText.hide();
        $btnLoader.show();
        $response.hide();

        $.ajax({
            url: tmvAjax.url,
            type: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    $response.removeClass('tmv-error').addClass('tmv-success')
                        .html('<strong>&#10003;</strong> ' + response.data.message)
                        .fadeIn();
                    $form[0].reset();
                } else {
                    $response.removeClass('tmv-success').addClass('tmv-error')
                        .html('<strong>&#10007;</strong> ' + response.data.message)
                        .fadeIn();
                }
            },
            error: function() {
                $response.removeClass('tmv-success').addClass('tmv-error')
                    .html('<strong>&#10007;</strong> Server error. Please try again.')
                    .fadeIn();
            },
            complete: function() {
                $btn.prop('disabled', false);
                $btnText.show();
                $btnLoader.hide();
            }
        });
    });

})(jQuery);
