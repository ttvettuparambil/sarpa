// Slider Functionality
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('.slider-container');
    const cards = document.querySelectorAll('.profile-card');
    const prevBtn = document.querySelector('.prev');
    const nextBtn = document.querySelector('.next');
    
    let currentIndex = 0;
    const cardWidth = cards[0].offsetWidth + 20; // width + gap
    
    function updateSlider() {
        slider.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
    }
    
    nextBtn.addEventListener('click', function() {
        if (currentIndex < cards.length - 1) {
            currentIndex++;
            updateSlider();
        }
    });
    
    prevBtn.addEventListener('click', function() {
        if (currentIndex > 0) {
            currentIndex--;
            updateSlider();
        }
    });
    
    // Auto-slide every 5 seconds
    setInterval(function() {
        if (currentIndex < cards.length - 1) {
            currentIndex++;
        } else {
            currentIndex = 0;
        }
        updateSlider();
    }, 5000);
    
    // Handle window resize
    window.addEventListener('resize', function() {
        cardWidth = cards[0].offsetWidth + 20;
        updateSlider();
    });
});