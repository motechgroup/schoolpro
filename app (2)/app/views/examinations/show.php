<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($examination['name']); ?></h1>
        <div class="flex space-x-3">
            <a href="<?php echo BASE_URL; ?>/examinations/enterMarks/<?php echo $examination['id']; ?>" 
               class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-edit mr-2"></i>Enter Marks
            </a>
            <a href="<?php echo BASE_URL; ?>/examinations" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>
    
    <!-- Examination Details -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="text-sm font-medium text-gray-500">Class</label>
                <p class="text-lg font-semibold"><?php echo htmlspecialchars($examination['class_name']); ?></p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Term</label>
                <p class="text-lg font-semibold">Term <?php echo $examination['term']; ?></p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Academic Year</label>
                <p class="text-lg font-semibold"><?php echo htmlspecialchars($examination['academic_year']); ?></p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Status</label>
                <span class="px-3 py-1 text-sm rounded <?php 
                    echo $examination['status'] == 'active' ? 'bg-green-100 text-green-800' : 
                        ($examination['status'] == 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800');
                ?>">
                    <?php echo ucfirst($examination['status']); ?>
                </span>
            </div>
            <?php if ($examination['exam_date']): ?>
            <div>
                <label class="text-sm font-medium text-gray-500">Examination Date</label>
                <p class="text-lg font-semibold"><?php echo date('d M Y', strtotime($examination['exam_date'])); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Subjects and Progress -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Subjects -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">Subjects (<?php echo count($subjects); ?>)</h2>
            <?php if (empty($subjects)): ?>
            <p class="text-gray-600">No subjects added to this examination.</p>
            <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($subjects as $subject): ?>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                    <div>
                        <span class="font-medium"><?php echo htmlspecialchars($subject['learning_area_name']); ?></span>
                        <span class="text-sm text-gray-500 ml-2">(Max: <?php echo $subject['max_marks']; ?>)</span>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/examinations/enterMarks/<?php echo $examination['id']; ?>/<?php echo $subject['id']; ?>" 
                       class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-edit mr-1"></i>Enter Marks
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Marks Entry Progress -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">Marks Entry Progress</h2>
            <?php if (empty($progress)): ?>
            <p class="text-gray-600">No subjects found.</p>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($progress as $item): ?>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium"><?php echo htmlspecialchars($item['subject_name']); ?></span>
                        <span><?php echo $item['marks_entered']; ?> / <?php echo $item['total_students']; ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" 
                             style="width: <?php echo $item['total_students'] > 0 ? ($item['marks_entered'] / $item['total_students'] * 100) : 0; ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Students List -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">Students (<?php echo count($students); ?>)</h2>
        <?php if (empty($students)): ?>
        <p class="text-gray-600">No students found in this class.</p>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admission No.</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            <?php echo htmlspecialchars($student['admission_number']); ?>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            <?php 
                            // Check if admin - only super admins, school admins, school managers, and head teachers can always view report cards
                            // All other roles (teachers, bursars, accountants, parents, students) are restricted if student has fee balance
                            $isAdmin = Auth::hasAnyRole(['super_admin', 'school_admin', 'school_manager', 'head_teacher']);
                            if ($isAdmin): 
                            ?>
                            <a href="<?php echo BASE_URL; ?>/examinations/reportCard/<?php echo $examination['id']; ?>/<?php echo $student['id']; ?>" 
                               class="text-blue-600 hover:text-blue-800" target="_blank">
                                <i class="fas fa-file-alt mr-1"></i>Report Card
                            </a>
                            <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>/examinations/reportCard/<?php echo $examination['id']; ?>/<?php echo $student['id']; ?>" 
                               class="text-blue-600 hover:text-blue-800" target="_blank" title="Report card access may be restricted if fees are outstanding">
                                <i class="fas fa-file-alt mr-1"></i>Report Card
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

