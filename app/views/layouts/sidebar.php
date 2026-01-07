<?php
/**
 * Sidebar Navigation Component
 * Modern sidebar menu with icons and role-based access
 */
if (!Auth::isLoggedIn()) {
    return;
}

$user = Auth::user();
$role = strtolower($user['role_name']);

// Get current path from REQUEST_URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
// Remove query string if present
$requestUri = parse_url($requestUri, PHP_URL_PATH) ?? $requestUri;
// Remove base path if present (e.g., /masomo)
$basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
if ($basePath && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}
// Remove leading/trailing slashes and split
$currentPath = trim($requestUri, '/');
$pathSegments = !empty($currentPath) ? explode('/', $currentPath) : [];
$currentSegment = $pathSegments[0] ?? 'dashboard';
$secondSegment = $pathSegments[1] ?? '';

// Define menu items based on role
$menuItems = [];

// Dashboard - All roles
$menuItems[] = [
    'title' => 'Dashboard',
    'icon' => 'fa-home',
    'url' => '/dashboard',
    'roles' => ['super_admin', 'school_admin', 'school_manager', 'head_teacher', 'teacher', 'accountant', 'receptionist', 'bursar', 'parent'],
    'active' => $currentSegment === 'dashboard'
];

// Academic Management
if (in_array($role, ['super_admin', 'school_admin', 'school_manager'])) {
    $menuItems[] = [
        'title' => 'Grades',
        'icon' => 'fa-graduation-cap',
        'url' => '/grades',
        'roles' => ['super_admin', 'school_admin', 'school_manager'],
        'active' => $currentSegment === 'grades'
    ];
}

if (in_array($role, ['super_admin', 'school_admin', 'school_manager', 'head_teacher'])) {
    $menuItems[] = [
        'title' => 'Subjects',
        'icon' => 'fa-book',
        'url' => '/subjects',
        'roles' => ['super_admin', 'school_admin', 'school_manager', 'head_teacher'],
        'active' => $currentSegment === 'subjects'
    ];
}

if (in_array($role, ['super_admin', 'school_admin', 'school_manager', 'head_teacher'])) {
    $menuItems[] = [
        'title' => 'Classes',
        'icon' => 'fa-school',
        'url' => '/classes',
        'roles' => ['super_admin', 'school_admin', 'school_manager', 'head_teacher'],
        'active' => $currentSegment === 'classes'
    ];
}

if (in_array($role, ['super_admin', 'school_admin', 'school_manager', 'head_teacher'])) {
    $menuItems[] = [
        'title' => 'Teachers',
        'icon' => 'fa-chalkboard-teacher',
        'url' => '/teachers',
        'roles' => ['super_admin', 'school_admin', 'school_manager', 'head_teacher'],
        'active' => $currentSegment === 'teachers'
    ];
}

if (in_array($role, ['super_admin', 'school_admin', 'school_manager', 'head_teacher', 'teacher', 'receptionist', 'accountant', 'bursar'])) {
    $menuItems[] = [
        'title' => 'Students',
        'icon' => 'fa-user-graduate',
        'url' => '/students',
        'roles' => ['super_admin', 'school_admin', 'school_manager', 'head_teacher', 'teacher', 'receptionist', 'accountant', 'bursar'],
        'active' => $currentSegment === 'students'
    ];
}

// Attendance
if (in_array($role, ['super_admin', 'school_admin', 'school_manager', 'head_teacher', 'teacher', 'receptionist'])) {
    $menuItems[] = [
        'title' => 'Attendance',
        'icon' => 'fa-check-circle',
        'url' => '/attendance',
        'roles' => ['super_admin', 'school_admin', 'school_manager', 'head_teacher', 'teacher', 'receptionist'],
        'active' => $currentSegment === 'attendance'
    ];
}

