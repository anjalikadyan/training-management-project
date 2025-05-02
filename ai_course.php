<?php
// --- SETUP AND CONFIGURATION ---
session_start();
require_once 'config.php'; // Ensure config.php provides $mysqli connection

// --- Generate/Get CSRF Token ---
if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        error_log("Error generating CSRF token: " . $e->getMessage());
        die("A security error occurred. Please refresh the page.");
    }
}
$csrf_token = $_SESSION['csrf_token'];

// --- Course Configuration ---
$course_slug = 'ai-fundamentals-free'; // Make sure this matches the URL/identifier
$course_title_display = 'AI & Machine Learning Bootcamp';
$is_paid_course = false; // This is a free course

// --- User & Enrollment Status ---
$is_enrolled = false;
$user_logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['id']);
$user_id = $user_logged_in ? (int) $_SESSION['id'] : null;
$db_connection_error = false;

// Check enrollment only if user is logged in and DB connection is valid
if ($user_logged_in) {
    // Check if $mysqli exists and is a valid connection from config.php
    if (isset($mysqli) && $mysqli instanceof mysqli && !$mysqli->connect_error) {
        $stmt = $mysqli->prepare("SELECT id FROM user_courses WHERE user_id = ? AND course_slug = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("is", $user_id, $course_slug);
            if ($stmt->execute()) {
                $stmt->store_result();
                $is_enrolled = $stmt->num_rows > 0;
            } else {
                error_log("Enrollment check failed for user $user_id, course $course_slug: " . $stmt->error);
                $db_connection_error = true; // Set error flag if execute fails
            }
            $stmt->close();
        } else {
            error_log("Enrollment check prepare failed for user $user_id, course $course_slug: " . $mysqli->error);
            $db_connection_error = true; // Set error flag if prepare fails
        }
    } elseif (!isset($mysqli) || !($mysqli instanceof mysqli)) {
        error_log("Database connection (\$mysqli) not available or invalid in course page ($course_slug) for user $user_id.");
        $db_connection_error = true; // Set error flag if $mysqli is not set or invalid
    } elseif ($mysqli->connect_error) {
        error_log("Database connection error in course page ($course_slug) for user $user_id: " . $mysqli->connect_error);
        $db_connection_error = true; // Set error flag on connection error
    }
}

// --- Button Configuration Based on Enrollment Status ---
$button_config = [
    'text' => $is_paid_course ? 'Enroll Now' : 'Start Free Course',
    'tag' => 'button', // Default to button for enrollment action
    'disabled' => false,
    'classes' => 'btn-primary hover:from-primary-700 hover:to-primary-800 transform hover:scale-105', // Default primary style
    'href' => '#', // Default href
    'id' => 'enroll-btn' // Default ID for enrollment button
];

if ($is_enrolled) {
    $button_config['text'] = $is_paid_course ? '<i class="fas fa-check mr-2"></i>Enrolled' : '<i class="fas fa-play mr-2"></i>Continue Learning';
    // For free courses, make it a link to the curriculum
    if (!$is_paid_course) {
        $button_config['tag'] = 'a'; // Change tag to link
        $button_config['href'] = '#curriculum-section'; // Point to curriculum
        $button_config['classes'] = 'btn-success hover:from-green-600 hover:to-green-700'; // Green style for link
        $button_config['id'] = 'continue-learning-link'; // Specific ID for this link state
    } else {
        // For paid courses where enrolled, disable the button (no action needed)
        $button_config['disabled'] = true;
        $button_config['classes'] = 'btn-success cursor-not-allowed opacity-75'; // Keep success style but make it look disabled
    }
} elseif ($db_connection_error && $user_logged_in) { // Check if user is logged in for DB error message
    // Handle DB connection errors only if the user is logged in (otherwise they see login prompt)
    $button_config['text'] = 'Service Unavailable';
    $button_config['disabled'] = true;
    $button_config['classes'] = 'bg-gray-600 cursor-not-allowed opacity-75'; // Gray disabled style
    $button_config['tag'] = 'button'; // Ensure it's a button
}

