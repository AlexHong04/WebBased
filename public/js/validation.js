// validation.js

function isEmpty(value) {
  return !value || value.trim() === "";
}

function hasNumber(value) {
  return /\d/.test(value);
}

function isFiveDigitPostcode(value) {
  return /^\d{5}$/.test(value);
}

function isValidEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

function checkStrength(password) {
    let score = 0;

    // 1. Check Length (8+)
    if (password.length >= 8) score++;

    // 2. Check Number
    if (/\d/.test(password)) score++;

    // 3. Check Uppercase
    if (/[A-Z]/.test(password)) score++;

    // 4. Check Special Char
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) score++;

    return { score: score };
}

function validateMalaysianPhone(phone, errorId) {
  const cleanPhone = phone.replace(/-/g, "");

  if (!/^\d+$/.test(cleanPhone)) {
    showError(errorId, "Phone must contain only numbers and dashes");
    return false;
  }

  if (cleanPhone.startsWith("011")) {
    if (cleanPhone.length !== 11) {
      showError(errorId, "011 numbers must have 11 digits (e.g. 011-12345678)");
      return false;
    }
  } else if (/^01[02-9]/.test(cleanPhone)) {
    if (cleanPhone.length !== 10) {
      showError(
        errorId,
        "012–019 numbers must have 10 digits (e.g. 012-3456789)"
      );
      return false;
    }
  } else {
    showError(errorId, "Invalid prefix. Must start with 01X");
    return false;
  }

  return true;
}

// 1. Helper to show error on a specific input
function showError(inputId, message) {
  const input = document.getElementById(inputId);
  if (!input) return;

  let errorSmall = document.getElementById(inputId + "Error");

  if (!errorSmall) {
    errorSmall = document.getElementById(inputId.replace("Input", "Error"));
  }

  if (errorSmall) {
    input.classList.add("is-invalid");
    errorSmall.textContent = message;
    errorSmall.style.display = "block";

    // Specific logic for phone hint
    if (inputId === "phoneInput") {
      const hint = document.getElementById("phoneFormatHint");
      if (hint) hint.style.display = "none";
    }
  }
}

// 2. Helper to clear all errors (Moved here so validation works standalone)
function clearErrors(form) {
  // Clear text content of error messages
  form.querySelectorAll(".error-message").forEach((el) => {
    el.textContent = "";
    el.style.display = "none";
  });
  // Remove invalid red border
  form.querySelectorAll(".form-control").forEach((input) => {
    input.classList.remove("is-invalid");
  });

  // Show phone hint again if it exists
  const hint = document.getElementById("phoneFormatHint");
  if (hint) hint.style.display = "block";

  // Clear general error if it exists
  const generalErr = document.getElementById("generalErrorMsg");
  if (generalErr) generalErr.innerText = "";
}

// Main Validation Function
function validateAddressForm(form) {
  clearErrors(form); // Now this function definitely exists!
  let isValid = true;

  // Mapping inputs to their error IDs
  const fields = [
    { value: form.ReceiverName.value, errorId: "nameInput", label: "Name" },
    { value: form.phoneNumber.value, errorId: "phoneInput", label: "Phone" },
    { value: form.street_line.value, errorId: "streetInput", label: "Street" },
    { value: form.postCode.value, errorId: "postcodeInput", label: "Postcode" },
    { value: form.City.value, errorId: "cityInput", label: "City" },
    { value: form.State.value, errorId: "stateInput", label: "State" },
  ];

  // Required check
  fields.forEach((f) => {
    if (isEmpty(f.value)) {
      showError(f.errorId, "Required");
      isValid = false;
    }
  });

  if (!isValid) return false;

  // Specific Format Rules
  if (hasNumber(form.ReceiverName.value)) {
    showError("nameInput", "Name cannot contain numbers");
    isValid = false;
  }

  if (hasNumber(form.City.value)) {
    showError("cityInput", "City cannot contain numbers");
    isValid = false;
  }

  if (hasNumber(form.State.value)) {
    showError("stateInput", "State cannot contain numbers");
    isValid = false;
  }

  // Phone Validation
  if (!validateMalaysianPhone(form.phoneNumber.value, "phoneInput")) {
    isValid = false;
  }

  // Postcode Validation
  if (!isFiveDigitPostcode(form.postCode.value)) {
    showError("postcodeInput", "Must be 5 digits");
    isValid = false;
  }

  return isValid;
}

