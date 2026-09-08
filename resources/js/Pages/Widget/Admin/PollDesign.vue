<template>
    <WidgetLayout title="Poll Presentation & Live Results">
        <div class="mx-auto py-6 px-4 sm:px-6 lg:px-8 font-sans antialiased text-gray-800 bg-gray-50/50 min-h-screen">
            <!-- Header Section -->
            <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between border-b pb-4 border-gray-200 bg-white p-6 rounded-2xl shadow-sm">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Live Poll Monitor</h1>
                    <p class="text-sm text-gray-500 mt-1">Manage presentation view, QR code access, and incoming live responses.</p>
                </div>
                <div class="mt-4 md:mt-0 flex items-center space-x-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                          :class="poll.status ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-gray-100 text-gray-700 ring-1 ring-gray-300/50'">
                        <span class="w-2 h-2 mr-1.5 rounded-full" :class="poll.status ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                        {{ poll.status ? 'Active' : 'Inactive' }}
                    </span>
                    <span class="text-sm text-gray-500 border-l pl-3 border-gray-200">
                        Total Responses: <strong class="text-gray-900 font-semibold">{{ poll.responses ? poll.responses.length : 0 }}</strong>
                    </span>
                </div>
            </div>

            <!-- Tab Navigation -->
            <a-tabs v-model:activeKey="tabActiveKey" type="line" class="bg-white rounded-2xl shadow-sm border border-gray-200/80 p-6">
                
                <!-- TAB 1: QUESTION DISPLAY -->
                <a-tab-pane key="question" tab="Presentation View">
                    <div class="max-w-3xl mx-auto py-6">
                        <!-- Question Card -->
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-8 text-white shadow-md mb-8">
                            <span class="uppercase tracking-widest text-xs font-bold text-blue-200 block mb-2">Question</span>
                            <h2 class="text-2xl md:text-3xl font-bold leading-snug" v-html="poll.question"></h2>
                        </div>

                        <!-- Option Cards -->
                        <div class="space-y-4">
                            <div 
                                v-for="option in availableOptions" 
                                :key="option.key"
                                class="flex items-center p-4 rounded-xl border border-gray-200 bg-white hover:border-blue-400 hover:shadow-md transition-all duration-200 cursor-pointer"
                                :class="{ 'ring-2 ring-blue-500 bg-blue-50/40 border-transparent': selected === option.key }"
                                @click="selected = option.key"
                            >
                                <div class="w-10 h-10 rounded-lg bg-gray-100 font-bold text-gray-700 flex items-center justify-center mr-4 flex-shrink-0"
                                     :class="{ 'bg-blue-600 text-white': selected === option.key }">
                                    {{ option.key }}
                                </div>
                                <div class="text-lg font-medium text-gray-800 flex-1">
                                    {{ option.text }}
                                </div>
                                <div class="ml-3">
                                    <input 
                                        type="radio" 
                                        :value="option.key" 
                                        v-model="selected" 
                                        class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </a-tab-pane>

<!-- TAB 2: LIVE RESULTS & ANALYTICS -->
<a-tab-pane key="result" tab="Live Results & Analytics">
    <div class="space-y-6 py-2">
        
        <!-- TOP CONTROL ROW: 3 EQUAL COLUMNS (QR | TIME DISPLAY | TIMER SETTINGS) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- COLUMN 1: QR Code Access -->
            <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm flex items-center space-x-4">
                <div class="p-2 bg-gray-50 rounded-xl border border-gray-100 flex-shrink-0">
                    <a :href="pollingUrl" target="Polling" class="block">
                        <a-qrcode :value="pollingUrl" :size="96" />
                    </a>
                </div>
                <div>
                    <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400 block mb-0.5">Live Access</span>
                    <h3 class="text-sm font-bold text-gray-900">Scan to Vote</h3>
                    <p class="text-xs text-gray-500 mt-1 leading-snug">Point camera or click QR code to open response form.</p>
                </div>
            </div>

            <!-- COLUMN 2: Large Countdown Clock -->
            <div 
                class="bg-white border rounded-2xl p-5 shadow-sm flex flex-col items-center justify-center text-center transition-all duration-300"
                :class="timer.finished ? 'border-red-200 bg-red-50/20' : 'border-gray-200'"
            >
                <div class="flex items-center space-x-2 mb-0.5">
                    <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Time Remaining</span>
                    <span v-if="timer.finished" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-600 animate-pulse">
                        FINISHED
                    </span>
                </div>

                <div 
                    class="font-mono text-5xl font-extrabold tracking-tight my-1 transition-all duration-300"
                    :class="{
                        'text-red-600 scale-105 animate-bounce': timer.finished,
                        'text-emerald-600': timer.started && !timer.finished,
                        'text-gray-800': !timer.started && !timer.finished
                    }"
                >
                    {{ timer.display }}
                </div>

                <div class="flex items-center space-x-1.5 text-xs text-gray-500">
                    <span 
                        class="w-2 h-2 rounded-full" 
                        :class="{
                            'bg-red-500': timer.finished,
                            'bg-emerald-500 animate-ping': timer.started && !timer.finished,
                            'bg-gray-300': !timer.started && !timer.finished
                        }"
                    ></span>
                    <span>{{ timer.finished ? 'Timer Concluded' : (timer.started ? 'Counting Down...' : 'Stopped') }}</span>
                </div>
            </div>

            <!-- COLUMN 3: Timer Controls -->
            <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 mb-1">Minutes</label>
                        <a-input-number v-model:value="timer.minutes" :min="0" :max="59" class="w-full rounded-lg" />
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 mb-1">Seconds</label>
                        <a-input-number v-model:value="timer.secondes" :min="0" :max="59" class="w-full rounded-lg" />
                    </div>
                </div>
                <div class="flex gap-2">
                    <a-button 
                        type="primary" 
                        class="flex-1 rounded-lg h-9 font-medium" 
                        @click="timerStart" 
                        :disabled="timer.started"
                    >
                        Start
                    </a-button>
                    <a-button 
                        danger 
                        class="flex-1 rounded-lg h-9 font-medium" 
                        @click="timerStop" 
                        :disabled="!timer.started"
                    >
                        Stop
                    </a-button>
                    <a-button class="flex-1 rounded-lg h-9 font-medium" @click="timerReset">
                        Reset
                    </a-button>
                </div>
            </div>

        </div>

