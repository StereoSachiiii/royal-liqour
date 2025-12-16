import { fetchProductRecognition, fetchModalDetails } from "./ProductRecognition.utils.js";
import { renderProductRecognitionEdit, initProductRecognitionEditHandlers } from "./ProductRecognitionEdit.js";
import { renderProductRecognitionCreate, initProductRecognitionCreateHandlers } from "./ProductRecognitionCreate.js";
import { escapeHtml, formatDate, openStandardModal, debounce } from "../../utils.js";

const DEFAULT_LIMIT = 20;
let currentOffset = 0;
let currentQuery = '';
let lastResults = [];

function getStatusClass(status) {
    switch (status) {
        case 'completed':
            return 'badge-active';
        case 'pending':
            return 'badge-warning';
        case 'failed':
            return 'badge-inactive';
        case 'manual_review':
            return 'badge-info';
        default:
            return 'badge-default';
    }
}

function renderRecognitionRow(recognition) {
    const confidenceDisplay = recognition.confidence_score
        ? `${(recognition.confidence_score * 100).toFixed(1)}%`
        : '-';

    const productDisplay = recognition.recognized_product_id
        ? `#${recognition.recognized_product_id}`
        : '-';

    return `
        <tr data-id="${recognition.id}">
            <td>${recognition.id}</td>
            <td>${escapeHtml(recognition.session_id || '-')}</td>
            <td>${productDisplay}</td>
            <td>${confidenceDisplay}</td>
            <td>
                <span class="badge ${getStatusClass(recognition.status)}">
                    ${(recognition.status || 'pending').toUpperCase()}
                </span>
            </td>
            <td>${recognition.processing_time ? recognition.processing_time.toFixed(2) + 'ms' : '-'}</td>
            <td>${formatDate(recognition.created_at)}</td>
            <td>
                <button class="btn btn-outline btn-sm product-recognition-view" data-id="${recognition.id}" title="View">👁️ View</button>
                <button class="btn btn-primary btn-sm product-recognition-edit" data-id="${recognition.id}" title="Edit">✏️ Edit</button>
            </td>
        </tr>
    `;
}

async function loadMoreRecognitions() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const recognitions = await fetchProductRecognition(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (recognitions.error) {
            return `<tr><td colspan="8" class="admin-entity__empty">Error: ${escapeHtml(recognitions.error)}</td></tr>`;
        }

        if (recognitions.length === 0) {
            return `<tr><td colspan="8" class="admin-entity__empty">No more recognitions to load</td></tr>`;
        }

        lastResults = [...lastResults, ...recognitions];
        return recognitions.map(renderRecognitionRow).join('');
    } catch (error) {
        console.error('Error loading more recognitions:', error);
        return `<tr><td colspan="8" class="admin-entity__empty">Failed to load recognitions</td></tr>`;
    }
}

