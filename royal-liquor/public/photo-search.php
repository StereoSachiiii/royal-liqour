<?php
$pageName = 'photo-search';
$pageTitle = 'Photo Search - Royal Liquor';
require_once __DIR__ . "/components/header.php";
?>

<main class="photo-search-page">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Photo Search</h1>
            <p class="page-subtitle">Upload a photo of a bottle to find it in our catalog</p>
        </div>

        <!-- Upload Section -->
        <div class="upload-section">
            <div class="upload-card" id="uploadCard">
                <div class="upload-dropzone" id="dropzone">
                    <div class="dropzone-content">
                        <div class="dropzone-icon">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                        </div>
                        <h3>Drop your image here</h3>
                        <p>or click to browse</p>
                        <p class="dropzone-hint">Supports JPG, PNG • Max 10MB</p>
                    </div>
                    <input type="file" id="fileInput" accept="image/*" hidden>
                </div>

                <!-- Preview -->
                <div class="upload-preview" id="uploadPreview" style="display: none;">
                    <img id="previewImage" src="" alt="Uploaded photo">
                    <button class="btn-remove-image" id="removeImage">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <!-- Analyze Button -->
                <button class="btn btn-gold btn-lg btn-analyze" id="analyzeBtn" disabled>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    Analyze Image
                </button>
            </div>

            <!-- How It Works -->
            <div class="how-it-works">
                <h3>How It Works</h3>
                <div class="steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-text">Upload a photo of any liquor bottle</div>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-text">Our AI analyzes the label and packaging</div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-text">Get matching products from our catalog</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="results-section" id="resultsSection" style="display: none;">
            <div class="results-header">
                <h2>Recognition Results</h2>
                <button class="btn btn-outline btn-sm" id="newSearchBtn">New Search</button>
            </div>

            <!-- Analyzing State -->
            <div class="analyzing-state" id="analyzingState">
                <div class="analyzing-animation">
                    <div class="scan-line"></div>
                    <img id="analyzingImage" src="" alt="">
                </div>
                <h3>Analyzing image...</h3>
                <p>Looking for matching products</p>
            </div>

            <!-- Results Content -->
            <div class="results-content" id="resultsContent" style="display: none;">
                <!-- Detected Info -->
                <div class="detected-info" id="detectedInfo">
                    <h3>Detected</h3>
                    <div class="detected-labels" id="detectedLabels"></div>
                </div>

                <!-- Matched Products -->
                <div class="matched-products">
                    <h3>Matching Products</h3>
                    <div class="matches-grid" id="matchesGrid"></div>
                </div>

                <!-- No Matches State -->
                <div class="no-matches" id="noMatches" style="display: none;">
                    <div class="no-matches-icon">🔍</div>
                    <h3>No exact matches found</h3>
                    <p>We couldn't find this product in our catalog. Try these alternatives:</p>
                    <a href="<?= getPageUrl('shop') ?>" class="btn btn-gold">Browse All Products</a>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.photo-search-page {
    padding: var(--space-2xl) 0 var(--space-3xl);
    min-height: calc(100vh - 200px);
    background: linear-gradient(180deg, var(--gray-50) 0%, var(--white) 100%);
}

.page-header {
    text-align: center;
    margin-bottom: var(--space-2xl);
}

.page-title {
    font-family: var(--font-serif);
    font-size: 3rem;
    font-weight: 300;
    font-style: italic;
    margin-bottom: var(--space-sm);
}

.page-title::after {
    content: '';
    display: block;
    width: 80px;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--gold), transparent);
    margin: var(--space-md) auto 0;
}

.page-subtitle {
    color: var(--gray-500);
    font-size: 1.1rem;
}

/* Upload Section */
.upload-section {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: var(--space-2xl);
    max-width: 900px;
    margin: 0 auto;
}

.upload-card {
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: var(--space-xl);
    box-shadow: var(--shadow-lg);
}

/* Dropzone */
.upload-dropzone {
    border: 2px dashed var(--gray-300);
    border-radius: var(--radius-lg);
    padding: var(--space-3xl);
    text-align: center;
    cursor: pointer;
    transition: all var(--duration-fast);
}

