<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Action Buttons -->
        <div class="mb-4 flex justify-end space-x-2">
            <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-print mr-2"></i>Print Report Card
            </button>
            <?php if (Auth::hasAnyRole(['super_admin', 'school_admin', 'school_manager', 'head_teacher'])): ?>
            <button onclick="sendReportCardSms()" id="btnSendSms" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-sms mr-2"></i>Send via SMS
            </button>
            <button onclick="sendReportCardEmail()" id="btnSendEmail" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                <i class="fas fa-envelope mr-2"></i>Send via Email (PDF)
            </button>
            <?php endif; ?>
        </div>
        
        <!-- Status Messages -->
        <div id="statusMessage" class="mb-4 hidden"></div>
        
        <!-- Report Card -->
        <div class="bg-white rounded-lg shadow-lg p-8 print:shadow-none">
            <!-- Header -->
            <div class="text-center mb-8 border-b pb-4">
                <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($schoolName ?? APP_NAME); ?></h1>
                <?php if (!empty($schoolSettings['school_address'])): ?>
                <p class="text-sm text-gray-600 mb-1"><?php echo htmlspecialchars($schoolSettings['school_address']); ?></p>
                <?php endif; ?>
                <?php if (!empty($schoolSettings['school_phone'])): ?>
                <p class="text-sm text-gray-600 mb-1">Tel: <?php echo htmlspecialchars($schoolSettings['school_phone']); ?></p>
                <?php endif; ?>
                <?php if (!empty($schoolSettings['school_email'])): ?>
                <p class="text-sm text-gray-600 mb-3">Email: <?php echo htmlspecialchars($schoolSettings['school_email']); ?></p>
                <?php endif; ?>
                <h2 class="text-2xl font-bold mb-2 mt-4">REPORT CARD</h2>
                <p class="text-lg text-gray-700"><?php echo htmlspecialchars($examination['name']); ?></p>
                <p class="text-sm text-gray-600">
                    Term <?php echo $examination['term']; ?> - <?php echo htmlspecialchars($examination['academic_year']); ?>
                </p>
            </div>
            
            <!-- Student Information -->
            <div class="grid grid-cols-2 gap-6 mb-8">
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Student Information</h3>
                    <table class="text-sm">
                        <tr>
                            <td class="pr-4 py-1 text-gray-600">Name:</td>
                            <td class="font-semibold"><?php echo htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']); ?></td>
                        </tr>
                        <tr>
                            <td class="pr-4 py-1 text-gray-600">Admission No:</td>
                            <td><?php echo htmlspecialchars($student['admission_number']); ?></td>
                        </tr>
                        <tr>
                            <td class="pr-4 py-1 text-gray-600">Class:</td>
                            <td><?php echo htmlspecialchars($student['class_name']); ?></td>
                        </tr>
                        <tr>
                            <td class="pr-4 py-1 text-gray-600">Grade:</td>
                            <td><?php echo htmlspecialchars($student['grade_display_name']); ?></td>
                        </tr>
                    </table>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Examination Details</h3>
                    <table class="text-sm">
                        <tr>
                            <td class="pr-4 py-1 text-gray-600">Examination:</td>
                            <td><?php echo htmlspecialchars($examination['name']); ?></td>
                        </tr>
                        <tr>
                            <td class="pr-4 py-1 text-gray-600">Term:</td>
                            <td>Term <?php echo $examination['term']; ?></td>
                        </tr>
                        <tr>
                            <td class="pr-4 py-1 text-gray-600">Academic Year:</td>
                            <td><?php echo htmlspecialchars($examination['academic_year']); ?></td>
                        </tr>
                        <?php if ($examination['exam_date']): ?>
                        <tr>
                            <td class="pr-4 py-1 text-gray-600">Date:</td>
                            <td><?php echo date('d M Y', strtotime($examination['exam_date'])); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
            
            <!-- Marks Table -->
            <div class="mb-8">
                <h3 class="font-semibold text-gray-700 mb-4">Subject Performance</h3>
                <table class="min-w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-300 px-4 py-2 text-left">Subject</th>
                            <th class="border border-gray-300 px-4 py-2 text-center">Marks Obtained</th>
                            <th class="border border-gray-300 px-4 py-2 text-center">Max Marks</th>
                            <th class="border border-gray-300 px-4 py-2 text-center">Percentage</th>
                            <th class="border border-gray-300 px-4 py-2 text-center">Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $marksMap = [];
                        foreach ($marks as $mark) {
                            $marksMap[$mark['learning_area_id']] = $mark;
                        }
                        
                        foreach ($subjects as $subject): 
                            $mark = $marksMap[$subject['learning_area_id']] ?? null;
                            $marksObtained = $mark ? floatval($mark['marks_obtained']) : 0;
                            $maxMarks = floatval($subject['max_marks']);
                            $percentage = $maxMarks > 0 ? round(($marksObtained / $maxMarks) * 100, 2) : 0;
                            $grade = $mark ? $mark['grade'] : '-';
                        ?>
                        <tr>
                            <td class="border border-gray-300 px-4 py-2 font-medium">
                                <?php echo htmlspecialchars($subject['learning_area_name']); ?>
                            </td>
                            <td class="border border-gray-300 px-4 py-2 text-center">
                                <?php echo $mark ? number_format($marksObtained, 2) : '-'; ?>
                            </td>
                            <td class="border border-gray-300 px-4 py-2 text-center">
                                <?php echo number_format($maxMarks, 2); ?>
                            </td>
                            <td class="border border-gray-300 px-4 py-2 text-center">
                                <?php echo $mark ? $percentage . '%' : '-'; ?>
                            </td>
                            <td class="border border-gray-300 px-4 py-2 text-center font-semibold">
                                <?php echo $grade; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <!-- Total Row -->
                        <tr class="bg-gray-50 font-semibold">
                            <td class="border border-gray-300 px-4 py-2">TOTAL</td>
                            <td class="border border-gray-300 px-4 py-2 text-center"><?php echo number_format($totalMarks, 2); ?></td>
                            <td class="border border-gray-300 px-4 py-2 text-center"><?php echo number_format($totalMaxMarks, 2); ?></td>
                            <td class="border border-gray-300 px-4 py-2 text-center"><?php echo $overallPercentage; ?>%</td>
                            <td class="border border-gray-300 px-4 py-2 text-center"><?php echo $overallGrade; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Grading Scale -->
            <div class="mb-8 text-sm">
                <h3 class="font-semibold text-gray-700 mb-2">Grading Scale</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div>A: 80% and above</div>
                    <div>B: 70% - 79%</div>
                    <div>C: 60% - 69%</div>
                    <div>D: 50% - 59%</div>
                    <div>E: 40% - 49%</div>
                    <div>F: Below 40%</div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="mt-8 pt-4 border-t grid grid-cols-2 gap-8 text-sm">
                <div>
                    <p class="font-semibold mb-2">Class Teacher</p>
                    <?php if (!empty($classTeacher) && !empty($classTeacher['first_name'])): ?>
                    <p class="text-gray-700 mb-1"><?php echo htmlspecialchars($classTeacher['first_name'] . ' ' . $classTeacher['last_name']); ?></p>
                    <?php else: ?>
                    <p class="text-gray-400 mb-1 italic">Not Assigned</p>
                    <?php endif; ?>
                    <div class="border-t pt-2 mt-8">
                        <p class="text-gray-600">Signature</p>
                    </div>
                </div>
                <div>
                    <p class="font-semibold mb-2">Head Teacher</p>
                    <?php if (!empty($headTeacher) && !empty($headTeacher['first_name'])): ?>
                    <p class="text-gray-700 mb-1"><?php echo htmlspecialchars($headTeacher['first_name'] . ' ' . $headTeacher['last_name']); ?></p>
                    <?php else: ?>
                    <p class="text-gray-400 mb-1 italic">Not Assigned</p>
                    <?php endif; ?>
                    <div class="border-t pt-2 mt-8">
                        <p class="text-gray-600">Signature</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function sendReportCardSms() {
    const btn = document.getElementById('btnSendSms');
    const statusDiv = document.getElementById('statusMessage');
    
    if (btn.disabled) return;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
    statusDiv.className = 'mb-4 hidden';
    
    const formData = new FormData();
    formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');
    formData.append('examination_id', <?php echo $examination['id']; ?>);
    formData.append('student_id', <?php echo $student['id']; ?>);
    
    fetch('<?php echo BASE_URL; ?>/examinations/sendReportCardSms', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sms mr-2"></i>Send via SMS';
        
        if (data.success) {
            statusDiv.className = 'mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded';
            statusDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + (data.message || 'Report card sent successfully via SMS');
        } else {
            statusDiv.className = 'mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            statusDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>' + (data.message || 'Failed to send SMS');
        }
        statusDiv.classList.remove('hidden');
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            statusDiv.classList.add('hidden');
        }, 5000);
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sms mr-2"></i>Send via SMS';
        statusDiv.className = 'mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
        statusDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>Error: ' + error.message;
        statusDiv.classList.remove('hidden');
    });
}

