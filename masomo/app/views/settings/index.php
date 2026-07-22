<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">System Settings</h1>
    </div>
    
    <div class="max-w-4xl mx-auto">
        <!-- School Logo Upload -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4">School Logo</h2>
            <p class="text-gray-600 mb-6">
                Upload your school logo. This will be used in student ID cards, reports, and official documents. The system logo will be used in dashboards and login pages.
            </p>
            
            <form id="logoForm" method="POST" action="<?php echo BASE_URL; ?>/settings/uploadLogo" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="flex items-center space-x-6">
                    <div class="flex-shrink-0">
                        <?php 
                        $schoolLogo = getSchoolLogo();
                        $isSystemLogo = strpos($schoolLogo, 'logo1.png') !== false;
                        ?>
                        <img id="logoPreview" 
                             src="<?php echo $schoolLogo; ?>" 
                             alt="School Logo" 
                             class="h-24 w-24 object-contain border-2 border-gray-300 rounded-lg p-2 bg-gray-50">
                        <?php if ($isSystemLogo): ?>
                        <p class="text-xs text-gray-500 mt-1 text-center">System Logo</p>
                        <?php else: ?>
                        <p class="text-xs text-green-600 mt-1 text-center">School Logo</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload Logo</label>
                        <input type="file" 
                               id="logoInput" 
                               name="logo" 
                               accept="image/jpeg,image/jpg,image/png,image/gif"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-500 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Max size: 2MB. Supported formats: JPEG, PNG, GIF
                        </p>
                        
                        <button type="submit" 
                                class="mt-4 bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                            <i class="fas fa-upload mr-2"></i>Upload Logo
                        </button>
                        
                        <?php if (!$isSystemLogo): ?>
                        <button type="button" 
                                id="removeLogo"
                                class="mt-4 ml-2 bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700">
                            <i class="fas fa-trash mr-2"></i>Remove Logo
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div id="logoResult" class="mt-4 hidden"></div>
            </form>
        </div>
        
        <!-- Dashboard Logo Upload -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4">Dashboard Logo</h2>
            <p class="text-gray-600 mb-6">
                Upload a custom logo to display in the dashboard sidebar. If no custom logo is uploaded, the system logo will be used.
            </p>
            
            <form id="dashboardLogoForm" method="POST" action="<?php echo BASE_URL; ?>/settings/uploadDashboardLogo" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="flex items-center space-x-6">
                    <div class="flex-shrink-0">
                        <?php 
                        $dashboardLogo = getDashboardLogo();
                        $db = Database::getInstance()->getConnection();
                        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'dashboard_logo'");
                        $stmt->execute();
                        $dashboardLogoResult = $stmt->fetch(PDO::FETCH_ASSOC);
                        $hasCustomDashboardLogo = $dashboardLogoResult && !empty($dashboardLogoResult['setting_value']);
                        ?>
                        <img id="dashboardLogoPreview" 
                             src="<?php echo $dashboardLogo; ?>" 
                             alt="Dashboard Logo" 
                             class="object-contain border-2 border-gray-300 rounded-lg p-2 bg-gray-50"
                             style="width: 183.5px; height: 38px; max-width: 100%;">
                        <?php if ($hasCustomDashboardLogo): ?>
                        <p class="text-xs text-green-600 mt-1 text-center">Custom Logo</p>
                        <?php else: ?>
                        <p class="text-xs text-gray-500 mt-1 text-center">System Logo</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload Dashboard Logo</label>
                        <input type="file" 
                               id="dashboardLogoInput" 
                               name="dashboard_logo" 
                               accept="image/jpeg,image/jpg,image/png,image/gif"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-500 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Max size: 2MB. Supported formats: JPEG, PNG, GIF. Required dimensions: <strong>367x76 pixels</strong>. Image will be automatically resized to these exact dimensions.
                        </p>
                        
                        <button type="submit" 
                                class="mt-4 bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                            <i class="fas fa-upload mr-2"></i>Upload Dashboard Logo
                        </button>
                        
                        <?php if ($hasCustomDashboardLogo): ?>
                        <button type="button" 
                                id="removeDashboardLogo"
                                class="mt-4 ml-2 bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700">
                            <i class="fas fa-trash mr-2"></i>Remove Logo
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div id="dashboardLogoResult" class="mt-4 hidden"></div>
            </form>
        </div>
        
        <!-- School Information -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4">School Information</h2>
            <p class="text-gray-600 mb-6">
                Configure your school details. These will be used in student ID cards, reports, and other documents.
            </p>
            
            <form id="settingsForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">School Name *</label>
                        <input type="text" name="school_name" 
                               value="<?php echo htmlspecialchars($settings['school_name'] ?? SCHOOL_NAME ?? ''); ?>" 
                               class="w-full border rounded px-3 py-2"
                               placeholder="Enter school name"
                               required>
                        <p class="text-xs text-gray-500 mt-1">This name will appear on student ID cards</p>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">School Address</label>
                        <textarea name="school_address" rows="2"
                                  class="w-full border rounded px-3 py-2"
                                  placeholder="Enter school address"><?php echo htmlspecialchars($settings['school_address'] ?? SCHOOL_ADDRESS ?? ''); ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="tel" name="school_phone" 
                               value="<?php echo htmlspecialchars($settings['school_phone'] ?? SCHOOL_PHONE ?? ''); ?>" 
                               class="w-full border rounded px-3 py-2"
                               placeholder="+254700000000">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" name="school_email" 
                               value="<?php echo htmlspecialchars($settings['school_email'] ?? SCHOOL_EMAIL ?? ''); ?>" 
                               class="w-full border rounded px-3 py-2"
                               placeholder="info@school.co.ke">
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Save Settings
                    </button>
                </div>
            </form>
            
            <div id="settingsResult" class="mt-4 hidden"></div>
        </div>
        
        <!-- SMTP Email Configuration -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4">Email Configuration (SMTP)</h2>
            <p class="text-gray-600 mb-6">
                Configure SMTP settings for sending emails from the system.
            </p>
            
            <form id="smtpForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Host *</label>
                        <input type="text" name="smtp_host" 
                               value="<?php echo htmlspecialchars($settings['smtp_host'] ?? 'smtp.gmail.com'); ?>" 
                               class="w-full border rounded px-3 py-2"
                               placeholder="smtp.gmail.com"
                               required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Port *</label>
                        <input type="number" name="smtp_port" 
                               value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '587'); ?>" 
                               class="w-full border rounded px-3 py-2"
                               placeholder="587"
                               required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Username *</label>
                        <input type="text" name="smtp_username" 
                               value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>" 
                               class="w-full border rounded px-3 py-2"
                               placeholder="your-email@gmail.com"
                               required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Password *</label>
                        <input type="password" name="smtp_password" 
                               value="<?php echo htmlspecialchars($settings['smtp_password'] ?? ''); ?>" 
                               class="w-full border rounded px-3 py-2"
                               placeholder="App password or email password"
                               required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Encryption</label>
                        <select name="smtp_encryption" class="w-full border rounded px-3 py-2">
                            <option value="tls" <?php echo ($settings['smtp_encryption'] ?? 'tls') == 'tls' ? 'selected' : ''; ?>>TLS</option>
                            <option value="ssl" <?php echo ($settings['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                            <option value="none" <?php echo ($settings['smtp_encryption'] ?? '') == 'none' ? 'selected' : ''; ?>>None</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Email *</label>
                        <input type="email" name="smtp_from_email" 
                               value="<?php echo htmlspecialchars($settings['smtp_from_email'] ?? ''); ?>" 
                               class="w-full border rounded px-3 py-2"
                               placeholder="noreply@school.co.ke"
                               required>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Name</label>
                        <input type="text" name="smtp_from_name" 
                               value="<?php echo htmlspecialchars($settings['smtp_from_name'] ?? getSchoolName()); ?>" 
                               class="w-full border rounded px-3 py-2"
                               placeholder="School Name">
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Save SMTP Settings
                    </button>
                </div>
            </form>
            
            <div id="smtpResult" class="mt-4 hidden"></div>
            
            <!-- Test Email Section -->
            <div class="mt-6 pt-6 border-t">
                <h3 class="text-lg font-semibold mb-3">Test Email Configuration</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Send a test email to verify your SMTP settings are working correctly.
                </p>
                <form id="testEmailForm" class="flex items-end space-x-3">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Test Email Address</label>
                        <input type="email" 
                               name="test_email" 
                               id="testEmailInput"
                               value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>"
                               class="w-full border rounded px-3 py-2"
                               placeholder="Enter email address to send test email">
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Leave empty to use SMTP username as recipient
                        </p>
                    </div>
                    <button type="submit" 
                            id="testEmailBtn"
                            class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                        <i class="fas fa-paper-plane mr-2"></i>Send Test Email
                    </button>
                </form>
                <div id="testEmailResult" class="mt-4 hidden"></div>
            </div>
        </div>
        
        <!-- Payment Methods Configuration -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4">Payment Methods Configuration</h2>
            <p class="text-gray-600 mb-6">
                Configure payment methods for fee collection. Set up M-Pesa PayBill and bank account details.
            </p>
            
            <form id="paymentForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <!-- M-Pesa Configuration -->
                <div class="border-b pb-6 mb-6">
                    <h3 class="text-xl font-semibold mb-4 text-green-600">
                        <i class="fas fa-mobile-alt mr-2"></i>M-Pesa PayBill Configuration
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">PayBill Number *</label>
                            <input type="text" name="mpesa_paybill_number" 
                                   value="<?php echo htmlspecialchars($settings['mpesa_paybill_number'] ?? ''); ?>" 
                                   class="w-full border rounded px-3 py-2"
                                   placeholder="123456"
                                   required>
                            <p class="text-xs text-gray-500 mt-1">Your M-Pesa PayBill number</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Account Number Format</label>
                            <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-2">
                                <p class="text-sm text-blue-800 font-semibold mb-1">Format: Business Number#Admission Number</p>
                                <p class="text-xs text-blue-700">Example: <code class="bg-blue-100 px-1 rounded">12345#100</code></p>
                                <p class="text-xs text-blue-700 mt-1">Parents will enter: Paybill Number → Account Number (e.g., 12345#100) → Amount</p>
                            </div>
                            <input type="text" name="mpesa_paybill_account_prefix" 
                                   value="<?php echo htmlspecialchars($settings['mpesa_paybill_account_prefix'] ?? ''); ?>" 
                                   class="w-full border rounded px-3 py-2"
                                   placeholder="Leave empty - system uses format: business_number#admission_number">
                            <p class="text-xs text-gray-500 mt-1">Optional: If you want a prefix before the # (e.g., SCH12345#100). Leave empty to use format: business_number#admission_number</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Consumer Key</label>
                            <input type="text" name="mpesa_api_consumer_key" 
                                   value="<?php echo htmlspecialchars($settings['mpesa_api_consumer_key'] ?? ''); ?>" 
                                   class="w-full border rounded px-3 py-2"
                                   placeholder="For automatic payment updates">
                            <p class="text-xs text-gray-500 mt-1">M-Pesa API Consumer Key (optional)</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Consumer Secret</label>
                            <input type="password" name="mpesa_api_consumer_secret" 
                                   value="<?php echo htmlspecialchars($settings['mpesa_api_consumer_secret'] ?? ''); ?>" 
                                   class="w-full border rounded px-3 py-2"
                                   placeholder="For automatic payment updates">
                            <p class="text-xs text-gray-500 mt-1">M-Pesa API Consumer Secret (optional)</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Passkey</label>
                            <input type="text" name="mpesa_api_passkey" 
                                   value="<?php echo htmlspecialchars($settings['mpesa_api_passkey'] ?? ''); ?>" 
                                   class="w-full border rounded px-3 py-2"
                                   placeholder="For automatic payment updates">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Shortcode</label>
                            <input type="text" name="mpesa_api_shortcode" 
                                   value="<?php echo htmlspecialchars($settings['mpesa_api_shortcode'] ?? ''); ?>" 
                                   class="w-full border rounded px-3 py-2"
                                   placeholder="For automatic payment updates">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Environment</label>
                            <select name="mpesa_environment" class="w-full border rounded px-3 py-2">
                                <option value="sandbox" <?php echo ($settings['mpesa_environment'] ?? 'sandbox') == 'sandbox' ? 'selected' : ''; ?>>Sandbox (Testing)</option>
                                <option value="production" <?php echo ($settings['mpesa_environment'] ?? '') == 'production' ? 'selected' : ''; ?>>Production (Live)</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Select sandbox for testing or production for live payments</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Callback URL *</label>
                            <div class="flex gap-2">
                                <input type="url" name="mpesa_callback_url" id="mpesaCallbackUrl"
                                       value="<?php echo htmlspecialchars($settings['mpesa_callback_url'] ?? BASE_URL . '/mpesa/callback'); ?>" 
                                       class="flex-1 border rounded px-3 py-2"
                                       placeholder="https://yourdomain.com/masomo/mpesa/callback"
                                       required>
                                <button type="button" id="useCurrentUrlBtn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                                    <i class="fas fa-magic mr-1"></i>Use Current URL
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                <strong>Current Detected URL:</strong> <code id="currentBaseUrl" class="bg-gray-100 px-2 py-1 rounded"><?php echo BASE_URL; ?>/mpesa/callback</code>
                                <?php if (strpos(BASE_URL, 'ngrok') !== false || strpos(BASE_URL, 'ngrok-free.app') !== false): ?>
                                <span class="ml-2 text-green-600 font-semibold">✓ ngrok detected</span>
                                <?php endif; ?>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                <strong>Important:</strong> This must be a publicly accessible URL (not localhost). 
                                If using ngrok, click "Use Current URL" button above to automatically set it. 
                                <strong class="text-red-600">Remember to update this when your ngrok URL changes!</strong>
                            </p>
                            <?php if (strpos(BASE_URL, 'localhost') !== false): ?>
                            <div class="mt-2 p-2 bg-yellow-100 border border-yellow-400 text-yellow-800 rounded text-xs">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <strong>Warning:</strong> You're using localhost. M-Pesa callbacks won't work. 
                                Use ngrok or a public domain for M-Pesa integration to work.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="payment_auto_reconcile" 
                                   value="1"
                                   <?php echo ($settings['payment_auto_reconcile'] ?? '0') == '1' ? 'checked' : ''; ?>
                                   class="mr-2">
                            <span class="text-sm text-gray-700">Enable automatic payment reconciliation via M-Pesa API</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-6">
                            When enabled, payments made via M-Pesa PayBill will be automatically updated in the system
                        </p>
                    </div>
                </div>
                
                <!-- Bank Accounts Configuration -->
                <div>
                    <h3 class="text-xl font-semibold mb-4 text-blue-600">
                        <i class="fas fa-university mr-2"></i>Bank Account Details
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Equity Bank -->
                        <div class="border rounded p-4 bg-green-50">
                            <h4 class="font-semibold mb-3 text-green-600">
                                <i class="fas fa-university mr-2"></i>Equity Bank
                            </h4>
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Number *</label>
                                <input type="text" name="equity_bank_account" 
                                       value="<?php echo htmlspecialchars($settings['equity_bank_account'] ?? ''); ?>" 
                                       class="w-full border rounded px-3 py-2"
                                       placeholder="Account number"
                                       required>
                                <p class="text-xs text-gray-500 mt-1">School's Equity Bank account number</p>
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                                <input type="text" name="equity_bank_name" 
                                       value="<?php echo htmlspecialchars($settings['equity_bank_name'] ?? 'Equity Bank'); ?>" 
                                       class="w-full border rounded px-3 py-2"
                                       placeholder="Account name">
                            </div>
                            
                            <div class="border-t pt-3 mt-3">
                                <h5 class="font-semibold text-sm mb-2 text-green-700">Jenga API Configuration</h5>
                                <p class="text-xs text-gray-600 mb-3">
                                    Configure Jenga API to automatically fetch and reconcile transactions from Equity Bank.
                                </p>
                                
                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">API Key *</label>
                                    <input type="text" name="jenga_api_key" 
                                           value="<?php echo htmlspecialchars($settings['jenga_api_key'] ?? ''); ?>" 
                                           class="w-full border rounded px-3 py-2"
                                           placeholder="Your Jenga API Key">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">API Secret *</label>
                                    <input type="password" name="jenga_api_secret" 
                                           value="<?php echo htmlspecialchars($settings['jenga_api_secret'] ?? ''); ?>" 
                                           class="w-full border rounded px-3 py-2"
                                           placeholder="Your Jenga API Secret">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Merchant Code</label>
                                    <input type="text" name="jenga_merchant_code" 
                                           value="<?php echo htmlspecialchars($settings['jenga_merchant_code'] ?? ''); ?>" 
                                           class="w-full border rounded px-3 py-2"
                                           placeholder="Merchant Code">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Environment</label>
                                    <select name="jenga_environment" class="w-full border rounded px-3 py-2">
                                        <option value="sandbox" <?php echo ($settings['jenga_environment'] ?? 'sandbox') == 'sandbox' ? 'selected' : ''; ?>>Sandbox (Testing)</option>
                                        <option value="production" <?php echo ($settings['jenga_environment'] ?? '') == 'production' ? 'selected' : ''; ?>>Production (Live)</option>
                                    </select>
                                </div>
                                
                                <div class="mt-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="jenga_auto_reconcile" 
                                               value="1"
                                               <?php echo ($settings['jenga_auto_reconcile'] ?? '0') == '1' ? 'checked' : ''; ?>
                                               class="mr-2">
                                        <span class="text-sm text-gray-700">Enable automatic transaction reconciliation</span>
                                    </label>
                                    <p class="text-xs text-gray-500 mt-1 ml-6">
                                        Automatically match and reconcile payments when transactions are fetched
                                    </p>
                                </div>
                                
                                <div class="mt-3">
                                    <a href="<?php echo BASE_URL; ?>/equitybank" 
                                       class="inline-block bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">
                                        <i class="fas fa-sync mr-2"></i>View Transactions
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Co-operative Bank -->
                        <div class="border rounded p-4">
                            <h4 class="font-semibold mb-3 text-blue-600">Co-operative Bank</h4>
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                                <input type="text" name="coop_bank_account" 
                                       value="<?php echo htmlspecialchars($settings['coop_bank_account'] ?? ''); ?>" 
                                       class="w-full border rounded px-3 py-2"
                                       placeholder="Account number">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                                <input type="text" name="coop_bank_name" 
                                       value="<?php echo htmlspecialchars($settings['coop_bank_name'] ?? 'Co-operative Bank'); ?>" 
                                       class="w-full border rounded px-3 py-2"
                                       placeholder="Account name">
                            </div>
                        </div>
                        
                        <!-- KCB Bank -->
                        <div class="border rounded p-4">
                            <h4 class="font-semibold mb-3 text-red-600">KCB Bank</h4>
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                                <input type="text" name="kcb_bank_account" 
                                       value="<?php echo htmlspecialchars($settings['kcb_bank_account'] ?? ''); ?>" 
                                       class="w-full border rounded px-3 py-2"
                                       placeholder="Account number">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                                <input type="text" name="kcb_bank_name" 
                                       value="<?php echo htmlspecialchars($settings['kcb_bank_name'] ?? 'Kenya Commercial Bank'); ?>" 
                                       class="w-full border rounded px-3 py-2"
                                       placeholder="Account name">
                            </div>
                        </div>
                        
                        <!-- Family Bank -->
                        <div class="border rounded p-4">
                            <h4 class="font-semibold mb-3 text-purple-600">Family Bank</h4>
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                                <input type="text" name="family_bank_account" 
                                       value="<?php echo htmlspecialchars($settings['family_bank_account'] ?? ''); ?>" 
                                       class="w-full border rounded px-3 py-2"
                                       placeholder="Account number">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                                <input type="text" name="family_bank_name" 
                                       value="<?php echo htmlspecialchars($settings['family_bank_name'] ?? 'Family Bank'); ?>" 
                                       class="w-full border rounded px-3 py-2"
                                       placeholder="Account name">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Save Payment Settings
                    </button>
                </div>
            </form>
            
            <div id="paymentResult" class="mt-4 hidden"></div>
        </div>
        
        <!-- Information Box -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
            <h3 class="font-bold text-blue-800 mb-2">Note:</h3>
            <ul class="list-disc list-inside text-sm text-blue-700 space-y-1">
                <li>School name will be used on student ID cards instead of the system name</li>
                <li>System footer will continue to show the system name (<?php echo APP_NAME; ?>)</li>
                <li>These settings are used across reports, ID cards, and official documents</li>
                <li>For Gmail, use an App Password instead of your regular password</li>
                <li><strong>M-Pesa:</strong> Parents will use PayBill number and student admission number as account number</li>
                <li><strong>Automatic Updates:</strong> Requires M-Pesa API integration with webhook callback</li>
            </ul>
        </div>
    </div>
</div>

<script>
document.getElementById('settingsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const resultDiv = document.getElementById('settingsResult');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    resultDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/settings/save', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultDiv.className = 'mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded';
            resultDiv.innerHTML = '<strong>Success!</strong> Settings saved successfully.';
            resultDiv.classList.remove('hidden');
        } else {
            resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            resultDiv.innerHTML = '<strong>Error:</strong> ' + (data.message || 'Failed to save settings');
            resultDiv.classList.remove('hidden');
        }
    } catch (error) {
        resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
        resultDiv.innerHTML = '<strong>Error:</strong> An error occurred. Please try again.';
        resultDiv.classList.remove('hidden');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Settings';
    }
});

