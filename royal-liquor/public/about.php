<?php 
$pageName = 'about';
$pageTitle = 'About Us - Royal Liquor';
require_once __DIR__ . "/components/header.php"; 
?>

<main class="about-page">
    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container">
            <h1 class="about-title">Our Story</h1>
            <p class="about-tagline">Curating the world's finest spirits since 1987.</p>
        </div>
    </section>

    <!-- Story Section -->
    <section class="about-story container">
        <div class="story-grid">
            <div class="story-image">
                <img src="<?= BASE_URL ?>assets/images/about-cellar.jpg" alt="Wine Cellar" data-src="https://images.unsplash.com/photo-1516594915307-8f71b9a63f4b?w=800">
            </div>
            <div class="story-content">
                <h2 class="section-heading">A Legacy of Excellence</h2>
                <p>Founded in the heart of Colombo, Royal Liquor began as a humble wine shop with a singular vision: to bring the world's finest spirits to Sri Lanka's discerning palates.</p>
                <p>Over three decades later, we've grown into the country's premier destination for premium whiskeys, rare wines, artisan gins, and small-batch spirits from around the globe.</p>
                <p>Every bottle in our collection is hand-selected by our team of certified sommeliers and whiskey experts, ensuring only the exceptional makes it to your glass.</p>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="about-values">
        <div class="container">
            <h2 class="section-heading text-center">Our Values</h2>
            <div class="divider-gold"></div>
            <div class="values-grid">
                <div class="value-card card card-hover">
                    <div class="value-icon">🎯</div>
                    <h3>Quality First</h3>
                    <p>We never compromise on quality. Every product meets our rigorous standards for authenticity and excellence.</p>
                </div>
                <div class="value-card card card-hover">
                    <div class="value-icon">🤝</div>
                    <h3>Trust & Integrity</h3>
                    <p>We build lasting relationships through honesty, transparency, and a genuine commitment to our customers.</p>
                </div>
                <div class="value-card card card-hover">
                    <div class="value-icon">🌍</div>
                    <h3>Global Selection</h3>
                    <p>From Scottish highlands to Japanese distilleries, we source the finest spirits from every corner of the world.</p>
                </div>
                <div class="value-card card card-hover">
                    <div class="value-icon">📚</div>
                    <h3>Education</h3>
                    <p>We're passionate about sharing knowledge. Our team is always ready to guide you on your discovery journey.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="about-team container">
        <h2 class="section-heading text-center">Meet Our Experts</h2>
        <div class="divider-gold"></div>
        <div class="team-grid">
            <div class="team-card card">
                <div class="team-image skeleton skeleton-avatar" style="width:150px;height:150px;margin:0 auto;"></div>
                <h3>Rajitha Fernando</h3>
                <p class="team-role">Master Sommelier</p>
                <p class="team-bio">With 25 years of experience and certifications from the Court of Master Sommeliers, Rajitha leads our wine selection program.</p>
            </div>
            <div class="team-card card">
                <div class="team-image skeleton skeleton-avatar" style="width:150px;height:150px;margin:0 auto;"></div>
                <h3>Amal Jayawardena</h3>
                <p class="team-role">Whiskey Expert</p>
                <p class="team-bio">A certified Keeper of the Quaich, Amal has visited over 100 distilleries and curates our whiskey collection.</p>
            </div>
            <div class="team-card card">
                <div class="team-image skeleton skeleton-avatar" style="width:150px;height:150px;margin:0 auto;"></div>
                <h3>Priya Mendis</h3>
                <p class="team-role">Customer Experience</p>
                <p class="team-bio">Priya ensures every customer receives personalized recommendations and an exceptional shopping experience.</p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="about-cta">
        <div class="container text-center">
            <h2>Ready to Explore?</h2>
            <p>Discover our curated collection of premium spirits.</p>
            <a href="<?= getPageUrl('shop') ?>" class="btn btn-gold btn-lg">Shop Now</a>
        </div>
    </section>
</main>

<style>
/* About Page Styles - Uses utilities.css variables */
.about-page {
    background: var(--white);
}

.about-hero {
    background: linear-gradient(135deg, var(--black) 0%, var(--gray-800) 100%);
    color: var(--white);
    padding: 120px 0 80px;
    text-align: center;
}

.about-title {
    font-family: var(--font-serif);
    font-size: 4rem;
    font-weight: 300;
    font-style: italic;
    margin: 0 0 var(--space-md);
}

.about-tagline {
    font-size: 1.25rem;
    color: var(--gold);
    letter-spacing: 0.05em;
}

.about-story {
    padding: var(--space-3xl) 0;
}

.story-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-3xl);
    align-items: center;
}

.story-image img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: var(--radius-lg);
}

.section-heading {
    font-family: var(--font-serif);
    font-size: 2rem;
    margin-bottom: var(--space-lg);
}

.story-content p {
    color: var(--gray-600);
    line-height: 1.8;
    margin-bottom: var(--space-md);
}

.about-values {
    background: var(--gray-50);
    padding: var(--space-3xl) 0;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--space-xl);
    margin-top: var(--space-2xl);
}

.value-card {
    text-align: center;
    padding: var(--space-xl);
}

.value-icon {
    font-size: 3rem;
    margin-bottom: var(--space-md);
}

.value-card h3 {
    font-size: 1.25rem;
    margin-bottom: var(--space-sm);
}

.value-card p {
    color: var(--gray-600);
    font-size: 0.95rem;
}

.about-team {
    padding: var(--space-3xl) 0;
}

.team-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-xl);
    margin-top: var(--space-2xl);
}

.team-card {
    text-align: center;
    padding: var(--space-xl);
}

.team-card h3 {
    margin-top: var(--space-lg);
    margin-bottom: var(--space-xs);
}

.team-role {
    color: var(--gold);
    font-weight: 600;
    margin-bottom: var(--space-md);
}

.team-bio {
    color: var(--gray-600);
    font-size: 0.95rem;
}

.about-cta {
    background: linear-gradient(135deg, var(--black) 0%, var(--gray-800) 100%);
    color: var(--white);
    padding: var(--space-3xl) 0;
}

.about-cta h2 {
    font-family: var(--font-serif);
    font-size: 2.5rem;
    margin-bottom: var(--space-md);
}

.about-cta p {
    color: var(--gray-400);
    margin-bottom: var(--space-xl);
}

@media (max-width: 1024px) {
    .values-grid { grid-template-columns: repeat(2, 1fr); }
    .team-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
    .about-title { font-size: 2.5rem; }
    .story-grid { grid-template-columns: 1fr; }
    .values-grid { grid-template-columns: 1fr; }
    .team-grid { grid-template-columns: 1fr; }
}
</style>

<?php require_once __DIR__ . "/footer/footer.php"; ?>
