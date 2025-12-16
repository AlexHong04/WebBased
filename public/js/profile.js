// Change this path to match your actual address API location
const BASE_ADDRESS_API_URL = "/app/controllers/address_router.php";

document.addEventListener("DOMContentLoaded", function () {
  const editBtn = document.getElementById("edit-btn");
  const form = document.getElementById("profile-form");
  const formActions = document.querySelector(".form-actions");
  const cancelBtn = document.querySelector(".cancel-btn");

  const inputs = form.querySelectorAll(
    "input.form-control, textarea.form-control"
  );

  editBtn.addEventListener("click", function (e) {
    if (editBtn.innerText === "Edit Profile") {
      e.preventDefault();
      inputs.forEach((input) => {
        input.removeAttribute("readonly");
        input.style.backgroundColor = "#fff";
      });

      // Create "Select Address" button dynamically
      // Check if it already exists to prevent duplicates
      // if (!document.getElementById("edit-address-btn")) {
      //   const editAddressBtn = document.createElement("button");
      //   editAddressBtn.type = "button";
      //   editAddressBtn.innerText = "Select Address";
      //   editAddressBtn.className = "save-btn";
      //   editAddressBtn.id = "edit-address-btn";
      //   editAddressBtn.style.marginLeft = "10px";
      //   editAddressBtn.style.marginRight = "10px";

      //   editAddressBtn.addEventListener("click", function () {
      //     openAddressModal();
      //   });

      //   formActions.insertBefore(editAddressBtn, cancelBtn);
      // }

      editBtn.innerText = "Save Changes";
      editBtn.type = "submit";

      if (inputs.length > 0) inputs[0].focus();
    } else {
      // Let the form submit naturally
      // form.submit();
    }
  });

  // REMOVED: conflicting 'handleAddressFormSubmit' listener here.
  // We will handle saving via the explicit button click below.
});

/* =========================================
   HELPER FUNCTIONS FOR MODALS & API
   ========================================= */

// let addressToDeleteId = null;

// // JSON Fetcher Helper
// async function fetchAndParseJSON(url, options = {}) {
//   const response = await fetch(url, options);
//   const text = await response.text();

//   try {
//     const jsonStartIndex = text.indexOf("{");
//     if (jsonStartIndex >= 0) {
//       return JSON.parse(text.substring(jsonStartIndex));
//     }
//     return JSON.parse(text);
//   } catch (e) {
//     console.error("Server Error:", text);
//     throw new Error("Server error.");
//   }
// }

// // 1. Open Address List Modal
// function openAddressModal() {
//   document.getElementById("addressModalOverlay").style.display = "flex";
//   loadAddressList();
// }

// function closeAddressModal() {
//   document.getElementById("addressModalOverlay").style.display = "none";
// }

// // 2. Load Address List from API (Updated with new UI)
// function loadAddressList() {
//   const container = document.getElementById("addressListContainer");
//   container.innerHTML = '<p style="text-align: center; color: #777;">Loading...</p>';

//   fetchAndParseJSON(BASE_ADDRESS_API_URL + "?action=list")
//     .then((res) => {
//       if (res.success && res.data.addresses.length > 0) {
//         const addresses = res.data.addresses;
//         let html = "";
        
//         const currentId = (typeof currentSelectedAddressId !== 'undefined') ? currentSelectedAddressId : null;

//         addresses.forEach((addr) => {
//           const jsonStr = JSON.stringify(addr).replace(/"/g, "&quot;");
//           const isSelected = (addr.id == currentId) ? 'selected' : '';
//           const isDef = addr.is_default == 1 ? 1 : 0;
//           html += `
//             <div class="address-list-item ${isSelected}" data-id="${addr.id}" onclick="selectAddressToProfile(${jsonStr})">
//                 <div class="address-icon"><i class="fas fa-map-marker-alt"></i></div>
//                 <div class="address-list-text">
//                     <strong>${addr.name}</strong> (${addr.phone})
//                     ${isDef ? '<span style="color:#fc84a3; margin-left:10px; font-size:0.8rem;">[Default]</span>' : ''}
//                     <p>${(addr.address || '').replace(/\n/g, '<br>')}</p>
//                 </div>
//                 <div class="address-action-buttons">
//                     <i class="fas fa-edit" onclick="event.stopPropagation(); editAddress('${addr.id}')"></i>
//                     <i class="fas fa-trash-alt" onclick="event.stopPropagation(); deleteAddress('${addr.id}')"></i>
//                 </div>
//             </div>`;
//         });
//         container.innerHTML = html;
//       } else {
//         container.innerHTML = '<p style="text-align: center;">No addresses found.</p>';
//       }
//     })
//     .catch((err) => {
//       container.innerHTML = '<p style="text-align: center; color: red;">Failed to load addresses.</p>';
//       console.error(err);
//     });
// }

// // 3. Save Address Logic (Fixed)
// document
//   .getElementById("btnSaveAddress")
//   .addEventListener("click", function (e) {
//     e.preventDefault();

//     const form = document.getElementById("addEditAddressForm");

//     if (!validateAddressForm(form)) {
//       return;
//     }

//     const id = form.address_id.value;
//     const action = id ? "update" : "add";
//     const formData = new URLSearchParams(new FormData(form));
//     formData.append("action", action);

//     const btn = document.getElementById("btnSaveAddress");
//     const originalText = btn.innerText;
//     btn.innerText = "Saving...";
//     btn.disabled = true;

