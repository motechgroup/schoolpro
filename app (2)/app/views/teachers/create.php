<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Add New Teacher</h1>
    
    <div class="bg-white rounded-lg shadow p-6">
        <form id="teacherForm" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="space-y-6">
                <!-- User Selection -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded mb-4">
                    <h2 class="text-lg font-bold mb-3">Add from Existing User</h2>
                    <p class="text-sm text-gray-700 mb-3">
                        Select an existing user (e.g., school manager) to add as a teacher. This allows users to have multiple roles.
                    </p>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2 cursor-pointer">
                            <input type="radio" name="user_selection" value="existing" id="useExistingUser" class="mr-2">
                            Select Existing User
                        </label>
                        <div id="existingUserContainer" style="display: none;">
                            <?php if (!empty($existingUsers)): ?>
                            <select name="user_id" id="existingUserSelect" class="w-full border rounded px-3 py-2 mt-2">
                                <option value="">Select a user...</option>
                                <?php foreach ($existingUsers as $user): ?>
                                <option value="<?php echo $user['id']; ?>" 
                                        data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                        data-first-name="<?php echo htmlspecialchars($user['first_name']); ?>"
                                        data-last-name="<?php echo htmlspecialchars($user['last_name']); ?>"
                                        data-phone="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                        data-role="<?php echo htmlspecialchars($user['role_name']); ?>">
                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email'] . ') - ' . ucfirst($user['role_name'])); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php else: ?>
                            <div class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded text-sm text-yellow-800">
                                <i class="fas fa-info-circle mr-2"></i>
                                No existing users available. All active users already have teacher profiles, or there are no active users in the system.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2 cursor-pointer">
                            <input type="radio" name="user_selection" value="new" id="createNewUser" checked class="mr-2">
                            Create New User
                        </label>
                    </div>
                </div>
                
                <!-- Photo Upload (always visible) -->
                <div>
                    <h2 class="text-xl font-bold mb-4">Teacher Photo</h2>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload Photo</label>
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/gif" class="w-full border rounded px-3 py-2">
                        <p class="text-xs text-gray-500 mt-1">Accepted formats: JPEG, PNG, GIF (Max 5MB). Recommended size: 400x400px</p>
                        <div id="photoPreview" class="mt-2 hidden">
                            <img id="previewImage" src="" alt="Preview" class="w-32 h-32 object-cover rounded border">
                        </div>
                    </div>
                </div>
                
                <div id="newUserFields">
                <div>
                    <h2 class="text-xl font-bold mb-4">Personal Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                            <input type="text" name="first_name" required 
                                   class="w-full border rounded px-3 py-2">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                            <input type="text" name="last_name" required 
                                   class="w-full border rounded px-3 py-2">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input type="email" name="email" required 
                                   class="w-full border rounded px-3 py-2">
                            <p class="text-xs text-gray-500 mt-1">Used for login</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="tel" name="phone" 
                                   placeholder="+254700000000"
                                   class="w-full border rounded px-3 py-2">
                        </div>
                    </div>
                </div>
                
                <div>
                    <h2 class="text-xl font-bold mb-4">Account Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                            <input type="password" name="password" required 
                                   minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                                   class="w-full border rounded px-3 py-2">
                            <p class="text-xs text-gray-500 mt-1">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</p>
                        </div>
                    </div>
                </div>
                </div>
                
                <!-- Professional Information (always visible) -->
                <div>
                    <h2 class="text-xl font-bold mb-4">Professional Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">TSC Number</label>
                            <input type="text" name="tsc_number" 
                                   placeholder="Optional"
                                   class="w-full border rounded px-3 py-2">
                            <p class="text-xs text-gray-500 mt-1">Teachers Service Commission number</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Employment Date</label>
                            <input type="date" name="employment_date" 
                                   value="<?php echo date('Y-m-d'); ?>"
                                   class="w-full border rounded px-3 py-2">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Qualification</label>
                            <input type="text" name="qualification" 
                                   placeholder="e.g., Bachelor of Education"
                                   class="w-full border rounded px-3 py-2">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Specialization</label>
                            <input type="text" name="specialization" 
                                   placeholder="e.g., Mathematics, English"
                                   class="w-full border rounded px-3 py-2">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/teachers" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Create Teacher
                </button>
            </div>
            
            <div id="errorMessage" class="hidden mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
        </form>
    </div>
