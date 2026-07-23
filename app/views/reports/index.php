<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Reports & Executive Analytics</h1>
            <p class="text-gray-500 text-sm mt-1">Real-time school performance metrics, fee collection summaries, and student statistics</p>
        </div>
        <form method="GET" action="<?php echo BASE_URL; ?>/reports" class="flex items-center">
            <select name="academic_year" onchange="this.form.submit()" class="bg-blue-50 text-blue-800 px-3 py-2 rounded-lg border border-blue-200 text-sm font-semibold cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                <option value="">All Academic Years</option>
                <?php if (!empty($academicYears)): ?>
                    <?php foreach ($academicYears as $ay): ?>
                        <option value="<?php echo htmlspecialchars($ay['name']); ?>" <?php echo ($selectedYear === $ay['name']) ? 'selected' : ''; ?>>
                            Academic Year: <?php echo htmlspecialchars($ay['name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </form>
    </div>
    
    <!-- Report Navigation Header Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <a href="<?php echo BASE_URL; ?>/reports/students" class="bg-white rounded-xl shadow-sm hover:shadow-md border border-gray-100 p-6 transition duration-200 group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-xl group-hover:scale-110 transition duration-200">
                    <i class="fas fa-users"></i>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full">Demographics</span>
            </div>
            <h2 class="text-xl font-bold text-gray-800 group-hover:text-blue-600 transition">Student Report</h2>
            <p class="text-gray-500 text-sm mt-1">Filter, view, and export detailed student profiles and class distribution.</p>
            <div class="mt-4 text-xs font-semibold text-blue-600 flex items-center">
                Generate Report <i class="fas fa-arrow-right ml-1 group-hover:translate-x-1 transition"></i>
            </div>
        </a>
        
        <a href="<?php echo BASE_URL; ?>/reports/attendance" class="bg-white rounded-xl shadow-sm hover:shadow-md border border-gray-100 p-6 transition duration-200 group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-lg bg-green-100 text-green-600 flex items-center justify-center text-xl group-hover:scale-110 transition duration-200">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-green-50 text-green-700 rounded-full">Daily Tracking</span>
            </div>
            <h2 class="text-xl font-bold text-gray-800 group-hover:text-green-600 transition">Attendance Report</h2>
            <p class="text-gray-500 text-sm mt-1">Monitor class attendance rates, present/absent trends, and monthly summaries.</p>
            <div class="mt-4 text-xs font-semibold text-green-600 flex items-center">
                Generate Report <i class="fas fa-arrow-right ml-1 group-hover:translate-x-1 transition"></i>
            </div>
        </a>
        
        <?php if (Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar', 'accountant'])): ?>
        <a href="<?php echo BASE_URL; ?>/reports/financial" class="bg-white rounded-xl shadow-sm hover:shadow-md border border-gray-100 p-6 transition duration-200 group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-xl group-hover:scale-110 transition duration-200">
                    <i class="fas fa-chart-line"></i>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-purple-50 text-purple-700 rounded-full">Financials</span>
            </div>
            <h2 class="text-xl font-bold text-gray-800 group-hover:text-purple-600 transition">Financial Report</h2>
            <p class="text-gray-500 text-sm mt-1">Audit fee collections, tuition vs other fee heads, and balance reconciliations.</p>
            <div class="mt-4 text-xs font-semibold text-purple-600 flex items-center">
                Generate Report <i class="fas fa-arrow-right ml-1 group-hover:translate-x-1 transition"></i>
            </div>
        </a>
        <?php endif; ?>
    </div>
    
    <!-- Stat Cards Section Header -->
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-gray-800 flex items-center">
            <i class="fas fa-tachometer-alt text-blue-600 mr-2"></i>Live System Key Performance Indicators
        </h2>
        <span class="text-xs text-gray-400">Updated just now</span>
    </div>

    <!-- Executive Stat Cards (4 Columns) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Card 1: Students -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:border-blue-300 transition border-l-4 border-l-blue-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Enrolled</p>
                    <h3 class="text-2xl font-extrabold text-gray-900 mt-1"><?php echo number_format($totalStudents); ?> <span class="text-sm font-normal text-gray-500">Students</span></h3>
                </div>
                <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                    <i class="fas fa-user-graduate text-xl"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center text-xs text-gray-500">
                <span class="text-blue-600 font-semibold mr-2"><i class="fas fa-mars mr-1"></i><?php echo $maleCount; ?> Male</span>
                <span>•</span>
                <span class="text-pink-600 font-semibold ml-2"><i class="fas fa-venus mr-1"></i><?php echo $femaleCount; ?> Female</span>
            </div>
        </div>

        <!-- Card 2: Tuition Collections -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:border-indigo-300 transition border-l-4 border-l-indigo-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">🎓 Tuition Fees Collected</p>
                    <h3 class="text-2xl font-extrabold text-indigo-700 mt-1"><?php echo formatCurrency($feeBreakdown['tuition']['collected'] ?? 0); ?></h3>
                </div>
                <div class="p-3 bg-indigo-50 text-indigo-600 rounded-lg">
                    <i class="fas fa-graduation-cap text-xl"></i>
                </div>
            </div>
            <div class="mt-3 text-xs text-gray-500">
                Billed: <span class="font-semibold text-gray-700"><?php echo formatCurrency($feeBreakdown['tuition']['billed'] ?? 0); ?></span>
            </div>
        </div>

        <!-- Card 3: Other Fee Heads Collections -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:border-emerald-300 transition border-l-4 border-l-emerald-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">📋 Other Fee Heads</p>
                    <h3 class="text-2xl font-extrabold text-emerald-700 mt-1"><?php echo formatCurrency($feeBreakdown['other']['collected'] ?? 0); ?></h3>
                </div>
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg">
                    <i class="fas fa-bus-alt text-xl"></i>
                </div>
            </div>
            <div class="mt-3 text-xs text-gray-500">
                Billed: <span class="font-semibold text-gray-700"><?php echo formatCurrency($feeBreakdown['other']['billed'] ?? 0); ?></span>
            </div>
        </div>

        <!-- Card 4: Attendance Rate -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:border-amber-300 transition border-l-4 border-l-amber-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Attendance Rate</p>
                    <h3 class="text-2xl font-extrabold text-gray-900 mt-1"><?php echo $attendanceRate; ?>%</h3>
                </div>
                <div class="p-3 bg-amber-50 text-amber-600 rounded-lg">
                    <i class="fas fa-chart-pie text-xl"></i>
                </div>
            </div>
            <div class="mt-3 text-xs text-gray-500">
                Active Classes: <span class="font-semibold text-gray-700"><?php echo number_format($classCount); ?> Streams</span>
            </div>
        </div>
    </div>

    <!-- Analytics Dashboard Grid (2 Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Financial Summary Overview -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800 flex items-center">
                    <i class="fas fa-coins text-purple-600 mr-2"></i>Fee Collections & Billing Summary
                </h3>
                <a href="<?php echo BASE_URL; ?>/reports/financial" class="text-xs font-semibold text-purple-600 hover:text-purple-800">
                    Full Financial Report <i class="fas fa-chevron-right ml-1"></i>
                </a>
            </div>

            <?php 
                $totalBilled = $feeBreakdown['total']['billed'] ?? 1;
                $totalCollected = $feeBreakdown['total']['collected'] ?? 0;
                $totalBalance = $feeBreakdown['total']['balance'] ?? 0;
                $collPercent = ($totalBilled > 0) ? min(100, round(($totalCollected / $totalBilled) * 100, 1)) : 0;
            ?>

            <!-- Collection Progress Bar -->
            <div class="mb-6">
                <div class="flex justify-between text-sm font-semibold mb-2">
                    <span class="text-gray-700">Overall Collection Progress</span>
                    <span class="text-purple-700"><?php echo $collPercent; ?>% Collected</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden flex">
                    <div class="bg-gradient-to-r from-purple-500 to-indigo-600 h-3 rounded-full transition-all duration-500" style="width: <?php echo $collPercent; ?>%"></div>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-2">
                    <span>Paid: <strong><?php echo formatCurrency($totalCollected); ?></strong></span>
                    <span>Outstanding: <strong class="text-red-600"><?php echo formatCurrency($totalBalance); ?></strong></span>
                </div>
            </div>

            <!-- Breakdown Mini-Table -->
            <div class="border rounded-lg overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-2">Category</th>
                            <th class="px-4 py-2 text-right">Billed</th>
                            <th class="px-4 py-2 text-right">Collected</th>
                            <th class="px-4 py-2 text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="px-4 py-2.5 font-medium text-indigo-700 flex items-center">
                                🎓 Tuition Fees
                            </td>
                            <td class="px-4 py-2.5 text-right"><?php echo formatCurrency($feeBreakdown['tuition']['billed']); ?></td>
                            <td class="px-4 py-2.5 text-right text-emerald-600 font-semibold"><?php echo formatCurrency($feeBreakdown['tuition']['collected']); ?></td>
                            <td class="px-4 py-2.5 text-right text-red-600"><?php echo formatCurrency($feeBreakdown['tuition']['balance']); ?></td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5 font-medium text-emerald-700 flex items-center">
                                📋 Other Fee Heads
                            </td>
                            <td class="px-4 py-2.5 text-right"><?php echo formatCurrency($feeBreakdown['other']['billed']); ?></td>
                            <td class="px-4 py-2.5 text-right text-emerald-600 font-semibold"><?php echo formatCurrency($feeBreakdown['other']['collected']); ?></td>
                            <td class="px-4 py-2.5 text-right text-red-600"><?php echo formatCurrency($feeBreakdown['other']['balance']); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Student & Class Demographics -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800 flex items-center">
                    <i class="fas fa-layer-group text-blue-600 mr-2"></i>Demographics & Class Structure
                </h3>
                <a href="<?php echo BASE_URL; ?>/reports/students" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                    Full Student Report <i class="fas fa-chevron-right ml-1"></i>
                </a>
            </div>

            <!-- Gender Distribution -->
            <div class="mb-6">
                <div class="flex justify-between text-sm font-semibold mb-2">
                    <span class="text-gray-700">Gender Distribution</span>
                    <span class="text-gray-500"><?php echo $maleCount; ?> Boys / <?php echo $femaleCount; ?> Girls</span>
                </div>
                <?php 
                    $malePercent = ($totalStudents > 0) ? round(($maleCount / $totalStudents) * 100) : 50;
                    $femalePercent = 100 - $malePercent;
                ?>
                <div class="w-full bg-pink-100 rounded-full h-3 overflow-hidden flex">
                    <div class="bg-blue-500 h-3 transition-all duration-500" style="width: <?php echo $malePercent; ?>%"></div>
                    <div class="bg-pink-500 h-3 transition-all duration-500" style="width: <?php echo $femalePercent; ?>%"></div>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-2">
                    <span class="text-blue-600 font-semibold"><i class="fas fa-circle mr-1"></i>Boys (<?php echo $malePercent; ?>%)</span>
                    <span class="text-pink-600 font-semibold"><i class="fas fa-circle mr-1"></i>Girls (<?php echo $femalePercent; ?>%)</span>
                </div>
            </div>

            <!-- Quick Action Cards -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50/50 p-4 rounded-lg border border-blue-100">
                    <p class="text-xs text-blue-700 font-semibold">Active Classes</p>
                    <p class="text-xl font-bold text-blue-900 mt-1"><?php echo $classCount; ?> Streams</p>
                    <p class="text-xs text-gray-500 mt-1">Playgroup to Grade 9</p>
                </div>
                <div class="bg-green-50/50 p-4 rounded-lg border border-green-100">
                    <p class="text-xs text-green-700 font-semibold">Today's Attendance</p>
                    <p class="text-xl font-bold text-green-900 mt-1"><?php echo $attendanceRate; ?>%</p>
                    <p class="text-xs text-gray-500 mt-1">High Presence Rate</p>
                </div>
            </div>
        </div>
    </div>
</div>

