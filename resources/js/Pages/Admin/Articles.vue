<template>
  <AdminLayout :title="$t('articles')">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $t("articles") }}
      </h2>
    </template>
    <div class="flex justify-end pb-3 gap-3">
      <template v-if="isDraggable">
        <a-button type="primary" @click="reloadFormFields">
          {{ $t("finish") }}
        </a-button>
      </template>
      <template v-else>
        <a-button type="primary" @click="isDraggable = !isDraggable">
          {{ $t("dragger_sort") }}
        </a-button>
        <a-button :href="route('admin.articles.create')" as="link" type="primary">
          {{ $t("create_article") }}
        </a-button>
      </template>
    </div>
    
    <div class="container mx-auto">
      <div class="flex flex-auto gap-2">
        <a-select
          class="w-64"
          :placeholder="$t('please_select_category')"
          v-model:value="search.category"
          allowClear
          :options="articleCategories"
        ></a-select>
        <a-input
          v-model:value="search.title"
          :placeholder="$t('please_input_title')"
          class="w-64"
        ></a-input>
        <a-button type="primary" @click="searchData">{{ $t("search") }}</a-button>
        <a-button type="primary" as="link" :href="route('admin.articles.index')">{{ $t("clear") }}</a-button>
      </div>
    </div>

    <div class="container mx-auto pt-5">
      <div class="bg-white relative shadow rounded-lg overflow-x-auto">
        <a-table
          :dataSource="dataModel"
          :columns="columns"
          :customRow="customRow"
          :pagination="pagination"
          @change="onPaginationChange"
        >
          <!-- <template #headerCell="{ column, record, index }">
            <template v-if="isDraggable">
                {{ $t(column.i18n) }}
            </template>
            <template v-else>
              <span v-if="column.dataIndex=='dragger'"></span>
              <span v-else>{{ $t(column.i18n) }}</span>
            </template>
          </template> -->
          <template #bodyCell="{ column, record, index }">
              <template v-if="column.dataIndex === 'operation'">
                <a-button @click="editRecord(record)">{{ $t("edit") }}</a-button>
                <a-button @click="deleteRecord(record)">{{ $t("delete") }}</a-button>
              </template>
              <template v-else-if="column.dataIndex==='category_code'">
                {{ getLable(articleCategories, record.category_code) }}
              </template>
              <template v-else-if="column.dataIndex==='dragger' && isDraggable">
                <holder-outlined />
              </template>
          </template>
        </a-table>
      </div>
    </div>
    <p>Article CAN NOT be delete if published.</p>  



    <!-- Modal Start-->
    <a-modal v-model:open="modal.isOpen" :title="modal.title" width="100%">
      <a-form
        ref="modalRef"
        :model="modal.data"
        name="Teacher"
        layout="vertical"
        autocomplete="off"
        :rules="rules"
        :validate-messages="validateMessages"
      >
        <a-form-item :label="$t('article_category')" name="category_code">
          <a-select
            v-model:value="modal.data.category_code"
            :options="articleCategories"
          />
        </a-form-item>
        <a-form-item :label="$t('title')" name="title">
          <a-input v-model:value="modal.data.title" />
        </a-form-item>
        <a-form-item :label="$t('content')" name="content_en">
          <ckeditor
            :editor="editor"
            v-model="modal.data.content_en"
            :config="editorConfig"
          />
        </a-form-item>
        <a-form-item :label="$t('valid_at')" name="valid_at">
          <a-date-picker
            v-model:value="modal.data.valid_at"
            :format="dateFormat"
            :valueFormat="dateFormat"
          />
        </a-form-item>
        <a-form-item :label="$t('expired_at')" name="expired_at">
          <a-date-picker v-model:value="modal.data.expire_at" :valueFormat="dateFormat" />
        </a-form-item>
        <a-form-item :label="$t('url')" name="url">
          <a-input v-model:value="modal.data.url" />
        </a-form-item>
        <a-row>
          <a-col>
            <a-form-item :label="$t('published')" name="published">
              <a-switch
                v-model:checked="modal.data.published"
                :checkedValue="1"
                :unCheckedValue="0"
                @change="modal.data.for_member = 0"
              />
            </a-form-item>
          </a-col>
          <a-col class="pl-10" v-if="modal.data.published">
            <a-form-item :label="$t('for_member')" name="for_member">
              <a-switch
                v-model:checked="modal.data.for_member"
                :checkedValue="1"
                :unCheckedValue="0"
              />
            </a-form-item>
          </a-col>
        </a-row>
      </a-form>
      <template #footer>
        <a-button
          v-if="modal.mode == 'EDIT'"
          key="Update"
          type="primary"
          @click="updateRecord()"
          >{{ $t("update") }}</a-button
        >
        <a-button
          v-if="modal.mode == 'CREATE'"
          key="Store"
          type="primary"
          @click="storeRecord()"
          >{{ $t("add") }}</a-button
        >
      </template>
    </a-modal>
    <!-- Modal End-->
  </AdminLayout>
