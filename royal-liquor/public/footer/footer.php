<footer class="site-footer">
  <div class="footer-top">
    <div class="footer-contact">
      <h3>Where to find us</h3>
      <p>Royal Liquor<br>
      Kelaniya, Sri Lanka.</p>
      <p>+94 (76) 415 5690<br>
      <a href="mailto:kelaniya@gmail.com">kelaniya@gmail.com</a></p>
    </div>

    <div class="footer-social">
      <h3>Follow Us</h3>
      <ul class="social-links">
        <li><a href="#">Facebook</a></li>
        <li><a href="#">Instagram</a></li>
        <li><a href="#">Twitter</a></li>
      </ul>
    </div>

    <div class="footer-links">
      <div class="link-group">
        <h4>Spirits</h4>
        <ul>
          <li><a href="#">All Spirits</a></li>
          <li><a href="#">Whisky</a></li>
          <li><a href="#">Rum</a></li>
          <li><a href="#">Gin</a></li>
          <li><a href="#">Liqueur / Bitter</a></li>
          <li><a href="#">Arrack / Arrak</a></li>
          <li><a href="#">Vodka</a></li>
          <li><a href="#">Brandy / Cognac</a></li>
          <li><a href="#">Cognac</a></li>
          <li><a href="#">Tequila</a></li>
        </ul>
      </div>

      <div class="link-group">
        <h4>Wine & Champagne</h4>
        <ul>
          <li><a href="#">All Wines</a></li>
          <li><a href="#">Red Wine</a></li>
          <li><a href="#">White Wine</a></li>
          <li><a href="#">Rosé Wine</a></li>
          <li><a href="#">Champagne / Sparkling Wine</a></li>
          <li><a href="#">Dessert / Fortified Wine</a></li>
        </ul>
      </div>

      <div class="link-group">
        <h4>Beer & Sake</h4>
        <ul>
          <li><a href="#">Beer</a></li>
          <li><a href="#">Cider</a></li>
          <li><a href="#">Sake</a></li>
        </ul>
      </div>

      <div class="link-group">
        <h4>Company</h4>
        <ul>
          <li><a href="<?= getPageUrl('about') ?>">About Us</a></li>
          <li><a href="<?= getPageUrl('contact') ?>">Contact Us</a></li>
          <li><a href="<?= getPageUrl('faq') ?>">FAQ</a></li>
          <li><a href="#">Terms & Conditions</a></li>
          <li><a href="#">Privacy Policy</a></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <p>&copy; 2025 Royal. All rights reserved.</p>
    <p>Design and developed by Stereo (Pvt)</p>
  </div>
</footer>
<script>
// Slider only runs if slides exist (homepage only)
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.dot');

if (slides.length > 0 && dots.length > 0) {
    let currentSlide = 0;
    const totalSlides = slides.length;

    function showSlide(n) {
        slides[currentSlide].classList.remove('active');
        dots[currentSlide].classList.remove('active');
        
        currentSlide = (n + totalSlides) % totalSlides;
        
        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');
    }

    function nextSlide() {
        showSlide(currentSlide + 1);
    }

    // Auto advance slides every 5 seconds
    let slideInterval = setInterval(nextSlide, 5000);

    // Dot click handlers
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            clearInterval(slideInterval);
            showSlide(index);
            slideInterval = setInterval(nextSlide, 5000);
        });
    });
}
</script>