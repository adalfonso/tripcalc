<template>
    <form  @submit.prevent="create">

        <!-- Content -->
        <textarea name="description" type="text" maxlength="255" style="border-radius: 0"
            placeholder="Enter a message..." v-model="form.content">
        </textarea>

        <button class="btn form-button" type="submit" style="float: right">Post</button>
    </form>
</template>

<script>
import Form from '../lib/Form.js';
import DatePicker from '../lib/DatePicker.js';

export default {

props: {
    trip_id: { default: null }
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
