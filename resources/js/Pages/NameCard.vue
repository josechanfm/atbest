<template>
  <MemberLayout title="會員卡">
    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- LEFT: card + actions -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Card exists -->
          <template v-if="hasNameCard">
            <div class="flex justify-center">
              <MemberCard :data="cardData" />
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap justify-center gap-3">
              <button
                type="button"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors"
                @click="printCard"
              >
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />
                </svg>
                列印會員卡
              </button>

              <button
                type="button"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-white border border-gray-300 hover:bg-gray-50 text-gray-800 font-medium transition-colors"
                @click="downloadQR"
              >
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 3v12m0 0l-4-4m4 4l4-4M4 21h16" />
                </svg>
                下載 QR Code
              </button>

              <Link
                v-if="nameCard?.card_link"
                :href="nameCard.card_link"
                target="_blank"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-white border border-gray-300 hover:bg-gray-50 text-gray-800 font-medium transition-colors"
              >
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                開啟公開連結
              </Link>
            </div>

            <!-- Usage tips -->
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
              <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                  <h4 class="font-medium text-gray-900 mb-1">使用說明</h4>
                  <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
                    <li>點擊卡片可翻轉，顯示 QR Code 及詳細會員資訊</li>
                    <li>QR Code 可用於活動報到、會員驗證及查閱會員檔案</li>
                    <li>如個人資料或照片有變動，請聯絡秘書處更新會員卡</li>
                  </ul>
                </div>
              </div>
            </div>
          </template>

          <!-- No card yet -->
          <template v-else>

            <!-- Member profile summary card (no name card) -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
              <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                會員基本資料
              </h3>

              <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                  <dt class="text-xs tracking-[0.18em] uppercase text-gray-400 mb-1">中文姓名</dt>
                  <dd class="text-gray-900 font-medium">{{ member.name_zh || '—' }}</dd>
                </div>
                <div>
                  <dt class="text-xs tracking-[0.18em] uppercase text-gray-400 mb-1">英文姓名</dt>
                  <dd class="text-gray-900 font-medium">{{ member.name_en || '—' }}</dd>
                </div>
                <div v-if="member.email">
                  <dt class="text-xs tracking-[0.18em] uppercase text-gray-400 mb-1">電郵</dt>
                  <dd class="text-gray-900 break-all">{{ member.email }}</dd>
                </div>
                <div v-if="member.organization">
                  <dt class="text-xs tracking-[0.18em] uppercase text-gray-400 mb-1">所屬組織</dt>
                  <dd class="text-gray-900">
                    {{ member.organization.name_zh }}
                    <span v-if="member.organization.name_pt" class="block text-xs text-gray-500 italic mt-0.5">
                      {{ member.organization.name_pt }}
                    </span>
                  </dd>
                </div>
              </dl>
            </div>
          </template>
        </div>

        <!-- RIGHT: member info panel (always shown) -->
        <aside class="lg:col-span-1">
          <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-6">
            <!-- Avatar + name -->
            <div class="flex items-center gap-4 mb-6">
              <div class="w-16 h-16 rounded-full overflow-hidden bg-gradient-to-br from-blue-100 to-cyan-100 flex-shrink-0">
                <img
                  v-if="member.avatar"
                  :src="member.avatar"
                  :alt="member.name_zh"
                  class="w-full h-full object-cover"
                />
                <svg v-else viewBox="0 0 24 24" class="w-full h-full text-blue-400 p-2" fill="currentColor">
                  <circle cx="12" cy="8" r="4" />
                  <path d="M12 14c-3.6 0-7 1.9-7 4.4V21h14v-2.6c0-2.5-3.4-4.4-7-4.4z" />
                </svg>
              </div>
              <div class="min-w-0">
                <h1 class="text-lg font-bold text-gray-900 truncate">{{ member.name_zh }}</h1>
                <p class="text-sm text-gray-500 truncate">{{ member.name_en }}</p>
              </div>
            </div>

            <hr class="border-gray-100 mb-6" />

            <!-- Info rows -->
            <dl class="space-y-4">
              <!-- Card number (only if card exists) -->
              <div v-if="hasNameCard">
                <dt class="text-xs tracking-[0.18em] uppercase text-gray-400 mb-1">會員編號</dt>
                <dd class="font-mono text-gray-900 tracking-wider">
                  {{ splitNumber(nameCard.number).part1 }}·{{ splitNumber(nameCard.number).part2 }}
                </dd>
              </div>

              <!-- Member type (card value if exists, fallback to member) -->
              <div>
                <dt class="text-xs tracking-[0.18em] uppercase text-gray-400 mb-1">會員類型</dt>
                <dd class="text-gray-900">
                  {{ hasNameCard ? nameCard.member_type : (member.member_type || '—') }}
                </dd>
              </div>

              <!-- Card status -->
              <div>
                <dt class="text-xs tracking-[0.18em] uppercase text-gray-400 mb-1">會員卡狀態</dt>
                <dd>
                  <span
                    v-if="hasNameCard"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-emerald-50 text-emerald-700"
                  >
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    已開通
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-amber-50 text-amber-700"
                  >
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    尚未開通
                  </span>
                </dd>
              </div>

              <div v-if="member.organization">
                <dt class="text-xs tracking-[0.18em] uppercase text-gray-400 mb-1">所屬組織</dt>
                <dd class="text-gray-900">
                  {{ member.organization.name_zh }}
                  <span v-if="member.organization.name_pt" class="block text-xs text-gray-500 italic mt-0.5">
                    {{ member.organization.name_pt }}
                  </span>
                </dd>
              </div>

              <div v-if="member.email">
                <dt class="text-xs tracking-[0.18em] uppercase text-gray-400 mb-1">電郵</dt>
                <dd class="text-gray-900 break-all">{{ member.email }}</dd>
              </div>

              <!-- Validity (only if card exists) -->
              <div v-if="hasNameCard" class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                <div>
                  <dt class="text-xs tracking-[0.18em] uppercase text-gray-400 mb-1">簽發日期</dt>
                  <dd class="font-mono text-sm text-gray-900">{{ formatDate(nameCard.issue_at) }}</dd>
                </div>
                <div class="text-right">
                  <dt class="text-xs tracking-[0.18em] uppercase text-gray-400 mb-1">有效至</dt>
                  <dd class="font-mono text-sm text-amber-600 font-semibold">{{ formatDate(nameCard.valid_to) }}</dd>
                </div>
              </div>
            </dl>
          </div>
        </aside>
      </div>
    </div>
  </MemberLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import MemberLayout from '@/Layouts/MemberLayout.vue'