function validatePasswordForm(form) {
  clearErrors(form);
  let isValid = true;

  // Get input elements
  const currentPass = form.querySelector('[name="currentPassword"]');
  const newPass = form.querySelector('[name="newPassword"]');
  const confirmPass = form.querySelector('[name="confirmPassword"]');

  // 1. Validate Current Password
  if (isEmpty(currentPass.value)) {
    showError(currentPass.id, "Current password is required");
    isValid = false;
  }

  // 2. Validate New Password
  if (isEmpty(newPass.value)) {
    showError(newPass.id, "New password is required");
    isValid = false;
  } else if (newPass.value.length < 8) {
    showError(newPass.id, "Password must be at least 8 characters");
    isValid = false;
  }

  // 3. Validate Confirm Password
  if (isEmpty(confirmPass.value)) {
    showError(confirmPass.id, "Please confirm your new password");
    isValid = false;
  } else if (confirmPass.value !== newPass.value) {
    showError(confirmPass.id, "Passwords do not match");
    isValid = false;
  }

  return isValid;
}

function validateLoginForm(form) {
  clearErrors(form);
  let isValid = true;

  // Get Inputs
  const emailInput = form.querySelector("#email");
  const passwordInput = form.querySelector("#password");

  // Safety Check
  if (!emailInput || !passwordInput) {
    console.error("Login inputs not found");
    return false;
  }

  // 1. Validate Email
  if (isEmpty(emailInput.value)) {
    showError("email", "Email is required");
    isValid = false;
  } else if (!isValidEmail(emailInput.value)) {
    showError("email", "Invalid email format");
    isValid = false;
  }

  // 2. Validate Password
  if (isEmpty(passwordInput.value)) {
    showError("password", "Password is required");
    isValid = false;
  }

  return isValid;
}

function validateSignUpForm(form) {
    clearErrors(form);
    let isValid = true;

    // Get Inputs
    const firstName = form.querySelector('#firstName');
    const lastName = form.querySelector('#lastName');
    const email = form.querySelector('#email');
    const gender = form.querySelector('#gender');
    const password = form.querySelector('#password');
    const confirmPass = form.querySelector('#confirm_password');

    // 1. Basic Fields
    if (isEmpty(firstName.value)) { showError('firstName', 'First name is required'); isValid = false; }
    if (isEmpty(lastName.value)) { showError('lastName', 'Last name is required'); isValid = false; }
    
    // 2. Email
    if (isEmpty(email.value)) { 
        showError('email', 'Email is required'); 
        isValid = false; 
    } else if (!isValidEmail(email.value)) {
        showError('email', 'Invalid email format');
        isValid = false;
    }

    // 3. Gender
    if (isEmpty(gender.value)) { showError('gender', 'Please select a gender'); isValid = false; }

    // 4. Password Presence
    if (isEmpty(password.value)) { showError('password', 'Password is required'); isValid = false; }
    
    // 5. Confirm Password
    if (isEmpty(confirmPass.value)) {
        showError('confirm_password', 'Please confirm your password');
        isValid = false;
    } else if (confirmPass.value !== password.value) {
        showError('confirm_password', 'Passwords do not match');
        isValid = false;
    }

    //  Check Password Strength Requirements (Strict Mode)
    const strength = checkStrength(password.value);
    if (strength.score < 4) {
        showError('password', 'Password is too weak. Please meet all requirements below.');
        isValid = false;
    }


    return isValid;
}

function initPasswordStrength() {
    const passwordInput = document.getElementById('password');
    if (!passwordInput) return;

    const bars = document.querySelectorAll('.bar');
    const reqLength = document.getElementById('req-length');
    const reqNum = document.getElementById('req-num');
    const reqUpper = document.getElementById('req-upper');
    const reqSpecial = document.getElementById('req-special');

    passwordInput.addEventListener('input', function() {
        const val = this.value;
        let score = 0;

        // 1. Check Length (8+)
        if (val.length >= 8) {
            reqLength.classList.add('valid');
            score++;
        } else {
            reqLength.classList.remove('valid');
        }

        // 2. Check Number
        if (/\d/.test(val)) {
            reqNum.classList.add('valid');
            score++;
        } else {
            reqNum.classList.remove('valid');
        }

        // 3. Check Uppercase
        if (/[A-Z]/.test(val)) {
            reqUpper.classList.add('valid');
            score++;
        } else {
            reqUpper.classList.remove('valid');
        }

        // 4. Check Special Char
        if (/[!@#$%^&*(),.?":{}|<>]/.test(val)) {
            reqSpecial.classList.add('valid');
            score++;
        } else {
            reqSpecial.classList.remove('valid');
        }

        // Update Bars
        bars.forEach((bar, index) => {
            if (index < score) {
                bar.classList.add('active');
            } else {
                bar.classList.remove('active');
            }
        });
    });
}