<?php
// --- SETUP AND CONFIGURATION ---
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php'; // Load config with $mysqli setup

// --- Generate/Get CSRF Token ---
if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        error_log("Error generating CSRF token: " . $e->getMessage());
        // Avoid dying immediately if possible, handle in button logic
        $_SESSION['csrf_token'] = null; // Ensure it's null if generation failed
    }
}
$csrf_token = $_SESSION['csrf_token'] ?? null;
if ($csrf_token === null && !(isset($button_config['disabled']) && $button_config['disabled'])) {
    error_log("CSRF token is null, potential security issue.");
    // Consider adding a visible warning or disabling actions if CSRF is critical here
}

// --- Course Configuration ---
$course_slug = 'ios-swiftui-dev';
$course_title_display = 'iOS Development with SwiftUI';
$is_paid_course = false;

// --- User & Enrollment Status ---
$is_enrolled = false;
$user_logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['id']);
$user_id = $user_logged_in ? (int) $_SESSION['id'] : null;
$db_connection_error = false;
$enrollment_check_error = false;

// Check DB connection if user logged in
if ($user_logged_in) {
    if (!isset($mysqli) || !($mysqli instanceof mysqli) || $mysqli->connect_error) {
        error_log("DB Connection Error: " . ($mysqli->connect_error ?? 'mysqli object not available or not connected'));
        $db_connection_error = true;
        $enrollment_check_error = true; // If DB connection fails, enrollment check also fails
    } else {
        // Check enrollment status
        $stmt = $mysqli->prepare("SELECT id FROM user_courses WHERE user_id = ? AND course_slug = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("is", $user_id, $course_slug);
            if ($stmt->execute()) {
                $stmt->store_result();
                $is_enrolled = $stmt->num_rows > 0;
            } else {
                error_log("Enrollment Check Execution Error for user $user_id, course $course_slug: " . $stmt->error);
                $enrollment_check_error = true; // Error during execution
            }
            $stmt->close();
        } else {
            error_log("Enrollment Check Prepare Statement Error: " . $mysqli->error);
            $enrollment_check_error = true; // Error preparing statement
        }
    }
}

// --- Button Configuration ---
$button_config = [
    'text' => $is_paid_course ? 'Enroll Now' : 'Start Free Course',
    'tag' => 'button',
    'disabled' => false,
    'classes' => 'bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center text-base shadow-md transform hover:scale-105 btn-primary', // Added btn-primary
    'href' => '#',
    'id' => 'enroll-btn',
    'data_attributes' => [
        'course-slug' => $course_slug,
        'csrf-token' => $csrf_token ?? '' // Pass even if null, JS should check
    ]
];

// Modify button based on state
if ($is_enrolled) {
    $button_config['text'] = '<i class="fas fa-play mr-2"></i>Continue Learning';
    $button_config['tag'] = 'a';
    $button_config['href'] = '#curriculum-section';
    $button_config['classes'] = 'bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center text-base shadow-md transform hover:scale-105 btn-success'; // Added btn-success
    $button_config['id'] = 'continue-learning-btn';
    $button_config['data_attributes'] = []; // Clear data attributes
    $button_config['disabled'] = false;
} elseif ($enrollment_check_error || $db_connection_error) {
    // Only show service unavailable if the user is logged in, otherwise they see login/signup prompt
    if ($user_logged_in) {
        $button_config['text'] = 'Service Unavailable';
        $button_config['disabled'] = true;
        $button_config['classes'] = 'bg-gray-600 cursor-not-allowed opacity-75 text-white px-6 py-3 rounded-lg font-medium flex items-center justify-center text-base shadow-md';
        $button_config['id'] = 'enroll-btn-disabled';
        $button_config['data_attributes'] = [];
    }
    // If not logged in, the default 'Start Free Course' button remains, JS will handle login redirect.
} elseif (!$user_logged_in && $is_paid_course) {
    // If it's a paid course and user is not logged in
    $button_config['tag'] = 'a';
    $button_config['href'] = 'Login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']);
    $button_config['text'] = 'Login to Enroll';
    $button_config['classes'] = 'bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center text-base shadow-md btn-primary'; // Added btn-primary
    $button_config['id'] = 'login-to-enroll-btn';
    $button_config['data_attributes'] = [];
}
// If CSRF token generation failed and the button wasn't already disabled
if ($csrf_token === null && !$button_config['disabled'] && $button_config['tag'] === 'button') {
    $button_config['text'] = 'Security Error';
    $button_config['disabled'] = true;
    $button_config['classes'] = 'bg-red-600 cursor-not-allowed opacity-75 text-white px-6 py-3 rounded-lg font-medium flex items-center justify-center text-base shadow-md';
    $button_config['id'] = 'enroll-btn-csrf-error';
    $button_config['data_attributes'] = [];
}

// Helper function for safe echoing
function safe_echo($str)
{
    echo htmlspecialchars((string) ($str ?? ''), ENT_QUOTES, 'UTF-8');
}

