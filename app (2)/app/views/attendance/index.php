<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Mark Attendance</h1>
    
    <!-- Academic Year Filter -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex items-center space-x-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
                <input type="text" name="academic_year" value="<?php echo htmlspecialchars($academicYear); ?>" 
                       placeholder="2024/2025" class="border rounded px-3 py-2">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
    
    <!-- Classes Grid -->
    <?php if (empty($classes)): ?>
    <div class="bg-white rounded-lg shadow p-6 text-center">
        <p class="text-gray-600">No classes found for academic year <?php echo htmlspecialchars($academicYear); ?>.</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($classes as $class): ?>
        <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">
                        <?php echo htmlspecialchars($class['grade_display_name'] ?? $class['grade_name']); ?>
                    </h3>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($class['name']); ?></p>
                </div>
            </div>
            
            <div class="space-y-2 mb-4">
                <?php if (!empty($class['teacher_name'])): ?>
                <p class="text-sm text-gray-600">
                    <i class="fas fa-chalkboard-teacher mr-2"></i>
                    <?php echo htmlspecialchars($class['teacher_name']); ?>
                </p>
                <?php endif; ?>
                <p class="text-sm text-gray-600">
                    <i class="fas fa-users mr-2"></i>
                    <?php echo htmlspecialchars($class['student_count'] ?? 0); ?> students
                </p>
            </div>
            
            <a href="<?php echo BASE_URL; ?>/attendance/mark?class_id=<?php echo $class['id']; ?>" 
               class="block w-full bg-blue-600 text-white text-center px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                <i class="fas fa-check-circle mr-2"></i>Mark Attendance
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

