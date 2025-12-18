<?php
require_once __DIR__ . '/../../app/config/database.php';

class productModel
{
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  public function getProductById($productId)
  {
    $sql = "SELECT p.*, c.category_name 
                FROM product p
                JOIN category c ON p.category_id = c.category_id
                WHERE p.product_id = ?";

    $this->db->query($sql);
    $this->db->bind(1, $productId);
    return $this->db->result();
  }

  public function getProductVariants($productId)
  {
    $sql = "SELECT 
                    pv.product_variant_id,
                    pv.stock_qty,
                    pv.stock_status,
                    v.variant_name,
                    pv.img_url
                FROM product_variant pv
                JOIN variant v ON pv.variant_id = v.variant_id
                WHERE pv.product_id = ?
                GROUP BY pv.product_variant_id";

    $this->db->query($sql);
    $this->db->bind(1, $productId);
    return $this->db->resultAll();
  }

  public function getProductGlobalStats($productId)
  {
    $sql = "SELECT 
                    COUNT(r.review_id) as total_reviews, 
                    AVG(r.rating) as avg_rating
                FROM review r
                JOIN product_variant pv ON r.product_variant_id = pv.product_variant_id
                WHERE pv.product_id = ?";

    $this->db->query($sql);
    $this->db->bind(1, $productId);
    return $this->db->result();
  }

  public function getProductReviews($productId, $limit = 5, $offset = 0)
  {
    $sql = "SELECT 
                    r.review_id,
                    r.description,
                    r.rating,
                    r.createdAt as created_at,
                    CONCAT(c.firstname, ' ', c.lastname) AS customer_name,
                    v.variant_name,
                    r.img_url
                FROM review r
                JOIN `ordertable` o ON r.order_id = o.order_id
                JOIN customer c ON o.customer_id = c.customer_id
                JOIN product_variant pv ON r.product_variant_id = pv.product_variant_id
                JOIN variant v ON pv.variant_id = v.variant_id
                WHERE pv.product_id = ?
                ORDER BY r.createdAt DESC
                LIMIT $limit OFFSET $offset";

    $this->db->query($sql);
    $this->db->bind(1, $productId);

    $reviews = $this->db->resultAll();

    return $reviews;
  }

  public function getRelatedProducts($categoryId, $currentProductId)
  {
    $sql = "SELECT p.product_id, p.product_name, p.sale_price, p.img_url, p.category_id, c.category_name
                FROM product p
                JOIN category c ON p.category_id = c.category_id
                WHERE p.category_id = ? AND p.product_id != ? 
                LIMIT 10";

    $this->db->query($sql);
    $this->db->bind(1, $categoryId);
    $this->db->bind(2, $currentProductId);
    return $this->db->resultAll();
  }
  //when user doesnot pay the product, after 4hour will expired and restore stock
  public function restoreStock($variantId, $quantity)
  {
    $sql = "UPDATE product_variant SET stock_qty = stock_qty + ? WHERE product_variant_id = ?";
    $this->db->query($sql);
    $this->db->bind(1, $quantity);
    $this->db->bind(2, $variantId);
    return $this->db->execute();
  }


  // zq
  public function getAllProducts()
  {
    $this->db->query("SELECT * FROM product");
    return $this->db->resultAll();
  }

  public function getAllCategories()
  {
    $this->db->query("SELECT * FROM category");
    return $this->db->resultAll();
  }

  public function getProductsByCategoryId($categoryID)
  {
    $this->db->query("SELECT * FROM product WHERE category_id = :categoryID");
    $this->db->bind(':categoryID', $categoryID);
    return $this->db->resultAll();
  }

  public function getCategory($categoryID)
  {
    $this->db->query("SELECT category_name FROM category WHERE category_id = :categoryID");
    $this->db->bind(':categoryID', $categoryID);
    return $this->db->result();
  }

  public function getProducts($productIDs)
  {
    // Build placeholders: ?, ?, ?, ...
    $placeholders = implode(',', array_fill(0, count($productIDs), '?'));

    // Build query
    $this->db->query("SELECT * FROM product WHERE product_id IN ($placeholders)");

    // Bind each value
    foreach ($productIDs as $index => $id) {
      $this->db->bind($index + 1, $id);
    }

    return $this->db->resultAll();
  }
}
