<?php
require_once __DIR__ . '/../config/database.php';

class OrderModel
{
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  public function createOrder($customerId, $addressId, $items, $totalAmount, $shippingFee, $taxFee, $totalOrderQty, $pointsRedeemed, $paymentMethod)
  {

    $this->db->query("START TRANSACTION");
    $this->db->execute();

    try {
      $orderId = $this->db->generateId('`order`', 'order_id', 'O');

      $sqlOrder = "INSERT INTO `order` (order_id, customer_id, address_id, total_amount, tax_fee, total_order_qty, redeemed_point) VALUES (?, ?, ?, ?, ?, ?, ?)";

      $this->db->query($sqlOrder);
      $this->db->bind(1, $orderId);
      $this->db->bind(2, $customerId);
      $this->db->bind(3, $addressId);
      $this->db->bind(4, $totalAmount);
      $this->db->bind(5, $taxFee);
      $this->db->bind(6, $totalOrderQty);
      $this->db->bind(7, $pointsRedeemed);
      $this->db->execute();

      $orderStatusId = $this->db->generateId('orderStatus', 'order_status_id', 'OS');

      $sqlStatus = "INSERT INTO orderStatus (order_status_id, order_status, created_datetime, order_id) 
                          VALUES (?, 'Pending', NOW(), ?)";

      $this->db->query($sqlStatus);
      $this->db->bind(1, $orderStatusId);
      $this->db->bind(2, $orderId);
      $this->db->execute();

      foreach ($items as $item) {
        $sqlItem = "INSERT INTO order_items (order_id, product_variant_id, price, order_qty) 
                            VALUES (?, ?, ?, ?)";
        $this->db->query($sqlItem);
        $this->db->bind(1, $orderId);
        $this->db->bind(2, $item['variant_id']);
        $this->db->bind(3, $item['unit_price']);
        $this->db->bind(4, $item['quantity']);
        $this->db->execute();

        $sqlStock = "UPDATE product_variant SET stock_qty = stock_qty - ? WHERE product_variant_id = ?";
        $this->db->query($sqlStock);
        $this->db->bind(1, $item['quantity']);
        $this->db->bind(2, $item['variant_id']);
        $this->db->execute();
      }

      $paymentId = $this->db->generateId('payment', 'payment_id', 'PM');

      $sqlPayment = "INSERT INTO payment (payment_id, order_id, amount, payment_method, payment_status, created_datetime) 
                           VALUES (?, ?, ?, ?, 'Unpaid', NOW())";

      $this->db->query($sqlPayment);
      $this->db->bind(1, $paymentId);
      $this->db->bind(2, $orderId);
      $this->db->bind(3, $totalAmount);
      $this->db->bind(4, $paymentMethod);
      $this->db->execute();

      $this->db->query("SELECT cart_id FROM cart WHERE customer_id = ?");
      $this->db->bind(1, $customerId);
      $cartRow = $this->db->result();

      if ($cartRow) {
        $cartId = $cartRow['cart_id'];
        foreach ($items as $item) {
          $this->db->query("DELETE FROM cart_items WHERE cart_id = ? AND product_variant_id = ? AND cart_status = 0");
          $this->db->bind(1, $cartId);
          $this->db->bind(2, $item['variant_id']);
          $this->db->execute();
        }
      }

      if ($pointsRedeemed > 0) {
        $this->db->query("SELECT rewardPoint FROM customer WHERE customer_id = ?");
        $this->db->bind(1, $customerId);
        $result = $this->db->result();
        $currentPoints = $result['rewardPoint'] ?? 0;

        if ($currentPoints < $pointsRedeemed) {
          throw new Exception("Insufficient points.");
        }

        $this->db->query("UPDATE customer SET rewardPoint = rewardPoint - ? WHERE customer_id = ?");
        $this->db->bind(1, $pointsRedeemed);
        $this->db->bind(2, $customerId);
        $this->db->execute();
      }

      $this->db->query("COMMIT");
      $this->db->execute();

      return $paymentId;
    } catch (Exception $e) {
      $this->db->query("ROLLBACK");
      $this->db->execute();
      return false;
    }
  }

