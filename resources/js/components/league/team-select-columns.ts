
import { h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table';
import { TeamSelectData } from '@/components/league/types';
import { Checkbox } from '@/components/ui/checkbox';

export const columns: ColumnDef<TeamSelectData>[] = [
    {
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            'modelValue': table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
            'onUpdate:modelValue': value => table.toggleAllPageRowsSelected(!!value),
            'ariaLabel': 'Select all',
            'class': 'translate-y-0.5',
        }),
        cell: ({ row }) => h(Checkbox, { 'modelValue': row.getIsSelected(), 'onUpdate:modelValue': value => row.toggleSelected(!!value), 'ariaLabel': 'Select row', 'class': 'translate-y-0.5' }),
        enableSorting: false,
        enableHiding: false,
    },
    {
        accessorKey: 'id',
        header: () => 'ID',
        cell: ({ row }) => h('div', { class: '' }, row.getValue('id')),
    },
    {
        accessorKey: 'name',
        header: () => 'Team Name',
        cell: ({ row }) => h('div', { class: '' }, row.getValue('name')),
    },
    {
        accessorKey: 'city',
        header: () => 'City',
        cell: ({ row }) => h('div', { class: '' }, row.getValue('city')),
    },
    {
        accessorKey: 'strength',
        header: () => 'Strength',
        cell: ({ row }) => h('div', { class: '' }, row.getValue('strength')),
    }
]
