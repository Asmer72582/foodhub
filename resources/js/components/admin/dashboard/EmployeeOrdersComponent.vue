<template>
    <LoadingComponent :props="loading" />
    <div class="col-12 xl:col-6 mb-6">
        <div class="db-card h-full bg-white rounded-xl shadow-sm">
            <div class="db-card-header flex flex-col sm:flex-row justify-between items-start sm:items-center p-6 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-800 mb-4 sm:mb-0">Employee Performance</h3>
                <div class="date-filter w-full sm:w-auto">
                    <Datepicker 
                        v-model="date" 
                        range 
                        :enableTimePicker="false" 
                        hideInputIcon 
                        autoApply 
                        :preset-ranges="presetRanges"
                        class="w-full sm:w-auto"
                        placeholder="Select date range"
                        @update:modelValue="handleDateChange"
                    >
                        <template #yearly="{ label, range, presetDateRange }">
                            <span @click="presetDateRange(range)">{{ label }}</span>
                        </template>
                    </Datepicker>
                </div>
            </div>
            <div class="db-card-body p-6">
                <!-- Error Message -->
                <div v-if="error" class="mb-6 p-4 bg-red-50 rounded-lg text-red-600 text-sm">
                    {{ error }}
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-[#D9DBE9]">
                                <th class="pb-2 text-sm font-medium text-heading">Employee</th>
                                <th class="pb-2 text-sm font-medium text-heading text-center">Generated Orders</th>
                                <th class="pb-2 text-sm font-medium text-heading text-right">Total Revenue</th>
                                <th class="pb-2 text-sm font-medium text-heading text-right">Tip Earned</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="employee_orders.length > 0" v-for="employee in employee_orders" :key="employee.id" class="border-b border-[#D9DBE9] last:border-0 hover:bg-gray-50 transition">
                                <td class="py-3 flex items-center gap-3">
                                    <img class="w-10 h-10 rounded-full object-cover" :src="employee.image" alt="avatar">
                                    <span class="text-sm font-medium capitalize text-heading">{{ employee.name }}</span>
                                </td>
                                <td class="py-3 text-center text-sm">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold text-white bg-[#008BBA]">{{ employee.total_orders }} {{ $t('label.orders') }}</span>
                                </td>
                                <td class="py-3 text-right text-sm font-semibold text-heading">{{ employee.total_sales_formatted }}</td>
                                <td class="py-3 text-right text-sm font-semibold text-primary group-hover:scale-110 transition-transform">{{ employee.total_tips_formatted }}</td>
                            </tr>
                            <tr v-else>
                                <td colspan="4" class="text-center py-4 text-sm text-gray-500">No employee orders generated in selected date range.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { format, startOfDay, endOfDay, startOfWeek, endOfWeek, startOfMonth, endOfMonth, startOfYear, endOfYear, subMonths } from 'date-fns';

export default {
    name: "EmployeeOrdersComponent",
    components: { LoadingComponent, Datepicker },
    data() {
        const lastMonthDate = subMonths(new Date(), 1);
        return {
            loading: {
                isActive: false,
            },
            employee_orders: {},
            date: null,
            selectedPreset: 'this_month',
            error: null,
            presetRanges: [
                {
                    label: 'Today',
                    range: [startOfDay(new Date()), endOfDay(new Date())],
                    preset: 'today'
                },
                {
                    label: 'This Week',
                    range: [startOfWeek(new Date()), endOfWeek(new Date())],
                    preset: 'this_week'
                },
                {
                    label: 'Last Month',
                    range: [startOfMonth(lastMonthDate), endOfMonth(lastMonthDate)],
                    preset: 'last_month'
                },
                {
                    label: 'This Month',
                    range: [startOfMonth(new Date()), endOfMonth(new Date())],
                    preset: 'this_month'
                },
                {
                    label: 'This Year',
                    range: [startOfYear(new Date()), endOfYear(new Date())],
                    preset: 'this_year'
                }
            ]
        };
    },
    mounted() {
        this.date = [startOfMonth(new Date()), endOfMonth(new Date())];
        this.fetchEmployeeOrders();
    },
    methods: {
        async handleDateChange(dates) {
            if (!dates) return;
            try {
                this.loading.isActive = true;
                this.error = null;
                this.selectedPreset = '';
                
                const formattedStartDate = format(new Date(dates[0]), 'yyyy-MM-dd');
                const formattedEndDate = format(new Date(dates[1]), 'yyyy-MM-dd');
                
                const params = {
                    date_range: 'custom',
                    start_date: formattedStartDate,
                    end_date: formattedEndDate
                };
                
                const res = await this.$store.dispatch('dashboard/employeeOrders', params);
                this.employee_orders = res.data.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch data';
            } finally {
                this.loading.isActive = false;
            }
        },
        async fetchEmployeeOrders() {
            try {
                this.loading.isActive = true;
                this.error = null;
                
                let params = {};
                if (this.selectedPreset) {
                    params = { date_range: this.selectedPreset };
                } else if (this.date) {
                    params = {
                        date_range: 'custom',
                        start_date: format(this.date[0], 'yyyy-MM-dd'),
                        end_date: format(this.date[1], 'yyyy-MM-dd')
                    };
                }

                const res = await this.$store.dispatch('dashboard/employeeOrders', params);
                this.employee_orders = res.data.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch data';
            } finally {
                this.loading.isActive = false;
            }
        },
    },
}
</script>

<style scoped>
/* Datepicker customization */
:deep(.dp__main) {
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    font-family: inherit;
}

:deep(.dp__input) {
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    font-size: 0.875rem;
    width: 100%;
    background-color: white;
}

:deep(.dp__input:hover) {
    border-color: #3b82f6;
}

:deep(.dp__preset_ranges) {
    padding: 1rem;
    border-right: 1px solid #e5e7eb;
}

:deep(.dp__range_end),
:deep(.dp__range_start),
:deep(.dp__active_date) {
    background: #3b82f6 !important;
}

:deep(.dp__range_between) {
    background: rgba(59, 130, 246, 0.1) !important;
}

@media (max-width: 640px) {
    .date-filter {
        width: 100%;
    }
    
    :deep(.dp__input) {
        font-size: 0.875rem;
    }
}
</style>
