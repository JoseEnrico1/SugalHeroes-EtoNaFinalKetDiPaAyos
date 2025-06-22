// Ensure API_BASE_URL, currentUserToken, updateBalanceDisplay, displayGameMessage
// are accessible from the <script> block in karakrus.html

// --- API Endpoints ---
const API_PLACE_BET_URL = `${API_BASE_URL}/place_bet.php`;
const API_RESOLVE_BET_URL = `${API_BASE_URL}/resolve_bet.php`;
const API_USER_BALANCE_URL = `${API_BASE_URL}/user_balance.php`; // To fetch balance for validation

// --- Game Variables ---
// Removed local playerBalance, balance is now fetched from API
const GAME_ID = 6; // Game ID for Kara Krus. Ensure this matches your 'games' table!

// Image paths for coins (make sure these match your folder and file names!)
const KRA_IMAGE_PATH = 'assets/kara_coin.png'; // Path to your Kara (Heads) coin image
const KRUS_IMAGE_PATH = 'assets/krus_coin.png'; // Path to your Krus (Tails) coin image

// --- DOM Elements ---
const playerBalanceSpan = document.getElementById('playerBalance'); // Footer balance (keep for consistency)
const betAmountInput = document.getElementById('betAmount');
const flipCoinsBtn = document.getElementById('flipCoinsBtn');
const coin1ResultImg = document.getElementById('coin1Result');
const coin2ResultImg = document.getElementById('coin2Result');
const gameMessagePara = document.getElementById('gameMessage'); // Uses displayGameMessage() helper now

// --- Global variables for ongoing bet ---
let currentBetTransactionId = null;

// --- Initial Setup (Modified) ---
// Balance display handled by updateBalanceDisplay() from HTML script
// Ensure coins are initially hidden or reset
coin1ResultImg.src = '';
coin2ResultImg.src = '';
coin1ResultImg.classList.remove('visible', 'flipping');
coin2ResultImg.classList.remove('visible', 'flipping');


// --- Game Logic Functions ---

/**
 * Simulates a single coin flip.
 * @returns {number} 1 for Kara (Heads), 0 for Krus (Tails).
 */
function flipCoin() {
    return Math.round(Math.random());
}

/**
 * Internal function to perform coin flips and determine outcome after bet is placed.
 */
async function _performCoinFlipsAndResolve(betAmount) {
    displayGameMessage('Flipping coins...', 'info');

    // Prepare for Coin Flip Animation
    coin1ResultImg.src = '';
    coin2ResultImg.src = '';
    coin1ResultImg.classList.remove('visible', 'flipping');
    coin2ResultImg.classList.remove('flipping');
    void coin1ResultImg.offsetWidth;
    void coin2ResultImg.offsetWidth;

    // Simulate Coin Flips and Update UI after a short delay
    setTimeout(async () => { // Made this setTimeout callback async
        const coin1 = flipCoin();
        const coin2 = flipCoin();

        coin1ResultImg.src = coin1 === 1 ? KRA_IMAGE_PATH : KRUS_IMAGE_PATH;
        coin2ResultImg.src = coin2 === 1 ? KRA_IMAGE_PATH : KRUS_IMAGE_PATH;

        coin1ResultImg.classList.add('visible', 'flipping');
        coin2ResultImg.classList.add('visible', 'flipping');

        // After animation visually completes, remove 'flipping' class
        setTimeout(() => {
            coin1ResultImg.classList.remove('flipping');
            coin2ResultImg.classList.remove('flipping');
        }, 700); // Match CSS animation duration

        // Determine Game Outcome
        let outcomeMessage = '';
        let outcomeType = ''; // 'win', 'loss', 'draw'
        let payoutAmount = 0; // Amount to send to resolve_bet.php

        if (coin1 === 1 && coin2 === 1) { // Both Kara (Heads)
            outcomeMessage = 'Both Kara! Dealer Wins. You lose your bet.';
            outcomeType = 'loss';
            payoutAmount = 0; // No return
        } else if (coin1 === 0 && coin2 === 0) { // Both Krus (Tails)
            outcomeMessage = `Both Krus! You Win! You double your bet.`;
            outcomeType = 'win';
            payoutAmount = betAmount * 2; // Double the original bet
        } else { // One Kara, One Krus (Mixed)
            outcomeMessage = 'One Kara, One Krus. It\'s a Draw! Your bet is returned.';
            outcomeType = 'win'; // Treat draw as a win to return the bet amount
            payoutAmount = betAmount; // Return the original bet amount
        }

        displayGameMessage(outcomeMessage, outcomeType); // Display outcome message

        // Call API to resolve bet
        if (currentBetTransactionId) {
            try {
                const resolveData = {
                    bet_transaction_id: currentBetTransactionId,
                    outcome: outcomeType,
                    payout_amount: payoutAmount.toFixed(2)
                };

                const response = await fetch(API_RESOLVE_BET_URL, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${currentUserToken}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(resolveData)
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    console.log('Bet resolved successfully via API:', data);
                    updateBalanceDisplay(); // Refresh balance display after resolution
                } else {
                    console.error('API Error resolving bet:', data.message || 'Unknown API error', data);
                    displayGameMessage(`Error resolving bet: ${data.message || 'Check console.'}`, 'error');
                    if (response.status === 401) { // Unauthorized, token likely expired
                        localStorage.removeItem('jwt_token');
                        localStorage.removeItem('user_id');
                        localStorage.removeItem('username');
                        window.location.href = '../../authentication/login.php';
                    }
                }
            } catch (error) {
                console.error('Network error during bet resolution:', error);
                displayGameMessage(`Network error during bet resolution.`, 'error');
            } finally {
                currentBetTransactionId = null; // Clear transaction ID
                flipCoinsBtn.disabled = false; // Re-enable button
                betAmountInput.disabled = false; // Re-enable input
            }
        } else {
            console.warn("No active bet transaction ID to resolve.");
            displayGameMessage("Game Over. No bet was recorded to resolve.", 'warning');
            flipCoinsBtn.disabled = false;
            betAmountInput.disabled = false;
        }
    }, 100); // Short delay before actual coin flip results are displayed
}

