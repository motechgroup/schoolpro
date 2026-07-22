<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Parents Management</h1>
        <div class="space-x-2">
            <a href="<?php echo BASE_URL; ?>/parents/create" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-plus mr-2"></i>Add Parent
            </a>
            <a href="<?php echo BASE_URL; ?>/communication" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-sms mr-2"></i>Send SMS
            </a>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>" 
                       placeholder="Name, phone, email..." 
                       class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border rounded px-3 py-2">
                    <option value="active" <?php echo ($filters['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($filters['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter</label>
                <select name="has_balance" class="w-full border rounded px-3 py-2">
                    <option value="">All Parents</option>
                    <option value="1" <?php echo ($filters['has_balance'] ?? '') == '1' ? 'selected' : ''; ?>>With Outstanding Balance</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
    
    <!-- Parents Table -->
    <?php if (empty($parents)): ?>
    <div class="bg-white rounded-lg shadow p-6 text-center">
        <p class="text-gray-600">No parents found matching your criteria.</p>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Children</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outstanding Balance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($parents as $parent): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name']); ?>
                        </div>
                        <?php if (!empty($parent['relationship'])): ?>
                        <div class="text-xs text-gray-500"><?php echo ucfirst($parent['relationship']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php echo htmlspecialchars($parent['phone']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php echo htmlspecialchars($parent['email'] ?? 'N/A'); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                            <?php echo $parent['children_count'] ?? 0; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php if (($parent['total_balance'] ?? 0) > 0): ?>
                        <span class="font-semibold text-red-600">
                            <?php echo formatCurrency($parent['total_balance']); ?>
                        </span>
                        <?php else: ?>
                        <span class="text-green-600">Paid</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded <?php 
                            echo $parent['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                        ?>">
                            <?php echo ucfirst($parent['status']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="<?php echo BASE_URL; ?>/parents/show/<?php echo $parent['id']; ?>" 
                           class="text-blue-600 hover:text-blue-900 mr-3" title="View Details">
                            <i class="fas fa-eye mr-1"></i>View
                        </a>
                        <a href="<?php echo BASE_URL; ?>/parents/edit/<?php echo $parent['id']; ?>" 
                           class="text-yellow-600 hover:text-yellow-900 mr-3" title="Edit Parent">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                        <button onclick="deleteParent(<?php echo $parent['id']; ?>, '<?php echo htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name']); ?>')" 
                                class="text-red-600 hover:text-red-900 mr-3" title="Delete Parent">
                            <i class="fas fa-trash mr-1"></i>Delete
                        </button>
                        <a href="<?php echo BASE_URL; ?>/communication?parent_id=<?php echo $parent['id']; ?>" 
                           class="text-green-600 hover:text-green-900" title="Send SMS">
                            <i class="fas fa-sms mr-1"></i>SMS
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Confirm Delete</h3>
            <p class="text-sm text-gray-500 mb-4" id="deleteMessage"></p>
            <div class="bg-yellow-50 border border-yellow-200 rounded p-3 mb-4">
                <p class="text-xs text-yellow-800">
                    <strong>Note:</strong> You cannot delete a parent with active students. Please transfer or deactivate students first.
                </p>
            </div>
            <form id="deleteForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeDeleteModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        <i class="fas fa-trash mr-2"></i>Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentParentId = null;

function deleteParent(id, name) {
    currentParentId = id;
    document.getElementById('deleteMessage').textContent = `Are you sure you want to delete parent "${name}"? This action cannot be undone.`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    currentParentId = null;
}

document.getElementById('deleteForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (!currentParentId) return;
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/parents/delete/' + currentParentId, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Parent deleted successfully');
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.reload();
            }
        } else {
            alert('Error: ' + (data.message || 'Failed to delete parent'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-trash mr-2"></i>Delete';
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-trash mr-2"></i>Delete';
    }
});
</script>

