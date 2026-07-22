<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Edit Teacher</h1>
    
    <div class="bg-white rounded-lg shadow p-6">
        <form id="teacherForm" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="space-y-6">
                <div>
                    <h2 class="text-xl font-bold mb-4">Personal Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teacher Photo</label>
                            <?php 
                            $photoUrl = !empty($teacher['photo']) ? getImageUrl($teacher['photo']) : null;
                            if ($photoUrl): 
                            ?>
                            <div class="mb-2">
                                <img src="<?php echo htmlspecialchars($photoUrl); ?>" 
                                     alt="Current Photo" class="w-32 h-32 object-cover rounded border"
                                     onerror="this.style.display='none'; this.parentElement.querySelector('p').textContent='Photo not found';">
                                <p class="text-xs text-gray-500 mt-1">Current photo</p>
                            </div>
                            <?php endif; ?>
                            <input type="file" name="photo" accept="image/jpeg,image/png,image/gif" class="w-full border rounded px-3 py-2">
                            <p class="text-xs text-gray-500 mt-1">Accepted formats: JPEG, PNG, GIF (Max 5MB). Leave empty to keep current photo.</p>
                            <div id="photoPreview" class="mt-2 hidden">
                                <img id="previewImage" src="" alt="Preview" class="w-32 h-32 object-cover rounded border">
                                <p class="text-xs text-gray-500 mt-1">New photo preview</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                            <input type="text" name="first_name" required 
                                   value="<?php echo htmlspecialchars($teacher['first_name']); ?>"
                                   class="w-full border rounded px-3 py-2">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                            <input type="text" name="last_name" required 
                                   value="<?php echo htmlspecialchars($teacher['last_name']); ?>"
                                   class="w-full border rounded px-3 py-2">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input type="email" name="email" required 
                                   value="<?php echo htmlspecialchars($teacher['email'] ?? ''); ?>"
                                   class="w-full border rounded px-3 py-2">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="tel" name="phone" 
                                   value="<?php echo htmlspecialchars($teacher['phone'] ?? ''); ?>"
                                   class="w-full border rounded px-3 py-2">
                        </div>
                    </div>
                </div>
                
                <div>
                    <h2 class="text-xl font-bold mb-4">Account Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" name="password" 
                                   minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                                   class="w-full border rounded px-3 py-2">
                            <p class="text-xs text-gray-500 mt-1">Leave blank to keep current password</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full border rounded px-3 py-2">
                                <option value="active" <?php echo ($teacher['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($teacher['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                <option value="retired" <?php echo ($teacher['status'] == 'retired') ? 'selected' : ''; ?>>Retired</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h2 class="text-xl font-bold mb-4">Professional Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">TSC Number</label>
                            <input type="text" name="tsc_number" 
                                   value="<?php echo htmlspecialchars($teacher['tsc_number'] ?? ''); ?>"
                                   class="w-full border rounded px-3 py-2">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Employment Date</label>
                            <input type="date" name="employment_date" 
                                   value="<?php echo htmlspecialchars($teacher['employment_date'] ?? ''); ?>"
                                   class="w-full border rounded px-3 py-2">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Qualification</label>
                            <input type="text" name="qualification" 
                                   value="<?php echo htmlspecialchars($teacher['qualification'] ?? ''); ?>"
                                   class="w-full border rounded px-3 py-2">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Specialization</label>
                            <input type="text" name="specialization" 
                                   value="<?php echo htmlspecialchars($teacher['specialization'] ?? ''); ?>"
                                   class="w-full border rounded px-3 py-2">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/teachers/show/<?php echo $teacher['id']; ?>" 
                   class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Update Teacher
                </button>
            </div>
            
            <div id="errorMessage" class="hidden mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
        </form>
    </div>
</div>

<script>
// Photo preview
document.querySelector('input[name="photo"]').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('photoPreview').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('photoPreview').classList.add('hidden');
    }
});

document.getElementById('teacherForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const errorDiv = document.getElementById('errorMessage');
    const submitBtn = this.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
    errorDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/teachers/update/<?php echo $teacher['id']; ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = data.redirect || '<?php echo BASE_URL; ?>/teachers/show/<?php echo $teacher['id']; ?>';
        } else {
            let errorMsg = data.message || 'Failed to update teacher';
            if (data.errors) {
                errorMsg += ': ' + Object.values(data.errors).join(', ');
            }
            errorDiv.textContent = errorMsg;
            errorDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update Teacher';
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update Teacher';
    }
});
</script>