.upload-dropzone:hover,
.upload-dropzone.drag-over {
    border-color: var(--gold);
    background: rgba(212, 175, 55, 0.05);
}

.dropzone-icon {
    color: var(--gray-400);
    margin-bottom: var(--space-lg);
}

.upload-dropzone:hover .dropzone-icon {
    color: var(--gold);
}

.dropzone-content h3 {
    font-size: 1.25rem;
    margin-bottom: var(--space-xs);
}

.dropzone-content p {
    color: var(--gray-500);
}

.dropzone-hint {
    font-size: 0.85rem;
    margin-top: var(--space-md);
}

/* Preview */
.upload-preview {
    position: relative;
    border-radius: var(--radius-lg);
    overflow: hidden;
    aspect-ratio: 4/3;
    background: var(--gray-100);
}

.upload-preview img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.btn-remove-image {
    position: absolute;
    top: var(--space-md);
    right: var(--space-md);
    width: 36px;
    height: 36px;
    background: rgba(0,0,0,0.6);
    border: none;
    border-radius: 50%;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background var(--duration-fast);
}

.btn-remove-image:hover {
    background: var(--error);
}

/* Analyze Button */
.btn-analyze {
    width: 100%;
    margin-top: var(--space-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-sm);
}

.btn-analyze:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* How It Works */
.how-it-works {
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: var(--space-xl);
    box-shadow: var(--shadow-sm);
}

.how-it-works h3 {
    font-family: var(--font-serif);
    font-style: italic;
    margin-bottom: var(--space-lg);
}

.steps {
    display: flex;
    flex-direction: column;
    gap: var(--space-lg);
}

.step {
    display: flex;
    gap: var(--space-md);
    align-items: flex-start;
}

.step-number {
    width: 28px;
    height: 28px;
    background: var(--gold);
    color: var(--black);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.step-text {
    color: var(--gray-600);
    line-height: 1.5;
    padding-top: 2px;
}

/* Results Section */
.results-section {
    max-width: 900px;
    margin: var(--space-3xl) auto 0;
}

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-xl);
}

.results-header h2 {
    font-family: var(--font-serif);
    font-size: 1.75rem;
    font-style: italic;
}

/* Analyzing State */
.analyzing-state {
    text-align: center;
    padding: var(--space-3xl);
    background: var(--white);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
}

.analyzing-animation {
    position: relative;
    width: 200px;
    height: 200px;
    margin: 0 auto var(--space-xl);
    border-radius: var(--radius-lg);
    overflow: hidden;
    background: var(--gray-100);
}

.analyzing-animation img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.scan-line {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, transparent, var(--gold), transparent);
    animation: scan 1.5s ease-in-out infinite;
}

@keyframes scan {
    0%, 100% { top: 0; }
    50% { top: calc(100% - 4px); }
}

.analyzing-state h3 {
    margin-bottom: var(--space-sm);
}

.analyzing-state p {
    color: var(--gray-500);
}

/* Results Content */
.results-content {
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: var(--space-xl);
    box-shadow: var(--shadow-md);
}

.detected-info {
    margin-bottom: var(--space-xl);
    padding-bottom: var(--space-xl);
    border-bottom: 1px solid var(--gray-100);
}

.detected-info h3 {
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--gray-500);
    margin-bottom: var(--space-md);
}

.detected-labels {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-sm);
}

.detected-label {
    padding: var(--space-xs) var(--space-md);
    background: rgba(212, 175, 55, 0.1);
    border: 1px solid rgba(212, 175, 55, 0.3);
    border-radius: var(--radius-full);
    font-size: 0.9rem;
    color: var(--gold);
    font-weight: 500;
}

.matched-products h3 {
    font-family: var(--font-serif);
    font-style: italic;
    margin-bottom: var(--space-lg);
}

.matches-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-lg);
}

.match-card {
    background: var(--gray-50);
    border-radius: var(--radius-lg);
    overflow: hidden;
    text-decoration: none;
    transition: all var(--duration-fast);
}

.match-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
}

.match-card-image {
    aspect-ratio: 1/1;
    overflow: hidden;
    position: relative;
}

