<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Examinations</h1>
        <a href="<?php echo BASE_URL; ?>/examinations/create" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Create New Examination
        </a>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Term</label>
                <select name="term" class="w-full border rounded px-3 py-2">
                    <option value="">All Terms</option>
                    <option value="1" <?php echo ($filters['term'] == '1') ? 'selected' : ''; ?>>Term 1</option>
                    <option value="2" <?php echo ($filters['term'] == '2') ? 'selected' : ''; ?>>Term 2</option>
                    <option value="3" <?php echo ($filters['term'] == '3') ? 'selected' : ''; ?>>Term 3</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
                <input type="text" name="academic_year" value="<?php echo htmlspecialchars($filters['academic_year']); ?>" 
                       placeholder="2024/2025" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border rounded px-3 py-2">
                    <option value="">All Status</option>
                    <option value="draft" <?php echo ($filters['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                    <option value="active" <?php echo ($filters['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="completed" <?php echo ($filters['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
            <div class="md:col-span-4 flex justify-end">
                <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
    
    <!-- Examinations Table -->
    <?php if (empty($examinations)): ?>
    <div class="bg-white rounded-lg shadow p-6 text-center">
        <p class="text-gray-600">No examinations found.</p>
        <a href="<?php echo BASE_URL; ?>/examinations/create" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Create First Examination
        </a>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Examination Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Academic Year</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subjects</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Students</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($examinations as $exam): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <?php echo htmlspecialchars($exam['name']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo htmlspecialchars($exam['class_name']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">Term <?php echo $exam['term']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($exam['academic_year']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="font-semibold"><?php echo $exam['subject_count'] ?? 0; ?></span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="font-semibold"><?php echo $exam['student_count'] ?? 0; ?></span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded <?php 
                            echo $exam['status'] == 'active' ? 'bg-green-100 text-green-800' : 
                                ($exam['status'] == 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800');
                        ?>">
                            <?php echo ucfirst($exam['status']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="<?php echo BASE_URL; ?>/examinations/show/<?php echo $exam['id']; ?>" 
                           class="text-blue-600 hover:text-blue-900 mr-3" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/examinations/enterMarks/<?php echo $exam['id']; ?>" 
                           class="text-green-600 hover:text-green-900" title="Enter Marks">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

