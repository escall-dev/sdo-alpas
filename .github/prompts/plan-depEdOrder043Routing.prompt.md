# DepEd Order 043 s. 2022 — AT Routing Overhaul Plan

## Summary

The current routing engine is a flat 2-tier model (unit chief recommends → SDS approves) that ignores travel type, destination scope, and requester position. This plan rewrites `determineRouting()` and surrounding logic into a position-aware, travel-type-aware decision tree per DepEd Order 043 s. 2022. Key changes: add 3rd travel scope value (`national` → outside-region-domestic, new `international`), make ASDS a recommending authority for Division Chiefs, remove `DIRECT_SDS_POSITIONS` bypass, add "forwarded to RO" handling for SDS/ASDS cases that need RD, and update the `recommend()` pipeline so the post-recommendation target is dynamic (not hardcoded to SDS).

---

## Decisions (User-Confirmed)

| Decision | Choice |
|----------|--------|
| Travel scopes | 3 values: `local` (Within Region), `national` (Outside Region), `international` (International) |
| RD handling | No RD role in system. SDS/ASDS→RD cases marked `status='approved'` + `forwarded_to_ro=1`. RD has no account. |
| DIRECT_SDS_POSITIONS | **Removed**. Attorney III, Accountant III, AO V follow standard chief → SDS chain. |
| ASDS recommending | Yes — ASDS gains `recommend` action for Division Chief local official travel. |
| PSDS | Regular users (`role_id=6`) identified by `employee_position`. Fall under CID/Division Chief routing. |
| Forwarded status | Use `status='approved'` with a `forwarded_to_ro` flag column (not a new status ENUM value). |
| Div Chief personal/international | Final approver = SDS, no recommending step. |

---

## Steps

### 1. Database Migration — Add `national` scope + `forwarded_to_ro` flag

Create `sql/migration_do043_routing.sql`:

- `ALTER TABLE authority_to_travel` → change `travel_scope` ENUM from `('local','national')` to `('local','national','international')`. The existing `national` value stays but its UI label changes to "Outside Region (National)". The new `international` value covers abroad travel.
- Add column `forwarded_to_ro TINYINT(1) DEFAULT 0` — flags ATs that need RD approval (for SDS & ASDS-outside-region cases). The AT keeps `status='approved'` locally but with this flag set.
- Add column `forwarded_to_ro_date DATE DEFAULT NULL`.
- Add column `final_approver_role VARCHAR(50) DEFAULT NULL` — stores the intended final approver at creation time so `recommend()` knows where to route after recommendation, instead of hardcoding `'SDS'`.
- Update `unit_routing_config.travel_scope` ENUM to include `'national'` alongside existing `'all','local','international'`.

### 2. Update Config Constants — `config/admin_config.php`

- Change `AT_SCOPES` from `['local' => 'Local', 'national' => 'National']` to `['local' => 'Within Region', 'national' => 'Outside Region (National)', 'international' => 'International']`.
- **Remove** `DIRECT_SDS_POSITIONS` constant entirely (Attorney III, Accountant III, AO V will follow standard chief → SDS chain).
- Update `RECOMMENDING_AUTHORITY_MAP` to include `ROLE_ASDS => 'ASDS'`.

### 3. Rewrite `determineRouting()` — `models/AuthorityToTravel.php` (lines 25–63)

Replace the current method with the full DepEd 043 decision tree. The new method signature keeps all existing params and adds `$travelCategory`:

```
determineRouting($requesterRoleId, $requesterOfficeId, $requesterOffice, $travelScope, $employeePosition, $travelCategory)
```

**Logic (pseudocode):**

#### If `travel_category` = personal OR `travel_scope` = international:

Single-level approval only. No recommending approver.

| Requester | Recommender | Final Approver | forwarded_to_ro | routing_stage |
|-----------|-------------|----------------|-----------------|---------------|
| SDS | NULL | RD | 1 | completed |
| ASDS | NULL | SDS | 0 | final |
| Division Chief (UNIT_HEAD_ROLES) | NULL | SDS | 0 | final |
| Below Division Chief | NULL | Division Chief | 0 | final |

#### If `travel_scope` = local (Within Region) — Official:

