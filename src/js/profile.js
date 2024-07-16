function checkPasswordStrength() {
    const password = document.getElementById('new_pwd').value;
    const meter = document.getElementById('password-strength-meter');
    const text = document.getElementById('password-strength-text');

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
            meter.style.backgroundColor = "Gold";
            text.innerHTML = "Medium";
            text.style.color = "Gold";
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
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profileForm');
    form.addEventListener('submit', function(event) {
        const changePassword = document.getElementById('change_password').value;
        if (changePassword === 'yes') {
            const strength = checkPasswordStrength();
            if (strength < 3) {
                event.preventDefault();
                alert('Password is too weak. Please choose a stronger password.');
                return;
            }
        }

        event.preventDefault();
        const formData = new FormData(form);
        fetch('process/process_profile_combined.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Profile updated successfully. Thank you');
                location.reload(); // Reload the page to show the updated data
            } else {
                // Store errors in sessionStorage and reload the page
                sessionStorage.setItem('formErrors', JSON.stringify(data.errors));
                location.reload(); // Reload the page to show the errors and clear fields
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

function togglePasswordFields() {
    var passwordFields = document.getElementById("passwordFields");
    if (document.getElementById("change_password").value === "yes") {
        passwordFields.style.display = "block";
    } else {
        passwordFields.style.display = "none";
    }
}