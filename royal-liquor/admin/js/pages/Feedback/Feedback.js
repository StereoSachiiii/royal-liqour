import { fetchFeedback, fetchModalDetails } from "./Feedback.utils.js";
import { renderFeedbackEdit, initFeedbackEditHandlers } from "./FeedbackEdit.js";
import { renderFeedbackCreate, initFeedbackCreateHandlers } from "./FeedbackCreate.js";
import { escapeHtml, formatDate, debounce, saveState, getState, openStandardModal, closeModal } from "../../utils.js";

const DEFAULT_LIMIT = 10;
let currentOffset = 0;
let currentQuery = getState('admin:feedback:query', '');
let lastResults = [];

function renderFeedbackRow(item) {
    if (!item || !item.id) return '';

    const userName = escapeHtml(item.user_name || '-');
    const productName = escapeHtml(item.product_name || '-');
    const comment = item.comment ? escapeHtml(item.comment).substring(0, 50) + '...' : '-';

    return `
        <tr data-feedback-id="${item.id}">
            <td>${item.id}</td>
            <td>${item.rating ?? '-'}</td>
            <td>${comment}</td>
            <td>${userName}</td>
            <td>${productName}</td>
            <td><span class="badge ${item.is_verified_purchase ? 'badge-active' : 'badge-inactive'}">${item.is_verified_purchase ? 'Verified' : 'Unverified'}</span></td>
            <td>${formatDate(item.created_at)}</td>
            <td>
                <button class="btn btn-outline btn-sm feedback-view" data-id="${item.id}" title="View Details">👁️ View</button>
                <button class="btn btn-primary btn-sm feedback-edit" data-id="${item.id}" title="Edit">✏️ Edit</button>
            </td>
        </tr>
    `;
}

async function loadMoreFeedback() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const feedback = await fetchFeedback(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (feedback.error) {
            return `<tr><td colspan="8" class="admin-entity__empty">Error: ${escapeHtml(feedback.error)}</td></tr>`;
        }

        if (!feedback.length) {
            return `<tr><td colspan="8" class="admin-entity__empty">No more feedback to load</td></tr>`;
        }

        return feedback.map(renderFeedbackRow).join('');
    } catch (error) {
        console.error('Error loading more feedback:', error);
        return `<tr><td colspan="8" class="admin-entity__empty">Failed to load feedback</td></tr>`;
    }
}

