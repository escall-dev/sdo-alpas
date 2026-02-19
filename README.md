# SDO ALPAS

**Schools Division Office of San Pedro City - Authority to Travel, Locator and Pass slip Approval System**

A comprehensive web-based system for managing Authority to Travel (AT) requests and Locator Slips for the Schools Division Office of San Pedro City, Department of Education.

## Overview

SDO ALPAS streamlines the approval workflow for employee travel requests and location tracking, providing role-based access control and automated routing based on organizational units.

## Features

### Authority to Travel Management
- **Multiple Travel Categories**: Official (Local/National) and Personal travel requests
- **Smart Routing**: Automatic routing to appropriate unit heads based on employee office
- **Unit-Based Approvals**: CID Chief, SGOD Chief, and OSDS Chief (AO V) can directly approve requests from their units
- **Executive Override**: Superadmin/SDS can approve requests at any stage
- **Document Generation**: Automated DOCX generation with proper authority signatures
- **Tracking System**: Unique tracking numbers (AT-YYYY-#####) for all requests

### Locator Slip Management
- **Quick Filing**: Simplified form for same-day local movements
- **Dual Approval**: ASDS and OSDS Chief can approve Locator Slips
- **Control Numbers**: Auto-generated LS control numbers (LS-YYYY-######)
- **Document Export**: DOCX generation for approved slips

### Dashboard & Analytics
- **Role-Specific Views**: Customized dashboards for employees, unit heads, and administrators
- **Real-time Statistics**: Personal request counts and unit-level metrics
- **Pending Queue**: Dedicated view for requests awaiting approval
- **Weekly Reports**: Track requests filed and approved weekly

### User Management
- **Role-Based Access Control**: Six user roles (Superadmin, ASDS, Unit Chiefs, User)
- **Office Assignments**: Employees mapped to organizational units (CID, SGOD, OSDS units)
- **Activity Logging**: Complete audit trail of all system actions
- **Token-Based Authentication**: Secure session management

## System Requirements

- **Web Server**: Apache 2.4+
- **PHP**: 7.4+ or 8.0+
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **PHP Extensions**: PDO, PDO_MySQL, mbstring, OpenSSL
- **Composer**: For dependency management

## Installation

### 1. Clone or Download the Repository
```bash
git clone https://github.com/escall-dev/sdo-alpas.git
cd sdo-alpas
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Database Setup
```sql
-- Import the database schema
mysql -u root -p < sql/sdo_atlas.sql

-- Or run the migration script for existing installations
mysql -u root -p < sql/migration_at_routing.sql
```

### 4. Configure Database Connection
Edit `config/database.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sdo_atlas');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 5. Configure Mail Settings (Optional)
Edit `config/mail_config.php` for email notifications.

### 6. Set Permissions
```bash
chmod -R 755 uploads/
chmod -R 755 uploads/generated/
```

### 7. Access the System
Navigate to `http://localhost/SDO-alpas/admin/` in your web browser.

**Default Credentials:**
- Username: `admin`
- Password: Check database or create your first admin user

## User Roles & Permissions

| Role | Code | Permissions |
|------|------|-------------|
| **Superadmin (SDS)** | ROLE_SUPERADMIN | Full system access, executive override, user management |
| **ASDS** | ROLE_ASDS | Approve all AT requests, approve Locator Slips |
| **OSDS Chief (AO V)** | ROLE_OSDS_CHIEF | Approve AT from OSDS units, approve Locator Slips |
| **CID Chief** | ROLE_CID_CHIEF | Approve AT from CID office |
| **SGOD Chief** | ROLE_SGOD_CHIEF | Approve AT from SGOD office |
| **User (Employee)** | ROLE_USER | File AT and LS requests, view own submissions |

## Organizational Units

### OSDS Units (Supervised by OSDS Chief)
- OSDS, Supply, Records, HR, Admin, Personnel, Cashier, Finance

### CID (Supervised by CID Chief)
- Curriculum Implementation Division

### SGOD (Supervised by SGOD Chief)
- School Governance Operations Division

## Approval Workflow

### Authority to Travel
1. **Employee** files AT request
2. Request routes to **Unit Head** based on employee's office
3. **Unit Head** approves (for regular employees)
4. If unit head files request → routes to **ASDS** for approval
5. **Superadmin/SDS** can approve at any stage (executive override)

### Locator Slip
1. **Employee** files LS request
2. Request visible to **ASDS** and **OSDS Chief**
3. Either approver can approve/reject
4. **Superadmin** can also approve

## Technology Stack

- **Backend**: PHP 7.4+/8.0+
- **Database**: MySQL with PDO
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Document Generation**: PHPOffice/PhpWord
- **Icons**: Font Awesome 6.5+, Boxicons
- **Fonts**: Plus Jakarta Sans, JetBrains Mono

## Project Structure

```
SDO-alpas/
├── admin/                  # Main application pages
│   ├── api/               # API endpoints
│   ├── assets/            # CSS, JS, logos
│   └── database/          # Database backups
├── config/                # Configuration files
├── includes/              # Shared components (header, footer, auth)
├── models/                # Data models (MVC pattern)
├── services/              # Business logic services
├── sql/                   # Database schemas and migrations
├── uploads/               # User uploads and generated files
└── vendor/                # Composer dependencies
```

## License

This project is proprietary software developed for the Schools Division Office of San Pedro City.

## Support

For issues, questions, or feature requests, please contact the development team.

---

## Developers

**Alexander Joerenz Escallente & Redgine Pinedes**
