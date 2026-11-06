export function prepareTranslations(messages) {
    const flattened = {};

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
    return { default: { ...flattened, ...messages } };
}
