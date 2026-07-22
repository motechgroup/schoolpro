<?php
/**
 * Payment Notification Helper
 * Sends SMS to parent after fee payment, including current fee status.
 */

class PaymentNotificationHelper {

    /**
     * Send fee payment SMS to a student's parent.
     *
     * @param int $studentId
     * @param float $amount
     * @param string $receiptNumber
     * @param int|null $invoiceId  Invoice ID for balance lookup (optional)
     */
    public static function sendFeePaymentSms($studentId, $amount, $receiptNumber, $invoiceId = null) {
        try {
            require_once APP_PATH . '/helpers/SmsHelper.php';

            $db = Database::getInstance()->getConnection();

            // Get parent phone + names and student name (using students.parent_id)
            $stmt = $db->prepare("SELECT 
                    s.first_name AS student_first_name,
                    s.last_name AS student_last_name,
                    p.phone,
                    p.first_name AS parent_first_name,
                    p.last_name AS parent_last_name
                FROM students s
                LEFT JOIN parents p ON p.id = s.parent_id AND p.status = 'active'
                WHERE s.id = ?
                LIMIT 1");
            $stmt->execute([$studentId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || empty($row['phone'])) {
                error_log("Payment SMS: No parent phone found for student ID: " . $studentId);
                return;
            }

            $studentName = trim(($row['student_first_name'] ?? '') . ' ' . ($row['student_last_name'] ?? ''));

            // Get school name
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'school_name'");
            $stmt->execute();
            $schoolResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $schoolName = $schoolResult['setting_value'] ?? APP_NAME;

            // Get current balance for this invoice if provided
            $balanceText = '';
            if ($invoiceId) {
                require_once APP_PATH . '/models/Invoice.php';
                $invoiceModel = new Invoice();
                $invoice = $invoiceModel->findById($invoiceId);
                if ($invoice) {
                    $balance = floatval($invoice['balance']);
                    if ($balance <= 0) {
                        $balanceText = ' Your fees for this term are fully paid.';
                    } else {
                        $balanceText = ' Outstanding balance: KES ' . number_format($balance, 2) . '.';
                    }
                }
            }

            // Format amount
            $formattedAmount = number_format($amount, 2);

            // Create SMS message
            $parentFirst = $row['parent_first_name'] ?? '';
            $message = "Dear {$parentFirst}, Payment of KES {$formattedAmount} received for {$studentName} (Receipt: {$receiptNumber}).{$balanceText} Thank you! - {$schoolName}";

            // Send SMS
            $smsHelper = new SmsHelper();
            $result = $smsHelper->sendSms($row['phone'], $message);

            if ($result['success']) {
                error_log("Payment SMS: Successfully sent to " . $row['phone']);
            } else {
                error_log("Payment SMS: Failed to send to " . $row['phone'] . " - " . ($result['message'] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            error_log("Payment SMS Error: " . $e->getMessage());
        }
    }
}


