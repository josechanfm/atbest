<template>
    <div>
        <quill
          v-model="content"
          :options="editorOptions"
          @blur="handleBlur"
          @focus="handleFocus"
        />
        <a-button type="primary" @click="submit">Submit</a-button>
    </div>
  </template>
  
  <script>
  import Quill from "quill";
  import 'quill/dist/quill.snow.css'; // Import Quill styles
  
  export default {
    components: {
      Quill,
    },
    props: {
      title: {
        type: String,
        default: 'Quill Editor',
      },
      value: {
        type: String,
        default: '',
      },
    },
    data() {
      return {
        content: this.value, // Initialize content with the prop value
        editorOptions: {
          theme: 'snow',
          modules: {
            toolbar: [
              [{ header: [1, 2, false] }],
              ['bold', 'italic', 'underline'],
              ['image', 'blockquote', 'code-block'],
              ['clean'],
            ],
          },
        },
      };
    },
    watch: {
      // Watch for changes to the value prop
      value(newValue) {
        this.content = newValue;
      },
    },
    methods: {
      submit() {
        this.$emit('input', this.content); // Emit input event to update the parent
      },
      handleBlur() {
        // Handle blur event if needed
      },
      handleFocus() {
        // Handle focus event if needed
      },
    },
  };
  </script>
  
  <style scoped>
  /* Add any custom styles here */
  </style>