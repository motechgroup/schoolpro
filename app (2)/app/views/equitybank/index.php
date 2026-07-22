<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Equity Bank Transactions</h1>
            <p class="text-gray-600 mt-1">Fetch and reconcile payments from Equity Bank via Jenga API</p>
        </div>
        <div class="flex space-x-2">
            <button onclick="fetchTransactions()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-sync mr-2"></i>Fetch Transactions
            </button>
            <a href="<?php echo BASE_URL; ?>/settings" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                <i class="fas fa-cog mr-2"></i>Settings
            </a>
        </div>
    </div>
    
    <?php if (!empty($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>
    
    <?php if (empty($account_number)): ?>
    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
        <strong>Configuration Required:</strong> Please configure Equity Bank account number and Jenga API credentials in 
        <a href="<?php echo BASE_URL; ?>/settings" class="underline font-semibold">Settings</a>.
    </div>
    <?php else: ?>
    
    <!-- Account Info -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-green-600">Equity Bank Account</h2>
                <p class="text-gray-600 mt-1">Account: <strong><?php echo htmlspecialchars($account_number); ?></strong></p>
            </div>
            <?php if ($balance !== null): ?>
            <div class="text-right">
                <p class="text-sm text-gray-600">Available Balance</p>
                <p class="text-2xl font-bold text-green-600"><?php echo formatCurrency($balance); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Date Filter -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form id="dateFilterForm" class="flex items-end space-x-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" 
                       value="<?php echo htmlspecialchars($start_date); ?>"
                       class="w-full border rounded px-3 py-2">
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" 
                       value="<?php echo htmlspecialchars($end_date); ?>"
                       class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </form>
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
            <p class="text-gray-600">No transactions found for the selected date range.</p>
            <button onclick="fetchTransactions()" class="mt-4 bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                <i class="fas fa-sync mr-2"></i>Fetch Transactions
            </button>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Narration</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Matched Student</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($transactions as $transaction): 
                        $transactionId = $transaction['transactionId'] ?? $transaction['id'] ?? '';
                        $amount = floatval($transaction['amount'] ?? $transaction['transactionAmount'] ?? 0);
                        $date = $transaction['transactionDate'] ?? $transaction['date'] ?? date('Y-m-d');
                        $reference = $transaction['reference'] ?? $transaction['transactionReference'] ?? '';
                        $narration = $transaction['narration'] ?? $transaction['description'] ?? '';
                        $type = $transaction['transactionType'] ?? $transaction['type'] ?? 'Credit';
                        $isCredit = stripos($type, 'credit') !== false || $amount > 0;
                        $student = $transaction['matched_student'] ?? null;
                        $reconciled = $transaction['reconciled'] ?? false;
                    ?>
                    <tr class="hover:bg-gray-50 <?php echo $reconciled ? 'bg-green-50' : ''; ?>">
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php echo formatDate($date, 'd M Y'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">
                            <?php echo htmlspecialchars($reference); ?>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <?php echo htmlspecialchars($narration); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold <?php echo $isCredit ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $isCredit ? '+' : '-'; ?><?php echo formatCurrency(abs($amount)); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 py-1 text-xs rounded <?php echo $isCredit ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo htmlspecialchars($type); ?>
                            </span>
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
                            <?php if ($reconciled): ?>
                            <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800 font-semibold">
                                <i class="fas fa-check-circle mr-1"></i>Reconciled
                            </span>
                            <?php else: ?>
                            <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800">
                                Pending
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <?php if (!$reconciled && $isCredit && $student): ?>
                            <button onclick="reconcileTransaction('<?php echo htmlspecialchars($transactionId, ENT_QUOTES); ?>', <?php echo $student['id']; ?>, <?php echo $amount; ?>, '<?php echo htmlspecialchars($date, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($reference, ENT_QUOTES); ?>')" 
                                    class="text-green-600 hover:text-green-800 text-sm font-medium">
                                <i class="fas fa-check mr-1"></i>Reconcile
                            </button>
                            <?php elseif (!$reconciled && $isCredit): ?>
                            <button onclick="showReconcileModal('<?php echo htmlspecialchars($transactionId, ENT_QUOTES); ?>', <?php echo $amount; ?>, '<?php echo htmlspecialchars($date, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($reference, ENT_QUOTES); ?>')" 
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                <i class="fas fa-link mr-1"></i>Match
                            </button>
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
    <?php endif; ?>
</div>

<!-- Reconcile Modal -->
<div id="reconcileModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Reconcile Transaction</h3>
                <button onclick="closeReconcileModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="reconcileForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="transaction_id" id="modalTransactionId">
                <input type="hidden" name="amount" id="modalAmount">
                <input type="hidden" name="transaction_date" id="modalTransactionDate">
                <input type="hidden" name="reference" id="modalReference">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Amount</label>
                    <p class="text-lg font-semibold text-green-600" id="modalAmountDisplay"></p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
                    <p class="text-sm text-gray-600" id="modalReferenceDisplay"></p>
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
function fetchTransactions() {
    const form = document.getElementById('dateFilterForm');
    const formData = new FormData(form);
    
    // Show loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Fetching...';
    
    fetch('<?php echo BASE_URL; ?>/equitybank/fetchTransactions', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to fetch transactions'));
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
        console.error('Error:', error);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function reconcileTransaction(transactionId, studentId, amount, date, reference) {
    if (!confirm('Reconcile this transaction with the matched student?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('csrf_token', '<?php echo $csrf_token; ?>');
    formData.append('transaction_id', transactionId);
    formData.append('student_id', studentId);
    formData.append('amount', amount);
    formData.append('transaction_date', date);
    formData.append('reference', reference);
    
    fetch('<?php echo BASE_URL; ?>/equitybank/reconcile', {
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

function showReconcileModal(transactionId, amount, date, reference) {
    document.getElementById('modalTransactionId').value = transactionId;
    document.getElementById('modalAmount').value = amount;
    document.getElementById('modalAmountDisplay').textContent = formatCurrency(amount);
    document.getElementById('modalTransactionDate').value = date;
    document.getElementById('modalReference').value = reference;
    document.getElementById('modalReferenceDisplay').textContent = reference;
    
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
    
    fetch('<?php echo BASE_URL; ?>/equitybank/reconcile', {
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
</script>

