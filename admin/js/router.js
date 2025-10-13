import { render } from "./render.js";


const menuElement = document.querySelector(".sidebar-menu");
const breadcrumbElement = document.querySelector("#breadcrumb"); // Add this to your HTML
const mainElement = document.querySelector("#content")
const pages = [
    { page: "overview", path: "overview" },
    { page: "Products", path: "products" },
    { page: "Orders", path: "orders" },
    { page: "Users", path: "users" },
    { page: "Stock", path: "stock" },
    { page: "Categories", path: "categories" },
    { page: "Suppliers", path: "suppliers" },
    { page: "Warehouses", path: "warehouses" },
    { page: "Reports", path: "reports" },
    { page: "Settings", path: "settings" }
];

// Current state - just one variable!
let currentPage = "Overview";

// Navigation function
const navigate = (pagePath) => {
    if (currentPage !== pagePath) {
        currentPage = pagePath;
        updateBreadcrumb(pagePath);
        render(pagePath, mainElement);
        updateActiveLink(pagePath);
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

// Handle clicks
menuElement.addEventListener('click', (event) => {
    event.preventDefault();
    
    let link = event.target.closest("a");
    
    if (link) {
        const pagePath = link.getAttribute('data-page');
        navigate(pagePath);
    }
});

// Initial render
navigate('Overview');