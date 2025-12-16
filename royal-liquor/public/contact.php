<?php 
$pageName = 'contact';
$pageTitle = 'Contact Us - Royal Liquor';
require_once __DIR__ . "/components/header.php"; 
?>

<main class="contact-page">
    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <h1 class="contact-title">Get in Touch</h1>
            <p class="contact-tagline">We'd love to hear from you. Our team is here to help.</p>
        </div>
    </section>

    <!-- Contact Content -->
    <section class="contact-content container">
        <div class="contact-grid">
            <!-- Contact Form -->
            <div class="contact-form-wrapper card">
                <h2 class="section-heading">Send a Message</h2>
                <form id="contactForm" class="contact-form">
                    <div class="input-group">
                        <label class="label" for="contactName">Full Name *</label>
                        <input type="text" id="contactName" name="name" class="input" required placeholder="John Doe">
                    </div>
                    
                    <div class="input-group">
                        <label class="label" for="contactEmail">Email Address *</label>
                        <input type="email" id="contactEmail" name="email" class="input" required placeholder="john@example.com">
                    </div>
                    
                    <div class="input-group">
                        <label class="label" for="contactPhone">Phone Number</label>
                        <input type="tel" id="contactPhone" name="phone" class="input" placeholder="+94 77 123 4567" data-validate="phone">
                    </div>
                    
                    <div class="input-group">
                        <label class="label" for="contactSubject">Subject *</label>
                        <select id="contactSubject" name="subject" class="input" required>
                            <option value="">Select a topic</option>
                            <option value="general">General Inquiry</option>
                            <option value="order">Order Support</option>
                            <option value="product">Product Question</option>
                            <option value="corporate">Corporate Orders</option>
                            <option value="partnership">Partnership</option>
                            <option value="feedback">Feedback</option>
                        </select>
                    </div>
                    
                    <div class="input-group">
                        <label class="label" for="contactMessage">Message *</label>
                        <textarea id="contactMessage" name="message" class="input" rows="5" required placeholder="How can we help you?"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-gold btn-full" id="contactSubmit">
                        Send Message
                    </button>
                    
                    <p class="form-note text-muted text-sm mt-md text-center">
                        We typically respond within 24 hours.
                    </p>
                </form>
            </div>

            <!-- Contact Info -->
            <div class="contact-info">
                <!-- Address Card -->
                <div class="info-card card">
                    <div class="info-icon">📍</div>
                    <h3>Visit Us</h3>
                    <p>
                        Royal Liquor<br>
                        123 Galle Road<br>
                        Colombo 03, Sri Lanka
                    </p>
                    <a href="https://maps.google.com" target="_blank" class="info-link">Get Directions →</a>
                </div>

                <!-- Phone Card -->
                <div class="info-card card">
                    <div class="info-icon">📞</div>
                    <h3>Call Us</h3>
                    <p>
                        <strong>Sales:</strong> +94 11 234 5678<br>
                        <strong>Support:</strong> +94 11 234 5679
                    </p>
                    <p class="text-muted text-sm">Mon-Sat: 10AM - 8PM</p>
                </div>

                <!-- Email Card -->
                <div class="info-card card">
                    <div class="info-icon">✉️</div>
                    <h3>Email Us</h3>
                    <p>
                        <strong>General:</strong> info@royalliquor.lk<br>
                        <strong>Orders:</strong> orders@royalliquor.lk
                    </p>
                    <a href="mailto:info@royalliquor.lk" class="info-link">Send Email →</a>
                </div>

                <!-- Social Card -->
                <div class="info-card card">
                    <div class="info-icon">🌐</div>
                    <h3>Follow Us</h3>
                    <div class="social-links">
                        <a href="#" class="social-link" aria-label="Facebook">
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="social-link" aria-label="Instagram">
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                            </svg>
                        </a>
                        <a href="#" class="social-link" aria-label="Twitter">
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="contact-map">
        <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.798467128636!2d79.84871987499715!3d6.914744018383573!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae2596a04578f77%3A0x39e524f3c56a9f6a!2sGalle%20Rd%2C%20Colombo%2C%20Sri%20Lanka!5e0!3m2!1sen!2s!4v1702432000000!5m2!1sen!2s"
            width="100%" 
            height="100%" 
            style="border:0;" 
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade"
            title="Royal Liquor Location">
        </iframe>
    </section>

    <!-- FAQ Preview -->
    <section class="contact-faq container">
        <h2 class="section-heading text-center">Frequently Asked Questions</h2>
        <div class="divider-gold"></div>
        <div class="faq-grid">
            <div class="faq-item card">
                <h3>What are your delivery areas?</h3>
                <p>We deliver island-wide across Sri Lanka. Colombo orders are typically delivered within 24 hours.</p>
            </div>
            <div class="faq-item card">
                <h3>Do you offer corporate orders?</h3>
                <p>Yes! We offer special pricing for bulk and corporate orders. Contact us for a custom quote.</p>
            </div>
            <div class="faq-item card">
                <h3>What payment methods do you accept?</h3>
                <p>We accept all major credit/debit cards, bank transfers, and cash on delivery for eligible orders.</p>
            </div>
            <div class="faq-item card">
                <h3>Can I return a product?</h3>
                <p>Unopened products can be returned within 7 days. Please see our returns policy for details.</p>
            </div>
        </div>
        <div class="text-center mt-xl">
            <a href="<?= BASE_URL ?>faq.php" class="btn btn-outline">View All FAQs</a>
        </div>
    </section>
