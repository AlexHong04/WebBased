<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $json = file_get_contents("php://input");
    if (!$json) {
        echo "ERROR: NO JSON";
        exit;
    }
    $data = json_decode($json, true);
    exit;
}

$title = "Admin Listing Page";
$pageCSS = "adminlisting.css";

require_once __DIR__ . '/../../controllers/adminController.php';

$adminController = new adminController();

// --- HANDLE FORM SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_staff') {
        $adminController->addStaff();
    } elseif ($_POST['action'] === 'edit_staff') {
        $adminController->editStaff();
    } elseif ($_POST['action'] === 'delete_staff') {
        $adminController->deleteStaff();
    }
}

// Fetch Data
$data = $adminController->index();
$admins = $data['admins'] ?? [];
$pagination = $data['pagination'] ?? null;

// Get Session Messages
$errors = $_SESSION['flash_error'] ?? [];
$old = $_SESSION['old'] ?? [];
$success = $_SESSION['flash_success'] ?? null;

unset($_SESSION['flash_error'], $_SESSION['old'], $_SESSION['flash_success']);

function displayValue($value)
{
    return empty($value) && $value !== "0" ? "-" : $value;
}

$fields = [
    'admin_id'   => 'Admin ID',
    'email'      => 'Email',
    'firstName'  => 'First Name',
    'lastName'   => 'Last Name',
    'phone'      => 'Phone',
    'position'   => 'Position',
    'address'    => 'Address',
    'created_at' => 'Created At'
];

$sort = $_GET['sort'] ?? 'admin_id';
$dir  = $_GET['dir'] ?? 'desc';

