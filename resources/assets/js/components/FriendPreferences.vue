<template>
    <div class="friend-preferences clearfix">
        <div class="status">
            <p>
                &#10004; Friends
                <span class="caret">&#9660;</span>
            </p>
        </div>
        <div class="options">
            <div class="arw-up"></div>
            <div class="invis"></div>
            <p @click="unfriend"><a>Unfriend</a></p>
        </div>
    </div>
</template>

<script>
    export default {

        props: {
            id: { required: true }
        },

        methods: {
            unfriend() {
                axios.delete(`/friend/${this.id}/unfriend`)
                .then( response => {
                    window.location = window.location.href;
                });
            }
        }
    }
</script>
