<template>
    <form  @submit.prevent="onSubmit">
        <h4 class="margin-top">Recent Activity</h4>

        <!-- Content -->
        <textarea name="description" type="text" maxlength="255"
            placeholder="Enter a message..." v-model="form.content">
        </textarea>

        <button class="btn form-button" type="submit">Post</button>
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

created() {
    // if (!this.isUpdatable()) {
    //     return;
    // }
    //
    // this.form.get(`/trips/${ this.trip_id }/data`)
    // .then(data => {
    //     this.form = new Form({
    //         name: data.name,
    //         start_date: '', end_date: '',
    //         budget: data.budget,
    //         description: data.description,
    //         delete: false, delete_confirmation: false,
    //         password: null
    //     });
    //
    //     this.start_date.parse(data.start_date);
    //     this.end_date.parse(data.end_date);
    //
    // }).catch(errors => {});
},

computed: {
},

methods: {

    isUpdatable() {
        return false;
    },

    create() {
        this.form.post(`/trips/${ this.trip_id }/posts`)
        .then(data => {
            window.location = '/trips/' + this.trip_id
        })
        .catch(errors => {});
    },

    update() {
        this.form.post(`/trips/${ this.trip_id }`)
        .then(data => { window.location = '/trips/' + data.id })
        .catch(errors => {});
    },

    onSubmit() {
        return this.isUpdatable()
            ? this.update()
            : this.create();
    }
}

}
</script>
