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
        <div v-if="active && !virtualUsers.length">
            <p class="ui-error" v-if="form.errors.has('virtual_users')"
                v-text="form.errors.get('virtual_users')"></p>
            <div class="ui-checkbox" @click="toggleVirtualUsers">
                <input type="hidden" v-model="form.virtual_users">
                <div class="ui-input-btn no-hover"
                    v-html="form.virtual_users ? '&#10004;' : '' "></div>
                <p>Enable virtual users</p>
            </div>
        </div>

        <!-- Private transactions -->
        <div class="ui-checkbox" @click="closeout"
            v-if="active">
            <input type="hidden" v-model="form.closeout">
            <div class="ui-input-btn no-hover"
                v-html="form.closeout ? '&#10004;' : '' "></div>
            <p>Closeout this trip</p>
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
            virtual_users: false,
            closeout: false
        }),
        active: true,
        virtualUsers: []
    };
},

created() {
    axios.get(`/trip/${this.trip_id}/advancedSettings`)
    .then(response => {
        this.active = response.data.active;
        this.virtualUsers = response.data.virtualUsers;
        this.form = new Form(response.data.settings);
    })
    .catch();
},

methods: {
    toggleVirtualUsers() {
        if (this.virtualUsers.length) {
            return;
        }

        this.form.toggle('virtual_users')
    },

    hide() {
        this.$emit('hide');
    },

    closeout() {
        if (!this.active) {
            return;
        }

        this.form.toggle('closeout');
    },

    onSubmit() {
        this.form.patch(`/trip/${this.trip_id}/advancedSettings`)
        .then(response => {
            this.$emit('change-trip-state', response);
            this.$emit('change-virtual-user-state', this.form.virtual_users);
            this.hide();
        })
        .catch(error => {});
    }
}

}
</script>
