// homepage/crypto-wallet.js

// --- Configuration for your SugalCoin Token ---
const SUGALCOIN_CONTRACT_ADDRESS = '0xA14BD2ba7E888eaC70F815F24081e453befEf634'; // Your deployed SugalCoin contract address on Sepolia
const SUGALCOIN_DECIMALS = 18; // Your token's decimals
const SUGALCOIN_SYMBOL = 'SGC'; // Your token's symbol (assuming SGC for SugalCoin)

const SUGALCOIN_ABI = [
	{
		"inputs": [
			{
				"internalType": "string",
				"name": "name_",
				"type": "string"
			},
			{
				"internalType": "string",
				"name": "symbol_",
				"type": "string"
			}
		],
		"stateMutability": "nonpayable",
		"type": "constructor"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "spender",
				"type": "address"
			},
			{
				"internalType": "uint256",
				"name": "allowance",
				"type": "uint256"
			},
			{
				"internalType": "uint256",
				"name": "needed",
				"type": "uint256"
			}
		],
		"name": "ERC20InsufficientAllowance",
		"type": "error"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "sender",
				"type": "address"
			},
			{
				"internalType": "uint256",
				"name": "balance",
				"type": "uint256"
			},
			{
				"internalType": "uint256",
				"name": "needed",
				"type": "uint256"
			}
		],
		"name": "ERC20InsufficientBalance",
		"type": "error"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "approver",
				"type": "address"
			}
		],
		"name": "ERC20InvalidApprover",
		"type": "error"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "receiver",
				"type": "address"
			}
		],
		"name": "ERC20InvalidReceiver",
		"type": "error"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "sender",
				"type": "address"
			}
		],
		"name": "ERC20InvalidSender",
		"type": "error"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "spender",
				"type": "address"
			}
		],
		"name": "ERC20InvalidSpender",
		"type": "error"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "owner",
				"type": "address"
			}
		],
		"name": "OwnableInvalidOwner",
		"type": "error"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "account",
				"type": "address"
			}
		],
		"name": "OwnableUnauthorizedAccount",
		"type": "error"
	},
	{
		"anonymous": false,
		"inputs": [
			{
				"indexed": true,
				"internalType": "address",
				"name": "owner",
				"type": "address"
			},
			{
				"indexed": true,
				"internalType": "address",
				"name": "spender",
				"type": "address"
			},
			{
				"indexed": false,
				"internalType": "uint256",
				"name": "value",
				"type": "uint256"
			}
		],
		"name": "Approval",
		"type": "event"
	},
	{
		"anonymous": false,
		"inputs": [
			{
				"indexed": true,
				"internalType": "address",
				"name": "previousOwner",
				"type": "address"
			},
			{
				"indexed": true,
				"internalType": "address",
				"name": "newOwner",
				"type": "address"
			}
		],
		"name": "OwnershipTransferred",
		"type": "event"
	},
	{
		"anonymous": false,
		"inputs": [
			{
				"indexed": true,
				"internalType": "address",
				"name": "from",
				"type": "address"
			},
			{
				"indexed": true,
				"internalType": "address",
				"name": "to",
				"type": "address"
			},
			{
				"indexed": false,
				"internalType": "uint256",
				"name": "value",
				"type": "uint256"
			}
		],
		"name": "Transfer",
		"type": "event"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "owner",
				"type": "address"
			},
			{
				"internalType": "address",
				"name": "spender",
				"type": "address"
			}
		],
		"name": "allowance",
		"outputs": [
			{
				"internalType": "uint256",
				"name": "",
				"type": "uint256"
			}
		],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "spender",
				"type": "address"
			},
			{
				"internalType": "uint256",
				"name": "value",
				"type": "uint256"
			}
		],
		"name": "approve",
		"outputs": [
			{
				"internalType": "bool",
				"name": "",
				"type": "bool"
			}
		],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "account",
				"type": "address"
			}
		],
		"name": "balanceOf",
		"outputs": [
			{
				"internalType": "uint256",
				"name": "",
				"type": "uint256"
			}
		],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "uint256",
				"name": "amount",
				"type": "uint256"
			}
		],
		"name": "burn",
		"outputs": [],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "decimals",
		"outputs": [
			{
				"internalType": "uint8",
				"name": "",
				"type": "uint8"
			}
		],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "name",
		"outputs": [
			{
				"internalType": "string",
				"name": "",
				"type": "string"
			}
		],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "owner",
		"outputs": [
			{
				"internalType": "address",
				"name": "",
				"type": "address"
			}
		],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "renounceOwnership",
		"outputs": [],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "symbol",
		"outputs": [
			{
				"internalType": "string",
				"name": "",
				"type": "string"
			}
		],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "totalSupply",
		"outputs": [
			{
				"internalType": "uint256",
				"name": "",
				"type": "uint256"
			}
		],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "to",
				"type": "address"
			},
			{
				"internalType": "uint256",
				"name": "value",
				"type": "uint256"
			}
		],
		"name": "transfer",
		"outputs": [
			{
				"internalType": "bool",
				"name": "",
				"type": "bool"
			}
		],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "from",
				"type": "address"
			},
			{
				"internalType": "address",
				"name": "to",
				"type": "address"
			},
			{
				"internalType": "uint256",
				"name": "value",
				"type": "uint256"
			}
		],
		"name": "transferFrom",
		"outputs": [
			{
				"internalType": "bool",
				"name": "",
				"type": "bool"
			}
		],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "newOwner",
				"type": "address"
			}
		],
		"name": "transferOwnership",
		"outputs": [],
		"stateMutability": "nonpayable",
		"type": "function"
	}
];


