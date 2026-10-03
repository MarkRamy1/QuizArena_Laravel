<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\StudyPlannerController;
use App\Http\Controllers\LiveQuizController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\AiTutorController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\BattleController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\GuardianNotificationController;

// 1. Public & Guest Routes
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::post('/join-quiz', [LandingController::class, 'joinQuiz'])->name('landing.joinQuiz');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Locale Switcher
Route::get('/locale/{locale}', [ProfileController::class, 'setLocale'])->name('locale.set');

// Public Certificate Verification
Route::get('/verify/{code?}', [CertificateController::class, 'verify'])->name('certificates.verify');

// 2. Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/link-teacher', [DashboardController::class, 'linkTeacher'])->name('student.linkTeacher');
    Route::post('/student/link-teacher', [DashboardController::class, 'linkTeacher']);

    // Global Search
    Route::get('/search', [GlobalSearchController::class, 'search'])->name('search');

    // Profile & Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/info', [ProfileController::class, 'updateInfo'])->name('profile.updateInfo');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.updateAvatar');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');

    // Courses & Lessons (Requirement 12)
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::get('/courses/{course}/lessons/{lesson}', [CourseController::class, 'lesson'])->name('courses.lesson');
    Route::post('/lessons/{lesson}/complete', [CourseController::class, 'completeLesson'])->name('courses.lessons.complete');
    Route::post('/courses/{course}/lessons', [CourseController::class, 'storeLesson'])->name('courses.lessons.store');

    // Classrooms
    Route::get('/classrooms', [ClassroomController::class, 'index'])->name('classrooms.index');
    Route::get('/classrooms/create', [ClassroomController::class, 'create'])->name('classrooms.create');
    Route::post('/classrooms', [ClassroomController::class, 'store'])->name('classrooms.store');
    Route::post('/classrooms/join', [ClassroomController::class, 'joinByCode'])->name('classrooms.join');
    Route::post('/classrooms/{classroom}/request', [ClassroomController::class, 'requestJoin'])->name('classrooms.request');
    Route::post('/classrooms/{classroom}/members/{student}', [ClassroomController::class, 'handleRequest'])->name('classrooms.handleRequest');
    Route::get('/classrooms/{classroom}', [ClassroomController::class, 'show'])->name('classrooms.show');

    // Chat & Messages (Requirement 14)
    Route::post('/classrooms/{classroom}/messages', [ChatController::class, 'postClassroomMessage'])->name('classroom.messages.store');
    Route::get('/messages/{recipient?}', [ChatController::class, 'privateChat'])->name('chat.private');
    Route::post('/messages/{recipient}', [ChatController::class, 'sendPrivateMessage'])->name('chat.sendPrivate');

    // Materials
    Route::post('/classrooms/{classroom}/materials', [MaterialController::class, 'store'])->name('materials.store');
    Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');
    Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');

    // Assignments
    Route::get('/classrooms/{classroom}/assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/classrooms/{classroom}/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::post('/assignments/{assignment}/submit', [AssignmentController::class, 'submit'])->name('assignments.submit');
    Route::post('/submissions/{submission}/grade', [AssignmentController::class, 'grade'])->name('submissions.grade');
    Route::get('/assignments/{assignment}/download-ref', [AssignmentController::class, 'downloadReference'])->name('assignments.downloadReference');
    Route::get('/submissions/{submission}/download', [AssignmentController::class, 'downloadSubmission'])->name('submissions.download');

    // Quizzes, Analytics, & Export (Requirements 4, 5, 7, 17)
    Route::get('/quizzes/analytics', [QuizController::class, 'analytics'])->name('quizzes.analytics');
    Route::get('/quizzes/{quiz}/export', [QuizController::class, 'exportResults'])->name('quizzes.exportResults');
    Route::get('/quizzes', [QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/quizzes/create', [QuizController::class, 'create'])->name('quizzes.create');
    Route::post('/quizzes', [QuizController::class, 'store'])->name('quizzes.store');
    Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::post('/quizzes/{quiz}/start', [QuizController::class, 'start'])->name('quizzes.start');
    Route::get('/quizzes/{quiz}/take/{attempt}', [QuizController::class, 'take'])->name('quizzes.take');
    Route::post('/quizzes/{quiz}/submit/{attempt}', [QuizController::class, 'submitAttempt'])->name('quizzes.submitAttempt');
    Route::get('/quiz-attempts/{attempt}/results', [QuizController::class, 'results'])->name('quizzes.results');
    Route::get('/quizzes/{quiz}/download-pdf', [QuizController::class, 'downloadPdf'])->name('quizzes.downloadPdf');

    // Live Quiz (Requirement 6)
    Route::get('/live-quiz', [LiveQuizController::class, 'index'])->name('live-quiz.index');
    Route::post('/live-quiz/join', [LiveQuizController::class, 'join'])->name('live-quiz.join');
    Route::post('/live-quiz/start/{quiz}', [LiveQuizController::class, 'start'])->name('live-quiz.start');
    Route::get('/live-quiz/host/{session}', [LiveQuizController::class, 'host'])->name('live-quiz.host');
    Route::get('/live-quiz/room/{session}', [LiveQuizController::class, 'room'])->name('live-quiz.room');
    Route::post('/live-quiz/room/{session}/submit', [LiveQuizController::class, 'submitLiveAnswer'])->name('live-quiz.submitLiveAnswer');

    // Weekly Study Planner (Requirement 16)
    Route::get('/planner', [StudyPlannerController::class, 'index'])->name('planner.index');
    Route::post('/planner', [StudyPlannerController::class, 'store'])->name('planner.store');
    Route::post('/planner/{task}/toggle', [StudyPlannerController::class, 'toggle'])->name('planner.toggle');
    Route::delete('/planner/{task}', [StudyPlannerController::class, 'destroy'])->name('planner.destroy');

    // QuizArena AI (Requirement 8)
    Route::get('/ai/tutor', [AiTutorController::class, 'index'])->name('ai.tutor');
    Route::post('/ai/chat', [AiTutorController::class, 'chat'])->name('ai.chat');
    Route::get('/ai/teacher', [AiTutorController::class, 'teacherAssistant'])->name('ai.teacher');
    Route::post('/ai/teacher/generate-questions', [AiTutorController::class, 'generateQuizQuestions'])->name('ai.generateQuestions');
    Route::get('/ai/admin', [AiTutorController::class, 'adminAssistant'])->name('ai.admin');

    // Gamification & Leaderboard (Requirement 11)
    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
    Route::get('/battle', [BattleController::class, 'index'])->name('battle.index');
    Route::get('/battle/start', [BattleController::class, 'start'])->name('battle.start');
    Route::post('/battle/finish', [BattleController::class, 'finish'])->name('battle.finish');

    // Certificates (Requirement 13)
    Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/{id}', [CertificateController::class, 'show'])->name('certificates.show');

    // Notification Center (Requirement 15)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    // Feedback & Problem Reporting (Requirement 22)
    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::get('/feedback/create', [FeedbackController::class, 'create'])->name('feedback.create');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');

    // Guardian Notifications (Absence & Exam Scores)
    Route::post('/guardian/alert', [GuardianNotificationController::class, 'sendAlert'])->name('guardian.alert');
    Route::get('/guardian/alerts', [GuardianNotificationController::class, 'log'])->name('guardian.log');

    // Admin Dashboard & System Governance (Requirements 20 & 21)
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/teachers', [AdminController::class, 'teachers'])->name('admin.teachers');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/admin/users/{user}/status', [AdminController::class, 'toggleUserStatus'])->name('admin.users.toggleStatus');
    Route::post('/admin/users/{user}/role', [AdminController::class, 'changeRole'])->name('admin.users.changeRole');
    Route::get('/admin/courses', [AdminController::class, 'courses'])->name('admin.courses');
    Route::post('/admin/courses/{course}/status', [AdminController::class, 'toggleCourseStatus'])->name('admin.courses.toggleStatus');
    Route::get('/admin/quizzes', [AdminController::class, 'quizzes'])->name('admin.quizzes');
    Route::get('/admin/audit-logs', [AdminController::class, 'auditLogs'])->name('admin.auditLogs');
    Route::get('/admin/feedback', [AdminController::class, 'feedback'])->name('admin.feedback');
    Route::post('/admin/feedback/{report}/resolve', [AdminController::class, 'resolveFeedback'])->name('admin.feedback.resolve');
    Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/admin/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
});
