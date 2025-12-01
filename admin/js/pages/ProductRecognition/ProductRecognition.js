import { fetchProductRecognition, fetchModalDetails } from "./ProductRecognition.utils.js";
import { escapeHtml, formatDate, formatOrderDate } from "../../utils.js";

// ...existing code...
const DEFAULT_LIMIT = 5;
let currentOffset = 0;
let currentQuery = '';

async function loadMoreProductRecognition() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const recognitions = await fetchProductRecognition(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (recognitions.error) {
            return `<tr><td colspan="8" class="product_recognition_error-cell">Error: ${escapeHtml(recognitions.error)}</td></tr>`;
        }

        if (recognitions.length === 0) {
            return `<tr><td colspan="8" class="product_recognition_no-data-cell">No more product recognitions to load</td></tr>`;
        }

        return recognitions.map(recognition => renderProductRecognitionRow(recognition)).join('');
    } catch (error) {
        console.error('Error loading more product recognitions:', error);
        return `<tr><td colspan="8" class="product_recognition_error-cell">Failed to load product recognitions</td></tr>`;
    }
}

function renderProductRecognitionRow(recognition) {
    return `
        <tr class="product_recognition_row product-recognition-row" data-recognition-id="${recognition.id}">
            <td class="product_recognition_cell">${recognition.id}</td>
            <td class="product_recognition_cell">${escapeHtml(recognition.session_id)}</td>
            <td class="product_recognition_cell">${recognition.matched_product_id || 'NO MATCH'}</td>
            <td class="product_recognition_cell">${escapeHtml(recognition.matched_product_name || 'NO MATCH')}</td>
            <td class="product_recognition_cell">${recognition.confidence_score ? recognition.confidence_score : '0'}</td>
            <td class="product_recognition_cell">${escapeHtml(recognition.api_provider || '-')}</td>
            <td class="product_recognition_cell">${formatDate(recognition.created_at)}</td>
            <td class="product_recognition_cell">${escapeHtml(recognition.user_name || '-')}</td>
            <td class="product_recognition_cell product_recognition_actions">
                <button class="product_recognition_btn-view" data-id="${recognition.id}" title="View Details">👁️ View</button>
                <a href="manage/product_recognition/update.php?id=${recognition.id}" class="product_recognition_btn-edit btn-edit" title="Edit Product Recognition">✏️ Edit</a>
            </td>
        </tr>
    `;
}

