<?php
$title = "Product Maintenance";
include '../../controllers/productsController.php';
require_once __DIR__ . '/../../helpers/auth.php';

authenticate();
$current_sort = $_GET['sort'] ?? 'customer_id';
$current_order = $_GET['order'] ?? 'asc';
$templateId = $_GET['template_id'] ?? 1; // Default template

// Hardcoded WhatsApp message templates
$whatsappTemplates = [
    1 => [
        'name' => 'Default Back in Stock',
        'text' => "🎉 Hi {firstname}! 🛒\nThe product {product_name} is back in stock!\n🔹 Still have {stock_qty} left\nCheck it out: {product_link}"
    ],
    2 => [
        'name' => 'Promo Message',
        'text' => "🔥 Hi {firstname}! {product_name} is back in stock and 10% off today!\nGet it now: {product_link}"
    ],
    3 => [
        'name' => 'Short Alert',
        'text' => "⚡ {firstname}, {product_name} is available now! {product_link}"
    ]
];

// Helper function to replace placeholders
function renderTemplate($templateText, $customer, $item)
{
    $replacements = [
        '{firstname}' => $customer['firstname'] ?? '',
        '{lastname}' => $customer['lastname'] ?? '',
        '{product_name}' => $item['product_name'] ?? '',
        '{product_id}' => $item['product_id'] ?? '',
        '{stock_qty}' => $item['stock_qty'] ?? '',
        '{product_link}' => "http://localhost:8000/app/views/product/product_details.php?id={$item['product_id']}" ?? ''
    ];
    return str_replace(array_keys($replacements), array_values($replacements), $templateText);
}

$controller = new productsController();
if (isset($_GET['action']) && $_GET['action'] === 'systemCall' && isset($_GET['phone'])) {
    $phone = $_GET['phone'];
    $cname = $_GET['cname'] ?? 'Customer';
    $pname = $_GET['pname'] ?? 'item';

    $controller->systemCall($phone, $cname, $pname);

    $current_page = strtok($_SERVER["REQUEST_URI"], '?');
    header("Location: $current_page?call_active=true&called_num=" . urlencode($phone) . "&cname=" . urlencode($cname));
    exit;
}
$wishlistItems = $controller->getAllUserWishlist($current_sort, $current_order) ?: [];

$demandCounts = [];
if (is_iterable($wishlistItems)) {
    foreach ($wishlistItems as $item) {
        $v_id = $item['product_variant_id'];
        $demandCounts[$v_id] = ($demandCounts[$v_id] ?? 0) + 1;
    }
}
$demandCounts = [];
foreach ($wishlistItems as $item) {
    $v_id = $item['product_variant_id'];
    $demandCounts[$v_id] = ($demandCounts[$v_id] ?? 0) + 1;
}

$customers = [];
foreach ($wishlistItems as $item) {
    $customer_id = $item['customer_id'];
    if (!isset($customers[$customer_id])) {
        $customers[$customer_id] = [
            'customer_id' => $customer_id,
            'firstname'   => $item['firstname'],
            'lastname'    => $item['lastname'],
            'phone'       => $item['phone'],
            'items'       => []
        ];
    }
    $customers[$customer_id]['items'][] = $item;
}
$customerCount = count($customers);

include '../adminHeader.php';
?>

<head>
    <meta charset="UTF-8">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="/public/css/remindUser.css">
    <link rel="stylesheet" href="/public/css/lowStockAlert.css">
</head>

