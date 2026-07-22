<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Create New Examination</h1>
            <a href="<?php echo BASE_URL; ?>/examinations" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to Examinations
            </a>
        </div>
        
        <form id="examinationForm" class="bg-white rounded-lg shadow p-6">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Examination Name *</label>
                    <input type="text" name="name" required 
                           placeholder="e.g., Mid-Term Examination"
                           class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Class *</label>
                    <?php if (empty($classes)): ?>
                        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded">
                            <p class="text-yellow-800 text-sm">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>No classes assigned.</strong> You need to be assigned to a class before you can create examinations. Please contact your administrator.
                            </p>
                        </div>
                        <select name="class_id" id="classSelect" required class="w-full border rounded px-3 py-2" disabled>
                            <option value="">No classes available</option>
                        </select>
                    <?php else: ?>
                        <select name="class_id" id="classSelect" required class="w-full border rounded px-3 py-2">
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>" 
                                    <?php echo ($selectedClassId == $class['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class['grade_display_name'] . ' - ' . $class['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (Auth::hasAnyRole(['teacher', 'head_teacher'])): ?>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                You can only create examinations for classes assigned to you.
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Term *</label>
                    <select name="term" required class="w-full border rounded px-3 py-2">
                        <option value="1">Term 1</option>
                        <option value="2">Term 2</option>
                        <option value="3">Term 3</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year *</label>
                    <input type="text" name="academic_year" required 
                           value="<?php echo htmlspecialchars($academicYear); ?>"
                           placeholder="2024/2025" 
                           class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Examination Date</label>
                    <input type="date" name="exam_date" class="w-full border rounded px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border rounded px-3 py-2">
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                    </select>
                </div>
            </div>
            
            <!-- Subjects Section -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-4">Select Subjects</h3>
                <div id="subjectsContainer" class="border rounded p-4 bg-gray-50">
                    <p class="text-gray-600 text-sm">Please select a class first to load subjects.</p>
                </div>
            </div>
            
            <div class="flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/examinations" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Create Examination
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('classSelect');
    const subjectsContainer = document.getElementById('subjectsContainer');
    const form = document.getElementById('examinationForm');
    
    // Load subjects when class is selected
    classSelect.addEventListener('change', function() {
        const classId = this.value;
        if (!classId) {
            subjectsContainer.innerHTML = '<p class="text-gray-600 text-sm">Please select a class first to load subjects.</p>';
            return;
        }
        
        // Fetch subjects for this class
        fetch(`<?php echo BASE_URL; ?>/examinations/getClassSubjects?class_id=${classId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.subjects.length > 0) {
                    let html = '<div class="space-y-3">';
                    data.subjects.forEach(subject => {
                        html += `
                            <div class="flex items-center space-x-4 p-3 bg-white rounded border">
                                <input type="checkbox" name="subjects[]" value="${subject.id}" 
                                       id="subject_${subject.id}" class="subject-checkbox">
                                <label for="subject_${subject.id}" class="flex-1 cursor-pointer">
                                    <span class="font-medium">${subject.name}</span>
                                    <span class="text-sm text-gray-500 ml-2">(${subject.code})</span>
                                </label>
                                <div class="flex space-x-2">
                                    <input type="number" name="max_marks[${subject.id}]" 
                                           value="100" step="0.01" min="1" max="1000"
                                           placeholder="Max Marks" 
                                           class="w-24 border rounded px-2 py-1 text-sm">
                                    <input type="number" name="passing_marks[${subject.id}]" 
                                           value="40" step="0.01" min="0"
                                           placeholder="Passing" 
                                           class="w-24 border rounded px-2 py-1 text-sm">
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    subjectsContainer.innerHTML = html;
                } else {
                    subjectsContainer.innerHTML = '<p class="text-red-600 text-sm">No subjects found for this class.</p>';
                }
            })
            .catch(error => {
                console.error('Error loading subjects:', error);
                subjectsContainer.innerHTML = '<p class="text-red-600 text-sm">Error loading subjects. Please try again.</p>';
            });
    });
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Check if class select is disabled (no classes available)
        if (classSelect.disabled) {
            alert('You cannot create an examination because you have no assigned classes. Please contact your administrator.');
            return;
        }
        
        const formData = new FormData(form);
        const selectedSubjects = Array.from(document.querySelectorAll('.subject-checkbox:checked'));
        
        if (selectedSubjects.length === 0) {
            alert('Please select at least one subject.');
            return;
        }
        
        fetch('<?php echo BASE_URL; ?>/examinations/store', {
            method: 'POST',
            body: formData
        })
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
    });
    
    // Trigger change if class is pre-selected
    if (classSelect.value) {
        classSelect.dispatchEvent(new Event('change'));
    }
});
</script>

