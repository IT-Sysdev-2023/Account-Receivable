<!-- components/TeamCard.vue -->
<template>
    <div class="flex flex-col justify-center items-center gap-2">
        <div class="text-center">
            <h2 class="text-xl font-bold text-[var(--color-text-secondary)]/80">
                {{ member.role }}
            </h2>
        </div>
        <div
            class="group relative bg-[var(--color-primary)]/10 rounded-2xl overflow-hidden shadow-xl transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 w-full max-w-[390px]"
        >
            <div class="relative h-90 overflow-hidden">
                <img
                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                    :src="member.image"
                    :alt="member.name"
                />
                <div
                    class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent flex items-end p-6 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                ></div>
            </div>
            <div class="p-6">
                <h3 class="text-xl font-bold text-[var(--color-text-primary)]">
                    {{ member.name }}
                </h3>
                <p class="text-[var(--color-primary)] font-medium">
                    {{ member.position }}
                </p>
                <!-- <p class="mt-3 text-[var(--color-text-secondary)] text-justify">
                {{ member.bio }}
            </p> -->
                <div class="mt-4 flex flex-wrap gap-2 justify-center">
                    <span
                        v-for="skill in member.skills"
                        :key="skill"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="
                            skill === 'Active'
                                ? 'text-emerald-300 bg-emerald-700'
                                : 'text-red-300 bg-red-700'
                        "
                    >
                        <span>{{ skill }}</span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    member: {
        type: Object,
        required: true,
    },
});
</script>
