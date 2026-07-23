<?php
/**
 * SchoolPro V2.0.0 Real Payment Allocation Script
 * Allocates existing payments to Student Fee Heads (Tuition first, then Other Fee Heads).
 * Safely processes live historical payments without modifying payments or invoice totals.
 */

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');

require_once BASE_PATH . '/app/config/config.php';
require_once BASE_PATH . '/app/core/Autoloader.php';
Autoloader::register();

$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    echo '<!DOCTYPE html><html><head><title>Payment Allocation Tool</title>';
    echo '<style>body{font-family:sans-serif;padding:20px;background:#f4f6f9;color:#333;}';
    echo '.box{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.1);max-width:900px;margin:auto;}';
    echo '.success{color:#059669;}.info{color:#2563eb;}.error{color:#dc2626;}</style></head><body>';
    echo '<div class="box"><h2>SchoolPro Real Payment to Fee Head Allocation Tool</h2><pre>';
}

echo "Connecting to database (" . DB_NAME . ")...\n";

try {
    $db = Database::getInstance()->getConnection();
    echo "Connected successfully!\n\n";

    // Fetch all existing payments that have invoice_id or student_id
    $stmt = $db->query("SELECT p.id, p.student_id, p.invoice_id, p.amount, p.payment_date, i.term, i.academic_year 
                        FROM payments p 
                        LEFT JOIN invoices i ON p.invoice_id = i.id 
                        ORDER BY p.payment_date ASC, p.id ASC");
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($payments) . " payment records in database.\n";
    echo "Allocating payments to Tuition vs Other Fee Heads...\n\n";

    $totalAllocatedCount = 0;
    $totalTuitionAmount = 0;
    $totalOtherAmount = 0;
    $skippedCount = 0;

    foreach ($payments as $payment) {
        $paymentId = $payment['id'];
        $studentId = $payment['student_id'];
        $paymentAmount = floatval($payment['amount']);
        $term = $payment['term'];
        $academicYear = $payment['academic_year'];

        if ($paymentAmount <= 0) continue;

        // Check if fee_head_payments already exists for this payment
        $chk = $db->prepare("SELECT COUNT(*) FROM fee_head_payments WHERE payment_id = ?");
        $chk->execute([$paymentId]);
        if ($chk->fetchColumn() > 0) {
            $skippedCount++;
            continue;
        }

        // Find student_fee_heads for this student (Tuition first, then others)
        $sfhStmt = $db->prepare("SELECT sfh.id, sfh.amount, sfh.fee_head_id, fh.code, fh.name,
                                         COALESCE((SELECT SUM(fhp.amount) FROM fee_head_payments fhp WHERE fhp.student_fee_head_id = sfh.id), 0) as paid_so_far
                                  FROM student_fee_heads sfh
                                  LEFT JOIN fee_heads fh ON sfh.fee_head_id = fh.id
                                  WHERE sfh.student_id = ? 
                                    AND (sfh.term = ? OR ? IS NULL)
                                    AND (sfh.academic_year = ? OR ? IS NULL)
                                    AND sfh.status = 'active'
                                  ORDER BY (CASE WHEN fh.code = 'TUITION' OR LOWER(fh.name) LIKE '%tuition%' THEN 0 ELSE 1 END), sfh.id ASC");
        $sfhStmt->execute([$studentId, $term, $term, $academicYear, $academicYear]);
        $feeHeads = $sfhStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fallback: If no match for term/academic_year, get any active student_fee_heads for student
        if (empty($feeHeads)) {
            $sfhStmt = $db->prepare("SELECT sfh.id, sfh.amount, sfh.fee_head_id, fh.code, fh.name,
                                             COALESCE((SELECT SUM(fhp.amount) FROM fee_head_payments fhp WHERE fhp.student_fee_head_id = sfh.id), 0) as paid_so_far
                                      FROM student_fee_heads sfh
                                      LEFT JOIN fee_heads fh ON sfh.fee_head_id = fh.id
                                      WHERE sfh.student_id = ? AND sfh.status = 'active'
                                      ORDER BY (CASE WHEN fh.code = 'TUITION' OR LOWER(fh.name) LIKE '%tuition%' THEN 0 ELSE 1 END), sfh.id ASC");
            $sfhStmt->execute([$studentId]);
            $feeHeads = $sfhStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if (empty($feeHeads)) {
            continue;
        }

        $remainingPayment = $paymentAmount;

        foreach ($feeHeads as $fh) {
            if ($remainingPayment <= 0) break;

            $dueAmount = max(0, floatval($fh['amount']) - floatval($fh['paid_so_far']));
            if ($dueAmount <= 0) continue; // Already fully paid

            $alloc = min($remainingPayment, $dueAmount);

            $ins = $db->prepare("INSERT INTO fee_head_payments (payment_id, student_fee_head_id, amount) VALUES (?, ?, ?)");
            $ins->execute([$paymentId, $fh['id'], $alloc]);

            $remainingPayment -= $alloc;
            $totalAllocatedCount++;

            $isTuition = ($fh['code'] === 'TUITION' || stripos($fh['name'], 'tuition') !== false);
            if ($isTuition) {
                $totalTuitionAmount += $alloc;
            } else {
                $totalOtherAmount += $alloc;
            }
        }

        // If payment exceeds due amounts, allocate remainder to the first fee head (Tuition)
        if ($remainingPayment > 0 && !empty($feeHeads)) {
            $firstFh = $feeHeads[0];
            $ins = $db->prepare("INSERT INTO fee_head_payments (payment_id, student_fee_head_id, amount) VALUES (?, ?, ?)");
            $ins->execute([$paymentId, $firstFh['id'], $remainingPayment]);
            $totalAllocatedCount++;

            $isTuition = ($firstFh['code'] === 'TUITION' || stripos($firstFh['name'], 'tuition') !== false);
            if ($isTuition) {
                $totalTuitionAmount += $remainingPayment;
            } else {
                $totalOtherAmount += $remainingPayment;
            }
        }
    }

    echo "✓ Successfully processed payment allocations!\n";
    echo "   - Payments Processed : " . count($payments) . "\n";
    echo "   - Already Allocated  : $skippedCount\n";
    echo "   - New Allocations    : $totalAllocatedCount\n";
    echo "   - Tuition Collected  : KES " . number_format($totalTuitionAmount, 2) . "\n";
    echo "   - Other Fee Heads    : KES " . number_format($totalOtherAmount, 2) . "\n\n";

    echo "========================================\n";
    echo "REAL PAYMENT ALLOCATION COMPLETED! ✓\n";
    echo "========================================\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

if (!$isCli) {
    echo '</pre><p class="success"><strong>Payment allocations to Tuition vs Other Fee Heads updated successfully!</strong></p></div></body></html>';
}
