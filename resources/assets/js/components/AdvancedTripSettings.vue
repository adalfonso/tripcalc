<template>
    <div class="popup-wrap" @click.self="hide">
    <form id="tripForm" class="popup" @submit.prevent="onSubmit">
        <div class="popup-close" @click="hide">&times;</div>
        <h4 class="centered">Advanced Settings</h4>
        <hr>

        <!-- Private transactions -->
        <div class="ui-checkbox" @click="form.toggle('privateTransactions')">
            <input type="hidden" v-model="form.transactionPrivate">
            <div class="ui-input-btn no-hover"
                v-html="form.privateTransactions ? '&#10004;' : '' "></div>
            <p>Make personal transactions private</p>
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
            privateTransactions: false,
            placeholderUsers: false,
            easyTransactions: false
        })
    };
},

created() {

},

computed: {

},


methods: {

    hide() {
        this.$emit('hide');
    },

    onSubmit() {

    }
}

}
</script>
