import { getCartCount } from "../utils/cart.js";
const cartCount = document.querySelector('.count'); 
const cartCountHeader = document.querySelector('.count-display');

export const updateCartCount = async () => {
    cartCount.innerHTML  = `${await getCartCount() ?? ''}`;
    cartCountHeader.innerHTML = `${await getCartCount() ?? ''}`;
}
document.addEventListener("DOMContentLoaded", async () => {
    const menuBtn = document.getElementById('menu');
    const modal = document.querySelector(".modal");
    const closeBtn = document.querySelector(".closeBtn");
    const search = document.querySelector("#search");
    const searchBar = document.querySelector(".search-bar");
    const closeSearchBar = document.querySelector(".close-searchBtn");
    const profile = document.querySelector('#profile');
    const profileModal = document.querySelector('.profile-expand');
    const profileCloseBtn = document.querySelector('.profile-close-btn'); 
    const cookieModalBg = document.querySelector('.cookie-modal-bg');
    const cookieModal = document.querySelector('.cookie-modal');
    const searchInput = document.querySelector('#searchInput'); 
    const cartIcon = document.getElementById('cart');
    const cartExpand = document.querySelector('.cart-expand');
    
    await updateCartCount()


    document.addEventListener('click', (event) => {
        const isClickInsideCart = cartIcon.contains(event.target) || cartExpand.contains(event.target);

        if (cartExpand.classList.contains('active') && !isClickInsideCart) {
            cartExpand.classList.remove('active');
        }
    });

    menuBtn.addEventListener("click", () => {
        modal.classList.toggle("modal-active");
    });

    closeBtn.addEventListener('click',()=>{
        modal.classList.toggle("modal-active");
    });

    search.addEventListener("click",()=>{
        searchBar.classList.toggle("search-bar-active");
        searchInput.focus();
    });

    closeSearchBar.addEventListener('click',()=>{
        searchBar.classList.toggle("search-bar-active");
    });

    profile.addEventListener('click', (event) => {
        event.stopPropagation(); 
        profileModal.classList.add("profile-expand-active");
    });

    profileCloseBtn.addEventListener('click', (event) => {
        event.stopPropagation();
        profileModal.classList.remove('profile-expand-active');
    });

    document.addEventListener('click', (event) => {
        const isClickInsideProfile = profile.contains(event.target) || profileModal.contains(event.target);

        if (profileModal.classList.contains('profile-expand-active') && !isClickInsideProfile) {
            profileModal.classList.remove('profile-expand-active');
        }
    });

    
    cookieModalBg.addEventListener('click',(e)=>{
        cookieModalBg.classList.toggle('cookie-modal-bg-active');
        cookieModal.classList.toggle('cookie-modal-active');
    });
    
    cookieModal.addEventListener('click', (e) => {
        e.stopPropagation();
    });
});