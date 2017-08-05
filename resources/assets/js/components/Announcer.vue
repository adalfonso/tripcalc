<template>
    <div class="announcer clearfix">

        <requests @show="showOnMenu"></requests>

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
        showOnMenu(id) {
            if (!this.smallScreen) {
                return;
            }

            let elem = document.getElementById(id);
            let show = !elem.classList.contains('showOnMenu');

            document.querySelectorAll('.menu').forEach(item => {
                item.classList.remove('showOnMenu');
            });

            if (show) {
                bus.$emit('closeNav');
                elem.classList.add('showOnMenu');
            }
        }
    }
}
</script>
