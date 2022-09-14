<template>
<!--    <picture-input-->
<!--        ref="pictureInput"-->
<!--        :removable="true"-->
<!--        removeButtonClass="ui red button"-->
<!--        :height="500"-->
<!--        accept="image/jpeg, image/png, image/gif"-->
<!--        buttonClass="ui button primary"-->
<!--        size="1000"-->
<!--        :customStrings="-->
<!--        {upload: '<h1>Upload it!</h1>',-->
<!--        drag: 'Drag and drop your image here'}">-->
<!--    </picture-input>-->

    <div id="vue-cloudinary-uploader">
        <input type="hidden" :value="uploadedImageData.secureUrl">

        <button v-if="uploadedImageData.secureUrl"
                class="vcu-button button-danger"
                type="button"
                @click="deleteImageFromCloud()"
        >
            Change Image
        </button>
        <button id="uploader-button"
                class="vcu-button button-info"
                type="button"
                v-else @click.prevent="showModal()"
                :disabled="processingUpload || modelVisible"
        >
            Select Image File
        </button>

        <div id="modal-wrapper" v-show="modelVisible">
            <div class="image-cropper">
                <div class="editor">
                    <div class="input">
                        <div>
                            <input type="file" ref="photo"
                                   accept="image/*"
                                   @change="addLocalImage()"
                                   id="vcu-file-input">
                        </div>
                        <span v-if="fileError" class="text-red-400">{{ errorMessage }}</span>
                    </div>
                    <div v-if="showUploadProgress" class="vcu-progress-wrapper">
                        <div class="vcu-progress" :style="'width: ' + uploadProgress + '%'"></div>
                    </div>
                    <div class="img">
                        <img ref="working_image" id="image" :src="localFileDataUrl" alt="">
                    </div>
                </div>
                <div class="options">
                    <div>
                        <button class="vcu-button button-danger" type="button" @click="destroyUploaderInstance(true)" :disabled="processingUpload">CANCEL</button>
                    </div>
                    <div>
                        <button class="vcu-button button-info" type="button" @click="getImageUrl()" :disabled="processingUpload">CROP</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="image-foreach">
            <img v-for="image in images"
                 :src="image.title"
                 alt=""
                 class="w-[10%] h-[10%] m-2 object-fill"
            >
        </div>
    </div>

</template>

