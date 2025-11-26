<style>.slider-container {
    margin-top: 20px;
    position: relative;
    width: 100%;
    height: 600px;
    overflow: hidden;
    background: #000;
}

.slider-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
}

.slide {
    position: absolute;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 1s ease-in-out;
}

.slide.active {
    opacity: 1;
}

.slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.slide-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.4) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.slide-content {
    text-align: center;
    color: white;
    padding: 0 20px;
    max-width: 800px;
    animation: fadeInUp 1s ease-out;
}

.slide-content h1 {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 20px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    line-height: 1.2;
}

.slide-content p {
    font-size: 1.3rem;
    margin-bottom: 30px;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
}

.slide-btn {
    display: inline-block;
    padding: 15px 40px;
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid white;
    color: white;
    text-decoration: none;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 50px;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.slide-btn:hover {
    background: white;
    color: #000;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(255, 255, 255, 0.3);
}

.slider-dots {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 15px;
    z-index: 10;
}

.dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.dot:hover {
    background: rgba(255, 255, 255, 0.8);
    transform: scale(1.2);
}

.dot.active {
    background: white;
    width: 40px;
    border-radius: 6px;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .slider-container {
        height: 500px;
    }
    
    .slide-content h1 {
        font-size: 2.5rem;
    }
    
    .slide-content p {
        font-size: 1.1rem;
    }
}</style>
<div class="slider-container">
    <div class="slider-wrapper">
        <!-- Slide 1 -->
        <div class="slide active">
            <img src="https://images.unsplash.com/photo-1558346489-19413928158b?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1170" alt="Modern Office">
            <div class="slide-overlay">
                <div class="slide-content">
                    <h1>Welcome to Royal Liquor</h1>
                    <p>Transforming the beverage industry</p>
                    <a href="#" class="slide-btn">Explore More</a>
                </div>
            </div>
        </div>

        <!-- Slide 2 -->
        <div class="slide">
            <img src="https://images.unsplash.com/photo-1504279577054-acfeccf8fc52?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1074" alt="Team Collaboration">
            <div class="slide-overlay">
                <div class="slide-content">
                    <h1>For enthusiasts</h1>
                    <p>Enjoy in your own time</p>
                    <a href="#" class="slide-btn">Learn More</a>
                </div>
            </div>
        </div>

        <!-- Slide 3 -->
        <div class="slide">
            <img src="https://images.unsplash.com/photo-1516594915697-87eb3b1c14ea?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1170" alt="Analytics Dashboard">
            <div class="slide-overlay">
                <div class="slide-content">
                    <h1>Expirience the best products from the finest of brewers</h1>
                    <p>swiss and dutch</p>
                    <a href="#" class="slide-btn">Get Started</a>
                </div>
            </div>
        </div>

        <!-- Slide 4 -->
  

    <!-- Navigation Dots -->
    <div class="slider-dots">
        <span class="dot active" data-slide="0"></span>
        <span class="dot" data-slide="1"></span>
        <span class="dot" data-slide="2"></span>
    </div>
</div>
</div> 
