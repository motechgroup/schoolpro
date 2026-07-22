<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
        <div class="flex space-x-2">
            <a href="<?php echo BASE_URL; ?>/users/edit/<?php echo $user['id']; ?>" 
               class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <a href="<?php echo BASE_URL; ?>/users" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- User Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">User Information</h2>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">Full Name</label>
                    <p class="text-lg font-semibold"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Email</label>
                    <p class="text-lg"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Phone</label>
                    <p class="text-lg"><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Role</label>
                    <div class="flex items-center space-x-2 mt-1">
                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800 capitalize">
                            <?php echo str_replace('_', ' ', $user['role_name'] ?? 'N/A'); ?>
                        </span>
                        <?php if (!empty($user['role_id_for_permissions'])): ?>
                        <a href="<?php echo BASE_URL; ?>/roles/show/<?php echo $user['role_id_for_permissions']; ?>" 
                           class="text-blue-600 hover:text-blue-800 text-sm">
                            <i class="fas fa-cog"></i> Manage Permissions
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($user['role_description'])): ?>
                    <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($user['role_description']); ?></p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Status</label>
                    <span class="px-2 py-1 text-xs rounded <?php 
                        echo $user['status'] == 'active' ? 'bg-green-100 text-green-800' : 
                            ($user['status'] == 'suspended' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800');
                    ?>">
                        <?php echo ucfirst($user['status']); ?>
                    </span>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Last Login</label>
                    <p class="text-lg"><?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Never'; ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Account Created</label>
                    <p class="text-lg"><?php echo formatDateTime($user['created_at']); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Related Information -->
        <?php if (!empty($relatedData)): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <?php if (isset($relatedData['teacher'])): ?>
            <h2 class="text-2xl font-bold mb-4">Teacher Profile</h2>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">TSC Number</label>
                    <p class="text-lg"><?php echo htmlspecialchars($relatedData['teacher']['tsc_number'] ?? 'Not provided'); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Qualification</label>
                    <p class="text-lg"><?php echo htmlspecialchars($relatedData['teacher']['qualification'] ?? 'Not provided'); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Specialization</label>
                    <p class="text-lg"><?php echo htmlspecialchars($relatedData['teacher']['specialization'] ?? 'Not provided'); ?></p>
                </div>
                
                <?php if (!empty($relatedData['assignedClasses'])): ?>
                <div>
                    <label class="text-sm font-medium text-gray-500 mb-2 block">Assigned Classes</label>
                    <div class="space-y-2">
                        <?php foreach ($relatedData['assignedClasses'] as $class): ?>
                        <div class="bg-gray-50 p-2 rounded">
                            <p class="font-medium"><?php echo htmlspecialchars($class['grade_display_name'] . ' - ' . $class['name']); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <a href="<?php echo BASE_URL; ?>/teachers/show/<?php echo $relatedData['teacher']['id']; ?>" 
                   class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mt-4">
                    View Teacher Profile
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

