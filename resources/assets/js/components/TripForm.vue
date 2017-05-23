<template>
    <div class="popup-wrap" @click.self="hide">
    <form id="tripForm" class="popup" @submit.prevent="onSubmit">
        <div class="popup-close" @click="hide">&times;</div>
        <h4 class="centered">Trip</h4>
        <hr>

        <!-- Trip Name -->
        <p class="ui-error" v-if="form.errors.has('name')" v-text="form.errors.get('name')"></p>
        <input type="text" name="name" placeholder="*Trip Name" maxlength="50"
            v-model="form.name" required>

        <!-- Start Date -->
        <date-picker v-if="start_date.visible" :date="start_date"></date-picker>
        <p class="ui-error" v-text="form.errors.get('start_date')"></p>
        <div class="ui-input-btn">
            <img src="/img/icon/calendar-white-256x256.png" @click="start_date.show()">
        </div>
        <input type="text" class="hasBtn" name="start_date" placeholder="*Start Date" maxlength="50"
            v-model="startDatePretty" required>

        <!-- End Date -->
        <date-picker v-if="end_date.visible" :date="end_date"></date-picker>
        <p class="ui-error" v-text="form.errors.get('end_date')"></p>
        <div class="ui-input-btn">
            <img src="/img/icon/calendar-white-256x256.png" @click="end_date.show()">
        </div>
        <input type="text" class="hasBtn" name="end_date" placeholder="*End Date" maxlength="50"
            v-model="endDatePretty" @blur="" required>

        <!-- Budget -->
        <p class="ui-error" v-text="form.errors.get('budget')"></p>
        <div class="ui-input-btn">$</div>
        <input type="number" class="hasBtn" min="0" name="budget" placeholder="Budget"
            v-model="form.budget">

        <!-- Description -->
        <textarea type="text" name="description" placeholder="Description" maxlength="500"
            v-model="form.description">
        </textarea>

        <!-- Delete this trip -->
        <div class="ui-checkbox" v-if="trip_id" @click="form.toggle('deletable')">
            <!-- Fake fields to stop browser from trying to save password -->
            <input style="display:none" type="text">
            <input style="display:none" type="password">
            <div class="ui-input-btn no-hover" style="font-size: 2rem"
                v-html="form.deletable ? '&#9760;' : '' "></div>
            <p>Delete this trip</p>
        </div>

        <!-- Password To Delete -->
        <div v-if="form.isDeletable()">
        <p class="ui-error" v-if="form.errors.has('password')"
            v-text="form.errors.get('password')"></p>
        <input type="password" name="deleteTripPassword" v-model="form.password"
            placeholder="Enter password to continue">
        </div>

        <button class="btn-full form-button" type="submit">Submit Trip</button>
    </form>
    </div>
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
            deletable: false,
            password: null
        })
    };
},

created() {
    if (!this.isUpdatable()) {
        return;
    }

    this.form.get(`/trip/${ this.trip_id }/data`)
    .then(data => {
        this.form = new Form({
            name: data.name,
            start_date: '', end_date: '',
            budget: data.budget,
            description: data.description,
            deletable: false,
            password: null
        });

        this.start_date.parse(data.start_date);
        this.end_date.parse(data.end_date);

    }).catch(errors => {});
},

computed: {

    startDatePretty: {
        get() { return this.start_date.pretty(); },

        set(input) {
            return this.start_date.parse(input);
        }
    },

    endDatePretty: {
        get() { return this.end_date.pretty(); },

        set(input) {
            return this.end_date.parse(input);
        }
    }
},

watch: {

    startDatePretty(){
        this.form.start_date = this.start_date.lessPretty();
    },

    endDatePretty(){
        this.form.end_date = this.end_date.lessPretty();
    }
},

methods: {

    hide() {
        this.$emit('hide');
    },

    isUpdatable() { return this.trip_id !== null; },

    create() {
        this.form.post('/trips')
        .then(data => {
            window.location = '/trip/' + data.id
        })
        .catch(errors => {});
    },

    update() {
        this.form.patch(`/trip/${ this.trip_id }`)
        .then(data => { window.location = '/trip/' + data.id })
        .catch(errors => {});
    },

    delete() {
        this.form.delete(`/trip/${ this.trip_id }`)
        .then(data => {
            if (data.success === true ) {
                window.location = '/trips';
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
