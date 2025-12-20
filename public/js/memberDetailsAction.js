document.addEventListener("DOMContentLoaded", function () {
  const hiddenInput = document.getElementById("isBlockedInput");
  const successPopup = document.getElementById("successPopup");
  const errorPopup = document.getElementById("customPopup");

  if (successPopup) {
    setTimeout(() => {
      successPopup.classList.remove("show");
    }, 3000);
  }

  if (errorPopup) {
    setTimeout(() => {
      errorPopup.classList.remove("show");
    }, 3000);
  }

  const statusDisplay = document.getElementById("statusDisplay");
  const statusModal = document.getElementById("statusModal");
  const cancelBtn = document.getElementById("cancelBtn");
  const confirmBtn = document.getElementById("confirmBtn");
  const memberForm = document.querySelector("form");

  let newStatusValue = hiddenInput.value; // current status

  // When user clicks the status display
  statusDisplay.addEventListener("click", () => {
    // Toggle status value
    newStatusValue = hiddenInput.value == "1" ? "0" : "1";
    const statusText = newStatusValue == "1" ? "Blocked" : "Normal";

    document.getElementById(
      "modalMessage"
    ).textContent = `Are you sure you want to change member status to "${statusText}"?`;

    // Show modal
    statusModal.style.display = "flex";
  });

  // Cancel button
  cancelBtn.addEventListener("click", () => {
    statusModal.style.display = "none";
  });

  // Confirm button
  confirmBtn.addEventListener("click", () => {
    // Update hidden input
    hiddenInput.value = newStatusValue;

    // Update status display UI
    statusDisplay.textContent = newStatusValue == "1" ? "Blocked" : "Normal";
    statusDisplay.classList.toggle("blocked", newStatusValue == "1");
    statusDisplay.classList.toggle("normal", newStatusValue != "1");

    // Close modal
    statusModal.style.display = "none";

    // Submit form automatically
    memberForm.submit();
  });

  // Tab switching
  document.querySelectorAll(".tab").forEach((tab) => {
    tab.addEventListener("click", function (e) {
      e.preventDefault();
      document
        .querySelectorAll(".tab")
        .forEach((t) => t.classList.remove("active"));
      tab.classList.add("active");
      document
        .querySelectorAll(".profile-form-container")
        .forEach((sec) => (sec.style.display = "none"));
      document.getElementById(tab.dataset.target).style.display = "block";
    });
  });

  // Profile pic preview
  const fileInput = document.getElementById("profile-pic-input");
  const previewImg = document.getElementById("profile-pic-preview");

  fileInput.addEventListener("change", function () {
    const file = this.files[0];
    if (file) {
      previewImg.src = URL.createObjectURL(file);
    }
  });

  let isFormEdited = false;

  function showCustomPopup(message) {
    const errorPopup = document.getElementById("customPopup");

    // If the popup doesn't exist yet, create it
    if (!errorPopup) {
      const div = document.createElement("div");
      div.id = "customPopup";
      div.className = "customPopup show";
      div.textContent = message;
      document.body.appendChild(div);

      // Remove after 3 seconds
      setTimeout(() => div.classList.remove("show"), 3000);
      return;
    }

    // If it exists, update text and show
    errorPopup.textContent = message;
    errorPopup.classList.add("show");
    setTimeout(() => errorPopup.classList.remove("show"), 3000);
  }

  // Track changes in all inputs
  memberForm.querySelectorAll("input").forEach((input) => {
    // Store initial value
    const initialValue = input.type === "file" ? null : input.value;

    // For text, email, number, etc.
    input.addEventListener("input", () => {
      if (input.type !== "hidden" && input.value !== initialValue) {
        isFormEdited = true;
      }
    });

    // For file inputs
    if (input.type === "file") {
      input.addEventListener("change", () => {
        if (input.files.length > 0) isFormEdited = true;
      });
    }
  });

  // Form submit validation
  memberForm.addEventListener("submit", (e) => {
    if (!isFormEdited) {
      e.preventDefault();
      showCustomPopup("Please enter something before submitting.");
      return;
    }

    // Reset tracking after successful submission
    isFormEdited = false;
    window.removeEventListener("beforeunload", beforeUnloadHandler);
  });

  function beforeUnloadHandler(e) {
    if (isFormEdited) {
      e.preventDefault();
      e.returnValue = "";
    }
  }

  window.addEventListener("beforeunload", beforeUnloadHandler);
});
