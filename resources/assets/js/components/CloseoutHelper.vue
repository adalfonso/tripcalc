<template>
    <div style="display:inline-block">
        <button v-if="pending" class="btn-tiny" @click="close">
            Close Now
        </button>
    </div>
</template>

<script>
    export default {

        props: {
            trip_id: { required: true }
        },

        data() {
            return {
                pending: false
            };
        },

        created() {
            axios.get(`/trip/${this.trip_id}/advancedSettings`)
            .then(response => {
                this.pending = !response.data.settings.closeout;
            })
            .catch(error => {});
        },

        methods: {
            close(){
                axios.post(`/trip/${this.trip_id}/closeout`)
                .then(response => {
                    this.pending = false;
                    this.$emit('change-trip-state', response.data);
                })
                .catch(error => {});
            }
        }
    }
</script>
