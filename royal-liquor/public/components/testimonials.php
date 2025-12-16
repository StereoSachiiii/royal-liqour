<?php
/**
 * Testimonials Section Component
 * Rotating customer reviews carousel
 */
?>

<section class="testimonials-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">What Our Customers Say</h2>
            <p class="section-subtitle">Trusted by thousands of spirit enthusiasts</p>
        </div>
        
        <div class="testimonials-carousel" id="testimonialsCarousel">
            <div class="testimonials-track" id="testimonialsTrack">
                <div class="testimonial-card">
                    <div class="testimonial-rating">★★★★★</div>
                    <p class="testimonial-text">"The selection is incredible. Found a rare whisky I'd been searching for years. Fast delivery and beautifully packaged."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">JM</div>
                        <div class="author-info">
                            <span class="author-name">James Mitchell</span>
                            <span class="author-location">London, UK</span>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-rating">★★★★★</div>
                    <p class="testimonial-text">"Best online liquor store I've used. The customer service is exceptional and their recommendations are always spot on."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">SC</div>
                        <div class="author-info">
                            <span class="author-name">Sarah Chen</span>
                            <span class="author-location">New York, USA</span>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-rating">★★★★★</div>
                    <p class="testimonial-text">"Royal Liquor has become my go-to for all special occasions. Premium quality, great prices, and always reliable."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">RP</div>
                        <div class="author-info">
                            <span class="author-name">Rajesh Patel</span>
                            <span class="author-location">Dubai, UAE</span>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-rating">★★★★☆</div>
                    <p class="testimonial-text">"Impressed by the flavor profile details. It really helps choosing the perfect bottle. Will definitely order again!"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">MK</div>
                        <div class="author-info">
                            <span class="author-name">Maria Kowalski</span>
                            <span class="author-location">Berlin, Germany</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="carousel-controls">
                <button class="carousel-btn prev" id="testimonialPrev" aria-label="Previous">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6"/>
                    </svg>
                </button>
                <div class="carousel-dots" id="testimonialDots"></div>
                <button class="carousel-btn next" id="testimonialNext" aria-label="Next">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Trust Indicators -->
        <div class="trust-indicators">
            <div class="trust-item">
                <span class="trust-value">15K+</span>
                <span class="trust-label">Happy Customers</span>
            </div>
            <div class="trust-divider"></div>
            <div class="trust-item">
                <span class="trust-value">4.9</span>
                <span class="trust-label">Average Rating</span>
            </div>
            <div class="trust-divider"></div>
            <div class="trust-item">
                <span class="trust-value">500+</span>
                <span class="trust-label">Premium Brands</span>
            </div>
            <div class="trust-divider"></div>
            <div class="trust-item">
                <span class="trust-value">24/7</span>
                <span class="trust-label">Support</span>
            </div>
        </div>
    </div>
</section>

<style>
/* Testimonials Section */
.testimonials-section {
    padding: var(--space-3xl) 0;
    background: linear-gradient(180deg, var(--gray-50) 0%, var(--white) 100%);
}

.testimonials-section .section-header {
    text-align: center;
    margin-bottom: var(--space-2xl);
}

.testimonials-section .section-title {
    font-family: var(--font-serif);
    font-size: 2.5rem;
    font-weight: 300;
    font-style: italic;
    color: var(--black);
    margin-bottom: var(--space-sm);
}

.testimonials-section .section-subtitle {
    color: var(--gray-500);
    font-size: 1.1rem;
}

/* Carousel */
.testimonials-carousel {
    position: relative;
    max-width: 1200px;
    margin: 0 auto;
    overflow: hidden;
}

.testimonials-track {
    display: flex;
    gap: var(--space-lg);
    transition: transform 0.5s ease;
}

.testimonial-card {
    flex: 0 0 calc(33.333% - var(--space-lg));
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: var(--space-xl);
    box-shadow: var(--shadow-md);
    min-width: 300px;
}

.testimonial-rating {
    color: var(--gold);
    font-size: 1.25rem;
    margin-bottom: var(--space-md);
}

.testimonial-text {
    font-size: 1rem;
    line-height: 1.7;
    color: var(--gray-700);
    margin-bottom: var(--space-lg);
    font-style: italic;
}

