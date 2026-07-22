# CMS Extended Features Documentation

This document describes all the extended features added to the CMS system.

## Features Overview

1. **Fee Management** - Setup fees and yearly maintenance fees
2. **Revenue Tracking** - View and manage revenue from system sales
3. **School Owners Management** - Manage school owners separately from admins
4. **CMS Settings** - Centralized settings management including system version
5. **Communication Module** - Email and internal notifications
6. **System Logs** - Comprehensive logging for CMS and school systems
7. **Backup Module** - Database and file backup system

## Database Tables

### New Tables Created

1. **cms_school_owners** - School owner information
2. **cms_school_fees** - Setup and maintenance fees
3. **cms_payments** - Payment records and revenue tracking
4. **cms_notifications** - Internal notification system
5. **cms_email_communications** - Email communication records
6. **cms_system_logs** - CMS admin and system logs
7. **school_system_logs** - School system logs (for school super admin)
8. **cms_backups** - Backup records
9. **cms_settings** - CMS configuration settings

### Updated Tables

- **cms_schools** - Added fields:
  - `setup_fee_paid` (BOOLEAN)
  - `setup_fee_amount` (DECIMAL)
  - `maintenance_fee_paid` (BOOLEAN)
  - `maintenance_fee_amount` (DECIMAL)
  - `last_maintenance_payment_date` (DATE)

## Installation

1. **Run the database migration**:
   ```sql
   mysql -u root -p masomo_school_db < database/add_cms_extended_features.sql
   ```

2. **Create backup directory**:
   ```bash
   mkdir -p backups
   chmod 755 backups
   ```

## Features Details

### 1. Fee Management

**Location**: School Details Page → Fees Section

- **Setup Fee**: One-time fee when school is first set up
- **Maintenance Fee**: Yearly recurring fee
- Admin can create fees, mark as paid, and track payment status
- Fees can be waived if needed

**Usage**:
- Go to School Details
- Click "Create Setup Fee" or "Create Maintenance Fee"
- Enter amount and due date
- Mark as paid when payment is received

### 2. Revenue Tracking

**Location**: `/cms/revenue`

- View all payments received
- Filter by date range, payment type, school
- View revenue statistics:
  - Total revenue
  - Setup fee revenue
  - Maintenance fee revenue
  - Monthly breakdown
- Record new payments manually

**Usage**:
- Navigate to Revenue section in CMS
- View payments and statistics
- Record new payments when received

### 3. School Owners Management

**Location**: School Details Page → Owners Section

- Manage school owners separately from system admins
- Track owner information:
  - Name, email, phone
  - Address, ID number
  - Primary owner designation
- Multiple owners per school supported

**Usage**:
- Go to School Details
- Click "Manage Owners"
- Add, edit, or delete owners
- Set primary owner

### 4. CMS Settings

**Location**: `/cms/settings`

**Settings Categories**:
- **Fees**: Default setup and maintenance fee amounts
- **System**: System version, general settings
- **Backup**: Backup retention, schedule, auto-backup
- **Email**: Email sender configuration
- **Notification**: Notification system settings

**System Version Management**:
- Set current system version
- Version is displayed on school systems
- Track which schools are on which version

**Usage**:
- Navigate to Settings
- Update settings by category
- Set system version
- Configure backup settings

### 5. Communication Module

**Location**: `/cms/communication`

**Features**:
- **Email Communication**:
  - Send emails to individual schools
  - Send emails to school owners
  - Broadcast to all schools
  - Track email status (sent, failed, bounced)

- **Internal Notifications**:
  - Send notifications to schools
  - Send notifications to owners
  - Send notifications to all schools
  - Notification types: info, warning, error, success, payment, system_update

**Usage**:
- Navigate to Communication
- Choose Email or Notification
- Select recipient type
- Compose and send

### 6. System Logs

**Location**: `/cms/logs`

**CMS Admin Logs**:
- Track all CMS admin actions
- Filter by log type, school, action, date
- View request/response data
- Monitor system health

**School System Logs**:
- Logs for each school system
- Track user actions
- Module-based logging
- Accessible from school details page

**Usage**:
- Navigate to Logs section
- Filter logs as needed
- View detailed log information
- Export logs if needed

### 7. Backup Module

**Location**: `/cms/backups`

**Features**:
- **Backup Types**:
  - Database backup (CMS database)
  - School database backup (individual school)
  - Files backup
  - Full backup (database + files)

- **Backup Management**:
  - Create manual backups
  - Schedule automatic backups
  - Download backups
  - Delete old backups
  - View backup status

**Usage**:
- Navigate to Backups
- Click "Create Backup"
- Select backup type and school (if applicable)
- Download or delete backups as needed

## API Integration

### School Systems Reporting Version

School systems can report their version via API:

```php
POST /cms/api/heartbeat
Headers:
  X-API-Key: {school_api_key}
  X-API-Secret: {school_api_secret}
Body:
{
  "version": "1.0.0",
  "status": "online",
  ...
}
```

## Helper Classes

### SystemLogHelper (School Systems)

```php
SystemLogHelper::log('user_login', 'User logged in', 'auth', 'success');
```

### CmsLogHelper (CMS)

```php
CmsLogHelper::log('suspend_school', 'School suspended', $schoolId, 'success');
```

## Menu Updates

The CMS sidebar should include:
- Revenue
- Owners (accessible from School Details)
- Communication
- Logs
- Backups
- Settings

## System Version Display

The system version set in CMS Settings is displayed on school systems. This helps track which schools have been updated.

## Notification System

Notifications appear in:
- CMS dashboard (for CMS admins)
- School dashboards (for school admins)
- Owner portals (for school owners)

## Backup Storage

Backups are stored in: `{BASE_PATH}/backups/`

Backup retention is configurable in Settings (default: 30 days).

## Security Considerations

1. All sensitive operations are logged
2. Backup files should be stored securely
3. Email communications are logged
4. Payment records are immutable (for audit purposes)
5. System logs include IP addresses and user agents

## Future Enhancements

- Automated email sending integration (SMTP/API)
- Scheduled backup jobs (cron)
- Payment gateway integration
- Advanced reporting and analytics
- Export functionality for logs and reports

