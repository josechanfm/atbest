<template>
    <div class="rounded-2xl overflow-hidden border border-gray-300 bg-white focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 transition-all">
        <!-- Toolbar -->
        <div class="flex items-center gap-1.5 bg-gray-50 px-3 py-2 border-b border-gray-200 flex-wrap">
            
            <!-- Block Format Dropdown (H1, H2, H3, Paragraph) -->
            <select 
                @change="formatBlock($event)" 
                class="text-xs bg-white border border-gray-300 rounded-lg px-2.5 py-1 text-gray-700 shadow-sm outline-none focus:ring-1 focus:ring-indigo-500"
                title="段落樣式"
            >
                <option value="p">內文 (Paragraph)</option>
                <option value="h1">標題 1 (Heading 1)</option>
                <option value="h2">標題 2 (Heading 2)</option>
                <option value="h3">標題 3 (Heading 3)</option>
            </select>

            <div class="h-4 w-px bg-gray-300 mx-1"></div>

            <button type="button" @click="formatText('bold')" class="px-2.5 py-1 text-xs font-bold bg-white border border-gray-300 rounded-lg hover:bg-gray-100 text-gray-700 shadow-sm" title="粗體">B</button>
            <button type="button" @click="formatText('italic')" class="px-2.5 py-1 text-xs italic bg-white border border-gray-300 rounded-lg hover:bg-gray-100 text-gray-700 shadow-sm" title="斜體">I</button>
            <button type="button" @click="formatText('underline')" class="px-2.5 py-1 text-xs underline bg-white border border-gray-300 rounded-lg hover:bg-gray-100 text-gray-700 shadow-sm" title="底線">U</button>
            
            <div class="h-4 w-px bg-gray-300 mx-1"></div>
            
            <!-- Bullet List Icon Button -->
            <button 
                type="button" 
                @click="formatText('insertUnorderedList')" 
                class="p-1.5 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 text-gray-700 shadow-sm transition-colors" 
                title="項目清單 (Bullets)"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16M2 6h.01M2 12h.01M2 18h.01" />
                </svg>
            </button>

            <!-- Numbered/Digit List Icon Button -->
            <button 
                type="button" 
                @click="formatText('insertOrderedList')" 
                class="p-1.5 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 text-gray-700 shadow-sm transition-colors" 
                title="編號清單 (Digits)"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 6h13M7 12h13M7 18h13M3 5v2m0 4v2m0 4v2" />
                </svg>
            </button>
        </div>

        <!-- ContentEditable Area -->
        <div 
            contenteditable="true"
            @input="onInput"
            ref="editableDiv"
            class="w-full p-4 text-sm text-gray-800 min-h-[180px] max-h-[350px] overflow-y-auto outline-none prose prose-indigo max-w-none"
            :placeholder="placeholder"
        ></div>
    </div>
</template>

<script>
export default {
    name: 'SimpleEditor',
    props: {
        modelValue: {
            type: String,
            default: ''
        },
        placeholder: {
            type: String,
            default: '請在此輸入內容...'
        }
    },
    emits: ['update:modelValue'],
    mounted() {
        if (this.$refs.editableDiv) {
            this.$refs.editableDiv.innerHTML = this.modelValue || '<p></p>';
        }
    },
    watch: {
        modelValue(newValue) {
            if (this.$refs.editableDiv && newValue !== this.$refs.editableDiv.innerHTML) {
                this.$refs.editableDiv.innerHTML = newValue || '<p></p>';
            }
        }
    },
    methods: {
        formatText(command) {
            document.execCommand(command, false, null);
            this.onInput();
        },
        formatBlock(event) {
            const tag = event.target.value;
            document.execCommand('formatBlock', false, `<${tag}>`);
            this.onInput();
        },
        onInput() {
            const html = this.$refs.editableDiv ? this.$refs.editableDiv.innerHTML : '';
            this.$emit('update:modelValue', html);
        }
    }
}
</script>

<style scoped>
[contenteditable]:empty:before {
    content: attr(placeholder);
    color: #9ca3af;
    pointer-events: none;
    display: block;
}
[contenteditable] ul {
    list-style-type: disc;
    padding-left: 1.5rem;
}
[contenteditable] ol {
    list-style-type: decimal;
    padding-left: 1.5rem;
}
[contenteditable] h1 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
}
[contenteditable] h2 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
}
[contenteditable] h3 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
}
</style>