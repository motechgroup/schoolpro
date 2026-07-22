<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold mb-6">Assign Book to Student</h1>
            
            <form method="POST" action="<?php echo BASE_URL; ?>/library/storeAssign">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Student <span class="text-red-500">*</span></label>
                        <select name="student_id" required id="studentSelect" class="w-full border rounded px-3 py-2">
                            <option value="">-- Select a Student --</option>
                            <?php foreach ($students as $student): 
                                $balance = floatval($student['fee_balance'] ?? 0);
                                $hasBalance = $balance > 0;
                                $isLibrarian = Auth::hasRole('librarian');
                            ?>
                            <option value="<?php echo $student['id']; ?>" 
                                    data-balance="<?php echo $balance; ?>"
                                    data-has-balance="<?php echo $hasBalance ? '1' : '0'; ?>"
                                    data-can-borrow="<?php echo ($student['can_borrow_more'] ?? true) ? '1' : '0'; ?>"
                                    data-class-id="<?php echo $student['class_id']; ?>"
                                    <?php echo ($hasBalance || !($student['can_borrow_more'] ?? true)) ? 'disabled' : ''; ?>>
                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?> 
                                (<?php echo htmlspecialchars($student['admission_number']); ?>)
                                <?php 
                                $rating = floatval($student['library_rating'] ?? 5.00);
                                $points = intval($student['library_points'] ?? 100);
                                $activeCount = intval($student['active_borrows_count'] ?? 0);
                                $maxBorrows = intval($student['max_borrows'] ?? 3);
                                ?>
                                - Rating: <?php echo number_format($rating, 1); ?>/5 | Points: <?php echo $points; ?> | Borrows: <?php echo $activeCount; ?>/<?php echo $maxBorrows; ?>
                                <?php if ($hasBalance): ?>
                                - <?php echo $isLibrarian ? '(Cannot borrow - Fee balance)' : 'Balance: KES ' . number_format($balance, 2) . ' (Cannot borrow)'; ?>
                                <?php elseif (!$student['can_borrow_more']): ?>
                                - (Cannot borrow - Limit reached)
                                <?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Book <span class="text-red-500">*</span></label>
                        <select name="book_id" required id="bookSelect" class="w-full border rounded px-3 py-2" disabled>
                            <option value="">-- Select a Student First --</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Books will be filtered based on the selected student's class</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Student <span class="text-red-500">*</span></label>
                        <select name="student_id" required id="studentSelect" class="w-full border rounded px-3 py-2">
                            <option value="">-- Select a Student --</option>
                            <?php foreach ($students as $student): 
                                $balance = floatval($student['fee_balance'] ?? 0);
                                $hasBalance = $balance > 0;
                                $isLibrarian = Auth::hasRole('librarian');
                            ?>
                            <option value="<?php echo $student['id']; ?>" 
                                    data-balance="<?php echo $balance; ?>"
                                    data-has-balance="<?php echo $hasBalance ? '1' : '0'; ?>"
                                    data-can-borrow="<?php echo ($student['can_borrow_more'] ?? true) ? '1' : '0'; ?>"
                                    <?php echo ($hasBalance || !($student['can_borrow_more'] ?? true)) ? 'disabled' : ''; ?>>
                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?> 
                                (<?php echo htmlspecialchars($student['admission_number']); ?>)
                                <?php 
                                $rating = floatval($student['library_rating'] ?? 5.00);
                                $points = intval($student['library_points'] ?? 100);
                                $activeCount = intval($student['active_borrows_count'] ?? 0);
                                $maxBorrows = intval($student['max_borrows'] ?? 3);
                                ?>
                                - Rating: <?php echo number_format($rating, 1); ?>/5 | Points: <?php echo $points; ?> | Borrows: <?php echo $activeCount; ?>/<?php echo $maxBorrows; ?>
                                <?php if ($hasBalance): ?>
                                - <?php echo $isLibrarian ? '(Cannot borrow - Fee balance)' : 'Balance: KES ' . number_format($balance, 2) . ' (Cannot borrow)'; ?>
                                <?php elseif (!$student['can_borrow_more']): ?>
                                - (Cannot borrow - Limit reached)
                                <?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Date <span class="text-red-500">*</span></label>
                        <input type="date" name="due_date" required min="<?php echo date('Y-m-d'); ?>" class="w-full border rounded px-3 py-2">
                        <p class="text-xs text-gray-500 mt-1">Select the return due date</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                        <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2" placeholder="Additional notes"></textarea>
                    </div>
                    
                    <div id="balanceWarning" class="hidden bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                        <strong>Warning:</strong> Selected student has an outstanding fee balance and cannot borrow books.
                    </div>
                    <div id="limitWarning" class="hidden bg-orange-100 border border-orange-400 text-orange-700 px-4 py-3 rounded">
                        <strong>Warning:</strong> Selected student has reached their maximum borrowing limit.
                    </div>
                </div>
                
                <input type="hidden" id="studentClassId" name="student_class_id" value="">
                
                <div class="mt-6 flex gap-4">
                    <button type="submit" id="submitBtn" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-hand-holding mr-2"></i>Assign Book
                    </button>
                    <a href="<?php echo BASE_URL; ?>/library" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('studentSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const studentId = this.value;
    const studentClassId = selectedOption.getAttribute('data-class-id');
    const hasBalance = selectedOption.getAttribute('data-has-balance') === '1';
    const canBorrow = selectedOption.getAttribute('data-can-borrow') === '1';
    const balanceWarning = document.getElementById('balanceWarning');
    const limitWarning = document.getElementById('limitWarning');
    const submitBtn = document.getElementById('submitBtn');
    const bookSelect = document.getElementById('bookSelect');
    const studentClassIdInput = document.getElementById('studentClassId');
    
    // Set student class ID
    studentClassIdInput.value = studentClassId || '';
    
    if (!studentId || hasBalance || !canBorrow) {
        bookSelect.disabled = true;
        bookSelect.innerHTML = '<option value="">-- Select a Student First --</option>';
        
        if (hasBalance) {
            balanceWarning.classList.remove('hidden');
            limitWarning.classList.add('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else if (!canBorrow) {
            balanceWarning.classList.add('hidden');
            limitWarning.classList.remove('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            balanceWarning.classList.add('hidden');
            limitWarning.classList.add('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
        return;
    }
    
    // Load books for this student's class
    bookSelect.disabled = true;
    bookSelect.innerHTML = '<option value="">Loading books...</option>';
    
    fetch('<?php echo BASE_URL; ?>/library/getBooksForClass?class_id=' + studentClassId + '&available_only=1')
        .then(response => response.json())
        .then(data => {
            bookSelect.innerHTML = '<option value="">-- Select a Book --</option>';
            
            if (data.success && data.books && data.books.length > 0) {
                data.books.forEach(function(book) {
                    const option = document.createElement('option');
                    option.value = book.id;
                    option.setAttribute('data-available', book.available_copies);
                    option.textContent = book.title + 
                        (book.author ? ' by ' + book.author : '') + 
                        ' (Available: ' + book.available_copies + ')';
                    bookSelect.appendChild(option);
                });
                bookSelect.disabled = false;
            } else {
                bookSelect.innerHTML = '<option value="">No available books for this class</option>';
                bookSelect.disabled = true;
            }
            
            balanceWarning.classList.add('hidden');
            limitWarning.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        })
        .catch(error => {
            console.error('Error loading books:', error);
            bookSelect.innerHTML = '<option value="">Error loading books</option>';
            bookSelect.disabled = true;
        });
});
</script>

