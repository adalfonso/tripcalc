<template>
    <a class="nav-right" @click="logout">Log Out</a>
</template>

<script>
    export default {
        methods: {
            logout() {
                axios.post('/logout')
                .then(response => { window.location = '/'})
                .catch(err => {});
            }
        }
    }
</script>
