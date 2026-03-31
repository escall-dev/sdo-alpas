`## Plan: AT/LS Routing, UI, and Approval Flow Updates

**TL;DR**: Add unit/office fields to the AT detail card, implement direct-to-SDS routing for Attorney III / Accountant III / AO V positions, fix ASDS→SDS routing in LS, add SDS visibility and view-only access in LS, add ASDS/SDS as approver filter options, remove summary cards from unit-routing.php, normalize password-resets.php to use shared CSS classes, and fix responsive menu behavior. All routing changes are driven by `employee_position` matching (not new roles).

---

**Steps**

### 1. Display Unit & Office in AT Detail Card

In [admin/authority-to-travel.php](admin/authority-to-travel.php#L374-L378), after the "Position" `detail-item` (line 374), insert two new `detail-item` divs:

- **Office**: label "Office/Division", value `$viewData['requester_office']`
- **Unit**: label "Unit", value from a joined office name via `requester_office_id` (or fallback to `requester_office` if no separate unit concept exists)

The data is already fetched by `getById()` (`SELECT at.*` at [AuthorityToTravel.php line 355](models/AuthorityToTravel.php#L355)). No model changes needed — just template additions.

For SDS role: render these fields as plain text (no edit capability). All other roles also see them as read-only display since office is derived from user profile, not form input.

### 2. Direct-to-SDS Routing for Specific Positions

In [models/AuthorityToTravel.php](models/AuthorityToTravel.php#L24-L49) `determineRouting()`:

- Add a position-title check: if `employee_position` matches "Attorney III", "Accountant III", or "Administrative Officer V" (case-insensitive), skip the recommending step and route directly to SDS (`routing_stage = 'final'`, `current_approver_role = 'SDS'`).
- This mirrors the existing Unit Head logic at [line 28](models/AuthorityToTravel.php#L28) but uses position title instead of role ID.
- Define a constant `DIRECT_SDS_POSITIONS` in [config/admin_config.php](config/admin_config.php) for maintainability:
  ```
  DIRECT_SDS_POSITIONS = ['Attorney III', 'Accountant III', 'Administrative Officer V']
  ```

In [models/LocatorSlip.php](models/LocatorSlip.php#L92-L127) `create()`:

- Same position-title check: if the requester's position is in `DIRECT_SDS_POSITIONS`, route directly to SDS instead of OSDS Chief.
- Set `assigned_approver_role_id = ROLE_SDS` and resolve `assigned_approver_user_id` for SDS.

For these positions, ensure no intermediate approver is selectable — the routing is hardcoded. No UI changes needed since the approver is auto-determined server-side (users never pick their approver).

### 3. ASDS → SDS Routing Consistency

**AT (already correct)**: `recommend()` at [AuthorityToTravel.php line 665](models/AuthorityToTravel.php#L665) already sets `current_approver_role = 'SDS'`. No change needed.

**LS**: In [models/LocatorSlip.php](models/LocatorSlip.php#L101-L117),

- Change the routing so that after ASDS approval, the flow completes (LS is single-step, so ASDS approval = final). However, when the ASDS files for request of LS "Set SDS as the approver" — meaning LS filed by ASDS should route to **SDS**.

### 4. SDS Visibility & View-Only in LS

In [models/LocatorSlip.php](models/LocatorSlip.php#L202-L290) `getAll()`:

- Add explicit `ROLE_SDS` handling: SDS sees **all** LS records (like Superadmin), but with no action buttons.

In [admin/locator-slips.php](admin/locator-slips.php#L100-L142):

- In the approval POST handler, block SDS from approving/disapproving LS.
- In the detail view sidebar (actions card), hide Approve/Disapprove buttons when `$currentRoleId === ROLE_SDS`.

In [models/LocatorSlip.php](models/LocatorSlip.php) `getAvailableAction()`:

- Return `null` for `ROLE_SDS` to enforce view-only.

### 5. Remove Summary Cards from unit-routing.php

Remove the "Summary Cards by Approver" HTML block at [admin/unit-routing.php lines 309-326](admin/unit-routing.php#L309-L326) and its associated inline `<style>` block at [lines 529-590](admin/unit-routing.php#L529-L590) (`.stats-grid-approvers`, `.stat-card-compact` etc.).

### 6. Uniform Color Palette for password-resets.php

In [admin/password-resets.php](admin/password-resets.php):

- Replace all inline `style=""` stat cards (lines 89-112) with `.stats-row` + `.stat-card` CSS classes used in other modules.
- Replace inline filter bar styling (lines 115-142) with `.filter-bar` + `.filter-form` + `.filter-group` classes.
- Replace inline status badges with `.status-badge` + `.status-approved` / `.status-disapproved` classes.
- Replace inline button colors with `.btn-primary`, `.btn-danger`, `.btn-success` classes.
- This aligns password-resets with the design system in [admin.css](admin.css#L841-L935).

### 7. ASDS & SDS Approver Filter Options

In [admin/authority-to-travel.php](admin/authority-to-travel.php#L974-L995) filter section:

- Add "ASDS" and "SDS" as explicit `<option>` entries in the Approver dropdown, using `current_approver_role` as the filter key (value = role name string, not user ID). This requires distinguishing between filtering by `approver_id` (user) vs `current_approver_role` (role name).
- Alternatively, add them as special options in the existing approver `<select>` with prefixed values like `role:ASDS`, `role:SDS`, and handle parsing in the PHP filter logic.

In [admin/locator-slips.php](admin/locator-slips.php#L702-L712) filter section:

- Same approach: add ASDS and SDS as filter options.

In both models' `getAll()` methods, support filtering by `current_approver_role` when the special role-prefixed value is detected.

### 8. AT & LS Color Scheme Alignment

Review [admin/authority-to-travel.php](admin/authority-to-travel.php) and [admin/locator-slips.php](admin/locator-slips.php) to ensure both use the same CSS variables and class patterns from [admin.css](admin.css#L4-L56):

- Status badges: `.status-pending`, `.status-recommended`, `.status-approved`, `.status-disapproved`
- Cards: `.detail-card`, `.ref-card`
- Buttons: `.btn-primary`, `.btn-success`, `.btn-danger`
- Any inline color overrides should be replaced with CSS variable references.

### 9. LS Badge Count for AO V

In [includes/header.php](includes/header.php#L19-L51):

- Currently, unit heads get `$ls_pending = 0`. For `ROLE_OSDS_CHIEF` (AO V), the LS badge should show the count of pending LS assigned to them.
- Update the badge logic: if the effective role is OSDS Chief, query `LocatorSlip::getStatistics()` filtered to their role to get pending count.
- Apply `.nav-badge` CSS class (already exists at [admin.css line 393](admin.css#L393)).
- Hide badge when count = 0 (already handled by the `if ($ls_pending > 0)` conditional at [header.php line 122](includes/header.php#L122)).

### 10. Unit Routing Rules Enforcement

In [admin/unit-routing.php](admin/unit-routing.php):

- Add explicit routing entries (or validation) for ASDS → SDS and the three direct-SDS positions.
- The `unit_routing_config` table constrains `approver_role_id` to 3/4/5 (unit heads). To route directly to SDS (role 7), this constraint needs updating, OR the direct-SDS logic remains hardcoded in the models via `DIRECT_SDS_POSITIONS`.
- Recommended: keep `DIRECT_SDS_POSITIONS` as a config constant and document it in the routing page UI (read-only display) so admins are aware of these fixed routes.

### 11. Fix `fas fa-bars` Responsive Behavior

In [admin.js](admin.js#L43-L100):

- The `initSidebar()` references `#sidebarToggle` but the actual ID is `#desktopSidebarToggle` (from [header.php line 223](includes/header.php#L223)). Fix the selector.
- There's a competing sidebar toggle in [includes/footer.php](includes/footer.php#L35-L66). Consolidate both implementations into `admin.js` `initSidebar()` and remove the duplicate IIFE from footer.php.
- In the resize handler ([admin.js lines 86-100](admin.js#L86-L100)), ensure:
  - Below 992px: sidebar closes, mobile toggle (`.fas.fa-bars`) is visible and functional.
  - Above 992px: sidebar restores from localStorage state, mobile toggle hides.
  - Transitioning between breakpoints doesn't leave the sidebar in an inconsistent state (e.g., `open` class persisting after resize to desktop).

---

**Verification**

1. **Manual test**: Log in as each role (User, AO V, CID Chief, SGOD Chief, ASDS, SDS, Superadmin) and verify:
   - AT detail card shows Office and Unit fields
   - SDS sees all LS but cannot approve/disapprove
   - Attorney III / Accountant III / AO V AT requests route directly to SDS
   - LS filed by unit heads routes to SDS (not ASDS)
   - ASDS and SDS appear in approver filter dropdowns
   - Password resets page uses uniform styling
2. **Responsive test**: Resize browser through 1200px → 992px → 640px breakpoints. Verify hamburger menu toggles correctly, sidebar doesn't get stuck.
3. **Badge test**: Log in as AO V — LS nav item should show pending count badge. Badge disappears when count = 0.
4. **Routing consistency**: Create test AT and LS records for each position type and verify the `current_approver_role` / `assigned_approver_role_id` values in the database match expected routing.

---

**Decisions**

- **Position matching over new roles**: Attorney III, Accountant III, AO V identified by `employee_position` field, not new `ROLE_*` constants. Config constant `DIRECT_SDS_POSITIONS` used for maintainability.
- **LS ASDS→SDS**:LS filed by ASDS will redirect directly to SDS.
- **SDS view-only scope**: SDS is view-only for LS only; retains full AT approval power.
- **Summary cards removal**: Removed from unit-routing.php entirely (not just hidden by role).
- **Sidebar consolidation**: Single implementation in admin.js, removing duplicate from footer.php.