</main>

<style>
/* Contact Page Styles */
.contact-page {
    background: var(--white);
}

.contact-hero {
    background: linear-gradient(135deg, var(--black) 0%, var(--gray-800) 100%);
    color: var(--white);
    padding: 120px 0 80px;
    text-align: center;
}

.contact-title {
    font-family: var(--font-serif);
    font-size: 3.5rem;
    font-weight: 300;
    font-style: italic;
    margin: 0 0 var(--space-md);
}

.contact-tagline {
    color: var(--gray-400);
    font-size: 1.125rem;
}

.contact-content {
    padding: var(--space-3xl) 0;
}

.contact-grid {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: var(--space-3xl);
}

.contact-form-wrapper {
    padding: var(--space-2xl);
}

.section-heading {
    font-family: var(--font-serif);
    font-size: 1.75rem;
    margin-bottom: var(--space-xl);
}

.contact-form .input-group {
    margin-bottom: var(--space-lg);
}

.contact-form textarea.input {
    resize: vertical;
    min-height: 120px;
}

.form-note {
    margin-top: var(--space-md);
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: var(--space-lg);
}

.info-card {
    padding: var(--space-xl);
}

.info-icon {
    font-size: 2rem;
    margin-bottom: var(--space-sm);
}

.info-card h3 {
    font-size: 1.125rem;
    margin-bottom: var(--space-sm);
}

.info-card p {
    color: var(--gray-600);
    margin-bottom: var(--space-sm);
    line-height: 1.6;
}

.info-link {
    color: var(--gold);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
}

.info-link:hover {
    text-decoration: underline;
}

.social-links {
    display: flex;
    gap: var(--space-md);
}

.social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    background: var(--gray-100);
    border-radius: var(--radius-full);
    color: var(--gray-600);
    transition: all var(--duration-fast) var(--ease-out);
}

.social-link:hover {
    background: var(--gold);
    color: var(--black);
}

.contact-map {
    height: 400px;
    background: var(--gray-100);
}

.map-placeholder {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gray-400);
    font-size: 1.25rem;
}

.contact-faq {
    padding: var(--space-3xl) 0;
}

.faq-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-lg);
    margin-top: var(--space-2xl);
}

.faq-item {
    padding: var(--space-lg);
}

.faq-item h3 {
    font-size: 1rem;
    margin-bottom: var(--space-sm);
}

.faq-item p {
    color: var(--gray-600);
    font-size: 0.95rem;
    line-height: 1.6;
}

@media (max-width: 1024px) {
    .contact-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .contact-title { font-size: 2.5rem; }
    .faq-grid { grid-template-columns: 1fr; }
}
</style>

<script type="module">
import { toast } from '<?= BASE_URL ?>utils/toast.js';

const contactForm = document.getElementById('contactForm');
const submitBtn = document.getElementById('contactSubmit');

