<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Fee Heads Management</h1>
        <a href="<?php echo BASE_URL; ?>/feeheads/create" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Create Fee Head
        </a>
    </div>
    
    <!-- Fee Heads Table -->
    <?php if (empty($feeHeads)): ?>
    <div class="bg-white rounded-lg shadow p-6 text-center">
        <p class="text-gray-600">No fee heads found.</p>
        <a href="<?php echo BASE_URL; ?>/feeheads/create" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Create First Fee Head
        </a>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Default Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mandatory</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($feeHeads as $feeHead): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                        <?php echo htmlspecialchars($feeHead['code']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <?php echo htmlspecialchars($feeHead['name']); ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <?php echo htmlspecialchars($feeHead['description'] ?? 'N/A'); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo formatCurrency($feeHead['default_amount']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php if ($feeHead['is_mandatory']): ?>
                            <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800">Yes</span>
                        <?php else: ?>
                            <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800">No</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded <?php 
                            echo $feeHead['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                        ?>">
                            <?php echo ucfirst($feeHead['status']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="<?php echo BASE_URL; ?>/feeheads/edit/<?php echo $feeHead['id']; ?>" 
                           class="text-green-600 hover:text-green-900 mr-3" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="#" onclick="deleteFeeHead(<?php echo $feeHead['id']; ?>)" 
                           class="text-red-600 hover:text-red-900" title="Delete">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
function deleteFeeHead(id) {
    if (!confirm('Are you sure you want to delete this fee head? This action cannot be undone if it is assigned to students.')) {
        return;
    }
    
    fetch('<?php echo BASE_URL; ?>/feeheads/delete/' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Fee head deleted successfully');
            location.reload();
        } else {
            alert('Failed to delete fee head: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
    });
}
</script>

