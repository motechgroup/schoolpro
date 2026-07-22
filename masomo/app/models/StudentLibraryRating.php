<?php
/**
 * Student Library Rating Model
 * Handles student library ratings, points, and borrowing privileges
 */

class StudentLibraryRating extends Model {
    protected $table = 'student_library_ratings';
    
    /**
     * Get or create rating for student
     */
    public function getOrCreate($studentId) {
        $rating = $this->getByStudent($studentId);
        
        if (!$rating) {
            // Create default rating
            $this->create([
                'student_id' => $studentId,
                'total_points' => 100, // Starting points
                'rating' => 5.00,
                'borrowing_level' => 'good',
                'max_borrows' => 3
            ]);
            $rating = $this->getByStudent($studentId);
        }
        
        return $rating;
    }
    
    /**
     * Get rating by student ID
     */
    public function getByStudent($studentId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE student_id = ?");
        $stmt->execute([$studentId]);
        return $stmt->fetch();
    }
    
    /**
     * Get rating with student details
     */
    public function getWithStudentDetails($studentId) {
        $sql = "SELECT slr.*,
                       s.first_name,
                       s.last_name,
                       s.admission_number,
                       c.name as class_name,
                       g.display_name as grade_display_name
                FROM {$this->table} slr
                LEFT JOIN students s ON slr.student_id = s.id
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN grades g ON c.grade_id = g.id
                WHERE slr.student_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetch();
    }
    
