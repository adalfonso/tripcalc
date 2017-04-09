<template>
        <div id="splash-iconWrapper">

            <transition v-for="(img, index) in icons" name="carousel-icon">
                <img v-if="visible(index)" :src="'img/icon/' + img + '-256x256.png'">
            </transition>

            <div id="splashWrapper">
                <transition v-for="(img, index) in splash" name="carousel-splash">
                    <div v-if="visible(index)" :class=" 'splash' + img ">
                    </div>
                </transition>
            </div>

        </div>
</template>

<script>
    export default {

        data() {
            return {
                stage: 0,

                interval: 12000,

                icons: [
                    'car-light_blue',
                    'palm_tree-light_blue',
                    'pisa-light_blue',
                    'pinetree-light_blue'
                ],

                splash: [1, 2, 3 ,4]
            };
        },

        methods: {
            rotate() {
                setInterval(() => {
                    this.stage = this.stage === 3 ? 0 : this.stage + 1;
                }, this.interval);
            },

            visible(item) {
                return item === this.stage;
            }
        },

        created() {
            this.rotate();
        }
    }
</script>