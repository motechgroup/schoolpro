<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Reports</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="<?php echo BASE_URL; ?>/reports/students" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <div class="text-blue-600 text-4xl mb-4">
                <i class="fas fa-users"></i>
            </div>
            <h2 class="text-xl font-bold mb-2">Student Report</h2>
            <p class="text-gray-600">View and export student information</p>
        </a>
        
        <a href="<?php echo BASE_URL; ?>/reports/attendance" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <div class="text-green-600 text-4xl mb-4">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="text-xl font-bold mb-2">Attendance Report</h2>
            <p class="text-gray-600">View attendance summaries and statistics</p>
        </a>
        
        <?php if (Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar', 'accountant'])): ?>
        <a href="<?php echo BASE_URL; ?>/reports/financial" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <div class="text-purple-600 text-4xl mb-4">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <h2 class="text-xl font-bold mb-2">Financial Report</h2>
            <p class="text-gray-600">View fees, payments, and financial summaries</p>
        </a>
        <?php endif; ?>
    </div>
</div>

