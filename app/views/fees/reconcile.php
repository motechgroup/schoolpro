<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Payment Reconciliation</h1>
        <a href="<?php echo BASE_URL; ?>/feeheads" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-cog mr-2"></i>Manage Fee Heads
        </a>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                <select name="class_id" class="w-full border rounded px-3 py-2">
                    <option value="">Select Class</option>
                    <?php foreach ($classes as $class): ?>
                    <option value="<?php echo $class['id']; ?>" <?php echo ($classId == $class['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($class['grade_display_name'] . ' - ' . $class['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Term</label>
                <select name="term" class="w-full border rounded px-3 py-2">
                    <option value="1" <?php echo ($term == 1) ? 'selected' : ''; ?>>Term 1</option>
                    <option value="2" <?php echo ($term == 2) ? 'selected' : ''; ?>>Term 2</option>
                    <option value="3" <?php echo ($term == 3) ? 'selected' : ''; ?>>Term 3</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
                <input type="text" name="academic_year" value="<?php echo htmlspecialchars($academicYear); ?>" 
                       placeholder="2024/2025" class="w-full border rounded px-3 py-2">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
    
    <!-- Students List -->
    <?php if (empty($students)): ?>
    <div class="bg-white rounded-lg shadow p-6 text-center">
        <p class="text-gray-600">Please select a class to view students and their fee balances.</p>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admission No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Fees</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($students as $student): 
                    // Calculate totals from multi-term invoices
                    $totalFees = 0;
                    $paid = 0;
                    $carriedIn = 0;
                    $balance = 0;
                    
                    if (isset($student['invoices'])) {
                        foreach ($student['invoices'] as $inv) {
                            if ($inv['term'] == $term) {
                                $totalFees = $inv['total_amount'] ?? 0;
                                $carriedIn = $inv['carried_in'] ?? 0;
                                $paid = $inv['paid_amount'] ?? 0;
                                $balance = $inv['net_term_balance'] ?? $inv['balance'];
                            }
                        }
                    }
                    
                    // Determine status
                    $statusLabel = 'Pending';
                    $statusClass = 'bg-red-100 text-red-800';
                    
                    if ($balance <= 0 && $paid > 0) {
                        $statusLabel = 'Cleared';
                        $statusClass = 'bg-green-100 text-green-800';
                    } elseif ($paid > 0 && $balance > 0) {
                        $statusLabel = 'Partial';
                        $statusClass = 'bg-yellow-100 text-yellow-800';
                    } elseif ($balance < 0) {
                        $statusLabel = 'Overpaid/Credit';
                        $statusClass = 'bg-emerald-100 text-emerald-800';
                    }
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($student['admission_number']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                        <?php if ($carriedIn != 0): ?>
                            <span class="block text-xs font-normal <?php echo $carriedIn > 0 ? 'text-red-500' : 'text-green-600'; ?>">
                                <?php echo $carriedIn > 0 ? '(Includes +' . formatCurrency($carriedIn) . ' Prior Arrears)' : '(Includes -' . formatCurrency(abs($carriedIn)) . ' Prior Credit)'; ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo formatCurrency($totalFees); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                        <?php echo formatCurrency($paid); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold <?php echo $balance > 0 ? 'text-red-600' : 'text-green-600'; ?>">
                        <?php echo $balance < 0 ? formatCurrency(abs($balance)) . ' Cr' : formatCurrency($balance); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded <?php echo $statusClass; ?>">
                            <?php echo $statusLabel; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="#" onclick="showPaymentForm(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>'); return false;" 
                           class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-money-bill-wave mr-1"></i>Pay
                        </a>
                        <a href="<?php echo BASE_URL; ?>/studentfees/assign/<?php echo $student['id']; ?>?term=<?php echo $term; ?>&academic_year=<?php echo urlencode($academicYear); ?>" 
                           class="text-green-600 hover:text-green-900">
                            <i class="fas fa-edit mr-1"></i>Fees
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Record Payment</h3>
                <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="paymentFormContainer">
                <!-- Payment form will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function showPaymentForm(studentId, studentName) {
    const modal = document.getElementById('paymentModal');
    const container = document.getElementById('paymentFormContainer');
    
    container.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-2xl"></i><p class="mt-2">Loading...</p></div>';
    modal.classList.remove('hidden');
    
    const term = <?php echo $term; ?>;
    const academicYear = '<?php echo htmlspecialchars($academicYear); ?>';
    
    fetch('<?php echo BASE_URL; ?>/fees/paymentForm/' + studentId + '?term=' + term + '&academic_year=' + encodeURIComponent(academicYear))
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(error => {
            container.innerHTML = '<div class="text-red-600 p-4">Error loading payment form. Please try again.</div>';
        });
}

function closePaymentModal() {
    const modal = document.getElementById('paymentModal');
    if (modal) {
        modal.classList.add('hidden');
        // Clear the form container
        const container = document.getElementById('paymentFormContainer');
        if (container) {
            container.innerHTML = '';
        }
    }
}

// Make closePaymentModal available globally so it can be called from dynamically loaded forms
window.closePaymentModal = closePaymentModal;
</script>

