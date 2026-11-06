import assert from 'node:assert/strict';
import test from 'node:test';
import { createServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import { createSSRApp } from 'vue';
import { renderToString } from '@vue/server-renderer';

test('member card applies background colors with a gradient on both sides', async () => {
    const server = await createServer({
        configFile: false,
        logLevel: 'silent',
        server: { middlewareMode: true },
        resolve: { alias: { 'qrcode-vue3': '\0stub-qr-code' } },
        plugins: [
            {
                name: 'stub-qr-code',
                resolveId(id) {
                    if (id === '\0stub-qr-code') return id;
                },
                load(id) {
                    if (id === '\0stub-qr-code') return 'export default { render() { return null } }';
                },
            },
            vue(),
        ],
    });
    try {
        const { default: MemberCard } = await server.ssrLoadModule('/resources/js/Components/Member/MemberCard.vue');
        async function render(org) {
            return renderToString(createSSRApp(MemberCard, { data: { org } }));
        }

        const styled = await render({
            logo: '/organization.png',
            card_style: { logo: '/style.png', color: 'blue', bg_color: '#123456' },
        });
        assert.equal((styled.match(/src="\/style\.png"/g) || []).length, 2);
        assert.equal((styled.match(/background-color:#123456;background-image:linear-gradient\(/g) || []).length, 2);
        assert.doesNotMatch(styled, /background-color:blue/);

        const bgOnly = await render({ card_style: { bg_color: ' green ' } });
        assert.equal((bgOnly.match(/background-color:green;background-image:linear-gradient\(/g) || []).length, 2);

        const colorOnly = await render({ logo: '/organization.png', card_style: { color: '#123456' } });
        assert.equal((colorOnly.match(/background-color:#123456;background-image:linear-gradient\(/g) || []).length, 2);

        const emptyBg = await render({ card_style: { bg_color: ' ', color: 'blue' } });
        assert.equal((emptyBg.match(/background-color:blue;background-image:linear-gradient\(/g) || []).length, 2);

        const fallback = await render({ card_style: { color: '' } });
        assert.doesNotMatch(fallback, /background-color:/);
        assert.match(fallback, /from-\[#0e2a63\] via-\[#153a8a\] to-\[#0a1f4d\]/);
        assert.match(fallback, /from-\[#0a1f4d\] via-\[#0e2a63\] to-\[#081736\]/);

        const legacy = await render({ card_style: 'card_01' });
        assert.doesNotMatch(legacy, /background-color:/);

        const missing = await render({});
        assert.doesNotMatch(missing, /background-color:/);

        const fontColor = await render({ card_style: { font_color: ' #123456 ' } });
        assert.equal((fontColor.match(/card-custom-font/g) || []).length, 2);
        assert.equal((fontColor.match(/--card-font-color:#123456/g) || []).length, 2);
        assert.match(fontColor, /text-\[#e0c98a\]/);

        for (const cardStyle of [undefined, null, {}, { font_color: ' ' }, { font_color: null }, 'card_01']) {
            const html = await render({ card_style: cardStyle });
            assert.equal((html.match(/card-custom-font/g) || []).length, 2);
            assert.equal((html.match(/--card-font-color:white/g) || []).length, 2);
        }

        const allStyles = await render({ card_style: {
            bg_image: 'card_01.png',
            bg_color: 'blue',
            font_color: 'white',
            font_style: 'font-family:sans-serif;color:#c24641',
        } });
        assert.equal((allStyles.match(/\/storage\/images\/card_01\.png/g) || []).length, 2);
        assert.equal((allStyles.match(/background-image:linear-gradient\(/g) || []).length, 2);
        assert.equal((allStyles.match(/--card-font-color:white/g) || []).length, 2);
        assert.equal((allStyles.match(/font-family:sans-serif;color:#c24641/g) || []).length, 2);

        const fontFallback = await render({ card_style: { font_style: 'font-family:serif;color:red' } });
        assert.equal((fontFallback.match(/--card-font-color:red/g) || []).length, 2);

        const fontPriority = await render({ card_style: { font_color: 'blue', font_style: 'color:red' } });
        assert.equal((fontPriority.match(/--card-font-color:blue/g) || []).length, 2);
    } finally {
        await server.close();
    }
});
