<template>
    <div class="min-h-screen bg-slate-150 flex items-center justify-center p-2">

        <div class="w-full max-w-lg space-y-4">

            <!-- Card -->
            <div class="bg-slate-100 rounded-3xl border border-indigo-500/30 overflow-hidden">

                <!-- Header -->
                <div class="p-8 text-center flex flex-col items-center">

                    <span
                        v-if="businessCard.user?.member?.organization?.name_zh || businessCard.user?.member?.organization?.name_en"
                        class="text-xl font-bold uppercase tracking-widest text-cyan-200 bg-indigo-600 px-4 py-1.5 rounded-full mb-6"
                    >
                        {{ businessCard.user.member.organization.name_zh || businessCard.user.member.organization.name_en }}
                    </span>

                    <img v-if="businessCard.user?.member?.avatar" :src="businessCard.user.member.avatar" alt="Avatar" class="w-28 h-28 rounded-full object-cover border-2 border-indigo-400/50 mb-5" />
                    <div v-else class="w-28 h-28 rounded-full bg-indigo-950 border-2 border-indigo-400/50 flex items-center justify-center text-indigo-300 mb-5">
                        <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                        </svg>
                    </div>

                    <h1 class="text-3xl font-extrabold text-indigo-500">
                        {{ businessCard.content?.name_zh || businessCard.content?.name_en || '姓名 Name' }}
                    </h1>
                    <p v-if="businessCard.content?.name_zh && businessCard.content?.name_en" class="text-sm font-semibold text-indigo-500 mt-1">
                        {{ businessCard.content.name_en }}
                    </p>
                    <p v-if="businessCard.content?.title_zh || businessCard.content?.title_en" class="text-base font-semibold text-indigo-500 mt-3">
                        {{ businessCard.content.title_zh || businessCard.content.title_en }}
                    </p>
                </div>

                <!-- Contact List -->
                <div v-if="contactFields.length > 0" class="px-2 pb-6 space-y-3">
                    <component
                        v-for="(field, index) in contactFields"
                        :key="index"
                        :is="getFieldWrapper(field)"
                        :href="getFieldLink(field)"
                        :target="isExternal(field) ? '_blank' : undefined"
                        :rel="isExternal(field) ? 'noopener noreferrer' : undefined"
                        class="flex items-center justify-between gap-4 px-5 py-3.5 rounded-2xl bg-slate-100 text-slate-900"
                    >
                        <span class="text-xs font-bold uppercase text-indigo-700">{{ field.label }}</span>
                        <span class="text-sm font-semibold truncate">{{ field.value }}</span>
                    </component>
                </div>

                <!-- Bio -->
                <div
                    v-if="businessCard.bio?.details"
                    class="p-2 bg-slate-200 border-t border-indigo-500/20"
                >
                    <div
                        class="prose prose-sm max-w-none
                            leading-snug
                            prose-p:my-1 prose-p:leading-snug
                            prose-ul:my-1 prose-ul:leading-snug prose-ul:list-disc prose-ul:pl-5
                            prose-ol:my-1 prose-ol:leading-snug prose-ol:list-decimal prose-ol:pl-5
                            prose-li:my-0 prose-li:leading-snug prose-li:marker:text-slate-800
                            prose-headings:my-2 prose-headings:leading-tight
                            prose-headings:text-slate-800
                            prose-h1:text-2xl prose-h1:font-bold
                            prose-h2:text-2xl prose-h2:font-bold
                            prose-h3:text-xl prose-h3:font-semibold
                            prose-p:text-indigo-500
                            prose-strong:text-slate-500
                            prose-a:text-cyan-300
                            prose-blockquote:bg-slate-100"
                        v-html="businessCard.bio.details"
                    />
                </div>

                <!-- QR Code + Share Section -->
                <div class="px-2 py-8 bg-slate-100 border-t border-indigo-500/20 flex flex-col items-center">
                    <p class="text-xs font-bold uppercase tracking-widest text-indigo-700 mb-4">
                        掃描分享名片 (Scan to Share)
                    </p>

                    <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-sm mb-5">
                        <img
                            v-if="qrDataUrl"
                            ref="qrImage"
                            :src="qrDataUrl"
                            alt="QR Code"
                            class="w-40 h-40"
                        />
                        <div v-else class="w-40 h-40 flex items-center justify-center text-slate-300">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center justify-center gap-3">

                        <!-- Share -->
                        <button
                            type="button"
                            @click="shareCard"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                            <span>分享名片</span>
                        </button>

                        <!-- Copy Link -->
                        <button
                            type="button"
                            @click="copyLink"
                            :class="copiedLink ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-slate-800 hover:bg-slate-900'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-white text-xs font-bold rounded-xl shadow-sm transition-all"
                        >
                            <svg v-if="!copiedLink" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <span>{{ copiedLink ? '已複製連結' : '複製連結' }}</span>
                        </button>

                        <!-- Copy QR Image -->
                        <button
                            type="button"
                            @click="copyQrImage"
                            :disabled="!qrDataUrl"
                            :class="copiedImage ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-slate-800 hover:bg-slate-900'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-white text-xs font-bold rounded-xl shadow-sm transition-all disabled:opacity-50"
                        >
                            <svg v-if="!copiedImage" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <span>{{ copiedImage ? '已複製 QR' : '複製 QR Code' }}</span>
                        </button>

                    </div>
                </div>

            </div>

            <p class="text-center text-xs text-slate-400">Digital Business Card System</p>

        </div>
    </div>