| Requester | Recommender | Final Approver | forwarded_to_ro | routing_stage |
|-----------|-------------|----------------|-----------------|---------------|
| SDS | NULL | RD | 1 | completed |
| ASDS | SDS | SDS | 0 | final* |
| Division Chief | ASDS | SDS | 0 | recommending |
| Below Division Chief | Division Chief | SDS | 0 | recommending |

*ASDS within region: recommender = SDS, final = SDS. Since the same person both recommends and approves, route directly to SDS at `final` stage (single step).

#### If `travel_scope` = national (Outside Region, Domestic) — Official:

| Requester | Recommender | Final Approver | forwarded_to_ro | routing_stage |
|-----------|-------------|----------------|-----------------|---------------|
| SDS | NULL | RD | 1 | completed |
| ASDS | SDS | RD | 0 | recommending → then forwarded_to_ro=1 |
| Division Chief | ASDS | SDS | 0 | recommending |
| Below Division Chief | Division Chief | SDS | 0 | recommending |

### 4. Update `recommend()` — `models/AuthorityToTravel.php` (lines 670–683)

Change `current_approver_role = 'SDS'` (hardcoded) to use the stored `final_approver_role` from the AT record:

- Read `$at['final_approver_role']`
- If it equals `'RD'`, set `forwarded_to_ro = 1`, `status = 'approved'`, `routing_stage = 'completed'` (auto-forwarded after recommendation)
- Otherwise, set `current_approver_role = $at['final_approver_role']`, `routing_stage = 'final'`

### 5. Update `create()` — `models/AuthorityToTravel.php` (lines 247–317)

- Pass `$travelCategory` to `determineRouting()`
- Store `final_approver_role` and `forwarded_to_ro` from routing result into the INSERT
- For SDS filing their own AT: immediately set `status = 'approved'`, `forwarded_to_ro = 1`, `routing_stage = 'completed'` (auto-forwarded to RO since no in-system approver)
- Remove the `DIRECT_SDS_POSITIONS` bypass (already removed from `determineRouting()`)

### 6. Update `getAvailableAction()` — `models/AuthorityToTravel.php` (lines 856–870)

- Add `ROLE_ASDS` as valid for `'recommend'` action when `routing_stage = 'recommending'` and `current_approver_role = 'ASDS'`
- Keep SDS approve at final stage
- Keep unit head recommend at recommending stage
- **Remove** ASDS approve at final stage — per DepEd 043, ASDS is never the final approver for AT

### 7. Update `canUserActOn()` — `models/AuthorityToTravel.php` (lines 793–828)

- Add ASDS to the list of roles that can act (for recommending stage on Division Chief ATs)
- ASDS doesn't need supervised-office verification since they recommend for all Division Chiefs regardless of division

### 8. Update Form UI — `admin/authority-to-travel.php` (lines 1140–1260)

- Change radio buttons from 2 options (Local / International) to 3 options:
  - **Within Region** (`local`) — forces `travel_category = 'official'`
  - **Outside Region (National)** (`national`) — shows Official/Personal picker
  - **International** (`international`) — shows Official/Personal picker
- Update `toggleScopeCategory()` JS to handle the 3rd option
- Update submission handler to pass `travel_category` along for routing

### 9. Update POST Handlers — `admin/authority-to-travel.php` (lines 31–118)

- **Create handler**: Pass `travel_category` to the model `create()` so it can be forwarded to `determineRouting()`
- **SDS filing**: Show success message "Forwarded to Regional Office for RD approval"
- **Recommend handler** (line 97): Change guard from `$auth->isUnitHead()` to `$auth->isUnitHead() || $auth->isASDS()` so ASDS can recommend Division Chief ATs
- **Approve handler**: Remove ASDS from final approval (currently allowed on line 76)

### 10. Update Visibility/Filtering — `models/AuthorityToTravel.php` (lines 420–469)

- ASDS: when in recommending stage and `current_approver_role = 'ASDS'`, show those ATs in ASDS's queue
- Add `forwarded_to_ro` display in list views — show a badge "Forwarded to RO" for those records

### 11. Update Activity Logging

- In `create()`, after `determineRouting()`, add structured logging via `$auth->logActivity()` with action type `'ROUTING_DECISION'`:
  - `travel_type` (category + scope)
  - `position` (requester role/position)
  - `destination_scope` (travel_scope value)
  - `assigned_recommending_approver`
  - `assigned_final_approver`
  - `forwarded_to_ro` (true/false)

