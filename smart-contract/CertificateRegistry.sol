// SPDX-License-Identifier: MIT
pragma solidity ^0.8.19;

/**
 * @title CertificateRegistry
 * @dev Smart Contract for Certificate Management on Blockchain
 * @author Certificate Verification Platform Team
 */

contract CertificateRegistry {
    
    // ============================================
    // State Variables
    // ============================================
    
    address public admin;
    uint256 public certificateCount;
    uint256 public issuerCount;
    
    // ============================================
    // Structs
    // ============================================
    
    struct Certificate {
        string certificateId;
        address issuerAddress;
        string issuerName;
        string recipientName;
        string recipientId;
        string ipfsHash;
        string title;
        string field;
        uint256 issueDate;
        uint256 expiryDate;
        bool isValid;
        bool exists;
        uint256 createdAt;
    }
    
    struct Issuer {
        string name;
        string email;
        bool isAuthorized;
        uint256 certificatesIssued;
        uint256 registeredAt;
    }
    
    // ============================================
    // Mappings
    // ============================================
    
    mapping(string => Certificate) public certificates;
    mapping(address => Issuer) public issuers;
    mapping(address => bool) public authorizedIssuers;
    mapping(address => string[]) public issuerCertificates;
    
    // ============================================
    // Events
    // ============================================
    
    event CertificateIssued(
        string indexed certificateId,
        address indexed issuer,
        string recipientName,
        string ipfsHash,
        uint256 timestamp
    );
    
    event CertificateRevoked(
        string indexed certificateId,
        address indexed revokedBy,
        uint256 timestamp
    );
    
    event IssuerAuthorized(
        address indexed issuer,
        string name,
        uint256 timestamp
    );
    
    event IssuerRevoked(
        address indexed issuer,
        uint256 timestamp
    );
    
    // ============================================
    // Modifiers
    // ============================================
    
    modifier onlyAdmin() {
        require(msg.sender == admin, "Only admin can perform this action");
        _;
    }
    
    modifier onlyAuthorizedIssuer() {
        require(authorizedIssuers[msg.sender], "Not an authorized issuer");
        _;
    }
    
    modifier certificateExists(string memory _certificateId) {
        require(certificates[_certificateId].exists, "Certificate does not exist");
        _;
    }
    
    // ============================================
    // Constructor
    // ============================================
    
    constructor() {
        admin = msg.sender;
        certificateCount = 0;
        issuerCount = 0;
    }
    
    // ============================================
    // Admin Functions
    // ============================================
    
    /**
     * @dev Authorize a new issuer
     * @param _issuerAddress Address of the issuer
     * @param _name Name of the issuing organization
     * @param _email Email of the issuer
     */
    function authorizeIssuer(
        address _issuerAddress,
        string memory _name,
        string memory _email
    ) public onlyAdmin {
        require(_issuerAddress != address(0), "Invalid address");
        require(!authorizedIssuers[_issuerAddress], "Issuer already authorized");
        
        issuers[_issuerAddress] = Issuer({
            name: _name,
            email: _email,
            isAuthorized: true,
            certificatesIssued: 0,
            registeredAt: block.timestamp
        });
        
        authorizedIssuers[_issuerAddress] = true;
        issuerCount++;
        
        emit IssuerAuthorized(_issuerAddress, _name, block.timestamp);
    }
    
    /**
     * @dev Revoke an issuer's authorization
     * @param _issuerAddress Address of the issuer to revoke
     */
    function revokeIssuerAuthorization(address _issuerAddress) public onlyAdmin {
        require(authorizedIssuers[_issuerAddress], "Issuer not authorized");
        
        authorizedIssuers[_issuerAddress] = false;
        issuers[_issuerAddress].isAuthorized = false;
        
        emit IssuerRevoked(_issuerAddress, block.timestamp);
    }
    
    /**
     * @dev Transfer admin role to a new address
     * @param _newAdmin Address of the new admin
     */
    function transferAdmin(address _newAdmin) public onlyAdmin {
        require(_newAdmin != address(0), "Invalid address");
        admin = _newAdmin;
    }
    
    // ============================================
    // Issuer Functions
    // ============================================
    
    /**
     * @dev Issue a new certificate
     */
    function issueCertificate(
        string memory _certificateId,
        string memory _recipientName,
        string memory _recipientId,
        string memory _ipfsHash,
        string memory _title,
        string memory _field,
        uint256 _issueDate,
        uint256 _expiryDate
    ) public onlyAuthorizedIssuer {
        require(!certificates[_certificateId].exists, "Certificate ID already exists");
        require(bytes(_certificateId).length > 0, "Certificate ID cannot be empty");
        require(bytes(_recipientName).length > 0, "Recipient name cannot be empty");
        require(bytes(_ipfsHash).length > 0, "IPFS hash cannot be empty");
        
        certificates[_certificateId] = Certificate({
            certificateId: _certificateId,
            issuerAddress: msg.sender,
            issuerName: issuers[msg.sender].name,
            recipientName: _recipientName,
            recipientId: _recipientId,
            ipfsHash: _ipfsHash,
            title: _title,
            field: _field,
            issueDate: _issueDate,
            expiryDate: _expiryDate,
            isValid: true,
            exists: true,
            createdAt: block.timestamp
        });
        
        issuerCertificates[msg.sender].push(_certificateId);
        issuers[msg.sender].certificatesIssued++;
        certificateCount++;
        
        emit CertificateIssued(
            _certificateId,
            msg.sender,
            _recipientName,
            _ipfsHash,
            block.timestamp
        );
    }
    
    /**
     * @dev Revoke a certificate
     * @param _certificateId ID of the certificate to revoke
     */
    function revokeCertificate(string memory _certificateId) 
        public 
        certificateExists(_certificateId) 
    {
        Certificate storage cert = certificates[_certificateId];
        require(
            cert.issuerAddress == msg.sender || msg.sender == admin,
            "Not authorized to revoke this certificate"
        );
        require(cert.isValid, "Certificate already revoked");
        
        cert.isValid = false;
        
        emit CertificateRevoked(_certificateId, msg.sender, block.timestamp);
    }
    
    // ============================================
    // View Functions
    // ============================================
    
    /**
     * @dev Verify a certificate
     * @param _certificateId ID of the certificate to verify
     * @return All certificate details
     */
    function verifyCertificate(string memory _certificateId) 
        public 
        view 
        certificateExists(_certificateId)
        returns (
            string memory certificateId,
            string memory issuerName,
            string memory recipientName,
            string memory ipfsHash,
            string memory title,
            uint256 issueDate,
            bool isValid
        ) 
    {
        Certificate memory cert = certificates[_certificateId];
        return (
            cert.certificateId,
            cert.issuerName,
            cert.recipientName,
            cert.ipfsHash,
            cert.title,
            cert.issueDate,
            cert.isValid
        );
    }
    
    /**
     * @dev Get complete certificate details
     */
    function getCertificateDetails(string memory _certificateId)
        public
        view
        certificateExists(_certificateId)
        returns (Certificate memory)
    {
        return certificates[_certificateId];
    }
    
    /**
     * @dev Check if a certificate exists and is valid
     */
    function isCertificateValid(string memory _certificateId)
        public
        view
        returns (bool)
    {
        if (!certificates[_certificateId].exists) {
            return false;
        }
        
        Certificate memory cert = certificates[_certificateId];
        
        // Check if expired
        if (cert.expiryDate > 0 && block.timestamp > cert.expiryDate) {
            return false;
        }
        
        return cert.isValid;
    }
    
    /**
     * @dev Get issuer information
     */
    function getIssuerInfo(address _issuerAddress)
        public
        view
        returns (
            string memory name,
            string memory email,
            bool isAuthorized,
            uint256 certificatesIssued
        )
    {
        Issuer memory issuer = issuers[_issuerAddress];
        return (
            issuer.name,
            issuer.email,
            issuer.isAuthorized,
            issuer.certificatesIssued
        );
    }
    
    /**
     * @dev Get all certificates issued by an issuer
     */
    function getIssuerCertificates(address _issuerAddress)
        public
        view
        returns (string[] memory)
    {
        return issuerCertificates[_issuerAddress];
    }
    
    /**
     * @dev Get platform statistics
     */
    function getStats()
        public
        view
        returns (
            uint256 totalCertificates,
            uint256 totalIssuers
        )
    {
        return (certificateCount, issuerCount);
    }
}