.match-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.confidence-badge {
    position: absolute;
    top: var(--space-sm);
    right: var(--space-sm);
    padding: var(--space-xs) var(--space-sm);
    font-size: 0.75rem;
    font-weight: 700;
    border-radius: var(--radius-sm);
}

.confidence-badge.high {
    background: #22c55e;
    color: #fff;
}

.confidence-badge.medium {
    background: var(--gold);
    color: var(--black);
}

.confidence-badge.low {
    background: var(--gray-400);
    color: #fff;
}

.match-card-info {
    padding: var(--space-md);
}

.match-card-name {
    font-weight: 600;
    color: var(--black);
    margin-bottom: var(--space-xs);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.match-card-price {
    color: var(--gold);
    font-weight: 700;
}

/* No Matches */
.no-matches {
    text-align: center;
    padding: var(--space-3xl);
}

.no-matches-icon {
    font-size: 4rem;
    margin-bottom: var(--space-lg);
}

.no-matches p {
    color: var(--gray-500);
    margin: var(--space-md) 0 var(--space-xl);
}

/* Responsive */
@media (max-width: 768px) {
    .page-title {
        font-size: 2rem;
    }
    
    .upload-section {
        grid-template-columns: 1fr;
    }
    
    .matches-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<script type="module">
import { API } from '<?= BASE_URL ?>utils/api-helper.js';
import { updateCartCount } from '<?= BASE_URL ?>utils/header.js';

let uploadedImage = null;
let allProducts = [];

// DOM Elements
const dropzone = document.getElementById('dropzone');
const fileInput = document.getElementById('fileInput');
const uploadPreview = document.getElementById('uploadPreview');
const previewImage = document.getElementById('previewImage');
const analyzeBtn = document.getElementById('analyzeBtn');
const resultsSection = document.getElementById('resultsSection');
const analyzingState = document.getElementById('analyzingState');
const resultsContent = document.getElementById('resultsContent');

// Initialize
const init = async () => {
    await updateCartCount();
    await loadProducts();
    setupEventListeners();
};

// Load products for matching
const loadProducts = async () => {
    try {
        const response = await API.products.list({ limit: 100 });
        if (response.success && response.data) {
            allProducts = response.data;
        }
    } catch (error) {
        console.error('[PhotoSearch] Failed to load products:', error);
    }
};

// Handle file selection
const handleFile = (file) => {
    if (!file || !file.type.startsWith('image/')) {
        alert('Please select an image file');
        return;
    }
    
    if (file.size > 10 * 1024 * 1024) {
        alert('File too large. Maximum size is 10MB');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = (e) => {
        uploadedImage = e.target.result;
        previewImage.src = uploadedImage;
        dropzone.style.display = 'none';
        uploadPreview.style.display = 'block';
        analyzeBtn.disabled = false;
    };
    reader.readAsDataURL(file);
};

// Mock AI recognition (simulates API call)
const analyzeImage = async () => {
    // Show results section with analyzing state
    resultsSection.style.display = 'block';
    analyzingState.style.display = 'block';
    resultsContent.style.display = 'none';
    document.getElementById('analyzingImage').src = uploadedImage;
    
    // Scroll to results
    resultsSection.scrollIntoView({ behavior: 'smooth' });
    
    // Simulate AI processing delay
    await new Promise(resolve => setTimeout(resolve, 2500));
    
    // Mock recognition result - simulate detecting labels from image
    // In real implementation, this would call Google Vision API or similar
    const mockLabels = generateMockLabels();
    const matchedProducts = findMatchingProducts(mockLabels);
    
    // Show results
    analyzingState.style.display = 'none';
    resultsContent.style.display = 'block';
    
    renderResults(mockLabels, matchedProducts);
};

// Generate mock labels based on random product
const generateMockLabels = () => {
    // For demo, randomly pick a product and use its data as "detected" labels
    const randomProduct = allProducts[Math.floor(Math.random() * allProducts.length)];
    
    const labels = [];
    if (randomProduct) {
        // Add category
        if (randomProduct.category_name) labels.push(randomProduct.category_name);
        
        // Add some words from name
        const nameWords = randomProduct.name.split(' ').filter(w => w.length > 3);
        labels.push(...nameWords.slice(0, 2));
        
        // Add some flavor tags
        try {
            const flavor = typeof randomProduct.flavor_profile === 'string'
                ? JSON.parse(randomProduct.flavor_profile)
                : randomProduct.flavor_profile;
            if (flavor && flavor.tags) {
                labels.push(...flavor.tags.slice(0, 2));
            }
        } catch {}
        
        // Add generic labels
        labels.push('Bottle', 'Premium Spirit', 'Glass');
    }
    
    return [...new Set(labels)]; // Remove duplicates
};

// Find products matching detected labels
const findMatchingProducts = (labels) => {
    if (!labels.length) return [];
    
    const labelLower = labels.map(l => l.toLowerCase());
    
    return allProducts
        .map(product => {
            let score = 0;
            const searchText = `${product.name} ${product.category_name} ${product.description}`.toLowerCase();
            
            // Calculate match score based on label matches
            for (const label of labelLower) {
                if (searchText.includes(label)) {
                    score += 20;
                }
            }
            
            // Check flavor tags too
            try {
                const flavor = typeof product.flavor_profile === 'string'
                    ? JSON.parse(product.flavor_profile)
                    : product.flavor_profile;
                if (flavor && flavor.tags) {
                    for (const tag of flavor.tags) {
                        if (labelLower.includes(tag.toLowerCase())) {
                            score += 15;
                        }
                    }
                }
            } catch {}
            
            // Cap at 95 (never 100% for simulated results)
            return { ...product, confidence: Math.min(95, score) };
        })
        .filter(p => p.confidence > 0)
        .sort((a, b) => b.confidence - a.confidence)
        .slice(0, 6);
};

// Render results
const renderResults = (labels, products) => {
    // Render detected labels
    const detectedLabels = document.getElementById('detectedLabels');
    detectedLabels.innerHTML = labels.map(label => 
        `<span class="detected-label">${label}</span>`
    ).join('');
    
    // Render matched products
    const matchesGrid = document.getElementById('matchesGrid');
    const noMatches = document.getElementById('noMatches');
    
    if (products.length === 0) {
        matchesGrid.style.display = 'none';
        noMatches.style.display = 'block';
        return;
    }
    
    matchesGrid.style.display = 'grid';
    noMatches.style.display = 'none';
    
    matchesGrid.innerHTML = products.map(p => {
        const price = (p.price_cents / 100).toFixed(2);
        const confClass = p.confidence >= 70 ? 'high' : (p.confidence >= 40 ? 'medium' : 'low');
        return `
            <a href="product.php?id=${p.id}" class="match-card">
                <div class="match-card-image">
                    <img src="${p.image_url}" alt="${p.name}">
                    <span class="confidence-badge ${confClass}">${p.confidence}% match</span>
                </div>
                <div class="match-card-info">
                    <div class="match-card-name">${p.name}</div>
                    <div class="match-card-price">$${price}</div>
                </div>
            </a>
        `;
    }).join('');
};

// Reset to new search
const resetSearch = () => {
    uploadedImage = null;
    previewImage.src = '';
    fileInput.value = '';
    dropzone.style.display = 'block';
    uploadPreview.style.display = 'none';
    analyzeBtn.disabled = true;
    resultsSection.style.display = 'none';
};

// Setup event listeners
const setupEventListeners = () => {
    // Click to upload
    dropzone.addEventListener('click', () => fileInput.click());
    
    // File input change
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFile(e.target.files[0]);
        }
    });
    
    // Drag and drop
    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('drag-over');
    });
    
    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('drag-over');
    });
    
    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('drag-over');
        if (e.dataTransfer.files.length > 0) {
            handleFile(e.dataTransfer.files[0]);
        }
    });
    
    // Remove image
    document.getElementById('removeImage').addEventListener('click', resetSearch);
    
    // Analyze button
    analyzeBtn.addEventListener('click', analyzeImage);
    
    // New search button
    document.getElementById('newSearchBtn').addEventListener('click', () => {
        resetSearch();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
};

document.addEventListener('DOMContentLoaded', init);
</script>

<?php require_once __DIR__ . "/footer/footer.php"; ?>
