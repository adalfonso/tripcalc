<template>
    <div class="alert">
        <div v-if="hasButton" class="popup-close" @click="$emit('hide')">
            &times;
        </div>

        <p style="white-space:pre-wrap;">{{ message }}</p>

        <div v-if="agree" class="align-right">
            <button class="btn btn-extra-thin" type="button" @click="hide">
                Ok
            </button>
        </div>

        <div v-if="decide" class="align-right">
            <button class="btn btn-extra-thin" type="button" @click="$emit('yes')">
                Ok
            </button>
            <button class="btn btn-extra-thin" type="button" @click="$emit('no')">
                Exit
            </button>
        </div>
    </div>
</template>

<script>
    export default {
        props : {
            message: { required: true },
            type: { default: null }
        },

        computed: {
            agree() {
                return this.type === 'agree';
            },

            decide() {
                return this.type === 'decide';
            },

            hasButton() {
                return !this.agree && !this.decide;
            }
        },

        methods: {
            hide() {
                this.$emit('hide');
            }
        }
    }
</script>