if (!empty($admins)) {
    usort($admins, function ($a, $b) use ($sort, $dir) {
        $a = (array)$a;
        $b = (array)$b;
        $valA = $a[$sort] ?? '';
        $valB = $b[$sort] ?? '';
        if ($valA == $valB) return 0;
        return ($dir === 'asc') ? ($valA < $valB ? -1 : 1) : ($valA > $valB ? -1 : 1);
    });
}
require_once __DIR__ . '/../adminHeader.php';
?>
<div class='dataListing'>
    <p>Admin Listing</p>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if (isset($errors['global'])): ?>
        <div class="alert alert-danger"><?= $errors['global'] ?></div>
    <?php endif; ?>

    <div class="filters-pagination-container">
        <div class="filters-actions">
            <div class="filters">
                <input type="text" id="dataSearch" placeholder="Search...">
                <div class="custom-select" id="positionSelect">
                    <input type="hidden" id="positionFilter" value="">
                    <div class="selected">All Positions</div>
                    <ul class="options">
                        <li data-value="">All</li>
                        <li data-value="Manager">Manager</li>
                        <li data-value="Staff">Staff</li>
                    </ul>
                </div>
            </div>

            <div class="main-actions">
                <button type="button" id="openAddModalBtn" class="btn-add">
                    <i class="fas fa-plus"></i> Add New Admin
                </button>
            </div>
        </div>

        <div class="pagination-container">
            <?php if ($pagination): ?> <?= $pagination->render(); ?> <?php endif; ?>
        </div>
    </div>

    <table class='data'>
        <thead>
            <tr>
                <th>Select</th>
                <?php foreach ($fields as $key => $label): ?>
                    <th><a href="?sort=<?= $key ?>&dir=<?= $dir === 'asc' ? 'desc' : 'asc' ?>">
                            <?= $label ?> <?= ($sort === $key) ? ($dir === 'asc' ? '▴' : '▾') : '' ?>
                        </a></th>
                <?php endforeach; ?>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($admins)): ?>
                <?php foreach ($admins as $admin): $admin = (object)$admin; ?>
                    <tr class="normal-row" data-position="<?= displayValue($admin->position) ?>">
                        <td><input type="checkbox" name="selected_datas[]" value="<?= $admin->admin_id ?>" class="dataCheckbox"></td>
                        <td><?= displayValue($admin->admin_id) ?></td>
                        <td><?= displayValue($admin->email) ?></td>
                        <td><?= displayValue($admin->firstName) ?></td>
                        <td><?= displayValue($admin->lastName) ?></td>
                        <td><?= displayValue($admin->phone) ?></td>
                        <td><?= displayValue($admin->position) ?></td>
                        <td><?= displayValue($admin->address) ?></td>
                        <td><?= displayValue($admin->created_at) ?></td>

                        <td>
                            <div class="action-buttons-row">
                                <button type="button" class="btn-icon btn-edit"
                                    data-id="<?= $admin->admin_id ?>"
                                    data-fname="<?= $admin->firstName ?>"
                                    data-lname="<?= $admin->lastName ?>"
                                    data-email="<?= $admin->email ?>"
                                    data-phone="<?= $admin->phone ?>"
                                    data-pos="<?= $admin->position ?>"
                                    data-addr="<?= $admin->address ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 512 512">
                                        <path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z" />
                                    </svg>
                                </button>

                                <button type="button" class="btn-icon btn-delete"
                                    data-id="<?= $admin->admin_id ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 448 512">
                                        <path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">No admins found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<div id="addStaffModal" class="<?= !empty($errors) ? 'show-error' : '' ?>">
    <div class="popup-box form-box">
        <div class="popup-header">
            <h3>Add New Admin</h3>
            <span class="close-modal">&times;</span>
        </div>

        <form action="" method="POST" class="modal-form" autocomplete="off">
            <input type="hidden" name="action" value="add_staff">

            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="firstName" value="<?= htmlspecialchars($old['firstName'] ?? '') ?>" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="lastName" value="<?= htmlspecialchars($old['lastName'] ?? '') ?>" required autocomplete="off">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required autocomplete="off">
                    <?php if (isset($errors['email'])): ?> <small class="error"><?= $errors['email'] ?></small> <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($old['phone'] ?? '') ?>" required autocomplete="off">
                    <?php if (isset($errors['phone'])): ?> <small class="error"><?= $errors['phone'] ?></small> <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Position</label>
                    <select name="position" required autocomplete="off">
                        <option value="" disabled selected>Select Position</option>
                        <option value="Manager" <?= (isset($old['position']) && $old['position'] == 'Manager') ? 'selected' : '' ?>>Manager</option>
                        <option value="Staff" <?= (isset($old['position']) && $old['position'] == 'Staff') ? 'selected' : '' ?>>Staff</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" value="<?= htmlspecialchars($old['address'] ?? '') ?>" autocomplete="off">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required autocomplete="new-password">
                    <?php if (isset($errors['password'])): ?> <small class="error"><?= $errors['password'] ?></small> <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Confirm</label>
                    <input type="password" name="confirm_password" required autocomplete="new-password">
                    <?php if (isset($errors['confirm_password'])): ?> <small class="error"><?= $errors['confirm_password'] ?></small> <?php endif; ?>
                </div>
            </div>

            <div class="popup-actions">
                <button type="button" class="close-modal btn-cancel">Cancel</button>
                <button type="submit" class="btn-submit">Create</button>
            </div>
        </form>
    </div>
