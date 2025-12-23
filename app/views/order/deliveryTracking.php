<?php
$title = "Delivery Tracking Page";
$pageCSS = "deliverytracking.css";

include '../header.php';
include '../../controllers/orderController.php';
require_once __DIR__ . '/../../helpers/auth.php';

authenticate();
$orderController = new OrderController();

if (!isset($_GET['id'])) {
  die("No order ID provided.");
}

$orderID = $_GET['id'];
$deliveryDetails = $orderController->getDeliveryDetails($orderID);
if (!empty($deliveryDetails)) {
  $orderStatuses = $orderController->getOrderStatus($orderID);
}

if ($deliveryDetails === false) {
  $deliveryDetails = [
    'shipment_id' => '-',
    'receiver_name' => '-',
    'receiver_phone' => '-',
    'receiver_address' => '-'
  ];
}

function getTrackingData($order_id, $deliveryDetails, $orderStatuses)
{
  $delivery_data = [
    'order_id' => $order_id,
    'shipment_id' => $deliveryDetails['shipment_id'],
    'receiver_name' => $deliveryDetails['receiver_name'],
    'receiver_phone' => $deliveryDetails['receiver_phone'],
    'receiver_address' => $deliveryDetails['receiver_address']
  ];

  $status_map = [
    'Paid' => [
      'description' => 'Order successfully placed and received by seller.',
      'location' => 'Online Platform',
    ],
    'Packing' => [
      'description' => 'Order is packed and ready to schedule delivery.',
      'location' => 'Seller Warehouse',
    ],
    'Out for Delivery' => [
      'description' => 'Your parcel is now out for delivery! Expect delivery soon.',
      'location' => 'Kuala Lumpur Hub',
    ],
    'Delivered' => [
      'description' => 'Your parcel has been delivered successfully!',
      'location' => $delivery_data['receiver_address'],
    ],
  ];

  $timeline = [];
  $latest_status_data = null;

  if (!empty($orderStatuses)) {
    foreach ($orderStatuses as $status_item) {
      $status_name = $status_item['order_status'];
      $datetime = $status_item['created_datetime'];

      if (isset($status_map[$status_name])) {
        $map_data = $status_map[$status_name];

        $timeline[] = [
          'status' => $status_name,
          'date' => $datetime,
          'location' => $map_data['location'],
          'description' => $map_data['description'],
        ];

        $latest_status_data = [
          'status' => $status_name,
          'latest_update' => $datetime . ', ' . $map_data['location'],
        ];
      }
    }
  }

  if (empty($timeline)) {
    return null;
  }

  return array_merge(
    $delivery_data,
    $latest_status_data,
    [
      'estimated_delivery' => calculateEstimatedDelivery($orderStatuses),
      'timeline' => array_reverse($timeline)
    ]
  );
}
if (!empty($orderStatuses) && !empty($deliveryDetails)) {
  $data = getTrackingData($orderID, $deliveryDetails, $orderStatuses);
}

function calculateEstimatedDelivery(array $orderStatuses): string
{
  usort($orderStatuses, function ($a, $b) {
    $timeA = strtotime($a['created_datetime']);
    $timeB = strtotime($b['created_datetime']);
    return $timeA <=> $timeB;
  });

  $paidDate = null;
  $outForDeliveryDate = null;

  foreach ($orderStatuses as $status_item) {
    $status = $status_item['order_status'];
    $datetime = $status_item['created_datetime'];

    if ($status === 'Paid' && $paidDate === null) {
      $paidDate = new DateTime($datetime);
    }

    if ($status === 'Out for Delivery') {
      $outForDeliveryDate = new DateTime($datetime);
    }
  }

  if ($outForDeliveryDate !== null) {
    $etaStart = clone $outForDeliveryDate;
    $etaStart->setTime(0, 0, 0);

    $etaEnd = clone $outForDeliveryDate;
    $etaEnd->modify('+1 day');

    $startDay = $etaStart->format('j');
    $endDayMonth = $etaEnd->format('j F Y');

    if ($etaStart->format('Y-m-d') === $etaEnd->format('Y-m-d')) {
      return $etaStart->format('j F Y');
    } else {
      return "$startDay - $endDayMonth";
    }
  }

  if ($paidDate !== null) {
    $etaStart = clone $paidDate;
    $etaStart->modify('+7 days');

    $etaEnd = clone $paidDate;
    $etaEnd->modify('+8 days');

    $startDay = $etaStart->format('j');
    $endDayMonth = $etaEnd->format('j F Y');

    if ($etaStart->format('F Y') === $etaEnd->format('F Y')) {
      return "$startDay - $endDayMonth";
    } else {
      $startFull = $etaStart->format('j F');
      return "$startFull - $endDayMonth";
    }
  }

  return "Estimation currently unavailable";
}

$steps = [
  'Processing',
  'Packing',
  'Out for Delivery',
  'Delivered'
];

