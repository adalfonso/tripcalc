<template>
    <div>
        <photo-upload-form v-if="photoUploadForm.visible" @hide="hideAll" :id="id">
        </photo-upload-form>

        <div class="photo-container clearfix" :ref="'photoContainer'" v-if="path">

            <img class="photo-thumb" :ref="'photoThumb'" :src="path" />

            <div class="photo-new" v-if="uploadable" @click="togglePhotoUpload">
                Upload a new photo
            </div>

        </div>

        <a @click="togglePhotoUpload" v-if="uploadable && path === '' ">
            Upload a photo
        </a>
    </div>
</template>

<script>
    export default {

        props: {
            id: { required: true },
            path: { default: null },
            uploadable: { default: 0 }
        },

        data() {
            return {
                photoUploadForm: { visible: false }
            };
        },

        methods: {

            hideAll() {
                this.photoUploadForm.visible = false;
            },

            togglePhotoUpload() {
                this.photoUploadForm.visible =  !this.photoUploadForm.visible;
            }
        }
    }
</script>
