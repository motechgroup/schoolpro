<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">M-Pesa Transactions</h1>
            <p class="text-gray-600 mt-1">View and reconcile M-Pesa payments received via PayBill or STK Push</p>
        </div>
        <div class="flex space-x-2">
            <a href="<?php echo BASE_URL; ?>/settings" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                <i class="fas fa-cog mr-2"></i>Settings
            </a>
        </div>
    </div>
    
    <!-- Date Filter and Get Transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <!-- Date Filter -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold mb-3">Filter Transactions</h3>
            <form id="dateFilterForm" method="GET" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" 
                           value="<?php echo htmlspecialchars($start_date); ?>"
                           class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" 
                           value="<?php echo htmlspecialchars($end_date); ?>"
                           class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <button type="submit" class="w-full bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Get Transactions -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold mb-3">Fetch PayBill Transactions</h3>
            <p class="text-sm text-gray-600 mb-3">Process and match PayBill transactions. Updates pending transactions and matches them to students based on account reference (admission number).</p>
            <div class="bg-yellow-50 border border-yellow-200 rounded p-2 mb-3">
                <p class="text-xs text-yellow-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Note:</strong> For automatic PayBill transaction capture, configure webhooks in your M-Pesa Business Portal. The callback URL should be: <code class="text-xs"><?php echo BASE_URL; ?>/mpesa/callback</code>
                </p>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date Range to Process</label>
                    <select id="fetchDays" class="w-full border rounded px-3 py-2">
                        <option value="1">Last 24 Hours</option>
                        <option value="3">Last 3 Days</option>
                        <option value="7" selected>Last 7 Days</option>
                        <option value="14">Last 14 Days</option>
                        <option value="30">Last 30 Days</option>
                    </select>
                </div>
                <div>
                    <button type="button" id="getTransactionsBtn" class="w-full bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                        <i class="fas fa-sync mr-2"></i>Get Transactions
                    </button>
                </div>
                <div id="fetchStatus" class="hidden text-sm"></div>
            </div>
        </div>
    </div>
    
    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold">Transactions</h2>
            <p class="text-sm text-gray-600 mt-1"><?php echo count($transactions); ?> transaction(s) found</p>
        </div>
        
        <?php if (empty($transactions)): ?>
        <div class="p-8 text-center">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-600">No M-Pesa transactions found for the selected date range.</p>
            <p class="text-sm text-gray-500 mt-2">Transactions appear here when payments are made via M-Pesa PayBill or STK Push.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Matched Student</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($transactions as $transaction): 
                        $receiptNumber = $transaction['mpesa_receipt_number'] ?? '';
                        $amount = floatval($transaction['amount'] ?? 0);
                        $transactionDate = $transaction['transaction_date'] ?? '';
                        $phoneNumber = $transaction['phone_number'] ?? '';
                        $accountNumber = $transaction['account_number'] ?? '';
                        $status = $transaction['status'] ?? 'pending';
                        $isReconciled = ($transaction['is_reconciled'] ?? 0) == 1 || !empty($transaction['payment_id']);
                        $resultCode = $transaction['result_code'] ?? 1;
                        $student = null;
                        
                        if (!empty($transaction['student_id'])) {
                            $student = [
                                'id' => $transaction['student_id'],
                                'admission_number' => $transaction['admission_number'] ?? '',
                                'first_name' => $transaction['first_name'] ?? '',
                                'last_name' => $transaction['last_name'] ?? ''
                            ];
                        }
                        
                        // Format transaction date
                        if (!empty($transactionDate)) {
                            if (strlen($transactionDate) == 14) {
                                // Format: YYYYMMDDHHmmss
                                $year = substr($transactionDate, 0, 4);
                                $month = substr($transactionDate, 4, 2);
                                $day = substr($transactionDate, 6, 2);
                                $hour = substr($transactionDate, 8, 2);
                                $minute = substr($transactionDate, 10, 2);
                                $second = substr($transactionDate, 12, 2);
                                $formattedDate = "$year-$month-$day $hour:$minute:$second";
                            } else {
                                $formattedDate = $transactionDate;
                            }
                        } else {
                            $formattedDate = $transaction['created_at'] ?? date('Y-m-d H:i:s');
                        }
                    ?>
                    <tr class="hover:bg-gray-50 <?php echo $isReconciled ? 'bg-green-50' : ''; ?>">
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php echo formatDate($formattedDate, 'd M Y H:i'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">
                            <?php echo htmlspecialchars($receiptNumber ?: '-'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php echo htmlspecialchars($phoneNumber ?: '-'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-green-600">
                            <?php echo formatCurrency($amount); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">
                            <?php echo htmlspecialchars($accountNumber ?: '-'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php if ($student): ?>
                            <a href="<?php echo BASE_URL; ?>/students/show/<?php echo $student['id']; ?>" 
                               class="text-blue-600 hover:text-blue-800">
                                <?php echo htmlspecialchars($student['admission_number']); ?> - 
                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                            </a>
                            <?php else: ?>
                            <span class="text-gray-400 italic">Not matched</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <?php if ($isReconciled): ?>
                            <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800 font-semibold">
                                <i class="fas fa-check-circle mr-1"></i>Reconciled
                            </span>
                            <?php elseif ($status === 'completed' && $resultCode == 0): ?>
                            <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">
                                <i class="fas fa-check mr-1"></i>Completed
                            </span>
                            <?php elseif ($status === 'pending'): ?>
                            <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i>Pending
                            </span>
                            <?php else: ?>
                            <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i><?php echo htmlspecialchars(ucfirst($status)); ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <?php if (!$isReconciled && $status === 'completed' && $resultCode == 0 && $amount > 0): ?>
                                <?php if ($student): ?>
                                <button onclick="reconcileTransaction(<?php echo $transaction['id']; ?>, <?php echo $student['id']; ?>, <?php echo $amount; ?>, '<?php echo htmlspecialchars($receiptNumber, ENT_QUOTES); ?>')" 
                                        class="text-green-600 hover:text-green-800 text-sm font-medium">
                                    <i class="fas fa-check mr-1"></i>Reconcile
                                </button>
                                <?php else: ?>
                                <button onclick="showReconcileModal(<?php echo $transaction['id']; ?>, <?php echo $amount; ?>, '<?php echo htmlspecialchars($receiptNumber, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($accountNumber, ENT_QUOTES); ?>')" 
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    <i class="fas fa-link mr-1"></i>Match
                                </button>
                                <?php endif; ?>
                            <?php elseif ($isReconciled): ?>
                            <a href="<?php echo BASE_URL; ?>/payments?receipt=<?php echo urlencode($transaction['payment_receipt'] ?? ''); ?>" 
                               class="text-blue-600 hover:text-blue-800 text-sm">
                                <i class="fas fa-eye mr-1"></i>View Payment
                            </a>
                            <?php else: ?>
                            <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Reconcile Modal -->
<div id="reconcileModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Reconcile M-Pesa Transaction</h3>
                <button onclick="closeReconcileModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="reconcileForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="transaction_id" id="modalTransactionId">
                <input type="hidden" name="receipt_number" id="modalReceiptNumber">
                <input type="hidden" name="amount" id="modalAmount">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Amount</label>
                    <p class="text-lg font-semibold text-green-600" id="modalAmountDisplay"></p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Number</label>
                    <p class="text-sm text-gray-600 font-mono" id="modalReceiptDisplay"></p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Reference</label>
                    <p class="text-sm text-gray-600 font-mono" id="modalAccountDisplay"></p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Student *</label>
                    <select name="student_id" id="modalStudentId" required class="w-full border rounded px-3 py-2">
                        <option value="">-- Select Student --</option>
                        <!-- Will be populated via AJAX -->
                    </select>
                </div>
                
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="closeReconcileModal()" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                        <i class="fas fa-check mr-2"></i>Reconcile
                    </button>
                </div>
                
                <div id="reconcileResult" class="hidden mt-4"></div>
            </form>
        </div>
    </div>
</div>

<script>
function reconcileTransaction(transactionId, studentId, amount, receiptNumber) {
    if (!confirm('Reconcile this M-Pesa transaction with the matched student?\n\nAmount: KES ' + amount.toFixed(2) + '\nReceipt: ' + receiptNumber)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('csrf_token', '<?php echo $csrf_token; ?>');
    formData.append('receipt_number', receiptNumber);
    formData.append('amount', amount);
    formData.append('student_id', studentId);
    
    fetch('<?php echo BASE_URL; ?>/mpesa/reconcileReceipt', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Transaction reconciled successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to reconcile transaction'));
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
        console.error('Error:', error);
    });
}

function showReconcileModal(transactionId, amount, receiptNumber, accountNumber) {
    document.getElementById('modalTransactionId').value = transactionId;
    document.getElementById('modalReceiptNumber').value = receiptNumber;
    document.getElementById('modalAmount').value = amount;
    document.getElementById('modalAmountDisplay').textContent = formatCurrency(amount);
    document.getElementById('modalReceiptDisplay').textContent = receiptNumber;
    document.getElementById('modalAccountDisplay').textContent = accountNumber || '-';
    
    // Load students
    fetch('<?php echo BASE_URL; ?>/students?format=json')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('modalStudentId');
            select.innerHTML = '<option value="">-- Select Student --</option>';
            if (data.students) {
                data.students.forEach(student => {
                    const option = document.createElement('option');
                    option.value = student.id;
                    option.textContent = student.admission_number + ' - ' + student.first_name + ' ' + student.last_name;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading students:', error);
        });
    
    document.getElementById('reconcileModal').classList.remove('hidden');
}

function closeReconcileModal() {
    document.getElementById('reconcileModal').classList.add('hidden');
    document.getElementById('reconcileForm').reset();
    document.getElementById('reconcileResult').classList.add('hidden');
}

document.getElementById('reconcileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const resultDiv = document.getElementById('reconcileResult');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
    resultDiv.classList.add('hidden');
    
    fetch('<?php echo BASE_URL; ?>/mpesa/reconcileReceipt', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.className = 'mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded';
            resultDiv.innerHTML = '<strong>Success!</strong> Transaction reconciled successfully.';
            resultDiv.classList.remove('hidden');
            
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            resultDiv.innerHTML = '<strong>Error:</strong> ' + (data.message || 'Failed to reconcile transaction');
            resultDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Reconcile';
        }
    })
    .catch(error => {
        resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
        resultDiv.innerHTML = '<strong>Error:</strong> An error occurred. Please try again.';
        resultDiv.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Reconcile';
    });
});

function formatCurrency(amount) {
    return 'KES ' + parseFloat(amount).toLocaleString('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Handle Get Transactions Button
document.getElementById('getTransactionsBtn').addEventListener('click', function() {
    const btn = this;
    const statusDiv = document.getElementById('fetchStatus');
    const days = document.getElementById('fetchDays').value;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Fetching...';
    statusDiv.classList.remove('hidden');
    statusDiv.innerHTML = '<div class="text-blue-600"><i class="fas fa-info-circle mr-2"></i>Fetching transactions from M-Pesa. This may take a moment...</div>';
    
    fetch('<?php echo BASE_URL; ?>/mpesa/fetch-transactions?days=' + days, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusDiv.innerHTML = '<div class="text-green-600"><i class="fas fa-check-circle mr-2"></i>' + 
                (data.message || 'Transactions fetched successfully!') + 
                (data.new_transactions ? ' Found ' + data.new_transactions + ' new transaction(s).' : '') +
                '</div>';
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            statusDiv.innerHTML = '<div class="text-red-600"><i class="fas fa-exclamation-circle mr-2"></i>' + 
                (data.message || 'Failed to fetch transactions') + '</div>';
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        statusDiv.innerHTML = '<div class="text-red-600"><i class="fas fa-exclamation-circle mr-2"></i>Error: Failed to fetch transactions. Please try again.</div>';
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});
</script>

