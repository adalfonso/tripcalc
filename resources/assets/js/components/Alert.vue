<template>
    <div class="alert">
        <img src="/img/icon/closePopup.png" class="closePopup closePopup-small" @click="$emit('hide')">
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