export const ProductRecognition = async () => {
    try {
        currentOffset = 0;
        currentQuery = '';
        const recognitions = await fetchProductRecognition(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (recognitions.error) {
            return `
                <div class="product_recognition_table product-recognition-table">
                    <div class="product_recognition_error-box">
                        <strong>Error:</strong> ${escapeHtml(recognitions.error)}
                    </div>
                </div>
            `;
        }

        if (recognitions.length === 0) {
            return `
                <div class="product_recognition_table product-recognition-table">
                    <div class="product_recognition_no-data-box">
                        <p>📭 No product recognitions found.</p>
                    </div>
                </div>
            `;
        }
        console.log(recognitions);

        const tableRows = recognitions.map(recognition => renderProductRecognitionRow(recognition)).join('');

        return `
            <div class="product_recognition_table product-recognition-table">
                <div class="product_recognition_header table-header">
                    <h2>Product Recognition Management (${recognitions.length}${recognitions.length === DEFAULT_LIMIT ? '+' : ''})</h2>

                    <div class="product_recognition_header-actions" style="display:flex; gap:8px; align-items:center;">
                        <input id="product_recognition-search-input" class="product_recognition_search-input" type="search" placeholder="Search session id or user" aria-label="Search product recognitions" />
                                    <a href="manage/product_recognition/create.php" class="product_recognition_btn-primary btn-primary">
          Create
        </a>
                        <button id="product_recognition_refresh-btn" class="product_recognition_btn-refresh">
                            🔄 Refresh
                        </button>
                    </div>
                </div>

                <div class="product_recognition_wrapper table-wrapper">
                    <table class="product_recognition_data-table product-recognition-data-table">
                        <thead>
                            <tr class="product_recognition_header-row table-header-row">
                                <th>ID</th>
                                <th>Session ID</th>
                                <th>Matched Product ID</th>
                                <th>Matched Product Name</th>
                                <th>Confidence Score</th>
                                <th>API Provider</th>
                                <th>Created At</th>
                                <th>User Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="product_recognition-table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>

                <div id="product_recognition_load-more-wrapper" class="product_recognition_load-more-wrapper" style="text-align:center;">
                    ${recognitions.length === DEFAULT_LIMIT ? `
                        <button id="product_recognition_load-more-btn" class="product_recognition_btn-load-more btn-load-more">
                            Load More Product Recognitions
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering product recognition table:', error);
        return `
            <div class="product_recognition_table product-recognition-table">
                <div class="product_recognition_error-box">
                    <strong>Error:</strong> Failed to load product recognition table
                </div>
            </div>
        `;
    }
};

// ...existing code...
const detailsHtml = (recognition) => {
    return `
<div class="product_recognition_card product-recognition-card">
    <div class="product_recognition_card-header product-recognition-card-header">
        <span>Product Recognition Details</span>
        <button class="product_recognition_close-btn modal-close-btn">&times;</button>
    </div>

    <div class="product_recognition_section-title product-recognition-section-title">Basic Info</div>
    <div class="product_recognition_data-grid product-recognition-data-grid">
        <div class="product_recognition_field data-field">
            <strong class="product_recognition_label data-label">ID</strong>
            <span class="product_recognition_value data-value">${recognition.id || 'N/A'}</span>
        </div>
        <div class="product_recognition_field data-field">
            <strong class="product_recognition_label data-label">Session ID</strong>
            <span class="product_recognition_value data-value">${escapeHtml(recognition.session_id)}</span>
        </div>
        <div class="product_recognition_field data-field">
            <strong class="product_recognition_label data-label">Image URL</strong>
            <span class="product_recognition_value data-value">
                ${recognition.image_url ? `<a href="${escapeHtml(recognition.image_url)}" target="_blank" class="link-primary">View Image</a>` : '<span class="data-empty">-</span>'}
            </span>
        </div>
        <div class="product_recognition_field data-field">
            <strong class="product_recognition_label data-label">Recognized Text</strong>
            <span class="product_recognition_value data-value">${recognition.recognized_text ? escapeHtml(recognition.recognized_text) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="product_recognition_field data-field">
            <strong class="product_recognition_label data-label">Recognized Labels</strong>
            <span class="product_recognition_value data-value">${recognition.recognized_labels ? recognition.recognized_labels.join(', ') : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="product_recognition_field data-field">
            <strong class="product_recognition_label data-label">Matched Product Name</strong>
            <span class="product_recognition_value data-value">${recognition.matched_product_name ? escapeHtml(recognition.matched_product_name) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="product_recognition_field data-field">
            <strong class="product_recognition_label data-label">Matched Product Slug</strong>
            <span class="product_recognition_value data-value">${recognition.matched_product_slug ? escapeHtml(recognition.matched_product_slug) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="product_recognition_field data-field">
            <strong class="product_recognition_label data-label">Confidence Score</strong>
            <span class="product_recognition_value data-value">${recognition.confidence_score ? Number(recognition.confidence_score).toFixed(2) : '-'}</span>
        </div>
        <div class="product_recognition_field data-field">
            <strong class="product_recognition_label data-label">API Provider</strong>
            <span class="product_recognition_value data-value">${recognition.api_provider || '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="product_recognition_field data-field">
            <strong class="product_recognition_label data-label">Created At</strong>
            <span class="product_recognition_value data-value">${recognition.created_at ? formatDate(recognition.created_at) : 'N/A'}</span>
        </div>
    </div>

    <div class="product_recognition_section-title product-recognition-section-title">User Info</div>
    <div class="product_recognition_data-grid product-recognition-data-grid">
        <div class="product_recognition_field data-field">
            <strong class="product_recognition_label data-label">User Name</strong>
            <span class="product_recognition_value data-value">${recognition.user_name ? escapeHtml(recognition.user_name) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="product_recognition_field data-field">
            <strong class="product_recognition_label data-label">User Email</strong>
            <span class="product_recognition_value data-value">${recognition.user_email ? escapeHtml(recognition.user_email) : '<span class="data-empty">-</span>'}</span>
        </div>
    </div>

    <div class="product_recognition_footer card-footer">
        <a href="manage/product_recognition/update.php?id=${recognition.id}" class="product_recognition_btn-primary btn-primary">
            Edit Product Recognition
        </a>
    </div>
</div>
`;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(id);
        console.log(result);

        if (!result || !result.success) {
            throw new Error(result?.error || result?.message || 'Failed to fetch product recognition details');
        }

        const recognition = result.product_recognition;

        console.log(recognition);
        return detailsHtml(recognition);

    } catch (error) {
        throw new Error(error.message || 'Failed to load product recognition details');
    }
};


