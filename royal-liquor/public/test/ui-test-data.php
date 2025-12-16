<?php
/**
 * UI Test Data
 * Hardcoded test data for frontend UI testing
 * Use this when the backend API is not available
 */

// Test Products
function getTestProducts() {
    return [
        [
            'id' => 1,
            'name' => 'Johnnie Walker Black Label',
            'description' => 'A rich, smooth blend of whiskies aged for at least 12 years. Deep and complex with notes of fruit and smoke.',
            'price_cents' => 4999,
            'image_url' => 'https://images.unsplash.com/photo-1569529465841-dfecdab7503b?w=400',
            'category_id' => 1,
            'category_name' => 'Whisky',
            'supplier_name' => 'Diageo',
            'available_stock' => 45,
            'is_available' => true,
            'units_sold' => 234,
            'avg_rating' => 4.7,
            'flavor_profile' => json_encode([
                'sweetness' => 6,
                'bitterness' => 3,
                'strength' => 8,
                'smokiness' => 7,
                'fruitiness' => 5,
                'spiciness' => 4,
                'tags' => ['Smoky', 'Rich', 'Complex']
            ])
        ],
        [
            'id' => 2,
            'name' => 'Hendricks Gin',
            'description' => 'An unusual gin created from eleven botanicals including cucumber and rose petals. Wonderfully refreshing.',
            'price_cents' => 3799,
            'image_url' => 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=400',
            'category_id' => 2,
            'category_name' => 'Gin',
            'supplier_name' => 'William Grant & Sons',
            'available_stock' => 67,
            'is_available' => true,
            'units_sold' => 189,
            'avg_rating' => 4.8,
            'flavor_profile' => json_encode([
                'sweetness' => 4,
                'bitterness' => 2,
                'strength' => 7,
                'smokiness' => 1,
                'fruitiness' => 6,
                'spiciness' => 3,
                'tags' => ['Floral', 'Refreshing', 'Botanical']
            ])
        ],
        [
            'id' => 3,
            'name' => 'Don Julio 1942 Tequila',
            'description' => 'Aged in oak barrels for a minimum of two and a half years, this añejo tequila is rich and complex.',
            'price_cents' => 15999,
            'image_url' => 'https://images.unsplash.com/photo-1582095133179-bfd08e2fc6b3?w=400',
            'category_id' => 3,
            'category_name' => 'Tequila',
            'supplier_name' => 'Diageo',
            'available_stock' => 12,
            'is_available' => true,
            'units_sold' => 45,
            'avg_rating' => 4.9,
            'flavor_profile' => json_encode([
                'sweetness' => 7,
                'bitterness' => 2,
                'strength' => 8,
                'smokiness' => 3,
                'fruitiness' => 6,
                'spiciness' => 5,
                'tags' => ['Premium', 'Smooth', 'Aged']
            ])
        ],
        [
            'id' => 4,
            'name' => 'Grey Goose Vodka',
            'description' => 'Made from French wheat and spring water, this vodka is exceptionally smooth and clean.',
            'price_cents' => 4299,
            'image_url' => 'https://images.unsplash.com/photo-1560508801-e1d1f2c2e6e7?w=400',
            'category_id' => 4,
            'category_name' => 'Vodka',
            'supplier_name' => 'Bacardi',
            'available_stock' => 89,
            'is_available' => true,
            'units_sold' => 312,
            'avg_rating' => 4.6,
            'flavor_profile' => json_encode([
                'sweetness' => 3,
                'bitterness' => 1,
                'strength' => 7,
                'smokiness' => 0,
                'fruitiness' => 2,
                'spiciness' => 1,
                'tags' => ['Clean', 'Smooth', 'Premium']
            ])
        ],
        [
            'id' => 5,
            'name' => 'Bacardi Superior Rum',
            'description' => 'A light and aromatic white rum with subtle notes of almond and vanilla.',
            'price_cents' => 2499,
            'image_url' => 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=400',
            'category_id' => 5,
            'category_name' => 'Rum',
            'supplier_name' => 'Bacardi',
            'available_stock' => 156,
            'is_available' => true,
            'units_sold' => 567,
            'avg_rating' => 4.4,
            'flavor_profile' => json_encode([
                'sweetness' => 6,
                'bitterness' => 1,
                'strength' => 6,
                'smokiness' => 0,
                'fruitiness' => 5,
                'spiciness' => 2,
                'tags' => ['Light', 'Versatile', 'Classic']
            ])
        ],
        [
            'id' => 6,
            'name' => 'Moët & Chandon Champagne',
            'description' => 'A prestigious champagne with elegant maturity and seductive palate.',
            'price_cents' => 5999,
            'image_url' => 'https://images.unsplash.com/photo-1547595628-c61a29f496f0?w=400',
            'category_id' => 6,
            'category_name' => 'Champagne',
            'supplier_name' => 'LVMH',
            'available_stock' => 34,
            'is_available' => true,
            'units_sold' => 98,
            'avg_rating' => 4.9,
            'flavor_profile' => json_encode([
                'sweetness' => 5,
                'bitterness' => 1,
                'strength' => 5,
                'smokiness' => 0,
                'fruitiness' => 7,
                'spiciness' => 0,
                'tags' => ['Elegant', 'Celebratory', 'Luxury']
            ])
        ],
        [
            'id' => 7,
            'name' => 'Jack Daniels Tennessee Whiskey',
            'description' => 'Charcoal mellowed whiskey with a smooth, sweet flavor and hints of vanilla and caramel.',
            'price_cents' => 3299,
            'image_url' => 'https://images.unsplash.com/photo-1569529465841-dfecdab7503b?w=400',
            'category_id' => 1,
            'category_name' => 'Whisky',
            'supplier_name' => 'Brown-Forman',
            'available_stock' => 0,
            'is_available' => false,
            'units_sold' => 445,
            'avg_rating' => 4.5,
            'flavor_profile' => json_encode([
                'sweetness' => 7,
                'bitterness' => 2,
                'strength' => 7,
                'smokiness' => 4,
                'fruitiness' => 4,
                'spiciness' => 3,
                'tags' => ['Smooth', 'Sweet', 'American']
            ])
        ],
        [
            'id' => 8,
            'name' => 'Tanqueray London Dry Gin',
            'description' => 'A perfectly balanced gin with a crisp, dry finish and juniper-forward flavor.',
            'price_cents' => 2999,
            'image_url' => 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=400',
            'category_id' => 2,
            'category_name' => 'Gin',
            'supplier_name' => 'Diageo',
            'available_stock' => 78,
            'is_available' => true,
            'units_sold' => 223,
            'avg_rating' => 4.6,
            'flavor_profile' => json_encode([
                'sweetness' => 3,
                'bitterness' => 3,
                'strength' => 8,
                'smokiness' => 0,
                'fruitiness' => 4,
                'spiciness' => 5,
                'tags' => ['Classic', 'Juniper', 'Crisp']
            ])
        ]
    ];
}