// --- Helper Functions ---

// Function to format address for display (e.g., 0x123...abc)
function formatAddress(address) {
    if (!address) return 'N/A';
    return `${address.substring(0, 6)}...${address.substring(address.length - 4)}`;
}

// Function to convert raw token balance (BigInt) to human-readable format
function formatTokenBalance(rawBalance, decimals, symbol) {
    if (!rawBalance) return `0 ${symbol}`;
    // Convert BigInt to string to handle large numbers before Decimal conversion
    const balanceString = rawBalance.toString();
    const divisor = BigInt(10) ** BigInt(decimals);
    const integerPart = balanceString.slice(0, -decimals) || '0';
    const decimalPart = balanceString.slice(-decimals).padStart(decimals, '0').substring(0, 4); // Show 4 decimal places
    return `${integerPart}.${decimalPart} ${symbol}`;
}

// --- Main Web3 Functions ---

let web3; // Global Web3 instance
let sugalCoinContract; // Global contract instance
let currentAccount = null;
let sugalCoinBalanceSpan; // Reference to the HTML span

// Function to redirect to the purchase page
function redirectToPurchasePage() {
    window.location.href = 'purchase_sgc.php'; // Path to your new purchase page
}

// Initialize Web3 and connect to contract
async function initializeWeb3() {
    sugalCoinBalanceSpan = document.getElementById('sugalCoinBalance'); // Get reference on init

    if (window.ethereum) {
        web3 = new Web3(window.ethereum);
        sugalCoinContract = new web3.eth.Contract(SUGALCOIN_ABI, SUGALCOIN_CONTRACT_ADDRESS);
        console.log("Web3 and contract initialized.");

        // Listen for account changes
        window.ethereum.on('accountsChanged', handleAccountsChanged);
        // Listen for network changes
        window.ethereum.on('chainChanged', handleChainChanged);

        // Conditional click handler for the balance span based on page
        if (window.location.pathname.includes('index.php') || window.location.pathname === '/') {
            sugalCoinBalanceSpan.addEventListener('click', handleSugalCoinBalanceClick);
        } else {
            // On purchase_sgc.php, make it a non-clickable display
            sugalCoinBalanceSpan.style.cursor = 'default';
            sugalCoinBalanceSpan.onclick = null; // Ensure no click behavior
        }

        // Check current connection status on load
        const accounts = await web3.eth.getAccounts();
        if (accounts.length > 0) {
            currentAccount = accounts[0];
            await updateWalletUI('connected');
            await handleChainChanged(window.ethereum.chainId); // Check network immediately
        } else {
            updateWalletUI('disconnected'); // Set initial state
        }

    } else {
        console.error("MetaMask is not installed. Please install it to use this feature.");
        sugalCoinBalanceSpan.textContent = `Install MetaMask`;
        sugalCoinBalanceSpan.setAttribute('data-status', 'install');
        sugalCoinBalanceSpan.style.cursor = 'pointer';
        sugalCoinBalanceSpan.onclick = () => window.open('https://metamask.io/download/', '_blank');
    }
}

