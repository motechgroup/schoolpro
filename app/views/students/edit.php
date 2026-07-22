<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Edit Student</h1>
    
    <div class="bg-white rounded-lg shadow p-6">
        <form id="studentForm" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <h2 class="text-xl font-bold mb-4">Student Information</h2>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Admission Number *</label>
                    <input type="text" name="admission_number" value="<?php echo htmlspecialchars($student['admission_number']); ?>" 
                           required class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">UPI</label>
                    <input type="text" value="<?php echo htmlspecialchars($student['upi'] ?? ''); ?>" 
                           disabled class="w-full border rounded px-3 py-2 bg-gray-100">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" 
                           required class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                    <input type="text" name="middle_name" value="<?php echo htmlspecialchars($student['middle_name'] ?? ''); ?>" 
                           class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" 
                           required class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gender *</label>
                    <select name="gender" required class="w-full border rounded px-3 py-2">
                        <option value="male" <?php echo ($student['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo ($student['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth *</label>
                    <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($student['date_of_birth']); ?>" 
                           required class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Admission Date *</label>
                    <input type="date" name="admission_date" value="<?php echo htmlspecialchars($student['admission_date']); ?>" 
                           required class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Class *</label>
                    <select name="class_id" required class="w-full border rounded px-3 py-2">
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo ($student['class_id'] == $class['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['grade_display_name'] . ' - ' . $class['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                    <select name="status" required class="w-full border rounded px-3 py-2">
                        <option value="active" <?php echo ($student['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="alumni" <?php echo ($student['status'] == 'alumni') ? 'selected' : ''; ?>>Alumni</option>
                        <option value="transferred" <?php echo ($student['status'] == 'transferred') ? 'selected' : ''; ?>>Transferred</option>
                        <option value="suspended" <?php echo ($student['status'] == 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Student Photo</label>
                    <?php 
                    $photoUrl = !empty($student['photo']) ? getImageUrl($student['photo']) : null;
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
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Medical Information</label>
                    <textarea name="medical_info" rows="3" class="w-full border rounded px-3 py-2"><?php echo htmlspecialchars($student['medical_info'] ?? ''); ?></textarea>
                </div>
                
                <div class="md:col-span-2 mt-6">
                    <h2 class="text-xl font-bold mb-4">Parent/Guardian Information</h2>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parent ID *</label>
                    <input type="number" name="parent_id" value="<?php echo htmlspecialchars($student['parent_id']); ?>" 
                           required class="w-full border rounded px-3 py-2">
                    <p class="text-xs text-gray-500 mt-1">Enter parent ID from parent records</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Relationship</label>
                    <select name="parent_relationship" class="w-full border rounded px-3 py-2">
                        <option value="father" <?php echo ($student['parent_relationship'] == 'father') ? 'selected' : ''; ?>>Father</option>
                        <option value="mother" <?php echo ($student['parent_relationship'] == 'mother') ? 'selected' : ''; ?>>Mother</option>
                        <option value="guardian" <?php echo ($student['parent_relationship'] == 'guardian') ? 'selected' : ''; ?>>Guardian</option>
                        <option value="other" <?php echo ($student['parent_relationship'] == 'other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/students/show/<?php echo $student['id']; ?>" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Update Student
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

document.getElementById('studentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const errorDiv = document.getElementById('errorMessage');
    const submitBtn = this.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
    errorDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/students/update/<?php echo $student['id']; ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = data.redirect || '<?php echo BASE_URL; ?>/students/show/<?php echo $student['id']; ?>';
        } else {
            let errorMsg = data.message || 'Failed to update student';
            if (data.errors) {
                errorMsg += ': ' + Object.values(data.errors).join(', ');
            }
            errorDiv.textContent = errorMsg;
            errorDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update Student';
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update Student';
    }
});
</script>

