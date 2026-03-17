---
name: htms-ui-patterns
description: Use when creating or refactoring management screens (like Assets, Members, Chronicles) that list data and manage complex entity forms
---

# HTMS UI Patterns

## Overview
This skill standardizes UI consistency across HTMS management screens. HTMS uses a modern layout emphasizing vertical space by replacing traditional centering Modals with right-side Side Panels (SlideOver) for complex forms, and providing contextual aggregate statistics in a 4-card Grid Header.

## When to Use
- When implementing a new CRUD index page for complex entities.
- When an index page lacks an overview of its dataset.
- When refactoring an existing management screen that currently uses popup `<Modal>` components.

## Core Pattern 1: Stats Grid Header
Before the main data table or timeline block, inject a responsive 4-column grid mapping to a `$stats` aggregate object from the Controller.

```vue
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl shadow-sm border flex flex-col justify-between">
        <p class="text-xs font-bold uppercase tracking-wider mb-2">Tổng Số Lượng</p>
        <div class="flex items-end justify-between">
            <h3 class="text-3xl font-black">{{ stats.total }}</h3>
            <div class="w-10 h-10 rounded-full flex items-center justify-center">
                <!-- Icon Here -->
            </div>
        </div>
    </div>
    <!-- 3 more cards with varied semantic colors (indigo, amber, emerald) -->
</div>
```

*Controller Side:*
Ensure to calculate the aggregate data via standard Eloquent counts and pass it directly to Inertia as `stats`.

## Core Pattern 2: SlideOver Drawer
Instead of placing forms inside `<Modal>` (which centers and shrinks real estate on mobile), use the HTMS custom `<SlideOver>` wrapper.

**Before (Anti-Pattern):**
```vue
<Modal :show="showModal" @close="closeModal" maxWidth="2xl">
    <form @submit.prevent="submit">
       <!-- Form fields -->
       <PrimaryButton type="submit">Lưu</PrimaryButton>
    </form>
</Modal>
```

**After (HTMS Standard):**
```vue
<SlideOver v-model="showModal" title="Tạo Mới Thực Thể" size="md">
    <form id="entityForm" @submit.prevent="submit">
        <!-- Form Fields -->
    </form>
    
    <template #footer>
        <div class="flex justify-end gap-3 w-full">
            <SecondaryButton @click="showModal = false">Hủy</SecondaryButton>
            <PrimaryButton form="entityForm" type="submit">Lưu Hệ Thống</PrimaryButton>
        </div>
    </template>
</SlideOver>
```
*Key notes: Make sure the action buttons are placed inside `<template #footer>`, using the HTML5 `form="id"` attribute binding so it links the isolated button back to the isolated `<form>` tags.*

## Common Mistakes
- Not replacing `import Modal from '@/Components/Modal.vue'` with `import SlideOver...`
- Forgetting to explicitly add `type="submit"` and `form="entityForm"` to the PrimaryButton inside the footer. Without this, the form submit payload will not trigger.
- Placing the `<SlideOver>` element inside layout wrappers that limit z-index, causing the sliding panel to get cut off. Always put `<SlideOver>` near the bottom of `<template>` relative to your layouts.
