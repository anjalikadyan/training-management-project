<!DOCTYPE html>
<!-- Add class="dark" here initially if you want dark mode by default -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Resources | EduPro Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- PRINT STYLES --- */
        @media print {
            body * { visibility: hidden; }
            #notesModal, #notesModal *,
            #certificateModal, #certificateModal * { visibility: visible; }
            #notesModal, #certificateModal { position: absolute; left: 0; top: 0; width: 100%; height: auto; margin: 0; padding: 0; border: none; box-shadow: none; overflow: visible !important; }
            #notesModal .flex-col, #certificateModal .flex-col { height: auto; }
            #notesModal .border-b, #notesModal .border-t, #notesModal .px-6.py-4, #notesModal .px-6.py-3,
            #certificateModal .border-b, #certificateModal .border-t, #certificateModal .px-6.py-4, #certificateModal .px-6.py-3 { display: none; }
            #notesModal .flex-grow, #certificateModal .flex-grow { overflow: visible !important; height: auto; padding: 1rem; }
            #notesContent, #certificateContent img { color: #000 !important; max-width: 100% !important; } /* Ensure print content is black and fits */
            #notesModal button, #certificateModal button { display: none; }
            nav, #uploadBtn, #uploadModal, .flex.justify-between.items-center.mb-8, .mb-6, #resourceGrid { display: none; }
            .resource-card button, .resource-card a[download], .resource-card a[target="_blank"] { display: none; }
        }
        /* --- PROSE STYLES --- */
        .prose { line-height: 1.6; }
        .prose h1 { font-size: 1.8em; font-weight: bold; margin-bottom: 1em; }
        .prose h2 { font-size: 1.4em; font-weight: bold; margin-top: 1.5em; margin-bottom: 0.8em; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.3em; }
        .prose p, .prose ul, .prose ol { margin-bottom: 1em; }
        .prose ul { list-style: disc; margin-left: 1.5em; }
        .prose ol { list-style: decimal; margin-left: 1.5em; }
        .prose li { margin-bottom: 0.5em; }
        .prose strong { font-weight: bold; }
        .prose code { background-color: #f3f4f6; padding: 0.2em 0.4em; border-radius: 3px; font-family: monospace; }
        .prose a { color: #2563eb; text-decoration: underline; }
        .prose a:hover { color: #1d4ed8; }
        /* --- DARK MODE STYLES --- */
        html.dark body { background-color: #111827; color: #d1d5db; }
        html.dark nav { background-color: #1f2937; border-color: #374151; }
        html.dark nav a { color: #d1d5db; }
        html.dark nav a:hover { color: #ffffff; }
        html.dark nav a.text-blue-200 { color: #93c5fd; }
        html.dark .bg-white { background-color: #1f2937; }
        html.dark .text-gray-800 { color: #f9fafb; }
        html.dark .text-gray-600 { color: #9ca3af; }
        html.dark .text-gray-500 { color: #6b7280; }
        html.dark .border, html.dark .border-t, html.dark .border-b { border-color: #374151; }
        html.dark .shadow-md { box-shadow: 0 4px 6px -1px rgba(255, 255, 255, 0.05), 0 2px 4px -2px rgba(255, 255, 255, 0.05); }
        html.dark .hover\:shadow-lg:hover { box-shadow: 0 10px 15px -3px rgba(255, 255, 255, 0.05), 0 4px 6px -4px rgba(255, 255, 255, 0.05); }
        html.dark .bg-gray-50 { background-color: #111827; }
        html.dark .bg-red-50 { background-color: rgba(153, 27, 27, 0.2); }
        html.dark .bg-green-50 { background-color: rgba(4, 120, 87, 0.2); }
        html.dark .bg-blue-50 { background-color: rgba(37, 99, 235, 0.2); }
        html.dark .bg-yellow-50 { background-color: rgba(161, 98, 7, 0.2); }
        html.dark .bg-purple-50 { background-color: rgba(107, 33, 168, 0.2); }
        html.dark .bg-pink-50 { background-color: rgba(199, 24, 101, 0.2); } /* Added Pink BG for Graphic Design */
        html.dark .bg-red-100 { background-color: #450a0a; }
        html.dark .bg-green-100 { background-color: #064e3b; }
        html.dark .bg-blue-100 { background-color: #1e40af; }
        html.dark .bg-purple-100 { background-color: #581c87; }
        html.dark .bg-yellow-100 { background-color: #78350f; }
        html.dark .bg-pink-100 { background-color: #831843; }
        html.dark .bg-gray-100 { background-color: #374151; }
        html.dark .text-red-600 { color: #fca5a5; }
        html.dark .text-green-600 { color: #6ee7b7; }
        html.dark .text-blue-600 { color: #93c5fd; }
        html.dark .text-purple-600 { color: #c084fc; }
        html.dark .text-yellow-600 { color: #facc15; } /* Added yellow text for DM */
        html.dark .text-pink-600 { color: #f472b6; } /* Added pink text for GD */
        html.dark .text-purple-800 { color: #d8b4fe; }
        html.dark .text-yellow-800 { color: #fde047; }
        html.dark .text-pink-800 { color: #f9a8d4; }
        html.dark .text-gray-800 { color: #e5e7eb; }
        html.dark .tab-btn:not(.active) { color: #9ca3af; }
        html.dark .tab-btn:hover:not(.active) { color: #60a5fa; }
        html.dark .tab-btn.active { color: #60a5fa; border-color: #60a5fa; }
        html.dark input, html.dark select, html.dark textarea { background-color: #374151; border-color: #4b5563; color: #d1d5db; }
        html.dark input:focus, html.dark select:focus, html.dark textarea:focus { ring-color: #60a5fa; border-color: #60a5fa;}
        html.dark ::placeholder { color: #6b7280; }
        html.dark #uploadModal .bg-white, html.dark #notesModal .bg-white, html.dark #certificateModal .bg-white { background-color: #1f2937; }
        html.dark #uploadModal .text-gray-700, html.dark #notesModal .text-gray-700, html.dark #certificateModal .text-gray-700 { color: #d1d5db; }
        html.dark #uploadModal .border-gray-300, html.dark #notesModal .border-gray-300, html.dark #certificateModal .border-gray-300 { border-color: #4b5563; }
        html.dark button.border { background-color: #374151; border-color: #4b5563; color: #d1d5db; }
        html.dark button.border:hover { background-color: #4b5563; }
        html.dark .prose h2 { border-color: #374151; }
        html.dark .prose code { background-color: #374151; }
        html.dark .prose a { color: #93c5fd; }
        html.dark .prose a:hover { color: #60a5fa; }
        html.dark .dark\:prose-invert { color: #d1d5db; }
        html.dark .dark\:prose-invert h1, html.dark .dark\:prose-invert h2 { color: #f9fafb; }
        html.dark .dark\:prose-invert strong { color: #f3f4f6; }
        html.dark .dark\:prose-invert a { color: #93c5fd; }
        html.dark .dark\:prose-invert code { color: #f3f4f6; }
        html.dark .dark\:prose-invert ul > li::marker { color: #6b7280; }
         /* Certificate Modal Specific */
        #certificateContent img, #certificateContent .placeholder {
            max-height: 75vh; /* Limit image height slightly more */
            max-width: 100%; /* Ensure image width doesn't overflow */
            object-fit: contain;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-blue-800 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fas fa-graduation-cap text-2xl"></i>
                <span class="text-xl font-bold">EduPro Center</span>
            </div>
            <div class="hidden md:flex space-x-6 items-center">
                <a href="Home.php" class="hover:text-blue-200">Dashboard</a>
                <a href="courses_list.php" class="hover:text-blue-200">Courses</a>
                <a href="resources.php" class="text-blue-200 font-semibold">Resources</a>
            </div>
            <div class="flex items-center space-x-4">
                <button id="theme-toggle" type="button" class="text-gray-200 hover:text-white focus:outline-none focus:ring-4 focus:ring-gray-700 rounded-lg text-sm p-2.5">
                    <i id="theme-toggle-dark-icon" class="hidden fas fa-moon text-xl"></i>
                    <i id="theme-toggle-light-icon" class="hidden fas fa-sun text-xl"></i>
                </button>
                <div class="relative">
                    <i class="fas fa-bell text-xl cursor-pointer"></i>
                    <span class="absolute -top-1 -right-1 bg-red-500 text-xs rounded-full h-4 w-4 flex items-center justify-center">3</span>
                </div>
                <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center cursor-pointer">
                    <a href="Dashboard.php"> <i class="fas fa-user"></i></a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-500 ">Training Resources</h1>
            <button id="uploadBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-200">
                <i class="fas fa-upload"></i>
                <span>Upload Resource</span>
            </button>
        </div>

        <!-- Upload Modal (No changes here needed from previous version) -->
        <div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md">
                <div class="flex justify-between items-center border-b border-gray-300 dark:border-gray-700 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Upload New Resource</h3>
                    <button id="closeModal" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-6 max-h-[80vh] overflow-y-auto">
                    <form id="uploadForm" class="space-y-4">
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-1 text-sm font-medium">Resource Title</label>
                            <input type="text" name="resourceTitle" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-1 text-sm font-medium">Resource Type</label>
                            <select name="resourceType" id="resourceTypeSelect" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" required>
                                <option value="">Select type</option>
                                <option value="pdf">PDF Document</option>
                                <option value="notes">Study Notes (HTML/Text)</option>
                                <option value="video">Video (Link)</option>
                                <option value="certificate">Certificate (Image/PDF)</option>
                                <option value="other">Other (e.g., ZIP, DOCX)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-1 text-sm font-medium">Course</label>
                            <select name="course" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" required>
                                <option value="">Select course</option>
                                <option value="web-dev">Web Development</option>
                                <option value="data-science">Data Science</option>
                                <option value="digital-marketing">Digital Marketing</option>
                                <option value="graphic-design">Graphic Design</option>
                                <option value="general">General</option>
                            </select>
                        </div>
                        <div id="linkInputDiv" class="hidden">
                           <label class="block text-gray-700 dark:text-gray-300 mb-1 text-sm font-medium">Resource Link (URL)</label>
                            <input type="url" name="resourceLink" placeholder="Enter URL (e.g., https://...)" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Required for Video type.</p>
                        </div>
                         <div id="fileInputDiv">
                            <label class="block text-gray-700 dark:text-gray-300 mb-1 text-sm font-medium">Upload File</label>
                            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center bg-gray-50 dark:bg-gray-700">
                                <i class="fas fa-cloud-upload-alt text-4xl text-blue-500 mb-2"></i>
                                <p class="text-gray-600 dark:text-gray-400 mb-2 text-sm">Drag & drop file here</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">or</p>
                                <label class="bg-blue-600 text-white px-4 py-2 rounded-lg cursor-pointer hover:bg-blue-700 transition duration-200 text-sm">
                                    Browse Files
                                    <input type="file" name="resourceFile" class="hidden" accept=".pdf,.txt,.html,.zip,.docx,.doc,.png,.jpg,.jpeg">
                                </label>
                                <p id="fileNameDisplay" class="text-xs text-gray-600 dark:text-gray-400 mt-3"></p>
                            </div>
                             <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" id="fileInputDescription">Select a resource type to see file requirements.</p>
                        </div>
                         <div id="notesInputDiv" class="hidden">
                             <label class="block text-gray-700 dark:text-gray-300 mb-1 text-sm font-medium">Notes Content (HTML or Text)</label>
                             <textarea name="notesContent" rows="6" placeholder="Paste or type your notes here..." class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
                             <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Alternatively, upload an HTML or TXT file using the file uploader above.</p>
                        </div>
                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-300 dark:border-gray-700 mt-6">
                            <button type="button" id="cancelUpload" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 transition duration-200 text-sm">Cancel</button>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200 text-sm flex items-center space-x-2" id="submitUploadBtn">
                                <span id="uploadButtonText">Upload</span>
                                <i id="uploadSpinner" class="fas fa-spinner fa-spin hidden"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Resource Tabs -->
        <div class="mb-6">
            <div class="flex border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                <button class="tab-btn active px-4 py-2 text-blue-600 border-b-2 border-blue-600 font-medium text-sm flex-shrink-0" data-tab="all">All Resources</button>
                <button class="tab-btn px-4 py-2 text-gray-600 hover:text-blue-600 font-medium text-sm flex-shrink-0" data-tab="pdf">PDFs</button>
                <button class="tab-btn px-4 py-2 text-gray-600 hover:text-blue-600 font-medium text-sm flex-shrink-0" data-tab="notes">Notes</button>
                <button class="tab-btn px-4 py-2 text-gray-600 hover:text-blue-600 font-medium text-sm flex-shrink-0" data-tab="video">Videos</button>
                <button class="tab-btn px-4 py-2 text-gray-600 hover:text-blue-600 font-medium text-sm flex-shrink-0" data-tab="certificate">Certificates</button>
                <button class="tab-btn px-4 py-2 text-gray-600 hover:text-blue-600 font-medium text-sm flex-shrink-0" data-tab="other">Other</button>
            </div>
        </div>

        <!-- Resource Cards -->
        <div id="resourceGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Existing non-certificate cards -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-lg transition duration-200 resource-card" data-type="pdf" data-course="web-dev" data-title="Web Development Basics">
                <div class="bg-red-50 dark:bg-red-900/20 p-4 flex items-center">
                    <div class="bg-red-100 dark:bg-red-900 p-3 rounded-full">
                        <i class="fas fa-file-pdf text-red-600 dark:text-red-300 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Web Development Basics</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">PDF Document • 2.4 MB</p>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 px-2 py-1 rounded">Web Development</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Uploaded: 2 days ago</span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 line-clamp-2">Introduction to HTML, CSS and JavaScript for beginners. Covers all fundamental concepts.</p>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <i class="fas fa-download mr-1"></i>
                            <span>124 downloads</span>
                        </div>
                        <a href="resources/web-dev-basics.pdf" download="Web Development Basics.pdf"
                           class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition duration-200 inline-flex items-center space-x-1">
                            <i class="fas fa-download text-xs"></i><span>Download</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-lg transition duration-200 resource-card" data-type="notes" data-course="data-science" data-title="Data Science Notes">
                <div class="bg-green-50 dark:bg-green-900/20 p-4 flex items-center">
                    <div class="bg-green-100 dark:bg-green-900 p-3 rounded-full">
                        <i class="fas fa-file-alt text-green-600 dark:text-green-300 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Data Science Notes</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Study Notes • 1.1 MB</p>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 px-2 py-1 rounded">Data Science</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Uploaded: 1 week ago</span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 line-clamp-2">Comprehensive notes covering Python for data analysis, pandas, numpy and visualization.</p>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <i class="fas fa-eye mr-1"></i>
                            <span>89 views</span>
                        </div>
                        <button class="view-notes-btn bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm transition duration-200 inline-flex items-center space-x-1"
                                data-content-id="data-science-notes" data-title="Data Science Notes">
                            <i class="fas fa-eye text-xs"></i><span>View Notes</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-lg transition duration-200 resource-card" data-type="video" data-course="digital-marketing" data-title="Marketing Strategies Video">
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 flex items-center">
                    <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full">
                        <i class="fas fa-video text-blue-600 dark:text-blue-300 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Marketing Strategies</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Video • 45 min</p>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 px-2 py-1 rounded">Digital Marketing</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Uploaded: 3 days ago</span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 line-clamp-2">Video lecture covering modern digital marketing strategies and social media campaigns.</p>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <i class="fas fa-play-circle mr-1"></i>
                            <span>56 views</span>
                        </div>
                        <a href="https://www.youtube.com/watch?v=ZvChhHNTz1g" target="_blank" rel="noopener noreferrer"
                           class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition duration-200 inline-flex items-center space-x-1">
                            <i class="fas fa-external-link-alt text-xs"></i><span>Watch Video</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-lg transition duration-200 resource-card" data-type="other" data-course="general" data-title="Project Templates Pack">
                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 flex items-center">
                    <div class="bg-yellow-100 dark:bg-yellow-900 p-3 rounded-full">
                        <i class="fas fa-file-archive text-yellow-800 dark:text-yellow-300 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Project Templates Pack</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">ZIP Archive • 15.2 MB</p>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200 px-2 py-1 rounded">General</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Uploaded: 2 months ago</span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 line-clamp-2">Collection of useful project starter templates (briefs, proposals, timelines).</p>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <i class="fas fa-download mr-1"></i>
                            <span>35 downloads</span>
                        </div>
                        <a href="resources/project-templates.zip" download="Project Templates Pack.zip"
                           class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition duration-200 inline-flex items-center space-x-1">
                            <i class="fas fa-download text-xs"></i><span>Download</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- CERTIFICATE EXAMPLES - UPDATED WITH VISUALS -->

            <!-- Web Development Certificate (Image) -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-lg transition duration-200 resource-card" data-type="certificate" data-course="web-dev" data-title="Web Development Completion Certificate">
                <div class="bg-purple-50 dark:bg-purple-900/20 p-4 flex items-center">
                    <div class="bg-purple-100 dark:bg-purple-900 p-3 rounded-full">
                        <i class="fas fa-award text-purple-600 dark:text-purple-300 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Web Dev Completion Cert</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Certificate • Image</p>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 px-2 py-1 rounded">Web Development</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Issued: 1 week ago</span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 line-clamp-2">Certificate of completion for the comprehensive Web Development course.</p>
                    <div class="flex justify-between items-center">
                        <div></div> <!-- Spacer -->
                        <button class="view-certificate-btn bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm transition duration-200 inline-flex items-center space-x-1"
                                data-title="Web Development Completion Certificate"
                                data-src="https://placehold.co/800x600/3b82f6/ffffff.png?text=EduPro+Center%0A%0ACertificate+of+Completion%0A%0AWeb+Development%0A%0AIssued+to%3A+Trainee+Name%0ADate%3A+17-Jul-2024"> <!-- Placeholder Image URL -->
                            <i class="fas fa-eye text-xs"></i><span>View Certificate</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Data Science Certificate (PDF) -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-lg transition duration-200 resource-card" data-type="certificate" data-course="data-science" data-title="Data Science Fundamentals Certificate">
                <div class="bg-purple-50 dark:bg-purple-900/20 p-4 flex items-center">
                    <div class="bg-purple-100 dark:bg-purple-900 p-3 rounded-full">
                        <i class="fas fa-award text-purple-600 dark:text-purple-300 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Data Science Fundamentals</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Certificate • PDF</p>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 px-2 py-1 rounded">Data Science</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Issued: 3 weeks ago</span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 line-clamp-2">Awarded for successfully completing the Data Science Fundamentals module.</p>
                    <div class="flex justify-between items-center">
                         <div></div> <!-- Spacer -->
                        <button class="view-certificate-btn bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm transition duration-200 inline-flex items-center space-x-1"
                                data-title="Data Science Fundamentals Certificate"
                                data-src="resources/certs/data-science-cert.pdf"> <!-- Simulated PDF Path -->
                             <i class="fas fa-eye text-xs"></i><span>View Certificate</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Digital Marketing Certificate (Image) -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-lg transition duration-200 resource-card" data-type="certificate" data-course="digital-marketing" data-title="Digital Marketing Pro Certificate">
                <div class="bg-purple-50 dark:bg-purple-900/20 p-4 flex items-center"> <!-- Using purple header like others -->
                    <div class="bg-purple-100 dark:bg-purple-900 p-3 rounded-full">
                        <i class="fas fa-award text-purple-600 dark:text-purple-300 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Digital Marketing Pro</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Certificate • Image</p>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 px-2 py-1 rounded">Digital Marketing</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Issued: 10 days ago</span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 line-clamp-2">Recognizes proficiency in digital marketing strategies and campaign execution.</p>
                    <div class="flex justify-between items-center">
                         <div></div> <!-- Spacer -->
                        <button class="view-certificate-btn bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm transition duration-200 inline-flex items-center space-x-1"
                                data-title="Digital Marketing Pro Certificate"
                                data-src="https://placehold.co/800x600/f59e0b/ffffff.png?text=EduPro+Center%0A%0ACertificate+of+Achievement%0A%0ADigital+Marketing+Pro%0A%0AIssued+to%3A+Trainee+Name%0ADate%3A+14-Jul-2024"> <!-- Placeholder Image URL -->
                             <i class="fas fa-eye text-xs"></i><span>View Certificate</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Graphic Design Certificate (Image) -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-lg transition duration-200 resource-card" data-type="certificate" data-course="graphic-design" data-title="Graphic Design Essentials Certificate">
                <div class="bg-purple-50 dark:bg-purple-900/20 p-4 flex items-center"> <!-- Using purple header like others -->
                    <div class="bg-purple-100 dark:bg-purple-900 p-3 rounded-full">
                        <i class="fas fa-award text-purple-600 dark:text-purple-300 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Graphic Design Essentials</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Certificate • Image</p>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200 px-2 py-1 rounded">Graphic Design</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Issued: 1 month ago</span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 line-clamp-2">Awarded upon successful completion of the Graphic Design Essentials course.</p>
                    <div class="flex justify-between items-center">
                         <div></div> <!-- Spacer -->
                        <button class="view-certificate-btn bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm transition duration-200 inline-flex items-center space-x-1"
                                data-title="Graphic Design Essentials Certificate"
                                data-src="https://placehold.co/800x600/ec4899/ffffff.png?text=EduPro+Center%0A%0ACertified+Professional%0A%0AGraphic+Design+Essentials%0A%0AIssued+to%3A+Trainee+Name%0ADate%3A+24-Jun-2024"> <!-- Placeholder Image URL -->
                             <i class="fas fa-eye text-xs"></i><span>View Certificate</span>
                        </button>
                    </div>
                </div>
            </div>

             <!-- Cards will be added here dynamically -->
        </div>

    </div>

    <!-- Notes Viewer Modal (No changes needed here) -->
    <div id="notesModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-4xl h-[90vh] flex flex-col">
            <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 px-6 py-3 flex-shrink-0">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100" id="notesTitle">Study Notes</h3>
                <button id="closeNotesModal" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="flex-grow overflow-auto p-6 md:p-8">
                <div id="notesContent" class="prose dark:prose-invert max-w-none">
                    <p>Loading notes...</p>
                </div>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-3 flex justify-end items-center space-x-3 flex-shrink-0">
                 <button id="printNotesBtn" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm inline-flex items-center space-x-1">
                    <i class="fas fa-print text-xs"></i><span>Print</span>
                </button>
                <button id="downloadNotesBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm inline-flex items-center space-x-1" title="Download as simple text file">
                    <i class="fas fa-download text-xs"></i><span>Download Text</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Certificate Viewer Modal (No changes needed here) -->
    <div id="certificateModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-4xl h-auto max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 px-6 py-3 flex-shrink-0">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100" id="certificateTitle">Certificate</h3>
                <button id="closeCertificateModal" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="flex-grow overflow-auto p-6 md:p-8 text-center">
                <div id="certificateContent">
                    <p>Loading certificate...</p>
                </div>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-3 flex justify-end items-center space-x-3 flex-shrink-0">
                <button id="downloadCertificateBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm inline-flex items-center space-x-1" title="Download Certificate File">
                    <i class="fas fa-download text-xs"></i><span>Download</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        // --- THEME TOGGLE START ---
        const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
        const themeToggleButton = document.getElementById('theme-toggle');

        const setTheme = (theme) => {
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                themeToggleLightIcon?.classList.remove('hidden');
                themeToggleDarkIcon?.classList.add('hidden');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                themeToggleDarkIcon?.classList.remove('hidden');
                themeToggleLightIcon?.classList.add('hidden');
                localStorage.setItem('theme', 'light');
            }
        };

        const storedTheme = localStorage.getItem('theme');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (storedTheme) {
            setTheme(storedTheme);
        } else {
            setTheme(systemPrefersDark ? 'dark' : 'light');
        }

        themeToggleButton?.addEventListener('click', () => {
            const currentTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            setTheme(currentTheme === 'dark' ? 'light' : 'dark');
        });
        // --- THEME TOGGLE END ---


        // --- TAB FUNCTIONALITY START ---
        const tabButtons = document.querySelectorAll('.tab-btn');
        const resourceGrid = document.getElementById('resourceGrid');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                tabButtons.forEach(btn => {
                    btn.classList.remove('active', 'text-blue-600', 'border-blue-600');
                    btn.classList.add('text-gray-600', 'hover:text-blue-600');
                    if (document.documentElement.classList.contains('dark')) {
                        btn.classList.remove('dark:tab-btn.active');
                        btn.classList.add('dark:tab-btn:not(.active)', 'dark:tab-btn:hover:not(.active)');
                    }
                });
                 button.classList.add('active', 'text-blue-600', 'border-blue-600');
                 button.classList.remove('text-gray-600', 'hover:text-blue-600');
                 if (document.documentElement.classList.contains('dark')) {
                     button.classList.add('dark:tab-btn.active');
                     button.classList.remove('dark:tab-btn:not(.active)', 'dark:tab-btn:hover:not(.active)');
                 }
                 filterResources(button.dataset.tab);
            });
        });

        function filterResources(filter) {
             const currentResourceCards = resourceGrid.querySelectorAll('.resource-card');
             currentResourceCards.forEach(card => {
                const cardType = card.dataset.type;
                if (filter === 'all' || cardType === filter) {
                    card.style.display = ''; // Show
                } else {
                    card.style.display = 'none'; // Hide
                }
            });
        }
        // --- TAB FUNCTIONALITY END ---


        // --- UPLOAD MODAL START ---
        const uploadBtn = document.getElementById('uploadBtn');
        const uploadModal = document.getElementById('uploadModal');
        const closeModal = document.getElementById('closeModal');
        const cancelUpload = document.getElementById('cancelUpload');
        const uploadForm = document.getElementById('uploadForm');
        const resourceTypeSelect = document.getElementById('resourceTypeSelect');
        const linkInputDiv = document.getElementById('linkInputDiv');
        const fileInputDiv = document.getElementById('fileInputDiv');
        const notesInputDiv = document.getElementById('notesInputDiv');
        const fileInput = uploadForm.querySelector('input[type="file"]');
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        const fileInputDescription = document.getElementById('fileInputDescription');
        const submitUploadBtn = document.getElementById('submitUploadBtn');
        const uploadButtonText = document.getElementById('uploadButtonText');
        const uploadSpinner = document.getElementById('uploadSpinner');

        resourceTypeSelect?.addEventListener('change', (e) => {
            const type = e.target.value;
            const linkInput = linkInputDiv.querySelector('input[name="resourceLink"]');
            const notesTextArea = notesInputDiv.querySelector('textarea[name="notesContent"]');

            linkInputDiv.classList.toggle('hidden', type !== 'video');
            notesInputDiv.classList.toggle('hidden', type !== 'notes');
            fileInputDiv.classList.toggle('hidden', type === 'video');

            if (linkInput) linkInput.required = false;
            if (fileInput) fileInput.required = false;
            if (notesTextArea) notesTextArea.required = false;

            // Reset accept attribute first
            if (fileInput) fileInput.accept = ".pdf,.txt,.html,.zip,.docx,.doc,.png,.jpg,.jpeg";

            if (type === 'video') {
                if (linkInput) linkInput.required = true;
                fileInputDescription.textContent = "File upload not applicable for Video type.";
            } else if (type === 'notes') {
                fileInputDescription.textContent = "Upload an optional HTML or TXT file OR use the text area below.";
                if (fileInput) fileInput.accept = ".txt,.html"; // Limit notes file types
            } else if (type === 'certificate') {
                if (fileInput) fileInput.required = true;
                fileInputDescription.textContent = "Required: Upload certificate file (JPG, PNG, PDF).";
                if (fileInput) fileInput.accept = ".pdf,.png,.jpg,.jpeg"; // Limit cert file types
            } else if (type === 'pdf') {
                if (fileInput) fileInput.required = true;
                fileInputDescription.textContent = "Required: Upload PDF file.";
                 if (fileInput) fileInput.accept = ".pdf";
            } else if (type === 'other') {
                 if (fileInput) fileInput.required = true;
                 fileInputDescription.textContent = "Required: Upload ZIP, DOCX, etc.";
                 if (fileInput) fileInput.accept = ".zip,.doc,.docx,.ppt,.pptx,.xls,.xlsx"; // Suggest common 'other' types
            } else {
                 fileInputDescription.textContent = "Select a resource type to see file requirements.";
            }
        });

        uploadBtn?.addEventListener('click', () => {
             uploadModal?.classList.remove('hidden');
             uploadForm.reset();
             fileNameDisplay.textContent = '';
             linkInputDiv.classList.add('hidden');
             notesInputDiv.classList.add('hidden');
             fileInputDiv.classList.remove('hidden');
             fileInputDescription.textContent = "Select a resource type to see file requirements.";
             const linkInput = linkInputDiv.querySelector('input[name="resourceLink"]');
             if (linkInput) linkInput.required = false;
             if (fileInput) {
                 fileInput.required = false;
                 fileInput.accept = ".pdf,.txt,.html,.zip,.docx,.doc,.png,.jpg,.jpeg";
             }
        });
        closeModal?.addEventListener('click', () => uploadModal?.classList.add('hidden'));
        cancelUpload?.addEventListener('click', () => uploadModal?.classList.add('hidden'));

        fileInput?.addEventListener('change', () => {
            fileNameDisplay.textContent = fileInput.files.length > 0 ? `Selected: ${fileInput.files[0].name}` : '';
        });

        uploadForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(uploadForm);
            const resourceType = formData.get('resourceType');
            const resourceFile = formData.get('resourceFile');
            const notesContentValue = formData.get('notesContent');
            const resourceLink = formData.get('resourceLink');

            let isValid = true;
            let alertMessage = '';
            if (!formData.get('resourceTitle')) { isValid = false; alertMessage = 'Please enter a Resource Title.'; }
            else if (!resourceType) { isValid = false; alertMessage = 'Please select a Resource Type.'; }
            else if (!formData.get('course')) { isValid = false; alertMessage = 'Please select a Course.'; }
            else if (resourceType === 'video' && !resourceLink) { isValid = false; alertMessage = 'Please provide a Resource Link for video type.'; }
            else if ((resourceType === 'pdf' || resourceType === 'other' || resourceType === 'certificate') && (!resourceFile || resourceFile.size === 0)) { isValid = false; alertMessage = `Please upload a file for ${resourceType} type.`; }
            else if (resourceType === 'notes' && (!resourceFile || resourceFile.size === 0) && !notesContentValue) { isValid = false; alertMessage = 'Please either upload a file or enter content for Notes type.'; }
            else if (resourceType === 'certificate' && resourceFile && resourceFile.size > 0) { const allowedCertTypes = ['image/jpeg', 'image/png', 'application/pdf']; if (!allowedCertTypes.includes(resourceFile.type)) { isValid = false; alertMessage = 'Invalid file type for Certificate. Please upload JPG, PNG, or PDF.'; } }
            else if (resourceType === 'notes' && resourceFile && resourceFile.size > 0) { const allowedNotesTypes = ['text/plain', 'text/html']; if (!allowedNotesTypes.includes(resourceFile.type)) { isValid = false; alertMessage = 'Invalid file type for Notes. Please upload TXT or HTML.'; } }

            if (!isValid) { alert(alertMessage); return; }

            submitUploadBtn.disabled = true;
            uploadButtonText.textContent = 'Uploading...';
            uploadSpinner.classList.remove('hidden');

            let tempFilePath = null;
            if (resourceFile && resourceFile.size > 0) { tempFilePath = URL.createObjectURL(resourceFile); }

            const resourceData = {
                title: formData.get('resourceTitle'), type: resourceType, course: formData.get('course'),
                link: resourceLink, file: resourceFile, notes: notesContentValue,
                contentId: formData.get('resourceTitle').toLowerCase().replace(/\s+/g, '-') + '-' + Date.now(),
                filePath: tempFilePath, fileType: resourceFile?.type
            };

             await new Promise(resolve => setTimeout(resolve, 1500)); // Simulate network delay
             console.log("Simulating upload for:", resourceData);
             addNewResourceCard(resourceData);
             alert('Resource added successfully! (Frontend Simulation)');
             uploadModal.classList.add('hidden');

             submitUploadBtn.disabled = false;
             uploadButtonText.textContent = 'Upload';
             uploadSpinner.classList.add('hidden');
        });

        function addNewResourceCard(data) {
             const uploadedDate = "Just now";
             let fileSize = 'N/A';
             if (data.file && data.file.size > 0) { fileSize = `${(data.file.size / 1024 / 1024).toFixed(1)} MB`; }
             else if (data.type === 'video') { fileSize = 'Link'; }
             else if (data.type === 'notes' && data.notes) { fileSize = `${(data.notes.length / 1024).toFixed(1)} KB`; }

             let iconClass = 'fa-file', iconBgClass = 'bg-gray-100 dark:bg-gray-700', iconColorClass = 'text-gray-800 dark:text-gray-300';
             let headerBgClass = 'bg-gray-50 dark:bg-gray-900/20', typeText = data.type.charAt(0).toUpperCase() + data.type.slice(1);
             let actionButtonHTML = '', statsHTML = '';
             const resourceUrl = data.type === 'video' ? data.link : data.filePath;

             switch (data.type) {
                 case 'pdf':
                     iconClass = 'fa-file-pdf'; iconBgClass = 'bg-red-100 dark:bg-red-900'; iconColorClass = 'text-red-600 dark:text-red-300'; headerBgClass = 'bg-red-50 dark:bg-red-900/20'; typeText = 'PDF Document';
                     actionButtonHTML = `<a href="${resourceUrl || '#'}" download="${data.title}.pdf" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition duration-200 inline-flex items-center space-x-1"><i class="fas fa-download text-xs"></i><span>Download</span></a>`;
                     statsHTML = '<i class="fas fa-download mr-1"></i> 0 downloads';
                     break;
                 case 'notes':
                     iconClass = 'fa-file-alt'; iconBgClass = 'bg-green-100 dark:bg-green-900'; iconColorClass = 'text-green-600 dark:text-green-300'; headerBgClass = 'bg-green-50 dark:bg-green-900/20'; typeText = 'Study Notes';
                     const notesDataSource = data.notes ? `data-notes-content="${escape(data.notes)}"` : `data-notes-url="${resourceUrl || '#'}"`;
                     actionButtonHTML = `<button class="view-notes-btn bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm transition duration-200 inline-flex items-center space-x-1" data-content-id="${data.contentId}" data-title="${data.title}" ${notesDataSource}><i class="fas fa-eye text-xs"></i><span>View Notes</span></button>`;
                     statsHTML = '<i class="fas fa-eye mr-1"></i> 0 views';
                     break;
                 case 'video':
                     iconClass = 'fa-video'; iconBgClass = 'bg-blue-100 dark:bg-blue-900'; iconColorClass = 'text-blue-600 dark:text-blue-300'; headerBgClass = 'bg-blue-50 dark:bg-blue-900/20'; typeText = 'Video';
                     actionButtonHTML = `<a href="${resourceUrl || '#'}" target="_blank" rel="noopener noreferrer" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition duration-200 inline-flex items-center space-x-1"><i class="fas fa-external-link-alt text-xs"></i><span>Watch Video</span></a>`;
                     statsHTML = '<i class="fas fa-play-circle mr-1"></i> 0 views';
                     break;
                 case 'certificate':
                     iconClass = 'fa-award'; iconBgClass = 'bg-purple-100 dark:bg-purple-900'; iconColorClass = 'text-purple-600 dark:text-purple-300'; headerBgClass = 'bg-purple-50 dark:bg-purple-900/20'; typeText = 'Certificate';
                     if (data.fileType?.startsWith('image/')) typeText += ' (Image)'; else if (data.fileType === 'application/pdf') typeText += ' (PDF)';
                     actionButtonHTML = `<button class="view-certificate-btn bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm transition duration-200 inline-flex items-center space-x-1" data-title="${data.title}" data-src="${resourceUrl || '#'}"><i class="fas fa-eye text-xs"></i><span>View Certificate</span></button>`;
                     statsHTML = ''; // No stats
                     break;
                 case 'other':
                     iconClass = 'fa-file-archive'; iconBgClass = 'bg-yellow-100 dark:bg-yellow-900'; iconColorClass = 'text-yellow-800 dark:text-yellow-300'; headerBgClass = 'bg-yellow-50 dark:bg-yellow-900/20'; typeText = 'Other File';
                     actionButtonHTML = `<a href="${resourceUrl || '#'}" download="${data.title}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition duration-200 inline-flex items-center space-x-1"><i class="fas fa-download text-xs"></i><span>Download</span></a>`;
                     statsHTML = '<i class="fas fa-download mr-1"></i> 0 downloads';
                     break;
             }

             let courseTagBg = 'bg-gray-100 dark:bg-gray-600', courseTagText = 'text-gray-800 dark:text-gray-200';
             switch (data.course) { /* Cases for course tag colors */
                 case 'web-dev': courseTagBg = 'bg-blue-100 dark:bg-blue-900'; courseTagText = 'text-blue-800 dark:text-blue-200'; break;
                 case 'data-science': courseTagBg = 'bg-purple-100 dark:bg-purple-900'; courseTagText = 'text-purple-800 dark:text-purple-200'; break;
                 case 'digital-marketing': courseTagBg = 'bg-yellow-100 dark:bg-yellow-900'; courseTagText = 'text-yellow-800 dark:text-yellow-200'; break;
                 case 'graphic-design': courseTagBg = 'bg-pink-100 dark:bg-pink-900'; courseTagText = 'text-pink-800 dark:text-pink-200'; break;
             }
             const courseName = data.course.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');

             const cardHTML = `
                 <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-lg transition duration-200 resource-card" data-type="${data.type}" data-course="${data.course}" data-title="${data.title}">
                     <div class="${headerBgClass} p-4 flex items-center">
                         <div class="${iconBgClass} p-3 rounded-full"><i class="fas ${iconClass} ${iconColorClass} text-xl"></i></div>
                         <div class="ml-4">
                             <h3 class="font-semibold text-gray-800 dark:text-gray-100">${data.title}</h3>
                             <p class="text-sm text-gray-600 dark:text-gray-400">${typeText} • ${fileSize}</p>
                         </div>
                     </div>
                     <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                         <div class="flex justify-between items-center mb-3">
                             <span class="text-xs ${courseTagBg} ${courseTagText} px-2 py-1 rounded">${courseName}</span>
                             <span class="text-xs text-gray-500 dark:text-gray-400">${data.type === 'certificate' ? 'Issued:' : 'Uploaded:'} ${uploadedDate}</span>
                         </div>
                         <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 line-clamp-2">Newly added resource. ${data.type === 'certificate' ? 'Certificate.' : 'Description...'}</p>
                         <div class="flex justify-between items-center">
                             <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">${statsHTML}</div>
                             ${actionButtonHTML}
                         </div>
                     </div>
                 </div>`;
             resourceGrid.insertAdjacentHTML('afterbegin', cardHTML);

             const currentFilter = document.querySelector('.tab-btn.active')?.dataset.tab || 'all';
             filterResources(currentFilter); // Apply filter immediately
             if (data.type === 'notes') attachNotesViewerListeners();
             else if (data.type === 'certificate') attachCertificateViewerListeners();
         }
        // --- UPLOAD MODAL END ---


        // --- NOTES VIEWER START ---
        const notesModal = document.getElementById('notesModal');
        const closeNotesModal = document.getElementById('closeNotesModal');
        const notesTitle = document.getElementById('notesTitle');
        const notesContent = document.getElementById('notesContent');
        const printNotesBtn = document.getElementById('printNotesBtn');
        const downloadNotesBtn = document.getElementById('downloadNotesBtn');

        function attachNotesViewerListeners() {
            const viewButtons = resourceGrid.querySelectorAll('.view-notes-btn');
            viewButtons.forEach(viewButton => {
                viewButton.removeEventListener('click', handleViewNotesClick);
                viewButton.addEventListener('click', handleViewNotesClick);
            });
        }

        async function handleViewNotesClick(event) {
            const viewButton = event.currentTarget;
            const contentId = viewButton.dataset.contentId;
            const title = viewButton.dataset.title || 'Study Notes';
            const notesTextContent = viewButton.dataset.notesContent;
            const notesUrl = viewButton.dataset.notesUrl;

            notesTitle.textContent = title;
            notesContent.innerHTML = '<p class="text-center p-8">Loading notes...</p>';
            downloadNotesBtn.dataset.notesTextForDownload = '';
            notesModal?.classList.remove('hidden');

             try {
                 let fetchedHtml = '';
                 if (notesTextContent) { fetchedHtml = unescape(notesTextContent); }
                 else if (notesUrl && notesUrl !== '#') {
                      if (notesUrl.startsWith('blob:')) {
                         const response = await fetch(notesUrl); if (!response.ok) throw new Error(`Failed blob fetch`); fetchedHtml = await response.text();
                      } else { fetchedHtml = getPlaceholderNotes(contentId); } // Use placeholder for non-blob
                 } else { fetchedHtml = getPlaceholderNotes(contentId); }

                 if (!fetchedHtml) { throw new Error('No content found.'); }
                 notesContent.innerHTML = fetchedHtml;
                 downloadNotesBtn.dataset.notesTextForDownload = notesContent.textContent || '';
             } catch (error) {
                 console.error("Error loading notes:", error);
                 notesContent.innerHTML = `<p class="text-center p-8 text-red-500 dark:text-red-400">Could not load notes content.</p>`;
                 downloadNotesBtn.dataset.notesTextForDownload = '';
             }
        }

        function getPlaceholderNotes(contentId) { /* Placeholder content */
            switch (contentId) {
                 case 'data-science-notes': return `<h1>Data Science Notes</h1><h2>Python Fundamentals</h2>...`;
                 case 'design-principles': return `<h1>Design Principles Notes</h1><h2>Key Principles</h2>...`;
                 case 'seo-basics': return `<h1>SEO Basics Notes</h1><h2>What is SEO?</h2>...`;
                 default: return `<p>Notes content for ${contentId} not found.</p>`;
            }
        }

        closeNotesModal?.addEventListener('click', () => { notesModal?.classList.add('hidden'); notesContent.innerHTML = '<p>Loading notes...</p>'; downloadNotesBtn.dataset.notesTextForDownload = ''; });
        printNotesBtn?.addEventListener('click', () => { window.print(); });
        downloadNotesBtn?.addEventListener('click', () => { /* Download logic */
            const textToDownload = downloadNotesBtn.dataset.notesTextForDownload; const title = notesTitle.textContent || 'notes'; if (!textToDownload) { alert('No text content.'); return; } const blob = new Blob([textToDownload], { type: 'text/plain' }); const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = `${title.replace(/[^a-z0-9]/gi, '_').toLowerCase()}.txt`; document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url);
        });
        // --- NOTES VIEWER END ---


        // --- CERTIFICATE VIEWER START ---
        const certificateModal = document.getElementById('certificateModal');
        const closeCertificateModal = document.getElementById('closeCertificateModal');
        const certificateTitle = document.getElementById('certificateTitle');
        const certificateContent = document.getElementById('certificateContent');
        const downloadCertificateBtn = document.getElementById('downloadCertificateBtn');

        function attachCertificateViewerListeners() {
            const viewCertButtons = resourceGrid.querySelectorAll('.view-certificate-btn');
            viewCertButtons.forEach(button => {
                button.removeEventListener('click', handleViewCertificateClick);
                button.addEventListener('click', handleViewCertificateClick);
            });
        }

        async function handleViewCertificateClick(event) {
            const button = event.currentTarget;
            const title = button.dataset.title || 'Certificate';
            const src = button.dataset.src;

            certificateTitle.textContent = title;
            certificateContent.innerHTML = '<p class="text-center p-8">Loading certificate...</p>';
            downloadCertificateBtn.dataset.downloadUrl = '';
            downloadCertificateBtn.dataset.downloadFilename = '';
            certificateModal?.classList.remove('hidden');

            if (!src || src === '#') { certificateContent.innerHTML = '<p class="text-center p-8 text-red-500 dark:text-red-400">Certificate source not found.</p>'; return; }

            let filename = `${title.replace(/[^a-z0-9]/gi, '_').toLowerCase()}`;
            const extensionMatch = src.match(/\.([a-z0-9]+)(?:[?#]|$)/i); // Try to get ext from URL
            const isBlob = src.startsWith('blob:');

            downloadCertificateBtn.dataset.downloadUrl = src;

            try {
                 // Prioritize image display if it looks like an image URL or is a blob URL (might be an image)
                 if (src.match(/\.(jpeg|jpg|gif|png|svg|webp)(?:[?#]|$)/i) || isBlob) {
                     // If it's a blob URL, we need to be more careful about its validity
                     if (isBlob) {
                         try { await fetch(src, { method: 'HEAD' }); }
                         catch (fetchError) { throw new Error("Preview unavailable (source expired). Please try downloading."); }
                         // Try to guess filename extension from original file if stored (not implemented here)
                         // filename += ".png"; // Default guess if blob and no extension found
                     }

                     // Add extension to filename if found in URL
                     if (extensionMatch && !isBlob) filename += `.${extensionMatch[1]}`;
                     else if(isBlob) filename += ".png"; // Default extension for blob download if unknown

                     certificateContent.innerHTML = `<img src="${src}" alt="${title}" class="max-w-full h-auto mx-auto">`;

                 } else if (src.match(/\.(pdf)(?:[?#]|$)/i)) {
                      filename += `.pdf`;
                      certificateContent.innerHTML = `<div class="placeholder text-center p-8"><i class="fas fa-file-pdf text-6xl text-red-500 dark:text-red-400 mb-4"></i><p class="text-gray-600 dark:text-gray-400">This is a PDF certificate.</p><p class="text-gray-500 dark:text-gray-500 text-sm mt-2">Use the download button below to view the file.</p></div>`;

                 } else {
                     if (extensionMatch) filename += `.${extensionMatch[1]}`;
                     certificateContent.innerHTML = `<div class="placeholder text-center p-8"><i class="fas fa-file-alt text-6xl text-gray-500 dark:text-gray-400 mb-4"></i><p class="text-gray-600 dark:text-gray-400">Cannot preview this file type.</p><p class="text-gray-500 dark:text-gray-500 text-sm mt-2">Use the download button below.</p></div>`;
                 }
                 downloadCertificateBtn.dataset.downloadFilename = filename; // Set final filename

            } catch (error) {
                 console.error("Error loading certificate preview:", error);
                 certificateContent.innerHTML = `<p class="text-center p-8 text-red-500 dark:text-red-400">Could not load certificate preview: ${error.message}</p>`;
            }
        }

        closeCertificateModal?.addEventListener('click', () => { certificateModal?.classList.add('hidden'); certificateContent.innerHTML = '<p>Loading certificate...</p>'; });
        downloadCertificateBtn?.addEventListener('click', () => { /* Download logic */
            const url = downloadCertificateBtn.dataset.downloadUrl; const filename = downloadCertificateBtn.dataset.downloadFilename || 'certificate'; if (!url || url === '#') { alert('No download link.'); return; } const a = document.createElement('a'); a.href = url; a.download = filename; document.body.appendChild(a); a.click(); document.body.removeChild(a);
        });
        // --- CERTIFICATE VIEWER END ---


        // --- Initial Setup ---
        filterResources('all');
        attachNotesViewerListeners();
        attachCertificateViewerListeners();

    </script>

</body>
</html>