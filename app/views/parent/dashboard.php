<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Parent Portal</h1>
    
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">Welcome, <?php echo htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name']); ?>!</h2>
        <p class="text-gray-600">Phone: <?php echo htmlspecialchars($parent['phone']); ?></p>
        <?php if (!empty($parent['email'])): ?>
        <p class="text-gray-600">Email: <?php echo htmlspecialchars($parent['email']); ?></p>
        <?php endif; ?>
    </div>
    
    <h2 class="text-2xl font-bold mb-4">Your Children</h2>
    
    <?php if (empty($children)): ?>
    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
        No children registered under your account.
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($children as $child): ?>
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <h3 class="text-xl font-bold mb-2">
                <?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?>
            </h3>
            <p class="text-gray-600 mb-2">
                <strong>Admission:</strong> <?php echo htmlspecialchars($child['admission_number']); ?>
            </p>
            <p class="text-gray-600 mb-2">
                <strong>Class:</strong> <?php echo htmlspecialchars($child['class_name'] ?? 'N/A'); ?>
            </p>
            <p class="text-gray-600 mb-4">
                <strong>Grade:</strong> <?php echo htmlspecialchars($child['grade_display_name'] ?? 'N/A'); ?>
            </p>
            <a href="<?php echo BASE_URL; ?>/parent/child/<?php echo $child['id']; ?>" 
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 inline-block">
                View Details
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

