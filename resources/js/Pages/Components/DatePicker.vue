<template>
    <div class="relative w-full" ref="datepicker">
        <!-- Input field (unchanged) -->
        <div class="relative">
            <input
                type="text"
                :value="formattedDate"
                @click="toggleCalendar"
                @input="handleInput"
                :placeholder="placeholder"
                :class="[
                    'form-input cursor-pointer',
                    localMessage
                        ? '!border-red-400 !ring-2 !ring-red-500/50'
                        : validation === 'yes'
                        ? formattedDate
                            ? 'border-[var(--color-border)]'
                            : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                        : '',
                ]"
                readonly
            />
        </div>

        <transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 scale-95 translate-y-2"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95 translate-y-2"
        >
            <!-- Calendar dropdown -->
            <div
                v-if="isOpen"
                ref="calendarDropdown"
                class="absolute z-50 bg-[var(--color-bg-primary)] rounded-lg shadow-lg border border-[var(--color-border)] p-4 w-full overflow-hidden"
                :class="
                    dropUp
                        ? 'bottom-full mb-1 origin-bottom'
                        : 'mt-1 origin-top'
                "
            >
                <!-- Header -->
                <div class="flex justify-between items-center mb-4">
                    <div class="flex gap-2">
                        <button
                            type="button"
                            @click="showMonthPicker"
                            class="text-lg font-semibold hover:text-[var(--color-primary)]"
                        >
                            {{ monthNames[month] }}
                        </button>
                        <button
                            type="button"
                            @click="showYearPicker"
                            class="text-lg font-semibold hover:text-[var(--color-primary)]"
                        >
                            {{ year }}
                        </button>
                    </div>
                    <div>
                        <button
                            type="button"
                            @click="previousMonth"
                            class="p-1 rounded-full hover:bg-[var(--color-primary)]/80 hover:text-white"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 19l-7-7 7-7"
                                />
                            </svg>
                        </button>
                        <button
                            type="button"
                            @click="nextMonth"
                            class="p-1 rounded-full hover:bg-[var(--color-primary)]/80 hover:text-white"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Month Picker -->
                <div
                    v-if="showMonths"
                    data-datepicker-internal
                    class="grid grid-cols-4 gap-2 h-[210px] overflow-y-auto scrollbar-thin scrollbar-thumb-[var(--color-scrollbar-track)] scrollbar-track-[var(--color-scrollbar-track)] scrollbar-stable [scrollbar-gutter:stable] scrollbar-thumb-rounded-full"
                >
                    <button
                        v-for="(monthName, index) in monthNames"
                        :key="monthName"
                        @click="selectMonth(index)"
                        class="p-2 rounded hover:bg-[var(--color-primary)]/20 text-sm"
                        :class="{
                            'bg-[var(--color-primary)] text-white':
                                index === month,
                            'font-semibold': index === new Date().getMonth(),
                        }"
                    >
                        {{
                            monthName === "January"
                                ? "Jan"
                                : monthName === "February"
                                ? "Feb"
                                : monthName === "March"
                                ? "Mar"
                                : monthName === "April"
                                ? "Apr"
                                : monthName === "May"
                                ? "May"
                                : monthName === "June"
                                ? "Jun"
                                : monthName === "July"
                                ? "Jul"
                                : monthName === "August"
                                ? "Aug"
                                : monthName === "September"
                                ? "Sept"
                                : monthName === "October"
                                ? "Oct"
                                : monthName === "November"
                                ? "Nov"
                                : monthName === "December"
                                ? "Dec"
                                : ""
                        }}
                    </button>
                </div>

                <!-- Year Picker -->
                <div
                    v-else-if="showYears"
                    data-datepicker-internal
                    class="grid grid-cols-4 gap-2 h-[210px] overflow-y-auto scrollbar-thin scrollbar-thumb-[var(--color-scrollbar-track)] scrollbar-track-[var(--color-scrollbar-track)] scrollbar-stable [scrollbar-gutter:stable] scrollbar-thumb-rounded-full"
                >
                    <button
                        v-for="yearOption in yearRange"
                        :key="yearOption"
                        @click="selectYear(yearOption)"
                        class="p-2 rounded hover:bg-[var(--color-primary)]/20 text-xs"
                        :class="{
                            'bg-[var(--color-primary)] text-white':
                                yearOption === year,
                            'font-semibold':
                                yearOption === new Date().getFullYear(),
                        }"
                    >
                        {{ yearOption }}
                    </button>
                </div>

                <!-- Default Calendar View -->
                <template v-else>
                    <!-- Week days -->
                    <div class="grid grid-cols-7 gap-1 mb-2">
                        <div
                            v-for="day in weekDays"
                            :key="day"
                            class="text-center text-xs font-medium text-[var(--color-text-secondary)]"
                        >
                            {{ day }}
                        </div>
                    </div>

                    <!-- Days grid with transition -->
                    <div class="relative h-[210px] overflow-hidden">
                        <transition-group :name="transitionDirection" tag="div">
                            <div
                                :key="`${month}-${year}`"
                                class="grid grid-cols-7 gap-1 absolute top-0 left-0 w-full"
                            >
                                <div
                                    v-for="(day, index) in days"
                                    :key="`day-${index}-${
                                        day ? day.date : 'empty'
                                    }`"
                                    @click="selectDate(day)"
                                    class="text-center p-1 rounded-full cursor-pointer text-sm h-8 w-8 flex items-center justify-center mx-auto"
                                    :class="{
                                        'text-[var(--color-text-secondary)]/50':
                                            !day || day.month !== 'current',
                                        'hover:bg-[var(--color-primary)]/80 hover:text-white':
                                            day && day.month === 'current',
                                        'bg-[var(--color-primary)] text-white':
                                            isSelected(day),
                                        'font-semibold':
                                            day && day.month === 'current',
                                    }"
                                >
                                    {{ day ? day.date : "" }}
                                </div>
                            </div>
                        </transition-group>
                    </div>
                </template>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from "vue";

const props = defineProps({
    modelValue: {
        type: [Date, String],
        default: null,
    },
    placeholder: {
        type: String,
        default: "Select a date",
    },
    format: {
        type: String,
        default: "YYYY-MM-DD",
    },
    message: {
        type: String,
        default: "",
    },
    validation: {
        type: String,
        default: "yes",
    },
});

const emit = defineEmits(["update:modelValue"]);

const isOpen = ref(false);
const date = ref(props.modelValue ? new Date(props.modelValue) : null);
const month = ref(new Date().getMonth());
const year = ref(new Date().getFullYear());
const dropUp = ref(false);
const datepicker = ref(null);
const calendarDropdown = ref(null);
const resizeObserver = ref(null);
const transitionDirection = ref("slide-next");
const showMonths = ref(false);
const showYears = ref(false);
const localMessage = ref(props.message);

const yearRange = computed(() => {
    const currentYear = new Date().getFullYear();
    const years = [];
    for (let i = currentYear - 20; i <= currentYear + 20; i++) {
        years.push(i);
    }
    return years;
});
const weekDays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
const monthNames = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
];

// Toggle month picker
const showMonthPicker = () => {
    showMonths.value = true;
    showYears.value = false;
};

// Toggle year picker
const showYearPicker = () => {
    showYears.value = true;
    showMonths.value = false;
};

// Select month
const selectMonth = (selectedMonth) => {
    month.value = selectedMonth;
    showMonths.value = false;
};

// Select year
const selectYear = (selectedYear) => {
    year.value = selectedYear;
    showYears.value = false;
};

// Return to calendar view
const showCalendar = () => {
    showMonths.value = false;
    showYears.value = false;
};

// Format date based on the format prop
const formattedDate = computed(() => {
    if (!props.modelValue) return "";
    const d = date.value;
    const pad = (num) => num.toString().padStart(2, "0");

    return props.format
        .replace("YYYY", d.getFullYear())
        .replace("MM", pad(d.getMonth() + 1))
        .replace("DD", pad(d.getDate()));
});

// Generate days for the current month view
const days = computed(() => {
    const firstDay = new Date(year.value, month.value, 1).getDay();
    const daysInMonth = new Date(year.value, month.value + 1, 0).getDate();
    const daysInPrevMonth = new Date(year.value, month.value, 0).getDate();

    const daysArray = [];

    // Previous month days (always start from Sunday)
    const prevMonthDays = firstDay === 0 ? 0 : firstDay;
    for (let i = 0; i < prevMonthDays; i++) {
        daysArray.push({
            date: daysInPrevMonth - prevMonthDays + i + 1,
            month: "previous",
            year: month.value === 0 ? year.value - 1 : year.value,
            monthIndex: month.value === 0 ? 11 : month.value - 1,
        });
    }

    // Current month days
    for (let i = 1; i <= daysInMonth; i++) {
        daysArray.push({
            date: i,
            month: "current",
            year: year.value,
            monthIndex: month.value,
        });
    }

    // Next month days (fill up to 42 days total)
    const totalDays = prevMonthDays + daysInMonth;
    const remainingDays = 42 - totalDays;
    for (let i = 1; i <= remainingDays; i++) {
        daysArray.push({
            date: i,
            month: "next",
            year: month.value === 11 ? year.value + 1 : year.value,
            monthIndex: month.value === 11 ? 0 : month.value + 1,
        });
    }

    return daysArray;
});

// Check if a day is selected
const isSelected = (day) => {
    if (!day || !date.value) return false;
    return (
        day.date === date.value.getDate() &&
        day.monthIndex === date.value.getMonth() &&
        day.year === date.value.getFullYear()
    );
};

// Check available space and position the dropdown accordingly
const checkDropdownPosition = () => {
    if (!isOpen.value || !calendarDropdown.value || !datepicker.value) return;

    const datepickerRect = datepicker.value.getBoundingClientRect();
    const dropdownHeight = calendarDropdown.value.scrollHeight;

    // Check space below the datepicker
    const spaceBelow = window.innerHeight - datepickerRect.bottom;
    // Check space above the datepicker
    const spaceAbove = datepickerRect.top;

    // Default to drop down if there's enough space or if there's more space below
    if (spaceBelow >= dropdownHeight || spaceBelow >= spaceAbove) {
        dropUp.value = false;
    } else {
        // If not enough space below and more space above, drop up
        dropUp.value = true;
    }

    // Adjust max height if needed to prevent going off-screen
    if (dropUp.value) {
        const menuTop = datepickerRect.top - dropdownHeight;
        if (menuTop < 0) {
            calendarDropdown.value.style.maxHeight = `${
                datepickerRect.top - 10
            }px`;
            calendarDropdown.value.style.overflowY = "auto";
        }
    } else {
        const menuBottom = datepickerRect.bottom + dropdownHeight;
        if (menuBottom > window.innerHeight) {
            calendarDropdown.value.style.maxHeight = `${
                window.innerHeight - datepickerRect.bottom - 10
            }px`;
            calendarDropdown.value.style.overflowY = "auto";
        }
    }
};

// Toggle calendar visibility
const toggleCalendar = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value && !date.value) {
        // Initialize with current date if no date is selected
        const today = new Date();
        date.value = today;
        month.value = today.getMonth();
        year.value = today.getFullYear();
    }

    if (isOpen.value) {
        // Check position in next tick to ensure DOM is updated
        setTimeout(checkDropdownPosition, 0);
    }
};

// Navigate to previous month with animation
const previousMonth = () => {
    transitionDirection.value = "slide-prev";
    if (month.value === 0) {
        month.value = 11;
        year.value--;
    } else {
        month.value--;
    }
};

// Navigate to next month with animation
const nextMonth = () => {
    transitionDirection.value = "slide-next";
    if (month.value === 11) {
        month.value = 0;
        year.value++;
    } else {
        month.value++;
    }
};

// Handle input changes (if not readonly)
const handleInput = (e) => {
    // You could add parsing logic here if you want to allow manual input
    // For now, we'll keep it simple with readonly
};

// Select a date
const selectDate = (day) => {
    if (!day || day.month !== "current") return;

    const selectedDate = new Date(day.year, day.monthIndex, day.date);
    date.value = selectedDate;

    // Format as YYYY-MM-DD
    const formattedDate = `${selectedDate.getFullYear()}-${String(
        selectedDate.getMonth() + 1
    ).padStart(2, "0")}-${String(selectedDate.getDate()).padStart(2, "0")}`;
    emit("update:modelValue", formattedDate);
    isOpen.value = false;
};

watch(formattedDate, (newVal, oldVal) => {
    if (newVal !== oldVal) {
        localMessage.value = "";
    }
});

watch(
    () => props.message,
    async (newVal) => {
        localMessage.value = newVal;
    }
);

// Clear the selected date
const clearDate = () => {
    date.value = null;
    emit("update:modelValue", null);
    isOpen.value = false;
};

// Close calendar when clicking outside
const handleClickOutside = (event) => {
    const isInternalClick = event.target.closest("[data-datepicker-internal]");
    if (
        datepicker.value &&
        !datepicker.value.contains(event.target) &&
        !isInternalClick
    ) {
        isOpen.value = false;
    }
};

onMounted(() => {
    // Initialize ResizeObserver to detect changes in viewport
    resizeObserver.value = new ResizeObserver(checkDropdownPosition);
    resizeObserver.value.observe(document.body);
});

onBeforeUnmount(() => {
    if (resizeObserver.value) {
        resizeObserver.value.disconnect();
    }
    document.removeEventListener("click", handleClickOutside);
    window.removeEventListener("resize", checkDropdownPosition);
});

watch(isOpen, (newVal) => {
    if (newVal) {
        document.addEventListener("click", handleClickOutside);
        window.addEventListener("resize", checkDropdownPosition);
        // Recheck position in next tick to ensure DOM is updated
        setTimeout(checkDropdownPosition, 0);
    } else {
        document.removeEventListener("click", handleClickOutside);
        window.removeEventListener("resize", checkDropdownPosition);
    }
});

// Watch for modelValue changes from parent
watch(
    () => props.modelValue,
    (newValue) => {
        if (newValue) {
            const [y, m, d] = newValue.split("-");
            date.value = new Date(y, m - 1, d);
            month.value = date.value.getMonth();
            year.value = date.value.getFullYear();
        } else {
            date.value = null;
        }
    }
);
</script>

<style>
/* Slide transition styles */
.slide-next-enter-active,
.slide-next-leave-active,
.slide-prev-enter-active,
.slide-prev-leave-active {
    position: absolute;
    width: 100%;
    transition: transform 0.3s ease;
}

.slide-next-enter-from {
    transform: translateX(100%);
}
.slide-next-leave-to {
    transform: translateX(-100%);
}

.slide-prev-enter-from {
    transform: translateX(-100%);
}
.slide-prev-leave-to {
    transform: translateX(100%);
}
</style>
