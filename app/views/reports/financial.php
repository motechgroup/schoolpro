<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Financial Report</h1>
        <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-print mr-2"></i>Print
        </button>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
                <select name="academic_year" class="w-full border rounded px-3 py-2">
                    <option value="">All Academic Years</option>
                    <?php if (!empty($academicYears)): ?>
                    <?php foreach ($academicYears as $ay): ?>
                        <option value="<?php echo htmlspecialchars($ay['name']); ?>" <?php echo ($filters['academic_year'] == $ay['name']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ay['name']); ?> <?php echo $ay['is_current'] ? '(Current)' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($filters['start_date']); ?>" 
                       class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($filters['end_date']); ?>" 
                       class="w-full border rounded px-3 py-2">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 font-medium">
                    <i class="fas fa-filter mr-2"></i>Generate Report
                </button>
            </div>
        </form>
    </div>
    
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
            <p class="text-gray-600 text-xs font-semibold uppercase mb-1">Total Year Billed</p>
            <p class="text-2xl font-bold text-purple-700"><?php echo formatCurrency($summary['total_billed'] ?? 0); ?></p>
            <p class="text-xs text-gray-500 mt-1"><?php echo number_format($summary['total_invoices'] ?? 0); ?> Invoices Issued</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <p class="text-gray-600 text-xs font-semibold uppercase mb-1">🎓 Tuition Fees Collected</p>
            <p class="text-2xl font-bold text-blue-700"><?php echo formatCurrency($feeBreakdown['tuition']['collected'] ?? 0); ?></p>
            <p class="text-xs text-gray-500 mt-1">Billed: <?php echo formatCurrency($feeBreakdown['tuition']['billed'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <p class="text-gray-600 text-xs font-semibold uppercase mb-1">📋 Other Fee Heads Collected</p>
            <p class="text-2xl font-bold text-green-700"><?php echo formatCurrency($feeBreakdown['other']['collected'] ?? 0); ?></p>
            <p class="text-xs text-gray-500 mt-1">Billed: <?php echo formatCurrency($feeBreakdown['other']['billed'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
            <p class="text-gray-600 text-xs font-semibold uppercase mb-1">Outstanding Balance</p>
            <p class="text-2xl font-bold text-red-700"><?php echo formatCurrency($summary['total_balance'] ?? 0); ?></p>
            <p class="text-xs text-gray-500 mt-1">Total Paid: <?php echo formatCurrency($summary['total_paid'] ?? 0); ?></p>
        </div>
    </div>

    <!-- Fee Heads Breakdown Section -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-4 flex items-center">
            <i class="fas fa-layer-group text-blue-600 mr-2"></i>Tuition vs Other Fee Heads Collection Analysis
        </h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fee Head / Category</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Billed</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Collected</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Outstanding Balance</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Collection Rate</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($feeBreakdown['headBreakdown'])): ?>
                    <?php foreach ($feeBreakdown['headBreakdown'] as $head): ?>
                    <?php 
                        $rate = ($head['total_billed'] > 0) ? round(($head['total_collected'] / $head['total_billed']) * 100, 1) : 0;
                    ?>
                    <tr class="<?php echo $head['is_tuition'] ? 'bg-blue-50/50 font-medium' : ''; ?>">
                        <td class="px-4 py-3 text-sm">
                            <?php if ($head['is_tuition']): ?>
                                <span class="font-bold text-blue-800">🎓 <?php echo htmlspecialchars($head['fee_head_name']); ?></span>
                            <?php else: ?>
                                <span>📋 <?php echo htmlspecialchars($head['fee_head_name']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-0.5 text-xs rounded font-semibold <?php echo $head['is_tuition'] ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700'; ?>">
                                <?php echo $head['is_tuition'] ? 'Tuition Fee' : 'Other Fee Head'; ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-right"><?php echo formatCurrency($head['total_billed']); ?></td>
                        <td class="px-4 py-3 text-sm text-right text-green-600 font-semibold"><?php echo formatCurrency($head['total_collected']); ?></td>
                        <td class="px-4 py-3 text-sm text-right text-red-600 font-semibold"><?php echo formatCurrency($head['balance']); ?></td>
                        <td class="px-4 py-3 text-center text-sm font-semibold">
                            <span class="px-2 py-0.5 text-xs rounded <?php echo $rate >= 80 ? 'bg-green-100 text-green-800' : ($rate >= 40 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                <?php echo $rate; ?>%
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Recent Payments -->
    <?php if (!empty($recentPayments)): ?>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="text-xl font-bold">Recent Payments</h2>
            <p class="text-sm text-gray-600">
                Period: <?php echo formatDate($filters['start_date']); ?> to <?php echo formatDate($filters['end_date']); ?>
            </p>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($recentPayments as $payment): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($payment['receipt_number'] ?? 'N/A'); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo htmlspecialchars(($payment['student_first_name'] ?? '') . ' ' . ($payment['student_last_name'] ?? '')); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo formatDate($payment['payment_date']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">
                            <?php echo ucfirst($payment['payment_method']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold">
                        <?php echo formatCurrency($payment['amount']); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-lg shadow p-6 text-center">
        <p class="text-gray-600">No payments found for the selected period.</p>
    </div>
    <?php endif; ?>
</div>

<style>
@media print {
    nav, .no-print { display: none; }
    body { margin: 0; }
}
</style>