// Calculate number of rendered lessons (MUST match the actual count below)
$rendered_lessons_count_ios = 14;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php safe_echo($course_title_display); ?> | EduPro</title>
    <meta name="description"
        content="Build modern, declarative iOS apps with SwiftUI. Learn state management, navigation, data handling, and integrate with UIKit.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Tailwind Config (Standardized)
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af' },
                        dark: { 900: '#0f172a', 800: '#1e293b', 700: '#334155', 600: '#475569', 500: '#64748b' },
                        success: { 500: '#10b981', 600: '#059669', 700: '#047857' },
                        purple: { 400: '#c084fc', 500: '#a855f7', 600: '#9333ea' },
                        green: { 400: '#4ade80', 500: '#22c55e', 600: '#16a34a', 700: '#15803d' }, // Added 700 for hover
                        red: { 500: '#ef4444', 600: '#dc2626' },    // Progress Bar & Errors
                        orange: { 500: '#f97316' }, // Progress Bar
                        yellow: { 400: '#facc15', 500: '#eab308' }, // Progress Bar & Ratings
                        gray: { 100: '#f3f4f6', 200: '#e5e7eb', 300: '#d1d5db', 400: '#9ca3af', 500: '#6b7280', 600: '#4b5563', 700: '#374151', 800: '#1f2937', 900: '#11182c' }
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    boxShadow: {
                        'card': '0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.2)',
                        'card-hover': '0 25px 50px -12px rgba(0, 0, 0, 0.4)',
                        'inner': 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.06)',
                        'lg': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)', // Standard Tailwind lg
                        'md': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)' // Standard Tailwind md
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom CSS (Standardized) */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        :root {
            --scroll-margin: 100px;
            /* Adjust if nav height changes */
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: var(--scroll-margin);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a;
            /* Tailwind dark-900 */
            color: #d1d5db;
            /* Tailwind gray-300 */
        }

        /* Cards - consistent style */
        .content-card {
            background-color: #1e293b;
            /* Tailwind dark-800 */
            border-radius: 0.75rem;
            /* rounded-xl */
            padding: 1.5rem;
            /* p-6 */
            border: 1px solid #334155;
            /* border-gray-700 */
            box-shadow: var(--tw-shadow-lg);
            /* Use Tailwind's shadow */
            margin-bottom: 2rem;
            /* mb-8 */
        }

        .lesson-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: rgba(55, 65, 81, 0.5);
            /* Tailwind gray-700 with opacity */
            padding: 1rem;
            /* p-4 */
            border-radius: 0.5rem;
            /* rounded-lg */
            border: 1px solid #4b5563;
            /* border-gray-600 */
            transition: background-color 0.2s ease-in-out;
        }

        .lesson-item:hover {
            background-color: rgba(71, 85, 105, 0.7);
            /* Tailwind gray-600 with opacity */
        }

        .lesson-item label {
            display: flex;
            align-items: center;
            cursor: pointer;
            flex-grow: 1;
            margin-right: 1rem;
            transition: color 0.2s, text-decoration 0.2s;
            color: #e5e7eb;
            /* Tailwind gray-200 */
            font-size: 0.95rem;
            /* Slightly smaller */
        }

        .lesson-item label.line-through {
            text-decoration: line-through;
            text-decoration-color: #6b7280;
            /* Tailwind gray-500 */
        }

        .lesson-item label.text-gray-500 {
            /* Use Tailwind class directly */
            color: #6b7280 !important;
            /* Tailwind gray-500 */
        }

        .lesson-item .checkbox-visual {
            width: 1.25rem;
            /* w-5 */
            height: 1.25rem;
            /* h-5 */
            border: 1px solid #6b7280;
            /* border-gray-500 */
            border-radius: 0.25rem;
            /* rounded */
            margin-right: 1rem;
            /* mr-4 */
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s, border-color 0.2s;
            flex-shrink: 0;
            position: relative;
            background-color: #374151;
            /* Tailwind gray-700 */
        }

        .lesson-checkbox:checked+label .checkbox-visual {
            background-color: #2563eb;
            /* Tailwind primary-600 */
            border-color: #2563eb;
            /* Tailwind primary-600 */
        }

        .lesson-checkbox:checked+label .checkbox-visual .fa-check {
            opacity: 1;
        }

        .lesson-checkbox:not(:checked)+label .checkbox-visual .fa-check {
            opacity: 0;
        }

        .checkbox-visual .fa-check {
            transition: opacity 0.2s ease-in-out;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 0.7rem;
            /* Smaller check */
            color: white;
        }

        .lesson-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            /* gap-3 */
            flex-shrink: 0;
        }

        .progress-bar {
            transition: width 0.5s ease-in-out, background-color 0.5s ease-in-out;
        }

        .progress-container {
            height: 0.75rem;
            /* h-3 */
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.3);
            /* More subtle inner shadow */
        }

        /* Tooltip Styling */
        .has-tooltip {
            position: relative;
        }

        .tooltip {
            visibility: hidden;
            /* Use visibility for better transition */
            opacity: 0;
            transition: opacity 0.2s ease, visibility 0s linear 0.2s;
            /* Delay visibility change */
            pointer-events: none;
            position: absolute;
            z-index: 10;
            background-color: #1f2937;
            /* Tailwind gray-800 */
            color: #e5e7eb;
            /* Tailwind gray-200 */
            padding: 0.3rem 0.6rem;
            /* py-1 px-2.5 */
            border-radius: 0.375rem;
            /* rounded-md */
            font-size: 0.75rem;
            /* text-xs */
            white-space: nowrap;
            box-shadow: var(--tw-shadow-md);
            bottom: calc(100% + 8px);
            /* Spacing from element */
            left: 50%;
            transform: translateX(-50%);
        }

        .has-tooltip:hover .tooltip {
            visibility: visible;
            /* Make visible on hover */
            opacity: 1;
            transition: opacity 0.2s ease;
            /* Transition opacity in */
        }

        /* Spinner */
        .spinner {
            display: inline-block;
            border: 3px solid rgba(255, 255, 255, .3);
            border-left-color: #fff;
            border-radius: 50%;
            width: 1rem;
            /* w-4 */
            height: 1rem;
            /* h-4 */
            animation: spin 1s linear infinite;
        }

        .spinner.hidden {
            display: none;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Disabled state */
        button:disabled,
        a.disabled {
            opacity: 0.6;
            cursor: not-allowed !important;
        }

        button[disabled],
        a[aria-disabled="true"] {
            /* Use aria-disabled for links */
            opacity: 0.6;
            cursor: not-allowed !important;
            pointer-events: none;
        }

        /* Curriculum Styles */
        #curriculum-section {
            scroll-margin-top: var(--scroll-margin);
        }

        /* Use variable */

        .module-header {
            background: linear-gradient(90deg, rgba(51, 65, 85, 0.7) 0%, rgba(30, 41, 59, 0.9) 100%);
            /* Slightly adjusted gradient */
            border-left: 4px solid #2563eb;
            /* Default primary color */
            transition: background-color 0.2s ease, border-color 0.2s ease;
            cursor: pointer;
        }

        .module-header:hover {
            background: linear-gradient(90deg, rgba(51, 65, 85, 0.9) 0%, rgba(30, 41, 59, 1) 100%);
        }

        .module-content {
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), padding 0.4s cubic-bezier(0.4, 0, 0.2, 1), margin 0.4s cubic-bezier(0.4, 0, 0.2, 1), border 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            max-height: 1000px;
            /* Sufficiently large */
        }

        .module-content.hidden {
            max-height: 0;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            border-width: 0 !important;
        }

        .accordion-icon {
            transition: transform 0.3s ease;
        }

        .accordion-icon.rotate-180 {
            transform: rotate(180deg);
        }

        /* Button Gradient Styles */
        .btn-primary {
            background-image: linear-gradient(to right, var(--tw-gradient-stops));
            --tw-gradient-from: #2563eb;
            /* primary-600 */
            --tw-gradient-to: #1d4ed8;
            /* primary-700 */
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3), 0 2px 4px -1px rgba(37, 99, 235, 0.1);
        }

        .btn-primary:hover:not(:disabled):not([aria-disabled="true"]) {
            /* Check for disabled state */
            --tw-gradient-from: #1d4ed8;
            /* primary-700 */
            --tw-gradient-to: #1e40af;
            /* primary-800 */
        }

        .btn-success {
            background-image: linear-gradient(to right, var(--tw-gradient-stops));
            --tw-gradient-from: #10b981;
            /* success-500 */
            --tw-gradient-to: #059669;
            /* success-600 */
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3), 0 2px 4px -1px rgba(16, 185, 129, 0.1);
        }

        .btn-success:hover:not(:disabled):not([aria-disabled="true"]) {
            /* Check for disabled state */
            --tw-gradient-from: #059669;
            /* success-600 */
            --tw-gradient-to: #047857;
            /* success-700 */
        }

        /* Section Title Gradient */
        .section-title-gradient {
            /* More descriptive class */
            background: linear-gradient(to right, #e2e8f0, #94a3b8);
            /* slate-200 to slate-400 */
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            -webkit-text-fill-color: transparent;
            /* For Safari */
        }

        /* Mobile Menu Styling */
        .mobile-menu {
            background-color: #1f2937;
            /* dark-800 */
            position: absolute;
            top: 100%;
            /* Position below the navbar */
            left: 0;
            right: 0;
            border-top: 1px solid #334155;
            /* gray-700 */
            box-shadow: var(--tw-shadow-lg);
            padding: 0.5rem 0;
            /* Add some vertical padding */
        }

        .mobile-menu a,
        .mobile-menu button {
            /* Target links and buttons inside */
            display: block;
            padding: 0.75rem 1.5rem;
            /* py-3 px-6 */
            color: #d1d5db;
            /* gray-300 */
            font-size: 1rem;
            /* text-base */
            font-weight: 500;
            /* font-medium */
            transition: background-color 0.2s, color 0.2s;
        }

        .mobile-menu a:hover,
        .mobile-menu button:hover {
            background-color: #334155;
            /* gray-700 */
            color: #ffffff;
            /* white */
        }

        .mobile-menu .profile-link {
            /* Specific style for profile link */
            display: flex;
            align-items: center;
        }
    </style>
</head>

<body class="bg-dark-900 text-gray-300 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-gray-800 border-b border-gray-700 py-4 sticky top-0 z-50 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="1.Home.php" class="flex items-center text-white">
                        <i class="fas fa-graduation-cap text-primary-500 text-2xl"></i>
                        <span class="ml-2 text-xl font-bold">EduPro</span>
                    </a>
                    <div class="hidden md:flex space-x-6">
                        <a href="courses_list.php"
                            class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors">Courses</a>
                        <a href="resources.php"
                            class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors">Resources</a>
                        <!-- Add other nav links as needed -->
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Search and Notifications (Placeholder) -->
                    <button
                        class="p-2 rounded-full text-gray-400 hover:text-white hover:bg-gray-700 transition relative has-tooltip"
                        aria-label="Search courses">
                        <i class="fas fa-search"></i>
                        <span class="tooltip">Search courses</span>
                    </button>
                    <button
                        class="p-2 rounded-full text-gray-400 hover:text-white hover:bg-gray-700 transition relative has-tooltip"
                        aria-label="Notifications">
                        <i class="fas fa-bell"></i>
                        <span
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center ring-2 ring-gray-800 hidden">3</span>
                        <!-- Example badge -->
                        <span class="tooltip">Notifications</span>
                    </button>

                    <!-- User Area -->
                    <div class="relative">
                        <?php if ($user_logged_in): ?>
                            <div class="flex items-center space-x-2">
                                <a href="Dashboard.php"
                                    class="flex items-center hover:bg-gray-700 p-1 rounded-full transition relative group"
                                    title="Dashboard">
                                    <img src="<?php echo $_SESSION['profile_image_url'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['username'] ?? 'U') . '&background=4b5563&color=f9fafb&size=32'; ?>"
                                        alt="Profile"
                                        class="w-8 h-8 rounded-full object-cover bg-gray-600 border-2 border-gray-700 group-hover:border-primary-500 transition-colors">
                                    <span id="completion-badge"
                                        class="hidden absolute -top-1 -right-1 bg-primary-600 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center ring-2 ring-gray-800">0</span>
                                </a>
                                <a href="logout.php"
                                    class="p-2 rounded-full text-gray-400 hover:text-white hover:bg-gray-700 transition relative has-tooltip"
                                    title="Logout">
                                    <i class="fas fa-sign-out-alt"></i>
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

                    <!-- Mobile Menu Button -->
                    <div class="md:hidden">
                        <button
                            class="mobile-menu-button p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none"
                            aria-label="Toggle menu" aria-expanded="false">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div class="mobile-menu hidden md:hidden">
                <a href="courses_list.php">Courses</a>
                <a href="resources.php">Resources</a>
                <!-- Add other nav links -->
                <div class="pt-2 mt-2 border-t border-gray-700">
                    <?php if ($user_logged_in): ?>
                        <a href="Dashboard.php" class="profile-link">
                            <img src="<?php echo $_SESSION['profile_image_url'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['username'] ?? 'U') . '&background=4b5563&color=f9fafb&size=32'; ?>"
                                alt="Profile"
                                class="w-8 h-8 rounded-full object-cover bg-gray-600 border border-gray-500 mr-3">
                            My Dashboard
                        </a>
                        <a href="logout.php">Log out</a>
                    <?php else: ?>
                        <a href="Login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Log in</a>
                        <a href="Signup.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                            class="block w-full mt-2 px-3 py-2 bg-primary-600 rounded-md hover:bg-primary-700 transition text-center text-white font-medium">Sign
                            up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
        <!-- Course Header -->
        <section class="bg-gray-800 rounded-xl p-6 md:p-8 mb-8 md:mb-10 border border-gray-700 shadow-lg">
            <div class="flex flex-col md:flex-row justify-between gap-8">
                <!-- Left Column: Text Content -->
                <div class="md:w-2/3">
                    <span
                        class="inline-block <?php echo $is_paid_course ? 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/30' : 'bg-primary-600/20 text-primary-400 ring-primary-600/30'; ?> px-3 py-1 rounded-full text-xs font-semibold mb-4 ring-1 ring-inset">
                        <?php echo $is_paid_course ? 'PROFESSIONAL CERTIFICATE' : 'FREE COURSE'; ?>
                    </span>
                    <h1 class="text-3xl md:text-4xl font-extrabold mb-4 text-white leading-tight">
                        <?php safe_echo($course_title_display); ?>
                    </h1>
                    <p class="text-lg text-gray-300 mb-6">Build beautiful, responsive iOS apps with SwiftUI. Master
                        declarative UI, Combine framework, and modern Apple development techniques.</p>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mb-6 text-sm text-gray-400">
                        <div class="flex items-center">
                            <div class="flex items-center text-yellow-400 mr-1.5">
                                <i class="fas fa-star text-base"></i>
                                <i class="fas fa-star text-base"></i>
                                <i class="fas fa-star text-base"></i>
                                <i class="fas fa-star text-base"></i>
                                <i class="fas fa-star-half-alt text-base"></i>
                            </div>
                            <span class="text-gray-300 font-medium">4.8</span>
                            <span class="ml-1">(3,450 ratings)</span>
                        </div>
                        <span class="hidden sm:inline text-gray-600">•</span>
                        <div class="flex items-center">
                            <i class="fas fa-users mr-1.5 text-gray-500"></i>
                            <span>42,000+ students</span>
                        </div>
                        <span class="hidden sm:inline text-gray-600">•</span>
                        <div class="flex items-center">
                            <i class="fas fa-calendar-alt mr-1.5 text-gray-500"></i>
                            <span>Updated May 2024</span>
                        </div>
                    </div>
                    <!-- Enrollment Action Area -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-4 mt-8"
                        id="enrollment-action-area">
                        <?php
                        $tag = $button_config['tag'];
                        $text = $button_config['text'];
                        $classes = $button_config['classes'];
                        $href_attr = ($tag === 'a') ? ' href="' . htmlspecialchars($button_config['href']) . '"' : '';
                        $id_attr = $button_config['id'] ? ' id="' . htmlspecialchars($button_config['id']) . '"' : '';
                        $disabled_attrs = '';
                        if ($button_config['disabled']) {
                            if ($tag === 'button') {
                                $disabled_attrs = ' disabled';
                            } else { // For 'a' tags, use aria-disabled
                                $disabled_attrs = ' aria-disabled="true"';
                                $classes .= ' disabled'; // Add class for visual styling
                            }
                        }
                        $data_attrs_str = '';
                        if (!empty($button_config['data_attributes'])) {
                            foreach ($button_config['data_attributes'] as $key => $value) {
                                $data_attrs_str .= ' data-' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
                            }
                        }

                        echo "<{$tag}{$id_attr} class=\"{$classes}\"{$href_attr}{$disabled_attrs}{$data_attrs_str}>";
                        echo '<span class="btn-text">' . $text . '</span>';
                        // Show spinner only for the specific enrollment button when processing
                        if ($tag === 'button' && $button_config['id'] === 'enroll-btn' && !$button_config['disabled']) {
                            echo '<span class="spinner hidden ml-2"></span>';
                        }
                        echo "</{$tag}>";

                        // Display "Enrolled" status clearly if applicable
                        if ($is_enrolled) {
                            echo '<span class="flex items-center text-green-400 text-sm font-medium pt-1 sm:pt-0"><i class="fas fa-check-circle mr-1.5"></i> You are enrolled</span>';
                        } elseif (!$enrollment_check_error && !$db_connection_error && $tag !== 'a' && !$is_paid_course) {
                            // Optionally show a "Save" button placeholder if needed (currently disabled)
                            // echo '<button type="button" class="flex items-center text-gray-400 hover:text-white transition group has-tooltip opacity-50 cursor-not-allowed" aria-label="Save course for later (coming soon)" disabled><i class="far fa-heart mr-2"></i> Save<span class="tooltip">Save (coming soon)</span></button>';
                        }
                        ?>
                    </div>
                    <?php if (!$is_enrolled && !$user_logged_in && !$is_paid_course): ?>
                        <p class="text-xs text-gray-400 mt-3"><a
                                href="Login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                class="text-primary-500 hover:text-primary-400 hover:underline font-medium">Log in</a> or <a
                                href="Signup.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                class="text-primary-500 hover:text-primary-400 hover:underline font-medium">sign up</a> to
                            enroll and track your progress.</p>
                    <?php endif; ?>
                    <?php if ($enrollment_check_error || $db_connection_error): ?>
                        <p class="text-xs text-red-400 mt-3"><i class="fas fa-exclamation-triangle mr-1"></i>
                            <?php echo $db_connection_error ? 'Database connection error.' : 'Error checking enrollment status.'; ?>
                            Please try refreshing the page or contact support.
                        </p>
                    <?php elseif ($csrf_token === null && $button_config['tag'] === 'button'): ?>
                        <p class="text-xs text-red-400 mt-3"><i class="fas fa-exclamation-triangle mr-1"></i> Security token
                            error. Please refresh the page.</p>
                    <?php endif; ?>
                </div>
                <!-- Right Column: Image/Video Preview -->
                <div class="md:w-1/3 flex-shrink-0 mt-6 md:mt-0">
                    <div
                        class="relative overflow-hidden rounded-lg shadow-lg border border-gray-700 aspect-video cursor-pointer group">
                        <img src="https://images.unsplash.com/photo-1607252650355-f7fd0460ccdb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80"
                            alt="<?php safe_echo($course_title_display); ?> Preview"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/40 to-transparent flex items-center justify-center">
                            <button
                                class="flex items-center justify-center w-16 h-16 bg-white/10 backdrop-blur-sm rounded-full hover:bg-white/20 transition border border-white/20 text-white hover:text-primary-300 transform group-hover:scale-110"
                                aria-label="Play course preview">
                                <i class="fas fa-play text-2xl pl-1"></i>
                            </button>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black/80 to-transparent">
                            <p class="text-white font-medium text-sm text-center">Watch Course Preview</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Progress Section -->
        <?php if ($user_logged_in): ?>
            <section class="content-card" aria-labelledby="progress-heading">
                <div class="flex flex-wrap justify-between items-center gap-4 mb-4">
                    <h2 id="progress-heading" class="text-xl font-bold flex items-center text-white">
                        <i class="fas fa-tasks text-primary-500 mr-3" aria-hidden="true"></i>Your Learning Progress
                    </h2>
                    <span id="progress-text" class="text-sm text-gray-400 font-medium tabular-nums"
                        aria-live="polite">Calculating...</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-3 mb-4 overflow-hidden progress-container" role="progressbar"
                    aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" aria-labelledby="progress-text">
                    <div id="progress-bar" class="h-full rounded-full progress-bar bg-primary-600" style="width: 0%"></div>
                </div>
                <div class="mt-5 flex flex-wrap justify-center items-center gap-4">
                    <a href="resource/AI & Machine-Certificate.png"  id="certificate-download-button" download="AI & Machine-Certificate.png"
                        class="hidden inline-flex items-center justify-center px-5 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
                        aria-label="Download your course completion certificate">
                        <i class="fas fa-certificate mr-2"></i>Download Certificate
                    </a>
                    <button type="button" id="reset-progress-button"
                        class="inline-flex items-center justify-center px-4 py-1.5 border border-red-500/50 text-red-400 hover:bg-red-500/20 hover:text-red-300 text-xs font-medium rounded-lg transition duration-300 ease-in-out"
                        aria-label="Reset course progress">
                        <i class="fas fa-undo-alt mr-1.5 text-xs"></i>Reset Progress
                    </button>
                </div>
            </section>
        <?php endif; ?>

        <!-- Course Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 md:gap-10 mb-12">
            <!-- Main Content Column -->
            <div class="lg:col-span-2 space-y-8 md:space-y-10">
                <!-- What You'll Learn -->
                <section class="content-card">
                    <h2 class="text-2xl font-bold mb-5 text-white section-title-gradient">What You'll Learn</h2>
                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-gray-300">
                        <li class="flex items-start"><i
                                class="fas fa-check-circle text-primary-500 mt-1 mr-2.5 flex-shrink-0"></i><span>Build
                                dynamic user interfaces with SwiftUI</span></li>
                        <li class="flex items-start"><i
                                class="fas fa-check-circle text-primary-500 mt-1 mr-2.5 flex-shrink-0"></i><span>Master
                                state management using @State, @Binding, @EnvironmentObject</span></li>
                        <li class="flex items-start"><i
                                class="fas fa-check-circle text-primary-500 mt-1 mr-2.5 flex-shrink-0"></i><span>Implement
                                complex navigation flows</span></li>
                        <li class="flex items-start"><i
                                class="fas fa-check-circle text-primary-500 mt-1 mr-2.5 flex-shrink-0"></i><span>Work
                                with data using Core Data and networking</span></li>
                        <li class="flex items-start"><i
                                class="fas fa-check-circle text-primary-500 mt-1 mr-2.5 flex-shrink-0"></i><span>Integrate
                                with UIKit components when necessary</span></li>
                        <li class="flex items-start"><i
                                class="fas fa-check-circle text-primary-500 mt-1 mr-2.5 flex-shrink-0"></i><span>Understand
                                the Combine framework for asynchronous events</span></li>
                    </ul>
                </section>

                <!-- Requirements -->
                <section class="content-card">
                    <h2 class="text-2xl font-bold mb-5 text-white section-title-gradient">Requirements</h2>
                    <ul class="space-y-3 text-gray-300">
                        <li class="flex items-start"><i
                                class="fab fa-apple text-primary-500 mt-1 mr-2.5 flex-shrink-0 fa-fw"></i><span>A Mac
                                computer running macOS Monterey or later</span></li>
                        <li class="flex items-start"><i
                                class="fas fa-code text-primary-500 mt-1 mr-2.5 flex-shrink-0 fa-fw"></i><span>Xcode 13
                                or later installed (free from App Store)</span></li>
                        <li class="flex items-start"><i
                                class="fas fa-mouse-pointer text-primary-500 mt-1 mr-2.5 flex-shrink-0 fa-fw"></i><span>Basic
                                programming concepts are helpful but not strictly required</span></li>
                    </ul>
                </section>

                <!-- Curriculum Section -->
                <section class="content-card" id="curriculum-section">
                    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
                        <h2 class="text-2xl font-bold text-white section-title-gradient">Course Curriculum</h2>
                        <span class="text-sm text-gray-400"><?php echo $rendered_lessons_count_ios; ?> Lessons</span>
                    </div>
                    <p class="text-sm text-gray-400 mb-6 -mt-4">Check the box or click the play icon to mark a lesson as
                        complete and track your progress.</p>

                    <!-- Module 1: SwiftUI Essentials -->
                    <div class="mb-6">
                        <button
                            class="module-header flex items-center justify-between w-full mb-4 px-4 py-3 rounded-lg cursor-pointer text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-800"
                            data-module="1" aria-expanded="true" aria-controls="module-content-1">
                            <h3 class="text-xl font-semibold text-primary-400">Module 1: SwiftUI Essentials</h3>
                            <div class="flex items-center space-x-3">
                                <span
                                    class="text-xs text-gray-400 bg-gray-700/50 px-2 py-0.5 rounded-full font-medium">4
                                    lessons</span>
                                <i
                                    class="fas fa-chevron-down accordion-icon text-gray-400 transition-transform duration-300"></i>
                            </div>
                        </button>
                        <div class="module-content space-y-3 pl-4 border-l-2 border-gray-700 ml-2" id="module-content-1"
                            role="region">
                            <div class="lesson-item">
                                <input type="checkbox" id="lesson-1-ios" class="lesson-checkbox hidden"
                                    data-lesson-id="lesson-1-ios">
                                <label for="lesson-1-ios">
                                    <span class="checkbox-visual"><i class="fas fa-check text-xs"></i></span>
                                    <span>Introduction to SwiftUI and Declarative Syntax</span>
                                </label>
                                <div class="lesson-actions">
                                    <span class="text-sm text-gray-400 tabular-nums">25 min</span>
                                    <a href="https://www.youtube.com/watch?v=7jziCb31ftc&t=281s" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-lg has-tooltip relative"
                                        title="Start Lesson" data-lesson-id="lesson-1-ios">
                                        <i class="fas fa-play-circle"></i><span class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <div class="lesson-item">
                                <input type="checkbox" id="lesson-2-ios" class="lesson-checkbox hidden"
                                    data-lesson-id="lesson-2-ios">
                                <label for="lesson-2-ios">
                                    <span class="checkbox-visual"><i class="fas fa-check text-xs"></i></span>
                                    <span>Views and Modifiers</span>
                                </label>
                                <div class="lesson-actions">
                                    <span class="text-sm text-gray-400 tabular-nums">35 min</span>
                                    <a href="https://www.youtube.com/watch?v=s22TboJomT4" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-lg has-tooltip relative"
                                        title="Start Lesson" data-lesson-id="lesson-2-ios">
                                        <i class="fas fa-play-circle"></i><span class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <div class="lesson-item">
                                <input type="checkbox" id="lesson-3-ios" class="lesson-checkbox hidden"
                                    data-lesson-id="lesson-3-ios">
                                <label for="lesson-3-ios">
                                    <span class="checkbox-visual"><i class="fas fa-check text-xs"></i></span>
                                    <span>State and Data Flow</span>
                                </label>
                                <div class="lesson-actions">
                                    <span class="text-sm text-gray-400 tabular-nums">45 min</span>
                                    <a href="https://www.youtube.com/watch?v=6Jc6-INantQ" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-lg has-tooltip relative"
                                        title="Start Lesson" data-lesson-id="lesson-3-ios">
                                        <i class="fas fa-play-circle"></i><span class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <div class="lesson-item">
                                <input type="checkbox" id="lesson-4-ios" class="lesson-checkbox hidden"
                                    data-lesson-id="lesson-4-ios">
                                <label for="lesson-4-ios">
                                    <span class="checkbox-visual"><i class="fas fa-check text-xs"></i></span>
                                    <span>Building Layouts with Stacks</span>
                                </label>
                                <div class="lesson-actions">
                                    <span class="text-sm text-gray-400 tabular-nums">40 min</span>
                                    <a href="https://www.youtube.com/watch?v=Sxxw3qtb3_g" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-lg has-tooltip relative"
                                        title="Start Lesson" data-lesson-id="lesson-4-ios">
                                        <i class="fas fa-play-circle"></i><span class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Module 2: Data and Navigation -->
                    <div class="mb-6">
                        <button
                            class="module-header flex items-center justify-between w-full mb-4 px-4 py-3 rounded-lg cursor-pointer text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-500 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-800"
                            data-module="2" aria-expanded="false" aria-controls="module-content-2"
                            style="border-left-color: #a855f7;">
                            <h3 class="text-xl font-semibold text-purple-400">Module 2: Data and Navigation</h3>
                            <div class="flex items-center space-x-3">
                                <span
                                    class="text-xs text-gray-400 bg-gray-700/50 px-2 py-0.5 rounded-full font-medium">5
                                    lessons</span>
                                <i
                                    class="fas fa-chevron-down accordion-icon text-gray-400 transition-transform duration-300 rotate-180"></i>
                            </div>
                        </button>
                        <div class="module-content space-y-3 pl-4 border-l-2 border-purple-500/50 ml-2 hidden"
                            id="module-content-2" role="region">
                            <div class="lesson-item">
                                <input type="checkbox" id="lesson-5-ios" class="lesson-checkbox hidden"
                                    data-lesson-id="lesson-5-ios">
                                <label for="lesson-5-ios">
                                    <span class="checkbox-visual"><i class="fas fa-check text-xs"></i></span>
                                    <span>Working with ObservableObject</span>
                                </label>
                                <div class="lesson-actions">
                                    <span class="text-sm text-gray-400 tabular-nums">50 min</span>
                                    <a href="https://www.youtube.com/watch?v=-yjKAb0Pj60" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-lg has-tooltip relative"
                                        title="Start Lesson" data-lesson-id="lesson-5-ios">
                                        <i class="fas fa-play-circle"></i><span class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <div class="lesson-item">
                                <input type="checkbox" id="lesson-6-ios" class="lesson-checkbox hidden"
                                    data-lesson-id="lesson-6-ios">
                                <label for="lesson-6-ios">
                                    <span class="checkbox-visual"><i class="fas fa-check text-xs"></i></span>
                                    <span>Navigation in SwiftUI</span>
                                </label>
                                <div class="lesson-actions">
                                    <span class="text-sm text-gray-400 tabular-nums">40 min</span>
                                    <a href="https://www.youtube.com/watch?v=oxp8Qqwr4AY" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-lg has-tooltip relative"
                                        title="Start Lesson" data-lesson-id="lesson-6-ios">
                                        <i class="fas fa-play-circle"></i><span class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <div class="lesson-item">
                                <input type="checkbox" id="lesson-7-ios" class="lesson-checkbox hidden"
                                    data-lesson-id="lesson-7-ios">
                                <label for="lesson-7-ios">
                                    <span class="checkbox-visual"><i class="fas fa-check text-xs"></i></span>
                                    <span>Core Data with SwiftUI</span>
                                </label>
                                <div class="lesson-actions">
                                    <span class="text-sm text-gray-400 tabular-nums">55 min</span>
                                    <a href="https://www.youtube.com/watch?v=BPQkpxtgalY" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-lg has-tooltip relative"
                                        title="Start Lesson" data-lesson-id="lesson-7-ios">
                                        <i class="fas fa-play-circle"></i><span class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <div class="lesson-item">
                                <input type="checkbox" id="lesson-8-ios" class="lesson-checkbox hidden"
                                    data-lesson-id="lesson-8-ios">
                                <label for="lesson-8-ios">
                                    <span class="checkbox-visual"><i class="fas fa-check text-xs"></i></span>
                                    <span>Networking with URLSession</span>
                                </label>
                                <div class="lesson-actions">
                                    <span class="text-sm text-gray-400 tabular-nums">48 min</span>
                                    <a href="https://www.youtube.com/watch?v=ERr0GXqILgc" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-lg has-tooltip relative"
                                        title="Start Lesson" data-lesson-id="lesson-8-ios">
                                        <i class="fas fa-play-circle"></i><span class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <div class="lesson-item">
                                <input type="checkbox" id="lesson-9-ios" class="lesson-checkbox hidden"
                                    data-lesson-id="lesson-9-ios">
                                <label for="lesson-9-ios">
                                    <span class="checkbox-visual"><i class="fas fa-check text-xs"></i></span>
                                    <span>Working with JSON Data</span>
                                </label>
                                <div class="lesson-actions">
                                    <span class="text-sm text-gray-400 tabular-nums">42 min</span>
                                    <a href="https://www.youtube.com/watch?v=9N6a-VLBa2I" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-lg has-tooltip relative"
                                        title="Start Lesson" data-lesson-id="lesson-9-ios">
                                        <i class="fas fa-play-circle"></i><span class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Module 3: Advanced SwiftUI -->
                    <div class="mb-6">
                        <button
                            class="module-header flex items-center justify-between w-full mb-4 px-4 py-3 rounded-lg cursor-pointer text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-800"
                            data-module="3" aria-expanded="false" aria-controls="module-content-3"
                            style="border-left-color: #22c55e;">
                            <h3 class="text-xl font-semibold text-green-400">Module 3: Advanced SwiftUI</h3>
                            <div class="flex items-center space-x-3">
                                <span
                                    class="text-xs text-gray-400 bg-gray-700/50 px-2 py-0.5 rounded-full font-medium">5
                                    lessons</span>
                                <i
                                    class="fas fa-chevron-down accordion-icon text-gray-400 transition-transform duration-300 rotate-180"></i>
                            </div>
                        </button>
                        <div class="module-content space-y-3 pl-4 border-l-2 border-green-500/50 ml-2 hidden"
                            id="module-content-3" role="region">
                            <div class="lesson-item">
                                <input type="checkbox" id="lesson-10-ios" class="lesson-checkbox hidden"
                                    data-lesson-id="lesson-10-ios">
                                <label for="lesson-10-ios">
                                    <span class="checkbox-visual"><i class="fas fa-check text-xs"></i></span>
                                    <span>Custom Views and ViewModifiers</span>
                                </label>
                                <div class="lesson-actions">
                                    <span class="text-sm text-gray-400 tabular-nums">45 min</span>
                                    <a href="https://www.youtube.com/watch?v=MQl4DlDf_5k" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-lg has-tooltip relative"
                                        title="Start Lesson" data-lesson-id="lesson-10-ios">
                                        <i class="fas fa-play-circle"></i><span class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <div class="lesson-item">
                                <input type="checkbox" id="lesson-11-ios" class="lesson-checkbox hidden"
                                    data-lesson-id="lesson-11-ios">
                                <label for="lesson-11-ios">
                                    <span class="checkbox-visual"><i class="fas fa-check text-xs"></i></span>
                                    <span>Animations and Transitions</span>
                                </label>
                                <div class="lesson-actions">
                                    <span class="text-sm text-gray-400 tabular-nums">50 min</span>
                                    <a href="https://www.youtube.com/watch?v=z2LQYsZhsFw" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-lg has-tooltip relative"
                                        title="Start Lesson" data-lesson-id="lesson-11-ios">
                                        <i class="fas fa-play-circle"></i><span class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <div class="lesson-item">
                                <input type="checkbox" id="lesson-12-ios" class="lesson-checkbox hidden"
                                    data-lesson-id="lesson-12-ios">
                                <label for="lesson-12-ios">
                                    <span class="checkbox-visual"><i class="fas fa-check text-xs"></i></span>
                                    <span>Combine Framework Basics</span>
                                </label>
                                <div class="lesson-actions">
                                    <span class="text-sm text-gray-400 tabular-nums">60 min</span>
                                    <a href="https://www.youtube.com/watch?v=2ORJcQgP4a0" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-lg has-tooltip relative"
                                        title="Start Lesson" data-lesson-id="lesson-12-ios">
                                        <i class="fas fa-play-circle"></i><span class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <div class="lesson-item">
                                <input type="checkbox" id="lesson-13-ios" class="lesson-checkbox hidden"
                                    data-lesson-id="lesson-13-ios">
                                <label for="lesson-13-ios">
                                    <span class="checkbox-visual"><i class="fas fa-check text-xs"></i></span>
                                    <span>Testing SwiftUI Apps</span>
                                </label>
                                <div class="lesson-actions">
                                    <span class="text-sm text-gray-400 tabular-nums">48 min</span>
                                    <a href="https://www.youtube.com/watch?v=uwpUQIUmw2g" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-lg has-tooltip relative"
                                        title="Start Lesson" data-lesson-id="lesson-13-ios">
                                        <i class="fas fa-play-circle"></i><span class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                            <div class="lesson-item">
                                <input type="checkbox" id="lesson-14-ios" class="lesson-checkbox hidden"
                                    data-lesson-id="lesson-14-ios">
                                <label for="lesson-14-ios">
                                    <span class="checkbox-visual"><i class="fas fa-check text-xs"></i></span>
                                    <span>App Store Submission</span>
                                </label>
                                <div class="lesson-actions">
                                    <span class="text-sm text-gray-400 tabular-nums">35 min</span>
                                    <a href="https://www.youtube.com/watch?v=bz_KJdXylh0" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-lg has-tooltip relative"
                                        title="Start Lesson" data-lesson-id="lesson-14-ios">
                                        <i class="fas fa-play-circle"></i><span class="tooltip">Start Lesson</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Instructor Section -->
                <section class="content-card">
                    <h2 class="text-2xl font-bold mb-6 text-white section-title-gradient">About the Instructor</h2>
                    <div class="flex flex-col md:flex-row items-start gap-6">
                        <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="Emma Rodriguez"
                            class="w-24 h-24 rounded-full object-cover flex-shrink-0 border-4 border-primary-700/50 shadow-md">
                        <div class="flex-grow">
                            <h3 class="text-xl font-bold mb-1 text-white">Emma Rodriguez</h3>
                            <p class="text-primary-400 font-medium mb-3 text-sm">Senior iOS Engineer | Apple Alumni</p>
                            <p class="text-gray-300 mb-4 text-sm leading-relaxed">With 8+ years of experience building
                                scalable and user-friendly iOS applications for millions of users worldwide, Emma brings
                                deep expertise in Swift, SwiftUI, and the Apple ecosystem. She's passionate about
                                mentoring the next generation of iOS developers.</p>
                            <div
                                class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-gray-400 border-t border-gray-700 pt-4 mt-4">
                                <div class="flex items-center" title="Instructor Rating">
                                    <i class="fas fa-star text-yellow-400 mr-1.5"></i>
                                    <span class="font-medium text-gray-300">4.9</span> Rating
                                </div>
                                <div class="flex items-center" title="Total Students">
                                    <i class="fas fa-user-graduate text-gray-500 mr-1.5"></i>
                                    <span class="font-medium text-gray-300">15,800</span> Students
                                </div>
                                <div class="flex items-center" title="Courses Offered">
                                    <i class="fas fa-book-open text-gray-500 mr-1.5"></i>
                                    <span class="font-medium text-gray-300">6</span> Courses
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="sticky top-28 space-y-6 md:space-y-8"> <!-- Adjusted top offset -->
                    <!-- Course Features -->
                    <div class="content-card !p-5"> <!-- Slightly less padding -->
                        <h3 class="text-lg font-bold mb-4 text-white">This Course Includes:</h3>
                        <ul class="space-y-3 text-sm text-gray-300">
                            <li class="flex items-center"><i
                                    class="fas fa-video text-primary-500 mr-3 w-5 text-center fa-fw"></i><span><span
                                        class="font-medium text-white">55 hours</span> on-demand video</span></li>
                            <li class="flex items-center"><i
                                    class="fas fa-file-alt text-primary-500 mr-3 w-5 text-center fa-fw"></i><span><span
                                        class="font-medium text-white">40</span> downloadable resources</span></li>
                            <li class="flex items-center"><i
                                    class="fas fa-mobile-alt text-primary-500 mr-3 w-5 text-center fa-fw"></i><span>Access
                                    on mobile and TV</span></li>
                            <li class="flex items-center"><i
                                    class="fas fa-project-diagram text-primary-500 mr-3 w-5 text-center fa-fw"></i><span><span
                                        class="font-medium text-white">4</span> complete iOS projects</span></li>
                            <li class="flex items-center"><i
                                    class="fas fa-infinity text-primary-500 mr-3 w-5 text-center fa-fw"></i><span>Full
                                    lifetime access</span></li>
                            <li class="flex items-center"><i
                                    class="fas fa-certificate text-primary-500 mr-3 w-5 text-center fa-fw"></i><span>Certificate
                                    of completion</span></li>
                        </ul>
                    </div>

                    <!-- Resources -->
                    <div class="content-card !p-5">
                        <h3 class="text-lg font-bold mb-4 text-white">Resources</h3>
                        <div class="space-y-2">
                            <a href="https://developer.apple.com/tutorials/swiftui" target="_blank"
                                rel="noopener noreferrer"
                                class="flex items-center p-3 -m-3 hover:bg-gray-700/50 rounded-lg transition group">
                                <div
                                    class="bg-blue-600/20 text-blue-400 group-hover:text-blue-300 p-2 rounded-lg mr-3 flex-shrink-0 w-10 h-10 flex items-center justify-center ring-1 ring-blue-600/30 group-hover:ring-blue-500/70 transition">
                                    <i class="fab fa-apple fa-lg"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-sm text-gray-200 group-hover:text-white">Official
                                        SwiftUI Tutorials</h4>
                                    <p class="text-xs text-gray-400">Apple Developer Docs</p>
                                </div>
                                <i
                                    class="fas fa-external-link-alt text-gray-500 ml-auto group-hover:text-blue-400 transition text-xs opacity-70 group-hover:opacity-100"></i>
                            </a>
                            <a href="https://github.com/topics/swiftui-sample-apps" target="_blank"
                                rel="noopener noreferrer"
                                class="flex items-center p-3 -m-3 hover:bg-gray-700/50 rounded-lg transition group">
                                <div
                                    class="bg-purple-600/20 text-purple-400 group-hover:text-purple-300 p-2 rounded-lg mr-3 flex-shrink-0 w-10 h-10 flex items-center justify-center ring-1 ring-purple-600/30 group-hover:ring-purple-500/70 transition">
                                    <i class="fas fa-code-branch fa-lg"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-sm text-gray-200 group-hover:text-white">Sample SwiftUI
                                        Apps</h4>
                                    <p class="text-xs text-gray-400">GitHub Repositories</p>
                                </div>
                                <i
                                    class="fas fa-external-link-alt text-gray-500 ml-auto group-hover:text-purple-400 transition text-xs opacity-70 group-hover:opacity-100"></i>
                            </a>
                            <a href="https://discord.gg/swift" target="_blank" rel="noopener noreferrer"
                                class="flex items-center p-3 -m-3 hover:bg-gray-700/50 rounded-lg transition group">
                                <div
                                    class="bg-indigo-600/20 text-indigo-400 group-hover:text-indigo-300 p-2 rounded-lg mr-3 flex-shrink-0 w-10 h-10 flex items-center justify-center ring-1 ring-indigo-600/30 group-hover:ring-indigo-500/70 transition">
                                    {/* Changed color */}
                                    <i class="fab fa-discord fa-lg"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-sm text-gray-200 group-hover:text-white">Swift Community
                                    </h4>
                                    <p class="text-xs text-gray-400">Discord Server</p>
                                </div>
                                <i
                                    class="fas fa-external-link-alt text-gray-500 ml-auto group-hover:text-indigo-400 transition text-xs opacity-70 group-hover:opacity-100"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Related Courses -->
                    <div class="content-card !p-5">
                        <h3 class="text-lg font-bold mb-4 text-white">Students Also Viewed</h3>
                        <div class="space-y-4">
                            <a href="react-masterclass.php"
                                class="flex items-center hover:bg-gray-700/50 p-3 -m-3 rounded-lg transition group">
                                <img src="https://images.unsplash.com/photo-1633356122544-f134324a6cee?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=60"
                                    alt="React.js Masterclass"
                                    class="w-16 h-12 rounded-md object-cover mr-3 flex-shrink-0 border border-gray-700 group-hover:border-primary-600 transition">
                                <div>
                                    <h4
                                        class="font-medium text-sm leading-tight text-gray-200 group-hover:text-white transition">
                                        React.js Masterclass</h4>
                                    <div class="flex items-center text-xs text-gray-400 mt-1">
                                        <i class="fas fa-star text-yellow-400 mr-1 text-[11px]"></i><span>4.9 • 65k
                                            students</span>
                                    </div>
                                </div>
                            </a>
                            <a href="js_course.php"
                                class="flex items-center hover:bg-gray-700/50 p-3 -m-3 rounded-lg transition group">
                                <img src="https://images.unsplash.com/photo-1579468118864-1b9ea3c0db4a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=60"
                                    alt="JavaScript Course"
                                    class="w-16 h-12 rounded-md object-cover mr-3 flex-shrink-0 border border-gray-700 group-hover:border-primary-600 transition">
                                <div>
                                    <h4
                                        class="font-medium text-sm leading-tight text-gray-200 group-hover:text-white transition">
                                        Modern JavaScript Mastery</h4>
                                    <div class="flex items-center text-xs text-gray-400 mt-1">
                                        <i class="fas fa-star text-yellow-400 mr-1 text-[11px]"></i><span>4.8 • 42k
                                            students</span>
                                    </div>
                                </div>
                            </a>
                            <!-- Add more related courses if needed -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 border-t border-gray-700 py-12 mt-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8">
                <div class="col-span-2 lg:col-span-1">
                    <a href="1.Home.php" class="flex items-center mb-4 text-white">
                        <i class="fas fa-graduation-cap text-primary-500 text-2xl mr-2"></i>
                        <span class="text-xl font-bold">EduPro</span>
                    </a>
                    <p class="text-gray-400 mb-4 text-sm">Empowering learners through accessible and engaging online
                        education.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition" aria-label="Facebook"><i
                                class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition" aria-label="Twitter"><i
                                class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition" aria-label="LinkedIn"><i
                                class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition" aria-label="GitHub"><i
                                class="fab fa-github"></i></a>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Courses</h3>
                    <ul class="space-y-2">
                        <li><a href="web_dev_course.php" class="text-sm text-gray-400 hover:text-white transition">Web
                                Development</a></li>
                        <li><a href="python_course.php" class="text-sm text-gray-400 hover:text-white transition">Python
                                Programming</a></li>
                        <li><a href="data_science_course.php"
                                class="text-sm text-gray-400 hover:text-white transition">Data Science</a></li>
                        <li><a href="js_course.php"
                                class="text-sm text-gray-400 hover:text-white transition">JavaScript</a></li>
                        <li><a href="ios-swiftui-dev.php" class="text-sm text-gray-400 hover:text-white transition">iOS
                                Development</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Company</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">About Us</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Careers</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Blog</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Press</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Support</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Help Center</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Contact Us</a></li>
                        <li><a href="FAQ.php" class="text-sm text-gray-400 hover:text-white transition">FAQ</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Community</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Legal</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Terms of Service</a>
                        </li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Privacy Policy</a>
                        </li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Cookie Policy</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Accessibility</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-10 pt-8 border-t border-gray-700 text-center">
                <p class="text-gray-500 text-sm">© <?php echo date('Y'); ?> EduPro. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isUserLoggedIn = <?php echo json_encode($user_logged_in); ?>;
            const courseSlug = <?php echo json_encode($course_slug); ?>;
            const totalLessons = <?php echo $rendered_lessons_count_ios; ?>;
            const isPaid = <?php echo json_encode($is_paid_course); ?>;
            const csrfToken = <?php echo json_encode($csrf_token); ?>;

            // --- Mobile menu toggle ---
            const mobileMenuButton = document.querySelector('.mobile-menu-button');
            const mobileMenu = document.querySelector('.mobile-menu');
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function () {
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';
                    mobileMenu.classList.toggle('hidden');
                    this.setAttribute('aria-expanded', !isExpanded);
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-bars', isExpanded);
                        icon.classList.toggle('fa-times', !isExpanded);
                    }
                });
                // Close mobile menu on link click
                mobileMenu.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', () => {
                        if (!mobileMenu.classList.contains('hidden')) {
                            mobileMenu.classList.add('hidden');
                            mobileMenuButton.setAttribute('aria-expanded', 'false');
                            const icon = mobileMenuButton.querySelector('i');
                            if (icon) { icon.classList.remove('fa-times'); icon.classList.add('fa-bars'); }
                        }
                    });
                });
            }

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

                            // Optionally close mobile menu if open
                            if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                                mobileMenu.classList.add('hidden');
                                const menuIcon = mobileMenuButton?.querySelector('i');
                                if (menuIcon) { menuIcon.classList.remove('fa-times'); menuIcon.classList.add('fa-bars'); }
                                mobileMenuButton?.setAttribute('aria-expanded', 'false');
                            }
                            // Optionally add focus to the target section for accessibility
                            // setTimeout(() => { targetElement.focus(); }, 300); // Add tabindex="-1" to target sections
                        }
                    }
                });
            });

            // --- Curriculum Accordion ---
            const moduleHeaders = document.querySelectorAll('.module-header');
            moduleHeaders.forEach(header => {
                header.addEventListener('click', function () {
                    const content = document.getElementById(this.getAttribute('aria-controls'));
                    const icon = this.querySelector('.accordion-icon');
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';

                    this.setAttribute('aria-expanded', !isExpanded);
                    if (content) {
                        content.classList.toggle('hidden');
                    }
                    if (icon) {
                        icon.classList.toggle('rotate-180');
                    }

                    // Optional: Close other accordions when one is opened
                    // moduleHeaders.forEach(otherHeader => {
                    //     if (otherHeader !== this && otherHeader.getAttribute('aria-expanded') === 'true') {
                    //         otherHeader.click(); // Simulate click to close
                    //     }
                    // });
                });

                // Ensure initial state matches aria-expanded
                const initialContent = document.getElementById(header.getAttribute('aria-controls'));
                const initialIcon = header.querySelector('.accordion-icon');
                if (initialContent && initialIcon) {
                    if (header.getAttribute('aria-expanded') === 'true') {
                        initialContent.classList.remove('hidden');
                        initialIcon.classList.remove('rotate-180');
                    } else {
                        initialContent.classList.add('hidden');
                        initialIcon.classList.add('rotate-180');
                    }
                }
            });

            // --- Progress Bar, Lesson Completion & Reset Logic ---
            if (isUserLoggedIn) {
                const checkboxes = document.querySelectorAll('.lesson-checkbox');
                const progressBar = document.getElementById('progress-bar');
                const progressText = document.getElementById('progress-text');
                const completionBadge = document.getElementById('completion-badge');
                const certificateButton = document.getElementById('certificate-download-button');
                const resetProgressButton = document.getElementById('reset-progress-button');
                const playLinks = document.querySelectorAll('.play-lesson-link');

                const getStorageKey = (lessonId) => `completed_${lessonId}_${courseSlug}`;

                // Update overall progress UI
                function updateOverallProgressUI(completedCount) {
                    if (!progressBar || !progressText || totalLessons <= 0) return;

                    const progress = Math.round((completedCount / totalLessons) * 100);
                    progressBar.style.width = `${progress}%`;
                    progressBar.setAttribute('aria-valuenow', progress);
                    progressText.textContent = `${completedCount} / ${totalLessons} lessons completed (${progress}%)`;

                    // Update progress bar color based on completion percentage
                    let barColorClass = 'bg-primary-600'; // Default
                    if (progress === 100) barColorClass = 'bg-green-500';
                    else if (progress > 75) barColorClass = 'bg-green-400'; // Adjusted thresholds
                    else if (progress > 50) barColorClass = 'bg-yellow-500';
                    else if (progress > 25) barColorClass = 'bg-orange-500';
                    else if (progress > 0) barColorClass = 'bg-red-500';

                    // Apply the color class, ensuring others are removed
                    progressBar.classList.remove('bg-primary-600', 'bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-400', 'bg-green-500');
                    progressBar.classList.add(barColorClass);


                    // Update completion badge in nav
                    if (completionBadge) {
                        if (completedCount > 0) {
                            completionBadge.textContent = completedCount;
                            completionBadge.classList.remove('hidden');
                        } else {
                            completionBadge.classList.add('hidden');
                        }
                    }

                    // Show/hide certificate button
                    if (certificateButton) {
                        if (progress >= 100) {
                            certificateButton.classList.remove('hidden');
                            certificateButton.removeAttribute('disabled');
                            certificateButton.removeAttribute('aria-disabled');
                        } else {
                            certificateButton.classList.add('hidden');
                            certificateButton.setAttribute('disabled', '');
                            certificateButton.setAttribute('aria-disabled', 'true');
                        }
                    }
                }

                // Get count of completed lessons from localStorage
                function getCompletedLessonsCount() {
                    let count = 0;
                    checkboxes.forEach(checkbox => {
                        const lessonId = checkbox.id;
                        if (lessonId && localStorage.getItem(getStorageKey(lessonId)) === 'true') {
                            count++;
                        }
                    });
                    return count;
                }

                // Update checkbox visual style based on checked state
                function updateCheckboxVisual(checkbox, isChecked) {
                    const label = checkbox.closest('.lesson-item')?.querySelector(`label[for="${checkbox.id}"]`);
                    if (!label) return;

                    label.classList.toggle('text-gray-500', isChecked); // Use Tailwind class
                    label.classList.toggle('line-through', isChecked);

                    const visualCheckbox = label.querySelector('.checkbox-visual');
                    if (visualCheckbox) {
                        visualCheckbox.classList.toggle('bg-primary-600', isChecked);
                        visualCheckbox.classList.toggle('border-primary-600', isChecked);
                        visualCheckbox.classList.toggle('bg-gray-700', !isChecked); // Ensure default bg is set when unchecked
                        visualCheckbox.classList.toggle('border-gray-500', !isChecked); // Ensure default border is set when unchecked
                    }
                    // Check icon opacity is handled by CSS rules based on :checked state
                }

                // Function to handle lesson completion (called by checkbox change or play link click)
                function completeLesson(lessonId, shouldComplete) {
                    const checkbox = document.getElementById(lessonId);
                    if (!checkbox) return;

                    const storageKey = getStorageKey(lessonId);
                    const currentlyCompleted = localStorage.getItem(storageKey) === 'true';

                    if (shouldComplete && !currentlyCompleted) {
                        localStorage.setItem(storageKey, 'true');
                        checkbox.checked = true;
                        updateCheckboxVisual(checkbox, true);
                        updateOverallProgressUI(getCompletedLessonsCount());
                    } else if (!shouldComplete && currentlyCompleted) {
                        localStorage.removeItem(storageKey);
                        checkbox.checked = false;
                        updateCheckboxVisual(checkbox, false);
                        updateOverallProgressUI(getCompletedLessonsCount());
                    } else {
                        // No change needed (e.g., clicking play on already completed lesson)
                        // or unchecking an already unchecked box
                    }
                }


                // Function to Reset Course Progress
                function resetCourseProgress() {
                    if (!confirm('Are you sure you want to reset your progress for this course? This action cannot be undone.')) {
                        return;
                    }
                    console.log(`Resetting progress for course: ${courseSlug}`);
                    checkboxes.forEach(checkbox => {
                        const lessonId = checkbox.id;
                        if (lessonId) {
                            localStorage.removeItem(getStorageKey(lessonId));
                            checkbox.checked = false; // Update DOM state
                            updateCheckboxVisual(checkbox, false); // Update visual style
                        }
                    });
                    updateOverallProgressUI(0); // Update progress bar/text/badge/certificate
                    alert('Course progress has been reset.');
                    console.log(`Progress reset complete for course: ${courseSlug}`);
                }

                // --- Initialization ---
                checkboxes.forEach(checkbox => {
                    const lessonId = checkbox.id;
                    if (!lessonId) return;

                    const isCompleted = localStorage.getItem(getStorageKey(lessonId)) === 'true';
                    checkbox.checked = isCompleted;
                    updateCheckboxVisual(checkbox, isCompleted);

                    // Add change listener to checkboxes
                    checkbox.addEventListener('change', function () {
                        completeLesson(this.id, this.checked);
                    });
                });

                // Add click listener to play links
                playLinks.forEach(link => {
                    link.addEventListener('click', function (event) {
                        // We don't preventDefault, we want the link to work
                        const lessonId = this.dataset.lessonId;
                        if (!lessonId) return;
                        // Mark as complete (if not already) when play is clicked
                        completeLesson(lessonId, true);
                    });
                });


                // Initial progress update on page load
                updateOverallProgressUI(getCompletedLessonsCount());

                // Add Event Listener for Reset Button
                if (resetProgressButton) {
                    resetProgressButton.addEventListener('click', resetCourseProgress);
                } else {
                    console.warn("Reset progress button not found.");
                }

            } // End if(isUserLoggedIn)

            // --- Enrollment Button Logic ---
            const enrollButton = document.getElementById('enroll-btn'); // Specific ID for the enrollment button
            const enrollmentActionArea = document.getElementById('enrollment-action-area');

            // Extract original button text content correctly (handling potential HTML like icons)
            const getElementTextContent = (element) => {
                if (!element) return '';
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = element.innerHTML; // Use innerHTML to preserve structure
                return tempDiv.innerHTML; // Return the full HTML content
            };
            const initialButtonHTML = getElementTextContent(enrollButton?.querySelector('.btn-text')); // Get HTML content


            if (enrollButton && enrollButton.tagName === 'BUTTON' && enrollmentActionArea) {
                const enrollButtonTextSpan = enrollButton.querySelector('.btn-text');
                const enrollButtonSpinner = enrollButton.querySelector('.spinner');

                enrollButton.addEventListener('click', function () {
                    const button = this; // Reference the button itself

                    // 1. Check Login Status (Client-side check first)
                    if (!isUserLoggedIn) {
                        alert('Please log in or sign up to enroll in this course.');
                        // Redirect to login, passing the current page as the redirect destination
                        window.location.href = `Login.php?redirect=${encodeURIComponent(window.location.pathname + window.location.search)}`;
                        return; // Stop execution
                    }

                    // 2. Handle Paid Course (Placeholder)
                    if (isPaid) {
                        alert('Paid course enrollment requires payment processing (not implemented in this demo).');
                        // In a real application, redirect to a checkout page here.
                        return; // Stop execution
                    }

                    // 3. CSRF Token Check
                    const currentCsrfToken = button.dataset.csrfToken;
                    if (!currentCsrfToken) {
                        console.error("CSRF token missing from button data attribute.");
                        alert('A security token is missing. Please refresh the page and try again.');
                        // Optionally disable the button permanently or add a more visible error
                        button.disabled = true;
                        button.textContent = 'Error';
                        button.classList.add('bg-red-600', 'cursor-not-allowed');
                        return;
                    }

                    // 4. Prepare for Fetch Request
                    const courseSlugToEnroll = button.dataset.courseSlug;

                    // Disable button and show loading state
                    button.disabled = true;
                    button.setAttribute('aria-disabled', 'true');
                    button.classList.add('opacity-60', 'cursor-wait');
                    if (enrollButtonTextSpan) { enrollButtonTextSpan.textContent = 'Processing...'; }
                    if (enrollButtonSpinner) { enrollButtonSpinner.classList.remove('hidden'); }

                    // Create FormData
                    const formData = new FormData();
                    formData.append('course_slug', courseSlugToEnroll);
                    formData.append('csrf_token', currentCsrfToken); // Use the token from the button

                    // 5. Perform Fetch Request
                    fetch('enroll_course.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => {
                            // Check for non-JSON responses or network errors first
                            if (!response.ok) {
                                // Try to get text for more specific errors
                                return response.text().then(text => {
                                    throw new Error(`Server Error ${response.status}: ${text || response.statusText || '(No server details)'}`);
                                });
                            }
                            const contentType = response.headers.get("content-type");
                            if (!contentType || !contentType.includes("application/json")) {
                                // Handle cases where the server didn't return JSON as expected
                                return response.text().then(text => {
                                    throw new Error(`Unexpected response format from server: ${text.substring(0, 100) || '(Empty response)'}`);
                                });
                            }
                            return response.json(); // Process valid JSON response
                        })
                        .then(data => {
                            // 6. Handle Fetch Response
                            if (data.success) {
                                console.log("Enrollment successful:", data.message);
                                // Update UI to show "Enrolled" status and "Continue Learning"
                                enrollmentActionArea.innerHTML = `
                                    <a href="#curriculum-section" id="continue-learning-link"
                                    class="btn-success text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center text-base shadow-md transform hover:scale-105">
                                        <i class="fas fa-play mr-2"></i>Continue Learning
                                    </a>
                                    <span class="flex items-center text-green-400 text-sm font-medium ml-2 pt-1 sm:pt-0">
                                        <i class="fas fa-check-circle mr-1.5"></i> You are enrolled
                                    </span>`;
                                // Re-attach smooth scroll to the new link if needed
                                document.querySelectorAll('a[href^="#"]').forEach(anchor => { /* ... re-add listener if complex */ });
                            } else {
                                // Handle specific error cases from the server
                                if (data.action === 'redirect_login') {
                                    alert('Your session may have expired. Please log in again.');
                                    window.location.href = `Login.php?redirect=${encodeURIComponent(window.location.pathname + window.location.search)}`;
                                } else if (data.message && data.message.toLowerCase().includes('already enrolled')) {
                                    // Gracefully handle if already enrolled (e.g., race condition or prior enrollment)
                                    console.warn("Attempted to enroll, but already enrolled:", data.message);
                                    enrollmentActionArea.innerHTML = `
                                        <a href="#curriculum-section" id="continue-learning-link" class="btn-success text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center text-base shadow-md transform hover:scale-105">
                                            <i class="fas fa-play mr-2"></i>Continue Learning
                                        </a>
                                        <span class="flex items-center text-green-400 text-sm font-medium ml-2 pt-1 sm:pt-0">
                                            <i class="fas fa-check-circle mr-1.5"></i> You are enrolled
                                        </span>`;
                                } else {
                                    // General enrollment failure
                                    alert('Enrollment failed: ' + (data.message || 'An unknown error occurred. Please try again.'));
                                    resetEnrollButton(); // Reset button to allow retry
                                }
                            }
                        })
                        .catch(error => {
                            // 7. Handle Fetch Errors (Network, Parsing, Thrown Errors)
                            console.error('Enrollment fetch/processing error:', error);
                            alert('Could not process enrollment request. Please check your connection and try again.\nError: ' + error.message);
                            resetEnrollButton(); // Reset button on error
                        });

                    // Helper function to reset the button to its original state
                    function resetEnrollButton() {
                        button.disabled = false;
                        button.removeAttribute('aria-disabled');
                        button.classList.remove('opacity-60', 'cursor-wait');
                        if (enrollButtonTextSpan) { enrollButtonTextSpan.innerHTML = initialButtonHTML; } // Restore original HTML
                        if (enrollButtonSpinner) { enrollButtonSpinner.classList.add('hidden'); }
                    }
                }); // End enrollButton click listener

            } else if (enrollButton && enrollButton.tagName === 'A') {
                // If the initial button is a link (e.g., "Login to Enroll"), no fetch logic needed here.
                console.log("Enrollment action is a link, no fetch listener added.");
            } else if (document.getElementById('enroll-btn-disabled') || document.getElementById('enroll-btn-csrf-error')) {
                console.log("Enrollment button is disabled due to error state.");
            } else {
                console.warn("Enrollment button or action area not found or button is not a BUTTON tag.");
            } // End if(enrollButton)

        }); // End DOMContentLoaded
    </script>

</body>

</html>
<?php
// Close database connection if it exists, is open, and is a valid mysqli object
if (isset($mysqli) && $mysqli instanceof mysqli && $mysqli->thread_id && !$mysqli->connect_error) {
    $mysqli->close();
}
?>