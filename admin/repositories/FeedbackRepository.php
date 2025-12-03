<?php
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/FeedbackModel.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class FeedbackRepository {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getPdo();
    }

    public function create(array $data): FeedbackModel {
        $stmt = $this->pdo->prepare("
            INSERT INTO feedback (user_id, product_id, rating, comment, is_verified_purchase, is_active)
            VALUES (:user_id, :product_id, :rating, :comment, :is_verified_purchase, :is_active)
            RETURNING *
        ");
        $verified = filter_var($data['is_verified_purchase'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $active   = filter_var($data['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $stmt->bindValue(':is_verified_purchase', $verified ?? false, PDO::PARAM_BOOL);
        $stmt->bindValue(':is_active', $active ?? true, PDO::PARAM_BOOL);    
        $stmt->bindValue(':comment', $data['comment'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':rating', $data['rating'], PDO::PARAM_INT);
        $stmt->bindValue(':product_id', $data['product_id'], PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $data['user_id'], PDO::PARAM_INT);

        $stmt->execute();
        $row = $stmt->fetch();
        if (!$row) throw new DatabaseException('Failed to create feedback');
        return $this->mapToModel($row);
    }

    public function getById(int $id): ?FeedbackModel {
        $stmt = $this->pdo->prepare("
            SELECT * FROM feedback WHERE id = :id AND deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->mapToModel($row) : null;
    }

    public function getAllWithProductDetails(): array {
    $stmt = $this->pdo->query("
        SELECT 
            f.id,
            f.user_id,
            f.product_id,
            f.rating,
            f.comment,
            f.is_verified_purchase,
            f.is_active,
            f.created_at,
            f.updated_at,
            f.deleted_at,
            p.name as product_name,
            p.slug as product_slug,
            p.description as product_description,
            p.price_cents,
            p.image_url as product_image,
            c.name as category_name,
            u.name as user_name,
            u.email as user_email,
            (SELECT COUNT(*) FROM order_items oi 
             JOIN orders o ON oi.order_id = o.id 
             WHERE oi.product_id = f.product_id 
             AND o.status IN ('paid', 'delivered')) as units_sold,
            (SELECT COUNT(*) FROM feedback f2 
             WHERE f2.product_id = f.product_id 
             AND f2.deleted_at IS NULL 
             AND f2.is_active = TRUE) as total_reviews,
            (SELECT AVG(rating) FROM feedback f3 
             WHERE f3.product_id = f.product_id 
             AND f3.deleted_at IS NULL 
             AND f3.is_active = TRUE) as avg_rating
        FROM feedback f
        JOIN products p ON f.product_id = p.id
        JOIN categories c ON p.category_id = c.id
        JOIN users u ON f.user_id = u.id
        WHERE f.deleted_at IS NULL 
        AND p.deleted_at IS NULL
        ORDER BY f.created_at DESC
    ");
    
    $feedbacks = [];
    while ($row = $stmt->fetch()) {
        $feedbacks[] = [
            'id' => (int)$row['id'],
            'userId' => (int)$row['user_id'],
            'productId' => (int)$row['product_id'],
            'rating' => (int)$row['rating'],
            'comment' => $row['comment'],
            'isVerifiedPurchase' => (bool)$row['is_verified_purchase'],
            'isActive' => (bool)$row['is_active'],
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
            'deletedAt' => $row['deleted_at'],
            'productName' => $row['product_name'],
            'productSlug' => $row['product_slug'],
            'productDescription' => $row['product_description'],
            'priceCents' => (int)$row['price_cents'],
            'productImage' => $row['product_image'],
            'categoryName' => $row['category_name'],
            'userName' => $row['user_name'],
            'userEmail' => $row['user_email'],
            'unitsSold' => (int)$row['units_sold'],
            'totalReviews' => (int)$row['total_reviews'],
            'avgRating' => round((float)$row['avg_rating'], 1)
        ];
    }
    return $feedbacks;
}
    public function getByUserId(int $userId): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM feedback WHERE user_id = :user_id AND deleted_at IS NULL ORDER BY created_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        $feedbacks = [];
        while ($row = $stmt->fetch()) $feedbacks[] = $this->mapToModel($row);
        return $feedbacks;
    }

    public function exists(int $userId, int $productId): bool {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count FROM feedback
            WHERE user_id = :user_id AND product_id = :product_id AND deleted_at IS NULL
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId
        ]);
        $row = $stmt->fetch();
        return $row && $row['count'] > 0;
    }

    public function getByProductId(int $productId): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM feedback WHERE product_id = :product_id AND deleted_at IS NULL ORDER BY created_at DESC
        ");
        $stmt->execute([':product_id' => $productId]);
        $feedbacks = [];
        while ($row = $stmt->fetch()) $feedbacks[] = $this->mapToModel($row);
        return $feedbacks;
    }

    public function getAverageRating(int $productId): float {
        $stmt = $this->pdo->prepare("
            SELECT AVG(rating) as avg_rating FROM feedback
            WHERE product_id = :product_id AND deleted_at IS NULL
        ");
        $stmt->execute([':product_id' => $productId]);
        $row = $stmt->fetch();
        return $row['avg_rating'] !== null ? (float)$row['avg_rating'] : 0.0;
    }

    public function update(int $id, array $data): ?FeedbackModel {
        $sets = ['updated_at = NOW()'];
        $params = [':id' => $id];

        foreach (['rating', 'comment', 'is_verified_purchase', 'is_active'] as $col) {
            if (isset($data[$col])) {
                $sets[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }

        $sql = "UPDATE feedback SET " . implode(', ', $sets) . " WHERE id = :id AND deleted_at IS NULL RETURNING *";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ? $this->mapToModel($row) : null;
    }

    public function delete(int $id): int {
        $stmt = $this->pdo->prepare("
            UPDATE feedback SET deleted_at = NOW(), updated_at = NOW() , is_active = FALSE WHERE id = :id AND deleted_at IS NULL
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }

    public function getAll(): array {
        $stmt = $this->pdo->query("
            SELECT * FROM feedback WHERE deleted_at IS NULL ORDER BY created_at DESC
        ");
        $feedbacks = [];
        while ($row = $stmt->fetch()) $feedbacks[] = $this->mapToModel($row);
        return $feedbacks;
    }

    public function getAllPaginated(int $limit, int $offset): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM feedback WHERE deleted_at IS NULL ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $feedbacks = [];
        while ($row = $stmt->fetch()) $feedbacks[] = $this->mapToModel($row);
        return $feedbacks;
    }

    public function hardDelete(int $id): int {
        $stmt = $this->pdo->prepare("
            DELETE FROM feedback WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }

    private function mapToModel(array $row): FeedbackModel {
        return new FeedbackModel(
            id: (int)$row['id'],
            userId: (int)$row['user_id'],
            productId: (int)$row['product_id'],
            rating: (int)$row['rating'],
            comment: $row['comment'],
            isVerifiedPurchase: (bool)$row['is_verified_purchase'],
            isActive: (bool)$row['is_active'],
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
            deletedAt: $row['deleted_at']
        );
    }

    
}
?>
