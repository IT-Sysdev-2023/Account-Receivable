<template>
    <div class="relative w-full" ref="datepicker">
        <!-- Input field -->
        <div class="relative">
            <input
                type="text"
                :value="formattedDateTime"
                @click="toggleCalendar"
                @input="handleInput"
                :placeholder="placeholder"
                :class="[
                    'form-input cursor-pointer',
                    localMessage
                        ? '!border-red-400 !ring-2 !ring-red-500/50'
                        : validation === 'yes'
                        ? formattedDateTime
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

                    <!-- Time Picker -->
                    <div
                        class="mt-4 pt-4 border-t border-[var(--color-border)] flex items-center justify-center gap-2"
                    >
                        <div class="flex items-center gap-1">
                            <input
                                type="number"
                                v-model="hours"
                                min="0"
                                max="23"
                                @change="validateHours"
                                class="w-12 text-center p-1 rounded border border-[var(--color-border)] bg-transparent"
                            />
                            <span>:</span>
                            <input
                                type="number"
                                v-model="minutes"
                                min="0"
                                max="59"
                                @change="validateMinutes"
                                class="w-12 text-center p-1 rounded border border-[var(--color-border)] bg-transparent"
                            />
                        </div>
                        <div
                            class="flex items-center gap-1 ml-2"
                            v-if="!use24HourFormat"
                        >
                            <button
                                type="button"
                                @click="setAmPm('AM')"
                                class="px-4 py-3 rounded text-xs"
                                :class="{
                                    'bg-[var(--color-primary)] text-white':
                                        amPm === 'AM',
                                    'bg-[var(--color-bg-secondary)]':
                                        amPm !== 'AM',
                                }"
                            >
                                AM
                            </button>
                            <button
                                type="button"
                                @click="setAmPm('PM')"
                                class="px-4 py-3 rounded text-xs"
                                :class="{
                                    'bg-[var(--color-primary)] text-white':
                                        amPm === 'PM',
                                    'bg-[var(--color-bg-secondary)]':
                                        amPm !== 'PM',
                                }"
                            >
                                PM
                            </button>
                        </div>
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
        default: "Select a date and time",
    },
    format: {
        type: String,
        default: "YYYY-MM-DD HH:mm",
    },
    message: {
        type: String,
        default: "",
    },
    validation: {
        type: String,
        default: "yes",
    },
    use24HourFormat: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue"]);

const isOpen = ref(false);
const date = ref(props.modelValue ? new Date(props.modelValue) : null);
const month = ref(new Date().getMonth());
const year = ref(new Date().getFullYear());
const hours = ref(
    props.modelValue ? new Date(props.modelValue).getHours() : 12
);
const minutes = ref(
    props.modelValue ? new Date(props.modelValue).getMinutes() : 0
);
const amPm = ref(hours.value >= 12 ? "PM" : "AM");
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

// Format date and time based on the format prop
const formattedDateTime = computed(() => {
    if (!date.value) return "";
    const d = date.value;
    const pad = (num) => num.toString().padStart(2, "0");

    let formattedTime;
    if (props.use24HourFormat) {
        formattedTime = `${pad(hours.value)}:${pad(minutes.value)}`;
    } else {
        const displayHours = hours.value % 12 === 0 ? 12 : hours.value % 12;
        formattedTime = `${pad(displayHours)}:${pad(minutes.value)} ${
            amPm.value
        }`;
    }

    return props.format
        .replace("YYYY", d.getFullYear())
        .replace("MM", pad(d.getMonth() + 1))
        .replace("DD", pad(d.getDate()))
        .replace("HH", pad(hours.value))
        .replace("mm", pad(minutes.value))
        .replace("hh", pad(hours.value % 12 === 0 ? 12 : hours.value % 12))
        .replace("TT", amPm.value)
        .replace("tt", amPm.value.toLowerCase());
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

// Toggle calendar visibility
const toggleCalendar = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value && !date.value) {
        setCurrentDateTime();
    }

    if (isOpen.value) {
        setTimeout(checkDropdownPosition, 0);
    }
};

