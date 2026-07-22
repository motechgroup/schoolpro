<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">CBC Assessments</h1>
        <div class="flex space-x-2">
            <select id="termFilter" class="border rounded px-3 py-2">
                <option value="1" <?php echo ($currentTerm == 1) ? 'selected' : ''; ?>>Term 1</option>
                <option value="2" <?php echo ($currentTerm == 2) ? 'selected' : ''; ?>>Term 2</option>
                <option value="3" <?php echo ($currentTerm == 3) ? 'selected' : ''; ?>>Term 3</option>
            </select>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <p class="text-gray-600">Academic Year: <strong><?php echo htmlspecialchars($currentYear); ?></strong></p>
        <p class="text-gray-600">Term: <strong><?php echo $currentTerm; ?></strong></p>
    </div>
    
    <!-- Recent Assessments -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="text-xl font-bold">Recent Assessments</h2>
        </div>
        <?php if (empty($recentAssessments)): ?>
        <div class="p-6 text-center text-gray-500">
            No assessments found for Term <?php echo $currentTerm; ?>.
        </div>
        <?php else: ?>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Learning Area</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($recentAssessments as $assessment): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo formatDate($assessment['assessed_date']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo htmlspecialchars(($assessment['student_first_name'] ?? '') . ' ' . ($assessment['student_last_name'] ?? '')); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($assessment['learning_area_name'] ?? 'N/A'); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">
                            <?php echo ucfirst($assessment['level'] ?? 'N/A'); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo $assessment['score'] ?? 'N/A'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('termFilter').addEventListener('change', function() {
    const term = this.value;
    window.location.href = '<?php echo BASE_URL; ?>/assessments?term=' + term;
});
</script>

