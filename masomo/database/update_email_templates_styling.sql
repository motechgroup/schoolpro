-- Update email templates with modern HTML styling
-- This script updates all email templates to have beautiful, modern styling

UPDATE email_templates SET body = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Payment Reminder</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, ''Segoe UI'', Roboto, ''Helvetica Neue'', Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f4f4f4;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden;">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600;">Fee Payment Reminder</h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">Dear {parent_name},</p>
                            
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                This is a reminder that your child <strong>{student_name}</strong> (Admission No: <strong>{admission_number}</strong>) has an outstanding fee balance of <strong style="color: #e74c3c; font-size: 18px;">KES {balance_amount}</strong> for <strong>{term}</strong> <strong>{academic_year}</strong>.
                            </p>
                            
                            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 25px 0; border-radius: 4px;">
                                <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.6;">
                                    <strong>⚠️ Please make payment at your earliest convenience to avoid any inconvenience.</strong>
                                </p>
                            </div>
                            
                            <div style="background-color: #f8f9fa; padding: 20px; border-radius: 6px; margin: 25px 0;">
                                <h2 style="margin: 0 0 15px 0; color: #333333; font-size: 18px; font-weight: 600;">Payment Methods</h2>
                                <ul style="margin: 0; padding-left: 20px; color: #555555; font-size: 15px; line-height: 1.8;">
                                    <li style="margin-bottom: 10px;"><strong>M-Pesa PayBill:</strong> <span style="color: #667eea; font-weight: 600;">{paybill_number}</span> Account: <span style="color: #667eea; font-weight: 600;">{admission_number}</span></li>
                                    <li style="margin-bottom: 10px;"><strong>Bank Transfer:</strong> {bank_details}</li>
                                </ul>
                            </div>
                            
                            <p style="margin: 25px 0 0 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Thank you for your prompt attention to this matter.
                            </p>
                            
                            <p style="margin: 20px 0 0 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Best regards,<br>
                                <strong style="color: #667eea;">{school_name}</strong>
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0; color: #6c757d; font-size: 12px; line-height: 1.5;">
                                This is an automated email. Please do not reply to this message.<br>
                                For inquiries, please contact us at {school_phone} or {school_email}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>'
WHERE name = 'Fee Payment Reminder';

UPDATE email_templates SET body = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Payment Confirmation</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, ''Segoe UI'', Roboto, ''Helvetica Neue'', Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f4f4f4;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden;">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600;">✓ Payment Confirmed</h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">Dear {parent_name},</p>
                            
                            <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <p style="margin: 0; color: #155724; font-size: 15px; line-height: 1.6;">
                                    <strong>✓ We have received your payment of <span style="color: #28a745; font-size: 18px;">KES {payment_amount}</span> for {student_name} (Admission No: {admission_number}).</strong>
                                </p>
                            </div>
                            
                            <div style="background-color: #f8f9fa; padding: 20px; border-radius: 6px; margin: 25px 0;">
                                <h2 style="margin: 0 0 15px 0; color: #333333; font-size: 18px; font-weight: 600;">Payment Details</h2>
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 8px 0; color: #555555; font-size: 15px;"><strong>Receipt Number:</strong></td>
                                        <td style="padding: 8px 0; color: #333333; font-size: 15px; text-align: right;"><strong>{receipt_number}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #555555; font-size: 15px;"><strong>Payment Date:</strong></td>
                                        <td style="padding: 8px 0; color: #333333; font-size: 15px; text-align: right;"><strong>{payment_date}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #555555; font-size: 15px;"><strong>Payment Method:</strong></td>
                                        <td style="padding: 8px 0; color: #333333; font-size: 15px; text-align: right;"><strong>{payment_method}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #555555; font-size: 15px;"><strong>Remaining Balance:</strong></td>
                                        <td style="padding: 8px 0; color: #e74c3c; font-size: 15px; text-align: right; font-weight: 600;">KES {balance_amount}</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <p style="margin: 25px 0 0 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Thank you for your payment.
                            </p>
                            
                            <p style="margin: 20px 0 0 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Best regards,<br>
                                <strong style="color: #11998e;">{school_name}</strong>
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0; color: #6c757d; font-size: 12px; line-height: 1.5;">
                                This is an automated email. Please do not reply to this message.<br>
                                For inquiries, please contact us at {school_phone} or {school_email}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>'
WHERE name = 'Fee Payment Confirmation';