// Fees Management
if (in_array($role, ['super_admin', 'school_admin', 'school_manager', 'accountant', 'bursar'])) {
    $menuItems[] = [
        'title' => 'Fee Management',
        'icon' => 'fa-money-bill-wave',
        'url' => '/fees',
        'roles' => ['super_admin', 'school_admin', 'school_manager', 'accountant', 'bursar'],
        'active' => $currentSegment === 'fees' || $currentSegment === 'feeheads' || ($currentSegment === 'fees' && $secondSegment === 'reconcile') || $currentSegment === 'equitybank' || $currentSegment === 'kcbbank' || $currentSegment === 'mpesa' || $currentSegment === 'payments',
        'children' => [
            [
                'title' => 'Fee Heads',
                'icon' => 'fa-list',
                'url' => '/feeheads',
                'active' => $currentSegment === 'feeheads'
            ],
            [
                'title' => 'Payments',
                'icon' => 'fa-receipt',
                'url' => '/payments',
                'active' => $currentSegment === 'payments'
            ],
            [
                'title' => 'Reconcile Payments',
                'icon' => 'fa-money-check-alt',
                'url' => '/fees/reconcile',
                'active' => $currentSegment === 'fees' && $secondSegment === 'reconcile'
            ],
            [
                'title' => 'M-Pesa',
                'icon' => 'fa-mobile-alt',
                'url' => '/mpesa',
                'active' => $currentSegment === 'mpesa'
            ],
            [
                'title' => 'Equity Bank',
                'icon' => 'fa-university',
                'url' => '/equitybank',
                'active' => $currentSegment === 'equitybank'
            ],
            [
                'title' => 'KCB Bank',
                'icon' => 'fa-university',
                'url' => '/kcbbank',
                'active' => $currentSegment === 'kcbbank'
            ]
        ]
    ];
}

// Assessments
if (in_array($role, ['super_admin', 'school_admin', 'school_manager', 'head_teacher', 'teacher'])) {
    $menuItems[] = [
        'title' => 'Assessments',
        'icon' => 'fa-book-open',
        'url' => '/assessments',
        'roles' => ['super_admin', 'school_admin', 'school_manager', 'head_teacher', 'teacher'],
        'active' => $currentSegment === 'assessments'
    ];
}

// Examinations
if (in_array($role, ['super_admin', 'school_admin', 'school_manager', 'head_teacher', 'teacher'])) {
    $menuItems[] = [
        'title' => 'Examinations',
        'icon' => 'fa-clipboard-list',
        'url' => '/examinations',
        'roles' => ['super_admin', 'school_admin', 'school_manager', 'head_teacher', 'teacher'],
        'active' => $currentSegment === 'examinations'
    ];
}

// Library
if (in_array($role, ['super_admin', 'school_admin', 'school_manager', 'librarian'])) {
    $menuItems[] = [
        'title' => 'Library',
        'icon' => 'fa-book-reader',
        'url' => '/library',
        'roles' => ['super_admin', 'school_admin', 'school_manager', 'librarian'],
        'active' => $currentSegment === 'library',
        'children' => [
            [
                'title' => 'Books',
                'icon' => 'fa-book',
                'url' => '/library',
                'active' => $currentSegment === 'library' && empty($secondSegment)
            ],
            [
                'title' => 'Assign Book',
                'icon' => 'fa-hand-holding',
                'url' => '/library/assign',
                'active' => $currentSegment === 'library' && $secondSegment === 'assign'
            ],
            [
                'title' => 'Borrows',
                'icon' => 'fa-list-alt',
                'url' => '/library/borrows',
                'active' => $currentSegment === 'library' && $secondSegment === 'borrows'
            ],
            [
                'title' => 'Student Ratings',
                'icon' => 'fa-star',
                'url' => '/library/ratings',
                'active' => $currentSegment === 'library' && $secondSegment === 'ratings'
            ]
        ]
    ];
}

// Parents Management
if (in_array($role, ['super_admin', 'school_admin', 'school_manager', 'receptionist'])) {
    $menuItems[] = [
        'title' => 'Parents',
        'icon' => 'fa-user-friends',
        'url' => '/parents',
        'roles' => ['super_admin', 'school_admin', 'school_manager', 'receptionist'],
        'active' => $currentSegment === 'parents'
    ];
}