// Centralized click handler for the SugalCoin balance span (ONLY on index.php)
async function handleSugalCoinBalanceClick() {
    // This function will only be attached if on index.php
    if (!window.ethereum) { // MetaMask not installed
        // This behavior is handled by initializeWeb3() now
        return; 
    }

    if (!currentAccount) { // Not connected, try to connect
        await connectWallet(false);
    } else { // Connected, check network or redirect
        const sepoliaChainId = 11155111;
        const currentChainIdNum = web3.utils.hexToNumber(window.ethereum.chainId);

        if (currentChainIdNum !== sepoliaChainId) {
            // Wrong network, try to switch
            try {
                await window.ethereum.request({
                    method: 'wallet_switchEthereumChain',
                    params: [{ chainId: web3.utils.numberToHex(sepoliaChainId) }],
                });
            } catch (switchError) {
                console.error("Error switching network:", switchError);
                // Error handling is already in handleChainChanged
            }
        } else {
            // Wallet connected AND on Sepolia, so redirect to purchase page
            redirectToPurchasePage();
        }
    }
}


// Handle account changes
async function handleAccountsChanged(accounts) {
    if (accounts.length === 0) {
        console.log('Please connect to MetaMask.');
        currentAccount = null;
        updateWalletUI('disconnected');
    } else if (accounts[0] !== currentAccount) {
        currentAccount = accounts[0];
        console.log(`Account changed to: ${currentAccount}`);
        await updateWalletUI('connected');
        await fetchTokenBalance();
    }
}

// Handle chain changes
async function handleChainChanged(chainId) {
    console.log(`Chain changed to: ${chainId}`);
    const sepoliaChainId = 11155111; // Sepolia's chain ID
    const currentChainIdNum = web3.utils.hexToNumber(chainId);

    if (currentChainIdNum !== sepoliaChainId) {
        sugalCoinBalanceSpan.textContent = `Wrong Network!`;
        sugalCoinBalanceSpan.setAttribute('data-status', 'wrong-network');
        
        // If on index.php, keep it clickable to try to switch.
        // If on purchase_sgc.php, it's a display only.
        if (window.location.pathname.includes('index.php') || window.location.pathname === '/') {
            sugalCoinBalanceSpan.style.cursor = 'pointer'; 
            sugalCoinBalanceSpan.onclick = async () => {
                try {
                    await window.ethereum.request({
                        method: 'wallet_switchEthereumChain',
                        params: [{ chainId: web3.utils.numberToHex(sepoliaChainId) }],
                    });
                } catch (switchError) {
                    console.error("Error switching network:", switchError);
                    if (switchError.code === 4902) {
                        sugalCoinBalanceSpan.textContent = `Network Not Added!`;
                        sugalCoinBalanceSpan.setAttribute('data-status', 'error');
                    } else if (switchError.code === 4001) {
                        sugalCoinBalanceSpan.textContent = `Switch Rejected`;
                        sugalCoinBalanceSpan.setAttribute('data-status', 'wrong-network');
                    }
                }
            };
        } else {
             sugalCoinBalanceSpan.style.cursor = 'default'; // On purchase page, not clickable
             sugalCoinBalanceSpan.onclick = null;
        }
        currentAccount = null; // Mark account as invalid for current network
    } else {
        sugalCoinBalanceSpan.removeAttribute('data-status');
        
        // If on index.php, make it clickable to purchase page.
        // If on purchase_sgc.php, it's a non-clickable display.
        if (window.location.pathname.includes('index.php') || window.location.pathname === '/') {
            sugalCoinBalanceSpan.style.cursor = 'pointer'; 
            sugalCoinBalanceSpan.onclick = redirectToPurchasePage;
        } else {
            sugalCoinBalanceSpan.style.cursor = 'default';
            sugalCoinBalanceSpan.onclick = null;
        }

        if (currentAccount) {
            await fetchTokenBalance();
        } else {
            updateWalletUI('disconnected');
        }
    }
}

// Connect to MetaMask wallet
async function connectWallet(isAutoConnect = false) {
    try {
        if (!web3) {
            console.error("Web3 not initialized. MetaMask might not be detected.");
            sugalCoinBalanceSpan.textContent = `Web3 Error!`;
            sugalCoinBalanceSpan.setAttribute('data-status', 'error');
            return;
        }

        const accounts = await window.ethereum.request({ method: 'eth_requestAccounts' });
        if (accounts.length > 0) {
            currentAccount = accounts[0];
            console.log(`Wallet connected: ${currentAccount}`);
            await updateWalletUI('connected');
            await handleChainChanged(window.ethereum.chainId); // Check network immediately after connection
        } else {
            console.log('No accounts found.');
            if (!isAutoConnect) { 
                updateWalletUI('disconnected');
            }
        }
    } catch (error) {
        console.error("Error connecting wallet:", error);
        if (error.code === 4001) { // User rejected connection
            sugalCoinBalanceSpan.textContent = `Connection Rejected`;
            sugalCoinBalanceSpan.setAttribute('data-status', 'error');
            // If on index.php, keep it clickable to retry connection.
            // If on purchase_sgc.php, it's a display only.
            if (window.location.pathname.includes('index.php') || window.location.pathname === '/') {
                sugalCoinBalanceSpan.style.cursor = 'pointer'; 
                sugalCoinBalanceSpan.onclick = () => connectWallet(false);
            } else {
                sugalCoinBalanceSpan.style.cursor = 'default';
                sugalCoinBalanceSpan.onclick = null;
            }
        } else {
            sugalCoinBalanceSpan.textContent = `Connection Error!`;
            sugalCoinBalanceSpan.setAttribute('data-status', 'error');
            sugalCoinBalanceSpan.style.cursor = 'default'; // General error, not clickable
            sugalCoinBalanceSpan.onclick = null;
        }
    }
}

