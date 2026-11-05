<template>
    <MemberLayout title="Invitations">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                邀請註冊 (Invitations)
            </h2>
        </template>

        <div class="py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Create Invitation -->
                <div class="bg-white rounded-3xl shadow-xl p-6 sm:p-8">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">
                        建立新邀請 (Create Invitation)
                    </h3>

                    <form @submit.prevent="createInvitation" class="flex flex-col sm:flex-row gap-3">
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="(選填) 指定電郵 — 留空則任何人可用"
                            class="flex-1 rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                        />
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md disabled:opacity-50 transition-all"
                        >
                            <span v-if="form.processing">建立中...</span>
                            <span v-else>+ 建立邀請</span>
                        </button>
                    </form>

                    <p class="text-xs text-gray-400 mt-2">
                        每個邀請有效期 3 天，且僅能使用一次。
                    </p>
                </div>

                <!-- Invitation List -->
                <div class="bg-white rounded-3xl shadow-xl p-6 sm:p-8">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">
                        我的邀請 (My Invitations)
                    </h3>

                    <div v-if="invitations.length === 0" class="text-sm text-gray-400 text-center py-8 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                        尚未建立任何邀請。
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="inv in invitations"
                            :key="inv.id"
                            class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 rounded-2xl border bg-gray-50"
                            :class="statusBorder(inv)"
                        >
                            <!-- Status + URL -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span
                                        class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full"
                                        :class="statusBadge(inv)"
                                    >
                                        {{ statusLabel(inv) }}
                                    </span>
                                    <span class="text-[10px] text-gray-400">
                                        到期：{{ formatDate(inv.expires_at) }}
                                    </span>
                                </div>
                                <p class="text-xs font-mono text-gray-700 truncate">
                                    {{ inv.share_url }}
                                </p>
                                <p v-if="inv.email" class="text-[10px] text-gray-400 mt-0.5">
                                    限定電郵：{{ inv.email }}
                                </p>
                                <p v-if="inv.used_by" class="text-[10px] text-gray-400 mt-0.5">
                                    使用者：{{ inv.used_by.name_zh || inv.used_by.name_en }}
                                </p>
                            </div>

                            <!-- Actions -->
                            <div v-if="inv.is_valid" class="flex items-center gap-2 shrink-0">
                                <button
                                    type="button"
                                    @click="copy(inv.share_url)"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white text-[11px] font-bold rounded-lg transition-all"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    複製
                                </button>

                                <button
                                    type="button"
                                    @click="share(inv.share_url)"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold rounded-lg transition-all"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                    </svg>
                                    分享
                                </button>

                                <button
                                    type="button"
                                    @click="revoke(inv)"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-red-50 text-red-600 text-[11px] font-bold rounded-lg border border-red-200 transition-all"
                                >
                                    撤銷
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </MemberLayout>
</template>

<script>
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { useForm, router } from '@inertiajs/vue3';

export default {
    components: { MemberLayout },
    props: {
        invitations: { type: Array, default: () => [] },
    },
    data() {
        return {
            form: useForm({ email: '' }),
        };
    },
    methods: {
        createInvitation() {
            this.form.post(route('member.invitations.store'), {
                preserveScroll: true,
                onSuccess: () => this.form.reset('email'),
            });
        },
        revoke(inv) {
            if (!confirm('確定撤銷此邀請？')) return;
            router.delete(route('member.invitations.destroy', inv.id), {
                preserveScroll: true,
            });
        },
        async copy(url) {
            await navigator.clipboard.writeText(url);
        },
        async share(url) {
            const text = `邀請您加入：\n${url}`;
            if (navigator.share) {
                try {
                    await navigator.share({ title: '邀請註冊', text });
                    return;
                } catch (e) {
                    if (e.name === 'AbortError') return;
                }
            }
            window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
        },
        statusLabel(inv) {
            if (inv.is_used) return '已使用';
            if (inv.is_expired) return '已過期';
            return '有效';
        },
        statusBadge(inv) {
            if (inv.is_used) return 'bg-gray-200 text-gray-600';
            if (inv.is_expired) return 'bg-red-100 text-red-600';
            return 'bg-emerald-100 text-emerald-700';
        },
        statusBorder(inv) {
            if (inv.is_used) return 'border-gray-200';
            if (inv.is_expired) return 'border-red-200';
            return 'border-emerald-200';
        },
        formatDate(iso) {
            return new Date(iso).toLocaleString('zh-HK', {
                year: 'numeric', month: '2-digit', day: '2-digit',
                hour: '2-digit', minute: '2-digit',
            });
        },
    },
};
</script>