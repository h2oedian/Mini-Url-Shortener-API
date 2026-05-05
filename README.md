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

## API Endpoints

### ایجاد لینک کوتاه
POST /api/shorten
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
"url": "https://example.com/very/long/url"
}


### لیست لینک‌ها
GET /api/urls
Authorization: Bearer YOUR_TOKEN


### جزئیات یک لینک
GET /api/urls/{id}
Authorization: Bearer YOUR_TOKEN


### حذف لینک
DELETE /api/urls/{id}
Authorization: Bearer YOUR_TOKEN


### ریدایرکت
GET /{shortCode}


### آمار کلیک
GET /api/stats/{shortCode}
Authorization: Bearer YOUR_TOKEN


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


