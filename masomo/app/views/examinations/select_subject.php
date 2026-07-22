<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Select Subject to Enter Marks</h1>
            <a href="<?php echo BASE_URL; ?>/examinations/show/<?php echo $examination['id']; ?>" 
               class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4"><?php echo htmlspecialchars($examination['name']); ?></h2>
            <p class="text-gray-600 mb-6">Select a subject to enter marks for students.</p>
            
            <?php if (empty($subjects)): ?>
            <div class="text-center py-8">
                <p class="text-gray-600 mb-4">No subjects found for this examination.</p>
                <a href="<?php echo BASE_URL; ?>/examinations/show/<?php echo $examination['id']; ?>" 
                   class="text-blue-600 hover:text-blue-800">
                    Go back to examination details
                </a>
            </div>
            <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($subjects as $subject): ?>
                <a href="<?php echo BASE_URL; ?>/examinations/enterMarks/<?php echo $examination['id']; ?>/<?php echo $subject['id']; ?>" 
                   class="block p-4 border rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-semibold text-lg"><?php echo htmlspecialchars($subject['learning_area_name']); ?></h3>
                            <p class="text-sm text-gray-600">Max Marks: <?php echo $subject['max_marks']; ?></p>
                            <?php if ($subject['teacher_first_name']): ?>
                            <p class="text-xs text-gray-500 mt-1">
                                Teacher: <?php echo htmlspecialchars($subject['teacher_first_name'] . ' ' . $subject['teacher_last_name']); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

