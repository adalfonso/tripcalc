<template>
<div class="popup-wrap" @click.self="hide">
<form id="transactionForm" class="popup" @submit.prevent="onSubmit">
    <div class="popup-close" @click="hide">&times;</div>
    <h4 class="centered">Transaction</h4>
    <hr>

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
    <input class="hasBtn" type="text" placeholder="*Transaction Date"
        maxlength="50" v-model="datePretty" required>

    <!-- Amount -->
    <p class="ui-error" v-text="form.errors.get('amount')"></p>
    <div class="ui-input-btn">$</div>
    <input type="number" class="hasBtn" placeholder="*Transaction Amount" maxlength="50"
        v-model="form.amount" required>

    <!-- Description -->
    <input type="text" placeholder="Description" maxlength="50"
        v-model="form.description">

    <!-- Hashtags -->
    <div class="ui-input-btn" @click="form.hashtags.add()">
        <span class="font-large">+</span>
    </div>
    <input class="hasBtn" type="text" placeholder="#hashtags" maxlength="25"
        v-model="form.hashtags.input" @keyup.enter.prevent="form.hashtags.add()">
    <div class="clearfix" v-if="form.hashtags.items.length > 0">
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
    <div style="margin: .6rem 0">
        <div v-show="form.split.type === 'custom'"
            v-for="(traveler, index) in form.split.travelers">
            <p class="ui-error"
                v-if="form.errors.has('travelers.' + index + '.split_ratio')">
                *Invalid Split Ratio for {{ traveler.full_name }}
            </p>
            <div class="ui-checkbox" @click="form.split.toggle(traveler.id)">
                <div class="ui-input-btn no-hover"
                    v-html="traveler.is_spender ? '&#10004;' : '' "></div>
                <p>{{ traveler.full_name }}</p>
                <input type="text" placeholder="Split Ratio" maxlength="5"
                    class="splitRatio" v-model="traveler.split_ratio"
                    @click.stop="selectTraveler(traveler.id)">
            </div>
        </div>
    </div>

    <hr>

    <!-- Delete This Transaction -->
    <div class="ui-checkbox" v-if="transaction_id" @click="form.toggle('deletable')">
        <!-- Fake fields to stop browser from trying to save password -->
        <input style="display:none" type="text">
        <input style="display:none" type="password">
        <div class="ui-input-btn no-hover" style="font-size: 2rem"
            v-html="form.deletable ? '&#9760;' : '' "></div>
        <p>Delete this transaction</p>
    </div>

    <hr v-if="transaction_id">

    <!-- Password To Delete -->
    <input type="password" name="deletePassword" v-if="form.isDeletable()" v-model="form.password"
        placeholder="Enter password to continue">

    <div class="btn btn-full" @click.prevent="onSubmit">Submit Transaction</div>
</form>
</div>

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
    trip_id: { required: true },
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
            deletable: false,
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

    // Auto set date when new transaction
    let now = this.date.date;
    this.date.set(now.getFullYear(), now.getMonth(), now.getDate());
},

computed: {

    datePretty: {
        get() { return this.date.pretty(); },

        set(input) {
            return this.date.parse(input);
        }
    },

    rememberIcon() {
        return '&#10004;';
    }
},

watch: {
    datePretty(){
        this.form.date = this.date.lessPretty();
    }
},

methods: {

    getTravelers() {
        axios.get(`/trip/${ this.trip_id }/travelers`)
        .then(response => {
            this.form.split = new Split(
                response.data.travelers,
                response.data.user
            );
        });
    },

    getTransactionData() {
        axios.get(`/trip/${this.trip_id}/transaction/${this.transaction_id}`)
        .then(response => {
            let transaction = response.data.transaction;

            this.form = new Form({
                date: '',
                amount: transaction.amount,
                description: transaction.description,
                deletable: false,
                password: null,
                split: new Split(response.data.travelers, response.data.user),
                hashtags: new Hashtags(response.data.hashtags)
            });

            this.creator = response.data.creator;
            this.form.split.interpret();
            this.date.parse(transaction.date);
        });
    },

    hide() {
        this.$emit('hide');
    },

    isUpdatable() {
        return this.transaction_id !== null;
    },

    selectTraveler(traveler) {
        this.form.split.travelers[traveler].is_spender = 1;
    },

    create() {
        this.form.post(`/trip/${ this.trip_id }/transactions`)
        .then(data => {
            location.reload();
        }).catch(errors => {});
    },

    update(){
        this.form.patch(`/trip/${ this.trip_id }/transaction/${ this.transaction_id }`)
        .then(data => {
            location.reload();
        }).catch(errors => {});
    },

    delete() {
        this.form.delete(`/trip/${ this.trip_id }/transaction/${ this.transaction_id }`)
        .then(data => {
            if (data.success === true ) {
                window.location = '/trip/' + this.trip_id;
            }
        }).catch(errors => {});
    },

    onSubmit() {
        if (this.form.hashtags.input) {
            this.form.hashtags.add();
        }

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
