import { fetchDashboard } from "./Overview.utils.js";
import { formatCurrency, formatNumber, formatPercent , escapeHtml } from "../../utils.js";

export const Overview = async () => {
    let stats = null;
    let error = null;

    try {
        const response = await fetchDashboard();
        
        if (response.error) {
            throw new Error(response.error);
        }
        stats = response;
    } catch (err) {
        console.error('Dashboard error:', err);
        error = err.message;
    }

    const topProductsHtml = stats?.products.top_products_by_revenue
        .map(p => `
            <div class="product-row">
                <span class="product-name">${escapeHtml(p.name)}</span>
                <span class="product-sold">${formatNumber(p.total_sold)} sold</span>
                <span class="product-revenue">${formatCurrency(p.revenue_cents)}</span>
            </div>
        `)
        .join('') || '';

    const lowPerfHtml = stats?.products.low_performing_products
        .map(p => `
            <div class="product-row warning">
                <span class="product-name">${escapeHtml(p.name)}</span>
                <span class="product-stock">${formatNumber(p.available_stock)} in stock</span>
            </div>
        `)
        .join('') || '';

    return `
        <div class="overview-page">
            <div class="overview-header">
                <h1 class="overview-title">Dashboard Overview</h1>
                <p class="overview-subtitle">Business metrics & actionable insights</p>
            </div>

            <div id="dashboard-loader" class="dashboard-loader ${stats ? 'hidden' : ''}">
                <div class="spinner"></div>
                <span>Loading dashboard stats...</span>
            </div>

            <div id="dashboard-error" class="dashboard-error ${error ? '' : 'hidden'}">
                ${error ? `Failed to load stats: ${escapeHtml(error)}` : ''}
            </div>

            <div id="dashboard-content" class="dashboard-content ${stats ? '' : 'hidden'}">

               
                <section class="dashboard-section">
                    <h2 class="dashboard-section-title">👥 Users</h2>
                    <div class="dashboard-grid dashboard-grid-4">
                        <div class="dashboard-card">
                            <span class="label">Total Users</span>
                            <span class="value">${stats ? formatNumber(stats.users.total) : '0'}</span>
                        </div>
                        <div class="dashboard-card">
                            <span class="label">Active Today</span>
                            <span class="value">${stats ? formatNumber(stats.users.active_today) : '0'}</span>
                        </div>
                        <div class="dashboard-card">
                            <span class="label">New (7 days)</span>
                            <span class="value">${stats ? formatNumber(stats.users.new_last_7_days) : '0'}</span>
                        </div>
                        <div class="dashboard-card alert-info">
                            <span class="label">Growth %</span>
                            <span class="value value-percent">${stats ? formatPercent(stats.users.weekly_user_growth_pct) : '0%'}</span>
                        </div>
                    </div>
                </section>

               
                <section class="dashboard-section">
                    <h2 class="dashboard-section-title">💰 Revenue</h2>
                    <div class="dashboard-grid dashboard-grid-3">
                        <div class="dashboard-card card-highlight-green">
                            <span class="label">Total Revenue</span>
                            <span class="value value-currency">${stats ? formatCurrency(stats.revenue.total_cents) : 'Rs 0'}</span>
                        </div>
                        <div class="dashboard-card card-highlight-blue">
                            <span class="label">Last 30 Days</span>
                            <span class="value value-currency">${stats ? formatCurrency(stats.revenue.last_30_days_cents) : 'Rs 0'}</span>
                        </div>
                        <div class="dashboard-card card-highlight-white">
                            <span class="label">Today</span>
                            <span class="value value-currency">${stats ? formatCurrency(stats.revenue.today_cents) : 'Rs 0'}</span>
                        </div>
                    </div>
                </section>

                <section class="dashboard-section">
                    <h2 class="dashboard-section-title">📦 Orders & Fulfillment</h2>
                    <div class="dashboard-grid dashboard-grid-5">
                        <div class="dashboard-card">
                            <span class="label">Total Orders</span>
                            <span class="value">${stats ? formatNumber(stats.orders.total) : '0'}</span>
                        </div>
                        <div class="dashboard-card alert-warning">
                            <span class="label">Pending</span>
                            <span class="value">${stats ? formatNumber(stats.orders.pending) : '0'}</span>
                        </div>
                        <div class="dashboard-card">
                            <span class="label">Today</span>
                            <span class="value">${stats ? formatNumber(stats.orders.today) : '0'}</span>
                        </div>
                        <div class="dashboard-card">
                            <span class="label">Avg Order Value</span>
                            <span class="value value-currency">${stats ? formatCurrency(stats.orders.avg_order_value_cents) : 'Rs 0'}</span>
                        </div>
                        <div class="dashboard-card">
                            <span class="label">Avg Items/Order</span>
                            <span class="value">${stats ? formatNumber(stats.orders.avg_items_per_order) : '0'}</span>
                        </div>
                        <div class="dashboard-card">
                            <span class="label">Delivery Time</span>
                            <span class="value">${stats ? stats.orders.avg_time_to_deliver_hours + 'h' : '0h'}</span>
                        </div>
                        <div class="dashboard-card card-highlight-blue">
                            <span class="label">Conversion Rate</span>
                            <span class="value value-percent">${stats ? formatPercent(stats.orders.conversion_rate_pct) : '0%'}</span>
                        </div>
                        <div class="dashboard-card card-highlight-green">
                            <span class="label">Repeat Customers</span>
                            <span class="value value-percent">${stats ? formatPercent(stats.orders.repeat_customer_rate_pct) : '0%'}</span>
                        </div>
                    </div>
                </section>

                <section class="dashboard-section">
                    <h2 class="dashboard-section-title">🛍️ Products</h2>
                    <div class="dashboard-grid dashboard-grid-4">
                        <div class="dashboard-card">
                            <span class="label">Total Products</span>
                            <span class="value">${stats ? formatNumber(stats.products.total) : '0'}</span>
                        </div>
                        <div class="dashboard-card card-highlight-blue">
                            <span class="label">Active</span>
                            <span class="value">${stats ? formatNumber(stats.products.active) : '0'}</span>
                        </div>
                        <div class="dashboard-card alert-warning">
                            <span class="label">Low Stock</span>
                            <span class="value">${stats ? formatNumber(stats.products.low_stock_products) : '0'}</span>
                        </div>
                        <div class="dashboard-card alert-danger">
                            <span class="label">Out of Stock</span>
                            <span class="value">${stats ? formatNumber(stats.products.out_of_stock_products) : '0'}</span>
                        </div>
                    </div>
                </section>

                <section class="dashboard-section">
                    <h2 class="dashboard-section-title">📊 Inventory Health</h2>
                    <div class="dashboard-grid dashboard-grid-4">
                        <div class="dashboard-card">
                            <span class="label">Total Items</span>
                            <span class="value">${stats ? formatNumber(stats.products.inventory_total_items) : '0'}</span>
                        </div>
                        <div class="dashboard-card">
                            <span class="label">Reserved</span>
                            <span class="value">${stats ? formatNumber(stats.products.inventory_reserved) : '0'}</span>
                        </div>
                        <div class="dashboard-card card-highlight-green">
                            <span class="label">Available</span>
                            <span class="value">${stats ? formatNumber(stats.products.inventory_available) : '0'}</span>
                        </div>
                        <div class="dashboard-card card-highlight-green">
                            <span class="label">Inventory Value</span>
                            <span class="value value-currency">${stats ? formatCurrency(stats.products.inventory_value_cents) : 'Rs 0'}</span>
                        </div>
                    </div>
                </section>

                <section class="dashboard-section">
                    <h2 class="dashboard-section-title">⭐ Top Products (by Revenue)</h2>
                    <div class="dashboard-list">
                        <div class="products-list">${topProductsHtml}</div>
                    </div>
                </section>

                <section class="dashboard-section">
                    <h2 class="dashboard-section-title">⚠️ Low Performing Products (No Sales 90 days)</h2>
                    <div class="dashboard-list">
                        <div class="products-list">${lowPerfHtml}</div>
                    </div>
                </section>

            </div>
        </div>
    `;
};