<script>
import axios from "axios";
import Cropper from "cropperjs";
import "cropperjs/dist/cropper.css";
export default {
    name: "CloudinaryVueUploader",
    data(){
        return {
            showFileSelector: true,
            showImageCropper: false,
            showUploadProgress: false,
            modelVisible: false,
            cropperInstance: null,
            uploadProgress: 0,
            processingUpload: false, // this will be true when image is being uploaded to prevent any other upload request
            localFileDataUrl: "",
            cloudinaryUploadUrl: "",
            cloudinaryDeleteUrl: "",
            uploadedImageData: {
                deleteToken: "",
                publicId: "",
                secureUrl: ""
            },
            fileError: false,
            errorMessage: ''
        };
    },
    props: {
        images: {
            type: Array,
            default: []
        }
    },
    methods: {
        showModal() {
            this.errorMessage = '';
            this.fileError = false;
            this.modelVisible = true;
        },
        hideModal() {
            this.modelVisible = false;
        },
        editImage() {
            this.showImageCropper = true;
            if(this.cropperInstance){
                this.cropperInstance.destroy();
                this.showImageCropper = false;
            }
            this.$nextTick(() => {
                this.cropperInstance = new Cropper(this.$refs.working_image, {
                    aspectRatio: this.aspectRatio,
                    viewMode: 2,
                    background: false,
                    ready() {
                        this.showImageCropper = true;
                    }
                });
            });
        },
        async addLocalImage() {
            if(this.$refs.photo.files.length < 1){
                console.log("No photo selected");
                return false;
            }
            let photo = this.$refs.photo.files[0];

            // if (photo.size > 0.5 * 1024 * 1024) {
            //     this.errorMessage = 'File size should be no more than 1 MB';
            //     this.fileError = true;
            //     return false;
            // }

            // if (photo.size > 0.5 * 1024 * 1024) {
            //     this.errorMessage = 'File size should be no more than 1 MB';
            //     this.fileError = true;
            //     return false;
            // }
            console.log(photo);
            this.localFileDataUrl = window.URL.createObjectURL(photo);
            await this.$nextTick(this.editImage());
        },
        async getImageUrl(){
            if(!this.cropperInstance){
                alert("Select Image File!");
                return false;
            }
            // if(!this.cropperInstance.getCroppedCanvas()){
            //     alert("No Image Detected!");
            //     return false;
            // }
            // if(this.processingUpload){ // don't initiate another upload while one is running
            //     alert("Previous upload not completed!");
            //     return false;
            // }
            let canvas = this.cropperInstance.getCroppedCanvas();
            await canvas.toBlob( (blob) => {
                let formData = new FormData();
                formData.append("file", blob);
                formData.append("upload_preset", this.cloudinaryUploadPreset);
                this.uploadImageToCloud(formData);
            })
        },
        destroyUploaderInstance(closeCropper = false){
            // // destroy cropper instance
            // if(this.cropperInstance && closeCropper){
            //     this.cropperInstance.destroy();
            // }
            // // set all other variables to their defaults
            this.cropperInstance = null;
            this.localFileDataUrl = "";
            this.processingUpload = false;
            this.showFileSelector = true;
            this.showImageCropper = false;
            this.showUploadProgress = false;
            this.uploadProgress = 0;
            document.getElementById("vcu-file-input").value = "";
            this.uploadedImageData = { deleteToken: "", publicId: "", secureUrl: "" };
            this.$emit("uploaderDestroyed", "" );
            if(closeCropper){
                this.hideModal();
            }
        },
        uploadImageToCloud(formData){
            this.showUploadProgress = true;
            this.processingUpload = true;
            this.uploadProgress = 0;
            axios.post('/save-crop-image', formData, {
                onUploadProgress: (progressEvent) => {
                    this.uploadProgress = progressEvent.lengthComputable ? Math.round( (progressEvent.loaded * 100) / progressEvent.total ) : 0 ;
                }
            })
                .then( (response) => {
                    this.images.push(response.data.image);
                    this.showUploadProgress = false;
                    this.processingUpload = false;
                    this.hideModal();
                })
                .catch((error) => {
                    console.log(error.response.data.message);
                    if(error.response){
                        console.log(error.message);
                    }else{
                        console.log(error);
                    }
                    this.showUploadProgress = false;
                    this.processingUpload = false;
                })
        },
        deleteImageFromCloud(){
            if(this.uploadedImageData.deleteToken === ""){ // if delete token is not provided
                console.log("uploadedImageData ", this.uploadedImageData);
                alert("No Delete token");
            }
            let formData = new FormData();
            formData.append("token", this.uploadedImageData.deleteToken);
            axios.post(this.cloudinaryDeleteUrl, formData)
                .then(response => {
                    if(this.cropperInstance){
                        this.cropperInstance.destroy();
                    }
                    this.destroyUploaderInstance()
                    this.showModal();
                    this.$emit("remoteImageDeleted");
                })
                .catch(error=>{
                    console.log(error);
                    return false;
                })
        }
    },
    // created() {
    //     console.log(this.images);
    // }
}
</script>

