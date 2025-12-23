<?php
$title = "Admin Dashboard";
$pageCSS = "admindashboard.css";


require_once __DIR__ . '/../controllers/orderController.php';
require_once __DIR__ . '/../helpers/html.php';
$orderController = new OrderController();
$topOrders = $orderController->getTopOrders();
$topCategory = $orderController->getTopCategory();
$statusOrders = $orderController->getOrdersByStatus();

$pendingOrders = 0;
$packingOrders = 0;
$cancellationRequests = 0;

foreach ($statusOrders as $status) {
  if ($status['order_status'] === 'Pending') {
    $pendingOrders = $status['numOfOrder'];
  } elseif ($status['order_status'] === 'Paid') {
    $packingOrders = $status['numOfOrder'];
  } elseif ($status['order_status'] === 'Cancelled') {
    $cancellationRequests = $status['numOfOrder'];
  }
}

$weeklyOrders = $orderController->getOrdersFilterByTime('week');
$monthlyOrders = $orderController->getOrdersFilterByTime('month');
$yearlyOrders = $orderController->getOrdersFilterByTime('year');
$orderData = [
  'week' => $weeklyOrders,
  'month' => $monthlyOrders,
  'year' => $yearlyOrders
];

function fillEmptyPeriods($data)
{
  $filled = [
    'week' => [],
    'month' => [],
    'year' => []
  ];

  // --- Week: Mon-Sun ---
  $weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  foreach ($weekDays as $day) {
    $found = false;
    foreach ($data['week'] as $d) {
      if ($d['period_label'] === $day) {
        $filled['week'][] = $d;
        $found = true;
        break;
      }
    }
    if (!$found) $filled['week'][] = ['period_label' => $day, 'numOfOrder' => 0];
  }

  // --- Month: Week 1 - Week 4 (adjust depending on month) ---
  $weeksInMonth = 4; // or 5
  for ($i = 1; $i <= $weeksInMonth; $i++) {
    $label = "Week $i";
    $found = false;
    if (!empty($data['month'])) {
      foreach ($data['month'] as $d) {
        if ("Week " . $d['period_label'] === $label) {
          $filled['month'][] = $d;
          $found = true;
          break;
        }
      }
    }
    if (!$found) $filled['month'][] = ['period_label' => $label, 'numOfOrder' => 0];
  }

  // --- Year: Jan-Dec ---
  $months = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December'
  ];
  foreach ($months as $month) {
    $found = false;
    if (!empty($data['year'])) {
      foreach ($data['year'] as $d) {
        if ($d['period_label'] === $month) {
          $filled['year'][] = $d;
          $found = true;
          break;
        }
      }
    }
    if (!$found) $filled['year'][] = ['period_label' => $month, 'numOfOrder' => 0];
  }

  return $filled;
}

$fullOrderData = fillEmptyPeriods($orderData);
require_once __DIR__ . '/adminheader.php';
?>

