<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Attendance Report</h1>
        <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-print mr-2"></i>Print
        </button>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                <select name="class_id" class="w-full border rounded px-3 py-2">
                    <option value="">Select Class</option>
                    <?php foreach ($classes as $class): ?>
                    <option value="<?php echo $class['id']; ?>" <?php echo ($filters['class_id'] == $class['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($class['grade_display_name'] . ' - ' . $class['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($filters['start_date']); ?>" 
                       class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($filters['end_date']); ?>" 
                       class="w-full border rounded px-3 py-2">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-filter mr-2"></i>Generate Report
                </button>
            </div>
        </form>
    </div>
    
    <?php if (!empty($attendanceData)): ?>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="text-xl font-bold">Attendance Summary Report</h2>
            <p class="text-sm text-gray-600">
                Period: <?php echo formatDate($filters['start_date']); ?> to <?php echo formatDate($filters['end_date']); ?>
            </p>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admission No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student Name</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Present</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Absent</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Late</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Excused</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total Days</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Attendance %</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($attendanceData as $item): 
                    $student = $item['student'];
                    $summary = $item['summary'];
                    $totalDays = $summary['total_days'] ?? 0;
                    $presentDays = $summary['present_days'] ?? 0;
                    $attendancePercent = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0;
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($student['admission_number']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-green-600 font-semibold">
                        <?php echo $summary['present_days'] ?? 0; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-red-600 font-semibold">
                        <?php echo $summary['absent_days'] ?? 0; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-yellow-600 font-semibold">
                        <?php echo $summary['late_days'] ?? 0; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-blue-600 font-semibold">
                        <?php echo $summary['excused_days'] ?? 0; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold">
                        <?php echo $totalDays; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                        <span class="px-2 py-1 rounded <?php 
                            echo $attendancePercent >= 80 ? 'bg-green-100 text-green-800' : 
                                ($attendancePercent >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                        ?>">
                            <?php echo $attendancePercent; ?>%
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="p-4 border-t bg-gray-50">
            <p class="text-sm text-gray-600">Total Students: <strong><?php echo count($attendanceData); ?></strong></p>
        </div>
    </div>
    <?php elseif (!empty($filters['class_id'])): ?>
    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
        No attendance data found for the selected period.
    </div>
    <?php else: ?>
    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
        Please select a class and date range to generate the attendance report.
    </div>
    <?php endif; ?>
</div>

<style>
@media print {
    nav, .no-print { display: none; }
    body { margin: 0; }
}
</style>

