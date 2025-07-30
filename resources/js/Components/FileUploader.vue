<template>
  <div>
    <template v-if="file">
      <img :src="file" width="300px" />
      <a @click="onDeleteImage">Delete</a>
    </template>
    <template v-else>
      <template v-if="previewImage">
        <img :src="previewImage" width="300px" />
        <a @click="onRemoveImage">Remove</a>
      </template>
      <template v-else>
        <div class="flex items-center justify-center w-64">
          <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
            <div class="flex flex-col items-center justify-center pt-5 pb-6">
              <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
              </svg>
              <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                <div v-html="$t('upload_drag_drop')"/>
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t('image_size_note') }}</p>
            </div>
            <div>
              <input id="dropzone-file" type="file" @change="beforeUpload" :accept="allowedTypes" style="display:none" />
            </div>
          </label>
        </div>
      </template>
    </template>


  </div>
</template>

<script>
import { UploadOutlined } from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';

export default {
  name: 'FileUpload',
  components: {
    UploadOutlined,
  },
  props: {
    file:{
      type: String,
    },
    maxSize: {
      type: Number,
      required: true,
      default: 2048, // 2MB
    },
    maxWidth: {
      type: Number,
      default: 800, // 2MB
    },
    maxHeight: {
      type: Number,
      default: 600, // 2MB
    },
    imageTypes: {
      type: Array,
      default: () => ['image/jpeg', 'image/png','image/gif'],
    },
    allowedTypes: {
      type: Array,
      required: true,
      default: () => ['image/jpeg', 'image/png', 'application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
    },
  },
  data() {
    return {
      previewImage: null,
    };
  },
  methods: {
    onSelectFile(file){
      console.log(file)
    },
    beforeUpload(event) {
      const file =event.target.files[0] 
      const isValidType = this.allowedTypes.includes(file.type)
      const isValidSize = file.size / 1024 <= this.maxSize
      const isImage = this.imageTypes.includes(file.type)

      if (!isValidType) {
        message.error('Invalid file type. Please upload a PDF, Word, Excel, or image file.');
        return false;
      }

      if (!isImage && !isValidSize) {
        message.error(`File must be smaller than ${this.maxSize}KB!`);
        return false;
      }

      if (file.type.startsWith('image/')) {
        this.resizeImage(file).then((resizedFile) => {
          this.previewImage = URL.createObjectURL(resizedFile);
          this.$emit('upload', resizedFile);
        });
      } else {
        this.$emit('upload', file);
      }

      return false;
    },
    resizeImage(file) {
      return new Promise((resolve) => {
        const img = new Image();
        img.src = URL.createObjectURL(file);
        img.onload = () => {
          let { width, height } = img;
          let canvas = document.createElement('canvas');
          let scale = 1;

          if (width > this.maxWidth) {
            scale = this.maxWidth / width;
          }
          if (height > this.maxHeight) {
            scale = Math.min(scale, this.maxHeight / height);
          }

          width *= scale;
          height *= scale;
          canvas.width = width;
          canvas.height = height;

          const ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0, width, height);

          canvas.toBlob((blob) => {
            resolve(blob);
          }, file.type);
        };
      });
    },
    onDeleteImage(){
      this.$emit('delete',this.file);
      console.log('in compoeent delete');
    },
    onRemoveImage(){
      this.previewImage=null
      this.$emit('upload', null);
      console.log('to remove image')
    }
  },
};
</script>

<style scoped>
.preview-container {
  margin-top: 1rem;
}
</style>