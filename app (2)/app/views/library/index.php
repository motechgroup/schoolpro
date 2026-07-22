<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Library - Books</h1>
        <a href="<?php echo BASE_URL; ?>/library/create" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add New Book
        </a>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>" 
                       placeholder="Title, Author, or ISBN" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <select name="subject_id" class="w-full border rounded px-3 py-2">
                    <option value="">All Subjects</option>
                    <?php foreach ($subjects as $subject): ?>
                    <option value="<?php echo $subject['id']; ?>" <?php echo ($filters['subject_id'] == $subject['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($subject['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                <select name="class_id" class="w-full border rounded px-3 py-2">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $class): ?>
                    <option value="<?php echo $class['id']; ?>" <?php echo ($filters['class_id'] == $class['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($class['grade_display_name'] . ' - ' . $class['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border rounded px-3 py-2">
                    <option value="">All Status</option>
                    <option value="active" <?php echo ($filters['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($filters['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    <option value="lost" <?php echo ($filters['status'] == 'lost') ? 'selected' : ''; ?>>Lost</option>
                    <option value="damaged" <?php echo ($filters['status'] == 'damaged') ? 'selected' : ''; ?>>Damaged</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <label class="flex items-center">
                    <input type="checkbox" name="available_only" value="1" <?php echo (isset($filters['available_only']) && $filters['available_only']) ? 'checked' : ''; ?> class="mr-2">
                    <span class="text-sm">Available Only</span>
                </label>
                <button type="submit" class="flex-1 bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
    
    <!-- Books Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ISBN</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Author</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Copies</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Available</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($books)): ?>
                <tr>
                    <td colspan="9" class="px-6 py-4 text-center text-gray-500">No books found</td>
                </tr>
                <?php else: ?>
                <?php foreach ($books as $book): 
                    $available = intval($book['available_copies'] ?? 0);
                    $borrowed = intval($book['borrowed_count'] ?? 0);
                    $total = intval($book['total_copies'] ?? 0);
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($book['isbn'] ?? 'N/A'); ?></td>
                    <td class="px-6 py-4 text-sm">
                        <a href="<?php echo BASE_URL; ?>/library/show/<?php echo $book['id']; ?>" class="text-blue-600 hover:text-blue-900 font-medium">
                            <?php echo htmlspecialchars($book['title']); ?>
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($book['author'] ?? 'N/A'); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($book['subject_name'] ?? 'N/A'); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars(($book['grade_display_name'] ?? '') . ' - ' . ($book['class_name'] ?? 'N/A')); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo $total; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php if ($available > 0): ?>
                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800 font-semibold">
                            <?php echo $available; ?>
                        </span>
                        <?php else: ?>
                        <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800 font-semibold">
                            0
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded <?php 
                            echo $book['status'] == 'active' ? 'bg-green-100 text-green-800' : 
                                ($book['status'] == 'inactive' ? 'bg-gray-100 text-gray-800' : 
                                ($book['status'] == 'lost' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'));
                        ?>">
                            <?php echo ucfirst($book['status']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="<?php echo BASE_URL; ?>/library/show/<?php echo $book['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/library/edit/<?php echo $book['id']; ?>" class="text-green-600 hover:text-green-900 mr-3" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="#" onclick="deleteBook(<?php echo $book['id']; ?>, '<?php echo htmlspecialchars($book['title']); ?>'); return false;" 
                           class="text-red-600 hover:text-red-900" title="Delete">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function deleteBook(id, title) {
    if (!confirm('Are you sure you want to delete book: ' + title + '?\n\nThis action cannot be undone.')) {
        return;
    }
    
    fetch('<?php echo BASE_URL; ?>/library/delete/' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Book deleted successfully');
            location.reload();
        } else {
            alert('Failed to delete book: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
        console.error('Error:', error);
    });
}
</script>

