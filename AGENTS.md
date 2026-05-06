# AGENTS.md — XenForo AI Connect (Scope Guard)

**חובה לקרוא לפני כל פעולה בתיקייה זו.**

---

## תחום אחריות (Scope)

AI agent שעובד בתיקייה זו מטפל **אך ורק** בתוסף XenForo AI Connect.

### מותר לגשת

```
/workspace/platforms/xenforo/           ← תיקייה זו בלבד
/workspace/plugins/xenforo/             ← source code של התוסף (אם קיים)
```

### אסור לגשת — בשום מקרה

```
/workspace/plugins-manager/             ❌ מנהל התוספים — לא נוגעים
/workspace/platforms/wordpress/         ❌ פלטפורמה אחרת
/workspace/platforms/drupal/            ❌ פלטפורמה אחרת
/workspace/plugins/drupal/              ❌ פלטפורמה אחרת
/workspace/plugins/shopify/             ❌ פלטפורמה אחרת
```

> ⛔ **אם המשימה שקיבלת מצריכה לגעת ב-`plugins-manager/` — עצור ושאל את המשתמש. אל תפעל.**

---

## בידוד OS — Linux User

**משתמש פלטפורמה:** `xenforo-dev` (uid=993)

כל פקודת bash **חייבת** לרוץ תחת המשתמש הזה:

```bash
sudo -u xenforo-dev bash -c 'php -l /workspace/platforms/xenforo/upload/src/addons/chgold/AIConnect/Api/Controller/Token.php'
sudo -u xenforo-dev bash -c 'git -C /workspace/platforms/xenforo status'
```

אם תנסה לגשת לתיקייה אחרת — **OS יחסום** ✅:
```bash
sudo -u xenforo-dev bash -c 'cat /workspace/plugins-manager/api.php'
# → Permission denied  ← זה נכון
```

לסשן OpenCode מבודד מלא (חוסם גם Read/Edit/Write):
```bash
sudo -u xenforo-dev opencode
```

לבדיקת הרשאות: `pm-isolate check xenforo <path>`

---

## כללי עבודה

1. **חובה להריץ בדיקה לאחר כל שינוי:**
   ```bash
   cd /workspace/plugins-manager && php bin/check.php xenforo-addon
   ```

2. **Workflow מחייב לפני push:**
   ```bash
   cd /workspace/platforms/xenforo/upload
   php cmd.php xf:addon-upgrade chgold/AIConnect
   echo "y" | php cmd.php xf-addon:build-release chgold/AIConnect
   cd /workspace/plugins-manager && php bin/check.php xenforo-addon
   ```

3. **Source canonical:** `/workspace/plugins/xenforo/` (אם קיים) → sync ל-`platforms/`
4. **אסור INSERT/UPDATE/DELETE ישיר ל-DB** — רק דרך XF migrations
5. **כל פקודת git עם prefix:** `GIT_MASTER=1 git ...`

---

## מה לא לתקן ללא הוראה

ה-issues הבאות הן **ידועות ומכוונות** — אל תתקן:

| בעיה | RULE | הסבר |
|---|---|---|
| JSON-RPC format במקום REST path | RULE-008 | XenForo limitation |

ראה `/workspace/plugins-manager/AGENTS.md` § 2.4 לרשימה מלאה.
