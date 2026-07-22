<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Create Announcement</h1>
    
    <div class="bg-white rounded-lg shadow p-6">
        <form id="announcementForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                    <input type="text" name="title" required 
                           class="w-full border rounded px-3 py-2" 
                           placeholder="Enter announcement title">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Content *</label>
                    <textarea name="content" required rows="10" 
                              class="w-full border rounded px-3 py-2" 
                              placeholder="Enter announcement content"></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Target Audience</label>
                        <select name="target_audience" class="w-full border rounded px-3 py-2">
                            <option value="all">All</option>
                            <option value="parents">Parents</option>
                            <option value="students">Students</option>
                            <option value="teachers">Teachers</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select name="priority" class="w-full border rounded px-3 py-2">
                            <option value="low">Low</option>
                            <option value="normal" selected>Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full border rounded px-3 py-2">
                            <option value="draft">Draft</option>
                            <option value="published">Publish Now</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/announcements" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Create Announcement
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
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';
    errorDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/announcements/store', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = data.redirect || '<?php echo BASE_URL; ?>/announcements';
        } else {
            let errorMsg = data.message || 'Failed to create announcement';
            if (data.errors) {
                errorMsg += ': ' + Object.values(data.errors).join(', ');
            }
            errorDiv.textContent = errorMsg;
            errorDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Create Announcement';
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Create Announcement';
    }
});
</script>