// Set current date and time
const setCurrentDateTime = () => {
    const now = new Date();
    date.value = now;
    month.value = now.getMonth();
    year.value = now.getFullYear();
    hours.value = now.getHours();
    minutes.value = now.getMinutes();
    amPm.value = hours.value >= 12 ? "PM" : "AM";
};

// Clear date and time
const clearDateTime = () => {
    date.value = null;
    hours.value = 12;
    minutes.value = 0;
    amPm.value = "AM";
    isOpen.value = false;
    emit("update:modelValue", null);
};

// Apply the selected date and time
const applyDateTime = () => {
    if (!date.value) {
        setCurrentDateTime();
    }

    const selectedDate = new Date(
        date.value.getFullYear(),
        date.value.getMonth(),
        date.value.getDate(),
        props.use24HourFormat
            ? hours.value
            : amPm.value === "PM"
            ? (hours.value % 12) + 12
            : hours.value % 12,
        minutes.value
    );

    emit("update:modelValue", selectedDate.toISOString());
    isOpen.value = false;
};

// Select a date
const selectDate = (day) => {
    if (!day || day.month !== "current") return;

    date.value = new Date(day.year, day.monthIndex, day.date);
    applyDateTime();
};

// Validate hours input
const validateHours = () => {
    if (props.use24HourFormat) {
        hours.value = Math.min(23, Math.max(0, parseInt(hours.value) || 0));
    } else {
        hours.value = Math.min(12, Math.max(1, parseInt(hours.value) || 12));
    }
};

// Validate minutes input
const validateMinutes = () => {
    minutes.value = Math.min(59, Math.max(0, parseInt(minutes.value) || 0));
};

// Set AM/PM
const setAmPm = (value) => {
    amPm.value = value;
};

// Check dropdown position (same as before)
const checkDropdownPosition = () => {
    if (!isOpen.value || !calendarDropdown.value || !datepicker.value) return;

    const datepickerRect = datepicker.value.getBoundingClientRect();
    const dropdownHeight = calendarDropdown.value.scrollHeight;

    const spaceBelow = window.innerHeight - datepickerRect.bottom;
    const spaceAbove = datepickerRect.top;

    if (spaceBelow >= dropdownHeight || spaceBelow >= spaceAbove) {
        dropUp.value = false;
    } else {
        dropUp.value = true;
    }

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

// Navigation methods (same as before)
const previousMonth = () => {
    transitionDirection.value = "slide-prev";
    if (month.value === 0) {
        month.value = 11;
        year.value--;
    } else {
        month.value--;
    }
};

const nextMonth = () => {
    transitionDirection.value = "slide-next";
    if (month.value === 11) {
        month.value = 0;
        year.value++;
    } else {
        month.value++;
    }
};

const showMonthPicker = () => {
    showMonths.value = true;
    showYears.value = false;
};

const showYearPicker = () => {
    showYears.value = true;
    showMonths.value = false;
};

const selectMonth = (selectedMonth) => {
    month.value = selectedMonth;
    showMonths.value = false;
};

const selectYear = (selectedYear) => {
    year.value = selectedYear;
    showYears.value = false;
};

// Watch for modelValue changes from parent
watch(
    () => props.modelValue,
    (newValue) => {
        if (newValue) {
            const d = new Date(newValue);
            date.value = d;
            month.value = d.getMonth();
            year.value = d.getFullYear();
            hours.value = d.getHours();
            minutes.value = d.getMinutes();
            amPm.value = hours.value >= 12 ? "PM" : "AM";
        } else {
            date.value = null;
            hours.value = 12;
            minutes.value = 0;
            amPm.value = "AM";
        }
    },
    { immediate: true }
);

// Other watchers and lifecycle methods (same as before)
watch(formattedDateTime, (newVal, oldVal) => {
    if (newVal !== oldVal) {
        localMessage.value = "";
    }
});

watch(
    () => props.message,
    (newVal) => {
        localMessage.value = newVal;
    }
);

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
        setTimeout(checkDropdownPosition, 0);
    } else {
        document.removeEventListener("click", handleClickOutside);
        window.removeEventListener("resize", checkDropdownPosition);
    }
});
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
