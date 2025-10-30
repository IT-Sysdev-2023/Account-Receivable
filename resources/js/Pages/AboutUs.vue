<template>
    <Head :title="` | About Us`" />
    <div class="min-h-screen pb-16 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div
            class="max-w-7xl mx-auto mb-16 flex flex-col items-center justify-between"
        >
            <img
                :src="'/storage/images/mflogo.png'"
                alt="Logo"
                class="w-40 h-40 object-contain transition-all duration-300"
            />
            <div class="flex-1 text-center">
                <div class="relative inline-block">
                    <h1
                        class="text-5xl font-bold sm:text-6xl lg:text-7xl bg-clip-text text-transparent bg-gradient-to-b from-[var(--color-primary)] to-[var(--color-primary-hover)]"
                    >
                        Meet Our Farms Team
                    </h1>
                </div>
                <p
                    class="mt-2 max-w-2xl mx-auto text-xl text-[var(--color-text-secondary)]/80 font-medium"
                >
                    The talented people behind our success in developing this
                    application
                </p>
            </div>
        </div>

        <div class="flex flex-col gap-16 max-w-7xl mx-auto">
            <!-- Leadership Section -->
            <div class="space-y-10">
                <div class="text-center">
                    <span
                        class="inline-block px-4 py-1 text-sm font-semibold text-[var(--color-primary-hover)] bg-[var(--color-primary)]/20 rounded-full mb-2"
                    >
                        MANAGEMENT
                    </span>
                    <h2
                        class="text-3xl font-bold text-[var(--color-text-secondary)]/80"
                    >
                        Leadership Team
                        <span
                            class="block w-16 h-1 mx-auto mt-3 bg-[var(--color-border)] rounded-full"
                        ></span>
                    </h2>
                </div>
                <div class="flex justify-center">
                    <div class="grid grid-cols-1 gap-10 max-w-[420px]">
                        <TeamCard
                            v-for="member in Director"
                            :key="member.id"
                            :member="member"
                        />
                    </div>
                </div>
            </div>

            <!-- Junior Leadership Section -->
            <div class="space-y-10">
                <div class="flex justify-center">
                    <div class="grid grid-cols-1 gap-10 max-w-[420px]">
                        <TeamCard
                            v-for="member in JrSupervisor"
                            :key="member.id"
                            :member="member"
                        />
                    </div>
                </div>
            </div>

            <!-- Section Heads -->
            <div class="space-y-10">
                <div class="text-center">
                    <span
                        class="inline-block px-4 py-1 text-sm font-semibold text-[var(--color-primary-hover)] bg-[var(--color-primary)]/20 rounded-full mb-2"
                    >
                        DEPARTMENT LEADERS
                    </span>
                    <h2
                        class="text-3xl font-bold text-[var(--color-text-secondary)]/80"
                    >
                        Section Heads
                        <span
                            class="block w-16 h-1 mx-auto mt-3 bg-[var(--color-border)] rounded-full"
                        ></span>
                    </h2>
                </div>
                <div
                    class="grid grid-cols-1 gap-10 sm:grid-cols-2 justify-items-center"
                >
                    <TeamCard
                        v-for="member in SectionHeads"
                        :key="member.id"
                        :member="member"
                    />
                </div>
            </div>

            <!-- Development Team Section -->
            <div class="space-y-10">
                <div class="text-center">
                    <span
                        class="inline-block px-4 py-1 text-sm font-semibold text-[var(--color-primary-hover)] bg-[var(--color-primary)]/20 rounded-full mb-2"
                    >
                        DEVELOPMENT TEAM
                    </span>
                    <h2
                        class="text-3xl font-bold text-[var(--color-text-secondary)]/80"
                    >
                        Team Leader
                        <span
                            class="block w-16 h-1 mx-auto mt-3 bg-[var(--color-border)] rounded-full"
                        ></span>
                    </h2>
                </div>
                <div
                    class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-3 justify-items-center"
                >
                    <TeamCard
                        v-for="member in teamLeader"
                        :key="member.id"
                        :member="member"
                        class="col-start-2"
                    />
                </div>
            </div>
            <div class="space-y-10">
                <div class="text-center">
                    <h2
                        class="text-3xl font-bold text-[var(--color-text-secondary)]/80"
                    >
                        Programmers
                        <span
                            class="block w-16 h-1 mx-auto mt-3 bg-[var(--color-border)] rounded-full"
                        ></span>
                    </h2>
                </div>
                <div
                    class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-3 justify-items-center"
                >
                    <TeamCard
                        v-for="member in teamMembers"
                        :key="member.id"
                        :member="member"
                    />
                </div>
            </div>
            <div class="space-y-10">
                <div
                    class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-3 justify-items-center"
                >
                    <TeamCard
                        v-for="member in teamMembersTwo"
                        :key="member.id"
                        :member="member"
                        class="col-start-2"
                    />
                </div>
            </div>

            <div class="space-y-10">
                <div class="text-center">
                    <h2
                        class="text-3xl font-bold text-[var(--color-text-secondary)]/80"
                    >
                        System Analysts
                        <span
                            class="block w-16 h-1 mx-auto mt-3 bg-[var(--color-border)] rounded-full"
                        ></span>
                    </h2>
                </div>
                <div
                    class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-3 justify-items-center"
                >
                    <TeamCard
                        v-for="member in teamMembersAnalyst"
                        :key="member.id"
                        :member="member"
                    />
                </div>
            </div>

            <div class="space-y-10">
                <div class="text-center">
                    <h2
                        class="text-3xl font-bold text-[var(--color-text-secondary)]/80"
                    >
                        Contributors
                        <span
                            class="block w-16 h-1 mx-auto mt-3 bg-[var(--color-border)] rounded-full"
                        ></span>
                    </h2>
                </div>
                <div
                    class="grid grid-cols-1 gap-10 sm:grid-cols-2 justify-items-center"
                >
                    <TeamCard
                        v-for="member in activeContributors"
                        :key="member.id"
                        :member="member"
                    />
                </div>
            </div>

            <div class="space-y-10">
                <div
                    class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-3 justify-items-center"
                >
                    <TeamCard
                        v-for="member in contributors"
                        :key="member.id"
                        :member="member"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import TeamCard from "./Components/TeamCard.vue";

