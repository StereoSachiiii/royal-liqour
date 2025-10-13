
const API_URL = 'http://localhost/royal-liquor/admin/api/users.php';
const DEFAULT_LIMIT = 50;

let currentOffset = 0;

async function fetchUsers(limit = DEFAULT_LIMIT, offset = 0) {
    try {
        const response = await fetch(`${API_URL}?action=getAllUsers&limit=${limit}&offset=${offset}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include' 
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Failed to fetch users');
        }

        return data.users || [];
    } catch (error) {
        console.error('Error fetching users:', error);
        return { error: error.message };
    }
}

async function loadMoreUsers() {
    currentOffset += DEFAULT_LIMIT;
    const users = await fetchUsers(DEFAULT_LIMIT, currentOffset);
    if (users.error) {
        return `<div class="users-table-error">Error: ${users.error}</div>`;
    }

    if (users.length === 0) {
        return '';
    }

    return users.map(user => `
        <tr>
            <td style="border: 1px solid #ddd; padding: 8px;">${user.id}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${user.name}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${user.email}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${user.phone || '-'}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${user.is_active ? 'Yes' : 'No'}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${user.is_admin ? 'Yes' : 'No'}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${new Date(user.created_at).toLocaleString()}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${user.last_login_at ? new Date(user.last_login_at).toLocaleString() : '-'}</td>
        </tr>
    `).join('');
}

export const Users = async () => {
    currentOffset = 0;
    const users = await fetchUsers(DEFAULT_LIMIT, currentOffset);

    if (users.error) {
        return `
            <div class="users-table">
                <p style="color: red;">Error: ${users.error}</p>
            </div>
        `;
    }

    if (users.length === 0) {
        return `
            <div class="users-table">
                <p>No users found.</p>
            </div>
        `;
    }

    return `
        <div class="users-table">
            <table style="border-collapse: collapse; width: 100%; border: 1px solid #ddd;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">ID</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Name</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Email</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Phone</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Active</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Admin</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Created At</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Last Login</th>
                    </tr>
                </thead>
                <tbody id="users-table-body">
                    ${users.map(user => `
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px;">${user.id}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">${user.name}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">${user.email}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">${user.phone || '-'}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">${user.is_active ? 'Yes' : 'No'}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">${user.is_admin ? 'Yes' : 'No'}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">${new Date(user.created_at).toLocaleString()}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">${user.last_login_at ? new Date(user.last_login_at).toLocaleString() : '-'}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
            ${users.length === DEFAULT_LIMIT ? `
                <button onclick="loadMoreUsers().then(html => document.getElementById('users-table-body').insertAdjacentHTML('beforeend', html))"
                        style="margin-top: 10px; padding: 8px 16px; cursor: pointer;">
                    Load More
                </button>
            ` : ''}
        </div>
    `;
};

window.loadMoreUsers = loadMoreUsers;
