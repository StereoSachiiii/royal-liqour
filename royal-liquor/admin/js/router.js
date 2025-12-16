import { render } from "./render.js";
import { saveState, getState } from "./utils.js";

const menuElement = document.querySelector(".sidebar-menu");
const breadcrumbElement = document.querySelector("#breadcrumb");
const mainElement = document.querySelector("#content");

const pages = [
    { page: "overview", path: "overview" },
    { page: "Products", path: "products" },
    { page: "Orders", path: "orders" },
    { page: "Users", path: "users" },
    { page: "Stock", path: "stock" },
    { page: "Categories", path: "categories" },
    { page: "Suppliers", path: "suppliers" },
    { page: "Warehouses", path: "warehouses" },
    { page: 'Order Items', path: 'order-items' },
    { page: 'User Preferences', path: 'user-preferences' },
    { page: 'User Addresses', path: 'user-addresses' },
    { page: 'Feedback', path: 'feedback' },
    { page: 'Carts', path: 'carts' },
    { page: 'Cart Items', path: 'cart-items' },
    { page: 'Flavour Profiles', path: 'flavour-profiles' },
    { page: 'Recipe Ingredients', path: 'recipe-ingredients' },
    {page: 'Cocktail Recipes', path: 'cocktail-recipes' },
    {page: 'suppliers', path: 'suppliers' },
    {page: 'Payments' , path: 'payments'},{
        page: 'Product Recognition', path:'product-recognition'
    }
];

let currentPage = getState('admin:lastPage', 'overview');

// Navigation function
const navigate = (pagePath) => {
    if (!pagePath) return;
    if (currentPage !== pagePath) {
        currentPage = pagePath;
        saveState('admin:lastPage', currentPage);
        updateBreadcrumb(currentPage);
        updateActiveLink(currentPage);
        render(currentPage, mainElement);
        history.pushState({}, '', `#${currentPage}`);
    }
}

// Update breadcrumb
const updateBreadcrumb = (pagePath) => {
    const page = pages.find(p => p.path === pagePath);
    if (page && breadcrumbElement) {
        breadcrumbElement.innerHTML = `
            <span class="breadcrumb-home">Admin</span>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-home">dashboard</span>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">${page.page}</span>
        `;
    }
}

// Update active sidebar link
const updateActiveLink = (pagePath) => {
    document.querySelectorAll('.sidebar-menu a').forEach(a => {
        a.classList.remove('active');
    });
    
    const activeLink = document.querySelector(`a[data-page="${pagePath}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }
}

// Generate menu
const menuItems = pages.map((page) => {
    return `<li><a href="#" data-page="${page.path}">${page.page}</a></li>`;
}).join("");

menuElement.innerHTML = menuItems;
updateActiveLink(currentPage);
updateBreadcrumb(currentPage);
render(currentPage, mainElement);

// Handle clicks
menuElement.addEventListener('click', (event) => {
    event.preventDefault();
    
    let link = event.target.closest("a");
    if (link) {
        const pagePath = link.getAttribute('data-page');
        navigate(pagePath);
    }
});