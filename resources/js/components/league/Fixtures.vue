<script setup lang="ts">
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import {
    Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle,
} from '@/components/ui/card'
import axiosClient from '@/lib/axios';
import { computed, onMounted, ref } from 'vue';
import { GeneratedFixtureData, TeamSelectData } from '@/components/league/types';
import { Button } from '@/components/ui/button';
import ResetLeagueButton from '@/components/league/ResetLeagueButton.vue';

const data = ref<GeneratedFixtureData[]>([])
const fixtures = computed(() => data.value)

const getData = async () => {
    try {
        const response = await axiosClient.get('/api/fixtures');
        if (response.status && response.data && Array.isArray(response.data.data)) {
            data.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching fixtures:', error);
    }
};

const startSimulation = () => {
    emit('start-simulation',true)
}

const emit = defineEmits<{
    (e: 'start-simulation', isStarted: boolean): void
}>()

onMounted(() => getData());
</script>

<template>
    <div class="flex container items-center justify-between">
        <div class="left">
            <h2 class="text-3xl font-bold tracking-tight">
                Fixtures
            </h2>
            <p class="text-muted-foreground">
                Here is the generated fixture.
            </p>
        </div>
        <div class="right space-x-4">
            <ResetLeagueButton />
            <Button
                @click="startSimulation"
                variant="default"
            >
                Start Simulations
            </Button>
        </div>
    </div>


    <div class="flex-col md:flex">
        <div class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card v-for="fixture in fixtures" :key="fixture.week" class="w-[380px] col-span-1">
                    <CardHeader>
                        <CardTitle>Week {{ fixture.week }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Home</TableHead>
                                    <TableHead>Score</TableHead>
                                    <TableHead class="text-right"> Away </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="(match, index) in fixture.matches" :key="`week-${fixture.week}-match-${index}`">
                                    <TableCell>{{ match.home_team_name }}</TableCell>
                                    <TableCell>-</TableCell>
                                    <TableCell class="text-right">
                                        {{ match.away_team_name }}
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
