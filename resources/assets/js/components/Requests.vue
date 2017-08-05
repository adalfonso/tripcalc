<template>
<div class="announcement request">
    <img src="/img/icon/profile-64x64.png" @click="goToProfile">

    <div class="badge offset" v-if="count" @click.stop="showOnMenu">
        {{ count }}
    </div>

    <div class="menu medium" id="requests">

        <div class="arw-up-left">
            <div class="hovercatch"></div>
            <div class="inner-shadow"></div>
        </div>

        <div class='body scroll scroll-midnight'>
            <table v-if="count">
                <template v-for="(group, type) in requests" v-if="group.length">
                    <tr>
                        <td colspan="3">
                            <h6>{{ capitalize(type) + 's' }}</h6>
                        </td>
                    </tr>
                    <tr v-for="request in group">
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

        data() {
            return {
                requests: {}
            };
        },

        created() {
            axios.get('/user/requests')
            .then(response => {
                this.requests = response.data;
            });
        },

        computed: {
            count() {
                let count = 0;

                for (let type in this.requests) {
                    count += this.requests[type].length;
                }

                return count > 99 ? 99 : count;
            }
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

            goToProfile() {
                window.location = '/profile';
            },

            resolve(id, type, resolution) {
                axios.post(`/${type}/${id}/resolveRequest`, {
                    resolution: resolution
                })
                .then(response => {
                    this.requests = response.data;
                }).catch(error => {
                    if (error.response.data.redirect) {
                        window.location = error.response.data.redirect;
                    }
                });
            },

            showOnMenu() {
                this.$emit('show', 'requests');
            }
        }
    }
</script>
