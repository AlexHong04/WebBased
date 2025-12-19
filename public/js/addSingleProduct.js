const mainInput = document.getElementById("product_images");
const mainPreview = document.getElementById("previewContainer");
const IS_EDIT_MODE = Boolean(
  document.querySelector('[name="product_id"]')?.value
);

setupImagePreview(mainInput, mainPreview, 5, existingProductImages || []);

function setupImagePreview(
  inputElement,
  previewContainer,
  maxImages = 5,
  initialImages = []
) {
  let filesArray = [];
  let staticImageNames = [];
  let hiddenExistingInput = null;

  function createHiddenInput() {
    const existingInputs = inputElement.form.querySelectorAll(
      `input[name="${inputElement.name.replace("[]", "_existing[]")}"]`
    );
    existingInputs.forEach((input) => input.remove());

    hiddenExistingInput = document.createElement("input");
    hiddenExistingInput.type = "hidden";
    hiddenExistingInput.name = inputElement.name.replace("[]", "_existing[]");
    
    if (inputElement.name === "variant_images_group[]") {
      hiddenExistingInput.name = "variant_existing_images[]";
    }
    
    inputElement.form.appendChild(hiddenExistingInput);
  }

  if (inputElement.form) {
    createHiddenInput();
  }

  function loadExistingImages(urls) {

    const urlsToLoad = urls.slice(0, maxImages);
    
    previewContainer
      .querySelectorAll(".static-image-container")
      .forEach((el) => el.remove());

    urlsToLoad.forEach((url, index) => {
      const filename = url.split("/").pop().split("?")[0];
      staticImageNames.push(filename);
      
      const div = createPreviewElement(url, filename, true);
      previewContainer.appendChild(div); 
    });
    
    updateHiddenInput();
  }

  function createPreviewElement(src, identifier, isStatic = false) {
    const div = document.createElement("div");
    div.classList.add("preview-image-container");
    if (isStatic) {
      div.classList.add("static-image-container");
    } else {
      div.classList.add("dynamic-preview");
    }
    
    div.style.cssText =
      "position: relative; display: inline-block; margin-right: 10px; margin-bottom: 10px;";

    const img = document.createElement("img");
    img.src = src;
    img.classList.add("preview-image");
    img.style.cssText =
      "width:90px;height:90px;object-fit:cover;border-radius:6px;cursor:pointer;";

    img.addEventListener("click", () => {
      window.open(src, "_blank");
    });

    div.appendChild(img);

    const btn = document.createElement("button");
    btn.type = "button";
    btn.innerHTML = "✖";
    btn.classList.add("preview-delete-btn");
    btn.style.cssText =
      "position: absolute; top: -5px; right: -5px; background: red; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer;";
    btn.title = "Remove this image";

    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      
      if (isStatic) {
        const nameIndex = staticImageNames.indexOf(identifier);
        if (nameIndex > -1) {
          staticImageNames.splice(nameIndex, 1);
          div.remove();
          updateHiddenInput();
          
          if (staticImageNames.length + filesArray.length < maxImages) {
            inputElement.disabled = false;
          }
        }
      } else {
        const fileIndex = filesArray.findIndex((f) => f.name === identifier);
        if (fileIndex > -1) {
          filesArray.splice(fileIndex, 1);
          div.remove();
          updateInputFiles();
          
          if (staticImageNames.length + filesArray.length < maxImages) {
            inputElement.disabled = false;
          }
        }
      }
    });

    div.appendChild(btn);
    return div;
  }

  function updateHiddenInput() {
    if (hiddenExistingInput) {
      hiddenExistingInput.value = staticImageNames.join(",");
    }
  }

  function updateInputFiles() {
    previewContainer
      .querySelectorAll(".dynamic-preview")
      .forEach((el) => el.remove());

    filesArray.forEach((file) => {
      const reader = new FileReader();
      reader.onload = function (e) {
        const div = createPreviewElement(e.target.result, file.name, false);
        previewContainer.appendChild(div);
      };
      reader.readAsDataURL(file);
    });

    const dt = new DataTransfer();
    filesArray.forEach((f) => dt.items.add(f));
    inputElement.files = dt.files;
    
    if (staticImageNames.length + filesArray.length >= maxImages) {
      inputElement.disabled = true;
    } else {
      inputElement.disabled = false;
    }
  }

  inputElement.addEventListener("change", (event) => {
    const newFiles = Array.from(event.target.files);
    const currentImageCount = filesArray.length + staticImageNames.length;

    const uniqueNewFiles = newFiles.filter(
      (newFile) =>
        !filesArray.some(
          (existingFile) =>
            existingFile.name === newFile.name &&
            existingFile.size === newFile.size
        )
    );

    if (currentImageCount + uniqueNewFiles.length > maxImages) {
      alert(
        `You can upload up to ${maxImages} images. You currently have ${currentImageCount} images (${staticImageNames.length} existing + ${filesArray.length} new).`
      );
      updateInputFiles();
      return;
    }

    filesArray = filesArray.concat(uniqueNewFiles);
    updateInputFiles();
  });

  if (inputElement.files && inputElement.files.length > 0) {
    filesArray = Array.from(inputElement.files);
    updateInputFiles();
  }

  if (initialImages && initialImages.length > 0) {
    loadExistingImages(initialImages);
  }

  return {
    loadExistingImages,
    updateInputFiles,
    getStaticCount: () => staticImageNames.length,
    getDynamicCount: () => filesArray.length
  };
}

