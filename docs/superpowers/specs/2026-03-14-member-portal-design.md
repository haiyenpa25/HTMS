# Member Portal Enhancements Design Spec

## 1. Overview
The user wants to revamp the Member Portal (`/member`) with three major enhancements:
1. **Custom Calendar UI:** Instead of integrating the heavy FullCalendar library, we will craft a neat, custom Tailwind-based Calendar that fits seamlessly into the portal, allowing Week/Month views.
2. **Personal Info Card with Map:** Improve the member profile card to display detailed information (address, tags, faith journey dates) and intergrate a Leaflet Map pinpointing the member's GPS location.
3. **Church Announcements:** Implement a news creation module for the Admin under "Truyền thông > Tạo tin tức", which subsequently pushes rich-text announcements to the Member Portal.

## 2. Architecture & Approach
### 2.1 Calendar Component
- Fetch events using the existing `/api/calendar/events` endpoint from `CalendarController` via Axios.
- Render a custom Week/Month layout using native JavaScript Date math inside Vue 3.
- Map the event categories directly to color dots under corresponding days.

### 2.2 Member Info Card & Map
- Requires installing `leaflet` and `@vue-leaflet/vue-leaflet` (or basic `leaflet` JS approach).
- The `Member` model already supports `latitude` and `longitude`. If these values are valid, render the map. If not, render a fallback visual or generic OpenStreetMap tile.

### 2.3 News & Announcements
- **Backend:** `Announcement.php` model exists.
  - Create `AnnouncementController` with `index`, `create`, `store`, `edit`, `destroy` methods.
  - Routes in `web.php` under `communication`.
- **Frontend Admin:** `resources/js/Pages/Communication/Announcements/`
  - WYSIWYG Editor for Rich text editing (Installing `@vueup/vue-quill` to avoid complexities).
- **Frontend Member:** Fetch latest announcements and display them in `MemberPortal/Index.vue`.

## 3. Tech Stack Impacts
- Vue 3 + Tailwind CSS + InertiaJS (Laravel Backend).
- New npm dependencies: `leaflet`, `@vueup/vue-quill`.

## 4. Risks & Mitigations
- **Map rendering:** Browsers sometimes face map container sizing issues if rendered inside hidden tabs. Mitigation: Call `.invalidateSize()` upon tab/modal visibility change or use standard fixed containers.
- **Rich Text Content:** Outputting raw HTML from Quill can pose XSS risks. Since it's from trusted internal Admins, rendering via `v-html` is acceptable, along with typical Laravel string sanitization.
