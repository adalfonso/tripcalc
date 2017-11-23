<template>
<div class="announcement notification">
    <img src="/img/icon/paper_airplane-64x64.png" @click="showOnMenu(true)">

    <div class="badge offset"
        v-if="unseen"
        @click.stop="showOnMenu"
        @mouseover="haveBeenSeen">
        {{ unseen }}
    </div>

    <div class="menu medium" id="notifications" v-if="notifications.count() || loading">

        <div class="arw-up-left">
            <div class="hovercatch"></div>
            <div :class="shadowClass"></div>
        </div>

        <div class='body scroll scroll-midnight' ref="scroll">

            <div v-for="notification in notifications.use()" v-if="notifications.count()">
                <p :class="notification.seen ? 'unseen' : 'seen'"
                    @click="link(notification)">

                    <span v-if="isCloseout(notification)">
                        A closeout for
                        <b>{{ notification.notifiable.name }}</b>
                        has been initiated by
                        <b>
                            {{ notification.creator.first_name }}
                            {{ notification.creator.last_name }}
                        </b>.
                    </span>

                    <span v-if="isClosed(notification)">
                        <b>{{ notification.notifiable.name }}</b>
                        is now officially closed.
                    </span>

                    <span v-if="isPost(notification) && !isComment(notification)">
                        <b>
                            {{ notification.creator.first_name }}
                            {{ notification.creator.last_name }}
                        </b>

                        <template v-if="isTripPost(notification)">
                            has posted on
                            <b @click.stop="visit('/trip/' + notification.notifiable.postable_id)">
                                {{ notification.notifiable.postable.name }}</b>.
                        </template>

                        <template v-if="isProfilePost(notification)">
                            has posted on your
                            <b @click.stop="visit('/profile')">profile</b>.
                        </template>
                    </span>

                    <span v-if="isComment(notification)">
                        <b>
                            {{ notification.creator.first_name }}
                            {{ notification.creator.last_name }}
                        </b>

                        <span v-if="createdByUser(notification.notifiable)">
                            has commented on your post.
                        </span>

                        <span v-else-if="createdByNotifier(notification)">
                            has commented on their post.
                        </span>

                        <span v-else-if="hasCommented(notification.notifiable)">
                            has replied to your comment.
                        </span>

                        <span v-else>
                            has commented on
                            {{ notification.notifiable.user.first_name}}
                            {{ notification.notifiable.user.last_name}}'s post.
                        </span>
                    </span>

                    <span class="announce-date">{{ notification.date }}</span>
                </p>
            </div>

        </div>
    </div>
</div>

</template>

<script>
    import ScrollTracker from '../lib/ScrollTracker.js';

    export default {

        data() {
            return {
                notifications: new Collect([]),
                loading: true,
                tracker: ''
            };
        },

        created() {
            axios.post('/user/notifications')
            .then(response => {
                this.notifications = new Collect(response.data);
                this.loading = false;
            });
        },

        mounted() {
            this.tracker = new ScrollTracker(this.$refs.scroll, this.more);
        },

        computed: {
            unseen() {
                let count = this.notifications
                    .where('seen', 0)
                    .count();

                return count > 99 ? 99 : count;
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
            createdByNotifier(notification) {
                return notification.created_by === notification.notifiable.created_by;
            },

            createdByUser(notifiable) {
                return notifiable.created_by === userId;
            },

            isTrip(notification) {
                return notification.notifiable_type === 'App\\Trip';
            },

            isCloseout(notification) {
                return this.isTrip(notification) && notification.subtype === 'closeout';
            },

            isClosed(notification) {
                return this.isTrip(notification) && notification.subtype === 'closed';
            },

            isComment(notification) {
                return this.isPost(notification) && notification.subtype === 'comment';
            },

            isPost(notification) {
                return notification.notifiable_type === 'App\\Post';
            },

            isTripPost(notification) {
                return this.isPost(notification) && notification.subtype === 'trip';
            },

            isProfilePost(notification) {
                return this.isPost(notification) && notification.subtype === 'profile';
            },

            hasCommented(notifiable) {
                return new Collect(notifiable.comments)
                    .where('created_by', userId).isNotEmpty();
            },

            link (notification) {
                if (this.isTrip(notification)) {
                    window.location = '/trip/' + notification.notifiable_id;
                }

                if (this.isPost(notification)) {
                    window.location = '/post/' + notification.notifiable_id;
                }
            },

            visit(endpoint) {
                window.location = endpoint;
            },

            haveBeenSeen() {
                let unseen = this.notifications.where('seen', 0);

                if (unseen.isEmpty()) {
                    return;
                }

                axios.post('/user/notifications/see', {
                    seen: unseen.pluck('id').use()
                })
                .then(response => {})
                .catch(error => {});
            },

            more() {
                axios.post('/user/notifications', {
                    last: this.notifications.last()
                })
                .then(response => {
                    if (response.data.length === 0) {
                        return;
                    }

                    this.notifications = this.notifications.merge(response.data);
                });
            },

            showOnMenu(forceful = false) {
                this.$emit('show', 'notifications', forceful);
            }
        }
    }
</script>
