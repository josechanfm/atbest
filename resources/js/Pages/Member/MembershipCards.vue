<template>
    <MemberLayout title="My Cards">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                我的會員卡卡包 (Wallet Pass)
            </h2>
        </template>

        <div class="py-2">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Empty State -->
                <div v-if="!localMembers || localMembers.length === 0" class="bg-white rounded-3xl shadow-xl p-12 text-center text-gray-400">
                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    暫無會員卡記錄
                </div>

                <template v-else>
                    <!-- Stack & Swipe Container -->
                    <div
                        class="relative w-full flex flex-col items-center py-4 select-none"
                        @touchstart="onTouchStart"
                        @touchend="onTouchEnd"
                    >

                        <!-- Card Stack Viewport -->
                        <div class="relative w-full max-w-md h-[500px] sm:h-[540px]">
                            <div
                                v-for="(member, index) in localMembers"
                                :key="member.id"
                                @click="bringToLast(index)"
                                class="absolute inset-x-0 top-0 transition-all duration-500 ease-out cursor-pointer origin-top"
                                :style="getStackStyle(index)"
                            >
                                <!-- Card Container -->
                                <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden p-2 flex flex-col justify-between hover:border-indigo-200">

                                    <!-- Organization Header Tab (Always visible on stacked cards) -->
                                    <div class="flex items-center justify-between mb-2 border-b border-gray-100 bg-white/90 backdrop-blur-sm">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-9 h-9 rounded-xl bg-gray-50 flex items-center justify-center p-1 border border-gray-100 shadow-sm">
                                                <img :src="member.organization?.logo ?? '/storage/images/site_logo.png'" class="w-full h-full object-contain" alt="Logo" />
                                            </div>
                                            <div>
                                                <div class="font-bold text-sm text-gray-900 line-clamp-1">
                                                    {{ member.organization?.name_zh || member.organization?.name || '會員組織' }}
                                                </div>
                                                <div class="text-[10px] text-gray-400 font-mono">
                                                    ID: {{ member.id }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Stack Sequence Indicator -->
                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full" :class="isFrontCard(index) ? 'bg-indigo-50 text-indigo-600' : 'bg-gray-100 text-gray-500'">
                                            {{ isFrontCard(index) ? '當前顯示' : `#${index + 1}` }}
                                        </span>
                                    </div>

                                    <!-- Member Card Body Content -->
                                    <div :class="{ 'opacity-40 pointer-events-none': !isFrontCard(index) }" class="transition-opacity duration-300">
                                        <MemberCard :data="getMemberCardData(member)" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Active Front Card Action Link -->
                        <div v-if="activeFrontMember" class="mt-8 flex items-center justify-center">
                            <a
                                v-if="activeFrontMember.name_card_url"
                                :href="activeFrontMember.name_card_url"
                                target="_blank"
                                class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all shadow-lg shadow-indigo-200 hover:shadow-indigo-300"
                            >
                                <span>開啟數碼名片網址</span>
                                <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>

                        <!-- Manage Business Card Link (Bottom of Wallet) -->
                        <div class="mt-10 pt-6 w-full max-w-md border-t border-gray-200 flex justify-center">
                            <Link
                                :href="route('member.businessCard.edit')"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-white hover:bg-indigo-50 text-indigo-600 text-xs font-bold rounded-xl border border-indigo-200 hover:border-indigo-300 shadow-sm transition-all"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span>管理數碼名片</span>
                            </Link>
                        </div>

                    </div>

                </template>

            </div>
        </div>
    </MemberLayout>
</template>

<script>
import MemberLayout from '@/Layouts/MemberLayout.vue';
import MemberCard from "@/Components/Member/MemberCard.vue";
import { Link } from '@inertiajs/vue3';

export default {
    components: {
        MemberLayout,
        MemberCard,
        Link,
    },
    props: {
        members: {
            type: Array,
            default: () => [],
        }
    },
    data() {
        return {
            // Local reactive array so we can swap / reorder card sequence
            localMembers: [],
            touchStartX: 0,
            touchEndX: 0,
        }
    },
    computed: {
        // The last item in localMembers is physically rendered at the top/front of the stack
        activeFrontMember() {
            if (this.localMembers.length === 0) return null;
            return this.localMembers[this.localMembers.length - 1];
        }
    },
    watch: {
        members: {
            immediate: true,
            handler(newVal) {
                this.localMembers = [...newVal];
            }
        }
    },
    methods: {
        // Is this card currently at the end of the array (front of stack)?
        isFrontCard(index) {
            return index === this.localMembers.length - 1;
        },

        // Move the selected card to the LAST position in localMembers
        bringToLast(index) {
            if (this.isFrontCard(index)) return; // Already front card

            // Remove selected member and append to the end of array
            const selected = this.localMembers.splice(index, 1)[0];
            this.localMembers.push(selected);
        },

        // Calculate card stack visual positions strictly based on array sequence index
        getStackStyle(index) {
            const total = this.localMembers.length;
            const distanceToLast = total - 1 - index;

            if (distanceToLast === 0) {
                // Front active card (Last item in localMembers)
                return {
                    zIndex: 30,
                    transform: `translateY(${(total - 1) * 30}px) scale(1)`,
                    opacity: 1,
                    pointerEvents: 'auto',
                };
            } else {
                // Background stacked cards (offset vertically by sequence index so top header is visible)
                return {
                    zIndex: 10 + index,
                    transform: `translateY(${index * 30}px) scale(${1 - distanceToLast * 0.02})`,
                    opacity: 0.95,
                    pointerEvents: 'auto',
                };
            }
        },

        // Touch Swipe Gesture (Swipe left/right on front card to push top card to the bottom/start)
        onTouchStart(e) {
            this.touchStartX = e.changedTouches[0].screenX;
        },
        onTouchEnd(e) {
            this.touchEndX = e.changedTouches[0].screenX;
            const threshold = 50;

            if (Math.abs(this.touchEndX - this.touchStartX) > threshold) {
                // Swipe detected: Move current top card to the beginning of the stack
                if (this.localMembers.length > 1) {
                    const topMember = this.localMembers.pop();
                    this.localMembers.unshift(topMember);
                }
            }
        },

        getMemberCardData(member) {
            if (!member) return {};
            return {
                org: {
                    logo: member.organization?.logo ?? '/storage/images/site_logo.png',
                    name_zh: member.organization?.name_zh ?? '',
                    name_pt: member.organization?.name_pt ?? '',
                },
                member: {
                    number: member.id ?? '0000000',
                    name_zh: member.name_zh ?? '',
                    name_en: member.name_en ?? '',
                    issueAt: this.formatCardDate(member.issue_at ?? '---'),
                    validTo: this.formatCardDate(member.valid_to ?? '---'),
                    memberType: member.member_type ?? '會員',
                    avatarSrc: member.avatar ?? '',
                    nameCardLink: member.name_card_url ?? '',
                },
            };
        },
        formatCardDate(dateString) {
            if (!dateString || dateString === '---') return '---';
            return dateString.substring(0, 10);
        }
    },
}
</script>