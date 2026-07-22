<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Create Fee Head</h1>
    
    <div class="bg-white rounded-lg shadow p-6">
        <form id="feeHeadForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Code *</label>
                    <input type="text" name="code" required 
                           placeholder="e.g., TUITION, LUNCH, TRANSPORT" 
                           class="w-full border rounded px-3 py-2" maxlength="20">
                    <p class="text-xs text-gray-500 mt-1">Unique code (uppercase, no spaces)</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input type="text" name="name" required 
                           placeholder="e.g., Tuition Fees, Lunch Fees" 
                           class="w-full border rounded px-3 py-2">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" 
                              placeholder="Optional description of the fee head"
                              class="w-full border rounded px-3 py-2"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Amount</label>
                    <input type="number" name="default_amount" step="0.01" min="0" value="0.00"
                           placeholder="0.00" 
                           class="w-full border rounded px-3 py-2">
                    <p class="text-xs text-gray-500 mt-1">Default amount (can be overridden per student)</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mandatory</label>
                    <div class="mt-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_mandatory" value="1" 
                                   class="form-checkbox h-5 w-5 text-blue-600">
                            <span class="ml-2 text-sm text-gray-700">This fee head is mandatory for all students</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded p-4">
                <h3 class="font-semibold text-blue-900 mb-2">Common Fee Heads:</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• <strong>TUITION</strong> - Main tuition fees (usually mandatory)</li>
                    <li>• <strong>LUNCH</strong> - School lunch program fees</li>
                    <li>• <strong>TRANSPORT</strong> - School transport/bus fees</li>
                    <li>• <strong>LIBRARY</strong> - Library and reading materials</li>
                    <li>• <strong>SPORTS</strong> - Sports and games activities</li>
                    <li>• <strong>MEDICAL</strong> - Medical and health services</li>
                    <li>• <strong>EXAM</strong> - Examination and assessment fees</li>
                    <li>• <strong>UNIFORM</strong> - School uniform fees</li>
                    <li>• <strong>BUILDING</strong> - School infrastructure development</li>
                    <li>• <strong>PTA</strong> - Parent Teacher Association fees</li>
                </ul>
            </div>
            
            <div class="mt-6 flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/feeheads" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Create Fee Head
                </button>
            </div>
            
            <div id="errorMessage" class="hidden mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
        </form>
    </div>
</div>

<script>
document.getElementById('feeHeadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const errorDiv = document.getElementById('errorMessage');
    const submitBtn = this.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';
    errorDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/feeheads/store', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = data.redirect || '<?php echo BASE_URL; ?>/feeheads';
        } else {
            let errorMsg = data.message || 'Failed to create fee head';
            if (data.errors) {
                errorMsg += ': ' + Object.values(data.errors).join(', ');
            }
            errorDiv.textContent = errorMsg;
            errorDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Create Fee Head';
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Create Fee Head';
    }
});
</script>

