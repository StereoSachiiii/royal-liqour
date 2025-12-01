export const fetchOrders = async (id) => {
    try {
        const response = await fetch(`http://localhost/royal-liquor/admin/api/orders.php?user_id=${id}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw Error(`Error fetching orders ${response.statusText}`);
        }
        const body = await response.json();
        console.log(body);
        return body.data;
    } catch (error) {
        return { error: error };
    }
};