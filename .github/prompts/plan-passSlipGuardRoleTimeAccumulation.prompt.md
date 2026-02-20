# Plan: Pass Slip 3hr Limit + Guard Role + Time Accumulation

**TL;DR:** Three interconnected features: (1) enforce a 3-hour max per pass slip with auto-calculation of IAT from IDT, (2) add a new Guard role (ROLE_GUARD, ID 8) that uses the existing pass-slips.php page with guard-specific UI — "Departed" and "Arrived" buttons that auto-record server time, and (3) track accumulated actual hours across all pass slips per employee, showing a VL deduction note when 8 hours is reached. The VL system itself is out of scope — only flagging/notification is needed.

**Steps**

## A. Database Changes (new migration file)

1. Add a new row in `admin_roles` table: `id=8, name='GUARD', description='Guard on Duty - Pass Slip monitoring'`
2. Add a new column `accumulated_hours_note` (TEXT, nullable) to `pass_slips` to store per-slip notes when 8hr threshold is crossed (optional — can also be computed on-the-fly)
3. Add new columns to `pass_slips`:
   - `guard_departed_by` INT nullable FK → `admin_users(id)` — which guard clicked "Departed"
   - `guard_arrived_by` INT nullable FK → `admin_users(id)` — which guard clicked "Arrived"
   - `departed_at` TIMESTAMP nullable — exact timestamp of departure button click
   - `arrived_at` TIMESTAMP nullable — exact timestamp of arrival button click
   - `excess_hours` DECIMAL(5,2) nullable — computed excess beyond IAT (if late)
   - `vl_deduction_note` VARCHAR(255) nullable — note like "Accumulated 8hrs — 1 day VL deduction"

## B. Config Changes — config/admin_config.php

4. Add `ROLE_GUARD = 8` constant after the existing role constants (~L31)
5. Add `isGuard($roleId)` helper function (similar to `isEmployee()`)
6. Ensure guard role is NOT in `UNIT_HEAD_ROLES`, `OFFICE_CHIEF_ROLES`, or any approver maps

## C. Auth Changes — includes/auth.php

