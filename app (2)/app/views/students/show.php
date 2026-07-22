<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Student Details</h1>
        <div class="space-x-2">
            <?php if (!Auth::hasAnyRole(['teacher'])): ?>
            <a href="<?php echo BASE_URL; ?>/students/generateId/<?php echo $student['id']; ?>" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700" target="_blank">
                <i class="fas fa-id-card mr-2"></i>Generate ID Card
            </a>
            <?php endif; ?>
            <?php if (Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar', 'accountant'])): ?>
            <a href="<?php echo BASE_URL; ?>/studentfees/assign/<?php echo $student['id']; ?>" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                <i class="fas fa-money-bill-wave mr-2"></i>Assign Fees
            </a>
            <?php endif; ?>
            <?php if (!Auth::hasAnyRole(['teacher'])): ?>
            <a href="<?php echo BASE_URL; ?>/students/edit/<?php echo $student['id']; ?>" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <?php if ($student['status'] == 'active'): ?>
            <a href="#" onclick="deleteStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>'); return false;" 
               class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                <i class="fas fa-trash mr-2"></i>Delete
            </a>
            <?php else: ?>
            <a href="#" onclick="restoreStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>'); return false;" 
               class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-undo mr-2"></i>Restore
            </a>
            <?php endif; ?>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>/students" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Student Information -->
        <div class="md:col-span-2 bg-white rounded-lg shadow p-6">
            <div class="flex items-start space-x-6 mb-4">
                <!-- Student Photo -->
                <div class="flex-shrink-0">
                    <?php if (!empty($student['photo'])): ?>
                    <img src="<?php echo BASE_URL; ?>/public/uploads/<?php echo htmlspecialchars($student['photo']); ?>" 
                         alt="Student Photo" 
                         class="w-32 h-32 object-cover rounded-lg border-4 border-blue-200 shadow-lg">
                    <?php else: ?>
                    <div class="w-32 h-32 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg border-4 border-blue-200 shadow-lg flex items-center justify-center">
                        <i class="fas fa-user text-white text-5xl"></i>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Student Name and Basic Info -->
                <div class="flex-1">
                    <h2 class="text-2xl font-bold mb-2">
                        <?php echo htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ?? '') . ' ' . $student['last_name']); ?>
                    </h2>
                    <p class="text-gray-600 mb-1">
                        <span class="font-semibold">Admission No:</span> <?php echo htmlspecialchars($student['admission_number']); ?>
                    </p>
                    <?php if (!empty($student['upi'])): ?>
                    <p class="text-gray-600 mb-1">
                        <span class="font-semibold">UPI:</span> <?php echo htmlspecialchars($student['upi']); ?>
                    </p>
                    <?php endif; ?>
                    <p class="text-gray-600">
                        <span class="font-semibold">Class:</span> <?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?>
                    </p>
                </div>
            </div>
            
            <h3 class="text-xl font-bold mb-4 mt-6">Details</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">Gender</label>
                    <p class="text-lg"><?php echo ucfirst($student['gender']); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Date of Birth</label>
                    <p class="text-lg"><?php echo formatDate($student['date_of_birth']); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Admission Date</label>
                    <p class="text-lg"><?php echo formatDate($student['admission_date']); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Grade</label>
                    <p class="text-lg"><?php echo htmlspecialchars($student['grade_display_name'] ?? 'N/A'); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Status</label>
                    <span class="px-2 py-1 text-xs rounded <?php 
                        echo $student['status'] == 'active' ? 'bg-green-100 text-green-800' : 
                            ($student['status'] == 'alumni' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800');
                    ?>">
                        <?php echo ucfirst($student['status']); ?>
                    </span>
                </div>
                
                <?php if (!empty($student['medical_info'])): ?>
                <div class="col-span-2">
                    <label class="text-sm font-medium text-gray-500">Medical Information</label>
                    <p class="text-lg"><?php echo htmlspecialchars($student['medical_info']); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Parent Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">Parent/Guardian</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">Name</label>
                    <p class="text-lg"><?php echo htmlspecialchars(($student['parent_first_name'] ?? '') . ' ' . ($student['parent_last_name'] ?? '')); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Phone</label>
                    <p class="text-lg"><?php echo htmlspecialchars($student['parent_phone'] ?? 'N/A'); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Email</label>
                    <p class="text-lg"><?php echo htmlspecialchars($student['parent_email'] ?? 'N/A'); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Relationship</label>
                    <p class="text-lg"><?php echo ucfirst($student['parent_relationship'] ?? 'N/A'); ?></p>
                </div>
                
                <?php if (Auth::hasAnyRole(['super_admin', 'school_admin', 'head_teacher', 'teacher']) && !empty($student['parent_id'])): ?>
                <div class="pt-4 border-t border-gray-200">
                    <label class="text-sm font-medium text-gray-700 mb-2 block">Quick Communication</label>
                    <div class="flex flex-wrap gap-2">
                        <?php if (!empty($student['parent_phone'])): ?>
                        <button onclick="showCommunicationModal('sms', <?php echo $student['parent_id']; ?>, '<?php echo htmlspecialchars($student['parent_phone']); ?>', '<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>', <?php echo $student['id']; ?>)" 
                                class="bg-blue-600 text-white px-3 py-2 rounded text-sm hover:bg-blue-700">
                            <i class="fas fa-sms mr-1"></i>SMS
                        </button>
                        <button onclick="showCommunicationModal('whatsapp', <?php echo $student['parent_id']; ?>, '<?php echo htmlspecialchars($student['parent_phone']); ?>', '<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>', <?php echo $student['id']; ?>)" 
                                class="bg-green-600 text-white px-3 py-2 rounded text-sm hover:bg-green-700">
                            <i class="fab fa-whatsapp mr-1"></i>WhatsApp
                        </button>
                        <?php endif; ?>
                        <?php if (!empty($student['parent_email'])): ?>
                        <button onclick="showCommunicationModal('email', <?php echo $student['parent_id']; ?>, '<?php echo htmlspecialchars($student['parent_email']); ?>', '<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>', <?php echo $student['id']; ?>)" 
                                class="bg-purple-600 text-white px-3 py-2 rounded text-sm hover:bg-purple-700">
                            <i class="fas fa-envelope mr-1"></i>Email
                        </button>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>/communication?parent_id=<?php echo $student['parent_id']; ?>" 
                           class="bg-gray-600 text-white px-3 py-2 rounded text-sm hover:bg-gray-700">
                            <i class="fas fa-comments mr-1"></i>All Options
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Attendance Information -->
    <?php if (Auth::hasAnyRole(['super_admin', 'school_admin', 'head_teacher', 'teacher', 'parent'])): ?>
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">Attendance</h2>
            <a href="<?php echo BASE_URL; ?>/attendance?class_id=<?php echo $student['class_id']; ?>" class="text-blue-600 hover:text-blue-800 text-sm">
                <i class="fas fa-calendar-check mr-1"></i>View All Attendance
            </a>
        </div>
        
        <?php if (isset($attendanceSummary) && $attendanceSummary): ?>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                <p class="text-sm text-gray-600 mb-1">Present Days</p>
                <p class="text-2xl font-bold text-green-600"><?php echo $attendanceSummary['present_days'] ?? 0; ?></p>
            </div>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                <p class="text-sm text-gray-600 mb-1">Absent Days</p>
                <p class="text-2xl font-bold text-red-600"><?php echo $attendanceSummary['absent_days'] ?? 0; ?></p>
            </div>
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                <p class="text-sm text-gray-600 mb-1">Late Days</p>
                <p class="text-2xl font-bold text-yellow-600"><?php echo $attendanceSummary['late_days'] ?? 0; ?></p>
            </div>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <p class="text-sm text-gray-600 mb-1">Total Days</p>
                <p class="text-2xl font-bold text-blue-600"><?php echo $attendanceSummary['total_days'] ?? 0; ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Recent Attendance Records -->
        <div>
            <h3 class="text-lg font-semibold mb-3">Recent Attendance (Last 30 Days)</h3>
            <?php if (empty($attendanceRecords)): ?>
            <p class="text-gray-600">No attendance records found for the last 30 days.</p>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach (array_slice($attendanceRecords, 0, 10) as $record): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <?php echo formatDate($record['attendance_date'], 'd M Y'); ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <?php
                                $statusColors = [
                                    'present' => 'bg-green-100 text-green-800',
                                    'absent' => 'bg-red-100 text-red-800',
                                    'late' => 'bg-yellow-100 text-yellow-800',
                                    'excused' => 'bg-blue-100 text-blue-800'
                                ];
                                $color = $statusColors[$record['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded <?php echo $color; ?>">
                                    <?php echo ucfirst($record['status']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <?php echo htmlspecialchars($record['remarks'] ?? '-'); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Fee Information -->
    <?php if (Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar', 'parent'])): ?>
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">Fee Information</h2>
            <div class="flex space-x-2">
                <a href="<?php echo BASE_URL; ?>/feereport/student/<?php echo $student['id']; ?>" class="text-purple-600 hover:text-purple-800 text-sm">
                    <i class="fas fa-file-alt mr-1"></i>Fee Report
                </a>
                <?php if (Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar', 'accountant'])): ?>
                <a href="<?php echo BASE_URL; ?>/studentfees/assign/<?php echo $student['id']; ?>" class="text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-edit mr-1"></i>Manage Fees
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (empty($invoices)): ?>
        <p class="text-gray-600">No invoices found. Assign fee heads to generate invoices.</p>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <?php if (Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar'])): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($invoices as $invoice): ?>
                    <tr>
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
                        <?php if (Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar']) && $invoice['balance'] > 0): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="#" onclick="showPaymentForm(<?php echo $student['id']; ?>, <?php echo $invoice['term']; ?>, '<?php echo htmlspecialchars($invoice['academic_year']); ?>'); return false;" 
                               class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-money-bill-wave mr-1"></i>Pay
                            </a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function deleteStudent(id, studentName) {
    if (!confirm('Are you sure you want to delete student: ' + studentName + '?\n\nThis will set the student status to inactive. You can restore them later if needed.')) {
        return;
    }
    
    fetch('<?php echo BASE_URL; ?>/students/delete/' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Student deleted successfully');
            window.location.href = '<?php echo BASE_URL; ?>/students';
        } else {
            alert('Failed to delete student: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
        console.error('Error:', error);
    });
}

function restoreStudent(id, studentName) {
    if (!confirm('Restore student: ' + studentName + '?\n\nThis will set the student status back to active.')) {
        return;
    }
    
    fetch('<?php echo BASE_URL; ?>/students/restore/' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Student restored successfully');
            location.reload();
        } else {
            alert('Failed to restore student: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
        console.error('Error:', error);
    });
}
</script>

<!-- Payment Modal -->
<?php if (Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar'])): ?>
<div id="paymentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
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
function showPaymentForm(studentId, term, academicYear) {
    const modal = document.getElementById('paymentModal');
    const container = document.getElementById('paymentFormContainer');
    
    container.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-2xl"></i><p class="mt-2">Loading...</p></div>';
    modal.classList.remove('hidden');
    
    fetch('<?php echo BASE_URL; ?>/fees/paymentForm/' + studentId + '?term=' + term + '&academic_year=' + encodeURIComponent(academicYear))
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
            
            // Execute any scripts in the loaded HTML
            const scripts = container.querySelectorAll('script[data-form-init]');
            scripts.forEach(oldScript => {
                const newScript = document.createElement('script');
                Array.from(oldScript.attributes).forEach(attr => {
                    newScript.setAttribute(attr.name, attr.value);
                });
                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });
            
            // Attach form submission handler
            const form = container.querySelector('#paymentForm');
            if (form) {
                // Check if this is M-Pesa payment confirmation
                const paymentMethod = form.querySelector('#paymentMethod');
                const submitBtn = form.querySelector('#submitPaymentBtn');
                
                // Handle M-Pesa confirmation differently
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const formData = new FormData(form);
                    const errorDiv = form.querySelector('#errorMessage');
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const currentPaymentMethod = form.querySelector('#paymentMethod')?.value || '';
                    
                    // If M-Pesa is selected, handle confirmation instead of recording payment
                    if (currentPaymentMethod === 'mpesa') {
                        handleMpesaConfirmation(form);
                        return false;
                    }
                    
                    // Add total_amount as amount for backward compatibility
                    formData.append('amount', formData.get('total_amount'));
                    
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
                    if (errorDiv) errorDiv.classList.add('hidden');
                    
                    // Validate amount before submitting
                    const paymentAmount = parseFloat(formData.get('total_amount') || formData.get('amount') || 0);
                    if (paymentAmount <= 0) {
                        alert('Please enter a valid payment amount greater than 0');
                        submitBtn.disabled = false;
                        const submitBtnText = form.querySelector('#submitBtnText');
                        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>' + (submitBtnText ? submitBtnText.textContent : 'Record Payment');
                        return;
                    }
                    
                    console.log('Submitting payment:', {
                        invoiceId: formData.get('invoice_id'),
                        amount: paymentAmount,
                        method: formData.get('payment_method')
                    });
                    
                    try {
                        const response = await fetch('<?php echo BASE_URL; ?>/fees/processPayment/' + studentId + '?term=' + term + '&academic_year=' + encodeURIComponent(academicYear), {
                            method: 'POST',
                            body: formData
                        });
                        
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        
                        const data = await response.json();
                        
                        console.log('Payment response:', data);
                        
                        if (data.success) {
                            alert('Payment recorded successfully!');
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                window.location.reload();
                            }
                        } else {
                            const errorMessage = data.message || 'Failed to record payment. Please check the console for details.';
                            if (errorDiv) {
                                errorDiv.textContent = errorMessage;
                                errorDiv.classList.remove('hidden');
                            } else {
                                alert(errorMessage);
                            }
                            console.error('Payment error:', data);
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Record Payment';
                        }
                    } catch (error) {
                        const errorMsg = 'An error occurred: ' + error.message + '. Please try again.';
                        if (errorDiv) {
                            errorDiv.textContent = errorMsg;
                            errorDiv.classList.remove('hidden');
                        } else {
                            alert(errorMsg);
                        }
                        console.error('Payment submission error:', error);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Record Payment';
                    }
                });
            }
        })
        .catch(error => {
            container.innerHTML = '<div class="text-red-600 p-4">Error loading payment form. Please try again.</div>';
            console.error('Error loading payment form:', error);
        });
}

// Handle M-Pesa payment confirmation (manual fetch)
function handleMpesaConfirmation(form) {
    const invoiceId = form.querySelector('input[name="invoice_id"]')?.value || '';
    const checkoutRequestId = form.getAttribute('data-checkout-request-id') || '';
    const submitBtn = form.querySelector('#submitPaymentBtn') || form.querySelector('button[type="submit"]');
    const errorDiv = form.querySelector('#errorMessage') || document.createElement('div');
    
    if (!errorDiv.id) {
        errorDiv.id = 'errorMessage';
        errorDiv.className = 'hidden mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded';
        form.appendChild(errorDiv);
    }
    
    if (!checkoutRequestId) {
        errorDiv.textContent = 'No STK Push request found. Please send STK Push first, or use "Reconcile by Receipt Number" if you paid manually.';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    // Disable button and show loading
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Checking Payment...';
    }
    
    errorDiv.classList.add('hidden');
    
    // Check payment status
    fetch('<?php echo BASE_URL; ?>/fees/checkPaymentStatus', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            checkout_request_id: checkoutRequestId,
            invoice_id: invoiceId,
            csrf_token: form.querySelector('input[name="csrf_token"]')?.value || ''
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.status === 'completed') {
            // Payment confirmed!
            errorDiv.className = 'mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded';
            errorDiv.innerHTML = '<strong>✓ Payment Confirmed!</strong><br>' +
                'Amount: KES ' + parseFloat(data.amount || 0).toFixed(2) + '<br>' +
                'Receipt: ' + (data.receipt_number || 'N/A') + '<br>' +
                'M-Pesa Receipt: ' + (data.mpesa_receipt || 'N/A');
            errorDiv.classList.remove('hidden');
            
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Payment Confirmed';
            }
            
            // Close modal and reload after 2 seconds
            setTimeout(function() {
                closePaymentModal();
                setTimeout(function() {
                    location.reload();
                }, 300);
            }, 2000);
        } else {
            // Payment not found or still pending
            errorDiv.className = 'mt-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded';
            errorDiv.textContent = data.message || 'Payment not yet confirmed. Please wait a moment and try again, or check if payment was completed on your phone.';
            errorDiv.classList.remove('hidden');
            
            if (submitBtn) {
                submitBtn.disabled = false;
                const submitBtnText = form.querySelector('#submitBtnText');
                submitBtn.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + (submitBtnText ? submitBtnText.textContent : 'Confirm Payment');
            }
        }
    })
    .catch(error => {
        errorDiv.className = 'mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded';
        errorDiv.textContent = 'Error checking payment status: ' + error.message;
        errorDiv.classList.remove('hidden');
        
        if (submitBtn) {
            submitBtn.disabled = false;
            const submitBtnText = form.querySelector('#submitBtnText');
            submitBtn.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + (submitBtnText ? submitBtnText.textContent : 'Confirm Payment');
        }
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

// Make functions available globally
window.closePaymentModal = closePaymentModal;
window.handleMpesaConfirmation = handleMpesaConfirmation;
</script>
<?php endif; ?>

<!-- Communication Modal -->
<?php if (Auth::hasAnyRole(['super_admin', 'school_admin', 'head_teacher', 'teacher']) && !empty($student['parent_id'])): ?>
<div id="communicationModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold" id="communicationModalTitle">Send Message</h3>
                <button onclick="closeCommunicationModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="communicationForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="parent_id" id="commParentId">
                <input type="hidden" name="student_id" id="commStudentId">
                <input type="hidden" name="method" id="commMethod">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Recipient</label>
                    <p class="text-sm text-gray-600" id="commRecipient"></p>
                </div>
                
                <div class="mb-4" id="commMessageField">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                    <textarea name="message" id="commMessage" rows="6" 
                              class="w-full border rounded px-3 py-2" 
                              placeholder="Type your message here..."
                              required></textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        <span id="commCharCount">0</span> characters
                        <span id="commSmsCount" class="hidden"> | <span id="smsCountValue">0</span> SMS</span>
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        You can use: {parent_name}, {student_name}, {admission_number}, {class_name}, {balance_amount}
                    </p>
                </div>
                
                <div class="mb-4" id="commSubjectField" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                    <input type="text" name="subject" id="commSubject" 
                           class="w-full border rounded px-3 py-2"
                           placeholder="Email subject">
                </div>
                
                <div id="commResult" class="hidden mb-4"></div>
                
                <div class="flex space-x-2">
                    <button type="button" onclick="closeCommunicationModal()" class="flex-1 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" id="commSubmitBtn" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-paper-plane mr-2"></i>Send
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentCommMethod = '';
let currentParentId = null;
let currentStudentId = null;

function showCommunicationModal(method, parentId, contact, studentName, studentId) {
    currentCommMethod = method;
    currentParentId = parentId;
    currentStudentId = studentId;
    
    const modal = document.getElementById('communicationModal');
    const title = document.getElementById('communicationModalTitle');
    const recipient = document.getElementById('commRecipient');
    const messageField = document.getElementById('commMessageField');
    const subjectField = document.getElementById('commSubjectField');
    const messageInput = document.getElementById('commMessage');
    const subjectInput = document.getElementById('commSubject');
    const charCount = document.getElementById('commCharCount');
    const smsCount = document.getElementById('commSmsCount');
    const submitBtn = document.getElementById('commSubmitBtn');
    const form = document.getElementById('communicationForm');
    
    // Reset form
    form.reset();
    document.getElementById('commParentId').value = parentId;
    document.getElementById('commStudentId').value = studentId;
    document.getElementById('commMethod').value = method;
    
    // Set title and recipient
    const methodNames = {
        'sms': 'Send SMS',
        'whatsapp': 'Send WhatsApp',
        'email': 'Send Email'
    };
    title.textContent = methodNames[method] || 'Send Message';
    recipient.textContent = contact;
    
    // Show/hide fields based on method
    if (method === 'email') {
        messageField.style.display = 'block';
        subjectField.style.display = 'block';
        subjectInput.required = true;
        messageInput.rows = 8;
        submitBtn.className = 'flex-1 bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700';
        submitBtn.innerHTML = '<i class="fas fa-envelope mr-2"></i>Send Email';
        smsCount.classList.add('hidden');
    } else {
        messageField.style.display = 'block';
        subjectField.style.display = 'none';
        subjectInput.required = false;
        messageInput.rows = 6;
        if (method === 'whatsapp') {
            submitBtn.className = 'flex-1 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700';
            submitBtn.innerHTML = '<i class="fab fa-whatsapp mr-2"></i>Send WhatsApp';
            smsCount.classList.add('hidden');
        } else {
            submitBtn.className = 'flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700';
            submitBtn.innerHTML = '<i class="fas fa-sms mr-2"></i>Send SMS';
            smsCount.classList.remove('hidden');
        }
    }
    
    // Set default message with placeholders
    const schoolName = '<?php echo getSchoolName(); ?>';
    const defaultMessage = `Dear {parent_name},\n\nThis is regarding ${studentName} (Admission: {admission_number}).\n\nThank you.\n${schoolName}`;
    messageInput.value = defaultMessage;
    updateCharCount();
    
    modal.classList.remove('hidden');
}

function closeCommunicationModal() {
    const modal = document.getElementById('communicationModal');
    const resultDiv = document.getElementById('commResult');
    modal.classList.add('hidden');
    resultDiv.classList.add('hidden');
    document.getElementById('communicationForm').reset();
}

function updateCharCount() {
    const message = document.getElementById('commMessage').value;
    const charCount = document.getElementById('commCharCount');
    const smsCountValue = document.getElementById('smsCountValue');
    
    charCount.textContent = message.length;
    if (smsCountValue) {
        smsCountValue.textContent = Math.ceil(message.length / 160);
    }
}

document.getElementById('commMessage').addEventListener('input', updateCharCount);

document.getElementById('communicationForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = document.getElementById('commSubmitBtn');
    const resultDiv = document.getElementById('commResult');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
    resultDiv.classList.add('hidden');
    
    try {
        let url = '';
        if (currentCommMethod === 'sms') {
            url = '<?php echo BASE_URL; ?>/communication/sendSms';
            formData.append('phone', document.getElementById('commRecipient').textContent);
        } else if (currentCommMethod === 'whatsapp') {
            url = '<?php echo BASE_URL; ?>/communication/sendWhatsApp';
            formData.append('phone', document.getElementById('commRecipient').textContent);
        } else if (currentCommMethod === 'email') {
            url = '<?php echo BASE_URL; ?>/communication/sendEmail';
            formData.append('recipient_type', 'parent');
            formData.append('recipient_ids[]', currentParentId);
        }
        
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        resultDiv.classList.remove('hidden');
        
        if (data.success) {
            resultDiv.className = 'p-4 bg-green-100 border border-green-400 text-green-700 rounded';
            resultDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i><strong>Success!</strong> ' + (data.message || 'Message sent successfully');
            
            // Close modal after 2 seconds
            setTimeout(() => {
                closeCommunicationModal();
            }, 2000);
        } else {
            resultDiv.className = 'p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            resultDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i><strong>Error:</strong> ' + (data.message || 'Failed to send message');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        resultDiv.classList.remove('hidden');
        resultDiv.className = 'p-4 bg-red-100 border border-red-400 text-red-700 rounded';
        resultDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i><strong>Error:</strong> ' + error.message;
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});
</script>
<?php endif; ?>

