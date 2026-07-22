<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($announcement['title']); ?></h1>
        <div class="flex space-x-2">
            <?php if (Auth::hasAnyRole(['super_admin', 'school_admin', 'head_teacher'])): ?>
            <a href="<?php echo BASE_URL; ?>/announcements/edit/<?php echo $announcement['id']; ?>" 
               class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>/announcements" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4 pb-4 border-b">
            <div class="flex items-center space-x-4 text-sm text-gray-600">
                <span>
                    <i class="fas fa-user mr-1"></i>
                    <?php echo htmlspecialchars(($announcement['created_by_first_name'] ?? '') . ' ' . ($announcement['created_by_last_name'] ?? '')); ?>
                </span>
                <span>
                    <i class="fas fa-calendar mr-1"></i>
                    <?php echo formatDateTime($announcement['published_at'] ?? $announcement['created_at']); ?>
                </span>
                <span class="px-2 py-1 text-xs rounded <?php 
                    echo $announcement['priority'] == 'urgent' ? 'bg-red-100 text-red-800' : 
                        ($announcement['priority'] == 'high' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800');
                ?>">
                    <?php echo ucfirst($announcement['priority']); ?>
                </span>
                <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">
                    <?php echo ucfirst($announcement['target_audience']); ?>
                </span>
            </div>
        </div>
        
        <div class="prose max-w-none">
            <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
        </div>
    </div>
</div>

