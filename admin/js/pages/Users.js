const API_URL = 'http://localhost/royal-liquor/admin/api/users.php';
const DEFAULT_LIMIT = 50;

let currentOffset = 0;

/**
 * Fetch users from API with proper error handling
 * @param {number} limit - Number of users to fetch
 * @param {number} offset - Offset for pagination
 * @returns {Promise<Array|Object>} Array of users or error object
 */
async function fetchUsers(limit = DEFAULT_LIMIT, offset = 0) {
    try {
        const response = await fetch(`${API_URL}?action=getAllUsers&limit=${limit}&offset=${offset}`, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include'
        });

        // Handle HTTP errors
        if (!response.ok) {
            // Try to get error message from JSON
            const errorData = await response.text().catch(() => ({}));
            
            console.log(errorData);
            if (response.status === 401) {
                // Unauthorized - redirect to login
                window.location.href = '/royal-liquor/public/auth/auth.php';
                return { error: 'Please login to continue' };
            }
            
            if (response.status === 403) {
                // Forbidden - not admin
                return { error: 'Access denied. Admin privileges required.' };
            }
            
            throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        return data.data || [];
        
    } catch (error) {
        console.error('Error fetching users:', error);
        return { error: error.message };
    }
}

/**
 * Fetch single user by ID
 * @param {number} userId - User ID
 * @returns {Promise<Object>} User data or error
 */
