// Base URL - all API requests use /api/v1 prefix through router
const BASE_URL = '/royal-liquor/api/v1';

export const API_URL = BASE_URL + '/admin/views';
export const API_USER = BASE_URL + '/users';
export const API_CATEGORY = BASE_URL + '/categories';
export const API_SUPPLIER = BASE_URL + '/suppliers';
export const API_PRODUCT = BASE_URL + '/products';
export const API_ORDER = BASE_URL + '/orders';
export const API_ADDRESS = BASE_URL + '/addresses';
export const API_WAREHOUSE = BASE_URL + '/warehouses';
export const API_ORDER_ITEM = BASE_URL + '/order-items';
export const API_USER_PREFERENCE = BASE_URL + '/user-preferences';
export const API_FEEDBACK = BASE_URL + '/feedback';
export const API_USER_ADDRESS = BASE_URL + '/addresses';
export const API_FLAVOUR_PROFILE = BASE_URL + '/flavour-profiles';
export const API_RECIPE_INGREDIENT = BASE_URL + '/recipe-ingredients';
export const API_STOCK = BASE_URL + '/stock';
export const API_PAYMENTS = BASE_URL + '/payments';
export const API_PRODUCT_RECOGNITION = BASE_URL + '/product-recognition';
export const API_COCKTAIL_RECIPES = BASE_URL + '/cocktail-recipes';
export const API_CART = BASE_URL + '/carts';
export const API_IMAGES = '/royal-liquor/admin/api/images.php'; // Direct access for FormData uploads


export const fetchHandler = async (url, method, body = {}) => {
    try {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin',
            body: method === 'GET' ? null : JSON.stringify(body),
        };

        const response = await fetch(`${url}`, options);

        if (!response.ok) {
            const text = await response.text();
            return { error: true, msg: text || `HTTP error ${response.status}` };
        }


        const json = await response.json();

        console.log(json.data);
        return json.data?.data ?? json.data ?? json ?? {};
    } catch (error) {
        return { error: true, msg: error.message || 'Fetch failed' };
    }
};

// Format address for display
export const formatAddress = (address) => {
    if (!address) return "No address available";
    return `
Recipient Name: ${address.recipient_name}
Phone: ${address.phone}
Address Line 1: ${address.address_line1}
Address Line 2: ${address.address_line2 || "-"}
City: ${address.city}
State: ${address.state || "-"}
Postal Code: ${address.postal_code}
Country: ${address.country}
    `.trim();
};

// Format items for display
export const formatItems = (items) => {
    if (!items || items.length === 0) return "No items found";
    return items.map(item => `
Product Name: ${item.product_name}
Quantity: ${item.quantity}
Price (Cents): ${item.price_cents}
Warehouse: ${item.warehouse_name || "-"}
    `.trim()).join("\n\n");
};

// Format payments for display
export const formatPayments = (payments) => {
    if (!payments || payments.length === 0) return "No payments found";
    return payments.map(payment => `
Payment Method: ${payment.method}
Amount (Cents): ${payment.amount_cents}
Status: ${payment.status}
    `.trim()).join("\n\n");
};

// Populate address dropdowns
export const populateAddressDropdown = (selectElement, addresses, selectedId) => {
    selectElement.innerHTML = '';
    addresses.forEach(addr => {
        const option = document.createElement('option');
        option.value = addr.id;
        option.textContent = `${addr.recipient_name} - ${addr.address_line1}, ${addr.city}`;
        if (addr.id === selectedId) option.selected = true;
        selectElement.appendChild(option);
    });
};