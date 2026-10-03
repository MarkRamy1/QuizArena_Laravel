# QuizArena ULTRA — المنصة التعليمية المتكاملة 🚀 (نسخة Laravel 12)
> **Modern Arabic-First Gamified Assessment Platform built with Laravel 12 & MySQL**

تحويل شامل ومكتمل لمشروع QuizArena ULTRA من بيئة **Node.js (NestJS & Next.js)** إلى إطار عمل **PHP / Laravel 12** مع قاعدة بيانات **MySQL (XAMPP)**، محافظاً على كافة الميزات الهندسية المتقدمة: التصحيح الخادمي الحازم، معالجة النصوص العربية، تعقب الـ Streak الذكي، سجل المعاملات المالية/النقاط الـ Idempotent، الشهادات الرقمية الموثقة، معارك الذكاء 1v1، والمساعد الذكي المراعي لنزاهة الامتحانات.

---

## 🏛️ البنية التقنية (Architecture & Stack)

- **إطار العمل الأساسي**: Laravel 12 (PHP 8.2+)
- **قاعدة البيانات**: MySQL (قاعدة بيانات `quizarena` داخل بيئة XAMPP)
- **واجهة المستخدم**: Blade Templates مع نظام تصميم ULTRA Theme بتصميم عربي RTL أصيل ودعم الوضع الداكن والفاتح
- **محرك التصحيح**: تصحيح تلقائي فوري متعدد المعايير لأسئلة الاختيار من متعدد والأسئلة المقالية القصيرة
- **معالجة اللغة العربية**: خوارزميات تطبيع الألف والتاء المربوطة والألف المقصورة وحذف التشكيل والتطويل (`ArabicHelper`)
- **نظام التلعيب (Gamification)**:
  - سجل معاملات XP غير قابلة للتكرار (`XpTransaction` مع `idempotency_key`)
  - تقييم الـ Streak اليومي المستمر وحساب المستويات التراكمية
  - إنجازات آلية الفتح (أول اختبار، نسبة 90%+، المثابر الثلاثي، جامع الشهادات)
  - مهام يومية متجددة مع إمكانية استلام المكافآت
- **شهادات الإنجاز المعتمدة**:
  - كود تحقق فريد لكل شهادة بصيغة `CERT-XXXX-XXXX`
  - صفحة تحقق علنية مستقلة `/verify/{code}`
  - تصميم رسمي قابل للطباعة مباشرة عبر المتصفح (`window.print()`)
- **معارك 1v1 التفاعلية (Battle Arena)**:
  - معارك سريعة من 3 جولات ضد منافس ذكي (AI Rival) مع احتساب النقاط بناءً على زمن الاستجابة والدقة
- **المعلم الذكي (Assessment-Aware AI Tutor)**:
  - حارس النزاهة الأكاديمية: يكتشف تلقائياً وجود أي امتحان قيد الحل ويمنع تقديم إجابات مباشرة
  - تعقب استهلاك الرموز (Tokens Telemetry) وتكاليف الاستخدام
- **واجهة برمجة التطبيقات (REST API)**:
  - مسارات مهيكلة تحت البادئة `/api/v1/...` مطابقة لكافة العقود والـ DTOs

---

## 👥 حسابات الدخول التجريبية الجاهزة (Demo Accounts)

تم تهيئة قاعدة البيانات مسبقاً (`php artisan db:seed`) بالحسابات التالية مع إمكانية الدخول بنقرة واحدة من صفحة تسجيل الدخول:

| الدور | البريد الإلكتروني | كلمة المرور | التفاصيل |
|---|---|---|---|
| **طالب (Student)** | `student@quizarena.com` | `Student123!` | المستوى 4، 1,250 XP، 5 أيام Streak |
| **طالبة متفوقة (Achiever)** | `nour@quizarena.com` | `Student123!` | المستوى 6، 1,890 XP، 12 يوماً Streak |
| **معلم (Teacher)** | `teacher@quizarena.com` | `Teacher123!` | معلم أول فيزياء وحاسب، مؤلف الاختبارات |
| **مسؤول النظام (Admin)** | `admin@quizarena.com` | `Admin123!` | المشرف العام، لوحة التحكم، وإدارة الحسابات |

---

## 🚀 التشغيل والإعداد المحلي (Local Setup)

### 1. المتطلبات:
- **XAMPP**: مع تشغيل مشغلي Apache و MySQL
- **PHP**: إصدار 8.2 أو أحدث
- **قاعدة البيانات**: إنشاء قاعدة بيانات باسم `quizarena` في phpMyAdmin أو MySQL

### 2. التثبيت والتهيئة:
```bash
# الانتقال لمجلد المشروع
cd "c:\xampp2\htdocs\New folder\QuizArena_Laravel"

# تفعيل الامتدادات وتهيئة المفتاح
php artisan key:generate

# تشغيل التهجيرات وتغذية البيانات الأولية
php artisan migrate:fresh --seed
```

### 3. تشغيل خادم التطوير:
```bash
php artisan serve
```
- الرابط المحلي: [http://localhost:8000](http://localhost:8000)
- فحص سلامة النظام: [http://localhost:8000/api/v1/health](http://localhost:8000/api/v1/health)
- التحقق من الشهادات: [http://localhost:8000/verify](http://localhost:8000/verify)

أو عبر خادم Apache المباشر في XAMPP:
[http://localhost/New%20folder/QuizArena_Laravel/public/](http://localhost/New%20folder/QuizArena_Laravel/public/)

---

## 🧪 الاختبارات الآلية (Automated Testing Suite)

يتضمن المشروع 11 اختباراً آلياً شاملاً يغطي:
- إعادة توجيه الضيوف وتسجيل الدخول
- تحميل لوحة التحكم والاختبارات
- خوارزمية معالجة النصوص العربية
- محاكي الـ Streak وتعاقب الأيام
- فحص سلامة الـ API
- دورة تقديم الامتحان كاملة من البدء وحتى التصحيح وإصدار الشهادة والتحقق منها

لتشغيل الاختبارات:
```bash
php artisan test
```
*(النتيجة: `Tests: 11 passed (34 assertions)`)*

---

## 📁 هيكل مجلدات المشروع (Directory Structure)

```
QuizArena_Laravel/
├── app/
│   ├── Helpers/              # ArabicHelper, CryptoHelper, StreakHelper
│   ├── Http/Controllers/    # Auth, Dashboard, Quiz, Attempt, Battle, AI, Admin, etc.
│   │   └── Api/              # ApiController (REST endpoints /api/v1/...)
│   └── Models/               # 28 Eloquent Models with complete relationships
├── database/
│   ├── migrations/           # Normalized database tables with foreign keys
│   └── seeders/              # Comprehensive seeder for users, quizzes, missions
├── resources/
│   └── views/                # Blade views with ULTRA RTL Design System
│       ├── layouts/          # Master layout with theme toggle & nav
│       ├── auth/             # Login with quick autofill & Register
│       ├── quizzes/          # Bank, exam detail, builder, analytics
│       ├── attempts/         # Timed exam room & detailed results breakdown
│       ├── certificates/     # Catalog, printable certificate, public verify
│       ├── battle/           # 1v1 live interactive arena vs AI
│       ├── ai/               # Assessment-aware tutor with integrity guard
│       ├── resources/        # Digital library
│       ├── leaderboard/      # Global XP ranking podium
│       ├── chat/             # Discussion lounge
│       └── admin/            # Platform KPIs & user governance
├── routes/
│   ├── web.php               # 52 organized web routes
│   └── api.php               # /api/v1 REST routes
└── tests/
    └── Feature/              # Complete Pest / PHPUnit test suite
```
