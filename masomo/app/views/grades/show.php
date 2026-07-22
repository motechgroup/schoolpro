<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($grade['display_name']); ?></h1>
        <div class="flex space-x-2">
            <a href="<?php echo BASE_URL; ?>/grades/edit/<?php echo $grade['id']; ?>" 
               class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <a href="<?php echo BASE_URL; ?>/classes/create?grade_id=<?php echo $grade['id']; ?>" 
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Create Class
            </a>
            <a href="<?php echo BASE_URL; ?>/grades" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>
    
    <!-- Grade Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">Grade Information</h2>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">Grade Code</label>
                    <p class="text-lg font-semibold"><?php echo htmlspecialchars($grade['name']); ?></p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Display Name</label>
                    <p class="text-lg font-semibold"><?php echo htmlspecialchars($grade['display_name']); ?></p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Level</label>
                    <p class="text-lg"><?php echo $grade['level']; ?></p>
                </div>
                <?php if (!empty($grade['description'])): ?>
                <div>
                    <label class="text-sm font-medium text-gray-500">Description</label>
                    <p class="text-lg"><?php echo htmlspecialchars($grade['description']); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">Statistics</h2>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">Total Classes</label>
                    <p class="text-3xl font-bold text-blue-600"><?php echo count($classes); ?></p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Total Students</label>
                    <p class="text-3xl font-bold text-green-600"><?php echo number_format($studentCount); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Classes List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b flex justify-between items-center">
            <h2 class="text-xl font-bold">Classes (<?php echo count($classes); ?>)</h2>
            <a href="<?php echo BASE_URL; ?>/classes/create?grade_id=<?php echo $grade['id']; ?>" 
               class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                <i class="fas fa-plus mr-1"></i>Add Class
            </a>
        </div>
        <?php if (empty($classes)): ?>
        <div class="p-6 text-center text-gray-500">
            No classes created for this grade yet.
        </div>
        <?php else: ?>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class Teacher</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Academic Year</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($classes as $class): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <?php echo htmlspecialchars($class['name']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php if ($class['class_teacher_id']): ?>
                            <span class="text-gray-600">Assigned</span>
                        <?php else: ?>
                            <span class="text-gray-400">Not assigned</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($class['academic_year']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="<?php echo BASE_URL; ?>/classes/show/<?php echo $class['id']; ?>" 
                           class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

