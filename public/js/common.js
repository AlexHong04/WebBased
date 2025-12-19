function setupConfirmationModal({
  triggerSelector,   // Elements that trigger the popup
  modalId,           // ID of the modal
  messageId,         // ID of the element inside modal where message shows
  confirmBtnId,      // ID of confirm button
  cancelBtnId,       // ID of cancel button
  getMessage,        // Function to get message for this action
  onConfirm          // Function to execute when confirmed
}) {
  const modal = document.getElementById(modalId);
  const messageEl = document.getElementById(messageId);
  const confirmBtn = document.getElementById(confirmBtnId);
  const cancelBtn = document.getElementById(cancelBtnId);

  let currentItem = null;

  // Open modal
  document.querySelectorAll(triggerSelector).forEach((btn) => {
    btn.addEventListener("click", () => {
      currentItem = btn;
      messageEl.textContent = getMessage(btn);
      modal.style.display = "flex";
    });
  });

  // Cancel button
  cancelBtn.addEventListener("click", () => {
    modal.style.display = "none";
  });

  // Confirm button
  confirmBtn.addEventListener("click", () => {
    onConfirm(currentItem);
    modal.style.display = "none";
  });
}
