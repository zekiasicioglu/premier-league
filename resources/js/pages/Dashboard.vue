<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import TeamSelect from '@/components/league/TeamSelect.vue';
import { onMounted, ref } from 'vue';
import { TeamSelectData } from '@/components/league/types';
import Fixtures from '@/components/league/Fixtures.vue';
import Simulation from '@/components/league/Simulation.vue';
import axiosClient from '@/lib/axios';
import { toast } from 'vue-sonner';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const isFixtureCreated = ref<boolean>(false)
const fixtureTeams = ref<TeamSelectData[]>([])
const isLeagueStarted = ref<boolean>(false)

onMounted( async () => {
    try {
        const response = await axiosClient.get('/api/fixtures/current');
        if (response.status) {
            isFixtureCreated.value = !!(Array.isArray(response.data.fixture) && response.data.fixture.length > 0);
            isLeagueStarted.value = response.data.is_played;
        }
    } catch (error) {
        console.error('Error fetching fixtures:', error);
        isFixtureCreated.value = false;
    }
})

const handleGeneratedFixture = (fixtures: any[], teams: TeamSelectData[]) => {
    isFixtureCreated.value = true
    fixtureTeams.value = teams
}
const handleStartSimulation = (isStarted: boolean) => {
    isLeagueStarted.value = isStarted
}

</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex-1 flex-col space-y-8 p-8">
            <TeamSelect v-if="!isFixtureCreated" @fixture-generated="handleGeneratedFixture"/>
            <Fixtures v-if="isFixtureCreated && !isLeagueStarted" @start-simulation="handleStartSimulation"/>
            <Simulation v-if="isFixtureCreated && isLeagueStarted"/>
        </div>
    </AppLayout>
</template>
