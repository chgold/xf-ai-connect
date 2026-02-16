# 🧹 ניקוי התקנה כושלת של XenForo Addon

## הבעיה
```
One or more add-ons currently have actions pending and may be in an inconsistent state.
```

זה קורה כשהתקנה של addon נכשלה באמצע והוא נשאר במצב "תקוע".

---

## 🎯 פתרון מהיר (MySQL)

### אופציה 1: דרך phpMyAdmin / MySQL CLI (מומלץ)

התחבר ל-MySQL ורוץ את השאילתות הבאות:

```sql
-- 1. מחק את ה-addon מרשימת ההתקנות
DELETE FROM xf_addon WHERE addon_id = 'chgold/AIConnect';

-- 2. מחק jobs תקועים הקשורים לתוסף
DELETE FROM xf_job WHERE unique_key LIKE '%chgold%';
DELETE FROM xf_job WHERE unique_key LIKE '%AIConnect%';

-- 3. מחק טבלאות שנוצרו (אם בכלל נוצרו)
DROP TABLE IF EXISTS xf_ai_connect_api_keys;
DROP TABLE IF EXISTS xf_ai_connect_rate_limits;
DROP TABLE IF EXISTS xf_ai_connect_blocked_users;

-- 4. מחק אופציות שנוצרו (אם בכלל נוצרו)
DELETE FROM xf_option WHERE option_id LIKE 'aiConnect%';

-- 5. נקה cache
TRUNCATE TABLE xf_addon_install_batch;
```

### אופציה 2: דרך SSH (אם יש לך גישה)

```bash
# התחבר לשרת
ssh root@amuta-for

# התחבר ל-MySQL
mysql -u [username] -p [database_name]

# רוץ את כל השאילתות מלמעלה
```

---

## 🗑️ מחיקת קבצים מהשרת

אם העלית קבצים ידנית, מחק אותם:

```bash
# דרך SSH
rm -rf /var/www/gold-t/src/addons/chgold/AIConnect/

# דרך FTP
# מחק את התיקייה: /src/addons/chgold/AIConnect/
```

---

## ✅ אימות הניקוי

לאחר הניקוי, בדוק:

### 1. בדוק שה-addon לא קיים ב-database:
```sql
SELECT * FROM xf_addon WHERE addon_id = 'chgold/AIConnect';
-- צריך להחזיר: Empty set (0 rows)
```

### 2. בדוק שאין jobs תקועים:
```sql
SELECT * FROM xf_job WHERE unique_key LIKE '%chgold%';
-- צריך להחזיר: Empty set (0 rows)
```

### 3. בדוק שאין טבלאות של התוסף:
```sql
SHOW TABLES LIKE 'xf_ai_connect%';
-- צריך להחזיר: Empty set (0 rows)
```

---

## 🔄 התקנה מחדש לאחר הניקוי

### שיטה 1: Upload ZIP (המומלצת ביותר)

1. ✅ **ודא שה-database נקי** (רוץ את השאילתות למעלה)
2. ✅ **ודא שאין קבצים ישנים** בשרת
3. לך ל-**Admin Panel → Add-ons → Upload add-on**
4. בחר את הקובץ המתוקן:
   ```
   /home/chagold/ai-connect-multi-platform/xenforo-ai-connect.zip
   ```
5. לחץ **Upload**

### שיטה 2: Install from Directory (רק אם ZIP לא עובד)

1. העלה את כל התיקייה:
   ```
   Local:  /home/chagold/ai-connect-multi-platform/xenforo-ai-connect/upload/src/addons/chgold/AIConnect/
   Server: /var/www/gold-t/src/addons/chgold/AIConnect/
   ```

2. **חשוב:** העלה ב-**BINARY MODE** (לא ASCII!)

3. לך ל-**Admin Panel → Add-ons → Install from directory**

4. הזן: `chgold/AIConnect`

5. לחץ **Install**

---

## 🚨 אם עדיין לא עובד

### בעיה: "The directory could not be found"

**פתרון:** ודא שהקבצים בדיוק במקום הנכון:
```
/var/www/gold-t/src/addons/chgold/AIConnect/addon.json
/var/www/gold-t/src/addons/chgold/AIConnect/Setup.php
... (all other files)
```

### בעיה: "Permissions error"

**פתרון:** תקן הרשאות:
```bash
# דרך SSH
cd /var/www/gold-t/src/addons/chgold/
chown -R www-data:www-data AIConnect/
chmod -R 755 AIConnect/
```

### בעיה: "Hash mismatch"

**פתרון:** זה אומר שהקבצים שונו בזמן ההעלאה (ASCII mode).
- מחק את כל התיקייה מהשרת
- העלה מחדש ב-**BINARY MODE**
- או: פשוט השתמש ב-**Upload ZIP** במקום

---

## 📋 Checklist לניקוי מלא

- [ ] רצתי `DELETE FROM xf_addon WHERE addon_id = 'chgold/AIConnect';`
- [ ] רצתי `DELETE FROM xf_job WHERE unique_key LIKE '%chgold%';`
- [ ] רצתי `DROP TABLE IF EXISTS xf_ai_connect_api_keys;`
- [ ] רצתי `DROP TABLE IF EXISTS xf_ai_connect_rate_limits;`
- [ ] רצתי `DROP TABLE IF EXISTS xf_ai_connect_blocked_users;`
- [ ] רצתי `DELETE FROM xf_option WHERE option_id LIKE 'aiConnect%';`
- [ ] רצתי `TRUNCATE TABLE xf_addon_install_batch;`
- [ ] אימתתי שה-database נקי (SELECT queries)
- [ ] מחקתי קבצים ישנים מהשרת (אם היו)
- [ ] מוכן להתקנה מחדש ✓

---

## 💡 טיפ חשוב

**תמיד העדף Upload ZIP על Install from Directory!**

למה?
- ✅ XenForo מטפל בכל הקבצים אוטומטית
- ✅ לא צריך להתעסק עם FTP modes (ASCII vs BINARY)
- ✅ פחות סיכוי לבעיות הרשאות
- ✅ פחות סיכוי לבעיות hash mismatch

---

## 🎯 סיכום מהיר

```sql
-- רוץ את זה ב-MySQL ואתה נקי:
DELETE FROM xf_addon WHERE addon_id = 'chgold/AIConnect';
DELETE FROM xf_job WHERE unique_key LIKE '%chgold%';
DROP TABLE IF EXISTS xf_ai_connect_api_keys;
DROP TABLE IF EXISTS xf_ai_connect_rate_limits;
DROP TABLE IF EXISTS xf_ai_connect_blocked_users;
DELETE FROM xf_option WHERE option_id LIKE 'aiConnect%';
TRUNCATE TABLE xf_addon_install_batch;
```

ואז:
1. Admin → Add-ons → **Upload add-on**
2. בחר `xenforo-ai-connect.zip`
3. התקן ✓

---

*נוצר: 16.02.2026 10:20 IST*
