<?php
require_once __DIR__ . '/../models/orderModel.php';
require_once __DIR__ . '/../lib/Pagination.php';

class OrderController
{
  private $orderModel;

  public function __construct()
  {
    $this->orderModel = new OrderModel();
  }

  public function index()
  {

    // Count total records
    $total = $this->orderModel->countOrders();

    // Get current page
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    // Create pagination object
    $pagination = new Pagination($total, 10, $page); // 10 rows per page

    // Fetch paginated members
    $orders = $this->orderModel->getOrders($pagination->offset, $pagination->recordsPerPage);

    return [
      "orders" => $orders,
      "pagination" => $pagination
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

    $orderID = $_GET['id'];
    $items = $this->orderModel->getDetails($orderID);

    return $items;
  }

  public function getOrderStatus($orderId)
  {
    $items = $this->orderModel->getAllStatus($orderId);

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
    return $this->orderModel->getDelivery($orderID);
  }

  public function updateStatus($order_id, $newStatus)
  {
    if (empty($order_id) || empty($newStatus)) {
      return false;
    }
    $newStatus = trim($_POST['status']);
    if ($newStatus === "Packing") {
      return $this->orderModel->createShipment($order_id, $newStatus);
    } else {
      return $this->orderModel->adminUpdateOrderStatus($order_id, $newStatus);
    }
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

    $cancel = $this->orderModel->saveCancellation($orderId);

    if ($cancel) {
      $_SESSION['success_message'] = "Order cancellation request submitted successfully.";
    } else {
      $_SESSION['error_message'] = "Failed to cancel order.";
    }

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
  }
}
