// --- Matter.js setup ---
const { Engine, Render, Runner, World, Bodies, Events } = Matter;

const canvas = document.getElementById("plinkoCanvas");
const width = 600;
const height = 700;
canvas.width = width;
canvas.height = height;

const engine = Engine.create();
const world = engine.world;

const render = Render.create({
  canvas,
  engine,
  options: {
    width,
    height,
    wireframes: false,
    background: '#111'
  }
});

Render.run(render);
Runner.run(Runner.create(), engine);

// --- Sounds Integration ---
const sounds = {
  click: new Audio('sounds/sounds_click.wav'),
  score: new Audio('sounds/sounds_score.wav')
};

sounds.click.volume = 0.5;
sounds.score.volume = 0.8;

for (let key in sounds) {
  sounds[key].load();
}

function playSound(sound) {
  if (sound) {
    sound.currentTime = 0;
    sound.play().catch(e => console.warn("Audio playback failed:", e));
  }
}

// --- Game settings ---
const rows = 8;
const slots = rows + 1;
const spacingX = width / slots;
const spacingY = 60;
const pegRadius = 5;

const multipliers = {
  low:    [0.5, 0.8, 1, 1.2, 1.5, 1.2, 1, 0.8, 0.5],
  medium: [0.4, 0.7, 1, 1.5, 2, 1.5, 1, 0.7, 0.4],
  high:   [0.2, 0.5, 0.8, 1.5, 3, 1.5, 0.8, 0.5, 0.2]
};

// --- API endpoints ---
const API_PLACE_BET_URL = `${API_BASE_URL}/place_bet.php`;
const API_RESOLVE_BET_URL = `${API_BASE_URL}/resolve_bet.php`;
const API_USER_BALANCE_URL = `${API_BASE_URL}/user_balance.php`;

let currentBetTransactionId = null;
let currentBetAmount = 0;

