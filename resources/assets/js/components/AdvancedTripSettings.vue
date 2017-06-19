<template>
    <div class="popup-wrap" @click.self="hide">
    <form id="tripForm" class="popup" @submit.prevent="onSubmit">
        <div class="popup-close" @click="hide">&times;</div>
        <h4 class="centered">Advanced Settings</h4>
        <hr>

        <!-- Private transactions -->
        <div class="ui-checkbox" @click="form.toggle('private_transactions')">
            <input type="hidden" v-model="form.private_transactions">
            <div class="ui-input-btn no-hover"
                v-html="form.private_transactions ? '&#10004;' : '' "></div>
            <p>Hide personal transactions from global feed</p>
        </div>

        <!-- Private transactions -->
        <div class="ui-checkbox" @click="form.toggle('placeholderUsers')">
            <input type="hidden" v-model="form.placeholderUsers">
            <div class="ui-input-btn no-hover"
                v-html="form.placeholderUsers ? '&#10004;' : '' "></div>
            <p>Enable placeholder users</p>
        </div>

        <!-- Private transactions -->
        <div class="ui-checkbox" @click="form.toggle('easyTransactions')">
            <input type="hidden" v-model="form.easyTransactions">
            <div class="ui-input-btn no-hover"
                v-html="form.easyTransactions ? '&#10004;' : '' "></div>
            <p>Enable easy transactions</p>
        </div>
        <button class="btn-full form-button" type="submit">Apply</button>
    </form>
    </div>
</template>

<script>

export default {

props: {
    trip_id: { default: null }
},

data() {
    return {
        form: new Form ({
            private_transactions: false,
            editable_transactions: false,
            placeholderUsers: false,
            easyTransactions: false
        })
    };
},

created() {
    axios.get(`/trip/${this.trip_id}/advancedSettings`)
    .then(response => {
        this.form.private_transactions = response.data.private_transactions;
        this.form.editable_transactions = response.data.editable_transactions;
    })
    .catch();
},

methods: {

    hide() {
        this.$emit('hide');
    },

    onSubmit() {
        this.form.patch(`/trip/${this.trip_id}/advancedSettings`)
        .then( response => {
            this.hide();
        })
        .catch();
    }
}

}
</script>
