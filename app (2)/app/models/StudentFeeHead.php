<?php
/**
 * Student Fee Head Model
 * Links students to fee heads
 */

class StudentFeeHead extends Model {
    protected $table = 'student_fee_heads';
    
    /**
     * Get fee heads assigned to student for term
     */
    public function getStudentFeeHeads($studentId, $term, $academicYear) {
        $sql = "SELECT sfh.*, 
                       fh.code as fee_head_code,
                       fh.name as fee_head_name,
                       fh.description as fee_head_description
                FROM student_fee_heads sfh
                LEFT JOIN fee_heads fh ON sfh.fee_head_id = fh.id
                WHERE sfh.student_id = ? AND sfh.term = ? AND sfh.academic_year = ? AND sfh.status = 'active'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId, $term, $academicYear]);
        return $stmt->fetchAll();
    }
    
    /**
     * Calculate total fees for student
     */
    public function calculateTotalFees($studentId, $term, $academicYear) {
        $sql = "SELECT SUM(amount) as total FROM {$this->table} 
                WHERE student_id = ? AND term = ? AND academic_year = ? AND status = 'active'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId, $term, $academicYear]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Assign fee heads to student
     * @param int $studentId Student ID
     * @param array $feeHeads Array of fee_head_id => amount pairs
     * @param int $term Term (1, 2, or 3)
     * @param string $academicYear Academic year (e.g., "2025/2026")
     * @return bool Success status
     */
    public function assignFeeHeads($studentId, $feeHeads, $term, $academicYear) {
        $this->db->beginTransaction();
        
        try {
            // Get existing assignments to preserve those with payments
            $existingStmt = $this->db->prepare("SELECT id, fee_head_id, amount FROM {$this->table} 
                                                WHERE student_id = ? AND term = ? AND academic_year = ? AND status = 'active'");
            $existingStmt->execute([$studentId, $term, $academicYear]);
            $existing = $existingStmt->fetchAll();
            
            $existingFeeHeadIds = [];
            $existingWithPayments = [];
            
            // Check which existing assignments have payments (these must be preserved)
            foreach ($existing as $existingAssignment) {
                $existingFeeHeadIds[] = $existingAssignment['fee_head_id'];
                
                // Check if this assignment has payments
                $paymentCheckStmt = $this->db->prepare("SELECT COUNT(*) as count FROM fee_head_payments WHERE student_fee_head_id = ?");
                $paymentCheckStmt->execute([$existingAssignment['id']]);
                $paymentCount = $paymentCheckStmt->fetch()['count'] ?? 0;
                
                if ($paymentCount > 0) {
                    $existingWithPayments[$existingAssignment['fee_head_id']] = $existingAssignment['amount'];
                }
            }
            
            // Deactivate assignments that are no longer selected (except those with payments)
            $feeHeadIdsToKeep = array_keys($feeHeads);
            $feeHeadIdsToKeep = array_merge($feeHeadIdsToKeep, array_keys($existingWithPayments)); // Always keep those with payments
            
            foreach ($existingFeeHeadIds as $existingFeeHeadId) {
                if (!in_array($existingFeeHeadId, $feeHeadIdsToKeep)) {
                    // This fee head is being removed and has no payments, so deactivate it
                    $deactivateStmt = $this->db->prepare("UPDATE {$this->table} 
                                                          SET status = 'inactive' 
                                                          WHERE student_id = ? AND fee_head_id = ? AND term = ? AND academic_year = ?");
                    $deactivateStmt->execute([$studentId, $existingFeeHeadId, $term, $academicYear]);
                }
            }
            
            // Insert/update new assignments
            $insertStmt = $this->db->prepare("INSERT INTO {$this->table} 
                                              (student_id, fee_head_id, amount, term, academic_year, status) 
                                              VALUES (?, ?, ?, ?, ?, 'active')
                                              ON DUPLICATE KEY UPDATE 
                                              amount = VALUES(amount), status = 'active'");
            
            foreach ($feeHeads as $feeHeadId => $amount) {
                $amount = floatval($amount);
                if ($amount > 0) {
                    // If this fee head has payments, preserve the existing amount
                    if (isset($existingWithPayments[$feeHeadId])) {
                        $insertStmt->execute([$studentId, $feeHeadId, $existingWithPayments[$feeHeadId], $term, $academicYear]);
                    } else {
                        $insertStmt->execute([$studentId, $feeHeadId, $amount, $term, $academicYear]);
                    }
                }
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error assigning fee heads: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get students with fee heads for term
     */
    public function getStudentsWithFeeHeads($term, $academicYear, $classId = null) {
        $sql = "SELECT s.id, s.first_name, s.last_name, s.admission_number,
                       c.name as class_name,
                       g.display_name as grade_display_name,
                       SUM(sfh.amount) as total_fees
                FROM students s
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN grades g ON c.grade_id = g.id
                LEFT JOIN student_fee_heads sfh ON s.id = sfh.student_id 
                    AND sfh.term = ? AND sfh.academic_year = ? AND sfh.status = 'active'
                WHERE s.status = 'active'";
        
        $params = [$term, $academicYear];
        
        if ($classId) {
            $sql .= " AND s.class_id = ?";
            $params[] = $classId;
        }
        
        $sql .= " GROUP BY s.id ORDER BY s.first_name, s.last_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

