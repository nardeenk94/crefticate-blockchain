# منصة توثيق الشهادات

نظام شامل لإصدار وإدارة والتحقق من الشهادات الرقمية.

---

## معلومات المشروع

**اسم المشروع**: Certificate Verification Platform  
**قاعدة البيانات**: `certificate_db`

---

## التثبيت والإعداد

### 1. إنشاء قاعدة البيانات

```sql
CREATE DATABASE certificate_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

ثم استورد ملف `database/schema.sql`

### 2. إعداد الاتصال بقاعدة البيانات

افتح ملف `config/database.php` وعدل البيانات التالية:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'اسم_المستخدم');
define('DB_PASS', 'كلمة_المرور');
define('DB_NAME', 'certificate_db');
```

### 3. تعديل رابط الموقع

**مهم جداً**: افتح ملف `config/config.php` وعدل الرابط حسب موقعك:

```php
// إذا كان الموقع في المجلد الرئيسي:
define('SITE_URL', 'http://yourdomain.com');

// إذا كان الموقع في مجلد فرعي:
define('SITE_URL', 'http://yourdomain.com/certificate-platform');
```

⚠️ **يجب تعديل هذا الرابط وإلا لن تعمل روابط الموقع بشكل صحيح**

### 4. صلاحيات المجلدات

```bash
chmod 755 uploads/
chmod 755 uploads/certificates/
```

---

## بيانات تسجيل الدخول

### حساب المسؤول (Admin)

**البريد الإلكتروني**: `admin@platform.com`  
**كلمة المرور**: `password`

⚠️ **يجب تغيير كلمة المرور فوراً بعد أول تسجيل دخول!**

---

## الاستخدام

1. افتح الموقع في المتصفح
2. سجل دخول بحساب المسؤول
3. ابدأ بإصدار الشهادات

---

## المتطلبات

- PHP 7.4 أو أحدث
- MySQL 5.7 أو أحدث
- Apache أو Nginx

---

## الدعم الفني

للأسئلة والدعم: admin@platform.com

---

**تم تطويره في 2025**
