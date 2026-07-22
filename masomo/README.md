# Kenyan Primary School Management System

A comprehensive, CBC-compliant School Management System built for Kenyan primary schools using PHP MVC architecture.

## Features

- **User Management**: Role-based access control (Super Admin, School Admin, Head Teacher, Teacher, Bursar, Parent, Student)
- **Student Management**: Complete student records with admission numbers, UPI, and parent linking
- **Staff & Teacher Management**: Teacher profiles with TSC numbers and class assignments
- **CBC Academic Module**: Full CBC curriculum support with learning areas, strands, sub-strands, and competency levels
- **Attendance System**: Daily student and teacher attendance tracking
- **Fee Management**: Comprehensive fee structure with M-Pesa STK Push integration
- **Parent Portal**: Parents can view student progress, attendance, and fee balances
- **Communication**: Internal announcements and notifications
- **Reports**: Academic performance, attendance, and financial reports with PDF/Excel export

## Technology Stack

- **Backend**: Core PHP (OOP, MVC) - No frameworks
- **Frontend**: HTML5 + Tailwind CSS
- **Database**: MySQL
- **Client-side**: Vanilla JavaScript (AJAX/Fetch API)
- **Server**: Apache (XAMPP/LAMP compatible)

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server
- XAMPP/LAMP/WAMP (for local development)

## Installation

### 1. Clone or Download

Place the project in your web server directory:
- **XAMPP**: `C:\xampp\htdocs\masomo`
- **LAMP**: `/var/www/html/masomo`
- **WAMP**: `C:\wamp64\www\masomo`

### 2. Database Setup

1. Open phpMyAdmin or MySQL command line
2. Import the database schema:
   ```sql
   mysql -u root -p < database/schema.sql
   ```
   Or use phpMyAdmin to import `database/schema.sql`

3. The database will be created as `masomo_school_db`

### 3. Configuration

1. Open `app/config/config.php`
2. Update database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'masomo_school_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

3. Update base URL:
   ```php
   define('BASE_URL', 'http://localhost/masomo');
   ```

4. (Optional) Configure M-Pesa credentials for payment integration:
   ```php
   define('MPESA_CONSUMER_KEY', 'your_consumer_key');
   define('MPESA_CONSUMER_SECRET', 'your_consumer_secret');
   define('MPESA_SHORTCODE', 'your_shortcode');
   define('MPESA_PASSKEY', 'your_passkey');
   ```

### 4. File Permissions

Ensure the uploads directory is writable:
```bash
chmod 755 public/uploads
```

### 5. Apache Configuration

The `.htaccess` file is already configured for clean URLs. Ensure mod_rewrite is enabled:
```bash
sudo a2enmod rewrite
sudo service apache2 restart
```

## Default Login Credentials

- **Email**: admin@school.co.ke
- **Password**: admin123

**⚠️ IMPORTANT**: Change the default password immediately after first login!

## Project Structure

```
masomo/
├── app/
│   ├── config/          # Configuration files
│   ├── controllers/     # MVC Controllers
│   ├── core/            # Core classes (Database, Auth, etc.)
│   ├── helpers/         # Helper classes (M-Pesa, etc.)
│   ├── middleware/      # Middleware classes
│   ├── models/          # MVC Models
│   └── views/           # MVC Views
├── database/            # Database schema and migrations
├── public/              # Public assets
│   └── uploads/         # File uploads directory
├── .htaccess           # Apache rewrite rules
├── index.php           # Main entry point
└── README.md           # This file
```

## Usage Guide

### Adding Students

1. Navigate to **Students** → **Add New Student**
2. Fill in student information
3. Add parent/guardian details
4. Select class and grade
5. System will auto-generate admission number and UPI

### Marking Attendance

1. Navigate to **Attendance** → **Mark Attendance**
2. Select class and date
3. Mark each student as Present, Absent, Late, or Excused
4. Save attendance

### Processing Payments

1. Navigate to **Fees** → **Invoices**
2. Select student invoice
3. Choose payment method:
   - **M-Pesa**: Enter phone number and amount, initiate STK Push
   - **Cash**: Enter amount and reference number
4. System will update invoice balance automatically

### CBC Assessments

1. Navigate to **Academic** → **Assessments**
2. Select student, learning area, strand, and sub-strand
3. Record competency level and score
4. Generate CBC report cards

## M-Pesa Integration

The system includes M-Pesa STK Push integration using Safaricom Daraja API.

### Setup

1. Register for Daraja API credentials at [Safaricom Developer Portal](https://developer.safaricom.co.ke/)
2. Get Consumer Key, Consumer Secret, Shortcode, and Passkey
3. Update credentials in `app/config/config.php`
4. Set callback URL: `http://yourdomain.com/masomo/api/mpesa/callback`

### Testing (Sandbox)

- Use test credentials provided by Safaricom
- Test phone numbers: 254708374149, 254712345678
- Test amounts: Any amount (will not be charged)

## Security Features

- Password hashing using `password_hash()`
- CSRF protection on all forms
- Prepared statements (PDO) to prevent SQL injection
- Input validation and sanitization
- Role-based access control (RBAC)
- Session management

## CBC Compliance

The system is fully compliant with Kenya's Competency-Based Curriculum (CBC):

- **Grades**: PP1, PP2, Grade 1-6
- **Learning Areas**: Per grade requirements
- **Strands & Sub-strands**: Organized by learning area
- **Competency Levels**: Exceeding, Meeting, Approaching, Below
- **Continuous Assessment**: Term-based assessments
- **Report Cards**: Auto-generated CBC-compliant reports

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Troubleshooting

### Clean URLs Not Working

1. Ensure mod_rewrite is enabled
2. Check `.htaccess` file exists
3. Verify Apache `AllowOverride All` is set

### Database Connection Error

1. Verify database credentials in `app/config/config.php`
2. Ensure MySQL service is running
3. Check database exists: `masomo_school_db`

### M-Pesa Payment Fails

1. Verify API credentials are correct
2. Check callback URL is accessible
3. Ensure server has SSL certificate (for production)
4. Check M-Pesa transaction logs in database

## Support

For issues, questions, or contributions, please contact the development team.

## License

This project is proprietary software for Kenyan Primary Schools.

## Version

Current Version: 1.0.0

---

**Built with ❤️ for Kenyan Primary Schools**

