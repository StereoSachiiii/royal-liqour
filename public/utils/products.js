    import { API_URL } from './config.js';

    export async function fetchJSON(url, options = {}) {
        try {
            const res = await fetch(`${API_URL}${url}`, {
                headers: {
                    'Content-Type': 'application/json',
                    ...(options.headers || {}),
                },
                credentials: 'same-origin',
                ...options,
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            const data = await res.json();
            if (!data.success) throw new Error(data.message || 'API Error');
            return data.data;
        } catch (err) {
            console.error('Fetch Error:', err);
            return { error: err.message || err };
        }
    }
export const fetchProductById = async (id) => {
        return fetchJSON(`products.php?id=${id}`);
    };

    export const fetchCategoryById = async (id) => {
        return fetchJSON(`categories.php?id=${id}`);
    };

    export const fetchSupplierById = async (id) => {
        return fetchJSON(`suppliers.php?id=${id}`);
    };

    export const fetchStockByProductId = async (productId) => {
        return fetchJSON(`stock.php?product_id=${productId}`);
    };

    export const fetchFlavorProfileByProductId = async (productId) => {
        return fetchJSON(`flavor_profiles.php?product_id=${productId}`);
    };

    export const fetchFeedbackByProductId = async (productId) => {
        return fetchJSON(`feedback.php?product_id=${productId}`);
    };

    export const fetchProductModalData = async (productId) => {
        const product = await fetchProductById(productId);
        if (product.error) return { error: product.error };

        const [category, supplier, stock, flavorProfile, feedback] = await Promise.all([
            product.category_id ? fetchCategoryById(product.category_id) : null,
            product.supplier_id ? fetchSupplierById(product.supplier_id) : null,
            fetchStockByProductId(productId),
            fetchFlavorProfileByProductId(productId),
            fetchFeedbackByProductId(productId),
        ]);

        const averageRating =
            feedback?.length
                ? feedback.reduce((sum, f) => sum + f.rating, 0) / feedback.length
                : null;

        return {
            id: product.id,
            name: product.name,
            description: product.description,
            price_cents: product.price_cents,
            image_url: product.image_url,
            is_active: product.is_active,
            category: category?.name || null,
            supplier: supplier?.name || null,
            stock: stock?.map(s => ({
                warehouse: s.warehouse_name || 'Unknown',
                quantity: s.quantity,
                reserved: s.reserved,
            })) || [],
            flavor_profile: flavorProfile || null,
            feedback: {
                average_rating: averageRating,
                review_count: feedback?.length || 0,
            },
            created_at: product.created_at,
            updated_at: product.updated_at,
        };
    };