const contributors = [
    {
        id: 1,
        name: "Joel Lagrimas",
        position: "CSU - Assistant Head IT",
        bio: "",
        image: "/storage/aboutus/Lagrimas, Joel R.jpg",
        skills: ["Resigned"],
        role: "",
    },
    {
        id: 2,
        name: "John Edward Bongolan",
        position: "Programmer",
        bio: "",
        image: "/storage/aboutus/Bongolan, Edward.jpg",
        skills: ["Resigned"],
        role: "",
    },
    {
        id: 3,
        name: "John Virgil Bulac",
        position: "Programmer",
        bio: "",
        image: "/storage/aboutus/Bulac, John Vergil.jpg",
        skills: ["Resigned"],
        role: "",
    },
    {
        id: 4,
        name: "Jeffrey Joy Jaspe",
        position: "Programmer",
        bio: "",
        image: "/storage/aboutus/Jaspe, Jeffrey Joy.jpg",
        skills: ["Resigned"],
        role: "",
    },
    {
        id: 5,
        name: "Mike Joshua Dagumbal",
        position: "Programmer",
        bio: "",
        image: "/storage/aboutus/Dagumbal, Mike.jpg",
        skills: ["Resigned"],
        role: "",
    },

    {
        id: 7,
        name: "Darryl May Garan",
        position: "System Analyst",
        bio: "",
        image: "/storage/aboutus/Garan, Darryl Mae.jpg",
        skills: ["Resigned"],
        role: "",
    },
    {
        id: 8,
        name: "Mary Camille Notarte",
        position: "System Analyst",
        bio: "",
        image: "/storage/aboutus/Notarte, Mary Camille.jpg",
        skills: ["Resigned"],
        role: "",
    },
    {
        id: 9,
        name: "Riza Mae Chagas",
        position: "System Analyst",
        bio: "",
        image: "/storage/aboutus/Chagas, Riza Mae.jpg",
        skills: ["Resigned"],
        role: "",
    },
    {
        id: 10,
        name: "Princess Candy Lou Dela Cruz",
        position: "System Analyst",
        bio: "",
        image: "/storage/aboutus/Dela Cruz, Princess Candy Lou.jpg",
        skills: ["Resigned"],
        role: "",
    },
    {
        id: 11,
        name: "Flordelyn Maputol",
        position: "System Analyst",
        bio: "",
        image: "/storage/aboutus/Maputol, Flordelyn.jpg",
        skills: ["Resigned"],
        role: "",
    },

    {
        id: 13,
        name: "John Marve Penaso",
        position: "Programmer",
        bio: "",
        image: "/storage/aboutus/John Marve Penaso.jpg",
        skills: ["Resigned"],
        role: "",
    },
    {
        id: 14,
        name: "Russell Reynolds",
        position: "Programmer",
        bio: "",
        image: "/storage/aboutus/Russell Reynolds.jpg",
        skills: ["Resigned"],
        role: "",
    },
    {
        id: 15,
        name: "Mhelvin Reyes",
        position: "Programmer",
        bio: "",
        image: "/storage/aboutus/mhelvin_reyes.png",
        skills: ["Resigned"],
        role: "",
    },
    {
        id: 16,
        name: "Rolando Pelpinosas",
        position: "Programmer",
        bio: "",
        image: "/storage/aboutus/rolando_pelpinosas.png",
        skills: ["Resigned"],
        role: "",
    },
    {
        id: 17,
        name: "Jestoni Abulag",
        position: "Programmer",
        bio: "",
        image: "/storage/aboutus/jestoni.jpg",
        skills: ["Resigned"],
        role: "",
    },
];

