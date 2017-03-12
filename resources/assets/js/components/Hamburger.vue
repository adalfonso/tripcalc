<template>
    <a class="menu" @click="toggleMobileMenu">
        <div></div>
        <div></div>
        <div></div>
    </a>
</template>

<script>
export default {
    data() {
        return {
            menuIsVisible: false
        };
    },

    methods: {
        toggleMobileMenu() {
            let links = document.querySelectorAll(
                '#nav > a:nth-of-type(n+2), #nav > div'
            );

            links.forEach(function(link) {
                link.style.display = this.menuIsVisible ? '' : 'block';
            }.bind(this));

            this.menuIsVisible = !this.menuIsVisible;
        }
    }
}
</script>