if (!empty($data)) {
  $currentStatus = $data ? $data['status'] : '';
  $activeIndex = -1;
  if ($currentStatus) {
    if (strpos($currentStatus, 'Out for Delivery') !== false) {
      $currentStepName = 'Out for Delivery';
    } elseif (strpos($currentStatus, 'Packing') !== false) {
      $currentStepName = 'Packing';
    } elseif (strpos($currentStatus, 'Paid') !== false) {
      $currentStepName = 'Processing';
    } elseif (strpos($currentStatus, 'Delivered') !== false) {
      $currentStepName = 'Delivered';
    } else {
      $currentStepName = '';
    }

    $activeIndex = array_search($currentStepName, $steps);
  }

  $totalSteps = count($steps);

  $lineProgressWidth = 0;
  if ($activeIndex > 0) {
    $segmentProgress = $activeIndex / ($totalSteps - 1);
    $lineProgressWidth = $segmentProgress * 80;
  }

  $lineStyle = "style=\"--progress-width: " . $lineProgressWidth . "%;\"";

  $statusText = $data ? $data['status'] : 'Tracking Not Found';
}


?>

<div class="tracking-container">
  <div class="tracking-header">
    <a href="#" class="back-link" onclick="history.back(); return false;">&#x293A;</a>
    <h1>Delivery Tracking</h1>
  </div>

  <?php if (!empty($data)): ?>
    <div class="progress-stepper" <?= $lineStyle ?>>
      <?php foreach ($steps as $index => $step): ?>
        <?php
        $timelineEntry = null;
        foreach ($data['timeline'] as $tl) {
          if ($step === 'Processing' && $tl['status'] === 'Paid') {
            $timelineEntry = $tl;
            break;
          } elseif ($tl['status'] === $step) {
            $timelineEntry = $tl;
            break;
          }
        }
        $isDone = ($index <= $activeIndex);
        $isActive = ($index === $activeIndex);
        $stepClass = $isDone ? 'done' : '';
        if ($isActive) $stepClass .= ' active';
        ?>
        <div class="step <?= $stepClass ?>">
          <div class="icon-circle">
            <?php
            $iconMap = ['Processing' => '&#x1F4DC;', 'Packing' => '&#x1F4E6;', 'Out for Delivery' => '&#x1F69A;', 'Delivered' => '&#x2705;'];
            echo $iconMap[$step] ?? '&#x25CF;';
            ?>
          </div>
          <p class="step-label"><?= $step ?></p>
          <?php if ($timelineEntry): ?>
            <p class="timeline-label"><?= $timelineEntry['date'] ?></p>
          <?php else: ?>
            <p class="timeline-label">Not yet</p>
          <?php endif; ?>
        </div>

      <?php endforeach; ?>
    </div>

    <hr class="separator">

    <div class="tracking-timeline">
    </div>

    <div class="summary-cards-container">

      <div class="tracking-card partner-card">
        <div class="card-icon">&#x1F69A;</div>
        <div class="card-content">
          <p class="card-label">Delivery Partner</p>
          <h3 class="card-title">Lovine Express</h3>
          <p class="card-detail">Tracking ID: <strong><?= htmlspecialchars($data['shipment_id']) ?></strong></p>
        </div>
      </div>

      <div class="tracking-card receiver-card">
        <div class="card-icon">&#x1F464;</div>
        <div class="card-content">
          <p class="card-label">Receiver Information</p>
          <h3 class="card-title"><?= htmlspecialchars($data['receiver_name']) ?></h3>
          <p class="card-detail"><?= htmlspecialchars($data['receiver_phone']) ?></p>
          <p class="card-detail address-detail"><?= nl2br(htmlspecialchars($data['receiver_address'])) ?></p>
        </div>
      </div>

      <div class="tracking-card status-card">
        <div class="status-box">
          <span class="main-status"><?= $statusText ?></span>
          <span class="latest-update"><?= htmlspecialchars($data['latest_update']) ?></span>
        </div>
        <p class="delivery-estimate">Est. Delivery: <strong><?= htmlspecialchars($data['estimated_delivery']) ?></strong></p>
      </div>

    </div>

    <div class="tracking-timeline">
      <h3>Shipment History</h3>
      <ul id="timelineList">
        <?php
        $timeline = $data['timeline'];
        $isFirst = true;
        foreach ($timeline as $event):
          $datetimeParts = explode(' ', $event['date']);
          $datePart = $datetimeParts[0] ?? ''; // e.g., '2025-12-13'
          $timePart = $datetimeParts[1] ?? ''; // e.g., '01:46:17'
        ?>
          <li class="timeline-event <?= $isFirst ? 'active' : '' ?>">
            <div class="timeline-date-time">
              <span class="date"><?= htmlspecialchars($datePart) ?></span>
              <span class="time"><?= htmlspecialchars($timePart) ?></span>
            </div>
            <div class="timeline-icon"></div>
            <div class="timeline-details">
              <p class="description"><?= htmlspecialchars($event['description']) ?></p>
              <p class="location"><?= htmlspecialchars($event['location']) ?></p>
            </div>
          </li>
        <?php
          $isFirst = false;
        endforeach;
        ?>
      </ul>
    </div>
  <?php else: ?>
    <p class="no-order">No delivery details found.</p>
  <?php endif; ?>

</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const timelineEvents = document.querySelectorAll('.timeline-event');

    function initializeTimeline() {
      timelineEvents.forEach(event => {
        if (event.classList.contains('active')) {
          console.log('Latest event:', event.querySelector('.description').textContent);
        }
      });
    }

    timelineEvents.forEach(event => {
      event.addEventListener('click', function() {
        this.classList.toggle('expanded');
      });
    });

    initializeTimeline();
  });
</script>

<?php include '../footer.php' ?>