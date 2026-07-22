<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Communication Settings</h1>
        <a href="<?php echo BASE_URL; ?>/communication" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>
    
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4">TextSMS.co.ke Configuration</h2>
            <p class="text-gray-600 mb-6">
                Configure your SMS gateway settings. Get your API credentials from 
                <a href="https://textsms.co.ke/" target="_blank" class="text-blue-600 hover:underline">TextSMS.co.ke</a>
            </p>
            
            <form id="settingsForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
                    <input type="text" name="sms_api_key" 
                           value="<?php echo htmlspecialchars($sms_settings['sms_api_key'] ?? ''); ?>" 
                           class="w-full border rounded px-3 py-2"
                           placeholder="Enter your TextSMS API key">
                    <p class="text-xs text-gray-500 mt-1">Get your API key from your TextSMS account dashboard</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sender ID</label>
                    <input type="text" name="sms_sender_id" 
                           value="<?php echo htmlspecialchars($sms_settings['sms_sender_id'] ?? 'MASOMO'); ?>" 
                           class="w-full border rounded px-3 py-2"
                           placeholder="MASOMO"
                           maxlength="11">
                    <p class="text-xs text-gray-500 mt-1">Sender ID must be registered with TextSMS (max 11 characters)</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Partner ID</label>
                    <input type="text" name="sms_partner_id" 
                           value="<?php echo htmlspecialchars($sms_settings['sms_partner_id'] ?? ''); ?>" 
                           class="w-full border rounded px-3 py-2"
                           placeholder="Enter your partner ID (optional)">
                    <p class="text-xs text-gray-500 mt-1">Partner ID for TextSMS gateway (if provided by your SMS provider)</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">API URL</label>
                    <input type="text" name="sms_api_url" 
                           value="<?php echo htmlspecialchars($sms_settings['sms_api_url'] ?? 'https://sms.textsms.co.ke'); ?>" 
                           class="w-full border rounded px-3 py-2"
                           placeholder="https://sms.textsms.co.ke">
                    <p class="text-xs text-gray-500 mt-1">SMS API base URL (e.g., https://sms.textsms.co.ke). The system will automatically append the endpoint: /api/services/sendsms/</p>
                    <p class="text-xs text-blue-600 mt-1">ℹ️ Default endpoint: <code class="bg-gray-100 px-1 rounded">/api/services/sendsms/</code> (Official TextSMS.co.ke endpoint)</p>
                </div>
                
                <div class="flex space-x-2">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Save Settings
                    </button>
                    <button type="button" onclick="showTestSmsModal()" class="flex-1 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        <i class="fas fa-paper-plane mr-2"></i>Test SMS
                    </button>
                </div>
            </form>
            
            <div id="settingsResult" class="mt-4 hidden"></div>
        </div>
        
        <!-- Test SMS Modal -->
        <div id="testSmsModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold">Test SMS Sending</h3>
                        <button onclick="closeTestSmsModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <form id="testSmsForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Test Phone Number</label>
                            <input type="tel" name="test_phone" id="testPhone" 
                                   class="w-full border rounded px-3 py-2" 
                                   placeholder="+254700000000 or 0700000000"
                                   required>
                            <p class="text-xs text-gray-500 mt-1">Enter a phone number to receive the test SMS</p>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Test Message</label>
                            <textarea name="test_message" id="testMessage" rows="4" 
                                      class="w-full border rounded px-3 py-2" 
                                      placeholder="This is a test message from Masomo School Management System">This is a test SMS from <?php echo APP_NAME; ?>. Your SMS gateway is configured correctly!</textarea>
                            <p class="text-xs text-gray-500 mt-1">
                                <span id="testCharCount">0</span> characters | 
                                <span id="testSmsCount">0</span> SMS
                            </p>
                        </div>
                        
                        <div class="flex space-x-2">
                            <button type="button" onclick="closeTestSmsModal()" class="flex-1 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                                Cancel
                            </button>
                            <button type="submit" class="flex-1 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                <i class="fas fa-paper-plane mr-2"></i>Send Test SMS
                            </button>
                        </div>
                    </form>
                    
                    <div id="testSmsResult" class="mt-4 hidden"></div>
                </div>
            </div>
        </div>
        
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded mb-6">
            <h3 class="font-bold text-blue-800 mb-2">How to Get Started:</h3>
            <ol class="list-decimal list-inside text-sm text-blue-700 space-y-1">
                <li>Visit <a href="https://textsms.co.ke/" target="_blank" class="underline">TextSMS.co.ke</a></li>
                <li>Create an account or log in</li>
                <li>Register your Sender ID (e.g., "MASOMO")</li>
                <li>Get your API key from the dashboard</li>
                <li>Enter the credentials above and save</li>
            </ol>
        </div>
        
        <!-- WhatsApp Settings -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4">
                <i class="fab fa-whatsapp text-green-500 mr-2"></i>WhatsApp Configuration
            </h2>
            <p class="text-gray-600 mb-6">
                Configure your WhatsApp Business API settings. Supports WhatsApp Cloud API, Twilio, MessageBird, and Green API.
            </p>
            
            <form id="whatsappSettingsForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Provider</label>
                    <select name="whatsapp_provider" class="w-full border rounded px-3 py-2">
                        <option value="cloud_api" <?php echo ($whatsapp_settings['whatsapp_provider'] ?? 'cloud_api') === 'cloud_api' ? 'selected' : ''; ?>>WhatsApp Cloud API (Meta)</option>
                        <option value="business_api" <?php echo ($whatsapp_settings['whatsapp_provider'] ?? '') === 'business_api' ? 'selected' : ''; ?>>WhatsApp Business API</option>
                        <option value="twilio" <?php echo ($whatsapp_settings['whatsapp_provider'] ?? '') === 'twilio' ? 'selected' : ''; ?>>Twilio</option>
                        <option value="messagebird" <?php echo ($whatsapp_settings['whatsapp_provider'] ?? '') === 'messagebird' ? 'selected' : ''; ?>>MessageBird</option>
                        <option value="green_api" <?php echo ($whatsapp_settings['whatsapp_provider'] ?? '') === 'green_api' ? 'selected' : ''; ?>>Green API</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Select your WhatsApp API provider</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">API Key / Access Token</label>
                    <input type="text" name="whatsapp_api_key" 
                           value="<?php echo htmlspecialchars($whatsapp_settings['whatsapp_api_key'] ?? ''); ?>" 
                           class="w-full border rounded px-3 py-2"
                           placeholder="Enter your WhatsApp API key or access token">
                    <p class="text-xs text-gray-500 mt-1">For Cloud API: Access Token | For Twilio: Account SID | For Green API: Instance ID</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">API Secret (Optional)</label>
                    <input type="password" name="whatsapp_api_secret" 
                           value="<?php echo htmlspecialchars($whatsapp_settings['whatsapp_api_secret'] ?? ''); ?>" 
                           class="w-full border rounded px-3 py-2"
                           placeholder="Enter your API secret (for Twilio/Green API)">
                    <p class="text-xs text-gray-500 mt-1">Required for Twilio (Auth Token) and Green API (API Token)</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number ID</label>
                    <input type="text" name="whatsapp_phone_number_id" 
                           value="<?php echo htmlspecialchars($whatsapp_settings['whatsapp_phone_number_id'] ?? ''); ?>" 
                           class="w-full border rounded px-3 py-2"
                           placeholder="Phone Number ID (for Cloud API) or WhatsApp number (for Twilio)">
                    <p class="text-xs text-gray-500 mt-1">For Cloud API: Phone Number ID | For Twilio: WhatsApp number (format: whatsapp:+14155238886)</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Business Account ID (Optional)</label>
                    <input type="text" name="whatsapp_business_account_id" 
                           value="<?php echo htmlspecialchars($whatsapp_settings['whatsapp_business_account_id'] ?? ''); ?>" 
                           class="w-full border rounded px-3 py-2"
                           placeholder="Business Account ID">
                    <p class="text-xs text-gray-500 mt-1">Optional: Business Account ID for WhatsApp Cloud API</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">API URL (Optional)</label>
                    <input type="text" name="whatsapp_api_url" 
                           value="<?php echo htmlspecialchars($whatsapp_settings['whatsapp_api_url'] ?? ''); ?>" 
                           class="w-full border rounded px-3 py-2"
                           placeholder="Leave empty to use default API URL">
                    <p class="text-xs text-gray-500 mt-1">Optional: Custom API URL (defaults based on provider)</p>
                </div>
                
                <div class="flex space-x-2">
                    <button type="submit" class="flex-1 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        <i class="fas fa-save mr-2"></i>Save WhatsApp Settings
                    </button>
                    <button type="button" onclick="showTestWhatsAppModal()" class="flex-1 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        <i class="fab fa-whatsapp mr-2"></i>Test WhatsApp
                    </button>
                </div>
            </form>
            
            <div id="whatsappSettingsResult" class="mt-4 hidden"></div>
        </div>
        
        <!-- Test WhatsApp Modal -->
        <div id="testWhatsAppModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold">Test WhatsApp Sending</h3>
                        <button onclick="closeTestWhatsAppModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <form id="testWhatsAppForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Test Phone Number</label>
                            <input type="tel" name="test_phone" id="testWhatsAppPhone" 
                                   class="w-full border rounded px-3 py-2" 
                                   placeholder="+254700000000 or 0700000000"
                                   required>
                            <p class="text-xs text-gray-500 mt-1">Enter a phone number to receive the test WhatsApp message</p>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Test Message</label>
                            <textarea name="test_message" id="testWhatsAppMessage" rows="4" 
                                      class="w-full border rounded px-3 py-2" 
                                      placeholder="This is a test message from Masomo School Management System">This is a test WhatsApp message from <?php echo APP_NAME; ?>. Your WhatsApp gateway is configured correctly!</textarea>
                        </div>
                        
                        <div class="flex space-x-2">
                            <button type="button" onclick="closeTestWhatsAppModal()" class="flex-1 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                                Cancel
                            </button>
                            <button type="submit" class="flex-1 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                <i class="fab fa-whatsapp mr-2"></i>Send Test WhatsApp
                            </button>
                        </div>
                    </form>
                    
                    <div id="testWhatsAppResult" class="mt-4 hidden"></div>
                </div>
            </div>
        </div>
        
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
            <h3 class="font-bold text-green-800 mb-2">WhatsApp Setup Guides:</h3>
            <ul class="list-disc list-inside text-sm text-green-700 space-y-1">
                <li><strong>Cloud API:</strong> <a href="https://developers.facebook.com/docs/whatsapp/cloud-api" target="_blank" class="underline">Meta's WhatsApp Cloud API Documentation</a></li>
                <li><strong>Twilio:</strong> <a href="https://www.twilio.com/docs/whatsapp" target="_blank" class="underline">Twilio WhatsApp API Guide</a></li>
                <li><strong>MessageBird:</strong> <a href="https://developers.messagebird.com/api/conversations" target="_blank" class="underline">MessageBird Conversations API</a></li>
                <li><strong>Green API:</strong> <a href="https://green-api.com/en/docs/" target="_blank" class="underline">Green API Documentation</a></li>
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
        const response = await fetch('<?php echo BASE_URL; ?>/communication/saveSettings', {
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

// Test SMS Modal Functions
function showTestSmsModal() {
    document.getElementById('testSmsModal').classList.remove('hidden');
    // Update character count
    document.getElementById('testMessage').dispatchEvent(new Event('input'));
}

function closeTestSmsModal() {
    document.getElementById('testSmsModal').classList.add('hidden');
    document.getElementById('testSmsForm').reset();
    document.getElementById('testSmsResult').classList.add('hidden');
    document.getElementById('testCharCount').textContent = '0';
    document.getElementById('testSmsCount').textContent = '0';
}

// Character count for test message
document.getElementById('testMessage').addEventListener('input', function() {
    const charCount = this.value.length;
    const smsCount = Math.ceil(charCount / 160);
    
    document.getElementById('testCharCount').textContent = charCount;
    document.getElementById('testSmsCount').textContent = smsCount;
});

// Test SMS Form Submission
document.getElementById('testSmsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const resultDiv = document.getElementById('testSmsResult');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
    resultDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/communication/testSms', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        // Check if response is OK and is JSON
        if (!response.ok) {
            throw new Error('HTTP ' + response.status + ': ' + response.statusText);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error('Server returned non-JSON response. Route may not exist. Response: ' + text.substring(0, 200));
        }
        
        const data = await response.json();
        
        if (data.success) {
            let successMsg = '<strong>Success!</strong> Test SMS request was accepted by the API. Please check your phone for the message.<br><br>';
            
            // Show API response details even on success
            if (data.debug) {
                successMsg += '<strong class="text-sm">API Response Details:</strong>';
                successMsg += '<br><small class="text-xs mt-1 block font-mono bg-green-50 p-2 rounded overflow-auto max-h-40">' + data.debug.replace(/\|/g, '<br>') + '</small>';
            }
            
            if (data.full_response) {
                successMsg += '<br><small class="text-xs mt-1 block font-mono bg-gray-50 p-2 rounded overflow-auto max-h-40">Raw API Response: ' + (typeof data.full_response === 'string' ? data.full_response : JSON.stringify(data.full_response, null, 2)) + '</small>';
            }
            
            successMsg += '<br><small class="text-xs mt-2 block text-gray-600">If you did not receive the SMS, check: 1) Phone number format (should be 254XXXXXXXXX), 2) SMS balance in your TextSMS account, 3) Sender ID registration status, 4) API endpoint URL is correct (check TextSMS dashboard).</small>';
            
            // Check if response contains HTML (indicates wrong endpoint) - override success message
            if (data.error_type === 'invalid_endpoint' || (data.full_response && (data.full_response.includes('<!doctype') || data.full_response.includes('<html') || data.full_response.includes('Page Not Found')))) {
                successMsg = '<strong class="text-red-600">⚠️ API Endpoint Error!</strong><br>The SMS gateway returned an HTML 404 page instead of JSON. This means the API endpoint URL is incorrect.<br><br>';
                
                if (data.suggested_urls && data.suggested_urls.length > 0) {
                    successMsg += '<strong>Try these alternative endpoint URLs:</strong><br>';
                    successMsg += '<ul class="list-disc list-inside mt-2 mb-2">';
                    data.suggested_urls.forEach(function(url) {
                        successMsg += '<li class="font-mono text-sm">' + url + '</li>';
                    });
                    successMsg += '</ul>';
                }
                
                successMsg += '<strong>What to do:</strong><br>';
                successMsg += '1. Try one of the suggested URLs above, or<br>';
                successMsg += '2. Log into your TextSMS.co.ke dashboard<br>';
                successMsg += '3. Check the API documentation or settings page<br>';
                successMsg += '4. Find the correct API endpoint URL<br>';
                successMsg += '5. Update the API URL in the settings above and test again<br><br>';
                successMsg += '<small class="text-xs block font-mono bg-red-50 p-2 rounded overflow-auto max-h-40">Current URL: ' + (data.current_url || 'N/A') + '<br>API Response: ' + (typeof data.full_response === 'string' ? data.full_response.substring(0, 300) : JSON.stringify(data.full_response, null, 2)) + '</small>';
                resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            } else {
                resultDiv.className = 'mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded';
            }
            
            resultDiv.innerHTML = successMsg;
            resultDiv.classList.remove('hidden');
        } else {
            let errorMsg = '<strong>Error:</strong> ' + (data.message || 'Failed to send test SMS');
            
            // Show detailed debugging information
            if (data.debug) {
                errorMsg += '<br><br><strong class="text-sm">Debug Information:</strong>';
                errorMsg += '<br><small class="text-xs mt-1 block font-mono bg-red-50 p-2 rounded">' + data.debug.replace(/\|/g, '<br>') + '</small>';
            }
            
            if (data.http_code) {
                errorMsg += '<br><small class="text-xs mt-1">HTTP Status: ' + data.http_code + '</small>';
            }
            
            if (data.curl_error) {
                errorMsg += '<br><small class="text-xs mt-1">Connection Error: ' + data.curl_error + '</small>';
            }
            
            if (data.response) {
                const responseText = typeof data.response === 'string' ? data.response.substring(0, 200) : JSON.stringify(data.response).substring(0, 200);
                errorMsg += '<br><small class="text-xs mt-1">Raw Response: ' + responseText + '</small>';
            }
            
            resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            resultDiv.innerHTML = errorMsg;
            resultDiv.classList.remove('hidden');
        }
    } catch (error) {
        resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded';
        let errorMsg = '<strong>Error:</strong> ' + error.message;
        if (error.message.includes('404') || error.message.includes('Route may not exist')) {
            errorMsg += '<br><small class="text-xs mt-2 block">The test SMS route was not found. Please check that the CommunicationController has a testSms() method.</small>';
        }
        resultDiv.innerHTML = errorMsg;
        resultDiv.classList.remove('hidden');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Send Test SMS';
    }
});

// WhatsApp Settings Form
document.getElementById('whatsappSettingsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const resultDiv = document.getElementById('whatsappSettingsResult');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    resultDiv.classList.add('hidden');
    
    // Merge with SMS settings form data to ensure both are saved together
    const smsFormData = new FormData(document.getElementById('settingsForm'));
    for (let [key, value] of smsFormData.entries()) {
        // Only add if not already in WhatsApp form data
        if (!formData.has(key)) {
            formData.append(key, value);
        }
    }
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/communication/saveSettings', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultDiv.className = 'mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded';
            resultDiv.innerHTML = '<strong>Success!</strong> WhatsApp settings saved successfully.';
            resultDiv.classList.remove('hidden');
            
            // Reload page after 1 second to show updated values
            setTimeout(() => {
                window.location.reload();
            }, 1000);
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
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save WhatsApp Settings';
    }
});

