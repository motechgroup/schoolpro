<?php
$pdo = new PDO('mysql:host=localhost;dbname=masomo_school_db', 'root', '');
$stmt = $pdo->query('DESCRIBE cms_payments');
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "cms_payments table structure:\n";
foreach($cols as $col) {
    echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
}