// Communication / SMS / Email
if (in_array($role, ['super_admin', 'school_admin', 'school_manager'])) {
    $menuItems[] = [
        'title' => 'Communication',
        'icon' => 'fa-comments',
        'url' => '/communication',
        'roles' => ['super_admin', 'school_admin', 'school_manager'],
        'active' => $currentSegment === 'communication' || $currentSegment === 'emailtemplates',
        'children' => [
            [
                'title' => 'SMS & Email',
                'icon' => 'fa-envelope',
                'url' => '/communication',
                'active' => $currentSegment === 'communication' && $secondSegment !== 'settings'
            ],
            [
                'title' => 'Email Templates',
                'icon' => 'fa-file-alt',
                'url' => '/emailtemplates',
                'active' => $currentSegment === 'emailtemplates'
            ],
            [
                'title' => 'SMS Settings',
                'icon' => 'fa-cog',
                'url' => '/communication/settings',
                'active' => $currentSegment === 'communication' && $secondSegment === 'settings'
            ]
        ]
    ];
}

// Announcements
$menuItems[] = [
    'title' => 'Announcements',
    'icon' => 'fa-bullhorn',
    'url' => '/announcements',
    'roles' => ['super_admin', 'school_admin', 'school_manager', 'head_teacher', 'teacher', 'accountant', 'receptionist', 'bursar', 'parent'],
    'active' => $currentSegment === 'announcements'
];

// Parent Portal
if ($role === 'parent') {
    $menuItems[] = [
        'title' => 'My Children',
        'icon' => 'fa-child',
        'url' => '/parent/dashboard',
        'roles' => ['parent'],
        'active' => $currentSegment === 'parent'
    ];
}

// Reports
if (in_array($role, ['super_admin', 'school_admin', 'school_manager', 'accountant', 'bursar', 'head_teacher'])) {
    $menuItems[] = [
        'title' => 'Reports',
        'icon' => 'fa-chart-bar',
        'url' => '/reports',
        'roles' => ['super_admin', 'school_admin', 'school_manager', 'accountant', 'bursar', 'head_teacher'],
        'active' => $currentSegment === 'reports'
    ];
}

// User Management (Super Admin only)
if ($role === 'super_admin') {
    $menuItems[] = [
        'title' => 'User Management',
        'icon' => 'fa-users-cog',
        'url' => '/users',
        'roles' => ['super_admin'],
        'active' => $currentSegment === 'users'
    ];
    
    $menuItems[] = [
        'title' => 'Role Management',
        'icon' => 'fa-user-shield',
        'url' => '/roles',
        'roles' => ['super_admin'],
        'active' => $currentSegment === 'roles'
    ];
}

// Academic Years (Super Admin only)
if ($role === 'super_admin') {
    $menuItems[] = [
        'title' => 'Academic Years',
        'icon' => 'fa-calendar-alt',
        'url' => '/academicyears',
        'roles' => ['super_admin'],
        'active' => $currentSegment === 'academicyears'
    ];
}

// System Logs (Super Admin and School Manager)
if (in_array($role, ['super_admin', 'school_manager'])) {
    $menuItems[] = [
        'title' => 'System Logs',
        'icon' => 'fa-list-alt',
        'url' => '/systemlogs',
        'roles' => ['super_admin', 'school_manager'],
        'active' => $currentSegment === 'systemlogs'
    ];
}

// Settings
if (in_array($role, ['super_admin', 'school_admin', 'school_manager'])) {
    $menuItems[] = [
        'title' => 'Settings',
        'icon' => 'fa-cog',
        'url' => '/settings',
        'roles' => ['super_admin', 'school_admin', 'school_manager'],
        'active' => $currentSegment === 'settings'
    ];
}

// Filter menu items for current role
$filteredMenuItems = array_filter($menuItems, function($item) use ($role) {
    return in_array($role, $item['roles'] ?? []);
});
?>