// Payment Form Handler
document.getElementById('paymentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const resultDiv = document.getElementById('paymentResult');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    resultDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/settings/savePayment', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultDiv.className = 'mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded';
            resultDiv.innerHTML = '<strong>Success!</strong> Payment settings saved successfully.';
            resultDiv.classList.remove('hidden');
        } else {
            resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            resultDiv.innerHTML = '<strong>Error:</strong> ' + (data.message || 'Failed to save payment settings');
            resultDiv.classList.remove('hidden');
        }
    } catch (error) {
        resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
        resultDiv.innerHTML = '<strong>Error:</strong> An error occurred. Please try again.';
        resultDiv.classList.remove('hidden');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Payment Settings';
    }
});

// SMTP Form Handler
document.getElementById('smtpForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const resultDiv = document.getElementById('smtpResult');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    resultDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/settings/saveSmtp', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultDiv.className = 'mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded';
            resultDiv.innerHTML = '<strong>Success!</strong> SMTP settings saved successfully.';
            resultDiv.classList.remove('hidden');
        } else {
            resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            resultDiv.innerHTML = '<strong>Error:</strong> ' + (data.message || 'Failed to save SMTP settings');
            resultDiv.classList.remove('hidden');
        }
    } catch (error) {
        resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
        resultDiv.innerHTML = '<strong>Error:</strong> An error occurred. Please try again.';
        resultDiv.classList.remove('hidden');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save SMTP Settings';
    }
});