export const ProductRecognition = async () => {
    try {
        currentOffset = 0;
        currentQuery = '';
        const recognitions = await fetchProductRecognition(DEFAULT_LIMIT, currentOffset, currentQuery);

        lastResults = recognitions.error ? [] : (Array.isArray(recognitions) ? recognitions : []);
        const hasData = lastResults.length > 0;
        const countLabel = hasData ? `${lastResults.length}${lastResults.length === DEFAULT_LIMIT ? '+' : ''}` : '0';

        const tableRows = hasData
            ? lastResults.map(renderRecognitionRow).join('')
            : `<tr><td colspan="8" class="admin-entity__empty">${recognitions.error ? escapeHtml(recognitions.error) : 'No recognitions found'}</td></tr>`;

        return `
            <div class="admin-entity">
                <div class="admin-entity__header">
                    <h2 class="admin-entity__title">Product Recognition (${countLabel})</h2>
                    <div class="admin-entity__actions">
                        <input id="product-recognition-search-input" class="admin-entity__search" type="search" 
                               placeholder="Search by session ID..." value="${escapeHtml(currentQuery)}" />
                        <button id="product-recognition-create-btn" class="btn btn-primary">Add Recognition</button>
                        <button id="product-recognition-refresh-btn" class="btn btn-outline btn-sm">🔄 Refresh</button>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="admin-entity__table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Session ID</th>
                                <th>Matched Product</th>
                                <th>Confidence</th>
                                <th>Status</th>
                                <th>Processing Time</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="product-recognition-table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>

                <div id="product-recognition-load-more-wrapper" style="text-align:center; margin-top: var(--space-4);">
                    ${hasData && lastResults.length === DEFAULT_LIMIT ? `<button id="product-recognition-load-more-btn" class="btn btn-outline btn-sm">Load More</button>` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering product recognition table:', error);
        return `<div class="admin-entity"><div class="admin-entity__empty"><strong>Error:</strong> Failed to load product recognition table</div></div>`;
    }
};

// View modal content (products_card styling)
const recognitionDetailsHtml = (recognition) => {
    if (!recognition) return '<div class="admin-entity__empty">No recognition data</div>';

    const confidenceDisplay = recognition.confidence_score
        ? `${(recognition.confidence_score * 100).toFixed(1)}%`
        : '-';

    return `
        <div class="products_card">
            <div class="products_card-header">
                <span>Recognition Details</span>
                <span class="badge ${getStatusClass(recognition.status)}">${(recognition.status || 'pending').toUpperCase()}</span>
            </div>
            <div class="products_section-title">Basic Info</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>ID</strong><span>${recognition.id ?? 'N/A'}</span></div>
                <div class="products_field"><strong>Session ID</strong><span>${escapeHtml(recognition.session_id || '-')}</span></div>
                <div class="products_field"><strong>Matched Product ID</strong><span>${recognition.recognized_product_id || '-'}</span></div>
                <div class="products_field"><strong>Confidence Score</strong><span>${confidenceDisplay}</span></div>
                <div class="products_field"><strong>Processing Time</strong><span>${recognition.processing_time ? recognition.processing_time.toFixed(2) + 'ms' : '-'}</span></div>
            </div>
            ${recognition.image_data ? `
            <div class="products_section-title">Image</div>
            <div style="padding: 1rem; text-align: center;">
                <img src="${escapeHtml(recognition.image_data)}" alt="Recognition Image" 
                     style="max-width: 100%; max-height: 300px; border-radius: 8px;">
            </div>
            ` : ''}
            <div class="products_section-title">Timestamps</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>Created</strong><span>${formatDate(recognition.created_at)}</span></div>
            </div>
            <div class="products_footer">
                <button class="btn btn-primary product-recognition-edit" data-id="${recognition.id}">Edit Recognition</button>
            </div>
        </div>
    `;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(Number(id));
        if (result.error) throw new Error(result.error);
        if (!result.product_recognition) throw new Error('No recognition data returned from API');
        return recognitionDetailsHtml(result.product_recognition);
    } catch (error) {
        console.error('[ProductRecognition] Render modal error:', error);
        throw new Error(error.message || 'Failed to load recognition details');
    }
};

// Attach delegated handlers
(() => {
    async function performSearch(query) {
        try {
            currentQuery = query || '';
            currentOffset = 0;
            const results = await fetchProductRecognition(DEFAULT_LIMIT, 0, currentQuery);

            lastResults = results.error ? [] : (Array.isArray(results) ? results : []);
            const hasData = lastResults.length > 0;
            const countLabel = hasData ? `${lastResults.length}${lastResults.length === DEFAULT_LIMIT ? '+' : ''}` : '0';

            const titleEl = document.querySelector('.admin-entity__title');
            if (titleEl) titleEl.textContent = `Product Recognition (${countLabel})`;

            const tbody = document.getElementById('product-recognition-table-body');
            if (tbody) {
                tbody.innerHTML = hasData
                    ? lastResults.map(renderRecognitionRow).join('')
                    : `<tr><td colspan="8" class="admin-entity__empty">${results.error ? escapeHtml(results.error) : 'No recognitions found'}</td></tr>`;
            }

            const loadMoreWrapper = document.getElementById('product-recognition-load-more-wrapper');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = hasData && lastResults.length === DEFAULT_LIMIT
                    ? '<button id="product-recognition-load-more-btn" class="btn btn-outline btn-sm">Load More</button>'
                    : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('click', async (e) => {
        // View modal
        if (e.target.matches('.product-recognition-view') || e.target.closest('.product-recognition-view')) {
            const btn = e.target.closest('.product-recognition-view');
            const id = btn?.dataset.id;
            if (!id) return;

            try {
                const html = await renderModal(id);
                openStandardModal({
                    title: 'Product Recognition Details',
                    bodyHtml: html,
                    size: 'lg'
                });
            } catch (error) {
                openStandardModal({
                    title: 'Error',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
                });
            }
        }

        // Edit modal (direct injection like Products)
        if (e.target.matches('.product-recognition-edit') || e.target.closest('.product-recognition-edit')) {
            const btn = e.target.closest('.product-recognition-edit');
            const id = btn?.dataset.id;
            if (!id) return;

            console.log('[ProductRecognition] Edit button clicked:', { id });

            try {
                const formHtml = await renderProductRecognitionEdit(parseInt(id));

                const modal = document.getElementById('modal');
                const modalBody = document.getElementById('modal-body');

                if (!modal || !modalBody) {
                    console.error('[ProductRecognition] Modal elements not found!');
                    return;
                }

                modalBody.innerHTML = formHtml;
                modal.classList.remove('hidden');
                modal.classList.add('active');
                modal.style.display = 'flex';

                initProductRecognitionEditHandlers(modalBody, parseInt(id), (data, action) => {
                    if (action === 'updated' || action === 'deleted') {
                        ProductRecognition().then(html => {
                            const adminEntity = document.querySelector('.admin-entity');
                            if (adminEntity) adminEntity.outerHTML = html;
                        });
                    }
                });
            } catch (error) {
                console.error('[ProductRecognition] Error opening edit form:', error);
                openStandardModal({
                    title: 'Error',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
                });
            }
        }

        // Create modal (direct injection like Products)
        if (e.target.id === 'product-recognition-create-btn') {
            console.log('[ProductRecognition] Create button clicked');

            try {
                const formHtml = await renderProductRecognitionCreate();

                const modal = document.getElementById('modal');
                const modalBody = document.getElementById('modal-body');

                if (!modal || !modalBody) {
                    console.error('[ProductRecognition] Modal elements not found!');
                    return;
                }

                modalBody.innerHTML = formHtml;
                modal.classList.remove('hidden');
                modal.classList.add('active');
                modal.style.display = 'flex';

                initProductRecognitionCreateHandlers(modalBody, (data, action) => {
                    if (action === 'created') {
                        ProductRecognition().then(html => {
                            const adminEntity = document.querySelector('.admin-entity');
                            if (adminEntity) adminEntity.outerHTML = html;
                        });
                    }
                });
            } catch (error) {
                console.error('[ProductRecognition] Error opening create form:', error);
                openStandardModal({
                    title: 'Error',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
                });
            }
        }

        // Load More
        if (e.target.id === 'product-recognition-load-more-btn') {
            const btn = e.target;
            btn.disabled = true;
            btn.textContent = 'Loading...';
            const html = await loadMoreRecognitions();
            document.getElementById('product-recognition-table-body').insertAdjacentHTML('beforeend', html);
            if (html.includes('No more') || html.includes('Error') || html.includes('Failed')) {
                btn.disabled = true;
                btn.textContent = 'No more items';
            } else {
                btn.disabled = false;
                btn.textContent = 'Load More';
            }
        }

        // Refresh
        if (e.target.id === 'product-recognition-refresh-btn') {
            const btn = e.target;
            btn.disabled = true;
            btn.textContent = 'Refreshing...';
            const html = await ProductRecognition();
            const container = document.querySelector('.admin-entity');
            if (container) container.outerHTML = html;
        }
    });

    document.addEventListener('input', (e) => {
        if (e.target.id === 'product-recognition-search-input') {
            debouncedSearch(e);
        }
    });
})();

window.loadMoreProductRecognition = loadMoreRecognitions;
window.fetchProductRecognition = fetchProductRecognition;

// Legacy export for backward compatibility
export const productRecognitionListeners = () => {
    console.log('[ProductRecognition] Event listeners already attached via IIFE');
};