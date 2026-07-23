<?php
/**
 * SchoolPro V2.0.0 Fee Head Billing & Payment Allocation Seeder
 * Populates student_fee_heads and fee_head_payments for all active students.
 */

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
require_once BASE_PATH . '/app/config/config.php';
require_once BASE_PATH . '/app/core/Helper.php';
require_once BASE_PATH . '/app/core/Autoloader.php';

Autoloader::register();

$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    echo '<!DOCTYPE html><html><head><title>Fee Head Seeder</title>';
    echo '<style>body{font-family:sans-serif;padding:20px;background:#f4f6f9;color:#333;}';
    echo '.box{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.1);max-width:900px;margin:auto;}';
    echo '.success{color:#059669;}.info{color:#2563eb;}.error{color:#dc2626;}</style></head><body>';
    echo '<div class="box"><h2>SchoolPro Fee Head Billing & Collection Seeder</h2><pre>';
}

echo "Connecting to database (" . DB_NAME . ")...\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Fetch fee heads
    $stmt = $db->query("SELECT id, code, name FROM fee_heads");
    $feeHeads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($feeHeads)) {
        die("No fee heads found. Please run database/run_migration.php first.\n");
    }
    
    $feeHeadMap = [];
    foreach ($feeHeads as $fh) {
        $feeHeadMap[$fh['code']] = $fh['id'];
    }
    
    echo "1. Active Fee Heads loaded:\n";
    foreach ($feeHeads as $fh) {
        echo "   - [{$fh['code']}] {$fh['name']} (ID: {$fh['id']})\n";
    }
    
    // 2. Fetch all students
    $students = $db->query("SELECT s.id, s.admission_number, c.grade_id, g.name as grade_code 
                             FROM students s 
                             LEFT JOIN classes c ON s.class_id = c.id 
                             LEFT JOIN grades g ON c.grade_id = g.id")->fetchAll(PDO::FETCH_ASSOC);
                             
    echo "\n2. Seeding Fee Head Billing for " . count($students) . " students...\n";
    
    $academicYears = ['2026/2027', '2025/2026'];
    $billedCount = 0;
    
    foreach ($students as $student) {
        $studentId = $student['id'];
        $gradeCode = $student['grade_code'] ?? 'G1';
        
        // Fee head pricing template per term
        $isJss = in_array($gradeCode, ['G7', 'G8', 'G9']);
        $isEy = in_array($gradeCode, ['PG', 'PP1', 'PP2']);
        
        $pricing = [
            'TUITION' => $isJss ? 15000.00 : ($isEy ? 8000.00 : 11000.00),
            'LUNCH' => 3000.00,
            'TRANSPORT' => 2500.00,
            'EXAM' => 1000.00,
            'ACTIVITY' => 800.00,
            'ICT_LIB' => 700.00,
            'BUILDING' => 2000.00
        ];
        
        foreach ($academicYears as $academicYear) {
            for ($term = 1; $term <= 3; $term++) {
                $totalTermFee = 0;
                
                foreach ($pricing as $code => $amount) {
                    if (!isset($feeHeadMap[$code])) continue;
                    $feeHeadId = $feeHeadMap[$code];
                    
                    // Insert or update student fee head
                    $stmt = $db->prepare("SELECT id FROM student_fee_heads WHERE student_id = ? AND fee_head_id = ? AND term = ? AND academic_year = ?");
                    $stmt->execute([$studentId, $feeHeadId, $term, $academicYear]);
                    $sfhRow = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$sfhRow) {
                        $ins = $db->prepare("INSERT INTO student_fee_heads (student_id, fee_head_id, amount, term, academic_year, status) 
                                             VALUES (?, ?, ?, ?, ?, 'active')");
                        $ins->execute([$studentId, $feeHeadId, $amount, $term, $academicYear]);
                        $sfhId = $db->lastInsertId();
                    } else {
                        $sfhId = $sfhRow['id'];
                    }
                    $totalTermFee += $amount;
                    $billedCount++;
                }
                
                // Ensure invoice exists and matches total term fee
                $chkInv = $db->prepare("SELECT id FROM invoices WHERE student_id = ? AND term = ? AND academic_year = ?");
                $chkInv->execute([$studentId, $term, $academicYear]);
                $invRow = $chkInv->fetch(PDO::FETCH_ASSOC);
                
                if (!$invRow) {
                    $invNum = 'INV-' . substr($academicYear, 0, 4) . '-T' . $term . '-' . str_pad($studentId, 4, '0', STR_PAD_LEFT);
                    $insInv = $db->prepare("INSERT INTO invoices (invoice_number, student_id, term, academic_year, total_amount, paid_amount, balance, status, due_date) 
                                            VALUES (?, ?, ?, ?, ?, 0.00, ?, 'pending', NOW())");
                    $insInv->execute([$invNum, $studentId, $term, $academicYear, $totalTermFee, $totalTermFee]);
                    $invoiceId = $db->lastInsertId();
                } else {
                    $invoiceId = $invRow['id'];
                    // Update total_amount if different
                    $db->prepare("UPDATE invoices SET total_amount = ? WHERE id = ?")->execute([$totalTermFee, $invoiceId]);
                }
                
                // 3. Allocate fee head payments for existing payment records
                $payments = $db->prepare("SELECT id, amount FROM payments WHERE invoice_id = ?");
                $payments->execute([$invoiceId]);
                $paymentList = $payments->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($paymentList as $p) {
                    $paymentId = $p['id'];
                    $paymentAmount = floatval($p['amount']);
                    
                    // Check if fee_head_payments already allocated for this payment
                    $chkFhp = $db->prepare("SELECT COUNT(*) as cnt FROM fee_head_payments WHERE payment_id = ?");
                    $chkFhp->execute([$paymentId]);
                    if ($chkFhp->fetch()['cnt'] == 0) {
                        // Get student fee heads for this term
                        $getSfh = $db->prepare("SELECT id, amount FROM student_fee_heads WHERE student_id = ? AND term = ? AND academic_year = ? ORDER BY id ASC");
                        $getSfh->execute([$studentId, $term, $academicYear]);
                        $sfhList = $getSfh->fetchAll(PDO::FETCH_ASSOC);
                        
                        $remPay = $paymentAmount;
                        foreach ($sfhList as $sfhItem) {
                            if ($remPay <= 0) break;
                            $allocAmt = min($remPay, floatval($sfhItem['amount']));
                            $insFhp = $db->prepare("INSERT INTO fee_head_payments (payment_id, student_fee_head_id, amount) VALUES (?, ?, ?)");
                            $insFhp->execute([$paymentId, $sfhItem['id'], $allocAmt]);
                            $remPay -= $allocAmt;
                        }
                    }
                }
                
                // Recalculate invoice balance
                $sumPaid = $db->prepare("SELECT COALESCE(SUM(amount), 0) as paid FROM payments WHERE invoice_id = ?");
                $sumPaid->execute([$invoiceId]);
                $paidTotal = floatval($sumPaid->fetch()['paid'] ?? 0);
                $newBal = max(0, $totalTermFee - $paidTotal);
                $status = ($newBal <= 0) ? 'paid' : (($paidTotal > 0) ? 'partial' : 'pending');
                
                $db->prepare("UPDATE invoices SET paid_amount = ?, balance = ?, status = ? WHERE id = ?")
                   ->execute([$paidTotal, $newBal, $status, $invoiceId]);
            }
        }
    }
    
    echo "   ✓ Successfully billed student fee heads across Tuition, Lunch, Transport, Exam, Activity, Library & Building Fund!\n";
    echo "   ✓ Successfully allocated payment collections to specific fee heads!\n";
    
    echo "\n========================================\n";
    echo "FEE HEAD BILLING & SEEDING COMPLETED SUCCESSFULLY! ✓\n";
    echo "========================================\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

if (!$isCli) {
    echo '</pre><p class="success"><strong>Fee Heads have been successfully populated and linked to student invoices & payments!</strong></p></div></body></html>';
}