<style>
:root {
    --default-font-family: Arial, Helvetica, sans-serif;
    --default-font-weight-small: 300;
    --default-font-weight-medium: 600;
    --default-font-weight-heavy: 900;
    --default-space-x-small: 5px;
    --default-space-small: 10px;
    --default-space-medium: 15px;
    --default-space-large: 25px;

    --color-primary: rgb(233,233,239);
    --color-secondary: rgb(248,248,250);
    --color-tertiary: rgb(255,255,255);
    --color-danger: rgb(220, 20, 60);
    --color-danger-dark: rgb(200, 20, 60);
    --color-danger-light: rgb(240, 20, 60);
    --color-info: rgb(13, 125, 216);
    --color-info-dark: rgb(10, 94, 196);
    --color-info-light: rgb(10, 94, 236);
    --color-success: rgb(16, 190, 10);
    --color-success-dark: rgb(16, 170, 10);
    --color-success-light: rgb(16, 210, 10);
    --color-text-button: aliceblue;
}
* {
    font-family: var(--default-font-family);
}
#vue-cloudinary-uploader {
}
.vcu-progress-wrapper {
    height: 30px;
    width: 100%;
    padding: 0;
}
.vcu-progress {
    margin: 0;
    height: inherit;
    background: var(--color-success)
}
/*button styles*/
.vcu-button {
    position: relative;
    background: var(--color-primary);
    border: none;
    border-radius: 2px;
    color: var(--color-text-button);
    font-weight: 500;
    padding: var(--default-space-small);
    margin-left: 5px;
    cursor: pointer;
}

.vcu-button:disabled {
    background: var(--color-primary) !important;
    color: black !important;
}
.close-button {
    position: absolute;
    top: 0; right: 0;
    margin: var(--default-space-x-small);
    padding: var(--default-space-small);
    font-weight: var(--default-font-weight-medium);
    cursor: pointer;
    z-index: 5;
}

.button-danger {
    background-color: var(--color-danger);
}

.button-danger:hover {
    background-color: var(--color-danger-light);
}

.button-danger:active {
    background-color: var(--color-danger-dark);
}

.button-info {
    background-color: var(--color-info);
}

.button-info:hover {
    background-color: var(--color-info-light);
}

.button-info:active {
    background-color: var(--color-info-dark);
}
#modal-wrapper {
    width: 70%;
    height: 80%;
    position: fixed;
    top: 10px; bottom: 10px;
    left: 100px; right: 100px;
    border: 1px solid var(--color-primary);
    margin: var(--default-space-small);
    z-index: 99999;
    display: flex;
    box-shadow: 0 0 5px 0 #b1b0b0;
}
.image-cropper {
    flex-grow: 1;
    /* padding: var(--default-space-small); */
    display: flex;
    flex-direction: column;
    background-color: var(--color-tertiary);
    max-height: inherit;
    max-width: inherit;
}
.image-cropper > .editor {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    background-color: var(--color-tertiary);
    padding: var(--default-space-small);
    max-height: inherit;
    max-width: inherit;
    overflow: hidden;
}

.image-cropper > .editor > .input {
    height: 50px;
    display: flex;
    flex-direction: row;
    align-items: flex-start;
}

.image-cropper > .editor > .input {
    height: 50px;
    display: flex;
    flex-direction: row;
    justify-content: center;
}

.image-cropper > .editor > div > .input > input, .image-cropper > .editor > .input > button {
    min-height: 20px;
    font-size: 16px;
    margin-left: var(--default-space-small);
}

input[type="file"] {
    position: relative !important;
    top: 1% !important;
    z-index: 1 !important;
    width: initial !important;
    height: initial !important;
    -webkit-appearance: initial !important;
    opacity: 1 !important;
    cursor: pointer !important;
}
.image-cropper > .editor > .img {
    position: relative;
    padding: var(--default-space-small);
    flex-grow: 1;
    background: var(--color-tertiary);
    min-height: 20px;
    margin-bottom: 20px;
}
.image-cropper > .options {
    display: flex;
    flex-direction: row;
    justify-items: center;
    justify-content: flex-end;
    height: 50px;
    background-color: var(--color-secondary);
    padding: var(--default-space-x-small);
    border-top: 1px solid var(--color-primary);
}
.image-cropper > .options > div {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100px;
    padding: var(--default-space-small);
    margin-left: var(--default-space-x-small);
    cursor: pointer;
    font-weight: var(--default-font-weight-small);
}

@media screen and (orientation: portrait) {
    #modal-wrapper {
        left: 20px; right: 20px;
    }
    .image-cropper > .options {
        height: 10vmin;
    }
}
img {
    max-width: 100%;
}
/*model styes*/
.show {
    display: flex !important
}

.hide {
    display: none !important
}

.image-foreach {
    display: flex;
    width: 100%;
}
</style>
