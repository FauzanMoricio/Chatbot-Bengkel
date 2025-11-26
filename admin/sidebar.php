<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CNS SIDEBAR</title>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Tailwind CSS -->
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->
    <link href="../../../css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<!-- Sidebar component -->
<div x-data="{ open: false }" class="flex h-screen bg-gray-100 overflow-hidden">
    <!-- Mobile menu button -->
    <div class="md:hidden fixed top-0 left-0 right-0 z-40 bg-white shadow-md">
        <div class="flex items-center p-4">
            <button @click="open = !open" class="bg-blue-800 text-white p-2 rounded-md">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <div class="ml-4 flex-1">
                <div class="bg-blue-50 rounded-lg p-2 px-4 flex items-center">
                    <span class="font-medium text-blue-800">Welcome, Admin!</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div 
        :class="open ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-30 w-64 bg-blue-800 text-white transition-transform duration-300 ease-in-out md:translate-x-0 h-screen overflow-y-auto">
        <div class="p-5 text-center border-b border-blue-700">
            <a href="../public/index.php"><img src="../assets/img/logocns.png" width="80" height="50" class="m-5" style="display: block; margin: auto;"></a>
            <h2 class="text-xl font-bold">Bengkel Motor</h2>
        </div>
        
        <nav class="flex-1 py-4 space-y-1">
            <a href="index.php" class="flex items-center px-4 py-3 text-white hover:bg-blue-700 rounded-md transition-colors <?= strpos($_SERVER['PHP_SELF'], 'index.php') !== false ? 'bg-blue-700' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Respon Percakapan
            </a>

            <a href="image.php" class="flex items-center px-4 py-3 text-white hover:bg-blue-700 rounded-md transition-colors <?= strpos($_SERVER['PHP_SELF'], 'image.php') !== false ? 'bg-blue-700' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"  d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
                Link Gambar
            </a>
                <a href="bokings.php"class="flex items-center px-4 py-3 text-white hover:bg-blue-700 rounded-md transition-colors <?= strpos($_SERVER['PHP_SELF'], 'bokings.php') !== false ? 'bg-blue-700' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24"  stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                </svg>
                Atur Jadwal
            </a>
            
            <a href="ulasan.php" class="flex items-center px-4 py-3 text-white hover:bg-blue-700 rounded-md transition-colors <?= strpos($_SERVER['PHP_SELF'], 'ulasan.php') !== false ? 'bg-blue-700' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24"  stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                </svg>
                Daftar Ulasan
            </a>

        </nav>
        <div class="p-4 border-t border-blue-700">
            <a href="../admin/logout.php" class="flex items-center text-white hover:text-blue-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout

            </a>
        </div>
    </div>

    <!-- Overlay for mobile sidebar -->
    <div 
        @click="open = false"
        :class="open ? 'opacity-50 pointer-events-auto' : 'opacity-0 pointer-events-none'"
        class="fixed inset-0 z-20 bg-black transition-opacity duration-300 ease-in-out  md:hidden">
    </div>
    
    <!-- Main Content Area (just a placeholder, will be replaced by your actual content) -->
    <div class="flex-1 md:ml-64 overflow-y-auto h-screen">
        <!-- Your content goes here -->
    </div>
</div>
</body>
</html>