# SDO ATLAS - System Architecture Documentation

**Schools Division Office - Authority to Travel and Locator Approval System**

---

## Table of Contents
1. [System Overview](#1-system-overview)
2. [System Architecture Diagram](#2-system-architecture-diagram)
3. [Component Architecture](#3-component-architecture)
4. [Data Flow Architecture](#4-data-flow-architecture)
5. [Database Schema Overview](#5-database-schema-overview)
6. [Security Architecture](#6-security-architecture)
7. [Request Processing Flows](#7-request-processing-flows)

---

## 1. System Overview

SDO ATLAS is a comprehensive web-based document approval system designed for the Schools Division Office of San Pedro City, Department of Education. The system manages:

- **Authority to Travel (AT)** requests - Official/Personal, Local/International travel
- **Locator Slips (LS)** - Same-day local movement tracking
- **Role-Based Approval Workflows** - Multi-tier approval routing
- **Document Generation** - Automated DOCX generation

### Technology Stack

| Layer | Technology |
|-------|------------|
| **Frontend** | HTML5, CSS3, JavaScript, Bootstrap |
| **Backend** | PHP 7.4+ / 8.0+ |
| **Database** | MySQL 5.7+ / MariaDB 10.3+ |
| **Web Server** | Apache 2.4+ (XAMPP) |
| **Document Generation** | PHPWord Library |
| **Authentication** | Token-Based Session Management |

---

## 2. System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              SDO ATLAS SYSTEM ARCHITECTURE                       │
└─────────────────────────────────────────────────────────────────────────────────┘

                                    ┌─────────────┐
                                    │   CLIENTS   │
                                    └──────┬──────┘
                                           │
            ┌──────────────────────────────┼──────────────────────────────┐
            │                              │                              │
            ▼                              ▼                              ▼
    ┌───────────────┐            ┌───────────────┐            ┌───────────────┐
    │   Employee    │            │  Unit Head    │            │  Admin/ASDS   │
    │   Browser     │            │   Browser     │            │   Browser     │
    │  (User Role)  │            │ (Chief Role)  │            │ (SDS/ASDS)    │
    └───────┬───────┘            └───────┬───────┘            └───────┬───────┘
            │                            │                            │
            └────────────────────────────┼────────────────────────────┘
                                         │
                                         ▼
┌────────────────────────────────────────────────────────────────────────────────┐
│                            PRESENTATION LAYER                                   │
│  ┌──────────────────────────────────────────────────────────────────────────┐  │
│  │                         Admin Panel (admin/)                              │  │
│  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────────────┐ │  │
│  │  │  index.php  │ │ login.php   │ │ profile.php │ │ authority-to-      │ │  │
│  │  │ (Dashboard) │ │ (Auth)      │ │ (User)      │ │ travel.php         │ │  │
│  │  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────────────┘ │  │
│  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────────────┐ │  │
│  │  │ locator-    │ │ my-         │ │ users.php   │ │ oic-management.php │ │  │
│  │  │ slips.php   │ │ requests.php│ │ (Admin)     │ │ (Delegation)       │ │  │
│  │  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────────────┘ │  │
│  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────────────────────────────┐ │  │
│  │  │ logs.php    │ │ register.php│ │ unit-routing.php (Config)          │ │  │
│  │  │ (Audit)     │ │ (Admin)     │ │                                     │ │  │
│  │  └─────────────┘ └─────────────┘ └─────────────────────────────────────┘ │  │
│  └──────────────────────────────────────────────────────────────────────────┘  │
│                                                                                 │
│  ┌────────────────────────────────────────┐  ┌───────────────────────────────┐ │
│  │         Includes (includes/)           │  │     Assets (admin/assets/)    │ │
│  │  ┌───────────┐ ┌───────────┐           │  │  ┌─────────┐ ┌─────────────┐  │ │
│  │  │ header.php│ │ footer.php│           │  │  │  CSS    │ │  JavaScript │  │ │
│  │  └───────────┘ └───────────┘           │  │  │admin.css│ │  admin.js   │  │ │
│  │  ┌───────────────────────────┐         │  │  └─────────┘ └─────────────┘  │ │
│  │  │      auth.php             │         │  │  ┌─────────────────────────┐  │ │
│  │  │  (AdminAuth Class)        │         │  │  │       Logos/Images      │  │ │
│  │  └───────────────────────────┘         │  │  └─────────────────────────┘  │ │
│  └────────────────────────────────────────┘  └───────────────────────────────┘ │
└────────────────────────────────────────────────────────────────────────────────┘
                                         │
                                         ▼
┌────────────────────────────────────────────────────────────────────────────────┐
│                             BUSINESS LOGIC LAYER                                │
│  ┌──────────────────────────────────────────────────────────────────────────┐  │
│  │                           Models (models/)                                │  │
│  │  ┌─────────────────────┐  ┌─────────────────────┐  ┌──────────────────┐  │  │
│  │  │  AuthorityToTravel  │  │    LocatorSlip      │  │   AdminUser      │  │  │
│  │  │    .php             │  │      .php           │  │     .php         │  │  │
│  │  │                     │  │                     │  │                  │  │  │
│  │  │ - determineRouting  │  │ - create()          │  │ - authenticate() │  │  │
│  │  │ - create()          │  │ - approve()         │  │ - getById()      │  │  │
│  │  │ - approve()         │  │ - reject()          │  │ - getByRole()    │  │  │
│  │  │ - recommend()       │  │ - getStatistics()   │  │ - updateProfile()│  │  │
│  │  │ - reject()          │  │ - getPending()      │  │                  │  │  │
│  │  │ - getPending()      │  │                     │  │                  │  │  │
│  │  └─────────────────────┘  └─────────────────────┘  └──────────────────┘  │  │
│  │  ┌─────────────────────┐  ┌─────────────────────┐  ┌──────────────────┐  │  │
│  │  │   OICDelegation     │  │   SessionToken      │  │   ActivityLog    │  │  │
│  │  │      .php           │  │      .php           │  │      .php        │  │  │
│  │  │                     │  │                     │  │                  │  │  │
│  │  │ - create()          │  │ - create()          │  │ - log()          │  │  │
│  │  │ - getActiveOIC()    │  │ - validate()        │  │ - getLogs()      │  │  │
│  │  │ - deactivate()      │  │ - revoke()          │  │ - getLogsCount() │  │  │
│  │  │ - getEffective      │  │ - cleanup()         │  │                  │  │  │
│  │  │   ApproverUserId()  │  │                     │  │                  │  │  │
│  │  └─────────────────────┘  └─────────────────────┘  └──────────────────┘  │  │
│  └──────────────────────────────────────────────────────────────────────────┘  │
│                                                                                 │
│  ┌──────────────────────────────────────────────────────────────────────────┐  │
│  │                          Services (services/)                             │  │
│  │  ┌─────────────────────────────┐  ┌───────────────────────────────────┐  │  │
│  │  │      TrackingService        │  │        DocxGenerator              │  │  │
│  │  │          .php               │  │           .php                    │  │  │
│  │  │                             │  │                                   │  │  │
│  │  │  - generateLSNumber()       │  │  - generateLocatorSlip()          │  │  │
│  │  │  - generateATNumber()       │  │  - generateATLocal()              │  │  │
│  │  │  - parseTrackingNumber()    │  │  - generateATNational()           │  │  │
│  │  │                             │  │  - generateATPersonal()           │  │  │
│  │  │  Format: AT-YYYY-NNNNN      │  │                                   │  │  │
│  │  │  Format: LS-YYYY-NNNNNN     │  │  Uses: PHPWord TemplateProcessor  │  │  │
│  │  └─────────────────────────────┘  └───────────────────────────────────┘  │  │
│  └──────────────────────────────────────────────────────────────────────────┘  │
│                                                                                 │
│  ┌──────────────────────────────────────────────────────────────────────────┐  │
│  │                       Configuration (config/)                             │  │
│  │  ┌─────────────────┐  ┌───────────────────┐  ┌─────────────────────────┐ │  │
│  │  │  database.php   │  │  admin_config.php │  │    mail_config.php      │ │  │
│  │  │                 │  │                   │  │                         │ │  │
│  │  │  - DB_HOST      │  │  - ROLE_*         │  │  - SMTP Settings        │ │  │
│  │  │  - DB_NAME      │  │  - UNIT_HEAD_*    │  │  - Email Templates      │ │  │
│  │  │  - DB_USER      │  │  - OSDS_UNITS     │  │                         │ │  │
│  │  │  - DB_PASS      │  │  - ROLE_OFFICE_MAP│  │                         │ │  │
│  │  │  - Database     │  │  - STATUS_CONFIG  │  │                         │ │  │
│  │  │    Singleton    │  │  - DOCX_TEMPLATES │  │                         │ │  │
│  │  └─────────────────┘  └───────────────────┘  └─────────────────────────┘ │  │
│  └──────────────────────────────────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────────────────────────────────┘
                                         │
                                         ▼
┌────────────────────────────────────────────────────────────────────────────────┐
│                              DATA ACCESS LAYER                                  │
│  ┌──────────────────────────────────────────────────────────────────────────┐  │
│  │                     Database Class (Singleton Pattern)                    │  │
│  │                                                                           │  │
│  │   ┌─────────────────────────────────────────────────────────────────┐    │  │
│  │   │  PDO Connection with:                                            │    │  │
│  │   │  - ERRMODE_EXCEPTION                                             │    │  │
│  │   │  - FETCH_ASSOC                                                   │    │  │
│  │   │  - Prepared Statements (SQL Injection Prevention)                │    │  │
│  │   └─────────────────────────────────────────────────────────────────┘    │  │
│  │                                                                           │  │
│  │   Methods: getInstance() | getConnection() | query() | lastInsertId()    │  │
│  └──────────────────────────────────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────────────────────────────────┘
                                         │
                                         ▼
┌────────────────────────────────────────────────────────────────────────────────┐
│                              PERSISTENCE LAYER                                  │
│  ┌──────────────────────────────────────────────────────────────────────────┐  │
│  │                        MySQL / MariaDB Database                           │  │
│  │                                                                           │  │
│  │  ┌──────────────────────────────────────────────────────────────────────┐│  │
│  │  │                        DATABASE: sdo_atlas                           ││  │
│  │  │                                                                      ││  │
│  │  │  CORE TABLES:                                                        ││  │
│  │  │  ┌────────────────┐ ┌────────────────┐ ┌─────────────────────────┐  ││  │
│  │  │  │  admin_users   │ │  admin_roles   │ │  authority_to_travel    │  ││  │
│  │  │  └────────────────┘ └────────────────┘ └─────────────────────────┘  ││  │
│  │  │  ┌────────────────┐ ┌────────────────┐ ┌─────────────────────────┐  ││  │
│  │  │  │ locator_slips  │ │ session_tokens │ │    activity_logs        │  ││  │
│  │  │  └────────────────┘ └────────────────┘ └─────────────────────────┘  ││  │
│  │  │  ┌────────────────┐ ┌────────────────┐ ┌─────────────────────────┐  ││  │
│  │  │  │oic_delegations │ │master_offices  │ │  unit_routing_config    │  ││  │
│  │  │  └────────────────┘ └────────────────┘ └─────────────────────────┘  ││  │
│  │  │  ┌─────────────────────────────────────────────────────────────────┐││  │
│  │  │  │                  tracking_sequences                             │││  │
│  │  │  └─────────────────────────────────────────────────────────────────┘││  │
│  │  └──────────────────────────────────────────────────────────────────────┘│  │
│  └──────────────────────────────────────────────────────────────────────────┘  │
│                                                                                 │
│  ┌──────────────────────────────────────────────────────────────────────────┐  │
│  │                          File System Storage                              │  │
│  │  ┌─────────────────────────────┐  ┌───────────────────────────────────┐  │  │
│  │  │  reference-forms/doc-forms/ │  │      uploads/generated/           │  │  │
│  │  │  (DOCX Templates)           │  │  (Generated Documents)            │  │  │
│  │  │                             │  │                                   │  │  │
│  │  │  - at_local.docx            │  │  - AT_AT-2026-00001_*.docx        │  │  │
│  │  │  - at_national.docx         │  │  - LS_LS-2026-000001_*.docx       │  │  │
│  │  │  - at_personal.docx         │  │                                   │  │  │
│  │  │  - locator_slip.docx        │  │                                   │  │  │
│  │  └─────────────────────────────┘  └───────────────────────────────────┘  │  │
│  └──────────────────────────────────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Component Architecture

### 3.1 User Roles Hierarchy

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         USER ROLES & PERMISSIONS                             │
└─────────────────────────────────────────────────────────────────────────────┘

                            ┌─────────────────┐
                            │   SUPERADMIN    │
                            │     (SDS)       │
                            │   Role ID: 1    │
                            └────────┬────────┘
                    ┌────────────────┼────────────────┐
                    │                │                │
                    ▼                ▼                ▼
           ┌─────────────┐  ┌─────────────┐  Executive Override
           │    ASDS     │  │  All System │  for any request
           │  Role ID: 2 │  │   Access    │
           └──────┬──────┘  └─────────────┘
                  │
                  │ Final Approval Authority
                  │ for ALL AT Requests
                  ▼
    ┌─────────────┴─────────────────────────────────────┐
    │                 UNIT HEAD LEVEL                    │
    │        (Recommending Authority Stage)              │
    └─────────────┬────────────────────────────────────┬┘
                  │                                    │
    ┌─────────────┴─────────────────┐    ┌────────────┴────────────┐
    │         CID CHIEF             │    │       SGOD CHIEF        │
    │         Role ID: 4            │    │       Role ID: 5        │
    │                               │    │                         │
    │  Supervises:                  │    │  Supervises:            │
    │  - CID (Main)                 │    │  - SGOD (Main)          │
    │  - IM (Instructional Mgmt)    │    │  - SMME                 │
    │  - LRM (Learning Resources)   │    │  - HRD                  │
    │  - ALS (Alternative Learning) │    │  - SMN                  │
    │  - DIS (Division Info System) │    │  - PR, DRRM, EF         │
    └───────────────────────────────┘    │  - SHN_DENTAL/MEDICAL   │
                                         └─────────────────────────┘
                  │
    ┌─────────────┴─────────────────────────────────────┐
    │               OSDS CHIEF (AO V)                    │
    │                 Role ID: 3                         │
    │                                                    │
    │  Supervises ALL Administrative Units:              │
    │  OSDS, Personnel, Property & Supply, Records,      │
    │  Cash, Procurement, General Services, Legal,       │
    │  ICT, Accounting, Budget                           │
    │                                                    │
    │  Also: Locator Slip Approval Authority             │
    └────────────────────────────────────────────────────┘
                  │
                  ▼
    ┌─────────────────────────────────────────────────────┐
    │                 REGULAR USER                         │
    │                 Role ID: 6                           │
    │                                                      │
    │  Permissions:                                        │
    │  - File own AT requests                              │
    │  - File own Locator Slips                            │
    │  - View own request history                          │
    │  - Download approved documents                       │
    └─────────────────────────────────────────────────────┘
```

### 3.2 OIC Delegation System

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                      OIC (Officer-In-Charge) DELEGATION                      │
└─────────────────────────────────────────────────────────────────────────────┘

    ┌─────────────────┐         Delegates To         ┌─────────────────┐
    │   Unit Head     │ ─────────────────────────▶   │   OIC User      │
    │  (e.g., CID     │                              │  (Any Employee) │
    │   Chief)        │                              │                 │
    └────────┬────────┘                              └────────┬────────┘
             │                                                │
             │  Delegation Record:                            │
             │  - start_date                                  │
             │  - end_date                                    │
             │  - is_active                                   │
             │                                                │
             ▼                                                ▼
    ┌─────────────────────────────────────────────────────────────────────────┐
    │                          oic_delegations TABLE                           │
    │                                                                          │
    │  • unit_head_user_id (FK → admin_users)                                 │
    │  • unit_head_role_id (FK → admin_roles)                                 │
    │  • oic_user_id (FK → admin_users)                                       │
    │  • start_date / end_date                                                │
    │  • is_active                                                            │
    └─────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
    ┌─────────────────────────────────────────────────────────────────────────┐
    │                    EFFECTIVE APPROVER RESOLUTION                         │
    │                                                                          │
    │    getEffectiveApproverUserId(roleId, unitHeadUserId)                   │
    │         │                                                                │
    │         ├──▶ Check for active OIC delegation                            │
    │         │         │                                                      │
    │         │         ├──▶ OIC exists & active? → Return OIC User ID        │
    │         │         │                                                      │
    │         │         └──▶ No OIC? → Return Unit Head User ID               │
    │         │                                                                │
    │         └──▶ Assign request to effective approver                       │
    └─────────────────────────────────────────────────────────────────────────┘
```

---

## 4. Data Flow Architecture

### 4.1 Authority to Travel (AT) Request Flow

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│              AUTHORITY TO TRAVEL - COMPLETE DATA FLOW DIAGRAM                    │
└─────────────────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   EMPLOYEE   │
│   (User)     │
└──────┬───────┘
       │
       │ 1. Fills AT Request Form
       │    - Travel Category (Official/Personal)
       │    - Travel Scope (Local/International)
       │    - Travel Details
       ▼
┌──────────────────────────────────────────────────────────────────────────────────┐
│                         authority-to-travel.php                                   │
│  ┌─────────────────────────────────────────────────────────────────────────────┐ │
│  │ STEP 1: Validate Input                                                      │ │
│  │  - Check required fields                                                    │ │
│  │  - Validate date ranges                                                     │ │
│  │  - Sanitize input data                                                      │ │
│  └─────────────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────┬─────────────────────────────────────┘
                                             │
                                             ▼
┌──────────────────────────────────────────────────────────────────────────────────┐
│                           TrackingService.php                                     │
│  ┌─────────────────────────────────────────────────────────────────────────────┐ │
│  │ STEP 2: Generate Tracking Number                                            │ │
│  │  - Lock tracking_sequences table                                            │ │
│  │  - Get/Create sequence for AT-{YEAR}                                        │ │
│  │  - Increment counter atomically                                             │ │
│  │  - Return: AT-2026-00001                                                    │ │
│  └─────────────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────┬─────────────────────────────────────┘
                                             │
                                             ▼
┌──────────────────────────────────────────────────────────────────────────────────┐
│                          AuthorityToTravel.php (Model)                            │
│  ┌─────────────────────────────────────────────────────────────────────────────┐ │
│  │ STEP 3: Determine Routing                                                   │ │
│  │                                                                             │ │
│  │  determineRouting(requesterRoleId, requesterOfficeId, office, travelScope)  │ │
│  │         │                                                                   │ │
│  │         ├──▶ Is requester a Unit Head?                                      │ │
│  │         │         │                                                         │ │
│  │         │    YES ─┴──▶ Route directly to ASDS (final stage)                 │ │
│  │         │                                                                   │ │
│  │         └──▶ NO: Regular Employee                                           │ │
│  │                   │                                                         │ │
│  │                   ▼                                                         │ │
│  │         ┌─────────────────────────────────────────────────────────────┐     │ │
│  │         │  Get Recommender Role by Office:                           │     │ │
│  │         │                                                            │     │ │
│  │         │  1. Query unit_routing_config (database-driven)            │     │ │
│  │         │  2. Fallback: OSDS_UNITS array check                       │     │ │
│  │         │  3. Fallback: ROLE_OFFICE_MAP static mapping               │     │ │
│  │         │                                                            │     │ │
│  │         │  Result:                                                   │     │ │
│  │         │  CID/IM/LRM/ALS/DIS      → CID_CHIEF (Role 4)              │     │ │
│  │         │  SGOD/SMME/HRD/SMN/etc.  → SGOD_CHIEF (Role 5)             │     │ │
│  │         │  Personnel/ICT/Cash/etc. → OSDS_CHIEF (Role 3)             │     │ │
│  │         └─────────────────────────────────────────────────────────────┘     │ │
│  └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                   │
│  ┌─────────────────────────────────────────────────────────────────────────────┐ │
│  │ STEP 4: Check for Active OIC                                                │ │
│  │                                                                             │ │
│  │  OICDelegation::getActiveOICForUnit(recommenderRoleId)                      │ │
│  │         │                                                                   │ │
│  │         ├──▶ Active OIC exists? → assigned_approver = OIC User              │ │
│  │         │                                                                   │ │
│  │         └──▶ No OIC? → assigned_approver = Unit Head User                   │ │
│  └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                   │
│  ┌─────────────────────────────────────────────────────────────────────────────┐ │
│  │ STEP 5: Create AT Record                                                    │ │
│  │                                                                             │ │
│  │  INSERT INTO authority_to_travel:                                           │ │
│  │  - at_tracking_no          (generated)                                      │ │
│  │  - user_id                 (requester)                                      │ │
│  │  - travel_category         (official/personal)                              │ │
│  │  - travel_scope            (local/international)                            │ │
│  │  - status                  ('pending')                                      │ │
│  │  - routing_stage           ('recommending' or 'final')                      │ │
│  │  - current_approver_role   (CID_CHIEF/SGOD_CHIEF/OSDS_CHIEF/ASDS)           │ │
│  │  - assigned_approver_user_id (effective approver)                           │ │
│  └─────────────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────┬─────────────────────────────────────┘
                                             │
                                             ▼
┌──────────────────────────────────────────────────────────────────────────────────┐
│                             ActivityLog.php                                       │
│  ┌─────────────────────────────────────────────────────────────────────────────┐ │
│  │ STEP 6: Log Activity                                                        │ │
│  │                                                                             │ │
│  │  INSERT INTO activity_logs:                                                 │ │
│  │  - user_id, action_type: 'AT_CREATED'                                       │ │
│  │  - entity_type: 'authority_to_travel'                                       │ │
│  │  - entity_id: (new AT id)                                                   │ │
│  │  - ip_address, user_agent                                                   │ │
│  └─────────────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────┬─────────────────────────────────────┘
                                             │
                                             ▼
                              REQUEST NOW IN PENDING STATE
                                             │
       ┌─────────────────────────────────────┼─────────────────────────────────────┐
       │                                     │                                     │
       ▼                                     ▼                                     ▼
┌─────────────┐                     ┌─────────────┐                      ┌─────────────┐
│  CID CHIEF  │                     │ SGOD CHIEF  │                      │ OSDS CHIEF  │
│  Dashboard  │                     │  Dashboard  │                      │  Dashboard  │
└──────┬──────┘                     └──────┬──────┘                      └──────┬──────┘
       │                                   │                                    │
       │ Reviews request from CID units    │ Reviews SGOD units                 │ Reviews OSDS units
       │                                   │                                    │
       └───────────────────────────────────┴────────────────────────────────────┘
                                           │
                                           ▼
┌──────────────────────────────────────────────────────────────────────────────────┐
│                    UNIT HEAD RECOMMENDING APPROVAL PROCESS                        │
│  ┌─────────────────────────────────────────────────────────────────────────────┐ │
│  │                                                                             │ │
│  │     ┌──────────┐        ┌──────────┐        ┌──────────────────────┐       │ │
│  │     │ APPROVE  │        │  REJECT  │        │ (Optional) RETURN    │       │ │
│  │     │(Recommend)│       │          │        │    FOR REVISION      │       │ │
│  │     └────┬─────┘        └────┬─────┘        └──────────────────────┘       │ │
│  │          │                   │                                              │ │
│  │          ▼                   ▼                                              │ │
│  │   ┌─────────────────┐ ┌─────────────────┐                                  │ │
│  │   │ recommend()     │ │ reject()        │                                  │ │
│  │   │                 │ │                 │                                  │ │
│  │   │ UPDATE:         │ │ UPDATE:         │                                  │ │
│  │   │ - routing_stage │ │ - status =      │                                  │ │
│  │   │   = 'final'     │ │   'rejected'    │                                  │ │
│  │   │ - current_      │ │ - rejection_    │                                  │ │
│  │   │   approver_role │ │   reason        │                                  │ │
│  │   │   = 'ASDS'      │ │                 │                                  │ │
│  │   │ - recommending_ │ │                 │                                  │ │
│  │   │   authority_name│ │                 │                                  │ │
│  │   │ - recommending_ │ │                 │                                  │ │
│  │   │   date          │ │                 │                                  │ │
│  │   └────────┬────────┘ └────────┬────────┘                                  │ │
│  │            │                   │                                            │ │
│  │            │                   └──────▶ END (Request Rejected)              │ │
│  │            │                                                                │ │
│  │            ▼                                                                │ │
│  │     Log Activity                                                            │ │
│  │     'AT_RECOMMENDED'                                                        │ │
│  └─────────────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────┬─────────────────────────────────────┘
                                             │
                                             │ Request escalated to ASDS
                                             ▼
                                    ┌─────────────────┐
                                    │      ASDS       │
                                    │   Dashboard     │
                                    │                 │
                                    │  Views requests │
                                    │  in 'final'     │
                                    │  stage          │
                                    └────────┬────────┘
                                             │
                                             ▼
┌──────────────────────────────────────────────────────────────────────────────────┐
│                        ASDS FINAL APPROVAL PROCESS                                │
│  ┌─────────────────────────────────────────────────────────────────────────────┐ │
│  │                                                                             │ │
│  │     ┌──────────┐              ┌──────────┐                                  │ │
│  │     │ APPROVE  │              │  REJECT  │                                  │ │
│  │     │ (Final)  │              │          │                                  │ │
│  │     └────┬─────┘              └────┬─────┘                                  │ │
│  │          │                         │                                        │ │
│  │          ▼                         ▼                                        │ │
│  │   ┌─────────────────┐       ┌─────────────────┐                            │ │
│  │   │ approve()       │       │ reject()        │                            │ │
│  │   │                 │       │                 │                            │ │
│  │   │ UPDATE:         │       │ UPDATE:         │                            │ │
│  │   │ - status =      │       │ - status =      │                            │ │
│  │   │   'approved'    │       │   'rejected'    │                            │ │
│  │   │ - approved_by   │       │ - rejection_    │                            │ │
│  │   │ - approval_date │       │   reason        │                            │ │
│  │   │ - routing_stage │       │                 │                            │ │
│  │   │   = 'completed' │       │                 │                            │ │
│  │   └────────┬────────┘       └────────┬────────┘                            │ │
│  │            │                         │                                      │ │
│  │            │                         └──────▶ END (Request Rejected)        │ │
│  │            │                                                                │ │
│  │            ▼                                                                │ │
│  │     Log Activity                                                            │ │
│  │     'AT_APPROVED'                                                           │ │
│  └─────────────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────┬─────────────────────────────────────┘
                                             │
                                             │ Request Approved
                                             ▼
┌──────────────────────────────────────────────────────────────────────────────────┐
│                          DOCUMENT GENERATION FLOW                                 │
│  ┌─────────────────────────────────────────────────────────────────────────────┐ │
│  │                                                                             │ │
│  │  User clicks "Download Document"                                            │ │
│  │         │                                                                   │ │
│  │         ▼                                                                   │ │
│  │  ┌─────────────────────────────────────────────────────────────────────┐   │ │
│  │  │              DocxGenerator.php                                      │   │ │
│  │  │                                                                     │   │ │
│  │  │  Based on travel_scope:                                             │   │ │
│  │  │  - Local      → generateATLocal()                                   │   │ │
│  │  │  - National   → generateATNational()                                │   │ │
│  │  │  - Personal   → generateATPersonal()                                │   │ │
│  │  │                                                                     │   │ │
│  │  │  Process:                                                           │   │ │
│  │  │  1. Load template from reference-forms/doc-forms/                   │   │ │
│  │  │  2. PHPWord TemplateProcessor                                       │   │ │
│  │  │  3. Replace placeholders:                                           │   │ │
│  │  │     ${at_tracking_no}, ${employee_name}, ${travel_dates},           │   │ │
│  │  │     ${destination}, ${purpose}, ${recommending_authority_name},     │   │ │
│  │  │     ${approver_name}, ${approval_date}                              │   │ │
│  │  │  4. Save to uploads/generated/                                      │   │ │
│  │  │  5. Return file path for download                                   │   │ │
│  │  └─────────────────────────────────────────────────────────────────────┘   │ │
│  │                                                                             │ │
│  └─────────────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────┬─────────────────────────────────────┘
                                             │
                                             ▼
                                    ┌─────────────────┐
                                    │  Generated DOCX │
                                    │    Download     │
                                    └─────────────────┘
```

### 4.2 Locator Slip (LS) Request Flow

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                   LOCATOR SLIP - COMPLETE DATA FLOW DIAGRAM                      │
└─────────────────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   EMPLOYEE   │
│   (User)     │
└──────┬───────┘
       │
       │ 1. Fills Locator Slip Form
       │    - Purpose of Travel
       │    - Travel Type (Official Business / Official Time)
       │    - Date/Time
       │    - Destination
       ▼
┌──────────────────────────────────────────────────────────────────────────────────┐
│                            locator-slips.php                                      │
│  ┌─────────────────────────────────────────────────────────────────────────────┐ │
│  │ STEP 1: Validate Input                                                      │ │
│  │  - Check required fields                                                    │ │
│  │  - Validate same-day travel                                                 │ │
│  └─────────────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────┬─────────────────────────────────────┘
                                             │
                                             ▼
┌──────────────────────────────────────────────────────────────────────────────────┐
│                           TrackingService.php                                     │
│  ┌─────────────────────────────────────────────────────────────────────────────┐ │
│  │ STEP 2: Generate Control Number                                             │ │
│  │                                                                             │ │
│  │  generateLSNumber()                                                         │ │
│  │  - Atomic increment in tracking_sequences                                   │ │
│  │  - Format: LS-YYYY-NNNNNN                                                   │ │
│  │  - Example: LS-2026-000001                                                  │ │
│  └─────────────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────┬─────────────────────────────────────┘
                                             │
                                             ▼
┌──────────────────────────────────────────────────────────────────────────────────┐
│                            LocatorSlip.php (Model)                                │
│  ┌─────────────────────────────────────────────────────────────────────────────┐ │
│  │ STEP 3: Determine Approver (Simpler than AT)                                │ │
│  │                                                                             │ │
│  │  getApproverRoleForOffice(requesterOffice)                                  │ │
│  │         │                                                                   │ │
│  │         ├──▶ CID       → CID_CHIEF                                          │ │
│  │         ├──▶ SGOD      → SGOD_CHIEF                                         │ │
│  │         └──▶ OSDS Unit → OSDS_CHIEF                                         │ │
│  │                                                                             │ │
│  │  Note: LS uses SINGLE-STEP approval (no recommending stage)                 │ │
│  └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                   │
│  ┌─────────────────────────────────────────────────────────────────────────────┐ │
│  │ STEP 4: Check for Active OIC & Create Record                                │ │
│  │                                                                             │ │
│  │  INSERT INTO locator_slips:                                                 │ │
│  │  - ls_control_no                                                            │ │
│  │  - user_id                                                                  │ │
│  │  - purpose_of_travel, travel_type                                           │ │
│  │  - date_time, destination                                                   │ │
│  │  - status = 'pending'                                                       │ │
│  │  - assigned_approver_role_id                                                │ │
│  │  - assigned_approver_user_id (OIC or Unit Head)                             │ │
│  └─────────────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────┬─────────────────────────────────────┘
                                             │
                                             ▼
                              LS NOW IN PENDING STATE
                                             │
       ┌─────────────────────────────────────┴─────────────────────────────────────┐
       │                                                                           │
       │              Assigned Unit Chief (or ASDS/OSDS Chief)                     │
       │                         views pending LS                                   │
       │                                                                           │
       └─────────────────────────────────────┬─────────────────────────────────────┘
                                             │
                                             ▼
┌──────────────────────────────────────────────────────────────────────────────────┐
│                         LS APPROVAL PROCESS (SINGLE STEP)                         │
│                                                                                   │
│       ┌──────────────────┐                    ┌──────────────────┐               │
│       │     APPROVE      │                    │      REJECT      │               │
│       └────────┬─────────┘                    └────────┬─────────┘               │
│                │                                       │                         │
│                ▼                                       ▼                         │
│         ┌─────────────────┐                    ┌─────────────────┐               │
│         │ UPDATE:         │                    │ UPDATE:         │               │
│         │ - status =      │                    │ - status =      │               │
│         │   'approved'    │                    │   'rejected'    │               │
│         │ - approved_by   │                    │ - rejection_    │               │
│         │ - approval_date │                    │   reason        │               │
│         └────────┬────────┘                    └────────┬────────┘               │
│                  │                                      │                        │
│                  ▼                                      ▼                        │
│           Log Activity                          Log Activity                     │
│           'LS_APPROVED'                         'LS_REJECTED'                    │
│                  │                                      │                        │
│                  ▼                                      ▼                        │
│         Generate DOCX Available                   END (Rejected)                 │
│                  │                                                               │
└──────────────────┼───────────────────────────────────────────────────────────────┘
                   │
                   ▼
┌──────────────────────────────────────────────────────────────────────────────────┐
│                          DOCX GENERATION (on approval)                            │
│                                                                                   │
│  DocxGenerator::generateLocatorSlip($data)                                       │
│                                                                                   │
│  Placeholders replaced:                                                          │
│  - ${ls_control_no}                                                              │
│  - ${employee_name}, ${employee_position}, ${employee_office}                    │
│  - ${purpose_of_travel}                                                          │
│  - ${cb_ob} / ${cb_ot} (checkboxes for travel type)                              │
│  - ${date_time}, ${destination}                                                  │
│  - ${approver_name}, ${approver_position}, ${approval_date}                      │
└──────────────────────────────────────────────────────────────────────────────────┘
```

### 4.3 Authentication & Session Flow

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                     AUTHENTICATION & SESSION DATA FLOW                           │
└─────────────────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│    USER      │
│  (Browser)   │
└──────┬───────┘
       │
       │ 1. Access login.php
       │    Submit email + password
       │
       ▼
┌──────────────────────────────────────────────────────────────────────────────────┐
│                               login.php                                           │
│  ┌─────────────────────────────────────────────────────────────────────────────┐ │
│  │ Validate credentials                                                        │ │
│  └─────────────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────┬─────────────────────────────────────┘
                                             │
                                             ▼
┌──────────────────────────────────────────────────────────────────────────────────┐
│                         AdminUser.php::authenticate()                             │
│  ┌─────────────────────────────────────────────────────────────────────────────┐ │
│  │                                                                             │ │
│  │  SELECT * FROM admin_users                                                  │ │
│  │  WHERE email = ? AND status = 'active' AND is_active = 1                    │ │
│  │         │                                                                   │ │
│  │         ▼                                                                   │ │
│  │  password_verify($password, $user['password_hash'])                         │ │
│  │         │                                                                   │ │
│  │         ├──▶ VALID: Update last_login, return user data                     │ │
│  │         │                                                                   │ │
│  │         └──▶ INVALID: Return false                                          │ │
│  │                                                                             │ │
│  └─────────────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────┬─────────────────────────────────────┘
                                             │
                          Authentication Successful
                                             │
                                             ▼
┌──────────────────────────────────────────────────────────────────────────────────┐
│                        SessionToken.php::create()                                 │
│  ┌─────────────────────────────────────────────────────────────────────────────┐ │
│  │                                                                             │ │
│  │  1. Generate secure random token                                            │ │
│  │     $token = bin2hex(random_bytes(32))                                      │ │
│  │                                                                             │ │
│  │  2. Store in database                                                       │ │
│  │     INSERT INTO session_tokens (                                            │ │
│  │       user_id, token, expires_at, ip_address, user_agent                    │ │
│  │     )                                                                       │ │
│  │                                                                             │ │
│  │  3. Set cookie                                                              │ │
│  │     setcookie('atlas_token', $token, ...)                                   │ │
│  │                                                                             │ │
│  └─────────────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────┬─────────────────────────────────────┘
                                             │
                                             ▼
                                    Token returned to client
                                             │
                                             ▼
┌──────────────────────────────────────────────────────────────────────────────────┐
│                        SUBSEQUENT REQUEST AUTHENTICATION                          │
│                                                                                   │
│  ┌─────────────────────────────────────────────────────────────────────────────┐ │
│  │                    AdminAuth.php (Singleton)                                │ │
│  │                                                                             │ │
│  │  Token Sources (checked in order):                                          │ │
│  │  1. URL Parameter: ?token=xxx                                               │ │
│  │  2. POST Parameter: _token                                                  │ │
│  │  3. Authorization Header: Bearer xxx                                        │ │
│  │  4. Custom Header: X-Auth-Token                                             │ │
│  │  5. Cookie: atlas_token                                                     │ │
│  │                                                                             │ │
│  │         ▼                                                                   │ │
│  │  SessionToken::validate($token)                                             │ │
│  │         │                                                                   │ │
│  │         ├──▶ Token valid & not expired → Load user data                     │ │
│  │         │         │                                                         │ │
│  │         │         ▼                                                         │ │
│  │         │    Check OIC Delegation                                           │ │
│  │         │         │                                                         │ │
│  │         │         ├──▶ User is OIC → Set effective role                     │ │
│  │         │         │                                                         │ │
│  │         │         └──▶ Normal user → Use assigned role                      │ │
│  │         │                                                                   │ │
│  │         └──▶ Token invalid → Redirect to login                              │ │
│  │                                                                             │ │
│  └─────────────────────────────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────────────────────────────┘
```

---

## 5. Database Schema Overview

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          DATABASE ENTITY RELATIONSHIPS                           │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────┐         ┌─────────────────────────┐
│      admin_roles        │         │      master_offices     │
├─────────────────────────┤         ├─────────────────────────┤
│ id (PK)                 │         │ id (PK)                 │
│ role_name               │         │ office_code             │
│ permissions (JSON)      │         │ office_name             │
│ created_at              │         │ parent_id (FK)          │
└───────────┬─────────────┘         │ is_active               │
            │                       └───────────┬─────────────┘
            │ 1:N                               │ 1:N
            │                                   │
            ▼                                   ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                                admin_users                                       │
├─────────────────────────────────────────────────────────────────────────────────┤
│ id (PK)                 │ email (UNIQUE)        │ full_name                      │
│ password_hash           │ employee_no           │ employee_position              │
│ employee_office         │ office_id (FK)        │ role_id (FK → admin_roles)     │
│ status                  │ is_active             │ last_login                     │
│ created_by (FK → self)  │ created_at            │ updated_at                     │
└─────────────────────────────────────────────────────────────────────────────────┘
            │
            │ 1:N (user_id)
            │
            ├───────────────────────────────┬───────────────────────────────┐
            │                               │                               │
            ▼                               ▼                               ▼
┌─────────────────────────┐   ┌─────────────────────────┐   ┌─────────────────────────┐
│  authority_to_travel    │   │     locator_slips       │   │     session_tokens      │
├─────────────────────────┤   ├─────────────────────────┤   ├─────────────────────────┤
│ id (PK)                 │   │ id (PK)                 │   │ id (PK)                 │
│ at_tracking_no (UNIQUE) │   │ ls_control_no (UNIQUE)  │   │ user_id (FK)            │
│ user_id (FK)            │   │ user_id (FK)            │   │ token (UNIQUE)          │
│ travel_category         │   │ purpose_of_travel       │   │ expires_at              │
│ travel_scope            │   │ travel_type             │   │ ip_address              │
│ purpose_of_travel       │   │ date_time               │   │ user_agent              │
│ destination             │   │ destination             │   │ created_at              │
│ departure_date          │   │ status                  │   └─────────────────────────┘
│ return_date             │   │ approved_by (FK)        │
│ status                  │   │ approval_date           │
│ routing_stage           │   │ assigned_approver_*     │
│ current_approver_role   │   │ created_at              │
│ assigned_approver_*     │   └─────────────────────────┘
│ recommending_authority  │
│ recommending_date       │
│ approved_by (FK)        │
│ approval_date           │
│ rejection_reason        │
│ created_at              │
└─────────────────────────┘
            │
            ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              activity_logs                                       │
├─────────────────────────────────────────────────────────────────────────────────┤
│ id (PK)        │ user_id (FK)    │ action_type      │ entity_type               │
│ entity_id      │ description     │ old_value (JSON) │ new_value (JSON)          │
│ ip_address     │ user_agent      │ created_at                                   │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                             oic_delegations                                      │
├─────────────────────────────────────────────────────────────────────────────────┤
│ id (PK)              │ unit_head_user_id (FK)  │ unit_head_role_id (FK)         │
│ oic_user_id (FK)     │ start_date              │ end_date                       │
│ is_active            │ created_by (FK)         │ created_at                     │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                           unit_routing_config                                    │
├─────────────────────────────────────────────────────────────────────────────────┤
│ id (PK)              │ unit_name               │ office_id (FK)                 │
│ approver_role_id (FK)│ travel_scope            │ is_active                      │
│ created_at           │ updated_at              │                                │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                           tracking_sequences                                     │
├─────────────────────────────────────────────────────────────────────────────────┤
│ id (PK)              │ prefix (AT/LS)          │ year                           │
│ last_number          │                         │                                │
│ UNIQUE(prefix, year) │                         │                                │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 6. Security Architecture

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           SECURITY ARCHITECTURE                                  │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                          AUTHENTICATION LAYER                                    │
│                                                                                  │
│  ┌────────────────────────────────────────────────────────────────────────────┐ │
│  │ Token-Based Session Management                                             │ │
│  │                                                                            │ │
│  │  • Secure random token generation (64 hex chars)                           │ │
│  │  • Token stored in database with expiration                                │ │
│  │  • 8-hour session lifetime (configurable)                                  │ │
│  │  • Multiple token sources supported (URL, Header, Cookie)                  │ │
│  │  • IP address and User-Agent tracking                                      │ │
│  └────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                  │
│  ┌────────────────────────────────────────────────────────────────────────────┐ │
│  │ Password Security                                                          │ │
│  │                                                                            │ │
│  │  • bcrypt hashing (password_hash/password_verify)                          │ │
│  │  • Automatic rehashing on algorithm updates                                │ │
│  │  • Password complexity enforcement                                         │ │
│  └────────────────────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                          AUTHORIZATION LAYER                                     │
│                                                                                  │
│  ┌────────────────────────────────────────────────────────────────────────────┐ │
│  │ Role-Based Access Control (RBAC)                                           │ │
│  │                                                                            │ │
│  │  AdminAuth Methods:                                                        │ │
│  │  • isSuperadmin()  - Check for SDS role                                    │ │
│  │  • isASDS()        - Check for ASDS role                                   │ │
│  │  • isUnitHead()    - Check for CID/SGOD/OSDS Chief                         │ │
│  │  • isEmployee()    - Check for regular user                                │ │
│  │  • canApprove($request) - Verify approval authority                        │ │
│  │                                                                            │ │
│  │  Page-Level Protection:                                                    │ │
│  │  • requireAdmin()  - Require any authenticated user                        │ │
│  │  • requireRole($roles) - Require specific role(s)                          │ │
│  │                                                                            │ │
│  └────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                  │
│  ┌────────────────────────────────────────────────────────────────────────────┐ │
│  │ OIC Delegation Security                                                    │ │
│  │                                                                            │ │
│  │  • Effective role calculation for delegated users                          │ │
│  │  • Time-bound delegations (start_date to end_date)                         │ │
│  │  • Single active OIC per unit constraint                                   │ │
│  │  • Audit trail for delegation changes                                      │ │
│  └────────────────────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                          DATA PROTECTION LAYER                                   │
│                                                                                  │
│  ┌────────────────────────────────────────────────────────────────────────────┐ │
│  │ SQL Injection Prevention                                                   │ │
│  │                                                                            │ │
│  │  • PDO with prepared statements                                            │ │
│  │  • Parameterized queries throughout                                        │ │
│  │  • No direct SQL string concatenation                                      │ │
│  └────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                  │
│  ┌────────────────────────────────────────────────────────────────────────────┐ │
│  │ XSS Prevention                                                             │ │
│  │                                                                            │ │
│  │  • htmlspecialchars() on all output                                        │ │
│  │  • Content-Type headers set appropriately                                  │ │
│  │  • Laminas Escaper library available                                       │ │
│  └────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                  │
│  ┌────────────────────────────────────────────────────────────────────────────┐ │
│  │ Audit Trail                                                                │ │
│  │                                                                            │ │
│  │  • Complete activity logging (ActivityLog model)                           │ │
│  │  • Action types: LOGIN, LOGOUT, CREATE, UPDATE, APPROVE, REJECT, etc.      │ │
│  │  • Entity tracking: user, authority_to_travel, locator_slip                │ │
│  │  • Old/New value storage for data changes                                  │ │
│  │  • IP address and browser fingerprinting                                   │ │
│  └────────────────────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 7. Request Processing Flows

### 7.1 Complete Request Lifecycle Summary

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                     COMPLETE REQUEST LIFECYCLE SUMMARY                           │
└─────────────────────────────────────────────────────────────────────────────────┘

    AUTHORITY TO TRAVEL (AT)                     LOCATOR SLIP (LS)
    ========================                     =================

    ┌─────────────────────┐                      ┌─────────────────────┐
    │  Employee Files AT  │                      │  Employee Files LS  │
    └──────────┬──────────┘                      └──────────┬──────────┘
               │                                            │
               ▼                                            ▼
    ┌─────────────────────┐                      ┌─────────────────────┐
    │ Generate AT-YYYY-## │                      │ Generate LS-YYYY-## │
    └──────────┬──────────┘                      └──────────┬──────────┘
               │                                            │
               ▼                                            │
    ┌─────────────────────┐                                 │
    │ Determine Routing   │                                 │
    │                     │                                 │
    │ Unit Head?          │                                 │
    │ ├─ YES → ASDS       │                                 │
    │ └─ NO → Unit Chief  │                                 │
    └──────────┬──────────┘                                 │
               │                                            │
               ▼                                            ▼
    ┌─────────────────────┐                      ┌─────────────────────┐
    │ STATUS: pending     │                      │ STATUS: pending     │
    │ STAGE: recommending │                      │ (No stages)         │
    └──────────┬──────────┘                      └──────────┬──────────┘
               │                                            │
               ▼                                            │
    ┌─────────────────────┐                                 │
    │ Unit Chief Reviews  │                                 │
    │                     │                                 │
    │ ├─ RECOMMEND        │                                 │
    │ │   → STAGE: final  │                                 │
    │ │   → APPROVER: ASDS│                                 │
    │ │                   │                                 │
    │ └─ REJECT           │                                 │
    │     → STATUS:rejected│                                │
    └──────────┬──────────┘                                 │
               │                                            │
               ▼                                            ▼
    ┌─────────────────────┐                      ┌─────────────────────┐
    │  ASDS/SDS Reviews   │                      │ Unit Chief Reviews  │
    │                     │                      │ (or ASDS/OSDS Chief)│
    │ ├─ APPROVE          │                      │                     │
    │ │   → STATUS:approved│                     │ ├─ APPROVE          │
    │ │   → STAGE:completed│                     │ │   → STATUS:approved│
    │ │                   │                      │ │                   │
    │ └─ REJECT           │                      │ └─ REJECT           │
    │     → STATUS:rejected│                     │     → STATUS:rejected│
    └──────────┬──────────┘                      └──────────┬──────────┘
               │                                            │
               ▼                                            ▼
    ┌─────────────────────┐                      ┌─────────────────────┐
    │ DOCX Generation     │                      │ DOCX Generation     │
    │                     │                      │                     │
    │ Based on scope:     │                      │ Single template:    │
    │ - AT Local          │                      │ - Locator Slip      │
    │ - AT National       │                      │                     │
    │ - AT Personal       │                      │                     │
    └─────────────────────┘                      └─────────────────────┘
```

### 7.2 API Endpoints Overview

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              API ENDPOINTS                                       │
└─────────────────────────────────────────────────────────────────────────────────┘

    ENDPOINT                              METHOD    DESCRIPTION
    ────────────────────────────────────────────────────────────────────────────
    
    AUTHENTICATION
    /admin/login.php                      GET/POST  Login page & authentication
    /admin/logout.php                     GET       Logout & token revocation
    /admin/register.php                   GET/POST  User registration (admin)
    
    DASHBOARD
    /admin/index.php                      GET       Role-specific dashboard
    /admin/profile.php                    GET/POST  User profile management
    
    AUTHORITY TO TRAVEL
    /admin/authority-to-travel.php        GET       List AT requests
    /admin/authority-to-travel.php?action=new        GET/POST  Create AT
    /admin/authority-to-travel.php?action=view&id=X  GET       View AT details
    /admin/authority-to-travel.php?action=approve    POST      Approve/Recommend
    /admin/authority-to-travel.php?action=reject     POST      Reject AT
    
    LOCATOR SLIPS
    /admin/locator-slips.php              GET       List LS requests
    /admin/locator-slips.php?action=new   GET/POST  Create LS
    /admin/locator-slips.php?action=view&id=X   GET  View LS details
    /admin/locator-slips.php?action=approve     POST Approve LS
    /admin/locator-slips.php?action=reject      POST Reject LS
    
    MY REQUESTS
    /admin/my-requests.php                GET       User's own requests
    /admin/my-requests.php?type=ls        GET       User's LS requests
    /admin/my-requests.php?type=at        GET       User's AT requests
    
    DOCUMENT GENERATION (AJAX)
    /admin/api/generate-docx.php          POST      Generate DOCX document
    /admin/api/notification-count.php     GET       Get pending notifications
    
    ADMINISTRATION
    /admin/users.php                      GET       User management
    /admin/oic-management.php             GET/POST  OIC delegation
    /admin/unit-routing.php               GET/POST  Unit routing config
    /admin/logs.php                       GET       Activity logs viewer
```

---

## Appendix: Directory Structure

```
SDO-atlas/
├── admin/                          # Admin panel pages
│   ├── api/                        # AJAX endpoints
│   │   ├── generate-docx.php
│   │   └── notification-count.php
│   ├── assets/                     # Frontend assets
│   │   ├── css/admin.css
│   │   ├── js/admin.js
│   │   └── logos/
│   ├── database/                   # Database dumps
│   ├── index.php                   # Dashboard
│   ├── login.php                   # Authentication
│   ├── authority-to-travel.php     # AT management
│   ├── locator-slips.php           # LS management
│   ├── my-requests.php             # User requests
│   ├── users.php                   # User management
│   ├── oic-management.php          # OIC delegation
│   ├── unit-routing.php            # Routing config
│   ├── logs.php                    # Activity logs
│   └── profile.php                 # User profile
├── config/                         # Configuration
│   ├── admin_config.php            # App settings, roles
│   ├── database.php                # DB connection
│   └── mail_config.php             # Email settings
├── includes/                       # Shared includes
│   ├── auth.php                    # AdminAuth class
│   ├── header.php                  # Page header
│   └── footer.php                  # Page footer
├── models/                         # Data models
│   ├── ActivityLog.php
│   ├── AdminUser.php
│   ├── AuthorityToTravel.php
│   ├── LocatorSlip.php
│   ├── OICDelegation.php
│   └── SessionToken.php
├── services/                       # Business services
│   ├── DocxGenerator.php           # Document generation
│   └── TrackingService.php         # Tracking numbers
├── reference-forms/                # Document templates
│   ├── doc-forms/                  # DOCX templates
│   └── doc-imgs/                   # Template images
├── uploads/                        # File uploads
│   └── generated/                  # Generated documents
├── vendor/                         # Composer packages
│   ├── phpoffice/phpword/          # Document generation
│   └── laminas/laminas-escaper/    # Security
├── sql/                            # SQL migrations
├── composer.json                   # Dependencies
└── README.md                       # Documentation
```

---

**Document Version:** 1.0  
**Last Updated:** January 29, 2026  
**System:** SDO ATLAS - Schools Division Office Authority to Travel and Locator Approval System
