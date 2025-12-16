import { DETAIL_VIEW_API_URL } from "../config.js";

/**
* Fetch Dashboard from API with proper error handling

* @returns {Promise<Array|Object>} Array of products or error object
*/
export async function fetchDashboard() {
    try {
        const response = await fetch(`${DETAIL_VIEW_API_URL}/dashboard`, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            const errorText = await response.text().catch(() => '');

            if (response.status === 401) {
                window.location.href = '/royal-liquor/public/auth/auth.php';
                return { error: 'Please login to continue' };
            }

            if (response.status === 403) {
                return { error: 'Access denied. Admin privileges required.' };
            }

            // Check if response is HTML (PHP error)
            if (errorText.includes('<') && errorText.includes('>') && errorText.includes('<br')) {
                console.error('Server returned HTML instead of JSON:', errorText);
                return { error: 'Server error: API endpoint returned HTML instead of JSON. Check server logs.' };
            }

            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const responseText = await response.text();

        // Check if response is HTML instead of JSON
        if (responseText.includes('<') && responseText.includes('>') && responseText.includes('<br')) {
            console.error('Server returned HTML instead of JSON:', responseText);
            return { error: 'Server error: API endpoint returned HTML instead of JSON. Check server logs.' };
        }

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError, 'Response text:', responseText);
            return { error: 'Invalid JSON response from server. Check server logs.' };
        }

        if (!data.success) {
            throw new Error(data.message || 'Failed to fetch stats');
        }

        return data.data || [];

    } catch (error) {
        console.error('Error fetching dashboard stats:', error);
        return { error: error.message };
    }
}

