const step1 = document.getElementById("step-1");
const step2 = document.getElementById("step-2");
const step3 = document.getElementById("step-3");

function validateStep1Form() {
  const form = document.getElementById("form-step-1");
  clearErrors(form);

  const phone = document.getElementById("phone");
  let isValid = true;

  if (isEmpty(phone.value)) {
    showError("phone", "Phone Number is required");
    isValid = false;
  } else if (!/^\+?6?01[0-9]{8}$/.test(phone.value)) {
    showError("phone", "Please enter a valid phone number");
    isValid = false;
  }
  return isValid;
}

function validateStep2Form() {
  const form = document.getElementById("form-step-2");
  clearErrors(form);

  const otp = document.getElementById("otp");
  let isValid = true;

  if (isEmpty(otp.value)) {
    showError("otp", "OTP is required");
    isValid = false;
  } else if (!/^\d{6}$/.test(otp.value)) {
    showError("otp", "OTP must be exactly 6 digits");
    isValid = false;
  }
  return isValid;
}

function validateStep3Form() {
  const form = document.getElementById("form-step-3");
  clearErrors(form);

  const newPass = document.getElementById("newPassword");
  const confirmPass = document.getElementById("confirmPassword");
  let isValid = true;

  if (isEmpty(newPass.value)) {
    showError("newPassword", "New password is required");
    isValid = false;
  } else if (newPass.value.length < 8) {
    showError("newPassword", "Password must be at least 8 characters");
    isValid = false;
  }

  if (isEmpty(confirmPass.value)) {
    showError("confirmPassword", "Please confirm your password");
    isValid = false;
  } else if (newPass.value !== confirmPass.value) {
    showError("confirmPassword", "Passwords do not match");
    isValid = false;
  }

  return isValid;
}

function animateToStep2() {
  step1.style.left = "-510px";
  step1.style.opacity = "0";
  step1.style.visibility = "hidden";
  step2.style.right = "5px";
  step2.style.left = "auto";
  step2.style.opacity = "1";
  step2.style.visibility = "visible";
}

function animateToStep3() {
  step2.style.right = "520px";
  step2.style.opacity = "0";
  step2.style.visibility = "hidden";
  step3.style.right = "5px";
  step3.style.left = "auto";
  step3.style.opacity = "1";
  step3.style.visibility = "visible";
}

function backToStep1(e) {
  if (e) e.preventDefault();
  step1.style.left = "4px";
  step1.style.opacity = "1";
  step1.style.visibility = "visible";
  step2.style.right = "-520px";
  step2.style.opacity = "0";
  step2.style.visibility = "hidden";
  clearErrors(document.getElementById("form-step-2"));
}

document.getElementById("form-step-1").addEventListener("submit", function (e) {
  e.preventDefault();

  if (!validateStep1Form()) return;

  const btn = this.querySelector(".submit");
  const oldText = btn.value;

  const phoneVal = document.getElementById("phone").value;

  btn.value = "Sending...";
  btn.disabled = true;

  fetch(window.location.href, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "action=sendOtp&phone=" + encodeURIComponent(phoneVal),
  })
    .then((res) => res.text())
    .then((text) => {
      console.log("Response Text:", text); // Debugging line
      try {
        return JSON.parse(text);
      } catch (e) {
        throw new Error("Server returned invalid JSON: " + text);
      }
    })
    .then((data) => {
      if (data.success) {
        setTimeout(() => {
          animateToStep2();
          btn.value = oldText;
          btn.disabled = false;
        }, 1000);
      } else {
        alert(data.message || "Failed to send OTP");
      }
    })
    .catch((err) => {
      console.log(err);
      alert("Server error");
    })
    .finally(() => {
      btn.value = oldText;
      btn.disabled = false;
    });
});

document.getElementById("form-step-2").addEventListener("submit", function (e) {
  e.preventDefault();

  if (!validateStep2Form()) return;

  const btn = this.querySelector(".submit");
  const originalText = btn.value;
  const otpValue = document.getElementById("otp").value;

  btn.value = "Verifying...";
  btn.disabled = true;

  fetch(window.location.href, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "action=checkOtp&otp=" + encodeURIComponent(otpValue),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        setTimeout(() => {
          animateToStep3();
          btn.value = originalText;
          btn.disabled = false;
        }, 500);
      } else {
        showError("otp", data.message || "Invalid OTP");
        btn.value = originalText;
        btn.disabled = false;
      }
    })
    .catch((err) => {
      console.error(err);
      alert("Server error");
      btn.value = originalText;
      btn.disabled = false;
    });
});

document.getElementById("form-step-3").addEventListener("submit", function (e) {
  e.preventDefault();

  if (!validateStep3Form()) return;
  const btn = this.querySelector(".submit");
  const originalText = btn.value;
  const newPass = document.getElementById("newPassword").value;
  const confirmPass = document.getElementById("confirmPassword").value;

  btn.value = "Resetting...";
  btn.disabled = true;
  fetch(window.location.href, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body:
      "action=resetPassword&newPassword=" +
      encodeURIComponent(newPass) +
      "&confirmPassword=" +
      encodeURIComponent(confirmPass),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        alert("Password reset successfully! Redirecting to login...");
        window.location.href = "/app/views/security/signIn.php";
      } else {
        showError("confirmPassword", data.message || "Reset failed");
        btn.value = originalText;
        btn.disabled = false;
      }
    })
    .catch((err) => {
      console.error(err);
      alert("Server error");
      btn.value = originalText;
      btn.disabled = false;
    });
});
