<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { TeamSelectData } from './types';
import { columns } from './team-select-columns'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'

import {
    ColumnFiltersState, ExpandedState, SortingState, VisibilityState,
    getCoreRowModel, getFilteredRowModel, getSortedRowModel, getExpandedRowModel,
    getPaginationRowModel, useVueTable, FlexRender
} from '@tanstack/vue-table'

import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue} from '@/components/ui/select'
import {
    DropdownMenu, DropdownMenuCheckboxItem, DropdownMenuContent, DropdownMenuTrigger
} from '@/components/ui/dropdown-menu'
import {ChevronLeftIcon, ChevronRightIcon, ChevronsLeftIcon, ChevronsRightIcon, ChevronDown} from 'lucide-vue-next'
import axiosClient from '@/lib/axios'
import { valueUpdater } from '@/components/ui/table/utils'

const data = ref<TeamSelectData[]>([])
const teams = computed(() => data.value)

const sorting = ref<SortingState>([])
const columnFilters = ref<ColumnFiltersState>([])
const columnVisibility = ref<VisibilityState>({})
const rowSelection = ref({})
const expanded = ref<ExpandedState>({})

const table = useVueTable({
    get data() {
        return teams.value
    },
    columns,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getExpandedRowModel: getExpandedRowModel(),
    onSortingChange: updater => valueUpdater(updater, sorting),
    onColumnFiltersChange: updater => valueUpdater(updater, columnFilters),
    onColumnVisibilityChange: updater => valueUpdater(updater, columnVisibility),
    onRowSelectionChange: updater => valueUpdater(updater, rowSelection),
    onExpandedChange: updater => valueUpdater(updater, expanded),
    state: {
        get sorting() { return sorting.value },
        get columnFilters() { return columnFilters.value },
        get columnVisibility() { return columnVisibility.value },
        get rowSelection() { return rowSelection.value },
        get expanded() { return expanded.value },
    },
})

const getData = async () => {
    try {
        const response = await axiosClient.get('/api/teams')
        if (response.status && response.data && Array.isArray(response.data.data)) {
            data.value = response.data.data
        }
    } catch (error) {
        console.error('Error fetching teams:', error)
    }
}

const generateFixture = async () => {
    const selectedIds = selectedTeams.value.map(team => String(team.id))

    try {
        const response = await axiosClient.post('/api/fixtures', {
            teams: selectedIds,
            strategy: 'round-robin'
        })

        if (response.status === 200 && response.data.success) {
            emit('fixture-generated', response.data.data, selectedTeams)
        } else {
            console.error('Unexpected response structure', response)
        }
    } catch (err) {
        console.error('Fixture generation failed:', err)
    }
}

const selectedTeams = computed(() => table.getSelectedRowModel().rows.map(row => row.original))

const canGenerateFixture = computed(() => {
    return selectedTeams.value.length === 4 &&
        new Set(selectedTeams.value.map(team => team.city)).size === 4
})

const selectionError = computed(() => {
    if (selectedTeams.value.length !== 4) return 'You must select exactly 4 teams'
    if (new Set(selectedTeams.value.map(team => team.city)).size !== 4) return 'Each team must be from a different city'
    return ''
})

const emit = defineEmits<{
    (e: 'selected', selectedIds: number[], selectedTeams: TeamSelectData[]): void
    (e: 'fixture-generated', fixtures: any[], selectedTeams: TeamSelectData[]): void
}>()

onMounted(() => getData())
</script>

