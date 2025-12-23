<?php
require_once __DIR__ . '/../models/orderModel.php';
require_once __DIR__ . '/../models/userModel.php';
require_once __DIR__ . '/../../app/helpers/mail.php';
require_once __DIR__ . '/../lib/Pagination.php';

class OrderController
{
  private $orderModel;
  private $userModel;

  public function __construct()
  {
    $this->orderModel = new OrderModel();
    $this->userModel = new UserModel();
  }

  public function index()
  {
    $filters = [
      'page'   => $_GET['page'] ?? 1,
      'status' => $_GET['status'] ?? '',
      'search' => $_GET['search'] ?? '',
      'sort'   => $_GET['sort'] ?? 'order_id',
      'dir'    => $_GET['dir'] ?? 'asc'
    ];

    $total = $this->orderModel->countOrders($filters['status']);
    $pagination = new Pagination($total, 10, $filters['page']);

    $orders = $this->orderModel->getOrders(
      $pagination->offset,
      $pagination->recordsPerPage,
      $filters['status'],
      $filters['search'],
      $filters['sort'],
      $filters['dir']
    );

    return [
      'orders'     => $orders,
      'pagination' => $pagination,
      'filters'    => $filters
    ];
  }

  public function getCustomerProducts()
  {
    if (!isset($_GET['id'])) {
      die("No customer ID provided.");
    }

    $custID = $_GET['id'];
    $products = $this->orderModel->getCustomerProducts($custID);

    return $products;
  }

  public function getOrderItems()
  {
    if (!isset($_GET['id'])) {
      die("No customer ID provided.");
    }

    $custID = $_GET['id'];
    $items = $this->orderModel->getOrder($custID);

    return $items;
  }

  public function getOrderDetails()
  {
    if (!isset($_GET['id'])) {
      die("No order ID provided.");
    }

    $customerId = $_SESSION['customerId'] ?? null;

    $orderID = $_GET['id'];
    $items = $this->orderModel->getDetails($orderID, $customerId);

    return $items;
  }

  public function getOrderStatus($orderId)
  {
    $custId = $_SESSION['customerId'] ?? null;

    $items = $this->orderModel->getAllStatus($orderId, $custId);

    return $items;
  }

  public function getReviewOrders($orderId)
  {
    $items = $this->orderModel->getReviewOrderDetails($orderId);

    return $items;
  }

  public function getReviewOrderDetails()
  {
    if (!isset($_GET['order_id']) && !isset($_GET['variant_id'])) {
      die("No ID provided.");
    }
    $orderID = $_GET['order_id'];
    $variantID = $_GET['variant_id'];
    $items = $this->orderModel->getReviewOrderDetailsByID($orderID, $variantID);

    return $items;
  }

  public function getDeliveryDetails($orderID)
  {
    $custId = $_SESSION['customerId'];
    return $this->orderModel->getDelivery($orderID, $custId);
  }

  public function updateStatus($orderId, $newStatus)
  {
    if (!$orderId || trim($newStatus) === '') {
      return false;
    }

    $newStatus = trim($newStatus);

    $updated = $this->orderModel->adminUpdateOrderStatus($orderId, $newStatus);
    if (!$updated) {
      return false;
    }

    if ($newStatus === "Packing") {
      $this->orderModel->createShipment($orderId, $newStatus);
    }

    if ($newStatus === "Out for Delivery" || $newStatus === "Delivered") {
      $this->orderModel->updateShipment($orderId, $newStatus);
    }

    if ($newStatus === "Cancelled") {
      $refundUpdated = $this->orderModel->updateRefundStatus($orderId);
      $emailSent = $this->sendCancelApproveEmail($orderId);
      return $refundUpdated && $emailSent;
    }

    if ($newStatus === "Completed") {
      $order = $this->orderModel->getOrderById($orderId);

      if ($order) {
        $points = floor($order['total_amount']);
        $custId = $order['customer_id'];

        $this->userModel->updatePoint($custId, $points);
        $this->orderModel->updateOrderReward($orderId, $points);
        $this->orderModel->updateProductTotalSold($orderId);
      }
    }
    return true;
  }

  public function getTopOrders()
  {
    return $this->orderModel->getTopOrders();
  }

  public function getTopCategory()
  {
    return $this->orderModel->getOrdersGroupByCategory();
  }
  public function getOrdersByStatus()
  {
    return $this->orderModel->getOrdersByStatus();
  }

  public function getOrdersFilterByTime($period)
  {
    return $this->orderModel->getOrdersByTimeline($period);
  }

  public function cancelOrder($orderId, $reason)
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      return false;
    }

    if (empty($reason)) {
      $_SESSION['error_message'] = "Cancellation reason is required.";
      return;
    }

    return $this->orderModel->saveCancellation($orderId);
  }

  public function sendCancelRequestEmail($orderId)
  {
    $cust = $this->orderModel->getCustInfoByOrderId($orderId);
    if ($cust && !empty($cust['email'])) {
      $fullName = $cust['firstname'] . ' ' . $cust['lastname'];
      sendCancelOrderReceived($orderId, $cust['email'], $fullName);
    }
  }

  public function sendCancelApproveEmail($orderId)
  {
    $cust = $this->orderModel->getCustInfoByOrderId($orderId);
    if ($cust && !empty($cust['email'])) {
      $fullName = $cust['firstName'] . ' ' . $cust['lastName'];
      sendCancelOrderApproved($orderId, $cust['email'], $fullName);
    }
  }

  public function completeOrder()
  {
    $orderId = $_POST['order_id'] ?? null;

    if (!$orderId) {
      echo json_encode(['success' => false, 'message' => 'Missing Order ID']);
      return;
    }

    $order = $this->orderModel->getOrderById($orderId);

    if (!$order) {
      echo json_encode(['success' => false, 'message' => 'Order not found']);
      return;
    }

    if (!isset($_SESSION['customerId']) || $order['customer_id'] !== $_SESSION['customerId']) {
      echo json_encode(['success' => false, 'message' => 'Unauthorized']);
      return;
    }

    $statusRow = $this->orderModel->getOrderStatusById($orderId);
    $currentStatus = $statusRow ? $statusRow['order_status'] : '';

    if ($currentStatus === 'Completed') {
      echo json_encode(['success' => false, 'message' => 'Order already completed']);
      return;
    }

    $result = $this->updateStatus($orderId, 'Completed');

    if ($result) {
      $pointsEarned = floor($order['total_amount']);
      $_SESSION['success_message'] = "Order completed! You earned $pointsEarned points.";

      echo json_encode(['success' => true]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Database update failed']);
    }
  }
}
