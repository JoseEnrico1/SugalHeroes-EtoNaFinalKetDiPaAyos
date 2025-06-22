<?php
// homepage/index.php

// All PHP logic for sessions and database access has been removed and
// replaced by JavaScript and API calls for authentication and data.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Home - SUGALHEROES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="homepage.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../images/newlogo.png" alt="Your Brand Logo" id="sidebarLogo">
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="#home" class="nav-item active"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="#games-section" class="nav-item"><i class="fas fa-gamepad"></i> Games</a></li>
                    <li><a href="../dashboard/user_dashboard.php" class="nav-item"><i class="fas fa-clipboard-list"></i> Dashboard</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <div class="left">

                </div>
                <div class="crypto-wallet-display">
                    <span id="sugalCoinBalance">0 SGC</span> 
                </div>
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
            </header>

            <section class="dashboard-widget hero" id="home">
                <div class="hero-content-wrapper">
                    <h1>SUGALHEROES</h1>
                    <div class="slideshow-container">
                        <div class="mySlides fade">
                            <img src="../images/slide1.png" style="width:100%">
                            <a href="../authentication/signup.php" class="slide-button" id="heroRegisterBtn1">REGISTER NOW</a>
                        </div>
                        <div class="mySlides fade">
                            <img src="../images/AldenDila.jpg" style="width:100%">
                            <a href="../authentication/signup.php" class="slide-button" id="heroRegisterBtn2">PLAY MORE</a>
                        </div>
                        <div class="mySlides fade">
                            <img src="../images/sugalheroesbanner2.png" style="width:100%">
                            <a href="../authentication/signup.php" class="slide-button" id="heroRegisterBtn3">JOIN US</a>
                        </div>

                        <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
                        <a class="next" onclick="plusSlides(1)">&#10095;</a>
                    </div>
                </div>
            </section>

            <section class="dashboard-widget games" id="games-section">
                <div class="header">
                    <h2>GAMES</h2>
                    <a href="#" class="see-all">SEE ALL</a>
                </div>
                <div class="grid">
                    
                    <a href="../games/plinko/plinko.html" class="card">
                        <img src="../images/plinko.png" alt="Plinko">
                        <div class="title">Plinko</div>
                    </a>
                    <a href="../games/karakrus/karakrus.html" class="card">
                        <img src="../images/KaraKrusBanner.png" alt="Kara Krus">
                        <div class="title">Kara Krus</div>
                    </a>
                    
                    <a href="../games/falling_tokens/falling_tokens.html" class="card">
                        <img src="../images/fallingtokensbanner.png" alt="Falling Tokens"> <div class="title">Falling Tokens</div>
                    </a>
                </div>
            </section>
        </main>
    </div>

    <div id="topUpModal" class="modal">
        <div class="modal-content">
            <span class="close-button" id="closeTopUpModal">&times;</span>
            <h2>Deposit Funds</h2>
            <div class="form-group">
                <label for="modalDepositAmount">Amount to Deposit ($)</label>
                <input type="number" id="modalDepositAmount" value="50.00" min="0.01" step="0.01">
            </div>
            <button id="confirmDepositBtn">Confirm Deposit</button>
            <p id="modalMessage" class="modal-message"></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/web3@1.7.0/dist/web3.min.js"></script>
    <script>
        // API base URL
        const API_BASE_URL = 'http://localhost/sugalhero-api'; // Adjust if your API folder name is different

        // --- Global variables for API access ---
        let currentUserToken = null;

        // --- Helper function to update balance display ---
        async function updateBalanceDisplay() {
            const balanceDisplays = document.querySelectorAll('#userBalanceDisplay'); // Select the correct ID
            if (!balanceDisplays.length) {
                console.error("Balance display element not found on homepage!");
                return;
            }
            if (!currentUserToken) {
                balanceDisplays.forEach(el => el.textContent = 'Balance: $N/A');
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
                    window.location.href = '../authentication/login.php';
                    return;
                }

                const data = await response.json();
                if (data.success) {
                    const balance = parseFloat(data.balance).toFixed(2);
                    balanceDisplays.forEach(el => el.textContent = `Balance: $${balance}`);
                } else {
                    console.error('API Error fetching balance:', data.message);
                    if (data.message.includes('Unauthorized') || data.message.includes('token')) {
                        localStorage.removeItem('jwt_token');
                        localStorage.removeItem('user_id');
                        localStorage.removeItem('username');
                        window.location.href = '../authentication/login.php';
                    }
                }
            } catch (error) {
                console.error('Network or Fetch Error fetching balance:', error);
                localStorage.removeItem('jwt_token');
                localStorage.removeItem('user_id');
                localStorage.removeItem('username');
                window.location.href = '../authentication/login.php';
            }
        }

        // --- NEW: Function to handle deposit from modal ---
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
                    amountInput.value = '50.00'; // Reset input value for next deposit
                    await updateBalanceDisplay(); // Refresh balance on homepage
                } else {
                    modalMessage.textContent = data.message || "Deposit failed. Please try again.";
                    modalMessage.className = 'modal-message error';
                    if (response.status === 401) { // Token issue
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


        // --- Slideshow functionality ---
        let slideIndex = 1;
        let slideshowInterval; // To store the interval ID for auto-advance

        function showSlides(n) {
            let i;
            let slides = document.getElementsByClassName("mySlides");
            if (slides.length === 0) return; // Exit if no slides are found

            if (n > slides.length) { slideIndex = 1 }
            if (n < 1) { slideIndex = slides.length }
            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            slides[slideIndex - 1].style.display = "block";
        }

        function plusSlides(n) {
            clearInterval(slideshowInterval); // Stop auto-advance when manual navigation occurs
            showSlides(slideIndex += n);
            startSlideshow(); // Restart auto-advance after a brief delay
        }

        function startSlideshow() {
            // Clear any existing interval to prevent multiple intervals running
            if (slideshowInterval) {
                clearInterval(slideshowInterval);
            }
            slideshowInterval = setInterval(() => {
                plusSlides(1); // Advance to the next slide
            }, 5000); // Change image every 5 seconds
        }


        // --- Page Protection & Initial Data Load ---
        document.addEventListener('DOMContentLoaded', async () => {
            const jwtToken = localStorage.getItem('jwt_token');
            const username = localStorage.getItem('username');
            const userId = localStorage.getItem('user_id');

            const userDropdownToggle = document.getElementById('userDropdownToggle');
            const myDropdown = document.getElementById('myDropdown');
            const logoutBtn = document.getElementById('logoutBtn');
            const topUpBtn = document.getElementById('topUpBtn'); // Get the Top Up button
            const gotoDashboardBtn = document.getElementById('gotoDashboardBtn');

            if (jwtToken && username && userId) {
                currentUserToken = jwtToken;

                document.getElementById('headerUsername').textContent = username;
                await updateBalanceDisplay();

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
                        window.location.href = '../authentication/login.php';
                    });
                }

                // --- NEW: Event listener for Top Up button on homepage to OPEN MODAL ---
                if (topUpBtn) {
                    topUpBtn.addEventListener('click', () => {
                        document.getElementById('topUpModal').style.display = 'flex'; // Show modal
                        document.getElementById('modalDepositAmount').value = '50.00'; // Reset amount
                        document.getElementById('modalMessage').textContent = ''; // Clear previous messages
                    });
                }

                // --- NEW: Event listener for Close Modal button ---
                const closeTopUpModalBtn = document.getElementById('closeTopUpModal');
                if(closeTopUpModalBtn) {
                    closeTopUpModalBtn.addEventListener('click', () => {
                        document.getElementById('topUpModal').style.display = 'none'; // Hide modal
                    });
                }

                // Hide modal if user clicks outside of modal content
                const topUpModal = document.getElementById('topUpModal');
                if(topUpModal) {
                    window.addEventListener('click', (event) => {
                        if (event.target == topUpModal) {
                            topUpModal.style.display = 'none';
                        }
                    });
                }

                // --- NEW: Event listener for Confirm Deposit button inside modal ---
                const confirmDepositBtn = document.getElementById('confirmDepositBtn');
                if(confirmDepositBtn) {
                    confirmDepositBtn.addEventListener('click', handleDeposit);
                }


                if (gotoDashboardBtn) {
                    gotoDashboardBtn.addEventListener('click', () => {
                        window.location.href = '../user_dashboard.php';
                    });
                }

                const slideButtons = document.querySelectorAll('.slide-button');
                slideButtons.forEach(button => {
                    button.style.display = 'none';
                });


            } else {
                window.location.href = '../authentication/login.php';
                return;
            }

            // Initialize slideshow only after the DOM is loaded
            showSlides(slideIndex);
            startSlideshow();


            // Adjusted JS for sidebar indicator to work with a fixed sidebar width
            const sidebar = document.querySelector('.sidebar');
            const menuLinks = document.querySelectorAll('.main-nav a'); // Target anchor tags directly

            if (sidebar && menuLinks.length > 0) {
                // Create and append the indicator dynamically if it doesn't exist
                let indicator = sidebar.querySelector('.indicator');
                if (!indicator) {
                    indicator = document.createElement('div');
                    indicator.classList.add('indicator');
                    sidebar.appendChild(indicator);
                }

                // Function to set indicator position
                const setIndicatorPosition = (element) => {
                    if (element) {
                        const rect = element.getBoundingClientRect();
                        const sidebarRect = sidebar.getBoundingClientRect();
                        indicator.style.top = (rect.top - sidebarRect.top) + 'px';
                        indicator.style.height = rect.height + 'px'; // Make indicator height match link height
                        indicator.style.opacity = '1'; // Make it visible
                    } else {
                        indicator.style.opacity = '0'; // Hide if no element is active
                    }
                };

                // Find active link on load and set indicator
                const activeLink = document.querySelector('.main-nav a.active');
                setIndicatorPosition(activeLink);

                // Add event listeners for mouseenter/mouseleave
                menuLinks.forEach(link => {
                    link.addEventListener('mouseenter', () => {
                        setIndicatorPosition(link);
                    });
                });

                sidebar.addEventListener('mouseleave', () => {
                    const currentActiveLink = document.querySelector('.main-nav a.active');
                    if (currentActiveLink) {
                        setIndicatorPosition(currentActiveLink); // Return to active link
                    } else {
                        indicator.style.opacity = '0'; // Hide if no active link
                    }
                });

                // Handle click to set active class and position indicator
                menuLinks.forEach(link => {
                    link.addEventListener('click', (event) => {
                        menuLinks.forEach(item => item.classList.remove('active')); // Remove active from all
                        link.classList.add('active'); // Add active to clicked
                        setIndicatorPosition(link);
                    });
                });
            }
        });
    </script>
    <script src="crypto-wallet.js"></script>

</body>
</html>