// Test Email Form Handler
document.getElementById('testEmailForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('testEmailBtn');
    const resultDiv = document.getElementById('testEmailResult');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
    resultDiv.classList.add('hidden');
    
    try {
        const formData = new FormData(this);
        const response = await fetch('<?php echo BASE_URL; ?>/settings/testEmail', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        resultDiv.classList.remove('hidden');
        
        if (data.success) {
            resultDiv.className = 'mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded';
            resultDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i><strong>Success!</strong> ' + data.message;
        } else {
            resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            let errorHtml = '<i class="fas fa-exclamation-circle mr-2"></i><strong>Error:</strong> ' + (data.message || 'Failed to send test email');
            
            // If PHPMailer is needed, add installation instructions
            if (data.needs_phpmailer) {
                errorHtml += '<div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded">';
                errorHtml += '<p class="font-semibold mb-2">Installation Instructions:</p>';
                errorHtml += '<ol class="list-decimal list-inside space-y-1 text-sm">';
                errorHtml += '<li>Open Command Prompt or Terminal</li>';
                errorHtml += '<li>Navigate to your project directory: <code class="bg-gray-200 px-1 rounded">cd C:\\xampp\\htdocs\\masomo</code></li>';
                errorHtml += '<li>Run: <code class="bg-gray-200 px-1 rounded">composer require phpmailer/phpmailer</code></li>';
                errorHtml += '<li>If composer is not installed, download it from <a href="https://getcomposer.org/download/" target="_blank" class="text-blue-600 underline">getcomposer.org</a></li>';
                errorHtml += '</ol>';
                errorHtml += '</div>';
            }
            
            resultDiv.innerHTML = errorHtml;
        }
    } catch (error) {
        resultDiv.classList.remove('hidden');
        resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
        resultDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i><strong>Error:</strong> ' + error.message;
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Handle "Use Current URL" button for M-Pesa callback
const useCurrentUrlBtn = document.getElementById('useCurrentUrlBtn');
const mpesaCallbackUrlInput = document.getElementById('mpesaCallbackUrl');
const currentBaseUrlDisplay = document.getElementById('currentBaseUrl');

if (useCurrentUrlBtn && mpesaCallbackUrlInput) {
    useCurrentUrlBtn.addEventListener('click', function() {
        const currentUrl = '<?php echo BASE_URL; ?>/mpesa/callback';
        mpesaCallbackUrlInput.value = currentUrl;
        
        // Show confirmation
        const originalText = useCurrentUrlBtn.innerHTML;
        useCurrentUrlBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Updated!';
        useCurrentUrlBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        useCurrentUrlBtn.classList.add('bg-green-600', 'hover:bg-green-700');
        
        setTimeout(function() {
            useCurrentUrlBtn.innerHTML = originalText;
            useCurrentUrlBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
            useCurrentUrlBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }, 2000);
    });
}

// Update current URL display if ngrok is detected
if (currentBaseUrlDisplay) {
    const currentUrl = '<?php echo BASE_URL; ?>';
    if (currentUrl.includes('ngrok') || currentUrl.includes('ngrok-free.app')) {
        currentBaseUrlDisplay.classList.add('text-green-600', 'font-semibold');
        currentBaseUrlDisplay.innerHTML = '<span class="text-green-600">✓ ' + currentUrl + '/mpesa/callback</span> <small class="text-gray-500">(ngrok detected)</small>';
    }
}

// Logo Form Handler
const logoForm = document.getElementById('logoForm');
if (logoForm) {
    logoForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const resultDiv = document.getElementById('logoResult');
        const logoPreview = document.getElementById('logoPreview');
        const logoInput = document.getElementById('logoInput');
        
        // Check if file is selected
        if (!logoInput.files || !logoInput.files[0]) {
            resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            resultDiv.innerHTML = '<strong>Error:</strong> Please select a logo file to upload.';
            resultDiv.classList.remove('hidden');
            return;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Uploading...';
        resultDiv.classList.add('hidden');
        
        try {
            const response = await fetch('<?php echo BASE_URL; ?>/settings/uploadLogo', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                resultDiv.className = 'mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded';
                resultDiv.innerHTML = '<strong>Success!</strong> ' + (data.message || 'Logo uploaded successfully.');
                resultDiv.classList.remove('hidden');
                
                // Update preview image
                if (data.logo_url && logoPreview) {
                    logoPreview.src = data.logo_url + '?t=' + new Date().getTime();
                }
                
                // Reset form
                logoInput.value = '';
                
                // Reload page after 2 seconds to show updated logo
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
                resultDiv.innerHTML = '<strong>Error:</strong> ' + (data.message || 'Failed to upload logo');
                resultDiv.classList.remove('hidden');
            }
        } catch (error) {
            resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            resultDiv.innerHTML = '<strong>Error:</strong> An error occurred. Please try again.';
            resultDiv.classList.remove('hidden');
            console.error('Logo upload error:', error);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-upload mr-2"></i>Upload Logo';
        }
    });
}

// Remove Logo Handler
const removeLogoBtn = document.getElementById('removeLogo');
if (removeLogoBtn) {
    removeLogoBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to remove the school logo? This will restore the default system logo.')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('csrf_token', '<?php echo $csrf_token; ?>');
        
        this.disabled = true;
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Removing...';
        
        try {
            const response = await fetch('<?php echo BASE_URL; ?>/settings/removeLogo', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Reload page to show default logo
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to remove logo'));
                this.disabled = false;
                this.innerHTML = originalText;
            }
        } catch (error) {
            alert('Error: An error occurred. Please try again.');
            this.disabled = false;
            this.innerHTML = originalText;
            console.error('Remove logo error:', error);
        }
    });
}

