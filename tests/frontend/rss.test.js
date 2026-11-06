import assert from 'node:assert/strict';
import test from 'node:test';
import { fileURLToPath } from 'node:url';
import { createServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import { createSSRApp } from 'vue';
import { renderToString } from '@vue/server-renderer';
import { parseRss } from '../../resources/js/utils/parseRss.js';

test('parses RSS feeds with one or several items', () => {
    const item = '<item><title>News</title><link>https://example.test/news</link></item>';
    assert.equal(parseRss(`<rss><channel>${item}</channel></rss>`)[0].title, 'News');
    assert.equal(parseRss(`<rss><channel>${item}${item}</channel></rss>`).length, 2);
});

test('accepts empty channels and rejects invalid feeds', () => {
    assert.deepEqual(parseRss('<rss><channel><title>News</title></channel></rss>'), []);
    assert.deepEqual(parseRss('<rss><channel /></rss>'), []);
    assert.throws(() => parseRss('<rss><channel>'), /Invalid RSS XML/);
    assert.throws(() => parseRss('<html><body>Error</body></html>'), /missing channel/);
    assert.throws(() => parseRss('<rss><channel><item>invalid</item></channel></rss>'), /malformed item/);
});

test('RSS component requests the correct route and renders success or an explicit error', async () => {
    const server = await createServer({
        configFile: false,
        logLevel: 'silent',
        server: { middlewareMode: true },
        plugins: [vue()],
        resolve: { alias: { '@': fileURLToPath(new URL('../../resources/js', import.meta.url)) } },
    });
    const originalFetch = globalThis.fetch;
    const originalRoute = globalThis.route;
    const originalError = console.error;
    const errors = [];
    try {
        const { default: component } = await server.ssrLoadModule('/resources/js/Components/RSSNews.vue');
        globalThis.route = name => {
            assert.equal(name, 'host.getRss');
            return '/getRss';
        };
        console.error = error => errors.push(error);
        const state = component.data();
        const render = async () => {
            const app = createSSRApp({ ...component, data: () => state });
            app.config.globalProperties.$t = key => key;
            app.component('a-divider', { template: '<hr>' });
            return renderToString(app);
        };

        globalThis.fetch = async url => {
            assert.equal(url, '/getRss');
            return { ok: true, text: async () => '<rss><channel><item><title>Test news</title></item></channel></rss>' };
        };
        await component.methods.getRss.call(state);
        assert.equal(state.loading, false);
        assert.equal(state.error, null);
        assert.match(await render(), /Test news/);

        for (const fetchResponse of [
            async () => ({ ok: false, status: 502 }),
            async () => ({ ok: true, text: async () => '<html />' }),
            async () => { throw new Error('Network failed'); },
        ]) {
            globalThis.fetch = fetchResponse;
            await component.methods.getRss.call(state);
            assert.equal(state.loading, false);
            assert.equal(state.error, true);
            const html = await render();
            assert.match(html, /rss_load_failed/);
            assert.doesNotMatch(html, /Test news/);
        }
        assert.equal(errors.length, 3);
    } finally {
        globalThis.fetch = originalFetch;
        if (originalRoute === undefined) delete globalThis.route;
        else globalThis.route = originalRoute;
        console.error = originalError;
        await server.close();
    }
});
