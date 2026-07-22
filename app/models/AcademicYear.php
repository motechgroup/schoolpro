<?php
/**
 * Academic Year Model
 * Handles academic year and term management
 */

class AcademicYear extends Model {
    protected $table = 'academic_years';
    
    /**
     * Get all academic years
     */
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY start_date DESC");
        return $stmt->fetchAll();
    }
    
    /**
     * Get current academic year
     */
    public function getCurrent() {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} 
                                   WHERE start_date <= ? AND end_date >= ? 
                                   AND status = 'active' 
                                   ORDER BY start_date DESC LIMIT 1");
        $stmt->execute([$today, $today]);
        return $stmt->fetch();
    }
    
    /**
     * Get academic year by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get terms for an academic year
     */
    public function getTerms($academicYearId) {
        $stmt = $this->db->prepare("SELECT * FROM terms 
                                   WHERE academic_year_id = ? 
                                   ORDER BY term_number ASC");
        $stmt->execute([$academicYearId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get current term
     */
    public function getCurrentTerm() {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("SELECT t.*, ay.name as academic_year_name 
                                   FROM terms t
                                   JOIN academic_years ay ON t.academic_year_id = ay.id
                                   WHERE t.start_date <= ? AND t.end_date >= ? 
                                   AND t.status = 'active' 
                                   ORDER BY t.start_date DESC LIMIT 1");
        $stmt->execute([$today, $today]);
        return $stmt->fetch();
    }
    
    /**
     * Get term by ID
     */
    public function getTermById($termId) {
        $stmt = $this->db->prepare("SELECT t.*, ay.name as academic_year_name 
                                   FROM terms t
                                   JOIN academic_years ay ON t.academic_year_id = ay.id
                                   WHERE t.id = ?");
        $stmt->execute([$termId]);
        return $stmt->fetch();
    }
    
    /**
     * Create academic year
     */
    public function create($data) {
        // Ensure only one current academic year
        if (!empty($data['is_current']) && $data['is_current']) {
            $this->db->query("UPDATE {$this->table} SET is_current = FALSE");
        }
        
        $stmt = $this->db->prepare("INSERT INTO {$this->table} 
                                   (name, start_date, end_date, status, is_current, created_by) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([
            $data['name'],
            $data['start_date'],
            $data['end_date'],
            $data['status'] ?? 'upcoming',
            $data['is_current'] ?? false,
            $data['created_by'] ?? null
        ])) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Update academic year
     */
    public function update($id, $data) {
        // Ensure only one current academic year
        if (!empty($data['is_current']) && $data['is_current']) {
            $this->db->query("UPDATE {$this->table} SET is_current = FALSE WHERE id != ?");
        }
        
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        
        $values[] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * Create term
     */
    public function createTerm($data) {
        // Ensure only one current term
        if (!empty($data['is_current']) && $data['is_current']) {
            $this->db->query("UPDATE terms SET is_current = FALSE");
        }
        
        $stmt = $this->db->prepare("INSERT INTO terms 
                                   (academic_year_id, term_number, name, start_date, end_date, status, is_current, created_by) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([
            $data['academic_year_id'],
            $data['term_number'],
            $data['name'],
            $data['start_date'],
            $data['end_date'],
            $data['status'] ?? 'upcoming',
            $data['is_current'] ?? false,
            $data['created_by'] ?? null
        ])) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Update term
     */
    public function updateTerm($id, $data) {
        // Ensure only one current term
        if (!empty($data['is_current']) && $data['is_current']) {
            $this->db->query("UPDATE terms SET is_current = FALSE WHERE id != ?");
        }
        
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        
        $values[] = $id;
        $sql = "UPDATE terms SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * Delete academic year (and its terms)
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Delete term
     */
    public function deleteTerm($id) {
        $stmt = $this->db->prepare("DELETE FROM terms WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Check if school is open (within any active term)
     */
    public function isSchoolOpen() {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM terms 
                                   WHERE start_date <= ? AND end_date >= ? 
                                   AND status = 'active'");
        $stmt->execute([$today, $today]);
        $result = $stmt->fetch();
        return ($result['count'] ?? 0) > 0;
    }
}

