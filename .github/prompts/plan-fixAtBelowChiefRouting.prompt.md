# Fix AT Routing for Below-Division-Chief Personal/International Travel

## Problem

When requestors below Division Chiefs file an Authority to Travel for **personal local outside region** or **international (personal and official)**, the request is sent to Division Chiefs with `routing_stage = 'final'`, but `getAvailableAction()` has no code path allowing Division Chiefs to approve at the final stage — resulting in no action button.

Per the user and the attached DepEd Order 043 routing table (row 4), the fix is to change the routing so Division Chiefs **recommend** (not final-approve) and **SDS is the final approver**.

---

## Root Cause

In [models/AuthorityToTravel.php](models/AuthorityToTravel.php#L73-L80), the below-Division-Chief block inside the `$isPersonalOrInternational` section sets:

```php
'routing_stage' => 'final',
'final_approver_role' => $recommenderRoleName,  // Division Chief
```

Meanwhile, `getAvailableAction()` at [line 1063](models/AuthorityToTravel.php#L1063) only returns `'recommend'` for `UNIT_HEAD_ROLES` at `routing_stage = 'recommending'`. There is **no code path** for unit heads to `'approve'` at `'final'` stage — only SDS is allowed to approve at final stage. So Division Chiefs see no button.

Additionally, the approve POST handler at [line 104](admin/authority-to-travel.php#L104) guards with `$auth->isASDS() || $auth->isSDS()` — Division Chiefs are excluded from approving even if the button were shown.

---

## Steps

### 1. Change routing in `determineRouting()` — `models/AuthorityToTravel.php` lines 73–80

**Current** (below-Division-Chief, personal or international):

```php
// Below Division Chief — final approver is their Division Chief
$recommenderRole = $this->getRecommenderRoleForOffice($requesterOfficeId, $requesterOffice, $travelScope);
$recommenderRoleName = $this->getRoleNameById($recommenderRole);
return array_merge($base, [
    'current_approver_role' => $recommenderRoleName,
    'routing_stage' => 'final',
    'final_approver_role' => $recommenderRoleName,
    'forwarded_to_ro' => 0,
]);
```

**Change to:**

```php
// Below Division Chief — Division Chief recommends -> SDS final approves
$recommenderRole = $this->getRecommenderRoleForOffice($requesterOfficeId, $requesterOffice, $travelScope);
$recommenderRoleName = $this->getRoleNameById($recommenderRole);
return array_merge($base, [
    'current_approver_role' => $recommenderRoleName,
    'routing_stage' => 'recommending',
    'final_approver_role' => 'SDS',
    'forwarded_to_ro' => 0,
]);
```

This makes the Division Chief the **recommending authority** and SDS the **final approver**, matching row 4 of the DepEd Order 043 routing table.

### 2. No changes needed to `getAvailableAction()`

The existing code at [line 1063](models/AuthorityToTravel.php#L1063) already returns `'recommend'` for `UNIT_HEAD_ROLES` at `routing_stage = 'recommending'`. Once step 1 changes the routing stage, Division Chiefs will see the "Recommend Approval" button.

### 3. No changes needed to POST handlers

The recommend POST handler at [line 128](admin/authority-to-travel.php#L128) already accepts `$auth->isUnitHead()`. No changes needed.

### 4. No changes needed to `recommend()` method

The `recommend()` method at [line 818](models/AuthorityToTravel.php#L818) already reads `final_approver_role` from the AT record and routes dynamically. With `final_approver_role = 'SDS'`, it will set `current_approver_role = 'SDS'`, `routing_stage = 'final'`, `status = 'recommended'` — which gives SDS the approve button.

### 5. Update the prompt plan decision — `.github/prompts/plan-depEdOrder043Routing.prompt.md`

The decision at [line 17](plan-depEdOrder043Routing.prompt.md#L17):

> Div Chief personal/international: Final approver = SDS, no recommending step.

should be updated to clarify the below-Division-Chief case.

The routing table at [line 74](plan-depEdOrder043Routing.prompt.md#L74):

```
| Below Division Chief | NULL | Division Chief | 0 | final |
```

should be updated to:

```
| Below Division Chief | Division Chief | SDS | 0 | recommending |
```

---

## Updated Routing Table (Personal OR International)

| Requester                        | Recommender        | Final Approver | forwarded_to_ro | routing_stage    |
| -------------------------------- | ------------------ | -------------- | --------------- | ---------------- |
| SDS                              | NULL               | RD             | 1               | completed        |
| ASDS                             | NULL               | SDS            | 0               | final            |
| Division Chief (UNIT_HEAD_ROLES) | NULL               | SDS            | 0               | final            |
| **Below Division Chief**         | **Division Chief** | **SDS**        | **0**           | **recommending** |

---

## Decision

Chose to change routing logic (Division Chief recommends → SDS approves) over adding an approve path for Division Chiefs, because:

- The user explicitly stated "the recommending approval must direct for final approver for the SDS"
- The attached DepEd Order 043 routing table (row 4) confirms Division Chiefs are the **Approving Authority** (recommender) and SDS is the **Final Approver** for requestors below Division Chiefs

---

## Verification

1. File an AT as a user below Division Chief with travel type **personal + local outside region** — verify the Division Chief sees the "Recommend Approval" button
2. Have the Division Chief click "Recommend Approval" — verify status changes to `recommended`, `current_approver_role` = `SDS`, `routing_stage` = `final`
3. Log in as SDS — verify the "Approve" button appears and approval works
4. Repeat steps 1–3 for **international personal** and **international official** travel types
5. Verify existing local official routing (within region and outside region) for below-Division-Chief requestors is unaffected (already uses `routing_stage = 'recommending'` with `final_approver_role = 'SDS'`)
