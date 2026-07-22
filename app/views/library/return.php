<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold mb-6">Return Book</h1>
            
            <!-- Book and Student Info -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Book</p>
                        <p class="font-semibold"><?php echo htmlspecialchars($borrow['book_title']); ?></p>
                        <?php if (!empty($borrow['book_isbn'])): ?>
                        <p class="text-sm text-gray-500">ISBN: <?php echo htmlspecialchars($borrow['book_isbn']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Student</p>
                        <p class="font-semibold"><?php echo htmlspecialchars($borrow['student_first_name'] . ' ' . $borrow['student_last_name']); ?></p>
                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($borrow['admission_number']); ?> - <?php echo htmlspecialchars($borrow['class_name'] ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Borrow Date</p>
                        <p class="font-semibold"><?php echo date('M d, Y', strtotime($borrow['borrow_date'])); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Due Date</p>
                        <?php 
                        $dueDate = strtotime($borrow['due_date']);
                        $isOverdue = $dueDate < time();
                        ?>
                        <p class="font-semibold <?php echo $isOverdue ? 'text-red-600' : ''; ?>">
                            <?php echo date('M d, Y', $dueDate); ?>
                            <?php if ($isOverdue): ?>
                            <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded ml-2">Overdue</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <form method="POST" action="<?php echo BASE_URL; ?>/library/return/<?php echo $borrow['id']; ?>">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Book Condition <span class="text-red-500">*</span></label>
                        <select name="book_condition" required class="w-full border rounded px-3 py-2">
                            <option value="excellent">Excellent - Like new, no visible wear</option>
                            <option value="good" selected>Good - Minor wear, fully functional</option>
                            <option value="fair">Fair - Noticeable wear but usable</option>
                            <option value="poor">Poor - Significant damage, barely usable</option>
                            <option value="damaged">Damaged - Requires repair or replacement</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Select the condition of the book when returned</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Condition Notes (Optional)</label>
                        <textarea name="condition_notes" rows="3" class="w-full border rounded px-3 py-2" placeholder="Describe any damage, marks, or issues with the book"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Additional Notes (Optional)</label>
                        <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2" placeholder="Any additional notes about the return"><?php echo htmlspecialchars($borrow['notes'] ?? ''); ?></textarea>
                    </div>
                    
                    <?php if ($isOverdue): ?>
                    <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                        <p class="text-sm text-yellow-800">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            This book is overdue. A fine may be calculated and points may be deducted.
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded p-3">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Points System:</strong> On-time returns earn points (Excellent: +15, Good: +10, Fair: +5). 
                            Late returns deduct points. Damaged books result in additional deductions.
                        </p>
                    </div>
                </div>
                
                <div class="mt-6 flex gap-4">
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                        <i class="fas fa-check mr-2"></i>Confirm Return
                    </button>
                    <a href="<?php echo BASE_URL; ?>/library/borrows" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

