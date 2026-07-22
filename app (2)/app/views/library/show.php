<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($book['title']); ?></h1>
                    <p class="text-gray-600">by <?php echo htmlspecialchars($book['author'] ?? 'Unknown Author'); ?></p>
                </div>
                <a href="<?php echo BASE_URL; ?>/library/edit/<?php echo $book['id']; ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-600">ISBN</p>
                    <p class="font-medium"><?php echo htmlspecialchars($book['isbn'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Publisher</p>
                    <p class="font-medium"><?php echo htmlspecialchars($book['publisher'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Subject</p>
                    <p class="font-medium"><?php echo htmlspecialchars($book['subject_name'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Class</p>
                    <p class="font-medium"><?php echo htmlspecialchars(($book['grade_display_name'] ?? '') . ' - ' . ($book['class_name'] ?? 'N/A')); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Edition</p>
                    <p class="font-medium"><?php echo htmlspecialchars($book['edition'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Copies</p>
                    <p class="font-medium"><?php echo $book['total_copies']; ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Available Copies</p>
                    <p class="font-medium">
                        <?php if ($book['available_copies'] > 0): ?>
                        <span class="text-green-600 font-semibold"><?php echo $book['available_copies']; ?></span>
                        <?php else: ?>
                        <span class="text-red-600 font-semibold">0</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Location</p>
                    <p class="font-medium"><?php echo htmlspecialchars($book['location'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <span class="px-2 py-1 text-xs rounded <?php 
                        echo $book['status'] == 'active' ? 'bg-green-100 text-green-800' : 
                            ($book['status'] == 'inactive' ? 'bg-gray-100 text-gray-800' : 
                            ($book['status'] == 'lost' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'));
                    ?>">
                        <?php echo ucfirst($book['status']); ?>
                    </span>
                </div>
            </div>
            
            <?php if (!empty($book['description'])): ?>
            <div class="mt-4">
                <p class="text-sm text-gray-600 mb-2">Description</p>
                <p class="text-gray-800"><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Active Borrows -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">Currently Borrowed</h2>
            
            <?php if (empty($activeBorrows)): ?>
            <p class="text-gray-500">No active borrows for this book.</p>
            <?php else: ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Borrow Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($activeBorrows as $borrow): ?>
                    <tr>
                        <td class="px-4 py-3 text-sm">
                            <?php echo htmlspecialchars($borrow['student_first_name'] . ' ' . $borrow['student_last_name']); ?>
                            <br>
                            <span class="text-gray-500 text-xs"><?php echo htmlspecialchars($borrow['admission_number']); ?> - <?php echo htmlspecialchars($borrow['class_name'] ?? 'N/A'); ?></span>
                        </td>
                        <td class="px-4 py-3 text-sm"><?php echo date('M d, Y', strtotime($borrow['borrow_date'])); ?></td>
                        <td class="px-4 py-3 text-sm">
                            <?php 
                            $dueDate = strtotime($borrow['due_date']);
                            $isOverdue = $dueDate < time() && $borrow['status'] !== 'returned';
                            ?>
                            <span class="<?php echo $isOverdue ? 'text-red-600 font-semibold' : ''; ?>">
                                <?php echo date('M d, Y', $dueDate); ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 text-xs rounded <?php 
                                echo $borrow['status'] == 'borrowed' ? 'bg-blue-100 text-blue-800' : 
                                    ($borrow['status'] == 'overdue' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800');
                            ?>">
                                <?php echo ucfirst($borrow['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

