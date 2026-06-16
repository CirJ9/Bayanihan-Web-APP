// Note: 'userPoints' is defined in rewards.php before this script loads.

const pointsDisplay = document.getElementById("points");
const modal = document.getElementById("rewardModal");
const modalText = document.getElementById("modalText");

// Progress Bar Elements
const progressFill = document.getElementById("progressFill");
const currentPointsText = document.getElementById("currentPoints");
const maxPoints = 1000; // Level cap

// Initialize Progress Bar on Load
window.addEventListener('load', updateProgress);

function updateProgress() {
    // Calculate percentage
    let percentage = (userPoints / maxPoints) * 100;
    if(percentage > 100) percentage = 100;
    
    // Update visuals
    if(progressFill) progressFill.style.width = percentage + "%";
    if(currentPointsText) currentPointsText.textContent = userPoints;
}

function redeemReward(cost, rewardName) {
    if (userPoints >= cost) {
        // 1. Visually deduct points (Real deduction happens in Database via PHP later)
        // For a prototype, we just alert success. 
        // In a full app, this would use fetch() to call a PHP API.
        
        /* Since we are keeping it simple:
           We will redirect the user to a PHP script to process the redemption
           and then come back here.
        */
        if(confirm(`Confirm redemption of ${rewardName} for ${cost} points?`)) {
            // Redirect to a handling script (concept for Phase 3)
            alert(`🎉 Request sent! (Logic to deduct points from DB goes here)`);
        }
        
    } else {
        modalText.textContent = "❌ Not enough points to redeem this reward.";
        modal.style.display = "flex";
    }
}

function closeModal() {
    modal.style.display = "none";
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}