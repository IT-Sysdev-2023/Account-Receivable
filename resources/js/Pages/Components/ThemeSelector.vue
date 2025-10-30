<!-- resources/js/Components/ThemeSelector.vue -->
<script setup>
import useTheme from "../Composables/useTheme";
import { SunIcon, MoonIcon, SparklesIcon } from "@heroicons/vue/24/outline";

const { theme, setTheme } = useTheme();

const themeOptions = [
    { name: "Light", value: "light", icon: SunIcon },
    { name: "Dark", value: "dark", icon: MoonIcon },
    { name: "Green", value: "green", icon: SparklesIcon },
    { name: "Dark Green", value: "dark green", icon: SparklesIcon },
];
</script>

<template>
    <div class="relative">
        <select
            v-model="theme"
            @change="setTheme($event.target.value)"
            class="block w-full pl-3 pr-10 py-2 text-base border rounded-md focus:outline-none focus:ring-1 bg-[var(--color-bg-secondary)] border-[var(--color-border)] text-[var(--color-text-primary)] focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)]"
        >
            <option
                v-for="option in themeOptions"
                :key="option.value"
                :value="option.value"
            >
                {{ option.name }}
            </option>
        </select>
        <div
            class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"
        >
            <component
                :is="themeOptions.find((t) => t.value === theme)?.icon"
                class="h-5 w-5 text-[var(--color-text-secondary)]"
            />
        </div>
    </div>
</template>
