<template>
  <div class="flex items-center justify-center">
    <div class="w-full [container-type:inline-size]" :style="{ maxWidth: card.maxWidth }">
      <!-- Flip wrapper -->
      <div
        class="relative w-full aspect-[1.586/1] cursor-pointer select-none [perspective:1400px]"
        style="container-type: inline-size;"
        role="button"
        tabindex="0"
        :aria-label="isFlipped ? '顯示會員卡正面' : '顯示會員卡背面'"
        :aria-pressed="isFlipped"
        @click="toggleFlip"
        @keydown.enter.prevent="toggleFlip"
        @keydown.space.prevent="toggleFlip"
      >
        <!-- Inner (rotates) -->
        <div
          class="relative w-full h-full transition-transform duration-700 ease-[cubic-bezier(.22,.9,.3,1)]"
          style="transform-style: preserve-3d;"
          :style="{ transform: isFlipped ? 'rotateY(180deg)' : 'rotateY(0deg)' }"
        >
          <!-- ==================== FRONT ==================== -->
          <div
            class="absolute inset-0 rounded-[3.2cqw] overflow-hidden shadow-[0_6cqw_12cqw_-4cqw_rgba(20,40,90,0.55)]"
            style="backface-visibility: hidden; -webkit-backface-visibility: hidden;"
          >
            <!-- Base gradient -->
            <div class="absolute inset-0 bg-gradient-to-br from-[#0e2a63] via-[#153a8a] to-[#0a1f4d]"></div>

            <!-- Radial gold glow top-right -->
            <div
              class="absolute -top-[9.6cqw] -right-[9.6cqw] w-[56cqw] h-[56cqw] rounded-full opacity-40 blur-[12.8cqw]"
              style="background: radial-gradient(circle, #e0c98a, transparent 65%)"
            ></div>

            <!-- Radial sky glow bottom-left -->
            <div
              class="absolute -bottom-[9.6cqw] -left-[8cqw] w-[52cqw] h-[52cqw] rounded-full opacity-30 blur-[12.8cqw]"
              style="background: radial-gradient(circle, #7dd3fc, transparent 65%)"
            ></div>

            <!-- Pinstripe -->
            <svg class="absolute inset-0 w-full h-full opacity-[0.08]" xmlns="http://www.w3.org/2000/svg">
              <defs>
                <pattern
                  id="atecCardLinesFront"
                  width="2.8cqw"
                  height="2.8cqw"
                  patternUnits="userSpaceOnUse"
                  patternTransform="rotate(45)"
                >
                  <line x1="0" y1="0" x2="0" y2="2.8cqw" stroke="#e0c98a" stroke-width="0.2cqw" />
                </pattern>
              </defs>
              <rect width="100%" height="100%" fill="url(#atecCardLinesFront)" />
            </svg>

            <!-- Holographic shine -->
            <div
              class="pointer-events-none absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-700"
              style="
                background: linear-gradient(
                  115deg,
                  transparent 30%,
                  rgba(255, 255, 255, 0.18) 45%,
                  rgba(224, 201, 138, 0.32) 50%,
                  rgba(255, 255, 255, 0.18) 55%,
                  transparent 70%
                );
                background-size: 300% 300%;
                animation: cardShine 3.5s ease-in-out infinite;
              "
            ></div>

            <!-- Gold border -->
            <div class="absolute inset-0 rounded-[3.2cqw] ring-[0.2cqw] ring-inset ring-[#c9a961]/45"></div>

            <!-- Content -->
            <div class="relative z-10 h-full w-full p-[4cqw] flex flex-col justify-between text-[#f5f1e8]">
              <!-- TOP: logo + bilingual name -->
              <div class="flex items-center gap-[2.4cqw] min-w-0">
                <img
                  :src="card.logoSrc"
                  :alt="card.organization.nameChinese"
                  class="shrink-0 h-[9.5cqw] w-auto max-w-[14cqw] object-contain drop-shadow-[0_0.4cqw_1cqw_rgba(0,0,0,0.35)]"
                />
                <div class="min-w-0 flex-1">
                  <div class="text-[3.8cqw] font-bold tracking-[0.02em] text-[#f5f1e8] leading-tight truncate">
                    {{ card.organization.nameChinese }}
                  </div>
                  <div class="text-[2.4cqw] italic tracking-[0.01em] text-[#e0c98a]/95 leading-snug mt-[0.4cqw] truncate">
                    {{ card.organization.namePortuguese }}
                  </div>
                </div>
              </div>

              <!-- MIDDLE: member type + number · avatar -->
              <div class="flex items-center justify-between gap-[3.2cqw]">
                <div class="min-w-0">
                  <div class="text-[1.6cqw] tracking-[0.28em] uppercase text-[#f5f1e8]/50 mb-[0.2cqw]">
                    {{ card.labels.memberType }}
                  </div>
                  <div class="font-serif italic text-[3.8cqw] text-[#e0c98a] leading-none mb-[2.4cqw]">
                    {{ card.memberType.value }}
                  </div>

                  <div class="font-mono text-[3.8cqw] tracking-[0.34em] text-[#f5f1e8]/95">
                    <span>{{ card.memberNumber.part1 }}</span>
                    <span class="text-[#c9a961] mx-[0.4cqw]">·</span>
                    <span>{{ card.memberNumber.part2 }}</span>
                  </div>
                </div>

                <!-- Avatar -->
                <div class="relative shrink-0">
                  <div
                    class="w-[22.5cqw] h-[25cqw] rounded-[2.4cqw] overflow-hidden ring-[0.4cqw] ring-[#c9a961]/80 shadow-[0_2cqw_4.8cqw_-1.6cqw_rgba(0,0,0,0.7)]"
                    :class="card.avatarSrc
                      ? 'bg-transparent'
                      : 'bg-gradient-to-br from-[#e8eefb] to-[#c9d6ee]'"
                  >
                    <img
                      v-if="card.avatarSrc"
                      :src="card.avatarSrc"
                      :alt="card.member.nameChinese"
                      class="w-full h-full object-cover object-top"
                    />
                    <svg
                      v-else
                      viewBox="0 0 24 24"
                      preserveAspectRatio="xMidYMid meet"
                      class="w-full h-full text-[#7a90b8]"
                      fill="currentColor"
                      aria-hidden="true"
                    >
                      <circle cx="12" cy="8" r="4" />
                      <path d="M12 14c-3.6 0-7 1.9-7 4.4V21h14v-2.6c0-2.5-3.4-4.4-7-4.4z" />
                    </svg>
                  </div>

                  <span
                    v-if="card.showVerifiedBadge"
                    class="absolute -bottom-[0.2cqw] -left-[0.2cqw] w-[4cqw] h-[4cqw] rounded-full bg-[#c9a961] ring-[0.4cqw] ring-[#0e2a63] flex items-center justify-center"
                  >
                    <svg
                      class="w-[2.4cqw] h-[2.4cqw] text-[#0e2a63]"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="3"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    >
                      <path d="M5 13l4 4L19 7" />
                    </svg>
                  </span>
                </div>
              </div>

              <!-- BOTTOM: name + issue/valid -->
              <div>
                <div class="mb-[2.4cqw]">
                  <div class="text-[1.6cqw] tracking-[0.28em] uppercase text-[#f5f1e8]/50 mb-[0.2cqw]">
                    {{ card.labels.member }}
                  </div>
                  <div class="text-[3cqw] tracking-[0.16em] uppercase text-[#f5f1e8] w-full">
                    {{ card.member.nameChinese }}
                    <span class="text-[#f5f1e8]/55 font-light">
                      · {{ card.member.nameEnglish }}
                    </span>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-[3.2cqw] pt-[2.4cqw] border-t border-[#f5f1e8]/15">
                  <div>
                    <div class="text-[1.6cqw] tracking-[0.28em] uppercase text-[#f5f1e8]/50 mb-[0.2cqw]">
                      {{ card.labels.issueAt }}
                    </div>
                    <div class="font-mono text-[2.4cqw] tracking-[0.18em] text-[#f5f1e8]/85">
                      {{ card.validity.issueAt }}
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="text-[1.6cqw] tracking-[0.28em] uppercase text-[#f5f1e8]/50 mb-[0.2cqw]">
                      {{ card.labels.validTo }}
                    </div>
                    <div class="font-mono text-[2.4cqw] tracking-[0.18em] text-[#e0c98a] font-semibold">
                      {{ card.validity.validTo }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Bottom gold hairline -->
            <div class="absolute bottom-0 left-0 right-0 h-[0.4cqw] bg-gradient-to-r from-transparent via-[#c9a961]/70 to-transparent"></div>

            <!-- Flip hint (front) -->
            <div class="absolute top-[3cqw] right-[3cqw] z-20 text-[#f5f1e8]/45 text-[1.6cqw] tracking-[0.24em] uppercase flex items-center gap-[0.8cqw]">
              <svg
                class="w-[2.4cqw] h-[2.4cqw]"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M21 12a9 9 0 11-3-6.7L21 8" />
                <path d="M21 3v5h-5" />
              </svg>
              <span>Flip</span>
            </div>
          </div>

          <!-- ==================== BACK ==================== -->
          <div
            class="absolute inset-0 rounded-[3.2cqw] overflow-hidden shadow-[0_6cqw_12cqw_-4cqw_rgba(20,40,90,0.55)]"
            style="
              backface-visibility: hidden;
              -webkit-backface-visibility: hidden;
              transform: rotateY(180deg);
            "
          >
            <!-- Base gradient -->
            <div class="absolute inset-0 bg-gradient-to-br from-[#0a1f4d] via-[#0e2a63] to-[#081736]"></div>

            <!-- Radial gold glow top-left -->
            <div
              class="absolute -top-[9.6cqw] -left-[9.6cqw] w-[56cqw] h-[56cqw] rounded-full opacity-35 blur-[12.8cqw]"
              style="background: radial-gradient(circle, #e0c98a, transparent 65%)"
            ></div>

            <!-- Radial sky glow bottom-right -->
            <div
              class="absolute -bottom-[9.6cqw] -right-[8cqw] w-[52cqw] h-[52cqw] rounded-full opacity-30 blur-[12.8cqw]"
              style="background: radial-gradient(circle, #7dd3fc, transparent 65%)"
            ></div>

            <!-- Pinstripe -->
            <svg class="absolute inset-0 w-full h-full opacity-[0.08]" xmlns="http://www.w3.org/2000/svg">
              <defs>
                <pattern
                  id="atecCardLinesBack"
                  width="2.8cqw"
                  height="2.8cqw"
                  patternUnits="userSpaceOnUse"
                  patternTransform="rotate(-45)"
                >
                  <line x1="0" y1="0" x2="0" y2="2.8cqw" stroke="#e0c98a" stroke-width="0.2cqw" />
                </pattern>
              </defs>
              <rect width="100%" height="100%" fill="url(#atecCardLinesBack)" />
            </svg>

            <!-- Gold border -->
            <div class="absolute inset-0 rounded-[3.2cqw] ring-[0.2cqw] ring-inset ring-[#c9a961]/45"></div>

            <!-- Content -->
            <div class="relative z-10 h-full w-full p-[4cqw] flex flex-col text-[#f5f1e8]">
              <!-- FULL-WIDTH HEADER: logo + bilingual organization name (identical to front) -->
              <div class="flex items-center gap-[2.4cqw] min-w-0">
                <img
                  :src="card.logoSrc"
                  :alt="card.organization.nameChinese"
                  class="shrink-0 h-[9.5cqw] w-auto max-w-[14cqw] object-contain drop-shadow-[0_0.4cqw_1cqw_rgba(0,0,0,0.35)]"
                />
                <div class="min-w-0 flex-1">
                  <div class="text-[3.8cqw] font-bold tracking-[0.02em] text-[#f5f1e8] leading-tight truncate">
                    {{ card.organization.nameChinese }}
                  </div>
                  <div class="text-[2.4cqw] italic tracking-[0.01em] text-[#e0c98a]/95 leading-snug mt-[0.4cqw] truncate">
                    {{ card.organization.namePortuguese }}
                  </div>
                </div>
              </div>

              <!-- MAIN: QR (left) + holder info (right) -->
              <div class="flex-1 flex items-center gap-[4cqw] pt-[3cqw]">
                <!-- LEFT: QR code -->
                <div class="shrink-0 flex flex-col items-center gap-[1.4cqw]">
                  <div
                    class="bg-white p-[1.4cqw] rounded-[1.8cqw] ring-[0.2cqw] ring-[#c9a961]/60 shadow-[0_2cqw_5cqw_-2cqw_rgba(0,0,0,0.6)]"
                  >
                    <QRCodeVue3
                      :value="qrValue"
                      :width="100"
                      :height="100"
                    />
                  </div>
                  <div class="text-[1.3cqw] tracking-[0.26em] uppercase text-[#e0c98a]/85 text-center">
                    {{ card.labels.scanHint }}
                  </div>
                </div>

                <!-- RIGHT: holder info -->
                <div class="flex-1 min-w-0 flex flex-col justify-between h-full py-[0.6cqw]">
                  <div>
                    <div class="text-[1.4cqw] tracking-[0.28em] uppercase text-[#f5f1e8]/45 mb-[0.3cqw]">
                      {{ card.labels.member }}
                    </div>
                    <div class="text-[2.8cqw] font-semibold tracking-[0.08em] text-[#f5f1e8]">
                      {{ card.member.nameChinese }}
                    </div>
                    <div class="text-[1.8cqw] tracking-[0.14em] uppercase text-[#f5f1e8]/60 mt-[0.2cqw]">
                      {{ card.member.nameEnglish }}
                    </div>

                    <div class="grid grid-cols-2 gap-[2.4cqw] mt-[2.2cqw]">
                      <div>
                        <div class="text-[1.4cqw] tracking-[0.28em] uppercase text-[#f5f1e8]/45 mb-[0.3cqw]">
                          Member No.
                        </div>
                        <div class="font-mono text-[2.4cqw] tracking-[0.22em] text-[#e0c98a]">
                          {{ card.memberNumber.part1 }}·{{ card.memberNumber.part2 }}
                        </div>
                      </div>
                      <div>
                        <div class="text-[1.4cqw] tracking-[0.28em] uppercase text-[#f5f1e8]/45 mb-[0.3cqw]">
                          {{ card.labels.memberType }}
                        </div>
                        <div class="font-serif italic text-[2.4cqw] text-[#e0c98a]">
                          {{ card.memberType.value }}
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Validity row -->
                  <div class="grid grid-cols-2 gap-[2.4cqw] pt-[2cqw] border-t border-[#f5f1e8]/15">
                    <div>
                      <div class="text-[1.4cqw] tracking-[0.28em] uppercase text-[#f5f1e8]/45 mb-[0.3cqw]">
                        {{ card.labels.issueAt }}
                      </div>
                      <div class="font-mono text-[2cqw] tracking-[0.18em] text-[#f5f1e8]/85">
                        {{ card.validity.issueAt }}
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="text-[1.4cqw] tracking-[0.28em] uppercase text-[#f5f1e8]/45 mb-[0.3cqw]">
                        {{ card.labels.validTo }}
                      </div>
                      <div class="font-mono text-[2cqw] tracking-[0.18em] text-[#e0c98a] font-semibold">
                        {{ card.validity.validTo }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Bottom gold hairline -->
            <div class="absolute bottom-0 left-0 right-0 h-[0.4cqw] bg-gradient-to-r from-transparent via-[#c9a961]/70 to-transparent"></div>

            <!-- Flip hint (back) -->
            <div class="absolute top-[3cqw] right-[3cqw] z-20 text-[#f5f1e8]/45 text-[1.6cqw] tracking-[0.24em] uppercase flex items-center gap-[0.8cqw]">
              <svg
                class="w-[2.4cqw] h-[2.4cqw]"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M21 12a9 9 0 11-3-6.7L21 8" />
                <path d="M21 3v5h-5" />
              </svg>
              <span>Back</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Floor reflection -->
      <div
        class="absolute left-[4.8cqw] right-[4.8cqw] -bottom-[4.8cqw] h-[8cqw] rounded-full opacity-50 blur-[3.2cqw] pointer-events-none"
        style="
          background: radial-gradient(
            ellipse,
            rgba(201, 169, 97, 0.4),
            transparent 70%
          );
        "
      ></div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import QRCodeVue3 from "qrcode-vue3";


defineOptions({ name: 'MemberCard' })

const props = defineProps({
  /**
   * Card data from the parent.
   * Expected shape:
   * {
   *   org:    { logo, name_zh, name_pt },
   *   member: {
   *     number, name_zh, name_en,
   *     issueAt, validTo,
   *     memberType, avatarSrc, nameCardLink
   *   }
   * }
   * All fields optional — missing values fall back to `defaults`.
   */
  data: { type: Object, default: () => ({}) },
})

const isFlipped = ref(false)

const toggleFlip = () => {
  isFlipped.value = !isFlipped.value
}

/* ------------------------------------------------------------------
 * Defaults — used when a field is not provided by the parent.
 * ------------------------------------------------------------------ */
const defaults = {
  logoSrc: '/storage/images/site_logo.png',
  avatarSrc: '',

  organization: {
    nameChinese: '澳門公務專業人員協會',
    namePortuguese: 'Associação dos Técnicos da Administração Pública de Macau',
  },

  labels: {
    memberType: 'Member Type',
    member: 'Member',
    issueAt: 'Issue at',
    validTo: 'Valid to',
    scanHint: 'Scan · 會員檔案',
  },

  memberType: {
    value: '資深會員',
  },

  memberNumber: {
    part1: '0000',
    part2: '0000',
  },

  member: {
    nameChinese: '陳大文',
    nameEnglish: 'CHAN TAI MAN',
  },

  validity: {
    issueAt: '2024 · 01 · 15',
    validTo: '2029 · 01 · 14',
  },

  nameCardLink: '',
  showVerifiedBadge: true,
  maxWidth: '500px',
}

/* ------------------------------------------------------------------
 * Merged card — maps the incoming { org, member } shape onto
 * the internal shape the template expects.
 * ------------------------------------------------------------------ */
const card = computed(() => {
  const d = defaults
  const u = props.data || {}
  const org = u.org || {}
  const mem = u.member || {}

  const rawNumber = String(mem.number ?? '')
    .replace(/\D/g, '')
    .padStart(8, '0')
    .slice(0, 8)

  return {
    logoSrc: org.logo || d.logoSrc,

    avatarSrc:
      typeof mem.avatarSrc === 'string' && mem.avatarSrc.trim() !== ''
        ? mem.avatarSrc
        : '',

    organization: {
      nameChinese: org.name_zh || d.organization.nameChinese,
      namePortuguese: org.name_pt || d.organization.namePortuguese,
    },

    labels: { ...d.labels, ...(u.labels || {}) },

    memberType: {
      value: mem.memberType || d.memberType.value,
    },

    memberNumber: {
      part1: rawNumber.slice(0, 4),
      part2: rawNumber.slice(4, 8),
    },

    member: {
      nameChinese: mem.name_zh || d.member.nameChinese,
      nameEnglish: mem.name_en || d.member.nameEnglish,
    },

    validity: {
      issueAt: mem.issueAt || d.validity.issueAt,
      validTo: mem.validTo || d.validity.validTo,
    },

    nameCardLink: mem.nameCardLink || d.nameCardLink,

    showVerifiedBadge: u.showVerifiedBadge ?? d.showVerifiedBadge,
    maxWidth: u.maxWidth ?? d.maxWidth,
  }
})

/* ------------------------------------------------------------------
 * QR value — the string encoded into the QR code.
 * Prefers nameCardLink, falls back to a members route.
 * ------------------------------------------------------------------ */
const qrValue = computed(() => {
  if (card.value.nameCardLink) {
    return card.value.nameCardLink
  }

  const origin =
    typeof window !== 'undefined' ? window.location.origin : ''

  return `${origin}/members/${card.value.memberNumber.part1}${card.value.memberNumber.part2}`
})
</script>

<style scoped>
@keyframes cardShine {
  0% {
    background-position: 0% 0%;
  }
  50% {
    background-position: 100% 100%;
  }
  100% {
    background-position: 0% 0%;
  }
}
</style>