function checkPasswordStrength(passwordFieldId, meterId, textId) {
    const password = document.getElementById(passwordFieldId).value;
    const meter = document.getElementById(meterId);
    const text = document.getElementById(textId);

    let strength = 0;

    if (password.length >= 12) strength += 1;
    if (/[A-Z]/.test(password)) strength += 1;
    if (/[a-z]/.test(password)) strength += 1;
    if (/[0-9]/.test(password)) strength += 1;
    if (/[\W]/.test(password)) strength += 1;

    switch (strength) {
        case 0:
        case 1:
            meter.style.width = "20%";
            meter.style.backgroundColor = "red";
            text.innerHTML = "Very Weak";
            text.style.color = "red";
            break;
        case 2:
            meter.style.width = "40%";
            meter.style.backgroundColor = "orange";
            text.innerHTML = "Weak";
            text.style.color = "orange";
            break;
        case 3:
            meter.style.width = "60%";
            meter.style.backgroundColor = "gold";
            text.innerHTML = "Medium";
            text.style.color = "gold";
            break;
        case 4:
            meter.style.width = "80%";
            meter.style.backgroundColor = "lightgreen";
            text.innerHTML = "Strong";
            text.style.color = "lightgreen";
            break;
        case 5:
            meter.style.width = "100%";
            meter.style.backgroundColor = "green";
            text.innerHTML = "Very Strong";
            text.style.color = "green";
            break;
    }

    return strength; // Return the strength value to be used in form submission check
}

document.addEventListener('DOMContentLoaded', function() {
    // For register form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            const strength = checkPasswordStrength('customer_pwd', 'password-strength-meter', 'password-strength-text');
            if (strength < 3) {
                event.preventDefault();
                alert('Password is too weak. Please choose a stronger password.');
            }
        });

        document.getElementById('customer_pwd').addEventListener('keyup', function() {
            checkPasswordStrength('customer_pwd', 'password-strength-meter', 'password-strength-text');
        });
    }

    // For reset password form
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    if (resetPasswordForm) {
        resetPasswordForm.addEventListener('submit', function(event) {
            const strength = checkPasswordStrength('new_password', 'password-strength-meter', 'password-strength-text');
            if (strength < 3) {
                event.preventDefault();
                alert('Password is too weak. Please choose a stronger password.');
            }
        });

        document.getElementById('new_password').addEventListener('keyup', function() {
            checkPasswordStrength('new_password', 'password-strength-meter', 'password-strength-text');
        });
    }
});
