<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Assign Fee Heads</h1>
        <a href="<?php echo BASE_URL; ?>/students/show/<?php echo $student['id']; ?>" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>
    
    <!-- Student Info -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Student Information</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="text-sm font-medium text-gray-500">Name</label>
                <p class="text-lg font-semibold"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Admission No</label>
                <p class="text-lg"><?php echo htmlspecialchars($student['admission_number']); ?></p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Class</label>
                <p class="text-lg"><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Grade</label>
                <p class="text-lg"><?php echo htmlspecialchars($student['grade_display_name'] ?? 'N/A'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Fee Assignment Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <form id="assignForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="term" value="<?php echo $term; ?>">
            <input type="hidden" name="academic_year" value="<?php echo htmlspecialchars($academicYear); ?>">
            
            <div class="mb-4 flex space-x-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Term</label>
                    <select name="term" id="termSelect" class="border rounded px-3 py-2">
                        <option value="1" <?php echo ($term == 1) ? 'selected' : ''; ?>>Term 1</option>
                        <option value="2" <?php echo ($term == 2) ? 'selected' : ''; ?>>Term 2</option>
                        <option value="3" <?php echo ($term == 3) ? 'selected' : ''; ?>>Term 3</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
                    <input type="text" name="academic_year" id="yearSelect" 
                           value="<?php echo htmlspecialchars($academicYear); ?>"
                           class="border rounded px-3 py-2">
                </div>
            </div>
            
            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-semibold">Fee Heads</h3>
                    <a href="<?php echo BASE_URL; ?>/feeheads" class="text-sm text-blue-600 hover:text-blue-800">
                        <i class="fas fa-cog mr-1"></i>Manage Fee Heads
                    </a>
                </div>
                <p class="text-sm text-gray-600 mb-4">
                    Select fee heads to assign to this student. The system will use the default amount for each fee head (as defined in fee head settings). Amounts can only be changed when recording payments.
                    <br><span class="text-yellow-700 font-semibold"><i class="fas fa-info-circle mr-1"></i>Note: Fee heads with existing payments are locked and cannot be removed.</span>
                </p>
                
                <?php if (empty($feeHeads)): ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded p-4 mb-4">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        No fee heads found. Please <a href="<?php echo BASE_URL; ?>/feeheads/create" class="underline font-semibold">create fee heads</a> first before assigning fees to students.
                    </p>
                </div>
                <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($feeHeads as $feeHead): 
                        $assignedAmount = $assignedFeeHeads[$feeHead['id']] ?? 0;
                        $hasPayments = isset($feeHeadsWithPayments[$feeHead['id']]);
                        $isAssigned = $assignedAmount > 0;
                        $displayAmount = $hasPayments ? $feeHeadsWithPayments[$feeHead['id']]['amount'] : ($isAssigned ? $assignedAmount : $feeHead['default_amount']);
                        $isLocked = $hasPayments || ($feeHead['is_mandatory'] && $isAssigned);
                    ?>
                    <div class="flex items-center justify-between p-3 border rounded <?php echo $hasPayments ? 'bg-yellow-50 border-yellow-300' : ($isAssigned ? 'bg-blue-50' : 'hover:bg-gray-50'); ?> transition">
                        <div class="flex items-center flex-1">
                            <!-- Checkbox for selection -->
                            <div class="mr-4">
                                <?php if ($isLocked): ?>
                                    <input type="checkbox" 
                                           id="fee_head_<?php echo $feeHead['id']; ?>"
                                           name="fee_heads[<?php echo $feeHead['id']; ?>]" 
                                           value="1"
                                           checked
                                           disabled
                                           class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <input type="hidden" name="fee_heads[<?php echo $feeHead['id']; ?>]" value="1">
                                <?php else: ?>
                                    <input type="checkbox" 
                                           id="fee_head_<?php echo $feeHead['id']; ?>"
                                           name="fee_heads[<?php echo $feeHead['id']; ?>]" 
                                           value="1"
                                           <?php echo $isAssigned ? 'checked' : ''; ?>
                                           class="fee-head-checkbox w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                           data-fee-head-id="<?php echo $feeHead['id']; ?>"
                                           data-default-amount="<?php echo $feeHead['default_amount']; ?>">
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <label class="font-semibold cursor-pointer" for="fee_head_<?php echo $feeHead['id']; ?>">
                                        <?php echo htmlspecialchars($feeHead['name']); ?>
                                    </label>
                                    <span class="text-xs px-2 py-1 rounded <?php echo $feeHead['is_mandatory'] ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600'; ?>">
                                        <?php echo $feeHead['is_mandatory'] ? 'Mandatory' : 'Optional'; ?>
                                    </span>
                                    <?php if ($hasPayments): ?>
                                    <span class="text-xs px-2 py-1 rounded bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-lock mr-1"></i>Has Payments
                                    </span>
                                    <?php elseif ($isAssigned): ?>
                                    <span class="text-xs px-2 py-1 rounded bg-blue-100 text-blue-800">
                                        <i class="fas fa-check mr-1"></i>Assigned
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($feeHead['description'])): ?>
                                <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($feeHead['description']); ?></p>
                                <?php endif; ?>
                                <?php if ($hasPayments): ?>
                                <p class="text-xs text-orange-600 mt-1">
                                    Amount (per fee structure): <?php echo formatCurrency($feeHeadsWithPayments[$feeHead['id']]['amount']); ?>
                                    <!-- Historical per-fee-head payment breakdown is hidden to avoid confusing decimal balances -->
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Read-only amount display -->
                        <div class="w-32 ml-4 text-right">
                            <div class="flex items-center justify-end">
                                <span class="text-sm text-gray-600 mr-2">KES</span>
                                <span class="text-sm font-semibold text-gray-700 fee-head-amount-display" 
                                      data-fee-head-id="<?php echo $feeHead['id']; ?>">
                                    <?php echo number_format($displayAmount, 2); ?>
                                </span>
                            </div>
                            <input type="hidden" 
                                   name="fee_head_amounts[<?php echo $feeHead['id']; ?>]" 
                                   class="fee-head-amount-input"
                                   data-fee-head-id="<?php echo $feeHead['id']; ?>"
                                   value="<?php echo $displayAmount; ?>">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-4">
                <p class="text-sm text-blue-800">
                    <strong>Total:</strong> <span id="totalAmount" class="font-bold text-lg">KES 0.00</span>
                </p>
            </div>
            
            <div class="flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/students/show/<?php echo $student['id']; ?>" 
                   class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Assign Fee Heads
                </button>
            </div>
            
            <div id="errorMessage" class="hidden mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
        </form>
    </div>
