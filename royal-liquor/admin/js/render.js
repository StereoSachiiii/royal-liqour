// Track active page handlers for cleanup
let activeHandlers = [];

export const render = async (path, mainElement) => {
    const segments = (path || '').split('/').filter(Boolean);
    const page = segments.pop() || null;

    if (!mainElement) {
        return;
    }

    // Clean up previous page handlers
    activeHandlers.forEach(handler => {
        if (handler && typeof handler.cleanup === 'function') {
            handler.cleanup();
        }
    });
    activeHandlers = [];

    const requestedPage = page || 'overview';
    console.log('Rendering page:', requestedPage);

    try {
        switch (requestedPage) {
            case 'overview': {
                const { Overview } = await import('./pages/overview/Overview.js');
                mainElement.innerHTML = await Overview();
                break;
            }
            case 'products': {
                const { Products, productsListeners } = await import('./pages/Products/Products.js');
                mainElement.innerHTML = await Products();
                if (typeof productsListeners === 'function') {
                    const handler = await productsListeners(mainElement);
                    if (handler) activeHandlers.push(handler);
                }
                break;
            }
            case 'users': {
                const { Users, usersListeners } = await import('./pages/Users/Users.js');
                mainElement.innerHTML = await Users();
                if (typeof usersListeners === 'function') {
                    const handler = await usersListeners(mainElement);
                    if (handler) activeHandlers.push(handler);
                }
                break;
            }
            case 'orders': {
                const { Orders, ordersListeners } = await import('./pages/Orders/Orders.js');
                mainElement.innerHTML = await Orders();
                if (typeof ordersListeners === 'function') {
                    const handler = await ordersListeners(mainElement);
                    if (handler) activeHandlers.push(handler);
                }
                break;
            }
            case 'stock': {
                const { Stock, stockListeners } = await import('./pages/Stock/Stocks.js');
                mainElement.innerHTML = await Stock();
                if (typeof stockListeners === 'function') {
                    const handler = await stockListeners(mainElement);
                    if (handler) activeHandlers.push(handler);
                }
                break;
            }
            case 'categories': {
                const { Categories, categoriesListeners } = await import('./pages/Categories/Categories.js');
                mainElement.innerHTML = await Categories();
                if (typeof categoriesListeners === 'function') {
                    const handler = await categoriesListeners(mainElement);
                    if (handler) activeHandlers.push(handler);
                }
                break;
            }
            case 'suppliers': {
                const { Suppliers, suppliersListeners } = await import('./pages/Suppliers/Suppliers.js');
                mainElement.innerHTML = await Suppliers();
                if (typeof suppliersListeners === 'function') {
                    const handler = await suppliersListeners(mainElement);
                    if (handler) activeHandlers.push(handler);
                }
                break;
            }
            case 'warehouses': {
                const { Warehouses, warehousesListeners } = await import('./pages/Warehouses/Warehouses.js');
                mainElement.innerHTML = await Warehouses();
                if (typeof warehousesListeners === 'function') {
                    const handler = await warehousesListeners(mainElement);
                    if (handler) activeHandlers.push(handler);
                }
                break;
            }
            case 'payments': {
                const { Payments, paymentsListeners } = await import('./pages/Payments/Payments.js');
                mainElement.innerHTML = await Payments();
                if (typeof paymentsListeners === 'function') {
                    const handler = await paymentsListeners(mainElement);
                    if (handler) activeHandlers.push(handler);
                }
                break;
            }
            case 'product-recognition': {
                const { ProductRecognition, productRecognitionListeners } = await import('./pages/ProductRecognition/ProductRecognition.js');
                mainElement.innerHTML = await ProductRecognition();
                if (typeof productRecognitionListeners === 'function') {
                    const handler = await productRecognitionListeners(mainElement);
                    if (handler) activeHandlers.push(handler);
                }
                break;
            }
            case 'user-preferences': {
                const { UserPreferences, userPreferencesListeners } = await import('./pages/UserPreferences/UserPreferences.js');
                mainElement.innerHTML = await UserPreferences();
                if (typeof userPreferencesListeners === 'function') {
                    const handler = await userPreferencesListeners(mainElement);
                    if (handler) activeHandlers.push(handler);
                }
                break;
            }
            case 'user-addresses': {
                const { UserAddresses, userAddressesListeners } = await import('./pages/UserAddresses/UserAddresses.js');
                mainElement.innerHTML = await UserAddresses();
                if (typeof userAddressesListeners === 'function') {
                    const handler = await userAddressesListeners(mainElement);
                    if (handler) activeHandlers.push(handler);
                }
                break;
            }
            case 'cart-items': {
                const { CartItems, cartItemsListeners } = await import('./pages/CartItems/CartItems.js');
                mainElement.innerHTML = await CartItems();
                if (typeof cartItemsListeners === 'function') {
                    const handler = await cartItemsListeners(mainElement);
                    if (handler) activeHandlers.push(handler);
                }
                break;
            }
            case 'carts': {
                const { Carts, cartsListeners } = await import('./pages/Carts/Carts.js');
                mainElement.innerHTML = await Carts();
                if (typeof cartsListeners === 'function') {
                    const handler = await cartsListeners(mainElement);
                    if (handler) activeHandlers.push(handler);
                }
                break;
            }
            case 'flavour-profiles': {
                const { FlavourProfiles, flavourProfilesListener } = await import('./pages/FlavourProfiles/FlavourProfiles.js');
                mainElement.innerHTML = await FlavourProfiles();
                if (typeof flavourProfilesListener === 'function') {
                    const handler = await flavourProfilesListener(mainElement);
                    if (handler) activeHandlers.push(handler);
                }
                break;
            }
            case 'feedback': {
                const { Feedback, feedbackListeners } = await import('./pages/Feedback/Feedback.js');
                mainElement.innerHTML = await Feedback();
                if (typeof feedbackListeners === 'function') {
                    const handler = await feedbackListeners(mainElement);
                    if (handler) activeHandlers.push(handler);
                }
                break;
            }
            case 'order-items': {
                const { OrderItems, orderItemsListeners } = await import('./pages/OrderItems/OrderItems.js');
                mainElement.innerHTML = await OrderItems();
                if (typeof orderItemsListeners === 'function') {
                    const handler = await orderItemsListeners(mainElement);
                    if (handler) activeHandlers.push(handler);
                }
                break;
            }
            case 'recipe-ingredients': {
                const { RecipeIngredients, recipeIngredientsListeners } = await import('./pages/RecipeIngredients/RecipeIngredients.js');
                mainElement.innerHTML = await RecipeIngredients();
                if (typeof recipeIngredientsListeners === 'function') {
                    const handler = await recipeIngredientsListeners(mainElement);
                    if (handler) activeHandlers.push(handler);
                }
                break;
            }
            case 'cocktail-recipes': {
                const { CocktailRecipes, cocktailRecipesListeners } = await import('./pages/CocktailRecipes/CocktailRecipes.js');
                mainElement.innerHTML = await CocktailRecipes();
                if (typeof cocktailRecipesListeners === 'function') {
                    const handler = await cocktailRecipesListeners(mainElement);
                    if (handler) activeHandlers.push(handler);
                }
                break;
            }
            default: {
                const { Overview } = await import('./pages/overview/Overview.js');
                mainElement.innerHTML = await Overview();
                break;
            }
        }
    } catch (error) {
        console.error('Error rendering page', requestedPage, error);
        mainElement.innerHTML = `
            <div class="dashboard-error">
                <strong>Failed to load ${requestedPage || 'overview'}.</strong>
                <div>${error && error.message ? error.message : 'Check console for details.'}</div>
            </div>
        `;
    }
};

// Handle browser back/forward navigation
window.addEventListener('popstate', async () => {
    const mainElement = document.querySelector('#content');
    await render(window.location.hash.replace('#', '') || 'overview', mainElement);
});

document.addEventListener('DOMContentLoaded', () => {

    const modal = document.getElementById('modal');
    const modalBody = document.getElementById('modal-body');
    const modalClose = document.getElementById('modal-close');

    if (!modal || !modalClose || !modalBody) {
        console.warn('Modal elements not found');
        return;
    }

    // Modal close handlers
    const closeModalFn = () => {
        modal.classList.remove('active');
        modal.classList.add('hidden');
        modal.style.display = 'none';
        modalBody.innerHTML = ''; // Clear content
    };

    modalClose.addEventListener('click', closeModalFn);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModalFn();
    });

})