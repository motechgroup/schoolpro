<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Parent Details</h1>
        <div class="space-x-2">
            <a href="<?php echo BASE_URL; ?>/parents/edit/<?php echo $parent['id']; ?>" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <button onclick="deleteParent()" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                <i class="fas fa-trash mr-2"></i>Delete
            </button>
            <button onclick="showQuickSmsModal()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-sms mr-2"></i>Quick SMS
            </button>
            <a href="<?php echo BASE_URL; ?>/communication?parent_id=<?php echo $parent['id']; ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-envelope mr-2"></i>Send SMS
            </a>
            <a href="<?php echo BASE_URL; ?>/parents" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Parent Information -->
        <div class="md:col-span-2 bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">Parent Information</h2>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">Full Name</label>
                    <p class="text-lg font-semibold"><?php echo htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name']); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Phone</label>
                    <p class="text-lg"><?php echo htmlspecialchars($parent['phone']); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Email</label>
                    <p class="text-lg"><?php echo htmlspecialchars($parent['email'] ?? 'N/A'); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Relationship</label>
                    <p class="text-lg"><?php echo ucfirst($parent['relationship'] ?? 'N/A'); ?></p>
                </div>
                
                <?php if (!empty($parent['id_number'])): ?>
                <div>
                    <label class="text-sm font-medium text-gray-500">ID Number</label>
                    <p class="text-lg"><?php echo htmlspecialchars($parent['id_number']); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($parent['occupation'])): ?>
                <div>
                    <label class="text-sm font-medium text-gray-500">Occupation</label>
                    <p class="text-lg"><?php echo htmlspecialchars($parent['occupation']); ?></p>
                </div>
                <?php endif; ?>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Status</label>
                    <span class="px-2 py-1 text-xs rounded <?php 
                        echo $parent['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                    ?>">
                        <?php echo ucfirst($parent['status']); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Summary Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">Summary</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">Children</label>
                    <p class="text-3xl font-bold text-blue-600"><?php echo count($children); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Outstanding Balance</label>
                    <?php if ($total_balance > 0): ?>
                    <p class="text-3xl font-bold text-red-600"><?php echo formatCurrency($total_balance); ?></p>
                    <?php else: ?>
                    <p class="text-3xl font-bold text-green-600"><?php echo formatCurrency(0); ?></p>
                    <p class="text-sm text-green-600">All fees paid</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Children Information -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">Children</h2>
        
        <?php if (empty($children)): ?>
        <p class="text-gray-600">No children registered for this parent.</p>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admission No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Grade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($children as $child): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($child['admission_number']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($child['class_name'] ?? 'N/A'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($child['grade_display_name'] ?? 'N/A'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded <?php 
                                echo $child['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                            ?>">
                                <?php echo ucfirst($child['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="<?php echo BASE_URL; ?>/students/show/<?php echo $child['id']; ?>" 
                               class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye mr-1"></i>View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Fee Information -->
    <?php if (!empty($invoices)): ?>
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">Fee Information</h2>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($invoices as $invoice): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php 
                            $student = array_filter($children, function($c) use ($invoice) {
                                return $c['id'] == $invoice['student_id'];
                            });
                            $student = reset($student);
                            echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']);
                            ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">Term <?php echo $invoice['term']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo formatCurrency($invoice['total_amount']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600"><?php echo formatCurrency($invoice['paid_amount'] ?? 0); ?></td>
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
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Quick SMS Modal -->
<div id="quickSmsModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Send SMS to <?php echo htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name']); ?></h3>
                <button onclick="closeQuickSmsModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="quickSmsForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="parent_id" value="<?php echo $parent['id']; ?>">
                <input type="hidden" name="phone" value="<?php echo htmlspecialchars($parent['phone']); ?>">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                    <textarea name="message" id="quickSmsMessage" rows="5" 
                              class="w-full border rounded px-3 py-2" 
                              placeholder="Type your message..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        <span id="quickCharCount">0</span> characters | 
                        <span id="quickSmsCount">0</span> SMS
                    </p>
                    <div class="mt-2 p-2 bg-blue-50 rounded text-xs text-blue-700">
                        <strong>Tip:</strong> Use placeholders like {student_name}, {fee_balance}, {admission_number} for personalized messages
                    </div>
                </div>
                
                <div class="flex space-x-2">
                    <button type="button" onclick="useQuickTemplate('fee_reminder')" class="flex-1 bg-gray-100 text-gray-700 px-3 py-2 rounded hover:bg-gray-200 text-sm">
                        Fee Reminder
                    </button>
                    <button type="button" onclick="useQuickTemplate('attendance')" class="flex-1 bg-gray-100 text-gray-700 px-3 py-2 rounded hover:bg-gray-200 text-sm">
                        Attendance Alert
                    </button>
                    <button type="button" onclick="useQuickTemplate('general')" class="flex-1 bg-gray-100 text-gray-700 px-3 py-2 rounded hover:bg-gray-200 text-sm">
                        General
                    </button>
                </div>
                
                <div class="mt-4 flex justify-end space-x-2">
                    <button type="button" onclick="closeQuickSmsModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        <i class="fas fa-paper-plane mr-2"></i>Send SMS
                    </button>
                </div>
            </form>
            
            <div id="quickSmsResult" class="mt-4 hidden"></div>
        </div>
    </div>
</div>

<script>
const quickTemplates = {
    fee_reminder: 'Dear {parent_name}, This is a reminder that {student_name} (Adm: {admission_number}) has outstanding fees of {fee_balance}. Please make payment at your earliest convenience. Thank you. - {school_name}',
    attendance: 'Dear {parent_name}, We noticed that {student_name} was absent today ({current_date}). Please contact the school if there is any concern. Thank you. - {school_name}',
    general: 'Dear {parent_name}, Important message from {school_name}. Please contact the school for details. Thank you.'
};

function showQuickSmsModal() {
    document.getElementById('quickSmsModal').classList.remove('hidden');
}

function closeQuickSmsModal() {
    document.getElementById('quickSmsModal').classList.add('hidden');
    document.getElementById('quickSmsForm').reset();
    document.getElementById('quickCharCount').textContent = '0';
    document.getElementById('quickSmsCount').textContent = '0';
    document.getElementById('quickSmsResult').classList.add('hidden');
}

function useQuickTemplate(type) {
    document.getElementById('quickSmsMessage').value = quickTemplates[type] || '';
    document.getElementById('quickSmsMessage').dispatchEvent(new Event('input'));
}

document.getElementById('quickSmsMessage').addEventListener('input', function() {
    const charCount = this.value.length;
    const smsCount = Math.ceil(charCount / 160);
    
    document.getElementById('quickCharCount').textContent = charCount;
    document.getElementById('quickSmsCount').textContent = smsCount;
});

document.getElementById('quickSmsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const resultDiv = document.getElementById('quickSmsResult');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
    resultDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/communication/sendSms', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultDiv.className = 'mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded';
            resultDiv.innerHTML = '<strong>Success!</strong> SMS sent successfully.';
            resultDiv.classList.remove('hidden');
            setTimeout(() => {
                closeQuickSmsModal();
            }, 2000);
        } else {
            resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            resultDiv.innerHTML = '<strong>Error:</strong> ' + (data.message || 'Failed to send SMS');
            resultDiv.classList.remove('hidden');
        }
    } catch (error) {
        resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
        resultDiv.innerHTML = '<strong>Error:</strong> An error occurred. Please try again.';
        resultDiv.classList.remove('hidden');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Send SMS';
    }
});

function deleteParent() {
    if (!confirm('Are you sure you want to delete this parent? This action cannot be undone. Note: You cannot delete a parent with active students.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('csrf_token', '<?php echo $csrf_token; ?>');
    
    fetch('<?php echo BASE_URL; ?>/parents/delete/<?php echo $parent['id']; ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Parent deleted successfully');
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.href = '<?php echo BASE_URL; ?>/parents';
            }
        } else {
            alert('Error: ' + (data.message || 'Failed to delete parent'));
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
    });
}
</script>
