<?php
require_once __DIR__ . '/../../app/config/database.php';

class cartModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // Get or Create Cart ID (ensures every customer has a valid active cart)
    public function getOrCreateCart($customerId)
    {
        $sql = "SELECT cart_id FROM cart WHERE customer_id = ?";
        $this->db->query($sql);
        $this->db->bind(1, $customerId);
        $row = $this->db->result();

        if ($row) {
            return $row['cart_id'];
        } else {
            // create new cart if not found
            $newId = $this->db->generateId('cart', 'cart_id', 'CA');

            $sql = "INSERT INTO cart (cart_id, customer_id, created_datetime, updated_datetime) VALUES (?, ?, NOW(), NOW())";

            $this->db->query($sql);
            $this->db->bind(1, $newId);
            $this->db->bind(2, $customerId);

            return $this->db->execute() ? $newId : false;
        }
    }

    // Add Item to Cart
    public function addCartItem($cartId, $variantId, $qty)
    {
        $sql = "SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND product_variant_id = ? AND cart_status = 0";

        $this->db->query($sql);
        $this->db->bind(1, $cartId);
        $this->db->bind(2, $variantId);
        $item = $this->db->result();

        if ($item) {
            // update existing item quantity
            $newQty = $item['quantity'] + $qty;
            $sql = "UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?";
            $this->db->query($sql);
            $this->db->bind(1, $newQty);
            $this->db->bind(2, $item['cart_item_id']);
            return $this->db->execute();
        } else {
            //insert new item
            $newItemId = $this->db->generateId('cart_items', 'cart_item_id', 'CI');

            $sql = "INSERT INTO cart_items (cart_item_id, cart_id, product_variant_id, quantity, cart_status) VALUES (?, ?, ?, ?, 0)";
            $this->db->query($sql);
            $this->db->bind(1, $newItemId);
            $this->db->bind(2, $cartId);
            $this->db->bind(3, $variantId);
            $this->db->bind(4, $qty);
            return $this->db->execute();
        }
    }

    // Get Cart Count
    public function getCartCount($customerId)
    {
        $sql = "SELECT SUM(ci.quantity) as total_qty 
                FROM cart c 
                JOIN cart_items ci ON c.cart_id = ci.cart_id 
                WHERE c.customer_id = ? AND ci.cart_status = 0";

        $this->db->query($sql);
        $this->db->bind(1, $customerId);
        $row = $this->db->result();
        return ($row && isset($row['total_qty'])) ? (int)$row['total_qty'] : 0;
    }

    // Get Member Cart Details
    public function getMemberCartDetails($customerId)
    {
        $sql = "SELECT ci.cart_item_id, ci.quantity, pv.product_variant_id, pv.stock_qty, v.variant_name,
                pv.img_url as variant_img,
                p.product_id, p.product_name, p.sale_price, p.img_url as main_img, cat.category_name,
                p.is_deleted as p_deleted, pv.is_deleted as pv_deleted  
            FROM cart c
            JOIN cart_items ci ON c.cart_id = ci.cart_id
            JOIN product_variant pv ON ci.product_variant_id = pv.product_variant_id
            JOIN product p ON pv.product_id = p.product_id
            LEFT JOIN variant v ON pv.variant_id = v.variant_id
            LEFT JOIN category cat ON p.category_id = cat.category_id
            WHERE c.customer_id = ? AND ci.cart_status = 0
            ORDER BY ci.cart_item_id DESC";

        $this->db->query($sql);
        $this->db->bind(1, $customerId);
        return $this->db->resultAll();
    }

    // Update Quantity
    public function updateCartItemQty($cartId, $variantId, $qty)
    {
        $sql = "UPDATE cart_items 
                SET quantity = ? 
                WHERE cart_id = ? AND product_variant_id = ? AND cart_status = 0";

        $this->db->query($sql);
        $this->db->bind(1, $qty);
        $this->db->bind(2, $cartId);
        $this->db->bind(3, $variantId);

        if ($this->db->execute()) {
            $sql = "UPDATE cart SET updated_datetime = NOW() WHERE cart_id = ?";
            $this->db->query($sql);
            $this->db->bind(1, $cartId);
            $this->db->execute();
            return true;
        }
        return false;
    }

    // Remove Item
    public function removeCartItem($cartId, $variantId)
    {
        $sql = "DELETE FROM cart_items 
                WHERE cart_id = ? AND product_variant_id = ? AND cart_status = 0";

        $this->db->query($sql);
        $this->db->bind(1, $cartId);
        $this->db->bind(2, $variantId);
        return $this->db->execute();
    }

    // Batch Remove Items
    public function removeBatchCartItems($cartId, $variantIds)
    {
        if (empty($variantIds)) return false;

        $placeholders = implode(',', array_fill(0, count($variantIds), '?'));

        $sql = "DELETE FROM cart_items 
                WHERE cart_id = ? 
                AND product_variant_id IN ($placeholders) 
                AND cart_status = 0";

        $this->db->query($sql);
        $this->db->bind(1, $cartId);
        foreach ($variantIds as $k => $id) {
            $this->db->bind($k + 2, $id); // +2 because index 1 is cartId
        }

        return $this->db->execute();
    }

    //to check available stock for a variant.
    public function getProductStock($variantId)
    {
        $sql = "SELECT stock_qty FROM product_variant WHERE product_variant_id = ?";
        $this->db->query($sql);
        $this->db->bind(1, $variantId);
        $row = $this->db->result();
        return $row ? (int)$row['stock_qty'] : 0;
    }

    //check current quantity of an item already in the user's cart
    public function getCartItemQty($cartId, $variantId)
    {
        $sql = "SELECT quantity FROM cart_items WHERE cart_id = ? AND product_variant_id = ? AND cart_status = 0";
        $this->db->query($sql);
        $this->db->bind(1, $cartId);
        $this->db->bind(2, $variantId);
        $row = $this->db->result();
        return $row ? (int)$row['quantity'] : 0;
    }
}
