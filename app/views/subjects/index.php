<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Subject Management</h1>
        <a href="<?php echo BASE_URL; ?>/subjects/create" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add New Subject
        </a>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Grade</label>
                <select name="grade_id" class="w-full border rounded px-3 py-2">
                    <option value="">All Grades</option>
                    <?php foreach ($grades as $grade): ?>
                    <option value="<?php echo $grade['id']; ?>" <?php echo ($filters['grade_id'] == $grade['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($grade['display_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>" 
                       placeholder="Search by name or code"
                       class="w-full border rounded px-3 py-2">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 w-full">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
    
    <!-- Subjects Table -->
    <?php if (empty($subjects)): ?>
    <div class="bg-white rounded-lg shadow p-6 text-center">
        <p class="text-gray-600">No subjects found.</p>
        <a href="<?php echo BASE_URL; ?>/subjects/create" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Add First Subject
        </a>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Grade</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($subjects as $subject): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <?php echo htmlspecialchars($subject['code']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                        <?php echo htmlspecialchars($subject['name']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo htmlspecialchars($subject['grade_display_name'] ?? $subject['grade_name']); ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <?php echo htmlspecialchars(substr($subject['description'] ?? '', 0, 50)); ?>
                        <?php if (strlen($subject['description'] ?? '') > 50): ?>...<?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="<?php echo BASE_URL; ?>/subjects/edit/<?php echo $subject['id']; ?>" 
                           class="text-blue-600 hover:text-blue-900 mr-3" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button onclick="deleteSubject(<?php echo $subject['id']; ?>)" 
                                class="text-red-600 hover:text-red-900" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
function deleteSubject(id) {
    if (!confirm('Are you sure you want to delete this subject? This action cannot be undone.')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?php echo BASE_URL; ?>/subjects/delete/' + id;
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = 'csrf_token';
    csrfToken.value = '<?php echo generateCSRFToken(); ?>';
    form.appendChild(csrfToken);
    
    document.body.appendChild(form);
    form.submit();
}
</script>

