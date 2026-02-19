# SDO ALPAS - Users Categorization Table

**Registered users (active, pending, and inactive) categorized by Name, Office, Unit, and Email.**

---

## Table of Contents

1. [Overview](#1-overview)
2. [Categorization Structure](#2-categorization-structure)
3. [Data Source & Model](#3-data-source--model)
4. [Users Categorization Table](#4-users-categorization-table)
5. [By Office (Division)](#5-by-office-division)
6. [By Status](#6-by-status)

---

## 1. Overview

This document provides a **reference table of all registered users** in SDO ALPAS, regardless of account status. Users are categorized by:

| Category | Description |
|----------|-------------|
| **Name** | Full name of the registered user (`admin_users.full_name`) |
| **Office** | Top-level division: OSDS, SGOD, or CID |
| **Unit** | Specific unit/section under that division (e.g. Personnel, Records, IM, HRD) |
| **Email** | User's login email (`admin_users.email`) |

Status (active / pending / inactive) is included for reference. **Live data** is maintained in the admin panel; this document reflects structure and a snapshot for architecture/reference.

---

## 2. Categorization Structure

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                     USERS CATEGORIZATION STRUCTURE                                │
└─────────────────────────────────────────────────────────────────────────────────┘

    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                          REGISTERED USERS (admin_users)                       │
    │                                                                              │
    │   All users who have completed registration (self-register or admin-created)│
    │   Status: active | pending | inactive                                         │
    └─────────────────────────────────────────────────────────────────────────────┘
                                        │
                    ┌───────────────────┼───────────────────┐
                    │                   │                   │
                    ▼                   ▼                   ▼
            ┌───────────────┐   ┌───────────────┐   ┌───────────────┐
            │     NAME      │   │    OFFICE     │   │     UNIT      │
            │               │   │  (Division)   │   │  (Section)    │
            │ full_name     │   │               │   │               │
            │               │   │ OSDS          │   │ From sdo_     │
            │               │   │ SGOD          │   │ offices or    │
            │               │   │ CID           │   │ employee_     │
            │               │   │ (from         │   │ office        │
            │               │   │ office_id)    │   │               │
            └───────────────┘   └───────────────┘   └───────────────┘
                    │                   │                   │
                    └───────────────────┼───────────────────┘
                                        │
                                        ▼
                            ┌───────────────────────┐
                            │        EMAIL          │
                            │  admin_users.email    │
                            │  (unique, login id)   │
                            └───────────────────────┘
```

---

## 3. Data Source & Model

| Source | Field / Logic |
|--------|----------------|
| **Name** | `admin_users.full_name` |
| **Office** | Derived from `admin_users.office_id` → `sdo_offices` → parent or `is_osds_unit` → **OSDS** / **SGOD** / **CID** |
| **Unit** | `sdo_offices.office_name` where `sdo_offices.id = admin_users.office_id`, or fallback `admin_users.employee_office` |
| **Email** | `admin_users.email` |
| **Status** | `admin_users.status` (`active` \| `pending` \| `inactive`) |

Office (division) mapping:

- **OSDS** — Office of the Schools Division Superintendent Staff (all OSDS units: Personnel, Records, Cash, ICT, Legal, etc.)
- **SGOD** — School Governance and Operations Division (SMME, HRD, SMN, PR, DRRM, EF, SHN Dental/Medical)
- **CID** — Curriculum Implementation Division (IM, LRM, ALS, DIS)

---

## 4. Users Categorization Table

All registered users, categorized by **Name**, **Office**, **Unit**, and **Email**. Status included.

| # | Name | Office | Unit | Email | Status |
|---|------|--------|------|-------|--------|
| 1 | Alexander Joerenz Escallente | — | SDS | joerenz.dev@gmail.com | active |
| 2 | Joe-Bren L. Consuelo | OSDS | OSDS | jb@deped.com | active |
| 3 | Redgine Pinedes | CID | CID | teacher@deped.com | active |
| 4 | Paul Jeremy I. Aguja | OSDS | OSDS | aov@deped.com | active |
| 5 | Erma S. Valenzuela | CID | CID | cidchief@deped.com | active |
| 6 | Frederick G. Byrd Jr. | SGOD | SGOD | sgdochief@deped.com | active |
| 7 | Alexander Joerenz Escallente | OSDS | OSDS | ito@deped.com | active |
| 8 | Algen Loveres | SGOD | SGOD | pdo@deped.com | active |
| 9 | Cedrick Bacaresas | OSDS | Records | acct@deped.com | active |
| 10 | John Daniel P. Tec | CID | CID | psds@deped.com | active |
| 11 | Eljohn S. Beleta | OSDS | OSDS | ictclerk1@deped.com | active |
| 12 | Gizelle Cabrejas | OSDS | Legal | lglasst@deped.com | active |
| 13 | Abigail A. Olivenza | OSDS | Personnel | abigailaaiii@deped.com | active |
| 14 | Edwin Joseph B. De Peralta | OSDS | Records | edwinaavi@deped.com | pending |
| 15 | Kristine Kate R. Aguilar | OSDS | Cash | kristineaavi@deped.com | pending |
| 16 | Arcel F. Sopena | OSDS | Procurement | arcelaoii@deped.com | pending |
| 17 | Mariano L. Abiad | OSDS | General Services | marianoaoii@deped.com | pending |
| 18 | Jessa M. Fiedalan | OSDS | Accounting | jessaaaii@deped.com | pending |
| 19 | Baby Hazel Ann D. Perfas | OSDS | Budget | babyaaiii@deped.com | pending |
| 20 | Christoper John C. Tabrilla | OSDS | Property and Supply | christoperaavi@deped.com | pending |
| 21 | Michael Angelo S. Moresca | OSDS | Personnel | michaelaoiii@deped.com | active |
| 22 | Christian James C. Remoquillo | OSDS | Property and Supply | christianlsb@deped.com | active |
| 23 | Micah Joy Alao | OSDS | Procurement | micahlsb@deped.com | active |
| 24 | Larry Bonoan | OSDS | Cash | larrylsb@deped.com | active |
| 25 | Jun Jun Marinas | OSDS | Procurement | junlsb@deped.com | active |
| 26 | Jaypee Q. Jasa | OSDS | General Services | jaypeeaaii@deped.com | active |
| 27 | Arlene Angeles | OSDS | Accounting | arleneaavi@deped.com | active |
| 28 | Marvin Austria | OSDS | Budget | marvinlsb@deped.com | active |
| 29 | Shiela Mae Laude | OSDS | Legal | shielaatty@deped.com | active |
| 30 | Rogelio Sapitula Jr. | OSDS | ICT | jrlasb@deped.com | active |
| 31 | Laurence Parto | SGOD | SMME | laurenceseps@deped.com | active |
| 32 | Orimar Duab-dagandan | SGOD | HRD | orimarseps@deped.com | active |
| 33 | Mary Rose Aguilar | SGOD | SMN | maryseps@deped.com | active |
| 34 | Jenina Ambayec | SGOD | SHN (Dental) | jeninadentist@deped.com | active |
| 35 | Princess Leanna | SGOD | SMME | cesslsb@deped.com | pending |
| 36 | Shiela Manalo | SGOD | HRD | shielalsb@deped.com | pending |
| 37 | Jennesh Larena | SGOD | SMN | jennesheps@deped.com | pending |
| 38 | Ana Marie Mercado | SGOD | PR | analsb@deped.com | pending |
| 39 | Jaimee Lee Aseoche | SGOD | SHN (Medical) | leenurse@deped.com | pending |
| 40 | Marites Martinez | CID | IM | maritesepsm@deped.com | pending |
| 41 | Carl Alora | CID | LRM | carllibrarian@deped.com | pending |
| 42 | April Manlangit-Banaag | CID | ALS | aprilepsii@deped.com | pending |
| 43 | Emelinda Amil | CID | DIS | emelpsds@deped.com | active |
| 44 | Ernesto Caberte | CID | IM | ernestoepsf@deped.com | active |
| 45 | Brianne Basilan | CID | LRM | brilsb@deped.com | active |
| 46 | Rowena June Mirondo | CID | ALS | juneepsals@deped.com | active |
| 47 | Shirley Britos | CID | DIS | shirleypsds@deped.com | active |

---

## 5. By Office (Division)

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                    USERS GROUPED BY OFFICE (DIVISION)                            │
└─────────────────────────────────────────────────────────────────────────────────┘

  OSDS (Office of the Schools Division Superintendent Staff)
  ├── Units: Personnel, Records, Cash, Procurement, General Services, Legal,
  │          ICT, Accounting, Budget, Property and Supply, OSDS
  └── Users: All staff in the table above with Office = "OSDS"

  SGOD (School Governance and Operations Division)
  ├── Units: SGOD, SMME, HRD, SMN, PR, DRRM, EF, SHN Dental, SHN Medical
  └── Users: All staff with Office = "SGOD"

  CID (Curriculum Implementation Division)
  ├── Units: CID, IM, LRM, ALS, DIS
  └── Users: All staff with Office = "CID"
```

---

## 6. By Status

| Status | Description |
|--------|-------------|
| **active** | Account approved; user can sign in and use the system. |
| **pending** | Registered but awaiting approval by an admin. |
| **inactive** | Deactivated; user cannot sign in until reactivated. |

Counts (from snapshot above): **Active** — 34 | **Pending** — 13.

---

**Document:** Users Categorization Table  
**Purpose:** Reference of all registered users categorized by Name, Office, Unit, and Email.  
**Live data:** Admin → Users (`admin/users.php`).  
**Last updated:** February 2026.
