// --- API Configuration ---
const API_BASE_URL = 'http://localhost/sugalhero-api'; // Adjust if your API folder name is different
let currentUserToken = null; // To be used by falling_tokens.js for API calls
let fixedBetAmount = 40.00; // The fixed bet amount for this game


// --- Helper function to update balance display on the page ---
async function updateBalanceDisplay() {
    const balanceDisplay = document.getElementById('gameBalance');
    if (!balanceDisplay) {
        console.error("Balance display element not found!");
        return;
    }
    if (!currentUserToken) {
        balanceDisplay.textContent = 'N/A';
        return;
    }
    try {
        const response = await fetch(`${API_BASE_URL}/user_balance.php`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${currentUserToken}`,
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            console.error('Failed to fetch balance, token might be invalid or expired. Redirecting...');
            localStorage.removeItem('jwt_token');
            localStorage.removeItem('user_id');
            localStorage.removeItem('username');
            window.location.href = '../../authentication/login.php';
            return;
        }

        const data = await response.json();
        if (data.success) {
            balanceDisplay.textContent = parseFloat(data.balance).toFixed(2);
        } else {
            console.error('API Error fetching balance:', data.message);
            if (data.message.includes('Unauthorized') || data.message.includes('token')) {
                localStorage.removeItem('jwt_token');
                localStorage.removeItem('user_id');
                localStorage.removeItem('username');
                window.location.href = '../../authentication/login.php';
            }
        }
    } catch (error) {
        console.error('Network or Fetch Error fetching balance:', error);
        localStorage.removeItem('jwt_token');
        localStorage.removeItem('user_id');
        localStorage.removeItem('username');
        window.location.href = '../../authentication/login.php';
    }
}

// --- Helper function to display messages to the user ---
function displayGameOutcomeMessage(message, type = 'info') {
    const outcomeMessageDiv = document.getElementById("gameOutcomeMessage");
    if (outcomeMessageDiv) {
        outcomeMessageDiv.textContent = message;
        outcomeMessageDiv.style.color = {
            'info': 'white',
            'success': 'lightgreen',
            'error': 'red',
            'loss': 'red',
            'win': 'lightgreen'
        }[type] || 'white';
    }
}


// --- Page Protection & Initial Data Load (Moved from HTML) ---
document.addEventListener('DOMContentLoaded', async () => {
    const jwtToken = localStorage.getItem('jwt_token');
    const username = localStorage.getItem('username');
    const userId = localStorage.getItem('user_id');

    if (!jwtToken || !username || !userId) {
        console.log('No JWT token found. Redirecting to login...');
        window.location.href = '../../authentication/login.php';
        return;
    }

    currentUserToken = jwtToken; // Set the global variable

    document.getElementById('gameUsername').textContent = username;
    await updateBalanceDisplay(); // Initial balance display

    // --- Exit Game Functions ---
    document.getElementById('exitGameOverBtn').addEventListener('click', () => {
        window.location.href = '../../homepage/index.php';
    });

    // --- Event Listeners for Game Control Buttons ---
    document.getElementById("startButton").addEventListener("click", placeBetAndStartGame);
    document.getElementById("restartGameBtn").addEventListener("click", _restartGameInternal);
    document.getElementById("resetTokensBtn").addEventListener("click", resetTokens);

    // Initial call to gameLoop (will return immediately if not gameStarted yet)
    // This makes sure Matter.js starts its loop if you had it doing so.
    gameLoop();
});


// --- Game Logic and Core Functions (Original content) ---
const canvas = document.getElementById("gameCanvas");
const ctx = canvas.getContext("2d");
const sounds = {
  token: new Audio("sounds/collectcoin-6075.mp3"),
  multiplier: new Audio("sounds/power-up-type-1-230548.mp3"),
  bomb: new Audio("sounds/explosion-6055.mp3"),
  gameover: new Audio("sounds/negative_beeps-6008.mp3"),
  start: new Audio("sounds/game-start-6104.mp3")
};


let collector = { x: 200, width: 100, height: 20 };
let tokens = [];
let fallingSpeed = 2;
let score = 0; // Reset score for every game
document.getElementById("tokenCount").textContent = score;
let multiplier = 1;
let gameStarted = false;
let gameOver = false;
let timeLeft = 30;
let timerInterval = null;
let gradientOffset = 0;
let particles = [];
let stars = [];

// --- API Endpoints ---
const API_PLACE_BET_URL = `${API_BASE_URL}/place_bet.php`;
const API_RESOLVE_BET_URL = `${API_BASE_URL}/resolve_bet.php`;


// --- Global variables for ongoing bet ---
let currentBetTransactionId = null;


function playSound(audio) {
  audio.currentTime = 0;
  audio.play();
}


function startTimer() {
  const timerElement = document.getElementById("timer");
  timeLeft = 30;
  timerElement.textContent = timeLeft;

  clearInterval(timerInterval);

  timerInterval = setInterval(() => {
    timeLeft--;
    timerElement.textContent = timeLeft;

    timerElement.classList.remove("timer-animate");
    void timerElement.offsetWidth;
    timerElement.classList.add("timer-animate");

    if (timeLeft <= 0) {
      clearInterval(timerInterval);
      _endGameAndResolveBet();
    }
  }, 1000);
}



function createParticle() {
  particles.push({
    x: Math.random() * canvas.width,
    y: 0,
    radius: Math.random() * 5 + 2,
    speedX: Math.random() * 2 - 1,
    speedY: Math.random() * 3 + 1,
    color: 'rgba(255, 255, 255, 0.5)',
  });
}

function drawParticles() {
  for (let i = 0; i < particles.length; i++) {
    let p = particles[i];

    p.x += p.speedX;
    p.y += p.speedY;

    ctx.beginPath();
    ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
    ctx.fillStyle = p.color;
    ctx.fill();

    if (p.y > canvas.height || p.x > canvas.width || p.x < 0) {
      particles.splice(i, 1);
      i--;
    }
  }
}

function createStar() {
  stars.push({
    x: Math.random() * canvas.width,
    y: Math.random() * canvas.height,
    size: Math.random() * 2 + 1,
    opacity: Math.random(),
  });
}

function drawStars() {
  for (let i = 0; i < stars.length; i++) {
    let star = stars[i];
    ctx.beginPath();
    ctx.arc(star.x, star.y, star.size, 0, Math.PI * 2);
    ctx.fillStyle = `rgba(255, 255, 255, ${star.opacity})`;
    ctx.fill();

    star.opacity -= 0.01;
    if (star.opacity <= 0) {
      stars.splice(i, 1);
      i--;
    }
  }
}

function startParticles() {
  const particleContainer = document.getElementById("fallingParticles");
  
  setInterval(() => {
    const particle = document.createElement("div");
    particle.classList.add("particle");

    const randomX = Math.random() * canvas.width;
    const size = Math.random() * 5 + 5;
    particle.style.left = `${randomX}px`;
    particle.style.width = `${size}px`;
    particle.style.height = `${size}px`;

    particleContainer.appendChild(particle);

    setTimeout(() => {
      particle.remove();
    }, 3000);
  }, 100);
}

function drawBackground() {
  const gradient = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);

  gradient.addColorStop(0, `rgb(${Math.floor(gradientOffset)}, ${Math.floor(gradientOffset / 2)}, 60)`);
  gradient.addColorStop(0.5, `rgb(7, 55, 26)`);
  gradient.addColorStop(1, `rgb(0, 0, 20)`);

  ctx.fillStyle = gradient;
  ctx.fillRect(0, 0, canvas.width, canvas.height);
  
  gradientOffset += 0.5;
  if (gradientOffset > 255) gradientOffset = 0;
}


function spawnItem() {
  const types = ["token", "multiplier", "bomb"];
  const type = types[Math.floor(Math.random() * types.length)];
  const x = Math.random() * (canvas.width - 30);

  let value = 1;
  if (type === "multiplier") value = Math.floor(Math.random() * 5) + 1;

  tokens.push({ x, y: 0, type, value });
}

function drawCollector() {
  ctx.fillStyle =`rgb(50, 205, 50)`;
  ctx.fillRect(collector.x, canvas.height - collector.height - 10, collector.width, collector.height);
}

function drawItems() {
  tokens.forEach((item) => {
    if (item.type === "token") {
      ctx.fillStyle = "#FFD700";
      ctx.font = "30px Arial";
      ctx.fillText("💠", item.x, item.y);
    } else if (item.type === "multiplier") {
      ctx.fillStyle = "#7FFF00";
      ctx.font = "25px Arial";
      ctx.fillText(`${item.value}x`, item.x, item.y);
    } else if (item.type === "bomb") {
      ctx.fillStyle = "#FF4500";
      ctx.font = "30px Arial";
      ctx.fillText("💣", item.x, item.y);
    }
  });
}

function updateItems() {
  tokens.forEach((item, index) => {
    item.y += fallingSpeed;

    const hit = item.y > canvas.height - 30 &&
      item.x > collector.x - 20 &&
      item.x < collector.x + collector.width;

    if (hit) {
      if (item.type === "token") {
        score += 1 * multiplier;
        document.getElementById("tokenCount").textContent = score;
        playSound(sounds.token);
      } else if (item.type === "multiplier") {
        multiplier = item.value;
        document.getElementById("multiplier").textContent = multiplier + "x";
        playSound(sounds.multiplier);
      } else if (item.type === "bomb") {
        playSound(sounds.bomb);
        _endGameAndResolveBet();
      }

      tokens.splice(index, 1);
    }

    if (item.y > canvas.height) tokens.splice(index, 1);
  });
}

// --- Internal End Game function to resolve bet ---
async function _endGameAndResolveBet() {
    gameOver = true;
    clearInterval(timerInterval);
    playSound(sounds.gameover);

    const totalWinAmount = score; // Score already includes the multiplier effect on tokens
    // --- MODIFIED PAYOUT CALCULATION ---
    // payoutAmount is just the profit from tokens. Original bet is not returned.
    const payoutAmount = totalWinAmount;

    const outcome = (totalWinAmount > 0) ? 'win' : 'loss';
    const gameId = 5;

    document.getElementById("gameOverScreen").style.display = "flex";
    document.getElementById("finalTokens").textContent = score;
    displayGameOutcomeMessage(`Outcome: ${outcome.toUpperCase()}! ${outcome === 'win' ? `You won $${totalWinAmount.toFixed(2)}!` : 'You lost your bet.'}`, outcome);

    const restartBtn = document.getElementById("restartGameBtn");
    if (restartBtn) {
        restartBtn.textContent = `Restart? $${fixedBetAmount.toFixed(2)}`;
    }


    if (currentBetTransactionId) {
        try {
            const resolveData = {
                bet_transaction_id: currentBetTransactionId,
                outcome: outcome,
                // Pass payoutAmount (which is totalWinAmount if win, else 0)
                payout_amount: outcome === 'win' ? payoutAmount.toFixed(2) : 0
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
                updateBalanceDisplay();
            } else {
                console.error('API Error resolving bet:', data.message || 'Unknown API error', data);
                displayGameOutcomeMessage(`Error resolving bet: ${data.message || 'Check console.'}`, 'error');
                if (response.status === 401) {
                    localStorage.removeItem('jwt_token');
                    localStorage.removeItem('user_id');
                    localStorage.removeItem('username');
                    window.location.href = '../../authentication/login.php';
                }
            }
        } catch (error) {
            console.error('Network error during bet resolution:', error);
            displayGameOutcomeMessage(`Network error during bet resolution.`, 'error');
        } finally {
            currentBetTransactionId = null;
        }
    } else {
        console.warn("No active bet transaction ID to resolve.");
        displayGameOutcomeMessage("Game Over. No bet was recorded to resolve.", 'warning');
    }
}


function resetTokens() {
  score = 0;
  document.getElementById("tokenCount").textContent = "0";
  displayGameOutcomeMessage('');
}

// --- Modified restartGameInternal to call placeBetAndStartGame ---
function _restartGameInternal() {
  // Clear game over screen immediately
  document.getElementById("gameOverScreen").style.display = "none";
  displayGameOutcomeMessage(''); // Clear message

  // Reset core game variables only (score, multiplier, tokens, gameOver)
  score = 0;
  multiplier = 1;
  tokens = [];
  gameOver = false;
  document.getElementById("tokenCount").textContent = "0";
  document.getElementById("multiplier").textContent = "1x";
  
  // Call the function that places a bet and then starts the game
  placeBetAndStartGame();
}

// --- Main function to handle placing bet and starting game ---
async function placeBetAndStartGame() {
    console.log("Attempting to place bet and start game...");
    if (!currentUserToken) {
        displayGameOutcomeMessage("You must be logged in to play.", 'error');
        window.location.href = '../../authentication/login.php';
        return;
    }

    displayGameOutcomeMessage(`Placing bet of $${fixedBetAmount.toFixed(2)}...`, 'info');

    try {
        const response = await fetch(API_PLACE_BET_URL, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${currentUserToken}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ amount: fixedBetAmount, game_id: 5, details: "Falling Tokens Bet" })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            displayGameOutcomeMessage("Bet placed! Starting game...", 'success');
            currentBetTransactionId = data.betTransactionId;
            _startGamePhysics();
        } else {
            displayGameOutcomeMessage(`Bet failed: ${data.message || 'Unknown error'}`, 'error');
            if (response.status === 401) {
                localStorage.removeItem('jwt_token');
                localStorage.removeItem('user_id');
                localStorage.removeItem('username');
                window.location.href = '../../authentication/login.php';
            }
        }
    } catch (error) {
        displayGameOutcomeMessage(`Network error during bet placement: ${error.message}`, 'error');
        console.error('Place Bet Network Error:', error);
    }
}


// --- Internal function to handle actual game start (renamed from original startGame) ---
function _startGamePhysics() {
  gameStarted = true;
  document.getElementById("startScreen").style.display = "none";
  playSound(sounds.start);
  startTimer();
  gameLoop();
  startParticles();
}


function gameLoop() {
    if (!gameStarted || gameOver) return;

  ctx.clearRect(0, 0, canvas.width, canvas.height);

  drawBackground();
  drawParticles();
  drawStars();
  drawCollector();
  drawItems();
  updateItems();

  if (Math.random() < 0.05) {
    createParticle();

  if (Math.random() < 0.05) {
    createStar();
  }
  }

  requestAnimationFrame(gameLoop);
}

canvas.addEventListener("mousemove", (e) => {
  const rect = canvas.getBoundingClientRect();
  const mouseX = e.clientX - rect.left;

  collector.x = mouseX - collector.width / 2;

  if (collector.x < 0) collector.x = 0;
  if (collector.x + collector.width > canvas.width)
    collector.x = canvas.width - collector.width;
});


// Start spawning items after initial setup
setInterval(() => {
  if (!gameOver && gameStarted) spawnItem();
}, 1000);

// Initial call to gameLoop (will return immediately if not gameStarted)
gameLoop();