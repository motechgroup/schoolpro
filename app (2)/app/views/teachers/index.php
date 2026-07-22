<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Teacher Management</h1>
        <a href="<?php echo BASE_URL; ?>/teachers/create" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add New Teacher
        </a>
    </div>
    
    <!-- Teachers Table -->
    <?php if (empty($teachers)): ?>
    <div class="bg-white rounded-lg shadow p-6 text-center">
        <p class="text-gray-600">No teachers found.</p>
        <a href="<?php echo BASE_URL; ?>/teachers/create" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Add First Teacher
        </a>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">TSC Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qualification</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($teachers as $teacher): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo htmlspecialchars($teacher['tsc_number'] ?? 'N/A'); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo htmlspecialchars($teacher['email'] ?? 'N/A'); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo htmlspecialchars($teacher['phone'] ?? 'N/A'); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo htmlspecialchars($teacher['qualification'] ?? 'N/A'); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded <?php 
                            echo $teacher['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                        ?>">
                            <?php echo ucfirst($teacher['status']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="<?php echo BASE_URL; ?>/teachers/show/<?php echo $teacher['id']; ?>" 
                           class="text-blue-600 hover:text-blue-900 mr-3" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/teachers/edit/<?php echo $teacher['id']; ?>" 
                           class="text-green-600 hover:text-green-900 mr-3" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="#" onclick="deleteTeacher(<?php echo $teacher['id']; ?>)" 
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
function deleteTeacher(id) {
    if (!confirm('Are you sure you want to delete this teacher? This action cannot be undone if the teacher is assigned to classes.')) {
        return;
    }
    
    fetch('<?php echo BASE_URL; ?>/teachers/delete/' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Teacher deleted successfully');
            location.reload();
        } else {
            alert('Failed to delete teacher: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
    });
}
</script>