// Dashboard Logo Form Handler
const dashboardLogoForm = document.getElementById('dashboardLogoForm');
if (dashboardLogoForm) {
    dashboardLogoForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const resultDiv = document.getElementById('dashboardLogoResult');
        const dashboardLogoPreview = document.getElementById('dashboardLogoPreview');
        const dashboardLogoInput = document.getElementById('dashboardLogoInput');
        
        // Check if file is selected
        if (!dashboardLogoInput.files || !dashboardLogoInput.files[0]) {
            resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            resultDiv.innerHTML = '<strong>Error:</strong> Please select a logo file to upload.';
            resultDiv.classList.remove('hidden');
            return;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Uploading...';
        resultDiv.classList.add('hidden');
        
        try {
            const response = await fetch('<?php echo BASE_URL; ?>/settings/uploadDashboardLogo', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                resultDiv.className = 'mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded';
                resultDiv.innerHTML = '<strong>Success!</strong> ' + (data.message || 'Dashboard logo uploaded successfully.');
                resultDiv.classList.remove('hidden');
                
                // Update preview image
                if (data.logo_url && dashboardLogoPreview) {
                    dashboardLogoPreview.src = data.logo_url + '?t=' + new Date().getTime();
                }
                
                // Reset form
                dashboardLogoInput.value = '';
                
                // Reload page after 2 seconds to show updated logo
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
                resultDiv.innerHTML = '<strong>Error:</strong> ' + (data.message || 'Failed to upload dashboard logo');
                resultDiv.classList.remove('hidden');
            }
        } catch (error) {
            resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            resultDiv.innerHTML = '<strong>Error:</strong> An error occurred. Please try again.';
            resultDiv.classList.remove('hidden');
            console.error('Dashboard logo upload error:', error);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-upload mr-2"></i>Upload Dashboard Logo';
        }
    });
}

// Remove Dashboard Logo Handler
const removeDashboardLogoBtn = document.getElementById('removeDashboardLogo');
if (removeDashboardLogoBtn) {
    removeDashboardLogoBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to remove the dashboard logo? This will restore the default system logo.')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('csrf_token', '<?php echo $csrf_token; ?>');
        
        this.disabled = true;
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Removing...';
        
        try {
            const response = await fetch('<?php echo BASE_URL; ?>/settings/removeDashboardLogo', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Reload page to show default logo
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to remove dashboard logo'));
                this.disabled = false;
                this.innerHTML = originalText;
            }
        } catch (error) {
            alert('Error: An error occurred. Please try again.');
            this.disabled = false;
            this.innerHTML = originalText;
            console.error('Remove dashboard logo error:', error);
        }
    });
}
</script>

