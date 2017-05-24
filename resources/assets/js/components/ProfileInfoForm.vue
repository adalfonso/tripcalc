<template>
    <form id="tripForm" class="dialogue popup" @submit.prevent="onSubmit">
        <div class="popup-close" @click="hide">&times;</div>
        <h4 class="centered">Basic Info</h4>
        <hr>

        <p class="ui-error" v-if="form.errors.has('first_name')"
            v-text="form.errors.get('first_name')"></p>
        <input type="text" name="first_name" placeholder="*First Name" maxlength="50"
            v-model="form.first_name" required>

        <p class="ui-error" v-if="form.errors.has('last_name')"
            v-text="form.errors.get('last_name')"></p>
        <input type="text" name="last_name" placeholder="*Last Name" maxlength="50"
            v-model="form.last_name" required>

        <textarea type="text" name="about_me" placeholder="About Me" maxlength="500"
            v-model="form.about_me"></textarea>

        <button class="btn-full form-button" type="submit">Update Info</button>
    </form>
</template>

<script>
import Form from '../lib/Form.js';
import DatePicker from '../lib/DatePicker.js';

export default {

    data() {
        return {
            start_date: new DatePicker(),
            end_date: new DatePicker(),
            form: new Form({
               first_name : null,
               last_name : null
            })
        };
    },

    created() {
        this.form.get('/user')

        .then(data => {
            this.form = new Form({
                'first_name': data.first_name,
                'last_name': data.last_name
            });
        })

        .catch(errors => {});
    },

    methods: {

        hide() {
            this.$emit('hide');
        },

        onSubmit() {
            this.form.patch('/user')
            .then(data => { window.location = '/profile' })
            .catch(errors => {});
        }
    }
}
</script>
