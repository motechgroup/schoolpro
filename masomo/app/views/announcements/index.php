<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Announcements</h1>
        <?php if ($canManage): ?>
        <a href="<?php echo BASE_URL; ?>/announcements/create" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Create Announcement
        </a>
        <?php endif; ?>
    </div>
    
    <?php if (empty($announcements)): ?>
    <div class="bg-white rounded-lg shadow p-6 text-center">
        <p class="text-gray-600">No announcements found.</p>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($announcements as $announcement): ?>
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <div class="flex justify-between items-start mb-2">
                <div class="flex-1">
                    <h2 class="text-xl font-bold mb-2">
                        <a href="<?php echo BASE_URL; ?>/announcements/show/<?php echo $announcement['id']; ?>" 
                           class="text-blue-600 hover:text-blue-800">
                            <?php echo htmlspecialchars($announcement['title']); ?>
                        </a>
                    </h2>
                    <p class="text-gray-600 text-sm mb-2">
                        <?php echo substr(strip_tags($announcement['content']), 0, 150); ?>
                        <?php echo strlen($announcement['content']) > 150 ? '...' : ''; ?>
                    </p>
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
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
                    </div>
                </div>
                <?php if ($canManage): ?>
                <div class="ml-4 flex space-x-2">
                    <a href="<?php echo BASE_URL; ?>/announcements/edit/<?php echo $announcement['id']; ?>" 
                       class="text-green-600 hover:text-green-800">
                        <i class="fas fa-edit"></i>
                    </a>
                    <?php if (Auth::hasAnyRole(['super_admin', 'school_admin'])): ?>
                    <a href="#" onclick="deleteAnnouncement(<?php echo $announcement['id']; ?>)" 
                       class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function deleteAnnouncement(id) {
    if (!confirm('Are you sure you want to delete this announcement?')) {
        return;
    }
    
    fetch('<?php echo BASE_URL; ?>/announcements/delete/' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to delete announcement: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
    });
}
</script>

