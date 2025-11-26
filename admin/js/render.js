
import { Overview } from './pages/Overview.js';
import { Users } from './pages/Users.js';
import { Products } from './pages/Products.js';
import { Categories } from './pages/Categories.js';


export const render = async (path, mainElement) => {
    if (!mainElement) {
        console.error('Main content element not found');
        return;
    }

    const requestedPage = path.split('/').pop() || 'overview';
    console.log('Rendering page:', requestedPage);

    switch (requestedPage) {
        case 'overview':
            mainElement.innerHTML = Overview();
            break;
        case 'users':
            mainElement.innerHTML = await Users(); 
            break;
            //new
        case 'products':
            mainElement.innerHTML = await Products();
            break;

        case 'categories':
            console.log("hlo");
            mainElement.innerHTML = await Categories();
            break;
        default:
            mainElement.innerHTML = Overview(); // Default to overview
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