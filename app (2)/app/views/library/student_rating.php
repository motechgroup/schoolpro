<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Rating Summary Card -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($rating['first_name'] . ' ' . $rating['last_name']); ?></h1>
                    <p class="text-gray-600"><?php echo htmlspecialchars($rating['admission_number']); ?> - <?php echo htmlspecialchars($rating['class_name'] ?? 'N/A'); ?></p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4">
                    <p class="text-sm text-blue-600 mb-1">Rating</p>
                    <p class="text-3xl font-bold text-blue-700"><?php echo number_format($rating['rating'], 2); ?>/5</p>
                    <div class="mt-2">
                        <?php 
                        $stars = round($rating['rating']);
                        for ($i = 1; $i <= 5; $i++): 
                            if ($i <= $stars):
                        ?>
                        <i class="fas fa-star text-yellow-400"></i>
                        <?php else: ?>
                        <i class="far fa-star text-gray-300"></i>
                        <?php endif; endfor; ?>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4">
                    <p class="text-sm text-green-600 mb-1">Total Points</p>
                    <p class="text-3xl font-bold text-green-700"><?php echo number_format($rating['total_points']); ?></p>
                </div>
                
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4">
                    <p class="text-sm text-purple-600 mb-1">Borrowing Level</p>
                    <p class="text-xl font-bold text-purple-700"><?php echo ucfirst($rating['borrowing_level']); ?></p>
                    <p class="text-xs text-purple-600 mt-1">Max: <?php echo $rating['max_borrows']; ?> books</p>
                </div>
                
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-4">
                    <p class="text-sm text-indigo-600 mb-1">Total Borrows</p>
                    <p class="text-3xl font-bold text-indigo-700"><?php echo $rating['total_borrows']; ?></p>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600"><?php echo $rating['on_time_returns']; ?></p>
                    <p class="text-sm text-gray-600">On-Time Returns</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-red-600"><?php echo $rating['late_returns']; ?></p>
                    <p class="text-sm text-gray-600">Late Returns</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-orange-600"><?php echo $rating['damaged_books']; ?></p>
                    <p class="text-sm text-gray-600">Damaged Books</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-red-600"><?php echo $rating['lost_books']; ?></p>
                    <p class="text-sm text-gray-600">Lost Books</p>
                </div>
            </div>
        </div>
        
        <!-- Borrow History -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">Borrow History</h2>
            
            <?php if (empty($borrows)): ?>
            <p class="text-gray-500">No borrow history found.</p>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Book</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Borrow Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Return Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Condition</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Points</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($borrows as $borrow): ?>
                        <tr>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium"><?php echo htmlspecialchars($borrow['book_title']); ?></div>
                                <?php if (!empty($borrow['book_isbn'])): ?>
                                <div class="text-xs text-gray-500">ISBN: <?php echo htmlspecialchars($borrow['book_isbn']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm"><?php echo date('M d, Y', strtotime($borrow['borrow_date'])); ?></td>
                            <td class="px-4 py-3 text-sm"><?php echo date('M d, Y', strtotime($borrow['due_date'])); ?></td>
                            <td class="px-4 py-3 text-sm">
                                <?php if (!empty($borrow['return_date'])): ?>
                                <?php echo date('M d, Y', strtotime($borrow['return_date'])); ?>
                                <?php else: ?>
                                <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <?php if (!empty($borrow['book_condition'])): ?>
                                <span class="px-2 py-1 text-xs rounded <?php 
                                    echo $borrow['book_condition'] == 'excellent' ? 'bg-green-100 text-green-800' : 
                                        ($borrow['book_condition'] == 'good' ? 'bg-blue-100 text-blue-800' : 
                                        ($borrow['book_condition'] == 'fair' ? 'bg-yellow-100 text-yellow-800' : 
                                        ($borrow['book_condition'] == 'poor' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800')));
                                ?>">
                                    <?php echo ucfirst($borrow['book_condition']); ?>
                                </span>
                                <?php else: ?>
                                <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <?php if (intval($borrow['points_awarded'] ?? 0) > 0): ?>
                                <span class="text-green-600 font-semibold">+<?php echo $borrow['points_awarded']; ?></span>
                                <?php endif; ?>
                                <?php if (intval($borrow['points_deducted'] ?? 0) > 0): ?>
                                <span class="text-red-600 font-semibold">-<?php echo $borrow['points_deducted']; ?></span>
                                <?php endif; ?>
                                <?php if (intval($borrow['points_awarded'] ?? 0) == 0 && intval($borrow['points_deducted'] ?? 0) == 0): ?>
                                <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 text-xs rounded <?php 
                                    echo $borrow['status'] == 'borrowed' ? 'bg-blue-100 text-blue-800' : 
                                        ($borrow['status'] == 'returned' ? 'bg-green-100 text-green-800' : 
                                        ($borrow['status'] == 'overdue' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'));
                                ?>">
                                    <?php echo ucfirst($borrow['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

