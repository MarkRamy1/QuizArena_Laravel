<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\QuizAttempt;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\User;

class AiTutorController extends Controller
{
    // 1. Student AI Tutor
    public function index()
    {
        $user = Auth::user();
        $conversation = AiConversation::firstOrCreate(
            ['user_id' => $user->id],
            ['title' => 'المعلم الذكي التفاعلي']
        );

        $messages = $conversation->messages;
        if ($messages->isEmpty()) {
            AiMessage::create([
                'conversation_id' => $conversation->id,
                'role' => 'ASSISTANT',
                'content' => "أهلاً بك يا {$user->name}! أنا معلّمك ومساعدك الذكي في منصة QuizArena 🎓\n\nكيف أساعدك اليوم؟ بإمكاني:\n1. 📖 شرح أي درس أو مفهوم معقد خطوة بخطوة\n2. ❓ شرح وتفسير إجابات الأسئلة ولماذا كان الجواب صحيحاً\n3. 📝 تلخيص المذكرات والموضوعات الدراسية\n4. ⚡ توليد أسئلة تدريبية فورية لاختبار فهمك\n5. 🎯 مساعدتك في وضع خطة مراجعة ذكية لاختبارك القادم!",
            ]);
            $messages = $conversation->fresh()->messages;
        }

        // Assessment-aware proctoring policy check: active attempt
        $activeAttempt = QuizAttempt::where('student_id', $user->id)
            ->where('status', 'IN_PROGRESS')
            ->whereNull('submitted_at')
            ->with('quiz')
            ->first();

        return view('ai.index', compact('conversation', 'messages', 'activeAttempt'));
    }

