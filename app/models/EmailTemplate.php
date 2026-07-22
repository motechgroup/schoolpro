<?php
/**
 * Email Template Model
 */

class EmailTemplate extends Model {
    protected $table = 'email_templates';
    
    /**
     * Get all active templates
     */
    public function getActiveTemplates($category = null) {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1";
        $params = [];
        
        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY category, name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get template by ID
     */
    public function getTemplate($id) {
        return $this->findById($id);
    }
    
    /**
     * Replace variables in template
     */
    public function replaceVariables($template, $variables) {
        $subject = $template['subject'];
        $body = $template['body'];
        
        foreach ($variables as $key => $value) {
            $subject = str_replace('{' . $key . '}', $value, $subject);
            $body = str_replace('{' . $key . '}', $value, $body);
        }
        
        return [
            'subject' => $subject,
            'body' => $body
        ];
    }
    
    /**
     * Get available variables for a template
     */
    public function getTemplateVariables($templateId) {
        $template = $this->findById($templateId);
        if (!$template || empty($template['variables'])) {
            return [];
        }
        
        $variables = json_decode($template['variables'], true);
        return is_array($variables) ? $variables : [];
    }
}