<!-- Sidebar -->
<div id="sidebar" class="fixed top-0 left-0 h-screen w-64 bg-gray-900 text-white transform transition-transform duration-300 ease-in-out z-40 lg:translate-x-0 -translate-x-full">
    <div class="flex flex-col h-full">
        <!-- Logo/Brand Section -->
        <div class="flex items-center justify-center border-b border-gray-700 relative p-2">
            <img src="<?php echo getDashboardLogo(); ?>" alt="Logo" class="object-contain transition-transform duration-300 hover:scale-105" style="max-width: 100%; width: auto; height: auto; aspect-ratio: 367/76;">
            <button id="closeSidebar" class="lg:hidden text-gray-400 hover:text-white absolute top-0.5 right-2">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- User Info -->
        <div class="p-4 border-b border-gray-700">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center">
                    <i class="fas fa-user text-white"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                    <p class="text-xs text-gray-400 truncate"><?php echo ucwords(str_replace('_', ' ', $user['role_name'])); ?></p>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1 px-2">
                <?php foreach ($filteredMenuItems as $item): 
                    $hasChildren = !empty($item['children']);
                    $isActive = $item['active'] || ($hasChildren && in_array(true, array_column($item['children'], 'active')));
                ?>
                <li>
                    <?php if ($hasChildren): ?>
                    <!-- Menu item with submenu -->
                    <div class="menu-item-group">
                        <a href="<?php echo BASE_URL . $item['url']; ?>" 
                           class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200 <?php echo $isActive ? 'bg-blue-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?>">
                            <i class="fas <?php echo $item['icon']; ?> w-5 text-center mr-3"></i>
                            <span class="flex-1"><?php echo htmlspecialchars($item['title']); ?></span>
                            <i class="fas fa-chevron-down text-xs transform transition-transform duration-200 <?php echo $isActive ? 'rotate-180' : ''; ?>"></i>
                        </a>
                        <ul class="mt-1 ml-9 space-y-1 <?php echo $isActive ? '' : 'hidden'; ?>">
                            <?php foreach ($item['children'] as $child): ?>
                            <li>
                                <a href="<?php echo BASE_URL . $child['url']; ?>" 
                                   class="flex items-center px-4 py-2 text-sm rounded-lg transition-colors duration-200 <?php echo $child['active'] ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                                    <i class="fas <?php echo $child['icon']; ?> w-4 text-center mr-3"></i>
                                    <span><?php echo htmlspecialchars($child['title']); ?></span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php else: ?>
                    <!-- Single menu item -->
                    <a href="<?php echo BASE_URL . $item['url']; ?>" 
                       class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200 <?php echo $isActive ? 'bg-blue-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?>">
                        <i class="fas <?php echo $item['icon']; ?> w-5 text-center mr-3"></i>
                        <span><?php echo htmlspecialchars($item['title']); ?></span>
                        <?php if ($isActive): ?>
                        <span class="ml-auto h-2 w-2 bg-blue-400 rounded-full"></span>
                        <?php endif; ?>
                    </a>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <!-- Bottom Section -->
        <div class="border-t border-gray-700 p-4 space-y-2">
            <a href="<?php echo BASE_URL; ?>/profile" 
               class="flex items-center px-4 py-2 text-sm text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-colors duration-200">
                <i class="fas fa-user-cog w-5 text-center mr-3"></i>
                <span>Profile</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/auth/logout" 
               class="flex items-center px-4 py-2 text-sm text-red-400 hover:bg-red-900 hover:text-white rounded-lg transition-colors duration-200">
                <i class="fas fa-sign-out-alt w-5 text-center mr-3"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</div>

<!-- Sidebar Overlay (Mobile) -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden hidden"></div>

<script>
// Toggle sidebar on mobile
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const openSidebarBtn = document.getElementById('openSidebar');
    const closeSidebarBtn = document.getElementById('closeSidebar');
    
    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
    }
    
    if (openSidebarBtn) {
        openSidebarBtn.addEventListener('click', openSidebar);
    }
    
    if (closeSidebarBtn) {
        closeSidebarBtn.addEventListener('click', closeSidebar);
    }
    
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }
    
    // Toggle submenu items
    document.querySelectorAll('.menu-item-group > a').forEach(link => {
        link.addEventListener('click', function(e) {
            const submenu = this.nextElementSibling;
            if (submenu && submenu.tagName === 'UL') {
                e.preventDefault();
                const icon = this.querySelector('.fa-chevron-down');
                
                submenu.classList.toggle('hidden');
                if (icon) {
                    icon.classList.toggle('rotate-180');
                }
            }
        });
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 1024 && !sidebar.contains(e.target) && !e.target.closest('#openSidebar')) {
            closeSidebar();
        }
    });
});
</script>

