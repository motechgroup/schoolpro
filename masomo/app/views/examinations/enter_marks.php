<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Enter Marks</h1>
            <p class="text-gray-600 mt-1">
                <?php echo htmlspecialchars($examination['name']); ?> - 
                <?php echo htmlspecialchars($subject['learning_area_name']); ?>
            </p>
        </div>
        <a href="<?php echo BASE_URL; ?>/examinations/show/<?php echo $examination['id']; ?>" 
           class="text-gray-600 hover:text-gray-800">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>
    
    <form id="marksForm" class="bg-white rounded-lg shadow p-6">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="examination_id" value="<?php echo $examination['id']; ?>">
        <input type="hidden" name="examination_subject_id" value="<?php echo $subject['id']; ?>">
        
        <div class="mb-4 p-4 bg-blue-50 rounded-lg">
            <p class="text-sm text-blue-800">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Max Marks:</strong> <?php echo $subject['max_marks']; ?> | 
                <strong>Passing Marks:</strong> <?php echo $subject['passing_marks']; ?>
            </p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admission No.</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marks Obtained</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Grade</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($students as $student): 
                        $existingMark = $existingMarks[$student['id']] ?? null;
                    ?>
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                            <?php echo htmlspecialchars($student['admission_number']); ?>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <input type="number" 
                                   name="marks[<?php echo $student['id']; ?>][marks]" 
                                   value="<?php echo $existingMark ? $existingMark['marks_obtained'] : ''; ?>"
                                   step="0.01" 
                                   min="0" 
                                   max="<?php echo $subject['max_marks']; ?>"
                                   class="marks-input w-24 border rounded px-2 py-1 text-sm"
                                   data-student-id="<?php echo $student['id']; ?>"
                                   data-max-marks="<?php echo $subject['max_marks']; ?>">
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="grade-display text-sm font-semibold" data-student-id="<?php echo $student['id']; ?>">
                                <?php echo $existingMark ? $existingMark['grade'] : '-'; ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <input type="text" 
                                   name="marks[<?php echo $student['id']; ?>][remarks]" 
                                   value="<?php echo $existingMark ? htmlspecialchars($existingMark['remarks']) : ''; ?>"
                                   placeholder="Optional remarks"
                                   class="w-48 border rounded px-2 py-1 text-sm">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-6 flex justify-end space-x-4">
            <a href="<?php echo BASE_URL; ?>/examinations/show/<?php echo $examination['id']; ?>" 
               class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                Cancel
            </a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Save Marks
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('marksForm');
    const marksInputs = document.querySelectorAll('.marks-input');
    const maxMarks = <?php echo $subject['max_marks']; ?>;
    
    // Calculate grade based on marks
    function calculateGrade(marks, maxMarks) {
        if (!marks || maxMarks <= 0) return '-';
        const percentage = (marks / maxMarks) * 100;
        if (percentage >= 80) return 'A';
        if (percentage >= 70) return 'B';
        if (percentage >= 60) return 'C';
        if (percentage >= 50) return 'D';
        if (percentage >= 40) return 'E';
        return 'F';
    }
    
    // Update grade when marks change
    marksInputs.forEach(input => {
        input.addEventListener('input', function() {
            const marks = parseFloat(this.value) || 0;
            const studentId = this.dataset.studentId;
            const gradeDisplay = document.querySelector(`.grade-display[data-student-id="${studentId}"]`);
            if (gradeDisplay) {
                gradeDisplay.textContent = calculateGrade(marks, maxMarks);
            }
        });
    });
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        fetch('<?php echo BASE_URL; ?>/examinations/saveMarks', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Marks saved successfully!');
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
});
</script>

