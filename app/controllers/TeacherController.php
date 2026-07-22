<?php
/**
 * Teacher Controller
 * Handles teacher management operations
 */

class TeacherController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'head_teacher'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * List all teachers
     */
    public function index() {
        $teacherModel = $this->model('Teacher');
        $teachers = $teacherModel->getAllWithDetails();
        
        $data = [
            'title' => 'Teacher Management - ' . APP_NAME,
            'teachers' => $teachers
        ];
        
        $this->view('teachers/index', $data);
    }
    
    /**
     * Show teacher details
     */
    public function show($id) {
        $teacherModel = $this->model('Teacher');
        
        $teacher = $teacherModel->getTeacherWithDetails($id);
        
        if (!$teacher) {
            $this->setFlash('error', 'Teacher not found');
            $this->redirect('/teachers');
            return;
        }
        
        // Get classes assigned to this teacher
        $assignedClasses = $teacherModel->getAssignedClasses($id);
        
        $data = [
            'title' => 'Teacher Details - ' . APP_NAME,
            'teacher' => $teacher,
            'assignedClasses' => $assignedClasses
        ];
        
        $this->view('teachers/show', $data);
    }
    
    /**
     * Show create form
     */
    public function create() {
        $roleModel = $this->model('Role');
        $db = Database::getInstance()->getConnection();
        
        // Get teacher role
        $stmt = $db->query("SELECT * FROM roles WHERE name = 'teacher' LIMIT 1");
        $teacherRole = $stmt->fetch();
        
        // Get existing users who don't have teacher records yet
        // This allows school managers and other users to also be teachers
        // Exclude users who already have teacher profiles
        $stmt = $db->query("SELECT DISTINCT u.id, u.email, u.first_name, u.last_name, u.phone, r.name as role_name
                           FROM users u
                           LEFT JOIN roles r ON u.role_id = r.id
                           LEFT JOIN teachers t ON u.id = t.user_id
                           WHERE u.status = 'active' 
                           AND (t.id IS NULL OR t.id = '')
                           AND u.id IS NOT NULL
                           ORDER BY u.first_name, u.last_name");
        $existingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: Log if no users found
        if (empty($existingUsers)) {
            error_log("No existing users found without teacher profiles. Total users: " . 
                $db->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'")->fetch()['count']);
        }
        
        $data = [
            'title' => 'Create Teacher - ' . APP_NAME,
            'teacherRoleId' => $teacherRole['id'] ?? null,
            'existingUsers' => $existingUsers,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('teachers/create', $data);
    }
    
    /**
     * Store new teacher
     */
    public function store() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $db = Database::getInstance()->getConnection();
        
        // Check if using existing user or creating new one
        $useExistingUser = !empty($_POST['user_id']) && intval($_POST['user_id']) > 0;
        $userId = null;
        
        // Validate input
        $errors = [];
        
        if ($useExistingUser) {
            // Using existing user - validate user_id
            $userId = intval($_POST['user_id']);
            
            // Check if user exists
            $stmt = $db->prepare("SELECT u.*, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ? AND u.status = 'active'");
            $stmt->execute([$userId]);
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existingUser) {
                $this->json(['success' => false, 'message' => 'Selected user not found or inactive']);
                return;
            }
            
            // Check if user already has a teacher record
            $stmt = $db->prepare("SELECT id FROM teachers WHERE user_id = ?");
            $stmt->execute([$userId]);
            if ($stmt->fetch()) {
                $this->json(['success' => false, 'message' => 'This user already has a teacher profile']);
                return;
            }
            
            // Use existing user data
            $first_name = $existingUser['first_name'];
            $last_name = $existingUser['last_name'];
            $email = $existingUser['email'];
            $phone = $existingUser['phone'] ?? null;
        } else {
            // Creating new user - validate all fields
            if (empty($_POST['first_name'])) {
                $errors['first_name'] = 'First name is required';
            }
            if (empty($_POST['last_name'])) {
                $errors['last_name'] = 'Last name is required';
            }
            if (empty($_POST['email'])) {
                $errors['email'] = 'Email is required';
            } elseif (!isValidEmail($_POST['email'])) {
                $errors['email'] = 'Invalid email format';
            }
            if (empty($_POST['password'])) {
                $errors['password'] = 'Password is required';
            } elseif (strlen($_POST['password']) < PASSWORD_MIN_LENGTH) {
                $errors['password'] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
            }
            
            if (!empty($errors)) {
                $this->json(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
                return;
            }
            
            // Check if email already exists
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$_POST['email']]);
            if ($stmt->fetch()) {
                $this->json(['success' => false, 'message' => 'Email already exists. You can select this user from "Add from Existing User" option.']);
                return;
            }
            
            // Get teacher role
            $stmt = $db->query("SELECT id FROM roles WHERE name = 'teacher' LIMIT 1");
            $teacherRole = $stmt->fetch();
            
            if (!$teacherRole) {
                $this->json(['success' => false, 'message' => 'Teacher role not found']);
                return;
            }
            
            $first_name = sanitize($_POST['first_name']);
            $last_name = sanitize($_POST['last_name']);
            $email = sanitize($_POST['email']);
            $phone = !empty($_POST['phone']) ? formatPhone(sanitize($_POST['phone'])) : null;
        }
        
        // Start transaction
        $db->beginTransaction();
        
        try {
            if (!$useExistingUser) {
                // Create new user account with teacher role
                $userData = [
                    'role_id' => $teacherRole['id'],
                    'email' => $email,
                    'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'phone' => $phone,
                    'status' => 'active'
                ];
                
                $userStmt = $db->prepare("INSERT INTO users (role_id, email, password, first_name, last_name, phone, status) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?)");
                $userStmt->execute([
                    $userData['role_id'],
                    $userData['email'],
                    $userData['password'],
                    $userData['first_name'],
                    $userData['last_name'],
                    $userData['phone'],
                    $userData['status']
                ]);
                
                $userId = $db->lastInsertId();
            }
            
            // Handle photo upload
            $photoPath = null;
            if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
                $photoPath = $this->uploadTeacherPhoto($_FILES['photo'], $email);
                if (!$photoPath) {
                    $db->rollBack();
                    $this->json(['success' => false, 'message' => 'Failed to upload photo. Please ensure it is a valid image file (JPEG, PNG, GIF) and less than 5MB.']);
                    return;
                }
            }
            
            // Create teacher profile
            // Use provided phone or existing user's phone
            $teacherPhone = !empty($_POST['phone']) ? formatPhone(sanitize($_POST['phone'])) : $phone;
            
            $teacherData = [
                'user_id' => $userId,
                'tsc_number' => !empty($_POST['tsc_number']) ? sanitize($_POST['tsc_number']) : null,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone' => $teacherPhone,
                'email' => $email,
                'qualification' => !empty($_POST['qualification']) ? sanitize($_POST['qualification']) : null,
                'specialization' => !empty($_POST['specialization']) ? sanitize($_POST['specialization']) : null,
                'employment_date' => !empty($_POST['employment_date']) ? sanitize($_POST['employment_date']) : date('Y-m-d'),
                'photo' => $photoPath,
                'status' => 'active'
            ];
            
            $teacherStmt = $db->prepare("INSERT INTO teachers 
                                        (user_id, tsc_number, first_name, last_name, phone, email, qualification, specialization, employment_date, photo, status) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $teacherStmt->execute([
                $teacherData['user_id'],
                $teacherData['tsc_number'],
                $teacherData['first_name'],
                $teacherData['last_name'],
                $teacherData['phone'],
                $teacherData['email'],
                $teacherData['qualification'],
                $teacherData['specialization'],
                $teacherData['employment_date'],
                $teacherData['photo'],
                $teacherData['status']
            ]);
            
            $teacherId = $db->lastInsertId();
            
            $db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'Teacher created successfully',
                'redirect' => BASE_URL . '/teachers/show/' . $teacherId
            ]);
        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'message' => 'Failed to create teacher: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Show edit form
     */
    public function edit($id) {
        $teacherModel = $this->model('Teacher');
        $teacher = $teacherModel->getTeacherWithDetails($id);
        
        if (!$teacher) {
            $this->setFlash('error', 'Teacher not found');
            $this->redirect('/teachers');
            return;
        }
        
        $data = [
            'title' => 'Edit Teacher - ' . APP_NAME,
            'teacher' => $teacher,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('teachers/edit', $data);
    }
    
    /**
     * Update teacher
     */
    public function update($id) {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $teacherModel = $this->model('Teacher');
        $teacher = $teacherModel->findById($id);
        
        if (!$teacher) {
            $this->json(['success' => false, 'message' => 'Teacher not found']);
            return;
        }
        
        $db = Database::getInstance()->getConnection();
        
        // Handle photo upload
        $photoPath = $teacher['photo'] ?? null; // Keep existing photo
        
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
            // Delete old photo if exists
            if ($photoPath && file_exists(UPLOAD_DIR . $photoPath)) {
                @unlink(UPLOAD_DIR . $photoPath);
            }
            
            $newPhotoPath = $this->uploadTeacherPhoto($_FILES['photo'], $teacher['email']);
            if ($newPhotoPath) {
                $photoPath = $newPhotoPath;
            } else {
                $this->json(['success' => false, 'message' => 'Failed to upload photo. Please ensure it is a valid image file (JPEG, PNG, GIF) and less than 5MB.']);
                return;
            }
        }
        
        // Update teacher profile
        $teacherData = [
            'tsc_number' => !empty($_POST['tsc_number']) ? sanitize($_POST['tsc_number']) : null,
            'first_name' => sanitize($_POST['first_name']),
            'last_name' => sanitize($_POST['last_name']),
            'phone' => !empty($_POST['phone']) ? formatPhone(sanitize($_POST['phone'])) : null,
            'email' => sanitize($_POST['email']),
            'qualification' => !empty($_POST['qualification']) ? sanitize($_POST['qualification']) : null,
            'specialization' => !empty($_POST['specialization']) ? sanitize($_POST['specialization']) : null,
            'employment_date' => !empty($_POST['employment_date']) ? sanitize($_POST['employment_date']) : null,
            'photo' => $photoPath,
            'status' => sanitize($_POST['status'] ?? 'active')
        ];
        
        // Update user account
        $userData = [
            'first_name' => sanitize($_POST['first_name']),
            'last_name' => sanitize($_POST['last_name']),
            'phone' => !empty($_POST['phone']) ? formatPhone(sanitize($_POST['phone'])) : null,
            'email' => sanitize($_POST['email']),
            'status' => sanitize($_POST['status'] ?? 'active')
        ];
        
        // Check if email changed and if new email exists
        if ($teacher['email'] !== $userData['email']) {
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$userData['email'], $teacher['user_id']]);
            if ($stmt->fetch()) {
                $this->json(['success' => false, 'message' => 'Email already exists']);
                return;
            }
        }
        
        // Update password if provided
        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < PASSWORD_MIN_LENGTH) {
                $this->json(['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters']);
                return;
            }
            $userData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        
        try {
            $db->beginTransaction();
            
            // Update teacher
            $teacherStmt = $db->prepare("UPDATE teachers SET 
                                         tsc_number = ?, first_name = ?, last_name = ?, phone = ?, 
                                         email = ?, qualification = ?, specialization = ?, 
                                         employment_date = ?, photo = ?, status = ? 
                                         WHERE id = ?");
            $teacherStmt->execute([
                $teacherData['tsc_number'],
                $teacherData['first_name'],
                $teacherData['last_name'],
                $teacherData['phone'],
                $teacherData['email'],
                $teacherData['qualification'],
                $teacherData['specialization'],
                $teacherData['employment_date'],
                $teacherData['photo'],
                $teacherData['status'],
                $id
            ]);
            
            // Update user
            if (isset($userData['password'])) {
                $userStmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, email = ?, password = ?, status = ? WHERE id = ?");
                $userStmt->execute([
                    $userData['first_name'],
                    $userData['last_name'],
                    $userData['phone'],
                    $userData['email'],
                    $userData['password'],
                    $userData['status'],
                    $teacher['user_id']
                ]);
            } else {
                $userStmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, email = ?, status = ? WHERE id = ?");
                $userStmt->execute([
                    $userData['first_name'],
                    $userData['last_name'],
                    $userData['phone'],
                    $userData['email'],
                    $userData['status'],
                    $teacher['user_id']
                ]);
            }
            
            $db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'Teacher updated successfully',
                'redirect' => BASE_URL . '/teachers/show/' . $id
            ]);
        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'message' => 'Failed to update teacher: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Delete teacher (soft delete)
     */
    public function delete($id) {
        $teacherModel = $this->model('Teacher');
        
        $teacher = $teacherModel->findById($id);
        
        if (!$teacher) {
            $this->json(['success' => false, 'message' => 'Teacher not found']);
            return;
        }
        
        // Check if teacher is assigned to any classes
        $assignedClasses = $teacherModel->getAssignedClasses($id);
        
        if (!empty($assignedClasses)) {
            $this->json(['success' => false, 'message' => 'Cannot delete teacher assigned to classes. Please reassign classes first.']);
            return;
        }
        
        $db = Database::getInstance()->getConnection();
        
        try {
            $db->beginTransaction();
            
            // Soft delete teacher
            $teacherStmt = $db->prepare("UPDATE teachers SET status = 'inactive' WHERE id = ?");
            $teacherStmt->execute([$id]);
            
            // Soft delete user account
            $userStmt = $db->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
            $userStmt->execute([$teacher['user_id']]);
            
            $db->commit();
            
            $this->json(['success' => true, 'message' => 'Teacher deleted successfully']);
        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'message' => 'Failed to delete teacher']);
        }
    }
    
    /**
     * Assign teacher to class
     */
    public function assignClass() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $teacherId = intval($_POST['teacher_id'] ?? 0);
        $classId = intval($_POST['class_id'] ?? 0);
        
        if (empty($teacherId) || empty($classId)) {
            $this->json(['success' => false, 'message' => 'Teacher and class are required']);
            return;
        }
        
        $classModel = $this->model('ClassModel');
        
        if ($classModel->update($classId, ['class_teacher_id' => $teacherId])) {
            $this->json([
                'success' => true,
                'message' => 'Teacher assigned to class successfully'
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to assign teacher']);
        }
    }
    
    /**
     * Upload teacher photo
     */
    private function uploadTeacherPhoto($file, $email) {
        // Validate file
        if ($file['error'] != UPLOAD_ERR_OK) {
            return false;
        }
        
        // Check file size (5MB max)
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            return false;
        }
        
        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
            return false;
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'teacher_' . preg_replace('/[^a-zA-Z0-9]/', '_', $email) . '_' . time() . '.' . $extension;
        $uploadPath = UPLOAD_DIR . $filename;
        
        // Create uploads directory if it doesn't exist
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Resize image to standard size (optional - for consistency)
            $this->resizeImage($uploadPath, 400, 400);
            return $filename;
        }
        
        return false;
    }
    
    /**
     * Resize image to specified dimensions
     */
    private function resizeImage($filePath, $maxWidth, $maxHeight) {
        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) {
            return false;
        }
        
        $mimeType = $imageInfo['mime'];
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        // Calculate new dimensions maintaining aspect ratio
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);
        
        // Only resize if image is larger than max dimensions
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return true;
        }
        
        // Create image resource based on type
        switch ($mimeType) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($filePath);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($filePath);
                break;
            default:
                return false;
        }
        
        if (!$source) {
            return false;
        }
        
        // Create new image
        $destination = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($mimeType == 'image/png' || $mimeType == 'image/gif') {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
            imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Resize image
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Save resized image
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($destination, $filePath, 85);
                break;
            case 'image/png':
                imagepng($destination, $filePath, 8);
                break;
            case 'image/gif':
                imagegif($destination, $filePath);
                break;
        }
        
        // Free memory
        imagedestroy($source);
        imagedestroy($destination);
        
        return true;
    }
}

