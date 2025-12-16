<?php
/**
 * Newsletter Section Component
 * Email signup with incentive
 */
?>

<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-content">
            <div class="newsletter-text">
                <span class="newsletter-badge">Exclusive</span>
                <h2 class="newsletter-title">Join Our Private Collection</h2>
                <p class="newsletter-description">
                    Subscribe for early access to limited editions, exclusive discounts, and expert tasting notes delivered straight to your inbox.
                </p>
                <ul class="newsletter-benefits">
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        10% off your first order
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Early access to new arrivals
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Members-only events
                    </li>
                </ul>
            </div>
            
            <div class="newsletter-form-wrapper">
                <form class="newsletter-form" id="newsletterForm">
                    <div class="form-group">
                        <input type="email" id="newsletterEmail" placeholder="Enter your email" required>
                        <button type="submit" class="btn-subscribe">
                            Subscribe
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                    </div>
                    <p class="form-note">No spam, ever. Unsubscribe anytime.</p>
                </form>
                
                <div class="newsletter-success" id="newsletterSuccess" style="display: none;">
                    <div class="success-icon">✓</div>
                    <h3>Welcome to the Club!</h3>
                    <p>Check your email for your exclusive 10% discount code.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Newsletter Section */
.newsletter-section {
    padding: var(--space-3xl) 0;
    background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
    color: var(--white);
}

.newsletter-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-3xl);
    align-items: center;
}

.newsletter-badge {
    display: inline-block;
    padding: var(--space-xs) var(--space-md);
    background: var(--gold);
    color: var(--black);
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    border-radius: var(--radius-sm);
    margin-bottom: var(--space-md);
}

.newsletter-title {
    font-family: var(--font-serif);
    font-size: 2.5rem;
    font-weight: 300;
    font-style: italic;
    margin-bottom: var(--space-md);
}

.newsletter-description {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.7);
    line-height: 1.7;
    margin-bottom: var(--space-xl);
}

.newsletter-benefits {
    list-style: none;
    padding: 0;
    margin: 0;
}

.newsletter-benefits li {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    margin-bottom: var(--space-sm);
    color: rgba(255, 255, 255, 0.9);
}

.newsletter-benefits svg {
    color: var(--gold);
    flex-shrink: 0;
}

/* Form */
.newsletter-form-wrapper {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-xl);
    padding: var(--space-2xl);
}

.newsletter-form .form-group {
    display: flex;
    gap: var(--space-sm);
}

.newsletter-form input {
    flex: 1;
    padding: var(--space-md) var(--space-lg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--radius-lg);
    background: rgba(255, 255, 255, 0.05);
    color: var(--white);
    font-size: 1rem;
}

.newsletter-form input::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

.newsletter-form input:focus {
    outline: none;
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.15);
}

.btn-subscribe {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    padding: var(--space-md) var(--space-xl);
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    color: var(--black);
    border: none;
    border-radius: var(--radius-lg);
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    transition: all var(--duration-fast);
    white-space: nowrap;
}

.btn-subscribe:hover {
    transform: translateX(4px);
    box-shadow: var(--shadow-lg);
}

.form-note {
    margin-top: var(--space-md);
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.5);
    text-align: center;
}

/* Success State */
.newsletter-success {
    text-align: center;
    padding: var(--space-xl);
}

.success-icon {
    width: 64px;
    height: 64px;
    background: var(--gold);
    color: var(--black);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin: 0 auto var(--space-lg);
    animation: scaleIn 0.3s ease;
}

@keyframes scaleIn {
    from { transform: scale(0); }
    to { transform: scale(1); }
}

.newsletter-success h3 {
    font-family: var(--font-serif);
    font-style: italic;
    font-size: 1.5rem;
    margin-bottom: var(--space-sm);
}

.newsletter-success p {
    color: rgba(255, 255, 255, 0.7);
}

/* Responsive */
@media (max-width: 1024px) {
    .newsletter-content {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .newsletter-benefits {
        display: inline-block;
        text-align: left;
    }
}

@media (max-width: 640px) {
    .newsletter-form .form-group {
        flex-direction: column;
    }
    
    .btn-subscribe {
        justify-content: center;
    }
}
</style>

<script>
document.getElementById('newsletterForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('newsletterEmail').value;
    console.log('Newsletter signup:', email);
    
    // Show success
    this.style.display = 'none';
    document.getElementById('newsletterSuccess').style.display = 'block';
    
    // Could call API here
    // api.post('newsletter', { email });
});
</script>
