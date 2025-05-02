<?php
// --- SETUP AND CONFIGURATION ---
// Ensure session is started only once
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php'; // Ensure this path is correct and sets up $mysqli

// --- Generate/Get CSRF Token ---
if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        error_log("Error generating CSRF token: " . $e->getMessage());
        // Provide a user-friendly error without dying if possible, or log and die
        die("A critical security error occurred. Please refresh the page or contact support if the problem persists.");
    }
}
$csrf_token = $_SESSION['csrf_token'] ?? null; // Use null coalescing

// --- Course Configuration ---
$course_slug = 'Cloud Computing Course'; // Unique identifier for this course
$course_title_display = 'Cloud Computing Essentials with AWS'; // Title from the page
$is_paid_course = false; // Set this dynamically based on the course slug

// --- User & Enrollment Status ---
$is_enrolled = false;
$user_logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['id']);
$user_id = $user_logged_in ? (int) $_SESSION['id'] : null;
$db_connection_error = false; // General DB connection flag
$enrollment_check_error = false; // Specific enrollment check flag

// Check DB connection only if needed (user logged in)
if ($user_logged_in) {
    if (!isset($mysqli) || !($mysqli instanceof mysqli) || $mysqli->connect_error) {
        error_log("Database connection error: " . ($mysqli->connect_error ?? 'mysqli object not available'));
        $db_connection_error = true;
        $enrollment_check_error = true; // Treat connection error as enrollment check error too
    } else {
        // Proceed with enrollment check
        $stmt = $mysqli->prepare("SELECT id FROM user_courses WHERE user_id = ? AND course_slug = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("is", $user_id, $course_slug); // Use the correct $course_slug
            if ($stmt->execute()) {
                $stmt->store_result();
                $is_enrolled = $stmt->num_rows > 0;
            } else {
                error_log("Enrollment check query failed for user $user_id, course $course_slug: " . $stmt->error);
                $enrollment_check_error = true;
            }
            $stmt->close();
        } else {
            error_log("Enrollment check prepare failed: " . $mysqli->error);
            $enrollment_check_error = true;
        }
    }
}

// --- Button Configuration ---
$button_config = [
    'text' => $is_paid_course ? 'Enroll Now' : 'Start Free Course',
    'tag' => 'button',
    'disabled' => false,
    'classes' => 'bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center text-base shadow-md transform hover:scale-105',
    'href' => '#',
    'id' => 'enroll-btn',
    'data_attributes' => [
        'course-slug' => $course_slug,
        'csrf-token' => $csrf_token ?? '' // Pass token safely
    ]
];

// Modify based on specific states
if ($is_enrolled) {
    $button_config['text'] = '<i class="fas fa-play mr-2"></i>Continue Learning';
    $button_config['tag'] = 'a';
    $button_config['href'] = '#curriculum-section';
    $button_config['classes'] = 'bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center text-base shadow-md transform hover:scale-105';
    $button_config['id'] = 'continue-learning-btn';
    $button_config['data_attributes'] = [];
    $button_config['disabled'] = false;

} elseif ($enrollment_check_error || $db_connection_error) {
    if ($user_logged_in) {
        $button_config['text'] = 'Service Unavailable';
        $button_config['tag'] = 'button';
        $button_config['disabled'] = true;
        $button_config['classes'] = 'bg-gray-600 cursor-not-allowed opacity-75 text-white px-6 py-3 rounded-lg font-medium flex items-center justify-center text-base shadow-md';
        $button_config['id'] = 'enroll-btn-disabled';
        $button_config['data_attributes'] = [];
    }
    // If not logged in, default "Start Free Course" is fine even with DB error

} elseif (!$user_logged_in && $is_paid_course) {
    $button_config['tag'] = 'a';
    $button_config['href'] = 'Login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']);
    $button_config['text'] = 'Login to Enroll';
    $button_config['classes'] = 'bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center text-base shadow-md';
    $button_config['id'] = 'login-to-enroll-btn';
    $button_config['data_attributes'] = [];
    $button_config['disabled'] = false;
}

// Helper function for safe echoing
function safe_echo($str)
{
    echo htmlspecialchars((string) ($str ?? ''), ENT_QUOTES, 'UTF-8');
}