### 12. Add Routing Rejection Guard

- In `create()`, if `determineRouting()` returns `null` or cannot resolve a chain, throw an exception with a descriptive message
- The submission must be **rejected** if routing rule cannot be resolved (per "Reject submission if routing rule cannot be resolved" constraint)

### 13. Update `getTypeLabel()` — `models/AuthorityToTravel.php` (lines 1068–1077)

- Add `national` scope: return `'Official - National'`
- Keep `international` → `'Official - International'`
- Keep `personal` → `'Personal'`
- Keep `local` → `'Official - Local'`

### 14. Update DOCX Generation — `services/DocxGenerator.php`

- Handle the new `national` scope label in template selection (currently maps `national` → `at_national` template)
- Map `international` → `at_national` template (same as previous `national`)
- Ensure `forwarded_to_ro` ATs still generate the correct document

### 15. Block Manual Override of Final Approver

- Ensure no admin UI or API endpoint allows changing the `final_approver_role` or `current_approver_role` fields directly
- Review `executiveApprove()`: per strict enforcement, restrict to only work when SDS is actually the designated `final_approver_role` for that AT (prevents SDS from overriding an AT that should go to RD)

### 16. Update `executiveApprove()` — `models/AuthorityToTravel.php` (lines 753–781)

- Add guard: if `$at['forwarded_to_ro'] == 1` or `$at['final_approver_role'] == 'RD'`, block executive override (SDS cannot substitute for RD)
- If `$at['final_approver_role'] == 'SDS'`, allow executive override as before

---

## Routing Decision Tree (Visual Reference)

```
START
│
├── Check travel_category + travel_scope
│
├── IF personal OR international
│   │
│   ├── No Recommender for any position
│   │
│   ├── SDS → forwarded_to_ro (RD approval)
│   ├── ASDS → Final = SDS
│   ├── Division Chief → Final = SDS
│   └── Below Division Chief → Final = Division Chief
│
├── IF local (Within Region) — Official
│   │
│   ├── SDS → forwarded_to_ro (RD approval)
│   ├── ASDS → Final = SDS (single step, SDS is both recommender & approver)
│   ├── Division Chief → Recommender = ASDS → Final = SDS
│   └── Below Division Chief → Recommender = Division Chief → Final = SDS
│
└── IF national (Outside Region) — Official
    │
    ├── SDS → forwarded_to_ro (RD approval)
    ├── ASDS → Recommender = SDS → then forwarded_to_ro (RD approval)
    ├── Division Chief → Recommender = ASDS → Final = SDS
    └── Below Division Chief → Recommender = Division Chief → Final = SDS
```

---

## Files Changed (Summary)

| File | Action |
|------|--------|
| `sql/migration_do043_routing.sql` | **New** — migration script |
| `config/admin_config.php` | Edit — scopes, remove DIRECT_SDS_POSITIONS, update maps |
| `models/AuthorityToTravel.php` | Major rewrite — determineRouting, recommend, create, canUserActOn, getAvailableAction, getTypeLabel |
| `admin/authority-to-travel.php` | Edit — form UI (3 scopes), POST handlers (ASDS recommend, SDS forwarding), visibility |
| `services/DocxGenerator.php` | Edit — template mapping for new scopes |

---

## Verification Checklist

- [ ] Run `sql/migration_do043_routing.sql` on dev database
- [ ] SDS files local AT → auto-forwarded to RO, `forwarded_to_ro = 1`
- [ ] ASDS files local within-region AT → routes to SDS final
- [ ] ASDS files national AT → routes to SDS recommend, then forwarded to RO
- [ ] Division Chief files local AT → routes to ASDS recommend → SDS final
- [ ] Division Chief files personal AT → routes to SDS final (no recommender)
- [ ] Regular user / PSDS files local AT → routes to Division Chief recommend → SDS final
- [ ] Regular user files personal AT → routes to Division Chief final (no recommender)
- [ ] ASDS can see and act on Division Chief recommending-stage ATs
- [ ] `forwarded_to_ro` badge appears in list views
- [ ] Attorney III / Accountant III / AO V now route through their chief (no bypass)
- [ ] Submission is rejected if routing fails
- [ ] `ROUTING_DECISION` activity log entry with full context is created
- [ ] Executive override blocked for ATs designated for RD
- [ ] No manual override of `final_approver_role` possible via UI
