<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">My Profile</h1>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Profile Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">Profile Information</h2>
            <form id="profileForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                               disabled class="w-full border rounded px-3 py-2 bg-gray-100">
                        <p class="text-xs text-gray-500 mt-1">Email cannot be changed</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                        <input type="text" name="first_name" required 
                               value="<?php echo htmlspecialchars($user['first_name']); ?>"
                               class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                        <input type="text" name="last_name" required 
                               value="<?php echo htmlspecialchars($user['last_name']); ?>"
                               class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="tel" name="phone" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                               class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <input type="text" value="<?php echo ucwords(str_replace('_', ' ', $user['role_name'])); ?>" 
                               disabled class="w-full border rounded px-3 py-2 bg-gray-100">
                    </div>
                </div>
                
                <div class="mt-6">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Update Profile
                    </button>
                </div>
                
                <div id="profileError" class="hidden mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
                <div id="profileSuccess" class="hidden mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"></div>
            </form>
        </div>
        
        <!-- Change Password -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">Change Password</h2>
            <form id="passwordForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Password *</label>
                        <input type="password" name="current_password" required 
                               class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password *</label>
                        <input type="password" name="new_password" required 
                               minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                               class="w-full border rounded px-3 py-2">
                        <p class="text-xs text-gray-500 mt-1">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password *</label>
                        <input type="password" name="confirm_password" required 
                               minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                               class="w-full border rounded px-3 py-2">
                    </div>
                </div>
                
                <div class="mt-6">
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                        <i class="fas fa-key mr-2"></i>Change Password
                    </button>
                </div>
                
                <div id="passwordError" class="hidden mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
                <div id="passwordSuccess" class="hidden mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"></div>
            </form>
        </div>
    </div>
</div>

<script>
// Profile form
document.getElementById('profileForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const errorDiv = document.getElementById('profileError');
    const successDiv = document.getElementById('profileSuccess');
    
    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/profile/update', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            successDiv.textContent = data.message || 'Profile updated successfully';
            successDiv.classList.remove('hidden');
            setTimeout(() => location.reload(), 1500);
        } else {
            errorDiv.textContent = data.message || 'Failed to update profile';
            errorDiv.classList.remove('hidden');
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
    }
});

// Password form
document.getElementById('passwordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const errorDiv = document.getElementById('passwordError');
    const successDiv = document.getElementById('passwordSuccess');
    
    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/profile/changePassword', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            successDiv.textContent = data.message || 'Password changed successfully';
            successDiv.classList.remove('hidden');
            this.reset();
        } else {
            errorDiv.textContent = data.message || 'Failed to change password';
            errorDiv.classList.remove('hidden');
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
    }
});
</script>