</div>
<div id="editStaffModal">
    <div class="popup-box form-box">
        <div class="popup-header">
            <h3>Edit Admin</h3>
            <span class="close-modal close-edit">&times;</span>
        </div>

        <form action="" method="POST" class="modal-form" autocomplete="off">
            <input type="hidden" name="action" value="edit_staff">
            <input type="hidden" name="admin_id" id="edit_admin_id">

            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="firstName" id="edit_firstName" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="lastName" id="edit_lastName" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_email" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" id="edit_phone" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Position</label>
                    <select name="position" id="edit_position" required>
                        <option value="Manager">Manager</option>
                        <option value="Staff">Staff</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" id="edit_address">
                </div>
            </div>

            <div class="form-group">
                <label>New Password (Leave blank to keep current)</label>
                <input type="password" name="password" autocomplete="new-password" placeholder="******">
            </div>

            <div class="popup-actions">
                <button type="button" class="close-modal close-edit btn-cancel">Cancel</button>
                <button type="submit" class="btn-submit">Update</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteStaffModal">
    <div class="popup-box" style="width: 400px;">
        <div class="popup-header">
            <h3 style="color: #dc3545;">Delete Admin</h3>
            <span class="close-modal close-delete">&times;</span>
        </div>
        <p>Are you sure you want to delete this admin? This action cannot be undone.</p>

        <form action="" method="POST">
            <input type="hidden" name="action" value="delete_staff">
            <input type="hidden" name="admin_id" id="delete_admin_id">

            <div class="popup-actions">
                <button type="button" class="close-modal close-delete btn-cancel">Cancel</button>
                <button type="submit" class="btn-submit" style="background-color: #dc3545; border-color: #dc3545;">Delete</button>
            </div>
        </form>
    </div>
</div>

