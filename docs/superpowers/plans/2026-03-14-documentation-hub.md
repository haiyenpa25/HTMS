# Documentation Hub Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Transform the single static `/huong-dan/cai-dat` page into a comprehensive Documentation Hub with a unified layout (`DocsLayout.vue`) and detailed guides for Setup, Overview, Duty Roster, Finance, and Membership using Inertia navigation.

**Architecture:** We will create a new layout component (`DocsLayout.vue`) featuring a left sidebar table of contents, and main content area. The `DocsController` will map specific routes (`/huong-dan/cai-dat`, `/huong-dan/lich-phan-cong`, etc.) to distinct Vue components under `Pages/Docs/`.

**Tech Stack:** Laravel, Vue 3, Inertia.js, Tailwind CSS

---

### Task 1: Scaffolding Routing and Controller

**Files:**
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/DocsController.php`

- [ ] **Step 1: Update Routes**
Modify `routes/web.php` to define the new documentation routes under the `huong-dan` prefix: `setup`, `overview`, `dutyRoster`, `members`, `finance`.

- [ ] **Step 2: Update Controller**
Modify `DocsController.php` to add methods returning Inertia views for `Docs/Setup`, `Docs/Overview`, `Docs/DutyRoster`, `Docs/Members`, `Docs/Finance`.

- [ ] **Step 3: Commit**
`git add routes/web.php app/Http/Controllers/DocsController.php && git commit -m "feat(docs): Scaffold documentation routing and controller"`

### Task 2: Create Docs Layout Base

**Files:**
- Create: `resources/js/Layouts/DocsLayout.vue`

- [ ] **Step 1: Implement basic layout structure**
Create the dual-column layout (sidebar navigation and active content) similar to typical documentation sites (e.g., Laravel's or Tailwind's docs layout). Includes a top navigation back to the Dashboard.

- [ ] **Step 2: Define Navigation Links**
In the sidebar, iterate over links pointing to the routes defined in Task 1. Ensure the active route is highlighted.

- [ ] **Step 3: Commit**
`git add resources/js/Layouts/DocsLayout.vue && git commit -m "feat(docs): Create DocsLayout base framework"`

### Task 3: Migrate Setup Page to DocsLayout

**Files:**
- Modify: `resources/js/Pages/Docs/Setup.vue`

- [ ] **Step 1: Wrap Setup content in DocsLayout**
Replace the raw static markup wrapper with the new `DocsLayout` wrapper. Ensure the layout functions correctly when rendering.

- [ ] **Step 2: Commit**
`git add resources/js/Pages/Docs/Setup.vue && git commit -m "refactor(docs): Migrate Setup page to DocsLayout"`

### Task 4: Write Duty Roster Documentation

**Files:**
- Create: `resources/js/Pages/Docs/DutyRoster.vue`

- [ ] **Step 1: Draft the Duty Roster User Guide**
Write the comprehensive guide covering: Templates (creation, sections), Event/Meeting creation, and Assignment workflow. Ensure it includes placeholder areas where screenshots can be displayed.

- [ ] **Step 2: Commit**
`git add resources/js/Pages/Docs/DutyRoster.vue && git commit -m "docs: Add detailed Duty Roster user guide"`

### Task 5: Create Placeholder Pages for Other Modules

**Files:**
- Create: `resources/js/Pages/Docs/Overview.vue`
- Create: `resources/js/Pages/Docs/Members.vue`
- Create: `resources/js/Pages/Docs/Finance.vue`

- [ ] **Step 1: Create Overview Docs Page**
Stub out the Overview guide (Matrix Access Control and Portal features).

- [ ] **Step 2: Create Members & Finance Docs Pages**
Stub out the guides for managing members and finances.

- [ ] **Step 3: Commit and Push**
`git add . && git commit -m "docs: Add overview, members, finance stubs"`
