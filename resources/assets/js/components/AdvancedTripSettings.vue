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
            <p>Mask personal transactions</p>
        </div>

        <!-- Virtual users -->
        <p class="ui-error" v-if="form.errors.has('virtual_users')"
            v-text="form.errors.get('virtual_users')"></p>
        <div class="ui-checkbox" @click="form.toggle('virtual_users')">
            <input type="hidden" v-model="form.virtual_users">
            <div class="ui-input-btn no-hover"
                v-html="form.virtual_users ? '&#10004;' : '' "></div>
            <p>Enable virtual users</p>
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
            virtual_users: false
        })
    };
},

created() {
    axios.get(`/trip/${this.trip_id}/advancedSettings`)
    .then(response => {
        this.form = new Form(response.data);
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
            this.$emit('changestate', this.form.virtual_users);
            this.hide();
        })
        .catch();
    }
}

}
</script>