<div id="toast-container"></div>
<?php showToast(); ?>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const modal = document.getElementById("addStaffModal");
        const openBtn = document.getElementById("openAddModalBtn");
        const closeBtns = document.querySelectorAll(".close-modal");
        
        const editModal = document.getElementById("editStaffModal");
        const editBtns = document.querySelectorAll(".btn-edit");
        const closeEditBtns = document.querySelectorAll(".close-edit");
        
        const deleteModal = document.getElementById("deleteStaffModal");
        const deleteBtns = document.querySelectorAll(".btn-delete");
        const closeDeleteBtns = document.querySelectorAll(".close-delete");

        // --- Add Modal Logic ---
        if (openBtn) {
            openBtn.addEventListener("click", (e) => {
                e.preventDefault();
                const form = modal.querySelector('form');
                if (form) {
                    form.reset();
                    // 清空非 hidden 的 input
                    const inputs = form.querySelectorAll('input');
                    inputs.forEach(input => {
                        if (input.type !== 'hidden') input.value = '';
                    });
                }
                modal.style.setProperty('display', 'flex', 'important');
            });
        }

        // 通用的关闭 Modal 函数
        const closeModal = (targetModal) => {
            if(targetModal) targetModal.style.display = "none";
        };

        // 绑定所有关闭按钮 (X 号)
        closeBtns.forEach(btn => {
            btn.addEventListener("click", () => {
                closeModal(modal);
                closeModal(editModal);
                closeModal(deleteModal);
            });
        });

        // 点击背景关闭
        window.addEventListener("click", (e) => {
            if (e.target === modal) closeModal(modal);
            if (e.target === editModal) closeModal(editModal);
            if (e.target === deleteModal) closeModal(deleteModal);
        });

        // --- Custom Select Logic ---
        const customSelects = document.querySelectorAll(".custom-select");
        customSelects.forEach((select) => {
            const selected = select.querySelector(".selected");
            const optionsList = select.querySelectorAll(".options li");
            const hiddenInput = select.querySelector("input[type='hidden']");

            selected.addEventListener("click", (e) => {
                e.stopPropagation();
                customSelects.forEach((other) => {
                    if (other !== select) other.classList.remove("open");
                });
                select.classList.toggle("open");
            });

            optionsList.forEach((option) => {
                option.addEventListener("click", (e) => {
                    e.stopPropagation();
                    selected.innerText = option.innerText;
                    select.classList.remove("open");
                    if(hiddenInput) hiddenInput.value = option.getAttribute("data-value");
                    filterTable();
                });
            });
        });
        document.addEventListener("click", () => {
            customSelects.forEach((select) => select.classList.remove("open"));
        });

        // --- Search Filter ---
        const searchInput = document.getElementById("dataSearch");
        const rows = document.querySelectorAll(".data tbody tr");

        function filterTable() {
            if(!searchInput) return;
            const searchTerm = searchInput.value.toLowerCase();
            const positionInput = document.getElementById("positionFilter");
            const positionFilter = positionInput ? positionInput.value.toLowerCase() : "";

            rows.forEach((row) => {
                if (row.cells.length < 2) return;
                // 假设 Name 在第3和4列 (Index 2, 3)，Email 在第2列 (Index 1)
                const text = row.innerText.toLowerCase(); 
                const positionAttr = row.getAttribute("data-position");
                const position = positionAttr ? positionAttr.toLowerCase() : "";
                
                const matchesSearch = text.includes(searchTerm);
                const matchesPosition = positionFilter === "" || position === positionFilter;
                
                row.style.display = (matchesSearch && matchesPosition) ? "" : "none";
            });
        }
        
        if(searchInput) {
            searchInput.addEventListener("keyup", filterTable);
        }

        // --- Checkbox Logic ---
        // ⚠️ 修复：增加判断 actionButtons 是否存在，防止报错
        const checkboxes = document.querySelectorAll(".dataCheckbox");
        const actionButtons = document.getElementById("actionButtons");
        
        checkboxes.forEach(box => {
            box.addEventListener("change", () => {
                const checkedCount = document.querySelectorAll(".dataCheckbox:checked").length;
                if(actionButtons) {
                    actionButtons.style.display = checkedCount > 0 ? "flex" : "none";
                }
            });
        });

        // ⚠️ 修复：增加判断 viewBtn 是否存在，防止报错
        const viewBtn = document.getElementById("viewBtn");
        if (viewBtn) {
            viewBtn.addEventListener("click", () => {
                const checked = document.querySelectorAll(".dataCheckbox:checked");
                if (checked.length === 1) {
                    window.location.href = `adminProfile.php?id=${checked[0].value}`;
                } else {
                    alert("Please select only one admin.");
                }
            });
        }

        // --- 🟢 EDIT BUTTON LOGIC ---
        // 确保这段代码被执行到
        if(editBtns.length > 0) {
            editBtns.forEach(btn => {
                btn.addEventListener("click", (e) => {
                    e.preventDefault(); // 防止可能的跳转
                    console.log("Edit button clicked", btn.dataset.id); // 调试信息

                    // Populate form
                    const idInput = document.getElementById("edit_admin_id");
                    if(idInput) idInput.value = btn.dataset.id;

                    const fnameInput = document.getElementById("edit_firstName");
                    if(fnameInput) fnameInput.value = btn.dataset.fname;

                    const lnameInput = document.getElementById("edit_lastName");
                    if(lnameInput) lnameInput.value = btn.dataset.lname;

                    const emailInput = document.getElementById("edit_email");
                    if(emailInput) emailInput.value = btn.dataset.email;

                    const phoneInput = document.getElementById("edit_phone");
                    if(phoneInput) phoneInput.value = btn.dataset.phone;

                    const posInput = document.getElementById("edit_position");
                    if(posInput) posInput.value = btn.dataset.pos;

                    const addrInput = document.getElementById("edit_address");
                    if(addrInput) addrInput.value = btn.dataset.addr || "";

                    // Show modal
                    if(editModal) editModal.style.setProperty('display', 'flex', 'important');
                });
            });
        }

        // --- 🔴 DELETE BUTTON LOGIC ---
        if(deleteBtns.length > 0) {
            deleteBtns.forEach(btn => {
                btn.addEventListener("click", (e) => {
                    e.preventDefault();
                    console.log("Delete button clicked", btn.dataset.id); // 调试信息

                    const deleteIdInput = document.getElementById("delete_admin_id");
                    if(deleteIdInput) deleteIdInput.value = btn.dataset.id;
                    
                    if(deleteModal) deleteModal.style.setProperty('display', 'flex', 'important');
                });
            });
        }
        
        // 绑定 Modal 内部的 Cancel 按钮
        closeEditBtns.forEach(btn => btn.addEventListener("click", () => closeModal(editModal)));
        closeDeleteBtns.forEach(btn => btn.addEventListener("click", () => closeModal(deleteModal)));
    });
</script>