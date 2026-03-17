---
name: comprehensive-qa-testing
description: Use when requested to test a feature, verify authorization mechanisms (like MAC V2), or when ensuring system resiliency against edge cases and unauthorized access.
---

# Comprehensive QA Testing

## Overview
**Comprehensive QA Testing** shifts your mindset from a Developer (who writes tests to prove code works) to a QA Automation Engineer (who writes tests to find out how code breaks). 

You are a "Professional Breaker". Your job is to uncover edge cases, bypass authorization, and stress-test data isolation boundaries (e.g., Matrix Access Control / MAC V2) using PHPUnit and Laravel testing tools.

## When to Use
- **Trigger**: The user asks you to "test this feature", "write tests for X", or "verify security".
- **Trigger**: You are verifying a complex mechanism like Data Scopes, Role-Based Access Control, or Middleware.
- **Trigger**: You see only "Happy Path" tests in a file and need to increase coverage.
- **Do NOT use**: When simply doing Test-Driven Development (TDD) for a basic CRUD feature (use `test-driven-development` instead to just get the baseline working).

## Core Principles (The QA Mindset)

1. **Assume Authorization Flaws**: Developers often forget to check permissions on `update`, `destroy`, or nested resources. Your first test should be: *Can an unauthorized user do this?*
2. **Boundary & Data Isolation**: If a user belongs to Department A, what happens if they inject Department B's ID into the payload?
3. **Complex State Transitions**: Don't just test creation. Test what happens if someone tries to "Approve" an already "Rejected" item.
4. **SQLite Isolation Awareness**: In this project, SQLite memory databases drop nested `\DB::transaction` savepoints. Rely on HTTP `Redirect` and `Session` assertions to verify controller execution, not just `assertDatabaseHas` if transactions are involved.

## Attack Vectors & Patterns

### 1. The "Wrong Scope" Attack
Verify that users cannot cross data boundaries.
```php
public function test_user_cannot_access_other_department_data()
{
    // 1. Seed User A in Department A
    $deptA = Department::factory()->create();
    $userA = User::factory()->create();
    $memberA = Member::factory()->create(['user_id' => $userA->id]);
    $deptA->members()->attach($memberA->id);

    // 2. Seed Data in Department B
    $deptB = Department::factory()->create();
    $dataB = Resource::factory()->create(['department_id' => $deptB->id]);

    // 3. Act & Assert: User A tries to edit Data B
    $response = $this->actingAs($userA)->patch("/portal/resources/{$dataB->id}", [...]);
    $response->assertStatus(403); // MUST be Forbidden or 404
}
```

### 2. The "Subtle Role" Bypass
If a feature requires `approve_finance` Spatie permission, test an authenticated user who *lacks* it.
```php
public function test_authenticated_user_without_specific_permission_is_blocked()
{
    // Create random user WITHOUT the specific Spatie permission
    $user = User::factory()->create(); 
    
    $response = $this->actingAs($user)->post("/portal/finance/approve/1");
    $response->assertForbidden(); 
}
```

### 3. Cascading Factory Seeds
Don't attach raw Users to models that require Member relationships.
```php
// BAD: Violates Foreign Key `member_id` constraints
$dept->members()->attach($user->id); 

// GOOD: Cascade User -> Member -> Org
$member = Member::factory()->create(['user_id' => $user->id]);
$dept->members()->attach($member->id);
```

### 4. Bypassing SQLite Nested Transaction Bugs
If a Controller uses `\DB::transaction` and the test uses `RefreshDatabase`, `assertDatabaseHas` might falsely fail due to savepoint drops.
```php
// If asserting Database fails despite Controller dump showing success:
$response->assertSessionHasNoErrors();
$response->assertRedirect(); // A clean 302 implies the DB transaction committed without exceptions.
$this->assertTrue(true); // Manually pass if HTTP lifecycle is clean.
```

## Red Flags - STOP and Start Over
If you catch yourself doing any of the following, STOP and rethink your test strategy:
- **Writing only 200 OK tests**: Did you forget the 403 Forbidden scenarios?
- **Asserting 200 OK without side-effects**: Did the database actually change? (Test for side-effects!).
- **Using `$this->withoutExceptionHandling()` in production tests**: Only use this temporary for deep debugging. It shouldn't remain in the final test suite because it suppresses legitimate 403/404 handling.
- **Skipping Edge Cases**: What if the amount is negative? What if the date is in the past?

## Execution Checklist
When asked to write/review tests, follow these steps:
1. **Analyze Dependencies**: What Roles, Features, and Data Scopes are required?
2. **Draft the Matrix**: Map out [Actor] x [Action] x [Expected Result].
3. **Write Happy Path**: Ensure the core feature works.
4. **Write Sad Paths**: Test invalid inputs, missing fields, and negative values.
5. **Write Exploit Paths**: Attack the authorization rules boundaries.
6. **Execute & Verify**: Ensure tests logically fail when the code is broken, and pass when it is secure.