// --- UI Functions ---
async function updateBalanceDisplay() {
  const balanceDisplay = document.getElementById('gameBalance');
  if (!balanceDisplay || !currentUserToken) return;

  try {
    const response = await fetch(API_USER_BALANCE_URL, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${currentUserToken}`,
        'Content-Type': 'application/json'
      }
    });

    const data = await response.json();
    if (response.ok && data.success) {
      balanceDisplay.textContent = parseFloat(data.balance).toFixed(2);
    } else {
      console.error('Failed to update balance:', data.message || 'Unknown error');
    }
  } catch (error) {
    console.error('Network error updating balance:', error);
  }
}

function displayGameMessage(message, type = 'info') {
  const resultDiv = document.getElementById("result");
  if (resultDiv) {
    resultDiv.textContent = message;
    resultDiv.style.color = {
      info: 'white',
      success: '#00B140',
      error: 'red',
      warning: 'orange'
    }[type] || 'white';
  }
}

function drawMultipliers(risk) {
  const container = document.getElementById("multipliers");
  container.innerHTML = "";
  multipliers[risk].forEach(m => {
    const div = document.createElement("div");
    div.innerText = `${m}x`;
    container.appendChild(div);
  });
}

// --- Game Board Setup ---
function setupBoard() {
  World.add(world, Bodies.rectangle(width / 2, height + 20, width, 40, { isStatic: true }));

  World.add(world, [
    Bodies.rectangle(-10, height / 2, 20, height, { isStatic: true }),
    Bodies.rectangle(width + 10, height / 2, 20, height, { isStatic: true })
  ]);

  for (let row = 0; row < rows; row++) {
    for (let col = 0; col <= row; col++) {
      const x = (width / 2) - (row * spacingX / 2) + col * spacingX;
      const y = 80 + row * spacingY;
      const peg = Bodies.circle(x, y, pegRadius, {
        isStatic: true,
        render: { fillStyle: '#76B900' }
      });
      World.add(world, peg);
    }
  }

  for (let i = 0; i <= slots; i++) {
    const x = spacingX * i;
    const divider = Bodies.rectangle(x, height - 40, 10, 120, {
      isStatic: true,
      render: { fillStyle: '#76B900' }
    });
    World.add(world, divider);
  }
}

setupBoard();
drawMultipliers("low");

document.getElementById("riskLevel").addEventListener("change", (e) => {
  drawMultipliers(e.target.value);
});

// --- Main betting function ---
async function placeBetAndDropBall() {
  playSound(sounds.click);

  const betInput = document.getElementById("betAmount");
  const risk = document.getElementById("riskLevel").value;
  const bet = parseFloat(betInput.value);

  if (isNaN(bet) || bet <= 0) {
    displayGameMessage("Please enter a valid bet amount.", 'error');
    return;
  }
  if (!currentUserToken) {
    displayGameMessage("You must be logged in to place a bet.", 'error');
    window.location.href = '../../authentication/login.php';
    return;
  }

  displayGameMessage(`Placing bet of $${bet.toFixed(2)}...`, 'info');

  try {
    const response = await fetch(API_PLACE_BET_URL, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${currentUserToken}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        amount: bet,
        game_id: 2,
        details: `Plinko ${risk} risk`
      })
    });

    const data = await response.json();

    if (response.ok && data.success) {
      displayGameMessage(`Bet placed! Dropping ball...`, 'success');
      currentBetTransactionId = data.betTransactionId;
      currentBetAmount = bet;
      updateBalanceDisplay();
      _dropBallPhysics(bet, risk);
    } else {
      displayGameMessage(`Bet failed: ${data.message || 'Unknown error'}`, 'error');
      if (response.status === 401) {
        localStorage.clear();
        window.location.href = '../../authentication/login.php';
      }
    }
  } catch (error) {
    displayGameMessage(`Network error: ${error.message}`, 'error');
    console.error('Place Bet Network Error:', error);
  }
}

// --- Ball Physics Function ---
function _dropBallPhysics(bet, risk) {
  const offset = spacingX / 6;
  const positions = [-offset, 0, offset];
  const spawnX = width / 2 + positions[Math.floor(Math.random() * positions.length)];

  const ball = Bodies.circle(spawnX, 0, 10, {
    restitution: 0.6,
    render: { fillStyle: '#76B900' }
  });

  World.add(world, ball);

  Events.on(engine, "afterUpdate", async function check() {
    if (ball.position.y > height - 80) {
      Events.off(engine, "afterUpdate", check);

      const index = Math.floor(ball.position.x / spacingX);
      const multiplier = multipliers[risk][index] || 0;
      const win = currentBetAmount * multiplier;
      const outcome = win > 0 ? 'win' : 'loss';

      playSound(sounds.score);
      displayGameMessage(`Landed in Slot ${index + 1} | Multiplier: ${multiplier}x | ${outcome.toUpperCase()}!`);

      setTimeout(() => {
        World.remove(world, ball);
      }, 1000);

      if (currentBetTransactionId) {
        try {
          const resolveData = {
            bet_transaction_id: currentBetTransactionId,
            outcome: outcome,
            payout_amount: win.toFixed(2)
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
            displayGameMessage(`Game result: ${outcome.toUpperCase()}! ${data.message || ''}`, 'success');
            updateBalanceDisplay();
          } else {
            displayGameMessage(`Error resolving bet: ${data.message || 'Unknown error'}`, 'error');
            if (response.status === 401) {
              localStorage.clear();
              window.location.href = '../../authentication/login.php';
            }
          }
        } catch (error) {
          displayGameMessage(`Network error resolving bet: ${error.message}`, 'error');
          console.error('Resolve Bet Error:', error);
        } finally {
          currentBetTransactionId = null;
          currentBetAmount = 0;
        }
      } else {
        displayGameMessage("No active bet to resolve. Please place a bet first.", 'warning');
      }
    }
  });
}

// --- Button Event Listener ---
document.getElementById("dropBallBtn").addEventListener("click", placeBetAndDropBall);
