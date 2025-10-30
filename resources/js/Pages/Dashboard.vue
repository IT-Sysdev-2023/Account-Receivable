<template>
    <Head title="Dashboard" />
    <ToastAlert :show="showToast" :message="toastMessage" />
    <ToastAlertWarning :show="showWToast" :message="toastWMessage" />
    <!-- Main Dashboard Grid -->
    <div class="dashboard-grid w-full">
        <!-- Top Stats Cards -->
        <div class="stats-grid w-full">
            <!-- SI Card -->
            <DashboardCard
                iconType="invoice"
                label="Sales Invoice Total Paid"
                prefix="Php"
                :values="{
                    'Last 7 days': ledgerTotals.sales_invoice.last_7_days,
                    'Last 30 days': ledgerTotals.sales_invoice.last_30_days,
                    'Overall Total': ledgerTotals.sales_invoice.overall,
                }"
                :lastUpdated="ledgerTotals.sales_invoice.last_updated"
                :isCurrency="true"
                color="lime"
            />

            <!-- CI Card -->
            <DashboardCard
                iconType="invoice"
                label="Charge Invoice Total Paid"
                prefix="Php"
                :values="{
                    'Last 7 days': ledgerTotals.charge_invoice.last_7_days,
                    'Last 30 days': ledgerTotals.charge_invoice.last_30_days,
                    'Overall Total': ledgerTotals.charge_invoice.overall,
                }"
                :lastUpdated="ledgerTotals.charge_invoice.last_updated"
                :isCurrency="true"
                color="lime"
            />

            <!-- Payment Card -->
            <DashboardCard
                iconType="payment"
                label="Payment Total Paid"
                prefix="Php"
                :values="{
                    'Last 7 days': ledgerTotals.payment.last_7_days,
                    'Last 30 days': ledgerTotals.payment.last_30_days,
                    'Overall Total': ledgerTotals.payment.overall,
                }"
                :lastUpdated="ledgerTotals.payment.last_updated"
                :isCurrency="true"
                color="lime"
            />

            <!-- Balance Card -->
            <DashboardCard
                iconType="balance"
                label="Beginning Balance Total Paid"
                prefix="Php"
                :values="{
                    'Last 7 days': ledgerTotals.bg.last_7_days,
                    'Last 30 days': ledgerTotals.bg.last_30_days,
                    'Overall Total': ledgerTotals.bg.overall,
                }"
                :lastUpdated="ledgerTotals.bg.last_updated"
                :isCurrency="true"
                color="lime"
            />
        </div>

        <!-- Middle Section - Floating Checks -->
        <div class="checks-grid">
            <!-- Floating Post Dated Check -->
            <DashboardCard
                iconType="check"
                label="Floating Post Dated Check"
                prefix="Php"
                :values="{
                    'Last 7 days':
                        props.floatingTotals.check.post_dated.last_7_days,
                    'Last 30 days':
                        props.floatingTotals.check.post_dated.last_30_days,
                    'Overall Total':
                        props.floatingTotals.check.post_dated.overall,
                }"
                :isCurrency="true"
                color="emerald"
            />

            <!-- Floating Dated Check -->
            <DashboardCard
                iconType="check"
                label="Floating Dated Check"
                prefix="Php"
                :values="{
                    'Last 7 days': props.floatingTotals.check.dated.last_7_days,
                    'Last 30 days':
                        props.floatingTotals.check.dated.last_30_days,
                    'Overall Total': props.floatingTotals.check.dated.overall,
                }"
                :isCurrency="true"
                color="emerald"
            />

            <!-- WHT  -->
            <DashboardCard
                iconType="check"
                label="Floating With Holding Tax"
                prefix="Php"
                :values="{
                    'Last 7 days': props.floatingTotals.creditable.last_7_days,
                    'Last 30 days':
                        props.floatingTotals.creditable.last_30_days,
                    'Overall Total': props.floatingTotals.creditable.overall,
                }"
                :isCurrency="true"
                color="emerald"
            />
        </div>

        <!-- Charts Section -->
        <div class="charts-grid">
            <!-- Bar Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="text-sm sm:text-base md:text-lg">
                        Total Invoices Count Overview
                    </h3>
                    <!-- Custom Dropdown -->
                    <div
                        class="relative z-20"
                        @mouseenter="dropdownOpen = true"
                        @mouseleave="dropdownOpen = false"
                    >
                        <button
                            class="flex items-center justify-between w-full bg-[var(--color-bg-secondary)]/10 text-[var(--color-text-primary)] text-xs rounded px-2 py-1 border border-[var(--color-border)] hover:bg-[var(--color-bg-secondary)]/15 focus:outline-none focus:ring-1 focus:ring-[var(--color-border)]/20 transition-colors duration-150"
                        >
                            <span>{{ selectedPeriod }}</span>
                            <svg-icon
                                type="mdi"
                                :path="
                                    dropdownOpen ? mdiChevronUp : mdiChevronDown
                                "
                                class="w-3 h-3 sm:w-4 sm:h-4 ml-1 sm:ml-2"
                            >
                            </svg-icon>
                        </button>

                        <transition
                            enter-active-class="transition duration-100 ease-out"
                            enter-from-class="transform scale-95 opacity-0"
                            enter-to-class="transform scale-100 opacity-100"
                            leave-active-class="transition duration-75 ease-in"
                            leave-from-class="transform scale-100 opacity-100"
                            leave-to-class="transform scale-95 opacity-0"
                        >
                            <ul
                                v-show="dropdownOpen"
                                class="absolute mt-1 w-full border border-[var(--color-border)] rounded shadow-lg bg-[var(--color-bg-secondary)]/90 backdrop-blur-sm z-30"
                            >
                                <li
                                    class="px-2 py-1 sm:px-3 sm:py-2 text-xs text-[var(--color-text-primary)] hover:bg-[var(--color-primary)]/20 cursor-pointer transition-colors duration-150 border-b border-[var(--color-border)]/20"
                                    @click="selectPeriod('Last 7 days')"
                                >
                                    Last 7 days
                                </li>
                                <li
                                    class="px-2 py-1 sm:px-3 sm:py-2 text-xs text-[var(--color-text-primary)] hover:bg-[var(--color-primary)]/20 cursor-pointer transition-colors duration-150 border-b border-[var(--color-border)]/20"
                                    @click="selectPeriod('Last 30 days')"
                                >
                                    Last 30 days
                                </li>
                                <li
                                    class="px-2 py-1 sm:px-3 sm:py-2 text-xs text-[var(--color-text-primary)] hover:bg-[var(--color-primary)]/20 cursor-pointer transition-colors duration-150"
                                    @click="selectPeriod('Overall Total')"
                                >
                                    Overall Total
                                </li>
                            </ul>
                        </transition>
                    </div>
                </div>
                <div
                    class="chart-placeholder bg-[var(--color-primary)]/20 backdrop-blur-sm"
                >
                    <canvas ref="chartCanvas"></canvas>
                </div>
            </div>

            <!-- Pie graph -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="text-sm sm:text-base md:text-lg">
                        Total Item Sales Count Overview
                    </h3>
                    <!-- Custom Dropdown -->
                    <div
                        class="relative z-20"
                        @mouseenter="dropdownOpenPie = true"
                        @mouseleave="dropdownOpenPie = false"
                    >
                        <button
                            class="flex items-center justify-between w-full bg-[var(--color-bg-secondary)]/10 text-[var(--color-text-primary)] text-xs rounded px-2 py-1 border border-[var(--color-border)] hover:bg-[var(--color-bg-secondary)]/15 focus:outline-none focus:ring-1 focus:ring-[var(--color-border)]/20 transition-colors duration-150"
                        >
                            <span>{{ selectedPeriodPie }}</span>
                            <svg-icon
                                type="mdi"
                                :path="
                                    dropdownOpenPie
                                        ? mdiChevronUp
                                        : mdiChevronDown
                                "
                                class="w-3 h-3 sm:w-4 sm:h-4 ml-1 sm:ml-2"
                            >
                            </svg-icon>
                        </button>

                        <transition
                            enter-active-class="transition duration-100 ease-out"
                            enter-from-class="transform scale-95 opacity-0"
                            enter-to-class="transform scale-100 opacity-100"
                            leave-active-class="transition duration-75 ease-in"
                            leave-from-class="transform scale-100 opacity-100"
                            leave-to-class="transform scale-95 opacity-0"
                        >
                            <ul
                                v-show="dropdownOpenPie"
                                class="absolute mt-1 w-full border border-[var(--color-border)] rounded shadow-lg bg-[var(--color-bg-secondary)]/90 backdrop-blur-sm z-30"
                            >
                                <li
                                    class="px-2 py-1 sm:px-3 sm:py-2 text-xs text-[var(--color-text-primary)] hover:bg-[var(--color-primary)]/20 cursor-pointer transition-colors duration-150 border-b border-[var(--color-border)]/20"
                                    @click="selectPeriodPie('Last 7 days')"
                                >
                                    Last 7 days
                                </li>
                                <li
                                    class="px-2 py-1 sm:px-3 sm:py-2 text-xs text-[var(--color-text-primary)] hover:bg-[var(--color-primary)]/20 cursor-pointer transition-colors duration-150 border-b border-[var(--color-border)]/20"
                                    @click="selectPeriodPie('Last 30 days')"
                                >
                                    Last 30 days
                                </li>
                                <li
                                    class="px-2 py-1 sm:px-3 sm:py-2 text-xs text-[var(--color-text-primary)] hover:bg-[var(--color-primary)]/20 cursor-pointer transition-colors duration-150"
                                    @click="selectPeriodPie('Overall Total')"
                                >
                                    Overall Total
                                </li>
                            </ul>
                        </transition>
                    </div>
                </div>
                <div
                    class="pie-placeholder bg-[var(--color-primary)]/20 backdrop-blur-sm"
                >
                    <div
                        v-if="!chartInstancePie?.data?.labels?.length"
                        class="absolute inset-0 flex items-center justify-center"
                    >
                        <span class="text-[var(--color-text-secondary)]"
                            >No data available</span
                        >
                    </div>
                    <canvas ref="pieCanvas"></canvas>
                </div>
            </div>

            <!-- Table Chart -->
            <div class="chart-card col-span-1 md:col-span-2">
                <div class="chart-header">
                    <h3 class="text-sm sm:text-base md:text-lg">
                        Customer Account Overview
                    </h3>
                    <!-- Custom Dropdown -->
                    <div
                        class="relative z-20"
                        @mouseenter="dropdownOpenTable = true"
                        @mouseleave="dropdownOpenTable = false"
                    >
                        <button
                            class="flex items-center justify-between w-full bg-[var(--color-bg-secondary)]/10 text-[var(--color-text-primary)] text-xs rounded px-2 py-1 border border-[var(--color-border)] hover:bg-[var(--color-bg-secondary)]/15 focus:outline-none focus:ring-1 focus:ring-[var(--color-border)]/20 transition-colors duration-150"
                        >
                            <span>{{ selectedPeriodTable }}</span>
                            <svg-icon
                                type="mdi"
                                :path="
                                    dropdownOpenTable
                                        ? mdiChevronUp
                                        : mdiChevronDown
                                "
                                class="w-3 h-3 sm:w-4 sm:h-4 ml-1 sm:ml-2"
                            >
                            </svg-icon>
                        </button>

                        <transition
                            enter-active-class="transition duration-100 ease-out"
                            enter-from-class="transform scale-95 opacity-0"
                            enter-to-class="transform scale-100 opacity-100"
                            leave-active-class="transition duration-75 ease-in"
                            leave-from-class="transform scale-100 opacity-100"
                            leave-to-class="transform scale-95 opacity-0"
                        >
                            <ul
                                v-show="dropdownOpenTable"
                                class="absolute mt-1 w-full border border-[var(--color-border)] rounded shadow-lg bg-[var(--color-bg-secondary)]/90 backdrop-blur-sm z-30"
                            >
                                <li
                                    class="px-2 py-1 sm:px-3 sm:py-2 text-xs text-[var(--color-text-primary)] hover:bg-[var(--color-primary)]/20 cursor-pointer transition-colors duration-150 border-b border-[var(--color-border)]/20"
                                    @click="selectPeriodTable('Last 7 days')"
                                >
                                    Last 7 days
                                </li>
                                <li
                                    class="px-2 py-1 sm:px-3 sm:py-2 text-xs text-[var(--color-text-primary)] hover:bg-[var(--color-primary)]/20 cursor-pointer transition-colors duration-150 border-b border-[var(--color-border)]/20"
                                    @click="selectPeriodTable('Last 30 days')"
                                >
                                    Last 30 days
                                </li>
                                <li
                                    class="px-2 py-1 sm:px-3 sm:py-2 text-xs text-[var(--color-text-primary)] hover:bg-[var(--color-primary)]/20 cursor-pointer transition-colors duration-150"
                                    @click="selectPeriodTable('Overall Total')"
                                >
                                    Overall Total
                                </li>
                            </ul>
                        </transition>
                    </div>
                </div>
                <div class="relative table-placeholder">
                    <div
                        class="w-full rounded-xl overflow-hidden bg-[var(--color-primary)]/20 backdrop-blur-sm"
                    >
                        <div class="sticky top-0 z-10">
                            <table
                                class="w-full text-[var(--color-text-primary)]"
                            >
                                <thead>
                                    <tr>
                                        <th
                                            class="text-center py-2 sm:py-3 px-3 sm:px-6 font-medium text-xs sm:text-sm tracking-wide w-[30%]"
                                        >
                                            <div
                                                class="flex items-center justify-center space-x-1 sm:space-x-2"
                                            >
                                                <svg-icon
                                                    type="mdi"
                                                    :path="mdiAccountGroup"
                                                    class="w-3 h-3 sm:w-4 sm:h-4"
                                                />
                                                <span>CUSTOMER NAME</span>
                                            </div>
                                        </th>
                                        <th
                                            class="text-center py-2 sm:py-3 px-3 sm:px-6 font-medium text-xs sm:text-sm tracking-wide w-[20%]"
                                        >
                                            <div
                                                class="flex items-center justify-center space-x-1 sm:space-x-2"
                                            >
                                                <svg-icon
                                                    type="mdi"
                                                    :path="mdiCash"
                                                    class="w-3 h-3 sm:w-4 sm:h-4"
                                                />
                                                <span>AR AMOUNT</span>
                                            </div>
                                        </th>
                                        <th
                                            class="text-center py-2 sm:py-3 px-3 sm:px-6 font-medium text-xs sm:text-sm tracking-wide w-[20%]"
                                        >
                                            <div
                                                class="flex items-center justify-center space-x-1 sm:space-x-2"
                                            >
                                                <svg-icon
                                                    type="mdi"
                                                    :path="mdiCashRemove"
                                                    class="w-3 h-3 sm:w-4 sm:h-4"
                                                />
                                                <span>OUTSTANDING BALANCE</span>
                                            </div>
                                        </th>
                                        <th
                                            class="text-center py-2 sm:py-3 px-3 sm:px-6 font-medium text-xs sm:text-sm tracking-wide w-[30%]"
                                        >
                                            <div
                                                class="flex items-center justify-center space-x-1 sm:space-x-2"
                                            >
                                                <svg-icon
                                                    type="mdi"
                                                    :path="mdiProgressClock"
                                                    class="w-3 h-3 sm:w-4 sm:h-4"
                                                />
                                                <span>PAYMENT PROGRESS</span>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="relative overflow-hidden">
                            <div
                                class="md:max-h-105 sm:max-h-80 max-h-70 overflow-y-auto relative scrollbar-thin scrollbar-thumb-[var(--color-scrollbar-track)] scrollbar-track-[var(--color-primary)]/20 scrollbar-stable [scrollbar-gutter:stable] scrollbar-thumb-rounded-full"
                            >
                                <table class="w-full text-white">
                                    <!-- Loading State -->
                                    <tbody v-if="isLoading">
                                        <tr>
                                            <td
                                                colspan="6"
                                                class="text-center py-8 h-109"
                                            >
                                                <div
                                                    class="flex justify-center items-center"
                                                >
                                                    <svg
                                                        width="30"
                                                        height="30"
                                                        viewBox="0 0 24 24"
                                                        fill="var(--color-icon)"
                                                    >
                                                        <rect
                                                            class="spinner_jCIR"
                                                            x="1"
                                                            y="6"
                                                            width="2.8"
                                                            height="12"
                                                        />
                                                        <rect
                                                            class="spinner_jCIR spinner_upm8"
                                                            x="5.8"
                                                            y="6"
                                                            width="2.8"
                                                            height="12"
                                                        />
                                                        <rect
                                                            class="spinner_jCIR spinner_2eL5"
                                                            x="10.6"
                                                            y="6"
                                                            width="2.8"
                                                            height="12"
                                                        />
                                                        <rect
                                                            class="spinner_jCIR spinner_Rp9l"
                                                            x="15.4"
                                                            y="6"
                                                            width="2.8"
                                                            height="12"
                                                        />
                                                        <rect
                                                            class="spinner_jCIR spinner_dy3W"
                                                            x="20.2"
                                                            y="6"
                                                            width="2.8"
                                                            height="12"
                                                        />
                                                    </svg>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr
                                            v-for="(
                                                customer, index
                                            ) in customers"
                                            :key="customer.name"
                                            class="hover:bg-[var(--color-icon)]/20 transition-all duration-300 ease-out text-[var(--color-text-primary)]"
                                            :style="`animation: fadeIn 0.5s ease-out ${
                                                index * 0.1
                                            }s both`"
                                        >
                                            <td
                                                class="py-3 sm:py-4 px-3 sm:px-6 w-[30%]"
                                            >
                                                <div
                                                    class="flex items-center space-x-2 sm:space-x-3"
                                                >
                                                    <div
                                                        class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-[var(--color-bg-avatar)] flex items-center justify-center"
                                                    >
                                                        <span
                                                            class="text-white text-xs font-medium"
                                                            >{{
                                                                getFirstValidChar(
                                                                    customer.customer_name
                                                                )
                                                            }}</span
                                                        >
                                                    </div>
                                                    <div>
                                                        <div
                                                            class="font-medium text-xs sm:text-sm"
                                                        >
                                                            {{
                                                                customer.customer_name
                                                            }}
                                                        </div>
                                                        <div class="text-xs">
                                                            {{
                                                                customer.customer_code
                                                            }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td
                                                class="py-3 sm:py-4 px-3 sm:px-6 text-right w-[20%] text-xs sm:text-sm"
                                            >
                                                <div>
                                                    <AnimatedCurrency
                                                        :value="
                                                            customer.total_amount
                                                        "
                                                    />
                                                </div>
                                            </td>
                                            <td
                                                class="py-3 sm:py-4 px-3 sm:px-6 text-right w-[20%] text-xs sm:text-sm"
                                            >
                                                <div
                                                    class="font-bold"
                                                    :class="{
                                                        'text-green-500':
                                                            getProgress(
                                                                customer.total_amount,
                                                                customer.current_balance
                                                            ) >= 80,
                                                        'text-amber-500':
                                                            getProgress(
                                                                customer.total_amount,
                                                                customer.current_balance
                                                            ) >= 50 &&
                                                            getProgress(
                                                                customer.total_amount,
                                                                customer.current_balance
                                                            ) < 80,
                                                        'text-red-500':
                                                            getProgress(
                                                                customer.total_amount,
                                                                customer.current_balance
                                                            ) < 50,
                                                    }"
                                                >
                                                    <AnimatedCurrency
                                                        :value="
                                                            customer.current_balance
                                                        "
                                                    />
                                                </div>
                                                <div
                                                    v-if="
                                                        customer.total_adjusted >
                                                            0 ||
                                                        customer.total_adjusted <
                                                            0
                                                    "
                                                    class="text-xs"
                                                >
                                                    <AnimatedCurrency
                                                        :value="
                                                            customer.total_adjusted
                                                        "
                                                    />
                                                    Adjusted
                                                </div>
                                                <div
                                                    v-if="
                                                        customer.total_shrinkage >
                                                            0 ||
                                                        customer.total_shrinkage <
                                                            0
                                                    "
                                                    class="text-xs"
                                                >
                                                    <AnimatedCurrency
                                                        :value="
                                                            customer.total_shrinkage
                                                        "
                                                    />
                                                    Shrinkage
                                                </div>
                                                <div
                                                    v-if="
                                                        customer.total_overage >
                                                            0 ||
                                                        customer.total_overage <
                                                            0
                                                    "
                                                    class="text-xs"
                                                >
                                                    <AnimatedCurrency
                                                        :value="
                                                            customer.total_overage
                                                        "
                                                    />
                                                    Overage
                                                </div>
                                                <div
                                                    v-if="
                                                        customer.total_return >
                                                            0 ||
                                                        customer.total_return <
                                                            0
                                                    "
                                                    class="text-xs"
                                                >
                                                    <AnimatedCurrency
                                                        :value="
                                                            customer.total_return
                                                        "
                                                    />
                                                    Returned
                                                </div>
                                                <div class="text-xs">
                                                    <AnimatedCurrency
                                                        :value="
                                                            customer.total_amount_paid
                                                        "
                                                    />
                                                    Paid
                                                </div>
                                            </td>
                                            <td
                                                class="py-3 sm:py-4 px-3 sm:px-6 w-[30%]"
                                            >
                                                <div
                                                    class="flex items-center justify-end space-x-2 sm:space-x-3"
                                                >
                                                    <div
                                                        class="text-right min-w-[40px] text-xs sm:text-sm"
                                                    >
                                                        <animated-number
                                                            :value="
                                                                getProgress(
                                                                    customer.total_amount,
                                                                    customer.current_balance
                                                                )
                                                            "
                                                            :duration="1500"
                                                            :delay="index * 200"
                                                            class="font-medium"
                                                            :class="{
                                                                'text-green-500':
                                                                    getProgress(
                                                                        customer.total_amount,
                                                                        customer.current_balance
                                                                    ) >= 80,
                                                                'text-amber-500':
                                                                    getProgress(
                                                                        customer.total_amount,
                                                                        customer.current_balance
                                                                    ) >= 50 &&
                                                                    getProgress(
                                                                        customer.total_amount,
                                                                        customer.current_balance
                                                                    ) < 80,
                                                                'text-red-500':
                                                                    getProgress(
                                                                        customer.total_amount,
                                                                        customer.current_balance
                                                                    ) < 50,
                                                            }"
                                                        />
                                                    </div>
                                                    <div
                                                        class="w-3/4 bg-white/10 rounded-full h-2.5 overflow-hidden"
                                                    >
                                                        <div
                                                            class="h-full rounded-full"
                                                            :class="
                                                                getProgressColor(
                                                                    getProgress(
                                                                        customer.total_amount,
                                                                        customer.current_balance
                                                                    )
                                                                )
                                                            "
                                                            :style="{
                                                                width: '0%',
                                                                animation: `progressGrow 1.5s ease-out forwards`,
                                                                'animation-delay': `${
                                                                    index * 0.2
                                                                }s`,
                                                                '--progress-width': `${getProgress(
                                                                    customer.total_amount,
                                                                    customer.current_balance
                                                                )}%`,
                                                            }"
                                                        ></div>
                                                    </div>
                                                </div>
                                                <div
                                                    class="text-right text-xs mt-1"
                                                >
                                                    <template
                                                        v-if="
                                                            getProgress(
                                                                customer.total_amount,
                                                                customer.current_balance
                                                            ) === 100
                                                        "
                                                    >
                                                        Paid in full
                                                    </template>
                                                    <template
                                                        v-else-if="
                                                            getProgress(
                                                                customer.total_amount,
                                                                customer.current_balance
                                                            ) >= 80
                                                        "
                                                    >
                                                        Almost complete
                                                    </template>
                                                    <template
                                                        v-else-if="
                                                            getProgress(
                                                                customer.total_amount,
                                                                customer.current_balance
                                                            ) >= 50
                                                        "
                                                    >
                                                        Partially paid
                                                    </template>
                                                    <template v-else>
                                                        Payment pending
                                                    </template>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Empty State -->
                                        <tr
                                            v-if="
                                                !isLoading &&
                                                customers.length === 0
                                            "
                                        >
                                            <td
                                                colspan="6"
                                                class="px-5 py-6 text-center"
                                            >
                                                <div
                                                    class="flex flex-col items-center justify-center text-[var(--color-text-primary)]"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="h-10 w-10 mb-2"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                        />
                                                    </svg>
                                                    <p class="font-medium">
                                                        No data found
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Pagination Links -->
                <div
                    class="flex flex-col sm:flex-row justify-between items-center gap-2 py-3 sm:py-4 px-2"
                >
                    <!-- Page Info -->
                    <p
                        class="text-xs sm:text-sm text-[var(--color-text-primary)] mt-1 sm:mt-0"
                    >
                        Page
                        <span
                            class="font-bold text-[var(--color-text-primary)]"
                        >
                            {{ pagination.current_page }}
                        </span>
                        of
                        <span
                            class="font-bold text-[var(--color-text-primary)]"
                        >
                            {{ pagination.last_page }}
                        </span>
                    </p>

                    <!-- Pagination Buttons -->
                    <div class="flex items-center gap-1 overflow-hidden">
                        <!-- Previous Page -->
                        <button
                            @click="goToPage(pagination.current_page - 1)"
                            :disabled="
                                pagination.current_page === 1 || isLoading
                            "
                            class="w-7 h-7 sm:w-8 sm:h-8 text-xs sm:text-sm grid place-items-center rounded-lg text-[var(--color-text-primary)] transition-all duration-200"
                            :class="[
                                pagination.current_page > 1
                                    ? 'hover:bg-[var(--color-primary-hover)] hover:text-[var(--color-text-primary)] cursor-pointer'
                                    : 'opacity-50 cursor-not-allowed',
                                'bg-[var(--color-primary)]',
                            ]"
                        >
                            ‹
                        </button>

                        <!-- Current Page -->
                        <span
                            class="w-7 h-7 sm:w-8 sm:h-8 text-xs sm:text-sm grid place-items-center rounded-lg bg-[var(--color-primary-hover)] text-[var(--color-text-primary)] font-bold cursor-default"
                        >
                            {{ pagination.current_page }}
                        </span>

                        <!-- Next Page -->
                        <button
                            @click="goToPage(pagination.current_page + 1)"
                            :disabled="
                                pagination.current_page ===
                                    pagination.last_page || isLoading
                            "
                            class="w-7 h-7 sm:w-8 sm:h-8 text-xs sm:text-sm grid place-items-center rounded-lg text-[var(--color-text-primary)] transition-all duration-200"
                            :class="[
                                pagination.current_page < pagination.last_page
                                    ? 'hover:bg-[var(--color-primary-hover)] hover:text-[var(--color-text-primary)] cursor-pointer'
                                    : 'opacity-50 cursor-not-allowed',
                                'bg-[var(--color-primary)]',
                            ]"
                        >
                            ›
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, defineComponent, h, onMounted, ref, watch } from "vue";
import DashboardCard from "./Components/DashboardCard.vue";
import Chart from "chart.js/auto";
import SvgIcon from "@jamescoyle/vue-icon";
import {
    mdiAccountGroup,
    mdiCash,
    mdiCashRemove,
    mdiChevronDown,
    mdiChevronUp,
    mdiProgressCheck,
    mdiProgressClock,
} from "@mdi/js";
import { route } from "../../../vendor/tightenco/ziggy/src/js";
import { usePage } from "@inertiajs/vue3";
import ToastAlertWarning from "./Components/ToastAlertWarning.vue";
import ToastAlert from "./Components/ToastAlert.vue";

const props = defineProps({
    ledgerTotals: Object,
    floatingTotals: Object,
});

const getCssVar = (name) => {
    return getComputedStyle(document.documentElement)
        .getPropertyValue(name)
        .trim();
};

function hexToRgba(hex, opacity) {
    hex = hex.replace("#", "");
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    return `rgba(${r}, ${g}, ${b}, ${opacity})`;
}

//BAR CHART
//CUSTOM DROPDOWN
const selectedPeriod = ref("Last 7 days");

const dropdownOpen = ref(false);

const selectPeriod = (period) => {
    selectedPeriod.value = period;
    dropdownOpen.value = false;
};

const chartCanvas = ref(null);
let chartInstance = null;

const fetchChartData = async (period) => {
    let apiPeriod;
    switch (period) {
        case "Last 7 days":
            apiPeriod = "7days";
            break;
        case "Last 30 days":
            apiPeriod = "4weeks";
            break;
        case "Overall Total":
            apiPeriod = "months";
            break;
    }

    const response = await axios.get(
        route("getInvoiceChartData", { period: apiPeriod })
    );
    return response.data;
};

const updateChart = (chartData) => {
    if (!chartInstance) return;

    chartInstance.data.labels = chartData.labels;
    chartInstance.data.datasets[0].data = chartData.datasets[0].data;
    chartInstance.data.datasets[1].data = chartData.datasets[1].data;

    // Auto-adjust y-axis max
    const allValues = [
        ...chartData.datasets[0].data,
        ...chartData.datasets[1].data,
    ];
    const maxValue = Math.max(...allValues);
    chartInstance.options.scales.y.max = Math.ceil(maxValue / 10) * 10; // Round up to nearest 10

    chartInstance.update();
};

onMounted(async () => {
    if (!chartCanvas.value) return;
    let delayed;
    // Initial chart setup with empty data
    chartInstance = new Chart(chartCanvas.value, {
        type: "bar",
        data: {
            labels: [],
            datasets: [
                {
                    label: "Sales Invoice",
                    data: [],
                    backgroundColor: "rgba(16,185,129,0.5)",
                    borderColor: "rgba(16,185,129,1)",
                    borderWidth: 2,
                    borderRadius: Number.MAX_VALUE,
                    borderSkipped: false,
                },
                {
                    label: "Charge Invoice",
                    data: [],
                    backgroundColor: "rgba(20,184,166, 0.5)",
                    borderColor: "rgba(20,184,166, 1)",
                    borderWidth: 2,
                    borderRadius: Number.MAX_VALUE,
                    borderSkipped: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                onComplete: () => {
                    delayed = true;
                },
                delay: (context) => {
                    let delay = 0;
                    if (
                        context.type === "data" &&
                        context.mode === "default" &&
                        !delayed
                    ) {
                        delay =
                            context.dataIndex * 600 +
                            context.datasetIndex * 200;
                    }
                    return delay;
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: () =>
                            hexToRgba(getCssVar("--color-text-primary"), 0.1),
                    },
                    ticks: {
                        color: getCssVar("--color-text-primary"),
                        // stepSize: 5,
                        autoSkip: true,
                    },
                },
                x: {
                    grid: {
                        color: () =>
                            hexToRgba(getCssVar("--color-text-primary"), 0.1),
                    },
                    ticks: {
                        color: getCssVar("--color-text-primary"),
                    },
                },
            },
            plugins: {
                legend: {
                    labels: {
                        color: getCssVar("--color-text-primary"),
                    },
                },
            },
        },
    });

    // Load initial data
    const initialData = await fetchChartData(selectedPeriod.value);
    updateChart(initialData);
});

// Watch for period changes
watch(selectedPeriod, async (newPeriod) => {
    const newData = await fetchChartData(newPeriod);
    updateChart(newData);
});

//PIE CHART
//CUSTOM DROPDOWN
const selectedPeriodPie = ref("Last 7 days");
const dropdownOpenPie = ref(false);

const selectPeriodPie = (period) => {
    selectedPeriodPie.value = period;
    dropdownOpenPie.value = false;
    updatePieChartData();
};

// Pie CHART
const pieCanvas = ref(null);
let chartInstancePie = null;

const fetchPieChartData = async (period) => {
    let apiPeriod;
    switch (period) {
        case "Last 7 days":
            apiPeriod = "7days";
            break;
        case "Last 30 days":
            apiPeriod = "4weeks";
            break;
        case "Overall Total":
            apiPeriod = "months";
            break;
    }

    try {
        const response = await axios.get(route("getInvoicePieData"), {
            params: { period: apiPeriod },
        });
        return response.data;
    } catch (error) {
        console.error("Error fetching pie chart data:", error);
        return null;
    }
};

const updatePieChartData = async () => {
    if (!chartInstancePie) return;

    const chartData = await fetchPieChartData(selectedPeriodPie.value);
    if (!chartData) return;

    chartInstancePie.data.labels = chartData.labels;
    chartInstancePie.data.datasets[0].data = chartData.datasets[0].data;
    chartInstancePie.data.datasets[0].backgroundColor =
        chartData.datasets[0].backgroundColor;
    chartInstancePie.data.datasets[0].borderColor =
        chartData.datasets[0].borderColor;

    chartInstancePie.update();
};

onMounted(async () => {
    if (!pieCanvas.value) return;
    let delayed;
    chartInstancePie = new Chart(pieCanvas.value, {
        type: "doughnut",
        data: {
            labels: [],
            datasets: [
                {
                    label: "Items Sold",
                    data: [],
                    backgroundColor: [],
                    borderColor: [],
                    borderWidth: 2,
                    hoverOffset: 4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                onComplete: () => {
                    delayed = true;
                },
                delay: (context) => {
                    let delay = 0;
                    if (
                        context.type === "data" &&
                        context.mode === "default" &&
                        !delayed
                    ) {
                        delay =
                            context.dataIndex * 600 +
                            context.datasetIndex * 200;
                    }
                    return delay;
                },
            },
            plugins: {
                legend: {
                    labels: {
                        font: {
                            size: 11,
                        },
                        padding: 20,
                        boxWidth: 12,
                        generateLabels(chart) {
                            // Get the default behavior
                            const original =
                                Chart.overrides.pie.plugins.legend.labels.generateLabels(
                                    chart
                                );

                            // Modify it to add your value after the label
                            original.forEach((item, i) => {
                                const value = chart.data.datasets[0].data[i];
                                item.text = `${chart.data.labels[i]}: ${value}`;
                                item.fillStyle =
                                    chart.data.datasets[0].backgroundColor[i];
                                item.strokeStyle =
                                    chart.data.datasets[0].borderColor[i];
                                item.fontColor = getCssVar(
                                    "--color-text-primary"
                                );
                            });

                            return original;
                        },
                    },
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const label = context.label || "";
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce(
                                (sum, current) => {
                                    return sum + (Number(current) || 0);
                                },
                                0
                            );

                            if (total === 0) return `${label}: ${value}`;

                            const percentage = Math.round(
                                (value / total) * 100
                            );
                            return `${label}: ${value} (${percentage}%)`;
                        },
                    },
                },
            },
        },
    });
    await updatePieChartData();
});
watch(selectedPeriodPie, async () => {
    await updatePieChartData();
});

//Table
//CUSTOM DROPDOWN
const selectedPeriodTable = ref("Last 7 days");
const dropdownOpenTable = ref(false);
const isLoading = ref(false);
const customers = ref([]); // will hold response.data.data (array of customers)
const pagination = ref({}); // holds pagination meta info
const currentPage = ref(1); // track current page

const selectPeriodTable = (period) => {
    selectedPeriodTable.value = period;
    dropdownOpenTable.value = false;
    currentPage.value = 1; // reset to page 1 when period changes
    fetchCustomerData();
    progressAnimation();
};

const fetchCustomerData = async () => {
    try {
        isLoading.value = true;

        let apiPeriod;
        switch (selectedPeriodTable.value) {
            case "Last 7 days":
                apiPeriod = "7days";
                break;
            case "Last 30 days":
                apiPeriod = "4weeks";
                break;
            case "Overall Total":
                apiPeriod = "overall";
                break;
        }

        const response = await axios.get(route("getCustomerAccountsSummary"), {
            params: {
                period: apiPeriod,
                page: currentPage.value,
            },
        });

        customers.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            total: response.data.total,
            per_page: response.data.per_page,
        };
    } catch (error) {
        console.error("Error fetching customer data:", error);
    } finally {
        isLoading.value = false;
    }
};

const goToPage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        currentPage.value = page;
        fetchCustomerData();
    }
};

onMounted(() => {
    fetchCustomerData();
});

const getProgress = (arAmount, outstanding) => {
    const paid = arAmount - outstanding;
    const percentage = (paid / arAmount) * 100;
    return Math.max(0, percentage);
};

const getProgressColor = (percentage) => {
    percentage = Math.max(0, percentage);
    if (percentage >= 80) return "bg-green-500";
    if (percentage >= 50) return "bg-yellow-500";
    return "bg-red-500";
};

// Animated Number Component
const AnimatedNumber = defineComponent({
    props: {
        value: { type: Number, required: true },
        duration: { type: Number, default: 2000 },
        delay: { type: Number, default: 0 },
    },
    setup(props, { attrs }) {
        const currentValue = ref(0);

        onMounted(() => {
            setTimeout(() => {
                const startTime = Date.now();
                const animate = () => {
                    const elapsed = Date.now() - startTime;
                    const progress = Math.min(elapsed / props.duration, 1);
                    currentValue.value = Math.max(
                        Math.floor(progress * props.value)
                    );
                    if (progress < 1) {
                        requestAnimationFrame(animate);
                    }
                };
                animate();
            }, props.delay);
        });

        return () =>
            h(
                "span",
                {
                    class: attrs.class, // Inherit all classes
                },
                props.value <= 0 ? "0%" : `${currentValue.value}%`
            );
    },
});

const progressAnimation = () => {
    const style = document.createElement("style");
    style.textContent = `
    [data-width] {
      --progress-width: attr(data-width %);
    }
  `;
    document.head.appendChild(style);
};

// Add style rules for progress animation
onMounted(() => {
    progressAnimation();
});

const AnimatedCurrency = defineComponent({
    props: {
        value: { type: Number, required: true },
        duration: { type: Number, default: 1600 },
        delay: { type: Number, default: 0 },
        locale: { type: String, default: "en-PH" },
        currency: { type: String, default: "PHP" },
    },
    setup(props, { attrs }) {
        const currentValue = ref(0);

        const formatCurrency = (val) =>
            new Intl.NumberFormat(props.locale, {
                style: "currency",
                currency: props.currency,
            }).format(val);

        onMounted(() => {
            setTimeout(() => {
                const startTime = Date.now();
                const animate = () => {
                    const elapsed = Date.now() - startTime;
                    const progress = Math.min(elapsed / props.duration, 1);
                    currentValue.value = progress * props.value;
                    if (progress < 1) {
                        requestAnimationFrame(animate);
                    }
                };
                animate();
            }, props.delay);
        });

        return () =>
            h(
                "span",
                {
                    class: attrs.class,
                },
                formatCurrency(currentValue.value)
            );
    },
});

const getFirstValidChar = (name) => {
    if (!name) return "";

    const trimmedName = name.trim();
    for (let i = 0; i < trimmedName.length; i++) {
        if (trimmedName[i] !== " ") {
            return trimmedName[i].toUpperCase();
        }
    }
    return "";
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(amount);
};

const page = usePage();
const flash = computed(() => page.props.flash);

//WARNING TOAST
const showToast = ref(false);
const toastMessage = ref("");
const showWToast = ref(false);
const toastWMessage = ref("");

let toastTimeout = null;
let toastWTimeout = null;
const showWarningToast = (message) => {
    toastWMessage.value = message;
    showWToast.value = false;
    if (toastWTimeout) clearTimeout(toastWTimeout);

    setTimeout(() => {
        showWToast.value = true;
    }, 0);

    toastWTimeout = setTimeout(() => {
        showWToast.value = false;
        toastWTimeout = null;
    }, 3000);
};

const showSuccessToast = (message) => {
    toastMessage.value = message;
    showToast.value = true;

    if (toastTimeout) clearTimeout(toastTimeout);

    setTimeout(() => {
        showToast.value = true;
    }, 0);

    toastTimeout = setTimeout(() => {
        showToast.value = false;
        toastTimeout = null;
    }, 3000);
};

onMounted(() => {
    if (flash.value?.warning) {
        showWarningToast(flash.value.warning);
    } else if (flash.value?.successful){
        showSuccessToast(flash.value.successful);
    }
    
});
</script>

<style>
@keyframes progressGrow {
    from {
        width: 0%;
    }
    to {
        width: var(--progress-width);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Create a custom animated number component */
.animated-number {
    display: inline-block;
}
</style>
