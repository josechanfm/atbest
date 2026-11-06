import { XMLParser, XMLValidator } from 'fast-xml-parser';

export function parseRss(xml) {
    const validation = XMLValidator.validate(xml);
    if (validation !== true) {
        throw new Error(`Invalid RSS XML: ${validation.err.msg}`);
    }

    const result = new XMLParser({
        ignoreAttributes: false,
        attributeNamePrefix: '@_',
        allowBooleanAttributes: true,
        parseNodeValue: true,
        parseAttributeValue: true,
        trimValues: true,
    }).parse(xml);
    let channel = result.rss?.channel;
    if (channel === '') channel = {};
    if (!channel || typeof channel !== 'object' || Array.isArray(channel)) {
        throw new Error('Invalid RSS feed: missing channel');
    }
    if (channel.item == null) return [];
    const items = Array.isArray(channel.item) ? channel.item : [channel.item];
    if (items.some(item => !item || typeof item !== 'object' || Array.isArray(item))) {
        throw new Error('Invalid RSS feed: malformed item');
    }
    return items;
}