UPDATE email_templates SET body = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Progress Update</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, ''Segoe UI'', Roboto, ''Helvetica Neue'', Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f4f4f4;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden;">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600;">📊 Academic Progress Update</h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">Dear {parent_name},</p>
                            
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                We would like to inform you about <strong>{student_name}</strong>''s academic progress for <strong>{term}</strong> <strong>{academic_year}</strong>.
                            </p>
                            
                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 8px; margin: 25px 0; text-align: center; color: #ffffff;">
                                <p style="margin: 0 0 10px 0; font-size: 14px; opacity: 0.9;">Overall Performance</p>
                                <p style="margin: 0; font-size: 36px; font-weight: 700;">{overall_percentage}%</p>
                                <p style="margin: 5px 0 0 0; font-size: 18px; font-weight: 600;">Grade: {overall_grade}</p>
                            </div>
                            
                            <div style="background-color: #e8f5e9; padding: 20px; border-radius: 6px; margin: 25px 0; border-left: 4px solid #4caf50;">
                                <h3 style="margin: 0 0 15px 0; color: #2e7d32; font-size: 16px; font-weight: 600;">🌟 Top Performing Subjects</h3>
                                <p style="margin: 0; color: #1b5e20; font-size: 15px; line-height: 1.8;">{top_subjects}</p>
                            </div>
                            
                            <div style="background-color: #fff3e0; padding: 20px; border-radius: 6px; margin: 25px 0; border-left: 4px solid #ff9800;">
                                <h3 style="margin: 0 0 15px 0; color: #e65100; font-size: 16px; font-weight: 600;">📈 Areas for Improvement</h3>
                                <p style="margin: 0; color: #bf360c; font-size: 15px; line-height: 1.8;">{improvement_areas}</p>
                            </div>
                            
                            <p style="margin: 25px 0 0 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                We encourage you to discuss this progress with <strong>{student_name}</strong> and support their continued learning.
                            </p>
                            
                            <p style="margin: 20px 0 0 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Best regards,<br>
                                <strong style="color: #f5576c;">{teacher_name}</strong><br>
                                <span style="color: #667eea;">{school_name}</span>
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0; color: #6c757d; font-size: 12px; line-height: 1.5;">
                                This is an automated email. Please do not reply to this message.<br>
                                For inquiries, please contact us at {school_phone} or {school_email}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>'
WHERE name = 'Academic Progress Update';

UPDATE email_templates SET body = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{announcement_title}</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, ''Segoe UI'', Roboto, ''Helvetica Neue'', Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f4f4f4;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden;">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600;">📢 {announcement_title}</h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">Dear {recipient_name},</p>
                            
                            <div style="background-color: #f8f9fa; padding: 25px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #fa709a;">
                                <p style="margin: 0; color: #333333; font-size: 16px; line-height: 1.8; white-space: pre-line;">{announcement_content}</p>
                            </div>
                            
                            <p style="margin: 20px 0 0 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Best regards,<br>
                                <strong style="color: #fa709a;">{school_name}</strong>
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0; color: #6c757d; font-size: 12px; line-height: 1.5;">
                                This is an automated email. Please do not reply to this message.<br>
                                For inquiries, please contact us at {school_phone} or {school_email}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>'
WHERE name = 'General Announcement';

UPDATE email_templates SET body = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {school_name}</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, ''Segoe UI'', Roboto, ''Helvetica Neue'', Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f4f4f4;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden;">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600;">🎓 Welcome to {school_name}!</h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">Dear {parent_name},</p>
                            
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                We are delighted to have <strong>{student_name}</strong> join our school community!
                            </p>
                            
                            <div style="background-color: #e3f2fd; padding: 20px; border-radius: 6px; margin: 25px 0; border-left: 4px solid #2196f3;">
                                <h2 style="margin: 0 0 15px 0; color: #1976d2; font-size: 18px; font-weight: 600;">Student Details</h2>
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 8px 0; color: #555555; font-size: 15px;"><strong>Name:</strong></td>
                                        <td style="padding: 8px 0; color: #333333; font-size: 15px; text-align: right;"><strong>{student_name}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #555555; font-size: 15px;"><strong>Admission Number:</strong></td>
                                        <td style="padding: 8px 0; color: #333333; font-size: 15px; text-align: right;"><strong>{admission_number}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #555555; font-size: 15px;"><strong>Class:</strong></td>
                                        <td style="padding: 8px 0; color: #333333; font-size: 15px; text-align: right;"><strong>{class_name}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #555555; font-size: 15px;"><strong>Grade:</strong></td>
                                        <td style="padding: 8px 0; color: #333333; font-size: 15px; text-align: right;"><strong>{grade_name}</strong></td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div style="background-color: #f8f9fa; padding: 20px; border-radius: 6px; margin: 25px 0;">
                                <h2 style="margin: 0 0 15px 0; color: #333333; font-size: 18px; font-weight: 600;">📞 Important Information</h2>
                                <ul style="margin: 0; padding-left: 20px; color: #555555; font-size: 15px; line-height: 1.8;">
                                    <li style="margin-bottom: 8px;"><strong>School Contact:</strong> {school_phone}</li>
                                    <li style="margin-bottom: 8px;"><strong>School Email:</strong> {school_email}</li>
                                    <li style="margin-bottom: 8px;"><strong>School Address:</strong> {school_address}</li>
                                </ul>
                            </div>
                            
                            <p style="margin: 25px 0 0 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                We look forward to working together to ensure <strong>{student_name}</strong>''s success.
                            </p>
                            
                            <p style="margin: 20px 0 0 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Best regards,<br>
                                <strong style="color: #4facfe;">{school_name}</strong>
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0; color: #6c757d; font-size: 12px; line-height: 1.5;">
                                This is an automated email. Please do not reply to this message.<br>
                                For inquiries, please contact us at {school_phone} or {school_email}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>'
WHERE name = 'Welcome Email';

