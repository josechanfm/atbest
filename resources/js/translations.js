export function prepareTranslations(messages) {
    const flattened = {};
    const aliases = {};

    function flatten(value, prefix) {
        if (value !== null && typeof value === 'object') {
            for (const [key, child] of Object.entries(value)) {
                flatten(child, prefix ? `${prefix}.${key}` : key);
            }
        } else {
            flattened[prefix] = value;
        }
    }

    flatten(messages, '');
    for (const group of Object.values(messages.sections || {})) {
        Object.assign(aliases, group);
    }

    const directEntries = Object.fromEntries(
        Object.entries(messages).filter(([, value]) => value === null || typeof value !== 'object'),
    );
    return { default: { ...messages, ...flattened, ...aliases, ...directEntries } };
}