export const productRecognitionListeners = async () => {
        const modal = document.getElementById('modal');
    const modalBody = document.getElementById('modal-body');
    const modalClose = document.getElementById('modal-close');

    // search debounce helper
    function debounce(fn, wait = 300) {
        let t = null;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), wait);
        };
    }

    async function performSearch(query) {
        try {
            currentQuery = query || '';
            currentOffset = 0;
            const results = await fetchProductRecognition(DEFAULT_LIMIT, 0, currentQuery);

            const tbody = document.getElementById('product_recognition-table-body');
            const loadMoreWrapper = document.getElementById('product_recognition_load-more-wrapper');

            if (!tbody) return;

            if (results.error) {
                tbody.innerHTML = `<tr><td colspan="8" class="product_recognition_error-cell">${escapeHtml(results.error)}</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            if (!results.length) {
                tbody.innerHTML = `<tr><td colspan="8" class="product_recognition_no-data-cell">No product recognitions found</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            tbody.innerHTML = results.map(renderProductRecognitionRow).join('');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = results.length === DEFAULT_LIMIT ? `<button id="product_recognition_load-more-btn" class="product_recognition_btn-load-more btn-load-more">Load More Product Recognitions</button>` : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);
    document.addEventListener('click', async (e) => {
        if (e.target.matches('.product_recognition_btn-view') || e.target.closest('.product_recognition_btn-view')) {
            console.log('clicked');
            e.preventDefault();
            const button = e.target.matches('.product_recognition_btn-view') ? e.target : e.target.closest('.product_recognition_btn-view');
            const recognitionId = button.dataset.id;

            if (!recognitionId) return;

            modalBody.innerHTML = '<div class="modal-loading">⏳ Loading product recognition details...</div>';
            modal.classList.add('active');

            try {
                const html = await renderModal(parseInt(recognitionId));
                modalBody.innerHTML = html;

                const closeBtn = modalBody.querySelector('.modal-close-btn');
                if (closeBtn) {
                    closeBtn.addEventListener('click', () => {
                        modal.classList.remove('active');
                    });
                }

            } catch (error) {
                modalBody.innerHTML = `
                    <div class="modal-error">
                        <div class="modal-error-icon">⚠️</div>
                        <h3 class="modal-error-title">Error Loading Product Recognition</h3>
                        <p class="modal-error-message">${escapeHtml(error.message)}</p>
                        <button class="modal-close-btn modal-error-btn">
                            Close
                        </button>
                    </div>
                `;

                const errorCloseBtn = modalBody.querySelector('.modal-close-btn');
                if (errorCloseBtn) {
                    errorCloseBtn.addEventListener('click', () => {
                        modal.classList.remove('active');
                    });
                }
            }
        }

        // load more
        if (e.target.id === 'product_recognition_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';

            try {
                const html = await loadMoreProductRecognition();
                document.getElementById('product_recognition-table-body').insertAdjacentHTML('beforeend', html);

                if (html.includes('No more product recognitions to load') || html.includes('Failed to load')) {
                    button.textContent = 'No more product recognitions to load';
                    button.disabled = true;
                } else {
                    button.disabled = false;
                    button.textContent = 'Load More Product Recognitions';
                }
            } catch (error) {
                console.error('Error loading more product recognitions:', error);
                button.disabled = false;
                button.textContent = 'Load More Product Recognitions';
            }
        }

        // refresh
        if (e.target.id === 'product_recognition_refresh-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = '🔄 Refreshing...';

            try {
                currentOffset = 0;
                currentQuery = '';
                const content = await ProductRecognition();
                document.querySelector('.product-recognition-table').outerHTML = content;
            } catch (error) {
                console.error('Error refreshing product recognitions:', error);
                button.disabled = false;
                button.textContent = '🔄 Refresh';
            }
        }
    });

    // wire search input
    document.addEventListener('input', (e) => {
        if (e.target && e.target.id === 'product_recognition-search-input') {
            debouncedSearch(e);
        }
    });

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    }

    if (modalClose) {
        modalClose.addEventListener('click', () => {
            modal.classList.remove('active');
        });
    }
}




window.loadMoreProductRecognition = loadMoreProductRecognition;
window.fetchProductRecognition = fetchProductRecognition;