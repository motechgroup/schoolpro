<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Students</h1>
        <?php if (!Auth::hasAnyRole(['teacher'])): ?>
        <a href="<?php echo BASE_URL; ?>/students/create" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add New Student
        </a>
        <?php endif; ?>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>" 
                       placeholder="Name or Admission No" class="w-full border rounded px-3 py-2">
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
                    <option value="active" <?php echo ($filters['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($filters['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive (Deleted)</option>
                    <option value="alumni" <?php echo ($filters['status'] == 'alumni') ? 'selected' : ''; ?>>Alumni</option>
                    <option value="transferred" <?php echo ($filters['status'] == 'transferred') ? 'selected' : ''; ?>>Transferred</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fee Status</label>
                <select name="fee_status" class="w-full border rounded px-3 py-2">
                    <option value="">All Students</option>
                    <option value="with_balance" <?php echo ($filters['fee_status'] == 'with_balance') ? 'selected' : ''; ?>>With Balance</option>
                    <option value="no_balance" <?php echo ($filters['fee_status'] == 'no_balance') ? 'selected' : ''; ?>>No Balance</option>
                    <option value="fully_paid" <?php echo ($filters['fee_status'] == 'fully_paid') ? 'selected' : ''; ?>>Fully Paid</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
    
    <!-- Students Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admission No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fee Balance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($students)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No students found</td>
                </tr>
                <?php else: ?>
                <?php foreach ($students as $student): 
                    $balance = floatval($student['total_balance'] ?? 0);
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"><?php echo htmlspecialchars($student['admission_number']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo htmlspecialchars(($student['parent_first_name'] ?? '') . ' ' . ($student['parent_last_name'] ?? '')); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php if ($balance > 0): ?>
                        <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800 font-semibold">
                            <i class="fas fa-exclamation-circle mr-1"></i>KES <?php echo number_format($balance, 2); ?>
                        </span>
                        <?php else: ?>
                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>KES 0.00
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded <?php 
                            echo $student['status'] == 'active' ? 'bg-green-100 text-green-800' : 
                                ($student['status'] == 'alumni' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800');
                        ?>">
                            <?php echo ucfirst($student['status']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="<?php echo BASE_URL; ?>/students/show/<?php echo $student['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php if (!Auth::hasAnyRole(['teacher'])): ?>
                        <a href="<?php echo BASE_URL; ?>/students/edit/<?php echo $student['id']; ?>" class="text-green-600 hover:text-green-900 mr-3" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar', 'accountant'])): ?>
                        <a href="<?php echo BASE_URL; ?>/studentfees/assign/<?php echo $student['id']; ?>" class="text-purple-600 hover:text-purple-900 mr-3" title="Assign Fees">
                            <i class="fas fa-money-bill-wave"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (!Auth::hasAnyRole(['teacher'])): ?>
                        <?php if ($student['status'] == 'active'): ?>
                        <a href="#" onclick="deleteStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>'); return false;" 
                           class="text-red-600 hover:text-red-900" title="Delete">
                            <i class="fas fa-trash"></i>
                        </a>
                        <?php else: ?>
                        <a href="#" onclick="restoreStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>'); return false;" 
                           class="text-green-600 hover:text-green-900" title="Restore">
                            <i class="fas fa-undo"></i>
                        </a>
                        <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function deleteStudent(id, studentName) {
    if (!confirm('Are you sure you want to delete student: ' + studentName + '?\n\nThis will set the student status to inactive. You can restore them later if needed.')) {
        return;
    }
    
    fetch('<?php echo BASE_URL; ?>/students/delete/' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Student deleted successfully');
            location.reload();
        } else {
            alert('Failed to delete student: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
        console.error('Error:', error);
    });
}

function restoreStudent(id, studentName) {
    if (!confirm('Restore student: ' + studentName + '?\n\nThis will set the student status back to active.')) {
        return;
    }
    
    fetch('<?php echo BASE_URL; ?>/students/restore/' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Student restored successfully');
            location.reload();
        } else {
            alert('Failed to restore student: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
        console.error('Error:', error);
    });
}
</script>

