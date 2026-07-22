<?php 
// Use roleData instead of role to avoid conflicts with Auth::getUserRole() or similar
$role = $roleData ?? null;

// Ensure role is an array
if (!isset($role) || !is_array($role)) {
    echo '<div class="container mx-auto px-4 py-8"><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Role not found</div></div>';
    return;
}
?>
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold capitalize"><?php echo isset($role['name']) ? str_replace('_', ' ', $role['name']) : 'Role'; ?> Permissions</h1>
            <p class="text-gray-600 mt-1">Manage and view all available permissions for this role</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/roles" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-2"></i>Back to Roles
        </a>
    </div>
    
    <?php 
    // Get current permissions
    $currentPermissions = [];
    if (isset($role['permissions'])) {
        if (is_array($role['permissions'])) {
            $currentPermissions = $role['permissions'];
        } elseif (is_string($role['permissions'])) {
            $decoded = json_decode($role['permissions'], true);
            $currentPermissions = $decoded !== null ? $decoded : [];
        }
    }
    $hasAllPermissions = is_array($currentPermissions) && in_array('*', $currentPermissions);
    ?>
    
    <!-- Permissions Summary -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">
                    <i class="fas fa-shield-alt mr-2 text-blue-600"></i>Current Permissions Status
                </h3>
                <div class="flex items-center space-x-4">
                    <?php if ($hasAllPermissions): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-2"></i>All Permissions Granted
                    </span>
                    <?php else: ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-list-check mr-2"></i><?php echo count($currentPermissions); ?> Permissions Assigned
                    </span>
                    <span class="text-sm text-gray-600">
                        Out of <?php echo count($availablePermissions); ?> available permissions
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!$hasAllPermissions && count($currentPermissions) > 0): ?>
            <div class="text-right">
                <div class="text-2xl font-bold text-blue-600">
                    <?php echo round((count($currentPermissions) / count($availablePermissions)) * 100); ?>%
                </div>
                <div class="text-xs text-gray-600">Coverage</div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (isset($role['name']) && $role['name'] === 'super_admin'): ?>
    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded mb-6">
        <p class="text-sm text-yellow-800">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Super Admin has all permissions and cannot be modified.
        </p>
    </div>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Permissions Management -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold">Available Permissions</h2>
                    <div class="text-sm text-gray-600">
                        <span class="font-semibold">
                            <?php 
                            $currentPermissions = [];
                            if (isset($role['permissions'])) {
                                if (is_array($role['permissions'])) {
                                    $currentPermissions = $role['permissions'];
                                } elseif (is_string($role['permissions'])) {
                                    $decoded = json_decode($role['permissions'], true);
                                    $currentPermissions = $decoded !== null ? $decoded : [];
                                }
                            }
                            $hasAllPermissions = is_array($currentPermissions) && in_array('*', $currentPermissions);
                            if ($hasAllPermissions) {
                                echo 'All Permissions';
                            } else {
                                echo count($currentPermissions) . ' of ' . count($availablePermissions) . ' permissions';
                            }
                            ?>
                        </span>
                    </div>
                </div>
                
                <form id="permissionsForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="selectAll" class="mr-2">
                            <span class="font-medium">Select All Permissions</span>
                        </label>
                    </div>
                    
                    <?php 
                    // Group permissions by category
                    $permissionGroups = [
                        'Students' => ['students.view', 'students.create', 'students.edit', 'students.delete'],
                        'Teachers' => ['teachers.view', 'teachers.create', 'teachers.edit', 'teachers.delete'],
                        'Parents' => ['parents.view', 'parents.create', 'parents.edit', 'parents.delete'],
                        'Classes & Grades' => ['classes.view', 'classes.create', 'classes.edit', 'classes.delete', 'grades.view', 'grades.create', 'grades.edit', 'grades.delete'],
                        'Attendance' => ['attendance.view', 'attendance.create', 'attendance.edit', 'attendance.delete'],
                        'Assessments' => ['assessments.view', 'assessments.create', 'assessments.edit', 'assessments.delete'],
                        'Fees' => ['fees.view', 'fees.create', 'fees.edit', 'fees.delete', 'feeheads.view', 'feeheads.create', 'feeheads.edit', 'feeheads.delete'],
                        'Payments' => ['payments.view', 'payments.create', 'payments.edit', 'payments.delete', 'payments.reconcile'],
                        'Reports' => ['reports.view', 'reports.financial', 'reports.academic', 'reports.attendance'],
                        'Communication' => ['communication.view', 'communication.send', 'communication.settings'],
                        'Settings' => ['settings.view', 'settings.edit'],
                        'Users & Roles' => ['users.view', 'users.create', 'users.edit', 'users.delete', 'roles.view', 'roles.edit'],
                        'Announcements' => ['announcements.view', 'announcements.create', 'announcements.edit', 'announcements.delete'],
                    ];
                    ?>
                    
                    <?php foreach ($permissionGroups as $groupName => $groupPermissions): ?>
                    <?php 
                    // Count how many permissions in this group are checked
                    $groupChecked = 0;
                    $groupTotal = 0;
                    foreach ($groupPermissions as $perm) {
                        if (isset($availablePermissions[$perm])) {
                            $groupTotal++;
                            if ($hasAllPermissions || in_array($perm, $currentPermissions)) {
                                $groupChecked++;
                            }
                        }
                    }
                    ?>
                    <div class="mb-6 border-b pb-4 last:border-b-0">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-folder-open mr-2 text-blue-500"></i><?php echo $groupName; ?>
                            </h3>
                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                <?php echo $groupChecked; ?>/<?php echo $groupTotal; ?> selected
                            </span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <?php foreach ($groupPermissions as $perm): ?>
                            <?php if (isset($availablePermissions[$perm])): ?>
                            <?php $isChecked = $hasAllPermissions || in_array($perm, $currentPermissions); ?>
                            <label class="flex items-center p-3 hover:bg-blue-50 rounded cursor-pointer border <?php echo $isChecked ? 'border-blue-300 bg-blue-50' : 'border-gray-200'; ?> transition-colors">
                                <input type="checkbox" 
                                       name="permissions[]" 
                                       value="<?php echo htmlspecialchars($perm); ?>"
                                       class="mr-3 permission-checkbox w-4 h-4 text-blue-600"
                                       <?php echo $isChecked ? 'checked' : ''; ?>
                                       <?php echo $role['name'] === 'super_admin' ? 'disabled' : ''; ?>>
                                <span class="text-sm <?php echo $isChecked ? 'font-medium text-gray-900' : 'text-gray-700'; ?>">
                                    <?php echo htmlspecialchars($availablePermissions[$perm]); ?>
                                </span>
                                <?php if ($isChecked): ?>
                                <i class="fas fa-check-circle ml-auto text-blue-500"></i>
                                <?php endif; ?>
                            </label>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if ($role['name'] !== 'super_admin'): ?>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                            <i class="fas fa-save mr-2"></i>Save Permissions
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <div id="resultMessage" class="hidden mt-4"></div>
                </form>
            </div>
        </div>
        
        <!-- Role Info & Users -->
        <div class="space-y-6">
            <!-- Role Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">Role Information</h2>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Role Name</label>
                        <p class="text-lg font-semibold capitalize"><?php echo str_replace('_', ' ', $role['name']); ?></p>
                    </div>
                    <?php if ($role['description']): ?>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Description</label>
                        <p class="text-lg"><?php echo htmlspecialchars($role['description']); ?></p>
                    </div>
                    <?php endif; ?>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Total Permissions</label>
                        <p class="text-lg font-semibold">
                            <?php 
                            if ($hasAllPermissions) {
                                echo 'All Permissions';
                            } else {
                                echo count($currentPermissions);
                            }
                            ?>
                        </p>
                    </div>
                    <?php if ($role['name'] !== 'super_admin'): ?>
                    <a href="<?php echo BASE_URL; ?>/roles/edit/<?php echo $role['id']; ?>" 
                       class="block w-full bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-center mt-4">
                        <i class="fas fa-edit mr-2"></i>Edit Role Details
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Users with this Role -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">Users with this Role</h2>
                <?php if (empty($users)): ?>
                <p class="text-gray-500 text-sm">No users assigned to this role</p>
                <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($users as $user): ?>
                    <a href="<?php echo BASE_URL; ?>/users/show/<?php echo $user['id']; ?>" 
                       class="block p-2 hover:bg-gray-50 rounded">
                        <p class="font-medium text-sm"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($user['email']); ?></p>
                    </a>
                    <?php endforeach; ?>
                </div>
                <a href="<?php echo BASE_URL; ?>/users?role=<?php echo urlencode($role['name']); ?>" 
                   class="block text-center text-blue-600 hover:text-blue-800 text-sm mt-4">
                    View All <i class="fas fa-arrow-right ml-1"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.permission-checkbox:not([disabled])');
    checkboxes.forEach(cb => cb.checked = this.checked);
});

// Update select all when individual checkboxes change
document.querySelectorAll('.permission-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
        const allCheckboxes = document.querySelectorAll('.permission-checkbox:not([disabled])');
        const checkedCheckboxes = document.querySelectorAll('.permission-checkbox:not([disabled]):checked');
        document.getElementById('selectAll').checked = allCheckboxes.length === checkedCheckboxes.length;
    });
});

// Form submission
document.getElementById('permissionsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const resultDiv = document.getElementById('resultMessage');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    resultDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/roles/updatePermissions/<?php echo $role['id']; ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultDiv.className = 'mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded';
            resultDiv.innerHTML = '<strong>Success!</strong> Permissions updated successfully.';
            resultDiv.classList.remove('hidden');
            
            setTimeout(() => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            }, 1500);
        } else {
            resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            resultDiv.innerHTML = '<strong>Error:</strong> ' + (data.message || 'Failed to update permissions');
            resultDiv.classList.remove('hidden');
        }
    } catch (error) {
        resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
        resultDiv.innerHTML = '<strong>Error:</strong> An error occurred. Please try again.';
        resultDiv.classList.remove('hidden');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Permissions';
    }
});
</script>

