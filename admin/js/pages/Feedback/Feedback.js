import { fetchFeedback, fetchModalDetails } from "./Feedback.utils.js";
import { escapeHtml, formatDate, formatOrderDate } from "../../utils.js";

const DEFAULT_LIMIT = 5;
let currentOffset = 0;
let currentQuery = '';

async function loadMoreFeedback() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const feedback = await fetchFeedback(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (feedback.error) {
            return `<tr><td colspan="7" class="feedback_error-cell">Error: ${escapeHtml(feedback.error)}</td></tr>`;
        }

        if (feedback.length === 0) {
            return `<tr><td colspan="7" class="feedback_no-data-cell">No more feedback to load</td></tr>`;
        }

        return feedback.map(item => renderFeedbackRow(item)).join('');
    } catch (error) {
        console.error('Error loading more feedback:', error);
        return `<tr><td colspan="7" class="feedback_error-cell">Failed to load feedback</td></tr>`;
    }
}

function renderFeedbackRow(item) {
    return `
        <tr class="feedback_row feedback-row" data-feedback-id="${item.id}">
            <td class="feedback_cell">${item.id}</td>
            <td class="feedback_cell">${item.rating}</td>
            <td class="feedback_cell">
                <span class="feedback_badge ${item.is_active ? 'feedback_status_paid' : 'feedback_status_cancelled'}">
                    ${item.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="feedback_cell">${formatDate(item.created_at)}</td>
            <td class="feedback_cell">${escapeHtml(item.user_name)}</td>
            <td class="feedback_cell">${escapeHtml(item.product_name)}</td>
            <td class="feedback_cell">${item.is_verified_purchase ? 'Yes' : 'No'}</td>
            <td class="feedback_cell feedback_actions">
                <button class="feedback_btn-view " data-id="${item.id}" title="View Details">👁️ View</button>
                <a href="manage/feedback/update.php?id=${item.id}" class="feedback_btn-edit btn-edit" title="Edit Feedback">✏️ Edit</a>
            </td>
        </tr>
    `;
}

export const Feedback = async () => {
    try {
        currentOffset = 0;
        currentQuery = '';
        const feedback = await fetchFeedback(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (feedback.error) {
            return `
                <div class="feedback_table feedback-table">
                    <div class="feedback_error-box">
                        <strong>Error:</strong> ${escapeHtml(feedback.error)}
                    </div>
                </div>
            `;
        }

        if (feedback.length === 0) {
            return `
                <div class="feedback_table feedback-table">
                    <div class="feedback_no-data-box">
                        <p>📭 No feedback found.</p>
                    </div>
                </div>
            `;
        }

        const tableRows = feedback.map(item => renderFeedbackRow(item)).join('');

        return `
            <div class="feedback_table feedback-table">
                <div class="feedback_header table-header">
                    <h2>Feedback Management (${feedback.length}${feedback.length === DEFAULT_LIMIT ? '+' : ''})</h2>

                    <div class="feedback_header-actions" style="display:flex; gap:8px; align-items:center;">
                        <input id="feedback-search-input" class="feedback_search-input" type="search" placeholder="Search user or product name" aria-label="Search feedback" />
                          <a href="manage/feedback/create.php" class="feedback_btn-primary btn-primary"> Create </a>
                        <button id="feedback_refresh-btn" class="feedback_btn-refresh">
                            🔄 Refresh
                        </button>
                    </div>
                </div>

                <div class="feedback_wrapper table-wrapper">
                    <table class="feedback_data-table feedback-data-table">
                        <thead>
                            <tr class="feedback_header-row table-header-row">
                                <th>ID</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>User Name</th>
                                <th>Product Name</th>
                                <th>Verified Purchase</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="feedback-table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>

                <div id="feedback_load-more-wrapper" class="feedback_load-more-wrapper" style="text-align:center;">
                    ${feedback.length === DEFAULT_LIMIT ? `
                        <button id="feedback_load-more-btn" class="feedback_btn-load-more btn-load-more">
                            Load More Feedback
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering feedback table:', error);
        return `
            <div class="feedback_table feedback-table">
                <div class="feedback_error-box">
                    <strong>Error:</strong> Failed to load feedback table
                </div>
            </div>
        `;
    }
};

const detailsHtml = (item) => {
    return `
<div class="feedback_card feedback-card">
    <div class="feedback_card-header feedback-card-header">
        <span>Feedback Details</span>
        <span class="feedback_badge ${item.is_active ? 'feedback_status_paid' : 'feedback_status_cancelled'}">
            ${item.is_active ? 'Active' : 'Inactive'}
        </span>
        <button class="feedback_close-btn modal-close-btn">&times;</button>
    </div>

    <div class="feedback_section-title feedback-section-title">Basic Info</div>
    <div class="feedback_data-grid feedback-data-grid">
        <div class="feedback_field data-field">
            <strong class="feedback_label data-label">ID</strong>
            <span class="feedback_value data-value">${item.id || 'N/A'}</span>
        </div>
        <div class="feedback_field data-field">
            <strong class="feedback_label data-label">Rating</strong>
            <span class="feedback_value data-value">${item.rating || 'N/A'}</span>
        </div>
        <div class="feedback_field data-field">
            <strong class="feedback_label data-label">Comment</strong>
            <span class="feedback_value data-value">${item.comment ? escapeHtml(item.comment) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="feedback_field data-field">
            <strong class="feedback_label data-label">Verified Purchase</strong>
            <span class="feedback_value data-value">${item.is_verified_purchase ? 'Yes' : 'No'}</span>
        </div>
    </div>

    <div class="feedback_section-title feedback-section-title">User & Product</div>
    <div class="feedback_data-grid feedback-data-grid">
        <div class="feedback_field data-field">
            <strong class="feedback_label data-label">User Name</strong>
            <span class="feedback_value data-value">${item.user_name ? escapeHtml(item.user_name) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="feedback_field data-field">
            <strong class="feedback_label data-label">User Email</strong>
            <span class="feedback_value data-value">${item.user_email ? escapeHtml(item.user_email) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="feedback_field data-field">
            <strong class="feedback_label data-label">Product Name</strong>
            <span class="feedback_value data-value">${item.product_name ? escapeHtml(item.product_name) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="feedback_field data-field">
            <strong class="feedback_label data-label">Product Slug</strong>
            <span class="feedback_value data-value">${item.product_slug ? escapeHtml(item.product_slug) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="feedback_field data-field">
            <strong class="feedback_label data-label">Purchase Count</strong>
            <span class="feedback_value data-value">${item.purchase_count || 0}</span>
        </div>
    </div>

    <div class="feedback_section-title feedback-section-title">Timeline</div>
    <div class="feedback_data-grid feedback-data-grid">
        <div class="feedback_field data-field">
            <strong class="feedback_label data-label">Created At</strong>
            <span class="feedback_value data-value">${item.created_at ? formatDate(item.created_at) : 'N/A'}</span>
        </div>
        <div class="feedback_field data-field">
            <strong class="feedback_label data-label">Updated At</strong>
            <span class="feedback_value data-value">${item.updated_at ? formatDate(item.updated_at) : 'N/A'}</span>
        </div>
    </div>

    <div class="feedback_footer card-footer">
        <a href="manage/feedback/update.php?id=${item.id}" class="feedback_btn-primary btn-primary">
            Edit Feedback
        </a>
    </div>
</div>
`;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(id);

        if (!result || !result.success) {
            throw new Error(result?.error || result?.message || 'Failed to fetch feedback details');
        }

        const item = result.feedback;

        if (!item || typeof item !== 'object' || !item.id) {
            throw new Error('Invalid feedback data format');
        }

        return detailsHtml(item);

    } catch (error) {
        throw new Error(error.message || 'Failed to load feedback details');
    }
};



