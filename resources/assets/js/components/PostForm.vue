<style>
    .post-form textarea {
        background: transparent;
        color: #495a69;
        box-shadow: none;
        border: 1px solid #495a69;
        opacity: 1;
    }

    @media (max-width: 768px) {
        .post-form { margin-top: 1.5rem }
    }
</style>

<template>
    <form @submit.prevent="create" class="post-form">

        <!-- Content -->
        <textarea name="description" type="text" maxlength="255" class="placeholder-dark"
            placeholder="Enter a message..." v-model="form.content">
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
        this.form.post(`/trips/${ this.trip_id }/posts`)
        .then(data => { window.location = '/trips/' + this.trip_id })
        .catch(errors => {});
    }
}

}
</script>
