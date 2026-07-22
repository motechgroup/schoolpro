<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <!-- Restricted Access Message -->
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="mb-6">
                <i class="fas fa-lock text-6xl text-red-500 mb-4"></i>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Report Card Access Restricted</h1>
                <p class="text-lg text-gray-600">You cannot view the report card due to outstanding fee balance</p>
            </div>
            
            <!-- Student Information -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
                <h2 class="text-xl font-semibold mb-4">Student Information</h2>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-600">Name:</span>
                        <span class="ml-2"><?php echo htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']); ?></span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Admission No:</span>
                        <span class="ml-2"><?php echo htmlspecialchars($student['admission_number']); ?></span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Class:</span>
                        <span class="ml-2"><?php echo htmlspecialchars($student['class_name']); ?></span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Examination:</span>
                        <span class="ml-2"><?php echo htmlspecialchars($examination['name']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Outstanding Balance -->
            <div class="bg-red-50 border-2 border-red-200 rounded-lg p-6 mb-6">
                <div class="flex items-center justify-center mb-4">
                    <i class="fas fa-exclamation-triangle text-3xl text-red-600 mr-3"></i>
                    <h3 class="text-2xl font-bold text-red-800">Outstanding Fee Balance</h3>
                </div>
                <p class="text-3xl font-bold text-red-600 mb-2">
                    KES <?php echo number_format($totalBalance, 2); ?>
                </p>
                <p class="text-gray-700">Please clear all outstanding fees to access the report card.</p>
            </div>
            
            <!-- Fee Details -->
            <?php if (!empty($invoices)): ?>
            <div class="bg-white border rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Outstanding Invoices</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice No.</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($invoices as $invoice): 
                                if ($invoice['status'] == 'pending' || $invoice['status'] == 'partial'):
                                    $balance = floatval($invoice['balance'] ?? 0);
                                    if ($balance > 0):
                            ?>
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    <?php echo htmlspecialchars($invoice['invoice_number']); ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">Term <?php echo $invoice['term']; ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    KES <?php echo number_format($invoice['total_amount'], 2); ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    KES <?php echo number_format($invoice['paid_amount'], 2); ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-red-600">
                                    KES <?php echo number_format($balance, 2); ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded <?php 
                                        echo $invoice['status'] == 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800';
                                    ?>">
                                        <?php echo ucfirst($invoice['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php 
                                    endif;
                                endif;
                            endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Contact Information -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-2 text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>What to do next?
                </h3>
                <ul class="text-left text-gray-700 space-y-2">
                    <li><i class="fas fa-check-circle text-blue-600 mr-2"></i>Visit the school office to clear outstanding fees</li>
                    <li><i class="fas fa-check-circle text-blue-600 mr-2"></i>Make payment via M-Pesa or other accepted payment methods</li>
                    <li><i class="fas fa-check-circle text-blue-600 mr-2"></i>Once fees are cleared, you can access the report card</li>
                </ul>
            </div>
            
            <!-- Back Button -->
            <div class="mt-6">
                <a href="<?php echo BASE_URL; ?>/examinations/show/<?php echo $examination['id']; ?>" 
                   class="inline-block bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Examination
                </a>
            </div>
        </div>
    </div>
</div>

