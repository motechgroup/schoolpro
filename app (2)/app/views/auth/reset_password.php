<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Background Image with Overlay -->
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" 
         style="background-image: url('<?php echo BASE_URL; ?>/public/uploads/schoolbg.jpg');">
        <!-- Dark overlay for better text readability -->
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/80 via-purple-900/75 to-pink-900/80 backdrop-blur-sm"></div>
        <!-- Additional overlay for opacity control -->
        <div class="absolute inset-0 bg-black/30"></div>
    </div>
    
    <!-- Subtle animated elements for depth -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-400/20 rounded-full mix-blend-overlay filter blur-3xl opacity-50 animate-blob"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-400/20 rounded-full mix-blend-overlay filter blur-3xl opacity-50 animate-blob animation-delay-2000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-indigo-400/20 rounded-full mix-blend-overlay filter blur-3xl opacity-50 animate-blob animation-delay-4000"></div>
    </div>
    
    <div class="max-w-md w-full space-y-8 relative z-10">
        <!-- Modern glassmorphism card -->
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/20 p-8 transform transition-all duration-300">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full shadow-lg mb-4 transform transition-transform duration-300">
                    <i class="fas fa-lock text-white text-2xl"></i>
                </div>
                <h2 class="text-4xl font-bold text-white mb-2">Reset Your Password</h2>
                <p class="text-white/80 text-sm">Enter your new password below</p>
            </div>
            
            <form id="resetPasswordForm" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="reset_id" value="<?php echo $reset_id; ?>">
            <?php if (!empty($token)): ?>
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <?php endif; ?>
            <?php if (!empty($code)): ?>
            <input type="hidden" name="code" value="<?php echo htmlspecialchars($code); ?>">
            <div class="mb-4 p-4 bg-blue-500/20 backdrop-blur-sm border border-blue-400/50 rounded-xl">
                <p class="text-sm text-blue-100">
                    <i class="fas fa-info-circle mr-2"></i>
                    You are resetting your password using SMS code: <strong><?php echo htmlspecialchars($code); ?></strong>
                </p>
            </div>
            <?php endif; ?>
            
            <div class="space-y-4">
                <div>
                    <label for="new_password" class="block text-sm font-medium text-white/90 mb-2">
                        <i class="fas fa-lock mr-2"></i>New Password
                    </label>
                    <input id="new_password" name="new_password" type="password" autocomplete="new-password" required
                           class="w-full px-4 py-3 bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-white/50 transition-all duration-200"
                           placeholder="Enter new password (min. 6 characters)">
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-white/90 mb-2">
                        <i class="fas fa-lock mr-2"></i>Confirm Password
                    </label>
                    <input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" required
                           class="w-full px-4 py-3 bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-white/50 transition-all duration-200"
                           placeholder="Confirm new password">
                </div>
            </div>
            
            <div id="result" class="hidden"></div>
            
            <div>
                <button type="submit" id="submitBtn"
                        class="group relative w-full flex justify-center items-center py-3 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg transform transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-transparent">
                    <i class="fas fa-lock mr-2"></i>
                    <span>Reset Password</span>
                </button>
            </div>
            
            <div class="text-center pt-4 border-t border-white/20">
                <a href="<?php echo BASE_URL; ?>/auth/login" class="inline-flex items-center text-sm font-medium text-white/90 hover:text-white transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span>Back to Login</span>
                </a>
            </div>
        </form>
        </div>
    </div>
</div>

<script>
document.getElementById('resetPasswordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = document.getElementById('submitBtn');
    const resultDiv = document.getElementById('result');
    const originalText = submitBtn.innerHTML;
    
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        resultDiv.classList.remove('hidden');
        resultDiv.className = 'p-4 bg-red-500/20 backdrop-blur-sm border border-red-400/50 text-red-100 rounded-xl';
        resultDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i><strong>Error:</strong> Passwords do not match';
        return;
    }
    
    if (newPassword.length < 6) {
        resultDiv.classList.remove('hidden');
        resultDiv.className = 'p-4 bg-red-500/20 backdrop-blur-sm border border-red-400/50 text-red-100 rounded-xl';
        resultDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i><strong>Error:</strong> Password must be at least 6 characters';
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Resetting...';
    resultDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/auth/processPasswordReset', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        resultDiv.classList.remove('hidden');
        
        if (data.success) {
            resultDiv.className = 'p-4 bg-green-500/20 backdrop-blur-sm border border-green-400/50 text-green-100 rounded-xl';
            resultDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i><strong>Success!</strong> ' + data.message;
            
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            }
        } else {
            resultDiv.className = 'p-4 bg-red-500/20 backdrop-blur-sm border border-red-400/50 text-red-100 rounded-xl';
            resultDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i><strong>Error:</strong> ' + (data.message || 'Failed to reset password');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        resultDiv.classList.remove('hidden');
        resultDiv.className = 'p-4 bg-red-500/20 backdrop-blur-sm border border-red-400/50 text-red-100 rounded-xl';
        resultDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i><strong>Error:</strong> ' + error.message;
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>