/**
 * Main function triggered by "Flip Coins!" button. Handles bet placement.
 */
async function playRound() {
    const betAmount = parseFloat(betAmountInput.value); // Use parseFloat for currency
    const currentBalanceText = document.getElementById('gameBalance').textContent; // Get balance from displayed HTML
    const currentBalance = parseFloat(currentBalanceText);

    if (isNaN(betAmount) || betAmount <= 0) {
        displayGameMessage('Please enter a valid positive bet amount.', 'warning');
        return;
    }

    if (isNaN(currentBalance) || betAmount > currentBalance) { // Check against actual displayed balance
        displayGameMessage(`You don't have enough money! Your balance: $${currentBalance.toFixed(2)}`, 'error');
        return;
    }

    displayGameMessage(`Placing bet of $${betAmount.toFixed(2)}...`, 'info');
    flipCoinsBtn.disabled = true;
    betAmountInput.disabled = true;

    try {
        const response = await fetch(API_PLACE_BET_URL, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${currentUserToken}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ amount: betAmount, game_id: GAME_ID, details: "Kara Krus Bet" })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            displayGameMessage("Bet placed! Flipping coins...", 'success');
            currentBetTransactionId = data.betTransactionId;
            updateBalanceDisplay(); // Refresh balance display after bet deduction
            _performCoinFlipsAndResolve(betAmount); // Proceed with coin flips
        } else {
            displayGameMessage(`Bet failed: ${data.message || 'Unknown error'}`, 'error');
            flipCoinsBtn.disabled = false; // Re-enable button on failure
            betAmountInput.disabled = false; // Re-enable input on failure
            if (response.status === 401) {
                localStorage.removeItem('jwt_token');
                localStorage.removeItem('user_id');
                localStorage.removeItem('username');
                window.location.href = '../../authentication/login.php';
            }
        }
    } catch (error) {
        displayGameMessage(`Network error during bet placement: ${error.message}`, 'error');
        console.error('Place Bet Network Error:', error);
        flipCoinsBtn.disabled = false;
        betAmountInput.disabled = false;
    }
}

// --- Event Listener ---
// Attach the main playRound function to the 'click' event of the "Flip Coins!" button
flipCoinsBtn.addEventListener('click', playRound);

// Optional: Initial setup for the game message after page load (can be handled by displayGameMessage)
window.onload = () => {
    displayGameMessage('Place your bet and flip the coins!', 'info');
    coin1ResultImg.src = '';
    coin2ResultImg.src = '';
    coin1ResultImg.classList.remove('visible');
    coin2ResultImg.classList.remove('visible');
};