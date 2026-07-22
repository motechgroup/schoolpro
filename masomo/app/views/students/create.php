<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Add New Student</h1>
    
    <div class="bg-white rounded-lg shadow p-6">
        <form id="studentForm" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Student Information -->
                <div class="md:col-span-2">
                    <h2 class="text-xl font-bold mb-4">Student Information</h2>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Admission Number</label>
                    <input type="text" name="admission_number" class="w-full border rounded px-3 py-2" placeholder="Auto-generated (starts from 100)">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to auto-generate sequential number starting from 100</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                    <input type="text" name="first_name" required class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                    <input type="text" name="middle_name" class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                    <input type="text" name="last_name" required class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gender *</label>
                    <select name="gender" required class="w-full border rounded px-3 py-2">
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth *</label>
                    <input type="date" name="date_of_birth" required class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Admission Date *</label>
                    <input type="date" name="admission_date" required class="w-full border rounded px-3 py-2" value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Class *</label>
                    <select name="class_id" required class="w-full border rounded px-3 py-2">
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>">
                            <?php echo htmlspecialchars($class['grade_display_name'] . ' - ' . $class['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Student Photo</label>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/gif" class="w-full border rounded px-3 py-2">
                    <p class="text-xs text-gray-500 mt-1">Accepted formats: JPEG, PNG, GIF (Max 5MB). Recommended size: 400x400px</p>
                    <div id="photoPreview" class="mt-2 hidden">
                        <img id="previewImage" src="" alt="Preview" class="w-32 h-32 object-cover rounded border">
                    </div>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Medical Information</label>
                    <textarea name="medical_info" rows="3" class="w-full border rounded px-3 py-2"></textarea>
                </div>
                
                <!-- Parent Information -->
                <div class="md:col-span-2 mt-6">
                    <h2 class="text-xl font-bold mb-2">Parent/Guardian Information</h2>
                    <p class="text-xs text-gray-600 mb-3">
                        You can either select an existing parent from the list below or enter details to create a new parent.
                    </p>
                </div>

                <!-- Existing parent selector -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Existing Parent (optional)</label>
                    <select name="parent_id" id="parentSelect" class="w-full border rounded px-3 py-2">
                        <option value="">-- Select existing parent --</option>
                        <?php if (!empty($parents)): ?>
                            <?php foreach ($parents as $parent): ?>
                                <option value="<?php echo $parent['id']; ?>">
                                    <?php echo htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name'] . ' - ' . $parent['phone']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Leave blank to create a new parent record for this student.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parent First Name *</label>
                    <input type="text" name="parent_first_name" required class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parent Last Name *</label>
                    <input type="text" name="parent_last_name" required class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                    <input type="tel" name="parent_phone" required class="w-full border rounded px-3 py-2" placeholder="+254700000000">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="parent_email" class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Relationship</label>
                    <select name="parent_relationship" class="w-full border rounded px-3 py-2">
                        <option value="father">Father</option>
                        <option value="mother">Mother</option>
                        <option value="guardian" selected>Guardian</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/students" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Save Student
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

const studentForm = document.getElementById('studentForm');

// Toggle parent required fields based on existing parent selection
const parentSelect = document.getElementById('parentSelect');
if (parentSelect) {
    const parentFirst = studentForm.querySelector('input[name=\"parent_first_name\"]');
    const parentLast = studentForm.querySelector('input[name=\"parent_last_name\"]');
    const parentPhone = studentForm.querySelector('input[name=\"parent_phone\"]');

    parentSelect.addEventListener('change', function () {
        const hasExistingParent = this.value !== '';
        [parentFirst, parentLast, parentPhone].forEach(input => {
            if (!input) return;
            if (hasExistingParent) {
                input.removeAttribute('required');
            } else {
                input.setAttribute('required', 'required');
            }
        });
    });
}

studentForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const errorDiv = document.getElementById('errorMessage');
    const submitBtn = this.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    errorDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/students/store', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = data.redirect || '<?php echo BASE_URL; ?>/students';
        } else {
            let errorMsg = data.message || 'Failed to save student';
            if (data.errors) {
                errorMsg += ': ' + Object.values(data.errors).join(', ');
            }
            errorDiv.textContent = errorMsg;
            errorDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Student';
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Student';
    }
});
</script>

