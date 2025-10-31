<?php
/**
 * Web3 Integration Helper Functions
 * Handles blockchain and IPFS operations
 */

require_once __DIR__ . '/../config/config.php';

class Web3Manager {
    
    /**
     * Upload file to IPFS
     * @param string $filePath Path to the file to upload
     * @return array Result with success status and IPFS hash
     */
    public static function uploadToIPFS($filePath) {
        if (!file_exists($filePath)) {
            return [
                'success' => false,
                'error' => 'File not found'
            ];
        }
        
        $url = IPFS_GATEWAY . '/api/v0/add';
        $auth = base64_encode(IPFS_PROJECT_ID . ':' . IPFS_PROJECT_SECRET);
        
        $ch = curl_init();
        $file = new CURLFile($filePath);
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['file' => $file],
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . $auth
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode == 200 && $response) {
            $data = json_decode($response, true);
            if (isset($data['Hash'])) {
                return [
                    'success' => true,
                    'hash' => $data['Hash'],
                    'url' => 'https://ipfs.io/ipfs/' . $data['Hash'],
                    'size' => $data['Size'] ?? 0
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => $error ?: 'Failed to upload to IPFS',
            'http_code' => $httpCode
        ];
    }
    
    /**
     * Verify certificate from blockchain using Node.js script
     * @param string $certificateId Certificate ID to verify
     * @return array Verification result
     */
    public static function verifyCertificateOnBlockchain($certificateId) {
        // Create temporary Node.js script
        $script = <<<JS
const Web3 = require('web3');
const web3 = new Web3('{WEB3_PROVIDER}');

const contractABI = {CONTRACT_ABI};
const contractAddress = '{CONTRACT_ADDRESS}';
const contract = new web3.eth.Contract(contractABI, contractAddress);

async function verify() {
    try {
        const result = await contract.methods.verifyCertificate('{$certificateId}').call();
        console.log(JSON.stringify({
            success: true,
            certificateId: result.certificateId,
            issuerName: result.issuerName,
            recipientName: result.recipientName,
            ipfsHash: result.ipfsHash,
            title: result.title,
            issueDate: result.issueDate,
            isValid: result.isValid
        }));
    } catch (error) {
        console.log(JSON.stringify({
            success: false,
            error: error.message
        }));
    }
}

verify();
JS;

        $scriptPath = sys_get_temp_dir() . '/verify_' . uniqid() . '.js';
        file_put_contents($scriptPath, $script);
        
        $output = shell_exec("node $scriptPath 2>&1");
        unlink($scriptPath);
        
        $result = json_decode($output, true);
        return $result ?: [
            'success' => false,
            'error' => 'Failed to execute blockchain verification'
        ];
    }
    
    /**
     * Issue certificate on blockchain using Node.js script
     * @param array $certificateData Certificate data
     * @param string $issuerPrivateKey Issuer's private key
     * @return array Transaction result
     */
    public static function issueCertificateOnBlockchain($certificateData, $issuerPrivateKey) {
        $script = <<<JS
const Web3 = require('web3');
const web3 = new Web3('{WEB3_PROVIDER}');

const contractABI = {CONTRACT_ABI};
const contractAddress = '{CONTRACT_ADDRESS}';
const contract = new web3.eth.Contract(contractABI, contractAddress);

const privateKey = '{$issuerPrivateKey}';
const account = web3.eth.accounts.privateKeyToAccount(privateKey);
web3.eth.accounts.wallet.add(account);

async function issue() {
    try {
        const tx = await contract.methods.issueCertificate(
            '{$certificateData['certificate_id']}',
            '{$certificateData['recipient_name']}',
            '{$certificateData['recipient_id']}',
            '{$certificateData['ipfs_hash']}',
            '{$certificateData['title']}',
            '{$certificateData['field']}',
            {$certificateData['issue_date']},
            {$certificateData['expiry_date']}
        ).send({
            from: account.address,
            gas: 300000
        });
        
        console.log(JSON.stringify({
            success: true,
            transactionHash: tx.transactionHash,
            blockNumber: tx.blockNumber,
            gasUsed: tx.gasUsed
        }));
    } catch (error) {
        console.log(JSON.stringify({
            success: false,
            error: error.message
        }));
    }
}

issue();
JS;

        $scriptPath = sys_get_temp_dir() . '/issue_' . uniqid() . '.js';
        file_put_contents($scriptPath, $script);
        
        $output = shell_exec("node $scriptPath 2>&1");
        unlink($scriptPath);
        
        $result = json_decode($output, true);
        return $result ?: [
            'success' => false,
            'error' => 'Failed to execute blockchain transaction'
        ];
    }
    
    /**
     * Get certificate from IPFS
     * @param string $ipfsHash IPFS hash
     * @return array File content or error
     */
    public static function getFromIPFS($ipfsHash) {
        $url = "https://ipfs.io/ipfs/" . $ipfsHash;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200 && $response) {
            return [
                'success' => true,
                'content' => $response
            ];
        }
        
        return [
            'success' => false,
            'error' => 'Failed to retrieve from IPFS'
        ];
    }
    
    /**
     * Check if Web3 is configured
     * @return bool
     */
    public static function isWeb3Configured() {
        return defined('WEB3_PROVIDER') && 
               WEB3_PROVIDER !== 'https://sepolia.infura.io/v3/YOUR_INFURA_KEY' &&
               defined('CONTRACT_ADDRESS') &&
               CONTRACT_ADDRESS !== '0x0000000000000000000000000000000000000000';
    }
    
    /**
     * Check if IPFS is configured
     * @return bool
     */
    public static function isIPFSConfigured() {
        return defined('IPFS_PROJECT_ID') && 
               IPFS_PROJECT_ID !== 'YOUR_PROJECT_ID' &&
               defined('IPFS_PROJECT_SECRET') &&
               IPFS_PROJECT_SECRET !== 'YOUR_PROJECT_SECRET';
    }
}

/**
 * Helper function to upload certificate file to IPFS
 */
function upload_certificate_to_ipfs($filePath) {
    if (!Web3Manager::isIPFSConfigured()) {
        return [
            'success' => false,
            'error' => 'IPFS not configured'
        ];
    }
    
    return Web3Manager::uploadToIPFS($filePath);
}

/**
 * Helper function to store certificate on blockchain
 */
function store_certificate_on_blockchain($certificateData, $issuerPrivateKey) {
    if (!Web3Manager::isWeb3Configured()) {
        return [
            'success' => false,
            'error' => 'Web3 not configured'
        ];
    }
    
    return Web3Manager::issueCertificateOnBlockchain($certificateData, $issuerPrivateKey);
}

/**
 * Helper function to verify certificate from blockchain
 */
function verify_certificate_from_blockchain($certificateId) {
    if (!Web3Manager::isWeb3Configured()) {
        return [
            'success' => false,
            'error' => 'Web3 not configured'
        ];
    }
    
    return Web3Manager::verifyCertificateOnBlockchain($certificateId);
}
?>
