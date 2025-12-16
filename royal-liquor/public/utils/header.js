import { getCartItemCount } from "./cart-storage.js";


export const updateCartCount = async () => {
    const cartCount = document.querySelector('.count');
    const cartCountHeader = document.querySelector('.count-display');
    const count = getCartItemCount();

    if (cartCount) cartCount.textContent = count || '';
    if (cartCountHeader) cartCountHeader.textContent = count || '';
};


document.addEventListener("DOMContentLoaded", async () => {
    const menuBtn = document.getElementById('menu');
    const mobileSidebar = document.getElementById('mobileSidebar');
    const mobileClose = document.getElementById('mobileClose');

    const profileBtn = document.getElementById('profile');
    const profileSidebar = document.getElementById('profileSidebar');
    const profileClose = document.getElementById('profileClose');

    const sidebarOverlay = document.getElementById('sidebarOverlay');

    const searchBtn = document.getElementById('searchBtn');
    const searchWrapper = document.querySelector('.search-wrapper');
    const searchInput = document.getElementById('searchInput');
    const searchCloseBtn = document.getElementById('searchCloseBtn');

    const cartIcon = document.getElementById('cart');
    const cartExpand = document.querySelector('.cart-expand');

    const cookieModalBg = document.getElementById('cookieModalBg');
    const cookieAccept = document.getElementById('cookieAccept');
    const cookieReject = document.getElementById('cookieReject');

    await updateCartCount();


    const openMobileSidebar = () => {
        mobileSidebar.classList.add('active');
        sidebarOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    const closeMobileSidebar = () => {
        mobileSidebar.classList.remove('active');
        if (!profileSidebar.classList.contains('active')) {
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    if (menuBtn) {
        menuBtn.addEventListener('click', openMobileSidebar);
    }

    if (mobileClose) {
        mobileClose.addEventListener('click', closeMobileSidebar);
    }


    const openProfileSidebar = () => {
        profileSidebar.classList.add('active');
        sidebarOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    const closeProfileSidebar = () => {
        profileSidebar.classList.remove('active');
        if (!mobileSidebar.classList.contains('active')) {
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    if (profileBtn) {
        profileBtn.addEventListener('click', openProfileSidebar);
    }

    if (profileClose) {
        profileClose.addEventListener('click', closeProfileSidebar);
    }


    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', () => {
            closeMobileSidebar();
            closeProfileSidebar();
        });
    }


    const expandSearch = () => {
        searchWrapper.classList.add('expanded');
        setTimeout(() => searchInput.focus(), 100);
    };

    const collapseSearch = () => {
        searchWrapper.classList.remove('expanded');
        searchInput.value = '';
        searchInput.blur();
    };

    if (searchBtn) {
        searchBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (searchWrapper.classList.contains('expanded')) {
                if (searchInput.value.trim()) {
                    console.log('Search for:', searchInput.value);
                } else {
                    collapseSearch();
                }
            } else {
                expandSearch();
            }
        });
    }

    if (searchCloseBtn) {
        searchCloseBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            collapseSearch();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && searchInput.value.trim()) {
                console.log('Search for:', searchInput.value);
            }
        });
    }

    document.addEventListener('click', (e) => {
        if (searchWrapper.classList.contains('expanded')) {
            const isClickInside = searchWrapper.contains(e.target);
            if (!isClickInside) {
                collapseSearch();
            }
        }
    });

    if (cartIcon && cartExpand) {
        cartIcon.addEventListener('click', (e) => {
            if (e.target.closest('svg') || e.target.closest('.count-display')) {
                e.preventDefault();
                e.stopPropagation();
                cartExpand.classList.toggle('active');
            }
        });

        document.addEventListener('click', (e) => {
            const isClickInside = cartIcon.contains(e.target) || cartExpand.contains(e.target);
            if (cartExpand.classList.contains('active') && !isClickInside) {
                cartExpand.classList.remove('active');
            }
        });
    }


    const cookieConsent = localStorage.getItem('cookieConsent');

    if (!cookieConsent && cookieModalBg) {
        setTimeout(() => {
            cookieModalBg.classList.add('active');
        }, 1000);
    }

    if (cookieAccept) {
        cookieAccept.addEventListener('click', () => {
            localStorage.setItem('cookieConsent', 'accepted');
            cookieModalBg.classList.remove('active');
        });
    }

    if (cookieReject) {
        cookieReject.addEventListener('click', () => {
            localStorage.setItem('cookieConsent', 'rejected');
            cookieModalBg.classList.remove('active');
        });
    }


    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMobileSidebar();
            closeProfileSidebar();
            collapseSearch();
            if (cartExpand) cartExpand.classList.remove('active');
        }
    });
});