<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Create Academic Year</h1>
            <a href="<?php echo BASE_URL; ?>/academicyears" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
        
        <form id="academicYearForm" class="bg-white rounded-lg shadow p-6">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year Name *</label>
                    <select name="name" required class="w-full border rounded px-3 py-2 bg-white">
                        <?php
                        $defaultYear = date('Y');
                        for ($y = date('Y') - 2; $y <= date('Y') + 5; $y++):
                            $sYear = (string)$y;
                            $spYear = $y . '/' . ($y + 1);
                        ?>
                            <option value="<?php echo $sYear; ?>" <?php echo ($defaultYear == $sYear) ? 'selected' : ''; ?>>
                                <?php echo $sYear; ?>
                            </option>
                            <option value="<?php echo $spYear; ?>">
                                <?php echo $spYear; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                        <input type="date" name="start_date" required 
                               class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                        <input type="date" name="end_date" required 
                               class="w-full border rounded px-3 py-2">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                    <select name="status" required class="w-full border rounded px-3 py-2">
                        <option value="upcoming">Upcoming</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="is_current" id="is_current" value="1" class="mr-2">
                    <label for="is_current" class="text-sm text-gray-700">
                        Set as current academic year
                    </label>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/academicyears" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Create Academic Year
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('academicYearForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/academicyears/store', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            alert('Error: ' + data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});
</script>