// Phone validation helper
const validatePhone = (phone) => {
    if (!phone) return true; // Optional field
    const phoneRegex = /^[+]?[(]?[0-9]{1,4}[)]?[-\s./0-9]*$/;
    return phoneRegex.test(phone) && phone.replace(/\D/g, '').length >= 9;
};

// Show error on field
const showError = (field, message) => {
    field.classList.add('has-error');
    let errorEl = field.parentElement.querySelector('.error-message');
    if (!errorEl) {
        errorEl = document.createElement('span');
        errorEl.className = 'error-message';
        field.parentElement.appendChild(errorEl);
    }
    errorEl.textContent = message;
};

// Clear all errors
const clearErrors = () => {
    contactForm.querySelectorAll('.has-error').forEach(el => {
        el.classList.remove('has-error');
    });
    contactForm.querySelectorAll('.error-message').forEach(el => {
        el.remove();
    });
};

// Add error styling
const style = document.createElement('style');
style.textContent = `
    .input.has-error { border-color: var(--error); }
    .error-message { color: var(--error); font-size: 0.85rem; margin-top: 4px; display: block; }
    .form-success { text-align: center; padding: var(--space-3xl); }
    .form-success .success-icon { font-size: 4rem; margin-bottom: var(--space-lg); }
    .form-success h3 { font-family: var(--font-serif); font-size: 1.75rem; font-style: italic; margin-bottom: var(--space-md); }
    .form-success p { color: var(--gray-600); margin-bottom: var(--space-xl); }
`;
document.head.appendChild(style);

// Handle form submission
contactForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearErrors();
    
    let hasErrors = false;
    const formData = {
        name: document.getElementById('contactName').value.trim(),
        email: document.getElementById('contactEmail').value.trim(),
        phone: document.getElementById('contactPhone').value.trim(),
        subject: document.getElementById('contactSubject').value,
        message: document.getElementById('contactMessage').value.trim()
    };
    
    // Validate required fields
    if (!formData.name) {
        showError(document.getElementById('contactName'), 'Name is required');
        hasErrors = true;
    }
    
    if (!formData.email) {
        showError(document.getElementById('contactEmail'), 'Email is required');
        hasErrors = true;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
        showError(document.getElementById('contactEmail'), 'Please enter a valid email');
        hasErrors = true;
    }
    
    if (formData.phone && !validatePhone(formData.phone)) {
        showError(document.getElementById('contactPhone'), 'Please enter a valid phone number');
        hasErrors = true;
    }
    
    if (!formData.subject) {
        showError(document.getElementById('contactSubject'), 'Please select a subject');
        hasErrors = true;
    }
    
    if (!formData.message) {
        showError(document.getElementById('contactMessage'), 'Message is required');
        hasErrors = true;
    } else if (formData.message.length < 10) {
        showError(document.getElementById('contactMessage'), 'Message is too short');
        hasErrors = true;
    }
    
    if (hasErrors) {
        toast.error('Please fix the errors and try again');
        return;
    }
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';
    
    // Simulate API call (replace with real API when available)
    try {
        await new Promise(resolve => setTimeout(resolve, 1500));
        
        // Save to localStorage for demo (would normally go to backend)
        const messages = JSON.parse(localStorage.getItem('contactMessages') || '[]');
        messages.push({
            ...formData,
            id: Date.now(),
            createdAt: new Date().toISOString(),
            status: 'pending'
        });
        localStorage.setItem('contactMessages', JSON.stringify(messages));
        
        // Show success
        const wrapper = document.querySelector('.contact-form-wrapper');
        wrapper.innerHTML = `
            <div class="form-success">
                <div class="success-icon">✓</div>
                <h3>Message Sent!</h3>
                <p>Thank you for reaching out, ${formData.name}. We'll get back to you within 24 hours.</p>
                <button class="btn btn-gold" onclick="location.reload()">Send Another Message</button>
            </div>
        `;
        
        toast.success('Your message has been sent successfully!');
        
    } catch (error) {
        console.error('[Contact] Submit error:', error);
        toast.error('Failed to send message. Please try again.');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Message';
    }
});

console.log('[Contact] Contact page ready');
</script>

<?php require_once __DIR__ . "/footer/footer.php"; ?>
