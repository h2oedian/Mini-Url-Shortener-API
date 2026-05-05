# Mini URL Shortener API

یک API سبک و امن برای کوتاه‌سازی لینک با قابلیت ردیابی کلیک و آمار پیشرفته.

## ویژگی‌های کلیدی

- **کوتاه‌سازی URL** با کدهای 6 کاراکتری یونیک (Base62)
- **احراز هویت** با Bearer Token
- **ردیابی کلیک** با ثبت IP، User-Agent و Referer
- **آمار پیشرفته** شامل کل کلیک‌ها، بازدیدکنندگان یونیک و آمار روزانه
- **معماری تمیز** بدون فریمورک، با PSR-4 autoloading
- **امنیت** با Prepared Statements و جلوگیری از SQL Injection

## الزامات سیستم

- PHP 8.0+ (توصیه: 8.2+)
- MySQL 5.7+
- Apache با `mod_rewrite`

## نصب و راه‌اندازی

1. پروژه را در `htdocs` قرار دهید
2. دیتابیس `url_shortener` بسازید و `database/schema.sql` را اجرا کنید
3. فایل `.env.example` را به `.env` کپی کرده و تنظیمات را وارد کنید
4. از طریق `http://localhost/Mini-Url-Shortener-API/` دسترسی داشته باشید

# 📘 مستندات API برای Postman

## 🔐 احراز هویت

تمام endpointهای API نیاز به **Bearer Token** دارند:

Authorization: Bearer YOUR_TOKEN_HERE


---

## 📍 Endpoints

### 1️⃣ ایجاد لینک کوتاه

**`POST /api/shorten`**

#### Headers
Authorization: Bearer test_token_12345
Content-Type: application/json


#### Request Body
```json
{
  "url": "https://www.google.com"
}
```

#### Success Response (201)
```json
{
  "success": true,
  "short_url": "http://localhost:8000/aB3xY9",
  "short_code": "aB3xY9",
  "original_url": "https://www.google.com"
}
```

#### Error Responses
| کد | پیام |
|-----|------|
| `400` | `{"error":"URL is required"}` |
| `400` | `{"error":"URL is too long (max 2048 characters)"}` |
| `400` | `{"error":"Invalid URL format"}` |
| `400` | `{"error":"Only HTTP/HTTPS URLs are allowed"}` |
| `400` | `{"error":"This domain is not allowed"}` |
| `500` | `{"error":"Failed to create short URL"}` |

---

### 2️⃣ لیست تمام لینک‌های کوتاه

**`GET /api/urls`**

#### Headers
Authorization: Bearer test_token_12345


#### Success Response (200)
```json
{
  "urls": [
    {
      "id": 1,
      "short_code": "aB3xY9",
      "original_url": "https://www.google.com",
      "click_count": 5,
      "created_at": "2025-05-05 10:30:00"
    }
  ]
}
```

---

### 3️⃣ مشاهده یک لینک خاص

**`GET /api/urls/{id}`**

#### Headers
Authorization: Bearer test_token_12345


#### Success Response (200)
```json
{
  "id": 1,
  "short_code": "aB3xY9",
  "original_url": "https://www.google.com",
  "click_count": 5,
  "created_at": "2025-05-05 10:30:00"
}
```

#### Error Response (404)
```json
{
  "error": "URL not found"
}
```

---

### 4️⃣ حذف لینک کوتاه

**`DELETE /api/urls/{id}`**

#### Headers
Authorization: Bearer test_token_12345


#### Success Response (200)
```json
{
  "message": "URL deleted successfully"
}
```

#### Error Response (404)
```json
{
  "error": "URL not found"
}
```

---

### 5️⃣ آمار و تحلیل کلیک‌ها

**`GET /api/stats/{shortCode}`**

#### Headers
Authorization: Bearer test_token_12345


#### Success Response (200)
```json
{
  "short_code": "aB3xY9",
  "original_url": "https://www.google.com",
  "total_clicks": 10,
  "unique_clicks": 7,
  "daily_chart": [
    {
      "date": "2025-05-05",
      "count": 3
    }
  ],
  "top_user_agents": [
    {
      "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
      "count": 5
    }
  ]
}
```

#### Error Response (404)
```json
{
  "error": "Short URL not found"
}
```

---

### 6️⃣ ریدایرکت به لینک اصلی

**`GET /{shortCode}`**

#### مثال
GET http://localhost:8000/aB3xY9


#### رفتار
- کاربر به لینک اصلی ریدایرکت می‌شود
- اطلاعات کلیک (IP، User Agent، Referer) ذخیره می‌شود
- شمارنده کلیک افزایش می‌یابد

#### Error Response (404)
```json
{
  "error": "404 - Short URL not found"
}
```

---

## 🛡️ محدودیت‌ها

- **Rate Limiting:** 100 درخواست در ساعت به ازای هر IP
- **حداکثر طول URL:** 2048 کاراکتر
- **دامنه‌های مسدود شده:** `localhost`, `127.0.0.1`, `0.0.0.0`
- **پروتکل‌های مجاز:** فقط `http` و `https`

---

## 🔑 نحوه دریافت توکن

### روش 1: درج دستی در دیتابیس
```sql
INSERT INTO api_tokens (user_id, token, expires_at) 
VALUES (1, 'test_token_12345', DATE_ADD(NOW(), INTERVAL 30 DAY));
```

### روش 2: استفاده از endpoint ثبت‌نام (در صورت پیاده‌سازی)
```bash
POST /api/register
{
  "username": "user1",
  "email": "user@example.com",
  "password": "securepass123"
}
```
## معماری

├── public/          # نقطه ورود (index.php)
├── src/
│   ├── Core/        # Database, Router
│   ├── Middleware/  # Auth, RateLimiting
│   ├── Controllers/ # منطق API
│   └── Models/      # دسترسی به دیتابیس
├── routes/          # تعریف مسیرها
├── config/          # پیکربندی
└── database/        # Schema


## امنیت

- **SQL Injection:** استفاده از PDO Prepared Statements
- **Token Auth:** اعتبارسنجی Bearer Token برای تمام endpoint‌های API
- **تولید کد امن:** استفاده از `random_int()` برای تولید کدهای کوتاه

## الگوریتم تولید کد کوتاه

- **Character set:** `0-9a-zA-Z` (62 کاراکتر)
- **طول پیش‌فرض:** 6 کاراکتر (~56.8 میلیارد ترکیب)
- **تولید:** `random_int()` برای انتخاب تصادفی امن
- **بررسی یونیک:** چک کردن وجود در دیتابیس
- **مکانیزم بازگشتی:** افزایش طول تا 10 کاراکتر در صورت تکراری بودن
- **مدیریت تراکنش:** retry خودکار در صورت duplicate key error


