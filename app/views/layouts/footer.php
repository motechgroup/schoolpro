    <?php if ($isLoggedIn): ?>
            </main>
            
            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 py-4 mt-auto">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="text-center text-sm text-gray-600">
                        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
                        <p class="text-xs text-gray-500 mt-1">CBC-Compliant School Management System for Kenyan Primary Schools</p>
                    </div>
                </div>
            </footer>
        </div>
    <?php endif; ?>
    
    <script>
        // Auto-hide flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                }, 5000);
            });
            
            // Initialize notifications if container exists
            const notificationContainer = document.getElementById('notificationContainer');
            if (notificationContainer) {
                initNotifications();
            }
        });
        
        // Notification System
        function initNotifications() {
            const bell = document.getElementById('notificationBell');
            const dropdown = document.getElementById('notificationDropdown');
            const badge = document.getElementById('notificationBadge');
            const list = document.getElementById('notificationList');
            const empty = document.getElementById('notificationEmpty');
            const markAllBtn = document.getElementById('markAllReadBtn');
            
            // Toggle dropdown
            bell.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.classList.toggle('hidden');
                if (!dropdown.classList.contains('hidden')) {
                    loadNotifications();
                }
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!notificationContainer.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
            
            // Mark all as read
            if (markAllBtn) {
                markAllBtn.addEventListener('click', function() {
                    markAllNotificationsAsRead();
                });
            }
            
            // Load notifications on page load
            loadNotifications();
            
            // Refresh notifications every 30 seconds
            setInterval(loadNotifications, 30000);
        }
        
        function loadNotifications() {
            const list = document.getElementById('notificationList');
            const empty = document.getElementById('notificationEmpty');
            const badge = document.getElementById('notificationBadge');
            
            fetch('<?php echo BASE_URL; ?>/notification/getNotifications')
                .then(response => response.json())
                .then(data => {
                    if (data.notifications && data.notifications.length > 0) {
                        list.innerHTML = '';
                        data.notifications.forEach(notif => {
                            const item = createNotificationItem(notif);
                            list.appendChild(item);
                        });
                        list.classList.remove('hidden');
                        empty.classList.add('hidden');
                    } else {
                        list.classList.add('hidden');
                        empty.classList.remove('hidden');
                    }
                    
                    // Update badge
                    if (data.unread_count > 0) {
                        badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                });
        }
        
        function createNotificationItem(notif) {
            const div = document.createElement('div');
            div.className = `p-4 hover:bg-gray-50 cursor-pointer ${notif.is_read ? 'bg-gray-50' : 'bg-white'}`;
            
            const iconClass = {
                'info': 'fa-info-circle text-blue-500',
                'warning': 'fa-exclamation-triangle text-yellow-500',
                'error': 'fa-times-circle text-red-500',
                'success': 'fa-check-circle text-green-500',
                'payment': 'fa-money-bill-wave text-green-600',
                'system_update': 'fa-sync-alt text-blue-600'
            }[notif.notification_type] || 'fa-bell text-gray-500';
            
            const timeAgo = getTimeAgo(notif.created_at);
            
            div.innerHTML = `
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <i class="fas ${iconClass} text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 ${notif.is_read ? '' : 'font-bold'}">${escapeHtml(notif.title)}</p>
                        <p class="text-sm text-gray-600 mt-1">${escapeHtml(notif.message)}</p>
                        <p class="text-xs text-gray-400 mt-1">${timeAgo}</p>
                    </div>
                    ${notif.is_read ? '' : '<div class="flex-shrink-0"><span class="h-2 w-2 bg-blue-500 rounded-full"></span></div>'}
                </div>
            `;
            
            div.addEventListener('click', function() {
                markNotificationAsRead(notif.id);
                if (notif.action_url) {
                    window.location.href = notif.action_url;
                }
            });
            
            return div;
        }
        
        function markNotificationAsRead(id) {
            fetch('<?php echo BASE_URL; ?>/notification/markAsRead/' + id, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
        }
        
        function markAllNotificationsAsRead() {
            fetch('<?php echo BASE_URL; ?>/notification/markAllAsRead', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                }
            })
            .catch(error => {
                console.error('Error marking all notifications as read:', error);
            });
        }
        
        function getTimeAgo(dateString) {
            const now = new Date();
            const date = new Date(dateString);
            const diff = Math.floor((now - date) / 1000);
            
            if (diff < 60) return 'Just now';
            if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
            if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
            if (diff < 604800) return Math.floor(diff / 86400) + ' days ago';
            return date.toLocaleDateString();
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>