export const Feedback = async () => {
    try {
        currentOffset = 0;
        const feedback = await fetchFeedback(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (feedback.error) {
            lastResults = [];
        } else {
            lastResults = Array.isArray(feedback) ? feedback : [];
        }

        const hasData = lastResults && lastResults.length > 0;
        const tableRows = hasData
            ? lastResults.map(renderFeedbackRow).join('')
            : `<tr><td colspan="8" class="admin-entity__empty">${feedback && feedback.error ? escapeHtml(feedback.error) : 'No feedback found'}</td></tr>`;

        const countLabel = hasData ? `${lastResults.length}${lastResults.length === DEFAULT_LIMIT ? '+' : ''}` : '0';

        return `
            <div class="admin-entity">
                <div class="admin-entity__header">
                    <h2 class="admin-entity__title">Feedback Management (${countLabel})</h2>
                    <div class="admin-entity__actions">
                        <input id="feedback-search-input" class="admin-entity__search" type="search" placeholder="Search feedback..." value="${escapeHtml(currentQuery)}" />
                        <button id="feedback-create-btn" class="btn btn-primary">+ New Feedback</button>
                        <button id="feedback_refresh-btn" class="btn btn-outline btn-sm">🔄 Refresh</button>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="admin-entity__table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>User</th>
                                <th>Product</th>
                                <th>Verified</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="feedback_table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>
                <div id="feedback_load-more-wrapper" style="text-align:center; margin-top: var(--space-4);">
                    ${hasData && lastResults.length === DEFAULT_LIMIT ? `<button id="feedback_load-more-btn" class="btn btn-outline btn-sm">Load More Feedback</button>` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering feedback table:', error);
        return `<div class="admin-entity"><div class="admin-entity__empty"><strong>Error:</strong> Failed to load feedback table</div></div>`;
    }
};

const feedbackDetailsHtml = (item) => {
    if (!item) return '<div class="admin-entity__empty">No feedback data</div>';

    console.log('[Feedback] Rendering details for:', item);

    return `
        <div class="products_card">
            <div class="products_card-header">
                <span>Feedback Details</span>
                <span class="badge ${item.is_active ? 'badge-active' : 'badge-inactive'}">${item.is_active ? 'Active' : 'Inactive'}</span>
            </div>
            <div class="products_section-title">Rating & Comment</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>ID</strong><span>${item.id ?? 'N/A'}</span></div>
                <div class="products_field"><strong>Rating</strong><span>${item.rating ?? '-'}/5</span></div>
                <div class="products_field"><strong>Verified Purchase</strong><span>${item.is_verified_purchase ? 'Yes' : 'No'}</span></div>
            </div>
            <div class="products_data-grid">
                <div class="products_field" style="grid-column: 1 / -1;"><strong>Comment</strong><span>${item.comment ? escapeHtml(item.comment) : '-'}</span></div>
            </div>
            <div class="products_section-title">User & Product</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>User ID</strong><span>${item.user_id ?? '-'}</span></div>
                <div class="products_field"><strong>User Name</strong><span>${escapeHtml(item.user_name || '-')}</span></div>
                <div class="products_field"><strong>User Email</strong><span>${escapeHtml(item.user_email || '-')}</span></div>
                <div class="products_field"><strong>Product ID</strong><span>${item.product_id ?? '-'}</span></div>
                <div class="products_field"><strong>Product Name</strong><span>${escapeHtml(item.product_name || '-')}</span></div>
                <div class="products_field"><strong>Product Slug</strong><span>${escapeHtml(item.product_slug || '-')}</span></div>
                <div class="products_field"><strong>Purchase Count</strong><span>${item.purchase_count ?? 0}</span></div>
            </div>
            <div class="products_section-title">Timeline</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>Created At</strong><span>${formatDate(item.created_at)}</span></div>
                <div class="products_field"><strong>Updated At</strong><span>${item.updated_at ? formatDate(item.updated_at) : '-'}</span></div>
            </div>
            <div class="products_footer">
                <button class="btn btn-primary feedback-edit" data-id="${item.id}">Edit Feedback</button>
            </div>
        </div>
    `;
};

const renderModal = async (id) => {
    try {
        console.log('[Feedback] Fetching modal for ID:', id);
        const result = await fetchModalDetails(Number(id));

        console.log('[Feedback] Modal fetch result:', result);

        if (result.error) {
            throw new Error(result.error);
        }

        if (!result.feedback) {
            throw new Error('No feedback data returned from API');
        }

        return feedbackDetailsHtml(result.feedback);
    } catch (error) {
        console.error('[Feedback] Render modal error:', error);
        throw new Error(error.message || 'Failed to load feedback details');
    }
};

// Attach delegated handlers
(() => {
    async function performSearch(query) {
        try {
            currentQuery = query || '';
            saveState('admin:feedback:query', currentQuery);
            currentOffset = 0;
            const results = await fetchFeedback(DEFAULT_LIMIT, 0, currentQuery);

            lastResults = results.error ? [] : (Array.isArray(results) ? results : []);
            const hasData = lastResults.length > 0;
            const countLabel = hasData ? `${lastResults.length}${lastResults.length === DEFAULT_LIMIT ? '+' : ''}` : '0';

            const titleEl = document.querySelector('.admin-entity__title');
            if (titleEl) titleEl.textContent = `Feedback Management (${countLabel})`;

            const tbody = document.getElementById('feedback_table-body');
            if (tbody) {
                tbody.innerHTML = hasData
                    ? lastResults.map(renderFeedbackRow).join('')
                    : `<tr><td colspan="8" class="admin-entity__empty">${results.error ? escapeHtml(results.error) : 'No feedback found'}</td></tr>`;
            }

            const loadMoreWrapper = document.getElementById('feedback_load-more-wrapper');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = hasData && lastResults.length === DEFAULT_LIMIT
                    ? `<button id="feedback_load-more-btn" class="btn btn-outline btn-sm">Load More Feedback</button>`
                    : '';
            }
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('input', (e) => {
        if (e.target.id === 'feedback-search-input') {
            debouncedSearch(e);
        }
    });

    document.addEventListener('click', async (e) => {
        // View button - feedback-view class only
        if (e.target.matches('.feedback-view') || e.target.closest('.feedback-view')) {
            const btn = e.target.closest('.feedback-view') || e.target;
            const id = btn.dataset.id;
            if (!id) return;

            try {
                const html = await renderModal(id);
                openStandardModal({
                    title: 'Feedback Details',
                    bodyHtml: html,
                    size: 'xl'
                });
            } catch (err) {
                openStandardModal({
                    title: 'Error loading feedback',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(err.message)}</div>`,
                    size: 'xl'
                });
            }
        }

        // Edit button - feedback-edit class only
        if (e.target.matches('.feedback-edit') || e.target.closest('.feedback-edit')) {
            const btn = e.target.closest('.feedback-edit') || e.target;
            const id = btn.dataset.id;
            if (!id) return;

            try {
                const formHtml = await renderFeedbackEdit(parseInt(id));
                const modal = document.getElementById('modal');
                const modalBody = document.getElementById('modal-body');

                modalBody.innerHTML = formHtml;
                modal.classList.remove('hidden');
                modal.classList.add('active');
                modal.style.display = 'flex';

                initFeedbackEditHandlers(modalBody, parseInt(id), (data, action) => {
                    if (action === 'updated' || action === 'deleted') {
                        performSearch(currentQuery);
                    }
                });
            } catch (error) {
                console.error('Error opening edit feedback:', error);
                openStandardModal({
                    title: 'Error',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
                });
            }
        }

        // Create button
        if (e.target.id === 'feedback-create-btn') {
            try {
                const formHtml = await renderFeedbackCreate();
                const modal = document.getElementById('modal');
                const modalBody = document.getElementById('modal-body');

                modalBody.innerHTML = formHtml;
                modal.classList.remove('hidden');
                modal.classList.add('active');
                modal.style.display = 'flex';

                initFeedbackCreateHandlers(modalBody, (data, action) => {
                    if (action === 'created') {
                        performSearch(currentQuery);
                    }
                });
            } catch (error) {
                console.error('Error opening create feedback:', error);
                openStandardModal({
                    title: 'Error',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
                });
            }
        }

        // Load more
        if (e.target.id === 'feedback_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';
            try {
                const html = await loadMoreFeedback();
                document.getElementById('feedback_table-body').insertAdjacentHTML('beforeend', html);

                if (html.includes('No more feedback') || html.includes('Error')) {
                    button.remove();
                } else {
                    button.disabled = false;
                    button.textContent = 'Load More Feedback';
                }
            } catch {
                button.disabled = false;
                button.textContent = 'Load More Feedback';
            }
        }

        // Refresh
        if (e.target.id === 'feedback_refresh-btn') {
            performSearch(currentQuery);
        }
    });
})();

// Export for backwards compatibility
export const feedbackListeners = async () => {
    console.log('feedbackListeners called - listeners are now auto-attached');
};

window.loadMoreFeedback = loadMoreFeedback;