<?php
require_once __DIR__ . '/../models/reviewModel.php';

class ReviewController
{
  private $reviewModel;

  public function __construct()
  {
    $this->reviewModel = new ReviewModel();
  }

  // public function addReview($orderId, $rating, $review)
  // {
  //   if (!isset($_GET['id'])) {
  //     die("No customer ID provided.");
  //   }

  //   $customer_id = $_GET['id'];
  //   return $this->reviewModel->saveReview($customer_id, $orderId, $rating, $review);
  // }

  public function reviewOrder()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $orderId = $_GET['order_id'] ?? null;
      $variantId = $_GET['variant_id'] ?? null;

      if (!$orderId || !$variantId) {
        $_SESSION['error_message'] = "Invalid order or product variant.";
        return;
      }

      $rating = intval($_POST['rating'] ?? 0);
      $description = trim($_POST['description'] ?? '');
      $img_urls = [];

      // Skip if nothing is provided
      if ($rating === 0 && $description === '' && empty($_FILES['media']['name'][0])) {
        $_SESSION['error_message'] = "Please provide a rating, description, or upload media.";
        return;
      }

      // Handle media uploads
      if (!empty($_FILES['media']['name'][0])) {
        $totalFiles = count($_FILES['media']['name']);
        $targetDir = __DIR__ . "/../../public/images/review/";

        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        for ($i = 0; $i < $totalFiles; $i++) {
          $tmpName = $_FILES['media']['tmp_name'][$i];
          $uniqueId = uniqid();
          $filename = $uniqueId . '_' . basename($_FILES['media']['name'][$i]);
          $targetFile = $targetDir . $filename;

          if (move_uploaded_file($tmpName, $targetFile)) {
            $img_urls[] = $filename;
          }
        }
      }

      // Save review
      $this->reviewModel->saveReview(
        $description,
        $rating,
        !empty($img_urls) ? json_encode($img_urls) : null,
        $variantId,
        $orderId
      );
    }
  }
}
