<template>
    <form id="tripForm" class="dialogue popup" @submit.prevent="onSubmit">

        <h4 class="centered form-header">Trip</h4>
        <hr>
        <img src="/img/icon/closePopup.png" id="closeTripForm" class="closePopup"
            @click="$emit('close')">

        <p class="ui-error" v-if="form.errors.has('name')" v-text="form.errors.get('name')"></p>
        <input type="text" name="name" placeholder="*Trip Name" maxlength="50"
            v-model="form.name" required>

        <date-picker v-if="start_date.visible" :date="start_date"></date-picker>
        <p class="ui-error" v-text="form.errors.get('start_date')"></p>
        <div class="ui-input-btn">
            <img src="/img/icon/calendar-white-256x256.png" @click="start_date.show()">
        </div>
        <input type="text" class="hasBtn" name="start_date" placeholder="*Start Date" maxlength="50"
            v-model="startDatePretty" required>

        <date-picker v-if="end_date.visible" :date="end_date"></date-picker>
        <p class="ui-error" v-text="form.errors.get('end_date')"></p>
        <div class="ui-input-btn">
            <img src="/img/icon/calendar-white-256x256.png" @click="end_date.show()">
        </div>
        <input type="text" class="hasBtn" name="end_date" placeholder="*End Date" maxlength="50"
            v-model="endDatePretty" @blur="" required>

        <p class="ui-error" v-text="form.errors.get('budget')"></p>
        <div class="ui-input-btn">$</div>
        <input type="number" class="hasBtn" min="0" name="budget" placeholder="Budget"
            v-model="form.budget">

        <textarea type="text" name="description" placeholder="Description" maxlength="500"
            v-model="form.description">
        </textarea>

        <div class="ui-checkbox" v-if="trip_id">

            <!-- Fake fields to stop browser from trying to save password -->
            <input style="display:none" type="text" name="userFix">
            <input style="display:none" type="password" name="passwordFix">
            
            <label id="delete">
                <input type="checkbox" name="delete" v-model="form.delete"
                @click="setPasswordNull">
                Delete this trip
            </label>
        </div>

        <div class="ui-checkbox" v-if="form.delete">
            <label id="deleteConfirmation">
                <input type="checkbox" name="deleteConfirmation" v-model="form.delete_confirmation">
                Are you sure? There is no going back!
            </label>
        </div>

        <p class="ui-error" v-if="form.errors.has('password') && form.delete &&
            form.delete_confirmation" v-text="form.errors.get('password')"></p>
        <input type="password" name="deleteTripPassword" v-if="form.delete &&
            form.delete_confirmation" v-model="form.password"
            placeholder="Enter password to continue">

        <button class="btn-full form-button" type="submit">Submit Trip</button>
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
        start_date: new DatePicker(),
        end_date: new DatePicker(),
        form: new Form({
            name: '',
            budget: '',
            start_date: '', end_date: '',
            description: '',
            delete: false, delete_confirmation: false,
            password: null
        })
    };
},

created() {
    if (this.isUpdatable()) {
        this.form.get(`/trips/${ this.trip_id }/data`)
        .then(data => {
            this.form = new Form({
                name: data.name,
                start_date: '', end_date: '',
                budget: data.budget,
                description: data.description,
                delete: false, delete_confirmation: false,
                password: null
            })
            this.setDate(data.start_date, this.start_date);
            this.setDate(data.end_date, this.end_date);
        }).catch(errors => {});
    }
},

computed: {

    startDatePretty: {
        get() { return this.start_date.pretty(); },

        set(input) {
            return this.setDate(input, this.start_date);
        }
    },

    endDatePretty: {
        get() { return this.end_date.pretty(); },

        set(input) {
            return this.setDate(input, this.end_date);
        }
    }
},

// Watchers to update form start and end date
watch: {

    startDatePretty(){
        this.form.start_date = this.start_date.lessPretty();
    },

    endDatePretty(){
        this.form.end_date = this.end_date.lessPretty();
    }
},

methods: {

    setDate(input, reference) {
        return reference.parse(input);
    },

    setPasswordNull() {
        this.form.password = null;
        this.form.delete_confirmation = false;
    },

    isUpdatable() { return this.trip_id !== null; },

    create() {
        this.form.post('/trips')
        .then(data => {
            window.location = '/trips/' + data.id
        })
        .catch(errors => {});
    },

    update() {
        this.form.post(`/trips/${ this.trip_id }`)
        .then(data => { window.location = '/trips/' + data.id })
        .catch(errors => {});
    },

    delete() {
        this.form.post(`/trips/${ this.trip_id }/delete`)
        .then(data => {
            if (data.success === true ) {
                window.location = '/trips/dashboard';
            }
        }).catch(errors => {});
    },

    onSubmit() {
        if (this.form.isDeletable()) {
            return this.delete();

        } else if (this.isUpdatable())  {
            return this.update();
        }

        return this.create();
    }
}

}
</script>
