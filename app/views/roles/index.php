<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Role Management</h1>
    </div>
    
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded mb-6">
        <p class="text-sm text-blue-800">
            <i class="fas fa-info-circle mr-2"></i>
            Manage roles and their permissions. Click on a role to view and edit its permissions.
        </p>
    </div>
    
    <!-- Roles Grid -->
    <?php if (empty($roles)): ?>
    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
        <p class="text-sm text-yellow-800">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            No roles found. Please ensure the database has been initialized with default roles by running the SQL migrations.
        </p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($roles as $role): ?>
        <?php if (!isset($role['id']) || !isset($role['name'])) continue; ?>
        <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 capitalize">
                            <?php echo str_replace('_', ' ', $role['name']); ?>
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">ID: <?php echo $role['id']; ?></p>
                        <?php if (!empty($role['description'])): ?>
                        <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($role['description']); ?></p>
                        <?php endif; ?>
                    </div>
                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                        <?php echo number_format($role['user_count'] ?? 0); ?> users
                    </span>
                </div>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">Permissions:</p>
                    <?php 
                    $permissions = [];
                    if (isset($role['permissions'])) {
                        if (is_array($role['permissions'])) {
                            $permissions = $role['permissions'];
                        } elseif (is_string($role['permissions'])) {
                            $decoded = json_decode($role['permissions'], true);
                            $permissions = $decoded !== null ? $decoded : [];
                        }
                    }
                    if (is_array($permissions) && in_array('*', $permissions)): 
                    ?>
                    <span class="inline-block px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">
                        All Permissions
                    </span>
                    <?php else: ?>
                    <span class="inline-block px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800">
                        <?php echo is_array($permissions) ? count($permissions) : 0; ?> permission(s)
                    </span>
                    <?php endif; ?>
                </div>
                
                <div class="flex space-x-2">
                    <a href="<?php echo BASE_URL; ?>/roles/show/<?php echo $role['id']; ?>" 
                       class="flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-center text-sm">
                        <i class="fas fa-cog mr-2"></i>Manage Permissions
                    </a>
                    <?php if (isset($role['name']) && $role['name'] !== 'super_admin'): ?>
                    <a href="<?php echo BASE_URL; ?>/roles/edit/<?php echo $role['id']; ?>" 
                       class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">
                        <i class="fas fa-edit"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

