<template>

<div class="menu-wrap">

<div class="menu" id="request-menu">

    <div class="arw-up-left">
        <div></div>
    </div>

    <div class="invis"></div>

    <div class="body">
        <table v-if="Object.keys(data).length">
            <template v-for="(requests, type) in data" v-if="requests.length">
                <tr>
                    <td colspan="3">
                        <h6>{{ capitalize(type) + 's' }}</h6>
                    </td>
                </tr>
                <tr v-for="request in requests">
                    <td>
                        <p v-if="type == 'friend'">
                            {{ request.first_name }} {{ request.last_name }}
                        </p>

                        <p v-if="type == 'trip'">
                            {{ request.name }}
                        </p>
                    </td>
                    <td>
                        <div class="btn btn-tiny" @click="accept(request.id, type)">
                            Accept
                        </div>
                    </td>
                    <td>
                        <div class="btn btn-tiny" @click="decline(request.id, type)">
                            Decline
                        </div>
                    </td>
                </tr>
            </template>
        </table>
    </div>
</div>

</div>

</template>


<script>
    export default {

        props: {
            data: { required: true }
        },

        methods: {
            capitalize(word) {
                return word.charAt(0).toUpperCase() + word.slice(1);
            },

            accept(id, type) {
                return this.resolve(id, type, 1);
            },

            decline(id, type) {
                return this.resolve(id, type, -1);
            },

            resolve(id, type, resolution) {
                axios.post(`/${type}/${id}/resolveRequest`, {
                    resolution: resolution
                })
                .then(response => {
                    this.$emit('refresh', response.data);
                }).catch(error => {
                    if (error.response.data.redirect) {
                        window.location = error.response.data.redirect;
                    }
                });
            }
        }
    }
</script>
