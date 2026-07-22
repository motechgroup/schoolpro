<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Fee Management</h1>
        <div class="flex space-x-2">
            <a href="<?php echo BASE_URL; ?>/feeheads" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-list mr-2"></i>Fee Heads
            </a>
            <a href="<?php echo BASE_URL; ?>/fees/reconcile" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-money-check-alt mr-2"></i>Reconcile Payments
            </a>
            <a href="<?php echo BASE_URL; ?>/reports/financial" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                <i class="fas fa-chart-bar mr-2"></i>Financial Report
            </a>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-xs font-semibold uppercase">🎓 Tuition Fees Collected</p>
                    <p class="text-2xl font-bold text-blue-700 mt-1"><?php echo formatCurrency($feeBreakdown['tuition']['collected'] ?? 0); ?></p>
                    <p class="text-xs text-gray-500 mt-1">Billed: <?php echo formatCurrency($feeBreakdown['tuition']['billed'] ?? 0); ?></p>
                </div>
                <div class="text-blue-500 text-3xl">
                    <i class="fas fa-graduation-cap"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-xs font-semibold uppercase">📋 Other Fee Heads Collected</p>
                    <p class="text-2xl font-bold text-green-700 mt-1"><?php echo formatCurrency($feeBreakdown['other']['collected'] ?? 0); ?></p>
                    <p class="text-xs text-gray-500 mt-1">Billed: <?php echo formatCurrency($feeBreakdown['other']['billed'] ?? 0); ?></p>
                </div>
                <div class="text-green-500 text-3xl">
                    <i class="fas fa-list-ul"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-xs font-semibold uppercase">Outstanding Balance</p>
                    <p class="text-2xl font-bold text-orange-600 mt-1"><?php echo formatCurrency($outstandingBalance); ?></p>
                    <p class="text-xs text-gray-500 mt-1">Pending: <?php echo number_format($pendingInvoices); ?> Invoices</p>
                </div>
                <div class="text-orange-600 text-3xl">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-xs font-semibold uppercase">Active Fee Heads</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1"><?php echo count($feeHeads); ?></p>
                    <p class="text-xs text-gray-500 mt-1">Tuition + <?php echo max(0, count($feeHeads) - 1); ?> Other Heads</p>
                </div>
                <div class="text-purple-600 text-3xl">
                    <i class="fas fa-cubes"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Invoices -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="text-xl font-bold">Recent Invoices</h2>
        </div>
        <?php if (empty($recentInvoices)): ?>
        <div class="p-6 text-center text-gray-500">
            No invoices found.
        </div>
        <?php else: ?>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($recentInvoices as $invoice): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo htmlspecialchars(($invoice['student_first_name'] ?? '') . ' ' . ($invoice['student_last_name'] ?? '')); ?>
                        <br><span class="text-xs text-gray-500"><?php echo htmlspecialchars($invoice['admission_number'] ?? ''); ?></span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">Term <?php echo $invoice['term']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo formatCurrency($invoice['total_amount']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo formatCurrency($invoice['paid_amount']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold"><?php echo formatCurrency($invoice['balance']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded <?php 
                            echo $invoice['status'] == 'paid' ? 'bg-green-100 text-green-800' : 
                                ($invoice['status'] == 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                        ?>">
                            <?php echo ucfirst($invoice['status']); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

