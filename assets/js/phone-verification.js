// Phone Verification System for Checkout

let phoneVerificationState = {
    phoneNumber: '',
    otpSent: false,
    otpExpireTime: 0,
    timerInterval: null,
    modalOpen: false
};

document.addEventListener('DOMContentLoaded', function() {
    const sendOtpBtn = document.getElementById('sendOtpBtn');
    const verifyOtpBtn = document.getElementById('verifyOtpBtn');
    const resendOtpBtn = document.getElementById('resendOtpBtn');
    const phoneInput = document.getElementById('phoneInput');
    const otpInput = document.getElementById('otpInput');
    const closeVerificationModal = document.getElementById('closeVerificationModal');

    if (sendOtpBtn) {
        sendOtpBtn.addEventListener('click', sendPhoneOTP);
    }

    if (verifyOtpBtn) {
        verifyOtpBtn.addEventListener('click', verifyPhoneOTP);
    }

    if (resendOtpBtn) {
        resendOtpBtn.addEventListener('click', function() {
            sendPhoneOTP();
        });
    }

    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            // Auto-format phone number
            let value = this.value.replace(/\D/g, '');
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            this.value = value;
        });
    }

    if (otpInput) {
        otpInput.addEventListener('input', function() {
            // Only allow numbers
            this.value = this.value.replace(/\D/g, '');
            // Auto-submit when 6 digits entered
            if (this.value.length === 6) {
                // You could auto-submit here if desired
            }
        });
    }

    if (closeVerificationModal) {
        closeVerificationModal.addEventListener('click', closeVerificationModalWindow);
    }

    const modal = document.getElementById('phoneVerificationModal');
    if (modal) {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeVerificationModalWindow();
            }
        });
    }
});

function openVerificationModal() {
    const modal = document.getElementById('phoneVerificationModal');
    if (!modal) {
        return;
    }

    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('verification-modal-open');
    phoneVerificationState.modalOpen = true;
}

function closeVerificationModalWindow() {
    const modal = document.getElementById('phoneVerificationModal');
    if (!modal) {
        return;
    }

    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('verification-modal-open');
    phoneVerificationState.modalOpen = false;
}

async function sendPhoneOTP() {
    const phoneInput = document.getElementById('phoneInput');
    const phone = phoneInput.value.trim();

    // Validate phone format
    if (!phone || !/^09\d{9}$/.test(phone)) {
        showVerificationMessage('Invalid phone number format (use 09xxxxxxxxx)', 'error');
        return;
    }

    const sendOtpBtn = document.getElementById('sendOtpBtn');
    const messageDiv = document.getElementById('verificationMessage');
    
    sendOtpBtn.disabled = true;
    sendOtpBtn.textContent = 'Sending...';

    try {
        const formData = new FormData();
        formData.append('phone', phone);

        const response = await fetch('api/send-phone-verification.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            phoneVerificationState.phoneNumber = phone;
            phoneVerificationState.otpSent = true;
            phoneInput.disabled = true;

            openVerificationModal();
            document.getElementById('resendOtpBtn').style.display = 'block';
            document.getElementById('verifyOtpBtn').style.display = 'inline-flex';
            document.getElementById('verifyOtpBtn').disabled = false;
            document.getElementById('verifyOtpBtn').textContent = 'Verify Code';
            document.getElementById('otpInput').value = '';
            document.getElementById('otpInput').disabled = false;

            // Start timer
            startOTPTimer();

            showVerificationMessage('Verification code sent to your email!', 'success');
            sendOtpBtn.textContent = 'Verify';
            sendOtpBtn.disabled = false;
        } else {
            showVerificationMessage(data.message || 'Failed to send verification code', 'error');
            sendOtpBtn.disabled = false;
            sendOtpBtn.textContent = 'Send Verification Code';
        }
    } catch (error) {
        showVerificationMessage('Error: ' + error.message, 'error');
        sendOtpBtn.disabled = false;
        sendOtpBtn.textContent = 'Send Verification Code';
    }
}

async function verifyPhoneOTP() {
    const otpInput = document.getElementById('otpInput');
    const otp = otpInput.value.trim();

    if (!otp || !/^\d{6}$/.test(otp)) {
        showVerificationMessage('Please enter a valid 6-digit code', 'error');
        return;
    }

    const verifyOtpBtn = document.getElementById('verifyOtpBtn');
    verifyOtpBtn.disabled = true;
    verifyOtpBtn.textContent = 'Verifying...';

    try {
        const response = await fetch('api/verify-phone-code.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ code: otp })
        });

        const data = await response.json();

        if (data.success) {
            showVerificationMessage('Phone verified successfully! ✓', 'success');
            
            // Clear timer
            if (phoneVerificationState.timerInterval) {
                clearInterval(phoneVerificationState.timerInterval);
            }

            const phoneField = document.getElementById('phoneInput');
            const sendOtpBtn = document.getElementById('sendOtpBtn');
            if (phoneField) {
                phoneField.disabled = true;
                phoneField.readOnly = true;
            }
            if (sendOtpBtn) {
                sendOtpBtn.style.display = 'none';
            }

            // Reload page after 1.5 seconds to ensure session is properly saved
            setTimeout(() => {
                window.location.reload();
            }, 1500);

        } else {
            showVerificationMessage(data.message || 'Verification failed', 'error');
            verifyOtpBtn.disabled = false;
            verifyOtpBtn.textContent = 'Verify Code';
        }
    } catch (error) {
        showVerificationMessage('Error: ' + error.message, 'error');
        verifyOtpBtn.disabled = false;
        verifyOtpBtn.textContent = 'Verify Code';
    }
}

function startOTPTimer() {
    phoneVerificationState.otpExpireTime = Date.now() + (10 * 60 * 1000); // 10 minutes

    if (phoneVerificationState.timerInterval) {
        clearInterval(phoneVerificationState.timerInterval);
    }

    const updateTimer = () => {
        const now = Date.now();
        const remainingMs = phoneVerificationState.otpExpireTime - now;

        if (remainingMs <= 0) {
            clearInterval(phoneVerificationState.timerInterval);
            document.getElementById('timerDisplay').textContent = '0:00';
            document.getElementById('timerDisplay').style.color = '#dc3545';
            document.getElementById('otpInput').disabled = true;
            document.getElementById('verifyOtpBtn').disabled = true;
            showVerificationMessage('Code expired. Please request a new one.', 'error');
            document.getElementById('resendOtpBtn').style.display = 'block';
            document.getElementById('verifyOtpBtn').style.display = 'none';
            phoneVerificationState.otpSent = false;
            return;
        }

        const minutes = Math.floor(remainingMs / 60000);
        const seconds = Math.floor((remainingMs % 60000) / 1000);
        document.getElementById('timerDisplay').textContent = 
            `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        // Change color to red when less than 2 minutes
        if (remainingMs < 120000) {
            document.getElementById('timerDisplay').style.color = '#dc3545';
        }
    };

    updateTimer(); // Initial call
    phoneVerificationState.timerInterval = setInterval(updateTimer, 1000);
}

function showVerificationMessage(message, type) {
    const messageDiv = document.getElementById('verificationMessage');
    if (!messageDiv) {
        return;
    }

    messageDiv.textContent = message;
    messageDiv.className = 'verification-message ' + type;
}

// Handle reset phone query parameter
function handleResetPhone() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('reset_phone')) {
        // Reset session on server side - you'll need to add this
        window.location.href = 'checkout.php';
    }
}

handleResetPhone();
