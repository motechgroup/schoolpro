<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Statement - <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></title>
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
            background: #fff;
            padding: 10px;
        }
        
        @media print {
            @page {
                size: A4;
                margin: 0.5cm;
            }
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
        
        .receipt {
            max-width: 80mm;
            margin: 0 auto;
            background: #fff;
            padding: 10px;
        }
        
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .school-name {
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .school-address {
            font-size: 10px;
            margin-bottom: 5px;
        }
        
        .receipt-title {
            font-weight: bold;
            font-size: 13px;
            margin-top: 5px;
        }
        
        .info-section {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 11px;
        }
        
        .info-label {
            font-weight: bold;
        }
        
        .info-value {
            text-align: right;
        }
        
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        
        .fee-section {
            margin-bottom: 10px;
        }
        
        .fee-head {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .fee-details {
            margin-left: 5px;
            margin-bottom: 8px;
        }
        
        .fee-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            margin-bottom: 2px;
        }
        
        .payment-list {
            margin-left: 10px;
            margin-top: 5px;
            margin-bottom: 5px;
        }
        
        .payment-item {
            font-size: 9px;
            margin-bottom: 3px;
            padding-left: 5px;
        }
        
        .payment-date {
            display: inline-block;
            width: 70px;
        }
        
        .payment-amount {
            display: inline-block;
            width: 60px;
            text-align: right;
        }
        
        .payment-method {
            display: inline-block;
            width: 50px;
            text-transform: uppercase;
        }
        
        .payment-receipt {
            display: inline-block;
            width: 80px;
            font-size: 8px;
        }
        
        .summary {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 8px 0;
            margin: 10px 0;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 3px;
        }
        
        .total-row {
            font-size: 12px;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px dashed #000;
        }
        
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 9px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #000;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-family: Arial, sans-serif;
            font-size: 14px;
            z-index: 1000;
        }
        
        .print-btn:hover {
            background: #333;
        }
        
        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #666;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-family: Arial, sans-serif;
            font-size: 14px;
            z-index: 1000;
            text-decoration: none;
            display: inline-block;
        }
        
        .back-btn:hover {
            background: #555;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="<?php echo BASE_URL; ?>/students/show/<?php echo $student['id']; ?>" class="back-btn">
            ← Back
        </a>
        <button onclick="window.print()" class="print-btn">🖨️ Print</button>
    </div>
    
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="school-name"><?php echo htmlspecialchars(getSchoolName()); ?></div>
            <div class="school-address">Fee Statement</div>
            <div class="receipt-title">TERM <?php echo $term; ?> - <?php echo htmlspecialchars($academicYear); ?></div>
        </div>
        
        <!-- Student Information -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Student:</span>
                <span class="info-value"><?php echo htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ?? '') . ' ' . $student['last_name']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Adm No:</span>
                <span class="info-value"><?php echo htmlspecialchars($student['admission_number']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Class:</span>
                <span class="info-value"><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Date:</span>
                <span class="info-value"><?php echo date('d/m/Y H:i'); ?></span>
            </div>
        </div>
        
        <!-- Fee Details -->
        <?php if (!empty($feeHeadBreakdown)): ?>
        <div class="fee-section">
            <?php foreach ($feeHeadBreakdown as $feeHead): ?>
            <div class="fee-head">
                <?php echo htmlspecialchars($feeHead['fee_head_name']); ?>
            </div>
            <div class="fee-details">
                <div class="fee-row">
                    <span>Amount:</span>
                    <span><?php echo formatCurrency($feeHead['amount']); ?></span>
                </div>
                <div class="fee-row">
                    <span>Paid:</span>
                    <span><?php echo formatCurrency($feeHead['paid_amount']); ?></span>
                </div>
                <div class="fee-row">
                    <span>Balance:</span>
                    <span><?php echo formatCurrency($feeHead['balance']); ?></span>
                </div>
                
                <!-- Payment Details -->
                <?php if (!empty($feeHead['payments'])): ?>
                <div class="payment-list">
                    <div style="font-weight: bold; margin-bottom: 3px; font-size: 9px;">PAYMENT HISTORY:</div>
                    <?php foreach ($feeHead['payments'] as $payment): ?>
                    <div class="payment-item">
                        <div>
                            <span class="payment-date"><?php 
                                // Show date and time
                                $paymentDateTime = !empty($payment['created_at']) ? $payment['created_at'] : $payment['payment_date'];
                                echo date('d/m/Y H:i', strtotime($paymentDateTime)); 
                            ?></span>
                            <span class="payment-amount"><?php echo formatCurrency($payment['amount']); ?></span>
                            <span class="payment-method"><?php echo strtoupper(getPaymentMethodName($payment['payment_method'])); ?></span>
                        </div>
                        <div style="font-size: 8px; margin-top: 1px;">
                            <?php if (!empty($payment['receipt_number'])): ?>
                            Receipt: <?php echo htmlspecialchars($payment['receipt_number']); ?>
                            <?php endif; ?>
                            <?php if (!empty($payment['mpesa_receipt'])): ?>
                            | M-Pesa: <?php echo htmlspecialchars($payment['mpesa_receipt']); ?>
                            <?php endif; ?>
                            <?php if (!empty($payment['mpesa_transaction_id'])): ?>
                            | Txn: <?php echo htmlspecialchars($payment['mpesa_transaction_id']); ?>
                            <?php endif; ?>
                            <?php if (!empty($payment['reference_number']) && empty($payment['mpesa_receipt'])): ?>
                            | Ref: <?php echo htmlspecialchars($payment['reference_number']); ?>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($payment['remarks'])): ?>
                        <div style="font-size: 8px; margin-top: 1px; font-style: italic;">
                            Note: <?php echo htmlspecialchars($payment['remarks']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="payment-list">
                    <div style="font-size: 9px; font-style: italic;">No payments recorded</div>
                </div>
                <?php endif; ?>
            </div>
            <div class="divider"></div>
            <?php endforeach; ?>
        </div>
        
        <!-- Payment History (All Payments) -->
        <?php if (!empty($allPayments)): ?>
        <div class="fee-section" style="margin-top: 15px;">
            <div class="fee-head">PAYMENT HISTORY</div>
            <div class="fee-details">
                <?php foreach ($allPayments as $payment): ?>
                <div class="payment-item" style="border-bottom: 1px dashed #ccc; padding-bottom: 5px; margin-bottom: 5px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                        <div>
                            <span style="font-weight: bold; font-size: 10px;">
                                <?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?> 
                                <?php echo date('H:i', strtotime($payment['created_at'] ?? $payment['payment_date'])); ?>
                            </span>
                            <span style="margin-left: 10px; font-size: 10px; text-transform: uppercase;">
                                <?php echo getPaymentMethodName($payment['payment_method']); ?>
                            </span>
                        </div>
                        <span style="font-weight: bold; font-size: 10px;">
                            <?php echo formatCurrency($payment['amount']); ?>
                        </span>
                    </div>
                    <div style="font-size: 8px; margin-top: 2px;">
                        <?php if (!empty($payment['receipt_number'])): ?>
                        <span>Receipt: <?php echo htmlspecialchars($payment['receipt_number']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($payment['mpesa_receipt'])): ?>
                        <span style="margin-left: 10px;">M-Pesa Code: <strong><?php echo htmlspecialchars($payment['mpesa_receipt']); ?></strong></span>
                        <?php endif; ?>
                        <?php if (!empty($payment['mpesa_transaction_id'])): ?>
                        <span style="margin-left: 10px;">Txn ID: <?php echo htmlspecialchars($payment['mpesa_transaction_id']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($payment['reference_number']) && empty($payment['mpesa_receipt'])): ?>
                        <span style="margin-left: 10px;">Ref: <?php echo htmlspecialchars($payment['reference_number']); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($payment['remarks'])): ?>
                    <div style="font-size: 8px; margin-top: 2px; font-style: italic; color: #666;">
                        <?php echo htmlspecialchars($payment['remarks']); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Summary -->
        <div class="summary">
            <div class="summary-row">
                <span>TOTAL AMOUNT:</span>
                <span><?php echo formatCurrency($totalAmount); ?></span>
            </div>
            <div class="summary-row">
                <span>TOTAL PAID:</span>
                <span><?php echo formatCurrency($totalPaid); ?></span>
            </div>
            <div class="total-row summary-row">
                <span>OUTSTANDING BALANCE:</span>
                <span><?php echo formatCurrency($totalBalance); ?></span>
            </div>
        </div>
        <?php else: ?>
        <div style="text-align: center; padding: 20px; font-size: 11px;">
            No fee heads assigned for this term.
        </div>
        <?php endif; ?>
        
        <!-- Footer -->
        <div class="footer">
            <div>--------------------------------</div>
            <div style="margin-top: 5px;">This is a computer-generated statement</div>
            <div>No signature required</div>
            <div style="margin-top: 5px;"><?php echo APP_NAME; ?></div>
            <div style="margin-top: 10px;">Generated: <?php echo date('d/m/Y H:i:s'); ?></div>
        </div>
    </div>
</body>
</html>
