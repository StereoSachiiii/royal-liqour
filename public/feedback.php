<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Reviews</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            background: linear-gradient(135deg, #fafafa 0%, #ffffff 100%);
            color: #000000;
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        header {
            text-align: center;
            margin-bottom: 60px;
            padding: 40px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        }

        h1 {
            font-size: 3.5rem;
            font-weight: 300;
            letter-spacing: 8px;
            margin-bottom: 15px;
            color: #000000;
            text-transform: uppercase;
        }

        .tagline {
            font-style: italic;
            font-size: 1.1rem;
            color: #000000;
            letter-spacing: 2px;
        }

        .controls {
            display: flex;
            gap: 20px;
            margin-bottom: 50px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 500px;
        }

        input[type="text"] {
            width: 100%;
            padding: 16px 24px;
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.15);
            color: #000000;
            font-size: 1rem;
            font-family: inherit;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: rgba(0, 0, 0, 0.4);
            background: #fafafa;
        }

        input[type="text"]::placeholder {
            color: #999;
            font-style: italic;
        }

        select {
            padding: 16px 24px;
            padding-right: 50px;
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.15);
            color: #000000;
            font-size: 1rem;
            cursor: pointer;
            font-family: inherit;
            letter-spacing: 1px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23000000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 20px;
            position: relative;
        }

        select:hover {
            background: #fafafa;
            border-color: rgba(0, 0, 0, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        select:focus {
            outline: none;
            border-color: rgba(0, 0, 0, 0.5);
            background: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }

        select:active {
            transform: translateY(0);
        }

        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 35px;
            margin-bottom: 60px;
        }

        .review-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.08);
            padding: 35px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .review-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(0,0,0,0.3), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .review-card:hover::before {
            transform: translateX(100%);
        }

        .review-card:hover {
            background: #fafafa;
            border-color: rgba(0, 0, 0, 0.2);
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .product-name {
            font-size: 1.6rem;
            font-weight: 400;
            margin-bottom: 12px;
            letter-spacing: 2px;
            color: #000000;
        }

        .category {
            font-style: italic;
            color: #000000;
            font-size: 0.9rem;
            margin-bottom: 20px;
            letter-spacing: 1px;
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .stars {
            color: #000000;
            font-size: 1.2rem;
            letter-spacing: 3px;
        }

        .rating-number {
            color: #000000;
            font-size: 1rem;
        }

        .excerpt {
            font-style: italic;
            color: #000000;
            line-height: 1.8;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            padding-top: 20px;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            font-size: 0.85rem;
            color: #000000;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            overflow-y: auto;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            max-width: 1200px;
            margin: 80px auto;
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.15);
            padding: 60px;
            position: relative;
            animation: slideUp 0.4s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .close {
            position: absolute;
            top: 30px;
            right: 40px;
            font-size: 2.5rem;
            cursor: pointer;
            color: #999;
            transition: color 0.3s;
            font-weight: 100;
        }

        .close:hover {
            color: #000000;
        }

        .modal-header {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .modal-title {
            font-size: 2.5rem;
            font-weight: 300;
            margin-bottom: 15px;
            letter-spacing: 3px;
            color: #000000;
        }

        .modal-category {
            font-style: italic;
            color: #666;
            font-size: 1rem;
            letter-spacing: 1px;
        }

        .modal-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin: 40px 0;
            padding: 40px 0;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .stat {
            text-align: center;
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 300;
            margin-bottom: 8px;
            color: #000000;
        }

        .stat-label {
            font-style: italic;
            color: #666;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }

        .comments-section {
            margin-top: 50px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 300;
            margin-bottom: 30px;
            letter-spacing: 2px;
            color: #000000;
        }

        .comment {
            padding: 25px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        .comment:last-child {
            border-bottom: none;
        }

        .comment-author {
            font-weight: 500;
            margin-bottom: 10px;
            color: #000000;
            letter-spacing: 1px;
        }

        .comment-text {
            font-style: italic;
            color: #444;
            line-height: 1.8;
        }

        .comment-rating {
            margin: 10px 0;
        }

        .comment-date {
            font-size: 0.8rem;
            color: #666;
        }

        .verified {
            color: green;
            font-size: 0.85rem;
            margin-left: 10px;
        }

        .no-results {
            text-align: center;
            padding: 80px 20px;
            font-style: italic;
            color: #000000;
            font-size: 1.2rem;
        }

        .fab {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #000;
            color: #fff;
            font-size: 30px;
            border: none;
            cursor: pointer;
            z-index: 999;
        }

        .add-modal-content {
            max-width: 600px;
            margin: 80px auto;
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.15);
            padding: 60px;
            position: relative;
            animation: slideUp 0.4s ease;
        }

        .success-modal-content {
            max-width: 400px;
            margin: 80px auto;
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.15);
            padding: 60px;
            position: relative;
            animation: slideUp 0.4s ease;
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        textarea {
            height: 100px;
            padding: 10px;
            border: 1px solid rgba(0, 0, 0, 0.15);
        }

        button {
            padding: 10px;
            background: #000;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        .highlight {
            background: #ffffcc !important;
            transition: background 3s;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>REVIEWS</h1>
            <p class="tagline">Curated insights on exceptional products</p>
        </header>

        <div class="controls">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search products...">
            </div>
            <select id="sortSelect">
                <option value="rating">Highest Rated</option>
                <option value="sold">Most Sold</option>
                <option value="name">Alphabetical</option>
                <option value="category">By Category</option>
            </select>
        </div>

        <div class="reviews-grid" id="reviewsGrid"></div>
    </div>

    <div class="modal" id="productModal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle"></h2>
                <p class="modal-category" id="modalCategory"></p>
            </div>
            <div class="rating" id="modalRating"></div>
            <div class="modal-stats" id="modalStats"></div>
            <div class="excerpt" id="modalExcerpt"></div>
            <div class="comments-section">
                <h3 class="section-title">Customer Reviews</h3>
                <div id="modalComments"></div>
            </div>
        </div>
    </div>

    <div class="modal" id="addFeedbackModal">
        <div class="add-modal-content">
            <span class="close add-close">&times;</span>
            <h2>Add Review</h2>
            <form id="addForm">
                <select id="productSelect"></select>
                <select id="ratingSelect">
                    <option value="1">1 Star</option>
                    <option value="2">2 Stars</option>
                    <option value="3">3 Stars</option>
                    <option value="4">4 Stars</option>
                    <option value="5">5 Stars</option>
                </select>
                <textarea id="commentInput" placeholder="Your comment..."></textarea>
                <button type="button" id="submitReview">Finish</button>
            </form>
        </div>
    </div>

    <div class="modal" id="successModal">
        <div class="success-modal-content">
            <span class="close success-close">&times;</span>
            <h2>Success</h2>
            <p>Your review has been created!</p>
            <button id="okButton">OK</button>
        </div>
    </div>

    <button id="addButton" class="fab">+</button>

    <script>
        let products = [];
        let currentProducts = [];
        const API_URL = 'http://localhost/royal-liquor/admin/api/feedback.php?details=true';

        // Fetch feedback data from API
        async function fetchFeedback() {
            try {
                const response = await fetch(API_URL);
                const result = await response.json();
                
                if (result.success && result.data) {
                    // Transform API data to our product format
                    const productMap = new Map();
                    
                    result.data.forEach(feedback => {
                        const pid = feedback.productId;
                        if (!productMap.has(pid)) {
                            productMap.set(pid, {
                                id: pid,
                                name: feedback.productName || `Product ${pid}`,
                                slug: feedback.productSlug || `product-${pid}`,
                                category: feedback.categoryName || 'Unknown',
                                sold: Number(feedback.unitsSold || 0),
                                excerpt: feedback.productDescription || '',
                                image: feedback.productImage || '',
                                priceCents: Number(feedback.priceCents || 0),
                                rating: Number(feedback.avgRating || 0),
                                totalReviews: Number(feedback.totalReviews || 0),
                                comments: []
                            });
                        }
                        
                        const product = productMap.get(pid);
                        
                        // Only add this comment if it's active
                        if (feedback.isActive) {
                            product.comments.push({
                                author: feedback.userName || `User ${feedback.userId}`,
                                email: feedback.userEmail || '',
                                text: feedback.comment,
                                rating: Number(feedback.rating),
                                verified: feedback.isVerifiedPurchase,
                                date: new Date(feedback.createdAt).toLocaleDateString('en-US', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric'
                                })
                            });
                        }
                        
                        // Use the product description or first comment as excerpt
                        if (!product.excerpt && feedback.comment) {
                            product.excerpt = feedback.comment;
                        }
                    });
                    
                    products = Array.from(productMap.values());
                    currentProducts = [...products];
                    renderProducts(currentProducts);
                } else {
                    console.error('Failed to fetch feedback:', result.message);
                    showError('Unable to load product reviews. Please try again later.');
                }
            } catch (error) {
                console.error('Error fetching feedback:', error);
                showError('Unable to connect to the server. Please check your connection.');
            }
        }

        function showError(message) {
            const grid = document.getElementById('reviewsGrid');
            grid.innerHTML = `<div class="no-results">${message}</div>`;
        }

        // Initialize on page load
        fetchFeedback();

        function renderProducts(productsToRender) {
            const grid = document.getElementById('reviewsGrid');
            
            if (productsToRender.length === 0) {
                grid.innerHTML = '<div class="no-results">No products match your search</div>';
                return;
            }

            grid.innerHTML = productsToRender.map((product, index) => `
                <div class="review-card" data-product-id="${product.id}" onclick="openModal(${index})">
                    <h3 class="product-name">${product.name}</h3>
                    <p class="category">${product.category}</p>
                    <div class="rating">
                        <span class="stars">${'★'.repeat(Math.floor(product.rating))}${'☆'.repeat(5 - Math.floor(product.rating))}</span>
                        <span class="rating-number">${product.rating.toFixed(1)}</span>
                    </div>
                    <p class="excerpt">${product.excerpt}</p>
                    <div class="meta">
                        <span>${product.sold.toLocaleString()} sold</span>
                        <span>${product.totalReviews} reviews</span>
                    </div>
                </div>
            `).join('');
        }

        function openModal(index) {
            const product = currentProducts[index];
            const modal = document.getElementById('productModal');
            
            document.getElementById('modalTitle').textContent = product.name;
            document.getElementById('modalCategory').textContent = product.category;
            document.getElementById('modalRating').innerHTML = `
                <span class="stars">${'★'.repeat(Math.floor(product.rating))}${'☆'.repeat(5 - Math.floor(product.rating))}</span>
                <span class="rating-number">${product.rating.toFixed(1)} / 5.0</span>
            `;
            document.getElementById('modalStats').innerHTML = `
                <div class="stat">
                    <div class="stat-value">${product.rating.toFixed(1)}</div>
                    <div class="stat-label">Average Rating</div>
                </div>
                <div class="stat">
                    <div class="stat-value">${product.sold.toLocaleString()}</div>
                    <div class="stat-label">Units Sold</div>
                </div>
                <div class="stat">
                    <div class="stat-value">${product.totalReviews}</div>
                    <div class="stat-label">Total Reviews</div>
                </div>
                <div class="stat">
                    <div class="stat-value">$${(product.priceCents / 100).toFixed(2)}</div>
                    <div class="stat-label">Price</div>
                </div>
            `;
            document.getElementById('modalExcerpt').textContent = product.excerpt;
            document.getElementById('modalComments').innerHTML = product.comments.map(comment => `
                <div class="comment">
                    <div class="comment-author">${comment.author}${comment.verified ? '<span class="verified">Verified Purchase</span>' : ''}</div>
                    <div class="comment-rating">
                        <span class="stars">${'★'.repeat(comment.rating)}${'☆'.repeat(5 - comment.rating)}</span> ${comment.rating}/5
                    </div>
                    <p class="comment-text">${comment.text}</p>
                    <div class="comment-date">${comment.date}</div>
                </div>
            `).join('');
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        document.querySelector('#productModal .close').onclick = function() {
            document.getElementById('productModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        };

        window.onclick = function(event) {
            const modals = [document.getElementById('productModal'), document.getElementById('addFeedbackModal'), document.getElementById('successModal')];
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        };

        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            currentProducts = products.filter(product => 
                product.name.toLowerCase().includes(searchTerm) ||
                product.category.toLowerCase().includes(searchTerm)
            );
            sortProducts(document.getElementById('sortSelect').value);
        });

        document.getElementById('sortSelect').addEventListener('change', function(e) {
            sortProducts(e.target.value);
        });

        function sortProducts(sortBy) {
            switch(sortBy) {
                case 'rating':
                    currentProducts.sort((a, b) => b.rating - a.rating);
                    break;
                case 'sold':
                    currentProducts.sort((a, b) => b.sold - a.sold);
                    break;
                case 'name':
                    currentProducts.sort((a, b) => a.name.localeCompare(b.name));
                    break;
                case 'category':
                    currentProducts.sort((a, b) => a.category.localeCompare(b.category));
                    break;
            }
            renderProducts(currentProducts);
        }

        // Add feedback functionality
        document.getElementById('addButton').onclick = function() {
            populateProductSelect();
            document.getElementById('addFeedbackModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        };

        function populateProductSelect() {
            const select = document.getElementById('productSelect');
            select.innerHTML = products.map(product => `<option value="${product.id}">${product.name} (${product.category})</option>`).join('');
        }

        document.getElementById('submitReview').onclick = async function() {
            const productId = document.getElementById('productSelect').value;
            const rating = document.getElementById('ratingSelect').value;
            const comment = document.getElementById('commentInput').value;

            if (!productId || !rating || !comment) {
                alert('Please fill all fields');
                return;
            }

            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        user_id: 1, // Assuming user ID 1 for demo
                        product_id: Number(productId),
                        rating: Number(rating),
                        comment: comment,
                        is_verified_purchase: true,
                        is_active: true
                    })
                });

                const result = await response.json();

                if (result.success) {
                    document.getElementById('addFeedbackModal').style.display = 'none';
                    document.getElementById('successModal').style.display = 'block';
                    const newProductId = result.data.productId; // Assuming the response has the productId
                    document.getElementById('okButton').onclick = function() {
                        document.getElementById('successModal').style.display = 'none';
                        document.body.style.overflow = 'auto';
                        fetchFeedback();
                        setTimeout(() => highlightProduct(newProductId), 500);
                    };
                } else {
                    alert('Failed to create review: ' + result.message);
                }
            } catch (error) {
                console.error('Error creating review:', error);
                alert('Error creating review');
            }
        };

        document.querySelector('#addFeedbackModal .add-close').onclick = function() {
            document.getElementById('addFeedbackModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        };

        document.querySelector('#successModal .success-close').onclick = function() {
            document.getElementById('successModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        };

        function highlightProduct(pid) {
            const card = document.querySelector(`.review-card[data-product-id="${pid}"]`);
            if (card) {
                card.classList.add('highlight');
                setTimeout(() => card.classList.remove('highlight'), 3000);
            }
        }
    </script>
</body>
</html>