<template>
    <a-modal 
        :open="open" 
        :title="isEditing ? 'Edit Poll' : 'Create Poll'" 
        @ok="submitForm" 
        @cancel="handleCancel"
        :confirmLoading="form.processing"
        width="750px"
    >
        <a-form layout="vertical" class="pt-4">
            <!-- POLL QUESTION -->
            <a-form-item label="Poll Question" required>
                <a-textarea 
                    v-model:value="form.question" 
                    :rows="3" 
                    placeholder="Enter poll question..." 
                />
            </a-form-item>

            <!-- POLL TYPE -->
            <a-form-item label="Poll Type" required>
                <a-radio-group v-model:value="form.type" @change="handleTypeChange">
                    <a-radio value="radio">Single Choice (Radio Button)</a-radio>
                    <a-radio value="checkbox">Multiple Choice (Checkboxes)</a-radio>
                </a-radio-group>
            </a-form-item>

            <!-- OPTIONS & CORRECT ANSWERS LIST -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-3">
                    <label class="font-semibold text-gray-700">
                        Options & Correct Answer Selection <span class="text-red-500">*</span>
                    </label>
                    <span class="text-xs text-gray-500">
                        {{ form.type === 'radio' ? 'Pick 1 correct answer' : 'Pick at least 1 correct answer' }}
                    </span>
                </div>

                <!-- RADIO WRAPPER (SINGLE CHOICE) -->
                <div v-if="form.type === 'radio'" class="w-full flex flex-col gap-3">
                    <div 
                        v-for="(option, index) in form.options" 
                        :key="index" 
                        class="flex items-center justify-between gap-4 p-3 border rounded-lg bg-gray-50 hover:bg-gray-100/80 transition-colors"
                    >
                        <!-- LEFT ALIGNED: Key Label & Input -->
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <span class="font-bold text-gray-700 w-6 flex-shrink-0 text-right select-none">
                                {{ option.key }}:
                            </span>
                            <a-input 
                                type="input"
                                v-model:value="option.text" 
                                :placeholder="`Option ${option.key} text`" 
                                class="w-full"
                            />
                        </div>

                        <!-- RIGHT ALIGNED: Correct Answer Selection & Remove -->
                        <div class="flex items-center gap-4 flex-shrink-0 border-l pl-4 border-gray-200">
                            <a-radio 
                                :checked="selectedRadioAnswer === option.key"
                                @change="selectedRadioAnswer = option.key"
                            >
                                <span class="text-sm font-medium text-gray-700">Correct Answer</span>
                            </a-radio>

                            <a-button 
                                type="text" 
                                danger 
                                :disabled="form.options.length <= 2"
                                @click="removeOption(index)"
                            >
                                Remove
                            </a-button>
                        </div>
                    </div>
                </div>

                <!-- CHECKBOX WRAPPER (MULTIPLE CHOICE) -->
                <a-checkbox-group 
                    v-else 
                    v-model:value="form.answer" 
                    class="w-full flex flex-col gap-3"
                >
                    <div 
                        v-for="(option, index) in form.options" 
                        :key="index" 
                        class="flex items-center justify-between gap-4 p-3 border rounded-lg bg-gray-50 hover:bg-gray-100/80 transition-colors"
                    >
                        <!-- LEFT ALIGNED: Key Label & Input -->
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <span class="font-bold text-gray-700 w-6 flex-shrink-0 text-right select-none">
                                {{ option.key }}:
                            </span>
                            <a-input 
                                v-model:value="option.text" 
                                :placeholder="`Option ${option.key} text`" 
                                class="w-full"
                            />
                        </div>

                        <!-- RIGHT ALIGNED: Correct Answer Selection & Remove -->
                        <div class="flex items-center gap-4 flex-shrink-0 border-l pl-4 border-gray-200">
                            <a-checkbox :value="option.key">
                                <span class="text-sm font-medium text-gray-700 ml-1">Correct Answer</span>
                            </a-checkbox>

                            <a-button 
                                type="text" 
                                danger 
                                :disabled="form.options.length <= 2"
                                @click="removeOption(index)"
                            >
                                Remove
                            </a-button>
                        </div>
                    </div>
                </a-checkbox-group>

                <!-- ADD OPTION BUTTON (Max 5: A, B, C, D, E) -->
                <div class="mt-3">
                    <a-button 
                        type="dashed" 
                        block 
                        :disabled="form.options.length >= 5"
                        @click="addOption"
                    >
                        + Add Option
                    </a-button>
                </div>
            </div>

            <!-- STATUS SWITCH -->
            <a-form-item label="Status">
                <a-switch v-model:checked="form.status" />
                <span class="ml-2 font-medium text-gray-700">
                    {{ form.status ? 'Active' : 'Inactive' }}
                </span>
            </a-form-item>
        </a-form>
    </a-modal>
</template>

