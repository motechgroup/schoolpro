<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Add New Parent/Guardian</h1>
        <a href="<?php echo BASE_URL; ?>/parents" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <form id="parentForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <h2 class="text-xl font-bold mb-4">Parent/Guardian Information</h2>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                    <input type="text" name="first_name" required class="w-full border rounded px-3 py-2" 
                           placeholder="Enter first name">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                    <input type="text" name="last_name" required class="w-full border rounded px-3 py-2" 
                           placeholder="Enter last name">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                    <input type="tel" name="phone" required class="w-full border rounded px-3 py-2" 
                           placeholder="+254700000000 or 0700000000">
                    <p class="text-xs text-gray-500 mt-1">Kenyan format: +254 or 0 followed by 9 digits</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alternate Phone</label>
                    <input type="tel" name="phone_alt" class="w-full border rounded px-3 py-2" 
                           placeholder="+254700000000 or 0700000000">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" class="w-full border rounded px-3 py-2" 
                           placeholder="email@example.com">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ID Number</label>
                    <input type="text" name="id_number" class="w-full border rounded px-3 py-2" 
                           placeholder="National ID number">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Relationship</label>
                    <select name="relationship" class="w-full border rounded px-3 py-2">
                        <option value="father">Father</option>
                        <option value="mother">Mother</option>
                        <option value="guardian" selected>Guardian</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Occupation</label>
                    <input type="text" name="occupation" class="w-full border rounded px-3 py-2" 
                           placeholder="Enter occupation">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="address" rows="3" class="w-full border rounded px-3 py-2" 
                              placeholder="Enter full address"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border rounded px-3 py-2">
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/parents" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Save Parent
                </button>
            </div>
            
            <div id="errorMessage" class="hidden mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
        </form>
    </div>
</div>

<script>
document.getElementById('parentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const errorDiv = document.getElementById('errorMessage');
    const submitBtn = this.querySelector('button[type="submit"]');
    
    errorDiv.classList.add('hidden');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/parents/store', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.href = '<?php echo BASE_URL; ?>/parents';
            }
        } else {
            let errorMsg = data.message || 'Failed to save parent';
            if (data.errors) {
                const errorList = Object.values(data.errors).join('<br>');
                errorMsg += '<br>' + errorList;
            }
            errorDiv.innerHTML = errorMsg;
            errorDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Parent';
        }
    } catch (error) {
        errorDiv.innerHTML = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Parent';
    }
});
</script>

