<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></h1>
        <div class="flex space-x-2">
            <a href="<?php echo BASE_URL; ?>/teachers/edit/<?php echo $teacher['id']; ?>" 
               class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <a href="<?php echo BASE_URL; ?>/teachers" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Teacher Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-start space-x-6 mb-4">
                <!-- Teacher Photo -->
                <div class="flex-shrink-0">
                    <?php 
                    $photoUrl = !empty($teacher['photo']) ? getImageUrl($teacher['photo']) : null;
                    if ($photoUrl): 
                    ?>
                    <img src="<?php echo htmlspecialchars($photoUrl); ?>" 
                         alt="Teacher Photo" 
                         class="w-32 h-32 object-cover rounded-lg border-4 border-blue-200 shadow-lg"
                         onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="w-32 h-32 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg border-4 border-blue-200 shadow-lg items-center justify-center hidden">
                        <i class="fas fa-user text-white text-5xl"></i>
                    </div>
                    <?php else: ?>
                    <div class="w-32 h-32 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg border-4 border-blue-200 shadow-lg flex items-center justify-center">
                        <i class="fas fa-user text-white text-5xl"></i>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Teacher Name -->
                <div class="flex-1">
                    <h2 class="text-2xl font-bold mb-2">
                        <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                    </h2>
                    <?php if (!empty($teacher['tsc_number'])): ?>
                    <p class="text-gray-600 mb-1">
                        <span class="font-semibold">TSC No:</span> <?php echo htmlspecialchars($teacher['tsc_number']); ?>
                    </p>
                    <?php endif; ?>
                    <p class="text-gray-600">
                        <span class="font-semibold">Email:</span> <?php echo htmlspecialchars($teacher['email'] ?? 'N/A'); ?>
                    </p>
                </div>
            </div>
            
            <h3 class="text-xl font-bold mb-4 mt-6">Details</h3>
            <div class="space-y-3">
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Phone</label>
                    <p class="text-lg"><?php echo htmlspecialchars($teacher['phone'] ?? 'N/A'); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Qualification</label>
                    <p class="text-lg"><?php echo htmlspecialchars($teacher['qualification'] ?? 'Not provided'); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Specialization</label>
                    <p class="text-lg"><?php echo htmlspecialchars($teacher['specialization'] ?? 'Not provided'); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Employment Date</label>
                    <p class="text-lg"><?php echo formatDate($teacher['employment_date'] ?? ''); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Status</label>
                    <span class="px-2 py-1 text-xs rounded <?php 
                        echo $teacher['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                    ?>">
                        <?php echo ucfirst($teacher['status']); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Assigned Classes -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">Assigned Classes</h2>
            </div>
            
            <?php if (empty($assignedClasses)): ?>
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-school text-4xl mb-4 text-gray-300"></i>
                <p>No classes assigned</p>
            </div>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($assignedClasses as $class): ?>
                <div class="border rounded p-3 hover:bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-semibold"><?php echo htmlspecialchars($class['grade_display_name'] . ' - ' . $class['name']); ?></p>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($class['academic_year']); ?></p>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/classes/show/<?php echo $class['id']; ?>" 
                           class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

