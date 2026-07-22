<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Add New Subject</h1>
            <a href="<?php echo BASE_URL; ?>/subjects" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to Subjects
            </a>
        </div>
        
        <form id="subjectForm" class="bg-white rounded-lg shadow p-6">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject Code *</label>
                    <input type="text" name="code" required 
                           placeholder="e.g., ENG, MAT, KIS"
                           class="w-full border rounded px-3 py-2"
                           maxlength="20">
                    <p class="text-xs text-gray-500 mt-1">Unique code for this subject (e.g., ENG for English)</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject Name *</label>
                    <input type="text" name="name" required 
                           placeholder="e.g., English Language"
                           class="w-full border rounded px-3 py-2"
                           maxlength="100">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Grade *</label>
                    <select name="grade_id" required class="w-full border rounded px-3 py-2">
                        <option value="">Select Grade</option>
                        <?php foreach ($grades as $grade): ?>
                        <option value="<?php echo $grade['id']; ?>" 
                                <?php echo ($selectedGradeId == $grade['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($grade['display_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Select the grade level for this subject</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="4" 
                              placeholder="Optional description of the subject"
                              class="w-full border rounded px-3 py-2"></textarea>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/subjects" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Create Subject
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('subjectForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        fetch('<?php echo BASE_URL; ?>/subjects/store', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });
});
</script>

