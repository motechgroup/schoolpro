<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Academic Years & Terms Management</h1>
        <a href="<?php echo BASE_URL; ?>/academicyears/create" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Create Academic Year
        </a>
    </div>
    
    <div class="mb-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
        <p class="text-sm text-blue-800">
            <i class="fas fa-info-circle mr-2"></i>
            <strong>Note:</strong> The system uses term dates to determine when school is open or closed. 
            Only terms with active status and current dates will be considered "open".
        </p>
    </div>
    
    <?php if (empty($academicYears)): ?>
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <i class="fas fa-calendar-times text-gray-400 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Academic Years Found</h3>
        <p class="text-gray-600 mb-4">Create your first academic year to get started.</p>
        <a href="<?php echo BASE_URL; ?>/academicyears/create" class="inline-block bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Create Academic Year
        </a>
    </div>
    <?php else: ?>
    <div class="space-y-6">
        <?php foreach ($academicYears as $year): ?>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($year['name']); ?></h2>
                        <p class="text-blue-100 text-sm mt-1">
                            <i class="fas fa-calendar mr-1"></i>
                            <?php echo formatDate($year['start_date'], 'd M Y'); ?> - 
                            <?php echo formatDate($year['end_date'], 'd M Y'); ?>
                        </p>
                    </div>
                    <div class="text-right">
                        <?php if ($year['is_current']): ?>
                        <span class="inline-block bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold mb-2">
                            <i class="fas fa-check-circle mr-1"></i>Current
                        </span>
                        <?php endif; ?>
                        <span class="inline-block bg-white/20 text-white px-3 py-1 rounded-full text-sm font-semibold">
                            <?php echo ucfirst($year['status']); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Terms</h3>
                    <a href="<?php echo BASE_URL; ?>/academicyears/edit/<?php echo $year['id']; ?>" 
                       class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </a>
                </div>
                
                <?php if (empty($year['terms'])): ?>
                <p class="text-gray-500 text-sm">No terms defined for this academic year.</p>
                <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php foreach ($year['terms'] as $term): ?>
                    <div class="border rounded-lg p-4 <?php echo $term['is_current'] ? 'bg-green-50 border-green-300' : ''; ?>">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($term['name']); ?></h4>
                            <?php if ($term['is_current']): ?>
                            <span class="bg-green-500 text-white text-xs px-2 py-1 rounded">
                                <i class="fas fa-check-circle mr-1"></i>Current
                            </span>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            <?php echo formatDate($term['start_date'], 'd M Y'); ?> - 
                            <?php echo formatDate($term['end_date'], 'd M Y'); ?>
                        </p>
                        <p class="text-xs text-gray-500">
                            Status: <span class="font-semibold"><?php echo ucfirst($term['status']); ?></span>
                        </p>
                        <?php 
                        $today = date('Y-m-d');
                        $termStart = $term['start_date'];
                        $termEnd = $term['end_date'];
                        if ($today >= $termStart && $today <= $termEnd && $term['status'] == 'active'): 
                        ?>
                        <p class="text-xs text-green-600 mt-2 font-semibold">
                            <i class="fas fa-school mr-1"></i>School is OPEN
                        </p>
                        <?php elseif ($today < $termStart): ?>
                        <p class="text-xs text-blue-600 mt-2">
                            <i class="fas fa-clock mr-1"></i>Upcoming
                        </p>
                        <?php else: ?>
                        <p class="text-xs text-gray-500 mt-2">
                            <i class="fas fa-lock mr-1"></i>Closed
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

