import assert from 'node:assert/strict';
import fs from 'node:fs';
import test from 'node:test';
import 'vue';
import { prepareTranslations } from '../../resources/js/translations.js';

test('flattens nested entries while preserving explicit keys and arrays', () => {
    const messages = {
        nested: { deeper: { label: 'Nested' }, list: ['First', 'Second'] },
        'nested.deeper.label': 'Explicit',
        simple: 'Plain',
    };

    const prepared = prepareTranslations(messages).default;
    assert.equal(prepared['nested.deeper.label'], 'Explicit');
    assert.equal(prepared['nested.list.0'], 'First');
    assert.equal(prepared['nested.list.1'], 'Second');
    assert.equal(prepared.simple, 'Plain');
    assert.deepEqual(prepared.nested, messages.nested);
    assert.deepEqual(prepareTranslations({ simple: 'Plain' }).default, { simple: 'Plain' });
});

test('translation library resolves language-pack keys and switches locales', async () => {
    globalThis.window = {};
    globalThis.document = { documentElement: { lang: 'en', setAttribute() {} } };
    const { I18n } = await import('laravel-vue-i18n');
    const resolve = async lang => prepareTranslations(
        JSON.parse(fs.readFileSync(new URL(`../../lang/${lang}.json`, import.meta.url), 'utf8')),
    );
    const i18n = new I18n({ resolve, fallbackLang: 'en' });

    await i18n.loadLanguageAsync('en');
    assert.equal(i18n.trans('home_menu.member'), 'Member');
    assert.equal(i18n.trans('back'), 'Back');

    await i18n.loadLanguageAsync('zh');
    const zh = JSON.parse(fs.readFileSync(new URL('../../lang/zh.json', import.meta.url), 'utf8'));
    for (const [group, entries] of Object.entries(zh)) {
        if (entries && typeof entries === 'object' && !Array.isArray(entries)) {
            for (const [key, value] of Object.entries(entries)) {
                if (typeof value === 'string') {
                    assert.equal(i18n.trans(`${group}.${key}`), value);
                }
            }
        }
    }
    assert.equal(i18n.trans('back'), zh.back);
});
