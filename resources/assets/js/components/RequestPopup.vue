<template>
<div id="friendRequests" class="pendingRequests" v-if="size > 0">
    <p @click="visible = !visible">
        You have
        <a id="friendRequestsNumeric">{{ size }}</a>
        new {{ type }} requests.
    </p>
    <div id="tripRequestGroup" class="pendingRequestsPopup clearfix" v-if="visible">
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

        props: ['type', 'requests'],

        data() {
            return {
                visible: false
            };
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

            accept(item) {
                axios.post('/requests/' + this.type, {'resolution': 1, id: item})
                .then(response => {
                    this.requests = response.data;
                });
            },

            decline(item) {
                axios.post('/requests/' + this.type, {'resolution': -1, id: item})
                .then(response => {
                    this.requests = response.data;
                });
            }
        }
    }
</script>