export const feedbackListeners = async () => {
    
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
            const results = await fetchFeedback(DEFAULT_LIMIT, 0, currentQuery);

            const tbody = document.getElementById('feedback-table-body');
            const loadMoreWrapper = document.getElementById('feedback_load-more-wrapper');

            if (!tbody) return;

            if (results.error) {
                tbody.innerHTML = `<tr><td colspan="7" class="feedback_error-cell">${escapeHtml(results.error)}</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            if (!results.length) {
                tbody.innerHTML = `<tr><td colspan="7" class="feedback_no-data-cell">No feedback found</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            tbody.innerHTML = results.map(renderFeedbackRow).join('');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = results.length === DEFAULT_LIMIT ? `<button id="feedback_load-more-btn" class="feedback_btn-load-more btn-load-more">Load More Feedback</button>` : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('click', async (e) => {
        // view modal
        if (e.target.matches('.feedback_btn-view') || e.target.closest('.feedback_btn-view')) {
            e.preventDefault();
            const button = e.target.matches('.feedback_btn-view') ? e.target : e.target.closest('.feedback_btn-view');
            const feedbackId = button.dataset.id;

            if (!feedbackId) return;

            modalBody.innerHTML = '<div class="modal-loading">⏳ Loading feedback details...</div>';
            modal.classList.add('active');

            try {
                const html = await renderModal(parseInt(feedbackId));
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
                        <h3 class="modal-error-title">Error Loading Feedback</h3>
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
        if (e.target.id === 'feedback_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';

            try {
                const html = await loadMoreFeedback();
                document.getElementById('feedback-table-body').insertAdjacentHTML('beforeend', html);

                if (html.includes('No more feedback to load') || html.includes('Failed to load')) {
                    button.textContent = 'No more feedback to load';
                    button.disabled = true;
                } else {
                    button.disabled = false;
                    button.textContent = 'Load More Feedback';
                }
            } catch (error) {
                console.error('Error loading more feedback:', error);
                button.disabled = false;
                button.textContent = 'Load More Feedback';
            }
        }

        // refresh
        if (e.target.id === 'feedback_refresh-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = '🔄 Refreshing...';

            try {
                currentOffset = 0;
                currentQuery = '';
                const content = await Feedback();
                document.querySelector('.feedback-table').outerHTML = content;
            } catch (error) {
                console.error('Error refreshing feedback:', error);
                button.disabled = false;
                button.textContent = '🔄 Refresh';
            }
        }
    });

    // wire search input
    document.addEventListener('input', (e) => {
        if (e.target && e.target.id === 'feedback-search-input') {
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
;

}





window.loadMoreFeedback = loadMoreFeedback;
window.fetchFeedback = fetchFeedback;