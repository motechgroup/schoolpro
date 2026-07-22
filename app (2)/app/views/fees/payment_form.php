<form id="paymentForm" action="#" method="POST" onsubmit="return false;">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
    <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
    
    <div class="mb-4">
        <p class="text-sm text-gray-600">Student: <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong></p>
        <p class="text-sm text-gray-600">Invoice: <strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong></p>
        <p class="text-sm text-gray-600">Total Balance: <strong class="text-red-600"><?php echo formatCurrency($invoice['balance']); ?></strong></p>
    </div>
    
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Amount *</label>
            <input type="number" name="total_amount" id="totalAmount" required 
                   step="0.01" 
                   min="0.01"
                   max="<?php echo $invoice['balance']; ?>"
                   value=""
                   placeholder="Enter amount to pay (max: <?php echo formatCurrency($invoice['balance']); ?>)"
                   class="w-full border rounded px-3 py-2 font-semibold">
            <p class="text-xs text-gray-500 mt-1">
                <span class="font-semibold">Outstanding Balance: <?php echo formatCurrency($invoice['balance']); ?></span>
                <span class="ml-2 text-orange-600">⚠️ Please enter the actual amount paid (not the balance)</span>
            </p>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
            <select name="payment_method" id="paymentMethod" required class="w-full border rounded px-3 py-2">
                <option value="cash">Cash</option>
                <option value="mpesa">M-Pesa</option>
                <option value="equity">Equity Bank</option>
                <option value="coop">Co-operative Bank</option>
                <option value="kcb">KCB Bank</option>
                <option value="family_bank">Family Bank</option>
                <option value="bank">Other Bank Transfer</option>
                <option value="cheque">Cheque</option>
                <option value="other">Other</option>
            </select>
        </div>
        
        <div id="mpesaFields" class="hidden">
            <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-3">
                <p class="text-xs text-blue-800 mb-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>M-Pesa Payment Options:</strong>
                </p>
                <div class="space-y-2">
                    <div>
                        <button type="button" id="stkPushBtn" class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm font-semibold">
                            <i class="fas fa-mobile-alt mr-2"></i>Send STK Push Request
                        </button>
                        <p class="text-xs text-blue-700 mt-1">Click to send payment request to parent's phone</p>
                    </div>
                    <div class="border-t border-blue-300 pt-2 mt-2">
                        <p class="text-xs text-blue-800 font-semibold mb-1">OR Pay Manually via PayBill:</p>
                        <ol class="text-xs text-blue-700 list-decimal list-inside space-y-1">
                            <li>Go to M-Pesa Menu</li>
                            <li>Select "Pay Bill"</li>
                            <li>Enter PayBill Number: <strong id="paybillNumber">-</strong></li>
                            <li>Enter Account Number: <strong id="accountNumberFormat">-</strong></li>
                            <li>Enter Amount and complete payment</li>
                        </ol>
                    </div>
                </div>
                <p class="text-xs text-blue-800 mt-2 font-semibold">
                    <i class="fas fa-bell mr-1"></i>You will receive an SMS confirmation once payment is processed.
                </p>
            </div>
            <label class="block text-sm font-medium text-gray-700 mb-1">M-Pesa Phone Number</label>
            <input type="tel" name="phone_number" id="mpesaPhoneNumber"
                   placeholder="254700000000"
                   class="w-full border rounded px-3 py-2">
            <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">M-Pesa Transaction Code (Receipt Number)</label>
            <input type="text" name="mpesa_receipt" id="mpesaReceipt"
                   placeholder="Enter M-Pesa transaction code (e.g., QGH123456789)"
                   class="w-full border rounded px-3 py-2">
            <p class="text-xs text-gray-500 mt-1">Enter the M-Pesa transaction code from the SMS confirmation</p>
            
            <div id="reconcileSection" class="mt-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Amount <span class="text-red-500">*</span></label>
                <input type="number" id="reconcileAmount" 
                       step="0.01" min="0.01"
                       placeholder="Enter amount paid"
                       class="w-full border rounded px-3 py-2">
            </div>
            
            <div class="mt-3 flex gap-2">
                <button type="button" id="reconcileReceiptBtn" class="flex-1 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    <i class="fas fa-check-circle mr-1"></i>Reconcile Payment
                </button>
                <button type="button" id="stopPollingBtn" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 hidden">
                    <i class="fas fa-stop mr-1"></i>Stop Checking
                </button>
            </div>
            
            <div id="stkPushStatus" class="hidden mt-2 p-2 rounded text-sm"></div>
        </div>
        
        <div id="bankFields" class="hidden">
            <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account Number</label>
            <input type="text" name="bank_account" 
                   placeholder="Enter bank account number"
                   class="w-full border rounded px-3 py-2">
            <p class="text-xs text-gray-500 mt-1">Account number used for this payment</p>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
            <input type="text" name="reference_number" 
                   placeholder="Optional reference number"
                   class="w-full border rounded px-3 py-2">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
            <textarea name="remarks" rows="2" 
                      placeholder="Optional remarks"
                      class="w-full border rounded px-3 py-2"></textarea>
        </div>
        
        <!-- Fee Head Summary (read-only; allocation handled automatically based on saved fee heads) -->
        <?php if (!empty($feeHeadBreakdown)): ?>
        <div class="border-t pt-4 mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-3">
                <i class="fas fa-list mr-2"></i>Fee Heads Summary
            </label>
            <p class="text-xs text-gray-600 mb-3">
                The amounts below come from the fee structure saved for this student. Payments will be allocated automatically based on these balances.
            </p>
            
            <div class="space-y-2 max-h-64 overflow-y-auto">
                <?php foreach ($feeHeadBreakdown as $feeHead): ?>
                <div class="p-2 border rounded bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="font-semibold text-sm"><?php echo htmlspecialchars($feeHead['fee_head_name']); ?></p>
                            <div class="text-xs text-gray-600 mt-1">
                                <span>Amount (per fee structure): <?php echo formatCurrency($feeHead['amount']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="mt-6 flex justify-end space-x-4">
        <button type="button" onclick="closePaymentModal()" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
            Cancel
        </button>
        <button type="submit" id="submitPaymentBtn" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-save mr-2"></i><span id="submitBtnText">Record Payment</span>
        </button>
    </div>
    
    <div id="errorMessage" class="hidden mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
</form>

<script data-form-init="paymentForm">
// This script will be executed by the parent page after form is loaded
(function() {
    const form = document.getElementById('paymentForm');
    if (!form) return;
    
    // Load M-Pesa PayBill number and format account number
    fetch('<?php echo BASE_URL; ?>/settings/getPaymentSettings')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.settings) {
                const paybillEl = form.querySelector('#paybillNumber');
                const accountNumberEl = form.querySelector('#accountNumberFormat');
                
                if (paybillEl && data.settings.mpesa_paybill_number) {
                    paybillEl.textContent = data.settings.mpesa_paybill_number;
                }
                
                // Format account number: business_number#admission_number
                if (accountNumberEl) {
                    const paybillNumber = data.settings.mpesa_paybill_number || '';
                    const admissionNumber = '<?php echo htmlspecialchars($student['admission_number']); ?>';
                    const accountNumber = paybillNumber && admissionNumber ? paybillNumber + '#' + admissionNumber : admissionNumber;
                    accountNumberEl.textContent = accountNumber;
                }
            }
        })
        .catch(err => console.log('Could not load PayBill number'));
    
    // Show/hide payment method specific fields
    const paymentMethod = form.querySelector('#paymentMethod');
    const submitBtn = form.querySelector('#submitPaymentBtn');
    const submitBtnText = form.querySelector('#submitBtnText');
    const submitBtnIcon = submitBtn ? submitBtn.querySelector('i') : null;
    
    if (paymentMethod) {
        const mpesaFields = form.querySelector('#mpesaFields');
        const bankFields = form.querySelector('#bankFields');
        
        function updateSubmitButton() {
            if (submitBtn && submitBtnText) {
                if (paymentMethod.value === 'mpesa') {
                    submitBtnText.textContent = 'Confirm Payment';
                    submitBtn.className = 'bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700';
                    if (submitBtnIcon) {
                        submitBtnIcon.className = 'fas fa-check-circle mr-2';
                    }
                } else {
                    submitBtnText.textContent = 'Record Payment';
                    submitBtn.className = 'bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700';
                    if (submitBtnIcon) {
                        submitBtnIcon.className = 'fas fa-save mr-2';
                    }
                }
            }
        }
        
        paymentMethod.addEventListener('change', function() {
            // Hide all fields first
            if (mpesaFields) mpesaFields.classList.add('hidden');
            if (bankFields) bankFields.classList.add('hidden');
            
            // Show relevant fields
            if (this.value === 'mpesa' && mpesaFields) {
                mpesaFields.classList.remove('hidden');
            } else if (['equity', 'coop', 'kcb', 'family_bank', 'bank'].includes(this.value) && bankFields) {
                bankFields.classList.remove('hidden');
            }
            
            // Update submit button text/color
            updateSubmitButton();
        });
        
        // Trigger on load
        paymentMethod.dispatchEvent(new Event('change'));
    }
    
    // STK Push button handler
    const stkPushBtn = form.querySelector('#stkPushBtn');
    const stkPushStatus = form.querySelector('#stkPushStatus');
    const mpesaPhoneInput = form.querySelector('#mpesaPhoneNumber');
    const totalAmountInput = form.querySelector('#totalAmount');
    
    if (stkPushBtn) {
        stkPushBtn.addEventListener('click', function() {
            const phoneNumber = mpesaPhoneInput ? mpesaPhoneInput.value.trim() : '';
            const amount = totalAmountInput ? parseFloat(totalAmountInput.value) : 0;
            const invoiceId = form.querySelector('input[name="invoice_id"]')?.value || '';
            
            if (!phoneNumber) {
                showStkStatus('Please enter phone number', 'error');
                if (mpesaPhoneInput) mpesaPhoneInput.focus();
                return;
            }
            
            if (!amount || amount <= 0) {
                showStkStatus('Please enter payment amount', 'error');
                if (totalAmountInput) totalAmountInput.focus();
                return;
            }
            
            // Disable button and show loading
            stkPushBtn.disabled = true;
            stkPushBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
            showStkStatus('Sending STK Push request...', 'info');
            
            // Send STK Push request
            fetch('<?php echo BASE_URL; ?>/fees/initiateStkPush', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    invoice_id: invoiceId,
                    phone_number: phoneNumber,
                    amount: amount,
                    csrf_token: form.querySelector('input[name="csrf_token"]')?.value || ''
                })
            })
            .then(response => {
                // Always get text first to see what we're dealing with
                return response.text().then(text => {
                    // Check if response is actually JSON
                    const contentType = response.headers.get('content-type');
                    
                    // Try to parse as JSON first
                    if (contentType && contentType.includes('application/json')) {
                        try {
                            const data = JSON.parse(text);
                            return data;
                        } catch (e) {
                            console.error('STK Push - Failed to parse JSON:', text);
                            throw new Error('Invalid JSON response: ' + text.substring(0, 200));
                        }
                    }
                    
                    // Not JSON - log the full response for debugging
                    console.error('STK Push - Non-JSON response received:');
                    console.error('Status:', response.status);
                    console.error('Status Text:', response.statusText);
                    console.error('Content-Type:', contentType);
                    console.error('Response Text:', text);
                    
                    // Try to extract meaningful error message
                    let errorMsg = 'Server returned invalid response.';
                    if (text.includes('Fatal error') || text.includes('Parse error')) {
                        const match = text.match(/(Fatal error|Parse error)[^<]*/i);
                        errorMsg = match ? match[0].substring(0, 150) : 'PHP Error occurred';
                    } else if (text.includes('404') || text.includes('Not Found')) {
                        errorMsg = 'Endpoint not found (404). Please check the route: /fees/initiateStkPush';
                    } else if (text.includes('500') || text.includes('Internal Server Error')) {
                        errorMsg = 'Server error (500). Please check PHP error logs.';
                    } else if (text.trim().startsWith('<')) {
                        errorMsg = 'Server returned HTML instead of JSON. This usually means a PHP error occurred. Check browser console for details.';
                    } else if (text.length < 500) {
                        errorMsg = 'Server error: ' + text.substring(0, 200);
                    } else {
                        errorMsg = 'Server returned unexpected response. Check browser console (F12) for full details.';
                    }
                    
                    throw new Error(errorMsg);
                });
            })
            .then(data => {
                if (data.success) {
                    showStkStatus('STK Push sent successfully! Please complete payment on your phone...', 'info');
                    if (data.checkout_request_id) {
                        // Store checkout request ID for status polling
                        form.setAttribute('data-checkout-request-id', data.checkout_request_id);
                        // Start polling for payment status
                        startPaymentStatusPolling(data.checkout_request_id, invoiceId);
                    }
                } else {
                    showStkStatus('Failed: ' + (data.message || 'Unknown error'), 'error');
                    stkPushBtn.disabled = false;
                    stkPushBtn.innerHTML = '<i class="fas fa-mobile-alt mr-2"></i>Send STK Push Request';
                }
            })
            .catch(error => {
                console.error('STK Push error:', error);
                let errorMsg = 'Error: ' + error.message;
                if (error.message.includes('JSON')) {
                    errorMsg = 'Server returned invalid response. Please check your M-Pesa credentials in Settings or check the server logs.';
                }
                showStkStatus(errorMsg, 'error');
                stkPushBtn.disabled = false;
                stkPushBtn.innerHTML = '<i class="fas fa-mobile-alt mr-2"></i>Send STK Push Request';
            });
        });
    }
    
    // Reconcile receipt button handler
    const reconcileReceiptBtn = form.querySelector('#reconcileReceiptBtn');
    const mpesaReceiptInput = form.querySelector('#mpesaReceipt');
    const reconcileSection = form.querySelector('#reconcileSection');
    const reconcileAmountInput = form.querySelector('#reconcileAmount');
    
    // Show stop polling button when polling starts
    const stopPollingBtn = form.querySelector('#stopPollingBtn');
    
    // Show reconcile amount field when receipt is entered
    if (mpesaReceiptInput && reconcileSection) {
        mpesaReceiptInput.addEventListener('input', function() {
            // Always show reconcile section - it's now always visible
            // This is just for validation
        });
    }
    
    if (reconcileReceiptBtn && mpesaReceiptInput) {
        reconcileReceiptBtn.addEventListener('click', function() {
            // Stop polling if active
            stopPaymentPolling();
            
            const receiptNumber = mpesaReceiptInput.value.trim();
            const invoiceId = form.querySelector('input[name="invoice_id"]')?.value || '';
            const studentId = form.querySelector('input[name="student_id"]')?.value || '';
            const amount = reconcileAmountInput ? parseFloat(reconcileAmountInput.value) : 0;
            
            if (!receiptNumber) {
                showStkStatus('Please enter M-Pesa transaction code (receipt number)', 'error');
                mpesaReceiptInput.focus();
                return;
            }
            
            if (!amount || amount <= 0) {
                showStkStatus('Please enter the payment amount', 'error');
                if (reconcileAmountInput) reconcileAmountInput.focus();
                return;
            }
            
            if (!confirm('Reconcile this payment?\n\nAmount: KES ' + amount.toFixed(2) + '\nTransaction Code: ' + receiptNumber + '\n\nThis will create a payment record.')) {
                return;
            }
            
            reconcileReceiptBtn.disabled = true;
            reconcileReceiptBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Processing...';
            showStkStatus('Processing payment reconciliation...', 'info');
            
            fetch('<?php echo BASE_URL; ?>/mpesa/reconcileReceipt', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    invoice_id: invoiceId,
                    student_id: studentId,
                    receipt_number: receiptNumber,
                    amount: amount,
                    csrf_token: form.querySelector('input[name="csrf_token"]')?.value || ''
                })
            })
            .then(response => {
                // Check if response is actually JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Non-JSON response:', text);
                        throw new Error('Server returned non-JSON response. Check console for details.');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Stop polling if active
                    stopPaymentPolling();
                    
                    // Show success message
                    showStkStatus(
                        '<strong>✓ Payment Reconciled Successfully!</strong><br>' +
                        'Amount: KES ' + parseFloat(data.amount || amount).toFixed(2) + '<br>' +
                        'Receipt: ' + (data.receipt_number || receiptNumber) + '<br>' +
                        'M-Pesa Code: ' + receiptNumber + '<br>' +
                        '<small style="color: #059669;">Closing modal...</small>',
                        'success'
                    );
                    
                    // Disable reconcile button
                    reconcileReceiptBtn.disabled = true;
                    reconcileReceiptBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Payment Reconciled';
                    
                    // Close modal and reload after delay
                    setTimeout(function() {
                        if (typeof window.closePaymentModal === 'function') {
                            window.closePaymentModal();
                        } else {
                            const modal = document.getElementById('paymentModal');
                            if (modal) modal.classList.add('hidden');
                        }
                        setTimeout(function() {
                            if (typeof window.reloadPaymentData === 'function') {
                                window.reloadPaymentData();
                            } else {
                                location.reload();
                            }
                        }, 300);
                    }, 1500);
                } else {
                    if (data.requires_amount) {
                        // Amount required but not provided - show amount field
                        if (reconcileSection) reconcileSection.classList.remove('hidden');
                        if (reconcileAmountInput) {
                            reconcileAmountInput.focus();
                            reconcileAmountInput.value = data.invoice_balance || '';
                        }
                    }
                    showStkStatus('Failed: ' + (data.message || 'Receipt not found or already processed'), 'error');
                }
            })
            .catch(error => {
                showStkStatus('Error: ' + error.message + '. Please try again.', 'error');
            })
            .finally(() => {
                reconcileReceiptBtn.disabled = false;
                reconcileReceiptBtn.innerHTML = '<i class="fas fa-search mr-1"></i>Reconcile by Receipt Number';
            });
        });
    }
    
    function showStkStatus(message, type) {
        if (!stkPushStatus) return;
        stkPushStatus.classList.remove('hidden');
        stkPushStatus.className = 'mt-2 p-2 rounded text-sm';
        
        if (type === 'success') {
            stkPushStatus.classList.add('bg-green-100', 'text-green-800', 'border', 'border-green-300');
        } else if (type === 'error') {
            stkPushStatus.classList.add('bg-red-100', 'text-red-800', 'border', 'border-red-300');
        } else {
            stkPushStatus.classList.add('bg-blue-100', 'text-blue-800', 'border', 'border-blue-300');
        }
        
        stkPushStatus.innerHTML = message;
    }
    
    // Poll payment status after STK Push
    let pollingInterval = null;
    let pollAttempts = 0;
    const MAX_POLL_ATTEMPTS = 60; // Poll for up to 3 minutes (60 * 3 seconds)
    const POLL_INTERVAL = 3000; // Poll every 3 seconds to avoid API rate limits
    
    function startPaymentStatusPolling(checkoutRequestId, invoiceId) {
        // Clear any existing polling
        if (pollingInterval) {
            clearInterval(pollingInterval);
        }
        
        pollAttempts = 0;
        
        // Show stop polling button
        if (stopPollingBtn) {
            stopPollingBtn.classList.remove('hidden');
            stopPollingBtn.onclick = function() {
                stopPaymentPolling();
            };
        }
        
        // Start polling immediately, then every 3 seconds
        checkPaymentStatusOnce(checkoutRequestId, invoiceId);
        
        pollingInterval = setInterval(function() {
            pollAttempts++;
            
            // Reduce max attempts to 40 (2 minutes total) - faster timeout
            if (pollAttempts > 40) {
                stopPaymentPolling();
                showStkStatus(
                    '<strong>⚠️ Auto-check timed out</strong><br>' +
                    'If payment was successful, enter the M-Pesa transaction code above and click "Reconcile Payment".<br>' +
                    '<small>You can also refresh the page to check payment status.</small>', 
                    'error'
                );
                return;
            }
            
            checkPaymentStatusOnce(checkoutRequestId, invoiceId);
        }, 3000); // Poll every 3 seconds
    }
    
    function stopPaymentPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
        if (stopPollingBtn) {
            stopPollingBtn.classList.add('hidden');
        }
        if (stkPushBtn) {
            stkPushBtn.disabled = false;
            stkPushBtn.innerHTML = '<i class="fas fa-mobile-alt mr-2"></i>Send STK Push Request';
        }
    }
    
    function checkPaymentStatusOnce(checkoutRequestId, invoiceId) {
            
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
            if (data.success) {
                if (data.status === 'completed') {
                    // Payment confirmed!
                    if (pollingInterval) {
                        clearInterval(pollingInterval);
                        pollingInterval = null;
                    }
                    showStkStatus(
                        '<strong>✓ Payment Successful!</strong><br>' +
                        'Amount: KES ' + parseFloat(data.amount || 0).toFixed(2) + '<br>' +
                        'Receipt: ' + (data.receipt_number || 'N/A') + '<br>' +
                        'M-Pesa Receipt: ' + (data.mpesa_receipt || 'N/A') + '<br>' +
                        '<small style="color: #059669;">Closing modal...</small>',
                        'success'
                    );
                    
                    // Disable the button
                    if (stkPushBtn) {
                        stkPushBtn.disabled = true;
                        stkPushBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Payment Confirmed';
                    }
                    
                    // Close modal immediately after showing success message
                    setTimeout(function() {
                        // Try to close modal - check if function exists in parent scope
                        if (typeof window.closePaymentModal === 'function') {
                            window.closePaymentModal();
                        } else if (typeof window.parent !== 'undefined' && typeof window.parent.closePaymentModal === 'function') {
                            window.parent.closePaymentModal();
                        } else {
                            // Fallback: find and close modal directly
                            const modal = document.getElementById('paymentModal');
                            if (modal) {
                                modal.classList.add('hidden');
                            }
                            // Also try to find modal in parent window
                            if (window.parent) {
                                try {
                                    const parentModal = window.parent.document.getElementById('paymentModal');
                                    if (parentModal) {
                                        parentModal.classList.add('hidden');
                                    }
                                } catch (e) {
                                    // Cross-origin or other error, ignore
                                }
                            }
                        }
                        
                        // Reload page immediately after closing modal
                        setTimeout(function() {
                            if (typeof window.reloadPaymentData === 'function') {
                                window.reloadPaymentData();
                            } else {
                                location.reload();
                            }
                        }, 300);
                    }, 800); // Reduced from 1500ms to 800ms
                } else if (data.status === 'failed') {
                    // Payment failed
                    if (pollingInterval) {
                        clearInterval(pollingInterval);
                        pollingInterval = null;
                    }
                    showStkStatus('Payment failed: ' + (data.message || 'Unknown error'), 'error');
                    if (stkPushBtn) {
                        stkPushBtn.disabled = false;
                        stkPushBtn.innerHTML = '<i class="fas fa-mobile-alt mr-2"></i>Send STK Push Request';
                    }
                } else if (data.status === 'cancelled') {
                    // Payment cancelled
                    stopPaymentPolling();
                    showStkStatus('Payment was cancelled. You can try again or reconcile manually.', 'error');
                } else {
                    // Still pending - show status with manual option
                    if (pollAttempts % 5 === 0 || pollAttempts <= 3) {
                        const remaining = MAX_POLL_ATTEMPTS - pollAttempts;
                        showStkStatus(
                            '<strong>Waiting for payment confirmation...</strong><br>' +
                            'Attempt ' + pollAttempts + ' of ' + MAX_POLL_ATTEMPTS + ' (~' + Math.ceil(remaining * 3 / 60) + ' min remaining)<br>' +
                            '<small>If you\'ve already paid, enter the M-Pesa transaction code above and click "Reconcile Payment"</small>',
                            'info'
                        );
                    }
                }
            } else {
                // Error checking status - show after several attempts
                if (pollAttempts >= 10 && pollAttempts % 10 === 0) {
                    showStkStatus(
                        '<strong>Checking payment status...</strong> (' + pollAttempts + '/' + MAX_POLL_ATTEMPTS + ')<br>' +
                        '<small>If payment was successful, use manual reconciliation above.</small>', 
                        'info'
                    );
                }
            }
        })
        .catch(error => {
            // Network error - show after several attempts
            if (pollAttempts >= 10 && pollAttempts % 10 === 0) {
                showStkStatus(
                    '<strong>Connection issue</strong><br>' +
                    'Attempt ' + pollAttempts + ' of ' + MAX_POLL_ATTEMPTS + '<br>' +
                    '<small>If payment was successful, use manual reconciliation above.</small>', 
                    'error'
                );
            }
            
            // Stop polling after too many errors
            if (pollAttempts >= 30) {
                stopPaymentPolling();
                showStkStatus(
                    '<strong>⚠️ Unable to check payment status</strong><br>' +
                    'Please enter the M-Pesa transaction code above and click "Reconcile Payment" to process manually.',
                    'error'
                );
            }
        });
    }
    
    // No manual fee-head allocation JS: allocation is handled on the server based on saved fee heads
})();
</script>
