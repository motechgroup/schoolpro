<?php
/**
 * Email Template Controller
 * Manages email templates (Super Admin only)
 */

class EmailTemplateController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * List all email templates
     */
    public function index() {
        $emailTemplateModel = $this->model('EmailTemplate');
        $templates = $emailTemplateModel->getActiveTemplates();
        
        $data = [
            'title' => 'Email Templates - ' . APP_NAME,
            'templates' => $templates,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('email_templates/index', $data);
    }
    
    /**
     * Create new template
     */
    public function create() {
        $data = [
            'title' => 'Create Email Template - ' . APP_NAME,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('email_templates/create', $data);
    }
    
    /**
     * Store new template
     */
    public function store() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $emailTemplateModel = $this->model('EmailTemplate');
        
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'subject' => sanitize($_POST['subject'] ?? ''),
            'body' => $_POST['body'] ?? '',
            'variables' => json_encode($_POST['variables'] ?? []),
            'category' => sanitize($_POST['category'] ?? 'general'),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        if (empty($data['name']) || empty($data['subject']) || empty($data['body'])) {
            $this->json(['success' => false, 'message' => 'Name, subject, and body are required']);
            return;
        }
        
        if ($emailTemplateModel->create($data)) {
            $this->json([
                'success' => true,
                'message' => 'Email template created successfully',
                'redirect' => BASE_URL . '/emailtemplates'
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to create template']);
        }
    }
    
    /**
     * Edit template
     */
    public function edit($id) {
        $emailTemplateModel = $this->model('EmailTemplate');
        $template = $emailTemplateModel->findById($id);
        
        if (!$template) {
            $this->setFlash('error', 'Template not found');
            $this->redirect('/emailtemplates');
            return;
        }
        
        // Decode variables
        if (!empty($template['variables'])) {
            $template['variables'] = json_decode($template['variables'], true);
        }
        
        $data = [
            'title' => 'Edit Email Template - ' . APP_NAME,
            'template' => $template,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('email_templates/edit', $data);
    }
    
    /**
     * Update template
     */
    public function update($id) {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $emailTemplateModel = $this->model('EmailTemplate');
        $template = $emailTemplateModel->findById($id);
        
        if (!$template) {
            $this->json(['success' => false, 'message' => 'Template not found']);
            return;
        }
        
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'subject' => sanitize($_POST['subject'] ?? ''),
            'body' => $_POST['body'] ?? '',
            'variables' => json_encode($_POST['variables'] ?? []),
            'category' => sanitize($_POST['category'] ?? 'general'),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        if (empty($data['name']) || empty($data['subject']) || empty($data['body'])) {
            $this->json(['success' => false, 'message' => 'Name, subject, and body are required']);
            return;
        }
        
        if ($emailTemplateModel->update($id, $data)) {
            $this->json([
                'success' => true,
                'message' => 'Email template updated successfully',
                'redirect' => BASE_URL . '/emailtemplates'
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to update template']);
        }
    }
    
    /**
     * Delete template
     */
    public function delete($id) {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $emailTemplateModel = $this->model('EmailTemplate');
        
        // Soft delete (set is_active to 0)
        if ($emailTemplateModel->update($id, ['is_active' => 0])) {
            $this->json([
                'success' => true,
                'message' => 'Template deleted successfully'
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to delete template']);
        }
    }
}