//     fetchAndParseJSON(BASE_ADDRESS_API_URL + "?action=save", {
//       method: "POST",
//       headers: {
//         "Content-Type": "application/x-www-form-urlencoded",
//       },
//       body: formData,
//     })
//       .then((res) => {
//         btn.innerText = originalText;
//         btn.disabled = false;

//         if (res.success) {
//           const d = res.data;
//           const fullAddr =
//             form.street_line.value +
//             "\n" +
//             form.postCode.value +
//             " " +
//             form.City.value +
//             "\n" +
//             form.State.value;

//           // selectAddress(d.address_id, form.ReceiverName.value, form.phoneNumber.value, fullAddr);

//           document.getElementById("addEditAddressModalOverlay").style.display =
//             "none";
//           showSuccess("Address has been saved successfully!");
//         } else {
//           const errDiv = document.getElementById("generalErrorMsg");
//           if (errDiv) {
//             errDiv.innerText = res.message || "Failed.";
//             errDiv.style.display = "block";
//           } else {
//             alert(res.message);
//           }
//         }
//       })
//       .catch((err) => {
//         btn.innerText = originalText;
//         btn.disabled = false;
//         alert("Error: " + err.message);
//       });
//   });

// // 4. Select Address -> Fill Profile Form
// function selectAddressToProfile(addr) {
//   // Fill the Profile Page inputs
//   const street =
//     document.querySelector('[name="street_line"]') ||
//     document.getElementById("streetInput");
//   const city =
//     document.querySelector('[name="City"]') ||
//     document.getElementById("cityInput");
//   const state =
//     document.querySelector('[name="State"]') ||
//     document.getElementById("stateInput");
//   const postcode =
//     document.querySelector('[name="postCode"]') ||
//     document.getElementById("postcodeInput");

//   if (street) street.value = addr.street_line || addr.address;
//   if (city) city.value = addr.city;
//   if (state) state.value = addr.state;
//   if (postcode) postcode.value = addr.postcode;

//   // Close both modals
//   closeAddressModal();
//   document.getElementById("addEditAddressModalOverlay").style.display = "none";
// }

// // 5. Open Add/Edit Address Modal
// function openAddAddressModal(event, address = null) {
//   if (event) event.preventDefault();
//   document.getElementById("addressModalOverlay").style.display = "none"; // Hide list
//   document.getElementById("addEditAddressModalOverlay").style.display = "flex"; // Show form

//   const form = document.getElementById("addEditAddressForm");
//   form.reset();
//   clearErrors(form);

//   document.getElementById("addEditModalTitle").innerText = address
//     ? "Edit Address"
//     : "Add New Address";
//   document.getElementById("addressIdInput").value = address ? address.id : "";

//   if (address) {
//     // Populate form fields
//     if (form.elements["ReceiverName"])
//       form.elements["ReceiverName"].value = address.name;
//     if (form.elements["phoneNumber"])
//       form.elements["phoneNumber"].value = address.phone;
//     if (form.elements["street_line"])
//       form.elements["street_line"].value =
//         address.street_line || address.address; // Fallback
//     if (form.elements["City"]) form.elements["City"].value = address.city;
//     if (form.elements["State"]) form.elements["State"].value = address.state;
//     if (form.elements["postCode"])
//       form.elements["postCode"].value = address.postcode;
//   }
// }

// function closeAddEditAddressModal() {
//   document.getElementById("addEditAddressModalOverlay").style.display = "none";
//   document.getElementById("addressModalOverlay").style.display = "flex"; // Return to list
// }

// // REMOVED: handleAddressFormSubmit (Redundant, functionality moved to btnSaveAddress listener)

// // 6. Edit Address Button Logic
// function editAddress(id) {
//   fetchAndParseJSON(BASE_ADDRESS_API_URL + "?action=get&id=" + id).then(
//     (res) => {
//       if (res.success) openAddAddressModal(null, res.data);
//     }
//   );
// }

// // 7. Delete Address Logic
// function deleteAddress(id) {
//   addressToDeleteId = id;
//   document.getElementById("addressModalOverlay").style.display = "none";
//   document.getElementById("deleteConfirmModalOverlay").style.display = "flex";
// }

// function closeDeleteConfirmModal() {
//   document.getElementById("deleteConfirmModalOverlay").style.display = "none";
//   document.getElementById("addressModalOverlay").style.display = "flex";
//   addressToDeleteId = null;
// }

// function executeDeleteAddress() {
//   if (!addressToDeleteId) return;
//   const formData = new URLSearchParams({ address_id: addressToDeleteId });

//   fetchAndParseJSON(BASE_ADDRESS_API_URL + "?action=delete", {
//     method: "POST",
//     headers: { "Content-Type": "application/x-www-form-urlencoded" },
//     body: formData,
//   }).then((res) => {
//     closeDeleteConfirmModal();
//     if (res.success) loadAddressList();
//     else showWarning(res.message);
//   });
// }

// // 8. Feedback UI
// function showSuccess(msg) {
//   const el = document.getElementById("successMessage");
//   if (el) el.innerText = msg;
//   const overlay = document.getElementById("successModalOverlay");
//   if (overlay) overlay.style.display = "flex";
//   else alert(msg); // Fallback
// }

// function closeSuccessModal() {
//   document.getElementById("successModalOverlay").style.display = "none";
// }

// function showWarning(msg) {
//   const el = document.getElementById("warningMessage");
//   if (el) el.innerText = msg;
//   const overlay = document.getElementById("warningModalOverlay");
//   if (overlay) overlay.style.display = "flex";
//   else alert(msg);
// }

// function closeWarningModal() {
//   document.getElementById("warningModalOverlay").style.display = "none";
// }