7. Add `isGuard()` method to `AdminAuth` class — returns `true` when `role_id == ROLE_GUARD`
8. Update `isEmployee()` to explicitly exclude ROLE_GUARD (so guards don't see employee dashboard features like "File New Request")

## D. Pass Slip Model Changes — models/PassSlip.php

9. **Update `validateSubmission()`** (~L412): Add validation that IAT - IDT must be > 0 and ≤ 3 hours. Return error message if exceeded.
10. **Update `updateGuardTimes()`** (~L133): Refactor to two separate methods:
    - `recordDeparture($id, $guardUserId)` — sets `actual_departure_time = NOW()`, `departed_at = NOW()`, `guard_departed_by = $guardUserId`. Only works if status = 'approved' and `actual_departure_time` is NULL.
    - `recordArrival($id, $guardUserId)` — sets `actual_arrival_time = NOW()`, `arrived_at = NOW()`, `guard_arrived_by = $guardUserId`. Only works if `actual_departure_time` is set and `actual_arrival_time` is NULL.
11. **Add `getAccumulatedHours($userId)`** — query that sums the actual time away (difference between `actual_arrival_time` and `actual_departure_time`) across ALL approved pass slips for a given employee where both actual times are recorded. Returns total hours as a decimal.
12. **Add `checkAndFlagVLDeduction($userId, $passSlipId)`** — after recording arrival, call `getAccumulatedHours()`. If total ≥ 8 (or crosses an 8hr multiple), write a `vl_deduction_note` on the pass slip and return the note for display.
13. **Update `getAll()` visibility** (~L174): Add a branch for ROLE_GUARD — guards can see ALL approved pass slips (any employee, any date), plus pending slips (read-only, for awareness).
14. **Add `getForGuardDashboard($filters)`** — returns pass slips sorted by date descending with guard-relevant columns (employee name, destination, IDT, IAT, actual times, status, departed/arrived state).

## E. Pass Slips Page Changes — admin/pass-slips.php

15. **POST handler changes** (~L183-L194):
    - Replace `update_guard_times` with two actions: `guard_depart` and `guard_arrive`
    - `guard_depart`: Validate caller is guard or superadmin → call `recordDeparture()`
    - `guard_arrive`: Validate caller is guard or superadmin → call `recordArrival()` → call `checkAndFlagVLDeduction()`
16. **Guard-specific list view**: When `$auth->isGuard()`, show a modified table layout:
    - Columns: Control No, Employee Name, Office, Date, IDT, IAT, Status, Departure Status, Arrival Status, Action
    - Departure Status: shows "Not departed" / actual time with green badge
    - Arrival Status: shows "Not arrived" / actual time with green badge / "LATE by Xhr" in red if actual > IAT
    - Action column: "Mark Departed" button (visible only for approved slips with no departure recorded) and "Mark Arrived" button (visible only after departure recorded, before arrival recorded)
    - Default filter: today's date, but guards can browse all dates
    - Status filter: show all statuses so guards see what's pending/approved
17. **Guard section in detail view** (~L503-L535): Restrict the guard form to only `$auth->isGuard() || $auth->isSuperAdmin()`. Show read-only actual times for everyone else.
18. **Hide create/approve/reject/cancel actions from guards**: Guards should NOT see the "New Pass Slip" button, approve/reject modals, or edit forms. They only see list + detail views with departure/arrival buttons.

## F. Pass Slip Creation Form Changes — admin/pass-slips.php (~L972-L1088)

19. **Auto-calculate IAT from IDT**: Add inline JavaScript on the IDT input's `change` event:
    - When user sets IDT, auto-fill IAT to IDT + 3 hours
    - User can manually reduce IAT but not beyond +3 hours from IDT
    - Show validation message if IAT - IDT > 3 hours or IAT ≤ IDT
20. **Enforce on submit**: Both client-side (JS) and server-side (`validateSubmission()`) enforce the 3-hour max

## G. Accumulated Hours Display

21. **On employee's my-requests or pass-slips list view**: Add a banner/card showing:
    - "Your accumulated pass slip hours: X.X hrs / 8 hrs"
    - Progress bar visual
    - When ≥ 8hrs: warning note "You have accumulated a total of 8hrs+ for pass slips. This will be deducted from your Vacation Leave." (highlighted in red/orange)
    - Show breakdown: list of pass slips with their actual hours contributing to the total
22. **On guard's arrival recording**: After clicking "Mark Arrived", if the employee has now crossed the 8hr threshold, show a toast/alert to the guard noting the VL deduction flag
23. **On pass slip detail view**: Show the `vl_deduction_note` if present, and show the employee's accumulated hours

## H. Dashboard Changes — admin/index.php

24. Add a guard-specific dashboard branch: When `$auth->isGuard()`, show:
    - Today's summary: X approved, X departed, X returned, X pending
    - Quick list of today's approved slips with departure/arrival action buttons
    - Redirect guard to pass-slips.php as their primary page (or show a simplified dashboard with a prominent "Go to Pass Slips" button)

## I. Sidebar/Navigation Changes — includes/header.php

25. For guard role, show only: Dashboard, Pass Slips, Profile, Logout. Hide: Authority to Travel, Locator Slips, My Requests, Logs, Users, OIC Management, Unit Routing, etc.

## J. Registration/User Management

26. Guards are NOT self-registering. The superadmin assigns the guard role via admin/users.php — this already works automatically since the role dropdown pulls from the `admin_roles` DB table. No code change needed here beyond the DB migration.

## K. CSS/Styling — admin.css

27. Add styles for:
    - Guard action buttons (green "Mark Departed", blue "Mark Arrived")
    - Late arrival badge (red)
    - Accumulated hours progress bar
    - VL deduction warning banner

---

## Verification

1. **3hr max**: File a pass slip with IDT=9:00, verify IAT auto-fills to 12:00. Try setting IAT to 13:00 — should be blocked both client-side and server-side.
2. **Guard role**: Create a user via superadmin, assign ROLE_GUARD. Login as guard — verify restricted navigation, guard dashboard, and pass slip list with action buttons.
3. **Departure/Arrival recording**: As guard, click "Mark Departed" on an approved slip — verify `actual_departure_time` is recorded as current server time. Click "Mark Arrived" — verify same. Verify buttons disappear after use.
4. **Accumulated hours**: Create multiple pass slips for one employee, record departures and arrivals. Verify accumulated hours sum correctly. Cross 8hr threshold — verify VL deduction note appears for the employee and on the pass slip.
5. **Late arrival**: Set IAT=12:00, record arrival at 13:00 — verify "LATE by 1hr" badge appears and excess hours are tracked.
6. **Permission checks**: Verify non-guard users cannot click departure/arrival buttons. Verify guards cannot create, approve, or reject pass slips.

---

## Decisions

- **ROLE_GUARD = 8**: Next available ID after SDS (7)
- **Accumulated hours never reset**: Per user's requirement, no monthly/yearly reset
- **ALL actual time away counts toward 8hrs**: Not just excess, but total time from actual departure to actual arrival
- **Server time auto-recorded**: Guard clicks button → current PHP `date('H:i:s')` / `NOW()` is stored; no manual time entry
- **VL deduction is flag-only**: No actual VL balance system — just visual notes for HR to process
- **Guard uses same pass-slips.php**: No separate page; role-conditional UI within the existing page
- **`departed_at`/`arrived_at` as TIMESTAMP**: Stores full datetime (not just TIME) for audit trail, while `actual_departure_time`/`actual_arrival_time` remain TIME type for compatibility with existing DOCX template placeholders