<div class="dashboard">
  <h1>Staff Dashboard</h1>

  <!-- Summary Cards -->
  <div class="cards">
    <div class="card pending">
      <h3>Pending Orders</h3>
      <p><?= $pendingOrders ?></p>
    </div>
    <div class="card packing">
      <h3>Packing Orders</h3>
      <p><?= $packingOrders ?></p>
    </div>
    <div class="card cancel">
      <h3>Cancellation Requests</h3>
      <p><?= $cancellationRequests ?></p>
    </div>

    <div class="card low-stock">
      <a href="/app/views/product/lowStockAlert.php" class="card-link"></a>
      <h3>Low Stock</h3>
      <p>&gt;</p>
    </div>
  </div>

  <div class="chart-card-wrapper">
    <div class="chart-card">
      <h3>Top 5 Most Ordered Products</h3>

      <div class="bar-chart">
        <?php $i = 0 ?>
        <?php foreach ($topOrders as $order): ?>

          <div class="bar-item">
            <span class="label"><?= $order['product_name'] ?></span>
            <div class="bar">
              <div class="bar-fill" data-value=<?= $order['numOfOrder'] ?>></div>

            </div>
            <span class="value"><?= $order['numOfOrder'] ?></span>
          </div>
          <?php $i++ ?>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="pie-chart-wrapper">
      <h3>Category Orders</h3>
      <div id="categoryPie" class="pie-chart"></div>

      <ul class="pie-legend">
        <?php foreach ($topCategory as $i => $category): ?>
          <li>
            <span class="legend-color color-<?= $i ?>"></span>
            <?= $category['category_name'] ?> (<?= $category['numOfOrder'] ?>)
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

  </div>

  <div class="order-chart-card">
    <div class="chart-header">
      <h3>Number of Orders in 2025</h3>

      <div class="custom-select" id="statusSelect">
        <input type="hidden" id="statusFilter" value="week">
        <div class="selected">Week</div>
        <ul class="options">
          <li data-value="week">Week</li>
          <li data-value="month">Month</li>
          <li data-value="year">Year</li>
        </ul>
      </div>
    </div>
    <div id="orderChart" class="order-bar-chart"></div>
  </div>

</div>
<?php showToast(); ?>
<script>
  const bars = document.querySelectorAll(".bar-fill");
  const orderData = <?= json_encode($fullOrderData) ?>;
  const statusSelect = document.getElementById('statusSelect');
  const selected = statusSelect.querySelector('.selected');
  const options = statusSelect.querySelectorAll('.options li');
  const statusFilterInput = document.getElementById('statusFilter');

  selected.addEventListener('click', () => {
    statusSelect.classList.toggle('open');
  });

  options.forEach(option => {
    option.addEventListener('click', () => {
      const value = option.dataset.value;
      selected.textContent = option.textContent;
      statusFilterInput.value = value;
      statusSelect.classList.remove('open');

      // Load the chart based on the selected period
      loadChart(value);
    });
  });

  // Close dropdown when clicking outside
  document.addEventListener('click', (e) => {
    if (!statusSelect.contains(e.target)) {
      statusSelect.classList.remove('open');
    }
  });

  const maxValue = Math.max(
    ...Array.from(bars).map(bar => parseInt(bar.dataset.value))
  );

  bars.forEach(bar => {
    const value = bar.dataset.value;
    const percentage = (value / maxValue) * 100;
    bar.style.width = percentage + "%";
  });

  function loadChart(type) {
    const chart = document.getElementById("orderChart");
    chart.innerHTML = "";

    const maxValue = Math.max(...orderData[type].map(d => d.numOfOrder));

    orderData[type].forEach(item => {
      const orderbar = document.createElement("div");
      orderbar.className = "order-bar";
      orderbar.style.height = (item.numOfOrder / maxValue * 100) + "%";

      orderbar.innerHTML = `
      <div class="order-bar-value">${item.numOfOrder}</div>
      <span>${item.period_label}</span>
    `;

      chart.appendChild(orderbar);
    });
  }

  // Load default (week)
  loadChart("week");

  const categoryData = <?= json_encode($topCategory) ?>;
  const pie = document.getElementById("categoryPie");

  const total = categoryData.reduce(
    (sum, item) => sum + parseInt(item.numOfOrder),
    0
  );

  let currentAngle = 0;
  const colors = ["rgba(128, 61, 252, 1)", "rgba(155, 104, 252, 1)", "rgba(175, 135, 248, 1)", "rgb(193, 165, 245)", "rgb(221, 208, 246)", ];
  const segments = [];

  categoryData.forEach((item, index) => {
    const value = parseInt(item.numOfOrder);
    const angle = (value / total) * 360;

    segments.push(
      `${colors[index]} ${currentAngle}deg ${currentAngle + angle}deg`
    );

    currentAngle += angle;
  });

  pie.style.background = `conic-gradient(${segments.join(",")})`;
</script>