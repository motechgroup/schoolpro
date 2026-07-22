<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($class['grade_display_name'] . ' - ' . $class['name']); ?></h1>
        <div class="flex space-x-2">
            <a href="<?php echo BASE_URL; ?>/classes/edit/<?php echo $class['id']; ?>" 
               class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <a href="<?php echo BASE_URL; ?>/attendance/mark?class_id=<?php echo $class['id']; ?>" 
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-check-circle mr-2"></i>Mark Attendance
            </a>
            <a href="<?php echo BASE_URL; ?>/classes" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>
    
    <!-- Class Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">Class Information</h2>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">Grade</label>
                    <p class="text-lg font-semibold"><?php echo htmlspecialchars($class['grade_display_name']); ?></p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Class Name</label>
                    <p class="text-lg font-semibold"><?php echo htmlspecialchars($class['name']); ?></p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Academic Year</label>
                    <p class="text-lg"><?php echo htmlspecialchars($class['academic_year']); ?></p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Class Teacher</label>
                    <p class="text-lg">
                        <?php if ($class['teacher_first_name']): ?>
                            <?php echo htmlspecialchars($class['teacher_first_name'] . ' ' . $class['teacher_last_name']); ?>
                        <?php else: ?>
                            <span class="text-gray-400">Not assigned</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Capacity</label>
                    <p class="text-lg"><?php echo $class['capacity']; ?> students</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Status</label>
                    <span class="px-2 py-1 text-xs rounded <?php 
                        echo $class['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                    ?>">
                        <?php echo ucfirst($class['status']); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">Statistics</h2>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">Total Students</label>
                    <p class="text-3xl font-bold text-blue-600"><?php echo count($students); ?></p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Available Spaces</label>
                    <p class="text-2xl font-semibold text-green-600">
                        <?php echo max(0, $class['capacity'] - count($students)); ?>
                    </p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Capacity Utilization</label>
                    <div class="w-full bg-gray-200 rounded-full h-4 mt-2">
                        <div class="bg-blue-600 h-4 rounded-full" style="width: <?php echo $class['capacity'] > 0 ? (count($students) / $class['capacity'] * 100) : 0; ?>%"></div>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">
                        <?php echo $class['capacity'] > 0 ? round((count($students) / $class['capacity']) * 100, 1) : 0; ?>%
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Students List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b flex justify-between items-center">
            <h2 class="text-xl font-bold">Students (<?php echo count($students); ?>)</h2>
            <a href="<?php echo BASE_URL; ?>/students/create?class_id=<?php echo $class['id']; ?>" 
               class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                <i class="fas fa-plus mr-1"></i>Add Student
            </a>
        </div>
        <?php if (empty($students)): ?>
        <div class="p-6 text-center text-gray-500">
            No students assigned to this class yet.
        </div>
        <?php else: ?>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admission No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gender</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($students as $student): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($student['admission_number']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo ucfirst($student['gender']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="<?php echo BASE_URL; ?>/students/show/<?php echo $student['id']; ?>" 
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

