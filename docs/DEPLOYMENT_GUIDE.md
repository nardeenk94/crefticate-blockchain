# دليل نشر Smart Contract على Remix IDE 🚀

## 📋 المتطلبات الأساسية

### 1. المحفظة الرقمية (Wallet)
- تثبيت MetaMask: https://metamask.io
- إنشاء حساب جديد وحفظ Seed Phrase بأمان
- التبديل إلى Sepolia Testnet

### 2. الحصول على Testnet ETH
- Sepolia Faucet: https://sepoliafaucet.com
- أو: https://faucet.quicknode.com/ethereum/sepolia
- احتاج على الأقل 0.1 ETH للنشر

---

## 🔧 خطوات النشر على Remix

### الخطوة 1: فتح Remix IDE
1. اذهب إلى: https://remix.ethereum.org
2. ستفتح واجهة Remix IDE

### الخطوة 2: إنشاء الملف
1. في قائمة File Explorer على اليسار
2. اضغط على أيقونة + لإنشاء ملف جديد
3. اسم الملف: `CertificateRegistry.sol`
4. انسخ كود Smart Contract من الملف:
   ```
   smart-contract/CertificateRegistry.sol
   ```

### الخطوة 3: Compile (الترجمة)
1. اذهب إلى تاب "Solidity Compiler" (أيقونة S على اليسار)
2. اختر Compiler Version: **0.8.19** أو أحدث
3. تأكد من تفعيل: "Auto compile" أو اضغط "Compile CertificateRegistry.sol"
4. يجب أن يظهر علامة ✓ خضراء = نجح الترجمة

### الخطوة 4: Deploy (النشر)
1. اذهب إلى تاب "Deploy & Run Transactions" (أيقونة Ethereum)
2. في قسم ENVIRONMENT اختر: **"Injected Provider - MetaMask"**
3. سيطلب MetaMask الاتصال - اضغط Confirm
4. تأكد من اختيار **Sepolia Test Network** في MetaMask
5. في ACCOUNT ستظهر محفظتك
6. تأكد من وجود رصيد ETH كافي
7. في CONTRACT اختر: **CertificateRegistry**
8. اضغط زر **Deploy** البرتقالي 🟠

### الخطوة 5: تأكيد المعاملة
1. ستظهر نافذة MetaMask للموافقة
2. راجع Gas Fee (يجب أن تكون معقولة)
3. اضغط **Confirm**
4. انتظر حتى يتم النشر (10-30 ثانية)

### الخطوة 6: حفظ معلومات العقد
بعد النشر الناجح، ستظهر معلومات العقد أسفل "Deployed Contracts":

```
CONTRACT ADDRESS: 0x... (انسخه!)
```

احفظ هذه المعلومات في مكان آمن:
- **Contract Address**: عنوان العقد على البلوكشين
- **Network**: Sepolia Testnet
- **Deployer Address**: عنوان محفظتك

---

## 📝 بعد النشر: الحصول على ABI

### طريقة 1: من Remix
1. في تاب Compiler
2. أسفل زر Compile، اضغط على "Compilation Details"
3. ابحث عن قسم "ABI"
4. اضغط على أيقونة النسخ 📋
5. احفظه في ملف: `contract-abi.json`

### طريقة 2: من Deployed Contracts
1. في تاب Deploy
2. اضغط على العقد المنشور لتوسيعه
3. ستجد جميع الوظائف المتاحة

---

## ⚙️ إعداد ملف Config في PHP

بعد النشر، حدّث ملف `config/config.php`:

```php
// Blockchain settings
define('WEB3_PROVIDER', 'https://sepolia.infura.io/v3/YOUR_INFURA_KEY');
define('CONTRACT_ADDRESS', '0xYOUR_CONTRACT_ADDRESS_HERE'); // من الخطوة 6
define('BLOCKCHAIN_NETWORK', 'sepolia');

// Contract ABI (ضع ABI الكامل هنا)
define('CONTRACT_ABI', '[
    {
        "inputs": [],
        "stateMutability": "nonpayable",
        "type": "constructor"
    },
    // ... باقي ABI
]');
```

