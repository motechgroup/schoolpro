# Role Integration Summary

## Overview
This document summarizes the role-based access control (RBAC) implementation across the SchoolPro V2.0.0 system.

## Roles and Their Access Levels

### 1. Super Admin
**Full System Access**
- All modules and features
- User management
- Role management
- System settings
- Can bypass all restrictions (including fee balance for report cards)

**Controllers Access:** All controllers
**Menu Items:** All menu items visible

### 2. School Admin / School Manager
**Administrative Access**
- Student management (CRUD)
- Teacher management (CRUD)
- Class management
- Grade management
- Fee management
- Reports (all types)
- Announcements
- Can bypass fee balance for report cards

**Controllers Access:** 
- StudentController (full access)
- TeacherController
- ClassController
- GradeController
- FeeController
- FeeHeadController
- StudentFeeController
- FeeReportController
- PaymentController
- ReportController
- ExaminationController (can bypass fee balance)
- SubjectController
- AnnouncementController
- SettingsController
- CommunicationController

### 3. Head Teacher
**Academic Management**
- View students and teachers
- Manage classes
- Manage subjects
- Manage assessments
- Manage examinations
- Enter marks
- View attendance
- Create announcements
- View reports
- **Can bypass fee balance for report cards** (can view report cards even if student has outstanding fees)

**Controllers Access:**
- StudentController (view only)
- TeacherController (view)
- ClassController
- SubjectController
- AssessmentController
- ExaminationController (can bypass fee balance)
- AttendanceController (view)
- ReportController
- AnnouncementController

### 4. Teacher
**Limited Academic Access**
- View students (read-only, cannot manage)
- View own classes
- Mark attendance
- Create/edit assessments
- Enter examination marks
- View examinations
- **Cannot** generate student ID cards
- **Cannot** create/edit/delete students
- **Restricted** from report cards if student has fee balance

**Controllers Access:**
- StudentController (view only - restricted from create/edit/delete/generateId)
- AttendanceController (mark attendance)
- AssessmentController
- ExaminationController (enter marks, but restricted by fee balance for report cards)

**Restrictions:**
- Cannot create/edit/delete students
- Cannot generate student ID cards
- Cannot view report cards if student has outstanding fees

### 5. Accountant
**Financial Management Access**
- View students (for fee management)
- Full fee management (create, edit, view)
- Manage fee heads
- Assign fees to students
- Process payments
- Reconcile M-Pesa payments
- View financial reports
- View examinations
- **Restricted from report cards if student has fee balance**

**Controllers Access:**
- StudentController (view only)
- FeeController
- FeeHeadController
- StudentFeeController
- FeeReportController
- PaymentController
- ReportController
- MpesaController (reconcile functions)
- ExaminationController (can bypass fee balance)
- EquityBankController

**Menu Items:**
- Dashboard
- Students (view only)
- Fee Management (full access)
- Reports (financial reports)
- Examinations (view report cards)

### 6. Bursar
**Financial Management Access** (Similar to Accountant)
- View students
- Full fee management
- Process payments
- Reconcile payments
- View financial reports
- **Restricted from report cards if student has fee balance**

**Controllers Access:** Same as Accountant

### 7. Receptionist
**Student Registration Access**
- View students
- Create/edit students
- View parents
- Create/edit parents
- Mark attendance
- View announcements

**Controllers Access:**
- StudentController (create, edit, view - but not delete or generate ID)
- ParentController
- AttendanceController (mark attendance)

**Restrictions:**
- Cannot delete students
- Cannot generate student ID cards

### 8. Parent
**Limited Access - Own Children Only**
- View own children's information
- View children's assessments
- View children's attendance
- View fee balances and payments
- View fee reports
- View announcements
- **Restricted** from report cards if fees are outstanding

**Controllers Access:**
- FeeReportController (own children only)
- PaymentController (own children only)
- ExaminationController (restricted by fee balance)

**Restrictions:**
- Cannot view report cards if outstanding fees exist

### 9. Student
**Minimal Access**
- View own assessments
- View own attendance
- View announcements

**Controllers Access:**
- AssessmentController (own records only)
- AttendanceController (own records only)

## Key Integration Points

### Fee Balance Restrictions
- **Can bypass fee balance for report cards:** Super Admin, School Admin, School Manager, Head Teacher
- **Restricted by fee balance:** Bursar, Accountant, Teacher, Parent, Student

### Student Management
- **Full access:** Super Admin, School Admin, Receptionist
- **View only:** Head Teacher, Teacher, Accountant, Bursar
- **Cannot manage:** Teacher (cannot create/edit/delete/generate ID)

### Fee Management
- **Full access:** Super Admin, School Admin, Accountant, Bursar
- **View only:** Parent (own children)

### Report Cards
- **Always accessible (bypass fee balance):** Super Admin, School Admin, School Manager, Head Teacher
- **Restricted by fees:** Bursar, Accountant, Teacher, Parent, Student

## Database Updates Required

Run the following SQL files to ensure proper role setup:
1. `database/add_roles_and_teacher_photo.sql` - Creates accountant, school_manager, receptionist roles
2. `database/update_accountant_role.sql` - Updates accountant permissions

## Testing Checklist

- [ ] Accountant can access Fee Management menu
- [ ] Accountant can view students
- [ ] Accountant can assign fees to students
- [ ] Accountant can process payments
- [ ] Accountant can reconcile M-Pesa payments
- [ ] Accountant can view financial reports
- [ ] Accountant can view report cards (bypasses fee balance)
- [ ] Teacher cannot create/edit/delete students
- [ ] Teacher cannot generate ID cards
- [ ] Teacher restricted from report cards with fee balance
- [ ] All roles have appropriate menu items
- [ ] All controllers enforce proper role checks