function sendReportCardEmail() {
    const btn = document.getElementById('btnSendEmail');
    const statusDiv = document.getElementById('statusMessage');
    
    if (btn.disabled) return;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
    statusDiv.className = 'mb-4 hidden';
    
    const formData = new FormData();
    formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');
    formData.append('examination_id', <?php echo $examination['id']; ?>);
    formData.append('student_id', <?php echo $student['id']; ?>);
    
    fetch('<?php echo BASE_URL; ?>/examinations/sendReportCardEmail', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-envelope mr-2"></i>Send via Email (PDF)';
        
        if (data.success) {
            statusDiv.className = 'mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded';
            statusDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + (data.message || 'Report card sent successfully via email');
        } else {
            statusDiv.className = 'mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            statusDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>' + (data.message || 'Failed to send email');
        }
        statusDiv.classList.remove('hidden');
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            statusDiv.classList.add('hidden');
        }, 5000);
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-envelope mr-2"></i>Send via Email (PDF)';
        statusDiv.className = 'mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
        statusDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>Error: ' + error.message;
        statusDiv.classList.remove('hidden');
    });
}
</script>

<style>
@media print {
    .container {
        max-width: 100%;
    }
    button {
        display: none;
    }
    #statusMessage {
        display: none;
    }
    .print\:shadow-none {
        box-shadow: none;
    }
}
</style>