const activeContributors = [
    {
        id: 2,
        name: "Michael S. Malate",
        position: "System Programmer II",
        bio: "",
        image: "/storage/aboutus/Malate, Michael.jpg",
        skills: ["Active"],
        role: "",
    },
    {
        id: 12,
        name: "Jeremiah Gamao Abadecio",
        position: "System Analyst",
        bio: "",
        image: "/storage/aboutus/Abadecio, Jeremiah.jpg",
        skills: ["Active"],
        role: "",
    },
];

const teamMembersAnalyst = [
    {
        id: 1,
        name: "Gedym Mae Sab",
        position: "System Analyst II",
        bio: "Dedicated System Analyst of the Farms Team in system development and support.",
        image: "/storage/aboutus/Sab, Gedym Mae.jpg",
        skills: ["Active"],
        role: "",
    },
    {
        id: 2,
        name: "Jessa A. Marapao",
        position: "System Analyst II",
        bio: "Dedicated System Analyst of the Farms Team in system development and support.",
        image: "/storage/aboutus/Marapao, Jessa A.jpg",
        skills: ["Active"],
        role: "",
    },
    {
        id: 3,
        name: "Mark James M. Bitalas",
        position: "System Analyst II",
        bio: "Dedicated System Analyst of the Farms Team in system development and support.",
        image: "/storage/aboutus/Bitalas, Mark James M.jpg",
        skills: ["Active"],
        role: "",
    },
];

