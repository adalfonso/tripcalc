<template>
    <div class="friend-preferences clearfix">
        <div class="status trigger">
            <p>
                &#10004; Friends
                <span class="caret">&#9660;</span>
            </p>
        </div>
        <div class="menu">
            <div class="arw-up-right">
                <div class="hovercatch"></div>
                <div class="inner-shadow"></div>
            </div>

            <div class="body">
                <p @click="unfriend"><a>Unfriend</a></p>
            </div>
        </div>
    </div>
</template>

<script>
    export default {

        props: {
            id: { required: true },
            refresh: { default: false }
        },

        methods: {
            unfriend() {
                axios.delete(`/friend/${this.id}/unfriend`)
                .then( response => {
                    if (this.refresh) {
                        return window.location = window.location.href;
                    }

                    this.$emit('unfriend');
                });
            }
        }
    }
</script>
