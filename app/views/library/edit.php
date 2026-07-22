<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Book</h1>
            
            <form method="POST" action="<?php echo BASE_URL; ?>/library/update/<?php echo $book['id']; ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ISBN <span class="text-gray-500">(Optional)</span></label>
                        <input type="text" name="isbn" value="<?php echo htmlspecialchars($book['isbn'] ?? ''); ?>" class="w-full border rounded px-3 py-2" placeholder="ISBN">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" required value="<?php echo htmlspecialchars($book['title']); ?>" class="w-full border rounded px-3 py-2" placeholder="Book Title">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Author</label>
                        <input type="text" name="author" value="<?php echo htmlspecialchars($book['author'] ?? ''); ?>" class="w-full border rounded px-3 py-2" placeholder="Author Name">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Publisher</label>
                        <input type="text" name="publisher" value="<?php echo htmlspecialchars($book['publisher'] ?? ''); ?>" class="w-full border rounded px-3 py-2" placeholder="Publisher">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject <span class="text-red-500">*</span></label>
                        <select name="subject_id" required class="w-full border rounded px-3 py-2">
                            <option value="">-- Select Subject --</option>
                            <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo $subject['id']; ?>" <?php echo ($book['subject_id'] == $subject['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($subject['name']); ?>
                                <?php if (!empty($subject['code'])): ?>
                                (<?php echo htmlspecialchars($subject['code']); ?>)
                                <?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Class <span class="text-red-500">*</span></label>
                        <select name="class_id" required class="w-full border rounded px-3 py-2">
                            <option value="">-- Select Class --</option>
                            <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>" <?php echo ($book['class_id'] == $class['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class['grade_display_name'] . ' - ' . $class['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Edition</label>
                        <input type="text" name="edition" value="<?php echo htmlspecialchars($book['edition'] ?? ''); ?>" class="w-full border rounded px-3 py-2" placeholder="e.g., 1st Edition">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Copies <span class="text-red-500">*</span></label>
                        <input type="number" name="total_copies" required min="1" value="<?php echo $book['total_copies']; ?>" class="w-full border rounded px-3 py-2">
                        <p class="text-xs text-gray-500 mt-1">Available copies will be updated automatically based on current borrows</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($book['location'] ?? ''); ?>" class="w-full border rounded px-3 py-2" placeholder="Shelf/Section">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3" class="w-full border rounded px-3 py-2" placeholder="Book description"><?php echo htmlspecialchars($book['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full border rounded px-3 py-2">
                            <option value="active" <?php echo ($book['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($book['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            <option value="lost" <?php echo ($book['status'] == 'lost') ? 'selected' : ''; ?>>Lost</option>
                            <option value="damaged" <?php echo ($book['status'] == 'damaged') ? 'selected' : ''; ?>>Damaged</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-6 flex gap-4">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Update Book
                    </button>
                    <a href="<?php echo BASE_URL; ?>/library" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

