<?php
require_once __DIR__ . '/../config/database.php';

class ProductModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function getAllCategories()
    {
        $this->db->query("SELECT * FROM category ORDER BY category_name ASC");
        $this->db->execute();
        return $this->db->resultAll();
    }

    public function getAllVariants()
    {
        $this->db->query("SELECT * FROM variant ORDER BY variant_name ASC");
        $this->db->execute();
        return $this->db->resultAll();
    }

    public function getAllProducts($sort_column = 'product_id', $sort_order = 'asc')
    {
        $safe_columns = [
            'product_id',
            'product_name',
            'created_at',
            'updated_at',
        ];
        $sort = in_array($sort_column, $safe_columns) ? $sort_column : 'product_id';
        $order = (strtoupper($sort_order) === 'DESC') ? 'DESC' : 'ASC';
        $query = "SELECT * FROM product ORDER BY {$sort} {$order}";

        $this->db->query($query);
        $this->db->execute();

        return $this->db->resultAll();
    }

    public function getProductVariantsByFilter(
        $filter,
        $sort_column = 'product_variant_id',
        $sort_order = 'asc'
    ) {
        $where_clause = '';

        // 1. Handle Filtering using the stock_status attribute
        switch ($filter) {
            case 'lowStock':
                $where_clause = "WHERE stock_status IN ('Low Stock', 'Out of Stock')";
                break;
            case 'outOfStock':
                $where_clause = "WHERE stock_status = 'Out of Stock'";
                break;
            case 'all':
                $where_clause = "";
                break;
            case 'countLowStock':
                return $this->getCountByFilter("stock_status = 'Low Stock'");
            case 'countOutOfStock':
                return $this->getCountByFilter("stock_status = 'Out of Stock'");
            case 'countAll':
                return $this->getCountByFilter("1=1");
        }

        $order = (strtoupper($sort_order) === 'DESC') ? 'DESC' : 'ASC';

        $safe_columns = ['product_variant_id', 'stock_qty', 'min_stock_level', 'product_name', 'stock_status'];

        $sort = in_array($sort_column, $safe_columns) ? $sort_column : 'product_variant_id';

        $sort_sql = "ORDER BY {$sort} {$order}";

        // 3. Execute Query
        $query = "SELECT * FROM product_variant {$where_clause} {$sort_sql}";

        $this->db->query($query);
        $this->db->execute();

        return $this->db->resultAll();
    }
    private function getCountByFilter(string $condition): int
    {
        $query = "SELECT COUNT(*) AS count FROM product_variant WHERE {$condition}";
        $this->db->query($query);
        $this->db->execute();
        $result = $this->db->result();
        return (int)($result['count'] ?? 0);
    }

    public function generateId($table, $column, $prefix)
    {
        return $this->db->generateId($table, $column, $prefix);
    }

    public function addProduct($data)
    {
        try {
            $this->db->query("START TRANSACTION");
            $this->db->execute();

            // Insert product
            $this->db->query('
                INSERT INTO product 
                (product_id, product_name, description, cost_price, sale_price, category_id, img_url, created_at, updated_at)
                VALUES (:product_id, :product_name, :description, :cost_price, :sale_price, :category_id, :img_url, NOW(), NOW())
            ');
            foreach ($data['product'] as $key => $value) {
                $this->db->bind(":$key", $value);
            }
            $this->db->execute();

            // Insert variants
            foreach ($data['variants'] as $variant) {
                $this->db->query('
                    INSERT INTO product_variant
                    (product_variant_id, min_stock_level, stock_qty, stock_status, variant_id, product_id, img_url)
                    VALUES (:product_variant_id, :min_stock_level, :stock_qty, :stock_status, :variant_id, :product_id, :img_url)
                ');
                foreach ($variant as $key => $value) {
                    $this->db->bind(":$key", $value);
                }
                $this->db->execute();
            }

            $this->db->query("COMMIT");
            $this->db->execute();
            return true;
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            $this->db->execute();
            throw new Exception($e->getMessage());
        }
    }

    public function generateMultipleVariantIds(int $count): array
    {
        $this->db->query("
        SELECT product_variant_id
        FROM product_variant
        ORDER BY product_variant_id DESC
        LIMIT 1
    ");

        $lastId = $this->db->single();

        $lastNumber = 0;
        if (!empty($lastId)) {
            $lastNumber = (int) substr($lastId, 2);
        }

        $ids = [];
        for ($i = 1; $i <= $count; $i++) {
            $ids[] = 'PV' . str_pad($lastNumber + $i, 4, '0', STR_PAD_LEFT);
        }

        return $ids;
    }


    public function getCategoryNameById(string $categoryId): ?string
    {
        $sql = "SELECT category_name FROM category WHERE category_id = :category_id LIMIT 1";

        try {
            $this->db->query($sql);
            $this->db->bind(':category_id', $categoryId);

            $result = $this->db->result();

            if ($result && isset($result['category_name'])) {
                return $result['category_name'];
            } else {
                return null;
            }
        } catch (Exception $e) {
            error_log("Error fetching category name: " . $e->getMessage());
            return null;
        }
    }
    public function getCategoryById($categoryId)
    {
        $this->db->query('SELECT * FROM category WHERE category_id = :category_id');
        $this->db->bind(':category_id', $categoryId);
        return $this->db->result();
    }

    public function getProductById($productId)
    {
        $this->db->query('SELECT * FROM product WHERE product_id = :productId');
        $this->db->bind(':productId', $productId);
        return $this->db->result();
    }

    public function getProductVariantsByProductId($productId)
    {
        $this->db->query('SELECT * FROM product_variant WHERE product_id = :productId');
        $this->db->bind(':productId', $productId);
        return $this->db->resultAll();
    }

    public function deleteProductById(string $productId): bool
    {
        try {
            $this->db->query("START TRANSACTION");
            $this->db->execute();

            $this->db->query("DELETE FROM product_variant WHERE product_id = :product_id");
            $this->db->bind(':product_id', $productId);
            $this->db->execute();

            $this->db->query("DELETE FROM product WHERE product_id = :product_id");
            $this->db->bind(':product_id', $productId);
            $this->db->execute();

            $this->db->query("COMMIT");
            $this->db->execute();

            return true;
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            $this->db->execute();
            throw new Exception("Failed to delete product: " . $e->getMessage());
        }
    }

    public function updateProduct($productId, $data)
    {
        try {
            // Start Transaction using your existing query method
            $this->db->query("START TRANSACTION");
            $this->db->execute();

            // 1. Update Main Product Table
            $this->db->query('
            UPDATE product SET 
                product_name = :product_name,
                description = :description,
                cost_price = :cost_price,
                sale_price = :sale_price,
                category_id = :category_id,
                img_url = :img_url,
                updated_at = NOW()
            WHERE product_id = :product_id
        ');

            // Bind main product data explicitly
            $this->db->bind(':product_id', $productId);
            $this->db->bind(':product_name', $data['product']['product_name']);
            $this->db->bind(':description', $data['product']['description']);
            $this->db->bind(':cost_price', $data['product']['cost_price']);
            $this->db->bind(':sale_price', $data['product']['sale_price']);
            $this->db->bind(':category_id', $data['product']['category_id']);
            $this->db->bind(':img_url', $data['product']['img_url']);
            $this->db->execute();

            // 2. Update Variants
            foreach ($data['variants'] as $variant) {
                // Use your existing result() method to check if the variant exists
                $this->db->query('SELECT product_variant_id FROM product_variant WHERE product_variant_id = :id');
                $this->db->bind(':id', $variant['product_variant_id']);
                $existing = $this->db->result();

                if ($existing) {
                    $this->db->query('
                    UPDATE product_variant SET
                        min_stock_level = :min_stock_level,
                        stock_qty = :stock_qty,
                        stock_status = :stock_status,
                        variant_id = :variant_id,
                        img_url = :img_url
                    WHERE product_variant_id = :product_variant_id
                ');
                } else {
                    $this->db->query('
                    INSERT INTO product_variant
                        (product_variant_id, min_stock_level, stock_qty, stock_status, variant_id, product_id, img_url)
                    VALUES 
                        (:product_variant_id, :min_stock_level, :stock_qty, :stock_status, :variant_id, :product_id, :img_url)
                ');
                    $this->db->bind(':product_id', $productId);
                }

                // CRITICAL: Only bind the fields used in the variant queries above
                $this->db->bind(':product_variant_id', $variant['product_variant_id']);
                $this->db->bind(':min_stock_level',    $variant['min_stock_level']);
                $this->db->bind(':stock_qty',          $variant['stock_qty']);
                $this->db->bind(':stock_status',       $variant['stock_status']);
                $this->db->bind(':variant_id',         $variant['variant_id']);
                $this->db->bind(':img_url',            $variant['img_url']);

                $this->db->execute();
            }

            // Commit using your existing query method
            $this->db->query("COMMIT");
            $this->db->execute();
            return true;
        } catch (Exception $e) {
            // Rollback on error
            $this->db->query("ROLLBACK");
            $this->db->execute();
            throw new Exception("Failed to update product: " . $e->getMessage());
        }
    }

    public function getWishlistBackInStockItems(
        $sort_column = 'customer_id',
        $sort_order = 'asc'
    ) {
        $safe_columns = [
            'customer_id',
            'firstname',
            'lastname',
            'phone',
            'wishlist_id',
            'product_variant_id',
            'product_name',
            'stock_qty',
            'stock_status'
        ];

        $sort = in_array($sort_column, $safe_columns) ? $sort_column : 'customer_id';
        $order = (strtoupper($sort_order) === 'DESC') ? 'DESC' : 'ASC';

        $sql = "
        SELECT 
            c.customer_id,
            c.firstname,
            c.lastname,
            c.phone,
            w.wishlist_id,
            pv.product_variant_id,
            pv.stock_qty,
            pv.stock_status,
            pv.img_url,
            p.product_id,
            p.product_name,
            p.category_id,
            cat.category_name
        FROM wishlist_items wi
        INNER JOIN wishlist w 
            ON wi.wishlist_id = w.wishlist_id
        INNER JOIN customer c 
            ON w.customer_id = c.customer_id
        INNER JOIN product_variant pv 
            ON wi.product_variant_id = pv.product_variant_id
        INNER JOIN product p 
            ON pv.product_id = p.product_id
        INNER JOIN category cat
            ON p.category_id = cat.category_id
        WHERE pv.stock_qty > 0
          AND pv.stock_status = 'In Stock'
        ORDER BY {$sort} {$order}
    ";

        $this->db->query($sql);
        $this->db->execute();
        return $this->db->resultAll();
    }


    public function restockVariant($productVariantId, $qty)
    {
        $this->db->query("UPDATE product_variant 
                      SET stock_qty = stock_qty + :qty
                      WHERE product_variant_id = :variant_id");

        $this->db->bind(':qty', $qty);
        $this->db->bind(':variant_id', $productVariantId); // Bind as string
        return $this->db->execute();
    }

    public function updateProductUpdatedAt($productId)
    {
        $this->db->query("UPDATE product 
                      SET updated_at = NOW()
                      WHERE product_id = :product_id");
        $this->db->bind(':product_id', $productId);
        return $this->db->execute();
    }
    public function getNewArrivalsProducts($limit)
    {
        $limit = (int)$limit;
        $this->db->query("
            SELECT p.product_id, p.product_name, p.created_at, p.rate, p.img_url, p.sale_price, c.category_name
            FROM product AS p JOIN category AS c ON p.category_id = c.category_id
            GROUP BY p.product_id, p.product_name, p.created_at, p.rate, p.img_url, p.sale_price, c.category_name
            ORDER BY p.created_at DESC
            LIMIT $limit
        ");
        $this->db->execute();
        return $this->db->resultAll();
    }
    public function getTopSellingProducts($limit)
    {
        $limit = (int)$limit;
        $this->db->query("
            SELECT p.product_id, p.product_name, p.total_sold, p.rate, p.img_url, p.sale_price, c.category_name
            FROM product AS p JOIN category AS c ON p.category_id = c.category_id
            GROUP BY p.product_id, p.product_name, p.total_sold, p.rate, p.img_url, p.sale_price, c.category_name
            ORDER BY p.total_sold DESC
            LIMIT $limit
        ");
        $this->db->execute();
        return $this->db->resultAll();
    }

    //pf
    // fetches the product details and its associated category name --> product_details
    public function getProductWithCategory($productId)
    {
        $sql = "SELECT p.*, c.category_name 
                FROM product p
                JOIN category c ON p.category_id = c.category_id
                WHERE p.product_id = ?";

        $this->db->query($sql);
        $this->db->bind(1, $productId);
        return $this->db->result();
    }

    //fetches all variants for a specific product --> product_details
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

    //calculates review for the product --> product_details
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

    //fetches detailed reviews for the product --> product_details
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

    //fetches related products (suggestions) from the same category --> product_details
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
    // when user doesnot pay the product, after 4hour will expired and restore stock -->payment
    public function restoreStock($variantId, $quantity)
    {
        $sql = "UPDATE product_variant SET stock_qty = stock_qty + ? WHERE product_variant_id = ?";
        $this->db->query($sql);
        $this->db->bind(1, $quantity);
        $this->db->bind(2, $variantId);
        return $this->db->execute();
    }


    // zq
    public function fetchAllProducts()
    {
        $this->db->query("SELECT * FROM product");
        return $this->db->resultAll();
    }

    public function fetchAllCategories()
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

    public function searchAllProducts($keyword)
    {
        // Search by name or description
        $this->db->query("SELECT p.*, c.category_name 
                          FROM product p
                          LEFT JOIN category c ON p.category_id = c.category_id
                          WHERE p.product_name LIKE :keyword 
                          OR p.description LIKE :keyword
                          ORDER BY p.product_name ASC");

        $this->db->bind(':keyword', "%$keyword%");

        return $this->db->resultAll();
    }
    public function deleteVariantById($variantId)
    {

        try {
            $this->db->query("START TRANSACTION");
            $this->db->execute();

            $this->db->query("DELETE FROM product_variant WHERE product_variant_id = :variantId");
            $this->db->bind(':variantId', $variantId);
            $this->db->execute();

            $this->db->query("COMMIT");
            $this->db->execute();

            return true;
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            $this->db->execute();
            throw new Exception("Failed to delete product: " . $e->getMessage());
        }
    }

    public function getAllProductVariants()
    {
        $this->db->query("
        SELECT 
            pv.product_variant_id,
            pv.product_id,
            v.variant_name,
            pv.stock_qty,
            pv.stock_status,
            pv.img_url
        FROM product_variant pv
        LEFT JOIN variant v ON v.variant_id = pv.variant_id
        ORDER BY pv.product_id
    ");

        return $this->db->resultAll();
    }
}
