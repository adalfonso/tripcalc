<template>
<div class="announcement notification">
    <img src="/img/icon/paper_airplane-64x64.png">

    <div class="badge offset" v-if="count" @click.stop="showOnMenu">
        {{ count }}
    </div>

    <div class="menu medium" id="notifications">

        <div class="arw-up-left">
            <div class="hovercatch"></div>
            <div class="inner-shadow"></div>
        </div>

        <div class='body'>
            <div v-for="notification in notifications" v-if="notifications.length">
                <p :class="notification.seen ? '' : 'new'">
                    <span v-if="isCloseout(notification)">
                        A closeout for
                        <b @click="visit('/trip/' + notification.notifiable_id)">
                            {{ notification.notifiable.name }}
                        </b>
                        has been initiated <b>{{ notification.creator.first_name }}
                        {{ notification.creator.last_name }}</b>.
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>

</template>


<script>
    export default {

        data() {
            return {
                notifications: []
            };
        },

        created() {
            axios.get('/user/notifications')
            .then(response => {
                this.notifications = response.data;
            });
        },

        computed: {
            count() {
                return this.notifications.length;
            }
        },

        methods: {

            isCloseout(notification) {
                return notification.notifiable_type === 'App\\Trip'
                    && notification.subtype === 'closeout';
            },

            visit(endpoint) {
                window.location = endpoint;
            },

            showOnMenu() {
                this.$emit('show', 'notifications');
            }
        }
    }
</script>
