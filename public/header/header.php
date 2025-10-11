<?php
require_once __DIR__ . "/../session/session.php";
require_once __DIR__ . "/../config/constants.php";

$session = new Session();

$currentUser = [
    "user_id" => $session->get("user_id"),
    "username" => $session->get("username"),
    "email" => $session->get("email"),
    "is_admin" => $session->get("is_admin"),
    "logged_in" => $session->get("logged_in"),
    "login_time" => $session->get("login_time")
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/popup.css">
    <link rel="stylesheet" href="<?= BASE_URL?>css/footer.css">
  </head>
<body>
  
  <div class="modal">
    <div class="closeBtn-container">
        <div class="closeBtn">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
          </svg>

        </div>
      </div>
  </div>

  <div class="container">

  <header>

  <div id="menu">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
      <rect y="4" width="24" height="2"/>
      <rect y="11" width="24" height="2"/>
      <rect y="18" width="24" height="2"/>
    </svg>
  </div>

  <div id="title">
      <h2>Royal Liquor</h2>
  </div>
  <div id="right-panel">
    <div id="search">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8"/>
        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
    </div>

    <div id="profile">
     
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="8" r="4"/>
        <path d="M6 20c0-3.33 5.33-5 6-5s6 1.67 6 5"/>
      </svg>
    </div>
  </div>
  </header>

   <div class="profile-expand">

        <div class="profile-close">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" height="36px" width="36px">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
          </svg>

      
      </div>
    
      </div>

  <section class="search-bar">
    <form action="#" id="search-form">
    <input type="text" placeholder="search" id="searchInput">
     </form>
     <div class="close-searchBtn">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
          </svg>




  </section>

  </div>
  <div class="cookie-modal-bg">
    <div class="cookie-modal">
    
      The cookies on this website are used by us and third parties for different purposes. By clicking "Accept All", you consent to the storing of all cookies on your device. To opt out of certain cookies or to manage your preferences at any time, click on "Manage Cookies" or for more information, visit our Privacy Policy.
    <div class="actions">
      <button class="cookie-reject">Reject cookies</button>
      <button>Accept cookies</button>
    </div>

    </div>

  </div>
  
<script src="<?=BASE_URL?>header/header.js" ></script>
    
