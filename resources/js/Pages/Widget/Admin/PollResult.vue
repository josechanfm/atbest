<template>
    <div class="font-sans text-gray-900 antialiased">
        <div class="container mx-auto pt-5">
            <div class="card-container">
                <a-tabs v-model:activeKey="tabActiveKey" type="card">
                    <a-tab-pane key="question" tab="Question">
                        <div class="bg-white relative shadow rounded-lg overflow-x-auto">
                            <div v-html="poll.question" />
                        </div>
                        <div style="line-height: 2;">
                            <div>A: <input type="radio" v-model="selected" value="A">&nbsp;{{ poll.option_a }}</input></div>
                            <div>B: <input type="radio" v-model="selected" value="B">&nbsp;{{ poll.option_b }}</input></div>
                            <div>C: <input type="radio" v-model="selected" value="C">&nbsp;{{ poll.option_c }}</input></div>
                            <div>D: <input type="radio" v-model="selected" value="D">&nbsp;{{ poll.option_d }}</input></div>
                        </div>
                    </a-tab-pane>
                    <a-tab-pane key="result" tab="Result">
                        <a-row>
                            <a-col :span="8">
                                <a :href="route('widget.polling', { uuid: poll.uuid })" target="Polling">
                                    <a-qrcode :value="route('widget.polling', { uuid: poll.uuid })" />
                                </a>
                                
                                <div class="pt-5 pb-5"><a-button @click="showIndividuals = !showIndividuals">Show
                                        Individuals</a-button></div>
                                <div><a-button @click="clearResponses(poll)">Clear Responses</a-button></div>
                                <div class="pt-5">
                                    <div class="text-xl">Timer:</div>
                                    <a-form :label-col="{ span: 4 }">
                                        <a-form-item label="Minutes" for="min">
                                            <a-input type="number" v-model:value="timer.minutes" min="0" max="59"
                                                style="width:100px" />
                                        </a-form-item>
                                        <a-form-item label="Seconds" for="min">
                                            <a-input type="number" v-model:value="timer.secondes" min="0" max="59"
                                                style="width:100px" />
                                        </a-form-item>
                                        <a-button @click="timerStart" :disabled="timer.started">Start</a-button>
                                        <a-button @click="timerStop" :disabled="!timer.started">Stop</a-button>
                                        <a-button @click="timerReset">Reset</a-button>
                                    </a-form>

                                </div>
                            </a-col>
                            <a-col :span="8">
                                <div class="text-9xl">{{ timer.display }}</div>
                                <template v-for="(num, ans) in groupSelected">
                                    <div class="text-4xl">{{ ans }}:{{ num }}</div>
                                </template>
                                <div class="card flex justify-center">
                                    <DoughnutChart :chartData="testData" :options="chartOptions" />
                                </div>
                            </a-col>
                            <a-col :span="8" v-show="showIndividuals">
                                <table class="text-2xl">
                                    <tr v-for="response in poll.responses">
                                        <td>
                                            <a-tooltip>
                                                <template #title>{{ response.ip_address }}</template>
                                                {{ response.username }}
                                            </a-tooltip>
                                        </td>
                                        <td>&nbsp;:&nbsp;</td>
                                        <td>{{ response.answer }}</td>
                                    </tr>
                                </table>
                            </a-col>
                        </a-row>

                    </a-tab-pane>
                </a-tabs>
            </div>
        </div>
    </div>


</template>


<script>
import WebLayout from '@/Layouts/WebLayout.vue';
import { message } from 'ant-design-vue';
import { DoughnutChart } from 'vue-chart-3';
import { Chart, registerables } from "chart.js";

Chart.register(...registerables);

export default {
    components: {
        WebLayout,
        message,
        DoughnutChart
    },
    props: ['poll'],
    data() {
        return {
            radioStyle: {
                display: 'flex',
                height: '30px',
                lineHeight: '30px'
            },
            timer: {
                ticker: null,
                count: 0,
                minutes: 1,
                secondes: 0,
                started: false
            },
            updateInterval: null,
            tabActiveKey: "question",
            selected: null,
            showIndividuals: false,
            groupSelected: {
                A: 0,
                B: 0,
                C: 0,
                D: 0
            },
            testData: {
                labels: ['A', 'B', 'C', 'D'],
                datasets: [
                    {
                        data: [30, 40, 60, 70],
                        backgroundColor: ['#77CEFF', '#0079AF', '#123E6B', '#97B0C4', '#A5C8ED'],
                    },
                ],
            },
            chartOptions: {
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }

        }
    },
    created() {
    },
    mounted() {
        this.updatePollResult()

    },
    methods: {
        clearResponses(poll) {
            axios.post(route("widget.admin.poll.responseClear", poll))
                .then((resp) => {
                    poll.responses_count = 0
                    poll.responses = []
                    message.success(resp.data.message)
                });
        },
        updatePollResult() {
            axios.post(route("widget.admin.poll.responseAll", this.poll))
                .then((resp) => {
                    this.poll.responses = resp.data
                    this.groupSelected = { A: 0, B: 0, C: 0, D: 0 };
                    this.poll.responses.forEach(resp => {
                        this.groupSelected[resp.answer]++
                    })

                });
        },
        timerStart() {
            console.log('timer start')
            this.timerReset()
            this.timer.started = true
            this.timer.ticker = setInterval(() => {
                this.timer.count--
                this.timer.display = ('00' + Math.trunc(this.timer.count / 60)).slice(-2) + ":" + ('00' + this.timer.count % 60).slice(-2)
                if (this.timer.count == 0) {
                    clearInterval(this.timer.ticker)
                    console.log('timer up!');
                }
            }, 1000)
        },
        timerStop() {
            this.timer.started = false
            clearInterval(this.timer.ticker)
        },
        timerReset() {
            this.timer.count = (this.timer.minutes * 60) + parseInt(this.timer.secondes)
            this.timer.display = ('00' + Math.trunc(this.timer.count / 60)).slice(-2) + ":" + ('00' + this.timer.count % 60).slice(-2)
            console.log('timer reset')
        }
    },
    watch: {
        tabActiveKey() {
            if (this.tabActiveKey == 'result') {
                console.log('start interval')
                this.updateInterval = setInterval(this.updatePollResult, 5000)
            } else {
                console.log('clear interval')
                clearInterval(this.updateInterval)
            }
        }
    }
}

</script>
