<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? APP_NAME; ?></title>
    <link rel="icon" type="image/png" href="<?php echo getSystemLogo(); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e40af',
                        secondary: '#059669'
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom scrollbar for sidebar */
        #sidebar nav::-webkit-scrollbar {
            width: 6px;
        }
        #sidebar nav::-webkit-scrollbar-track {
            background: #1f2937;
        }
        #sidebar nav::-webkit-scrollbar-thumb {
            background: #4b5563;
            border-radius: 3px;
        }
        #sidebar nav::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }
        
        /* Blob animation for auth pages */
        @keyframes blob {
            0% {
                transform: translate(0px, 0px) scale(1);
            }
            33% {
                transform: translate(30px, -50px) scale(1.1);
            }
            66% {
                transform: translate(-20px, 20px) scale(0.9);
            }
            100% {
                transform: translate(0px, 0px) scale(1);
            }
        }
        
        .animate-blob {
            animation: blob 7s infinite;
        }
        
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        
        .animation-delay-4000 {
            animation-delay: 4s;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php $isLoggedIn = Auth::isLoggedIn(); ?>
    <?php if ($isLoggedIn): ?>
        <?php include APP_PATH . '/views/layouts/sidebar.php'; ?>
        
        <!-- Main Content Wrapper -->
        <div class="lg:ml-64">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <!-- Mobile menu button -->
                        <button id="openSidebar" class="lg:hidden text-gray-600 hover:text-gray-900 focus:outline-none focus:text-gray-900">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        
                        <!-- Page Title (hidden on mobile) -->
                        <div class="hidden md:block">
                            <h1 class="text-xl font-semibold text-gray-800"><?php echo $title ?? 'Dashboard'; ?></h1>
                        </div>
                        
                        <!-- Right side actions -->
                        <div class="flex items-center space-x-4">
                            <div class="hidden md:flex items-center space-x-4 text-sm text-gray-600">
                                <span><i class="far fa-calendar mr-1"></i><?php echo date('d M Y'); ?></span>
                            </div>
                            
                            <?php 
                            // Show notifications only for super_admin and school_manager
                            $user = Auth::user();
                            $role = strtolower($user['role_name'] ?? '');
                            if (in_array($role, ['super_admin', 'school_manager'])): 
                            ?>
                            <!-- Notifications -->
                            <div class="relative" id="notificationContainer">
                                <button id="notificationBell" class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:text-gray-900">
                                    <i class="fas fa-bell text-xl"></i>
                                    <span id="notificationBadge" class="absolute top-0 right-0 h-4 w-4 bg-red-500 rounded-full text-xs text-white flex items-center justify-center hidden">0</span>
                                </button>
                                
                                <!-- Notification Dropdown -->
                                <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50 max-h-96 overflow-y-auto">
                                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                                        <h3 class="font-semibold text-gray-800">Notifications</h3>
                                        <button id="markAllReadBtn" class="text-xs text-blue-600 hover:text-blue-800">Mark all as read</button>
                                    </div>
                                    <div id="notificationList" class="divide-y divide-gray-200">
                                        <div class="p-4 text-center text-gray-500">
                                            <i class="fas fa-spinner fa-spin"></i> Loading...
                                        </div>
                                    </div>
                                    <div id="notificationEmpty" class="hidden p-4 text-center text-gray-500">
                                        <i class="fas fa-bell-slash text-2xl mb-2"></i>
                                        <p>No notifications</p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="flex items-center space-x-2">
                                <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-sm font-medium">
                                    <?php 
                                    echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); 
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="min-h-screen">
                <?php 
                $flash = getFlashMessage();
                if ($flash): 
                ?>
                <div class="px-4 sm:px-6 lg:px-8 mt-4">
                    <div class="alert alert-<?php echo $flash['type']; ?> bg-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-100 border border-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-400 text-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($flash['message']); ?></span>
                    </div>
                </div>
                <?php endif; ?>
    <?php endif; ?>