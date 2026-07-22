<?php
/**
 * Announcement Controller
 * Handles announcements management
 */

class AnnouncementController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
    }
    
    /**
     * List all announcements
     */
    public function index() {
        $announcementModel = $this->model('Announcement');
        $user = Auth::user();
        
        // Get announcements based on user role
        if (Auth::hasAnyRole(['super_admin', 'school_admin'])) {
            // Admins see all announcements
            $announcements = $announcementModel->findAll([], 'created_at DESC');
        } else {
            // Others see only published announcements for their audience
            $announcements = $announcementModel->getForUser($user['id'], $user['role_name']);
        }
        
        $data = [
            'title' => 'Announcements - ' . APP_NAME,
            'announcements' => $announcements,
            'canManage' => Auth::hasAnyRole(['super_admin', 'school_admin', 'head_teacher'])
        ];
        
        $this->view('announcements/index', $data);
    }
    
    /**
     * Show announcement details
     */
    public function show($id) {
        $announcementModel = $this->model('Announcement');
        $announcement = $announcementModel->findById($id);
        
        if (!$announcement) {
            $this->setFlash('error', 'Announcement not found');
            $this->redirect('/announcements');
            return;
        }
        
        // Check if user can view (must be published or user has manage permission)
        if ($announcement['status'] !== 'published' && !Auth::hasAnyRole(['super_admin', 'school_admin', 'head_teacher'])) {
            $this->setFlash('error', 'Access denied');
            $this->redirect('/announcements');
            return;
        }
        
        $data = [
            'title' => htmlspecialchars($announcement['title']) . ' - ' . APP_NAME,
            'announcement' => $announcement
        ];
        
        $this->view('announcements/show', $data);
    }
    
    /**
     * Show create form
     */
    public function create() {
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'head_teacher'])) {
            http_response_code(403);
            die("Access denied");
        }
        
        $data = [
            'title' => 'Create Announcement - ' . APP_NAME,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('announcements/create', $data);
    }
    
    /**
     * Store new announcement
     */
    public function store() {
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'head_teacher'])) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $announcementModel = $this->model('Announcement');
        
        // Validate input
        $errors = [];
        if (empty($_POST['title'])) {
            $errors['title'] = 'Title is required';
        }
        if (empty($_POST['content'])) {
            $errors['content'] = 'Content is required';
        }
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
            return;
        }
        
        $data = [
            'title' => sanitize($_POST['title']),
            'content' => sanitize($_POST['content']),
            'target_audience' => sanitize($_POST['target_audience'] ?? 'all'),
            'priority' => sanitize($_POST['priority'] ?? 'normal'),
            'status' => sanitize($_POST['status'] ?? 'draft'),
            'created_by' => Auth::userId()
        ];
        
        // Set published_at if status is published
        if ($data['status'] === 'published') {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        
        $id = $announcementModel->create($data);
        
        if ($id) {
            $this->json([
                'success' => true,
                'message' => 'Announcement created successfully',
                'redirect' => BASE_URL . '/announcements/show/' . $id
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to create announcement']);
        }
    }
    
    /**
     * Show edit form
     */
    public function edit($id) {
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'head_teacher'])) {
            http_response_code(403);
            die("Access denied");
        }
        
        $announcementModel = $this->model('Announcement');
        $announcement = $announcementModel->findById($id);
        
        if (!$announcement) {
            $this->setFlash('error', 'Announcement not found');
            $this->redirect('/announcements');
            return;
        }
        
        $data = [
            'title' => 'Edit Announcement - ' . APP_NAME,
            'announcement' => $announcement,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('announcements/edit', $data);
    }
    
    /**
     * Update announcement
     */
    public function update($id) {
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'head_teacher'])) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $announcementModel = $this->model('Announcement');
        
        $data = [
            'title' => sanitize($_POST['title']),
            'content' => sanitize($_POST['content']),
            'target_audience' => sanitize($_POST['target_audience'] ?? 'all'),
            'priority' => sanitize($_POST['priority'] ?? 'normal'),
            'status' => sanitize($_POST['status'] ?? 'draft')
        ];
        
        // Set published_at if status changed to published
        $current = $announcementModel->findById($id);
        if ($current['status'] !== 'published' && $data['status'] === 'published') {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        
        if ($announcementModel->update($id, $data)) {
            $this->json([
                'success' => true,
                'message' => 'Announcement updated successfully',
                'redirect' => BASE_URL . '/announcements/show/' . $id
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to update announcement']);
        }
    }
    
    /**
     * Delete announcement
     */
    public function delete($id) {
        if (!Auth::hasAnyRole(['super_admin', 'school_admin'])) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        $announcementModel = $this->model('Announcement');
        
        // Soft delete - set status to archived
        if ($announcementModel->update($id, ['status' => 'archived'])) {
            $this->json(['success' => true, 'message' => 'Announcement deleted successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to delete announcement']);
        }
    }
}

