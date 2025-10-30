<script setup>
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    paginator: {
        type: Object,
        required: true,
    },
});

const MAX_VISIBLE_PAGES = 5;

const visiblePages = computed(() => {
    const current = props.paginator.current_page;
    const last = props.paginator.last_page;
    const range = Math.floor(MAX_VISIBLE_PAGES / 2);
    let start = Math.max(current - range, 1);
    let end = Math.min(current + range, last);

    if (current <= range) {
        end = Math.min(MAX_VISIBLE_PAGES, last);
    }
    if (current >= last - range) {
        start = Math.max(last - MAX_VISIBLE_PAGES + 1, 1);
    }

    const pages = [];
    for (let i = start; i <= end; i++) {
        pages.push({
            label: i.toString(),
            url: buildPageUrl(i),
            active: i === current,
        });
    }

    return pages;
});

// Build URL with all current query parameters
const buildPageUrl = (page) => {
    const url = new URL(props.paginator.path, window.location.origin);

    // Preserve all existing query parameters except 'page'
    const params = new URLSearchParams(window.location.search);
    params.delete("page"); // Remove existing page parameter

    // Add the new page number
    if (page > 1) {
        params.set("page", page);
    } else {
        params.delete("page"); // Remove page param if going to page 1
    }

    return `${url.pathname}?${params.toString()}`;
};

const makeLabel = (label) => {
    if (label.includes("Previous")) return "‹";
    if (label.includes("Next")) return "›";
    return label;
};
</script>

<template>
    <div
        class="flex flex-col sm:flex-row justify-between items-center gap-2 py-4 px-2"
    >
        <!-- Page Info -->
        <p class="text-sm text-[var(--color-text-primary)] mt-1 sm:mt-0">
            Showing
            <span class="font-bold text-[var(--color-text-primary)]">{{
                paginator.from
            }}</span>
            to
            <span class="font-bold text-[var(--color-text-primary)]">{{
                paginator.to
            }}</span>
            of
            <span class="font-bold text-[var(--color-text-primary)]">{{
                paginator.total
            }}</span>
            results
        </p>

        <!-- Pagination Buttons -->
        <div class="flex items-center gap-1 overflow-hidden">
            <!-- First Page -->
            <component
                :is="paginator.current_page > 1 ? 'Link' : 'span'"
                :href="buildPageUrl(1)"
                class="w-8 h-8 text-sm grid place-items-center rounded-lg text-[var(--color-text-primary)] transition-all duration-200"
                :class="[
                    paginator.current_page > 1
                        ? 'hover:bg-[var(--color-primary-hover)] hover:text-[var(--color-text-primary)] cursor-pointer'
                        : 'opacity-50 cursor-not-allowed',
                    'bg-[var(--color-primary)]',
                ]"
                preserve-state
            >
                «
            </component>

            <!-- Previous Page -->
            <component
                :is="paginator.prev_page_url ? 'Link' : 'span'"
                :href="
                    paginator.prev_page_url
                        ? buildPageUrl(paginator.current_page - 1)
                        : null
                "
                class="w-8 h-8 text-sm grid place-items-center rounded-lg text-[var(--color-text-primary)] transition-all duration-200"
                :class="[
                    paginator.prev_page_url
                        ? 'hover:bg-[var(--color-primary-hover)] hover:text-[var(--color-text-primary)] cursor-pointer'
                        : 'opacity-50 cursor-not-allowed',
                    'bg-[var(--color-primary)]',
                ]"
                preserve-state
            >
                ‹
            </component>

            <!-- First Page Indicator -->
            <span
                v-if="visiblePages[0]?.label !== '1'"
                class="w-8 h-8 text-sm grid place-items-center rounded-lg bg-[var(--color-primary)]/50 text-[var(--color-text-primary)] cursor-default"
            >
                ...
            </span>

            <!-- Visible Page Numbers -->
            <component
                v-for="page in visiblePages"
                :is="page.url ? 'Link' : 'span'"
                :href="page.url"
                :key="page.label"
                class="w-8 h-8 text-sm grid place-items-center rounded-lg text-[var(--color-text-primary)] transition-all duration-200 overflow-hidden"
                :class="[
                    page.url
                        ? 'hover:bg-[var(--color-primary-hover)] hover:text-[var(--color-text-primary)] cursor-pointer'
                        : 'cursor-default',
                    page.active
                        ? 'bg-[var(--color-primary-hover)] text-[var(--color-text-primary)] font-bold'
                        : 'bg-[var(--color-primary)] ',
                ]"
                preserve-state
            >
                {{ page.label }}
            </component>

            <!-- Last Page Indicator -->
            <span
                v-if="
                    visiblePages[visiblePages.length - 1]?.label !==
                    paginator.last_page.toString()
                "
                class="w-8 h-8 text-sm grid place-items-center rounded-lg bg-[var(--color-primary)]/50 text-[var(--color-text-primary)] cursor-default"
            >
                ...
            </span>

            <!-- Next Page -->
            <component
                :is="paginator.next_page_url ? 'Link' : 'span'"
                :href="
                    paginator.next_page_url
                        ? buildPageUrl(paginator.current_page + 1)
                        : null
                "
                class="w-8 h-8 text-sm grid place-items-center rounded-lg text-[var(--color-text-primary)] transition-all duration-200"
                :class="[
                    paginator.next_page_url
                        ? 'hover:bg-[var(--color-primary-hover)] hover:text-[var(--color-text-primary)] cursor-pointer'
                        : 'opacity-50 cursor-not-allowed',
                    'bg-[var(--color-primary)] ',
                ]"
                preserve-state
            >
                ›
            </component>

            <!-- Last Page -->
            <component
                :is="
                    paginator.current_page < paginator.last_page
                        ? 'Link'
                        : 'span'
                "
                :href="buildPageUrl(paginator.last_page)"
                class="w-8 h-8 text-sm grid place-items-center rounded-lg text-[var(--color-text-primary)] transition-all duration-200"
                :class="[
                    paginator.current_page < paginator.last_page
                        ? 'hover:bg-[var(--color-primary-hover)] hover:text-[var(--color-text-primary)] cursor-pointer'
                        : 'opacity-50 cursor-not-allowed',
                    'bg-[var(--color-primary)] ',
                ]"
                preserve-state
            >
                »
            </component>
        </div>
    </div>
</template>
