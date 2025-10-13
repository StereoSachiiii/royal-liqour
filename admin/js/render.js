
import { Overview } from './pages/Overview.js';
import { Users } from './pages/Users.js';
import { Products } from './pages/Products.js';

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