.testimonial-author {
    display: flex;
    align-items: center;
    gap: var(--space-md);
}

.author-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    color: var(--black);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
}

.author-info {
    display: flex;
    flex-direction: column;
}

.author-name {
    font-weight: 600;
    color: var(--black);
}

.author-location {
    font-size: 0.85rem;
    color: var(--gray-500);
}

/* Carousel Controls */
.carousel-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-lg);
    margin-top: var(--space-xl);
}

.carousel-btn {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--white);
    border: 1px solid var(--gray-200);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all var(--duration-fast);
}

.carousel-btn:hover {
    background: var(--black);
    border-color: var(--black);
    color: var(--white);
}

.carousel-dots {
    display: flex;
    gap: var(--space-sm);
}

.carousel-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--gray-300);
    cursor: pointer;
    transition: all var(--duration-fast);
}

.carousel-dot.active {
    background: var(--gold);
    width: 30px;
    border-radius: 5px;
}

/* Trust Indicators */
.trust-indicators {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: var(--space-2xl);
    margin-top: var(--space-3xl);
    padding-top: var(--space-2xl);
    border-top: 1px solid var(--gray-200);
}

.trust-item {
    text-align: center;
}

.trust-value {
    display: block;
    font-family: var(--font-serif);
    font-size: 2rem;
    font-weight: 600;
    color: var(--gold);
}

.trust-label {
    font-size: 0.9rem;
    color: var(--gray-500);
}

.trust-divider {
    width: 1px;
    height: 40px;
    background: var(--gray-200);
}

/* Responsive */
@media (max-width: 1024px) {
    .testimonial-card {
        flex: 0 0 calc(50% - var(--space-lg));
    }
}

@media (max-width: 768px) {
    .testimonial-card {
        flex: 0 0 100%;
    }
    
    .trust-indicators {
        flex-wrap: wrap;
        gap: var(--space-xl);
    }
    
    .trust-divider {
        display: none;
    }
    
    .trust-item {
        flex: 0 0 45%;
    }
}
</style>

<script>
(function() {
    const track = document.getElementById('testimonialsTrack');
    const dotsContainer = document.getElementById('testimonialDots');
    const prevBtn = document.getElementById('testimonialPrev');
    const nextBtn = document.getElementById('testimonialNext');
    
    if (!track) return;
    
    const cards = track.querySelectorAll('.testimonial-card');
    let currentIndex = 0;
    let cardsPerView = 3;
    
    const updateCardsPerView = () => {
        if (window.innerWidth <= 768) cardsPerView = 1;
        else if (window.innerWidth <= 1024) cardsPerView = 2;
        else cardsPerView = 3;
    };
    
    const totalSlides = () => Math.ceil(cards.length / cardsPerView);
    
    const createDots = () => {
        dotsContainer.innerHTML = '';
        for (let i = 0; i < totalSlides(); i++) {
            const dot = document.createElement('span');
            dot.className = 'carousel-dot' + (i === currentIndex ? ' active' : '');
            dot.addEventListener('click', () => goToSlide(i));
            dotsContainer.appendChild(dot);
        }
    };
    
    const updateDots = () => {
        const dots = dotsContainer.querySelectorAll('.carousel-dot');
        dots.forEach((dot, i) => dot.classList.toggle('active', i === currentIndex));
    };
    
    const goToSlide = (index) => {
        currentIndex = Math.max(0, Math.min(index, totalSlides() - 1));
        const cardWidth = cards[0].offsetWidth + parseInt(getComputedStyle(track).gap);
        track.style.transform = `translateX(-${currentIndex * cardsPerView * cardWidth}px)`;
        updateDots();
    };
    
    prevBtn.addEventListener('click', () => goToSlide(currentIndex - 1));
    nextBtn.addEventListener('click', () => goToSlide(currentIndex + 1));
    
    window.addEventListener('resize', () => {
        updateCardsPerView();
        createDots();
        goToSlide(Math.min(currentIndex, totalSlides() - 1));
    });
    
    // Auto-advance
    setInterval(() => {
        if (currentIndex >= totalSlides() - 1) currentIndex = -1;
        goToSlide(currentIndex + 1);
    }, 6000);
    
    updateCardsPerView();
    createDots();
})();
</script>
