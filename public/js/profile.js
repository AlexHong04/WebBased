// --- profile.js ---

document.addEventListener("DOMContentLoaded", function () {
  const editBtn = document.getElementById("edit-btn");
  const form = document.getElementById("profile-form");
  const avatarDropZone = document.getElementById("avatarDropZone");
  const fileInput = document.getElementById("profile-pic-input");
  const previewImg = document.getElementById("profile-pic-preview");

  const inputs = form.querySelectorAll(
    "input.form-control, textarea.form-control"
  );

  // 点击 Edit 按钮的逻辑
  editBtn.addEventListener("click", function (e) {
    if (editBtn.innerText === "Edit Profile") {
      e.preventDefault();

      // 1. 开启所有文本输入框
      inputs.forEach((input) => {
        input.removeAttribute("readonly");
        input.style.backgroundColor = "#fff";
      });

      // 2. 📸 开启图片上传功能
      fileInput.removeAttribute("disabled"); // 启用 input
      avatarDropZone.classList.add("edit-mode"); // 添加 CSS 类，显示 + 号和允许点击

      editBtn.innerText = "Save Changes";
      editBtn.type = "submit";

      if (inputs.length > 0) inputs[0].focus();
    } else {
      // 提交表单...
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

  // 点击上传 (Input Change)
  fileInput.addEventListener("change", function () {
    handleProfileFile(this.files[0]);
  });

  // 拖拽相关逻辑 (Drag and Drop)
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
      // ⚠️ 关键检查：如果没有点击 Edit，不允许拖拽上传
      if (!avatarDropZone.classList.contains("edit-mode")) return;

      const droppedFiles = e.dataTransfer.files;
      if (droppedFiles.length > 0) {
        handleProfileFile(droppedFiles[0]);
      }
    },
    false
  );
});