<!-- MIDDLE SECTION: 3-COLUMN ANALYTICS & AUDIT PANEL -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- COLUMN 1: Visual Distribution Chart -->
    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm flex flex-col items-center justify-between">
        <h3 class="text-base font-bold text-gray-900 mb-2 self-start">Visual Distribution</h3>
        <div class="w-full max-w-[220px] aspect-square flex items-center justify-center my-auto relative">
            <DoughnutChart :chartData="chartData" :options="chartOptions" />
        </div>
        <div class="text-[11px] text-gray-400 mt-2 self-start">Real-time aggregate breakdown</div>
    </div>

    <!-- COLUMN 2: Option Tallies -->
    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-gray-900">Tallies by Option</h3>
            <a-popconfirm
                title="Are you sure you want to clear all responses?"
                ok-text="Yes, Clear"
                cancel-text="Cancel"
                @confirm="clearResponses(poll)"
            >
                <a-button size="small" danger class="rounded-md text-xs">
                    Clear Data
                </a-button>
            </a-popconfirm>
        </div>
        
        <div class="space-y-2.5 flex-1 overflow-y-auto max-h-[320px] pr-1">
            <div 
                v-for="option in availableOptions" 
                :key="option.key"
                class="p-3 bg-gray-50 border border-gray-200/60 rounded-xl flex items-center justify-between hover:border-gray-300 transition-colors"
            >
                <div class="min-w-0 pr-2">
                    <span class="text-[10px] font-semibold uppercase text-gray-400 block">Option {{ option.key }}</span>
                    <span class="text-sm font-medium text-gray-800 truncate block">{{ option.text }}</span>
                </div>
                <span class="text-xl font-bold text-blue-600 ml-2 flex-shrink-0">
                    {{ groupSelected[option.key] || 0 }}
                </span>
            </div>
        </div>
    </div>

    <!-- COLUMN 3: Live Submission Audit Log -->
    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-gray-900">Audit Log</h3>
            <span class="text-xs text-gray-400">Incoming Feed</span>
        </div>

        <div class="flex-1 max-h-[320px] overflow-y-auto border border-gray-100 rounded-xl divide-y divide-gray-100 bg-gray-50/30">
            <div 
                v-for="(response, idx) in poll.responses" 
                :key="idx"
                class="p-2.5 text-sm flex items-center justify-between hover:bg-white transition-colors"
            >
                <a-tooltip placement="top">
                    <template #title>IP: {{ response.ip_address || 'N/A' }}</template>
                    <span class="font-medium text-gray-700 truncate max-w-[130px] text-xs">
                        {{ response.username || 'Anonymous' }}
                    </span>
                </a-tooltip>
                <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200/60 font-bold text-xs">
                    {{ formattedAnswer(response.answer) }}
                </span>
            </div>

            <div v-if="!poll.responses || poll.responses.length === 0" class="p-8 text-center text-xs text-gray-400">
                No responses recorded yet.
            </div>
        </div>
    </div>

</div>

    </div>
</a-tab-pane>


            </a-tabs>
        </div>
    </WidgetLayout>
</template>

<script>
import WidgetLayout from '@/Layouts/WidgetLayout.vue';
import { message } from 'ant-design-vue';
import { DoughnutChart } from 'vue-chart-3';
import { Chart, registerables } from "chart.js";
import axios from 'axios';

