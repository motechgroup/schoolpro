<?php
/**
 * Library Controller
 * Handles library management operations
 */

class LibraryController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        // Librarian, admins, and school managers can access library
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'school_manager', 'librarian'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * List all books
     */
    public function index() {
        $bookModel = $this->model('Book');
        $subjectModel = $this->model('LearningArea');
        $classModel = $this->model('ClassModel');
        
        $filters = [
            'search' => $_GET['search'] ?? null,
            'subject_id' => $_GET['subject_id'] ?? null,
            'class_id' => $_GET['class_id'] ?? null,
            'status' => $_GET['status'] ?? null,
            'available_only' => isset($_GET['available_only']) ? true : false
        ];
        
        $books = $bookModel->getAll($filters);
        $subjects = $subjectModel->getAllWithDetails();
        $classes = $classModel->getAllWithDetails();
        
        $data = [
            'title' => 'Library - Books - ' . APP_NAME,
            'books' => $books,
            'subjects' => $subjects,
            'classes' => $classes,
            'filters' => $filters
        ];
        
        $this->view('library/index', $data);
    }
    
    /**
     * Show book details
     */
    public function show($id) {
        $bookModel = $this->model('Book');
        $borrowModel = $this->model('BookBorrow');
        
        $book = $bookModel->getById($id);
        
        if (!$book) {
            $this->setFlash('error', 'Book not found');
            $this->redirect('/library');
            return;
        }
        
        $activeBorrows = $borrowModel->getActiveBorrowsByBook($id);
        
        $data = [
            'title' => 'Book Details - ' . APP_NAME,
            'book' => $book,
            'activeBorrows' => $activeBorrows
        ];
        
        $this->view('library/show', $data);
    }
    
    /**
     * Show create book form
     */
    public function create() {
        $subjectModel = $this->model('LearningArea');
        $classModel = $this->model('ClassModel');
        
        $subjects = $subjectModel->getAllWithDetails();
        $classes = $classModel->getAllWithDetails();
        
        $data = [
            'title' => 'Add New Book - ' . APP_NAME,
            'subjects' => $subjects,
            'classes' => $classes
        ];
        
        $this->view('library/create', $data);
    }
    
    /**
     * Store new book
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/library');
            return;
        }
        
        $bookModel = $this->model('Book');
        
        // Validate ISBN if provided
        if (!empty($_POST['isbn'])) {
            if ($bookModel->isbnExists($_POST['isbn'])) {
                $this->setFlash('error', 'ISBN already exists');
                $this->redirect('/library/create');
                return;
            }
        }
        
        $data = [
            'isbn' => $_POST['isbn'] ?? null,
            'title' => trim($_POST['title'] ?? ''),
            'author' => trim($_POST['author'] ?? ''),
            'publisher' => trim($_POST['publisher'] ?? ''),
            'subject_id' => !empty($_POST['subject_id']) ? intval($_POST['subject_id']) : null,
            'class_id' => !empty($_POST['class_id']) ? intval($_POST['class_id']) : null,
            'edition' => trim($_POST['edition'] ?? ''),
            'total_copies' => intval($_POST['total_copies'] ?? 1),
            'available_copies' => intval($_POST['total_copies'] ?? 1),
            'location' => trim($_POST['location'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'status' => $_POST['status'] ?? 'active'
        ];
        
        if (empty($data['title'])) {
            $this->setFlash('error', 'Book title is required');
            $this->redirect('/library/create');
            return;
        }
        
        if (empty($data['subject_id'])) {
            $this->setFlash('error', 'Subject is required');
            $this->redirect('/library/create');
            return;
        }
        
        if (empty($data['class_id'])) {
            $this->setFlash('error', 'Class is required');
            $this->redirect('/library/create');
            return;
        }
        
        $bookId = $bookModel->create($data);
        
        if ($bookId) {
            $this->setFlash('success', 'Book added successfully');
            $this->redirect('/library');
        } else {
            $this->setFlash('error', 'Failed to add book');
            $this->redirect('/library/create');
        }
    }
    
    /**
     * Show edit book form
     */
    public function edit($id) {
        $bookModel = $this->model('Book');
        $subjectModel = $this->model('LearningArea');
        $classModel = $this->model('ClassModel');
        
        $book = $bookModel->findById($id);
        
        if (!$book) {
            $this->setFlash('error', 'Book not found');
            $this->redirect('/library');
            return;
        }
        
        $subjects = $subjectModel->getAllWithDetails();
        $classes = $classModel->getAllWithDetails();
        
        $data = [
            'title' => 'Edit Book - ' . APP_NAME,
            'book' => $book,
            'subjects' => $subjects,
            'classes' => $classes
        ];
        
        $this->view('library/edit', $data);
    }
    
    /**
     * Update book
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/library');
            return;
        }
        
        $bookModel = $this->model('Book');
        $book = $bookModel->findById($id);
        
        if (!$book) {
            $this->setFlash('error', 'Book not found');
            $this->redirect('/library');
            return;
        }
        
        // Validate ISBN if provided
        if (!empty($_POST['isbn']) && $_POST['isbn'] !== $book['isbn']) {
            if ($bookModel->isbnExists($_POST['isbn'], $id)) {
                $this->setFlash('error', 'ISBN already exists');
                $this->redirect('/library/edit/' . $id);
                return;
            }
        }
        
        $data = [
            'isbn' => $_POST['isbn'] ?? null,
            'title' => trim($_POST['title'] ?? ''),
            'author' => trim($_POST['author'] ?? ''),
            'publisher' => trim($_POST['publisher'] ?? ''),
            'subject_id' => !empty($_POST['subject_id']) ? intval($_POST['subject_id']) : null,
            'class_id' => !empty($_POST['class_id']) ? intval($_POST['class_id']) : null,
            'edition' => trim($_POST['edition'] ?? ''),
            'total_copies' => intval($_POST['total_copies'] ?? 1),
            'location' => trim($_POST['location'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'status' => $_POST['status'] ?? 'active'
        ];
        
        if (empty($data['title'])) {
            $this->setFlash('error', 'Book title is required');
            $this->redirect('/library/edit/' . $id);
            return;
        }
        
        if (empty($data['subject_id'])) {
            $this->setFlash('error', 'Subject is required');
            $this->redirect('/library/edit/' . $id);
            return;
        }
        
        if (empty($data['class_id'])) {
            $this->setFlash('error', 'Class is required');
            $this->redirect('/library/edit/' . $id);
            return;
        }
        
        // Update available copies based on current borrows
        $bookModel->updateAvailableCopies($id);
        $updatedBook = $bookModel->getById($id);
        $data['available_copies'] = $updatedBook['available_copies'] ?? 0;
        
        if ($bookModel->update($id, $data)) {
            $this->setFlash('success', 'Book updated successfully');
            $this->redirect('/library');
        } else {
            $this->setFlash('error', 'Failed to update book');
            $this->redirect('/library/edit/' . $id);
        }
    }
    
    /**
     * Delete book
     */
    public function delete($id) {
        // Check if this is an AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        $bookModel = $this->model('Book');
        $borrowModel = $this->model('BookBorrow');
        
        // Check if book has active borrows
        $activeBorrows = $borrowModel->getActiveBorrowsByBook($id);
        if (!empty($activeBorrows)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Cannot delete book with active borrows']);
                return;
            }
            $this->setFlash('error', 'Cannot delete book with active borrows');
            $this->redirect('/library');
            return;
        }
        
        if ($bookModel->delete($id)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Book deleted successfully']);
                return;
            }
            $this->setFlash('success', 'Book deleted successfully');
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to delete book']);
                return;
            }
            $this->setFlash('error', 'Failed to delete book');
        }
        
        if (!$isAjax) {
            $this->redirect('/library');
        }
    }
    
    /**
     * List all borrows
     */
    public function borrows() {
        $borrowModel = $this->model('BookBorrow');
        
        $filters = [
            'status' => $_GET['status'] ?? null,
            'student_id' => $_GET['student_id'] ?? null,
            'book_id' => $_GET['book_id'] ?? null,
            'overdue_only' => isset($_GET['overdue_only']) ? true : false
        ];
        
        // Update overdue status
        $borrowModel->updateOverdueStatus();
        
        $borrows = $borrowModel->getAll($filters);
        
        $data = [
            'title' => 'Library - Borrows - ' . APP_NAME,
            'borrows' => $borrows,
            'filters' => $filters
        ];
        
        $this->view('library/borrows', $data);
    }
    
    /**
     * Show assign book form
     */
    public function assign() {
        $bookModel = $this->model('Book');
        $studentModel = $this->model('Student');
        $invoiceModel = $this->model('Invoice');
        
        // Get active students
        $students = $studentModel->getAllWithDetails(['status' => 'active']);
        
        // Check fee balance and get library ratings for each student
        $currentYear = date('Y') . '/' . (date('Y') + 1);
        $ratingModel = $this->model('StudentLibraryRating');
        $borrowModel = $this->model('BookBorrow');
        
        foreach ($students as &$student) {
            // Check fee balance
            $invoices = $invoiceModel->getByStudent($student['id'], $currentYear);
            $totalBalance = 0;
            foreach ($invoices as $inv) {
                if ($inv['status'] == 'pending' || $inv['status'] == 'partial') {
                    $totalBalance += floatval($inv['balance'] ?? 0);
                }
            }
            $student['fee_balance'] = $totalBalance;
            
            // Get library rating
            $rating = $ratingModel->getByStudent($student['id']);
            if (!$rating) {
                $ratingModel->getOrCreate($student['id']);
                $rating = $ratingModel->getByStudent($student['id']);
            }
            
            // Get active borrows count
            $activeBorrows = $borrowModel->getActiveBorrowsByStudent($student['id']);
            $student['library_rating'] = $rating['rating'] ?? 5.00;
            $student['library_points'] = $rating['total_points'] ?? 100;
            $student['borrowing_level'] = $rating['borrowing_level'] ?? 'good';
            $student['max_borrows'] = $rating['max_borrows'] ?? 3;
            $student['active_borrows_count'] = count($activeBorrows);
            $student['can_borrow_more'] = count($activeBorrows) < intval($rating['max_borrows'] ?? 3);
        }
        
        $data = [
            'title' => 'Assign Book to Student - ' . APP_NAME,
            'books' => [], // Will be loaded via AJAX based on selected student
            'students' => $students
        ];
        
        $this->view('library/assign', $data);
    }
    
    /**
     * Store book assignment
     */
    public function storeAssign() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/library/assign');
            return;
        }
        
        $bookModel = $this->model('Book');
        $borrowModel = $this->model('BookBorrow');
        $invoiceModel = $this->model('Invoice');
        
        $bookId = intval($_POST['book_id'] ?? 0);
        $studentId = intval($_POST['student_id'] ?? 0);
        $dueDate = $_POST['due_date'] ?? null;
        
        // Validate inputs
        if (!$bookId || !$studentId || !$dueDate) {
            $this->setFlash('error', 'Please fill all required fields');
            $this->redirect('/library/assign');
            return;
        }
        
        // Get student details to check class
        $student = $studentModel->getStudentWithDetails($studentId);
        if (!$student) {
            $this->setFlash('error', 'Student not found');
            $this->redirect('/library/assign');
            return;
        }
        
        // Check if book exists and is available
        $book = $bookModel->getById($bookId);
        if (!$book || $book['status'] !== 'active') {
            $this->setFlash('error', 'Book not available');
            $this->redirect('/library/assign');
            return;
        }
        
        // Check if book is assigned to student's class
        if (!empty($book['class_id']) && $book['class_id'] != $student['class_id']) {
            $this->setFlash('error', 'This book is not assigned to the student\'s class. Students can only borrow books for their class.');
            $this->redirect('/library/assign');
            return;
        }
        
        $availableCopies = $bookModel->getAvailableCopies($bookId);
        if ($availableCopies <= 0) {
            $this->setFlash('error', 'No copies available');
            $this->redirect('/library/assign');
            return;
        }
        
        // Check student fee balance
        $currentYear = date('Y') . '/' . (date('Y') + 1);
        $invoices = $invoiceModel->getByStudent($studentId, $currentYear);
        $totalBalance = 0;
        foreach ($invoices as $inv) {
            if ($inv['status'] == 'pending' || $inv['status'] == 'partial') {
                $totalBalance += floatval($inv['balance'] ?? 0);
            }
        }
        
        if ($totalBalance > 0) {
            // Librarians should not see the balance amount
            $isLibrarian = Auth::hasRole('librarian');
            $message = $isLibrarian 
                ? 'Student has outstanding fee balance. Cannot borrow books.'
                : 'Student has outstanding fee balance (KES ' . number_format($totalBalance, 2) . '). Cannot borrow books.';
            $this->setFlash('error', $message);
            $this->redirect('/library/assign');
            return;
        }
        
        // Check if student already has this book borrowed
        $activeBorrows = $borrowModel->getActiveBorrowsByStudent($studentId);
        foreach ($activeBorrows as $borrow) {
            if ($borrow['book_id'] == $bookId) {
                $this->setFlash('error', 'Student already has this book borrowed');
                $this->redirect('/library/assign');
                return;
            }
        }
        
        // Check borrowing limit based on rating
        $ratingModel = $this->model('StudentLibraryRating');
        if (!$ratingModel->canBorrow($studentId)) {
            $limit = $ratingModel->getBorrowingLimit($studentId);
            $this->setFlash('error', "Student has reached maximum borrowing limit ({$limit} books). Please return books to borrow more.");
            $this->redirect('/library/assign');
            return;
        }
        
        // Create borrow record
        $data = [
            'book_id' => $bookId,
            'student_id' => $studentId,
            'borrowed_by' => Auth::userId(),
            'borrow_date' => date('Y-m-d'),
            'due_date' => $dueDate,
            'status' => 'borrowed',
            'notes' => trim($_POST['notes'] ?? '')
        ];
        
        $borrowId = $borrowModel->create($data);
        
        if ($borrowId) {
            // Update available copies
            $bookModel->updateAvailableCopies($bookId);
            
            // Record borrow in rating system
            $ratingModel = $this->model('StudentLibraryRating');
            $ratingModel->recordBorrow($studentId);
            
            $this->setFlash('success', 'Book assigned successfully');
            $this->redirect('/library/borrows');
        } else {
            $this->setFlash('error', 'Failed to assign book');
            $this->redirect('/library/assign');
        }
    }
    
    /**
     * Return book
     */
    public function return($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Show return form
            $borrowModel = $this->model('BookBorrow');
            $borrow = $borrowModel->getById($id);
            
            if (!$borrow) {
                $this->setFlash('error', 'Borrow record not found');
                $this->redirect('/library/borrows');
                return;
            }
            
            if ($borrow['status'] === 'returned') {
                $this->setFlash('error', 'Book already returned');
                $this->redirect('/library/borrows');
                return;
            }
            
            $data = [
                'title' => 'Return Book - ' . APP_NAME,
                'borrow' => $borrow
            ];
            
            $this->view('library/return', $data);
            return;
        }
        
        $borrowModel = $this->model('BookBorrow');
        $bookModel = $this->model('Book');
        $ratingModel = $this->model('StudentLibraryRating');
        
        $borrow = $borrowModel->getById($id);
        
        if (!$borrow) {
            $this->setFlash('error', 'Borrow record not found');
            $this->redirect('/library/borrows');
            return;
        }
        
        if ($borrow['status'] === 'returned') {
            $this->setFlash('error', 'Book already returned');
            $this->redirect('/library/borrows');
            return;
        }
        
        // Get book condition from form
        $bookCondition = $_POST['book_condition'] ?? 'good';
        $conditionNotes = trim($_POST['condition_notes'] ?? '');
        
        // Check if returned on time
        $dueDate = strtotime($borrow['due_date']);
        $returnDate = time();
        $onTime = $returnDate <= $dueDate;
        
        // Calculate fine if overdue
        $fineAmount = 0;
        if (!$onTime) {
            $fineAmount = $borrowModel->calculateFine($id);
        }
        
        // Calculate points
        $pointsAwarded = 0;
        $pointsDeducted = 0;
        
        if ($onTime) {
            // Award points for on-time return
            switch ($bookCondition) {
                case 'excellent':
                    $pointsAwarded = 15;
                    break;
                case 'good':
                    $pointsAwarded = 10;
                    break;
                case 'fair':
                    $pointsAwarded = 5;
                    break;
                case 'poor':
                    $pointsAwarded = 0;
                    break;
                case 'damaged':
                    $pointsDeducted = 10;
                    break;
            }
        } else {
            // Deduct points for late return
            switch ($bookCondition) {
                case 'excellent':
                    $pointsDeducted = 5;
                    break;
                case 'good':
                    $pointsDeducted = 10;
                    break;
                case 'fair':
                    $pointsDeducted = 15;
                    break;
                case 'poor':
                    $pointsDeducted = 25;
                    break;
                case 'damaged':
                    $pointsDeducted = 40;
                    break;
            }
        }
        
        // Additional deduction for damaged books
        if ($bookCondition === 'damaged' || $bookCondition === 'poor') {
            $pointsDeducted += 20;
        }
        
        $data = [
            'return_date' => date('Y-m-d'),
            'returned_to' => Auth::userId(),
            'status' => 'returned',
            'fine_amount' => $fineAmount,
            'book_condition' => $bookCondition,
            'condition_notes' => $conditionNotes,
            'points_awarded' => $pointsAwarded,
            'points_deducted' => $pointsDeducted,
            'notes' => trim($_POST['notes'] ?? $borrow['notes'] ?? '')
        ];
        
        if ($borrowModel->update($id, $data)) {
            // Update available copies
            $bookModel->updateAvailableCopies($borrow['book_id']);
            
            // Update student rating
            $ratingModel->recordReturn($borrow['student_id'], $onTime, $bookCondition);
            
            // Award/deduct points
            if ($pointsAwarded > 0) {
                $ratingModel->awardPoints($borrow['student_id'], $pointsAwarded, 'Book returned on time in ' . $bookCondition . ' condition');
            }
            if ($pointsDeducted > 0) {
                $ratingModel->deductPoints($borrow['student_id'], $pointsDeducted, 'Book returned late or in ' . $bookCondition . ' condition');
            }
            
            $message = 'Book returned successfully';
            if ($pointsAwarded > 0) {
                $message .= '. Points awarded: +' . $pointsAwarded;
            }
            if ($pointsDeducted > 0) {
                $message .= '. Points deducted: -' . $pointsDeducted;
            }
            if ($fineAmount > 0) {
                $message .= '. Fine: KES ' . number_format($fineAmount, 2);
            }
            
            $this->setFlash('success', $message);
            $this->redirect('/library/borrows');
        } else {
            $this->setFlash('error', 'Failed to return book');
            $this->redirect('/library/borrows');
        }
    }
    
    /**
     * Mark book as lost
     */
    public function markLost($id) {
        $borrowModel = $this->model('BookBorrow');
        $bookModel = $this->model('Book');
        
        $borrow = $borrowModel->getById($id);
        
        if (!$borrow) {
            $this->setFlash('error', 'Borrow record not found');
            $this->redirect('/library/borrows');
            return;
        }
        
        $data = [
            'status' => 'lost',
            'fine_amount' => floatval($_POST['fine_amount'] ?? 0),
            'notes' => trim($_POST['notes'] ?? $borrow['notes'] ?? '')
        ];
        
        if ($borrowModel->update($id, $data)) {
            // Update available copies
            $bookModel->updateAvailableCopies($borrow['book_id']);
            
            // Record lost book in rating system
            $ratingModel = $this->model('StudentLibraryRating');
            $ratingModel->recordLostBook($borrow['student_id']);
            $ratingModel->deductPoints($borrow['student_id'], 50, 'Book marked as lost');
            
            $this->setFlash('success', 'Book marked as lost. Student rating updated.');
            $this->redirect('/library/borrows');
        } else {
            $this->setFlash('error', 'Failed to mark book as lost');
            $this->redirect('/library/borrows');
        }
    }
    
    /**
     * View student ratings
     */
    public function ratings() {
        $ratingModel = $this->model('StudentLibraryRating');
        
        $filters = [
            'search' => $_GET['search'] ?? null,
            'borrowing_level' => $_GET['borrowing_level'] ?? null
        ];
        
        $ratings = $ratingModel->getAllWithDetails($filters);
        
        $data = [
            'title' => 'Library - Student Ratings - ' . APP_NAME,
            'ratings' => $ratings,
            'filters' => $filters
        ];
        
        $this->view('library/ratings', $data);
    }
    
    /**
     * View student rating details
     */
    public function studentRating($studentId) {
        $ratingModel = $this->model('StudentLibraryRating');
        $borrowModel = $this->model('BookBorrow');
        
        $rating = $ratingModel->getWithStudentDetails($studentId);
        
        if (!$rating) {
            // Create default rating
            $ratingModel->getOrCreate($studentId);
            $rating = $ratingModel->getWithStudentDetails($studentId);
        }
        
        // Get borrow history
        $borrows = $borrowModel->getAll(['student_id' => $studentId]);
        
        $data = [
            'title' => 'Student Library Rating - ' . APP_NAME,
            'rating' => $rating,
            'borrows' => $borrows
        ];
        
        $this->view('library/student_rating', $data);
    }
    
    /**
     * Get books for a specific class (AJAX endpoint)
     */
    public function getBooksForClass() {
        $bookModel = $this->model('Book');
        
        $classId = intval($_GET['class_id'] ?? 0);
        $availableOnly = isset($_GET['available_only']) && $_GET['available_only'] == '1';
        
        if (!$classId) {
            $this->json(['success' => false, 'message' => 'Class ID is required']);
            return;
        }
        
        $filters = [
            'class_id' => $classId,
            'status' => 'active'
        ];
        
        if ($availableOnly) {
            $filters['available_only'] = true;
        }
        
        $books = $bookModel->getAll($filters);
        
        $this->json([
            'success' => true,
            'books' => $books
        ]);
    }
}

