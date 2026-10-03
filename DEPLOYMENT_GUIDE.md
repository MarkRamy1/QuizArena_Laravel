# 🚀 دليل نشر وتشغيل QuizArena ULTRA على السحابة (Cloud Deployment Guide)

تم إعداد المشروع بالكامل ليكون جاهزاً للنشر الفوري (Production-Ready) على أشهر منصات الـ PaaS السحابية (Railway, Render, Fly.io) بواسطة **Docker** متعدد المراحل فائق الخفة والسرعة، مع دعم التشغيل التلقائي لقاعدة البيانات (Migrations & Seeders) وشهادات SSL التلقائية.

---

## ⚡ الخيار الأسرع والموصى به: النشر على Railway (يستغرق 3 دقائق)

تعتبر منصة **[Railway.app](https://railway.app)** الخيار الأسهل والأسرع لأنها تدعم إنشاء قاعدة بيانات **MySQL** بنقرة واحدة وربطها تلقائياً بالمشروع دون أي تعقيد.

### الخطوات:
1. **رفع المشروع على GitHub**:
   - تأكد من رفع كود المشروع إلى مستودع (Repository) خاص أو عام على حسابك في GitHub:
   ```bash
   git init
   git add .
   git commit -m "feat: setup cloud deployment with docker & paas configs"
   git remote add origin https://github.com/USERNAME/QuizArena_Laravel.git
   git branch -M main
   git push -u origin main
   ```

2. **إنشاء مشروع جديد في Railway**:
   - سجل الدخول إلى [Railway](https://railway.app).
   - اضغط على **+ New Project** ثم اختر **Deploy from GitHub repo**.
   - اختر مستودع `QuizArena_Laravel`.

3. **إضافة قاعدة بيانات MySQL**:
   - داخل صفحة المشروع في Railway، اضغط على زر **+ New** (أو اضغط `Ctrl + K`).
   - اختر **Database** -> **Add MySQL**.
   - ستقوم Railway بإنشاء خادم MySQL فوراً وتوفير بيانات الاتصال.

4. **ربط المتغيرات البيئية (Environment Variables)**:
   - اضغط على خدمة تطبيق Laravel في Railway، ثم انتقل لتبويب **Variables**.
   - أضف المتغيرات التالية:
     ```env
     APP_NAME=QuizArena ULTRA
     APP_ENV=production
     APP_DEBUG=false
     APP_KEY=base64:ZCMQD55lX6ZKbpxPZPdyvwpc+AWNDuF0UR8SdHA9e44=
     DB_CONNECTION=mysql
     DB_HOST=${{MySQL.MYSQLHOST}}
     DB_PORT=${{MySQL.MYSQLPORT}}
     DB_DATABASE=${{MySQL.MYSQLDATABASE}}
     DB_USERNAME=${{MySQL.MYSQLUSER}}
     DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
     RUN_MIGRATIONS=true
     RUN_DB_SEED=true
     SESSION_DRIVER=database
     QUEUE_CONNECTION=database
     CACHE_STORE=database
     ```
   *(ملاحظة: Railway يدعم صيغة `${{MySQL.MYSQLHOST}}` لجلب القيم تلقائياً من خادم MySQL الخاص بك)*.

5. **توليد رابط الدخول (Public Domain)**:
   - انتقل إلى تبويب **Settings** داخل خدمة Laravel.
   - تحت قسم **Networking** اضغط على **Generate Domain**.
   - مبروك! أصبح موقعك متاحاً ويعمل بشهادة HTTPS رسمية.

---

## 🌐 الخيار الثاني: النشر على Render.com

يدعم المشروع ملف **`render.yaml`** (Render Blueprint) المعد مسبقاً.

### الخطوات:
1. ادخل إلى لوحة تحكم [Render Dashboard](https://dashboard.render.com).
2. اضغط على **New +** ثم اختر **Blueprint**.
3. اربط مستودع GitHub الخاص بـ `QuizArena_Laravel`.
4. سيكتشف Render ملف `render.yaml` ويقوم بإنشاء الخدمة تلقائياً.
5. لقاعدة البيانات: يمكنك ربط أي خادم MySQL مجاني (مثل [Aiven](https://aiven.io) أو [TiDB Cloud](https://tidbcloud.com) أو قاعدة بيانات Railway) بوضع بيانات الاتصال في المتغيرات:
   - `DB_HOST`
   - `DB_DATABASE`
   - `DB_USERNAME`
   - `DB_PASSWORD`
   - `RUN_MIGRATIONS=true`
   - `RUN_DB_SEED=true`

---

## 🛸 الخيار الثالث: النشر عبر Fly.io

إذا كنت تفضل استخدام الـ CLI عبر سطر الأوامر:
```bash
# تسجيل الدخول وتثبيت flyctl
fly auth login

# إطلاق التطبيق
fly launch

# ربط قاعدة بيانات MySQL وضبط المفاتيح
fly secrets set APP_KEY="base64:ZCMQD55lX6ZKbpxPZPdyvwpc+AWNDuF0UR8SdHA9e44=" RUN_MIGRATIONS=true RUN_DB_SEED=true

# تشغيل النشر
fly deploy
```

---

## 🐳 الخيار الرابع: التشغيل المحلي أو عبر سيرفر خاص (Docker Compose)

إذا كنت ترغب في تشغيل الحزمة محلياً أو على خادم VPS خاص (Ubuntu / Debian):
```bash
# تشغيل التطبيق وقاعدة البيانات و phpMyAdmin بحركة واحدة:
docker compose up -d --build
```
- المنصة: `http://localhost:8000`
- لوحة phpMyAdmin: `http://localhost:8080` (المستخدم: `root`، كلمة المرور: `rootpassword`)

---

## 🔑 حسابات الدخول التجريبية الجاهزة (Demo Accounts)

عند تفعيل `RUN_DB_SEED=true` يتم تلقائياً تفعيل الحسابات التالية:

| الدور | البريد الإلكتروني | كلمة المرور | الصلاحيات |
|---|---|---|---|
| **مسؤول النظام (Admin)** | `admin@quizarena.com` | `Admin123!` | المشرف العام، لوحة التحكم، وإدارة النظام |
| **معلم (Teacher)** | `teacher@quizarena.com` | `Teacher123!` | مؤلف الاختبارات، إدارة الفصول والطلاب |
| **طالب (Student)** | `student@quizarena.com` | `Student123!` | المستوى 4، تجربة الاختبارات، المعارك 1v1 |

---

## 🩺 فحص صحة التطبيق (Health Check)
تم تزويد المنصة بنقطة فحص سحابية `/up` تضمن لخدمات الاستضافة معرفة أن الحاوية وسيرفر Nginx يعملان بكفاءة 100%.
