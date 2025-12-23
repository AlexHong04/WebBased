document.addEventListener("DOMContentLoaded", function () {
  const hiddenInput = document.getElementById("isBlockedInput");
  const successPopup = document.getElementById("successPopup");
  const errorPopup = document.getElementById("customPopup");
  const statusDisplay = document.getElementById("statusDisplay");
  const statusModal = document.getElementById("statusModal");
  const cancelBtn = document.getElementById("cancelBtn");
  const confirmBtn = document.getElementById("confirmBtn");
  const avatarDropZone = document.getElementById("avatarDropZone");
  const fileInput = document.getElementById("profile-pic-input");
  const previewImg = document.getElementById("profile-pic-preview");
  const memberForm = document.querySelector("form");

  let newStatusValue = hiddenInput.value;
  let isFormEdited = false;

  const savedTab = localStorage.getItem("activeMemberTab");
  if (savedTab) {
    document
      .querySelectorAll(".tab")
      .forEach((t) => t.classList.remove("active"));
    document
      .querySelectorAll(".profile-form-container")
      .forEach((sec) => (sec.style.display = "none"));

    const activeTab = document.querySelector(`.tab[data-target="${savedTab}"]`);
    if (activeTab) activeTab.classList.add("active");

    const activeContainer = document.getElementById(savedTab);
    if (activeContainer) activeContainer.style.display = "block";

    localStorage.removeItem("activeMemberTab");
  }

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

  // Status Popup
  statusDisplay.addEventListener("click", () => {
    newStatusValue = hiddenInput.value == "1" ? "0" : "1";
    const statusText = newStatusValue == "1" ? "Blocked" : "Normal";

    document.getElementById(
      "modalMessage"
    ).textContent = `Are you sure you want to change member status to "${statusText}"?`;

    statusModal.style.display = "flex";
  });

  // Cancel button
  cancelBtn.addEventListener("click", () => {
    statusModal.style.display = "none";
  });

  // Confirm button
  confirmBtn.addEventListener("click", () => {
    hiddenInput.value = newStatusValue;

    statusDisplay.textContent = newStatusValue == "1" ? "Blocked" : "Normal";
    statusDisplay.classList.toggle("blocked", newStatusValue == "1");
    statusDisplay.classList.toggle("normal", newStatusValue != "1");

    isFormEdited = true;

    statusModal.style.display = "none";

    memberForm.requestSubmit();
  });

  // Tab switching
  document.querySelectorAll(".tab").forEach((tab) => {
    tab.addEventListener("click", function (e) {
      e.preventDefault();

      if (isFormEdited) {
        showCustomPopup("Please save your changes before switching tabs.");
        return;
      }

      document
        .querySelectorAll(".tab")
        .forEach((t) => t.classList.remove("active"));
      tab.classList.add("active");
      document
        .querySelectorAll(".profile-form-container")
        .forEach((sec) => (sec.style.display = "none"));
      document.getElementById(tab.dataset.target).style.display = "block";
      localStorage.setItem("activeMemberTab", tab.dataset.target);
    });
  });

  // process the file and show preview
  function handleProfileFile(file) {
    if (file && file.type.startsWith("image/")) {
      previewImg.src = URL.createObjectURL(file);
      isFormEdited = true;

      const dataTransfer = new DataTransfer();
      dataTransfer.items.add(file);
      fileInput.files = dataTransfer.files;
    }
  }

  // Standard File Input (Click)
  fileInput.addEventListener("change", function () {
    handleProfileFile(this.files[0]);
  });

  // Drag and Drop
  ["dragenter", "dragover"].forEach((eventName) => {
    avatarDropZone.addEventListener(
      eventName,
      (e) => {
        e.preventDefault();
        e.stopPropagation();
        avatarDropZone.classList.add("drag-over");
      },
      false
    );
  });

  ["dragleave", "drop"].forEach((eventName) => {
    avatarDropZone.addEventListener(
      eventName,
      (e) => {
        e.preventDefault();
        e.stopPropagation();
        avatarDropZone.classList.remove("drag-over");
      },
      false
    );
  });

  avatarDropZone.addEventListener(
    "drop",
    (e) => {
      const droppedFiles = e.dataTransfer.files;
      if (droppedFiles.length > 0) {
        handleProfileFile(droppedFiles[0]);
      }
    },
    false
  );

  function showCustomPopup(message) {
    const errorPopup = document.getElementById("customPopup");

    if (!errorPopup) {
      const div = document.createElement("div");
      div.id = "customPopup";
      div.className = "customPopup show";
      div.textContent = message;
      document.body.appendChild(div);

      setTimeout(() => div.classList.remove("show"), 3000);
      return;
    }

    errorPopup.textContent = message;
    errorPopup.classList.add("show");
    setTimeout(() => errorPopup.classList.remove("show"), 3000);
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const malaysiaPhoneRegex = /^(\+?6?01)[0-9]{8,9}$/;

  function showError(input, message) {
    const group = input.closest(".info-group");
    if (!group) return;

    let error = group.querySelector(".error-msg");
    if (!error) {
      error = document.createElement("small");
      error.className = "error-msg";
      group.appendChild(error);
    }

    error.textContent = message;
    input.classList.add("input-error");
  }

  function clearError(input) {
    const group = input.closest(".info-group");
    if (!group) return;

    const error = group.querySelector(".error-msg");
    if (error) {
      error.textContent = "";
    }

    input.classList.remove("input-error");
  }

  memberForm.querySelectorAll("input").forEach((input) => {
    input.addEventListener("input", () => clearError(input));
  });

  // Track changes in all inputs
  memberForm.querySelectorAll("input").forEach((input) => {
    const initialValue = input.type === "file" ? null : input.value;

    input.addEventListener("input", () => {
      if (input.type !== "hidden" && input.value !== initialValue) {
        isFormEdited = true;
      }
    });

    if (input.type === "file") {
      input.addEventListener("change", () => {
        if (input.files.length > 0) isFormEdited = true;
      });
    }
  });

  function beforeUnloadHandler(e) {
    if (isFormEdited) {
      e.preventDefault();
      e.returnValue = "";
    }
  }

  window.addEventListener("beforeunload", beforeUnloadHandler);

  memberForm.addEventListener("submit", (e) => {
    let isValid = true;

    if (!isFormEdited) {
      e.preventDefault();
      showCustomPopup("Please enter something before submitting.");
      return;
    }

    memberForm.querySelectorAll("input[required]").forEach((input) => {
      if (!input.value.trim()) {
        showError(input, "This field is required");
        isValid = false;
      } else {
        clearError(input);
      }
    });

    const email = memberForm.querySelector('input[name="email"]');
    if (email && !emailRegex.test(email.value.trim())) {
      showError(email, "Invalid email format");
      isValid = false;
    }

    const phone = memberForm.querySelector('input[name="phone"]');
    if (phone && !malaysiaPhoneRegex.test(phone.value.replace(/[\s\-]/g, ""))) {
      showError(phone, "Invalid Malaysian phone number");
      isValid = false;
    }

    const reward = memberForm.querySelector('input[name="rewardPoint"]');
    if (reward && reward.value !== "" && Number(reward.value) < 0) {
      showError(reward, "Reward points cannot be negative");
      isValid = false;
    }

    if (!isValid) {
      e.preventDefault();
      return;
    }

    isFormEdited = false;
    window.removeEventListener("beforeunload", beforeUnloadHandler);
  });
});
