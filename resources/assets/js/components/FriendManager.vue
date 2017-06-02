<template>
<div class="popup-wrap" @click.self="hide" id="friend-manager">
    <div class="popup popup-wide">
        <div class="popup-close" @click="hide">&times;</div>

        <h4 class="centered">Friend Manager</h4>

    	<div class="friendList clearfix">
			<div class="tile" v-for="(friend, key) in friends">

                <div class="left">
                    <div class="img" v-if="friend.path !== null"
                        :style="'background-image: url(' + friend.path + ')'">
                    </div>

                    <div class="img" v-if="friend.path === null">
                        No Picture
                    </div>
                </div>

                <div class="left">
                    <p>{{ friend.fullname }}</p>
                    <friend-preferences :id="friend.id" @unfriend="removeFriend(key)">
                    </friend-preferences>
                </div>

			</div>
    	</div>

    </div>
</div>
</template>

<script>

export default {

created() {
    axios.get('/friends')
    .then(response => {
        this.friends = response.data;
    });
},

data() {
    return {
        friends: []
    };
},

methods: {
    hide() {
        this.$emit('hide');
    },

    removeFriend(key) {
        this.friends.splice(key, 1);
    }
}

}
</script>