  public function getSelectedCartItems($customerId, $selectedVariantIds)
  {
    if (empty($selectedVariantIds)) {
      return [];
    }

    $results = [];
    foreach ($selectedVariantIds as $vid) {
      $sql = "SELECT 
                        ci.quantity,
                        pv.product_variant_id, 
                        v.variant_name,
                        p.product_name, 
                        p.sale_price, 
                        pv.img_url as variant_img, 
                        p.img_url as main_img
                    FROM cart c
                    JOIN cart_items ci ON c.cart_id = ci.cart_id
                    JOIN product_variant pv ON ci.product_variant_id = pv.product_variant_id
                    JOIN product p ON pv.product_id = p.product_id
                    LEFT JOIN variant v ON pv.variant_id = v.variant_id
                    WHERE c.customer_id = ?
                    AND ci.product_variant_id = ?
                    AND ci.cart_status = 0";

      $this->db->query($sql);
      $this->db->bind(1, $customerId);
      $this->db->bind(2, $vid);

      $row = $this->db->result();
      if ($row) {
        $results[] = $row;
      }
    }
    return $results;
  }

  public function getOrderWithDetails($orderId)
  {

    $sql = "SELECT o.*, 
                       a.recipient_name , a.recipient_phone, a.street_line, a.city, a.state, a.postcode,
                       p.payment_method, 
                       p.payment_status,
                       p.payment_id,
                       p.updated_datetime as payment_updated_at,
                       p.created_datetime as payment_created_at
                FROM `order` o
                JOIN address a ON o.address_id = a.address_id
                JOIN payment p ON o.order_id = p.order_id
                WHERE o.order_id = ? LIMIT 1";

    $this->db->query($sql);
    $this->db->bind(1, $orderId);
    return $this->db->result();
  }

  public function getOrderItems($orderId)
  {
    $sql = "SELECT oi.*, p.product_name, v.variant_name 
                FROM order_items oi
                JOIN product_variant pv ON oi.product_variant_id = pv.product_variant_id
                JOIN variant v ON pv.variant_id = v.variant_id
                JOIN product p ON pv.product_id = p.product_id
                WHERE oi.order_id = ?";

    $this->db->query($sql);
    $this->db->bind(1, $orderId);
    return $this->db->resultAll();
  }
  // Get Order by ID
  public function getOrderById($orderId)
  {
    $sql = "SELECT * FROM `order` WHERE order_id = ? LIMIT 1";
    $this->db->query($sql);
    $this->db->bind(1, $orderId);
    return $this->db->result();
  }

  // Update Order Status
  public function updateOrderStatus($orderId, $status)
  {
    $statusId = $this->db->generateId('orderStatus', 'order_status_id', 'OS');
    $sql = "INSERT INTO orderStatus (order_status_id, order_status, created_datetime, order_id) 
                VALUES (?, ?, NOW(), ?)";
    $this->db->query($sql);
    $this->db->bind(1, $statusId);
    $this->db->bind(2, $status);
    $this->db->bind(3, $orderId);
    return $this->db->execute();
  }

  // Get Customer Info needed for Email Receipt
  public function getCustomerInfoByOrder($orderId)
  {
    $sql = "SELECT c.email, c.firstname, c.lastname 
                FROM `order` o 
                JOIN customer c ON o.customer_id = c.customer_id 
                WHERE o.order_id = ?";
    $this->db->query($sql);
    $this->db->bind(1, $orderId);
    return $this->db->result();
  }
  public function countOrders()
  {
    $this->db->query("SELECT COUNT(*) AS total FROM ordertable");
    $result = $this->db->result();
    return $result["total"];
  }

  // public function getOrders($offset, $limit)
  // {
  //   $this->db->query("SELECT * FROM ordertable LIMIT $offset, $limit");
  //   return $this->db->resultAll();
  // }

