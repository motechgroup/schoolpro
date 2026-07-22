<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Library - Borrows</h1>
        <a href="<?php echo BASE_URL; ?>/library/assign" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-hand-holding mr-2"></i>Assign Book
        </a>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border rounded px-3 py-2">
                    <option value="">All Status</option>
                    <option value="borrowed" <?php echo ($filters['status'] == 'borrowed') ? 'selected' : ''; ?>>Borrowed</option>
                    <option value="returned" <?php echo ($filters['status'] == 'returned') ? 'selected' : ''; ?>>Returned</option>
                    <option value="overdue" <?php echo ($filters['status'] == 'overdue') ? 'selected' : ''; ?>>Overdue</option>
                    <option value="lost" <?php echo ($filters['status'] == 'lost') ? 'selected' : ''; ?>>Lost</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <label class="flex items-center">
                    <input type="checkbox" name="overdue_only" value="1" <?php echo (isset($filters['overdue_only']) && $filters['overdue_only']) ? 'checked' : ''; ?> class="mr-2">
                    <span class="text-sm">Overdue Only</span>
                </label>
                <button type="submit" class="flex-1 bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
    
    <!-- Borrows Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Book</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Borrow Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Return Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Condition</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Points</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fine</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($borrows)): ?>
                <tr>
                    <td colspan="10" class="px-6 py-4 text-center text-gray-500">No borrows found</td>
                </tr>
                <?php else: ?>
                <?php foreach ($borrows as $borrow): 
                    $dueDate = strtotime($borrow['due_date']);
                    $isOverdue = $dueDate < time() && $borrow['status'] === 'borrowed';
                ?>
                <tr>
                    <td class="px-6 py-4 text-sm">
                        <div class="font-medium"><?php echo htmlspecialchars($borrow['book_title']); ?></div>
                        <?php if (!empty($borrow['book_isbn'])): ?>
                        <div class="text-gray-500 text-xs">ISBN: <?php echo htmlspecialchars($borrow['book_isbn']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <div><?php echo htmlspecialchars($borrow['student_first_name'] . ' ' . $borrow['student_last_name']); ?></div>
                        <div class="text-gray-500 text-xs"><?php echo htmlspecialchars($borrow['admission_number']); ?></div>
                        <div class="text-gray-500 text-xs"><?php echo htmlspecialchars($borrow['class_name'] ?? 'N/A'); ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo date('M d, Y', strtotime($borrow['borrow_date'])); ?>
                        <?php if (!empty($borrow['borrowed_by_first_name'])): ?>
                        <div class="text-gray-500 text-xs">by <?php echo htmlspecialchars($borrow['borrowed_by_first_name'] . ' ' . $borrow['borrowed_by_last_name']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="<?php echo $isOverdue ? 'text-red-600 font-semibold' : ''; ?>">
                            <?php echo date('M d, Y', $dueDate); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php if (!empty($borrow['return_date'])): ?>
                        <?php echo date('M d, Y', strtotime($borrow['return_date'])); ?>
                        <?php if (!empty($borrow['returned_to_first_name'])): ?>
                        <div class="text-gray-500 text-xs">by <?php echo htmlspecialchars($borrow['returned_to_first_name'] . ' ' . $borrow['returned_to_last_name']); ?></div>
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="text-gray-400">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php if (!empty($borrow['book_condition']) && $borrow['status'] === 'returned'): ?>
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
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
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
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php if (floatval($borrow['fine_amount'] ?? 0) > 0): ?>
                        <span class="text-red-600 font-semibold">KES <?php echo number_format($borrow['fine_amount'], 2); ?></span>
                        <?php if ($borrow['fine_paid']): ?>
                        <div class="text-green-600 text-xs">Paid</div>
                        <?php else: ?>
                        <div class="text-red-600 text-xs">Unpaid</div>
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="text-gray-400">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded <?php 
                            echo $borrow['status'] == 'borrowed' ? 'bg-blue-100 text-blue-800' : 
                                ($borrow['status'] == 'returned' ? 'bg-green-100 text-green-800' : 
                                ($borrow['status'] == 'overdue' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'));
                        ?>">
                            <?php echo ucfirst($borrow['status']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <?php if ($borrow['status'] === 'borrowed' || $borrow['status'] === 'overdue'): ?>
                        <a href="#" onclick="returnBook(<?php echo $borrow['id']; ?>); return false;" class="text-green-600 hover:text-green-900 mr-3" title="Return">
                            <i class="fas fa-undo"></i>
                        </a>
                        <a href="#" onclick="markLost(<?php echo $borrow['id']; ?>); return false;" class="text-red-600 hover:text-red-900" title="Mark as Lost">
                            <i class="fas fa-exclamation-triangle"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Return Book Modal -->
<div id="returnModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-bold mb-4">Return Book</h3>
        <form id="returnForm" method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Book Condition <span class="text-red-500">*</span></label>
                <select name="book_condition" required class="w-full border rounded px-3 py-2">
                    <option value="excellent">Excellent - Like new</option>
                    <option value="good" selected>Good - Minor wear</option>
                    <option value="fair">Fair - Noticeable wear</option>
                    <option value="poor">Poor - Significant damage</option>
                    <option value="damaged">Damaged - Requires repair</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Condition Notes (Optional)</label>
                <textarea name="condition_notes" rows="2" class="w-full border rounded px-3 py-2" placeholder="Describe any damage or issues"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Additional Notes (Optional)</label>
                <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2"></textarea>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Return Book
                </button>
                <button type="button" onclick="closeReturnModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Mark Lost Modal -->
<div id="lostModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-bold mb-4">Mark Book as Lost</h3>
        <form id="lostForm" method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Fine Amount (KES)</label>
                <input type="number" name="fine_amount" step="0.01" min="0" value="0" class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2"></textarea>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    Mark as Lost
                </button>
                <button type="button" onclick="closeLostModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function returnBook(id) {
    // Redirect to return page instead of modal for better UX with condition selection
    window.location.href = '<?php echo BASE_URL; ?>/library/return/' + id;
}

function closeReturnModal() {
    document.getElementById('returnModal').classList.add('hidden');
}

function markLost(id) {
    document.getElementById('lostForm').action = '<?php echo BASE_URL; ?>/library/markLost/' + id;
    document.getElementById('lostModal').classList.remove('hidden');
}

function closeLostModal() {
    document.getElementById('lostModal').classList.add('hidden');
}
</script>

