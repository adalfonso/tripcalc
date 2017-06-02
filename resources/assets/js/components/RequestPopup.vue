<template>
<div id="friendRequests" v-if="size > 0">
    <p @click="visible = !visible">
        You have
        <a id="friendRequestsNumeric">{{ size }}</a>
        new {{ type }} requests.
    </p>
    <div id="tripRequestGroup" class="pendingRequests clearfix" v-if="visible">
        <div class="arw-up"></div>
        <table>
            <tr v-for="(request, index) in requests" :id="request.id">
                <td>{{ request }}</td>
                <td>
                    <div class="btn" @click="accept(index)">Accept</div>
                </td>
                <td>
                    <div class="btn" @click="decline(index)">Decline</div>
                </td>
            </tr>
        </table>
    </div>
    </div>
</template>

<script>
    export default {

        props: {
            type: { required: true }
        },

        data() {
            return {
                visible: false,
                requests: []
            };
        },

        created() {
            this.get();
        },

        computed: {
            size() {
                let size = 0, key;
                for (key in this.requests) {
                    if (this.requests.hasOwnProperty(key)) size++;
                }
                return size;
            }
        },

        methods: {

            accept(id) { return this.resolve(id, 1); },

            decline(id) { return this.resolve(id, -1); },

            resolve(id, resolution) {
                axios.post(`/${this.type}/${id}/resolveRequest`, {
                    resolution: resolution
                })
                .then(response => {
                    this.requests = response.data;
                });
            },

            get() {
                axios.get(`/${this.type}/requests`)
                .then(response => {
                    this.requests = response.data;
                });
            }
        }
    }
</script>