</div>

<script>
// Calculate total from selected fee heads
function calculateTotal() {
    let total = 0;
    document.querySelectorAll('.fee-head-checkbox:checked, input[type="hidden"][name^="fee_heads"]').forEach(checkbox => {
        const feeHeadId = checkbox.dataset.feeHeadId || checkbox.name.match(/\[(\d+)\]/)?.[1];
        if (feeHeadId) {
            const amountInput = document.querySelector(`input.fee-head-amount-input[data-fee-head-id="${feeHeadId}"]`);
            if (amountInput) {
                total += parseFloat(amountInput.value) || 0;
            }
        }
    });
    document.getElementById('totalAmount').textContent = 'KES ' + total.toLocaleString('en-KE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// Update amount display and total when checkbox is toggled
document.querySelectorAll('.fee-head-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const feeHeadId = this.dataset.feeHeadId;
        const defaultAmount = parseFloat(this.dataset.defaultAmount) || 0;
        const amountInput = document.querySelector(`input.fee-head-amount-input[data-fee-head-id="${feeHeadId}"]`);
        const amountDisplay = document.querySelector(`span.fee-head-amount-display[data-fee-head-id="${feeHeadId}"]`);
        const row = this.closest('.border.rounded');
        
        if (this.checked) {
            // Set to default amount when checked
            if (amountInput) amountInput.value = defaultAmount;
            if (amountDisplay) {
                amountDisplay.textContent = defaultAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }
            if (row && !row.classList.contains('bg-blue-50')) {
                row.classList.add('bg-blue-50');
            }
        } else {
            // Clear when unchecked
            if (amountInput) amountInput.value = 0;
            if (amountDisplay) {
                amountDisplay.textContent = '0.00';
            }
            if (row) {
                row.classList.remove('bg-blue-50');
            }
        }
        calculateTotal();
    });
});

// Initial calculation
calculateTotal();

// Term/Year change
document.getElementById('termSelect').addEventListener('change', function() {
    const term = this.value;
    const year = document.getElementById('yearSelect').value;
    window.location.href = '<?php echo BASE_URL; ?>/studentfees/assign/<?php echo $student['id']; ?>?term=' + term + '&academic_year=' + year;
});

document.getElementById('yearSelect').addEventListener('change', function() {
    const term = document.getElementById('termSelect').value;
    const year = this.value;
    window.location.href = '<?php echo BASE_URL; ?>/studentfees/assign/<?php echo $student['id']; ?>?term=' + term + '&academic_year=' + year;
});

// Form submission
document.getElementById('assignForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const errorDiv = document.getElementById('errorMessage');
    const submitBtn = this.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    errorDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/studentfees/saveAssignments/<?php echo $student['id']; ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = data.redirect || '<?php echo BASE_URL; ?>/students/show/<?php echo $student['id']; ?>';
        } else {
            errorDiv.textContent = data.message || 'Failed to assign fee heads';
            errorDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Assign Fee Heads';
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Assign Fee Heads';
    }
});
</script>

