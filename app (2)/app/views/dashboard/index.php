<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Dashboard</h1>
        <div class="text-sm text-gray-600">
            <i class="far fa-calendar mr-2"></i><?php echo date('l, F d, Y'); ?>
        </div>
    </div>
    
    <?php if (Auth::hasAnyRole(['super_admin', 'school_admin', 'school_manager'])): ?>
    <!-- User Management Stats (Super Admin) -->
    <?php if (Auth::hasRole('super_admin') && isset($stats['users_by_role'])): ?>
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold flex items-center">
                <i class="fas fa-users-cog mr-2 text-indigo-600"></i>User Management
            </h3>
            <a href="<?php echo BASE_URL; ?>/users" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                Manage All Users <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
            <?php foreach ($stats['users_by_role'] as $roleStat): ?>
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <p class="text-2xl font-bold text-indigo-600"><?php echo number_format($roleStat['count']); ?></p>
                <p class="text-xs text-gray-600 mt-1 capitalize"><?php echo str_replace('_', ' ', $roleStat['role_name']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Main Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium mb-1">Total Students</p>
                    <p class="text-4xl font-bold"><?php echo number_format($stats['total_students'] ?? 0); ?></p>
                    <?php if (isset($stats['new_students_this_month']) && $stats['new_students_this_month'] > 0): ?>
                    <p class="text-blue-100 text-xs mt-2">
                        <i class="fas fa-arrow-up mr-1"></i><?php echo $stats['new_students_this_month']; ?> new this month
                    </p>
                    <?php endif; ?>
                </div>
                <div class="text-blue-200 text-5xl opacity-80">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium mb-1">Total Teachers</p>
                    <p class="text-4xl font-bold"><?php echo number_format($stats['total_teachers'] ?? 0); ?></p>
                </div>
                <div class="text-green-200 text-5xl opacity-80">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium mb-1">Total Classes</p>
                    <p class="text-4xl font-bold"><?php echo number_format($stats['total_classes'] ?? 0); ?></p>
                </div>
                <div class="text-purple-200 text-5xl opacity-80">
                    <i class="fas fa-school"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium mb-1">Total Parents</p>
                    <p class="text-4xl font-bold"><?php echo number_format($stats['total_parents'] ?? 0); ?></p>
                </div>
                <div class="text-orange-200 text-5xl opacity-80">
                    <i class="fas fa-user-friends"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Secondary Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium mb-1">Pending Fees</p>
                    <p class="text-3xl font-bold text-red-600"><?php echo number_format($stats['pending_fees'] ?? 0); ?></p>
                    <p class="text-gray-500 text-xs mt-1">Students with outstanding fees</p>
                </div>
                <div class="text-red-500 text-4xl">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium mb-1">Total Fees Collected</p>
                    <p class="text-2xl font-bold text-green-600"><?php echo formatCurrency($stats['total_fees_collected'] ?? 0); ?></p>
                    <?php if (isset($stats['revenue_this_month'])): ?>
                    <p class="text-gray-500 text-xs mt-1"><?php echo formatCurrency($stats['revenue_this_month']); ?> this month</p>
                    <?php endif; ?>
                </div>
                <div class="text-green-500 text-4xl">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium mb-1">Attendance Today</p>
                    <p class="text-3xl font-bold text-blue-600"><?php echo number_format($stats['attendance_today'] ?? 0); ?></p>
                    <p class="text-gray-500 text-xs mt-1">Students present</p>
                </div>
                <div class="text-blue-500 text-4xl">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium mb-1">Recent Assessments</p>
                    <p class="text-3xl font-bold text-purple-600"><?php echo number_format($stats['recent_assessments'] ?? 0); ?></p>
                    <p class="text-gray-500 text-xs mt-1">Last 7 days</p>
                </div>
                <div class="text-purple-500 text-4xl">
                    <i class="fas fa-book-open"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Detailed Information Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Students by Gender -->
        <?php if (isset($stats['students_by_gender'])): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-4 flex items-center">
                <i class="fas fa-chart-pie mr-2 text-blue-600"></i>Students by Gender
            </h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">
                            <i class="fas fa-mars text-blue-500 mr-2"></i>Male
                        </span>
                        <span class="text-lg font-bold text-blue-600"><?php echo number_format($stats['students_by_gender']['male'] ?? 0); ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: <?php 
                            $total = ($stats['students_by_gender']['male'] ?? 0) + ($stats['students_by_gender']['female'] ?? 0);
                            echo $total > 0 ? (($stats['students_by_gender']['male'] ?? 0) / $total * 100) : 0;
                        ?>%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">
                            <i class="fas fa-venus text-pink-500 mr-2"></i>Female
                        </span>
                        <span class="text-lg font-bold text-pink-600"><?php echo number_format($stats['students_by_gender']['female'] ?? 0); ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-pink-600 h-2 rounded-full" style="width: <?php 
                            $total = ($stats['students_by_gender']['male'] ?? 0) + ($stats['students_by_gender']['female'] ?? 0);
                            echo $total > 0 ? (($stats['students_by_gender']['female'] ?? 0) / $total * 100) : 0;
                        ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Students by Status -->
        <?php if (isset($stats['students_by_status'])): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-4 flex items-center">
                <i class="fas fa-chart-bar mr-2 text-green-600"></i>Students by Status
            </h3>
            <div class="space-y-3">
                <?php foreach ($stats['students_by_status'] as $status => $count): ?>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-700 capitalize"><?php echo $status; ?></span>
                    <span class="px-3 py-1 rounded-full text-sm font-bold <?php 
                        echo $status == 'active' ? 'bg-green-100 text-green-800' : 
                            ($status == 'alumni' ? 'bg-blue-100 text-blue-800' : 
                            ($status == 'transferred' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'));
                    ?>">
                        <?php echo number_format($count); ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-4 flex items-center">
                <i class="fas fa-bolt mr-2 text-yellow-600"></i>Quick Actions
            </h3>
            <div class="space-y-2">
                <?php if (Auth::hasAnyRole(['super_admin', 'school_admin', 'school_manager', 'receptionist'])): ?>
                <a href="<?php echo BASE_URL; ?>/students/create" class="block w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-center">
                    <i class="fas fa-user-plus mr-2"></i>Add New Student
                </a>
                <?php endif; ?>
                <?php if (Auth::hasAnyRole(['super_admin', 'school_admin', 'school_manager', 'head_teacher', 'teacher', 'receptionist'])): ?>
                <a href="<?php echo BASE_URL; ?>/attendance" class="block w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-center">
                    <i class="fas fa-check-circle mr-2"></i>Mark Attendance
                </a>
                <?php endif; ?>
                <?php if (Auth::hasAnyRole(['super_admin', 'school_admin', 'school_manager', 'accountant', 'bursar'])): ?>
                <a href="<?php echo BASE_URL; ?>/fees" class="block w-full bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-center">
                    <i class="fas fa-money-bill-wave mr-2"></i>Manage Fees
                </a>
                <?php endif; ?>
                <?php if (Auth::hasRole('super_admin')): ?>
                <a href="<?php echo BASE_URL; ?>/users" class="block w-full bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 text-center">
                    <i class="fas fa-users-cog mr-2"></i>Manage Users
                </a>
                <a href="<?php echo BASE_URL; ?>/users/create" class="block w-full bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600 text-center">
                    <i class="fas fa-user-plus mr-2"></i>Create New User
                </a>
                <a href="<?php echo BASE_URL; ?>/roles" class="block w-full bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-center">
                    <i class="fas fa-user-shield mr-2"></i>Manage Roles & Permissions
                </a>
                <?php endif; ?>
                <?php if (Auth::hasAnyRole(['super_admin', 'school_admin', 'school_manager'])): ?>
                <a href="<?php echo BASE_URL; ?>/parents" class="block w-full bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700 text-center">
                    <i class="fas fa-user-friends mr-2"></i>Manage Parents
                </a>
                <a href="<?php echo BASE_URL; ?>/communication" class="block w-full bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-center">
                    <i class="fas fa-sms mr-2"></i>Send SMS
                </a>
                <a href="<?php echo BASE_URL; ?>/settings" class="block w-full bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-center">
                    <i class="fas fa-cog mr-2"></i>Settings
                </a>
                <?php endif; ?>
                <?php if (Auth::hasAnyRole(['super_admin', 'school_admin', 'school_manager'])): ?>
                <a href="<?php echo BASE_URL; ?>/reports" class="block w-full bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700 text-center">
                    <i class="fas fa-chart-bar mr-2"></i>View Reports
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Revenue Summary -->
    <?php if (isset($stats['revenue_this_year'])): ?>
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h3 class="text-lg font-bold mb-4 flex items-center">
            <i class="fas fa-chart-line mr-2 text-green-600"></i>Revenue Summary
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-2">This Month</p>
                <p class="text-3xl font-bold text-green-600"><?php echo formatCurrency($stats['revenue_this_month'] ?? 0); ?></p>
            </div>
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-2">This Year</p>
                <p class="text-3xl font-bold text-blue-600"><?php echo formatCurrency($stats['revenue_this_year'] ?? 0); ?></p>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-2">All Time</p>
                <p class="text-3xl font-bold text-purple-600"><?php echo formatCurrency($stats['total_fees_collected'] ?? 0); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php elseif (Auth::hasAnyRole(['accountant', 'bursar'])): ?>
    <!-- Accountant/Bursar Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php if (isset($stats['pending_invoices'])): ?>
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium mb-1">Pending Invoices</p>
                    <p class="text-4xl font-bold"><?php echo number_format($stats['pending_invoices']); ?></p>
                </div>
                <div class="text-red-200 text-5xl opacity-80">
                    <i class="fas fa-file-invoice"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($stats['partial_payments'])): ?>
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm font-medium mb-1">Partial Payments</p>
                    <p class="text-4xl font-bold"><?php echo number_format($stats['partial_payments']); ?></p>
                </div>
                <div class="text-yellow-200 text-5xl opacity-80">
                    <i class="fas fa-money-check-alt"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($stats['total_fees_collected'])): ?>
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium mb-1">Total Collected</p>
                    <p class="text-2xl font-bold"><?php echo formatCurrency($stats['total_fees_collected']); ?></p>
                </div>
                <div class="text-green-200 text-5xl opacity-80">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($stats['revenue_this_month'])): ?>
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium mb-1">This Month</p>
                    <p class="text-2xl font-bold"><?php echo formatCurrency($stats['revenue_this_month']); ?></p>
                </div>
                <div class="text-blue-200 text-5xl opacity-80">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if (isset($stats['revenue_this_year'])): ?>
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h3 class="text-lg font-bold mb-4 flex items-center">
            <i class="fas fa-chart-line mr-2 text-green-600"></i>Revenue Summary
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-2">This Month</p>
                <p class="text-3xl font-bold text-green-600"><?php echo formatCurrency($stats['revenue_this_month'] ?? 0); ?></p>
            </div>
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-2">This Year</p>
                <p class="text-3xl font-bold text-blue-600"><?php echo formatCurrency($stats['revenue_this_year'] ?? 0); ?></p>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-2">All Time</p>
                <p class="text-3xl font-bold text-purple-600"><?php echo formatCurrency($stats['total_fees_collected'] ?? 0); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php elseif (Auth::hasAnyRole(['receptionist'])): ?>
    <!-- Receptionist Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php if (isset($stats['total_students'])): ?>
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium mb-1">Total Students</p>
                    <p class="text-4xl font-bold"><?php echo number_format($stats['total_students']); ?></p>
                </div>
                <div class="text-blue-200 text-5xl opacity-80">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($stats['total_parents'])): ?>
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium mb-1">Total Parents</p>
                    <p class="text-4xl font-bold"><?php echo number_format($stats['total_parents']); ?></p>
                </div>
                <div class="text-orange-200 text-5xl opacity-80">
                    <i class="fas fa-user-friends"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($stats['new_students_this_month'])): ?>
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium mb-1">New This Month</p>
                    <p class="text-4xl font-bold"><?php echo number_format($stats['new_students_this_month']); ?></p>
                </div>
                <div class="text-green-200 text-5xl opacity-80">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($stats['attendance_today'])): ?>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium mb-1">Attendance Today</p>
                    <p class="text-4xl font-bold"><?php echo number_format($stats['attendance_today']); ?></p>
                </div>
                <div class="text-purple-200 text-5xl opacity-80">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php else: ?>
    <!-- Other Roles Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php if (isset($stats['total_students'])): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Students</p>
                    <p class="text-3xl font-bold text-blue-600"><?php echo number_format($stats['total_students']); ?></p>
                </div>
                <div class="text-blue-600 text-4xl">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($stats['total_classes'])): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Classes</p>
                    <p class="text-3xl font-bold text-purple-600"><?php echo number_format($stats['total_classes']); ?></p>
                </div>
                <div class="text-purple-600 text-4xl">
                    <i class="fas fa-school"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($stats['attendance_today'])): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Attendance Today</p>
                    <p class="text-3xl font-bold text-green-600"><?php echo number_format($stats['attendance_today']); ?></p>
                </div>
                <div class="text-green-600 text-4xl">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($stats['recent_assessments'])): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Recent Assessments</p>
                    <p class="text-3xl font-bold text-purple-600"><?php echo number_format($stats['recent_assessments']); ?></p>
                </div>
                <div class="text-purple-600 text-4xl">
                    <i class="fas fa-book-open"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($stats['pending_invoices'])): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Pending Invoices</p>
                    <p class="text-3xl font-bold text-red-600"><?php echo number_format($stats['pending_invoices']); ?></p>
                </div>
                <div class="text-red-600 text-4xl">
                    <i class="fas fa-file-invoice"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($stats['children'])): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">My Children</p>
                    <p class="text-3xl font-bold text-blue-600"><?php echo number_format($stats['children']); ?></p>
                </div>
                <div class="text-blue-600 text-4xl">
                    <i class="fas fa-child"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Welcome Section, Announcements, and Recent Logs -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</h2>
            <p class="text-gray-600 mb-4">You are logged in as <strong><?php echo ucwords(str_replace('_', ' ', $user['role_name'])); ?></strong></p>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    Use the navigation menu to access different modules of the system.
                </p>
            </div>
        </div>
        
        <?php if (!empty($announcements)): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">Recent Announcements</h2>
                <a href="<?php echo BASE_URL; ?>/announcements" class="text-blue-600 hover:text-blue-800 text-sm">
                    View All <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="space-y-3">
                <?php foreach (array_slice($announcements, 0, 3) as $announcement): ?>
                <div class="border-l-4 border-blue-500 pl-3 py-2 hover:bg-gray-50 rounded-r">
                    <a href="<?php echo BASE_URL; ?>/announcements/show/<?php echo $announcement['id']; ?>" 
                       class="font-semibold text-gray-800 hover:text-blue-600 block">
                        <?php echo htmlspecialchars($announcement['title']); ?>
                    </a>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="far fa-clock mr-1"></i>
                        <?php echo formatDateTime($announcement['published_at'] ?? $announcement['created_at']); ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (Auth::hasAnyRole(['super_admin', 'school_manager']) && !empty($recentLogs)): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">Recent System Activity</h2>
                <a href="<?php echo BASE_URL; ?>/systemlogs" class="text-blue-600 hover:text-blue-800 text-sm">
                    View All <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="space-y-3">
                <?php foreach ($recentLogs as $log): ?>
                <div class="border-l-4 <?php 
                    echo $log['status'] === 'success' ? 'border-green-500' : 
                        ($log['status'] === 'error' ? 'border-red-500' : 'border-yellow-500'); 
                ?> pl-3 py-2 hover:bg-gray-50 rounded-r">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-800 text-sm">
                                <?php echo htmlspecialchars($log['action']); ?>
                            </p>
                            <?php if ($log['module']): ?>
                            <p class="text-xs text-gray-500 mt-1">
                                <span class="px-2 py-0.5 bg-gray-100 rounded"><?php echo htmlspecialchars($log['module']); ?></span>
                            </p>
                            <?php endif; ?>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="far fa-clock mr-1"></i>
                                <?php echo date('M d, H:i', strtotime($log['created_at'])); ?>
                            </p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded <?php 
                            echo $log['status'] === 'success' ? 'bg-green-100 text-green-800' : 
                                ($log['status'] === 'error' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); 
                        ?>">
                            <?php echo ucfirst($log['status']); ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
