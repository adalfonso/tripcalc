<template>
<div class="announcement notification">
    <img src="/img/icon/paper_airplane-64x64.png">

    <div class="badge offset"
        v-if="unseen"
        @click.stop="showOnMenu"
        @mouseover="haveBeenSeen">
        {{ unseen }}
    </div>

    <div class="menu medium" id="notifications">

        <div class="arw-up-left">
            <div class="hovercatch"></div>
            <div :class="shadowClass"></div>
        </div>

        <div class='body'>
            <div v-for="notification in notifications.use()" v-if="notifications.count()">
                <p :class="notification.seen ? 'unseen' : 'seen'">
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
                notifications: new Collect([])
            };
        },

        created() {
            axios.get('/user/notifications')
            .then(response => {
                this.notifications = new Collect(response.data);
            });
        },

        computed: {
            unseen() {
                return this.notifications
                    .where('seen', 0)
                    .count();
            },

            shadowClass() {
                let className = 'inner-shadow';

                if (this.notifications.isEmpty()) {
                    return className;
                }

                return className += this.notifications.first().seen === 1
                    ? ''
                    : ' lighter';
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

            haveBeenSeen() {
                let unseen = this.notifications.where('seen', 0);

                if (unseen.isEmpty()) {
                    return;
                }

                axios.post('user/notifications/see', {
                    seen: unseen.pluck('id').use()
                })
                .then(response => {})
                .catch(error => {});
            },

            showOnMenu(forceful = false) {
                this.$emit('show', 'notifications', forceful);
            }
        }
    }
</script>
