# SDO ALPAS – Offices, Units, and Chiefs (System Architecture)

This document describes the **organizational hierarchy**, **Office Chiefs**, and **request routing** so you can clarify and correct routing (especially for **unit personnel**).

---

## 1. Top-Level Structure

The SDO has **three top-level offices** (divisions). Each has one **Office Chief**. Units under an office report to that office’s chief.

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                    SDO – TOP-LEVEL OFFICES (3 DIVISIONS)                        │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│   ┌─────────────────────┐   ┌─────────────────────┐  ┌────────────────────┐     │
│   │        OSDS         │   │        SGOD         │  │        CID         │     │
│   │  (Office of the     │   │  School Governance  │  │  Curriculum        │     │
│   │   SDS Staff)        │   │  and Operations     │  │  Implementation    │     │
│   │                     │   │  Division           │  │  Division          │     │
│   │  Chief: AO V        │   │   Chief: SGOD Chief │  │  Chief: CID Chief  │     │
│   │  Role: OSDS_CHIEF(3)│   └─────────┬──────────-┘  └──────────┬─────────┘     │
│              │                        │                        │                │
│              ▼                        ▼                        ▼                │
│        [Units below]            [Units below]            [Units below]          │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Offices, Units, and Chiefs (Detail)

### 2.1 OSDS – Office of the Schools Division Superintendent Staff

| Role ID | Role Name   | Display Title |     Supervises       |
|---------|-------------|---------------|----------------------|
| **3**   | OSDS_CHIEF  | AO V          | All OSDS units below |

**Units under OSDS** (all route to **OSDS Chief / AO V**):

| Unit Code / Name        | Full name (if different)                |
|-------------------------|-----------------------------------------|
| OSDS                    | Office of the SDS Staff                 |
| Personnel               | Personnel                               |
| Property and Supply     | Property and Supply                     |
| Records                 | Records                                 |
| Cash                    | Cash                                    |
| Procurement             | Procurement                             |
| General Services        | General Services                        |
| Legal                   | Legal                                   |
| ICT                     | Information and Communication Technology|
| Accounting              | Finance (Accounting)                    |
| Budget                  | Finance (Budget)                        |
| Administrative          | Administrative                          |

---

### 2.2 SGOD – School Governance and Operations Division

| Role ID | Role Name   | Display Title |      Supervises      |
|--------|--------------|---------------|----------------------|
| **5**  | SGOD_CHIEF   | SGOD Chief    | All SGOD units below |

**Units under SGOD** (all route to **SGOD Chief**):

| Unit Code |                    Full Name                |
|-----------|---------------------------------------------|
| SGOD      | School Governance and Operations Division   |
| SMME      | School Management Monitoring and Evaluation |
| HRD       | Human Resource Development                  |
| SMN       | Social Mobilization and Networking          |
| PR        | Planning and Research                       | 
| DRRM      | Disaster Risk Reduction and Management      |
| EF        | Education Facilities                        |
| SHN_DENTAL| School Health and Nutrition (Dental)        |
| SHN_MEDICAL| School Health and Nutrition (Medical)      |
| SHN       | School Health and Nutrition                 |

---

### 2.3 CID – Curriculum Implementation Division

| Role ID | Role Name    | Display Title |     Supervises      | 
|---------|--------------|---------------|---------------------|  
| **4**  -| CID_CHIEF    | CID Chief     | All CID units below |

**Units under CID** (all route to **CID Chief**):

| Unit Code |              Full Name             |
|-----------|------------------------------------|
| CID       | Curriculum Implementation Division |
| IM        | Instructional Management           |
| LRM       | Learning Resource Management       |
| ALS       | Alternative Learning System        |
| DIS       | District Instructional Supervision |

---

## 3. Roles Summary (All Chiefs and Approvers)

| Role ID | Constant        | Role Name   | Who they are           | Approves / Recommends |
|--------|-----------------|-------------|------------------------|------------------------|
| 1      | ROLE_SUPERADMIN | Superadmin  | SDS (override)         | All (override)         |
| 2      | ROLE_ASDS       | ASDS        | Assistant SDS          | AT final; LS when requestor is Office Chief    |
| 3      | ROLE_OSDS_CHIEF | OSDS_CHIEF  | AO V – OSDS Office Chief | OSDS units           |
| 4      | ROLE_CID_CHIEF  | CID_CHIEF   | CID Office Chief       | CID units              |
| 5      | ROLE_SGOD_CHIEF | SGOD_CHIEF  | SGOD Office Chief      | SGOD units             |
| 6      | ROLE_USER       | User        | Regular employee       | Own requests only      | 

**Office Chief** = any of: **OSDS_CHIEF (3), CID_CHIEF (4), SGOD_CHIEF (5)**.  
**UNIT_HEAD_ROLES** in code = `[3, 4, 5]`.

---

## 4. Request Routing (How It Should Work)

### 4.1 Authority to Travel (AT)

- **Unit personnel** (role USER, in a unit under OSDS/SGOD/CID):  
  - Route to their **Office Chief** (recommending).  
  - Then to **ASDS** for final approval.  
- **Office Chief** as requestor:  
  - Skips recommending; goes **directly to ASDS** for final approval.  
- Routing uses: **office_id** (preferred), **unit_routing_config**, then **ROLE_OFFICE_MAP** (e.g. IM → CID_CHIEF, SMME → SGOD_CHIEF).

### 4.2 Locator Slip (LS)

- **Unit personnel** (role USER, in a unit under an office):  
  - Route **only to their Office Chief**.  
  - **Office Chief is the sole approver**; request is **not** forwarded to ASDS.  
  - Only that Office Chief (and requestor, Superadmin) can view it.  
