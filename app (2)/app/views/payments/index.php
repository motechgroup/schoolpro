<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Payments</h1>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search Receipt</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($filters['receipt_number'] ?? ''); ?>" 
                       placeholder="Receipt Number" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($filters['start_date'] ?? ''); ?>" 
                       class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($filters['end_date'] ?? ''); ?>" 
                       class="w-full border rounded px-3 py-2">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
    
    <!-- Payments Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admission No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Received By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($payments)): ?>
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">No payments found</td>
                </tr>
                <?php else: ?>
                <?php foreach ($payments as $payment): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="<?php echo BASE_URL; ?>/payments?receipt=<?php echo urlencode($payment['receipt_number']); ?>" 
                           class="text-blue-600 hover:text-blue-800">
                            <?php echo htmlspecialchars($payment['receipt_number']); ?>
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo displayText(($payment['student_first_name'] ?? '') . ' ' . ($payment['student_last_name'] ?? '')); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo htmlspecialchars($payment['admission_number'] ?? 'N/A'); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo formatDate($payment['payment_date']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 py-1 text-xs rounded <?php 
                            $method = strtolower($payment['payment_method'] ?? 'cash');
                            echo $method === 'mpesa' ? 'bg-yellow-100 text-yellow-800' : 
                                 ($method === 'cash' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800');
                        ?>">
                            <?php echo ucfirst($payment['payment_method'] ?? 'Cash'); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold">
                        <?php echo formatCurrency($payment['amount']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo displayText(($payment['received_by_first_name'] ?? '') . ' ' . ($payment['received_by_last_name'] ?? 'System')); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="<?php echo BASE_URL; ?>/payments?receipt=<?php echo urlencode($payment['receipt_number']); ?>" 
                           class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-receipt mr-1"></i>View Receipt
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

