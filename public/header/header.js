document.addEventListener("DOMContentLoaded", () => {
    const menuBtn = document.getElementById('menu');
    const modal = document.querySelector(".modal");
    const closeBtn = document.querySelector(".closeBtn");
    const search = document.querySelector("#search");
    const searchBar = document.querySelector(".search-bar")
    const closeSearchBar = document.querySelector(".close-searchBtn");
    const profile = document.querySelector('#profile');
    const profileModal = document.querySelector('.profile-expand');
    const profileClose = document.querySelector('.profile-close');
    const cookieModalBg = document.querySelector('.cookie-modal-bg')
    const cookieModal = document.querySelector('.cookie-modal');
    const searchInput = document.querySelector('#searchInput');    
    menuBtn.addEventListener("click", () => {
        console.log("menu clicked!");
        modal.classList.toggle("modal-active");
    });

    closeBtn.addEventListener('click',()=>{
      console.log("close btn clicked");
      modal.classList.toggle("modal-active")
    })

    search.addEventListener("click",()=>{
        console.log("search");
        searchBar.classList.toggle("search-bar-active")
        searchInput.focus()
    })

    closeSearchBar.addEventListener('click',()=>{
        searchBar.classList.toggle("search-bar-active")
    })

    profile.addEventListener('click',()=>{
        console.log("hlo");
        profileModal.classList.toggle("profile-expand-active")

    })

    profileClose.addEventListener('click',()=>{
        console.log("clicked");
        profileModal.classList.toggle('profile-expand-active')
    })

    setTimeout(()=>{
        cookieModalBg.classList.toggle('cookie-modal-bg-active');
        cookieModal.classList.toggle('cookie-modal-active')

    },2000)

    cookieModalBg.addEventListener('click',(e)=>{

       
        cookieModalBg.classList.toggle('cookie-modal-bg-active')
        cookieModal.classList.toggle('cookie-modal-active')
    })
    
    cookieModal.addEventListener('click', (e) => {
  
  e.stopPropagation();
});



});