// user_settings.js - Final version with fixes

const API_BASE_URL = 'http://localhost/sugalhero-api'; // Adjust if your API folder name is different
let currentUserToken = null; // Will store the JWT

// --- API Endpoints for Settings ---
const API_USER_PROFILE_URL = `${API_BASE_URL}/user_profile.php`; // New endpoint for profile
const API_CHANGE_PASSWORD_URL = `${API_BASE_URL}/change_password.php`;
const API_CHANGE_USERNAME_URL = `${API_BASE_URL}/change_username.php`;
const API_DEPOSIT_URL = `${API_BASE_URL}/deposit.php`; // For Top Up Modal
const API_DELETE_ACCOUNT_URL = `${API_BASE_URL}/delete_account.php`; // NEW API endpoint for deletion

// --- Helper Functions (Common to dashboard, and now includes fetchAndDisplayUserProfile globally) ---

// Helper function to update balance display (for header dropdown and profile card)
async function updateBalanceDisplay() {
    const balanceDisplay = document.getElementById('headerBalanceDisplay');
    const profileBalanceDisplay = document.getElementById('currentBalanceDisplay'); // In the User Profile card

    if (!balanceDisplay || !currentUserToken) {
        if (!balanceDisplay && profileBalanceDisplay) { // Fallback if main header balance is missing
            profileBalanceDisplay.textContent = `$N/A`;
        }
        return;
    }

    try {
        const response = await fetch(`${API_BASE_URL}/user_profile.php`, { // Using user_profile.php for balance now
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
            if (profileBalanceDisplay) {
                profileBalanceDisplay.textContent = `$${balance}`; // Update profile card balance
            }
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

// Helper function to display messages for forms
function displaySettingsMessage(elementId, message, type) {
    const messageElement = document.getElementById(elementId);
    if (messageElement) {
        messageElement.textContent = message;
        messageElement.className = `settings-message ${type}`;
    }
}

// --- Function to Fetch and Display User Profile (MOVED TO GLOBAL SCOPE) ---
async function fetchAndDisplayUserProfile() {
    try {
        const response = await fetch(API_USER_PROFILE_URL, {
            method: 'GET',
            headers: { 'Authorization': `Bearer ${currentUserToken}` }
        });
        const data = await response.json();
        if (response.ok && data.success) {
            document.getElementById('currentUsername').textContent = data.profile.username;
            document.getElementById('currentUserEmail').textContent = data.profile.email;
            // currentBalanceDisplay is updated by updateBalanceDisplay, called earlier/separately
        } else {
            console.error('Failed to fetch user profile:', data.message);
        }
    } catch (error) {
        console.error('Network error fetching user profile:', error);
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
    
    // Initial fetch for balance and profile
    await updateBalanceDisplay(); // Update header balance and profile card balance
    await fetchAndDisplayUserProfile(); // Fetch and display username/email

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


    // --- Account Deletion Logic ---
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    if (deleteAccountBtn) {
        deleteAccountBtn.addEventListener('click', async () => {
            const confirmDeletion = window.confirm("Are you absolutely sure you want to delete your account? This action cannot be undone.");

            if (!confirmDeletion) {
                displaySettingsMessage('passwordMessage', 'Account deletion cancelled.', 'info'); // Using passwordMessage div for now
                return;
            }

            displaySettingsMessage('passwordMessage', 'Deleting account...', 'info');

            try {
                const response = await fetch(API_DELETE_ACCOUNT_URL, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${currentUserToken}`,
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    console.log('Account deleted successfully:', data.message);
                    localStorage.removeItem('jwt_token');
                    localStorage.removeItem('user_id');
                    localStorage.removeItem('username');
                    window.location.href = '../authentication/account_deleted.php';
                } else {
                    displaySettingsMessage('passwordMessage', data.message || 'Failed to delete account.', 'error');
                    console.error('Account deletion API error:', data);
                    if (response.status === 401) {
                        localStorage.removeItem('jwt_token');
                        localStorage.removeItem('user_id');
                        localStorage.removeItem('username');
                        window.location.href = '../authentication/login.php';
                    }
                }
            } catch (error) {
                displaySettingsMessage('passwordMessage', 'Network error deleting account.', 'error');
                console.error('Account deletion network error:', error);
            }
        });
    }

    // --- Change Password Logic ---
    document.getElementById('changePasswordBtn').addEventListener('click', async (e) => {
        e.preventDefault(); // Prevent form submission (if it's a form button)
        const oldPassword = document.getElementById('oldPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmNewPassword = document.getElementById('confirmNewPassword').value;

        if (!oldPassword || !newPassword || !confirmNewPassword) {
            displaySettingsMessage('passwordMessage', 'All fields are required.', 'error');
            return;
        }
        if (newPassword !== confirmNewPassword) {
            displaySettingsMessage('passwordMessage', 'New passwords do not match.', 'error');
            return;
        }
        if (newPassword.length < 8) {
            displaySettingsMessage('passwordMessage', 'Password must be at least 8 characters long.', 'error');
            return;
        }

        displaySettingsMessage('passwordMessage', 'Changing password...', 'info');

        try {
            const response = await fetch(API_CHANGE_PASSWORD_URL, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${currentUserToken}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ old_password: oldPassword, new_password: newPassword, confirm_new_password: confirmNewPassword })
            });
            const data = await response.json();

            if (response.ok && data.success) {
                displaySettingsMessage('passwordMessage', 'Password updated successfully!', 'success');
                // Clear fields
                document.getElementById('oldPassword').value = '';
                document.getElementById('newPassword').value = '';
                document.getElementById('confirmNewPassword').value = '';
            } else {
                displaySettingsMessage('passwordMessage', data.message || 'Failed to change password.', 'error');
                if (response.status === 401 || (data.message && (data.message.includes('Unauthorized') || data.message.includes('token')))) {
                    localStorage.removeItem('jwt_token');
                    localStorage.removeItem('user_id');
                    localStorage.removeItem('username');
                    window.location.href = '../authentication/login.php';
                }
            }
        } catch (error) {
            displaySettingsMessage('passwordMessage', 'Network error changing password.', 'error');
        }
    });


    // --- Change Username Logic ---
    document.getElementById('changeUsernameBtn').addEventListener('click', async (e) => {
        e.preventDefault(); // Prevent form submission
        const newUsername = document.getElementById('newUsername').value;

        if (!newUsername) {
            displaySettingsMessage('usernameMessage', 'New username is required.', 'error');
            return;
        }
        if (newUsername.length < 3 || newUsername.length > 50) {
            displaySettingsMessage('usernameMessage', 'Username must be 3-50 characters.', 'error');
            return;
        }

        displaySettingsMessage('usernameMessage', 'Changing username...', 'info');

        try {
            const response = await fetch(API_CHANGE_USERNAME_URL, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${currentUserToken}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ new_username: newUsername })
            });
            const data = await response.json();

            if (response.ok && data.success) {
                displaySettingsMessage('usernameMessage', 'Username updated successfully!', 'success');
                document.getElementById('newUsername').value = '';
                localStorage.setItem('username', data.newUsername); // Update username in localStorage
                document.getElementById('headerUsername').textContent = data.newUsername; // Update header
                document.getElementById('currentUsername').textContent = data.newUsername; // Update profile card
            } else {
                displaySettingsMessage('usernameMessage', data.message || 'Failed to change username.', 'error');
                if (response.status === 401 || (data.message && (data.message.includes('Unauthorized') || data.message.includes('token')))) {
                    localStorage.removeItem('jwt_token');
                    localStorage.removeItem('user_id');
                    localStorage.removeItem('username');
                    window.location.href = '../authentication/login.php';
                }
            }
        } catch (error) {
            displaySettingsMessage('usernameMessage', 'Network error changing username.', 'error');
        }
    });

    // --- Deposit Modal Handler (from dashboard.js, copied here) ---
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
}); // End of DOMContentLoaded