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
      if (!isStatic) {
        // dynamic file from input
        const file = filesArray.find((f) => f.name === identifier);
        if (file) {
          const blobUrl = URL.createObjectURL(file);
          window.open(blobUrl, "_blank");
          // optional: revoke later
          setTimeout(() => URL.revokeObjectURL(blobUrl), 1000);
        }
      } else {
        // static image from server
        window.open(src, "_blank");
      }
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
    getDynamicCount: () => filesArray.length,
  };
}

const addVariantBtn = document.getElementById("add-variant-btn");
const variantTemplate = document.getElementById("variantTemplate");
const activeVariantsContainer = document.getElementById(
  "activeVariantsContainer"
);

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

    const fileInput = variant.querySelector(
      'input[name="variant_images_group[]"]'
    );
    if (fileInput) {
      fileInput.name = "variant_images_group[]";
    }
  });
}

document
  .querySelectorAll(".variant-section-basicInfo")
  .forEach((section, index) => {
    const initialImages = existingVariantImages[index] || [];
    setupVariantSection(section, initialImages);
  });

function setupVariantSection(section, initialImages = []) {
  const variantImageInput = section.querySelector(".variant-image-input");
  const variantPreview = section.querySelector(".variant-preview-container");
  const variantDropZone = section.querySelector(".variant-drop-zone");
  const removeBtn = section.querySelector(".remove-variant-btn");
  const isExistingVariant = section.querySelector(
    'input[name="product_variant_ids[]"]'
  );

  if (removeBtn) {
    if (IS_EDIT_MODE && isExistingVariant) {
      removeBtn.style.display = "none";
    } else {
      removeBtn.style.display = "inline-block";
      removeBtn.onclick = function () {
        section.remove();
        renumberVariants();
      };
    }
  }
  if (!variantImageInput || !variantPreview) return;

  let filesArray = [];
  let staticImageNames = initialImages.map((url) => url.split("/").pop());

  // Create hidden input
  let hiddenInput = section.querySelector(
    'input[name="variant_existing_images[]"]'
  );
  if (!hiddenInput) {
    hiddenInput = document.createElement("input");
    hiddenInput.type = "hidden";
    hiddenInput.name = "variant_existing_images[]";
    section.appendChild(hiddenInput);
  }
  hiddenInput.value = staticImageNames.join(",");

  variantPreview.innerHTML = "";

  // Load existing images
  initialImages.forEach((url, idx) => {
    const filename = staticImageNames[idx];
    const div = createPreviewElement(url, filename, true);
    variantPreview.appendChild(div);
  });

  function createPreviewElement(src, identifier, isStatic = false) {
    const div = document.createElement("div");
    div.classList.add("preview-image-container");
    if (!isStatic) div.classList.add("dynamic-preview");

    div.style.cssText =
      "position: relative; display: inline-block; margin-right: 10px; margin-bottom: 10px;";

    const img = document.createElement("img");
    img.src = src;
    img.style.cssText =
      "width:90px;height:90px;object-fit:cover;border-radius:6px;cursor:pointer;";
    img.addEventListener("click", () => window.open(src, "_blank"));
    div.appendChild(img);

    const btn = document.createElement("button");
    btn.type = "button";
    btn.innerHTML = "✖";
    btn.style.cssText =
      "position: absolute; top: -5px; right: -5px; background: red; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer;";

    btn.addEventListener("click", (e) => {
      e.stopPropagation();

      if (isStatic) {
        const index = staticImageNames.indexOf(identifier);
        if (index > -1) {
          staticImageNames.splice(index, 1);
          hiddenInput.value = staticImageNames.join(",");
          div.remove();
        }
      } else {
        const fileIndex = filesArray.findIndex((f) => f.name === identifier);
        if (fileIndex > -1) {
          filesArray.splice(fileIndex, 1);
          updateInputFiles();
        }
      }
    });

    div.appendChild(btn);
    return div;
  }

  function updateInputFiles() {
    variantPreview.innerHTML = "";

    if (filesArray.length > 0) {
      staticImageNames = [];
      hiddenInput.value = "";

      const file = filesArray[0];
      const reader = new FileReader();
      reader.onload = function (e) {
        if (
          variantPreview.querySelectorAll(".preview-image-container").length ===
          0
        ) {
          const div = createPreviewElement(e.target.result, file.name, false);
          variantPreview.appendChild(div);
        }
      };
      reader.readAsDataURL(file);
    }
    const dt = new DataTransfer();
    filesArray.forEach((f) => dt.items.add(f));
    variantImageInput.files = dt.files;
  }
  variantImageInput.addEventListener("change", (e) => {
    const newFiles = Array.from(e.target.files);
    const uniqueFiles = newFiles.filter(
      (f) =>
        !filesArray.some(
          (existing) => existing.name === f.name && existing.size === f.size
        )
    );

    filesArray = uniqueFiles.slice(0, 1);
    updateInputFiles();
  });

  if (variantDropZone) {
    ["dragenter", "dragover", "dragleave", "drop"].forEach((event) => {
      variantDropZone.addEventListener(event, (e) => e.preventDefault());
    });

    variantDropZone.addEventListener("dragover", () =>
      variantDropZone.classList.add("drag-over")
    );

    variantDropZone.addEventListener("dragleave", () =>
      variantDropZone.classList.remove("drag-over")
    );

    variantDropZone.addEventListener("drop", (e) => {
      e.preventDefault();
      e.stopPropagation();
      variantDropZone.classList.remove("drag-over");

      const droppedFiles = Array.from(e.dataTransfer.files);

      const uniqueFiles = droppedFiles.filter(
        (f) =>
          !filesArray.some(
            (existing) => existing.name === f.name && existing.size === f.size
          )
      );

      filesArray = filesArray.concat(uniqueFiles).slice(0, 1);
      updateInputFiles();
    });
  }
}

