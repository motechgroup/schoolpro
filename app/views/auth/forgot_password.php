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
                    <i class="fas fa-key text-white text-2xl"></i>
                </div>
                <h2 class="text-4xl font-bold text-white mb-2">Forgot Password?</h2>
                <p class="text-white/80 text-sm">Enter your email or phone number to receive a password reset link or code</p>
            </div>
            
            <form id="forgotPasswordForm" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-white/90 mb-3">
                        <i class="fas fa-paper-plane mr-2"></i>Reset Method
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="flex items-center justify-center p-3 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl cursor-pointer transition-all duration-200 hover:bg-white/20 hover:border-white/40">
                            <input type="radio" name="method" value="email" checked class="mr-2 accent-white">
                            <span class="text-sm text-white font-medium">Email</span>
                        </label>
                        <label class="flex items-center justify-center p-3 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl cursor-pointer transition-all duration-200 hover:bg-white/20 hover:border-white/40">
                            <input type="radio" name="method" value="sms" class="mr-2 accent-white">
                            <span class="text-sm text-white font-medium">SMS</span>
                        </label>
                        <label class="flex items-center justify-center p-3 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl cursor-pointer transition-all duration-200 hover:bg-white/20 hover:border-white/40">
                            <input type="radio" name="method" value="both" class="mr-2 accent-white">
                            <span class="text-sm text-white font-medium">Both</span>
                        </label>
                    </div>
                </div>
                
                <div id="emailField">
                    <label for="email" class="block text-sm font-medium text-white/90 mb-2">
                        <i class="fas fa-envelope mr-2"></i>Email Address
                    </label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="w-full px-4 py-3 bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-white/50 transition-all duration-200"
                           placeholder="Enter your email address">
                </div>
                
                <div id="phoneField" style="display: none;">
                    <label for="phone" class="block text-sm font-medium text-white/90 mb-2">
                        <i class="fas fa-phone mr-2"></i>Phone Number
                    </label>
                    <input id="phone" name="phone" type="tel" autocomplete="tel"
                           class="w-full px-4 py-3 bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-white/50 transition-all duration-200"
                           placeholder="Enter your phone number (e.g., 254712345678)">
                </div>
            </div>
            
            <div id="result" class="hidden"></div>
            
            <div>
                <button type="submit" id="submitBtn"
                        class="group relative w-full flex justify-center items-center py-3 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg transform transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-transparent">
                    <i class="fas fa-key mr-2"></i>
                    <span>Send Reset Link/Code</span>
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
document.querySelectorAll('input[name="method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const emailField = document.getElementById('emailField');
        const phoneField = document.getElementById('phoneField');
        const emailInput = document.getElementById('email');
        const phoneInput = document.getElementById('phone');
        
        if (this.value === 'email') {
            emailField.style.display = 'block';
            phoneField.style.display = 'none';
            emailInput.required = true;
            phoneInput.required = false;
        } else if (this.value === 'sms') {
            emailField.style.display = 'none';
            phoneField.style.display = 'block';
            emailInput.required = false;
            phoneInput.required = true;
        } else {
            emailField.style.display = 'block';
            phoneField.style.display = 'block';
            emailInput.required = true;
            phoneInput.required = true;
        }
    });
});

document.getElementById('forgotPasswordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = document.getElementById('submitBtn');
    const resultDiv = document.getElementById('result');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
    resultDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/auth/requestPasswordReset', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        resultDiv.classList.remove('hidden');
        
        if (data.success) {
            resultDiv.className = 'p-4 bg-green-500/20 backdrop-blur-sm border border-green-400/50 text-green-100 rounded-xl';
            resultDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i><strong>Success!</strong> ' + data.message;
            this.reset();
        } else {
            resultDiv.className = 'p-4 bg-red-500/20 backdrop-blur-sm border border-red-400/50 text-red-100 rounded-xl';
            resultDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i><strong>Error:</strong> ' + (data.message || 'Failed to send reset link/code');
        }
    } catch (error) {
        resultDiv.classList.remove('hidden');
        resultDiv.className = 'p-4 bg-red-500/20 backdrop-blur-sm border border-red-400/50 text-red-100 rounded-xl';
        resultDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i><strong>Error:</strong> ' + error.message;
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>