</template>

<script>
import QRCode from 'qrcode';

const LABELS = {
    mobile: '手機 Mobile',
    phone: '電話 Phone',
    office_tel: '辦公室電話 Office Tel',
    email: '電郵 Email',
    fax: '傳真 Fax',
    postal_address: '通訊地址 Address',
    website: '網站 Website',
    linkedin: 'LinkedIn',
    facebook: 'Facebook',
    youtube: 'YouTube',
    github: 'GitHub',
    line: 'LINE',
    wechat: '微信 WeChat',
    whatsapp: 'WhatsApp',
    telegram: 'Telegram',
    instagram: 'Instagram',
    x_twitter: 'X / Twitter',
    tiktok: 'TikTok',
};

const EXTERNAL_KEYS = [
    'postal_address',
    'website', 'linkedin', 'facebook', 'youtube', 'github',
    'whatsapp', 'telegram', 'instagram', 'x_twitter', 'tiktok',
];

const hasProtocol = (v) => /^https?:\/\//i.test(v);
const stripAt = (v) => v.replace(/^@/, '');
const digitsOnly = (v) => v.replace(/[^\d]/g, '');

export default {
    name: 'BusinessCardShow',
    props: {
        businessCard: { type: Object, required: true }
    },
    data() {
        return {
            qrDataUrl: '',
            copiedLink: false,
            copiedImage: false,
            _linkTimer: null,
            _imageTimer: null,
        };
    },
    computed: {
        shareUrl() {
            return window.location.href;
        },
        shareTitle() {
            const c = this.businessCard.content || {};
            return c.name_zh || c.name_en || '數位名片';
        },
        shareText() {
            const c = this.businessCard.content || {};
            const name = c.name_zh || c.name_en || '';
            const title = c.title_zh || c.title_en || '';
            const org = this.businessCard.user?.member?.organization?.name_zh
                     || this.businessCard.user?.member?.organization?.name_en
                     || '';
            // Compose a description-like message
            return [name, title, org].filter(Boolean).join(' · ');
        },
        contactFields() {
            const content = this.businessCard?.content || {};
            const coreKeys = ['name_zh', 'name_en', 'title_zh', 'title_en'];

            return Object.entries(content)
                .filter(([key]) => !coreKeys.includes(key))
                .map(([key, value]) => ({
                    key,
                    label: LABELS[key] || key,
                    value,
                }))
                .filter(item => item.value);
        }
    },
    async mounted() {
        try {
            this.qrDataUrl = await QRCode.toDataURL(this.shareUrl, {
                width: 400,
                margin: 1,
                color: { dark: '#0f172a', light: '#ffffff' },
            });
        } catch (e) {
            console.error('QR generation failed', e);
        }
    },
    beforeUnmount() {
        clearTimeout(this._linkTimer);
        clearTimeout(this._imageTimer);
    },
    methods: {
        getFieldWrapper(field) {
            return this.getFieldLink(field) ? 'a' : 'div';
        },

        getFieldLink(field) {
            const v = String(field.value ?? '').trim();
            if (!v) return undefined;

            switch (field.key) {
                case 'email':
                    return `mailto:${v}`;
                case 'mobile':
                case 'phone':
                case 'office_tel':
                    return `tel:${v.replace(/[^\d+]/g, '')}`;
                case 'postal_address':
                    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(v)}`;
                case 'website':
                    return hasProtocol(v) ? v : `https://${v}`;
                case 'linkedin':
                    return hasProtocol(v) ? v : `https://www.linkedin.com/in/${stripAt(v)}`;
                case 'facebook':
                    return hasProtocol(v) ? v : `https://www.facebook.com/${stripAt(v)}`;
                case 'youtube':
                    return hasProtocol(v) ? v : `https://www.youtube.com/@${stripAt(v)}`;
                case 'github':
                    return hasProtocol(v) ? v : `https://github.com/${stripAt(v)}`;
                case 'whatsapp':
                    return `https://wa.me/${digitsOnly(v)}`;
                case 'telegram':
                    return `https://t.me/${stripAt(v)}`;
                case 'instagram':
                    return `https://www.instagram.com/${stripAt(v)}`;
                case 'x_twitter':
                    return `https://x.com/${stripAt(v)}`;
                case 'tiktok':
                    return `https://www.tiktok.com/@${stripAt(v)}`;
                case 'line':
                case 'wechat':
                default:
                    return undefined;
            }
        },

        isExternal(field) {
            const link = this.getFieldLink(field);
            if (!link) return false;
            if (link.startsWith('mailto:') || link.startsWith('tel:')) return false;
            return EXTERNAL_KEYS.includes(field.key);
        },

        async shareCard() {
            const text = `${this.shareText}\n${this.shareUrl}`;

            if (navigator.share) {
                try {
                    await navigator.share({
                        title: this.shareTitle,
                        text,
                    });
                    return;
                } catch (err) {
                    if (err.name === 'AbortError') return;
                    // Other errors fall through to the manual fallback
                }
            }

            this.fallbackShare();
        },

        fallbackShare() {
            const text = encodeURIComponent(`${this.shareText}\n${this.shareUrl}`);
            window.open(`https://wa.me/?text=${text}`, '_blank');
        },

        async copyLink() {
            try {
                await navigator.clipboard.writeText(this.shareUrl);
                this.copiedLink = true;
                clearTimeout(this._linkTimer);
                this._linkTimer = setTimeout(() => (this.copiedLink = false), 2000);
            } catch (e) {
                console.error('Copy link failed', e);
            }
        },

        async copyQrImage() {
            if (!this.qrDataUrl) return;
            try {
                const blob = await (await fetch(this.qrDataUrl)).blob();
                await navigator.clipboard.write([
                    new ClipboardItem({ [blob.type]: blob }),
                ]);
                this.copiedImage = true;
                clearTimeout(this._imageTimer);
                this._imageTimer = setTimeout(() => (this.copiedImage = false), 2000);
            } catch (e) {
                console.error('Copy QR image failed', e);
                window.open(this.qrDataUrl, '_blank');
            }
        },
    }
}
</script>