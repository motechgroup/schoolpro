<?php
/**
 * Email Template Helper
 * Handles email template processing and variable replacement
 */

class EmailTemplateHelper {
    
    /**
     * Get common variables for parents
     */
    public static function getParentVariables($parentId, $studentId = null) {
        $db = Database::getInstance()->getConnection();
        $variables = [];
        
        // Get parent info
        $stmt = $db->prepare("SELECT first_name, last_name, email, phone FROM parents WHERE id = ?");
        $stmt->execute([$parentId]);
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($parent) {
            $variables['parent_name'] = trim($parent['first_name'] . ' ' . $parent['last_name']);
            $variables['parent_email'] = $parent['email'] ?? '';
            $variables['parent_phone'] = $parent['phone'] ?? '';
        }
        
        // Get student info if provided
        if ($studentId) {
            $stmt = $db->prepare("SELECT s.*, c.name as class_name, g.display_name as grade_name 
                FROM students s 
                LEFT JOIN classes c ON s.class_id = c.id 
                LEFT JOIN grades g ON c.grade_id = g.id 
                WHERE s.id = ?");
            $stmt->execute([$studentId]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($student) {
                $variables['student_name'] = trim($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']);
                $variables['admission_number'] = $student['admission_number'];
                $variables['class_name'] = $student['class_name'] ?? '';
                $variables['grade_name'] = $student['grade_name'] ?? '';
            }
        }
        
        // Get fee balance for parent
        require_once APP_PATH . '/models/ParentModel.php';
        $parentModel = new ParentModel();
        $parentWithDetails = $parentModel->getParentWithDetails($parentId);
        $totalBalance = $parentWithDetails['total_balance'] ?? 0;
        $variables['balance_amount'] = number_format($totalBalance, 2);
        
        // Get fee-related variables (term, academic year)
        $feeVars = self::getFeeVariables($studentId);
        $variables = array_merge($variables, $feeVars);
        
        // Get school info
        $schoolVars = self::getSchoolVariables();
        $variables = array_merge($variables, $schoolVars);
        
        return $variables;
    }
    
    /**
     * Get common variables for teachers
     */
    public static function getTeacherVariables($teacherId) {
        $db = Database::getInstance()->getConnection();
        $variables = [];
        
        // Get teacher info
        $stmt = $db->prepare("SELECT first_name, last_name, email, phone FROM teachers WHERE id = ?");
        $stmt->execute([$teacherId]);
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($teacher) {
            $variables['teacher_name'] = trim($teacher['first_name'] . ' ' . $teacher['last_name']);
            $variables['recipient_name'] = $variables['teacher_name'];
            $variables['teacher_email'] = $teacher['email'] ?? '';
            $variables['teacher_phone'] = $teacher['phone'] ?? '';
        }
        
        // Get school info
        $schoolVars = self::getSchoolVariables();
        $variables = array_merge($variables, $schoolVars);
        
        return $variables;
    }
    
    /**
     * Get school variables
     */
    public static function getSchoolVariables() {
        $db = Database::getInstance()->getConnection();
        $variables = [];
        
        $keys = ['school_name', 'school_address', 'school_phone', 'school_email'];
        foreach ($keys as $key) {
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $varKey = str_replace('school_', '', $key);
            $variables[$varKey] = $result['setting_value'] ?? '';
            // Also add with school_ prefix for consistency
            $variables[$key] = $result['setting_value'] ?? '';
        }
        
        // Get payment info
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'mpesa_paybill_number'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $variables['paybill_number'] = $result['setting_value'] ?? '';
        
        // Get bank details
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE '%_bank_%'");
        $bankDetails = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (strpos($row['setting_key'], 'bank_name') !== false || strpos($row['setting_key'], 'bank_account') !== false) {
                $bankDetails[] = $row['setting_value'];
            }
        }
        $variables['bank_details'] = implode(', ', $bankDetails);
        
        return $variables;
    }
    
    /**
     * Get fee-related variables
     */
    public static function getFeeVariables($studentId, $invoiceId = null) {
        $db = Database::getInstance()->getConnection();
        $variables = [];
        
        // Get current academic year and term
        $stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'current_academic_year' LIMIT 1");
        $yearResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $academicYear = $yearResult['setting_value'] ?? date('Y');
        
        $stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'current_term' LIMIT 1");
        $termResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $term = $termResult['setting_value'] ?? '1';
        
        $variables['academic_year'] = $academicYear;
        $variables['term'] = $term;
        
        // Get invoice info if provided
        if ($invoiceId) {
            require_once APP_PATH . '/models/Invoice.php';
            $invoiceModel = new Invoice();
            $invoice = $invoiceModel->findById($invoiceId);
            
            if ($invoice) {
                $variables['balance_amount'] = number_format($invoice['balance'], 2);
                $variables['total_amount'] = number_format($invoice['total_amount'], 2);
                $variables['paid_amount'] = number_format($invoice['paid_amount'], 2);
            }
        }
        
        return $variables;
    }
    
    /**
     * Process template with variables
     */
    public static function processTemplate($template, $variables) {
        $subject = $template['subject'];
        $body = $template['body'];
        
        foreach ($variables as $key => $value) {
            $subject = str_replace('{' . $key . '}', $value ?? '', $subject);
            $body = str_replace('{' . $key . '}', $value ?? '', $body);
        }
        
        // Remove any remaining unreplaced variables
        $subject = preg_replace('/\{[^}]+\}/', '', $subject);
        $body = preg_replace('/\{[^}]+\}/', '', $body);
        
        return [
            'subject' => $subject,
            'body' => $body
        ];
    }
}

