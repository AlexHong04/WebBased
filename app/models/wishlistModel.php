<?php
require_once __DIR__ . '/../../app/config/database.php';

class WishlistModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function toggleWishlist($customerId, $variantId) {
        $wishlistId = $this->getOrCreateWishlistId($customerId);
        if (!$wishlistId) return false;

        $this->db->query("SELECT wishlist_item_id FROM wishlist_items WHERE wishlist_id = ? AND product_variant_id = ?");
        $this->db->bind(1, $wishlistId);
        $this->db->bind(2, $variantId);
        $existingItem = $this->db->result();

        if ($existingItem) {
            $this->db->query("DELETE FROM wishlist_items WHERE wishlist_item_id = ?");
            $this->db->bind(1, $existingItem['wishlist_item_id']);
            return $this->db->execute() ? 'removed' : false;
        } else {
            $newItemId = $this->db->generateId('wishlist_items', 'wishlist_item_id', 'WI');
            $this->db->query("INSERT INTO wishlist_items (wishlist_item_id, wishlist_id, product_variant_id) VALUES (?, ?, ?)");
            $this->db->bind(1, $newItemId);
            $this->db->bind(2, $wishlistId);
            $this->db->bind(3, $variantId);
            return $this->db->execute() ? 'added' : false;
        }
    }

    private function getOrCreateWishlistId($customerId) {
        $this->db->query("SELECT wishlist_id FROM wishlist WHERE customer_id = ?");
        $this->db->bind(1, $customerId);
        $row = $this->db->result();

        if ($row) {
            return $row['wishlist_id'];
        } else {
            $newId = $this->db->generateId('wishlist', 'wishlist_id', 'WL');
            $this->db->query("INSERT INTO wishlist (wishlist_id, customer_id) VALUES (?, ?)");
            $this->db->bind(1, $newId);
            $this->db->bind(2, $customerId);
            
            if ($this->db->execute()) {
                return $newId;
            }
            return false;
        }
    }

    public function isItemInWishlist($customerId, $variantId) {
        $this->db->query("SELECT wi.wishlist_item_id 
                          FROM wishlist w 
                          JOIN wishlist_items wi ON w.wishlist_id = wi.wishlist_id 
                          WHERE w.customer_id = ? AND wi.product_variant_id = ?");
        $this->db->bind(1, $customerId);
        $this->db->bind(2, $variantId);
        return $this->db->result() ? true : false;
    }

    public function getWishlistItems($customerId) {
        $sql = "SELECT 
                    wi.wishlist_item_id,
                    pv.product_variant_id,
                    pv.stock_qty,
                    v.variant_name,
                    p.product_id,
                    p.product_name,
                    p.sale_price as price,
                    pv.img_url as variant_img,
                    p.img_url as main_img,
                    c.category_name
                FROM wishlist w
                JOIN wishlist_items wi ON w.wishlist_id = wi.wishlist_id
                JOIN product_variant pv ON wi.product_variant_id = pv.product_variant_id
                LEFT JOIN variant v ON pv.variant_id = v.variant_id
                JOIN product p ON pv.product_id = p.product_id
                LEFT JOIN category c ON p.category_id = c.category_id
                WHERE w.customer_id = ?
                ORDER BY wi.wishlist_item_id DESC";

        $this->db->query($sql);
        $this->db->bind(1, $customerId);
        return $this->db->resultAll();
    }

    public function removeBatchWishlistItems($customerId, $variantIds) {
        if (empty($variantIds)) return false;

        $wishlistId = $this->getOrCreateWishlistId($customerId);
        if (!$wishlistId) return false;

        $placeholders = implode(',', array_fill(0, count($variantIds), '?'));
        
        $sql = "DELETE FROM wishlist_items 
                WHERE wishlist_id = ? 
                AND product_variant_id IN ($placeholders)";

        $this->db->query($sql);
        $this->db->bind(1, $wishlistId);
        
        foreach ($variantIds as $k => $id) {
            $this->db->bind($k + 2, $id);
        }

        return $this->db->execute();
    }
}
?>