### الحصول على Infura API Key
1. اذهب إلى: https://infura.io
2. أنشئ حساب مجاني
3. أنشئ مشروع جديد (Create New Project)
4. اختر Product: Ethereum
5. انسخ Project ID وضعه في YOUR_INFURA_KEY

---

## 🧪 اختبار العقد على Remix

### 1. تفويض مصدر (Authorize Issuer)
```
Function: authorizeIssuer
Parameters:
- _issuerAddress: 0x... (عنوان محفظة المصدر)
- _name: "University Name"
- _email: "admin@university.com"

اضغط: transact
```

### 2. إصدار شهادة (Issue Certificate)
```
Function: issueCertificate
Parameters:
- _certificateId: "CERT-2025-ABC123"
- _recipientName: "أحمد محمد"
- _recipientId: "12345678"
- _ipfsHash: "QmXXXXXXX..." (من IPFS)
- _title: "شهادة تخرج"
- _field: "Computer Science"
- _issueDate: 1735689600 (Unix timestamp)
- _expiryDate: 0 (0 = لا تنتهي)

اضغط: transact
```

### 3. التحقق من شهادة (Verify Certificate)
```
Function: verifyCertificate
Parameters:
- _certificateId: "CERT-2025-ABC123"

اضغط: call (أزرق - قراءة فقط)

النتيجة ستظهر:
- certificateId
- issuerName
- recipientName
- ipfsHash
- title
- issueDate
- isValid: true/false
```

### 4. إلغاء شهادة (Revoke Certificate)
```
Function: revokeCertificate
Parameters:
- _certificateId: "CERT-2025-ABC123"

اضغط: transact
```

---

## 🔍 التحقق من المعاملات

### Etherscan Sepolia
بعد كل معاملة، ستحصل على Transaction Hash:

1. اذهب إلى: https://sepolia.etherscan.io
2. الصق Transaction Hash في البحث
3. ستجد تفاصيل المعاملة:
   - Status: Success ✓ / Failed ✗
   - Block Number
   - Gas Used
   - From/To
   - Input Data

### عرض العقد
1. اذهب إلى: https://sepolia.etherscan.io/address/YOUR_CONTRACT_ADDRESS
2. ستجد:
   - جميع المعاملات
   - الرصيد
   - الكود (بعد Verification)

---

## 📱 ربط العقد مع الموقع

### ملف: issuer/issue-certificate-blockchain.php

```php
<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/web3-functions.php';

start_secure_session();
require_role(['issuer']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. حفظ البيانات في Database
    $certificate_id = generate_certificate_id();
    
    // 2. رفع الملف على IPFS
    $ipfs_result = Web3Manager::uploadToIPFS($uploaded_file_path);
    
    if ($ipfs_result['success']) {
        $ipfs_hash = $ipfs_result['hash'];
        
        // 3. إصدار على البلوكشين
        $blockchain_data = [
            'certificate_id' => $certificate_id,
            'recipient_name' => $_POST['recipient_name'],
            'recipient_id' => $_POST['recipient_id'],
            'ipfs_hash' => $ipfs_hash,
            'title' => $_POST['title'],
            'field' => $_POST['field'],
            'issue_date' => strtotime($_POST['issue_date']),
            'expiry_date' => 0
        ];
        
        // هنا تحتاج Private Key للمصدر (احفظه بأمان!)
        $issuer_private_key = $_SESSION['issuer_private_key'];
        
        $blockchain_result = Web3Manager::issueCertificateOnBlockchain(
            $blockchain_data,
            $issuer_private_key
        );
        
        if ($blockchain_result['success']) {
            // 4. حفظ TX Hash في Database
            $pdo = getPDOConnection();
            $stmt = $pdo->prepare("
                UPDATE certificates 
                SET ipfs_hash = ?, blockchain_tx_hash = ?
                WHERE certificate_id = ?
            ");
            $stmt->execute([
                $ipfs_hash,
                $blockchain_result['transactionHash'],
                $certificate_id
            ]);
            
            $_SESSION['success'] = 'Certificate issued on blockchain!';
        }
    }
}
?>
```