</template>

<script>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import { defineComponent, reactive } from "vue";
//import Editor from 'ckeditor5-custom-build/build/ckeditor';
import CKEditor from "@ckeditor/ckeditor5-vue";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import UploadAdapter from "@/Components/ImageUploadAdapter.vue";
import { VueDraggableNext } from "vue-draggable-next";
import { HolderOutlined } from "@ant-design/icons-vue";
import { Empty } from "ant-design-vue";

export default {
  components: {
    AdminLayout,
    Pagination,
    ckeditor: CKEditor.component,
    UploadAdapter,
    draggable: VueDraggableNext,
    HolderOutlined,
    //UploadAdapter
  },
  props: ["classifies", "articleCategories", "articles"],
  data() {
    return {
      dataModel:null,
      sourceObj:null,
      targetObj:null,
      isDraggable: false,
      dateFormat: "YYYY-MM-DD",
      modal: {
        isOpen: false,
        data: { content_en: "" },
        title: "Modal",
        mode: "",
      },
      pagination: {
        total: this.articles.total,
        current: this.articles.current_page,
        pageSize: this.articles.per_page,
      },
      search: {},
      simpleImage: Empty.PRESENTED_IMAGE_SIMPLE,
      originSequences: [],
      pagination: {
        total: this.articles.total,
        current: this.articles.current_page,
        pageSize: this.articles.per_page,
      },
      originalSequences: [],
      editor: ClassicEditor,
      editorData: "<p>Content of the editor.</p>",
      editorConfig: {
        extraPlugins: [
          function (editor) {
            editor.plugins.get("FileRepository").createUploadAdapter = (loader) => {
              return new UploadAdapter(loader);
            };
          },
        ],
        // The configuration of the editor.
      },
      rules: {
        category_code: { required: true },
        classify_id: { required: true },
        title_en: { required: true },
      },
      validateMessages: {
        required: "${label} is required!",
        types: {
          email: "${label} is not a valid email!",
          number: "${label} is not a valid number!",
        },
        number: {
          range: "${label} must be between ${min} and ${max}",
        },
      },
      labelCol: {
        style: {
          width: "150px",
        },
      },
    };
  },
  created() {
    this.originalSequences = this.articles.data.map((a) => a.sequence);
    this.dataModel=this.articles.data
  },
  mounted() {
    this.search = this.route().params.search ?? {};
  },
  computed:{
    columns(){
      var dataColumns = [
          {
            title: this.$t("article_category"),
            dataIndex: "category_code",
          },{
            title: this.$t("title"),
            dataIndex: "title",
          },{
            title: this.$t("valid_at"),
            i18n: "valid_at",
            dataIndex: "valid_at",
          },{
            title: this.$t("expired_at"),
            dataIndex: "expired_at",
          },{
            title: this.$t("published"),
            dataIndex: "published",
          },{
            title: this.$t("operation"),
            dataIndex: "operation",
            key: "operation"
          },
      ];
      if(this.isDraggable){
        dataColumns.unshift({title: this.$t("dragger_sort"), dataIndex: "dragger"});
      }
      return dataColumns
    }
  },
  watch:{
    // isDraggable(val){
    //   console.log(val)
    //   if(val){
    //     this.columns.unshift({title: "Dragger", i18n: "dragger_sort",dataIndex: "dragger"});
    //   }else{
    //     this.columns.shift();

    //   }
    // }
  },
  methods: {
    onShowSizeChange(current, pageSize) {
      this.pagination.pageSize = pageSize;
    },
    rowChange(event) {
      this.articles.data.map((d, i) => {
        d.sequence = this.originalSequences[i];
      });
      this.$inertia.post(route("admin.article.sequence"), this.articles.data, {
        onSuccess: (page) => {
          console.log(page);
        },
        onError: (error) => {
          console.log(error);
        },
      });
    },
    createRecord() {
      this.modal.data = {};
      this.modal.data.for_member = 0;
      this.modal.mode = "CREATE";
      this.modal.title = "Create";
      this.modal.isOpen = true;
    },
    editRecord(record) {
      this.modal.data = { ...record };
      this.modal.mode = "EDIT";
      //this.modal.title = "Edit";
      this.modal.isOpen = true;
    },
    storeRecord() {
      this.$refs.modalRef
        .validateFields()
        .then(() => {
          this.$inertia.post(route("admin.articles.store"), this.modal.data, {
            onSuccess: (page) => {
              this.modal.data = {};
              this.modal.isOpen = false;
            },
            onError: (err) => {
              console.log(err);
            },
          });
        })
        .catch((err) => {
          console.log(err);
        });
    },
    updateRecord() {
      this.$refs.modalRef
        .validateFields()
        .then(() => {
          this.$inertia.put(
            route("admin.articles.update", this.modal.data.id),
            this.modal.data,
            {
              onSuccess: (page) => {
                this.modal.data = {};
                this.modal.isOpen = false;
                console.log(page);
              },
              onError: (error) => {
                console.log(error);
              },
            }
          );
        })
        .catch((err) => {
          console.log("error", err);
        });
    },
    deleteConfirmed(record) {
      this.$inertia.delete(route("admin.articles.destroy", record.id), {
        onSuccess: (page) => {
          console.log(page);
        },
        onError: (error) => {
          console.log(error);
        },
      });
    },
    deleteRecord(recordId) {
    if (!confirm('Are you sure want to remove?')) return;
    this.$inertia.delete(route('admin.articles.destroy', recordId), {
        onSuccess: (page) => {
            console.log(page);
        },
        onError: (error) => {
            console.log(error);
        }
    });
    },
    createLogin(recordId) {
      console.log("create login" + recordId);
    },
    uploader(editor) {
      editor.plugins.get("FileRepository").createUploadAdapter = (loader) => {
        return new UploadAdapter(loader);
      };
    },
    searchData() {
      this.$inertia.get(
        route("admin.articles.index"),
        { search: this.search, pagination: this.pagination },
        {
          onSuccess: (page) => {
            console.log(page);
          },
          //preserveState: true,
        }
      );
    },
    // onPaginationChange(page, filters, sorter) {
    //   this.$inertia.get(route("admin.articles.index"), {
    //     page: page,
    //     per_page: this.pagination.pageSize,
    //   });
    // },

    onPaginationChange(page, filters, sorter) {
      this.$inertia.get(route("admin.articles.index"),{
          page: page.current,
          per_page: page.pageSize,
        },{
          onSuccess: (page) => {
            console.log(page);
          },
          onError: (error) => {
            console.log(error);
          },
      });
    },

    reloadFormFields() {
      this.$inertia.reload({
        only: ["articles"],
        onSuccess: (page) => {
          this.isDraggable = false;
        },
      });
    },
    customRow(record, index){
      return {
        domProps:{
          draggable:this.isDraggable
        },
        style:{
          cursor: this.isDraggable?'move':'default'
        },
        // mouse move
        onMouseenter: event =>{
          if(this.isDraggable){
            var ev = event || window.event // for competible with IE
            ev.target.draggable = true
          }
        },
        // start drag
        onDragstart: event => {
          if(this.isDraggable){
            var ev = event || window.event
            ev.stopPropagation() // block popup
            this.sourceObj = record // get sourceObject with record id
          }
        },
        // drag crossing elements
        onDragover: event => {
          if(this.isDraggable){
            var ev = event || window.event
            ev.preventDefault()
          }
        },
        // mouse up
        onDrop: event => {
          if(this.isDraggable){
            var ev = event || window.event
            ev.stopPropagation()
            this.targetObj=record // get target Object
            // swap record position
            let sourceIndex = ''
            let targetIndex = ''
            this.dataModel.map((item,idx) => {
              if(this.sourceObj == item){
                sourceIndex = idx
              }
              if(this.targetObj == item){
                targetIndex = idx
              }
            })
            const sequences=[];
            this.dataModel.map((item,idx) => {
              sequences.push(item.sequence)
            })
            // show swap data
            let arr=[]
            this.dataModel.map((item,idx) => {
              if(sourceIndex == idx){
                arr.push(this.targetObj)
              }else if(targetIndex == idx){
                arr.push(this.sourceObj)
              }else{
                arr.push(item)
              }
            })
            arr.map((item,idx) => {
              arr[idx].sequence=sequences[idx]
            })
            this.dataModel=arr
            this.$inertia.post(route("admin.article.sequence"), this.dataModel, {
              onSuccess: (page) => {
                console.log(page);
              },
              onError: (error) => {
                console.log(error);
              },
            });
          }
        },
      }
    },
    getLable(categories, value){
      const item=categories.find(c=>c.value==value)
      if(item) return item.label
      return '--';
    } 

  },
};
</script>