<body>
    <div class="container">
        <div class="backInStock-header">
            <a href="#" class="back-link" onclick="window.location.href='/app/views/adminDashboard.php'">&#x293A;</a>
            <h1>Back In Stock Management</h1>
        </div>
        <div class="controls-row">
            <div class="search_sort">
                <input
                    type="text"
                    class="form-control"
                    id="searchInput"
                    placeholder="Search by Customer ID, Name, Phone, Product, Variant ID">

                <div class="pagination">
                    <select name="rowPerPage" id="rowPerPage">
                        <?php
                        $options = [5, 10, 20, 50];

                        foreach ($options as $opt) {
                            if ($opt < $customerCount) {
                                echo "<option value='$opt'>$opt</option>";
                            }
                        }

                        echo "<option value='$customerCount' selected>All ($customerCount)</option>";
                        ?>
                    </select>

                </div>
            </div>

            <form id="backInStockForm" method="get">
                <label for="templateSelect">Template: </label>
                <select id="templateSelect" name="template_id">
                    <?php foreach ($whatsappTemplates as $id => $tpl): ?>
                        <option value="<?php echo $id; ?>" <?php echo $id == $templateId ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tpl['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" id="applyTemplateButton">Apply</button>
            </form>
        </div>

        <table class="back-in-stock-table">
            <thead>
                <tr>
                    <th><a href="?sort=customer_id&order=<?php echo $current_sort === 'customer_id' && $current_order === 'asc' ? 'desc' : 'asc'; ?>">Customer ID <?php echo $current_sort === 'customer_id' ? ($current_order === 'asc' ? '▲' : '▼') : ''; ?></a></th>
                    <th><a href="?sort=firstname&order=<?php echo $current_sort === 'firstname' && $current_order === 'asc' ? 'desc' : 'asc'; ?>">Name <?php echo $current_sort === 'firstname' ? ($current_order === 'asc' ? '▲' : '▼') : ''; ?></a></th>
                    <th>Phone</th>
                    <th><a href="?sort=product_name&order=<?php echo $current_sort === 'product_name' && $current_order === 'asc' ? 'desc' : 'asc'; ?>">Product <?php echo $current_sort === 'product_name' ? ($current_order === 'asc' ? '▲' : '▼') : ''; ?></a></th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer):

                    $product_lines = [];
                    foreach ($customer['items'] as $item) {
                        $product_lines[] = "• " . $item['product_name'];
                    }
                    $itemListText = implode("\n", $product_lines);

                    $messageText = renderTemplate($whatsappTemplates[$templateId]['text'], $customer, $customer['items'][0]);
                    $wa_phone = '6' . preg_replace('/[^0-9]/', '', $customer['phone']);
                    $wa_link = "https://wa.me/{$wa_phone}?text=" . rawurlencode($messageText);
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer['customer_id']); ?></td>
                        <td><strong><?php echo htmlspecialchars($customer['firstname'] . ' ' . $customer['lastname']); ?></strong></td>
                        <td>
                            <!-- Display the phone number -->
                            <span class="phone-number-display"><?php echo htmlspecialchars($customer['phone']); ?></span>

                            <!-- System call link -->
                            <a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=systemCall&phone=<?php echo urlencode($customer['phone']); ?>&cname=<?php echo urlencode($customer['firstname']); ?>&pname=<?php echo urlencode($customer['items'][0]['product_name']); ?>"
                                class="phone-link"
                                aria-label="System Call"
                                onclick="return confirm('Initiate Twilio system call to <?php echo $customer['phone']; ?>?')">
                                <svg class="phone-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="16" height="16" fill="currentColor">
                                    <path d="M164.9 24.6c-7.7-18.6-28-28.5-47.4-23.2l-88 24C12.1 30.2 0 46 0 64C0 311.4 200.6 512 448 512c18 0 33.8-12.1 38.6-29.5l24-88c5.3-19.4-4.6-39.7-23.2-47.4l-96-40c-16.3-6.8-35.2-2.1-46.3 11.6L304.7 368C234.3 334.7 177.3 277.7 144 207.3L193.3 167c13.7-11.2 18.4-30 11.6-46.3l-40-96z" />
                                </svg>
                            </a>

                            <!-- WhatsApp sent log container -->
                            <div class="log-container-<?php echo preg_replace('/[^0-9]/', '', $customer['phone']); ?>"></div>

                            <!-- Call info container for JS -->
                            <div id="callInfoContainer-<?php echo preg_replace('/[^0-9]/', '', $customer['phone']); ?>" class="call-info-container"></div>
                        </td>

                        <td>
                            <div class="nested-product-container">
                                <?php foreach ($customer['items'] as $item):
                                    $v_id = $item['product_variant_id'];
                                    $stock = $item['stock_qty'];
                                    $waiting = $demandCounts[$v_id] ?? 0;

                                    $priorityClass = $controller->getPriorityClass($waiting, $stock);

                                    $img_url = $item['img_url'] ?? '';
                                    $display_img = "../../../public/images/{$item['category_name']}/{$img_url}";
                                ?>
                                    <div class="product-item-row <?php echo $priorityClass; ?>">
                                        <img src="<?php echo $display_img; ?>" class="thumb-img" alt="Product">
                                        <div class="product-details">
                                            <span class="p-name"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                            <span class="p-id">
                                                ID: <?php echo htmlspecialchars($v_id); ?> |
                                                Stock: <?php echo $stock; ?> |
                                                <strong>Waiting: <?php echo $waiting; ?></strong>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </td>

                        <td class="action-cell">
                            <div class="action-wrapper">
                                <textarea class="templatePreview" rows="10" readonly><?php echo htmlspecialchars($messageText); ?></textarea>

                                <div class="button-group">
                                    <a href="<?php echo $wa_link; ?>"
                                        target="_blank"
                                        class="notify-btn js-send-btn"
                                        data-customer-id="<?php echo $customer['customer_id']; ?>">
                                        Send WhatsApp
                                    </a>

                                    <span class="sent-status" id="status-<?php echo $customer['customer_id']; ?>"></span>
                                </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="cancel">
            <button onclick="
        localStorage.removeItem('wa_sent_log');
        localStorage.removeItem('call_log');
        location.reload();
    " class="btn-clear">
                Reset All "Sent" & "Call" Badges
            </button>
        </div>


        <div id="paginationControls" class="pagination-controls-bar"></div>
    </div>
    <script src="/public/js/remindUser.js"></script>
</body>