- **Office Chief** as requestor:  
  - Route **to ASDS** only.  
  - ASDS is the sole approver.  

Routing for LS is determined by **requester role** and **requester office** (or unit):

- If `requester_role_id` is in **UNIT_HEAD_ROLES** → assign to **ASDS**.  
- Else → assign to the **Office Chief** for that office/unit (using office/unit → chief mapping).

---

## 5. Unit Personnel Routing – What Can Go Wrong

Routing for **unit personnel** depends on how **office/unit** is identified when creating a request.

### 5.1 Data used for routing

- **Authority to Travel**  
  - Uses **requester_office_id** (from `sdo_offices.id`) when available, else **requester_office** (text).  
  - Resolves chief via **unit_routing_config** (by `office_id` or unit name) and **ROLE_OFFICE_MAP** (e.g. `'IM' => CID_CHIEF`, `'SMME' => SGOD_CHIEF`).  

- **Locator Slip**  
  - Uses **requesterOffice** only (text): `$currentUser['employee_office'] ?? $_POST['employee_office']`.  
  - No **office_id** is passed to the LocatorSlip model.  
  - Chief is resolved by **getApproverRoleForOffice(office)**.

### 5.2 LocatorSlip chief resolution (current logic)

In **LocatorSlip.php**, `getApproverRoleForOffice($office)` currently does:

1. `office === 'CID'` → CID_CHIEF  
2. `office === 'SGOD'` → SGOD_CHIEF  
3. `office` in **OSDS_UNITS** → OSDS_CHIEF  
4. Otherwise → **default OSDS_CHIEF**

So:

- If **employee_office** (or form office) is a **unit code/name** (e.g. **IM, LRM, ALS, DIS, SMME, HRD, PR**, etc.), it does **not** match `'CID'` or `'SGOD'` and may not be in **OSDS_UNITS**.  
- Then it falls to **default → OSDS_CHIEF**.  
- Result: **CID and SGOD unit personnel** can be **misrouted to OSDS Chief** instead of their own Office Chief (CID Chief or SGOD Chief).

To align with AT and with the intended architecture, Locator Slip routing for unit personnel should use the **same** office/unit → chief mapping as AT (e.g. **ROLE_OFFICE_MAP** or **unit_routing_config**), so that:

- IM, LRM, ALS, DIS → **CID Chief**  
- SMME, HRD, SMN, PR, DRRM, EF, SHN_* → **SGOD Chief**  
- OSDS units → **OSDS Chief**

---

## 6. Config Reference (Where It’s Defined)

| What                | Where |
|---------------------|--------|
| Role IDs            | `config/admin_config.php` – ROLE_ASDS, ROLE_OSDS_CHIEF, ROLE_CID_CHIEF, ROLE_SGOD_CHIEF, ROLE_USER |
| UNIT_HEAD_ROLES     | `config/admin_config.php` – [3, 4, 5] |
| OSDS_UNITS (list)   | `config/admin_config.php` – OSDS, Personnel, Property and Supply, Records, Cash, Procurement, General Services, Legal, ICT, Accounting, Budget |
| Office/unit → Chief | `config/admin_config.php` – ROLE_OFFICE_MAP (unit code → role id) |
| Chief → Offices     | `config/admin_config.php` – UNIT_HEAD_OFFICES (role id → list of office/unit codes) |
| DB offices          | Table `sdo_offices` (id, office_code, office_name, parent_office_id, approver_role_id, …) |
| DB routing          | Table `unit_routing_config` (unit_name, office_id, approver_role_id, …) |
| User office         | `admin_users.employee_office` (text), `admin_users.office_id` (FK to sdo_offices) |

---

## 7. Quick Diagram – Who Approves What (Locator Slip)

```
                    LOCATOR SLIP REQUESTOR
                              │
              ┌───────────────┴───────────────┐
              │                               │
     Unit personnel                    Office Chief
     (USER, in a unit                  (OSDS/CID/SGOD Chief)
      under an office)
              │                               │
              ▼                               ▼
     Route to OFFICE CHIEF             Route to ASDS
     of that office only               only
     (CID/SGOD/OSDS Chief)             (ASDS)
              │                               │
              ▼                               ▼
     Only that Office Chief            Only ASDS
     can view/approve                  can view/approve
     (NOT forwarded to ASDS)           (sole approver)
```

---

Use this architecture to:

1. Confirm that your **real** offices/units match the lists in §2 and §6.  
2. Confirm that **unit personnel** under CID and SGOD should always route to **CID Chief** and **SGOD Chief** respectively, not to OSDS Chief.  
3. Locator Slip now uses **office_id** when available and display-name/alias resolution in `getApproverRoleForOffice()`.

If you tell me which office/unit names or codes you use in the system (and how they’re stored in `employee_office` / `sdo_offices`), I can suggest exact code changes so unit personnel routing matches this architecture.

---

## 8. Dentist / SHN routing anomaly (fixed)

**Symptom:** Locator Slips from Dentist II (e.g. under School Health and Nutrition – Dental) were appearing for **OSDS Chief** instead of **SGOD Chief**.

**Cause:** Routing used `employee_office` (text). Only **codes** like `SHN_DENTAL` matched in `ROLE_OFFICE_MAP`. Display names, aliases ("Dental", "SHN"), or empty office defaulted to **OSDS Chief**.

**Fixes applied:** (1) When the requestor has `office_id` set (from Users), routing uses `sdo_offices.approver_role_id`. (2) `getApproverRoleForOffice()` now matches exact display names from `SDO_OFFICES` and treats aliases "dental", "medical", "shn" as SGOD Chief.

**What you should do:** In **Users**, set the dentist's **Office/Division** so their **office_id** points to the SHN (Dental) office. New Locator Slips will then route to SGOD Chief.