// Fetch and display SugalCoin balance
async function fetchTokenBalance() {
    if (!currentAccount || !sugalCoinContract) {
        sugalCoinBalanceSpan.textContent = `0 ${SUGALCOIN_SYMBOL}`;
        sugalCoinBalanceSpan.setAttribute('data-status', 'disconnected');
        return;
    }

    try {
        const rawBalance = await sugalCoinContract.methods.balanceOf(currentAccount).call();
        const formattedBalance = formatTokenBalance(BigInt(rawBalance), SUGALCOIN_DECIMALS, SUGALCOIN_SYMBOL);
        sugalCoinBalanceSpan.textContent = formattedBalance;
        sugalCoinBalanceSpan.removeAttribute('data-status'); // Clear status once balance is shown
        
        // If on index.php, make it clickable to purchase page.
        // If on purchase_sgc.php, it's a non-clickable display.
        if (window.location.pathname.includes('index.php') || window.location.pathname === '/') {
            sugalCoinBalanceSpan.style.cursor = 'pointer'; 
            sugalCoinBalanceSpan.onclick = redirectToPurchasePage; 
        } else {
            sugalCoinBalanceSpan.style.cursor = 'default';
            sugalCoinBalanceSpan.onclick = null;
        }
        console.log(`SugalCoin balance for ${currentAccount}: ${formattedBalance}`);
    } catch (error) {
        console.error("Error fetching SugalCoin balance:", error);
        sugalCoinBalanceSpan.textContent = `Error ${SUGALCOIN_SYMBOL}`;
        sugalCoinBalanceSpan.setAttribute('data-status', 'error');
        sugalCoinBalanceSpan.style.cursor = 'default'; // Error state on purchase page, not clickable
        sugalCoinBalanceSpan.onclick = null;
    }
}

// Update UI based on wallet status
async function updateWalletUI(status) {
    if (status === 'connected' && currentAccount) {
        sugalCoinBalanceSpan.textContent = `Fetching ${SUGALCOIN_SYMBOL}...`; // Show loading state
        sugalCoinBalanceSpan.removeAttribute('data-status'); // Clear any previous status
        sugalCoinBalanceSpan.style.cursor = 'default'; // Temporarily default cursor
        sugalCoinBalanceSpan.onclick = null; // Temporarily remove click handler
    } else { // Disconnected state
        sugalCoinBalanceSpan.textContent = `Connect Wallet (${SUGALCOIN_SYMBOL})`; // Text when disconnected
        sugalCoinBalanceSpan.setAttribute('data-status', 'disconnected'); // Set data-status for initial state
        
        // Only make clickable for connection if on index.php
        if (window.location.pathname.includes('index.php') || window.location.pathname === '/') {
            sugalCoinBalanceSpan.style.cursor = 'pointer';
            sugalCoinBalanceSpan.onclick = handleSugalCoinBalanceClick;
        } else {
            sugalCoinBalanceSpan.style.cursor = 'default';
            sugalCoinBalanceSpan.onclick = null;
        }
    }
}

// --- Event Listeners and Initial Load ---
document.addEventListener('DOMContentLoaded', async () => {
    sugalCoinBalanceSpan = document.getElementById('sugalCoinBalance'); // Ensure it's available early
    if (typeof Web3 !== 'undefined') {
        initializeWeb3();
    } else {
        console.error("Web3.js library not found. Make sure it's loaded before crypto-wallet.js.");
        sugalCoinBalanceSpan.textContent = 'Web3.js Error!';
        sugalCoinBalanceSpan.setAttribute('data-status', 'error');
        sugalCoinBalanceSpan.style.cursor = 'default'; // Not clickable if Web3.js is missing
    }
});