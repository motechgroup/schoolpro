<?php
/**
 * Attendance Model
 */

class Attendance extends Model {
    protected $table = 'student_attendance';
    
    /**
     * Mark attendance for multiple students
     */
    public function markBulkAttendance($classId, $date, $attendanceData, $markedBy) {
        $this->db->beginTransaction();
        
        try {
            // Delete existing attendance for the date
            $deleteStmt = $this->db->prepare("DELETE FROM {$this->table} WHERE class_id = ? AND attendance_date = ?");
            $deleteStmt->execute([$classId, $date]);
            
            // Insert new attendance records
            $insertStmt = $this->db->prepare("INSERT INTO {$this->table} 
                                              (student_id, class_id, attendance_date, status, marked_by) 
                                              VALUES (?, ?, ?, ?, ?)");
            
            foreach ($attendanceData as $studentId => $status) {
                $insertStmt->execute([$studentId, $classId, $date, $status, $markedBy]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Get attendance for class and date
     */
    public function getClassAttendance($classId, $date) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE class_id = ? AND attendance_date = ?");
        $stmt->execute([$classId, $date]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get student attendance summary
     */
    public function getStudentAttendanceSummary($studentId, $startDate, $endDate) {
        $sql = "SELECT 
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days,
                    SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused_days
                FROM {$this->table}
                WHERE student_id = ? AND attendance_date BETWEEN ? AND ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId, $startDate, $endDate]);
        return $stmt->fetch();
    }
}