    /**
     * Get all ratings with student details
     */
    public function getAllWithDetails($filters = []) {
        $sql = "SELECT slr.*,
                       s.first_name,
                       s.last_name,
                       s.admission_number,
                       c.name as class_name,
                       g.display_name as grade_display_name
                FROM {$this->table} slr
                LEFT JOIN students s ON slr.student_id = s.id
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN grades g ON c.grade_id = g.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['borrowing_level'])) {
            $sql .= " AND slr.borrowing_level = ?";
            $params[] = $filters['borrowing_level'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.admission_number LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY slr.rating DESC, slr.total_points DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Award points for good behavior
     */
    public function awardPoints($studentId, $points, $reason = '') {
        $rating = $this->getOrCreate($studentId);
        
        $newPoints = intval($rating['total_points']) + intval($points);
        
        $this->update($rating['id'], [
            'total_points' => $newPoints
        ]);
        
        $this->recalculateRating($studentId);
        
        return $newPoints;
    }
    
    /**
     * Deduct points for poor behavior
     */
    public function deductPoints($studentId, $points, $reason = '') {
        $rating = $this->getOrCreate($studentId);
        
        $newPoints = max(0, intval($rating['total_points']) - intval($points));
        
        $this->update($rating['id'], [
            'total_points' => $newPoints
        ]);
        
        $this->recalculateRating($studentId);
        
        return $newPoints;
    }
    
    /**
     * Record book return (without points - points are handled separately)
     */
    public function recordReturn($studentId, $onTime = true, $bookCondition = 'good') {
        $rating = $this->getOrCreate($studentId);
        
        $data = [
            'total_returns' => intval($rating['total_returns']) + 1
        ];
        
        if ($onTime) {
            $data['on_time_returns'] = intval($rating['on_time_returns']) + 1;
        } else {
            $data['late_returns'] = intval($rating['late_returns']) + 1;
        }
        
        // Track book condition
        if ($bookCondition === 'damaged' || $bookCondition === 'poor') {
            $data['damaged_books'] = intval($rating['damaged_books']) + 1;
        }
        
        $this->update($rating['id'], $data);
        $this->recalculateRating($studentId);
    }
    
    /**
     * Record lost book
     */
    public function recordLostBook($studentId) {
        $rating = $this->getOrCreate($studentId);
        
        $this->update($rating['id'], [
            'lost_books' => intval($rating['lost_books']) + 1,
            'total_points' => max(0, intval($rating['total_points']) - 50) // Heavy penalty
        ]);
        
        $this->recalculateRating($studentId);
    }
    
    /**
     * Record book borrow
     */
    public function recordBorrow($studentId) {
        $rating = $this->getOrCreate($studentId);
        
        $this->update($rating['id'], [
            'total_borrows' => intval($rating['total_borrows']) + 1
        ]);
    }
    
    /**
     * Calculate return points
     */
    private function calculateReturnPoints($condition, $onTime) {
        $points = 0;
        
        if ($onTime) {
            // Points for on-time return based on condition
            switch ($condition) {
                case 'excellent':
                    $points = 15;
                    break;
                case 'good':
                    $points = 10;
                    break;
                case 'fair':
                    $points = 5;
                    break;
                case 'poor':
                    $points = 0;
                    break;
                case 'damaged':
                    $points = -10; // Deduction even if on time
                    break;
            }
        } else {
            // Deductions for late return
            switch ($condition) {
                case 'excellent':
                    $points = -5; // Small deduction for late but excellent condition
                    break;
                case 'good':
                    $points = -10;
                    break;
                case 'fair':
                    $points = -15;
                    break;
                case 'poor':
                    $points = -25;
                    break;
                case 'damaged':
                    $points = -40; // Heavy deduction
                    break;
            }
        }
        
        return $points;
    }
    
    /**
     * Recalculate rating and borrowing level
     */
    public function recalculateRating($studentId) {
        $rating = $this->getByStudent($studentId);
        if (!$rating) {
            return;
        }
        
        $totalReturns = intval($rating['total_returns']);
        $onTimeReturns = intval($rating['on_time_returns']);
        $damagedBooks = intval($rating['damaged_books']);
        $lostBooks = intval($rating['lost_books']);
        $totalPoints = intval($rating['total_points']);
        
        // Calculate rating (out of 5.00)
        $ratingValue = 5.00;
        
        if ($totalReturns > 0) {
            // Base rating on on-time return percentage
            $onTimePercentage = ($onTimeReturns / $totalReturns) * 100;
            $ratingValue = ($onTimePercentage / 100) * 5.00;
            
            // Deduct for damaged books (each damaged book reduces rating by 0.5)
            $ratingValue -= ($damagedBooks * 0.5);
            
            // Deduct for lost books (each lost book reduces rating by 1.0)
            $ratingValue -= ($lostBooks * 1.0);
            
            // Ensure rating is between 0 and 5
            $ratingValue = max(0, min(5.00, $ratingValue));
        }
        
        // Determine borrowing level based on points and rating
        $borrowingLevel = 'good';
        $maxBorrows = 3;
        
        if ($totalPoints >= 200 && $ratingValue >= 4.5) {
            $borrowingLevel = 'excellent';
            $maxBorrows = 5;
        } elseif ($totalPoints >= 150 && $ratingValue >= 4.0) {
            $borrowingLevel = 'good';
            $maxBorrows = 4;
        } elseif ($totalPoints >= 100 && $ratingValue >= 3.0) {
            $borrowingLevel = 'fair';
            $maxBorrows = 3;
        } elseif ($totalPoints >= 50 && $ratingValue >= 2.0) {
            $borrowingLevel = 'poor';
            $maxBorrows = 2;
        } else {
            $borrowingLevel = 'restricted';
            $maxBorrows = 1;
        }
        
        $this->update($rating['id'], [
            'rating' => round($ratingValue, 2),
            'borrowing_level' => $borrowingLevel,
            'max_borrows' => $maxBorrows
        ]);
    }
    
    /**
     * Check if student can borrow more books
     */
    public function canBorrow($studentId) {
        $rating = $this->getOrCreate($studentId);
        $borrowModel = $this->model('BookBorrow');
        
        $activeBorrows = $borrowModel->getActiveBorrowsByStudent($studentId);
        $activeCount = count($activeBorrows);
        
        return $activeCount < intval($rating['max_borrows']);
    }
    
    /**
     * Get borrowing limit for student
     */
    public function getBorrowingLimit($studentId) {
        $rating = $this->getOrCreate($studentId);
        return intval($rating['max_borrows']);
    }
}

