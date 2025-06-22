<?php
// homepage/purchase_sgc.php

// This page allows users to purchase SugalCoin (SGC).
// Actual payment processing will be handled by a backend API.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Purchase SGC - SUGALHEROES</title>
    <!-- Link to Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Link ONLY to the comprehensive purchase-sgc.css -->
    <link rel="stylesheet" href="purchase-sgc.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar - Consistent with homepage -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../images/newlogo.png" alt="Your Brand Logo" id="sidebarLogo">
            </div>
            <nav class="main-nav">
                <ul>
                    <!-- Nav items, updated to link correctly from this page -->
                    <li><a href="index.php#home" class="nav-item"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="index.php#games-section" class="nav-item"><i class="fas fa-gamepad"></i> Games</a></li>
                    <li><a href="../dashboard/user_dashboard.php" class="nav-item"><i class="fas fa-clipboard-list"></i> Dashboard</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <!-- Header - Consistent with homepage -->
            <header class="header">
                <div class="left">
                    <!-- Home Button - Similar to dashboard, leads back to homepage -->
                    <a href="index.php" class="home-button">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                </div>
                
                <div class="right-header-actions">
                    <!-- Crypto Wallet Display - display only, not clickable on this page -->

                    <div class="crypto-wallet-display">
                        <span id="sugalCoinBalance">0 SGC</span> 
                        <button id="depositSGCBtnHeader" class="small-header-button">Deposit SGC</button>
                    </div>

                    <!-- User Account Dropdown - Consistent with homepage -->
                    <div class="user-account-dropdown">
                        <button class="dropdown-toggle" id="userDropdownToggle">
                            <i class="fas fa-user-circle"></i> <span id="headerUsername">Username</span>
                        </button>
                        <div class="dropdown-menu" id="myDropdown">
                            <div class="account-balance" id="userBalanceDisplay">Balance: $0.00</div>
                            <button class="top-up-button" id="topUpBtn">Top Up</button>
                            <button class="logout-button" id="logoutBtn">Log Out</button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content for Purchase SGC -->
            <section class="dashboard-widget" id="purchase-sgc-page-content">
                <div class="hero-content-wrapper">
                    <h1 class="page-title">SUGALHEROES</h1> <!-- Custom class for giant page title -->
                    <p class="purchase-info">Select the amount of SugalCoin (SGC) you wish to purchase.</p>

                    <div class="purchase-options-grid">
                        <!-- Purchase Option 1 -->
                        <div class="purchase-card">
                            <span class="sgc-amount">100 SGC</span>
                            <span class="price">($10,000.00)</span>
                            <button class="buy-sgc-btn" data-sgc-amount="100">Buy Now</button>
                        </div>
                        <!-- Purchase Option 2 -->
                        <div class="purchase-card">
                            <span class="sgc-amount">250 SGC</span>
                            <span class="price">($25,000.00)</span>
                            <button class="buy-sgc-btn" data-sgc-amount="250">Buy Now</button>
                        </div>
                        <!-- Purchase Option 3 -->
                        <div class="purchase-card">
                            <span class="sgc-amount">500 SGC</span>
                            <span class="price">($50,000.00)</span>
                            <button class="buy-sgc-btn" data-sgc-amount="500">Buy Now</button>
                        </div>
                        <!-- Purchase Option 4 -->
                        <div class="purchase-card">
                            <span class="sgc-amount">1,000 SGC</span>
                            <span class="price">($100,000.00)</span>
                            <button class="buy-sgc-btn" data-sgc-amount="1000">Buy Now</button>
                        </div>
                        <!-- Purchase Option 5 -->
                        <div class="purchase-card">
                            <span class="sgc-amount">2,500 SGC</span>
                            <span class="price">($250,000.00)</span>
                            <button class="buy-sgc-btn" data-sgc-amount="2500">Buy Now</button>
                        </div>
                        <!-- Purchase Option 6 -->
                        <div class="purchase-card">
                            <span class="sgc-amount">5,000 SGC</span>
                            <span class="price">($500,000.00)</span>
                            <button class="buy-sgc-btn" data-sgc-amount="5000">Buy Now</button>
                        </div>
                    </div>
                    <p id="purchaseSGC_message" class="modal-message" style="margin-top: 25px;"></p> 
                    <p style="font-size: 0.85em; color: var(--text-color); margin-top: 15px; text-align: center;">
                        *Note: Purchases are simulated for frontend. Actual token delivery depends on backend payment confirmation.*
                    </p>
                </div>
            </section>

        </main>
    </div>

    <!-- Existing Top Up Modal (Fiat) - Include if you use it on this page -->
    <div id="sgcDepositModal" class="modal">
    <div class="modal-content">
        <span class="close-button" id="closeSgcDepositModal">&times;</span>
        <h2>Deposit SugalCoin (SGC)</h2>
        <p style="margin-bottom: 15px;">Your current wallet balance: <span id="modalSgcWalletBalance">Loading...</span></p>
        <div class="form-group">
            <label for="sgcDepositAmount">Amount of SGC to Deposit (from wallet to platform)</label>
            <input type="number" id="sgcDepositAmount" value="100.00" min="0.000000000000000001" step="0.000000000000000001">
        </div>
        <p style="font-size: 0.9em; color: #666; margin-bottom: 15px;">
            This will transfer SGC from your MetaMask wallet to your internal platform balance for betting.
        </p>
        <button id="confirmSgcDepositBtn">Confirm SGC Deposit</button>
        <p id="sgcModalMessage" class="modal-message"></p>
    </div>
