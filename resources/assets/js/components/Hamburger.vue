<template>
    <a class="hamburger clearfix">
        <div class="bars" @click="toggleMobileMenu">
            <div></div>
            <div></div>
            <div></div>
        </div>
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
            this.closeMobileMenu();
        });
    },

    methods: {
        links() {
            return document.querySelectorAll(
               `#nav-search > *,
                #nav-left > *,
                .mobile-nav > *,
                #nav-right > *,
                #nav-right .advanced-settings .menu`
            );
        },

        closeMobileMenu () {
            this.links().forEach(link => {
                link.style.display = '';
            });

            this.menuIsVisible = false;
        },

        toggleMobileMenu() {
            this.links().forEach(link => {
                link.style.display = this.menuIsVisible ? '' : 'block';
            });

            document.querySelectorAll('.menu').forEach(item => {
                item.classList.remove('showOnMenu');
            });

            this.menuIsVisible = !this.menuIsVisible;
        }
    }
}
</script>