const teamMembers = [
    {
        id: 1,
        name: "Timothy Jan S. Paredes",
        position: "System Programmer III",
        bio: "Dedicated programmer of the Farms Team in system development.",
        image: "/storage/aboutus/Paredes, Timothy Jan.jpg",
        skills: ["Active"],
    },
    {
        id: 2,
        name: "Mariel Alejandria Taray",
        position: "System Programmer III",
        bio: "Dedicated programmer of the Farms Team in system development.",
        image: "/storage/aboutus/Taray, Mariel Alejandria.jpg",
        skills: ["Active"],
        role: "",
    },
    {
        id: 3,
        name: "Jan Jeffrey B. Amodia",
        position: "System Programmer III",
        bio: "Dedicated programmer of the Farms Team in system development.",
        image: "/storage/aboutus/Amodia, Jan Jeffrey B.jpg",
        skills: ["Active"],
        role: "",
    },
    {
        id: 4,
        name: "Riza I. Salva",
        position: "System Programmer III",
        bio: "Dedicated programmer of the Farms Team in system development.",
        image: "/storage/aboutus/Salva, Riza Item.jpg",
        skills: ["Active"],
        role: "",
    },
    {
        id: 5,
        name: "Renan Carlo S. Dalut",
        position: "System Programmer II",
        bio: "Dedicated programmer of the Farms Team in system development.",
        image: "/storage/aboutus/Dalut, Renan Carlo.jpg",
        skills: ["Active"],
        role: "",
    },
    {
        id: 6,
        name: "Michael A. Carmona",
        position: "System Programmer II",
        bio: "Dedicated programmer of the Farms Team in system development.",
        image: "/storage/aboutus/Carmona, Michael.jpg",
        skills: ["Active"],
        role: "",
    },
];

const teamMembersTwo = [
    {
        id: 7,
        name: "Erl Christian Roxas",
        position: "System Programmer I",
        bio: "Dedicated programmer of the Farms Team in system development.",
        image: "/storage/aboutus/Roxas, Erl Christian.jpg",
        skills: ["Active"],
        role: "",
    },
];

const teamLeader = [
    {
        id: 1,
        name: "Feliciano S. Bueno Jr.",
        position: "System Programmer III",
        bio: "Seasoned leader of the Farms Team with 20+ years of experience in system development.",
        image: "/storage/aboutus/Bueno, Feliciano Jr_.jpg",
        skills: ["Active"],
        role: "",
    },
];

const SectionHeads = [
    {
        id: 1,
        name: "Mark Anthony S. Rabaca",
        position: "Supervisor",
        bio: "Seasoned Section Head with 10+ years specializing in record management and technical systems oversight. Combines technical expertise with strategic planning to optimize information workflows and operational efficiency across departments.",
        image: "/storage/aboutus/Rabaca, Mark Anthony S.jpg",
        skills: ["Active"],
        role: "",
    },
    {
        id: 2,
        name: "Jeneffer C. Cutanda",
        position: "Section Head",
        bio: "HRMS System and Time Keeping Analyst with 10+ years of experience in optimizing HR information systems. Leads the development and implementation of streamlined timekeeping solutions that enhance workforce management and payroll accuracy.",
        image: "/storage/aboutus/Cutanda, Jeneffer C.jpg",
        skills: ["Active"],
        role: "",
    },
];

const JrSupervisor = [
    {
        id: 1,
        name: "Maria Cristina C. Evarle",
        position: "Jr. Supervisor",
        bio: "Accounting graduate with 12+ years of demonstrated success in system development operations. As Jr. Supervisor, consistently delivers accurate reporting, streamlines processes, and fosters collaborative team environments that drive departmental success.",
        image: "/storage/aboutus/Evarle, Maria Cristina C.jpg",
        skills: ["Active"],
        role: "Checked By",
    },
];

const Director = [
    {
        id: 1,
        name: "Maria Neliza U. Fuertes CPA, CIA, CSCU, CISA, REB, REA, CICA, CrFA, REC",
        position: "Group Managing Director",
        bio: "Distinguished finance executive and multiple-certified professional (CPA, CIA, CSCU, CISA, REB, REA, CICA, CrFA, REC) with extensive expertise in management, internal auditing, and corporate governance.",
        image: "/storage/aboutus/Fuertes, Maria Neliza Uy.jpg",
        skills: ["Active"],
        role: "Reviewed By",
    },
];
</script>
