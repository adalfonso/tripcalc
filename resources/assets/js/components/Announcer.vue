<template>
    <div class="announcer clearfix">

        <requests
            @show="showOnMenu"
            @hideMenus="hideForcefulMenus">
        </requests>

        <notifications @show="showOnMenu"></notifications>

    </div>
</template>

<script>


export default {
    computed: {
        smallScreen() {
            return window.innerWidth <= 768;
        }
    },

    methods: {
        showOnMenu(id, forceful = false) {
            let elem = document.getElementById(id);

            // In case there are no items
            if (!elem) {
                return;
            }

            let show = !elem.classList.contains('showOnMenu');

            this.hideForcefulMenus();

            if (!this.smallScreen && !forceful) {
                return;
            }

            if (show) {
                bus.$emit('closeNav');
                elem.classList.add('showOnMenu');
                elem.querySelector('.hovercatch').style.display = 'none';
            }
        },

        hideForcefulMenus() {
            document.querySelectorAll('.showOnMenu').forEach(item => {
                item.classList.remove('showOnMenu');
                item.querySelector('.hovercatch').style.display = '';
            });
        }
    }
}
</script>
