<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Create New Grade</h1>
    
    <div class="bg-white rounded-lg shadow p-6">
        <form id="gradeForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Grade Code *</label>
                    <input type="text" name="name" required 
                           placeholder="e.g., PP1, PP2, G1, G2" 
                           class="w-full border rounded px-3 py-2" maxlength="10">
                    <p class="text-xs text-gray-500 mt-1">Short code (e.g., PP1, G1, G2)</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Display Name *</label>
                    <input type="text" name="display_name" required 
                           placeholder="e.g., Pre-Primary 1, Grade 1" 
                           class="w-full border rounded px-3 py-2">
                    <p class="text-xs text-gray-500 mt-1">Full display name</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Level *</label>
                    <input type="number" name="level" required 
                           min="1" max="20"
                           placeholder="e.g., 1, 2, 3" 
                           class="w-full border rounded px-3 py-2">
                    <p class="text-xs text-gray-500 mt-1">Numeric level for ordering (1 = lowest, higher = advanced)</p>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" 
                              placeholder="Optional description of the grade"
                              class="w-full border rounded px-3 py-2"></textarea>
                </div>
            </div>
            
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded p-4">
                <h3 class="font-semibold text-blue-900 mb-2">Kenyan Education System Reference:</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold text-blue-900 mb-1">CBC Primary:</h4>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li>• PP1 (Pre-Primary 1) - Level 1</li>
                            <li>• PP2 (Pre-Primary 2) - Level 2</li>
                            <li>• G1 (Grade 1) - Level 3</li>
                            <li>• G2 (Grade 2) - Level 4</li>
                            <li>• G3 (Grade 3) - Level 5</li>
                            <li>• G4 (Grade 4) - Level 6</li>
                            <li>• G5 (Grade 5) - Level 7</li>
                            <li>• G6 (Grade 6) - Level 8</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-blue-900 mb-1">Junior Secondary (JSS):</h4>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li>• G7 (Grade 7 - JSS) - Level 9</li>
                            <li>• G8 (Grade 8 - JSS) - Level 10</li>
                            <li>• G9 (Grade 9 - JSS) - Level 11</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/grades" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Create Grade
                </button>
            </div>
            
            <div id="errorMessage" class="hidden mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
        </form>
    </div>
</div>

<script>
document.getElementById('gradeForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const errorDiv = document.getElementById('errorMessage');
    const submitBtn = this.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';
    errorDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/grades/store', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = data.redirect || '<?php echo BASE_URL; ?>/grades';
        } else {
            let errorMsg = data.message || 'Failed to create grade';
            if (data.errors) {
                errorMsg += ': ' + Object.values(data.errors).join(', ');
            }
            errorDiv.textContent = errorMsg;
            errorDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Create Grade';
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Create Grade';
    }
});
</script>

