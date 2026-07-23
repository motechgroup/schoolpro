# 📘 SchoolPro V2.0.0 — Administrator Guide & System Tips

Welcome to **SchoolPro V2.0.0** (Masomo Primary & JSS School Management System). This guide is tailored for School Administrators, Super Admins, Head Teachers, and Bursars to help you navigate, operate, and maximize the features of the system effectively.

---

## 🎨 1. Branding & Identity

- **System Name**: SchoolPro V2.0.0 (Kenyan CBC Primary & JSS Management System)
- **System Logo**: Dynamic school logo displayed across the login screen, sidebar, student report cards, and receipts.
- **Customization**: Go to **Settings** or **CMS Settings** to upload your official school logo, badge, address, phone number, and official email.

---

## 👥 2. User Roles & Access Control

SchoolPro enforces strict Role-Based Access Control (RBAC) to ensure security and data privacy:

| Role | Primary Responsibilities | Main Access Scope |
| :--- | :--- | :--- |
| **Super Admin** | System configuration, database migrations, role management, full access | All Modules & System Settings |
| **School Manager / Admin** | Day-to-day administration, student/teacher records, announcements, fee oversight | All Modules except System Core |
| **Head Teacher** | Academic oversight, CBC assessments, examinations, attendance supervision | Students, Classes, CBC, Attendance, Reports |
| **Teacher** | Classroom attendance, student CBC assessments, examination entry | Assigned Classes, Attendance, Assessments |
| **Bursar / Accountant** | Fee structure setup, billing, payment processing (Cash, Bank, M-Pesa), receipts | Fee Management, Payments, Financial Reports |
| **Parent / Guardian** | Monitoring child attendance, CBC progress, report cards, fee balances | Parent Portal & Student Statements |

---

## 🏫 3. Academic Structure & CBC Integration

### Setting Up Grades & Classes
1. Navigate to **Grades** to view or create CBC levels (Playgroup, PP1, PP2, Grades 1–6, and JSS Grades 7–9).
2. Navigate to **Classes** to create class streams (e.g., *Grade 4 Blue*, *Grade 7 Gold*).
3. Assign **Class Teachers** to each stream for automated attendance routing.

### CBC Learning Areas & Assessments
- **Learning Areas**: Standard Ministry of Education CBC subjects (Literacy, Numeracy, Environmental, Creative Arts, Hygiene, Kiswahili, English, Integrated Science, Social Studies).
- **Competency Rating Scale**:
  - `EE` — Exceeding Expectations (4)
  - `ME` — Meeting Expectations (3)
  - `AE` — Approaching Expectations (2)
  - `BE` — Below Expectations (1)
- **Assessment Entry**: Teachers enter assessments per strand and sub-strand. System automatically computes overall term report cards.

---

## 👨‍🎓 4. Student & Parent Management

### Adding & Registering Students
1. Go to **Students** → **Add New Student**.
2. Fill in:
   - **Admission Number**: Auto-generated or custom.
   - **MoE UPI**: Unique Personal Identifier (Ministry of Education).
   - **Personal Details**: Name, Gender, DOB, Date of Admission.
   - **Class Assignment**: Select active class stream.
3. Link the student to a **Parent/Guardian** record so parents can log into the Parent Portal.

---

## 💰 5. Fee Management & Financial Operations

### Fee Structure (Tuition vs. Other Fee Heads)
SchoolPro V2.0.0 categorizes billing into two main financial buckets:
1. **Tuition Fees**: Core academic tuition billed per term/grade.
2. **Other Fee Heads**: Itemized non-tuition billing:
   - Admission Fees
   - Lunch / Boarding Fund
   - School Bus / Transport
   - Uniform & Dress Code
   - Examination & KNEC Fees
   - ICT & Library Fund
   - Development & Building Fund

### Processing Payments & Receipts
- **Payment Methods**: Cash, Direct Bank Deposit (KCB, Equity), and **M-Pesa STK Push / Paybill**.
- **Automated Receipting**: Every transaction generates a unique receipt number and updates student invoice balances immediately.
- **Tuition vs Other Breakdown**: Reports provide real-time executive summaries separating Tuition collections from Other Fee Head collections.

---

## 📅 6. Daily Attendance Tracking

- **Marking Attendance**: Class teachers can mark daily attendance in under 30 seconds using the **Bulk Attendance Tool** (`Present`, `Absent`, `Late`, `Excused`).
- **Attendance Rate**: System calculates live attendance percentages shown on the Executive Dashboard.

---

## 📊 7. Executive Reports & Analytics

Navigate to **Reports** to generate print-ready PDF and screen analytics:
1. **Financial Report**: Total billed, total collected, outstanding balances, tuition vs other fee head collection breakdown.
2. **Student Report**: Filterable student profiles, gender distribution, stream sizes.
3. **Attendance Summary**: Class-wise and monthly student attendance logs.

---

## 💡 8. Pro Administrator Tips & System Maintenance

> [!TIP]
> **Tip 1: Daily End-of-Day Reconciliation**
> Bursars should run the **Financial Report** (`/reports/financial`) at the end of each day to cross-check cash/bank receipts against M-Pesa transactions.

> [!IMPORTANT]
> **Tip 2: Environment File Protection (`.env`)**
> Ensure your live `.env` file on cPanel contains your live database credentials (`DB_NAME`, `DB_USER`, `DB_PASS`) wrapped in double quotes if there are special characters.

> [!NOTE]
> **Tip 3: Updating Live Code via cPanel Git**
> To update your live website after a code release:
> 1. Log into cPanel → **Git™ Version Control**.
> 2. Go to **Pull or Deploy**.
> 3. Click **Update from Remote**.

> [!WARNING]
> **Tip 4: Database Safety**
> Always take a full database dump/backup via phpMyAdmin before performing major end-of-year academic rollover or database schema migrations.

---

## 📞 Support & System Version
- **Current Version**: SchoolPro V2.0.0
- **Framework**: Custom PHP MVC with Tailwind CSS & MySQL
- **Timezone**: `Africa/Nairobi`
