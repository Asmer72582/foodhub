<template>
    <div class="col-12 lg:col-6 mb-6">
        <div class="db-card h-full bg-white rounded-xl shadow-sm">
            <!-- Header Section -->
            <div class="db-card-header flex flex-col sm:flex-row justify-between items-start sm:items-center p-6 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-800 mb-4 sm:mb-0">{{ $t('Payment Method Breakdown') }}</h3>
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

            <!-- Content Section -->
            <div class="p-6">
                <!-- Error Message -->
                <div v-if="error" class="mb-6 p-4 bg-red-50 rounded-lg text-red-600 text-sm">
                    {{ error }}
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="flex justify-center items-center min-h-[400px]">
                    <div class="loading-spinner"></div>
                </div>

                <!-- Data Display -->
                <template v-else>
                    <div class="flex flex-wrap -mx-3">
                        <!-- Stats Grid -->
                        <div class="w-full lg:w-1/2 px-3 mb-6 lg:mb-0">
                            <div class="grid grid-cols-1 gap-4">
                                <!-- Dine In -->
                                <div class="stat-card bg-gradient-to-br from-blue-50 to-blue-100 p-5 rounded-xl border border-blue-200">
                                    <div class="flex items-center">
                                        <div class="stat-icon bg-white p-3 rounded-lg shadow-sm mr-4">
                                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-blue-600">{{ $t('Dine In') }}</p>
                                            <h4 class="text-2xl font-bold text-blue-700 mt-1">{{ dineInAmount }}</h4>
                                        </div>
                                    </div>
                                </div>

                                <!-- Takeaway -->
                                <div class="stat-card bg-gradient-to-br from-green-50 to-green-100 p-5 rounded-xl border border-green-200">
                                    <div class="flex items-center">
                                        <div class="stat-icon bg-white p-3 rounded-lg shadow-sm mr-4">
                                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-green-600">{{ $t('Takeaway') }}</p>
                                            <h4 class="text-2xl font-bold text-green-700 mt-1">{{ takeawayAmount }}</h4>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delivery -->
                                <div class="stat-card bg-gradient-to-br from-purple-50 to-purple-100 p-5 rounded-xl border border-purple-200">
                                    <div class="flex items-center">
                                        <div class="stat-icon bg-white p-3 rounded-lg shadow-sm mr-4">
                                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-purple-600">{{ $t('Delivery') }}</p>
                                            <h4 class="text-2xl font-bold text-purple-700 mt-1">{{ deliveryAmount }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pie Chart -->
                        <div class="w-full lg:w-1/2 px-3">
                            <div class="bg-white rounded-xl p-4 h-full flex items-center justify-center">
                                <div class="chart-container" style="max-width: 320px;">
                                    <apexchart 
                                        v-if="chartOptions" 
                                        :options="chartOptions" 
                                        :series="chartOptions.series"
                                        type="pie"
                                        class="w-full"
                                    ></apexchart>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
import appService from "../../../services/appService";
import { mapGetters } from 'vuex';
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { format, startOfDay, endOfDay, startOfWeek, endOfWeek, startOfMonth, endOfMonth, startOfYear, endOfYear } from 'date-fns';
import orderMethodEnum from "../../../enums/modules/orderMethodEnum";

export default {
    name: "DashboardPaymentMethodBreakdownComponent",
    components: {
        Datepicker
    },
    data() {
        return {
            date: null,
            selectedDate: null,
            selectedPreset: 'this_month',
            loading: false,
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
        }
    },
    computed: {
        ...mapGetters({
            paymentMethodData: 'dashboard/getPaymentMethodBreakdown'
        }),
        dineInAmount() {
            return this.currencyFormat(this.extractNumericValue(this.paymentMethodData?.data?.dine_in || 0));
        },
        takeawayAmount() {
            return this.currencyFormat(this.extractNumericValue(this.paymentMethodData?.data?.takeaway || 0));
        },
        deliveryAmount() {
            return this.currencyFormat(this.extractNumericValue(this.paymentMethodData?.data?.delivery || 0));
        },
        rawDineInAmount() {
            return this.extractNumericValue(this.paymentMethodData?.data?.dine_in || 0);
        },
        rawTakeawayAmount() {
            return this.extractNumericValue(this.paymentMethodData?.data?.takeaway || 0);
        },
        rawDeliveryAmount() {
            return this.extractNumericValue(this.paymentMethodData?.data?.delivery || 0);
        },
        chartData() {
            if (!this.paymentMethodData?.data) return null;

            const dineInAmount = this.rawDineInAmount;
            const takeawayAmount = this.rawTakeawayAmount;
            const deliveryAmount = this.rawDeliveryAmount;
            const totalAmount = dineInAmount + takeawayAmount + deliveryAmount;

            return {
                dineInAmount,
                takeawayAmount,
                deliveryAmount,
                totalAmount,
                dineInPercentage: totalAmount > 0 ? (dineInAmount / totalAmount) * 100 : 0,
                takeawayPercentage: totalAmount > 0 ? (takeawayAmount / totalAmount) * 100 : 0,
                deliveryPercentage: totalAmount > 0 ? (deliveryAmount / totalAmount) * 100 : 0
            };
        },
        chartOptions() {
            if (!this.chartData) return null;

            return {
                series: [
                    this.chartData.dineInPercentage, 
                    this.chartData.takeawayPercentage, 
                    this.chartData.deliveryPercentage
                ],
                chart: {
                    type: 'pie',
                    height: 300,
                    width: 320,
                    fontFamily: 'inherit',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        }
                    }
                },
                labels: ['Dine In', 'Takeaway', 'Delivery'],
                colors: ['#60A5FA', '#34D399', '#A78BFA'],
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    floating: false,
                    fontSize: '13px',
                    fontFamily: 'inherit',
                    markers: {
                        width: 10,
                        height: 10,
                        radius: 5
                    },
                    itemMargin: {
                        horizontal: 10,
                        vertical: 2
                    },
                    formatter: function(seriesName, opts) {
                        return `${seriesName}: ${opts.w.globals.series[opts.seriesIndex].toFixed(1)}%`;
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '0%'
                        },
                        expandOnClick: false
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val.toFixed(1) + '%';
                    },
                    style: {
                        fontSize: '14px',
                        fontFamily: 'inherit',
                        fontWeight: 'bold',
                        colors: ['#fff']
                    },
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 2,
                        left: 0,
                        blur: 3,
                        opacity: 0.2
                    }
                },
                tooltip: {
                    enabled: true,
                    y: {
                        formatter: (value, { seriesIndex }) => {
                            const amount = seriesIndex === 0 ? this.dineInAmount : 
                                          seriesIndex === 1 ? this.takeawayAmount : 
                                          this.deliveryAmount;
                            return `${amount} (${value.toFixed(1)}%)`;
                        }
                    },
                    style: {
                        fontSize: '14px',
                        fontFamily: 'inherit'
                    }
                },
                stroke: {
                    width: 2,
                    colors: ['#fff']
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 260,
                            width: 260
                        },
                        legend: {
                            position: 'bottom',
                            floating: false,
                            fontSize: '12px'
                        }
                    }
                }]
            };
        }
    },
    mounted() {
        this.date = [startOfMonth(new Date()), endOfMonth(new Date())];
        this.fetchData();
    },
    methods: {
        async handleDateChange(dates) {
            if (!dates) {
                console.log('No dates selected');
                return;
            }
            
            console.log('Raw dates from picker:', dates);
            
            try {
                this.loading = true;
                this.error = null;
                this.selectedPreset = '';
                
                // Convert UTC dates to local dates and format them
                const startDate = new Date(dates[0]);
                const endDate = new Date(dates[1]);
                
                // Format dates for API in YYYY-MM-DD format
                const formattedStartDate = format(startDate, 'yyyy-MM-dd');
                const formattedEndDate = format(endDate, 'yyyy-MM-dd');
                
                console.log('Formatted dates:', {
                    start: formattedStartDate,
                    end: formattedEndDate
                });
                
                const params = {
                    date_range: 'custom',
                    start_date: formattedStartDate,
                    end_date: formattedEndDate
                };
                
                console.log('API request params:', params);
                
                // Make API call
                const response = await this.$store.dispatch('dashboard/paymentMethodBreakdown', params);
                console.log('API response:', response);

                // Reset error if successful
                this.error = null;
            } catch (error) {
                console.error('Detailed error:', {
                    message: error.message,
                    response: error.response?.data,
                    status: error.response?.status,
                    fullError: error
                });
                
                if (error.response?.data?.message) {
                    this.error = error.response.data.message;
                } else if (error.message) {
                    this.error = error.message;
                } else {
                    this.error = 'Failed to fetch payment method data';
                }
            } finally {
                this.loading = false;
            }
        },
        async handlePresetChange() {
            if (!this.selectedPreset) {
                console.log('No preset selected');
                return;
            }
            
            console.log('Selected preset:', this.selectedPreset);
            
            try {
                this.loading = true;
                this.error = null;
                
                let dateRange;
                switch(this.selectedPreset) {
                    case 'today':
                        dateRange = [startOfDay(new Date()), endOfDay(new Date())];
                        break;
                    case 'this_week':
                        dateRange = [startOfWeek(new Date()), endOfWeek(new Date())];
                        break;
                    case 'this_month':
                        dateRange = [startOfMonth(new Date()), endOfMonth(new Date())];
                        break;
                    case 'this_year':
                        dateRange = [startOfYear(new Date()), endOfYear(new Date())];
                        break;
                }
                
                if (dateRange) {
                    console.log('Date range for preset:', {
                        start: format(dateRange[0], 'yyyy-MM-dd'),
                        end: format(dateRange[1], 'yyyy-MM-dd')
                    });
                    
                    this.date = dateRange;
                    const response = await this.$store.dispatch('dashboard/paymentMethodBreakdown', {
                        date_range: this.selectedPreset
                    });
                    console.log('API response for preset:', response);
                }
            } catch (error) {
                console.error('Detailed preset error:', {
                    message: error.message,
                    response: error.response?.data,
                    status: error.response?.status,
                    fullError: error
                });
                
                if (error.response?.data?.message) {
                    this.error = error.response.data.message;
                } else if (error.message) {
                    this.error = error.message;
                } else {
                    this.error = 'Failed to fetch payment method data';
                }
            } finally {
                this.loading = false;
            }
        },
        async fetchData() {
            try {
                console.log('Starting fetchData for payment method breakdown');
                this.loading = true;
                this.error = null;
                
                let params = {};
                
                if (this.selectedPreset) {
                    console.log('Using preset date range:', this.selectedPreset);
                    params = {
                        date_range: this.selectedPreset
                    };
                } else if (this.date) {
                    console.log('Using custom date range');
                    const startDate = format(this.date[0], 'yyyy-MM-dd');
                    const endDate = format(this.date[1], 'yyyy-MM-dd');
                    
                    console.log('Date range:', { startDate, endDate });
                    
                    params = {
                        date_range: 'custom',
                        start_date: startDate,
                        end_date: endDate
                    };
                }
                
                console.log('API request params:', params);
                
                // Make API call and add explicit debugging
                console.log('Calling API with action: dashboard/paymentMethodBreakdown');
                const response = await this.$store.dispatch('dashboard/paymentMethodBreakdown', params);
                console.log('API response received:', response);
                
                // Additional checking for the expected data structure
                if (!response) {
                    console.error('Response is empty or null');
                    this.error = 'No data received from API';
                    return;
                }
                
                if (!response.data) {
                    console.error('Response has no data property:', response);
                    this.error = 'Invalid data structure received from API';
                    return;
                }
                
                console.log('Payment method data:', response.data);
            } catch (error) {
                console.error('Error details:', {
                    message: error.message,
                    stack: error.stack,
                    response: error.response?.data,
                    status: error.response?.status
                });
                
                this.error = error.response?.data?.message || 'Failed to fetch payment method data';
            } finally {
                this.loading = false;
            }
        },
        currencyFormat(amount) {
            const settings = this.$store.getters['frontendSetting/lists'];
            if (!settings) return '0';
            
            // Handle null, undefined, or invalid input
            if (amount === null || amount === undefined) {
                amount = 0;
            }
            
            // Convert to number and handle NaN
            const numericAmount = Number(amount);
            if (isNaN(numericAmount)) {
                return '0';
            }
            
            return appService.currencyFormat(
                numericAmount,
                settings.site_digit_after_decimal_point,
                settings.site_default_currency_symbol,
                settings.site_currency_position
            );
        },
        extractNumericValue(value) {
            if (!value) return 0;
            if (typeof value === 'number') return value;
            // Remove currency symbol and any non-numeric characters except decimal point
            const numStr = value.replace(/[^0-9.-]/g, '');
            const num = parseFloat(numStr);
            return isNaN(num) ? 0 : num;
        }
    }
}
</script>

<style scoped>
.loading-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #f3f3f3;
    border-radius: 50%;
    border-top: 3px solid #3498db;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.stat-card {
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.stat-icon {
    transition: all 0.3s ease;
}

.stat-card:hover .stat-icon {
    transform: scale(1.1);
}

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
    
    .stat-card {
        padding: 1rem;
    }
}
</style> 