async function fetchUserById(userId) {
    try {
        const response = await fetch(`${API_URL}?action=getUserById&id=${userId}`, {
            method: 'GET',
            credentials: 'include'
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            
            if (response.status === 401) {
                window.location.href = '/login.php';
                return { error: 'Please login to continue' };
            }
            
            if (response.status === 403) {
                return { error: 'Access denied. Admin privileges required.' };
            }
            
            if (response.status === 404) {
                return { error: 'User not found' };
            }
            
            throw new Error(errorData.message || `Failed to fetch user`);
        }

        const data = await response.json();
        return { success: true, user: data.data };
        
    } catch (error) {
        console.error('Error fetching user:', error);
        return { error: error.message };
    }
}

/**
 * Load more users for pagination
 * @returns {Promise<string>} HTML string for table rows
 */
async function loadMoreUsers() {
    currentOffset += DEFAULT_LIMIT;
    const users = await fetchUsers(DEFAULT_LIMIT, currentOffset);
    
    if (users.error) {
        return `<tr><td colspan="9" style="text-align: center; color: red; padding: 20px;">Error: ${users.error}</td></tr>`;
    }
    
    if (users.length === 0) {
        return `<tr><td colspan="9" style="text-align: center; padding: 20px;">No more users to load</td></tr>`;
    }

    return users.map(user => renderUserRow(user)).join('');
}

/**
 * Render a single table row
 * @param {Object} user - User object
 * @returns {string} HTML string for table row
 */
function renderUserRow(user) {
    return `
        <tr data-user-id="${user.id}">
            <td style="border: 1px solid #ddd; padding: 8px;">${user.id}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${escapeHtml(user.name)}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${escapeHtml(user.email)}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${user.phone ? escapeHtml(user.phone) : '-'}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">
                <span class="badge ${user.is_active ? 'badge-success' : 'badge-inactive'}">
                    ${user.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td style="border: 1px solid #ddd; padding: 8px;">
                <span class="badge ${user.is_admin ? 'badge-admin' : 'badge-user'}">
                    ${user.is_admin ? 'Admin' : 'User'}
                </span>
            </td>
            <td style="border: 1px solid #ddd; padding: 8px;">${formatDate(user.created_at)}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${user.last_login_at ? formatDate(user.last_login_at) : '-'}</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
                <button class="btn-view" data-id="${user.id}" style="background-color:#007bff; color:white; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; margin-right:4px;" title="View Details">
                    👁️ View
                </button>
                <a href="manage/user/edit.php?id=${user.id}" class="btn-edit" style="background-color:#28a745; color:white; text-decoration:none; padding:6px 12px; border-radius:4px; display:inline-block;" title="Edit User">
                    ✏️ Edit
                </a>
            </td>
        </tr>
    `;
}

/**
 * Render full users table
 * @returns {Promise<string>} HTML string for complete table
 */
export const Users = async () => {
    currentOffset = 0;
    const users = await fetchUsers(DEFAULT_LIMIT, currentOffset);

    if (users.error) {
        return `
            <div class="users-table">
                <div style="padding: 20px; text-align: center; color: red; background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;">
                    <strong>Error:</strong> ${escapeHtml(users.error)}
                </div>
            </div>
        `;
    }

    if (users.length === 0) {
        return `
            <div class="users-table">
                <div style="padding: 40px; text-align: center; color: #6c757d;">
                    <p style="font-size: 18px;">📭 No users found.</p>
                </div>
            </div>
        `;
    }

    const tableRows = users.map(user => renderUserRow(user)).join('');

    return `
        <div class="users-table">
            <div class="table-header" style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="margin: 0;">Users Management (${users.length}${users.length === DEFAULT_LIMIT ? '+' : ''})</h2>
                <button id="refresh-users-btn" style="padding: 8px 16px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    🔄 Refresh
                </button>
            </div>
            
            <div style="overflow-x: auto;">
                <table style="border-collapse: collapse; width: 100%; border: 1px solid #ddd; background-color: white;">
                    <thead>
                        <tr style="background-color: #f8f9fa;">
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">ID</th>
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">Name</th>
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">Email</th>
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">Phone</th>
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">Status</th>
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">Role</th>
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">Created At</th>
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">Last Login</th>
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: center; font-weight: 600;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body">
                        ${tableRows}
                    </tbody>
                </table>
            </div>
            
            ${users.length === DEFAULT_LIMIT ? `
                <div style="margin-top: 15px; text-align: center;">
                    <button id="load-more-btn" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
                        Load More Users
                    </button>
                </div>
            ` : ''}
        </div>
    `;
};

/**
 * Initialize event listeners when DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const modalBody = document.getElementById('modal-body');
    const modalClose = document.getElementById('modal-close');

    if (!modal || !modalClose || !modalBody) {
        console.warn('Modal elements not found');
        return;
    }

    // Modal close handlers
    modalClose.addEventListener('click', () => modal.classList.remove('active'));
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.remove('active');
    });

    // Click delegation for all buttons
    document.addEventListener('click', async (e) => {
        // View user details button
        if (e.target.matches('.btn-view') || e.target.closest('.btn-view')) {
            const button = e.target.matches('.btn-view') ? e.target : e.target.closest('.btn-view');
            const userId = button.dataset.id;
            
            if (!userId) return;
            
            // Show loading state
            modalBody.innerHTML = '<div style="text-align: center; padding: 40px;">Loading...</div>';
            modal.classList.add('active');
            
            try {
                const result = await fetchUserById(userId);
                
                if (result.error) {
                    modalBody.innerHTML = `
                        <div style="text-align: center; padding: 20px; color: red;">
                            <strong>Error:</strong> ${escapeHtml(result.error)}
                        </div>
                    `;
                    return;
                }
                
                const user = result.user;
                
                modalBody.innerHTML = `
                    <div style="padding: 20px;">
                        <h2 style="margin-top: 0; border-bottom: 2px solid #007bff; padding-bottom: 10px;">User Details</h2>
                        
                        <div class="user-details" style="display: grid; gap: 15px;">
                            <div class="field">
                                <strong style="color: #6c757d;">ID:</strong> 
                                <span>${user.id}</span>
                            </div>

                            <div class="field">
                                <strong style="color: #6c757d;">Name:</strong> 
                                <span>${escapeHtml(user.name)}</span>
                            </div>

                            <div class="field">
                                <strong style="color: #6c757d;">Email:</strong> 
                                <span>${escapeHtml(user.email)}</span>
                            </div>

                            <div class="field">
                                <strong style="color: #6c757d;">Phone:</strong> 
                                <span>${user.phone ? escapeHtml(user.phone) : '-'}</span>
                            </div>

                            <div class="field">
                                <strong style="color: #6c757d;">Status:</strong> 
                                <span class="badge ${user.is_active ? 'badge-success' : 'badge-inactive'}">
                                    ${user.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </div>

                            <div class="field">
                                <strong style="color: #6c757d;">Role:</strong> 
                                <span class="badge ${user.is_admin ? 'badge-admin' : 'badge-user'}">
                                    ${user.is_admin ? 'Admin' : 'User'}
                                </span>
                            </div>

                            <div class="field">
                                <strong style="color: #6c757d;">Created At:</strong> 
                                <span>${formatDate(user.created_at)}</span>
                            </div>

                            <div class="field">
                                <strong style="color: #6c757d;">Last Login:</strong> 
                                <span>${user.last_login_at ? formatDate(user.last_login_at) : 'Never'}</span>
                            </div>
                        </div>

                        <div style="margin-top: 20px; text-align: right;">
                            <a href="manage/user/edit.php?id=${user.id}" class="btn-edit" style="background-color:#28a745; color:white; text-decoration:none; padding:10px 20px; border-radius:4px; display:inline-block;">
                                Edit User
                            </a>
                        </div>
                    </div>
                `;
                
            } catch (err) {
                console.error('Error loading user details:', err);
                modalBody.innerHTML = `
                    <div style="text-align: center; padding: 20px; color: red;">
                        Failed to load user details. Please try again.
                    </div>
                `;
            }
        }

        // Load more button
        if (e.target.id === 'load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';
            
            try {
                const html = await loadMoreUsers();
                document.getElementById('users-table-body').insertAdjacentHTML('beforeend', html);
                
                // Check if we got results
                const newUsers = await fetchUsers(DEFAULT_LIMIT, currentOffset);
                if (newUsers.length < DEFAULT_LIMIT) {
                    button.remove(); // No more users to load
                } else {
                    button.disabled = false;
                    button.textContent = 'Load More Users';
                }
            } catch (error) {
                button.disabled = false;
                button.textContent = 'Load More Users';
                alert('Failed to load more users. Please try again.');
            }
        }

        // Refresh button
        if (e.target.id === 'refresh-users-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = '🔄 Refreshing...';
            
            try {
                // Reload the entire users table
                currentOffset = 0;
                const content = await Users();
                document.querySelector('.users-table').outerHTML = content;
            } catch (error) {
                alert('Failed to refresh users. Please try again.');
            }
        }
    });
});

/**
 * Utility function to escape HTML and prevent XSS
 * @param {string} text - Text to escape
 * @returns {string} Escaped text
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Format date to readable string
 * @param {string} dateString - ISO date string
 * @returns {string} Formatted date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Export for use in other modules
window.loadMoreUsers = loadMoreUsers;
window.fetchUsers = fetchUsers;