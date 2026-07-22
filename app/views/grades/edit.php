<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Edit Grade</h1>
    
    <div class="bg-white rounded-lg shadow p-6">
        <form id="gradeForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Grade Code *</label>
                    <input type="text" name="name" required 
                           value="<?php echo htmlspecialchars($grade['name']); ?>"
                           class="w-full border rounded px-3 py-2" maxlength="10">
                    <p class="text-xs text-gray-500 mt-1">Short code (e.g., PP1, G1, G2)</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Display Name *</label>
                    <input type="text" name="display_name" required 
                           value="<?php echo htmlspecialchars($grade['display_name']); ?>"
                           class="w-full border rounded px-3 py-2">
                    <p class="text-xs text-gray-500 mt-1">Full display name</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Level *</label>
                    <input type="number" name="level" required 
                           value="<?php echo $grade['level']; ?>"
                           min="1" max="20"
                           class="w-full border rounded px-3 py-2">
                    <p class="text-xs text-gray-500 mt-1">Numeric level for ordering</p>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" 
                              class="w-full border rounded px-3 py-2"><?php echo htmlspecialchars($grade['description'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/grades/show/<?php echo $grade['id']; ?>" 
                   class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Update Grade
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
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
    errorDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/grades/update/<?php echo $grade['id']; ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = data.redirect || '<?php echo BASE_URL; ?>/grades/show/<?php echo $grade['id']; ?>';
        } else {
            let errorMsg = data.message || 'Failed to update grade';
            if (data.errors) {
                errorMsg += ': ' + Object.values(data.errors).join(', ');
            }
            errorDiv.textContent = errorMsg;
            errorDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update Grade';
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update Grade';
    }
});
</script>

