<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Grade Management</h1>
        <a href="<?php echo BASE_URL; ?>/grades/create" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Create New Grade
        </a>
    </div>
    
    <!-- Grades Table -->
    <?php if (empty($grades)): ?>
    <div class="bg-white rounded-lg shadow p-6 text-center">
        <p class="text-gray-600">No grades found.</p>
        <a href="<?php echo BASE_URL; ?>/grades/create" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Create First Grade
        </a>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Display Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($grades as $grade): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                        <?php echo $grade['level']; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <?php echo htmlspecialchars($grade['name']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo htmlspecialchars($grade['display_name']); ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <?php echo htmlspecialchars($grade['description'] ?? 'N/A'); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="<?php echo BASE_URL; ?>/grades/show/<?php echo $grade['id']; ?>" 
                           class="text-blue-600 hover:text-blue-900 mr-3" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/grades/edit/<?php echo $grade['id']; ?>" 
                           class="text-green-600 hover:text-green-900 mr-3" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="#" onclick="deleteGrade(<?php echo $grade['id']; ?>)" 
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
function deleteGrade(id) {
    if (!confirm('Are you sure you want to delete this grade? This action cannot be undone if the grade has classes or learning areas assigned.')) {
        return;
    }
    
    fetch('<?php echo BASE_URL; ?>/grades/delete/' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Grade deleted successfully');
            location.reload();
        } else {
            alert('Failed to delete grade: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
    });
}
</script>

