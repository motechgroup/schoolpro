<?php
/**
 * Examination Controller
 * Handles examination management and marks entry
 */

class ExaminationController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        // Different roles have different access levels
        $role = Auth::user()['role_name'] ?? '';
        if (!in_array($role, ['super_admin', 'school_admin', 'head_teacher', 'teacher'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * List all examinations
     */
    public function index() {
        $examinationModel = $this->model('Examination');
        $classModel = $this->model('ClassModel');
        
        $filters = [
            'class_id' => $_GET['class_id'] ?? null,
            'term' => $_GET['term'] ?? null,
            'academic_year' => $_GET['academic_year'] ?? date('Y') . '/' . (date('Y') + 1),
            'status' => $_GET['status'] ?? null
        ];
        
        $examinations = $examinationModel->getAllWithDetails($filters);
        $classes = $classModel->getAllWithDetails();
        
        $data = [
            'title' => 'Examinations - ' . APP_NAME,
            'examinations' => $examinations,
            'classes' => $classes,
            'filters' => $filters
        ];
        
        $this->view('examinations/index', $data);
    }
    
    /**
     * Show create examination form
     */
    public function create() {
        $classModel = $this->model('ClassModel');
        $gradeModel = $this->model('Grade');
        $teacherModel = $this->model('Teacher');
        
        $user = Auth::user();
        $role = $user['role_name'] ?? '';
        
        // For teachers and head teachers, only show classes they are assigned to
        if ($role === 'teacher' || $role === 'head_teacher') {
            $teacher = $teacherModel->findByUserId(Auth::userId());
            if ($teacher) {
                $classes = $teacherModel->getAssignedClasses($teacher['id']);
                // Format classes to match getAllWithDetails structure
                $formattedClasses = [];
                foreach ($classes as $class) {
                    $formattedClasses[] = [
                        'id' => $class['id'],
                        'name' => $class['name'],
                        'grade_display_name' => $class['grade_display_name'] ?? '',
                        'grade_id' => $class['grade_id'] ?? null
                    ];
                }
                $classes = $formattedClasses;
            } else {
                // Teacher record not found, show empty list
                $classes = [];
            }
        } else {
            // For admins, show all classes
            $classes = $classModel->getAllWithDetails();
        }
        
        $currentYear = date('Y') . '/' . (date('Y') + 1);
        $selectedClassId = $_GET['class_id'] ?? null;
        
        $data = [
            'title' => 'Create Examination - ' . APP_NAME,
            'classes' => $classes,
            'academicYear' => $currentYear,
            'selectedClassId' => $selectedClassId,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('examinations/create', $data);
    }
    
    /**
     * Store new examination
     */
    public function store() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $examinationModel = $this->model('Examination');
        $teacherModel = $this->model('Teacher');
        $db = Database::getInstance()->getConnection();
        
        $user = Auth::user();
        $role = $user['role_name'] ?? '';
        $classId = intval($_POST['class_id'] ?? 0);
        
        // For teachers and head teachers, validate that they can only create exams for their assigned classes
        if ($role === 'teacher' || $role === 'head_teacher') {
            $teacher = $teacherModel->findByUserId(Auth::userId());
            if (!$teacher) {
                $this->json(['success' => false, 'message' => 'Teacher record not found'], 403);
                return;
            }
            
            // Check if the class is assigned to this teacher
            $assignedClasses = $teacherModel->getAssignedClasses($teacher['id']);
            $isAssigned = false;
            foreach ($assignedClasses as $class) {
                if ($class['id'] == $classId) {
                    $isAssigned = true;
                    break;
                }
            }
            
            if (!$isAssigned) {
                $this->json(['success' => false, 'message' => 'You can only create examinations for classes assigned to you'], 403);
                return;
            }
        }
        
        try {
            $db->beginTransaction();
            
            // Create examination
            $examinationData = [
                'name' => sanitize($_POST['name'] ?? ''),
                'class_id' => $classId,
                'term' => intval($_POST['term'] ?? 1),
                'academic_year' => sanitize($_POST['academic_year'] ?? ''),
                'exam_date' => !empty($_POST['exam_date']) ? sanitize($_POST['exam_date']) : null,
                'total_marks' => floatval($_POST['total_marks'] ?? 100),
                'passing_marks' => floatval($_POST['passing_marks'] ?? 40),
                'status' => sanitize($_POST['status'] ?? 'draft'),
                'created_by' => Auth::userId()
            ];
            
            if (empty($examinationData['name']) || empty($examinationData['class_id'])) {
                throw new Exception('Examination name and class are required');
            }
            
            $examinationId = $examinationModel->create($examinationData);
            
            if (!$examinationId) {
                throw new Exception('Failed to create examination');
            }
            
            // Add subjects to examination
            if (!empty($_POST['subjects']) && is_array($_POST['subjects'])) {
                foreach ($_POST['subjects'] as $subjectId) {
                    $subjectId = intval($subjectId);
                    if ($subjectId > 0) {
                        $subjectData = [
                            'examination_id' => $examinationId,
                            'learning_area_id' => $subjectId,
                            'max_marks' => floatval($_POST['max_marks'][$subjectId] ?? 100),
                            'passing_marks' => floatval($_POST['passing_marks'][$subjectId] ?? 40),
                            'teacher_id' => !empty($_POST['teacher_id'][$subjectId]) ? intval($_POST['teacher_id'][$subjectId]) : null
                        ];
                        
                        $stmt = $db->prepare("INSERT INTO examination_subjects 
                            (examination_id, learning_area_id, max_marks, passing_marks, teacher_id) 
                            VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $subjectData['examination_id'],
                            $subjectData['learning_area_id'],
                            $subjectData['max_marks'],
                            $subjectData['passing_marks'],
                            $subjectData['teacher_id']
                        ]);
                    }
                }
            }
            
            $db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'Examination created successfully',
                'redirect' => BASE_URL . '/examinations/show/' . $examinationId
            ]);
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Examination creation error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Show examination details
     */
    public function show($id) {
        $examinationModel = $this->model('Examination');
        $examination = $examinationModel->getExaminationWithDetails($id);
        
        if (!$examination) {
            $this->setFlash('error', 'Examination not found');
            $this->redirect('/examinations');
            return;
        }
        
        $subjects = $examinationModel->getExaminationSubjects($id);
        $students = $examinationModel->getExaminationStudents($id);
        $progress = $examinationModel->getMarksEntryProgress($id);
        
        $data = [
            'title' => 'Examination Details - ' . APP_NAME,
            'examination' => $examination,
            'subjects' => $subjects,
            'students' => $students,
            'progress' => $progress
        ];
        
        $this->view('examinations/show', $data);
    }
    
    /**
     * Show marks entry form for a subject
     */
    public function enterMarks($examinationId, $subjectId = null) {
        $examinationModel = $this->model('Examination');
        $examination = $examinationModel->getExaminationWithDetails($examinationId);
        
        if (!$examination) {
            $this->setFlash('error', 'Examination not found');
            $this->redirect('/examinations');
            return;
        }
        
        $subjects = $examinationModel->getExaminationSubjects($examinationId);
        
        // If subject ID provided, show marks entry for that subject
        if ($subjectId) {
            $selectedSubject = null;
            foreach ($subjects as $subject) {
                if ($subject['id'] == $subjectId) {
                    $selectedSubject = $subject;
                    break;
                }
            }
            
            if (!$selectedSubject) {
                $this->setFlash('error', 'Subject not found in this examination');
                $this->redirect('/examinations/show/' . $examinationId);
                return;
            }
            
            $students = $examinationModel->getExaminationStudents($examinationId);
            
            // Get existing marks
            $existingMarks = [];
            $db = Database::getInstance()->getConnection();
            $marksStmt = $db->prepare("SELECT * FROM examination_marks 
                WHERE examination_id = ? AND examination_subject_id = ?");
            $marksStmt->execute([$examinationId, $subjectId]);
            foreach ($marksStmt->fetchAll() as $mark) {
                $existingMarks[$mark['student_id']] = $mark;
            }
            
            $data = [
                'title' => 'Enter Marks - ' . APP_NAME,
                'examination' => $examination,
                'subject' => $selectedSubject,
                'students' => $students,
                'existingMarks' => $existingMarks,
                'csrf_token' => generateCSRFToken()
            ];
            
            $this->view('examinations/enter_marks', $data);
        } else {
            // Show subject selection
            $data = [
                'title' => 'Select Subject - ' . APP_NAME,
                'examination' => $examination,
                'subjects' => $subjects
            ];
            
            $this->view('examinations/select_subject', $data);
        }
    }
    
    /**
     * Save marks
     */
    public function saveMarks() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $examinationId = intval($_POST['examination_id'] ?? 0);
        $subjectId = intval($_POST['examination_subject_id'] ?? 0);
        
        if (empty($examinationId) || empty($subjectId)) {
            $this->json(['success' => false, 'message' => 'Examination and subject are required']);
            return;
        }
        
        $examinationModel = $this->model('Examination');
        $db = Database::getInstance()->getConnection();
        
        try {
            $db->beginTransaction();
            
            // Get subject details for max marks
            $subjectStmt = $db->prepare("SELECT max_marks FROM examination_subjects WHERE id = ?");
            $subjectStmt->execute([$subjectId]);
            $subject = $subjectStmt->fetch();
            
            if (!$subject) {
                throw new Exception('Subject not found');
            }
            
            $maxMarks = floatval($subject['max_marks']);
            
            // Process marks for each student
            if (!empty($_POST['marks']) && is_array($_POST['marks'])) {
                foreach ($_POST['marks'] as $studentId => $marksData) {
                    $studentId = intval($studentId);
                    $marksObtained = floatval($marksData['marks'] ?? 0);
                    $remarks = sanitize($marksData['remarks'] ?? '');
                    
                    if ($marksObtained < 0 || $marksObtained > $maxMarks) {
                        continue; // Skip invalid marks
                    }
                    
                    $grade = $examinationModel->calculateGrade($marksObtained, $maxMarks);
                    
                    // Check if marks already exist
                    $checkStmt = $db->prepare("SELECT id FROM examination_marks 
                        WHERE examination_id = ? AND examination_subject_id = ? AND student_id = ?");
                    $checkStmt->execute([$examinationId, $subjectId, $studentId]);
                    $existing = $checkStmt->fetch();
                    
                    if ($existing) {
                        // Update existing marks
                        $updateStmt = $db->prepare("UPDATE examination_marks 
                            SET marks_obtained = ?, grade = ?, remarks = ?, entered_by = ?, updated_at = NOW()
                            WHERE id = ?");
                        $updateStmt->execute([
                            $marksObtained,
                            $grade,
                            $remarks,
                            Auth::userId(),
                            $existing['id']
                        ]);
                    } else {
                        // Insert new marks
                        $insertStmt = $db->prepare("INSERT INTO examination_marks 
                            (examination_id, examination_subject_id, student_id, marks_obtained, grade, remarks, entered_by) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $insertStmt->execute([
                            $examinationId,
                            $subjectId,
                            $studentId,
                            $marksObtained,
                            $grade,
                            $remarks,
                            Auth::userId()
                        ]);
                    }
                }
            }
            
            $db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'Marks saved successfully',
                'redirect' => BASE_URL . '/examinations/show/' . $examinationId
            ]);
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Save marks error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Generate report card for a student
     */
    public function reportCard($examinationId, $studentId) {
        $examinationModel = $this->model('Examination');
        $studentModel = $this->model('Student');
        $invoiceModel = $this->model('Invoice');
        
        $examination = $examinationModel->getExaminationWithDetails($examinationId);
        $student = $studentModel->getStudentWithDetails($studentId);
        
        if (!$examination || !$student) {
            $this->setFlash('error', 'Examination or student not found');
            $this->redirect('/examinations');
            return;
        }
        
        // Verify student belongs to examination class
        if ($student['class_id'] != $examination['class_id']) {
            $this->setFlash('error', 'Student does not belong to this examination class');
            $this->redirect('/examinations/show/' . $examinationId);
            return;
        }
        
        // Check if student has fee balance
        // Only super admins, school admins, school managers, and head teachers can bypass fee balance check
        // All other roles (teachers, bursars, accountants, parents, students) are restricted when students have outstanding fees
        $isAdmin = Auth::hasAnyRole(['super_admin', 'school_admin', 'school_manager', 'head_teacher']);
        
        if (!$isAdmin) {
            // Check for fee balance - get all invoices for current academic year
            $currentYear = $examination['academic_year'];
            $invoices = $invoiceModel->getByStudent($studentId, $currentYear);
            
            // Update balances to ensure they're current
            foreach ($invoices as $inv) {
                $invoiceModel->updateBalance($inv['id']);
            }
            
            // Re-fetch invoices with updated balances
            $invoices = $invoiceModel->getByStudent($studentId, $currentYear);
            
            // Calculate total balance
            $totalBalance = 0;
            foreach ($invoices as $inv) {
                if ($inv['status'] == 'pending' || $inv['status'] == 'partial') {
                    $totalBalance += floatval($inv['balance'] ?? 0);
                }
            }
            
            // Block report card if balance exists
            if ($totalBalance > 0) {
                $data = [
                    'title' => 'Report Card Access Restricted - ' . APP_NAME,
                    'student' => $student,
                    'examination' => $examination,
                    'totalBalance' => $totalBalance,
                    'invoices' => $invoices
                ];
                $this->view('examinations/report_card_restricted', $data);
                return;
            }
        }
        
        $marks = $examinationModel->getStudentMarks($examinationId, $studentId);
        $subjects = $examinationModel->getExaminationSubjects($examinationId);
        
        // Calculate totals
        $totalMarks = 0;
        $totalMaxMarks = 0;
        foreach ($marks as $mark) {
            $totalMarks += floatval($mark['marks_obtained']);
            $totalMaxMarks += floatval($mark['max_marks']);
        }
        
        $overallGrade = $totalMaxMarks > 0 ? $examinationModel->calculateGrade($totalMarks, $totalMaxMarks) : 'N/A';
        $overallPercentage = $totalMaxMarks > 0 ? round(($totalMarks / $totalMaxMarks) * 100, 2) : 0;
        
        // Get class teacher information
        $classModel = $this->model('ClassModel');
        $class = $classModel->getClassWithDetails($student['class_id']);
        $classTeacher = null;
        if ($class && !empty($class['class_teacher_id'])) {
            $teacherModel = $this->model('Teacher');
            $classTeacher = $teacherModel->getTeacherWithDetails($class['class_teacher_id']);
        }
        
        // Get head teacher information
        $db = Database::getInstance()->getConnection();
        $headTeacherStmt = $db->prepare("SELECT u.first_name, u.last_name, t.first_name as teacher_first_name, t.last_name as teacher_last_name
                                        FROM users u
                                        LEFT JOIN roles r ON u.role_id = r.id
                                        LEFT JOIN teachers t ON t.user_id = u.id
                                        WHERE r.name = 'head_teacher' AND u.status = 'active'
                                        LIMIT 1");
        $headTeacherStmt->execute();
        $headTeacher = $headTeacherStmt->fetch();
        
        // Get school information from settings
        $schoolName = getSchoolName();
        $schoolStmt = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('school_name', 'school_address', 'school_phone', 'school_email')");
        $schoolSettings = [];
        foreach ($schoolStmt->fetchAll() as $setting) {
            $schoolSettings[$setting['setting_key']] = $setting['setting_value'];
        }
        
        $data = [
            'title' => 'Report Card - ' . APP_NAME,
            'examination' => $examination,
            'student' => $student,
            'marks' => $marks,
            'subjects' => $subjects,
            'totalMarks' => $totalMarks,
            'totalMaxMarks' => $totalMaxMarks,
            'overallGrade' => $overallGrade,
            'overallPercentage' => $overallPercentage,
            'classTeacher' => $classTeacher,
            'headTeacher' => $headTeacher,
            'schoolName' => $schoolName,
            'schoolSettings' => $schoolSettings
        ];
        
        $this->view('examinations/report_card', $data);
    }
    
    /**
     * Get learning areas for a class
     */
    public function getClassSubjects() {
        $classId = intval($_GET['class_id'] ?? 0);
        
        if (empty($classId)) {
            $this->json(['success' => false, 'message' => 'Class ID is required']);
            return;
        }
        
        $classModel = $this->model('ClassModel');
        $class = $classModel->findById($classId);
        
        if (!$class) {
            $this->json(['success' => false, 'message' => 'Class not found']);
            return;
        }
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT la.* FROM learning_areas la 
            WHERE la.grade_id = ? ORDER BY la.name");
        $stmt->execute([$class['grade_id']]);
        $subjects = $stmt->fetchAll();
        
        $this->json(['success' => true, 'subjects' => $subjects]);
    }
    
    /**
     * Send report card via SMS (summarized)
     */
    public function sendReportCardSms() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'school_manager', 'head_teacher'])) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        $examinationId = intval($_POST['examination_id'] ?? 0);
        $studentId = intval($_POST['student_id'] ?? 0);
        
        if (empty($examinationId) || empty($studentId)) {
            $this->json(['success' => false, 'message' => 'Examination ID and Student ID are required']);
            return;
        }
        
        require_once APP_PATH . '/helpers/ReportCardHelper.php';
        
        $examinationModel = $this->model('Examination');
        $studentModel = $this->model('Student');
        
        $examination = $examinationModel->getExaminationWithDetails($examinationId);
        $student = $studentModel->getStudentWithDetails($studentId);
        
        if (!$examination || !$student) {
            $this->json(['success' => false, 'message' => 'Examination or student not found']);
            return;
        }
        
        $marks = $examinationModel->getStudentMarks($examinationId, $studentId);
        $subjects = $examinationModel->getExaminationSubjects($examinationId);
        
        // Calculate totals
        $totalMarks = 0;
        $totalMaxMarks = 0;
        foreach ($marks as $mark) {
            $totalMarks += floatval($mark['marks_obtained']);
            $totalMaxMarks += floatval($mark['max_marks']);
        }
        
        $overallGrade = $totalMaxMarks > 0 ? $examinationModel->calculateGrade($totalMarks, $totalMaxMarks) : 'N/A';
        $overallPercentage = $totalMaxMarks > 0 ? round(($totalMarks / $totalMaxMarks) * 100, 2) : 0;
        
        $result = ReportCardHelper::sendReportCardSms(
            $studentId,
            $examination,
            $student,
            $marks,
            $subjects,
            $totalMarks,
            $totalMaxMarks,
            $overallGrade,
            $overallPercentage
        );
        
        $this->json($result);
    }
    
    /**
     * Send report card via Email (PDF detailed)
     */
    public function sendReportCardEmail() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'school_manager', 'head_teacher'])) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        $examinationId = intval($_POST['examination_id'] ?? 0);
        $studentId = intval($_POST['student_id'] ?? 0);
        
        if (empty($examinationId) || empty($studentId)) {
            $this->json(['success' => false, 'message' => 'Examination ID and Student ID are required']);
            return;
        }
        
        require_once APP_PATH . '/helpers/ReportCardHelper.php';
        
        $examinationModel = $this->model('Examination');
        $studentModel = $this->model('Student');
        
        $examination = $examinationModel->getExaminationWithDetails($examinationId);
        $student = $studentModel->getStudentWithDetails($studentId);
        
        if (!$examination || !$student) {
            $this->json(['success' => false, 'message' => 'Examination or student not found']);
            return;
        }
        
        $marks = $examinationModel->getStudentMarks($examinationId, $studentId);
        $subjects = $examinationModel->getExaminationSubjects($examinationId);
        
        // Calculate totals
        $totalMarks = 0;
        $totalMaxMarks = 0;
        foreach ($marks as $mark) {
            $totalMarks += floatval($mark['marks_obtained']);
            $totalMaxMarks += floatval($mark['max_marks']);
        }
        
        $overallGrade = $totalMaxMarks > 0 ? $examinationModel->calculateGrade($totalMarks, $totalMaxMarks) : 'N/A';
        $overallPercentage = $totalMaxMarks > 0 ? round(($totalMarks / $totalMaxMarks) * 100, 2) : 0;
        
        $result = ReportCardHelper::sendReportCardEmail(
            $studentId,
            $examination,
            $student,
            $marks,
            $subjects,
            $totalMarks,
            $totalMaxMarks,
            $overallGrade,
            $overallPercentage
        );
        
        $this->json($result);
    }
}

