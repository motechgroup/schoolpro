<?php
/**
 * Report Card Helper
 * Handles sending report cards via SMS (summarized) and Email (PDF detailed)
 */

class ReportCardHelper {
    
    /**
     * Send summarized report card via SMS
     * 
     * @param int $studentId
     * @param array $examination
     * @param array $student
     * @param array $marks
     * @param array $subjects
     * @param float $totalMarks
     * @param float $totalMaxMarks
     * @param string $overallGrade
     * @param float $overallPercentage
     * @return array
     */
    public static function sendReportCardSms($studentId, $examination, $student, $marks, $subjects, $totalMarks, $totalMaxMarks, $overallGrade, $overallPercentage) {
        try {
            require_once APP_PATH . '/helpers/SmsHelper.php';
            
            $db = Database::getInstance()->getConnection();
            
            // Get parent phone number
            $stmt = $db->prepare("SELECT p.phone, p.first_name, p.last_name, p.email
                FROM students s
                LEFT JOIN parents p ON p.id = s.parent_id AND p.status = 'active'
                WHERE s.id = ?
                LIMIT 1");
            $stmt->execute([$studentId]);
            $parent = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$parent || empty($parent['phone'])) {
                return [
                    'success' => false,
                    'message' => 'Parent phone number not found for this student'
                ];
            }
            
            // Get school name
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'school_name'");
            $stmt->execute();
            $schoolResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $schoolName = $schoolResult['setting_value'] ?? APP_NAME;
            
            $studentName = trim($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']);
            $parentName = trim($parent['first_name'] . ' ' . $parent['last_name']);
            
            // Build summarized SMS message
            $message = "REPORT CARD - {$schoolName}\n";
            $message .= "Student: {$studentName}\n";
            $message .= "Exam: {$examination['name']}\n";
            $message .= "Term {$examination['term']} - {$examination['academic_year']}\n";
            $message .= "Overall: {$overallPercentage}% (Grade: {$overallGrade})\n";
            $message .= "Marks: " . number_format($totalMarks, 2) . "/" . number_format($totalMaxMarks, 2) . "\n";
            
            // Add top 3 subjects (if available)
            if (!empty($marks)) {
                $subjectScores = [];
                foreach ($marks as $mark) {
                    $maxMarks = floatval($mark['max_marks']);
                    if ($maxMarks > 0) {
                        $percentage = round((floatval($mark['marks_obtained']) / $maxMarks) * 100, 1);
                        $subjectScores[] = [
                            'name' => $mark['learning_area_name'],
                            'percentage' => $percentage,
                            'grade' => $mark['grade']
                        ];
                    }
                }
                
                // Sort by percentage descending
                usort($subjectScores, function($a, $b) {
                    return $b['percentage'] <=> $a['percentage'];
                });
                
                // Add top 3 subjects
                $topSubjects = array_slice($subjectScores, 0, 3);
                if (!empty($topSubjects)) {
                    $message .= "\nTop Subjects:\n";
                    foreach ($topSubjects as $subj) {
                        $message .= "{$subj['name']}: {$subj['percentage']}% ({$subj['grade']})\n";
                    }
                }
            }
            
            $message .= "\nDetailed report card sent via email.";
            
            // Send SMS
            $smsHelper = new SmsHelper();
            $result = $smsHelper->sendSms($parent['phone'], $message);
            
            if ($result['success']) {
                error_log("Report Card SMS: Successfully sent to " . $parent['phone']);
            } else {
                error_log("Report Card SMS: Failed to send to " . $parent['phone'] . " - " . ($result['message'] ?? 'Unknown error'));
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Report Card SMS Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error sending SMS: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate PDF report card and send via email
     * 
     * @param int $studentId
     * @param array $examination
     * @param array $student
     * @param array $marks
     * @param array $subjects
     * @param float $totalMarks
     * @param float $totalMaxMarks
     * @param string $overallGrade
     * @param float $overallPercentage
     * @param array|null $classTeacher
     * @param array|null $headTeacher
     * @param string|null $schoolName
     * @param array $schoolSettings
     * @return array
     */
    public static function sendReportCardEmail($studentId, $examination, $student, $marks, $subjects, $totalMarks, $totalMaxMarks, $overallGrade, $overallPercentage, $classTeacher = null, $headTeacher = null, $schoolName = null, $schoolSettings = []) {
        try {
            require_once APP_PATH . '/helpers/EmailHelper.php';
            
            $db = Database::getInstance()->getConnection();
            
            // Get parent email
            $stmt = $db->prepare("SELECT p.phone, p.first_name, p.last_name, p.email
                FROM students s
                LEFT JOIN parents p ON p.id = s.parent_id AND p.status = 'active'
                WHERE s.id = ?
                LIMIT 1");
            $stmt->execute([$studentId]);
            $parent = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$parent || empty($parent['email'])) {
                return [
                    'success' => false,
                    'message' => 'Parent email address not found for this student'
                ];
            }
            
            // Get class teacher and head teacher information
            $classTeacher = null;
            $headTeacher = null;
            $schoolName = getSchoolName();
            $schoolSettings = [];
            
            try {
                $db = Database::getInstance()->getConnection();
                
                // Get class teacher
                $classStmt = $db->prepare("SELECT c.class_teacher_id, t.first_name, t.last_name
                                          FROM classes c
                                          LEFT JOIN teachers t ON c.class_teacher_id = t.id
                                          WHERE c.id = ?");
                $classStmt->execute([$student['class_id']]);
                $class = $classStmt->fetch();
                if ($class && !empty($class['class_teacher_id'])) {
                    $classTeacher = [
                        'first_name' => $class['first_name'],
                        'last_name' => $class['last_name']
                    ];
                }
                
                // Get head teacher
                $headTeacherStmt = $db->prepare("SELECT u.first_name, u.last_name, t.first_name as teacher_first_name, t.last_name as teacher_last_name
                                                FROM users u
                                                LEFT JOIN roles r ON u.role_id = r.id
                                                LEFT JOIN teachers t ON t.user_id = u.id
                                                WHERE r.name = 'head_teacher' AND u.status = 'active'
                                                LIMIT 1");
                $headTeacherStmt->execute();
                $headTeacherData = $headTeacherStmt->fetch();
                if ($headTeacherData) {
                    $headTeacher = [
                        'first_name' => $headTeacherData['teacher_first_name'] ?: $headTeacherData['first_name'],
                        'last_name' => $headTeacherData['teacher_last_name'] ?: $headTeacherData['last_name']
                    ];
                }
                
                // Get school settings
                $settingsStmt = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('school_name', 'school_address', 'school_phone', 'school_email')");
                foreach ($settingsStmt->fetchAll() as $setting) {
                    $schoolSettings[$setting['setting_key']] = $setting['setting_value'];
                }
            } catch (Exception $e) {
                error_log("Error fetching teacher/school info: " . $e->getMessage());
            }
            
            // Generate PDF
            $pdfPath = self::generateReportCardPdf($studentId, $examination, $student, $marks, $subjects, $totalMarks, $totalMaxMarks, $overallGrade, $overallPercentage, $classTeacher, $headTeacher, $schoolName, $schoolSettings);
            
            if (!$pdfPath) {
                // Check if dompdf is installed
                $dompdfInstalled = class_exists('Dompdf\Dompdf') || class_exists('Dompdf\Dompdf\Dompdf');
                $errorMsg = 'Failed to generate PDF report card. ';
                
                if (!$dompdfInstalled) {
                    $errorMsg .= 'Please install dompdf by running: composer require dompdf/dompdf';
                } else {
                    $errorMsg .= 'Please check server logs for details.';
                }
                
                return [
                    'success' => false,
                    'message' => $errorMsg
                ];
            }
            
            // Get school name
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'school_name'");
            $stmt->execute();
            $schoolResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $schoolName = $schoolResult['setting_value'] ?? APP_NAME;
            
            $studentName = trim($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']);
            $parentName = trim($parent['first_name'] . ' ' . $parent['last_name']);
            
            // Prepare email
            $subject = "Report Card - {$studentName} - {$examination['name']}";
            $emailBody = "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                        <h2 style='color: #2c3e50;'>Report Card - {$schoolName}</h2>
                        <p>Dear {$parentName},</p>
                        <p>Please find attached the detailed report card for <strong>{$studentName}</strong> for the {$examination['name']} examination (Term {$examination['term']}, {$examination['academic_year']}).</p>
                        <p><strong>Overall Performance:</strong> {$overallPercentage}% (Grade: {$overallGrade})</p>
                        <p><strong>Total Marks:</strong> " . number_format($totalMarks, 2) . " / " . number_format($totalMaxMarks, 2) . "</p>
                        <p>For any questions or concerns, please contact the school administration.</p>
                        <p>Best regards,<br>{$schoolName}</p>
                    </div>
                </body>
                </html>
            ";
            
            // Send email with PDF attachment
            $emailHelper = new EmailHelper();
            
            // Try PHPMailer first (supports attachments), fallback to basic mail
            if (method_exists($emailHelper, 'sendEmailWithAttachment')) {
                $result = $emailHelper->sendEmailWithAttachment(
                    $parent['email'],
                    $subject,
                    $emailBody,
                    $pdfPath,
                    'Report_Card_' . $student['admission_number'] . '_' . date('Y-m-d') . '.pdf'
                );
            } else {
                // Fallback: send email with link to download PDF
                $emailBody .= "<p><strong>Note:</strong> PDF attachment requires PHPMailer. Please contact the school to receive the PDF report card.</p>";
                $result = $emailHelper->sendEmailWithPHPMailer($parent['email'], $subject, $emailBody, true);
            }
            
            // Clean up temporary PDF file
            if (file_exists($pdfPath)) {
                @unlink($pdfPath);
            }
            
            if ($result) {
                error_log("Report Card Email: Successfully sent to " . $parent['email']);
                return [
                    'success' => true,
                    'message' => 'Report card sent successfully via email'
                ];
            } else {
                error_log("Report Card Email: Failed to send to " . $parent['email']);
                return [
                    'success' => false,
                    'message' => 'Failed to send email'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Report Card Email Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error sending email: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate PDF report card
     * Uses HTML to PDF conversion (dompdf or TCPDF if available, otherwise creates HTML file)
     * 
     * @return string|false Path to generated PDF file or false on failure
     */
    private static function generateReportCardPdf($studentId, $examination, $student, $marks, $subjects, $totalMarks, $totalMaxMarks, $overallGrade, $overallPercentage, $classTeacher = null, $headTeacher = null, $schoolName = null, $schoolSettings = []) {
        try {
            // Create HTML content for PDF
            ob_start();
            // Pass variables to the view
            $classTeacher = $classTeacher;
            $headTeacher = $headTeacher;
            $schoolName = $schoolName ?: getSchoolName();
            $schoolSettings = $schoolSettings;
            include APP_PATH . '/views/examinations/report_card_pdf.php';
            $html = ob_get_clean();
            
            if (empty($html)) {
                error_log("PDF Generation Error: Empty HTML content generated");
                return false;
            }
            
            // Try to use dompdf if available (v2.0+)
            if (class_exists('Dompdf\Dompdf')) {
                try {
                    $dompdf = new Dompdf\Dompdf();
                    $dompdf->loadHtml($html);
                    $dompdf->setPaper('A4', 'portrait');
                    $dompdf->render();
                    
                    $pdfPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'report_card_' . $studentId . '_' . time() . '.pdf';
                    $pdfContent = $dompdf->output();
                    
                    if (empty($pdfContent)) {
                        error_log("PDF Generation Error: dompdf output is empty");
                        return false;
                    }
                    
                    if (file_put_contents($pdfPath, $pdfContent) === false) {
                        error_log("PDF Generation Error: Failed to write PDF file to: " . $pdfPath);
                        return false;
                    }
                    
                    if (!file_exists($pdfPath)) {
                        error_log("PDF Generation Error: PDF file was not created at: " . $pdfPath);
                        return false;
                    }
                    
                    // Verify file is readable and has content
                    if (filesize($pdfPath) === 0) {
                        error_log("PDF Generation Error: PDF file is empty");
                        @unlink($pdfPath);
                        return false;
                    }
                    
                    return $pdfPath;
                } catch (Exception $e) {
                    error_log("Dompdf Error: " . $e->getMessage());
                    error_log("Dompdf Stack trace: " . $e->getTraceAsString());
                    // Continue to try other methods
                } catch (Error $e) {
                    error_log("Dompdf Fatal Error: " . $e->getMessage());
                    error_log("Dompdf Stack trace: " . $e->getTraceAsString());
                    // Continue to try other methods
                }
            }
            
            // Try TCPDF if available
            if (class_exists('TCPDF')) {
                try {
                    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                    $pdf->SetCreator(APP_NAME);
                    $pdf->SetAuthor(APP_NAME);
                    $pdf->SetTitle('Report Card - ' . $student['first_name'] . ' ' . $student['last_name']);
                    $pdf->AddPage();
                    $pdf->writeHTML($html, true, false, true, false, '');
                    
                    $pdfPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'report_card_' . $studentId . '_' . time() . '.pdf';
                    $pdf->Output($pdfPath, 'F');
                    
                    if (file_exists($pdfPath)) {
                        return $pdfPath;
                    }
                } catch (Exception $e) {
                    error_log("TCPDF Error: " . $e->getMessage());
                    // Continue to try other methods
                }
            }
            
            // Fallback: Use wkhtmltopdf if available (system command)
            $htmlPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'report_card_' . $studentId . '_' . time() . '.html';
            if (file_put_contents($htmlPath, $html) === false) {
                error_log("PDF Generation Error: Failed to write HTML file");
                return false;
            }
            
            $pdfPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'report_card_' . $studentId . '_' . time() . '.pdf';
            $command = "wkhtmltopdf --page-size A4 --orientation Portrait \"{$htmlPath}\" \"{$pdfPath}\" 2>&1";
            exec($command, $output, $returnVar);
            
            @unlink($htmlPath);
            
            if ($returnVar === 0 && file_exists($pdfPath)) {
                return $pdfPath;
            }
            
            // Last resort: return HTML file path (can be converted manually)
            error_log("PDF generation failed. No PDF library available. Tried dompdf, TCPDF, and wkhtmltopdf. HTML saved to: " . $htmlPath);
            error_log("Error details: " . implode("\n", $output));
            return false;
            
        } catch (Exception $e) {
            error_log("PDF Generation Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
}

