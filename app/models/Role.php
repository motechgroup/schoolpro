<?php
/**
 * Role Model
 */

class Role extends Model {
    protected $table = 'roles';
    
    /**
     * Get role with permissions
     */
    public function getRoleWithPermissions($id) {
        // Use the EXACT same pattern as getAllWithDetails() which we know works
        $id = intval($id);
        if ($id <= 0) {
            return null;
        }
        
        // Use the exact same query structure as getAllWithDetails() but filter by ID
        $sql = "SELECT r.*, COUNT(u.id) as user_count
                FROM roles r
                LEFT JOIN users u ON r.id = u.role_id AND u.status = 'active'
                WHERE r.id = ?
                GROUP BY r.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if role was found
        if (!$role || !is_array($role)) {
            return null;
        }
        
        // Ensure required fields exist (same check as getAllWithDetails())
        if (!isset($role['id'])) {
            return null;
        }
        
        // Decode permissions JSON to array (EXACT same logic as getAllWithDetails())
        if (!empty($role['permissions'])) {
            if (is_string($role['permissions'])) {
                $decoded = json_decode($role['permissions'], true);
                $role['permissions'] = $decoded !== null ? $decoded : [];
            } elseif (!is_array($role['permissions'])) {
                $role['permissions'] = [];
            }
        } else {
            $role['permissions'] = [];
        }
        
        // Ensure all required fields exist
        if (!isset($role['name'])) {
            $role['name'] = '';
        }
        if (!isset($role['description'])) {
            $role['description'] = '';
        }
        
        return $role;
    }
    
    /**
     * Get all roles with permission counts
     */
    public function getAllWithDetails() {
        $sql = "SELECT r.*, COUNT(u.id) as user_count
                FROM roles r
                LEFT JOIN users u ON r.id = u.role_id AND u.status = 'active'
                GROUP BY r.id
                ORDER BY r.name";
        
        $stmt = $this->db->query($sql);
        $roles = $stmt->fetchAll();
        
        foreach ($roles as &$role) {
            // Ensure permissions is always an array
            if (!empty($role['permissions'])) {
                if (is_string($role['permissions'])) {
                    $decoded = json_decode($role['permissions'], true);
                    $role['permissions'] = $decoded !== null ? $decoded : [];
                } elseif (!is_array($role['permissions'])) {
                    $role['permissions'] = [];
                }
            } else {
                $role['permissions'] = [];
            }
            
            // Ensure required fields exist
            if (!isset($role['id'])) {
                continue; // Skip invalid roles
            }
        }
        
        return $roles;
    }
    
    /**
     * Update role permissions
     */
    public function updatePermissions($roleId, $permissions) {
        $permissionsJson = json_encode($permissions);
        return $this->update($roleId, ['permissions' => $permissionsJson]);
    }
    
    /**
     * Get all available permissions
     */
    public static function getAvailablePermissions() {
        return [
            // Students
            'students.view' => 'View Students',
            'students.create' => 'Create Students',
            'students.edit' => 'Edit Students',
            'students.delete' => 'Delete Students',
            
            // Teachers
            'teachers.view' => 'View Teachers',
            'teachers.create' => 'Create Teachers',
            'teachers.edit' => 'Edit Teachers',
            'teachers.delete' => 'Delete Teachers',
            
            // Parents
            'parents.view' => 'View Parents',
            'parents.create' => 'Create Parents',
            'parents.edit' => 'Edit Parents',
            'parents.delete' => 'Delete Parents',
            
            // Classes
            'classes.view' => 'View Classes',
            'classes.create' => 'Create Classes',
            'classes.edit' => 'Edit Classes',
            'classes.delete' => 'Delete Classes',
            
            // Grades
            'grades.view' => 'View Grades',
            'grades.create' => 'Create Grades',
            'grades.edit' => 'Edit Grades',
            'grades.delete' => 'Delete Grades',
            
            // Attendance
            'attendance.view' => 'View Attendance',
            'attendance.create' => 'Mark Attendance',
            'attendance.edit' => 'Edit Attendance',
            'attendance.delete' => 'Delete Attendance',
            
            // Assessments
            'assessments.view' => 'View Assessments',
            'assessments.create' => 'Create Assessments',
            'assessments.edit' => 'Edit Assessments',
            'assessments.delete' => 'Delete Assessments',
            
            // Fees
            'fees.view' => 'View Fees',
            'fees.create' => 'Create Fees',
            'fees.edit' => 'Edit Fees',
            'fees.delete' => 'Delete Fees',
            'fees.assign' => 'Assign Fees to Students',
            'feeheads.view' => 'View Fee Heads',
            'feeheads.create' => 'Create Fee Heads',
            'feeheads.edit' => 'Edit Fee Heads',
            'feeheads.delete' => 'Delete Fee Heads',
            
            // Payments
            'payments.view' => 'View Payments',
            'payments.create' => 'Create Payments',
            'payments.edit' => 'Edit Payments',
            'payments.delete' => 'Delete Payments',
            'payments.reconcile' => 'Reconcile Payments',
            
            // Reports
            'reports.view' => 'View Reports',
            'reports.financial' => 'Financial Reports',
            'reports.academic' => 'Academic Reports',
            'reports.attendance' => 'Attendance Reports',
            
            // Examinations
            'examinations.view' => 'View Examinations',
            'examinations.create' => 'Create Examinations',
            'examinations.edit' => 'Edit Examinations',
            'examinations.marks' => 'Enter Marks',
            'examinations.reports' => 'View Report Cards',
            
            // Subjects
            'subjects.view' => 'View Subjects',
            'subjects.create' => 'Create Subjects',
            'subjects.edit' => 'Edit Subjects',
            'subjects.delete' => 'Delete Subjects',
            
            // Communication
            'communication.view' => 'View Communication',
            'communication.send' => 'Send SMS',
            'communication.settings' => 'SMS Settings',
            
            // Settings
            'settings.view' => 'View Settings',
            'settings.edit' => 'Edit Settings',
            
            // Users (Super Admin only)
            'users.view' => 'View Users',
            'users.create' => 'Create Users',
            'users.edit' => 'Edit Users',
            'users.delete' => 'Delete Users',
            
            // Roles
            'roles.view' => 'View Roles',
            'roles.edit' => 'Edit Roles',
            
            // Announcements
            'announcements.view' => 'View Announcements',
            'announcements.create' => 'Create Announcements',
            'announcements.edit' => 'Edit Announcements',
            'announcements.delete' => 'Delete Announcements',
            
            // Library
            'library.view' => 'View Library',
            'library.books.view' => 'View Books',
            'library.books.create' => 'Create Books',
            'library.books.edit' => 'Edit Books',
            'library.books.delete' => 'Delete Books',
            'library.borrow.view' => 'View Borrows',
            'library.borrow.create' => 'Assign Books',
            'library.borrow.return' => 'Return Books',
            'library.reports.view' => 'View Library Reports',
        ];
    }
}
