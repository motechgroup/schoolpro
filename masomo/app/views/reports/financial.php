<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Financial Report</h1>
        <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-print mr-2"></i>Print
        </button>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                <button type="submit" class="w-full bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-filter mr-2"></i>Generate Report
                </button>
            </div>
        </form>
    </div>
    
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm mb-2">Total Invoices</p>
            <p class="text-3xl font-bold text-blue-600"><?php echo number_format($summary['total_invoices'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm mb-2">Total Billed</p>
            <p class="text-3xl font-bold text-purple-600"><?php echo formatCurrency($summary['total_billed'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm mb-2">Total Paid</p>
            <p class="text-3xl font-bold text-green-600"><?php echo formatCurrency($summary['total_paid'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm mb-2">Outstanding Balance</p>
            <p class="text-3xl font-bold text-red-600"><?php echo formatCurrency($summary['total_balance'] ?? 0); ?></p>
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