const addVariantBtn = document.getElementById("add-variant-btn");
const variantTemplate = document.getElementById("variantTemplate");
const activeVariantsContainer = document.getElementById("activeVariantsContainer");

function renumberVariants() {
  const allVariants = activeVariantsContainer.querySelectorAll(
    ".variant-section-basicInfo"
  );
    
  allVariants.forEach((variant, index) => {
    const heading = variant.querySelector("h3");
    if (heading) {
      heading.textContent = `Variant ${index + 1}`;
    }
    
    const hiddenInputs = variant.querySelectorAll(
      'input[type="hidden"][name^="variant_existing_images"]'
    );
    
    hiddenInputs.forEach((input) => {
      input.name = `variant_existing_images[${index}]`;
    });
    
    const fileInput = variant.querySelector('input[name="variant_images_group[]"]');
    if (fileInput) {
      fileInput.name = "variant_images_group[]";
    }
  });
}

function setupVariantSection(section, initialImages = []) {
  
  const variantImageInput = section.querySelector(".variant-image-input");
  const variantPreview = section.querySelector(".variant-preview-container");
  
  let previewManager = null;
  
  if (variantImageInput && variantPreview) {
    variantPreview.innerHTML = '';
    
    previewManager = setupImagePreview(
      variantImageInput,
      variantPreview,
      1,
      initialImages
    );
    
    const hiddenInput = document.createElement("input");
    hiddenInput.type = "hidden";
    hiddenInput.name = "variant_existing_images[]";
    hiddenInput.value = initialImages.map(img => img.split("/").pop().split("?")[0]).join(",");
    section.appendChild(hiddenInput);
  }

  const removeBtn = section.querySelector(".remove-variant-btn");
  if (removeBtn) {
    removeBtn.addEventListener("click", () => {
      if (previewManager && previewManager.getStaticCount() > 0) {
        if (!confirm("This variant has existing images. Removing the variant will delete these images. Continue?")) {
          return;
        }
      }
      
      section.remove();
      renumberVariants();
    });
  }
  
  return previewManager;
}

function addNewVariant() {
  const clone = variantTemplate.content.cloneNode(true);
  const section = clone.querySelector(".variant-section-basicInfo");
  
  if (section) {
    setupVariantSection(section, []);
    activeVariantsContainer.appendChild(section);
    renumberVariants();
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const existingVariants = activeVariantsContainer.querySelectorAll(
    ".variant-section-basicInfo"
  );
    
  if (existingVariants.length > 0) {
    existingVariants.forEach((variant, index) => {      
      const variantImages = existingVariantImages && existingVariantImages[index] 
        ? existingVariantImages[index] 
        : [];
    
      setupVariantSection(variant, variantImages);
    });
    
    renumberVariants();
  } else {
    addNewVariant();
  }

  if (addVariantBtn) {
    addVariantBtn.addEventListener("click", addNewVariant);
  }
  
  setupFormSubmission();
});

function setupFormSubmission() {
  const submitBtn = document.querySelector(".confirm-btn");
  const form = document.getElementById("productForm");
  const modal = document.getElementById("submitModal");
  const confirmSubmit = document.getElementById("confirmSubmit");
  const cancelSubmit = document.getElementById("cancelSubmit");

  if (!submitBtn || !form || !modal) return;

  function showModal() {
    modal.style.display = "block";
    
    const productName = form.querySelector('[name="product_name"]')?.value || "this product";
    const isEdit = form.querySelector('[name="product_id"]')?.value?.startsWith('P');
    
    const message = isEdit 
      ? `Are you sure you want to update "${productName}"?`
      : `Are you sure you want to add "${productName}"?`;
    
    document.getElementById("deleteMessage").textContent = message;
  }

  function hideModal() {
    modal.style.display = "none";
  }

  function validateForm() {
    document.querySelectorAll(".error-message").forEach(el => el.remove());
    
    let isValid = true;
    
    const productName = form.querySelector('[name="product_name"]');
    if (!productName || !productName.value.trim()) {
      alert("Product name is required");
      isValid = false;
    }
    
    const variantCount = activeVariantsContainer.querySelectorAll(".variant-section-basicInfo").length;
    if (variantCount === 0) {
      alert("At least one variant is required");
      isValid = false;
    }
    
    return isValid;
  }

  submitBtn.addEventListener("click", (e) => {
    e.preventDefault();
    
    if (validateForm()) {
      showModal();
    }
  });

  if (confirmSubmit) {
    confirmSubmit.addEventListener("click", () => {
      const finalSubmitInput = document.createElement("input");
      finalSubmitInput.type = "hidden";
      finalSubmitInput.name = "final_submission";
      finalSubmitInput.value = "1";
      form.appendChild(finalSubmitInput);
      
      form.submit();
    });
  }

  if (cancelSubmit) {
    cancelSubmit.addEventListener("click", hideModal);
  }

  window.addEventListener("click", (e) => {
    if (e.target === modal) {
      hideModal();
    }
  });
}

removeBtn.addEventListener("click", () => {
  const hasExistingImages =
    previewManager && previewManager.getStaticCount() > 0;

  const hasVariantId =
    section.querySelector('select[name="variant_ids[]"]')?.value;

  if (IS_EDIT_MODE && (hasExistingImages || hasVariantId)) {
    const confirmDelete = confirm(
      "This variant already exists.\n\nRemoving it will permanently delete its data and images.\n\nDo you want to continue?"
    );

    if (!confirmDelete) return;
  }

  section.remove();
  renumberVariants();
});