</div>


    <!-- Web3.js library -->
    <script src="https://cdn.jsdelivr.net/npm/web3@1.7.0/dist/web3.min.js"></script>
    <!-- FIX: Your crypto-wallet.js script must be loaded BEFORE page-specific script -->
    <script src="crypto-wallet.js"></script>
    
    <!-- Page-specific JavaScript for purchase_sgc.php -->
    <script>
        // API base URL - Kept for handlePurchaseSGC if it makes API calls
        const API_BASE_URL = 'http://localhost/sugalhero-api'; // Adjust if your API folder name is different
        const PLATFORM_SGC_DEPOSIT_ADDRESS = '0xEa3B92DB904919A09Ff86E75C2Fa8699dd675149'; // <<< IMPORTANT: REPLACE WITH YOUR ACTUAL ADDRESS!

      
       
        // IMPORTANT: currentAccount is now accessible globally from crypto-wallet.js
        // No need for a separate currentConnectedMetamaskAccount here if crypto-wallet.js sets it.

        async function handlePurchaseSGC(event) {
            const amount = event.target.dataset.sgcAmount;
            const purchaseSGCMessage = document.getElementById('purchaseSGC_message');

            // Ensure MetaMask is connected and we have the user's address from crypto-wallet.js
            // 'currentAccount' is global due to crypto-wallet.js loading first.
            if (!window.ethereum || !currentAccount) { // Check if web3 provider exists and account is connected
                purchaseSGCMessage.textContent = "Error: Please connect your MetaMask wallet first.";
                purchaseSGCMessage.className = 'modal-message error';
                console.error('MetaMask not connected or web3 provider not found.');
                // Optionally, trigger connection attempt if on homepage, or suggest redirect for connection.
                return;
            }

            if (!amount) {
                purchaseSGCMessage.textContent = "Error: Amount not specified.";
                purchaseSGCMessage.className = 'modal-message error';
                return;
            }

            purchaseSGCMessage.textContent = `Processing purchase of ${amount} SGC...`;
            purchaseSGCMessage.className = 'modal-message info';
            console.log(`Attempting to initiate SGC purchase for ${amount} SGC to ${currentAccount}`);

            try {
                const response = await fetch(`${API_BASE_URL}/initiate_sgc_sale.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        // Ensure JWT is sent to authenticate the user's purchase request
                        'Authorization': `Bearer ${localStorage.getItem('jwt_token')}` 
                    },
                    body: JSON.stringify({
                        amountSGC: parseFloat(amount),
                        metamaskAddress: currentAccount // Send the user's connected MetaMask address
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    purchaseSGCMessage.textContent = `${data.message} It might take a moment for tokens to appear in your MetaMask wallet.`;
                    purchaseSGCMessage.className = 'modal-message success';
                    
                    // After successful "sale", prompt user to check their MetaMask balance
                    // The fetchTokenBalance() function from crypto-wallet.js will update the 'sugalCoinBalance' span
                    if (typeof fetchTokenBalance === 'function') {
                        fetchTokenBalance(); 
                    }

                    console.log('SGC Purchase successful:', data);

                } else {
                    purchaseSGCMessage.textContent = data.message || "SGC purchase failed. Please try again.";
                    purchaseSGCMessage.className = 'modal-message error';
                    console.error('SGC Purchase API Error:', data.message);
                    // Handle authentication errors (e.g., expired JWT)
                    if (response.status === 401) {
                        alert('Your session has expired. Please log in again.');
                        localStorage.removeItem('jwt_token');
                        localStorage.removeItem('user_id');
                        localStorage.removeItem('username');
                        window.location.href = '../authentication/login.php';
                    }
                }

            } catch (error) {
                purchaseSGCMessage.textContent = "Network error during SGC purchase. Please check your connection.";
                purchaseSGCMessage.className = 'modal-message error';
                console.error('SGC Purchase Network Error:', error);
            }
        }

        const depositSGCBtnHeader = document.getElementById('depositSGCBtnHeader');
        const sgcDepositModal = document.getElementById('sgcDepositModal');
        const closeSgcDepositModalBtn = document.getElementById('closeSgcDepositModal');
        const confirmSgcDepositBtn = document.getElementById('confirmSgcDepositBtn');
        const modalSgcWalletBalance = document.getElementById('modalSgcWalletBalance'); // New element

        if (depositSGCBtnHeader && sgcDepositModal) {
            depositSGCBtnHeader.addEventListener('click', async () => {
                if (!currentAccount) { // 'currentAccount' is from crypto-wallet.js
                    alert('Please connect your MetaMask wallet first to deposit SGC.');
                    return;
                }
                sgcDepositModal.style.display = 'flex';
                document.getElementById('sgcDepositAmount').value = '100.00'; // Default amount
                document.getElementById('sgcModalMessage').textContent = ''; // Clear messages

                // Display current MetaMask SGC balance in the modal
                if (typeof fetchTokenBalance === 'function') {
                    // Directly query the balance and display it in the modal
                    try {
                        const rawBalance = await sugalCoinContract.methods.balanceOf(currentAccount).call();
                        const formattedBalance = formatTokenBalance(BigInt(rawBalance), SUGALCOIN_DECIMALS, SUGALCOIN_SYMBOL);
                        modalSgcWalletBalance.textContent = formattedBalance;
                    } catch (error) {
                        console.error("Error fetching balance for modal:", error);
                        modalSgcWalletBalance.textContent = `Error fetching balance.`;
                    }
                }
            });
        }
        if (closeSgcDepositModalBtn) {
            closeSgcDepositModalBtn.addEventListener('click', () => {
                sgcDepositModal.style.display = 'none';
            });
        }
        if (sgcDepositModal) {
            window.addEventListener('click', (event) => {
                if (event.target == sgcDepositModal) {
                    sgcDepositModal.style.display = 'none';
                }
            });
        }
        if (confirmSgcDepositBtn) { confirmSgcDepositBtn.addEventListener('click', handleSgcDeposit); }

        // ... (rest of your DOMContentLoaded logic, like JWT checks, username display)

        // --- Logic for Fiat Top Up Modal on this page (if you keep it here) ---
        // This function would typically call a different API endpoint for fiat deposits.
        // For now, it remains a placeholder as per your previous setup.
        async function handleFiatDeposit() {
            const modalMessage = document.getElementById('modalMessage');
            modalMessage.textContent = "Fiat deposit not implemented on this page yet.";
            modalMessage.className = 'modal-message info';
            console.warn("Fiat deposit clicked on SGC purchase page - logic missing.");
        }


        document.addEventListener('DOMContentLoaded', async () => {
            // User dropdown and logout logic
            const userDropdownToggle = document.getElementById('userDropdownToggle');
            const myDropdown = document.getElementById('myDropdown');
            const logoutBtn = document.getElementById('logoutBtn');
            
            if (userDropdownToggle && myDropdown) {
                userDropdownToggle.addEventListener('click', () => {
                    myDropdown.classList.toggle('show');
                });
                window.onclick = function(event) {
                    if (!event.target.matches('.dropdown-toggle') && !event.target.closest('.dropdown-toggle')) {
                        if (myDropdown.classList.contains('show')) {
                            myDropdown.classList.remove('show');
                        }
                    }
                };
            }

            if (logoutBtn) {
                logoutBtn.addEventListener('click', () => {
                    localStorage.removeItem('jwt_token');
                    localStorage.removeItem('user_id');
                    localStorage.removeItem('username');
                    // You might also want to disconnect MetaMask here if it makes sense for your app
                    // if (window.ethereum && currentAccount) {
                    //     // This depends on how you want to handle MetaMask disconnect on logout
                    //     // web3.currentProvider.disconnect(); // Not standard, but some providers have it
                    // }
                    window.location.href = '../authentication/login.php';
                });
            }

            // Fiat Top Up Modal setup
            const topUpBtn = document.getElementById('topUpBtn'); 
            const topUpModal = document.getElementById('topUpModal'); 
            const closeTopUpModalBtn = document.getElementById('closeTopUpModal');
            const confirmDepositBtn = document.getElementById('confirmDepositBtn');

            if (topUpBtn && topUpModal) {
                topUpBtn.addEventListener('click', () => {
                    topUpModal.style.display = 'flex';
                    document.getElementById('modalDepositAmount').value = '50.00';
                    document.getElementById('modalMessage').textContent = ''; 
                });
            }
            if(closeTopUpModalBtn) {
                closeTopUpModalBtn.addEventListener('click', () => {
                    topUpModal.style.display = 'none';
                });
            }
            if(topUpModal) {
                window.addEventListener('click', (event) => {
                    if (event.target == topUpModal) {
                        topUpModal.style.display = 'none';
                    }
                });
            }
            if(confirmDepositBtn) { confirmDepositBtn.addEventListener('click', handleFiatDeposit); }


            // --- Add event listeners for the new purchase buttons ---
            const buySGCButtons = document.querySelectorAll('.buy-sgc-btn');
            buySGCButtons.forEach(button => {
                button.addEventListener('click', handlePurchaseSGC);
            });

            // Basic Session Check for this page - now simplified
            const jwtToken = localStorage.getItem('jwt_token');
            const username = localStorage.getItem('username'); // Get username for display
            const userId = localStorage.getItem('user_id'); // Get userId if needed for API calls

            if (!jwtToken || !username || !userId) { // Check all necessary login credentials
                window.location.href = '../authentication/login.php'; 
                return; // Stop further script execution if not authenticated
            } else {
                // If logged in, update username and fiat balance
                // currentUserToken = jwtToken; // This variable is not used in this specific script now.
                                              // JWT is passed directly in fetch calls.
                document.getElementById('headerUsername').textContent = username; // Update username display
                
                // Fetch the on-chain SGC balance and display it.
                // This assumes initializeWeb3() and updateWalletUI() in crypto-wallet.js
                // will eventually call fetchTokenBalance() based on connection status.
                // If you also have a separate "fiat balance" for the header dropdown,
                // you would need another API call here similar to user_profile.php.
                // For now, I'm assuming headerBalanceDisplay is optional or will reflect SGC balance.
                // If userBalanceDisplay is for fiat, it needs its own fetch.
                // For now, assuming it's for internal balance which isn't available here directly.
                // You might need to add a fetch call for internal balance if you want to display it here.
                // For now, let's leave userBalanceDisplay as is or remove it if not used for SGC.
                // document.getElementById('userBalanceDisplay').textContent = "Balance: N/A"; // Or fetch from internal API
            }
        });

        async function handleSgcDeposit() {
    const amountInput = document.getElementById('sgcDepositAmount');
    const amount = parseFloat(amountInput.value);
    const sgcModalMessage = document.getElementById('sgcModalMessage');

    if (isNaN(amount) || amount <= 0) {
        sgcModalMessage.textContent = "Please enter a valid positive amount.";
        sgcModalMessage.className = 'modal-message error';
        return;
    }

    // Ensure MetaMask is connected and we have the user's address and contract instance
    // 'currentAccount', 'web3', 'sugalCoinContract' are globals from crypto-wallet.js
    if (!currentAccount || !web3 || !sugalCoinContract) {
        sgcModalMessage.textContent = "MetaMask not connected or Web3 not initialized. Please connect your wallet.";
        sgcModalMessage.className = 'modal-message error';
        console.error('Web3/MetaMask not ready for deposit.');
        return;
    }

    sgcModalMessage.textContent = `Initiating deposit of ${amount} SGC from your wallet... Please confirm in MetaMask.`;
    sgcModalMessage.className = 'modal-message info';

    try {
        // Convert human-readable amount to BigInt for contract interaction (wei-like units)
        const amountInWei = BigInt(Math.floor(amount * (10 ** SUGALCOIN_DECIMALS)));

        // Initiate the token transfer from the user's wallet to the platform's deposit address
        // This is the actual MetaMask transaction for the user to send SGC to your platform.
        const transactionParameters = {
            from: currentAccount, // User's connected MetaMask account
            to: SUGALCOIN_CONTRACT_ADDRESS, // The SGC token contract address
            data: sugalCoinContract.methods.transfer(PLATFORM_SGC_DEPOSIT_ADDRESS, amountInWei).encodeABI()
        };

        const txHash = await window.ethereum.request({
            method: 'eth_sendTransaction',
            params: [transactionParameters],
        });

        sgcModalMessage.textContent = `Deposit transaction sent! Waiting for server confirmation... Tx Hash: ${formatAddress(txHash)}`;
        sgcModalMessage.className = 'modal-message info';
        console.log('MetaMask transaction sent:', txHash);

        // Now, send this transaction hash and details to your backend API for verification and database update
        const response = await fetch(`${API_BASE_URL}/sgc_deposit.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('jwt_token')}`
            },
            body: JSON.stringify({
                amount: amount, // Send human-readable amount
                txHash: txHash,
                fromAddress: currentAccount // User's address
            })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            sgcModalMessage.textContent = `${data.message} Your internal balance will update shortly.`;
            sgcModalMessage.className = 'modal-message success';
            amountInput.value = '100.00'; // Reset input

            // Refresh on-chain balance displayed in header from crypto-wallet.js
            if (typeof fetchTokenBalance === 'function') {
                fetchTokenBalance(); // This fetches on-chain balance
            }

            // Close modal after a short delay or when user clicks OK
            setTimeout(() => { sgcDepositModal.style.display = 'none'; }, 2000);

        } else {
            sgcModalMessage.textContent = data.message || "Deposit verification failed on server. Please contact support.";
            sgcModalMessage.className = 'modal-message error';
            console.error('SGC Deposit API Error:', data.message);
            if (response.status === 401) {
                alert('Your session has expired. Please log in again.');
                localStorage.removeItem('jwt_token');
                localStorage.removeItem('user_id');
                localStorage.removeItem('username');
                window.location.href = '../authentication/login.php';
            }
        }

    } catch (error) {
        console.error('MetaMask / Network error during SGC deposit:', error);
        if (error.code === 4001) { // User rejected transaction in MetaMask
            sgcModalMessage.textContent = "MetaMask transaction rejected by user.";
            sgcModalMessage.className = 'modal-message error';
        } else {
            sgcModalMessage.textContent = "An error occurred during deposit. Please try again.";
            sgcModalMessage.className = 'modal-message error';
        }
    }
}
    </script>
</body>
</html>