<template>
    <form @submit.prevent="create" :class="'post-form ' + type">

        <!-- Content -->
        <p class="ui-error"
            v-if="form.errors.has('content')"
            v-text="form.errors.get('content')">
        </p>

        <textarea maxlength="255" v-model="form.content"
            class="plain placeholder-dark" placeholder="Enter a message...">
        </textarea>

         <button v-if="includeButton"
             @click.prevent="create"
             type="submit">
             &#10003;
         </button>
    </form>
</template>

<script>
import Form from '../lib/Form.js';
import DatePicker from '../lib/DatePicker.js';

export default {

props: {
    id: { required: true },
    type: { required: true }
},

created() {
    bus.$on('submit', () => {
        return this.create();
    });
},

data() {
    return {
        form: new Form({ content: '' })
    };
},

computed: {
    includeButton() {
        return this.type === 'profile';
    }
},

methods: {

    isUpdatable() {
        return false;
    },

    create() {
        this.form.post(`/${ this.type }/${ this.id }/posts`)
        .then(data => {
             window.location = window.location.href;
        })
        .catch(errors => {});
    }
}

}
</script>
