import { API_URL} from "./config.js";
/**
 * 
 * @param {Number} userId: 
 * @returns 
 */

export const fetchUserAddresses = async (addressId) => {
    try {
        const response = await fetch(`${API_URL}addresses.php?id=${addressId}`, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw Error(`Error fetching address: ${response.statusText}`);
        }

        const body = await response.json();
        const addr = body.data;

        return formatAddress(addr);

    } catch (error) {
        console.error(error);
        return "Address unavailable";
    }
};
/**
 * Format a full address object into a clean display string.
 * 
 * @param {Object} addr - address object returned by API
 * @returns {String} formatted address
 */
export const formatAddress = (addr) => {
    if (!addr || typeof addr !== 'object') return 'Address unavailable';

    const {
        recipient_name,
        address_line1,
        address_line2,
        city,
        state,
        postal_code,
        country,
        phone
    } = addr;

    return [
        recipient_name ? `${recipient_name}` : '',
        address_line1 ? `${address_line1}` : '',
        address_line2 ? `${address_line2}` : '',
        city || state ? `${city || ''}${state ? ', ' + state : ''}` : '',
        postal_code ? `${postal_code}` : '',
        country ? `${country}` : '',
        phone ? `Phone: ${phone}` : ''
    ]
    .filter(Boolean) // remove empty fields
    .join(', ');
};


export const parseAddresses = (addresses) => {
    if (!Array.isArray(addresses)) return "<p>No addresses found.</p>";

    return addresses.map(addr => `
        <div class="address-card">
            <strong>Type:</strong> ${addr.address_type || '-'}<br>
            <strong>Name:</strong> ${addr.recipient_name || '-'}<br>
            <strong>Phone:</strong> ${addr.phone || '-'}<br>
            <strong>Address:</strong> 
                ${addr.address_line1 || '-'} 
                ${addr.address_line2 || ''}<br>
            ${addr.city || '-'}, ${addr.state || '-'}<br>
            <strong>Postal Code:</strong> ${addr.postal_code || '-'}<br>
            <strong>Country:</strong> ${addr.country || '-'}
        </div>
    `).join('');
};

/**
 * Fetch addresses by user_id
 * @param {Number} id
 * @returns {Promise<Array|Object>}
 */
export const getAddresses = async (id) => {
    try {
        const response = await fetch(
            `http://localhost/royal-liquor/admin/api/addresses.php?user_id=${id}`,
            {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin'
            }
        );

        if (!response.ok) {
            throw new Error(`Failed to fetch addresses: ${response.statusText}`);
        }

        const body = await response.json();
        return body.data; // FIX
    } catch (error) {
        return { error: error.message };
    }
};
