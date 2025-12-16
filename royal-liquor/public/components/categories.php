<div>
    <div class="section-title">Browse Categories</div>
    <div class="categories-container"></div>
</div>

<!-- Category Detail Modal -->
<div class="detail-modal" id="detail-modal-category">
    <div class="detail-modal-body" id="detail-modal-body-category"></div>
</div>

<script type="module">
    import { API } from '<?= BASE_URL ?>utils/api-helper.js';

    // Store categories in memory
    let categoriesData = [];

    // Fetch all categories using real API
    const fetchCategories = async () => {
        console.log('[Categories] Fetching categories from API...');
        try {
            const response = await API.categories.list();
            console.log('[Categories] API Response:', response);
            if (response.success && response.data) {
                const cats = response.data.items || response.data || [];
                console.log('[Categories] Found', cats.length, 'categories');
                return cats;
            }
            console.warn('[Categories] No categories in response');
            return [];
        } catch (e) {
            console.error('[Categories] Failed to load categories:', e);
            return [];
        }
    };

    // Fetch single category
    const fetchCategory = async (id) => {
        try {
            const response = await API.categories.get(id);
            if (response.success && response.data) {
                return response.data;
            }
            return null;
        } catch (e) {
            console.warn('[Categories] Failed to load category:', id);
            return null;
        }
    };

    // Render each category card
    const renderCard = (cat) => {
        const hasImage = cat.image_url && cat.image_url.trim() && !cat.image_url.includes('null');
        const imageHtml = hasImage
            ? `<img src="${cat.image_url}" alt="${cat.name}" class="category-image" loading="lazy">`
            : '<div class="category-placeholder"></div>';

        const badge = cat.is_active
            ? '<span class="category-badge active">Active</span>'
            : '<span class="category-badge inactive">Inactive</span>';

        return `
            <div class="category-card" data-id="${cat.id}">
                <div class="category-image-container">
                    ${imageHtml}
                    ${badge}
                    <div class="category-product-count">${cat.product_count || 0} Products</div>
                </div>
                <div class="category-info">
                    <h3 class="category-name">${cat.name}</h3>
                    <p class="category-description">${cat.description || 'No description available'}</p>
                    <div class="category-actions">
                        <a href="<?= BASE_URL ?>category.php?id=${cat.id}" class="btn-browse">
                            Browse Products
                        </a>
                        <button class="btn-details btn-details-category" data-id="${cat.id}">
                            View Details
                        </button>
                    </div>
                </div>
            </div>
        `;
    };

    // Render modal content
    const renderDetail = (cat) => {
        if (!cat) {
            return `<div style="text-align:center;padding:80px;color:#e74c3c;font-size:1.5rem;">Failed to load category</div>`;
        }

        const hasImage = cat.image_url && cat.image_url.trim() && !cat.image_url.includes('null');
        const imageHtml = hasImage
            ? `<img src="${cat.image_url}" alt="${cat.name}" class="detail-image">`
            : '<div class="no-image-placeholder">No Image Available</div>';

        const status = cat.is_active ? { text: "Active", color: "#4CAF50" } : { text: "Inactive", color: "#e74c3c" };

        const created = cat.created_at ? new Date(cat.created_at).toLocaleDateString() : "—";
        const updated = cat.updated_at ? new Date(cat.updated_at).toLocaleDateString() : "—";

        return `
            <div class="detail-wrapper">
                <button class="close-modal">×</button>
                <div class="detail-image-box">${imageHtml}</div>
                <div class="detail-content">
                    <h1 class="detail-title">${cat.name}</h1>
                    <div class="detail-meta">
                        <span>ID: #${cat.id}</span>
                        <span style="color:${status.color};font-weight:600;">${status.text}</span>
                        <span class="product-count-badge">${cat.product_count || 0} Products</span>
                    </div>
                    <h3>Description</h3>
                    <p>${cat.description || 'No description provided.'}</p>
                    <h3>Info</h3>
                    <p><strong>Created:</strong> ${created}</p>
                    <p><strong>Updated:</strong> ${updated}</p>
                    <div class="detail-actions">
                        <a href="<?= BASE_URL ?>category.php?id=${cat.id}" class="btn-browse-modal">
                            Browse All ${cat.product_count || 0} Products
                        </a>
                    </div>
                </div>
            </div>
        `;
    };

    // Load categories
    document.addEventListener('DOMContentLoaded', async () => {
        const container = document.querySelector('.categories-container');
        const categories = await fetchCategories();

        if (categories.length === 0) {
            container.innerHTML = '<div class="empty-state">No categories available at the moment.</div>';
            return;
        }

        container.innerHTML = categories.map(renderCard).join('');
    });

    // Click handler
    document.addEventListener('click', async (e) => {
        const modal = document.getElementById('detail-modal-category');
        const body = document.getElementById('detail-modal-body-category');

        // Open modal
        if (e.target.closest('.btn-details-category')) {
            const id = e.target.closest('.btn-details-category').dataset.id;
            modal.classList.add('active');
            body.innerHTML = '<div style="text-align:center;padding:60px;">Loading...</div>';
            const cat = await fetchCategory(id);
            body.innerHTML = renderDetail(cat);
        }

        // Close modal
        if (e.target.closest('.close-modal') || e.target === modal) {
            modal.classList.remove('active');
        }
    });

    // Close on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.getElementById('detail-modal-category').classList.remove('active');
        }
    });
