// JavaScript for swipe navigation and page control
let currentPageIndex = 0;
const pages = document.querySelectorAll('.page');
const pageContainer = document.querySelector('.page-container');
const pageCount = pages.length;

let startX = 0; // For storing touch start position
let endX = 0; // For storing touch end position

// Show page based on the index
function showPage(index) {
    if (index < 0) {
        currentPageIndex = pageCount - 1; // Loop to last page
    } else if (index >= pageCount) {
        currentPageIndex = 0; // Loop to first page
    } else {
        currentPageIndex = index;
    }

    // Adjust the page container's position
    pageContainer.style.transform = `translateX(-${currentPageIndex * 100}%)`;
}

// Swipe left (next page) or swipe right (previous page)
function handleSwipe() {
    if (endX < startX) {
        // Swipe left, go to next page
        currentPageIndex++;
    } else if (endX > startX) {
        // Swipe right, go to previous page
        currentPageIndex--;
    }

    // Show the appropriate page based on the new index
    showPage(currentPageIndex);
}

// Handle touch start event
pageContainer.addEventListener('touchstart', (e) => {
    startX = e.touches[0].clientX;
});

// Handle touch end event
pageContainer.addEventListener('touchend', (e) => {
    endX = e.changedTouches[0].clientX;
    handleSwipe();
});

// Initialize first page
showPage(currentPageIndex);
