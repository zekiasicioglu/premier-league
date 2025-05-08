<script setup lang="ts">

import { Button } from '@/components/ui/button';
import axiosClient from '@/lib/axios';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const isResettingLeagueData = ref(false)

const resetLeagueData = async () => {
    isResettingLeagueData.value = true;
    try {
        const response = await axiosClient.delete('/api/fixtures');
        if (response.status) {
            router.visit('/dashboard');
        }
    } catch (error) {
        console.error('Error resetting league data:', error);
    } finally {
        isResettingLeagueData.value = false;
    }
};
</script>

<template>
    <Button @click="resetLeagueData" :disabled="isResettingLeagueData" variant="destructive">
        <span v-if="isResettingLeagueData">Resetting...</span>
        <span v-else>Reset League</span>
    </Button>
</template>
