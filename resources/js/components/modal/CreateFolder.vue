<template>
  <div class="form-horizontal">
    <form-group
      :config="data"
      :k="'foldername'"
      :info="createFolderInfo"
      :status="data.status"
    ></form-group>
    <form-group :config="data" :k="'select'" :info="createFolderInfo">
      <div v-if="!data.folders">
        <div class="loading"></div>
        <div class="text-gray text-center">正在加载，客官莫急。</div>
      </div>
      <template v-else>
        <hr />
        <only-folder-item
          v-for="item in data.folders"
          :key="item.id"
          :info="item"
        />
      </template>
    </form-group>
  </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
import OnlyFolderItem from "../OnlyFolderItem";
import FormGroup from "../FormGroup";
import configInfo from "../../utils/configInfo";
export default {
  name: "create-folder",
  components: {
    "only-folder-item": OnlyFolderItem,
    "form-group": FormGroup
  },
  data() {
    return {
      createFolderInfo: configInfo.createFolder
    };
  },
  computed: {
    ...mapState({
      data: state => state.tools.miModal.data,
      modal: state => state.tools.miModal
    })
  },
  methods: {
    ...mapActions("tools", ["setMiModalData", "hideMiModal"]),
    ...mapActions("toast", ["timeToast", "showLoadToast", "hideLoadToast"]),
    ...mapActions("note", ["folderOperate", "listOperate", "loadCloudFolders"])
  },
  created() {
    this.modal.title = "新建文件夹";
    let wTimeout = null;
    let watch = () => {
      if (wTimeout) {
        clearTimeout(wTimeout);
      }
      wTimeout = setTimeout(() => {
        this.setMiModalData({
          ...this.data,
          status: "loading"
        });
        this.folderOperate({
          operate: "exist",
          folderInfo: {
            path: this.data.select + "/" + this.data.foldername
          }
        }).then(data => {
          if (data.exist) {
            this.setMiModalData({
              ...this.data,
              status: "error"
            });
          } else {
            this.setMiModalData({
              ...this.data,
              status: ""
            });
          }
        });
      }, 500);
    };
    let uwFolderName = this.$watch("data.foldername", watch);
    let uwTitle = this.$watch("data.select", watch);
    this.modal.confirm = {
      content: "新建",
      handler: () => {
        if (!this.data.foldername || this.data.status !== "") {
          return;
        }
        this.modal.confirm.loading = true;
        let path = this.data.select + "/" + this.data.foldername;
        this.folderOperate({
          operate: "create",
          folderInfo: {
            path: path
          }
        })
          .then(() => {
            this.modal.confirm.loading = false;
            this.modal.cancel.handler();
            this.showLoadToast({ message: "重新读取文件夹列表中..." });
            this.loadCloudFolders()
              .then(() => {
                this.hideLoadToast();
              })
              .catch(err => {
                this.hideLoadToast();
              });
            this.timeToast({
              message: "创建文件夹成功！",
              status: "success",
              delay: 1000
            });
          })
          .catch(err => {
            this.modal.confirm.loading = false;
            this.timeToast({
              message:
                "创建文件夹失败！请重试。(" + err.response.data.error + ")",
              status: "error",
              delay: 1000
            });
          });
      }
    };
    this.modal.cancel = {
      handler: () => {
        uwFolderName();
        uwTitle();
        this.hideMiModal();
      }
    };
    this.folderOperate({ operate: "readOnly", folderInfo: null }).then(data => {
      this.setMiModalData({
        ...this.data,
        folders: data.folders
      });
    });
  }
};
</script>