// Test Categories
function getTestCategories() {
    return [
        ['id' => 1, 'name' => 'Whisky', 'description' => 'Premium whisky selection', 'product_count' => 45],
        ['id' => 2, 'name' => 'Gin', 'description' => 'Craft and classic gins', 'product_count' => 32],
        ['id' => 3, 'name' => 'Tequila', 'description' => 'Fine tequilas and mezcals', 'product_count' => 28],
        ['id' => 4, 'name' => 'Vodka', 'description' => 'Premium vodkas', 'product_count' => 38],
        ['id' => 5, 'name' => 'Rum', 'description' => 'Caribbean and spiced rums', 'product_count' => 41],
        ['id' => 6, 'name' => 'Champagne', 'description' => 'Sparkling wines', 'product_count' => 23],
    ];
}

// Test User Profile
function getTestUser() {
    return [
        'id' => 1,
        'username' => 'john_doe',
        'email' => 'john.doe@example.com',
        'full_name' => 'John Doe',
        'phone' => '+1 (555) 123-4567',
        'created_at' => '2024-01-15 10:30:00',
        'total_orders' => 12,
        'total_spent_cents' => 45678
    ];
}

// Test Addresses
function getTestAddresses() {
    return [
        [
            'id' => 1,
            'user_id' => 1,
            'label' => 'Home',
            'street' => '123 Main Street',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'USA',
            'is_default' => true
        ],
        [
            'id' => 2,
            'user_id' => 1,
            'label' => 'Office',
            'street' => '456 Business Ave',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10002',
            'country' => 'USA',
            'is_default' => false
        ]
    ];
}

// Test Orders
function getTestOrders() {
    return [
        [
            'id' => 1,
            'user_id' => 1,
            'total_cents' => 12998,
            'status' => 'delivered',
            'payment_method' => 'credit_card',
            'created_at' => '2024-12-01 14:30:00',
            'items' => [
                ['product_name' => 'Johnnie Walker Black Label', 'quantity' => 2, 'price_cents' => 4999],
                ['product_name' => 'Hendricks Gin', 'quantity' => 1, 'price_cents' => 3799]
            ]
        ],
        [
            'id' => 2,
            'user_id' => 1,
            'total_cents' => 15999,
            'status' => 'processing',
            'payment_method' => 'paypal',
            'created_at' => '2024-12-10 09:15:00',
            'items' => [
                ['product_name' => 'Don Julio 1942 Tequila', 'quantity' => 1, 'price_cents' => 15999]
            ]
        ],
        [
            'id' => 3,
            'user_id' => 1,
            'total_cents' => 8798,
            'status' => 'shipped',
            'payment_method' => 'credit_card',
            'created_at' => '2024-12-08 16:45:00',
            'items' => [
                ['product_name' => 'Grey Goose Vodka', 'quantity' => 1, 'price_cents' => 4299],
                ['product_name' => 'Bacardi Superior Rum', 'quantity' => 2, 'price_cents' => 2499]
            ]
        ]
    ];
}

// Test Cart Items
function getTestCartItems() {
    $products = getTestProducts();
    return [
        array_merge($products[0], ['quantity' => 2]),
        array_merge($products[1], ['quantity' => 1]),
        array_merge($products[3], ['quantity' => 1]),
    ];
}

// Test Wishlist Items
function getTestWishlistItems() {
    $products = getTestProducts();
    return [
        $products[2], // Don Julio
        $products[5], // Moët & Chandon
        $products[7], // Tanqueray
    ];
}

// Helper function to return JSON response
function jsonResponse($data, $success = true, $message = '') {
    return [
        'success' => $success,
        'data' => $data,
        'message' => $message
    ];
}

// Export all test data
function getAllTestData() {
    return [
        'products' => getTestProducts(),
        'categories' => getTestCategories(),
        'user' => getTestUser(),
        'addresses' => getTestAddresses(),
        'orders' => getTestOrders(),
        'cart' => getTestCartItems(),
        'wishlist' => getTestWishlistItems()
    ];
}
