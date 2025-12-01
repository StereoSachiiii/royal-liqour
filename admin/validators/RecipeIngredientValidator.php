<?php
declare(strict_types=1);

require_once __DIR__ . '/../exceptions/ValidationException.php';

class RecipeIngredientValidator
{
    /**
     * Validate data for creating a recipe ingredient
     */
    public static function validateCreate(array $data): void
    {
        $errors = [];

        // Recipe ID - required
        if (empty($data['recipe_id'])) {
            $errors['recipe_id'] = 'Recipe ID is required';
        } elseif (!is_numeric($data['recipe_id']) || (int)$data['recipe_id'] <= 0) {
            $errors['recipe_id'] = 'Recipe ID must be a positive integer';
        }

        // Product ID - required
        if (empty($data['product_id'])) {
            $errors['product_id'] = 'Product ID is required';
        } elseif (!is_numeric($data['product_id']) || (int)$data['product_id'] <= 0) {
            $errors['product_id'] = 'Product ID must be a positive integer';
        }

        // Quantity - required
        if (!isset($data['quantity'])) {
            $errors['quantity'] = 'Quantity is required';
        } elseif (!is_numeric($data['quantity']) || (float)$data['quantity'] <= 0) {
            $errors['quantity'] = 'Quantity must be greater than 0';
        } elseif ((float)$data['quantity'] > 10000) {
            $errors['quantity'] = 'Quantity seems unreasonably large';
        }

        // Unit - required
        if (empty($data['unit'])) {
            $errors['unit'] = 'Unit is required';
        } elseif (!is_string($data['unit'])) {
            $errors['unit'] = 'Unit must be a string';
        } elseif (strlen($data['unit']) > 50) {
            $errors['unit'] = 'Unit must not exceed 50 characters';
        } elseif (!self::isValidUnit($data['unit'])) {
            $errors['unit'] = 'Invalid unit. Common units: ml, cl, oz, shot, dash, drop, tsp, tbsp, piece, slice';
        }

        // Is Optional - optional boolean
        if (isset($data['is_optional']) && !is_bool($data['is_optional']) && !in_array($data['is_optional'], [0, 1, '0', '1', 'true', 'false'])) {
            $errors['is_optional'] = 'is_optional must be a boolean value';
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }
    }

    /**
     * Validate data for updating a recipe ingredient
     */
    public static function validateUpdate(array $data): void
    {
        $errors = [];

        // Quantity - optional but must be valid if provided
        if (isset($data['quantity'])) {
            if (!is_numeric($data['quantity']) || (float)$data['quantity'] <= 0) {
                $errors['quantity'] = 'Quantity must be greater than 0';
            } elseif ((float)$data['quantity'] > 10000) {
                $errors['quantity'] = 'Quantity seems unreasonably large';
            }
        }

        // Unit - optional but must be valid if provided
        if (isset($data['unit'])) {
            if (!is_string($data['unit'])) {
                $errors['unit'] = 'Unit must be a string';
            } elseif (strlen($data['unit']) > 50) {
                $errors['unit'] = 'Unit must not exceed 50 characters';
            } elseif (!self::isValidUnit($data['unit'])) {
                $errors['unit'] = 'Invalid unit. Common units: ml, cl, oz, shot, dash, drop, tsp, tbsp, piece, slice';
            }
        }

        // Is Optional - optional boolean
        if (isset($data['is_optional']) && !is_bool($data['is_optional']) && !in_array($data['is_optional'], [0, 1, '0', '1', 'true', 'false'])) {
            $errors['is_optional'] = 'is_optional must be a boolean value';
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }
    }

    /**
     * Validate bulk creation data
     */
    public static function validateBulkCreate(array $ingredients): void
    {
        if (empty($ingredients)) {
            throw new ValidationException('At least one ingredient is required', ['errors' => ['ingredients' => 'Empty array']]);
        }

        if (count($ingredients) > 50) {
            throw new ValidationException('Cannot add more than 50 ingredients at once', ['errors' => ['ingredients' => 'Too many items']]);
        }

        $errors = [];
        foreach ($ingredients as $index => $ingredient) {
            try {
                // Temporarily add recipe_id for validation if not present
                if (!isset($ingredient['recipe_id'])) {
                    $ingredient['recipe_id'] = 1; // Placeholder
                }
                self::validateCreate($ingredient);
            } catch (ValidationException $e) {
                $errors["ingredient_$index"] = $e->getContext()['errors'] ?? $e->getMessage();
            }
        }

        if (!empty($errors)) {
            throw new ValidationException('Bulk validation failed', ['errors' => $errors]);
        }
    }

    /**
     * Check if a unit is valid
     */
    private static function isValidUnit(string $unit): bool
    {
        $validUnits = [
            // Volume - Metric
            'ml', 'milliliter', 'milliliters',
            'cl', 'centiliter', 'centiliters',
            'l', 'liter', 'liters',
            
            // Volume - Imperial
            'oz', 'ounce', 'ounces', 'fl oz', 'fluid ounce', 'fluid ounces',
            'cup', 'cups',
            'pint', 'pints',
            'quart', 'quarts',
            'gallon', 'gallons',
            
            // Volume - Bar/Cocktail Specific
            'shot', 'shots',
            'jigger', 'jiggers',
            'dash', 'dashes',
            'splash', 'splashes',
            'drop', 'drops',
            'barspoon', 'barspoons',
            
            // Volume - Cooking
            'tsp', 'teaspoon', 'teaspoons',
            'tbsp', 'tablespoon', 'tablespoons',
            
            // Weight
            'g', 'gram', 'grams',
            'kg', 'kilogram', 'kilograms',
            'lb', 'pound', 'pounds',
            'mg', 'milligram', 'milligrams',
            
            // Count
            'piece', 'pieces',
            'slice', 'slices',
            'wedge', 'wedges',
            'leaf', 'leaves',
            'sprig', 'sprigs',
            'whole',
            'half', 'halves',
            'quarter', 'quarters',
            
            // Other
            'to taste',
            'as needed',
            'pinch',
        ];

        return in_array(strtolower(trim($unit)), $validUnits);
    }

    /**
     * Sanitize unit input
     */
    public static function sanitizeUnit(string $unit): string
    {
        return trim($unit);
    }

    /**
     * Get list of valid units for reference
     */
    public static function getValidUnits(): array
    {
        return [
            'volume_metric' => ['ml', 'cl', 'l'],
            'volume_imperial' => ['oz', 'cup', 'pint', 'quart', 'gallon'],
            'volume_bar' => ['shot', 'jigger', 'dash', 'splash', 'drop', 'barspoon'],
            'volume_cooking' => ['tsp', 'tbsp'],
            'weight' => ['g', 'kg', 'mg', 'lb'],
            'count' => ['piece', 'slice', 'wedge', 'leaf', 'sprig', 'whole', 'half', 'quarter'],
            'other' => ['to taste', 'as needed', 'pinch']
        ];
    }
}