</script>

<!-- Your Beautiful CSS (Cleaned & Enhanced) -->
<style>
    .section-title {
        width: 100%;
        height: 100px;
        font-size: 3rem;
        margin-top: 40px;
        text-align: center;
        font-style: italic;
        font-weight: 800;
        color: #1a1a1a;
    }

    .categories-container {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        padding: 20px;
        background: #f5f5f5;
        max-width: 1400px;
        margin: 0 auto;
    }

    .category-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: relative;
    }

    .category-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 12px 24px rgba(0,0,0,0.15);
    }

    .category-image-container {
        width: 100%;
        height: 220px;
        background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%);
        position: relative;
        overflow: hidden;
    }

    .category-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .category-card:hover .category-image {
        transform: scale(1.08);
    }

    .category-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: #000;
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .category-badge.active { background: #4CAF50; }
    .category-badge.inactive { background: #999; }

    .category-info {
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .category-name {
        font-size: 18px;
        font-weight: 700;
        color: #000;
        margin: 0;
    }

    .category-description {
        font-size: 14px;
        color: #666;
        margin: 0;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .category-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 12px;
    }

    #browse {
        width: 100%;
        height: 100%;
        background: #000;
        color: white;
        text-decoration: none;
        padding: 12px;
        border-radius: 6px;
        font-weight: 600;
        text-align: center;
        transition: 0.2s;
    }

    #browse:hover { background: #333; }

    .btn-details {
        background: white;
        color: #000;
        border: 2px solid #000;
        padding: 12px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
    }

    .btn-details:hover {
        background: #000;
        color: white;
    }

    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 80px 20px;
        color: #999;
        font-size: 1.2rem;
    }

    /* Modal Styles */
    .detail-modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.92);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .detail-modal.active { display: flex; }

    .detail-modal-body {
        background: white;
        border-radius: 16px;
        max-width: 900px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        position: relative;
        padding: 0;
    }

    .detail-wrapper {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        padding: 40px;
    }

    .detail-image-box {
        border-radius: 12px;
        overflow: hidden;
        background: #f0f0f0;
    }

    .detail-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .no-image-placeholder {
        width: 100%;
        height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #aaa;
        font-size: 1.2rem;
        background: #f8f8f8;
    }

    .detail-content h1 {
        font-size: 2.4rem;
        margin: 0 0 16px;
    }

    .detail-meta {
        display: flex;
        gap: 20px;
        margin-bottom: 24px;
        font-size: 14px;
        color: #555;
    }

    .detail-content h3 {
        margin: 24px 0 12px;
        color: #000;
    }

    .detail-content p {
        color: #444;
        line-height: 1.7;
    }

    .close-modal {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 48px;
        height: 48px;
        background: rgba(0,0,0,0.7);
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 32px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .close-modal:hover { background: #000; }

    @media (max-width: 768px) {
        .categories-container { grid-template-columns: 1fr; padding: 12px; }
        .detail-wrapper { grid-template-columns: 1fr; }
        .section-title { font-size: 2.2rem; }
    }
</style>