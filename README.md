# پلتفرم ایمپورت (کارخانه / ثبت منبع / مسئول فنی / IRC)

## امکانات
- کنترل پذیرش با وایت‌لیست (شناسه ملی یا نام)
- نرمال‌سازی تاریخ جلالی، ارقام، نام‌ها
- نسخه‌گذاری (row_hash) برای ثبت منبع و مسئول فنی
- محاسبه مدت زمان بررسی ثبت منبع (committee - final_fix)
- پاسخ JSON با آمار inserted / skipped / skip_reasons
- اسکریپت کامل ساخت اسکیمای دیتابیس

## Endpoints
| Dataset | Endpoint | متد |
|---------|----------|-----|
| factories | api/import_factories.php | POST |
| source | api/import_source.php | POST |
| tech | api/import_tech.php | POST |
| irc | api/import_irc.php | POST |
| whitelist diag | api/whitelist_diag.php?id=&name= | GET |
| health | api/health.php | GET |

## Skip Reasons
- factories: no_identifier, not_whitelisted, economic_code_mismatch, other
- source: missing_source_code, not_whitelisted, duplicate_identical, other
- tech: no_person_data, not_whitelisted, duplicate_identical, other
- irc: missing_irc_code, not_whitelisted, duplicate_identical, other

## ENV Flags
در `.env.example` توضیح داده شده.

## جریان ایمپورت
1. بارگذاری CSV
2. Auto-map ستون‌ها
3. نرمال‌سازی تاریخ / نام / شناسه
4. بررسی وایت‌لیست
5. محاسبه hash یا upsert
6. پاسخ JSON با آمار

## بازمحاسبه‌ها
`tools/recalc_review_duration.php` و اسکریپت‌های hash موجود است.

## توسعه بعدی
درخواست بدهید برای: احراز هویت، کش، گزارش‌ها، REST CRUD، پیاده‌سازی row_hash برای IRC (در صورت نیاز)، تحلیل اختلاف نسخه‌ها، اعتبار کد ملی.

```bash
mysql -u root -p < sql/full_schema_rebuild.sql
cp .env.example .env
# سپس آدرس /public/import.html را باز کنید
```