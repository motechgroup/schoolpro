<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Edit Academic Year: <?php echo htmlspecialchars($academicYear['name']); ?></h1>
            <a href="<?php echo BASE_URL; ?>/academicyears" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Edit Academic Year Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">Academic Year Details</h2>
                
                <form id="academicYearForm" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year Name *</label>
                        <input type="text" name="name" required 
                               value="<?php echo htmlspecialchars($academicYear['name']); ?>"
                               class="w-full border rounded px-3 py-2"
                               pattern="\d{4}/\d{4}">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                            <input type="date" name="start_date" required 
                                   value="<?php echo $academicYear['start_date']; ?>"
                                   class="w-full border rounded px-3 py-2">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                            <input type="date" name="end_date" required 
                                   value="<?php echo $academicYear['end_date']; ?>"
                                   class="w-full border rounded px-3 py-2">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                        <select name="status" required class="w-full border rounded px-3 py-2">
                            <option value="upcoming" <?php echo $academicYear['status'] == 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                            <option value="active" <?php echo $academicYear['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="completed" <?php echo $academicYear['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="archived" <?php echo $academicYear['status'] == 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="is_current" id="is_current" value="1" 
                               <?php echo $academicYear['is_current'] ? 'checked' : ''; ?> class="mr-2">
                        <label for="is_current" class="text-sm text-gray-700">
                            Set as current academic year
                        </label>
                    </div>
                    
                    <div class="flex justify-end space-x-4 pt-4">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                            <i class="fas fa-save mr-2"></i>Update Academic Year
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Terms Management -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">Terms</h2>
                    <button onclick="openCreateTermModal()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                        <i class="fas fa-plus mr-1"></i>Add Term
                    </button>
                </div>
                
                <?php if (empty($terms)): ?>
                <p class="text-gray-500 text-sm">No terms defined. Click "Add Term" to create one.</p>
                <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($terms as $term): ?>
                    <div class="border rounded-lg p-4 <?php echo $term['is_current'] ? 'bg-green-50 border-green-300' : ''; ?>">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <h3 class="font-semibold"><?php echo htmlspecialchars($term['name']); ?></h3>
                                    <?php if ($term['is_current']): ?>
                                    <span class="bg-green-500 text-white text-xs px-2 py-1 rounded">
                                        <i class="fas fa-check-circle mr-1"></i>Current
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    <?php echo formatDate($term['start_date'], 'd M Y'); ?> - 
                                    <?php echo formatDate($term['end_date'], 'd M Y'); ?>
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Status: <?php echo ucfirst($term['status']); ?>
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="openEditTermModal(<?php echo htmlspecialchars(json_encode($term)); ?>)" 
                                        class="text-blue-600 hover:text-blue-800 text-sm">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteTerm(<?php echo $term['id']; ?>)" 
                                        class="text-red-600 hover:text-red-800 text-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Term Modal -->
<div id="termModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold" id="modalTitle">Add Term</h3>
                <button onclick="closeTermModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="termForm" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="academic_year_id" value="<?php echo $academicYear['id']; ?>">
                <input type="hidden" name="term_id" id="term_id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Term Number *</label>
                    <input type="number" name="term_number" id="term_number" required min="1" max="3"
                           class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Term Name *</label>
                    <input type="text" name="name" id="term_name" required 
                           placeholder="e.g., Term 1"
                           class="w-full border rounded px-3 py-2">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                        <input type="date" name="start_date" id="term_start_date" required 
                               class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                        <input type="date" name="end_date" id="term_end_date" required 
                               class="w-full border rounded px-3 py-2">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                    <select name="status" id="term_status" required class="w-full border rounded px-3 py-2">
                        <option value="upcoming">Upcoming</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="is_current" id="term_is_current" value="1" class="mr-2">
                    <label for="term_is_current" class="text-sm text-gray-700">
                        Set as current term
                    </label>
                </div>
                
                <div class="flex justify-end space-x-4 pt-4">
                    <button type="button" onclick="closeTermModal()" 
                            class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Save Term
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Academic Year Form
document.getElementById('academicYearForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/academicyears/update/<?php echo $academicYear['id']; ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            alert('Error: ' + data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Term Modal Functions
function openCreateTermModal() {
    document.getElementById('modalTitle').textContent = 'Add Term';
    document.getElementById('termForm').reset();
    document.getElementById('term_id').value = '';
    document.getElementById('termModal').classList.remove('hidden');
}

function openEditTermModal(term) {
    document.getElementById('modalTitle').textContent = 'Edit Term';
    document.getElementById('term_id').value = term.id;
    document.getElementById('term_number').value = term.term_number;
    document.getElementById('term_name').value = term.name;
    document.getElementById('term_start_date').value = term.start_date;
    document.getElementById('term_end_date').value = term.end_date;
    document.getElementById('term_status').value = term.status;
    document.getElementById('term_is_current').checked = term.is_current == 1;
    document.getElementById('termModal').classList.remove('hidden');
}

function closeTermModal() {
    document.getElementById('termModal').classList.add('hidden');
}

// Term Form
document.getElementById('termForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const termId = document.getElementById('term_id').value;
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    
    try {
        const url = termId 
            ? `<?php echo BASE_URL; ?>/academicyears/updateTerm/${termId}`
            : `<?php echo BASE_URL; ?>/academicyears/createTerm`;
        
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            alert('Error: ' + data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Delete Term
function deleteTerm(termId) {
    if (!confirm('Are you sure you want to delete this term? This action cannot be undone.')) {
        return;
    }
    
    const csrfToken = '<?php echo $csrf_token; ?>';
    fetch(`<?php echo BASE_URL; ?>/academicyears/deleteTerm/${termId}?csrf_token=${csrfToken}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
}
</script>

