<template>
    <button class="db-btn-outline sm danger modal-btn m-0.5">
        <i class="lab lab-delete"></i>
        <span>{{ $t("button.delete") }}</span>
    </button>
</template>

<script>
export default {
    name: "EditButtonPOS",
    props: {
        orderId: {
            type: Number,
            required: true
        }
    }
}
</script>

<style scoped>

</style>


<!-- <button 
@click="editOrder(order.id)"
class="db-table-action-btn text-primary  hover:text-white hover:bg-primary"
v-if="permissionChecker('pos-orders')"
:title="$t('button.edit')">
<i class="lab lab-edit-line"></i>
</button> -->