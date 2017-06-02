<template>

<div class="popup-wrap" @click.self="hide">
    <div class="popup">
        <div class="popup-close" @click="hide">&times;</div>
        <h4 class="centered">Upload a photo</h4>
        <form method="POST" :ref="'submit'" :action="endpoint" enctype="multipart/form-data">
            <div class="ui-file-upload clearfix">

                <input type="hidden" name="_token" :value="token.content">
                <input type="file" name="photo" id="photo" @change="updatePath" hidden>

                <div class="input-container" @click="openFileSelect">
                    <p>{{ path }}</p>
                </div>

                <button class="btn-upload" @click.prevent="submit" type="submit">
                    Upload
                 </button>
            </div>
        </form>
    </div>
</div>

</template>

<script>

export default {

props: {
    id: { required: true }
},

data() {
    return {
        endpoint: `/user/${this.id}/photos/upload`,
        path: 'Browse',
        token: document.querySelector('[name="csrf-token"]')
    };
},

methods: {

    hide() {
        this.$emit('hide');
    },

    openFileSelect() {
        document.querySelector('#photo').click();
    },

    updatePath() {
        let fullPath = document.querySelector('#photo').value;

        if (fullPath === '') {
            return this.path = 'Browse';
        }

        this.path = fullPath.match(/^.*\\(.*\.\w{1,5})$/)[1];
    },

    submit() {
        if (this.path === 'Browse') {
            return;
        }

        this.$refs.submit.submit();
    }
}

}
</script>
