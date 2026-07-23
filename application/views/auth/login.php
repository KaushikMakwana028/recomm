<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Recomm</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-navy: #0A2A4D;
            --primary-green: #4CAF50;
            --light-green: #6FCF63;
            --background: #F8F9FA;
            --white: #FFFFFF;
            --text: #1A1A1A;
        }
        
        body {
            background: linear-gradient(135deg, #4CAF50 0%, #0A2A4D 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }
        
        .login-card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-section h1 {
            color: var(--primary-navy);
            font-weight: 700;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .logo-section p {
            color: #6c757d;
            font-size: 14px;
        }
        
        .form-label {
            color: var(--text);
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }
        
        .btn-primary {
            background: var(--primary-green);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            width: 100%;
        }
        
        .btn-primary:hover {
            background: var(--light-green);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
        
        .btn-outline-secondary {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 10px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-outline-secondary:hover {
            background: #f8f9fa;
            border-color: var(--primary-green);
            color: var(--primary-green);
        }
        
        .otp-section {
            display: none;
        }
        
        .input-group-text {
            background: var(--background);
            border: 2px solid #e0e0e0;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }
        
        .input-group .form-control {
            border-left: none;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .timer {
            color: var(--primary-green);
            font-weight: 600;
            font-size: 14px;
        }
        
        #otpHelp {
            font-size: 13px;
            color: #6c757d;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="logo-section">
    <img src="<?php echo base_url('assets/recomm-logo.png'); ?>" alt="ReComm" class="logo-img">
    <p>Admin Panel Login</p>
</div>
<style>
    .logo-section {
    text-align: center;
}

.logo-section .logo-img {
    max-width: 130px;   /* adjust to taste */
    width: 100%;
    height: auto;
    display: block;
    margin: 0 auto 10px;
}

.logo-section p {
    margin: 0;
    color: #6b7280; /* adjust to match your theme */
    font-size: 14px;
}
</style>
        
        <div id="alertBox"></div>
        
        <!-- Mobile Number Section -->
        <div id="mobileSection">
            <form id="mobileForm">
                <div class="mb-3">
                    <label for="mobile" class="form-label">Mobile Number</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-mobile-alt"></i>
                        </span>
                        <input type="text" class="form-control" id="mobile" name="mobile" 
                               placeholder="Enter 10-digit mobile number" maxlength="10" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" id="sendOtpBtn">
                    <i class="fas fa-paper-plane me-2"></i> Send OTP
                </button>
            </form>
        </div>
        
        <!-- OTP Section -->
        <div id="otpSection" class="otp-section">
            <form id="otpForm">
                <input type="hidden" id="mobileHidden" name="mobile">
                
                <div class="mb-3">
                    <label for="otp" class="form-label">Enter OTP</label>
                    <input type="text" class="form-control" id="otp" name="otp" 
                           placeholder="Enter 6-digit OTP" maxlength="6" required>
                    <div id="otpHelp" class="form-text">
                        OTP sent to <strong id="displayMobile"></strong>
                        <span class="timer ms-2">(<span id="countdown">10:00</span>)</span>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary mb-2" id="verifyOtpBtn">
                    <i class="fas fa-check-circle me-2"></i> Verify OTP
                </button>
                
                <button type="button" class="btn btn-outline-secondary" id="backBtn">
                    <i class="fas fa-arrow-left me-2"></i> Change Number
                </button>
            </form>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    let countdownTimer;
    
    // Send OTP
    $('#mobileForm').submit(function(e) {
        e.preventDefault();
        
        const mobile = $('#mobile').val().trim();
        
        // Validate mobile
        if (!/^[0-9]{10}$/.test(mobile)) {
            showAlert('danger', 'Please enter a valid 10-digit mobile number');
            return;
        }
        
        $('#sendOtpBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Sending...');
        
        $.ajax({
            url: '<?= base_url("login/send-otp") ?>',
            type: 'POST',
            data: { mobile: mobile },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    showAlert('success', response.message);
                    $('#mobileHidden').val(mobile);
                    $('#displayMobile').text(mobile);
                    
                    // Show OTP section
                    setTimeout(function() {
                        $('#mobileSection').hide();
                        $('#otpSection').show();
                        startCountdown(600); // 10 minutes
                    }, 1000);
                } else {
                    showAlert('danger', response.message);
                    $('#sendOtpBtn').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Send OTP');
                }
            },
            error: function() {
                showAlert('danger', 'Something went wrong. Please try again.');
                $('#sendOtpBtn').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Send OTP');
            }
        });
    });
    
    // Verify OTP
    $('#otpForm').submit(function(e) {
        e.preventDefault();
        
        const mobile = $('#mobileHidden').val();
        const otp = $('#otp').val().trim();
        
        // Validate OTP
        if (!/^[0-9]{6}$/.test(otp)) {
            showAlert('danger', 'Please enter a valid 6-digit OTP');
            return;
        }
        
        $('#verifyOtpBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Verifying...');
        
        $.ajax({
            url: '<?= base_url("login/verify-otp") ?>',
            type: 'POST',
            data: { mobile: mobile, otp: otp },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    showAlert('success', response.message);
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 1000);
                } else {
                    showAlert('danger', response.message);
                    $('#verifyOtpBtn').prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i> Verify OTP');
                }
            },
            error: function() {
                showAlert('danger', 'Something went wrong. Please try again.');
                $('#verifyOtpBtn').prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i> Verify OTP');
            }
        });
    });
    
    // Back button
    $('#backBtn').click(function() {
        $('#otpSection').hide();
        $('#mobileSection').show();
        $('#mobile').val('');
        $('#otp').val('');
        $('#sendOtpBtn').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Send OTP');
        clearInterval(countdownTimer);
        $('#alertBox').empty();
    });
    
    // Show alert
    function showAlert(type, message) {
        const alertHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('#alertBox').html(alertHTML);
    }
    
    // Countdown timer
    function startCountdown(seconds) {
        let remaining = seconds;
        
        countdownTimer = setInterval(function() {
            const minutes = Math.floor(remaining / 60);
            const secs = remaining % 60;
            
            $('#countdown').text(`${minutes}:${secs < 10 ? '0' : ''}${secs}`);
            
            if (remaining <= 0) {
                clearInterval(countdownTimer);
                showAlert('warning', 'OTP expired. Please request a new one.');
                $('#backBtn').click();
            }
            
            remaining--;
        }, 1000);
    }
    
    // Only allow numbers in mobile and OTP fields
    $('#mobile, #otp').on('keypress', function(e) {
        return e.charCode >= 48 && e.charCode <= 57;
    });
});
</script>

</body>
</html>