<script>
import { message } from 'ant-design-vue';

export default {
    name: 'PollForm',
    props: {
        open: {
            type: Boolean,
            default: false
        },
        poll: {
            type: Object,
            default: null
        }
    },
    emits: ['update:open', 'saved'],
    data() {
        return {
            form: this.$inertia.form({
                question: '',
                type: 'radio',
                option_a: '',
                option_b: '',
                option_c: '',
                option_d: '',
                option_e: '',
                options: [], // UI-only state to drive dynamic input rows
                answer: [],   // Array expected by Eloquent $casts ['answer' => 'array']
                status: true
            })
        };
    },
    computed: {
        isEditing() {
            return Boolean(this.poll && this.poll.id);
        },
        selectedRadioAnswer: {
            get() {
                return Array.isArray(this.form.answer) && this.form.answer.length > 0 
                    ? this.form.answer[0] 
                    : null;
            },
            set(val) {
                this.form.answer = val ? [val] : [];
            }
        }
    },
    watch: {
        open(val) {
            if (val) {
                this.initForm();
            }
        }
    },
    methods: {
        initForm() {
            if (this.isEditing) {
                this.form.question = this.poll.question || '';
                this.form.type = this.poll.type || 'radio';
                this.form.status = Boolean(this.poll.status);

                // Reconstruct options array from option_a through option_e columns
                const optionsList = [
                    { key: 'A', text: this.poll.option_a || '' },
                    { key: 'B', text: this.poll.option_b || '' },
                    { key: 'C', text: this.poll.option_c || '' },
                    { key: 'D', text: this.poll.option_d || '' },
                    { key: 'E', text: this.poll.option_e || '' }
                ];

                // Keep non-empty options (always retain at least A & B)
                this.form.options = optionsList.filter(
                    opt => opt.text.trim() !== '' || ['A', 'B'].includes(opt.key)
                );

                // Normalize answer cast to array
                if (Array.isArray(this.poll.answer)) {
                    this.form.answer = [...this.poll.answer];
                } else if (typeof this.poll.answer === 'string' && this.poll.answer) {
                    this.form.answer = [this.poll.answer];
                } else {
                    this.form.answer = [];
                }
            } else {
                this.form.reset();
                this.form.question = '';
                this.form.type = 'radio';
                this.form.status = true;
                this.form.options = [
                    { key: 'A', text: '' },
                    { key: 'B', text: '' },
                    { key: 'C', text: '' },
                    { key: 'D', text: '' }
                ];
                this.form.answer = [];
            }
        },
        handleTypeChange() {
            if (this.form.type === 'radio' && this.form.answer.length > 1) {
                this.form.answer = [this.form.answer[0]];
            }
        },
        addOption() {
            if (this.form.options.length >= 5) return;
            const nextChar = String.fromCharCode(65 + this.form.options.length);
            this.form.options.push({ key: nextChar, text: '' });
        },
        removeOption(index) {
            const removedKey = this.form.options[index].key;
            this.form.options.splice(index, 1);

            // Re-index remaining option keys (A, B, C...)
            this.form.options.forEach((opt, i) => {
                opt.key = String.fromCharCode(65 + i);
            });

            // Clean up removed key from answer array
            this.form.answer = this.form.answer.filter(k => k !== removedKey);
        },
        syncOptionsToFormPayload() {
            const keys = ['a', 'b', 'c', 'd', 'e'];
            
            // Reset all option fields
            keys.forEach(k => {
                this.form[`option_${k}`] = null;
            });

            // Map UI list to explicit columns
            this.form.options.forEach((opt, i) => {
                if (keys[i]) {
                    this.form[`option_${keys[i]}`] = opt.text;
                }
            });
        },
        validateForm() {
            if (!this.form.question.trim()) {
                message.error('Please enter a poll question.');
                return false;
            }
            if (this.form.options.some(opt => !opt.text.trim())) {
                message.error('Please ensure all option fields are filled out.');
                return false;
            }
            if (!this.form.answer || this.form.answer.length === 0) {
                message.error('Please select at least one correct answer.');
                return false;
            }
            return true;
        },
        submitForm() {
            if (!this.validateForm()) return;

            // Map UI option list into option_a..option_e columns before submitting
            this.syncOptionsToFormPayload();

            if (this.isEditing) {
                this.form.put(route('widget.admin.polls.update', this.poll.id), {
                    onSuccess: () => {
                        message.success('Poll updated successfully');
                        this.$emit('update:open', false);
                        this.$emit('saved');
                    }
                });
            } else {
                this.form.post(route('widget.admin.poll.store'), {
                    onSuccess: () => {
                        message.success('Poll created successfully');
                        this.$emit('update:open', false);
                        this.$emit('saved');
                    }
                });
            }
        },
        handleCancel() {
            this.$emit('update:open', false);
        }
    }
}
</script>