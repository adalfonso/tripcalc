<style>
    @media (max-width: 768px) {
        .post-form { margin-top: 1.5rem }
    }
</style>

<template>
    <form @submit.prevent="create" class="post-form">

        <!-- Content -->
        <p class="ui-error" v-if="form.errors.has('content')" v-text="form.errors.get('content')"></p>
        <textarea maxlength="255" v-model="form.content"
            class="plain placeholder-dark" placeholder="Enter a message...">
        </textarea>

    </form>
</template>

<script>
import Form from '../lib/Form.js';
import DatePicker from '../lib/DatePicker.js';

export default {

props: {
    trip_id: { default: null }
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

methods: {

    isUpdatable() {
        return false;
    },

    create() {
        this.form.post(`/trip/${ this.trip_id }/posts`)
        .then(data => { window.location = '/trip/' + this.trip_id })
        .catch(errors => {});
    }
}

}
</script>
