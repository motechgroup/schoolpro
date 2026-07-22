<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Edit Email Template</h1>
            <a href="<?php echo BASE_URL; ?>/emailtemplates" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to Templates
            </a>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <form id="templateForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Template Name *</label>
                    <input type="text" name="name" required
                           value="<?php echo htmlspecialchars($template['name']); ?>"
                           class="w-full border rounded px-3 py-2"
                           placeholder="e.g., Fee Payment Reminder">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select name="category" class="w-full border rounded px-3 py-2">
                        <option value="fee" <?php echo $template['category'] === 'fee' ? 'selected' : ''; ?>>Fee Related</option>
                        <option value="academic" <?php echo $template['category'] === 'academic' ? 'selected' : ''; ?>>Academic</option>
                        <option value="announcement" <?php echo $template['category'] === 'announcement' ? 'selected' : ''; ?>>Announcement</option>
                        <option value="general" <?php echo $template['category'] === 'general' ? 'selected' : ''; ?>>General</option>
                        <option value="custom" <?php echo $template['category'] === 'custom' ? 'selected' : ''; ?>>Custom</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Subject *</label>
                    <input type="text" name="subject" required
                           value="<?php echo htmlspecialchars($template['subject']); ?>"
                           class="w-full border rounded px-3 py-2"
                           placeholder="e.g., Fee Payment Reminder - {school_name}">
                    <p class="text-xs text-gray-500 mt-1">Use variables like {parent_name}, {student_name}, {school_name}, etc.</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Body *</label>
                    <textarea name="body" rows="15" required
                              class="w-full border rounded px-3 py-2 font-mono text-sm"
                              placeholder="Enter email body here. Use HTML for formatting and variables like {parent_name}, {student_name}, etc."><?php echo htmlspecialchars($template['body']); ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        HTML is supported. Use variables like {parent_name}, {student_name}, {admission_number}, {balance_amount}, etc.
                    </p>
                </div>
                
                <div class="mb-4 p-3 bg-blue-50 rounded border border-blue-200">
                    <p class="text-xs font-semibold text-blue-800 mb-2">Available Variables:</p>
                    <div class="grid grid-cols-2 gap-2 text-xs text-blue-700">
                        <div><code>{parent_name}</code> - Parent's full name</div>
                        <div><code>{student_name}</code> - Student's full name</div>
                        <div><code>{admission_number}</code> - Student admission number</div>
                        <div><code>{class_name}</code> - Class name</div>
                        <div><code>{grade_name}</code> - Grade name</div>
                        <div><code>{school_name}</code> - School name</div>
                        <div><code>{balance_amount}</code> - Fee balance amount</div>
                        <div><code>{payment_amount}</code> - Payment amount</div>
                        <div><code>{receipt_number}</code> - Receipt number</div>
                        <div><code>{term}</code> - Current term</div>
                        <div><code>{academic_year}</code> - Academic year</div>
                        <div><code>{payment_date}</code> - Payment date</div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" <?php echo $template['is_active'] ? 'checked' : ''; ?> class="mr-2">
                        <span class="text-sm text-gray-700">Active (template will be available for use)</span>
                    </label>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <a href="<?php echo BASE_URL; ?>/emailtemplates" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Update Template
                    </button>
                </div>
            </form>
            
            <div id="result" class="mt-4 hidden"></div>
        </div>
    </div>
</div>

<script>
document.getElementById('templateForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const resultDiv = document.getElementById('result');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    resultDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/emailtemplates/update/<?php echo $template['id']; ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                resultDiv.className = 'mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded';
                resultDiv.innerHTML = '<strong>Success!</strong> ' + data.message;
                resultDiv.classList.remove('hidden');
            }
        } else {
            resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            resultDiv.innerHTML = '<strong>Error:</strong> ' + (data.message || 'Failed to update template');
            resultDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update Template';
        }
    } catch (error) {
        resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
        resultDiv.innerHTML = '<strong>Error:</strong> ' + error.message;
        resultDiv.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update Template';
    }
});
</script>