// Helper function for safe echoing
function safe_echo($str)
{
    echo htmlspecialchars((string) ($str ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php safe_echo($course_title_display); ?> | EduPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Tailwind Config
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af' },
                        dark: { 900: '#0f172a', 800: '#1e293b', 700: '#334155', 600: '#475569', 500: '#64748b' },
                        success: { 500: '#10b981', 600: '#059669', 700: '#047857' },
                        purple: { 400: '#c084fc', 500: '#a855f7', 600: '#9333ea' },
                        green: { 400: '#4ade80', 500: '#22c55e', 600: '#16a34a' },
                        red: { 500: '#ef4444' },    // For progress bar
                        orange: { 500: '#f97316' }, // For progress bar
                        yellow: { 500: '#eab308' }  // For progress bar
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    boxShadow: {
                        'card': '0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.2)',
                        'card-hover': '0 25px 50px -12px rgba(0, 0, 0, 0.4)',
                        'inner': 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.06)'
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom CSS */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        :root {
            --scroll-margin: 100px;
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: var(--scroll-margin);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a;
            color: #e2e8f0;
        }

        .course-card {
            box-shadow: var(--tw-shadow-card);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .course-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--tw-shadow-card-hover);
        }

        .lesson-item {
            transition: all 0.2s ease;
            border: 1px solid #334155;
        }

        .lesson-item:hover {
            transform: translateX(6px);
            background-color: rgba(51, 65, 85, 0.5);
            border-color: #3b82f6;
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.1);
        }

        .lesson-checkbox:checked+label {
            color: #64748b;
        }

        .lesson-checkbox:checked+label .lesson-title {
            text-decoration: line-through;
        }

        .lesson-checkbox:checked+label .fa-check {
            display: inline-block !important;
        }

        .lesson-checkbox+label .fa-check {
            display: none;
        }

        .progress-bar {
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.6s ease;
        }

        .has-tooltip {
            position: relative;
        }

        .tooltip {
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            background-color: #1e293b;
            color: white;
            padding: 0.3rem 0.6rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            z-index: 50;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            pointer-events: none;
        }

        .has-tooltip:hover .tooltip {
            opacity: 1;
            visibility: visible;
        }

        .module-header {
            background: linear-gradient(90deg, rgba(51, 65, 85, 0.6) 0%, rgba(30, 41, 59, 0.9) 100%);
            border-left: 4px solid #2563eb;
            transition: background-color 0.2s ease;
            cursor: pointer;
        }

        .module-header:hover {
            background: linear-gradient(90deg, rgba(51, 65, 85, 0.8) 0%, rgba(30, 41, 59, 1) 100%);
        }

        .module-content {
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), padding 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            max-height: 1000px;
        }

        .module-content.hidden {
            max-height: 0;
            padding-top: 0;
            padding-bottom: 0;
            margin-top: 0;
            margin-bottom: 0;
            border-width: 0;
        }

        .accordion-icon {
            transition: transform 0.3s ease;
        }

        .accordion-icon.rotate-180 {
            transform: rotate(180deg);
        }

        .spinner {
            display: inline-block;
            border: 3px solid rgba(255, 255, 255, .3);
            border-left-color: #fff;
            border-radius: 50%;
            width: 1rem;
            height: 1rem;
            animation: spin 1s linear infinite;
        }

        .spinner.hidden {
            display: none;
        }

        .section-title {
            background: linear-gradient(to right, #e2e8f0, #94a3b8);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            -webkit-text-fill-color: transparent;
        }

        .btn-primary {
            background-image: linear-gradient(to right, #2563eb, #1d4ed8);
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3), 0 2px 4px -1px rgba(37, 99, 235, 0.1);
        }

        .btn-primary:hover:not(:disabled) {
            background-image: linear-gradient(to right, #1d4ed8, #1e40af);
        }

        .btn-success {
            background-image: linear-gradient(to right, #10b981, #059669);
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3), 0 2px 4px -1px rgba(16, 185, 129, 0.1);
        }

        .btn-success:hover:not(:disabled) {
            background-image: linear-gradient(to right, #059669, #047857);
        }

        button:disabled,
        a.disabled,
        a[disabled] {
            opacity: 0.6;
            cursor: not-allowed !important;
            pointer-events: none;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        #curriculum-section {
            scroll-margin-top: var(--scroll-margin);
        }

        .progress-container {
            height: 0.75rem;
        }

        label[for^="lesson-"] {
            display: flex;
            cursor: pointer;
            width: 100%;
        }

        label[for^="lesson-"] .fa-check,
        label[for^="lesson-"] span {
            pointer-events: none;
        }
    </style>
</head>

<body class="bg-dark-900 text-gray-200 min-h-screen">

    <!-- Navigation -->
    <nav class="bg-dark-800 border-b border-dark-700 py-3 sticky top-0 z-50 backdrop-blur-sm bg-opacity-90 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <a href="1.Home.php" class="flex items-center">
                    <i class="fas fa-graduation-cap text-primary-600 text-3xl" aria-hidden="true"></i>
                    <span class="ml-2 text-2xl font-bold text-white">EduPro</span>
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="courses_list.php"
                        class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors">Courses</a>
                    <a href="resources.php"
                        class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors">Resources</a>

                    <!-- Search and Notifications -->
                    <div class="flex items-center space-x-3">
                        <button type="button" class="p-2 rounded-full hover:bg-dark-700 transition relative has-tooltip"
                            aria-label="Search courses">
                            <i class="fas fa-search text-gray-400" aria-hidden="true"></i>
                            <span class="tooltip">Search courses</span>
                        </button>
                        <button type="button" class="p-2 rounded-full hover:bg-dark-700 transition relative has-tooltip"
                            aria-label="Notifications">
                            <i class="fas fa-bell text-gray-400" aria-hidden="true"></i>
                            <span class="tooltip">Notifications</span>
                        </button>
                    </div>

                    <!-- User Area -->
                    <?php if ($user_logged_in): ?>
                        <div class="flex items-center space-x-2">
                            <a href="Dashboard.php"
                                class="flex items-center hover:bg-dark-700 px-3 py-1 rounded-full transition relative"
                                title="Dashboard">
                                <img src="<?php echo $_SESSION['profile_image_url'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['id'] ?? 'U') . '&background=475569&color=f8fafc&size=32'; ?>"
                                    alt="Profile"
                                    class="w-8 h-8 rounded-full object-cover bg-dark-600 border border-dark-500">
                                <span id="completion-badge"
                                    class="hidden absolute -top-1 -right-1 bg-primary-600 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center ring-2 ring-dark-800">0</span>
                            </a>
                            <a href="logout.php" class="p-2 rounded-full hover:bg-dark-700 transition relative has-tooltip"
                                title="Logout">
                                <i class="fas fa-sign-out-alt text-gray-400"></i>
                                <span class="tooltip">Logout</span>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center space-x-3">
                            <a href="Login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                class="text-gray-300 hover:text-white px-3 py-1.5 rounded-md text-sm font-medium transition-colors">Log
                                In</a>
                            <a href="Signup.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-1.5 rounded-md text-sm font-medium transition-colors shadow-md">Sign
                                Up</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Course Header Section -->
        <section
            class="bg-dark-800 rounded-2xl p-6 md:p-10 mb-10 border border-dark-700 shadow-xl overflow-hidden relative">
            <div class="absolute top-0 right-0 h-full w-1/3 bg-gradient-to-l from-primary-600/10 to-transparent -z-10 opacity-50"
                aria-hidden="true"></div>
            <div class="flex flex-col md:flex-row justify-between gap-8">
                <!-- Left Column: Details -->
                <div class="md:w-2/3 z-10">
                    <span
                        class="inline-block bg-gradient-to-r from-primary-600 to-primary-700 text-white px-3 py-1 rounded-full text-xs font-bold mb-4 uppercase tracking-wider shadow-md">
                        <?php echo $is_paid_course ? 'Professional Certificate' : 'Free Course'; ?>
                    </span>
                    <h1
                        class="text-4xl lg:text-5xl font-extrabold mb-4 bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent leading-tight">
                        <?php safe_echo($course_title_display); ?>
                    </h1>
                    <!-- Course Description specific to AI -->
                    <p class="text-lg text-gray-300 mb-6 leading-relaxed">
                        Master artificial intelligence and machine learning from the ground up with Python, TensorFlow,
                        and real-world projects. Build models that solve complex problems and drive innovation.
                    </p>
                    <!-- Ratings/Info specific to AI -->
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 mb-8 text-sm text-gray-400">
                        <div class="flex items-center">
                            <div class="flex items-center text-yellow-400 mr-1.5"
                                aria-label="Rating: 4.8 out of 5 stars">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                    class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                            </div>
                            <span class="text-gray-200 font-medium">4.8</span>
                            <span class="ml-1">(3,120 ratings)</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-users mr-1.5 text-primary-500" aria-hidden="true"></i>
                            <span class="text-gray-200 font-medium">65,000+</span>
                            <span class="ml-1">students</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-sync-alt mr-1.5 text-primary-500" aria-hidden="true"></i>
                            <span>Updated April 2024</span>
                        </div>
                    </div>
                    <!-- Enrollment Action Area (Dynamically configured by PHP) -->
                    <div class="flex flex-wrap items-center gap-4" id="enrollment-action-area">
                        <<?php echo $button_config['tag']; ?>
                            id="<?php safe_echo($button_config['id']); ?>" <?php // Use dynamic ID ?>
                            <?php if ($button_config['tag'] == 'button'): ?>
                                type="button"
                                data-course-slug="<?php safe_echo($course_slug); ?>"
                                data-csrf-token="<?php safe_echo($csrf_token ?? ''); // Ensure token exists ?>"
                            <?php else: // It's an 'a' tag ?>
                                href="<?php safe_echo($button_config['href']); ?>"
                            <?php endif; ?>
                            class="text-white px-7 py-3 rounded-lg font-semibold transition duration-300 ease-in-out
                            flex items-center justify-center text-base shadow-lg <?php safe_echo($button_config['classes']); ?>"
                            <?php echo $button_config['disabled'] ? 'disabled aria-disabled="true"' : ''; // Add aria-disabled for accessibility ?>>
                            <span
                                class="btn-text"><?php echo $button_config['text']; // Output configured text/HTML ?></span>
                            <?php // Show spinner only on the non-disabled, non-enrolled *button* state ?>
                            <?php if ($button_config['tag'] == 'button' && !$button_config['disabled'] && !$is_enrolled): ?>
                                <span class="spinner hidden ml-2"></span>
                            <?php endif; ?>
                        </<?php echo $button_config['tag']; ?>>

                        <?php // Show 'Enrolled' status text only if the main action became a link (i.e., free course, enrolled) ?>
                        <?php if ($is_enrolled && !$is_paid_course): ?>
                            <span class="flex items-center text-gray-400 text-sm font-medium ml-2">
                                <i class="fas fa-check-circle mr-1.5 text-success-500" aria-hidden="true"></i> Enrolled
                            </span>
                        <?php elseif (!$is_enrolled && !$db_connection_error): // Show 'Save' button if not enrolled and no DB error ?>
                            <button type="button"
                                class="flex items-center text-gray-300 hover:text-white transition relative has-tooltip group px-4 py-3 rounded-lg hover:bg-dark-700 opacity-50 cursor-not-allowed"
                                aria-label="Save course for later (coming soon)" disabled>
                                <i class="far fa-bookmark mr-2 group-hover:text-primary-500 transition-colors"
                                    aria-hidden="true"></i> Save
                                <span class="tooltip">Save (coming soon)</span>
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- User feedback messages -->
                    <?php if (!$user_logged_in && !$db_connection_error): ?>
                        <p class="text-xs text-gray-400 mt-4">
                            <a href="Login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                class="text-primary-500 hover:underline font-medium">Log in</a> or
                            <a href="Signup.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                class="text-primary-500 hover:underline font-medium">sign up</a>
                            to <?php echo $is_paid_course ? 'enroll in this course' : 'start this free course'; ?>.
                        </p>
                    <?php elseif ($db_connection_error && $user_logged_in): ?>
                        <p class="text-xs text-red-400 mt-4 font-medium">
                            <i class="fas fa-exclamation-circle mr-1" aria-hidden="true"></i>Could not check enrollment
                            status. Please try again later.
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Right Column: Course Image -->
                <div class="md:w-1/3 mt-8 md:mt-0 flex items-center justify-center">
                    <div class="relative rounded-xl course-card overflow-hidden w-full max-w-sm">
                        <!-- Image specific to AI -->
                        <img src="https://images.unsplash.com/photo-1620712943543-bcc4688e7485?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80"
                            alt="<?php safe_echo($course_title_display); ?> course visual"
                            class="w-full h-auto aspect-[4/3] object-cover">
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent flex items-end p-5">
                            <!-- Preview link specific to AI -->
                            <a href="https://www.youtube.com/watch?v=AMxtGWcMYd4&t=296s" target="_blank"
                                rel="noopener noreferrer"
                                class="flex items-center justify-center w-14 h-14 bg-white/20 backdrop-blur-md rounded-full hover:bg-white/30 transition relative has-tooltip ring-2 ring-white/30 hover:ring-white/50"
                                aria-label="Watch course preview video">
                                <i class="fas fa-play text-white text-2xl pl-1" aria-hidden="true"></i>
                                <span class="tooltip">Watch Preview</span>
                            </a>
                            <span class="ml-4 text-white font-semibold text-lg">Course Preview</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php if ($user_logged_in): ?>
            <section class="bg-dark-800 rounded-xl p-6 mb-10 border border-dark-700 shadow-lg"
                aria-labelledby="progress-heading">
                <div class="flex flex-wrap justify-between items-center gap-4 mb-4">
                    <h2 id="progress-heading" class="text-xl font-bold flex items-center">
                        <i class="fas fa-chart-line text-primary-600 mr-3" aria-hidden="true"></i>
                        Your Learning Progress
                    </h2>
                    <span id="progress-text" class="text-sm text-gray-300 font-medium"
                        aria-live="polite">Calculating...</span>
                </div>
                <div class="w-full bg-dark-600 rounded-full h-3 mb-4 overflow-hidden shadow-inner progress-container"
                    role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                    aria-labelledby="progress-text">
                    <div id="progress-bar" class="bg-primary-600 h-full rounded-full progress-bar" style="width: 0%"></div>
                </div>
                <div class="mt-4 flex flex-wrap justify-center items-center gap-4">
                    <!-- Certificate Download Button -->
                    <a href="resource/AI-Fundamentals-Certificate.png" <?php // <-- *** CONFIRM/CHANGE PATH *** ?>
                        id="certificate-download-button" download="AI-Fundamentals-Certificate.png"
                        class="hidden inline-flex items-center justify-center px-5 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105"
                        aria-label="Download your course completion certificate">
                        <i class="fas fa-certificate mr-2"></i>
                        Download Certificate
                    </a>
                    <!-- Reset Progress Button -->
                    <button type="button" id="reset-progress-button"
                        class="inline-flex items-center justify-center px-4 py-1.5 border border-red-500 text-red-400 hover:bg-red-500/20 hover:text-red-300 text-xs font-medium rounded-lg transition duration-300 ease-in-out"
                        aria-label="Reset course progress">
                        <i class="fas fa-undo-alt mr-1.5 text-xs"></i>
                        Reset Progress
                    </button>
                </div>
            </section>
        <?php endif; ?>


        <!-- Course Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10 mb-12">
            <!-- Main Content Column -->
            <div class="lg:col-span-2 space-y-10">

                <!-- What You'll Learn (AI Specific) -->
                <section class="bg-dark-800 rounded-xl p-6 border border-dark-700 shadow-lg"
                    aria-labelledby="learn-heading">
                    <h2 id="learn-heading" class="text-2xl font-bold mb-6 section-title">What You'll Learn</h2>
                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-gray-300">
                        <li class="flex items-start"><i
                                class="fas fa-check-circle text-primary-500 mt-1 mr-3 flex-shrink-0"
                                aria-hidden="true"></i><span>Understand core AI and ML concepts</span></li>
                        <li class="flex items-start"><i
                                class="fas fa-check-circle text-primary-500 mt-1 mr-3 flex-shrink-0"
                                aria-hidden="true"></i><span>Implement algorithms using Python, NumPy, Pandas</span>
                        </li>
                        <li class="flex items-start"><i
                                class="fas fa-check-circle text-primary-500 mt-1 mr-3 flex-shrink-0"
                                aria-hidden="true"></i><span>Build and train models with TensorFlow/Keras</span></li>
                        <li class="flex items-start"><i
                                class="fas fa-check-circle text-primary-500 mt-1 mr-3 flex-shrink-0"
                                aria-hidden="true"></i><span>Apply supervised and unsupervised learning
                                techniques</span></li>
                        <li class="flex items-start"><i
                                class="fas fa-check-circle text-primary-500 mt-1 mr-3 flex-shrink-0"
                                aria-hidden="true"></i><span>Evaluate model performance effectively</span></li>
                        <li class="flex items-start"><i
                                class="fas fa-check-circle text-primary-500 mt-1 mr-3 flex-shrink-0"
                                aria-hidden="true"></i><span>Work with CNNs and RNNs for specific tasks</span></li>
                    </ul>
                </section>

                <!-- Requirements (AI Specific) -->
                <section class="bg-dark-800 rounded-xl p-6 border border-dark-700 shadow-lg"
                    aria-labelledby="requirements-heading">
                    <h2 id="requirements-heading" class="text-2xl font-bold mb-5 section-title">Requirements</h2>
                    <ul class="space-y-2 text-gray-300">
                        <li class="flex items-start"><i class="fas fa-code text-primary-500 mt-1 mr-3 flex-shrink-0"
                                aria-hidden="true"></i><span>Basic Python programming knowledge</span></li>
                        <li class="flex items-start"><i
                                class="fas fa-calculator text-primary-500 mt-1 mr-3 flex-shrink-0"
                                aria-hidden="true"></i><span>Understanding of basic mathematical concepts (linear
                                algebra, calculus helpful)</span></li>
                        <li class="flex items-start"><i class="fas fa-laptop text-primary-500 mt-1 mr-3 flex-shrink-0"
                                aria-hidden="true"></i><span>A computer with Python and necessary libraries installed
                                (Anaconda recommended)</span></li>
                    </ul>
                </section>

                <!-- Curriculum Section (AI Specific Content, Template Structure) -->
                <section class="bg-dark-800 rounded-xl p-6 border border-dark-700 shadow-lg" id="curriculum-section"
                    aria-labelledby="curriculum-heading">
                    <h2 id="curriculum-heading" class="text-2xl font-bold mb-6 section-title">Course Curriculum</h2>

                    <!-- Module 1: Python for AI -->
                    <div class="mb-6 border-b border-dark-700 pb-6">
                        <button type="button"
                            class="module-header flex items-center justify-between w-full mb-4 px-4 py-3 rounded-lg cursor-pointer text-left"
                            data-module="1" aria-expanded="true" aria-controls="module-content-1">
                            <h3 class="text-xl font-semibold text-primary-400">Module 1: Python for AI</h3>
                            <div class="flex items-center space-x-3">
                                <span class="text-sm text-gray-400 bg-dark-700 px-2 py-0.5 rounded">4 lessons</span>
                                <i class="fas fa-chevron-down accordion-icon text-gray-400" aria-hidden="true"></i>
                            </div>
                        </button>
                        <div class="module-content space-y-3 pl-4 border-l-2 border-dark-600 ml-2" id="module-content-1"
                            role="region">
                            <!-- Lesson 1.1 -->
                            <div
                                class="lesson-item flex items-center justify-between bg-dark-800 p-3 rounded-lg border border-dark-600 hover:border-primary-600/50">
                                <div class="flex items-center flex-1 mr-3">
                                    <input type="checkbox" id="lesson-1-ml" class="lesson-checkbox hidden"
                                        aria-labelledby="lesson-title-1-ml">
                                    <label for="lesson-1-ml"
                                        class="flex items-center cursor-pointer text-gray-200 hover:text-white w-full">
                                        <span
                                            class="w-5 h-5 border border-dark-600 rounded mr-3 flex items-center justify-center flex-shrink-0 transition duration-200"
                                            aria-hidden="true">
                                            <i class="fas fa-check text-xs text-primary-600 hidden"></i>
                                        </span>
                                        <span class="lesson-title" id="lesson-title-1-ml">Python Fundamentals for Data
                                            Science</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3 flex-shrink-0">
                                    <span class="text-xs text-gray-400 mr-2">30 min</span>
                                    <a href="https://www.youtube.com/watch?v=ad79nYk2keg" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-sm relative has-tooltip"
                                        data-lesson-id="lesson-1-ml"
                                        aria-label="Start Lesson: Python Fundamentals for Data Science">
                                        <i class="fas fa-play-circle text-lg" aria-hidden="true"></i>
                                        <span class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <!-- Lesson 1.2 -->
                            <div
                                class="lesson-item flex items-center justify-between bg-dark-800 p-3 rounded-lg border border-dark-600 hover:border-primary-600/50">
                                <div class="flex items-center flex-1 mr-3">
                                    <input type="checkbox" id="lesson-2-ml" class="lesson-checkbox hidden"
                                        aria-labelledby="lesson-title-2-ml">
                                    <label for="lesson-2-ml"
                                        class="flex items-center cursor-pointer text-gray-200 hover:text-white w-full">
                                        <span
                                            class="w-5 h-5 border border-dark-600 rounded mr-3 flex items-center justify-center flex-shrink-0 transition duration-200"
                                            aria-hidden="true"><i
                                                class="fas fa-check text-xs text-primary-600 hidden"></i></span>
                                        <span class="lesson-title" id="lesson-title-2-ml">NumPy and Pandas
                                            Mastery</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3 flex-shrink-0">
                                    <span class="text-xs text-gray-400 mr-2">45 min</span>
                                    <a href="https://www.youtube.com/watch?v=3qRJfUv7W_Y" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-sm relative has-tooltip"
                                        data-lesson-id="lesson-2-ml"
                                        aria-label="Start Lesson: NumPy and Pandas Mastery">
                                        <i class="fas fa-play-circle text-lg" aria-hidden="true"></i><span
                                            class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <!-- Lesson 1.3 -->
                            <div
                                class="lesson-item flex items-center justify-between bg-dark-800 p-3 rounded-lg border border-dark-600 hover:border-primary-600/50">
                                <div class="flex items-center flex-1 mr-3">
                                    <input type="checkbox" id="lesson-3-ml" class="lesson-checkbox hidden"
                                        aria-labelledby="lesson-title-3-ml">
                                    <label for="lesson-3-ml"
                                        class="flex items-center cursor-pointer text-gray-200 hover:text-white w-full">
                                        <span
                                            class="w-5 h-5 border border-dark-600 rounded mr-3 flex items-center justify-center flex-shrink-0 transition duration-200"
                                            aria-hidden="true"><i
                                                class="fas fa-check text-xs text-primary-600 hidden"></i></span>
                                        <span class="lesson-title" id="lesson-title-3-ml">Data Visualization with
                                            Matplotlib & Seaborn</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3 flex-shrink-0">
                                    <span class="text-xs text-gray-400 mr-2">40 min</span>
                                    <a href="https://www.youtube.com/watch?v=hQBrL9UxBrM" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-sm relative has-tooltip"
                                        data-lesson-id="lesson-3-ml" aria-label="Start Lesson: Data Visualization">
                                        <i class="fas fa-play-circle text-lg" aria-hidden="true"></i><span
                                            class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <!-- Lesson 1.4 -->
                            <div
                                class="lesson-item flex items-center justify-between bg-dark-800 p-3 rounded-lg border border-dark-600 hover:border-primary-600/50">
                                <div class="flex items-center flex-1 mr-3">
                                    <input type="checkbox" id="lesson-4-ml" class="lesson-checkbox hidden"
                                        aria-labelledby="lesson-title-4-ml">
                                    <label for="lesson-4-ml"
                                        class="flex items-center cursor-pointer text-gray-200 hover:text-white w-full">
                                        <span
                                            class="w-5 h-5 border border-dark-600 rounded mr-3 flex items-center justify-center flex-shrink-0 transition duration-200"
                                            aria-hidden="true"><i
                                                class="fas fa-check text-xs text-primary-600 hidden"></i></span>
                                        <span class="lesson-title" id="lesson-title-4-ml">Working with APIs and Web
                                            Scraping</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3 flex-shrink-0">
                                    <span class="text-xs text-gray-400 mr-2">50 min</span>
                                    <!-- Placeholder link -->
                                    <a href="https://www.youtube.com/watch?v=cOdPVpM1WSU"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-sm relative has-tooltip"
                                        data-lesson-id="lesson-4-ml" aria-label="Start Lesson: APIs and Web Scraping">
                                        <i class="fas fa-play-circle text-lg" aria-hidden="true"></i><span
                                            class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Module 2: Machine Learning Basics -->
                    <div class="mb-6 border-b border-dark-700 pb-6">
                        <button type="button"
                            class="module-header flex items-center justify-between w-full mb-4 px-4 py-3 rounded-lg cursor-pointer text-left"
                            data-module="2" aria-expanded="false" aria-controls="module-content-2"
                            style="border-left-color: #a855f7;">
                            <h3 class="text-xl font-semibold text-purple-400">Module 2: Machine Learning Basics</h3>
                            <div class="flex items-center space-x-3">
                                <span class="text-sm text-gray-400 bg-dark-700 px-2 py-0.5 rounded">5 lessons</span>
                                <i class="fas fa-chevron-down accordion-icon text-gray-400 rotate-180"
                                    aria-hidden="true"></i>
                            </div>
                        </button>
                        <div class="module-content space-y-3 pl-4 border-l-2 border-purple-500/50 ml-2 hidden"
                            id="module-content-2" role="region">
                            <!-- Lesson 2.1 -->
                            <div
                                class="lesson-item flex items-center justify-between bg-dark-800 p-3 rounded-lg border border-dark-600 hover:border-primary-600/50">
                                <div class="flex items-center flex-1 mr-3">
                                    <input type="checkbox" id="lesson-5-ml" class="lesson-checkbox hidden"
                                        aria-labelledby="lesson-title-5-ml">
                                    <label for="lesson-5-ml"
                                        class="flex items-center cursor-pointer text-gray-200 hover:text-white w-full">
                                        <span
                                            class="w-5 h-5 border border-dark-600 rounded mr-3 flex items-center justify-center flex-shrink-0 transition duration-200"
                                            aria-hidden="true"><i
                                                class="fas fa-check text-xs text-primary-600 hidden"></i></span>
                                        <span class="lesson-title" id="lesson-title-5-ml">Supervised vs Unsupervised
                                            Learning</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3 flex-shrink-0">
                                    <span class="text-xs text-gray-400 mr-2">35 min</span>
                                    <a href="https://www.youtube.com/watch?v=fM8XdC1EweU" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-sm relative has-tooltip"
                                        data-lesson-id="lesson-5-ml"
                                        aria-label="Start Lesson: Supervised vs Unsupervised Learning">
                                        <i class="fas fa-play-circle text-lg" aria-hidden="true"></i><span
                                            class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <!-- Lesson 2.2 -->
                            <div
                                class="lesson-item flex items-center justify-between bg-dark-800 p-3 rounded-lg border border-dark-600 hover:border-primary-600/50">
                                <div class="flex items-center flex-1 mr-3">
                                    <input type="checkbox" id="lesson-6-ml" class="lesson-checkbox hidden"
                                        aria-labelledby="lesson-title-6-ml">
                                    <label for="lesson-6-ml"
                                        class="flex items-center cursor-pointer text-gray-200 hover:text-white w-full">
                                        <span
                                            class="w-5 h-5 border border-dark-600 rounded mr-3 flex items-center justify-center flex-shrink-0 transition duration-200"
                                            aria-hidden="true"><i
                                                class="fas fa-check text-xs text-primary-600 hidden"></i></span>
                                        <span class="lesson-title" id="lesson-title-6-ml">Regression Models (Linear,
                                            Polynomial)</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3 flex-shrink-0">
                                    <span class="text-xs text-gray-400 mr-2">50 min</span>
                                    <a href="https://www.youtube.com/watch?v=cHT-qLnRm0E&t=101s" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-sm relative has-tooltip"
                                        data-lesson-id="lesson-6-ml" aria-label="Start Lesson: Regression Models">
                                        <i class="fas fa-play-circle text-lg" aria-hidden="true"></i><span
                                            class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <!-- Lesson 2.3 -->
                            <div
                                class="lesson-item flex items-center justify-between bg-dark-800 p-3 rounded-lg border border-dark-600 hover:border-primary-600/50">
                                <div class="flex items-center flex-1 mr-3">
                                    <input type="checkbox" id="lesson-7-ml" class="lesson-checkbox hidden"
                                        aria-labelledby="lesson-title-7-ml">
                                    <label for="lesson-7-ml"
                                        class="flex items-center cursor-pointer text-gray-200 hover:text-white w-full">
                                        <span
                                            class="w-5 h-5 border border-dark-600 rounded mr-3 flex items-center justify-center flex-shrink-0 transition duration-200"
                                            aria-hidden="true"><i
                                                class="fas fa-check text-xs text-primary-600 hidden"></i></span>
                                        <span class="lesson-title" id="lesson-title-7-ml">Classification Algorithms
                                            (Logistic, KNN, SVM)</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3 flex-shrink-0">
                                    <span class="text-xs text-gray-400 mr-2">55 min</span>
                                    <a href="https://www.youtube.com/watch?v=O1nWXTXcCwI&t=56s" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-sm relative has-tooltip"
                                        data-lesson-id="lesson-7-ml"
                                        aria-label="Start Lesson: Classification Algorithms">
                                        <i class="fas fa-play-circle text-lg" aria-hidden="true"></i><span
                                            class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <!-- Lesson 2.4 -->
                            <div
                                class="lesson-item flex items-center justify-between bg-dark-800 p-3 rounded-lg border border-dark-600 hover:border-primary-600/50">
                                <div class="flex items-center flex-1 mr-3">
                                    <input type="checkbox" id="lesson-8-ml" class="lesson-checkbox hidden"
                                        aria-labelledby="lesson-title-8-ml">
                                    <label for="lesson-8-ml"
                                        class="flex items-center cursor-pointer text-gray-200 hover:text-white w-full">
                                        <span
                                            class="w-5 h-5 border border-dark-600 rounded mr-3 flex items-center justify-center flex-shrink-0 transition duration-200"
                                            aria-hidden="true"><i
                                                class="fas fa-check text-xs text-primary-600 hidden"></i></span>
                                        <span class="lesson-title" id="lesson-title-8-ml">Clustering Techniques
                                            (K-Means, Hierarchical)</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3 flex-shrink-0">
                                    <span class="text-xs text-gray-400 mr-2">45 min</span>
                                    <!-- Placeholder link -->
                                    <a href="https://www.youtube.com/watch?v=QXOkPvFM6NU"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-sm relative has-tooltip"
                                        data-lesson-id="lesson-8-ml" aria-label="Start Lesson: Clustering Techniques">
                                        <i class="fas fa-play-circle text-lg" aria-hidden="true"></i><span
                                            class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <!-- Lesson 2.5 -->
                            <div
                                class="lesson-item flex items-center justify-between bg-dark-800 p-3 rounded-lg border border-dark-600 hover:border-primary-600/50">
                                <div class="flex items-center flex-1 mr-3">
                                    <input type="checkbox" id="lesson-9-ml" class="lesson-checkbox hidden"
                                        aria-labelledby="lesson-title-9-ml">
                                    <label for="lesson-9-ml"
                                        class="flex items-center cursor-pointer text-gray-200 hover:text-white w-full">
                                        <span
                                            class="w-5 h-5 border border-dark-600 rounded mr-3 flex items-center justify-center flex-shrink-0 transition duration-200"
                                            aria-hidden="true"><i
                                                class="fas fa-check text-xs text-primary-600 hidden"></i></span>
                                        <span class="lesson-title" id="lesson-title-9-ml">Model Evaluation & Selection
                                            (Metrics, Cross-Validation)</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3 flex-shrink-0">
                                    <span class="text-xs text-gray-400 mr-2">40 min</span>
                                    <!-- Placeholder link -->
                                    <a href="https://www.youtube.com/watch?v=fSytzGwwBVw"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-sm relative has-tooltip"
                                        data-lesson-id="lesson-9-ml" aria-label="Start Lesson: Model Evaluation">
                                        <i class="fas fa-play-circle text-lg" aria-hidden="true"></i><span
                                            class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Module 3: Deep Learning -->
                    <div class="mb-6 border-b border-dark-700 pb-6">
                        <button type="button"
                            class="module-header flex items-center justify-between w-full mb-4 px-4 py-3 rounded-lg cursor-pointer text-left"
                            data-module="3" aria-expanded="false" aria-controls="module-content-3"
                            style="border-left-color: #22c55e;">
                            <h3 class="text-xl font-semibold text-green-400">Module 3: Deep Learning</h3>
                            <div class="flex items-center space-x-3">
                                <span class="text-sm text-gray-400 bg-dark-700 px-2 py-0.5 rounded">4 lessons</span>
                                <i class="fas fa-chevron-down accordion-icon text-gray-400 rotate-180"
                                    aria-hidden="true"></i>
                            </div>
                        </button>
                        <div class="module-content space-y-3 pl-4 border-l-2 border-green-500/50 ml-2 hidden"
                            id="module-content-3" role="region">
                            <!-- Lesson 3.1 -->
                            <div
                                class="lesson-item flex items-center justify-between bg-dark-800 p-3 rounded-lg border border-dark-600 hover:border-primary-600/50">
                                <div class="flex items-center flex-1 mr-3">
                                    <input type="checkbox" id="lesson-10-ml" class="lesson-checkbox hidden"
                                        aria-labelledby="lesson-title-10-ml">
                                    <label for="lesson-10-ml"
                                        class="flex items-center cursor-pointer text-gray-200 hover:text-white w-full">
                                        <span
                                            class="w-5 h-5 border border-dark-600 rounded mr-3 flex items-center justify-center flex-shrink-0 transition duration-200"
                                            aria-hidden="true"><i
                                                class="fas fa-check text-xs text-primary-600 hidden"></i></span>
                                        <span class="lesson-title" id="lesson-title-10-ml">Neural Networks Fundamentals
                                            (ANNs)</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3 flex-shrink-0">
                                    <span class="text-xs text-gray-400 mr-2">45 min</span>
                                    <a href="https://www.youtube.com/watch?v=EYeF2e2IKEo" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-sm relative has-tooltip"
                                        data-lesson-id="lesson-10-ml"
                                        aria-label="Start Lesson: Neural Networks Fundamentals">
                                        <i class="fas fa-play-circle text-lg" aria-hidden="true"></i><span
                                            class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <!-- Lesson 3.2 -->
                            <div
                                class="lesson-item flex items-center justify-between bg-dark-800 p-3 rounded-lg border border-dark-600 hover:border-primary-600/50">
                                <div class="flex items-center flex-1 mr-3">
                                    <input type="checkbox" id="lesson-11-ml" class="lesson-checkbox hidden"
                                        aria-labelledby="lesson-title-11-ml">
                                    <label for="lesson-11-ml"
                                        class="flex items-center cursor-pointer text-gray-200 hover:text-white w-full">
                                        <span
                                            class="w-5 h-5 border border-dark-600 rounded mr-3 flex items-center justify-center flex-shrink-0 transition duration-200"
                                            aria-hidden="true"><i
                                                class="fas fa-check text-xs text-primary-600 hidden"></i></span>
                                        <span class="lesson-title" id="lesson-title-11-ml">Building Models with
                                            TensorFlow & Keras</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3 flex-shrink-0">
                                    <span class="text-xs text-gray-400 mr-2">60 min</span>
                                    <!-- Placeholder link -->
                                    <a href="https://www.youtube.com/watch?v=8Nl-of5C3uA"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-sm relative has-tooltip"
                                        data-lesson-id="lesson-11-ml"
                                        aria-label="Start Lesson: Building Models with TensorFlow/Keras">
                                        <i class="fas fa-play-circle text-lg" aria-hidden="true"></i><span
                                            class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <!-- Lesson 3.3 -->
                            <div
                                class="lesson-item flex items-center justify-between bg-dark-800 p-3 rounded-lg border border-dark-600 hover:border-primary-600/50">
                                <div class="flex items-center flex-1 mr-3">
                                    <input type="checkbox" id="lesson-12-ml" class="lesson-checkbox hidden"
                                        aria-labelledby="lesson-title-12-ml">
                                    <label for="lesson-12-ml"
                                        class="flex items-center cursor-pointer text-gray-200 hover:text-white w-full">
                                        <span
                                            class="w-5 h-5 border border-dark-600 rounded mr-3 flex items-center justify-center flex-shrink-0 transition duration-200"
                                            aria-hidden="true"><i
                                                class="fas fa-check text-xs text-primary-600 hidden"></i></span>
                                        <span class="lesson-title" id="lesson-title-12-ml">Convolutional Neural Networks
                                            (CNNs) for Images</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3 flex-shrink-0">
                                    <span class="text-xs text-gray-400 mr-2">75 min</span>
                                    <a href="https://www.youtube.com/watch?v=QzY57FaENXg" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-sm relative has-tooltip"
                                        data-lesson-id="lesson-12-ml"
                                        aria-label="Start Lesson: Convolutional Neural Networks (CNNs)">
                                        <i class="fas fa-play-circle text-lg" aria-hidden="true"></i><span
                                            class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <!-- Lesson 3.4 -->
                            <div
                                class="lesson-item flex items-center justify-between bg-dark-800 p-3 rounded-lg border border-dark-600 hover:border-primary-600/50">
                                <div class="flex items-center flex-1 mr-3">
                                    <input type="checkbox" id="lesson-13-ml" class="lesson-checkbox hidden"
                                        aria-labelledby="lesson-title-13-ml">
                                    <label for="lesson-13-ml"
                                        class="flex items-center cursor-pointer text-gray-200 hover:text-white w-full">
                                        <span
                                            class="w-5 h-5 border border-dark-600 rounded mr-3 flex items-center justify-center flex-shrink-0 transition duration-200"
                                            aria-hidden="true"><i
                                                class="fas fa-check text-xs text-primary-600 hidden"></i></span>
                                        <span class="lesson-title" id="lesson-title-13-ml">Recurrent Neural Networks
                                            (RNNs) for Sequences</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3 flex-shrink-0">
                                    <span class="text-xs text-gray-400 mr-2">70 min</span>
                                    <!-- Placeholder link -->
                                    <a href="https://www.youtube.com/watch?v=AsNTP8Kwu80"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-sm relative has-tooltip"
                                        data-lesson-id="lesson-13-ml"
                                        aria-label="Start Lesson: Recurrent Neural Networks (RNNs)">
                                        <i class="fas fa-play-circle text-lg" aria-hidden="true"></i><span
                                            class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Modules -->
                    <!-- Placeholder for more modules -->
                    <div class="text-center mt-6">
                        <button type="button"
                            class="text-primary-500 hover:text-primary-400 font-medium text-sm opacity-75 cursor-not-allowed"
                            disabled aria-disabled="true">
                            Show all modules (Coming Soon) <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </button>
                    </div>
                </section>

                <!-- Instructor Section (AI Specific) -->
                <section class="bg-dark-800 rounded-xl p-6 border border-dark-700 shadow-lg"
                    aria-labelledby="instructor-heading">
                    <h2 id="instructor-heading" class="text-2xl font-bold mb-6 section-title">About the Instructor</h2>
                    <div class="flex flex-col md:flex-row items-start">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Instructor Dr. Sarah Johnson"
                            class="w-24 h-24 rounded-full object-cover mb-4 md:mb-0 md:mr-6 border-4 border-primary-600/30 shadow-md flex-shrink-0">
                        <div>
                            <h3 class="text-xl font-bold mb-1 text-white">Dr. Sarah Johnson</h3>
                            <p class="text-primary-500 mb-3 font-medium">AI Research Scientist | EduPro Lead Instructor
                            </p>
                            <p class="text-gray-300 mb-4 text-sm leading-relaxed">
                                Former AI researcher at a leading tech firm with a PhD in Machine Learning.
                                Dr. Johnson has published numerous papers in top-tier conferences and brings both
                                cutting-edge research
                                and practical industry experience to this comprehensive bootcamp.
                            </p>
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-gray-400 mt-4">
                                <div class="flex items-center"><i class="fas fa-star text-yellow-400 mr-1.5"
                                        aria-hidden="true"></i><span class="font-medium text-gray-200">4.9</span><span
                                        class="ml-1">Instructor Rating</span></div>
                                <div class="flex items-center"><i class="fas fa-user-graduate text-primary-500 mr-1.5"
                                        aria-hidden="true"></i><span
                                        class="font-medium text-gray-200">18,200</span><span
                                        class="ml-1">Students</span></div>
                                <div class="flex items-center"><i class="fas fa-play-circle text-primary-500 mr-1.5"
                                        aria-hidden="true"></i><span class="font-medium text-gray-200">6</span><span
                                        class="ml-1">Courses</span></div>
                            </div>
                        </div>
                    </div>
                </section>
            </div><!-- End Main Content Column -->

            <!-- Sidebar Column -->
            <aside class="lg:col-span-1">
                <div class="sticky top-[calc(var(--scroll-margin)+1rem)] space-y-8">

                    <!-- Includes Section (AI Specific Numbers) -->
                    <div class="bg-dark-800 rounded-xl p-6 border border-dark-700 shadow-lg"
                        aria-labelledby="includes-heading-sidebar">
                        <h3 id="includes-heading-sidebar" class="text-lg font-bold mb-5 text-white">This Course
                            Includes:</h3>
                        <ul class="space-y-3.5 text-gray-300 text-sm">
                            <li class="flex items-start"><i
                                    class="fas fa-video text-primary-500 mt-1 mr-3 flex-shrink-0 w-4 text-center"
                                    aria-hidden="true"></i><span>60 hours on-demand video</span></li>
                            <li class="flex items-start"><i
                                    class="fas fa-file-alt text-primary-500 mt-1 mr-3 flex-shrink-0 w-4 text-center"
                                    aria-hidden="true"></i><span>45 downloadable resources & notebooks</span></li>
                            <li class="flex items-start"><i
                                    class="fas fa-laptop-code text-primary-500 mt-1 mr-3 flex-shrink-0 w-4 text-center"
                                    aria-hidden="true"></i><span>25 hands-on coding exercises</span></li>
                            <li class="flex items-start"><i
                                    class="fas fa-certificate text-primary-500 mt-1 mr-3 flex-shrink-0 w-4 text-center"
                                    aria-hidden="true"></i><span>Certificate of completion</span></li>
                            <li class="flex items-start"><i
                                    class="fas fa-infinity text-primary-500 mt-1 mr-3 flex-shrink-0 w-4 text-center"
                                    aria-hidden="true"></i><span>Full lifetime access</span></li>
                            <li class="flex items-start"><i
                                    class="fas fa-mobile-alt text-primary-500 mt-1 mr-3 flex-shrink-0 w-4 text-center"
                                    aria-hidden="true"></i><span>Access on mobile and TV</span></li>
                            <li class="flex items-start"><i
                                    class="fas fa-comments text-primary-500 mt-1 mr-3 flex-shrink-0 w-4 text-center"
                                    aria-hidden="true"></i><span>Instructor Q&A forum access</span></li>
                        </ul>
                    </div>

                    <!-- Resources Section (AI Specific Links) -->
                    <div class="bg-dark-800 rounded-xl p-6 border border-dark-700 shadow-lg"
                        aria-labelledby="resources-heading-sidebar">
                        <h3 id="resources-heading-sidebar" class="text-lg font-bold mb-5 text-white">Resources</h3>
                        <div class="space-y-3">
                            <a href="https://sabiod.lis-lab.fr/pub/machinelearningAIDeep_resume.pdf" target="_blank"
                                rel="noopener noreferrer"
                                class="flex items-center p-3 hover:bg-dark-700 rounded-lg transition group">
                                <div
                                    class="bg-primary-600/20 text-primary-500 group-hover:text-primary-400 p-2 rounded-lg mr-3 transition flex-shrink-0 w-10 h-10 flex items-center justify-center">
                                    <i class="fas fa-file-pdf text-xl" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-200 group-hover:text-white transition text-sm">
                                        AI/ML Cheat Sheets</h4>
                                    <p class="text-xs text-gray-400">PDF • 2.5MB</p>
                                </div>
                            </a>
                            <a href="https://www.kaggle.com/datasets" target="_blank" rel="noopener noreferrer"
                                class="flex items-center p-3 hover:bg-dark-700 rounded-lg transition group">
                                <div
                                    class="bg-purple-600/20 text-purple-400 group-hover:text-purple-300 p-2 rounded-lg mr-3 transition flex-shrink-0 w-10 h-10 flex items-center justify-center">
                                    <i class="fas fa-database text-xl" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-200 group-hover:text-white transition text-sm">
                                        Sample Datasets (Kaggle)</h4>
                                    <p class="text-xs text-gray-400">External Link</p>
                                </div>
                            </a>
                            <a href="https://chat.whatsapp.com/CqW46y9Fkgh6RT09xO8ylV" target="_blank"
                                rel="noopener noreferrer"
                                class="flex items-center p-3 hover:bg-dark-700 rounded-lg transition group">
                                <div
                                    class="bg-green-600/20 text-green-400 group-hover:text-green-300 p-2 rounded-lg mr-3 transition flex-shrink-0 w-10 h-10 flex items-center justify-center">
                                    <i class="fab fa-whatsapp text-xl" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-200 group-hover:text-white transition text-sm">Join
                                        AI Community</h4>
                                    <p class="text-xs text-gray-400">WhatsApp Group</p>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Students Also Viewed Section (Links updated) -->
                    <div class="bg-dark-800 rounded-xl p-6 border border-dark-700 shadow-lg"
                        aria-labelledby="related-heading">
                        <h3 id="related-heading" class="text-lg font-bold mb-5 text-white">Students Also Viewed</h3>
                        <div class="space-y-4">
                            <a href="js.php" <?php // Link to the actual JS course file ?>
                                class="flex items-center hover:bg-dark-700 p-3 -m-3 rounded-lg transition group">
                                <img src="https://images.unsplash.com/photo-1579468118864-1b9ea3c0db4a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80"
                                    alt="Modern JavaScript Mastery Course"
                                    class="w-16 h-12 rounded-lg object-cover mr-4 flex-shrink-0 shadow-md">
                                <div>
                                    <h4
                                        class="font-medium text-gray-200 group-hover:text-white transition text-sm leading-tight">
                                        Modern JavaScript Mastery</h4>
                                    <div class="flex items-center text-xs text-gray-400 mt-1"><i
                                            class="fas fa-star text-yellow-400 mr-1 text-xs"
                                            aria-hidden="true"></i><span>4.8 • 42k students</span></div>
                                </div>
                            </a>
                            <a href="Data%20Science%20free.php" <?php // Link to the actual Data Science course file ?>
                                class="flex items-center hover:bg-dark-700 p-3 -m-3 rounded-lg transition group">
                                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&crop=entropy&cs=tinysrgb&w=200&fit=max&q=80"
                                    alt="Data Science with Python Course"
                                    class="w-16 h-12 rounded-lg object-cover mr-4 flex-shrink-0 shadow-md">
                                <div>
                                    <h4
                                        class="font-medium text-gray-200 group-hover:text-white transition text-sm leading-tight">
                                        Data Science with Python</h4>
                                    <div class="flex items-center text-xs text-gray-400 mt-1"><i
                                            class="fas fa-star text-yellow-400 mr-1 text-xs"
                                            aria-hidden="true"></i><span>4.9 • 8.2k students</span></div>
                                </div>
                            </a>
                            <a href="Cybersecurity%20Fundamentals%20free.php" <!-- Link to Cybersecurity Course -->
                                class="flex items-center hover:bg-dark-700 p-3 -m-3 rounded-lg transition group">
                                <img src="https://media.istockphoto.com/id/1412282189/photo/lock-network-technology-concept.jpg?s=612x612&w=0&k=20&c=hripuxLs9pS_7Ln6YWQR-Ow2_-BU5RdQ4vOY8s1q1iQ="
                                    alt="Cybersecurity Fundamentals"
                                    class="w-16 h-12 rounded-lg object-cover mr-4 flex-shrink-0 shadow-md">
                                <div>
                                    <h4
                                        class="font-medium text-gray-200 group-hover:text-white transition text-sm leading-tight">
                                        Cybersecurity Fundamentals</h4>
                                    <div class="flex items-center text-xs text-gray-400 mt-1"><i
                                            class="fas fa-star text-yellow-400 mr-1 text-xs"
                                            aria-hidden="true"></i><span>4.7 • 6.5k students</span></div>
                                </div>
                            </a>
                        </div>
                    </div>

                </div> <!-- End Sticky Wrapper -->
            </aside> <!-- End Sidebar Column -->
        </div> <!-- End Grid -->
    </main>

    <!-- Footer -->
    <footer class="bg-dark-900 border-t border-dark-700 py-12 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Column 1 -->
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-graduation-cap text-primary-600 text-2xl mr-2" aria-hidden="true"></i>
                        <span class="text-xl font-bold text-gray-100">EduPro</span>
                    </div>
                    <p class="text-gray-400 mb-4 text-sm leading-relaxed">Empowering learners worldwide with accessible,
                        high-quality digital education for career advancement.</p>
                    <div class="flex space-x-4">
                        <a href="#" aria-label="Facebook" class="text-gray-400 hover:text-primary-500 transition"><i
                                class="fab fa-facebook-f text-lg"></i></a>
                        <a href="#" aria-label="Twitter" class="text-gray-400 hover:text-primary-500 transition"><i
                                class="fab fa-twitter text-lg"></i></a>
                        <a href="#" aria-label="LinkedIn" class="text-gray-400 hover:text-primary-500 transition"><i
                                class="fab fa-linkedin-in text-lg"></i></a>
                        <a href="#" aria-label="YouTube" class="text-gray-400 hover:text-primary-500 transition"><i
                                class="fab fa-youtube text-lg"></i></a>
                    </div>
                </div>
                <!-- Column 2 -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Company</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition text-sm">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Careers</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Blog</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Press</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Affiliates</a></li>
                    </ul>
                </div>
                <!-- Column 3 -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Support</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Help Center</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Contact Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Community Forum</a>
                        </li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Accessibility</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition text-sm">System Status</a></li>
                    </ul>
                </div>
                <!-- Column 4 -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Legal</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Terms of Service</a>
                        </li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Privacy Policy</a>
                        </li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Cookie Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition text-sm">DMCA</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Sitemap</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 pt-8 border-t border-dark-700 text-center">
                <p class="text-gray-500 text-sm">© <?php echo date('Y'); ?> EduPro, Inc. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- ================================================== -->
    <!--          FINAL JAVASCRIPT WITH RESET               -->
    <!-- ================================================== -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- Smooth scrolling ---
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    const targetId = this.getAttribute('href');
                    if (targetId && targetId.length > 1 && targetId.startsWith('#')) {
                        const targetElement = document.querySelector(targetId);
                        if (targetElement) {
                            e.preventDefault();
                            const navHeight = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--scroll-margin'), 10) || 80;
                            const elementPosition = targetElement.getBoundingClientRect().top;
                            const offsetPosition = elementPosition + window.pageYOffset - navHeight;

                            window.scrollTo({ top: offsetPosition, behavior: "smooth" });
                        }
                    }
                });
            });

            // --- Curriculum Accordion ---
            const moduleHeaders = document.querySelectorAll('.module-header');
            moduleHeaders.forEach(header => {
                header.addEventListener('click', function () {
                    const content = this.nextElementSibling;
                    const icon = this.querySelector('.accordion-icon');
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';

                    if (content && content.classList.contains('module-content')) {
                        content.classList.toggle('hidden');
                        this.setAttribute('aria-expanded', !isExpanded);
                    }
                    if (icon) {
                        icon.classList.toggle('rotate-180');
                    }
                });
                // Set initial state
                const content = header.nextElementSibling;
                const icon = header.querySelector('.accordion-icon');
                if (content && content.classList.contains('module-content')) {
                    if (header.getAttribute('aria-expanded') === 'true') {
                        content.classList.remove('hidden');
                        if (icon) icon.classList.remove('rotate-180');
                    } else {
                        content.classList.add('hidden');
                        if (icon) icon.classList.add('rotate-180');
                    }
                }
            });

            // --- Progress Bar, Lesson Completion & Reset Logic ---
            const userIsLoggedInForJS = <?php echo json_encode($user_logged_in); ?>;

            if (userIsLoggedInForJS) {
                const checkboxes = document.querySelectorAll('.lesson-checkbox');
                // *** Lesson count for AI course ***
                const totalLessonsForProgress = checkboxes.length > 0 ? checkboxes.length : 13;
                const courseSlugForStorage = <?php echo json_encode($course_slug); ?>;
                const progressBar = document.getElementById('progress-bar');
                const progressText = document.getElementById('progress-text');
                const completionBadge = document.getElementById('completion-badge');
                const certificateButton = document.getElementById('certificate-download-button');
                const resetProgressButton = document.getElementById('reset-progress-button'); // Get reset button

                // Update overall progress UI
                function updateOverallProgressUI(completedCount) {
                    if (!progressBar || !progressText) return;
                    const progress = totalLessonsForProgress > 0 ? Math.round((completedCount / totalLessonsForProgress) * 100) : 0;
                    progressBar.style.width = progress + '%';
                    progressBar.setAttribute('aria-valuenow', progress);
                    progressText.textContent = `${completedCount}/${totalLessonsForProgress} lessons completed (${progress}%)`;
                    let barColorClass = 'bg-primary-600';
                    if (progress === 100) { barColorClass = 'bg-green-500'; }
                    else if (progress > 70) { barColorClass = 'bg-yellow-500'; }
                    else if (progress > 30) { barColorClass = 'bg-orange-500'; }
                    else if (progress > 0) { barColorClass = 'bg-red-500'; }
                    progressBar.className = `h-full rounded-full progress-bar ${barColorClass}`;
                    if (completionBadge) {
                        if (completedCount > 0) { completionBadge.textContent = completedCount; completionBadge.classList.remove('hidden'); }
                        else { completionBadge.classList.add('hidden'); }
                    }
                    if (certificateButton) {
                        if (progress >= 100) { certificateButton.classList.remove('hidden'); }
                        else { certificateButton.classList.add('hidden'); }
                    }
                }

                // Get count of completed lessons
                function getCompletedLessonsCount() {
                    let count = 0;
                    checkboxes.forEach(checkbox => {
                        const lessonId = checkbox.id;
                        if (lessonId) { // Ensure ID exists
                            const storageKey = `completed_${lessonId}_${courseSlugForStorage}`;
                            if (localStorage.getItem(storageKey) === 'true') { count++; }
                        }
                    });
                    return count;
                }

                // Update checkbox visual
                function updateCheckboxVisual(checkbox, isChecked) {
                    const label = checkbox.closest('.lesson-item')?.querySelector(`label[for="${checkbox.id}"]`);
                    const checkIconInLabel = label?.querySelector('.fa-check');
                    if (!label) return;
                    if (isChecked) {
                        if (checkIconInLabel) checkIconInLabel.classList.remove('hidden');
                        label.classList.add('text-gray-500');
                        const titleSpan = label.querySelector('.lesson-title');
                        if (titleSpan) titleSpan.classList.add('line-through');
                    } else {
                        if (checkIconInLabel) checkIconInLabel.classList.add('hidden');
                        label.classList.remove('text-gray-500');
                        const titleSpan = label.querySelector('.lesson-title');
                        if (titleSpan) titleSpan.classList.remove('line-through');
                    }
                }

                // *** Function to Reset Course Progress ***
                function resetCourseProgress() {
                    if (!confirm('Are you sure you want to reset your progress for this course? This action cannot be undone.')) {
                        return; // Stop if user cancels
                    }
                    console.log(`Resetting progress for course: ${courseSlugForStorage}`);
                    checkboxes.forEach(checkbox => {
                        const lessonId = checkbox.id;
                        if (lessonId) {
                            const storageKey = `completed_${lessonId}_${courseSlugForStorage}`;
                            localStorage.removeItem(storageKey); // Remove from storage
                            checkbox.checked = false; // Uncheck box
                            updateCheckboxVisual(checkbox, false); // Update style
                        }
                    });
                    updateOverallProgressUI(0); // Reset bar/text/button visibility
                    alert('Course progress has been reset.'); // Confirmation
                    console.log(`Progress reset complete for course: ${courseSlugForStorage}`);
                }
                // *** END: Reset Function ***

                // --- Initialization ---
                checkboxes.forEach(checkbox => {
                    const lessonId = checkbox.id;
                    if (!lessonId) return;
                    const storageKey = `completed_${lessonId}_${courseSlugForStorage}`;
                    const isCompleted = localStorage.getItem(storageKey) === 'true';
                    checkbox.checked = isCompleted;
                    updateCheckboxVisual(checkbox, isCompleted);
                    checkbox.addEventListener('change', function () {
                        const currentLessonId = this.id;
                        const currentStorageKey = `completed_${currentLessonId}_${courseSlugForStorage}`;
                        if (this.checked) { localStorage.setItem(currentStorageKey, 'true'); }
                        else { localStorage.removeItem(currentStorageKey); }
                        updateCheckboxVisual(this, this.checked);
                        updateOverallProgressUI(getCompletedLessonsCount());
                    });
                });

                const playLinks = document.querySelectorAll('.play-lesson-link');
                playLinks.forEach(link => {
                    link.addEventListener('click', function (event) {
                        const lessonId = this.dataset.lessonId; // e.g., lesson-1-ml
                        if (!lessonId) return;
                        const checkbox = document.getElementById(lessonId);
                        if (!checkbox) return;
                        const storageKey = `completed_${lessonId}_${courseSlugForStorage}`;
                        if (localStorage.getItem(storageKey) !== 'true') {
                            localStorage.setItem(storageKey, 'true');
                            checkbox.checked = true;
                            updateCheckboxVisual(checkbox, true);
                            updateOverallProgressUI(getCompletedLessonsCount());
                        }
                    });
                });

                // Initial progress update
                updateOverallProgressUI(getCompletedLessonsCount());

                // *** Add Event Listener for Reset Button ***
                if (resetProgressButton) {
                    resetProgressButton.addEventListener('click', resetCourseProgress);
                } else {
                    console.warn("Reset progress button not found.");
                }

            } // End if(userIsLoggedInForJS)

            // --- Enrollment Button Logic ---
            const enrollButton = document.getElementById('enroll-btn');
            const enrollmentActionArea = document.getElementById('enrollment-action-area');
            // isLoggedIn declared earlier via PHP echo
            const isPaidCourse = <?php echo json_encode($is_paid_course); ?>;
            const initialButtonConfigText = <?php echo json_encode($button_config['text']); ?>;
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = initialButtonConfigText;
            const initialButtonTextContent = tempDiv.textContent || tempDiv.innerText || '';

            if (enrollButton && enrollButton.tagName === 'BUTTON' && enrollmentActionArea) {
                const enrollButtonTextSpan = enrollButton.querySelector('.btn-text');
                const enrollButtonSpinner = enrollButton.querySelector('.spinner');

                enrollButton.addEventListener('click', function () {
                    if (!userIsLoggedInForJS) { // Use JS variable
                        alert('Please log in or sign up to enroll.');
                        window.location.href = 'Login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
                        return;
                    }
                    if (isPaidCourse) {
                        alert('Paid course enrollment: Payment processing not implemented yet.');
                        return;
                    }

                    const courseSlug = this.dataset.courseSlug;
                    const csrfToken = this.dataset.csrfToken;
                    const button = this;

                    if (!csrfToken) {
                        alert('Security token missing. Please refresh the page.');
                        return;
                    }

                    button.disabled = true;
                    button.setAttribute('aria-disabled', 'true');
                    button.classList.add('opacity-60', 'cursor-wait');
                    if (enrollButtonTextSpan) { enrollButtonTextSpan.textContent = 'Processing...'; }
                    else { button.innerHTML = 'Processing...'; }
                    if (enrollButtonSpinner) { enrollButtonSpinner.classList.remove('hidden'); }

                    const formData = new FormData();
                    formData.append('course_slug', courseSlug);
                    formData.append('csrf_token', csrfToken);

                    fetch('enroll_course.php', { method: 'POST', body: formData })
                        .then(response => {
                            if (!response.ok) { return response.text().then(text => { throw new Error(`Server Error ${response.status}: ${text || '(No details)'}`); }); }
                            const contentType = response.headers.get("content-type");
                            if (contentType && contentType.includes("application/json")) { return response.json(); }
                            else { return response.text().then(text => { throw new Error(`Unexpected response format: ${text || '(Empty)'}`); }); }
                        })
                        .then(data => {
                            if (data.success) {
                                console.log("Enrollment successful:", data.message);
                                enrollmentActionArea.innerHTML = `
                                <a href="#curriculum-section" id="continue-learning-link"
                                   class="btn-success hover:from-green-600 hover:to-green-700 text-white px-7 py-3 rounded-lg font-semibold transition duration-300 flex items-center justify-center text-base shadow-lg transform hover:scale-105">
                                    <i class="fas fa-play mr-2"></i>Continue Learning
                                </a>
                                <span class="flex items-center text-gray-400 text-sm font-medium ml-2">
                                     <i class="fas fa-check-circle mr-1.5 text-success-500" aria-hidden="true"></i> Enrolled
                                </span>`;
                                // Re-run smooth scroll setup if needed
                                document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                                    anchor.addEventListener('click', function (e) {
                                        const targetId = this.getAttribute('href');
                                        if (targetId && targetId.length > 1 && targetId.startsWith('#')) {
                                            const targetElement = document.querySelector(targetId);
                                            if (targetElement) {
                                                e.preventDefault();
                                                const navHeight = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--scroll-margin'), 10) || 80;
                                                const elementPosition = targetElement.getBoundingClientRect().top;
                                                const offsetPosition = elementPosition + window.pageYOffset - navHeight;
                                                window.scrollTo({ top: offsetPosition, behavior: "smooth" });
                                            }
                                        }
                                    });
                                });
                            } else {
                                if (data.action === 'redirect_login') {
                                    alert('Session issue. Please log in again.');
                                    window.location.href = 'Login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
                                } else if (data.message && data.message.toLowerCase().includes('already enrolled')) {
                                    console.warn("Already enrolled:", data.message);
                                    enrollmentActionArea.innerHTML = `
                                    <a href="#curriculum-section" id="continue-learning-link" class="btn-success hover:from-green-600 hover:to-green-700 text-white px-7 py-3 rounded-lg font-semibold transition duration-300 flex items-center justify-center text-base shadow-lg transform hover:scale-105"> <i class="fas fa-play mr-2"></i>Continue Learning </a>
                                    <span class="flex items-center text-gray-400 text-sm font-medium ml-2"><i class="fas fa-check-circle mr-1.5 text-success-500" aria-hidden="true"></i> Enrolled</span>`;
                                } else {
                                    alert('Enrollment failed: ' + (data.message || 'Unknown server error.'));
                                    resetEnrollButton();
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Enrollment fetch error:', error);
                            alert('Could not enroll. Please check connection and try again.\nError: ' + error.message);
                            resetEnrollButton();
                        });

                    function resetEnrollButton() {
                        button.disabled = false;
                        button.removeAttribute('aria-disabled');
                        button.classList.remove('opacity-60', 'cursor-wait');
                        if (enrollButtonTextSpan) { enrollButtonTextSpan.innerHTML = initialButtonConfigText; }
                        else { button.innerHTML = initialButtonConfigText; }
                        if (enrollButtonSpinner) { enrollButtonSpinner.classList.add('hidden'); }
                    }
                });
            } // End if(enrollButton)
        }); // End DOMContentLoaded
    </script>
</body>

</html>
<?php
// Close database connection if it exists and is open
if (isset($mysqli) && $mysqli instanceof mysqli && !$mysqli->connect_error && $mysqli->thread_id) {
    $mysqli->close();
}
?>