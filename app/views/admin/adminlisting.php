<?php
// Handle JSON requests
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
$pageCSS = "datalisting.css";

require_once __DIR__ . '/../../controllers/adminController.php';

$adminController = new adminController();

// --- HANDLE FORM SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_staff') {
    $adminController->addStaff();
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

            <div id="actionButtons" style="display:none;">
                <button id="viewBtn">View Details</button>
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

<div id="toast-container"></div>
<?php showToast(); ?>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const modal = document.getElementById("addStaffModal");
        const openBtn = document.getElementById("openAddModalBtn");
        const closeBtns = document.querySelectorAll(".close-modal");

        if (openBtn) {
            openBtn.addEventListener("click", (e) => {
                e.preventDefault();

                const form = modal.querySelector('form');
                if (form) {
                    form.reset();
                    const inputs = form.querySelectorAll('input');
                    inputs.forEach(input => {
                        if (input.type !== 'hidden') {
                            input.value = '';
                        }
                    });
                }

                console.log("Opening Modal...");
                modal.style.setProperty('display', 'flex', 'important');
            });
        }
        const closeModal = () => {
            modal.style.display = "none";
        };

        closeBtns.forEach(btn => btn.addEventListener("click", closeModal));

        window.addEventListener("click", (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });

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
                    hiddenInput.value = option.getAttribute("data-value");
                    filterTable();
                });
            });
        });
        document.addEventListener("click", () => {
            customSelects.forEach((select) => select.classList.remove("open"));
        });

        // Search Filter
        const searchInput = document.getElementById("dataSearch");
        const rows = document.querySelectorAll(".data tbody tr");

        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();
            const positionFilter = document.getElementById("positionFilter").value.toLowerCase();

            rows.forEach((row) => {
                if (row.cells.length < 2) return;
                const text = row.innerText.toLowerCase();
                const position = row.getAttribute("data-position").toLowerCase();
                const matchesSearch = text.includes(searchTerm);
                const matchesPosition = positionFilter === "" || position === positionFilter;
                row.style.display = (matchesSearch && matchesPosition) ? "" : "none";
            });
        }
        searchInput.addEventListener("keyup", filterTable);

        // Checkbox Logic
        const checkboxes = document.querySelectorAll(".dataCheckbox");
        const actionButtons = document.getElementById("actionButtons");
        const viewBtn = document.getElementById("viewBtn");

        checkboxes.forEach(box => {
            box.addEventListener("change", () => {
                const checkedCount = document.querySelectorAll(".dataCheckbox:checked").length;
                actionButtons.style.display = checkedCount > 0 ? "flex" : "none";
            });
        });

        viewBtn.addEventListener("click", () => {
            const checked = document.querySelectorAll(".dataCheckbox:checked");
            if (checked.length === 1) {
                window.location.href = `adminProfile.php?id=${checked[0].value}`;
            } else {
                alert("Please select only one admin.");
            }
        });
    });
</script>