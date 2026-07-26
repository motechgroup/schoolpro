<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 flex items-center">
                <i class="fas fa-file-invoice-dollar text-purple-600 mr-3"></i>Fee Report & Term Summary
            </h1>
            <p class="text-gray-500 text-sm mt-1">Executive overview of billing, fee collections, term-by-term balances, class matrices, and student fee carry-forwards.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 no-print">
            <?php 
                $queryString = $_SERVER['QUERY_STRING'] ?? '';
                $exportUrl = BASE_URL . '/feereport/exportCsv' . ($queryString ? '?' . $queryString : '');
            ?>
            <a href="<?php echo $exportUrl; ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg shadow-sm transition flex items-center">
                <i class="fas fa-file-excel mr-2"></i>Export CSV
            </a>
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg shadow-sm transition flex items-center">
                <i class="fas fa-print mr-2"></i>Print Report
            </button>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-8 no-print">
        <form method="GET" action="<?php echo BASE_URL; ?>/feereport/terms" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 items-end">
            <!-- Academic Year -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Academic Year</label>
                <select name="academic_year" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 p-2.5 font-medium">
                    <option value="all" <?php echo (empty($filters['academic_year']) || $filters['academic_year'] === 'all') ? 'selected' : ''; ?>>All Academic Years</option>
                    <?php if (!empty($academicYears)): ?>
                        <?php foreach ($academicYears as $ay): ?>
                            <option value="<?php echo htmlspecialchars($ay['name']); ?>" <?php echo ($filters['academic_year'] === $ay['name']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ay['name']); ?> <?php echo !empty($ay['is_current']) ? '(Current)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Term Filter -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Term</label>
                <select name="term" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 p-2.5 font-medium">
                    <option value="">All Terms (Term 1 - 3)</option>
                    <option value="1" <?php echo ($filters['term'] === 1) ? 'selected' : ''; ?>>Term 1</option>
                    <option value="2" <?php echo ($filters['term'] === 2) ? 'selected' : ''; ?>>Term 2</option>
                    <option value="3" <?php echo ($filters['term'] === 3) ? 'selected' : ''; ?>>Term 3</option>
                </select>
            </div>

            <!-- Class Filter -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Class Stream</label>
                <select name="class_id" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 p-2.5 font-medium">
                    <option value="">All Classes</option>
                    <?php if (!empty($classes)): ?>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo ($filters['class_id'] == $c['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['name']); ?> (<?php echo htmlspecialchars($c['grade_name'] ?? ''); ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Payment Status -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Fee Status</label>
                <select name="status" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 p-2.5 font-medium">
                    <option value="all" <?php echo ($filters['status'] === 'all') ? 'selected' : ''; ?>>All Statuses</option>
                    <option value="paid" <?php echo ($filters['status'] === 'paid') ? 'selected' : ''; ?>>Fully Paid</option>
                    <option value="partial" <?php echo ($filters['status'] === 'partial') ? 'selected' : ''; ?>>Partially Paid</option>
                    <option value="pending" <?php echo ($filters['status'] === 'pending') ? 'selected' : ''; ?>>Pending Arrears</option>
                    <option value="overpaid" <?php echo ($filters['status'] === 'overpaid') ? 'selected' : ''; ?>>Overpaid / Credit</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="flex space-x-2">
                <button type="submit" class="w-full bg-purple-700 hover:bg-purple-800 text-white font-semibold text-sm p-2.5 rounded-lg shadow transition flex items-center justify-center">
                    <i class="fas fa-filter mr-1.5"></i>Filter
                </button>
                <a href="<?php echo BASE_URL; ?>/feereport/terms" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold text-sm p-2.5 rounded-lg transition flex items-center justify-center" title="Reset Filters">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Executive Stat Cards (Overall Year Summary) -->
    <?php $ov = $summaryMetrics['overall']; ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Billed -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 border-l-4 border-l-purple-600">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Year Billed</p>
                    <h3 class="text-2xl font-extrabold text-purple-900 mt-1"><?php echo formatCurrency($ov['billed']); ?></h3>
                </div>
                <div class="p-3 bg-purple-50 text-purple-600 rounded-lg">
                    <i class="fas fa-file-invoice text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-3"><i class="fas fa-receipt mr-1 text-purple-500"></i><?php echo number_format($ov['invoices']); ?> Invoices Issued</p>
        </div>

        <!-- Total Collected -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 border-l-4 border-l-emerald-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Fees Collected</p>
                    <h3 class="text-2xl font-extrabold text-emerald-700 mt-1"><?php echo formatCurrency($ov['paid']); ?></h3>
                </div>
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg">
                    <i class="fas fa-hand-holding-usd text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-3"><i class="fas fa-check-circle mr-1 text-emerald-500"></i>Reconciled & Paid</p>
        </div>

        <!-- Outstanding Arrears -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 border-l-4 border-l-rose-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Outstanding Balance</p>
                    <h3 class="text-2xl font-extrabold text-rose-700 mt-1"><?php echo formatCurrency($ov['balance']); ?></h3>
                </div>
                <div class="p-3 bg-rose-50 text-rose-600 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-3"><i class="fas fa-clock mr-1 text-rose-500"></i>Pending Arrears Carried Forward</p>
        </div>

        <!-- Overall Collection Rate -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 border-l-4 border-l-blue-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Collection Rate</p>
                    <h3 class="text-2xl font-extrabold text-blue-900 mt-1"><?php echo $ov['rate']; ?>%</h3>
                </div>
                <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                    <i class="fas fa-chart-pie text-xl"></i>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-3 overflow-hidden">
                <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo min(100, $ov['rate']); ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Term-by-Term Comparison Section -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-calendar-alt text-purple-600 mr-2"></i>Term-by-Term Collection Performance
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ([1, 2, 3] as $tNum): 
                $tm = $summaryMetrics['terms'][$tNum] ?? ['billed' => 0, 'paid' => 0, 'balance' => 0, 'rate' => 0, 'invoices' => 0];
                $tColors = [
                    1 => ['border' => 'border-blue-500', 'bg' => 'bg-blue-50', 'text' => 'text-blue-800', 'bar' => 'from-blue-500 to-indigo-600'],
                    2 => ['border' => 'border-emerald-500', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-800', 'bar' => 'from-emerald-500 to-teal-600'],
                    3 => ['border' => 'border-indigo-500', 'bg' => 'bg-indigo-50', 'text' => 'text-indigo-800', 'bar' => 'from-indigo-500 to-purple-600']
                ];
                $c = $tColors[$tNum];
            ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 border-t-4 <?php echo $c['border']; ?>">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Term <?php echo $tNum; ?></h3>
                    <span class="px-2.5 py-1 text-xs font-bold rounded-full <?php echo $c['bg']; ?> <?php echo $c['text']; ?>">
                        <?php echo $tm['rate']; ?>% Collected
                    </span>
                </div>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Total Billed:</span>
                        <strong class="text-gray-900"><?php echo formatCurrency($tm['billed']); ?></strong>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Total Collected:</span>
                        <strong class="text-emerald-600"><?php echo formatCurrency($tm['paid']); ?></strong>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Outstanding Balance:</span>
                        <strong class="text-rose-600"><?php echo formatCurrency($tm['balance']); ?></strong>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100">
                    <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-gradient-to-r <?php echo $c['bar']; ?> h-2.5 rounded-full transition-all duration-500" style="width: <?php echo min(100, $tm['rate']); ?>%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-right"><?php echo number_format($tm['invoices']); ?> Term Invoices</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Class-Wise Term Breakdown Matrix Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8 overflow-hidden">
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-layer-group text-blue-600 mr-2"></i>Class-Wise Term Breakdown Matrix
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Summary of fee billing and collections per class across Term 1, Term 2, and Term 3</p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 bg-blue-100 text-blue-800 rounded-full">
                <?php echo count($classBreakdown); ?> Active Classes
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100 text-gray-700 text-xs font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Class / Stream</th>
                        <th class="px-3 py-3 text-center">Students</th>
                        <th class="px-4 py-3 text-right text-blue-700">T1 Paid / Billed</th>
                        <th class="px-4 py-3 text-right text-emerald-700">T2 Paid / Billed</th>
                        <th class="px-4 py-3 text-right text-indigo-700">T3 Paid / Billed</th>
                        <th class="px-4 py-3 text-right text-purple-900">Total Billed</th>
                        <th class="px-4 py-3 text-right text-emerald-700">Total Paid</th>
                        <th class="px-4 py-3 text-right text-rose-700">Net Arrears</th>
                        <th class="px-3 py-3 text-center">Rate</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                    <?php if (!empty($classBreakdown)): ?>
                        <?php foreach ($classBreakdown as $cRow): ?>
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-4 py-3.5 font-bold text-gray-900">
                                <?php echo htmlspecialchars($cRow['class_name']); ?>
                                <span class="block text-xs font-normal text-gray-500"><?php echo htmlspecialchars($cRow['grade_name'] ?? ''); ?></span>
                            </td>
                            <td class="px-3 py-3.5 text-center font-semibold text-gray-700">
                                <?php echo $cRow['student_count']; ?>
                            </td>
                            <td class="px-4 py-3.5 text-right font-medium text-xs">
                                <span class="text-emerald-600 font-bold"><?php echo formatCurrency($cRow['term_1']['paid']); ?></span> / 
                                <span class="text-gray-500"><?php echo formatCurrency($cRow['term_1']['billed']); ?></span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-medium text-xs">
                                <span class="text-emerald-600 font-bold"><?php echo formatCurrency($cRow['term_2']['paid']); ?></span> / 
                                <span class="text-gray-500"><?php echo formatCurrency($cRow['term_2']['billed']); ?></span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-medium text-xs">
                                <span class="text-emerald-600 font-bold"><?php echo formatCurrency($cRow['term_3']['paid']); ?></span> / 
                                <span class="text-gray-500"><?php echo formatCurrency($cRow['term_3']['billed']); ?></span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-bold text-gray-800">
                                <?php echo formatCurrency($cRow['total_billed']); ?>
                            </td>
                            <td class="px-4 py-3.5 text-right font-bold text-emerald-600">
                                <?php echo formatCurrency($cRow['total_paid']); ?>
                            </td>
                            <td class="px-4 py-3.5 text-right font-bold text-rose-600">
                                <?php echo formatCurrency($cRow['total_balance']); ?>
                            </td>
                            <td class="px-3 py-3.5 text-center">
                                <span class="px-2 py-0.5 text-xs font-extrabold rounded-full <?php echo $cRow['collection_rate'] >= 80 ? 'bg-emerald-100 text-emerald-800' : ($cRow['collection_rate'] >= 40 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800'); ?>">
                                    <?php echo $cRow['collection_rate']; ?>%
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-gray-500">No active classes found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Fee Heads Collection Analysis by Term -->
    <?php if (!empty($feeHeadBreakdown)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8 overflow-hidden">
        <div class="p-5 border-b border-gray-200 bg-gray-50">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-th-list text-purple-600 mr-2"></i>Fee Heads Breakdown by Term
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">Detailed collection breakdown for Tuition and itemized Fee Heads across Term 1, 2, and 3</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100 text-gray-700 text-xs font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Fee Head</th>
                        <th class="px-4 py-3 text-left">Category</th>
                        <th class="px-4 py-3 text-right text-blue-700">Term 1 Collected</th>
                        <th class="px-4 py-3 text-right text-emerald-700">Term 2 Collected</th>
                        <th class="px-4 py-3 text-right text-indigo-700">Term 3 Collected</th>
                        <th class="px-4 py-3 text-right text-purple-900">Total Billed</th>
                        <th class="px-4 py-3 text-right text-emerald-700">Total Collected</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                    <?php foreach ($feeHeadBreakdown as $fh): ?>
                    <tr class="<?php echo $fh['is_tuition'] ? 'bg-blue-50/60 font-semibold' : 'hover:bg-gray-50'; ?>">
                        <td class="px-4 py-3 text-gray-900 font-bold">
                            <?php if ($fh['is_tuition']): ?>
                                <span class="text-blue-800"><i class="fas fa-graduation-cap mr-1 text-blue-600"></i><?php echo htmlspecialchars($fh['fee_head_name']); ?></span>
                            <?php else: ?>
                                <span><i class="fas fa-clipboard-list mr-1 text-gray-400"></i><?php echo htmlspecialchars($fh['fee_head_name']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            <span class="px-2 py-0.5 rounded font-bold <?php echo $fh['is_tuition'] ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700'; ?>">
                                <?php echo $fh['is_tuition'] ? 'Tuition Fee' : 'Other Fee Head'; ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-blue-700 font-medium"><?php echo formatCurrency($fh['t1']['collected']); ?></td>
                        <td class="px-4 py-3 text-right text-emerald-700 font-medium"><?php echo formatCurrency($fh['t2']['collected']); ?></td>
                        <td class="px-4 py-3 text-right text-indigo-700 font-medium"><?php echo formatCurrency($fh['t3']['collected']); ?></td>
                        <td class="px-4 py-3 text-right font-bold text-gray-800"><?php echo formatCurrency($fh['total_billed']); ?></td>
                        <td class="px-4 py-3 text-right font-bold text-emerald-600"><?php echo formatCurrency($fh['total_collected']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Student Term Fee Balance Report Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-user-graduate text-purple-600 mr-2"></i>Student Term Fee Balances
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Detailed student list showing term-by-term balances and net cumulative carry-forwards</p>
            </div>
            
            <!-- Search bar -->
            <form method="GET" action="<?php echo BASE_URL; ?>/feereport/terms" class="flex items-center space-x-2 no-print">
                <?php foreach ($filters as $k => $v): if ($k !== 'search' && !empty($v)): ?>
                    <input type="hidden" name="<?php echo htmlspecialchars($k); ?>" value="<?php echo htmlspecialchars($v); ?>">
                <?php endif; endforeach; ?>
                <div class="relative">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" placeholder="Search student name/adm..." 
                           class="bg-white border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-purple-500 focus:border-purple-500 pl-8 pr-3 py-2 w-56">
                    <i class="fas fa-search absolute left-2.5 top-2.5 text-gray-400 text-xs"></i>
                </div>
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold px-3 py-2 rounded-lg">Search</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100 text-gray-700 text-xs font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Adm No</th>
                        <th class="px-4 py-3 text-left">Student Name</th>
                        <th class="px-4 py-3 text-left">Class</th>
                        <th class="px-4 py-3 text-right text-blue-700">Term 1 (Paid / Billed)</th>
                        <th class="px-4 py-3 text-right text-emerald-700">Term 2 (Paid / Billed)</th>
                        <th class="px-4 py-3 text-right text-indigo-700">Term 3 (Paid / Billed)</th>
                        <th class="px-4 py-3 text-right text-purple-900">Net Cumulative Balance</th>
                        <th class="px-3 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center no-print">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                    <?php if (!empty($studentList)): ?>
                        <?php foreach ($studentList as $s): ?>
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-4 py-3.5 font-mono text-xs font-bold text-purple-700">
                                <?php echo htmlspecialchars($s['admission_number']); ?>
                            </td>
                            <td class="px-4 py-3.5 font-bold text-gray-900">
                                <?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?>
                            </td>
                            <td class="px-4 py-3.5 text-gray-600 text-xs font-medium">
                                <?php echo htmlspecialchars($s['class_name'] ?? 'Unassigned'); ?>
                            </td>
                            <td class="px-4 py-3.5 text-right text-xs">
                                <span class="text-emerald-600 font-bold"><?php echo formatCurrency($s['t1']['paid']); ?></span> / 
                                <span class="text-gray-500"><?php echo formatCurrency($s['t1']['billed']); ?></span>
                            </td>
                            <td class="px-4 py-3.5 text-right text-xs">
                                <span class="text-emerald-600 font-bold"><?php echo formatCurrency($s['t2']['paid']); ?></span> / 
                                <span class="text-gray-500"><?php echo formatCurrency($s['t2']['billed']); ?></span>
                            </td>
                            <td class="px-4 py-3.5 text-right text-xs">
                                <span class="text-emerald-600 font-bold"><?php echo formatCurrency($s['t3']['paid']); ?></span> / 
                                <span class="text-gray-500"><?php echo formatCurrency($s['t3']['billed']); ?></span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-extrabold text-sm">
                                <?php if ($s['net_balance'] > 0): ?>
                                    <span class="text-rose-600"><?php echo formatCurrency($s['net_balance']); ?></span>
                                <?php elseif ($s['net_balance'] < 0): ?>
                                    <span class="text-blue-600"><?php echo formatCurrency($s['net_balance']); ?> (CR)</span>
                                <?php else: ?>
                                    <span class="text-emerald-600">KES 0.00</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-3.5 text-center">
                                <?php
                                    $statusPill = [
                                        'paid' => 'bg-emerald-100 text-emerald-800',
                                        'partial' => 'bg-amber-100 text-amber-800',
                                        'pending' => 'bg-rose-100 text-rose-800',
                                        'overpaid' => 'bg-blue-100 text-blue-800'
                                    ][$s['fee_status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-2.5 py-1 text-xs font-bold uppercase rounded-full <?php echo $statusPill; ?>">
                                    <?php echo htmlspecialchars($s['fee_status']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center no-print">
                                <a href="<?php echo BASE_URL; ?>/feereport/student/<?php echo $s['student_id']; ?>" 
                                   class="inline-flex items-center text-xs font-bold text-purple-700 hover:text-purple-900 bg-purple-50 hover:bg-purple-100 px-2.5 py-1.5 rounded-lg border border-purple-200 transition">
                                    <i class="fas fa-file-alt mr-1"></i>View Fee Report
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                <p class="font-semibold">No student records found matching the current filters.</p>
                                <p class="text-xs text-gray-400 mt-1">Try resetting the filters or searching for a different name/admission number.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    nav, .no-print, header, #sidebar, #sidebarOverlay { display: none !important; }
    body { background-color: #fff !important; color: #000 !important; }
    .container { max-width: 100% !important; width: 100% !important; padding: 0 !important; }
    .shadow-sm, .shadow { box-shadow: none !important; }
    .border { border-color: #ddd !important; }
}
</style>
