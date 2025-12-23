// --- profile.js ---

document.addEventListener("DOMContentLoaded", function () {
  const editBtn = document.getElementById("edit-btn");
  const form = document.getElementById("profile-form");
  const avatarDropZone = document.getElementById("avatarDropZone");
  const fileInput = document.getElementById("profile-pic-input");
  const previewImg = document.getElementById("profile-pic-preview");
  const inputs = form.querySelectorAll(
    "input:not([type='hidden']):not([type='file']), textarea, select"
  );
  editBtn.addEventListener("click", function (e) {
    if (editBtn.innerText.trim() === "Edit Profile") {
      e.preventDefault();
      inputs.forEach((input) => {
        input.removeAttribute("readonly");
        input.removeAttribute("disabled");
        input.classList.remove("readonly-style");
        input.style.backgroundColor = "#fff";
        input.style.cursor = "text";
      });


      fileInput.removeAttribute("disabled"); 
      avatarDropZone.classList.add("edit-mode"); 

      editBtn.innerText = "Save Changes";
      editBtn.type = "submit";

      if (inputs.length > 0) inputs[0].focus();
    } else {
      form.submit();
    }
  });

  function handleProfileFile(file) {
    if (file && file.type.startsWith("image/")) {
      previewImg.src = URL.createObjectURL(file);
      const dataTransfer = new DataTransfer();
      dataTransfer.items.add(file);
      fileInput.files = dataTransfer.files;
    }
  }

  fileInput.addEventListener("change", function () {
    handleProfileFile(this.files[0]);
  });

  ["dragenter", "dragover"].forEach((eventName) => {
    avatarDropZone.addEventListener(
      eventName,
      (e) => {
        e.preventDefault();
        e.stopPropagation();

        if (!avatarDropZone.classList.contains("edit-mode")) return;

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
      if (!avatarDropZone.classList.contains("edit-mode")) return;

      const droppedFiles = e.dataTransfer.files;
      if (droppedFiles.length > 0) {
        handleProfileFile(droppedFiles[0]);
      }
    },
    false
  );
});