</div>

<script>
// Handle user selection toggle
const useExistingUserRadio = document.getElementById('useExistingUser');
const createNewUserRadio = document.getElementById('createNewUser');
const existingUserContainer = document.getElementById('existingUserContainer');
const existingUserSelect = document.getElementById('existingUserSelect');
const newUserFields = document.getElementById('newUserFields');

if (useExistingUserRadio && createNewUserRadio) {
    useExistingUserRadio.addEventListener('change', function() {
        if (this.checked) {
            // Show existing user container
            if (existingUserContainer) {
                existingUserContainer.style.display = 'block';
            }
            if (existingUserSelect) {
                existingUserSelect.disabled = false;
                existingUserSelect.required = true;
            }
            // Hide new user fields
            if (newUserFields) {
                newUserFields.style.display = 'none';
                // Make new user fields not required
                const requiredFields = newUserFields.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    field.removeAttribute('required');
                    field.disabled = true;
                });
            }
        }
    });
    
    createNewUserRadio.addEventListener('change', function() {
        if (this.checked) {
            // Hide existing user container
            if (existingUserContainer) {
                existingUserContainer.style.display = 'none';
            }
            if (existingUserSelect) {
                existingUserSelect.disabled = true;
                existingUserSelect.required = false;
                existingUserSelect.value = '';
            }
            // Show new user fields
            if (newUserFields) {
                newUserFields.style.display = 'block';
                // Make new user fields required again
                const requiredFields = newUserFields.querySelectorAll('input[type="text"], input[type="email"], input[type="password"]');
                requiredFields.forEach(field => {
                    if (field.name === 'first_name' || field.name === 'last_name' || field.name === 'email' || field.name === 'password') {
                        field.setAttribute('required', 'required');
                    }
                    field.disabled = false;
                });
            }
            // Remove selected user info if it exists
            const infoDiv = document.getElementById('selectedUserInfo');
            if (infoDiv) {
                infoDiv.remove();
            }
        }
    });
    
    // Auto-fill fields when user is selected
    if (existingUserSelect) {
        existingUserSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const firstName = selectedOption.getAttribute('data-first-name');
                const lastName = selectedOption.getAttribute('data-last-name');
                const email = selectedOption.getAttribute('data-email');
                const phone = selectedOption.getAttribute('data-phone');
                const role = selectedOption.getAttribute('data-role');
                
                // Show info about selected user
                let infoDiv = document.getElementById('selectedUserInfo');
                if (!infoDiv) {
                    infoDiv = document.createElement('div');
                    infoDiv.id = 'selectedUserInfo';
                    infoDiv.className = 'mt-2 p-3 bg-green-50 border border-green-200 rounded text-sm';
                    this.parentNode.appendChild(infoDiv);
                }
                infoDiv.innerHTML = `
                    <strong>Selected User:</strong> ${firstName} ${lastName}<br>
                    <strong>Email:</strong> ${email}<br>
                    <strong>Current Role:</strong> ${role}<br>
                    <strong>Phone:</strong> ${phone || 'Not set'}<br>
                    <span class="text-blue-600 text-xs mt-1 block">This user will be able to access both ${role} and teacher features.</span>
                `;
            } else {
                const infoDiv = document.getElementById('selectedUserInfo');
                if (infoDiv) {
                    infoDiv.remove();
                }
            }
        });
    }
}

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
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';
    errorDiv.classList.add('hidden');
    
    try {
        // Add user_id if using existing user
        if (useExistingUserRadio && useExistingUserRadio.checked && existingUserSelect && existingUserSelect.value) {
            formData.append('user_id', existingUserSelect.value);
        }
        
        const response = await fetch('<?php echo BASE_URL; ?>/teachers/store', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = data.redirect || '<?php echo BASE_URL; ?>/teachers';
        } else {
            let errorMsg = data.message || 'Failed to create teacher';
            if (data.errors) {
                errorMsg += ': ' + Object.values(data.errors).join(', ');
            }
            errorDiv.textContent = errorMsg;
            errorDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Create Teacher';
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Create Teacher';
    }
});
</script>

