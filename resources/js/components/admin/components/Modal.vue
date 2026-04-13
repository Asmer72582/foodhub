<template>
    <TransitionRoot appear :show="show" as="template">
        <Dialog as="div" @close="$emit('close')" class="relative z-50">
            <!-- Background overlay -->
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black/30" aria-hidden="true" />
            </TransitionChild>

            <!-- Modal panel -->
            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel class="w-full max-w-2xl transform overflow-hidden rounded-lg bg-white text-left align-middle shadow-xl transition-all">
                            <!-- Close button -->
                            <button 
                                type="button"
                                class="absolute right-4 top-4 text-gray-400 hover:text-gray-500"
                                @click="$emit('close')"
                            >
                                <span class="sr-only">Close</span>
                                <i class="lab lab-close-line lab-font-size-22"></i>
                            </button>

                            <!-- Modal content -->
                            <slot></slot>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script>
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue'

export default {
    name: 'Modal',
    components: {
        Dialog,
        DialogPanel,
        TransitionChild,
        TransitionRoot
    },
    props: {
        show: {
            type: Boolean,
            required: true
        }
    },
    emits: ['close']
}
</script>

<style scoped>
.fixed {
    position: fixed;
}

.inset-0 {
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
}

.z-50 {
    z-index: 50;
}

.overflow-y-auto {
    overflow-y: auto;
}

.bg-black\/30 {
    background-color: rgba(0, 0, 0, 0.3);
}

.flex {
    display: flex;
}

.min-h-full {
    min-height: 100%;
}

.items-center {
    align-items: center;
}

.justify-center {
    justify-content: center;
}

.p-4 {
    padding: 1rem;
}

.text-center {
    text-align: center;
}

.w-full {
    width: 100%;
}

.max-w-2xl {
    max-width: 42rem;
}

.rounded-lg {
    border-radius: 0.5rem;
}

.bg-white {
    background-color: #ffffff;
}

.text-left {
    text-align: left;
}

.align-middle {
    vertical-align: middle;
}

.shadow-xl {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.absolute {
    position: absolute;
}

.right-4 {
    right: 1rem;
}

.top-4 {
    top: 1rem;
}

.text-gray-400 {
    color: #9ca3af;
}

.text-gray-400:hover {
    color: #6b7280;
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border-width: 0;
}
</style>