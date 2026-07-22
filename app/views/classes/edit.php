<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Edit Class</h1>
    
    <div class="bg-white rounded-lg shadow p-6">
        <form id="classForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Grade *</label>
                    <select name="grade_id" required class="w-full border rounded px-3 py-2">
                        <option value="">Select Grade</option>
                        <?php foreach ($grades as $grade): ?>
                        <option value="<?php echo $grade['id']; ?>" <?php echo ($class['grade_id'] == $grade['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($grade['display_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Class Name *</label>
                    <input type="text" name="name" required 
                           value="<?php echo htmlspecialchars($class['name']); ?>"
                           class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year *</label>
                    <input type="text" name="academic_year" required 
                           value="<?php echo htmlspecialchars($class['academic_year']); ?>"
                           placeholder="2024/2025" 
                           class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Class Teacher</label>
                    <select name="class_teacher_id" class="w-full border rounded px-3 py-2">
                        <option value="">Not assigned</option>
                        <?php foreach ($teachers as $teacher): ?>
                        <option value="<?php echo $teacher['id']; ?>" <?php echo ($class['class_teacher_id'] == $teacher['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                            <?php if ($teacher['tsc_number']): ?>
                                (TSC: <?php echo htmlspecialchars($teacher['tsc_number']); ?>)
                            <?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Assign a teacher to manage this class</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                    <input type="number" name="capacity" value="<?php echo $class['capacity']; ?>" 
                           min="1" max="60" class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border rounded px-3 py-2">
                        <option value="active" <?php echo ($class['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="archived" <?php echo ($class['status'] == 'archived') ? 'selected' : ''; ?>>Archived</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/classes/show/<?php echo $class['id']; ?>" 
                   class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Update Class
                </button>
            </div>
            
            <div id="errorMessage" class="hidden mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
        </form>
    </div>
</div>

<script>
document.getElementById('classForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const errorDiv = document.getElementById('errorMessage');
    const submitBtn = this.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
    errorDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/classes/update/<?php echo $class['id']; ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = data.redirect || '<?php echo BASE_URL; ?>/classes/show/<?php echo $class['id']; ?>';
        } else {
            let errorMsg = data.message || 'Failed to update class';
            if (data.errors) {
                errorMsg += ': ' + Object.values(data.errors).join(', ');
            }
            errorDiv.textContent = errorMsg;
            errorDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update Class';
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update Class';
    }
});
</script>

