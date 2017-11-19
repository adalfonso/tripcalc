<template>
    <form @submit.prevent="submit" :class="'post-form ' + type">

        <!-- Content -->
        <p class="ui-error"
            v-if="form.errors.has('content')"
            v-text="form.errors.get('content')">
        </p>

        <textarea maxlength="255"
            class="plain placeholder-dark"
            placeholder="Enter a message..."
            v-model="form.content"
            @keyup.enter="submit"
            @keyup="form.errors.clear()">
        </textarea>

         <button v-if="includeButton"
             @click.prevent="submit"
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
        return this.submit();
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

    submit($event) {
        if (event.shiftKey) {
            return;    
        }

        this.form.post(`/${ this.type }/${ this.id }/posts`)
        .then(data => {
            window.location = window.location.href;
        })
        .catch(errors => {});
    }
}

}
</script>