  public function getOrders($offset, $limit)
  {
    $this->db->query("
        SELECT o.*, os_latest.order_status AS order_status, os_latest.created_datetime AS created_datetime
        FROM ordertable o
        LEFT JOIN (
            SELECT os1.order_id, os1.order_status, os1.created_datetime
            FROM orderstatus os1
            INNER JOIN (
                SELECT order_id, MAX(created_datetime) AS latest_time
                FROM orderstatus
                GROUP BY order_id
            ) os2 ON os1.order_id = os2.order_id AND os1.created_datetime = os2.latest_time
        ) AS os_latest ON o.order_id = os_latest.order_id
        LIMIT $offset, $limit
    ");

    return $this->db->resultAll();
  }


  public function getCustomerProducts($customerId)
  {
    $this->db->query("
            SELECT DISTINCT pv.product_id
            FROM ordertable o
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN product_variant pv ON oi.product_variant_id = pv.product_variant_id
            WHERE o.customer_id = :customer_id
        ");

    $this->db->bind(':customer_id', $customerId);

    return $this->db->resultAll();
  }


  // zq
  // public function getOrderItemsByCustID($customerId)
  // {
  //   $this->db->query("
  //           SELECT DISTINCT oi.order_qty, oi.price
  //           FROM ordertable o
  //           JOIN order_items oi ON o.order_id = oi.order_id
  //           WHERE o.customer_id = :customer_id
  //   ");
  //   $this->db->bind(':customer_id', $customerId);

  //   return $this->db->resultAll();
  // }

  public function getOrder($customerId)
  {
    $this->db->query("
        SELECT o.order_id, os_latest.order_status, o.total_amount, oi.product_variant_id, 
            oi.price, oi.order_qty, p.product_name, p.description, pv.img_url, c.category_name, v.variant_name
        FROM ordertable o

        -- Get latest status from orderstatus table
        JOIN (
            SELECT os1.*
            FROM orderstatus os1
            JOIN (
                SELECT order_id, MAX(created_datetime) AS latest_time
                FROM orderstatus
                GROUP BY order_id
            ) os2
            ON os1.order_id = os2.order_id AND os1.created_datetime = os2.latest_time
        ) AS os_latest
        ON o.order_id = os_latest.order_id

        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN product_variant pv ON oi.product_variant_id = pv.product_variant_id
        JOIN variant v ON pv.variant_id = v.variant_id
        JOIN product p ON pv.product_id = p.product_id
        JOIN category c ON p.category_id = c.category_id
        WHERE o.customer_id = :customer_id
    ");

    $this->db->bind(':customer_id', $customerId);

    return $this->db->resultAll();
  }

  public function getOrderStatusById($orderId)
  {

    $this->db->query("
        SELECT os_latest.order_status
        FROM ordertable o

        -- Get latest status from orderstatus table
        JOIN (
            SELECT os1.*
            FROM orderstatus os1
            JOIN (
                SELECT order_id, MAX(created_datetime) AS latest_time
                FROM orderstatus
                GROUP BY order_id
            ) os2
            ON os1.order_id = os2.order_id AND os1.created_datetime = os2.latest_time
        ) AS os_latest
        ON o.order_id = os_latest.order_id

        WHERE o.order_id = :order_id
    ");

    $this->db->bind(':order_id', $orderId);

    return $this->db->result();
  }

  public function getDetails($orderID)
  {
    $this->db->query("
    SELECT 
        o.order_id, 
        os_latest.order_status, 
        os_earliest.earliest_time,
        o.total_amount, 
        o.tax_fee, 
        oi.product_variant_id, 
        oi.price, 
        oi.order_qty,
        p.product_name, 
        p.description, 
        p.img_url,
        c.category_name, 
        v.variant_name,
        pm.created_datetime AS payment_time
    FROM ordertable o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN product_variant pv ON oi.product_variant_id = pv.product_variant_id
    JOIN variant v ON pv.variant_id = v.variant_id
    JOIN product p ON pv.product_id = p.product_id
    JOIN category c ON p.category_id = c.category_id
    JOIN payment pm ON o.order_id = pm.order_id

    -- Join only the latest order status
    JOIN (
        SELECT os1.order_id, os1.order_status
        FROM orderstatus os1
        JOIN (
            SELECT order_id, MAX(created_datetime) AS latest_time
            FROM orderstatus
            GROUP BY order_id
        ) os2 ON os1.order_id = os2.order_id AND os1.created_datetime = os2.latest_time
    ) AS os_latest ON o.order_id = os_latest.order_id

    -- Join to get the earliest time
    JOIN (
        SELECT order_id, MIN(created_datetime) AS earliest_time
        FROM orderstatus
        GROUP BY order_id
    ) AS os_earliest ON o.order_id = os_earliest.order_id

    WHERE o.order_id = :order_id
");

    $this->db->bind(':order_id', $orderID);

    return $this->db->resultAll();
  }

  public function getAllStatus($orderId)
  {
    $this->db->query("
    SELECT os.*
    FROM orderstatus os
    INNER JOIN (
      SELECT order_status, MAX(created_datetime) AS latest_time
      FROM orderstatus
      WHERE order_id = :order_id
      GROUP BY order_status
    ) latest
      ON os.order_status = latest.order_status
     AND os.created_datetime = latest.latest_time
    WHERE os.order_id = :order_id
    ORDER BY os.created_datetime ASC
  ");

    $this->db->bind(':order_id', $orderId);

    return $this->db->resultAll();
  }

  public function getReviewOrderDetails($orderID)
  {
    $this->db->query("
        SELECT 
            o.order_id, 
            o.customer_id,
            o.total_amount, 
            o.tax_fee, 
            oi.product_variant_id, 
            oi.price, 
            oi.order_qty,
            p.product_name, 
            p.description, 
            p.img_url,
            c.category_name, 
            v.variant_name,
            pm.created_datetime AS payment_time
        FROM ordertable o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN product_variant pv ON oi.product_variant_id = pv.product_variant_id
        JOIN variant v ON pv.variant_id = v.variant_id
        JOIN product p ON pv.product_id = p.product_id
        JOIN category c ON p.category_id = c.category_id
        JOIN payment pm ON o.order_id = pm.order_id

        -- Left join review to check which items already reviewed
        LEFT JOIN review r 
            ON r.order_id = o.order_id 
            AND r.product_variant_id = oi.product_variant_id

        WHERE o.order_id = :order_id
          AND r.product_variant_id IS NULL
    ");

    $this->db->bind(':order_id', $orderID);

    return $this->db->resultAll();
  }

  public function getReviewOrderDetailsByID($orderID, $variantID)
  {
    $allItems = $this->getReviewOrderDetails($orderID);

    foreach ($allItems as $item) {
      if ($item['product_variant_id'] == $variantID) {
        return $item; // return the single match
      }
    }

    return null;
  }


  public function getDelivery($orderID)
  {
    $this->db->query("
        SELECT shipment_id, receiver_name, receiver_phone, receiver_address FROM shipments
        WHERE order_id = :order_id
    ");
    $this->db->bind(':order_id', $orderID);

    return $this->db->result();
  }

  public function adminUpdateOrderStatus($orderId, $newStatus)
  {
    $newId = $this->db->generateId("orderstatus", "order_status_id", "OS");

    $this->db->query("
        INSERT INTO orderstatus (order_status_id, order_id, order_status, created_datetime)
        VALUES (:newId, :order_id, :status, NOW())
    ");

    $this->db->bind(':newId', $newId);
    $this->db->bind(':order_id', $orderId);
    $this->db->bind(':status', $newStatus);

    return $this->db->execute();
  }


  public function saveCancellation($orderId)
  {
    $newId = $this->db->generateId("orderstatus", "order_status_id", "OS");

    $this->db->query("
        INSERT INTO orderstatus (order_status_id, order_id, order_status, created_datetime)
        VALUES (:newId, :order_id, :status, NOW())
    ");
    $this->db->bind(':newId', $newId);
    $this->db->bind(':order_id', $orderId);
    $this->db->bind(':status', "Cancel Requested");
    return $this->db->execute();
  }

  public function getAddressByOrderId($orderId)
  {
    $this->db->query("
        SELECT c.firstName, c.lastName, c.phone, a.
        WHERE order_id = :order_id
    ");
    $this->db->bind(':order_id', $orderId);

    return $this->db->result();
  }

  public function createShipment($orderId, $newStatus)
  {
    $updated = $this->updateOrderStatus($orderId, $newStatus);
    if (!$updated) {
      return false;
    }

    $newId = $this->db->generateId("shipments", "shipment_id", "SH");

    $this->db->query("
    SELECT a.*
    FROM ordertable o
    JOIN address a ON o.address_id = a.address_id
    WHERE o.order_id = :order_id
  ");
    $this->db->bind(':order_id', $orderId);
    $addressInfo = $this->db->result();

    if (!$addressInfo) {
      return false;
    }

    $receiverName = $addressInfo['recipient_name'];
    $receiverPhone = $addressInfo['recipient_phone'];
    $receiverAddress =
      $addressInfo['street_line'] . ', ' .
      $addressInfo['postcode'] . ' ' .
      $addressInfo['city'] . ', ' .
      $addressInfo['state'];

    $this->db->query("
    INSERT INTO shipments
    (shipment_id, order_id, receiver_name, receiver_phone, receiver_address, shipment_date, status, created_datetime)
    VALUES
    (:shipment_id, :order_id, :receiver_name, :receiver_phone, :receiver_address, CURDATE(), :status, NOW())
  ");

    $this->db->bind(':shipment_id', $newId);
    $this->db->bind(':order_id', $orderId);
    $this->db->bind(':receiver_name', $receiverName);
    $this->db->bind(':receiver_phone', $receiverPhone);
    $this->db->bind(':receiver_address', $receiverAddress);
    $this->db->bind(':status', $newStatus);

    return $this->db->execute();
  }


  public function updateCancellation($orderId)
  {
    $newId = $this->db->generateId("orderstatus", "order_status_id", "OS");

    $this->db->query("
        INSERT INTO orderstatus (order_status_id, order_id, order_status, created_datetime)
        VALUES (:newId, :order_id, :status, NOW())
    ");
    $this->db->bind(':newId', $newId);
    $this->db->bind(':order_id', $orderId);
    $this->db->bind(':status', "Cancelled");
    return $this->db->execute();
  }

  public function getTopOrders()
  {
    $this->db->query("
        SELECT p.product_id, p.product_name, SUM(oi.order_qty) AS numOfOrder
        FROM ordertable o
        JOIN orderstatus os ON o.order_id = os.order_id
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN product_variant pv ON oi.product_variant_id = pv.product_variant_id
        JOIN product p ON pv.product_id = p.product_id
        WHERE os.order_status = 'Paid'
        GROUP BY p.product_id, p.product_name
        ORDER BY numOfOrder DESC
        LIMIT 5
    ");

    return $this->db->resultAll();
  }

  public function getOrdersGroupByCategory()
  {
    $this->db->query("
        SELECT c.category_name, SUM(oi.order_qty) AS numOfOrder
        FROM ordertable o
        JOIN orderstatus os ON o.order_id = os.order_id
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN product_variant pv ON oi.product_variant_id = pv.product_variant_id
        JOIN product p ON pv.product_id = p.product_id
        JOIN category c ON c.category_id = p.category_id
        WHERE os.order_status = 'Paid'
        GROUP BY c.category_name
        ORDER BY numOfOrder DESC
        LIMIT 3
    ");

    return $this->db->resultAll();
  }

  public function getOrdersByStatus()
  {
    $this->db->query("
        SELECT os_latest.order_status, SUM(oi.order_qty) AS numOfOrder
        FROM ordertable o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN (
            SELECT os1.order_id, os1.order_status
            FROM orderstatus os1
            JOIN (
                SELECT order_id, MAX(created_datetime) AS latest_time
                FROM orderstatus
                GROUP BY order_id
            ) os2 ON os1.order_id = os2.order_id AND os1.created_datetime = os2.latest_time
        ) AS os_latest ON o.order_id = os_latest.order_id
        GROUP BY os_latest.order_status
    ");

    return $this->db->resultAll();
  }
  public function getOrdersByTimeline($period)
  {
    switch ($period) {
      case 'month':
        // Aggregate by week of the month
        $label = "FLOOR((DAY(os.created_datetime)-1)/7)+1"; // Week 1-4
        $groupBy = $label;
        break;

      case 'year':
        // Aggregate by month
        $label = "MONTHNAME(os.created_datetime)";
        $groupBy = "MONTH(os.created_datetime)";
        break;

      case 'week':
      default:
        // Aggregate by day of week
        $label = "CASE DAYOFWEEK(os.created_datetime)
                        WHEN 1 THEN 'Sun'
                        WHEN 2 THEN 'Mon'
                        WHEN 3 THEN 'Tue'
                        WHEN 4 THEN 'Wed'
                        WHEN 5 THEN 'Thu'
                        WHEN 6 THEN 'Fri'
                        WHEN 7 THEN 'Sat'
                      END";
        $groupBy = "DAYOFWEEK(os.created_datetime)";
        break;
    }

    $this->db->query("
        SELECT 
            $label AS period_label, 
            SUM(oi.order_qty) AS numOfOrder
        FROM ordertable o
        JOIN orderstatus os ON o.order_id = os.order_id
        JOIN order_items oi ON o.order_id = oi.order_id
        WHERE os.order_status = 'Paid'
        GROUP BY $groupBy
        ORDER BY $groupBy
    ");

    return $this->db->resultAll();
  }



  // public function getCancelDetails($orderID)
  // {
  //   $this->db->query("
  //   SELECT 
  //       o.order_id, 
  //       os_latest.order_status, 
  //       os_earliest.earliest_time,
  //       o.total_amount, 
  //       o.tax_fee, 
  //       oi.product_variant_id, 
  //       oi.price, 
  //       oi.order_qty,
  //       p.product_name, 
  //       p.description, 
  //       p.img_url,
  //       c.category_name, 
  //       v.variant_name,
  //       pm.created_datetime AS payment_time
  //   FROM ordertable o
  //   JOIN order_items oi ON o.order_id = oi.order_id
  //   JOIN product_variant pv ON oi.product_variant_id = pv.product_variant_id
  //   JOIN variant v ON pv.variant_id = v.variant_id
  //   JOIN product p ON pv.product_id = p.product_id
  //   JOIN category c ON p.category_id = c.category_id
  //   JOIN payment pm ON o.order_id = pm.order_id

  //   -- Join only the latest order status
  //   JOIN (
  //       SELECT os1.order_id, os1.order_status
  //       FROM orderstatus os1
  //       JOIN (
  //           SELECT order_id, MAX(created_datetime) AS latest_time
  //           FROM orderstatus
  //           GROUP BY order_id
  //       ) os2 ON os1.order_id = os2.order_id AND os1.created_datetime = os2.latest_time
  //       WHERE os1.order_status NOT IN ('Cancelled', 'Cancel Requested')
  //   ) AS os_latest ON o.order_id = os_latest.order_id

  //   -- Join to get the earliest time
  //   JOIN (
  //       SELECT order_id, MIN(created_datetime) AS earliest_time
  //       FROM orderstatus
  //       GROUP BY order_id
  //   ) AS os_earliest ON o.order_id = os_earliest.order_id

  //   WHERE o.order_id = :order_id
  //   ");

  //   $this->db->bind(':order_id', $orderID);

  //   return $this->db->resultAll();
  // }

}
