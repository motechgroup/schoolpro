<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Student Report</h1>
        <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-print mr-2"></i>Print
        </button>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    <option value="alumni" <?php echo ($filters['status'] == 'alumni') ? 'selected' : ''; ?>>Alumni</option>
                    <option value="transferred" <?php echo ($filters['status'] == 'transferred') ? 'selected' : ''; ?>>Transferred</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
    
    <!-- Report Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="text-xl font-bold">Student List Report</h2>
            <p class="text-sm text-gray-600">Generated on: <?php echo date('d/m/Y H:i'); ?></p>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admission No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($students)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No students found</td>
                </tr>
                <?php else: ?>
                <?php foreach ($students as $student): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($student['admission_number']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo htmlspecialchars(($student['parent_first_name'] ?? '') . ' ' . ($student['parent_last_name'] ?? '')); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo ucfirst($student['status']); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if (!empty($students)): ?>
        <div class="p-4 border-t bg-gray-50">
            <p class="text-sm text-gray-600">Total Students: <strong><?php echo count($students); ?></strong></p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
@media print {
    nav, .no-print { display: none; }
    body { margin: 0; }
}
</style>

