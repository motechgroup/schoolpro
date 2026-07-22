<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Communication</h1>
        <div class="space-x-2">
            <a href="<?php echo BASE_URL; ?>/communication/settings" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                <i class="fas fa-cog mr-2"></i>SMS Settings
            </a>
            <a href="<?php echo BASE_URL; ?>/parents" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-users mr-2"></i>Parents
            </a>
        </div>
    </div>
    
    <!-- SMS Balance -->
    <?php if (isset($sms_balance) && $sms_balance['success']): ?>
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
        <div class="flex items-center">
            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
            <span class="text-blue-800">
                SMS Balance: <strong><?php echo htmlspecialchars($sms_balance['balance'] ?? 'Unknown'); ?></strong>
            </span>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button onclick="switchTab('sms')" id="tab-sms" class="tab-button active py-4 px-6 border-b-2 border-blue-500 text-blue-600 font-medium">
                    <i class="fas fa-sms mr-2"></i>SMS
                </button>
                <button onclick="switchTab('whatsapp')" id="tab-whatsapp" class="tab-button py-4 px-6 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fab fa-whatsapp mr-2"></i>WhatsApp
                </button>
                <button onclick="switchTab('email')" id="tab-email" class="tab-button py-4 px-6 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-envelope mr-2"></i>Email
                </button>
            </nav>
        </div>
    </div>
    
    <!-- SMS Tab Content -->
    <div id="sms-tab" class="tab-content">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- SMS Form -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold mb-4">Send SMS</h2>
                
                <form id="smsForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Recipient Type</label>
                        <select name="recipient_type" id="smsRecipientType" class="w-full border rounded px-3 py-2">
                            <option value="selected">Selected Parents</option>
                            <option value="class">All Parents in Class</option>
                            <option value="all">All Parents</option>
                        </select>
                    </div>
                    
                    <div class="mb-4" id="smsClassSelection" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Class</label>
                        <select name="class_id" class="w-full border rounded px-3 py-2">
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>">
                                <?php echo htmlspecialchars($class['grade_display_name'] . ' - ' . $class['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4" id="smsParentSelection">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Parents</label>
                        <div class="border rounded p-3 max-h-64 overflow-y-auto">
                            <?php 
                            $selectedParentId = $_GET['parent_id'] ?? null;
                            foreach ($parents as $parent): 
                            ?>
                            <label class="flex items-center mb-2">
                                <input type="checkbox" name="parent_ids[]" value="<?php echo $parent['id']; ?>" 
                                       class="mr-2 parent-checkbox"
                                       <?php echo ($selectedParentId && $parent['id'] == $selectedParentId) ? 'checked' : ''; ?>>
                                <span class="text-sm">
                                    <?php echo displayText($parent['first_name'] . ' ' . $parent['last_name']); ?>
                                    <span class="text-gray-500">(<?php echo htmlspecialchars($parent['phone']); ?>)</span>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-2 flex space-x-2">
                            <button type="button" onclick="selectAll('sms')" class="text-sm text-blue-600 hover:text-blue-800">Select All</button>
                            <button type="button" onclick="selectNone('sms')" class="text-sm text-blue-600 hover:text-blue-800">Deselect All</button>
                            <button type="button" onclick="selectWithBalance('sms')" class="text-sm text-red-600 hover:text-red-800">Select with Balance</button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                        <textarea name="message" id="smsMessage" rows="6" 
                                  class="w-full border rounded px-3 py-2" 
                                  placeholder="Type your message here... (160 characters = 1 SMS)"></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            <span id="smsCharCount">0</span> characters | 
                            <span id="smsCount">0</span> SMS
                        </p>
                        <div class="mt-2 p-3 bg-blue-50 rounded border border-blue-200">
                            <p class="text-xs font-semibold text-blue-800 mb-2">Available Placeholders:</p>
                            <div class="grid grid-cols-2 gap-2 text-xs text-blue-700">
                                <div><code>{parent_name}</code> - Parent's full name</div>
                                <div><code>{student_name}</code> - First child's name</div>
                                <div><code>{students_names}</code> - All children's names</div>
                                <div><code>{admission_number}</code> - First child's admission number</div>
                                <div><code>{admission_numbers}</code> - All admission numbers</div>
                                <div><code>{class_name}</code> - First child's class</div>
                                <div><code>{class_names}</code> - All classes</div>
                                <div><code>{grade}</code> - First child's grade</div>
                                <div><code>{fee_balance}</code> - Total outstanding balance</div>
                                <div><code>{children_count}</code> - Number of children</div>
                                <div><code>{school_name}</code> - School name</div>
                                <div><code>{current_date}</code> - Today's date</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="has_balance" value="1" class="mr-2">
                            <span class="text-sm text-gray-700">Only send to parents with outstanding balance</span>
                        </label>
                    </div>
                    
                    <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        <i class="fas fa-paper-plane mr-2"></i>Send SMS
                    </button>
                </form>
                
                <div id="smsResult" class="mt-4 hidden"></div>
            </div>
            
            <!-- Quick Templates -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold mb-4">Quick Templates</h2>
                
                <div class="space-y-2">
                    <button onclick="useSmsTemplate('fee_reminder')" class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 rounded text-sm">
                        <strong>Fee Reminder</strong>
                        <p class="text-gray-600 text-xs mt-1">Remind parents about outstanding fees</p>
                    </button>
                    
                    <button onclick="useSmsTemplate('attendance')" class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 rounded text-sm">
                        <strong>Attendance Alert</strong>
                        <p class="text-gray-600 text-xs mt-1">Notify about student absence</p>
                    </button>
                    
                    <button onclick="useSmsTemplate('announcement')" class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 rounded text-sm">
                        <strong>General Announcement</strong>
                        <p class="text-gray-600 text-xs mt-1">Send school announcements</p>
                    </button>
                    
                    <button onclick="useSmsTemplate('payment_confirmation')" class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 rounded text-sm">
                        <strong>Payment Confirmation</strong>
                        <p class="text-gray-600 text-xs mt-1">Confirm fee payment received</p>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- WhatsApp Tab Content -->
    <div id="whatsapp-tab" class="tab-content hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- WhatsApp Form -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold mb-4">
                    <i class="fab fa-whatsapp text-green-500 mr-2"></i>Send WhatsApp Message
                </h2>
                
                <form id="whatsappForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Recipient Type</label>
                        <select name="recipient_type" id="whatsappRecipientType" class="w-full border rounded px-3 py-2">
                            <option value="selected">Selected Parents</option>
                            <option value="class">All Parents in Class</option>
                            <option value="all">All Parents</option>
                        </select>
                    </div>
                    
                    <div class="mb-4" id="whatsappClassSelection" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Class</label>
                        <select name="class_id" class="w-full border rounded px-3 py-2">
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>">
                                <?php echo htmlspecialchars($class['grade_display_name'] . ' - ' . $class['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4" id="whatsappParentSelection">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Parents</label>
                        <div class="border rounded p-3 max-h-64 overflow-y-auto">
                            <?php 
                            foreach ($parents as $parent): 
                                $hasBalance = isset($parent['total_balance']) && $parent['total_balance'] > 0;
                            ?>
                            <label class="flex items-center mb-2">
                                <input type="checkbox" name="parent_ids[]" value="<?php echo $parent['id']; ?>" 
                                       class="mr-2 whatsapp-parent-checkbox"
                                       data-has-balance="<?php echo $hasBalance ? '1' : '0'; ?>">
                                <span class="text-sm flex-1">
                                    <?php echo displayText($parent['first_name'] . ' ' . $parent['last_name']); ?>
                                    <span class="text-gray-500">(<?php echo htmlspecialchars($parent['phone']); ?>)</span>
                                    <?php if ($hasBalance): ?>
                                    <span class="ml-2 text-xs text-red-600 font-semibold">
                                        <i class="fas fa-exclamation-circle mr-1"></i>Balance: <?php echo formatCurrency($parent['total_balance']); ?>
                                    </span>
                                    <?php endif; ?>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-2 flex space-x-2">
                            <button type="button" onclick="selectAll('whatsapp')" class="text-sm text-blue-600 hover:text-blue-800">Select All</button>
                            <button type="button" onclick="selectNone('whatsapp')" class="text-sm text-blue-600 hover:text-blue-800">Deselect All</button>
                            <button type="button" onclick="selectWithBalance('whatsapp')" class="text-sm text-red-600 hover:text-red-800">Select with Balance</button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                        <textarea name="message" id="whatsappMessage" rows="6" 
                                  class="w-full border rounded px-3 py-2" 
                                  placeholder="Type your WhatsApp message here..."></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            You can use placeholders: {parent_name}, {student_name}, {balance_amount}, {school_name}
                        </p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Media URL (Optional)</label>
                        <input type="url" name="media_url" id="whatsappMediaUrl" 
                               class="w-full border rounded px-3 py-2"
                               placeholder="https://example.com/image.jpg">
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Optional: URL to an image, document, or video to send with the message
                        </p>
                    </div>
                    
                    <div id="whatsappResult" class="hidden mb-4"></div>
                    
                    <button type="submit" id="whatsappSubmitBtn"
                            class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">
                        <i class="fab fa-whatsapp mr-2"></i>Send WhatsApp Message
                    </button>
                </form>
            </div>
            
            <!-- Info Panel -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold mb-4">WhatsApp Information</h3>
                <div class="space-y-4 text-sm">
                    <div>
                        <p class="font-semibold text-gray-700 mb-2">Features:</p>
                        <ul class="list-disc list-inside text-gray-600 space-y-1">
                            <li>Send text messages</li>
                            <li>Send images & documents</li>
                            <li>Personalized messages</li>
                            <li>Bulk messaging</li>
                        </ul>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-700 mb-2">Placeholders:</p>
                        <ul class="list-disc list-inside text-gray-600 space-y-1 text-xs">
                            <li>{parent_name} - Parent's name</li>
                            <li>{student_name} - Student's name</li>
                            <li>{balance_amount} - Fee balance</li>
                            <li>{school_name} - School name</li>
                        </ul>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                        <p class="text-yellow-800 text-xs">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>Note:</strong> Make sure WhatsApp is configured in settings before sending messages.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Email Tab Content -->
    <div id="email-tab" class="tab-content hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Email Form -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold mb-4">Send Email</h2>
                
                <form id="emailForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Recipient Type</label>
                        <select name="recipient_type" id="emailRecipientType" class="w-full border rounded px-3 py-2">
                            <option value="parent">Parents</option>
                            <option value="teacher">Teachers</option>
                        </select>
                    </div>
                    
                    <div class="mb-4" id="emailTemplateSelection">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Template (Optional)</label>
                        <select name="template_id" id="emailTemplateId" class="w-full border rounded px-3 py-2">
                            <option value="0">Custom Message</option>
                            <?php foreach ($email_templates as $template): ?>
                            <option value="<?php echo $template['id']; ?>" data-category="<?php echo $template['category']; ?>">
                                <?php echo htmlspecialchars($template['name']); ?> (<?php echo ucfirst($template['category']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Select a template to auto-fill subject and message, or choose "Custom Message" to compose your own
                        </p>
                    </div>
                    
                    <div class="mb-4" id="emailClassSelection" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Class (for Parents)</label>
                        <select name="class_id" id="emailClassId" class="w-full border rounded px-3 py-2">
                            <option value="">All Parents</option>
                            <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>">
                                <?php echo htmlspecialchars($class['grade_display_name'] . ' - ' . $class['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4" id="emailParentSelection">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Parents</label>
                        <div class="border rounded p-3 max-h-64 overflow-y-auto">
                            <?php foreach ($parents as $parent): 
                                $hasBalance = isset($parent['total_balance']) && $parent['total_balance'] > 0;
                            ?>
                            <label class="flex items-center mb-2">
                                <input type="checkbox" name="recipient_ids[]" value="<?php echo $parent['id']; ?>" 
                                       class="mr-2 email-parent-checkbox"
                                       data-email="<?php echo htmlspecialchars($parent['email'] ?? ''); ?>"
                                       data-has-balance="<?php echo $hasBalance ? '1' : '0'; ?>">
                                <span class="text-sm flex-1">
                                    <?php echo displayText($parent['first_name'] . ' ' . $parent['last_name']); ?>
                                    <span class="text-gray-500">(<?php echo htmlspecialchars($parent['email'] ?? 'No email'); ?>)</span>
                                    <?php if ($hasBalance): ?>
                                    <span class="ml-2 text-xs text-red-600 font-semibold">
                                        <i class="fas fa-exclamation-circle mr-1"></i>Balance: <?php echo formatCurrency($parent['total_balance']); ?>
                                    </span>
                                    <?php endif; ?>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-2 flex space-x-2 flex-wrap gap-2">
                            <button type="button" onclick="selectAll('email', 'parent')" class="text-sm text-blue-600 hover:text-blue-800">Select All</button>
                            <button type="button" onclick="selectNone('email', 'parent')" class="text-sm text-blue-600 hover:text-blue-800">Deselect All</button>
                            <button type="button" onclick="selectWithEmail('email', 'parent')" class="text-sm text-green-600 hover:text-green-800">Select with Email</button>
                            <button type="button" onclick="selectWithBalance('email', 'parent')" class="text-sm text-red-600 hover:text-red-800 font-semibold">
                                <i class="fas fa-money-bill-wave mr-1"></i>Select with Balance
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4" id="emailBalanceFilter" style="display: none;">
                        <label class="flex items-center">
                            <input type="checkbox" name="has_balance" id="emailHasBalance" value="1" class="mr-2">
                            <span class="text-sm text-gray-700">
                                <i class="fas fa-filter mr-1"></i>
                                Only send to parents with outstanding fee balance
                            </span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-6">
                            When checked, only parents with outstanding balances will receive the email, even if manually selected.
                        </p>
                    </div>
                    
                    <div class="mb-4" id="emailTeacherSelection" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Teachers</label>
                        <div class="border rounded p-3 max-h-64 overflow-y-auto">
                            <?php foreach ($teachers as $teacher): ?>
                            <label class="flex items-center mb-2">
                                <input type="checkbox" name="recipient_ids[]" value="<?php echo $teacher['id']; ?>" 
                                       class="mr-2 email-teacher-checkbox"
                                       data-email="<?php echo htmlspecialchars($teacher['email'] ?? ''); ?>">
                                <span class="text-sm">
                                    <?php echo displayText($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                    <span class="text-gray-500">(<?php echo htmlspecialchars($teacher['email'] ?? 'No email'); ?>)</span>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-2 flex space-x-2">
                            <button type="button" onclick="selectAll('email', 'teacher')" class="text-sm text-blue-600 hover:text-blue-800">Select All</button>
                            <button type="button" onclick="selectNone('email', 'teacher')" class="text-sm text-blue-600 hover:text-blue-800">Deselect All</button>
                            <button type="button" onclick="selectWithEmail('email', 'teacher')" class="text-sm text-green-600 hover:text-green-800">Select with Email</button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                        <input type="text" name="subject" id="emailSubject" 
                               class="w-full border rounded px-3 py-2"
                               placeholder="Email subject"
                               required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                        <textarea name="message" id="emailMessage" rows="10" 
                                  class="w-full border rounded px-3 py-2" 
                                  placeholder="Type your email message here..."></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            HTML is supported. Use placeholders like {parent_name}, {student_name}, etc.
                        </p>
                        <div class="mt-2 flex items-center">
                            <input type="checkbox" name="is_html" id="isHtml" value="1" checked class="mr-2">
                            <label for="isHtml" class="text-sm text-gray-700">Send as HTML email</label>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                        <i class="fas fa-envelope mr-2"></i>Send Email
                    </button>
                </form>
                
                <div id="emailResult" class="mt-4 hidden"></div>
            </div>
            
            <!-- Email Templates & Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold mb-4">Email Templates</h2>
                
                <div class="space-y-2 mb-4">
                    <?php foreach ($email_templates as $template): ?>
                    <button onclick="useEmailTemplate(<?php echo $template['id']; ?>)" 
                            class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 rounded text-sm">
                        <strong><?php echo htmlspecialchars($template['name']); ?></strong>
                        <p class="text-gray-600 text-xs mt-1"><?php echo htmlspecialchars($template['subject']); ?></p>
                        <span class="text-xs text-blue-600"><?php echo ucfirst($template['category']); ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-4 p-3 bg-blue-50 rounded border border-blue-200">
                    <p class="text-xs font-semibold text-blue-800 mb-2">Available Variables:</p>
                    <div class="text-xs text-blue-700 space-y-1">
                        <div><code>{parent_name}</code> - Parent's name</div>
                        <div><code>{student_name}</code> - Student's name</div>
                        <div><code>{admission_number}</code> - Admission number</div>
                        <div><code>{class_name}</code> - Class name</div>
                        <div><code>{school_name}</code> - School name</div>
                        <div><code>{balance_amount}</code> - Fee balance</div>
                        <div><code>{term}</code> - Current term</div>
                        <div><code>{academic_year}</code> - Academic year</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Tab switching
function switchTab(tab) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab
    document.getElementById(tab + '-tab').classList.remove('hidden');
    
    // Add active class to selected button
    const btn = document.getElementById('tab-' + tab);
    btn.classList.add('active', 'border-blue-500', 'text-blue-600');
    btn.classList.remove('border-transparent', 'text-gray-500');
}

// SMS Form Handlers
document.getElementById('smsRecipientType').addEventListener('change', function() {
    const classSelection = document.getElementById('smsClassSelection');
    const parentSelection = document.getElementById('smsParentSelection');
    
    if (this.value === 'class') {
        classSelection.style.display = 'block';
        parentSelection.style.display = 'none';
    } else {
        classSelection.style.display = 'none';
        parentSelection.style.display = 'block';
    }
});

document.getElementById('smsMessage').addEventListener('input', function() {
    const charCount = this.value.length;
    const smsCount = Math.ceil(charCount / 160);
    
    document.getElementById('smsCharCount').textContent = charCount;
    document.getElementById('smsCount').textContent = smsCount;
});

function selectAll(type, recipientType) {
    let selector;
    if (type === 'sms') {
        selector = '.parent-checkbox';
    } else if (type === 'whatsapp') {
        selector = '.whatsapp-parent-checkbox';
    } else {
        selector = recipientType === 'parent' ? '.email-parent-checkbox' : '.email-teacher-checkbox';
    }
    document.querySelectorAll(selector).forEach(cb => cb.checked = true);
}

function selectNone(type, recipientType) {
    let selector;
    if (type === 'sms') {
        selector = '.parent-checkbox';
    } else if (type === 'whatsapp') {
        selector = '.whatsapp-parent-checkbox';
    } else {
        selector = recipientType === 'parent' ? '.email-parent-checkbox' : '.email-teacher-checkbox';
    }
    document.querySelectorAll(selector).forEach(cb => cb.checked = false);
}

function selectWithBalance(type) {
    if (type === 'whatsapp') {
        document.querySelectorAll('.whatsapp-parent-checkbox').forEach(cb => {
            const hasBalance = cb.getAttribute('data-has-balance') === '1';
            cb.checked = hasBalance;
        });
    } else if (type === 'sms') {
        // Enhanced to check balance status
        selectAll(type);
    }
}

function selectWithEmail(type, recipientType) {
    const selector = recipientType === 'parent' ? '.email-parent-checkbox' : '.email-teacher-checkbox';
    document.querySelectorAll(selector).forEach(cb => {
        const email = cb.getAttribute('data-email');
        cb.checked = email && email.trim() !== '' && email !== 'No email';
    });
}

function selectWithBalance(type, recipientType) {
    if (recipientType === 'parent') {
        document.querySelectorAll('.email-parent-checkbox').forEach(cb => {
            const hasBalance = cb.getAttribute('data-has-balance') === '1';
            cb.checked = hasBalance;
        });
        // Also check the balance filter checkbox
        const balanceFilter = document.getElementById('emailHasBalance');
        if (balanceFilter) {
            balanceFilter.checked = true;
        }
    }
}

const smsTemplates = {
    fee_reminder: 'Dear {parent_name}, This is a reminder that {student_name} (Adm: {admission_number}) has outstanding fees of {fee_balance}. Please make payment at your earliest convenience. Thank you. - {school_name}',
    attendance: 'Dear {parent_name}, We noticed that {student_name} was absent today ({current_date}). Please contact the school if there is any concern. Thank you. - {school_name}',
    announcement: 'Dear {parent_name}, Important school announcement. Please check your email or visit the school for details. Thank you. - {school_name}',
    payment_confirmation: 'Dear {parent_name}, We have received your payment for {student_name}. Thank you for your prompt payment. Receipt available at school office. - {school_name}'
};

function useSmsTemplate(type) {
    document.getElementById('smsMessage').value = smsTemplates[type] || '';
    document.getElementById('smsMessage').dispatchEvent(new Event('input'));
}

// WhatsApp Form Handlers
document.getElementById('whatsappRecipientType').addEventListener('change', function() {
    const classSelection = document.getElementById('whatsappClassSelection');
    const parentSelection = document.getElementById('whatsappParentSelection');
    
    if (this.value === 'class') {
        classSelection.style.display = 'block';
        parentSelection.style.display = 'none';
    } else {
        classSelection.style.display = 'none';
        parentSelection.style.display = 'block';
    }
});

document.getElementById('whatsappForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = document.getElementById('whatsappSubmitBtn');
    const resultDiv = document.getElementById('whatsappResult');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
    resultDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/communication/sendBulkWhatsApp', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        resultDiv.classList.remove('hidden');
        
        if (data.success) {
            resultDiv.className = 'p-4 bg-green-100 border border-green-400 text-green-700 rounded';
            resultDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i><strong>Success!</strong> ' + data.message;
            this.reset();
        } else {
            resultDiv.className = 'p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            resultDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i><strong>Error:</strong> ' + (data.message || 'Failed to send WhatsApp message');
        }
    } catch (error) {
        resultDiv.classList.remove('hidden');
        resultDiv.className = 'p-4 bg-red-100 border border-red-400 text-red-700 rounded';
        resultDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i><strong>Error:</strong> ' + error.message;
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Email Form Handlers
document.getElementById('emailRecipientType').addEventListener('change', function() {
    const parentSelection = document.getElementById('emailParentSelection');
    const teacherSelection = document.getElementById('emailTeacherSelection');
    const classSelection = document.getElementById('emailClassSelection');
    const balanceFilter = document.getElementById('emailBalanceFilter');
    
    if (this.value === 'parent') {
        parentSelection.style.display = 'block';
        teacherSelection.style.display = 'none';
        classSelection.style.display = 'block';
        balanceFilter.style.display = 'block';
    } else {
        parentSelection.style.display = 'none';
        teacherSelection.style.display = 'block';
        classSelection.style.display = 'none';
        balanceFilter.style.display = 'none';
    }
});

document.getElementById('emailTemplateId').addEventListener('change', function() {
    const templateId = this.value;
    if (templateId > 0) {
        // Load template preview
        const recipientType = document.getElementById('emailRecipientType').value;
        const firstRecipient = document.querySelector('.email-' + recipientType + '-checkbox:checked');
        if (firstRecipient) {
            const recipientId = firstRecipient.value;
            loadTemplatePreview(templateId, recipientType, recipientId);
        }
    } else {
        // Clear fields
        document.getElementById('emailSubject').value = '';
        document.getElementById('emailMessage').value = '';
    }
});

function loadTemplatePreview(templateId, recipientType, recipientId) {
    fetch(`<?php echo BASE_URL; ?>/communication/getTemplatePreview?template_id=${templateId}&recipient_type=${recipientType}&recipient_id=${recipientId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('emailSubject').value = data.subject;
                document.getElementById('emailMessage').value = data.body;
            }
        })
        .catch(error => {
            console.error('Error loading template:', error);
        });
}

function useEmailTemplate(templateId) {
    document.getElementById('emailTemplateId').value = templateId;
    document.getElementById('emailTemplateId').dispatchEvent(new Event('change'));
}

// SMS Form Submit
document.getElementById('smsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const resultDiv = document.getElementById('smsResult');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
    resultDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/communication/sendBulkSms', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultDiv.className = 'mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded';
            resultDiv.innerHTML = `
                <strong>Success!</strong><br>
                Sent to ${data.success_count} recipients. 
                ${data.failure_count > 0 ? data.failure_count + ' failed.' : ''}
            `;
            resultDiv.classList.remove('hidden');
            this.reset();
            document.getElementById('smsCharCount').textContent = '0';
            document.getElementById('smsCount').textContent = '0';
        } else {
            resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            resultDiv.innerHTML = '<strong>Error:</strong> ' + (data.message || 'Failed to send SMS');
            resultDiv.classList.remove('hidden');
        }
    } catch (error) {
        resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
        resultDiv.innerHTML = '<strong>Error:</strong> An error occurred. Please try again.';
        resultDiv.classList.remove('hidden');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Send SMS';
    }
});

// Email Form Submit
document.getElementById('emailForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const resultDiv = document.getElementById('emailResult');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
    resultDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/communication/sendEmail', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultDiv.className = 'mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded';
            resultDiv.innerHTML = `
                <strong>Success!</strong><br>
                Sent to ${data.success_count} recipients. 
                ${data.failure_count > 0 ? data.failure_count + ' failed.' : ''}
            `;
            resultDiv.classList.remove('hidden');
            this.reset();
            document.getElementById('emailSubject').value = '';
            document.getElementById('emailMessage').value = '';
        } else {
            resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            resultDiv.innerHTML = '<strong>Error:</strong> ' + (data.message || 'Failed to send email');
            resultDiv.classList.remove('hidden');
        }
    } catch (error) {
        resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
        resultDiv.innerHTML = '<strong>Error:</strong> ' + error.message;
        resultDiv.classList.remove('hidden');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-envelope mr-2"></i>Send Email';
    }
});
</script>
