export const API_URL = 'http://localhost/royal-liquor/admin/api/admin-views.php';
export const API_USER = 'http://localhost/royal-liquor/admin/api/users.php'
export const API_CATEGORY = 'http://localhost/royal-liquor/admin/api/categories.php'
export const API_SUPPLIER =  'http://localhost/royal-liquor/admin/api/suppliers.php'
export const API_PRODUCT = 'http://localhost/royal-liquor/admin/api/products.php'
export const API_ORDER = 'http://localhost/royal-liquor/admin/api/orders.php'
export const API_ADDRESS = 'http://localhost/royal-liquor/admin/api/addresses.php'
export const API_WAREHOUSE = 'http://localhost/royal-liquor/admin/api/warehouses.php'
export const API_ORDER_ITEM = "http://localhost/royal-liquor/admin/api/order-items.php"
export const API_USER_PREFERENCE  = "http://localhost/royal-liquor/admin/api/user-preferences.php"
export const API_FEEDBACK = "http://localhost/royal-liquor/admin/api/feedback.php"
export const API_USER_ADDRESS = "http://localhost/royal-liquor/admin/api/addresses.php"
export const API_FLAVOUR_PROFILE = "http://localhost/royal-liquor/admin/api/flavor-profile.php"
export const API_RECIPE_INGREDIENT = "http://localhost/royal-liquor/admin/api/recipe-ingredients.php"
export const API_STOCK = "http://localhost/royal-liquor/admin/api/stock.php"
export const API_PAYMENTS = "http://localhost/royal-liquor/admin/api/payments.php"
export const API_PRODUCT_RECOGNITION = "http://localhost/royal-liquor/admin/api/product-recognition.php"
export const API_COCKTAIL_RECIPES = "http://localhost/royal-liquor/admin/api/cocktail-recipes.php"
export const API_CART =  "http://localhost/royal-liquor/admin/api/cart.php"


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
        return json.data?.data ?? json.data ?? json  ?? {};
    } catch (error) {
        return { error: true, msg: err.message || 'Fetch failed' };
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