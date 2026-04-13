<template>
    <Modal :show="isOpen" @close="closeModal">
        <div class="p-4 sm:p-6">
            <h3 class="db-modal-title">{{ $t('label.edit_order') }}</h3>
            <div v-if="loading.isActive" class="flex justify-center my-4">
                <LoadingComponent />
            </div>
            <form v-else @submit.prevent="updateOrder" class="mt-4">
                <div class="space-y-4">
                    <!-- Order Status -->
                    <div>
                        <label class="db-field-title">{{ $t('label.status') }}</label>
                        <vue-select 
                            class="db-field-control f-b-custom-select"
                            v-model="form.status"
                            :options="[
                                { id: enums.orderStatusEnum.ACCEPT, name: $t('label.accept') },
                                { id: enums.orderStatusEnum.PROCESSING, name: $t('label.processing') },
                                { id: enums.orderStatusEnum.DELIVERED, name: $t('label.delivered') },
                            ]"
                            label-by="name"
                            value-by="id"
                            :searchable="true"
                            :clearable="false"
                        />
                    </div>

                    <!-- Rest of your form fields... -->

                    <div class="mt-5 flex justify-end gap-2">
                        <button 
                            type="button"
                            class="db-btn bg-gray-500 text-white"
                            @click="closeModal"
                        >
                            {{ $t('button.cancel') }}
                        </button>
                        <button 
                            type="submit"
                            class="db-btn bg-primary text-white"
                            :disabled="loading.isActive"
                        >
                            {{ $t('button.update') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script>
import { ref, reactive, onMounted, watch } from 'vue';
import axios from 'axios';
import Modal from '../Modal.vue';
import LoadingComponent from '../LoadingComponent.vue'
import orderStatusEnum from '../../../../enums/modules/orderStatusEnum';
import alertService from '../../../../services/alertService';

export default {
    name: 'OrderEditModal',
    components: { 
        Modal,
        LoadingComponent
    },
    props: {
        orderId: {
            type: Number,
            required: true
        },
        modelValue: {
            type: Boolean,
            default: false
        }
    },
    emits: ['update:modelValue', 'updated'],
    setup(props, { emit }) {
        const isOpen = ref(props.modelValue);
        const loading = reactive({
            isActive: false
        });
        const form = reactive({
            status: null,
            user_id: null,
            items: [],
            total_amount_price: 0
        });

        // Watch for changes in modelValue prop
        watch(() => props.modelValue, (newValue) => {
            isOpen.value = newValue;
            if (newValue) {
                fetchOrderDetails();
            }
        });

        // Watch for changes in isOpen
        watch(isOpen, (newValue) => {
            emit('update:modelValue', newValue);
        });

        const fetchOrderDetails = async () => {
            try {
                loading.isActive = true;
                const response = await axios.get(`/api/admin/pos-orders/${props.orderId}`);
                
                if (!response || !response.data) {
                    throw new Error('Invalid response from server');
                }

                const orderData = response.data.data || response.data;
                
                if (!orderData) {
                    throw new Error('No order data received');
                }

                form.status = orderData.status || null;
                form.user_id = orderData.user_id || null;
                form.items = Array.isArray(orderData.items) ? orderData.items : [];
                form.total_amount_price = orderData.total_amount_price || 0;
                
            } catch (error) {
                console.error('Error fetching order details:', error);
                const errorMessage = error.response?.data?.message || error.message || 'Failed to fetch order details';
                alertService.error(errorMessage);
                closeModal();
            } finally {
                loading.isActive = false;
            }
        };

        const calculateTotal = () => {
            form.total_amount_price = form.items.reduce((total, item) => {
                return total + (item.quantity * item.unit_price);
            }, 0);
        };

        const updateOrder = async () => {
            try {
                loading.isActive = true;
                await axios.put(`/api/admin/pos-orders/${props.orderId}`, form);
                alertService.success(null, 'Order updated successfully');
                emit('updated');
                closeModal();
            } catch (error) {
                console.error('Error updating order:', error);
                const errorMessage = error.response?.data?.message || error.message || 'Failed to update order';
                alertService.error(errorMessage);
            } finally {
                loading.isActive = false;
            }
        };

        const closeModal = () => {
            isOpen.value = false;
            form.status = null;
            form.user_id = null;
            form.items = [];
            form.total_amount_price = 0;
        };

        return {
            isOpen,
            loading,
            form,
            enums: { orderStatusEnum },
            closeModal,
            updateOrder,
            calculateTotal
        };
    }
}
</script>