---

## 🛡️ أمان Smart Contract

### Best Practices المطبقة

✅ **Access Control**: فقط Admin يمكنه تفويض المصدرين
✅ **Modifier Protection**: فقط المصدرين المعتمدين يمكنهم الإصدار
✅ **Validation**: التحقق من جميع المدخلات
✅ **Events**: تسجيل جميع الأحداث المهمة
✅ **Immutability**: البيانات لا يمكن تعديلها بعد الإصدار

### توصيات إضافية

1. **Private Key Management**:
   - لا تحفظ Private Keys في الكود
   - استخدم Environment Variables
   - أو خدمات مثل AWS KMS

2. **Gas Optimization**:
   - استخدم `string memory` بدلاً من `string storage` حيثما أمكن
   - تجنب الحلقات الكبيرة

3. **Upgradability**:
   - يمكن استخدام Proxy Pattern للتحديثات
   - أو نشر نسخة جديدة ونقل البيانات

---

## 📊 تكاليف Gas التقديرية

على Sepolia Testnet:

| العملية | Gas | التكلفة (تقريبية) |
|---------|-----|-------------------|
| Deploy Contract | ~2,500,000 | 0.05 ETH |
| Authorize Issuer | ~100,000 | 0.002 ETH |
| Issue Certificate | ~250,000 | 0.005 ETH |
| Verify Certificate | 0 (Read) | مجاناً |
| Revoke Certificate | ~50,000 | 0.001 ETH |

**ملاحظة**: هذه أسعار Testnet. على Mainnet ستكون أعلى!

---

## 🔧 حل المشاكل الشائعة

### ❌ "Insufficient funds"
**الحل**: احصل على المزيد من Testnet ETH من Faucet

### ❌ "Gas estimation failed"
**الحل**: 
- تحقق من صحة المدخلات
- تأكد من أن الوظيفة ليست `view` أو `pure`

### ❌ "Execution reverted"
**الحل**: 
- اقرأ رسالة الخطأ
- تحقق من الشروط (require statements)

### ❌ "Nonce too low"
**الحل**: 
- Reset Account في MetaMask
- Settings > Advanced > Reset Account

---

## 🎯 Next Steps

بعد النشر الناجح:

1. ✅ اختبر جميع الوظائف على Remix
2. ✅ احفظ Contract Address و ABI
3. ✅ حدّث `config/config.php`
4. ✅ أنشئ Infura Project
5. ✅ اربط الموقع مع Smart Contract
6. ✅ اختبر الإصدار والتحقق
7. ✅ نشر على Mainnet (عندما تكون جاهزاً)

---

## 📚 مصادر إضافية

- **Remix Documentation**: https://remix-ide.readthedocs.io
- **Solidity Docs**: https://docs.soliditylang.org
- **OpenZeppelin Contracts**: https://docs.openzeppelin.com/contracts
- **Web3.js**: https://web3js.readthedocs.io
- **Ethereum.org**: https://ethereum.org/en/developers/

---

## ⚠️ تحذيرات مهمة

1. 🔴 **لا تشارك Private Key أبداً!**
2. 🟡 اختبر على Testnet أولاً
3. 🟡 تأكد من صحة الكود قبل النشر
4. 🟡 Smart Contracts لا يمكن تعديلها بعد النشر
5. 🔴 على Mainnet: كل معاملة تكلف ETH حقيقي!

---

**تم إنشاؤه بواسطة**: Certificate Verification Platform Team
**التاريخ**: 30 أكتوبر 2025
**الإصدار**: 1.0
