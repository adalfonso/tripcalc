<template>
    <div class="alert">
        <div class="popup-close" @click="$emit('hide')">&times;</div>
        <p>{{ message }}</p>
    </div>
</template>

<script>
    export default {
        props : {
            message: { required: true }
        }
    }
</script>
