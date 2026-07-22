<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Mark Attendance</h1>
    
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Class</label>
                <p class="text-lg font-semibold"><?php echo htmlspecialchars($class['grade_display_name'] . ' - ' . $class['name']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Date</label>
                <input type="date" id="attendanceDate" value="<?php echo htmlspecialchars($date); ?>" 
                       class="border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Total Students</label>
                <p class="text-lg font-semibold"><?php echo count($students); ?></p>
            </div>
        </div>
    </div>
    
    <form id="attendanceForm">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
        <input type="hidden" name="date" id="formDate" value="<?php echo htmlspecialchars($date); ?>">
        
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admission No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Present</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Absent</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Late</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Excused</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($student['admission_number']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="present" 
                                   <?php echo (($existingAttendance[$student['id']] ?? 'present') == 'present') ? 'checked' : ''; ?>
                                   class="form-radio">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="absent"
                                   <?php echo (($existingAttendance[$student['id']] ?? '') == 'absent') ? 'checked' : ''; ?>
                                   class="form-radio">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="late"
                                   <?php echo (($existingAttendance[$student['id']] ?? '') == 'late') ? 'checked' : ''; ?>
                                   class="form-radio">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="excused"
                                   <?php echo (($existingAttendance[$student['id']] ?? '') == 'excused') ? 'checked' : ''; ?>
                                   class="form-radio">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-6 flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Save Attendance
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('attendanceDate').addEventListener('change', function() {
    const newDate = this.value;
    document.getElementById('formDate').value = newDate;
    window.location.href = '<?php echo BASE_URL; ?>/attendance/mark?class_id=<?php echo $class['id']; ?>&date=' + newDate;
});

document.getElementById('attendanceForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/attendance/save', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Attendance saved successfully!');
            location.reload();
        } else {
            alert('Failed to save attendance: ' + (data.message || 'Unknown error'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Attendance';
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Attendance';
    }
});
</script>

