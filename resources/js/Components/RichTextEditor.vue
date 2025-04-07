<template>
  <div>
    <div ref="editor" class="quill-editor"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, defineProps, defineEmits } from 'vue';
import Quill from 'quill';
import 'quill/dist/quill.snow.css'; // Optional theme

// Define props
const props = defineProps({
  modelValue: String,
  type:{
    type: String,
    default: 'basic'
  }
});

// Define emits
const emit = defineEmits(['update:modelValue']);

// Create a ref for the editor element
const editor = ref(null);
const quill = ref(null);
// Define toolbar options based on type
const toolbarOptions = {
  basic: [
    [{ 'header': 1 }, { 'header': 2 }, { 'header': 3 }],               // custom button values
    ['bold', 'italic', 'underline'],
    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
    ['clean'], // Remove formatting button
  ],
  advanced: [
    [{ 'header': 1 }, { 'header': 2 }, { 'header': 3 }],               // custom button values
    ['bold', 'italic', 'underline'],
    [{ 'align': [] }],
    [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
    ['link', 'image', 'video'],
    [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
    [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
    ['clean'], // Remove formatting button
  ],
  full: [
    ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
    ['blockquote', 'code-block'],
    ['link', 'image', 'video', 'formula'],

    [{ 'header': 1 }, { 'header': 2 }],               // custom button values
    [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
    [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
    [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
    [{ 'direction': 'rtl' }],                         // text direction

    [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

    [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
    [{ 'font': [] }],
    [{ 'align': [] }],
    ['clean']                                         // remove formatting button
  ],
};
// // Register custom fonts
// const Font = Quill.import('formats/font');
// Font.whitelist = ['Noto-Sans-TC', 'times-new-roman']; // Add your custom fonts here
// Quill.register(Font, true);


onMounted(() => {
  // Initialize Quill
  quill.value = new Quill(editor.value, {
    theme: 'snow', // Specify theme
    modules: {
      toolbar: toolbarOptions[props.type] || toolbarOptions.basic,
    },
    placeholder: 'Start writing...',
  });

  // Set initial content
  if (props.modelValue) {
    quill.value.root.innerHTML = props.modelValue;
  }

  // Handle content changes
  quill.value.on('text-change', () => {
    emit('update:modelValue', quill.value.root.innerHTML); // Emit updated content
  });
});
</script>

<style scoped>
.quill-editor {
  height: 300px; /* Set height for the editor */
}
</style>