<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Edit Announcement</h1>
    
    <div class="bg-white rounded-lg shadow p-6">
        <form id="announcementForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                    <input type="text" name="title" required 
                           value="<?php echo htmlspecialchars($announcement['title']); ?>"
                           class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Content *</label>
                    <textarea name="content" required rows="10" 
                              class="w-full border rounded px-3 py-2"><?php echo htmlspecialchars($announcement['content']); ?></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Target Audience</label>
                        <select name="target_audience" class="w-full border rounded px-3 py-2">
                            <option value="all" <?php echo ($announcement['target_audience'] == 'all') ? 'selected' : ''; ?>>All</option>
                            <option value="parents" <?php echo ($announcement['target_audience'] == 'parents') ? 'selected' : ''; ?>>Parents</option>
                            <option value="students" <?php echo ($announcement['target_audience'] == 'students') ? 'selected' : ''; ?>>Students</option>
                            <option value="teachers" <?php echo ($announcement['target_audience'] == 'teachers') ? 'selected' : ''; ?>>Teachers</option>
                            <option value="staff" <?php echo ($announcement['target_audience'] == 'staff') ? 'selected' : ''; ?>>Staff</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select name="priority" class="w-full border rounded px-3 py-2">
                            <option value="low" <?php echo ($announcement['priority'] == 'low') ? 'selected' : ''; ?>>Low</option>
                            <option value="normal" <?php echo ($announcement['priority'] == 'normal') ? 'selected' : ''; ?>>Normal</option>
                            <option value="high" <?php echo ($announcement['priority'] == 'high') ? 'selected' : ''; ?>>High</option>
                            <option value="urgent" <?php echo ($announcement['priority'] == 'urgent') ? 'selected' : ''; ?>>Urgent</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full border rounded px-3 py-2">
                            <option value="draft" <?php echo ($announcement['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo ($announcement['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                            <option value="archived" <?php echo ($announcement['status'] == 'archived') ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/announcements/show/<?php echo $announcement['id']; ?>" 
                   class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Update Announcement
                </button>
            </div>
            
            <div id="errorMessage" class="hidden mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
        </form>
    </div>
</div>

<script>
document.getElementById('announcementForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const errorDiv = document.getElementById('errorMessage');
    const submitBtn = this.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
    errorDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/announcements/update/<?php echo $announcement['id']; ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = data.redirect || '<?php echo BASE_URL; ?>/announcements/show/<?php echo $announcement['id']; ?>';
        } else {
            let errorMsg = data.message || 'Failed to update announcement';
            if (data.errors) {
                errorMsg += ': ' + Object.values(data.errors).join(', ');
            }
            errorDiv.textContent = errorMsg;
            errorDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update Announcement';
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update Announcement';
    }
});
</script>

