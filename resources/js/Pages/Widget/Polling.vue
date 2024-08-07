<template>
    <div class="font-sans text-gray-900 antialiased">
        <div class="container mx-auto pt-5">
            <div class="container" v-if="poll">
                <a-form
                    name="myPoll"
                    :model="poll"
                    @finish="onFinish"
                    @finishFialed="onFinishFialed"
                >
                <a-form-item name="username" label="Nick Name:">
                    <a-input v-model:value="poll.username"/>
                </a-form-item>
                
                <div v-html="poll.question" />
                <div style="line-height: 2;">
                    <div :class="selectedAnswer=='A'?'text-red-500':''">A: <input type="radio" v-model="poll.answer" value="A">&nbsp;{{ poll.option_a }}</input></div>
                    <div :class="selectedAnswer=='B'?'text-red-500':''">B: <input type="radio" v-model="poll.answer" value="B">&nbsp;{{ poll.option_b }}</input></div>
                    <div :class="selectedAnswer=='C'?'text-red-500':''">C: <input type="radio" v-model="poll.answer" value="C">&nbsp;{{ poll.option_c }}</input></div>
                    <div :class="selectedAnswer=='D'?'text-red-500':''">D: <input type="radio" v-model="poll.answer" value="D">&nbsp;{{ poll.option_d }}</input></div>
                </div>
                
                <a-form-item :wrapper-col="{ offset: 8, span: 16 }">
                    <a-button type="primary" html-type="submit" :disabled="selectedAnswer!=null">Submit</a-button>
                </a-form-item>

                </a-form>
            </div>
            <div v-else>
                noting to show
            </div>

        </div>
    </div>
</template>

<script>
import WebLayout from '@/Layouts/WebLayout.vue';

export default {
    components: {
        WebLayout,
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
            selectedAnswer:null,
        }
    },
    created() {
        this.poll.username='User';
        this.poll.poll_id=this.poll.id;
        this.poll.answer=null
    },
    methods: {
        onFinish(){
            axios.post(route("widget.poll.vote", this.poll))
            .then((resp) => {
                this.selectedAnswer=this.poll.answer
                console.log(resp.data)
            });
        },
        onFinishFialed(errorInfo){
            console.log(errorInfo)
        }
    }
}

</script>