// Test WhatsApp Modal Functions
function showTestWhatsAppModal() {
    document.getElementById('testWhatsAppModal').classList.remove('hidden');
}

function closeTestWhatsAppModal() {
    document.getElementById('testWhatsAppModal').classList.add('hidden');
    document.getElementById('testWhatsAppForm').reset();
    document.getElementById('testWhatsAppResult').classList.add('hidden');
}

// Test WhatsApp Form Submission
document.getElementById('testWhatsAppForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const resultDiv = document.getElementById('testWhatsAppResult');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
    resultDiv.classList.add('hidden');
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/communication/testWhatsApp', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        resultDiv.classList.remove('hidden');
        
        if (data.success) {
            let successMsg = '<strong>Success!</strong> Test WhatsApp message sent. Please check your phone.<br><br>';
            
            // Show API response details even on success
            if (data.debug) {
                successMsg += '<strong class="text-sm">API Response Details:</strong>';
                successMsg += '<br><small class="text-xs mt-1 block font-mono bg-green-50 p-2 rounded overflow-auto max-h-40">' + data.debug.replace(/\|/g, '<br>') + '</small>';
            }
            
            if (data.full_response) {
                successMsg += '<br><small class="text-xs mt-1 block font-mono bg-gray-50 p-2 rounded overflow-auto max-h-40">Raw API Response: ' + (typeof data.full_response === 'string' ? data.full_response : JSON.stringify(data.full_response, null, 2)) + '</small>';
            }
            
            if (data.message_id) {
                successMsg += '<br><small class="text-xs mt-1 text-green-600">Message ID: ' + data.message_id + '</small>';
            }
            
            successMsg += '<br><small class="text-xs mt-2 block text-gray-600">If you did not receive the message, check: 1) Phone number format (should be 254XXXXXXXXX), 2) Green API instance is authorized (linked to WhatsApp), 3) Phone number is registered on WhatsApp, 4) Check Green API dashboard for delivery status.</small>';
            
            resultDiv.className = 'p-4 bg-green-100 border border-green-400 text-green-700 rounded';
            resultDiv.innerHTML = successMsg;
        } else {
            let errorMsg = '<strong>Error:</strong> ' + (data.message || 'Failed to send test WhatsApp message');
            
            // Show detailed debugging information
            if (data.debug) {
                errorMsg += '<br><br><strong class="text-sm">Debug Information:</strong>';
                errorMsg += '<br><small class="text-xs mt-1 block font-mono bg-red-50 p-2 rounded">' + data.debug.replace(/\|/g, '<br>') + '</small>';
            }
            
            if (data.http_code) {
                errorMsg += '<br><small class="text-xs mt-1">HTTP Status: ' + data.http_code + '</small>';
            }
            
            if (data.full_response) {
                const responseText = typeof data.full_response === 'string' ? data.full_response.substring(0, 500) : JSON.stringify(data.full_response, null, 2).substring(0, 500);
                errorMsg += '<br><small class="text-xs mt-1 block font-mono bg-red-50 p-2 rounded">Raw Response: ' + responseText + '</small>';
            }
            
            // Common Green API errors
            if (data.message && data.message.includes('Not Authorized')) {
                errorMsg += '<br><br><strong class="text-sm text-red-600">⚠️ Your Green API instance is not authorized!</strong>';
                errorMsg += '<br><small class="text-xs mt-1">Go to your Green API dashboard and link your WhatsApp account using "Link with QR code" or "Link with phone number".</small>';
            }
            
            resultDiv.className = 'p-4 bg-red-100 border border-red-400 text-red-700 rounded';
            resultDiv.innerHTML = errorMsg;
        }
    } catch (error) {
        resultDiv.classList.remove('hidden');
        resultDiv.className = 'p-4 bg-red-100 border border-red-400 text-red-700 rounded';
        resultDiv.innerHTML = '<strong>Error:</strong> ' + error.message;
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});
</script>

