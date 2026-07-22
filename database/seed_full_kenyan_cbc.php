<?php
/**
 * SchoolPro V2.0.0 Kenyan CBC Full Dataset Seeder
 * Populates realistic student, teacher, parent, class, and fee data for Playgroup to Grade 9 (JSS).
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
    echo '<!DOCTYPE html><html><head><title>SchoolPro Dataset Seeder</title>';
    echo '<style>body{font-family:sans-serif;padding:20px;background:#f4f6f9;color:#333;}';
    echo '.box{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.1);max-width:900px;margin:auto;}';
    echo '.success{color:#059669;}.info{color:#2563eb;}.error{color:#dc2626;}</style></head><body>';
    echo '<div class="box"><h2>SchoolPro Kenyan CBC Dataset Seeder (Playgroup to Grade 9)</h2><pre>';
}

echo "Connecting to database (" . DB_NAME . ")...\n";

try {
    $db = Database::getInstance()->getConnection();
    $invoiceModel = new Invoice();
    $paymentModel = new Payment();
    
    echo "1. Verifying Grades (Playgroup to Grade 9)...\n";
    $gradeDefs = [
        ['code' => 'PG', 'name' => 'Playgroup', 'level' => 1, 'fee' => 12000],
        ['code' => 'PP1', 'name' => 'Pre-Primary 1', 'level' => 2, 'fee' => 14000],
        ['code' => 'PP2', 'name' => 'Pre-Primary 2', 'level' => 3, 'fee' => 14000],
        ['code' => 'G1', 'name' => 'Grade 1', 'level' => 4, 'fee' => 18000],
        ['code' => 'G2', 'name' => 'Grade 2', 'level' => 5, 'fee' => 18000],
        ['code' => 'G3', 'name' => 'Grade 3', 'level' => 6, 'fee' => 18000],
        ['code' => 'G4', 'name' => 'Grade 4', 'level' => 7, 'fee' => 20000],
        ['code' => 'G5', 'name' => 'Grade 5', 'level' => 8, 'fee' => 20000],
        ['code' => 'G6', 'name' => 'Grade 6', 'level' => 9, 'fee' => 20000],
        ['code' => 'G7', 'name' => 'Grade 7 (JSS)', 'level' => 10, 'fee' => 25000],
        ['code' => 'G8', 'name' => 'Grade 8 (JSS)', 'level' => 11, 'fee' => 25000],
        ['code' => 'G9', 'name' => 'Grade 9 (JSS)', 'level' => 12, 'fee' => 25000]
    ];
    
    $gradeMap = [];
    foreach ($gradeDefs as $g) {
        $stmt = $db->prepare("SELECT id FROM grades WHERE name = ?");
        $stmt->execute([$g['code']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $ins = $db->prepare("INSERT INTO grades (name, display_name, level) VALUES (?, ?, ?)");
            $ins->execute([$g['code'], $g['name'], $g['level']]);
            $gradeId = $db->lastInsertId();
        } else {
            $gradeId = $row['id'];
        }
        $gradeMap[$g['code']] = [
            'id' => $gradeId,
            'name' => $g['name'],
            'fee' => $g['fee']
        ];
    }
    echo "   ✓ Verified 12 Grade Levels!\n";
    
    echo "\n2. Creating Classes for Playgroup to Grade 9...\n";
    $academicYear = '2026/2027';
    $streamNames = [
        'PG' => 'Red',
        'PP1' => 'Yellow',
        'PP2' => 'Blue',
        'G1' => 'Eagles',
        'G2' => 'Falcons',
        'G3' => 'Lions',
        'G4' => 'Elephants',
        'G5' => 'Rhinos',
        'G6' => 'Cheetahs',
        'G7' => 'JSS Alpha',
        'G8' => 'JSS Beta',
        'G9' => 'JSS Gamma'
    ];
    
    $classMap = [];
    foreach ($gradeMap as $code => $gInfo) {
        $stream = $streamNames[$code];
        $stmt = $db->prepare("SELECT id FROM classes WHERE grade_id = ? AND name = ? AND academic_year = ?");
        $stmt->execute([$gInfo['id'], $stream, $academicYear]);
        $cRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$cRow) {
            $ins = $db->prepare("INSERT INTO classes (grade_id, name, capacity, academic_year, status) VALUES (?, ?, 40, ?, 'active')");
            $ins->execute([$gInfo['id'], $stream, $academicYear]);
            $classId = $db->lastInsertId();
        } else {
            $classId = $cRow['id'];
        }
        $classMap[$code] = $classId;
    }
    echo "   ✓ Created/Verified 12 Class Streams!\n";
    
    echo "\n3. Seeding Kenyan Parent Profiles...\n";
    $parentsData = [
        ['first_name' => 'James', 'last_name' => 'Mwangi', 'phone' => '0712345671', 'email' => 'jmwangi@gmail.com', 'relationship' => 'father'],
        ['first_name' => 'Grace', 'last_name' => 'Wanjiku', 'phone' => '0722345672', 'email' => 'gwanjiku@yahoo.com', 'relationship' => 'mother'],
        ['first_name' => 'Peter', 'last_name' => 'Ochieng', 'phone' => '0733345673', 'email' => 'pochieng@gmail.com', 'relationship' => 'father'],
        ['first_name' => 'Amina', 'last_name' => 'Hassan', 'phone' => '0744345674', 'email' => 'ahassan@hotmail.com', 'relationship' => 'mother'],
        ['first_name' => 'David', 'last_name' => 'Kipkorir', 'phone' => '0755345675', 'email' => 'dkipkorir@gmail.com', 'relationship' => 'father'],
        ['first_name' => 'Mary', 'last_name' => 'Chebet', 'phone' => '0766345676', 'email' => 'mchebet@gmail.com', 'relationship' => 'mother'],
        ['first_name' => 'John', 'last_name' => 'Mutua', 'phone' => '0777345677', 'email' => 'jmutua@yahoo.com', 'relationship' => 'father'],
        ['first_name' => 'Faith', 'last_name' => 'Nanjala', 'phone' => '0788345678', 'email' => 'fnanjala@gmail.com', 'relationship' => 'mother'],
        ['first_name' => 'Emmanuel', 'last_name' => 'Wekesa', 'phone' => '0799345679', 'email' => 'ewekesa@gmail.com', 'relationship' => 'guardian'],
        ['first_name' => 'Sarah', 'last_name' => 'Auma', 'phone' => '0711223344', 'email' => 'sauma@gmail.com', 'relationship' => 'mother']
    ];
    
    $parentIds = [];
    foreach ($parentsData as $p) {
        $stmt = $db->prepare("SELECT id FROM parents WHERE phone = ?");
        $stmt->execute([$p['phone']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $ins = $db->prepare("INSERT INTO parents (first_name, last_name, phone, email, relationship, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $ins->execute([$p['first_name'], $p['last_name'], $p['phone'], $p['email'], $p['relationship']]);
            $parentIds[] = $db->lastInsertId();
        } else {
            $parentIds[] = $row['id'];
        }
    }
    echo "   ✓ Created/Verified " . count($parentIds) . " Parent Profiles!\n";
    
    echo "\n4. Seeding Students from Playgroup to Grade 9...\n";
    $studentsDataSet = [
        // Playgroup
        ['class_code' => 'PG', 'adm' => '2026/001', 'upi' => 'UPI-PG-001', 'first_name' => 'Ethan', 'last_name' => 'Mwangi', 'gender' => 'male', 'dob' => '2023-04-12'],
        ['class_code' => 'PG', 'adm' => '2026/002', 'upi' => 'UPI-PG-002', 'first_name' => 'Chloe', 'last_name' => 'Wanjiku', 'gender' => 'female', 'dob' => '2023-07-25'],
        
        // PP1
        ['class_code' => 'PP1', 'adm' => '2026/010', 'upi' => 'UPI-PP1-010', 'first_name' => 'Liam', 'last_name' => 'Ochieng', 'gender' => 'male', 'dob' => '2022-02-15'],
        ['class_code' => 'PP1', 'adm' => '2026/011', 'upi' => 'UPI-PP1-011', 'first_name' => 'Zahra', 'last_name' => 'Hassan', 'gender' => 'female', 'dob' => '2022-09-08'],
        
        // PP2
        ['class_code' => 'PP2', 'adm' => '2026/020', 'upi' => 'UPI-PP2-020', 'first_name' => 'Noah', 'last_name' => 'Kipkorir', 'gender' => 'male', 'dob' => '2021-03-30'],
        ['class_code' => 'PP2', 'adm' => '2026/021', 'upi' => 'UPI-PP2-021', 'first_name' => 'Joy', 'last_name' => 'Chebet', 'gender' => 'female', 'dob' => '2021-06-18'],
        
        // Grade 1
        ['class_code' => 'G1', 'adm' => '2026/030', 'upi' => 'UPI-G1-030', 'first_name' => 'Brian', 'last_name' => 'Mutua', 'gender' => 'male', 'dob' => '2020-01-10'],
        ['class_code' => 'G1', 'adm' => '2026/031', 'upi' => 'UPI-G1-031', 'first_name' => 'Faith', 'last_name' => 'Nanjala', 'gender' => 'female', 'dob' => '2020-08-22'],
        
        // Grade 2
        ['class_code' => 'G2', 'adm' => '2026/040', 'upi' => 'UPI-G2-040', 'first_name' => 'Kevin', 'last_name' => 'Wekesa', 'gender' => 'male', 'dob' => '2019-05-14'],
        ['class_code' => 'G2', 'adm' => '2026/041', 'upi' => 'UPI-G2-041', 'first_name' => 'Stacy', 'last_name' => 'Auma', 'gender' => 'female', 'dob' => '2019-11-05'],
        
        // Grade 3
        ['class_code' => 'G3', 'adm' => '2026/050', 'upi' => 'UPI-G3-050', 'first_name' => 'David', 'last_name' => 'Mwangi', 'gender' => 'male', 'dob' => '2018-02-28'],
        ['class_code' => 'G3', 'adm' => '2026/051', 'upi' => 'UPI-G3-051', 'first_name' => 'Mercy', 'last_name' => 'Wanjiku', 'gender' => 'female', 'dob' => '2018-10-12'],
        
        // Grade 4
        ['class_code' => 'G4', 'adm' => '2026/060', 'upi' => 'UPI-G4-060', 'first_name' => 'Victor', 'last_name' => 'Ochieng', 'gender' => 'male', 'dob' => '2017-07-19'],
        ['class_code' => 'G4', 'adm' => '2026/061', 'upi' => 'UPI-G4-061', 'first_name' => 'Fatuma', 'last_name' => 'Hassan', 'gender' => 'female', 'dob' => '2017-12-01'],
        
        // Grade 5
        ['class_code' => 'G5', 'adm' => '2026/070', 'upi' => 'UPI-G5-070', 'first_name' => 'Caleb', 'last_name' => 'Kipkorir', 'gender' => 'male', 'dob' => '2016-04-03'],
        ['class_code' => 'G5', 'adm' => '2026/071', 'upi' => 'UPI-G5-071', 'first_name' => 'Daisy', 'last_name' => 'Chebet', 'gender' => 'female', 'dob' => '2016-09-17'],
        
        // Grade 6
        ['class_code' => 'G6', 'adm' => '2026/080', 'upi' => 'UPI-G6-080', 'first_name' => 'Samuel', 'last_name' => 'Mutua', 'gender' => 'male', 'dob' => '2015-01-26'],
        ['class_code' => 'G6', 'adm' => '2026/081', 'upi' => 'UPI-G6-081', 'first_name' => 'Hellen', 'last_name' => 'Nanjala', 'gender' => 'female', 'dob' => '2015-08-30'],
        
        // Grade 7 (JSS)
        ['class_code' => 'G7', 'adm' => '2026/090', 'upi' => 'UPI-G7-090', 'first_name' => 'Dennis', 'last_name' => 'Wekesa', 'gender' => 'male', 'dob' => '2014-03-11'],
        ['class_code' => 'G7', 'adm' => '2026/091', 'upi' => 'UPI-G7-091', 'first_name' => 'Sharon', 'last_name' => 'Auma', 'gender' => 'female', 'dob' => '2014-11-20'],
        
        // Grade 8 (JSS)
        ['class_code' => 'G8', 'adm' => '2026/100', 'upi' => 'UPI-G8-100', 'first_name' => 'Alex', 'last_name' => 'Mwangi', 'gender' => 'male', 'dob' => '2013-05-09'],
        ['class_code' => 'G8', 'adm' => '2026/101', 'upi' => 'UPI-G8-101', 'first_name' => 'Brenda', 'last_name' => 'Wanjiku', 'gender' => 'female', 'dob' => '2013-10-14'],
        
        // Grade 9 (JSS)
        ['class_code' => 'G9', 'adm' => '2026/110', 'upi' => 'UPI-G9-110', 'first_name' => 'Francis', 'last_name' => 'Ochieng', 'gender' => 'male', 'dob' => '2012-02-04'],
        ['class_code' => 'G9', 'adm' => '2026/111', 'upi' => 'UPI-G9-111', 'first_name' => 'Amina', 'last_name' => 'Kipkorir', 'gender' => 'female', 'dob' => '2012-07-29']
    ];
    
    $studentCount = 0;
    foreach ($studentsDataSet as $idx => $s) {
        $classId = $classMap[$s['class_code']];
        $parentId = $parentIds[$idx % count($parentIds)];
        
        $stmt = $db->prepare("SELECT id FROM students WHERE admission_number = ?");
        $stmt->execute([$s['adm']]);
        $stRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$stRow) {
            $ins = $db->prepare("INSERT INTO students (admission_number, upi, first_name, last_name, gender, date_of_birth, admission_date, class_id, parent_id, status) 
                                 VALUES (?, ?, ?, ?, ?, ?, '2026-01-08', ?, ?, 'active')");
            $ins->execute([$s['adm'], $s['upi'], $s['first_name'], $s['last_name'], $s['gender'], $s['dob'], $classId, $parentId]);
            $studentId = $db->lastInsertId();
        } else {
            $studentId = $stRow['id'];
        }
        $studentCount++;
        
        // 5. Create Invoices and Carried-Forward Payments for Term 1, Term 2, Term 3
        $termFee = $gradeMap[$s['class_code']]['fee'];
        
        for ($term = 1; $term <= 3; $term++) {
            // Check if invoice exists for this student and term
            $chkInv = $db->prepare("SELECT id, balance FROM invoices WHERE student_id = ? AND term = ? AND academic_year = ?");
            $chkInv->execute([$studentId, $term, $academicYear]);
            $invRow = $chkInv->fetch(PDO::FETCH_ASSOC);
            
            if (!$invRow) {
                $invNum = 'INV-' . substr($academicYear, 0, 4) . '-T' . $term . '-' . str_pad($studentId, 4, '0', STR_PAD_LEFT);
                $insInv = $db->prepare("INSERT INTO invoices (invoice_number, student_id, term, academic_year, total_amount, paid_amount, balance, status, due_date) 
                                        VALUES (?, ?, ?, ?, ?, 0.00, ?, 'pending', NOW())");
                $insInv->execute([$invNum, $studentId, $term, $academicYear, $termFee, $termFee]);
                $invoiceId = $db->lastInsertId();
            } else {
                $invoiceId = $invRow['id'];
            }
            
            // Add representative realistic payments:
            // Term 1: student pays partial amount (creating arrears to carry into Term 2)
            // Term 2: student pays amount (reducing arrears)
            if ($term === 1 && ($idx % 2 === 0)) {
                $chkP = $db->prepare("SELECT id FROM payments WHERE invoice_id = ?");
                $chkP->execute([$invoiceId]);
                if (!$chkP->fetch()) {
                    $paidAmount = $termFee - 5000; // Leave 5,000 arrears
                    if ($paidAmount > 0) {
                        $pData = [
                            'invoice_id' => $invoiceId,
                            'student_id' => $studentId,
                            'payment_method' => 'mpesa',
                            'amount' => $paidAmount,
                            'payment_date' => '2026-01-15',
                            'receipt_number' => $paymentModel->generateReceiptNumber(),
                            'mpesa_receipt' => 'QGH' . rand(10000000, 99999999),
                            'received_by' => 1,
                            'remarks' => 'Term 1 Initial Payment'
                        ];
                        $paymentModel->create($pData);
                        $invoiceModel->updateBalance($invoiceId);
                    }
                }
            } elseif ($term === 2 && ($idx % 3 === 0)) {
                $chkP = $db->prepare("SELECT id FROM payments WHERE invoice_id = ?");
                $chkP->execute([$invoiceId]);
                if (!$chkP->fetch()) {
                    $paidAmount = $termFee + 2000; // Overpay by 2,000 to test credit carry-forward
                    $pData = [
                        'invoice_id' => $invoiceId,
                        'student_id' => $studentId,
                        'payment_method' => 'bank',
                        'amount' => $paidAmount,
                        'payment_date' => '2026-05-10',
                        'receipt_number' => $paymentModel->generateReceiptNumber(),
                        'reference_number' => 'REF-' . rand(10000, 99999),
                        'received_by' => 1,
                        'remarks' => 'Term 2 Fee + Advance Payment'
                    ];
                    $paymentModel->create($pData);
                    $invoiceModel->updateBalance($invoiceId);
                }
            }
        }
    }
    
    echo "   ✓ Successfully Seeded $studentCount Students from Playgroup to Grade 9!\n";
    echo "   ✓ Successfully Created Invoices and Multi-Term Carried-Forward Payments!\n";
    
    echo "\n========================================\n";
    echo "DATABASE SEEDING COMPLETED SUCCESSFULLY! ✓\n";
    echo "========================================\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

if (!$isCli) {
    echo '</pre><p class="success"><strong>System is fully populated with Playgroup to Grade 9 Kenyan CBC dataset!</strong></p></div></body></html>';
}
