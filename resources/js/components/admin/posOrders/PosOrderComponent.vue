<template>
    <div class="row">
        <div class="col-12">
            <BreadcrumbComponent />
        </div>
     
        <router-view></router-view>
    </div>
</template>

<script>
import BreadcrumbComponent from "../components/BreadcrumbComponent";
import alertService from "../../../services/alertService";
import { mapGetters } from 'vuex';

export default {
    name: "PosOrderComponent",
    components: {
        BreadcrumbComponent,
    },
    data() {
        return {
            orderMethodEnum: {
                DINE_IN: 1,
                TAKEAWAY: 2,
                DELIVERY: 3
            },
            orderMethods: [
                { label: 'Dine In', value: 1 },
                { label: 'Takeaway', value: 2 },
                { label: 'Delivery', value: 3 }
            ],
            selectedMethod: 1, // Default to Dine In
            deliveryOption: 'existing',
            selectedCustomer: null,
            showNewCustomerDialog: false,
            newCustomer: {
                name: '',
                phone: '',
                email: '',
                address: ''
            }
        }
    },
    computed: {
        ...mapGetters({
            customers: 'user/lists' // Assuming you have a user store module
        })
    },
    mounted() {
        // Fetch customers list when component mounts
        this.fetchCustomers();
    },
    methods: {
        selectOrderMethod(method) {
            this.selectedMethod = method;
            if (method === this.orderMethodEnum.DELIVERY) {
                this.deliveryOption = 'existing';
            }
        },
        async fetchCustomers() {
            try {
                await this.$store.dispatch('user/lists', {
                    status: 1, // Assuming 1 is active status
                    paginate: 0 // Get all customers without pagination
                });
            } catch (error) {
                alertService.error(error.response?.data?.message || 'Failed to fetch customers');
            }
        },
        handleCustomerSelect(customerId) {
            const customer = this.customers.find(c => c.id === customerId);
            if (customer) {
                // Handle customer selection, e.g., update order details
                console.log('Selected customer:', customer);
            }
        },
        async saveNewCustomer() {
            try {
                // Add validation here if needed
                const response = await this.$store.dispatch('user/store', this.newCustomer);
                
                // Update the selected customer with the newly created one
                this.selectedCustomer = response.data.id;
                
                // Close the dialog and reset form
                this.showNewCustomerDialog = false;
                this.newCustomer = {
                    name: '',
                    phone: '',
                    email: '',
                    address: ''
                };
                
                // Show success message
                alertService.success('Customer added successfully');
                
                // Refresh customers list
                await this.fetchCustomers();
            } catch (error) {
                alertService.error(error.response?.data?.message || 'Failed to add customer');
            }
        }
    }
};
</script>

<style scoped>
.db-btn {
    @apply px-4 py-2 rounded-md transition-colors duration-200 font-medium;
}
.db-btn:hover {
    @apply opacity-90;
}
.db-field-title {
    @apply block text-sm font-medium text-gray-700 mb-1;
}
.db-field-control {
    @apply block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50;
}
</style>
