<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - <?php echo htmlspecialchars($payment['receipt_number']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #f5f5f5;
            padding: 20px;
        }
        
        @media print {
            @page {
                size: A4;
                margin: 0.5cm;
            }
            body {
                padding: 0;
                background: #fff;
            }
            .no-print {
                display: none !important;
            }
        }
        
        .no-print {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .no-print a, .no-print button {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 5px;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .no-print a:hover, .no-print button:hover {
            background: #0056b3;
        }
        
        .receipt {
            max-width: 80mm;
            margin: 0 auto;
            background: #fff;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        
        .school-name {
            font-weight: bold;
            font-size: 16px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .school-address {
            font-size: 10px;
            margin-bottom: 5px;
        }
        
        .receipt-title {
            font-weight: bold;
            font-size: 14px;
            margin-top: 10px;
            text-transform: uppercase;
        }
        
        .info-section {
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 11px;
        }
        
        .info-label {
            font-weight: bold;
        }
        
        .info-value {
            text-align: right;
        }
        
        .payment-details {
            margin-bottom: 15px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 11px;
        }
        
        .detail-label {
            font-weight: bold;
        }
        
        .detail-value {
            text-align: right;
        }
        
        .amount-section {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 10px 0;
            margin: 15px 0;
        }
        
        .amount-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .total-amount {
            font-size: 16px;
            text-transform: uppercase;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }
        
        .method-badge {
            display: inline-block;
            padding: 3px 8px;
            background: #007bff;
            color: #fff;
            border-radius: 3px;
            font-size: 10px;
            text-transform: uppercase;
        }
        
        .method-badge.cash {
            background: #28a745;
        }
        
        .method-badge.mpesa {
            background: #ffc107;
            color: #000;
        }
        
        .method-badge.bank {
            background: #17a2b8;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="<?php echo BASE_URL; ?>/payments">← Back to Payments</a>
        <button onclick="window.print()">🖨️ Print Receipt</button>
    </div>
    
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="school-name"><?php echo htmlspecialchars(getSchoolName()); ?></div>
            <?php 
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'school_address' LIMIT 1");
                $result = $stmt->fetch();
                $schoolAddress = $result['setting_value'] ?? '';
                if (!empty($schoolAddress)): 
            ?>
            <div class="school-address"><?php echo htmlspecialchars($schoolAddress); ?></div>
            <?php endif; } catch (Exception $e) {} ?>
            <div class="receipt-title">Payment Receipt</div>
        </div>
        
        <!-- Receipt Information -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Receipt No:</span>
                <span class="info-value"><?php echo htmlspecialchars($payment['receipt_number']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Date:</span>
                <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($payment['payment_date'])); ?></span>
            </div>
            <?php if (!empty($payment['invoice_number'])): ?>
            <div class="info-row">
                <span class="info-label">Invoice:</span>
                <span class="info-value"><?php echo htmlspecialchars($payment['invoice_number']); ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Student Information -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Student:</span>
                <span class="info-value"><?php echo displayText(($payment['student_first_name'] ?? '') . ' ' . ($payment['student_middle_name'] ?? '') . ' ' . ($payment['student_last_name'] ?? '')); ?></span>
            </div>
            <?php if (!empty($payment['admission_number'])): ?>
            <div class="info-row">
                <span class="info-label">Adm No:</span>
                <span class="info-value"><?php echo htmlspecialchars($payment['admission_number']); ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($payment['class_name'])): ?>
            <div class="info-row">
                <span class="info-label">Class:</span>
                <span class="info-value"><?php echo htmlspecialchars($payment['class_name']); ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Payment Details -->
        <div class="payment-details">
            <div class="detail-row">
                <span class="detail-label">Payment Method:</span>
                <span class="detail-value">
                    <span class="method-badge <?php echo strtolower($payment['payment_method'] ?? 'cash'); ?>">
                        <?php echo ucfirst($payment['payment_method'] ?? 'Cash'); ?>
                    </span>
                </span>
            </div>
            
            <?php if (!empty($payment['mpesa_receipt'])): ?>
            <div class="detail-row">
                <span class="detail-label">M-Pesa Receipt:</span>
                <span class="detail-value"><?php echo htmlspecialchars($payment['mpesa_receipt']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($payment['reference_number']) && empty($payment['mpesa_receipt'])): ?>
            <div class="detail-row">
                <span class="detail-label">Reference:</span>
                <span class="detail-value"><?php echo htmlspecialchars($payment['reference_number']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($payment['remarks'])): ?>
            <div class="detail-row" style="margin-top: 10px;">
                <span class="detail-label">Remarks:</span>
                <span class="detail-value" style="text-align: left; font-size: 10px;"><?php echo htmlspecialchars($payment['remarks']); ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Amount Section -->
        <div class="amount-section">
            <?php if (!empty($payment['invoice_total'])): ?>
            <div class="detail-row" style="margin-bottom: 8px; font-size: 11px;">
                <span class="detail-label">Invoice Total:</span>
                <span class="detail-value"><?php echo formatCurrency($payment['invoice_total']); ?></span>
            </div>
            <?php endif; ?>
            <div class="amount-row">
                <span>Amount Paid:</span>
                <span><?php echo formatCurrency($payment['amount']); ?></span>
            </div>
            <?php if (isset($payment['invoice_balance']) && !empty($payment['invoice_id'])): ?>
            <div class="amount-row" style="margin-top: 8px; <?php echo floatval($payment['invoice_balance']) <= 0 ? 'color: #28a745;' : 'color: #dc3545;'; ?>">
                <span>Outstanding Balance:</span>
                <span class="total-amount" style="font-size: 14px; font-weight: bold;">
                    <?php 
                    $balance = floatval($payment['invoice_balance'] ?? 0);
                    if ($balance <= 0) {
                        echo '<span style="color: #28a745;">Fully Paid</span>';
                    } else {
                        echo formatCurrency($balance);
                    }
                    ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <?php if (!empty($payment['received_by_first_name'])): ?>
            <div style="margin-bottom: 5px;">
                Received by: <?php echo displayText(($payment['received_by_first_name'] ?? '') . ' ' . ($payment['received_by_last_name'] ?? '')); ?>
            </div>
            <?php endif; ?>
            <div>Thank you for your payment!</div>
            <div style="margin-top: 5px; font-size: 9px;">
                Generated on <?php echo date('d/m/Y H:i:s'); ?>
            </div>
        </div>
    </div>
</body>
</html>

