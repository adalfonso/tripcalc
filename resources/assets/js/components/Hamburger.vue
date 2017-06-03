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

    created() {
        bus.$on('closeNav', () => {
            this.toggleMobileMenu();
        });
    },

    methods: {
        toggleMobileMenu() {
            let links = document.querySelectorAll(
                '#nav-left > *, #nav-right > *'
            );

            links.forEach( link => {
                link.style.display = this.menuIsVisible ? '' : 'block';
            });

            this.menuIsVisible = !this.menuIsVisible;
        }
    }
}
</script>
