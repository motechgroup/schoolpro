<?php
/**
 * Student Controller
 * Handles student management operations
 */

class StudentController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        // Require appropriate permissions - teachers can only view, not manage
        // Accountant and bursar can view students for fee management
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'head_teacher', 'teacher', 'receptionist', 'accountant', 'bursar'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * List all students
     */
    public function index() {
        $studentModel = $this->model('Student');
        $classModel = $this->model('ClassModel');
        
        $filters = [
            'status' => $_GET['status'] ?? 'active',
            'class_id' => $_GET['class_id'] ?? null,
            'search' => $_GET['search'] ?? null,
            'fee_status' => $_GET['fee_status'] ?? null
        ];
        
        $students = $studentModel->getAllWithDetails($filters);
        $classes = $classModel->getAllWithDetails();
        
        $data = [
            'title' => 'Students - ' . APP_NAME,
            'students' => $students,
            'classes' => $classes,
            'filters' => $filters
        ];
        
        $this->view('students/index', $data);
    }
    
    /**
     * Show student details
     */
    public function show($id) {
        $studentModel = $this->model('Student');
        $invoiceModel = $this->model('Invoice');
        $attendanceModel = $this->model('Attendance');
        
        $student = $studentModel->getStudentWithDetails($id);
        
        if (!$student) {
            $this->setFlash('error', 'Student not found');
            $this->redirect('/students');
            return;
        }
        
        // Get multi-term fee summary (with carried-forward balances)
        $currentYear = getAcademicYearName();
        $termSummary = $invoiceModel->getStudentTermBalances($id, $currentYear);
        $invoices = $termSummary['invoices'] ?? [];
        
        // Get attendance records - last 30 days
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $endDate = date('Y-m-d');
        $attendanceRecords = [];
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM student_attendance 
                               WHERE student_id = ? AND attendance_date BETWEEN ? AND ? 
                               ORDER BY attendance_date DESC");
        $stmt->execute([$id, $startDate, $endDate]);
        $attendanceRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get attendance summary for current month
        $monthStart = date('Y-01-01');
        $monthEnd = date('Y-m-t');
        $attendanceSummary = $attendanceModel->getStudentAttendanceSummary($id, $monthStart, $monthEnd);
        
        $data = [
            'title' => 'Student Details - ' . APP_NAME,
            'student' => $student,
            'invoices' => $invoices,
            'termSummary' => $termSummary,
            'attendanceRecords' => $attendanceRecords,
            'attendanceSummary' => $attendanceSummary
        ];
        
        $this->view('students/show', $data);
    }
    
    /**
     * Show create student form
     */
    public function create() {
        // Restrict teachers from creating students
        if (Auth::hasAnyRole(['teacher'])) {
            http_response_code(403);
            die("Access denied. Teachers cannot create students.");
        }
        $gradeModel = $this->model('Grade');
        $classModel = $this->model('ClassModel');
        $parentModel = $this->model('ParentModel');
        
        $grades = $gradeModel->getAllOrdered();
        $currentYear = date('Y') . '/' . (date('Y') + 1);
        $classes = $classModel->getAllWithDetails($currentYear);
        $parents = $parentModel->getAllWithDetails(['status' => 'active']);
        
        $data = [
            'title' => 'Add New Student - ' . APP_NAME,
            'grades' => $grades,
            'classes' => $classes,
            'parents' => $parents,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('students/create', $data);
    }
    
    /**
     * Store new student
     */
    public function store() {
        // Restrict teachers from creating students
        if (Auth::hasAnyRole(['teacher'])) {
            $this->json(['success' => false, 'message' => 'Access denied. Teachers cannot create students.'], 403);
            return;
        }
        
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $studentModel = $this->model('Student');
        $parentModel = $this->model('ParentModel');
        
        // Validate input
        $errors = $this->validateStudentInput($_POST);
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
            return;
        }
        
        // Check if parent exists, if not create
        $parentId = $_POST['parent_id'] ?? null;
        
        if (empty($parentId) && !empty($_POST['parent_phone'])) {
            // Create new parent
            $parentData = [
                'first_name' => sanitize($_POST['parent_first_name']),
                'last_name' => sanitize($_POST['parent_last_name']),
                'phone' => formatPhone(sanitize($_POST['parent_phone'])),
                'email' => sanitize($_POST['parent_email'] ?? ''),
                'relationship' => sanitize($_POST['parent_relationship'] ?? 'guardian')
            ];
            
            $parentId = $parentModel->create($parentData);
        }
        
        // Generate admission number if not provided
        $admissionNumber = sanitize($_POST['admission_number'] ?? '');
        if (empty($admissionNumber)) {
            do {
                $admissionNumber = generateAdmissionNumber();
            } while ($studentModel->admissionNumberExists($admissionNumber));
        } else {
            if ($studentModel->admissionNumberExists($admissionNumber)) {
                $this->json(['success' => false, 'message' => 'Admission number already exists']);
                return;
            }
        }
        
        // Generate UPI
        $upi = generateUPI();
        
        // Handle photo upload
        $photoPath = null;
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
            $photoPath = $this->uploadStudentPhoto($_FILES['photo'], $admissionNumber);
            if (!$photoPath) {
                $this->json(['success' => false, 'message' => 'Failed to upload photo. Please ensure it is a valid image file (JPEG, PNG, GIF) and less than 5MB.']);
                return;
            }
        }
        
        // Prepare student data
        $studentData = [
            'admission_number' => $admissionNumber,
            'upi' => $upi,
            'first_name' => sanitize($_POST['first_name']),
            'middle_name' => sanitize($_POST['middle_name'] ?? ''),
            'last_name' => sanitize($_POST['last_name']),
            'gender' => sanitize($_POST['gender']),
            'date_of_birth' => sanitize($_POST['date_of_birth']),
            'admission_date' => sanitize($_POST['admission_date']),
            'class_id' => intval($_POST['class_id']),
            'parent_id' => $parentId,
            'parent_relationship' => sanitize($_POST['parent_relationship'] ?? ''),
            'status' => 'active',
            'medical_info' => sanitize($_POST['medical_info'] ?? ''),
            'photo' => $photoPath
        ];
        
        $studentId = $studentModel->create($studentData);
        
        if ($studentId) {
            $this->json([
                'success' => true,
                'message' => 'Student added successfully',
                'redirect' => BASE_URL . '/students/show/' . $studentId
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to add student']);
        }
    }
    
    /**
     * Show edit form
     */
    public function edit($id) {
        // Restrict teachers from editing students
        if (Auth::hasAnyRole(['teacher'])) {
            http_response_code(403);
            die("Access denied. Teachers cannot edit students.");
        }
        $studentModel = $this->model('Student');
        $gradeModel = $this->model('Grade');
        $classModel = $this->model('ClassModel');
        
        $student = $studentModel->getStudentWithDetails($id);
        
        if (!$student) {
            $this->setFlash('error', 'Student not found');
            $this->redirect('/students');
            return;
        }
        
        $grades = $gradeModel->getAllOrdered();
        $currentYear = date('Y') . '/' . (date('Y') + 1);
        $classes = $classModel->getAllWithDetails($currentYear);
        
        $data = [
            'title' => 'Edit Student - ' . APP_NAME,
            'student' => $student,
            'grades' => $grades,
            'classes' => $classes,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('students/edit', $data);
    }
    
    /**
     * Update student
     */
    public function update($id) {
        // Restrict teachers from updating students
        if (Auth::hasAnyRole(['teacher'])) {
            $this->json(['success' => false, 'message' => 'Access denied. Teachers cannot update students.'], 403);
            return;
        }
        
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $studentModel = $this->model('Student');
        
        // Validate input
        $errors = $this->validateStudentInput($_POST, $id);
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
            return;
        }
        
        // Check admission number uniqueness
        $admissionNumber = sanitize($_POST['admission_number'] ?? '');
        if ($studentModel->admissionNumberExists($admissionNumber, $id)) {
            $this->json(['success' => false, 'message' => 'Admission number already exists']);
            return;
        }
        
        // Handle photo upload
        $student = $studentModel->findById($id);
        $photoPath = $student['photo'] ?? null; // Keep existing photo
        
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
            // Delete old photo if exists
            if ($photoPath && file_exists(UPLOAD_DIR . $photoPath)) {
                @unlink(UPLOAD_DIR . $photoPath);
            }
            
            $newPhotoPath = $this->uploadStudentPhoto($_FILES['photo'], $admissionNumber);
            if ($newPhotoPath) {
                $photoPath = $newPhotoPath;
            } else {
                $this->json(['success' => false, 'message' => 'Failed to upload photo. Please ensure it is a valid image file (JPEG, PNG, GIF) and less than 5MB.']);
                return;
            }
        }
        
        // Prepare update data
        $studentData = [
            'admission_number' => $admissionNumber,
            'first_name' => sanitize($_POST['first_name']),
            'middle_name' => sanitize($_POST['middle_name'] ?? ''),
            'last_name' => sanitize($_POST['last_name']),
            'gender' => sanitize($_POST['gender']),
            'date_of_birth' => sanitize($_POST['date_of_birth']),
            'admission_date' => sanitize($_POST['admission_date']),
            'class_id' => intval($_POST['class_id']),
            'parent_id' => intval($_POST['parent_id']),
            'parent_relationship' => sanitize($_POST['parent_relationship'] ?? ''),
            'status' => sanitize($_POST['status'] ?? 'active'),
            'medical_info' => sanitize($_POST['medical_info'] ?? ''),
            'photo' => $photoPath
        ];
        
        if ($studentModel->update($id, $studentData)) {
            $this->json([
                'success' => true,
                'message' => 'Student updated successfully',
                'redirect' => BASE_URL . '/students/show/' . $id
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to update student']);
        }
    }
    
    /**
     * Delete student (soft delete)
     */
    public function delete($id) {
        // Restrict teachers from deleting students
        if (Auth::hasAnyRole(['teacher'])) {
            $this->json(['success' => false, 'message' => 'Access denied. Teachers cannot delete students.'], 403);
            return;
        }
        
        $studentModel = $this->model('Student');
        
        $student = $studentModel->findById($id);
        
        if (!$student) {
            $this->json(['success' => false, 'message' => 'Student not found']);
            return;
        }
        
        // Check if student has active invoices with balance
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM invoices WHERE student_id = ? AND status IN ('pending', 'partial') AND balance > 0");
        $stmt->execute([$id]);
        $hasPendingFees = $stmt->fetch()['count'] > 0;
        
        if ($hasPendingFees) {
            $this->json(['success' => false, 'message' => 'Cannot delete student with pending fee balances. Please clear fees first.']);
            return;
        }
        
        // Soft delete - set status to inactive
        if ($studentModel->update($id, ['status' => 'inactive'])) {
            $this->json(['success' => true, 'message' => 'Student deleted successfully (status set to inactive)']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to delete student']);
        }
    }
    
    /**
     * Restore student (undo delete)
     */
    public function restore($id) {
        // Restrict teachers from restoring students
        if (Auth::hasAnyRole(['teacher'])) {
            $this->json(['success' => false, 'message' => 'Access denied. Teachers cannot restore students.'], 403);
            return;
        }
        
        $studentModel = $this->model('Student');
        
        $student = $studentModel->findById($id);
        
        if (!$student) {
            $this->json(['success' => false, 'message' => 'Student not found']);
            return;
        }
        
        // Restore - set status to active
        if ($studentModel->update($id, ['status' => 'active'])) {
            $this->json(['success' => true, 'message' => 'Student restored successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to restore student']);
        }
    }
    
    /**
     * Generate student ID card
     */
    public function generateId($id) {
        // Restrict teachers from generating ID cards
        if (Auth::hasAnyRole(['teacher'])) {
            http_response_code(403);
            die("Access denied. Teachers cannot generate student ID cards.");
        }
        
        $studentModel = $this->model('Student');
        $student = $studentModel->getStudentWithDetails($id);
        
        if (!$student) {
            $this->setFlash('error', 'Student not found');
            $this->redirect('/students');
            return;
        }
        
        // Get school name and logo from settings
        $schoolName = getSchoolName();
        $schoolLogo = getSchoolLogo();
        
        // Generate QR code URL
        $qrCodeUrl = generateStudentQRCode($student['id'], $student['admission_number'], 80);
        
        $data = [
            'title' => 'Student ID Card - ' . APP_NAME,
            'student' => $student,
            'school_name' => $schoolName,
            'school_logo' => $schoolLogo,
            'qr_code_url' => $qrCodeUrl
        ];
        
        $this->view('students/id_card', $data);
    }
    
    /**
     * Validate student input
     */
    private function validateStudentInput($data, $excludeId = null) {
        $errors = [];
        
        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }
        
        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }
        
        if (empty($data['gender']) || !in_array($data['gender'], ['male', 'female'])) {
            $errors['gender'] = 'Valid gender is required';
        }
        
        if (empty($data['date_of_birth'])) {
            $errors['date_of_birth'] = 'Date of birth is required';
        }
        
        if (empty($data['admission_date'])) {
            $errors['admission_date'] = 'Admission date is required';
        }
        
        if (empty($data['class_id'])) {
            $errors['class_id'] = 'Class is required';
        }
        
        if (empty($data['parent_id']) && empty($data['parent_phone'])) {
            $errors['parent'] = 'Parent information is required';
        }
        
        return $errors;
    }
    
    /**
     * Upload student photo
     */
    private function uploadStudentPhoto($file, $admissionNumber) {
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
        $filename = 'student_' . $admissionNumber . '_' . time() . '.' . $extension;
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