Chart.register(...registerables);

export default {
    name: 'PollDesign',
    components: {
        WidgetLayout,
        DoughnutChart
    },
    props: {
        poll: {
            type: Object,
            required: true,
            default: () => ({
                question: '',
                option_a: '',
                option_b: '',
                option_c: '',
                option_d: '',
                option_e: '',
                responses: []
            })
        }
    },
    data() {
        return {
            tabActiveKey: "question",
            selected: null,
            showIndividuals: false,
            updateInterval: null,
            timer: {
                ticker: null,
                count: 0,
                minutes: 1,
                secondes: 0,
                display: '01:00',
                started: false
            },
            groupSelected: {
                A: 0,
                B: 0,
                C: 0,
                D: 0,
                E: 0
            },
            chartOptions: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            color: '#4B5563'
                        }
                    }
                }
            }
        };
    },
    computed: {
        pollingUrl() {
            return route('widget.polling', { uuid: this.poll.uuid });
        },
        availableOptions() {
            const keys = ['A', 'B', 'C', 'D', 'E'];
            return keys
                .map(k => ({ key: k, text: this.poll[`option_${k.toLowerCase()}`] }))
                .filter(opt => Boolean(opt.text && opt.text.trim()));
        },
        chartData() {
            const labels = this.availableOptions.map(opt => `Option ${opt.key}`);
            const data = this.availableOptions.map(opt => this.groupSelected[opt.key] || 0);

            return {
                labels,
                datasets: [
                    {
                        data,
                        backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'],
                        borderWidth: 2,
                        borderColor: '#FFFFFF'
                    }
                ]
            };
        }
    },
    mounted() {
        this.updatePollResult();
        this.timerReset();
    },
    beforeUnmount() {
        this.clearIntervals();
    },
    methods: {
        formattedAnswer(answer) {
            if (Array.isArray(answer)) {
                return answer.join(', ');
            }
            return answer || '-';
        },
        clearResponses(poll) {
            axios.post(route("widget.admin.poll.responseClear", poll))
                .then((resp) => {
                    poll.responses_count = 0;
                    poll.responses = [];
                    this.resetGroupSelected();
                    message.success(resp.data.message || 'Responses cleared successfully');
                })
                .catch(() => {
                    message.error('Failed to clear responses');
                });
        },
        resetGroupSelected() {
            this.groupSelected = { A: 0, B: 0, C: 0, D: 0, E: 0 };
        },
        updatePollResult() {
            axios.post(route("widget.admin.poll.responseAll", this.poll))
                .then((resp) => {
                    this.poll.responses = resp.data || [];
                    this.resetGroupSelected();

                    this.poll.responses.forEach(item => {
                        if (Array.isArray(item.answer)) {
                            item.answer.forEach(ansKey => {
                                if (this.groupSelected[ansKey] !== undefined) {
                                    this.groupSelected[ansKey]++;
                                }
                            });
                        } else if (item.answer && this.groupSelected[item.answer] !== undefined) {
                            this.groupSelected[item.answer]++;
                        }
                    });
                });
        },
        timerStart() {
            if (this.timer.started) return;
            if (this.timer.count <= 0) {
                this.timerReset();
            }
            this.timer.started = true;
            this.timer.ticker = setInterval(() => {
                if (this.timer.count > 0) {
                    this.timer.count--;
                    this.updateTimerDisplay();
                } else {
                    this.timerStop();
                    message.info('Timer finished!');
                }
            }, 1000);
        },
        timerStop() {
            this.timer.started = false;
            if (this.timer.ticker) {
                clearInterval(this.timer.ticker);
                this.timer.ticker = null;
            }
        },
        timerReset() {
            this.timerStop();
            const mins = parseInt(this.timer.minutes) || 0;
            const secs = parseInt(this.timer.secondes) || 0;
            this.timer.count = (mins * 60) + secs;
            this.updateTimerDisplay();
        },
        updateTimerDisplay() {
            const m = Math.floor(this.timer.count / 60);
            const s = this.timer.count % 60;
            this.timer.display = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        },
        clearIntervals() {
            if (this.updateInterval) {
                clearInterval(this.updateInterval);
                this.updateInterval = null;
            }
            if (this.timer.ticker) {
                clearInterval(this.timer.ticker);
                this.timer.ticker = null;
            }
        }
    },
    watch: {
        tabActiveKey(newTab) {
            if (newTab === 'result') {
                this.updatePollResult();
                if (!this.updateInterval) {
                    this.updateInterval = setInterval(this.updatePollResult, 5000);
                }
            } else {
                if (this.updateInterval) {
                    clearInterval(this.updateInterval);
                    this.updateInterval = null;
                }
            }
        }
    }
}
</script>