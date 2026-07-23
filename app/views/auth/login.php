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
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/20 p-8 transform transition-all duration-300 hover:scale-105">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full shadow-lg mb-4 transform transition-transform duration-300 hover:scale-110">
                    <img src="<?php echo getSystemLogo(); ?>" alt="Logo" class="h-16 w-16 rounded-full object-contain">
                </div>
                <h2 class="text-4xl font-bold text-white mb-2">Welcome Back</h2>
                <p class="text-white/80 text-sm">Sign in to Masomo School Management System</p>
            </div>
            
            <form id="loginForm" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <!-- Role Selection -->
                <div class="space-y-1">
                    <label for="role" class="block text-sm font-medium text-white/90 mb-2">
                        <i class="fas fa-user-tag mr-2"></i>Select Your Role
                    </label>
                    <div class="relative">
                        <select id="role" name="role" required 
                                class="w-full px-4 py-3 pl-12 bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-white/50 transition-all duration-200 appearance-none cursor-pointer">
                            <option value="" class="text-gray-900">Select Role</option>
                            <option value="super_admin" class="text-gray-900">Super Admin</option>
                            <option value="school_manager" class="text-gray-900">School Manager (Admin)</option>
                            <option value="teacher" class="text-gray-900">Teacher</option>
                            <option value="accountant" class="text-gray-900">Accountant</option>
                            <option value="receptionist" class="text-gray-900">Receptionist</option>
                            <option value="head_teacher" class="text-gray-900">Head Teacher</option>
                            <option value="bursar" class="text-gray-900">Bursar</option>
                            <option value="librarian" class="text-gray-900">Librarian</option>
                            <option value="parent" class="text-gray-900">Parent</option>
                        </select>
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-user-shield text-white/70"></i>
                        </div>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-white/70"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Email Input -->
                <div class="space-y-1">
                    <label for="email" class="block text-sm font-medium text-white/90 mb-2">
                        <i class="fas fa-envelope mr-2"></i>Email Address
                    </label>
                    <div class="relative">
                        <input id="email" name="email" type="email" required 
                               class="w-full px-4 py-3 pl-12 bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-white/50 transition-all duration-200" 
                               placeholder="Enter your email">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-white/70"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Password Input -->
                <div class="space-y-1">
                    <label for="password" class="block text-sm font-medium text-white/90 mb-2">
                        <i class="fas fa-lock mr-2"></i>Password
                    </label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required 
                               class="w-full px-4 py-3 pl-12 pr-12 bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-white/50 transition-all duration-200" 
                               placeholder="Enter your password">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-white/70"></i>
                        </div>
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-white/70 hover:text-white transition-colors">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer group">
                        <input id="remember-me" name="remember-me" type="checkbox" 
                               class="w-4 h-4 text-indigo-600 bg-white/20 border-white/30 rounded focus:ring-2 focus:ring-white/50 cursor-pointer">
                        <span class="ml-2 text-sm text-white/90 group-hover:text-white transition-colors">Remember me</span>
                    </label>
                    <a href="<?php echo BASE_URL; ?>/auth/forgot-password" class="text-sm text-white/90 hover:text-white transition-colors font-medium">
                        Forgot password?
                    </a>
                </div>
                
                <!-- Submit Button -->
                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center items-center py-3.5 px-4 border border-transparent text-base font-semibold rounded-xl text-indigo-600 bg-white hover:bg-white/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white/50 shadow-lg transform transition-all duration-200 hover:scale-105 hover:shadow-xl">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-4">
                            <i class="fas fa-sign-in-alt text-indigo-600 group-hover:translate-x-1 transition-transform"></i>
                        </span>
                        <span id="submitText">Sign In</span>
                        <span id="submitSpinner" class="hidden ml-2">
                            <i class="fas fa-spinner fa-spin"></i>
                        </span>
                    </button>
                </div>
                
                <!-- Error Message -->
                <div id="errorMessage" class="hidden bg-red-500/20 backdrop-blur-sm border border-red-400/50 text-red-100 px-4 py-3 rounded-xl text-sm"></div>
            </form>
        </div>
        
        <!-- Footer -->
        <div class="text-center">
            <p class="text-white/60 text-sm">
                &copy; 2025 Masomo School Management System. All rights reserved.
            </p>
        </div>
    </div>
</div>

<style>
@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    100% {
        transform: translate(0px, 0px) scale(1);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

/* Custom select arrow */
select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}
</style>

<script>
// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
});

// Form submission
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const errorDiv = document.getElementById('errorMessage');
    const submitBtn = this.querySelector('button[type="submit"]');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');
    
    // Disable submit button and show loading state
    submitBtn.disabled = true;
    submitText.textContent = 'Signing in...';
    submitSpinner.classList.remove('hidden');
    errorDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/auth/processLogin', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Success animation
            submitBtn.classList.add('bg-green-500');
            submitText.textContent = 'Success! Redirecting...';
            
            setTimeout(() => {
                window.location.href = data.redirect || '<?php echo BASE_URL; ?>/dashboard';
            }, 500);
        } else {
            errorDiv.textContent = data.message || 'Login failed. Please check your credentials and try again.';
            errorDiv.classList.remove('hidden');
            
            // Shake animation for error
            errorDiv.classList.add('animate-pulse');
            setTimeout(() => {
                errorDiv.classList.remove('animate-pulse');
            }, 1000);
            
            // Reset button
            submitBtn.disabled = false;
            submitText.textContent = 'Sign In';
            submitSpinner.classList.add('hidden');
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please check your connection and try again.';
        errorDiv.classList.remove('hidden');
        
        // Reset button
        submitBtn.disabled = false;
        submitText.textContent = 'Sign In';
        submitSpinner.classList.add('hidden');
    }
});

// Add focus effects to inputs
document.querySelectorAll('input, select').forEach(input => {
    input.addEventListener('focus', function() {
        this.parentElement.classList.add('ring-2', 'ring-white/50');
    });
    
    input.addEventListener('blur', function() {
        this.parentElement.classList.remove('ring-2', 'ring-white/50');
    });
});
</script>
