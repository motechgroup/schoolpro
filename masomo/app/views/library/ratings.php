<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Student Library Ratings</h1>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>" 
                       placeholder="Student name or admission number" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Borrowing Level</label>
                <select name="borrowing_level" class="w-full border rounded px-3 py-2">
                    <option value="">All Levels</option>
                    <option value="excellent" <?php echo ($filters['borrowing_level'] == 'excellent') ? 'selected' : ''; ?>>Excellent</option>
                    <option value="good" <?php echo ($filters['borrowing_level'] == 'good') ? 'selected' : ''; ?>>Good</option>
                    <option value="fair" <?php echo ($filters['borrowing_level'] == 'fair') ? 'selected' : ''; ?>>Fair</option>
                    <option value="poor" <?php echo ($filters['borrowing_level'] == 'poor') ? 'selected' : ''; ?>>Poor</option>
                    <option value="restricted" <?php echo ($filters['borrowing_level'] == 'restricted') ? 'selected' : ''; ?>>Restricted</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
    
    <!-- Ratings Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Points</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Borrowing Level</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Max Borrows</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statistics</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($ratings)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No ratings found</td>
                </tr>
                <?php else: ?>
                <?php foreach ($ratings as $rating): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-medium"><?php echo htmlspecialchars($rating['first_name'] . ' ' . $rating['last_name']); ?></div>
                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($rating['admission_number']); ?></div>
                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($rating['class_name'] ?? 'N/A'); ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <span class="text-2xl font-bold <?php 
                                echo $rating['rating'] >= 4.5 ? 'text-green-600' : 
                                    ($rating['rating'] >= 4.0 ? 'text-blue-600' : 
                                    ($rating['rating'] >= 3.0 ? 'text-yellow-600' : 
                                    ($rating['rating'] >= 2.0 ? 'text-orange-600' : 'text-red-600')));
                            ?>">
                                <?php echo number_format($rating['rating'], 2); ?>
                            </span>
                            <span class="text-gray-400 ml-1">/ 5.00</span>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            <?php 
                            $stars = round($rating['rating']);
                            for ($i = 1; $i <= 5; $i++): 
                                if ($i <= $stars):
                            ?>
                            <i class="fas fa-star text-yellow-400"></i>
                            <?php else: ?>
                            <i class="far fa-star text-gray-300"></i>
                            <?php endif; endfor; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-lg font-semibold <?php 
                            echo $rating['total_points'] >= 200 ? 'text-green-600' : 
                                ($rating['total_points'] >= 150 ? 'text-blue-600' : 
                                ($rating['total_points'] >= 100 ? 'text-yellow-600' : 
                                ($rating['total_points'] >= 50 ? 'text-orange-600' : 'text-red-600')));
                        ?>">
                            <?php echo number_format($rating['total_points']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded font-semibold <?php 
                            echo $rating['borrowing_level'] == 'excellent' ? 'bg-green-100 text-green-800' : 
                                ($rating['borrowing_level'] == 'good' ? 'bg-blue-100 text-blue-800' : 
                                ($rating['borrowing_level'] == 'fair' ? 'bg-yellow-100 text-yellow-800' : 
                                ($rating['borrowing_level'] == 'poor' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800')));
                        ?>">
                            <?php echo ucfirst($rating['borrowing_level']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="text-lg font-semibold"><?php echo $rating['max_borrows']; ?></span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <div class="text-xs">
                            <div>Borrows: <strong><?php echo $rating['total_borrows']; ?></strong></div>
                            <div>Returns: <strong><?php echo $rating['total_returns']; ?></strong></div>
                            <div class="text-green-600">On-time: <strong><?php echo $rating['on_time_returns']; ?></strong></div>
                            <div class="text-red-600">Late: <strong><?php echo $rating['late_returns']; ?></strong></div>
                            <?php if ($rating['damaged_books'] > 0): ?>
                            <div class="text-orange-600">Damaged: <strong><?php echo $rating['damaged_books']; ?></strong></div>
                            <?php endif; ?>
                            <?php if ($rating['lost_books'] > 0): ?>
                            <div class="text-red-600">Lost: <strong><?php echo $rating['lost_books']; ?></strong></div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="<?php echo BASE_URL; ?>/library/studentRating/<?php echo $rating['student_id']; ?>" class="text-blue-600 hover:text-blue-900" title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

