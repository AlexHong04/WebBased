  <?php

  require_once __DIR__ . '/../config/database.php';

  class ReviewModel
  {
    private $db;

    public function __construct()
    {
      $this->db = new Database();
    }

    public function saveReview($description, $rating, $img_url, $product_variant_id, $order_id)
    {
      $newId = $this->db->generateId("review", "review_id", "R");

      $sql = "INSERT INTO review
            (review_id, `description`, rating, createdAt, img_url, product_variant_id, order_id)
            VALUES (:review_id, :description, :rating, NOW(), :img_url, :product_variant_id, :order_id)";

      $this->db->query($sql);

      $this->db->bind(':review_id', $newId);
      $this->db->bind(':description', $description);
      $this->db->bind(':rating', $rating);
      $this->db->bind(':img_url', $img_url);
      $this->db->bind(':product_variant_id', $product_variant_id);
      $this->db->bind(':order_id', $order_id);

      return $this->db->execute();
    }
  }