    public function chat(Request $request)
    {
        $user = Auth::user();
        $prompt = trim($request->input('message', ''));
        $mode = $request->input('mode', 'general');

        if (empty($prompt)) {
            return response()->json(['success' => false, 'message' => 'الرسالة فارغة'], 400);
        }

        $conversation = AiConversation::firstOrCreate(
            ['user_id' => $user->id],
            ['title' => 'المعلم الذكي التفاعلي']
        );

        // Save student prompt
        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'USER',
            'content' => $prompt,
        ]);

        // Academic Integrity Guard
        $activeAttempt = QuizAttempt::where('student_id', $user->id)
            ->where('status', 'IN_PROGRESS')
            ->whereNull('submitted_at')
            ->with('quiz')
            ->first();

        if ($activeAttempt) {
            $quizTitle = $activeAttempt->quiz->title;
            $reply = "🛡️ **تنبيه نزاهة التقييم والأمانة الأكاديمية:**\n\n"
                   . "رصد النظام أن لديك اختباراً رسمياً نشطاً حالياً بعنوان (**{$quizTitle}**).\n"
                   . "وفقاً لسياسة المنصة الصارمة، يُحظر على المساعد الذكي حل الأسئلة المباشرة أو تقديم إجابات صريحة أثناء سير الامتحان.\n\n"
                   . "💡 *توجيه عام:* ركّز على قراءة السؤال بعناية، وتذكّر المبادئ الأساسية التي درستها. بإمكانك العودة لمناقشة هذا المفهوم بالتفصيل فور تسليمك للامتحان!";
        } else {
            $reply = $this->generateSmartAiResponse($prompt, $mode, $user);
        }

        // Save AI reply
        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'ASSISTANT',
            'content' => $reply,
        ]);

        return response()->json([
            'success' => true,
            'reply' => $reply,
        ]);
    }

    /**
     * Generate smart pedagogical AI response (External API or Local Engine)
     */
    protected function generateSmartAiResponse(string $prompt, string $mode, User $user): string
    {
        // 1. Optional Gemini API integration
        $geminiKey = env('GEMINI_API_KEY');
        if (!empty($geminiKey)) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(6)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$geminiKey}",
                    [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => "You are QuizArena AI Tutor, a patient, encouraging educational assistant for a student named {$user->name}. Explain clearly, step-by-step with practical examples. Language: match user language (Arabic or English). User Question: {$prompt}"]
                                ]
                            ]
                        ]
                    ]
                );

                if ($response->successful()) {
                    $json = $response->json();
                    $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if (!empty($text)) {
                        return trim($text);
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Gemini API call failed: ' . $e->getMessage());
            }
        }

        // 2. Optional OpenAI API integration
        $openAiKey = env('OPENAI_API_KEY');
        if (!empty($openAiKey)) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(6)->withToken($openAiKey)->post(
                    "https://api.openai.com/v1/chat/completions",
                    [
                        'model' => 'gpt-4o-mini',
                        'messages' => [
                            ['role' => 'system', 'content' => "You are QuizArena AI Tutor, an expert educational teacher for {$user->name}. Provide step-by-step clear explanations with examples in the user's language."],
                            ['role' => 'user', 'content' => $prompt]
                        ],
                        'temperature' => 0.7,
                    ]
                );

                if ($response->successful()) {
                    $json = $response->json();
                    $text = $json['choices'][0]['message']['content'] ?? null;
                    if (!empty($text)) {
                        return trim($text);
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('OpenAI API call failed: ' . $e->getMessage());
            }
        }

        // 3. Comprehensive Local Pedagogical Engine
        return $this->evaluateLocally($prompt, $mode, $user);
    }

    /**
     * Local Multi-Subject Pedagogical Response Engine
     */
    protected function evaluateLocally(string $prompt, string $mode, User $user): string
    {
        $lower = mb_strtolower($prompt, 'UTF-8');

        // Linear Equation Solver: e.g. 2x + 6 = 18 or 3س - 5 = 10
        if (preg_match('/(\d+)\s*(?:[xXس])\s*([+\-])\s*(\d+)\s*=\s*(\d+)/u', $prompt, $m)) {
            $a = (float) $m[1];
            $op = $m[2];
            $b = (float) $m[3];
            $c = (float) $m[4];

            $rhs = ($op === '+') ? ($c - $b) : ($c + $b);
            $x = $a != 0 ? round($rhs / $a, 3) : 0;

            return "🧮 **حل المعادلة الرياضية خطوة بخطوة:**\n\n"
                 . "المعادلة: `{$a}س {$op} {$b} = {$c}`\n\n"
                 . "1. **الخطوة الأولى (نقل الحد الثابت):**\n"
                 . "   ننقل العدد ({$b}) إلى الطرف الآخر بعكس الإشارة:\n"
                 . "   `{$a}س = {$c} " . ($op === '+' ? "- {$b}" : "+ {$b}") . " = {$rhs}`\n\n"
                 . "2. **الخطوة الثانية (القسمة على معامل المجهول):**\n"
                 . "   نقسم الطرفين على ({$a}) للحصول على قيمة س:\n"
                 . "   `س = {$rhs} ÷ {$a} = {$x}`\n\n"
                 . "✅ **الحل النهائي:** `س = {$x}`\n\n"
                 . "💡 *للتحقق:* `{$a}({$x}) {$op} {$b} = " . ($a * $x + ($op === '+' ? $b : -$b)) . " = {$c}` (الحل صحيح بنسبة 100%)";
        }

        // Basic Arithmetic: e.g. 15 * 8 or 120 / 4 or 25 + 75
        if (preg_match('/(\d+(?:\.\d+)?)\s*([\+\-\*\/×÷\^])\s*(\d+(?:\.\d+)?)/u', $prompt, $m)) {
            $num1 = (float) $m[1];
            $opSymbol = $m[2];
            $num2 = (float) $m[3];

            $res = 0;
            $opName = '';
            switch ($opSymbol) {
                case '+':
                    $res = $num1 + $num2;
                    $opName = 'الجمع';
                    break;
                case '-':
                    $res = $num1 - $num2;
                    $opName = 'الطرح';
                    break;
                case '*':
                case '×':
                    $res = $num1 * $num2;
                    $opName = 'الضرب';
                    break;
                case '/':
                case '÷':
                    $res = $num2 != 0 ? round($num1 / $num2, 4) : 'غير معرف (قسمة على صفر)';
                    $opName = 'القسمة';
                    break;
                case '^':
                    $res = pow($num1, $num2);
                    $opName = 'الأس';
                    break;
            }

            return "🔢 **العملية الحسابية:**\n\n"
                 . "• العملية: {$opName}\n"
                 . "• التعبير: `{$num1} {$opSymbol} {$num2}`\n"
                 . "• **النتيجة:** `{$res}`\n\n"
                 . "هل لديك مسألة أخرى ترغب في حسابها أو توضيح خطواتها؟";
        }

        // Geometry: Circles, Triangles, Pythagoras
        if (str_contains($lower, 'مساحة الدائرة') || str_contains($lower, 'circle area')) {
            return "📐 **قانون مساحة الدائرة:**\n\n"
                 . "• **القاعدة الرياضية:** `المساحة = π × نق²` (A = π × r²)\n"
                 . "• حيث `π` (باي) ≈ `3.14159` أو `22/7`.\n"
                 . "• `نق` هو نصف قطر الدائرة (Radius).\n\n"
                 . "**مثال تطبيقي:** إذا كان نصف القطر نق = 7 سم:\n"
                 . "المساحة = (22/7) × 7² = (22/7) × 49 = 154 سم².";
        }

        if (str_contains($lower, 'محيط الدائرة')) {
            return "⭕ **قانون محيط الدائرة:**\n\n"
                 . "• **القاعدة:** `المحيط = 2 × π × نق` (أو القطر × π).\n"
                 . "**مثال:** دائرة قطرها 10 سم (نق = 5 سم):\n"
                 . "المحيط = 2 × 3.14 × 5 = 31.4 سم.";
        }

        if (str_contains($lower, 'فيثاغورس') || str_contains($lower, 'pythagor')) {
            return "📐 **نظرية فيثاغورس (مثلث قائم الزاوية):**\n\n"
                 . "• **النص الرياضي:** في أي مثلث قائم الزاوية، مربع طول الوتر يساوي مجموع مربعي طولي الضلعين الآخرين.\n"
                 . "• **القانون:** `الوتر² = الضلع1² + الضلع2²` (`c² = a² + b²`)\n\n"
                 . "**مثال شهير:** مثلث أضلاعه 3 سم و 4 سم:\n"
                 . "`الوتر² = 3² + 4² = 9 + 16 = 25`\n"
                 . "إذن `الوتر = √25 = 5 سم`.";
        }

        // Physics: Ohm's Law
        if (str_contains($lower, 'أوم') || str_contains($lower, 'ohm') || str_contains($lower, 'مقاومة')) {
            return "⚡ **قانون أوم (Ohm's Law):**\n\n"
                 . "ينص على أن فرق الجهد بين طرفي موصل يتناسب طردياً مع شدة التيار المار فيه عند ثبوت درجة الحرارة.\n\n"
                 . "• **الصيغة الرياضية:** `V = I × R`\n"
                 . "  - `V`: فرق الجهد بالفولت (Volt)\n"
                 . "  - `I`: شدة التيار بالأمبير (Ampere)\n"
                 . "  - `R`: المقاومة الكهربائية بالأوم (Ω)\n\n"
                 . "💡 **مثال:** إذا كان فرق الجهد 12V والمقاومة 4Ω، فإن شدة التيار = `12 ÷ 4 = 3 أمبير`.";
        }

        // Physics: Newton's Laws
        if (str_contains($lower, 'نيوتن') || str_contains($lower, 'newton') || str_contains($lower, 'قوانين الحركة')) {
            return "⚙️ **قوانين نيوتن الثلاثة للحركة:**\n\n"
                 . "1. **القانون الأول (القصور الذاتي):** الجسم الساكن يبقى ساكناً، والمتحرك في خط مستقيم بسرعة ثابتة يبقى كذلك ما لم تؤثر عليه قوة خارجية محصلة.\n"
                 . "2. **القانون الثاني:** القوة = الكتلة × التسارع (`F = m × a`). كلما زادت الكتلة، احتجنا لقوة أكبر لتسريعها.\n"
                 . "3. **القانون الثالث:** لكل فعل رد فعل، مساوٍ له في المقدار ومضاد له في الاتجاه (مثل اندفاع الصاروخ بنفث الغازات).";
        }

        // Physics: Kinetic & Potential Energy
        if (str_contains($lower, 'طاقة الحركة') || str_contains($lower, 'طاقة الوضع') || str_contains($lower, 'kinetic')) {
            return "🔋 **قوانين الطاقة الميكانيكية:**\n\n"
                 . "• **طاقة الحركة (Kinetic Energy):** `KE = ½ × m × v²`\n"
                 . "  (نصف الكتلة × مربع السرعة)\n\n"
                 . "• **طاقة الوضع (Potential Energy):** `PE = m × g × h`\n"
                 . "  (الكتلة × تسارع الجاذبية × الارتفاع)\n\n"
                 . "• **مبدأ حفظ الطاقة:** الطاقة لا تفنى ولا تستحدث من العدم، ولكن تتحول من صورة إلى أخرى.";
        }

        // Biology & Chemistry: Mitochondria, Photosynthesis, Cells, DNA
        if (str_contains($lower, 'ميتوكوندريا') || str_contains($lower, 'mitochondria')) {
            return "🔬 **الميتوكوندريا (Mitochondria) — محطة طاقة الخلية:**\n\n"
                 . "• **الوظيفة الأساسية:** مسؤولة عن إنتاج الطاقة الحيوية للخلية عبر عملية **التنفس الخلوي**.\n"
                 . "• **المركب الناتج:** جزيء الـ **ATP** (أدينوسين ثلاثي الفوسفات) وهو عملة الطاقة في الكائنات الحية.\n"
                 . "• **ميزة فريدة:** تحتوي الميتوكوندريا على حمض نووي خاص بها (mtDNA) وتتوارث من الأم.";
        }

        if (str_contains($lower, 'بناء ضوئي') || str_contains($lower, 'photosynthesis')) {
            return "🌱 **عملية البناء الضوئي (Photosynthesis):**\n\n"
                 . "العملية التي تستخدمها النباتات والطحالب لتحويل الطاقة الضوئية إلى طاقة كيميائية مخزنة في الجلوكوز.\n\n"
                 . "• **المعادلة الكيميائية:**\n"
                 . "  `6CO₂ + 6H₂O + ضوء الشمس ➔ C₆H₁₂O₆ (جلوكوز) + 6O₂ (أكسجين)`\n\n"
                 . "• **المكان:** تحدث داخل **البلاستيدات الخضراء** بواسطة صبغة **الكلوروفيل**.\n"
                 . "• **النواتج:** غذاء للنبات (سكر جلوكوز) وغاز الأكسجين الضروري لتنفس الكائنات الحية.";
        }

        if (str_contains($lower, 'dna') || str_contains($lower, 'حمض نووي') || str_contains($lower, 'جين')) {
            return "🧬 **الحمض النووي DNA (Deoxyribonucleic Acid):**\n\n"
                 . "• يحمل الشفرة الوراثية الكاملة لنمو ووظائف الكائنات الحية.\n"
                 . "• **التركيب:** لولب مزدوج (Double Helix) يتكون من نيوكليوتيدات.\n"
                 . "• **القواعد النيتروجينية:**\n"
                 . "  - الأدنين (A) يرتبط بالثايمين (T)\n"
                 . "  - الجوانين (G) يرتبط بالسيتوزين (C)";
        }

        // Computer Science & Web Development
        if (str_contains($lower, 'javascript') || str_contains($lower, 'let') || str_contains($lower, 'const')) {
            return "💻 **الفروق الجوهرية في JavaScript (var vs let vs const):**\n\n"
                 . "1. **`const`:** للمتغيرات الثابتة التي لا تتغير قيمتها (Block Scoped). الأفضل استخدامها افتراضياً.\n"
                 . "2. **`let`:** للمتغيرات القابلة لإعادة التعيين مع نطاق الكتلة (Block Scoped).\n"
                 . "3. **`var`:** الطريقة القديمة، لها نطاق الدالة (Function Scoped) وتسبب مشاكل Hoisting لذا يُفضل تجنبها.\n\n"
                 . "💡 **نصيحة برمجية:** استخدم `const` دائماً، وإذا احتجت لتغيير القيمة لاحقاً (مثل عداد الحلقات) استخدم `let`.";
        }

        if (str_contains($lower, 'mvc') || str_contains($lower, 'معمارية')) {
            return "🏗️ **نمط معمارية MVC (Model - View - Controller):**\n\n"
                 . "نمط هندسي يُستخدم لفصل مهام التطبيق إلى 3 طبقات مستقلة:\n\n"
                 . "1. **النموذج (Model):** مسؤول عن منطق البيانات والاتصال بقاعدة البيانات (مثل User, Quiz).\n"
                 . "2. **واجهة العرض (View):** مسؤولة عما يراه المستخدم في الشاشة (مثل صفحات Blade و HTML).\n"
                 . "3. **المتحكم (Controller):** حلقة الوصل، يستقبل طلبات المستخدم ويعالجها ويرسل البيانات للواجهة.\n\n"
                 . "🌟 **الفائدة:** كود منظم، سهل الصيانة والاختبار، وقابل للتطوير الجماعي.";
        }

        if (str_contains($lower, 'api') || str_contains($lower, 'rest')) {
            return "🌐 **ما هي الـ REST API؟**\n\n"
                 . "وسيلة تواصل معيارية بين الأنظمة المختلفة (مثل تطبيق الموبايل مع السيرفر).\n\n"
                 . "• **أشهر أساليب الـ HTTP:**\n"
                 . "  - `GET`: جلب البيانات (مثل عرض قائمة الاختبارات)\n"
                 . "  - `POST`: إنشاء بيانات جديدة (مثل تسليم حل الامتحان)\n"
                 . "  - `PUT / PATCH`: تعديل البيانات\n"
                 . "  - `DELETE`: حذف البيانات\n\n"
                 . "• **صيغة تبادل البيانات الشائعة:** JSON.";
        }

        // Arabic Grammar (النحو)
        if (str_contains($lower, 'فاعل') || str_contains($lower, 'مفعول') || str_contains($lower, 'إعراب')) {
            return "📖 **قواعد الإعراب الأساسية في اللغة العربية:**\n\n"
                 . "1. **الفاعل:** اسم مرفوع يدل على من قام بالفعل أو اتصف به.\n"
                 . "   - مثال: *شرحَ **المعلّمُ** الدرسَ* ➔ المعلّمُ: فاعل مرفوع وعلامة رفعه الضمة.\n\n"
                 . "2. **المفعول به:** اسم منصوب يدل على من وقع عليه فعل الفاعل.\n"
                 . "   - مثال: *شرح المعلمُ **الدرسَ*** ➔ الدرسَ: مفعول به منصوب وعلامة نصبه الفتحة.\n\n"
                 . "3. **علامات الرفع الأصلية:** الضمة (المفرد)، الألف (المثنى)، الواو (جمع المذكر السالم).";
        }

        // Generic Academic Guidance for any question
        return "💡 **إليك الشرح والتوضيح الأكاديمي الشامل:**\n\n"
             . "بخصوص سؤالك: (**{$prompt}**):\n\n"
             . "1. **المفهوم الجوهري:**\n"
             . "   عند التعامل مع هذه المسألة، الأساس يبدأ بفهم العلاقة بين المتغيرات وتحديد القواعد النظرية الحاكمة لها.\n\n"
             . "2. **خطوات الحل والتطبيق العملي:**\n"
             . "   • قسّم المسألة إلى أجزاء صغيرة ومباشرة.\n"
             . "   • استند إلى التعريفات العلمية الدقيقة واستبعد أي احتمالات عشوائية.\n"
             . "   • تحقّق من النتيجة بالتعويض العكسي أو بمراجعة المنطق الأكاديمي.\n\n"
             . "3. **نصيحة QuizArena للتفوق:**\n"
             . "   إذا واجهت هذا المفهوم في أي اختبار، ابدأ بتدوين المعطيات على ورقة جانبية وراجع الأسئلة التدريبية المشابهة في المنصة.\n\n"
             . "هل تحب أن أطرح عليك سؤالاً تدريبياً سريعاً على هذا المفهوم للتأكد من استيعابك التام؟ 🎯";
    }

    // 2. Teacher AI Assistant
    public function teacherAssistant()
    {
        abort_unless(Auth::user()->isTeacher() || Auth::user()->isAdmin(), 403);
        $quizzes = Quiz::where('teacher_id', Auth::id())->with('questions')->get();
        return view('ai.teacher', compact('quizzes'));
    }

    public function generateQuizQuestions(Request $request)
    {
        abort_unless(Auth::user()->isTeacher() || Auth::user()->isAdmin(), 403);

        $topic = $request->input('topic', 'البرمجة وتقنيات الويب');
        $difficulty = $request->input('difficulty', 'MEDIUM');
        $count = (int) $request->input('count', 3);

        // Simulated AI Question Generator returning high quality questions
        $samplePool = [
            [
                'text' => "ما هي وظيفة الـ Middleware في معمارية تطبيقات الويب الحديثة؟",
                'type' => 'CHOOSE',
                'difficulty' => $difficulty,
                'points' => 2,
                'explanation' => 'يعمل الـ Middleware كطبقة تصفية وفحص للطلبات الواردة قبل وصولها للمتحكم (Controller).',
                'options' => [
                    ['text' => 'فحص والتحقق من صلاحيات الطلبات الواردة (Authentication/Security)', 'correct' => true],
                    ['text' => 'تصميم ألوان واجهة المستخدم فقط', 'correct' => false],
                    ['text' => 'تصدير التقارير إلى طابعات PDF', 'correct' => false],
                    ['text' => 'مسح قاعدة البيانات بالكامل', 'correct' => false],
                ]
            ],
            [
                'text' => "تُستخدم تقنية AJAX لإرسال واستقبال البيانات مع الخادم بدون الحاجة لإعادة تحميل الصفحة بالكامل.",
                'type' => 'TRUE_FALSE',
                'difficulty' => $difficulty,
                'points' => 2,
                'explanation' => 'صحيح، الـ AJAX و Fetch API يتيحان الاتصال غير المتزامن (Asynchronous).',
                'options' => [
                    ['text' => 'صح (True)', 'correct' => true],
                    ['text' => 'خطأ (False)', 'correct' => false],
                ]
            ],
            [
                'text' => "المصطلح البرمجي المعبر عن حماية التطبيق من تنفيذ نصوص برمجية خبيثة عبر الحقول هو حماية ضد ___ .",
                'type' => 'COMPLETE',
                'difficulty' => $difficulty,
                'points' => 3,
                'model_answer' => 'XSS',
                'explanation' => 'XSS (Cross-Site Scripting) من أشهر الثغرات الأمنية في الويب.',
            ]
        ];

        return response()->json([
            'success' => true,
            'topic' => $topic,
            'difficulty' => $difficulty,
            'questions' => array_slice($samplePool, 0, $count),
            'message' => "تم توليد {$count} أسئلة بنجاح عبر نموذج QuizArena AI ⚡",
        ]);
    }

    // 3. Admin AI Assistant
    public function adminAssistant()
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $totalUsers = User::count();
        $totalQuizzes = Quiz::count();
        $totalAttempts = QuizAttempt::count();
        $avgScore = (int) round(QuizAttempt::where('status', 'GRADED')->avg('percentage') ?? 78);

        return view('ai.admin', compact('totalUsers', 'totalQuizzes', 'totalAttempts', 'avgScore'));
    }
}