function addNewVariant() {
  const template = document.getElementById("variantTemplate");
  const clone = template.content.cloneNode(true);
  const section = clone.querySelector(".variant-section-basicInfo");

  document.getElementById("activeVariantsContainer").appendChild(section);

  setupVariantSection(section, []);
  renumberVariants();
}

document.addEventListener("DOMContentLoaded", () => {
  const existingVariants = activeVariantsContainer.querySelectorAll(
    ".variant-section-basicInfo"
  );

  if (existingVariants.length > 0) {
    existingVariants.forEach((variant, index) => {
      const variantImages =
        existingVariantImages && existingVariantImages[index]
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
  }

  function hideModal() {
    modal.style.display = "none";
  }

  function validateForm() {
    document.querySelectorAll(".error-message").forEach((el) => el.remove());
    let isValid = true;

    const showError = (
      inputName,
      message,
      isArray = false,
      index = null,
      targetElement = null,
      insertAfter = false
    ) => {
      let errorTarget = targetElement;

      if (!errorTarget) {
        let selector = isArray
          ? `[name="${inputName}[]"]`
          : `[name="${inputName}"]`;
        let inputs = document.querySelectorAll(selector);
        errorTarget = index !== null ? inputs[index] : inputs[0];
      }

      if (errorTarget) {
        const errorSpan = document.createElement("span");
        errorSpan.className = "error-message";
        errorSpan.style.cssText =
          "color: red; font-size: 0.8em; display: block; margin-top: 5px;";
        errorSpan.textContent = message;

        if (insertAfter) {
          // Insert after the target element
          errorTarget.insertAdjacentElement("afterend", errorSpan);
        } else {
          // Insert inside the container (default behavior)
          const container =
            errorTarget.closest(".form-group") || errorTarget.parentNode;
          container.appendChild(errorSpan);
        }

        isValid = false;
      }
    };

    const formData = new FormData(document.getElementById("productForm"));

    if (!formData.get("product_name").trim())
      showError("product_name", "Product name is required.");
    if (!formData.get("description").trim())
      showError("description", "Product description is required.");
    if (!formData.get("category_id"))
      showError("category_id", "Please select a category.");

    const cost = parseFloat(formData.get("cost_price"));
    const sales = parseFloat(formData.get("sales_price"));

    if (isNaN(cost) || cost <= 0)
      showError("cost_price", "Invalid cost price.");
    if (isNaN(sales) || sales <= 0)
      showError("sales_price", "Invalid sales price.");
    if (sales < cost)
      showError("sales_price", "Sales price cannot be less than cost price.");

    const variantSections = document.querySelectorAll(
      ".variant-section-basicInfo"
    );

    if (variantSections.length === 0) {
      const variantContainer = document.getElementById(
        "activeVariantsContainer"
      );
      showError(
        "variants",
        "At least one variant is required.",
        false,
        null,
        variantContainer
      );
      isValid = false;
    } else {
      variantSections.forEach((section, i) => {
        const vId = section.querySelector('[name="variant_ids[]"]')?.value;
        const minStock = section.querySelector(
          '[name="min_stock_levels[]"]'
        )?.value;
        const stockQty = section.querySelector('[name="stock_qtys[]"]')?.value;

        if (!vId) {
          const variantSelect = section.querySelector('[name="variant_ids[]"]');
          showError(
            "variant_ids",
            `Please select a variant for Variant ${i + 1}.`,
            false,
            null,
            variantSelect
          );
        }

        if (!minStock || minStock < 1) {
          const minStockInput = section.querySelector(
            '[name="min_stock_levels[]"]'
          );
          showError(
            "min_stock_levels",
            "Must be more than 1",
            false,
            null,
            minStockInput
          );
        }

        if (!stockQty || stockQty < 1) {
          const stockQtyInput = section.querySelector('[name="stock_qtys[]"]');
          showError(
            "stock_qtys",
            "Must be more than 1.",
            false,
            null,
            stockQtyInput
          );
        }

        const variantImageInput = section.querySelector(".variant-image-input");
        const variantPreview = section.querySelector(
          ".variant-preview-container"
        );
        const variantExistingImages = variantPreview
          ? variantPreview.querySelectorAll(".preview-image-container").length
          : 0;
        const variantNewImages = variantImageInput
          ? variantImageInput.files.length
          : 0;

        if (variantExistingImages === 0 && variantNewImages === 0) {
          const variantImageContainer =
            section.querySelector(".variant-image-upload-container") ||
            section.querySelector(".variant-drop-zone") ||
            variantImageInput;
          showError(
            `variant_images_${i}`,
            `Please upload an image for this variant.`,
            false,
            null,
            variantImageContainer,
            true // Insert AFTER the container
          );
          isValid = false;
        }
      });
    }

    const isEditField = document.querySelector('[name="is_edit"]');
    const isEdit = isEditField ? isEditField.value === "1" : false;

    console.log("Is edit mode?", isEdit);

    const previewContainer = document.getElementById("previewContainer");

    const totalProductImages = previewContainer
      ? previewContainer.querySelectorAll(".preview-image-container").length
      : 0;


    if (totalProductImages === 0) {
      const dropZone = document.getElementById("productDropZone");
      const uploadContainer = document.querySelector(
        ".form-row-group:nth-child(4) .form-column:first-child"
      );

      if (dropZone) {
        const errorSpan = document.createElement("span");
        errorSpan.className = "error-message";
        errorSpan.style.cssText =
          "color: red; font-size: 0.8em; display: block; margin-top: 5px;";

        if (isEdit) {
          errorSpan.textContent =
            "Product must have at least one image. Please add an image.";
        } else {
          errorSpan.textContent = "Please upload at least one product image.";
        }

        // Insert the error message AFTER the dropzone container
        dropZone.parentNode.insertBefore(errorSpan, dropZone.nextSibling);
        isValid = false;
      } else if (uploadContainer) {
        const errorSpan = document.createElement("span");
        errorSpan.className = "error-message";
        errorSpan.style.cssText =
          "color: red; font-size: 0.8em; display: block; margin-top: 5px;";
        errorSpan.textContent = "Please upload at least one product image.";

        uploadContainer.appendChild(errorSpan);
        isValid = false;
      }
    }

    return isValid;
  }

  submitBtn.addEventListener("click", (e) => {
    e.preventDefault();

    if (validateForm()) {
      showModal();
    } else {
      const firstError = document.querySelector(".error-message");
      if (firstError) {
        firstError.scrollIntoView({ behavior: "smooth", block: "center" });

        // Optional: Highlight the problematic section
        const variantSection = firstError.closest(".variant-section-basicInfo");
        if (variantSection) {
          variantSection.style.backgroundColor = "rgba(255, 0, 0, 0.05)";
          variantSection.style.border = "1px solid red";
          variantSection.style.borderRadius = "5px";
          variantSection.style.padding = "10px";
          variantSection.style.marginBottom = "15px";

          // Remove highlighting after 3 seconds
          setTimeout(() => {
            variantSection.style.backgroundColor = "";
            variantSection.style.border = "";
            variantSection.style.padding = "";
            variantSection.style.marginBottom = "";
          }, 3000);
        }

        // Also highlight product image section if it has error
        const productImageError = firstError.closest(
          "#previewContainer, #productDropZone"
        );
        if (productImageError) {
          productImageError.style.border = "2px solid red";
          productImageError.style.borderRadius = "5px";
          productImageError.style.padding = "10px";

          setTimeout(() => {
            productImageError.style.border = "";
            productImageError.style.padding = "";
          }, 3000);
        }
      }
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

document.addEventListener("DOMContentLoaded", () => {
  const removeVariantsBtn = document.getElementById("removeVariants");
  const activeVariantsContainer = document.getElementById(
    "activeVariantsContainer"
  );
  const deleteVariantModal = document.getElementById("deleteVariantModal");
  const variantCheckboxContainer = document.getElementById(
    "variantCheckboxContainer"
  );
  const confirmDeleteVariants = document.getElementById(
    "confirmDeleteVariants"
  );
  const cancelDeleteVariants = document.getElementById("cancelDeleteVariants");
  const deleteInput = document.getElementById("delete_variant_ids");
  const deleteForm = document.getElementById("deleteVariantsForm");

  removeVariantsBtn.addEventListener("click", () => {
    const variantSections = activeVariantsContainer.querySelectorAll(
      ".variant-section-basicInfo"
    );

    if (variantSections.length <= 1) {
      alert(
        "Cannot delete the only variant. A product must have at least one variant."
      );
      return;
    }

    variantCheckboxContainer.innerHTML = "";

    variantSections.forEach((section, index) => {
      const variantName = section.querySelector("select[name='variant_ids[]']")
        .selectedOptions[0].text;
      const variantId = section.querySelector(
        "input[name='product_variant_ids[]']"
      ).value;

      const checkboxWrapper = document.createElement("div");
      checkboxWrapper.style.marginBottom = "8px";
      checkboxWrapper.innerHTML = `
                <label>
                    <input type="checkbox" name="variant_to_delete" value="${variantId}">
                    Variant ${index + 1}: ${variantName}
                </label>
            `;
      variantCheckboxContainer.appendChild(checkboxWrapper);
    });

    deleteVariantModal.style.display = "block";
  });

  cancelDeleteVariants.addEventListener("click", () => {
    deleteVariantModal.style.display = "none";
  });

  confirmDeleteVariants.addEventListener("click", () => {
    const checkedBoxes = variantCheckboxContainer.querySelectorAll(
      "input[name='variant_to_delete']:checked"
    );
    if (checkedBoxes.length === 0) {
      alert("Please select at least one variant to delete.");
      return;
    }

    const variantIdsToDelete = Array.from(checkedBoxes).map((cb) => cb.value);
    deleteInput.value = variantIdsToDelete.join(",");
    deleteForm.submit();
  });
});

function setupDragAndDrop(dropZone, fileInput, previewContainer, maxFiles = 5) {
  ["dragenter", "dragover", "dragleave", "drop"].forEach((event) => {
    dropZone.addEventListener(event, (e) => e.preventDefault());
  });

  dropZone.addEventListener("dragover", () =>
    dropZone.classList.add("drag-over")
  );
  dropZone.addEventListener("dragleave", () =>
    dropZone.classList.remove("drag-over")
  );

  dropZone.addEventListener("drop", (e) => {
    e.preventDefault();
    e.stopPropagation();
    dropZone.classList.remove("drag-over");

    const dt = new DataTransfer();
    const droppedFiles = Array.from(e.dataTransfer.files);

    droppedFiles.forEach((file) => dt.items.add(file));

    fileInput.files = dt.files;
    fileInput.dispatchEvent(new Event("change"));
  });
}
document.addEventListener("DOMContentLoaded", () => {
  const productDropZone = document.getElementById("productDropZone");
  const productInput = document.getElementById("product_images");
  const productPreview = document.getElementById("previewContainer");

  setupDragAndDrop(productDropZone, productInput, productPreview, 5);
});
