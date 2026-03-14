# Writing Plan: Comprehensive User Guide & Demo Data

## Phase 1: Mock Data Generation (FullDemoSeeder)
- [ ] Implement `database/seeders/FullDemoSeeder.php`.
- [ ] Add Factory states for `Asset`, `AssetLoan`, `CareRequest`, `Visitor`, `VisitorFollowup`, `Donation`, `Broadcast`, `Announcement`, `Document`, `EducationClass`, `EducationSession`.
- [ ] Run the seeder to populate the local database for screenshotting.

## Phase 2: System Admin & Leadership Docs
- [ ] **Task 2.1:** Update `web.php` and `DocsController` with routes for `admin`, `leadership`, `care`, `assets`.
- [ ] **Task 2.2:** Add links to `DocsLayout.vue` under new categories (Header separation for Admin / Lãnh đạo).
- [ ] **Task 2.3:** Write `Docs/SysAdmin.vue` (Users, Features, Broadcasts).
- [ ] **Task 2.4:** Write `Docs/Leadership.vue` (Dashboard, Visitors, Assets, Care Tickets).

## Phase 3: Portals & Education Docs
- [ ] **Task 3.1:** Add routes for `portals`, `education`, `member-app`.
- [ ] **Task 3.2:** Write `Docs/Education.vue` (Classes, Sessions, Attendance, Offerings).
- [ ] **Task 3.3:** Write `Docs/Portals.vue` (Deacon, Ministry, Member App differences).

## Phase 4: Capture UI & Polish
- [ ] User takes screenshots of the newly seeded data in the browser.
- [ ] Update documentation pages with actual image tags pointing to `/images/docs/x.png`.
