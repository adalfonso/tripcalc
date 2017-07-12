<template>
    <div id="requests" class="clearfix">


        <div class="arw">
            <div></div>
        </div>

        <div class="border">
            <div class="invis"></div>
            <ul>
                <li v-for="(requests, type) in data" v-if="requests.length">
                    <h6>{{ capitalize(type) + 's' }}</h6>
                    <ul class="clearfix">
                        <li v-for="request in requests">
                        <span v-if="type == 'friend'">
                            {{ request.first_name }} {{ request.last_name }}
                        </span>

                        <span v-if="type == 'trip'">
                            {{ request.name }}
                        </span>
                    </li>
                    </ul>
                </li>
            </ul>
            <!-- <p @click="visible = !visible">
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
            </div> -->
        </div>
    </div>
</template>


<script>
    export default {

        props: {

        },

        data() {
            return {
                data: []
            };
        },

        created() {
            axios.get('/user/requests')
            .then(response => {
                this.data = response.data.requests;
            });
        },

        computed: {
        },

        methods: {
            capitalize(word) {
                return word.charAt(0).toUpperCase() + word.slice(1);
            }
        }
    }
</script>
