// user_help.js

const API_BASE_URL = 'http://localhost/sugalhero-api'; // Adjust if your API folder name is different
let currentUserToken = null; // Will store the JWT

// --- Helper function to update balance display (copied from user_settings.js) ---
async function updateBalanceDisplay() {
    const balanceDisplay = document.getElementById('headerBalanceDisplay');
    // The help page doesn't have a profile balance display, but this function is robust
    if (!balanceDisplay || !currentUserToken) return;

    try {
        const response = await fetch(`${API_BASE_URL}/user_profile.php`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${currentUserToken}`,
                'Content-Type': 'application/json'
            }
        });
        const data = await response.json();
        if (response.ok && data.success) {
            const balance = parseFloat(data.profile.currency_balance).toFixed(2);
            balanceDisplay.textContent = `Balance: $${balance}`;
        } else {
            console.error('API Error fetching balance:', response.status, data.message || 'Unknown error');
            if (response.status === 401 || (data.message && (data.message.includes('Unauthorized') || data.message.includes('token')))) {
                localStorage.removeItem('jwt_token');
                localStorage.removeItem('user_id');
                localStorage.removeItem('username');
                window.location.href = '../authentication/login.php';
            }
        }
    } catch (error) {
        console.error('Network or Fetch Error fetching balance (catch block):', error);
        localStorage.removeItem('jwt_token');
        localStorage.removeItem('user_id');
        localStorage.removeItem('username');
        window.location.href = '../authentication/login.php';
    }
}

// --- Deposit Modal Handler (copied from user_settings.js) ---
async function handleDeposit() {
    const amountInput = document.getElementById('modalDepositAmount');
    const amount = parseFloat(amountInput.value);
    const modalMessage = document.getElementById('modalMessage');

    if (isNaN(amount) || amount <= 0) {
        modalMessage.textContent = "Please enter a valid positive amount.";
        modalMessage.className = 'modal-message error';
        return;
    }

    modalMessage.textContent = "Processing deposit...";
    modalMessage.className = 'modal-message info';

    try {
        const response = await fetch(`${API_BASE_URL}/deposit.php`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${currentUserToken}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ amount: amount })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            modalMessage.textContent = "Deposit successful!";
            modalMessage.className = 'modal-message success';
            amountInput.value = '50.00';
            await updateBalanceDisplay(); // Refresh balance after deposit
        } else {
            modalMessage.textContent = data.message || "Deposit failed. Please try again.";
            modalMessage.className = 'modal-message error';
            if (response.status === 401) {
                localStorage.removeItem('jwt_token');
                localStorage.removeItem('user_id');
                localStorage.removeItem('username');
                window.location.href = '../authentication/login.php';
            }
        }
    } catch (error) {
        modalMessage.textContent = "Network error. Please check your connection.";
        modalMessage.className = 'modal-message error';
        console.error('Deposit network error:', error);
    }
}


// --- Main Page Load Logic ---
document.addEventListener('DOMContentLoaded', async () => {
    const jwtToken = localStorage.getItem('jwt_token');
    const username = localStorage.getItem('username');
    const userId = localStorage.getItem('user_id');

    // --- Page Protection ---
    if (!jwtToken || !username || !userId) {
        console.log('No JWT token found. Redirecting to login...');
        window.location.href = '../authentication/login.php';
        return;
    }
    currentUserToken = jwtToken; // Set global token

    // --- Header User Info & Dropdown ---
    document.getElementById('headerUsername').textContent = username;
    await updateBalanceDisplay(); // Initial balance display

    // Dropdown toggle logic
    document.getElementById('userDropdownToggle').addEventListener('click', () => {
        document.getElementById('userDropdownMenu').classList.toggle('show');
    });
    window.onclick = function(event) {
        if (!event.target.matches('.dropdown-toggle') && !event.target.closest('.dropdown-toggle')) {
            const dropdown = document.getElementById("userDropdownMenu");
            if (dropdown && dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        }
    };

    // Logout button
    document.getElementById('logoutBtn').addEventListener('click', () => {
        localStorage.removeItem('jwt_token');
        localStorage.removeItem('user_id');
        localStorage.removeItem('username');
        window.location.href = '../authentication/login.php';
    });

    // Top Up button (from header dropdown)
    document.getElementById('topUpBtn').addEventListener('click', () => {
        document.getElementById('topUpModal').style.display = 'flex'; // Show modal
        document.getElementById('modalDepositAmount').value = '50.00'; // Reset amount
        document.getElementById('modalMessage').textContent = ''; // Clear message
    });
    // Top Up Modal close/confirm logic
    document.getElementById('closeTopUpModal').addEventListener('click', () => {
        document.getElementById('topUpModal').style.display = 'none';
    });
    window.addEventListener('click', (event) => {
        if (event.target == document.getElementById('topUpModal')) {
            document.getElementById('topUpModal').style.display = 'none';
        }
    });
    document.getElementById('confirmDepositBtn').addEventListener('click', handleDeposit);

    // --- Sidebar Navigation (No specific JS for internal links, they are direct hrefs) ---
    // The help page doesn't have a main content that scrolls, so no specific scroll links needed here.
    // The helpLink itself is active and handled by HTML.
});