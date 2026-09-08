<template>
    <div class="font-sans text-gray-900 antialiased">
        <div class="p-2 w-full h-auto sm:w-auto sm:h-auto max-w-[800px] mx-auto">
            <div class="container" v-if="poll">
                <a-form
                    name="myPoll"
                    :model="poll"
                    @finish="onFinish"
                    @finishFailed="onFinishFailed"
                >
                    <a-form-item name="username" label="Nick Name:">
                        <a-input type="input" v-model:value="poll.username" />
                    </a-form-item>
                    
                    <div v-html="poll.question" class="w-full h-auto overflow-hidden" />
                    
                    <div style="line-height: 2;">
                        <div :class="selectedAnswer == 'A' ? 'text-red-500' : ''">
                            A: <input type="radio" v-model="poll.answer" value="A" />&nbsp;{{ poll.option_a }}
                        </div>
                        <div :class="selectedAnswer == 'B' ? 'text-red-500' : ''">
                            B: <input type="radio" v-model="poll.answer" value="B" />&nbsp;{{ poll.option_b }}
                        </div>
                        <div :class="selectedAnswer == 'C' ? 'text-red-500' : ''">
                            C: <input type="radio" v-model="poll.answer" value="C" />&nbsp;{{ poll.option_c }}
                        </div>
                        <div :class="selectedAnswer == 'D' ? 'text-red-500' : ''">
                            D: <input type="radio" v-model="poll.answer" value="D" />&nbsp;{{ poll.option_d }}
                        </div>
                    </div>
                    
                    <a-form-item :wrapper-col="{ offset: 8, span: 16 }">
                        <a-button 
                            type="primary" 
                            html-type="submit" 
                            :disabled="!poll.answer || selectedAnswer != null"
                        >
                            Submit
                        </a-button>
                    </a-form-item>
                </a-form>
            </div>
            <div v-else>
                Nothing to show
            </div>
        </div>
    </div>
</template>

<script>
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';

export default {
    components: {
        WebsiteLayout,
    },
    props: ['poll'],
    data() {
        return {
            radioStyle: {
                display: 'flex',
                height: '30px',
                lineHeight: '30px'
            },
            tabActiveKey: "question",
            selectedAnswer: null,
        }
    },
    created() {
        if (this.poll) {
            this.poll.username = 'User';
            this.poll.poll_id = this.poll.id;
            this.poll.answer = null;
        }
    },
    methods: {
        onFinish() {
            // Send payload data directly in the 2nd argument
            this.$inertia.post(
                route("widget.poll.vote"), 
                {
                    poll_id: this.poll.id,
                    username: this.poll.username,
                    answer: this.poll.answer
                },
                {
                    onSuccess: () => {
                        this.selectedAnswer = this.poll.answer;
                    },
                    onError: (errors) => {
                        console.error('Validation errors:', errors);
                    }
                }
            );
        },
        onFinishFailed(errorInfo) {
            console.log(errorInfo);
        }
    }
}
</script>