// --- Calculate number of rendered lessons for initial placeholder ---
$rendered_lessons_count_aws = 6; // Manually counted: 2 (M1) + 2 (M2) + 2 (M3) = 6
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php safe_echo($course_title_display); ?> | EduPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { /* Tailwind config remains the same */
            theme: {
                extend: {
                    colors: {
                        primary: { 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af' },
                        dark: { 900: '#0f172a', 800: '#1e293b', 700: '#334155', 600: '#475569', 500: '#64748b' },
                        success: { 500: '#10b981', 600: '#059669', 700: '#047857' },
                        yellow: { 400: '#facc15', 500: '#eab308' },
                        red: { 500: '#ef4444' },
                        orange: { 500: '#f97316' }, // Added for progress bar
                        purple: { 500: '#a855f7', 600: '#9333ea' }, // Added for module
                        green: { 500: '#22c55e', 600: '#16a34a' }, // Added for module
                        gray: { 100: '#f3f4f6', 200: '#e5e7eb', 300: '#d1d5db', 400: '#9ca3af', 500: '#6b7280', 600: '#4b5563', 700: '#374151', 800: '#1f2937', 900: '#11182c' }
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
        /* CSS styles remain the same as the previous example */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        :root {
            --scroll-margin: 100px;
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: var(--scroll-margin);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #11182c;
            color: #d1d5db;
        }

        .has-tooltip {
            position: relative;
        }

        .tooltip {
            opacity: 0;
            transition: opacity 0.2s ease;
            pointer-events: none;
            position: absolute;
            z-index: 10;
            background-color: #1f2937;
            color: white;
            padding: 0.3rem 0.6rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            white-space: nowrap;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
        }

        .has-tooltip:hover .tooltip {
            opacity: 1;
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

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        button:disabled,
        a.disabled,
        a[disabled] {
            opacity: 0.6;
            cursor: not-allowed !important;
            pointer-events: none;
        }

        #curriculum-section {
            scroll-margin-top: 90px;
        }

        .lesson-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: rgba(55, 65, 81, 0.5);
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid #4b5563;
            transition: background-color 0.2s;
        }

        .lesson-item:hover {
            background-color: rgba(55, 65, 81, 0.8);
        }

        .lesson-item label {
            display: flex;
            align-items: center;
            cursor: pointer;
            flex-grow: 1;
            margin-right: 1rem;
            transition: color 0.2s, text-decoration 0.2s;
        }

        .lesson-item label.line-through {
            text-decoration: line-through;
        }

        .lesson-item label.text-gray-500 {
            color: #6b7280;
        }

        .lesson-item .checkbox-visual {
            width: 1.25rem;
            height: 1.25rem;
            border: 1px solid #6b7280;
            border-radius: 0.25rem;
            margin-right: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s, border-color 0.2s;
            flex-shrink: 0;
            position: relative;
        }

        .lesson-checkbox:checked+label .checkbox-visual {
            background-color: #2563eb;
            border-color: #2563eb;
        }

        .lesson-checkbox:checked+label .checkbox-visual .fa-check {
            color: white;
            opacity: 1;
        }

        .lesson-checkbox:not(:checked)+label .checkbox-visual .fa-check {
            opacity: 0;
        }

        .checkbox-visual .fa-check {
            transition: opacity 0.2s;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 0.7rem;
        }

        .lesson-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-shrink: 0;
        }

        .progress-bar {
            transition: width 0.5s ease-in-out, background-color 0.5s ease-in-out;
        }

        .progress-container {
            height: 0.75rem;
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

        .module-header {
            background: linear-gradient(90deg, rgba(51, 65, 85, 0.6) 0%, rgba(30, 41, 59, 0.9) 100%);
            border-left: 4px solid #2563eb;
            transition: background-color 0.2s ease;
            cursor: pointer;
        }

        .module-header:hover {
            background: linear-gradient(90deg, rgba(51, 65, 85, 0.8) 0%, rgba(30, 41, 59, 1) 100%);
        }

        .accordion-icon {
            transition: transform 0.3s ease;
        }

        .accordion-icon.rotate-180 {
            transform: rotate(180deg);
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

        .section-title {
            background: linear-gradient(to right, #e2e8f0, #94a3b8);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>

<body class="bg-gray-900 text-gray-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-gray-800 border-b border-gray-700 py-4 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="1.Home.php" class="flex items-center"><i
                            class="fas fa-graduation-cap text-primary-600 text-2xl"></i><span
                            class="ml-2 text-xl font-bold text-white">EduPro</span></a>
                    <div class="hidden md:flex space-x-6"><a href="courses_list.php"
                            class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Courses</a><a
                            href="resources.php"
                            class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Resources</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button
                        class="p-2 rounded-full text-gray-400 hover:text-white hover:bg-gray-700 transition relative has-tooltip"
                        aria-label="Search courses"><i class="fas fa-search"></i><span
                            class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-700 text-white text-xs py-1 px-2 rounded whitespace-nowrap shadow-lg">Search
                            courses</span></button>
                    <button
                        class="p-2 rounded-full text-gray-400 hover:text-white hover:bg-gray-700 transition relative has-tooltip"
                        aria-label="Notifications"><i class="fas fa-bell"></i><span
                            class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-700 text-white text-xs py-1 px-2 rounded whitespace-nowrap shadow-lg">Notifications</span></button>
                    <div class="relative">
                        <?php if ($user_logged_in): ?>
                            <div class="flex items-center space-x-2">
                                <a href="Dashboard.php"
                                    class="flex items-center hover:bg-gray-700 px-3 py-1 rounded-full transition relative"
                                    title="Dashboard">
                                    <img src="<?php echo $_SESSION['profile_image_url'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['username'] ?? 'U') . '&background=4b5563&color=f9fafb'; ?>"
                                        alt="Profile"
                                        class="w-8 h-8 rounded-full object-cover bg-gray-600 border border-gray-500">
                                    <span id="completion-badge"
                                        class="hidden absolute -top-1 -right-1 bg-primary-600 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center ring-2 ring-gray-800">0</span>
                                </a>
                                <a href="logout.php"
                                    class="p-2 rounded-full hover:bg-gray-700 transition relative has-tooltip"
                                    title="Logout"><i class="fas fa-sign-out-alt text-gray-400 hover:text-white"></i><span
                                        class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-700 text-white text-xs py-1 px-2 rounded whitespace-nowrap shadow-lg">Logout</span></a>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center space-x-3"><a
                                    href="Login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                    class="text-gray-300 hover:text-white px-3 py-1.5 rounded-md text-sm font-medium transition-colors">Log
                                    In</a><a href="Signup.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                    class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-1.5 rounded-md text-sm font-medium transition-colors shadow-md">Sign
                                    Up</a></div>
                        <?php endif; ?>
                    </div>
                    <div class="md:hidden"><button
                            class="mobile-menu-button p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none"
                            aria-label="Toggle menu" aria-expanded="false"><i class="fas fa-bars"></i></button></div>
                </div>
            </div>
            <div
                class="mobile-menu hidden md:hidden bg-gray-800 absolute top-full left-0 right-0 border-t border-gray-700 shadow-lg">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3"><a href="courses_list.php"
                        class="block text-gray-300 hover:text-white hover:bg-gray-700 px-3 py-2 rounded-md text-base font-medium">Courses</a><a
                        href="resources.html"
                        class="block text-gray-300 hover:text-white hover:bg-gray-700 px-3 py-2 rounded-md text-base font-medium">Resources</a>
                    <div class="pt-4 border-t border-gray-700">
                        <?php if ($user_logged_in): ?>
                            <a href="Dashboard.php"
                                class="flex items-center w-full px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-700"><img
                                    src="<?php echo $_SESSION['profile_image_url'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['username'] ?? 'U') . '&background=4b5563&color=f9fafb'; ?>"
                                    alt="Profile"
                                    class="w-8 h-8 rounded-full object-cover bg-gray-600 border border-gray-500 mr-3">My
                                Dashboard</a>
                            <a href="logout.php"
                                class="block w-full mt-2 px-3 py-2 text-left rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-700">Log
                                out</a>
                        <?php else: ?>
                            <a href="Login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                class="block w-full px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-700">Log
                                in</a>
                            <a href="Signup.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                class="block w-full mt-2 px-3 py-2 bg-primary-600 rounded-md hover:bg-primary-700 transition text-center text-white">Sign
                                up</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Course Header -->
        <div class="bg-gray-800 rounded-xl p-8 mb-8 border border-gray-700 shadow-lg">
            <div class="flex flex-col md:flex-row justify-between gap-8">
                <div class="md:w-2/3">
                    <span
                        class="inline-block <?php echo $is_paid_course ? 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/30' : 'bg-primary-600/20 text-primary-400 ring-primary-600/30'; ?> px-3 py-1 rounded-full text-xs font-semibold mb-4 ring-1 ring-inset">
                        <?php echo $is_paid_course ? 'PROFESSIONAL CERTIFICATE' : 'FREE COURSE'; ?>
                    </span>
                    <h1 class="text-3xl md:text-4xl font-bold mb-4 text-white">
                        <?php safe_echo($course_title_display); ?>
                    </h1>
                    <p class="text-lg text-gray-300 mb-6">Understand the fundamentals of cloud computing and gain
                        hands-on experience with core Amazon Web Services (AWS) like EC2, S3, VPC, and IAM.</p>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mb-6 text-sm text-gray-400">
                        <div class="flex items-center">
                            <div class="flex items-center text-yellow-400 mr-1"><i class="fas fa-star"></i><i
                                    class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                    class="far fa-star"></i></div><span class="text-gray-300">4.4</span> (1,920 ratings)
                        </div>
                        <span class="hidden sm:inline text-gray-600">•</span>
                        <span><i class="fas fa-users mr-1 opacity-70"></i>25,000+ students</span>
                        <span class="hidden sm:inline text-gray-600">•</span>
                        <span><i class="fas fa-calendar-alt mr-1 opacity-70"></i>Updated Jan 2024</span>
                    </div>
                    <!-- Enrollment Action Area -->
                    <div class="flex items-center space-x-4 mt-8" id="enrollment-action-area">
                        <?php
                        $tag = $button_config['tag'];
                        $text = $button_config['text'];
                        $classes = $button_config['classes'];
                        $href = ($tag === 'a') ? ' href="' . htmlspecialchars($button_config['href']) . '"' : '';
                        $id_attr = $button_config['id'] ? ' id="' . htmlspecialchars($button_config['id']) . '"' : '';
                        $disabled_attr = $button_config['disabled'] ? ' disabled aria-disabled="true"' : '';
                        $data_attrs_str = '';
                        if (!empty($button_config['data_attributes'])) {
                            foreach ($button_config['data_attributes'] as $key => $value) {
                                $data_attrs_str .= ' data-' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
                            }
                        }
                        echo "<$tag{$id_attr} class=\"$classes\"{$href}{$disabled_attr}{$data_attrs_str}>";
                        echo '<span class="btn-text">' . $text . '</span>';
                        if ($tag === 'button' && !$button_config['disabled'] && !$is_enrolled) {
                            echo '<span class="spinner hidden ml-2"></span>';
                        }
                        echo "</$tag>";
                        if ($is_enrolled) {
                            echo '<button class="flex items-center text-gray-400 transition group has-tooltip opacity-75 cursor-default" disabled><i class="fas fa-check-circle mr-2 text-green-500"></i> Enrolled<span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-700 text-white text-xs py-1 px-2 rounded whitespace-nowrap shadow-lg">You are enrolled</span></button>';
                        } elseif (!$enrollment_check_error && !$db_connection_error && $tag !== 'a') {
                            echo '<button type="button" class="flex items-center text-gray-400 hover:text-white transition group has-tooltip opacity-50 cursor-not-allowed" aria-label="Save course for later (coming soon)" disabled><i class="far fa-heart mr-2"></i> Save<span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-700 text-white text-xs py-1 px-2 rounded whitespace-nowrap shadow-lg">Save (coming soon)</span></button>';
                        }
                        ?>
                    </div>
                    <?php if (!$is_enrolled && !$user_logged_in && !$is_paid_course): ?>
                        <p class="text-xs text-gray-400 mt-3"><a
                                href="Login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                class="text-primary-500 hover:text-primary-400 hover:underline">Log in</a> or <a
                                href="Signup.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                class="text-primary-500 hover:text-primary-400 hover:underline">sign up</a> to enroll in
                            this free course and track your progress.</p>
                    <?php endif; ?>
                    <?php if ($enrollment_check_error || $db_connection_error): ?>
                        <p class="text-xs text-red-400 mt-3"><i class="fas fa-exclamation-triangle mr-1"></i> Error checking
                            enrollment status. Please try refreshing.</p>
                    <?php endif; ?>
                </div>
                <div class="hidden md:block md:w-1/3 flex-shrink-0">
                    <div class="relative overflow-hidden rounded-lg shadow-lg border border-gray-700 aspect-video">
                        <img src="https://images.unsplash.com/photo-1560415755-bd80d06eda60?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80"
                            alt="<?php safe_echo($course_title_display); ?> Preview" class="w-full h-full object-cover">
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent flex items-end p-4">
                            <button
                                class="flex items-center justify-center w-12 h-12 bg-white/10 backdrop-blur-sm rounded-full hover:bg-white/20 transition border border-white/20 text-white hover:text-primary-300"
                                aria-label="Play course preview"><i class="fas fa-play text-xl pl-1"></i></button><span
                                class="ml-3 text-white font-medium">Course Preview</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Section -->
        <?php if ($user_logged_in): ?>
            <section class="bg-gray-800 rounded-xl p-6 mb-10 border border-gray-700 shadow-lg"
                aria-labelledby="progress-heading">
                <div class="flex flex-wrap justify-between items-center gap-4 mb-4">
                    <h2 id="progress-heading" class="text-xl font-bold flex items-center"><i
                            class="fas fa-chart-line text-primary-500 mr-3" aria-hidden="true"></i>Your Learning Progress
                    </h2>
                    <span id="progress-text" class="text-sm text-gray-400 font-medium"
                        aria-live="polite">Calculating...</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-3 mb-4 overflow-hidden shadow-inner progress-container"
                    role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                    aria-labelledby="progress-text">
                    <div id="progress-bar" class="bg-primary-600 h-full rounded-full progress-bar" style="width: 0%"></div>
                </div>
                <div class="mt-4 flex flex-wrap justify-center items-center gap-4">
                    <a href="resource/CloudComputing-Certificate.png" <?php // <-- *** CHANGE CERTIFICATE PATH *** ?>
                        id="certificate-download-button" download="CloudComputing-Certificate.png"
                        class="hidden inline-flex items-center justify-center px-5 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105"
                        aria-label="Download your course completion certificate"><i
                            class="fas fa-certificate mr-2"></i>Download Certificate</a>
                    <button type="button" id="reset-progress-button"
                        class="inline-flex items-center justify-center px-4 py-1.5 border border-red-500 text-red-400 hover:bg-red-500/20 hover:text-red-300 text-xs font-medium rounded-lg transition duration-300 ease-in-out"
                        aria-label="Reset course progress"><i class="fas fa-undo-alt mr-1.5 text-xs"></i>Reset
                        Progress</button>
                </div>
            </section>
        <?php endif; ?>

        <!-- Course Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <div class="lg:col-span-2">
                <!-- What You'll Learn -->
                <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-8 shadow-lg">
                    <h2 class="text-2xl font-bold mb-4 text-white">What You'll Learn</h2>
                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-gray-300">
                        <?php
                        $learning_points_aws = [
                            "Understand core cloud computing concepts (IaaS, PaaS, SaaS)",
                            "Navigate the AWS Management Console",
                            "Launch and manage EC2 virtual servers",
                            "Store and retrieve data using S3 buckets",
                            "Understand basic AWS networking with VPC",
                            "Manage users and permissions with IAM"
                        ];
                        foreach ($learning_points_aws as $point): ?>
                            <li class="flex items-start"><i
                                    class="fas fa-check-circle text-primary-500 mt-1 mr-2 flex-shrink-0"></i><span><?php echo $point; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Requirements -->
                <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-8 shadow-lg">
                    <h2 class="text-2xl font-bold mb-4 text-white">Requirements</h2>
                    <ul class="space-y-2 text-gray-300">
                        <li class="flex items-start"><i
                                class="fas fa-desktop text-primary-500 mt-1 mr-2 flex-shrink-0"></i><span>Basic computer
                                literacy and internet navigation skills</span></li>
                        <li class="flex items-start"><i
                                class="fas fa-cloud text-primary-500 mt-1 mr-2 flex-shrink-0"></i><span>No prior cloud
                                or AWS experience is required</span></li>
                        <li class="flex items-start"><i
                                class="fas fa-credit-card text-primary-500 mt-1 mr-2 flex-shrink-0"></i><span>An AWS
                                account (free tier eligible, credit card needed for setup)</span></li>
                    </ul>
                </div>

                <!-- Curriculum Section -->
                <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-8 shadow-lg" id="curriculum-section">
                    <h2 class="text-2xl font-bold mb-6 text-white">Course Curriculum</h2>
                    <p class="text-sm text-gray-400 mb-6 -mt-4">Check the box or click the play icon to mark a lesson as
                        complete.</p>

                    <!-- Module 1: Intro to Cloud & AWS -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4 border-b border-gray-700 pb-2">
                            <h3 class="text-xl font-semibold text-primary-400">Module 1: Introduction to Cloud & AWS
                            </h3>
                            <span class="text-sm text-gray-400">2 lessons</span>
                        </div>
                        <div class="space-y-3">
                            <div class="lesson-item"><input type="checkbox" id="lesson-1-aws"
                                    class="lesson-checkbox hidden" data-lesson-id="lesson-1-aws"><label
                                    for="lesson-1-aws"><span class="checkbox-visual"><i
                                            class="fas fa-check"></i></span><span class="lesson-title">What is Cloud
                                        Computing? (IaaS, PaaS, SaaS)</span></label>
                                <div class="lesson-actions"><span class="text-sm text-gray-400">20 min</span><a
                                        href="https://www.youtube.com/watch?v=lsvpvCU6Oxs" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-lg has-tooltip relative"
                                        data-lesson-id="lesson-1-aws" title="Start Lesson"><i
                                            class="fas fa-play-circle"></i><span class="tooltip">Start Lesson</span></a>
                                </div>
                            </div>
                            <div class="lesson-item"><input type="checkbox" id="lesson-2-aws"
                                    class="lesson-checkbox hidden" data-lesson-id="lesson-2-aws"><label
                                    for="lesson-2-aws"><span class="checkbox-visual"><i
                                            class="fas fa-check"></i></span><span class="lesson-title">AWS Global
                                        Infrastructure & Console Tour</span></label>
                                <div class="lesson-actions"><span class="text-sm text-gray-400">30 min</span><a
                                        href="https://www.youtube.com/watch?v=JIbIYCM48to" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-lg has-tooltip relative"
                                        data-lesson-id="lesson-2-aws" title="Start Lesson"><i
                                            class="fas fa-play-circle"></i><span class="tooltip">Start Lesson</span></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Module 2: Core AWS Services -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4 border-b border-gray-700 pb-2">
                            <h3 class="text-xl font-semibold text-purple-400">Module 2: Core AWS Compute & Storage</h3>
                            <span class="text-sm text-gray-400">2 lessons</span>
                        </div>
                        <div class="space-y-3">
                            <div class="lesson-item"><input type="checkbox" id="lesson-3-aws"
                                    class="lesson-checkbox hidden" data-lesson-id="lesson-3-aws"><label
                                    for="lesson-3-aws"><span class="checkbox-visual"><i
                                            class="fas fa-check"></i></span><span class="lesson-title">Launching and
                                        Managing EC2 Instances</span></label>
                                <div class="lesson-actions"><span class="text-sm text-gray-400">45 min</span><a
                                        href="https://www.youtube.com/watch?v=6zcky-Bl1aE" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-lg has-tooltip relative"
                                        data-lesson-id="lesson-3-aws" title="Start Lesson"><i
                                            class="fas fa-play-circle"></i><span class="tooltip">Start Lesson</span></a>
                                </div>
                            </div>
                            <div class="lesson-item"><input type="checkbox" id="lesson-4-aws"
                                    class="lesson-checkbox hidden" data-lesson-id="lesson-4-aws"><label
                                    for="lesson-4-aws"><span class="checkbox-visual"><i
                                            class="fas fa-check"></i></span><span class="lesson-title">Introduction to
                                        S3 Bucket Storage</span></label>
                                <div class="lesson-actions"><span class="text-sm text-gray-400">35 min</span><a
                                        href="https://www.youtube.com/watch?v=d8A8JmAImc4" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-lg has-tooltip relative"
                                        data-lesson-id="lesson-4-aws" title="Start Lesson"><i
                                            class="fas fa-play-circle"></i><span class="tooltip">Start Lesson</span></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Module 3: AWS Networking & Security -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4 border-b border-gray-700 pb-2">
                            <h3 class="text-xl font-semibold text-green-400">Module 3: AWS Networking & Security Basics
                            </h3>
                            <span class="text-sm text-gray-400">2 lessons</span>
                        </div>
                        <div class="space-y-3">
                            <div class="lesson-item"><input type="checkbox" id="lesson-5-aws"
                                    class="lesson-checkbox hidden" data-lesson-id="lesson-5-aws"><label
                                    for="lesson-5-aws"><span class="checkbox-visual"><i
                                            class="fas fa-check"></i></span><span class="lesson-title">Understanding
                                        Virtual Private Cloud (VPC)</span></label>
                                <div class="lesson-actions"><span class="text-sm text-gray-400">40 min</span><a
                                        href="https://www.youtube.com/watch?v=ZMJV5AIfVBE" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-lg has-tooltip relative"
                                        data-lesson-id="lesson-5-aws" title="Start Lesson"><i
                                            class="fas fa-play-circle"></i><span class="tooltip">Start Lesson</span></a>
                                </div>
                            </div>
                            <div class="lesson-item"><input type="checkbox" id="lesson-6-aws"
                                    class="lesson-checkbox hidden" data-lesson-id="lesson-6-aws"><label
                                    for="lesson-6-aws"><span class="checkbox-visual"><i
                                            class="fas fa-check"></i></span><span class="lesson-title">Identity and
                                        Access Management (IAM) Basics</span></label>
                                <div class="lesson-actions"><span class="text-sm text-gray-400">30 min</span><a
                                        href="https://www.youtube.com/watch?v=gsgdAyGhV0o" target="_blank"
                                        rel="noopener noreferrer"
                                        class="play-lesson-link text-primary-500 hover:text-primary-400 text-lg has-tooltip relative"
                                        data-lesson-id="lesson-6-aws" title="Start Lesson"><i
                                            class="fas fa-play-circle"></i><span class="tooltip">Start Lesson</span></a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Instructor Section -->
                <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 shadow-lg">
                    <h2 class="text-2xl font-bold mb-6 text-white">About the Instructor</h2>
                    <div class="flex flex-col md:flex-row items-start gap-6">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Priya Sharma"
                            class="w-24 h-24 rounded-full object-cover flex-shrink-0 border-4 border-primary-700/50 shadow-md">
                        <div>
                            <h3 class="text-xl font-bold mb-1 text-white">Priya Sharma</h3>
                            <p class="text-primary-400 font-medium mb-3">AWS Certified Solutions Architect | Cloud
                                Consultant</p>
                            <p class="text-gray-300 mb-4 text-sm leading-relaxed">Priya is an AWS certified expert with
                                8 years of experience designing and implementing cloud solutions for businesses of all
                                sizes. She is passionate about making cloud technology accessible and enjoys breaking
                                down complex topics.</p>
                            <div
                                class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-400 border-t border-gray-700 pt-3 mt-3">
                                <div class="flex items-center" title="Instructor Rating"><i
                                        class="fas fa-star text-yellow-400 mr-1.5"></i><span>4.8 Rating</span></div>
                                <div class="flex items-center" title="Total Students"><i
                                        class="fas fa-user-graduate mr-1.5"></i><span>9,500 Students</span></div>
                                <div class="flex items-center" title="Courses Offered"><i
                                        class="fas fa-play-circle mr-1.5"></i><span>3 Courses</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="sticky top-24 space-y-6">
                    <!-- Course Features -->
                    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 shadow-lg">
                        <h3 class="text-lg font-bold mb-4 text-white">This Course Includes:</h3>
                        <ul class="space-y-3 text-sm text-gray-300">
                            <li class="flex items-center"><i
                                    class="fas fa-video text-primary-500 mr-3 w-5 text-center fa-fw"></i><span>15 hours
                                    on-demand video</span></li>
                            <li class="flex items-center"><i
                                    class="fas fa-file-alt text-primary-500 mr-3 w-5 text-center fa-fw"></i><span>10
                                    downloadable resources</span></li>
                            <li class="flex items-center"><i
                                    class="fas fa-laptop-code text-primary-500 mr-3 w-5 text-center fa-fw"></i><span>Hands-on
                                    labs & demos</span></li>
                            <li class="flex items-center"><i
                                    class="fas fa-certificate text-primary-500 mr-3 w-5 text-center fa-fw"></i><span>Certificate
                                    of completion</span></li>
                            <li class="flex items-center"><i
                                    class="fas fa-infinity text-primary-500 mr-3 w-5 text-center fa-fw"></i><span>Full
                                    lifetime access</span></li>
                        </ul>
                    </div>

                    <!-- Resources -->
                    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 shadow-lg">
                        <h3 class="text-lg font-bold mb-4 text-white">Resources</h3>
                        <div class="space-y-2">
                            <a href="https://docs.aws.amazon.com/" target="_blank" rel="noopener noreferrer"
                                class="flex items-center p-3 -m-3 hover:bg-gray-700/50 rounded-lg transition group">
                                <div
                                    class="bg-yellow-600/20 text-yellow-500 group-hover:text-yellow-400 p-2 rounded-lg mr-3 flex-shrink-0 w-10 h-10 flex items-center justify-center ring-1 ring-yellow-600/30 group-hover:ring-yellow-500 transition">
                                    <i class="fas fa-book fa-lg"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-sm text-gray-200 group-hover:text-white">AWS
                                        Documentation</h4>
                                    <p class="text-xs text-gray-400">Official Docs</p>
                                </div><i
                                    class="fas fa-external-link-alt text-gray-500 ml-auto group-hover:text-yellow-400 transition text-xs"></i>
                            </a>
                            <a href="https://github.com/aws/aws-cli" target="_blank" rel="noopener noreferrer"
                                class="flex items-center p-3 -m-3 hover:bg-gray-700/50 rounded-lg transition group">
                                <div
                                    class="bg-purple-600/20 text-purple-400 p-2 rounded-lg mr-3 flex-shrink-0 w-10 h-10 flex items-center justify-center ring-1 ring-purple-600/30 group-hover:ring-purple-500 transition">
                                    <i class="fas fa-terminal fa-lg"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-sm text-gray-200 group-hover:text-white">AWS CLI Tool
                                    </h4>
                                    <p class="text-xs text-gray-400">GitHub Repo</p>
                                </div><i
                                    class="fas fa-external-link-alt text-gray-500 ml-auto group-hover:text-purple-400 transition text-xs"></i>
                            </a>
                            <a href="https://www.reddit.com/r/aws/" target="_blank" rel="noopener noreferrer"
                                class="flex items-center p-3 -m-3 hover:bg-gray-700/50 rounded-lg transition group">
                                <div
                                    class="bg-red-600/20 text-red-400 p-2 rounded-lg mr-3 flex-shrink-0 w-10 h-10 flex items-center justify-center ring-1 ring-red-600/30 group-hover:ring-red-500 transition">
                                    <i class="fab fa-reddit-alien fa-lg"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-sm text-gray-200 group-hover:text-white">AWS Community
                                    </h4>
                                    <p class="text-xs text-gray-400">Reddit Forum</p>
                                </div><i
                                    class="fas fa-external-link-alt text-gray-500 ml-auto group-hover:text-red-400 transition text-xs"></i>
                            </a>
                        </div>
                    </div>
                    <!-- Related Courses -->
                    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 shadow-lg">
                        <h3 class="text-lg font-bold mb-4 text-white">Students Also Viewed</h3>
                        <div class="space-y-4">
                            <a href="ux-ui-fundamentals.php"
                                class="flex items-center hover:bg-gray-700/50 p-3 -m-3 rounded-lg transition group"><img
                                    src="https://images.unsplash.com/photo-1607799279861-4dd421887fb3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8ZGV2b3BzfGVufDB8fDB8fHww&auto=format&fit=crop&w=200&q=60"
                                    alt="UX/UI Design Fundamentals"
                                    class="w-16 h-12 rounded-md object-cover mr-3 flex-shrink-0 border border-gray-700 group-hover:border-primary-600 transition">
                                <div>
                                    <h4
                                        class="font-medium text-sm leading-tight text-gray-200 group-hover:text-white transition">
                                        Introduction to DevOps</h4>
                                    <div class="flex items-center text-xs text-gray-400 mt-1"><i
                                            class="fas fa-star text-yellow-400 mr-1 text-xs"></i><span>4.6 • 5.1k
                                            students</span></div>
                                </div>
                            </a>
                            <a href="js_course.php"
                                class="flex items-center hover:bg-gray-700/50 p-3 -m-3 rounded-lg transition group"><img
                                    src="https://images.unsplash.com/photo-1579468118864-1b9ea3c0db4a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=60"
                                    alt="JavaScript Course"
                                    class="w-16 h-12 rounded-md object-cover mr-3 flex-shrink-0 border border-gray-700 group-hover:border-primary-600 transition">
                                <div>
                                    <h4
                                        class="font-medium text-sm leading-tight text-gray-200 group-hover:text-white transition">
                                        Modern JavaScript Mastery</h4>
                                    <div class="flex items-center text-xs text-gray-400 mt-1"><i
                                            class="fas fa-star text-yellow-400 mr-1 text-xs"></i><span>4.8 • 42k
                                            students</span></div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 border-t border-gray-700 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4"><i
                            class="fas fa-graduation-cap text-primary-500 text-2xl mr-2"></i><span
                            class="text-xl font-bold text-white">EduPro</span></div>
                    <p class="text-gray-400 mb-4 text-sm">Advancing careers through world-class digital education.</p>
                    <div class="flex space-x-4"><a href="#" class="text-gray-400 hover:text-white transition"
                            aria-label="Facebook"><i class="fab fa-facebook-f"></i></a><a href="#"
                            class="text-gray-400 hover:text-white transition" aria-label="Twitter"><i
                                class="fab fa-twitter"></i></a><a href="#"
                            class="text-gray-400 hover:text-white transition" aria-label="LinkedIn"><i
                                class="fab fa-linkedin-in"></i></a><a href="#"
                            class="text-gray-400 hover:text-white transition" aria-label="YouTube"><i
                                class="fab fa-youtube"></i></a></div>
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
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Feedback</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Accessibility</a></li>
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
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">GDPR</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 pt-8 border-t border-gray-700 flex flex-col sm:flex-row justify-between items-center">
                <p class="text-gray-500 text-sm mb-4 sm:mb-0">© <?php echo date('Y'); ?> EduPro, Inc. All rights
                    reserved.</p>
                <div class="flex space-x-6"><a href="#"
                        class="text-gray-500 hover:text-gray-400 text-sm transition">Sitemap</a><a href="#"
                        class="text-gray-500 hover:text-gray-400 text-sm transition">Trademark</a><a href="#"
                        class="text-gray-500 hover:text-gray-400 text-sm transition">Policies</a></div>
            </div>
        </div>
    </footer>


    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- Mobile menu toggle ---
            const mobileMenuButton = document.querySelector('.mobile-menu-button');
            const mobileMenu = document.querySelector('.mobile-menu');

            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function () {
                    mobileMenu.classList.toggle('hidden');
                    const icon = mobileMenuButton.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-bars');
                        icon.classList.toggle('fa-times');
                    }
                    const isExpanded = !mobileMenu.classList.contains('hidden');
                    this.setAttribute('aria-expanded', isExpanded);
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
                            if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                                mobileMenu.classList.add('hidden');
                                const menuIcon = mobileMenuButton?.querySelector('i');
                                if (menuIcon) { menuIcon.classList.remove('fa-times'); menuIcon.classList.add('fa-bars'); }
                                mobileMenuButton?.setAttribute('aria-expanded', 'false');
                            }
                        }
                    }
                });
            });

            // --- Curriculum Accordion ---
            const moduleHeaders = document.querySelectorAll('.module-header');
            moduleHeaders.forEach(header => {
                header.addEventListener('click', function () {
                    const content = this.nextElementSibling; // Find next element
                    const icon = this.querySelector('.accordion-icon');
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';

                    if (content && content.classList.contains('module-content')) {
                        content.classList.toggle('hidden');
                        this.setAttribute('aria-expanded', !isExpanded);
                    }
                    if (icon) icon.classList.toggle('rotate-180');
                });
                // Set initial state based on HTML attribute
                const content = header.nextElementSibling; const icon = header.querySelector('.accordion-icon');
                if (content && content.classList.contains('module-content')) {
                    if (header.getAttribute('aria-expanded') === 'true') { content.classList.remove('hidden'); if (icon) icon.classList.remove('rotate-180'); }
                    else { content.classList.add('hidden'); if (icon) icon.classList.add('rotate-180'); }
                }
            });


            // --- Progress Bar, Lesson Completion & Reset Logic ---
            const userIsLoggedInForJS = <?php echo json_encode($user_logged_in); ?>;

            if (userIsLoggedInForJS) {
                const checkboxes = document.querySelectorAll('.lesson-checkbox');
                // *** Lesson count for Cloud course ***
                const totalLessonsForProgress = checkboxes.length > 0 ? checkboxes.length : 6; // 2+2+2 lessons
                const courseSlugForStorage = <?php echo json_encode($course_slug); ?>;
                const progressBar = document.getElementById('progress-bar');
                const progressText = document.getElementById('progress-text');
                const completionBadge = document.getElementById('completion-badge');
                const certificateButton = document.getElementById('certificate-download-button');
                const resetProgressButton = document.getElementById('reset-progress-button'); // *** Get reset button ***

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
                    else if (progress > 30) { barColorClass = 'bg-orange-500'; } // Added orange
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
                        const lessonId = checkbox.id; // e.g., lesson-1-aws
                        if (lessonId) {
                            const storageKey = `completed_${lessonId}_${courseSlugForStorage}`;
                            if (localStorage.getItem(storageKey) === 'true') { count++; }
                        }
                    });
                    return count;
                }

                // Update checkbox visual
                function updateCheckboxVisual(checkbox, isChecked) {
                    const label = checkbox.closest('.lesson-item')?.querySelector(`label[for="${checkbox.id}"]`);
                    const checkIconVisual = label?.querySelector('.checkbox-visual .fa-check');
                    if (!label || !checkIconVisual) return;
                    if (isChecked) {
                        label.classList.add('text-gray-500', 'line-through');
                        checkIconVisual.style.opacity = '1';
                        label.querySelector('.checkbox-visual').classList.add('bg-primary-600', 'border-primary-600');
                    } else {
                        label.classList.remove('text-gray-500', 'line-through');
                        checkIconVisual.style.opacity = '0';
                        label.querySelector('.checkbox-visual').classList.remove('bg-primary-600', 'border-primary-600');
                    }
                }

                // *** Function to Reset Course Progress ***
                function resetCourseProgress() {
                    if (!confirm('Are you sure you want to reset your progress for this course? This action cannot be undone.')) { return; }
                    console.log(`Resetting progress for course: ${courseSlugForStorage}`);
                    checkboxes.forEach(checkbox => {
                        const lessonId = checkbox.id;
                        if (lessonId) {
                            const storageKey = `completed_${lessonId}_${courseSlugForStorage}`;
                            localStorage.removeItem(storageKey);
                            checkbox.checked = false;
                            updateCheckboxVisual(checkbox, false);
                        }
                    });
                    updateOverallProgressUI(0); // Reset UI
                    alert('Course progress has been reset.');
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
                        const lessonId = this.dataset.lessonId; // e.g., lesson-1-aws
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
            // isLoggedIn defined earlier
            const isPaidCourse = <?php echo json_encode($is_paid_course); ?>;
            const initialButtonConfigText = <?php echo json_encode($button_config['text']); ?>;
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = initialButtonConfigText; // Handle potential HTML in button text
            const initialButtonTextContent = tempDiv.textContent || tempDiv.innerText || '';

            if (enrollButton && enrollButton.tagName === 'BUTTON' && enrollmentActionArea) {
                const enrollButtonTextSpan = enrollButton.querySelector('.btn-text');
                const enrollButtonSpinner = enrollButton.querySelector('.spinner');

                enrollButton.addEventListener('click', function () {
                    if (!userIsLoggedInForJS) {
                        alert('Please log in or sign up to enroll.');
                        window.location.href = 'Login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
                        return;
                    }
                    if (isPaidCourse) {
                        alert('Paid course enrollment processing not implemented.'); return;
                    }

                    const courseSlug = this.dataset.courseSlug;
                    const csrfToken = this.dataset.csrfToken;
                    const button = this;

                    if (!csrfToken) { alert('Security token missing. Please refresh.'); return; }

                    button.disabled = true; button.setAttribute('aria-disabled', 'true'); button.classList.add('opacity-60', 'cursor-wait');
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
                                <span class="flex items-center text-gray-400 text-sm font-medium ml-2"><i class="fas fa-check-circle mr-1.5 text-success-500" aria-hidden="true"></i> Enrolled</span>`;
                                document.querySelectorAll('a[href^="#"]').forEach(anchor => { /* Re-attach smooth scroll if needed */ });
                            } else {
                                if (data.action === 'redirect_login') { alert('Session issue. Please log in again.'); window.location.href = 'Login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search); }
                                else if (data.message && data.message.toLowerCase().includes('already enrolled')) {
                                    console.warn("Already enrolled:", data.message);
                                    enrollmentActionArea.innerHTML = `
                                    <a href="#curriculum-section" id="continue-learning-link" class="btn-success hover:from-green-600 hover:to-green-700 text-white px-7 py-3 rounded-lg font-semibold transition duration-300 flex items-center justify-center text-base shadow-lg transform hover:scale-105"> <i class="fas fa-play mr-2"></i>Continue Learning </a>
                                    <span class="flex items-center text-gray-400 text-sm font-medium ml-2"><i class="fas fa-check-circle mr-1.5 text-success-500" aria-hidden="true"></i> Enrolled</span>`;
                                } else { alert('Enrollment failed: ' + (data.message || 'Unknown server error.')); resetEnrollButton(); }
                            }
                        })
                        .catch(error => {
                            console.error('Enrollment fetch error:', error);
                            alert('Could not enroll. Please check connection and try again.\nError: ' + error.message);
                            resetEnrollButton();
                        });

                    function resetEnrollButton() {
                        button.disabled = false; button.removeAttribute('aria-disabled'); button.classList.remove('opacity-60', 'cursor-wait');
                        if (enrollButtonTextSpan) { enrollButtonTextSpan.innerHTML = initialButtonConfigText; } // Restore HTML if needed
                        else { button.innerHTML = initialButtonTextContent; }
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