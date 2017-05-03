<template>
    <form id="transactionForm" class="dialogue popup" @submit.prevent="onSubmit">
        <h4 class="centered form-header">Transaction</h4>
        <hr>
        <img src="/img/icon/closePopup.png" class="closePopup"
            @click="$emit('close')">

        <div v-if="isUpdatable()">
            <p><strong>Paid by:</strong> {{ creator }}</p>
            <hr>
        </div>

        <p><strong>Basic Info:</strong></p>

        <!-- Date -->
        <date-picker v-if="date.visible" :date="date"></date-picker>
        <p class="ui-error" v-text="form.errors.get('date')"></p>
        <div class="ui-input-btn">
            <img src="/img/icon/calendar-white-256x256.png" @click="date.show()">
        </div>
        <input class="hasBtn "type="text" placeholder="*Transaction Date"
            maxlength="50" v-model="datePretty" required>

        <!-- Amount -->
        <p class="ui-error" v-text="form.errors.get('amount')"></p>
        <div class="ui-input-btn">$</div>
        <input type="text" class="hasBtn" placeholder="*Transaction Amount" maxlength="50"
            v-model="form.amount" required>

        <!-- Description -->
        <textarea type="text" placeholder="Description" maxlength="500"
            v-model="form.description">
        </textarea>

        <!-- Hashtags -->
        <div class="ui-input-btn" @click="form.hashtags.add()">
            <span class="font-large">+</span>
        </div>
        <input class="hasBtn" type="text" placeholder="#hashtags" maxlength="25"
            v-model="form.hashtags.input" @keyup.enter.prevent="form.hashtags.add()">
        <div class="item-wrapper clearfix" v-if="form.hashtags.items.length > 0">
            <div class="item" v-for="hashtag in form.hashtags.items"
                @click="form.hashtags.remove(hashtag)">
                #{{ hashtag }}
            </div>
        </div>

        <!-- Split -->
        <hr class="marginless">
        <p><strong>How to Split:</strong></p>
        <div class="toggleGroup clearfix">
            <div :active="form.split.is('even')" @click="form.split.even()">
                Evenly
            </div>
            <div :active="form.split.is('personal')" @click="form.split.personal()">
                Personal
            </div>
            <div :active="form.split.is('custom')" @click="form.split.custom()">
                Custom
            </div>
        </div>

        <!-- Travelers - Custom Split -->
        <div class="travelers">
            <div v-show="form.split.type === 'custom'"
                v-for="(traveler, index) in form.split.travelers">
                <p class="ui-error"
                    v-if="form.errors.has('travelers.' + index + '.split_ratio')">
                    *Invalid Split Ratio for {{ traveler.full_name }}
                </p>
                <div class="ui-checkbox">
                <label>
                    <input type="checkbox" v-model="traveler.is_spender">
                    {{ traveler.full_name }}
                    <div class="splitRatio">
                        <input type="text" placeholder="Split Ratio" maxlength="5"
                            v-model="traveler.split_ratio">
                    </div>
                </label>
                </div>
            </div>
        </div>

        <!-- Delete this transaction -->
        <hr v-if="transaction_id">
        <div class="ui-checkbox" v-if="transaction_id">
            <!-- Fake fields to stop browser from trying to save password -->
            <input style="display:none" type="text" name="userFix">
            <input style="display:none" type="password" name="passwordFix">

            <label id="delete">
                <input type="checkbox" name="delete" v-model="form.delete"
                    @click="form.setPasswordNull()">
                Delete this transaction
            </label>
        </div>
        <div class="ui-checkbox" v-if="form.delete">
            <label id="deleteConfirmation">
                <input type="checkbox" name="deleteConfirmation"
                    v-model="form.delete_confirmation">
                Are you really sure? There is no going back!
            </label>
        </div>
        <p class="ui-error" v-if="form.errors.has('password') && form.delete &&
            form.delete_confirmation" v-text="form.errors.get('password')">
        </p>
        <input type="password" name="deletePassword" v-if="form.delete &&
            form.delete_confirmation" v-model="form.password"
            placeholder="Enter password to continue">

        <button class="btn-full form-button" type="button" @click="onSubmit">
            Submit Transaction
        </button>
    </form>
</template>

<script>
import Form from '../lib/Form.js';
import Hashtags from '../lib/Hashtags.js';
import Split from '../lib/Split.js';
import DatePicker from '../lib/DatePicker.js';

export default {

props: {
    edit: {
        type: Boolean,
        default: false
    },
    trip_id: { default: null },
    transaction_id: { default: null }
},

data() {
    return {
        creator: null,
        date: new DatePicker(),
        user: '',
        form: new Form({
            date: '',
            amount: '',
            description: '',
            split:  new Split(),
            delete: false, delete_confirmation: false,
            password: null,
            hashtags: new Hashtags()
        }),
    };
},

created() {
    if (this.isUpdatable()) {
       return this.getTransactionData();
    }

    this.getTravelers();
},

computed: {

    datePretty: {
        get() { return this.date.pretty(); },

        set(input) {
            return this.date.parse(input);
        }
    }
},

watch: {
    datePretty(){
        this.form.date = this.date.lessPretty();
    }
},

methods: {

    getTravelers() {
        axios.get(`/trips/${ this.trip_id }/travelers`)
        .then(response => {
            this.form.split = new Split(
                response.data.travelers,
                response.data.user
            );
        });
    },

    getTransactionData() {
        axios.get(`
            /trips/${ this.trip_id }/transactions/${ this.transaction_id }
        `)
        .then(response => {
            let transaction = response.data.transaction;

            this.form = new Form({
                date: '',
                amount: transaction.amount,
                description: transaction.description,
                delete: false, delete_confirmation: false,
                password: null,
                split: new Split(response.data.travelers, response.data.user),
                hashtags: new Hashtags(response.data.hashtags)
            });

            this.creator = response.data.creator;
            this.form.split.interpret();
            this.date.parse(transaction.date, this.date);
        });
    },


    isUpdatable() {
        return this.transaction_id !== null;
    },

    create() {
        this.form.post(`/trips/${ this.trip_id }/transactions`
        )
        .then(data => {
            location.reload();
        }).catch(errors => {});
    },

    update(){
        this.form.post(`/trips/${ this.trip_id }/transactions/${ this.transaction_id }`
        )
        .then(data => {
            location.reload();
        }).catch(errors => {});
    },

    delete(){
        this.form.post(`/trips/${ this.trip_id }/transactions/${ this.transaction_id }/delete`)
        .then(data => {
            if (data.success === true ) {
                window.location = '/trips/' + this.trip_id;
            }
        }).catch(errors => {});
    },

    onSubmit() {
        if (this.form.isDeletable()) {
            return this.delete();

        } else if (this.isUpdatable()) {
            return this.update();
        }

        return this.create();
    }
}

}
</script>
