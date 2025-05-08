<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import {
    Card, CardContent, CardDescription, CardHeader, CardTitle,
} from '@/components/ui/card'
import axiosClient from '@/lib/axios';
import { computed, onMounted, ref } from 'vue';
import { CurrentWeekData} from '@/components/league/types';
import { toast } from 'vue-sonner'
import { Button } from '@/components/ui/button';
import ResetLeagueButton from '@/components/league/ResetLeagueButton.vue';

const data = ref<CurrentWeekData>()
const isCompleted = ref<boolean>(false)
const isPlayed = computed(() => data.value?.is_played)
const week = computed(() => data.value?.week)
const teams = computed(() => data.value?.teams)
const fixture = computed(() => data.value?.fixture)
const isPlayingNextWeek = ref(false)
const isResettingData = ref(false)

const getData = async () => {
    try {
        const response = await axiosClient.get('/api/fixtures/current');
        if (response.status && response.data) {
            data.value = response.data;
            isCompleted.value = false;
        }
    } catch (error) {
        console.error('Error fetching fixtures:', error);
    }
};

const resetData = async () => {
    isResettingData.value = true;
    try {
        const response = await axiosClient.post('/api/fixtures/reset');
        if (response.status && response.data) {
            await getData();
        }
    } catch (error) {
        console.error('Error resetting fixture:', error);
    } finally {
        isResettingData.value = false;
    }
};

const playNextWeek = async () => {
    isPlayingNextWeek.value = true;
    try {
        const response = await axiosClient.get('/api/fixtures/play_next_week');
        if (response.status && response.data) {
            if (response.data.completed) {
                isCompleted.value = true;
                toast.success("Fixture is completed", {
                    description: "You can reset data and start over",
                });
            } else {
                await getData();
            }
        }
    } catch (error) {
        console.error('Error next week:', error);
    } finally {
        isPlayingNextWeek.value = false;
    }
};

const isAutoPlaying = ref(false)
const autoplaySpeed = ref(1000)
let autoplayTimeout: ReturnType<typeof setTimeout> | null = null;

const playAllWeeks = async () => {
    if (isAutoPlaying.value) return;

    isAutoPlaying.value = true;

    const playNext = async () => {
        try {
            const response = await axiosClient.get('/api/fixtures/play_next_week');
            if (response.status && response.data) {
                if (response.data.completed) {
                    isCompleted.value = true;
                    isAutoPlaying.value = false;
                    toast.success('Fixture is completed');
                    return;
                } else {
                    await getData();
                }
            }
        } catch (error) {
            console.error('Autoplay error:', error);
            isAutoPlaying.value = false;
            return;
        }

        if (isAutoPlaying.value) {
            autoplayTimeout = setTimeout(playNext, autoplaySpeed.value);
        }
    };

    playNext();
};

const stopAutoplay = () => {
    isAutoPlaying.value = false;
    if (autoplayTimeout) {
        clearTimeout(autoplayTimeout);
        autoplayTimeout = null;
    }
};

onMounted(() => getData());
</script>

<template>
    <div class="flex container items-center justify-between">
        <div class="left">
            <h2 class="text-3xl font-bold tracking-tight">
                Simulation
            </h2>
            <p class="text-muted-foreground">
                Enjoy your league simulation.
            </p>
        </div>
        <div class="right">
            <ResetLeagueButton />
        </div>
    </div>
    <div class="flex-col md:flex">
        <div class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-7">
                <Card class="col-span-3">
                    <CardHeader>
                        <CardTitle>Teams</CardTitle>
                        <CardDescription>All teams in the fixture</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Team Name</TableHead>
                                    <TableHead>P</TableHead>
                                    <TableHead>W</TableHead>
                                    <TableHead>D</TableHead>
                                    <TableHead>L</TableHead>
                                    <TableHead>GF</TableHead>
                                    <TableHead>GA</TableHead>
                                    <TableHead>GD</TableHead>
                                    <TableHead class="text-right">PTS</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="team in teams" :key="team.id">
                                    <TableCell>{{ team.name }}</TableCell>
                                    <TableCell>{{ team.played }}</TableCell>
                                    <TableCell>{{ team.won }}</TableCell>
                                    <TableCell>{{ team.drawn }}</TableCell>
                                    <TableCell>{{ team.lost }}</TableCell>
                                    <TableCell>{{ team.goals_for }}</TableCell>
                                    <TableCell>{{ team.goals_against }}</TableCell>
                                    <TableCell>{{ team.goals_difference }}</TableCell>
                                    <TableCell class="text-right">
                                        {{ team.points }}
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
                <Card class="col-span-2">
                    <CardHeader>
                        <CardTitle>Week {{ week }} {{ isCompleted ? " - COMPLETED": ""}}</CardTitle>
                        <CardDescription>Current week ({{ isPlayed ? "Played" : "Not Played"}})</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Home</TableHead>
                                    <TableHead>Score</TableHead>
                                    <TableHead class="text-right">Away</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="(match, index) in fixture" :key="`week-${week}-match-${index}`">
                                    <TableCell>{{ match.home_team_name }}</TableCell>
                                    <TableCell>{{ `${match.home_team_score}-${match.away_team_score}` }}</TableCell>
                                    <TableCell class="text-right">{{ match.away_team_name }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
                <Card class="col-span-2">
                    <CardHeader>
                        <CardTitle>Championship Predictions</CardTitle>
                        <CardDescription>Based on the team strength and scores.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Team Name</TableHead>
                                    <TableHead class="text-right">%</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="team in teams" :key="team.id">
                                    <TableCell>{{ team.name }}</TableCell>
                                    <TableCell class="text-right">
                                        {{ team.prediction?.toFixed(2) }}
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>


    <div class="flex container items-center justify-between">
        <div class="flex items-center space-x-2">
            <label class="text-sm font-medium">Speed:</label>
            <input
                type="range"
                min="200"
                max="2000"
                step="100"
                v-model="autoplaySpeed"
                class="w-48"
            />
            <span class="text-sm">{{ autoplaySpeed }}ms</span>
        </div>
    </div>
    <div class="flex container items-center justify-between">
        <Button v-if="!isAutoPlaying" @click="playAllWeeks" :disabled="isAutoPlaying || isResettingData || isPlayingNextWeek">Play All</Button>
        <Button v-else @click="stopAutoplay" variant="secondary" :disabled="!isAutoPlaying">Stop</Button>
        <Button @click="playNextWeek" :disabled="isPlayingNextWeek || isAutoPlaying || isResettingData">
            <span v-if="isPlayingNextWeek">Playing...</span>
            <span v-else-if="isAutoPlaying">Auto-playing...</span>
            <span v-else>Play Next Week</span>
        </Button>

        <Button @click="resetData" :disabled="isResettingData || isAutoPlaying || isPlayingNextWeek" variant="destructive">
            <span v-if="isResettingData">Resetting...</span>
            <span v-else>Reset Data</span>
        </Button>
    </div>
</template>
