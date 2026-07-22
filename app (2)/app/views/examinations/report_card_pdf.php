<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Report Card - <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
        }
        .student-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .student-info-row {
            display: table-row;
        }
        .student-info-cell {
            display: table-cell;
            padding: 5px 10px;
            width: 50%;
        }
        .student-info-label {
            font-weight: bold;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        td {
            text-align: center;
        }
        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .grading-scale {
            margin-top: 20px;
            font-size: 11px;
        }
        .footer {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .footer-section {
            display: table-cell;
            width: 50%;
            padding: 20px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="font-size: 28px; margin-bottom: 10px;"><?php echo htmlspecialchars($schoolName ?: APP_NAME); ?></h1>
        <?php if (!empty($schoolSettings['school_address'])): ?>
        <p style="font-size: 11px; margin: 2px 0;"><?php echo htmlspecialchars($schoolSettings['school_address']); ?></p>
        <?php endif; ?>
        <?php if (!empty($schoolSettings['school_phone'])): ?>
        <p style="font-size: 11px; margin: 2px 0;">Tel: <?php echo htmlspecialchars($schoolSettings['school_phone']); ?></p>
        <?php endif; ?>
        <?php if (!empty($schoolSettings['school_email'])): ?>
        <p style="font-size: 11px; margin: 2px 0;">Email: <?php echo htmlspecialchars($schoolSettings['school_email']); ?></p>
        <?php endif; ?>
        <h2 style="font-size: 20px; margin-top: 15px; margin-bottom: 5px;">REPORT CARD</h2>
        <p style="font-size: 14px; font-weight: bold;"><?php echo htmlspecialchars($examination['name']); ?></p>
        <p style="font-size: 12px;">Term <?php echo $examination['term']; ?> - <?php echo htmlspecialchars($examination['academic_year']); ?></p>
    </div>
    
    <div class="student-info">
        <div class="student-info-row">
            <div class="student-info-cell">
                <span class="student-info-label">Student Name:</span>
                <?php echo htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']); ?>
            </div>
            <div class="student-info-cell">
                <span class="student-info-label">Examination:</span>
                <?php echo htmlspecialchars($examination['name']); ?>
            </div>
        </div>
        <div class="student-info-row">
            <div class="student-info-cell">
                <span class="student-info-label">Admission Number:</span>
                <?php echo htmlspecialchars($student['admission_number']); ?>
            </div>
            <div class="student-info-cell">
                <span class="student-info-label">Term:</span>
                Term <?php echo $examination['term']; ?>
            </div>
        </div>
        <div class="student-info-row">
            <div class="student-info-cell">
                <span class="student-info-label">Class:</span>
                <?php echo htmlspecialchars($student['class_name']); ?>
            </div>
            <div class="student-info-cell">
                <span class="student-info-label">Academic Year:</span>
                <?php echo htmlspecialchars($examination['academic_year']); ?>
            </div>
        </div>
        <div class="student-info-row">
            <div class="student-info-cell">
                <span class="student-info-label">Grade:</span>
                <?php echo htmlspecialchars($student['grade_display_name']); ?>
            </div>
            <?php if ($examination['exam_date']): ?>
            <div class="student-info-cell">
                <span class="student-info-label">Exam Date:</span>
                <?php echo date('d M Y', strtotime($examination['exam_date'])); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Subject</th>
                <th>Marks Obtained</th>
                <th>Max Marks</th>
                <th>Percentage</th>
                <th>Grade</th>
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
                <td style="text-align: left;"><?php echo htmlspecialchars($subject['learning_area_name']); ?></td>
                <td><?php echo $mark ? number_format($marksObtained, 2) : '-'; ?></td>
                <td><?php echo number_format($maxMarks, 2); ?></td>
                <td><?php echo $mark ? $percentage . '%' : '-'; ?></td>
                <td><?php echo $grade; ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td style="text-align: left; font-weight: bold;">TOTAL</td>
                <td><?php echo number_format($totalMarks, 2); ?></td>
                <td><?php echo number_format($totalMaxMarks, 2); ?></td>
                <td><?php echo $overallPercentage; ?>%</td>
                <td><?php echo $overallGrade; ?></td>
            </tr>
        </tbody>
    </table>
    
    <div class="grading-scale">
        <p><strong>Grading Scale:</strong></p>
        <p>A: 80% and above | B: 70% - 79% | C: 60% - 69% | D: 50% - 59% | E: 40% - 49% | F: Below 40%</p>
    </div>
    
    <div class="footer">
        <div class="footer-section">
            <p><strong>Class Teacher</strong></p>
            <?php if ($classTeacher && !empty($classTeacher['first_name'])): ?>
            <p style="margin: 5px 0;"><?php echo htmlspecialchars($classTeacher['first_name'] . ' ' . $classTeacher['last_name']); ?></p>
            <?php else: ?>
            <p style="margin: 5px 0; color: #999;">Not Assigned</p>
            <?php endif; ?>
            <div class="signature-line">
                <p>Signature</p>
            </div>
        </div>
        <div class="footer-section">
            <p><strong>Head Teacher</strong></p>
            <?php if ($headTeacher && !empty($headTeacher['first_name'])): ?>
            <p style="margin: 5px 0;"><?php echo htmlspecialchars($headTeacher['first_name'] . ' ' . $headTeacher['last_name']); ?></p>
            <?php else: ?>
            <p style="margin: 5px 0; color: #999;">Not Assigned</p>
            <?php endif; ?>
            <div class="signature-line">
                <p>Signature</p>
            </div>
        </div>
    </div>
</body>
</html>

