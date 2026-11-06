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
        sections: { actions: { save: 'Save' }, content: { 'article.title': 'Article' } },
    };

    const prepared = prepareTranslations(messages).default;
    assert.equal(prepared['nested.deeper.label'], 'Explicit');
    assert.equal(prepared['nested.list.0'], 'First');
    assert.equal(prepared['nested.list.1'], 'Second');
    assert.equal(prepared.simple, 'Plain');
    assert.equal(prepared.save, 'Save');
    assert.equal(prepared['article.title'], 'Article');
    assert.equal(prepared['sections.actions.save'], 'Save');
    assert.deepEqual(prepared.nested, messages.nested);
    assert.deepEqual(prepareTranslations({ simple: 'Plain' }).default, { simple: 'Plain' });
    const conflicting = prepareTranslations({
        'nested.label': 'Explicit',
        nested: { label: 'Nested' },
        member: { title: 'Member title' },
        sections: { members: { member: 'Member' } },
    }).default;
    assert.equal(conflicting['nested.label'], 'Explicit');
    assert.equal(conflicting.member, 'Member');
    assert.equal(conflicting['member.title'], 'Member title');
});

test('translation library resolves every grouped and nested language-pack key', async () => {
    globalThis.window = {};
    globalThis.document = { documentElement: { lang: 'en', setAttribute() {} } };
    const { I18n } = await import('laravel-vue-i18n');
    const resolve = async lang => prepareTranslations(
        JSON.parse(fs.readFileSync(new URL(`../../lang/${lang}.json`, import.meta.url), 'utf8')),
    );
    const i18n = new I18n({ resolve, fallbackLang: 'en' });

    for (const lang of ['en', 'zh', 'pt']) {
        await i18n.loadLanguageAsync(lang);
        const messages = JSON.parse(fs.readFileSync(new URL(`../../lang/${lang}.json`, import.meta.url), 'utf8'));
        const aliases = new Set();
        for (const [section, entries] of Object.entries(messages.sections)) {
            for (const [key, value] of Object.entries(entries)) {
                assert.ok(!aliases.has(key), `${lang}: duplicate alias ${key}`);
                aliases.add(key);
                assert.equal(i18n.trans(key), value, `${lang}: ${key}`);
                assert.equal(i18n.trans(`sections.${section}.${key}`), value, `${lang}: ${section}.${key}`);
            }
        }
        for (const [group, entries] of Object.entries(messages)) {
            if (group !== 'sections' && entries && typeof entries === 'object' && !Array.isArray(entries)) {
                for (const [key, value] of Object.entries(entries)) {
                    if (typeof value === 'string') {
                        assert.equal(i18n.trans(`${group}.${key}`), value, `${lang}: ${group}.${key}`);
                    }
                }
            }
        }
    }
});

test('all locales contain the same keys and interpolation placeholders', () => {
    const locales = Object.fromEntries(['en', 'zh', 'pt'].map(lang => [
        lang,
        prepareTranslations(JSON.parse(fs.readFileSync(new URL(`../../lang/${lang}.json`, import.meta.url), 'utf8'))).default,
    ]));
    const legacyKeys = messages => Object.keys(messages)
        .filter(key => !key.startsWith('sections.') && typeof messages[key] === 'string')
        .sort();
    const placeholders = value => [...value.matchAll(/:[A-Za-z_][A-Za-z_0-9]*/g)]
        .map(match => match[0])
        .sort();
    const expected = legacyKeys(locales.en);

    for (const lang of ['zh', 'pt']) {
        assert.deepEqual(legacyKeys(locales[lang]), expected);
        for (const key of expected) {
            assert.deepEqual(placeholders(locales[lang][key]), placeholders(locales.en[key]), `${lang}: ${key}`);
        }
    }
});
