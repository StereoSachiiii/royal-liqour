
import { Overview } from './pages/overview/Overview.js';
import { Users } from './pages/Users/Users.js';
import { Products } from './pages/Products/Products.js';
import { Categories,categoriesListeners } from './pages/Categories/Categories.js';
import { Orders } from './pages/Orders/Orders.js';
import { Suppliers } from './pages/Suppliers/Suppliers.js';
import { Warehouses } from './pages/Warehouses/Warehouses.js';
import { Stock, stockListeners} from './pages/Stock/Stocks.js';
import { Payments, paymentsListeners } from './pages/Payments/Payments.js';
import { ProductRecognition, productRecognitionListeners } from './pages/ProductRecognition/ProductRecognition.js';
import { UserPreferences, userPreferencesListeners} from './pages/UserPreferences/UserPreferences.js';
import { RecipeIngredients, recipeIngredientsListeners} from './pages/RecipeIngredients/RecipeIngredients.js';
import { Carts, cartsListeners } from './pages/Carts/Carts.js';
import { Feedback,feedbackListeners } from './pages/Feedback/Feedback.js';
import { FlavourProfiles, flavourProfilesListener } from './pages/FlavourProfiles/FlavourProfiles.js';
import { CartItems, cartItemsListeners } from './pages/CartItems/CartItems.js';
import { OrderItems, orderItemsListeners } from './pages/OrderItems/OrderItems.js';
import { UserAddresses,userAddressesListeners } from './pages/UserAddresses/UserAddresses.js';
import { CocktailRecipes, cocktailRecipesListeners } from './pages/CocktailRecipes/CocktailRecipes.js';



const saveState = (path) => {
    localStorage.setItem('lastVisitedPath', JSON.stringify(path));
}

const getLastVisitedPath = () => {
    const path = localStorage.getItem('lastVisitedPath');
    return path ? JSON.parse(path) : null;
}

export const render = async (path, mainElement) => {
    const segments = path.split('/').filter(Boolean);
    const page = segments.pop() || null;   
    if (!mainElement) {
        return;
    }

    
    const requestedPage = page?? getLastVisitedPath() ;
    console.log('Rendering page:', requestedPage);

   
    if(requestedPage)saveState(requestedPage)

    switch (requestedPage) {
        case 'overview':
            mainElement.innerHTML = await Overview();
            break;
        case 'users':
            mainElement.innerHTML = await Users(); 
            break;
        case 'products':
            mainElement.innerHTML = await Products();
            break;
        case 'orders':
            mainElement.innerHTML = await Orders();
            break;
        case 'suppliers':
            mainElement.innerHTML = await Suppliers();
            break;
        case 'warehouses':
            mainElement.innerHTML = await Warehouses();
            break;    
        case 'stock':
            mainElement.innerHTML = await Stock();
            await stockListeners();
            break;
        case 'categories':      
            mainElement.innerHTML = await Categories();
            await categoriesListeners();
            break;
       
        case 'payments':
            mainElement.innerHTML = await Payments();
            await paymentsListeners();
            break;
        case 'user-preferences':
            mainElement.innerHTML = await UserPreferences();
            await userPreferencesListeners();
            break;
        case 'cart-items':
            mainElement.innerHTML = await CartItems();
            await cartItemsListeners()
            break;
        case 'flavour-profiles':
            mainElement.innerHTML = await FlavourProfiles();
            await flavourProfilesListener();
            break;
        case 'feedback':
            mainElement.innerHTML = await Feedback();
            await feedbackListeners();
            break;
        case 'carts':
            mainElement.innerHTML = await Carts();
            await cartsListeners();
            break;
        case 'order-items':
            mainElement.innerHTML = await OrderItems();
            await orderItemsListeners();
            break;  
        case 'cocktail-recipes':
            mainElement.innerHTML = await CocktailRecipes();
            await cocktailRecipesListeners();
            break;
        case 'product-recognition':
            mainElement.innerHTML = await ProductRecognition();
            await productRecognitionListeners();
            break;

        case 'recipe-ingredients':
            mainElement.innerHTML = await RecipeIngredients();
            await recipeIngredientsListeners();
            break;
        case 'user-addresses':
            mainElement.innerHTML = await UserAddresses();
            await userAddressesListeners();
            break;
        default:
            mainElement.innerHTML = await Overview(); // Default to overview
            break;
    }
};

// Handle browser back/forward navigation
window.addEventListener('popstate', async () => {
    const mainElement = document.querySelector('#content');
    await render(window.location.pathname, mainElement);
});


document.addEventListener('DOMContentLoaded',()=>{
    
    const modal = document.getElementById('modal');
    const modalBody = document.getElementById('modal-body');
    const modalClose = document.getElementById('modal-close');

    if (!modal || !modalClose || !modalBody) {
        console.warn('Modal elements not found');
        return;
    }

    // Modal close handlers
    modalClose.addEventListener('click', () => modal.classList.remove('active'));
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.remove('active');
    });

})