import MemberCard from '@/Components/Member/MemberCard.vue'

const props = defineProps({
  member: { type: Object, required: true },
  nameCard: { type: Object, default: null },
})

/* ------------------------------------------------------------------
 * Whether a name card exists
 * ------------------------------------------------------------------ */
const hasNameCard = computed(() => {
  return props.nameCard !== null && props.nameCard !== undefined
})

/* ------------------------------------------------------------------
 * Shape the backend payloads into the MemberCard's expected `data`.
 * Only used when hasNameCard is true.
 * ------------------------------------------------------------------ */
const cardData = computed(() => ({
  org: {
    logo: props.member.organization?.logo ?? '/storage/images/site_logo.png',
    name_zh: props.member.organization?.name_zh ?? '',
    name_pt: props.member.organization?.name_pt ?? '',
  },
  member: {
    number: props.nameCard?.number ?? '00000000',
    name_zh: props.member.name_zh ?? '',
    name_en: props.member.name_en ?? '',
    issueAt: formatDate(props.nameCard?.issue_at),
    validTo: formatDate(props.nameCard?.valid_to),
    memberType: props.nameCard?.member_type ?? '會員',
    avatarSrc: props.nameCard?.avatar ?? props.member.avatar ?? '',
    nameCardLink: props.nameCard?.qr_url ?? props.nameCard?.card_link ?? '',
  },
}))

/* ------------------------------------------------------------------
 * Helpers
 * ------------------------------------------------------------------ */
function formatDate(value) {
  if (!value) return '—'
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return value
  const yyyy = d.getFullYear()
  const mm = String(d.getMonth() + 1).padStart(2, '0')
  const dd = String(d.getDate()).padStart(2, '0')
  return `${yyyy} · ${mm} · ${dd}`
}

function splitNumber(num) {
  const raw = String(num ?? '').replace(/\D/g, '').padStart(8, '0').slice(0, 8)
  return { part1: raw.slice(0, 4), part2: raw.slice(4, 8) }
}

function printCard() {
  window.print()
}

function downloadQR() {
  const canvas = document.querySelector('canvas')
  if (!canvas) return
  const link = document.createElement('a')
  link.download = `atec-member-${props.nameCard?.number ?? 'card'}.png`
  link.href = canvas.toDataURL('image/png')
  link.click()
}
</script>