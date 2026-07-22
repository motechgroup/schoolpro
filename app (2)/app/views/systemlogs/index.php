<?php
$title = 'System Logs';
require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">
            <i class="fas fa-list-alt mr-2"></i>System Logs
        </h1>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <input type="text" name="action" placeholder="Action filter..." 
                   value="<?php echo htmlspecialchars($filters['action'] ?? ''); ?>"
                   class="border rounded px-3 py-2">
            
            <select name="module" class="border rounded px-3 py-2">
                <option value="">All Modules</option>
                <?php foreach ($modules as $module): ?>
                <option value="<?php echo htmlspecialchars($module); ?>" 
                        <?php echo ($filters['module'] ?? '') === $module ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($module); ?>
                </option>
                <?php endforeach; ?>
            </select>
            
            <select name="status" class="border rounded px-3 py-2">
                <option value="">All Statuses</option>
                <option value="success" <?php echo ($filters['status'] ?? '') === 'success' ? 'selected' : ''; ?>>Success</option>
                <option value="error" <?php echo ($filters['status'] ?? '') === 'error' ? 'selected' : ''; ?>>Error</option>
                <option value="warning" <?php echo ($filters['status'] ?? '') === 'warning' ? 'selected' : ''; ?>>Warning</option>
            </select>
            
            <input type="date" name="start_date" 
                   value="<?php echo htmlspecialchars($filters['start_date'] ?? ''); ?>"
                   class="border rounded px-3 py-2" placeholder="Start Date">
            
            <input type="date" name="end_date" 
                   value="<?php echo htmlspecialchars($filters['end_date'] ?? ''); ?>"
                   class="border rounded px-3 py-2" placeholder="End Date">
            
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 col-span-full md:col-span-1">
                <i class="fas fa-filter mr-2"></i>Filter
            </button>
        </form>
    </div>
    
    <!-- Logs Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date/Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Module</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No logs found</td>
                </tr>
                <?php else: ?>
                <?php foreach ($logs as $log): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php if ($log['module']): ?>
                            <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800">
                                <?php echo htmlspecialchars($log['module']); ?>
                            </span>
                        <?php else: ?>
                            <span class="text-gray-400">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">
                        <?php echo htmlspecialchars($log['action']); ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                        <?php echo htmlspecialchars($log['description'] ?? 'N/A'); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php if ($log['first_name'] || $log['last_name']): ?>
                            <?php echo htmlspecialchars(trim(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? ''))); ?>
                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($log['email'] ?? ''); ?></div>
                        <?php else: ?>
                            <span class="text-gray-400">System</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                        <?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded <?php 
                            echo $log['status'] === 'success' ? 'bg-green-100 text-green-800' : 
                                ($log['status'] === 'error' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); 
                        ?>">
                            <?php echo ucfirst($log['status']); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($page > 1 || !empty($logs)): ?>
    <div class="bg-white rounded-lg shadow p-4 flex justify-between items-center">
        <div class="text-sm text-gray-600">
            Page <?php echo $page; ?>
        </div>
        <div class="flex space-x-2">
            <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query($filters); ?>" 
               class="px-3 py-1 border rounded hover:bg-gray-100">
                Previous
            </a>
            <?php endif; ?>
            <a href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query($filters); ?>" 
               class="px-3 py-1 border rounded hover:bg-gray-100">
                Next
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>