<template>
    <div>
        <h2 class="text-3xl font-bold tracking-tight">
            Team selection
        </h2>
        <p class="text-muted-foreground">
            Please select 4 teams to generate fixture
        </p>
    </div>
    <div class="w-full space-y-4">
        <div class="flex items-center py-4 space-x-4">
            <Input
                class="max-w-sm"
                placeholder="Filter team name..."
                :model-value="table.getColumn('name')?.getFilterValue() as string"
                @update:model-value="table.getColumn('name')?.setFilterValue($event)"
            />
            <TooltipProvider>
                <Tooltip>
                    <TooltipTrigger as-child>
                        <div>
                            <Button
                                @click="generateFixture"
                                :disabled="!canGenerateFixture"
                                variant="default"
                            >
                                Generate Fixture
                            </Button>
                        </div>
                    </TooltipTrigger>
                    <TooltipContent
                        side="top"
                        align="center"
                        avoid-collisions
                        hide-when-detached
                        update-position-strategy="optimized"
                        position-strategy="absolute"
                    >
                        <span v-if="!canGenerateFixture">{{ selectionError }}</span>
                        <span v-else>Click to generate fixture</span>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>

            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="outline" class="ml-auto">
                        Columns <ChevronDown class="ml-2 h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuCheckboxItem
                        v-for="column in table.getAllColumns().filter(c => c.getCanHide())"
                        :key="column.id"
                        class="capitalize"
                        :model-value="column.getIsVisible()"
                        @update:model-value="value => column.toggleVisibility(!!value)"
                    >
                        {{ column.id }}
                    </DropdownMenuCheckboxItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>

        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id">
                            <FlexRender
                                v-if="!header.isPlaceholder"
                                :render="header.column.columnDef.header"
                                :props="header.getContext()"
                            />
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="table.getRowModel().rows.length">
                        <template v-for="row in table.getRowModel().rows" :key="row.id">
                            <TableRow :data-state="row.getIsSelected() && 'selected'">
                                <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                    <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="row.getIsExpanded()">
                                <TableCell :colspan="row.getAllCells().length">
                                    {{ JSON.stringify(row.original) }}
                                </TableCell>
                            </TableRow>
                        </template>
                    </template>
                    <TableRow v-else>
                        <TableCell :colspan="columns.length" class="h-24 text-center">
                            No results.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div class="flex items-center justify-between px-2">
            <div class="flex-1 text-sm text-muted-foreground">
                {{ table.getFilteredSelectedRowModel().rows.length }} of
                {{ table.getFilteredRowModel().rows.length }} row(s) selected.
            </div>
            <div class="flex items-center space-x-6 lg:space-x-8">
                <div class="flex items-center space-x-2">
                    <p class="text-sm font-medium">
                        Rows per page
                    </p>
                    <Select
                        :model-value="`${table.getState().pagination.pageSize}`"
                        @update:model-value="table.setPageSize"
                    >
                        <SelectTrigger class="h-8 w-[70px]">
                            <SelectValue :placeholder="`${table.getState().pagination.pageSize}`" />
                        </SelectTrigger>
                        <SelectContent side="top">
                            <SelectItem v-for="pageSize in [10, 20, 30, 40, 50]" :key="pageSize" :value="`${pageSize}`">
                                {{ pageSize }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="flex w-[100px] items-center justify-center text-sm font-medium">
                    Page {{ table.getState().pagination.pageIndex + 1 }} of
                    {{ table.getPageCount() }}
                </div>
                <div class="flex items-center space-x-2">
                    <Button
                        variant="outline"
                        class="hidden h-8 w-8 p-0 lg:flex"
                        :disabled="!table.getCanPreviousPage()"
                        @click="table.setPageIndex(0)"
                    >
                        <span class="sr-only">Go to first page</span>
                        <ChevronsLeftIcon class="h-4 w-4" />
                    </Button>
                    <Button
                        variant="outline"
                        class="h-8 w-8 p-0"
                        :disabled="!table.getCanPreviousPage()"
                        @click="table.previousPage()"
                    >
                        <span class="sr-only">Go to previous page</span>
                        <ChevronLeftIcon class="h-4 w-4" />
                    </Button>
                    <Button
                        variant="outline"
                        class="h-8 w-8 p-0"
                        :disabled="!table.getCanNextPage()"
                        @click="table.nextPage()"
                    >
                        <span class="sr-only">Go to next page</span>
                        <ChevronRightIcon class="h-4 w-4" />
                    </Button>
                    <Button
                        variant="outline"
                        class="hidden h-8 w-8 p-0 lg:flex"
                        :disabled="!table.getCanNextPage()"
                        @click="table.setPageIndex(table.getPageCount() - 1)"
                    >
                        <span class="sr-only">Go to last page</span>
                        <ChevronsRightIcon class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
