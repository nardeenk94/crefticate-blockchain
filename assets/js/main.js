// Main JavaScript File

// Mobile Navigation Toggle
document.addEventListener('DOMContentLoaded', function() {
    const navbarToggle = document.getElementById('navbarToggle');
    const navbarMenu = document.getElementById('navbarMenu');
    
    if (navbarToggle && navbarMenu) {
        navbarToggle.addEventListener('click', function() {
            navbarMenu.classList.toggle('active');
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        if (navbarMenu && navbarToggle) {
            if (!navbarMenu.contains(event.target) && !navbarToggle.contains(event.target)) {
                navbarMenu.classList.remove('active');
            }
        }
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Add custom validation here if needed
        });
    });
    
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});

// Certificate Verification API Call
async function verifyCertificateAPI(certificateId) {
    try {
        const response = await fetch(`/api/verify-certificate.php?certificate_id=${certificateId}`);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error verifying certificate:', error);
        return null;
    }
}

// Get All Certificates API Call
async function getCertificatesAPI() {
    try {
        const response = await fetch('/api/get-certificates.php');
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error getting certificates:', error);
        return null;
    }
}

// Copy to Clipboard Function
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}

// Print Function
function printCertificate() {
    window.print();
}

// Share Function
function shareCertificate(certificateId) {
    const url = `${window.location.origin}/verify.php?id=${certificateId}`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Certificate Verification',
            text: 'Verify this certificate',
            url: url
        }).catch(err => console.error('Error sharing:', err));
    } else {
        copyToClipboard(url);
    }
}

// File Upload Preview
function previewFile(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        
        const preview = document.getElementById('filePreview');
        if (preview) {
            reader.onload = function(e) {
                if (file.type.startsWith('image/')) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 300px;">`;
                } else {
                    preview.innerHTML = `<p>File selected: ${file.name}</p>`;
                }
            };
            reader.readAsDataURL(file);
        }
    }
}

// Form Reset
function resetForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
    }
}

// Confirm Action
function confirmAction(message) {
    return confirm(message || 'Are you sure you want to proceed?');
}

// Data Table Search
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    
    if (!input || !table) return;
    
    input.addEventListener('keyup', function() {
        const filter = input.value.toUpperCase();
        const rows = table.getElementsByTagName('tr');
        
        for (let i = 1; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;
            
            for (let j = 0; j < cells.length; j++) {
                if (cells[j].textContent.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
            
            rows[i].style.display = found ? '' : 'none';
        }
    });
}

// Loading Spinner
function showLoading() {
    const loader = document.createElement('div');
    loader.id = 'loading-spinner';
    loader.innerHTML = '<div class="spinner"></div>';
    loader.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;';
    document.body.appendChild(loader);
}

function hideLoading() {
    const loader = document.getElementById('loading-spinner');
    if (loader) {
        loader.remove();
    }
}

// Web3 Integration (Phase IX)
let web3Provider = null;
let web3Instance = null;
let userAccount = null;

async function connectWallet() {
    if (typeof window.ethereum !== 'undefined') {
        try {
            showLoading();
            const accounts = await window.ethereum.request({ method: 'eth_requestAccounts' });
            userAccount = accounts[0];
            
            // Initialize web3
            web3Provider = window.ethereum;
            // web3Instance = new Web3(web3Provider);
            
            console.log('Connected account:', userAccount);
            
            // Update UI
            const walletAddressElement = document.getElementById('walletAddress');
            if (walletAddressElement) {
                walletAddressElement.textContent = userAccount.substring(0, 6) + '...' + userAccount.substring(38);
            }
            
            hideLoading();
            alert('Wallet connected successfully!');
            
            return userAccount;
        } catch (error) {
            hideLoading();
            console.error('Error connecting wallet:', error);
            alert('Failed to connect wallet');
            return null;
        }
    } else {
        alert('Please install MetaMask or another Web3 wallet');
        return null;
    }
}

async function disconnectWallet() {
    userAccount = null;
    web3Provider = null;
    web3Instance = null;
    
    const walletAddressElement = document.getElementById('walletAddress');
    if (walletAddressElement) {
        walletAddressElement.textContent = 'Connect Wallet';
    }
    alert('Wallet disconnected');
}

// IPFS Integration (Phase IX)
async function uploadToIPFS(file) {
    // Placeholder for IPFS integration
    console.log('Uploading to IPFS:', file.name);
    
    // This will be implemented in Phase IX with actual IPFS API
    return {
        success: false,
        hash: null,
        message: 'IPFS integration pending'
    };
}

// Blockchain Certificate Storage (Phase IX)
async function storeCertificateOnBlockchain(certificateData) {
    // Placeholder for blockchain integration
    console.log('Storing certificate on blockchain:', certificateData);
    
    // This will be implemented in Phase IX with smart contract interaction
    return {
        success: false,
        txHash: null,
        message: 'Blockchain integration pending'
    };
}

// Export functions for use in other scripts
window.certificatePlatform = {
    verifyCertificateAPI,
    getCertificatesAPI,
    copyToClipboard,
    printCertificate,
    shareCertificate,
    previewFile,
    resetForm,
    confirmAction,
    searchTable,
    showLoading,
    hideLoading,
    connectWallet,
    disconnectWallet,
    uploadToIPFS,
    storeCertificateOnBlockchain
};

console.log('Certificate Platform JS loaded successfully');