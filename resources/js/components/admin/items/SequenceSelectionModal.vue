<template>
    <div class="modal" :id="modalId" tabindex="-1" role="dialog" aria-labelledby="sequenceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-custom-width" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sequenceModalLabel">{{ $t('label.reorder_items') }}</h5>
                    <button type="button" class="modal-close close" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="loading-overlay" v-if="loading.isActive">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    
                    <div class="sequence-container">
        <div class="sequence-flex-container">
            <div class="sequence-column left-column">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <p class="mb-0 fw-bold">{{ $t('label.available_items') }}</p>
                    <div class="search-container">
                        <input 
                            type="text" 
                            v-model="searchQuery" 
                            class="search-input" 
                            :placeholder="$t('label.search_items')" 
                        />
                        <i class="lab lab-search search-icon"></i>
                    </div>
                </div>
                <div class="available-items-container">
                                    <div v-for="item in filteredAvailableItems" 
                                        :key="item.id" 
                                        class="sequence-item" 
                                        @click="addToSequence(item)">
                                        <div class="sequence-name">{{ item.name }}</div>
                                        <div class="sequence-category">{{ item.category_name }}</div>
                                        <div class="sequence-action">
                                            <i class="lab lab-plus-circle lab-font-size-20"></i>
                                        </div>
                                    </div>
                                    <div v-if="filteredAvailableItems.length === 0" class="text-center py-3 text-muted">
                                        {{ searchQuery ? $t('label.no_search_results') : $t('label.no_items_available') }}
                                    </div>
                                </div>
                            </div>
            <div class="sequence-column right-column">
                <p class="mb-2 fw-bold">{{ $t('label.selected_sequence') }}</p>
                <div class="selected-items-container">
                                    <div v-for="(item, index) in sequenceItems" 
                                        :key="item.id" 
                                        class="sequence-item">
                                        <div class="sequence-number">{{ index + 1 }}</div>
                                        <div class="sequence-name">{{ item.name }}</div>
                                        <div class="sequence-action">
                                            <i class="lab lab-trash lab-font-size-20" @click="removeFromSequence(index)"></i>
                                        </div>
                                    </div>
                                    <div v-if="sequenceItems.length === 0" class="text-center py-3 text-muted">
                                        {{ $t('label.no_items_selected') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="db-btn py-2 bg-gray-600 text-white modal-close">
                        {{ $t('button.cancel') }}
                    </button>
                    <button type="button" class="db-btn py-2 bg-primary text-white" @click="saveSequence" :disabled="loading.isActive">
                        {{ $t('button.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import alertService from "../../../services/alertService";

export default {
    name: "SequenceSelectionModal",
    props: {
        modalId: {
            type: String,
            default: 'sequenceModal'
        },
        items: {
            type: Array,
            required: true
        },
        searchParams: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            allItems: [],
            sequenceItems: [],
            searchQuery: '',
            loading: {
                isActive: false
            }
        };
    },
    computed: {
        availableItems() {
            // Filter out items that are already in the sequence
            return this.allItems.filter(item => {
                return !this.sequenceItems.some(seqItem => seqItem.id === item.id);
            });
        },
        filteredAvailableItems() {
            if (!this.searchQuery) {
                return this.availableItems;
            }
            
            const query = this.searchQuery.toLowerCase();
            return this.availableItems.filter(item => {
                return (
                    (item.name && item.name.toLowerCase().includes(query)) ||
                    (item.category_name && item.category_name.toLowerCase().includes(query))
                );
            });
        }
    },
    watch: {
        items: {
            handler(newItems) {
                if (newItems && newItems.length) {
                    // Store all items for reference
                    this.allItems = JSON.parse(JSON.stringify(newItems));
                    // Start with an empty sequence
                    this.sequenceItems = [];
                }
            },
            immediate: true
        }
    },
    methods: {
        addToSequence(item) {
            // Add the item to the sequence list
            this.sequenceItems.push(JSON.parse(JSON.stringify(item)));
        },
        
        removeFromSequence(index) {
            // Remove the item from the sequence list
            this.sequenceItems.splice(index, 1);
        },
        
        clearSearch() {
            this.searchQuery = '';
        },
        
        saveSequence() {
            if (this.sequenceItems.length === 0) {
                alertService.error(this.$t('message.no_items_selected'));
                return;
            }
            
            if (this.sequenceItems.length !== this.allItems.length) {
                alertService.error(this.$t('message.all_items_must_be_selected'));
                return;
            }
            
            this.loading.isActive = true;
            const sortedIds = this.sequenceItems.map(item => item.id);
            
            this.$store.dispatch('item/sortItem', {
                form: { item_id: sortedIds },
                search: this.searchParams
            }).then(() => {
                this.loading.isActive = false;
                alertService.successFlip(this.$t('message.sort_success'), this.$t('menu.items'));
                // Close the modal
                document.querySelector(`#${this.modalId}`).classList.remove('active');
                document.body.classList.remove('overflow-hidden');
                // Emit event to parent component
                this.$emit('sequence-updated');
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        }
    }
};
</script>

<style scoped>
.sequence-container {
    height: 75vh;
    max-height: 75vh;
    overflow: hidden;
}

.sequence-flex-container {
    display: flex;
    flex-direction: row;
    gap: 20px;
    width: 100%;
    height: 100%;
}

.sequence-column {
    flex: 1;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.left-column {
    margin-right: 10px;
}

.right-column {
    margin-left: 10px;
}

.available-items-container,
.selected-items-container {
    height: 65vh;
    max-height: 65vh;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.08);
    flex: 1;
}

.sequence-item {
    display: flex;
    align-items: center;
    padding: 14px 18px;
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.2s ease;
    margin-bottom: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.03);
    height: 60px;
    min-height: 60px;
    max-height: 60px;
    width: 100%;
}

.sequence-item:hover {
    background-color: #e9ecef;
    cursor: pointer;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.sequence-number {
    width: 36px;
    height: 36px;
    min-width: 36px;
    min-height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #e9ecef;
    border-radius: 50%;
    margin-right: 15px;
    font-weight: bold;
    color: #495057;
    flex-shrink: 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.sequence-name {
    flex: 1;
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    padding-right: 10px;
    width: 0; /* Force text truncation */
}

.sequence-category {
    color: #6c757d;
    margin-left: 10px;
    margin-right: 15px;
    font-size: 0.9em;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    width: 150px;
    min-width: 150px;
    max-width: 150px;
}

.sequence-action {
    color: #6c757d;
    cursor: pointer;
    flex-shrink: 0;
    transition: transform 0.2s;
    width: 30px;
    min-width: 30px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.sequence-action:hover {
    transform: scale(1.2);
}

.sequence-action i.lab-plus-circle {
    color: #28a745;
}

.sequence-action i.lab-trash {
    color: #dc3545;
}

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(255, 255, 255, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.search-container {
    position: relative;
    width: 250px;
    min-width: 250px;
    max-width: 250px;
}

.search-input {
    width: 100%;
    padding: 8px 12px 8px 35px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.search-input:focus {
    outline: none;
    border-color: var(--primary-color, #007bff);
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.search-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    font-size: 16px;
}

/* Make the modal 80% width */
.modal-custom-width {
    max-width: 80%;
    width: 80%;
    margin: 0 auto;
}

.modal-custom-width .modal-content {
    width: 100%;
    margin: 0 auto;
    height: auto;
    max-height: 90vh;
}

/* Responsive adjustments */
@media (max-width: 1200px) {
    .search-container {
        width: 220px;
        min-width: 220px;
        max-width: 220px;
    }
    
    .sequence-category {
        width: 120px;
        min-width: 120px;
        max-width: 120px;
    }
}

@media (max-width: 992px) {
    .search-container {
        width: 180px;
        min-width: 180px;
        max-width: 180px;
    }
    
    .available-items-container,
    .selected-items-container {
        height: 55vh;
        max-height: 55vh;
    }
}

@media (max-width: 768px) {
    .modal-custom-width {
        max-width: 95%;
        width: 95%;
    }
    
    .sequence-flex-container {
        flex-direction: column;
        height: auto;
    }
    
    .sequence-column {
        width: 100%;
        height: auto;
        margin: 0 0 20px 0;
    }
    
    .search-container {
        width: 100%;
        min-width: 100%;
        max-width: 100%;
        margin-top: 10px;
    }
    
    .available-items-container,
    .selected-items-container {
        height: 45vh;
        max-height: 45vh;
    }
    
    .sequence-item {
        padding: 12px 15px;
        height: 55px;
        min-height: 55px;
        max-height: 55px;
    }
    
    .sequence-category {
        width: 100px;
        min-width: 100px;
        max-width: 100px;
    }
}

@media (max-width: 576px) {
    .modal-custom-width {
        max-width: 100%;
        width: 100%;
    }
    
    .available-items-container,
    .selected-items-container {
        height: 40vh;
        max-height: 40vh;
    }
    
    .sequence-category {
        display: none;
        width: 0;
        min-width: 0;
        max-width: 0;
        margin: 0;
    }
    
    .sequence-name {
        font-size: 0.9em;
    }
    
    .sequence-item {
        height: 50px;
        min-height: 50px;
        max-height: 50px;
    }
    
    .sequence-number {
        width: 30px;
        height: 30px;
        min-width: 30px;
        min-height: 30px;
        margin-right: 10px;
    }
    
    .sequence-action {
        width: 25px;
        min-width: 25px;
    }
}
</style>