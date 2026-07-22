<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h1>
        <a href="<?php echo BASE_URL; ?>/parent/dashboard" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>
    
    <!-- Student Info -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">Student Information</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="text-sm font-medium text-gray-500">Admission Number</label>
                <p class="text-lg"><?php echo htmlspecialchars($student['admission_number']); ?></p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Class</label>
                <p class="text-lg"><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Grade</label>
                <p class="text-lg"><?php echo htmlspecialchars($student['grade_display_name'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Status</label>
                <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">
                    <?php echo ucfirst($student['status']); ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Attendance Summary -->
    <?php if ($attendanceSummary): ?>
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">Attendance Summary (<?php echo date('F Y'); ?>)</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
                <p class="text-3xl font-bold text-green-600"><?php echo $attendanceSummary['present_days'] ?? 0; ?></p>
                <p class="text-sm text-gray-600">Present</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-red-600"><?php echo $attendanceSummary['absent_days'] ?? 0; ?></p>
                <p class="text-sm text-gray-600">Absent</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-yellow-600"><?php echo $attendanceSummary['late_days'] ?? 0; ?></p>
                <p class="text-sm text-gray-600">Late</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-blue-600"><?php echo $attendanceSummary['total_days'] ?? 0; ?></p>
                <p class="text-sm text-gray-600">Total Days</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Recent Assessments -->
    <?php if (!empty($assessments)): ?>
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">Recent Assessments</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Learning Area</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Strand</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach (array_slice($assessments, 0, 10) as $assessment): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo formatDate($assessment['assessed_date']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($assessment['learning_area_name'] ?? 'N/A'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($assessment['strand_name'] ?? 'N/A'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">
                                <?php echo ucfirst($assessment['level'] ?? 'N/A'); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Fee Invoices -->
    <?php if (!empty($invoices)): ?>
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">Fee Invoices</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($invoices as $invoice): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">Term <?php echo $invoice['term']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo formatCurrency($invoice['total_amount']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo formatCurrency($invoice['paid_amount']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold"><?php echo formatCurrency($invoice['balance']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded <?php 
                                echo $invoice['status'] == 'paid' ? 'bg-green-100 text-green-800' : 
                                    ($invoice['status'] == 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                            ?>">
                                <?php echo ucfirst($invoice['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

